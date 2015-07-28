<?php

require_once 'DigestBlockText.php';
require_once 'DigestBlockTextAdv.php';
require_once 'DigestBlockListProject.php';
require_once 'DigestBlockListContest.php';
require_once 'DigestBlockListArticle.php';
require_once 'DigestBlockListBlog.php';
require_once 'DigestBlockListCommune.php';
require_once 'DigestBlockListFreelancer.php';
require_once 'DigestBlockListInterview.php';

/**
 * Класс для работы с блоками дайджеста
 * 
 */
class DigestFactory {
    
    /**
     * Переменная содержить блоки дайджеста 
     * 
     * @var array 
     */
    private $_blocks = array();
    
    /**
     * Количество созданных блоков в классе
     * 
     * @var integer
     */
    private $_current_position = 0;
    
    /**
     * Допустимые типы блоков
     * 
     * @var array
     */
    public static $types = array(
        DigestBlock, 
        DigestBlockText,
        DigestBlockAdv,
        DigestBlockList,
        DigestBlockListProject,
        DigestBlockListContest,
        DigestBlockListArticle,
        DigestBlockListBlog,
        DigestBlockListCommune,
        DigestBlockListFreelancer,
        DigestBlockListInterview
    );
    
    /**
     * Сортировка блоков по позиции
     * 
     * @param DigestBlock $a   
     * @param DigestBlock $b
     * @return int
     */
    public static function sortPosition($a, $b) {
        if ($a->getPosition() == $b->getPosition()) {
            return 0;
        }
        return ($a->getPosition() < $b->getPosition()) ? -1 : 1;
    }
    
    /**
     * Сортируем блоки по позиции
     * 
     * @param boolean $reverse Перевернуть блок после сортировки или нет
     */
    public function sort($reverse = false) {
        usort($this->_blocks, array('DigestFactory', 'sortPosition'));
        if($reverse) {
            $this->_blocks = array_reverse($this->_blocks);
        }
    }
    
    /**
     * Текущая позиция
     * 
     * @return integer
     */
    public function currentPosition() {
        return $this->_current_position;
    }
    
    /**
     * Увеличиваем текущую позиция на единицу
     */
    public function increasePosition() {
        $this->_current_position++;
    }
    
    /**
     * Уменьшаем текущую позиция на единицу
     */
    public function decreasePosition() {
        $this->_current_position--;
    }
    
    /**
     * Вспомогательная функция, при вставке блока в середину изменяет позицию следующих за ним блоков
     * 
     * @param DigestBlock $a
     * @return DigestBlock
     */
    public static function updatePositionBlock($a) {
        $a->setUpPosition();
        return $a;
    }
    
    /**
     * Создает блоки которые используются по умолчанию
     */
    public function createDefaultBlocks() {
        $blocks[] = new DigestBlockText();
        $blocks[] = new DigestBlockListContest();
        $blocks[] = new DigestBlockListProject();
        $blocks[] = new DigestBlockListFreelancer(6);
        $blocks[] = new DigestBlockListArticle(2);
        $blocks[] = new DigestBlockListInterview(2);
        $blocks[] = new DigestBlockListBlog(2);
        $blocks[] = new DigestBlockListCommune(2);
        $blocks[] = new DigestBlockTextAdv();
        
        $this->createBlocks($blocks);
    }
    
    /**
     * Создает дополнительные блоки такого же типа как есть в системе
     * 
     * @param DigestBlock $obj      Блок для создания
     * @param integer     $size     Количество блоков которые необходимо создать
     */
    public function createAdditionBlocks($obj, $size = 1) {
        for($i=0; $i < $size; $i++) {
            $obj->setNum($i+1);
            $blocks[] = clone $obj;
        }
        
        $this->createBlocks($blocks);
    }
    
    /**
     * Создание блоков
     * 
     * @param array $blocks Массив созданных блоков @see DigestBlock
     */
    public function createBlocks($blocks) {
        foreach($blocks as $block) {
            $this->createBlock($block);
        }
    }
    
    /**
     * Создает блок в системе
     * 
     * @param DigestBlock $block        Блок
     * @param integer     $position     Позиция блока, если null присваивается автоматически
     */
    public function createBlock(DigestBlock $block, $position = null) {
        if($position == null || $position <= 0) {
            $this->increasePosition();
            $block->setPosition($this->currentPosition());
            $this->_blocks[] = $block;
        } else {
            $block->setPosition($position);
            
            $after   = array_slice($this->_blocks, 0, $position-1);
            $after[] = $block;
            $before  = array_slice($this->_blocks, $position-1);
            $before  = array_map(array('DigestFactory', 'updatePositionBlock'), $before);
            
            $this->_blocks = array_merge($after, $before);
        }
    }
    
    /**
     * Сохраняем в базу данных данные о блоках
     * 
     * @global DB $DB Подключение к БД
     * 
     * @param integer $mailer_id Ид созданной рассылки @see table.mailer_messages.id  
     * @return boolean
     */
    public function saveDigestBlocks($mailer_id) {
        global $DB;
        
        $serialize = serialize($this->getBlocks());
        $insert = array(
            'id_mailer' => $mailer_id,
            'blocks'    => base64_encode($serialize)
        );
        
        return $DB->insert('mailer_digest', $insert, 'id');
    }
    
    /**
     * Обновляем данные о блоках
     * 
     * @global DB $DB
     * @param integer $mailer_id Ид созданной рассылки @see table.mailer_messages.id  
     * @return type
     */
    public function updateDigestBlocks($mailer_id) {
        global $DB;
        
        $serialize = serialize($this->getBlocks());
        $insert = array(
            'blocks'    => base64_encode($serialize)
        );
        
        return  $DB->update('mailer_digest', $insert, 'id_mailer = ?i', $mailer_id);
    }
    
    /**
     * На основе блоков собирает полную картину сообщения в HTML
     * 
     * @return string 
     */
    public function createHTMLMessage() {
        $this->host = $GLOBALS['host'];
        
        ob_start();
        
        include ($_SERVER['DOCUMENT_ROOT'] . DigestBlock::TEMPLATE_PATH . "/tpl.block.header.php");
        foreach($this->getBlocks() as $block) {
            if($block->isCheck()) {
                $block->htmlBlock();
            }
        }
        include ($_SERVER['DOCUMENT_ROOT'] . DigestBlock::TEMPLATE_PATH . "/tpl.block.footer.php");
        
        $html = ob_get_clean();
        return $html;
    }
    
    /**
     * Возвращает блоки созданные в системе
     * 
     * @return array
     */
    public function getBlocks() {
        return $this->_blocks;
    }
}