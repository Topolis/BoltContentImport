<?php

namespace Topolis\Bolt\Extension\ContentImport\Filter;

use Exception;
use Silex\Application;
use Topolis\Bolt\Extension\ContentImport\IFilter;

class Regex implements IFilter {

    public static function filter($input, $parameters, Application $app, $values, $source){

        $result = preg_match($parameters, $input, $matches, PREG_OFFSET_CAPTURE);

        if($result)
            // Returns the named pattern Target or the first match
            return $matches['Target'] ?? $matches[1][0];

        return false;
    }

}