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
require_once dirname(__FILE__) . '/class.dbdriver.php';


class DBDriverMysql implements DBDriver {
  
  protected $lid = 0;
  protected $qid = 0;
  protected $records = array();
  
  protected $row = 0;
  protected $errno = 0;
  protected $error = "";
  protected static $query_count = 0;


  public function __construct($link = null) {
     if($link) {
	   $this->lid = $link;
     } else {
       $this->connect();
     }
  }
  
  public function getArrayOfRows() {
    $result = array(); 
    while ($this->nextRecord()) {
        $result[] = $this->getRow();
    }

    return $result;
  }

  public function getEscapedString($str) {
    if(is_array($str)) {
      $new_str = array();
      foreach($str as $key => $value) {
        $new_str[$key] = $this->getEscapedString($value);
      }
      return $new_str;
    }
    
    $new_str = mysql_real_escape_string($str, $this->lid);
    
    return $new_str;
  }
  
  protected function connect() {
    if($this->lid == 0) {
      $this->lid = @mysql_pconnect(SITE_DB_HOST, SITE_DB_USER, SITE_DB_PASSWORD);
      if(! $this->lid) {
        throw new Exception("Couldn't connect to server " . SITE_DB_HOST, $this->errno);
      } //if
      if(! mysql_query("use `" . SITE_DB_NAME . "`", $this->lid)) {
        $this->errno = @mysql_errno($this->lid);
        $this->error = @mysql_error($this->lid);
        throw new Exception("Couldn't use database " . SITE_DB_NAME. ". MySQL says: (" . $this->errno . ") " . $this->error, $this->errno);
      } //if
     
      @mysql_query("set character_set_client=" . SITE_DB_CHARSET, $this->lid);
      @mysql_query("set character_set_connection=" . SITE_DB_CHARSET, $this->lid);
      @mysql_query("set collation_connection=" . SITE_DB_COLLATION, $this->lid);
      @mysql_query("set character_set_results=" . SITE_DB_CHARSET, $this->lid);
    }
  }
  
  public function Query($query_string, $params = null) {
    self::$query_count ++;
    $query = $this->prepareSQL($query_string, $params);
    $this->qid = @mysql_query($query, $this->lid);
    $this->row = 0;
    $this->errno = @mysql_errno($this->lid);
    $this->error = @mysql_error($this->lid);
    
    if(! $this->qid) {

      throw new Exception("Invalid query: " . $query . ". MySQL says: (" . $this->errno . ") " . $this->error, $this->errno);
    } //if
    
    return ($this->qid);
  } // query()

  protected function prepareSQL($sql, $params) {
    $sql = $this->convertTablesNames($sql);
    
    if($params === null) {
      return $sql; 
    }
    
    if (!is_array($params)) {
      $params = array($params);
    }
    
    $positional_params = array();
    
    $named_params = array("names" => array(), "values" => array());
    
    
    foreach ($params as $k => $v) {
      switch(gettype($v)) {
        case 'NULL':
          $v = 'NULL';
          break;
        case 'integer':  // TODO figure out
           break;
        case 'boolean':
          $v = $v ? 'TRUE' : 'FALSE';
           break;
        default:
          $v = "'".$this->getEscapedString($v)."'";
      }
      
      if(is_int($k)) {
        $positional_params[] = $v;
      } else {
        $named_params['names'][] = ":".$k;
        $named_params['values'][] = $v;
      }
    }
    
    $result = $sql;
      
    if(count($positional_params) > 0) {
      $tmp = explode("?", $result);
      
      if((count($tmp) - count($positional_params)) != 1) {
        throw new Exception("Wrong paramter count for query $sql");
      }
      
      $result = "";
      foreach ($tmp as $k => $v) {
        $result .= $v;    
        if(isset($positional_params[$k])) {
          $result .= $positional_params[$k];
        }
      }
    }
    
    if(count($named_params['names']) > 0) {
      $result = str_replace($named_params['names'], $named_params['values'], $result);
    }
    

    
    return $result;
  }
  
  protected function convertTablesNames($sql) {
    return preg_replace("/{([a-zA-Z0-9\_]+)}/", SITE_DB_TABLE_PREFIX."$1", $sql);
  }
  
  public function execArrayOfQuerys($querys) {
    for($i = 0; $i < sizeof($querys); $i ++) {
      try {
        $this->Query($querys[$i]);
      } catch(Exception $e) {
        throw $e;
      }
    }
  }

  public function nextRecord() {
    if($this->qid == 0) {
      return (false);
    }
    $this->records = mysql_fetch_array($this->qid, MYSQL_ASSOC);
    $this->row ++;
    $this->errno = mysql_errno();
    $this->error = mysql_error();
    
    if(! is_array($this->records)) {
      mysql_free_result($this->qid);
      $this->qid = 0;
    } //if
    

    return (is_array($this->records));
  } // next_record()
  
  public function getRow($field = '') {
    if(empty($field))
      return $this->records;
    elseif(is_array($field)) {
      $result = array();
      foreach($field as $fieldname) {
        $result[$fieldname] = $this->records[$fieldname];
      }

      return $result;
    } else

      return $this->records[$field];
  }

  public function getNumRows() {
    if($this->qid) {
      $r = @mysql_num_rows($this->qid);
      if($r > 0) {
        return $r;
      }
      return @mysql_affected_rows($this->lid);
    } else {
      return 0;
    }
  } // num_rows()

  public function getInsertId() {
    //                $this->connect(); 
    return @mysql_insert_id($this->lid);
  } // num_rows()

  protected function close() {
    
    // don't close any db connection for bitrix
    @mysql_close($this->lid);
    
    $this->lid = 0;
  } // close()

  public function __destruct() {
    $this->close();
  }

  public static function getQueryCount() {
    return self::$query_count;
  }

} // class DBMySQL


?>
