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
    $result = parent::getById($id, "
    				*,
                    unix_timestamp(created) as tscreated,
                    unix_timestamp(lastpingvisitor) as lpvisitor,
                    unix_timestamp(lastpingagent) as lpoperator,
                    unix_timestamp(CURRENT_TIMESTAMP) as current"
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
    $sql = "SELECT
                    *,
                    unix_timestamp(t.created) as tscreated,
                    unix_timestamp(lastpingvisitor) as lpvisitor,
                    unix_timestamp(lastpingagent) as lpoperator,
                    unix_timestamp(CURRENT_TIMESTAMP) as current
                FROM
                    {thread} as t
                INNER JOIN
                    {visitsession} as v
                ON
                    t.visitsessionid = v.visitsessionid
                WHERE
                    visitorid = ? AND state <> ?
                ORDER BY threadid DESC";
	   try {
	     $this->db->Query($sql, array($visitorid, STATE_CLOSED));
	     return $this->db->getArrayOfRows();
	   } catch (Exception $e) {

	     return array();
	   }
  }
  
  public function countOpenThreadsForIP($ip) {
    return count($this->getOpenThreadsForIP($ip));
  }
  
  public function getOpenThreadsForIP($ip) {
    $sql = "SELECT
                    *,
                    unix_timestamp(t.created) as tscreated,
                    unix_timestamp(lastpingvisitor) as lpvisitor,
                    unix_timestamp(lastpingagent) as lpoperator,
                    unix_timestamp(CURRENT_TIMESTAMP) as current
                FROM
                    {thread} as t
                INNER JOIN
                    {visitsession} as v
                ON
                    t.visitsessionid = v.visitsessionid
                WHERE
                    v.ip = ? AND state <> ?
                ORDER BY threadid DESC";
	   try {
	     $this->db->Query($sql, array($ip, STATE_CLOSED));
	     return $this->db->getArrayOfRows();
	   } catch (Exception $e) {

	     return array();
	   }
  }
 
  
  
  public function getActiveThreadForVisitor($visitorid) {
    return array_shift($this->getOpenThreadsForVisitor($visitorid));
  }
  
  public function incrementVisitorMessageCount($threadid) {
    $sql = " UPDATE {thread}
    		 SET visitormessagecount = visitormessagecount + 1
    		 WHERE threadid=?";
    try {
        $this->db->Query($sql, $threadid);
        return true;
    } catch (Exception $e) {

      return false;
    }
  }
  
  public function getNonEmptyThreadsCountByVisitorId($visitorid) {
     $sql = " SELECT 
     				COUNT(t.threadid) as total 
              FROM {thread} as t 
              LEFT JOIN {visitsession} as v 
              ON t.visitsessionid=v.visitsessionid
              WHERE v.visitorid=? AND t.visitormessagecount > 0
            ";
     try {
       $this->db->Query($sql, $visitorid);
       $this->db->nextRecord();
       $row = $this->db->getRow();

       return $row['total']; 
     } catch (Exception $e) {

       return null;
     }
  }
 
  public function getByVisitSessionId($visitsessionid) {
    $sql = " SELECT
    			  unix_timestamp(t.created) as created,
                  unix_timestamp(t.modified) as modified,
                  unix_timestamp(t.modified) - unix_timestamp(t.created) as diff,
                  t.threadid,
                  v.ip as remote,
                  t.operatorfullname,
                  v.visitorname as visitorname
             FROM {thread} as t 
             LEFT JOIN {visitsession} as v
             ON (t.visitsessionid = v.visitsessionid)
             WHERE t.visitsessionid = ?
             ORDER BY t.created DESC 
    ";
    try {
      $this->db->Query($sql, $visitsessionid);
      return $this->db->getArrayOfRows();
    } catch (Exception $e) {

      return array();
    }
  }

  public function countActiveThreads() {
    $result = $this->makeSearch("(state <> ?)", array(STATE_CLOSED), "COUNT(*) as total");
    $result = array_shift($result);

    return $result['total'];
  }

  public function getListThreads( $currentoperatorid, $q, $show_empty = true, $checkDepartmentsAccess = true, $nLimit = 15, $nOffset = 0, $start_date = null, $end_date = null, $operatorid = null, $departmentid = null, $locale = null, $rate = null, $offline = null ) {
    $departmentsExist = count(MapperFactory::getMapper("Department")->enumDepartments(Resources::getCurrentLocale())); // TODO probably not the best place
    $query_params = array();
    
    $sWhere = '';
    
    $sql = "SELECT  
            unix_timestamp(t.created) as created, 
            unix_timestamp(t.modified) as modified, 
            t.threadid, 
            t.operatorfullname, 
            t.visitormessagecount as size, 
            v.ip as remote, 
            v.remotehost, 
            v.visitorname 
        FROM {thread} as t  
        LEFT JOIN {visitsession} as v ON v.visitsessionid = t.visitsessionid 
        WHERE 1";
    
    if (!empty($q)) {
      $query_params['query'] = "%%$q%%";
      $sWhere .= " AND (t.threadid IN (
      					SELECT threadid 
      					FROM {message} as m
      					WHERE m.sendername LIKE :query
                		OR m.message LIKE :query
                	)
                    OR v.visitorid LIKE :query 
                    OR v.ip LIKE :query
                    OR v.remotehost LIKE :query
                    OR t.operatorfullname LIKE :query
                 )";
    }
    
    if (!empty($rate)) {
      $sign = $rate == 'positive' ? '>' : '<';
      $sWhere .= " AND EXISTS (SELECT * FROM {rate} r WHERE r.threadid=t.threadid AND r.rate $sign 0 AND r.deldate IS NULL) ";
    }
    
    if ($checkDepartmentsAccess) {
      $sWhere .= " AND (t.departmentid IS NULL OR EXISTS(SELECT * FROM {operatordepartment} od WHERE od.operatorid=:currentoperatorid AND od.departmentid=t.departmentid)) ";
      $query_params['currentoperatorid'] = $currentoperatorid;
    }
    
    if (!$show_empty) {
      $sWhere .= " AND t.visitormessagecount > 0 ";
    }
    
    if ($start_date !== null) { 
      $query_params['start_date'] = $start_date;
      $sWhere .= " AND unix_timestamp(t.created) >= :start_date";
    }
    
    if ($end_date !== null) {
      $query_params['end_date'] = $end_date;
      $sWhere .= " AND unix_timestamp(t.created) < :end_date";
    }
    
    if ($operatorid !== null) {
      $query_params['operatorid'] = $operatorid;
      $sWhere .= " AND (:operatorid IS NULL OR t.operatorid=:operatorid)";
    }
    
    if (!empty($departmentid)) {
      $query_params['departmentid'] = $departmentid;
      $sWhere .= " AND t.departmentid = :departmentid ";
    }
    
    if (!empty($locale)) {
      $query_params['locale'] = $locale;
      $sWhere .= " AND t.locale = :locale ";
    }
    
  	if ($offline !== null) {
      $query_params['offline'] = $offline;
      $sWhere .= " AND t.offline=:offline";
    }
    
    $sql .= $sWhere . " ORDER BY t.created DESC LIMIT $nOffset, $nLimit";
        
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
  
  public function getListThreadsCount( $currentoperatorid, $q, $show_empty = true, $checkDepartmentsAccess = true, $start_date = null, $end_date = null, $operatorid = null, $departmentid = null, $locale = null, $rate = null, $offline = null ) {
    $departmentsExist = count(MapperFactory::getMapper("Department")->enumDepartments(Resources::getCurrentLocale())); // TODO probably not the best place
    $query_params = array();
    
    $sWhere    = '';
    $nTotal    = 0;
    $sCountSql = 'SELECT COUNT(*) AS cnt FROM {thread} as t 
        LEFT JOIN {visitsession} as v ON v.visitsessionid = t.visitsessionid 
        WHERE 1';
    
    if (!empty($q)) {
      $query_params['query'] = "%%$q%%";
      $sWhere .= " AND (t.threadid IN (
      					SELECT threadid 
      					FROM {message} as m
      					WHERE m.sendername LIKE :query
                		OR m.message LIKE :query
                	)
                    OR v.visitorid LIKE :query 
                    OR v.ip LIKE :query
                    OR v.remotehost LIKE :query
                    OR t.operatorfullname LIKE :query
                 )";
    }
    
    if (!empty($rate)) {
      $sign = $rate == 'positive' ? '>' : '<';
      $sWhere .= " AND EXISTS (SELECT * FROM {rate} r WHERE r.threadid=t.threadid AND r.rate $sign 0 AND r.deldate IS NULL) ";
    }
    
    if ($checkDepartmentsAccess) {
      $sWhere .= " AND (t.departmentid IS NULL OR EXISTS(SELECT * FROM {operatordepartment} od WHERE od.operatorid=:currentoperatorid AND od.departmentid=t.departmentid)) ";
      $query_params['currentoperatorid'] = $currentoperatorid;
    }
    
    if (!$show_empty) {
      $sWhere .= " AND t.visitormessagecount > 0 ";
    }
    
    if ($start_date !== null) { 
      $query_params['start_date'] = $start_date;
      $sWhere .= " AND unix_timestamp(t.created) >= :start_date";
    }
    
    if ($end_date !== null) {
      $query_params['end_date'] = $end_date;
      $sWhere .= " AND unix_timestamp(t.created) < :end_date";
    }
    
    if ($operatorid !== null) {
      $query_params['operatorid'] = $operatorid;
      $sWhere .= " AND (:operatorid IS NULL OR t.operatorid=:operatorid)";
    }

    
    if (!empty($departmentid)) {
      $query_params['departmentid'] = $departmentid;
      $sWhere .= " AND t.departmentid = :departmentid ";
    }
    
    if (!empty($locale)) {
      $query_params['locale'] = $locale;
      $sWhere .= " AND t.locale = :locale ";
    }
    
  	if ($offline !== null) {
      $query_params['offline'] = $offline;
      $sWhere .= " AND t.offline=:offline";
    }
    
    $sCountSql .= $sWhere;

    
    try {
      $this->db->query( $sCountSql, $query_params );
      $this->db->nextRecord();
      
      $nTotal = $this->db->getRow( 'cnt' );
    } catch(Exception $e) {
      return 0;
    }
    
    return $nTotal;
  }

  public function enumByDate($start, $end) {
    return $this->makeSearch(
    	"unix_timestamp(created) >= :start and unix_timestamp(created) < :end",
        array("start" => $start, "end" => $end)
    );
  }
  public function getNextRevision() {
    $sql = "UPDATE {revision} SET id = LAST_INSERT_ID(id+1)";
    try {
      $this->db->Query($sql);
      return $this->db->getInsertId();
    } catch (Exception $e) {

      return null;
    }
  }
  
  public function getNextToken() {
    return mt_rand(99999, 99999999);
  }
  
  public function getPendingThreads($since, $includeClosed, $operatorid, $shouldcheckdepartments, $locales) {


    
    $departmentsSql = $shouldcheckdepartments ? " AND (t.departmentid IS NULL OR t.departmentid IN 
            (SELECT departmentid FROM {operatordepartment} WHERE operatorid=:operatorid))" : ''; 
            
    if (!empty($locales)) {
      $escapted = array();
      
      foreach ($locales as $l) {
        $escaped[] = $this->db->getEscapedString($l);
      }
      
      $localeSql = " AND (t.locale IN ('" . join("','", $escaped) . "')) ";
    } else {
      $localeSql = '';
    }
            
    $sql = "SELECT *, unix_timestamp(created) as created FROM {".$this->getTableName()."} t WHERE 1=1 "
            .$departmentsSql
            .$localeSql
            ." AND revision > :since AND (:condition OR state <> :closed)
            LIMIT 10";

    try {
      $this->db->Query($sql, array('operatorid'=>$operatorid, 
        'since'=>$since, 
        'closed'=>STATE_CLOSED, 
        'condition'=>$includeClosed ? TRUE : FALSE));
      return $this->db->getArrayOfRows();
    } catch(Exception $e) {

      return array();
    }
  }

  public function enumOpenWithTimeout($timeout) {
    return $this->makeSearch(
    	"state <> ?
    	AND unix_timestamp(CURRENT_TIMESTAMP) - unix_timestamp(modified) > ?",
        array(STATE_CLOSED, $timeout),
        null,
        10
    );
  }
  
  public function getReportByDate($start, $end, $departmentid = null, $locale = null) {
    $afrom = "";
    $awhere = "";
    $params = array(KIND_AGENT, KIND_USER, $start, $end);
    
    if($departmentid !== null || $locale !== null) {
       $afrom = "
       			LEFT JOIN
       				{thread} as t
       			ON
       				t.threadid = m.threadid 
       		 "; 
       if($departmentid !== null) {
           $awhere .= "AND t.departmentid=?";
           $params[] = $departmentid;
       }
       
       if($locale !== null) {
         $awhere .= "AND t.locale=?";
         $params[] = $locale;
       }
    }
     $sql ="
            SELECT
                UNIX_TIMESTAMP(m.created) as date,
                COUNT(distinct m.threadid) as threads,
                SUM(m.kind = ?) as agents,
                SUM(m.kind = ?) as visitors
            FROM
                {message} as m 
            $afrom
            WHERE
                unix_timestamp(m.created) >= ?
            AND
                unix_timestamp(m.created) < ?
            $awhere
            GROUP BY
                DATE(m.created)
            ORDER BY
                m.created DESC
        ";
     
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
    $params = array(KIND_AGENT, KIND_USER, $start, $end);
    
    if($departmentid !== null || $locale !== null) {
       $afrom = "
       			LEFT JOIN
       				{thread} as t
       			ON
       				t.threadid = m.threadid 
       		 "; 
       if($departmentid !== null) {
           $awhere .= "AND t.departmentid=?";
           $params[] = $departmentid;
       }
       
       if($locale !== null) {
         $awhere .= "AND t.locale=? ";
         $params[] = $locale;
       }
    }
    
    $sql = "
            SELECT
                COUNT(distinct m.threadid) as threads,
                SUM(m.kind = ?) as agents,
                SUM(m.kind = ?) as visitors
            FROM
                {message} as m
            $afrom
            WHERE
                unix_timestamp(m.created) >= ?
            AND
                unix_timestamp(m.created) < ?
            $awhere
         ";
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
      $this->db->query("SELECT DISTINCT(locale) as locale FROM {".$this->getTableName()."} ORDER BY locale");
      return $this->db->getArrayOfRows();
    } catch(Exception $e) {

      return array();
    }
  }
  
  public function removeHistory($threadid) {
    try {
        $this->db->query('DELETE FROM {'.$this->getTableName().'} WHERE threadid=:threadid', $threadid);
    } catch(Exception $e) {

    }
  }

  public function operatorHasActiveThreads($operatorid) {
    $result = $this->makeSearch(
    	"operatorid = ? AND state <> ?",
        array($operatorid, STATE_CLOSED),
        "COUNT(threadid) as cnt",
        1
    );
    
    $result = array_shift($result);
    
    return $result['cnt'] > 0;
  }

  public function getLocaleByVisitSessionId($visitsessionid) {
  	$result = $this->makeSearch("visitsessionid=?", array($visitsessionid), "locale", 1);
	
  	if(empty($result))
		return null;

	$result = array_shift($result);
	return $result['locale'];
  }
}

?>