<?php

/**
 * Основной класс для работы с новыми комментариями
 *
 */

abstract class TComments {
    
    
    /**
     * Максимальный размер файла прикладываемый к комментарию
     */
    const MAX_FILE_SIZE = 2097152;

    /**
     * Максимальное количество файлов прикладываемое к комментарию 
     */
    const MAX_FILE_COUNT = 10;
    
    /**
  	 * Максимальное количество символов в тексте сообщения
  	 *
  	 */
  	const MSG_TEXT_MAX_LENGTH  = 20000;
    
    /**
     * Включить редактор в комментах
     * 
     * @var boolean
     */
    public $enableWysiwyg = true;
    
    /**
     * Переключаемся на новый визивиг или нет
     * @var type 
     */
    public $enableNewWysiwyg = false;
    
    public $configNewWywiwyg = '/scripts/ckedit/config_nocut.js';
    
    /**
     * Включить рейтинги в комментариях
     * 
     * @var boolean
     */
    public $enableRating = true;

    /**
     * Разрешить сворачивать ветки
     *
     * @var boolean
     */
    public $enableHiddenThreads = true;
    
    /**
     * Разрешить скрывать комментарии ниже определенного уровня рейтинга
     *
     * @var boolean
     */
    public $enableHiddenByRating = false;
    
    /**
     * Включить в комментариях предупреждения
     * 
     * @var type 
     */
    public $enableWarningUsers = false;
    
    /**
     * Уровень рейтинга необходимый для того чтобы скрыть комментарий
     * По умолчанию отключен и равен = 0 
     * @see $enableHiddenByRating
     *
     * @var integer
     */
    public $hiddenByRating = 0;

    /**
     * Сообщения об ошибках при отправке формы
     * 
     * @var array 
     */
    public $errors = array();
    
    /**
     * Якорь на комментарий
     * 
     * @var string
     */
    public $anchor = false;

    /**
     * Количество комментариев
     * 
     * @var integer
     */
    public $msg_num = 0;

    /**
     * Путь до шаблонов
     * 
     * @var string
     */
    public $tpl_path = "";
    
    /**
     * Автомодерация false - выключено, true - включено
     * В заивисмости от рейтинга комментария к нему применяется какое-либо действие для отображения
     * 
     * @var boolean
     */
    public $enableAutoModeration = false;

    /**
     * Шаблоны
     * 
     * @var <type>
     */
    protected $templates = array(
        'main' => 'tpl.comments.php',
        'comment' => 'tpl.comment.php',
        'form' => 'tpl.comment-form.php',
    );
    
    /**
     * Подключить новую весртку или нет
     * 
     * @var type 
     */
    public $is_new_template = true;

    /**
     * Название сервиса где используются комментарии (commune, articles, etc..)
     * 
     * @var string 
     */
    protected $_sname;

    /**
     * Идентификатор ресурса, к которому относятся комментарии.
     * 
     * @var integer
     */
    protected $_resource_id;
    
    protected $_options = array(
        'lvt' => null,
        'maxlevel' => 11,
        'readonly' => 0,                                //Отключить возможность комментирования
        'readonly_alert' => 'Функция не доступна',      //Сообщение для алерта в случае, 
        //если возможность комментирования отключена.
    );
    // Шаблоны доступа к комментариям и выводы соотетствующих текстов 
    protected $_access = array(1 => 
                            array('update' => 'Отредактировано модератором',
                                  'delete' => 'Комментарий удален модератором')); 

    /**
     * В переменную записываются свернутые ветки комментариев
     * 
     * @see self::getHiddenThreads();
     * @var array 
     */
    protected $_hidden;

    /**
     * @deprecated Не нашел где эту переменную используют в классе
     * @todo перепроверить и убрать ее
     * @var type 
     */
    protected $_comments_array = array();

    /**
     * В эту перменную записываются данные при операциях над комментариями
     * 
     * @var array 
     */
    protected $_post_msg = array();

    /**
     * Массив с информацией о куках и таблице,
     * в которых хранятся идентификаторы свернутых ветвей комментов
     *
     * @var array
     */
    public static $hiddenThreadsDbConfig = array(
        'adminlog' => array(                     // имя сервиса
            'cookie_name'  => 'adminlogThreads', // имя куки
            'table_name'   => 'admin_log_users', // имя таблицы с пользовательскими данными
            'hidden_field' => 'hidden_threads',  // имя поля с массивом идентификаторов скрытых комментов
            'user_field'   => 'user_id',         // имя поля с ид пользователя
            'id_field'     => 'log_id',          // имя поля с ид лога
        ),
        'articles' => array(                    //имя сервиса
            'cookie_name' => 'articlesThreads',  //имя куки
            'table_name' => 'articles_users',   //имя таблицы с пользовательскими данными
            'hidden_field' => 'hidden_threads', //имя таблицы с массивом идентификаторов скрытых комментов
            'user_field' => 'user_id',          //имя таблицы с ид пользователя
            'id_field' => 'article_id',         //имя таблицы с ид статьи
        ),
        'commune' => array(
            'cookie_name' => 'communeThreads',
            'table_name' => 'commune_users_messages',
            'hidden_field' => 'hidden_threads',
            'user_field' => 'user_id',
            'id_field' => 'message_id',
        )
    );



    /**
     * Конфиг данных для комментариев сервиса.
     * Пример для статей.
     *
     * @return array
     */
    public function model() {
        return array(
            // комментарии
            'comments' => array(
                'table' => 'articles_comments',
                'maxlevel'=>13,
                'fields' => array(
                    'id' => 'id',
                    'resource' => 'article_id',
                    'author' => 'from_id',
                    'parent_id' => 'parent_id',
                    'msgtext' => 'msgtext',
                    'yt' => 'youtube_link',
                    'created_time' => 'created_time',
                    'modified' => 'modified_id',
                    'modified_time' => 'modified_time',
                    'deleted' => 'deleted_id',
                    'rating' => null,
                )
            ),
            // файлы, если аттачи в отдельной таблице
            'attaches' => array(
                'file_table' => 'file',
                'table' => 'articles_comments_files',
                'fields' => array(
                    'comment' => 'comment_id',
                    'file' => 'file_id',
                )
            ),
            // данные просмотров сообщений
//            'users' => array(
//                'table' => 'articles_users',
//                'fields' => array(
//                    'user' => 'user_id',
//                    'resource' => 'article_id',
//                    'lvt' => 'lastviewtime',
//                    'hidden' => 'hidden_threads'
//                )
//            )
        );
    }

    /**
     *
     * @param <type> $id        Идентификатор ресурса, к которому относятся комментарии.
     * @param <type> $lvt       Время последнего просмотра ресурса пользователем
     * @param <type> $options   Дополнительные параметры
     */
    public function __construct($id = null, $lvt = null, $options = array()) {

//        if(!$id)
//            throw new Exception();
        if($this->is_new_template) {
            $this->templates['comment'] = 'tpl.new.comment.php';
            
        }
        if($this->enableAutoModeration) {
            $this->auto_mod = $this->getAdapterAutoModeration();
        }
        $this->getSname();
        $this->_resource_id = $id;
        $this->_options = $options;
        $this->last_view_time = $lvt;
        $this->setModAccess($options);
        $this->checkCommentsThreads($_COOKIE);
        $this->_hidden = $this->getHiddenThreads();
        
        if(method_exists($this, 'init'))
            $this->init();

        $task = __paramInit('string', 'cmtask', 'cmtask');

        switch($task) {
            case 'add':
            case 'edit':
                $this->errors =  $this->checkInput( ($task == 'edit') );
                break;
            case 'delete':
                $this->errors =  $this->deleteComment();
                break;
            case 'restore':
                $this->errors =  $this->restoreComment();
                break;
        }

        $this->_task = $task;

    }

    /**
     * Выводит все комментарии на страницу
     * 
     * @return string HTML код 
     */
    public function render() {
        $uid = get_uid(false);
        $comments = $this->getData();
        $this->msg_num = count($comments);
        $comments = array(
            'children' => array2tree($comments, 'id', 'parent_id', true)
        );
        $comments_html = $this->msg_nodes($comments);
        $form = $this->renderForm();

        ob_start();
        include($this->tpl_path . $this->templates['main']);
        return ob_get_clean();
    }


    /**
     * Диалог. Печатает ветку комментариев.
     *
     * @param array $msg   родительский узел с элементом 'children' -- массив дочерних узлов.
     * @param boolean $need_box   нужно ли заворачивать в <ul>
     */
    function msg_nodes($msg, $need_box = true) {
        if(!$msg['children']) return;
        ob_start();
        if($need_box)                     echo '<ul class="cl-ul">';
        foreach($msg['children'] as $msg)  $this->msg_node($msg);
        if($need_box)                     echo '</ul>';
        return ob_get_clean();
    }

    /**
     * Диалог. Печатает один комментарий.
     *
     * @param array $msg   информация по комментарию.
     * @return string
     */
    function msg_node($msg) {
        if ($msg["msgtext"]) {
            validate_code_style($msg["msgtext"]);
        }
        global $session;
        static $pos = 0;
        static $prev_post_time = 0;
        $model = $this->model();
        $max_level = $this->_options['maxlevel']?$this->_options['maxlevel']:13;
        if(!isset($msg['post_date'])) $msg['post_date'] = $msg['created_time'];
        $post_time = strtotime($msg['post_date']);
        $li_in_cls = (!$msg['level'] ? ' cl-li-first' : '')
                    .($post_time > strtotime($this->last_view_time) ? ' cl-li-new' : '')
                    .($msg['is_admin']=='t' ? ' nr-ua' : '');
        $msg['is_new'] = $post_time > strtotime($this->last_view_time);
        $msg['is_permission'] = isset($this->_options['is_permission'])? $this->_options['is_permission'] : false;
        if(!isset($msg['access']) || (int) $msg['access'] <= 0) {
            $msg['access'] = $this->_access[1]; // По умолчанию
        } else {
            $msg['access'] = $this->_access[$msg['access']];
        }
        $hidden_cls = "";
        if ((!in_array(-1, $this->_hidden) && in_array($msg['id'], $this->_hidden))
            || (in_array(-1, $this->_hidden) && !in_array($msg['id'], $this->_hidden))) {
            $hidden_cls .= ' cl-li-hidden-c ';
        }

//        $is_edit = $this->post_msg['id'] == $msg['id'];
//        if($this->post_msg && $is_edit || !$this->post_msg['id'] && $this->post_msg['parent_id'] == $msg['id']) {
//            if($is_edit) {
//                // нужно выдать форму для редактирования (если были ошибки ввода). Заполняем post_msg недостающими данными по комменту.
//                foreach($msg as $f=>$v) {
//                    if(!isset($this->post_msg[$f]))
//                        $this->post_msg[$f] = $v;
//                }
//            }
//            $edit_form = $this->msg_form($this->post_msg, $this->error['msgs'], true);
//        }
        
        if($this->_post_msg['parent_id'] && count($this->errors) && $msg['id'] == $this->_post_msg['parent_id']) {
            $edit_form = $this->renderForm();
        }
        if($this->enableHiddenByRating && ($msg['rating']*-1) >= $this->hiddenByRating && $this->enableRating) {
            $hidden_cls = 'cl-li-hidden';
            $msg['hiddenRating'] = true;
        }
    ?>
         <a name="c_<?= $msg['id'] ?>"></a>
         <li class="cl-li<?=(!$msg['level'] ? ' first' : '')?> <?= $hidden_cls ?>" id="c__<?=$msg['id']?>">
            <?if(!$this->is_new_template){?><div class="cl-li-in <?=$li_in_cls?>"><?}?>
                <?=$this->msg_node_content($msg)?>
            <?if(!$this->is_new_template){?></div><?}?>
            <div id="msg_form_box<?=$msg['id']?>"><?=$edit_form?></div>
            <? if($msg['level'] < $max_level) { ?>
                <?=$this->msg_nodes($msg)?>
            <? } ?>
        </li>
        <? if($msg['level'] >= $max_level) { ?>
            <?=$this->msg_nodes($msg, false)?>
        <? } ?>
    <?
        ++$pos;
    }

    /**
     * Диалог. Печатает содержимое комментария.
     * @see msg_node()
     *
     * @param array $msg   информация по комментарию.
     * @return string
     */
    function msg_node_content($msg) {
        global $session; //, $stop_words;
        $uid = get_uid(false);

        if($msg['moduser_id']) {
            $mod_a = $msg['moduser_id'] != $msg['user_id'] ? 'a' : 'u';
            $mod_alt = ($mod_a=='a' ? 'Отредактировано Администрацией: ' : 'Внесены изменения: ') . date('d.m.Y | H:i', strtotime($msg['modified']));
        }
        $data = $msg;

        $wordlength = 45;
        if($data['parent_id']) $wordlength = 45 - ceil(45 * ( ($data['level'] > 11 ? 11 : $data['level']) /20));

        $is_hidden = (!in_array(-1, $this->_hidden)&&in_array($msg['id'], $this->_hidden))
                || (in_array(-1, $this->_hidden)&&!in_array($msg['id'], $this->_hidden));


        $rating_class = $msg['rating'] < 0 ? 'pr-minus' : ($msg['rating'] >= 1 ? 'pr-plus' : '') ;
        
        /*if ( isset($stop_words) && isset($data['moderator_status']) ) {
            $data['msgtext'] = $data['moderator_status'] === '0' ? $stop_words->replace($data['msgtext']) : $data['msgtext'];
        }*/

        ob_start();
        include($this->tpl_path . $this->templates['comment']);
        return ob_get_clean();
    }


    /**
     * Выводит форму для создания, редактирования комментариев
     * 
     * @return string HTML Форма
     */
    protected function renderForm() {
        if($this->_form_set || $this->_options['readonly']) return false;
        
        $alert = count($this->errors) ? $this->errors : null;
        
        if($this->_post_msg) {
            $msg = $this->_post_msg['msgtext'];
            $reply = $this->_post_msg['parent_id'];
        }

//        var_dump($this->_post_msg);

        $this->_form_set = true;

        ob_start();
        include($this->tpl_path . $this->templates['form']);
        return ob_get_clean();
    }

    /**
     * Получение данных сообщений, либо одного сообщения по ID
     * 
     * @param integer $message_id 
     * @return array
     */
    public function getData($message_id = NULL) {
        $model = $this->model();

        $resource_id = $this->_resource_id;
        if (isset($model['resource_id'])) $resource_id = $model['resource_id'];

        $fields = array();

        $sql_fields = array();
        foreach($model as $tid => $table) {
            if($tid == 'attaches' || !is_array($table['fields'])) continue;
            if($tid == 'users' && !get_uid(false)) continue;
            
            $fields[$table['table']] = array();
            foreach($table['fields'] as $k => $v) {
                if(!$v) continue;
                $fields[$tid][$k] = str_replace("ONLY ", "", $table['table']) . "." . $v;

                if (isset($model[$tid]['expr'][$k]))
                    $sql_fields[] = $model[$tid]['expr'][$k] . " AS {$k}";
                else
                    $sql_fields[] = str_replace("ONLY ", "", $table['table']) . "." . $v . " AS {$k}";
            }
        }

        $sql_fields_author = array(
            'u_auth.uid AS author_uid',
            'u_auth.login AS author_login',
            'u_auth.uname AS author_uname',
            'u_auth.usurname AS author_usurname',
            'u_auth.photo AS author_photo',
            'u_auth.is_banned AS author_is_banned',
            'u_auth.is_pro AS author_is_pro',
            'u_auth.is_profi AS author_is_profi',
            'u_auth.is_verify AS author_is_verify',
            'u_auth.is_team AS author_is_team',
            'u_auth.is_pro_test AS author_is_pro_test',
            'u_auth.role AS author_role',
            'u_auth.warn AS warn',
            'u_auth.reg_date AS author_reg_date'
        );
        $sql_fields_mod = array(
            'u_mod.uid AS mod_uid',
            'u_mod.login AS mod_login',
            'u_mod.uname AS mod_uname',
            'u_mod.usurname AS mod_usurname',
            'u_mod.role AS mod_role',
        //добавил выборку данных удалившего комментарий пользователя, т. к. modify может быть пусто как при удалении модератором из модераторской, так и при удалении со страницы сообщества
            'u_mod2.uid AS mod_uid_del',
            'u_mod2.login AS mod_login_del',
            'u_mod2.uname AS mod_uname_del',
            'u_mod2.usurname AS mod_usurname_del',
            'u_mod2.role AS mod_role_del'
        );
        $sql_fields_inner = array();
        if(!empty($model['users']['inner_fields'])) {
            foreach($model['users']['inner_fields'] as $name_field=>$as_name) {
                $sql_fields_inner[] = "{$name_field} as {$as_name}";
            }
        }
        $sql_fields = implode(", ", array_merge($sql_fields, $sql_fields_author, $sql_fields_mod, $sql_fields_inner));

        if($model['comments']['set']) {
            $sql[] = $model['comments']['set'] . ';';
        }

        $sql[] = "SELECT {$sql_fields} FROM " . $model['comments']['table'];

        if (isset($model['users']) && get_uid(false))
            $sql[] = "LEFT JOIN " . $model['users']['table'] . " ON " 
                    . $fields['comments']['id'] . " = " . $fields['users']['comment'] . " AND "
                    . $fields['users']['user'] . " = " . get_uid(false);
        
        // Данные автора комментария
        $sql[] = "LEFT JOIN users as u_auth ON u_auth.uid = " . $fields['comments']['author'];
        // Данные модератора или админа, редактировавшего коммент
        $sql[] = "LEFT JOIN users as u_mod ON u_mod.uid = " . $fields['comments']['modified'];
        // Данные модератора или админа, удалившего коммент
        $sql[] = "LEFT JOIN users as u_mod2 ON u_mod2.uid = " . $fields['comments']['deleted'];
        if(!empty($model['users']['inner'])) {
            $sql[] = implode(" \n ", $model['users']['inner']);
        }
        $sql[] = "WHERE " . $fields['comments'][ !$message_id ? 'resource' : 'id'] . " = ? ";

        if (isset($model['comments']['where'])) {
            $sql[] = "AND " . implode(" AND " ,$model['comments']['where']);
        }
        $sql[] = "ORDER BY " . $fields['comments']['created_time'];
        
        $sql = implode(" \n ", $sql);

        $DB = new DB('master');
        $res = $DB->rows($sql, !$message_id ? $resource_id : $message_id);
        

        $this->msg_num = count($res);

        $comment_ids = array();
        $comment_arr = array();
        if ($this->msg_num) {
            foreach ($res as $row) {
                $comment_ids[] = $row['id'];
                $comments_arr[$row['id']] = $row;
            }
        }

        // Выбираем файлы
        if (count($comment_ids) && isset($model['attaches'])) {
            $tbl = $model['attaches']['table'];
            $fl_tbl = $model['attaches']['file_table'];
            if($tbl == $fl_tbl) {
                if ($model['attaches']['fields']['inline']) {
                    $where = " $tbl.".$model['attaches']['fields']['inline']." = FALSE";
                }
                
                $res = CFile::selectFilesBySrc($tbl, $comment_ids, 'id', $where);
            } else {
                $exclude_inline = '';
                if ($model['attaches']['fields']['inline']) {
                    $exclude_inline = " AND $tbl.".$model['attaches']['fields']['inline']." = FALSE";
                }
                $sql = array();

                $sql[] = "SELECT * FROM " . $tbl;
                $sql[] = "INNER JOIN {$fl_tbl} file ON file.id = $tbl." . $model['attaches']['fields']['file'];
                $sql[] = "WHERE $tbl." . $model['attaches']['fields']['comment'] . " IN (" . implode(', ', $comment_ids) . ")$exclude_inline";

                $sql = implode(" \n ", $sql);
                $res = $DB->rows($sql);
            }
            foreach($res as $file) {
                $comments_arr[$file[$model['attaches']['fields']['comment']]]['attach'][] = $file;
            }
        }

        $ret = $comments_arr;
        if ($message_id && count($comments_arr)) {
            $ret = array_shift($ret);
        }
        if(!count($ret)) $ret = array();

        return $ret;
    }
    
    /**
     * Функция создания/обновления комментария
     * 
     * @param array   $params  Данные на сохранение
     * @param integer $cid     Ид комментария для редактирования    
     * @param integer $author  UID автора комментария
     * @return boolean 
     */
    protected  function save($params = array(), $cid = null, $author = 0) {
        $DB = new DB('master');
        $model = $this->model();
        
        if ($this->_options['readonly']) {
            return false;
        }

        $insert_fields = array();
        $insert_data = array();
        
        validate_code_style($params["msgtext"]);#0024876
        
        foreach($model['comments']['fields'] as $k => $v) {
            if(isset($params[$k])) {
                $insert_fields[] = $v;
                $insert_data[] = $params[$k];
            }
        }

        if (!$cid) {
            if (isset($model['comments']['fields']['created_time'])) {
                $insert_fields[] = $model['comments']['fields']['created_time'];
                $insert_data[] = 'NOW()';
            }
            
            $sModFld = !empty($model['comments']['fields']['moderator_status']) ? ', '.$model['comments']['fields']['moderator_status'] : '';
            $sModVal = !empty($model['comments']['fields']['moderator_status']) ? ', '.(is_pro() ? 'NULL' : '0') : '';
            
            // Новый комментарий
            $insert_table = ( $model['comments']['insert_table'] != '' ? $model['comments']['insert_table'] : $model['comments']['table'] ); 
            $sql[] = "INSERT INTO " . $insert_table . " (" . implode(", ", $insert_fields) . "$sModFld) ";
            $sql[] = "VALUES ('" . implode("', '", $insert_data) . "'$sModVal) ";
            $sql[] = "RETURNING " . $model['comments']['fields']['id'];
        } else {
            // Обновление коммента
            $sql[] = "UPDATE " . $model['comments']['table'] . " SET ";

            if (isset($model['comments']['fields']['modified']) && isset($model['comments']['fields']['modified_time'])) {
                $insert_fields[] = $model['comments']['fields']['modified'];
                $insert_data[] = get_uid(false);
                $insert_fields[] = $model['comments']['fields']['modified_time'];
                $insert_data[] = 'NOW()';
            }
            
            if ( $author == get_uid(false) && !$model['permissions'] && !empty($model['comments']['fields']['moderator_status']) && !empty($model['moderation_rec_type']) && !is_pro() ) {
                /*require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );
                $insert_fields[] = $model['comments']['fields']['moderator_status'];
                $insert_data[] = '0';
                $stop_words    = new stop_words();
                $nStopWordsCnt = $stop_words->calculate( $params[$model['comments']['fields']['msgtext']] );
                $nSortOrder    = !empty($model['moderation_sort_order']) ? $model['moderation_sort_order'] : 3;
                $GLOBALS['DB']->insert( 'moderation', array('rec_id' => $cid, 'rec_type' => $model['moderation_rec_type'], 'stop_words_cnt' => $nStopWordsCnt, 'sort_order' => $nSortOrder) );*/
            }

            $update_sql = array();
            foreach($insert_fields as $i => $field) {
                if ($field != $model['comments']['fields']['msgtext'] &&
                    $field != $model['comments']['fields']['modified'] &&
                    $field != $model['comments']['fields']['modified_time'] &&
                    $field != $model['comments']['fields']['moderator_status'] &&
                    $field != $model['comments']['fields']['yt'] &&
                    $field != $model['comments']['fields']['access']) continue;
                $update_sql[] = $field . " = '{$insert_data[$i]}'";
            }
            $sql[] = implode(", ", $update_sql);
            $sql[] = "WHERE " . $model['comments']['fields']['id'] . " = " . $cid;
            $sql[] = "RETURNING " . $model['comments']['fields']['id'];
        }

        $sql = implode(" ", $sql);
        
        if(($res = $DB->squery($sql)) && pg_affected_rows($res)) {
            list($newid) = pg_fetch_row($res);
            
            if ( !$cid && $sModFld && !is_pro() ) {
                /*require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );
                $stop_words    = new stop_words();
                $nStopWordsCnt = $stop_words->calculate( $params[$model['comments']['fields']['msgtext']] );
                $nSortOrder    = !empty($model['moderation_sort_order']) ? $model['moderation_sort_order'] : 3;
                $GLOBALS['DB']->insert( 'moderation', array('rec_id' => $newid, 'rec_type' => $model['moderation_rec_type'], 'stop_words_cnt' => $nStopWordsCnt, 'sort_order' => $nSortOrder) );*/
            }
        }
        
        if (count($params['rmattaches']) && $cid) {

            $file = new CFile();
            $file->table = $model['attaches']['file_table'];

            // TODO добавить проверку идентификаторов аттачей к комменту $cid, пропускать, если левый
            foreach($params['rmattaches'] as $attach) {
//                if(!isset($comment_attaches[$attach])) continue;
                $file->Delete($attach);
            }
        }


        if(isset($model['attaches']) && isset($params['attaches']) && count($params['attaches'])) {
            if($model['attaches']['table'] == $model['attaches']['file_table']) {
                foreach($params['attaches'] as $file) {
                    $p = array();
                    foreach($model['attaches']['fields'] as $k => $v) {
                        switch($k) {
                            case 'small':
                                $p[$v] = $file['tn'] == 2 ? true : false;
                                break;;
                            case 'file':
                                $p[$v] = $file['f_id'];
                                break;
                            case 'comment':
                                $p[$v] = $newid;
                                break;
                            case 'temp':
                                $p[$v] = false;
                                break;
                            case 'inline':
                                $p[$v] = false;
                                break;
                            case 'sort':
                                $p[$v] = intval($v);
                                break;
                        }
                    }
                    
                    $cfile = new CFile($file['f_id']);
                    $cfile->table = $model['attaches']['table'];
                    $cfile->updateFileParams($p, false);
                }
            } else {
                $insert_fields = array();
                $insert_data = array();

                foreach($model['attaches']['fields'] as $k => $v) {
                    $insert_fields[] = $v;
                }

                $sql = "INSERT INTO " . $model['attaches']['table'] . " (" . implode(', ', $insert_fields) . ") VALUES ";

                $insert_sql = array();
                foreach($params['attaches'] as $file) {
                    $f_sql = array();
                    foreach($model['attaches']['fields'] as $k => $v) {
                        switch($k) {
                            case 'small':
                                $f_sql[] = $file['tn'] == 2 ? "'t'" : "'f'";
                                break;;
                            case 'file':
                                $f_sql[] = $file['f_id'];
                                break;
                            case 'comment':
                                $f_sql[] = $newid;
                                break;
                            case 'temp':
                                $f_sql[] = "'f'";
                                break;
                            case 'inline':
                                $f_sql[] = "'f'";
                                break;
                            case 'sort':
                                $f_sql[] = intval($v);
                                break;
                            default:
                                $f_sql[] = __paramValue('string', $v);
                        }
                    }
                    if(count($f_sql) > 1) {
                        $insert_sql[] = "(" . implode(", ", $f_sql) . ")";
                    }
                }

                if(count($insert_sql)) {
                    $sql .= implode(", ", $insert_sql);

                    $DB->squery($sql);
                }
            }
        }
        $this->checkWysiwygInlineImages($newid, $params['msgtext'], $cid);
        return $newid;
    }

    /**
     * При создании и изменении текста комментария проверяет, на все ли загруженые при наборе текста в визивиге
     * изображения есть ссылки в тексте комментария, если не на все, удаляет лишние.
     * При создании комментария обновляет cid (ID Сообщения сообщества) записи в commune_attaches
     * @param $messageId - номер записи в commune_messages 
     * @param $text      - текст комментария 
     * @param $cid       - идентификатор комментария в commune_messages
     * */
    function checkWysiwygInlineImages($messageId, $text, $cid) {
    	session_start();
    	$filesIds = $_SESSION['wysiwyg_inline_files']; //получить id вставленных при наборе текста файлов
    	global $DB;
    	$model = $this->model();
    	//если в таблице есть поля для хранения флага временного файла и флага файла в визивиге
    	if ($model['attaches']['fields']['temp'] && $model['attaches']['fields']['inline']) {
    		//получаем все теги img из соообщения
            $text = str_replace("<cut>", "", $text); // DOM не любит этот тег
    	    $dom = new DOMDocument();
    	    $dom->validateOnParse = false;
    	    $dom->loadHTML($text);
    	    $images = $dom->getElementsByTagName('img');
    	    $w_files = array();    //файлы, ссылки на которые есть в wisywyg
    	    for ($i = 0; $i < $images->length; $i++) {
    	    	$filePath = $images->item($i)->getAttribute('src');    	    	
    	    	$filePath = str_replace(WDCPREFIX."/", "", $filePath);
    	    	$file = new CFile($filePath, $model['attaches']['file_table']);
    	    	if ($file->id) {
    	        	$w_files[$file->id] = $file->id;
    	    	}
    	    }
    	    if ($cid) {//если комментарий редактируется, добавим к идентификаторам вновь вставленных в визивиг файлов идентификаторв ранее вставленных
    	        $cmd = "SELECT {$model['attaches']['fields']['file']} FROM {$model['attaches']['table']}
    	        			WHERE {$model['attaches']['fields']['comment']} = $cid AND {$model['attaches']['fields']['inline']} = TRUE";
    	    	$rows = $DB->rows($cmd);
    	        foreach ($rows as $row) {
    	        	$filesIds[$row[$model['attaches']['fields']['file']]] = $row[$model['attaches']['fields']['file']];
    	        }
    	    }
            if(!$filesIds) return;
    	    //удалить из $filesIds те, ссылок на которые нет в тексте визивига
    	    foreach ($filesIds as $id) {
    	    	if (!$w_files[$id]) {
    	    		$cfile = new CFile($id, $model['attaches']['file_table']);
    	    		if ($cfile->id) {
    	    			$cfile->delete($id);
    	    		}	
    	    		unset($filesIds[$id]);
    	    	}
    	    }
    	    $ids = join(',', $filesIds);
    	    if (count($filesIds)) {
    	    	$cmd = "UPDATE {$model['attaches']['table']} 
	    	    SET {$model['attaches']['fields']['comment']} = {$messageId},
	    	         temp = FALSE
	            WHERE {$model['attaches']['fields']['file']} IN ( $ids )";
	    	    $DB->query($cmd);
    	    }
    	}
        $_SESSION['wysiwyg_inline_files'] = array();
    }
    
    /**
     * Удаление комментария, настоящего удаления не происходит, в базу пишется флаг удаленности комментария
     * 
     * @param inetger $id ИД комментария
     * @param inetger $author UID автора комментария
     * @param boolean $from_stream true - удаление из потока, false - на сайте
     * @return boolean 
     */
    public function delete( $id, $author = 0, $from_stream = false ) {
        $DB = new DB('master');
        $model = $this->model();
        
        if ($this->_options['readonly']) {
            return false;
        }
        
        if ( !isset($model['comments']['fields']['deleted']) ) return false;

        $sql[] = "UPDATE " . $model['comments']['table'] . " SET ";
        
        $uid  = get_uid(false);
        $flds = $model['comments']['fields']['deleted'] . " = " . $uid;
        
        if ( !empty($model['comments']['fields']['moderator_status']) && !empty($model['moderation_rec_type']) ) {
            $flds .= ', ' . $model['comments']['fields']['moderator_status'] . ' = ' . ( $author == $uid ? 'NULL' : $uid );
            if ( !$from_stream ){
                $DB->query( 'DELETE FROM moderation WHERE rec_id = ?i AND rec_type = ?i', $id, $model['moderation_rec_type'] );
            }
        }
        
        if (isset($model['comments']['fields']['deleted_time'])) {
            $flds .= ", " . $model['comments']['fields']['deleted_time'] . " = NOW()";
        }
        if(isset($model['comments']['fields']['access'])) {
            $flds .= ", " . $model['comments']['fields']['access'] . " = ". (int) $this->_options['access'];
        }
        $sql[] = $flds;
        
        $sql[] = "WHERE " . $model['comments']['fields']['id'] . " = " . $id;
        $sql[] = "RETURNING " . $model['comments']['fields']['id'];

        $sql = implode(" ", $sql);

        if(($res = $DB->squery($sql)) && pg_affected_rows($res)) {
            list($newid) = pg_fetch_row($res);

            return $newid;
        }

        return false;
    }
    
    /**
     * Восстановление удаленного комментария
     * 
     * @param integer $id  ИД комментария 
     * @param integer $author UID автора комментария 
     * @return boolean 
     */
    public function restore( $id, $author = 0 ) {
        $DB = new DB('master');
        $model = $this->model();
        
        if ($this->_options['readonly']) {
            return false;
        }

        if ( !isset($model['comments']['fields']['deleted']) ) return false;

        $sql[] = "UPDATE " . $model['comments']['table'] . " SET ";

        $flds = $model['comments']['fields']['deleted'] . " = NULL";
        if (isset($model['comments']['fields']['deleted_time'])) {
            $flds .= ", " . $model['comments']['fields']['deleted_time'] . " = NULL";
        }
        
        if ( ($author == get_uid(false) || !$model['permissions']) && !empty($model['comments']['fields']['moderator_status']) && !is_pro() ) {
            $flds .= ', ' . $model['comments']['fields']['moderator_status'] . ' = 0';
        }
        
        $sql[] = $flds;

        $sql[] = "WHERE " . $model['comments']['fields']['id'] . " = " . $id;
        $sql[] = "RETURNING " . $model['comments']['fields']['id']. ', '. $model['comments']['fields']['msgtext'];

        $sql = implode(" ", $sql);

        if(($res = $DB->squery($sql)) && pg_affected_rows($res)) {
            list( $newid, $msgtext ) = pg_fetch_row( $res );
            
            if ( ($author == get_uid(false) || !$model['permissions']) && !empty($model['comments']['fields']['moderator_status']) && !is_pro(true, $author) ) {
                /*require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );
                $stop_words    = new stop_words();
                $nStopWordsCnt = $stop_words->calculate( $msgtext );
                $nSortOrder    = !empty($model['moderation_sort_order']) ? $model['moderation_sort_order'] : 3;
                $GLOBALS['DB']->insert( 'moderation', array('rec_id' => $id, 'rec_type' => $model['moderation_rec_type'], 'stop_words_cnt' => $nStopWordsCnt, 'sort_order' => $nSortOrder) );*/
            }

            return $newid;
        }

        return false;
    }

    /**
     * Проверка передаваемых данных для сохранения/изменения комментария
     * 
     * @param boolean $edit_mode   Флаг указывающий какие данные на проверке при создании или при редактировании, 
     *                             для проверки валидности автора к редактированию комментария
     * @return type 
     */
    protected function checkInput($edit_mode = false) {
        $uid = get_uid(false);
        
        if(!$uid) {
            header("Location: /fbd.php");
            die();
        }
        
        $tn = 0;
        if ($this->enableWysiwyg) {
            if($this->enableNewWysiwyg) {
                $msg = __paramValue('ckedit', antispam($_POST['cmsgtext']));
                //$msg = __paramValue('ckedit_nocut', antispam($_POST['cmsgtext']));
            } else {
                $msg = __paramValue('wysiwyg_tidy', antispam($_POST['cmsgtext']));
            }
        } else {
            $msg = change_q_x(antispam(stripslashes($_POST['cmsgtext'])), false, false, 'b|br|i|p|ul|li|cut|s|h[1-6]{1}', false, false);
        }
        $reply = __paramInit('int', null, 'parent_id', NULL);
        $order_type = __paramInit('int', null, 'ord');
        $thread = __paramInit('int', 'id');
        $rmatt = $_POST['rmattaches'];

        $no_redirect = $_POST['no_redirect'];

        if($edit_mode) {
            $mod = $this->_options['is_permission'];
            $comment = $this->getData($reply);

            if(!$mod && $comment['author'] != get_uid(false)) {
                header("Location: /fbd.php");
                die();
            }
        }
        
        // загрузка файлов
        $files = array();
        $attach = $_FILES['attach'];
        if (is_array($attach) && !empty($attach['name'])) {
            foreach ($attach['name'] as $key=>$v) {
                if (!$attach['name'][$key] || $key > self::MAX_FILE_COUNT) continue;
                $_POST['is_attached'] = true;
                $files[] = new CFile(array(
                    'name'     => $attach['name'][$key],
                    'type'     => $attach['type'][$key],
                    'tmp_name' => $attach['tmp_name'][$key],
                    'error'    => $attach['error'][$key],
                    'size'     => $attach['size'][$key]
                ));
                if ( $attach['size'][$key] == 0 ) {
                    $alert['attach'] = "Пустой файл";
                }
            }
        }
        $yt_link = $_POST['yt_link'];
        if((!$msg || is_empty_html($msg)) && !$_POST['is_attached'] && $yt_link == '') {
            $alert['msgtext'] = 'Поле не должно быть пустым';
        } elseif(strlen_real($msg) > self::MSG_TEXT_MAX_LENGTH ) {
            $alert['msgtext'] = 'Количество символов превышает допустимое';
        } elseif ($this->enableWysiwyg) {
            /*$tidy = new tidy();
            $msg = $tidy->repairString(
                $msg,
                array(
                    'fix-backslash' => false,
                    'show-body-only' => true,
                    'bare' => true,
                    'preserve-entities' => true,
                    'wrap' => '0'),
                'raw');*/
            $msg = str_replace("\n", "", $msg);
            $msg = preg_replace("/\h/", " ", $msg);
        } 
        if ($yt_link != '') {
            $v_yt_link = video_validate($yt_link);
            if(!$v_yt_link) {
                $alert['yt_link'] = "Неверная ссылка.";
            } else {
                $yt_link = $v_yt_link;
            }
        } else {
            $yt_link = null;
        }
        
        $model = $this->model();

        list($att, $uperr, $error_flag) = $this->UploadFiles($files, array('width' => 390, 'height' => 1000, 'less' => 0), '', $model['attaches']['file_table']);
        if($uperr) {
            $alert['attach'] = $uperr;
            $att = $comment['attach'];
        }
        $this->_post_msg = array(
            'resource' => $this->_resource_id,
            'parent_id' => $reply,
            'author' => get_uid(false),
            'msgtext' => $msg,
            'yt' => ($edit_mode && !$yt_link ? "" : $yt_link),
            'attaches' => $att,
            'rmattaches' => $rmatt
        );
        if(!isset($alert)) {
            $new = $this->save($this->_post_msg, $edit_mode ? $comment['id'] : null, $comment['author']);
            
            // если автор комментария прикрепляет новые файлы - на модерирование
            // пока не используется - на модерирование сразу при insert/update
            /*if ( $edit_mode && $new && $files && $comment['author'] == $uid 
                && $model['comments']['fields']['moderator_status'] && $model['moderation_rec_type'] 
            ) {
                $GLOBALS['DB']->query( 'UPDATE ' . $model['comments']['table'] 
                    . ' SET ' . $model['comments']['fields']['moderator_status'] .' = 0' 
                    . ' WHERE ' . $model['comments']['fields']['id'] .' = ?i', $comment['id'] );
                $GLOBALS['DB']->query( 'DELETE FROM moderation WHERE rec_id = ?i AND rec_type = ?i', 
                    $comment['id'], $model['moderation_rec_type'] );
            }*/
            
            if($new && !count($this->errors) && !$no_redirect) {
                // Сделано в связи с тем что IE(любой версии) не понимает #anchor, если делать в PHP header()
                $_SESSION['c_new_id'] = intVal($new);
                $parse = parse_url($_SERVER['HTTP_REFERER']);
                $location = $parse['path'] . '?' .url($_SERVER['HTTP_REFERER'], array('r' => rand(1,1000)));
                header("Location: {$location}", true, 303);
                exit();
            }
        }
        else {
            if ( $edit_mode ) {
                $this->_post_msg['attaches'] = $comment['attach'];
            }
            else {
                $this->_post_msg['attaches'] = null;
            }
        }
        return $alert;
    }

    /**
     * Функция обработки данных перед удаления комментария
     * 
     * @return type 
     */
    protected function deleteComment() {
        $id = __paramInit('int', 'cmid');
        $anchor = '';
        if($this->anchor !== false) {
            $anchor = $this->anchor.$id;
        }
        $mod = $this->_options['is_permission'];
        $comment = $this->getData($id);
        $model = $this->model();

        if($comment['deleted']) {
            return;
        }

        if(!$mod && $comment['author'] != get_uid(false) || $this->_options['readonly']) {
            header("Location: /fbd.php");
            die();
        }

        if ($cid = $this->delete($id, $comment['author'])) {
            $this->sendCommentDeleteWarn( $comment );
            header("Location: ?" . url($_GET, array('cmtask' => null, 'cmid' => null)) . $anchor);
            die();
        }
    }
    
    /**
     * Если нужно отсылает уведомление об удалении комментария пользователя
     * 
     * @param array $aComment массив данных удаляемого коментария.
     */
    public function sendCommentDeleteWarn( $aComment ) {
        if ( isset($this->sendDeleteWarn) && $this->sendDeleteWarn ) {
        	if ( $aComment['author'] != get_uid(false) ) {
        	    $aSearch  = array( '{host}' );
        	    $aReplace = array( $_SERVER['HTTP_HOST'] );
        	    $aTmp     = array_keys( $aComment );
        	    
        	    foreach ($aTmp as $sKey) {
        	    	$aSearch[]  = '{'. $sKey .'}';
        	    	$aReplace[] = $aComment[ $sKey ];
        	    }
        	    
        	    $sLink = str_replace( $aSearch, $aReplace, $this->urlTemplate );
                $aData = array(
                    'user_id'   => $aComment['author'],
                    'msg'       => $aComment['msgtext'],
                    'post_time' => $aComment['created_time'],
                    'link'      => $sLink
                );
                
                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/messages.php' );
                if ($this->_sname === 'Commune') {
                    /*$aData['commune_id'] = $aComment['resource_id'];
                    messages::sendCommuneCommentDeleteWarn($aData);*/
                } else {
                    messages::sendCommentDeleteWarn( $aData );
                }
        	}
        }
    }
    
    /**
     * Функция проверки данных перед восстановление комментария
     * 
     * @return boolean 
     */
    protected function restoreComment() {
        $id = __paramInit('int', 'cmid');
        
        if ($this->_options['readonly']) {
            return false;
        }
        
        $anchor = '';
        if($this->anchor !== false) {
            $anchor = $this->anchor.$id;
        }
        
        $mod = $this->_options['is_permission'];
        $comment = $this->getData($id);
        $model = $this->model();
        
        if ( !$comment['deleted'] || ($data['mod_access'] && $data['is_permission'] > $data['mod_access']) ) {
            return;
        }

        // если комментарий удалил автор комментария, то восстановить его уже нельзя
        if ($comment['author'] == $comment['mod_uid_del']) {
            return;
        }

        if(!$mod && $comment['author'] != get_uid(false)) {
            header("Location: /fbd.php");
            die();
        }

        if ($cid = $this->restore($id, $comment['author'])) {
            header("Location: ?" . url($_GET, array('cmtask' => null, 'cmid' => null)) . $anchor);
            die();
        }
    }

    /**
     * Возвращает свернутые ветки комментариев
     * 
     * @return array
     */
    protected function getHiddenThreads() {
        $sName = strtolower($this->_sname);
        $uid   = isset($_SESSION['uid']) ? $_SESSION['uid'] : NULL;
        
        if ( $uid && $this->_resource_id && isset(self::$hiddenThreadsDbConfig[ $sName ]) ) {
            $DB     = new DB( 'master' );
            $config = self::$hiddenThreadsDbConfig[ $sName ];
            $sql    = "SELECT {$config['hidden_field']} FROM {$config['table_name']}  
                WHERE {$config['id_field']} = {$this->_resource_id} AND {$config['user_field']} = $uid";
            
            if ( $mHidden = $DB->val($sql) ) {
            	$mHidden = preg_replace( '/[\{\}]/', '', $mHidden );
                $mHidden = explode( ',', $mHidden );
                
                return $mHidden;
            }
        }
        
        return array();
    }
    /**
     * Проверяет в куках наличие идентификаторов свернутых веток комментов.
     *
     * @param array $cookies массив с куками
     */
    public static function checkCommentsThreads($cookies) {
        session_start();
        $DB  = new DB( 'master' );
        $uid = isset($_SESSION['uid']) ? $_SESSION['uid'] : NULL;
        if(!$uid) return FALSE;

        foreach(self::$hiddenThreadsDbConfig as $sname => $config) {
            $cookie_name = $config['cookie_name'];
            if (isset($cookies[$cookie_name])) {
                $articles = json_decode(stripslashes($cookies[$cookie_name]), 1);
                if (!$articles)
                    return FALSE;

                if (is_array($articles)) {
                    foreach ($articles as $id => $hidden) {
                        $sql = "SELECT {$config['hidden_field']} FROM {$config['table_name']}
                                    WHERE {$config['id_field']} = ?i AND {$config['user_field']} = ?i";
                        $val = $DB->val($sql, $id, $uid);
                        if ( $DB->error )
                            return FALSE;
                        $hidden_db = $val;
                        $hidden_db = preg_replace('/[\{\}]/', '', $hidden_db);

                        $hidden_db = strlen(trim($hidden_db)) != 0 ? explode(',', $hidden_db) : array();
                        $hidden = explode(',', $hidden);

                        $changed = 0;

                        if(!in_array('hide', $hidden) && !in_array('show', $hidden)) {
                            foreach ($hidden as $tid) {
                                if (in_array(intval($tid), $hidden_db)) {
                                    unset($hidden_db[array_search(intval($tid), $hidden_db)]);
                                    $changed = 1;
                                } else {
                                    $hidden_db[] = intval($tid);
                                    $changed = 1;
                                }
                            }
                        } else {

                            if(in_array('show', $hidden) && count($hidden_db)) {
                                $hidden_db = array();
                                unset($hidden[array_search('show', $hidden)]);
                                $changed = 1;
                            }
                            if (in_array('hide', $hidden) &&
                                (!in_array(-1, $hidden_db) || (in_array(-1, $hidden_db) && count($hidden_db))) ) {
                                $hidden_db = array();
                                $hidden_db[] = -1;
                                unset($hidden[array_search('hide', $hidden)]);
                                $changed = 1;
                            }

                            if(count($hidden)) {
                                foreach ($hidden as $tid) {
                                    if(!intval($tid)) continue;
                                    $hidden_db[] = intval($tid);
                                    $changed = 1;
                                }
                            }
                        }
                        
                        if ($changed) {
                            //на всякий случай
                            foreach ($hidden_db as $k => $v) {
                                if(!$v) unset($hidden_db[$k]);
                            }

                            if ( !count($hidden_db) ) {
                                $hidden_db = NULL;
                            }

                            $sql = "UPDATE {$config['table_name']} SET {$config['hidden_field']} = ?a
                                        WHERE {$config['id_field']} = ?i AND {$config['user_field']} = ?i";
                            
                            if (!$DB->query($sql, $hidden_db, $id, $uid)) {
                                return FALSE;
                            }
                        }
                    }
                }
                
                setcookie($cookie_name, '', time() - 604800, '/');
            }
        }


        return TRUE;
    }

    /**
     * Возхвращает название сервиса где используются комментарии
     * 
     * @see self::$_sname;
     * @return string 
     */
    public function getSname() {
        if(!$this->_sname) {
            $this->_sname = str_replace("Comments", "", get_class($this));
        }

        return $this->_sname;
    }


    /**
     * Подгрузка аттачей
     *
     * @param array $attach			массив с элементами типа CFile
     * @param array $max_image_size	массив с максимальными размерами картинки (см. CFile). Один для всех элементов attach
     * @param string $login			логин юзера, которому загрузить картинку. По умолчанию - юзер из $_SESSION['login']
     * @return array				массив ($files, $alert, $error_flag)
     */
    function UploadFiles($attach, $max_image_size, $login = '', $table = '') {
        $alert = null;
        if ($login == '')
            $login = $_SESSION['login'];
        if ($login == '')
            $login = 'Anonymous';
        if ($attach)
            foreach ($attach as $x => $file) {
                if($table) $file->table = $table;
                $file->max_size = self::MAX_FILE_SIZE;
                $file->proportional = 1;
                $f_name = $file->MoveUploadedFile($login . "/upload");
                $f_id = $file->id;
                $ext = $file->getext();
                if (in_array($ext, $GLOBALS['graf_array']))
                    $is_image = TRUE;
                else
                    $is_image = FALSE;
                $p_name = '';
                $p_id = '';
                if (! isNulArray($file->error)) {
                    $error_flag = 1;
                    $alert = "Один или несколько файлов не удовлетворяют условиям загрузки.";
                    break;
                } else {
                    if ($is_image && $ext != 'swf' && $ext != 'flv') {
                        if (! $file->image_size['width'] || ! $file->image_size['height']) {
                            $error_flag = 1;
                            $alert = 'Невозможно уменьшить картинку';
                            break;
                        }
                        if (! $error_flag && ($file->image_size['width'] > $max_image_size['width'] || $file->image_size['height'] > $max_image_size['height'])) {
                            if (! $file->img_to_small("sm_" . $f_name, $max_image_size)) {
                                $error_flag = 1;
                                $alert = 'Невозможно уменьшить картинку.';
                                break;
                            } else {
                                $tn = 2;
                                $p_name = "sm_$f_name";
                                $p_id = $file->id;
                            }
                        } else {
                            $tn = 1;
                        }
                    } else
                    if ($ext == 'flv') {
                        $tn = 2;
                    } else {
                        $tn = 0;
                    }
                }
                $files[$f_id] = array();
                $files[$f_id]['f_id'] = $f_id;
                $files[$f_id]['f_name'] = $f_name;
                $files[$f_id]['p_name'] = $p_name;
                $files[$f_id]['p_id'] = $p_id;
                $files[$f_id]['tn'] = $tn;
                $files[$f_id]['small'] = $tn;
                $file->is_smalled = $tn;
//                $files[] = $file;
            }
        return array($files, $alert, $error_flag);
    }
    
    /**
     * При загрузке комментариев, устанавливает права на их использование (редактирование, удаление, восстановление)
     * 
     * @see self::__construct();
     * @param array $options Дополнительные параметры передаваемые в конструкторе для установки прав
     */
    protected function setModAccess($options) {
        if(!isset($options['is_permission'])) {
            $this->_options['is_permission'] = hasPermissions('comments') ? 1 : 0;
        }
        if(hasPermissions('comments')) {
            $this->_options['access'] = 1;
        }
    }
    
    /**
     * Связывает файлы загруженные ассинхронно с комментарием
     *
     * @param  array $files Список загруженных файлов @see attachedfiles::getFiles
     * @param  string $login Логин пользователя
     * @param  integer $msg_id ID комментария
     * @return bool
     */
    function addAttachedFiles( $files = array(), $msg_id = 0, $login = '' ) {
        global $DB;
        
        $model = $this->model();
        
        if ( !isset($model['attaches']) ) {
            return false;
        }
        
        if ( !$login ) {
            $login = $_SESSION['login'];
        }
        
        $sql = 'SELECT ' . $model['attaches']['fields']['file'] . ' AS fid FROM ' . $model['attaches']['table'] 
            . ' WHERE ' . $model['attaches']['fields']['comment'] . ' = ?i AND ' 
            . $model['attaches']['fields']['inline'] . ' != TRUE';
        $attaches  = $DB->rows( $sql, $msg_id );
        $old_files = array();
        
        if ( $attaches ) {
            foreach( $attaches as $f ) { 
                array_push( $old_files, $f['fid'] ); 
            }
        }
        
        $max_image_size = array( 'width' => 390, 'height' => 1000, 'less' => 0 );
        
        if ( $files ) {
            $num = 0;
            
            foreach( $files as $file ) {
                switch( $file['status'] ) {
                    case 4:
                        // Удаляем файл
                        $cFile = new CFile( $file['id'] );
                        $cFile->table = $model['attaches']['file_table'];
                        
                        if (  $cFile->id ) {
                            $sql = 'DELETE FROM ' . $model['attaches']['table'] 
                                . ' WHERE ' . $model['attaches']['fields']['file'] . ' = ?i';
                            $DB->query( $sql, $cFile->id );
                            $cFile->Delete( $cFile->id );
                        }
                        break;
                    case 1:
                        $num++;
                        
                        if ( in_array($file['id'], $old_files) ) {
                            $need_copy = false;
                        } 
                        else {
                            $need_copy = true;
                        }
                        
                        // Добавляем файл
                        $cFile = new CFile( $file['id'] );
                        $cFile->proportional = 1;
                        $cFile->table = $model['attaches']['file_table'];
                        $ext = $cFile->getext();

                        if ( $need_copy ) {
                            $tmp_dir  = 'users/' . substr($login, 0, 2) . '/' . $login . '/upload/';
                            $tmp_name = $cFile->secure_tmpname( $tmp_dir, '.' . $ext );
                            $tmp_name = substr_replace( $tmp_name, '', 0, strlen($tmp_dir) );
                            $cFile->_remoteCopy( $tmp_dir . $tmp_name, true );
                        }
                        
                        if ( in_array($ext, $GLOBALS['graf_array']) ) {
                            $is_image = TRUE;
                        }
                        else {
                            $is_image = FALSE;
                        }
                        
                        if ( $is_image && $ext != 'swf' && $ext != 'flv' ) {
                            if ( ($cFile->image_size['width'] > $max_image_size['width'] || $cFile->image_size['height'] > $max_image_size['height']) ) {
                                if ( $need_copy ) {
                                    if ( $cFile->resizeImage($cFile->path.'sm_'.$cFile->name, $max_image_size['width'], $cFile->image_size['height'], 'landscape')) {
                                        $cFile->small = true;
                                    }
                                } 
                                else {
                                    $cFile->small = true;
                                }
                            } 
                            else {
                                $cFile->small = false;
                            }
                        } 
                        else {
                            $cFile->small = false;
                        }
                        
                        $aData = array(
                            $model['attaches']['fields']['comment'] => $msg_id,
                            $model['attaches']['fields']['file']    => $cFile->id 
                        );
                        
                        if ( isset($model['attaches']['fields']['small']) ) {
                            $aData[$model['attaches']['fields']['small']] = $cFile->small;
                        }
                        
                        if ( isset($model['attaches']['fields']['sort']) ) {
                            $aData[$model['attaches']['fields']['sort']] = $num;
                        }
                        
                        $DB->insert( $model['attaches']['table'], $aData );
                        break;
                }
            }
        }
        
        return true;
    }
    
    public function getAdapterAutoModeration() {
        return false;
    }
}

