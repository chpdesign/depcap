<?php
namespace ComposerPack\System\ORM\Types;

class Date extends Type {

    public function __construct(array $field = [])
    {
        parent::__construct($field);
        $this->inline(isset($field['inline']) ? $field['inline'] : null);
        $this->sidebyside(isset($field['sidebyside']) ? $field['sidebyside'] : null);
        $this->format(isset($field['format']) ? $field['format'] : null);

    }

    protected $format = 'Y-m-d';

    public function where($value, $type = null)
    {
        if(strpos($value, " : ") !== false)
        {
            $values = explode(" : ", $value);
            return "(`".$this->table->from()."`.`".$this->key()."` >= '".$values[0]." 00:00:00' AND `".$this->table->from()."`.`".$this->key()."` <= '".$values[1]." 23:59:59')";
        }
        else
        {
            return "`".$this->table->from()."`.`".$this->key()."` = '".$value."'";
        }
    }

    public function format($format = null)
    {
        if(is_null($format))
        {
            return $this->format;
        }
        else
        {
            $this->format = $format;
            return $this;
        }
    }

    protected $inline = true;

    public function inline($inline = null)
    {
        if(is_null($inline))
        {
            return $this->inline;
        }
        else
        {
            $this->inline = (bool) $inline;
        }
    }

    protected $sidebyside = true;

    public function sidebyside($sidebyside = null)
    {
        if(is_null($sidebyside))
        {
            return $this->sidebyside;
        }
        else
        {
            $this->sidebyside = (bool) $sidebyside;
        }
    }

    protected function formatToJs()
    {
        //'YYYY-MM-DD HH:mm:ss'
        //'Y-m-d H:i:s'
        $format = $this->format();
        $format = preg_replace("/([Y|y]+)/", "YYYY", $format);
        $format = preg_replace("/([m]+)/", "MM", $format);
        $format = preg_replace("/([D|d]+)/", "DD", $format);
        $format = preg_replace("/([H|h]+)/", "HH", $format);
        $format = preg_replace("/([i]+)/", "mm", $format);
        $format = preg_replace("/([s]+)/", "ss", $format);
        return trim($format);
    }
	
	public function blockField($model, $formid, $url)
	{
	    $key = $this->key();
	    if(isset($model[$key]))
	        $value = $model[$key];
	    else
	        $value = null;

        ob_start();
        ?>
        <div style="position:relative;">
		<input class="form-control <?php echo $this->disabled() ? '' : 'date'; ?> <?php echo 'can-disable '.($this->disabled() ? 'field-disabled disabled' : ''); ?>" data-side-by-side="<?php echo $this->sidebyside() ? "true" : "false"; ?>" data-inline="<?php echo $this->inline() ? "true" : "false"; ?>" type="text" name="<?php echo $key ?>" value="<?php echo htmlentities($value); ?>" <?php echo $this->required() ? 'required="required"' : ''; ?> <?php echo $this->disabled() ? 'disabled="disabled"' : ''; ?>/>
        <script type="text/javascript">
            $('[name="<?php echo $key; ?>"]').each(function(){
                $(this).datetimepicker({
                    inline: $(this).data('inline') == undefined ? false : $(this).data('inline'),
                    sideBySide: $(this).data('side-by-side') == undefined ? false : $(this).data('side-by-side'),
                    format: '<?php echo $this->formatToJs(); ?>'
                });
                <?php if(!$this->disabled()) { ?>
                if($(this).data('inline') == undefined ? false : $(this).data('inline'))
                    $(this).hide();
                <? } ?>
            });
        </script>
        </div>
		<?php
		return ob_get_clean();
	}

	
}