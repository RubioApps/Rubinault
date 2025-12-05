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

use Rubinault\Framework\Request;
use Rubinault\Framework\Language\Text;
use Exception;

class Helpers
{
    /**
     * Get Vehicle Properties
     */
    public static function getInfo($vin, $scope = 'vin')
    {
        $array = [
            'images'     => [],
            'thumnails'  => [],
            'properties' => [],
            'others'     => []
        ];

        $hidden = [
            'family',
            'navigationAssistanceLevel',
            'easyConnectStore',
            'connectivityTechnology',
            'vcd',
            'retrievedFromDhs',
            'radioCode',
            'passToSalesDate',
            'modelSCR',
            'engineEnergyType',
            'yearsOfMaintenance'
        ];

        $task   = Factory::getTask();
        $user   = Factory::getUser();
        if ($user->isLogged()) {

            $data = $user->get();

            // Each vehicle in the profile
            foreach ($data->vehicles as $vehicle) {
                // Selected vehicle
                if ($vin == $vehicle['vin']) {

                    //Single info
                    if (in_array($scope, array_keys($vehicle))) {
                        return $vehicle[$scope];
                    }
                    //Details
                    foreach ($vehicle['vehicleDetails'] as $key => $prop) {
                        if (in_array($key, $hidden)) continue;
                        if (is_array($prop)) {
                            //Assets
                            if ($key == 'assets') {
                                foreach ($prop as $res) {
                                    $viewpoint = $res['viewpoint'];
                                    switch ($viewpoint) {
                                        case 'mybrand_2':
                                            //if($task != 'home' && $task != 'view' ) break;
                                            foreach ($res['renditions'] as $asset) {
                                                if ($asset['resolutionType'] == 'ONE_MYRENAULT_LARGE') {
                                                    $array['images'][] = $asset['url'];
                                                } else {
                                                    $array['thumbnails'][] = $asset['url'];
                                                }
                                            }
                                            break;
                                        case 'myb_plug_and_charge_my_car':
                                            //if($task != 'ev') break;
                                            foreach ($res['renditions'] as $asset) {
                                                if ($asset['resolutionType'] == 'ONE_MYRENAULT_LARGE') {
                                                    $array['images'][] = $asset['url'];
                                                } else {
                                                    $array['thumbnails'][] = $asset['url'];
                                                }
                                            }
                                            break;
                                    }
                                }
                                //Coded Properties                                
                            } else {
                                $array['properties'][$key] = [];
                                foreach ($prop as $p => $v) {
                                    $array['properties'][$key][$p] = $v;
                                }
                            }
                            //Single lines
                        } else {
                            if ($prop && !in_array($prop, $hidden)) {
                                if (is_bool($prop)) $prop = $prop ? 'TRUE' : 'FALSE';
                                $array['others'][$key] = $prop;
                            }
                        }
                    }
                    //Single info
                    if (in_array($scope, array_keys($array['others']))) {
                        return $array['others'][$scope];
                    } else {
                        return $array[$scope];
                    }
                }
            }
        }
        die('Not found');
    }

    /** 
     * Get a given property for a given vehicle    
     * @param string $vin VIN of the given vehicle
     * @param string $property The property to extract
     * @param string $islabel The type of return: code or label
     * @return string Returns the value of the requested property or null if not found
     */

    public static function getProperty($vin, $property, $islabel = false)
    {
        $props = self::getInfo($vin, 'properties');
        foreach ($props as $key => $prop) {
            if ($key == $property) {
                return $prop[$islabel ? 'label' : 'code'] ?? null;
            }
        }
    }

    /** 
     * Get a given property for a given vehicle    
     */

    public static function getCarModel($vin, $key = 'label')
    {
        return self::getProperty($vin, 'model', $key == 'label');
    }


    /**
     * Check whether this is a connected vehicle (gives access to the connected services) 
     */
    public static function isConnected($vin)
    {
        $result = false;
        if ($vin) {
            $code = self::getProperty($vin, 'tcu');
            switch ($code) {
                case 'SSTCU':
                    break;
                case 'AIVCT':
                    $result = true;
            }
        }
        return $result;
    }

    /**
     * Check whether this is an Electric Vehicle   (gives access to the EV connected services)
     */
    public static function isEV($vin)
    {
        if (!$vin) return false;
        $code = self::getProperty($vin, 'energy');
        switch ($code) {
            case 'ESS':
            case 'DIE':
                return false;
                break;
            case 'ELECX':
            default:
                return true;
        }
    }

    /**
     * Get attributes
     * 
     * @param string endpoint The endpoint to read
     * @param array query An array of key=>value to add to the GET query
     * @return object stdClass object or null
     */
    public static function getAttributes($endpoint, $query = [])
    {
        $config = Factory::getConfig();
        $user   = Factory::getUser();
        $vin    = Request::getVar('vin', null, 'GET');
        $isnew  = true;

        if (!$vin) return null;

        //Only works if the user is logged
        if ($user->isLogged()) {

            //Does the vin folder exist?
            if (!file_exists($user->userfolder .  DIRECTORY_SEPARATOR . $vin)) {
                mkdir($user->userfolder .  DIRECTORY_SEPARATOR . $vin);
            }

            //Set the attributes file
            $filename = $user->userfolder . DIRECTORY_SEPARATOR . $vin . DIRECTORY_SEPARATOR . 'attributes.json';

            //If the attributes file exists, returns the saved content for the given endpoint
            if (file_exists($filename)) {

                //Is the file obsolete (expired after 5 minutes)
                if (filemtime($filename) < strtotime('now') - $config->anti_throttle) {
                    unlink($filename);
                } else {
                    $content = file_get_contents($filename);
                    $locale = json_decode($content, true);
                    if (isset($locale[$endpoint])) {
                        $array = $locale[$endpoint];
                        $isnew = false;
                    }
                }
            }

            //If no locale, then try remote
            if ($isnew) {
                $array = [];
                if ($remote = Kamereon::Read($endpoint, $query)) $array  = json_decode((string) $remote, true);

                //Save the content if we called the remote API
                if (empty($locale)) $locale = [];
                file_put_contents($filename, json_encode(array_merge([$endpoint => $array], $locale)));
            }

            //Once we have the array of data from locale or remote
            if (isset($array['data'])) {
                $item = new \stdClass;
                foreach ($array['data']['attributes'] as $key => $attr) {
                    $item->$key = $attr;
                }

                return $item;
            } elseif (isset($array['errors'])) {
                return Helpers::extractErrors($array);
            }
        }
        return null;
    }

    /**
     *  Extract the message messages from a remote error    
     */
    public static function extractErrors($value, $messages = null)
    {
        if (!$value) return $messages;

        if (is_array($value) && isset($value['errors'])) {
            foreach ($value['errors'] as $err) {
                $messages .= self::extractErrors($err['errorMessage'], $messages);
            }
        } else if (is_string($value)) {
            if (json_validate($value)) {
                $array = json_decode($value, true);
                foreach ($array['errors'] as $err) {
                    if (isset($err['errorMessage'])) {
                        $messages .= self::extractErrors($err['errorMessage'], $messages);
                    }
                }
            } else {
                $messages .= $value;
            }
        }
        return $messages;
    }

    /**
     * Format Date into a formatted locale string
     */
    public static function formatDate($string, $format = 'YYYY-MM-DD')
    {
        $config = Factory::getConfig();
        $locale = $config->wired['locale'];
        $datetime = strtotime($string);

        $formatter = new \IntlDateFormatter($locale, \IntlDateFormatter::NONE, \IntlDateFormatter::NONE, NULL, NULL, $format);
        return $formatter->format($datetime);
    }

    /**
     * Encrypt a string.  This uses the encryption key defined in the configuration
     * @param string $string The text to be encrypted
     */
    public static function encrypt($string)
    {
        return $string;
        $config = Factory::getConfig();
        $method = 'AES-256-CBC';
        $key    = hash('sha256', $config->key);
        $iv     = substr(hash('sha256', IV_KEY), 0, 16);

        return base64_encode(openssl_encrypt($string, $method, $key, 0, $iv)) ?? null;
    }

    /**
     * Decrypt a string. This uses the encryption key defined in the configuration
     * @param string $string The text to be encrypted
     */
    public static function decrypt($string)
    {
        return $string;
        $config = Factory::getConfig();
        $method = 'AES-256-CBC';
        $key    = hash('sha256', $config->key);
        $iv     = substr(hash('sha256', IV_KEY), 0, 16);

        return openssl_decrypt(base64_decode($string), $method, $key, 0, $iv);
    }

    /**
     * Encode a string
     */
    public static function encode($string)
    {
        $encoding = mb_detect_encoding($string);
        $string = mb_convert_encoding($string, 'UTF-8', $encoding);

        $string = htmlentities($string, ENT_NOQUOTES, 'UTF-8');
        $string = preg_replace('#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $string);
        $string = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $string);
        $string = preg_replace('#&[^;]+;\.#', '', $string);
        $string = preg_replace('/[\s\%\'\"\,]+/', '-', $string);

        return strtolower($string);
    }

    /**
     * Decode a string
     */
    public static function decode($string)
    {
        $encoding = mb_detect_encoding($string);
        $string = mb_convert_encoding($string, 'UTF-8', $encoding);
        $array = preg_split('/[\s,\-]+/', $string);
        $array = array_map('ucfirst', $array);
        return join(' ', $array);
    }

    /**
     * Sanitize a filename, removing any character which is not a letter, number of space
     */
    public static function sanitize($string)
    {
        $string = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $string);
        $string = mb_ereg_replace("([\.]{2,})", '', $string);
        return $string;
    }

    public static function formatFileSize($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        // Uncomment one of the following alternatives
        $bytes /= pow(1024, $pow);
        // $bytes /= (1 << (10 * $pow)); 

        return round($bytes, $precision) . $units[$pow];
    }

    /**
     * Transform duration obtained from MiniDLNA (e.g. 0:04:15.026) and convert it into milliseconds
     * 
     * @param string $string Formatted MiniDLNA track duration
     * 
     * @return integer Milliseconds
     * 
     */
    public static function durationToMilliseconds($string)
    {
        if (!strlen($string) || !strstr($string, ':'))
            return 0;

        $parts = explode(':', $string);
        $n = count($parts);
        $H = 0;

        $ms = 1000 * (float) $parts[$n - 1];
        $m = (int) $parts[$n - 2];
        if ($n > 2) $H = (int) $parts[$n - 3];

        $time = (3600 * $H + 60 * $m) * 1000 + $ms;
        return $time;
    }

    /**
     * Formats milliseconds into a format hours minutes seconds (e.g. 5h 36m 4s)
     * 
     * @param integer $milliseconds 
     * 
     * @return string Formatted string
     * 
     */
    public static function formatMilliseconds($milliseconds)
    {

        if (!is_numeric($milliseconds)) return '';

        $seconds = floor($milliseconds / 1000);
        $minutes = floor($seconds / 60);
        $hours = floor($minutes / 60);
        $milliseconds = $milliseconds % 1000;
        $seconds = $seconds % 60;
        $minutes = $minutes % 60;

        if (!$hours) {
            if (!$minutes) {
                $format = '%us';
                $time = sprintf($format, $seconds);
            } else {
                $format = '%um %02us';
                $time = sprintf($format, $minutes, $seconds);
            }
        } else {
            $format = '%uh %um %02us';
            $time = sprintf($format, $hours, $minutes, $seconds);
        }

        return rtrim($time, '0');
    }

    public static function createThumbnail($filename, $target = null, $size = 150, $quality = 90)
    {
        // Deals only with jpeg      
        if (exif_imagetype($filename) != IMAGETYPE_JPEG) {
            return false;
        }

        if (empty($target))
            $target = $filename;

        // Convert old file into img
        $orig   = imagecreatefromjpeg($filename);
        $w      = imageSX($orig);
        $h      = imageSY($orig);

        // Create new image
        $new    = imagecreatetruecolor($size, $size);

        // The image is square, just issue resampled image with adjusted square sides and image quality
        if ($w == $h) {
            imagecopyresampled($new, $orig, 0, 0, 0, 0, $size, $size, $w, $w);

            // The image is vertical, use x side as initial square side
        } elseif ($w < $h) {
            $x = 0;
            $y = round(($h - $w) / 2);
            imagecopyresampled($new, $orig, 0, 0, $x, $y, $size, $size, $w, $w);

            // The image is horizontal, use y side as initial square side
        } else {
            $x = round(($w - $h) / 2);
            $y = 0;
            imagecopyresampled($new, $orig, 0, 0, $x, $y, $size, $size, $h, $h);
        }

        // Save it to the filesystem
        imagewebp($new, $target, $quality);

        // Destroys the images
        imagedestroy($orig);
        imagedestroy($new);

        return $target;
    }

    public static function UUID()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),

            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,

            // 48 bits for "node"
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }
}
