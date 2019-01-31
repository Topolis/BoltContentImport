<?php

namespace Topolis\Bolt\Extension\ContentImport\Filter;

use Exception;
use Silex\Application;
use Topolis\Bolt\Extension\ContentImport\IFilter;

class AggregationInfo implements IFilter {

    public static function filter($input, $parameters, Application $app, &$values, &$source){

        if (in_array($parameters['value'],$input)) {
            return true;
        }
        return false;
    }

}