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
abstract class BaseMapper {
  
  protected $db;
  protected $model_name;
  private $id_column;
  
  private $table;
  private $date_columns_names = array();
  private $autoincrement = true;
  
  public function __construct(DBDriver $db, $model_name, $date_columns_names = array(), $autoincrement = true, $id_column = null) {
    $this->db = $db;
    $this->model_name = $model_name;
    $this->table = strtolower($this->model_name);
    if($id_column !== null) {
      $this->id_column = $id_column;
    } else {
      $this->id_column =  $this->table . "id";
    }
   
    $this->date_columns_names = $date_columns_names;
    $this->autoincrement = $autoincrement;
  }

  protected function getDateColumnsNames() {
    return $this->date_columns_names;
  }

  protected function getDateColumnsSql($table_alias = null) {  
  	$result = "";
    foreach($this->date_columns_names as $k => $c) {
      if($k != 0) {
        $result .= ", ";
      }
      if(! $table_alias) {
        $result .= "WM_UNIX_TIMESTAMP(\"$c\") \"$c\"";
      } else {
      	$result .= "WM_UNIX_TIMESTAMP($table_alias.\"$c\") \"$c\"";
      }
    }
    
    return $result;
  }

  
  public function getById($id, $select = null) {
    $r = $this->makeSearch('"'.$this->id_column.'"=:id', array("id" => $id), $select, 1);
    return array_shift($r);
  }

  public function getAll($limit = null, $offset = null, $total = null, $orderby = null) {
    return $this->makeSearch(null, null, $limit, $offset, $total, $orderby);
  }

  
  public function makeSearch($where = null, $params = null, $select = null, $limit = null, $offset = null, $total = null, $orderby = null) {
    
    $query = "SELECT ";
    
    if($select === null) {
      $query .= "t.*";
      if(count($this->date_columns_names) > 0) {
      	$query .= ", ". $this->getDateColumnsSql();	
      }
      
    } else {
      $query .= $select;  
    }
    
    $query .= " FROM \"{" . $this->table . "}\" t ";
    
    $query_add = "";
    if($where != null) {
      $query_add .= "WHERE ".self::escapeBraces($where);
    }
    
    $query .= $query_add;
    
   	if($limit !== null) {
    	if($where === null) {	
    		$query .= "WHERE";
    	} else {
    		$query .= " AND";
    	}
    	
      	$query .= " rownum BETWEEN " .intval($offset);
      	$query .= " AND " .  $limit;
    }
  	
    if(is_array($orderby) && count($orderby)) {
      $query .= " ORDER BY " . $orderby[0] . " " . $orderby[1];
    }
    
    try {
      	$this->db->Query($query, $params);
    } catch (Exception $e) {

        return array();
    }
    
    $result = $this->db->getArrayOfRows(); 

    if($total !== null) {
      $query_total = "SELECT COUNT(\"{$this->id_column}\") as \"total\" FROM \"{" . $this->table . "}\" " . $query_add;
    
      try {
      	$this->db->Query($query_total, $params);
      } catch (Exception $e) {

        $total = 0;
      }
      
      $this->db->nextRecord();
      $row = $this->db->getRow();
      $total = $row['total'];
    }
   
    return $result;
  }

  public function save($data) {
  	if($this->autoincrement) {
  		if(isset($data[$this->id_column]) && ! empty($data[$this->id_column])) {  
      		return $this->update($data);
    	} 
      	return $this->add($data);
  	} else {
    	if(isset($data[$this->id_column]) && ! empty($data[$this->id_column])) {  
      		$r = $this->update($data);
      		if($r > 0) {
        		return true;
      		}
    	}
      	return $this->add($data);
    }
  }

  protected function add($data) {
    $fields = "";
    $values = "";
    $update = "";
    
    $first = true;
    $params = array();
    
    foreach ($data as $k => $d) {
      
      if(!$first) {
        $fields .= ", ";
        $values .= ", ";
      }
      $fields .= '"' . $k . '"';
           
      if(!in_array($k, $this->date_columns_names) || $d === null) {
        if(preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) {
          $values .= 'TO_DATE(:p_'.$k.', \'YYYY-MM-DD\')';
        } else {
          $values .= ':p_'.$k;
        }
      } else {
        $values .= 'TO_DATE(:p_' . $k . ', \'YYYY-MM-DD HH24:MI:SS\')'; //Convert string date in MySQL format to Oracle date
      }
      
      $params['p_'.$k] = $d; //Ensure that key is not oracle reserved word
        
//      if(!$this->autoincrement) {
//           if(!$first) {
//             $update .= ", ";
//           }
//           $update .= "\"$k\"=$d";
//      }
      
      $first = false;
    }    
    
    $query = 'INSERT INTO "{' . $this->table . '}" (' . $fields . ') VALUES (' . $values . ')';
//    if(!$this->autoincrement) {
//      $query .= " ON DUPLICATE KEY UPDATE $update";
//      
//    }
   
    $this->db->Query($query, $params);
    
    return $this->autoincrement ? $this->getLastSequenceValue("chat".$this->id_column."_seq") : $data[$this->id_column];
  }
  
  protected function getLastSequenceValue($sequence_name) {
  	$query = 'SELECT "'.$sequence_name.'".currval "last_val" FROM dual';
  	
  	$this->db->Query($query);
  	$this->db->nextRecord();
  	$row = $this->db->getRow();

  	return $row['last_val'];
  }
  
  protected function update($data) {
    $update = "";
    $first = true;
    $params = array();
    
    foreach($data as $k => $d) {
      if($k == $this->id_column) {
        continue;
      }
      
      if(! $first) {
        $update .= ", ";
      }
      
    if(!in_array($k, $this->date_columns_names) || $d === null) {
        if(preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) {
          $update .= '"' . $k . '" = TO_DATE(:p_' . $k . ', \'YYYY-MM-DD\')'; //Convert string date in MySQL format to Oracle date
        } else {
          $update .= '"' . $k . '" = :p_' . $k;  
        }
      } else {
        $update .= '"' . $k . '" = TO_DATE(:p_' . $k . ', \'YYYY-MM-DD HH24:MI:SS\')'; //Convert string date in MySQL format to Oracle date
      }
      
     
      $params['p_'.$k] = $d; //Ensure that key is not oracle reserved word
      $first = false;
    }
    
    $query = 'UPDATE "{' . $this->table . '}" SET ' . $update . ' WHERE "' . $this->id_column . '" = :p_' . $this->id_column;
    $params['p_' . $this->id_column] = $data[$this->id_column];
    $this->db->Query($query, $params);
    
    return $this->db->getNumRows();
  }

  public function delete($id) {
    $query = 'DELETE FROM "{' . $this->table . '}" WHERE "' . $this->id_column . '" = :id';
    
    $this->db->Query($query, array("id" => $id));
    
    return true; 
  }

  public function startTransaction() {
    $this->db->Query("START TRANSACTION;");
  }

  public function commit() {
    $this->db->Query("COMMIT;");
  }

  public function rollback() {
    $this->db->Query("ROLLBACK;");
  }

  public function getIdColumn() {
    return $this->id_column;
  }

  private static function escapeBraces($text) {
    return preg_replace("/{(.*?)}/", "\{\${1}\}", $text);
  }
  
  protected function getTableName() {
    return $this->table;
  }
}
?>