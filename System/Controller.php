<?php
namespace ComposerPack\System;

/**
 * Az oldalak alapköve ez az az osztály amiből az oldalaknak ajánlott, hogy származzanak. Enélkül holt oldalak jöhetnek létre.
 * @author Nagy Gergely info@nagygergely.eu
 * @version 0.2
 *
 */
class Controller implements RenderAbleInterface
{
    /**
     * Oldal template származtat az indexből
     * @var string
     */
    public $template = "index";

    /**
     * A keresendő page
     * @var string
     */
    private $__action = "";

    /**
     * A megkapott paraméterek
     * @var string
     */
    public $parameters = array();

    /**
     * Böngésző osztálya
     * @var Browser
     */
    public static $browser;
    /**
     * This is the page it self.
     * @var Controller
     */
    public static $self;

    public static function getCurrentInstance()
    {
        return self::$self;
    }

    public static $ACTIVE_URL = '';

    const DEFAULT_CONTROLLER = 'Index';
    const DEFAULT_ACTION = 'index';

    public function __construct($gets = array(), $action = null)
    {
        self::$self = $this;
        $this->parameters = $gets;
        /*$class = get_called_class();
        $class = explode('\\', $class);
        $class = array_shift($class);
        $this->__self = $class;*/
        //$this->__self = mb_ucfirst( (!empty($gets[0]) && isset($gets[0])) ? $gets[0] : Controller::DEFAULT_PAGE );
        $this->__action = !empty($action) ? $action : Controller::DEFAULT_ACTION;
        if(empty($this->template))
            $this->template = $this->__action;
        //unset($this->parameters[0]);
        //unset($this->parameters[1]);
        $this->parameters = array_values($this->parameters);
    }

    public function setAction($action)
    {
        $this->__action = $action;
    }

    public function getAction()
    {
        return $this->__action;
    }

    public function __before(){

    }

    public function __after(){

    }

    /**
     * Hiba oldal generálására szolgáló alap metódus
     * @param $code int
     */
    public function actionError($code = 404)
    {
        switch($code)
        {
            default:
            case 404:
                $this->template = new Template('404');
                break;
            case 403:
                $this->template = new Template('403');
                break;
        }
    }

    public function __set($name,$value)
    {
        $this->{$name} = $value;
    }

    public function __get($name)
    {
        return isset($this->{$name}) ? $this->{$name} : null;
    }

    public function __call($method, $args)
    {
        if ( is_callable($this->{$method}) ) {
            return call_user_func_array($this->{$method},$args);
        } else {
            return $this->actionError(404);
        }
    }

    /**
     * Az oldal kiiratásáért felel
     * @param $print boolean
     * @return string
     */
    public function render( $print = false )
    {
        ob_start();

        if($this->template instanceof Template)
        {
        }
        elseif(is_string($this->template))
        {
            $this->template = new Template($this->template);
        }
        else
        {
            $this->template = new Template($this->__action);
        }
        $_page = "action".mb_ucfirst($this->__action);

        $return = null;
            $return = call_user_func_array(array($this, '__before'), $this->parameters);
        if($return instanceof Controller)
        {
            return $return->render();
        }
        else if($return !== false)
            $return = call_user_func_array(array($this, $_page), $this->parameters);
        if($return instanceof Controller)
        {
            return $return->render();
        }
        else if($return !== false)
            $return = call_user_func_array(array($this, '__after'), $this->parameters);
        if($return instanceof Controller)
        {
            return $return->render();
        }

        if($this->template instanceof TEMPLATE)
        {
        }
        elseif(is_string($this->template))
        {
            $this->template = new Template($this->template);
        }
        else
        {
            $this->template = new Template($this->__action);
        }

        echo $this->template;
        $rendered = ob_get_clean();
        if( $print ) {
            echo $rendered;
            return '';
        }
        return $rendered;
    }

    public function __toString()
    {
        $output = '';
        try
        {
            $output = $this->render();
        }
        catch(\Exception $e)
        {
            return Settings::whoops()->handleException($e);
            //$output = '';
            //$output .= $e->getMessage();
            //$output .= PHP_EOL;
            //$output .= $e->getTraceAsString();
        }
        return $output;
    }

    public static function urlMake($url, $urls = [])
    {
        foreach($urls as $key => $url_arg)
        {
            $url = preg_replace('#'.$key.'#', $url_arg, $url,-1,$count);
        }
        return $url;
    }

    public static function isAjax()
    {
        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            return true;
        }
        return false;
    }

}