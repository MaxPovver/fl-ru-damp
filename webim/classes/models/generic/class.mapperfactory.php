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

class MapperFactory {
  protected static $mappers = array();
  
  protected static $db = null;

  protected function __construct() {}

  static public function getMapper($model_class) {
     
    if(! isset(self::$mappers[$model_class])) {
      
      $mapper_class = $model_class . "Mapper";
      
        $include_file = dirname(__FILE__) . "/../" . strtolower(SITE_DB_TYPE) . "/class." . strtolower($mapper_class) . ".php";
      
       
      if(! include_once ($include_file)) {
        throw new Exception("Cound't load mapper class $mapper_class file $include_file");
      }
      
      if(! self::$db) {
        
          $class = "DBDriver" . ucfirst(SITE_DB_TYPE);
        
         
        $include_file = dirname(__FILE__) . "/../dbdriver/class." . strtolower($class) . ".php";
        
        if(! include_once ($include_file)) {
          throw new Exception("Couldn't load dbdriver " . $class . " file $include_file");
        }
        
        
        self::$db = new $class();
        
         
      }
      
      $mapper = new $mapper_class(self::$db, $model_class);
      self::$mappers[$model_class] = $mapper;
    }
    
    return self::$mappers[$model_class];
  }
}
?>