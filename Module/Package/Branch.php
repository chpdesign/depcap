<?php

namespace ComposerPack\Module\Package;

use ComposerPack\Module\Language\Language;
use ComposerPack\System\ORM;

class Branch extends ORM
{

    protected $primary_key = array();

    protected $default_primary_key = array('author', 'repo', 'branch');

    protected $table = 'branch';

    public function __toString()
    {
        return $this["branch"];
    }

}
