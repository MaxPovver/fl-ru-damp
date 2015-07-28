<?php

require_once 'DigestBlockList.php';

/**
 * Класс для работы с блоком фрилансеров
 */
class DigestBlockListFreelancer extends DigestBlockList {
    
    /**
     * Доступна ли кнопка автозаполнения ссылок
     * 
     * @var boolean 
     */
    const AUTO_COMPLETE = true;
    
    /**
     * Маска валидации и проверки ссылки
     * 
     * @var string
     */
    const MASK_LINK = '~users\/(\S+)~mix';
    
    /**
     * Количество ссылок
     * 
     * @var integer 
     */
    protected $_list_size = 6;
    
    /**
     * @see parent::$title
     */
    public $title = 'Топ %s рекомендуемых <a class="b-layout__link" href="/freelancer/" target="_blank">фрилансеров</a>';
    
    /**
     * @see parent::$hint
     */
    public $hint = 'Например: http://www.free-lance.ru/users/example/';
    
    /**
     * @see parent::$title_field
     */
    public $title_field = 'Ссылки на фрилансеров:';
    
    /**
     * Конструктор класса
     * 
     * @param integer $size
     * @param mixed   $link
     */
    public function __construct($size = null, $link = null) {
        parent::__construct($size, $link);
        $this->setTitle($this->title, $this->getListSize());
    }
    
    /**
     * @see parent::initHtmlData
     */
    public function initHtmlData() {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/freelancer.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/professions.php';
        
        $login = $this->parseLinks();
        
        if($login) {
            $this->html_data = freelancer::getFreelancerByLogin($login);
        }
    }
    
    /**
     * Функция автозаполнения полей
     * 
     * @return boolean
     */
    public function setFieldAutoComplete() {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/freelancer.php';
        $freelancer = freelancer::getTopFreelancer( $this->getListSize() );
        
        if($freelancer) {
            foreach($freelancer as $frl) {
                $link[] = "{$GLOBALS['host']}/users/{$frl['login']}";
            }

            $this->initBlock($link);
            return true;
        }
        return false;
    }
}