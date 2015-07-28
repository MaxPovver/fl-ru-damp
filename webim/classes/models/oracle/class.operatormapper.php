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
                        WM_UNIX_TIMESTAMP(created) >= ?
                    AND
                        WM_UNIX_TIMESTAMP(created) < ?
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
  		$afrom = "";
	    $awhere = "";
	    $params = array("p_start" => $start, "p_end" => $end);
	    $aparams = array();
	    if($departmentid !== null || $locale !== null) {
	       $afrom = '
	       			LEFT JOIN
	       				"{operator}" op
	       			ON
	       				o."operatorid" = op."operatorid"
	       		 '; 
	       if($departmentid !== null) {
	       	
	           $awhere .= '
	           				AND op."operatorid" IN (
	           					SELECT "operatorid" 
	           					FROM "{operatordepartment}" 
	           					WHERE "departmentid" = :departmentid
	           				)
	           			';
	           $aparams['departmentid'] = $departmentid;
	       }
	       
	      if($locale !== null) {
 	         $awhere .= 'AND (o."locales" LIKE :locale OR o."locales" IS NULL)';
	         $aparams['locale'] = '%'.$locale.'%';
	       }
		
	    }
	    
  		$sql = 'SELECT
                ot."operatorid" "opid",
                WM_UNIX_TIMESTAMP(ot."date") "date",
                (
                	SELECT 
                		WM_SEC_TO_TIME(SUM("seconds")) "online_time"
					FROM 
						"{operatoronline}"
					WHERE  
						"operatorid" = ot."operatorid" and "threadid" = -1
					AND 
						"date" = ot."date"
					GROUP BY "operatorid"
				) "online_time",
				(
                	SELECT 
                		WM_SEC_TO_TIME(SUM("seconds")) "online_time"
					FROM 
						"{operatoronline}"
					WHERE  
						"operatorid" = ot."operatorid" and "threadid" = -2
					AND 
						"date" = ot."date"
					GROUP BY "operatorid"
				)  "online_chatting_time",
				(
                	SELECT 
                		WM_SEC_TO_TIME(SUM("seconds")) "online_time"
					FROM 
						"{operatoronline}"
					WHERE  
						"operatorid" = ot."operatorid" and "threadid" > 0
					AND 
						"date" = ot."date"
					GROUP BY "operatorid"
				) "online_sum_chatting_time",
				(
                	SELECT 
                		SUM("seconds") "online_time"
					FROM 
						"{operatoronline}"
					WHERE  
						"operatorid" = ot."operatorid" and "threadid" > 0
					AND 
						"date" = ot."date" 	
					GROUP BY "operatorid"
				) "online_sum_chat_time_seconds",
				(
					SELECT 
						COUNT(i."invitationid")
					FROM
						"{invitation}" i
					INNER JOIN
						"{thread}" t
					ON
						i."threadid" = t."threadid"
					WHERE 
						t."operatorid" = ot."operatorid"
					AND 
						TRUNC(t."created") = TRUNC(ot."date")
						
				) "invited_users"
				FROM 
					"{operatorlastaccess}" o
				LEFT JOIN 
					"{operatoronline}" ot
				ON 
					o."operatorid" = ot."operatorid"
				' . $afrom . '
				WHERE   
						WM_UNIX_TIMESTAMP(ot."date") >= :p_start
					AND 
						WM_UNIX_TIMESTAMP(ot."date") < :p_end
					' . $awhere . ' 
				GROUP BY ot."operatorid", ot."date" 
  	    ';
  		
  		try {
          $this->db->Query($sql, array_merge($params, $aparams));
  	      $result = array();
          while($this->db->nextRecord()) {
  	        $row = $this->db->getRow();
  	        $row['online_sum_chatting_time_seconds'] = $row['online_sum_chat_time_seconds']; //Cause indetifiers more than 30 chars not suppoted by Oracle
  	        unset($row['online_sum_chat_time_seconds']);
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
  	 
	  $query = 'SELECT o."operatorid" FROM "{operatorlastaccess}" o ' . $afrom . ' WHERE 1=1 ' . $awhere;
	       
       try{
       	$this->db->Query($query, $aparams);
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
       
    	$sql = '
    			SELECT
                m."operatorid" "opid",
                COUNT(DISTINCT(m."threadid")) "threads",
                SUM(
                   	(
	              		SELECT COUNT(*) 
	                	FROM "{message}" mi
	                	WHERE
	                	mi."messageid" = m."messageid"
	                	AND mi."kind" = :kind
                	)
                ) "msgs",
                AVG(LENGTH(m."message")) "avglen",
                WM_UNIX_TIMESTAMP(TRUNC(m."created")) as "date" 
            FROM
            	"{message}" m
            WHERE
            	m."operatorid" IS NOT NULL 
            AND
        		m."operatorid" IN (' . implode(", ",  array_keys($operators)) . ')
            AND
                WM_UNIX_TIMESTAMP(m."created") >= :p_start
            AND
                WM_UNIX_TIMESTAMP(m."created") < :p_end
            GROUP BY
                m."operatorid", TRUNC(m."created")';
      
  	  try {
         $this->db->Query($sql, array("kind" => KIND_AGENT, "p_start" => $start, "p_end" => $end));
         while($this->db->nextRecord()) {
           $row = $this->db->getRow();
           $row['date'] = date(getDateFormat(), $row['date']);
         
  	       if(!isset($result[$row['date']])) {
  	         $result[$row['date']] = array();
  	       }
         
  	       if(!isset($result[$row['date']][$row['opid']])) {
  	         $result[$row['date']][$row['opid']] = array();
  	       }
  	       
           $result[$row['date']][$row['opid']] = array_merge($result[$row['date']][$row['opid']], $row);
            
           if(isset($result[$row['date']][$row['opid']]['online_sum_chatting_time_seconds']) && $result[$row['date']][$row['opid']]['online_sum_chatting_time_seconds'] > 0) {
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
  	   
	   
  	    $sql = '
       		SELECT s.*, (WM_UNIX_TIMESTAMP(s."replied") - WM_UNIX_TIMESTAMP(s."started")) "delta" FROM 
       		(
       			SELECT "threadid" "tid", 
       			WM_UNIX_TIMESTAMP(TRUNC("created")) "date",
       			(
					SELECT ti."created"
					FROM "{thread}" ti
					WHERE
					"threadid" = m."threadid"
				) "started", 
				(
					SELECT MIN("created")
					FROM "{message}" mi
					WHERE "kind" = :kind_agent
					AND "threadid" = m."threadid"
					AND "operatorid" IS NOT NULL
				) "replied", 
				(
					SELECT "operatorid"
					FROM  "{message}"
					WHERE "kind" = :kind_agent
					AND  "threadid" =  m."threadid"
					AND "operatorid" IS NOT NULL
					AND "created" = (
						SELECT MIN("created") 
						FROM  "{message}"
						WHERE "kind" = :kind_agent
						AND  "threadid" =  m."threadid"
						AND "operatorid" IS NOT NULL
					)
				) "operatorid"
				FROM "{message}" m
				WHERE
						m."operatorid" IN (' . implode(", ",  array_keys($operators)) . ')
				AND
						WM_UNIX_TIMESTAMP("created") >= :p_start
				AND 
						WM_UNIX_TIMESTAMP("created") < :p_end
				GROUP BY "threadid", TRUNC("created")
			) s';
  	    
//       $sql = "
//       		SELECT "threadid"" AS tid, 
//       			WM_UNIX_TIMESTAMP(created) as date,
//       			(
//					SELECT created
//					FROM {thread}
//					WHERE
//					threadid =  tid
//					LIMIT 1
//				) AS started, 
//				(
//					SELECT created
//					FROM {message}
//					WHERE kind = ?
//					AND  threadid =  tid 
//					AND operatorid IS NOT NULL 
//					ORDER BY created ASC 
//					LIMIT 1
//				) AS replied, 
//				(
//					SELECT operatorid
//					FROM  {message}
//					WHERE kind = ?
//					AND  threadid =  tid 
//					AND operatorid IS NOT NULL 
//					ORDER BY created ASC 
//					LIMIT 1
//				) AS operatorid, 
//				(
//					SELECT WM_UNIX_TIMESTAMP(replied) - WM_UNIX_TIMESTAMP(started)
//				) AS delta
//				FROM {message} as m
//				WHERE
//						WM_UNIX_TIMESTAMP(created) >= ?
//				AND 
//						WM_UNIX_TIMESTAMP(created) < ?
//				AND
//        				m.operatorid IN (" . implode(", ",  array_keys($operators)) . ")
//				GROUP BY threadid, DATE(created)";
  		try {
         $this->db->Query($sql, array("kind_agent" => KIND_AGENT, "p_start" => $start, "p_end" => $end));
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
	    $params = array("p_start" => $start, "p_end" => $end);
	    
	    if($departmentid !== null || $locale !== null) {
	       $afrom = '
	       			LEFT JOIN
	       				"{operator}" op
	       			ON
	       				o."operatorid" = op."operatorid"
	       		 '; 
	       if($departmentid !== null) {
	       	
	           $awhere .= '
	           				WHERE op."operatorid" IN (
	           					SELECT "operatorid" 
	           					FROM "{operatordepartment}" 
	           					WHERE "departmentid" = :departmentid
	           				)
	           			';
	           $params['departmentid'] = $departmentid;
	       }
	       
	      if($locale !== null) {
	      	 $awhere .= empty($awhere) ? 'WHERE ' : 'AND ';
 	         $awhere .= '(o."locales" LIKE :locale OR o."locales" IS NULL)';
	         $params['locale'] = '%'.$locale.'%';
	       }
		
	    }
	    
  	    $sql = '
  	    		SELECT
                    o."operatorid" "opid",
                	(
                    	SELECT
                        	AVG("rate")
                    	FROM
                        	"{rate}" r
                    	WHERE
                        	"operatorid" = o."operatorid"
                    	AND
                        	WM_UNIX_TIMESTAMP(r."date") >= :p_start
                    	AND
                        	WM_UNIX_TIMESTAMP(r."date") < :p_end
                    	AND
                    		"deldate" IS NULL
               		) "rating", 
                	(
                    	SELECT
                       		COUNT("rate")
                   	 	FROM
                    	    "{rate}" r
                    	WHERE
                        	"operatorid" = o."operatorid"
                   		AND
                        	WM_UNIX_TIMESTAMP(r."date") >= :p_start
                    	AND
                        	WM_UNIX_TIMESTAMP(r."date") < :p_end
                    	AND
                    		"deldate" IS NULL
                	) "rate_count", 
                	(
                		SELECT 
                			WM_SEC_TO_TIME(SUM("seconds")) "online_time"
						FROM 
							"{operatoronline}"
						WHERE  
							"operatorid" = o."operatorid" and "threadid" = -1
						AND 
							WM_UNIX_TIMESTAMP("date") >= :p_start
						AND 
							WM_UNIX_TIMESTAMP("date") < :p_end
						GROUP BY "operatorid"
					) "online_time",
				(
                	SELECT 
                		WM_SEC_TO_TIME(SUM("seconds")) "online_time"
					FROM 
						"{operatoronline}"
					WHERE  
						"operatorid" = o."operatorid" and "threadid" = -2
					AND 
						WM_UNIX_TIMESTAMP("date") >= :p_start
					AND 
						WM_UNIX_TIMESTAMP("date") < :p_end
					GROUP BY "operatorid"
				) "online_chatting_time",
				(
                	SELECT 
                		WM_SEC_TO_TIME(SUM("seconds")) "online_time"
					FROM 
						"{operatoronline}"
					WHERE  
						"operatorid" = o."operatorid" and "threadid" > 0
					AND 
						WM_UNIX_TIMESTAMP("date") >= :p_start
					AND 
						WM_UNIX_TIMESTAMP("date") < :p_end
					GROUP BY "operatorid"
				) "online_sum_chatting_time",
				(
                	SELECT 
                		SUM("seconds") "online_time"
					FROM 
						"{operatoronline}"
					WHERE  
						"operatorid" = o."operatorid" and "threadid" > 0
					AND 
						WM_UNIX_TIMESTAMP("date") >= :p_start
					AND 
						WM_UNIX_TIMESTAMP("date") < :p_end
					GROUP BY "operatorid"
				) "online_sum_chat_time_seconds",
				(
					SELECT 
						COUNT(i."invitationid")
					FROM
						"{invitation}" i
					INNER JOIN
						"{thread}" t
					ON
						i."threadid" = t."threadid"
					WHERE 
						t."operatorid" = o."operatorid"
					AND 
						WM_UNIX_TIMESTAMP(t."created") >= :p_start
					AND 
						WM_UNIX_TIMESTAMP(t."created") < :p_end
				) "invited_users"
				FROM 
					"{operatorlastaccess}" o
				' . $afrom . ' 
				' . $awhere . '
				ORDER BY o.operatorid';
		
  	    try {
          $this->db->Query($sql, $params);
  	      $result = array();
          while($this->db->nextRecord()) {
  	        $row = $this->db->getRow();
  	        $row['online_sum_chatting_time_seconds'] = $row['online_sum_chat_time_seconds']; //Cause indetifiers more than 30 chars not suppoted by Oracle
  	        unset($row['online_sum_chat_time_seconds']);
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
  	   
  	    $sql = 'SELECT
                    m."operatorid" "opid",
                    COUNT(DISTINCT(m."threadid")) "threads",
                    SUM(
                    	(
	                		SELECT COUNT(*) 
	                		FROM "{message}" mi
	                		WHERE
	                		mi."messageid" = m."messageid"
	                		AND mi."kind" = :kind
                		)
                	) "msgs",
                    AVG(LENGTH(m."message")) "avglen" 
                FROM
                	"{message}" m
                WHERE
                	m."operatorid" IS NOT NULL
        		AND
        			m."operatorid" IN (' . implode(", ",  array_keys($result)) . ')
                AND
                    WM_UNIX_TIMESTAMP(m."created") >= :p_start
                AND
                    WM_UNIX_TIMESTAMP(m."created") < :p_end
                GROUP BY
                    m."operatorid"';
  	  try {
         $this->db->Query($sql, array("kind" => KIND_AGENT, "p_start" => $start, "p_end" => $end));
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

       
       /*РЎСЂРµРґРЅРµРµ РІСЂРµРјСЏ РѕС‚РІРµС‚Р° РѕРїРµСЂР°С‚РѕСЂР°*/
       $sql = '
       		SELECT s.*, (WM_UNIX_TIMESTAMP(s."replied") - WM_UNIX_TIMESTAMP(s."started")) "delta" FROM 
       		(
       			SELECT "threadid" "tid", 
       			(
					SELECT ti."created"
					FROM "{thread}" ti
					WHERE
					"threadid" = m."threadid"
				) "started", 
				(
					SELECT MIN("created")
					FROM "{message}" mi
					WHERE "kind" = :kind_agent
					AND "threadid" = m."threadid"
					AND "operatorid" IS NOT NULL
				) "replied", 
				(
					SELECT "operatorid"
					FROM  "{message}"
					WHERE "kind" = :kind_agent
					AND  "threadid" =  m."threadid"
					AND "operatorid" IS NOT NULL
					AND "created" = (
						SELECT MIN("created") 
						FROM  "{message}"
						WHERE "kind" = :kind_agent
						AND  "threadid" =  m."threadid"
						AND "operatorid" IS NOT NULL
					)
				) "operatorid"
				FROM "{message}" m
				WHERE
						"operatorid" IN (' . implode(", ",  array_keys($result)) . ')
				AND
						WM_UNIX_TIMESTAMP("created") >= :p_start
				AND 
						WM_UNIX_TIMESTAMP("created") < :p_end
				GROUP BY "threadid"
			) s';
  		try {
         $this->db->Query($sql, array("kind_agent" => KIND_AGENT, "p_start" => $start, "p_end" => $end));
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

  	/* end pb */
  	
  	public function getByLogin($login) {
  	  return array_shift($r = $this->makeSearch('"login" = :login', array("login" => $login), null, 1));  
  	}
    
  	public function getByLoginAndPassword($login, $password) {
  	  return array_shift($r = $this->makeSearch('"login" = :login AND "password" = WM_MD5(:password)', array("login" => $login, "password" => $password), null, 1));  
  	}
  	
}
?>