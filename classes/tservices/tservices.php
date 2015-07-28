<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/admin_log.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/messages.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/functions.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/atservices_model.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/tservices_tags.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/account.php');

/**
 * Модель типовых услуг
 *
 */
class tservices extends atservices_model
{
    private $TABLE                  = 'tservices';
    public static $_TABLE           = 'tservices';
    private $TABLE_BLOCKED          = 'tservices_blocked';
    private $TABLE_CATEGORIES       = 'tservices_categories';
    private $TABLE_USERS            = 'users';
    private $TABLE_FREELANCER       = 'freelancer';
    private $TABLE_COUNTERS         = 'tservices_counters';
    private $TABLE_FILES            = 'file_tservices';
    private $TABLE_SBR_FEEDBACKS    = 'sbr_feedbacks';
    private $TABLE_SBR_STAGES       = 'sbr_stages';
    private $TABLE_SBR              = 'sbr';
    private $TABLE_TSERVICES_SBR    = 'tservices_sbr';
    private $TABLE_EMPLOYER         = 'employer';
    private $TABLE_COUNTRY          = 'country';
    private $TABLE_CITY             = 'city';
    private $TABLE_MODERATION       = 'moderation';
    private $TABLE_ATTACHEDFILES    = 'attachedfiles';
    private $TABLE_BINDS            = 'tservices_binds';




    /**
     * ID пользователя
     * @var int
     */
    private $uid;


    /**
     * Массив полей записи
     * 
     * @var array
     */
    private $_default_fields_schema;

    
    /**
     * Массив свойств связных с записью
     * 
     * @var array 
     */
    //private $_props_schema;

    
    /**
     * Свойства требующие сериализации/десериализации
     * 
     * @var array 
     */
    private $_serialized_fields;    
    
    
    
    /**
     * Выставляется некоторыми методами 
     * при наличии отрицательных отзывов
     * 
     * @var boolean 
     */
    public $is_angry = FALSE;


    
    
    
    
    /**
     * Конструктор
     * 
     * @param type $uid
     */
    public function __construct($uid = 0) 
    {
        $this->uid = $uid;
        $this->initProps();
    }   
    
    
    /**
     * Помечает последний элемент массива
     * 
     * @todo Возможно здесь не пригодится.
     * 
     * @param array $rows
     */
    private function _is_last(&$rows)
    {
        $cnt = count($rows);

        if($cnt > 0 && $cnt < $this->limit)
        {
            $rows[$cnt - 1]['is_last'] = TRUE;
        }
    }    
    
    
    
    /**
     * Есть ли отзывы по ТУ и сколько
     * 
     * @param int $service_id
     * @return int
     */
    public function isExistFeedbacks($service_id)
    {
        return $this->db()->val("
            SELECT 
                (sbr_null + sbr_plus + sbr_minus)
            FROM {$this->TABLE_COUNTERS} 
            WHERE service_id = ?i 
            LIMIT 1
        ",$service_id);
    }    
    
    
    
    /**
     * Всего отзывов у юзера
     * 
     * @return array
     */
    public function getTotalCount()
    {
        $row = $this->db()->row("
            SELECT 
                SUM(sc.sbr_null + sc.sbr_plus + sc.order_plus) AS plus, 
                SUM(sc.sbr_minus + sc.order_minus) AS minus
            FROM {$this->TABLE_COUNTERS} AS sc 
            INNER JOIN {$this->TABLE} AS s ON s.id = sc.service_id 
            WHERE s.user_id = ?i 
            LIMIT 1
        ",$this->uid);
        
         return $row;
    }    
    
    
    
    
    /**
     * Услуги рядом
     * 
     * @param string $type
     * @param int $current_id
     * @return array
     */
    public function getNearBy($type = 'next', $current_id)
    {
        $where = '';
        $order = '';
        
        switch($type)
        {
            case 'next': 
                $where = 's.id > ?i';
                $order = 's.id';
                break;
            case 'prev':
                $where = 's.id < ?i';
                $order = 's.id DESC';
                break;
        }
        
        $row = $this->db()->row("
            SELECT
                s.id,
                s.title
            FROM {$this->TABLE} AS s 
            LEFT JOIN {$this->TABLE_BLOCKED} AS sb ON sb.src_id = s.id  
            LEFT JOIN {$this->TABLE_USERS} AS u ON u.uid = s.user_id 
            WHERE 
                {$where} AND
                s.user_id = ?i AND 
                s.active = TRUE AND 
                s.deleted = FALSE AND 
                u.is_banned = B'0' AND 
                u.self_deleted = FALSE AND 
                sb.src_id IS NULL 
            ORDER BY {$order} 
            LIMIT 1",
            $current_id,
            $this->uid);   
            
        return $row;    
    }    
    
    
    
    
    /**
     * Количество завершенных ТУ по СБ
     * 
     * @todo Переработать для новой СБ
     * 
     * @return int
     */
    public function getCountCompleteSbrServices()
    {
        $cnt = $this->db()->val("
            SELECT 
                COUNT(s.id) 
            FROM {$this->TABLE_SBR} AS s 
            INNER JOIN {$this->TABLE_TSERVICES_SBR} AS ts ON ts.sbr_id = s.id 
            WHERE
                s.frl_id = ?i AND
                s.is_draft = '0' AND 
                s.status = 700 
        ",$this->uid);

        return $cnt;
    }    
    
    
    
    
    /**
     * Удаление типовой услуги по ID
     * доступ и наличие долны быть проверены перед вызовом
     * 
     * @param int $id
     * @return boolean
     */
    public function deleteById($id)
    {
        $files = $this->db()->col("
            SELECT id  
            FROM {$this->TABLE_FILES} 
            WHERE src_id = ?i
        ",
        $id);

        if(count($files))
        {
            $cfile = new cFile();
            foreach($files as $file_id)
            {
                $cfile->Delete($file_id);
            }
        }

        $this->db()->query("
            DELETE FROM {$this->TABLE} 
            WHERE id = ?i AND user_id = ?i
        ",$id,$this->uid);
        
        return true;
    }    
    
    
    
    /**
     * Существует ли у пользователя 
     * запись с таким ID
     * 
     * @param int $id
     * @return row
     */
    public function isExists($id)
    {
        return $this->db()->row("
            SELECT 
                s.id, 
                s.title, 
                s.active::int,
                COALESCE((sc.sbr_minus + sc.order_minus),0) AS minus_feedbacks
            FROM {$this->TABLE} AS s  
            LEFT JOIN {$this->TABLE_COUNTERS} AS sc ON sc.service_id = s.id
            WHERE s.deleted = FALSE AND s.id = ?i AND s.user_id = ?i 
            LIMIT 1",
            $id,
            $this->uid);
    }    
    
    
    /**
     * Есть ли у пользователя указанная ТУ
     * Должна быть не забаненой / не удаленной и активной
     */
    public function isExistActive($id)
    {
        return $this->db()->val("
            SELECT 1
            FROM {$this->TABLE} AS s 
            LEFT JOIN {$this->TABLE_BLOCKED} AS sb ON sb.src_id = s.id
            LEFT JOIN tservices_orders_debt AS od ON od.user_id = s.user_id     
            WHERE 
                s.id = ?i 
                AND s.user_id = ?i 
                AND s.deleted = FALSE 
                AND s.active = TRUE 
                AND sb.src_id IS NULL 
                AND (od.id IS NULL OR od.date >= NOW())
            LIMIT 1
         ", $id, $this->uid);   
    }

    



    /**
     * Переключаем доступность 
     * типовой услуги для публики
     * 
     * @param int $id
     * @return type
     */
    public function switchActive($id)
    {
        return $this->db()->query("
            UPDATE {$this->TABLE} 
            SET active = NOT active 
            WHERE id = ?i AND user_id = ?i
        ",
        $id,
        $this->uid);
    }    
    
    
    
    
    /**
     * Получить отзывы
     * 
     * @param int $id
     * @return array
     */
    public function getFeedbacks($id = 0)
    {
        //if(!$id) $id = $this->property()->get('id');
        // data???
        
        $sql = $this->db()->parse("
            SELECT 
                0 AS type,
                e.login,
                e.uname,
                e.usurname,
                e.photo,
                e.is_pro,
                e.is_verify,
                e.is_team,
                e.country,
                e.city,
                co.country_name,
                ci.city_name,
                fb.id::text,
                fb.descr,
                fb.posted_time,
                fb.rating,
                sr.cost
            FROM {$this->TABLE_SBR_FEEDBACKS} AS fb 
            INNER JOIN {$this->TABLE_SBR_STAGES} AS ss ON ss.emp_feedback_id = fb.id 
            INNER JOIN {$this->TABLE_SBR} AS sr ON sr.id = ss.sbr_id 
            INNER JOIN {$this->TABLE_TSERVICES_SBR} AS ts ON (ts.stage_id = ss.id AND ts.service_id = {$id})
            INNER JOIN {$this->TABLE_EMPLOYER} AS e ON (e.uid = sr.emp_id AND e.is_banned = B'0' AND e.self_deleted = FALSE) 
            LEFT JOIN {$this->TABLE_COUNTRY} AS co ON co.id = e.country 
            LEFT JOIN {$this->TABLE_CITY} AS ci ON ci.id = e.city 
            -- можно сделать проверку на существование типовой услуги раньше
            -- тогда join и с фрилансером отпадает
            INNER JOIN {$this->TABLE} AS s ON (s.id = ts.service_id AND s.active = TRUE AND s.deleted = FALSE) 
            LEFT JOIN {$this->TABLE_BLOCKED} AS sb ON sb.src_id = s.id
            INNER JOIN {$this->TABLE_FREELANCER} AS f ON (f.uid = s.user_id AND f.is_banned = B'0' AND f.self_deleted = FALSE) 
            WHERE 
                sb.src_id IS NULL 
                

            UNION ALL


            SELECT 
                1 AS type,
                e.login,
                e.uname,
                e.usurname,
                e.photo,
                e.is_pro,
                e.is_verify,
                e.is_team,
                e.country,
                e.city,
                co.country_name,
                ci.city_name,
                (fb.id || '-1') AS id,
                fb.feedback AS descr,
                fb.posted_time,
                fb.rating, 
                o.order_price AS cost
          FROM tservices_orders_feedbacks AS fb 
          INNER JOIN tservices_orders AS o ON o.emp_feedback_id = fb.id
          INNER JOIN {$this->TABLE_EMPLOYER} AS e ON (e.uid = o.emp_id AND e.is_banned = B'0' AND e.self_deleted = FALSE) 
          LEFT JOIN {$this->TABLE_COUNTRY} AS co ON co.id = e.country 
          LEFT JOIN {$this->TABLE_CITY} AS ci ON ci.id = e.city 
          WHERE
              fb.deleted = FALSE
              AND o.tu_id = {$id}
          ORDER BY posted_time DESC, id DESC 
        ");
            
        $sql = $this->_limit($sql);
        $rows = $this->db()->rows($sql);  
        $this->_is_last($rows); //???

        return $rows;
    }    
    
    
    
    /**
     * Получаем карточку ТУ с нужными полями
     * для передачи на создание заказа
     * 
     * @param int $id - ID ТУ
     * @return boolean|array
     */
    public function getCardForOrder($id)
    {
        $sql = "
            SELECT 
                s.id AS tu_id, -- ID ТУ именуем как в таблице tservices_orders
                s.user_id AS frl_id, -- ID фрилансера именуем как в таблице tservices_orders
                s.title,
                s.price,
                s.days,
                s.category_id,
                s.description,
                s.requirement,
                s.extra,
                s.is_express,
                s.express_price,
                s.express_days,
                s.is_meet,
                s.city
            FROM {$this->TABLE} AS s 
            LEFT JOIN {$this->TABLE_BLOCKED} AS sb ON sb.src_id = s.id  
            LEFT JOIN {$this->TABLE_USERS} AS u ON u.uid = s.user_id 
            WHERE 
                sb.src_id IS NULL AND 
                s.active = TRUE AND 
                s.deleted = FALSE AND 
                u.is_banned = B'0' AND 
                u.self_deleted = FALSE AND 
                s.id = ?i
            LIMIT 1"; 
        
        $row = $this->db()->row($sql, $id);  

        if($row)
        {
            $row['extra'] = mb_unserialize($row['extra']);
            return $row;
        }    
        
        return FALSE;
    }


    /**
     * Получить карточку услуги по ID
     * 
     * @todo: не учитывается active в категориях
     * @todo: возможно не лучший способ получения категории
     * 
     * @param int $id
     * @return array
     */
    public function getCard($id, $is_public = true)
    {
        $sql = $this->db()->parse("
            SELECT 
                s.*, 
                -- u.uid, 
                -- u.login, 
                -- u.uname, 
                -- u.usurname, 
                -- u.email,
                -- u.icq,
                -- u.skype,
                -- u.photo, 
                -- u.photosm, 
                -- u.is_pro, 
                -- u.warn, 
                -- u.role, 
                -- u.is_banned, 
                -- u.ban_where, 
                -- u.is_team, 
                -- u.reg_date, 
                -- u.modified_time, 
                -- u.is_verify, 
                -- u.self_deleted, 
                -- u.photo_modified_time, 
                c1.title AS category_spec_title,
                c1.link AS category_spec_link,
                c2.title AS category_group_title,
                c2.link AS category_group_link,
                COALESCE((sc.sbr_null + sc.sbr_plus + sc.sbr_minus + sc.order_plus + sc.order_minus),0) AS total_feedbacks,
                COALESCE((sc.sbr_null + sc.sbr_plus + sc.order_plus),0) AS plus_feedbacks,
                COALESCE((sc.sbr_minus + sc.order_minus),0) AS minus_feedbacks, 
                -- ( (sc.sbr_plus * 100)/(sc.sbr_null + sc.sbr_plus + sc.sbr_minus) ) AS perplus_feedbacks
                COALESCE(sb.src_id::boolean, FALSE) AS is_blocked 
            FROM {$this->TABLE} AS s 
            LEFT JOIN {$this->TABLE_BLOCKED} AS sb ON sb.src_id = s.id  
            LEFT JOIN {$this->TABLE_USERS} AS u ON u.uid = s.user_id 
            LEFT JOIN {$this->TABLE_CATEGORIES} AS c1 ON c1.id = s.category_id 
            LEFT JOIN {$this->TABLE_CATEGORIES} AS c2 ON c2.id = c1.parent_id 
            LEFT JOIN {$this->TABLE_COUNTERS} AS sc ON sc.service_id = s.id 
            WHERE 
                " . ($is_public?"s.active = TRUE AND":"") . " 
                s.deleted = FALSE AND 
                s.id = ?i AND 
                u.is_banned = B'0' AND 
                u.self_deleted = FALSE
            LIMIT 1", 
            $id
        );

        $row = $this->db()->row($sql);
        
        if($row)
        {
            $row['extra'] = mb_unserialize($row['extra']);
            $row['videos'] = mb_unserialize($row['videos']);
            $row['images'] = CFile::selectFilesBySrc($this->TABLE_FILES, $id, 'id','small = 0 AND preview=\'f\'');
            return $row;
        }
        
        return false;
    }
    
    
    /**
     * Получаем услугу, принадлежащую текущему пользователю, по ID и
     * заполняем свойства обьекта
     * 
     * @param type $id
     * @return boolean
     */
    public function getByID($id) 
    {
        $row = $this->db()->row("
            SELECT 
                s.*,
                COALESCE((sc.sbr_minus + sc.order_minus),0) AS minus_feedbacks
            FROM {$this->TABLE} AS s 
            LEFT JOIN {$this->TABLE_COUNTERS} AS sc ON sc.service_id = s.id
            WHERE 
                s.deleted = FALSE 
                AND s.user_id = ?i 
                AND s.id = ?i", 
            $this->uid, 
            $id);
        
        
        if($this->arrayToFieldsProps($row))
        {
            $tservices_tags = new tservices_tags();
            $this->tags = $tservices_tags->getsByTServiceId($id);
            $this->images = CFile::selectFilesBySrc($this->TABLE_FILES, $id, 'id','small = 1 AND preview=\'f\'');
            $this->preview = CFile::selectFilesBySrc($this->TABLE_FILES, $id, 'id','small = 1 AND preview=\'t\'');
            $this->is_angry = ($row['minus_feedbacks'] > 0);
            return true;
        }
        
        return false; 
    }    
    
    
    
    /**
     * Список типовых услуг юзера с основной информацией
     * и картинкой. Если $is_public = TRUE только публичные
     * иначе все.
     * 
     * @param bool $is_public
     * @return array
     */
    public function getShortList($is_public = true)
    {
        $sql = $this->db()->parse("
            SELECT 
                DISTINCT ON (s.id) 
                s.id AS id, 
                s.title AS title, 
                s.price AS price,
                s.active AS active,
                s.videos AS videos,
                -- f.path||f.fname AS file,
                f.fname AS file,
                COALESCE(sb.src_id::boolean, FALSE) AS is_blocked,
                sb.reason
            FROM {$this->TABLE} AS s 
            LEFT JOIN {$this->TABLE_BLOCKED} AS sb ON sb.src_id = s.id 
            LEFT JOIN {$this->TABLE_FILES} AS f ON f.src_id = s.id AND f.small = 4 
            " . ($is_public? "LEFT JOIN tservices_orders_debt AS od ON od.user_id = s.user_id":"") . "  
            WHERE 
                s.user_id = ?i 
                AND s.deleted = FALSE ".($is_public?" 
                AND s.active = TRUE 
                AND sb.src_id IS NULL
                AND (od.id IS NULL OR od.date >= NOW()) 
            ":"")."
            ORDER BY s.id DESC, f.preview DESC, f.id 
         ", $this->uid);        
        
         $sql = $this->_limit($sql);
         $rows = $this->db()->rows($sql);
         $this->_is_last($rows);
         
         return $rows;
    }    
    
    
    /**
     * Кол-во ТУ в зависимости от вида видимости
     * 
     * @param bool $is_public
     * @return int
     */
    public function getCount($is_public = true)
    {
        $sql = $this->db()->parse("
            SELECT COUNT(*) 
            FROM {$this->TABLE} AS s 
            LEFT JOIN {$this->TABLE_BLOCKED} AS sb ON sb.src_id = s.id 
            " . ($is_public? "LEFT JOIN tservices_orders_debt AS od ON od.user_id = s.user_id":"") . "  
            WHERE 
                s.user_id = ?i 
                AND s.deleted = FALSE ".($is_public?" 
                AND s.active = TRUE 
                AND sb.src_id IS NULL 
                AND (od.id IS NULL OR od.date >= NOW()) 
            ":"")."
         ", $this->uid);        
            
         return (int)$this->db()->val($sql);
    }    
    
    
    
    /**
     * Обновление типовой услуги
     * 
     * @param type $data
     */
    public function update($id, $data = array())
    {
        $data = $this->fieldsPropsToArray($data);
        
        if($this->db()->update($this->TABLE,  $data, 'id = ?i AND user_id = ?i', $id, $this->uid))
        {
            $tservices_tags = new tservices_tags();
            $tservices_tags->updateByTServiceId($id,$this->category_id,$this->tags);
            //$this->UnBlocked($id);
            $moderation_data = $this->getCardModeration($id);
            $status = $moderation_data['is_blocked'] == 't' ? 2 : 0;
            $this->sendToModeration($id, $data, $status);
            return true;
        }

        return false;
    }    
    
    

    /**
     * Создать типовую услугу
     * 
     * @param type $data
     */
    public function create($data = array()) 
    {
        $data = $this->fieldsPropsToArray($data);
        
        $id = $this->db()->insert($this->TABLE, $data, 'id');
                
        if($id > 0)
        {
            $tservices_tags = new tservices_tags();
            $tservices_tags->updateByTServiceId($id,$data['category_id'],$this->tags);
            $this->sendToModeration($id, $data);
        }
        
        return $id;
    }    
    
 
    
    /**
     * Необходимые преобразование перед вставкой/обновлением БД
     * 
     * @param array $data
     * @return type
     */
    protected function beforeDb(Array $data) 
    {
        //Фиксируем заголовки из доп.полей для индексации поиска sphinx
        if (isset($this->extra) && !empty($this->extra)) {
            $extra_title = implode(', ',array_map(function($a) {return $a['title'];}, $this->extra));
            if (!empty($extra_title)) {
                $data['extra_title'] = $extra_title;
            }
        }
        
        return $data;
    }



    /**
     * Отправить услугу на модерацию
     * 
     * @param int $id
     * @param array $data
     */
    public function sendToModeration($id, $data, $status = 0)
    {
        $stop_words    = new stop_words();
        $nStopWordsCnt = $stop_words->calculate($data['title'],$data['description'],$data['requirement']);
        
        $this->db()->insert($this->TABLE_MODERATION,array(
            'rec_id' => $id,
            'rec_type' => 22,//ID в admin_contents - сущность для модерирования
            'stop_words_cnt' => $nStopWordsCnt,
            'status' => $status
        ));        
    }     
    
    
    
    /**
     * Разблокирование ТУ
     * 
     * @param int $id
     * @return array()
     */
    public function unBlocked($id, $user_id)
    {
        $sQuery = "
             UPDATE {$this->TABLE_MODERATION} SET 
                 status = 1 
             WHERE 
                rec_id = ?i 
                AND rec_type = ?i 
             RETURNING rec_id";
             
        $this->db()->val($sQuery, $id, user_content::MODER_TSERVICES);
        
        $data = $this->db()->row("
                SELECT
                s.id,
                s.title,
                u.uid,
                u.login, 
                u.uname,
                u.usurname,
                COALESCE(sb.src_id::boolean, FALSE) AS is_blocked 
            FROM {$this->TABLE} AS s 
            LEFT JOIN {$this->TABLE_BLOCKED} AS sb ON sb.src_id = s.id  
            INNER JOIN {$this->TABLE_FREELANCER} AS u ON u.uid = s.user_id 
            WHERE s.id = ?i 
            LIMIT 1
            ",$id);
       
        if(!$data) return FALSE;
        if($data['is_blocked'] == 'f') return TRUE;        

        $this->db()->query("
            DELETE FROM {$this->TABLE_BLOCKED} 
            WHERE src_id = ?i
        ",$id);
            
        $sObjLink = sprintf('%s/tu/%d/%s.html',$GLOBALS['host'],$data['id'],translit(strtolower(htmlspecialchars_decode($data['title'], ENT_QUOTES))));
        //пишем лог админских действий
        admin_log::addLog(admin_log::OBJ_CODE_TSERVICES, 65, $data['uid'], $id, $data['title'], $sObjLink, 0, '', 0, '', 0, '', $user_id);      
            
        return TRUE;
    }

    
    
    
    public function Blocked($id, $user_id, $reason, $reason_id = 0)
    {
        $data = $this->getCardModeration($id);
       
        if (!$data) {
            return FALSE;
        }
        
        if ($data['is_blocked'] == 't') {
            return TRUE;
        }
        
        
        $sQuery = "
             UPDATE {$this->TABLE_MODERATION} SET 
                 status = 2 
             WHERE 
                rec_id = ?i 
                AND rec_type = ?i 
             RETURNING rec_id";

        $sRecId = $this->db()->val($sQuery, $id, user_content::MODER_TSERVICES);
        
        //Если вдруг записи нет на модерации добавляем
        if(!$sRecId) {
            $this->sendToModeration($id, $data, 2);
        }

        
        $sBlockId = $this->db()->insert($this->TABLE_BLOCKED, array(
            'src_id' => $id,
            'admin' => $user_id,
            'reason' => $reason,
            'reason_id' => $reason_id,
            'blocked_time' => 'NOW()'
        ),'id');
        
        if(!$sBlockId) {
            return FALSE;
        }
        
        $sObjLink = sprintf('%s/tu/%d/%s.html',$GLOBALS['host'],$data['id'],translit(strtolower(htmlspecialchars_decode($data['title'], ENT_QUOTES))));
        //пишем лог админских действий
        admin_log::addLog(admin_log::OBJ_CODE_TSERVICES, 64, $data['uid'], $id, $data['title'], $sObjLink, 0, '', 0, $reason, $sBlockId, '', $user_id);  
        //отправляем сообщение о блокировки
        messages::SendBlockedTServices($data, $reason);
        
        return TRUE;
    }

    /**
     * Получает карточку услуги с данными о модерации
     * @param type $id
     * @return type
     */
    private function getCardModeration($id) 
    {
        return $this->db()->row("
                SELECT
                s.id,
                s.title,
                s.description,
                s.requirement,
                u.uid,
                u.login, 
                u.uname,
                u.usurname,
                COALESCE(sb.src_id::boolean, FALSE) AS is_blocked 
            FROM {$this->TABLE} AS s 
            LEFT JOIN {$this->TABLE_BLOCKED} AS sb ON sb.src_id = s.id  
            INNER JOIN {$this->TABLE_FREELANCER} AS u ON u.uid = s.user_id 
            WHERE s.id = ?i 
            LIMIT 1
            ",$id);
    }



    /**
     * Связываем с записью
     * TODO: нет обновления сортировки возможно сделать ее при загрузке?
     * 
     * @param string $sess
     * @param int $id
     * @param bool $preview
     * @return boolean
     */
    public function addAttachedFiles($sess, $id, $clear = false)
    {
        $file_ids = $this->db()->col("
            SELECT file_id 
            FROM {$this->TABLE_ATTACHEDFILES} 
            WHERE session = ? AND status IN (?l) 
            ORDER BY file_id ASC",
            $sess,
            array(1,3));
        
        if(count($file_ids))
        {
            //Если загружаем превью, то удалить ранее загруженные превью
            if ($clear) $this->clearOldPreview($id);
            
            $res = $this->db()->update($this->TABLE_FILES,  array('src_id' => $id), 'id IN(?l)', $file_ids);
            if(!$res) return false;
            $this->db()->query("
                DELETE FROM {$this->TABLE_ATTACHEDFILES}  
                WHERE session = ?", 
                $sess);
        }
        
        return true;
    }    
    
    
    
    /**
     * Массив в свойства обьекта
     * 
     * @param type $data
     * @return boolean
     */
    public function arrayToFieldsProps($data = array())
    {
        if(!count($data)) return false;
        
        foreach($data as $key => $value)
        {
            if(in_array($key,$this->_default_fields_schema))
            {
                if(in_array($key, $this->_serialized_fields) && $value) 
                {
                    $value = mb_unserialize($value);
                }
                
                $this->{$key} = $value;
            }
        }
        
        return true;
    }    
    
    
    
    
    /**
     * Свойства обьекта в массив
     * 
     * @param array $fields
     * @return array
     */
    public function fieldsPropsToArray($fields = array())
    {
        $fields = $this->beforeDb($fields);
        
        $data = array();
        foreach($this->_default_fields_schema as $key)
        {
            $value = $this->{$key};
            
            if(in_array($key, $this->_serialized_fields) && $value) 
            {
                $value = serialize($value);
            }
            
            $data[$key] = $value;
        }
        
        $data = array_merge($data,$fields);
        
        return $data;
    }    
    
    
    
    
    /**
     * Инициализация свойств обьекта
     * 
     * @param array $props
     * @return array
     */
    public function initProps($props = array())
    {
        //@todo: лучше получить из базы? describe?
        $_default_fields = array(
            'user_id' => $this->uid,
            'title' => '',
            'price' => '',
            'days' => 1,
            'category_id' => 0,
            'description' => '',
            'requirement' => '',
            'videos' => NULL,
            'extra' => NULL,
            'is_express' => 'f',
            'express_price' => 0,
            'express_days' => 1,
            'is_meet' => 'f',
            'city' => 0,
            'agree' => 'f',
            'active' => 't',
            'deleted' => 'f'
        );
        
        $this->_default_fields_schema = array_keys($_default_fields);
        
        $_default_props = array(
            'tags' => array(),
            'images' => array(),
        );
        
        //@todo: а зачем мне они?
        $this->_props_schema = array_keys($_default_props);

        $this->_serialized_fields = array(
            'videos',
            'extra'
        );
        

        $props = array_merge($_default_fields, $_default_props, $props);
        
        foreach ($props as $key => $value) 
        {
            $this->{$key} = $value;
        }
        
        return $props;
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
        $tservice_order = $this->getOrderByOpid($opid);
        
        if ($tservice_order) {
            $this->db()->update('tservices_orders', array('status' => '-1', 'acc_op_id' => 0), 'id = '.$tservice_order['id']);
            $this->db()->update('tservices_orders_feedbacks', array('deleted' => true), 'id = '.(int)$tservice_order['emp_feedback_id']);
            $this->db()->update('tservices_orders_feedbacks', array('deleted' => true), 'id = '.(int)$tservice_order['frl_feedback_id']);
        }
        
        return 0;
    }
    
    function updateTab($uid) {
        $account = new account();
        $account->GetInfo($uid, false);
        if ($account->sum > 0) {
            $this->db()->query('DELETE FROM tservices_orders_debt WHERE user_id = ?', $uid);
        }
    }
    
    function getOrderByOpid($opid) {
        return $this->db()->row("
            SELECT * FROM tservices_orders
            WHERE acc_op_id = ?i 
            LIMIT 1
        ",$opid);
    }
    
    function clearOldPreview($tuid) {
        $this->db()->query("
                DELETE FROM {$this->TABLE_FILES}  
                WHERE src_id = ? AND preview = 't'", 
                $tuid);
    }
    
    /**
     * Имеет ли пользователь хотя бы одну типовую услугу
     * @param array $profs Список категорий, в которых ищется услуга
     */
    public function hasUserTservice($is_public = false, $profs = array()) {
        if (count($profs)) {
            $query = $this->db()->parse("SELECT s.id FROM {$this->TABLE} AS s
                LEFT JOIN {$this->TABLE_BLOCKED} AS sb ON sb.src_id = s.id 
                WHERE s.user_id = ?i AND s.category_id IN (?l) 
                ".($is_public?" AND s.active = TRUE AND sb.src_id IS NULL":"")."
                LIMIT 1;
            ", $this->uid, $profs);
            $id = $this->db()->val($query);
        } else {
            $id = $this->db()->val("SELECT s.id FROM {$this->TABLE} AS s
                LEFT JOIN {$this->TABLE_BLOCKED} AS sb ON sb.src_id = s.id 
                WHERE s.user_id = ?i 
                ".($is_public?" AND s.active = TRUE AND sb.src_id IS NULL":"")."
                LIMIT 1;", $this->uid);
        }
        return $id > 0;
    }
    
    /**
     * Список незакрепленных типовых услуг юзера
     * 
     * @param bool $kind
     * @return array
     */
    public function getNotBindedList($kind, $profs = array())
    {
        $sql = $this->db()->parse("
            SELECT 
                DISTINCT ON (s.id) 
                s.id AS id, 
                s.title AS title
            FROM {$this->TABLE} AS s 
            LEFT JOIN {$this->TABLE_BLOCKED} AS sb ON sb.src_id = s.id
            LEFT JOIN {$this->TABLE_BINDS} AS tb ON tb.tservice_id = s.id AND tb.kind = ?i AND tb.date_stop > now()
            WHERE s.user_id = ?i AND s.active = TRUE AND s.deleted = FALSE AND sb.src_id IS NULL AND tb.id IS NULL
            ".(count($profs) ? "AND s.category_id IN (?l)" : "")."
            ORDER BY s.id DESC
            LIMIT 100
        ", (int)$kind, $this->uid, $profs);

        $rows = $this->db()->rows($sql);
         
        return $rows;
    }    
    
    /**
     * Имеет ли пользователь незакрепленные услуги
     */
    public function hasUnbindedTservices($kind, $user_id, $profs)
    {
        $sql = $this->db()->parse("
        SELECT s.id
        FROM {$this->TABLE} AS s 
        LEFT JOIN {$this->TABLE_BINDS} AS tb ON tb.tservice_id = s.id AND tb.kind = ?i AND tb.date_stop > now()
        LEFT JOIN {$this->TABLE_BLOCKED} AS sb ON sb.src_id = s.id 
        WHERE s.deleted = FALSE AND s.active = TRUE AND sb.src_id IS NULL AND tb.id IS NULL
        AND s.user_id = ?i
        ".(count($profs) ? "AND s.category_id IN (?l)" : "")."
        LIMIT 1;
        ", (int)$kind, (int)$user_id, $profs);

        $id = $this->db()->val($sql);
        return $id > 0;
    }

}

