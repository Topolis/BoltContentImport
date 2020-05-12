<?php

namespace Topolis\Bolt\Extension\ContentImport\Filter;

use Exception;
use Silex\Application;
use Topolis\Bolt\Extension\ContentImport\IFilter;

class Date implements IFilter {

    public static function filter($input, $parameters, Application $app, $values, $source){

        $format = $parameters['format'] ?? 'Y-m-d H:i:s';
        return date($format, strtotime($input));
    }

}