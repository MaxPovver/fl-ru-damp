<?php
/**
 * API для работы с клиентом Free-Tray.
 */
class externalApi_Freetray extends externalApi {

    // @todo php 5.3 сделать const
    protected $API_NAMESPACE      = 'http://www.free-lance.ru/external/api/freetray';
    protected $API_DEFAULT_PREFIX = 'f';

    protected $_methodsCfg = array
    (
        'getProjects' => array (
            'page_size'     => 20,
            'max_page_size' => 40,
            'min_page_size' => 1,
            'default_kind'  => -1,
            'fields' => array (
                'id'=>EXTERNAL_DT_STRING,
                'name'=>EXTERNAL_DT_STRING,
                'descr'=>EXTERNAL_DT_STRING,
                'category'=>EXTERNAL_DT_STRING,
                'subcategory'=>EXTERNAL_DT_STRING,
                'city'=>EXTERNAL_DT_STRING,
                'country'=>EXTERNAL_DT_STRING,
                'cost'=>EXTERNAL_DT_STRING,
                'kind'=>EXTERNAL_DT_STRING,
                'offers_count'=>EXTERNAL_DT_STRING,
                'pro_only'=>EXTERNAL_DT_BOOL,
                'post_date'=>EXTERNAL_DT_TIME,
                'create_date'=>EXTERNAL_DT_TIME,
                'currency'=>EXTERNAL_DT_STRING,
                'logo'=>EXTERNAL_DT_STRING,
                'is_pro'=>EXTERNAL_DT_BOOL,
                'priceby'=>EXTERNAL_DT_STRING,
                'prefer_sbr' => EXTERNAL_DT_BOOL,
            ),
            'attach-fields' => array (
                'size'=>EXTERNAL_DT_STRING
            )
        ),
        'getTable' => array (
            'tables' => array (
                'city'        => array('view' => 'vw_external_city'),
                'country'     => array('view' => 'vw_external_country'),
                'professions' => array('view' => 'vw_external_professions'),
                'prof_group'  => array('view' => 'vw_external_prof_group')
            )
        ),
        'getContacts' => array (
            'page_size'     => 20,
            'fields' => array (
                'uid'=>EXTERNAL_DT_STRING,
                'he_last_read'=>EXTERNAL_DT_TIME,
                'i_last_read'=>EXTERNAL_DT_TIME,
                'my_last_post'=>EXTERNAL_DT_TIME,
                'his_last_post'=>EXTERNAL_DT_TIME,
                'login'=>EXTERNAL_DT_STRING,
                'uname'=>EXTERNAL_DT_STRING,
                'usurname'=>EXTERNAL_DT_STRING,
                'photo'=>EXTERNAL_DT_STRING,
                'role'=>EXTERNAL_DT_STRING,
                'is_team'=>EXTERNAL_DT_BOOL,
                'is_pro'=>EXTERNAL_DT_BOOL,
                'is_pro_test' => EXTERNAL_DT_BOOL,
                'msg_count'=>EXTERNAL_DT_STRING
            )
        ),
        'getNewMessages' => array (
            'fields' => array (
                'id'=>EXTERNAL_DT_STRING,
                'uid'=>EXTERNAL_DT_STRING,
                'post_time'=>EXTERNAL_DT_TIME,
                'msg_text'=>EXTERNAL_DT_STRING
            ),
            'msg_text_length' => 130
        ),
        'getMessFolders' => array (
            'fields' => array (
                'id'=>EXTERNAL_DT_STRING,
                'fname'=>EXTERNAL_DT_STRING
            )
        )
    );


    /**
     * Вызывается для проверки доступности авторизации данного пользователя.
     *
     * @param object $user   пользователь (инициализированный экземпляр класса users).
     * @return integer   код ошибки или 0 -- можно авторизировать.
     */
    protected function _authDenied($user) {
        $err = parent::_authDenied($user);
        if(!$err) {
            if(is_emp($user->role))
                $err = EXTERNAL_ERR_ONLYFRL;
        }
        return $err;
    }

    /**
     * Вызывается перед каждым методом только внутри данного пространства имен (кроме методов externalApi) для 
     * проверки прав на вызов метода.
     * Доступны $this->_mName и $this->_mCfg.
     * В методах freetray запрещены вызовы без авторизации, а также работодательским аккаунтам.
     *
     * @return integer   код ошибки или 0 -- метод разрешен.
     */
    protected function _methodsDenied() {
        if(!$this->_sess->id)
            $this->error( EXTERNAL_ERR_NEED_AUTH );
        if($this->_sess->role == 1)
            $this->error( EXTERNAL_ERR_ONLYFRL );
        return 0;
    }


    /**
     * Возвращает запрошенную таблицу. Список таблиц определяются в конфиге данного класса. Каждой таблицы соотвествует определенная VIEW в БД.
     *
     * @param string $table   имя таблицы.
     * @param string $client_version   клиенская версия таблицы (для кэширования).
     * @return array   массив:
     *                   data => 0 или вся таблица. 0, если клиент должен использовать свой кэш.
     *                   version => серверная версия таблицы. Клиент должен обновить это значение.
     */
    protected function x____getTable($args)
    {
        list($table, $client_version) = $args;

        if(!isset($table))
            $this->error( EXTERNAL_ERR_INVALID_METHOD_ARG, 'Use getTable(string table[, int client_version])' );

        global $DB;

        $tables = $this->_mCfg['tables'];

        if(!$tables[$table])
            return $this->warning( EXTERNAL_WARN_UNDEFINED_TABLE );

        $data = NULL;
        $client_version = (int)$client_version;
        $sql = "SELECT version FROM external_cache WHERE obj_name = '{$table}' AND obj_type = " . self::OBJTYPE_TABLE;
        if($server_version = $DB->val($sql)) {
            $server_version = $this->pg2ex($server_version, EXTERNAL_DT_TIME);
            if($client_version && $server_version <= $client_version)
                $data = 0;
        }
        if($data === NULL) {
            $data = $DB->rows("SELECT * FROM {$tables[$table]['view']}");
        }
        $result = array('data'=>$data);
        if($server_version)
            $result['version'] = $server_version;
        return $result;
    }


    /**
     * Возвращает ленту проектов.
     * 
     * @param integer $kind        тип проектов (-1=5=Все проекты; 2=Конкурсы; 4=В офис; 6=Только для про)
     * @param array   $filter      массив с фильтром проектов (тот же, что для projects::getProjects(), но разделы в таком виде: [[1,2,3], [44,55,66]], где по индексу 0 -- разделы, по 1 -- подразделы)
     * @param integer $page_size   кол-во проектов на странице.
     * @return array
     */
    protected function x____getProjects($args)
    {
        list($kind, $filter, $page_size) = $args;

        require_once(ABS_PATH.'/classes/projects.php');
        require_once(ABS_PATH.'/classes/projects_filter.php');
        require_once(ABS_PATH.'/classes/professions.php');
        $result = NULL;
        $projects = new new_projects();
        $kind = $kind ? (int)$kind : $this->_mCfg['default_kind'];
        $page_size = (int)$page_size;
        $limit = $page_size > $this->_mCfg['max_page_size'] ? $this->_mCfg['max_page_size'] : ($page_size < $this->_mCfg['min_page_size'] ? $this->_mCfg['page_size'] : $page_size);
        if($filter) {
            $filter['active'] = $this->ex2pg(EXTERNAL_TRUE, EXTERNAL_DT_BOOL);
            $filter['wo_cost'] = $this->ex2pg($filter['wo_cost'], EXTERNAL_DT_BOOL);
            $filter['only_sbr'] = $this->ex2pg($filter['prefer_sbr'], EXTERNAL_DT_BOOL);
            if($filter['my_specs']) {
                $filter['my_specs'] = $this->ex2pg($filter['my_specs'], EXTERNAL_DT_BOOL);
                $filter['user_specs'] = professions::GetProfessionsByUser($this->_sess->_uid, false, true);
            }
            if(isset($filter['categories']) && is_array($filter['categories'])) {
                $filter['categories'] = intarrPgSql($filter['categories']);
                $cats = $filter['categories'];
                $filter['categories'] = array();
                foreach($cats as $i=>$arr) {
                    if($i>1) break;
                    if(is_array($arr) && !isNulArray($arr)) {
                        if($i==1)
                            $arr = professions::GetMirroredProfs(implode(',', $arr));
                        $filter['categories'][$i] = array_fill_keys($arr, $i);
                    }
                }
            }
            list($filter['cost_from'], $filter['cost_to']) = projects_filters::preCosts($filter['cost_from'], $filter['cost_to']);
        }
        if($prjs = $projects->getLastProjects($kind, $filter, $limit, true)) {
            foreach($prjs as $key=>$p) {
                $row = $this->pg2exRow($this->_mCfg['fields'], $p);
                if($row['logo'])
                    $row['logo'] = WDCPREFIX.'/'.$row['logo'];
                if($attach = $projects->getAllAttach($p['id'])) {
                    $row['attach'] = array();
                    foreach($attach as $a) {
                        $att = $this->pg2exRow($this->_mCfg['attach-fields'], $a);
                        $att['link'] = WDCPREFIX.'/'.$a['path'].$a['name'];
                        $row['attach'][] = $att;
                    }
                }
                $result[$key] = $row;
            }
        }

        return $result;
    }

    /**
     * Возвращает папки пользователя в личных сообщениях.
     * @see mess_folders::GetAll()
     * 
     * @return array
     */
    protected function x____getMessFolders()
    {
        require_once(ABS_PATH.'/classes/mess_folders.php');
        $mf = new mess_folders();
        $mf->from_id = $this->_sess->_uid;
        $result = NULL;

        if($flds = $mf->GetAll()) {
            foreach($flds as $key=>$f)
                $result[$key] = $this->pg2exRow($this->_mCfg['fields'], $f);
        }

        return $result;
    }

    /**
     * Возвращает все непрочитанные сообщения пользователя.
     * @see messages::getNewMessages()
     * 
     * @return array
     */
    protected function x____getNewMessages()
    {
        require_once(ABS_PATH.'/classes/messages.php');
        $uid = $this->_sess->_uid;
        $messages = new messages();
        if($msgs = $messages->getNewMessages($uid)) {
            foreach($msgs as $key=>$m) {
                $m['msg_text'] = preg_replace("/%USER_NAME%/", $this->_sess->uname, $m['msg_text']);
                $m['msg_text'] = preg_replace("/%USER_SURNAME%/", $this->_sess->usurname, $m['msg_text']);
                $m['msg_text'] = preg_replace( "/%USER_LOGIN%/", $this->_sess->login, $m['msg_text'] );
                
                $row = $this->pg2exRow($this->_mCfg['fields'], $m);
                if($this->_mCfg['msg_text_length'] > 0) 
                    $row['msg_text'] = substr_quasi($row['msg_text'], 0, $this->_mCfg['msg_text_length']);
                $result[$key] = $row;
            }
        }

        return $result;
    }

    /**
     * Возвращает список контактов пользователя в определенной папке.
     * @see messages::GetContacts()
     * 
     * @param integer $folder   ид. папки.
     * @return array
     */
    protected function x____getContacts($args) {
        list($folder) = $args;
        require_once(ABS_PATH.'/classes/messages.php');
        $folder = (int)$folder;
        $result = NULL;
        $uid = $this->_sess->_uid;
        $limit = $this->_mCfg['page_size'];
        $messages = new messages();
        if($cnts = $messages->GetContacts($uid, $folder, NULL, $limit)) {
            foreach($cnts as $key=>$c) {
                $row = $this->pg2exRow($this->_mCfg['fields'], $c);
                if($row['photo'])
                    $row['photo'] = WDCPREFIX."/users/{$row['login']}/foto/{$row['photo']}";
                $row['role'] = (int)is_emp($row['role']);
                $row['link'] = $GLOBALS['host']."/contacts/?from={$row['login']}";
                $result[$key] = $row;
            }
        }

        return $result;
    }
}
