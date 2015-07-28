<?php
/**
 * Подключаем предка
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/admin_parent.php");
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php');

/**
 * Класс для работы с модерированием пользовательского контента
 * 
 * @author Max 'BlackHawk' Yastrembovich
 */
class user_content extends admin_parent {
    /**
     * Допустимые значения $site
     * 
     * @var array
     */
    static $site_allow = array( 15 => 'choose', 'shifts', 'streams', 'blocked', 'stream', 'frames' );
    
    /**
     * Константы для модерируемых сущностей
     */
    const MODER_MSSAGES      = 1;  // Личные сообщения
    const MODER_BLOGS        = 2;  // Блоги: посты и комментарии
    const MODER_COMMUNITY    = 3;  // Сообщества: посты и комментарии
    const MODER_PROJECTS     = 4;  // Проекты
    const MODER_PRJ_OFFERS   = 5;  // Предложения в проектах
    const MODER_ART_COM      = 8;  // Комментарии в статьях
    const MODER_PROFILE      = 9;  // Изменения в профилях
    const MODER_PRJ_DIALOG   = 10; // Комментарии к предложениям по проектам
    const MODER_CONTEST_COM  = 11; // Комментарии к предложениям конкурсов
    const MODER_PORTF_CHOISE = 12; // Уточнения к разделам в портфолио
    const MODER_PORTFOLIO    = 13; // Работы в портфолио
    const MODER_SDELAU       = 14; // Предложения фрилансеров "Сделаю"
    const MODER_PRJ_COM      = 15; // Сборная: Предложения в проектах/конкурсах, комментарии к предложениям в проектах/конкурсах, Предложения фрилансеров Сделаю
    const MODER_COMMENTS     = 16; // Сборная: Комментарии: магазин, статьи
    const MODER_PORTF_UNITED = 17; // Сборная: Работы в портфолио, Уточнения к разделам в портфолио
    const MODER_FIRST_PAGE   = 18; // Платные места
    const MODER_CAROUSEL     = 19; // Карусель
    const MODER_BLOGS_UNITED = 20; // Сборная: Блоги: посты и комментарии, Комментарии в статьях
    const MODER_USER_UNITED  = 21; // Сборная: Изменения в профилях и Уточнения к разделам в портфолио
    const MODER_TSERVICES    = 22; // Типовые услуги
    const MODER_SBR_REQV     = 23; // Реквизиты в разделе финансы
    
    
    /**
     * Сущности для которых отсутствуют вкладки Проверенные
     * 
     * @var array 
     */
    static $aNoApproved = array( 
        user_content::MODER_PROFILE, 
        user_content::MODER_PORTF_CHOISE,
        user_content::MODER_USER_UNITED
    );
    
    /**
     * Сущности для которых отсутствуют вкладки Заблокированные
     * 
     * @var array 
     */
    static $aNoRejected = array( 
        user_content::MODER_PROFILE, 
        user_content::MODER_PRJ_DIALOG, 
        user_content::MODER_PORTF_CHOISE, 
        user_content::MODER_PORTFOLIO,
        user_content::MODER_USER_UNITED
    );


    /**
     * Имена таблиц
     * 
     * @var array 
     */
    static $table = array(
        self::MODER_MSSAGES      => array( 'main' => '',                         'moder' => array(self::MODER_MSSAGES) ),
        self::MODER_BLOGS        => array( 'main' => 'blogs_msgs',               'moder' => array(self::MODER_BLOGS) ),
        self::MODER_COMMUNITY    => array( 'main' => 'commune_messages',         'moder' => array(self::MODER_COMMUNITY) ),
        self::MODER_PROJECTS     => array( 'main' => 'projects',                 'moder' => array(self::MODER_PROJECTS) ),
        self::MODER_PRJ_OFFERS   => array( 'main' => 'projects_offers',          'moder' => array(self::MODER_PRJ_OFFERS) ),
        self::MODER_ART_COM      => array( 'main' => 'articles_comments',        'moder' => array(self::MODER_ART_COM) ),
        self::MODER_PROFILE      => array( 'main' => 'users_change',             'moder' => array(self::MODER_PROFILE) ),
        self::MODER_PRJ_DIALOG   => array( 'main' => 'projects_offers_dialogue', 'moder' => array(self::MODER_PRJ_DIALOG) ),
        self::MODER_CONTEST_COM  => array( 'main' => 'projects_contest_msgs',    'moder' => array(self::MODER_CONTEST_COM) ),
        self::MODER_PORTF_CHOISE => array( 'main' => 'portf_choise_change',      'moder' => array(self::MODER_PORTF_CHOISE) ),
        self::MODER_PORTFOLIO    => array( 'main' => 'portfolio',                'moder' => array(self::MODER_PORTFOLIO) ),
        self::MODER_SDELAU       => array( 'main' => 'freelance_offers',         'moder' => array(self::MODER_SDELAU) ),
        self::MODER_PRJ_COM      => array( 'main' => '',                         'moder' => array(self::MODER_PRJ_OFFERS, self::MODER_PRJ_DIALOG, self::MODER_CONTEST_COM) ),
        self::MODER_COMMENTS     => array( 'main' => '',                         'moder' => array(self::MODER_ART_COM) ),
        self::MODER_PORTF_UNITED => array( 'main' => '',                         'moder' => array(self::MODER_PORTF_CHOISE, self::MODER_PORTFOLIO) ),
        self::MODER_BLOGS_UNITED => array( 'main' => '',                         'moder' => array(self::MODER_BLOGS, self::MODER_ART_COM) ),
        self::MODER_USER_UNITED  => array( 'main' => '',                         'moder' => array(self::MODER_PROFILE, self::MODER_PORTF_CHOISE) ),        
        self::MODER_TSERVICES    => array( 'main' => 'tservices',                'moder' => array(self::MODER_TSERVICES) ),
        self::MODER_SBR_REQV     => array( 'main' => 'sbr_reqv',                 'moder' => array(self::MODER_SBR_REQV) )
    );
    
    /**
     * Всплывающие счетчики в шапках потоков
     * 
     * @var array 
     */
    static $counters = array(
        self::MODER_USER_UNITED    => array(
            array('name' => 'Жалобы на спам', 'link' => '/siteadmin/messages_spam', 'class' => 'messages_spam', 'method' => 'getSpamCount')
        ),
        self::MODER_PRJ_COM => array(
            array('name' => 'Жалобы на проекты', 'link' => '/siteadmin/ban-razban/?mode=complain', 'class' => 'projects', 'method' => 'GetComplainPrjsCount'),
            array('name' => 'Подозрительные пользователи', 'link' => '/siteadmin/suspicious-users/', 'class' => 'users', 'method' => 'GetCountSuspiciousUsers')
        )
    );


    /**
     * раз во сколько секунд делается запрос на обновление страницы выбора потоков. быстро, наприер: 5 секунд.
     */
    const MODER_CHOOSE_REFRESH = 5;

    /**
     * раз во сколько секунд проверять не сменилась ли смена. медленно, наприер: 10 минут.
     */
    const MODER_SHIFTS_REFRESH = 600;
    
    /**
     * через сколько секунд простоя автоматически освобождать поток. средне, наприер: 5 минут.
     */
    const MODER_STREAM_RELEASE = 600;

    /**
     * как часто обновлять данные о количестве неотмодерированныех сообщений
     */
    const MODER_QUEUE_CNT_REFRESH = 120;
    
    /**
     * Частота обновления счетчиков "Подозрительные пользователи", "Жалобы на проекты", "Жалобы на спам", "Платные места"
     */
    const MODER_OTHER_CNT_REFRESH = 180;
    
    /**
     * Время жизни мэмкэша в секундах. Должно быть больше MODER_CHOOSE_REFRESH, MODER_SHIFTS_REFRESH и MODER_STREAM_RELEASE
     */
    const MODER_MEMBUFF_TTL = 3600;
    
    /**
     * По сколько записей выводить на страницу за раз
     */
    const CONTENTS_PER_PAGE = 50;
    
    /**
     * По сколько записей выводить на страницу за раз в потоке лички
     */
    const MESSAGES_PER_PAGE = 25;
    
    /**
     * По сколько записей выводить на вкладках Проверенные и Заблокированные
     */
    const TWITTER_PER_PAGE = 10;

    /**
     * Объект класса memBuff
     * 
     * @var object
     */
    var $mem_buff = null;
    
    /**
     * Список модерируемых сущностей
     * 
     * @var array
     */
    var $contents = null;
    
    /**
     * Список текущих потоков
     * 
     * @var array 
     */
    var $content_streams = false;
    
    /**
     * Количество занятых потоков в каждой сущности
     * 
     * @var array 
     */
    var $streams_count = false;
    
    /**
     * Время первого обновления списка текущих потоков
     * 
     * @var type 
     */
    var $first_update = false;
    
    /**
     * Время последнего обновления списка текущих потоков
     * 
     * @var type 
     */
    var $last_update = false;

    /**
     * Список потоков во время создания класса (копия $content_streams в __construct)
     * В дальнейшем нужен для отслеживания изменений в __destruct
     * 
     * @var array 
     */
    var $initial_sreams = array();
    
    /**
     * Нужно ли сохранять потоки при вызове __desctruct
     * 0 - нет, 1 - только при имзенениях, 2 - обновить все потоки в любом случае
     * 
     * @var integer
     */
    var $save_streams = 0;
    
    /**
     * Информация о потоке.
     * 
     * @var array
     */
    var $aStream = null;
    
    /**
     * Количество проверенных записей
     * 
     * @var int 
     */
    var $nResolveCnt = 0;
    
    /**
     * Конструктор класса
     * 
     * @param  array $user_permissions права пользователя
     * @param int $items_pp Количество элементов на странице
     */
    function __construct( $uid = 0, $user_permissions = array(), $items_pp = 20 ) {
        $this->curr_uid         = $uid;
        $this->user_permissions = $user_permissions;
        $this->mem_buff         = new memBuff();
        $this->contents         = $this->getContents();
        $this->content_streams  = $this->mem_buff->get( 'user_content_streams' );
        $this->streams_count    = $this->mem_buff->get( 'ucs_streams_count' );
        $this->first_update     = $this->mem_buff->get( 'ucs_first_update' );
        $this->last_update      = $this->mem_buff->get( 'ucs_last_update' );
        $this->initial_sreams   = $this->content_streams;

        // если в мемкеше потерялось, читаем с базы
        if ( $this->content_streams === false ) {
            $this->streams_count = array();
            $this->first_update  = time();
            $this->last_update   = time();
            
            $DB  = new DB('plproxy');
            $res = $DB->query("SELECT * FROM mod_streams() ORDER BY type, pos");
            while ( $row = pg_fetch_assoc($res) ) {
                $type = $row['type'];
                $pos  = $row['pos'];
                $aOne = array();
                if ( $row['id'] == '_first_update' ) {
                    $this->first_update = $row['time'];
                    continue;
                }
                if ( empty($this->content_streams[$type]) ) {
                    $this->content_streams[$type] = array();
                }
                
                if ( empty($this->streams_count[$type]) ) {
                    $this->streams_count[$type] = 0;
                }
                $aOne['stream_id']   = $row['id'];
                $aOne['admin_id']    = $row['admin_id']? (int) $row['admin_id']: '';
                $aOne['stream_num']  = is_null($row['num'])? '': (int) $row['num'];
                $aOne['resolve_cnt'] = $row['resolve_cnt']? (int) $row['resolve_cnt']: 0;
                if ( $row['admin_name'] ) {
                    $aOne['admin_name'] = $row['admin_name'];
                }
                if ( $row['time'] ) {
                    $aOne['time'] = (int) $row['time'];
                    $this->streams_count[$type]++;
                    if ( $row['time'] > $this->last_update ) {
                        $this->last_update = $row['time'];
                    }
                }
                
                $this->content_streams[$type][] = $aOne;
            }
            
            if ( is_array($this->content_streams) && $this->content_streams ) {
                $this->_saveStreams(true);
            }
        }

        //$this->mem_buff->touchTag( 'user_content' ); // сброс мемкеша в процессе разработки

        parent::__construct( $items_pp );
    }
    
    /**
     * Проверяет удерживает ли юзер поток
     * 
     * @param  int $content_id идентификатор сущности из admin_contents
     * @param  string $stream_id идентификатор потока
     * @param  int $user_id UID пользователя
     * @param  mixed $aStream поток или номер ошибки
     * @param  int $nResolveCnt количество проверенных записей
     * @return bool true - удерживает, false - нет
     */
    function checkStream( $content_id = 0, $stream_id = '', $user_id = 0, &$aStream = 0, $nResolveCnt = 0 ) {
        $bRet   = false;
        $bFound = false;
        
        if ( $content_id && $stream_id ) {
            if ( isset($this->content_streams[$content_id]) && count($this->content_streams[$content_id]) ) {
                if ( $this->hasContentPermissions($content_id) ) {
                    foreach ( $this->content_streams[$content_id] as $sKey => $aOne ) {
                        if ( $aOne['stream_id'] == $stream_id ) {
                            $bFound = true;

                            if ( empty($aOne['admin_id']) ) {
                                $aStream = -6; // поток освобожден
                            }
                            elseif ( $aOne['admin_id'] != $user_id ) {
                                $aStream = -5; // поток захвачен другим юзером
                            }
                            elseif ( $aOne['admin_id'] == $user_id ) {
                                $bRet = true; // юзер удерживает поток
                                $aOne['time'] = time();
                                $aOne['resolve_cnt'] += $nResolveCnt;
                                $this->content_streams[$content_id][$sKey] = $aOne;
                                $this->last_update = time();
                                $this->_saveStreams();
                                $aOne['title_num'] = $sKey + 1;
                                $this->aStream     = $aStream = $aOne;
                                $this->nResolveCnt = $aOne['resolve_cnt'];
                            }

                            break;
                        }
                    }
                    
                    if ( !$bFound ) {
                        $aStream = -4; // поток не существует
                    }
                }
                else {
                    $aStream = -3; // не хватает прав
                }
            }
            else {
                $aStream = -2; // сущность не существует
            }
        }
        else {
            $aStream = -1; // не хватает данных
        }
        
        return $bRet;
    }
    
    /**
     * Автоматическое освобождение просроченных потоков
     * 
     * вызывается из minutly.php
     * в идеале должна просто просто пробегаться по массиву из мэмкэша.
     * появление просроченных потоков должно быть редкостью та как модерирование беспрерывное
     */
    function releaseDelayedStreams() {
        if ( $this->content_streams === false || $this->first_update === false || $this->last_update === false ) {
            $this->_initStreams();
        }
        else {
            $nNow = time();
            $bRelease = false;

            foreach ( $this->content_streams as $content_id => $aStreams ) {
                foreach ( $aStreams as $sKey => $aOne ) {
                    if ( $aOne['admin_id'] && $nNow - self::MODER_STREAM_RELEASE > $aOne['time'] ) {
                        $this->_releaseContent( $content_id, $aOne['stream_id'] );
                        
                        $aOne = array( 'stream_id' => $aOne['stream_id'], 'admin_id' => '' );
                        $bRelease = true;
                        
                        $this->content_streams[$content_id][$sKey] = $aOne;
                    }
                }
                
                $this->_countChosenStreams( $content_id );
            }
            
            if ( $bRelease ) {
                $memBuff  = new memBuff;
                $memBuff->delete('ucs_streams_queue');
            }
            
            $this->last_update = time();
            $this->_saveStreams();
        }
    }
    
    /**
     * Освобождение потока пользователем
     * 
     * @param  int $content_id идентификатор сущности из admin_contents
     * @param  string $stream_id идентификатор потока
     * @param  int $user_id UID пользователя
     */
    function releaseStream( $content_id = 0, $stream_id = '', $user_id = 0 ) {
        if ( $this->content_streams === false || $this->first_update === false || $this->last_update === false ) {
            $this->_initStreams();
        }
        elseif ( isset($this->content_streams[$content_id]) && count($this->content_streams[$content_id]) ) {
            foreach ( $this->content_streams[$content_id] as $sKey => $aOne ) {
                if ( $aOne['stream_id'] == $stream_id && $aOne['admin_id'] == $user_id ) {
                    $aOne = array( 'stream_id' => $aOne['stream_id'], 'admin_id' => '' );

                    $this->content_streams[$content_id][$sKey] = $aOne;
                    $this->_countChosenStreams( $content_id );
                    $this->_releaseContent( $content_id, $stream_id );
                    
                    $memBuff  = new memBuff;
                    $memBuff->delete('ucs_streams_queue');
                    break;
                }
            }
            
            $this->last_update = time();
            $this->_saveStreams();
        }
    }

    /**
     * Отсылка уведомления в личку об Утверждение/удаление записи
     * 
     * @param  int $user_id идентификатор модератора
     * @param  int $from_id идентификатор пользователя
     * @param  int $content_id идентификатор сущности из admin_contents
     * @param  int $rec_id идентификатор записи
     * @param  int $rec_type тип записи 
     * @param  int $action действие: 1 - утверждено, 2 - удалено
     * @param  string $reason причина
     * @return bool true - успех, false - провал
     */
    function sendNotification( $user_id = 0, $from_id = 0, $content_id = 0, $rec_id = 0, $rec_type = 0, $action = 1, $reason = '' ) {
        global $DB;
        if($action!=2) return;

        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/messages.php' );
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/admin_parent.php' );
        $need_send = false;

        $users = new users;
        $users->GetUserByUID( $from_id );
        $reason = str_replace( '%USERNAME%', $users->uname . ' ' . $users->usurname, $reason );

        switch ($content_id) {
            case self::MODER_MSSAGES:
                // Личные сообщения
                $DB9 = new DB( 'plproxy' );
                $sQuery = 'SELECT * FROM messages_moder_send_get(?i, ?i);';
                $aMsg   = $DB9->row( $sQuery, $from_id, $rec_id );
                $msg = $aMsg['msg_text'];
                $message = "Сообщение заблокировано.\n\n".($msg ? "-----\n\n{$msg}\n\n-----\n\n" : "")."Причина: {$reason}";
                $need_send = true;
                break;
            case self::MODER_BLOGS:
                // Блоги: посты и комментарии
                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/blogs.php' );
                $aMsg = blogs::GetMsgInfo($rec_id, $ee, $tt);
                $msg = $aMsg['msgtext'];
                if($rec_type==1) {
                    $message = "Сообщение в блоге заблокировано.\n\n".($msg ? "-----\n\n{$msg}\n\n-----\n\n" : "")."Причина: {$reason}";
                }
                if($rec_type==2) {
                    $message = "Комментарий в блоге заблокирован.\n\n".($msg ? "-----\n\n{$msg}\n\n-----\n\n" : "")."Причина: {$reason}";
                }
                $need_send = true;
                break;
            case self::MODER_COMMUNITY:
                // Сообщества: посты и комментарии
                $sql = "SELECT * FROM commune_messages WHERE id = ?i";
                $aMsg = $DB->row($sql, $rec_id);
                $msg = $aMsg['msgtext'];
                if($rec_type==1) {
                    $message = "Сообщение в сообществе заблокировано.\n\n".($msg ? "-----\n\n{$msg}\n\n-----\n\n" : "")."Причина: {$reason}";
                }
                if($rec_type==2) {
                    $message = "Комментарий в сообществе заблокирован.\n\n".($msg ? "-----\n\n{$msg}\n\n-----\n\n" : "")."Причина: {$reason}";
                }
                $need_send = true;
                break;
            case self::MODER_PROJECTS:
                // Проекты
                $sql = "SELECT * FROM projects WHERE id= ?i";
                $aMsg = $DB->row($sql, $rec_id);
                $msg = $aMsg['descr'];
                $message = "Проект заблокирован.\n\n".($msg ? "-----\n\n{$msg}\n\n-----\n\n" : "")."Причина: {$reason}";
                $need_send = true;
                break;
            case self::MODER_PRJ_OFFERS:
                // Предложения в проектах
                $sql = "SELECT * FROM projects_offers WHERE id = ?i";
                $aMsg = $DB->row($sql, $rec_id);
                $msg = $aMsg['descr'];
                $message = "Предложение в проекте заблокировано.\n\n".($msg ? "-----\n\n{$msg}\n\n-----\n\n" : "")."Причина: {$reason}";
                $need_send = true;
                break;
            case self::MODER_ART_COM:
                // Комментарии в статьях
                $sql = "SELECT * FROM articles_comments WHERE id = ?i";
                $aMsg = $DB->row($sql, $rec_id);
                $msg = $aMsg['msgtext'];
                $message = "Комментарий в статьях заблокирован.\n\n".($msg ? "-----\n\n{$msg}\n\n-----\n\n" : "")."Причина: {$reason}";
                $need_send = true;
                break;
            case self::MODER_PROFILE:
                // Изменения в профилях
                $message = "Изменение в профиле отредактировано.\n\nПричина: {$reason}";
                $need_send = true;
                break;
            case self::MODER_PRJ_DIALOG:
                // Комментарии к предложениям по проектам
                $sql = "SELECT * FROM projects_offers_dialogue WHERE id = ?i";
                $aMsg = $DB->row($sql, $rec_id);
                $msg = $aMsg['post_text'];
                $message = "Комментарий к предложению проекта заблокирован.\n\n".($msg ? "-----\n\n{$msg}\n\n-----\n\n" : "")."Причина: {$reason}";
                $need_send = true;
                break;
            case self::MODER_CONTEST_COM:
                // Комментарии к предложениям конкурсов
                $sql = "SELECT * FROM projects_contest_msgs WHERE id = ?i";
                $aMsg = $DB->row($sql, $rec_id);
                $msg = $aMsg['msg'];
                $message = "Комментарии к предложению конкурса заблокирован.\n\n".($msg ? "-----\n\n{$msg}\n\n-----\n\n" : "")."Причина: {$reason}";
                $need_send = true;
                break;
            case self::MODER_PORTF_CHOISE:
                // Уточнения к разделам в портфолио
                $message = "Уточнения к разделам в портфолио отредактированы.\n\nПричина: {$reason}";
                $need_send = true;
                break;
            case self::MODER_PORTFOLIO:
                // Работы в портфолио
                $message = "Работа в портфолио заблокирована.\n\nПричина: {$reason}";
                $need_send = true;
                break;
            case self::MODER_SDELAU:
                // Предложения фрилансеров "Сделаю"
                $sql = "SELECT * FROM freelance_offers WHERE id = ?i";
                $aMsg = $DB->row($sql, $rec_id);
                $msg = $aMsg['descr'];
                $message = "Объявление в предложениях фрилансеров заблокировано.\n\n".($msg ? "-----\n\n{$msg}\n\n-----\n\n" : "")."Причина: {$reason}";
                $need_send = true;
                break;
        }

        if($need_send) {
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
            $users = new users();
            messages::Add( users::GetUid($err, 'admin'), $users->GetField($from_id, $err, 'login'), $message, '', 1 );
        }
    }
    
    /**
     * Утверждение/удаление записи
     * 
     * @param  string $stream_id идентификатор потока
     * @param  int $user_id идентификатор модератора
     * @param  int $from_id идентификатор пользователя
     * @param  int $content_id идентификатор сущности из admin_contents
     * @param  int $rec_id идентификатор записи
     * @param  int $rec_type тип записи 
     * @param  int $action действие: 1 - утверждено, 2 - удалено
     * @param  string $is_sent было ли отправлено уведомление
     * @param  string $reason причина удаления
     * @return bool true - успех, false - провал
     */
    function resolveContent( $stream_id = '', $user_id = 0, $from_id = 0, $content_id = 0, $rec_id = 0, $rec_type = 0, $action = 1, $is_sent = '', $reason = '' ) {
        $reason = !$reason ? '' : $reason;
        $bRet   = false;
        
        switch ($content_id) {
            case self::MODER_MSSAGES:
                // Личные сообщения
                $bRet = $this->resolveMessages( $stream_id, $user_id, $from_id, $rec_id, $rec_type, $action, $is_sent, $reason );
                break;
            case self::MODER_BLOGS:
                // Блоги: посты и комментарии
                $bRet = $this->resolveBlogs( $stream_id, $user_id, $from_id, $rec_id, $rec_type, $action, $is_sent, $reason );
                break;
            case self::MODER_COMMUNITY:
                // Сообщества: посты и комментарии
                $bRet = $this->resolveCommunity( $stream_id, $user_id, $from_id, $rec_id, $rec_type, $action, $is_sent, $reason );
                break;
            case self::MODER_PROJECTS:
                // Проекты
                $bRet = $this->resolveProjects( $stream_id, $user_id, $from_id, $rec_id, $rec_type, $action, $is_sent, $reason );
                break;
            case self::MODER_PRJ_OFFERS:
                // Предложения в проектах
                $bRet = $this->resolvePrjOffers( $stream_id, $user_id, $from_id, $rec_id, $rec_type, $action, $is_sent, $reason );
                break;
            case self::MODER_ART_COM:
                // Комментарии в статьях
                $bRet = $this->resolveArtCom( $stream_id, $user_id, $from_id, $rec_id, $rec_type, $action, $is_sent, $reason );
                break;
            case self::MODER_PROFILE:
                // Изменения в профилях
                $bRet = $this->resolveProfile( $stream_id, $user_id, $from_id, $rec_id, $rec_type, $action, $is_sent, $reason );
                break;
            case self::MODER_PRJ_DIALOG:
                // Комментарии к предложениям по проектам
                $bRet = $this->resolvePrjDialog( $stream_id, $user_id, $from_id, $rec_id, $rec_type, $action, $is_sent, $reason );
                break;
            case self::MODER_CONTEST_COM:
                // Комментарии к предложениям конкурсов
                $bRet = $this->resolveContestCom( $stream_id, $user_id, $from_id, $rec_id, $rec_type, $action, $is_sent, $reason );
                break;
            case self::MODER_PORTF_CHOISE:
                // Уточнения к разделам в портфолио
                $bRet = $this->resolvePortfChoice( $stream_id, $user_id, $from_id, $rec_id, $rec_type, $action, $is_sent, $reason );
                break;
            case self::MODER_PORTFOLIO:
                // Работы в портфолио
                $bRet = $this->resolvePortfolio( $stream_id, $user_id, $from_id, $rec_id, $rec_type, $action, $is_sent, $reason );
                break;
            case self::MODER_SDELAU:
                // Предложения фрилансеров "Сделаю"
                $bRet = $this->resolveSdelau( $stream_id, $user_id, $from_id, $rec_id, $rec_type, $action, $is_sent, $reason );
                break;
            case self::MODER_TSERVICES:
                // Типовые услуги
                $bRet = $this->resolveTServices( $stream_id, $user_id, $from_id, $rec_id, $rec_type, $action, $is_sent, $reason );
                break;
            case self::MODER_SBR_REQV:
                $bRet = $this->resolveSbrReqv( $stream_id, $user_id, $from_id, $rec_id, $rec_type, $action, $is_sent, $reason );
                break;
            default:
                break;
        }
        
        if ( $bRet ) {
            $this->nResolveCnt++;
        }
        
        return $bRet;
    }
    
    /**
     * Разблокировака сущностей
     * 
     * @param  int $content_id идентификатор сущности из admin_contents
     * @param  int $from_id идентификатор пользователя
     * @param  int $rec_id идентификатор записи
     * @param  int $rec_type тип записи 
     * @param  string $is_sent было ли отправлено уведомление
     */
    function unblock( $content_id = 0, $from_id = 0, $rec_id = 0, $rec_type = 0, $is_sent = '' ) {
        switch ($content_id) {
            case self::MODER_BLOGS:
                // Блоги: посты и комментарии
                if ( $rec_type == 1 ) { // пост
                    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/blogs.php' );
                    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/admin_log.php' );

                    $blogs  = new blogs;
                    $sQuery = 'SELECT bm.title, bm.thread_id AS id, bb.blocked_time 
                        FROM blogs_msgs bm 
                        LEFT JOIN blogs_blocked bb ON bb.thread_id = bm.thread_id 
                        WHERE bm.id = ?i';

                    $aThread  = $GLOBALS['DB']->row( $sQuery, $rec_id );
                    $sObjName = $aThread['title'] ? $aThread['title'] : '<без темы>';
                    $sObjLink = '/blogs/view.php?tr=' . $aThread['id'];

                    if ( $aThread['blocked_time'] ) {
                        $blogs->UnBlocked( $aThread['id'] );
                        // пишем лог админских действий
                        admin_log::addLog( admin_log::OBJ_CODE_BLOG, 8, $from_id, $aThread['id'], $sObjName, $sObjLink, 0, '', 0, '' );
                    }
                }
                else { // комментарий
                    $sDeluserId = $GLOBALS['DB']->val( 'SELECT deluser_id FROM blogs_msgs WHERE id = ?i', $rec_id );

                    if ( $sDeluserId && $sDeluserId != $from_id ) {
                        $aData['deleted']    = null;
                        $aData['deluser_id'] = null;
                        $aData['deleted_reason'] = '';
                        
                        $GLOBALS['DB']->update( 'blogs_msgs', $aData, 'id = ?i', $rec_id );
                    }
                }
                break;
            case self::MODER_COMMUNITY:
                // Сообщества: посты и комментарии
                if ( $rec_type == 1 ) { // топик
                    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/commune.php' );
                    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/admin_log.php' );

                    $commune  = new commune;
                    $topic    = commune::GetTopMessageByAnyOther( $rec_id, $user_id, commune::MOD_ADMIN );
                    $sObjName = $topic['title'];
                    $sObjLink = getFriendlyURL( 'commune', $rec_id );

                    if ( $topic['is_blocked_s'] == 't' ) { //утверждаем
                        $commune->unblockedCommuneTheme( $topic['theme_id'] );
                        admin_log::addLog( admin_log::OBJ_CODE_COMM, 16, $from_id, $topic['theme_id'], $sObjName, $sObjLink, 0, '', 0, '' );
                    }
                }
                else {
                    if ( $is_sent == 'f' ) {
                        // отправка уведомления о новом сообщении
                        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/pmail.php' );

                        $pmail = new pmail;
                        $pmail->CommuneNewComment( $rec_id );

                        $GLOBALS['DB']->update( 'commune_messages', array('is_sent' => true), 'id = ?i', $rec_id );
                    }

                    $aRow = $GLOBALS['DB']->row( 'SELECT theme_id, deleted_id FROM commune_messages WHERE id = ?i', $rec_id );

                    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/comments/CommentsCommune.php' );
                    $comments = new CommentsCommune($aRow['theme_id']);

                    if ( $aRow['deleted_id'] && $aRow['deleted_id'] != $from_id ) {
                        $comments->restore( $rec_id );
                    }
                }
                break;
            case self::MODER_PROJECTS:
                // Проекты
                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/projects.php' );
                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/admin_log.php' );

                $projects = new projects;
                $project  = $projects->GetPrjCust( $rec_id );
                $sObjLink = getFriendlyURL( 'project', $rec_id ); // лог админских действий

                if ( $project['blocked_time'] ) {
                    // разблокируем
                    $projects->UnBlocked( $rec_id );
                    admin_log::addLog( admin_log::OBJ_CODE_PROJ, 10, $from_id, $rec_id, $project['name'], $sObjLink, 0, '', 0, '' );
                }
                break;
            case self::MODER_PRJ_OFFERS:
                // Предложения в проектах
                if ( $rec_type == 7 ) {
                    $aData      = array();
                    $sDeluserId = $GLOBALS['DB']->val( 'SELECT deluser_id FROM projects_contest_offers WHERE id = ?i', $rec_id );

                    if ( $sDeluserId && $sDeluserId != $from_id ) {
                        if ( $is_sent == 'f' ) {
                            // отправка уведомления о новом сообщении
                            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/pmail.php' );

                            $pmail = new pmail;
                            $pmail->NewPrjOffer( $rec_id );

                            $aData['is_sent'] = true; // устанавливаем флаг отправки
                        }
                        
                        $aData['is_deleted']    = false;
                        $aData['deluser_id']    = null;
                        $aData['deleted_reason'] = '';
                        
                        $GLOBALS['DB']->update( 'projects_contest_offers', $aData, 'id = ?i', $rec_id );
                    }
                }
                else {
                    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/projects_offers.php' );
                    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/admin_log.php' );
                    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/projects.php' );

                    $projects_offers = new projects_offers();
                    $offer           = $projects_offers->GetPrjOfferById( $rec_id );
                    $projects        = new projects;
                    $project         = $projects->GetPrjCust( $offer['project_id'] );
                    $sObjName        = $project['name'];
                    $sObjLink        = getFriendlyURL( 'project', $offer['project_id'] ); 

                    if ( $is_sent == 'f' ) {
                        // отправка уведомления о новом сообщении
                        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/pmail.php' );

                        $pmail = new pmail;
                        $pmail->NewPrjOffer( $rec_id );

                        $GLOBALS['DB']->update( 'projects_offers', array('is_sent' => true), 'id = ?i', $rec_id ); // устанавливаем флаг отправки
                    }

                    if ( $offer['blocked_time'] ) {
                        $projects_offers->UnBlocked( $rec_id );

                        // пишем лог админских действий
                        admin_log::addLog( admin_log::OBJ_CODE_PROJ, admin_log::ACT_ID_PRJ_UNBLOCK_OFFER, 
                            $from_id, $rec_id, $sObjName, $sObjLink, 0, '', 0, '' 
                        );
                    }
                }
                break;
            case self::MODER_ART_COM:
                // Комментарии в статьях
                $sDeluserId = $GLOBALS['DB']->val( 'SELECT deleted_id FROM articles_comments WHERE id = ?i', $rec_id );

                if ( $sDeluserId && $sDeluserId != $from_id ) {
                        if ( $is_sent == 'f' ) {
                        // отправка уведомления о новом сообщении
                        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/pmail.php' );

                        $pmail = new pmail;
                        $pmail->ArticleNewComment( $rec_id );

                        $aData['is_sent'] = true; // устанавливаем флаг отправки
                    }

                    $aData['modified_time'] = null;
                    $aData['deleted_id']    = null;
                    $aData['deleted_reason'] = '';

                    $GLOBALS['DB']->update( 'articles_comments', $aData, 'id = ?i', $rec_id );
                }
                break;
            case self::MODER_PRJ_DIALOG:
                // Комментарии к предложениям по проектам
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_offers_dialogue.php");
                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/admin_log.php' );

                $bRet     = true;
                $dialogue = projects_offers_dialogue::getDialogueMessageById( $rec_id );
                $sObjName = $dialogue['project_name'];
                $sObjLink = getFriendlyURL( 'project', $dialogue['project_id'] );

                if ( $is_sent == 'f' ) {
                    // отправка уведомления о новом сообщении
                    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/pmail.php' );

                    $pmail = new pmail;
                    $pmail->NewPrjMessageOnOffer( $rec_id );

                    $GLOBALS['DB']->update( 'projects_offers_dialogue', array('is_sent' => true), 'id = ?i', $rec_id ); // устанавливаем флаг отправки
                }

                if ( $dialogue['is_blocked'] == 't' ) {
                    projects_offers_dialogue::UnBlocked( $rec_id );

                    // пишем лог админских действий
                    admin_log::addLog( admin_log::OBJ_CODE_PROJ, admin_log::ACT_ID_PRJ_DIALOG_UNBLOCK, $from_id, $rec_id, $sObjName, $sObjLink, 0, '', 0, '' );
                }
                break;
            case self::MODER_CONTEST_COM:
                // Комментарии к предложениям конкурсов
                $sDeluserId = $GLOBALS['DB']->val( 'SELECT deluser_id FROM projects_contest_msgs WHERE id = ?i', $rec_id );

                if ( $sDeluserId && $sDeluserId != $from_id ) {
                    if ( $is_sent == 'f' ) {
                        // отправка уведомления о новом сообщении
                        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/pmail.php' );

                        $pmail = new pmail;
                        $pmail->ContestNewComment( $rec_id );

                        $aData['is_sent'] = true; // устанавливаем флаг отправки
                    }

                    $aData['deleted']        = null;
                    $aData['deluser_id']     = null;
                    $aData['deleted_reason'] = '';

                    $GLOBALS['DB']->update( 'projects_contest_msgs', $aData, 'id = ?i', $rec_id );
                }
                break;
            case self::MODER_PORTFOLIO:
                // Работы в портфолио
                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/portfolio.php' );
                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/admin_log.php' );

                $portfolio = portfolio::GetPrj( $rec_id );
                $bRet      = true;
                $objUser   = new users();
                $objUser->GetUserByUID( $from_id );

                $sObjName  = $portfolio['name'];
                $sObjLink  = '/users/'. $objUser->login .'/viewproj.php?prjid='. $portfolio['id']; 

                if ( $portfolio['is_blocked'] == 't' ) {
                    portfolio::UnBlocked( $rec_id );

                    // пишем лог админских действий
                    admin_log::addLog( admin_log::OBJ_CODE_PROJ, admin_log::ACT_ID_PORTFOLIO_UNBLOCK, $from_id, $rec_id, $sObjName, $sObjLink, 0, '', 0, '' );
                }
                break;
            case self::MODER_SDELAU:
                // Предложения фрилансеров "Сделаю"
                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/freelancer_offers.php' );
                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/admin_log.php' );

                $frl_offers = new freelancer_offers();
                $offer      = $frl_offers->getOfferById( $rec_id );
                $sObjName   = $offer['title'];
                $sObjLink   = ''; // нет ссылки на конкретное предложение
                $update     = array();

                if ( $offer['admin'] ) { //утверждаем
                    $update = array( 'is_blocked' => false, 'reason'=> '', 'reason_id' => 0, 'admin' => 0 );
                    admin_log::addLog( admin_log::OBJ_CODE_OFFER, 14, $from_id, $rec_id, $sObjName, $sObjLink, 0, '', 0, '' );
                    $GLOBALS['DB']->update( 'freelance_offers', $update, 'id = ?i', $rec_id );
                }
                break;
            default:
                return false;
                break;
        }
    }
    
    /**
     * Личные сообщения. Утверждение/удаление записи
     * 
     * @param  string $stream_id идентификатор потока
     * @param  int $user_id идентификатор модератора
     * @param  int $from_id идентификатор пользователя
     * @param  int $rec_id идентификатор записи
     * @param  int $rec_type тип записи 
     * @param  int $action действие: 1 - утверждено, 2 - удалено
     * @param  string $is_sent было ли отправлено уведомление
     * @param  string $reason причина удаления
     */
    function resolveMessages( $stream_id = '', $user_id = 0, $from_id = 0, $rec_id = 0, $rec_type = 0, $action = 1, $is_sent = '', $reason = '' ) {
        $bRet   = false;
        $DB     = new DB( 'plproxy' );
        $sQuery = 'SELECT messages_moder_up(?i, ?i, ?i, ?);';

        $DB->query( $sQuery, $user_id, $rec_id, $action, $reason );
        
        $bRet = empty( $DB->error );
        
        /*
        // отправка уведомлений сразу и без всяких цензур.
        $sQuery = 'SELECT * FROM messages_moder_send_get(?i, ?i);';
        $aMsg   = $DB->row( $sQuery, $from_id, $rec_id );

        if ( $action == 1 ) {
            // отправка уведомления о новом сообщении
            if ( $aMsg['is_sent'] == 'f' ) {
                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/pmail.php' );

                $pmail = new pmail;
                $pmail->NewMessage( $from_id, $aMsg['to_id'], $aMsg['msg_text'] );

                $sQuery = 'SELECT messages_moder_send_set(?i, ?i);';

                $DB->row( $sQuery, $from_id, $rec_id );
            }
        }
        */
        
        return $bRet;
    }
    
    /**
     * Блоги. Утверждение/удаление записи
     * 
     * @param  string $stream_id идентификатор потока
     * @param  int $user_id идентификатор модератора
     * @param  int $from_id идентификатор пользователя
     * @param  int $rec_id идентификатор записи
     * @param  int $rec_type тип записи 
     * @param  int $action действие: 1 - утверждено, 2 - удалено
     * @param  string $is_sent было ли отправлено уведомление
     * @param  string $reason причина удаления
     * @return bool true - успех, false - провал
     */
    function resolveBlogs( $stream_id = '', $user_id = 0, $from_id = 0, $rec_id = 0, $rec_type = 0, $action = 1, $is_sent = '', $reason = '' ) {
        $bRet   = false;
        $sQuery = 'UPDATE moderation SET moder_num = ?i, status = ?i WHERE rec_id = ?i AND rec_type = ?i AND stream_id = ? RETURNING rec_id';
        $sRecId = $GLOBALS['DB']->val( $sQuery, $this->nResolveCnt, $action, $rec_id, self::MODER_BLOGS, $stream_id );
        
        if ( $sRecId ) {
            $bRet  = true;
            $aData = array( 'moderator_status' => $user_id );
            
            if ( $rec_type == 1 ) { // топик
                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/blogs.php' );
                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/admin_log.php' );
                
                $blogs  = new blogs;
                $sQuery = 'SELECT bm.title, bm.thread_id AS id, u.uname, u.usurname, bb.blocked_time, bm.fromuser_id 
                    FROM blogs_msgs bm 
                    INNER JOIN users u ON u.uid = bm.fromuser_id 
                    LEFT JOIN blogs_blocked bb ON bb.thread_id = bm.thread_id 
                    WHERE bm.id = ?i';
                
                $aThread  = $GLOBALS['DB']->row( $sQuery, $rec_id );
                $sObjName = $aThread['title'] ? $aThread['title'] : '<без темы>';
                $sObjLink = '/blogs/view.php?tr=' . $aThread['id'];
                
                if ( $action == 1 && $aThread['blocked_time'] ) {
                    $blogs->UnBlocked( $aThread['id'] );
                    // пишем лог админских действий
                    admin_log::addLog( admin_log::OBJ_CODE_BLOG, 8, $aThread['fromuser_id'], $aThread['id'], $sObjName, $sObjLink, 0, '', 0, '' );
                }
                elseif ( $action == 2 && !$aThread['blocked_time'] ) {
                    $sBlockId  = $blogs->Blocked( $aThread['id'], $reason, 0, $_SESSION['uid'] );
                    // пишем лог админских действий
                    admin_log::addLog( admin_log::OBJ_CODE_BLOG, 7, $aThread['fromuser_id'], $aThread['id'], $sObjName, $sObjLink, 0, '', 0, $reason, $sBlockId );
                }
            }
            else {
                if ( $action == 1 && $rec_type == '2' && $is_sent == 'f' ) {
                    $aData['is_sent'] = true; // устанавливаем флаг отправки
                }

                $sDeluserId = $GLOBALS['DB']->val( 'SELECT deluser_id FROM blogs_msgs WHERE id = ?i', $rec_id );

                if ( $action == 1 && $sDeluserId && $sDeluserId != $from_id ) {
                    $aData['deleted']    = null;
                    $aData['deluser_id'] = null;
                    $aData['deleted_reason'] = '';
                }
                elseif ( $action == 2 && $sDeluserId != $from_id ) {
                    $aData['deleted']    = date( 'Y-m-d H:i:s' );
                    $aData['deluser_id'] = $user_id;
                    $aData['deleted_reason'] = $reason;
                }
            }
            
            $GLOBALS['DB']->update( 'blogs_msgs', $aData, 'id = ?i', $rec_id );
        }
        
        return $bRet;
    }
    
    /**
     * Сообщества. Утверждение/удаление записи
     * 
     * @param  string $stream_id идентификатор потока
     * @param  int $user_id идентификатор модератора
     * @param  int $from_id идентификатор пользователя
     * @param  int $rec_id идентификатор записи
     * @param  int $rec_type тип записи 
     * @param  int $action действие: 1 - утверждено, 2 - удалено
     * @param  string $is_sent было ли отправлено уведомление
     * @param  string $reason причина удаления
     * @return bool true - успех, false - провал
     */
    function resolveCommunity( $stream_id = '', $user_id = 0, $from_id = 0, $rec_id = 0, $rec_type = 0, $action = 1, $is_sent = '', $reason = '' ) {
        $bRet   = false;
        $sQuery = 'UPDATE moderation SET moder_num = ?i, status = ?i WHERE rec_id = ?i AND rec_type = ?i AND stream_id = ? RETURNING rec_id';
        $sRecId = $GLOBALS['DB']->val( $sQuery, $this->nResolveCnt, $action, $rec_id, self::MODER_COMMUNITY, $stream_id );
        
        if ( $sRecId ) {
            $bRet  = true;
            
            if ( $rec_type == 1 ) { // топик
                $GLOBALS['DB']->update( 'commune_messages', array('moderator_status' => $user_id, 'mod_access' => 1), 'id = ?i', $rec_id );

                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/commune.php' );
                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/admin_log.php' );

                $commune  = new commune;
                $topic    = commune::GetTopMessageByAnyOther( $rec_id, $user_id, commune::MOD_ADMIN );
                $sObjName = $topic['title'];
                $sObjLink = getFriendlyURL( 'commune', $rec_id );

                if ( $action == 1 && $topic['is_blocked_s'] == 't' ) { //утверждаем
                    $commune->unblockedCommuneTheme( $topic['theme_id'] );
                    admin_log::addLog( admin_log::OBJ_CODE_COMM, 16, $from_id, $topic['theme_id'], $sObjName, $sObjLink, 0, '', 0, '' );
                }
                elseif ( $action == 2 && $topic['is_blocked_s'] != 't' ) {
                    $commune->blockedCommuneTheme( $topic, $reason, 0, $user_id, true );
                    admin_log::addLog( admin_log::OBJ_CODE_COMM, 15, $from_id, $topic['theme_id'], $sObjName, $sObjLink, 0, '', 0, $reason, $topic['theme_id'] );
                }
            }
            else { // комментарий
                $aData = array( 'moderator_status' => $user_id, 'mod_access' => 1 );

                if ( $action == 1 && $is_sent == 'f' ) {
                    // отправка уведомления о новом сообщении
                    /*require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/pmail.php' );

                    $pmail = new pmail;
                    $pmail->CommuneNewComment( $rec_id );*/

                    $aData['is_sent'] = true; // устанавливаем флаг отправки
                }
                
                $aRow = $GLOBALS['DB']->row( 'SELECT theme_id, deleted_id FROM commune_messages WHERE id = ?i', $rec_id );
                
                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/comments/CommentsCommune.php' );
                $comments = new CommentsCommune($aRow['theme_id']);
                
                if ( $action == 1 && $aRow['deleted_id'] && $aRow['deleted_id'] != $from_id ) {
                    $comments->restore( $rec_id );
                }
                elseif ( $action == 2 && $aRow['deleted_id'] != $from_id ) {
                    $comments->delete( $rec_id, $from_id, true );
                    $aData['deleted_reason'] = $reason;
                }
                
                $GLOBALS['DB']->update( 'commune_messages', $aData, 'id = ?i', $rec_id );
            }
        }
        
        return $bRet;
    }
    
    
    
    /**
     * Рексизиты финансов. Утверждение/удаление записи
     * 
     * @param  string $stream_id идентификатор потока
     * @param  int $user_id идентификатор модератора
     * @param  int $from_id идентификатор пользователя
     * @param  int $rec_id идентификатор записи
     * @param  int $rec_type тип записи 
     * @param  int $action действие: 1 - утверждено, 2 - удалено
     * @param  string $is_sent было ли отправлено уведомление
     * @param  string $reason причина удаления
     */
    function resolveSbrReqv( $stream_id = '', $user_id = 0, $from_id = 0, $rec_id = 0, $rec_type = 0, $action = 1, $is_sent = '', $reason = '' ){
        
        $bRet   = false;
        $sQuery = 'UPDATE moderation SET moder_num = ?i, status = ?i WHERE rec_id = ?i AND rec_type = ?i AND stream_id = ? RETURNING rec_id';
        $sRecId = $GLOBALS['DB']->val( $sQuery, $this->nResolveCnt, $action, $rec_id, self::MODER_SBR_REQV, $stream_id );
        
        
        if ($sRecId) 
        {
            $data = $GLOBALS['DB']->row("
                SELECT 
                    sr.*,
                    COALESCE(srb.src_id::boolean, FALSE) AS is_blocked 
                FROM sbr_reqv AS sr
                LEFT JOIN sbr_reqv_blocked AS srb ON srb.src_id = sr.user_id
                WHERE sr.user_id = ?i
                LIMIT 1
            ", $rec_id);
            
            if($data)
            {
                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/Finance/FinanceSmail.php' );
                
                $finSmail = new FinanceSmail();
                $bRet  = true;
                
                if($action == 1 && $data['is_blocked'] == 't')
                {
                    //разблокируем если было блокированно
                    $GLOBALS['DB']->query("
                        DELETE FROM sbr_reqv_blocked 
                        WHERE src_id = ?i
                    ",$rec_id);
                    
                    //Обновляем статус на "Проверенные данные"
                    if($GLOBALS['DB']->update('sbr_reqv',array(
                        'validate_status' => 2
                    ),'user_id = ?i', $rec_id))
                    {
                        $finSmail->financeUnBlocked($rec_id);
                    }
                }
                elseif($action == 2 && $data['is_blocked'] != 't')
                {
                    //блокируем
                    $sBlockId = $GLOBALS['DB']->val("
                        INSERT INTO sbr_reqv_blocked (
                            src_id, 
                            admin, 
                            reason, 
                            reason_id, 
                            blocked_time) 
                        VALUES(?i, ?i, ?, ?i, NOW()) RETURNING id
                     ",$rec_id, $user_id, $reason, 0);                    
                    
                    //Обновляем статус на "Отклоненные данные"
                    if($GLOBALS['DB']->update('sbr_reqv',array(
                        'validate_status' => -1
                    ),'user_id = ?i', $rec_id))
                    {
                        $finSmail->financeBlocked($rec_id, $reason);
                    }
                }
            }
        }
        
        
        return $bRet;
    }
    
    
    
    /**
     * Типовые услуги. Утверждение/удаление записи
     * 
     * @param  string $stream_id идентификатор потока
     * @param  int $user_id идентификатор модератора
     * @param  int $from_id идентификатор пользователя
     * @param  int $rec_id идентификатор записи
     * @param  int $rec_type тип записи 
     * @param  int $action действие: 1 - утверждено, 2 - удалено
     * @param  string $is_sent было ли отправлено уведомление
     * @param  string $reason причина удаления
     */
    function resolveTServices( $stream_id = '', $user_id = 0, $from_id = 0, $rec_id = 0, $rec_type = 0, $action = 1, $is_sent = '', $reason = '' ){
        
        $bRet   = false;
        $sQuery = 'UPDATE moderation SET moder_num = ?i, status = ?i WHERE rec_id = ?i AND rec_type = ?i AND stream_id = ? RETURNING rec_id';
        $sRecId = $GLOBALS['DB']->val( $sQuery, $this->nResolveCnt, $action, $rec_id, self::MODER_TSERVICES, $stream_id );
        
        
        if ($sRecId) 
        {
            
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/admin_log.php' );
            
            $tservice = $GLOBALS['DB']->row("
                SELECT
                s.id,
                s.title, 
                u.login, 
                u.uname,
                u.usurname,
                COALESCE(sb.src_id::boolean, FALSE) AS is_blocked 
            FROM tservices AS s 
            LEFT JOIN tservices_blocked AS sb ON sb.src_id = s.id  
            INNER JOIN freelancer AS u ON u.uid = s.user_id 
            WHERE s.id = ?i 
            LIMIT 1
            ",$rec_id);
           
            if($tservice)
            {
                $bRet  = true;

                $sObjLink = sprintf('%s/tu/%d/%s.html',$GLOBALS['host'],$tservice['id'],translit(strtolower(htmlspecialchars_decode($tservice['title'], ENT_QUOTES))));
                
                if($action == 1 && $tservice['is_blocked'] == 't')
                {
                    //разблокируем
                    $GLOBALS['DB']->query("
                        DELETE FROM tservices_blocked 
                        WHERE src_id = ?i
                    ",$rec_id);

                    // пишем лог админских действий
                    admin_log::addLog( 
                            admin_log::OBJ_CODE_TSERVICES, 
                            65, 
                            $from_id, 
                            $rec_id, 
                            $tservice['title'], 
                            $sObjLink, 
                            0, 
                            '', 
                            0, 
                            '');
                }
                elseif($action == 2 && $tservice['is_blocked'] != 't')
                {
                    //блокируем
                    $sBlockId = $GLOBALS['DB']->val("
                        INSERT INTO tservices_blocked (
                            src_id, 
                            admin, 
                            reason, 
                            reason_id, 
                            blocked_time) 
                        VALUES(?i, ?i, ?, ?i, NOW()) RETURNING id
                     ",$rec_id, $user_id, $reason, 0);

                    //пишем лог админских действий
                    admin_log::addLog(
                            admin_log::OBJ_CODE_TSERVICES, 
                            64, 
                            $from_id, 
                            $rec_id, 
                            $tservice['title'], 
                            $sObjLink, 
                            0, 
                            '', 
                            0, 
                            $reason,
                            $sBlockId);
                    
                    
                    //отправляем сообщение о блокировки
                    require_once ( $_SERVER['DOCUMENT_ROOT'] . '/classes/messages.php' );
                    
                    messages::SendBlockedTServices($tservice, $reason);
                    
                    
                }
            }
        }

        return $bRet;
    }








    /**
     * Проекты. Утверждение/удаление записи
     * 
     * @param  string $stream_id идентификатор потока
     * @param  int $user_id идентификатор модератора
     * @param  int $from_id идентификатор пользователя
     * @param  int $rec_id идентификатор записи
     * @param  int $rec_type тип записи 
     * @param  int $action действие: 1 - утверждено, 2 - удалено
     * @param  string $is_sent было ли отправлено уведомление
     * @param  string $reason причина удаления
     */
    function resolveProjects( $stream_id = '', $user_id = 0, $from_id = 0, $rec_id = 0, $rec_type = 0, $action = 1, $is_sent = '', $reason = '' ) {
        $bRet   = false;
        $sQuery = 'UPDATE moderation SET moder_num = ?i, status = ?i WHERE rec_id = ?i AND rec_type = ?i AND stream_id = ? RETURNING rec_id';
        $sRecId = $GLOBALS['DB']->val( $sQuery, $this->nResolveCnt, $action, $rec_id, self::MODER_PROJECTS, $stream_id );
        
        if ( $sRecId ) {
            $bRet  = true;
            
            $GLOBALS['DB']->update( 'projects', array('moderator_status' => $user_id), 'id = ?i', $rec_id );

            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/projects.php' );
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/admin_log.php' );

            $projects = new projects;
            $project  = $projects->GetPrjCust( $rec_id );
            $sObjLink = getFriendlyURL( 'project', $rec_id ); // лог админских действий

            if ( $action == 1 && $project['blocked_time'] ) {
                // разблокируем
                $projects->UnBlocked( $rec_id );
                admin_log::addLog( admin_log::OBJ_CODE_PROJ, 10, $from_id, $rec_id, $project['name'], $sObjLink, 0, '', 0, '' );
            }
            elseif ( $action == 2 && !$project['blocked_time'] ) {
                // блокируем
                $sBlockId = $projects->Blocked( $rec_id, $reason, 0, $user_id, true );

                // Удаляем черновики жалобы
                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/drafts.php' );
                drafts::DeleteDraftByPrjID( $rec_id );
                $projects->DeleteComplains( $rec_id );

                // пишем лог админских действий
                admin_log::addLog( admin_log::OBJ_CODE_PROJ, 9, $from_id, $rec_id, $project['name'], $sObjLink, 0, '', 0, $reason, $sBlockId );
            }
        }
        
        return $bRet;
    }
    
    /**
     * Помечает проект заблокированным в админке, но не блокирует сам проект
     * @param type $stream_id
     * @param type $user_id
     * @param type $from_id
     * @param type $rec_id
     * @param type $rec_type
     * @param type $action
     * @param type $is_sent
     * @param type $reason
     */
    function markProjectBlocked( $stream_id = '', $rec_id = 0) {
        $sQuery = 'UPDATE moderation SET moder_num = ?i, status = ?i WHERE rec_id = ?i AND rec_type = ?i AND stream_id = ? RETURNING rec_id';
        $sRecId = $GLOBALS['DB']->val( $sQuery, $this->nResolveCnt, 2, $rec_id, self::MODER_PROJECTS, $stream_id );
        return $sRecId;
    }
    
    /**
     * Предложения в проектах. Утверждение/удаление записи
     * 
     * @param  string $stream_id идентификатор потока
     * @param  int $user_id идентификатор модератора
     * @param  int $from_id идентификатор пользователя
     * @param  int $rec_id идентификатор записи
     * @param  int $rec_type тип записи 
     * @param  int $action действие: 1 - утверждено, 2 - удалено
     * @param  string $is_sent было ли отправлено уведомление
     * @param  string $reason причина удаления
     */
    function resolvePrjOffers( $stream_id = '', $user_id = 0, $from_id = 0, $rec_id = 0, $rec_type = 0, $action = 1, $is_sent = '', $reason = '' ) {
        $bRet   = false;
        $sQuery = 'UPDATE moderation SET moder_num = ?i, status = ?i WHERE rec_id = ?i AND rec_type = ?i AND stream_id = ? RETURNING rec_id';
        $sRecId = $GLOBALS['DB']->val( $sQuery, $this->nResolveCnt, $action, $rec_id, self::MODER_PRJ_OFFERS, $stream_id );
        
        if ( $sRecId ) {
            $bRet  = true;
            
            if ( $rec_type == 7 ) {
                $aData = array( 'moderator_status' => $user_id );

                if ( $action == 1 && $is_sent == 'f' ) {
                    // отправка уведомления о новом сообщении
                    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/pmail.php' );

                    $pmail = new pmail;
                    $pmail->NewPrjOffer( $rec_id );

                    $aData['is_sent'] = true; // устанавливаем флаг отправки
                }
                
                $sDeluserId = $GLOBALS['DB']->val( 'SELECT deluser_id FROM projects_contest_offers WHERE id = ?i', $rec_id );
                
                if ( $action == 1 && $sDeluserId && $sDeluserId != $from_id ) {
                    $aData['is_deleted'] = false;
                    $aData['deluser_id'] = null;
                    $aData['deleted_reason'] = '';
                }
                elseif ( $action == 2 && $sDeluserId != $from_id ) {
                    $aData['is_deleted'] = true;
                    $aData['deluser_id'] = $user_id;
                    $aData['deleted_reason'] = $reason;
                }
                
                $GLOBALS['DB']->update( 'projects_contest_offers', $aData, 'id = ?i', $rec_id );
                $GLOBALS['DB']->update( 'projects_offers', array('moderator_status' => $user_id), 'id = ?i', $rec_id );
            }
            else {
                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/projects_offers.php' );
                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/admin_log.php' );
                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/projects.php' );
                
                $projects_offers = new projects_offers();
                $offer           = $projects_offers->GetPrjOfferById( $rec_id );
                $aData = $aData2 = array( 'moderator_status' => $user_id );
                $projects        = new projects;
                $project         = $projects->GetPrjCust( $offer['project_id'] );
                $sObjName        = $project['name'];
                $sObjLink        = getFriendlyURL( 'project', $offer['project_id'] ); 
                
                if ( $action == 1 ) {
                    if ( $is_sent == 'f' ) {
                        // отправка уведомления о новом сообщении
                        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/pmail.php' );

                        $pmail = new pmail;
                        $pmail->NewPrjOffer( $rec_id );

                        $aData['is_sent'] = true; // устанавливаем флаг отправки
                    }
                    
                    if ( $offer['blocked_time'] ) {
                        $projects_offers->UnBlocked( $rec_id );

                        // пишем лог админских действий
                        admin_log::addLog( admin_log::OBJ_CODE_PROJ, admin_log::ACT_ID_PRJ_UNBLOCK_OFFER, 
                            $from_id, $rec_id, $sObjName, $sObjLink, 0, '', 0, '' 
                        );
                    }
                }
                elseif ( $action == 2 && !$offer['blocked_time'] ) {
                    $sReason  = 'Содержит запрещенную информацию';
                    $sBlockId = $projects_offers->Blocked( $rec_id, $from_id, $offer['project_id'], $reason, 0, $user_id, true );

                    // пишем лог админских действий
                    admin_log::addLog( admin_log::OBJ_CODE_PROJ, admin_log::ACT_ID_PRJ_BLOCK_OFFER, 
                        $from_id, $rec_id, $sObjName, $sObjLink, 0, '', 0, $sReason, $sBlockId 
                    );
                }
                
                $GLOBALS['DB']->update( 'projects_offers', $aData, 'id = ?i', $rec_id );
                $GLOBALS['DB']->update( 'projects_offers_dialogue', $aData2, 'po_id = ?i AND root = true', $rec_id );
            }
        }
        
        return $bRet;
    }
    

    /**
     * Утверждение/удаление записи
     * 
     * @param  string $stream_id идентификатор потока
     * @param  int $user_id идентификатор модератора
     * @param  int $from_id идентификатор пользователя
     * @param  int $rec_id идентификатор записи
     * @param  int $rec_type тип записи 
     * @param  int $action действие: 1 - утверждено, 2 - удалено
     * @param  string $is_sent было ли отправлено уведомление
     * @param  string $reason причина удаления
     */
    function resolveArtCom( $stream_id = '', $user_id = 0, $from_id = 0, $rec_id = 0, $rec_type = 0, $action = 1, $is_sent = '', $reason = '' ) {
        $bRet   = false;
        $sQuery = 'UPDATE moderation SET moder_num = ?i, status = ?i WHERE rec_id = ?i AND rec_type = ?i AND stream_id = ? RETURNING rec_id';
        $sRecId = $GLOBALS['DB']->val( $sQuery, $this->nResolveCnt, $action, $rec_id, self::MODER_ART_COM, $stream_id );
        
        if ( $sRecId ) {
            $bRet  = true;
            $aData = array( 'moderator_status' => $user_id );

            if ( $action == 1 && $is_sent == 'f' ) {
                // отправка уведомления о новом сообщении
                /*require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/pmail.php' );

                $pmail = new pmail;
                $pmail->ArticleNewComment( $rec_id );*/

                $aData['is_sent'] = true; // устанавливаем флаг отправки
            }
            
            $sDeluserId = $GLOBALS['DB']->val( 'SELECT deleted_id FROM articles_comments WHERE id = ?i', $rec_id );
            
            if ( $action == 1 && $sDeluserId && $sDeluserId != $from_id ) {
                $aData['modified_time'] = null;
                $aData['deleted_id']    = null;
                $aData['deleted_reason'] = '';
            }
            elseif ( $action == 2 && $sDeluserId != $from_id ) {
                $aData['modified_time'] = date( 'Y-m-d H:i:s' );
                $aData['deleted_id']    = $user_id;
                $aData['deleted_reason'] = $reason;
            }

            $GLOBALS['DB']->update( 'articles_comments', $aData, 'id = ?i', $rec_id );
        }
        
        return $bRet;
    }
    
    /**
     * Изменения в профилях. Утверждение/удаление записи
     * 
     * @param  string $stream_id идентификатор потока
     * @param  int $user_id идентификатор модератора
     * @param  int $from_id идентификатор пользователя
     * @param  int $rec_id идентификатор записи
     * @param  int $rec_type тип записи 
     * @param  int $action действие: 1 - утверждено, 2 - удалено
     * @param  string $is_sent было ли отправлено уведомление
     * @param  string $reason причина удаления
     */
    function resolveProfile( $stream_id = '', $user_id = 0, $from_id = 0, $rec_id = 0, $rec_type = 0, $action = 1, $is_sent = '', $reason = '' ) {
        $bRet    = false;
        $aChange = $GLOBALS['DB']->row( 'SELECT c.utable, c.ucolumn, c.old_val, c.new_val, u.login, u.uname, u.usurname 
            FROM users_change c 
            INNER JOIN moderation m ON m.rec_id = c.id 
            INNER JOIN users u ON u.uid = c.user_id 
            WHERE c.id = ?i AND m.rec_type = ?i AND m.stream_id = ?', $rec_id, self::MODER_PROFILE, $stream_id 
        );
        
        if ( $aChange ) {
            $bRet    = true;
            $sNewVal = $action == 1 ? $aChange['new_val'] : $aChange['old_val'];
            $sDelVal = $action != 1 ? $aChange['new_val'] : $aChange['old_val'];
            
            if ( in_array($aChange['ucolumn'], array('resume_file', 'photo', 'logo')) && $sDelVal ) {
                $dir   = $aChange['ucolumn'] == 'resume_file' ? 'resume' : ($aChange['ucolumn'] == 'photo' ? 'foto' : 'logo');
                $oFile = new CFile();
                $oFile->Delete( 0, 'users/'.substr($aChange['login'], 0, 2).'/'.$aChange['login'].'/'.$dir.'/', $sDelVal );
                
                if ( in_array($aChange['ucolumn'], array('photo', 'logo')) ) {
                    $oFile->Delete( 0, 'users/'.substr($aChange['login'], 0, 2).'/'.$aChange['login'].'/'.$dir.'/', 'sm_'.$sDelVal );
                }
            }
            
            $GLOBALS['DB']->query( "UPDATE {$aChange['utable']} SET {$aChange['ucolumn']} = ?, moduser_id = ?i WHERE uid= ?i", $sNewVal, $user_id, $from_id );
            $GLOBALS['DB']->query( 'DELETE FROM users_change WHERE id = ?i', $rec_id );
            $GLOBALS['DB']->query( 'DELETE FROM moderation WHERE rec_id = ?i AND rec_type = ?i', $rec_id, self::MODER_PROFILE );
        }
        
        return $bRet;
    }
    
    /**
     * Изменения в профилях. редактирование админом
     * 
     * @param  int $rec_id идентификатор записи
     * @param  string $new_val новое значение
     * @return bool true - успех, false -провал
     */
    function editProfile( $rec_id = 0, $new_val = '' ) {
        $GLOBALS['DB']->query( 'UPDATE users_change SET new_val = ? WHERE id = ?i', $new_val, $rec_id );
        
        return empty($GLOBALS['DB']->error);
    }
    
    /**
     * Комментарии к предложениям по проектам. Утверждение/удаление записи
     * 
     * @param  string $stream_id идентификатор потока
     * @param  int $user_id идентификатор модератора
     * @param  int $from_id идентификатор пользователя
     * @param  int $rec_id идентификатор записи
     * @param  int $rec_type тип записи 
     * @param  int $action действие: 1 - утверждено, 2 - удалено
     * @param  string $is_sent было ли отправлено уведомление
     * @param  string $reason причина удаления
     */
    function resolvePrjDialog( $stream_id = '', $user_id = 0, $from_id = 0, $rec_id = 0, $rec_type = 0, $action = 1, $is_sent = '', $reason = '' ) {
        $bRet   = false;
        $sQuery = 'UPDATE moderation SET moder_num = ?i, status = ?i WHERE rec_id = ?i AND rec_type = ?i AND stream_id = ? RETURNING rec_id';
        $sRecId = $GLOBALS['DB']->val( $sQuery, $this->nResolveCnt, $action, $rec_id, self::MODER_PRJ_DIALOG, $stream_id );
        
        if ( $sRecId ) {
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_offers_dialogue.php");
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/admin_log.php' );
            
            $bRet     = true;
            $dialogue = projects_offers_dialogue::getDialogueMessageById( $rec_id );
            $sObjName = $dialogue['project_name'];
			$sObjLink = getFriendlyURL( 'project', $dialogue['project_id'] );
            $aData    = array('moderator_status' => $user_id);
            
            if ( $action == 1 ) {
                if ( $is_sent == 'f' ) {
                    // отправка уведомления о новом сообщении
                    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/pmail.php' );

                    $pmail = new pmail;
                    $pmail->NewPrjMessageOnOffer( $rec_id );

                    $aData['is_sent'] = true; // устанавливаем флаг отправки
                }
                
                if ( $dialogue['is_blocked'] == 't' ) {
                    projects_offers_dialogue::UnBlocked( $rec_id );

                    // пишем лог админских действий
                    admin_log::addLog( admin_log::OBJ_CODE_PROJ, admin_log::ACT_ID_PRJ_DIALOG_UNBLOCK, 
                        $from_id, $rec_id, $sObjName, $sObjLink, 0, '', 0, '' 
                    );
                }
            }
            elseif ( $action == 2 && $dialogue['is_blocked'] != 't' ) {
                $sReason  = 'Содержит запрещенную информацию';
                $sBlockId = projects_offers_dialogue::Blocked( $rec_id, $reason, 0, $_SESSION['uid'], true );
                
                // пишем лог админских действий
                admin_log::addLog( admin_log::OBJ_CODE_PROJ, admin_log::ACT_ID_PRJ_DIALOG_BLOCK, 
                    $from_id, $rec_id, $sObjName, $sObjLink, 0, '', 0, $sReason, $sBlockId 
                );
            }
            
            $GLOBALS['DB']->update( 'projects_offers_dialogue', $aData, 'id = ?i', $rec_id );
        }
        
        return $bRet;
    }
    
    /**
     * Комментарии к предложениям конкурсов. Утверждение/удаление записи
     * 
     * @param  string $stream_id идентификатор потока
     * @param  int $user_id идентификатор модератора
     * @param  int $from_id идентификатор пользователя
     * @param  int $rec_id идентификатор записи
     * @param  int $rec_type тип записи 
     * @param  int $action действие: 1 - утверждено, 2 - удалено
     * @param  string $is_sent было ли отправлено уведомление
     * @param  string $reason причина удаления
     */
    function resolveContestCom( $stream_id = '', $user_id = 0, $from_id = 0, $rec_id = 0, $rec_type = 0, $action = 1, $is_sent = '', $reason = '' ) {
        $bRet   = false;
        $sQuery = 'UPDATE moderation SET moder_num = ?i, status = ?i WHERE rec_id = ?i AND rec_type = ?i AND stream_id = ? RETURNING rec_id';
        $sRecId = $GLOBALS['DB']->val( $sQuery, $this->nResolveCnt, $action, $rec_id, self::MODER_CONTEST_COM, $stream_id );
        
        if ( $sRecId ) {
            $bRet  = true;
            $aData = array( 'moderator_status' => $user_id );

            if ( $action == 1 && $is_sent == 'f' ) {
                // отправка уведомления о новом сообщении
                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/pmail.php' );

                $pmail = new pmail;
                $pmail->ContestNewComment( $rec_id );

                $aData['is_sent'] = true; // устанавливаем флаг отправки
            }
            
            $sDeluserId = $GLOBALS['DB']->val( 'SELECT deluser_id FROM projects_contest_msgs WHERE id = ?i', $rec_id );
            
            if ( $action == 1 && $sDeluserId && $sDeluserId != $from_id ) {
                $aData['deleted']    = null;
                $aData['deluser_id'] = null;
                $aData['deleted_reason'] = '';
            }
            elseif ( $action == 2 && $sDeluserId != $from_id ) {
                $aData['deleted']    = date( 'Y-m-d H:i:s' );
                $aData['deluser_id'] = $user_id;
                $aData['deleted_reason'] = $reason;
            }
            
            $GLOBALS['DB']->update( 'projects_contest_msgs', $aData, 'id = ?i', $rec_id );
        }
        
        return $bRet;
    }
    
    /**
     * Уточнения к разделам в портфолио. Утверждение/удаление записи
     * 
     * @param  string $stream_id идентификатор потока
     * @param  int $user_id идентификатор модератора
     * @param  int $from_id идентификатор пользователя
     * @param  int $rec_id идентификатор записи
     * @param  int $rec_type тип записи 
     * @param  int $action действие: 1 - утверждено, 2 - удалено
     * @param  string $is_sent было ли отправлено уведомление
     * @param  string $reason причина удаления
     */
    function resolvePortfChoice( $stream_id = '', $user_id = 0, $from_id = 0, $rec_id = 0, $rec_type = 0, $action = 1, $is_sent = '', $reason = '' ) {
        $bRet    = false;
        $aChange = $GLOBALS['DB']->row( 'SELECT c.prof_id, c.ucolumn, c.old_val, u.login, u.uname, u.usurname 
            FROM portf_choise_change c 
            INNER JOIN moderation m ON m.rec_id = c.id 
            INNER JOIN users u ON u.uid = c.user_id 
            WHERE c.id = ?i AND m.rec_type = ?i AND m.stream_id = ?', $rec_id, self::MODER_PORTF_CHOISE, $stream_id 
        );
        
        if ( $aChange ) {
            $bRet  = true;
            
            if ( $action == 2 ) {
                // возвращаем старые значения
                if ( $aChange['ucolumn'] == 'text' ) {
                    if ( !$aChange['old_val'] ) $aChange['old_val'] = null;

                    $GLOBALS['DB']->query( "UPDATE portf_choise SET portf_text = ? WHERE user_id = ?i AND prof_id = ?i", 
                        $aChange['old_val'], $from_id, $aChange['prof_id'] 
                    );
                }
                else {
                    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/kwords.php' );

                    $kwords = new kwords();
                    $kwords->delUserKeys( $from_id, $aChange['prof_id'] );

                    if ( $aChange['old_val'] ) {
                        $aKwords = explode( ',', $aChange['old_val'] );
                        $kwords->addUserKeys( $from_id, $aKwords, $aChange['prof_id'] );
                    }
                }
                
            }

            $GLOBALS['DB']->query( 'DELETE FROM portf_choise_change WHERE id = ?i', $rec_id );
            $GLOBALS['DB']->query( 'DELETE FROM moderation WHERE rec_id = ?i AND rec_type = ?i', $rec_id, self::MODER_PORTF_CHOISE );
        }
        
        return $bRet;
    }
    
    /**
     * Работы в портфолио. Утверждение/удаление записи
     * 
     * @param  string $stream_id идентификатор потока
     * @param  int $user_id идентификатор модератора
     * @param  int $from_id идентификатор пользователя
     * @param  int $rec_id идентификатор записи
     * @param  int $rec_type тип записи 
     * @param  int $action действие: 1 - утверждено, 2 - удалено
     * @param  string $is_sent было ли отправлено уведомление
     * @param  string $reason причина удаления
     */
    function resolvePortfolio( $stream_id = '', $user_id = 0, $from_id = 0, $rec_id = 0, $rec_type = 0, $action = 1, $is_sent = '', $reason = '' ) {
        $bRet   = false;
        $sQuery = 'UPDATE moderation SET moder_num = ?i, status = ?i WHERE rec_id = ?i AND rec_type = ?i AND stream_id = ? RETURNING rec_id';
        $sRecId = $GLOBALS['DB']->val( $sQuery, $this->nResolveCnt, $action, $rec_id, self::MODER_PORTFOLIO, $stream_id );
        
        if ( $sRecId ) {
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/portfolio.php' );
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/admin_log.php' );
            
            $portfolio = portfolio::GetPrj( $rec_id );
            $bRet      = true;
            $objUser   = new users();
            $objUser->GetUserByUID( $from_id );
            
            $sObjName  = $portfolio['name'];
			$sObjLink  = '/users/'. $objUser->login .'/viewproj.php?prjid='. $portfolio['id']; 
            
            if ( $action == 1 && $portfolio['is_blocked'] == 't' ) {
                portfolio::UnBlocked( $rec_id );

                // пишем лог админских действий
                admin_log::addLog( admin_log::OBJ_CODE_PROJ, admin_log::ACT_ID_PORTFOLIO_UNBLOCK, 
                    $from_id, $rec_id, $sObjName, $sObjLink, 0, '', 0, '' 
                );
            }
            elseif ( $action == 2 && $portfolio['is_blocked'] != 't' ) {

                $sReason  = 'Содержит запрещенную информацию';
                $sBlockId = portfolio::Blocked( $rec_id, $reason, 0, $_SESSION['uid'], true );
                
                // пишем лог админских действий
                admin_log::addLog( admin_log::OBJ_CODE_PROJ, admin_log::ACT_ID_PORTFOLIO_BLOCK, 
                    $from_id, $rec_id, $sObjName, $sObjLink, 0, '', 0, $sReason, $sBlockId 
                );
            }
            
            $GLOBALS['DB']->update( 'portfolio', array( 'moderator_status' => $user_id ), 'id = ?i', $rec_id );
        }
        
        return $bRet;
    }
    
    /**
     * Предложения фрилансеров "Сделаю". Утверждение/удаление записи
     * 
     * @param  string $stream_id идентификатор потока
     * @param  int $user_id идентификатор модератора
     * @param  int $from_id идентификатор пользователя
     * @param  int $rec_id идентификатор записи
     * @param  int $rec_type тип записи 
     * @param  int $action действие: 1 - утверждено, 2 - удалено
     * @param  string $is_sent было ли отправлено уведомление
     * @param  string $reason причина удаления
     */
    function resolveSdelau( $stream_id = '', $user_id = 0, $from_id = 0, $rec_id = 0, $rec_type = 0, $action = 1, $is_sent = '', $reason = '' ) {
        $bRet   = false;
        $sQuery = 'UPDATE moderation SET moder_num = ?i, status = ?i WHERE rec_id = ?i AND rec_type = ?i AND stream_id = ? RETURNING rec_id';
        $sRecId = $GLOBALS['DB']->val( $sQuery, $this->nResolveCnt, $action, $rec_id, self::MODER_SDELAU, $stream_id );
        
        if ( $sRecId ) {
            $bRet  = true;
            
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/freelancer_offers.php' );
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/admin_log.php' );

            $frl_offers = new freelancer_offers();
            $offer      = $frl_offers->getOfferById( $rec_id );
            $sObjName   = $offer['title'];
            $sObjLink   = ''; // нет ссылки на конкретное предложение
            $update     = array();

            if ( $action == 1 && $offer['admin'] ) { //утверждаем
                $update = array( 'is_blocked' => false, 'reason'=> '', 'reason_id' => 0, 'admin' => 0 );
                admin_log::addLog( admin_log::OBJ_CODE_OFFER, 14, $from_id, $rec_id, $sObjName, $sObjLink, 0, '', 0, '' );
            }
            elseif ( $action == 2 && $topic['is_blocked_s'] != 't' ) {
                $sReason  = 'Содержит запрещенную информацию';
                $update   = array( 'is_blocked' => true, 'reason' => $reason, 'reason_id' => 0, 'admin' => $user_id, 'deleted_reason' => $reason );
                admin_log::addLog( admin_log::OBJ_CODE_OFFER, 13, $from_id, $rec_id, $sObjName, $sObjLink, 0, '', 0, $sReason, $rec_id );
            }
            
            $update['moderator_status'] = $user_id;
            
            $GLOBALS['DB']->update( 'freelance_offers', $update, 'id = ?i', $rec_id );
        }
        
        return $bRet;
    }
    
    /**
     * Освобождение сущностей из потока
     * 
     * @param  int $content_id идентификатор сущности из admin_contents
     * @param  string $stream_id опционально. идентификатор потока, иначе будут освобождены все сущности
     */
    private function _releaseContent( $content_id = 0, $stream_id = '' ) {
        $sStream = !empty($stream_id) ? $GLOBALS['DB']->parse(' AND stream_id = ?', $stream_id) : '';
        $sStatus = ' status = 0 ';
        $bCommon = true;
        
        switch ($content_id) {
            case self::MODER_MSSAGES:
                // Личные сообщения
                $bCommon = false;
                $DB      = new DB( 'plproxy' );
                $sQuery  = 'SELECT messages_moder_del(?);';

                $DB->query( $sQuery, $stream_id );
                break;
            case self::MODER_PROFILE:
            case self::MODER_PORTF_CHOISE:
            case self::MODER_PORTFOLIO:
            case self::MODER_PORTF_UNITED:
            case self::MODER_USER_UNITED:
                $sStatus = ' (status = 0 OR status = -1) ';
                break;
            default:
                break;
        }
        
        if ( $bCommon && in_array($content_id, array_keys(self::$table)) ) {
            $sQuery = '';
            
            foreach ( self::$table[$content_id]['moder'] as $rec_type ) {
                $sQuery .= 'UPDATE moderation SET stream_id = NULL WHERE '. $sStatus .' AND rec_type = ' . $rec_type . $sStream . ';';
                $sQuery .= 'DELETE FROM moderation WHERE status > 0 AND rec_type = ' . $rec_type . $sStream . ';';
            }
            
            $GLOBALS['DB']->query( $sQuery );
        }
    }
    
    /**
     * Захватить сущности для модерирования
     * 
     * @param  int $content_id идентификатор сущности из admin_contents
     * @param  string $stream_id идентификатор потока
     * @param  int $stream_num счетчик потока
     * @param  int $limit количество записей для захвата
     */
    function chooseContent( $content_id = 0, $stream_id = '', $stream_num = 0, $limit = 10 ) {
        if ( !empty($content_id) && !empty($stream_id) ) {
            $bCommon = true;
            $sAnd    = '';
            $sStatus = ' status = 0 ';
            $aUnited = array();
            $sOrder  = '';
            
            switch ($content_id) {
                case self::MODER_MSSAGES:
                    // Личные сообщения
                    $bCommon = false;
                    $DB      = new DB( 'plproxy' );
                    $sQuery  = 'SELECT messages_moder_choose(?, ?i, ?i, ?i);';
                    
                    $DB->query( $sQuery, $stream_id, $this->streams_count[$content_id], $stream_num, $limit );
                    break;
                case self::MODER_PRJ_DIALOG:
                    // Комментарии к предложениям по проектам
                    $sAnd = ' AND a.root = false ';
                    break;
                case self::MODER_PROFILE:
                case self::MODER_PORTF_CHOISE:
                case self::MODER_PORTFOLIO:
                    // то что после окончания ПРО нужно отправить на модерирование
                    $sStatus = ' (status = 0 OR status = -1) ';
                    break;
                case self::MODER_PRJ_COM:
                case self::MODER_COMMENTS:
                case self::MODER_PORTF_UNITED:
                case self::MODER_BLOGS_UNITED:
                case self::MODER_USER_UNITED:
                    // Сборные
                    $bCommon = false;
                    break;
                default:
                    break;
            }
            
            if ( in_array($content_id, array_keys(self::$table)) ) {
                if ( $bCommon ) {
                    $sQuery = 'UPDATE moderation AS m 
                        SET stream_id = ? 
                        FROM (
                            SELECT rec_id FROM moderation 
                            WHERE  rec_type = '. $content_id .' AND stream_id IS NULL 
                                AND rec_id % ?i = ?i AND '. $sStatus .' '. $sAnd .' 
                            ORDER BY sort_order ASC, stop_words_cnt DESC, rec_id ASC LIMIT ?i
                        ) AS i 
                        WHERE m.rec_id = i.rec_id AND m.rec_type = '. $content_id;

                    $GLOBALS['DB']->query( $sQuery, $stream_id, $this->streams_count[$content_id], $stream_num, $limit );
                }
                elseif ( $content_id != self::MODER_MSSAGES ) {
                    $bStatus = $content_id == self::MODER_PORTF_UNITED || $content_id == self::MODER_USER_UNITED;
                    $sQuery  = 'UPDATE moderation AS m 
                        SET stream_id = ? 
                        FROM (
                            SELECT i.rec_id, i.content_id, i.stop_words_cnt, i.sort_order 
                            FROM (
                                '. $this->_getChooseContentUnitedSql( self::$table[$content_id]['moder'], $this->streams_count[$content_id], $stream_num, $limit, $bStatus ) .' 
                            ) AS i 
                            ORDER BY i.sort_order ASC, i.stop_words_cnt DESC, i.rec_id ASC LIMIT ?i
                        ) AS o 
                        WHERE m.rec_id = o.rec_id AND m.rec_type = o.content_id';

                    $GLOBALS['DB']->query( $sQuery, $stream_id, $limit );
                }
            }
        }
    }
    
    /**
     * Возвращает внутренний запрос для захвата сущностей в сборных потоках
     * 
     * @param  array $aContentId массив идентификаторов сущностей из admin_contents
     * @param  int $nStreamsCnt количество потоков 
     * @param  int $nStreamNum номер потока
     * @param  int $nLimit количество сущностей для захвата
     * @param  bool $bStatus установить в true, если по окончании ПРО сущности нужно направить в потоки
     * @return string
     */
    function _getChooseContentUnitedSql( $aContentId = array(), $nStreamsCnt = 0, $nStreamNum = 0, $nLimit = 0, $bStatus = false ) {
        $sStatus = $bStatus ? ' (status = 0 OR status = -1) ' : 'status = 0';
        $aSql    = array();
        
        foreach ( $aContentId as $nContentId ) {
            $aSql[] = $GLOBALS['DB']->parse( '(SELECT rec_id, stop_words_cnt, sort_order, ?i AS content_id FROM moderation 
                WHERE rec_type = ?i AND stream_id IS NULL AND rec_id % ?i = ?i AND '. $sStatus .' 
                ORDER BY sort_order ASC, stop_words_cnt DESC, rec_id ASC LIMIT ?i)', 
                $nContentId, $nContentId, $nStreamsCnt, $nStreamNum, $nLimit 
            );
        }
        
        return implode( "\nUNION ALL\n", $aSql );
    }
    
    /**
     * Проверяет существует ли запись для модерирования и захвачена ли она
     * 
     * @param  int $content_id идентификатор сущности из admin_contents
     * @param  string $stream_id идентификатор потока
     * @param  int $rec_id идентификатор записи
     * @return bool true - существует, false - нет
     */
    function checkContent( $content_id = 0, $stream_id = '', $rec_id = 0 ) {
        $bRet = false;
        
        switch ($content_id) {
            case self::MODER_MSSAGES:
                $bCommon = false;
                $bRet    = true;
                break;
            default:
                $bCommon = true;
                break;
        }
        
        if ( $bCommon && in_array($content_id, array_keys(self::$table)) ) {
            $nCnt = $GLOBALS['DB']->val( 
                'SELECT COUNT(1) FROM moderation WHERE rec_id = ?i AND rec_type = ?i AND stream_id = ?', 
                $rec_id, $content_id, $stream_id 
            );
            $bRet = !empty( $nCnt );
        }
        
        return $bRet;
    }
    
    /**
     * Возвращает сущности для модерирования, утвержденные либо удаленные в потоке
     * 
     * @param  int $content_id идентификатор сущности из admin_contents
     * @param  string $stream_id идентификатор потока
     * @param  int $status статус сущностей: 0 - для модерирования, 1 - утвержденные, 2 - утвержденные
     * @param  int $last_id для статус = 1, 2 - последний полученный ID
     * @param  int $limit количество записей
     */
    function getContent( $content_id = 0, $stream_id = '', $status = 0, $last_id = 0, $limit = 10 ) {
        $aReturn = array();

        if ( !empty($content_id) && !empty($stream_id) ) {
            $sOrder  = ( $status && $last_id ) ? ' b.moder_num DESC ' : ' b.sort_order ASC, b.stop_words_cnt DESC, b.rec_id ASC ';
            $sOrderU = ( $status && $last_id ) ? ' i.moder_num DESC ' : ' i.sort_order ASC, i.stop_words_cnt DESC, i.id ASC ';
            $sAnd    = ( $status && $last_id ) ? $GLOBALS['DB']->parse(' AND b.moder_num < ?i ', $last_id) : '';
            
            switch ($content_id) {
                case self::MODER_MSSAGES:
                    // Личные сообщения
                    $sOrder  = ( $status && $last_id ) ? 'DESC' : 'ASC';
                    $sAnd    = ( $status && $last_id ) ? $GLOBALS['DB']->parse(' AND b.rec_id < ?i ', $last_id) : '';
                    $DB      = new DB( 'plproxy' );
                    $sQuery  = 'SELECT m.*, '. self::MODER_MSSAGES .' AS content_id 
                        FROM messages_moder_get(?i, ?, ?i, ?i) m ORDER BY m.id ' . $sOrder;
                    
                    $aReturn = $DB->rows( $sQuery, $status, $stream_id, $last_id, $limit );
                    
                    if ( $aReturn ) {
                        $this->_getContentMessagesEx( $aReturn );
                    }
                    break;
                case self::MODER_BLOGS:
                    // Блоги: посты и комментарии
                    $sQuery = 'SELECT b.rec_type AS content_id, b.moder_num, b.stop_words_cnt, b.sort_order, a.id, a.fromuser_id AS user_id, a.msgtext, a.thread_id AS src_id, a.is_sent, a.title AS src_name, a.yt_link AS youtube_link, a.post_time, a.modified AS mod_time, a.reply_to, t.id_gr, c.question as poll_question 
                            u.login, u.uname, u.usurname, u.role, u.is_pro, u.is_pro_test, u.is_team, u.is_chuck, u.warn, u.is_banned, u.ban_where, 
                        FROM moderation b 
                        INNER JOIN blogs_msgs a ON a.id = b.rec_id 
                        INNER JOIN blogs_themes t ON a.thread_id = t.thread_id 
                        INNER JOIN users u ON u.uid = a.fromuser_id 
                        LEFT JOIN blogs_poll c ON c.thread_id = a.thread_id 
                        WHERE b.rec_type = '. self::MODER_BLOGS .' AND b.status = ?i AND b.stream_id = ? '. $sAnd .' 
                        ORDER BY '. $sOrder .' LIMIT ?i';
                    
                    $aReturn = $GLOBALS['DB']->rows( $sQuery, $status, $stream_id, $limit );
                    
                    if ( is_array($aReturn) && count($aReturn) ) {
                        $this->_getBlogsAttachPoll( $aReturn );
                    }
                    break;
                case self::MODER_COMMUNITY:
                    // Сообщества: посты и комментарии
                    $sQuery = 'SELECT a.*, a.cnt_files AS file_exists, u.login, u.uname, u.usurname, u.role, u.is_pro, u.is_pro_test, u.is_team, u.is_chuck, u.warn, u.is_banned, u.ban_where, 
                            b.rec_type AS content_id, b.moder_num, cp.question AS question, t.id AS top_id, a.created_time AS post_time, a.modified_time AS mod_time 
                        FROM moderation b 
                        INNER JOIN commune_messages a ON a.id = b.rec_id 
                        INNER JOIN users u ON u.uid = a.user_id
                        LEFT JOIN commune_poll cp ON cp.theme_id = a.theme_id 
                        LEFT JOIN commune_messages t ON t.theme_id = a.theme_id AND t.parent_id IS NULL 
                        WHERE b.rec_type = '. self::MODER_COMMUNITY .' AND b.status = ?i AND b.stream_id = ? '. $sAnd .' 
                        ORDER BY '. $sOrder .' LIMIT ?i';
                    
                    $aReturn = $GLOBALS['DB']->rows( $sQuery, $status, $stream_id, $limit );
                    
                    if ( is_array($aReturn) && count($aReturn) ) {
                        $this->_getCommunityAttachPoll( $aReturn );
                    }
                    break;
                case self::MODER_PROJECTS:
                    // Проекты
                    $sQuery = 'SELECT p.*, city.city_name, country.country_name, (COALESCE(p.payed,0)<>0) as ico_payed, 
                            u.login, u.uname, u.usurname, u.email, u.photo, u.photosm, u.is_pro, u.warn, u.role, 
                            u.is_banned, u.ban_where, u.is_team, u.reg_date, pb.project_id::boolean AS is_blocked, 
                            pb.admin as blocked_admin, pb.reason as blocked_reason, pb.blocked_time, link, NULL AS category, 
                            offers_count, NULL as category_name, p.create_date AS post_time, p.edit_date AS mod_time, 
                            b.rec_type AS content_id, b.moder_num, admins.login as admin_login, admins.uname as admin_name, admins.usurname as admin_uname 
                        FROM moderation b 
                        INNER JOIN projects p ON p.id = b.rec_id 
                        LEFT JOIN projects_blocked AS pb ON pb.project_id = p.id 
                        LEFT JOIN city ON city.id = p.city 
                        LEFT JOIN country ON country.id = p.country 
                        LEFT JOIN users AS admins ON admins.uid = pb.admin 
                        LEFT JOIN employer AS u ON u.uid = p.user_id 
                        WHERE b.rec_type = '. self::MODER_PROJECTS .' AND b.status = ?i AND b.stream_id = ? '. $sAnd .' 
                        ORDER BY '. $sOrder .' LIMIT ?i';
                    
                    $aReturn = $GLOBALS['DB']->rows( $sQuery, $status, $stream_id, $limit );
                    
                    if ( is_array($aReturn) && count($aReturn) ) {
                        $this->_getProjectsAttachSpecs( $aReturn );
                    }
                    break;
                case self::MODER_PRJ_OFFERS:
                    // Предложения в проектах
                    $sQuery = 'SELECT po.*, po.post_date AS post_time, po.project_id AS src_id, po.descr AS post_text, b.rec_type AS content_id, b.moder_num, p.kind, p.name AS src_name, d.post_text AS dialog_root, 
                            u.login, u.uname, u.usurname, u.is_chuck, u.warn, u.is_banned, u.ban_where, u.role, u.is_pro, u.is_pro_test, u.is_team, po.modified AS mod_time 
                        FROM moderation b 
                        INNER JOIN projects_offers po ON po.id = b.rec_id 
                        INNER JOIN projects p ON p.id = po.project_id 
                        LEFT JOIN projects_offers_dialogue d ON d.po_id = po.id AND d.root = true 
                        LEFT JOIN freelancer AS u ON u.uid = po.user_id 
                        WHERE b.rec_type = '. self::MODER_PRJ_OFFERS .' AND b.status = ?i AND b.stream_id = ? '. $sAnd .' 
                        ORDER BY '. $sOrder .' LIMIT ?i';
                    
                    $aReturn = $GLOBALS['DB']->rows( $sQuery, $status, $stream_id, $limit );
                    
                    if ( is_array($aReturn) && count($aReturn) ) {
                        // аттачи
                        $this->_getPrjOffersAttach( $aReturn );
                    }
                    break;
                case self::MODER_ART_COM:
                    // Комментарии в статьях
                    $sQuery = 'SELECT c.id, c.from_id AS user_id, c.article_id AS src_id, c.msgtext, c.youtube_link, c.is_sent, c.created_time AS post_time, c.modified_time AS mod_time, 
                            b.rec_type AS content_id, b.moder_num, a.title AS src_name, u.login, u.uname, u.usurname, u.role, u.is_pro, u.is_pro_test, u.is_team, u.is_chuck, u.warn, u.is_banned, u.ban_where 
                        FROM moderation b 
                        INNER JOIN articles_comments c ON c.id = b.rec_id 
                        INNER JOIN articles_new a ON a.id = c.article_id 
                        LEFT JOIN users AS u ON u.uid = c.from_id 
                        WHERE b.rec_type = '. self::MODER_ART_COM .' AND b.status = ?i AND b.stream_id = ? '. $sAnd .' 
                        ORDER BY '. $sOrder .' LIMIT ?i';
                    
                    $aReturn = $GLOBALS['DB']->rows( $sQuery, $status, $stream_id, $limit );
                    
                    if ( $aReturn ) {
                        // аттачи
                        $this->_getCommentsAttach( $aReturn, self::MODER_ART_COM, 'articles_comments_files', 'file', 'comment_id', 'file_id' );
                    }
                    break;
                case self::MODER_PROFILE:
                    // Изменения в профилях
                    $sQuery = 'SELECT c.*, b.rec_type AS content_id, b.moder_num, u.login, u.uname, u.usurname, u.role, u.is_pro, u.is_pro_test, u.is_team, u.is_chuck, u.warn, u.is_banned, u.ban_where 
                        FROM moderation b 
                        INNER JOIN users_change c ON c.id = b.rec_id 
                        INNER JOIN users u ON u.uid = c.user_id 
                        WHERE b.rec_type = '. self::MODER_PROFILE .' AND b.status = ?i AND b.stream_id = ? '. $sAnd .' 
                        ORDER BY '. $sOrder .' LIMIT ?i';
                    
                    $aReturn = $GLOBALS['DB']->rows( $sQuery, $status, $stream_id, $limit );
                    break;
                case self::MODER_PRJ_DIALOG:
                    // Комментарии к предложениям по проектам
                    $sQuery = 'SELECT d.id, d.po_id AS offer_id, d.user_id, d.post_text, d.is_sent, d.post_date AS post_time, d.modified AS mod_time, po.project_id AS src_id, p.name AS src_name, 
                            p.kind, b.rec_type AS content_id, b.moder_num, u.login, u.uname, u.usurname, u.role, u.is_pro, u.is_pro_test, u.is_team, u.is_chuck, u.warn, u.is_banned, u.ban_where 
                        FROM moderation b 
                        INNER JOIN projects_offers_dialogue d ON d.id = b.rec_id 
                        INNER JOIN projects_offers po ON po.id = d.po_id 
                        INNER JOIN projects p ON p.id = po.project_id 
                        LEFT JOIN users AS u ON u.uid = d.user_id 
                        WHERE b.rec_type = '. self::MODER_PRJ_DIALOG .' AND b.status = ?i AND b.stream_id = ? '. $sAnd .' 
                        ORDER BY '. $sOrder .' LIMIT ?i';
                    
                    $aReturn = $GLOBALS['DB']->rows( $sQuery, $status, $stream_id, $limit );
                    break;
                case self::MODER_CONTEST_COM:
                    // Комментарии к предложениям конкурсов
                    $sQuery = 'SELECT m.id, m.offer_id, m.user_id, m.msg AS post_text, m.is_sent, m.post_date AS post_time, m.modified AS mod_time, po.project_id AS src_id, p.name AS src_name, 
                            b.rec_type AS content_id, b.moder_num, u.login, u.uname, u.usurname, u.role, u.is_pro, u.is_pro_test, u.is_team, u.is_chuck, u.warn, u.is_banned, u.ban_where 
                        FROM moderation b 
                        INNER JOIN projects_contest_msgs m ON m.id = b.rec_id 
                        INNER JOIN projects_contest_offers po ON po.id = m.offer_id 
                        INNER JOIN projects p ON p.id = po.project_id 
                        LEFT JOIN users AS u ON u.uid = m.user_id 
                        WHERE b.rec_type = '. self::MODER_CONTEST_COM .' AND b.status = ?i AND b.stream_id = ? '. $sAnd .' 
                        ORDER BY '. $sOrder .' LIMIT ?i';
                    
                    $aReturn = $GLOBALS['DB']->rows( $sQuery, $status, $stream_id, $limit );
                    break;
                case self::MODER_PORTF_CHOISE:
                    // Уточнения к разделам в портфолио
                    $sQuery = 'SELECT c.*, b.rec_type AS content_id, b.moder_num, pc.portf_text AS new_val, 
                            u.login, u.uname, u.usurname, u.is_chuck, u.is_pro, u.is_pro_test, u.is_team, u.warn, u.is_banned, u.ban_where 
                        FROM moderation b 
                        INNER JOIN portf_choise_change c ON c.id = b.rec_id 
                        INNER JOIN portf_choise pc ON pc.user_id = c.user_id AND pc.prof_id = c.prof_id 
                        INNER JOIN freelancer u ON u.uid = c.user_id 
                        WHERE b.rec_type = '. self::MODER_PORTF_CHOISE .' AND b.status = ?i AND b.stream_id = ? '. $sAnd .' 
                        ORDER BY '. $sOrder .' LIMIT ?i';
                    
                    $aReturn = $GLOBALS['DB']->rows( $sQuery, $status, $stream_id, $limit );    
                    break;
                
                //--------------------------------------------------------------
                
                //Реквизиты в разделе финансы
                case self::MODER_SBR_REQV:
                    
                    $sQuery = "
                        SELECT
                            s.user_id AS id,
                            s.*, 
                            b.rec_type AS content_id, 
                            b.moder_num,
                            u.login, 
                            u.uname, 
                            u.usurname, 
                            u.is_pro, 
                            u.is_pro_test, 
                            u.is_team, 
                            u.is_chuck, 
                            u.warn, 
                            u.is_banned, 
                            u.ban_where,
                            u.role                            
                        FROM moderation AS b
                        INNER JOIN sbr_reqv AS s ON s.user_id = b.rec_id 
                        INNER JOIN users AS u ON u.uid = s.user_id
                        LEFT JOIN sbr_reqv_blocked AS sb ON sb.src_id = s.user_id 
                        WHERE 
                            b.rec_type = " . self::MODER_SBR_REQV . " AND 
                            b.status = ?i AND 
                            b.stream_id = ? " . $sAnd . " 
                        ORDER BY " . $sOrder . " LIMIT ?i";
                    
                    $aReturn = $GLOBALS['DB']->rows( $sQuery, $status, $stream_id, $limit );
                    break;
                
                //--------------------------------------------------------------
                
                case self::MODER_TSERVICES:
                    //типовые услуги
                    $sQuery = "
                        SELECT 
                            s.*,
                            COALESCE(sb.src_id::boolean, FALSE) AS is_blocked,
                            b.rec_type AS content_id, 
                            b.moder_num,
                            u.login, 
                            u.uname, 
                            u.usurname, 
                            u.is_pro, 
                            u.is_pro_test, 
                            u.is_team, 
                            u.is_chuck, 
                            u.warn, 
                            u.is_banned, 
                            u.ban_where,
                            u.role,
                            c1.title AS category_spec_title,
                            c2.title AS category_group_title
                        FROM moderation AS b 
                        INNER JOIN tservices AS s ON s.id = b.rec_id 
                        INNER JOIN freelancer AS u ON u.uid = s.user_id 
                        LEFT JOIN tservices_blocked AS sb ON sb.src_id = s.id 
                        LEFT JOIN tservices_categories AS c1 ON c1.id = s.category_id 
                        LEFT JOIN tservices_categories AS c2 ON c2.id = c1.parent_id 
                        WHERE 
                            b.rec_type = " . self::MODER_TSERVICES . " AND 
                            b.status = ?i AND 
                            b.stream_id = ? " . $sAnd . " 
                        ORDER BY " . $sOrder . " LIMIT ?i";
                    
                    $aReturn = $GLOBALS['DB']->rows( $sQuery, $status, $stream_id, $limit );     
                    break;
                
                
                case self::MODER_PORTFOLIO:
                    // Работы в портфолио
                    $sQuery = 'SELECT p.*, p.post_date AS post_time, p.edit_date AS mod_time, b.rec_type AS content_id, b.moder_num, u.login, u.uname, u.usurname, u.is_pro, u.is_pro_test, u.is_team, u.is_chuck, u.warn, u.is_banned, u.ban_where 
                        FROM moderation b 
                        INNER JOIN portfolio p ON p.id = b.rec_id 
                        INNER JOIN freelancer u ON u.uid = p.user_id 
                        WHERE b.rec_type = '. self::MODER_PORTFOLIO .' AND b.status = ?i AND b.stream_id = ? '. $sAnd .' 
                        ORDER BY '. $sOrder .' LIMIT ?i';
                    
                    $aReturn = $GLOBALS['DB']->rows( $sQuery, $status, $stream_id, $limit );    
                    break;
                case self::MODER_SDELAU:
                    // Предложения фрилансеров "Сделаю"
                    $sQuery = 'SELECT o.*, o.post_date AS post_time, o.modify_date AS mod_time, o.descr AS post_text, u.login, u.uname, u.is_pro, u.is_pro_test, u.is_team, u.usurname, u.is_chuck, u.warn, u.is_banned, u.ban_where, 
                            b.rec_type AS content_id, b.moder_num, p.name as profname, p.is_text, pg.name as src_name, p.link 
                        FROM moderation b 
                        INNER JOIN freelance_offers o ON o.id = b.rec_id 
                        INNER JOIN freelancer u ON u.uid = o.user_id 
                        LEFT JOIN prof_group pg ON pg.id = o.category_id 
                        LEFT JOIN professions p ON p.id = o.subcategory_id 
                        WHERE b.rec_type = '. self::MODER_SDELAU .' AND b.status = ?i AND b.stream_id = ? '. $sAnd .'  
                        ORDER BY '. $sOrder .' LIMIT ?i';
                    
                    $aReturn = $GLOBALS['DB']->rows( $sQuery, $status, $stream_id, $limit );
                    break;
                case self::MODER_PRJ_COM:
                    // Сборная: Предложения в проектах/конкурсах, комментарии к предложениям в проектах/конкурсах, Предложения фрилансеров Сделаю
                    $sQuery = 'SELECT i.content_id, i.moder_num, i.stop_words_cnt, i.sort_order, i.id, i.user_id, i.src_id, i.src_name, i.post_text, 
                            i.kind, i.pict1, i.pict2, i.pict3, i.prev_pict1, i.prev_pict2, i.prev_pict3, i.time_from, i.time_to, i.time_type, i.cost_from, i.cost_to, i.cost_type, i.dialog_root, 
                            i.title, i.profname, i.link, i.is_sent, i.offer_id, i.moduser_id, i.modified_reason, i.post_time, i.mod_time, 
                            u.login, u.uname, u.usurname, u.role, u.is_pro, u.is_pro_test, u.is_team, u.is_chuck, u.warn, u.is_banned, u.ban_where 
                        FROM (
                            (SELECT b.rec_type AS content_id, b.moder_num, b.stop_words_cnt, b.sort_order, po.id, po.user_id, po.project_id AS src_id, p.name AS src_name, po.descr AS post_text, 
                                p.kind, po.pict1, po.pict2, po.pict3, po.prev_pict1, po.prev_pict2, po.prev_pict3, po.time_from, po.time_to, po.time_type, po.cost_from, po.cost_to, po.cost_type, d.post_text AS dialog_root, 
                                NULL AS title, NULL AS profname, NULL AS link, po.is_sent, NULL AS offer_id, po.moduser_id, po.modified_reason, po.post_date AS post_time, po.modified AS mod_time 
                            FROM moderation b 
                            INNER JOIN projects_offers po ON po.id = b.rec_id 
                            INNER JOIN projects p ON p.id = po.project_id 
                            LEFT JOIN projects_offers_dialogue d ON d.po_id = po.id AND d.root = true 
                            WHERE b.rec_type = '. self::MODER_PRJ_OFFERS .' AND b.status = ?i AND b.stream_id = ? '. $sAnd .' 
                            ORDER BY '. $sOrder .')
                            
                            UNION ALL
                            
                            (SELECT b.rec_type AS content_id, b.moder_num, b.stop_words_cnt, b.sort_order, d.id, d.user_id, po.project_id AS src_id, p.name AS src_name, d.post_text, 
                                p.kind, NULL AS pict1, NULL AS pict2, NULL AS pict3, NULL AS prev_pict1, NULL AS prev_pict2, NULL AS prev_pict3, NULL AS time_from, NULL AS time_to, NULL AS time_type, NULL AS cost_from, NULL AS cost_to, NULL AS cost_type, NULL AS dialog_root, 
                                NULL AS title, NULL AS profname, NULL AS link, d.is_sent, d.po_id AS offer_id, d.moduser_id, d.modified_reason, d.post_date AS post_time, d.modified AS mod_time 
                            FROM moderation b 
                            INNER JOIN projects_offers_dialogue d ON d.id = b.rec_id 
                            INNER JOIN projects_offers po ON po.id = d.po_id 
                            INNER JOIN projects p ON p.id = po.project_id 
                            WHERE b.rec_type = '. self::MODER_PRJ_DIALOG .' AND d.root = false AND b.status = ?i AND b.stream_id = ? '. $sAnd .' 
                            ORDER BY '. $sOrder .')
                            
                            UNION ALL
                            
                            (SELECT b.rec_type AS content_id, b.moder_num, b.stop_words_cnt, b.sort_order, m.id, m.user_id, po.project_id AS src_id, p.name AS src_name, m.msg AS post_text, 
                                p.kind, NULL AS pict1, NULL AS pict2, NULL AS pict3, NULL AS prev_pict1, NULL AS prev_pict2, NULL AS prev_pict3, NULL AS time_from, NULL AS time_to, NULL AS time_type, NULL AS cost_from, NULL AS cost_to, NULL AS cost_type, NULL AS dialog_root, 
                                NULL AS title, NULL AS profname, NULL AS link, m.is_sent, m.offer_id, m.moduser_id, NULL AS modified_reason, m.post_date AS post_time, m.modified AS mod_time 
                            FROM moderation b 
                            INNER JOIN projects_contest_msgs m ON m.id = b.rec_id 
                            INNER JOIN projects_contest_offers po ON po.id = m.offer_id 
                            INNER JOIN projects p ON p.id = po.project_id 
                            WHERE b.rec_type = '. self::MODER_CONTEST_COM .' AND b.status = ?i AND b.stream_id = ? '. $sAnd .' 
                            ORDER BY '. $sOrder .')
                            /*
                            UNION ALL
                            
                            (SELECT b.rec_type AS content_id, b.moder_num, b.stop_words_cnt, b.sort_order, o.id, o.user_id, NULL AS src_id, pg.name AS src_name, o.descr AS post_text, 
                                NULL AS kind, NULL AS pict1, NULL AS pict2, NULL AS pict3, NULL AS prev_pict1, NULL AS prev_pict2, NULL AS prev_pict3, NULL AS time_from, NULL AS time_to, NULL AS time_type, NULL AS cost_from, NULL AS cost_to, NULL AS cost_type, NULL AS dialog_root, 
                                o.title, p.name AS profname, p.link, NULL AS is_sent, NULL AS offer_id, NULL AS moduser_id, NULL AS modified_reason, o.post_date AS post_time, o.modify_date AS mod_time 
                            FROM moderation b 
                            INNER JOIN freelance_offers o ON o.id = b.rec_id 
                            LEFT JOIN prof_group pg ON pg.id = o.category_id 
                            LEFT JOIN professions p ON p.id = o.subcategory_id 
                            WHERE b.rec_type = '. self::MODER_SDELAU .' AND b.status = ?i AND b.stream_id = ? '. $sAnd .'  
                            ORDER BY '. $sOrder .')*/
                        ) AS i 
                        LEFT JOIN users AS u ON u.uid = i.user_id 
                        ORDER BY '. $sOrderU .' LIMIT ?i
                        ';
                    
                    $aReturn = $GLOBALS['DB']->rows( $sQuery, 
                        $status, $stream_id, 
                        $status, $stream_id, 
                        $status, $stream_id, 
                        $status, $stream_id, 
                        $limit 
                    );

                    if ( is_array($aReturn) && count($aReturn) ) {
                        // аттачи
                        $this->_getPrjOffersAttach( $aReturn );
                    }
                    break;
                case self::MODER_COMMENTS:
                    // Сборная: Комментарии: магазин, статьи
                    $sQuery = 'SELECT i.content_id, i.moder_num, i.stop_words_cnt, i.sort_order, i.id, i.user_id, i.msgtext, i.src_id, i.is_sent, i.src_name, i.youtube_link, i.post_time, i.mod_time, 
                            u.login, u.uname, u.usurname, u.role, u.is_pro, u.is_pro_test, u.is_team, u.is_chuck, u.warn, u.is_banned, u.ban_where 
                        FROM (
                            (SELECT b.rec_type AS content_id, b.moder_num, b.stop_words_cnt, b.sort_order, c.id, c.from_id AS user_id, c.msgtext, c.article_id AS src_id, c.is_sent, a.title AS src_name, c.youtube_link, c.created_time AS post_time, c.modified_time AS mod_time 
                            FROM moderation b 
                            INNER JOIN articles_comments c ON c.id = b.rec_id 
                            INNER JOIN articles_new a ON a.id = c.article_id 
                            WHERE b.rec_type = '. self::MODER_ART_COM .' AND b.status = ?i AND b.stream_id = ? '. $sAnd .' 
                            ORDER BY '. $sOrder .')
                        ) AS i 
                        LEFT JOIN users AS u ON u.uid = i.user_id 
                        ORDER BY '. $sOrderU .' LIMIT ?i';
                    
                    $aReturn = $GLOBALS['DB']->rows( $sQuery, 
                        $status, $stream_id, 
                        $status, $stream_id, 
                        $status, $stream_id, 
                        $limit 
                    );
                    
                    if ( $aReturn ) {
                        // аттачи
                        $this->_getCommentsAttach( $aReturn, self::MODER_ART_COM, 'articles_comments_files', 'file', 'comment_id', 'file_id' );
                    }
                    break;
                case self::MODER_PORTF_UNITED:
                    // Сборная: Работы в портфолио, Уточнения к разделам в портфолио
                    $sQuery = 'SELECT i.content_id, i.moder_num, i.stop_words_cnt, i.sort_order, i.id, i.user_id, i.ucolumn, i.new_val, 
                            i.is_video, i.video_link, i.pict, i.prev_pict, i.cost, i.cost_type, i.time_value, i.time_type, i.link, i.name, i.descr, i.prof_id, 
                            i.moduser_id, i.modified_reason, i.moderator_status, i.post_time, i.mod_time, 
                            u.login, u.uname, u.usurname, u.is_pro, u.is_pro_test, u.is_team, u.is_chuck, u.warn, u.is_banned, u.ban_where 
                        FROM ( 
                            (SELECT b.rec_type AS content_id, b.moder_num, b.stop_words_cnt, b.sort_order, c.id, c.user_id, c.ucolumn, pc.portf_text AS new_val, 
                                NULL AS is_video, NULL AS video_link, NULL AS pict, NULL AS prev_pict, NULL AS cost, NULL AS cost_type, NULL AS time_value, NULL AS time_type, NULL AS link, NULL AS name, NULL AS descr, pc.prof_id, 
                                pc.moduser_id, pc.modified_reason, moderator_status, c.post_time, c.post_time AS mod_time 
                            FROM moderation b 
                            INNER JOIN portf_choise_change c ON c.id = b.rec_id 
                            INNER JOIN portf_choise pc ON pc.user_id = c.user_id AND pc.prof_id = c.prof_id 
                            WHERE b.rec_type = '. self::MODER_PORTF_CHOISE .' AND b.status = ?i AND b.stream_id = ? '. $sAnd .' 
                            ORDER BY '. $sOrder .')
                            
                            UNION ALL
                            
                            (SELECT b.rec_type AS content_id, b.moder_num, b.stop_words_cnt, b.sort_order, p.id, p.user_id, NULL AS ucolumn, NULL AS new_val, 
                                p.is_video, p.video_link, p.pict, p.prev_pict, p.cost, p.cost_type, p.time_value, p.time_type, p.link, p.name, p.descr, NULL AS prof_id, 
                                p.edit_id, p.modified_reason, moderator_status, p.post_date AS post_time, p.edit_date AS mod_time 
                            FROM moderation b 
                            INNER JOIN portfolio p ON p.id = b.rec_id 
                            WHERE b.rec_type = '. self::MODER_PORTFOLIO .' AND b.status = ?i AND b.stream_id = ? '. $sAnd .' 
                            ORDER BY '. $sOrder .')
                        ) AS i 
                        LEFT JOIN freelancer AS u ON u.uid = i.user_id 
                        ORDER BY '. $sOrderU .' LIMIT ?i';
                    
                    $aReturn = $GLOBALS['DB']->rows( $sQuery, 
                        $status, $stream_id, 
                        $status, $stream_id, 
                        $limit 
                    );
                    break;
                case self::MODER_BLOGS_UNITED:
                    // Сборная: Блоги: посты и комментарии, Комментарии в Комментарии в статьях
                    $sQuery = 'SELECT i.content_id, i.moder_num, i.stop_words_cnt, i.sort_order, i.id, i.user_id, i.msgtext, i.src_id, i.is_sent, i.src_name, i.youtube_link, i.post_time, i.mod_time, i.reply_to, i.id_gr, i.poll_question, 
                            u.login, u.uname, u.usurname, u.role, u.is_pro, u.is_pro_test, u.is_team, u.is_chuck, u.warn, u.is_banned, u.ban_where 
                        FROM (
                            (SELECT b.rec_type AS content_id, b.moder_num, b.stop_words_cnt, b.sort_order, a.id, a.fromuser_id AS user_id, a.msgtext, a.thread_id AS src_id, a.is_sent, a.title AS src_name, a.yt_link AS youtube_link, a.post_time, a.modified AS mod_time, a.reply_to, t.id_gr, c.question as poll_question 
                            FROM moderation b 
                            INNER JOIN blogs_msgs a ON a.id = b.rec_id 
                            INNER JOIN blogs_themes t ON a.thread_id = t.thread_id 
                            LEFT JOIN blogs_poll c ON c.thread_id = a.thread_id 
                            WHERE b.rec_type = '. self::MODER_BLOGS .' AND b.status = ?i AND b.stream_id = ? '. $sAnd .' 
                            ORDER BY '. $sOrder .')
                            
                            UNION ALL
                            
                            (SELECT b.rec_type AS content_id, b.moder_num, b.stop_words_cnt, b.sort_order, c.id, c.from_id AS user_id, c.msgtext, c.article_id AS src_id, c.is_sent, a.title AS src_name, c.youtube_link, c.created_time AS post_time, c.modified_time AS mod_time, NULL AS reply_to, NULL AS id_gr, NULL AS poll_question 
                            FROM moderation b 
                            INNER JOIN articles_comments c ON c.id = b.rec_id 
                            INNER JOIN articles_new a ON a.id = c.article_id 
                            WHERE b.rec_type = '. self::MODER_ART_COM .' AND b.status = ?i AND b.stream_id = ? '. $sAnd .' 
                            ORDER BY '. $sOrder .')
                        ) AS i 
                        LEFT JOIN users AS u ON u.uid = i.user_id 
                        ORDER BY '. $sOrderU .' LIMIT ?i';
                    
                    $aReturn = $GLOBALS['DB']->rows( $sQuery, 
                        $status, $stream_id, 
                        $status, $stream_id, 
                        $status, $stream_id, 
                        $limit 
                    );
                    
                    if ( $aReturn ) {
                        // выбрать те которые блоги в отдельный массив ссылок на $aReturn и закинуть в _getBlogsAttachPoll
                        $aBlogs = array();
                        
                        foreach ( $aReturn as $key => $aOne ) {
                            if ( $aOne['content_id'] == self::MODER_BLOGS ) {
                                $aBlogs[] = &$aReturn[$key];
                            }
                        }
                        
                        if ( $aBlogs ) {
                            $this->_getBlogsAttachPoll( $aBlogs );
                        }
                        
                        $this->_getCommentsAttach( $aReturn, self::MODER_ART_COM, 'articles_comments_files', 'file', 'comment_id', 'file_id' );
                    }
                    break;
                case self::MODER_USER_UNITED:
                    // Сборная: Изменения в профилях и Уточнения к разделам в портфолио
                    $sQuery = 'SELECT i.content_id, i.moder_num, i.stop_words_cnt, i.sort_order, i.id, i.user_id, i.ucolumn, i.new_val, 
                            i.prof_id, 
                            i.moduser_id, i.modified_reason, i.moderator_status, i.post_time, i.mod_time, i.utable, 
                            u.login, u.uname, u.usurname, u.is_pro, u.is_pro_test, u.is_team, u.is_chuck, u.warn, u.is_banned, u.ban_where, u.role 
                        FROM ( 
                            (SELECT b.rec_type AS content_id, b.moder_num,  
                                c.ucolumn, c. new_val, c.id, c.user_id, moderator_status, c.post_time, b.stop_words_cnt, b.sort_order, 
                                NULL AS prof_id, NULL AS moduser_id, NULL AS modified_reason, NULL AS mod_time, utable 
                            FROM moderation b 
                            INNER JOIN users_change c ON c.id = b.rec_id 
                            INNER JOIN users u ON u.uid = c.user_id 
                            WHERE b.rec_type = '. self::MODER_PROFILE .' AND b.status = ?i AND b.stream_id = ? '. $sAnd .' 
                            ORDER BY '. $sOrder .')
                            
                            UNION ALL
                            
                            (SELECT b.rec_type AS content_id, b.moder_num, c.ucolumn, pc.portf_text AS new_val, c.id, c.user_id, 
                                moderator_status, c.post_time, b.stop_words_cnt, b.sort_order, 
                                pc.prof_id, pc.moduser_id, pc.modified_reason, c.post_time AS mod_time, NULL AS utable 
                            FROM moderation b 
                            INNER JOIN portf_choise_change c ON c.id = b.rec_id 
                            INNER JOIN portf_choise pc ON pc.user_id = c.user_id AND pc.prof_id = c.prof_id 
                            WHERE b.rec_type = '. self::MODER_PORTF_CHOISE .' AND b.status = ?i AND b.stream_id = ? '. $sAnd .' 
                            ORDER BY '. $sOrder .')
                        ) AS i 
                        LEFT JOIN users AS u ON u.uid = i.user_id 
                        ORDER BY '. $sOrderU .' LIMIT ?i';
                    
                    $aReturn = $GLOBALS['DB']->rows( $sQuery, 
                        $status, $stream_id, 
                        $status, $stream_id, 
                        $limit 
                    );
                    break;
                default:
                    break;
            }
        }
        
        return $aReturn;
    }
    
    /**
     * Вспомогательная функция для получения аттачей к предложениям в проектах/конкурсах
     * 
     * @param type $aReturn 
     */
    function _getPrjOffersAttach( &$aReturn = array() ) {
        // аттачи для конкурсных работ
        $aContest = array();
        $aContestId = array();

        foreach ( $aReturn as $key => $aOne ) {
            if ( $aOne['content_id'] == self::MODER_PRJ_OFFERS && $aOne['kind'] == 7 ) {
                $aContestId[] = $aOne['id'];
                $aContest[$aOne['id']] = &$aReturn[$key];
            }
        }

        if ( $aContestId ) {
            $sQuery = 'SELECT attach.*, file.fname AS filename, preview.fname AS prevname 
                FROM projects_contest_attach AS attach 
                INNER JOIN file ON attach.file_id = file.id 
                LEFT JOIN file AS preview ON attach.prev_id = preview.id
                WHERE offer_id IN (?l) ORDER BY offer_id, sort';

            $aRes = $GLOBALS['DB']->rows( $sQuery, $aContestId );

            if ( $aRes ) {
                foreach ( $aRes as $aOne ) {
                    $aContest[$aOne['offer_id']]['attach'][] = $aOne;
                }
            }
        }
        //-----------------------
    }
    
    /**
     * Вспомогательная функция для получения аттачей и опросов для блогов
     * 
     * @param type $aReturn 
     */
    function _getBlogsAttachPoll( &$aReturn = array() ) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/blogs.php");

        // прикрепленные файлы
        blogs::AddAttach( $aReturn );

        // опросы
        foreach ( $aReturn as $key => $aOne ) {
            if ( $aOne['poll_question'] && !$aOne['reply_to'] ) {
                $aOne['poll'] = $GLOBALS['DB']->rows( 
                    'SELECT * FROM blogs_poll_answers WHERE thread_id = ? ORDER BY id', 
                    $aOne['src_id'] 
                );

                $aReturn[$key] = $aOne;
            }
        }
    }
    
    /**
     * Вспомогательная функция для получения аттачей и опросов для сообществ
     * 
     * @param type $aReturn 
     */
    function _getCommunityAttachPoll( &$aReturn = array() ) {
        // ответы на опросы -----
        $ids = '';
        $lnk = array();
        for ( $i=0, $c=count($aReturn); $i < $c; $i++ ) {
            if ( !$aReturn[$i]['parent_id'] ) {
                $ids .= ",{$aReturn[$i]['theme_id']}";
                $lnk[ $aReturn[$i]['theme_id'] ] = &$aReturn[$i];
            }
        }

        if ( $ids ) {
            $res = $GLOBALS['DB']->rows("SELECT * FROM commune_poll_answers WHERE theme_id IN (".substr($ids, 1).") ORDER BY id");
            if ( $res ) {
                foreach ( $res as $row ) {
                    $lnk[ $row['theme_id'] ]['answers'][] = $row;
                }
            }
        }
        //-----------------------

        // аттачи ---------------
        $id_attach = $x = array();
        foreach($aReturn as $k=>$v) {
            $x[$v['id']] = $v;
            if($v['file_exists']) {
                $id_attach[$v['id']] = $v['id'];
            }
        }

        if($id_attach) {
            //$ret = $GLOBALS['DB']->rows("SELECT file.*, commune_attach.cid, commune_attach.small FROM commune_attach JOIN file_commune as file ON file.id = commune_attach.fid WHERE commune_attach.cid IN (".implode(", ", $id_attach).")");            
            $ret = CFile::selectFilesBySrc(commune::FILE_TABLE, $id_attach);
            if($ret) { 
                foreach($ret as $k=>$val) {
                    $x[$val['src_id']]['attach'][] = $val;
                }

                foreach($x as $k=>$val) $r[] = $val;

                $aReturn = $r;
            }
        }
        //-----------------------
    }
    
    /**
     * Вспомогательная функция для получения аттачей и профессий для проектов/конкурсов
     * 
     * @param type $aReturn 
     */
    function _getProjectsAttachSpecs( &$aReturn = array() ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php" );
        require_once( $_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php" );

        // аттачи
        foreach ($aReturn as $key => $aOne) {
            $aReturn[$key]['attach'] = projects::GetAllAttach( $aOne['id'] );
            $aReturn[$key]['specs']  = projects::getSpecsStr( $aOne['id'],' / ', ', ', true );
            $aReturn[$key]['specs']  = str_replace('<a', '<a target="_blank"', $aReturn[$key]['specs']);
        }
    }
    
    /**
     * Возвращает заблокированые личные сообщения
     * 
     * @param  array $filter фильтр
     * @param  type $last_id последний полученный ID
     * @param  type $limit количество записей
     * @return array     
     */
    function getBlockedMessages( $filter = array(), $last_id = 2147483647, $limit = 10 ) {
        $DB      = new DB( 'plproxy' );
        $aFilter = array();
        
        if ( is_array($filter) && count($filter) ) {
        	foreach ( $filter as $sKey => $sVal ) {
        		$aFilter[] = array( $sKey, $sVal );
        	}
        }
        
        $sQuery  = 'SELECT m.* FROM messages_moder_deleted(?a, ?i, ?i) m ORDER BY m.id DESC';
        $aReturn = $DB->rows( $sQuery, $aFilter, $last_id, $limit );


        if ( $aReturn ) {
            $this->_getContentMessagesEx( $aReturn );
        }
        
        return $aReturn;
    }
    
    /**
     * Возвращает заблокированые блоги
     * 
     * @param  array $filter фильтр
     * @param  type $last_id последний полученный ID
     * @param  type $limit количество записей
     * @return array     
     */
    function getBlockedBlogs( $filter = array(), $last_id = 2147483647, $limit = 10 ) {
        $sYear = substr($filter['date_from'], 0, 4) == substr($filter['date_to'], 0, 4) ? '_'.substr($filter['date_from'], 0, 4) : '';
        $sUser = !empty( $filter['login'] ) ? ' WHERE ' . ( empty($filter['login_ex']) ? "u.login ILIKE  '%{$filter['login']}%'" : "u.login = '{$filter['login']}'" ) : '';
        
        $sQuery = 'SELECT i.*, u.login, u.uname, u.usurname, u.role, u.is_chuck, u.warn, u.is_banned, u.ban_where, t.id_gr, 
            c.question as poll_question, a.login AS adm_login, a.uname AS adm_uname, a.usurname AS adm_usurname, '. self::MODER_BLOGS .' AS content_id 
            FROM (
                SELECT a.*, b.admin, b.reason, b.blocked_time 
                FROM blogs_msgs'. $sYear .' a 
                LEFT JOIN blogs_blocked b ON a.reply_to IS NULL AND a.thread_id = b.thread_id 
                WHERE a.id < '. $last_id .' 
                    AND (b.id IS NOT NULL OR a.reply_to IS NOT NULL AND a.deleted IS NOT NULL AND a.fromuser_id <> a.deluser_id) 
                    AND a.post_time >= \''. $filter['date_from'] .'\'::timestamp without time zone
                    AND a.post_time <= \''. $filter['date_to'] .'\'::timestamp without time zone + interval \'1 day\'
            ) AS i 
            INNER JOIN blogs_themes t ON i.thread_id = t.thread_id 
            INNER JOIN users u ON u.uid = i.fromuser_id 
            LEFT JOIN users a ON a.uid = COALESCE(i.admin, i.deluser_id)  
            LEFT JOIN blogs_poll c ON i.reply_to IS NULL AND c.thread_id = i.thread_id 
            '. $sUser .'
            ORDER BY i.id DESC LIMIT '. $limit;
        
        $aReturn = $GLOBALS['DB']->rows( $sQuery );

        if ( is_array($aReturn) && count($aReturn) ) {
            $this->_getBlogsAttachPoll( $aReturn );
        }
        
        return $aReturn;
    }
    
    /**
     * Возвращает заблокированые сообщества
     * 
     * @param  array $filter фильтр
     * @param  type $last_id последний полученный ID
     * @param  type $limit количество записей
     * @return array     
     */
    function getBlockedCommunity( $filter = array(), $last_id = 2147483647, $limit = 10 ) {
        $sUser = !empty( $filter['login'] ) ? ' WHERE ' . ( empty($filter['login_ex']) ? "u.login ILIKE  '%{$filter['login']}%'" : "u.login = '{$filter['login']}'" ) : '';
        
        $sQuery = 'SELECT i.*, u.login, u.uname, u.usurname, u.role, u.is_chuck, u.warn, u.is_banned, u.ban_where, 
            c.question as poll_question, t.id AS top_id, a.login AS adm_login, a.uname AS adm_uname, a.usurname AS adm_usurname, '. self::MODER_COMMUNITY .' AS content_id 
            FROM (
                SELECT a.*, a.created_time AS post_time, a.cnt_files AS file_exists, b.admin, b.reason, b.blocked_time 
                FROM commune_messages a 
                LEFT JOIN commune_theme_blocked b ON a.parent_id IS NULL AND a.theme_id = b.theme_id 
                WHERE a.id < '. $last_id .' 
                    AND (b.theme_id IS NOT NULL OR a.parent_id IS NOT NULL AND a.deleted_time IS NOT NULL AND a.user_id <> a.deleted_id) 
                    AND a.created_time >= \''. $filter['date_from'] .'\'::timestamp without time zone
                    AND a.created_time <= \''. $filter['date_to'] .'\'::timestamp without time zone + interval \'1 day\'
            ) AS i 
            INNER JOIN users u ON u.uid = i.user_id 
            LEFT JOIN users a ON a.uid = COALESCE(i.admin, i.deleted_id)  
            LEFT JOIN commune_poll c ON i.parent_id IS NULL AND c.theme_id = i.theme_id 
            LEFT JOIN commune_messages t ON t.theme_id = i.theme_id AND t.parent_id IS NULL 
            '. $sUser .'
            ORDER BY i.id DESC LIMIT '. $limit;
        
        $aReturn = $GLOBALS['DB']->rows( $sQuery );
        
        if ( is_array($aReturn) && count($aReturn) ) {
            $this->_getCommunityAttachPoll( $aReturn );
        }
        
        return $aReturn;
    }
    
    /**
     * Возвращает заблокированые проекты и конкрусы
     * 
     * @param  array $filter фильтр
     * @param  type $last_id последний полученный ID
     * @param  type $limit количество записей
     * @return array     
     */
    function getBlockedProjects( $filter = array(), $last_id = 2147483647, $limit = 10 ) {
        $sAnd = !empty( $filter['login'] ) ? ' AND ' . ( empty($filter['login_ex']) ? "u.login ILIKE  '%{$filter['login']}%'" : "u.login = '{$filter['login']}'" ) : '';
        
        $sQuery = 'SELECT p.*, p.create_date AS post_time, city.city_name, country.country_name, (COALESCE(p.payed,0)<>0) AS ico_payed, 
                u.login, u.uname, u.usurname, u.email, u.photo, u.photosm, u.is_pro, u.warn, 
                u.is_banned, u.ban_where, u.is_team, u.reg_date, pb.reason, pb.blocked_time, link, NULL AS category, 
                offers_count, NULL AS category_name, '. self::MODER_PROJECTS .' AS content_id, 
                admins.login AS adm_login, admins.uname AS adm_name, admins.usurname AS adm_uname 
            FROM projects_blocked pb 
            INNER JOIN projects p ON p.id = pb.project_id 
            LEFT JOIN city ON city.id = p.city 
            LEFT JOIN country ON country.id = p.country 
            LEFT JOIN users AS admins ON admins.uid = pb.admin 
            LEFT JOIN employer AS u ON u.uid = p.user_id 
            WHERE p.id < '. $last_id .' AND p.create_date >= \''. $filter['date_from'] .'\'::timestamp without time zone
                AND p.create_date <= \''. $filter['date_to'] .'\'::timestamp without time zone + interval \'1 day\' '. $sAnd .' 
            ORDER BY p.id DESC LIMIT '. $limit;
        
        $aReturn = $GLOBALS['DB']->rows( $sQuery );

        if ( is_array($aReturn) && count($aReturn) ) {
            $this->_getProjectsAttachSpecs( $aReturn );
        }
        
        return $aReturn;
    }
    
    /**
     * Возвращает заблокированые предложения по проектам
     * 
     * @param  array $filter фильтр
     * @param  type $last_id последний полученный ID
     * @param  type $limit количество записей
     * @return array     
     */
    function getBlockedPrjOffers( $filter = array(), $last_id = 2147483647, $limit = 10 ) {
        $sUser = !empty( $filter['login'] ) ? ' WHERE ' . ( empty($filter['login_ex']) ? "u.login ILIKE  '%{$filter['login']}%'" : "u.login = '{$filter['login']}'" ) : '';
        
        $sQuery = 'SELECT i.*, '. self::MODER_PRJ_OFFERS .' AS content_id, u.uid AS user_id, u.login, u.uname, u.usurname, u.is_chuck, u.warn, u.is_banned, u.ban_where, u.is_pro, 
                a.login AS adm_login, a.uname AS adm_uname, a.usurname AS adm_usurname, 
                p.id AS src_id, p.kind, p.name AS src_name, d.post_text AS dialog_root, '. self::MODER_PRJ_OFFERS .' AS content_id 
            FROM (
                SELECT po.*, po.post_date AS post_time, po.descr AS post_text, b.admin, b.reason, b.blocked_time 
                FROM projects_offers po 
                LEFT JOIN projects_offers_blocked b ON po.id = b.src_id 
                WHERE po.id < '. $last_id .' 
                    AND (b.src_id IS NOT NULL OR po.is_deleted = true AND po.deluser_id <> po.user_id) 
                    AND po.post_date >= \''. $filter['date_from'] .'\'::timestamp without time zone
                    AND po.post_date <= \''. $filter['date_to'] .'\'::timestamp without time zone + interval \'1 day\'
            ) AS i 
            INNER JOIN projects p ON p.id = i.project_id 
            INNER JOIN freelancer u ON u.uid = i.user_id 
            LEFT JOIN projects_offers_dialogue d ON d.po_id = i.id AND d.root = true 
            LEFT JOIN users a ON a.uid = COALESCE(i.admin, i.deluser_id)  
            '. $sUser .'
            ORDER BY i.id DESC LIMIT '. $limit;

        $aReturn = $GLOBALS['DB']->rows( $sQuery );

        if ( is_array($aReturn) && count($aReturn) ) {
            // аттачи
            $this->_getPrjOffersAttach( $aReturn );
        }
        
        return $aReturn;
    }
    
    /**
     * Возвращает заблокированые комментарии в статьях
     * 
     * @param  array $filter фильтр
     * @param  type $last_id последний полученный ID
     * @param  type $limit количество записей
     * @return array     
     */
    function getBlockedArtCom( $filter = array(), $last_id = 2147483647, $limit = 10 ) {
        $sAnd = !empty( $filter['login'] ) ? ' AND ' . ( empty($filter['login_ex']) ? "u.login ILIKE  '%{$filter['login']}%'" : "u.login = '{$filter['login']}'" ) : '';
        
        $sQuery = 'SELECT c.id, c.created_time AS post_time, c.from_id AS user_id, c.article_id AS src_id, c.msgtext, c.youtube_link, c.is_sent, 
                a.title AS src_name, u.login, u.uname, u.usurname, u.role, u.is_chuck, u.warn, u.is_banned, u.ban_where, '. self::MODER_ART_COM .' AS content_id, 
                adm.login AS adm_login, adm.uname AS adm_uname, adm.usurname AS adm_usurname, c.deleted_reason, c.modified_time AS deleted 
            FROM articles_comments c 
            INNER JOIN articles_new a ON a.id = c.article_id 
            LEFT JOIN users AS u ON u.uid = c.from_id 
            LEFT JOIN users adm ON adm.uid = c.deleted_id 
            WHERE c.id < '. $last_id .' AND c.deleted_id IS NOT NULL AND c.deleted_id <> c.from_id AND c.created_time >= \''. $filter['date_from'] .'\'::timestamp without time zone
                AND c.created_time <= \''. $filter['date_to'] .'\'::timestamp without time zone + interval \'1 day\' '. $sAnd .' 
            ORDER BY c.id DESC LIMIT '. $limit;
        
        $aReturn = $GLOBALS['DB']->rows( $sQuery );

        if ( $aReturn ) {
            // аттачи
            $this->_getCommentsAttach( $aReturn, self::MODER_ART_COM, 'articles_comments_files', 'file', 'comment_id', 'file_id' );
        }
        
        return $aReturn;
    }
    
    /**
     * Возвращает заблокированые комментарии в предложениях к проектам
     * 
     * @param  array $filter фильтр
     * @param  type $last_id последний полученный ID
     * @param  type $limit количество записей
     * @return array     
     */
    function getBlockedPrjDialog( $filter = array(), $last_id = 2147483647, $limit = 10 ) {
        $sAnd = !empty( $filter['login'] ) ? ' AND ' . ( empty($filter['login_ex']) ? "u.login ILIKE  '%{$filter['login']}%'" : "u.login = '{$filter['login']}'" ) : '';
        
        $sQuery = 'SELECT d.id, d.post_date AS post_time, d.po_id AS offer_id, d.user_id, d.post_text, d.is_sent, po.project_id AS src_id, p.name AS src_name, 
                p.kind, u.login, u.uname, u.usurname, u.role, u.is_chuck, u.warn, u.is_banned, u.ban_where, '. self::MODER_PRJ_DIALOG .' AS content_id, 
                adm.login AS adm_login, adm.uname AS adm_uname, adm.usurname AS adm_usurname, b.reason, b.blocked_time 
            FROM projects_offers_dialogue_blocked b 
            INNER JOIN projects_offers_dialogue d ON d.id = b.src_id 
            INNER JOIN projects_offers po ON po.id = d.po_id 
            INNER JOIN projects p ON p.id = po.project_id 
            LEFT JOIN users AS u ON u.uid = d.user_id 
            LEFT JOIN users adm ON adm.uid = b.admin 
            WHERE d.id < '. $last_id .' AND d.post_date >= \''. $filter['date_from'] .'\'::timestamp without time zone
                AND d.post_date <= \''. $filter['date_to'] .'\'::timestamp without time zone + interval \'1 day\' '. $sAnd .' 
            ORDER BY d.id DESC LIMIT '. $limit;

        return $GLOBALS['DB']->rows( $sQuery );
    }
    
    /**
     * Возвращает заблокированые комментарии в конкурсных работах
     * 
     * @param  array $filter фильтр
     * @param  type $last_id последний полученный ID
     * @param  type $limit количество записей
     * @return array     
     */
    function getBlockedContestCom( $filter = array(), $last_id = 2147483647, $limit = 10 ) {
        $sAnd = !empty( $filter['login'] ) ? ' AND ' . ( empty($filter['login_ex']) ? "u.login ILIKE  '%{$filter['login']}%'" : "u.login = '{$filter['login']}'" ) : '';
        
        $sQuery = 'SELECT m.id, m.post_date AS post_time, m.offer_id, m.user_id, m.msg AS post_text, m.is_sent, po.project_id AS src_id, p.name AS src_name, 
                u.login, u.uname, u.usurname, u.role, u.is_chuck, u.warn, u.is_banned, u.ban_where, m.deleted_reason, m.deleted, 
                adm.login AS adm_login, adm.uname AS adm_uname, adm.usurname AS adm_usurname, '. self::MODER_CONTEST_COM .' AS content_id  
            FROM projects_contest_msgs m 
            INNER JOIN projects_contest_offers po ON po.id = m.offer_id 
            INNER JOIN projects p ON p.id = po.project_id 
            LEFT JOIN users AS u ON u.uid = m.user_id 
            LEFT JOIN users adm ON adm.uid = m.deluser_id 
            WHERE m.id < '. $last_id .' AND m.deleted IS NOT NULL AND m.deluser_id <> m.user_id AND m.post_date >= \''. $filter['date_from'] .'\'::timestamp without time zone
                AND m.post_date <= \''. $filter['date_to'] .'\'::timestamp without time zone + interval \'1 day\' '. $sAnd .' 
            ORDER BY m.id DESC LIMIT '. $limit;

        return $GLOBALS['DB']->rows( $sQuery );
    }
    
    /**
     * Возвращает заблокированые работы в портфолио
     * 
     * @param  array $filter фильтр
     * @param  type $last_id последний полученный ID
     * @param  type $limit количество записей
     * @return array     
     */
    function getBlockedPortfolio( $filter = array(), $last_id = 2147483647, $limit = 10 ) {
        $sAnd = !empty( $filter['login'] ) ? ' AND ' . ( empty($filter['login_ex']) ? "u.login ILIKE  '%{$filter['login']}%'" : "u.login = '{$filter['login']}'" ) : '';
        
        $sQuery = 'SELECT p.*, p.post_date AS post_time, u.login, u.uname, u.usurname, u.is_chuck, u.warn, u.is_banned, u.ban_where, '. self::MODER_PORTFOLIO .' AS content_id, 
                adm.login AS adm_login, adm.uname AS adm_uname, adm.usurname AS adm_usurname, b.reason, b.blocked_time 
            FROM portfolio_blocked b 
            INNER JOIN portfolio p ON p.id = b.src_id 
            INNER JOIN freelancer u ON u.uid = p.user_id 
            LEFT JOIN users adm ON adm.uid = b.admin 
            WHERE p.id < '. $last_id .' AND p.post_date >= \''. $filter['date_from'] .'\'::timestamp without time zone
                AND p.post_date <= \''. $filter['date_to'] .'\'::timestamp without time zone + interval \'1 day\' '. $sAnd .' 
            ORDER BY p.id DESC LIMIT '. $limit;

        return $GLOBALS['DB']->rows( $sQuery );
    }
    
    /**
     * Возвращает заблокированые предложения фрилансеров "Сделаю"
     * 
     * @param  array $filter фильтр
     * @param  type $last_id последний полученный ID
     * @param  type $limit количество записей
     * @return array     
     */
    function getBlockedSdelau( $filter = array(), $last_id = 2147483647, $limit = 10 ) {
        $sAnd = !empty( $filter['login'] ) ? ' AND ' . ( empty($filter['login_ex']) ? "u.login ILIKE  '%{$filter['login']}%'" : "u.login = '{$filter['login']}'" ) : '';
        
        $sQuery = 'SELECT o.*, o.post_date AS post_time, o.descr AS post_text, u.login, u.uname, u.usurname, u.is_chuck, u.warn, u.is_banned, u.ban_where, 
                p.name as profname, p.is_text, pg.name as src_name, p.link, '. self::MODER_SDELAU .' AS content_id, 
                adm.login AS adm_login, adm.uname AS adm_uname, adm.usurname AS adm_usurname 
            FROM freelance_offers o 
            INNER JOIN users u ON u.uid = o.user_id 
            LEFT JOIN prof_group pg ON pg.id = o.category_id 
            LEFT JOIN professions p ON p.id = o.subcategory_id 
            LEFT JOIN users adm ON adm.uid = o.admin 
            WHERE o.id < '. $last_id .' AND o.is_blocked = true AND o.post_date >= \''. $filter['date_from'] .'\'::timestamp without time zone
                AND o.post_date <= \''. $filter['date_to'] .'\'::timestamp without time zone + interval \'1 day\' '. $sAnd .' 
            ORDER BY o.id DESC LIMIT '. $limit;

        return $GLOBALS['DB']->rows( $sQuery );
    }
    
    /**
     * Сбор дополнительных данных для личных сообщений
     * 
     * @param array $aReturn массив личных сообщений
     */
    private function _getContentMessagesEx( &$aReturn ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/messages.php' );

        // прикрепленные файлы 
        messages::getMessagesAttaches( $aReturn );

        // прикручиваем юзеров к сообщениям - на нодах есть не все поля
        $aUsers = array();

        foreach ( $aReturn as $aOne ) {
            $aUsers[$aOne['from_id']] = array();
            $aUsers[$aOne['to_id']]   = array();
            
            if ( !empty($aOne['deluser_id']) ) {
                $aUsers[$aOne['deluser_id']] = array();
            }
        }

        $aRes = $GLOBALS['DB']->rows( 'SELECT uid, login, uname, usurname, role, is_pro, is_pro_test, is_team, 
                is_chuck, warn, is_banned, ban_where 
            FROM users WHERE uid IN (?l)', array_unique(array_keys($aUsers)) );

        if ( $aRes ) {
            foreach ( $aRes as $aOne ) {
                $aUsers[$aOne['uid']] = $aOne;
            }

            foreach ( $aReturn as $key => $aOne ) {
                $aReturn[$key]['f_user'] = $aUsers[$aOne['from_id']];
                $aReturn[$key]['t_user'] = $aUsers[$aOne['to_id']];
                
                if ( !empty($aOne['deluser_id']) ) {
                    $aReturn[$key]['adm_login']    = $aUsers[$aOne['deluser_id']]['login'];
                    $aReturn[$key]['adm_uname']    = $aUsers[$aOne['deluser_id']]['uname'];
                    $aReturn[$key]['adm_usurname'] = $aUsers[$aOne['deluser_id']]['usurname'];
                }
            }
        }
        //---------------------------------------
    }


    /**
     * Возвращает количество опубликованных проектов за последние 24 часа
     * 
     * @param  array $aEmpId масств UID работодателей
     * @return array
     */
    function getProjectsPer24( $aEmpId = array() ) {
        if (is_array($aEmpId) && count($aEmpId) ) {
            $sQuery = "SELECT user_id, COUNT(id) AS prj_cnt FROM projects 
                WHERE user_id IN (?l) AND post_date > DATE_TRUNC('hour', now() - interval '1 day')
                GROUP BY user_id";
            
            $aReturn = array();
            $aPrjCnt = $GLOBALS['DB']->rows( $sQuery, $aEmpId );
            
            if ( $aPrjCnt ) {
                foreach ( $aPrjCnt as $aOne ) {
                    $aReturn[ $aOne['user_id'] ] = $aOne['prj_cnt'];
                }
            }
            
            return $aReturn;
        }
    }
    
    /**
     * Вспомогательная функция для получения аттачей к комментариям
     * 
     * @param array $aComments массив комментариев к которым нужно выбрать аттачи
     * @param string $sContentId идентификатор сущности из admin_contents
     * @param string $sTable имя таблицы типа shop_comments_files
     * @param string $sFileTable имя таблицы типа file
     * @param string $sIdFld имя поля идентификатора комментария из $sTable
     * @param string $sFileIdFld имя поля идентификатора файла из $sTable
     */
    function _getCommentsAttach( &$aComments = array(), $sContentId = '', $sTable = '', $sFileTable = '', $sIdFld = '', $sFileIdFld = '' ) {
        foreach ( $aComments as $key => $aOne ) {
            if ( $aOne['content_id'] == $sContentId ) {
                $comment_ids[] = $aOne['id'];
                $comments_arr[$aOne['id']] = &$aComments[$key];
            }
        }

        $sQuery = "SELECT * FROM {$sTable}
            INNER JOIN {$sFileTable} file ON file.id = {$sTable}.{$sFileIdFld}
            WHERE {$sTable}.{$sIdFld} IN (?l)";
        
        $res = $GLOBALS['DB']->rows( $sQuery, $comment_ids );

        foreach($res as $file) {
            $comments_arr[$file[$sIdFld]]['attach'][] = $file;
        }
    }
    
    /**
     * Обновляет счетчики занятых потоков
     * 
     * @param  int $content_id идентификатор сущности из admin_contents
     * @param  string $stream_id опционально. идентификатор потока, если нужно вернуть его счетчик
     * @return int счетчик потока
     */
    private function _countChosenStreams( $content_id = 0, $stream_id = '' ) {
        $mRet = false;
        
        if ( isset($this->content_streams[$content_id]) && count($this->content_streams[$content_id]) ) {
            $this->streams_count[$content_id] = 0;
            $nCnt = 0;
            
            foreach ( $this->content_streams[$content_id] as $sKey => $aOne ) {
                if ( !empty($aOne['admin_id']) ) {
                    if ( $aOne['stream_id'] == $stream_id ) {
                        $mRet = $nCnt;
                    }
                    
                    $aOne['stream_num'] = $nCnt;
                    $this->streams_count[$content_id]++;
                    $nCnt++;
                }
                else {
                    $aOne['stream_num'] = '';
                }
                
                $this->content_streams[$content_id][$sKey] = $aOne;
            }
            
            $this->last_update = time();
            $this->_saveStreams();
        }
        
        return $mRet;
    }
    
    /**
     * Захват/перехват потока пользователем
     * 
     * @param  int $content_id идентификатор сущности из admin_contents
     * @param  string $stream_id идентификатор потока
     * @param  int $user_id UID пользователя
     * @return string идентификатор захваченного потока - успех, пустая строка - провал
     */
    function chooseStream( $content_id = 0, $stream_id = '', $user_id = 0 ) {
        $sStreamId = '';
        
        if ( $this->content_streams === false || $this->first_update === false || $this->last_update === false ) {
            $this->_initStreams();
        }
        else {
            if ( isset($this->content_streams[$content_id]) && count($this->content_streams[$content_id]) ) {
                $bChoose = false;
                
                foreach ( $this->content_streams[$content_id] as $sKey => $aOne ) {
                    if ( $aOne['stream_id'] == $stream_id ) {
                        if ( $aOne['admin_id'] == $user_id ) {
                            // пользователь пытается захватить свой же поток
                            $sStreamId = 'user_id';
                        }
                        else {
                            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );

                            $users = new users();
                            $login = $users->GetField( $user_id, $error, 'login' );

                            if ( empty($aOne['admin_id']) ) {
                                // захват свободного потока
                                $bChoose = true;
                                $aOne['resolve_cnt'] = 0;
                            }

                            $aOne['admin_id']   = $user_id;
                            $aOne['admin_name'] = iconv( 'CP1251', 'UTF-8', $login );
                            $aOne['time']       = time();

                            $this->content_streams[$content_id][$sKey] = $aOne;
                            $sStreamId = $stream_id;
                        }
                        
                        break;
                    }
                }
                
                if ( $bChoose ) {
                    $stream_num = $this->_countChosenStreams( $content_id, $stream_id );
                    
                    if ( $stream_num !== false ) {
                        $nLimit = $content_id == self::MODER_MSSAGES ? self::MESSAGES_PER_PAGE : self::CONTENTS_PER_PAGE;
                        $this->chooseContent( $content_id, $stream_id, $stream_num, $nLimit );
                        
                        $memBuff  = new memBuff;
                        $memBuff->delete( 'ucs_streams_queue' );
                    }
                }
            }
            
            $this->last_update = time();
            $this->_saveStreams();
        }
        
        return $sStreamId;
    }
    
    /**
     * Возвращает список модерируемых сущностей с учетом прав пользователя
     * 
     * @return array 
     */
    function getContentsForUser() {
        $aReturn  = array();
        
        foreach ( $this->contents as $aOne ) {
            if ( $this->isAllowed( $aOne['rights'], $this->user_permissions ) ) {
                $aReturn[$aOne['id']] = array( 'name' => $aOne['name'] );
            }
        }
        
        return $aReturn;
    }
    
    /**
     * Возвращает список модерируемых сущностей с учетом прав пользователя
     * для раздела админки Заблокированные
     * 
     * @return array 
     */
    function getBlockedContentsForUser() {
        $aReturn   = array();
        $aContents = $GLOBALS['DB']->rows( 'SELECT * FROM admin_contents WHERE is_blocked = true ORDER BY id' );
        
        if ( $aContents ) {
            foreach ( $aContents as $aOne ) {
                if ( $this->isAllowed( $aOne['rights'], $this->user_permissions ) ) {
                    $aReturn[$aOne['id']] = array( 'name' => $aOne['name'] );
                }
            }
        }
        
        return $aReturn;
    }
    
    /**
     * Возвращает список захваченных пользователем
     * 
     * @param  int $user_id UID пользователя
     * @return array
     */
    function getStreamsForUser( $user_id = 0 ) {
        $aReturn = array();
        
        if ( $this->content_streams === false || $this->first_update === false || $this->last_update === false ) {
            $this->_initStreams();
        }
        else {
            foreach ( $this->content_streams as $content_id => $aStreams ) {
                foreach ( $aStreams as $sKey => $aOne ) {
                    if ( $aOne['admin_id'] == $user_id ) {
                        $aOne['content_id'] = $content_id;
                        $aOne['title_num']  = $sKey + 1;
                        $aReturn[] = $aOne;
                    }
                }
            }
        }
        
        return $aReturn;
    }
    
    /**
     * Возвращает список обновлений потоков для всех сущностей с учетом текущей смены и прав пользователя
     */
    function updateStreamsForUser() {
        $aReturn  = array();
        
        if ( $this->content_streams === false || $this->first_update === false || $this->last_update === false ) {
            $this->_initStreams();
        }
        
        if ( $this->last_update - self::MODER_SHIFTS_REFRESH > $this->first_update ) {
            $this->_updateStreams();
        }
        
        foreach ( $this->contents as $aOne ) {
            if ( $this->isAllowed( $aOne['rights'], $this->user_permissions ) ) {
                $aReturn[$aOne['id']] = $this->content_streams[$aOne['id']];
            }
        }
        
        $this->last_update = time();
        $this->_saveStreams();
        
        return $aReturn;
    }
    
    /**
     * Обновляет список потоков для всех сущностей с учетом текущей смены
     * 
     * @return bool true 
     */
    private function _updateStreams() {
        if ( $this->content_streams === false ) {
            $this->_initStreams();
            return true;
        }
        
        $aCount = $this->_getStreamsCount();
        
        if ( is_array($aCount) && count($aCount) ) {
            foreach ( $aCount as $aOne ) {
                $sId   = $aOne['content_id'];
                $nCnt  = count($this->content_streams[$sId]);
                $nDiff = $aOne['streams'] - $nCnt;
                
                if ( $nDiff > 0 ) {
                    for ( $i = 0; $i < $nDiff; $i++ ) {
                        $this->content_streams[$sId][] = array( 
                            'stream_id' => uniqid(), 'admin_id' => '' 
                        );
                    }
                }
                elseif ( $nDiff < 0 ) {
                    $i = 0;
                    
                    foreach ( $this->content_streams[$sId] as $aOne ) {
                        if ( $nDiff == 0 ) {
                            break;
                        }
                        elseif ( empty($aOne['admin_id']) ) {
                            array_splice( $this->content_streams[$sId], $i, 1 );
                            $nDiff++;
                            $i--;
                        }
                        
                        $i++;
                    }
                }
            }
        }
        
        $this->first_update = $this->last_update = time();
        $this->_saveStreams(true);
        
        return true;
    }
    
    /**
     * Инициализирует список потоков для всех сущностей с учетом текущей смены
     */
    private function _initStreams() {
        $aCount = $this->_getStreamsCount();
        $this->content_streams = array();

        if ( is_array($aCount) && count($aCount) ) {
            foreach ( $aCount as $aOne ) {
                $aStreams = array();
                
                for ( $i = 0; $i < $aOne['streams']; $i++ ) {
                    $aStreams[] = array( 'stream_id' => uniqid(), 'admin_id' => '' );
                }
                
                $this->content_streams[$aOne['content_id']] = $aStreams;
                $this->streams_count[$aOne['content_id']]   = 0;
                $this->_releaseContent( $aOne['content_id'] );
            }
        }
        
        $this->first_update = $this->last_update = time();
        $this->_saveStreams(true);
    }
    
    /**
     * Ставит метку о том, что нужно сохранить состояние поток в деструкторе
     * 
     * @param boolean $init   true - пересохранить данные, false - только если есть изменения
     */
    private function _saveStreams($init=false) {
        $DB   = new DB('plproxy');
        $save = false;
            
        if ( $init ) {
            $DB->query("SELECT mod_streams_release()");
            // для $this->first_time используется спец.ключ в mod_streams (чтобы не плодить лишние таблицы)
            $DB->query("SELECT mod_stream(?, ?, ?, ?, ?, ?, ?, ?i)", '_first_update', 0, 0, NULL, NULL, NULL, $this->first_update, 0);
        }
            
        foreach ( $this->content_streams as $i => $streams ) {
            foreach ( $streams as $j => $s ) {
                $itime = empty($this->initial_sreams[$i][$j]['time'])? 0: $this->initial_sreams[$i][$j]['time'];
                $stime = empty($s['time'])? 0: $s['time'];
                if ( $itime == $stime && !$init ) {
                    continue;
                }
                $num         = $s['stream_num'] === ''? NULL: $s['stream_num'];
                $admin_id    = empty($s['admin_id'])? NULL: $s['admin_id'];
                $admin_name  = empty($s['admin_name'])? NULL: $s['admin_name'];
                $time        = empty($s['time'])? NULL: $s['time'];
                $resolve_cnt = empty($s['resolve_cnt'])? 0: $s['resolve_cnt'];
                $save        = true;
                $DB->query("SELECT mod_stream(?, ?, ?, ?, ?, ?, ?, ?i)", $s['stream_id'], $i, $j, $num, $admin_id, $admin_name, $time, $resolve_cnt);
            }
        }

        if ( $save ) {
            $this->mem_buff->set( 'user_content_streams', $this->content_streams, self::MODER_MEMBUFF_TTL, 'user_content' );
            $this->mem_buff->set( 'ucs_streams_count',    $this->streams_count,   self::MODER_MEMBUFF_TTL, 'user_content' );
        }
        
        $this->mem_buff->set( 'ucs_first_update',     $this->first_update,    self::MODER_MEMBUFF_TTL, 'user_content' );
        $this->mem_buff->set( 'ucs_last_update',      $this->last_update,     self::MODER_MEMBUFF_TTL, 'user_content' );
    }

    /**
     * Возвращает из базы данных количесво потоков для всех сущностей с учетом текущей смены
     * 
     * @return array
     */
    private function _getStreamsCount() {
        return $GLOBALS['DB']->rows( 
            "SELECT i.content_id, SUM(i.streams) AS streams FROM (
                SELECT c.content_id, 
                CASE WHEN s.time_from < s.time_to THEN 
                        CASE WHEN now()::time >= s.time_from AND now()::time < s.time_to THEN c.streams 
                            ELSE 0
                        END
                    WHEN s.time_from > s.time_to THEN 
                        CASE WHEN (now()::time >= s.time_from AND now()::time <= '23:59:59'::time
                            OR now()::time >= '00:00:00'::time AND now()::time < s.time_to) THEN c.streams 
                            ELSE 0
                        END
                    ELSE 0 
                END AS streams 
                FROM admin_shifts_contents c 
                INNER JOIN admin_shifts s ON s.id = c.shift_id 
            ) AS i 
            GROUP BY i.content_id 
            ORDER BY i.content_id" 
        );
    }
    
    ///////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////
    //                                                   //
    //           Работа со сменами модераторов           //
    //                                                   //
    ///////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////
    
    /**
     * Обновляет количество потоков всех сущностей в каждую смену
     * 
     * @param type $aStreams 
     * @return bool true - успех, false - провал
     */
    function updateShiftsContents( $aStreams = array() ) {
        if ( is_array($aStreams) && !empty($aStreams) ) {
            foreach ( $aStreams as $sContentId => $aShifts ) {
                if (is_array($aShifts) && count($aShifts) ) {
                    foreach ( $aShifts as $sShiftId => $sStreams ) {
                        $GLOBALS['DB']->update( 
                            'admin_shifts_contents', 
                            array('streams' => $sStreams), 
                            'shift_id = ?i AND content_id = ?i', 
                            $sShiftId, $sContentId
                        );
                        
                        if ( $GLOBALS['DB']->error ) {
                            return false;
                        }
                    }
                }
            }
        }
        
        return true;
    }
    
    /**
     * Проверка на корректность количества потоков в сменах
     * 
     * @param  mixed $mParam данные доя проверки
     * @return bool true - успех, false - провал
     */
    function validShiftsContents( $mParam = '' ) {
        $bRet = true;

        if ( !is_array($mParam) ) {
            if ( preg_match('/^[\d]+$/', $mParam) && intval($mParam) > 0 && intval($mParam) < 32767 ) {
                return true;
            }
            else {
                return false;
            }
        }
        else {
            foreach ( $mParam as $mOne ) {
                if ( !$this->validShiftsContents($mOne) ) {
                    return false;
                }
            }
        }

        return $bRet;
    }
    
    /**
     * Возвращает количество потоков всех сущностей в каждую смену
     * 
     * @return array 
     */
    function getShiftsContents() {
        return $GLOBALS['DB']->rows( 'SELECT * FROM admin_shifts_contents ORDER BY content_id, shift_id' );
    }
    
    /**
     * Возвращает список модерируемых сущностей
     * 
     * @return array 
     */
    function getContents() {
        return $GLOBALS['DB']->rows( 'SELECT * FROM admin_contents WHERE is_active = true ORDER BY id' );
    }
    
    /**
     * Добавляет смены
     * 
     * @param  array $aFrom масив вреени начала смен
     * @param  array $aTo масив вреени конца смен
     * @return bool true - успех, false - провал
     */
    function insertShifts( $aFrom = array(), $aTo = array() ) {
        $bRet = false;
        
        if ( is_array($aFrom) && !empty($aFrom) && is_array($aTo) && !empty($aTo) ) {
            $aData = array();
            
            for ( $i = 0; $i < count($aFrom); $i++ ) {
                $aData[] = array( 'time_from' => $aFrom[$i], 'time_to' => $aTo[$i] );
            }
            
            $GLOBALS['DB']->insert( 'admin_shifts', $aData );
            
            $bRet = empty($GLOBALS['DB']->error);
        }
        
        return $bRet;
    }
    
    /**
     * Обновляет смены
     * 
     * @param  array $aId массив идентификаторов смен которые нужнообновить
     * @param  array $aFrom масив вреени начала смен соответствующий идентификаторам
     * @param  array $aTo масив вреени конца смен соответствующий идентификаторам
     * @return bool true - успех, false - провал
     */
    function updateShifts( $aId = array(), $aFrom = array(), $aTo = array() ) {
        $bRet = false;
        
        if ( is_array($aId) && !empty($aId) && is_array($aFrom) && !empty($aFrom) && is_array($aTo) && !empty($aTo) ) {
            for ( $i = 0; $i < count($aId); $i++ ) {
                $aData = array( 'time_from' => $aFrom[$i], 'time_to' => $aTo[$i] );
                
                $GLOBALS['DB']->update( 'admin_shifts', $aData, 'id = ?i', $aId[$i] );
                
                if ( $GLOBALS['DB']->error ) {
                    return false;
                }
            }
            
            $bRet = true;
        }
        
        return $bRet;
    }
    
    /**
     * Удаляет одну или несколько смен по ID
     * 
     * @param  mixed $mShiftId массив идентификаторов или один идентификатор
     * @return bool true - успех, false - провал
     */
    function deleteShifts( $mShiftId = array() ) {
        $bRet = false;
        
        if ( !empty($mShiftId) ) {
            if ( !is_array( $mShiftId ) ) {
                $mShiftId = array( $mShiftId );
            }
            
            $GLOBALS['DB']->query( 'DELETE FROM admin_shifts WHERE id IN (?l)', $mShiftId );
            
            $bRet = empty($GLOBALS['DB']->error);
        }
        
        return $bRet;
    }
    
    /**
     * Возвращает список смен
     * 
     * @return array 
     */
    function getShifts() {
        return $GLOBALS['DB']->rows( 'SELECT * FROM admin_shifts ORDER BY id' );
    }
    
    /**
     * Проверяет каждый элемент массива на формат H:i
     * 
     * @param  array $aTimes массив для проверки
     * @return bool true - успех, false - провал
     */
    function validTimes( $aTimes = array() ) {
        $bRet = true;
        
        if ( is_array($aTimes) && !empty($aTimes) ) {
            $sPattern = '/^(([0-1][0-9])|([2][0-3])):([0-5][0-9])$/';
            
            foreach ( $aTimes as $sTime ) {
                if ( !preg_match($sPattern, $sTime) ) {
                    $bRet = false;
                    break;
                }
            }
        }
        
        return $bRet;
    }
    
    /**
     * Проверяет чтобы все параметры были массивами одинаковой размерности
     * 
     * @return bool true - успех, false - провал
     */
    function matchCount() {
        $bRet  = true;
        $nArgs = func_num_args();
        
        if ( $nArgs ) {
            $aArgs = func_get_args();
            
            if (is_array($aArgs[0]) ) {
                $nCnt = count($aArgs[0]);
                
                for ( $i = 1; $i < count($aArgs); $i++ ) {
                    if ( !is_array($aArgs[$i]) || count($aArgs[$i]) != $nCnt ) {
                        $bRet = false;
                        break;
                    }
                }
            }
            else {
                $bRet = false;
            }
        }
        else {
            $bRet = false;
        }
        
        return $bRet;
    }
    
    ////////////////////////////////////////////////////////
    //                                                    //
    //                  UTILITY FUNCTIONS                 //
    //                                                    //
    ////////////////////////////////////////////////////////
    
    /**
     * Имеет ли пользователь доступ в раздел
     * 
     * @param  type $site раздел
     * @return bool true - имеет, false - не имеет
     */
    function hasPermissions( $site = '' ) {
        $nIdx = array_search( $site, self::$site_allow );
        
        switch ($nIdx) {
            case 15:
            case 19:
            case 20:
            case 21:
                $bRet = false;
                
                foreach ( $this->contents as $aOne ) {
                    if ( $this->isAllowed($aOne['rights'], $this->user_permissions) ) {
                        $bRet = true;
                        break;
                    }
                }
                
                return $bRet;
                break;
            
            case 16:
                return $this->isAllowed( 'all', $this->user_permissions );
                break;
            
            case 17:
                return $this->isAllowed( 'all', $this->user_permissions );
                break;
            
            case 18:
                return $this->isAllowed( 'adm', $this->user_permissions );
                break;

            default:
                return false;
                break;
        }
    }
    
    /**
     * Имеет ли пользователь доступ к модерированию конкретной сущности
     * 
     * @param  int $sId идентификатор сущности из admin_contents
     * @return bool true - имеет, false - не имеет
     */
    function hasContentPermissions( $sId = 0, $aPermissions = null ) {
        $bRet = $bFound = false;
        
        if ( is_null($aPermissions) ) {
            $aPermissions = $this->user_permissions;
        }
        
        foreach ( $this->contents as $aOne ) {
            if ( $aOne['id'] == $sId ) { 
                if ( $this->isAllowed( $aOne['rights'], $aPermissions ) ) {
                    $bRet = true;
                }
                
                $bFound = true;
                
                break;
            }
        }
        
        if ( !$bFound ) {
            $aOne['rights'] = $GLOBALS['DB']->val( 'SELECT rights FROM admin_contents WHERE id = ?i', $sId );
            
            if ( $this->isAllowed( $aOne['rights'], $aPermissions ) ) {
                $bRet = true;
            }
        }
        
        return $bRet;
    }

    /**
     * Получает группу для выбора причины удаления по типу сущности
     *
     * @param  int $type     идентификатор сущности
     * @param  int $rec_type подтип сущности
     * @return int           id группы
     */
    function getReasonGroup($type, $rec_type) {
        $group = 0;
        switch($type) {
            case user_content::MODER_MSSAGES:
                $group = 47;
                break;
            case user_content::MODER_BLOGS:
                if($rec_type==1) { // Блокировка сообщения
                    $group = 7; 
                }
                if($rec_type==2) { // Удаление комментария
                    $group = 24; 
                }
                if($rec_type==3) { // Удаление сообщения
                    $group = 49;
                }
                break;
            case user_content::MODER_COMMUNITY:
                if($rec_type==1) {
                    $group = 15;
                }
                if($rec_type==2) {
                    $group = 51;
                }
                break;
            case user_content::MODER_PROJECTS:
                $group = 9;
                break;
            case user_content::MODER_PRJ_OFFERS:
                $group = 22;
                break;
            case user_content::MODER_ART_COM:
                $group = 59;
                break;
            case user_content::MODER_PROFILE:
                $group = 61;
                break;
            case user_content::MODER_PRJ_DIALOG:
                $group = 31;
                break;
            case user_content::MODER_CONTEST_COM:
                $group = 31;
                break;
            case user_content::MODER_PORTF_CHOISE:
                $group = 62;
                break;
            case user_content::MODER_PORTFOLIO:
                $group = 29;
                break;
            case user_content::MODER_SDELAU:
                $group = 13;
                break;
            case user_content::MODER_TSERVICES:
                $group = 64;
                break;
        }
        return $group;
    }
    

    /**
     * Расчитывает количество непроверенных сообщений во всех сущностях и сохраняет результат в memcache
     *
     * @return array
     */
    function getQueueCounts() {
        global $DB;
        $memBuff  = new memBuff;
        $counters = $memBuff->get( 'ucs_queue_count' );
        
        if ( !empty($counters['update']) && $counters['update'] + self::MODER_QUEUE_CNT_REFRESH > time() ) {
            return $counters;
        }
        
        // в начале стандартный проход по всем сущностям
        $aIdx = array_keys( self::$table );
        
        foreach ( $aIdx as $nIdx ) {
            $counters[ $nIdx ] = intval( $DB->val('SELECT COUNT(*) FROM moderation WHERE rec_type = ?i AND stream_id IS NULL', $nIdx) );
        }
        
        // после этого - нестандартное
        // личные сообщения - переписывает стандартный проход, где будет 0
        $dbProxy = new DB('plproxy');
        $cnt = 0;
        $res = $dbProxy->query( 'SELECT * FROM messages_moder_queue_count()' );
        while ( $row = pg_fetch_row($res) ) {
            $cnt += $row[0];
        }
        $counters[self::MODER_MSSAGES] = $cnt;
        
        // сборная: предложения к проектам и конкурсам, комментарии к ним, предложения в "Сделаю"
        $counters[self::MODER_PRJ_COM] = $counters[self::MODER_PRJ_OFFERS] + $counters[self::MODER_PRJ_DIALOG] 
                + $counters[self::MODER_CONTEST_COM];// + $counters[self::MODER_SDELAU];

        // сборная: Комментарии: статьи
        $counters[self::MODER_COMMENTS] = $counters[self::MODER_ART_COM];

        // сборная: Работы в портфолио, Уточнения к разделам в портфолио  
        $counters[self::MODER_PORTF_UNITED] = $counters[self::MODER_PORTF_CHOISE] + $counters[self::MODER_PORTFOLIO];
        
        // Сборная: Блоги: посты и комментарии, Комментарии в Комментарии в статьях
        $counters[self::MODER_BLOGS_UNITED] = $counters[self::MODER_BLOGS] + $counters[self::MODER_ART_COM];
        
        // Сборная: Изменения в профилях и Уточнения к разделам в портфолио
        $counters[self::MODER_USER_UNITED] = $counters[self::MODER_PORTF_CHOISE] + $counters[self::MODER_PROFILE];
        
        // 
        $counters['update'] = time();
        $memBuff->set( 'ucs_queue_count', $counters, self::MODER_QUEUE_CNT_REFRESH * 2, 'user_content' );
        
        return $counters;
    }
    
    /**
     * Расчитывает количество непроверенных сообщений в каждом занятом потоке и сохраняет результат в memcache
     * 
     * @return array
     */
    function getStreamsQueueCounts() {
        $memBuff  = new memBuff;
        $aCounters = $memBuff->get( 'ucs_streams_queue' );
        
        if ( !empty($aCounters['update']) && $aCounters['update'] + self::MODER_QUEUE_CNT_REFRESH > time() ) {
            return $aCounters;
        }
        
        if ( $this->content_streams === false ) {
            $this->_initStreams();
        }
        
        $aCounters = array();
        $sQuery    = '';
        
        foreach ( $this->content_streams as $sContentId => $aStreams ) {
            foreach ( $aStreams as $sKey => $aOne ) {
                if ( $this->streams_count[$sContentId] && !empty($aOne['admin_id']) ) {
                    $sQuery .= $GLOBALS['DB']->parse( 
                        "COUNT(CASE WHEN stream_id = ? AND rec_type IN (?l) AND status = 0 THEN 1 ELSE NULL END) AS cnt_{$aOne['stream_id']}, \n", 
                        $aOne['stream_id'], self::$table[$sContentId]['moder'] 
                    );
                }
            }
        }
        
        if ( $sQuery ) {
            $aRow = $GLOBALS['DB']->row( 'SELECT '. rtrim( $sQuery, ", \n" ) .' FROM moderation' );
            
            if ( $aRow ) {
                foreach ( $aRow as $sKey => $sVal ) {
                    list( $sFake, $sStreamId ) = explode( '_', $sKey );
                    $aCounters[ $sStreamId ]   = intval( $sVal );
                }
            }
        }
        
        $aCounters['update'] = time();
        
        $memBuff->set( 'ucs_streams_queue', $aCounters, self::MODER_QUEUE_CNT_REFRESH * 2, 'user_content' );
        
        return $aCounters;
    }
    
    /**
     * Возвращает есть ли счетчики в шапке потоков данной сущности
     * 
     * @param  int $nContentId идентификатор сущности из admin_contents
     * @return bool true - , false 
     */
    public function isStreamCounters( $nContentId = 0 ) {
        return isset( self::$counters[$nContentId] );
    }
    
    /**
     * Возвращает данные для счетчиков в шапке потоков данной сущности
     * 
     * @param  int $nContentId идентификатор сущности из admin_contents
     * @param  bool $bNumsOnly опционально. установить true, если нужно вернуть только цифры
     * @param  bool $bShow опционально. установить true, если хотя бы один счетчик больше нуля
     * @return array
     */
    function getStreamCounters( $nContentId = 0, $bNumsOnly = false, &$bShow = false ) {
        $aReturn = array();
        $bShow   = false;
        
        if ( isset(self::$counters[$nContentId]) ) {
            foreach ( self::$counters[$nContentId] as $nKey => $aOne ) {
                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/' . $aOne['class'] . '.php' );
                
                $nCnt  = call_user_func( array($aOne['class'], $aOne['method']) );
                $bShow = !$bShow ? !empty($nCnt) : $bShow;
                
                if ( $bNumsOnly ) {
                    $aReturn[$nKey] = $nCnt;
                }
                else {
                    $aReturn[$nKey] = array( 'name' => $aOne['name'], 'link' => $aOne['link'], 'counter' => $nCnt );
                }
            }
        }
        
        return $aReturn;
    }
    
    
    
    /**
     * Отправить на модерирование запись какой-либо сущности
     * 
     * @param int $rec_id - ID записи
     * @param int $rec_type - ID в admin_contents - сущность для модерирования
     * @param array $data - тестовые данные для автоматической проверки на стоп-слова
     */
    static function sendToModeration($rec_id, $rec_type, $data = null)
    {
        $stop_words    = new stop_words();
        $nStopWordsCnt = $stop_words->calculate($data);
        
        $GLOBALS['DB']->insert('moderation',array(
            'rec_id' => $rec_id,
            'rec_type' => $rec_type,//ID в admin_contents - сущность для модерирования
            'stop_words_cnt' => $nStopWordsCnt
        ));
    }
    
}

