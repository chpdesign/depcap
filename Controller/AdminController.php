<?php

namespace ComposerPack\Controller;

use ComposerPack\Module\Language\Language;
use ComposerPack\Module\User\User;
use ComposerPack\System\Controller;
use ComposerPack\System\Response;
use ComposerPack\System\Session\Session;
use ComposerPack\System\Settings;
use ComposerPack\System\Url;

class AdminController extends DefaultController
{

    public function __before()
    {
        parent::__before();
        if (!Session::has('user')) {
            if (!empty($_POST)) {
                if (isset($_POST['email']) && isset($_POST['password'])) {

                    $user = new User();
                    $user = $user->where_login($_POST['email'], $_POST['password'])->first();
                    if (!empty($user)) {
                        Session::set("user", $user);
                        if (Controller::isAjax()) {
                            Response::json(['result' => true, 'message' => lang("success_login", null, array("hu" => "Sikeres bejelentkezés", "en" => "Successful login", "de" => "erfolgreicher Login"))]);
                        }
                    } else {
                        if (Controller::isAjax()) {
                            Response::json(['result' => false, 'message' => lang("success_failed", null, array("hu" => "Sikertelen bejelentkezés", "en" => "Login failed", "de" => "Anmeldung fehlgeschlagen"))]);
                        }
                    }
                }
            }
        }

        if (!Session::has('user')) {
            $this->template = $this->template('admin/login');

            $this->template->css->set('https://cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/themes/white/pace-theme-flash.css');
            $this->template->css->set('bootstrap-3.3.7/css/bootstrap.css');
            $this->template->css->set('font-awesome-4.7.0/css/font-awesome.css');

            $this->template->js->set('https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js');
            $this->template->js->set('https://cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/pace.js');
            $this->template->js->set('bootstrap-3.3.7/js/bootstrap.js');
            $this->template->js->set('js/jquery.serializejson.js');
            $this->template->js->set('js/jquery.deserializejson.js');
            $this->template->js->set('js/php.js');
            $this->template->js->set('js/jquery.ba-dotimeout.min.js');
            $this->template->js->set('https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.21.0/moment-with-locales.js');
            $this->template->js->set('js/page.js');
            $this->template->js->set('js/ordered.js');
            $this->template->js->set('adminlte-2.4.3/js/adminlte.js');

            $this->template->js['base'] = Settings::get("base_link");
            $this->template->js['url'] = url(Settings::get("base_link"));
            $this->template->js['active_url'] = Controller::$ACTIVE_URL;
            $this->template->js['lang'] = Language::language();

            $this->template->css->set('css/style.css');
            $this->template->css->set('adminlte-2.4.3/css/AdminLTE.css');
            $this->template->css->set('css/skin-depcap.css');

            return false;
        } else {

            $this->template = $this->template('admin/template');

            $this->template->css->set('https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,700');
            $this->template->css->set('https://cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/themes/white/pace-theme-flash.css');
            $this->template->css->set('font-awesome-4.7.0/css/font-awesome.css');
            $this->template->css->set('https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css');
            //$this->template->css->set('bootstrap-4.0.0/css/bootstrap.css');
            //$this->template->css->set('bootstrap-3.3.7-glyphicon/css/bootstrap.css');
            $this->template->css->set('bootstrap-3.3.7/css/bootstrap.css');
            $this->template->css->set('https://cdnjs.cloudflare.com/ajax/libs/bootstrap-social/5.1.1/bootstrap-social.css');
            $this->template->css->set('adminlte-2.4.3/css/AdminLTE.css');
            $this->template->css->set('adminlte-2.4.3/css/skins/skin-blue.css');
            $this->template->css->set('https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.2.0/jquery-confirm.min.css');

            $this->template->js->set('https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js');
            $this->template->js->set('https://cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/pace.js');
            //$this->template->js->set('bootstrap-4.0.0/js/bootstrap.bundle.js');
            $this->template->js->set('bootstrap-3.3.7/js/bootstrap.js');
            $this->template->js->set('js/jquery.serializejson.js');
            $this->template->js->set('js/jquery.deserializejson.js');
            $this->template->js->set('js/php.js');
            $this->template->js->set('js/jquery.ba-dotimeout.min.js');
            $this->template->js->set('js/ordered.js');
            $this->template->js->set('adminlte-2.4.3/js/adminlte.js');
            $this->template->js->set('https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.2.0/jquery-confirm.min.js');
            $this->template->js->set('https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.21.0/moment-with-locales.js');

            $this->template->js['base'] = Settings::get("base_link");
            $this->template->js['url'] = url(Settings::get("base_link"));
            $this->template->js['active_url'] = Controller::$ACTIVE_URL;
            $this->template->js['lang'] = Language::language();

            $urls = [];
            $urls['list'] = 'index/list/';

            $this->template->js['urls'] = $urls;
        }

    }

    public function actionLogout()
    {
        Session::remove("user");
        if(Controller::isAjax()) {
            Response::json(['result' => true]);
        } else {
            Url::goBack();
        }
    }

    public function actionIndex()
    {

    }

}