<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");

/**
 * Класс для работы по интеграции с сайтом www.hh.ru
 */
class hh
{
    const FILTER_LIFE = 60; // время жизни сохраненных фильтров (и ссылок на них)
    const MEM_LIFE = 600; // время жизни мемкэша для большинства запросов.

    /**
     * Конвертер HH-валют в наши (freelancer.cost)
     * @var array
     */
    static $hh_currency2ex = array(   
      'RUR'=>freelancer::RUR,
      'USD'=>freelancer::USD,
      'EUR'=>freelancer::EUR
    );
    
    /**
     * Получает карту ключей наших и HH-специализаций для админки
     * @return array
     */
	function getSpecsMap() {
        global $DB;
        $ret = array();

		$sql = "
		  SELECT hs.id as hh_spec_id, hf.id as hh_field, sp.prof_id, hf.name as hh_field_name, p.name as prof_name, hs.name as hh_spec_name, pg.name as group_name
		    FROM hh_specializations hs
		  INNER JOIN
		    hh_fields hf
		      ON hf.id = hs.field
		  LEFT JOIN
		    hh_specializations_professions sp
		      ON sp.hh_spec_id = hs.id
		  LEFT JOIN
		    professions p
		  INNER JOIN
		    prof_group pg
		      ON pg.id = p.prof_group
		      ON p.id = sp.prof_id
		   WHERE hs.is_ignored = false
		   ORDER BY hf.id, hs.id, p.name
		";
        $rows = $DB->rows($sql);
		foreach($rows as $r) {
		    if(!$ret[$r['hh_field']])
		        $ret[$r['hh_field']] = $r;
     	    if($r['hh_spec_id']) {
     	        if(!$ret[$r['hh_field']]['specs'][$r['hh_spec_id']])
     		        $ret[$r['hh_field']]['specs'][$r['hh_spec_id']] = $r;
        	    if($r['prof_id']) {
        		    $ret[$r['hh_field']]['specs'][$r['hh_spec_id']]['profs'][$r['prof_id']] = $r;
        		}
     		}
     		else if($r['prof_id'])
     		    $ret[$r['hh_field']]['profs'][$r['prof_id']] = $r;
     		    
		}
		return $ret;
	}

    /**
     * Связать нашу специализацию с HH-специализацией или разделом
     *
     * @param integer $hh_field   ид. HH-раздела (профобласти)
     * @param integer $hh_spec_id   ид. HH-специализации (если NULL, то привязка идет к разделу)
     * @param integer $prof_id   ид. нашей специализации (professions.id)
     * @return boolean   успешно?
     */
    function addHHSpecProf($hh_field, $hh_spec_id, $prof_id) {
        global $DB;
        if(!$hh_field || !$prof_id) return false;
        return !!$DB->insert('hh_specializations_professions', array(
            'hh_field'   => $hh_field, 
            'hh_spec_id' => ($hh_spec_id? $hh_spec_id: NULL), 
            'prof_id'    => $prof_id
        ));
	}
	
    /**
     * Удалить связь между нашей специализацие и HH-разделом/специализацией
     *
     * @param integer $hh_field   ид. HH-раздела (профобласти)
     * @param integer $prof_id   ид. нашей специализации (professions.id)
     * @param integer $hh_spec_id   ид. HH-специализации (если NULL, то удалятся все связи из раздела)
     * @return boolean   успешно?
     */
    function delProf($hh_field, $prof_id, $hh_spec_id) {
        global $DB;
        $where = $DB->parse("WHERE hh_field = ? AND prof_id = ?", $hh_field, $prof_id);
        if($hh_spec_id) {
            $where .= $DB->parse(" AND hh_spec_id = ?", $hh_spec_id);
        }
        $sql = "DELETE FROM hh_specializations_professions {$where}";
        return !!$DB->query($sql);
	}
	
    /**
     * Скрыть из карты HH-специализацию (считая, что ее невозможно привязать ни к одной нашей).
     * @see hh::getSpecsMap()
     *
     * @param integer $hh_spec_id   ид. HH-специализации
     * @return boolean   успешно?
     */
    function delHHSpec($hh_spec_id) {
        global $DB;
        return !!$DB->query("UPDATE hh_specializations SET is_ignored = true WHERE id = ?", $hh_spec_id);
	}
	
    /**
     * Импортирует базу HH-специализаций и профобластей. При повторном вызове только дополняет базу новыми данными.
     * @return boolean   успешно?
     */
	function importHHCatalog() {
        global $DB;
        libxml_disable_entity_loader();
        // Профобласти
        $xml = file_get_contents('http://api.hh.ru/1/xml/field/all/');
        $sxml = simplexml_load_string($xml);
        $sql = 'INSERT INTO hh_fields (id, name) SELECT x.* FROM (';
        $i=0;
        foreach ($sxml->children() as $item)
           $sql .= ($i++ ? ' UNION ALL ' : '').'SELECT '.(int)$item['id'].", '".pg_escape_string(iconv('utf-8', 'windows-1251//TRANSLIT', $item->{'name'}))."'";
        $sql .= ') as x(id,name) LEFT JOIN hh_fields hx ON hx.id = x.id WHERE hx.id IS NULL';
        if($DB->query($sql)) {
            // Специализации
            $xml = file_get_contents('http://api.hh.ru/1/xml/specialization/all/');
            $sxml = simplexml_load_string($xml);
            $sql = 'INSERT INTO hh_specializations (id, field, name) SELECT x.* FROM (';
            $i=0;
            foreach ($sxml->children() as $item) {
               $sql .= ($i++ ? ' UNION ALL ' : '').'SELECT '.(int)$item['id'].', '.(int)$item['field'].", '".pg_escape_string(iconv('utf-8', 'windows-1251//TRANSLIT', $item->{'name'}))."'";
            }
            $sql .= ') as x(id,field,name) LEFT JOIN hh_specializations hx ON hx.id = x.id WHERE hx.id IS NULL';
            return $DB->query($sql);
        }
        return false;
	}
	
    /**
     * Импортирует базу HH-регионов. Повторный вызов полностью реимпортирует базу.
     * @return boolean   успешно?
     */
	function importHHRegions() {
        global $DB;
        libxml_disable_entity_loader();
        // Регионы
        $xml = file_get_contents('http://api.hh.ru/1/xml/region/all/');
        $sxml = simplexml_load_string($xml);
        $sql = 'INSERT INTO hh_regions (id, parent, name) SELECT x.* FROM (';
        $i=0;
        foreach ($sxml->children() as $item) {
           $sql .= ($i++ ? ' UNION ALL ' : '').'SELECT '.(int)$item['id'].', '.(int)$item['parent'].", '".pg_escape_string(iconv('utf-8', 'windows-1251//TRANSLIT', $item->{'name'}))."'";
        }
        $sql .= ') as x(id,parent,name) LEFT JOIN hh_regions hx ON hx.id = x.id WHERE hx.id IS NULL';
        if($DB->query($sql)) {
            $sql = "
              TRUNCATE hh_regions_country;
              TRUNCATE hh_regions_city;
              TRUNCATE hh_regions_groups;
              INSERT INTO hh_regions_country (hh_region_id, country_id)
              SELECT DISTINCT COALESCE(hh.id, 1001) as hh_region_id, c.id as country_id
                FROM country c
              LEFT JOIN
                hh_regions hh
                  ON c.country_name ILIKE hh.name
                 AND hh.id > 0;
              INSERT INTO hh_regions_city (hh_region_id, city_id)
              SELECT DISTINCT hh.id as hh_region_id, c.id as city_id
                FROM city c
              INNER JOIN
                hh_regions hh
                  ON c.city_name ILIKE hh.name
                 AND hh.id > 0;
              INSERT INTO hh_regions_groups (hh_parent, hh_id, nest)
              WITH RECURSIVE parents (parent, id, n) as (
                SELECT parent, id, 0 FROM hh_regions
                UNION ALL
                SELECT r.parent, p.id, n+1
                  FROM parents p
                INNER JOIN
                  hh_regions r
                   ON r.id = p.parent
              )
              SELECT parent, id, n FROM parents WHERE parent > 0
            ";
            return !!$DB->query($sql);
        }
        return false;
	}

    /**
     * Импортирует базу HH-валют. Повторный вызов полностью реимортирует базу, обновляет курсы валют.
     * @return boolean   успешно?
     */
	function importHHCurrency() {
        global $DB;
        libxml_disable_entity_loader();
	
        // Валюты
        $xml = file_get_contents('http://api.hh.ru/1/xml/currency/');
        $sxml = simplexml_load_string($xml);
        $sql = 'TRUNCATE TABLE hh_currency; INSERT INTO hh_currency (code, name, rate, freelancer_code) SELECT x.* FROM (';
        $i=0;
        foreach ($sxml->children() as $item) {
           $sql .= ($i++ ? ' UNION ALL ' : '')."SELECT '".(string)$item->{'code'}."', '".pg_escape_string(iconv('utf-8', 'windows-1251//TRANSLIT', $item->{'name'}))."', ".$item->{'rate'}.', ' . (int)hh::$hh_currency2ex[(string)$item->{'code'}];
        }
        $sql .= ') as x';
        return !!$DB->query($sql);
	}

    /**
     * Возвращает идентификаторы стран и городов нашей базы по заданным ид. HH-регионов.
     *
     * @param array|string $ids   идендификаторы регионов (массив или строка с нечисловыми разделителями)
     * @return array   [страны, города]
     */
    function getCCByHHRegions($ids) {
        if(!is_array($ids)) $ids = preg_split('/\D+/', $ids);
        $ids = intarrPgSql($ids);
        if($ids) {
            $country = $this->getCountryByHHRegions($ids, $country_ids);
            foreach($ids as $k=>$id) {
                if($country_ids[$id])
                    unset($ids[$k]);
            }
            $city = $this->getCityByHHRegions($ids);
        }
        return array($country, $city);
    }

    /**
     * Возвращает идентификаторы стран нашей базы по заданным ид. HH-регионов.
     *
     * @param array|string $ids   идендификаторы регионов (массив или строка с нечисловыми разделителями)
     * @param array $fids   возвращает массив ключей, по которым были найдены соот. страны.
     * @return array   массив ид. стран
     */
    function getCountryByHHRegions($ids, &$fids = NULL) {
        global $DB;
        if(!is_array($ids)) $ids = preg_split('/\D+/', $ids);
        $ids = implode(',', intarrPgSql($ids));
        $ret = NULL;
        if($ids) {
            $fids = array();
            $sql = "SELECT hh_region_id, country_id FROM hh_regions_country WHERE hh_region_id IN ({$ids})";
            $memBuff = new memBuff();
            if($rows = $memBuff->getSql($error, $sql, self::MEM_LIFE)) {
                foreach($rows as $row) {
                    $fids[$row['hh_region_id']] = 1;
                    $ret[] = $row['country_id'];
                }
            }
        }
        return $ret;
    }

    /**
     * Возвращает идентификаторы городов нашей базы по заданным ид. HH-регионов.
     *
     * @param array|string $ids   идендификаторы регионов (массив или строка с нечисловыми разделителями)
     * @return array   массив ид. городов
     */
    function getCityByHHRegions($ids) {
        global $DB;
        if(!is_array($ids)) $ids = preg_split('/\D+/', $ids);
        $ids = implode(',', intarrPgSql($ids));
        $ret = NULL;
        if($ids) {
            $sql = "
              SELECT city_id FROM hh_regions_city WHERE hh_region_id IN ({$ids}) UNION
              SELECT city_id FROM hh_regions_groups rg INNER JOIN hh_regions_city rc ON rc.hh_region_id = rg.hh_id WHERE rg.hh_parent IN ({$ids})
            ";
            $memBuff = new memBuff();
            if($rows = $memBuff->getSql($error, $sql, self::MEM_LIFE)) {
                foreach($rows as $row)
                    $ret[] = $row['city_id'];
            }
        }
        return $ret;
    }

    /**
     * Возвращает все наши профессии привязанные к определенному HH-разделу (профобласти)
     *
     * @param array $ids   ид. HH-разделов (массив или строка ид. разделенных запятыми)
     * @return array   массив с индексами-идентификаторами найденных профессий
     */
    function getProfessionsByHHFields($ids) {
        global $DB;
        if(!is_array($ids)) $ids = preg_split('/\D+/', $ids);
        $ids = implode(',', intarrPgSql($ids));
        $sql = "SELECT DISTINCT prof_id FROM hh_specializations_professions WHERE hh_field IN ({$ids})";
        $memBuff = new memBuff();
        if($rows = $memBuff->getSql($error, $sql, self::MEM_LIFE)) {
            foreach($rows as $row)
                $ret[$row['prof_id']] = 1;
        }
        return $ret;
    }

    /**
     * Возвращает все наши профессии привязанные к определенной HH-специализации
     *
     * @param array $ids   ид. HH-специализаций (массив или строка ид. разделенных запятыми)
     * @return array   массив с индексами-идентификаторами найденных профессий
     */
    function getProfessionsByHHSpecs($ids) {
        global $DB;
        if(!is_array($ids)) $ids = preg_split('/\D+/', $ids);
        $ids = implode(',', intarrPgSql($ids));
        $sql = "SELECT DISTINCT prof_id FROM hh_specializations_professions WHERE hh_spec_id IN ({$ids})";
        $memBuff = new memBuff();
        if($rows = $memBuff->getSql($error, $sql, self::MEM_LIFE)) {
            foreach($rows as $row)
                $ret[$row['prof_id']] = 1;
        }
        return $ret;
    }

    /**
     * Возвращает информацию по заданной HH-валюте
     *
     * @param string $code   код валюты (USD, UAH и т.д.).
     * @return array
     */
    function getHHCurrency($code) {
        global $DB;
        $sql = "SELECT * FROM hh_currency WHERE code ILIKE '{$code}'";
        $memBuff = new memBuff();
        if($rows = $memBuff->getSql($error, $sql, self::MEM_LIFE))
            $ret = $rows[0];
        return $ret;
    }

    /**
     * Возвращает упакованный фильтр для удобного хранения в виде строки.
     * 
     * @param  array $filter фильтр
     * @return string
     */
    function packFilter($filter) {
        return base64_encode(serialize($filter));
    }

    /**
     * Возвращает распакованный фильтр
     * 
     * @param  string $filter фильтр упакованный packFilter
     * @return array
     */
    function unpackFilter($filter) {
        return unserialize(base64_decode($filter));
    }

    /**
     * Сохраняет фильтр по запросу соискателей с сайта HH. Возвращает значение параметра ?hhf для GET-запроса найденных фрилансеров в каталоге.
     * @see externalApi_Hh::x____getFrlCount()
     * @see hh::_encodeFilterLink()
     *
     * @param array $filter   массив параметро фильтра
     * @return string   ссылка
     */
    function saveFilter($filter) {
        global $DB;
        $fdata = $this->packFilter($filter);
        $sql = "INSERT INTO hh_filters (filter) VALUES ('{$fdata}') RETURNING *";
        if($row = $DB->row($sql))
            return $this->_encodeFilterLink($row['id'], $row['last_used']);
        return NULL;
    }

    /**
     * Из значения параметра ?hhf извлекает и декодирует в массив фильтр для получения каталога фрилансеров.
     * @see freelancer::getCatalog()
     *
     * @param string $flink   значение параметра (код фильтра).
     * @return array   параметры фильтра.
     */
    function getFilterByLink($flink) {
        global $DB;
        $this->_decodeFilterLink($flink, $id, $last_used);
        if(!$id) return NULL;
        $sql = 'SELECT *, now()::date - last_used as last_ago FROM hh_filters WHERE id = ?';
        if($row = $DB->row($sql, $id)) {
            if($row['last_ago'] > 0) {
                $sql = 'UPDATE hh_filters SET last_used = now()::date WHERE id = ?';
                $DB->query($sql, $id);
            }
            return $this->unpackFilter($row['filter']);
        }
        return NULL;
    }

    /**
     * Удаляет устаревшие фильтры из базы.
     * @return boolean
     */
    function delOldFilters() {
        global $DB;
        $sql = 'DELETE FROM hh_filters WHERE last_used < now()::date - ?i';
        return !!$DB->query($sql, hh::FILTER_LIFE);
    }

    /**
     * Кодирует ид. фильтра в спец. код фильтра
     *
     * @param integer $id   ид. фильтра
     * @param string $last_used   дата последнего использования фильтра (на всякий случай, на потом)
     * @return string   код фильтра.
     */
    function _encodeFilterLink($id, $last_used) {
        return base64_encode($id.'@'.$last_used);
    }

    /**
     * Декодирует код фильтра, извлекая ид. фильтра и дату последнего использования
     *
     * @param string $flink   код фильтра (значение параметра ?hhf).
     * @param integer $id   вернет ид. фильтра
     * @param string $last_used   вернет дату последнего использования фильтра
     */
    function _decodeFilterLink($flink, &$id, &$last_used) {
        list($id, $last_used) = explode('@', base64_decode($flink));
        $id = (int)$id;
    }
}
?>
