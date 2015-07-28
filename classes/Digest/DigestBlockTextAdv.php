<?php

require_once 'DigestBlockText.php';

/**
 * Класс для работы с блоком "Реклама"
 */
class DigestBlockTextAdv extends DigestBlockText {
    
    /**
     * @see parent::$title
     */
    public $title   = 'Рекламный блок';
    
    /**
     * @see parent::$created
     */
    const IS_CREATED = false;
    
    /**
     * Инициализация блока
     * 
     * @param array $data
     */
    public function initialize($data) {
        $class = $this->__toString();
        
        $this->setMain( $data[$class.'Main'] == 1 );
        $this->setPosition( $data['position'][$class] );
        $this->setCheck( isset($data[$class.'Check']) ? ($data[$class.'Check'] == 1) : false );
        $this->initBlock( $data[$class.'Name'], $data[$class.'Link'], $data[$class.'Descr']);
    }
}