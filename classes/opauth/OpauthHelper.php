<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/opauth/OdnoklassnikiStrategy.php");

/**
 * Class OpauthHelper
 *
 */
class OpauthHelper {
    const ACTION_REGISTER = 1;
    const ACTION_BIND = 2;
    
    const SECURITY_SALT = 'lnuVaEvtWzMZ12OWNIVh';
    
    const MESSAGE_ERROR = "Произошла ошибка авторизации";
    
    public static function getError($is_valid, $response) {
        return (!$is_valid || self::isInvalidResponse($response)) ? self::MESSAGE_ERROR : '';
    }
    
    public static function noEmail($response)
    {
        return empty($response['auth']['info']['email']);
    }
    
    
    public static function noLogin($response)
    {
        return empty($response['auth']['info']['nickname']);
    }
    
    
    public static function getConfig()
    {
        return array(
            'host' => (is_local() ? 'http' : 'https') . '://'. $_SERVER['HTTP_HOST'],
            'path' => '/auth/',
            'debug' => false,
            'callback_url' => '/auth/callback/',
            'security_salt' => self::SECURITY_SALT,
            'security_timeout' => '5 minutes',
            'Strategy' => array(
                'Facebook' => array(
                    'app_id' => FACEBOOK_APP_ID,
                    'app_secret' => FACEBOOK_APP_SECRET,
                    'scope' => 'email,user_friends',
                    'redirect_uri' => '{host}{path}?param=facebook&action=int_callback'
                ),
                'VKontakte' => array(
                    'app_id' => VK_APP_ID,
                    'app_secret' => VK_APP_SECRET,
                    'scope' => 'friends,email',
                    'redirect_uri' => '{host}{path}?param=vkontakte&action=int_callback'
                ),
                'Odnoklassniki' => array(
                    'app_id' => ODNOKLASSNIKI_APP_ID,
                    'app_public' => ODNOKLASSNIKI_APP_PUBLIC,
                    'app_secret' => ODNOKLASSNIKI_APP_SECRET,                   
                    'redirect_uri' => '{host}{path}?param=odnoklassniki&action=int_callback'
                )
            )
        );
    }
    
    public function getRegistrationData($response)
    {
        return array(
            'role' => self::getRole(),
            'email' => isset($response['auth']['info']['email']) 
                ? $response['auth']['info']['email'] 
                : '',
            'login' => self::getLogin($response)
        );
    }
    
    public static function setRole($role)
    {
        if (!in_array($role, array(1, 2))) {
            $role = 1;
        }
        $_SESSION['opauth_role'] = $role;
    }
    
    public static function getRole()
    {
        return isset($_SESSION['opauth_role']) ? $_SESSION['opauth_role'] : 1;
    }
    
    public static function setMultilevel($value)
    {
        $_SESSION['opauth_multilevel'] = (bool) $value;
    }
    
    public static function getMultilevel()
    {
        return isset($_SESSION['opauth_multilevel']) ? $_SESSION['opauth_multilevel'] : 0;
    }
    
    public static function saveRedirect()
    {
        $emp_redirect = __paramInit('link', 'emp_redirect');
        if ($emp_redirect) {
            $_SESSION['opauth_emp_redirect'] = $emp_redirect;
        }
    }
    
    public static function getEmpRedirect()
    {
        return isset($_SESSION['opauth_emp_redirect']) ? $_SESSION['opauth_emp_redirect'] : '';
    }
    
    private static function getLogin($response)
    {
        $login = '';
        
        if (isset($response['auth']['info']['nickname'])) {
            $login = $response['auth']['info']['nickname'];
        }

        if (preg_match("/^id[0-9]+$/", $login)) {
            $login = '';
        }
        
        return $login;
    }


    /**
     * Проверяет наличие ключей в запросе
     * @param type $response
     * @return boolean
     */
    private function isInvalidResponse($response)
    {
        return !is_array($response) 
            || array_key_exists('error', $response)
            || empty($response['auth']) 
            || empty($response['timestamp']) 
            || empty($response['signature']) 
            || empty($response['auth']['provider']) 
            || empty($response['auth']['uid']);
    }
}
