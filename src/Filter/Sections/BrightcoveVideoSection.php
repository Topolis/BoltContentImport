<?php


namespace Topolis\Bolt\Extension\ContentImport\Filter\Sections;


class BrightcoveVideoSection
{
    public function parse($input, $parameters){

        if(!$input['id'])
            return null;

        return [
            "type" => "brightcove",
            "data" => [
                "id" => $input['id']
            ]
        ];
    }
}