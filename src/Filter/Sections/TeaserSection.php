<?php


namespace Topolis\Bolt\Extension\ContentImport\Filter\Sections;


use Topolis\Bolt\Extension\ContentImport\Filter\Traits\ApplyFilter;
use Topolis\Bolt\Extension\ContentImport\Filter\Traits\ImportContent;
use Topolis\FunctionLibrary\Collection;

class TeaserSection {

    use ImportContent;

    private $app;

    public function __construct($app) {
        $this->app = $app;
    }

    /**
     * @param $input
     * @param $parameters
     * @return array|null
     * @throws \Exception
     */
    public function parse($input, $parameters){

        $parameters = Collection::get($parameters, 'Teaser',[]);
        $teaser = [];
        $map = $parameters['map'];
        $isInternal = $parameters['is_internal'] ?? false;
        $contenttype = Collection::get($parameters, 'config.contenttypeslug', '');
        $result = [
            "type" => ucfirst($contenttype),
            "data"=>[
                "status" => true,
                "items" => [
                    [
                        "id"=> "",
                        "service" => "content",
                        "type"=> "teaser",
                        "attributes"=>[]
                    ]
                ]
            ]
        ];

        // Internal teasers will have to already been imported
        // TODO: Let an external script set the right relationlist link
        if($isInternal && preg_match($isInternal, $input['url'])) {
            $path = explode('/', $input['url']);
            Collection::set($result, 'data.items.0.id', "unknown/".end($path));

            return $result;
        }

        foreach($map as $target => $src) {
            $srcPath = $src;
            $filters = null;

            if(is_array($src)){
                $srcPath = $src['source'];
                $filters  = $src['filters'];
            }

            $value = Collection::get($input, $srcPath, '');
            if($filters)
                $value = self::applyFilters($filters, $value, $this->app, $teaser, $input);

            Collection::set($teaser, $target, $value);
        }

        if(!$teaser)
            return null;

        $teaser = $this->importContent($this->app, $teaser, [], $parameters['config']);

        Collection::set($result, 'data.items.0.id', $contenttype."/".$teaser['id']);

        return $result;

    }
}