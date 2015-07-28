<?

/**
 * подключаем файл с основными функциями
 *
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

/**
 *
 * Класс для работы с системой документов (docs)
 *
 */
class docs {

    /**
     * Взять документов
     * 
     * @param  int опционально ID раздела документов
     * @return array Данные выборки
     */
    public static function getDocs($section_id = false) {
        global $DB;
        
        if ( $section_id ) {
        	$data = $DB->rows( 'SELECT D.*, S.name AS section_name FROM docs D
                        INNER JOIN docs_sections S ON (D.docs_sections_id = S.id)
                        WHERE docs_sections_id = ?i ORDER BY sort', $section_id );
        }
        else {
            $data = $DB->rows( 'SELECT D.*, S.name AS section_name  FROM docs D
                        INNER JOIN docs_sections S ON (D.docs_sections_id = S.id)
                        ORDER BY S.sort, sort' );
        }
        
        if ($data)
            foreach ($data as &$item) {
                $item['attach'] = docs_files::getDocsFiles($item['id']);
            }
        return $data;
    }

    /**
     * Взять последние $limit документов
     * 
     * @param  int $limit количество документов
     * @return array Данные выборки
     */
    public static function getLast($limit = 10) {
        global $DB;
        
        return $DB->rows( 'SELECT D.*, S.name AS section_name  FROM docs D
                        INNER JOIN docs_sections S ON (D.docs_sections_id = S.id)
                        ORDER BY date_create DESC LIMIT ?i', $limit );
    }

    /**
     * Взять определенный документ
     *
     * @param integer $id ИД документа
     * @return array Данные выборки
     */
    public static function getDoc($id) {
        global $DB;
        $data = $DB->row( 'SELECT * FROM docs WHERE id = ?i', $id );
        $data['attach'] = docs_files::getDocsFiles($id);
        return $data;
    }

    /**
     * Добавить документ в БД
     *
     * @param string  $name    Вопрос
     * @param string  $desc    Описание
     * @param string  $section_id      ID секции
     * @return mixed  false - ошибка, id-секции в случае успеха
     */
    public static function Add($name, $desc, $section_id) {
        if(!trim($name)) return false;
        global $DB;
        $max = $DB->val( 'SELECT MAX(sort) as _max FROM docs WHERE docs_sections_id= ?i', $section_id );
        $iOrder = ($max) ? ($max + 1) : 1;
        $sql = "INSERT INTO docs (\"name\", \"desc\", docs_sections_id, sort) VALUES (?, ?, ?i, ?i);
                SELECT MAX(id) FROM docs AS last_insert_id;";

        $tmp = $DB->val( $sql, trim($name), $desc, $section_id, $iOrder );
        return $DB->error ? false : $tmp;
    }

    /**
     * Обновить документ
     *
     * @param string  $docs_id    ID документа
     * @param string  $name      Название
     * @param boolean $desc     Описание
     * @param integer $section_id ИД секции документа
     * @return string Сообщение об ошибке
     */
    public static function Update($docs_id, $name, $desc, $section_id) {
        if(!trim($name)) return false;
        global $DB;
        $sql = "UPDATE docs SET \"name\"=?, \"desc\"=?, docs_sections_id=?i, date_update = NOW()
                WHERE id = ?i" ;
        
        $DB->query( $sql, trim($name), $desc, $section_id, $docs_id );
        
        return $DB->error;
    }

    /**
     * Удалить Doc
     *
     * @param mixed $docs_id Ид вопроса или строка в виде id|id2|id3...
     * @return string Сообщение об ошибке
     */
    public static function Delete($id) {
        if (is_numeric($id)) {
            $files = docs_files::getDocsFiles($id);
            $file = new CFile();
            foreach ($files as $key => $value) {
                $file->Delete($value['file_id']);
            }
            
            global $DB;
            $DB->query( "DELETE FROM docs WHERE id = ?i", $id );
            
            return $DB->error;
        } else {
            foreach (explode('|', $id) as $idx) {
                if (!(int) $idx)
                    continue;
                self::Delete((int) $idx);
            }
            return false;
        }
    }

    /**
     * Перенести документ в другой раздел
     * 
     * @param mixed $docs_id Ид вопроса или строка в виде id|id2|id3...
     * @param integer $section ID радела
     * @return string Сообщение об ошибке
     */
    public static function Move($docs_id, $section) {
        global $DB;
        
        if (is_numeric($id)) {
            $DB->update( 'docs', array('docs_sections_id' => $section), 'id = ?i', $docs_id );
            return $DB->error;
        } else {
            $in = array();
            foreach (explode('|', $docs_id) as $idx) {
                if (!(int) $idx)
                    continue;
                $in[] = $idx;
            }
            if (count($in)) {
                $DB->update( 'docs', array('docs_sections_id' => $section), 'id IN (?l)', $in );
                return $DB->error;
            } else {
                return false;
            }
        }
    }

    /**
     * Фильтрация поисковой фразы
     *
     * @param string $s      поисковая фраза
     * @return string        преобразованная поискавая фраза
     */
    public static function filterQuery($s) {
        $s = strip_tags($s);
        $s = str_replace('&nbsp;', ' ', $s);
        $s = html_entity_decode($s, ENT_QUOTES, 'cp1251');
        $s = self::filterStopWords($s);
        $s = preg_replace('/\s+/', ' ', $s);
        $s = trim($s);
        return $s;
    }

    /**
     * Удаление из поисковой фразы союзов, предлогов и т.д.
     *
     * @param string $s      поисковая фраза
     * @return string        поисковая фраза без предлогов, союзов и т.д.
     */
    public static function filterStopWords($s) {
        $stopWords = array(
            'а',
            'без', 'более', 'бы', 'был', 'была', 'были', 'было', 'быть',
            'в', 'вам', 'вас', 'весь', 'во', 'вот', 'все', 'всего', 'всех', 'вы',
            'где',
            'да', 'даже', 'для', 'до',
            'его', 'ее', 'если', 'есть', 'еще',
            'же',
            'за', 'здесь',
            'и', 'из', 'или', 'им', 'их',
            'к', 'как', 'ко', 'когда', 'кто',
            'ли', 'либо',
            'мне', 'может', 'мы',
            'на', 'надо', 'наш', 'не', 'него', 'нее', 'нет', 'ни', 'них', 'но', 'ну',
            'о', 'об', 'однако', 'он', 'она', 'они', 'оно', 'от', 'очень',
            'по', 'под', 'при',
            'с', 'со',
            'так', 'также', 'такой', 'там', 'те', 'тем', 'то', 'того', 'тоже', 'той', 'только', 'том', 'ты',
            'у', 'уже',
            'хотя',
            'чего', 'чей', 'чем', 'что', 'чтобы', 'чье', 'чья',
            'эта', 'эти', 'это',
            'я', ','
        );
        foreach ($stopWords as $w) {
            $s = preg_replace('/(?<!\pL)' . $w . '(?!\pL)/', '', $s);
        }
        $s = preg_replace("/\. /", " ", $s);
        $s = preg_replace("/\(/", "", $s);
        $s = preg_replace("/\)/", "", $s);
        $s = preg_replace("/«/", "", $s);
        $s = preg_replace("/»/", "", $s);
        $s = preg_replace("/\"/", "", $s);
        return $s;
    }

    /**
     * Преобразование текста ответа для вывода c подсветкой слов
     *
     * @param    string  $s          текст ответа
     * @param    boolean $is_short   true - не обрезать текст, false - оставить только текст рядом с подсвечиваемым словом
     * @param    string  $query      слово, которое необходимо подсветить
     * @param    boolean is_highlight    нужно ли подсвечивать слово
     * @return   string              текст с подсветкой слов
     */
    public static function cut($s, $is_short = false, $query = null, $is_highlight=true) {
        setlocale(LC_ALL, 'ru_RU.CP1251');
        $ret = '';
        $rw = "A-Za-z0-9А-Яа-я_.;&@";
        if (empty($s)) {
            return $ret;
        }
        $query = self::filterQuery($query);
        $qParams = preg_split('/[\s,:!?)(_]/u', $query);
        $temp = array();
        foreach ($qParams as $q) {
            if (!empty($q)) {
                $temp[] = $q;
            }
        }
        $qParams = $temp;
        unset($temp);

        if ($is_short) {
            foreach ($qParams as $q) {
                $pos = stripos($s, $q);
                if (!$pos)
                    return '';
                if ($pos > 200) {
                    $lpos = @strpos($s, ' ', $pos - 100);
                    $rpos = @stripos($s, ' ', $pos + 100);
                    if (empty($lpos))
                        $lpos = 0;
                    if (empty($rpos))
                        $rpos = strlen($s);
                    $ret = substr($s, $lpos, ($rpos - $lpos));
                    if ($lpos != 0)
                        $ret = '... ' . $ret;
                    if ($rpos != strlen($s))
                        $ret = $ret . ' ...';
                } else {
                    if (strlen($s) > 200) {
                        $ret = substr($s, 0, strpos($s, ' ', 200) - 1);
                        $ret .= ' ...';
                    } else {
                        $ret = $s;
                    }
                }
            }
        } else {
            foreach ($qParams as $q) {
                $ret = $s;
            }
        }

        if ($is_highlight) {
            $ret = preg_replace('/(?<!\pL)([' . $rw . '-]*' . preg_quote($q, '/') . '[' . $rw . '-]*)(?!\pL)/i', '=====s=====\\1=====e=====', $ret);
            $ret = preg_replace("/=====s=====/", '<strong class="help-colored">', $ret);
            $ret = preg_replace("/=====e=====/", '</strong>', $ret);
        }
        return $ret;
    }

    /**
     * Возвращает документы соответствующие поисковому запросу.
     * 
     * @param string $query - строка для поиска
     * @return mixed результат поиска
     */
    public static function Search($query) {
        $text = self::filterQuery($query);
        $s_texts = explode(" ", $text);
        foreach ($s_texts as $s_word) {
            $s_word = trim(pg_escape_string(DBConnect(), $s_word));
            // В заголовке есть все три слова
            $sql_1 .= "LOWER(h.\"name\") LIKE LOWER('%$s_word%') AND ";
            // В заголовке есть хотя бы одно из слов.
            $sql_3 .= "LOWER(h.\"name\") LIKE LOWER('%$s_word%') OR LOWER(h.\"desc\") LIKE LOWER('%$s_word%') OR ";
        }
        $sql_1 = preg_replace("/AND $/", "", $sql_1);
        $sql_3 = preg_replace("/OR $/", "", $sql_3);
        // В тексте фраза существует полностью (наличие и порядок слов совпадают)
        $sql_2 = "LOWER(h.desc) LIKE LOWER('%" . pg_escape_string(DBConnect(), $text) . "%') ";
        $sql = "SELECT h.*
                 FROM docs AS h
                 WHERE ($sql_1)
                UNION ALL
                SELECT h.*
                 FROM docs AS h
                 WHERE ($sql_2)
                UNION ALL
                SELECT h.*
                 FROM docs AS h
                 WHERE ($sql_3)";
        
        global $DB;
        $results = $DB->rows( $sql );
        
        if ( count($results) ) {
            $idx = array();
            while (list($key, $result) = each($results)) {
                if (in_array($result['id'], $idx)) {
                    unset($results[$key]);
                    continue;
                } else {
                    array_push($idx, $result['id']);
                }
                $text = strip_tags(strtr($result['desc'],array('&nbsp;' => ' ',
                    '&laquo;' => '"',
                    '&raquo;' => '"',
                    '&quot;' => '"',)));
                reset($s_texts);
                $n = 0;
                foreach ($s_texts as $word) {
                    $mode = ($n == 0) ? true : false;
                    $r_text = self::cut($text, $mode, $word, false);
                    if (!$r_text) {
                        $n = 0;
                    } else {
                        $text = $r_text;
                        $n = 1;
                    }
                }
                reset($s_texts);
                foreach ($s_texts as $word) {
                    $text = self::cut($text, false, $word, true);
                }
                $results[$key]['desc'] = $text;
            }
            return $results;
        } else {
            return false;
        }
    }

}