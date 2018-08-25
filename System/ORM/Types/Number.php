<?php
namespace ComposerPack\System\ORM\Types;

class Number extends Type {
	
	public function blockField($model, $formid, $url)
	{
        $key = $this->key();
        $value = $model[$key];
		ob_start();
		?>
		<input class="form-control number <?php echo 'can-disable '.($this->disabled() ? 'field-disabled disabled' : ''); ?>" type="text" name="<?php echo $key ?>" value="<?php echo htmlentities($value); ?>" <?php echo $this->required() ? 'required="required"' : ''; ?> <?php echo $this->disabled() ? 'disabled="disabled"' : ''; ?>/>
		<?php
		return ob_get_clean();
	}

	
}