<?php
namespace ComposerPack\System;

use PHPMailer\PHPMailer\PHPMailer;

class Email extends PHPMailer
{
    // HTML levelek alapértelmezetten:
    public $ContentType = 'text/html';
    // Alapértelmezetten utf-8 kódolású levelek küldése
    public $CharSet = 'UTF-8';

    public function __construct($exceptions = false)
    {
        parent::__construct($exceptions);

        // Ha vannak FW beállítások, akkor át kell őket hozni az osztályba:
        if (!empty(Settings::$config['smtp'])) {
            $this->Mailer = 'smtp';
            $this->SMTPAuth = true;
            $this->Host = Settings::$config['smtp']['host'];
            $this->Port = Settings::$config['smtp']['port'];
            $this->Username = Settings::$config['smtp']['user'];
            $this->Password = Settings::$config['smtp']['password'];
            // Ha beállított a 'secure' érték, akkor az is megy a levesbe:
            if (!empty(Settings::$config['smtp']['secure']))
                $this->SMTPSecure = Settings::$config['smtp']['secure'];
        }
    }


    /**
     * Levél elküldése!
     * Paraméterben tömbként megadható adatok felépítése:
     * Array(
     *  'from' => küldő email címe
     *  'fromname' => küldő neve
     *  'address' => megadási formái az address() metódusnál, kivéve a 2-at
     *  'html' => true|false
     *  'message' => üzenet szövege
     * );
     *
     * @param array $data
     * @return bool sikeres volt-e a levélküldés!
     */
    public function send($data = array())
    {
        if (isset($data['from'])) {
            if (!isset($data['fromname'])) $data['fromname'] = '';
            $this->from($data['from'], $data['fromname']);
        }

        if (isset($data['address'])) $this->address($data['address']);

        if (isset($data['html'])) parent::IsHTML($data['html']);

        if (isset($data['message'])) $this->message($data['message']);

        return parent::Send();
    }


    /**
     * Küldő beállítása email, név adatokkal.
     * Ha nem akarjuk, hogy automatikusan a válasz címhez is bekerüljön, akkor
     * a 3. paramétert false-ra kell állítani
     *
     * @param string $address
     * @param string $name
     * @param bool $auto
     * $return object|FALSE sikeres beállítás esetén magával az objektummal, egyébként FALSE
     */
    public function from($address, $name = '', $auto = true)
    {
        if (parent::setFrom($address, $name, $auto))
            return $this;
        else return false;
    }


    /**
     * Többféle megadási mód:
     *
     * 1. Egy cím név nélkül:
     *      ->address('info@domain.com');
     *      ->address(array('info@domain.com'));
     * 2. Egy cím és egy név:
     *      ->address('info@domain.com', 'John Doe');
     *      ->address(array('John Doe' => 'info@domain.com'));
     * 4. Több cím nevekkel, vagy név nélkül
     *      ->address(
     *        array(
     *            'John Doe' => 'info@domain.com',
     *            'info@domain.com',
     *            ...)
     *         );
     *
     * @param string|array $address
     * @param string|null $name
     * @return object instance
     */
    public function address($address, $name = null)
    {
        // Ha az 1. paraméter szöveg:
        if (is_string($address)) {
            if (is_null($name)) $name = '';
            parent::AddAddress($address, $name);
        }
        // Ha az 1. paraméter tömb:
        if (is_array($address)) {
            foreach ($address as $name => $email) {
                // Ha a tömb elem indexe nem szöveges:
                if (is_numeric($name)) $name = '';
                parent::AddAddress($email, $name);
            }
        }
        return $this;
    }


    /**
     * Üzenet szövegének megadása!
     *
     * @param string $message üzenet szövege
     * @param string $basedir alap könyvtár a szövegben lévő linkek átalakításához
     * @return object instnce
     */
    public function message($message, $basedir = '')
    {
        static::MsgHTML($message, $basedir);
        return $this;
    }


    /**
     * Az üzenet szövegének beállításának kiegészítése a relatív útvonalak
     * kezelésével, amivel nem bírkózott meg a phpmailer
     *
     * {@inheritDoc}
     * @see PHPMailer::msgHTML()
     */
    public function msgHTML($message, $basedir = '', $advanced = false)
    {
        $message = preg_replace_callback('/(src|background)(=["\'])(.*)(["\'])/Ui', function ($matches) {

            $url = $matches[3];
            if (!preg_match('#^/|https?://^/#', $url)) {
                $matches[3] = get('base_link') . $url;
            }
            unset($url);

            return implode('', array_slice($matches, 1));
        }, $message);

        return parent::msgHTML($message, $basedir, $advanced);
    }


    /**
     * Tárgy megadása
     *
     * @param string $subject
     * @return object instnce
     */
    public function subject($subject)
    {
        $this->Subject = $subject;
        return $this;
    }
}