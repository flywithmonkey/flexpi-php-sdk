<?php

/**
 * Copyright 2012 Fly With Monkey Sp. z o.o.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Flexpi;

use Flexpi\Exception\FlexpiApiException;
use Buzz;

if (!function_exists('curl_init')) {
  throw new Exception('Flexpi PHP-SDK needs the CURL PHP extension.');
}
if (!function_exists('json_decode')) {
  throw new Exception('Flexpi PHP-SDK needs the JSON PHP extension.');
}

/**
 * Provides access to the Flexpi.com REST API.  This class provides
 * a majority of the functionality needed.
 *
 * @author Paweł Mikołajczuk <pawel@flywithmonkey.com>
 */
class Flexpi {

   /**
    * Version.
    */
    const VERSION = '1.0.0';

    protected $clientId;
    protected $secret;
    protected $apiEndpoint = 'http://flexpi.dev/api';
    protected $browser;

   /**
    * The ID of the Flexpi Api client, or 0 if the client is not authorized.
    *
    * @var integer
    */
    protected $user;

   /**
    * The OAuth access token received in exchange for a valid authorization
    * code.  null means the access token has yet to be determined.
    *
    * @var string
    */
    protected $accessToken = null;

   /**
    * Initialize a Flexpi PHP-SDK.
    *
    * The configuration:
    * - client_id: the client ID
    * - secret: the client secret
    *
    * @param array $config The application configuration
    */
    public function __construct($config) {
        $this->clientId = $config['client_id'];
        $this->secret = $config['secret'];

        $this->browser = new Buzz\Browser();
    }

    /**
     * Gnerate uri for choosen resource
     * 
     * @param  string $redirect_uri Uri for redirection
     * @param  string $type         Generated url type (code, token, refrsh)
     * @param  string $code         Code or refresh_token used in uri
     * @return string               Uri for resource
     */
    public function getLoginUrl($redirect_uri, $type, $code = '') {
        if ($type == 'code') {
            return 'http://flexpi.dev/app_dev.php/oauth/v2/auth?'.
                'client_id='.$this->clientId.'&'.
                'redirect_uri='.$redirect_uri.'&'.
                'response_type=code';
        } else if ($type == 'token') {
            return 'http://flexpi.dev/app_dev.php/oauth/v2/token?'.
                'client_id='.$this->clientId.'&'.
                'client_secret='.$this->secret.'&'.
                'redirect_uri='.$redirect_uri.'&'.
                'grant_type=authorization_code'.'&'.
                'code='.$code;
        } else if ($type == 'refresh') {
            return 'http://flexpi.dev/app_dev.php/oauth/v2/token?'.
                'client_id='.$this->clientId.'&'.
                'client_secret='.$this->secret.'&'.
                'refresh_token='.$code.'&'.
                'grant_type=refresh_token&'.
                'redirect_uri='.$redirect_uri;
        }
    }

    /**
     * Get setted accessToken
     * 
     * @return string access_token value
     */
    public function getAccessToken() {
        return $this->accessToken;
    }

    /**
     * Set access_token value
     * 
     * @param  string $token access_token  value
     * @return object                      Flexpi object
     */
    public function setAccessToken($token) {
        $this->accessToken = $token;
        return $this;
    }

    /**
     * Get data from choosen resource
     * 
     * @param  string $path path to api resource (for ex. /me)
     * @return json         json response from REST API
     */
    public function api($path) {
        $uri = $this->apiEndpoint.$path;

        if(strpos($path, '?') === false) {
            $uri .= '?access_token='.$this->getAccessToken();
        } else {
            $uri .= '&access_token='.$this->getAccessToken();
        }
        
        $response = $this->browser->get(
           $uri
        );

        $result = $response->getContent();

        return $result;
    }
}