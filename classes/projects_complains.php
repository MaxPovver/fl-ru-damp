<?
/**
 * класс для работы с жалобами на проекты
 */
class projects_complains
{
    
    /**
     * возвращает массив с типами жалоб
     * @param boolean $moder - true/false - для модеров или для работодателей
     * @param boolean $useCache использовать кэш
     */
    public static function getTypes ($moder = null, $useCache = true) {
        global $DB;
        $sql = '
            SELECT id, name, pos,
            (CASE WHEN moder THEN 1 ELSE 0 END) as moder, name,
            (CASE WHEN textarea THEN 1 ELSE 0 END) as textarea,
            (CASE WHEN required THEN 1 ELSE 0 END) as required,
            notkind
            FROM projects_complains_types
            WHERE deleted IS NOT TRUE';
        if ($moder === false) {
            $sql .= ' AND moder = false';
        } elseif ($moder === true) {
            $sql .= ' AND moder = true';
        }
        $sql .= ' ORDER BY pos ASC';
        if ($useCache) {
            $rows = $DB->cache(600)->rows($sql);
        } else {
            $rows = $DB->rows($sql);
        }
        return $rows;
    }
    
    /**
     * обновляет список типов жалоб кучей
     * @global type $DB
     * @param array $add новые записи
     * @param array $edit редактируемые записи
     * @param array $delete удаляемые записи
     * @param boolean $moder жалоба для модератора (true) или для работодателя (false)
     */
    public static function updateTypes ($add, $edit, $delete, $moder) {
        global $DB;
        $sql = '';
        
        $sqlAdd = 'INSERT INTO projects_complains_types (moder, name, textarea, required, pos) VALUES (?b, ?, ?b, ?b, ?i);';
        foreach($add as $addType) {
            $sql .= $DB->parse($sqlAdd, $moder, $addType['name'], $addType['textarea'], $addType['required'], $addType['pos']);
        }
        
        $sqlEdit = 'UPDATE projects_complains_types SET name = ?, textarea = ?b, required = ?b, pos = ?i WHERE id = ?i;';
        foreach($edit as $editType) {
            $sql .= $DB->parse($sqlEdit, $editType['name'], $editType['textarea'], $editType['required'], $editType['pos'], $editType['id']);
        }
        
        $sqlDelete = 'UPDATE projects_complains_types SET deleted = true WHERE id = ?i;';
        foreach($delete as $deleteType) {
            $sql .= $DB->parse($sqlDelete, $deleteType['id']);
        }
        
        $DB->query($sql);
    }
    
    
    /**
     * Возвращает название типа нарушения по ID
     * @param  int $complainTypeID ID типа нарушения
     * @param  bool $deleted отметить тип жалобы как удаленный, если он удаленный (просто дописывается в конце (этот тип жалоб удален))
     * @return string
     */
    function GetComplainType($complainTypeID, $deleted = false) {
        if (!$complainTypeID) {
            return false;
        }
        
        global $DB;
        $row = $DB->row('SELECT name, deleted FROM projects_complains_types WHERE id = ?i', $complainTypeID);
        $name = $row['name'];
        if ($row['deleted'] === 't' && $deleted) {
            $name .= ' (этот тип жалоб удален)';
        }
        return $name;
    }
    
    /**
     * Возвращает принадлежность типа нарушения модератору по ID
     * @param  int $complainTypeID ID типа нарушения
     * @return boolean
     */
    function isComplainTypeModer($complainTypeID) {
        if (!$complainTypeID) {
            return false;
        }
        global $DB;
        $moder = $DB->cache(1800)->val('SELECT moder FROM projects_complains_types WHERE id = ?i', $complainTypeID);
        return $moder == 't';
    }
    
    /**
     * Возвращает статистику по жалобам
     * @param  string $by    - тип группировки результата
     * @param  array $bounds - массив границ для построения диапазонов в режиме группировки по бюджету проекта (когда $by == 'cost')
     * @return array         - результат, одномерный массив (строка) для $by == 'from', деление про / не про, 
     *                         двумерный масcив строк для $by == 'category', топ 10 категорий
     *                         в режиме группировки по бюджету проекта - трёхмерный (сами диапазоны и результат запроса)
     */
    public static function GetComplainsStats($by = 'from', $bounds = array()) {
        global $DB;
        switch ($by) {
            case 'from': {
                // Общее количество с делением "от про / не про"
                $sql = 'SELECT SUM(CAST(u.is_pro AS INT)) AS pro, SUM(1-CAST(u.is_pro AS INT)) AS nopro
                    FROM projects_complains_counter c
                    INNER JOIN projects p ON p.id  = c.project_id
                    INNER JOIN users u    ON u.uid = p.user_id';
                $complains = $DB->row($sql);
                $complains['sum'] = array_sum($complains);
                break;
            }
            case 'category': {
                // По категориям
                $sql = 'SELECT
                    g.id AS cat_id,
                    g.name,
                    COUNT(c.id) AS cnt 
                FROM projects_complains_counter c
                INNER JOIN projects        p ON p.id         = c.project_id
                INNER JOIN project_to_spec s ON s.project_id = c.project_id
                INNER JOIN prof_group      g ON g.id         = s.category_id
                GROUP BY cat_id
                ORDER BY cnt DESC
                LIMIT 10';
                $complains = $DB->rows($sql);
                break;
            }
            case 'cost': {
                if(!$bounds) return false;
                sort($bounds);
                // Деление по бюджету
                // Подготавливаем массив диапазонов
                $bcnt = count($bounds);
                $diaps = array();
                for ($i=0; $i<=$bcnt; $i++){
                    if (isset($bounds[($i-1)])) $diaps[$i]['start']   = $bounds[($i-1)];
                    if (isset($bounds[$i]))     $diaps[$i]['end']     = $bounds[$i];
                }
                // .. текст и sql - блок
                $sql = '';
                $complains_pcost = array();
                for ($i=0; $i<=$bcnt; $i++){
                    if (!isset($diaps[$i]['start'])) {
                        $diaps[$i]['html'] = '&lt; '.$diaps[$i]['end'];
                        $sql .= 'WHEN p.cost < '.$diaps[$i]['end'].  ' THEN '.$i."\n";
                    } elseif (!isset($diaps[$i]['end'])) {
                        $diaps[$i]['html'] = '&gt; '.$diaps[$i]['start'];
                        $sql .= 'WHEN p.cost > '.$diaps[$i]['start'].' THEN '.$i."\n";
                    } else {
                        $diaps[$i]['html'] = $diaps[$i]['start'].' &mdash; '.$diaps[$i]['end'];
                        $sql .= 'WHEN p.cost BETWEEN '.$diaps[$i]['start'].' AND '.$diaps[$i]['end'].' THEN '.$i."\n";
                    }
                    // Заодно и массив вывода проинициализируем
                    $complains_pcost[$i] = 0;
                }

                $sql = 'SELECT 
                    CASE 
                        WHEN p.cost = 0 THEN -1
                        '.$sql.'
                    END AS diap,
                    COUNT(c.id) AS cnt 
                FROM projects_complains_counter c
                INNER JOIN projects p ON p.id  = c.project_id
                INNER JOIN users u    ON u.uid = p.user_id
                GROUP BY diap';
                $result = $DB->rows($sql);

                foreach ($result as $val) {
                    $complains_pcost[($val['diap'] == -1 ? 'd' : $val['diap'])] = $val['cnt'];
                }
                $complains = array('diaps' => $diaps, 'result' => $complains_pcost);
                break;
            }
            default:
                $complains = false;
                break;
        }
        return $complains;
    }
    
}

?>
