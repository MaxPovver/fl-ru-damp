<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/project_exrates.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/smail.php';
/**
 * Класс для массовой рассылки сообщений в системе
 *
 */
class masssending {

	/**
	 * Код операции
	 *
	*/
	const OPER_CODE = 45;

	/**
	 * Возвращаемыый код операции
	 *
	 */
	const OPER_CODE_RETURN = 46;

	/**
	 * Режимы сортировки
	 * Сортировка по новизне 
	 * 
	 */
	const OM_NEW      = 0x0;
  
	/**
	 * Сортировка по доступности
	 *
	 */
	const OM_ACCEPTED = 0x1;
  
	/**
	 * Сортировка по недоступным
	 *
	 */
	const OM_DENIED   = 0x2;
  
	/**
	 * Сортировка по старине (по дате в обратном порядке)
	 *
	 */
	const OM_OLD      = 0x3;
	
	/**
	 * Максимальное количество прикрелпенных файлов
	 *
	 */
	const MAX_FILES = 10;

	/**
	 * Максимальный размер одного прикрепленного файла
	 *
	 */
	const MAX_FILE_SIZE = 5242880; //5Mb

	/**
	 * Время жизни сессии
	 *
	 */
	const SESS_TTL = 5400;
  
	/**
	 * Возникшая ошибка
	 *
	 * @var string
	 */
	public $error = '';
	/**
	 * ID текущего тарифа. Если NULL, то используется последний тариф.
	 *
	 * @var integer
	 */
	public $tariff_id = NULL;
    
    /**
     * Экзумпляр класса billing
     * @var type 
     */
    public $billing = null;

    
    
    /**
     * Флаги дополнительный активных
     * способов расчета количество пользователей
     * 
     * Поумолчанию активны все способы
     * 
     * @var type 
     */
    protected $calc_methods = array(
        //@todo: PRO всегда используется
        //'pro' => true, //отдельно считаем ПРО
        'locations' => true, //отдельно для каждого места положения страна/город
        'professions' => true //отдельно для каждого раздела
    );


    /**
     * Установить активные методы расчета
     * 
     * @param type $methods
     * @return \masssending
     */
    public function setCalcMethods($methods = array())
    {
        if ($methods) {
            $methods = is_array($methods)?$methods:array($methods);
            foreach ($this->calc_methods as $key => $value) {
                $this->calc_methods[$key] = isset($methods[$key]);
            }
        }
        
        return $this;
    }

    
    /**
     * Проверить активен ли метод расчета
     * 
     * @param type $name
     * @return type
     */
    protected function isCalcMethond($name)
    {
        return isset($this->calc_methods[$name]) && $this->calc_methods[$name];
    }

    



    /**
	 * Метод может использоваться в двух случаях:
	 * 1. Для подсчета количества пользователей и стомости рассылки исходя из заданного фильтра.
	 * 2. Расчитать (без дополнительно расчета каталога и городов) количество пользователей и стоимость + сохранить список пользователей в mass_sending_users
	 * @param  integer  $uid      uid пользователя совершаемого рассылку
	 * @param  array    $params   массив с данными фильтра фрилансеров
	 * @param  commit   integer   если не 0, то сохранит всех найденых пользователей для рассылки $commit иначе просто расчет
	 * @return          array     результат расчета в виде 
	 *                            array('count', 'cost', 'pro'=>array('count', 'cost'), locations=>array(array('city', 'country', 'count', 'cost')), professions=>array(array('group', 'profession', 'count', 'cost')))
	 *
	 */
	public function Calculate($uid, array $params, $commit=0) 
    {
        global $DB;
        
		$result = array('count'=>0, 'cost'=>0, 'pro'=>array('count'=>0, 'cost'=>0), 'professions'=>array(), 'locations'=>array());
		$cost = $this->GetTariff($this->tariff_id);

		$memBuff = new memBuff();
        $memBuffGroup = 'massending_calc';

		$ow = $cw = "";
		$op = $cp = array();
		$tmp = array();
		$profs = $profsgr = array(); 
		$jn = $wh = $whc = $whl = "";


        $wh .= " AND u.subscr & B'0000000000001000' = B'0000000000001000'";
        
        
        //----------------------------------------------------------------------
        
        
		// у меня в избранных
        if ( !empty($params['favorites']) ) {
            $dbProxy = new DB('plproxy');
            $targets = $dbProxy->col("SELECT target_id FROM teams(?)", $uid);
            if ( $targets ) {
                $wh .= $dbProxy->parse(" AND u.uid IN (?l)", $targets);
            }
        }
        
        //----------------------------------------------------------------------
        
		// только свободные
		if (!empty($params['free'])) $wh .= " AND u.status_type = 0";
        
        //----------------------------------------------------------------------
        
        
        // с верифицированым аккаунтом
        if(!empty($params['opi_is_verify'])) $wh .= ' AND (u.is_verify = true)';
        
        //----------------------------------------------------------------------
        
        
		// с примерами работ
		//if ($params['portfolio']) $wh .= " AND EXISTS(SELECT 1 FROM portfolio WHERE user_id = u.uid)";
		if (!empty($params['portfolio'])) $jn = " INNER JOIN rating r ON r.user_id = u.uid AND r.o_wrk_factor_a > 0";
        
        //----------------------------------------------------------------------
        
		// с успешными сбр и фрилансреами 1/2/3 разрядов
		if (!empty($params['sbr'])) {
			if (!$jn) $jn = " INNER JOIN rating r ON u.uid = r.user_id";
			if (!empty($params['discharge3'])) {
				$discharge = 3;
			} else if (!empty($params['discharge2'])) {
				$discharge = 2;
			} else if (!empty($params['discharge1'])) {
				$discharge = 1;
			} else {
				$discharge = 0;
			}
			if ($discharge) {
				$jn .= " AND r.rank >= {$discharge}";
			} else {
				$jn .= " AND r.sbr_count > 0 ";
			}
		}
        

        //----------------------------------------------------------------------
        
        
        $uc_where = '';
		// с положительными рекомендациями
		//if (!empty($params['sbr_is_positive'])) $uc_where .= " AND uc.sbr_opi_plus > 0";
		// без негативных рекомендация
		//if (!empty($params['sbr_not_negative'])) $uc_where .= " AND uc.sbr_opi_minus = 0";
        // с положительными отзывами
        if(!empty($params['opi_is_positive'])) $uc_where .= ' AND ((uc.ops_emp_plus + uc.ops_frl_plus + uc.sbr_opi_plus) > 0)';
        // без негативных отзывами
        if(!empty($params['opi_not_negative'])) $uc_where .= ' AND ((uc.ops_emp_minus + uc.ops_frl_minus + uc.sbr_opi_minus) = 0 OR uc.user_id IS NULL)';

        if ($uc_where) {
            $wh .= $uc_where;
            $jn .= "LEFT JOIN users_counters uc ON uc.user_id = u.uid";
        }
        
        
        
        //----------------------------------------------------------------------
        
        
		// ищет работу в офисе
		if ($params['inoffice']) $wh .= " AND u.in_office = 't'";        
        
        
        //----------------------------------------------------------------------
        
        
		// стоимость
		if ((!empty($params['cost_from']) && is_array($params['cost_from'])) || (!empty($params['cost_to']) && is_array($params['cost_to']))) {
			$exrates = project_exrates::GetAll();
			$cex = array(2,3,4,1); 
			$tmp = '';
			foreach ($params['cost_from'] as $i=>$val) {
				if (!$params['cost_from'][$i] && !$params['cost_to'][$i]) continue;
				$type = (isset($params['cost_type'][$i]) && in_array($params['cost_type'][$i], array(0, 1, 2, 3)))? $params['cost_type'][$i]: 0;
				if (isset($params['cost_period'][$i]) && $params['cost_period'][$i] == 'month') {
					$ct = 'u.cost_type_month';
					$cc = 'u.cost_month';
				} else {
					$ct = 'u.cost_type_hour';
					$cc = 'u.cost_hour';
				}
				$cost_from =  floatval(str_replace(array(' ', ','), array('', '.'), $params['cost_from'][$i])) * $exrates[$cex[$type].'1'];
				$cost_to = floatval(str_replace(array(' ', ','), array('', '.'), $params['cost_to'][$i])) * $exrates[$cex[$type].'1'];
				$s = "(CASE WHEN $ct = 0 THEN {$exrates[$cex[0].'1']} WHEN $ct = 1 THEN {$exrates[$cex[1].'1']} WHEN $ct = 2 THEN {$exrates[$cex[2].'1']} WHEN $ct = 3 THEN {$exrates[$cex[3].'1']} END)";
				if ($cost_to > $cost_from || !$cost_to || !$cost_from) {
					$s = ($cost_from? " AND ($cc * $s) >= $cost_from ": "") . ($cost_to? " AND ($cc * $s) <= $cost_to": "");
				} else {
					$s = ($cost_from? " AND ($cc * $s) <= $cost_from ": "") . ($cost_to? " AND ($cc * $s) >= $cost_to": "");
				}
				$tmp .= ' OR ('.substr($s, 5).')';
			}
			if ($tmp) $wh .= ' AND ('.substr($tmp, 4).')';
		}
        
        
        //----------------------------------------------------------------------
        
        
		// опыт в годах
		if (intval($params['expire_from']) || intval($params['expire_to'])) {
			$f = intval($params['expire_from']);
			$t = intval($params['expire_to']);
			if ($f && $t && $f > $t) list($f,$t) = array($t,$f);
			//if ($f) $wh .= " AND ((regexp_replace(u.exp, '^([0-9]+)?.*', E'\\\\1')) <> '' AND (regexp_replace(u.exp, '^([0-9]+)?.*', E'\\\\1'))::int >= $f)";
			//if ($t) $wh .= " AND ((regexp_replace(u.exp, '^([0-9]+)?.*', E'\\\\1')) <> '' AND (regexp_replace(u.exp, '^([0-9]+)?.*', E'\\\\1'))::int <= $t)";
			if ($f) $wh .= " AND u.exp >= $f";
			if ($t) $wh .= " AND u.exp <= $t";
		}
        
        
        
        //----------------------------------------------------------------------
        
        
		// только pro
		if (!empty($params['is_pro'])) $wh .= " AND u.is_pro = 't'";
        
        
        //----------------------------------------------------------------------

        
		// меторасположение
		if (!empty($params['locations']) && is_array($params['locations'])) {
			$tmp = '';
			$tmpc = array();
			foreach ($params['locations'] as $location) {
				if (preg_match("/^([0-9]{1,10})\:([0-9]{1,10})$/", $location, $o)) {
					if ($o[2]) {
						if (empty($tmpc["{$o[1]}:{$o[2]}"])) $tmpc["{$o[1]}:{$o[2]}"] = 1; else continue;
						$cw .= " OR (u.country = {$o[1]} AND u.city = {$o[2]})";
						$tmp .= " OR (u.country = {$o[1]} AND u.city = {$o[2]})";
					} else {
						if (empty($tmpc["{$o[1]}:0"])) $tmpc["{$o[1]}:0"] = 1; else continue;
						$ow .= " OR (u.country = {$o[1]})";
						$tmp .= " OR (u.country = {$o[1]})";
					}
				}
			}
			if ($tmp) $whl = " AND (".substr($tmp, 4).")";
		}
        
        
        //----------------------------------------------------------------------
        
        
		// разделы в каталоге
		if (!empty($params['professions']) && is_array($params['professions'])) {
			$tmpc = array();
			foreach ($params['professions'] as $profession) {
				if (preg_match("/^([0-9]{1,10})\:([0-9]{1,10})$/", $profession, $o)) {
					if ($o[2]) {
						if (empty($tmpc["{$o[1]}:{$o[2]}"])) $tmpc["{$o[1]}:{$o[2]}"] = 1; else continue;
						$cp[$o[2]] = array($o[1], $o[2]);
					} else {
						if (empty($tmpc["{$o[1]}:0"])) $tmpc["{$o[1]}:0"] = 1; else continue;
						$op[] = $o[1];
					}
				}
			}
		}

        
		// подготовка данных, если указаны разделы каталога
		if ($op || $cp) {
            
            // если группа и раздел
			if ($cp) {
				$tmp = array();
				foreach ($cp as $k=>$v) {
					if (in_array($v[0], $op)) unset($cp[$k]); else $tmp[] = $v[1];
				}
				if (!empty($cp)) {
					$res = $DB->query('SELECT main_prof, mirror_prof FROM mirrored_professions WHERE mirror_prof IN (?l)', $tmp);
					while ($row = pg_fetch_assoc($res)) {
						$profs[] = $row['main_prof'];
						$cp[$row['mirror_prof']][] = $row['main_prof'];
					}
					foreach ($cp as $v) {
						if (empty($v[2])) $profs[] = $v[1];
					}
				}
			}
            
			// если указаны только группы разделов
			if ($op) {
				$res = $DB->query('SELECT prof_group, id, main_prof FROM professions LEFT JOIN mirrored_professions ON mirror_prof = id WHERE prof_group IN (?l)', $op);
				$tmp = array();
				while ($row = pg_fetch_assoc($res)) {
					$profsgr[] = $row['id'];
					$tmp[$row['prof_group']] = 1;
					if ($row['main_prof']) {
						$profsgr[] = $row['main_prof'];
					}
				}
				$op = array_keys($tmp);
			}
            
            $in_ids = implode(array_unique(array_merge($profs, $profsgr)), ',');
			//@todo: здесь лучше бы избавится от подзапроса, но походу никак
            //JOIN не быстрее да и другие результаты выдает
			$whc = " AND (u.spec_orig IN ({$in_ids})
            OR (u.is_pro = TRUE AND EXISTS(
                SELECT 1 FROM
                    spec_add_choise
                WHERE user_id = u.uid AND prof_id IN ({$in_ids})
			)))";
                
		} else {
			$whc = " AND u.spec_orig IS NOT NULL AND u.spec_orig > 0";
		}
        
        
        
        //----------------------------------------------------------------------
        
        
        
		// если указано меторасположение, то оно обробатывается своими запросами
		if (($cw || $ow) && !$commit && 
            $this->isCalcMethond('locations')) {
            
			$locations = array();
			$haveTheir = array();
			// страны без городов
			if ($ow) {
				$sql = "SELECT country, is_pro, COUNT(*) AS cnt FROM freelancer u {$jn} WHERE is_banned = '0' {$whc}{$wh} AND (".substr($ow, 4).") GROUP BY country, is_pro";

                if (!($rows = $memBuff->getSql($error, $sql, 600, false, $memBuffGroup))) $rows = array();

				foreach ($rows as $row) {
					$c = "{$row['country']}:0";
					if (empty($locations[$c])) $locations[$c] = array( 'country'=>$row['country'], 'city'=>0, 'cost'=>0, 'cost'=>0, 'pro'=>array('cost'=>0, 'count'=>0) );
					$locations[$c]['count'] += $row['cnt'];
					if ($row['is_pro'] == 't') {
						$locations[$c]['cost'] += $row['cnt'] * $cost['pro'];
						$locations[$c]['pro']['count'] += $row['cnt'];
						$locations[$c]['pro']['cost']  += $row['cnt'] * $cost['pro'];
					} else {
						$locations[$c]['cost'] += $row['cnt'] * $cost['no_pro'];
					}
					$haveTheir[$row['country']] = TRUE;
				}
			}
			// страны с городами
			if ($cw) {
				$sql = "SELECT country, city, is_pro, COUNT(*) AS cnt FROM freelancer u {$jn} WHERE is_banned = '0' {$whc}{$wh} AND (".substr($cw, 4).") GROUP BY country, city, is_pro";

                if (!($rows = $memBuff->getSql($error, $sql, 600, false, $memBuffGroup))) $rows = array();

				foreach ($rows as $row) {
					$c = "{$row['country']}:{$row['city']}";
					if (empty($locations[$c])) $locations[$c] = array( 'country'=>$row['country'], 'city'=>$row['city'], 'cost'=>0, 'cost'=>0, 'pro'=>array('cost'=>0, 'count'=>0) );
					if (!empty($haveTheir[$row['country']])) $locations[$c]['no'] = 1;
					$locations[$c]['count'] += $row['cnt'];
					if ($row['is_pro'] == 't') {
						$locations[$c]['cost'] += $row['cnt'] * $cost['pro'];
						$locations[$c]['pro']['count'] += $row['cnt'];
						$locations[$c]['pro']['cost']  += $row['cnt'] * $cost['pro'];
					} else {
						$locations[$c]['cost'] += $row['cnt'] * $cost['no_pro'];
					}
				}
			}
			foreach ($locations as $k=>$v) {
				$v['cost'] = $v['cost'];
				if (empty($v['no'])) {
					$result['count'] += $v['count'];
					$result['cost']  += $v['cost'];
					$result['pro']['count'] += $v['pro']['count'];
					$result['pro']['cost']  += $v['pro']['cost'];
				}
				$result['locations'][] = $v;
			}
		}
		
        
        
        //----------------------------------------------------------------------
        
        
        
		// если указаны разделы каталога, то для них дополнительные запросы
		if (($op || $cp) && !$commit && 
            $this->isCalcMethond('professions')) {
            
			$professions = array();
            
			if ($op) {

                $profsgr = array_unique($profsgr);

                if ($profsgr) {
                    
                    $in_profsgr = implode($profsgr, ',');
                    
                    $sql = "SELECT prof_group, is_pro, COUNT(uid) AS cnt
                            FROM (
                                SELECT s.prof_group, is_pro, uid
                                FROM freelancer u
                                INNER JOIN professions s ON s.id = u.spec_orig
                                {$jn}
                                WHERE 
                                    u.is_banned = '0' 
                                    AND u.spec_orig IN({$in_profsgr}) 
                                    {$wh}
                                    {$whl}

                                UNION

                                SELECT s.prof_group, is_pro, uid
                                FROM spec_add_choise sp
                                INNER JOIN freelancer u ON sp.user_id = u.uid
                                INNER JOIN professions s ON s.id = sp.prof_id
                                {$jn}
                                WHERE 
                                    u.is_banned = '0' 
                                    AND u.is_pro = TRUE 
                                    AND sp.prof_id IN({$in_profsgr}) 
                                    {$wh}
                                    {$whl}
                            ) s
                            GROUP BY prof_group, is_pro";
                                    
                        if (!($rows = $memBuff->getSql($error, $sql, 600, false, $memBuffGroup))) $rows = array();

                        foreach ($rows as $row) {
                            if (empty($professions["{$row['prof_group']}:0"])) {
                                $professions["{$row['prof_group']}:0"] = array( 'id'=>0, 'group'=>$row['prof_group'], 'count'=>$row['cnt'], 'cost'=>($row['cnt']*(($row['is_pro']=='t')? $cost['pro']: $cost['no_pro'])) );
                            } else {
                                $professions["{$row['prof_group']}:0"]['count'] += $row['cnt'];
                                $professions["{$row['prof_group']}:0"]['cost'] += $row['cnt'] * (($row['is_pro']=='t')? $cost['pro']: $cost['no_pro']);
                            }
                        }
                
                }
			}
            

            
			if ($cp) {
                
                 $in_profs = implode($profs, ',');
                
                 $sql = "SELECT spec, is_pro, SUM(cnt) AS cnt
					FROM (
						SELECT spec_orig AS spec, is_pro, COUNT(uid) AS cnt
						FROM freelancer u
						{$jn}
						WHERE spec_orig IN ({$in_profs}) AND u.is_banned = '0' {$wh}{$whl}
						GROUP BY spec_orig, is_pro
                        
						UNION ALL
                        
						SELECT prof_id AS spec, is_pro, COUNT(uid) AS cnt
						FROM spec_add_choise sp
						INNER JOIN freelancer u ON sp.user_id = u.uid AND u.is_banned = '0' AND u.is_pro = TRUE
						{$jn}
						WHERE prof_id IN ({$in_profs}) {$wh}{$whl}
						GROUP BY prof_id, is_pro
					) s
					GROUP BY spec, is_pro
				";
                        
				if (!($rows = $memBuff->getSql($error, $sql, 600, false, $memBuffGroup))) $rows = array();
                
				foreach ($rows as $row) {
					foreach ($cp as $k=>$v) {
						if ($row['spec'] == $v[1] || (!empty($v[2]) && $row['spec'] == $v[2])) {
							if (empty($professions["{$v[0]}:{$v[1]}"])) {
								$professions["{$v[0]}:{$v[1]}"] = array('id'=>$v[1], 'group'=>$v[0], 'count'=>$row['cnt'], 'cost'=>($row['cnt']*(($row['is_pro']=='t')? $cost['pro']: $cost['no_pro'])));
							} else {
								$professions["{$v[0]}:{$v[1]}"]['count'] += $row['cnt'];
								$professions["{$v[0]}:{$v[1]}"]['cost'] += $row['cnt'] * (($row['is_pro']=='t')? $cost['pro']: $cost['no_pro']);
							}
						}
					}
				}
			}
            
			foreach ($professions as $k=>$v) {
				$v['cost'] = $v['cost'];
				$result['professions'][] = $v;
			}
		}
		
        
        
        //----------------------------------------------------------------------
        
        
        
		// подсчет общего количества, если еще не было подсета при обратки месторасположения
		if (!($cw || $ow) || $commit || 
            !$this->isCalcMethond('locations')) {
            
			$sql = "SELECT is_pro, COUNT(*) AS cnt FROM freelancer AS u {$jn} WHERE is_banned = '0' {$whc}{$whl}{$wh} GROUP BY is_pro";
            
			if (!($rows = $memBuff->getSql($error, $sql, 600, false, $memBuffGroup))) $rows = array();

			$result['count'] = 0;
			$result['cost'] = 0;
			$result['pro'] = array('count' => 0, 'cost' => 0);
            
			foreach ($rows as $row) {
				$result['count'] += $row['cnt'];
				if ($row['is_pro'] == 't') {
					$result['pro']['count'] += $row['cnt'];
					$result['pro']['cost']  += $row['cnt'] * $cost['pro'];
					$result['cost'] += $row['cnt'] * $cost['pro'];
				} else {
					$result['cost'] += $row['cnt'] * $cost['no_pro'];
				}
			}
		}
		
        
        //----------------------------------------------------------------------
        
        
		if ($commit) {
			$sql = "
				INSERT INTO mass_sending_users
				SELECT {$commit}, uid FROM freelancer u {$jn} WHERE is_banned = '0' {$whc}{$whl}{$wh} ".(($params['max_users']>0 && $params['max_cost']>0) ? 'ORDER BY u.rating DESC LIMIT '.$params['max_users'] : '')."
			";
			$DB->squery( $sql );
		}
        
		return $result;
	}
    
    
    /**
     * Подсчет получателей если пришли в рассылку из поиска исполнителей
	 * Метод может использоваться в двух случаях:
	 * 1. Для подсчета количества пользователей и стомости рассылки исходя из заданного фильтра.
	 * 2. Расчитать (без дополнительно расчета каталога и городов) количество пользователей и стоимость + сохранить список пользователей в mass_sending_users
	 * @param  integer  $uid      uid пользователя совершаемого рассылку
	 * @param  array    $param   массив с данными фильтра фрилансеров
	 * @param  commit   integer   если не 0, то сохранит всех найденых пользователей для рассылки $commit иначе просто расчет
	 * @return          array     результат расчета в виде 
	 *                            array('count', 'cost', 'pro'=>array('count', 'cost'), locations=>array(array('city', 'country', 'count', 'cost')), professions=>array(array('group', 'profession', 'count', 'cost')))
	 *
	 */
	public function CalculateFromSearch($uid, array $param, $commit=0) {
        global $DB;
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/search/search.php");
        
        if($param['exp'][0] > $param['exp'][1] && $param['exp'][1] != 0) {
            $a = $param['exp'][0];
            $param['exp'][0] = $param['exp'][1];
            $param['exp'][1] = $a;
        }

        if($param['age'][0] > $param['age'][1] && $param['age'][1] != 0) {
            $a = $param['age'][0];
            $param['age'][0] = $param['age'][1];
            $param['age'][1] = $a;
        }

        if(is_array($param['from_cost'])) {
            foreach($param['from_cost'] as $key=>$val) {
                if($val > $param['to_cost'][$key] && $param['to_cost'][$key] != 0) {
                    $a = $param['from_cost'][$key];
                    $param['from_cost'][$key] = $param['to_cost'][$key];
                    $param['to_cost'][$key] = $a;
                }
            }
        }

        if($param['action'] == "search_advanced" || $param['advanced_search']) {
            $filter = array("active"       => "t",
                            "categories"   => $param['pf_categofy'],
                            "prof"         => $param['pf_categofy'],
                            "kwords"       => $param['kword'],
                            "cost_type"    => is_array($param['cost_type']) ? array_map("intval", $param['cost_type']) : $param['cost_type'],
                            "from_cost"    => is_array($param['from_cost']) ? array_map("intval", $param['from_cost']) : $param['from_cost'],
                            "to_cost"      => is_array($param['to_cost']) ? array_map("intval", $param['to_cost']) : $param['to_cost'],
                            "curr_type"    => is_array($param['curr_type']) ? array_map("intval", $param['curr_type']) : $param['curr_type'],
                            "exp"          => is_array($param['exp']) ? array_map("intval", $param['exp']) : $param['exp'],
                            "exp_from"     => (int)$param['exp'][0],
                            "exp_to"       => (int)$param['exp'][1],
                            "login"        => htmlspecialchars($param['login']),
                            "age"          => is_array($param['age']) ? array_map("intval", $param['age']) : $param['age'],
                            "age_from"     => (int)$param['age'][0],
                            "age_to"       => (int)$param['age'][1],
                            "country"      => (int)$param['pf_country'],
                            "city"         => (int)$param['pf_city'],
                            "in_office"    => $param['in_office'],
                            "in_fav"       => $param['in_fav'],
                            "only_free"    => $param['only_free'],
                            "is_pro"       => $param['is_pro'],
                            "sbr_is_positive"  => $param['sbr_is_positive'],
                            "is_preview"   => $param['is_preview'],
                            "sbr_not_negative"  => $param['sbr_not_negative'],
                            "opi_is_positive"  => $param['opi_is_positive'],
                            "opi_not_negative"  => $param['opi_not_negative'],
                            "success_sbr"  => $param['success_sbr']);
        }

        if (!$filter) {
            $filter = array();
        }

        if($filter['cost_type']) {
            foreach($filter['cost_type'] as $key=>$value) {
                $cFilter[] = array("cost_type" => $filter['curr_type'][$key],
                                    "cost_from" => $filter['from_cost'][$key],
                                    "cost_to"   => $filter['to_cost'][$key],
                                    "type_date" => $value);
            }
            $filter['cost'] = $cFilter;
        }

        $searchString = __paramValue('htmltext', $param['search_string']);
        
        if ($filter["prof"][1] && is_array($filter["prof"][1])) {
            require_once($_SERVER['DOCUMENT_ROOT']."/classes/professions.php");
            $raw_professions = professions::GetProfessionsTitles(array_keys($filter["prof"][1]));
            $a_professions = array();
            foreach ($raw_professions as $profession_item) {
                $a_professions[$profession_item["name"]] = '(@name_prof "' 
                        . $profession_item["name"] 
                        . '" | @additional_specs "' 
                        . $profession_item["name"] . '")';
            }
            
            $searchString .= join(" ", $a_professions);
        }        

        $cost = $this->GetTariff($this->tariff_id);

        $search = new search($uid);
        
        // сохраняем всех будущих получателей в базу
        if ($commit) {
            $searchCount = __paramValue('int', $param['search_count']);
            $searchCount = $searchCount ? $searchCount : 1;
            $search->addElement('users', true, $searchCount);
            $search->search($searchString, 1, $filter);
            $elements = $search->getElements();
            $massSendingUsers = array();
            foreach ($elements['users']->results as $key => $user) {
                $massSendingUsers[] = array(
                    'mid' => $commit,
                    'uid' => $user['id'],
                );
            }
            $DB->insert('mass_sending_users', $massSendingUsers);
        }
        
        $search->addElement('users', true, 1);
        if ($filter['is_pro']) { // если нужны только ПРО
            $search->search($searchString, 1, $filter);
            $elements = $search->getElements();
            $searchCountPro = (int)$elements['users']->total;
            $searchCostPro = $searchCountPro * $cost['pro'];
            $calc = array(
                'count' => $searchCountPro,
                'cost' => $searchCostPro,
                'pro' => array (
                    'count' => $searchCountPro,
                    'cost' => $searchCostPro,
                )
            );
        } else {
            // все пользователи
            $search->search($searchString, 1, $filter);
            $elementsTotal = $search->getElements();
            $searchCountTotal = (int)$elementsTotal['users']->total;
            // только ПРО
            $filter['is_pro'] = true;
            $search->search($searchString, 1, $filter);
            $elementsPro = $search->getElements();
            // количество ПРО пользователей
            $searchCountPro = (int)$elementsPro['users']->total;
            // количество неПРО
            $searchCount = $searchCountTotal - $searchCountPro;
            // стоимость рассылки
            $searchCostPro = $searchCountPro * $cost['pro'];
            $searchCost = $searchCount * $cost['no_pro'];
            $searchCostTotal = $searchCostPro + $searchCost;

            $calc = array(
                'count' => $searchCountTotal,
                'cost' => $searchCostTotal,
                'pro' => array (
                    'count' => $searchCountPro,
                    'cost' => $searchCostPro,
                )
            );
        }
        return $calc;
    }
	

	/**
	 * Отправка рассылки на модерацию
	 * @param   integer         $uid     uid пользователя
	 * @param   array           $params  массив с данными рассылки
	 * @return  boolean|array            массив с расчетами или FALSE если произошла ошибка
	 * 
	 */
	public function Add($uid, array $params) {
		require_once $_SERVER['DOCUMENT_ROOT'].'/classes/account.php';
		$account = new account();
		$this->error = '';
		$tariff = $this->GetTariff($this->tariff_id);
		
		global $DB;
		
		$DB->start();

		// подготовка данных перед записью в БД
		$is_pro = empty($params['is_pro'])? 'f': 't';
		$positive = empty($params['positive'])? 'f': 't';
		$negative = empty($params['negative'])? 'f': 't';
		$free = empty($params['free'])? 'f': 't';
		$favorites = empty($params['favorites'])? 'f': 't';
		$portfolio = empty($params['portfolio'])? 'f': 't';
		$sbr = empty($params['sbr'])? 'f': 't';
		$inoffice = empty($params['inoffice'])? 'f': 't';
		$is_pro = empty($params['is_pro'])? 'f': 't';
		$rank = 0;
		if ($params['sbr']) {
			if ($params['discharge3']) {
				$rank = 3;
			} else if ($params['discharge2']) {
				$rank = 2;
			} else if ($params['discharge1']) {
				$rank = 1;
			}
		}
		$exp_from = (isset($params['expire_from']) && trim($params['expire_from']) != '')? intval($params['expire_from']): NULL;
		$exp_to = (isset($params['expire_to']) && trim($params['expire_to']) != '')? intval($params['expire_to']): NULL;

		$massid = $DB->insert(
            'mass_sending',
            array(
                'tariff_id'   => $tariff['id'],
                'user_id'     => $uid,
                'msgtext'     => $params['msg'],
                'to_pro'      => $is_pro,
                'positive'    => $positive,
                'no_negative' => $negative,
                'free'        => $free,
                'favorites'   => $favorites,
                'portfolio'   => $portfolio,
                'sbr'         => $sbr,
                'rank'        => $rank,
                'office'      => $inoffice,
                'exp_from'    => $exp_from,
                'exp_to'      => $exp_to
            ),
            'id'
        );
        if ( !$massid ) {
            $this->error = 'Ошибка рассылки (sending1)';
        }
		
		// пересчет пользователей и цены + сохранения пользователей для рассылки
        if ($params['from_search'] == 2) {
            $calc = $this->CalculateFromSearch($uid, $params, $massid);
        } else {
            $calc = $this->Calculate($uid, $params, $massid);
        }
		if (!$calc['count']) {
			$this->error = "Нет пользователей для рассылки";
		}

		// Снимаем деньги
		/*if(!($transaction_id = $account->start_transaction($uid))) {
			$this->error = "Невозможно завершить транзакцию. Попробуйте повторить операцию с самого начала.";
		} else {
			$this->error = $account->Buy($acc_op_id, $transaction_id, self::OPER_CODE, $uid, 'Платная рассылка', '', $calc['cost'], 1);
		}*/

		if ($this->error) {
			$DB->rollback();
			return FALSE;
		}
		
		// сохраняем результат расчетов и денежной операции
		if($params['max_users']>0 && $params['max_cost']) {
			$data = array(
	            'pre_sum'       => $params['max_cost'],
            	'all_count'     => $params['max_users'], 
            	'pro_count'     => $calc['pro']['count']
            );
		} else {
			$data = array(
	            'pre_sum'       => $calc['cost'],
            	'all_count'     => $calc['count'], 
            	'pro_count'     => $calc['pro']['count']
			);
		}
		
		if ( !$DB->update('mass_sending', $data, "id = ?", $massid) ) {
			$this->error = 'Ошибка рассылки (sending2)';
		}
		
		// файлы
		if (!empty($params['upfiles']) && is_array($params['upfiles']) && !empty($_SESSION['masssending']['files']) && is_array($_SESSION['masssending']['files'])) {
			$pos = 1;
			foreach ($params['upfiles'] as $file) {
				foreach ($_SESSION['masssending']['files'] as $v) {
					if ($file == $v['id'] && $pos <= self::MAX_FILES) {
					    $data = array(
					       'mass_sending_id' => $massid,
					       'pos' => $pos,
					       'sessionid' => NULL
					    );
					    
						if ( !$DB->update('mass_sending_files', $data, "fid = ? AND sessionid = ?", $file, session_id()) ) {
							$this->error = 'Ошибка рассылки (files1)';
						}
						++$pos;
						break;
					}
				}
			}
			
			if ( !$DB->squery("DELETE FROM mass_sending_files WHERE sessionid = '".session_id()."' AND mass_sending_id IS NULL") ) {
				$this->error = 'Ошибка рассылки (files2)';
			}
		}
		
		// сохраняем выбранные професии
		if (!empty($params['professions'])) {
			$sql = '';
			$tmp = array();
			foreach ($params['professions'] as $prof) {
				if (preg_match("/^([0-9]{1,10})\:([0-9]{1,10})$/", $prof, $o)) {
					$sql .= ",($massid, {$o[1]}, {$o[2]})";
				}
			}
			if ($sql) {
				$sql = "INSERT INTO mass_sending_profs (mass_sending_id, group_id, prof_id) VALUES".substr($sql, 1);
				if ( !$DB->squery($sql) ) {
					$this->error = 'Ошибка рассылки (profs)';
				}
			}
		}
		
		// сохраняем выбранные города и страны
		if (!empty($params['locations'])) {
			$sql = '';
			foreach ($params['locations'] as $location) {
				if (preg_match("/^([0-9]{1,10})\:([0-9]{1,10})$/", $location, $o)) {
					$sql .= ",($massid, {$o[1]}, {$o[2]})";
				}
			}
			if ($sql) {
				$sql = "INSERT INTO mass_sending_cities (mass_sending_id, country_id, city_id) VALUES".substr($sql, 1);
				if ( !$DB->squery($sql) ) {
					$this->error = 'Ошибка рассылки (cities)';
				}
			}
		}

		// сохраняем диапазоны стоимостей
		if ((!empty($params['cost_from']) && is_array($params['cost_from'])) || (!empty($params['cost_to']) && is_array($params['cost_to']))) {
			$sql = '';
			foreach ($params['cost_from'] as $i=>$val) {
				if (!$params['cost_from'][$i] && !$params['cost_to'][$i]) continue;
				$type = (isset($params['cost_type'][$i]) && in_array($params['cost_type'][$i], array(0, 1, 2, 3)))? $params['cost_type'][$i]: 0;
				$period = (isset($params['cost_period'][$i]) && $params['cost_period'][$i] == 'month')? 1: 0;
				$from = empty($params['cost_from'][$i])? 0: floatval(str_replace(',', '.', str_replace(' ', '', $params['cost_from'][$i])));
				$to = empty($params['cost_to'][$i])? 0: floatval(str_replace(',', '.', str_replace(' ', '', $params['cost_to'][$i])));
				$sql .= ",($massid, $type, $period, $from, $to)";
			}
			if ($sql) {
				$sql = "INSERT INTO mass_sending_costs (mass_sending_id, cost_type, cost_period, cost_from, cost_to) VALUES".substr($sql, 1);
				if ( !$DB->squery($sql) ) {
					$this->error = 'Ошибка рассылки (costs)';
				}
			}
		}
		
		// сохранение или откат всего

		if ($this->error) {
			$DB->rollback();
			$account->GetInfo($uid);

			$_SESSION['ac_sum'] = $account->sum;
			return FALSE;
		} else {
            //Создадим биллинг, чтобы работать впоследствии с ним
            $this->billing = new billing($uid);
            
			$DB->commit();
			$calc['massid'] = $massid;
			return $calc;
		}
        
	}


    /**
     * Одобрить рассылку
     * @param   integer   $id      id рассылки
     * @return  boolean            результат операции
     *
     */
    public function Accept($id, $send_email_alert=true) {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/messages.php';
        $this->error = '';

        global $DB, $aPmUserUids;
        $sql = 'SELECT * FROM mass_sending WHERE id = ? AND is_accepted IS NULL';

        if (!($row = $DB->row($sql, $id))) {
            $this->error = 'Рассылка выбрана неправильно';
            return FALSE;
        }

        // если рассылка не оплачена, значит она была создана после введения нового счета
        // в будущем, когда все старые (сразу оплаченные) рассылки уйдут, то можно логику этой функции заменить на логику из Accept_new
        if (!$row['account_op_id']) {
            return $this->Accept_new($id, $row, $send_email_alert);
        }

        if ( messages::Masssending($row['user_id'], $row['id'], $row['msgtext'], $row['posted_time']) ) {
            $DB->query('UPDATE mass_sending SET is_accepted=true, decided_time=now() WHERE id = ?', $row['id']);
        } else {
            return FALSE;
        }
        $users = new users();
        $login    = $users->GetName($row["user_id"], $e);
        $login = $login["login"];
        $users->GetUser($login);
        $authorId = users::GetUid($err, "admin");
        if($send_email_alert) {
	        messages::Add($authorId, $login, "Ваша рассылка
			".html_entity_decode($row["msgtext"])."
			одобрена администрацией");
	        // уведомляем автора о разрешении рассылки
	        $smail = new smail();
	        $smail->subject   = "Ваша заявка на рассылку прошла модерацию";
	        $smail->recipient = $users->uname." ".$users->usurname." [".$users->login."] <".$users->email.">";
	        $msg_text = $smail->ToHtml($row["msgtext"]);
	        $body = "Ваша заявка на рассылку была рассмотрена и одобрена модераторами сайта Free-lance.ru.
	                 Фрилансерам выбранных вами специализаций будет отправлено сообщение следующего содержания:<br/>
	                        ---<br/>
	                        {$msg_text}<br/>
	                        ---<br/>
	                 ";
	        $smail->message = $smail->GetHtml($users->uname, $body, array('header'=>'default', 'footer'=>'simple'));
	        $smail->send('text/html');
	    }
        return TRUE;
    }

	/**
	 * Одобрить рассылку для нового счета
     * отличается от старой тем что не отправляется сразу, а требует оплаты
	 * @param   integer   $id      id рассылки
	 * @return  boolean            результат операции
	 * 
	 */	
	public function Accept_new($id, $row, $send_email_alert=true) {
		require_once $_SERVER['DOCUMENT_ROOT'].'/classes/billing.php';
		$this->error = '';
		
		global $DB, $aPmUserUids;
		/*$sql = 'SELECT * FROM mass_sending WHERE id = ? AND is_accepted IS NULL';
		
		if (!($row = $DB->row($sql, $id))) {
			$this->error = 'Рассылка выбрана неправильно';
			return FALSE;
		}*/
		
        $DB->query('UPDATE mass_sending SET is_accepted=true, decided_time=now() WHERE id = ?', $row['id']);

        $this->billing = $this->billing ?: new billing($row['user_id']);
        
        $options = array(
            'amount' => $row['pre_sum'],
            'masssending_id' => $row['id'],
        );
        //Формируем заказ
        $billReserveId = $this->billing->addServiceAndCheckout(
                self::OPER_CODE, 
                $options);
        
        if (!$billReserveId) {
            return false;
        }

        $params = array(
            'id'        => $row['id'],
            'name'      => ($this->billing->user['uname'] ? $this->billing->user['uname'] : $this->billing->user['login']),
            'login'     => $this->billing->user['login'],
            'message'   => $row['msgtext'],
            'amount'    => $row['pre_sum'],
            'uname'     => $this->billing->user['uname'],
            'usurname'  => $this->billing->user['usurname'],
            'email'     => $this->billing->user['email']
        );
        if($send_email_alert) {
        	$smail2 = new smail2();
        	$smail2->masssendingAccepted($params);
        }

		return $billReserveId;	
	}
    
    /**
	 * Одобрить рассылку администратором
	 * @param   integer   $id      id рассылки
	 * @return  boolean            результат операции
	 * 
	 */	
    public function acceptByAdmin($id)
    {
        $result = false;
        $this->error = '';

        global $DB;
        $sql = 'SELECT * FROM mass_sending WHERE id = ? AND is_accepted IS NULL';

        if (!($row = $DB->row($sql, $id))) {
            $this->error = 'Рассылка выбрана неправильно';
            return $result;
        }

        if (!$row['account_op_id']) {
            $DB->query('UPDATE mass_sending SET is_accepted=true, decided_time=now() WHERE id = ?', $row['id']);
            $result = true;
        }
        
        $billing = $this->billing ?: new billing($row['user_id']);
        $params = array(
            'id'        => $row['id'],
            'name'      => ($billing->user['uname'] ? $billing->user['uname'] : $billing->user['login']),
            'login'     => $billing->user['login'],
            'message'   => $row['msgtext'],
            'amount'    => $row['pre_sum'],
            'uname'     => $billing->user['uname'],
            'usurname'  => $billing->user['usurname'],
            'email'     => $billing->user['email'],
        );
      	$smail2 = new smail2();
       	$smail2->masssendingAccepted($params);
        
        return $result;
    }


    /**
     * Модератор отвергает рассылку, деньги возращаются юзеру.
     *
     * @param integer  $id             ИД Рассылки
     * @param string   $denied_reason  Причина отказа
     * @param string   $error          Возвращает сообщение об ошибке
     * @return integer 1 - все сработало как надо, 0 - ошибка
     */
    public function Deny($id, $reason) {
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/messages.php";
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/account.php";
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
        $this->error = '';

        global $DB;
        $sql = 'SELECT * FROM mass_sending WHERE id = ? AND is_accepted IS NULL';

        if (!($row = $DB->row($sql, $id))) {
            $this->error = 'Рассылка выбрана неправильно';
            return FALSE;
        }

        // если рассылка не оплачена, значит она была создана после введения нового счета
        // в будущем, когда все старые (сразу оплаченные) рассылки уйдут, то можно логику этой функции заменить на логику из Deny_new
        if (!$row['account_op_id']) {
            return $this->Deny_new($id, $reason, $row);
        }

        $account = new account;
        $account->GetInfo($row['user_id']);

        if (!$account->id) {
            $this->error = 'Ошибка. Не опеределен счет пользователя.';
            return FALSE;
        }

        $users = new users;
        $user = $users->GetName($row['user_id'], $e);
        $login = $user['login'];
        $users->GetUser($login);
        $admin_id = users::GetUid($err,'admin');

        if (!($error=$account->deposit($acc_op_id, $account->id, $row['pre_sum'], 'Рассылка по разделам. Возврат денег.', 0, 0, self::OPER_CODE_RETURN, 0))) {
            $text = "Здравствуйте!

Администрацией нашего ресурса было принято решение отказать Вам в рассылке по каталогу по причине:

\"
". stripslashes($reason)."
\"

Потраченные деньги на рассылку возвращены на Ваш личный счет.

Это сообщение было выслано автоматически и ответ на него не будет рассматриваться.

Надеемся на понимание, Команда Free-lance.ru.

Исходный текст Вашей рассылки:

---
".html_entity_decode( $row['msgtext'], ENT_QUOTES )."
--- ";
            // уведомляем автора о разрешении рассылки
            $smail = new smail();
            $smail->subject   = "Ваша заявка на рассылку не прошла модерацию";
            $smail->recipient = $users->uname." ".$users->usurname." [".$users->login."] <".$users->email.">";
            $reason = $smail->ToHtml($reason);
            $body = "Ваша заявка на рассылку была отклонена модераторами сайта Free-lance.ru.<br/>
              Причина:<br/>
              ---<br/>
              {$reason}<br/>
              ---<br/>
          ";
            $smail->message = $smail->GetHtml($users->uname, $body, array('header'=>'default', 'footer'=>'simple'));

            $smail->send('text/html');
            messages::Add($admin_id, $login, $text, '', 1);
            if (!$DB->query("UPDATE mass_sending SET denied_reason = ?, is_accepted=false, decided_time=now() WHERE id=?", $reason, $id)) {
                $this->error = 'Произошла ошибка при установлении статуса "Отказано". Но деньги возвращены.';
                return FALSE;
            }

        } else {
            $this->error = 'Какой-то сбой. Деньги не возвращены.';
            return FALSE;
        }
        return TRUE;
    }

	/**
	 * Модератор отвергает рассылку, деньги возращаются юзеру.
	 *
	 * @param integer  $id             ИД Рассылки
	 * @param string   $denied_reason  Причина отказа
	 * @param string   $error          Возвращает сообщение об ошибке
	 * @return integer 1 - все сработало как надо, 0 - ошибка 
	 */
	public function Deny_new($id, $reason, $row) {
		require_once $_SERVER['DOCUMENT_ROOT']."/classes/messages.php";
		require_once $_SERVER['DOCUMENT_ROOT']."/classes/account.php";
		require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
		$this->error = '';
	
		global $DB;
		/*$sql = 'SELECT * FROM mass_sending WHERE id = ? AND is_accepted IS NULL';
		
		if (!($row = $DB->row($sql, $id))) {
			$this->error = 'Рассылка выбрана неправильно';
			return FALSE;
		}*/
		
		$account = new account;
		$account->GetInfo($row['user_id']);

		if (!$account->id) {
			$this->error = 'Ошибка. Не опеределен счет пользователя.';
			return FALSE;
		}
		
		$users = new users;
		$user = $users->GetName($row['user_id'], $e);
		$login = $user['login'];
		$users->GetUser($login);
		$admin_id = users::GetUid($err,'admin');
		
	    $text = "Здравствуйте!

Администрацией нашего ресурса было принято решение отказать Вам в рассылке по каталогу по причине:

\"
". stripslashes($reason)."
\"

Это сообщение было выслано автоматически и ответ на него не будет рассматриваться.

Надеемся на понимание, Команда Free-lance.ru. 

Исходный текст Вашей рассылки:

---
".html_entity_decode( $row['msgtext'], ENT_QUOTES )."
--- ";
     // уведомляем автора о разрешении рассылки
     $smail = new smail();
     $smail->subject   = "Ваша заявка на рассылку не прошла модерацию";
     $smail->recipient = $users->uname." ".$users->usurname." [".$users->login."] <".$users->email.">";
     $reason = $smail->ToHtml($reason);
     $body = "Ваша заявка на рассылку была отклонена модераторами сайта Free-lance.ru.<br/> 
              Причина:<br/>
              ---<br/>
              {$reason}<br/>
              ---<br/>
          ";
     $smail->message = $smail->GetHtml($users->uname, $body, array('header'=>'default', 'footer'=>'simple'));
     
     $smail->send('text/html');
	 messages::Add($admin_id, $login, $text, '', 1);
	 if (!$DB->query("UPDATE mass_sending SET denied_reason = ?, is_accepted=false, decided_time=now() WHERE id=?", $reason, $id)) {
	     $this->error = 'Произошла ошибка при установлении статуса "Отказано".';
		  return FALSE;
	 }
		
     return TRUE;
    }
	

	/**
	 * Возвращает информацию о тарифе.
	 *
	 * @param   integer   $tariff_id   Если NULL, то вернет текущий тариф.
	 * @return  array
	 */
	public function GetTariff($tariff_id=NULL) {
	    global $DB;
		$sql = 'SELECT * FROM mass_sending_tariffs '.(!$tariff_id ? 'ORDER BY t_time DESC LIMIT 1' :  "WHERE id={$tariff_id}");
		if ( ($res = $DB->squery($sql)) && pg_num_rows($res) ) {
			return pg_fetch_assoc($res);
		}
		return NULL;
	}
  

	/**
	* Устанавливает новый тариф
	*
	* @param integer $pro     Цена для рассылки ПРО
	* @param inetger $no_pro  Цена для рассылки не ПРО
	* @return array
	*/
	public function SetTariff($pro, $no_pro) {
	    global $DB;
		$sql = "INSERT INTO mass_sending_tariffs (pro, no_pro) VALUES (?f, ?f) RETURNING *";
		if ( ($res = $DB->row($sql, $pro, $no_pro)) ) {
			return $res;
		}
		return NULL;
	}
	
	
	/**
	 * Выборка рассылки
	 *
	 * @param integer $id         ИД рассылки или NULL -- взять все подходящие.
	 * @param integer $order_mode режим сортировки (фильтрации) 
	 * @param integer $offset     Позиция выборки
	 * @param string  $limit      Лимит выборки
	 * @return array Данные выборки, либо null
	 */
	public function Get($id=NULL, $order_mode=self::OM_NEW, $offset = 0, $limit = 'ALL') {
		$where = '';
		$order_by = 'ORDER BY ps.decided_time DESC';
		if ($order_mode == self::OM_NEW) {
			$where = 'WHERE ps.decided_time IS NULL';
			$order_by = 'ORDER BY ps.posted_time ASC';
		} else if ($order_mode == self::OM_OLD) {
			$where = 'WHERE ps.decided_time IS NOT NULL';
		} else if($order_mode & self::OM_ACCEPTED) {
			$where = 'WHERE ps.is_accepted = true';
		} else if ($order_mode & self::OM_DENIED) {
			$where = 'WHERE ps.is_accepted = false';
		}
    
		if ($id) $where = ($where ? ' AND' : 'WHERE')." ps.id = {$id}";

		$sql="
			SELECT
				ps.*,
				u.is_banned as user_is_banned,
				u.is_pro as user_is_pro,
				u.role as user_role,
				u.login as user_login,
				u.photo as user_photo,
				u.usurname as user_usurname,
				u.uname as user_uname,
				array_to_string(
					ARRAY(
						SELECT CASE WHEN pp.prof_id > 0 THEN g.name || ' -> ' || p.name ELSE g.name END
						FROM mass_sending_profs pp
						LEFT JOIN prof_group g ON g.id=pp.group_id
						LEFT JOIN professions p ON p.id=pp.prof_id
						WHERE mass_sending_id=ps.id
                   ), ','
				) as prof_names
			FROM mass_sending ps
			INNER JOIN users u ON u.uid = ps.user_id
			{$where}
			{$order_by}
			LIMIT {$limit} OFFSET {$offset}
		";
		
		global $DB;

        if ( $res = $DB->squery($sql) ) {
			$ms = array();
			$ls = array();
			$i = 0;
			while ($row = pg_fetch_assoc($res)) {
				$ms[$i] = $row;
				$ls[ $row['id'] ] = &$ms[$i++];
			}
			
			if ($ls) {
				$res = $DB->query('SELECT mf.mass_sending_id AS mid, file.* FROM mass_sending_files mf JOIN file ON file.id = mf.fid WHERE mf.mass_sending_id IN (?l)', array_keys($ls));
				while ($row = pg_fetch_assoc($res)) {
					if (empty($ls[ $row['mid'] ]['files'])) $ls[ $row['mid'] ]['files'] = array();
					$ls[ $row['mid'] ]['files'][] = $row;
				}
			}
			return $ms;
		}
    
		return NULL;
	}
    
    
    /**
     * Возвращает ообренную неоплаченную рассылку,
     * принадлежащую указанному юзеру
     * @param type $id ИД рассылки
     * @param type $uid ИД юзера
     * @return array Данные рассылки
     */
    public function getAccepted($id, $uid)
    {
        global $DB;
		$sql="SELECT * FROM mass_sending WHERE 
            is_accepted = true 
            AND account_op_id IS NULL
            AND user_id = ?i
            AND id = ?i";
        return $DB->row($sql, (int)$uid, (int)$id);
    }
    
    
	/**
	 * Взять количество рассылок
	 *
	 * @param integer $order_mode Режим фильтрации
	 * @return integer количество, либо 0
	 */
	public function GetCount($order_mode=self::OM_NEW) {
		$where = '';
		if ($order_mode == self::OM_NEW) {
			$where = 'WHERE ps.decided_time IS NULL';
		} else if ($order_mode == self::OM_OLD) {
			$where = 'WHERE ps.decided_time IS NOT NULL';
		} else if ($order_mode & self::OM_ACCEPTED) {
			$where = 'WHERE ps.is_accepted = true';
		} else if ($order_mode & self::OM_DENIED) {
			$where = 'WHERE ps.is_accepted = false';
		}
		
		global $DB;
		$sql = "SELECT COUNT(ps.id) FROM mass_sending ps INNER JOIN users u ON u.uid = ps.user_id {$where} {$order_by}";
		
		if ( $res = $DB->val($sql) ) return (int)$res;
		return 0;
	}
	
	
	/**
	 * Добавляет файл к рассылке во время редактирования (при ajax запросе)
	 *
	 * @param  integer  $fid         id файла
	 * @param  string   $sessionid   имя сессии пользователя, для которой прикрепляется файл
	 * @retun  boolean               результат операции
	 */
	public function AddFile($fid, $sessionid) {
	    global $DB;
	    
		if ( $DB->insert('mass_sending_files', array('fid'=>$fid, 'sessionid'=>$sessionid)) ) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	
	/**
	 * Удаляет ненужные файлы. Например, если пользователь редактировал рассылку, но потом передумал ее отправлять
	 *
	 * @param  string   $sessionid  id сессии пользователя или пустая строка, для очистки во всех сессиях
	 */
	public function ClearTempFiles($sessionid='') {
	    global $DB;
		$where = '';
		if ($sessionid) $where = "sessionid = '{$sessionid}' AND ";
		$where .= "round(extract('epoch' from NOW())) - round(extract('epoch' from post_time)) > ".(self::SESS_TTL + 600);
		$sql = "SELECT * FROM mass_sending_files WHERE (mass_sending_id = 0 OR mass_sending_id IS NULL) AND {$where}";
		$res = $DB->squery( $sql );
		$file = new CFile;
		$list = array();
		while ($row = pg_fetch_assoc($res)) {
			$file->Delete($row['fid']);
			$list[] = $row['fid'];
		}
		
		if ($list) {
		    $sql = "DELETE FROM mass_sending_files WHERE fid IN (?l)";
		    $DB->query( $sql, $list );
		}
	}


    /**
     * Информация о заказе в HTML по id в account_operations: логин, имя пользователя, где размещено, время действия.
     * @param   integer   $bill_id   id операции в account_operations
     * @param   integer   $uid       uid пользователя
     * @return  string               данные о заказе в виде HTML
     */
    public function GetOrderInfo( $bill_id, $uid ) {
        include_once($_SERVER['DOCUMENT_ROOT']).'/classes/professions.php';
        
        global $DB;
        $sql = "SELECT * FROM mass_sending WHERE account_op_id = ? LIMIT 1";
        $row = $DB->row( $sql, $bill_id );
        
        $out = "Для ".$row['all_count']." ".getTermination($row['all_count'], array('пользователя', 'пользователей', 'пользователей'));
        
        $sql  = "SELECT * FROM mass_sending_profs WHERE mass_sending_id  = ?";
        $row2 = $DB->rows( $sql, $row['id'] );
        
        if ( is_array($row2) && count($row2) ) {
            $sect = array();
            foreach ( $row2 as $ms ) {
                if ( (int)$ms['prof_id'] ) {
                    $sect[] = professions::GetProfNameWP((int)$ms['prof_id']);
                }
                else {
                    $sect[] = professions::GetGroupName((int)$ms['group_id']) ."/Все разделы";//'Все разделы';
                }
            }
            $out .= ', '.implode(', ', $sect);
        }
        
        return $out;
    }

	/**
	 * Функция для удаления операции
	 *
	 * @param integer $uid  Ид пользователя
	 * @param integer $opid ИД операции
 	 * @return integer
	 */
    function DelByOpid($uid, $opid){
        global $DB;
        $sql = "DELETE FROM mass_sending WHERE account_op_id=? AND (is_accepted IS NULL OR is_accepted = false)";
        $mass_id = $DB->val($sql, $opid);
        return 0;
    }

    /**
     * Изменение текста рассылки
     *
     * @param    integer    $id    ID рассылки
     * @param    string     $text  Текст рассылки
     */
    function UpdateText($id, $text) {
    	global $DB;
    	$sql = "UPDATE mass_sending SET msgtext = ? WHERE id = ?i";
    	$DB->query($sql, change_q_x($text, FALSE, FALSE, 'b|i|p|ul|li|s|h[1-6]{1}', FALSE, FALSE), $id);
    }

    /**
     * обновляет account_op_id в таблице mass_send
     * @param integer $masssendID ID рассылки
     * @param integer $accOpID ID операции
     */
    function UpdateAcOpID($masssendID, $accOpID) {
        global $DB;
        $data = array(
            'account_op_id' => $accOpID,
        );

        return (bool)$DB->update('mass_sending', $data, "id = ?", $masssendID);
    }
	
}

?>
