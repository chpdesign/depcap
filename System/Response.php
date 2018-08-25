<?php
namespace ComposerPack\System;

class Response
{

    public static $exitOnJson = true;

    /**
     * @param array $data
     *
     * @return string
     */
    public static function json(array $data)
    {
        if(self::$exitOnJson === true)
            header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        if(self::$exitOnJson === true)
            exit();
    }

}