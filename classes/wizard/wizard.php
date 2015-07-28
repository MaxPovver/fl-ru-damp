<?php 

/**
 * Класс для работы в мастерами, каркас
 * 
 * Работает через следующие таблицы 
 * 
 * wizard           - Таблица мастеров, где ид таблицы = self::$_id
 * wizard_step      - Таблица шагов (не зависимы от мастера, каждому мастеру можно присвоить одинаковые шаги, с разной обработкой)
 * wizard_to_step   - Таблица связывания мастера и шагов, с выставлением определенных настроек по их отображению в конкретном мастере
 * wizard_action    - Таблица действия пользователя с мастером и шагами. Присвоение статуса - пройден определенный шаг.
 *  
 * Данные по пользователю работают через куки (текущий шаг, уникальный ИД пользователя запустившего мастера) - 
 * Для каждого мастера будут генерироватся свои куки в формате имени "{name_cookie}{self::$_id}"
 * @todo Необходимо предусмотреть ситуацию когда куки отключены, тогда можно будет сохранять промежуточные данные в сессию
 * 
 * Конкретному мастеру присвают определенный класс для обработки шагов, по умолчанию это класс step_wizard, но можно переопределить свой класс
 * который должен наследовать функции и методы класса step_wizard
 * 
 * @example
 * 
 * $wizard = new wizard(1, new MY_step_wizard()); -- класс готов к использованию 
 * 
 */
class wizard 
{
    /**
     * Максимальные размер вложения файла
     *
     */
	const MAX_FILE_SIZE     = 5242880;
    
    /**
     * Максимальное количество файлов
     *
     */
    const MAX_FILE_COUNT    = 10;
    
    /**
     * Папка для файлов 
     */
    const FILE_DIR = "wizard/";
    
    /**
     * Максимальное время жизни данных по мастеру пользователей
     */
    const LIFE_TIME_ACTION = '1 month';
    
    /**
     * Уникальный ИД мастера
     * 
     * @var integer 
     */
    protected $_id = 0;
    
    /**
     * Подключение к БД
     * 
     * @var object
     */
    public $_db;
    
    /**
     * Название кук используемых системой
     * 
     * @var array
     */
    protected $_cookie_names = array(
        "uid"           => "W_UID",
        "step"          => "W_STEP",
        "role"          => "W_ROLE",
        "categories"    => "your_categories",
        "subcategories" => "your_subcategories",
        "visit"         => "visited_wizard"
    );
    
    /**
     * ИД пользователя Мастера
     * 
     * @var string 
     */
    protected $_uid = "";
    
    /**
     * Идентификатор текущего шага
     * 
     * @var integer 
     */
    protected $_step = 0;
    
    /**
     * Содержит последний ИД события записанного в базу
     * 
     * @var integer 
     */
    public $_action = 0;
    
    /**
     * Завершить шаг с записью в таблицу или нет
     * 
     * @var boolean 
     */
    protected $_complete_step = false;
    
    /**
     * Информация по шагам мастера
     * 
     * @var array 
     */
    public $steps = array();
    
    /**
     * Конструктор класса
     * 
     * @global object $DB
     * @param integer $id       ИД мастера
     * @param object $obj_step  Класс обработки шагов пол умолчанию @see step_wizard;    
     */
    public function __construct($id = false, $obj_step = false) {
        global $DB;
        $this->_db = $DB;
        
        $this->init($id, $obj_step);
    }
    
    /**
     * Инициализация данных для работы с мастером
     * 
     * @param type $id                  ИД мастера
     * @param step_wizard $obj_step     Класс обработки шагов пол умолчанию @see step_wizard;    
     * @return boolean false если ID не задан
     */
    public function init($id = false, $obj_step = false) {
        if(!$id) return false;
        
        if($id) {
            $this->_id = $id;
            $this->setInitWizard();
        }
        
        if($obj_step instanceof step_wizard) {
            $this->obj_step = $obj_step;
        } else {
            $this->obj_step = new step_wizard();
        }
        
        $this->_cookie_names['uid']  = "W_UID{$this->_id}";
        $this->_cookie_names['step'] = "W_STEP{$this->_id}";
        
        $this->setInitUser();
        $this->setInitSteps();
    }
    
    /**
     * Возвращает имя куки по его ключу @see self::_cookie_names
     * 
     * @param string $key ключ куки
     * @return boolean  Если такой куки не существует возвращает false
     */
    public function getCookieName($key) {
        if(isset($this->_cookie_names[$key])) {
            return $this->_cookie_names[$key];
        }
        return false;
    }
    
    public function getWizardUID() {
        return $this->_uid;
    }
    
    /**
     * Проверка доступа к мастеру
     * 
     * @return boolean 
     */
    public function isAccess() {
        if(empty($this->data)) {
            $this->setInitWizard();
        }
        
        switch($this->access_type) {
            // Всем пользователям
            case 0:
                return true;
                break;
            // Только НЕ зарегистрированным пользователям + пользователем которые зарегистрировались через мастер
            case 1:
                return (!get_uid(false) || $this->checkUserIDReg());
                break;
            // Только зарегистрированным пользователям
            case 2:
                $reg = $this->checkUserIDReg();
                return $reg;
                break;
            default:
                return false;
                break;
        }
    }
    
    /**
     * Инициализация данных мастера 
     */
    public function setInitWizard() {
        $sql = "SELECT * FROM wizard WHERE id = ?i";
        $this->data = $this->_db->row($sql, $this->_id);
    }
    
    /**
     * Инициализация данных пользователя запустившего мастера 
     * 
     * @todo предусмотреть использование мастера для зарегистрированных пользователей
     */
    public function setInitUser() {
        if (!isset($_COOKIE[$this->_cookie_names['uid']])) {
            $this->_uid = $this->_generateWizardUserID();
            setcookie($this->_cookie_names['uid'], $this->_uid, $this->_lifeTimeCookie(), '/', $GLOBALS['domain4cookie']);
            setcookie($this->_cookie_names['visit'], 1, $this->_lifeTimeCookie(), '/', $GLOBALS['domain4cookie']);
            $_COOKIE[$this->_cookie_names['uid']] = $this->_uid;
        } else {
            $this->_uid = __paramValue('string', $_COOKIE[$this->_cookie_names['uid']]);
        }
        $_SESSION['WUID'] = $this->_uid;
    }
    
    /**
     * Генерация уникального ИД пользователя запустившего мастера
     * @return type 
     */
    protected function _generateWizardUserID() {
        return substr(md5(microtime() + $_SERVER['HTTP_USER_AGENT'] + getRemoteIP()), 0, 10);
    }
    /**
     * Время жизни куков
     * 
     * @return timestamp
     */
    protected function _lifeTimeCookie() {
        return time() + 3600 * 24 * 180;
    }
    
    /**
     * Проверяем связку зарегистрированного пользователя с мастером
     * 
     * @return type 
     */
    public function checkUserIDReg() {
        $sql = "SELECT 1 FROM wizard_action WHERE reg_uid = ? AND wiz_uid = ?";
        return $this->_db->val($sql, $this->getUserIDReg(), step_wizard::getWizardUserID());
    }
    
    /**
     * Возвращаем ИД зарегистрированного пользователя
     * @return type 
     */
    public function getUserIDReg() {
        return $_SESSION['uid'] ? $_SESSION['uid'] : $_SESSION['RUID'];
    }
    
    /**
     * Инициализация шагов мастера 
     */
    public function setInitSteps() {
        $sql = "SELECT ws.*, wts.id as id_wiz_to_spec, wts.wizard_id, wts.step_id, wts.pos, wts.type_step, wa.status, wts.depend_pos, wa.reg_uid, wa.id as action_id 
                FROM wizard_to_step wts 
                INNER JOIN wizard_step ws ON ws.id = wts.step_id 
                LEFT JOIN wizard_action wa ON wa.id_wizard_to_step = wts.id AND wiz_uid = ?
                WHERE wts.wizard_id = ?i
                ORDER BY pos ASC"; 
        
        $steps = $this->_db->rows($sql, $this->_uid, $this->_id);
        if($steps) {
            foreach($steps  as $k=>$step) {
                if($step['reg_uid']) {
                    $this->reg_uid = $step['reg_uid'];
                    $_SESSION['RUID'] = $step['reg_uid'];
                }
                $wstep = $this->obj_step->initInstance($step['id_wiz_to_spec']);//new step_wizard($step['id_wiz_to_spec']);
                $wstep->setContent($step);
                $wstep->parent = $this;
                $this->steps[$step['pos']] = $wstep;
            }
        }
        $this->setLastStep();
    }
    
    /**
     * Берем ИД действие пользователя по активному шагу в мастере
     * 
     * @param integer $id ИД шага
     * @return integer 
     */
    public function getAction($id = false) {
        if(!$id) {
            $id = $this->steps[$this->_step]->id_wiz_to_spec;
        }
        $sql = "SELECT id FROM wizard_action WHERE id_wizard_to_step = ?i AND wiz_uid = ?";
        $res =  $this->_db->val($sql, $id, $this->_uid);
        $this->_action = $res;
        return $res;
    }
    
    /**
     * Проверка последнего действия
     * 
     * @param int $pos Позиция мастера по отношению к шагам 
     * @return boolean 
     */
    public function checkAction($pos) {
        if(!$this->_action) {
            $res = $this->getAction();
        } else {
            $res = $this->_action;
        }
        return (!$res  && $pos != $this->_step && $this->isCompliteStep());
    }
    
    /**
     * Сохранение действия пользователя по шагу (например действие пройденный шаг)
     * 
     * @param object  $step      Шаг пользователя @see step_wizard();
     * @param integer $status    Статус
     * @return integer ИД созданного действия
     */
    public function saveActionWizard($step, $status = 1) {
        $data = array(
            "id_wizard_to_step" => $step->id_wiz_to_spec,
            "wiz_uid"           => $this->_uid,
            "reg_uid"           => $this->getUserIDReg(),
            "status"            => $status
        );
        
        return $this->_db->insert("wizard_action", $data, "id");
    }
    
    /**
     * Переход на следующий шаг мастера
     * 
     * @param integer $pos     Позиция шага мастера
     */
    public function setNextStep($pos) {
        if($this->isStep($pos)) {
            // Если при переходе на следующий шаг присвоен статус завершения текущего шага, пишем информацию в БД
            $this->saveCheckStep($pos);
            setcookie($this->_cookie_names['step'], $pos, $this->_lifeTimeCookie(), '/', $GLOBALS['domain4cookie']);
            $_COOKIE[$this->_cookie_names['step']] = $pos;
            $this->steps[$this->_step]->clearSessionStep();
            $this->setLastStep();
        }
    }
    
    /**
     * Проверка пройденности этапа и запись информации про прохождению
     * 
     * @param integer $pos Шаг мастера
     */
    public function saveCheckStep($pos) {
        if($this->checkAction($pos)) {
            $this->_action = $this->saveActionWizard($this->steps[$this->_step]);
            $this->steps[$this->_step]->setContent();
        }
    }
    
    /**
     * Проверка шага на доступность при переходе на него
     * 
     * @param integer $pos     Позиция шага мастера
     * @return boolean 
     */
    public function isStep($pos) {
        return ((isset($this->steps[$pos]) && !$this->steps[$pos]->isDisable()) || $this->isCompliteStep()) ;
    }
    
    /**
     *  Задаем текущий активный шаг 
     */
    public function setLastStep() {
        if (!isset($_COOKIE[$this->_cookie_names['step']])) {
            $this->_step = current(array_keys($this->steps));
            setcookie($this->_cookie_names['step'], $this->_step, $this->_lifeTimeCookie(), '/', $GLOBALS['domain4cookie']);
        } else {
            $this->_step = __paramValue('int', $_COOKIE[$this->_cookie_names['step']]);
        }
    }
    
    /**
     * Возвращаем текущий активный шаг
     * 
     * @return object @see new step_wizard(); 
     */
    public function getLastStep() {
        return $this->steps[$this->_step];
    }
    
    /**
     * Возвращаем текущую позицию шага мастера
     * 
     * @return integer
     */
    public function getPosition() {
        return $this->_step;
    }
    
    /**
     * Задаем активному шагу разрешение|запрет на запись статуса, шаг завершен
     * 
     * @param boolean $action 
     */
    public function setCompliteStep($action = false) {
        $this->_complete_step = $action;
    }
    
    /**
     * Проверка на разрешение|запрет на запись статуса шагу
     * 
     * @return boolean
     */
    public function isCompliteStep() {
        return $this->_complete_step;
    }
    
    /**
     * Метод доступа к переменным мастера 
     * 
     * @param string $name    Имя переменной
     * @return mixed Данные переменной 
     */
    public function __get($name) {
        if(!is_array($this->data)) return null;
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        } else {
            return null;
            //trigger_error("Variable '{$name}',  not found", E_USER_NOTICE);  
        }
    }
    
    /**
     * Очищаем устаревшие данные , устаревшими будем считать данные которые лежат уже месяц
     */
    public function cleaningOldData() {
        $sql = "SELECT DISTINCT wiz_uid FROM wizard_action WHERE date_complite <= NOW() - '".self::LIFE_TIME_ACTION."'";
        $uid = $this->_db->col($sql);
        $sql = "DELETE FROM wizard_action WHERE wiz_uid IN (?l);";
        return $this->_db->query($sql, $uid);
    }
    
    /**
     * Чистим все куки по мастеру 
     */
    public function clearCookies() {
        foreach ($this->_cookie_names as $cookie) {
            unset($_COOKIE[$cookie]);
            setcookie($cookie, null, time(), '/', $GLOBALS['domain4cookie']);
        }
    }
    
    /**
     * Чистим все куки по мастерам
     */
    public function clearCookiesById($id) {
        foreach ($this->_cookie_names as $cookie) {
            unset($_COOKIE[$cookie . $id]);
            setcookie($cookie . $id, null, time(), '/', $GLOBALS['domain4cookie']);
        }
    }
    
    /**
     * При выходе чистим базу
     * @return type 
     */
    public function clearActions($uid = false) {
        if(!$uid) $uid = $this->_uid;
        $sql = "DELETE FROM wizard_action WHERE wiz_uid = ?";
        return $this->_db->query($sql, $uid);
    }
    
    /**
     * При выходе чистим сессии 
     */
    public function clearSession() {
        unset($_SESSION['WUID'], $_SESSION['RUID'], $_SESSION['view_wizard_project']);
    }
    
    /**
     * Выход из мастера 
     * 
     * @param boolean redirect Включено перенаправление на главную или нет
     */
    public function exitWizard($redirect = true) {
        $this->clearCookies();
        $this->clearActions();
        $this->clearSession();
        if($redirect) header("Location: /"); // Выходим из мастера
    }
    
    /**
     * Запись добавленных файлов в БД 
     * 
     * @global object $DB     Подключение к БД
     * @param integer $attach_id   ИД файла
     * @param integer $id          Ид рассылки
     */
    public function insertAttachedFile($attach_id, $id, $type = 1) {
        $update = array('src_id' => (int) $id, 'type' => (int) $type);
        $this->_db->update("file_wizard", $update, "fname = ?", $attach_id); 
    }
    
    /**
     * Добавление/удаление файлов 
     * 
     * @param array   $files   Список файлов
     * @param integer $id      Ид рассылки
     */
    public function addAttachedFiles($files, $id, $type = 1) {
        if($files) {
            foreach($files as $file) {
                switch($file['status']) {
                    case 4:
                        // Удаляем файл
                        $this->delAttach($file['id']);   
                        break;
                    case 1:
                        // Добавляем файл
                        $cFile = new CFile($file['id']);
                        $cFile->table = 'file_wizard';
                        $ext = $cFile->getext();
                        $tmp_name = $cFile->secure_tmpname(self::FILE_DIR, '.'.$ext);
                        $tmp_name = substr_replace($tmp_name,"",0,strlen(self::FILE_DIR));
                        $cFile->_remoteCopy(self::FILE_DIR.$tmp_name, true);
                        $this->insertAttachedFile($cFile->name, $id, $type);
                        break;
                }
            }
        }
    }
    
    /**
     * удаляет файл по ID
     */
    public function delAttach ($file_id) {
        $cFile = new CFile($file_id);
        $cFile->Delete($file_id);
    }
    
    /**
     * Биндим идишник зарегистрированного пользователя к его ИД мастера 
     * 
     * @param integer $uid ИД зарегистрированного пользователя
     */
    public function bindUserIDReg($ruid, $wuid = false) {
        if(!$wuid) $wuid = $this->_uid;
        $update = array("reg_uid" => $ruid);
        return $this->_db->update("wizard_action", $update, "wiz_uid = ?", $wuid);
    }
    
    /**
     * Берем дополнительные данные по мастеру
     * 
     * @param string $wiz_uid  Ид мастера пользователя
     * @return type 
     */
    public function getFieldsUser($wiz_uid = false) {
        if(!$wiz_uid) $wiz_uid = $this->_uid;
        
        $sql = "SELECT * FROM wizard_fields WHERE wiz_uid = ?";
        $rows = $this->_db->rows($sql, $wiz_uid);
        
        if($rows) {
            foreach($rows as $key=>$value) {
                $result[$value['field_name']] = $value['field_value'];
            }

            return $result;
        }
        
        return false;
    }
    
    /**
     * Записываем дополнительные данные по мастеру
     * 
     * @param array $data
     * @return type 
     */
    public function saveFieldsInfo($data) {
        if(!$data) return false;
        $this->clearFieldsInfo(array_keys($data));
        
        $sql = "INSERT INTO wizard_fields (field_name, field_value, field_type, wiz_uid) VALUES ";
        foreach($data as $key=>$value) {
            $insert[] = " ('{$key}', '{$value}', '".gettype($value)."', '".step_wizard::getWizardUserID()."') ";
        }
        
        $sql .= implode(", ", $insert);
        
        return $this->_db->query($sql);
    }
    
    /**
     * Удаляем дополнительные данные по мастеру
     * 
     * @param array  $fields      Поля для удаления
     * @param string $wiz_uid     Ид пользователя мастера
     * @return type 
     */
    function clearFieldsInfo($fields, $wiz_uid = false) {
        if(!$wiz_uid) $wiz_uid = step_wizard::getWizardUserID();
        $sql    = "DELETE FROM wizard_fields WHERE wiz_uid = ? AND field_name IN (?l)";
        return $this->_db->query($sql, $wiz_uid, $fields);
    }
    
    function isUserWizard($uid, $step, $wizard) {
        global $DB;
        $sql = "SELECT wa.wiz_uid, wa.id 
                FROM wizard_action wa
                INNER JOIN wizard_to_step wts ON wts.pos = ?i AND wizard_id = ?i
                WHERE wa.reg_uid = ?i AND wa.id_wizard_to_step = wts.id";
        return $DB->row($sql, $step, $wizard, $uid);
    }
}

?>