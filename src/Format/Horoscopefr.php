<?php

namespace Topolis\Bolt\Extension\ContentImport\Format;

use Exception;
use Topolis\Bolt\Extension\ContentImport\IFormat;
use Silex\Application;

class Horoscopefr extends BaseFormat implements IFormat {

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

        $type = $this->app["slugify"]->slugify($this->config["type"]);

        switch($type){
            case 'horoscope-du-jour':
                break;

            case 'horoscope-de-la-semaine':
                $tomorrow->modify('monday this week');
                break;

#            case 'monatshoroskop':
#                $tomorrow->modify('first day of this month');
#                break;

#            case 'jahreshoroskop':
#                $tomorrow->modify('first day of january this year');
#                break;

            default:
                break;

        }

        $input = $this->getUrl($url);
        $array = json_decode($input, true);

        foreach ($array as $idx => $item) {
            $guid = $this->app["slugify"]->slugify($type.'-'.$item['date'].'-'.$item['zodiac sign']);
            $sign = $this->app["slugify"]->slugify($item['zodiac sign']);
            if (isset($item['headline'])) {
                $sections[$idx][] = [
                    'type' => 'headline',
                    'level' => 1,
                    'text' => $item['headline']
                ];
            }
            if (isset($item['content'])) {
                $sections[$idx][] = [
                    'type' => 'text',
                    'text' => $item['content']
                ];
            }

            $date = $item['date'];

            $filterClass = $this->baseNS."\\Filter\\Sections2Structured";
            $Filter = new $filterClass();
            $section = $Filter->filter($sections[$idx], [], $this->app, [], []);

            $items[] = [
                'guid' => $guid,
                'name' => $item['zodiac sign'],
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
