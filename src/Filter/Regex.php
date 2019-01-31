<?php

namespace Topolis\Bolt\Extension\ContentImport\Filter;

use Exception;
use Silex\Application;
use Topolis\Bolt\Extension\ContentImport\IFilter;

class Regex implements IFilter {

    public static function filter($input, $parameters, Application $app, &$values, &$source){

        $result = preg_match($parameters, $input, $matches, PREG_OFFSET_CAPTURE);

        return $result ? $matches[1][0] : false;
    }

}