<?php

namespace Topolis\Bolt\Extension\ContentImport\Filter;

use Exception;
use Silex\Application;
use Topolis\Bolt\Extension\ContentImport\IFilter;

class ArticleImage implements IFilter {

    public static function filter($input, $parameters, Application $app, &$values, &$source){

        if ($source['content']['sections'][0]['type'] == 'image') {
            /* if the first section contains an image, use first section image */
            $image_data = $source['content']['sections'][0]['image'];
            array_shift($source['content']['sections']);
        }
        else {
            /* otherwise use teaser image */
            $image_data = $source['teaser']['media']['image'];
        }

        if (!$image_data)
            return false;

        foreach ($image_data as $image) {
            if ($image['custom']['sizeAlias'] == 'generic_large' && $image['width'] >= $image['height']) {
                $url = $image['url'];
                break;
            }
        }

        if (!$url)
            return false;

        return ImportImageservice::filter($url, $parameters, $app, $values, $source);
    }

}