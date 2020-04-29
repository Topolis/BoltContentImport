<?php


namespace Topolis\Bolt\Extension\ContentImport\Filter\Sections;


use Topolis\Bolt\Extension\ContentImport\Filter\Traits\ApplyFilter;
use Topolis\Bolt\Extension\ContentImport\Filter\Traits\ImportContent;
use Topolis\FunctionLibrary\Collection;

class TeaserSection
{

    use ImportContent;
    use ApplyFilter;

    private $app;

    public function __construct($app) {
        $this->app = $app;
    }

    public function parse($input, $parameters){
        $parameters = Collection::get($parameters, 'Teaser',[]);
        $teaser = [];
        $map = $parameters['map'];

        foreach($map as $target => $src) {
            $srcPath = $src;
            $filters = null;

            if(is_array($src)){
                $srcPath = $src['source'];
                $filters  = $src['filters'];
            }

            $value = Collection::get($input, $srcPath, '');

            if($filters)
                $value = $this->applyFilters($filters, $value, $this->app, $teaser, $input);

            Collection::set($teaser, $target, $value);
        }

        if($teaser)
            $teaser = $this->importContent($this->app, $teaser, [], $parameters['config']);

        $contenttype = Collection::get($parameters, 'config.contenttypeslug', '');

        return [
            "type" => ucfirst($contenttype),
            "data"=>[
                "status" => true,
                "items" => [
                    [
                        "id"=> $contenttype."/".$teaser['id'],
                        "service" => "content",
                        "type"=>"teaser",
                        "attributes"=>[]
                    ]
                ]
            ]
        ];

    }
}