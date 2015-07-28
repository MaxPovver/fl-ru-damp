<?php

/**
 * @setup
 * pear install Math_BigInteger
 */
require_once $_SERVER['DOCUMENT_ROOT']."/classes/Crypt/RSA.php";

/**
 * JSON Web Signature
 *
 * Подробнее ознакамливаемся тут http://tools.ietf.org/html/draft-jones-json-web-signature-04
 *
 * @example
 *
 * $jws = new JWS('RS256', time());
 * $jws->setPayload($data);
 * $private_key = file_get_contents("/path/to/keys/ssl/private.pem");
 * $jws->sign($private_key);
 * // Получаем подписанную строку в формате JWS кодируем приватным ключем
 * $rs256_token = $jws->getTokenString();
 *
 * // Проверка входящей строки публичным ключем
 * $public_key = file_get_contents("/path/to/keys/ssl/public.pem");
 * $vjws = JWS::load($rs256_token);
 * $result = $vjws->verify($public_key);
 *
 * if($result) {
 *     echo "Data OK";
 * }
 *
 */
class JWS extends JWT
{
    /**
     * Поддерживаемые алгоритмы
     *
     * @var array
     */
    private $_allowed_algs = array( 'none',
        'HS256', 'HS384', 'HS512',
        'RS256', 'RS384', 'RS512',
        'ES256', 'ES384', 'ES512');

    /**
     * Конструктор класса
     *
     * @param $alg          Алгоритм шифрования
     * @param string $typ
     */
    public function __construct($alg, $time = null) {
        if($time !== null) {
            $this->setHeaderItem('iat', $time);
        }
        $this->setHeaderItem('alg', $alg);

        //$this->setHeaderItem('typ', 'JWS');

    }

    /**
     * Инициализировать параметры шапки запроса
     *
     * @param $name     Название параметра
     * @param $value    Значение параметра
     */
    public function setHeaderItem($name, $value) {
        if($name=='alg'){
            if(!in_array($value, $this->_allowed_algs)) {
                trigger_error("Unknown Signature Algorithm", E_USER_ERROR);
            }
        } elseif($name=='typ') {
            if($value !== 'JWS' && $value !== 'JWT') {
                trigger_error("Unknown typ", E_USER_ERROR);
            }
        }
        $this->_header[$name] = $value;
    }

    /**
     * Получаем подписанный ключ
     *
     * @param mixed $key Ключик которым шифруем обычно это private.key
     */
    public function sign($key) {
        $signingInput = $this->generateSigningInput();
        switch(substr($this->_header['alg'], 0, 2)) {
            case "HS":
                $hashAlg = "sha".substr($this->_header['alg'], 2, 3);
                $this->_signature = hash_hmac($hashAlg, $signingInput, $key, true);
                break;
            case "RS":
                $hashAlg = "sha".substr($this->_header['alg'], 2, 3);
                $this->RSASign($hashAlg, $signingInput, $key);
                break;
            default:
                $this->_signature = "";
                break;
        }
    }

    /**
     * Получаем RSA ключ
     *
     * @param string $hashAlg        Алгоритм шифрования
     * @param string $signingInput   Данные для шифрования закодированные base64
     * @param mixed $key             Приватный ключ которым шифруем
     */
    private function RSASign($hashAlg, $signingInput, $key) {
        $this->_signature = $this->rsa($hashAlg, $key)->sign($signingInput);
    }

    /**
     * Проверка на доступность выбранного алгоритма
     *
     * @param string $alg Название алгоритма
     * @return bool
     */
    static public function isAllowedAlg($alg) {
        $_allowed_algs = array( 'none',
            'HS256', 'HS384', 'HS512',
            'RS256', 'RS384', 'RS512',
            'ES256', 'ES384', 'ES512');
        return in_array($alg, $_allowed_algs);
    }

    /**
     * Загружает JSON Web Signature
     *
     * @param string $jwt JSON Web Signature
     * @param bool $payload_is_array Данные запроса
     * @return JWS object
     */
    static public function load($jwt, $payload_is_array=false){
        // split 3 parts
        $part = explode('.', $jwt);
        if(!is_array($part) || empty($part) || count($part) !== 3 ){
            return false;
        }

        $header = self::getHeader($jwt);
        if($header && isset($header['alg'])){
            $jwtobj = new self($header['alg']);
            foreach($header as $key => $value){
                $jwtobj->setHeaderItem($key, $value);
            }
            $jwtobj->setPayload(self::getPayload($jwt, $payload_is_array));
            $jwtobj->setTokenString($jwt);
            return $jwtobj;
        }else{
            return false;
        }
    }

    /**
     * Проверка данных по публичному ключу
     *
     * @param mixed $key Публичный ключ
     */
    public function verify($key) {
        $part = explode('.', $this->_tokenstring);
        if(!is_array($part) || empty($part) || count($part) !== 3 ){
            return false;
        }
        $decoded_signature = JWS_Base64::urlDecode($part[2]);
        $signinginput = self::getSigningInput($this->_tokenstring);
        switch(substr($this->_header['alg'], 0, 2)) {
            case "HS":
                $hashAlg = "sha".substr($this->_header['alg'], 2, 3);
                $generated_signature = hash_hmac($hashAlg, $signinginput, $key, true);
                return ($generated_signature === $decoded_signature);
                break;
            case "RS":
                $hashAlg = "sha".substr($this->_header['alg'], 2, 3);
                return $this->RSAVerify($hashAlg, $signinginput, $decoded_signature, $key);
                break;
            default:
                return (empty($part[2]));
                break;
        }
    }

    /**
     * Создаем подпись для передаваемых данных
     *
     * @param $hashAlg              Алгоритм шифрования
     * @param $signingInput         Данные для шифрования
     * @param $decoded_signature    Ключ для шифрования
     * @param $pubkey
     * @return bool
     */
    public function RSAVerify($hashAlg, $signingInput, $decoded_signature, $pubKey) {
        return $this->rsa($hashAlg, $pubKey)->verify($signingInput, $decoded_signature);
    }

    public function rsa($hashAlg, $key) {
        $rsa = new Crypt_RSA();
        $rsa->loadKey($key);
        $rsa->setHash($hashAlg);
        $rsa->setSignatureMode(CRYPT_RSA_SIGNATURE_PKCS1);
        return $rsa;
    }
}

/**
 * JSON Web Token (JWT)
 *
 * Подробно можно ознакомиться тут
 * http://tools.ietf.org/html/draft-jones-json-web-signature-04#ref-JWT
 *
 */
class JWT
{
    /**
     * Шапка запроса
     *
     * @var array
     */
    protected $_header=array();

    /**
     * Данные запроса
     *
     * @var
     */
    protected $_payload;


    /**
     * Подпись запроса
     *
     * @var
     */
    protected $_signature;

    /**
     * Сгенерированная строка для запроса
     *
     * @var string
     */
    protected $_tokenstring;


    /**
     * Конструктор
     *
     * @param $alg  Алгоритм шифрования
     * @param $time Время отправки запроса
     */
    public function __construct($alg, $time = null) {
        if($time !== null) {
            $this->setHeaderItem('iat', $time);
        }
        $this->setHeaderItem('alg', $alg);
        //$this->setHeaderItem('typ', 'JWT');
    }


    /**
     * Инициализировать параметры шапки запроса
     *
     * @param $name
     * @param $value
     */
    public function setHeaderItem($name, $value) {
        $this->_header[$name] = $value;
    }

    /**
     * Инициализировать данные запроса
     *
     * @param $payload
     */
    public function setPayload($payload) {
        $this->_payload = $payload;
    }

    /**
     * Возвращяет JWT ключ для запроса
     *
     * @return string
     */
    public function getTokenString() {
        $token = $this->generateSigningInput();
        $token .= ".";
        if(!empty($this->_signature)){
            $token .= JWS_Base64::urlEncode($this->_signature);
        }
        return $token;
    }

    /**
     * Назначаем сгенерированный ключ
     *
     * @return string
     */
    public function setTokenString($jwt) {
        $this->_tokenstring = $jwt;
    }

    /**
     * Высылаемая строка запроса
     *
     * @return string
     */
    public function generateSigningInput() {
        $token = JWS_Base64::urlEncode(JWS_Json::encode($this->_header)).".";
        if(is_array($this->_payload)){
            $token .= JWS_Base64::urlEncode(JWS_Json::encode($this->_payload));
        }else{
            $token .= JWS_Base64::urlEncode($this->_payload);
        }
        return $token;
    }

    /**
     * Шапка запроса
     *
     * @param string $jwt JWT string
     * @return array JWT Header
     */
    static public function getHeader($jwt) {
        // проверяем все ли части на месте
        $part = explode('.', $jwt);
        if(!is_array($part) || empty($part) || count($part) !== 3 ){
            return false;
        }
        $header = json_decode(JWS_Base64::urlDecode($part[0]),true);
        return $header;
    }

    public function getLoadHeader() {
        return $this->_header;
    }

    public function getLoadPayload($return_is_array=true) {
        if ($return_is_array) {
            return json_decode($this->_payload, true);
        }
        return $this->_payload;
    }

    /**
     * Возвращает данные запроса
     *
     * @param string $jwt Запрос
     * @param bool $return_is_array вернуть в формате массива
     * @return mixed
     */
    static public function getPayload($jwt, $return_is_array=false) {
        // проверяем все ли части на месте
        $part = explode('.', $jwt);
        if(!is_array($part) || empty($part) || count($part) !== 3 ){
            return false;
        }
        if($return_is_array) {
            $payload = json_decode(JWS_Base64::urlDecode($part[1]),true);
        } else {
            $payload = JWS_Base64::urlDecode($part[1]);
        }
        return $payload;
    }

    /**
     * Возвращаем шапку запроса
     *
     * @param string $jwt Данные запроса
     * @return string
     */
    static public function getEncodedHeader($jwt) {
        // проверяем все ли части на месте
        $part = explode('.', $jwt);
        if(!is_array($part) || empty($part) || count($part) !== 3 ){
            return false;
        }
        return $part[0];
    }

    /**
     * Возвращает данные запроса
     *
     * @param string $jwt Данные запроса
     * @return string
     */
    static public function getEncodedPayload($jwt) {
        // проверяем все ли части на месте
        $part = explode('.', $jwt);
        if(!is_array($part) || empty($part) || count($part) !== 3 ){
            return false;
        }
        return $part[1];
    }

    /**
     * Возвращает строку запроса
     *
     * @param  string $jwt Строка JWT @see http://tools.ietf.org/html/draft-jones-json-web-signature-04#ref-JWT
     * @return string
     */
    static public function getSigningInput($jwt) {
        // проверяем все ли части на месте
        $part = explode('.', $jwt);
        if(!is_array($part) || empty($part) || count($part) !== 3 ){
            return false;
        }
        return self::getEncodedHeader($jwt).".".self::getEncodedPayload($jwt);
    }
}


/**
 * Вспомогательный класс для работы с base64
 */
class JWS_Base64
{

    /**
     * Base64 encode
     *
     * @param $str
     * @return string
     */
    static public function urlEncode($str) {
        $enc = base64_encode($str);
        $enc = rtrim($enc, "=");
        $enc = strtr($enc, "+/", "-_");
        return $enc;
    }

    /**
     * Base64 decode
     *
     * @param $str
     * @return string
     */
    static public function urlDecode($str) {
        $dec = strtr($str, "-_", "+/");
        switch (strlen($dec) % 4) {
            case 0:
                break;
            case 2:
                $dec .= "==";
                break;
            case 3:
                $dec .= "=";
                break;
            default:
                return "";
        }
        return base64_decode($dec);
    }
}

class JWS_Json
{
    // encode for php-5.2.xx (костыль для версии ПХП как на боевой)
    static public function encode($data) {
        return str_replace("\/", "/", json_encode($data));
    }
}


?>