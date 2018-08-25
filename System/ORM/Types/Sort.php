<?php
namespace ComposerPack\System\ORM\Types;

use ComposerPack\System\Sql;

class Sort extends Number
{

    public function __construct(array $field = [], Sql $db = null)
    {
        parent::__construct($field, $db);
        $this->visibleList($this->fieldVisible($field, 0, 'list'));
        $this->visibleBlock($this->fieldVisible($field, 1, 'block'));
    }

    /**
     * @param Type $field
     * @param int $index
     * @param string $key
     * @return bool
     */
    private function fieldVisible($field, $index, $key)
    {
        return
            isset($field['visible'])
            &&
            (
                $field['visible'] === true
                ||
                (
                    is_array($field['visible'])
                    &&
                    (
                        (isset($field['visible'][$key]) && $field['visible'][$key] == true)
                        ||
                        (isset($field['visible'][$index]) && $field['visible'][$index] == true)
                    )
                )
            );
    }

    public function listField($model, $url)
    {
        $value = $model[$this->key()];
        $column = $this->column();
        $_value = json_encode([$column => $value]);
        ob_start();
        ?>
        <?php echo $value; ?>.
        <?php if(!$this->disabled()) { ?>
        <i class="glyphicon glyphicon-sort sort grab"><textarea class="hidden" style="overflow-x: hidden; word-wrap: break-word; resize: none; overflow-y: visible;"><?php echo $_value; ?></textarea></i>
        <?php } ?>
        <?php
        return ob_get_clean();
    }

}