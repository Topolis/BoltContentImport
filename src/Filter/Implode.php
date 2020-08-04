<?php

namespace Topolis\Bolt\Extension\ContentImport\Filter;

use Exception;
use Silex\Application;
use Topolis\Bolt\Extension\ContentImport\IFilter;

class Implode implements IFilter {

    public static function filter($input, $parameters, Application $app, $values, $source){
       return implode($parameters ?: ' ',  $input);
    }

}