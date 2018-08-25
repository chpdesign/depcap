<?php
namespace ComposerPack\System\ORM\Types;

use ComposerPack\System\Session\Session;

class Select extends Type {
	
	protected $values = [];

	protected $disabledValues = [];
	
	public function __construct($field = []){
		parent::__construct($field);
		$this->values(isset($field['values']) ? $field['values'] : []);
		$this->size(isset($field['size']) ? $field['size'] : null);
		$this->canEmpty(isset($field['empty']) ? $field['empty'] : null);
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

	public function disabledValues($values = null)
    {
        if(is_null($values))
            return $this->disabledValues;
        else
        {
            $this->disabledValues = $values;
            $this->searchable($this->searchable());
            return $this;
        }
    }

	protected $size = 0;

	public function size($size = null)
	{
		if(is_null($size))
			return $this->size;
		else
		{
			$this->size = (int) $size;
			return $this;
		}
	}

	protected $empty = true;

	public function canEmpty($empty = null)
	{
		if(is_null($empty))
			return $this->empty;
		else
		{
			$this->empty = (bool) $empty;
			return $this;
		}
	}

	public function defaultValue($model)
	{
        $values = self::toKeyValues($this->values());
        if(is_string(key($values)) && is_numeric($this->defaultValue)) {
            $keys = array_keys($values);
            return $keys[$this->defaultValue];
        }
		return $this->defaultValue;
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

    /**
     * @param $model array
     * @param $formid string
     * @param $url string
     * @return string
     */
	public function blockField($model, $formid, $url)
	{
        $key = $this->key();
        if(isset($model[$key]))
            $value = $model[$key];
        else
            $value = null;

		ob_start();
		?>
		<select <?php echo $this->size() > 0 ? 'size="'.$this->size().'"' : ($this->size() < 0 ? 'size="'.(count(self::toKeyValues($this->values()))+$this->canEmpty()).'"' : ''); ?> name="<?php echo $key; ?>" class="form-control <?php echo 'can-disable '.($this->disabled() ? 'field-disabled disabled' : ''); ?>" <?php echo $this->required() ? 'required="required"' : ''; ?> <?php echo $this->disabled() ? 'disabled="disabled"' : ''; ?>>
            <?php if($this->canEmpty()){ ?>
			<option></option>
            <?php } ?>
			<?php
			$this->generate($this->values(), $value);
			?>
		</select>
		<?php
		return ob_get_clean();
	}
	
	protected $deap = "&#160;&#160;&#160;";
	
	public function generate($values, $value, $level = 0)
	{
		if(isset($values['label']) && isset($values['values']))
		{
			$label = $values['label'];
			$_values = array_merge($values);
			unset($_values['label']);
			unset($_values['values']);
			foreach($_values as $_value => $_name)
			{
				?><option <?php echo ($this->isAssoc($this->disabledValues()) && array_key_exists($_value, $this->disabledValues())) || in_array($_value, $this->disabledValues()) ? 'disabled="disabled"' : ''; ?> value="<?php echo $_value; ?>" <?php echo $this->compare($_value, $value) ? 'selected="selected"' : ''; ?>><?php echo str_repeat($this->deap, $level).$_name; ?></option><?php
			}
			$values = $values['values'];
			?><option disabled="disabled"><?php echo str_repeat($this->deap, $level); ?><?php echo $label; ?></option>
			<?php $this->generate($values, $value, $level+1); ?>
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
					?><option <?php echo ($this->isAssoc($this->disabledValues()) && array_key_exists($_value, $this->disabledValues())) || in_array($_value, $this->disabledValues()) ? 'disabled="disabled"' : ''; ?> value="<?php echo $_value; ?>" <?php echo $this->compare($_value, $value) ? 'selected="selected"' : ''; ?>><?php echo str_repeat($this->deap, $level).$_name; ?></option><?php
				}
			}
		}
	}

	protected function compare($a,$b)
    {
        if(is_null($a) && is_null($b))
            return true;
        if(is_null($a) || is_null($b))
            return false;
        return $a == $b;
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

    protected function isAssoc(array $arr)
    {
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }
	
	/*public function generate($values, $value)
	{
		if(isset($values['label']) && isset($values['values']))
		{
			?><optgroup label="<?php echo isset($values['label']) ? $values['label'] : ''; ?>">
			<?php $this->generate(isset($values['values']) ? $values['values'] : $values, $value); ?>
			</optgroup><?php
		}
		else
		{
			foreach($values as $_value => $_name)
			{
				?><option value="<?php echo $_value; ?>" <?php echo $_value == $value ? 'selected="selected"' : ''; ?>><?php echo $_name; ?></option><?php
			}
		}
	}*/
	
}