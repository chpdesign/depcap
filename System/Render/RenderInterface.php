<?php
namespace ComposerPack\System\Render;

interface RenderInterface {
	
	/**
	 * Render a template or any other content that need rendering
	 * @param string $template file or content
	 * @param array $data
	 * @return string rendered content
	 */
	public static function render($template = "",array $data = array());
	
}