<?php

/**
 * Класс для работы со значениями сео-текстов для разделов и специализаций
 *
 * @author danil
 */
class SeoValues {

    /**
     * Количество заголовков
     */
    const SIZE_TITLE = 4;
    
    /**
     * Количество ключевых слов
     */
    const SIZE_KEY = 10;
    const SIZE_TEXT = 4;
    
    const TABLE = 'seo_tags';

    /*
     * Заголовки в seo-текстах и разделах каталога услуг
     */
    protected $tu_titles;

    /*
     * Заголовки в seo-текстах и разделах каталога фрилансеров
     */
    protected $f_titles;

    /*
     * ключевые слова в разделах каталога
     */
    protected $keys;

    /*
     * seo-тексты в разделах каталога услуг
     */
    protected $tu_texts;

    /*
     * seo-тексты в разделах каталога фрилансеров
     */
    protected $f_texts;
    
    
    /**
     * Получение одной записи
     * @param integer $id ИД привязки 
     * @param bool $is_spec специализация или раздел
     */
    function initCard($id, $is_spec = true) {
        $sql = "SELECT * FROM " . self::TABLE . " WHERE parent_id = ?i AND is_spec = ?;";
        $row = $this->db()->cache(900)->row($sql, (int)$id, (bool)$is_spec);
        if($row) {
            for($i = 1; $i <= self::SIZE_KEY; $i++) {
                $this->keys[$i] = $row['key_'.$i];
            }
            
            for($i = 1; $i <= self::SIZE_TITLE; $i++) {
                $this->tu_titles[$i] = $row['tu_title_'.$i];
                $this->f_titles[$i] = $row['f_title_'.$i];
            }
            
            for($i = 1; $i <= self::SIZE_TEXT; $i++) {
                $this->tu_texts[$i] = $row['tu_text_'.$i];
                $this->f_texts[$i] = $row['f_text_'.$i];
            }
        }
    }
    
    /**
     * Получение списка записей для админки
     */
    public function getList() {
        $sql = "SELECT st.*, p.name as prof_title, pg.name as prof_group_title 
            FROM " . self::TABLE . " st
                LEFT JOIN professions p ON p.id = st.parent_id
                LEFT JOIN prof_group pg ON pg.id = st.parent_id
                ORDER BY st.id;";      
        return $this->db()->rows($sql);
    }
    
    /**
     * Получение одной записи для админки
     */
    public function getCardById($id) {
        $sql = "SELECT st.*, p.name as prof_title, pg.name as prof_group_title 
            FROM " . self::TABLE . " st
            LEFT JOIN professions p ON p.id = st.parent_id
            LEFT JOIN prof_group pg ON pg.id = st.parent_id
            WHERE st.id = ?i
            LIMIT 1;";      
        return $this->db()->row($sql, (int)$id);
    }
    
    public function save($id, $post) {
        return $this->db()->update(self::TABLE, $post, 'id = ?i', (int)$id);
    }
    
    /**
     * Возвращает одно ключевое слово по ключу
     * @param int $num Ключ
     * @return string
     */
    public function getKey($num) {
        return $this->keys[$num];
    }
    
    /**
     * Возвращает строку из ряда ключевых слов
     * @param int $count Количество используемых ключевых слов
     * @return string Собранная строка
     */
    public function getKeysString($count = self::SIZE_KEY) {
        if ($this->keys) {
            $keys = array_diff($this->keys, array(''));
            $keys = array_slice($keys, 0, $count);
            return implode(', ', $keys);
        }
        return '';
    }
    
    /**
     * Возвращает один из заголовков фрилансеров по ключу
     * @param int $num ключ
     * @return string Заголовок
     */
    public function getFTitle($num) {
        return isset($this->f_titles[$num]) ? $this->f_titles[$num] : '';
    }
    
    /**
     * Возвращает один из текстов фрилансеров по ключу
     * @param int $num ключ
     * @return string Текст
     */
    public function getFText($num) {
        return isset($this->f_texts[$num]) ? $this->f_texts[$num] : '';
    }
    
    /**
     * Возвращает один из заголовков услуг по ключу
     * @param int $num ключ
     * @return string Заголовок
     */
    public function getTUTitle($num) {
        return isset($this->tu_titles[$num]) ? $this->tu_titles[$num] : '';
    }
    
    /**
     * Возвращает один из текстов услуг по ключу
     * @param int $num ключ
     * @return string Текст
     */
    public function getTUText($num) {
        return isset($this->tu_texts[$num]) ? $this->tu_texts[$num] : '';
    }
    
    /**
     * @return $DB
     */
    function db() {
        return $GLOBALS['DB'];
    }

}
