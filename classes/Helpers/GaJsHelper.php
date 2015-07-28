<?php

/**
 * Класс обретка JS функция GA
 */

class GaJsHelper 
{
    protected static $instance;
   
    
    protected $_data = array();


    
    
    
    public function setTuCategories($group, $prof)
    {
        return $this->setCategories($group, $prof, 'dimension3');
    }
    
    
    public function setFrlCategories($group, $prof)
    {
        return $this->setCategories($group, $prof, 'dimension2');
    }


    /**
     * Данные для групп(ы) и специализации(ий)
     * 
     * @param type $group
     * @param type $prof
     * @return type
     */
    public function setCategories($group, $prof, $key)
    {
        if ($group) {
            $group = !is_array($group)?array($group):$group;
            $group = array_unique($group);
        }
        
        if ($prof) {
            $prof = !is_array($prof)?array($prof):$prof;
            $prof = array_unique($prof);
        }
        
        $value = $this->_prepareCategoryValue($group, $prof);
        $result = $this->gaSet($key, $value);

        return $result;        
    }
    


   /**
     * Данные категорий фильтра проектов
     * 
     * Пример входных данных:
     * Array ( [0] => Array ( [2] => 0 [3] => 0 [9] => 0 [44] => 0 ) )
     * Array ( [1] => Array ( [3] => 1 [172] => 1 [9] => 1 [37] => 1 [84] => 1 [36] => 1 ) )
     * Array ( [1] => Array ( [79] => 1 [157] => 1 [91] => 1 [183] => 1 [153] => 1 [17] => 1 [154] => 1 [11] => 1 ) )
     * 
     * @param type $categories
     * @return boolean
     */
    public function setProjectsFilterCategory($categories)
    {
        if (!$categories || empty($categories)) {
            return false;
        }
        
        $group = array();
        $prof = array();        
        
        if (isset($categories[0])) {
            $group = array_keys($categories[0]);
        }
        
        if (isset($categories[1])) {
            $prof = array_keys($categories[1]);
        }
        
        $value = $this->_prepareCategoryValue($group, $prof);
        $result = $this->gaSet('dimension2', $value);

        return $result;
    }


    /**
     * Данные категории текущего проекта
     * 
     * @param type $specs
     * @return boolean
     */
    public function setProjectCategory($specs)
    {
        if (!$specs || empty($specs)) {
            return false;
        }
        
        $group = array();
        $prof = array();
        
        foreach ($specs as $spec) {
            $group[] = $spec['category_id'];
            $prof[] = $spec['subcategory_id'];
        }

        $value = $this->_prepareCategoryValue($group, $prof);
        $result = $this->gaSet('dimension2', $value);

        return $result;
    }


    /**
     * Преобразование данных категории в строку
     * 
     * @param type $group
     * @param type $prof
     * @return boolean
     */
    protected function _prepareCategoryValue($group, $prof)
    {
        $values = array();
        
        $group = $this->_toArrayInt($group);
        if ($group) {
            $values[] = "[g" . implode('],[g', $group) . "]";
        }
        
        $prof = $this->_toArrayInt($prof);
        if ($prof) {
            $values[] = "[p" . implode('],[p', $prof) . "]";
        }
        
        if (empty($values)) {
            return false;
        }
        
        $result = implode(',', $values);
        return $result;
    }

    
    /**
     * Фильтр в массив положительных
     * 
     * @param type $input
     * @return boolean
     */
    protected function _toArrayInt($input)
    {
        if (!$input || empty($input)) {
            return false;
        }

        $result = array_filter($input, function($value){
            return preg_match('/^[0-9]+$/', $value) && $value > 0; 
        });
        
        return $result;
    }

    
    /**
     * Параметр GA 
     * ga('set', ...
     * 
     * @param type $key
     * @param type $value
     */
    public function gaSet($key, $value)
    {
        if (empty($value)) {
            return false;
        }
        
        $this->_data['set'][$key] = $value;
        return true;
    }

    
    /**
     * Сборка параметра GA в JS строку
     * 
     * @return type
     */
    public function renderGaSet()
    {
        $_js = '';
        
        if (isset($this->_data['set']) && 
            !empty($this->_data['set'])) {
            
            foreach ($this->_data['set'] as $key => $value) {
                $_js .= "ga('set','{$key}','{$value}');\n";
            }
        }
        
        return $_js;
    }


    
    /**
     * Вернуть JS строку со всемы вызовыми GA
     * 
     * @return type
     */
    public function render()
    {
        $_ga_js = $this->renderGaSet();
        return $_ga_js;
    }


    /**
    * Создаем синглтон
    * @return object
    */
    public static function getInstance() 
    {

        if (null === static::$instance) {
            static::$instance = new static;
        }

        return static::$instance;
    }
}