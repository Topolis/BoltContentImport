<?php

namespace Topolis\Bolt\Extension\ContentImport\Filter;

use Exception;
use Silex\Application;
use Topolis\Bolt\Extension\ContentImport\IFilter;

class Slugify implements IFilter {

    public static function filter($input, $parameters, Application $app, $values, $source){

        if(is_array($input)){
            foreach ($input as $key => $val) {
                $input[$key] = $app["slugify"]->slugify($input[$key]);
            }
        } else {
            $input = $app["slugify"]->slugify($input);
        }

        return $input;
    }

}