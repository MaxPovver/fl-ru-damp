<?php

require_once $_SERVER['DOCUMENT_ROOT']."/classes/sms_gate_a1.php";
require_once $_SERVER['DOCUMENT_ROOT']."/classes/projects_status.php";
require_once $_SERVER['DOCUMENT_ROOT']."/classes/sbr.php";

/**
 * СМС уведомления по проектам
 */
class ProjectsSms extends sms_gate_a1
{
    
    /**
     * Сообщения состояния проекта
     * Если массив то ключи указывают на специфические сообщения для определенного типа проекта
     * 0 - обычный проект, 9 - персональный проект
     * 
     * @var type 
     */
    public $txt_project_status = array(
        projects_status::STATUS_NEW => array(
            0 => 'Заказчик выбрал вас исполнителем проекта на FL.ru. Пожалуйста, подтвердите проект %s/projects/%d/ или откажитесь от него.',
            9 => 'Вам предложен персональный проект на FL.ru. Пожалуйста, подтвердите проект %s/projects/%d/ или откажитесь от него.'
        ),
        projects_status::STATUS_ACCEPT => array( 
            0 => 'Выбранный вами исполнитель подтвердил проект %s/projects/%d/ и начал его выполнение. Не забудьте завершить проект по окончании сотрудничества.',
            9 => 'Исполнитель подтвердил участие в предложенном ему проекте %s/projects/%d/ и начал его выполнение. Не забудьте завершить проект по окончании сотрудничества.'
        ),
        projects_status::STATUS_EMPCLOSE => 'Заказчик завершил сотрудничество с вами по проекту %s/projects/%d/. Не забудьте про отзыв.',
        projects_status::STATUS_FRLCLOSE => 'Исполнитель завершил сотрудничество с вами по проекту %s/projects/%d/. Не забудьте про отзыв.',
        
        projects_status::STATUS_DECLINE => 'К сожалению, исполнитель отказался от выполнения вашего проекта %s/projects/%d/.',
        projects_status::STATUS_CANCEL => 'К сожалению, заказчик отменил свой проект %s/projects/%d/.',
        
        //При получении положительного отзыва по проекту (если фрилансер с ПРО) или отрицательного отзыва (и с ПРО, и без ПРО)
        100 => 'Заказчик оставил вам отзыв по проекту %s/projects/%d/. Не забудьте оставить ответный отзыв.',
        102 => 'Заказчик оставил вам отзыв по проекту %s/projects/%d/.',
        //При получении положительного отзыва по проекту (если фрилансер без ПРО)
        101 => 'Заказчик оставил вам скрытый отзыв по проекту %s/projects/%d/. Вы можете купить PRO https://www.fl.ru/payed/ и сделать отзыв видимым всем.'
    );


    /**
     * Телефончик то есть?
     * 
     * @return type
     */
    public function isPhone()
    {
        return !empty($this->_msisdn);
    }

    

    /**
     * Отправить СМС по состоянию проекта
     * 
     * @param int $status - статус проекта
     * @param int $id - ID проекта
     * @return boolean
     */
    public function sendStatus($status, $id, $kind = 0)
    {
        if(!isset($this->txt_project_status[$status]) || !$this->isPhone()) return FALSE;
        $kind = ($kind == 9)?$kind:0;//пока другие типы не используются
        $txt = is_array($this->txt_project_status[$status])?$this->txt_project_status[$status][$kind]:$this->txt_project_status[$status];
        $message = sprintf($txt, $GLOBALS['host'], $id);
        
        return $this->sendSMS($message);
    }

    



    /**
     * Создаем сами себя
     * @return projects_sms
     */
    public static function model($uid) 
    {
        $phone = '';
        $reqv = sbr_meta::getUserReqvs($uid);
        
        if($reqv)
        {
            $ureqv = $reqv[$reqv['form_type']];
            $phone = $ureqv['mob_phone'];
        }

        $class = get_called_class();
        return new $class($phone);
    }
}