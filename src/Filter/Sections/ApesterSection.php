<?php


namespace Topolis\Bolt\Extension\ContentImport\Filter\Sections;


class ApesterSection
{
    public function parse($input, $parameters){

        if(!$input['id']) {
            return false;
        }

        return [
            "type" => "apester",
            "data" => [
                "text" => $input['id']
            ]
        ];
    }
}