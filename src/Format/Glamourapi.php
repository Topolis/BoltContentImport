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

        // glamour imports the Highlight image as the first section in kraken
        // that is why whe have to adjust the data before pass it to the filters
        foreach($items as $key => $item) {
            $first = Collection::get($item, 'content.sections.0', false);
            if($first && $first['type'] === 'image') {
                $item['content']['image'] = $first;
                array_shift($item['content']['sections']);
                $items[$key] = $item;
            }
        }

        return [
            "channel" => [],
            "items" => $items
        ];

    }

}