<?php
namespace ComposerPack\System\ORM\Types;

class Status extends Select {

    protected $values = [];

    public function __construct($field = []){
        parent::__construct($field);
    }

    public function whereFilter()
    {
        return "`".$this->table()->from()."`.`".$this->key()."` > -1";
    }

}