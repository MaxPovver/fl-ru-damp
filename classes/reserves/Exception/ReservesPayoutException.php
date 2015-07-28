<?php

require_once(__DIR__ . '/ReservesPayException.php');


class ReservesPayoutException extends ReservesPayException
{
    //Технические сообщения об ошибках локально в системе
    const RQST_EMPTY        = 'Пустой список задач на выплату';
    const WRONG_SUM         = 'Некорректная сумма выплаты';
    const REQV_FAIL         = 'Способ выплаты "%s" не доступен для пользователя uid = %s';
    const INS_FAIL          = 'Не удалось добавить задачу на выплату';
    const CARD_SYNONIM_FAIL = 'Не удалось получить синоним номера карты';
    const LAST_PAYED_FAIL   = 'Payout Id = %s сервис вернул ошибку %s%s';
    const REQV_INVALID      = 'Реквизиты отсутствуют или повреждены';
    const RQST_ACTIVE       = 'Уже есть задачи на выплату другим способом';
    const TYPE_INVALID      = 'Неподдерживаемый способ выплаты: "%s"';
    const PHONE_FAIL        = 'Неудалось найти номер телефона';
    
    public function __construct() 
    {
        $args = func_get_args();
        $cnt = count($args);
        
        if ($cnt > 0) {
            $message = current($args);
            if ($cnt > 1) {
                unset($args[0]);
                $message = (count($args))?vsprintf($message, $args):$message;
            }
            
            parent::__construct($message);
        }
    }
}