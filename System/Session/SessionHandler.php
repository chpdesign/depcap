<?php
namespace ComposerPack\System\Session;

use ComposerPack\System\Sql;

/**
 * SessionHandler kezelő osztály. Egyenlőre csak adatbázissal tud működni.
 * @author Nagy Gergely info@nagygergely.eu
 * @version 0.1.1
 *
 */
class SessionHandler implements \SessionHandlerInterface
{
    private $alive = true;
    protected $session_name = null;
    protected $connection = null;
    protected static $lol_sessions = [];

    public function __construct($name = null)
    {
        $this->connection = Sql::getDefaultDb();
        if ($name == null) {
            $name = session_name();
        }
        session_name($name);
        session_set_save_handler($this, true);

        session_start();
    }

    public function __destruct()
    {
        if ($this->alive) {
            session_write_close();
            $this->alive = false;
        }
    }

    public function create_sid() {
        $sid = bin2hex(openssl_random_pseudo_bytes(16));
        static::$lol_sessions[$sid] = [];
        return $sid;
    }

    public function delete()
    {
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }

        session_destroy();

        $this->alive = false;
    }

    public function open($save_path, $filename)
    {
        return true;
    }

    public function close()
    {
        return true;
    }

    public function read($sid)
    {
        $q = "SELECT `data` FROM `sessions` WHERE `id` = :sid LIMIT 1";
        $r = $this->connection->query($q, [':sid' => $sid]);

        if ($this->connection->rowCount($r) == 1) {
            $fields = $this->connection->fetch_assoc($r);

            return $this->decrypt($fields['data'], $this->session_name);
        } else {
            return '';
        }
    }

    public function write($sid, $data)
    {
        $q = "REPLACE INTO `sessions` (`id`, `data`) VALUES (:sid, :data)";
        return !!$this->connection->query($q, [':sid' => $sid, ':data' => $this->encrypt($data, $this->session_name)])->rowCount();
    }

    public function destroy($sid)
    {
        $q = "DELETE FROM `sessions` WHERE `id` = :sid";

        $_SESSION = array();

        return !!$this->connection->affected_rows($q, [':sid' => $sid]);
    }

    public function gc($expire)
    {
        $q = "DELETE FROM `sessions` WHERE DATE_ADD(`modified_at`, INTERVAL " . (int)$expire . " SECOND) < NOW()";

        return !!$this->connection->affected_rows($q);
    }

    /**
     * decrypt AES 256
     *
     * @param mixed $edata
     * @param string $password
     * @return mixed decrypted data
     */
    protected function decrypt($edata, $password)
    {
        $data = base64_decode($edata);
        $salt = substr($data, 0, 16);
        $ct = substr($data, 16);

        $rounds = 3; // depends on key length
        $data00 = $password . $salt;
        $hash = array();
        $hash[0] = hash('sha256', $data00, true);
        $result = $hash[0];
        for ($i = 1; $i < $rounds; $i++) {
            $hash[$i] = hash('sha256', $hash[$i - 1] . $data00, true);
            $result .= $hash[$i];
        }
        $key = substr($result, 0, 32);
        $iv = substr($result, 32, 16);

        return openssl_decrypt($ct, 'AES-256-CBC', $key, true, $iv);
    }

    /**
     * crypt AES 256
     *
     * @param mixed $data
     * @param string $password
     * @return string encrypted data
     */
    protected function encrypt($data, $password)
    {
        // Set a random salt
        $salt = openssl_random_pseudo_bytes(16);

        $salted = '';
        $dx = '';
        // Salt the key(32) and iv(16) = 48
        while (strlen($salted) < 48) {
            $dx = hash('sha256', $dx . $password . $salt, true);
            $salted .= $dx;
        }

        $key = substr($salted, 0, 32);
        $iv = substr($salted, 32, 16);

        $encrypted_data = openssl_encrypt($data, 'AES-256-CBC', $key, true, $iv);
        return base64_encode($salt . $encrypted_data);
    }
}