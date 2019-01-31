<?php

namespace Topolis\Bolt\Extension\ContentImport\Filter;

use Exception;
use Silex\Application;
use Topolis\Bolt\Extension\ContentImport\IFilter;
use Topolis\FunctionLibrary\Collection;

class Concat implements IFilter {

    public static function filter($input, $parameters, Application $app, &$values, &$source){
        $prefix  = isset($parameters[0]) ? $parameters[0] : "";
        $postfix = isset($parameters[1]) ? $parameters[1] : "";

        return $prefix.$input.$postfix;
    }

}