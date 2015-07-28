<?php

/**
 * Подключаем файл для работы с ключами оплаты
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/wallet/wallet.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/wallet/API_OAuth.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/ydpay.php");


/**
 * Класс для работы с кошельком Яндекс.Деньги для автоматической оплаты услуг
 *
 *
 */
class walletYandex extends Wallet
{
    /**
     * Задаем тип платежного метода
     *
     * @var int
     */
    protected $_type = WalletTypes::WALLET_YANDEX;

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
            $this->api = new API_Yandex(null, $this->getAccessToken());
        } else {
            $this->api = new API_Yandex();
        }

        $this->log = new log("wallet/yandex-".SERVER.'-%d%m%Y.log', 'a', '%d.%m.%Y %H:%M:%S : ');
    }

    /**
     * Оплачиваем услуг
     *
     * @param float   $sum          Сумма услуги
     * @return bool|mixed|null
     */
    public function payment($sum) {
        // На бете альфе включаем дебаг режим
        if(!is_release())  {
            $this->api->setDebug(true);
        }
        $result = $this->api->requestPayment(round((float)$sum,2), $this->account->id);

        if($result['status'] == API_Yandex::STATUS_SUCCESS) {
            // не откуда взять csc
//            foreach($result['money_source'] as $name=>$value) {
//                // Первый доступный метод оплаты
//                if($value['allowed'] == true) {
//                    $money_source = $name;
//                    break;
//                }
//            }
            // Для беты сумму баланса не проверяем
            if(($result['balance'] > $sum || !is_release()) && $result['request_id'] != '') {
                $process = $this->api->processPayment($result['request_id']);

                switch($process['status']) {
                    case API_Yandex::STATUS_SUCCESS:
                        // Зачисляем деньги на бете/альфе
                        if(!is_release()) {
                            $paymentDateTime = date('d.m.Y H:i');
                            $orderNumber     = rand(1, 99999999);
                            $descr = "ЯД с кошелька {$this->data['wallet']} сумма - {$sum}, обработан {$paymentDateTime}, номер покупки - $orderNumber";

                            $this->account->deposit($op_id, $this->account->id, $sum, $descr, 3, $sum, 12);
                        }

                        return true;
                        break;
                    case API_Yandex::STATUS_FAIL:
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
                    case API_Yandex::STATUS_PROCESS:
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
                //error
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
class API_Yandex extends API_OAuth
{
    /**
     * Адрес где мы получаем авторизацию для дальнейшей работы с АПИ
     */
    const AUTH_URI = 'https://sp-money.yandex.ru';

    /**
     * Адрес API с которым взаимодействуем после авторизации
     */
    const API_URI  = 'https://money.yandex.ru';

    /**
     * Адрес, где необходима авторизация OAUTH
     */
    const OAUTH_URI = 'money.yandex.ru';

    /**
     * Тестовые данные (используется для тестирования на бете, альфе)
     */
    const CLIENT_BETA_ID     = 'F9B4F15E7B0BF0E11DAE3324AB73E8211ABEAF197B927578AA043407767DF1D7';
    const CLIENT_BETA_SECRET = 'F77A2F9EA9E51C849F85B9BADEA2C6AF5E30FB1BB446CDECBCBBE814ABF9E63537F693CC0805EC5BF8B1D93B9FC98B6D51331C1A03A4FB9AB52EE2DD144EFEDE';
    const REDIRECT_BETA_URI  = 'https://beta.free-lance.ru/income/auto-yd.php';

    /**
     * Боевые данные @see classes/payment_keys.php
     */
    const CLIENT_ID     = YANDEX_CLIENT_ID;
    const CLIENT_SECRET = YANDEX_CLIENT_SECRET;
    const REDIRECT_URI  = 'https://www.fl.ru/income/auto-yd.php';

    /**
     * Наш ид для покупки услуг
     */
    const PATTERN_ID = '2200';

    /**
     * ИД покупки в магазине (пополнение счета)
     */
    const SHOP_ID = ydpay::SHOP_DEPOSIT;

    /**
     * Кодировки используемые в системе
     */
    const SERVER_ENCODING = 'CP1251';

    /**
     * Кодировка используемая в Яндекс.Деньги
     */
    const SEND_ENCODING   = 'UTF-8';

    /**
     * Статус API Яндекс.Денег
     * Успешное выполнение.
     */
    const STATUS_SUCCESS = 'success';

    /**
     * Статус API Яндекс.Денег
     * Отказ в проведении платежа, объяснение причины отказа содержится в поле error. Это конечное состояние платежа.
     */
    const STATUS_FAIL    = 'refused';

    /**
     * Статус API Яндекс.Денег
     * Авторизация платежа не завершена. Приложению следует повторить запрос с теми же параметрами спустя некоторое время.
     */
    const STATUS_PROCESS = 'in_progress';

    /**
     * Конструктор класса
     *
     * @param string $code            Временный ключ
     * @param string $accessToken     Ключ доступа
     */
    public function __construct($code = null, $accessToken = null) {
        $this->setAuthCode($code);
        $this->setAccessToken($accessToken);
        $this->log = new log("wallet/api-yandex-".SERVER.'-%d%m%Y.log', 'a', '%d.%m.%Y %H:%M:%S : ');
    }

    /**
     * Определяем по адресу нужна ли нам авторизация OAuth в запросе
     *
     * @param $uri      Адрес запроса
     * @return bool     true - Нужна, false - Не нужна
     */
    public function isOAuth($uri) {
        $result = parse_url($uri);
        return ($result['host'] == API_Yandex::OAUTH_URI);
    }

    /**
     * Возвращает ИД приложения
     *
     * @return string
     */
    static public function getClientID() {
        return ( is_release() ? API_Yandex::CLIENT_ID : API_Yandex::CLIENT_BETA_ID );
    }


    /**
     * Возвращает секретый код приложения
     *
     * @return string
     */
    static public function getClientSecret() {
        return ( is_release() ? API_Yandex::CLIENT_SECRET : API_Yandex::CLIENT_BETA_SECRET );
    }

    /**
     * Возвращает адрес редиректа приложения
     *
     * @return string
     */
    static public function getRedirectURI() {
        return ( is_release() ? API_Yandex::REDIRECT_URI : API_Yandex::REDIRECT_BETA_URI );
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
            $scope = 'account-info payment.to-pattern("' . API_Yandex::PATTERN_ID . '").limit(1,15000)';
        }

        $query = array(
            'client_id'     => self::getClientID(),
            'response_type' => 'code',
            'scope'         => trim( strtolower($scope) ),
            'redirect_uri'  => self::getRedirectURI()
        );
        $link = API_Yandex::AUTH_URI . "/oauth/authorize?" . http_build_query($query);

        return $link;
    }

    /**
     * Получение ключа доступа
     * @see http://api.yandex.ru/money/doc/dg/reference/obtain-access-token.xml
     *
     */
    public function initAccessToken() {
        $uri  = API_Yandex::AUTH_URI . "/oauth/token";
        $post = array(
            'code'          => $this->getAuthCode(),
            'client_id'     => self::getClientID(),
            'grant_type'    => 'authorization_code',
            'redirect_uri'  => self::getRedirectURI()
        );

        if(self::getClientSecret() != '') {
            $post['client_secret'] =  self::getClientSecret();
        }

        $resp = $this->request($uri, $post);
        return $this->getBodyArray($resp);
    }

    /**
     * Запрос создания платежа
     * @see http://api.yandex.ru/money/doc/dg/reference/request-payment.xml
     *
     *
     * @param $sum          Сумма платежа
     * @param $accountId    ИД аккаунта на сайте @see account.id
     * @return array
     */
    public function requestPayment($sum, $accountId) {
        $uri  = API_Yandex::API_URI . "/api/request-payment";
        $post = array(
            'pattern_id'     => API_Yandex::PATTERN_ID,
            'ammount'        => round($sum, 2),
            'Sum'            => round($sum, 2),
            'scid'           => API_Yandex::PATTERN_ID,
            'ShopID'         => API_Yandex::SHOP_ID,
            'CustomerNumber' => $accountId
        );

        if($this->isDebug()) {
            $post['test_payment'] = 'true';
            $post['test_result']  = API_Yandex::STATUS_SUCCESS;
        }

        $resp = $this->request($uri, $post);
        return $this->getBodyArray($resp);
    }

    /**
     * Подтверждение платежа
     * @see http://api.yandex.ru/money/doc/dg/reference/process-payment.xml
     *
     * @param $request_id   Идентификатор запроса, полученный из ответа метода self::requestPayment()
     * @return array
     */
    public function processPayment($request_id, $money_source = 'wallet') {
        $uri  = API_Yandex::API_URI . "/api/process-payment";
        $post = array(
            'request_id'     => $request_id,
            'money_source'   => $money_source
        );

        if($this->isDebug()) {
            $post['test_payment'] = 'true';
            $post['test_result']  = API_Yandex::STATUS_SUCCESS;
        }

        $resp = $this->request($uri, $post);
        return $this->getBodyArray($resp);
    }

    /**
     * Отзыв ключа доступа
     * @see http://api.yandex.ru/money/doc/dg/reference/revoke-access-token.xml
     *
     */
    public function revoke() {
        $uri  = API_Yandex::API_URI . "/api/revoke";
        $resp = $this->request($uri, array());
        return $resp->getStatus();
    }

    /**
     * Проверяем действует ли выданный токен
     *
     * @return bool|mixed
     */
    public function checkToken() {
        $info = $this->accountInfo();
        return ($info['account'] != '');
    }

    /**
     * Информация об аккаунте
     *
     * @return array
     */
    public function accountInfo() {
        $uri  = API_Yandex::API_URI . "/api/account-info";
        $resp = $this->request($uri, array());
        return $this->getBodyArray($resp);
    }
}