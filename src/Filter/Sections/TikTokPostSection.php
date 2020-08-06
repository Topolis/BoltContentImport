<?php

namespace Topolis\Bolt\Extension\ContentImport\Filter\Sections;

class TikTokPostSection {

    public function parse($input, $parameters){
        return [
            "type" => "oembed",
            "data" => [
                "type" => "tiktok",
                "url"  => "https://www.tiktok.com/".$input["id"]."/video/",
                "html" => $input["embed"]
            ]
        ];
    }

}