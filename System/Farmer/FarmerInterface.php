<?php
namespace ComposerPack\System\Farmer;

abstract class FarmerInterface {

	/**
	 * Render a template or any other content that need rendering
	 * @param string $content file or content
	 * @return string rendered content
	 */
	public abstract function farmer($content = "");

    // https://code.google.com/archive/p/phpquery/issues/212
    // https://stackoverflow.com/questions/11901364/phpquery-dom-parser-changing-the-contents-inside-the-script-tag
    public static function replaceScripts(&$js_atricle_html){
        preg_match_all('/<script.*?>[\s\S]*?<\/script>/', $js_atricle_html, $tmp);
        $scripts_array = $tmp[0];
        foreach ($scripts_array as $script_id=>$script_item){
            $js_atricle_html = self::str_replace_once($script_item, '<div class="script_item_num_'.$script_id.'"></div>', $js_atricle_html);
        }

        return $scripts_array;
    }

    public static function unreplaceScripts($aticle_content, $scripts_array){
        preg_match_all('/<div class="script_item_num_(.*?)"><\/div>/', $aticle_content, $tmp);
        foreach ($tmp[1] as $script_num_item){
            $aticle_content = str_replace('<div class="script_item_num_'.$script_num_item.'"></div>', $scripts_array[$script_num_item], $aticle_content);
        }
        return $aticle_content;
    }

    public static function str_replace_once($search, $replace, $text)
    {
        $pos = strpos($text, $search);
        return $pos!==false ? substr_replace($text, $replace, $pos, strlen($search)) : $text;
    }
	
}