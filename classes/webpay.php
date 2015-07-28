<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/onlinedengi_cards.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/account.php';
/**
 * Класс для пополнения счета через вебкошелек ПСКБ
 * @see http://dengionline.com/
 * 
 */
class webpay {

    /**
     * Код системы оплат
     */
    const PAYMENT_SYS_CODE = 13;
    
    /**
     * Код ошибки. Недостаточно данных.
     */
    const ERR_DATA   = 1;
    /**
     * Код ошибки. Не подходит секретный ключ.
     */
    const ERR_SECRET = 2;
    /**
     * Код ошибки. Нулевая или отрицательная сумма пополнения.
     */
    const ERR_AMOUNT = 3;
    /**
     * Код ошибки. Операция осуществлялась ранее.
     */
    const ERR_RETRY  = 4;
    /**
     * Код ошибки. Пользователя не существует
     */
    const ERR_USER  = 5;
    /**
     * Код ошибки. Ошибка при выполнениее account::deposit()
     */
    const ERR_DEPOSIT = 6;
    
    /**
     * Данные для ведения таблицы с логами (webpay_log)
     * 
     * @var array
     */
    protected $_fields = array();
    
    
    /**
     * Основная функция для пополнения. В нее нужно передать POST данны, которые пришли от веб-кошелька
     * 
     * @param  array    массив с данным от webpay
     * @return успех
     */
    public function income($data) {
        global $DB;
        $this->_fields = array();
        $id = $DB->insert('webpay_log', array('request' => serialize($data)), 'id');
        if ( 
            empty($data['amount']) || empty($data['userid']) || empty($data['userid_extra'])
            || empty($data['paymentid']) || empty($data['key']) || empty($data['paymode'])
        ) {
            $this->_error($id, self::ERR_DATA);
            return false;
        }
        $amount = floatval($data['amount']);
        $login  = (string) $data['userid_extra'];
        $this->_fields['payment_id'] = $paymentid = (string) $data['paymentid'];
        if ( $amount <= 0 ) {
            $this->_error($id, self::ERR_AMOUNT);
            return false;
        }
        $this->_fields['amount'] = $amount;
        if ( $data['key'] != md5($data['amount'] . $data['userid'] .$data['paymentid'] . onlinedengi_cards::SECRET) ) {
            $this->_error($id, self::ERR_SECRET);
            return false;
        }
        $user = new users;
        $user->GetUser($login);
        if ( empty($user->uid) ) {
            $this->_error($id, self::ERR_USER);
            return false;
        }
        $this->_fields['user_id'] = $user->uid;
        if ( $DB->val("SELECT COUNT(*) FROM webpay_log WHERE payment_id = ?", $paymentid) ) {
            $this->_success($id, true);
        } else {
            $account = new account;
            $account->GetInfo($user->uid);
            $comment = "Пополнение через Веб-кошелек";
            if ( $account->deposit($op_id, $account->id, $amount, $comment, self::PAYMENT_SYS_CODE, $amount) ) {
                $this->_error($id, self::ERR_DEPOSIT);
                return false;
            }
            $this->_fields['billing_id'] = $op_id;
            $this->_success($id);
        }
        return true;
    }
    
    
    /**
     * Возвращает в вебкошелек ошибку и пишет о ней в базу с в логами
     * 
     * @param integer $id    id записи в логах
     * @param integer $errno номер ошибки
     */
    protected function _error($id, $errno) {
        global $DB;
        $this->_fields['result'] = $errno;
        $DB->update('webpay_log', $this->_fields, "id = {$id}");
        switch ( $errno ) {
            case self::ERR_USER: {
                $comment = 'Не указан пользователь';
                break;
            }
            default: {
                $comment = 'Ошибка при пополнении счета';
            }
        }
        $xml  = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n";
        $xml .= "<result>\r\n<id>{$id}</id>\r\n<code>NO</code>\r\n<comment>{$comment}</comment>\r\n</result>";
        echo iconv('CP1251', 'UTF-8', $xml);
    }
    
    
    /**
     * Возвращает в вебкошелек успех пополенения
     * 
     * @param type $id     id записи в логах
     * @param type $retry  были ли такая операция ранее (@see http://dengionline.com/dev/protocol/notification)
     */
    protected function _success($id, $retry=false) {
        global $DB;
        $this->_fields['result'] = $retry? self::ERR_RETRY: 0;
        $DB->update('webpay_log', $this->_fields, "id = {$id}");
        $xml  = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\r\n";
        $xml .= "<result>\r\n<id>{$id}</id>\r\n<code>YES</code>\r\n<comment>Ваш счет пополнен</comment>\r\n</result>";
        echo iconv('CP1251', 'UTF-8', $xml);
    }
    
    
}

