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
class LostVisitorMapper extends BaseMapper {  
    public function __construct(DBDriver $db, $model_name) {
		parent::__construct($db, $model_name, array());
	}

    public function addLostVisitor($threadid, $operatorid, $anotheroperatorid = null) {
      $sql = "INSERT INTO {lostvisitor} 
        SET threadid = :threadid,
        operatorid = :operatorid, 
        anotheroperatorid = :anotheroperatorid,
        waittime = (
          SELECT (NOW() - created) as waittime 
          FROM {thread} 
          WHERE threadid = :threadid
        ) ON DUPLICATE KEY UPDATE waittime = (
	          SELECT (NOW() - created) as waittime 
	          FROM {thread} 
	          WHERE threadid = :threadid
        	)
        ";
      
      try {
        $this->db->Query($sql, array(
        	"threadid" => $threadid, 
        	"operatorid" => $operatorid,
            "anotheroperatorid" => $anotheroperatorid
        ));
      } catch (Exception $e) {

      }
    }

    public function getReportInterceptedByOperator($start, $end, $departmentid = null, $locale = null) {
       return $this->getReportLostOrInterceptedByOperator($start, $end, true, $departmentid, $locale);
    }
    
    public function getReportByOperator($start, $end, $departmentid = null, $locale = null) {
  	    return $this->getReportLostOrInterceptedByOperator($start, $end, false, $departmentid, $locale);
    }
    
    protected function getReportLostOrInterceptedByOperator($start, $end, $intercepted = false, $departmentid = null, $locale = null) {
    	$afrom = "";
	    $awhere = "";
	    $params = array("p_start" => $start, "p_end" => $end);
	    
	    
	    if($departmentid !== null || $locale !== null) {
	       $afrom = '
	       			LEFT JOIN
	       				"{operatorlastaccess}" o
	       			ON
	       				lv."operatorid" = o."operatorid"
	       		 '; 
	       if($departmentid !== null) {
	       	
	           $awhere .= '
	           				AND lv."operatorid" IN (
	           					SELECT "operatorid" 
	           					FROM "{operatordepartment}" 
	           					WHERE "departmentid" = :departmentid
	           				)
	           			';
	           $params['departmentid'] = $departmentid;
	       }
	       
	      if($locale !== null) {
 	         $awhere .= 'AND (o."locales" LIKE :locale OR o."locales" IS NULL)';
	         $params['locale'] = '%'.$locale.'%';
	       }
		
	    }
	    
    	$sql = 'SELECT
                    lv."operatorid" "opid",
                	COUNT(lv."id") "lost_vistors_count",
                	AVG(lv."waittime") "avg_waittime"
				FROM 
					"{lostvisitor}" lv
				LEFT JOIN 
					"{thread}" t
				ON
					t."threadid" = lv."threadid"
				' . $afrom . '
				WHERE
					WM_UNIX_TIMESTAMP(t."created") >= :p_start
				AND 
					WM_UNIX_TIMESTAMP(t."created") < :p_end
				AND 
					lv."anotheroperatorid" IS ' . ($intercepted ? "NOT" : "").' NULL
				' . $awhere . '
				GROUP BY lv."operatorid"';
    	      
        try {
          $this->db->Query($sql, $params);
          $result = array();
       	  while($this->db->nextRecord()) {
       	    $row = $this->db->getRow();
       	    $result[$row['opid']] = $row;
       	    $result[$row['opid']]['avg_waittime_str'] =  ((int)($result[$row['opid']]['avg_waittime'] / 60)) . ":". ($result[$row['opid']]['avg_waittime'] % 60);
       	  }
       } catch (Exception $e) {

       }
        
       $query = 'SELECT lv."operatorid" FROM "{operatorlastaccess}" lv '.$afrom.' WHERE 1=1 ' . $awhere;
       
       try{
       	$this->db->Query($query, array_slice($params, 2, 2));
       	$operators = array();
       	while($this->db->nextRecord()) {
       		$row = $this->db->getRow();
       		$operators[$row['operatorid']] = null;
       	}
       } catch (Exception $e) {

       }
       
  	   if(count($operators) == 0) {
  	   	return;
  	   }
  	   
       foreach (array_keys($operators) as $id) {
  	     $operator = Operator::getInstance()->GetOperatorById($id);  
  	     
  	     if(!isset($result[$id])) {
  	       $result[$id] = array(
  	        "opid" => $id,
  	       	"lost_vistors_count" => 0,
  	       	"avg_waittime" => 0,
  	        "dispersion" => 0,
  	        "avg_waittime" => 0,
  	        "st_deviation" => 0
  	       );
  	     }
  	     
  	     $result[$id]['name'] = $operator['fullname'];
  	     
  	   }
  	  
        $sql = 'SELECT
                    lv."operatorid" "opid",
                	lv."waittime"
				FROM 
					"{lostvisitor}" lv
				LEFT JOIN 
					"{thread}" t
				ON
					t."threadid" = lv."threadid"
				WHERE
					WM_UNIX_TIMESTAMP(t."created") >= :p_start
				AND 
					WM_UNIX_TIMESTAMP(t."created") < :p_end
				AND 
					lv."anotheroperatorid" IS ' . ($intercepted ? "NOT" : "") . ' NULL
				';
      try {
          $this->db->Query($sql, array("p_start" => $start, "p_end" => $end));
          
       	  while($this->db->nextRecord()) {
       	    $row = $this->db->getRow();
       	    
       	    if($result[$row['opid']]['lost_vistors_count'] == 0) {
       	      continue;
       	    }
       	    
       	    if(!isset($result[$row['opid']]['dispersion'])) {
       	      $result[$row['opid']]['dispersion'] = 0;
       	    }
       	    
       	    $result[$row['opid']]['dispersion'] += ($result[$row['opid']]['avg_waittime'] - $row['waittime'])
       	      * ($result[$row['opid']]['avg_waittime'] - $row['waittime']) 
       	      / $result[$row['opid']]['lost_vistors_count'];
       	    
       	    $result[$row['opid']]['st_deviation'] = sprintf("%.2f", sqrt($result[$row['opid']]['dispersion']));
       	  
       	  }
       } catch (Exception $e) {

       }
       
       return $result;
    }
    
}
?>