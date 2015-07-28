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
	    $params = array($start, $end);
	    
	    
	    if($departmentid !== null || $locale !== null) {
	       $afrom = "
	       			LEFT JOIN
	       				{operatorlastaccess} as ola
	       			ON
	       				lv.operatorid = ola.operatorid
	       		 "; 
	       if($departmentid !== null) {
	       	
	           $awhere .= "
	           				AND lv.operatorid IN (
	           					SELECT operatorid 
	           					FROM {operatordepartment} 
	           					WHERE departmentid = ?
	           				)
	           			";
	           $params[] = $departmentid;
	       }
	       
	      if($locale !== null) {
 	         $awhere .= "AND (ola.locales LIKE ? OR ola.locales IS NULL)";
	         $params[] = '%'.$locale.'%';
	       }
		
	    }
	    
    	$sql = "SELECT
                    lv.operatorid as opid,
                	COUNT(lv.id) as lost_vistors_count,
                	AVG(lv.waittime) as avg_waittime
				FROM 
					{lostvisitor} as lv
				LEFT JOIN 
					{thread} as t
				ON
					t.threadid = lv.threadid
				$afrom
				WHERE
					unix_timestamp(t.created) >= ? 	
				AND 
					unix_timestamp(t.created) < ?
				AND 
					lv.anotheroperatorid IS ".($intercepted ? "NOT" : "")." NULL
				$awhere
				GROUP BY opid";
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
        
       $query = "SELECT lv.operatorid FROM {operatorlastaccess} as lv $afrom WHERE 1 $awhere";
       
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
  	  
        $sql = "SELECT
                    lv.operatorid as opid,
                	lv.waittime
				FROM 
					{lostvisitor} as lv
				LEFT JOIN 
					{thread} as t
				ON
					t.threadid = lv.threadid
				$afrom
				WHERE
					unix_timestamp(t.created) >= ?
				AND 
					unix_timestamp(t.created) < ?
				AND 
					lv.anotheroperatorid IS ".($intercepted ? "NOT" : "")." NULL
				$awhere
				";
      try {
          $this->db->Query($sql, $params);
          
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