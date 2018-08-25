<?php
namespace ComposerPack\Module\Language;

use ComposerPack\System\ORM;
use ComposerPack\System\Session\Session;
use ComposerPack\System\Settings;

class Language extends ORM
{

	protected $default_primary_key = ['id'];
	protected $table = 'language';

	protected $language = null;

	public function setLanguage($language = null)
    {
        if(empty($language)) {
            $language = Language::language();
        }
        $this->language = $language;
    }

	public function getLanguage()
    {
        if(empty($this->language)) {
            $this->language = Language::language();
        }
        return $this->language;
    }

	public function __toString()
    {
        return $this[$this->getLanguage()].'';
    }

    public static function language($language = null)
    {
        return "en";
        if(is_null($language)) {
            $language = self::getDefaultLanguage();
            return Session::get('language', $language);
        } else {
            Session::set('language', $language);
        }
        return $language;
    }


    /**
     * @var array
     */
    public static $_current_translate_file = [];

    /**
     * @var Language
     */
    protected static $self = null;

    protected static function initialize()
    {
        if(self::$self == null)
            self::$self = new Language();

        if(!isset(static::$_current_translate_file[self::language()])){
            $json = [];
            $file = Settings::get("base_dir").DS."public".DS."lang".DS.self::language().".json";
            if(file_exists($file))
            {
                $j = file_get_contents($file);
                $json = array_merge($json,json_decode($j,true));
            }
            static::$_current_translate_file[self::language()] = !empty($json) ? $json : array();
        }
    }


    public static function getLangArray()
    {
        return array(
            'aa' => 'Afar',
            'ab' => 'Abkhaz',
            'ae' => 'Avestan',
            'af' => 'Afrikaans',
            'ak' => 'Akan',
            'am' => 'Amharic',
            'an' => 'Aragonese',
            'ar' => 'Arabic',
            'as' => 'Assamese',
            'av' => 'Avaric',
            'ay' => 'Aymara',
            'az' => 'Azerbaijani',
            'ba' => 'Bashkir',
            'be' => 'Belarusian',
            'bg' => 'Bulgarian',
            'bh' => 'Bihari',
            'bi' => 'Bislama',
            'bm' => 'Bambara',
            'bn' => 'Bengali',
            'bo' => 'Tibetan Standard, Tibetan, Central',
            'br' => 'Breton',
            'bs' => 'Bosnian',
            'ca' => 'Catalan; Valencian',
            'ce' => 'Chechen',
            'ch' => 'Chamorro',
            'co' => 'Corsican',
            'cr' => 'Cree',
            'cs' => 'Czech',
            'cu' => 'Old Church Slavonic, Church Slavic, Church Slavonic, Old Bulgarian, Old Slavonic',
            'cv' => 'Chuvash',
            'cy' => 'Welsh',
            'da' => 'Danish',
            'de' => 'German',
            'dv' => 'Divehi; Dhivehi; Maldivian;',
            'dz' => 'Dzongkha',
            'ee' => 'Ewe',
            'el' => 'Greek, Modern',
            'en' => 'English',
            'eo' => 'Esperanto',
            'es' => 'Spanish; Castilian',
            'et' => 'Estonian',
            'eu' => 'Basque',
            'fa' => 'Persian',
            'ff' => 'Fula; Fulah; Pulaar; Pular',
            'fi' => 'Finnish',
            'fj' => 'Fijian',
            'fo' => 'Faroese',
            'fr' => 'French',
            'fy' => 'Western Frisian',
            'ga' => 'Irish',
            'gd' => 'Scottish Gaelic; Gaelic',
            'gl' => 'Galician',
            'gn' => 'GuaranÃ­',
            'gu' => 'Gujarati',
            'gv' => 'Manx',
            'ha' => 'Hausa',
            'he' => 'Hebrew (modern)',
            'hi' => 'Hindi',
            'ho' => 'Hiri Motu',
            'hr' => 'Croatian',
            'ht' => 'Haitian; Haitian Creole',
            'hu' => 'Hungarian',
            'hy' => 'Armenian',
            'hz' => 'Herero',
            'ia' => 'Interlingua',
            'id' => 'Indonesian',
            'ie' => 'Interlingue',
            'ig' => 'Igbo',
            'ii' => 'Nuosu',
            'ik' => 'Inupiaq',
            'io' => 'Ido',
            'is' => 'Icelandic',
            'it' => 'Italian',
            'iu' => 'Inuktitut',
            'ja' => 'Japanese (ja)',
            'jv' => 'Javanese (jv)',
            'ka' => 'Georgian',
            'kg' => 'Kongo',
            'ki' => 'Kikuyu, Gikuyu',
            'kj' => 'Kwanyama, Kuanyama',
            'kk' => 'Kazakh',
            'kl' => 'Kalaallisut, Greenlandic',
            'km' => 'Khmer',
            'kn' => 'Kannada',
            'ko' => 'Korean',
            'kr' => 'Kanuri',
            'ks' => 'Kashmiri',
            'ku' => 'Kurdish',
            'kv' => 'Komi',
            'kw' => 'Cornish',
            'ky' => 'Kirghiz, Kyrgyz',
            'la' => 'Latin',
            'lb' => 'Luxembourgish, Letzeburgesch',
            'lg' => 'Luganda',
            'li' => 'Limburgish, Limburgan, Limburger',
            'ln' => 'Lingala',
            'lo' => 'Lao',
            'lt' => 'Lithuanian',
            'lu' => 'Luba-Katanga',
            'lv' => 'Latvian',
            'mg' => 'Malagasy',
            'mh' => 'Marshallese',
            'mi' => 'Maori',
            'mk' => 'Macedonian',
            'ml' => 'Malayalam',
            'mn' => 'Mongolian',
            'mr' => 'Marathi (Mara?hi)',
            'ms' => 'Malay',
            'mt' => 'Maltese',
            'my' => 'Burmese',
            'na' => 'Nauru',
            'nb' => 'Norwegian BokmÃ¥l',
            'nd' => 'North Ndebele',
            'ne' => 'Nepali',
            'ng' => 'Ndonga',
            'nl' => 'Dutch',
            'nn' => 'Norwegian Nynorsk',
            'no' => 'Norwegian',
            'nr' => 'South Ndebele',
            'nv' => 'Navajo, Navaho',
            'ny' => 'Chichewa; Chewa; Nyanja',
            'oc' => 'Occitan',
            'oj' => 'Ojibwe, Ojibwa',
            'om' => 'Oromo',
            'or' => 'Oriya',
            'os' => 'Ossetian, Ossetic',
            'pa' => 'Panjabi, Punjabi',
            'pi' => 'Pali',
            'pl' => 'Polish',
            'ps' => 'Pashto, Pushto',
            'pt' => 'Portuguese',
            'qu' => 'Quechua',
            'rm' => 'Romansh',
            'rn' => 'Kirundi',
            'ro' => 'Romanian, Moldavian, Moldovan',
            'ru' => 'Russian',
            'rw' => 'Kinyarwanda',
            'sa' => 'Sanskrit (Sa?sk?ta)',
            'sc' => 'Sardinian',
            'sd' => 'Sindhi',
            'se' => 'Northern Sami',
            'sg' => 'Sango',
            'si' => 'Sinhala, Sinhalese',
            'sk' => 'Slovak',
            'sl' => 'Slovene',
            'sm' => 'Samoan',
            'sn' => 'Shona',
            'so' => 'Somali',
            'sq' => 'Albanian',
            'sr' => 'Serbian',
            'ss' => 'Swati',
            'st' => 'Southern Sotho',
            'su' => 'Sundanese',
            'sv' => 'Swedish',
            'sw' => 'Swahili',
            'ta' => 'Tamil',
            'te' => 'Telugu',
            'tg' => 'Tajik',
            'th' => 'Thai',
            'ti' => 'Tigrinya',
            'tk' => 'Turkmen',
            'tl' => 'Tagalog',
            'tn' => 'Tswana',
            'to' => 'Tonga (Tonga Islands)',
            'tr' => 'Turkish',
            'ts' => 'Tsonga',
            'tt' => 'Tatar',
            'tw' => 'Twi',
            'ty' => 'Tahitian',
            'ug' => 'Uighur, Uyghur',
            'uk' => 'Ukrainian',
            'ur' => 'Urdu',
            'uz' => 'Uzbek',
            've' => 'Venda',
            'vi' => 'Vietnamese',
            'vo' => 'VolapÃ¼k',
            'wa' => 'Walloon',
            'wo' => 'Wolof',
            'xh' => 'Xhosa',
            'yi' => 'Yiddish',
            'yo' => 'Yoruba',
            'za' => 'Zhuang, Chuang',
            'zh' => 'Chinese',
            'zu' => 'Zulu',
        );
    }

    /**
     * Visszatér egy adott fordítás aktuális értékével
     * @param string $name akutális fordítás neve
     * @param string $lang akutális nyelv ha null az oldal alapértelmezett értékét veszi figyelembe!
     * @param array $langs ha nincs sehol meg akkor ebből a tömbből dolgozik természetesen ha nem üres.
     * @return string
     */
    public static function lang( $name = null, $lang = null, array $langs  = array())
    {
        self::initialize();

        if(func_num_args() == 2 && is_array($lang))
        {
            $langs = $lang;
            $lang = null;
        }
        if($name == null) return null;
        $lang = ($lang == null) ? self::language() : $lang;
        $value = new Language($name);
        if(!$value->isnew()) {
            return $value[$lang];
        }
        $found = false;
        $value = new Language();
        foreach (static::$_current_translate_file as $l => $clangs)
        {
            if(isset($clangs[$name])) {
                $value[$l] = $clangs[$name];
                $found = true;
            } else if(isset($langs[$l])) {
                $value[$l] = $langs[$l];
                $found = true;
            }
        }
        if($found)
            return $value[$lang];
        if(!empty($langs))
        {
            $newLang = new Language();
            foreach($langs as $key => $value)
            {
                $newLang[$key] = $value;
            }
            $newLang['id'] = $name;
            if($newLang->save())
            {
                return $newLang[$lang];
            }
        }
        return $name;
    }

    /**
     * Vissaadja a megadott name-hez tartozó össze fordítási rekordot hu,en,de,stb...
     * @param string $name
     * @return Language
     */
    public static function langs( $name = null )
    {
        self::initialize();

        if($name == null)
        {
            return new Language();
        }
        if($name instanceof Language)
            return $name;
        $lang = new Language($name);
        if($lang->isnew())
        {
            foreach (static::$_current_translate_file as $l => $clangs)
            {
                if(isset($clangs[$name])) {
                    $lang[$l] = $clangs[$name];
                }
            }
        }
        return $lang;
    }

    /**
     * Visszaadja az összes lehetséges fordítási nyelvet [hu,de,en] pl....
     * @return string[]
     */
    public static function get_langs()
    {
        self::initialize();

        //$langs_query = self::$self->connection->query("SELECT * FROM `".self::__table()."` LIMIT 1");
        $count_langs = self::$self->connection->columns(self::$self->table);
        if(is_bool($count_langs)) return array();
        array_shift($count_langs);
        array_shift($count_langs);
        array_pop($count_langs);
        array_pop($count_langs);
        $count_langs = array_values($count_langs);
        return $count_langs;
    }

    #########################################################
    # Copyright © 2008 Darrin Yeager                        #
    # http://www.dyeager.org/                               #
    # Licensed under BSD license.                           #
    #   http://www.dyeager.org/downloads/license-bsd.php    #
    #########################################################

    /**
     * Vissza adja az aktuális nyelvet. Oldal nyelve jelenleg.
     * @return string
     */
    public static function getDefaultLanguage() {
        if (isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]))
            return substr(self::parseDefaultLanguage($_SERVER["HTTP_ACCEPT_LANGUAGE"]),0,2);
        else
            return reset(self::get_langs());
    }

    private static function parseDefaultLanguage($http_accept) {
        $deflang = null;
        if(isset($http_accept) && strlen($http_accept) > 1)  {
            # Split possible languages into array
            $x = explode(",",$http_accept);
            foreach ($x as $val) {
                #check for q-value and create associative array. No q-value means 1 by rule
                if(preg_match("/(.*);q=([0-1]{0,1}\.\d{0,4})/i",$val,$matches))
                    $lang[$matches[1]] = (float)$matches[2];
                else
                    $lang[$val] = 1.0;
            }

            #return default language (highest q-value)
            $qval = 0.0;
            foreach ($lang as $key => $value) {
                if ($value > $qval) {
                    $qval = (float)$value;
                    $deflang = $key;
                }
            }
        }
        return strtolower($deflang);
    }

}
