<?php
/* 
 * 
 * Данный файл является частью проекта Веб Мессенджер.
 * 
 * Все права защищены. (c) 2005-2009 ООО "ТОП".
 * Данное программное обеспечение и все сопутствующие материалы
 * предоставляются на условиях лицензии, доступной по адресу
 * http://webim.ru/license.html
 * 
 */
?>
<?php

require_once (dirname(__FILE__) . '/class.basemapper.php');

class ThreadMapper extends BaseMapper {
  public function __construct(DBDriver $db, $model_name) {
    parent::__construct($db, $model_name, array(
      "created", "modified", "lastpingvisitor", "lastpingagent"
    ));  
  }

  public function getById($id) {
    $result = parent::getById($id, '
    				t.*,
                    WM_UNIX_TIMESTAMP("created") as "tscreated",
                    WM_UNIX_TIMESTAMP("lastpingvisitor") as "lpvisitor",
                    WM_UNIX_TIMESTAMP("lastpingagent") as "lpoperator",
                    WM_UNIX_TIMESTAMP(SYSDATE) as "current"
                    '
    );
    
    
      if($result) {
	      //Inject rate if needed
	      $crm = MapperFactory::getMapper("Rate");
	      $rate = $crm->getByThreadidAndOperatorid($result['threadid'], $result['operatorid']);
	      if($rate) {

	      	$result['ratedoperatorid'] = $rate['operatorid'];
	      	$result['rate'] = $rate['rate'];
	      } else {
	      	$result['ratedoperatorid'] = null;
	      	$result['rate'] = null;
	      }
      }
    
      
    return $result; 
  }
  
  public function getOpenThreadsForVisitor($visitorid) {
    $sql = 'SELECT
                    t.*, v.*,
                    WM_UNIX_TIMESTAMP(t."created") as "tscreated",
                    WM_UNIX_TIMESTAMP("lastpingvisitor") as "lpvisitor",
                    WM_UNIX_TIMESTAMP("lastpingagent") as "lpoperator",
                    WM_UNIX_TIMESTAMP(SYSDATE) as "current"
                FROM
                    "{thread}" t
                INNER JOIN
                    "{visitsession}" v
                ON
                    t."visitsessionid" = v."visitsessionid"
                WHERE
                    "visitorid" = :visitorid AND "state" <> :state
                ORDER BY t."threadid" DESC';
	   try {
	     $this->db->Query($sql, array("visitorid" => $visitorid, "state" => STATE_CLOSED));
	     return $this->db->getArrayOfRows();
	   } catch (Exception $e) {

	     return array();
	   }
  }
  
  public function countOpenThreadsForIP($ip) {
    return count($this->getOpenThreadsForIP($ip));
  }
  
  public function getOpenThreadsForIP($ip) {
    $sql = 'SELECT
                    t.*, v.*,
                    WM_UNIX_TIMESTAMP(t."created") as "tscreated",
                    WM_UNIX_TIMESTAMP("lastpingvisitor") as "lpvisitor",
                    WM_UNIX_TIMESTAMP("lastpingagent") as "lpoperator",
                    WM_UNIX_TIMESTAMP(SYSDATE) as "current"
                FROM
                    "{thread}" t
                INNER JOIN
                    "{visitsession}" v
                ON
                    t."visitsessionid" = v."visitsessionid"
                WHERE
                    v."ip" = :ip AND "state" <> :state
                ORDER BY t."threadid" DESC';
	   try {
	     $this->db->Query($sql, array("ip" => $ip, "state" => STATE_CLOSED));
	     return $this->db->getArrayOfRows();
	   } catch (Exception $e) {

	     return array();
	   }
  }
 
  
  
  public function getActiveThreadForVisitor($visitorid) {
    return array_shift($this->getOpenThreadsForVisitor($visitorid));
  }
  
  public function incrementVisitorMessageCount($threadid) {
    $sql = ' 
    		UPDATE "{thread}"
    		SET "visitormessagecount" = "visitormessagecount" + 1
    		WHERE "threadid"=:id
    	   ';
    try {
        $this->db->Query($sql, array("id" => $threadid));
        return true;
    } catch (Exception $e) {

      return false;
    }
  }
  
  public function getNonEmptyThreadsCountByVisitorId($visitorid) {
     $sql = ' SELECT 
     				COUNT(t."threadid") as "total" 
              FROM "{thread}" t 
              LEFT JOIN "{visitsession}" v 
              ON t."visitsessionid" = v."visitsessionid"
              WHERE v."visitorid" = :id AND t."visitormessagecount" > 0
            ';
     try {
       $this->db->Query($sql, array("id" => $visitorid));
       $this->db->nextRecord();
       $row = $this->db->getRow();

       return $row['total']; 
     } catch (Exception $e) {

       return null;
     }
  }
 
  public function getByVisitSessionId($visitsessionid) {
    $sql = ' SELECT
    			  WM_UNIX_TIMESTAMP(t."created") as "created",
                  WM_UNIX_TIMESTAMP(t."modified") as "modified",
                  WM_UNIX_TIMESTAMP(t."modified") - WM_UNIX_TIMESTAMP(t."created") as "diff",
                  t."threadid",
                  v."ip" as "remote",
                  t."operatorfullname",
                  v."visitorname" as "visitorname"
             FROM "{thread}" t 
             LEFT JOIN "{visitsession}" v
             ON (t."visitsessionid" = v."visitsessionid")
             WHERE t."visitsessionid" = :visitsessionid
             ORDER BY t."created" DESC 
    ';
    try {
      $this->db->Query($sql, array("visitsessionid" => $visitsessionid));
      return $this->db->getArrayOfRows();
    } catch (Exception $e) {

      return array();
    }
  }
  
  public function countActiveThreads() {
    $result = $this->makeSearch('("state" <> :state)', array("state" => STATE_CLOSED), 'COUNT(*) "total"');
    $result = array_shift($result);

    return $result['total'];
  }
  
  public function getListThreads($currentoperatorid, $q, $show_empty = true, $checkDepartmentsAccess = true, $start_date = null, $end_date = null, $operatorid = null, $offset = null, $limit = null, $departmentid = null, $locale = null, $rate = null) {
    $departmentsExist = count(MapperFactory::getMapper("Department")->enumDepartments(Resources::getCurrentLocale())); // TODO probably not the best place
    
    $query_params = array();
    $sql = '
                    SELECT  
                    	WM_UNIX_TIMESTAMP(t."created") "created",
                    	WM_UNIX_TIMESTAMP(t."modified") "modified",
                    	t."threadid", 
                    	t."operatorfullname", 
                    	t."visitormessagecount" as "size", 
                    	v."ip" as "remote", 
                    	v."remotehost", 
                    	v."visitorname" 
                    FROM "{thread}" t  
                    LEFT JOIN "{visitsession}" v 
                    ON v."visitsessionid" = t."visitsessionid" 
                    WHERE
                    1=1';
    if (!empty($q)) {
      $query_params['query'] = "%%$q%%";
      $sql .= ' AND (t."threadid" IN (
      					SELECT "threadid" 
      					FROM "{message}" m
      					WHERE m."sendername" LIKE :query
                		OR m."message" LIKE :query
                	)
                    OR v."visitorid" LIKE :query 
                    OR v."ip" LIKE :query
                    OR v."remotehost" LIKE :query
                    OR t."operatorfullname" LIKE :query
                 )';
    }
    
    if (!empty($rate)) {
      $sign = $rate == 'positive' ? '>' : '<';
      $sql .= ' AND EXISTS(SELECT * FROM "{rate}" r WHERE r."threadid"=t."threadid" AND r."rate" ' . $sign . ' 0 AND r."deldate" IS NULL)';
    }
    
    if ($checkDepartmentsAccess) {
      $sql .= ' AND (t."departmentid" IS NULL OR EXISTS(SELECT * FROM "{operatordepartment}" od WHERE od."operatorid"=:currentoperatorid AND od."departmentid"=t."departmentid"))';
      $query_params['currentoperatorid'] = $currentoperatorid;
    }
    
    if (!$show_empty) {
      $sql .= ' AND t."visitormessagecount" > 0 ';
    }
    
    if ($start_date !== null) { 
      $query_params['start_date'] = $start_date;
      $sql .= ' AND WM_UNIX_TIMESTAMP(t."created") >= :start_date';
    }
    
    if ($end_date !== null) {
      $query_params['end_date'] = $end_date;
      $sql .= ' AND WM_UNIX_TIMESTAMP(t."created") < :end_date';
    }
    
    if ($operatorid !== null) {
      $query_params['operatorid'] = $operatorid;
      $sql .= ' AND (:operatorid IS NULL OR t."operatorid"=:operatorid)';
    }

    if (!empty($departmentid)) {
      $query_params['departmentid'] = $departmentid;
      $sql .= ' AND t."departmentid" = :departmentid ';
    }
    
    if (!empty($locale)) {
      $query_params['locale'] = $locale;
      $sql .= ' AND t."locale" = :locale ';
    }
    
    if($limit !== null && $offset !== null) {
      $query_params['limit'] = $limit;
      $query_params['offset'] = $offset;
      $sql .= " AND rownum BETWEEN :offset AND :limit";  
    }
    
    $sql .= ' ORDER BY t."created" DESC';
    
    
    

    
    try {
      $this->db->query($sql, $query_params);
      $result = $this->db->getArrayOfRows();
    } catch(Exception $e) {

      return array();
    }
    

    foreach ($result as $k => $v) {     
    
      $geodata = GeoIPLookup::getGeoDataByIP($v['remote']);

      //for testing purpose
      //$geodata = GeoIPLookup::getGeoDataByIP('89.113.218.99');
      
      if($geodata == NULL) {
        $geodata = array(
          'city' => null, 'country' => null, 'lat' => null, 'lng' => null
        );
      }
      
      $result[$k] = array_merge($v, $geodata);

      
      
      $result[$k]['created'] = date(getDateTimeFormat(), $v['created']);
      $result[$k]['modified'] = date(getDateTimeFormat(), $v['modified']);
      $result[$k]['diff'] = webim_date_diff($v['modified'] - $v['created']);
    }
    
    
    return $result;
  }

  public function enumByDate($start, $end) {
    return $this->makeSearch(
    	'WM_UNIX_TIMESTAMP("created") >= :start and WM_UNIX_TIMESTAMP("created") < :end',
        array("start" => $start, "end" => $end)
    );
  }
  
  public function getNextRevision() {
    $sql = 'SELECT "{revision_seq}".nextval "rev" FROM dual';
    try {
      $this->db->Query($sql);
      $this->db->nextRecord();
      $row = $this->db->getRow();
      return $row['rev'];
    } catch (Exception $e) {

      return null;
    }
  }
  
  public function getNextToken() {
    return rand(99999, 99999999);
  }
  
  public function getPendingThreads($since, $includeClosed, $operatorid, $shouldcheckdepartments, $locales) {


    $params = array();
    
    if( $shouldcheckdepartments) {
      $departmentsSql = ' AND (t."departmentid" IS NULL OR t."departmentid" IN 
            (SELECT "departmentid" FROM "{operatordepartment}" WHERE "operatorid"=:operatorid))';
      $params = array('operatorid'=>$operatorid);       
    } else {
      $departmentsSql = '';
    }
    
    if (!empty($locales)) {
      $placeholders = array();
      
      $i = 0;
      foreach ($locales as $l) {
        $ph = "l".$i;
        $placeholders[] = ':' . $ph;
        $i++;
        $params[$ph] = $l;
      }
      
      $localeSql = ' AND (t."locale" IN (' . join(',', $placeholders) . ')) ';
    } else {
      $localeSql = '';
    }
            
    $sql = 'SELECT t.*, WM_UNIX_TIMESTAMP(t."created") "created" FROM "{' . $this->getTableName() . '}" t WHERE 1=1 '
            . $departmentsSql
            . $localeSql
            . ' AND "revision" > :since AND (' . ($includeClosed ? '1=1' : '1=0') . ' OR "state" <> :closed)';
            
    $params = array_merge($params, array(
        'since'=>$since, 
        'closed'=>STATE_CLOSED, 
    ));

    try {
      $this->db->Query($sql, $params);
      return $this->db->getArrayOfRows();
    } catch(Exception $e) {

      return array();
    }
  }

  public function enumOpenWithTimeout($timeout) {
    return $this->makeSearch(
    	'"state" <> :state
    	AND WM_UNIX_TIMESTAMP(SYSDATE) - WM_UNIX_TIMESTAMP("modified") > :timeout',
        array('state' => STATE_CLOSED, 'timeout' => $timeout)
    );
  }
  
  public function getReportByDate($start, $end, $departmentid = null, $locale = null) {
    $afrom = "";
    $awhere = "";
    $params = array("kind_agent" => KIND_AGENT, "kind_user" => KIND_USER, "p_start" => $start, "p_end" => $end);
    
    if($departmentid !== null || $locale !== null) {
       $afrom = '
       			LEFT JOIN
       				"{thread}" t
       			ON
       				t."threadid" = m."threadid" 
       		 '; 
       if($departmentid !== null) {
           $awhere .= 'AND t."departmentid"=:departmentid ';
           $params["departmentid"] = $departmentid;
       }
       
       if($locale !== null) {
         $awhere .= 'AND t."locale"=:locale';
         $params["locale"] = $locale;
       }
    }
     $sql ='
            SELECT
                WM_UNIX_TIMESTAMP(trunc(m."created")) "date",
                COUNT(distinct m."threadid") "threads",
                SUM( 
                	(
                		SELECT COUNT(*) 
                		FROM "{message}" mi
                		WHERE
                		mi."messageid" = m."messageid"
                		AND mi."kind" = :kind_agent
                	)
                ) "agents",
                SUM(
                	( 
                		SELECT COUNT(*) 
                		FROM "{message}" mi
                		WHERE
                		mi."messageid" = m."messageid"
                		AND mi."kind" = :kind_user
                	)
                ) "visitors"
                
            FROM
                "{message}" m 
            ' . $afrom . '
            WHERE
                WM_UNIX_TIMESTAMP(m."created") >= :p_start
            AND
                WM_UNIX_TIMESTAMP(m."created") < :p_end
            ' . $awhere . '
            GROUP BY
				trunc(m."created")
            ORDER BY
                trunc(m."created") DESC
        ';

    try {
      $this->db->query($sql, $params);
      $result = $this->db->getArrayOfRows();
    } catch(Exception $e) {

      return array();
    }
    
    foreach($result as $k => $v) {
      $result[$k]['date'] = date(getDateFormat(), $v['date']);
    }
    
    return $result;
  }

  public function getReportTotalByDate($start, $end, $departmentid = null, $locale = null) {
    $afrom = "";
    $awhere = "";
    
    $params = array("kind_agent" => KIND_AGENT, "kind_user" => KIND_USER, "p_start" => $start, "p_end" => $end);
    
    if($departmentid !== null || $locale !== null) {
       $afrom = '
       			LEFT JOIN
       				"{thread}" t
       			ON
       				t."threadid" = m."threadid" 
       		 '; 
       if($departmentid !== null) {
           $awhere .= 'AND t."departmentid"=:departmentid ';
           $params["departmentid"] = $departmentid;
       }
       
       if($locale !== null) {
         $awhere .= 'AND t."locale"=:locale';
         $params["locale"] = $locale;
       }
    }
    
    $sql = '
            SELECT
                COUNT(distinct m."threadid") as "threads",
                SUM( 
                	(
                		SELECT COUNT(*) 
                		FROM "{message}" mi
                		WHERE
                		mi."messageid" = m."messageid"
                		AND mi."kind" = :kind_agent
                	)
                ) "agents",
                SUM( 
                	(
                		SELECT COUNT(*) 
                		FROM "{message}" mi
                		WHERE
                		mi."messageid" = m."messageid"
                		AND mi."kind" = :kind_user
                	)
                ) "visitors"
            FROM
                "{message}" m
            ' . $afrom . '
            WHERE
                WM_UNIX_TIMESTAMP(m."created") >= :p_start
            AND
                WM_UNIX_TIMESTAMP(m."created") < :p_end
           ' . $awhere;
   try {
      $this->db->query($sql, $params); 
      $this->db->nextRecord();
      return $this->db->getRow();
    } catch(Exception $e) {

      return null;
    }
    
  }
  
  public function enumThreadLocales() {
    try {
      $this->db->query('SELECT DISTINCT("locale") "locale" FROM "{' . $this->getTableName() . '}" ORDER BY "locale"');
      return $this->db->getArrayOfRows();
    } catch(Exception $e) {

      return array();
    }
  }
  
  public function removeHistory($threadid) {
    try {
        $this->db->query('DELETE FROM "{'.$this->getTableName().'}" WHERE "threadid"=:threadid', array("threadid" => $threadid));
    } catch(Exception $e) {

    }
  }

  public function operatorHasActiveThreads($operatorid) {
    $result = $this->makeSearch(
    	'"operatorid" = :operatorid AND "state" <> :state',
        array("operatorid" => $operatorid, "state" => STATE_CLOSED),
        'COUNT("threadid") "cnt"',
        1
    );
    
    $result = array_shift($result);
    
    return $result['cnt'] > 0;
  }

  public function getLocaleByVisitSessionId($visitsessionid) {
    $result = $this->makeSearch('"visitsessionid" = :operatorid', 
                                array("operatorid" => $visitsessionid), "locale", 1);

    if(empty($result)) {
      return null;
    }

    $result = array_shift($result);
    return $result['locale'];
  }

}

?>