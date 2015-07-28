<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/country.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/city.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/seo/SeoValues.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/seo/SeoText.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/tservices/tservices_categories.php");
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );

/**
 * Класс для работы с сео-тегами разделов и специализаций
 */
class SeoTags {
    
    protected static $_instance;
    
    
    protected $seo_value;

    private $title;
    
    private $description;
    
    private $keywords;
    
    private $footer_head;
    
    private $footer_text;
    
    private $image_name;
    
    private $image_description;
    
    private $h1;
    
    private $side_head;
    
    private $side_text;
    
    /**
     * Конструктор по умолчанию
     */
    private function __construct(){
    }
    
    private function __clone(){
    }
    
    public static function getInstance() {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Заполняет значения в зависимости от для профиля юзера
     * 
     * @param array $user Информация о пользователе
     */
    function initByUser($user) {
        
        $role_text = is_emp($user->role) ? SeoText::ROLE_EMP : SeoText::ROLE_FRL;
        
        $user_name = trim(sprintf(SeoText::USER_NAME, (string)$user->uname, (string)$user->usurname, $user->login));
  
        //Получаем названия страны и города
        if ($user->country > 0) {
            $country = new country();
            $country_name = $country->GetCountryName($user->country);
        }
        
        if($user->city > 0) {
            $city = new city();
            $city_name = $city->GetCityName($user->city);
        }

        if ($user->spec) {
            $this->seo_value = new SeoValues();
            $this->seo_value->initCard($user->spec);

            $this->title = !$user->is_banned
                ? sprintf(SeoText::USER_TITLE_SPEC, 
                        $role_text, $user_name, 
                        $this->seo_value->getKey(1), @$country_name, @$city_name)
                : sprintf(SeoText::USER_TITLE_BLOCKED, $user->login);

            $this->description = sprintf(SeoText::USER_DESC_SPEC, 
                    $user_name, @$country_name, @$city_name,
                    $this->seo_value->getFTitle(1), $this->seo_value->getFTitle(2), 
                    $this->seo_value->getKey(1), $this->seo_value->getKey(2), $this->seo_value->getKey(3));


            $this->keywords = sprintf(SeoText::USER_KEY_SPEC, 
                    $user_name,
                    $this->seo_value->getFTitle(1), 
                    $this->seo_value->getKeysString()
                );


            $tags = ($this->seo_value->getFTitle(1) ? sprintf(SeoText::REMOTELY, $this->seo_value->getFTitle(1)) : '') 
                . $this->seo_value->getKeysString();
            $this->footer_text = $tags ? sprintf(SeoText::TAGS, $tags) : '';

            $this->image_description = sprintf(SeoText::REMOTELY, $this->seo_value->getFTitle(1)) . $this->seo_value->getKey(1);

        } else {

            $this->title = !$user->is_banned
                ? sprintf(SeoText::USER_TITLE_DEF, 
                        $role_text, $user_name,
                        @$country_name, @$city_name)
                : sprintf(SeoText::USER_TITLE_BLOCKED, $user->login);

            $this->description = sprintf(SeoText::USER_DESC_DEF, 
                    $user_name, @$country_name, @$city_name);
            $this->keywords = sprintf(SeoText::USER_KEY_DEF, $user_name);
            $this->footer_text = SeoText::USER_FOOTER_DEF;            
            $this->image_description = SeoText::IMAGE_DESC_DEF;
        }


        if ($user->is_pro=='t' && $user->pname) { //если в настройках задан пользовательский заголовок
            $stop_words = new stop_words( hasPermissions('projects') );
            $own_title = $user->isChangeOnModeration( $user->uid, 'pname' ) && $user->is_pro != 't' 
                ? $stop_words->replace($user->pname, 'plain', false) 
                : $user->pname;
            $this->title = $own_title;
        }
        
        if (is_emp($user->role)) {
            $this->description = sprintf(SeoText::EMP_DESC, $user_name, @$country_name, @$city_name);
            $this->keywords = sprintf(SeoText::EMP_KEY, $user_name);
            $this->footer_text = sprintf(SeoText::TAGS, SeoText::EMP_KEY);
        }

    }
    
    /**
     * Заполняет значения для страницы работы в портфолио
     * @param array $item
     */
    function initByPortfolio($item, $spec_text)
    {
        $user_name = trim(sprintf(SeoText::USER_NAME, $item['uname'], $item['usurname'], $item['login']));
        
        $this->title = sprintf(SeoText::PORTFOLIO_TITLE, $user_name, $spec_text, $item['name']);
        
        $this->description = sprintf(SeoText::PORTFOLIO_DESC, $item['uname'], $item['usurname'], $spec_text, $item['name'], $user_name);
        
        if ($item['user_spec']) {
            
            $this->seo_value = new SeoValues();
            $this->seo_value->initCard($item['user_spec']);

            $this->keywords = sprintf(SeoText::USER_KEY_SPEC, $user_name,
                    $this->seo_value->getFTitle(1), $this->seo_value->getKeysString()
                );
            
        } else {
            
            $this->keywords = sprintf(SeoText::USER_KEY_DEF, $user_name);
            
        }
    }
    
    /**
     * Заполняет значения в зависимости от раздела каталога фрилансеров
     * 
     * @param int $prof_id ИД раздела
     * @param bool $is_spec Флаг раздел или специализация
     */
    function initFreelancers($prof_id, $page = 1, $is_spec = true) {
        if ($prof_id) {
            $this->seo_value = new SeoValues();
            $this->seo_value->initCard($prof_id, $is_spec);

            $this->title = sprintf(SeoText::FRL_TITLE, 
                $this->seo_value->getFTitle(1), 
                $this->seo_value->getKey(1), 
                $this->seo_value->getKey(2),
                $page
            );

            $this->description = sprintf(SeoText::FRL_DESC,
                $this->seo_value->getFTitle(1),
                $this->seo_value->getFTitle(2),
                $this->seo_value->getKeysString(5)
            );

            $this->keywords = sprintf(SeoText::FRL_KEY,
                $this->seo_value->getFTitle(1),
                $this->seo_value->getKeysString()
            );

            $this->h1 = ($this->seo_value->getFTitle(1) ? $this->seo_value->getFTitle(1) . ', ' : '') . $this->seo_value->getFTitle(2);

            $this->side_head = $this->seo_value->getFTitle(3);

            $this->side_text = $this->seo_value->getFText(3);

            $this->footer_head = $this->seo_value->getFTitle(4);

            $this->footer_text = $this->seo_value->getFText(4);

            $this->image_description = sprintf(SeoText::FRL_IMG_DESC,
                $this->seo_value->getFTitle(1),
                $this->seo_value->getKey(1)
            );
        } else {
            $this->title = sprintf(SeoText::FRL_TITLE_DEF, $page);
            $this->h1 = 'Каталог фрилансеров';
        }
    }
    
    /**
     * Заполняет значения в зависимости от раздела услуг
     * 
     * @param int $prof_id ИД раздела
     * @param bool $is_spec Флаг раздел или специализация
     */
    function initTserviceList($tservice_prof_id, $is_spec = true, $count = 0, $min_price = 0) {
        $prof_id = $this->getRealProfId($tservice_prof_id);        
        $this->seo_value = new SeoValues();
        $this->seo_value->initCard((int)$prof_id, $is_spec);
        
        if ($this->seo_value->getTUTitle(1)) {
            $this->h1 = $this->seo_value->getTUTitle(1) . ($this->seo_value->getTUTitle(2) ? ', ' . $this->seo_value->getTUTitle(2) : '');
            
            $text_price = $min_price . ' ' . ending($min_price, 'рубля', 'рубля', 'рублей');
            $t_price = $min_price ? sprintf(SeoText::TU_TITLE_PRICE, $text_price) : '';
            $text_count = $count . ' ' . ending($count, 'предложения', 'предложений', 'предложений');
            $t_count = $count >= 10 ? sprintf(SeoText::TU_TITLE_COUNT, $text_count) : SeoText::TU_TITLE_COUNT_LESS;
            $this->title = sprintf(SeoText::TU_TITLE, 
                $this->h1, 
                $t_price,
                $t_count
            );
            
            $this->description = $this->description = sprintf(SeoText::TU_DESC,
                $this->seo_value->getTUTitle(1),
                $this->seo_value->getTUTitle(2),
                $this->seo_value->getKeysString(5)
            );
            
            $this->keywords = sprintf(SeoText::TU_KEY,
                $this->seo_value->getTUTitle(1),
                $this->seo_value->getKeysString()
            );
            
            $this->side_head = $this->seo_value->getTUTitle(3);
            $this->side_text = $this->seo_value->getTUText(3);
            
            $this->footer_head = $this->seo_value->getTUTitle(4);
            $this->footer_text = $this->seo_value->getTUText(4);
            
            $this->image_description = sprintf(SeoText::FRL_IMG_DESC,
                $this->seo_value->getTUTitle(1),
                $this->seo_value->getKey(1)
            );
            
        } else {
            $this->title = 'Фриланс сайт удаленной работы №1. Фрилансеры, работа на дому, freelance : FL.ru';
            $this->h1 = "Услуги фрилансеров";
        }
    }
    
    /**
     * Заполняет значения в зависимости от услуги
     * 
     * @param array $tservice Массив с данными услуги
     * @param string $user Массив с данными пользователя
     */
    function initTServicesCard($tservice, $user) {
        $prof_id = $this->getRealProfId($tservice['category_id']);
        $this->seo_value = new SeoValues();
        $this->seo_value->initCard($prof_id);
        
        $title50 = LenghtFormatEx(trim(strip_tags($tservice['title'])),50);
        $title100 = LenghtFormatEx(trim(strip_tags($tservice['title'])),100);
                
        //Получаем названия страны и города
        $country_name = $city_name = '';
        if ($user->country > 0) {
            $country = new country();
            
            $country_name = $country->GetCountryName($user->country);
        }
        
        if($user->city > 0) {
            $city = new city();
            $city_name = $city->GetCityName($user->city);
        }
        $fullname = trim("{$user->uname} {$user->usurname} [{$user->login}]");
        
        $this->title = sprintf(SeoText::TUC_TITLE, $title50, $tservice['price'], $fullname, $country_name, $city_name);
        $this->description = sprintf(SeoText::TUC_DESC, $fullname, $title100);
        $this->keywords = sprintf(SeoText::TUC_KEY, $fullname, $this->seo_value->getTUTitle(1), $this->seo_value->getKeysString());
        
        $this->image_name = $title50;
        
        $this->image_description = sprintf(SeoText::TUC_IMG_DESC, $title50, $this->seo_value->getKey(1));
        
        $this->footer_text = sprintf(SeoText::TUC_TAGS, $this->seo_value->getTUTitle(1), $this->seo_value->getKeysString());
    }
    
    /**
     * Получает ИД профессии по ИД категории услуг
     */
    private function getRealProfId($tservice_prof_id) {
        $tservice_categories = new tservices_categories();
        $category = $tservice_categories->getCategoryById((int)$tservice_prof_id);
        $prof_id = $category['pid'] ? $category['pid'] : $category['gid'];
        return $prof_id;
    }
    
    /**
     * Заполняет значения в зависимости от проекта
     * 
     * @param array $project данные проекта
     */
    function initProject($project) {
        $stop_words = new stop_words( hasPermissions('projects') );
        $title = $project['moderator_status'] === '0' && $project['kind'] != 4 && $project['is_pro'] != 't' 
                ? $stop_words->replace($project['name'], 'plain', false) 
                : $project['name'];
        $title50 = LenghtFormatEx(trim(strip_tags($title)),50);
        $title100 = LenghtFormatEx(trim(strip_tags($title)),100);
        
        $sp = new_projects::getSpecs($project['id']);
        
        $is_spec = $sp[0]['subcategory_id'] > 0;
        $prof_id = $is_spec ? $sp[0]['subcategory_id'] : $sp[0]['category_id'];
        
        $this->seo_value = new SeoValues();
        $this->seo_value->initCard($prof_id, $is_spec);
        
        $this->title = sprintf(SeoText::PRJ_TITLE, $title50, $this->seo_value->getKey(1));
        
        $this->description = sprintf(SeoText::PRJ_DESC, $title100, $this->seo_value->getKeysString(3));
        
        $this->keywords = sprintf(SeoText::PRJ_KEY, $this->seo_value->getFTitle(1), $this->seo_value->getKeysString());
        
        $this->footer_text = sprintf(SeoText::PRJ_TAGS, $this->seo_value->getFTitle(1), $this->seo_value->getKeysString());
        
    }


    /**
     * Текст для тега title
     * @return string Title страницы
     */
    function getTitle() {
        return $this->filter($this->title);
    }
    
    /**
     * Текст для тега description
     * @return string Description страницы
     */
    function getDescription() {
        return $this->filter($this->description);
    }
    
    /**
     * Текст для тега keywords
     * @return string keywords страницы
     */
    function getKeywords() {
        return $this->filter($this->keywords);
    }
    
    /**
     * @return string Заголовок в футере
     */
    function getFooterHead() {
        return $this->filter($this->footer_head);
    }
    
    /**
     * @return string Текст в футере
     */
    function getFooterText() {
        return $this->filter($this->footer_text);
    }
    
    /**
     * @return strine подпись к иллюстрациям
     */
    function getImageDescription() {
        return $this->filter($this->image_description);
    }
    
    /**
     * @return string Текст для тега h1
     */
    function getH1() {
        return $this->filter($this->h1);
    }
    
    /**
     * @return string Заголовок в боковой колонке
     */
    function getSideHead() {
        return $this->filter($this->side_head);
    }
    
    /**
     * @return string Текст в боковой колонке
     */
    function getSideText() {
        return $this->filter($this->side_text);
    }
    
    /**
     * Выполняет обработку строк перед выдачей.
     * @param type $string Исходный текст
     * @return type Преобразованный текст
     */
    private function filter($string) {
        $string = trim(trim($string), ',');
        $string = trim(str_replace(
                array(', ,',    ', .',  '  ', ' .'), 
                array(',',      '.',    ' ',  '.' ), 
                $string)
        );
        $string = trim(trim($string), ',');
        return $string;        
    }
    
}
