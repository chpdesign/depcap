<?php
namespace ComposerPack\Controller;

use ComposerPack\Module\Contact\Contact;
use ComposerPack\Module\User\User;
use ComposerPack\System\Controller;
use ComposerPack\System\Email;
use ComposerPack\System\Response;
use ComposerPack\System\Url;

/**
 * Class IndexController
 * @package ComposerPack\Controller
 */
class IndexController extends DefaultController {

    /**
     * Főoldal vagyis a hír lista oldal betöltése
     */
    public function actionIndex()
    {
        $this->template->js->set($this->theme.'/js/index.js');

        $this->template->content = $this->template('index');

    }

    public function actionLogin()
    {
        if(!empty($_POST))
        {
            if(isset($_POST['email']) && isset($_POST['password']))
            {
                $user = new User();
                $user['email'] = $_POST['email'];
                $user['password'] = $_POST['password'];
                $user = $user->where("`email` = '".$user['email']."' AND `password` = '".$user['password']."'")->first();
                if(!empty($user))
                {
                    $_SESSION['user'] = $user;
                    if(Controller::isAjax()) {
                        Response::json(['result' => true]);
                    }
                }
                else
                {
                    if(Controller::isAjax()) {
                        Response::json(['result' => false]);
                    }
                }
            }
        }
    }

    public function actionLogout()
    {
        if(isset($_SESSION['user']))
            unset($_SESSION['user']);
        if(Controller::isAjax()) {
            Response::json(['result' => true]);
        } else {
            Url::goBack();
        }
    }

    public function actionContact()
    {

        $this->template->content = $this->template('contact');

        if(!empty($_POST))
        {
            $data = $_POST;
            if(
                filter_var($data['email'], FILTER_VALIDATE_EMAIL)
                && !empty($data['name'])
                && !empty($data['message'])
            )
            {

                $contact = new Contact();
                $contact['email'] = $data['email'];
                $contact['name'] = $data['name'];
                $contact['message'] = $data['message'];

                $email_object = new Email();
                $email_object->from($data['email'], $data['name'])
                    ->subject("Új kapcsolat felvétel!")
                    ->message("Új kapcsolat felvétel!")
                    ->address("valami@valami.hu", "valaki");

                $json = [];
                $json['result'] = $contact->save() && $email_object->send();
                if ($json['result']) {
                    $json['message'] = lang("contact_message_send_successfull", null, array("hu" => "Köszönjük, nem sokára keresni fogjuk!", "en" => "We will contact you soon!", "de" => "Wir werden uns bald bei Ihnen melden!"));
                } else {
                    $json['message'] = lang("contact_message_send_unsuccessfull", null, array("hu" => "Valami hiba történt, kérjük próbálja meg később!", "en" => "Something went wrong, please try again later!", "de" => "Irgendetwas ist schiefgelaufen, bitte versuche es später noch einmal!"));
                }
            }
            else
            {
                $json['result'] = false;
                $json['message'] = lang("contact_message_wrong_fields", null, array("hu" => "Hibás adatokat adott meg! Minden mezőt kitöltött?", "en" => "You have entered incorrect information! Are all fields filled?", "de" => "Sie haben falsche Informationen eingegeben! Sind alle Felder ausgefüllt?"));
            }

            if(Controller::isAjax())
                Response::json($json);
        }
    }

}