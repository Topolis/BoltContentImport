<?php
namespace Topolis\Bolt\Extension\ContentImport\Command;

use Pimple as Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Topolis\Bolt\Extension\ContentImport\Extension;

/**
 * An nut command for then KoalaCatcher extension.
 *
 * @author Kenny Koala
 * <kenny@dropbear.com.au>
 */
class ImportIterative extends Command {

    protected $app;

    /**
     * @param Container $app
     */
    public function __construct(Container $app)
    {
        parent::__construct();
        $this->app = $app;
    }

    protected function configure() {
        $this
            ->setName('contentimport:iterative')
            ->setDescription('Import one or all content sources into Bolt')
            ->addOption(
                'source',
                's',
                InputArgument::OPTIONAL,
                'Only import this source',
                false
            )->addOption(
                'iterations',
                'i',
                InputArgument::OPTIONAL,
                'Max number Call to make to get records',
                false
            )->addOption(
                'iterator',
                'r',
                InputArgument::OPTIONAL,
                'Set Initial Import Offset',
                false
            )->addOption(
                'initial',
                't',
                InputArgument::OPTIONAL,
                'Max number Call to make to get records',
                0
            )->addOption(
                'source-file',
                'f',
                InputArgument::OPTIONAL,
                'Read overwrites from file',
                0
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $source     = $input->getOption('source');
        $verbose    = $input->getOption('verbose');
        $initial    = $input->getOption('initial') ?: 0;
        $iterator   = $input->getOption('iterator') ?: 'offset';
        $iterations = $input->getOption('iterations') ?: 1;
        $file       = $input->getOption('source-file') ?: null;

        $overrides = [];

        if($file) {

            $addOverrides = file_get_contents($file);
            $addOverrides = json_decode($addOverrides, true);
            $overrides += $addOverrides;
        }

        $overrides[$iterator] = $initial;
        $overrides['source.options.api.query.limit'] = 30;

        for ($i=0; $i<$iterations; $i++) {
            $output->writeln('Iteration: ' . $overrides[$iterator]);
            $this->app[Extension::EXTID . ".importer"]->import($source, $output, $verbose, $overrides);
            $overrides[$iterator] += 30;
        }

    }
}