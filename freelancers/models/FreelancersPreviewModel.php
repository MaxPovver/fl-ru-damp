<?php

require_once(ABS_PATH . '/classes/yii/CModel.php');
require_once(ABS_PATH . '/freelancers/widgets/FreelancersPreviewEditorPopup.php');

class FreelancersPreviewModel extends CModel
{
    const TABLE = 'freelancers_preview';

    const TAG_ALL_PREVIEW           = "getListByUids";
    const TAG_CURRENT_USER_PREVIEW  = "getListByUidsAnd_Group%s_Prof%s";
    
    const CACHE_TTL = 600;//10 min
    
    const SESS_EXIST_PREVIEW_DATA   = "existUserPreviewData";
    
    protected $type;
    protected $src_id;
    protected $uid;
    protected $pos;
    protected $group_id = 0;
    protected $prof_id = 0;
    protected $is_default = false;


    protected $is_valid = false;
    
    /**
     * Данные для основных настроек
     * и дополнительных при первом добавлении
     * 
     * @var type 
     */
    protected $data = array();
    
    /**
     *  Данные для обновления дополнительных настроек 
     * 
     * @var type 
     */
    protected $data_extra_update = array();
    protected $id;

    const TYPE_PF = 1;
    const TYPE_TU = 2;
    
    static $type_classes = array(
        self::TYPE_PF => 'PortfolioItem',
        self::TYPE_TU => 'TServiceItem'
    );
    
    static $type_from_string = array(
        FreelancersPreviewEditorPopup::TAB_PF => self::TYPE_PF,
        FreelancersPreviewEditorPopup::TAB_TU => self::TYPE_TU,
    );


    public static function getTypeClass($type)
    {
        return isset(self::$type_classes[$type])?
                self::$type_classes[$type]:null;
    }


    
    public static function setExistPreviewData($type, $ids)
    {
        $_SESSION[self::SESS_EXIST_PREVIEW_DATA] = array(
            'type' => $type,
            'ids' => $ids
        );
    }

    

    
    public function getPos()
    {
        return $this->pos;
    }

    

    protected function getPfSql($where = '')
    {
        return $this->db()->parse("
            SELECT 
                fp.id AS fp_id,
                fp.pos AS fp_pos,
                fp.type,
                p.id, 
                p.user_id, 
                p.name AS title, 
                p.descr, 
                p.pict, 
                p.prev_pict, 
                p.show_preview, 
                p.norder, 
                p.prev_type, 
                p.is_video,
                NULL AS price,
                NULL AS videos,
                NULL AS total_feedbacks,
                NULL AS file
            FROM portfolio AS p 
            INNER JOIN " . self::TABLE . " AS fp ON fp.src_id = p.id 
            LEFT JOIN portfolio_blocked AS pb ON p.id = pb.src_id 
            WHERE 
                fp.type = ?i 
                AND p.is_blocked = FALSE 
                AND pb.src_id IS NULL 
                {$where}", self::TYPE_PF);
    }

    
    protected function getTuSql($where = '')
    {
        return $this->db()->parse("
            (SELECT 
                DISTINCT ON (q.fp_id)
                q.*,
                f.fname AS file
             FROM (
                SELECT
                    fp.id AS fp_id,
                    fp.pos AS fp_pos,
                    fp.type,
                    s.id, 
                    s.user_id,
                    s.title, 
                    NULL AS descr,
                    NULL AS pict,
                    NULL AS prev_pict,
                    NULL AS show_preview,
                    NULL AS norder,
                    NULL AS prev_type,
                    NULL AS is_video,
                    s.price,
                    s.videos,
                    s.total_feedbacks
                FROM tservices AS s    
                INNER JOIN  ". self::TABLE ."  AS fp ON fp.src_id = s.id
                LEFT JOIN tservices_orders_debt AS od ON od.user_id = s.user_id 
                LEFT JOIN tservices_blocked AS sb ON sb.src_id = s.id 
                WHERE 
                    fp.type = ?i 
                    AND s.deleted = FALSE 
                    AND s.active = TRUE 
                    AND sb.src_id IS NULL
                    AND (od.id IS NULL OR od.date >= NOW()) 
                    {$where} 
             ) AS q      
             LEFT JOIN file_tservices AS f ON f.src_id = q.id AND f.small = 4             
             ORDER BY q.fp_id, f.preview DESC, f.id         
           )", self::TYPE_TU);
    }

    
    protected function getSqls(Array $where = array())
    {
        $where_sql = '';
        
        if (count($where)) {
            foreach ($where as $sql => $value) {
                $where_sql .= $this->db()->parse($sql, $value) . ' ';
            }
        }
        
        return array(
            self::TYPE_PF => $this->getPfSql($where_sql),
            self::TYPE_TU => $this->getTuSql($where_sql)
        );
    }








    /**
     * Выборка работ/услуг по указанным пользователям
     * 
     * @param array $uids
     * @param type $expire
     * @param type $group
     * @return type
     */
    public function getListByUids(Array $uids, $group_id = 0, $prof_id = 0)
    {
        require_once(ABS_PATH . '/freelancers/models/FreelancersPreviewItemIterator.php');
        
        $where = array('AND fp.user_id IN(?l)' => $uids);
        if ($prof_id > 0) {
            $where['AND fp.prof_id = ?i'] = $prof_id;
        } else {
            $where['AND fp.group_id = ?i'] = $group_id;
            $where['AND fp.prof_id = ?i'] = 0;
        }
        
        $sqls = $this->getSqls($where);
        $sql = implode(' UNION ALL ', $sqls);
        $sql = "-- FreelancersPreviewModel::getListByUids           
            {$sql}
            ORDER BY fp_pos";

        
        $key = sprintf(self::TAG_CURRENT_USER_PREVIEW, $group_id, $prof_id);
        $memBuff = new memBuff();
        
        if (!$result = $memBuff->get($key)) {
            $result = $this->db()->rows($sql);
            if ($result) {
                $memBuff->set($key, $result, self::CACHE_TTL, self::TAG_ALL_PREVIEW);
            }
        }
        
        $result = !$result?array():$result;
        return new FreelancersPreviewItemIterator($result);
    }


    /**
     * Получить данные последней настройки
     * 
     * @return \FreelancersPreviewItemIterator|boolean
     */
    public function getLastItem()
    {
        require_once(ABS_PATH . '/freelancers/models/FreelancersPreviewItemIterator.php');
        
        $sqls = $this->getSqls(array('AND fp.id = ?i' => $this->id));
        if (isset($sqls[$this->type])) {
            $sql = $sqls[$this->type];
            $sql .= ' LIMIT 1';
            
            $result = $this->db()->row($sql);
            $result = !$result?array():array($result);
            return new FreelancersPreviewItemIterator($result);
        }
        
        return false;
    }


    /**
     * Существует ли настройка для указанных параметров
     * 
     * @param type $uid
     * @param type $pos
     * @param type $group_id
     * @param type $prof_id
     * @return type
     */
    public function getRowByUidAndPos($uid, $pos, $group_id = 0, $prof_id = 0)
    {
       return $this->db()->row("SELECT id, is_default FROM " . self::TABLE . " 
                                WHERE 
                                    user_id = ?i 
                                    AND group_id = ?i 
                                    AND prof_id = ?i
                                    AND pos = ?i 
                                LIMIT 1", $uid, $group_id, $prof_id, $pos);
    }

   
    
    
    /**
     * Установить флаг по указанным параметрам
     * 
     * @param type $is_default
     * @return type
     */
    public function setDefaultByGroupAndProf($is_default = true)
    {
        if (!$this->uid) {
            return false;
        }
        
        return $this->db()->update(self::TABLE, array(
            'is_default' => $is_default
        ), 'user_id = ?i AND group_id = ?i AND prof_id = ?i', 
        $this->uid, $this->group_id, $this->prof_id);
    }

    




    /**
     * Выборка существующих настроек для общего раздела и указанной группы
     * 
     * @param type $group_id
     * @return type
     */
    public function getExistData($group_id)
    {
        return $this->db()->rows(
                    "SELECT  fp.*
                     FROM " . self::TABLE . " AS fp
                     WHERE 
                        fp.user_id = ?i AND 
                        ((fp.prof_id = ?i AND fp.group_id = 0) OR 
                         (fp.prof_id = 0 AND fp.group_id = ?i) OR 
                         (fp.prof_id = 0 AND fp.group_id = 0))", 
                $this->uid, $this->prof_id, $group_id);
    }
    
    
    
    /**
     * Очистить настройки в соответствии с параметрами
     * 
     * @param type $prof_id
     * @param type $group_id
     * @return boolean
     */
    public function clearByProfAndGroup($prof_id, $group_id)
    {
        if (!$this->uid) {
            return false;
        }
        
        return $this->db()->query(
                "DELETE FROM " . self::TABLE . " 
                 WHERE user_id = ?i AND prof_id = ?i AND group_id = ?i", 
                 $this->uid, $prof_id, $group_id);
    }

    





    /**
     * Провека параметров и подготовка настроек 
     * для обновлении или добавления
     * 
     * @param type $data
     * @return type
     */
    public function isValid($data)
    {
        require_once(ABS_PATH . '/freelancers/widgets/FreelancersPreviewWidget.php');
        require_once(ABS_PATH . '/classes/freelancer.php');
        
        $this->is_valid = false;
        $max_pos = FreelancersPreviewWidget::MAX_ITEMS;
        
        $this->group_id = isset($data['group'])?intval($data['group']):0;
        $this->prof_id = isset($data['prof'])?intval($data['prof']):0;
        $hash = isset($data['hash'])?$data['hash']:null;
        
        //Проверка группы/раздела именно тех на странице которых был открыт попап
        if ($hash !== paramsHash(array($this->group_id, $this->prof_id))) {
            return $this->is_valid;
        }
        
        
        $value = isset($data['value'])? $data['value']:'';
        $this->uid = isset($data['uid']) && $data['uid'] > 0? $data['uid']:null;
        $this->pos = isset($data['pos']) && 
                     $data['pos'] > 0 && 
                     $data['pos'] <= $max_pos? 
                        intval($data['pos']):null;
        
        if (!$this->uid || !$this->pos || empty($value)) {
            return $this->is_valid;
        }
        
        $user = new freelancer();
        $user->GetUserByUID($this->uid);
        if ($user->uid <= 0) {
            return $this->is_valid;
        }

        
        $pattern = implode('|', FreelancersPreviewEditorPopup::$types);
        $matches = array();

        if (preg_match("/^({$pattern})_([0-9]+)$/", $value, $matches)) {
            
            $this->type = self::$type_from_string[$matches[1]];
            $this->src_id = $matches[2];

            switch($this->type) {
                case self::TYPE_PF:
                    require_once(ABS_PATH . '/classes/portfolio.php');
                    $portfolio = new portfolio();
                    $this->is_valid = (bool)$portfolio->isExistActive($this->uid, $this->src_id);
                    break;
                
                case self::TYPE_TU:
                    require_once(ABS_PATH . '/classes/tservices/tservices.php');
                    $tservices = new tservices($this->uid);
                    $this->is_valid = (bool)$tservices->isExistActive($this->src_id);                    
                    break;
            }
            

            if ($this->is_valid) {

                $res = $this->getRowByUidAndPos(
                        $this->uid, 
                        $this->pos, 
                        $this->group_id, 
                        $this->prof_id);
                
                $this->id = ($res)?$res['id']:0;
                
                //Уже есть данная позиция
                //то готовим данные для обновления
                if ($this->id > 0) {

                    $this->data = array(
                        'src_id' => $this->src_id,
                        'type' => $this->type,
                        'is_default' => false
                    );                        
                    
                    $this->is_default = $res['is_default'] == 't';
                    
                //Первое добавление позиции
                //то готовим новые данные для добавления
                } else {
                    
                    $exist_data = isset($_SESSION[self::SESS_EXIST_PREVIEW_DATA])?
                            $_SESSION[self::SESS_EXIST_PREVIEW_DATA]:null;

                    for ($idx = 1; $idx <= $max_pos; $idx ++) {

                        if ($idx == $this->pos) {
                            $this->data[$idx] = array(
                                'src_id' => $this->src_id,
                                'type' => $this->type,
                                'user_id' => $this->uid,
                                'pos' => $idx,
                                'group_id' => $this->group_id,
                                'prof_id' => $this->prof_id,
                                'is_default' => false
                            );
                        } elseif (isset($exist_data['ids'][$idx-1])) {
                            $this->data[$idx] = array(
                                'src_id' => $exist_data['ids'][$idx-1],
                                'type' => $exist_data['type'],
                                'user_id' => $this->uid,
                                'pos' => $idx,
                                'group_id' => $this->group_id,
                                'prof_id' => $this->prof_id,
                                'is_default' => false
                            );                                
                        }
                    }
                }
                
     
                
                
                //Устанавливаем по возможности 
                //текущии настройки для общего каталога / раздела
                
                /*
                   - общий каталог поумолчанию из основного подраздела (специализации), но может и настраиваться по своему
                   - раздел каталога поумолчанию из последнего отредактированного подраздела, но может настраиваться по своему
                   - подраздел (специализация) настраивается по своему 
                 */                

                if ($this->prof_id > 0 && 
                    $this->group_id == 0 && 
                    count($this->data)) {
                    
                    $is_exist_setting_for_catalog = false;
                    $is_exist_setting_for_group = false;
                    
                    
                    require_once(ABS_PATH . '/classes/professions.php');
                    $group_id = professions::GetGroupIdByProf($this->prof_id);
                    $exist_data = $this->getExistData($group_id);
                    
                    $current_settings = array();
                    
                    if ($exist_data) {
                        
                        foreach ($exist_data as $el) {
                            //Существующие текущие настройки раздела
                            if ($el['prof_id'] == $this->prof_id && 
                                $el['group_id'] == 0) {
                                
                                unset($el['id']);
                                
                                if ($el['pos'] == $this->pos) {
                                    $el['src_id'] = $this->src_id;
                                    $el['type'] = $this->type;
                                }
                                
                                $current_settings[$el['pos']] = $el;
                                
                            //Существующие настройки для общего каталога
                            } elseif ($el['prof_id'] == 0 && 
                                      $el['group_id'] == 0 && 
                                      $el['is_default'] == 'f') {
                                $is_exist_setting_for_catalog = true; 

                            //Существующие настройки для группы 
                            } elseif($el['prof_id'] == 0 && 
                                     $el['group_id'] == $group_id && 
                                     $el['is_default'] == 'f') {
                                $is_exist_setting_for_group = true;
                            }
                        }
                    }                    
                    

                    //Если настройки для каталога нет и эта основная 
                    //специализация то ее добавляем в общий каталог
                    $data_extended = array();
                    $current_settings = $this->id > 0?$current_settings:$current_settings + $this->data;

                    if(!$is_exist_setting_for_catalog) {
                        $is_main_prof = (bool)$this->db()->val(
                                'SELECT 1 FROM freelancer 
                                 WHERE uid = ?i AND spec = ?i LIMIT 1', 
                                 $this->uid, $this->prof_id);

                        if ($is_main_prof) {
                            foreach ($current_settings as $el) {
                                $el['prof_id'] = 0;
                                $el['group_id'] = 0;
                                $el['is_default'] = true;
                                $data_extended[] = $el;
                            }
                        }
                    }                         
                    
                    
                    //Если нет настроек для группы раздела то добавляем текущии 
                    //настройки как поумолчанию для группы
                    if (!$is_exist_setting_for_group) {
                        foreach ($current_settings as $el) {
                            $el['prof_id'] = 0;
                            $el['group_id'] = $group_id;
                            $el['is_default'] = true;
                            $data_extended[] = $el;
                        }
                    }
                    
                    if ($data_extended) {
                        $this->data_extra_update = $data_extended;
                    }                    
                }
            }
        }
        
        return $this->is_valid;
    }
    
    
    /**
     * Сохранить настройки
     * 
     * @return boolean
     */
    public function save()
    {
        if (!$this->is_valid) {
            return false;
        }
        
        $cache_keys = array();
        
        $key = sprintf(FreelancersPreviewModel::TAG_CURRENT_USER_PREVIEW, $this->group_id, $this->prof_id);
        $cache_keys[$key] = $key;
        
        if ($this->id > 0) {
            
            $res = $this->db()->update(
                        self::TABLE, 
                        $this->data, 
                        'id = ?i AND user_id = ?i', 
                        $this->id, $this->uid);
            
            if ($this->is_default) {
                $this->setDefaultByGroupAndProf(false);
            }
            
        } else if(count($this->data)) {
            
            foreach ($this->data as $insert_data) {
                $ret_id = $this->db()->insert(self::TABLE, $insert_data, 'id');
                if ($insert_data['pos'] == $this->pos && 
                    $insert_data['prof_id'] == $this->prof_id && 
                    $insert_data['group_id'] == $this->group_id) {
                    
                    $this->id = $ret_id;
                }
            }
            
            $res = $this->id > 0;
        }
        
        
        if ($this->data_extra_update) {
            foreach ($this->data_extra_update as $insert_data) {
                
                $key = sprintf(FreelancersPreviewModel::TAG_CURRENT_USER_PREVIEW, $insert_data['group_id'], $insert_data['prof_id']);
                
                if (!isset($cache_keys[$key])) {
                    $this->clearByProfAndGroup($insert_data['prof_id'], $insert_data['group_id']);
                    $cache_keys[$key] = $key;
                }                
                
                $this->db()->insert(self::TABLE, $insert_data);
            }
        }
        
        
        //Чистим кеш выборки где присутствовали превью юзера
        if ($res) {
            $memBuff = new memBuff();
            foreach ($cache_keys as $value) {
                $memBuff->delete($value);
            }
            unset($_SESSION[self::SESS_EXIST_PREVIEW_DATA]);
        }
        
        return $res;
    }
    
    
}