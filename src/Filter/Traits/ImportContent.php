<?php


namespace Topolis\Bolt\Extension\ContentImport\Filter\Traits;


use Bolt\Application;
use Bolt\Storage\Collection\Taxonomy;
use Bolt\Storage\Entity\Content;
use Bolt\Storage\Repository;
use Topolis\FunctionLibrary\Collection;

trait ImportContent
{
    protected function importContent(Application $app, $values, $taxonomies, $config){

        $identifierField = Collection::get($config, "identifier", "guid");
        $identifier = Collection::get($values, $identifierField, sha1( serialize($values) ) );

        $contenttype = Collection::get($config, "contenttypeslug", false);
        $slugField = Collection::get($config, "slug", "title");
        $status = Collection::get($config, "status", "published");

        /* @var Repository $repo */
        $repo = $app['storage']->getRepository($contenttype);

        /* @var Content $content */
        $content = $repo->findOneBy([$identifierField => $identifier]);
        if(!$content) {
            $content = $repo->create(['contenttype' => $contenttype, 'status' => $status]);
            $content->setSlug($app["slugify"]->slugify($values[$slugField]));
            $content->setDatecreated(new \DateTime("now"));
        }

        $content->setDatechanged(new \DateTime("now"));

        foreach($values as $key => $value){
            $content->set($key, $value);
        }

        /* @var Taxonomy $taxonomy */
        $taxonomy = $app['storage']->createCollection('Bolt\Storage\Entity\Taxonomy');
        $taxonomy->setFromPost(["taxonomy" => $taxonomies], $content);
        $content->setTaxonomy($taxonomy);

        return $repo->save($content) ? $content : false;
    }
}