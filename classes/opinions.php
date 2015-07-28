<?

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

/**
 * Класс для работы с отзывами на сайте
 *
 */
class opinions {

    /**
     * Максимальное количество символов в отзыве.
     *
     * @var int
     */
    public static $opinion_max_length = 1000;
    /**
     * Максимальное количество символов в комментарии к отзыву.
     *
     * @var int
     */
    public static $comment_max_length = 1000;

    /**
     * Получает список отзывов, которые оставили пользователю
     *
     * @param integer $touser_id        id пользователя, которому написан отзыв
     * @param unknown $msg_cntr         не используется
     * @param unknown $page             не используется
     * @param unknown $num_msgs         не используется
     * @param string $error             текст ошибки
     * @param string $users_table       используемая таблица
     * @param string $sort              тип отзывов, которые следует выбрать (false - все, 1 - положительные, 2 - нейтральные, 3 - отрицательные)
     * @param integer $period           период за который отображать отзывы: 0 - все, 1 - за год, 2 - за пол года, 3 - за месяц
     * @param integer $author           0 - от всех пользователей, 1 - от фрилансеров, 2 - от работодателей
     * 
     * @return mixed                    массив отзывов или сообщение об ошибке в случае неуспеха
     */
    function GetMsgs($touser_id, $msg_cntr, $page, $num_msgs, $error, $users_table = 'users', $sort=false, $period=0, $author = 0) {
        if( !in_array($users_table, array('freelancer', 'employer', 'users')) ) {
            return NULL;
        }
        
        $bPermissions = hasPermissions('users');
        switch ($sort) {
            case 1: $rating = 1;
                break;
            case 2: $rating = 0;
                break;
            case 3: $rating = -1;
                break;
            default: $rating = false;
                break;
        }

        switch($period) {
            case 1:
                $periodSQL = "AND o.post_time > NOW()-interval '1 year'";
                break;
            case 2:
                $periodSQL = "AND o.post_time > NOW()-interval '6 month'";
                break;
            case 3:
                $periodSQL = "AND o.post_time > NOW()-interval '1 month'";
                break;
            default:
                $periodSQL = '';
        }
        
        switch($author) {
            case 1:
                $authorSQL = "AND u.role::bit(1)::int = 0";
                break;
            case 2:
                $authorSQL = "AND u.role::bit(1)::int = 1";
                break;
            default:
                $authorSQL = '';
        }
        

        $sql =
                "SELECT o.*, u.login, u.uname, u.usurname, u.role, u.photo, u.is_pro, u.is_pro_test, u.is_banned, u.reg_date as ago 
           FROM opinions o, {$users_table} u
              WHERE o.touser_id = ?i
                  " . ($rating !== false ? " AND o.rating = $rating" : "") . "
                AND u.uid = o.fromuser_id
                " . ( $bPermissions ? '' : ' AND u.is_banned::integer = 0 ' ) . "
                {$periodSQL}
                {$authorSQL}
              ORDER BY o.post_time DESC";
        
        global $DB;
        $res = $DB->query( $sql, $touser_id );
        $error = pg_errormessage();
        if ($error)
            $error = parse_db_error($error);
        else
            $ret = pg_fetch_all($res);
        return $ret;
    }

    /**
     * Получает список отзывов, которые оставил пользователь
     *
     * @param integer $fromuser_id      id пользователя, который написал отзыв
     * @param unknown $msg_cntr         не используется
     * @param unknown $page             не используется
     * @param unknown $num_msgs         не используется
     * @param string $error             текст ошибки
     * @param string $sort              тип отзывов, которые следует выбрать (false - все, 1 - положительные, 0 - нейтральные, -1 - отрицательные)
     * @param integer $period           период за который отображать отзывы: 0 - все, 1 - за год, 2 - за пол года, 3 - за месяц
     *
     * @return mixed                    массив отзывов или сообщение об ошибке в случае неуспеха
     */
    function GetMyMsgs($fromuser_id, $msg_cntr, $page, $num_msgs, $error, $sort = false, $period = 0) {
        //$sql = "SELECT id, fromuser_id, reg_date as ago, touser_id, post_time, msgtext,
        // modified, opinions.rating, login, uname, usurname, role, photo FROM opinions
        //  LEFT JOIN users ON fromuser_id=uid WHERE touser_id = '$touser_id' ORDER BY post_time DESC";

        switch ($sort) {
            case 1: $rating = 1;
                break;
            case 2: $rating = 0;
                break;
            case 3: $rating = -1;
                break;
            default: $rating = false;
                break;
        }

        switch($period) {
            case 1:
                $periodSQL = "AND post_time > NOW()-interval '1 year'";
                break;
            case 2:
                $periodSQL = "AND post_time > NOW()-interval '6 month'";
                break;
            case 3:
                $periodSQL = "AND post_time > NOW()-interval '1 month'";
                break;
            default:
                $periodSQL = '';
        }

        $sql = "SELECT id, fromuser_id, touser_id, reg_date as ago, touser_id, post_time, msgtext,
         modified, opinions.rating, login, uname, usurname, role, photo, is_pro, is_pro_test FROM opinions, users
          WHERE fromuser_id = ?i " . ($rating !== false ? " AND opinions.rating = $rating" : "") . " AND users.uid=touser_id AND users.is_banned=B'0' {$periodSQL} ORDER BY post_time DESC";

        global $DB;
        $res = $DB->query( $sql, $fromuser_id );
        $error = pg_errormessage();
        if ($error)
            $error = parse_db_error($error);
        else
            $ret = pg_fetch_all($res);
        return $ret;
    }

    /**
     * Получает определенный отзыв по его id
     *
     * @param integer $msg_id           id отзыва
     * @param string $error             текст ошибки
     *
     * @return mixed                    отзыв или сообщение об ошибке в случае неуспеха
     */
    function GetMsgInfo($msg_id, $error) {
        global $DB;
        $sql = "SELECT id, fromuser_id, msgtext, rating, post_time FROM opinions WHERE id = ?";
        $res = $DB->query( $sql, $msg_id );
        $error = pg_errormessage();
        if ($error)
            $error = parse_db_error($error);
        else
            $ret = pg_fetch_assoc($res);
        return $ret;
    }

    /**
     * Добавление нового отзыва
     *
     * @param integer $fromuser_id      id пользователя, который написал отзыв
     * @param integer $touser_id        id пользователя, которому написан отзыв
     * @param string $msg               отзыв
     * @param integer $rating           оценка (1 - положительный, 0 - нейтральный, -1 - отрицательный)
     * @param string $from_ip           IP написавшего отзыв
     *
     * @return string                   текст ошибки в случае неуспеха
     */
    function NewMsg($fromuser_id, $touser_id, $msgtext, $rating, $from_ip, &$new_id) {
        if ($fromuser_id == 0) return;
        
        global $DB;
        $data = compact( 'fromuser_id', 'touser_id', 'msgtext', 'rating', 'from_ip' );
        
        $new_id = $DB->insert( 'opinions', $data, 'id' );
        
        //
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
        $usr = new users();
        $sub = $usr->GetField($touser_id, $error, "subscr");

        return $DB->error;
    }

    /**
     * получаем последний добавленный отзыв, для пользователя $fromuser_id пользователю $touser_id
     *
     * @param integer $fromuser_id      id пользователя, который написал отзыв
     * @param integer $touser_id        id пользователя, которому написан отзыв
     *
     * @return array                    данные отзыва (или false в случае ошибки)
     */
    function GetLastMessage($fromuser_id, $touser_id) {
        $sql =
                "SELECT o.*, u.login, u.uname, u.usurname, u.role, u.photo, u.is_pro, u.is_pro_test, u.reg_date as ago
           FROM opinions o, users u
              WHERE o.touser_id = ? 
                AND o.fromuser_id = ? 
                AND u.uid = o.fromuser_id
                AND u.is_banned = '0'
              ORDER BY o.id DESC";

        global $DB;
        $res = $DB->query( $sql, $touser_id, $fromuser_id );
        $error = pg_errormessage();
        if ($error)
            return false;
        else
            return pg_fetch_assoc($res);
    }

    /**
     * получаем отзыв по его ID
     *
     * @param integer $msg_id      id отзыва
     *
     * @return array                    данные отзыва (или false в случае ошибки)
     */
    function GetMessageById($msg_id) {
        $sql =
                "SELECT o.*, u.login, u.uname, u.usurname, u.role, u.photo, u.is_pro, u.is_pro_test, u.reg_date as ago
           FROM opinions o, users u
              WHERE o.id = ? 
                AND u.uid = o.fromuser_id
                AND u.is_banned = '0'
              ORDER BY o.id DESC";
        
        global $DB;
        $res = $DB->query( $sql, $msg_id );
        $error = pg_errormessage();
        if ($error)
            return false;
        else
            return pg_fetch_assoc($res);
    }

    /**
     * Удаление отзыва
     *
     * @param integer $fromuser_id      id пользователя, который написал отзыв
     * @param integer $msg              id отзыва
     * @param integer $admin            кто удаляет отзыв (1 - администратор, 0 - пользователь)
     *
     * @return string                   текст ошибки в случае неуспеха
     */
    function DeleteMsg($fromuser_id, $msg, $admin = 0) {
        global $DB;
        
        if (!$admin)
            $row = $DB->row( 'SELECT touser_id,rating,fromuser_id FROM opinions WHERE id = ? AND fromuser_id = ?', $msg, $fromuser_id );
        else
            $row = $DB->row( 'SELECT touser_id,rating,fromuser_id FROM opinions WHERE id = ?', $msg );
        
        list( $touser_id, $raiting, $fromuserid) = $row;

        if (!$admin)
            $DB->query( "DELETE FROM opinions WHERE id = ? AND fromuser_id = ?", $msg, $fromuser_id );
        else
            $DB->query( "DELETE FROM opinions WHERE id = ?", $msg );
        
        //
        if ($touser_id) {
            if (!hasPermissions('users') || $fromuserid == get_uid(false)) {
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
                $usr = new users();
                $sub = $usr->GetField($touser_id, $error, "subscr");
            }
        }
        //

        return $DB->error;
    }

    /**
     * Посылает сообщение пользователю о том, что отзыв, оставленный ему удален
     *
     * @param string $fromuser_login    login пользователя, который написал отзыв
     *
     * @return mixed                    
     */
    function HideOpin($fromuser_login) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
        $usr = new users();
        $usr->GetUser($fromuser_login);
        $fromuser_id = $usr->uid;

        global $DB;
        $sql = "SELECT touser_id, rating FROM opinions WHERE fromuser_id = ?";
        $res = $DB->query( $sql, $fromuser_id );
        while (list( $touser_id, $raiting) = @pg_fetch_row($res)) {

            $sub = $usr->GetField($touser_id, $error, "subscr");
        }
    }

    /**
     * Редактирование отзыва
     *
     * @param integer $fromuser_id      id пользователя, который написал отзыв
     * @param integer $msgid            id отзыва
     * @param string $msg               отзыв
     * @param integer $rating           оценка (1 - положительный, 0 - нейтральный, -1 - отрицательный)
     * @param string $from_ip           IP написавшего отзыв
     * @param integer $admin            кто редактирует отзыв (1 - администратор, 0 - пользователь)
     * @param integer $modified_id      UID юзера кто редактирует отзыв
     *
     * @return string                   текст ошибки в случае неуспеха
     */
    function Edit($fromuser_id, $msgid, $msg, $rating, $from_ip, $admin = 0, $modified_id = null) {
        global $DB;
        
        if ($admin)
            $DB->query( 'UPDATE opinions SET msgtext = ?, rating = ?, from_ip = ?, modified_id = ?, modified=NOW() WHERE id = ?', 
                $msg, $rating, $from_ip, $modified_id, $msgid 
            );
        else
            $DB->query( "UPDATE opinions SET msgtext = ?, rating = ?, from_ip = ?, modified_id = ?, modified=NOW() WHERE id = ? AND fromuser_id = ?", 
                $msg, $rating, $from_ip, $modified_id, $msgid, $fromuser_id 
            );
        
        return pg_errormessage();
    }

    
    
    /**
     * Получает массив с количествм сообщений, разбитых по типам
     *
     * @param integer $user_id          id пользователя, для которого считаем
     * @param array $fromType           типы отзывов которых нужно посчитать (сейчас не используется)
     * 
     * @todo: $fromType - пережиток от предыдущей верии функции возможно не понадобиться, тогда убрать везде
     *
     * @return array                    массив данных
     *      'norisk' - СБР-отзывы, 
     *      'emp' - от работодателей, 
     *      'frl' - от фрилансеров
     */    
    static function GetCounts($user_id, $fromType = array())
    {
        global $DB;
        
        $ext = isset($_SESSION['uid']) && $user_id == $_SESSION['uid']? '_ext' : '';
        
        $ret = array('all' => array('p' => 0, 'n' => 0, 'm' => 0));
        
        //Сумма по простым типам
        $map = array(
            'all' => array(
                'p' => array('ops_frl_plus', 'ops_emp_plus'),
                'n' => array('ops_frl_null', 'ops_emp_null'),
                'm' => array('ops_frl_minus', 'ops_emp_minus')
            ),
            
            'emp' => array(
                'p' => array('ops_emp_plus'),
                'n' => array('ops_emp_null'),
                'm' => array('ops_emp_minus')
            ),
            
            'frl' => array(
                'p' => array('ops_frl_plus'),
                'n' => array('ops_frl_null'),
                'm' => array('ops_frl_minus')
            ),
            
            'norisk' => array(
                'p' => array('sbr_opi_plus'),
                'n' => array('sbr_opi_null'),
                'm' => array('sbr_opi_minus')
            ),
            
            'tu_orders' => array(
                'p' => array('tu_orders_plus'),
                'm' => array('tu_orders_minus')
            ),
            
            'project_feedbacks' => array(
                'p' => array("projects_fb{$ext}_plus"),
                'm' => array("projects_fb{$ext}_minus")
            ),
                        
            'paid_advices' => array(
                'p' => array('paid_advices_cnt')
            ),
        );
        
        //Составные типы
        $map_complex = array(
            'total' => array('all', 'norisk', 'tu_orders', 'project_feedbacks', 'paid_advices'),
            'frl_total' => array('frl', 'norisk', 'tu_orders', 'project_feedbacks', 'paid_advices'),
            'emp_total' => array('emp', 'norisk', 'tu_orders', 'project_feedbacks', 'paid_advices')
        );        
        
        
        //Обнуление всех типов
        $allTypes = array_merge(array_keys($map), array_keys($map_complex));

        foreach($allTypes as $type) {
            $ret[$type] = $ret['all'];
        }

        
        $data = $DB->row("
            SELECT * 
            FROM users_counters 
            WHERE user_id = ?i", 
        $user_id);
        
        if ($data) {
            //Подсчет простых типов
            foreach ($map as $type => $item) {
                foreach ($item as $rateType => $fields) {
                    foreach ($fields as $field) {
                        $ret[$type][$rateType] += $data[$field];
                    }
                }
            }
            
            //Подсчет составных типов
            foreach ($map_complex as $complexType => $simpleTypes) {
                foreach ($simpleTypes as $simpleType) {
                    foreach ($ret[$simpleType] as $rateType => $value ) {
                        $ret[$complexType][$rateType] += $value;
                    }
                }
            }
        }
        
        
        return $ret;
    }






    /**
     * @deprecated см GetCounts
     * 
     * Получает массив с количествм сообщений, разбитых по типам
     *
     * @param integer $user_id          id пользователя, для которого считаем
     * @param array $fromType           типы отзывов ('norisk' - СБР-отзывы, 'emp' - от работодателей, 'frl' - от фрилансеров, 'my' - мои)
     *
     * @return array                    массив данных
     */
    function GetCounts_OLD($user_id, $fromType) 
    {
        global $DB;
        
        $bPermissions = hasPermissions('users');
        
        $ret = array('all' => array('p' => 0, 'n' => 0, 'm' => 0));
        $ret['norisk'] = $ret['total'] = $ret['emp'] = $ret['frl'] = $ret['my'] = $ret['tu_orders'] = $ret['all'];
        
        if ( !$bPermissions ) {
            /*$sql = "SELECT SUM((o.rating = 1)::int) as p, SUM((o.rating = 0)::int) as n, SUM((o.rating = -1)::int) as m
                    FROM opinions o
                    INNER JOIN users u ON u.uid = o.fromuser_id 
                    WHERE o.touser_id = ?i AND u.is_banned::integer = 0";*/
            $sql = "SELECT ops_frl_plus + ops_emp_plus as p, ops_emp_null + ops_frl_null as n, ops_emp_minus + ops_frl_minus as m FROM users_counters WHERE user_id = ?";
            $ret['users']= $DB->row($sql, $user_id);
            
            /**
             * @deprecated #0017304 
             */
            if (in_array('all', $fromType) || in_array('frl', $fromType) || in_array('total', $fromType)) {
                $sql = "SELECT ops_frl_plus + ops_emp_plus as p, ops_frl_null + ops_emp_null as n, ops_frl_minus + ops_emp_minus as m FROM users_counters WHERE user_id = ?";
                if ( $res = $DB->query($sql, $user_id) )
                    $ret['all'] = pg_fetch_assoc($res);
            }
            if (in_array('emp', $fromType) || in_array('frl', $fromType)) {
                $sql = "SELECT ops_emp_plus as p, ops_emp_null as n, ops_emp_minus as m FROM users_counters WHERE user_id = ?";
                if ( $res = $DB->query($sql, $user_id) )
                    $ret['emp'] = pg_fetch_assoc($res);
            }
            
            if (in_array('frl', $fromType)) {
                $ret['frl']['p'] = $ret['all']['p'] - $ret['emp']['p'];
                $ret['frl']['n'] = $ret['all']['n'] - $ret['emp']['n'];
                $ret['frl']['m'] = $ret['all']['m'] - $ret['emp']['m'];
            }
        } else {
            $sql = "SELECT SUM((o.rating = 1)::int) as p, SUM((o.rating = 0)::int) as n, SUM((o.rating = -1)::int) as m
                    FROM opinions o
                    INNER JOIN users u ON u.uid = o.fromuser_id 
                    WHERE o.touser_id = ?i AND u.is_banned::integer = 0";
            $ret['users']= $DB->row($sql, $user_id);
            /**
             * @deprecated #0017304 
             */
            if ( in_array('all', $fromType) || in_array('frl', $fromType) || in_array('total', $fromType) ) {
                $sql = 'SELECT SUM((o.rating = 1)::int) as p, SUM((o.rating = 0)::int) as n, SUM((o.rating = -1)::int) as m
                    FROM opinions o
                    INNER JOIN freelancer u ON u.uid = o.fromuser_id
                    WHERE o.touser_id = ?';
                
                if ( $res = $DB->query($sql, $user_id) ) {
                    $ret['frl'] = pg_fetch_assoc( $res );
                }
            }
            
            if ( in_array('all', $fromType) || in_array('emp', $fromType) || in_array('total', $fromType) ) {
                $sql = 'SELECT SUM((o.rating = 1)::int) as p, SUM((o.rating = 0)::int) as n, SUM((o.rating = -1)::int) as m
                    FROM opinions o
                    INNER JOIN employer u ON u.uid = o.fromuser_id 
                    WHERE o.touser_id = ?';
                
                if ( $res = $DB->query($sql, $user_id) ) {
                    $ret['emp'] = pg_fetch_assoc( $res );
                }
            }
            
            if (in_array('emp', $fromType) || in_array('frl', $fromType)) {
                $sql = "SELECT ops_emp_plus as p, ops_emp_null as n, ops_emp_minus as m FROM users_counters WHERE user_id = ?";
                if ( $res = $DB->query($sql, $user_id) )
                    $ret['emp'] = pg_fetch_assoc($res);
            }
            
            if ( in_array('all', $fromType) || in_array('total', $fromType) ) {
                $ret['all']['p'] = $ret['frl']['p'] + $ret['emp']['p'];
                $ret['all']['n'] = $ret['frl']['n'] + $ret['emp']['n'];
                $ret['all']['m'] = $ret['frl']['m'] + $ret['emp']['m'];
            }
        }
        
        if (in_array('norisk', $fromType) || in_array('total', $fromType)) {
            $sql = "SELECT sbr_opi_plus as p, sbr_opi_null as n, sbr_opi_minus as m FROM users_counters WHERE user_id = ?";
            if ( $res = $DB->query($sql, $user_id) )
                $ret['norisk'] = pg_fetch_assoc($res);
        }
        
        if (in_array('total', $fromType)) {
            $ret['total']['p'] = $ret['all']['p'] + $ret['norisk']['p'];
            $ret['total']['n'] = $ret['all']['n'] + $ret['norisk']['n'];
            $ret['total']['m'] = $ret['all']['m'] + $ret['norisk']['m'];
        }
        
        if (in_array('my', $fromType)) {
            $sql = "SELECT SUM((o.rating = 1)::int) as p, SUM((o.rating = 0)::int) as n, SUM((o.rating = -1)::int) as m
                      FROM opinions o
                    INNER JOIN
                      users u
                        ON u.uid = o.touser_id
                        " . ( $bPermissions ? '' : ' AND u.is_banned::integer = 0 ' ) . "
                     WHERE o.active = true
                       AND o.fromuser_id = ?
                      ";
            if ( $res = $DB->query($sql, $user_id) )
                $ret['my'] = pg_fetch_assoc($res);
        }
        

        if (in_array('tu_orders', $fromType) || in_array('total', $fromType)) 
        {
            $sql = "SELECT 
                        tu_orders_plus as p, 
                        0 as n, 
                        tu_orders_minus as m 
                    FROM users_counters 
                    WHERE user_id = ?";
            
            if ( $res = $DB->query($sql, $user_id) )
            {
                $ret['tu_orders'] = pg_fetch_assoc($res);
            }
        }
        
        if (in_array('total', $fromType)) 
        {
            $ret['total']['p'] = $ret['total']['p'] + $ret['tu_orders']['p'];
            $ret['total']['n'] = $ret['total']['n'] + $ret['tu_orders']['n'];
            $ret['total']['m'] = $ret['total']['m'] + $ret['tu_orders']['m'];
        }
        
        if (in_array('project_feedbacks', $fromType) || in_array('total', $fromType)) 
        {
            $ext = $user_id == get_uid(false) || is_moder() ? '_ext' : ''; 
            $sql = "SELECT 
                        projects_fb{$ext}_plus as p, 
                        0 as n, 
                        projects_fb{$ext}_minus as m 
                    FROM users_counters 
                    WHERE user_id = ?";
            
            if ( $res = $DB->query($sql, $user_id) )
            {
                $ret['project_feedbacks'] = pg_fetch_assoc($res);
            }
        }
        
        if (in_array('total', $fromType)) 
        {
            $ret['total']['p'] = $ret['total']['p'] + $ret['project_feedbacks']['p'];
            $ret['total']['n'] = $ret['total']['n'] + $ret['project_feedbacks']['n'];
            $ret['total']['m'] = $ret['total']['m'] + $ret['project_feedbacks']['m'];
        }
        
        return $ret;
    }
    
    /**
     * возвращает количества отзывов (мнения + рекомендации) для фильтра в разделе ОТЗЫВЫ
     * количество для разных авторов отзываов (от всех, от фрилансеров, от работодателей)
     * количество для разных периодов (за все время, за месяц, за полгода ...)
     * 
     * @param integer $userID ID пользователя для чьи отзывы смотрим
     * @param bool $isEmp true - работодатель, false - фрилансер
     * @param integer $rating параметр сортировки на странице отзывов (false - все отзывы, 1 - положительные, 2 - нейтральные, 3 - отрицательные)
     * @param integer $author значение фильтри автор отзыва (false - все отзывы, 1 - от фрилансеров, 2 - от работодателей)
     * @param integer $period значение фильтра период отзыва (false - за все время, 1 - за последний год, 2 - за последние полгода, 3 - за последний месяц)
     * 
     * @return array массив с результатом
     */
    function getFilterCounts ($userID, $isEmp, $rating = false, $author = false, $period = false) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/paid_advices.php");
        global $DB;
        
        $current_uid = get_uid(false);
        $isAdmin = hasPermissions('users');
        
        if ($isEmp) {
            $userRole = 'emp';
            $userAntiRole = 'frl';
        } else {
            $userRole = 'frl';
            $userAntiRole = 'emp';
        }
        
        switch ($rating) {
            case 1:
                $opiRating = ' AND o.rating = 1';
                $recRating = ' AND sf.rating = 1';
                $tuRating = ' AND fb.rating = 1';
                $pfbRating = ' AND pfb.rating = 1';
                break;
            case 2:
                $opiRating = ' AND o.rating = 0';
                $recRating = ' AND sf.rating = 0';
                //У типовых услуг нет нейтрального рейтинга 
                //посему ничего не выберится
                $tuRating = ' AND fb.rating = 0';
                $pfbRating = ' AND pfb.rating = 0';
                break;
            case 3:
                $opiRating = ' AND o.rating = -1';
                $recRating = ' AND sf.rating = -1';
                $tuRating = ' AND fb.rating = -1';
                $pfbRating = ' AND pfb.rating = -1';
                break;
            default:
                $opiRating = '';
                $recRating = '';
                $tuRating = '';
                $pfbRating = '';
                break;
        }
        
        switch($period) {
            case 1:
                $opiPeriod = " AND o.post_time > NOW()-interval '1 year'";
                $recPeriod = " AND sf.posted_time > NOW()-interval '1 year'";
                $paidPeriod = " AND pa.create_date > NOW()-interval '1 year'";
                $tuPeriod = " AND fb.posted_time > NOW()-interval '1 year'";
                $pfbPeriod = " AND pfb.posted_time > NOW()-interval '1 year'";
                break;
            case 2:
                $opiPeriod = " AND o.post_time > NOW()-interval '6 month'";
                $recPeriod = " AND sf.posted_time > NOW()-interval '6 month'";
                $paidPeriod = " AND pa.create_date > NOW()-interval '6 month'";
                $tuPeriod = " AND fb.posted_time > NOW()-interval '6 month'";
                $pfbPeriod = " AND pfb.posted_time > NOW()-interval '6 month'";
                break;
            case 3:
                $opiPeriod = " AND o.post_time > NOW()-interval '1 month'";
                $recPeriod = " AND sf.posted_time > NOW()-interval '1 month'";
                $paidPeriod = " AND pa.create_date > NOW()-interval '1 month'";
                $tuPeriod = " AND fb.posted_time > NOW()-interval '1 month'";
                $pfbPeriod = " AND pfb.posted_time > NOW()-interval '1 month'";
                break;
            default:
                $opiPeriod = '';
                $recPeriod = '';
                $paidPeriod = '';
                $tuPeriod = '';
                $pfbPeriod = '';
        }
        
        switch($author) {
            case 1:
                $opiAuthor = "AND u.role::bit(1)::int = 0";
                $recAuthor = $userRole === 'frl' ? ' AND FALSE' : '';
                $paidAuthor = $recAuthor;
                $tuAuthor = $recAuthor;
                break;
            case 2:
                $opiAuthor = "AND u.role::bit(1)::int = 1";
                $recAuthor = $userRole === 'emp' ? ' AND FALSE' : '';
                $paidAuthor = $recAuthor;
                $tuAuthor = $recAuthor;
                $pfbAuthor = $recAuthor;
                break;
            default:
                $opiAuthor = '';
                $recAuthor = '';
                $paidAuthor = '';
                $tuAuthor = '';
                $pfbAuthor = '';
        }
        
        $sql = "
            -- мнения
            SELECT
            'opi' as type,
            SUM(CASE WHEN TRUE $opiPeriod THEN 1 ELSE 0 END) as from_total,
            SUM(CASE WHEN u.role::bit(1) = B'1' $opiPeriod THEN 1 ELSE 0 END) as from_emp,
            SUM(CASE WHEN u.role::bit(1) = B'0' $opiPeriod THEN 1 ELSE 0 END) as from_frl,
            SUM(CASE WHEN TRUE $opiAuthor THEN 1 ELSE 0 END) as last_total,
            SUM(CASE WHEN o.post_time > NOW() - interval '1 month' $opiAuthor THEN 1 ELSE 0 END) as last_month,
            SUM(CASE WHEN o.post_time > NOW() - interval '6 month' $opiAuthor THEN 1 ELSE 0 END) as last_half_year,
            SUM(CASE WHEN o.post_time > NOW() - interval '1 year' $opiAuthor THEN 1 ELSE 0 END) as last_year
            FROM opinions o
            INNER JOIN users u
                ON u.uid = o.fromuser_id
            WHERE o.touser_id = ?i
                AND u.is_banned = B'0'
                $opiRating
            

            -- рекомендации
            UNION
            SELECT
            'rec' as type,
            SUM(CASE WHEN TRUE $recPeriod THEN 1 ELSE 0 END) as from_total,";
        if ($userRole === 'frl') {
            $sql .= "
            SUM(CASE WHEN TRUE $recPeriod THEN 1 ELSE 0 END) as from_emp,
            0 as from_frl,";
            //$sql .= $author == 1 ? '0' : 'COUNT(*)' . 'as from_emp,';
            //$sql .= '0 as from_frl,';
        } else {
            $sql .= "
            0 as from_emp,
            SUM(CASE WHEN TRUE $recPeriod THEN 1 ELSE 0 END) as from_frl,";
            //$sql .= '0 as from_emp,';
            //$sql .= $author == 2 ? '0' : 'COUNT(*)' . 'as from_frl,';
        }            
        $sql .= "
            SUM(CASE WHEN TRUE $recAuthor THEN 1 ELSE 0 END) as last_total,
            SUM(CASE WHEN sf.posted_time > NOW() - interval '1 month' $recAuthor THEN 1 ELSE 0 END) as last_month,
            SUM(CASE WHEN sf.posted_time > NOW() - interval '6 month' $recAuthor THEN 1 ELSE 0 END) as last_half_year,
            SUM(CASE WHEN sf.posted_time > NOW() - interval '1 year' $recAuthor THEN 1 ELSE 0 END) as last_year
            FROM sbr s
            INNER JOIN sbr_stages ss
                ON s.id = ss.sbr_id
            INNER JOIN sbr_feedbacks sf
                ON sf.id = ss.{$userAntiRole}_feedback_id
            WHERE s.{$userRole}_id = ?i
                AND ss.{$userAntiRole}_feedback_id IS NOT NULL
                AND sf.deleted IS NOT TRUE
                $recRating";
                

        if ($rating != 2 && $rating != 3) {
            $sql .= "
                -- платные рекомендации
                UNION
                SELECT
                'paid' as type,
                SUM(CASE WHEN TRUE $paidPeriod THEN 1 ELSE 0 END) as from_total,";
            if ($userRole === 'frl') {
                $sql .= "
                SUM(CASE WHEN TRUE $paidPeriod THEN 1 ELSE 0 END) as from_emp,
                0 as from_frl,";
                //$sql .= $author == 1 ? '0' : 'COUNT(*)' . 'as from_emp,';
                //$sql .= '0 as from_frl,';
            } else {
                $sql .= "
                0 as from_emp,
                SUM(CASE WHEN TRUE $paidPeriod THEN 1 ELSE 0 END) as from_frl,";
                //$sql .= '0 as from_emp,';
                //$sql .= $author == 2 ? '0' : 'COUNT(*)' . 'as from_frl,';
            } 
            $sql .= "
                SUM(CASE WHEN TRUE $paidAuthor THEN 1 ELSE 0 END) as last_total,
                SUM(CASE WHEN pa.create_date > NOW() - interval '1 month' $paidAuthor THEN 1 ELSE 0 END) as last_month,
                SUM(CASE WHEN pa.create_date > NOW() - interval '6 month' $paidAuthor THEN 1 ELSE 0 END) as last_half_year,
                SUM(CASE WHEN pa.create_date > NOW() - interval '1 year' $paidAuthor THEN 1 ELSE 0 END) as last_year
                FROM paid_advices pa
                WHERE pa.user_to = ?i
                    AND pa.status = " . paid_advices::STATUS_PAYED . "
                    AND pa.op_id IS NOT NULL";
        }
        
        
        $sql .= "
            UNION
            SELECT
                'tu_orders' AS type,
                SUM(CASE WHEN TRUE {$tuPeriod} THEN 1 ELSE 0 END) as from_total,
                ".(($isEmp)?"0":"SUM(CASE WHEN TRUE {$tuPeriod} THEN 1 ELSE 0 END)")." as from_emp,
                ".((!$isEmp)?"0":"SUM(CASE WHEN TRUE {$tuPeriod} THEN 1 ELSE 0 END)")." as from_frl,    
                SUM(CASE WHEN TRUE {$tuAuthor} THEN 1 ELSE 0 END) as last_total,                
                SUM(CASE WHEN fb.posted_time > NOW() - interval '1 month'  THEN 1 ELSE 0 END) as last_month,
                SUM(CASE WHEN fb.posted_time > NOW() - interval '6 month'  THEN 1 ELSE 0 END) as last_half_year,
                SUM(CASE WHEN fb.posted_time > NOW() - interval '1 year'  THEN 1 ELSE 0 END) as last_year
            FROM tservices_orders_feedbacks AS fb 
            INNER JOIN tservices_orders AS o ON o.{$userAntiRole}_feedback_id = fb.id
            WHERE
                o.{$userRole}_id = ?i 
                AND fb.deleted = FALSE
                {$tuRating}
        ";
                
                
        $sql .= "
            -- Отзывы по проектам
            UNION
            SELECT
                'project_feedbacks' AS type,
                SUM(CASE WHEN TRUE {$pfbPeriod} THEN 1 ELSE 0 END) as from_total,
                ".(($isEmp)?"0":"SUM(CASE WHEN TRUE {$pfbPeriod} THEN 1 ELSE 0 END)")." as from_emp,
                ".((!$isEmp)?"0":"SUM(CASE WHEN TRUE {$pfbPeriod} THEN 1 ELSE 0 END)")." as from_frl,    
                SUM(CASE WHEN TRUE {$pfbAuthor} THEN 1 ELSE 0 END) as last_total,                
                SUM(CASE WHEN pfb.posted_time > NOW() - interval '1 month'  THEN 1 ELSE 0 END) as last_month,
                SUM(CASE WHEN pfb.posted_time > NOW() - interval '6 month'  THEN 1 ELSE 0 END) as last_half_year,
                SUM(CASE WHEN pfb.posted_time > NOW() - interval '1 year'  THEN 1 ELSE 0 END) as last_year
            FROM projects_feedbacks AS pfb 
            WHERE
                pfb.touser_id = ?i
                AND pfb.deleted = FALSE
                AND (pfb.show = TRUE ".(($current_uid > 0)?"OR pfb.user_id = {$current_uid} OR pfb.touser_id = {$current_uid} ":"")."OR ".($isAdmin?'TRUE':'FALSE').")
                {$pfbRating}
        ";

        $res = $DB->rows($sql, $userID, $userID, $userID, $userID, $userID);
        
        $result = array();
        foreach($res as $el)
        {
            foreach($el as $key => $value)
            {
                if(in_array($key, array('type'))) continue;
                if(!isset($result[$key])) $result[$key] = 0;
                $result[$key] += intval($value);
            }
        }
        
        return $result;
    }

    /**
     * Проверка на возможность пользователя оставлять отзыв
     *
     * @param integer $from_id          id пользователя, который хочет написать отзыв
     * @param integer $to_id            id пользователя, которому хотят написать отзыз
     *
     * @return integer                  id Ошибки или 3 в случае успеха
     */
    function CheckUserCanPost($from_id, $to_id) {
        if (!$from_id)
            return 1;
        if ($from_id == $to_id)
            return 0;
            
        global $DB;
        $sql = "SELECT reg_date FROM users WHERE uid = ?";
        $res = $DB->query( $sql, $from_id );
        list($reg_date) = pg_fetch_row($res);
        if (ElapsedMnths(strtotime($reg_date)) == "Меньше месяца")
            return 2;
        $sql = "SELECT COUNT(*) FROM opinions WHERE fromuser_id = ? AND touser_id = ?";
        $res = $DB->query( $sql, $from_id, $to_id );
        list($msgs) = pg_fetch_row($res);
        if ($msgs == 0)
            return 0;
        return 3;
    }

    /**
     * Получение статистики по отзывам для админки
     *
     * @param string $date              дата начала выборки
     * @param string $edate             дата окончания выборки
     * @param string $rating            тип отзыва (NULL - все, 1 - положительные, 0 - нейтральные, -1 - отрицательные)
     * @param string $login             логин определенного пользователя (NULL - все)
     * @global DB    $DB
     * 
     * @return array                    массив данных
     */
    function getOpinionsData($date, $edate, $rating=NULL, $login=NULL) {
        $date  = date("c", $date);
        $edate = date("c", mktime("23", "59", "59", date('m', $edate), date('d', $edate), date("Y", $edate)));
        //echo $date;
        if ($login) {
            $join = "
                INNER JOIN (SELECT uid FROM users WHERE LOWER(login) = LOWER( ? ) OR LOWER(uname) = LOWER( ? ) OR LOWER(usurname) = LOWER( ? )) users
                ON fromuser_id = users.uid OR touser_id = users.uid
            ";
        }
        $sql = "
            SELECT * 
            FROM opinions
            $join
            WHERE 
                post_time >= '$date' AND post_time <= '$edate' " .
                (!is_null($rating) ? " AND rating = '$rating' " : "") .
                "ORDER BY modified DESC NULLS LAST, post_time DESC
        ";
        //echo $sql;
        global $DB;
        if ($login) {
            $res = $DB->query( $sql, $login, $login, $login );
        } else {
            $res = $DB->squery( $sql );
        }
        
        if ( $DB->error )
            $error = parse_db_error( $DB->error );
        else
            $ret = pg_fetch_all($res);

        if (!$ret)
            return array(false, false);

        foreach ($ret as $k => $val) {
            $buser[$val['fromuser_id']] = $val['fromuser_id'];
            $buser[$val['touser_id']]   = $val['touser_id'];
        }

        $sql = "SELECT login, uname, usurname, uid, role FROM users WHERE uid IN (?l)";
        $res = $DB->query( $sql, $buser );
        
        if ( $DB->error )
            $error = parse_db_error( $DB->error );
        else
            $ubank = pg_fetch_all($res);

        if ( isset($ubank) && is_array($ubank) ) {
        	foreach ($ubank as $k => $v)
                $retbank[$v['uid']] = $v;
        }

        return array($ret, $retbank);
    }

    /**
     * Добавление комментария к мнению
     *
     * @param  string  $comment     текст комментария
     * @param  integer $user_id     id пользователя, который осавил комментарий
     * @param  integer $opinion_id  id комментария
     *
     * @return string               текст ошибки в случае неуспеха
     */
    function newCommentOpinion( $comment, $user_id, $opinion_id ) {
        if (self::isComOpinion($opinion_id) > 0) {
            return false;
        }
        global $DB;
        $data = compact( 'opinion_id', 'user_id', 'comment' );
        $opinion_user_id = $DB->val("SELECT touser_id FROM opinions WHERE id = ?", $data["'opinion_id'"]);
        if ($opinion_user_id != $user_id ) {
            return false;
        }
        $DB->insert( 'opinion_comments', $data );
        if ( !$DB->error ) {
            $fromuser_id = $DB->val( 'SELECT fromuser_id FROM opinions WHERE id = ?', $opinion_id );
            
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
            $smail = new smail();
            $smail->SendCommentOpinions($fromuser_id, $user_id);
        }

        return $DB->error;
    }
    
    /**
     * Добавление комментария к отзыву
     *
     * @param  string  $comment     текст комментария
     * @param  integer $user_id     id пользователя, который осавил комментарий
     * @param  integer $opinion_id  id комментария
     *
     * @return string               текст ошибки в случае неуспеха
     */
    function newCommentFeedback( $comment, $user_id, $feedback_id ) {
        /*if (self::isComOpinion($opinion_id) > 0)
            return false;*/

        global $DB;
        $data = compact( 'feedback_id', 'user_id', 'comment' );
        //#0024860
        $row = $DB->row("SELECT s.frl_id, s.emp_id 
                         FROM sbr_stages AS ss 
                         LEFT JOIN sbr AS s ON ss.sbr_id = s.id
                         WHERE ss.emp_feedback_id = ? OR ss.frl_feedback_id = ?;", $data['feedback_id'], $data['feedback_id']);
        if ( $user_id != $row['frl_id'] && $user_id != $row['emp_id']) {
            return false;
        }
        $DB->insert( 'sbr_feedbacks_comments', $data );

        if ( !$DB->error ) {
            $feedback = sbr_meta::getFeedback($feedback_id, true);
            $fromuser_id = $feedback['fromuser_id'];
            //$fromuser_id = $DB->val( 'SELECT fromuser_id FROM opinions WHERE id = ?', $opinion_id );
            
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
            $smail = new smail();
            $smail->SendCommentFeedback($fromuser_id, $user_id);
        }

        return $DB->error;
    }

    /**
     * Проверка на существование комментрия к отзыву
     *
     * @param integer $id             id отзыва
     *
     * @return mixed                  id комментария или текст ошибки в случае неуспеха
     */
    function isComOpinion($id) {
        global $DB;
        $sql = "SELECT id FROM opinion_comments WHERE opinion_id = ?";
        $res = $DB->query( $sql, $id );
        $error = pg_errormessage();
        if ($error)
            $error = parse_db_error($error);
        else
            $ret = pg_fetch_assoc($res);
        return $ret['id'];
    }

    /**
     * Получает комментарии к отзывам от определенного пользователя
     *
     * @param integer $uid             uid отзыва
     *
     * @return mixed                   массив с комментариями или текст ошибки в случае неуспеха
     */
    function getCommentOpinion($uid) {
        global $DB;
        $sql = "SELECT * FROM opinion_comments WHERE user_id = ?";
        $res = $DB->query( $sql, $uid );
        $error = pg_errormessage();
        if ($error)
            $error = parse_db_error($error);
        else
            $ret = pg_fetch_all($res);


        if (!$ret)
            return false;
        foreach ($ret as $val) {
            $result[$val['opinion_id']] = $val;
        }

        return $result;
    }

    /**
     * Получает комментарии к мнениям
     *
     * @param array $ids               массив с id отзывов
     *
     * @return mixed                   массив с комментариями или текст ошибки в случае неуспеха
     */
    function getCommentOpinionById($ids) {
        global $DB;
        $sql = "SELECT * FROM opinion_comments WHERE opinion_id IN (?l)";
        $res = $DB->query( $sql, $ids );
        $error = pg_errormessage();
        if ($error)
            $error = parse_db_error($error);
        else
            $ret = pg_fetch_all($res);

        if (!$ret)
            return false;
        foreach ($ret as $val) {
            $result[$val['opinion_id']] = $val;
        }

        return $result;
    }
    
    /**
     * Получает комментарии к отзывам 
     *
     * @param array $ids               массив с id отзывов
     *
     * @return mixed                   массив с комментариями или текст ошибки в случае неуспеха
     */
    function getCommentFeedbackById($ids) {
        global $DB;
        $sql = "SELECT * FROM sbr_feedbacks_comments WHERE feedback_id IN (?l)";
        $res = $DB->query( $sql, $ids );
        $error = pg_errormessage();
        if ($error)
            $error = parse_db_error($error);
        else
            $ret = pg_fetch_all($res);

        if (!$ret)
            return false;
        foreach ($ret as $val) {
            $result[$val['feedback_id']] = $val;
        }

        return $result;
    }

    /**
     * Удаление комментария к отзыву
     *
     * @param integer $id              id комментария
     * @param integer $uid             id пользователя, оставившего комментарий
     * @param integer $admin           кто удаляет отзыв (1 - администратор, 0 - пользователь)
     * @param integer $isFeedback      true - значит это отзыв
     *
     * @return string                  текст ошибки в случае неуспеха
     */
    function deleteComment($id, $uid, $admin = 0, $isFeedback = false) {
        global $DB;
        
        $tableName = $isFeedback ? 'sbr_feedbacks_comments' : 'opinion_comments';
        //$opinionName = $isFeedback ? 'feedback_id' : 'opinion_id';
        
        /*if (!$admin)
            $row = $DB->row( 'SELECT id, ' . $opinionName . ', user_id FROM ' . $tableName . ' WHERE id = ? AND user_id = ?', $id, $uid );
        else
            $row = $DB->row( 'SELECT id, ' . $opinionName . ', user_id FROM ' . $tableName . ' WHERE id = ?', $id );*/
        
        //list( $cid, $opinion_id, $user_id) = $row;

        if (!$admin)
            $DB->query( 'DELETE FROM ' . $tableName . ' WHERE id = ? AND user_id = ?', $id, $uid );
        else
            $DB->query( 'DELETE FROM ' . $tableName . ' WHERE id = ?', $id );

        return $DB->error;
    }

    /**
     * Получает комментрий к отзыву по его id
     *
     * @param integer $id              id комментария
     *
     * @return mixed                   массив с данными комментария или текст ошибки в случае неуспеха
     */
    function GetMsgComInfo($msg_id) {
        global $DB;
        $sql = "SELECT id, user_id, comment, opinion_id FROM opinion_comments WHERE id = ?";
        $res = $DB->query( $sql, $msg_id );
        $error = pg_errormessage();
        if ($error)
            $error = parse_db_error($error);
        else
            $ret = pg_fetch_assoc($res);
        return $ret;
    }

    /**
     * Редактирование комментария к мнению
     *
     * @param string $msg               id комментария
     * @param integer $uid              id пользователя, редактирующего комментарий
     * @param integer $id_edit          id комментария
     *
     * @return string                   текст ошибки в случае неуспеха
     */
    function editCommentOpinion($msg, $uid, $id_edit) {
        global $DB;
        
        if (hasPermissions('users')) {
            $DB->query( 'UPDATE opinion_comments SET comment = ?, date_change=now() WHERE id = ?', $msg, $id_edit );
        } else {
            $DB->query( 'UPDATE opinion_comments SET comment = ?, date_change=now() WHERE id = ? AND user_id = ?', $msg, $id_edit, get_uid(false) );
        }
        
        return $DB->error;
    }
    
    /**
     * Редактирование комментария к отзыву
     *
     * @param string $msg               id комментария
     * @param integer $uid              id пользователя, редактирующего комментарий
     * @param integer $id_edit          id комментария
     * @param integer $feedbackAuthorID id пользователя оставившего отзыв
     *
     * @return string                   текст ошибки в случае неуспеха
     */
    function editCommentFeedback($msg, $uid, $id_edit, $feedbackAuthorID) {
        global $DB;
        $msg = pg_escape_string($msg);
        if (hasPermissions('users')) {
            $DB->query( 'UPDATE sbr_feedbacks_comments SET comment = ?, date_change=now() WHERE id = ?', $msg, $id_edit );
        } else {
            $DB->query( 'UPDATE sbr_feedbacks_comments SET comment = ?, date_change=now() WHERE id = ? AND user_id = ?', $msg, $id_edit, get_uid(false) );
        }
        
        if (!$DB->error) {
            //$feedback = sbr_meta::getFeedback($feedback_id, true);
            //$fromuser_id = $feedback['fromuser_id'];
            
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
            $smail = new smail();
            $smail->SendCommentFeedback($feedbackAuthorID, $uid, true);
        }
        
        return $DB->error;
    }

    /**
     * Выборка информации о новых отзывах
     * 
     * После изменения этой функции, необходимо перезапустить консьюмер /classes/pgq/mail_cons.php на сервере.
     * Если нет возможности, то сообщить админу.
     * @see pmail::NewOpinion()
     * @see PGQMailSimpleConsumer::finish_batch()
     *
     * @param string|array  $ids            Идентификаторы отзывов
     * @param resource      $connect        Соединение к БД (необходимо в PgQ) или NULL -- создать новое.
     * @return array|mixed                  Если есть ответы к проектам то возвращает массив их, если нет то NULL
     */
    public function getNewOpinion($ids, $connect=NULL) {
        if (!$ids)
            return NULL;
        if (is_array($ids)) {
            foreach ($ids as $k => $v) {
                $ids[$k] = intval($v);
            }
            $ids = implode(',', array_unique($ids));
        } else {
            $ids = intval($ids);
        }

        $sql = "SELECT
                    o.*,
                    fu.uid as f_uid, fu.uname as f_uname, fu.usurname as f_usurname, fu.login as f_login, fu.role as f_role,
                    mu.uid as m_uid, mu.uname as m_uname, mu.usurname as m_usurname, mu.login as m_login, 
                    tu.uid as t_uid, tu.uname as t_uname, tu.usurname as t_usurname, tu.login as t_login, tu.email as t_email, tu.subscr as t_subscr, tu.is_banned as t_banned
                FROM 
                    opinions o
                INNER JOIN 
                    users fu ON fu.uid = o.fromuser_id
                INNER JOIN 
                    users tu ON tu.uid = o.touser_id 
                LEFT JOIN 
                    users mu ON mu.uid = o.modified_id 
                WHERE
                o.id IN ({$ids});";

        if ($res = pg_query($connect ? $connect : DBConnect(), $sql))
            return pg_fetch_all($res);

        return NULL;
    }

    
    //==================HTML_GENERATION==================
    
    /**
     * Возвращает html заголовка страницы с отзывами в массиве
     * 
     * @param string $from (frl|my|emp|norisk) параметр from
     * @param users $user пользователь, на странице которого мы в данный момент находимся
     * @param integer $to_id пользователь, для которго мы готовим вывод
     * @return array html заголовка страницы с отзывами в массиве
     */
    public static function getHeaderData($from, $user, $to_id) 
    {
        $opcount = opinions::GetCounts($to_id, array('emp', 'norisk', 'frl', 'all', 'total'));

        $out = array();
        
        /*
         * @todo: неиспользуется?
         * 
         * 
        $out['all'] = getSortOpinionLinkEx($from, "users", 1, $user->login, zin($opcount['all']['p']))
                . '&nbsp;&nbsp;' . getSortOpinionLinkEx($from, "users", 2, $user->login, zin($opcount['all']['n']))
                . '&nbsp;&nbsp;' . getSortOpinionLinkEx($from, "users", 3, $user->login, zin($opcount['all']['m']));
        
        $out['emp'] = getSortOpinionLinkEx($from, "emp", 1, $user->login, zin($opcount['emp']['p']))
                . '&nbsp;&nbsp;' . getSortOpinionLinkEx($from, "emp", 2, $user->login, zin($opcount['emp']['n']))
                . '&nbsp;&nbsp;' . getSortOpinionLinkEx($from, "emp", 3, $user->login, zin($opcount['emp']['m']));

        $out['frl'] = getSortOpinionLinkEx($from, "frl", 1, $user->login, zin($opcount['frl']['p']))
                . '&nbsp;&nbsp;' . getSortOpinionLinkEx($from, "frl", 2, $user->login, zin($opcount['frl']['n']))
                . '&nbsp;&nbsp;' . getSortOpinionLinkEx($from, "frl", 3, $user->login, zin($opcount['frl']['m']));
        
        $out['norisk'] = getSortOpinionLinkEx($from, "norisk", 1, $user->login, zin($opcount['norisk']['p']))
                . '&nbsp;&nbsp;' . getSortOpinionLinkEx($from, "norisk", 2, $user->login, zin($opcount['norisk']['n']))
                . '&nbsp;&nbsp;' . getSortOpinionLinkEx($from, "norisk", 3, $user->login, zin($opcount['norisk']['m']));
        
        $out['total'] = getSortOpinionLinkEx($from, "total", 1, $user->login, zin($opcount['total']['p']))
                . '&nbsp;&nbsp;' . getSortOpinionLinkEx($from, "total", 2, $user->login, zin($opcount['total']['n']))
                . '&nbsp;&nbsp;' . getSortOpinionLinkEx($from, "total", 3, $user->login, zin($opcount['total']['m']));
        */
        
        
        $out['total_no_author'] = getSortOpinionLinkEx($from, "total", 1, $user->login, zin($opcount['total']['p']), null, 0)
                . '&nbsp;&nbsp;' . getSortOpinionLinkEx($from, "total", 2, $user->login, zin($opcount['total']['n']), null, 0)
                . '&nbsp;&nbsp;' . getSortOpinionLinkEx($from, "total", 3, $user->login, zin($opcount['total']['m']), null, 0);

        
        return $out;
    }

    /**
     * Функция возвращает отдельный отзыв
     * 
     * @param array $theme отзыв
     * @param string $from (frl|my|emp|norisk) параметр from
     * @param integer $counter номер отзыва
     * @param boolean $with_container флаг, указывающий возвращать ли нам div-контейнер
     * @return string
     */
    public static function printTheme($theme, $from, $counter, $with_container = true) {
        global $session;
        session_start();
        $user_from = new users();
        $user_to = new users();
        $cnt_role = is_emp($theme['role']) ? 'employer' : 'freelancer';
        $block_class = $theme['rating'] == 1 ? 'plus' : ($theme['rating'] == -1 ? 'minus' : 'neitral' );
        $block_suffix = $counter == 1 ? ' first' : '';
        if($counter == -1) $block_suffix = ' last';
        $block_single = $from != 'my' ? true : false;

        $user_from->GetUserByUID($theme['fromuser_id']);
        $user_to->GetUserByUID($theme['touser_id']);
        
        $on_site = "";
        if (hasPermissions('users')) {
            $on_site = "На сайте " . ElapsedMnths(strtotime($user_from->reg_date));
        }

        $opcomm = opinions::getCommentOpinionById(array($theme['id']));

        $op_edited = ($theme['modified'] && $theme['modified']!=$theme['post_time']) ? date("d.m.Y H:i", strtotime($theme['modified'])) : false;
        $com_edited = !empty($opcomm[$theme['id']]['date_change']) ? date("d.m.Y H:i", strtotime($opcomm[$theme['id']]['date_change'])) : false;

        $html  = $with_container ? '<div class="ops-one c ops-one-' . $block_class .$block_suffix. '" id="opid_' . $theme['id'] . '">' : '';
        $html .= '
                <a name="o_' . $theme['id'] . '"><img src="/images/1.gif" width="1" height="1" alt="" /></a>
                <b class="ops-vs"></b>
                <div class="ops-one-cnt">
                    <ul class="ops-i">';
        if ($op_edited)
            $html .= '<li><img src="/images/ico-e-u.png" title="Отредактировано ' . $op_edited . '" alt="Отредактировано ' . $op_edited . '" /></li>';
        $html .= '<li class="ops-time">' . date("d.m.Y H:i", strtotime($theme['post_time'])) . '</li>
                        <li><a href="#o_' . $theme['id'] . '" onclick="hlAnchor(\'o\',' . $theme['id'] . ')" class="ops-anchor">#</a></li>
                    </ul>';
        if (!$block_single) {//Double Users
            $html .= self::printUserInfoMy($user_to, 'to') . self::printUserInfoMy($user_from);
        } else {
            $html .= '<a href="/users/' . $user_from->login . '" class="' . $cnt_role . '-name">' . strtr(view_avatar($theme['login'], $theme['photo']), array('<img' => '<img style="float: left"')) . '</a>';
        }
        $html .= '<div class="user-info" style="height:auto">';

        if ($block_single) {
            $html .= '<div class="username" style="font-size:12px">' . __prntUsrInfo($user_from);
            if ($on_site) {
                $html .= "<i>{$on_site}</i>";
            }
            $html .= '</div>';
        }
        
        if ( $user_from->is_banned ) { 
            $html .= ' <div style="color:#000; margin: 0 0 10px 0;" ><b>Пользователь&nbsp;забанен.</b></div>'; 
        }
        
        $html .= '<div class="utxt" id="msg_cont_' . $theme['id'] . '"><p id="message_text_' . $theme['id'] . '">' . reformat2($theme['msgtext'], 48, 0, 0, 1) . '</p></div>';

        if (($theme['fromuser_id'] == $_SESSION['uid'] || hasPermissions('users')) && $from != 'norisk') { //Мой комент или я админ
            $html .= '<ul class="opsa-op" id="edit_block_' . $theme['id'] . '">
                                <li><a href="#" onclick="if(!window._opiLock) { window._opiLock = true; xajax_EditOpinionForm(' . $theme['id'] . ',\'' . $from . '\'); } return false;" class="lnk-dot-red">Редактировать</a></li>
                                <li><a href="#" onclick="if (confirm(\'Вы действительно хотите удалить мнение?\')) xajax_DeleteOpinion(' . $theme['id'] . ',\'' . $from . '\'); return false;" class="lnk-dot-red">Удалить</a></li>
                            </ul>';
        }

        if (!empty($opcomm[$theme['id']])) {
            $html .= '
<a name="a_' . $theme['id'] . '"><img src="/images/1.gif" width="1" height="1" alt="" /></a>
<div class="ops-answer" id="ops_answer_' . $theme['id'] . '">
									<div id="ops_answer_link_' . $theme['id'] . '">
										<ul class="ops-i">';
            if ($com_edited)
                $html .= '<li><img src="/images/ico-e-u.png" title="Отредактировано ' . $com_edited . '" alt="Отредактировано ' . $com_edited . '" /></li>';
            $html .= '<li class="ops-time">' . date("d.m.Y H:i", strtotime($opcomm[$theme['id']]['date_create'])) . '</li>
											<li><a href="#a_' . $theme['id'] . '" onclick="hlAnchor(\'a\',' . $theme['id'] . ')" class="ops-anchor">#</a></li>
										</ul>
										<strong>' . __prntUsrInfo($user_to) . '</strong>
										<div class="utxt"><p>
                                                                                ' . reformat($opcomm[$theme['id']]['comment'], 48, 0, 0, 1) . '
										</p></div>
                                                                         ';
            if ($theme['touser_id'] == $_SESSION['uid'] || hasPermissions('users')) {
                $html .= '<ul class="opsa-op">
										<li><a href="#" onclick="if(!window._opiLock) { window._opiLock = true; xajax_AddOpComentForm(' . $theme['id'] . ',\'' . $from . '\'); } return false;" class="lnk-dot-red">Редактировать</a></li>
										<li><a href="#" onclick="if (confirm(\'Вы действительно хотите удалить комментарий?\'))xajax_DeleteOpinionComm(' . $theme['id'] . ',' . $opcomm[$theme['id']]['id'] . ', \'' . $from . '\'); return false;" class="lnk-dot-red">Удалить</a></li>
									</ul>';
            }

            $html .= '</div>
								</div>';
        }

        if ($theme['touser_id'] == $_SESSION['uid'] && empty($opcomm[$theme['id']]) && $from != "my") {
            $html .= '
                                        <div class="ops-answer" id="ops_answer_' . $theme['id'] . '">
                                            <div class="opsa-lnk-add" id="ops_answer_link_' . $theme['id'] . '"><a href="#" onclick="if(!window._opiLock) { window._opiLock = true; xajax_AddOpComentForm(' . $theme['id'] . ',\'' . $from . '\'); } return false;" class="lnk-dot-666">Добавить комментарий</a></div>
                                        </div>';
        }
        $html .= '</div></div>';
        $html .= $with_container ? '</div>' : '';
        return $html;
    }

    /**
     * Генерирует информацию про юзера, по его данным, в HTML-код для страницы отзывов (сдвоенный вывод)
     *
     * @global $session Сессия пользователя
     *
     * @param array   $user				Информация о юзере
     * @return string HTML-код
     */
    public static function printUserInfoMy($user, $direction = 'from') {
        global $session;
        $u_obj = $user;
        if (is_object($user))
            $user = get_object_vars($user);
        $is_emp = is_emp($user['role']);
        $login = $user['login'];
        $uname = ucfirst($user['uname']);
        $usurname = ucfirst($user['usurname']);
        $photo = $user['photo'];
        $dir = $user['login'];
        
        $on_site = "";
        if (hasPermissions('users')) {
            $on_site = "На сайте " . ElapsedMnths(strtotime($user['reg_date']));
            $on_site = "<i>{$on_site}</i>";
        }

        if (!in_array($direction, array('from', 'to')))
            $direction = 'from';
        $template = '<div class="ops-$direction">
		<a href="$href" class="$role-name">
                   <img src="$img" alt="" width="25" height="25" class="sav" />
		</a>$user_name ' . $on_site . '
	</div>';

        $role = $is_emp ? 'employer' : 'freelancer';
        $img = view_avatar($user['login'], $photo, 0, 0);
        if (!preg_match("/src\=(\'|\")([^\'\"]+)(\'|\")/i", $img, $res)) {
            $img = '/images/icons/f-pro.png';
        } else {
            $img = $res[2];
        }
        $href = "/users/{$login}/";
        $user_name = __prntUsrInfo($u_obj); // "{$uname} {$usurname} [{$login}]";

        $html = strtr($template, array(
                    '$direction' => $direction,
                    '$role' => $role,
                    '$img' => $img,
                    '$href' => $href,
                    '$user_name' => $user_name
                ));
        return $html;
    }

    /**
     * Функция возвращает html для формы редактирования определенного отзыва
     *
     * @param integer $op_id ID отзыва
     * @param string $from
     * @return string
     */
    public static function printEditOpForm($op_id, $from='frl') {
        $msg = opinions::GetMessageById($op_id);
        $html = '
            <div class="ops-add">
                <b class="b1"></b>
                <b class="b2"></b>
                <div class="ops-add-in">
                    <div class="ops-add-full ops-add-show">
                        <div class="form ops-form">
                            <div class="form-el" style="height:20px">
                                <label>Характер мнения:</label>
                                <input type="hidden" id="rating_edit_' . $msg['id'] . '" value="' . $msg['rating'] . '" />
                                <ul class="ops-type" id="ops-type">
                                    <li' . ((int) $msg['rating'] == 1 ? ' class="active"' : '') . '>
                                        <a rel="1" onclick="setRating(this, \'' . $msg['id'] . '\'); return false" href="#" class="lnk-dot-green">Положительный</a>
                                    </li>
                                    <li' . ((int) $msg['rating'] == 0 ? ' class="active"' : '') . '>
                                        <a rel="0" onclick="setRating(this, \'' . $msg['id'] . '\'); return false" href="#" class="lnk-dot-666">Нейтральный</a>
                                    </li>
                                    <li' . ((int) $msg['rating'] == -1 ? ' class="active"' : '') . '>
                                        <a rel="-1" onclick="setRating(this, \'' . $msg['id'] . '\'); return false" href="#" class="lnk-dot-red">Отрицательный</a>
                                    </li>
                                </ul>
                            </div>
                            <div class="form-el" style="font-size:10px">
                                <span class="no-more">Не более ' . self::$opinion_max_length . ' символов</span>
                                <textarea rows="5" id="edit_msg_' . $msg['id'] . '" cols="20" onkeydown="$(\'error_edit_msg_' . $msg['id'] . '\').set(\'html\', \'\');">' . $msg['msgtext'] . '</textarea>
                                <div id="error_edit_msg_' . $msg['id'] . '"></div>
                            </div>
                            <div class="form-btn">
                                <a href="javascript:void(0);" onclick="opinionSubmitEditForm(' . $msg['id'] . ', \'' . $from . '\'); return false" class="btnr btnr-t"><span class="btn-lc"><span class="btn-m"><span class="btn-txt">Выразить мнение</span></span></span></a>&nbsp;&nbsp;&nbsp;&nbsp;
                                <a href="javascript:void(0);" onclick="$(this).getParent(\'.ops-add\').setStyle(\'display\', \'none\'); $(\'message_text_' . $msg['id'] . '\').setStyle(\'display\', \'block\'); $(\'edit_block_' . $msg['id'] . '\').setStyle(\'display\', \'block\'); opinionCheckMaxLengthStop(\'edit_msg_' . $msg['id'] . '\');" class="lnk-dot-666">Отменить</a>
                            </div>
                        </div>
                    </div>
                </div>
                <b class="b2"></b>
                <b class="b1"></b>
            </div>';

        return $html;
    }

    /**
     * Функция возвращает html для формы редактирования коментария для определенного отзыва
     *
     * @param integer $op_id ID отзыва
     * @param string $from
     * @param bool $isFeedback если true значит отзыв, false - мнение
     * @return string
     */
    public static function printEditComForm($op_id, $from='frl', $isFeedback = false) {
        if ($isFeedback) {
            $opcomms = opinions::getCommentFeedbackById(array($op_id));
        } else {
            $opcomms = opinions::getCommentOpinionById(array($op_id));
        }
        $msg     = !empty($opcomms[$op_id]) ? $opcomms[$op_id] : false;
        $id      = $msg ? $msg['id'] : 0;
        $comment = $msg ? $msg['comment'] : '';
        $opinion_max_length = self::$opinion_max_length;
        
        ob_start();
        include($_SERVER['DOCUMENT_ROOT']."/user/opinions/comment-form.tpl.php");
        $html = ob_get_contents();
        ob_get_clean();
        
        return $html;
    }
    
    public static function printCommentOpinions($op_id, $isFeedback) {
        require_once ($_SERVER['DOCUMENT_ROOT']."/classes/users.php");
        
        if ($isFeedback) {
            $opcomm  = opinions::getCommentFeedbackById(array($op_id));
        } else {
            $opcomm  = opinions::getCommentOpinionById(array($op_id));
        }
        
        $comment = $opcomm[$op_id];
        $user = new users();
        $user->GetUserByUID($comment['user_id']);
        $aUser   = get_object_vars($user);
        
        ob_start();
        include($_SERVER['DOCUMENT_ROOT']."/user/opinions/comment.tpl.php");
        $html = ob_get_contents();
        ob_get_clean();
        
        return $html;
    }

    /**
     * Функция генерирует HTML для формы добавдения отзыва
     * 
     * @param integer $sid пользоваль, от которого будут осталяться сообщения
     * @param integer $uid пользоваль, которому будут осталяться сообщения
     * @param string $from (frl|emp|my|norisk)
     * @return string html формы
     * 
     * 
     * @deprecated #0015627
     */
    public static function printAddForm($sid, $uid, $from) {
        return false;
        @session_start();
        if (($sid == $uid) || in_array($from, array('my', 'norisk1')))
            return '';
        $can_post = 1;
        if ($sid)
            $can_post = opinions::CheckUserCanPost($sid, $uid);
        if ($from == 'norisk') {
            $can_post = 0;
        }
        $html = '';
        if (!$can_post) {
            if ($from == 'norisk') {// форма для СБР
                $html = '<div id="rating-tpl" style="display: none; float: right; margin: 0pt 0pt 0pt 25px; width: 270px;">
                        <p style="margin: 0 0 11px 0;">Пожалуйста оцените сотрудничество с фрилансером по трем критериям.</p>
                    </div>
                    <div id="message-tpl" style="margin: 0pt 295px 0pt 0pt; display:none;">
                        <form method="POST">
						<div>
                            <input type="hidden" name="id" value="" />
                            <input type="hidden" name="stage_id" value="" />
                            <input type="hidden" name="p_rate" value="" />
                            <input type="hidden" name="n_rate" value="" />
                            <input type="hidden" name="a_rate" value="" />
                            <input type="hidden" name="login" value="" />
                            <textarea name="to_user_feedback" rows="5" cols="20" style="width:95%;height:95px;" onkeydown="check_length(this)"></textarea>
                            <div class="errorBox" style="display:none;">
                                <img width="22" height="18" src="/images/ico_error.gif" alt="" />
                                <span></span>
                            </div>
                            <div style="padding: 5px 0 0 0;">
                                <input type="button" value="Сохрaнить" style="overflow: visible; padding: 0 10px; font-weight: 900; color: #333;" onclick="saveRating()" />
                                <input type="button" value="Отменить" style="overflow: visible; padding: 0 10px; color: #333;" onclick="closeForm()" />
                            </div>
							</div>
                        </form>
                    </div>';
            } else { // Форма для простого отзыва
                $html .= '
<form action="" method="post" name="frm_add" id="frm_add">
<div>
                        <input type="hidden" name="r" value="' . $_SESSION['rand'] . '" />
                        <input type="hidden" name="rating" value="" id="rating_add" />
                        <input type="hidden" name="action" id="action_com" value="new" />

                        <div class="ops-add-in">
                            <a class="btn btn-green3 ops-frm-toggler" href="javascript:void(0);" onclick="showOpinionsForm(this)" ><span class="btn-lc"><span class="btn-m"><span class="btn-txt">Выразить мнение</span></span></span></a>
                            <div class="ops-add-full " id="add_form_cont">
                                <p>Если вам приходилось работать с этим человеком, вы можете оставить мнение о нем как о личности и профессионале. Пожалуйста, внимательно отнеситесь к этому полю, так как удалить или изменить мнение сможете только вы. Помните, что своими словами вы влияете на профессиональную репутацию пользователя.</p>
                                <div class="form ops-form">
                                    <div class="form-el" style="height:20px">
                                        <label>Характер мнения:</label>
                                        <ul class="ops-type" id="ops-type">
                                            <li>
												<a rel="1" onclick="setRating(this); return false" href="#" class="lnk-dot-green">Положительный</a>
											</li>
											<li>
												<a rel="0" onclick="setRating(this); return false" href="#" class="lnk-dot-666">Нейтральный</a>
											</li>
											<li>
												<a rel="-1" onclick="setRating(this); return false" href="#" class="lnk-dot-red">Отрицательный</a>
											</li>
                                        </ul>
                                    </div>
                                    <div class="form-el"  style="font-size:10px">
                                        <span class="no-more">Не более ' . self::$opinion_max_length . ' символов</span>
                                        <textarea rows="5" cols="20" id="msg" name="msg" onkeydown="$(\'error_msg\').set(\'html\', \'\');"></textarea>
                                        <div id="error_msg"></div>
                                    </div>
                                    <div id="rating_error" style="display:hidden"></div>
                                                    <div class="form-btn">
                                                        <a href="javascript:void(0);" id="btn-send-opinions" class="btnr btnr-t" onclick="opinionSubmitAddForm(' . $sid . ', ' . $uid . ', \'' . $from . '\'); return false;"><span class="btn-lc"><span class="btn-m"><span class="btn-txt" id="btn">Выразить мнение</span></span></span></a>&nbsp;&nbsp;&nbsp;&nbsp;
                                                        <a href="javascript:void(0);" onclick="hideOpinionsForm(this)" class="lnk-dot-666">Отменить</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
										</div>
                                    </form>';
            }
            return '<div class="ops-add" id="form_container">' . $html . '</div>';
        } elseif ($can_post != 0 && $from != 'my') {
            $deny_text = "Вы не можете оставить мнение об этом пользователе:";
            $html = '
                <div class="ops-add-in"  style="font-size:12px">';
            switch ($can_post) {
                case 1:
                    $html .= view_error($deny_text.' Вы не <a href="/registration/">зарегистрированы</a> или не авторизованы.');
                    break;
                case 2:
                    $html .= view_error($deny_text.' С момента регистрации Вашего аккаунта прошло меньше месяца.');
                    break;
                case 3:
                    $html .= view_error('Вы уже выразили свое мнение о данном пользователе.');
                    break;
            }
            $html .= '</div>';
            if ($can_post == 3 && (($from == 'frl' && is_emp()) || ($from == 'emp' && !is_emp()))) {
                return '';
            }
            return '<div class="ops-add" id="form_container">' . $html . '</div>';
        }
        return false;
    }
    
    /**
     * Возвращает HTML код формы отзыва с СБР
     *
     * @param  int $op_id ID отзыва
     * @param  string $mode не используется
     * @return unknown
     */
    public static function getEditSBREditForm($op_id, $login_user=NULL, $mode = 'f'){
        session_start();
        global $DB;
        include_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr.php';
        $op_id = intval($op_id);
        $sql = "SELECT sbr_feedbacks.*, sbr_stages.id as stage_id, sbr_stages.name as stage_name, sbr.name as sbr_name FROM 
        sbr_feedbacks
        JOIN 
        sbr_stages ON 
        (sbr_stages.emp_feedback_id = sbr_feedbacks.id OR sbr_stages.frl_feedback_id = sbr_feedbacks.id)
        JOIN sbr ON
        (sbr.id = sbr_stages.sbr_id)
        WHERE sbr_feedbacks.id = ?i";
        if(!($op = $DB->row($sql, $op_id))) {
            return NULL;
        }
        
        $sbr_home = $_SERVER['DOCUMENT_ROOT'].'/norisk2';
        $ele_id = 'form_container_'.$op_id;
        $link_id = 'ops_edit_link_'.$op_id;
        $text_id = 'op_message_'.$op_id;
        ob_start();
        include_once($sbr_home.'/tpl.opinion_form.php');
        $html = ob_get_clean();
        return $html;
    }
    
    
    /**
     * @deprecated https://beta.free-lance.ru/mantis/view.php?id=29288
     * 
     * Печатает элемент вкладки в шапке страницы отзывов.
     * @param string $type   тип отзыва (см. ниже).
     * @param object $user   юзер, на странице которого находимся.
     * @param int    $sort   текущий параметр группировки отзывов (1:только положительные; 2:нейтральные; 3:отрицательные)
     * @param int    $period текущий параметр периода, за который выводить отзывы (0-4:все, год, полгода, месяц).
     * @param boolean $is_active   является ли вкладка активной.
     * @return string
     */
    static function view_op_header_item($type, $user, $sort = 0, $period = 0, $is_active = false) {
        
        switch($type) {
            case 'norisk' : $title = $user->is_pro == 't'? (is_emp($user->role) ? 'Рекомендации фрилансеров' : 'Рекомендации работодателей'): 'Рекомендации'; $cls = 'first'; break;
            case 'total'  : $title = $user->is_pro == 't'? (is_emp($user->role) ? 'Отзывы фрилансеров' : 'Отзывы работодателей'): 'Отзывы'; $cls = 'first'; break;
            case 'emp'    :
            case 'frl'    :
            case 'users'  : $title = 'Мнения пользователей'; $cls = 'last'; break; 
        }
        if($is_active) {
            $aL = '<span class="a"><span><span>';
            $aR = '</span></span></span>';
            $cls .= ' a';
        } else {
            $aL = '<strong>';
            $tR = '</strong>';
        }
        
        if(!$is_active || $sort > 0) {
            $aL .= '<a href="/users/'.$user->login.'/opinions/?from='.$type.'&period='.$period.'#op_head">';
            $tR = '</a>'.$tR;
        }
        
        $tmp = opinions::GetCounts($user->uid, array($type));
        $ops = array('plus'=>$tmp[$type]['p'], 'neitral'=>$tmp[$type]['n'], 'minus'=>$tmp[$type]['m']);
        
        include($_SERVER['DOCUMENT_ROOT'].'/user/tpl.op_header-item.php');
    }
    
    /**
     * печатает навигационную панель страницы отзывов
     * @param users $user объект класса users с данными о пользователе
     */
    static function view_op_nav_bar ($user, $sort) {
        global $filter_string, $author_filter_string, $period, $author, $filterCounts;
        $opinions = opinions::GetCounts($user->uid, array('total'));
        $opinionsTotal = $opinions['total'];
        $opinionsAll = $opinionsTotal['p'] + $opinionsTotal['n'] + $opinionsTotal['m'];
        $opinionsLink = '/users/' . $user->login . '/opinions/';
        $authorFilterLinkParams = "?sort=$sort&period=$period";
        $periodFilterLinkParams = "?sort=$sort&author=$author";
        $ratingFilterLinkParams = "?period=$period&author=$author";
        
        include($_SERVER['DOCUMENT_ROOT'] . '/user/tpl.op_nav_bar.php');
    }
    
    /**
     * Конвертируем данные из мнения в данные рекомендации
     * 
     * @param array $opinion Мнение
     * @return boolean|array  
     */
    static public function converOpinion2Advice($opinion) {
        if($opinion['rating'] != 1) return false;
        include_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
        
        $user_from = new users();
        $user_from->GetUserByUID($opinion['fromuser_id']);
        $user_from = get_object_vars($user_from);
        $user_to = get_uid(false);
        
        if(is_emp($user_from['role']) == is_emp($_SESSION['role'])) {
            return false;
        }
        
        $advice = array(
            "oid"         => $opinion['id'],
            "user_from"   => $user_from['uid'],
            "user_to"     => $user_to,
            "msgtext"     => $opinion['msgtext'],
            "create_date" => $opinion['post_time'],
            "login"       => $user_from['login'],
            "role"        => $user_from['role'],
            "photo"       => $user_from['photo'],
            "uname"       => $user_from['uname'],
            "usurname"    => $user_from['usurname'],
            "is_pro"      => $user_from['is_pro'],
            "is_team"     => $user_from['is_team'],
            "is_pro_test" => $user_from['is_pto_test']
        );
        
        return $advice;
    }
    
    /**
     * Задаем мнению статуса конвертирования
     * 
     * @global DB $DB
     * @param integer $opinion_id  ИД мнения
     * @param boolean $convert     Статус конвертирования null|true  
     * @return boolean 
     */
    static public function setConvertOpinion($opinion_id, $convert=true) {
        global $DB;
        if((int)$opinion_id <= 0) return false;
        
        $data = array(
            "is_converted" => $convert
            //"active"       => !$convert
        );
        return $DB->update("opinions", $data, "id = ?", $opinion_id);
    }
    
}

?>
