<?php
namespace ComposerPack\Controller;

use ComposerPack\Module\Currency\Currency;
use ComposerPack\System\Controller;
use ComposerPack\System\Settings;
use ComposerPack\System\Template;
use ComposerPack\System\Url;

/**
 * Class DefaultController
 * @package ComposerPack\Controller
 */
class DefaultController extends Controller {

    public $template = 'template';

    public $theme = 'depcap';

    public $breadcrumbs = [];

    public static $urls = [];

    function __before()
    {
        if(isset($_GET['currency'])){
            $currency = new Currency();
            $currency = $currency->where("code", $_GET['currency'])->first();
            Currency::currency($currency);
            if(isset($_SERVER['HTTP_REFERER']))
                Url::navigate($_SERVER['HTTP_REFERER'],true);
            else {
                $get = $_GET;
                if(isset($get['currency']))
                    unset($get['currency']);
                $get = http_build_query($get);
                Url::navigate(Settings::get("base_link").(!empty($get) ? '?'.$get : ''), true);
            }
        }
        parent::__before();
        $this->template = $this->template('template');
    }

    public function __after()
    {

    }

    public function template($template)
    {
        return new Template($this->theme.'/'.$template);
    }

}