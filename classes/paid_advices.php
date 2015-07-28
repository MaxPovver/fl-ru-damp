<?php

/**
 * 
 * Класс для работы с платными рекомендациями
 * http://beta.free-lance.ru/mantis/view.php?id=14420
 * 
 */
class paid_advices {
    
    /**
     * Заявка отправлена на утверждение пользователю
     */
    const STATUS_SENT           = 1;
    
    /**
     * Заявка отклонена
     */
    const STATUS_DECLINED       = 2;
    
    /**
     * Заявка удалена
     */
    const STATUS_DELETED        = 3;
    
    /**
     * Заявка принята
     */
    const STATUS_ACCEPTED       = 4;
    
    /**
     * Оплачена
     */
    const STATUS_PAYED          = 5;
    
    /**
     * Заявка заблокирована (В том случае если сам отзыв содержит мат и не может пройти модерацию)
     */
    const STATUS_BLOCKED        = 6;
    
    /**
     * Отправлена на модерацию
     */
    const MOD_STATUS_PENDING    = 1;
    
    /**
     * Отклонена модератором
     */
    const MOD_STATUS_DECLINED   = 2;
    
    /**
     * Разрешена модератором
     */
    const MOD_STATUS_ACCEPTED   = 3;
    
    /**
     * Отозвана пользователем для редактирования
     */
    const MOD_STATUS_USER_DECLINED = 4;
    
    /**
     * Комиссия www.free-lance.ru
     *
     */
    const PAID_COMMISION = 0.1;
    
    /**
	 * Максимально допустимый размер вложенных файлов 
     * (30 МБ общий объем файлов, то есть может быть один = 29МБ, остальные два по 4 кб)
	 *
	 */
    const MAX_FILE_SIZE = 31457280;
    /**
     * Код операции
     */
    const OP_CODE = 107; 
    /**
     * @var DB 
     */
    private $_db;
    
    public $added_sql;
    
    /**
     * Количество предложений на 1 странице в админке
     *
     */
    const COUNT_PAGE = 20;
    
    /**
     * Максимальное количество символов в отзыве
     * 
     */
    const MAX_DESCR_ADVICE = 1000;
    
    public function __construct($is_admin = false) {
        if(!$is_admin) $is_admin = hasPermissions('users');
        $is_admin = false;
        $this->added_sql = (!$is_admin ? "AND user_to = {$_SESSION['uid']}" : "");
        $this->_db = new DB('master');
    }
    
    public function add($to_user, $msgtext, $user_from = false, $create_date = false, $converted_id=null) {
        /**
         * @deprecated #0019740 
         */
        return;
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
        
        if(!$user_from) $user_from = get_uid(0);
        $_user = new users();
        $_user->GetUserByUID($to_user);
        $user = new users();
        $user->GetUserByUID($user_from);
        
        if (!$user_from || $to_user == $user_from || is_emp($_user->role) == is_emp($user->role)) {
            return FALSE;
        }
        $msgtext = change_q_x($msgtext, true);
        $res = $this->_db->insert('paid_advices', array(
            'create_date' => $create_date ? $create_date : "NOW()",
            'user_from'   => $user_from,
            'user_to'     => $to_user,
            'msgtext'     => $msgtext,
            'status'      => $converted_id ? self::STATUS_ACCEPTED : self::STATUS_SENT,
            'converted_id'=> $converted_id
        ), 'id');
        
        
        return $res;
    }
    
    public function getAdvice($id_advice, $uid = false) {
        if($uid) {
            $uid     = intval($uid);
            $uid_sql = "AND user_to = {$uid}";
        }
        $sql = "SELECT pa.*, e.login, e.role, e.photo, e.uname, e.usurname, e.is_pro, e.is_team, e.is_pro_test,
                file_docs_contract.original_name as fname_docs_contract, file_docs_contract.path as path_docs_contract, file_docs_contract.fname as name_docs_contract, 
                file_docs_contract.size as size_contract,   file_docs_tz.size as size_tz,  file_docs_result.size as size_result,
                file_docs_tz.original_name as fname_docs_tz, file_docs_tz.path as path_docs_tz, file_docs_tz.fname as name_docs_tz,
                file_docs_result.original_name as fname_docs_result, file_docs_result.path as path_docs_result, file_docs_result.fname as name_docs_result
                FROM paid_advices pa
                INNER JOIN users e ON e.uid = pa.user_from AND e.is_banned = B'0'
                LEFT JOIN file_advices as file_docs_contract ON file_docs_contract.id = pa.docs_contract
                LEFT JOIN file_advices as file_docs_tz ON file_docs_tz.id = pa.docs_tz
                LEFT JOIN file_advices as file_docs_result ON file_docs_result.id = pa.docs_result_file
                WHERE pa.id= ?i {$uid_sql} {$this->added_sql} AND (pa.status <> ?i AND pa.status <> ?i)
                ORDER BY pa.create_date DESC";
        $res = $this->_db->row($sql, $id_advice, self::STATUS_DELETED, self::STATUS_DECLINED);
        
        return $res;    
    }
    
    public function isConvertExist($convert_id) {
        $val = $this->_db->val("SELECT converted_id FROM paid_advices WHERE converted_id = ?i", $convert_id);
        return ($val > 0);
    }
    
    public function getAdviceById($id_advice) {
        $sql = "SELECT * FROM paid_advices WHERE id = ?i";
        $res = $this->_db->row($sql, $id_advice);
        return $res;
    }
        
    /**
     * возвращает массив с недооформленными платными рекомендациями
     * @param штеупук $to_user кому адресованы рекомендации
     * @param integer $author от кого рекомендация (false - от всех, 1 - от фрилансеров, 2 - от работодателей)
     * @return array
     */
    public function getAdvices($to_user, /*$approved = true, */$author = false) {
        switch ($author) {
            case 1:
                $authorSQL = "AND e.role::bit(1) = B'0'";
                break;
            case 2:
                $authorSQL = "AND e.role::bit(1) = B'1'";
                break;
            default:
                $authorSQL = "";
                break;
        }
        $sql = "SELECT pa.*, e.login, e.role, e.photo, e.uname, e.usurname, e.is_pro, e.is_team, e.is_pro_test 
                FROM paid_advices pa
                INNER JOIN users e ON e.uid = pa.user_from AND e.is_banned = B'0'
                WHERE pa.user_to = ?i AND (pa.status <> ?i AND pa.status <> ?i)
                    $authorSQL
                ORDER BY pa.id DESC";
        $res = $this->_db->rows($sql, $to_user, self::STATUS_DELETED, self::STATUS_PAYED);
        //echo "<pre>".$this->_db->sql;
        return $res;    
    }

    public function approve() {
        
    }
    
    public function update($id, $data, $force=false) {
        if(!$id) return false;
        if(!is_array($data)) return false;
        $added = $force ? "" : $this->added_sql;
        $res = $this->_db->update('paid_advices', $data, "id = ?i {$added}", $id);
        return $this->_db->error;    
    }
    
    public function accepted($id_advice) {
        return $this->update($id_advice, array("status" => self::STATUS_ACCEPTED, 'accept_date' => 'NOW()'));   
    }
    
    public function decline($id_advice) {
        return $this->update($id_advice, array("status" => self::STATUS_DECLINED, 'decline_date' => 'NOW()'));      
    }
    
    public function restore($id_advice, $status) {
        if($status == self::STATUS_ACCEPTED || $status == self::STATUS_SENT ) {
            return $this->update($id_advice, array("status" => self::STATUS_ACCEPTED, 'accept_date' => 'NOW()', 'decline_date' => null));
        }    
    }
    
    public function refuse($id_advice) {
        return $this->update($id_advice, array("mod_status" => self::MOD_STATUS_USER_DECLINED, "status" => self::STATUS_ACCEPTED));
    }
    
    public function getSBRRating($sum) {
        $sql = "SELECT * FROM sbr_rating_get(?, DATE '2012-01-01', " . self::PAID_COMMISION . ")";
        return round($this->_db->val($sql, round($sum/30,2) ), 2);
        //$sql = "SELECT * FROM sbr_rating_get_new(?, 'NOW()')";
        //return $this->_db->val($sql, $sum);
    }
    
    public function getModStatus() {
        return array(self::MOD_STATUS_PENDING => "Отправлено на модерацию", self::MOD_STATUS_ACCEPTED => "Разрешено модератором", self::MOD_STATUS_DECLINED => "Отклонено модератором", self::MOD_STATUS_USER_DECLINED => "Отозвано пользователем" );
    }
    
    public function getModAdvices($type = false, $filter = false, $page = 0) {
        
        list($type_sql, $order) = $this->getTypeSQL($type);
        
        if($filter) {
            $filter_sql = " AND ".$this->getFilterSQL($filter);
        }
        
        if($page < 0) $page = 0;
        $limit = (int)self::COUNT_PAGE;
        $offset = self::COUNT_PAGE * $page;
        
        $sql = "SELECT pa.*, e.login as e_login, e.role as e_role, e.photo as e_photo, e.uname as e_uname, e.usurname as e_usurname, e.is_pro as e_is_pro, e.is_team as e_is_team, e.is_pro_test as e_is_pro_test,
                f.login as f_login, f.role as f_role, f.photo as f_photo, f.uname as f_uname, f.usurname as f_usurname, f.is_pro as f_is_pro, f.is_team as f_is_team, f.is_pro_test as f_is_pro_test,
                file_docs_contract.original_name as fname_docs_contract, file_docs_contract.path as path_docs_contract, file_docs_contract.fname as fname_contract, 
                file_docs_tz.original_name as fname_docs_tz, file_docs_tz.path as path_docs_tz, file_docs_tz.fname as fname_tz, 
                file_docs_result.original_name as fname_docs_result, file_docs_result.path as path_docs_result, file_docs_result.fname as fname_result
                FROM paid_advices pa
                INNER JOIN users e ON e.uid = pa.user_from AND e.is_banned = B'0'
                INNER JOIN users f ON f.uid = pa.user_to AND f.is_banned = B'0'
                LEFT JOIN file_advices as file_docs_contract ON file_docs_contract.id = pa.docs_contract
                LEFT JOIN file_advices as file_docs_tz ON file_docs_tz.id = pa.docs_tz
                LEFT JOIN file_advices as file_docs_result ON file_docs_result.id = pa.docs_result_file
                WHERE {$type_sql} {$filter_sql}
                ORDER BY {$order} 
                LIMIT {$limit} OFFSET {$offset}";
        $res = $this->_db->rows($sql);
        return $res;     
    }
    
    public function getFilterSQL($filter) {
        if($filter['flogin'] != "") {
            $sql[] = " e.login LIKE '{$filter['flogin']}%' ";
        }
        
        if($filter['tlogin'] != "") {
            $sql[] = " f.login LIKE '{$filter['tlogin']}%' ";
        }
        
        $fdate = date('Y-m-d', mktime(0, 0, 0, $filter['fmnth'], $filter['fday'], $filter['fyear']));
        $tdate = date('Y-m-d', mktime(0, 0, 0, $filter['tmnth'], $filter['tday'], $filter['tyear']));
        
        $sql[] = " pa.accept_date >= DATE '$fdate' AND pa.accept_date <= DATE '$tdate' + interval '1day'";
        
        if($filter['mod_status']) {
            $sql[] = " pa.mod_status = {$filter['mod_status']}";
        }
        
        if($filter['paid_status'] === 0) {
            $sql[] = " pa.status <> " . self::STATUS_PAYED;
        } else if($filter['paid_status'] == 1) {
            $sql[] = " pa.status = " . self::STATUS_PAYED; 
        }
        
        return implode(" AND ", $sql);
    }
    
    public function delete($id_advice) {
        return $this->update($id_advice, array("status" => self::STATUS_DELETED, 'delete_date' => 'NOW()', 'converted_id' => null));     
    }
    
    public function adminAccept($id_advice) {
        if(hasPermissions('users')) {
            return $this->update($id_advice, array("mod_status" => self::MOD_STATUS_ACCEPTED,
                                                   "mod_status_date" => "NOW()",
                                                   "status"  => self::STATUS_ACCEPTED, 
                                                   "mod_id"  => $_SESSION['uid'],
                                                   "mod_msg" => null     
                                 ), true);   
        } 
    }
    
    public function getStatAdvices($filter = false) {
        // оптимизирован временно (год-два, пока рекомендаций не очень много; основная проблема в джойнах с users): 0018602
        $sql = "SET join_collapse_limit = 1;
                SELECT COUNT(*) as cnt, status, mod_status 
                FROM paid_advices
                INNER JOIN users f ON f.uid = paid_advices.user_from AND f.is_banned = B'0'
                INNER JOIN users t ON t.uid = paid_advices.user_to AND t.is_banned = B'0'
                WHERE mod_status <> 0 GROUP by status, mod_status";
        $res = $this->_db->cache(60)->rows($sql);
        
        $counter = array('all' => 0, 'new' => 0, 'accepted' => 0, 'declined' => 0, 'deleted' => 0, 'filter' => 0);
        foreach($res as $k=>$val) {
            $counter['all'] += $val['cnt']; 
            if($val['status'] == self::STATUS_ACCEPTED && $val['mod_status'] == self::MOD_STATUS_PENDING) $counter['new'] += $val['cnt'];
            if($val['mod_status'] == self::MOD_STATUS_ACCEPTED) $counter['accepted'] += $val['cnt'];
            if($val['mod_status'] == self::MOD_STATUS_DECLINED) $counter['declined'] += $val['cnt'];
            if($val['mod_status'] == self::MOD_STATUS_USER_DECLINED ) $counter['deleted'] += $val['cnt'];
        }
        
        if($filter !== false) {
            list($type_sql, $order) = $this->getTypeSQL($filter['type']);
            $filter_sql = " AND ".$this->getFilterSQL($filter);
            
            $sql = "SELECT COUNT(pa.*) as cnt, SUM(pa.cost_sum) as cost_sum_filter, SUM(pa.comm_sum) as comm_sum_filter FROM paid_advices as pa 
                    INNER JOIN users e ON e.uid = pa.user_from AND e.is_banned = B'0'
                    INNER JOIN users f ON f.uid = pa.user_to AND f.is_banned = B'0'
                    WHERE {$type_sql} {$filter_sql}";
            $res = $this->_db->row($sql);
            $counter['filter'] = (int) $res['cnt'];
            $counter['cost_sum_filter'] = round($res['cost_sum_filter'], 2);
            $counter['comm_sum_filter'] = round($res['comm_sum_filter'], 2);
        }
        
        return $counter;
    }
    
    public function adminDecline($id_advice, $msg_declined) {
        if(hasPermissions('users')) {
            return $this->update($id_advice, array("mod_status" => self::MOD_STATUS_DECLINED,
                                                   "mod_status_date" => "NOW()",
                                                   "mod_id"  => $_SESSION['uid'],
                                                   "mod_msg" => $msg_declined     
                                 ), true);   
        } 
    }
    
    public function adminDelete($id_advice, $msg_declined) {
        if(hasPermissions('users')) {
            return $this->update($id_advice, array("mod_status" => self::MOD_STATUS_DECLINED,
                                                   "status"     => self::STATUS_BLOCKED,  
                                                   "mod_status_date" => "NOW()",
                                                   "mod_id"  => $_SESSION['uid'],
                                                   "mod_msg" => $msg_declined     
                                 ), true);   
        } 
    }
    
    public function setPayed($id_advice, $op_id) {
        return $this->update($id_advice, array("status" => self::STATUS_PAYED, 'op_id' => $op_id, 'accept_date' => 'NOW()', 'decline_date' => null, 'converted_id' => null)); 
    }
    
    public function payedAdvice($id_advice, $uid, $transaction_id, $tarif, $trs_sum) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
        
        $tarif = floatval($tarif);
        
  		$account = new account();
        $link    = "<a href='{$GLOBALS['host']}/users/{$_SESSION['login']}/opinions/#p_".($id_advice * 2)."' target='_blank'>Рекомендация</a>";
  		$error = $account->Buy($bill_id, $transaction_id, self::OP_CODE, $uid, "Оплата рекомендации", $link, $tarif, 0, 0, $trs_sum);
  		if ($error!==0) return 0;
  		
  		if ($bill_id) {
  		    $account->commit_transaction($transaction_id, $uid, $bill_id);
  		    $this->setPayed($id_advice, $bill_id);
  		    
  		    return $bill_id;
  		}
  		
        return 0;
    }
    
    /**
     * Возвращаем статус рекомендации
     * 
     * @param integer $id_advice   ИД рекомендации
     * @return integer Статус рекомендации    
     */
    public function getAdviceStatus($id_advice) {
        $sql = "SELECT status FROM paid_advices WHERE id = ?i";
        return $this->_db->val($sql, $id_advice);
    }
    
    /**
     * Тип рекомендации
     * 
     * @param string $type    Тип рекомендации
     * @return type
     */
    public function getTypeSQL($type) {
        $order = 'pa.accept_date DESC ';
        switch($type) {
            case 'all':
                $type_sql  = ' pa.mod_status <> 0';
                break;
            case 'accepted':
                $type_sql  = ' pa.mod_status = ' . self::MOD_STATUS_ACCEPTED;
                break;
            case 'declined':
                $type_sql  = ' pa.mod_status = ' . self::MOD_STATUS_DECLINED;
                break;
            case 'deleted':
                $type_sql  = ' pa.mod_status = ' . self::MOD_STATUS_USER_DECLINED;
                break;
            default:
                $type_sql = ' pa.status = ' . self::STATUS_ACCEPTED . ' AND pa.mod_status = '. self::MOD_STATUS_PENDING;
                $order    = 'pa.accept_date ASC ';
                break;
        }
        
        return array($type_sql, $order);
    }
    
    /**
     * Тип рекомендации относительно фильтра
     * 
     * @param string $type    Тип рекомендации
     * @return int 
     */
    public function getTypeModStatus($type) {
        switch($type) {
            case 'all':
                return 0;
                break;
            case 'accepted':
                return self::MOD_STATUS_ACCEPTED;
                break;
            case 'declined':
                return self::MOD_STATUS_DECLINED;
                break;
            case 'deleted':
                return self::MOD_STATUS_USER_DECLINED;
                break;
            default:
                return self::MOD_STATUS_PENDING;
                break;
        }
    }
}