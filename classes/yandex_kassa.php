<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/template.php');
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payment_keys.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/billing.php");
//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");

/*
 * Класс для работы с сервисом Яндекс.Касса
 * https://money.yandex.ru/start/#1
 * 
 * Ссылка на протокол https://money.yandex.ru/doc.xml?id=526537
 * scid=52128
 * ShopID=17004
 * 
 * Тестовый счет для проверки платежей Яндекс.Денег:
 * Логин: testkunchenko
 * Пароль: 123456
 * Платежный пароль: 123456789
 * Счет: 4100322324227
 * Пополнить тестовый счет - https://demomoney.yandex.ru/shop.xml?scid=50215
 * 
 * Тестовые данные для проверки платежей по картам:
 * номер карты 4268 0337 0354 5624, 
 * срок действия - любой в будущем, 
 * cvv 123, 
 * указать любую фамилию держателя карты.
 * 
 * Значения shopid и scid в коде формы не меняем, они от нашего магазина.
 */

class yandex_kassa {
    
    
    /**
     * Идентификатор магазина при обычных платежах
     */
    const SHOPID_DEPOSIT        = 17004;
    const SCID_DEPOSIT_MAIN     = 8420;
    const SCID_DEPOSIT_TEST     = 52128;
    
    
    
    /**
     * Идентификатор магазина при платежах через БС
     */
    const SHOPID_SBR     = 17233;
    const SCID_SBR_MAIN  = 9283;
    const SCID_SBR_TEST  = 52249;
    
    /**
     * Url-адрес платежной системы при реальных платежах
     */
    const URL_MAIN = "https://money.yandex.ru/eshop.xml";
    
    /**
     * Url-адрес платежной системы при тестовых платежах
     */
    const URL_TEST = "https://demomoney.yandex.ru/eshop.xml";
    
    /**
     * Url-адрес платежной системы при платежах на локальном сервере
     */
    const URL_LOCAL = "/bill/test/ykassa.php";
    
    /**
     * Оплата из кошелька в Яндекс.Деньгах.
     */
    const PAYMENT_YD = "PC";
    
    /**
     * Оплата с произвольной банковской карты.
     */
    const PAYMENT_AC = "AC";
    
    /**
     * Оплата из кошелька в системе WebMoney
     */
    const PAYMENT_WM = "WM";
    
    /**
     * Оплата с Альфа-клик
     */
    const PAYMENT_AB = "AB";
    
    /**
     * Оплата из Сбербанк Онлайн
     */
    const PAYMENT_SB = "SB";
    
    /**
     * Максимальные суммы для оплаты
     */
    const MAX_PAYMENT_ALFA = 15000;
    const MAX_PAYMENT_SB = 10000;
    
    /**
     * Платежные системы, доступные для оплаты demomoney.yandex.ru
     */
    protected $test_payments = array(
        self::PAYMENT_YD,
        self::PAYMENT_AC
    );
    
    /**
     * Список магазинов и соответствующих им боевых витрин
     * @var array
     */
    protected $shops_main = array(
        self::SHOPID_DEPOSIT => self::SCID_DEPOSIT_MAIN,
        self::SHOPID_SBR     => self::SCID_SBR_MAIN
    );

    
    /**
     * Список магазитов и соответствующих им тестовых витрин
     * @var array 
     */
    protected $shops_test = array(
        self::SHOPID_DEPOSIT => self::SCID_DEPOSIT_TEST,
        self::SHOPID_SBR     => self::SCID_SBR_TEST        
    );

    
    /**
     * Текущий список доступных магазинов и витрин 
     * в записимости от режима работы
     * @var array
     */
    protected $shops = array();
    
    /**
     * Флаг тестового режима
     * 
     * @var bool
     */
    protected $is_test = false;
    
    /**
     * Полный адрес платежной системы
     * 
     * @var string
     */
    protected $url;
    
    /**
     * Массив доступных способов оплаты
     */
    protected $payments;
    
    protected $shopid;
    protected $scid;

    protected $url_success;
    protected $url_fail;
    
    protected $ip_real = array(
        '77.75.157.168',
        '77.75.157.169',
        '77.75.159.166',
        '77.75.159.170'
    );
    
    protected $ip_test = array(
        '77.75.157.166',
        '77.75.157.170',
        '127.0.0.1',
        '2.92.3.100',
        '62.213.65.100'
    );
    
    /**
     * Идентификатор транзакции в ИС Оператора. Должен дублировать поле invoiceId запроса.
     */
    public $params;
    
    /**
     * Текстовое пояснение в случае отказа принять платеж.
     */
    public $message = "";
    
    /**
     * Дополнительное текстовое пояснение ответа Контрагента. 
     * Как правило, используется как дополнительная информация об ошибках. 
     * Необязательное поле
     */
    public $techMessage = "";


    
    
    
    public function __construct() 
    {
        $this->payments = array(
            3 => self::PAYMENT_YD,
            6 => self::PAYMENT_AC,
            10 => self::PAYMENT_WM,
            16 => self::PAYMENT_AB,
            17 => self::PAYMENT_SB
        );
        
        
        $this->setTest(!is_release());
    }
    
    
    
    /**
     * Принудительно переключает тестовый режим независимо от сервера
     * true для тестовых, false для реальных платежей
     * 
     * @param bool $value
     */
    public function setTest($value) 
    {
        $this->is_test = (bool) $value;
        $this->init();
    }
    
    
    
    /**
     * Указать магазин
     * 
     * @param int $shopid
     * @return boolean
     */
    public function setShop($shopid)
    {
        if(!isset($this->shops[$shopid])) return false;

        $this->shopid = $shopid;
        $this->scid = $this->shops[$shopid];
        
        return true;
    }

    

    /**
     * Заполняет поля класса данными в зависимости от настроек
     */
    protected function init() 
    {
        if($this->is_test) 
        {
            $this->url = is_local() ? self::URL_LOCAL : self::URL_TEST;
            $this->shops = $this->shops_test;
        } 
        else 
        {
            $this->url = self::URL_MAIN;
            $this->shops = $this->shops_main;
        }
        
        
        //Магазин и витрина поумолчанию
        $this->shopid = self::SHOPID_DEPOSIT;
        $this->scid = $this->shops[self::SHOPID_DEPOSIT];
    }
    
    
    
    
    /**
     * Генерирует и возвращает форму оплаты
     * 
     * @param int $ammount Сумма к оплате
     * @param int $bill_id Номер счета к оплате
     * @param int $payment Код способа оплаты из payments
     * @param int $billReserveId ID заказа в bill_reserve
     * 
     * @return string Html-форма оплаты
     */
    public function render($ammount, $bill_id, $payment, $billReserveId = null) 
    {
        //Сумма должна быть положительной
        if ($ammount <= 0) {
            return false;
        }
        
        //Неизвестный способ оплаты. Используем Яндекс.Деньги
        if (!in_array($payment, $this->payments)) {
            $payment = self::PAYMENT_YD;
        }
        
        if ($this->is_test && !in_array($payment, $this->test_payments)) {
            $this->url = self::URL_LOCAL;
        }
        
        $data = array(
            'url' => $this->url,
            'scid' => $this->scid,
            'shopId' => $this->shopid,
            'ammount' => $ammount,
            'customerNumber' => $bill_id,
            'payment' => $payment//,
            //'cps_email',
            //'cps_phone'
        );
        
        //зачисление осносится к услуги из очереди
        if($billReserveId > 0) {
            $data['billReserveId'] = $billReserveId;
        }
        
        $form = Template::render(ABS_PATH . '/templates/yandex.kassa.php', $data);
        return str_replace("\n", '', $form);
    }
    
    
    
    
    /**
     * Проверка заказа
     */
    public function order($pay = false) {
        
        $this->initParams();

        $code = $this->validateParams($pay);
        
        if ($code == 0) {
            if (!$pay) {
                $code = $this->insertTemp(); 
            } else {
                $code = $this->addOperation();
            }
        }
        
        return $this->getResult($code);
    }
    
    
    
    
    private function initParams() {
        $post = array(
            'requestDatetime' => __paramInit('string', null, 'requestDatetime'),
            'action' =>	__paramInit('string', null, 'action'),
            'shopId' => __paramInit('int', null, 'shopId'),
            'invoiceId' => __paramInit('string', null, 'invoiceId'),
            'customerNumber' => __paramInit('string', null, 'customerNumber'),
            'orderCreatedDatetime' => __paramInit('string', null, 'orderCreatedDatetime'),
            'orderSumAmount' => __paramInit('string', null, 'orderSumAmount'),
            'orderSumCurrencyPaycash' => __paramInit('string', null, 'orderSumCurrencyPaycash'),
            'orderSumBankPaycash' => __paramInit('string', null, 'orderSumBankPaycash'),
            'shopSumAmount' => __paramInit('string', null, 'shopSumAmount'),
            'shopSumCurrencyPaycash' => __paramInit('string', null, 'shopSumCurrencyPaycash'),
            'shopSumBankPaycash' => __paramInit('string', null, 'shopSumBankPaycash'),
            'paymentPayerCode' => __paramInit('string', null, 'paymentPayerCode'),
            'paymentType' => __paramInit('string', null, 'paymentType'),
            'md5' => __paramInit('string', null, 'md5'),
            'orderId' => __paramInit('int', null, 'orderId', null)
        );
        
        $this->params = $post;
    }
    
    
    
    
    /**
     * Проверяет параметры платежа и возвращает код
     * 0 - успешно, 1 - ошибка авторизации, 200 - Ошибка разбора запроса
     */
    private function validateParams($pay) {
        if ($this->isErrorIP()) {
            $this->message = "Неразрешенный IP: ".getRemoteIp();
            return 200;            
        }        
        if ($this->isErrorMd5()) {
            $this->message = "Неверная хэш-сумма";
            return 1;
        }        
        if ($this->isErrorShop()) {
            $this->message = "Неверный магазин";
            return 200;            
        }        
        if ($this->isErrorAmmount()) {
            $this->message = "Неверная сумма";
            return 200;
        }        
        if ($pay && $this->isErrorInvoiceId()) {
            $this->message = "Платеж не найден";
            return 200;
        }        
        return 0;
    }
    
    private function isErrorIP() {
        $ip = getRemoteIp();        
        $allowedIPs = $this->is_test ? $this->ip_test : $this->ip_real;        
        return !in_array($ip, $allowedIPs);    
    }
    
    private function isErrorMd5() {
        $data = array(
            $this->params['action'],
            $this->params['orderSumAmount'],
            $this->params['orderSumCurrencyPaycash'],
            $this->params['orderSumBankPaycash'],
            $this->params['shopId'],
            $this->params['invoiceId'],
            $this->params['customerNumber'],
            YK_KEY
        );
        
        $hash = md5(implode(';', $data));
        
        return strtoupper($hash) != $this->params['md5'];
    }
    
    private function isErrorShop() {
        return !isset($this->shops[$this->params['shopId']]);
    }
    
    private function isErrorAmmount() {
        return $this->params['orderSumAmount'] <= 0;
    }
    
    private function isErrorInvoiceId() {
        global $DB;
        $tmp_payment = $DB->val('SELECT id FROM account_operations_yd WHERE invoice_id = ?', $this->params['invoiceId']);
        return !$tmp_payment;
    }
    

    
    private function insertTemp() 
    {
        global $DB;

        $uid = $DB->val('SELECT uid FROM account WHERE id = ?i', 
                $this->params['customerNumber']);

        if (!$uid) {
            $this->message = 'Not found user account (customerNumber)';
            return 200;
        }
        
        
        //проверка возможности зачисления по ID заказа
        //и если транзакция была раньше то проверяется и не был ли куплен заказ
        //если куплен то все ок если нет и делаем проверку checkOrder
        if ($this->params['orderId']) {
            $billing = new billing($uid);
            if (!$billing->checkOrder($this->params)) {
                $billing->cancelReserveById($this->params['orderId']);
                $this->message = 'Failed check order';
                return 200;
            }
        }
        
        
        $dups = $DB->val('SELECT id FROM account_operations_yd WHERE invoice_id = ?', 
                $this->params['invoiceId']);
        
        if (!$dups) {
            $shopParams = $this->getShopParams();
        
            $descr = "Платеж через Яндекс.Кассу. Сумма - {$this->params['orderSumAmount']}, номер покупки - {$this->params['invoiceId']}";
            $descr .= $shopParams['op_descr'];
            
            $DB->insert('account_operations_yd', array(
                'billing_id'  => $this->params['customerNumber'],
                'op_date'     => $this->params['requestDatetime'],
                'op_code'     => $shopParams['op_code'],
                'ammount'     => $shopParams['ammount'],
                'trs_sum'     => $this->params['orderSumAmount'],
                'descr'       => $descr,
                'invoice_id'  => $this->params['invoiceId'],
            ), 'id');
        
            if($DB->error) {
                //@todo: может лучше юзать techMessage ? 
                //message в ЯД нигде не отображается случаем?
                $this->message = $DB->error;
                return 200;
            }
        }
        
        
        //если все ок
        return 0;
    }
    
    
    
    
    
    /**
     * Выполняет платеж
     */
    private function addOperation() 
    {
        global $DB;

        $DB->error_output = false;
        $shopParams = $this->getShopParams();
        
        $payment = $DB->row('
            SELECT 
                aoy.id,            
                aoy.descr,
                ao.id AS acc_op_id 
            FROM account_operations_yd AS aoy 
            LEFT JOIN account_operations AS ao ON ao.id = aoy.acc_op_id AND ao.billing_id = aoy.billing_id
            WHERE invoice_id = ?', 
            $this->params['invoiceId']);
        
        //Пополнение и покупка уже были отвечаем успехом
        if ($payment['acc_op_id'] > 0) {
            return 0;
        }
        
        
        $DB->start();
        
        $op_id = 0;
        $data = array();
        $billing = null;
        
        $account = new account();       
        //Заносим деньги на ЛС
        $error = $account->deposit(
                $op_id,
                $this->params['customerNumber'], 
                $shopParams['ammount'], 
                $payment['descr'], 
                array_search($this->params['paymentType'], $this->payments), 
                $this->params['orderSumAmount'], 
                $shopParams['op_code']);     
        
        //Если все без ошибок и ЛС зачислены то пробуем купить заказ
        if (!$error && $op_id > 0) {
            
            $success = true;
            $data['acc_op_id'] = $op_id;
            
            //Пробуем купить заказ за который занесли деньги выше
            //Если заказ уже куплен или отменен то ничего не делаем но получим успех
            if ($this->params['orderId']) {
                $billing = new billing($account->uid);
                if($success = $billing->buyOrder(
                        $this->params['orderId'], 
                        $shopParams['op_code'],//@todo: подсомнением необходимость параметра
                        $this->params)) {
                    
                    $data['bill_reserve_id'] = $this->params['orderId'];
                }
            }
            
            //Фиксируем ID операции пополнения ЛС и ID купленного заказа при наличии
            if ($success) {
                $DB->update('account_operations_yd', $data, 'id = ?i', $payment['id']);
                $DB->commit();
                return 0;
            } else {
                $this->message = sprintf('Failed to purchase order #%s', $this->params['orderId']);
            }
            
        } else {
            $this->message = 'Failed deposit to account';
        }        
        
        //Не удалось приобрести заказ откатываем транзакцию 
        //и возвращаем ошибку что приводит к возврату средств        
        $DB->rollback();
        
        //Если отказ принять деньги то и отменяем заказ
        //чтобы не висел в истории
        if ($billing && $this->params['orderId'] > 0) {
            $billing->cancelReserveById($this->params['orderId']);
        }
        
        //Ошибка, возврат средств
        return 100;
    }
    
    
    
    
    
    
    /**
     * Проверка данных на дублирование
     *
     * @return integer id предыдущей операции, false если операция не найдена
     */
    private function checkDups($str) {
        global $DB;

        $sql = "SELECT id FROM account_operations WHERE descr = ?";
        $out = $DB->val($sql, $str);
        if ($out !== null)
            return $out;
        return false;
    }
    
        
        
        
        
        
    /**
     * Собирает ряд настроек, зависящих от магазина
     */
    private function getShopParams() 
    {
        $data = array();
        
        switch ($this->params['shopId']) 
        {
            //@todo: В данной обработке совершенно нет никакой необходимости, покрайне мене пока.
            //Весь этот подход скопирован похоже с класса яндекс денег но тут бесполезен.
            
            /*
            case self::SHOPID_SBR : // Резерв денег по БС
                $data['op_code'] = sbr::OP_RESERVE;
                $data['ammount'] = 0;
                $data['op_descr'] = ", СбР #000"; //Не нашел, где передается ИД операции БС
                break;
            */
            
            //case self::SHOPID_DEPOSIT : // Перевод денег на личный счет
            
            default:
                $data['op_code'] = 12;
                $data['ammount'] = $this->params['orderSumAmount'];
                $data['op_descr'] = '';
                break;
        }
        
        return $data;
    }

    
    
    
    
    /**
     * Составляет параметры ответа
     */
    private function getResult($code) {
        $result = array(
            'performedDatetime' => date('c'),
            'code' => $code,
            'invoiceId' => $this->params['invoiceId'],
            'shopId' => $this->params['shopId']
        );
        if ($this->message) {
            $result['message'] = $this->message;
        }
        if ($this->techMessage) {
            $result['techMessage'] = $this->techMessage;
        }
        return $result;
    }
    
}