<?php
namespace Topolis\Bolt\Extension\ContentImport\Service;
use Bolt\Storage\Collection\Taxonomy;
use Bolt\Storage\Entity\Content;
use Bolt\Storage\Repository;
use DateTime;
use Doctrine\DBAL\Statement;
use Exception;
use Silex\Application;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Topolis\Bolt\Extension\ContentImport\Filter\Traits\ApplyFilter;
use Topolis\Bolt\Extension\ContentImport\Filter\Traits\ImportContent;
use Topolis\Bolt\Extension\ContentImport\IFilter;
use Topolis\Bolt\Extension\ContentImport\IFormat;
use Topolis\FunctionLibrary\Collection;
use Topolis\FunctionLibrary\Token;

/**
 * An nut command for then KoalaCatcher extension.
 *
 * @author Kenny Koala
 * <kenny@dropbear.com.au>
 */
class Importer {

    use ImportContent;

    /* @var array $config */
    protected $config;
    /* @var Application $app */
    protected $app;

    protected $baseNS = null;

    protected static $defaults = [
        "imports" => []
    ];

    protected static $defaultField = [
        "source" => "unknown",
        "filters" => [],
        "default" => false
    ];


    public function __construct($app, $config){
        $this->app = $app;
        $this->config = $config + self::$defaults;

        $ns = explode("\\", __NAMESPACE__);
        array_pop($ns);
        $this->baseNS = implode("\\", $ns);
    }

    /**
     * Return list of sources with cron intervals
     * @return array
     */
    public function getCronTasks(){
        $list = [];

        foreach($this->config["imports"] as $key => $task) {
            if(!isset($task["interval"]) || !$task["interval"])
                continue;

            $list[$key] = $task["interval"];
        }

        return $list;
    }

    /**
     * @param string|bool $source
     * @param OutputInterface|bool $output
     * @param bool $verbose
     * @throws Exception
     */
    public function import($source = false, $output = false, $verbose = false, $overrideSource=[]){
        $hasMore = [];
        foreach($this->config["imports"] as $key => $task) {
            if (!$source || $source == $key) {

                // Allows to manually change the Predefined Import
                // for manual imports and tests
                foreach($overrideSource as $path => $value) {
                    Collection::set($task, $path, $value);
                }

                $count = Collection::get($task, "count", 100);

                $output->writeln("Importing source ".$key." ");

                $parsed = $this->parseSource($task, $output, $verbose);

                $progress = new ProgressBar($output, min(count($parsed["items"]), $count));
                $progress->setFormat('very_verbose');
                $progress->start();

                $imported = 0;
                foreach($parsed["items"] as $item){

                    if($imported >= $count)
                        break;

                    $this->importContent($this->app, $item, $parsed['channel'], $task);

                    $progress->advance();
                    $imported ++;
                }

                $hasMore[$key] = $imported <= $count;

                $progress->finish();
                $output->writeln("");
            }
        }

        return $hasMore;
    }

    /**
     * @param string|bool $source
     * @param OutputInterface|bool $output
     * @param bool $verbose
     */
    public function purge($source = false, $output = false, $verbose = false){
        foreach($this->config["purges"] as $key => $task) {
            if (!$source || $source == $key) {
                $output->write("Purging source ".$key." ");

                $contenttype = Collection::get($task, "contenttypeslug", false);
                $filters = Collection::get($task, "filters", []);
                $keep = Collection::get($task, "keep", 0);

                /* @var Repository $repo */
                $repo = $this->app['storage']->getRepository($contenttype);

                $total = 0;

                $elements = $repo->findBy($filters, ["datecreated","DESC"], 999999, $keep);
                if($elements){
                    foreach($elements as $element){
                        if ($repo->delete($element))
                            $total++;
                    }
                }

                $output->writeln($total." elements deleted");
            }
        }

        return;
    }

    /**
     * @param $source
     * @param OutputInterface $output
     * @param $verbose
     * @return array
     * @throws Exception
     */
    protected function parseSource($source, OutputInterface $output, $verbose){

        $format = Collection::get($source, "source.format", "rss2");
        $url = Collection::get($source, "source.url", false);
        $options = Collection::get($source, "source.options", []);

        $formatClass = $this->baseNS."\\Format\\".ucfirst($format);

        if(!class_exists($formatClass))
            throw new Exception("Unknown format '".$format."' specified");

        if(!$url)
            throw new Exception("Invalid url '".$url."' specified");

        $Format = new $formatClass($options, $this->app);

        if(!$Format instanceof IFormat)
            throw new Exception("Invalid format class '".$format."' specified");

        return $Format->parse($url);
    }

}