<?php

namespace Topolis\Bolt\Extension\ContentImport\Filter\Sections;

class GallerySection {

    protected $imageSection = null;

    public function __construct($app) {
        $this->app = $app;
        $this->imageSection = new ImageSection($this->app);
    }

    public function parse($input, $parameters){

        $images = [];
        foreach($input["items"] as $item) {
            $result = $this->imageSection->parse($item, $parameters);

            if(isset($result["data"]["items"]))
                $images = array_merge($images, $result["data"]["items"]);
        }

        return [
            "type" => "imageservice",
            "data" => [
                "items" => $images
            ]
        ];
    }

}