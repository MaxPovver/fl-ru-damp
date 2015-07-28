<?php

/**
 * Class OdnoklassnikiStrategy
 *
 */
class OdnoklassnikiStrategy extends OpauthStrategy {

    /**
     * Compulsory config keys, listed as unassociative arrays
     */
    public $expects = array('app_id', 'app_secret', 'app_public');

    /**
     * Optional config keys, without predefining any default values.
     */
    public $optionals = array('redirect_uri', 'scope');

    /**
     * Optional config keys with respective default values, listed as associative arrays
     * eg. array('scope' => 'email');
     */
    public $defaults = array(
        'redirect_uri' => '{complete_url_to_strategy}oauth2callback',
        'scope' => ''
    );

    /**
     * Auth request
     */
    public function request()
    {
        $url = 'http://www.odnoklassniki.ru/oauth/authorize';
        $params = array(
            'client_id' => $this->strategy['app_id'],
            'scope' => $this->strategy['scope'],
            'redirect_uri' => $this->strategy['redirect_uri'],
            'response_type' => 'code',
        );

        $this->clientGet($url, $params);
    }

    /**
     * Internal callback to get the code and request que authorization token, after Odnoklassniki's OAuth
     */
    public function int_callback()
    {
        if (array_key_exists('code', $_GET) && !empty($_GET['code'])) {
            $url = 'https://api.odnoklassniki.ru/oauth/token.do';
            $params = array(
                'code' => $_GET['code'],
                'client_id' => $this->strategy['app_id'],
                'client_secret' => $this->strategy['app_secret'],
                'redirect_uri' => $this->strategy['redirect_uri'],
                'grant_type' => 'authorization_code'
            );
            
            $response = $this->serverPost($url, $params, false, $headers);
            
            if (empty($response)) {
                $error = array(
                    'code' => 'Get access token error',
                    'message' => 'Failed when attempting to get access token',
                    'raw' => array(
                        'headers' => $headers
                    )
                );
                exit();
                $this->errorCallback($error);
            }
            $results = json_decode($response, true);
            $okUser = $this->getUser($results['access_token']);

            $this->auth = array(
                'provider' => 'Odnoklassniki',
                'uid' => $okUser['uid'],
                'info' => array(
                ),
                'credentials' => array(
                    'token' => $results['access_token'],
                    'expires' => date('c', time() + $results['expires_in'])
                ),
                'raw' => $okUser
            );

            if (!empty($okUser['first_name'])) {
                $this->auth['info']['name'] = $okUser['first_name'];
            }
            if (!empty($okUser['gender'])) {
                $this->auth['info']['gender'] = ($okUser['sex'] == 'female') ? 'female' : 'male';
            }
            if (!empty($okUser['pic_2'])) { 
                $this->auth['info']['image'] = $okUser['pic_2'];
            }

            $this->callback();
        } else {
            $error = array(
                'code' => isset($_GET['error']) ? $_GET['error'] : 0,
                'raw' => $_GET
            );

            $this->errorCallback($error);
        }
    }
    
    private function getUser($access_token)
    {
        $param_string = 'application_key=' . $this->strategy['app_public'] . 'method=users.getCurrentUser';
        $md5 = md5($access_token . $this->strategy['app_secret']);
        $sig = strtolower(md5($param_string . $md5));
        
        $okUser = $this->serverget('http://api.ok.ru/fb.do', array(
            'application_key' => $this->strategy['app_public'],
            'method' => 'users.getCurrentUser',
            'access_token' => $access_token,
            'sig' => $sig
        ));
        
        if (!empty($okUser)) {
            return json_decode($okUser,true);
        } else {
            $error = array(
                'code' => 'Get User error',
                'message' => 'Failed when attempting to query for user information',
                'raw' => array(
                    'access_token' => $access_token,	
                    'headers' => $headers
                )
            );
            $this->errorCallback($error);
        }
    }
}