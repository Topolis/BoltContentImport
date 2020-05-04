<?php


namespace Topolis\Bolt\Extension\ContentImport\Filter\Sections;


class SpotifyEpisodeSection
{
    public function parse($input, $parameters){

        return [
            "type" => "oembed",
            "data" => [
                "type" => "spotify",
                "url" => "https://open.spotify.com/embed-podcast/episode/". $input['id'],
                "html" => $input["embed"]
            ]
        ];
    }
}