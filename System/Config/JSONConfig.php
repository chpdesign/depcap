<?php
namespace ComposerPack\System\Config;

class JSONConfig extends Config
{

	public function __construct($config) {
		if(is_string($config))
		{
			$json = @json_decode($config, true);
			if($json != false)
                $config = $json;
		}
        parent::__construct($config);
	}

	public function save($file)
    {
        file_put_contents($file, json_encode($this->config));
        return true;
    }

    public function __toString()
    {
        return (string) json_encode($this->config);
    }
}