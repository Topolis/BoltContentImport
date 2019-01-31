<?php

namespace Topolis\Bolt\Extension\ContentImport\Filter;

use Exception;
use Silex\Application;
use Topolis\Bolt\Extension\ContentImport\IFilter;
use Topolis\FunctionLibrary\Collection;

class Sections2Structured implements IFilter {

    public static $parsers = [];

    public static function filter($input, $parameters, Application $app, &$values, &$source){

        $items = [
            "data" => []
        ];

        foreach($input as $section) {

            $type = isset($section["type"]) ? $section["type"] : false;
            if(!$type)
                continue;

            $parser = self::loadParser($type, $app);
            if(!$parser){
                echo "Missing type: ".$type."\n";
                continue;
            }

            $item = $parser->parse($section, $parameters);
            if(!$item)
                continue;

            $items["data"][] = $item;
        }

        return json_encode($items);
    }

    protected static function loadParser($type, $app) {

        if(isset(self::$parsers[$type]))
            return self::$parsers[$type];


        $type = ucwords($type, " -_");
        $type = str_replace("-","",$type);

        $classname = "Topolis\\Bolt\\Extension\\ContentImport\\Filter\\Sections\\".$type."Section";

        if(!class_exists($classname))
            return false;

        self::$parsers[$type] = new $classname($app);

        return self::$parsers[$type];
    }

}
