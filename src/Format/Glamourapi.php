<?php

namespace Topolis\Bolt\Extension\ContentImport\Format;

use Silex\Application;
use Bolt\Legacy\Content;
use CND\KrakenSDK\Services\AuthService;
use CND\KrakenSDK\Services\KrakenService;
use Topolis\Bolt\Extension\ContentImport\Format\BaseFormat;
use Bolt\Extension\CND\TwigLibrary\Extensions\CollectionLibrary;
use Topolis\Bolt\Extension\ContentImport\IFormat;
use Topolis\FunctionLibrary\Collection;

class Glamourapi extends Krakenapi {

    /* @var KrakenService $store */
    protected $store;
    protected $config;
    protected $app;

    const TTL = 60;

    /**
     * @inheritdoc
     * @throws \CND\KrakenSDK\Exception
     * @throws \Exception
     */
    public function getUrl($url) {

        $query = Collection::get($this->config, "api.query", []);
        // Basic Query
        $query = $query + [
                'filter' => [],
                'limit' => 20,
                'offset' => 0,
                'order' => [],// ['control.publishDate' => true],
            ];

        $items = $this->requestKraken($query['filter'], $query['limit'], $query['offset'], $query['order']);

        $items = $items ?: [];

        // General fixes
        foreach($items as $key => $item) {

            // glamour imports the Highlight image as the first section in kraken
            // that is why whe have to adjust the data before pass it to the filters

            $sections = Collection::get($item, 'content.sections', []);
            $imageFound = false;

            // Fix section errors that are specific to GLAMOUR import from EZ
            foreach ($sections as $i => $section) {

                if($i===0 && $section['type'] === 'image') {
                    $item['content']['image'] = $section;
                    $imageFound = true;
                    continue;
                }

                // Fix glamour embed imports SSL Problem
                if($section['embed'] ?? false) {
                    $section['embed'] = str_replace('http://','https://', $section['embed']);
                }

                $sections[$i] = $section;
            }

            // Remove First image as its used as a Highlight image
            if($imageFound) {
                array_shift($sections);
            }

            // Update the Sections
            $item['content']['sections'] = $sections;

            // Add Subcategory as Tag
            $subCat = $item['meta']['subcategory'] ?? false;
            if($subCat) {
                $taxonomy = $this->app['storage']->createCollection('Bolt\Storage\Entity\Taxonomy');
                $subCatName = $taxonomy->config['subcategories']['options'][$subCat] ?? false;

                if($subCatName)
                    $item['meta']['tags'][] = $subCatName;
            }

            $items[$key] = $item;
        }

        return [
            "channel" => [],
            "items" => $items
        ];

    }

}