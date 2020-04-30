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
            $operator = $param['operator'] ?? "eq";
            $target = $param['target'];

            // Digs deeper in to the field or use the field value for the check
            $value = Collection::get($output, $field, $output);

            switch($operator) {
                case 'ne':
                    $state  = $target !== $value;
                    break;
                case 'eq':
                    $state  = $target === $value;
                    break;
                case 'lt':
                    $state  = $target > $value;
                    break;
                case 'lte':
                    $state  = $target >= $value;
                    break;
                case 'gt':
                    $state  = $target < $value;
                    break;
                case 'gte':
                    $state  = $target <= $value;
                    break;
                default:
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