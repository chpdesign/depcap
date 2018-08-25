<?php
namespace ComposerPack\System\Config;

class INIConfig extends Config
{

	public function __construct($config) {
		parent::__construct($config);
		if(file_exists($config))
		{
			$ini = parse_ini_file($config);
			if($ini !== false)
			$this->config = $ini;
		}
		else
		{
			$ini = parse_ini_file($config);
			if($ini !== false)
			$this->config = $ini;
		}
	}

	public function save($file)
    {
        file_put_contents($file, $this->__toString());
        return true;
    }

    public function __toString()
    {
        return $this->arr2ini($this->config);
    }

    function arr2ini(array $a, array $parent = array())
    {
        $out = '';
        foreach ($a as $k => $v)
        {
            if (is_array($v))
            {
                //subsection case
                //merge all the sections into one array...
                $sec = array_merge((array) $parent, (array) $k);
                //add section information to the output
                $out .= '[' . join('.', $sec) . ']' . PHP_EOL;
                //recursively traverse deeper
                $out .= $this->arr2ini($v, $sec);
            }
            else
            {
                //plain key->value case
                $out .= "$k=$v" . PHP_EOL;
            }
        }
        return $out;
    }

	
}