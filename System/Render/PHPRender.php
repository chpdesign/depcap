<?php
namespace ComposerPack\System\Render;

class PHPRender implements RenderInterface
{
	
	/* (non-PHPdoc)
	 * @see \ComposerPack\System\RenderInterface::render()
	 */
	public static function render($template = "", array $data = array()) {
		ob_start();
		if(file_exists($template))
		{
			if(!empty($data))
				extract($data);
			include $template;
		}
		else
		{
			if(!empty($data))
				extract($data);
			eval('?>'.$template);
		}
		$rendered = ob_get_clean();
		return $rendered;
	}

}