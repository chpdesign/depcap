<?php

namespace ComposerPack\Module\Package;

use ComposerPack\System\ORM;

class Repository extends ORM
{

    protected $primary_key = array();

    protected $default_primary_key = array('author', 'repo');

    protected $table = 'repository';

    public function __toString()
    {
        return $this["author"] . "/" . $this["repo"] . "";
    }

}
