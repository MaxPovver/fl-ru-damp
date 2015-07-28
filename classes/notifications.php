<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/stdf.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/messages.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/sbr.php';
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_offers.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_offers_dialogue.php");
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/models/TServiceMsgModel.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/models/TServiceOrderModel.php');

/**
 * класс для генерации высплывающих сообщений для юзербара 
 */
class notifications
{
    
    
    
    
    /**
     * Получение подсказки из нескольких источников
     * для отображения в обьедененной кнопке "Проекты и заказы"
     * Обработка в поряке очереди.
     * 
     * @return array
     */
    static public function getFrlGroupTip()
    {
        //по умолчанию ссылаемся на список проектов или заказов
        $default = array(
            'count' => 0,
            'tip' => 'Список проектов и заказов',
            'link' => (@$_SESSION['po_count'])?'/proj/?p=list':'/tu-orders/'
        );
        
        //есть
        $projectsTip = self::getProjectsTipFrl();
        //@todo: нужно доработать метод выше
        if(isset($projectsTip['count']) && $projectsTip['count'] > 0)
        {
            $default = $projectsTip;
        }
        
        //есть ли события по заказам ТУ
        $tservicesOrdersTip = self::getTServicesOrdersTip();
        if(isset($tservicesOrdersTip['count']) && $tservicesOrdersTip['count'] > 0)
        {
            $default['tip'] = ($default['count'] > 0)?$default['tip'] . PHP_EOL . $tservicesOrdersTip['tip']:$tservicesOrdersTip['tip'];
            $default['count'] += $tservicesOrdersTip['count'];
            $default['link'] = $tservicesOrdersTip['link'];
        }
        
        return $default;
    }

    
    static public function getEmpGroupTip()
    {
        //по умолчанию ссылаемся на список проектов
        $default = array(
            'count' => 0,
            'tip' => 'Список проектов и заказов',
            'link' => "/users/{$_SESSION['login']}/setup/projects/"
        );
        
        //есть
        $projectsTip = self::getProjectsTipEmp();
        //@todo: нужно доработать метод выше
        if(isset($projectsTip['count']) && $projectsTip['count'] > 0)
        {
            $default = $projectsTip;
        }
        
        //есть ли события по заказам ТУ
        $tservicesOrdersTip = self::getTServicesOrdersTip();
        if(isset($tservicesOrdersTip['count']) && $tservicesOrdersTip['count'] > 0)
        {
            $default['tip'] = ($default['count'] > 0)?$tservicesOrdersTip['tip'].PHP_EOL.$default['tip']:$tservicesOrdersTip['tip'];
            $default['count'] += $tservicesOrdersTip['count'];
            $default['link'] = $tservicesOrdersTip['link'];
        }
        
        return $default;
    }    
    
    
    
    
    
    
    

    /**
     * События в заказе ТУ
     * 
     * @return array
     */
    static public function getTServicesOrdersTip()
    {
        $uid = get_uid(FALSE); 
        $is_emp = is_emp();
        
        $tips = array(
            "Новое сообщение в заказе",
            "В заказах %d %s",
            "Новое событие в заказе",
            "В заказах %d %s и %d %s"
        );
        
        $msg_ending = array("новое сообщение", "новых сообщения", "новых сообщений");
        $event_ending = array("новое событие", "новых события", "новых событий");
        
        $tip = 'Мои заказы';
        $link = '';
        
        //@todo: здесь используются каунты без кеша 
        //по мере заполнения БД они будут тормозить. Нужно переделать!
        $tserviceMsgModel = TServiceMsgModel::model();
        $newTserviceMsgCount = $tserviceMsgModel->countNew($uid);
        
        
        $tserviceOrderModel = TServiceOrderModel::model();
        $newTserviceOrderEventCount = $tserviceOrderModel->getCountEvents($uid, $is_emp);
        
        $total = $newTserviceMsgCount + $newTserviceOrderEventCount;
        
        $code = ($newTserviceMsgCount > 0)?1:0;
        $code .= ($newTserviceOrderEventCount > 0)?1:0;
        
        switch($code)
        {
            case '10':
                $tip = ($newTserviceMsgCount == 1)?sprintf($tips[0]):
                sprintf($tips[1], $newTserviceMsgCount, ending($newTserviceMsgCount, $msg_ending[0], $msg_ending[1], $msg_ending[2])); 
                break;
            
            case '01':
                $tip = ($newTserviceOrderEventCount == 1)?sprintf($tips[2]):
                sprintf($tips[1], $newTserviceOrderEventCount, ending($newTserviceOrderEventCount, $event_ending[0], $event_ending[1], $event_ending[2]));
                $link = $tserviceOrderModel->getLastEventOrderURL($uid, $is_emp);
                break;
            
            case '11':
                $tip = sprintf($tips[3], 
                        $newTserviceMsgCount, ending($newTserviceMsgCount, $msg_ending[0], $msg_ending[1], $msg_ending[2]), 
                        $newTserviceOrderEventCount, ending($newTserviceOrderEventCount, $event_ending[0], $event_ending[1], $event_ending[2]));
                break;
        }

        return array(
            'count' => $total,
            'tip' => $tip,
            'link' => (!empty($link))?$link:($is_emp ? "/users/" . $_SESSION['login'] : '') . "/tu-orders/"
        );
    }
    
    

    
    /**
     * возвращает подсказку для кнопки "Проекты" для работодателя
     * в виде массива ((int)count, (string)tip)
     * null - в случае ошибки
     */
    static public function getProjectsTipEmp ()
    {
        $uid = get_uid(0);
        if (!$uid) {
            return null;
        }
        $newMessCount = $newOffersCount = $newPrjEvents = 0;

        // количество ответов и сообщений в проектах
        $complexCount = projects_offers_dialogue::CountMessagesForEmp($uid, true, true);
        
        $newOffersCount = (int)$complexCount['offers'];
        $newMessCount = (int)$complexCount['messages'];
        
        // новые события
        $newPrjEvents = 0; //projects_offers::CountNewPrjEventsForEmp($_SESSION['uid']); #0020922

        if (($newOffersCount + $newMessCount) == 1) {
            $last_emp_new_messages_pid = projects_offers_dialogue::FindLastMessageProjectForEmp($uid);
            $lastPrjLink = "/projects/" . $last_emp_new_messages_pid;
        } else {
            $lastPrjLink = "/users/" . $_SESSION['login'] . "/projects/";
        }
        $_SESSION['lst_emp_new_messages']['cnt'] = $complexCount['all'];
        
        if ($newMessCount === null || $newPrjEvents === null) {
            return array(
                'count'     => 0,
                'tip'       => 'Список проектов',
                'link'  => "/users/" . $_SESSION['login'] . "/projects/"
            );
        }
        
        //$news = $newMessCount + $newPrjEvents;
        
        /*if ((int)$newMessCount === 0 && (int)$newPrjEvents === 1) {
            $tip = 'Новое событие в вашем проекте';
        } elseif ((int)$newMessCount === 1 && (int)$newPrjEvents === 0) {
            $tip = "Новый ответ на ваш проект";
        } else*/if (($newOffersCount + $newMessCount + $newPrjEvents) > 0) {
            $tip = "В ваших проектах ";
            $tip .= $newOffersCount > 0 ? $newOffersCount . ending($newOffersCount, " новый ответ", " новых ответа", " новых ответов") : "";
            $tip .= ($newOffersCount > 0 && $newMessCount > 0) ? " и " : "";
            $tip .= $newMessCount > 0 ? $newMessCount . ending($newMessCount, " новое сообщение", " новых сообщения", " новых сообщений") : "";
            
            $tip .= $newPrjEvents > 0 ? $newPrjEvents . ending($newPrjEvents, " новое событие", " новых события", " новых событий") : "";
        }
        return array(
            'count'     => $newOffersCount + $newMessCount + $newPrjEvents,
            'tip'       => $tip,
            'link'      => $lastPrjLink
        );
    }
    
    /**
     * возвращает подсказку для кнопки "Проекты" для фрилансера
     * в виде массива ((int)count, (string)tip)
     * null - в случае ошибки
     */
    static public function getProjectsTipFrl ()
    {
        $uid = get_uid(0);
        if (!$uid) {
            return null;
        }
        $newEventsCount = $newMessCount = 0;
        // количество новых событий
        $newEventsCount = projects_offers::GetNewFrlEventsCount($uid, false);
        // сколько новых сообщений
        $newMessCount = projects_offers_dialogue::CountMessagesForFrl($uid, true, false);
        
        if ($newEventsCount === null || $newMessCount === null) {
            return array(
                'count' => 0,
                'tip'   => 'Список проектов',
                'link' => '/proj/?p=list'
            );
        }
        
        
        
        $newAnsCount = $newEventsCount + $newMessCount;
        
        if ((int)$newMessCount === 0 && (int)$newEventsCount === 1) {
            $tip = 'Новое событие к вашему ответу в проекте';
        } elseif ((int)$newMessCount === 1 && (int)$newEventsCount === 0) {
            $tip = "Новое сообщение к вашему ответу в проекте";
        } elseif (($newMessCount + $newEventsCount) > 0) {
            $tip = "";
            $tip .= $newMessCount > 0 ? $newMessCount . ending($newMessCount, " новое сообщение", " новых сообщения", " новых сообщений") : "";
            $tip .= ($newMessCount > 0 && $newEventsCount > 0) ? " и " : "";
            $tip .= $newEventsCount > 0 ? $newEventsCount . ending($newEventsCount, " новое событие", " новых события", " новых событий") : "";
            $tip .= $newEventsCount > 0 ? " к вашим ответам в проектах" : " на ваши ответы в проектах";
        }
        
        return array(
            'count' => $newAnsCount,
            'tip'   => $tip,
            'link' => '/proj/?p=list'
        );
    }
    
    
    /**
     * возвращает подсказку для кнопки "Сообщения"
     * в виде массива ((int)count, (string)tip)
     * null - в случае ошибки
     * 
     * @param boolean $ajax вызов функции ajax'ом
     */
    static public function getMessTip ($ajax = false)
    {
        $mem = new memBuff();
        
        $uid = get_uid(0);
        if (!$uid) {
            return null;
        }
        
        if ($ajax) {
            $newMessCount = messages::GetNewMsgCount($uid, true);
        } else {
            $newMessCount = $_SESSION['newmsgs'];
        }
        if ($newMessCount === null) {
            return null;
        } elseif ((int)$newMessCount === 0) {
            $tip = 'Мои сообщения и переписка';
        } elseif ((int)$newMessCount === 1) {
            /*$mess = new messages;
            if ( empty($_SESSION['newMsgSender']) ) {
                $user = $mess->GetLastMessageContact($uid);
                $_SESSION['newMsgSender'] = $user['uname'] . ' ' . $user['usurname'] . ' [' . $user['login'] . ']';
            }
            $tip = 'Новое сообщение от пользователя ' . $_SESSION['newMsgSender'];*/
            $newMsgSender = $mem->get("msgsNewSender{$uid}");
            if ($newMsgSender === false || trim($newMsgSender) == '[]') {
                $mess = new messages;
                $sender = $mess->GetLastMessageContact($uid);
                if(trim($sender['login']) != '') {
                    $newMsgSender = $sender['uname'] . ' ' . $sender['usurname'] . ' [' . $sender['login'] . ']';
                    $mem->set("msgsNewSender{$uid}", $newMsgSender, 3600, 'msgsNewSenderID' . $sender['uid']);
                }
            }
            $tip = 'Новое сообщение от пользователя ' . $newMsgSender;
        } else {
            $tip = $newMessCount . ' ' . ending($newMessCount, 'непрочитанное сообщение', 'непрочитанных сообщения', 'непрочитанных сообщений');
        }
        
        return array(
            'count' => $newMessCount,
            'tip'   => $tip
        );
    }
    
    /**
     * возвращает подсказку для кнопки "Сообщения"
     * в виде массива ((int)count, (string)tip)
     * null - в случае ошибки
     * 
     * @param  string  $interface События какого интерфейса брать (старой СБР. или новой СБР) @todo Убрать когда закончатся старые СБР
     * @param boolean $ajax вызов функции ajax'ом
     */
    static public function getSbrTip ($interface = 'new')
    {
        $uid = get_uid(0);
        if (!$uid) {
            return null;
        }
        $name_session = $interface == 'old' ? 'sbr_tip_old' : 'sbr_tip';
        $eventCount = sbr_meta::getNewEventCount($uid, true, $interface);
        //$messCount = sbr_meta::getNewMsgCount($uid, true);
        if ($eventCount === null) {
            if(isset($_SESSION[$name_session])) {
                $tip = $_SESSION[$name_session];
                unset($_SESSION[$name_session]);
                return $tip;
            }
            return null;
        }
        
        $totalCount = $eventCount; // + $messCount;
        if ((int)$totalCount === 0) {
            if(isset($_SESSION[$name_session])) { // Для того чтобы моргало хотя бы один раз если пользователь находится сразу на странице СБР и обновляет страницу
                $tip = $_SESSION[$name_session];
                unset($_SESSION[$name_session]);
                return $tip;
            }
            $tip = 'Список Безопасных сделок';
            $alert = false;
        } elseif ((int)$totalCount === 1) {
            $tip = 'Новое событие в «Безопасной Сделке»';
            $alert = true;
        } else {
            $tip = $totalCount . ' ' . ending($totalCount, 'новое событие', 'новых события', 'новых событий') . ' в ваших «Безопасных Сделках»';
            $alert = false;
        }
        
        return array(
            'count' => $totalCount,
            'tip'   => $tip,
            'alert' => $alert
        );
    }
    
    
    
    /**
     * Обьединяем события по старой и новой безопасных сделок
     * 
     * @return string
     */
    static public function getAllSbrTip()
    {
        $default = array(
            'count' => 0,
            'tip'   => 'Список Безопасных сделок',
            'alert' => FALSE
        );
        
        $sbrTip = notifications::getSbrTip();
        if(isset($sbrTip['count']) && $sbrTip['count'] > 0)
            $default['count'] = $sbrTip['count'];
        
        
        $sbrOldTip = notifications::getSbrTip('old');
        if(isset($sbrOldTip['count']) && $sbrOldTip['count'] > 0)
            $default['count'] += $sbrOldTip['count'];
        
        if ((int)$default['count'] === 1) 
        {
            $default['tip'] = 'Новое событие в «Безопасной Сделке»';
            $default['alert'] = TRUE;
        } 
        elseif($default['count'] > 1) 
        {
            $default['tip'] = $default['count'] . ' ' . ending($default['count'], 'новое событие', 'новых события', 'новых событий') . ' в ваших «Безопасных Сделках»';
        }
        
        return $default;
    }
    
    
    
}

?>