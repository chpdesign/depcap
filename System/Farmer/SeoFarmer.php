<?php
namespace ComposerPack\System\Farmer;

use ComposerPack\Module\Language\Language;
use ComposerPack\System\Controller;
use ComposerPack\Module\Seo\Seo;
use ComposerPack\System\Session\Session;

class SeoFarmer extends FarmerInterface
{

	public function farmer($content = "") {
		//return $content;
        $scripts = self::replaceScripts($content);
		$html = \phpQuery::newDocumentHTML($content);
		$seo = Seo::where_url(Controller::$ACTIVE_URL,Language::language());
		if(!empty($seo) && !$seo->isNew())
		{
			$title = pq($html['html>head>title']);
			$seoTitle = $seo['title']."";
			if($seoTitle != "")
			{
				if($title->size() > 0)
				{
					$title->text($seoTitle);
				}
				else
				{
					pq($html['html>head'])->append('<title>'.$seoTitle.'</title>');
				}
			}

			foreach($seo['meta'] as $name => $content)
			{
			    $meta = pq($html['html>head>meta[name="'.$name.'"]']);
			    if($meta->size() == 0)
				    pq($html['html>head'])->append('<meta name="'.$name.'" content="'.$content.'" />');
			    else
			        $meta->attr('content', $content);
			    // <link rel="$rel" href="$href" /> link esetén
			}
		}
		return self::unreplaceScripts($html->htmlOuter(), $scripts);
	}

}