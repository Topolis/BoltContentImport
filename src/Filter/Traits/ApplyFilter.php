<?php

namespace Topolis\Bolt\Extension\ContentImport\Filter\Traits;

use Silex\Application;
use Topolis\Bolt\Extension\ContentImport\IFilter;

trait ApplyFilter {

    /**
     * @param $filters
     * @param $input
     * @param Application $app
     * @param $values
     * @param $source
     * @return array
     * @throws \Exception
     */
    protected static function applyFilters($filters, $input, Application $app, $values, $source) {
        $output = $input;

        // Check if this is an associative array. This allows short notation: "filters: [first, second]" beside complex with parameters "filters: [first: [a,b], second: [cd]]"
        if( !(array_keys($filters) !== range(0, count($filters) - 1)) )
            $filters = array_flip($filters);

        foreach ($filters as $filter=>$params) {
            $filterClass = "Topolis\\Bolt\\Extension\\ContentImport\\Filter\\".ucfirst($filter);

            if(!class_exists($filterClass))
                throw new \Exception("Unknown filter '".$filter."' specified");

            $Filter = new $filterClass();

            if(!$Filter instanceof IFilter)
                throw new \Exception("Invalid format class '".$filter."' specified");

            $output = $Filter::filter($output, $params, $app, $values, $source);
        }

        return $output;
    }
}