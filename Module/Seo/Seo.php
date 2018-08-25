<?php
namespace ComposerPack\Module\Seo;

use ComposerPack\Module\Language\Language;
use ComposerPack\System\ORM;

class Seo extends ORM
{

    protected $primary_key = array();

    protected $default_primary_key = array('url');

    protected $table = 'seo';

    public function __toString()
    {
        return $this["url"].'';
    }

    /**
     * @param $model ORM
     * @return Seo|bool|mixed|null
     */
    public static function where_model($model)
    {
        if(!$model->isNew()) {
            $id = json_encode($model->getPrimaryKeys());
            $class = str_replace('\\', '\\\\', get_class($model));
            $seo = new Seo();
            $seo = $seo->where("model", $class."::".$id)->first();
            if(empty($seo))
                return new Seo();
            return $seo;
        }
        return new Seo();
    }

    /**
     * @param $url
     * @param null $lang
     * @param null $model
     * @return Seo
     */
    public static function where_url($url, $lang = null, $model = null)
    {
        if (is_null($lang)) {
            $lang = Language::language();
        }
        if (is_array($url)) {
            $id = "";
            $seo = new Seo();
            $tlang = new Language();
            $tlang = $tlang->where('what', 'seo')->where_regexp('id', '^seo_url');
            foreach ($url as $lang => $value) {
                $tlang->where($lang, $value);
            }
            $tlang = $tlang->first(false);
            if(!empty($tlang)) {
                if ($id == "") $id = $tlang['id'];
            }
            if ($id != "") {
                $_seo = new Seo();
                $_seo->where("url", $id);
                if ($model != null) {
                    if (!is_string($model))
                        $model = str_replace("\\", "\\\\", get_class($model));
                    $_seo->where_regexp("model", "^".str_replace("\\", "\\\\", $model));
                }
                if ($_seo->count() == 1) {
                    $seo = $_seo->first();
                }
            }
            return $seo;
        } else {
            $seo = new Seo();
            $tlang = new Language();
            //$tlang = $tlang->where('what','seo')->where($lang,$url)->where_regexp('id','^seo_url')->result(false);
            $tlang = $tlang->where('what', 'seo')->where($lang, $url)->where_regexp('id', '^seo_url');
            $tlang = $tlang->first(false);
            if (!empty($tlang)) {
                $_seo = new Seo();
                $_seo->where("url", $tlang['id']);
                if ($model != null) {
                    if (!is_string($model))
                        $model = str_replace("\\", "\\\\", get_class($model));
                    $_seo->where_regexp("model", "^".str_replace("\\", "\\\\", $model));
                }
                if ($_seo->count() == 1) {
                    $seo = $_seo->first();
                }
            }
            return $seo;
        }
    }

    public function read_meta($value)
    {
        if (is_array($value)) return $value;
        $value = json_decode($value, true);
        if ($value == null)
            $value = array();
        return $value;
    }

    public function write_meta($value)
    {
        if (!is_array($value)) return $value;
        $value = json_encode($value);
        return $value;
    }

    public function read_model($value)
    {
        $model = $value;
        if(!is_null($model) && is_string($model)) {
            $s = explode("::", $model);
            $model = array_shift($s);
            $id = json_decode(implode("::", $s), true);
            $this['model'] = new $model($id);
        }
        else
        {
            $this['model'] = $value;
        }
        return $this['model'];
    }

    public function write_model($value)
    {
        if(!is_null($value) && !is_string($value)) {
            if($value->isnew())
            {
                return get_class($value);
            }
            return get_class($value)."::".json_encode($value->getPrimaryKeys());
        }
        return null;
    }

}
