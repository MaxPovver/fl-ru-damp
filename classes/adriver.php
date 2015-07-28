<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/country.php");

/**
 * Класс для работы с банерами adriver
 *
 */
class adriver
{
    protected static $instance;    
    
    protected $_data = array();
    
    /**
     * @todo настройки таргетинга хорошо бы в будущем нужно вынести в базу данных с настройкой через админку
     *
     * @var array
     */
    public $target = array(
        'user'   => array(
            'emp'      => array('id' => 11, 'value' => 'emp'),
            'frl'      => array('id' => 11, 'value' => 'frl'),
            'unauth'   => array('id' => 11, 'value' => 'unauth'),
        ),
        'acc'    => array(
            'pro'      => array('id' => 12, 'value' => 'pro'),
            'unpro'    => array('id' => 12, 'value' => 'unpro'),
        ),
        'ver' => array(
            'verif'    => array('id' => 13, 'value' => 'verif'),
            'unverif'  => array('id' => 13, 'value' => 'unverif'),
        ),
        //@todo: не используется
        'spec' => array (
            1  => array ('id' => 14, 'value' => 'manage'), // Менеджмент
            2  => array ('id' => 14, 'value' => 'rsites'), // Разработка сайтов
            3  => array ('id' => 14, 'value' => 'dezign'), // Дизайн
            18 => array ('id' => 14, 'value' => 'art'),    // Арт
            5  => array ('id' => 14, 'value' => 'progr'),  // Программирование
            6  => array ('id' => 14, 'value' => 'optim'),  // Оптимизация
            17 => array ('id' => 14, 'value' => 'pgraph'), // Полиграфия
            4  => array ('id' => 14, 'value' => 'flash'),  // Флеш
            8  => array ('id' => 14, 'value' => 'texts'),  // Тексты
            7  => array ('id' => 14, 'value' => 'transl'), // Переводы
            9  => array ('id' => 14, 'value' => '3dgrph'), // 3Д графика
            19 => array ('id' => 14, 'value' => 'animat'), // Анимация и Мультипликация
            10 => array ('id' => 14, 'value' => 'photo'),  // Фотография
            11 => array ('id' => 14, 'value' => 'audvid'), // Аудио и Видео
            12 => array ('id' => 14, 'value' => 'reclam'), // Реклама и маркетинг
            16 => array ('id' => 14, 'value' => 'games'),  // Разработка игр
            14 => array ('id' => 14, 'value' => 'arhint'), // Архитектура и интерьер
            20 => array ('id' => 14, 'value' => 'engine'), // Инжиниринг
            13 => array ('id' => 14, 'value' => 'conslt'), // Консалтинг
            22 => array ('id' => 14, 'value' => 'study'),  // Обучение
            44 => array ('id' => 14, 'value' => 'mobprg'), // Мобильные приложения
            45 => array ('id' => 14, 'value' => 'net'),    // Сети и инф. системы
            46 => array ('id' => 14, 'value' => 'obslcl'), // Обслуживание клиентов
            47 => array ('id' => 14, 'value' => 'market'), // Маркетинг и продажи
            48 => array ('id' => 14, 'value' => 'busines'),// Бизнес услуги
            49 => array ('id' => 14, 'value' => 'admin'),  // Административная поддержка
            50 => array ('id' => 14, 'value' => 'reppr'),  // Репетиторы и преподаватели
        ),
        'gender' => array(
            'm' => array('id' => 100, 'value' => 'm'), // Мужчина
            'f' => array('id' => 100, 'value' => 'f'), // Женщина
        ),
        'age' => array(
            '16-18' => array('id' => 101, 'value' => '16-18' ),
            '19-25' => array('id' => 101, 'value' => '19-25' ),
            '26-30' => array('id' => 101, 'value' => '26-30' ),
            '31+'   => array('id' => 101, 'value' => '31+' ),
        )
    );
    
    
    
    protected $uid;
    protected $is_emp;



    public function __construct() 
    {
        $this->uid = get_uid(false);
        $this->is_emp = is_emp();
    }
    
    
    protected function isAuth()
    {
        return $this->uid > 0;
    }

    
    protected function isEmp()
    {
        return $this->is_emp;
    }




    /**
     * По входящей строке инициализируем данные по таргетингу
     *
     * @param $list
     * @return array
     */
    function packTarget($list) 
    {
        if(!is_array($list)) {
            $list = explode(",", $list);
        }
        
        foreach($list as $name) {
            if($name == '') continue;
            list($name, $value) = explode(".", $name);

            if(isset($this->target[$name][$value])) {
                $result[] = $this->target[$name][$value];
            }
        }

        return $result;
    }

    
    /**
     * Определяем группы по возврасту
     *
     * @param $age
     * @return string
     */
    function ageTarget($age) 
    {
        if($age >= 16 && $age <= 18) {
            return "age.16-18";
        }

        if($age >= 19 && $age <= 25) {
            return "age.19-25";
        }

        if($age >= 26 && $age <= 30) {
            return "age.26-30";
        }

        if($age >= 31 ) {
            return "age.31+";
        }

        return "";
    }

    
    
    /**
     * Уникальные ключевые слова для категорий из фильтра проектов
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
            $this->setCategories($group, "pg%s");
        }
        
        if (isset($categories[1])) {
            $prof = array_keys($categories[1]);
            $this->setCategories($prof, "ps%s");
        }
        
        return true;
    }

    



    /**
     * Уникальные ключевые слова для категории и подкатегорий каталога ТУ
     * 
     * @param type $group
     * @param type $prof
     */
    public function setTuCategories($group, $prof)
    {
        $this->setCategories($group, "tg%s");
        $this->setCategories($prof, "ts%s");
    }
    
    
    /**
     * Уникальные ключевые слова для групп и специализаций каталога фрилансеров
     * 
     * @param type $group
     * @param type $prof
     */
    public function setFrlCategories($group, $prof)
    {
        $this->setCategories($group, "fg%s");
        $this->setCategories($prof, "fs%s");
    }
    
    
    /**
     * Добавить уникальные идентификаторы для категорий
     * по шаблону в параметрах
     * 
     * @param type $categories
     * @param type $template
     * @return boolean
     */
    protected function setCategories($categories, $template)
    {
        if (!$categories || empty($categories)) {
            return false;
        }
        
        $categories = !is_array($categories)?array($categories):$categories;
        $categories = array_unique($categories);
        
        $categories = array_filter($categories, function($value){
            return preg_match('/^[0-9]+$/', $value) && $value > 0; 
        });

        if (empty($categories)) {
            return false;
        }
        
        if (array_walk(
                $categories, 
                function(&$value) use ($template) { 
                    $value = array('value' => sprintf($template, $value)); 
                })) {
                    
            $this->_data = array_merge($this->_data, $categories);
        }        

        return true;
    }    
    


    /**
     * Инициализация таргетинга на текущую страницу
     */
    function initPageTarget() 
    {
        $target = array();

        if($this->isAuth())  {
            
            $str[] = $this->isEmp() ? "user.emp"  : "user.frl";
            //$str[] = is_pro()    ? "acc.pro"   : "acc.unpro";
            //$str[] = is_verify() ? "ver.verif" : "ver.unverif";
            //$str[] = $_SESSION['sex'] == 't' || $_SESSION['sex'] == NULL ? 'gender.m' : 'gender.f';
            //$str[] = $_SESSION['specs'][0] > 0 ? "spec." . professions::GetProfField($_SESSION['specs'][0], 'prof_group') : "";
            $str[] = self::ageTarget($_SESSION['age']);
            //$str[] = $_SESSION['age'] > 0 ? 'exactage.'.$_SESSION['age'] : 'exactage.0';
            
            $target = $this->packTarget($str);
            
            $this->addNamedParams($target);

        } else {
            $target = $this->packTarget('user.unauth,acc.unpro,ver.unverif');
        }

        if ($this->_data) {
            $target = array_merge($target, $this->_data);
        }
        
        return $target;
    }

    
    /**
     * Инициализируем JS переменную для таргетинга
     *
     * @example В коде страницы после инициализации нужно передать данную переменную в adriver
     * new adriver("adriver_banner", {sid: 1, bt: 52, bn: 1, custom: CUSTOM_TARGET});
     *
     * @param $target
     * @param string $type
     * @return string
     */
    function viewTarget($target, $type = 'AjaxJS') 
    {
        $js_code = "";
        switch($type) {
            case 'keywords':
                $js_code .= "var CUSTOM_TARGET = '';\r\n";
                foreach($target as $tg) {
                    $val[] = (isset($tg['name'])?$tg['name'].'=':'').$tg['value'];
                }
                $js_code .= "CUSTOM_TARGET = '".implode(";", $val)."';\r\n";
                break;
            case 'AjaxJS':
            default:
                if(!empty($target)) {
                    $js_code .= "var CUSTOM_TARGET = {};\r\n";
                    foreach($target as $tg) {
                        $js_code .= "CUSTOM_TARGET[{$tg['id']}] = '{$tg['value']}';\r\n";
                    }
                }
                break;
        }

        return $js_code;
    }

    
    /**
     * Вывод JS скрипта с метками
     */
    public function target() 
    {
        echo $this->viewTarget( $this->initPageTarget(), 'keywords' );
    }

    
    /*
    static public function getTarget() 
    {
        $ad = new adriver();
        $target = $ad->initPageTarget();
        $ret = '';
        foreach($target as $tg) {
            $val[] = $tg['value'];
        }
        $ret = implode(";", $val);
        return $ret;
    }
    */
    
    
    /* 
     * @todo: метод требует оптимизации получаемых параметров
     * 
     * Инициализация ключеных меток из значений окружения
     * 
     * @param type $target
     */
    public function addNamedParams(&$target) 
    {
        if ($this->isAuth()) {
            
            //Год рождения
            if ($_SESSION['birthday']) {
                $year = date('Y', $_SESSION['birthday']);
                $target[] = array('name' => 'user_yob', 'value' => $year);
            }
            
            //Пол
            $gender = $_SESSION['sex'] == NULL ? 'O' : ($_SESSION['sex'] == 't' ? 'M' : 'F');
            $target[] = array('name' => 'user_gender', 'value' => $gender); //M, F, O

            //Метки для групп спецализаций
            if (isset($_SESSION['groups']) && !empty($_SESSION['groups'])) {
                $this->setCategories($_SESSION['groups'], "ug%s");
            }            
            
            //Метки для специализаций
            if (!$this->isEmp() && isset($_SESSION['specs']) && !empty($_SESSION['specs'])) {
                $this->setCategories($_SESSION['specs'], "us%s");
            }
            
            //@todo: лучше использовать гео-таргетинг AdRiver
            //$country = new country();
            //$country_code = $country->GetCountryIsoCode($user->country);
            //$target[] = array('name' => 'user_geo_country', 'value'=>$country_code);
        }
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