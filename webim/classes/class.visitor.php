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
require_once('common.php');
require_once('models/generic/class.mapperfactory.php');
require_once('functions.php');

class Visitor  {
    private static $instance = NULL;

    static function getInstance() {
        if (self::$instance == NULL) {
            self::$instance = new Visitor();
        }
        return self::$instance;
    }

    private function __construct() {

    }

    private function __clone() {
    }

    public function canVisitorChangeName() {
     

        
        return true;
    
    }

    public  function getEmail($threadid = false) {
        if(!$threadid) return '';
        $firstMessage = MapperFactory::getMapper("Message")->getFirstMessage($threadid);
        if(sizeof($firstMessage) == 0) return '';
        preg_match("/mail:.*?(\S*?@\S*?\.\S*)/mix", $firstMessage['message'], $find);
        if($find[1]) return $find[1];
        
        return '';
    }

    public function getPhone() {
         
        return '';
    }

  public function setVisitorNameCookie($visitorName) {

    setcookie(WEBIM_COOKIE_VISITOR_NAME, $visitorName, time()+60*60*24*365, '/');
  }


}

?>
