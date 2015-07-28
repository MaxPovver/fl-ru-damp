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
        $result .= "unix_timestamp(`$c`) as `$c`";
      } else {
      	$result .= "unix_timestamp(`$table_alias`.`$c`) as `$c`";
      }
    }
    
    return $result;
  }

  
  public function getById($id, $select = null) {
    $r = $this->makeSearch("{$this->id_column}=?", $id, $select, 1);
    return array_shift($r);
  }

  public function getAll($limit = null, $offset = null, $total = null, $orderby = null) {
    return $this->makeSearch(null, null, $limit, $offset, $total, $orderby);
  }

  
  public function makeSearch($where = null, $params = null, $select = null, $limit = null, $offset = null, $total = null, $orderby = null) {
    
    $query = "SELECT ";
    
    if($select === null) {
      $query .= "*";
      if(count($this->date_columns_names) > 0) {
        $query .= ", ".$this->getDateColumnsSql();
      }
    } else {
      $query .= $select;  
    }
    
    $query .= " FROM `{" . $this->table . "}` ";
    
    $query_add = "";
    if($where != null) {
      $query_add .= "WHERE ".self::escapeBraces($where);
    }
    
    $query .= $query_add;
    
    if(is_array($orderby) && count($orderby)) {
      $query .= " ORDER BY " . $orderby[0] . " " . $orderby[1];
    }
    
    if($limit !== null) {
      $query .= " LIMIT " . $limit;
      if($offset !== null) {
        $query .= " OFFSET " . $offset;
      }
    }
    
    
    try {
      	$this->db->Query($query, $params);
    } catch (Exception $e) {

        return array();
    }
    
    $result = $this->db->getArrayOfRows(); 
    
    if($total !== null) {
      $query = "SELECT COUNT(`{$this->id_column}`) as `total` FROM `{" . $this->table . "}` " . $query_add;
    
      try {
      	$this->db->Query($query, $params);
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
    foreach ($data as $k => $d) {      
      if(!$first) {
        $fields .= ", ";
        $values .= ", ";
      }
      
      $fields .= "`" . $k . "`";
      
      if($d === TRUE) {
        $d = '1';
      } else if($d === FALSE) {
        $d = '0';
      } else if($d === null) {
        $d = "NULL";
      } else {
        $d = "'" . $this->db->getEscapedString($d) . "'";
      }
      
      $values .= $d;
      
      if(!$this->autoincrement) {
           if(!$first) {
             $update .= ", ";
           }
           $update .= "`$k`=$d";
      }
      
      $first = false;
    }    
    
    $query = "INSERT INTO `{" . $this->table . "}` (" . $fields . ") VALUES (" . self::escapeBraces($values) . ")";
    if(!$this->autoincrement) {
      $query .= " ON DUPLICATE KEY UPDATE $update";
      
    }
   
    $this->db->Query($query);
    
    return $this->db->getInsertId();
  }

  protected function update($data) {
    $update = "";
    $first = true;
    
    foreach($data as $k => $d) {
      if($k == $this->id_column) {
        continue;
      }
      
      if(! $first) {
        $update .= ", ";
      }
      
      if($d === TRUE) {
        $d = '1';
      } else if($d === FALSE) {
        $d = '0';
      } else if($d === null) {
        $d = "NULL";
      } else {
        $d = "'" . $this->db->getEscapedString($d) . "'";
      }
      
      $update .= "`" . $k . "` = " . self::escapeBraces($d);
      $first = false;
    }
    
    $query = "UPDATE `{" . $this->table . "}` SET " . $update . " WHERE `{$this->id_column}` = '" . $this->db->getEscapedString($data[$this->id_column]) . "'";
    
    $this->db->Query($query);
    
    return $this->db->getNumRows();
  }

  public function delete($id) {
    $query = "DELETE FROM `{" . $this->table . "}` WHERE `{$this->id_column}` = '" . $this->db->getEscapedString($id) . "'";
    
    $this->db->Query($query);
    
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