<?php

namespace Topolis\Bolt\Extension\ContentImport\Filter;

use Exception;
use Silex\Application;
use Topolis\Bolt\Extension\ContentImport\IFilter;

class Explode implements IFilter {

    public static function filter($input, $parameters, Application $app, &$values, &$source){
        $split     = isset($parameters[0]) ? $parameters[0] : " ";
        $index     = isset($parameters[1]) ? $parameters[1] : 0;
        $expectmin = isset($parameters[2]) ? $parameters[2] : 2;
        $errorall  = isset($parameters[3]) ? $parameters[3] : true;

        $exploded = self::explode($split, $input);

        if($index === false)
            return $exploded;

        if(count($exploded) < $expectmin)
            return $errorall ? $input : false;

        return $exploded[$index] ?: false;
    }

    protected static function explode($seperators, $input){
        $seperators = is_array($seperators) ? $seperators : [$seperators];

        $results = [$input];

        foreach($seperators as $seperator){
            $offset = 0;
            foreach($results as $idx => $result){
                $exploded = explode($seperator, $result);
                array_splice($results, $idx+$offset, 1, $exploded);
                $offset = $offset + count($exploded)-1;
            }
        }

        return $results;
    }

}