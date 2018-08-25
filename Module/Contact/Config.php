<?php
namespace ComposerPack\Module\Contact;

use ComposerPack\System\Modules;

class Config extends \ComposerPack\Module\Config
{
    public function __construct($config, Modules $modules)
    {
        $moduleConfig = __DIR__.'/module.json';
        if(file_exists($moduleConfig))
            $config = array_merge($config, json_decode(file_get_contents($moduleConfig), true));
        parent::__construct($config, $modules);
    }

    public function getClass()
    {
        return new Contact();
    }
}