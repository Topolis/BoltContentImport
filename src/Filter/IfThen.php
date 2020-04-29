<?php

namespace Topolis\Bolt\Extension\ContentImport\Filter;

use Exception;
use Silex\Application;
use Topolis\Bolt\Extension\ContentImport\Filter\Traits\ApplyFilter;
use Topolis\Bolt\Extension\ContentImport\IFilter;
use Topolis\FunctionLibrary\Collection;

class IfThen implements IFilter {

    use ApplyFilter;

    public static function filter($input, $parameters, Application $app, $values, $source)
    {
        $output = $input;

        foreach ($parameters as $param) {
            $field = $param['field'] ?? false;

            // Digs deeper in to the field or use the field value for the check
            $value = Collection::get($output, $field, $output);
            $state = true;

            // Made like this to exten with the other Checks ne, gt, lt ...
            if (isset($param['eq']) && $param['eq'] !== $value) {
                $state = false;
            }

            if (isset($param['ne']) && $param['ne'] === $value) {
                $state = false;
            }

            if (isset($param['lt']) && $param['lt'] > $value) {
                $state = false;
            }

            if (isset($param['gt']) && $param['gt'] < $value) {
                $state = false;
            }

            $then = $param['then'] ?? '';

            if ($state && is_array($then) ) {
                $value = Collection::get($source, $then['source'] ?? ''  , $input);
                $output = self::applyFilters($then['filters'], $value, $app, $values, $source);
            }

            if ($state && !is_array($then) ) {
                $output =  $then;
            }

        }

        return $output;

    }
}