<?php

namespace Topolis\Bolt\Extension\ContentImport\Filter\Sections;

use Bolt\Extension\CND\ImageService\Image;
use Bolt\Extension\CND\ImageService\Service\FileService;
use Bolt\Extension\CND\ImageService\Service\ImageService;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Topolis\FunctionLibrary\Collection;

class ImageSection {

    protected static $allowed = ["jpg", "jpeg", "png", "gif"];

    public function __construct($app) {
        $this->app = $app;
    }

    public function parse($input){

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
        $path = explode(".",$urlparts["path"]);
        $imageext = array_pop($path);

        if(!in_array($imageext, ["jpg", "gif", "png"]))
            return -2;

        /* @var ImageService $imageService */
        $imageService = $this->app["cnd.image-service.image"];
        /* @var FileService $fileService */
        $fileService = $this->app["cnd.image-service.file"];

        $image = Image::create([
            "id" => $imageid,
            "service" => isset($parameters["service"]) ? $parameters["service"] : "content",
            "status" => "new",
            "attributes" => [
                "title" => Collection::get($input,"title", basename($urlparts["path"])),
                "description" => $input["description"] ?? '',
                "copyright" => $input["copyright"] ?? '',
                "alt" => $input["alt"] ?? ''
            ]
        ]);

        $fileService->setFileUrl($imageid, $imageid.".".$imageext, $imageurl);

        $result = $imageService->imageProcess([$image], $messages);

        if(!$result) {
            echo "Image upload failed";
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