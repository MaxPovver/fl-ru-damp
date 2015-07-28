<?
/**
 * Подключаем класс для работы с кешем
 */
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/memBuff.php");

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer_binds.php");

/**
 * Максимальное количество цены за 1000 знаков
 *
 */
define ('PROF_COST_1000', 1000);

/**
 * Максимальное количество цены за 1000 знаков (USD)
 *
 */
define ('PROF_COST_1000_USD', 1000);

/**
 * Максимальная цена за 1000 знаков (FM)
 *
 */
define ('PROF_COST_1000_FM', 1000);

/**
 * Максимальная цена  за 1000 знаков (руб)
 *
 */
define ('PROF_COST_1000_RUB', 25000);

/**
 * Максимальная цена за 1000 знаков (Евро)
 *
 */
define ('PROF_COST_1000_EU', 1000);

/**
 * Максимальная цена за час 
 *
 */
define ('PROF_COST_HOUR', 1000);

/**
 * Максимальная цена за час (USD)
 *
 */
define ('PROF_COST_HOUR_USD', 1000);

/**
 * Максимальная цена за час (FM)
 *
 */
define ('PROF_COST_HOUR_FM', 1000);

/**
 * Максимальная цена за час (руб)
 *
 */
define ('PROF_COST_HOUR_RUB', 25000);

/**
 * Максимальная цена за час (EU)
 *
 */
define ('PROF_COST_HOUR_EU', 1000);

/**
 * Максимальный ценовой придел начальной стоимости
 *
 */
define ('PROF_COST_FROM', 100000);

/**
 * Максимальный ценовой придел конечной стоимости
 *
 */
define ('PROF_COST_TO', 100000);

/**
 * Максимальное количество времени начала работы
 *
 */
define ('PROF_TIME_FROM', 100);

/**
 * Максимальное количество времи конца работы
 *
 */
define ('PROF_TIME_TO', 100);

/**
 * Количество дополнительных специализаций для аккаунта PRO
 *
 */
define ('PROF_SPEC_ADD', 4);


/**
 * Класс для работы с профессиями фрилансеров
 *
 */
class professions
{
  /**
   * ИД Лучших работ (используется только у аккаунта ПРО)
   *
   */
  const BEST_PROF_ID    = -3;
  
  /**
   * ИД Клиентов (используется только у аккаунта ПРО)
   *
   */
  const CLIENTS_PROF_ID = -4;
  
  const OP_PAID_SPEC = 80;

    /**
	 * Возвращает список дополнительных специализаций учитывая mirrored
	 *
	 * @deprecated 
	 * 
	 * @param integer $user  ИД Пользователя
	 * @param string  $table Таблица выборки
	 * @return array
	 */
    function GetAllProfessionsPortf($user, $table="portf_choise"){
		global $DB;
		$sql = "
        SELECT DISTINCT
               p.id,
               g.name as groupname,
               p.name as profname,
               t.user_id as checked,
               p.n_order as n_order,
               g.n_order as gn_order
    
          FROM professions p
        INNER JOIN
          prof_group g
            ON g.id = p.prof_group
        LEFT JOIN
          {$table} t
            ON t.prof_id = p.id
           AND t.user_id = ?
         WHERE p.id <> 0
           AND p.prof_group <> 0
         ORDER BY g.n_order, p.n_order";
     
        return $DB->rows($sql, $user);
		
	}
  
	/**
	 * Возвращает список дополнительных специализаций НЕ учитывая mirrored
     * (то, что выбрал пользователь)
	 *
	 * @param integer $user ИД Пользователя
	 * @return array 
	 */
	function GetAllProfessionsPortfWithoutMirrored($user, $checked = ""){
		global $DB;
		$sql = "SELECT DISTINCT
                   p.id,
                   g.name as groupname,
                   p.name as profname,
                   t.user_id as checked,
                   p.n_order as n_order,
                   g.n_order as gn_order
                  FROM professions p
                INNER JOIN
                  prof_group g
                    ON g.id = p.prof_group
                LEFT JOIN
                  portf_choise t
                    ON ((t.prof_origin IS NULL AND t.prof_id = p.id) OR t.prof_origin = p.id)
                   AND t.user_id = ?
                 WHERE p.id <> 0
                   AND p.prof_group <> 0
                   {$checked}
                 ORDER BY g.n_order, p.n_order";
 
		return $DB->rows($sql, $user);
	}
	
   /**
    * Возвращает список допольнительных специализация
    *
    * 
    * @param integer $user ИД Пользователя
    * @return array
    */
    function GetAllProfessionsSpec($user) {
        global $DB;
		$sql = "SELECT DISTINCT
               p.id,
               g.name as groupname,
               p.name as profname,
               t.uid as checked,
               p.n_order as n_order,
               g.n_order as gn_order
              FROM professions p
            INNER JOIN
              prof_group g
                ON g.id = p.prof_group
            LEFT JOIN
              freelancer t
                ON t.spec = p.id
               AND t.uid = {$user}
               AND t.is_active = true
             WHERE p.id <> 0
               AND p.prof_group <> 0
             ORDER BY g.n_order, p.n_order";
    
        return $DB->rows($sql, $user);
    }

    /**
     * Возвращает список всех профессий
     *
     * @param integer $prof_group   ИД группы профессий
     * @param integer $mod          deprecated
     * @param string  $orderByCount Сортировка результата
     * @param boolean $nocache      не использовать кеш
     * @return array
     */
    function GetAllProfessions($prof_group = "", $mod = 1, $orderByCount = 0, $nocache = false) {
        global $DB;
        static $professions;
        
        if ($prof_group !== 0) {
            $addit = 'pg.id <> 0';
        }
        if ($prof_group !== '') {
            $prof_group = (int) $prof_group;
            $addit = "pg.id = {$prof_group}";
        }
        $orderByCount = $orderByCount ? 'pg.cnt DESC, p.pcount DESC NULLS LAST' : 'pg.n_order, p.n_order';

        $sql = "SELECT 
                  pg.name as groupname, pg.link as grouplink, pg.id as groupid,
                  p.id as id, p.title as proftitle, p.descr as profdescr, p.name as profname, p.is_text as is_text, p.descr_text, p.descr_text2,
                  NULLIF(p.pcount, 0) as count, NULLIF(p.pro_count, 0) as pro_count, p.link, 
                  p.min_cost_prj, p.max_cost_prj, p.avg_cost_prj, p.min_cost_hour, p.max_cost_hour, p.avg_cost_hour, 
                  p.is_manual_cost_hour, p.is_manual_cost_prj, p.name_case, p.header_about, p.header_list 
                FROM 
                  prof_group pg
                INNER JOIN professions p ON p.prof_group = pg.id
                WHERE {$addit}
                ORDER BY {$orderByCount}";
        $md5_key = md5($sql);  
        if(isset($professions[$md5_key])) {
            return $professions[$md5_key];
        } else {
            $ret = ( $nocache ? $DB->rows($sql) : $DB->cache(60)->rows($sql) );
            $professions[$md5_key] = $ret;
        }
        return $ret ? $ret : array();
    }

	/**
	 * Возвращает профессии, отнесенные к фрилансеру.
	 * При получении профессий используется та же система, что и в каталоге -- когда определяется
     * относится ли конкретный пользователь к той или иной профессии (к тому или иному разделу каталога).
     * Возвращает массив идентификаторов найденных профессий.
	 * 
	 * 
	 * @param integer $user_id  Ид пользователя   
	 * @param boolean $get_0    выяснять, есть ли юзер в общем каталоге.
	 * @param boolean $mirr     Подключить зеркальные профы или нет
	 * @return array
	 */
    function GetProfessionsByUser($user_id, $get_0 = TRUE, $mirr=false) {
        global $DB;
		$sql =
        "SELECT 
                s.spec as prof_id, 
                MIN(s.ordering) as ordering
           FROM
           (".
           ( $get_0 ? 
             "SELECT 
                 0 as spec, 
                 0 as ordering, 
                 0 as priority
              FROM fu 
              INNER JOIN
                portf_choise pc
                  ON pc.user_id = fu.uid
                 AND pc.prof_id = fu.spec_orig
              WHERE fu.uid = {$user_id}
              UNION " : ''
           )."
             SELECT 
                spec, 
                1 as ordering, 
                1 as priority 
             FROM fu 
             WHERE uid = {$user_id} AND spec_orig<>0
                 
             UNION ALL
             
             SELECT 
                COALESCE(sp.prof_origin, sp.prof_id), 
                2, 
                1/(sp.priority+0.1)::float as priority
             FROM fu
             INNER JOIN
               spec_add_choise sp
                 ON sp.user_id = fu.uid
             WHERE fu.is_pro = true
                AND fu.uid = {$user_id}
           ) as s
          GROUP BY s.spec, priority
          ORDER BY ordering, priority";
        
		$profs = $DB->col($sql);

        if($mirr && $profs) {
            $mirr = self::GetMirroredProfs(implode(",", $profs));
            $profs = array_unique(array_merge($mirr, $profs));  
        }
        
        return $profs;
    }
    
    function getProfessionsByUser2($user_id, $mirr = false) {
        global $DB;
        $sql = "SELECT s.spec as prof_id, s.prof_group
                FROM
                    (SELECT spec, 1 as ordering, 1 as priority, p.prof_group as prof_group 
                     FROM fu 
                     INNER JOIN professions p ON p.id = fu.spec 
                     WHERE fu.uid = ? AND fu.spec_orig<>0
                        
                     UNION ALL
             
                     SELECT COALESCE(sp.prof_origin, sp.prof_id), 2, 1/(sp.priority+0.1)::float as priority, p.prof_group as prof_group
                     FROM fu
                     INNER JOIN professions p ON p.id = fu.spec
                     INNER JOIN spec_add_choise sp ON sp.user_id = fu.uid
                     WHERE fu.is_pro = true AND fu.uid = ?
                     
                     UNION ALL
             
                     SELECT spp.prof_id, 2, spp.priority, p.prof_group as prof_group
                     FROM fu
                     INNER JOIN professions p ON p.id = fu.spec
                     INNER JOIN spec_paid_choise spp ON spp.user_id = fu.uid
                     WHERE fu.is_pro = true AND fu.uid = ? AND spp.prof_id IS NOT NULL AND spp.paid_to > NOW()
                ) as s 
                GROUP BY s.spec, priority, s.prof_group";
        $profs = $DB->rows($sql, $user_id, $user_id, $user_id);
        
        foreach($profs as $k=>$v) {
            $subcategory[$v['prof_id']] = $v['prof_id'];
            $category[$v['prof_group']] = $v['prof_group'];
        } 
        
        if($mirr && $subcategory) {
            $mirr = self::GetMirroredProfs(implode(",", $subcategory));
            $subcategory = array_unique(array_merge($mirr, $subcategory)); 
        }
        if($category && $subcategory) {
            $profs = array("prof_group" => array_values($category), "prof" => array_values($subcategory));
            return $profs;
        }
        return false;
        
    }
  
    /**
     * Пересчитывает professions.pcount
     *
     * @return integer
     */
    function ReCalcProfessionsCount() {
		global $DB;
        return (int) $DB->mquery("SELECT recalc_professions_count()");
	}
	
    
    function removePortfChoise($uid, $prof_id) {
        global $DB;
        return $DB->query("DELETE FROM portf_choise WHERE user_id = ?i AND prof_id = ?i", $uid, $prof_id);
    }
    /**
     * Обновляем список профессий в портфолио
     *
     * @param integer $fid     ИД Пользователя
     * @param string  $profs   ИД Профессий
     * @return array
     */
    function UpdatePortfChoise($fid, $profs) {
        global $DB;
        foreach($profs as $val) if(intval(trim($val)) != 0) $params[] = intval(trim($val));
		$selected = implode(',', $params);
        $sql =  "
            CREATE TEMPORARY TABLE ___profs (prof_id int, prof_origin int);
            INSERT INTO  ___profs
            SELECT COALESCE(m.main_prof, p.id),
                   NULLIF(p.id, COALESCE(m.main_prof, p.id))
              FROM professions p
            LEFT JOIN
              mirrored_professions m
                ON m.mirror_prof = p.id
             WHERE p.id IN ({$selected});
            DELETE FROM portf_choise
             WHERE user_id = {$fid}
               AND prof_id NOT IN (SELECT prof_id FROM ___profs)
               AND prof_id NOT IN (".self::BEST_PROF_ID.','.self::CLIENTS_PROF_ID.")
               AND prof_id NOT IN (SELECT prof_id FROM portfolio WHERE user_id = {$fid});
            UPDATE portf_choise pc
               SET prof_origin = p.prof_origin
              FROM ___profs p
             WHERE pc.user_id = {$fid}
               AND pc.prof_id = p.prof_id
               AND COALESCE(pc.prof_origin,0) <> COALESCE(p.prof_origin,0);
            INSERT INTO portf_choise (user_id, prof_id, prof_origin)
            SELECT {$fid}, p.prof_id, p.prof_origin
              FROM ___profs p
            LEFT JOIN
              portf_choise pc
                ON pc.user_id = {$fid}
               AND pc.prof_id = p.prof_id
             WHERE pc.user_id IS NULL
        ";
		$DB->query($sql);
        foreach ($params as $prof_id) {
            freelancer::clearCacheFromProfIdNow($prof_id);
        }
        return $DB->error;
	}
	
	/**
	 * Возвращает список всех профессий отсортированных по группе и позиции
	 *
	 * @return array
	 */
	function GetProfList() {
		return $GLOBALS['DB']->rows("SELECT * FROM professions WHERE prof_group > 0 ORDER BY prof_group, pcount DESC NULLS LAST");
	}

	/**
	 * Возвращает название и описание професии по его ИД
	 *
	 * @param integer $id ИД Профессии
	 * @return array
	 */
	function GetProfTitle($id){
		return $GLOBALS['DB']->row("SELECT name, title FROM professions WHERE id = ?", $id);
	}

    /**
     * Возвращает название группы професий по его ИД
     *
     * @param integer $id ИД Группы
     * @return array
     */
    function GetProfGroupTitle($id){
        return $GLOBALS['DB']->val("SELECT name FROM prof_group WHERE id = ?", $id);
    }
	
	/**
	 * Возвращает имя професии по его ИД
	 *
	 * @param integer $id ИД Профессии
	 * @return string
	 */
    function GetProfName($id){
		return $GLOBALS['DB']->val("SELECT name FROM professions WHERE id = ?", $id);
	}
	
	/**
	 * Возвращает имя професии с его категорией по его ИД
	 *
	 * @param integer $id ИД Профессии
	 * @param string $delimiter Разделитель для Группа >HERE< профессия
     * @param &$link - сюда запишется ссылка на каталог
	 * @return string
	 */
    function GetProfNameWP($id, $delimiter='/', $default = "Все разделы", $with_link = 'b-layout__link b-layout__link_fontsize_11 b-layout__link_color_80', $p_name = false, &$link = ''){
		global $DB;
		if ((int)$id < 1) {
			return $default;
		}
		$tmp = $DB->row("SELECT a.id AS p_id, a.link AS lnk, a.name AS p_name, b.id AS b_id, b.link as g_link, b.name AS g_name
		FROM professions a 
		INNER JOIN prof_group b ON (a.prof_group = b.id)
		WHERE a.id = ?", $id);
                if($tmp){
                    $glink = '/freelancers/'.$tmp['g_link'].'/';
					$link = '/freelancers/'.$tmp['lnk'].'/';
                    if($with_link && (int)$tmp['p_id'] > 0){
                        $class = $with_link !== true ? $with_link : '';
                        if(!$p_name) {
                            $out = '<a href="'.$glink.'" class="'.$class.'">'.$tmp['g_name'].'</a>';
                            $out .= $delimiter;
                        } else {
                            $out = "";
                        }
                        $out .= ($tmp['p_id'] ? '<a href="'.$link.'" class="'.$class.'">' : '').$tmp['p_name'].($tmp['p_id'] ? '</a>' : '');
                        return $out;
                    }else{
                        return $tmp['g_name'].$delimiter.$tmp['p_name'];
                    }
                }
		return false;
	}

	/**
	 * Возвращает профессии определенно группы
	 * 
	 * @param  int $category_id ID группы
	 * @return array
	 */
    function GetProfs($category_id){
		global $DB;
		$tmp = $DB->rows("SELECT a.*
		FROM professions a
		WHERE a.prof_group = ?", $category_id);
		return $tmp;
	}

        /**
	 * Возвращает имя группы
	 *
	 * @param integer $id         ИД группы
	 * @return string
	 */
	function GetGroupName($id){
		return $GLOBALS['DB']->val("SELECT name FROM prof_group WHERE id = ?", $id);
	}
    
    /**
	 * Возвращает имя группы с его ссылкой
	 *
	 * @param integer $id ИД группы
	 * @return string
	 */
    function GetGroupNameWP($id) {
        global $DB;
        if ((int) $id < 1) {
            return false;
        }
        $tmp = $DB->row("SELECT id, link, name FROM prof_group WHERE id = ?", $id);
        if ($tmp) {
            $link = '/freelancers/' . $tmp['link'] . '/';

            if ((int) $tmp['id'] > 0) {
                return '<a href="' . $link . '">' . $tmp['name'] . '</a>';
            }
        }
        return false;
    }

    
    
    /**
     * Список специализаций
     * 
     * @global DB $DB
     * @param type $ids
     * @param type $link
     * @param type $glue
     * @return boolean
     */
    public static function getGroupLinks($ids, $link = false, $glue = ', ')
    {
        global $DB;
        
        $list = $DB->rows("
            SELECT id, link, name 
            FROM prof_group 
            WHERE id IN(?l)", $ids);
        
        if ($list) {
            $data = array();
            foreach ($list as $item) {
                if ($link) {
                    $data[] = '<a href="/freelancers/' . $item['link'] . '/">' . $item['name'] . '</a>';
                } else {
                    $data[] = $item['name'];
                }
            }
            
            return implode($glue, $data);
        }
        
        return false;
    }



    /**
	 * Возвращает определенной поле из таблицы професий по его ИД
	 *
	 * @param integer $id         ИД Профессии
	 * @param string  $fieldname  Поля
	 * @return string
	 */
	function GetProfField($id, $fieldname){
		return $GLOBALS['DB']->val("SELECT {$fieldname} FROM professions WHERE id = ?", $id);
	}

/**
   * Возвращает определенной поле из таблицы професий с привязкой к стране и городу по его ИД
   *
   * @param integer $id         ИД Профессии
   * @param string  $fieldname  Поля
   * @param integer $country_id ID страны
   * @param integer $city_id    ID города
   * @return string
   */
  function GetProfGEOField($id, $fieldname, $country_id, $city_id){
    return $GLOBALS['DB']->val("SELECT {$fieldname} FROM professions_seo_geo WHERE profession_id = ? AND country_id = ? AND city_id = ?i", $id, $country_id, $city_id);
  }


	/**
	 * Возвращает название группы определенной профессии, по его ИД 
	 *
	 * @deprecated 
	 * 
	 * @param integer $id ИД Професии
	 * @return string
	 */
	function GetProfGroupName($id){
		return $GLOBALS['DB']->val("SELECT pg.name FROM professions p INNER JOIN prof_group pg ON pg.id = p.prof_group WHERE p.id = ?", $id);
	}
	
	/**
	 * Определение типа профессии.
	 *
	 * @param integer $id id профессии
	 * @return boolean истина, если это текстовая профессия, ложь, если нет
	 */
	function GetProfType($id){
		$ret = $GLOBALS['DB']->val("SELECT professions.is_text FROM professions WHERE id = ?", $id);
		return ($ret == 't');
	}

	/**
	 * Определение id профессии по seo ссылке
	 * 
	 * @param  string  $link  имя ссылки
	 * @return integer        id профессии или 0, если профессия не найдена
	 */
	function GetProfId($link) {
		return (int) $GLOBALS['DB']->val("SELECT id FROM professions WHERE link = ? LIMIT 1", $link);
	}

    /**
     * Определение id группы по seo ссылке
     *
     * @param  string  $link  имя ссылки
     * @return integer        id группы или 0, если группа не найдена
     */
    function GetProfGroupId($link) {
        return (int) $GLOBALS['DB']->val("SELECT id FROM prof_group WHERE link = ? LIMIT 1", $link);
    }

    /**
     * Определение id группы роителя по seo ссылке
     *
     * @param  string  $link  имя ссылки
     * @return integer        id группы или 0, если группа не найдена
     */
    function GetProfGroupParentId($link) {
        return (int) $GLOBALS['DB']->val("SELECT prof_group FROM professions WHERE link = ? LIMIT 1", $link);
    }

    
	/**
	 * Возвращает SEO ссылку профессии
	 *
	 * @param  int $id id профессии
	 * @return string
	 */
	function GetProfLink($id) {
		return $GLOBALS['DB']->val("SELECT link FROM professions WHERE id = ?", $id);
	}

    
    function GetGroupLink($id) {
		return $GLOBALS['DB']->val("SELECT link FROM prof_group WHERE id = ?", $id);
	}
    
	/**
	 * Выбирает все основные группы профессий для зеленой менюхи
	 * (странная функция, КАТАСТРОФИЧЕСКИ тяжелая)
	 *
	 * @return array
	 */
	function GetAllGroups(){
		return $GLOBALS['DB']->rows("
			SELECT prof_group.name, prof_group.id, SUM(t.cnt) as cnt  FROM (SELECT prof_id, COUNT(*) as cnt FROM (SELECT user_id, prof_id FROM portf_choise UNION SELECT uid as user_id, spec as prof_id FROM freelancer) as d GROUP BY prof_id) as t
            LEFT JOIN professions ON professions.id = t.prof_id
            FULL JOIN prof_group
            ON prof_group.id = professions.prof_group
            GROUP BY prof_group.name, prof_group.id, prof_group.n_order
            ORDER BY prof_group.n_order
		");
	}

  /**
   * Получает ID групп по данным страны и города
   *
   * @param    integer    $country    ID страницы
   * @param    integer    $city       ID города
   * @return   array                  Информация о группах
   */
  function GetGEOGroups($country=0, $city=0) {
    global $DB;
    $sql = "SELECT * FROM professions_seo_geo WHERE country_id = ?i AND city_id = ?i";
    $res = $DB->rows($sql, $country, $city);
    $ret = array();
    if($res) {
      foreach($res as $row) {
        $ret[$row['profession_id']] = $row;
      }
    }
    return $ret;
  }
	
	/**
	 * Выбирает все основные группы профессий без подсчета кол-ва юзеров в них
	 *
	 * @param boolean $only_active 
	 * @return array
	 */
  function GetAllGroupsLite($only_active=FALSE, $idkey = false){
     global $DB;
     $sql = '
         SELECT g.name, g.id, g.name_case 
         FROM prof_group g'.($only_active ? ' WHERE g.id > 0 AND EXISTS (SELECT 1 FROM professions p WHERE p.prof_group = g.id AND p.id > 0)' : '').' 
         ORDER BY g.cnt DESC';
     
		$res = $DB->query($sql);
		if($idkey) {
		    while($row=pg_fetch_assoc($res))
		       $ret[$row['id']]=$row;
		}
		else
		    $ret = pg_fetch_all($res);
		
		return $ret;
	}
	
	/**
	 * Возвращает группу профессии по его ИД
	 *
	 * @param integer $id     ИД Группы
	 * @param string $error  Возвращает сообщение об ошибке, если она есть
	 * @return array
	 */
	function GetGroup($id, &$error){
		return $GLOBALS['DB']->row("SELECT * FROM prof_group WHERE id = ?", $id);
	}
	
	/**
	 * Взять данные специализаций по логину пользователя
	 *
	 * @param string $login  Логин пользователя
	 * @return array
	 */
	function GetSpecs($login) {
		return $GLOBALS['DB']->rows("
			SELECT professions.name, spec_choise.prof_id, professions.prof_group, spec_choise.prise, spec_choise.m_time
			FROM professions
			LEFT JOIN prof_group ON prof_group.id = professions.prof_group
			LEFT JOIN spec_choise ON spec_choise.prof_id=professions.id
			LEFT JOIN users ON spec_choise.user_id = uid
			WHERE (login = ?) ORDER BY prof_group.n_order, professions.n_order
		", $login);
	}
	
	/**
	 * Ввзять выбранные пользователем профессии
	 *
	 * @param integer $fid    ИД Пользователя
	 * @return array
	 */
	function GetSelProf($fid) {
		$res = $GLOBALS['DB']->query("SELECT professions.id as prof_id, professions.name FROM professions LEFT JOIN portf_choise ON portf_choise.prof_id=professions.id WHERE (portf_choise.user_id = ?)", $fid);
		$ret = (pg_num_rows($res) == 0)? 0 : @pg_fetch_all($res);
		return $ret;
	}

	/**
	 * Получение для юзера списка выбранных им профессий, в которые им добавлены работы с подгуженными файлами.
	 *
	 * @param integer $fid     ИД Пользователя
	 * @param boolean $onlyWithPreview если true - то выбраны будут только те категории в которых есть хоть одна работа с превью
	 * @return array|integer - O если ничего нет
	 */
	function GetSelFilProf($fid, $onlyWithPreview = false) {
        $fileFilter = $onlyWithPreview ? "prev_pict <> ''" : "(pict<>'' OR prev_pict<>'')";
		$res = $GLOBALS['DB']->query("
			SELECT professions.id, professions.name FROM professions
            LEFT JOIN portf_choise ON portf_choise.prof_id=professions.id
            WHERE (portf_choise.user_id = ?) AND EXISTS(SELECT id FROM portfolio WHERE portfolio.prof_id=professions.id AND portfolio.user_id=portf_choise.user_id AND $fileFilter) ORDER BY portf_choise.ordering
		", $fid);
		$ret = (pg_num_rows($res) == 0)? 0 : @pg_fetch_all($res);
		return $ret;
	}
    
    /**
     * Получение информации по конкретному разделу для конкретного фрилансера.
     * 
     * @param  int $fid код фрилансера
     * @param  int $prof_id код раздела
     * @return array
     */
    function GetProfDesc( $fid = 0, $prof_id = 0 ) {
        return $GLOBALS['DB']->row( "SELECT  
                pg.name as mainprofname, 
                p.name as profname, p.is_text as proftext, p.link AS proflink,
                pc.prof_id, p.id as prof_origin, pc.ordering, pc.show_comms as gr_comms, pc.show_preview as gr_prevs,
                pc.cost_from, pc.cost_to, pc.time_type, pc.time_from, pc.time_to, pc.cost_hour,
                pc.cost_1000, pc.cost_type, pc.cost_type_hour, pc.portf_text, m.on_moder 
            FROM portf_choise pc 
            INNER JOIN professions p ON p.id = COALESCE( pc.prof_origin, pc.prof_id )
            INNER JOIN prof_group pg ON pg.id = p.prof_group
            LEFT JOIN (
                SELECT user_id, prof_id, COUNT(id) AS on_moder FROM portf_choise_change WHERE ucolumn = 'text' GROUP BY user_id, prof_id 
            ) AS m ON m.user_id = pc.user_id AND m.prof_id = pc.prof_id 
            WHERE pc.user_id = ?i AND pc.prof_id = ?i", $fid, $prof_id );
    }
	
   /**
    * Сохранение информации по конкретному разделу для конкретного фрилансера.
    *
    * @param integer $fid код фрилансера
    * @param integer $prof_id код раздела
    * @param float $cost_from стоимость от
    * @param float $cost_to стоимость до
    * @param float $cost_hour оценка стоимости часа работы
    * @param integer $time_from срок в днях от
    * @param integer $time_to срок в днях до
    * @param string $text пояснительный текст к разделу
    * @param integer $moduser_id UID изменяющего пользователя (админа). если null - то берется $fid
    * @param string $modified_reason причина редактирования
    * @return string текст ошибки или пустая строка
    */
    function UpdateProfDesc( $fid, $prof_id, $cost_from, $cost_to, $cost_hour, $cost_1000, $cost_type, $cost_type_hour, $time_type, $time_from, $time_to, $text, &$errorProfText, $moduser_id = null, $modified_reason = '' ) {
        global $DB;
		$id = intval($fid);
        $prof_id = intval($prof_id);
        $cost_from = intval($cost_from * 100) / 100;
        $cost_to = intval($cost_to * 100) / 100;
        $cost_hour = intval($cost_hour * 100) / 100;
        $cost_1000 = intval($cost_1000 * 100) / 100;
        $cost_type = intval($cost_type);
        $cost_type_hour = intval($cost_type_hour);
        $time_type = intval($time_type);
        if ($time_type < 0) {
          $time_type = 0;
        }
        if ($time_type > 3)
        {
          $time_type = 2;
        }
        $time_from = intval($time_from);
        $time_to = intval($time_to);
        $error = '';
        $moduser_id = $moduser_id ? $moduser_id : $id;
        
        if (isset($text) && ($text != ''))
        {
          $text = trim(preg_replace_callback("|(\w{70,})|", create_function('$matches','return wordwrap($matches[1], 64, " ", 1);'), $text));
    //      $text = preg_replace("|[\s]+|", " ", $text);
          $text = preg_replace("|[\t]+|", " ", $text);
          $text = preg_replace("|[ ]+|", " ", $text);
          $text = stripslashes(change_q_x_a($text, false, false, "b|i|p|ul|li{1}"));
          if (strlen($text) > 300) {
              $error .= (($error == '') ? '' : '<br />') . 'Максимальная длина уточнения к разделу 300 символов';
              $errorProfText = $text; // нужен чтобы подставить в textarea
          }
        }
        if ($text == '') $text = "NULL"; else $text = "'" . $text . "'";
        
        /**
         * Проверка.
         */
    
            switch($cost_type) {
                case 0:
                    // USD
            		if (($cost_1000 < 0) || ($cost_1000 > PROF_COST_1000_USD))
            		{
            		  $error .= (($error == '') ? '' : '<br />') . 'Недопустимое значение. Стоимость 1000 знаков должна быть в пределе от 0 до ' . PROF_COST_1000_USD . ' $.';
            		}
                    break;
                case 1:
                    // EU
            		if (($cost_1000 < 0) || ($cost_1000 > PROF_COST_1000_EU))
            		{
            		  $error .= (($error == '') ? '' : '<br />') . 'Недопустимое значение. Стоимость 1000 знаков должна быть в пределе от 0 до ' . PROF_COST_1000_EU . ' евро.';
            		}
                    break;
                case 2:
                    // RUB
            		if (($cost_1000 < 0) || ($cost_1000 > PROF_COST_1000_RUB))
            		{
            		  $error .= (($error == '') ? '' : '<br />') . 'Недопустимое значение. Стоимость 1000 знаков должна быть в пределе от 0 до ' . PROF_COST_1000_RUB . ' рублей.';
            		}
                    break;
                case 3:
                    // FM
            		if (($cost_1000 < 0) || ($cost_1000 > PROF_COST_1000_FM))
            		{
            		  $error .= (($error == '') ? '' : '<br />') . 'Недопустимое значение. Стоимость 1000 знаков должна быть в пределе от 0 до ' . PROF_COST_1000_FM . ' FM.';
            		}
                    break;
            }
    
            switch($cost_type_hour) {
                case 0:
                    // USD
            		if (($cost_hour < 0) || ($cost_hour > PROF_COST_HOUR_USD))
            		{
            		  $error .= (($error == '') ? '' : '<br />') . 'Недопустимое значение. Стоимость часа работы должна быть в пределе от 0 до ' . PROF_COST_HOUR_USD . ' $.';
            		}
                    break;
                case 1:
                    // EU
            		if (($cost_hour < 0) || ($cost_hour > PROF_COST_HOUR_EU))
            		{
            		  $error .= (($error == '') ? '' : '<br />') . 'Недопустимое значение. Стоимость часа работы должна быть в пределе от 0 до ' . PROF_COST_HOUR_EU . ' евро.';
            		}
                    break;
                case 2:
                    // RUB
            		if (($cost_hour < 0) || ($cost_hour > PROF_COST_HOUR_RUB))
            		{
            		  $error .= (($error == '') ? '' : '<br />') . 'Недопустимое значение. Стоимость часа работы должна быть в пределе от 0 до ' . PROF_COST_HOUR_RUB . ' рублей.';
            		}
                    break;
                case 3:
                    // FM
            		if (($cost_hour < 0) || ($cost_hour > PROF_COST_HOUR_FM))
            		{
            		  $error .= (($error == '') ? '' : '<br />') . 'Недопустимое значение. Стоимость часа работы должна быть в пределе от 0 до ' . PROF_COST_HOUR_FM . ' FM.';
            		}
                    break;
            }
    
            switch($cost_type) {
                case 0:
                    // USD
            		if (($cost_from < 0) || ($cost_to > 100000) || ($cost_from > 100000))
            		{
            		  $error .= (($error == '') ? '' : '<br />') . 'Недопустимое значение. Стоимость работ должна быть в пределе от 0 до ' . 100000 . ' $.';
            		}
                    break;
                case 1:
                    // EU
            		if (($cost_from < 0) || ($cost_to > 100000) || ($cost_from > 100000))
            		{
            		  $error .= (($error == '') ? '' : '<br />') . 'Недопустимое значение. Стоимость работ должна быть в пределе от 0 до ' . 100000 . ' евро.';
            		}
                    break;
                case 2:
                    // RUB
            		if (($cost_from < 0) || ($cost_to > 5000000) || ($cost_from > 5000000))
            		{
            		  $error .= (($error == '') ? '' : '<br />') . 'Недопустимое значение. Стоимость работ должна быть в пределе от 0 до ' . 5000000 . ' рублей.';
            		}
                    break;
                case 3:
                    // FM
            		if (($cost_from < 0) || ($cost_to > 100000) || ($cost_from > 100000))
            		{
            		  $error .= (($error == '') ? '' : '<br />') . 'Недопустимое значение. Стоимость работ должна быть в пределе от 0 до ' . 100000 . ' FM.';
            		}
                    break;
            }
    
    
    
    		if (($cost_from > 0) && ($cost_to > 0) && ($cost_to < $cost_from))
    		{
    		  $error .= (($error == '') ? '' : '<br />') . 'Недопустимое значение. Конечная стоимость не должна быть меньше начальной.';
    		}
    		if (($time_from < 0) || ($time_from > PROF_TIME_FROM))
    		{
    		  $error .= (($error == '') ? '' : '<br />') . 'Недопустимое значение. Начальный срок должен быть в пределе от 0 до ' . PROF_TIME_FROM . '.';
    		}
    		if (($time_to < 0) || ($time_to > PROF_TIME_TO))
    		{
    		  $error .= (($error == '') ? '' : '<br />') . 'Недопустимое значение. Конечный срок должен быть в пределе от 0 до ' . PROF_TIME_TO . '.';
    		}
    		if (($time_to < $time_from) && ($time_to > 0))
    		{
    		  $error .= (($error == '') ? '' : '<br />') . 'Недопустимое значение. Конечный срок не должен быть меньше начального.';
    		}
    
    		if ($prof_id && $id && ($error == ''))
        {
          $sql .= "UPDATE portf_choise SET cost_from=$cost_from, cost_to=$cost_to, cost_hour=$cost_hour, cost_1000=$cost_1000, cost_type='$cost_type', cost_type_hour='$cost_type_hour', time_type=$time_type, time_from=$time_from, time_to=$time_to, portf_text=$text, moduser_id=$moduser_id, modified = now(), modified_reason = '$modified_reason' WHERE (user_id='$id' AND prof_id='$prof_id'); ";
          $DB->query($sql);
          $error_db = pg_errormessage();
    		  if ($error_db != '')
    		  {
            $error .= (($error_serv == '') ? '' : '<br />') . 'Ошибка сохранения в БД.';
    		  }
              elseif ( $id && $prof_id ) {
                  $sId = $DB->val( "SELECT id FROM portf_choise_change WHERE user_id = ?i AND prof_id = ?i AND ucolumn = 'text';", $id, $prof_id );
                  
                  if ( $id == $moduser_id && !hasPermissions('users') ) {
                      require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );
                      
                      $stop_words    = new stop_words();
                      $nStopWordsCnt = $stop_words->calculate( $text );
                      
                      // сам юзер
                      if ( !$sId && !empty($text) && $text != "NULL" ) {
                            $nModeratorStatus = is_pro() ? -2 : 0;
                            $sId = $DB->val( "INSERT INTO portf_choise_change (user_id, prof_id, ucolumn, stop_words_cnt, old_val, moderator_status) 
                            VALUES (?i, ?i, 'text', ?i, ?, ?i) RETURNING id", $id, $prof_id, $nStopWordsCnt, $text, $nModeratorStatus );
                            
                            if ( $nModeratorStatus == 0 ) {
                                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
                                $DB->insert( 'moderation', array('rec_id' => $sId, 'rec_type' => user_content::MODER_PORTF_CHOISE, 'stop_words_cnt' => $nStopWordsCnt) );
                            }
                      }
                      else {
                          require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
                          
                          if ( $sId && !empty($text) && $text != "NULL" ) {
                            $DB->query( 'UPDATE portf_choise_change SET stop_words_cnt = ?i WHERE id = ?i', $nStopWordsCnt, $sId );
                            $DB->query( 'UPDATE moderation SET stream_id = NULL, stop_words_cnt = ?i WHERE rec_id = ?i AND rec_type = ?i', $nStopWordsCnt, $sId, user_content::MODER_PORTF_CHOISE );
                          }
                          else {
                            $DB->query( 'DELETE FROM portf_choise_change WHERE id = ?i;
                                DELETE FROM moderation WHERE rec_id = ?i AND rec_type = ?i', $sId, $sId, user_content::MODER_PORTF_CHOISE );
                          }
                      }
                  }
                  elseif ( hasPermissions('users') ) {
                      if ( $sId ) {
                          require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
                          $DB->query( 'DELETE FROM portf_choise_change WHERE id = ?i;
                            DELETE FROM moderation WHERE rec_id = ?i AND rec_type = ?i', $sId, $sId, user_content::MODER_PORTF_CHOISE );
                      }
                  }
              }
        }
        if ($error != '')
        {
          $error = 'Данные не сохранены<br /><br />' . $error;
        }
        return $error;
    }
    
    public function updateUserKeywordsProfessions($uid, $prof_id, $keywords) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/kwords.php");
        $kwords  = new kwords(); 
        $aOldIds = array_keys($kwords->getUserKeys($uid, $prof_id));
        $ids     = array();
        $kwords->delUserKeys($uid, $prof_id);
        if (trim($keywords)) {
            $ukey = explode(",", $keywords);
            if (count($ukey) > 0) {
                $ukey = array_map( 'antispam', $ukey );
                $ids = $kwords->add($ukey, true);
                $kwords->addUserKeys($uid, $ids, $prof_id);
            }
        }
        $kwords->moderUserKeys($uid, $prof_id, $aOldIds, $ids, $uid, $keywords);
    }
    
    public function updateProfessionUser($uid, $prof_id, $params) {
        global $DB;
        
        $uid     = intval($uid);
        $prof_id = intval($prof_id);
        $params  = $this->prepareRequest($params);
        $this->validate($params);
        
        if(empty($this->errors)) {
            $this->update = array(
                'cost_from'      => $params['prof_cost_from'], 
                'cost_to'        => $params['prof_cost_to'],
                'cost_hour'      => $params['prof_cost_hour'], 
                'cost_1000'      => $params['prof_cost1000'], 
                'cost_type'      => $params['prof_cost_type_db_id'],
                'cost_type_hour' => $params['prof_cost_type_hour_db_id'],
                'time_type'      => $params['prof_time_type_db_id'],
                'time_from'      => $params['prof_time_from'], 
                'time_to'        => $params['prof_time_to'], 
                'portf_text'     => antispam( $params['portf_text'] ),
                'show_preview'   => $params['on_preview'] ? 't' : 'f',
                'modified'       => 'NOW()',
            );
            
            $is_upd = $DB->update("portf_choise", $this->update, "user_id= ?i AND prof_id = ?i", $uid, $prof_id);
            
            if($is_upd) {
                $this->checkUserContent($uid, $prof_id, $params['portf_text'], $params['old_portf_text']);
            } else {
                $this->errors['error'] = 'Данные не сохранены';
            }
        }
    }
    
    public function prepareCostText($prof, $stop_words = null) {
        if($stop_words == null) $stop_words = new stop_words( hasPermissions('users') );
        
        $prof['portf_text'] = trim( $prof['on_moder'] && $user->is_pro != 't' ? $stop_words->replace($prof['portf_text']) : $prof['portf_text'] );
        
        if ($prof['proftext'] == 't') { 
            $prof['cost_text']      = view_cost2($prof['cost_1000'], '', '', false, $prof['cost_type']);
            $prof['cost_hour_text'] = view_cost2($prof['cost_hour'], '', '', false, $prof['cost_type_hour']); 
        } else {
            $prof['time_text']      = view_range_time($prof['time_from'], $prof['time_to'], $prof['time_type']);
            $prof['cost_from_text'] = view_cost2($prof['cost_from'], '', '', false, $prof['cost_type']);
            $prof['cost_to_text']   = view_cost2($prof['cost_to'], '', '', false, $prof['cost_type']);
            $prof['cost_hour_text'] = view_cost2($prof['cost_hour'], '', '', false, $prof['cost_type_hour']);
            $prof['from_text']      = $prof['cost_from_text'] != '' ? "от ".$prof['cost_from_text'] : "";
            $prof['to_text']        = $prof['cost_to_text']   != '' ? "до ".$prof['cost_to_text']   : "";
        }
        $prof['is_pro_profession'] = ($prof['prof_id'] == professions::BEST_PROF_ID || $prof['prof_id'] == professions::CLIENTS_PROF_ID);
        
        return $prof;
    }
    
    public function loadProfessionUserKeyword($uid, $prof_id) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/kwords.php");
        $html_keyword_js = '<a href="/freelancers/?word=$1" class="inherit">$2</a>';
        $html_keyword = preg_replace('/\$\d/', '%s', $html_keyword_js);
        
        $stop_words = new stop_words( hasPermissions('users') );
        $wkeys  = kwords::getUserKeys($uid, $prof_id);
        $modkey = kwords::isModerUserKeys($uid, $prof_id);
        $kword_count = 0;
        $c = 0;
        if ($wkeys) {
            $links_keyword = array();
            $links_keyword_hide = array();
            $kword_count = count($wkeys);
            foreach ($wkeys as $key) {
                $sKey = stripslashes($modkey ? $stop_words->replace($key, 'plain') : $key);

                if (++$c > kwords::MAX_KWORDS_PORTFOLIO) {
                    $links_keyword_hide[] = urlencode($sKey) . ',,' . change_q_x($sKey, true, false);
                } else {
                    $links_keyword[] = sprintf($html_keyword, urlencode($sKey), change_q_x($sKey, true, false));
                }
            }

            $wkeys['links_keyword'] = $links_keyword;
            $wkeys['links_keyword_hide'] = $links_keyword_hide;
            $wkeys['count'] = $kword_count;
        }
        return $wkeys;
    }
    
    public function changePositionProfessionsUser($position, $uid, $prof_id) {
       global $DB;
       return $DB->query("UPDATE portf_choise SET ordering = ?i WHERE user_id = ?i AND prof_id = ?i", $position, $uid, $prof_id);
    }
    
    /**
     * Отправка уточнения к разделу в портфолио на модерирование
     * 
     * @global DB $DB
     * @param  int $uid UID пользователя
     * @param  int $prof_id ID профессии
     * @param  string $text новый текст уточнения
     * @param  string $old_text старый текст уточнения
     */
    public function checkUserContent($uid, $prof_id, $text, $old_text) {
        global $DB;
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );
        
        $choise_id = $DB->val("SELECT id FROM portf_choise_change WHERE user_id = ?i AND prof_id = ?i AND ucolumn = 'text';", $uid, $prof_id);
        $stop_words = new stop_words();
        $nStopWordsCnt = $stop_words->calculate($text);

        // сам юзер
        if ( !$choise_id && !empty($text) && $text != "" && $nStopWordsCnt ) {
            $nModeratorStatus = is_pro() ? -2 : 0;
            $sId = $DB->val("INSERT INTO portf_choise_change (user_id, prof_id, ucolumn, stop_words_cnt, old_val, moderator_status) 
                             VALUES (?i, ?i, 'text', ?i, ?, ?i) RETURNING id", $uid, $prof_id, $nStopWordsCnt, $old_text, $nModeratorStatus);

            if ($nModeratorStatus == 0) {
                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
                $DB->insert('moderation', array('rec_id' => $sId, 'rec_type' => user_content::MODER_PORTF_CHOISE, 'stop_words_cnt' => $nStopWordsCnt));
            }
        } else {
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );

            if ( $choise_id && !empty($text) && $text != "" && $nStopWordsCnt ) {
                $DB->query('UPDATE portf_choise_change SET stop_words_cnt = ?i WHERE id = ?i', $nStopWordsCnt, $choise_id);
                $DB->query('UPDATE moderation SET stream_id = NULL, stop_words_cnt = ?i WHERE rec_id = ?i AND rec_type = ?i', $nStopWordsCnt, $choise_id, user_content::MODER_PORTF_CHOISE);
            } else {
                $DB->query('DELETE FROM portf_choise_change WHERE id = ?i;
                            DELETE FROM moderation WHERE rec_id = ?i AND rec_type = ?i', $choise_id, $choise_id, user_content::MODER_PORTF_CHOISE);
            }
        }
    }
    
    public function prepareRequest($params) {
        foreach($params as $name=>$value) {
            switch($name) {
                case 'portf_text':
                    $params[$name] = ( __paramValue('html_save_ul_li_b_p_i', trim($params['portf_text']) ) );
                    break;
                case 'old_portf_text':
                    $params[$name] = ( __paramValue('html_save_ul_li_b_p_i', trim($params['old_portf_text']) ) );
                    break;
                case 'prof_cost_type_hour_db_id':
                case 'prof_cost_type_db_id':
                case 'prof_time_type_db_id':
                case 'prof_time_from':
                case 'prof_time_to':
                case 'on_preview':
                    $params[$name] = intval($value);
                    break;
                case 'prof_cost_hour':
                case 'prof_cost_to':
                case 'prof_cost_from':
                case 'prof_cost1000':
                    $params[$name] = intval($value * 100) / 100;
                    break;      
            }
        }
        return $params;
    }
    
    public function validate($params) {
        $this->errors = array(); // Обнуляем ошибки, проверяем заного
        foreach($params as $name=>$value) {
            switch($name) {
                // Уточнение к разделу
                case 'portf_text':
                    if (strlen($value) > 300) {
                        $this->errors[$name] = 'Максимальная длина уточнения к разделу 300 символов';
                    } 
                    break;
                // Стоимость часа работы (в часах, днях в минутах) ОТ - ДО
                case "prof_time_from":
                    if ( $value < 0 || $value > PROF_TIME_FROM ) {
                        $this->errors[$name] = 'Недопустимое значение. Начальный срок должен быть в пределе от 0 до ' . PROF_TIME_FROM . '.';
                    }
                    break;
                case "prof_time_to":
                    if ( $value < 0 || $value > PROF_TIME_TO ) {
                        $this->errors[$name] = 'Недопустимое значение. Конечный срок должен быть в пределе от 0 до ' . PROF_TIME_TO . '.';
                    } elseif($value > 0 && $value < $params['prof_time_from']) {
                        $this->errors[$name] = 'Недопустимое значение. Конечный срок не должен быть меньше начального';
                    }
                    break;
                // Стоимость работ ОТ - ДО    
                case "prof_cost_to":
                    if($value > 0 && $params['prof_cost_from'] > 0 && $value < $params['prof_cost_from'] ) {
                        $this->errors[$name] = 'Недопустимое значение. Конечная стоимость не должна быть меньше начальной.';
                    }
                case "prof_cost_from":
                    $cost_type = (int) $params['prof_cost_type_db_id'];
                    
                    if ( ($value < 0 || $value > 100000)  && $cost_type == 0) { // USD
                        $this->errors[$name] = 'Недопустимое значение. Стоимость работ должна быть в пределе от 0 до 100000 $.';
                    } elseif ( ($value < 0 || $value > 100000)  && $cost_type == 1) { // EUR
                        $this->errors[$name] = 'Недопустимое значение. Стоимость работ должна быть в пределе от 0 до 100000 Евро.';
                    } elseif ( ($value < 0 || $value > 5000000) && $cost_type == 2) { // RUB
                        $this->errors[$name] = 'Недопустимое значение. Стоимость работ должна быть в пределе от 0 до 5000000 рублей.';
                    }
                    break;
                // Стоимость часа работы    
                case "prof_cost_hour":
                    $cost_type = (int) $params['prof_cost_type_hour_db_id'];
                    if( $value < 0 || $value > PROF_COST_HOUR_USD && $cost_type == 0 ) {
                        $this->errors[$name] = 'Недопустимое значение. Стоимость часа работы должна быть в пределе от 0 до ' . PROF_COST_HOUR_USD . ' $.';
                    } elseif( $value < 0 || $value > PROF_COST_HOUR_EU && $cost_type == 1 ) {
                        $this->errors[$name] = 'Недопустимое значение. Стоимость часа работы должна быть в пределе от 0 до ' . PROF_COST_HOUR_EU . ' Евро.';
                    } elseif( $value < 0 || $value > PROF_COST_HOUR_RUB && $cost_type == 2 ) {
                        $this->errors[$name] = 'Недопустимое значение. Стоимость часа работы должна быть в пределе от 0 до ' . PROF_COST_HOUR_RUB . ' рублей.';
                    }
                    break;
                // Стоимость 1000 знаков
                case "prof_cost1000":
                    $cost_type = (int) $params['prof_cost_type_db_id'];
                    if( $value < 0 || $value > PROF_COST_1000_USD && $cost_type == 0 ) {
                        $this->errors[$name] = 'Недопустимое значение. Стоимость 1000 знаков должна быть в пределе от 0 до ' . PROF_COST_HOUR_USD . ' $.';
                    } elseif( $value < 0 || $value > PROF_COST_1000_EU && $cost_type == 1 ) {
                        $this->errors[$name] = 'Недопустимое значение. Стоимость 1000 знаков должна быть в пределе от 0 до ' . PROF_COST_HOUR_EU . ' Евро.';
                    } elseif( $value < 0 || $value > PROF_COST_1000_RUB && $cost_type == 2 ) {
                        $this->errors[$name] = 'Недопустимое значение. Стоимость 1000 знаков должна быть в пределе от 0 до ' . PROF_COST_HOUR_RUB . ' рублей.';
                    }
                    break;
                // Тип времени работы
                case 'prof_time_type_db_id':
                    if($value < 0 || $value > 3) {
                        $this->errors[$name] = 'Неизвестный идентификатор времени';
                    }
                    break;
                // Тип валюты
                case "prof_cost_type_hour_db_id":
                case "prof_cost_type_db_id":
                    if($value < 0 || $value > 2) {
                        $this->errors[$name] = 'Неизвестная валюта';
                    }
                    break;
            }
        }
    }
	
    /**
     * Обновляем описание специализации в разделе портфолио пользователя (Время и стоимость работы)
     *
     * @deprecated 
     * 
     * @param integer $fid        ИД Пользователя
     * @param array   $prof_ids   ИД Профессий 
     * @param array   $prises     Стоимость, Цены работ
     * @param array   $times      Время затраченное для работ  
     * @return string сообщение об ошибке если есть
     */
	function UpdateSpecDesc($fid, $prof_ids, $prises, $times){
		global $DB;
		$i = 0;
		if ($prof_ids){
			foreach ($prof_ids as $id){
				$id = (int)trim($id);
				$prise = substr(change_q($prises[$i], true, 13),0,64);
				$time = substr(change_q($times[$i], true, 13),0,128);
				if ($prise == '') $prise = "NULL"; else $prise = "'".$prise."'";
				if ($time == '') $time = "NULL"; else $time = "'".$time."'";
				$sql .= "UPDATE spec_choise SET prise=$prise, m_time=$time WHERE (user_id='$fid' AND prof_id='$id'); ";
				$i++;
			}
			$DB->query($sql);
			$error = $DB->error;
		}
		return $error;
	}
	
	/**
	 * Создать новую группу профессии
	 *
	 * @param string $name   название группы
	 * @return string Сообщение об ошибке если есть
	 */
    function NewGroup($name){
        global $DB;
		$DB->errors = FALSE;
		$DB->query("INSERT INTO prof_group (name) VALUES (?)", $name);
		$DB->errors = TRUE;
		return $DB->error;
	}
	
	/**
	 * Удаление группы професии
	 *
	 * @param string $id ИД группы
	 * @return string Сообщение об ошибке если есть
	 */
	function DeleteGroup($id){
        global $DB;
		$DB->query("DELETE FROM prof_group WHERE id=? RETURNING n_order", $id);
        return $DB->error;
    }
	
    /**
     * Редактирование группы профессий
     *
     * @param integer $id   Ид группы
     * @param string $name  Название группы
     * @return string   Сообщение об ошибке если есть
     */
	function EditGroup($id, $name=NULL, $nameCase=NULL){
            global $DB;
            $uptext = '';
            if ( !is_null($name) ) {
                $uptext = $DB->parse('name = ?', $name);
            }
            if ( !is_null($nameCase) ) {
                $uptext .= $DB->parse(($uptext? ', name_case = ?': 'name_case = ?'), $nameCase);
            }
            if ( $uptext ) {
                $DB->query("UPDATE prof_group SET {$uptext} WHERE id = ?", $id);
            }
            return $DB->error;
	}
	
	/**
	 * Обновление позиции группы в выдаче на страницу
	 *
	 * @param integer $pos    Позиция
	 * @return string Сообщение об ошибке если есть
	 */
	function PosGroupChange($pos){
		global $DB;
        $DB->clear();
		$DB->hold()->query("PREPARE upd(int, int) AS UPDATE prof_group SET n_order=$1 WHERE id=$2");
		foreach ($pos as $newpos => $id) {
            $DB->hold->query("EXECUTE upd(?, ?)", $newpos, $id);
        }
		$DB->query();
		return '';
	}
	
	/**
	 * Новая профессия
	 *
	 * @param string  $name   Название професии
	 * @param integer $porfgr Ид Группы професии
	 * @param array   $out    Возвращает данные по созданной професиии
         * @param array   $keywords ключевые слова
	 * @return string Сообщение об ошибке если есть
	 */
    function NewProf($name, $porfgr, &$out=NULL, $keywords = NULL){
        global $DB;
        $link = translit($name);
		$out = $DB->row("INSERT INTO professions (name, prof_group, link) VALUES (?, ?, ?) RETURNING *", $name, $porfgr, $link);
        if(!empty($keywords) && !empty($out['id'])){
            self::saveKeywords($out['id'], $keywords);
        }
        return $DB->error;
    }
    
    /**
     * Обновляет ключевые слова для специализации
     *
     * @param  int $spec_id ID специализации
     * @param  array $keywords ключевые слова
     * @return mixed false если обновление ну было, пустую строку или сообщение об ошибке.
     */
    public static function saveKeywords($spec_id, $keywords){
        global $DB;
        $sql = "DELETE FROM professions_keywords WHERE prof_id = ?i;
            INSERT INTO professions_keywords (prof_id,keyword) VALUES ";
        $tmp = array();
        foreach ($keywords as $key){
            $tmp[] = '('.$spec_id.',\''.trim($key).'\')';
        }
        if(count($tmp)){
            $sql .= implode(', ', $tmp);
            $DB->query($sql,$spec_id);
            return $DB->error;
        }
        return false;
    }
    
    /**
     * Возвращает ключевые слова специализации
     * 
     * @param  int $spec_id ID специализации
     * @param  bool $as_string опционально. установить в true если слова нужно вернуть в виде строки через запятую
     * @return array|string в зависимости от $as_string
     */
    public static function getKeywords($spec_id, $as_string = false){
        global $DB;
        $sql = "SELECT keyword FROM professions_keywords WHERE prof_id = ?i;";
        $rows = $DB->rows($sql, $spec_id);
        $tmp = array();
        foreach ($rows as $key){
            $tmp[] = $key['keyword'];
        }
        return $as_string ? implode(', ',$tmp) : $tmp;
    }
	
    /**
     * Возвращает ID профессий, у которых есть определенные ключевые словаю.
     *
     * @param  string $keywords одно или несколько ключевых слов через запятую.
     * @return array
     */
    public static function getProfsByKeywords($keywords) {
        global $DB;
        setlocale(LC_ALL, 'ru_RU.CP1251');
        $e = explode(',', $keywords);
        foreach($e as $k=>$v) {
            if($v = trim($v))
                $m[] = strtoupper($v);
        }
        $ret = $DB->col('SELECT prof_id FROM professions_keywords WHERE upper(keyword) IN (?l)', $m);
        setlocale(LC_ALL, "en_US.UTF-8");
        return $ret;
    }
	
    /**
     * Удаляем професиию
     *
     * @param integer $id  ИД Професии
     * @return string Сообщение об ошибке если есть
     */
    function DeleteProf($id){
        global $DB;
		$DB->rows("DELETE FROM professions WHERE id=? RETURNING *", $id);
        return $DB->error;
    }
	
    /**
     * Редактирование професии
     *
     * @param integr  $id      ИД Профессии
     * @param string  $name    Название професии
     * @param string  $descr   Описание професии
     * @param boolean $istext  Признак профессии, связанной с написанием текстов
     * @param string  $title   title для страницы каталога данной профессии
	   * @param string  $descr   Расширенное описание професии
     * @param boolean $use_geo Нужна ли привязка к стране и городу
     * @param integer $country ID страны
     * @param integer $city    ID города
     * @param string $name_case
     * @param string $header_about заголовок для каталога в блок "О разделе"
     * @param string $header_list заголовок для каталога сразу перед списком фрилансеров
     * @return string Сообщение об ошибке если есть
     */
    function EditProf($id, $name=NULL, $descr=NULL, $istext="true", $title = NULL, $descr_text=NULL, $keywords = NULL, $use_geo = false, $country = 0, $city = 0, $name_case = null, $header_about = null, $header_list = null, $descr_text2 = null){
        global $DB;
		if($name===NULL && $descr===NULL && $descr_text===NULL) return 0;
        if($country) {
          $sql = "SELECT id FROM professions_seo_geo WHERE profession_id = ?i AND country_id = ?i AND city_id = ?i";
          $geo_id = $DB->val($sql, $id, $country, $city);
          if($geo_id) {
            $sql = "UPDATE professions_seo_geo SET descr=?, title=?, descr_text=?, descr_text2 = ? WHERE id=?i";
            $DB->query($sql, ($descr!==NULL ? $descr : ''),  ($title!==NULL ? $title : ''), ($descr_text!==NULL ? $descr_text : ''), ($descr_text2!==NULL ? $descr_text2 : ''), $geo_id);
          } else {
            $sql = "INSERT INTO professions_seo_geo(profession_id, country_id, city_id, descr, title, descr_text, descr_text2) VALUES(?i, ?i, ?i, ?, ?, ?, ?)";
            $DB->query($sql, $id, $country, $city, ($descr!==NULL ? $descr : ''),  ($title!==NULL ? $title : ''), ($descr_text!==NULL ? $descr_text : ''), ($descr_text2!==NULL ? $descr_text2 : ''));
          }
        } else {
          $DB->query("UPDATE professions SET 
                        name=".($name!==NULL ? "'{$name}'" : 'name').
                        ($descr!==NULL ? ", descr='{$descr}'" : '').
                        ($title!==NULL ? ", title='{$title}'" : '').
                        ($descr_text!==NULL ? ",descr_text='{$descr_text}'" : '').
                        ($descr_text2!==NULL ? ",descr_text2='{$descr_text2}'" : '').
                        ($name_case!==NULL ? ",name_case='{$name_case}'" : '').
                        ($header_about !== NULL ? ",header_about='{$header_about}'" : '').
                        ($header_list !== NULL ? ",header_list='{$header_list}'" : '').", 
                        is_text='$istext'
                      WHERE id='$id'");
          if(!empty($keywords)){
            self::saveKeywords($id, $keywords);
          }
        }
        return $DB->error;
	}

	/**
	 * Поднять позиция професии в выдаче на страницу на один пункт
	 *
	 * @param integer $id ИД Професии
	 * @return string Сообщение об ошибке если есть
	 */
    function PosProfUp($id) {
		global $DB;
		$DB->query("UPDATE professions SET n_order = n_order - 1 WHERE id = ? AND n_order > 1", $id);
        return $DB->error;
    }

    /**
     * Поднять позицию группы в выдаче на страницу на один пункт
     *
     * @param integer $id  Ид ГРуппы
     * @return string Сообщение об ошибке если есть
     */
    function PosGroupUp($id) {
        global $DB;
		$DB->query("UPDATE prof_group SET n_order = n_order - 1 WHERE id = ? AND n_order > 1", $id);
        return $DB->error;
    }

	/**
	 * Изменить позицию професии в выдаче на страницу
	 *
	 * @param integer $pos    Позиция
	 * @return string  Сообщение об ошибке если есть
	 */
	function PosProfChange($pos){
		global $DB;
        $DB->clear();
		$DB->hold()->query("PREPARE upd(int, int) AS UPDATE professions SET n_order=$1 WHERE id=$2");
		foreach ($pos as $newpos => $id) {
			$DB->hold()->query("EXECUTE upd(?, ?)", $newpos, $id);
        }
		$DB->query();
		return '';
	}
	
	/**
	 * Взять профессии учитывая mirrored
	 *
	 * @param string $profid Ид професии
	 * @return array
	 */
	function GetMirroredProfs($profid){
		global $DB;
        if (!$profid || $profid === '') {
            return array(0);
        } else {
            $profid = explode(',',$profid);
        }
		return $DB->col("
			SELECT mirror_prof FROM mirrored_professions WHERE main_prof IN (?l)
			UNION
			SELECT main_prof FROM mirrored_professions WHERE mirror_prof IN (?l)
			UNION
			SELECT id FROM professions WHERE id IN (?l)
		", $profid, $profid, $profid);
	}
	
	/**
	 * Возвращает "основной" идентификатор профессии, если находит его в таблице mirrored_professions
	 * иначе возвращает $profid
	 *
     * @param integer|string|array $profid    идентификатор профессии, или несколько, разделенных запятыми.
     * @return integer|string       идентификатор "основной" профессии или несколько, разделенные запятыми.
	 */
	function GetProfessionOrigin($profid) {
        global $DB;
        if(!is_array($profid)) {
            $profid = explode(',', $profid);
        }
        $ret = $DB->col("
        SELECT DISTINCT COALESCE(m.main_prof,p.id)
           FROM professions p
         LEFT JOIN
           mirrored_professions m
             ON m.mirror_prof = p.id
          WHERE p.id IN (?l)
		", $profid);
		if (empty($ret)) {
			return implode(',', $profid);
		}
   
        return implode(',',$ret);
	}
	
	/**
	 * Возвращает массив c ID всех основных и зеркальных профессий
	 *
	 * @return array
	 */
    function GetAllMirroredProfsId() {
		return $GLOBALS['DB']->rows("SELECT * FROM mirrored_professions");
	}

	/**
	 * Возвращает количество всех профессий
	 *
	 * @return string
	 */
	function CountAllProfs(){
		return $GLOBALS['DB']->val("SELECT COUNT(*) FROM professions");
	}
	
	/**
	 * Возвращает список профессий отсортированный по ИД
	 *
	 * @param array $profs  ИД профессий
	 * @return array
	 */
	function GetProfessions($profs){
		global $DB;
		if ($profs){
			$ret = $DB->rows("SELECT * FROM professions WHERE id IN (?l) ORDER BY id", $profs);
			if ($ret)
				foreach ($ret as $ikey => $value){
					if ($value['id'] == 0) $value['name'] = 'Все фрилансеры';
					$out[$value['id']] = $value;
				}
		}
		return $out;
	}

	/**
	 * Получение имен двух профессий по их id
	 *
	 * @param integer $old_prof_id ИД Профессии
	 * @param integer $new_prof_id ИД Профессии
	 * @return array массив из двух имен профессий
	 */
	function GetChangeProfNames($old_prof_id, $new_prof_id)
	{
		return $GLOBALS['DB']->row("SELECT (SELECT name FROM professions WHERE id=?) AS old_name, (SELECT name FROM professions WHERE id=?) AS new_name ", $old_prof_id, $new_prof_id);
	}

	/**
	 * Обновление дополнительных специальностей фрилансера
	 *
	 * @param integer $user_id ИД Пользователя
	 * @param array $specs ИД Специализаций
	 * @return boolean 
	 */
    function UpdateProfsAddSpec($user_id, $oldprof_id, $prof_id, $paid_id)
	{
        global $DB;
		$nprof_id = 'NULL';
        $prof_orig = 'NULL';
        if($prof_id) {
            $nprof_id = self::GetProfessionOrigin($prof_id);
            if($nprof_id != $prof_id)
                $prof_orig = $prof_id;
        }
        if($paid_id) {
            $sql = "UPDATE spec_paid_choise SET prof_id = {$nprof_id}, prof_origin = {$prof_orig} WHERE user_id = {$user_id} AND id = {$paid_id}";
        }
        else {
            if($oldprof_id) {
                if(!$prof_id) {
                    freelancer::clearCacheFromProfIdNow($oldprof_id);
                    return !!$DB->query("DELETE FROM spec_add_choise WHERE user_id = ? AND prof_id = ?", $user_id, $oldprof_id);
                }
                $res = $DB->query("UPDATE spec_add_choise SET prof_id = ?, prof_origin = ? WHERE user_id = ? AND prof_id = ?", $nprof_id, ($prof_orig == 'NULL' ? NULL : $prof_orig), $user_id, $oldprof_id);
                freelancer::clearCacheFromProfIdNow($oldprof_id);
                freelancer::clearCacheFromProfIdNow($prof_orig);
                if(pg_affected_rows($res)) return true;
            }
            $sql = "
              INSERT INTO spec_add_choise (user_id, prof_id, prof_origin) 
              SELECT {$user_id}, {$nprof_id}, {$prof_orig}
               WHERE (SELECT COUNT(1) FROM spec_add_choise WHERE user_id = {$user_id}) < " . PROF_SPEC_ADD . "
            ";
            
            freelancer::clearCacheFromProfIdNow($prof_orig);
        }
        return $DB->query($sql);
	}

    /**
     * Перемещает вниз по списку дополнительную или платную специализацию
     *
     * @param  int $uid Ид пользователя
     * @param  int $paid_id ID професии если она платная (spec_paid_choise)
     * @param  int $prof_id ИД професии
     * @param  int $mode возвращает код действия
     * @return bool true - успех, false - провал
     */
    function downSpec($uid, $paid_id, $prof_id, &$mode) {
        global $DB;
		//if(!$prof_id) return false;
        $mode = 0;
        if(!$paid_id && !$prof_id) {
            list($pr, $max_pr, $prof_orig) = array(1, 0, NULL);
        }
        else {
            if($paid_id) {
                $sql = "
                  SELECT priority, (SELECT priority FROM spec_paid_choise WHERE user_id = ? ORDER BY priority DESC LIMIT 1), prof_origin
                    FROM spec_paid_choise s
                   WHERE s.user_id = ?
                     AND s.id = ?
                ";
            } else {
                $sql = "
                  SELECT priority, (SELECT priority FROM spec_add_choise WHERE user_id = ? ORDER BY priority DESC LIMIT 1), prof_origin
                    FROM spec_add_choise s
                   WHERE s.user_id = ?
                     AND s.prof_id = ?
                ";
            }
            list($pr, $max_pr, $prof_orig) = $DB->row($sql, $uid, $uid, $prof_id);
            if(!$pr) return false;
        }
        if(!$prof_orig) $prof_orig = 'NULL';
        if($paid_id) {
            if($pr == $max_pr) { return false;} // двигается вниз платная с последней позиции
            $sql = "UPDATE spec_paid_choise SET priority = priority - 1 WHERE user_id = {$uid} AND priority = {$pr} + 1;
                    UPDATE spec_paid_choise SET priority = priority + 1 WHERE user_id = {$uid} AND id = {$paid_id}";
        }
        else {
            if($pr == 1) {    //
                list($npaid_id, $nprof_id, $nprof_orig) = $DB->row("SELECT id, prof_id, prof_origin FROM spec_paid_choise WHERE user_id = ? ORDER BY NULLIF(priority,0) LIMIT 1", $uid);
                if(!$nprof_orig) $nprof_orig = 'NULL';
                if(!$prof_id) {
                    $mode = 2;
                    $sql = "
                      UPDATE spec_paid_choise SET prof_id = NULL, prof_origin = NULL WHERE id = {$npaid_id};
                      INSERT INTO spec_add_choise (user_id, prof_id, prof_origin) VALUES({$uid}, {$nprof_id}, {$nprof_orig});
                    ";
                }
                else {
                    if(!$nprof_id) {
                        $mode = 2;
                        $sql = "DELETE FROM spec_add_choise WHERE user_id = {$uid} AND prof_id = {$prof_id};";
                    } else {
                        $mode = 1;
                        $sql = "
                          UPDATE spec_paid_choise SET prof_id = 0, prof_origin = 0 WHERE id = {$npaid_id};
                          UPDATE spec_add_choise SET prof_id = {$nprof_id}, prof_origin = {$nprof_orig} WHERE user_id = {$uid} AND prof_id = {$prof_id};
                        ";
                    }
                    $sql .= "UPDATE spec_paid_choise SET prof_id = {$prof_id}, prof_origin = {$prof_orig} WHERE id = {$npaid_id}";
                }
            }
            else {
                $sql = "UPDATE spec_add_choise SET priority = priority + 1 WHERE user_id = {$uid} AND priority = {$pr} - 1;
                        UPDATE spec_add_choise SET priority = priority - 1 WHERE user_id = {$uid} AND prof_id = {$prof_id}";
            }
        }
        return !!$DB->query($sql);
    }

    /**
     * Перемещает вниз по списку дополнительную или платную специализацию
     * 
     * @global DB  $DB
     * @param  int $uid Ид пользователя
     * @param  int $paid_id ID професии если она платная (spec_paid_choise)
     * @param  int $prof_id ИД професии
     * @param  int $dir направление 1 или -1
     * @param  int $new_paid_id ID новой професии, если дополнительную или платную меняем местами
     * @return bool true - успех, false - провал
     */
    function moveSpec($uid, $paid_id, $prof_id, $dir, &$new_paid_id = null) {
		global $DB;
        if($paid_id) {
            $sql = $DB->parse("
              SELECT priority, (SELECT priority FROM spec_paid_choise WHERE user_id = ?i ORDER BY priority DESC LIMIT 1) as last_pr, prof_origin
                FROM spec_paid_choise s
               WHERE s.user_id = ?i
                 AND s.id = ?i
            ", $uid, $uid, $paid_id);
        } else {
            $sql = $DB->parse("
              SELECT priority, (SELECT priority FROM spec_add_choise WHERE user_id = ?i ORDER BY priority DESC LIMIT 1) as last_pr, prof_origin
                FROM spec_add_choise s
               WHERE s.user_id = ?i
                 AND s.prof_id = ?i
            ", $uid, $uid, $prof_id);
        }

        $ret = $DB->row($sql);
        
        $pr = $ret['priority'];
        $max_pr = $ret['last_pr'];
        $prof_orig = $ret['prof_origin'];
        
        if(!$pr) return false;

        //перемещаем внутри блока
        if(!(!$paid_id && $pr == 1 && $dir > 0 ) && !($paid_id && $pr == 1 && $dir < 0 ) && $pr >= 1) {
            
            $table_name = $paid_id ? "spec_paid_choise" : "spec_add_choise";
            $q = $paid_id ? $DB->parse("id = ?i", $paid_id) : $DB->parse("prof_id = ?i", $prof_id);
            $q_paid = $paid_id ? " AND paid_to > NOW() " : "";

            $plus = "+"; $minus = "-";

            if(!$paid_id) {
                $plus = "-";
                $minus = "+";
            }

            if($dir > 0) {
                $sql = $DB->parse("
                        UPDATE {$table_name} SET priority = priority {$minus} 1 WHERE user_id = ?i $q_paid AND priority = {$pr}{$plus}1;
                        UPDATE {$table_name} SET priority = priority {$plus} 1 WHERE user_id = ?i AND $q
                        ", $uid, $uid);
            } else {
                $sql = $DB->parse("
                        UPDATE {$table_name} SET priority = priority {$plus} 1 WHERE user_id = ?i $q_paid AND priority = {$pr}{$minus}1;
                        UPDATE {$table_name} SET priority = priority {$minus} 1 WHERE user_id = ?i AND $q
                        ", $uid, $uid);
            }
        }

        //перемещаем между блоками
        if($pr == 1 && (($paid_id && $dir < 0) || (!$paid_id && $dir > 0))) {
            $sql = "SELECT id, prof_id, prof_origin FROM spec_paid_choise WHERE user_id = ? AND paid_to > NOW() ORDER BY NULLIF(priority,0) DESC LIMIT 1";

            $ret = $DB->row($sql, $uid);
            
            $npaid_id = $ret['id'];
            $nprof_id = $ret['prof_id'];
            $nprof_orig = $ret['prof_origin'];
            
            $nprof_orig = $prof_orig;
            if (!$nprof_orig) $nprof_orig = 'NULL';
            
            if (!$paid_id) {
                $sql = $DB->parse("
                  DELETE FROM spec_add_choise WHERE user_id = ?i AND prof_id = ?i;
                  UPDATE spec_paid_choise SET priority = priority + 1 WHERE user_id = ?i AND paid_to > NOW() AND priority >= {$pr};
                  UPDATE spec_paid_choise SET priority = 1, prof_id = ?i, prof_origin = {$nprof_orig} WHERE id = {$npaid_id};
                ", $uid, $prof_id, $uid, $prof_id);

                if($dir > 0 && $nprof_id) {
                    $sql1 = "
                        SELECT id, prof_id, prof_origin FROM spec_paid_choise
                        WHERE user_id = ?
                            AND paid_to >= now()
                            ORDER BY priority ASC LIMIT 1;
                    ";

                    $ret = $DB->row($sql1, $uid);
                    $m_id = $ret['id'];
                    $m_prof_id = $ret['prof_id'];
                    $m_prof_orig = $ret['prof_origin'];
                    
                    if(!$m_prof_orig) $m_prof_orig = 'NULL';
                    if(!$prof_orig) $prof_orig = 'NULL';

                    $sql = $DB->parse("
                        DELETE FROM spec_add_choise WHERE user_id = ?i AND prof_id = ?i;
                        UPDATE spec_paid_choise SET prof_id = ?i, prof_origin = {$prof_orig} WHERE id = {$m_id};
                        UPDATE spec_add_choise SET priority = priority + 1 WHERE user_id = ?i AND priority >= 1;
                        INSERT INTO spec_add_choise (priority, user_id, prof_id, prof_origin)
                             VALUES(1, ?i, {$m_prof_id}, {$m_prof_orig});
                    ", $uid, $prof_id, $prof_id, $uid, $uid);
                }
                
                $new_paid_id = $npaid_id;
            } else {
                $sql = "SELECT (SELECT COUNT(*) FROM spec_add_choise WHERE user_id = ?) as priority,
                            prof_id, prof_origin FROM spec_add_choise s
                            WHERE s.user_id = ? ORDER BY s.priority ASC LIMIT 1";
                
                $ret = $DB->row($sql, $uid, $uid);
                $m_priority = $ret['priority'];
                $m_prof_id = $ret['prof_id'];
                $m_prof_origin = $ret['prof_origin'];
                
                $sql = $DB->parse("
                  UPDATE spec_paid_choise SET priority = NULL, prof_id = NULL, prof_origin = NULL, is_autopaid = FALSE WHERE id = ?i;
                  UPDATE spec_paid_choise SET priority = priority - 1 WHERE user_id = ?i AND paid_to > NOW() AND priority > {$pr};
                  UPDATE spec_add_choise SET priority = priority + 1 WHERE user_id = ?i AND priority >= {$pr};
                  INSERT INTO spec_add_choise (priority, user_id, prof_id, prof_origin) VALUES(1, ?i, ?i, {$nprof_orig});
                ", $paid_id, $uid, $uid, $uid, $prof_id);

                $free_cnt = (!!is_pro())*PROF_SPEC_ADD;

                if(!$m_prof_origin) $m_prof_origin = 'NULL';
                if(!$prof_orig) $prof_orig = 'NULL';

                if($m_priority >= $free_cnt) {
                    //просто поменяем местами
                    $sql = $DB->parse("
                        DELETE FROM spec_add_choise WHERE user_id = ?i AND prof_id = {$m_prof_id};
                        UPDATE spec_paid_choise SET prof_id = {$m_prof_id}, prof_origin = {$m_prof_origin} WHERE id = ?i;
                        UPDATE spec_add_choise SET priority = priority + 1 WHERE user_id = ?i AND priority >= 1;
                        INSERT INTO spec_add_choise (priority, user_id, prof_id, prof_origin) VALUES(1, ?i, ?i, {$prof_orig});
                    ", $uid, $paid_id, $uid, $uid, $prof_id);

                    $new_paid_id = $m_prof_id;
                }
            }
        }

        return !!$DB->query($sql);
    }

    /**
     * Меняем флаг автопродления для платной професии
     *
     * @param  int $uid Ид пользователя
     * @param  int $paid_id ID професии
     * @return bool новое стстояние автопродления
     */
    function setSpecAutoPay($uid, $paid_id) {
        global $DB;
		return $DB->val("UPDATE spec_paid_choise SET is_autopaid = NOT(is_autopaid) WHERE user_id = ? AND id = ? RETURNING is_autopaid", $uid, $paid_id);
    }

    
    
    /**
     * Дополнительные специальности фрилансера
     *
     * @param integer $user_id ИД Пользователя
     * @return array
     */
    function GetProfsAddSpec($user_id) 
    {
        global $DB;
		
        if (!$user_id) {
            return false;
        }
        
        $sql = "
            SELECT 
                COALESCE(prof_origin, prof_id) AS id
            FROM spec_add_choise sp
            INNER JOIN freelancer AS f ON f.uid = sp.user_id AND f.is_pro = TRUE
            WHERE (sp.user_id = ?i)
            ORDER BY priority DESC
        ";
        
        return $DB->col($sql, $user_id);
    }
	
    
    
	/**
	 * Обновить количество пользователей в профессиях
	 *
	 * @param integer $prof      ИД Профессий
	 * @param integer $count     Количество пользователей
	 * @param integr  $pro_count Количество ПРО пользователей
	 * @return string Сообщение об ошибке если есть
	 */
	function UpdateProfessionCount($prof, $count, $pro_count){
		global $DB;
		$mirrored = professions::GetMirroredProfs($prof);
		$DB->update('professions', array('pcount'=>$count, 'pro_count'=>$pro_count), "id IN (?l)", $mirrored);
		return $DB->error;
	}


   /** 
    * Возвращает номер позиции юзера в каталоге
    * 
    * @param integer $user_id       ИД Пользователя
    * @param integer $user_spec		специализация юзера.
    * @param integer $rating		рейтинг юзера.
    * @param integer $prof_id		рейтинг юзера.
    * @param integer $is_pro	    Пользователь ПРО или не ПРО.
    * @return NULL|multitype
    */
    function GetCatalogPosition($user_id, $user_spec, $rating, $prof_id, $is_pro, $not_exact = false) {
        global $DB;

        
        
        $R = 'rating_get(fu.rating, fu.is_pro, fu.is_verify, fu.is_profi)';
        $pos = NULL;
    
    	$memBuff = new memBuff();
        if(!$prof_id)  // в общем каталоге.
        {
            if(!$not_exact) {
                $pos_table = professions::GetCatalogPositionsTable();
                //такого не должно быть, но на всякий
                if(!$pos_table) return NULL;

                $out = $DB->row("SELECT 0 as prof_id, 'В общем каталоге' as prof_name, pos FROM {$pos_table} WHERE uid = ?", $user_id);

                if($out) {
                    return $out;
                }
            }
            
            $sql = 
            "SELECT 0 as prof_id, 'В общем каталоге' as prof_name, COUNT(fu.uid) + 1 as pos
               FROM fu
             INNER JOIN
               portf_choise pc
                 ON pc.user_id = fu.uid

                AND pc.prof_id = fu.spec_orig
              WHERE fu.is_banned = '0'
                AND (fu.is_pro = true ".($is_pro ? 'AND' : 'OR')." ({$R} > {$rating} OR ({$R} = {$rating} AND fu.uid < {$user_id})))";
            if(!($out = $memBuff->getSql($error, $sql, 300)))
              return NULL;
            return $out[0];
        }

        $or_prof = $prof_id;
        $or_prof = professions::GetProfessionOrigin($prof_id);
       
        
        
        
        $freelancer_binds = new freelancer_binds();
        $date_binded = $freelancer_binds->getBindDate($user_id, $or_prof);
        
        $bind_select = $bind_join = $spec_where = '';
        if ($date_binded) {
            $bind_join = "INNER JOIN freelancer_binds ON freelancer_binds.user_id = fu.uid AND freelancer_binds.prof_id = {$or_prof} AND freelancer_binds.is_spec = TRUE AND freelancer_binds.date_stop > NOW() AND freelancer_binds.date_start > '$date_binded'";
        } else {
            //Ищем всех закрепленных
            $bind_select = "SELECT fu.uid, fb.prof_id as spec FROM fu 
                LEFT JOIN freelancer_binds fb ON fb.user_id = fu.uid AND fb.date_stop > NOW()
                LEFT JOIN spec_add_choise bsa ON bsa.user_id = fu.uid
                WHERE fu.is_banned = '0' 
                    AND fb.prof_id={$or_prof} AND (fu.spec_orig = {$or_prof} OR bsa.prof_id = {$or_prof})
                    AND fu.uid<>{$user_id} AND fu.cat_show = TRUE
                UNION ALL ";
            $spec_where = "AND (fu.is_pro = true ".
                ($is_pro ? 'AND' : 'OR')." ({$R} > {$rating} OR ({$R} = {$rating} AND fu.uid <> {$user_id})".
                ($user_spec==$or_prof ? ") AND fu.spec_orig={$or_prof}" : " OR fu.spec_orig={$or_prof})").
                ")";
        }
        
        $user_where = "
            fu.is_banned = '0' 
            AND fu.uid <> {$user_id} 
            AND fu.cat_show = TRUE 
            AND fu.last_time > now() - '6 months'::interval
        " . $spec_where;
        
        $sql = 
        "SELECT 
                p.id as prof_id,
                p.name as prof_name,
                COUNT(DISTINCT s.uid) + 1 as pos,
                link
           
           FROM professions p
         LEFT JOIN
           (
            {$bind_select}
                
             SELECT uid, spec_orig as spec
               FROM fu
               {$bind_join}
              WHERE ".$user_where."
                
             UNION ALL
             
              SELECT fu.uid, sa.prof_id FROM fu
              INNER JOIN spec_add_choise sa ON sa.user_id = fu.uid
              {$bind_join}
               WHERE fu.is_pro = true AND ".$user_where."
                   
           ) AS s ON s.spec = p.id
    
          WHERE p.id = {$or_prof}
          GROUP BY p.id, p.name, link";
        
        
        $out = $memBuff->getSql(
                $error, 
                $sql, 
                300, 
                true, 
                freelancer::getCatalogMemTags($or_prof, true)
            );
        
        return $out ? $out[0] : null;
    }
    
  
    /**
	 * Редактирование текста страницы для пользователей
	 *
	 * @param integer $id     ИД профессии
	 * @param string  $name   название професии
	 * @param string  $descr  Описание професиии
	 * @param boolean $istext Признак профессии, связанной с написанием текстов
	 * @return string Сообщение об ошибке если есть
	 */
    function EditProfText($id, $descr=NULL){
		global $DB;
		if($descr===NULL) return 0;
    
      	$DB->query("UPDATE professions SET ".($descr!==NULL ? " descr_text='{$descr}'" : '')." WHERE id='$id'");
		return $DB->error;
  	}
  	
  	/**
  	 * Берем ид основных профессий по разделу 
  	 *
  	 * @param array|integer  $group   Ид Группы
  	 * @param boolean        $orig    Взять или нет основные профессии  @see self::GetProfessionOrigin() 
  	 * @return string ИД профессий через запятую
  	 */
  	function getProfIdForGroups($group, $orig = false) {
        global $DB;
		if(is_array($group)) {
            $ids = $DB->col("SELECT id FROM professions WHERE prof_group IN(?l) AND prof_group > 0", $group);
        } else {
            $ids = $DB->col("SELECT id FROM professions WHERE prof_group IN(?) AND prof_group > 0", $group);
        }
        
        if($orig && $ids) return self::GetProfessionOrigin(implode(", ", $ids));
         
        return implode(",", $ids);
  	}
    /**
     * Покупка дополнительной специализации.
     *
     * @param $uid integer   ид. пользователя-покупателя.
     * @param $ammount integer   количество специализаций.
     * @param $tr_id integer   ид. транзакции. Если нет, то создается новая.
     * @param integer $commit             Завершать ли транзакцию после этой операции.
     * @return mixed   0 -- все ок, иначе текст ошибки.
     */
    function buySpec($uid, $ammount, $tr_id = 0, $period = '1 mon', $commit = 1) {
        global $DB;
		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
        if($ammount <= 0) return 0;
        $account = new account;
        $transaction_id = $account->start_transaction($uid, $tr_id);
        $error = $account->Buy($billing_id, $transaction_id, self::OP_PAID_SPEC, $uid, "{$ammount} шт.", "{$ammount} шт.", $ammount, $commit);
        if ($error) return $error;
        do {
            $sql = "INSERT INTO spec_paid_choise (user_id, paid_to) VALUES ({$uid}, now() + '{$period}'::interval) RETURNING id,paid_from,paid_to";
            $res = $DB->query($sql);

            $paid_id = pg_fetch_result($res,0,0);
            $paid_from = pg_fetch_result($res,0,1);
            $paid_to = pg_fetch_result($res,0,2);

            $sql = "INSERT INTO spec_paid_acc_operations (billing_id, paid_from, paid_to)
                    VALUES ($billing_id, '$paid_from'::timestamp, '$paid_to'::timestamp)";
            $rs = $DB->query($sql);

        } while($res && --$ammount>0);
        if(!$res) {
            $account->Del($uid, $billing_id);
            return 'Неизвестная ошибка';
        }
        return 0;
    }

    /**
     * Продляет специализации по ID пользователя
     *
     * @param integer $uid ID пользователя
     * @param boolean $is_autopaid Учитывать только отмеченные для автопродления
     * @param string $period Период продления
     * @param boolean $prolong_only
     * @return string Сообщение об ошибке если есть
     */
    function prolongSpecs($uid, $is_autopaid = false, $period = '1 mon', $prolong_only = false) {
        global $DB;
        return; // #0022795
		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
        $account = new account;
        $specs = self::getPaidSpecs($uid, true, $is_autopaid, $prolong_only);
        if(!$specs) return 'Нет специализаций';
        $transaction_id = $account->start_transaction($uid);
        $billing_id = NULL;
        $sum = 0;
        foreach($specs as $i=>$spec) {
            $DB->start();
			$sql = "UPDATE spec_paid_choise SET paid_to = paid_to + '{$period}'::interval
                    WHERE id = {$spec['id']} AND user_id = {$uid} RETURNING paid_to, (paid_to - '{$period}'::interval) as paid_from";
            if($res = $DB->query($sql)) {
                $paid_to = pg_fetch_result($res,0,0);
                $paid_from = pg_fetch_result($res,0,1);
                $descr = 'Продление "' . $spec['name'] . '" до ' . date('d.m.Y', strtotime($paid_to));
                if($error = $account->Buy($bill_id, $transaction_id, self::OP_PAID_SPEC, $uid, $descr, $descr, 1, 0) ) {
                    $DB->rollback();
                    break;
                }
                if($DB->commit()) {
                    $billing_id = $bill_id;
                    $sum++;

                    $sql = "INSERT INTO spec_paid_acc_operations (billing_id, paid_from, paid_to)
                            VALUES ($bill_id, '$paid_from'::timestamp, '$paid_to'::timestamp)";
                    $rs = $DB->query($sql);
                }
            }
        }
        if($billing_id) {
            $account->commit_transaction($transaction_id, $uid, $billing_id);
            
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/op_codes.php");
            $mail = new smail();
            $ops = new op_codes();
            $price = $ops->getCodes(self::OP_PAID_SPEC);

            if($price) {
                $price = $price[self::OP_PAID_SPEC];
                $mail->PaidSpecsAutopayed($uid, $sum*$price['sum']);
            }
        }
        return $error;
    }

    /**
     * Автопродление платных специализаций
     */
    function autoProlongSpecs($period = '1 mon') {
        global $DB;
		self::deleteExpiredSpecs();
        if($res = $DB->query("SELECT DISTINCT user_id FROM spec_paid_choise WHERE (paid_to BETWEEN now() AND now() + interval '2 hours') AND is_autopaid = true")) {
            while($row = pg_fetch_assoc($res)) {
                self::prolongSpecs($row['user_id'], true, $period, true);
            }
        }
    }

    /**
     * Удаление закончившихся специализаций
     * @return array
     */
    function deleteExpiredSpecs() {
        return !!$GLOBALS['DB']->query("DELETE FROM spec_paid_choise WHERE paid_to < now()");
    }


    
    /**
     * Возвращает платные специализации для указанного пользователя
     *
     * @param integer $uid ID пользователя
     * @param boolean $only_def Выводить записи только с выбранной специализацией
     * @param boolean $is_autopaid Выводить только отмеченные для автопродления
     * @param boolean $prolong_only Выводить только те, у которых заканчивается срок публикации
     * @return array NULL, если ошибка
     */
    function getPaidSpecs($uid, $only_def = false, $is_autopaid = false, $prolong_only = false) {
        global $DB;
		$where = "WHERE s.user_id = {$uid} AND s.paid_to >= now()";
        if($only_def) $where .= ' AND prof_id IS NOT NULL';
        if($is_autopaid) $where .= ' AND is_autopaid = true';
        if($prolong_only) $where .= " AND (paid_to BETWEEN now() AND now() + interval '2 hours')";
        $sql = "
          SELECT s.*, s.id as paid_id, p.name, pg.name as group_name, pg.id as group_id
            FROM spec_paid_choise s
          LEFT JOIN
            professions p
          INNER JOIN
            prof_group pg
              ON pg.id = p.prof_group
              ON p.id = COALESCE(s.prof_origin, s.prof_id)
           {$where}
           ORDER BY NULLIF(s.priority,0)
        ";
        return $DB->rows($sql);
    }

    /**
     * Возвращает дополнительные специализации пользователя
     *
     * @param integer $uid ID пользователя
     * @return array
     */
    function getAddSpecs($uid) {
        global $DB;
		$sql = "
          SELECT s.*, p.name, pg.name as group_name, pg.id as group_id
            FROM spec_add_choise s
          INNER JOIN
            professions p
              ON p.id = COALESCE(s.prof_origin, s.prof_id)
          INNER JOIN
            prof_group pg
              ON pg.id = p.prof_group
           WHERE s.user_id = ?
           ORDER BY s.priority DESC
        ";
        return $DB->rows($sql, $uid);
    }


    /*
     * Рассылка уведомлений пользователям,
     * у которых заканчиваются платные специализации, выбранные для автопродления
     * 
     */
    function PaidSpecsEndingReminder() {
        global $DB;
		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/op_codes.php");
        $mail = new smail();
        $ops = new op_codes();
        $price = $ops->getCodes(self::OP_PAID_SPEC);
        if(!$price) return 0;

        $price = $price[self::OP_PAID_SPEC];

        $sql = "SELECT user_id, COUNT(*) AS cnt FROM spec_paid_choise
                INNER JOIN users u ON u.uid = user_id AND u.is_banned = '0'
                WHERE paid_to > NOW()+'1 day' AND paid_to <= NOW()+'1 day 1 hour' AND is_autopaid = TRUE
                GROUP BY user_id";

        $res = $DB->query($sql);
        while($row = pg_fetch_array($res)) {
            $mail->PaidSpecsEnding($row['user_id'], $row['cnt']*$price['sum']);
        }
        return 0;
    }


    /**
     * Возвращает имя актуальной таблицы с позициями фрилансеров
     * в общем каталоге
     *
     * @param boolean $clear_cache Удалить или нет запись из кеша
     * @return string Имя актуальной таблицы
     */
    function GetCatalogPositionsTable($clear_cache = false) {
        $memBuff = new memBuff();
        
        $sql = "SELECT value FROM settings WHERE module = 'professions' AND variable = 'pos_table' LIMIT 1";

        if($clear_cache) {
            $memBuff->delete(md5($sql));
        }
        
        if(!($pos_table = $memBuff->getSql($error, $sql, 3600)))
            return NULL;
        $pos_table = $pos_table[0]['value'];

        return $pos_table;
    }

    /**
     * Создает снимок общего каталога (позиция/юзер)
     *
     * @return integer 1 если успешно, 0 если ошибка
     */
    function RecalcCatalogPositions() {
        if ($GLOBALS['DB']->mquery('SELECT recalc_catalog_positions()')) {
			return 0;
		}

        professions::GetCatalogPositionsTable(1);
        return 1;
    }

    /**
     * Расчет средних цен размещенных работ фрилансеров. Вызывается в /hourly.php
     *
     */
    function calcAvgPrices() {
        global $DB;
        require_once ($_SERVER['DOCUMENT_ROOT']."/classes/project_exrates.php");
        $project_exrates = new project_exrates();
        $prj_exrates = $project_exrates->GetAll();

        $sql = "SELECT id, name FROM professions p LEFT JOIN mirrored_professions m ON p.id = m.mirror_prof WHERE m.main_prof IS NULL AND p.id>0";
        $professions = $DB->rows($sql);
        foreach($professions as $profession) {
            //  Проекты
            $sql = "SELECT COUNT(
                            CASE
                                WHEN cost_type=0 THEN cost*".($prj_exrates[21])."
                                WHEN cost_type=1 THEN cost*".($prj_exrates[31])."
                                WHEN cost_type=2 THEN cost*".($prj_exrates[41])."
                                WHEN cost_type=3 THEN cost
                                ELSE cost*".($prj_exrates[21])."
                            END
                           ) as cnt 
                    FROM portfolio WHERE prof_id=?i AND (time_value IS NULL OR time_value=0) AND (time_type=0 OR time_type IS NULL) AND cost>0";
            $cnt = $DB->val($sql, $profession['id']);
            $is_manual_cost_prj = 'f';
            if($cnt) {
                $count_portfolio = $DB->val("SELECT count(id) FROM portfolio WHERE prof_id=?i AND (time_value IS NULL OR time_value=0) AND (time_type=0 OR time_type IS NULL) AND cost>0", $profession['id']);
                if($count_portfolio>10) {
                    $sql = "SELECT (
                                    CASE
                                        WHEN cost_type=0 THEN cost*".($prj_exrates[21])."
                                        WHEN cost_type=1 THEN cost*".($prj_exrates[31])."
                                        WHEN cost_type=2 THEN cost*".($prj_exrates[41])."
                                        WHEN cost_type=3 THEN cost
                                        ELSE cost*".($prj_exrates[21])."
                                    END
                                   ) as acost
                            FROM portfolio WHERE prof_id=?i AND (time_value IS NULL OR time_value=0) AND (time_type=0 OR time_type IS NULL) AND cost>0 ORDER BY acost ASC OFFSET ?i LIMIT 1";
    
                    if($cnt%2 == 0) {
                        $n = $cnt/2;
                        $fin_avg_prj1 = $DB->val($sql, $profession['id'], $n-1);
                        $fin_avg_prj2 = $DB->val($sql, $profession['id'], $n);
                        $fin_avg_prj = ceil(($fin_avg_prj1+$fin_avg_prj1)/2);
                    } else {
                        $n = ($cnt-1)/2;
                        $fin_avg_prj = ceil($DB->val($sql, $profession['id'], $n));
                    }
                    $fin_min_prj = $fin_avg_prj-ceil($fin_avg_prj/4);
                    $fin_max_prj = $fin_avg_prj+ceil($fin_avg_prj/4);
                } else {
                    $is_manual_cost_prj = 't';
                }
            } else {
                $is_manual_cost_prj = 't';
            }


            // Время
            $sql = "SELECT COUNT(
                            CASE
                                WHEN p.cost_type=0 THEN p.cost*".($prj_exrates[21])."
                                WHEN p.cost_type=1 THEN p.cost*".($prj_exrates[31])."
                                WHEN p.cost_type=2 THEN p.cost*".($prj_exrates[41])."
                                WHEN p.cost_type=3 THEN p.cost
                                ELSE p.cost*".($prj_exrates[21])."
                            END
                           ) as cnt 
                    FROM (
                            SELECT (cost/time_value) as cost, cost_type FROM portfolio WHERE prof_id={$profession['id']} AND cost>0 AND time_value>0 AND time_type=0
                            UNION ALL
                            SELECT (cost/(8*time_value)) as cost, cost_type FROM portfolio WHERE prof_id={$profession['id']} AND cost>0 AND time_value>0 AND time_type=1
                            UNION ALL
                            SELECT (cost/(22*8*time_value)) as cost, cost_type FROM portfolio WHERE prof_id={$profession['id']} AND cost>0 AND time_value>0 AND time_type=2
                            UNION ALL
                            SELECT (cost*(time_value/60)) as cost, cost_type FROM portfolio WHERE prof_id={$profession['id']} AND cost>0 AND time_value>0 AND time_type=3
                         ) as p
                    ";
            $cnt = $DB->val($sql);
            $is_manual_cost_hour = 'f';
            if($cnt) {
                $sql = "SELECT COUNT(p.id)
                        FROM (
                                SELECT id FROM portfolio WHERE prof_id={$profession['id']} AND cost>0 AND time_value>0 AND time_type=0
                                UNION ALL
                                SELECT id FROM portfolio WHERE prof_id={$profession['id']} AND cost>0 AND time_value>0 AND time_type=1
                                UNION ALL
                                SELECT id FROM portfolio WHERE prof_id={$profession['id']} AND cost>0 AND time_value>0 AND time_type=2
                                UNION ALL
                                SELECT id FROM portfolio WHERE prof_id={$profession['id']} AND cost>0 AND time_value>0 AND time_type=3
                             ) as p
                        ";
                $count_portfolio = $DB->val($sql);
                if($cnt>10) {
                    $sql = "SELECT (
                                    CASE
                                        WHEN p.cost_type=0 THEN p.cost*".($prj_exrates[21])."
                                        WHEN p.cost_type=1 THEN p.cost*".($prj_exrates[31])."
                                        WHEN p.cost_type=2 THEN p.cost*".($prj_exrates[41])."
                                        WHEN p.cost_type=3 THEN p.cost
                                        ELSE p.cost*".($prj_exrates[21])."
                                    END
                                   ) as acost 
                            FROM (
                                    SELECT (cost/time_value) as cost, cost_type FROM portfolio WHERE prof_id={$profession['id']} AND cost>0 AND time_value>0 AND time_type=0
                                    UNION ALL
                                    SELECT (cost/(8*time_value)) as cost, cost_type FROM portfolio WHERE prof_id={$profession['id']} AND cost>0 AND time_value>0 AND time_type=1
                                    UNION ALL
                                    SELECT (cost/(22*8*time_value)) as cost, cost_type FROM portfolio WHERE prof_id={$profession['id']} AND cost>0 AND time_value>0 AND time_type=2
                                    UNION ALL
                                    SELECT (cost*(time_value/60)) as cost, cost_type FROM portfolio WHERE prof_id={$profession['id']} AND cost>0 AND time_value>0 AND time_type=3
                                 ) as p
                            ORDER BY acost ASC OFFSET ?i LIMIT 1;
                        ";
                    if($cnt%2 == 0) {
                        $n = $cnt/2;
                        $fin_avg_hour1 = $DB->val($sql, $n-1);
                        $fin_avg_hour2 = $DB->val($sql, $n);
                        $fin_avg_hour = ceil(($fin_avg_hour1+$fin_avg_hour1)/2);
                    } else {
                        $n = ($cnt-1)/2;
                        $fin_avg_hour = ceil($DB->val($sql, $n));
                    }
                    $fin_min_hour = $fin_avg_hour-ceil($fin_avg_hour/4);
                    $fin_max_hour = $fin_avg_hour+ceil($fin_avg_hour/4);
                } else {
                    $is_manual_cost_hour = 't';
                }
            } else {
                $is_manual_cost_hour = 't';
            }

            $mirrors = $DB->col("SELECT mirror_prof FROM mirrored_professions WHERE main_prof = ?i", $profession['id']);
            
            if($is_manual_cost_hour=='t') {
                $sql = "UPDATE professions SET is_manual_cost_hour='t' WHERE id IN (?l)";
                $DB->query($sql, array($profession['id']));
                if ($mirrors) {
                    $DB->query($sql, $mirrors);
                }
            } else {
                $sql = "UPDATE professions SET is_manual_cost_hour='f', min_cost_hour=?i, max_cost_hour=?, avg_cost_hour=?i WHERE id IN (?l)";
                $DB->query($sql, $fin_min_hour, $fin_max_hour, $fin_avg_hour, array($profession['id']));
                if ($mirrors) {
                    $DB->query($sql, $fin_min_hour, $fin_max_hour, $fin_avg_hour, $mirrors);
                }
            }
            
            if($is_manual_cost_prj=='t') {
                $sql = "UPDATE professions SET is_manual_cost_prj='t' WHERE id IN (?l)";
                $DB->query($sql, array($profession['id']));
                if ($mirrors) {
                    $DB->query($sql, $mirrors);
                }
            } else {
                $sql = "UPDATE professions SET is_manual_cost_prj='f', min_cost_prj=?i, max_cost_prj=?, avg_cost_prj=?i WHERE id IN (?l)";
                $DB->query($sql, $fin_min_prj, $fin_max_prj, $fin_avg_prj, array($profession['id']));
                if ($mirrors) {
                    $DB->query($sql, $fin_min_prj, $fin_max_prj, $fin_avg_prj, $mirrors);
                }
            }
            
        }
    }

    /**
     * Обновление средних цен размещенных работ фрилансеров
     *
     * @param   array   $data   данные с ценами для профессий
     */
    function updateAvgPrices($data) {
        global $DB;
        $DB->clear();

        if($data && is_array($data)) {
            $sql = '';
            foreach($data as $key=>$value) {
                list($type, $prof_id) = preg_split("/_/", $key);
                $prof_id = intval($prof_id);
                $value = round($value);
                switch($type) {
                    case 'hmin':
                        $DB->hold()->query("UPDATE professions SET min_cost_hour=? WHERE id=?", $value, $prof_id);
                        break;
                    case 'havg':
                        $DB->hold()->query("UPDATE professions SET avg_cost_hour=? WHERE id=?", $value, $prof_id);
                        break;
                    case 'hmax':
                        $DB->hold()->query("UPDATE professions SET max_cost_hour=? WHERE id=?", $value, $prof_id);
                        break;
                    case 'pmin':
                        $DB->hold()->query("UPDATE professions SET min_cost_prj=? WHERE id=?", $value, $prof_id);
                        break;
                    case 'pavg':
                        $DB->hold()->query("UPDATE professions SET avg_cost_prj=? WHERE id=?", $value, $prof_id);
                        break;
                    case 'pmax':
                        $DB->hold()->query("UPDATE professions SET max_cost_prj=? WHERE id=?", $value, $prof_id);
                        break;
                }
            }
            
            $DB->query();
        }
     }
     
     
    /**
     * Выборка всех профессий и групп к которым они относятся.
     * пустые группы не возвращаются
     * @return array $rows 
     * */
    function GetProfessionsAndGroup($order_by = 'gid')
    {
        global $DB;
        
        $cmd = "SELECT 
                    g.id AS gid, 
                    g.name AS gname,
                    COALESCE(g.cnt,0) AS gcnt,
                    p.id, 
                    p.name AS name,
                    COALESCE(p.pcount,0) AS pcount
                FROM prof_group AS g 
                INNER JOIN professions AS p ON p.prof_group = g.id
				ORDER BY {$order_by}";
        
        $rows = $DB->cache(3600)->rows($cmd);
        return $rows;
     }


     /**
      * @param array $ids - массив идентификаторов профессий 
      * Возвращает названия профессий по их id
      * */
     function GetProfessionsTitles($ids) {
        $ids = join(",", $ids);
        $DB = new DB('master');
        $cmd = "SELECT id, name FROM professions WHERE id IN ($ids)";
        $rows = $DB->cache(3600)->rows($cmd);
        return $rows;
     }
     
     public static function getLastModifiedSpec($uid) {
         global $DB;
         if(!$uid) $uid = $_SESSION['uid'];
         $spec = $DB->row("SELECT * FROM spec_modified WHERE uid = ?i ORDER BY id DESC LIMIT 1", $uid);
         if(!$spec) return 0;
         if($spec['new_spec'] == 0 || $spec['old_spec'] == $spec['new_spec']) {// Если нулевая меняй сколько хочешь
             $spec['next_date_update'] = strtotime($spec['modified_date']);
             $spec['days']             = 0;
             return $spec;
         }
         $spec['next_date_update'] = strtotime($spec['modified_date'] . ' + 30 days');
         $spec['days']             = round( ( $spec['next_date_update'] - time() ) / 86400 );
         return $spec;
     }
     
     public static function setLastModifiedSpec($uid = null, $spec = 0) {
         global $DB;
         if(!$uid) $uid = $_SESSION['uid'];
         
         $is_modified = self::getLastModifiedSpec($uid);
         if($is_modified) {
             $update = array(
                'modified_date' => 'NOW()',
                 'new_spec'     => $spec,
                 'old_spec'     => $is_modified['new_spec']
             );
             $DB->update("spec_modified", $update, 'uid = ?i', $uid);
         } else {
             $insert = array(
                 'modified_date' => 'NOW()', 
                 'new_spec'      => $spec,
                 'old_spec'      => $spec,
                 'uid'           => $uid
             );
             
             $DB->insert("spec_modified", $insert);
         }
     }
	 
	public static function GetGroupIdByProf($id){
		return $GLOBALS['DB']->val("SELECT prof_group FROM professions WHERE id = ?", $id);
	}
    
    public static function GetGroupIdsByProfs($ids){
		return $GLOBALS['DB']->col("SELECT prof_group FROM professions WHERE id IN (?l)", $ids);
	}
    
    
    
    public static function getGroupAndProf($group_id, $prof_id)
    {
        global $DB;
        return $DB->row('
            SELECT 
                pg.id AS group_id,
                ps.id AS prof_id
            FROM prof_group AS pg
            LEFT JOIN professions AS ps ON (ps.prof_group = pg.id) AND (ps.id = ?i)
            WHERE pg.id = ?i 
            LIMIT 1
        ', $prof_id, $group_id);
    }
    
    public function getProfGroupIds()
    {
        global $DB;
        return $DB->col('SELECT id FROM prof_group WHERE id > 0');
    }
    
    public function getOriginProfsIds()
    {
        global $DB;
        return $DB->col('SELECT DISTINCT COALESCE(m.main_prof,p.id)
           FROM professions p
         LEFT JOIN
           mirrored_professions m
             ON m.mirror_prof = p.id');
    }
    
    
    
    public static function isExistProfId($id, $group_id)
    {
        global $DB;
        return $DB->val('SELECT 1 FROM professions 
                         WHERE id = ?i AND prof_group = ?i 
                         LIMIT 1', $id, $group_id);
    }
    
}
