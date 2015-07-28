<?php

/**
 * Класс для работы с ключевыми словами
 * 
 * @example 
 * <?
 * $kw = new kwords();
 * 
 * $kw->add('XML');
 * $kw->add('XML');
 * $kw->add('Xml');
 * $kw->add('xml');
 * $kw->add('CSS');
 * $kw->add(array('xml', 'css', 'php', 'js'));
 * 
 * ?>
 * <script type="text/javascript" src="/kword_js.php"></script>
 */
class kwords 
{
    /**
     * Название ключа для кеширования js-скрипта, хранящего массив слов
     * @see /kword_js.php
     *
     */
    const MEM_KEY_NAME = 'kword_keys_js';

    /**
     * Время кеширования в секундах
     *
     */
    const MEM_TIME      = 1800;
    
    /**
     * Максимальное количество вывода ключевых слов для раздела Портфолио
     *
     */
    const MAX_KWORDS_PORTFOLIO = 20;
    
    /**
     * Добавить новое ключевое значение в таблицы
     *
     * @param string|array $key    Ключевое значение (или значения)
     * @param boolean      $return Вернут ИД записанных значений или нет
     * @return boolean
     */
    function add($key, $return = false) { 
        global $DB;
        
        if(is_array($key) && count($key) > 0) {
            //$sql = "INSERT INTO words (name) VALUES ";
            
            foreach($key as $k=>$v) if(trim($v) != "") $keys[$k] = trim(substr($v,0,32));
            
            $key = $keys;
            
            if ( count($key) ) {
                $data = array();
                
                foreach($key as $k=>$v) {
                    $data[] = array('name' => $v);// " ('".$v."')";
                }
                
                if ( count($data) ) {
                    $DB->insert("words", $data);
                }
            }
            
            if($return) {
                $ret = array();
                
                if ( count($key) ) {
                    $ursql = array();
                    
                    foreach($key as $k=>$v) {
                        if(trim($v) == "") continue;
                        $ursql[] = stripslashes($v);//htmlspecialchars($v,ENT_QUOTES);
                    }
                    
                    if ( count($ursql) ) {
                        $rsql  = "SELECT id, name FROM words WHERE name IN (?l)";
                        $ret = $DB->rows( $rsql , $ursql);
                    }
                }
                
                if(!$ret) return false;
                
                $i=0;
                foreach($key as $k=>$v) {
                    $i++;
                    foreach($ret as $key=>&$val) {
                        if(stripslashes($val['name']) == stripslashes(trim($v))) {
                            $val['pos'] = $i;
                        }
                    }
                    
                }
                foreach($ret as $k=>$v) $ids[(int)$v['pos']] = $v['id'];
                return $ids;
            }
            
            return true;    
        } elseif(trim($key) != "" ) {
		    $DB->insert( 'words', array('name' => $key) );   
        }
        
        return false;
    }
    
    /**
     * Загржаем пользователю самые частые ключевые значения, 
     * и выдаем их виде массива для выдачи в переменную JS
     *
     * @param integer $N Общее количество вызовов ключевого слова (популярность)
     * @return array  
     */
    function load($N=7, &$kw_info = array()) {
        global $DB;
        $sql = "SELECT name FROM words_groups WHERE mcount>=?i";
        $ret = $DB->rows( $sql, $N );
        if(!$ret) return array(0);
        $kw_info = $ret;
        foreach($ret as $k=>$v) $ar[] = "'".htmlspecialchars(addslashes($v['name']))."'";
       
        return $ar;
    }
    
    /**
     * Добавляем ключевые слова пользователю
     *
     * @param integer $uid  ИД Пользователя
     * @param array   $ids  Массив ИД добавленных ключевых слов
     * @param integer $prof ИД Профессии к которой добавляли ключевые слова
     */
    function addUserKeys($uid, $ids, $prof) {
        if ( $uid > 0 && is_array($ids) && count($ids) > 0 ) {
            global $DB;
            foreach($ids as $k=>$v) {
                $usql[] = array( 'uid' => $uid, 'wid' => trim($v), 'prof_id' => $prof, 'pos' => $k );
            }
            $DB->insert( 'portf_word', $usql );
        }
    }
    
    /**
     * Получаем ключевые слова пользователя
     *
     * @param integer $uid ИД Пользователя
     */
    function getUserKeys($uid, $prof) {
        global $DB;
        $sql = "SELECT w.name, w.id FROM portf_word pw JOIN words w ON w.id = pw.wid WHERE pw.uid = ?i AND prof_id = ?i ORDER BY pos ASC"; 
        $ret = $DB->rows( $sql, $uid, $prof );
        
        if(!$ret) return array(); 
        foreach($ret as $k=>$val) $result[$val['id']] = addslashes($val['name']);
        return $result;  
    }
    
    /**
     * Сохраняет изменения ключевые слова пользователя для модерирование
     * 
     * @param  int $uid UID Пользователя
     * @param  int $prof ID Профессии
     * @param  array $old_ids массив ID старых ключевых слов
     * @param  array $new_ids массив ID новых ключевых слов
     * @param  integer $moduser_id UID изменяющего пользователя (админа). если null - то берется $uid
     * @param  string $keys строка с ключевыми словами через запятую
     * @return bool true - успех, false - провал
     */
    function moderUserKeys( $uid = 0, $prof = 0, $old_ids = array(), $new_ids = array(), $moduser_id = null, $keys = '' ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
        $bRet = true;
        $moduser_id = $moduser_id ? $moduser_id : $uid;
        $new_ids = $new_ids ? $new_ids : array();
        
        if ( $uid && $uid == $moduser_id && !hasPermissions('users') && $prof && (array_diff($new_ids, $old_ids) || array_diff($old_ids, $new_ids)) ) {
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );
            
            $sId = $GLOBALS['DB']->val( "SELECT id FROM portf_choise_change 
                WHERE user_id = ?i AND prof_id = ?i AND ucolumn = 'kwords'", 
                $uid, $prof
            );
            
            $stop_words    = new stop_words();
            $nStopWordsCnt = $stop_words->calculate( $keys );
            
            if ( !$sId && $new_ids && $nStopWordsCnt ) {
                $aData = array(
                    'user_id' => $uid, 
                    'prof_id' => $prof, 
                    'ucolumn' => 'kwords', 
                    'stop_words_cnt' => $nStopWordsCnt, 
                    'old_val' => implode( ',', $old_ids ),
                    'moderator_status' => is_pro() ? -2 : 0
                );

                $sId = $GLOBALS['DB']->insert( 'portf_choise_change', $aData, 'id' );
                $bRet = empty( $GLOBALS['DB']->error );
                
                if ( $bRet && !is_pro() ) {
                    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
                    $GLOBALS['DB']->insert( 'moderation', array('rec_id' => $sId, 'rec_type' => user_content::MODER_PORTF_CHOISE, 'stop_words_cnt' => $nStopWordsCnt) );
                }
            }
            elseif ( $sId && $new_ids && $nStopWordsCnt ) {
                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
                $GLOBALS['DB']->query( 'UPDATE portf_choise_change SET stop_words_cnt = ?i WHERE id = ?i', $nStopWordsCnt, $sId );
                $GLOBALS['DB']->query( 'UPDATE moderation SET stream_id = NULL, stop_words_cnt = ?i WHERE rec_id = ?i AND rec_type = ?i', $nStopWordsCnt, $sId, user_content::MODER_PORTF_CHOISE );
            }
            else {
                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
                $GLOBALS['DB']->query( 'DELETE FROM portf_choise_change WHERE id = ?i', $sId );
                $GLOBALS['DB']->query( 'DELETE FROM moderation WHERE rec_id = ?i AND rec_type = ?i', $sId, user_content::MODER_PORTF_CHOISE );
            }
        }
        elseif ( $uid && $moduser_id && $uid != $moduser_id && hasPermissions('users') && $prof ) {
            $sId = $GLOBALS['DB']->val( "SELECT id FROM portf_choise_change 
                WHERE user_id = ?i AND prof_id = ?i AND ucolumn = 'kwords'", 
                $uid, $prof
            );
            
            if ( $sId ) {
                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
                $GLOBALS['DB']->query( 'DELETE FROM portf_choise_change WHERE id = ?i', $sId );
                $GLOBALS['DB']->query( 'DELETE FROM moderation WHERE rec_id = ?i AND rec_type = ?i', $sId, user_content::MODER_PORTF_CHOISE );
            }
        }
        
        return $bRet;
    }
    
    /**
     * Находятся ли ключевые слова пользователя на модерирование
     * 
     * @param  int $uid UID Пользователя
     * @param  int $prof ID Профессии
     * @return bool true - на модерации, false - уже прошло модерацию
     */
    function isModerUserKeys( $uid = 0, $prof = 0 ) {
        $nVal = $GLOBALS['DB']->val( "SELECT id FROM portf_choise_change WHERE user_id = ?i AND prof_id = ?i AND ucolumn = 'kwords' AND (moderator_status = 0 OR moderator_status = -1)", $uid, $prof );
        return !empty( $nVal );
    }
    
    /**
     * Получаем данные по ключам 
     *
     * @param string  $key        Строка ключей через запятую
     * @param boolean $is_key     Возвращает не пустая ли строка 
     * @return array
     */
    function getKeys($key, &$is_key=false) {
        setlocale(LC_ALL, 'ru_RU.CP1251');
        $e = explode(",", $key);
        if(strlen(trim($key)) != "") $is_key = true;
        foreach($e as $k=>$v) {
            if($v = trim($v))
                $m[] = strtoupper($v);
        }
        if($m) {
            global $DB;
            $sql = "SELECT w.name, w.id, w.group_id FROM words w WHERE upper(name) IN (?l)"; 
            $ret = $DB->rows( $sql, $m ); 
        }
        setlocale(LC_ALL, "en_US.UTF-8");
        return $ret;
    }
    
    
    /**
     * Удаляем все ключевые слова по ИД пользователя и профессии
     *
     * @param integer $uid  ИД Пользователя
     * @param integer $prof Ид Профессии
     */
    function delUserKeys($uid, $prof) {
        global $DB;
        $sql = "DELETE FROM portf_word WHERE prof_id = ?i AND uid = ?i";
        $DB->query( $sql, $prof, $uid );
    }
    
    /**
     * Формирует JS-скрипт с переменной-массивом ключевых слов.
     *
     * @param array $load_keys   массив слов.
     * @return string   скрипт.
     */
    function getJSValue($load_keys) {
        return "var kword = [".($load_keys ? implode(",", $load_keys) : '')."];";
    }
    
    /**
     * Берем случайное значение подсказки из двух систем
     *
     * @param string $type    Поисковый тип @see classes/search/
     * @return string Случайное ключевое слово
     */
    public function getRandomSearchHint($type) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/memBuff.php");
        $mem = new memBuff();
        
        $cache_name = self::MEM_KEY_NAME . '_rand';
        if (in_array($type, array('users', 'projects'))) {
            $cache_name .= $type;
        }
        
        $result = $mem->get($cache_name);
        
        if (!$result) {
            $kw_info = $se_info = array();
            self::load(7, $kw_info);

            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/search_parser.php");
            $parser = search_parser::factory();
            $parser->getTopQueries($type, 100, $se_info);

            foreach($kw_info as $kw) $result[] = $kw['name'];
            foreach($se_info as $se) $result[] = $se['query'];
            
            $mem->set($cache_name, $result, self::MEM_TIME);
        }
        
        return $result[mt_rand(0, count($result)-1)];
    }
    
    public function getKeyById($kid) {
        global $DB;
        $sql = "SELECT name FROM words WHERE id = ?i";
        return $DB->val($sql, $kid);
    }
}
?>