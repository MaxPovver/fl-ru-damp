<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/search/sphinxapi.php");

/**
 * Класс-наследник API SphinxClient, для управления сразу несколькими элементами поиска.
 * Правила именования. Имена файлов, классов, VIEW, ключей элементов должны соотвествовать или содержать в себе
 * название соответствующего индекса. Например:
 * 1. Индекс 'blogs'.
 * 2. Класс 'searchElementBlogs' {@link search::ELEMENT_BASE_CLASS}.
 * 3. Ключ элемента (определяется внешне, для инициализации и идентификации элемента) 'blogs'.
 * 4. VIEW 'blogs'.
 * 5. Файл с классом элемента 'search_element_blogs.php' {@link search::ELEMENT_FILE_PFX}.
 */
class search extends SphinxClient
{
    const EXAMPLE_PHRASE = 'работа';
    const ELEMENT_FILE_PFX = 'search_element_';
    const ELEMENT_BASE_CLASS = 'searchElement';

    /**
     * Элементы поиска, объекты класса {@link searchElement}
     * @var array
     */
    private $_elements = array();

    /**
     * Ид. текущего юзера (ищущего)
     * @var integer
     */
    public $uid;


    /**
     * @param integer $uid   ид. текущего юзера (ищущего)
     */
    function __construct($uid) {
        parent::__construct();
        $this->uid = $uid;
    }

    function setUserLimit($limit) {
        $this->_limit = (int)$limit;
    }
    /**
     * Добавляет элемент в коллекцию.
     *
     * @param string $key   ключ-идентификатор элемента. Рекомендуется использовать имя базового индекса элемента.
     * @param string $active   можно ли производить поиск по данному элементу сейчас (например, включил ли его юзер в настройках).
     * @return object   добавлелнный элемент.
     */
    function addElement($key, $active = true, $limit = 10) {
        if($class = $this->getElementClass($key)) {
            $cls = ($this->_elements[$key] = new $class($this, $active));
            $cls->setUserLimit($limit);
            return $cls;
        }
        return NULL;
    }

    /**
     * Получает имя класса элемента по ключу.
     *
     * @param string $key   ключ-идентификатор элемента.
     * @return string   имя класса.
     */
    function getElementClass($key) {
        $file = dirname(__FILE__) . '/' . self::ELEMENT_FILE_PFX . $key . '.php';
        if(file_exists($file)) {
            require_once($file);
            $class = self::ELEMENT_BASE_CLASS.ucfirst($key);
            if(class_exists($class) && is_subclass_of($class, self::ELEMENT_BASE_CLASS))
                return $class;
        }
        return NULL;
    }

    /**
     * Получает ключ элемента по экземпляру класса.
     *
     * @param object $elm   элемент
     * @return string   имя ключа.
     */
    function getElementKey($elm) {
        return strtolower(str_replace(self::ELEMENT_BASE_CLASS, '', get_class($elm)));
    }

    /**
     * Возвращает коллекцию элементов.
     *
     * @return array
     */
    function getElements() {
        return $this->_elements;
    }

    
    /**
     * Вернуть указанный элемент
     * 
     * @param type $type
     */
    function getElement($type) {
        return (isset($this->_elements[$type]))?$this->_elements[$type]:false;
    }



    /**
     * Возвращает (если нужно генерирует) фразу поиска для примера (отображается под формой поиска).
     *
     * @return string
     */
    function getExample() {
        return self::EXAMPLE_PHRASE;
    }

    /**
     * Вызывает поиск по всем элементам.
     *
     * @param string $string   строка поиска.
     * @param integer $page   номер текущей страницы (используется при поиске по конкретному элементу).
     */
    function search($string, $page = 0, $filter=false) {
        if(!$string && !$filter) return;
        foreach($this->_elements as $name=>$elm) {
            if($filter && $name != "projects" && $name != "users_test") $elm->setAdvancedSearch($page, $filter);
            if(strtolower($name) != strtolower($_SESSION['search_tab_active'])) {
                $elm->active_search = false;
            } else {
                $elm->active_search = true;
            }
            $elm->search($string, $page, ($name == "projects" || $name == "users_test" || $name == "users_simple" ) ? $filter : false); // #0014689 #0016532
        }
           
    }
}
