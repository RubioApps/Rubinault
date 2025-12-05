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

use stdClass;

defined('_RBNOEXEC') or die;

class Kamereon
{

    /**
     * Get accounts from Kamereon with Giya tokens.
     *
     * @param  array $giya [GiyaToken,GiyaPersonId, GiyaIdToken,GiyaIdTokenTime]
     * @return array Null or $giya [GiyaToken,GiyaPersonId, GiyaIdToken,GiyaIdTokenTime,accountId]
     */

    public static function getAccounts($giyatokens)
    {
        $config     = Factory::getConfig();
        $country    = $config->wired['country'];
        $root       = $config->wired['url'];

        //Get the accounts remotely
        $personId   = $giyatokens['GiyaPersonId'];
        $url        = "/commerce/v1/persons/$personId";

        $options    = [
            'debug'   => $config->debug,
            'headers' => self::_headers($giyatokens),
            'query'   => ['country' => $country]
        ];

        $client     = new \GuzzleHttp\Client();
        try {
            $response = $client->get($root . $url, $options);
            if ($response->getStatusCode() != 200) {
                return null;
            } else {
                $result = $response->getBody()->getContents();
                $json   = json_decode($result, true);
                if (isset($json['accounts'])) {
                    //Add only first account to the Gygia session
                    $giyatokens['accountId'] = $json['accounts'][0]['accountId'];
                } else {
                    $giyatokens = null;
                }

                return $giyatokens;
            }
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            return null;
        }
    }

    /**
     * Get full infomations about vehicles     
     * @return string
     */

    public static function getVehicles($accountId)
    {
        //If the vehicles exist, return the file
        $filename = RBNO_USERS . DIRECTORY_SEPARATOR . $accountId . DIRECTORY_SEPARATOR . 'vehicles.json';
        if (file_exists($filename)) return file_get_contents($filename);

        $url = "/commerce/v1/accounts/$accountId/vehicles";
        if ($content = self::_get($url)) file_put_contents($filename, $content);
        return $content;
    }

    /**
     * Get a VIN Property (with no adapter KCA or KCM)
     * 
     * @param string $vin Involved VIN number
     * @param string $property Property to set
     * @param array $query Array of (key,value) to compose a query string
     * 
     * @return string Returns a JSON string or null if the call fails
     */
    public static function getProperty($vin, $property, $query = [])
    {
        $config = Factory::getconfig();
        $user   = Factory::getUser();

        if ($user->isLogged()) {

            //Does the vin folder exist?
            if (!file_exists($user->userfolder .  DIRECTORY_SEPARATOR . $vin)) {
                mkdir($user->userfolder .  DIRECTORY_SEPARATOR . $vin);
            }

            //If the contracts exist, return the file            
            $filename = $user->userfolder .  DIRECTORY_SEPARATOR . $vin . DIRECTORY_SEPARATOR . $property . '.json';
            if (file_exists($filename)) {
                if (filemtime($filename) < strtotime('now') - $config->anti_throttle) {
                    unlink($filename);
                } else {
                    return file_get_contents($filename);
                }
            }

            if (isset($user->uuid)) {
                $accountId  = $user->uuid;
                $url        = "/commerce/v1/accounts/$accountId/vehicles/$vin/$property";
                $query      = array_merge(['locale' => $config->wired['locale']], $query);

                if ($content = self::_get($url, $query)) file_put_contents($filename, $content);
                return $content;
            }
        }
        return null;
    }

    /**
     * Get a Property (no adapter KCA or KCM)
     * 
     * @param string $vin Involved VIN number
     * @param string $property Property to set
     * @param array $payload Array of data to post
     * 
     * @return string Returns a JSON string or null if the call fails
     */
    public static function setProperty($vin, $property, $payload = [])
    {
        if (isset($_SESSION['utokens'])) {
            $accountId = $_SESSION['utokens']['accountId'];
            $url = "/commerce/v1/accounts/$accountId/vehicles/$vin/$property";

            return self::_post($url, $payload);
        }
        return null;
    }


    /**
     * Generic call api for infos about a vehicle
     *
     * @param  string  $endpoint
     * @param array $query Array of data to query
     * 
     * @return object Returns a JSON object with the response
     */
    public static function Read($endpoint, $query = [])
    {
        $vin    = Request::getVar('vin', null, 'GET');
        $result = self::_mapGet($vin, $endpoint, $query);
        self::_debug($result);
        return $result;
    }

    /**
     * Trigger an action
     * 
     * @param object StdClass object that contains the mapping of the command
     * @param array $post Array of data to post
     * 
     * @return string Returns a JSON string with the response
     */
    public static function Write($endpoint, $post = [])
    {
        $vin    = Request::getVar('vin', null, 'GET');
        $result = self::_mapPost($vin, $endpoint, $post);
        self::_debug($result);
        return $result;
    }

    /**
     * Map a request
     * 
     * @param string $vin VIN of the given vehicle
     * @param string $endpoint Endpoint to request
     * @param array  $query Query string, as an associative array of pairs key => value
     * 
     * @return object Returns a stdClass object with the mapped response to be used by a model/template
     */

    protected static function _mapGet($vin, $endpoint, $query = [])
    {
        $config = Factory::getConfig();
        $user   = Factory::getUser();
        $isnew  = true;

        //Retrieve the map file for the given VIN/Model
        $model =  Helpers::getCarModel($vin, 'code');
        $filename = RBNO_MAPPING . DIRECTORY_SEPARATOR . $model . '.json';
        if (!$user->isLogged() || !file_exists($filename)) return null;

        //Decode the map
        $content = json_decode(file_get_contents(RBNO_MAPPING . DIRECTORY_SEPARATOR . $model . '.json'));

        //If the endpoint does not exists for the given model, returns null
        if (!property_exists($content, 'Endpoint') || !property_exists($content->Endpoint, $endpoint)) {
            return null;
        }

        //Get the map for the given endpoint
        $mapping = (array) $content->Endpoint->$endpoint;

        //Explorer the map
        $result =  [];
        foreach ($mapping as $map) {

            //Get the part that points the the response
            $path = explode('/', $map->response->path);

            //Does the vin folder exist?
            if (!file_exists($user->userfolder .  DIRECTORY_SEPARATOR . $vin)) {
                mkdir($user->userfolder .  DIRECTORY_SEPARATOR . $vin);
            }

            /*
            The result of the mapping is cached in a temporary file. This avoid to throttle the server with the same request
            If the file already exists and it is still valid (not expired) we will take the content instead of make a new request
            If the file does not exists yet or it is expired, we will make a new request and store the response in the cache file
            */

            $filename = $user->userfolder . DIRECTORY_SEPARATOR . $vin . DIRECTORY_SEPARATOR . 'attributes.json';

            //If the attributes file exists, returns the saved content for the given endpoint
            if (file_exists($filename)) {

                //Is the file obsolete (expired after X seconds. See config)
                if (filemtime($filename) < strtotime('now') - $config->anti_throttle) {
                    unlink($filename);
                    //If the file is still valid
                } else {
                    $content = file_get_contents($filename);
                    $locale = json_decode($content, true);
                    if (isset($locale[$map->name])) {
                        $array = $locale[$map->name];
                        foreach ($path as $chunk) {
                            if (!isset($array[$chunk])) {
                                if (isset($array['errors'])) {
                                    return Helpers::extractErrors($array);
                                }
                            } else {
                                $array = $array[$chunk];
                            }
                        }
                        $isnew = false;
                    }
                }
            }

            //If no locale, then try remote
            if ($isnew) {

                $array = [];

                //Get the remote
                $url    = self::_url($map);
                $remote = self::_get($url, $query);
                error_log(print_r($remote,true));
                $array  = json_decode($remote, true);

                //Once we have the array of data from locale or remote                        
                foreach ($path as $chunk) {
                    if (!isset($array[$chunk])) {
                        if (isset($array['errors'])) {
                            return Helpers::extractErrors($array);
                        }
                    } else {
                        $array = $array[$chunk];
                    }
                }

                //Save the content if we called the remote API
                if (empty($locale)) $locale = [];
                file_put_contents($filename, json_encode(array_merge([$map->name => $array], $locale)));
            }

            //Add the properties to the result    
            $result = array_merge($result, self::_dictionary($map, $array));
        }
        return (object) $result;
    }

    /**
     * Map a command depending on the model
     * 
     * @param string $vin VIN of the given vehicle
     * @param string $command Command you want to trigger
     * @param array $post Array of arguments, usually $_POST
     * 
     * @return string Returns a JSON string to be used by Kamereon
     */

    protected static function _mapPost($vin, $command, $post = [])
    {
        $user      = Factory::getUser();
        $model     = Helpers::getCarModel($vin, 'code');
        $filename  = RBNO_MAPPING . DIRECTORY_SEPARATOR . $model . '.json';

        if (!$user->isLogged() || !file_exists($filename)) return null;

        $content = json_decode(file_get_contents(RBNO_MAPPING . DIRECTORY_SEPARATOR . $model . '.json'));

        //If the action does not exists for the given model, returns null
        if (!property_exists($content, 'Action') || !property_exists($content->Action, $command)) {
            return null;
        }

        //We map the command
        $mapping = $content->Action->$command;
        $mapped = new \stdClass;
        $mapped->type    = $command;
        $mapped->payload = [];

        //We store each key into the object
        foreach ($mapping as $key => $map) {

            //For the key 'payload', we will replace the wildcard % by the given value from the argument $post
            if ($key == 'payload') {
                foreach ($map as $pair) {
                    if (isset($post[$pair->key]) && $pair->value == '%') $pair->value = $post[$pair->key];
                    $mapped->payload[$pair->key] = $pair->value;
                }
            } else {
                $mapped->$key = $map;
            }
        }

        //Build the target URL
        $url    = self::_url($mapped);

        //Build the payload
        $payload = ['data' => [
            'type'      => $mapped->type,
            'id'        => 'guid',
            'attributes' => $mapped->payload
        ]];

        //Make the post
        return self::_post($url, $payload);
    }

    /**
     * Get the mapped field name based on the dictionary of the response
     * 
     * @param object $map stdClass object that contains the mapping
     * @param array $array Array thart contains the remote response
     * 
     * @return array Returns an array with the mapped labels
     */
    protected static function _dictionary($map, $array)
    {
        $result = [];
        foreach ($array as $key => $value) {
            $found = false;
            if (is_array($value)) {
                $result[$key] = self::_dictionary($map, $value);
            } else {
                foreach ($map->response->dictionary as $prop => $v) {
                    if (strstr($v, $key) !== false) {
                        $result[$prop] = $value;
                        $found = true;
                        break;
                    }
                }
                if (!$found) $result[$key] = $value;
            }
        }
        return $result;
    }

    /**
     * Prepare url 
     */
    private static function _url($map)
    {
        $user       = Factory::getUser();
        $accountId  = $user->tokens['accountId'];
        $vin        = Request::getVar('vin', null, 'GET');
        $endpoint   = $map->name;
        $version    = $map->version ?? 1;
        $adapter    = $map->adapter ?? 'kca/car-adapter';

        //Select the branch depending on the given adapter
        switch ($adapter) {
            case 'kcm':
                $branch = 'vehicles';
                break;
            case 'kca/car-adapter':
            default:
                $branch = 'cars';
                break;
        }
        //Format result
        return "/commerce/v1/accounts/$accountId/kamereon/$adapter/v$version/$branch/$vin/$endpoint";
    }

    /**
     * Prepare headers
     * 
     * @return array
     */
    private static function _headers($tokens = null)
    {
        $config     = Factory::getConfig();
        $apikey     = $config->wired['apikey'];

        if (!isset($tokens)) $tokens = $_SESSION['utokens'];

        $headers = [
            'Content-Type'      => 'application/vnd.api+json',
            'apikey'            => $apikey,
            'x-gigya-id_token'  => $tokens['GiyaIdToken'], //JWT             
        ];
        return $headers;
    }

    /**
     * Generic call to API
     *
     * @param  string $url
     * @return string JSON
     */
    private static function _get($url, $query = [])
    {
        $config     = Factory::getConfig();
        $country    = $config->wired['country'];
        $locale     = $config->wired['locale'];
        $root       = $config->wired['url'];
        $options    = [
            'debug'         => $config->debug,
            'http_errors'   => false,
            'headers'       => self::_headers(),
            'query'         => array_merge(['country' => $country, 'locale' => $locale], $query),
        ];

        $client     = new \GuzzleHttp\Client();
        try {

            $response = $client->get($root . $url, $options);

            self::_debug($url, 'GET');
            self::_debug($response, 'Response');

            if ($response->getStatusCode() !== 200) {
                $result = ['success' => false, 'reason' => $response->getStatusCode(), 'url' => $root . $url, 'errors' => []];
                $result['errors'][] = [
                    'errorCode'     => $response->getStatusCode(),
                    'errorMessage'  => $response->getReasonPhrase()
                ];
            } else {
                $body = $response->getBody();
                $body->rewind();
                $contents = $body->getContents();
                $array  = json_decode($contents, true);
                if (isset($array['errors'])) {
                    $result = ['success' => false, 'reason' => 'Errors found', 'url' => $root . $url, 'errors' => $array['errors']];
                } else {
                    $result = array_merge(['success' => true], $array);
                }
            }
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            if ($e->hasResponse()) {
                self::_debug($e->getResponse()->getBody(), 'Exception');
                $reason = $e->getResponse()->getReasonPhrase();
                $array = json_decode($e->getResponse()->getBody(), true);
            } else {
                $reason = 'Missing response';
                $array = ['errors' => ''];
            }
            $result = ['success' => false, 'reason' => $reason, 'url' => $root . $url, 'errors' => $array['errors']];
        }
        return json_encode($result, JSON_PRETTY_PRINT);
    }

    /**
     * Generic call to API
     *
     * @param  string $url
     * @param  array  $payload
     * @return string JSON
     */
    private static function _post($url, $payload = [])
    {

        $config     = Factory::getConfig();
        $root       = $config->wired['url'];
        $country    = $config->wired['country'];
        $locale     = $config->wired['locale'];
        $headers    = self::_headers();
        $query      = ['country' => $country, 'locale' => $locale];
        $length     = ['Content-Length' => strlen(json_encode($payload))];

        $options    = [
            'debug'         => $config->debug,
            'http_errors'   => false,
            'headers'       => array_merge($headers, $length),
            'query'         => $query,
            'body'          => json_encode($payload),
        ];

        $client     = new \GuzzleHttp\Client();

        try {
            $response = $client->post($root . $url, $options);

            self::_debug($url, 'POST');
            self::_debug($response, 'Response');

            if ($response->getStatusCode() != 200) {
                $result = ['success' => false, 'reason' => $response->getStatusCode(), 'url' => $root . $url, 'errors' => []];
                $result['errors'][] = [
                    'errorCode'     => $response->getStatusCode(),
                    'errorMessage'  => $response->getReasonPhrase()
                ];
            } else {
                $body = $response->getBody();
                $body->rewind();
                $contents = $body->getContents();
                $array  = json_decode($contents, true);
                if (isset($array['errors'])) {
                    $result = ['success' => false, 'reason' => 'Errors found', 'url' => $root . $url, 'errors' => Helpers::extractErrors($array)];
                } else {
                    $result = array_merge(['success' => true], $array);
                }
            }
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            if ($e->hasResponse()) {
                self::_debug($e->getResponse()->getBody(), 'Exception');
                $reason = $e->getResponse()->getReasonPhrase();
                $array = json_decode($e->getResponse()->getBody(), true);
            } else {
                $reason = 'Missing response';
                $array = ['errors' => ''];
            }
            $result = ['success' => false, 'reason' => $reason, 'url' => $root . $url, 'errors' => Helpers::extractErrors($array)];
        }
        return json_encode($result, JSON_PRETTY_PRINT);
    }

    private static function _debug($message, $title = null)
    {
        $config = Factory::getConfig();
        if (!$config->debug) return;

        if (isset($title)) error_log($title . ':');

        if (is_object($message) || is_array($message)) {
            error_log(print_r($message, true));
        } else {
            error_log(print_r($message, true));
        }
    }
}
/*
{
    "type":"FUNCTIONAL",
    "messages":[
        {
            "code":"err.func.access.denied.for.this.resource",
            "message":"{\"errors\":[{\"errorCode\":\"400900\",\"errorMessage\":\"Validation error on sendNavigationRequest, field 'destinations': must not be empty\",\"errorLevel\":\"error\",\"errorType\":\"functional\"}],\"error_reference\":\"46a033bc-e8e9-4c9d-aae3-c6c6912b80da3613609\"}"
        }],
    "errors":[
        {
            "errorCode":"err.func.access.denied.for.this.resource",
            "errorMessage":"{\"errors\":[{\"errorCode\":\"400900\",\"errorMessage\":\"Validation error on sendNavigationRequest, field 'destinations': must not be empty\",\"errorLevel\":\"error\",\"errorType\":\"functional\"}],\"error_reference\":\"46a033bc-e8e9-4c9d-aae3-c6c6912b80da3613609\"}"
        }],
    "error_reference":"FUNCTIONAL"
}
    */
