<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/stdf.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/messages.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/sbr_emp.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/sbr_frl.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php';

class QChat {

    /**
     * Префикс ключа в мемкеше для хранения данных о подключениях
     * 
     */
    const MEMBUFF_CONNECTS_KEY = 'QChatConn';
    /**
     * Префикс ключа в мемкеше для хранения данных о событиях
     * 
     */
    const MEMBUFF_EVENTS_KEY = 'QChatEvent';
    /**
     * Префикс ключа в мемкеше для хранения evid
     * 
     */
    const MEMBUFF_COUNTERS_KEY = 'QChatConter:';
    /**
     * Префикс ключа в мемкеше для хранения времени последнего обращения к событиям
     * 
     */
    const MEMBUFF_EVENTS_TIMER_KEY = 'QChatFunc';
    /**
     * Время жизни подключения, после которого оно считается "мертвым" и создается новое
     * 
     */
    const CONNECT_TTL = 60;
    /**
     * Через какое количество секунд нужно дропать соединение, чтобы клиент его переподключил
     * 
     */
    const SCRIPT_LIVE = 27;
    /**
     * Время в секундах от последнего события (время последнего сообщения для контактов в личке; 
     * время завершения сделки для сбр) когда контакт считается "активным" в родстере (списке контактов).
     * 
     */
    const ACTIVE_CONTACT = 2592000;
    /**
     * Количество последних сообщений, которые загружаются при открытии окна с диалогом
     * 
     */
    const HISTORY_COUNT = 4;
    
    
    /**
     * Данные текущего подключения. Массив состоит из
     * evid - integer
     *     номер последнего выполненного события в данном подключении
     * сid  - [a-zA-Z0-9]{8}
     *     уникальный id подключения в пространстве одного пользователя. необходимо, чтобы
     *     отличать несколько копий запущенных чатов (в разных вкладках/браузерах/хостах)
     * ckey - [a-z0-9]{32}
     *     уникальный ключ, который используется для того чтобы распозновать запущены ли
     *     чаты на разных компьютерах/браузерах или только в разных вкладках. В основном для того, 
     *     чтобы не спамить звуковыми уведомлениями если открыто несколько вкладок с чатом.
     *     Формируется следущим способом:
     *     ckey = md5(CLIENT_PART . IP . USER_AGENT), где CLIENT PART приходит от клиента:
     *     CLIENT_PART = screen.width + screen.height + screen.colorDepth (обычный плюс, не контенация строк)
     * uptime - unixtime
     *     время последнего обновления сессии
     * 
     * @var array
     */
    private $_connect = array();
    /**
     * uid текущего пользователя
     * 
     * @var integer
     */
    private $_uid;
    /**
     * Текущий cid (равен $this->_connect['cid'])
     * 
     * @var string
     */
    private $_cid;
    /**
     * Текущий ckey (равен $this->_connect['ckey'])
     * 
     * @var string
     */
    private $_ckey;
    /**
     * Экземпляет класс memBuff. Используется только для чтения из мемкеша.
     * 
     * @var memBuff
     */
    private $_memBuff;
    /**
     * Экземпляет класс Memcached
     * @see $this->_counter()
     * 
     * @var Memcached
     */
    private $_memcache;
    /**
     * Текущие настройки пользователя
     * chat  - вкл/выкл чат
     * sound - вкл/выкл звук
     * 
     * @var array
     */
    protected $_settings = array('chat'=>0, 'sound'=>1);
    /**
     * Данный вызов класса обращался к self::stream(). Требуется для учета количества
     * подкючений.
     * 
     * @var boolean
     */
    protected $_isStream = false;
    /**
     * Список событий инициируемых сервером для клиента, ключ массива - название события, значение
     * массив состоящий из
     * func  - имя метода в текущем классе, который нужно выполннить для проверки события
     * check - как часто, в секундах, делать проверку события
     * 
     * @var array
     */
    private $_sEvents = array(
        'income' => array('func' => '_seIncome', 'check' => 1)
    );
    /**
     * Список событий инициируемых клиентом для сервера, ключ массива - название события, значение
     * массив состоязи из
     * func - имя метода в текущем классе, которые нужно выполнить для обработки события
     * req  - список обязательных параметров для события @see $this->event()
     * 
     * @var type 
     */
    private $_cEvents = array(
        'send'     => array('func' => '_ceSend',     'req' => array('uid', 'text')),
        'contacts' => array('func' => '_ceContacts', 'req' => array('type')),
        'user'     => array('func' => '_ceUser',     'req' => array()),
        'history'  => array('func' => '_ceHistory',  'req' => array('uid')),
        'settings' => array('func' => '_ceSettings', 'req' => array()),
        'roster'   => array('func' => '_ceRoster',   'req' => array()),
        'contact'  => array('func' => '_ceContact',  'req' => array())
    );
    
    
    /**
     * Конструктор принимает три обязательных параметра и восстанавливает сессию
     * подключения (или создает новую, если ее нет)
     * 
     * @param  integer $uid    id пользователя
     * @param  string  $cid    id сессии
     * @param  string  $ckey   ключ сессии
     */
    public function __construct($uid, $cid, $ckey) {
        if ( !$uid || !$cid || !$ckey ) {
            return;
        }
        $this->_uid  = $uid;
        $this->_cid  = $cid;
        $this->_ckey = md5($ckey . getRemoteIP() . $_SERVER['HTTP_USER_AGENT']);
        
        $this->_memBuff = new memBuff;
        
        $this->_loadConnect();
        if ( empty($this->_connect) ) {
            $this->_connect = array(
                'evid'   => $this->_counter('evid'),
                'cid'    => $this->_cid,
                'ckey'   => $this->_ckey,
                'uptime' => time()
            );
        }
        $this->_settings = array(
            'chat'  => (int) $_SESSION['chat'],
            'sound' => (int) $_SESSION['chat_sound']
        );
    }
    
    
    /**
     * При уничтожении объекта, сохраняем данные сессии
     * 
     */
    public function __destruct() {
        $this->_saveConnect();
        if ( $this->_isStream ) {
            $this->_counter('connects', -1);
        }
    }
    
    
    /**
     * Чтение сессии из мемкеша
     * 
     */
    private function _loadConnect() {
        $connect = $this->_memBuff->get(self::MEMBUFF_CONNECTS_KEY . $this->_uid . ':' . $this->_cid);
        if ( empty($connect) || $connect['uptime'] + self::CONNECT_TTL < time() ) {
            return;
        }
        $this->_connect = $connect;
    }
    
    
    /**
     * Сохранение сессии в мемкеш.
     * Для сохранение используем свой экземляр memBuff для верности
     * 
     */
    private function _saveConnect() {
        $memBuff = new memBuff;
        $this->_connect['uptime'] = time();
        $memBuff->set(self::MEMBUFF_CONNECTS_KEY . $this->_uid . ':' . $this->_cid, $this->_connect, self::CONNECT_TTL);
    }
            
        
    /**
     * Получение текущего evid или инкремент текущего evid и получения нового.
     * Здесь необходимо подключаться к memcached напрямую без memBuff и всех его "обвесов", чтобы
     * делать инкремент штатными средствами. Если делать это с помощью set/get может возникнуть
     * ситуация когда два процесса получат одинковый evid, сделают инкремент и оба перезапишут
     * одинаковое значение, из-за этого все сломается :)
     * 
     * evid это счетчик событий. Когда создается новое событие, счетчик инкрементируется.
     * Каждое подключение знает какое последние событие оно выполнило и по evid ориентируется есть ли
     * новые.
     * 
     * @param  boolean $inc  если true -> инкрементирует evid и возвращает новое значение, иначе текущее
     * @return integer       новое или текущее значение evid
     */
    private function _counter($name, $inc=0) {
        if ( empty($this->_memcache) || $inc ) {
            $this->_memcache = new Memcached;
            $svk = $GLOBALS[memBuff::SERVERS_VARKEY];
            if( empty($GLOBALS[$svk]) ) {
                $svk = 'memcachedServers';
            }
            if ( !($servers = $GLOBALS[$svk]) ) {
                self::error(1, true);
            }
            foreach ( $servers as $s ) {
                $bIsConnected = $this->_memcache->addServer($s, 11211);
            }
            if ( !$bIsConnected ) {
                self::error(1, true);
            }
        }
        $key = self::MEMBUFF_COUNTERS_KEY . $this->_uid . ':' . $name . ( defined('SERVER')? SERVER: '' );
        if ( $inc ) {
            $v = $inc > 0? $this->_memcache->increment($key, $inc): $this->_memcache->decrement($key, $inc * -1);
        } else {
            $v = $this->_memcache->get($key);
        }
        if ( $v === false ) {
            $start = $name=='connects'? 1: 0;
            $this->_memcache->set($key, $start, ($name=='evid')? 0: self::CONNECT_TTL * 1.3 );
        }
        return (int) $v;
    }
    
    
    /**
     * Добавление нового события в буфер вывода
     * 
     * @param array $data    - массив с данными нового события
     */
    protected function _addEvent($data) {
        $event = array(
            'evid' => $evid,
            'data' => $data,
            'time' => time(),
            'cid'  => $this->_cid
        );
        $memBuff = new memBuff;
        $evid = $this->_counter('evid', 1);
        $memBuff->set(self::MEMBUFF_EVENTS_KEY . $this->_uid . ':' . $evid, $event, self::CONNECT_TTL);
    }
    
    
    /**
     * Послать событие клиенту
     * 
     * @param integer $evid  - id события которое нужно послать
     * @param boolean $one   - если false, то посылаются все события от $evid до текущего; если true - только $evid
     */
    protected function _sendEvent($evid, $one=false) {
        $events = array();
        $currEvid = $one? $evid: $this->_counter('evid');
        for ( $i=$evid; $i<=$currEvid; $i++ ) {
            $event = $this->_memBuff->get(self::MEMBUFF_EVENTS_KEY . $this->_uid . ':' . $i);
            $event['data']['cckey'] = $this->_counter('cckey:' . $i . ':' . $this->_ckey, 1);
            $event['data']['cid']   = $event['cid'];
            $events[] = $event['data'];
        }
        if ( $events ) {
            //flush();
            //ob_flush();
            echo json_encode($events);
            echo str_repeat(' ',1024 * 16);
            ob_end_flush();
            //echo str_repeat(' ',1024*64);
            //flush();
            //ob_flush();
            //echo ' ';
        }
    }
    
    
    /**
     * Послать клиенту пробел. Это необходимо делать постоянно по двум причинам.
     * Во-первых из-за особенности работы php, при отключении клиента скрипт не поймет
     * этого и будет висеть в процессах до тех пор пока не попытается что-то послать
     * http://www.php.net/manual/en/function.connection-status.php#43273
     * Во-вторых используется клиентам в качестве пинга, для определения статуса подключения
     * 
     */
    private function _sendByte() {
        //flush();
        //ob_flush();
        echo '*';
        //echo str_repeat(' ', 100);
        ob_end_flush();
        //flush();
        //ob_flush();
    }
    
    
    /**
     * Послать ошибку клиенту и, если необходимо, умреть
     * 
     * @param  integer $num  номер ошибки
     * @return boolean       если true - все выключаем, все сломалось :), если false - обычная ошибка
     */
    public static function error($num, $die=false) {
        // 1 - проблема с подключением мемкеша (fatal)
        // 2 - пользователь не авторизован / слетела авторизация (fatal)
        // 3 - этому пользователю нельзя отрпавлять сообщения
        // 4 - получатель забанен
        $text = '';
        switch ($num) {
            case 2: {
                $text = 'Авторизуйтесь, чтобы пользоваться быстрочатом.';
                break;
            }
            case 3: {
                $text = 'Этот пользователь запретил отправлять ему сообщения.';
                break;
            }
            case 4: {
                $text = 'Получатель заблокирован. Вы не можете отправлять ему личные сообщения.';
                break;
            }
        }
        if ( $die && empty($text) ) {
            $text = 'Произошла серверная ошибка. Пожалуйста, обновите страницу. Приносим извинения за неудобства.';
        }
        flush();
        ob_flush();
        echo json_encode(
            array(
                array(
                    'func' => 'error',
                    'attr' => array(
                        'num'  => $num,
                        'text' => iconv('CP1251', 'UTF8', $text),
                        'die'  => $die
                    )
                )
            )
        );
        flush();
        ob_flush();
        if ( $die ) {
            die;
        }
    }

    
    /**
     * Запускает "постоянное соединение" и в цикле начинает проверять новые серверные события и отсылать их
     * клиентам.
     * 
     */
    public function stream($type) {
        $timer = 0;
        $sConn = round(self::CONNECT_TTL * 0.7);
        $disconnect = false;
        $this->_isStream = true;
        // обязательно отключаем работу с сессией, т.к. скрипт работает длительное время
        // и он может переписать сделанные другими скриптами изменения на старые
        session_write_close();
        ob_implicit_flush(true);
        $this->_counter('connects', 1);
        while ( TRUE ) {
            // переодически сохраняем данные соединения
            if ( ++$timer % $sConn == 0 ) {
                $this->_saveConnect();
            }
            // проверяем новые события при необходимости
            foreach ( $this->_sEvents as $name => $event ) {
                $time = microtime(true);
                $key  = self::MEMBUFF_EVENTS_TIMER_KEY . $this->_uid . ':' . $name;
                $evTime  = (int) $this->_memBuff->get($key);
                if ( $evTime + $event['check'] < $time ) {
                    $this->_memBuff->set($key, $time, self::CONNECT_TTL);
                    call_user_func(array($this, $event['func']));
                }
            }
            // поспим
            sleep(1);
            // теперь можно все отдать клиенту
            $evid = $this->_counter('evid');
            if ( $this->_connect['evid'] < $evid ) {
                $this->_sendEvent( $this->_connect['evid'] + 1 );
                $this->_connect['evid'] = $evid;
                $this->_saveConnect();
                if ( $type != 'hold' ) {
                    $disconnect = true;
                }
            }
            if ( $disconnect || ($timer >= self::SCRIPT_LIVE) ) {
                break;
            }
            // и пробел для поддержания соеденения
            //$this->_sendByte();
        }
    }
    
    
    /**
     * Инициирует событие со стороны клиента для сервера
     * 
     * @param string $name  название события
     * @param array  $attr  параметы события
     */
    public function event($name, $attr) {
        if ( empty($this->_cEvents[$name]) ) {
            return;
        }
        if ( !empty($this->_cEvents[$name]['req']) ) {
            for ( $i=0; $i<count($this->_cEvents[$name]['req']); $i++ ) {
                $attrName = $this->_cEvents[$name]['req'][$i];
                if ( !isset($attr->$attrName) ) {
                    return;
                }
            }
        }
        $res = call_user_func(array($this, $this->_cEvents[$name]['func']), $attr);
        if ( !is_null($res) ) {
            echo json_encode($res);
        }
    }
    
    
    /**
     * Проверяет, включен ли чат в данные момент у пользователя (метод статический)
     * 
     * @param  integer  $uid  uid пользователя для проверки
     * @return boolean        true - чат в данный момент активен, false - нет
     */
    public static function active($uid) {
        if ( isset($this) ) {
            $memBuff = $this->_memBuff;
        } else {
            $memBuff = new memBuff;
        }
        $v = $memBuff->get(self::MEMBUFF_COUNTERS_KEY . $uid . ':connects');
        return (bool) $v;
    }
    
    //////////////////////////////////////////////////////////////////////////////////////////////////////
    // Методы событий ////////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////////////////////////
    
    
    /**
     * Проверяет наличие новых сообщений
     * 
     */
    protected function _seIncome() {
        $newmsg = messages::ChatNewMsgCount($this->_uid);
        if ( $newmsg ) {
            $rows = messages::ChatNewMessages($this->_uid, true);
            $stopWords = new stop_words( false );
            foreach ( $rows as $row ) {
                $message = $row['msg_text'];
                if ( $row['moderator_status'] === '0' ) {
                    $message = $stopWords->replace($message);
                }
                $message = reformat($message, 14, 0, 0, 1);
                $data = array(
                    'func' => 'income',
                    'attr' => array (
                        'id'    => $row['id'],
                        'uid'   => $row['uid'],
                        'text'  => iconv('CP1251', 'UTF-8', $message),
                        'files' => array(),
                        'date'  => dateFormat('Y-m-d H:i:s', $row['post_time']),
                    )
                );
                if ( $row['files'] ) {
                    foreach ( $row['files'] as $file ) {
                        if ( $row['uid'] == $this->_uid ) {
                            $login = $_SESSION['login'];
                        } else {
                            if ( empty($user) ) {
                                $user = new users;
                                $user->GetUserByUID($row['uid']);
                            }
                            $login = $user->login;
                        }
                        $data['files'][] = array(
                            'link'     => WDCPREFIX . '/users/' . $login . '/contacts/' . $file['fname'],
                            'filename' => $file['original_name'],
                        );
                    }
                }
                $this->_addEvent($data, array('ckeys'=>array()));
            }
        }
    }
    
    
    /**
     * Отправка сообщения
     * 
     * @param type $attr
     */
    function _ceSend($attr) {
        $user      = new users;
        $messages  = new messages;
        $stopWords = new stop_words( false );
        $user->getUserByUID(intval($attr->uid));
        if ( empty($user->login) || $user->login == 'admin' ) {
            self::error(3, false);
            return;
        }
        if ( $user->is_banned ) {
            self::error(4, false);
            return;
        }
        $text = iconv('UTF-8', 'CP1251', $attr->text);
        $text = antispam(change_q_x($text, FALSE, TRUE, null, false, false));
        $messages->Add($this->_uid, $user->login, addslashes($text), array(), 0, false, null, $id);
        if ( !is_pro() && !is_pro(true, $attr->uid) ) {
            if ( !hasPermissions('streamnomod') && !hasPermissions('streamnomod', $attr->uid) ) {
                $stopWords = new stop_words(false);
                $text = $stopWords->replace($text);
            }
        }
        $text = reformat($text, 14, 0, 0, 1);
        $time = date('Y-m-d H:i:s');
        $data = array(
            'func' => 'income',
            'attr' => array (
                'id'    => $id,
                'uid'   => $this->_uid,
                'cuid'  => $user->uid,
                'text'  => iconv('CP1251', 'UTF-8', $text),
                'files' => array(),
                'date'  => $time
            )
        );
        $this->_addEvent($data);
        return array(
            'id'    => $id,
            'uid'   => $user->uid,
            'text'  => iconv('CP1251', 'UTF-8', $text),
            'date'  => $time
        );
    }
    
    
    /**
     * Возвращает родстер (список контактов) пользователя
     * 
     * @param  stdClass $attr - список параметров
     *                          string  type  : all - вернуть всех пользователей, active - вернуть только "активных".
     *                                          Обязательные параметр. @see self::ACTIVE_CONTACT
     *                          integer online: 1 - возвращать только пользователей находящихся на сайте, 0 - всех
     * 
     * @return array          - массив с пользователями
     */
    protected function _ceContacts($attr) {
        global $session;
        $messages = new messages;
        if ( is_emp($this->_uid) ) {
            $sbr = new sbr_emp($this->_uid);
        } else {
            $sbr = new sbr_frl($this->_uid);
        }
        $rows = array_merge($messages->GetContacts($this->_uid), $sbr->getContacts());
        $uids = array();
        $res  = array();
        
        foreach ( $rows as $row ) {
            
            if ( in_array($row['uid'], $uids) || $row['login'] == 'admin' ) {
                continue;
            }
            $uids[] = $row['uid'];
            
            if ( $attr->type == 'active' && (isset($row['my_last_post']) || isset($row['his_last_post'])) ) {
                $last = max(strtotime($row['my_last_post']), strtotime($row['his_last_post']));
                if ( $last + self::ACTIVE_CONTACT < time() ) {
                    continue;
                }
            }
            
            $session->view_online_status($row['login']);
            $row['online'] = $session->is_active;
            if ( !empty($attr->online) && !$row['online'] ) {
                continue;
            }
                
            $res[] = array(
                'uid'    => $row['uid'],
                'name'   => $row['uname']? iconv('CP1251', 'UTF-8', $row['uname'].' '.$row['usurname']): $row['login'],
                'login'  => $row['login'],
                'online' => (int) $row['online'],
                'avatar' => $row['photo']? (WDCPREFIX . '/users/' . $row['login'] . '/foto/sm_' . $row['photo']): '',
                'emp'    => (int) is_emp($row['role']),
                'pro'    => (int) ($row['is_pro'] == 't')
            );
            
        }
        
        return $res;
    }
    
    
    /**
     * Возвращает информацию о пользователе
     * 
     * @param  stdClass $attr - список параметров
     *                          string uid: uid пользователя информацию о котором надо получить
     *                                      если не указать, то вернет данные о текущем пользователе
     * @return array          - массив с данными пользователя
     */
    protected function _ceUser($attr) {
        $uid  = empty($attr->uid)? $this->_uid: intval($attr->uid);
        $user = new users;
        $user->GetUserByUID($uid);
        if ( empty($user->login) ) {
            return array();
        }
        return array(
            'uid'    => $user->uid,
            'login'  => $user->login,
            'name'   => $user->uname? iconv('CP1251', 'UTF-8', $user->uname.' '.$user->usurname): $user->login,
            'avatar' => $user->photo? (WDCPREFIX . '/users/' . $user->login . '/foto/sm_' . $user->photo): '',
            'pro'    => (int) ($user->is_pro == 't'),
            'emp'    => (int) is_emp($user->role)
        );
    }

    
    /**
     * Возвращает несколько (@see self::HISTORY_COUNT) последних сообщений с контактом
     * 
     * @param  stdClass $attr - список параметров
     *                          mixed uid: uid пользователя или массив с uid'ами историю с которым нужно получить
     * @param  boolean        - если true - разрешено передавать в качестве uid массив, если false - только одного
     * @return array          - массив с историей
     */
    protected function _ceHistory($attr, $mmode=false) {
        $messages  = new messages;
        $stopWords = new stop_words(false);
        $res = array();
        $c   = 0;
        if ( $mmode ) {
            $uids    = $attr->uid;
            $oneUser = false;
            $maxid   = 0;
        } else {
            $uids    = intval($attr->uid);
            $oneUser = true;
            $maxid   = isset($attr->maxid)? intval($attr->maxid): 0;
        }
        $rows = $messages->GetHistory($this->_uid, $uids, self::HISTORY_COUNT, $maxid);
        if ( $rows ) {
            for ( $i=count($rows)-1; $i>=0; $i-- ) {
                $message = $rows[$i]['msg_text'];
                if ( $rows[$i]['moderator_status'] === '0' ) {
                    $message = $stopWords->replace($message);
                }
                $message = reformat($message, 14, 0, 0, 1);
                $res[$c] = array(
                    'id'       => $rows[$i]['id'],
                    'text'     => iconv('CP1251', 'UTF-8', $message),
                    'incoming' => ($rows[$i]['from_id'] == $this->_uid)? 0: 1,
                    'time'     => dateFormat('Y-m-d H:i:s', $rows[$i]['post_time']),
                    'files'    => array()
                );
                if ( !$oneUser ) {
                    $res[$c]['cuid'] = ($rows[$i]['to_id'] == $this->_uid)? $rows[$i]['from_id']: $rows[$i]['to_id'];
                }
                if ( $rows[$i]['files'] ) {
                    foreach ( $rows[$i]['files'] as $file ) {
                        if ( preg_match('/^users\/[-_a-z0-9]{2}\/([-_a-z0-9]+)/i', $file['path'], $o) ) {
                            $res[$c]['files'][] = array(
                                'link'     => WDCPREFIX . '/users/' . $o[1] . '/contacts/' . $file['fname'],
                                'filename' => $file['original_name']
                            );
                        }
                    }
                }
                $c++;
            }
        }
        return $res;
    }
    
    
    /**
     * Обновляет настройки чата
     * @param  stdClass $attr - список параметров
     *                          integer chat:  включить/выключить чат
     *                          integer sound: включить/выключить звук
     * @return array          - массив с текущими настройками
     */
    protected function _ceSettings($attr) {
        $settings = array();
        if ( isset($attr->chat) ) {
            $_SESSION['chat'] = $this->_settings['chat'] = $settings['chat'] = (int) (bool) $attr->chat;
        }
        if ( isset($attr->sound) ) {
            $_SESSION['chat_sound'] = $this->_settings['sound'] = $settings['chat_sound'] = (int) (bool) $attr->sound;
        }
        if ( $settings ) {
            $users = new users;
            $users->updateSettings($this->_uid, $settings);
        }
        return array(
            'chat'  => $this->_settings['chat'],
            'sound' => $this->_settings['sound'],
        );
    }
    
    
    /**
     * Возвращает ростер быстрочата (контакты + историю)
     * 
     * @param  stdClass $attr - список параметров
     *                          string mode: all - все пользователи из контакт листа
     *                                       inc - указанные в uids пользователи (должны быть в контакт листе)
     *                                       exc - всех пользователей из контакт листа, кроме указанных в uids
     *                          string uids: список uid пользователей разделенные запятыми (для режимов inc и exc)
     *                          string type: all - вернуть всех пользователей, active - вернуть только "активных".
     *                                          Обязательные параметр. @see self::ACTIVE_CONTACT
     *                          integer online: 1 - возвращать только пользователей находящихся на сайте, 0 - всех
     * @return array          - массив с пользователями
     */
    protected function _ceRoster($attr) {
        // проверяем входные данные
        $mode = '';
        $uids = array();
        if ( !isset($attr->mode) || !($attr->mode == 'inc' || $attr->mode == 'exc') ) {
            $mode = 'all';
        } else {
            $mode = $attr->mode;
            if ( isset($attr->uids) && is_string($attr->uids) ) {
                $rows = explode(',', $attr->uids);
                for ( $i=0; $i<count($rows) && $i<1000; $i++ ) {
                    $uid = intval(trim($rows[$i]));
                    if ( $uid > 0 ) {
                        $uids[] = $uid;
                    }
                }
            }
            if ( empty($uids) ) {
                $mode = 'all';
            }
        }
        // получаем контакты и отбрасываем лишнее
        $rows  = $this->_ceContacts($attr);
        $users = array();
        $links = array();
        $i = 0;
        $attr->uid = array();
        foreach ( $rows as $key=>$row ) {
            switch ( $mode ) {
                case 'inc': {
                    if ( in_array($row['uid'], $uids) ) {
                        $users[$i] = $row;
                        $attr->uid[] = $row['uid'];
                        $links[$row['uid']] = $i++;
                    }
                    break;
                }
                case 'exc': {
                    if ( !in_array($row['uid'], $uids) ) {
                        $users[$i] = $row;
                        $attr->uid[] = $row['uid'];
                        $links[$row['uid']] = $i++;
                    }
                    break;
                }
                default: {
                    $users[$i] = $row;
                    $attr->uid[] = $row['uid'];
                    $links[$row['uid']] = $i++;
                }
            }
        }
        // добавляем историю сообщений
        $rows = $this->_ceHistory($attr, true);
        foreach ( $rows as $row ) {
            $i = $links[$row['cuid']];
            if ( empty($users[$i]['history']) ) {
                $users[$i]['history'] = array();
            }
            unset($row['cuid']);
            $users[$i]['history'][] = $row;
        }
        return $users;
    }
    
    
    /**
     * Возвращает данные пользователя + его историю (_ceUser + _ceHistory)
     * Необходимо, чтобы уменьшить количество запросов, т.к. обычно сразу нужны данные
     * из обоих методов
     * 
     * @param  stdClass $attr - список параметров
     *                          string uid: uid пользователя информацию о котором надо получить
     *                                      если не указать, то вернет данные о текущем пользователе (без истории)
     * @return array          - массив с данными пользователя
     */
    protected function _ceContact($attr) {
        $user = $this->_ceUser($attr);
        if ( !empty($user['uid']) && !empty($attr->uid) && (intval($attr->uid) != $this->_uid) ) {
            $user['history'] = $this->_ceHistory($attr);
        }
        return $user;
    }
    
}
