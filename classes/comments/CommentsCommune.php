<?php

/**
 * Description of CommentsCommune
 * 
 */


require_once ($_SERVER['DOCUMENT_ROOT'] . "/classes/comments/Comments.php");
require_once ($_SERVER['DOCUMENT_ROOT'] . "/classes/commune.php");
require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/smtp.php');
require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/smail.php');
require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php');

class CommentsCommune extends TComments {
    
    /**
     * Шаблон адреса страницы с комментариями
     * 
     * @var string
     */
    public $urlTemplate = 'http://{host}/commune/index.php?newurl=1&site=Topic&post={parent_id2}#c_{id}';
    
    /**
     * Отправлять уведомления об удалении комментария.
     * в уведомлении используется urlTemplate
     * 
     * @var bool
     */
    public $sendDeleteWarn = true;
    
    /**
     * Переключаемся на новый визивиг или нет
     * @var type 
     */
    public $enableNewWysiwyg = true;
    
    /**
     * Якорь на комментарий
     * 
     * @var string
     */
    public $anchor = '#c_';
    
    /**
     * Разрешить скрывать комментарии ниже определенного уровня рейтинга
     *
     * @var boolean
     */
    public $enableHiddenByRating = false;
    
    /**
     * Подключить новую верстку или нет
     * @var type 
     */
    public $is_new_template = true;
    
    /**
     * Уровень рейтинга необходимый для того чтобы скрыть комментарий
     * @see $enableHiddenByRating
     *
     * @var integer
     */
    public $hiddenByRating = 10;
    
    /**
     * Включить в комментариях предупреждения
     * 
     * @var boolean
     */
    public $enableWarningUsers = true;
    
    public $warningFunction = "xajax_BanMemberNewComment";
    public $userBanFunction = "xajax_BanNewMember";
    public $maskLinkForComment = "%s#c_%d";
    public $enableAutoModeration = true;
    
    // Шаблоны доступа к комментариям и выводы соотетствующих текстов в сообществах
    protected $_access = array(1 => 
                            array('update' => 'Отредактировано модератором', 
                                  'delete' => 'Комментарий удален модератором'),
                               2 =>
                            array('update' => 'Отредактировано создателем сообщества', 
                                  'delete' => 'Комментарий удален создателем сообщества'),
                               3 =>
                            array('update' => 'Отредактировано модератором сообщества', 
                                  'delete' => 'Комментарий удален модератором сообщества'),
                               4 => 
                            array('delete' => 'Комментарий удален автором поста')    
                        );
    /**
     * Конфиг данных для комментариев сервиса
     *
     * @return array
     */
    public function model() {
        if(empty($this->_options['commune_id'])) {
            $this->_options['commune_id'] = commune::getCommuneIDByThemeID($this->_options['theme_id']);
        }
        
        $table      = commune::getTableName('commune_messages', $this->_options['commune_id']);
        $user_table = commune::getTableName('commune_users_messages', $this->_options['commune_id'], false, true);
        return array(
            // ИД ресурса, если отличается от $this->_resource_id
            'resource_id' => $this->_options['theme_id'],
            // комментарии
            'comments' => array(
                'insert_table' => $table,
                'table' => 'commune_messages',
                'fields' => array(
                    'id' => 'id',
                    'resource' => 'theme_id',
                    'author' => 'user_id',
                    'parent_id' => 'parent_id',
                    'parent_id2' => 'parent_id', // в обход expr
                    'msgtext' => 'msgtext',
                    'yt' => 'youtube_link',
                    'created_time' => 'created_time',
                    'modified' => 'modified_id',
                    'modified_time' => 'modified_time',
                    'deleted' => 'deleted_id',
                    'deleted_time' => 'deleted_time',
                    'reason' => 'deleted_reason',
                    'rating' => 'rating',
                    'access' => 'mod_access',
                    'mod_access' => 'mod_access', // в обход access, так как его перебивают в function msg_node($msg)
                    'moderator_status' => 'moderator_status'
                ),
                // Если в селекте для выбора какого-нибудь поля необходимо условие,
                // добавляем его в этот массив. Ключ = ключ из fields
                'expr' => array(
                    'parent_id' => $this->_resource_id ? "CASE WHEN commune_messages.parent_id = {$this->_resource_id} THEN NULL ELSE commune_messages.parent_id END" : null
                ),
                'where' => array(
                    "commune_messages.parent_id IS NOT NULL"
                ),
                'set' => 'SET enable_sort = false; SET enable_hashjoin = false',
            ),
            // файлы
            'attaches' => array(
                'file_table' => 'file_commune',
                'table' => 'file_commune',
                'fields' => array(
                    'comment' => 'src_id',
                    'small'   => 'small',
                    'inline'  => 'inline',
                    'sort'    => 'sort'
                )
            ),
            // данные пользователя
            'users' => array(
                'table' => $user_table,
                'fields' => array(
                    'user' => 'user_id',
                    'comment' => 'message_id',
                    'lvt' => 'last_viewed_time',
                    'hidden' => 'hidden_threads',
                    'user_rating' => 'rating'
                ),
                'inner_fields' => array(
                    'cm.warn_count' => 'warn_count',
                    'cm.commune_id' => 'resource_id',
                    'cm.id'         => 'member_id',
                    'cm.is_banned'  => 'is_banned'
                 ),
                'inner' => array(
                    "INNER JOIN commune_themes ct ON ct.id = commune_messages.theme_id",
                    "LEFT JOIN commune_members cm ON cm.user_id = commune_messages.user_id AND cm.commune_id = ct.commune_id"
                )
            ),
            'moderation_rec_type' => user_content::MODER_COMMUNITY, 
            'moderation_sort_order' => 3, 
            'permissions' => (hasPermissions('communes') || hasPermissions('comments'))
        );
    }
    
    public function __construct($id = null, $lvt = null, $options = array()) {
        include_once $_SERVER['DOCUMENT_ROOT'].'/classes/links.php';
        $GLOBALS[LINK_INSTANCE_NAME] = new links('commune');
        parent::__construct($id, $lvt, $options);
    }
    
    /**
     * Голосование в комментах
     *
     * @param <type> $uid       ид пользователя
     * @param <type> $id        ид комментария
     * @param <type> $dir       голос +1/-1
     */
    public function RateComment($uid, $id, $dir) {
        if(!$uid) return false;

        $res = commune::TopicVote($id, $uid, $dir);
        return $res;
    }

    protected  function save($params = array(), $cid = null, $author = 0) {
        $themeId = $params['resource']   = $this->_options['theme_id'];
        $params['parent_id']  = !$params['parent_id'] ? $this->_resource_id : $params['parent_id'];
        $params['access']     = (int) $this->_options['access'];
        $this->_options['commune_id'] = commune::getCommuneIDByThemeID($themeId);
        $messageId = parent::save($params, $cid, $author);  
        // если комментарий не удалось сохранить
        if (!$messageId) {
            return;
        }
        return $messageId;
    }

    /**
     * Выводит все комментарии на страницу
     * 
     * @return string HTML код 
     */
    public function render() {
       $user_is_subscribe_on_topic = commune::isCommuneTopicSubscribed($this->_resource_id, get_uid(false));
       $uid = get_uid(false);
       $comments = $this->getData();
       $this->msg_num = count($comments);
       $comments = array(
           'children' => array2tree($comments, 'id', 'parent_id', true)
       );
       $comments_html = $this->msg_nodes($comments);
       if (!$GLOBALS['top']['deleted_id']) {
           $form = $this->renderForm();
       }

       ob_start();
       include($this->tpl_path . $this->templates['main']);
       return ob_get_clean();
    }

    protected function setModAccess($options) {
        if(!isset($options['is_permission'])) {
            $this->_options['is_permission'] = hasPermissions('comments');
        }
        
        switch(true) {
            case (hasPermissions('comments')):
                $this->_options['access'] = 1;
                break;
            case ($this->_options['is_permission'] == 2):
                $this->_options['access'] = 2;
                break;
            case ($this->_options['is_permission'] == 3):
                $this->_options['access'] = 3;
                break;
            case ($this->_options['is_permission'] == 4):
                $this->_options['access'] = 4;
                break;
        }
    }
    
    public function getAdapterAutoModeration() {
        return new commune_carma();
    }
}
