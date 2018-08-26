<?php

namespace ComposerPack\Module\Package;

use ComposerPack\System\ORM;

class Label extends ORM
{

    protected $primary_key = array();

    protected $default_primary_key = array('author', 'repo', 'label');

    protected $table = 'label';

    public function __toString()
    {
        return $this["label"];
    }

}
