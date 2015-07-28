<?php

/**
 * @uses pear install HTTP_Request2
 */
require_once 'HTTP/Request2.php';

/**
 * Класс для работы с OAuth (может использоватся и просто как каркас отсылки запросов на сервер без oauth)
 *
 */
abstract class API_OAuth
{
    /**
     * Настройки по умолчанию для запроса
     *
     * @var array
     */
    protected $_requestConfig = array(
        'adapter'           => 'HTTP_Request2_Adapter_Curl',
        'connect_timeout'   => 20,
        'protocol_version'  => '1.1',
        'ssl_verify_peer'   => false,
        'ssl_verify_host'   => false,
        'ssl_cafile'        => null,
        'ssl_capath'        => null,
        'ssl_passphrase'    => null
    );

    /**
     * Временный код авторизации
     *
     * @var null
     */
    protected $_code;

    /**
     * Ключ для проведения операций
     *
     * @var
     */
    protected $_access_token;

    /**
     * Локальный дебаг
     *
     * @var bool
     */
    protected $_debug;

    /**
     * Адрес где мы получаем авторизацию для дальнейшей работы с АПИ
     */
    const AUTH_URI = '';

    /**
     * Адрес API с которым взаимодействуем после авторизации
     */
    const API_URI  = '';

    /**
     * Адрес, где необходима авторизация OAUTH
     */
    const OAUTH_URI = '';

    /**
     * Тестовые данные (используется для тестирования на бете, альфе)
     */
    const CLIENT_BETA_ID     = '';
    const CLIENT_BETA_SECRET = '';
    const REDIRECT_BETA_URI  = '';

    /**
     * Боевые данные @see classes/payment_keys.php
     */
    const CLIENT_ID     = '';
    const CLIENT_SECRET = '';
    const REDIRECT_URI  = '';

    /**
     * Кодировки используемые в системе
     */
    const SERVER_ENCODING = 'CP1251';

    /**
     * Кодировка используемая для отправления запросов
     */
    const SEND_ENCODING   = 'UTF-8';

    /**
     * Определяем по адресу нужна ли нам авторизация OAuth в запросе
     *
     * @param $uri      Адрес запроса
     * @return bool     true - Нужна, false - Не нужна
     */
    abstract public function isOAuth($uri);

    /**
     * Получаем данные в форме массива
     *
     * @param HTTP_Request2 $resp     Объект запроса
     * @return array
     */
    abstract public function getBodyArray($resp);

    /**
     * Генерирует адрес авторизации
     *
     * @param string $scope   Список запрашиваемых прав. Разделитель элементов списка - пробел. Элементы списка чувствительны к регистру.
     * @return string
     */
    abstract static public function getAuthorizeUri( $scope = null );

    /**
     * Данная функция должна проверять работоспособность токена
     *
     * @return mixed
     */
    abstract public function checkToken();

    /**
     * Конструктор класса
     *
     * @param string $code            Временный ключ
     * @param string $accessToken     Ключ доступа
     */
    public function __construct($code = null, $accessToken = null) {
        $this->setAuthCode($code);
        $this->setAccessToken($accessToken);
        $this->log = new log("wallet/api-oauth-".SERVER.'-%d%m%Y.log', 'a', '%d.%m.%Y %H:%M:%S : ');
    }

    /**
     * Задаем локальный уровень дебага
     *
     * @param $debug
     */
    public function setDebug($debug) {
        $this->_debug = $debug;
    }

    /**
     * Проверяем задана ли локальная отладка
     *
     * @return mixed
     */
    public function isDebug() {
        return $this->_debug;
    }

    /**
     * Задать временный код авторизации
     *
     * @param string $code
     */
    public function setAuthCode($code) {
        $this->_code = $code;
    }

    /**
     * Возвращает временный код авторизации
     *
     * @return null
     */
    public function getAuthCode() {
        return $this->_code;
    }

    /**
     * Задаем код для проведения операций
     *
     * @param $accessToken
     */
    public function setAccessToken($accessToken) {
        $this->_access_token = $accessToken;
    }

    /**
     * Возвращаем код для проведения операций
     *
     * @param $accessToken
     */
    public function getAccessToken() {
        return $this->_access_token;
    }

    /**
     * Возвращает конфигурацию запроса
     *
     * @return array
     */
    public function getRequestConfig() {
        return $this->_requestConfig;
    }

    /**
     * Добавляет или заменяет данные в конфигурации
     *
     * @param string $name     Название конфигурации
     * @param mixed  $value    Значение
     */
    public function setRequestConfig($name, $value) {
        $this->_requestConfig[$name] = $value;
    }

    /**
     * Возвращает ИД приложения
     *
     * @return string
     */
    static public function getClientID() {
        return ( is_release() ? self::CLIENT_ID : self::CLIENT_BETA_ID );
    }


    /**
     * Возвращает секретый код приложения
     *
     * @return string
     */
    static public function getClientSecret() {
        return ( is_release() ? self::CLIENT_SECRET : self::CLIENT_BETA_SECRET );
    }

    /**
     * Возвращает адрес редиректа приложения
     *
     * @return string
     */
    static public function getRedirectURI() {
        return ( is_release() ? self::REDIRECT_URI : self::REDIRECT_BETA_URI );
    }


    /**
     * Инициализация и подготовка данных для запроса
     *
     * @param $uri          Адресс запроса
     * @param $method       Метод запроса (POST, GET) @see http://pear.php.net/package/HTTP_Request2/
     * @return HTTP_Request2
     */
    public function initRequest($uri, $method = HTTP_Request2::METHOD_POST) {
        $request = new HTTP_Request2($uri, $method);
        $request->setConfig($this->getRequestConfig());
        $request->setHeader( 'Content-Type', 'application/x-www-form-urlencoded; charset=' . self::SEND_ENCODING );
        $request->setHeader( 'Expect', '' );
        if($this->isOAuth($uri)) {
            $request->setHeader( 'Authorization', 'Bearer ' . $this->getAccessToken() );
        }
        return $request;
    }

    /**
     * Делаем запрос
     *
     * @param string $uri    Адресс запроса
     * @param array  $req    POST данные если есть
     * @param $method        Метод запроса (по умолчанию POST)
     * @return mixed
     */
    public function request($uri, $req = array(), $method = HTTP_Request2::METHOD_POST) {
        $request = $this->initRequest($uri, $method);
        if($method == HTTP_Request2::METHOD_POST) {
            $request->addPostParameter($req);
        }
        $this->last_request = $request;
        $this->sended       = $request->send();
        if( $this->sended->getStatus() != 200) {
            $status = $this->sended->getStatus();
            ob_start();
            var_dump($req);
            $content = ob_get_clean();
            $this->log->writeln("FAIL Request({$status}):\nuri:{$uri}\n");
            $this->log->write("Request:\n " . $content);
            $this->log->write("Result:\n ". $this->sended->getBody());
        }
        return $this->sended;
    }


}