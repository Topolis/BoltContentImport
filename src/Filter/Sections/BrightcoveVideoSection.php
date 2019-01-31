<?php

namespace Topolis\Bolt\Extension\ContentImport\Filter\Sections;

class BrightcoveVideoSection {

    public function parse($input, $parameters){
        return [
            "type" => "brightcove-video",
            "data" => [
                "id" => $input["id"]
            ]
        ];
    }

}