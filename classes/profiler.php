<?php
/**
 * Профайлер БД, запись времени выполнения запросов
 * 
 * @see        search
 * @category   Free-lance.ru
 * @package    System
 */
class profiler
{
    /**
     * Начальное время.
     *
     * @var array
     */
    private $_start = array();

    /**
     * Конечное время.
     *
     * @var array 
     */
    private $_end = array();

    /**
     * Сохраняет время старта для операции с указанным ключем.
     *
     * @param string $key Специальный ключ для операции
     */
    function start($key) {
        //Считываем текущее время 
        $mtime = microtime(); 
        //Разделяем секунды и миллисекунды 
        $mtime = explode(" ",$mtime); 
        //Составляем одно число из секунд и миллисекунд 
        $mtime = $mtime[1] + $mtime[0]; 
        //Записываем стартовое время в переменную с указанным ключем.
        $this->_start[$key] = $mtime;
    }

    /**
     * Сохраняет время окончания для операции с указанным ключем.
     *
     * @param string $key Специальный ключ для операции
     */
    function stop($key) {
        //Считываем текущее время 
        $mtime = microtime(); 
        //Разделяем секунды и миллисекунды 
        $mtime = explode(" ",$mtime); 
        //Составляем одно число из секунд и миллисекунд 
        $mtime = $mtime[1] + $mtime[0]; 
        //Записываем время окончания в переменную с указанным ключем.
        $this->_end[$key] = $mtime;
    }

    /**
     * Очищает время старта и окончания для операции с указанным ключем.
     * Если ключ не указан - очищает все замеры.
     *
     * @param string $key Специальный ключ для операции
     */
    function clear($key = null) {
        if ($key === null) {
            $this->_start = array();
            $this->_end   = array();
        } else {
            if (is_array($this->_start) && (array_key_exists($key, $this->_start))) {
                unset($this->_start[$key]);
            }
            if (is_array($this->_end) && (array_key_exists($key, $this->_end))) {
                unset($this->_end[$key]);
            }
        }
    }

    /**
     * Возвращает время выполнения операции с указанным ключем.
     * Если ключ не указан - возвращает время выполнения всех операций.
     *
     * @param string $key Специальный ключ для операции
     * @return float время выполения или false, если операция не найдена или операций нет.
     */
    function get($key = null) {
        $result = false;
        if ($key === null) {
            if (is_array($this->_start) && (array_key_exists($key, $this->_start)) && is_array($this->_end) && (array_key_exists($key, $this->_end))) {
                $result = $this->_end[$key] - $this->_start[$key];
            }
        } else {
            if (is_array($this->_start)) {
                $result = 0;
                foreach ($this->_start as $key => $value) {
                    if (is_array($this->_end) && (array_key_exists($key, $this->_end))) {
                        $result += $this->_end[$key] - $this->_start[$key];
                    }
                }
            }
        }
        return $result;
    }
}