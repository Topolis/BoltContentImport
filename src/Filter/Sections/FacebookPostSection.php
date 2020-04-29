<?php


namespace Topolis\Bolt\Extension\ContentImport\Filter\Sections;


class FacebookPostSection
{
    public function parse($input, $parameters){
        return false;
        return [
            "type" => "oembed",
            "data" => [
                "type" => "facebook",
                "url" => $input["id"],
                "html" => $input["embed"]
            ]
        ];
    }
}