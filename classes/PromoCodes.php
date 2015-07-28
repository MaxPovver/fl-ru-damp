<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/template.php';
/**
 * Class PromoCodes
 * Работа с промо-кодами при покупке услуг
 *
 */
class PromoCodes {

    /**
     * Игнор функционала промокода
     */
    const IS_ACTIVE = false;
    
    /**
     * Тип услуги ПРО
     */
    const SERVICE_PRO = 10;

    /**
     * Тип услуги платные опции в проектах
     */
    const SERVICE_PROJECT = 15;

    /**
     * Тип услуги публикация конкурса
     */
    const SERVICE_CONTEST = 20;

    /**
     * Тип услуги публикация вакансии
     */
    const SERVICE_VACANCY = 25;

    /**
     * Тип услуги закрепление профиля
     */
    const SERVICE_FRLBIND = 30;

    /**
     * Тип услуги закрепление услуг
     */
    const SERVICE_TSERVICEBIND = 35;

    /**
     * Тип услуги карусель
     */
    const SERVICE_CARUSEL = 40;

    /**
     * Тип услуги автоответы
     */
    const SERVICE_AUTORESPONSE = 55;

    /**
     * Тип услуги рассылка по каталогу
     */
    const SERVICE_MASSSENDING = 60;
    
    const MESSAGE_ERROR_NOTFOUND = "Промо-код указан неверно, попробуйте еще раз.";
    const MESSAGE_ERROR_OUTOFDATE = "Сожалеем, но срок действия промо-кода истек.";
    CONST MESSAGE_ERROR_LIMIT = "Сожалеем, но действие промо-кода завершено - исчерпан лимит по количеству использований кода.";
    
    private $TABLE = "promo_codes";
    
    private $TABLE_SERVICES = "promo_codes_services";
    
    private $db;

    public function __construct()
    {
        global $DB;
        $this->db = $DB;
    }
    
    public function add($data, $services)
    {
        $data['count_used'] = 0;
        $id = $this->db->insert($this->TABLE, $data, 'id');
        
        foreach ($services as $service) {
            $this->db->insert($this->TABLE_SERVICES, array(
                'code_id' => $id,
                'service_id' => $service
            ));
        }
    }
    
    public function edit($id, $data, $services)
    {
        $this->db->update($this->TABLE, $data, 'id = ?i', $id);
        
        $this->db->query("DELETE FROM {$this->TABLE_SERVICES} WHERE code_id = ?i", $id);
        foreach ($services as $service) {
            $this->db->insert($this->TABLE_SERVICES, array(
                'code_id' => $id,
                'service_id' => $service
            ));
        }
    }
    
    public function delete($id)
    {
        $this->db->query("DELETE FROM {$this->TABLE} WHERE id = ?i", $id);
        $this->db->query("DELETE FROM {$this->TABLE_SERVICES} WHERE code_id = ?i", $id);
    }

    public function getList()
    {
        $codes = $this->db->rows("SELECT * FROM {$this->TABLE} ORDER BY code;");
        foreach ($codes as $key => $code) {
            $codes[$key]['services'] = $this->db->col("SELECT service_id FROM {$this->TABLE_SERVICES} WHERE code_id = ?i", $code['id']);
        }
        return $codes;
    }
    
    public function getById($id)
    {
        $code = $this->db->row("SELECT * FROM {$this->TABLE} WHERE id = ?i;", $id);
        $code['services'] = $this->db->col("SELECT service_id FROM {$this->TABLE_SERVICES} WHERE code_id = ?i", $code['id']);
        return $code;
    }

    /**
     * 
     * @param type $code
     * @param type $service_id
     */
    public function getByCode($code, $service_id)
    {
        $service_where = is_array($service_id) ? ' IN (?l)' : ' = ?i';
        $sql = "SELECT pc.id,
            (pc.date_start < NOW() AND pc.date_end > NOW())::boolean as up_to_date,
            pc.count, pc.count_used,
            pc.discount_percent, pc.discount_price
            FROM {$this->TABLE} pc
            INNER JOIN {$this->TABLE_SERVICES} pcs ON pc.id = pcs.code_id
            WHERE LOWER(pc.code)=LOWER(?) AND pcs.service_id {$service_where}
        ";
        $row = $this->db->row($sql, (string)$code, $service_id);
        if ($row) {
            $row['services'] = $this->db->col("SELECT service_id FROM {$this->TABLE_SERVICES} WHERE code_id = ?i", $row['id']);
        }
        return $row;
    }
    
    /**
     * Проверяет, существет ли хотя бы один код, 
     * доступный для услуги
     * @param type $service_id Код услуги (число или массив чисел)
     * @return boolean
     */
    private function isExistsForService($service_id)
    {
        if (!self::IS_ACTIVE) {
            return false;
        }
        
        $service_where = is_array($service_id) ? ' IN (?l)' : ' = ?i';
        $sql = "SELECT pc.id FROM {$this->TABLE} pc
            INNER JOIN {$this->TABLE_SERVICES} pcs ON pc.id = pcs.code_id
            WHERE 
                pc.date_start < NOW() AND pc.date_end > NOW()
                AND pc.count - pc.count_used > 0 AND pcs.service_id {$service_where}
        ";
        $id = $this->db->val($sql, $service_id);
        return $id > 0;
    }
    
    /**
     * Возвращает html-код для формы оплаты
     * @param type $service_id
     */
    public function render($service_id)
    {
        if ($this->isExistsForService($service_id)) {
            return Template::render(ABS_PATH . '/templates/quick_payment/promo_code.tpl.php', array(
                'service' => is_array($service_id) 
                    ? implode('|', $service_id) 
                    : $service_id
            ));
        } else {
            return "";
        }
    }
    
    public function check($code, $service_id)
    {
        $result = array(
            'success' => false,
            'message' => "",
            'discount_percent' => 0,
            'discount_price' => 0
        );
        $codeRow = $this->getByCode($code, $service_id);
        
        if (empty($codeRow)) {
            $result['message'] = self::MESSAGE_ERROR_NOTFOUND;
        } elseif ($codeRow['up_to_date'] !== 't') {
            $result['message'] = self::MESSAGE_ERROR_OUTOFDATE;
        } elseif ($codeRow['count'] <= $codeRow['count_used']) {
            $result['message'] = self::MESSAGE_ERROR_LIMIT;
        } else {
            $result['success'] = true;
            $result['discount_percent'] = (int)@$codeRow['discount_percent'];
            $result['discount_price'] = (int)@$codeRow['discount_price'];
            $result['services'] = @$codeRow['services'];
        }
        return $result;
    }
    
    /**
     * Возвращает скидку в рублях в зависимости от исходной цены
     * Проверки на доступность нет, т.к. действие выполняется уже 
     * после заказа, во время зачисления
     * @param type $price
     */
    public function getDiscount($id, $price)
    {
        if (is_array($id)) {
            $data = $id;
        } else {
            $sql = "SELECT discount_percent, discount_price FROM {$this->TABLE} WHERE id = ?i;";
            $data = $this->db->row($sql, (int)$id);
        }
        
        $discount = 0;
        
        if ($data['discount_price']) {
         
            $discount = $data['discount_price'] > $price ? $price : $data['discount_price'];
            
        } elseif ($data['discount_percent']) {
        
            $discount = $price * $data['discount_percent'] / 100;
        
        }        
        
        return $discount;
    }
    
    /**
     * Помечает код как использованный
     * @param type $id
     */
    public function markUsed($id)
    {
        $this->db->query("UPDATE {$this->TABLE} SET count_used = count_used + 1 WHERE id = ?i", (int)$id);
    }

}