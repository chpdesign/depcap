<?php
namespace ComposerPack\System\Render;

use ComposerPack\System\System;
class MustacheRender implements RenderInterface
{
	
	/* (non-PHPdoc)
	 * @see \ComposerPack\System\RenderInterface::render()
	 */
	public static function render($template = "", array $data = array()) {
		if(file_exists($template))
		{
			$template = file_get_contents($template);
		}
		$rendered = System::getMustache()->render($template,$data);
		return $rendered;
	}

}