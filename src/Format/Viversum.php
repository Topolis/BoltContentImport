<?php

namespace Topolis\Bolt\Extension\ContentImport\Format;

use Exception;
use Topolis\Bolt\Extension\ContentImport\IFormat;
use Silex\Application;

class Viversum extends BaseFormat implements IFormat {

    /**
     * Viversum constructor.
     * @param Application $app
     */
    public function __construct($config, Application $app){

        parent::__construct($config,  $app);

        $ns = explode("\\", __NAMESPACE__);
        array_pop($ns);
        $this->baseNS = implode("\\", $ns);

    }

    public function parse($url){

        // append date to url
        $tomorrow = new \DateTime('tomorrow');
        $url = $url . "&calcDate=" . $tomorrow->format('Y-m-d');

        $type = $this->app["slugify"]->slugify($this->config["type"]);

        switch($type){
            case 'tageshoroskop':
                break;

            case 'wochenhoroskop':
                $tomorrow->modify('monday this week');
                break;

            case 'monatshoroskop':
            case 'beautyhoroskop':
                $tomorrow->modify('first day of this month');
                break;

            case 'jahreshoroskop':
                $tomorrow->modify('first day of january this year');
                break;

            default:
                break;

        }
        $date = $tomorrow->format('Y-m-d');

        $input = $this->getUrl($url);
        $array = json_decode($input, true);

        $items = [];
        $sections = [];
        foreach ($array["horoscope"] as $idx => $item) {
            $guid = $this->app["slugify"]->slugify($type.'-'.$array['validUntil'].'-'.$item['zodiacSign']['name']);
            $sign = $this->app["slugify"]->slugify($item['zodiacSign']['name']);

            foreach ($item['zodiacSign']['section'] as $section) {
                if (isset($section['headline'])) {
                    $sections[$idx][] = [
                        'type' => 'headline',
                        'level' => 1,
                        'text' => $section['headline']
                    ];
                }
                if (isset($section['content'])) {
                    $sections[$idx][] = [
                        'type' => 'text',
                        'text' => $section['content']
                    ];
                }
            }


            $filterClass = $this->baseNS."\\Filter\\Sections2Structured";
            $Filter = new $filterClass();
            $section = $Filter->filter($sections[$idx], [], $this->app, [], []);

            $items[] = [
                'guid' => $guid,
                'name' => $item['zodiacSign']['name'],
                'sign' => $sign,
                'section' => $section,
                'date' => $date
            ];
        }

        $result = [
            "channel" => [],
            "items" => $items
        ];

        return $result;
    }
}
