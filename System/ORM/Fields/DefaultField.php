<?php
namespace ComposerPack\System\ORM\Fields;

class DefaultField extends Field {

    public function column()
    {
        $key = $this->column;
        return [$key => $key];
    }

    public function read()
    {
        $name = $this->column;
        $foreigns = $this->foreigns;
        $func = 'read_' . $name;
        if (method_exists($this->model, $func)) {
            return call_user_func_array(array($this->model, $func), array($this->value, $this->model));
        } elseif (isset($foreigns[$name])) {
            $table = $foreigns[$name]['table'];
            $func = 'read_from_' . $table;
            if (method_exists($this->model, $func)) {
                return call_user_func_array(array($this->model, $func), array($name, $this->value, $this->model));
            }
        }
        return $this->value;
    }

    public function write($save = true)
    {
        $key = $this->column;

        $func = 'write_'.$key;

        $foreigns = $this->foreigns;

        $value = $this->value;

        if(isset($foreigns[$key])) {
            $table = $foreigns[$key]['table'];
            $func_to = 'write_to_' . $table;
        }

        if(method_exists($this->model, $func))
        {
            return call_user_func_array(array($this->model,$func),array($value, $this->model));
        }
        elseif(isset($foreigns[$key]) && method_exists($this->model, $func_to))
        {
            //var_dump(call_user_func_array(array($this,$func_to),array($name,$tags[$name])));
            return call_user_func_array(array($this->model,$func_to),array($key, $value, $this->model, $save));
        }

        return $value;
    }

}