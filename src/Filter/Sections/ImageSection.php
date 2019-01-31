<?php

namespace Topolis\Bolt\Extension\ContentImport\Filter\Sections;

use Bolt\Extension\CND\ImageService\Image;
use Bolt\Extension\CND\ImageService\Service\FileService;
use Bolt\Extension\CND\ImageService\Service\ImageService;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class ImageSection {

    protected static $allowed = ["jpg", "jpeg", "png", "gif"];

    public function __construct($app) {
        $this->app = $app;
    }

    public function parse($input, $parameters){

        $image = false;
        $size = 0;

        foreach($input["image"] as $variant){

            $thisSize = $variant["width"] * $variant["height"];

            if($thisSize <= $size)
                continue;

            $image = $variant;
            $size = $thisSize;
        }

        if(!$image)
            return -1;

        $imageurl = $image["url"];
        $urlparts = parse_url($imageurl);
        $imageid = md5($imageurl);

        $path = $urlparts["path"];
        $path = explode(".",$path);
        $imageext = array_pop($path);

        if(!in_array($imageext, ["jpg", "gif", "png"]))
            return -2;

        /* @var ImageService $imageService */
        $imageService = $this->app["cnd.image-service.image"];
        /* @var FileService $fileService */
        $fileService = $this->app["cnd.image-service.file"];

        if (isset($input["title"]))
            $title = $input["title"] ? $input["title"] : basename($urlparts["path"]);
        else
            $title = basename($urlparts["path"]);

        if (isset($input["description"]))
            $description = $input["description"] ? $input["description"] : '';
        else
            $description = '';

        if (isset($input["alt"]))
            $alt = $input["alt"] ? $input["alt"] : $input["description"];
        else
            $alt = $description;

        if (isset($input["copyright"]))
            $copyright = $input["copyright"] ? $input["copyright"] : '';
        else
            $copyright = '';

        $image = Image::create([
            "id" => $imageid,
            "service" => isset($parameters["service"]) ? $parameters["service"] : "content",
            "status" => "new",
            "attributes" => [
                "title" => $title,
                "description" => $description,
                "copyright" => $copyright,
                "alt" => $alt
            ]
        ]);

        $fileService->setFileUrl($imageid, $imageid.".".$imageext, $imageurl);

        $result = $imageService->imageProcess([$image], $messages);

        if(!$result) {
            echo "Image upload failed\n";
            print_r($messages);
            return -3;
        }

        return [
            "type" => "imageservice",
            "data" => [
                "items" => $result,
            ]
        ];
    }

}