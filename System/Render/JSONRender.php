<?php

namespace ComposerPack\System\Render;

class JSONRender implements RenderInterface
{
    /* (non-PHPdoc)
     * @see \ComposerPack\System\RenderInterface::render()
     */
    public static function render($template = "", array $data = array())
    {
        return json_encode($data);
    }

}