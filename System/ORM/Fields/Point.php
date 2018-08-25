<?php
namespace ComposerPack\System\ORM\Fields;

class Point extends DefaultField {

    public function column()
    {
        $key = $this->column;
        return [$key => $key, $key."_lat" => "X(`".$key."`)", $key."_lng" => "Y(`".$key."`)"];
    }

    public function read()
    {
        $key = $this->column;
        return ['lat' => $this->model[$key.'_lat'], 'lng' => $this->model[$key.'_lng']];
    }

    public function write($save = true)
    {
        if(is_array($this->value))
        {
            return "PointFromText('POINT(".implode(" ", $this->value).")')";
        }
        else
        {
            return "PointFromText('POINT(".$this->value.")')";
        }
    }

}