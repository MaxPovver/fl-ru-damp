<?php

/**
 * Class DocGenQueue
 * Работа с очередью генерации файлов
 *
 */
class DocGenQueue {
    
    /**
     * Количество обрабатываемых документов за раз
     */
    const LIST_LIMIT = 50;
    
    /**
     * Максимальное количество попыток генерации документа
     */
    const TRY_MAX = 10;
    
    /**
     * Название таблицы БД
     * @var string
     */
    private $TABLE = 'docgen';
    
    
    /**
     * Добавляет новый элемент в очередь
     * @param type $data Массив данных
     * @return boolean
     */
    public function addItem($data)
    {
        if (!$this->isValidData($data)) {
            return false;
        }

        $data['class_params'] = serialize($data['class_params']);
        $data['fields'] = serialize($data['fields']);

        return $this->db()->insert($this->TABLE, $data);

    }
    
    /**
     * Берет список записей и генерирует документы
     */
    public function cron()
    {
        $queue = $this->getList();
        
        require_once(ABS_PATH . '/classes/log.php');
        $log = new log('docgen_cron/' . SERVER . '-%d%m%Y.log', 'a', "%d.%m.%Y %H:%M:%S: ");
        
        foreach ($queue as $item) {
            try {
                $docGenClass = $this->getClass($item['class_name'], $item['class_params']);
                $docGenClass->setData(mb_unserialize($item['fields']));
                $docGenClass->setDocName($item['type'], $item['original_name']);
                $docGenClass->beforeGenerate();
                
                if ($docGenClass->isExcel($item['type'])) {
                    $ok = $docGenClass->generateExcel($item['type']);
                } else {
                    $ok = $docGenClass->generate($item['type']);
                }
                
                if ($ok) {
                    $this->removeItem($item['id']);
                } else {
                    $this->incrementTry($item['id']);
                }
            } catch(Exception $e) {
                $log->writeln(sprintf("id = %s: %s", $item['id'], iconv('CP1251','UTF-8',$e->getMessage())));
                $this->incrementTry($item['id']);
            }
        }
    }
    
    /**
     * Получает список из очереди
     * @return array
     */
    private function getList()
    {
        $sql = "SELECT * FROM {$this->TABLE} 
            WHERE try_count <= ?i AND is_locked = FALSE
            ORDER BY id LIMIT ?i";
        $list = $this->db()->rows($sql, self::TRY_MAX, self::LIST_LIMIT); 
        
        if (count($list) == 0) {
            return array();
        }
        
        $ids = array();
        foreach ($list as $item) {
            $ids[] = $item['id'];
        }
        $this->db()->update($this->TABLE, array('is_locked' => true), 'id IN (?l)', $ids);
        
        return $list;
    }
    
    /**
     * Создает и возвращает экземпляр класса
     * @param type $className
     * @param type $classParams
     * @return object
     */
    private function getClass($className, $classParams)
    {
        $parameters = mb_unserialize($classParams);
        
        require_once("{$className}.php");
        $reflectionClass = new ReflectionClass($className);
        
        $class = $reflectionClass->newInstanceArgs($parameters);
        
        return $class;
    }
    
    /**
     * Увеличивает счетчик попыток генерации документа на 1
     * @param type $id
     * @return boolean
     */
    public function incrementTry($id)
    {
        $sql = "UPDATE {$this->TABLE} 
            SET try_count = try_count + 1, 
            is_locked = FALSE 
            WHERE id = ?i";
        return $this->db()->query($sql, (int)$id);
    }
    
    public function clear($class_name, $src_id, $types)
    {
        if (!is_array($types) || !count($types)) {
            return false;
        }
        
        $sql = "DELETE FROM {$this->TABLE} WHERE 
            class_name = ? 
            AND src_id = ?i
            AND type IN (?l);";
        
        return $this->db()->query($sql, $class_name, $src_id, $types);
    }
    
    /**
     * Удаляет запись из очереди
     * @param type $id
     */
    private function removeItem($id)
    {
        $sql = "DELETE FROM {$this->TABLE} WHERE id = ?i";
        $this->db()->query($sql, (int)$id);
    }


    /**
     * Проверяет валидность данных перед сохранением
     * @param type $data
     * @return boolean
     */
    private function isValidData($data)
    {
        if (!isset($data['class_name']) || !isset($data['class_params']) 
                || !isset($data['type']) || !isset($data['src_id']) 
                || !isset($data['file_path']) || !isset($data['fields'])) {
            return false;
        }
        
        return true;
    }
    
    /**
     * @return DB
     */
    private function db()
    {
        return $GLOBALS['DB'];
    }
}
