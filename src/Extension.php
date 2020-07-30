<?php

namespace Topolis\Bolt\Extension\ContentImport;

use Bolt\Events\CronEvent;
use Bolt\Events\CronEvents;
use Bolt\Extension\SimpleExtension;
use Pimple as Container;
use Silex\Application;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Topolis\Bolt\Extension\ContentImport\Command\Import;
use Topolis\Bolt\Extension\ContentImport\Command\ImportIterative;
use Topolis\Bolt\Extension\ContentImport\Command\Purge;
use Topolis\Bolt\Extension\ContentImport\Service\Importer;

class Extension extends SimpleExtension {

    const EXTID = "topolis.contentimport";

    static $cronMap = [
        "hourly"  => CronEvents::CRON_HOURLY,
        "daily"   => CronEvents::CRON_DAILY,
        "weekly"  => CronEvents::CRON_WEEKLY,
        "monthly" => CronEvents::CRON_MONTHLY,
        "yearly"  => CronEvents::CRON_YEARLY,
    ];

    /**
     * {@inheritdoc}
     */
    protected function registerNutCommands(Container $container)
    {
        return [
            new ImportIterative($container),
            new Import($container),
            new Purge($container),
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function subscribe(EventDispatcherInterface $dispatcher)
    {
        $dispatcher->addListener(CronEvents::CRON_HOURLY, [$this, 'cron']);
        $dispatcher->addListener(CronEvents::CRON_DAILY, [$this, 'cron']);
        $dispatcher->addListener(CronEvents::CRON_WEEKLY, [$this, 'cron']);
        $dispatcher->addListener(CronEvents::CRON_MONTHLY, [$this, 'cron']);
        $dispatcher->addListener(CronEvents::CRON_YEARLY, [$this, 'cron']);
    }

    public function cron(CronEvent $event, $name)
    {
        // Execute import tasks
        $imports = $this->container[self::EXTID.'.importer']->getCronTasks();

        foreach($imports as $source => $interval){
            if(isset(self::$cronMap[$interval]) && self::$cronMap[$interval] == $name){
                $this->container[self::EXTID.'.importer']->import($source, $event->output);
                $this->container[self::EXTID.'.importer']->purge($source, $event->output);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function registerServices(Application $app)
    {
        $app[self::EXTID.'.importer'] = $app->share(
            function ($app) {
                return new Importer($app, $this->getConfig());
            }
        );
    }
}