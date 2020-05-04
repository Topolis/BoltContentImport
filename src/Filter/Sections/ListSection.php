<?php

namespace Topolis\Bolt\Extension\ContentImport\Filter\Sections;

class ListSection {

    public function parse($input, $parameters){

        $items = [];
        foreach($input["elements"] ?? [] as $item){
            $items[] = [
                "content" => $item
            ];
        }

        return [
            "type" => "list",
            "data" => [
                "format" => "html",
                "listItems" => $items
            ]
        ];
    }

}