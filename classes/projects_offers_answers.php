<?php

require_once $_SERVER['DOCUMENT_ROOT']."/classes/account.php";

define(TPL_ANSWERS_DIR, $_SERVER['DOCUMENT_ROOT']."/projects");

/**
 * Класс для работы с платными ответами на проекты
 *
 */
class projects_offers_answers {
    
    /**
     * Шаблон тестового сообщения о количетве оставшихся бесплатных ответов
     */
    const FREE_ANSWERS_TXT          = 'Осталось ответов на проекты: %s';
    const FREE_ANSWERS_NEWBIE_TXT   = 'Осталось ответов на проекты на сегодня: %s';
    
    /**
     * Текущее сообщение
     * 
     * @var type 
     */
    public $free_answers_txt;
    
    
    /**
     * Количество бесплатный ответов в ДЕНЬ для пользователей
     * с датой регистрации от 01.01.2015
     * См так же триггер aU projects_offers_answers
     */
    const FREE_ANSWERS_CNT_NEWBIE   = 3;
    const FREE_ANSWERS_NEWBIE_DATE  = '2015-01-01';

    
    




    /**
     * Количество бесплатный ответов в МЕСЯЦ для остальных пользователей
     * См так же триггер aU projects_offers_answers
     */
    const FREE_ANSWERS_CNT = 5;
    
    
    
    //@todo: применение по коду не найдено
    const FREE_ANSWERS_CNT_PRO = 5;
    
    
    /**
     * Количество бесплатных ответов за день (это же еще есть в триггере aU projects_offers_answers)
     *
     * @var integer
     */
    
    //@todo: применение по коду не найдено
    public $free_answers_on_day = 0;
    
    
    
    /**
     * Количество бесплатных ответов за день (это же еще есть в триггере aU projects_offers_answers)
     *
     * @var integer
     */
    
    //@todo: при приобритении платных ответов устанавливает значение бесплатных см AddPayAnswers, 
    //более применение по коду не найдено.
    public $free_answers_on_month = 5;
    
    /**
     * Номер операции в op_codes для оплаты через FM
     *
     * @var integer
     */
    public $fm_op_code = 61;
    
    /**
     * Стоимости пакетов
     *
     * @var array
     */
    public $op_codes = array (
        1  => 30,
        5  => 120,
        10 => 210
    );
    
    /**
     * Номер операции для оплаты "Выделить предложение цветом"
     *
     * @var unknown_type
     */
    public $color_op_code = 98;
    
    /**
     * Номер операции в op_codes для возврата отзыва при блокировке проекта
     *
     * @var integer
     */
    public $return_op_code = 102;
    
    /**
     * Стоимость услуги "Выделить предложение цветом"
     *
     * @var unknown_type
     */
    public $color_fm_sum = 57;
    const COLOR_FM_COST = 57;
    
    
    /**
     * Конструктор класса
     */
    function __construct() {
        if(is_pro()) {
            $this->free_answers_on_day = 56;
        } 
    }
    
    /**
     * Получить информацию о кол-во ответов конкретного пользователя. Создает переменный класса:
     * $this->free_offers - кол-во оставшихся бесплатных ответов сегодня
     * $this->pay_offers - кол-во оставшихся платных ответов
     * $this->offers - общее кол-во возможных ответов
     *
     * @param integer $uid - UID пользователя (0 - если нужно проверить того, кто авторизовался)
     * @param boolean $freelancer_only - если false, то работодателям возвращает 0.
     *
     * return mixed - сообщение об ошибке или 0 в случае успеха
     */    
    function GetInfo($uid=0, $freelancer_only=true) {
        if ($uid && ($_SESSION['uid'] != $uid)) {
            require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
            $user = new users;
            $user->GetUserByUID($uid);
            $uid = $user->uid;
            $role = $user->role;
            $is_pro = $user->is_pro == 't';
            $reg_date = $user->reg_date;
        } else {
            $uid = $_SESSION['uid'];
            $role = $_SESSION['role'];
            $is_pro = is_pro();
            $reg_date = $_SESSION['reg_date'];
        }
        if (!$uid || ($freelancer_only && is_emp($role))) {
            $this->free_offers = 0;
            $this->pay_offers = 0;
            $this->offers = 0;
            return 0;
        }
        
        $row = $GLOBALS['DB']->row("
            SELECT 
                *, 
                DATE(NOW()) AS ut, 
                (date_trunc('month', last_offer) = date_trunc('month', now())) as has_wo 
             FROM projects_offers_answers 
             WHERE uid = ?i", $uid);
        
        
        $is_newbie = $this->isFreeAnswersNewBie($reg_date);
        $free_answers_cnt = $is_newbie? self::FREE_ANSWERS_CNT_NEWBIE : self::FREE_ANSWERS_CNT;
        
        
        if ($row) {
            if ($row['last_offer'] == $row['ut'] || 
                $is_pro || 
                ($row['has_wo'] == 't' && !$is_newbie)) {
                
                $this->free_offers = $row['free_offers'];
            } else {
                $this->free_offers = $free_answers_cnt;//self::FREE_ANSWERS_CNT; 
            }
            $this->pay_offers = $row['pay_offers'];
        } else {
            $this->free_offers = $free_answers_cnt;//self::FREE_ANSWERS_CNT;
            $this->pay_offers = 0;
        }
        
        $this->offers = $this->free_offers + $this->pay_offers;

        $_tmpl = $is_newbie? self::FREE_ANSWERS_NEWBIE_TXT : self::FREE_ANSWERS_TXT;
        $this->free_answers_txt = sprintf($_tmpl, $this->offers);
        
        return $GLOBALS['DB']->error;
    }

    
    public function getFreeAnswersTxt()
    {
        return $this->free_answers_txt;
    }

    

    /**
     * Указанная дата регистрация попадает под новичков или нет
     * 
     * @param type $reg_date
     * @return type
     */
    public function isFreeAnswersNewBie($reg_date)
    {
        return (strtotime($reg_date) >= strtotime(self::FREE_ANSWERS_NEWBIE_DATE));
    }



    /**
     * Купить платные ответы с помощью FM
     *
     * @param integer $uid  - UID пользователя
     * @param integer $code - код покупки из таблицы projects_offers_op_codes
     *
     * @return mixed - сообщение об ошибке или 0 в случае успеха
     */    
    function BuyByFM($uid, $ammount, $tr_id = 0, $commit = 1) {
        $uid = intval($uid);
        //if ($ammount == 1) {
        //    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sms_services.php';
        //    $row = sms_services::checkTariff(3, 4446);
        //    $cost = $row['fm_sum'];
        //} else
        if (!empty($this->op_codes[$ammount])) {
            $cost = $this->op_codes[$ammount];
        } else {
            return 'Выбранного тарифа не существует';
        }
        $account = new account;
        $transaction_id = $account->start_transaction($uid, $tr_id);
        $error = $account->Buy($billing_id, $transaction_id, $this->fm_op_code, $uid, "Покупка ответов на проекты (кол-во: {$ammount})", "Покупка ответов на проекты (кол-во: {$ammount})", $cost, 0);
        if ($error) return $error;
        if ($this->AddPayAnswers($uid, $ammount)) {
            if($commit) $account->commit_transaction($transaction_id, $uid, $billing_id);
            return 0;
        } else {
            return 'Ошибка при записи счета!';
        }
    }
    
    /**
     * Возвращает список доступных пакетов покупки из таблицы projects_offers_op_codes
     *
     * @return array
     */    
    function GetOpCodes() {
        // Ранее цена одного ответа зависела от цены SMS за один ответ.
        //require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sms_services.php';
        //$row = sms_services::checkTariff(3, 4446);
        //$this->op_codes[1] = $row['fm_sum'];
        //ksort($this->op_codes);
        return $this->op_codes;
    }
    
    /**
     * Добавить $ammount платных ответов
     *
     * @param integer $uid - UID пользователя
     * @param integer $ammount - кол-во ответов
     *
     * return boolean
     */   
    function AddPayAnswers($uid, $ammount=1) {
        $ammount = intval($ammount);
        $answers = $GLOBALS['DB']->val("SELECT COUNT(*) FROM projects_offers_answers WHERE uid = ?", $uid);

        if(is_pro()) {
            $this->free_answers_on_day = 56;   // Для новых про 56 ответов в день
        }
        
        if ($answers) {
            $GLOBALS['DB']->query("UPDATE projects_offers_answers SET pay_offers = pay_offers + ?i WHERE uid = ?", $ammount, $uid);
        } else {
            $GLOBALS['DB']->insert("projects_offers_answers", array(
				'uid'         => $uid,
				'pay_offers'  => $ammount,
				'free_offers' => $this->free_answers_on_month
			));
        }
        if ($GLOBALS['DB']->error) {
			return FALSE;
		} else {
			return TRUE;
		}
    }
    
    /**
     * Удалить $ammount платных ответов
     *
     * @param integer $uid - UID пользователя
     * @param integer $ammount - кол-во ответов
     *
     * return boolean
     */   
    function DelPayAnswers($uid, $ammount=1) {
        $GLOBALS['DB']->query("UPDATE projects_offers_answers SET pay_offers = pay_offers - ?i WHERE uid = ? AND pay_offers >= ?i", $ammount, $uid, $ammount);
		if ($GLOBALS['DB']->error) {
			return FALSE;
		} else {
			return TRUE;
		}
    }


    /**
     * Отображает информацию о количестве купленных проектов
     *
     * @param integer $ammount - кол-во купленных ответов
     *
     * return string
     */   
    function ShowDoneInfo($ammount)
    {
        $ammount = intval($ammount);

        if ($ammount >= 11 && $ammount <= 19 )
        {
            $ammount_str = "Покупка прошла успешно. На ваш счёт зачислено $ammount ответов на проекты.";
        }
        else
        {
            $ammount_tmp = $ammount % 10;

            if ($ammount_tmp == 1)
            {
                $ammount_str = "Покупка прошла успешно. На ваш счёт зачислен $ammount ответ на проект.";
            }
            elseif ($ammount_tmp >= 2 && $ammount_tmp <= 4)
            {
                $ammount_str = "Покупка прошла успешно. На ваш счёт зачислено $ammount ответа на проекты.";
            }
            else
            {
                $ammount_str = "Покупка прошла успешно. На ваш счёт зачислено $ammount ответов на проекты.";
            }
        }
    
        return $ammount_str;
    }

    /**
     * Удаление заказа по id в account_operations
     * @see account::DelByOpid()
     *
     * @param  intr $uid uid пользователя
     * @param  int $opid id операции в биллинге
     * @return int 0
     */
   function DelByOpid($uid, $opid) {
       global $DB;
       
       $sql = "SELECT pay_offers FROM projects_offers_answers WHERE uid = ?";
       $pay_offers = $DB->val($sql, $uid);
       
       $sql = "SELECT descr FROM account_operations WHERE id = ? AND billing_id=(SELECT id FROM account WHERE uid = ?)";
       $val = $DB->val($sql, $opid, $uid);
       
       preg_match("/.*?(\d+)/mix", $val, $find);
       $ammount = (int)$find[1];
       if($ammount > 0) {
           if($ammount > $pay_offers) {
               $ammount = $pay_offers;
           }
           $this->DelPayAnswers($uid, $ammount);
       }
       return 0;
   }

    /**
     * Возврат ответов в случае блокировки проекта
     *
     * @param integer $project_id  - ID проекта
     *
     * @return mixed - сообщение об ошибке или 0 в случае успеха
     */    
    function ReturnAnswers($project_id) {
        global $DB;     

        $descr = "Возврат ответа на проект в связи с блокировкой проекта";
        $op_code = $this->return_op_code;
        
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/projects_offers.php';
        $of = new projects_offers();
        $offers = $of->GetPrjOffers($count, $project_id, 'ALL', 0, 0, true);
        
        if (!$count) {
            return;
        }

        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/account.php';
        $account = new account;
        
        foreach ($offers as $offer) {
            $uid = $offer['user_id'];
            if ($offer['type'] == 0) {
                continue;
            }
            
            $transaction_id = $account->start_transaction($uid);
            
            $error = $account->Buy($billing_id, $transaction_id, $op_code, $uid, $descr, $descr, 0, 0);
            if ($error) return $error;
            
            if ($offer['type'] == 2) {
                $DB->query("UPDATE projects_offers_answers SET pay_offers = pay_offers + 1 WHERE uid = ?", $uid);
            } else {
                $free_cnt = self::FREE_ANSWERS_CNT;
                $DB->query("UPDATE projects_offers_answers 
                    SET free_offers = free_offers + (CASE WHEN free_offers < {$free_cnt} THEN 1 ELSE 0 END) WHERE uid = ?", $uid);
            }
            
            $account->commit_transaction($transaction_id, $uid, $billing_id);
        }
        
    }
  
}

