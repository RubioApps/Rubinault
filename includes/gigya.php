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
/**
 * Class for Gigya auth platform
 */
class Gigya
{
    /**
     * Login de l'utilisateur
     * Permet de récupérer le token puis dans un second temps le personId
     *
     * @param  string $username
     * @param  string $password
     * @return array ['GiyaToken', 'GiyaPersonId', GiyaIdToken', 'GiyaIdTokenTime'] or 'KO' or null
     */
    public static function login($username, $password)
    {
        $config     = Factory::getConfig();
        $url        = $config->gigya['url'];
        $apikey     = $config->gigya['apikey'];
        $headers    = ['Content-Type' => 'application/json'];
        $client     = new \GuzzleHttp\Client(['headers' => $headers]);

        $post    = ['form_params' => [
            'ApiKey'    => $apikey, 
            'loginID'   => $username, 
            'password'  => $password]
        ];
                
        try {
            $response   = $client->post($url . '/accounts.login', $post);  
            if($response->getStatusCode() != 200){
                $result = [ 'errors' => []];
                $result['errors'][] = [
                    'errorCode'     => $response->getStatusCode(),
                    'errorMessage'  => $response->getReasonPhrase()
                ];
            } else {                                    
                $json   = json_decode($response->getBody()->getContents(),true);
                self::_debug($json);
                $cookie  = $json['sessionInfo']['cookieValue']; 

                if ($info = self::getAccountInfo($cookie)) {
                    $result = [
                        'GiyaToken'         => $cookie,
                        'GiyaPersonId'      => $info['GiyaPersonId'],
                        'GiyaIdToken'       => $info['GiyaIdToken'] , 
                        'GiyaIdTokenTime'   => $info['GiyaIdTokenTime']
                    ];  
                } else {
                    return null;         
                }        
            }
            return $result;

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            self::_debug($e->getResponse()->getBody()->getContents());
            return $e->getResponse()->getBody()->getContents();
        } 


    }

    /**
     * Once we've got the token, we can retrieve the PersonId and the JWT
     *
     * @param  string $token
     * @return array ['GiyaPersonId', GiyaIdToken', 'GiyaIdTokenTime'] or null
     */

    private static function getAccountInfo($token)
    {
        $config     = Factory::getConfig();
        $url        = $config->gigya['url'];
        $apikey     = $config->gigya['apikey'];        
        $headers    = ['Content-Type' => 'application/vnd.api+json'];
        $client     = new \GuzzleHttp\Client(['headers' => $headers]);

        $params     = ['form_params' => [
            'ApiKey' => $apikey, 
            'login_token' => $token]
        ];

        $response   = $client->post($url . '/accounts.getAccountInfo', $params);
        $results    = $response->getBody()->getContents();
        $json       = json_decode($results,true);

        if ($json['statusCode'] != 200) {
            $ret = null;
        } else {            
            if ($jwt = self::getJwtToken($token)) {
                $ret = [
                    'GiyaPersonId'      => $json['data']['personId'],
                    'GiyaIdToken'       => $jwt['GiyaIdToken'] , 
                    'GiyaIdTokenTime'   => $jwt['GiyaIdTokenTime']
                ];                                
            } else {
                $ret = null;
            }
        }
        return $ret;
    }

    /**
     * JSON Web Token. This is used to keep alive the web session
     *
     * @param  string $token Giya token
     * @return array [GiyaIdToken,GiyaIdTokenTime] or null
     */

    public static function getJwtToken($token, $expiry = 86400)
    {
        $config     = Factory::getConfig();
        $url        = $config->gigya['url'];
        $apikey     = $config->gigya['apikey'];        
        $headers    = ['Content-Type' => 'application/json'];
        $client     = new \GuzzleHttp\Client(['headers' => $headers]);

        $params     = ['form_params' => [
            'ApiKey'        => $apikey,
            'login_token'   => $token,
            'fields'        => 'data.personId,data.gigyaDataCenter',
            'expiration'    => $expiry]];

        $response   = $client->post( $url . '/accounts.getJWT' , $params );
        $results    = $response->getBody()->getContents();
        $json       = json_decode($results,true);
        
        if ($json['statusCode'] !== 200) {
            $ret = null;
        } else {
            $ret = [
                'GiyaIdToken'       => $json['id_token'] , 
                'GiyaIdTokenTime'   => $json['time']
            ];
        }    
        return $ret;    
    }

    private static function _debug($message , $title = null)
    {
        $config = Factory::getConfig();
        if(!$config->debug) return;

        if(isset($title)) error_log($title . ':');

        if(is_object($message) || is_array($message))
        {
            error_log(print_r($message,true));
        } else {
            error_log(print_r($message));
        }

    }    
}
