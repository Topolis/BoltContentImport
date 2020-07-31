<?php


namespace Topolis\Bolt\Extension\ContentImport\Filter\Sections;


class TracDelightSection
{
    public function parse($input, $parameters){

        if(!$input['id']) {
            return false;
        }

        return [
            "type" => "tracdelight",
            "data" => [
                "text" => $input['id']
            ]
        ];
    }
}