<?php
namespace ComposerPack\System\ORM\Types;

use ComposerPack\Module\Language\Language;

class Textarea extends Type {

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
                    <textarea class="form-control <?php echo 'can-disable '.($this->disabled() ? 'field-disabled disabled' : ''); ?>" name="<?php echo $key; ?>" <?php $this->required() ? 'required="required"' : ''; ?> <?php echo $this->disabled() ? 'disabled="disabled"' : ''; ?>><?php echo $value; ?></textarea>
                </div>
            <?php } ?>
        </div>

        <?php } else {
            $key = $this->key();
            if(isset($model[$key]))
                $value = $model[$key];
            else
                $value = null;
            if(is_array($value))
                $value = "";
            ?>
            <textarea class="form-control <?php echo 'can-disable '.($this->disabled() ? 'field-disabled disabled' : ''); ?>" name="<?php echo $key; ?>" <?php $this->required() ? 'required="required"' : ''; ?> <?php echo $this->disabled() ? 'disabled="disabled"' : ''; ?>><?php echo $value; ?></textarea>
        <?php
        }


		return ob_get_clean();
	}
}