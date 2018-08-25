<?php
namespace ComposerPack\Controller;

use ComposerPack\Module\Language\Language;
use ComposerPack\System\Run;

class AdminController extends DefaultController
{

    public function actionInlineedit()
    {
        $this->template = 'inlineedit.js';
        Run::getCurrentInstance()->getSettings()->setFarmers([]);
    }

    public function actionSave()
    {
        $json = array();
        if(!empty($_POST['model']) && !empty($_POST['field']) && isset($_POST['value']))
        {
            if($_POST['model'] == 'lang')
            {
                $lang = new Language($_POST['field']);
                $l = !empty($_POST['where']) ? $_POST['where'] : Language::language();
                $lang[$l] = trim($_POST['value']);
                $lang->save();
                $json['value'] = $lang[$l];
            }
            else if(!empty($_POST['where']))
            {
                $model = $_POST['model'];
                $class = "ComposerPack\\Module\\".$model."\\".$model;
                $cls = new $class();
                $where = json_decode($_POST['where'], true);
                $model = $cls->where($where)->first();
                if(empty($model))
                {
                    $model = new $class($where);
                }
                if(!empty($model))
                {
                    $field = $_POST['field'];
                    $fields = explode(".",$field);
                    $f = array_pop($fields);
                    $modelField = $model;
                    foreach ($fields as $field) {
                        $modelField = $modelField[$field];
                    }
                    $modelField[$f] = $_POST['value'];
                    $model->save();
                    $json['value'] = $modelField[$f];
                }
            }
        }
        echo json_encode($json);
        die();
    }

}