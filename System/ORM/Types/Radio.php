<?php
namespace ComposerPack\System\ORM\Types;

use ComposerPack\System\Session\Session;

class Radio extends Type {

    protected $values = [];

    public function __construct($field = []){
        parent::__construct($field);
        $this->values(isset($field['values']) ? $field['values'] : []);
    }

    public function where($value)
    {
        $table = $this->table()->from();
        if(is_array($table))
            $table = reset($table);
        $key = $this->key();
        if($this->translate())
        {
            return "`".$key."_lang_table`.`".Session::get('language')."` LIKE '%".$value."%'";
        }
        else
        {
            if(is_numeric($value))
            {
                return "`".$table."`.`".$key."` LIKE '".$value."'";
            }
            else
            {
                return "`".$table."`.`".$key."` LIKE '%".$value."%'";
            }
        }
    }

    public function values($values = null)
    {
        if(is_null($values))
            return $this->values;
        else
        {
            $this->values = $values;
            $this->searchable($this->searchable());
            return $this;
        }
    }

    public function visibleValue($model)
    {
        $values = self::toKeyValues($this->values());
        if(isset($values[$model[$this->key()]]))
            return $values[$model[$this->key()]];
        return $model[$this->key()];
    }

    public function listField($model, $url)
    {
        $key = $this->key();
        $value = $model[$key];

        $values = self::toKeyValues($this->values());
        if(isset($values[$value]))
            return $values[$value];
        else
            return $value;
    }

    public function blockField($model, $formid, $url)
    {
        $key = $this->key();
        $value = $model[$key];
        ob_start();
        ?>
        <div class="input-group">
            <?php
            $this->generate($key, $this->values(), $value);
            ?>
        </div>
        <?php
        return ob_get_clean();
    }

    public function generate($key, $values, $value, $level = 0)
    {
        if(isset($values['label']) && isset($values['values']))
        {
            $label = $values['label'];
            $_values = array_merge($values);
            unset($_values['label']);
            unset($_values['values']);
            foreach($_values as $_value => $_name)
            {
                ?>
                <div class="radio">
                    <label><input class=" <?php echo 'can-disable '.($this->disabled() ? 'field-disabled disabled' : ''); ?>" type="radio" name="<?php echo $key; ?>" value="<?php echo $_value; ?>" <?php echo $_value == $value ? ' checked="checked"' : ''; ?><?php echo $this->disabled() ? ' disabled="disabled"' : ''; ?><?php echo $this->required() ? ' required="required"' : ''; ?>/><?php echo $_name; ?></label>
                </div>
                <?php
            }
            $values = $values['values'];
            ?>
            <div>
            <?php echo $label; ?>
            <?php $this->generate($key, $values, $value, $level+1); ?>
            </div>
            <?php
        }
        else
        {
            foreach($values as $_value => $_name)
            {
                if(is_array($_name))
                {
                    $this->generate($_name, $value, $level);
                }
                else
                {
                    ?>
                    <div class="radio">
                        <label><input class=" <?php echo 'can-disable '.($this->disabled() ? 'field-disabled disabled' : ''); ?>" type="radio" name="<?php echo $key; ?>" value="<?php echo $_value; ?>" <?php echo $_value == $value ? ' checked="checked"' : ''; ?><?php echo $this->disabled() ? ' disabled="disabled"' : ''; ?><?php echo $this->required() ? ' required="required"' : ''; ?>/><?php echo $_name; ?></label>
                    </div>
                    <?php
                }
            }
        }
    }

    public static function toKeyValues($values)
    {
        $return = [];
        if(isset($values['label']) && isset($values['values']))
        {
            $label = $values['label'];
            $_values = array_merge($values);
            unset($_values['label']);
            unset($_values['values']);
            foreach($_values as $_value => $_name)
            {
                $return[$_value] = $_name;
            }
            $values = $values['values'];
            $return += self::toKeyValues($values);
        }
        else
        {
            foreach($values as $_value => $_name)
            {
                if(is_array($_name))
                {
                    $return += self::toKeyValues($_name);
                }
                else
                {
                    $return[$_value] = $_name;
                }
            }
        }
        return $return;
    }

}