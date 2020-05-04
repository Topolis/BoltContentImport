<?php

namespace Topolis\Bolt\Extension\ContentImport\Filter;

use Bolt\Extension\CND\ImageService\Image;
use Bolt\Extension\CND\ImageService\Service\FileService;
use Bolt\Extension\CND\ImageService\Service\ImageService;
use Exception;
use GuzzleHttp\Exception\ClientException;
use Silex\Application;
use Topolis\Bolt\Extension\ContentImport\IFilter;
use Topolis\FunctionLibrary\Collection;
use Topolis\FunctionLibrary\Path;

class ImportImageservice implements IFilter {

    protected static $allowed = ["jpg", "jpeg", "png", "gif"];

    public static function filter($url, $parameters, Application $app, $values, $source){

        $urlparts = parse_url($url);
        $imageid = md5($url);
        $parts = explode(".", $urlparts["path"]);
        $imageext = array_pop($parts);

        if(!in_array($imageext, ["jpg", "gif", "png"]))
            return false;

        /* @var ImageService $imageService */
        $imageService = $app["cnd.image-service.image"];
        /* @var FileService $fileService */
        $fileService = $app["cnd.image-service.file"];

        // Get attrubutes if configured
        $attributes = [];
        if(isset($parameters["map"])) {
            foreach ($parameters["map"] as $target => $key) {
                $val = Collection::get($source, $key, "");
                Collection::set($attributes, $target, $val);
            }
        }

        $image = Image::create([
            "id" => $imageid,
            "service" => isset($parameters["service"]) ? $parameters["service"] : "content",
            "status" => "new",
            "attributes" => $attributes
        ]);

        $fileService->setFileUrl($imageid, $imageid.".".$imageext, $url);

        $result = $imageService->imageProcess([$image], $messages);

        if(!$result) {
            echo "Image upload failed";
            print_r($messages);
        }

        return [
            "items" => $result
        ];
    }

}