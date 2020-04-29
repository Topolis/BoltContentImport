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

class ImportKrakenImageservice implements IFilter {

    protected static $allowed = ["jpg", "jpeg", "png", "gif"];

    public static function filter($imageObject, $parameters, Application $app, $values, $source){

        $Filter = new ImportImageservice();
        $images = Collection::get($imageObject, 'image', []);
        $maxWidth = $parameters['maxWidth'] ?? 1000;
        $aspectRatios = $parameters['aspectRatios'] ?? [];
        $aspectRatios = is_array($aspectRatios) ? $aspectRatios : [$aspectRatios];
        $items = [];
        $urls = [];

        foreach($aspectRatios as $ratio) {

            // Finds the best image for this format
            $image = self::imgSearchVariant($images, $ratio, $maxWidth, $maxWidth*$ratio);

            // Assure no duplicates Images in Result
            if(in_array($image['url'], $urls))
                continue;

            $urls[] = $image['url'];

            $result = $Filter->filter($image['url'], $parameters, $app, $values, $imageObject);
            $items += $result["items"] ?? [];
        }

        return [
            "items"=>$items
        ];
    }

    /**
     * Finds the best image version suiting given width and height constrains
     * @param array $variants
     * @param int $width
     * @param int $height
     * @return bool|mixed
     */
    public static function imgSearchVariant(array $variants, $aspectRatio=0, $width=0, $height=0) {

        $okImage = false;
        $bestImage = false;
        $bestDiff = false;
        $target = $aspectRatio ? $aspectRatio :  $width / ($height ?: 1);

        foreach($variants as $variant){

            if(!isset($variant["url"]) || !$variant["url"])
                continue;

            $diff = abs($target - $variant["aspectRatio"]);

            if($bestDiff === false || $diff < $bestDiff){
                $okImage = $variant;
                $bestImage = $width <= $variant['width'] && $height <= $variant['height'] ? $variant : $bestImage;
                $bestDiff = $diff;
            }

        }

        return $bestImage ?: $okImage;

    }


}