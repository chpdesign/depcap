<?php
namespace ComposerPack\System;

use ComposerPack\Module\Language\Language;
use ComposerPack\System\Config\Config;
use ComposerPack\System\Config\JSONConfig;
use ComposerPack\System\Session\Session;
use ComposerPack\System\Session\SessionHandler;
use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PrettyPageHandler;

class Run
{
    /**
     * @var Settings
     */
    protected $settings;
    protected $URL;
    protected $namespace = '';

    /**
     * @var Run
     */
    protected static $self = null;

    /**
     * @return Run
     */
    public static function getCurrentInstance()
    {
        return self::$self;
    }

    public static function getInstance($dir, $namespace = null)
    {
        $class = get_called_class();

        $serverName = gethostname();
        $hostConfig = $dir.'/config.'.$serverName.'.json';
        $defaultConfig = $dir.'/config.json';
        if(file_exists($hostConfig))
            $config = new JSONConfig(file_get_contents($hostConfig));
        else
            $config = new JSONConfig(file_get_contents($defaultConfig));

        self::$self = new $class($dir, $config, $namespace = null);

        return self::$self;
    }

    public function getSettings()
    {
        return $this->settings;
    }

    private function __construct($dir, Config $config, $namespace = null)
    {

        $run = new \Whoops\Run();

        if (Controller::isAjax()) {
            $JsonHandler = new JsonResponseHandler();
            if($config['debug'])
                $JsonHandler->addTraceToOutput(true);
            $run->pushHandler($JsonHandler);
        } else {
            $handler = new PrettyPageHandler();
            $run->pushHandler($handler);
        }
        Settings::whoops($run->register());

        //include_once ('System/ClassAutoLoad.php');
        //spl_autoload_register('ClassAutoLoad::autoload');

        $this->settings = $settings = new Settings($config);

        //$CLIENT_URL = (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : '';
        Settings::set("base_dir", $DIR = str_replace(array("\\","/"),DS,$dir.'/'));
        $dirname = dirname($_SERVER['SCRIPT_FILENAME']);
        $direxp = explode(DS,$dirname);
        $dirend = end($direxp);

        $protocol = (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) ? $_SERVER['HTTP_X_FORWARDED_PROTO'] : ('http' . (array_key_exists('HTTPS',$_SERVER) || (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] == true))?'s':'')));
        Settings::set("protocol", $protocol . '://');
        $HOST_CHANGED = false;
        if(Settings::get("force_https") == true && $protocol != "https")
        {
            $HOST_CHANGED = true;
            Settings::set("protocol", "https://");
        }
        Settings::set("base_link", Settings::get("protocol") . str_replace('//', '/', $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . '/'));

        $dirname = dirname($_SERVER['SCRIPT_NAME']);
        if($dirname[strlen($dirname)-1] != '/') $dirname .= '/';
        Controller::$ACTIVE_URL = $URL = urldecode(substr($_SERVER['REQUEST_URI'], strpos( $_SERVER['REQUEST_URI'] , $dirname ) + strlen($dirname) ));

        if($HOST_CHANGED)
        {
            header('HTTP/1.1 410 Gone');
            Url::navigate(Settings::get("base_link").$URL);
        }

        $SessionHandler = new SessionHandler();

        Language::language((isset($_GET['lang']) && in_array($_GET['lang'],Language::get_langs())) ? $_GET['lang'] : Session::get('language'));

        if(!Settings::get("langdir"))
        {
            if(isset($_GET['lang'])){
                if(isset($_SERVER['HTTP_REFERER']))
                    Url::navigate($_SERVER['HTTP_REFERER'],true);
                else {
                    $get = $_GET;
                    if(isset($get['lang']))
                        unset($get['lang']);
                    $get = http_build_query($get);
                    Url::navigate(Settings::get("base_link").(!empty($get) ? '?'.$get : ''), true);
                }
            }
        }

        if(Settings::get("langdir"))
        {
            $LANG = preg_replace('#([^\/]*)(\/)?(.*)#','$1',$URL);
            if(isset($LANG) && !empty($LANG) && in_array($LANG,Language::get_langs()))
            {
                Controller::$ACTIVE_URL = $URL = (preg_replace('#([^\/]*)(\/)?(.*)#','$3',$URL) != $LANG) ? preg_replace('#([^\/]*)(\/)?(.*)#','$3',$URL) : '';
                if($LANG !== Language::language())
                {
                    Language::language($LANG);
                    if($URL == '' && Settings::get("langdir") && isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER']))
                    {
                        if(!Controller::isAjax())
                            Url::navigate($_SERVER['HTTP_REFERER'],true);
                    }
                    else
                    {
                        if(!Controller::isAjax())
                            Url::navigate(Language::language().'/'.$URL,true);
                    }
                }
                else
                {
                    Language::language($LANG);
                }
            }
            else
            {
                Language::language(Language::getDefaultLanguage());
                if(!Controller::isAjax())
                    Url::navigate(Language::language().'/'.$URL,true);
            }
        }

        $domain = str_replace('//', '/', $_SERVER['HTTP_HOST']);
        $subdomains = explode(".", $domain);

        $URL = explode('?',$URL);
        $URL = $URL[0];

        $this->URL = $URL = Controller::urlMake($URL, $settings->urlRules());

        if($config["namespace"] === true && $namespace == null)
        {
            $URL = explode("?", $URL);
            $namespaces = array_shift($URL);
            $namespace = preg_replace('#([^\/]*)(\/)?(.*)#','$1', $namespaces);
            array_unshift($URL, substr($namespaces, strlen($namespace)));
            $URL = implode("?", $URL);
            //$namespace = mb_ucfirst($namespace);
        }
        else if($config["namespace"] === false)
        {
            $namespace = '';
        }
        else if(is_string($config["namespace"]) && $config["namespace"] != "subdomains")
        {
            $namespace = $config["namespace"];
        }
        else if(is_string($config["namespace"]) && $config["namespace"] == "subdomains")
        {
            $namespace = null;
            if(count($subdomains) > 1)
            {
                $namespace = implode('\\', $subdomains);
            }
        }
        if(empty($namespace))
            $namespace = '';
        $this->namespace = $namespace;

        Template::$FOLDERS = [
            'GLOBAL' => $dir."/Template/",
        ];
    }
    
    public function render()
    {
        $config = $this->settings->getConfig();
        if(strpos($this->URL, '/') === 0)
            $url = substr($this->URL, 1);
        else
            $url = $this->URL;
        if(isset($_GET))
        {
            $get = $_GET;
            $_GET = explode("/",$url);
            $_GET = array_merge($_GET, $get);
        }
        else
        {
            $_GET = explode("/",$url);
        }
        $_PARAMETERS = explode("/", $url);
        if($_PARAMETERS[count($_PARAMETERS)-1] == "")
        {
            unset($_PARAMETERS[count($_PARAMETERS)-1]);
        }

        if($config["namespace"] === true)
        {
            array_shift($_PARAMETERS);
            array_shift($_GET);
        }

        //array_shift($_GET);

        if(isset($_PARAMETERS[0]))
            unset($_PARAMETERS[0]);
        if(isset($_PARAMETERS[1]))
            unset($_PARAMETERS[1]);

        $_SERVER['argv'] = $_GET;

        $page = mb_ucfirst( (!empty($_GET[0]) && isset($_GET[0])) ? $_GET[0] : Controller::DEFAULT_CONTROLLER );
        $action = (!empty($_GET[1]) && isset($_GET[1])) ? $_GET[1] : Controller::DEFAULT_ACTION;

        $namespace = $this->getNamespace();
        if(empty($namespace))
            $namespace = 'Controller';

        $class = 'ComposerPack\\'.$namespace.'\\'.$page.'Controller';
        $nclass = 'ComposerPack\\'.$namespace.'\\'.Controller::DEFAULT_CONTROLLER.'Controller';
        if(class_exists($class))
        {
            $PAGE = new $class($_PARAMETERS, $action);
            $PAGE::$browser = Browser::getInstance();
            echo $PAGE;
        }
        else if(class_exists($nclass))
        {
            array_unshift($_PARAMETERS, $action);
            $PAGE = new $nclass($_PARAMETERS, $page);
            $PAGE::$browser = Browser::getInstance();
            echo $PAGE;
        }
        else
        {
            $PAGE = new Controller($_PARAMETERS, $action);
            $PAGE::$browser = Browser::getInstance();
            echo $PAGE;
        }

        $content = ob_get_clean();
        $content = trim($content);

        $farmers = $this->settings->getFarmers();
        if($farmers != null && is_array($farmers))
        {
            foreach($farmers as $farmer)
            {
                $content = $farmer->farmer($content);
            }
        }
        else if($farmers != null)
        {
            $content = $farmers->farmer($content);
        }
        return $content;
    }

    public function getNamespace()
    {
        return mb_ucfirst($this->namespace);
    }
}