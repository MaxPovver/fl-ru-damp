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

class GeoIPLookup {
	private static $haveCngeoip = null;
	private static $defaultLocale = "en";
	private static $defaultEncoding = "UTF-8";
	 
	
	private static $indexesForLocales = array (
		"ru" => array(0, 5),
		"en" => array(1, 6)
	);
	
	private static $functionsForEncodings = array (
		"UTF-8" => "cngeoip_lookup_ip", 
		"CP1251" => "cngeoip_lookup_ip_cp1251"
	);
	
	private function __construct() {}
	
	public static function getGeoDataByIP($ip) {
		self::initGeoIP();
		
		if(self::$haveCngeoip) {
			if(!isset(self::$indexesForLocales[WEBIM_CURRENT_LOCALE])) {
				$locale = self::$defaultLocale;
			} else {
				$locale = WEBIM_CURRENT_LOCALE;
			}
			
			if(!isset(self::$functionsForEncodings[WEBIM_ENCODING])) {
				$encoding = self::$defaultEncoding;	
			} else {
				$encoding = WEBIM_ENCODING;
			}
			


			$data = call_user_func(self::$functionsForEncodings[$encoding], $ip);
	

			if(empty($data[0])) {

				return null;
			}
	
	        if($encoding != WEBIM_ENCODING) {

	          foreach ($data as $key => $value) { 
	            $data[$key] = smarticonv($encoding, WEBIM_ENCODING, $value);
	          }
	        }
	    
	        $result = array ("city" => $data[self::$indexesForLocales[$locale][0]], 
			    "country" => $data[self::$indexesForLocales[$locale][1]],
				"lat" => $data[3],
				"lng" => $data[4]
			);
	

			
			return $result;
		}
		 
		return null;
	}
	
  private static function initGeoIP() {
     

    if (self::$haveCngeoip === null) {

      $cngeoip_dir = $_SERVER['DOCUMENT_ROOT'] . WEBIM_ROOT . "/cngeoip";


      if(file_exists($cngeoip_dir)) {

        self::$haveCngeoip = true;

        if(!include_once($cngeoip_dir."/cngeoip.php")) {
          self::$haveCngeoip = false;

        }
      } else {

        self::$haveCngeoip = false;
      }
    }
  }
}

?>
