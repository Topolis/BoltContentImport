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

class Krakenapi extends BaseFormat implements IFormat {

    /* @var KrakenService $store */
    protected $store;
    protected $config;
    protected $app;

    const TTL = 60;

    public function parse($url) {
        return $this->getUrl($url);
    }

    /**
     * KrakenConnector constructor.
     * @param $config
     * @throws \Exception
     */
    public function __construct(array $config, Application $app){

        parent::__construct($config, $app);

        if(!class_exists('CND\\KrakenSDK\\Services\\KrakenService', true))
            throw new \Exception('Required composer package "cnd/kraken-sdk" not available');

        $auth = new AuthService();

        $privateKeyPath = $config['auth']['key-private'];
        $publicKeyPath  = $config['auth']['key-public'];

        $auth->setPrivateKey($privateKeyPath);
        $auth->setPublicKey($publicKeyPath);

        $this->store = new KrakenService($auth, $config['api'] ?? []);
    }

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

        return [
            "channel" => [],
            "items" => $items ?: []
        ];

    }

    /**
     * Get largest image for a desired aspect ration
     * @param Content $record
     * @param float $target
     * @return mixed
     */
    protected function getImage($record, $target = 1.5) {

        $variants = $record['teaser']['media']['image'] ?? [];

        $bestImage = false;
        $bestDiff = false;

        foreach($variants as $variant){

            if(!isset($variant["url"]) || !$variant["url"])
                continue;

            $diff = abs($target - $variant["aspectRatio"]);

            if($bestDiff === false || $diff < $bestDiff){
                $bestImage = $variant;
                $bestDiff = $diff;
            }

            return $bestImage['url'] ?? false;
        }

        return false;
    }

    /**
     * @param $filters
     * @param int $limit
     * @param int $offset
     * @param array $order
     * @return array|mixed
     * @throws \CND\KrakenSDK\Exception
     */
    protected function requestKraken($filters, $limit = 20, $offset = 0, $order = []){
        $cacheKey = md5(serialize([$filters,$limit,$offset,$order]));

        /* @var \Doctrine\Common\Cache\Cache $cache */
        $cache = $this->container['cache'];

        if($cache->contains($cacheKey))
            return $cache->fetch($cacheKey);


        $result = $this->store->findBy($filters, $limit, $offset, $order);

        $cache->save($cacheKey, $result, self::TTL);

        return $result;
    }
}