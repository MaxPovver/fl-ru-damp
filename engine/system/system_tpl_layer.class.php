<?php
/**
 * Класс для обработки в выполнения шаблонов
 */
class system_tpl_layer {
	/**
	 * Адрес шаблонов
	 * @var string
	 */
	private $templates_dir = "";
	/**
	 * Адрес для откомпилированных файлов
	 * @var string
	 */  
	private $cache_dir = "";
	/**
	 * Принудительно компилировать каждый раз
	 * @var bool
	 */  
	private $force_compile = 0; 
    private $template_data = false;
	/**
	 * Компилировать каждые - секунд
	 * @var integer
	 */   
	private $cache_time = 20; 
	private $ti = false; 
	/**
	 * Конструктор
	 * @param object $template [optional] Адрес до шаблона
	 * @return 
	 */
    function __construct($template=false) {
        $this->templates_dir = getcwd() . "/engine/templates/";
        $this->cache_dir = getcwd() . "/temp/complied/"; 
      //  $this->force_compile = 1; 
        if($_REQUEST['tplcompile'] || IS_LOCAL) $this->force_compile = 1;
        if($template) $this->parse_template($template);
        $this->ti = new Tpl_inst();
    }
	/**
	 * Возвращает адрес папки шаблонов
	 * @return 
	 */
    function getTemplatesDir() {return $this->templates_dir; }
	/**
	 * Возвращает адрес папки кэша
	 * @return 
	 */
    function getCacheDir() {return $this->cache_dir; }
	/**
	 * Возвращает переменную из шаблона
	 * @param object $name Имя
	 * @return Значение
	 */
	function &get($name) {
		return $this->ti->{$name};
	}
	/**
	 * Устанавливает переменные в переменные шаблон через массив
	 * @param object $arr Массив переменных
	 * @return 
	 */
    function sets($arr) {
        if(!is_array($arr)) return false;
	    foreach($arr as $k=>$v) {
            $this->set($k, $v);                                
        }
    }
    function gets() {
        return get_object_vars($this->ti);
    }
	/**
	 * Устанавливает переменную в шаблоне
	 * @param object $name Имя
	 * @param object $val Значение
	 * @return 
	 */
    function set($name, $val) {
		$this->ti->{$name} = $val;
	}
	/**
	 * Устанавливает переменную в шаблоне
	 * @param object $name Имя
	 * @param object $val Значение
	 * @return 
	 */
    public function __set($name, $val) {
        $this->set($name, $val);
    }
    /**
	 * Возвращает переменную из переменных шаблона
	 * @param object $name Имя
	 * @return Значение
	 */
    public function &__get($name) {
        return $this->get($name);
    }
	/**
	 * Удаляет переменную из переменных шаблона
	 * @param object $name
	 * @return 
	 */
	function delete($name) {
		unset($this->ti->{$name});
	}
    private function postfilter() {
        
    }
	/**
	 * Внутренняя функция взятия шаблона
	 * @param object $template Путь в шаблону
	 * @return 
	 */
	private function parse_template($template) {
        if(!($file = $this->get_cache_template($template)) || $this->force_compile) {
            $file = $this->get_template($template);
            $this->set_cache_template($template,$file);
        }
        $this->template_data = true;
	}
	/**
	 * Подготовка имен шаблонных переменных
	 * @param object $matches
	 * @return 
	 */
    function replace_callback($matches) {
        $matches[0] = preg_replace('#(?<!\\\)((?:\\\{2})*)\\$\\$#', "\\1\$this->", $matches[0]); 
        $matches[0] = preg_replace('#(?<!\\\)((?:\\\{2})*)\\%\\%#', "\\1\$this->misc()->", $matches[0]); 
        return $matches[0]; 
    }
	/**
	 * Парсер шаблона
	 * @param object $file Файл
	 * @return 
	 */
    private function get_template($file) {
        if(!file_exists($this->templates_dir.$file)) {
            user_error("Template $file - not exist.", E_USER_WARNING); 
            return '';
        }
        $file = file_get_contents($this->templates_dir.$file);
        $file = preg_replace_callback("#<\?.*?\?>#is", array('self', "replace_callback"), $file); 
        $file = preg_replace_callback('/\{\{include "([^{}]*)\.tpl"\}\}/i', array('self', 'include_callback'), $file);
        return $file;
    }
    /**
     * Установка подготовленного шаблона в кэш
     * @param object $file Файл
     * @param object $data Данные
     * @return 
     */
    private function set_cache_template($file,$data) {        
        return file_put_contents($this->get_path_cache_template($file), $data);
    }
    /**
     * Извлекает путь шаблона из кэша
     * @param object $file Файл
     * @return Путь до кэш файла
     */
    private function get_path_cache_template($file) {
        $file = str_replace("/", "__", $file);
        return $this->cache_dir.'%tpl_complied_'.$file.'%.tmp';
    }
	/**
	 * Возврашает данные шаблона из кэша
	 * @param object $template Путь
	 * @return 
	 */
    private function get_cache_template($template) {
        $path = $this->get_path_cache_template($template);
		if(!file_exists($path)) {
            return false;
        }
       // echo vardump(time() - filemtime($path));
        if(time() - filemtime($path) > $this->cache_time) {
            return false;
        }
        return file_get_contents($path);
    }
    /**
     * 
     * @param object $matches
     * @return 
     */
    private function this_callback($matches) {
        if(@$matches[1]) $file = "";
        return $file;
    }
    
	/**
	 * Процесс вложенных файлов.
	 * @param object $matches
	 * @return 
	 */
    private function include_callback($matches) {
        if(@$matches[1]) $file = $this->get_template($matches[1].".tpl");
        return $file;
    }
	/**
	 * Вывод шаблона на экран
	 * @param object $template
	 * @return 
	 */
	function clear($template) {
		echo $this->fetch($template);
	}
	
	/**
	 * Вывод шаблона на экран, очистка переменных
	 * @param string $template Путь до шаблона
	 * @return 
	 */
	function display($template) {
		if(!$this->template_data) $this->parse_template($template);
        $this->ti->display($this->get_path_cache_template($template));
        unset($this->ti);
	}
	
	/**
	 * Вывод шаблона в переменную
	 * @param string $template Путь до шаблона
	 * @return 
	 */
	function fetch($template) {
		if(!$this->template_data) $this->parse_template($template);
		$f = $this->ti->fetch($this->get_path_cache_template($template));
		//unset($this->ti);
		return $f;
	}
}
/**
 * Класс шаблона
 */
class Tpl_inst {	
    /**
     * Переменная шаблонных функций
     * @var
     */
	private $misc_class = false; 
	/**
	 * Отобращает шаблон на экране
	 * @param object $file Имя шаблона
	 * @return 
	 */
	function display($file) {
		include($file);
	}
	/**
	 * Выполняет шаблон в переменную
	 * @param object $file Имя файла
	 * @return Выполненный шаблон
	 */
	function fetch($file) {
		ob_start();
		$this->display($file);
		$data = ob_get_contents();
		ob_clean();
		return $data;
	}
	/**
	 * Задает и возвращает класс шаблонных функций
	 * @return Класс
	 */
    public function misc() {
        if(!$this->misc_class) {
            $this->misc_class = new system_tpl_helper();
        }
        return $this->misc_class;
    }
}

?>
