<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/memBuff2.php");

//Префиксы для ключей
define('MEM_STORAGE_PREFIX', 'mem_storage-');
define('MEM_STORAGE_PAGES_SUFIX', '-pages');
define('MEM_STORAGE_INDEX_SUFIX', '-index');
define('MEM_STORAGE_PAGESIZE_SUFIX', '-page_size');

//Какой сервер мемкеша использовать NULL поумолчанию
define('MEM_STORAGE_SERVER_KEY', 'memcachedSessionServer');


/**
 * Класс для организации постраничного 
 * хранилища в памяти на базе мемкеша
 */
class MemStorage
{
    //Экземпляр обьекта для работы с мемкешом
    protected $_membuff = NULL;
    

    //Ключи хранилища
    protected $_base_key;
    protected $_data_pages_key;
    protected $_index_key;
    protected $_data_page_size_key;

    //Время жизни хранилища
    protected $_life_time           = 604800;//86400;
    
    //Всего страниц в памяти
    protected $_total_pages         = 0;
    //Текущая страница
    protected $_current_page        = 0;
    //Размер первой страницы
    protected $_page_size;
    //По сколько кусочков страниц выдергивать индексы для поиска
    protected $_search_index_chunk  = 10;

    
    
    
    public function __construct($key) 
    {
        //@todo: Приходится прибегать к такому подходу
        // чтобы безопасно подключить membuff к нужному
        // серверу мемкеша

        $tmp1 = NULL;
        $tmp2 = NULL;
        
        if(isset($GLOBALS[memBuff::SERVERS_VARKEY]))
        {
            $tmp1 = $GLOBALS[memBuff::SERVERS_VARKEY];
        }
        
        $GLOBALS[memBuff::SERVERS_VARKEY] = MEM_STORAGE_SERVER_KEY;
        
        if(isset($GLOBALS[MEM_STORAGE_SERVER_KEY]) && 
           !is_array($GLOBALS[MEM_STORAGE_SERVER_KEY]))
        {
            $tmp2 = $GLOBALS[MEM_STORAGE_SERVER_KEY];
            $GLOBALS[MEM_STORAGE_SERVER_KEY] = array($GLOBALS[MEM_STORAGE_SERVER_KEY]);
        }

        
        $this->_membuff = new memBuff();
        

        if($tmp1)
        {
            $GLOBALS[memBuff::SERVERS_VARKEY] = $tmp1;
        }
        else
        {
            unset($GLOBALS[memBuff::SERVERS_VARKEY]);
        }

        if($tmp2)
        {
            $GLOBALS[MEM_STORAGE_SERVER_KEY] = $tmp2;
        }
        
        
        
        
        
        
        $this->_base_key = MEM_STORAGE_PREFIX . $key;
        $this->_data_pages_key = $this->_base_key . MEM_STORAGE_PAGES_SUFIX;
        $this->_index_key = $this->_base_key . MEM_STORAGE_INDEX_SUFIX;
        $this->_data_page_size_key = $this->_base_key . MEM_STORAGE_PAGESIZE_SUFIX;
        
        //@todo: в массив опций?
        $this->_total_pages = (int)$this->_membuff->get($this->_data_pages_key);
        $this->_page_size = (int)$this->_membuff->get($this->_data_page_size_key);
    }

    
    
    /**
     * Добавить массив данных в хранилище
     * Ключи массива должны быть уникальными идентификаторами
     * 
     * @param array $data
     * @return bool
     */
    public function addData($data)
    {
        if(!$this->_total_pages && !$this->_page_size)
        {
            $this->_page_size = count($data);
            $this->_membuff->set($this->_data_page_size_key, $this->_page_size, $this->_life_time, $this->_base_key);
        }
        
        $page = $this->_total_pages + 1;
        
        $key = sprintf('%s-%s-%s', $this->_base_key, $this->_page_size, $page);
        $result = $this->_membuff->set($key, $data, $this->_life_time, $this->_base_key);
        
        if($result)
        {
            //@todo: можно заюзать инкремент/декремент
            $this->_total_pages = $page;
            $this->_membuff->set($this->_data_pages_key, $this->_total_pages, $this->_life_time, $this->_base_key);

            $index_key = sprintf('%s-%s-%s', $this->_index_key, $this->_page_size, $page);
            $this->_membuff->set($index_key, array_keys($data), $this->_life_time, $this->_base_key);
        }
        
        return $result;
    }
    
    
    /**
     * Пербор и выборка страниц из хранилища
     * @todo: можно организовать множественную gets выборку 
     * 
     * @return array
     */
    public function getData()
    {
        //Все страницы перебрали?
        if($this->_current_page >= $this->_total_pages) 
        {
            $this->_current_page = 0;
            return FALSE;
        }
        
        $this->_current_page++;
        
        $key = $this->getBaseKey($this->_current_page);
        $result = $this->_membuff->get($key);
        
        //Если вдруг данные по ключу страницы
        //пропали то возврящаем пустышку чтобы
        //while цикл перебора незавершался а продолжил
        //выборку пока не переберет все страницы
        if(!$result) $result = array();
        
        return $result;
    }
    
    
    
    

    
    /**
     * Существует ли в хранилище элемент с указанным
     * идентификатором если да то вернет номер страницы
     * 
     * @param int $id
     * @return int|boolean
     */
    public function isExistItem($id)
    {
        $index_keys = array();
        
        for($page = 1; $page <= $this->_total_pages; $page++) 
            $index_keys[sprintf('%s-%s-%s', $this->_index_key, $this->_page_size, $page)] = $page;
        
        $chunks = array_chunk(array_keys($index_keys), $this->_search_index_chunk);

        foreach($chunks as $chunk)
        {
            $results = $this->_membuff->gets($chunk);

            if(count($results))
                foreach($results as $key => $result)
                {
                    if(in_array($id, $result))
                        return $index_keys[$key]; 
                }
        }
        
        return FALSE;
    }


    
    
    
    /**
     * Удалить элемент из хранилища
     * 
     * @param int $id
     * @param int $page
     * @return boolean
     */
    public function deleteItem($id, $page = NULL)
    {
        if(!$page) {
            $page = $this->isExistItem($id);
            if(!$page) return FALSE;
        }
        
        $result = FALSE;
        $index_key = $this->getIndexKey($page);
        $index_data = $this->_membuff->get($index_key);
        
        if($index_data && $idx = array_search($id, $index_data))
        {
            $key = $this->getBaseKey($page);
            $data = $this->_membuff->get($key);
            
            if(isset($data[$id]))
            {
                unset($data[$id]);
                $result = $this->_membuff->set($key, $data, $this->_life_time, $this->_base_key);
                
                if($result)
                {
                    unset($index_data[$idx]);
                    $result = $this->_membuff->set($index_key, $index_data, $this->_life_time, $this->_base_key);
                }
            }
            
        }
        
        return $result;
    }

    



    /**
     * Добавить элемент к последней странице
     * или создать и добавить в новую страницу
     * 
     * @param type $id
     * @param type $item
     * @return boolean
     */
    public function insertItem($id, $item)
    {
        //@todo: можно вынести кол-во элементов на последней странице в отдельный параметр
        $index_key = $this->getIndexKey($this->_total_pages);
        $index_data = $this->_membuff->get($index_key);
        if(!$index_data) return FALSE;
        
        if(count($index_data) < $this->_page_size)
        {
            $prev_index_data = $index_data;
            array_push($index_data, $id);
            $result = $this->_membuff->set($index_key, $index_data, $this->_life_time, $this->_base_key);
            
            if($result)
            {
                $key = $this->getBaseKey($this->_total_pages);
                $data = $this->_membuff->get($key);
                
                if($data)
                {
                    $data[$id] = $item;
                    $result = $this->_membuff->set($key, $data, $this->_life_time, $this->_base_key);
                } 
                else 
                    $result = FALSE;
            }
            
            if(!$result)
            {
                $this->_membuff->set($index_key, $prev_index_data, $this->_life_time, $this->_base_key);
            }
        }
        else
        {
            $data = array($id => $item);
            $result = $this->addData($data);
        }
        
        return $result;
    }





    /**
     * Обновить элемент 
     * найденого на странице хранилища
     * 
     * @param type $uid
     * @param type $item
     * @param type $page
     * @return boolean
     */
    public function updateItem($id, $item, $page = NULL)
    {
        if(!$page) {
            $page = $this->isExistItem($id);
            if(!$page) return FALSE;
        }
        
        $key = $this->getBaseKey($page);
        $data = $this->_membuff->get($key);
        
        if(!$data || !isset($data[$id])) return FALSE;
        
        $data[$id] = $item;
        
        $result = $this->_membuff->set($key, $data, $this->_life_time, $this->_base_key);
        
        return $result;
    }

    
    
    /**
     * Выбрать один элемент 
     * из страницы хранилища
     * 
     * @param type $id
     * @param type $page
     * @return boolean
     */
    public function getItem($id, $page = NULL)
    {
        if(!$page) {
            $page = $this->isExistItem($id);
            if(!$page) return FALSE;
        }
        
        
        $key = $this->getBaseKey($page);
        $data = $this->_membuff->get($key);
        
        if(!$data || !isset($data[$id])) return FALSE;
        
        return $data[$id];
    }

    






    protected function getIndexKey($page)
    {
        return sprintf('%s-%s-%s', $this->_index_key, $this->_page_size, $page);
    }

    

    protected function getBaseKey($page)
    {
        return sprintf('%s-%s-%s', $this->_base_key, $this->_page_size, $page);
    }
    
    
    
    
    /**
     * Вернуть индексы на указанной странице
     * 
     * @param int $page
     * @return array|bool
     */
    protected function getIndexData($page)
    {
        $index_key = $this->getIndexKey($page);
        return $this->_membuff->get($index_key);
    }



    
    /**
     * Есть ли данные в хранилище
     * 
     * @return bool
     */
    public function isExistData()
    {
        return ($this->_total_pages > 0 && $this->_page_size > 0);
    }
    
    
    /**
     * Очистка хранилища
     * 
     * @return bool
     */
    public function clear()
    {
        $this->_total_pages = $this->_current_page = $this->_page_size = 0;
        return $this->_membuff->flushGroup($this->_base_key);
    }
    
    
    /**
     * Сбрасываем внутренний указатель 
     * текущей страницы
     */
    public function reset()
    {
        $this->_current_page = 0;
    }
    
    
    public function getDebugInfo()
    {
        return PHP_EOL . 
               "_total_pages {$this->_data_pages_key} = {$this->_total_pages}" . PHP_EOL .
               "_current_page = {$this->_current_page}" . PHP_EOL .
               "_page_size = {$this->_page_size}" . PHP_EOL . 
               "getServerList = " . print_r($this->_membuff->getServerList(),true) . PHP_EOL .
               "SERVER = " .  (defined('SERVER')?SERVER:'NONE') . PHP_EOL;
    }
    
    
    public function getMemBuff()
    {
        return $this->_membuff;
    }
    
}
