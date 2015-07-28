<?php

class BillPaybackException extends Exception
{
    const INSERT_FAIL_MSG       = 'Не удается добавить запрос за возврат средств.';
    const ALREADY_PAYBACK_MSG   = 'Средства запроса уже были возвращены.';
    const PAYBACK_INPROGRESS    = 'Запрос в процессе возврата средств.';
    const PAYBACK_NOTFOUND      = 'Запрос на возврат средств не найден.';
    const UNDEFINED_STATUS      = 'На запрос возврата средств был получен не известный статус.';
    const REQUEST_LIMIT         = 'Превышен лимит в более 999 запросов.';
    const API_CRITICAL_FAIL     = 'Невозможно повторить запрос. Код ошибки API: %d.';
    
    protected $repeat = false;

    public function __construct() 
    {
        $args = func_get_args();
        $cnt = count($args);
        
        if($cnt > 0)
        {
            $message = current($args);
            if($cnt > 1) 
            {
                $this->repeat = (end($args) === true);
                unset($args[$cnt-1],$args[0]);
                $message = (count($args))?vsprintf($message, $args):$message;
            }
            
            parent::__construct($message);
        }
    }
    
    
    public function isRepeat()
    {
        return $this->repeat;
    }
    
}