<?php
namespace ComposerPack\System;

use ComposerPack\Module\Language\Language;

class Inflector extends \ICanBoogie\Inflector{

    /**
     * Returns an inflector for the specified locale.
     *
     * Note: Inflectors are shared for the same locale. If you need to alter an inflector you
     * MUST clone it first.
     *
     * @param string $locale
     *
     * @return \ICanBoogie\Inflector
     */
    static public function get($locale = null)
    {
        if(is_null($locale))
            $locale = Language::language();
        return parent::get($locale);
    }

    /**
     * Pluralizes a word if quantity is not one.
     *
     * @param int $quantity Number of items
     * @param string $singular Singular form of word
     * @param string $plural Plural form of word; function will attempt to deduce plural form from singular if not provided
     * @return string Pluralized word if quantity is not one, otherwise singular
     */
    public static function pluralizer($quantity, $singular, $plural) {
        if($quantity==1 || !strlen($singular)) return $singular;
        if($plural!==null) return $plural;

        $last_letter = strtolower($singular[strlen($singular)-1]);
        switch($last_letter) {
            case 'y':
                return substr($singular,0,-1).'ies';
            case 's':
                return $singular.'es';
            default:
                return $singular.'s';
        }
    }
}