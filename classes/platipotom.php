<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payment_keys.php");
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/template.php');
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/billing.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/memBuff2.php");

/**
 * Class platipotom
 * В попап оплаты PRO, карусели, автоответов, закреплений профиля и закреплений услуг добавить кнопку "Купить в кредит".
 */
class platipotom {
    
    /**
     * Адрес платежной системы
     */
    const SERVER_URL = 'https://pay.platipotom.ru';
    const SERVER_LOCAL = '/bill/test/platipotom.php';
    
    /**
     * Код платежной системы для внутреннего учета
     */
    const PAYMENT_CODE = 15;
    
    /**
     * Идентификатор магазина. Выдается при заключении договора.
     * @todo Добавить Тестовый аккаунт и указать его в SHOP_ID_TEST
     */
    const SHOP_ID = '10100';
    const SHOP_ID_TEST = '10100';
    
    
    /**
     * Максимальная сумма первого платежа
     */
    const PRICE_MAX = 1000;
    
    
    /**
     * Максимальная сумма второго и последующих платежей пользователя
     */
    const PRICE_MAX_MORE = 1000;
    
    
    /**
     * Максимальная сумма платежа для покупки ПРО
     */
    const PRICE_MAX_PRO = 1000;
    
    
    /**
     * Полный адрес платежной системы
     * 
     * @var string
     */
    protected $url;
    
    protected $shop_id;
    
    public $TABLE = 'account_operations_pp';
    
    private $is_pro = false;


    public function __construct($is_pro = false)
    {
        $this->url = !is_release() ? self::SERVER_LOCAL : self::SERVER_URL;
        $this->shop_id = is_release() ? self::SHOP_ID : self::SHOP_ID_TEST;
        $this->is_pro = $is_pro;
    }
    
    
    /**
     * Генерирует и возвращает форму оплаты
     * orderid - Уникальный идентификатор заказа в базе магазина.
     * subid - Уникальный идентификатор пользователя.
     * price - Суммарная стоимость покупки в рублях.
     * subtitle – Заголовок покупки, который отобразиться в окне фрейма и будет 
     *     использоваться внутри личного кабинета пользователя и магазина в системе «Плати потом». 
     *     Внимание текст должен передаваться в кодировке UTF8
     * sig – подпись инициирования платежа, является MD5-суммой строки shopid + orderid + key, 
     *     где key – секретный ключ магазина, который можно посмотреть в личном кабинете
     *     магазина в системе «Плати Потом».
     * @todo В примере sig формируется по-другому
     * 
     * @param int $price Сумма к оплате
     * @param int $bill_id Номер счета к оплате
     * @param int $bill_reserve_id ИД из таблицы bill_reserve
     * 
     * @return string Html-форма оплаты
     */
    public function render($price, $bill_id, $bill_reserve_id) 
    {
        //Сумма должна быть положительной
        if ($price <= 0) {
            return false;
        }
        
        //Сумма не должна быть больше допустимой
        if ($price > $this->getMaxPrice($bill_id)) {
            return false;
        }
        
        $order_id = $this->savePayment($bill_id, $price, $bill_reserve_id);
        
        $user = new users();
        $user->GetUserByUID(get_uid(false));
        $reg_date = dateFormat('U', $user->reg_date);
        
        $formData = array(
            'shopid' => $this->shop_id,
            'orderid' => $order_id,
            'subid' => $bill_id,
            'price' => (int)$price,
            'subtitle' => iconv('cp1251', 'utf-8', 'Пополнение счета'),
            'sig' => md5($this->shop_id . $order_id . PP_KEY . $user->login . $reg_date),
            'data[subid_register_date]' => $reg_date,
            'data[nickname]' => $user->login
        );
        
        $form = Template::render(ABS_PATH . '/templates/platipotom.php', array(
            'url' => $this->url,
            'formData' => $formData            
        ));
        return str_replace("\n", '', $form);
    }
    
    
    /**
     * Формируем подпись
     * Подпись платежа – это строка сформированная алгоритмом шифрования MD5 из набора
     * параметров платежа. Пример генерации подписи:
     * price = 30000, orderid = “5276ahe”, subid = 211637383, key = “__long_secret_passphrase__”
     * sig = md5(price + orderid + subid + key) = md5(“300005276ahe211637383__long_secret_passphrase__”) = 
     * “f52085ee39d017d958e78b6c652e539d”
     * 
     * @param int $price
     * @param int $order_id ИД заказа
     * @param int $sub_id ИД счета пользователя
     * @param array $data Массив с дополнительными параметрами
     */
    public function getSig($price, $order_id, $sub_id, $data = array())
    {
        $priceKop = $price * 100; // Цена в копейках
        ksort($data);
        $extraDataString = implode('', $data);
        $sig = md5($priceKop . $order_id . $sub_id . PP_KEY . $extraDataString);
        return $sig;
    }

    
    /**
     * Возвращает максимальную сумму покупки, проверив, покупал ли юзер 
     * что-нибудь через Плати потом ранее
     * @param type $bill_id ИД счета
     * @return int
     */
    public function getMaxPrice($bill_id = 0)
    {
        $maxPrice = self::PRICE_MAX;
        
        if ($this->is_pro) {
            $maxPrice = self::PRICE_MAX_PRO;
        } else {
            $uid = get_uid(false);

            if ($uid > 0) {
                $memBuff = new memBuff();
                if ($maxPriceSaved = $memBuff->get('platipotom_max_price_'.$uid)) {
                    return $maxPriceSaved;
                } else {
                    if(!$bill_id) {
                        $account = new account();
                        $account->GetInfo($uid, true);
                        $bill_id = $account->id;
                    }

                    $sql = "SELECT id FROM account_operations WHERE op_code = 12 AND payment_sys = ?i AND billing_id = ?i";
                    $operation_id = $this->db()->val($sql, self::PAYMENT_CODE, $bill_id);

                    if ($operation_id) {
                        $maxPrice = self::PRICE_MAX_MORE;
                    }
                    $memBuff->set('platipotom_max_price_'.$uid, $maxPrice);
                }
            }
        }
        
        return $maxPrice;
    }
    
    /**
     * Сохраняет данные платежа в базу
     * @param type $data
     */
    public function savePayment($billing_id, $price, $bill_reserve_id)
    {
        return $this->db()->insert($this->TABLE, array(
            'billing_id' => $billing_id, 
            'price' => $price,
            'bill_reserve_id' => $bill_reserve_id
        ), 'id');
    }
    
    public function getPayment($order_id)
    {
        return $this->db()->row("SELECT * FROM {$this->TABLE} WHERE id = ?i", $order_id);
    }
    
    /**
     * Выполняет покупку
     */
    public function order()
    {
        //Запрещаем вывод ошибок
        $this->db()->error_output = false;
        
        $orderid = $_GET['orderid'];
        if (!$orderid) exit;
        
        $json_data = array(
            'status' => '0',
            'time' => time()
        );
        
        $payment = $this->getPayment($orderid);
        
        if ($payment) {
            $data = isset($_REQUEST['data']) && is_array($_REQUEST['data']) ? $_REQUEST['data'] : array();
            $sig = $this->getSig($payment['price'], $orderid, $payment['billing_id'], $data);

            if ($sig == $_GET['sig']) {
                $json_data['status'] = '1';
                $op_id = 0;

                //Занесли деньги
                $account = new account();
                $error = $account->deposit(
                    $op_id, 
                    $payment['billing_id'], 
                    $payment['price'], 
                    //@todo: все тексты должны быть хотябы в константах наверху описания класа!
                    "Платеж через \"Плати потом\". Сумма - {$payment['price']}, номер покупки - {$orderid}", 
                    self::PAYMENT_CODE, 
                    $payment['price']
                );

                if (!$error) {
                    
                    //Пробуем купить
                    $billing = new billing($account->uid);
                    $billing->buyOrder(
                        $payment['bill_reserve_id'], 
                        12,//@todo: подсомнением необходимость параметра
                        array()//@todo: пока нет надобности
                    );

                    $this->db()->query("DELETE FROM {$this->TABLE} WHERE id = ?", $orderid);
                    
                    $memBuff = new memBuff();
                    $memBuff->delete('platipotom_max_price_'.$account->uid);
                }
            }
        }
        
        
        
        return $json_data;
    }
    
    
    /**
     * Была ли у пользователя покупка через ПлатиПотом
     * 
     * @param type $uid
     * @return boolean
     */
    public function isWasPlatipotom($bill_id = 0)
    {
        $value = $this->getMaxPrice($bill_id);
        return $value > self::PRICE_MAX;        
    }


    /**
     * @return DB
     */
    public function db()
    {
        return $GLOBALS['DB'];
    }
    
    
}
