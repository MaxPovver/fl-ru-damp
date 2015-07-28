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

class Settings  {
  private $settings = null;


  protected $tableName = 'chatconfig';
  protected $uniqueTableKey = 'configid';
  private static $instance = NULL;

  static function getInstance() {
    if (self::$instance == NULL) {
      self::$instance = new Settings();
    }
    return self::$instance;
  }

  private function __construct() {
      
  }

  private function __clone() {
  }

  static function Get($key, $defaultValue = null) {
    $res = self::getInstance()->_get($key);
    
    if (isset($defaultValue) && empty($res)) {
      $res = $defaultValue;
    }
    
    return $res;
  }

  public function Set($key, $value) {
    MapperFactory::getMapper("Config")->save(array(
    	'configkey' => $key, 
    	'configvalue' => $value
      )
    );
  }

  private function _get($key) {
     

    
    $this->ensureLoaded();
    return isset($this->settings[$key]) ? $this->settings[$key] : null;
    
  }

  public function GetAll() {
  
    $this->ensureLoaded();

    return $this->settings;
  
   
  }
  
  
  private function ensureLoaded() {
    if (!isset($this->settings)) {
      $this->settings = MapperFactory::getMapper("Config")->enumPairs();
    }
  }
  

   // need to setup config
//  function LoadSettingsPro() {
//    foreach (Resources::GetAvailableLocales() as $locale) {
//      $answers = Resources::Get("chat.predefined_answers", array(), $locale);
//      $this->Settings["answers_".$locale] = $answers;
//    }
//    return true;
//  }
  
  //====================================================================================================
  

  static function GetProductName() {
     
    
    $product = Resources::Get('webim.pro.title');
    
    return $product;
  }

  static function GetProduct() {
     
    
    $product = 'pro';
    
    return $product;
  }

  static function GetProductURL() {
     
    
    $url = 'http://webim.ru/pro/?p=pro';
    
    return $url;
  }


  static function GetProductAndVersion() {
    return Settings::GetProductName() ." ". WEBIM_VERSION;
  }



}
?>
