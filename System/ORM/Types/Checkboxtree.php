<?php
namespace ComposerPack\System\ORM\Types;

class Checkboxtree extends Select {
	
	public function listField($model, $url)
	{
		$values = $this->toKeyValues($this->values());
		if(isset($values[$model[$this->key()]]))
			return $values[$model[$this->key()]];
			else
				return $model[$this->key()];
	}
	
	public function blockField($model, $formid, $url)
	{
	    $key = $this->key();
		//$this->required() ? 'required="required"' : '';
		ob_start();
		?>
		<div class="checkboxtree">
		<ul>
		<?php
		$this->generate($key, $this, $this->values(), $model[$key]);
		?>
		</ul>
		</div>
		<?php
		return ob_get_clean();
	}
	
	protected $deap = "&#160;&#160;&#160;";
	
	public function generate($key, $field, $values, $value, $level = 0, $id = '')
	{
		if(isset($values['label']) && isset($values['values']))
		{
			$label = $values['label'];
			$_values = array_merge($values);
			unset($_values['label']);
			unset($_values['values']);
			foreach($_values as $_value => $_name)
			{
				?><li><label><input type="checkbox" name="<?php echo $key; ?>" value="<?php echo $_value; ?>" <?php echo in_array($_value,$value) ? 'checked="checked"' : ''; ?> <?php echo $this->disabled() ? 'disabled="disabled"' : ''; ?> class=" <?php echo 'can-disable '.($this->disabled() ? 'field-disabled disabled' : ''); ?>"/> <?php echo $_name; ?></label><?php
			}
			$values = $values['values'];
			$id = "ulcol".md5(microtime());
			?>
			<li><span role="button" data-toggle="collapse" href="#<?php echo $id; ?>" aria-expanded="false" aria-controls="<?php echo $id; ?>"><?php echo $label; ?> <i class="caret"></i></span>
				<ul<?php echo !empty($id) ? ' id="'.$id.'" aria-expanded="false" class="collapse"' : ''?>>
					<?php $this->generate($key, $field, $values, $value, $level+1, $id); ?>
				</ul>
			</li>
			<?php
		}
		else
		{
			foreach($values as $_value => $_name)
			{
				if(is_array($_name))
				{
					$this->generate($key, $field, $_name, $value, $level);
				}
				else
				{
					?><li><label><input type="checkbox" name="<?php echo $key; ?>" value="<?php echo $_value; ?>" <?php echo in_array($_value,$value) ? 'checked="checked"' : ''; ?> <?php echo $this->disabled() ? 'disabled="disabled"' : ''; ?> class=" <?php echo 'can-disable '.($this->disabled() ? 'field-disabled disabled' : ''); ?>"/><?php echo $_name; ?></label><?php
				}
			}
		}
	}

	
}