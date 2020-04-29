<?php

namespace Topolis\Bolt\Extension\ContentImport;

use Silex\Application;

interface IFormat {

    /**
     * IFormat constructor.
     * @param $config
     */
    public function __construct(array $config, Application $app);

    /**
     * @param string $url
     * @return array
     */
    public function parse($url);

}
