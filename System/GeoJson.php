<?php
namespace ComposerPack\System;

class GeoJson {

    public static function toPoint($data)
    {
        return [
            'type' => 'Feature',
            'properties' => [
                'name' => $data['name'],
            ],
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [(double) $data['lon'], (double) $data['lat']]
            ]
        ];
    }

    public static function importCounty()
    {
        $counties = [];
        $dir = Settings::get("base_dir")."/public/geo/hungary/district/";
        $files = scandir($dir);
        foreach($files as $file)
        {
            $sql = Sql::getDefaultDb();
            if(in_array($file,[".",".."]))
                continue;
            $county = json_decode(file_get_contents($dir.$file), true);
            $sql->query("INSERT INTO `region` (`name`,`type`,`coordinates`) SELECT '".$county['features'][0]['properties']['name']."' AS `name`, 'subregion' AS `type`, ST_GeomFromGeoJSON('".json_encode($county['features'][0])."') AS `coordinates`");
            $counties[] = $county;
        }

        $counties = [];
        $dir = Settings::get("base_dir")."/public/geo/hungary/county/";
        $files = scandir($dir);
        foreach($files as $file)
        {
            $sql = Sql::getDefaultDb();
            if(in_array($file,[".",".."]))
                continue;
            $county = json_decode(file_get_contents($dir.$file), true);
            $sql->query("INSERT INTO `region` (`name`,`type`,`coordinates`) SELECT '".$county['features'][0]['properties']['name']."' AS `name`, 'county' AS `type`, ST_GeomFromGeoJSON('".json_encode($county['features'][0])."') AS `coordinates`");
            $counties[] = $county;
        }

        $sql->query("UPDATE `region` SET `name` = REPLACE(`name`, 'járás', 'kistérség')");
    }

}