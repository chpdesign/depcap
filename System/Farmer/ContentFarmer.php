<?php
namespace ComposerPack\System\Farmer;

use ComposerPack\Module\Language\Language;

class ContentFarmer extends FarmerInterface
{

    public function farmer($content = "") {
        //return $content;
        $html = \phpQuery::newDocumentHTML($content);

        foreach ($html['[data-model][data-field]'] as $domelem) {

            $field = pq($domelem)->attr('data-field');
            $where = pq($domelem)->attr('data-where');

            $model = pq($domelem)->attr('data-model');
            $module = strtolower($model);

            if(!empty($module) && !empty($field)) {
                if ($module == 'lang') {
                    $lang = new Language($field);
                    $l = !empty($where) ? $where : Language::language();
                    if($lang->isnew()) {
                        $h = pq($domelem)->html();
                        $lang[$l] = trim($h);
                        $lang->save();
                    } else {
                        $h = $lang[$l];
                    }
                    pq($domelem)->html($h);
                } else if(!empty($where)) {
                    $class = "ComposerPack\\Module\\".$model."\\".$model;
                    $cls = new $class();
                    $where = json_decode($where, true);
                    $model = $cls->where($where)->first();

                    if (empty($model)) {
                        $model = new $class($where);
                        $h = pq($domelem)->html();

                        $fields = explode(".",$field);
                        $f = array_pop($fields);
                        $modelField = $model;
                        foreach ($fields as $field) {
                            $modelField = $modelField[$field];
                        }
                        $modelField[$f] = $h;
                        $model->save();

                    } else {
                        $fields = explode(".",$field);
                        $h = $model;
                        foreach ($fields as $field) {
                            $h = $h[$field];
                        }
                        $h = trim($h);
                    }
                    if (!empty($model)) {
                        pq($domelem)->html($h);
                    }
                }

                //if(!(!$_SESSION['user']->isNew() && $_SESSION['user']->hasPermission(new Perm($module.'.write'))))
                {
                    //pq($domelem)->removeAttr('data-where');
                    //pq($domelem)->removeAttr('data-model');
                    //pq($domelem)->removeAttr('data-field');
                }
            }
        }
        return $html->htmlOuter();
    }

    private static function getArray($array)
    {
        if(is_array($array))
        {
            $array = reset($array);
            return self::getArray($array);
        }
        else
        {
            return $array;
        }
    }

}