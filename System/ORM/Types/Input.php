<?php
namespace ComposerPack\System\ORM\Types;

use ComposerPack\Module\Language\Language;

class Input extends Type {

    protected $format = "text";

    public function __construct(array $field = [])
    {
        parent::__construct($field);
        if(isset($field['format'])) $this->format($field['format']);
    }

    public function format($format = null)
    {
        if(is_null($format))
        {
            return $this->format;
        }
        $this->format = $format;
        return $this;
    }
	
	public function blockField($model, $formid, $url)
	{
		ob_start();

        $value = $model[$this->key()];

        if($value instanceof Language)
        {
            $langs = Language::get_langs();
            ?>

            <!-- Nav tabs -->
            <ul class="nav nav-tabs <?php echo count($langs) == 1 ? 'hidden' : ''; ?>" role="tablist">
                <?php foreach($langs as $index => $lang){
                    $key = $this->key()."[".$lang."]";
                    ?>
                    <li role="presentation" class="<?php echo $index == 0 ? 'active' : ''; ?>"><a href="#<?php echo $this->key().'_translate_'.$lang; ?>" aria-controls="<?php echo $key.'_translate_'.$lang; ?>" role="tab" data-toggle="tab"><?php echo $lang; ?></a></li>
                <?php } ?>
            </ul>

            <!-- Tab panes -->
            <div class="tab-content">
                <?php
                if(isset($model[$this->key()]))
                    $values = $model[$this->key()];
                else
                    $values = [];
                foreach($langs as $index => $lang){
                    $key = $this->key()."[".$lang."]";
                    if(isset($values[$lang]))
                        $value = $values[$lang];
                    else
                        $value = "";
                    ?>
                    <div role="tabpanel" class="tab-pane <?php echo $index == 0 ? 'active' : ''; ?>" id="<?php echo $this->key().'_translate_'.$lang; ?>">
                        <input class="form-control <?php echo 'can-disable '.($this->disabled() ? 'field-disabled disabled' : ''); ?>" type="<?php echo $this->format; ?>" name="<?php echo $key ?>" value="<?php echo htmlentities($value); ?>" <?php echo $this->required() ? 'required="required"' : ''; ?> <?php echo $this->disabled() ? 'disabled="disabled"' : ''; ?>/>
                    </div>
                <?php } ?>
            </div>

        <?php } else {
            $key = $this->key();
            if(isset($model[$key]))
                $value = $model[$key];
            else
                $value = "";
            if(is_array($value))
                $value = "";
            ?>
            <input class="form-control <?php echo 'can-disable '.($this->disabled() ? 'field-disabled disabled' : ''); ?>" type="<?php echo $this->format; ?>" name="<?php echo $key ?>" value="<?php echo htmlentities($value); ?>" <?php echo $this->required() ? 'required="required"' : ''; ?> <?php echo $this->disabled() ? 'disabled="disabled"' : ''; ?>/>
            <?php
        }



		return ob_get_clean();
	}

	
}