<?php

namespace Topolis\Bolt\Extension\ContentImport\Filter;

use Exception;
use Silex\Application;
use Topolis\Bolt\Extension\ContentImport\IFilter;

class FindImageHtml implements IFilter {

    public static function filter($input, $parameters, Application $app, &$values, &$source){

        $found = preg_match('/<img[^>]+src="([^"]+)"/', $input, $matches);
        return $found ? $matches[1] : false;
    }

}