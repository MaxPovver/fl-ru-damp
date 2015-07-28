<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/wallet/wallet.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/wallet/API_OAuth.php");
/**
 * Класс для работы с кошельком WebMoney для автоматической оплаты услуг
 *
 */
class walletAlpha extends Wallet
{
    /**
     * Задаем тип платежного метода
     *
     * @var int
     */
    protected $_type = WalletTypes::WALLET_ALPHA;

    /**
     * Содержит объект класса через который пишем логи
     *
     * @var log
     */
    public $log;

    public $orderNumber;

    /**
     * Инициализация заказа
     */
    const STATE_INIT = 'init';

    /**
     * Заказа инициирован в данном случае следует перенаправлять на страницу оплаты
     */
    const STATE_NEW = 'new';

    /**
     * Статус возврата
     */
    const STATUS_REFUND = 'refund';

    /**
     * Статус проведения платежа, чекаем платеж раз в 3 минуты
     */
    const STATUS_PROGRESS = 'progress';

    /**
     * Статус успешного проведенного платежа
     */
    const STATUS_SUCCESS = 'complete';

    /**
     * Конструктор класса необходимо задать ИД пользователя
     *
     * @param integer $uid ИД пользователя
     */
    public function __construct($uid = null) {
        parent::__construct($uid);

        // Если есть код авторизации все гуд
        if($this->getAccessToken() !== false) {
            $this->api = new API_AlphaBank($this->getAccessToken());
        } else {
            $this->api = new API_AlphaBank();
        }

        $this->log = new log("wallet/alphabank-".SERVER.'-%d%m%Y.log', 'a', '%d.%m.%Y %H:%M:%S : ');
    }

    /**
     * Инициализирует срок действия ключа
     */
    public function initValidity() {
        return;
        //$this->data['validity'] = '3 years';
    }

    public function createOrder($amount = 0) {
        global $DB;

        $insert = array(
            'account_id' => $this->account->id,
            'amount'     => $amount,
            'state'      => self::STATE_INIT
        );

        $this->orderNumber = $DB->insert('alphabank_orders', $insert, 'id');

        return $this->orderNumber;
    }

    /**
     * Обновляем данные заказа
     *
     * @param $id       ИД заказа
     * @param $update   Данные на обновление
     */
    public function updateOrder($id, $update) {
        global $DB;
        $where = $DB->parse('id = ?i', $id);
        $DB->update('alphabank_orders', $update, $where);
    }

    /**
     * Возвращает заказ
     *
     * @param null $id
     * @param array $filter
     * @return mixed
     */
    public function getOrder($id = null, $filter = array()) {
        global $DB;

        if($id !== null) {
            $where = $DB->parse('id = ?', $id);
        } elseif(!empty($filter)) {
            foreach($filter as $field=>$value) {
                $result[] = $DB->parse("{$field} = ?", $value);
            }

            $where = implode(" AND ", $result);
        } else {
            $where = $DB->parse('account_id = ?', $this->account->id);
        }

        $sql = "SELECT * FROM alphabank_orders WHERE {$where} ORDER BY create_time DESC LIMIT 1";

        return $DB->row($sql);
    }

    /**
     * Автооплата услуги
     *
     * @param $sum
     * @return bool|mixed
     */
    public function payment($sum) {
        $this->createOrder($sum);
        $this->api->getAccessData('autopay');
        $result = $this->api->register($sum, $this->orderNumber, $this->account->id);

        if($result['orderId'] != '') {
            walletAlpha::updateOrder($this->orderNumber, array('order_id' => $result['orderId'], 'state' => walletAlpha::STATE_NEW));

            $payment = $this->api->paymentOrderBinding($result['orderId']);

            switch($payment['errorCode']) {
                case API_AlphaBank::STATUS_SUCCESS:
                    $status = $this->api->getOrderStatus($result['orderId']);

                    $update = array(
                        'pan'               => $status['Pan'],
                        'expiration'        => $status['expiration'],
                        'cardholder_name'   => $status['cardholderName'],
                        'ip'                => $status['Ip'],
                        'binding_id'        => Wallet::des()->encrypt($status['bindingId'])
                    );

                    $update['state'] = $this->deposit($this->account, $this->account->id, $status, $this->data['wallet'], $this->orderNumber, $sum);
                    $this->updateOrder($this->orderNumber, $update);
                    break;
                default:  // Ошибка оплаты
                    ob_start();
                    var_dump($result);
                    var_dump($payment);
                    $content = ob_get_clean();
                    $this->log->writeln("FAIL Payment:\naccount:{$this->account->id}\n");
                    $this->log->write("Request:\n " . $this->api->last_request->getBody());
                    $this->log->write("Result:\n {$content}");
                    return false;
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
    }

    /**
     * Зачисление денег
     *
     * @param $account
     * @param $account_id
     * @param $status
     * @param $wallet
     * @param $orderNumber
     * @param $sum
     * @return string
     */
    public function deposit($account, $account_id,  $status, $wallet, $orderNumber, $sum) {
        if($status['OrderStatus'] == API_AlphaBank::STATUS_SUCCESS_PAYMENT) { // Оплата уже прошла успешно
            $paymentDateTime = date('d.m.Y H:i');
            $descr = "Оплата с карты {$wallet} сумма - {$sum}, обработан {$paymentDateTime}, номер покупки - {$orderNumber}";
            $account->deposit($op_id, $account_id, $sum, $descr, 6, $sum, 12);
            return walletAlpha::STATUS_SUCCESS;
        } else {
            return walletAlpha::STATUS_PROGRESS;
        }
    }

    /**
     * Авторизация в системе для последующих платежей
     *
     * @return mixed
     */
    public function authorize() {
        $orderId = $this->createOrder(API_AlphaBank::REGISTER_SUM);
        return $this->api->getAuthorizeUri($orderId, $this->account->id);
    }

    /**
     * Проверка на зачисление денег
     */
    static public function checkProgressOrders() {
        global $DB;

        // Проверяем в течении часа
        $sql  = "SELECT * FROM alphabank_orders WHERE state = ? AND create_time + '1 hour'::interval > now()";
        $rows = $DB->rows($sql, self::STATUS_PROGRESS);

        if(!empty($rows)) {
            $api  = new API_AlphaBank();
            $api->getAccessData('autopay');

            // @todo нужно как-то оптимизировать
            foreach($rows as $order) {
                $status = $api->getOrderStatus($order['order_id']);
                $update['state'] = self::deposit(new account(), $order['account_id'], $status, $order['pan'], $order['id'], $order['amount']);
                self::updateOrder($order['id'], $update);
            }
        }
    }
}

class API_AlphaBank {

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

    const STATUS_SUCCESS_PAYMENT = 2;

    /**
     * Код после оплаты
     */
    const STATUS_SUCCESS = 0;

    /**
     * Кодировки используемые в системе
     */
    const SERVER_ENCODING = 'CP1251';

    /**
     * Кодировка используемая для отправления запросов
     */
    const SEND_ENCODING   = 'UTF-8';

    /**
     * Адрес API с которым взаимодействуем после авторизации
     */
    const API_URI  = 'https://engine.paymentgate.ru/payment/rest/';

    /**
     * Адрес API с которым взаимодействуем после авторизации
     */
    const API_BETA_URI  = 'https://test.paymentgate.ru/testpayment/rest/';

    /**
     * Код валюты платежа ISO 4217, рубли
     */
    const CURRENCY_RUB  = 810;

    /**
     * Язык в кодировке ISO 639-1.
     */
    const LANGUAGE = 'ru';

    // @todo вынести в отдельный файл убрать парольки с беты и альфы
    const LOGIN_BINDING  = 'freelance_binding-api';
    const PASSWD_TEST_BINDING = 'freelance';
    const PASSWD_BINDING = ALPHA_SECURE_PASSWD;

    const LOGIN_AUTOPAY  = 'freelance_autopay-api';
    const PASSWD_TEST_AUTOPAY = 'freelance';
    const PASSWD_AUTOPAY = ALPHA_SECURE_PASSWD;

    const LOGIN  = 'freelance-api';
    const PASSWD_TEST = 'freelance';
    const PASSWD = ALPHA_SECURE_PASSWD;


    const RETURN_BETA_URL = 'http://beta.fl.ru/income/auto-card.php';

    const RETURN_URL = 'https://www.fl.ru/income/auto-card.php';

    /**
     * Сумма списываемая при регистрации
     */
    const REGISTER_SUM = 10;

    /**
     * Конструктор класса
     *
     * @param string $code            ИД Привязки
     */
    public function __construct($accessToken = null) {
        $this->setAccessToken($accessToken);
        $this->getAccessData('bind');
        $this->log = new log("wallet/api-alphabank-".SERVER.'-%d%m%Y.log', 'a', '%d.%m.%Y %H:%M:%S : ');
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
     * Возвращает ИД приложения
     *
     * @return string
     */
    static public function getAPIUri() {
        return ( is_release() ? API_AlphaBank::API_URI : API_AlphaBank::API_BETA_URI );
    }

    /**
     * Возвращает адрес редиректа приложения
     *
     * @return string
     */
    static public function getReturnURL() {
        return ( is_release() ? API_AlphaBank::RETURN_URL : API_AlphaBank::RETURN_BETA_URL );
    }

    /**
     * Возвращает конфигурацию запроса
     *
     * @return array
     */
    public function getRequestConfig() {
        return $this->_requestConfig;
    }

    public function getAccessData($type = null) {
        switch($type) {
            case 'bind':
                $this->userName = API_AlphaBank::LOGIN_BINDING;
                $this->password = is_release() ? API_AlphaBank::PASSWD_BINDING : API_AlphaBank::PASSWD_TEST_BINDING;

                break;
            case 'autopay':
                $this->userName = API_AlphaBank::LOGIN_AUTOPAY;
                $this->password = is_release() ? API_AlphaBank::PASSWD_AUTOPAY : API_AlphaBank::PASSWD_TEST_AUTOPAY;

                break;
            default:
                $this->userName = API_AlphaBank::LOGIN;
                $this->password = is_release() ? API_AlphaBank::PASSWD : API_AlphaBank::PASSWD_TEST ;

                break;
        }
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

    public function getAuthorizeUri($orderId, $accountId) {
        $this->error = false;
        $data = $this->register(API_AlphaBank::REGISTER_SUM, $orderId, $accountId, 'Привязка пластиковой карты');
        if($data['errorCode'] > 0) {
            $this->error = true;
            return $data;
        }
        walletAlpha::updateOrder($orderId, array('order_id' => $data['orderId'], 'state' => walletAlpha::STATE_NEW));
        return $data['formUrl'];
    }

    /**
     * Получаем данные в форме массива
     *
     * @param HTTP_Request2 $resp     Объект запроса
     * @return array
     */
    public function getBodyArray($resp) {
        if($resp == '') return array();
        $body = json_decode($resp->getBody(), true);
        return $body;
    }

    public function register($sum, $orderId, $accountId, $description = '') {
        $uri  = API_AlphaBank::getAPIUri() . "register.do";
        $post = array(
            'userName'    => $this->userName,
            'password'    => $this->password,
            'amount'      => $sum * 100, // сумма в копейках
            'returnUrl'   => API_AlphaBank::getReturnURL(),
            'orderNumber' => $orderId,
            'clientId'    => $accountId,
            'description' => iconv('Windows-1251', 'UTF-8', $description)
        );

        $resp = $this->request($uri, $post);
        return $this->getBodyArray($resp);
    }

    public function refund($sum, $orderId) {
        $uri  = API_AlphaBank::getAPIUri() . "refund.do";
        $post = array(
            'userName'    => $this->userName,
            'password'    => $this->password,
            'amount'      => $sum * 100,  // сумма в копейках
            'orderId'     => $orderId,
        );

        $resp = $this->request($uri, $post);
        return $this->getBodyArray($resp);
    }

    public function reverse($orderId) {
        $uri  = API_AlphaBank::getAPIUri() . "reverse.do";
        $post = array(
            'userName'    => $this->userName,
            'password'    => $this->password,
            'orderId'     => $orderId,
        );

        $resp = $this->request($uri, $post);
        return $this->getBodyArray($resp);
    }

    public function getBindings($accountId) {
        $uri  = API_AlphaBank::getAPIUri() . "getBindings.do";
        $post = array(
            'userName'    => $this->userName,
            'password'    => $this->password,
            'clientId'    => $accountId
        );

        $resp = $this->request($uri, $post);
        return $this->getBodyArray($resp);
    }

    public function getOrderStatus($orderId) {
        $uri  = API_AlphaBank::getAPIUri() . "getOrderStatus.do";
        $post = array(
            'userName'    => $this->userName,
            'password'    => $this->password,
            'orderId'     => $orderId
        );

        $resp = $this->request($uri, $post);
        return $this->getBodyArray($resp);
    }

    public function paymentOrderBinding($mdOrder, $bindingId = null) {
        $uri  = API_AlphaBank::getAPIUri() . "paymentOrderBinding.do";
        $post = array(
            'userName'    => $this->userName,
            'password'    => $this->password,
            'mdOrder'     => $mdOrder,
            'bindingId'   => $bindingId === null ? $this->getAccessToken() : $bindingId
        );

        $resp = $this->request($uri, $post);
        return $this->getBodyArray($resp);
    }

    /**
     * Проверяем действует ли выданный токен
     *
     * @return bool|mixed
     */
    public function checkToken() {
        return true;
    }
}