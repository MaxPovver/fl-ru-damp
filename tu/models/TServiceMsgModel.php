<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/atservices_model.php');
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/CFile.php");

/**
 * Class TServiceMsgModel
 * Модель переписки в заказе типовой услуги
 */
class TServiceMsgModel extends atservices_model {
        
    private $TABLE                  = 'tservices_msg';
    private $TABLE_FREELANCER       = 'freelancer';
    private $TABLE_EMPLOYER         = 'employer';
    private $TABLE_ORDER            = 'tservices_orders';
    private $TABLE_FILES            = 'file_tservice_msg';
    private $TABLE_ATTACHEDFILES    = 'attachedfiles';

    /**
     * Куда сохраняет файлы сообщений заказа
     * Пример: kazakov/private/orders/777/
     */
    const UPLOAD_FILE_PATH          = "%s/private/orders/%d";
    
    /**
    * Максимальный размер файла в байтmsgsCntах
    *
    * @var integer
    */
   const MAX_FILE_SIZE = 5242880;

   /**
    * Максимальное количество прикрепленных файлов
    *
    * @var integer
    */
   const MAX_FILES = 10;

    /**
     * Список сообщений в заказе
     * 
     * @param int $order_id
     * @return array - список сообщений, можно ограничить постраничностью
     */
    public function getList($order_id)
    {
       $sql = $this->db()->parse("
            SELECT 
                m.*,
                u.login, u.uname, u.usurname
            FROM {$this->TABLE} AS m 
            LEFT JOIN users AS u ON u.uid = m.author_id
            WHERE
                m.order_id = ?i 
            ORDER BY
                m.sent DESC
        ",$order_id);

        $sql = $this->_limit($sql);
        $rows = $this->db()->rows($sql);

        if(count($rows))
        {
            foreach ($rows as &$row) 
            {
                $row['files'] = $this->getAttached($row['id']);
            }
        }

        return $rows;
    }

    /**
     * Новые непрочитанные сообщения в заказе
     * 
     * @param int $order_id
     * @return array - список сообщений
     */
    public function getListNew($order_id)
    {
       $sql = $this->db()->parse("
            SELECT 
                m.*,
                u.login, u.uname, u.usurname
            FROM {$this->TABLE} AS m 
            LEFT JOIN users AS u ON u.uid = m.author_id
            WHERE
                m.order_id = ?i 
                AND m.is_read != 't'
                AND m.reciever_id = ?i
            ORDER BY
                m.sent DESC
        ",$order_id, get_uid(false));
        //@todo: использование тут get_uid - это плохо!     


        $rows = $this->db()->rows($sql);

        if(count($rows))
        {
            foreach ($rows as &$row) 
            {
                $row['files'] = $this->getAttached($row['id']);
            }
        }

        return $rows;
    }


    
    public function isMsgMember($msg_id, $uid)
    {
        return (bool)$this->db()->val("
            SELECT m.id
            FROM {$this->TABLE} AS m
            WHERE 
                m.id = ?i AND 
                (m.author_id = ?i OR m.reciever_id = ?i)
            LIMIT 1
        ", $msg_id, $uid, $uid);       
    }
    
    
    
    /**
     * Получить сообщение
     * 
     * @param type $id
     * @return type
     */
    public function getCard($id)
    {
       $sql = $this->db()->parse("
            SELECT 
                m.*,
                u.login, u.uname, u.usurname
            FROM {$this->TABLE} AS m 
            LEFT JOIN users AS u ON u.uid = m.author_id
            WHERE
                m.id = ?i 
            ORDER BY
                m.sent DESC
        ",$id);

        $row = $this->db()->row($sql);

        if($row)
        {
            $row['files'] = $this->getAttached($row['id']);
        }

        return $row;
    }


    /**
     * Кол-во сообщений в заказе для юзера
     * 
     * @param type $order_id
     * @param type $user_id
     * @return type
     */
    public function getCount($order_id, $user_id) 
    {
        $sql = $this->db()->parse("
            SELECT COUNT (*) 
            FROM {$this->TABLE} 
            WHERE order_id = ?i AND (reciever_id = ?i OR author_id = ?i)", $order_id, $user_id, $user_id);

        $all = $this->db()->val($sql);
        $sql = $this->db()->parse("
            SELECT COUNT (*) 
            FROM {$this->TABLE} 
            WHERE order_id = ?i AND reciever_id = ?i AND is_read = FALSE", $order_id, $user_id);

        $new = $this->db()->val($sql);
        return array('all' => $all, 'new' => $new);            
    }


    /**
     * Получить кол-во новых сообщений для юзера
     * 
     * @param type $uid
     * @return type
     */
    public function countNew($uid) 
    {
        $sql = $this->db()->parse("
            SELECT COUNT (*) 
            FROM {$this->TABLE} 
            WHERE reciever_id = ?i AND is_read = FALSE", $uid);
        return $this->db()->val($sql);
    }


    /**
     * Добавить сообщение в заказе ТУ
     * 
     * @param type $order_id
     * @param type $author_id
     * @param type $reciever_id
     * @param type $text
     * @return type
     */
    public function add($order_id, $author_id, $reciever_id, $text) 
    {
        $id = $this->db()->insert($this->TABLE, array(
            'order_id' => $order_id,
            'author_id' => $author_id,
            'reciever_id' => $reciever_id,
            'message' => $text,
            'sent' => 'NOW()',
        ), 'id');

        return $id;
    }



    /**
     * Приаттачить загруженные файлы к сообщению
     * 
     * @param type $sess
     * @param type $msg_id
     * @return boolean
     */
    public function addAttached($sess, $msg_id) 
    {
        $file_ids = $this->db()->col("
            SELECT file_id 
            FROM {$this->TABLE_ATTACHEDFILES} 
            WHERE session = ? AND status IN (?l) 
            ORDER BY file_id ASC", $sess, array(1, 3)
        );


        if (count($file_ids)) 
        {
            $res = $this->db()->update($this->TABLE_FILES, array('src_id' => $msg_id), 'id IN(?l)', $file_ids);
            if (!$res) return false;
            $this->db()->query("DELETE FROM {$this->TABLE_ATTACHEDFILES} WHERE session = ?", $sess);
        }

        return true;
    }


    /**
     * Получить список файлов сообщения
     *  
     * @param type $msg_id
     * @return type
     */
    public function getAttached($msg_id) 
    {
        return CFile::selectFilesBySrc($this->TABLE_FILES, $msg_id, 'id');
        //return $this->db()->rows("SELECT * FROM {$this->TABLE_FILES} WHERE src_id = ?i", $msg_id);
    }


    /**
     * Пометить сообщения как прочитанные
     * 
     * @param type $order_id
     * @param type $uid
     */
    public function markAsRead($order_id, $uid) 
    {
        $this->db()->update($this->TABLE, array('is_read' => true), 'reciever_id = '.$uid.' AND order_id = '.$order_id);
    }
        
    /**
     * Получает список сообщений, отправленных до указанной даты
     * @param type $order_id
     * @param type $date
     * @return type
     */
    public function getMessagesBeforeDate($order_id, $date)
    {
       $sql = "SELECT 
                m.*,
                u.login, u.uname, u.usurname
            FROM {$this->TABLE} AS m 
            LEFT JOIN users AS u ON u.uid = m.author_id
            WHERE
                m.order_id = ?i 
                AND m.sent < ?
            ORDER BY
                m.sent DESC
        ";

        $rows = $this->db()->rows($sql, $order_id, $date);

        return $rows;
        
    }
        

    
    
    /**
     * Получить директорию для загружаемого файла
     * 
     * @param type $order_id
     * @param type $sess
     * @param type $uid
     * @param type $hash
     * @return boolean
     */
    public static function getUploadPath($order_id, $sess, $uid, $hash)
    {
        require_once(ABS_PATH . '/classes/tservices/tservices_helper.php');
        
        $_hash = tservices_helper::getOrderUrlHash(
                array($order_id, $sess), 
                $uid);

        //Если проверка принадлежности по хешу неудалась 
        //то пробуем получить через БД
        if ($hash !== $_hash) {
            require_once(ABS_PATH . "/tu/models/TServiceOrderModel.php");
            $isMember = TServiceOrderModel::model()->isOrderMember($order_id, $uid);
            
            if (!$isMember) {
                return false;
            }
        }
        
        return sprintf(self::UPLOAD_FILE_PATH, '', $order_id);
    }




    /**
     * Создаем сами себя
	 * @return TServiceModel
	 */
	public static function model()
	{
            $class = get_called_class();
            return new $class;
	}

}