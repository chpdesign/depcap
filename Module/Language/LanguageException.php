<?php
namespace ComposerPack\Module\Language;

use Throwable;

class LanguageException extends \Exception
{

    protected $language = null;

    public function __construct($id = "", $lang = null, array $langs = [], $code = 0, Throwable $previous = null)
    {
        parent::__construct($id, $code, $previous);
        $this->language = new Language(array_merge(["id" => $id], $langs));
        $this->language->setLanguage($lang);
    }

    public function getLanguage()
    {
        return $this->language;
    }

    public function __toString()
    {
        return $this->language."";
    }

}