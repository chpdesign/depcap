<?php
namespace ComposerPack\Module\Content;

use ComposerPack\System\ORM;

class Content extends ORM
{

    protected $primary_key = array ();
    protected $default_primary_key = array ('id');
    protected $table = 'content';

}
