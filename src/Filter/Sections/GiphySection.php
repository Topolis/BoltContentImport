<?php


namespace Topolis\Bolt\Extension\ContentImport\Filter\Sections;


class GiphySection
{
    public function parse($input, $parameters){

        // Pinterest is not directly supported by OEmbed2 -> Use custom Text
        return [
            "type" => "giphy",
            "data" => [
                "text" => "https://giphy.com/embed/" . $input['id'],
                "format" => "html",
                "custom_type" => "Giphy"
            ]
        ];
    }
}