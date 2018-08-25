<?php
namespace ComposerPack\System\ORM\Fields;

class Multipolygon extends DefaultField {

    public function column()
    {
        $key = $this->column;
        return [$key => "ST_AsGeoJSON(`".$key."`)"];
    }

    public function read()
    {
        return json_decode($this->value, true)['coordinates'];
    }

    public function write($save = true)
    {
        $v = ["geometry" => ["type" => "MultiPolygon", "coordinates" => $this->value]];
        return "ST_AsText(ST_GeomFromGeoJSON('".json_encode($v)."'))";
    }

}