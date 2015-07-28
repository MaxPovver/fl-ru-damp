<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/wizard/wizard.php';

/**
 * Класс для работы с определенным этапом мастера
 *  
 */
class step_wizard 
{
    /**
     * Статус завершение этапа мастера 
     */
    const STATUS_COMPLITED = 1;
    
    /**
     * Статус подтверждения этапа 
     * Если стоит этот статус то для перехода на следующий этап необходимо подтвертить прохождение шага
     * Например при регистрации подтверждением является активация аккаунта
     */
    const STATUS_CONFIRM   = 2;
    
    /**
     * Конструктор класса
     * 
     * @global object $DB Подключение к БД
     * @param integer $id  ИД шага
     */
    public function __construct($id = false) {
        global $DB;
        
        $this->_db = $DB;
        $this->_id = $id;
    }
    
    /**
     * Инициализация класса
     * 
     * @param integer $id ИД шага
     * @return object 
     */
    public function initInstance($id = false) {
        $this->_id = $id;
        return clone $this;
    }
    
    /**
     * Инициализация данных шага
     * 
     * @param mixed $content     Данные шага, если false, пробуем взять из таблицы
     */
    public function setContent($content = false) {
        if($content) {
            $this->data = $content;
        } else {
            $sql = "
                SELECT ws.*, wts.id as id_wiz_to_spec, wts.wizard_id, wts.step_id, wts.pos, wts.type_step, wa.status, wts.depend_pos, wa.reg_uid 
                FROM wizard_to_step wts 
                INNER JOIN wizard_step ws ON ws.id = wts.step_id 
                LEFT JOIN wizard_action wa ON wa.id_wizard_to_step = wts.id
                WHERE wts.id = ?i";
            $this->data = $this->_db->row($sql, $this->_id);
        }
    }
    
    /**
     * Вывод данных по конкретному шагу
     * 
     * @return string  
     */
    public function render() {
        return $this->name;
    }
    
    /**
     * Метод доступа к переменным шага
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
        }
    }
    
    /**
     * Проверка шага, активен он в данный момент или нет
     * 
     * @param integer $pos текущая позиция шага
     * @return boolean 
     */
    public function isActive($pos) {
        return ($pos == $this->parent->getPosition() );
    }
    
    /**
     * Проверка шага, был ли он завершен или нет
     * 
     * @return boolean 
     */
    public function isCompleted() {
        return ($this->status == self::STATUS_COMPLITED);
    }
    
    /**
     * Проверка шага, активен шаг для перехода или нет
     * 
     * @return boolean
     */
    public function isDisable() {
        // Если после прохождения шага, в него больше нельзя возвращатся
        if($this->isCompleted()) {
            $type_step = ($this->type_step == 'f');
        } else {
            $type_step = false;
        }
        // Если шаг зависит от какого-либо шага до него, он неактивен для нажатия.
        if($this->depend_pos) {
            $depend_step = ($this->parent->steps[$this->depend_pos]->status != self::STATUS_COMPLITED);
        } else {
            $depend_step = false;
        }
        
        return ($type_step || $depend_step);
    }
    
    /**
     * Записываем определенный статус шагу мастера
     * 
     * @param integer $status  Статус @see self::STATUS_*
     * @return boolean 
     */
    public function setStatusStep($status) {
        if(!$this->action_id) return false;
        return $this->_db->update("wizard_action", array("status" => $status, "reg_uid" => wizard::getUserIDReg()), "id = ?", $this->action_id);
    }
    
    public function setStatusStepAdmin($status, $uid, $action_id) {
        global $DB;
        return $DB->update("wizard_action", array("status" => $status), "reg_uid = ? AND id = ?", $uid, $action_id);
    }
    
    /**
     * Возвращаем Ид пользователя шага, необходимо для того чтобы сам класс был менее зависим от класса мастера
     * 
     * @return string
     */
    public function getWizardUserID() {
        if($this->parent instanceof wizard) {
            return $this->parent->getWizardUID();
        }
        
        return $_SESSION['WUID'];
    }
    
    /**
     * Чистим сессию используемую на этапе 
     */
    public function clearSessionStep() {}  
}

?>