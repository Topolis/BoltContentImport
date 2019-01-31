<?php

namespace Topolis\Bolt\Extension\ContentImport\Filter;

use Exception;
use GuzzleHttp\Exception\ClientException;
use Silex\Application;
use Topolis\Bolt\Extension\ContentImport\IFilter;
use Topolis\FunctionLibrary\Path;

class ImportImage implements IFilter {

    protected static $allowed = ["jpg", "jpeg", "png", "gif"];

    public static function filter($url, $parameters, Application $app, &$values, &$source){

        if(!$url)
            return false;

        $info = parse_url($url);

        if(!$info)
            return false;

        $extension = strtolower(substr($info["path"], strrpos($info["path"], '.') + 1));
        $filename = "import/".sha1($url).".".$extension;

        if(!in_array($extension, self::$allowed))
            return false;

        if(!$app["filesystem"]->has("files://".$filename)){

            $client = new \GuzzleHttp\Client();
            $res = $client->request('GET', $url, ['http_errors' => false]);

            if(!$res || $res->getStatusCode() != 200)
                return false;

            $app["filesystem"]->putStream("files://".$filename, $res->getBody());
        }

        return [
            "file" => $filename
        ];
    }

}