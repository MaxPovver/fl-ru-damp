<?php

require_once $_SERVER['DOCUMENT_ROOT']."/classes/account.php";
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/offers_filter.php");

class freelancer_offers
{
    /**
  	 * Максимальный объем текста в описании предложения
  	 *
  	 */
	const MAX_SIZE_DESCRIPTION = 1500;
	
	/**
  	 * Максимальный объем текста в заголовке предложения
  	 *
  	 */
	const MAX_SIZE_TITLE = 200;
    
    /**
     * Номер операции в op_codes для оплаты через FM
     *
     * @var integer
     */
    const FM_OP_CODE = 94;
    
    /**
     * Стоимости публикации предложения
     *
     * @var integer
     */
    const SUM_FM_COST = 1;
    
    /**
     * Количество пользователей на 1 странице
     *
     * @var integer
     */
    const FRL_COUNT_PAGES = 40; // !!! Проверить
    
    
    protected $access;
    
    public function __construct() {
        $this->access = "";
        if(!hasPermissions('projects')) $this->access = "AND user_id = {$_SESSION['uid']}";
    }
    
    /**
     * Создание нового предложения
     *
     * @param array $create    Переменная типа array(name=>value) где name - поле таблицы, value - значение для записи (@see Таблица freelance_offers) 
     * @return boolean|string
     */
    public function Create($create) {
        global $DB;
        $uid = $create['user_id'];
        if($_SESSION['uid'] == $uid && !is_emp()) {
            $account = new account;
            $transaction_id = $account->start_transaction($uid, $tr_id);
            $error = $account->Buy($billing_id, $transaction_id, self::FM_OP_CODE, $uid, "Покупка публикации предложения фрилансера", "Покупка публикации предложения", 1, 0);
            if ($error) return $error;
            $account->commit_transaction($transaction_id, $uid, $billing_id);
            $create['bill_id'] = $billing_id;
            
            $create['moderator_status'] = is_pro() ? NULL : 0;
            
            $id_offer = $DB->insert('freelance_offers', $create, 'id');
            if($id_offer > 0) {
                if ( !is_pro() ) {
                    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );
                    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
                    
                    $stop_words    = new stop_words();
                    $nStopWordsCnt = $stop_words->calculate( $fields['descr'], $fields['title'] );
                    
                    $DB->insert( 'moderation', array('rec_id' => $id_offer, 'rec_type' => user_content::MODER_SDELAU, 'stop_words_cnt' => $nStopWordsCnt) );
                }
                
                return $id_offer;
            }
            return false;
        } else {
            return false;
        }
    }
    
    /**
     * Обновление предложения
     *
     * @param integer $fid      ИД обновляемого предложения
     * @param array   $update   Переменная типа array(name=>value) где name - поле таблицы, value - значение для записи (@see Таблица freelance_offers) 
     * @return boolean
     */
    public function Update($fid, $update) {
        global $DB;
        
        if ( !hasPermissions('projects') && !is_pro() ) {
            // автор, не админ, не про
            $update['moderator_status'] = 0;
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
            
            $stop_words = new stop_words();
            $nStopWordsCnt = $stop_words->calculate( $fields['descr'], $fields['title'] );
            
            $DB->insert( 'moderation', array('rec_id' => $fid, 'rec_type' => user_content::MODER_SDELAU, 'stop_words_cnt' => $nStopWordsCnt) );
        }
        
        if ( isset($update['is_blocked']) && $update['is_blocked'] ) {
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
            $DB->query( 'DELETE FROM moderation WHERE rec_id = ?i AND rec_type = ?i;', $fid, user_content::MODER_SDELAU );
        }
        
        $DB->update("freelance_offers", $update, "id = ?i {$this->access}", $fid);
        return true;    
    }
    
    /**
     * Данные по ленте предложений
     *
     * @param array|mixed $filter  Фильтр (@see offers_filter.php)
     * @param bool        $only_my_offs  показывать только мои предложения
     * @return array
     */
    public function GetFreelancerOffers($filter=false, $offset=0, $limit = 'ALL', $only_my_offs = false, $is_ban = false) {
        global $DB;
        
        $fSql = "";
        if($filter) {
            $fSql = offers_filter::createSqlFilter($filter, "AND");
        }
        
        $is_block = "";
        if(!hasPermissions('projects') || $is_ban) {
            $is_block = "AND (fo.is_blocked = 'f' OR fo.user_id = " . get_uid(0) . ") AND fo.is_closed = 'f'";     
        }
        
//        $only_my = "";
//        if ($only_my_offs) {
//            $only_my = "AND s.uid = ".get_uid(0);
//        }
        
        $offers = $DB->rows("SELECT 
                                fo.*, 
                                s.is_profi,
                                s.is_pro, s.is_pro_test, s.uname, s.usurname, s.login, s.uid, s.photo, s.spec, s.status_type, s.role, s.is_team,
                                s.cost_month, s.cost_type_month, s.site, s.icq, s.phone, s.ljuser, s.country, s.city, s.last_time, 
                                s.boss_rate, s.tabs, s.spec_orig,  s.pop, s.reg_date, s.birthday, s.info_for_reg, s.warn, s.is_banned, s.ban_where,
                                sm.completed_cnt as success_cnt,
                                uc.sbr_opi_plus as sg, uc.sbr_opi_minus as sl, uc.sbr_opi_null as se, zin(uc.sbr_opi_plus) - zin(uc.sbr_opi_minus) as ssum, 
                                uc.ops_emp_plus + uc.ops_frl_plus as e_plus, uc.ops_emp_null + uc.ops_frl_null as e_null, uc.ops_emp_minus + uc.ops_frl_minus as e_minus,
                                rating_get(s.rating, s.is_pro, s.is_verify, s.is_profi) as rating, 
                                s.cost_hour, s.cost_type_hour, 
                                p.name as profname, p.is_text, pg.name as cat_name, p.link 
                            FROM freelance_offers fo 
                            INNER JOIN freelancer s ON s.uid = fo.user_id AND s.is_banned = '0'
                            LEFT JOIN sbr_meta sm ON sm.user_id = s.uid
                            LEFT JOIN prof_group pg ON pg.id = fo.category_id
                            LEFT JOIN professions p ON p.id = fo.subcategory_id 
                            LEFT JOIN users_counters uc ON uc.user_id = s.uid
                            WHERE 1=1
                            {$is_block}
                            {$fSql}
                            ORDER BY fo.id DESC LIMIT {$limit} OFFSET {$offset};
                            ");
        
        return $offers;       
    }
    
    /**
     * Блокированые предложения из раздела "Сделаю"
     *
     * @param int &$total_offers  - сколько всего предложений, удовлетворяющих условиям поиска   
     * @param int $offset
     * @param mixed int|string $limit
     * @param string $search      - подстрока, которую будем искать в title, descr, login, uname, usurname
     * @return array
     */
    public function GetBlockedFreelancerOffers(&$total_offers, $offset=0, $limit = 'ALL', $search='') {
        global $DB;
        $filter = '';
        if (trim($search)) {
            $search = "%$search%";
        	$filter = "AND (
    fo.title LIKE('$search')
    OR fo.descr LIKE('$search')
    OR s.uname LIKE('$search')
    OR s.usurname LIKE('$search')
    OR s.login LIKE('$search')
)";
        }
        $offers = $DB->rows("SELECT 
                                fo.*, 
                                u.uname AS admin_name, u.usurname AS admin_sname, u.login AS admin_login,
                                s.is_pro, s.uname, s.usurname, s.login,
                                s.is_banned  
                            FROM freelance_offers fo 
                            INNER JOIN freelancer s ON s.uid = fo.user_id AND s.is_banned = '0'
                            LEFT JOIN users u ON u.uid = fo.admin
                            WHERE fo.is_blocked = true AND fo.is_closed = false {$filter}
                            ORDER BY fo.id DESC LIMIT {$limit} OFFSET {$offset};
                            ");
        $total_offers = $DB->val("SELECT COUNT(fo.id) 
                            FROM freelance_offers fo 
                            INNER JOIN freelancer s ON s.uid = fo.user_id AND s.is_banned = '0'                            
                            WHERE fo.is_blocked = true AND fo.is_closed = false {$filter}
                            ");
        return $offers;       
    }
    
    /**
     * Идентификаторы предложений из раздела "Сделаю"
     * 
     * @return array ($id => $position)
     */
    public function GetFreelancerOffersIdsPosition() {
    	$filter = offers_filter::GetFilter($_SESSION["uid"]);
        $fSql = "";
        if($filter) {
            $fSql = offers_filter::createSqlFilter($filter, "AND");
        }
        global $DB;
        $limit = 10 * freelancer_offers::FRL_COUNT_PAGES;
        $ids = $DB->cache(1800)->rows("SELECT 
                                fo.id 
                            FROM freelance_offers fo
                            WHERE 1 = 1 {$fSql}
                            ORDER BY fo.id DESC LIMIT {$limit};
                            ");
        $mirror = array();
        foreach($ids as $k=>$i) {
        	$mirror[$i["id"]] = $k;
        }
        return $mirror;
    }
    
    /**
     * Количество блокированых предложений раздела "Сделаю"     
     * @return int
     */
    public static function GetCountFreelancerBlockedOffers() {
        global $DB;
        $total_offers = $DB->val("SELECT COUNT(fo.id) 
                            FROM freelance_offers fo 
                            INNER JOIN freelancer s ON s.uid = fo.user_id AND s.is_banned = '0'                            
                            WHERE fo.is_blocked = true AND fo.is_closed = false
                            ");
        return $total_offers;
    }
    
    /**
     * Удаление предложения
     *
     * @param integer $fid ИД Предложения
     * @return 
     */
    public function Delete($fid) {
        global $DB;

        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
        $DB->query( 'DELETE FROM moderation WHERE rec_id = ?i AND rec_type = ?i;', $fid, user_content::MODER_SDELAU );
        return  $DB->query("DELETE FROM freelance_offers WHERE id = ?i {$this->access}", $fid);
    }
    
    /**
     * Возвращает предложение по его ИД
     * 
     * @param  integer $fid ИД Предложения
     * @param  boolean $access флаг проверки прав доступа
     * @return array
     */
    public function getOfferById($fid, $access = true) {
        global $DB;
        $sql = 'SELECT * FROM freelance_offers WHERE id = ?i '. ( $access ? $this->access : '' );
        return $DB->row( $sql, intval($fid) );
    }
    
    /**
     * Возвращает общее количество предложений
     *
     * @param unknown_type $filter
     * @return unknown
     */
    public function getCountFreelancerOffers($filter=false) {
        global $DB;
        
        $fSql = "";
        if($filter) {
            $fSql = offers_filter::createSqlFilter($filter, "AND");
        }
        $is_block = $is_admin = "";
        if(!hasPermissions('projects')) {
            $is_block = "AND fo.is_blocked = 'f' AND fo.is_closed = 'f' ";
            $inner_block = "INNER JOIN freelancer f ON  f.uid = fo.user_id AND f.is_banned = B'0'";
        } 
        
        return $DB->cache(300)->val("SELECT COUNT(fo.id) as all_cnt FROM freelance_offers as fo {$inner_block} WHERE 1=1 {$is_block} {$fSql};");
    }
    
    /**
     * Добавляет жалобу на предложение
     * 
     * @param  int $offer_id Идентификатор предложения на которое жалуются
     * @param  int $user_id Идентификатор пользователя который жалуется
     * @param  int $type Тип нарушения
     * @param  string $msg Суть жалобы
     * @return bool true - успех, false - провал
     */
    function AddComplain( $offer_id, $user_id, $type, $msg ) {
        $msg   = change_q_new( stripslashes($msg), true, true );
        $aData = compact( 'offer_id', 'user_id', 'type', 'msg' );
        
        $GLOBALS['DB']->insert( 'freelance_offers_complains', $aData );
        
        if ( !$GLOBALS['DB']->error ) {
            $oMemBuf = new memBuff();
            $oMemBuf->delete( 'complain_offers_count' );
        }

        return (!$GLOBALS['DB']->error);
    }
    
    /**
     * Оставлял ли юзер жалобу на предложение
     *
     * @param  int $project_id Идентификатор предложения на которое жалуются
     * @param  int $user_id Идентификатор пользователя который жалуется
     * @return bool true - оставлял, false - нет
     */
    function ComplainExists( $offer_id, $user_id ) {
        $sQuery = "SELECT COUNT(id) FROM freelance_offers_complains WHERE offer_id=?i AND user_id=?i";
        return (bool) $GLOBALS['DB']->val( $sQuery, $offer_id, $user_id );
    }
    
    /**
     * Возвращает количество предложений с жалобами
     * 
     * @return int
     */
    function GetComplainOffersCount() {
        $oMemBuf = new memBuff();
        $nCount  = $oMemBuf->get('complain_offers_count');
        
        if ( $nCount === false ) {
            $sCountQuery = 'SELECT COUNT(msi.min_id) AS cnt FROM ( 
                SELECT MIN(oca.id) AS min_id FROM freelance_offers_complains oca 
                INNER JOIN freelance_offers foa ON foa.id = oca.offer_id 
                WHERE foa.is_closed = false 
                GROUP BY foa.id ) AS msi';
            
            $nCount = $GLOBALS['DB']->val( $sCountQuery );
            
            if ( !$GLOBALS['DB']->error ) {
            	$oMemBuf->set( 'complain_offers_count', $nCount, 3600 );
            }
        }
        
        return $nCount;
    }
    
    /**
     * Возвращает предложения с жалобами
     * 
     * @param  int $nums возвращает кол-во предложений с жалобами
     * @param  string $error возвращает сообщение об ошибке
     * @param  int $page номер страницы
     * @param  string $sort тип сортировки
     * @param  string $search строка для поиска
     * @param  int $admin uid модератора, заблокированные предложения которого нужно показать
     * @param  int $nLimit количество элементов на странице
     * @param  bool $unlimited установить в true если нужно получить все записи (без постраничного вывода)
     * @return array
     */
    function GetComplainOffers( &$nums, &$error, $page = 1, $sort = '', $search = '', $admin = 0, $nLimit = 20, $unlimited = false ) {
        $nLimit      = intval($nLimit);
        $nOffset     = $nLimit * ($page - 1);
        $bCountCache = false;
        
        // сортировка
        $sOrder = ( $sort == 'login' ) ? ' login ' : ( $search ? ' relevant DESC ' : ' oc.date DESC ' );
        
        // поиск
        $sSelect = '';
        $sWhere  = '';
        
        if ( $search ) {
        	$aWords = preg_split( "/\\s/", $search );
        	
        	foreach ( $aWords as $sWord ) {
                $sSelect .= "(
                    CASE
                    WHEN
                        (LOWER(login) = LOWER('$sWord') OR LOWER(uname) = LOWER('$sWord') OR LOWER(usurname) = LOWER('$sWord') OR LOWER(title) = LOWER('$sWord')) THEN 2 
                    WHEN
                        (LOWER(login) LIKE LOWER('%$sWord%') OR LOWER(uname) LIKE LOWER('%$sWord%') OR LOWER(usurname) LIKE LOWER('%$sWord%') OR LOWER(title) LIKE LOWER('%$sWord%')) THEN 1 
                    ELSE 0
                    END
                ) + ";
                
                $sWhere .= "(LOWER(ua.login) LIKE LOWER('%$sWord%') OR LOWER(ua.uname) LIKE LOWER('%$sWord%') OR LOWER(ua.usurname) LIKE LOWER('%$sWord%') OR LOWER(foa.title) LIKE LOWER('%$sWord%')) OR ";
            }
            
            $sSelect = substr( $sSelect, 0, strlen($sSelect) - 3 );
            $sWhere  = substr( $sWhere, 0, strlen($sWhere) - 4 );
        }
        else {
            $bCountCache = true;
        }
        
        $sQuery = 'SELECT fo.*, oc.type AS complain_type, oc.msg AS complain_msg, oc.date AS complain_date, 
                ocu.uname AS complain_uname, ocu.usurname AS complain_usurname, ocu.login AS complain_login, 
                fou.is_pro, fou.is_team, fou.uname, fou.usurname, fou.login, 
                p.name as profname, p.is_text, pg.name as cat_name, p.link, c.complain_cnt 
            FROM (
                SELECT MIN(oca.id) AS min_id FROM freelance_offers_complains oca 
                INNER JOIN freelance_offers foa ON foa.id = oca.offer_id 
                INNER JOIN freelancer ua ON ua.uid = foa.user_id 
                WHERE foa.is_closed = false ' . ( $sWhere ? ' AND ' . $sWhere : '' ) 
                . 'GROUP BY foa.id 
            ) AS oci 
            INNER JOIN freelance_offers_complains oc ON oc.id = oci.min_id 
            INNER JOIN freelance_offers fo ON fo.id = oc.offer_id 
            INNER JOIN users ocu ON ocu.uid = oc.user_id 
            INNER JOIN freelancer fou ON fou.uid = fo.user_id 
            LEFT JOIN prof_group pg ON pg.id = fo.category_id 
            LEFT JOIN professions p ON p.id = fo.subcategory_id 
            LEFT JOIN ( 
                -- количество жалоб на предложение
                SELECT MIN(foc.id) AS min_cnt_id, COUNT(occ.id) AS complain_cnt 
                FROM freelance_offers_complains occ 
                INNER JOIN freelance_offers foc ON foc.id = occ.offer_id 
                GROUP BY foc.id 
            ) AS c ON c.min_cnt_id = fo.id';
                
                
        $sQuery = ( $sSelect ? "SELECT s.*, ($sSelect) AS relevant FROM ($sQuery) AS s" : $sQuery )
            . ' ORDER BY ' . $sOrder 
            . ( !$unlimited ? ' LIMIT ' . $nLimit . ' OFFSET ' . $nOffset : '' );
            
        $sCountQuery = 'SELECT COUNT(msi.min_id) AS cnt FROM ( 
            SELECT MIN(oca.id) AS min_id FROM freelance_offers_complains oca 
            INNER JOIN freelance_offers foa ON foa.id = oca.offer_id ' 
            . ( $sWhere ? ' INNER JOIN freelancer ua ON ua.uid = foa.user_id ' : '' )
            . ' WHERE foa.is_closed = false ' . ( $sWhere ? ' AND ' . $sWhere : '' ) 
            . 'GROUP BY foa.id ) AS msi';
        
        $nums = $GLOBALS['DB']->val( $sCountQuery );
        
        $ret = $GLOBALS['DB']->rows( $sQuery );
        
        return $ret;
    }
    
    /**
     * Возвращает список жалоб на предложение.
     * 
     * @param  int $nOfferId Идентификатор предложения
     * @return array
     */
    function getOfferComplaints( $nOfferId = 0 ) {
        $sQuery = 'SELECT o.*, u.uname, u.usurname, u.login 
            FROM freelance_offers_complains o 
            INNER JOIN users u ON u.uid = o.user_id 
            WHERE o.offer_id = ? 
            ORDER BY o.id';
        
        return $GLOBALS['DB']->rows( $sQuery, $nOfferId );
    }
    
    /**
     * Удаляет все жалобы на предложение
     * 
     * @param  int $nOfferId Идентификатор предложения
     * @return bool true - успех, false - провал
     */
    function deleteOfferComplaints( $nOfferId = 0 ) {
        $GLOBALS['DB']->query( 'DELETE FROM freelance_offers_complains WHERE offer_id = ?i', $nOfferId );
        
        if ( !$GLOBALS['DB']->error ) {
            $oMemBuf = new memBuff();
            
            if ( ($nCount = $oMemBuf->get('complain_offers_count')) !== false ) {
                $nCount = $nCount - 1;
                $oMemBuf->set( 'complain_offers_count', $nCount, 3600 );
            }
            else {
                $oMemBuf->delete( 'complain_offers_count' );
            }
        }
        
        return (!$GLOBALS['DB']->error);
    }
    
    /**
     * Возвращает тип нарушения по номеру
     * 
     * @param  int $complain_type тип нарушения
     * @return string
     */
    function GetComplainType( $complain_type ) {
        switch( $complain_type ) {
            case 1:
                $sName = 'Реклама, массовая публикация';
                break;
            case 2:
                $sName = 'Дубликат предложения (за 24 часа)';
                break;
            case 4:
                $sName = 'Контактные данные';
                break;
            case 5:
                $sName = 'Реклама, ссылки на сторонние ресурсы';
                break;
            case 6:
                $sName = 'Мат, ругань, оскорбления';
                break;
            case 3:
            default:
                $sName = 'Другое';
                break;
        }
        
        return $sName;
    }
}

?>
