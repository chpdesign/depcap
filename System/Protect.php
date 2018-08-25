<?php
namespace ComposerPack\System;

class Protect
{
    public static function POST_Protect($node = null){
        if($node == null) $node = $_POST;
        foreach($node as $key => $value){
            if(is_array($value)){
                $node[$key] = Protect::POST_Protect($value);
            }else{
                $node[$key] = htmlspecialchars(addslashes($value));
            }
        }
        if($node != null){
            return $node;
        }else{
            $_POST = $node;
        }
    }
    public static function GET_Protect($node = null){
        if($node == null) $node = $_GET;
        foreach($node as $key => $value){
            if(is_array($value)){
                $node[$key] = Protect::GET_Protect($value);
            }else{
                $node[$key] = htmlspecialchars(addslashes($value));
            }
        }
        if($node != null){
            return $node;
        }else{
            $_GET = $node;
        }
    }
}