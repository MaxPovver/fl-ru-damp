<?php


class DocGenException extends Exception
{
    const DEFAULT_ERROR_MSG     = 'Ошибка при формировании документа.';
    const DEFAULT_ERROR_PREFFIX = 'Ошибка при формировании %s.';
    
    public function __construct($message = null, $self = false)
    {
        if(!$self)
        {
            $message = ($message)?
                sprintf(self::DEFAULT_ERROR_PREFFIX, $message):
                self::DEFAULT_ERROR_MSG;
        }
        
        parent::__construct($message);
    }

}