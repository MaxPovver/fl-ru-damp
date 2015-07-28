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

class OperatorOnlineMapper extends BaseMapper {
	public function __construct(DBDriver $db, $model_name) {
 		parent::__construct($db, $model_name, array("updated"), false, "id");	 	
  	}
  	
  	public function updateOperatorOnlineTime($operatorid, $threadid = -1) {
//  		$sql = '
//			MERGE INTO "{operatoronline}" oo 
//  			USING "{operatoronline}" s
//  			ON (s."operatorid" = oo."operatorid" AND s."threadid" = oo."threadid")
//  			WHEN MATCHED THEN
//  				UPDATE SET "seconds" = (
//  						SELECT 
//  							WHEN WM_UNIX_TIMESTAMP(SYSDATE) - WM_UNIX_TIMESTAMP("updated") < :timeout THEN
//  								"seconds" + WM_UNIX_TIMESTAMP(SYSDATE) - WM_UNIX_TIMESTAMP("updated")
//  							ELSE 
//  				    			"seconds" + 1
//  				    		ENDCASE
//  				    	FROM "{operatoronline}"
//  				    	WHERE s."operatorid" = oo."operatorid" AND s."threadid" = oo."threadid"
//  				    	)
//  			WHEN NOT MATCHED THEN		
//  				INSERT ("date", "operatorid", "threadid", "seconds")
//  				VALUES (TO_DATE(:p_date, \'YYYYMMDD\'), :operatorid, :threadid, :p_seconds) 
//  		 ';
  		
//  	    $sql = '
//  			IF EXITS(SELECT "operatorid" FROM "{operatoronline}" WHERE "operatorid"=:operatorid) THEN
//  			INSERT INTO {operatoronline}
//  				(date, operatorid, threadid, seconds)
//  				VALUES (:date, :operatorid, :threadid, :seconds) 
//			ELSE
//			 	UPDATE 
//  				    seconds = IF(
//  						WM_UNIX_TIMESTAMP(NOW())-WM_UNIX_TIMESTAMP(updated) < :timeout, 
//  						seconds + WM_UNIX_TIMESTAMP(NOW())-WM_UNIX_TIMESTAMP(updated), 
//  						seconds + 1
//  						)
//  			ENDIF';
  		try {
//        	/$this->db->Query($sql, array(
//        		"p_date" => date("Ymd"), 
//        		"operatorid" => $operatorid, 
//        		"p_seconds" => 0,
//        		"timeout" => TIMEOUT_OPERATOR_PING,
//        		"threadid" => $threadid
//        	));
      	} catch (Exception $e) {

      	}	
  	}
	
  	public function pushOnlineStatsForOperator($operatorid, $stats) {
  	  
  	    $min_date = 0;
  	    foreach ($stats as $d) {
  	      foreach (array_keys($d) as $date) {
  	        $cur_time = strtotime($date);
  	        if($min_date == 0) {
  	          $min_date = $cur_time;  
  	        }
  	        
  	        $min_date = min($cur_time, $min_date);
  	      }
  	    }
  	    
  	    $min_date = date("Y-m-d", $min_date);
  	      
  	    $results = $this->makeSearch('"operatorid" = :operatorid AND "date" >= TO_DATE(:min_date,\'YYYY-MM-DD\')', array("operatorid" => $operatorid, "min_date" => $min_date), 't.*, TO_CHAR("date", \'YYYY-MM-DD\') "date"');
  	    $db_stats = array();
        foreach ($results as $r) {
          if(!isset($db_stats[$r['threadid']])) {
            $db_stats[$r['threadid']] = array();
          } 
          
          $db_stats[$r['threadid']][$r['date']] = $r;
        }
        
        foreach ($stats as $id => $d) {
          foreach ($d as $date => $v ) {
            if(isset($db_stats[$id], $db_stats[$id][$date])) {
              $data = $db_stats[$id][$date];
              $data['seconds'] += $v['seconds'];
              $data['updated'] = date("Y-m-d H:i:s", $v['updated']);
              $this->update($data); 
            } else {
              $data = $v;
              $data['operatorid'] = $operatorid;
              $data['updated'] = date("Y-m-d H:i:s", $data['updated']);
              $this->add($data);
            }
          }
        } 
  	}
}
?>