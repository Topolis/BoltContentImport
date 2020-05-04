<?php

/*
 * This Filter transforms the Kraken gallery in to an ImageService set of images.
 * It uses
 */

namespace Topolis\Bolt\Extension\ContentImport\Filter;

use Exception;
use Silex\Application;
use Topolis\Bolt\Extension\ContentImport\Filter\Traits\ApplyFilter;
use Topolis\Bolt\Extension\ContentImport\IFilter;
use Topolis\FunctionLibrary\Collection;

class Sections2ImageService implements IFilter {

    use  ApplyFilter;

    public static $parsers = [];

    public static function filter($input, $parameters, Application $app, $values, $source){

        $items = [
            "items" => []
        ];

        if(is_array($input)) {
            foreach($input as $section) {

                $type = isset($section["type"]) ? $section["type"] : false;
                if(!$type || $type != 'image')
                    continue;

                $item = self::applyFilters($parameters['filters'] ?? [], $section,  $app, $values, $section);

                if(!$item)
                    continue;

                $items["items"] = array_merge($items["items"], $item['items']);
            }
        }

        return json_encode($items);
    }

}
