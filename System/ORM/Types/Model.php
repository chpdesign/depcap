<?php
namespace ComposerPack\System\ORM\Types;

use ComposerPack\System\ORM;
use ComposerPack\System\ORM\Table\Table;

class Model extends Type
{

    protected $displayBlock = null;

    public function displayBlock($display = null)
    {
        if (is_null($display))
            return $this->displayBlock;
        else {
            $this->displayBlock = $display;
            return $this;
        }
    }

    protected $displayList = null;

    public function displayList($display = null)
    {
        if (is_null($display))
            return $this->displayList;
        else {
            $this->displayList = $display;
            return $this;
        }
    }

    protected $foreign = null;

    public function foreign($foreign = null)
    {
        if (is_null($foreign))
            return $this->foreign;
        else {
            $this->foreign = $foreign;
            return $this;
        }
    }

    public function __construct($field = [])
    {
        parent::__construct($field);
        $this->displayList($this->getDisplay($field, 'list'));
        $this->displayBlock($this->getDisplay($field, 'block'));
        $this->table(isset($field['model']['from']) ? $field['model']['from'] : null);
        $this->foreign($field['model']['foreign']);
    }

    private function getDisplay($field, $key)
    {
        return isset($field['model']['display']) ? (is_array($field['model']['display']) ? (isset($field['model']['display'][$key]) ? $field['model']['display'][$key] : $field['model']['display']) : $field['model']['display']) : null;
    }

    /**
     * @param array $sqlParams
     * @param $table Table
     * @param null $type
     * @return array
     */
    public function sqlConnection($sqlParams, $table, $type = null)
    {
        $from = $table->from();
        if (is_array($from))
            $from = reset($from);
        $key = $this->key();
        if ($this->sql()) {
            $foreign = $this->foreign();
            if (is_array($foreign)) {
                $foreign = reset($foreign);
            }

            //$columns = $db->columns($this->table()->from());

            $sqlParams["SELECT"][$key] = "`" . $key . "_model_table`.`" . $foreign . "` AS `" . $key . "`";
            $types = $this->getTypes($type);
            foreach ($types as $type => $display) {
                if (is_array($display)) {
                    $concats = [];
                    foreach ($display as $col) {
                        if (substr($col, 0, 1) == '!') {
                            $concats[] = '"' . substr($col, 1) . '"';
                        } else {
                            //if(isset($columns[$col])) {
                                $sqlParams["SELECT"][$type . "__" . $key . "_" . $col] = "`" . $key . "_model_table`.`" . $col . "` AS `" . $type . "__" . $key . "_" . $col . "`";
                                if (!isset($sqlParams["SELECT"][$from . "_" . $col])) {
                                    $sqlParams["SELECT"][$from . "_" . $col] = "`" . $key . "_model_table`.`" . $col . "` AS `" . $from . "_" . $col . "`";
                                }
                                $concats[] = "`" . $key . "_model_table`.`" . $col . "`";
                            //}
                        }
                    }
                    if(!empty($concats)) {
                        $sqlParams["SELECT"][$type . "__" . $key] = "CONCAT(" . implode(", ", $concats) . ") AS `" . $type . "__" . $key . "`";
                    }
                } else {
                    if (substr($display, 0, 1) == '!') {
                        $sqlParams["SELECT"][$type . "__" . $key] = "'" . $display . "' AS `" . $type . "__" . $key . "`";
                    }
                    else
                    {
                        //if(isset($columns[$display])) {
                            $sqlParams["SELECT"][$type . "__" . $key] = "`" . $key . "_model_table`.`" . $display . "` AS `" . $type . "__" . $key . "`";
                        //}
                    }
                }
            }
            if (isset($sqlParams["JOIN"])) {
                if (!is_array($sqlParams["JOIN"]))
                    $sqlParams["JOIN"] = [$sqlParams["JOIN"]];
            } else {
                $sqlParams["JOIN"] = [];
            }
            $sqlParams["JOIN"][$key] = "
				LEFT JOIN
					`" . $this->table->from() . "` AS `" . $key . "_model_table`
				ON
			";
            if (is_array($this->foreign())) {
                foreach ($this->foreign() as $m => $v) {
                    $sqlParams["JOIN"][$key] .= "
						`" . $from . "`.`" . $m . "` = `" . $key . "_model_table`.`" . $v . "`
					";
                }
            } else {
                $sqlParams["JOIN"][$key] .= "
					`" . $from . "`.`" . $key . "` = `" . $key . "_model_table`.`" . $this->foreign() . "`
				";
            }
        }
        return $sqlParams;
    }

    public function displayBlockFormat($model, $key)
    {
        return $this->displayFormat($model, $key, 'block');
    }

    public function displayListFormat($model, $key)
    {
        return $this->displayFormat($model, $key, 'list');
    }

    private function displayFormat($model, $key, $type)
    {
        $display = $type == 'list' ? $this->displayList() : $this->displayBlock();

        if (is_array($display)) {
            $concats = [];
            foreach ($display as $col) {
                if (substr($col, 0, 1) == '!') {
                    $concats[] = substr($col, 1);
                } else {
                    $concats[] = $model[$type . '__' . $key . '_' . $col];
                }
            }
            return implode("", $concats);
        } else {
            return $model[$type . "__" . $key];
        }
    }

    protected function getTypes($type = null)
    {
        if($type == 'block')
            $types = ['block' => $this->displayBlock()];
        else if ($type == 'list')
            $types = ['list' => $this->displayList()];
        else
            $types = ['block' => $this->displayBlock(), 'list' => $this->displayList()];
        return $types;
    }
	
	public function where($value, $type = null)
	{
	    $key = $this->key();
		$where = [];
		$types = $this->getTypes($type);
		foreach($types as $type => $display)
		{
			if(is_array($display))
			{
				$wheres = [];
				foreach($display as $type => $col)
				{
					if(substr($col, 0, 1) == '!')
					{
						$wheres[] = '"'.substr($col, 1).'" LIKE "%'.$value.'%"';
					}
					else
					{
						$wheres[] = "`".$key."_model_table`.`".$col."` LIKE '%".$value."%'";
					}
				}
				$where[] = "(".implode(") OR (", $wheres).")";
			}
			else
			{
				$where [] =  "`".$key."_model_table`.`".$display."` LIKE '%".$value."%'";
			}
		}
		return "(".implode(") OR (", $where).")";
	}

	public function visibleValue($model)
    {
        if(isset($model['list__'.$this->key()]))
            return $model['list__'.$this->key()];
        return $model[$this->key()];
        //return $this->displayBlockFormat($model, $key);
    }

    public function processData(&$model, $data, $table, $type = null)
    {
        $key = $this->key();
        if(isset($data[$this->key()]))
            $value = $data[$this->key()];
        else if(isset($model[$this->key()]))
            $value = $model[$this->key()];
        else
            $value = $this->defaultValue($model);

        if(is_array($value) || is_object($value))
            return $value;

        if(is_array($this->foreign()))
        {
            foreach($this->foreign() as $m => $v)
            {
                if(!isset($data[$m]))
                    return $value;
            }

            $sqlParams = ['SELECT' => ['*'], 'FROM' => $this->table()->from()];
            $sqlParams = $this->sqlConnection($sqlParams, $table, $type);
            $sqlParams['FROM'] = "`".$this->table()->from()."` AS `".$key."_model_table`";
            $sqlParams['JOIN'] = [];
            $_keys = $this->foreign();
            if(!is_array($_keys))
            {
                $_keys = [$_keys => $_keys];
            }
            $WHERE = [];
            foreach($_keys as $_keyFrom => $_keyTo) {
                $WHERE[] = "`" . $key . "_model_table`.`".$_keyTo."` = '" . $data[$_keyFrom] . "'";
            }
            $sqlParams['WHERE'] = [];
            $sqlParams['WHERE'][] = "(".implode(" OR ", $WHERE).")";
            $subModel = $this->db->fetch_assoc($this->db->query(Table::__sql_generate_static($sqlParams)));
            if(!empty($subModel))
            foreach($subModel as $key => $val)
            {
                if(!isset($model[$key]))
                    $model[$key] = $val;
            }
            return $value;




        }
        else
        {
            $sqlParams = ['SELECT' => ['*'], 'FROM' => $this->table()->from()];
            $sqlParams = $this->sqlConnection($sqlParams, $table, $type);
            $sqlParams['FROM'] = "`".$this->table()->from()."` AS `".$key."_model_table`";
            $sqlParams['JOIN'] = [];
            $_keys = $this->foreign();
            if(!is_array($_keys))
            {
                $_keys = [$_keys => $_keys];
            }
            $WHERE = [];
            foreach($_keys as $_keyFrom => $_keyTo) {
                $WHERE[] = "`" . $key . "_model_table`.`".$_keyTo."` = '" . $value . "'";
            }
            $sqlParams['WHERE'] = [];
            $sqlParams['WHERE'][] = "(".implode(" OR ", $WHERE).")";
            $subModel = $this->db->fetch_assoc($this->db->query(Table::__sql_generate_static($sqlParams)));
            if(!empty($subModel))
            foreach($subModel as $key => $val)
            {
                if(!isset($model[$key]) && strpos($key, 'block__') === 0 || strpos($key, 'list__') === 0)
                    $model[$key] = $val;
            }

            return $value;
        }
    }

    public function listField($model, $url)
    {
        $key = $this->key();
        $value = $model[$key];
        if($value instanceof ORM)
        {
            $display = $this->displayList();
            if (is_array($display)) {
                $concats = [];
                foreach ($display as $col) {
                    if (substr($col, 0, 1) == '!') {
                        $concats[] = substr($col, 1);
                    } else {
                        $concats[] = $value[$col];
                    }
                }
                if(!empty($concats)) {
                    return implode("", $concats);
                }
            } else {
                if (substr($display, 0, 1) == '!') {
                    return $display;
                }
                else
                {
                    return $value[$display];
                }
            }
        }
        if(isset($model['list__'.$this->key()]))
            return $model['list__'.$this->key()];
        if(isset($model[$this->key()]))
            return $model[$this->key()];
        return $this->defaultValue($model);
    }
	
	public function blockField($model, $formid, $url)
	{
        $key = $this->key();
        if(isset($model[$key])) {
            //$value = $model[$key];
        }
        else {
            //$value = $this->defaultValue($model);
            $model[$key] = $this->defaultValue($model);
        }
        $value = $model[$key];//$this->processData($model, $model, $this->table(), 'block');
        $visibleValue = isset($model['block__'.$key]) ? $model['block__'.$key] : '';
        if($value instanceof ORM)
        {
            $display = $this->displayList();
            if (is_array($display)) {
                $concats = [];
                foreach ($display as $col) {
                    if (substr($col, 0, 1) == '!') {
                        $concats[] = substr($col, 1);
                    } else {
                        $concats[] = $value[$col];
                    }
                }
                if(!empty($concats)) {
                    $visibleValue = implode("", $concats);
                }
            } else {
                if (substr($display, 0, 1) == '!') {
                    $visibleValue = $display;
                }
                else
                {
                    $visibleValue = $value[$display];
                }
            }
            $value = htmlentities(json_encode($value->getPrimaryKeys()));
        }
        ob_start();
		?>
		<?php
		{
			$field_id = "field_".md5(microtime());
		?>
		<input type="hidden" class="form-control filter <?php echo 'can-disable '.($this->disabled() ? 'field-disabled disabled' : ''); ?>" id="<?php echo $field_id; ?>" name="<?php echo $key; ?>" value="<?php echo $value; ?>" placeholder="" <?php echo $this->disabled() ? 'disabled="disabled"' : ''; ?>/>
		<input type="text" name="<?php echo 'block__'.$key; ?>" class="form-control <?php echo 'can-disable '.($this->disabled() ? 'field-disabled disabled' : ''); ?>" id="<?php echo $field_id; ?>_complete" value="<?php echo $visibleValue; ?>" placeholder="Keresés..." <?php echo $this->disabled() ? 'disabled="disabled"' : ''; ?>/>
		<script type="text/javascript">
		$(function(){
			var selected = {};
			$('#<?php echo $field_id; ?>_complete').typeahead(null, {
			  display: 'block__<?php echo $key; ?>',
			  source: new Bloodhound({
				datumTokenizer: Bloodhound.tokenizers.obj.whitespace('<?php echo $key; ?>'),
				queryTokenizer: Bloodhound.tokenizers.whitespace,
				remote: {
					cache: false,
					url: 'auto',
					wildcard: '%QUERY',
					replace: function () {
						return '<?php echo $url; ?>/auto?f=<?php echo $key; ?>&search='+$('#<?php echo $field_id; ?>_complete').val()+'&'+$('#<?php echo $formid; ?>').serialize();
					}
				}
			  })
			}).on( 'typeahead:selected', function(event, selected_object, dataset) {
			    /*if(JSON.stringify(selected_object) === JSON.stringify(selected))
			        return;*/
				$.each(selected_object,function(key,val){
					$('#<?php echo $formid; ?> [data-name="'+key+'"]').text(val);
					$('#<?php echo $formid; ?> input[name="'+key+'"]').val(val);
                    $('#<?php echo $formid; ?> [data-name="'+key+'"]').trigger('fieldvalue', [val]);
				});
				selected = selected_object;
				$('#<?php echo $field_id; ?>').change();
				lastvalue = $('#<?php echo $field_id; ?>_complete').val();
			});
			var lastvalue = $('#<?php echo $field_id; ?>_complete').val();
			$('#<?php echo $field_id; ?>_complete').keyup(function(){
				if(lastvalue != $(this).val())
				{
					$.each(selected,function(key,val){
						$('#<?php echo $formid; ?> [data-name="'+key+'"]').text('');
						$('#<?php echo $formid; ?> input[name="'+key+'"]').val('');
                        $('#<?php echo $formid; ?> [data-name="'+key+'"]').trigger('fieldvalue', []);
					});
					$('#<?php echo $field_id; ?>').val('');
					selected = {};
					$('#<?php echo $field_id; ?>').change();
				}
			});
		});
		</script>
		<?php
		}
		?>
		<?php
		return ob_get_clean();
	}
	
}