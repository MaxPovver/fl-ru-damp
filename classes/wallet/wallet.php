<?php

/**
 * Для шифрования данных
 */
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/JWS.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/Crypt/DES.php";
/**
 * Подключаем файл для работы с ключами оплаты
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payment_keys.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");

/**
 * Класс для работы с кошельками и автооплатой
 */
abstract class Wallet
{
    /**
     * Тип метода оплаты (привязанного кошелька) @see WalletTypes::WALLET_* WalletTypes::getAllTypes();
     *
     * @integer
     */
    protected $_type;

    /**
     * ИД Пользователя
     *
     * @var int|null
     */
    public $uid;

    /**
     * Для работы с Базой
     *
     * @var DB
     */
    protected $_db;

    /**
     * Задать время активации при сохранении или нет
     *
     * @var bool
     */
    public $isNotNewAcessToken = false;


    /**
     * Код которым шифруем данные (можно заменить только после того как в базе перекодируют данные по предыдущему коду)
     *
     */
    const PIN_CODE        = TOKEN_PIN;

    /**
     * Функция для оплаты
     *
     * @return mixed
     */
    abstract function payment($sum);

    /**
     * Конструктор класса
     *
     * @param integer $uid   ИД Пользователя
     */
    public function __construct($uid = null) {
        global $DB;
        if($uid === null) {
            $uid = get_uid(false);
        }
        $this->uid = $uid;
        $account = new account();
        $account->GetInfo($uid, true);
        $this->account = $account;
        $this->_db  = $DB;

        $this->initWallet();
    }

    /**
     * Инициализирует класс для шифровки данных через DES
     *
     * @return Crypt_DES
     */
    static public function des() {
        $des = new Crypt_DES();
        $des->setKey(Wallet::PIN_CODE);
        return $des;
    }

    /**
     * Инициализирует срок действия ключа (у каждой системы он свой, по умолчанию 3 года)
     */
    public function initValidity() {
        $this->data['validity'] = '3 years';
    }

    /**
     * Инициализируем данные кошелька
     */
    public function initWallet() {
        $sql = "SELECT *, (access_time + validity) as validity_time FROM bill_wallet WHERE type = ?i AND uid = ?i";
        $this->data = $this->_db->row($sql, $this->_type, $this->uid);
    }

    /**
     * Сохраняем данные кошелька (для сохранения должны быть определены данные в перменной $this->data
     * согласно таблице bill_wallet
     *
     * @return integer Возвращает ИД записи в таблице
     */
    public function saveWallet() {
        if(empty($this->data))  {
            return false; // Данные для сохранения не определены
        }

        if($this->data['access_token'] === null) {
            $this->data['validity']    = null;
            $this->data['access_time'] = null;
            $this->data['active']      = false;
        } else {
            // Деактивируем кошелек который активирован в настоящий момент у пользователя
            Wallet::clearActiveWallet($this->uid);
            if(!$this->isNotNewAcessToken) {
                $this->initValidity();
                $this->data['access_time'] = 'now';
            }
            $this->data['active']      = true;
        }

        foreach($this->data as $name=>$value) {
            if($name == 'validity_time') continue;
            $fields[] = $this->_db->parse("{$name} = ?", $value);
        }
        $fields_sql = implode(", ", $fields);

        $sql = "UPDATE bill_wallet SET {$fields_sql} WHERE type = ?i AND uid = ?i RETURNING id";
        $res = $this->_db->row($sql, $this->_type, $this->uid);

        // Кошелька еще нет совсем тогда создаем по данным которые у нас есть
        if(empty($res)) {
            $data = $this->data;
            unset($data['validity_time']);
            return $this->_db->insert('bill_wallet', $data, 'id');
        }

        return $res['id'];
    }

    /**
     * Удаляем совсем данные кошелька
     *
     * @todo совсем наверное удалять не стоит, нужен флаг удаления
     */
    public function removeWallet() {
        $this->_db->query("DELETE FROM bill_wallet WHERE type = ?i AND uid = ?i", $this->_type, $this->uid);
    }

    /**
     * Возвращает ключ доступа для платежа если данные не инициализированы пытается их инициализировать из БД
     *
     * @return bool|int|string
     */
    public function getAccessToken() {
        if(empty($this->data)) {
            $this->initWallet();
        }

        if($this->data['access_token'] == null || strtotime($this->data['validity_time']) < time()) {
            return false;
        } else {
            return Wallet::des()->decrypt(JWS_Base64::urlDecode($this->data['access_token']));
        }
    }

    /**
     * Безопасный вывод номера кошелька (выводится не весь кошелек)
     *
     * @param integer $len      Сколько знаков показывать вначале и в конце
     * @param string  $char     Символ которым заменяем
     */
    public function getWalletBySecure() {
        if(empty($this->data)) {
            $this->initWallet();
        }
        $wallet = $this->data['wallet'];

        return self::secureString($wallet);
    }

    /**
     * Скрываем символы при выводе в строке
     *
     *
     * @param string  $string   Строка в которой скрываем
     * @param integer $len      Сколько знаков показывать вначале и в конце
     * @param string  $char     Символ которым заменяем
     * @return bool|string
     */
    static function secureString($string, $len = 4, $char = '*') {
        if($string == '') return false;
        if($len*2 > strlen($string)) $len = strlen($string) / 2;
        if($len <= 0) $len = 4;
        $repeat = ( strlen($string) - $len*2 );
        // Если скрывает меньше 3 символов уменьшаем длинну в 2 раза
        if($repeat < 3 ) {
            $len = round($len/2);
            $repeat = ( strlen($string) - $len*2 );
        }

        return substr($string, 0, $len) . ' ' . chunk_split( str_repeat($char, $repeat), 4, ' ') .substr($string, $len*-1);
    }

    /**
     * Шифрует ключ доступа платежей (для последующего сохранения в БД)
     *
     * @param string $token Не зашифрованные ключ
     */
    public function setAccessToken($token) {
        $this->data['access_token'] = JWS_Base64::urlEncode(Wallet::des()->encrypt($token));
    }

    /**
     * Вспомогательная функция, отменяет все методы оплаты которые включены
     * По идее включен всегда один метод оплаты, поэтому отключает только 1 метод по ИД пользователя
     *
     * @param integer $uid  ИД Пользователя
     * @return mixed
     */
    static public function clearActiveWallet($uid) {
        global $DB;
        $sql = "UPDATE bill_wallet SET active = false WHERE uid = ?i AND active = true";
        return $DB->query($sql, $uid);
    }

    /**
     * Активирует метод платежа по его типу и ИД пользователя
     *
     * @param integer $type     Тип метода платежа  @see WalletTypes::getAllTypes();
     * @param integer $uid      ИД Пользователя
     * @return mixed
     */
    static public function setActiveWallet($type, $uid) {
        global $DB;
        if(!WalletTypes::isValidType($type)) return false;

        Wallet::clearActiveWallet($uid);
        $sql = "UPDATE bill_wallet SET active = true WHERE type = ?i AND uid = ?i";
        return $DB->query($sql, $type, $uid);
    }

    /**
     * Авторизация в системе для последующих платежей
     *
     * @return mixed
     */
    public function authorize() {
        return $this->api->getAuthorizeUri();
    }
}

/**
 * Класс для инициализации определенного кошелька для оплаты
 *
 * при добавлении нового типа кошелька необходимо не забыть добавить этот тип в функцию getAllTypes();
 */
class WalletTypes
{

    /**
     * Тип оплаты Яндекс.Денеги
     */
    const WALLET_YANDEX   = 1;

    /**
     * Тип оплаты WebMOney
     */
    const WALLET_WEBMONEY = 2;

    /**
     * Тип оплаты банковской картой ДОЛ
     */
    const WALLET_DOL      = 3;

    /**
     * Тип оплаты банковской картой (Альфа-банк)
     */
    const WALLET_ALPHA    = 4;

    /**
     *
     * Инициализируем класс для работы с методом оплаты
     *
     * @param integer $uid  По умолчанию текущий пользователь
     * @param integer $type Если не задано берет активный метод оплаты и возвращает инициализированный объект
     *
     * @return bool|walletYandex|walletWebMoney
     */
    static function initWalletByType($uid = null, $type = null) {
        if($uid === null) {
            $uid = get_uid(false);
        }

        if($type === null) {
            $type = WalletTypes::getTypeWalletActive($uid);
        }

        switch($type) {
            case self::WALLET_YANDEX:
                require_once $_SERVER['DOCUMENT_ROOT']."/classes/wallet/walletYandex.php";
                $wallet = new walletYandex($uid);
                return $wallet;
                break;
            case self::WALLET_WEBMONEY:
                require_once $_SERVER['DOCUMENT_ROOT']."/classes/wallet/walletWebmoney.php";
                $wallet = new walletWebmoney($uid);
                return $wallet;
            case self::WALLET_ALPHA:
                require_once $_SERVER['DOCUMENT_ROOT']."/classes/wallet/walletAlpha.php";
                $wallet = new walletAlpha($uid);
                return $wallet;
            case self::WALLET_DOL:
            default:
                return false;
                break;
        }
    }

    /**
     * Проверяем есть ли активный метод оплаты
     *
     * @param integer $uid  По умолчанию текущий пользователь
     * @param integer $type Если не задано берет активный метод оплаты и возвращает инициализированный объект
     * @return bool
     */
    static function isWalletActive($uid = null, $type = null) {
        static $isWalletActive;
        if($isWalletActive !== null) {
            return $isWalletActive;
        }

        $wallet = self::initWalletByType($uid, $type);
        return ( $isWalletActive = self::checkWallet($wallet) );
    }

    /**
     * Проверяем
     *
     * @param $wallet
     * @return bool
     */
    static function checkWallet($wallet) {
        return !( $wallet == false || (is_object($wallet) && $wallet->getAccessToken() == false) );
    }

    /**
     * Список всех доступных созданных кошельков у пользователя
     *
     * @param null $uid
     * @return mixed
     */
    static function getListWallets($uid = null) {
        global $DB;

        if($uid === null) {
            $uid = get_uid(false);
        }

        $sql = "SELECT * FROm bill_wallet WHERE uid = ?i ORDER BY type";
        return $DB->rows($sql, $uid);
    }

    /**
     * Берем активированный тип оплаты по UID пользователя
     *
     * @param integer $uid     ИД Пользователя
     * @return mixed
     */
    static function getTypeWalletActive($uid = null) {
        global $DB;

        if($uid === null) {
            $uid = get_uid(false);
        }

        $sql = "SELECT type FROM bill_wallet WHERE uid = ? AND active = true";
        return $DB->val($sql, $uid);
    }

    /**
     * Проверка типа на валидность (существует ли в системе)
     *
     * @param $type
     * @return bool
     */
    static function isValidType($type) {
        $system_types = self::getAllTypes();
        return in_array($type, $system_types);
    }

    /**
     * Возвращаем все типы кошельков которые имеются в системе
     *
     * @return array
     */
    static function getAllTypes() {
        return array(
            self::WALLET_YANDEX,
            self::WALLET_WEBMONEY,
            //self::WALLET_DOL,
            self::WALLET_ALPHA
        );
    }

    /**
     * Возвращает название платежного метода
     *
     * @param $type
     * @return string
     */
    static public function getNameWallet($type, $n=0, $accountId = 0) {
        if( $n<0 && $n>3 ) return false;

        switch($type) {
            case self::WALLET_YANDEX:
                $name = array('Кошелек Яндекс.Деньги', 'Яндекс.Деньги', 'кошелек %WALLET% Яндекс.Деньги', 'вашего кошелька Яндекс.Денег');
                break;
            case self::WALLET_WEBMONEY;
                $name = array('Кошелек WebMoney', 'WebMoney', 'кошелек %WALLET% WebMoney', 'вашего кошелька WebMoney');
                break;
            case self::WALLET_ALPHA:
            case self::WALLET_DOL:
                $name = array('Банковская карта', 'VISA', 'пластиковую карту %WALLET%', 'вашей пластиковой карты');
                break;
            default:
                $name = array('Личный счет', 'Личный счет', 'счет %WALLET% на сайте', 'вашего счета №' . $accountId . ' на сайте');
                break;
        }

        return $name[$n];
    }
}


?>