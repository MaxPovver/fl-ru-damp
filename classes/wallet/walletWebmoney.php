<?php

/**
 * Подключаем файл для работы с ключами оплаты
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/wallet/wallet.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/pmpay.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/wallet/API_OAuth.php");
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/JWS.php";


/**
 * Класс для работы с кошельком WebMoney для автоматической оплаты услуг
 *
 */
class walletWebmoney extends Wallet
{
    /**
     * Задаем тип платежного метода
     *
     * @var int
     */
    protected $_type = WalletTypes::WALLET_WEBMONEY;

    /**
     * Содержит объект класса через который пишем логи
     *
     * @var log
     */
    public $log;

    /**
     * Конструктор класса необходимо задать ИД пользователя
     *
     * @param integer $uid ИД пользователя
     */
    public function __construct($uid = null) {
        parent::__construct($uid);

        // Если есть код авторизации все гуд
        if($this->getAccessToken() !== false) {
            $this->api = new API_Webmoney(null, $this->getAccessToken());
        } else {
            $this->api = new API_Webmoney();
        }

        $this->log = new log("wallet/webmoney-".SERVER.'-%d%m%Y.log', 'a', '%d.%m.%Y %H:%M:%S : ');
    }

    /**
     * Инициализирует срок действия ключа (у каждой системы он свой, по умолчанию 3 года)
     *
     */
    public function initValidity() {
        $this->data['validity'] = '1 year';
    }


    public function payment($sum) {
        // На бете альфе включаем дебаг режим
        if(!is_release())  {
            //$sum = 0.1;// @debug
            $this->api->setDebug(true);
        }
        $result = $this->api->requestPayment(round((float)$sum,2), $this->account->id);

        if($result['status'] == API_Webmoney::STATUS_SUCCESS) {
            $process = $this->api->processPayment($this->api->merchant_transaction_id, $result['processor_transaction_id']);

            switch($process['status']) {
                case API_Webmoney::STATUS_PAYMENT_PROGRESS:
                case API_Webmoney::STATUS_PAYMENT_SUCCESS:
                    // Зачисляем деньги на бете/альфе
//                    if(!is_release()) {
//                        $paymentDateTime = date('d.m.Y H:i');
//                        $orderNumber     = rand(1, 99999999);
//                        $descr = "WebMoney с кошелька {$this->data['wallet']} сумма - {$sum}, обработан {$paymentDateTime}, номер покупки - $orderNumber";
//
//                        $this->account->deposit($op_id, $this->account->id, $sum, $descr, 3, $sum, 12);
//                    }

                    return true;
                    break;
                case API_Webmoney::STATUS_PAYMENT_FAIL:
                    ob_start();
                    var_dump($result);
                    var_dump($process);
                    $content = ob_get_clean();
                    $this->log->writeln("FAIL Payment:\naccount:{$this->account->id}\n");
                    $this->log->write("Request:\n " . $this->api->last_request->getBody());
                    $this->log->write("Result:\n {$content}");
                    return false;
                    break;
                // Отложить платеж на пол часа
                // @todo придумать как отложить запрос на потом
                //case API_Webmoney::STATUS_PAYMENT_PROCESS:
                default:
                    return null;
                    break;

            }

        } else {
            ob_start();
            var_dump($result);
            $content = ob_get_clean();
            $this->log->writeln("FAIL Payment:\naccount:{$this->account->id}\n");
            $this->log->write("Request:\n " . $this->api->last_request->getBody());
            $this->log->write("Result:\n {$content}");
            return false;
        }
    }
}


    /**
     * Класс для работы с API Яндекс.деньги
     *
     * @link http://api.yandex.ru/money/doc/dg/concepts/About.xml
     */
class API_Webmoney extends API_OAuth
{
    /**
     * Адрес где мы получаем авторизацию для дальнейшей работы с АПИ
     */
    const AUTH_URI = 'https://paymaster.ru';

    /**
     * Адрес API с которым взаимодействуем после авторизации
     */
    const API_URI  = 'https://paymaster.ru';

    /**
     * Адрес, где необходима авторизация OAUTH
     */
    const OAUTH_URI = 'paymaster.ru';

    /**
     * Тестовые данные (используется для тестирования на бете, альфе)
     */
    const CLIENT_BETA_ID     = '8e9d6b16-4f21-4a1c-af24-659827ffaa87';
    const REDIRECT_BETA_URI  = 'https://beta.free-lance.ru/income/auto-wm.php';

    /**
     * Боевые данные @see classes/payment_keys.php
     */
    const CLIENT_ID     = WEBMONEY_CLIENT_ID;
    const REDIRECT_URI  = 'https://www.fl.ru/income/auto-wm.php';

    /**
     * Название алгоритма шифрования для работы с JWS
     */
    const JWS_ALG = 'RS256';

    /**
     * Приватный ключ для шифрования данных
     */
    const PRIVATE_KEY = '';

    /**
     * Публичный ключ для проверки и шифрования данных
     */
    const PUBLIC_KEY = '';

    /**
     * Статус API Webmoney
     * Успешное выполнение.
     */
    const STATUS_SUCCESS = 'success';

    /**
     * Статус API Webmoney
     * Ошибка выполнения
     */
    const STATUS_FAIL = 'failure';

    const STATUS_PAYMENT_SUCCESS    = 'complete';
    const STATUS_PAYMENT_PROGRESS   = 'in_progress';
    const STATUS_PAYMENT_FAIL       = 'failure';

    /**
     * Конструктор класса
     *
     * @param string $code            Временный ключ
     * @param string $accessToken     Ключ доступа
     */
    public function __construct($code = null, $accessToken = null) {
        $this->setAuthCode($code);
        $this->setAccessToken($accessToken);
        $this->log = new log("wallet/api-webmoney-".SERVER.'-%d%m%Y.log', 'a', '%d.%m.%Y %H:%M:%S : ');
        $this->pm  = new pmpay();
    }

    /**
     * Получаем данные от Яндекса в форме массива
     *
     * @param HTTP_Request2 $resp     Объект запроса
     * @return array
     */
    public function getBodyArray($resp) {
        if($resp == '') return array();
        $body = json_decode($resp->getBody(), true);
        return $body;
    }

    /**
     * Генерирует адрес авторизации
     * @see http://api.yandex.ru/money/doc/dg/reference/request-access-token.xml
     *
     * @param string $scope   Список запрашиваемых прав. Разделитель элементов списка - пробел. Элементы списка чувствительны к регистру.
     * @return string
     */
    static public function getAuthorizeUri( $scope = null ) {
        if ( empty($scope) ) {
            $scope = is_release() ? 58 : 57;
        }

        $query = array(
            'client_id'     => self::getClientID(),
            'response_type' => 'code',
            'scope'         => trim( strtolower($scope) ),
            'redirect_uri'  => self::getRedirectURI()
        );

        $jws = new JWS(API_Webmoney::JWS_ALG, time());
        $jws->setPayload($query);
        $jws->sign(self::getPrivatekey());
        $req['request'] = $jws->getTokenString();

        $link = API_Webmoney::AUTH_URI . "/direct/security/auth?" . http_build_query($req);

        return $link;
    }

    /**
     * Определяем по адресу нужна ли нам авторизация OAuth в запросе
     *
     * @param $uri      Адрес запроса
     * @return bool     true - Нужна, false - Не нужна
     */
    public function isOAuth($uri) {
        $result = parse_url($uri);
        return ($result['host'] == API_Webmoney::OAUTH_URI);
    }

    /**
     * Возвращает ИД приложения
     *
     * @return string
     */
    static public function getClientID() {
        return ( is_release() ? API_Webmoney::CLIENT_ID : API_Webmoney::CLIENT_BETA_ID );
    }

    /**
     * Возвращает адрес редиректа приложения
     *
     * @return string
     */
    static public function getRedirectURI() {
        return ( is_release() ? API_Webmoney::REDIRECT_URI : API_Webmoney::REDIRECT_BETA_URI );
    }

    /**
     * Возвращает приватный ключ
     *
     * @return string
     */
    static public function getPrivatekey() {
        if(file_exists($_SERVER['DOCUMENT_ROOT'] . "/classes/keys/fl.key")) {
            return file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/classes/keys/privatekey.pem");
        }
    }

    /**
     * Возвращает публичный ключ
     *
     * @return string
     */
    static public function getPublicKey() {
        if(file_exists($_SERVER['DOCUMENT_ROOT'] . "/classes/keys/fl.pub")) {
            return file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/classes/keys/fl.pub");
        }
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
            $jws = new JWS(API_Webmoney::JWS_ALG, time());
            $jws->setPayload($req);
            $jws->sign($this->getPrivatekey());
            $req['request'] = $jws->getTokenString();
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

    /**
     * Получение ключа доступа
     *
     */
    public function initAccessToken() {
        $uri  = API_Webmoney::AUTH_URI . "/direct/security/token";
        $post = array(
            'code'          => $this->getAuthCode(),
            'client_id'     => self::getClientID(),
            'client_secret' => self::getClientSecret(),
            'grant_type'    => 'authorization_code',
            'redirect_uri'  => self::getRedirectURI()
        );

        $resp = $this->request($uri, $post);
        return $this->getBodyArray($resp);
    }

    /**
     * Echo-запросы для проверки работы сервера партнеров
     *
     * @return array
     */
    public function echoRequest() {
        $uri  = API_Webmoney::AUTH_URI . "/api/echo";
        $post = array(
            'value' => rtrim(chunk_split(md5(microtime()), 8, '-'), '-')
        );
        $resp = $this->request($uri, $post);
        return $this->getBodyArray($resp);
    }

    /**
     * Запрос создания платежа
     *
     * @param $sum          Сумма платежа
     * @param $account      Класс аккаунта
     * @return array
     */
    public function requestPayment($sum, $accountId) {
        $uri  = API_Webmoney::API_URI . "/direct/payment/init";
        $this->merchant_transaction_id = $this->pm->genPaymentNo();
        $post = array(
            'access_token'              => $this->getAccessToken(),
            'merchant_id'               => $this->getClientID(),//$this->pm->merchants[pmpay::MERCHANT_BILL],
            'merchant_transaction_id'   => $this->merchant_transaction_id,
            'amount'                    => round($sum, 2),
            'currency'                  => 'RUB',
            'custom'                    => array('PAYMENT_BILL_NO' => $accountId),
            'description'               => iconv('CP1251', 'UTF-8', "Оплата за услуги сайта www.free-lance.ru, в том числе НДС - 18%. Счет #{$accountId}.") //PAYMENT_BILL_NO={$accountId}
        );
//        if($this->isDebug()) {
//            $post['test_payment'] = 'true';
//            $post['test_result']  = API_Yandex::STATUS_SUCCESS;
//        }

        $resp = $this->request($uri, $post);
        return $this->getBodyArray($resp);
    }

    /**
     * Подтверждение платежа
     *
     * @param $transaction_id   Идентификатор нашей транзакции
     * @param $request_id       Идентификатор запроса, полученный из ответа метода self::requestPayment()
     * @return array
     */
    public function processPayment($transaction_id, $request_id) {
        $uri  = API_Webmoney::API_URI . "/direct/payment/complete";
        $post = array(
            'access_token'              => $this->getAccessToken(),
            'merchant_id'               => $this->getClientID(),//$this->pm->merchants[pmpay::MERCHANT_BILL],
            'merchant_transaction_id'   => $transaction_id,
            'processor_transaction_id'  => $request_id
        );

        $resp = $this->request($uri, $post);
        return $this->getBodyArray($resp);
    }

    /**
     * Запрос отмены платежа (если исходная транзакция не завершена)
     *
     * @param $request_id       Идентификатор запроса, полученный из ответа метода self::requestPayment()
     * @param $transaction_id   Идентификатор нашей транзакции
     * @return array
     */
    public function refund($request_id, $transaction_id) {
        $uri  = API_Webmoney::API_URI . "/direct/payment/refund";
        $post = array(
            'access_token'              => $this->getAccessToken(),
            'merchant_id'               => $this->getClientID(),//$this->pm->merchants[pmpay::MERCHANT_BILL],
            'merchant_transaction_id'   => $transaction_id,
            'processor_transaction_id'  => $request_id
        );

        $resp = $this->request($uri, $post);
        return $this->getBodyArray($resp);
    }

    // @todo узнать как проверить токен
    public function checkToken() {
        //$info = $this->accountInfo();
        return true;
    }

}