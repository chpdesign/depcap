<?php
namespace ComposerPack\System\Config;

class PHPConfig extends Config
{
	
	public function __construct($config)
	{
		parent::__construct($config);
	}

	public function save($file)
    {
        file_put_contents($file, $this->__toString());
        return true;
    }

	public function __toString()
    {
        return var_export($this->config, true);
    }

}