<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr.php';

/**
 * Класс для работы с СБР со стороны фрилансера. Функции доступны только фрилансеру.
 */
class sbr_frl extends sbr
{
    public $uid_col = 'frl_id';
    public $anti_uid_col = 'emp_id';


    public $anti_tbl = 'employer';
    public $upfx = 'frl_';
    public $apfx = 'emp_';
    public $uclass = 'freelancer';

    /**
     * Фрилансер соглашается с условиями СБР.
     * 
     * @param integer $version   версия СБР, которую он видит при отправке запроса. Если заказчик успеет изменить условия, сделка сразу попадет в "Измененные".
     * @return boolean   успешно?
     */
    function agree($version) {
        if($this->status != self::STATUS_NEW) {
            // !!! Если заказчик успел добавить этап, то нужно предупреждение спрева выдать? Или потом показать изменения.
            // !!! Сначала нужно сделать/отладить добавление/удаление/перемещение этапов.
            // То же и с изменениями внутри этапа (пока после согласия выдаем изменения)... 

            $this->error[$this->id]['canceled'] = true;  // заказчик успел отменить.
            return false;
        }

        if(!$this->_openXact(TRUE))
            return false;

        $sql = "UPDATE sbr SET status = " . self::STATUS_PROCESS . ", frl_version = ?i WHERE id = ?i";
        $sql = $this->db()->parse($sql, $version, $this->id);
        if(!($res = pg_query(self::connect(false), $sql))) {

            $this->_abortXact();
            return false;
        }

        // Нельзя в триггер, т.к. соглашаемся с определенной версией.
        foreach($this->stages as $num=>$stage) {
            if(!$stage->agreeChanges($stage->data['version'])) {
                $this->_abortXact();
                return false;
            }

        }
        
        $this->_commitXact();

        return true;
    }

    /**
     * Фрилансер отказывается от сделки. Сделка попадает в "Отклоненные".
     * 
     * @param integer $id   ид. сделки.
     * @param string $reason   причина отказа
     * @return boolean   успешно?
     */
    function refuse($reason) {
        $sql = "
          UPDATE sbr
             SET status = " . self::STATUS_REFUSED . ",
                 frl_refuse_reason = '{$reason}',
                 project_id = NULL
           WHERE id = {$this->id}
             AND frl_id = {$this->uid}
             AND reserved_id IS NULL -- !!!выдать ошибку.
        ";
        return $this->_eventQuery($sql, true, 1);

    }
    
    /**
     * Возвращает uid пользователей с которыми были сделки у текущего ($this->uid) пользователя
     * 
     * @return array  массив с uid партеров
     */
    function getPartersId() {
        global $DB;
        $sql = "
            SELECT 
                DISTINCT u.uid
            FROM 
                sbr 
            INNER JOIN
                freelancer u ON sbr.emp_id = u.uid
            WHERE 
                (frl_id = {$this->uid} AND emp_id IS NOT NULL)
        ";
        return $DB->col($sql);
    }

    
    /**
     * Возвращает котакты сбр для быстрочата
     * 
     * @return array    массив с результатами (поля таблицы users)
     */
    function getContacts() {
        global $DB;
        $sql = "
            SELECT
                u.*
            FROM (
                SELECT 
                    DISTINCT emp_id
                FROM 
                    sbr 
                WHERE 
                    frl_id = ?
                    AND emp_id IS NOT NULL
                    AND status <= ?
            ) s
            INNER JOIN
                employer u ON u.uid = s.emp_id AND u.is_banned = B'0'
        ";
        return $DB->rows($sql, $this->uid, sbr::STATUS_PROCESS);
    }

    
    
}

?>