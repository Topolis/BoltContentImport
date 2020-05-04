<?php


namespace Topolis\Bolt\Extension\ContentImport\Filter\Sections;


class FacebookVideoSection
{
    public function parse($input, $parameters){
        if(!$input["id"]) {
            return null;
        }

        return [
            "type" => "oembed",
            "data" => [
                "type" => "facebookvideo",
                "url" => $input["id"],
                "html" => $input["embed"]
            ]
        ];
    }
}