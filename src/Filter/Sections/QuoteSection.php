<?php


namespace Topolis\Bolt\Extension\ContentImport\Filter\Sections;


class QuoteSection
{
    public function parse($input){

        if(!($input["text"] ?? ''))
            return null;

        return [
            "type" => "quote",
            "data" => [
                "format" => "html",
                "text" => $input["text"],
                "cite" => $input["cite"] ?? ''
            ]
        ];
    }
}