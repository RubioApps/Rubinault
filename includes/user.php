<?php

/**
 +-------------------------------------------------------------------------+
 | Rubinault  - Webapp Renault API                                         |
 | Version 1.0.0                                                           |
 |                                                                         |
 | This program is free software: you can redistribute it and/or modify    |
 | it under the terms of the GNU General Public License as published by    |
 | the Free Software Foundation.                                           |
 |                                                                         |
 | This file forms part of the Rubify software.                            |
 |                                                                         |
 | If you wish to use this file in another project or create a modified    |
 | version that will not be part of the Rubify Software, you               |
 | may remove the exception above and use this source code under the       |
 | original version of the license.                                        |
 |                                                                         |
 | This program is distributed in the hope that it will be useful,         |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of          |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the            |
 | GNU General Public License for more details.                            |
 |                                                                         |
 | You should have received a copy of the GNU General Public License       |
 | along with this program.  If not, see http://www.gnu.org/licenses/.     |
 |                                                                         |
 +-------------------------------------------------------------------------+
 | Author: Jaime Rubio <jaime@rubiogafsi.com>                              |
 +-------------------------------------------------------------------------+
 */

namespace Rubinault\Framework;


defined('_RBNOEXEC') or die;

use Rubinault\Framework\Gigya;
use Rubinault\Framework\Kamereon;
use Rubinault\Framework\Factory;
use Rubinault\Framework\Helpers;

use stdClass;

class User
{
    public $uuid;
    public $tokens;
    public $userfolder;
    protected $config;
    protected $credentials;
    protected $vehicles;
    protected $content;
    protected $data;
    protected $logged;

    public function __construct($params = null)
    {
        $this->config   = Factory::getConfig();
        $this->data     = $this->get();
    }

    public function __destruct() {}

    /**
     * Get the user object
     * @param string $uuid accountId. If empty, it will the logged accountId
     */
    public function get()
    {
        if (!isset($this->data) && $this->isLogged()) {
            $this->uuid     = $_SESSION['uuid'];
            $this->tokens   = $_SESSION['utokens'];
            $this->userfolder = RBNO_USERS . DIRECTORY_SEPARATOR . $_SESSION['uuid'];
            $this->_load();
        }
        return $this->data;
    }

    /**
     * Refresh JWT Token
     */

    public function refresh()
    {
        if ($this->uuid) {
            if ($tokens = Gigya::getJwtToken($this->tokens['GiyaIdToken'])) {
                $this->tokens = array_merge($this->tokens, $tokens);
                $_SESSION['utokens'] = $this->tokens;
            }
        }
    }

    /**
     * Login to Gygia
     * @param string $username  Username
     * @param string $password  Password
     */
    public function Login($username, $password)
    {

        $giyatokens = Gigya::login($username, $password);
        if (!$giyatokens || isset($giyatokens['errors'])) return false;

        //Get the accounts  
        $tokens = Kamereon::getAccounts($giyatokens);
        if (!$tokens) return false;

        //Store properties
        $this->uuid     = $tokens['accountId'];
        $this->tokens   = $tokens;

        //Set session
        $_SESSION['uuid']    = $this->uuid;
        $_SESSION['utokens'] = $this->tokens;

        //Save data   
        $this->userfolder = RBNO_USERS . DIRECTORY_SEPARATOR . $this->uuid;
        if (!file_exists($this->userfolder)) mkdir($this->userfolder);

        //Save credentials
        $this->credentials  = $this->userfolder .  DIRECTORY_SEPARATOR . 'credentials.json';
        if (file_exists($this->credentials)) unlink($this->credentials);
        file_put_contents($this->credentials, Helpers::encrypt(json_encode($tokens)));

        //Save vehicles through Kamereon
        $this->vehicles     = $this->userfolder .  DIRECTORY_SEPARATOR . 'vehicles.json';
        if (file_exists($this->vehicles)) unlink($this->vehicles);
        $this->content = Kamereon::getVehicles($this->uuid);
        file_put_contents($this->vehicles, Helpers::encrypt($this->content));
        return $this->_load();
    }

    /**
     * Checks whether a user is logged
     * @param string $uuid accountId. If empty, it will use the logged accountId
     */
    public function isLogged()
    {
        $this->logged = false;
        if (isset($_SESSION['uuid']) && isset($_SESSION['utokens'])) {

            $array  = $_SESSION['utokens'];
            if (!isset($array['GiyaIdTokenTime'])) return false;

            $now        = strtotime('now');
            $created    = strtotime($array['GiyaIdTokenTime']);

            if ($array['accountId'] == $_SESSION['uuid']) {
                if ($now - $created >= 900) {
                    self::refresh();
                }
                $this->uuid     = $_SESSION['uuid'];
                $this->tokens   = $_SESSION['utokens'];

                $this->userfolder = RBNO_USERS . DIRECTORY_SEPARATOR . $this->uuid;
                $this->credentials = $this->userfolder . DIRECTORY_SEPARATOR . 'credentials.json';

                if (!file_exists($this->credentials)) return false;

                $this->logged   = true;
            }
        }
        return $this->logged;
    }

    /**
     * Save the session of the identified user
     * 
     */

    public function Logon()
    {
        if (!isset($this->data)) return false;

        $_SESSION['uuid']   = $this->uuid;
        $_SESSION['utokens'] = $this->tokens;
        Factory::savePrefs();
        return true;
    }

    /**
     * Log off the current user
     */
    public function Logoff()
    {
        self::_removefolder();

        unset($this->uuid);
        unset($this->token);
        unset($this->data);

        unset($_SESSION['uuid']);
        unset($_SESSION['utokens']);

        setcookie('__Host_prefs', '', [
            'path'       => parse_url($this->config->live_site, PHP_URL_PATH),
            'secure'     => true,
            'httponly'   => true,
            'samesite'   => 'None',
            'expires'    =>  time() - 86400,
        ]);

        setcookie('__Host_sid', '', [
            'path'       => parse_url($this->config->live_site, PHP_URL_PATH),
            'secure'     => true,
            'httponly'   => true,
            'samesite'   => 'None',
            'expires'    =>  time() - 86400,
        ]);

        session_destroy();

        header('Location:' . Factory::Link());
        exit(0);
    }

    /**
     * Load the decrypted content of the users file
     */
    protected function _load()
    {
        $this->vehicles = $this->userfolder . DIRECTORY_SEPARATOR . 'vehicles.json';
        if ($this->vehicles !== null && file_exists($this->vehicles)) {
            $this->content = Helpers::decrypt(file_get_contents($this->vehicles));
            if (!$this->_parse()) return false;
            return true;
        }
        return false;
    }

    /**
     * Saves the content of the floating arrray into an encrypted file
     */
    protected function _save()
    {
        if (($fp = fopen($this->vehicles, 'r+')) !== false) {
            //Empty the file and close it                    
            ftruncate($fp, 0);
            fclose($fp);

            //Re-open in exclusive mode
            $fp = fopen($this->vehicles, 'a');
            flock($fp, LOCK_EX);

            //Save the new content and close
            fwrite($fp, Helpers::encrypt($this->_dump()));
            fclose($fp);
            return true;
        }
        return false;
    }

    /**
     * Parse the decrypted content and put it into the floating array
     */
    protected function _parse()
    {
        $json = json_decode($this->content, true);
        $item = new stdClass;
        $item->uuid         = $json['accountId'];
        $item->country      = $json['country'];
        $item->vehicles     = $json['vehicleLinks'];
        $this->data = $item;
        return $this->data;
    }

    /**
     * Put the content of the floating array into a string, ready to be encrypted
     */
    protected function _dump()
    {
        if (!$this->data)
            $this->data = $this->_parse();

        $buffer = [];
        $buffer['accountId']    = $this->data->uuid;
        $buffer['country']      = $this->data->country;
        $buffer['vehicleLinks'] = $this->data->vehicleLinks;

        $this->content = json_encode($buffer, JSON_FORCE_OBJECT);
        return $this->content;
    }

    /** 
     * Remove the user files into his folder
     */
    protected function _removefolder()
    {
        $dirPath = RBNO_USERS . DIRECTORY_SEPARATOR . $this->uuid;

        if (file_exists($dirPath) && is_dir($dirPath)) {
            $dir = new \RecursiveDirectoryIterator($dirPath, \RecursiveDirectoryIterator::SKIP_DOTS);
            $files = new \RecursiveIteratorIterator($dir, \RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($files as $file) {
                if ($file->isFile()) {
                    unlink($file->getPathname());
                } else {
                    rmdir($file->getPathname());
                }
            }
            rmdir($dirPath);
        }
    }
}
