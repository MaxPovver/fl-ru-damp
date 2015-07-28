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

class OperatorMapper extends BaseMapper {
	public function __construct(DBDriver $db, $model_name) {
 		parent::__construct($db, $model_name, array("recoverytime"));	 
  	}
  	
  	public function getReport($start, $end) {
  	  $sql = "
                    SELECT
                        m.operatorid as opid,
                        o.fullname as name,
                        COUNT(DISTINCT(m.threadid)) as threads,
                        SUM(m.kind = ?) as msgs,
                        AVG(CHAR_LENGTH(m.message)) as avglen
                    FROM
                        {message} as m
                    LEFT JOIN {operator} as o 
                    ON m.operatorid = o.operatorid
                    WHERE
                        unix_timestamp(created) >= ?
                    AND
                        unix_timestamp(created) < ?
                    GROUP BY
                        m.operatorid
                ";
       try {
         $this->db->Query($sql, array(KIND_AGENT, $start, $end));
         return $this->db->getArrayOfRows();
       } catch (Exception $e) {

       }
  	}
  	
  	
  	public function getAdvancedReportByDate($start, $end, $departmentid = null, $locale = null) {
	    $awhere = "";
	    $params = array("start" => $start, "end" => $end);
	    
	    if($departmentid !== null || $locale !== null) { 
	       if($departmentid !== null) {
	       	
	           $awhere .= "
	           				AND o.operatorid IN (
	           					SELECT operatorid 
	           					FROM {operatordepartment} 
	           					WHERE departmentid = :departmentid
	           				)
	           			";
	           $params['departmentid'] = $departmentid;
	       }
	       
	      if($locale !== null) {
 	         $awhere .= "AND (o.locales LIKE :locale OR o.locales IS NULL)";
	         $params['locale'] = '%'.$locale.'%';
	       }
		
	    }
	    
  		$sql = "SELECT
                o.operatorid as opid,
                UNIX_TIMESTAMP(ot.date) as date,
                (
                	SELECT 
                		SEC_TO_TIME(SUM(seconds)) as online_time
					FROM 
						{operatoronline}
					WHERE  
						operatorid = o.operatorid and threadid = -1
					AND 
						date = ot.date
					GROUP BY operatorid
				) as online_time,
				(
                	SELECT 
                		SEC_TO_TIME(SUM(seconds)) as online_time
					FROM 
						{operatoronline}
					WHERE  
						operatorid = o.operatorid and threadid = -2
					AND 
						date = ot.date
					GROUP BY operatorid
				) as online_chatting_time,
				(
                	SELECT 
                		SEC_TO_TIME(SUM(seconds)) as online_time
					FROM 
						{operatoronline}
					WHERE  
						operatorid = o.operatorid and threadid > 0
					AND 
						date = ot.date
					GROUP BY operatorid
				) as online_sum_chatting_time,
				(
                	SELECT 
                		SUM(seconds) as online_time
					FROM 
						{operatoronline}
					WHERE  
						operatorid = o.operatorid and threadid > 0
					AND 
						date = ot.date 	
					GROUP BY operatorid
				) as online_sum_chatting_time_seconds,
				(
					SELECT 
						COUNT(i.invitationid)
					FROM
						{invitation} as i
					INNER JOIN
						{thread} as t
					ON
						i.threadid = t.threadid
					WHERE 
						t.operatorid = ot.operatorid
					AND 
						DATE(t.created) = ot.date
						
				) as invited_users
				FROM 
					{operatorlastaccess} as o
				LEFT JOIN 
					{operatoronline} as ot
				ON 
					o.operatorid = ot.operatorid
				WHERE   
						unix_timestamp(ot.date) >= :start 	
					AND 
						unix_timestamp(ot.date) < :end
					$awhere
				GROUP BY o.operatorid, ot.date
  	    ";
  		
  		try {
          $this->db->Query($sql, $params);
  	      $result = array();
          while($this->db->nextRecord()) {
  	        $row = $this->db->getRow();
  	        
  	        $row['date'] = date(getDateFormat(), $row['date']);
            
  	        if(!isset($result[$row['date']])) {
  	        	$result[$row['date']] = array();
  	        }
			
  	        $result[$row['date']][$row['opid']] = $row;
  	        $result[$row['date']][$row['opid']]['threads'] = 0;
  	        $result[$row['date']][$row['opid']]['msgs'] = 0;
  	        $result[$row['date']][$row['opid']]['avglen'] = 0;
  	        if(!$result[$row['date']][$row['opid']]['online_time']) {
  	          $result[$row['date']][$row['opid']]['online_time'] = 0;
  	        }
  	        
  	        if(!$result[$row['date']][$row['opid']]['online_sum_chatting_time']) {
  	          $result[$row['date']][$row['opid']]['online_sum_chatting_time'] = 0;
  	        }
            
            if(!$result[$row['date']][$row['opid']]['online_chatting_time']) {
  	          $result[$row['date']][$row['opid']]['online_chatting_time'] = 0;
  	        }
            
  	        if(!$result[$row['date']][$row['opid']]['online_sum_chatting_time_seconds']) {
  	          $result[$row['date']][$row['opid']]['online_sum_chatting_time_seconds'] = 0;
  	        }              	                      
  	     
  	        $result[$row['date']][$row['opid']]['online_avg_chatting_time'] = 0;
  	      }
  	      
  	    } catch (Exception $e) {

        } 
  	 
	  $query = "SELECT o.operatorid FROM {operatorlastaccess} as o WHERE 1 $awhere";
	       
       try{
       	$this->db->Query($query, $params);
       	$operators = array();
       	while($this->db->nextRecord()) {
       		$row = $this->db->getRow();
       		$operators[$row['operatorid']] = null;
       	}
       } catch (Exception $e) {

       }
	   
       if(empty($operators)) {
       	return array();
       	
       }
       
    	$sql = "SELECT
                m.operatorid as opid,
                COUNT(DISTINCT(m.threadid)) as threads,
                SUM(m.kind = :kind) as msgs,
                AVG(CHAR_LENGTH(m.message)) as avglen,
                UNIX_TIMESTAMP(m.created) as date 
            FROM
            	{message} as m
            WHERE
            	m.operatorid IS NOT NULL 
            AND
        		m.operatorid IN (" . implode(", ",  array_keys($operators)) . ")
            AND
                unix_timestamp(m.created) >= :start
            AND
                unix_timestamp(m.created) < :end 
            GROUP BY
                m.operatorid, DATE(m.created)";
    	
  	  try {
         $this->db->Query($sql, array("kind" => KIND_AGENT, "start" => $start, "end" => $end));
         while($this->db->nextRecord()) {
           $row = $this->db->getRow();
           
           $row['date'] = date(getDateFormat(), $row['date']);
          
           if (!isset($result[$row['date']][$row['opid']])) {
             continue;
           }
           
           $result[$row['date']][$row['opid']] = array_merge($result[$row['date']][$row['opid']], $row);
            
           if($result[$row['date']][$row['opid']]['online_sum_chatting_time_seconds'] > 0) {
             $online_avg_chatting_time = $result[$row['date']][$row['opid']]['online_sum_chatting_time_seconds'] / $result[$row['date']][$row['opid']]['threads'];
             $result[$row['date']][$row['opid']]['online_avg_chatting_time'] = sprintf("%02d", (int)($online_avg_chatting_time/60)) . ":" . sprintf("%02d", $online_avg_chatting_time % 60);
           }           
         }
       } catch (Exception $e) {

       }

       foreach ($operators as $id => $v) {
  	     $operator = Operator::getInstance()->GetOperatorById($id);  
  	     $operators[$id] = $operator['fullname'];
  	   }
  	   
  	   foreach ($result as $date => $data) {
  	   	foreach ($operators as $id => $name) {
  	   		if(!isset($data[$id])) {
  	   			$result[$date][$id] = array(
  	   				'opid' => $id,
					'date' => $date,
					'online_time' => 0,
					'online_chatting_time' => 0,
					'online_sum_chatting_time' => 0,
					'online_sum_chatting_time_seconds' => 0,
					'threads' => 0,
					'msgs' => 0,
					'avglen' => 0,
					'online_avg_chatting_time' => 0,
  	   			    'invited_users' => 0
  	   			);
  	   		}
  	   		
  	   		$result[$date][$id]['name'] = $name;
  	   	}
  	   }
  	   
	   
       $sql = "
       		SELECT threadid AS tid, 
       			UNIX_TIMESTAMP(created) as date,
       			(
					SELECT created
					FROM {thread}
					WHERE
					threadid =  tid
					LIMIT 1
				) AS started, 
				(
					SELECT created
					FROM {message}
					WHERE kind = ?
					AND  threadid =  tid 
					AND operatorid IS NOT NULL 
					ORDER BY created ASC 
					LIMIT 1
				) AS replied, 
				(
					SELECT operatorid
					FROM  {message}
					WHERE kind = ?
					AND  threadid =  tid 
					AND operatorid IS NOT NULL 
					ORDER BY created ASC 
					LIMIT 1
				) AS operatorid, 
				(
					SELECT UNIX_TIMESTAMP(replied) - UNIX_TIMESTAMP(started)
				) AS delta
				FROM {message} as m
				WHERE
						unix_timestamp(created) >= ? 	
				AND 
						unix_timestamp(created) < ?
				AND
        				m.operatorid IN (" . implode(", ",  array_keys($operators)) . ")
				GROUP BY threadid, DATE(created)";
  		try {
         $this->db->Query($sql, array(KIND_AGENT, KIND_AGENT, $start, $end));
         $result_deltas = array();
       	 while($this->db->nextRecord()) {
       	 	$row = $this->db->getRow();
       	 	$row['date'] = date(getDateFormat(), $row['date']);
       	 	
       	 	if($row['operatorid'] == null)
       	 		continue;
       	 	
       	 	$date = $row['date'];
       	 	$opid = $row['operatorid'];
       	 	
       	 	if(!isset($result_deltas[$date])) {
       	 		$result_deltas[$date] = array();
       	 	}
       	 	
       	 	if(!isset($result_deltas[$date][$opid])) {
       	 		$result_deltas[$date][$opid]['sum'] = 0;
       	 		$result_deltas[$date][$opid]['count'] = 0;
       	 		$result_deltas[$date][$opid]['sum_square'] = 0;
       	 		$result_deltas[$date][$opid]['values'] = array();
       	 	}
       	 	$result_deltas[$date][$opid]['sum'] += $row['delta'];
       	 	$result_deltas[$date][$opid]['count'] += 1;
       	 	$result_deltas[$date][$opid]['sum_square'] += sqrt($row['delta']);
       	 	$result_deltas[$date][$opid]['values'][] = $row['delta'];
       	 }
       } catch (Exception $e) {

       }
       
       foreach ($result as $date => $data) {
       	foreach (array_keys($data) as $id) {
       		$result[$date][$id]['avg_answer_time'] = 0;
       		$result[$date][$id]['answer_time_st_deviation'] = 0;
       		if(isset($result_deltas[$date], $result_deltas[$date][$id]))	{
       			$avg = $result_deltas[$date][$id]['sum']/$result_deltas[$date][$id]['count'];
       			$avg_ceil = ceil($avg);
       			$result[$date][$id]['avg_answer_time'] = sprintf("%02d", (int)($avg_ceil / 60)) . ":". sprintf("%02d", $avg_ceil % 60);
       			$dispersion = 0;
       		
       			foreach ($result_deltas[$date][$id]['values'] as $v) {
       				$dispersion += (($avg-$v)*($avg-$v))/$result_deltas[$date][$id]['count'];
       			}
      
       			$result[$date][$id]['answer_time_st_deviation'] = sprintf("%.2f",sqrt($dispersion));
       		}
       	}
       }
       
       

  	   
  	   return $result;
  	}
  	
  	public function getAdvancedReport($start, $end, $departmentid = null, $locale = null) {
  	 	$afrom = "";
	    $awhere = "";
	    $params = array("start" => $start, "end" => $end);
	    
	    if($departmentid !== null || $locale !== null) {
	       if($departmentid !== null) {
	       	
	           $awhere .= "
	           				WHERE o.operatorid IN (
	           					SELECT operatorid 
	           					FROM {operatordepartment} 
	           					WHERE departmentid = :departmentid
	           				)
	           			";
	           $params['departmentid'] = $departmentid;
	       }
	       
	      if($locale !== null) {
	      	 $awhere .= empty($awhere) ? 'WHERE ' : 'AND ';
 	         $awhere .= "(o.locales LIKE :locale OR o.locales IS NULL)";
	         $params['locale'] = '%'.$locale.'%';
	       }
		
	    }
	    
  	    $sql = "SELECT
                    o.operatorid as opid,
                	(
                    	SELECT
                        	AVG(rate)
                    	FROM
                        	{rate} as r
                    	WHERE
                        	operatorid = o.operatorid
                    	AND
                        	unix_timestamp(r.date) >= :start
                    	AND
                        	unix_timestamp(r.date) < :end
                    	AND
                    		deldate IS NULL
               		) AS rating, 
                	(
                    	SELECT
                       		COUNT(rate)
                   	 	FROM
                    	    {rate} as r
                    	WHERE
                        	operatorid = o.operatorid
                   		AND
                        	unix_timestamp(r.date) >= :start
                    	AND
                        	unix_timestamp(r.date) < :end
                    	AND
                    		deldate IS NULL
                ) AS rate_count, 
                (
                	SELECT 
                		SEC_TO_TIME(SUM(seconds)) as online_time
					FROM 
						{operatoronline}
					WHERE  
						operatorid = o.operatorid and threadid = -1
					AND 
						unix_timestamp(date) >= :start 	
					AND 
						unix_timestamp(date) < :end
					GROUP BY operatorid
				) as online_time,
				(
                	SELECT 
                		SEC_TO_TIME(SUM(seconds)) as online_time
					FROM 
						{operatoronline}
					WHERE  
						operatorid = o.operatorid and threadid = -2
					AND 
						unix_timestamp(date) >= :start 	
					AND 
						unix_timestamp(date) < :end
					GROUP BY operatorid
				) as online_chatting_time,
				(
                	SELECT 
                		SEC_TO_TIME(SUM(seconds)) as online_time
					FROM 
						{operatoronline}
					WHERE  
						operatorid = o.operatorid and threadid > 0
					AND 
						unix_timestamp(date) >= :start 	
					AND 
						unix_timestamp(date) < :end
					GROUP BY operatorid
				) as online_sum_chatting_time,
				(
                	SELECT 
                		SUM(seconds) as online_time
					FROM 
						{operatoronline}
					WHERE  
						operatorid = o.operatorid and threadid > 0
					AND 
						unix_timestamp(date) >= :start 	
					AND 
						unix_timestamp(date) < :end
					GROUP BY operatorid
				) as online_sum_chatting_time_seconds,
				(
					SELECT 
						COUNT(i.invitationid)
					FROM
						{invitation} as i
					INNER JOIN
						{thread} as t
					ON
						i.threadid = t.threadid
					WHERE 
						t.operatorid = o.operatorid
					AND 
						unix_timestamp(t.created) >= :start 	
					AND 
						unix_timestamp(t.created) < :end
				) as invited_users
				FROM 
					{operatorlastaccess} as o
				$awhere
				ORDER BY o.operatorid
  	    ";
		
  	    try {
          $this->db->Query($sql, $params);
  	      $result = array();
          while($this->db->nextRecord()) {
  	        $row = $this->db->getRow();
  	        $result[$row['opid']] = $row;
  	        $result[$row['opid']]['threads'] = 0;
  	        $result[$row['opid']]['msgs'] = 0;
  	        $result[$row['opid']]['avglen'] = 0;
  	        if(!$result[$row['opid']]['online_time']) {
  	          $result[$row['opid']]['online_time'] = 0;
  	        }
  	        
  	        if(!$result[$row['opid']]['online_sum_chatting_time']) {
  	          $result[$row['opid']]['online_sum_chatting_time'] = 0;
  	        }
  	        
            if(!$result[$row['opid']]['online_chatting_time']) {
  	          $result[$row['opid']]['online_chatting_time'] = 0;
  	        }
            
  	        if(!$result[$row['opid']]['online_sum_chatting_time_seconds']) {
  	          $result[$row['opid']]['online_sum_chatting_time_seconds'] = 0;
  	        }
  	        
  	        $result[$row['opid']]['online_avg_chatting_time'] = 0;
  	      }
  	      

  	    } catch (Exception $e) {

        }
  	   
        if(empty($result)) { 
  	   		return array();
  	   	}
       
  	   	foreach ($result as $id => $data) {
  	    	$operator = Operator::getInstance()->GetOperatorById($id);  
  	     	$result[$id]['name'] = $operator['fullname'];
  	   	}
  	   
  	    $sql = "SELECT
                    m.operatorid as opid,
                    COUNT(DISTINCT(m.threadid)) as threads,
                    SUM(m.kind = :kind) as msgs,
                    AVG(CHAR_LENGTH(m.message)) as avglen 
                FROM
                	{message} as m
                WHERE
                	m.operatorid IS NOT NULL
        		AND
        			m.operatorid IN (" . implode(", ",  array_keys($result)) . ")
                AND
                    unix_timestamp(m.created) >= :start
                AND
                    unix_timestamp(m.created) < :end 
                GROUP BY
                    m.operatorid";
  	  try {
         $this->db->Query($sql, array("kind" => KIND_AGENT, "start" => $start, "end" => $end));
         while($this->db->nextRecord()) {
           $row = $this->db->getRow();
           $result[$row['opid']] = array_merge($result[$row['opid']], $row);
           
           if($result[$row['opid']]['online_sum_chatting_time_seconds'] != 0 ) {
             $online_avg_chatting_time = $result[$row['opid']]['online_sum_chatting_time_seconds'] / $result[$row['opid']]['threads'];
             $result[$row['opid']]['online_avg_chatting_time'] = sprintf("%02d", (int)($online_avg_chatting_time/60)) . ":" . sprintf("%02d", $online_avg_chatting_time % 60);
           } else {
             $result[$row['opid']]['online_avg_chatting_time'] = 0;
           }
           
         }
       } catch (Exception $e) {

       }

       
       
       $sql = "
       		SELECT threadid AS tid, 
       			(
					SELECT created
					FROM {thread}
					WHERE
					threadid =  tid
					LIMIT 1
				) AS started, 
				(
					SELECT created
					FROM {message}
					WHERE kind = ?
					AND  threadid =  tid 
					AND operatorid IS NOT NULL 
					ORDER BY created ASC 
					LIMIT 1
				) AS replied, 
				(
					SELECT operatorid
					FROM  {message}
					WHERE kind = ?
					AND  threadid =  tid 
					AND operatorid IS NOT NULL 
					ORDER BY created ASC 
					LIMIT 1
				) AS operatorid, 
				(
					SELECT UNIX_TIMESTAMP(replied) - UNIX_TIMESTAMP(started)
				) AS delta
				FROM {message}
				WHERE
						operatorid IN (" . implode(", ",  array_keys($result)) . ")
				AND
						unix_timestamp(created) >= ? 	
				AND 
						unix_timestamp(created) < ?
				GROUP BY threadid";
  		try {
         $this->db->Query($sql, array(KIND_AGENT, KIND_AGENT, $start, $end));
         $result_deltas = array();
       	 while($this->db->nextRecord()) {
       	 	$row = $this->db->getRow();
       	 	if($row['operatorid'] == null)
       	 		continue;
       	 		
       	 	$opid = $row['operatorid'];
       	 	if(!isset($result_deltas[$opid])) {
       	 		$result_deltas[$opid]['sum'] = 0;
       	 		$result_deltas[$opid]['count'] = 0;
       	 		$result_deltas[$opid]['sum_square'] = 0;
       	 		$result_deltas[$opid]['values'] = array();
       	 	}
       	 	$result_deltas[$opid]['sum'] += $row['delta'];
       	 	$result_deltas[$opid]['count'] += 1;
       	 	$result_deltas[$opid]['sum_square'] += sqrt($row['delta']);
       	 	$result_deltas[$opid]['values'][] = $row['delta'];
       	 }
       } catch (Exception $e) {

       }

       foreach ($result as $k => $v) {
       	$result[$k]['avg_answer_time'] = 0;
       	$result[$k]['answer_time_st_deviation'] = 0;
       	$opid = $v['opid'];
       	if(isset($result_deltas[$opid]))	{
       		$avg = $result_deltas[$opid]['sum']/$result_deltas[$opid]['count'];
       		$avg_ceil = ceil($avg);
       		$result[$k]['avg_answer_time'] = sprintf("%02d", (int)($avg_ceil / 60)) . ":". sprintf("%02d", $avg_ceil % 60);
       		$dispersion = 0;
       		
       		foreach ($result_deltas[$opid]['values'] as $v) {
       			$dispersion += (($avg-$v)*($avg-$v))/$result_deltas[$opid]['count'];
       		}
      
       		$result[$k]['answer_time_st_deviation'] = sprintf("%.2f",sqrt($dispersion));
       	}
       }
       
       return $result;
  	}

  	
  	
  	public function getByLogin($login) {
  	  return array_shift($r = $this->makeSearch("login = ?", $login, null, 1));  
  	}
    
  	public function getByLoginAndPassword($login, $password) {
  	  return array_shift($r = $this->makeSearch("login = ? AND password = MD5(?)", array($login, $password), null, 1));  
  	}
        
    /**
     * Сохраняет данные по оператору
     * А именно начальную метку времени для определения статуса online в countOnlineOperators и getOnlineOperators
     * 
     * Альтернатива папке online_stats
     * 
     * @param  int $operatorid ID оператора
     * @return bool true - успех, false - провал
     */
    public function insertOperatorTime( $operatorid ) {
        $sQuery = 'INSERT INTO {operatortime} (operatorid, operatortime) VALUES (:operatorid, 0)
            ON DUPLICATE KEY UPDATE operatortime = 0';
        
        try {
            $this->db->Query( $sQuery, array('operatorid' => $operatorid) );
            return true;
        } 
        catch ( Exception $e ) {
            return false;
        }
    }
    
    /**
     * Сохраняет данные по оператору
     * А именно текущую метку времени для определения статуса online в countOnlineOperators и getOnlineOperators
     * 
     * Альтернатива папке online_stats
     * 
     * @param  int $operatorid ID оператора
     * @return bool true - успех, false - провал
     */
    public function updateOperatorTime( $operatorid ) {
        $sQuery = 'UPDATE {operatortime} SET operatortime = ? WHERE operatorid = ?';
        
        try {
            $this->db->Query( $sQuery, array(time(), $operatorid), true );
            return true;
        } 
        catch ( Exception $e ) {
            return false;
        }
    }
    
    /**
     * Возвращает данные по оераторам
     * Отделы, локали, метки времени
     * 
     * Альтернатива папке online_stats
     * 
     * @return array
     */
    public function getOnlineOperatorsFromDB() {
        $sQuery = 'SELECT ot.operatorid, ot.operatortime, d.departmentkey, la.locales 
  		FROM {operatortime} ot 
                LEFT JOIN {operatorlastaccess} la ON la.operatorid = ot.operatorid 
                LEFT JOIN {operatordepartment} od ON od.operatorid = ot.operatorid 
  		LEFT JOIN {department} d ON d.departmentid = od.departmentid ';
        
        try {
            $this->db->Query( $sQuery, array() );
            return $this->db->getArrayOfRows(); 
        } 
        catch ( Exception $e ) {
            return array();
        }
    }
    
}
?>