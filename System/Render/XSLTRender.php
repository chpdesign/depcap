<?php
namespace ComposerPack\System\Render;

class XSLTRender implements RenderInterface
{
	
	/* (non-PHPdoc)
	 * @see \ComposerPack\System\RenderInterface::render()
	 */
	public static function render($template = "", array $data = array()) {
		ob_start();
		
		# LOAD XML FILE
		$XML = new \DOMDocument();
		$XML->load( "" );
		
		# START XSLT
		$xslt = new \XSLTProcessor();
		
		# IMPORT STYLESHEET 1
		$XSL = new \DOMDocument();
		$XSL->load( $template );
		$xslt->importStylesheet( $XSL );
				
		#PRINT
		print $xslt->transformToXML( $XML );
		
		
		$rendered = ob_get_clean();
		return $rendered;
	}

}