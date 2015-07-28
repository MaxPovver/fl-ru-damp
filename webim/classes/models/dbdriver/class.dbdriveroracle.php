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


class DBDriverOracle implements DBDriver {
  
  protected $lid = 0;
  protected $qid = 0;
  protected $records = array();
  
  protected $row = 0;
  protected $errno = 0;
  protected $error = "";
  protected static $query_count = 0;
  protected $selected_rows = null;
  protected $current_row = 0;
  
  public function __construct($link = null) {
     if($link) {
	   $this->lid = $link;
     } else {
       $this->connect();
     }
  }
  
  public function getArrayOfRows() {
    

    $result = $this->records;
    unset($this->records);  	
    return $result;
  }

  public function getEscapedString($str) {
    throw new Exception("Not implemented");
  }
  
  protected function connect() {
    if($this->lid == 0) {
      $charset = SITE_DB_CHARSET == 'cp1251' ? 'CL8MSWIN1251' : 'AL32UTF8';
      $this->lid = @oci_pconnect(SITE_DB_USER, SITE_DB_PASSWORD, SITE_DB_HOST, $charset);
      if(! $this->lid) {
        throw new Exception("Couldn't connect to server " . SITE_DB_HOST);
      } //if
    }
  } 
  
  public function Query($query_string, $params = null) {
    self::$query_count ++;


    
    $this->qid = $this->prepareSQL($query_string, $params);
    $result = oci_execute($this->qid);
    $this->row = 0;
    $this->selected_rows = null;
    $err = oci_error($this->qid);
    $this->error = $err['message'];
    $this->errno = $err['code'];

    if(oci_statement_type($this->qid) == "SELECT") {
       $this->records = array();
       $this->selected_rows = 0;
       $this->current_row = 0;
       while ($row = @oci_fetch_assoc($this->qid)) {
         $this->records[] = $row;
         $this->selected_rows++;
       } 
    }
    
    if(! $result) {

      throw new Exception("Invalid query: " . $query_string . ": " . $this->error, $this->errno);
    } //if
    
    return $result;
  } // query()

  protected function prepareSQL(&$sql, $params) {
    $sql = $this->convertTablesNames($sql);
    $stmt = @oci_parse($this->lid, $sql);
 
    if($params === null || !is_array($params)) {
      return $stmt;
    }
 
    foreach ($params as $k => $v) {
      //Need to bind params to array[k] cause oracle binds to variables by ptr so if variable value is chaged we got an error 
	 
      $result = @oci_bind_by_name($stmt, ":$k", $params[$k], -1); //TODO length of variables must be preserved


      if(!$result) {
        $err = oci_error($stmt);
        $this->error = $err['message'];
        $this->errno = $err['code'];
        throw new Exception("Unable to bind param $k => $v: ". $this->error, $this->errno);
      }
    }
    
    return $stmt;
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
    
    if(!is_array($this->records) || count($this->records) < 1) {
      return false;
    }

    if($this->current_row++ == 0 && current($this->records) !== FALSE) {
      return true;
    }
    
    $result = next($this->records);
   
    if($result === FALSE) {
      unset($this->qid);
      unset($this->records);
      return false;
    } //if
    
   
    return true;
  } // next_record()
  
  public function getRow($field = null) {
    $row = current($this->records);

    if(empty($field)) {
      return $row;
    } elseif(is_array($field)) {
      $result = array();
      foreach($field as $fieldname) {
        $result[$fieldname] = $row[$fieldname];
      }

      return $result;
    } else {

      return $row[$field];
    }
  }

  public function getNumRows() {
    if($this->selected_rows !== null) {
      return $this->selected_rows;
    }
    
    $r = oci_num_rows($this->qid);
    if($r !== false) {
      return $r;
    }
    
    return 0;
  } // num_rows()

  public function getInsertId() {
    throw new Exception("Not Implemented");
  } 

  public function close() {
    
    // don't close any db connection for bitrix
    @oci_close($this->lid);
    
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
