<?php


namespace Topolis\Bolt\Extension\ContentImport\Filter\Traits;


use Bolt\Application;
use Bolt\Storage\Collection\Taxonomy;
use Bolt\Storage\Entity\Content;
use Bolt\Storage\Repository;
use Topolis\FunctionLibrary\Collection;

trait ImportContent {

    use ApplyFilter;

    /**
     * @param Application $app
     * @param array $item
     * @param array $channel
     * @param array $config
     * @return Content|bool
     * @throws \Exception
     */
    protected function importContent(Application $app, $item, $channel, $config){

        $contenttype = Collection::get($config, "contenttypeslug", false);
        $slugField = Collection::get($config, "slug", "title");
        $identifierField = Collection::get($config, "identifier", "guid");
        $status = Collection::get($config, "status", "published");

        $updateAllowed = Collection::get($config, "update", true);

        // Phase 1 - get identifier field only
        $fields = Collection::get($config, "fields", []);

        if(!$fields)
            return false;

        $values = $this->getValues($item, $channel, array_intersect_key($fields, [
            $identifierField => true,
            $slugField => true,
        ]), "set");
        $identifier = Collection::get($values, $identifierField, sha1( serialize($values) ) );

        /* @var Repository $repo */
        $repo = $app['storage']->getRepository($contenttype);
        /* @var Content $content */
        $content = $repo->findOneBy([$identifierField => $identifier]);

        if(!$content || $updateAllowed) {

            if (!$content) {
                $content = $repo->create(['contenttype' => $contenttype, 'status' => $status]);
                $content->setSlug($app["slugify"]->slugify($values[$slugField]));
                $content->setDatecreated(new \DateTime("now"));
            }

            // Field values (Phase 2 - all values)
            $values = $this->getValues($item, $channel, $fields, "set");

            // Taxonomies
            $fields = Collection::get($config, "taxonomies", []);
            $taxonomies = $this->getValues($item, $channel, $fields, "add");

            $content->setDatechanged(new \DateTime("now"));

            foreach ($values as $key => $value) {
                $content->set($key, $value);
            }

            /* @var Taxonomy $taxonomy */
            $taxonomy = $app['storage']->createCollection('Bolt\Storage\Entity\Taxonomy');
            $taxonomy->setFromPost(["taxonomy" => $taxonomies], $content);
            $content->setTaxonomy($taxonomy);

            $content = $repo->save($content) ? $content : false;
        }

        return $content;
    }

    /**
     * @param $item
     * @param $channel
     * @param $fields
     * @param $mode
     * @return array
     * @throws \Exception
     */
    protected function getValues($item, $channel, $fields, $mode){
        $values = [];
        foreach($fields as $field => $config){
            $config = $config + self::$defaultField;

            // Hack for the sub-cathegory to be added to the tags as well.
            $field  = ltrim($field, '_');

            $filters = Collection::get($config, "filters", []);

            $value = $this->extractValues($item, $channel, $config);
            $value = self::applyFilters($filters, $value, $this->app, $values, $item);

            switch($mode){
                case "add":
                    if(!isset($values[$field]) || !is_array($values[$field]))
                        $values[$field] = [];

                    if(is_array($value))
                        $values[$field] = array_merge($values[$field], $value);
                    else
                        $values[$field][] = $value;
                    break;
                case "set":
                    $values[$field] = $value;
                    break;
            }
        }
        return $values;
    }

    protected function extractValues($item, $channel, $config){

        $data = ["item" => $item, "channel" => $channel];

        return Collection::get($data, $config["source"], $config["default"]);
    }

}