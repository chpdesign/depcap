<?php
namespace ComposerPack\Module\Currency;

use ComposerPack\System\ORM;
use ComposerPack\System\Session\Session;

/**
 * Pénznem osztály
 * @package ComposerPack\Model
 */
class Currency extends ORM{

    protected $table = "currency";

    protected $default_primary_key = array('id');

    public function __toString()
    {
        return $this["code"].'';
    }

    public static function currency($currency = null)
    {
        if(is_null($currency)) {
            $currency = new Currency();
            $currency = $currency->order_by_field("sort", "asc")->first();
            return Session::get('currency', $currency);
        }
        else
            Session::set('currency', $currency);
        return $currency;
    }

    public static function getActiveCurrencies()
    {
        $currency = new Currency();
        return $currency->where("status", 1)->order_by_field("sort", "asc")->result();
    }
}