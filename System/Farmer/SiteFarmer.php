<?php
namespace ComposerPack\System\Farmer;

use ComposerPack\System\Run;
use ComposerPack\System\Settings;

class SiteFarmer extends FarmerInterface
{

	public function farmer($content = "") {
		// https://davidwalsh.name/remove-html-comments-php
		// Remove unwanted HTML comments
		// https://stackoverflow.com/a/11337360
		$content = preg_replace('/<!--(?!<!)[^\[>].*?-->/', '', $content);
		$content = preg_replace('/<!--(?!\[if).*?-->/', '', $content);
		$scripts = self::replaceScripts($content);
        $html = \phpQuery::newDocumentHTML($content);
        pq($html['header>meta'])->remove();
        $base = pq($html['html>head>base']);
        if($base->size() == 0)
        {
            pq($html['html>head'])->append('<base href="'.Settings::get("base_link").'">');
            $base = pq($html['html>head>base']);
        }
        $href = "";

        $namepsace = "";
        if(Settings::get("namespace") === true) {
            $run = Run::getCurrentInstance();
            $namepsace = mb_strtolower($run->getNamespace());
            $href = $namepsace;
        }

        if(!empty($href)) {
            foreach (pq($html['a:not([href^="http://"]):not([href^="https://"]):not([href^="/"]):not([href^="#"]):not([href^="javascript:"])']) as $a) {
                $h = pq($a)->attr('href');
                if (!empty($namepsace)) {
                    if (strpos($h, $namepsace . '/') === 0) {
                        $h = substr($h, strlen($namepsace) + 1);
                    } else if (strpos($h, $namepsace) === 0) {
                        $h = substr($h, strlen($namepsace));
                    }
                }
                pq($a)->attr('href', $href . '/' . $h);
            }
        }

		return self::unreplaceScripts($html->htmlOuter(), $scripts);
	}

}