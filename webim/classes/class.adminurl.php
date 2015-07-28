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
class AdminURL {
  private static $instance = NULL;
  
  public static $ADMIN_MENU = array(
    array('name' => 'topMenu.visitors', 'link_name' => 'visitors', 'description' => 'page_client.pending_visitors', 'title' => 'page_client.pending_visitors.title', 'role' => 'operator', 'cssclass'=>'b-awaiting'),
    array('name' => 'leftMenu.client_visitors', 'link_name' => 'tracker', 'description' => 'admin.content.client_visitors', 'role' => 'operator', 'cssclass'=>'b-visitors'),
    array('name' => 'page_analysis.search.title', 'link_name' => 'history', 'description' => 'content.history', 'role' => 'operator', 'more' => array('adv_history')),
    array('name' => 'statistics.title', 'link_name' => 'statistics', 'description' => 'statistics.description', 'role' => 'admin'),
    array('name' => 'menu.blocked', 'link_name' => 'blocked', 'description' => 'content.blocked', 'role' => 'operator', 'more' => array('ban')),
    array('name' => 'leftMenu.auto_invites', 'link_name' => 'auto_invites', 'description' => 'admin.content.auto_invites', 'role' => 'admin', 'more' => array('auto_invite')), 
    array('name' => 'leftMenu.departments', 'link_name' => 'departments', 'description' => 'page_departments.intro', 'role' => 'admin', 'more' => array('department')),
  
    array('name' => 'leftMenu.client_gen_button', 'link_name' => 'getcode', 'description' => 'admin.content.client_gen_button', 'role' => 'admin'),
    array('name' => 'leftMenu.client_agents', 'link_name' => 'operators', 'description' => 'admin.content.client_agents', 'role' => 'admin'),
    array('name' => 'leftMenu.client_settings', 'link_name' => 'settings', 'description' => 'admin.content.client_settings', 'role' => 'admin'),
    array('name' => 'topMenu.logoff', 'link_name' => 'logout', 'description' => 'content.logoff', 'role' => 'operator'),

  );
  

  public static function getInstance() {
    if (self::$instance == NULL) {
      self::$instance = new AdminURL();
    }
    return self::$instance;
  }

  private function __construct() {
  }
  
  private function __clone() {
  }
  
  public function getURL($name, $lang = NULL, $isWithParamPostfix = NULL) {
     


    if ($lang === NULL) {
      $langParam = '';
      $postfix = $isWithParamPostfix ? '?' : '';
    } else {
      $langParam = '?lang='.$lang;
      $postfix = $isWithParamPostfix ? '&' : '';
    }   

     
    
    return WEBIM_ROOT.'/operator/'.$name.'.php'.$langParam.$postfix;
    
  }
  
}
?>
