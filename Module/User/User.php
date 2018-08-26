<?php
namespace ComposerPack\Module\User;

use ComposerPack\System\ORM;

/**
 * User osztály
 * @package ComposerPack\Model
 */
class User extends ORM{

    protected $table = "user";

    public function read_password($arg)
    {
        $this['_sr_pass'] = $arg;
        return $arg;
    }

    public function write_password($arg)
    {
        if(!empty($arg))
        {
            if ($this['_sr_pass'] != $arg) {
                return $this['_sr_pass'] = hash('sha512', md5($this['email'] . $arg), false);
            }
            else
            {
                return $this['_sr_pass'] = $arg;
            }
        }
        if(!empty($this['_sr_pass']))
            return $this['_sr_pass'];
        else
            return $this['password'];
    }

    public function password($value = null)
    {
        if(func_num_args() == 0) {
            return '';
        }
        else
        {
            return $this->write_password($value);
        }
    }

    public function where_login($email, $password)
    {
        $this->where("`email` = '".$email."'")->where("`password` = '".hash('sha512',md5($email.$password),false)."'");
        return $this;
    }

    public function hasPermission($permission)
    {
        return true;
    }

    public function getProfileImage()
    {
        if(empty($this['google_picture']))
        {
            return "https://www.gravatar.com/avatar/".md5($this['email'])."?d=mm";
        }
        else
        {
            return $this['google_picture'];
        }
    }

}