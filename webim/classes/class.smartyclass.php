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

 

define('SMARTY_DIR', $_SERVER['DOCUMENT_ROOT'].WEBIM_ROOT.'/classes/smarty/libs/');


require_once(SMARTY_DIR.'Smarty.class.php');
require_once('class.browser.php');
require_once('class.settings.php');
require_once('class.operator.php');

class SmartyClass extends Smarty {
  function SmartyClass($titleKey = null) {
    $this->Smarty();

    
    $this->template_dir = array();
    $this->template_dir[] = $_SERVER['DOCUMENT_ROOT'].WEBIM_ROOT.'/templates';
    $this->plugins_dir[] = $_SERVER['DOCUMENT_ROOT'].WEBIM_ROOT.'/templates/.plugins';
    $this->trusted_dir[] = $_SERVER['DOCUMENT_ROOT'].WEBIM_ROOT.'/classes/function';
    

     
    $this->template_dir[] = $_SERVER['DOCUMENT_ROOT'].WEBIM_ROOT.'/themes/'.Browser::getCurrentTheme().'/templates';

    $this->compile_dir = $_SERVER['DOCUMENT_ROOT'].WEBIM_ROOT.'/compiles';
    $this->left_delimiter = '<!--{';
    $this->right_delimiter = '}-->';
    $this->debugging = false;

    $this->force_compile = true;
    
    if (!empty($titleKey)) {
      $this->assign('title_key', $titleKey);
      $this->assign('title', Resources::Get($titleKey));
    }

  }

  function display($path) {

    $this->assign('current_locale', WEBIM_CURRENT_LOCALE, false);
    $this->assign('available_locales', Resources::GetAvailableLocales(), false);
    $this->assign('webim_root', WEBIM_ROOT, false);
    $this->assign('whois_url', WEBIM_WHOIS_LINK, false);
    $this->assign('browser_charset', BROWSER_CHARSET, false);
//    $this->assign('resources', Resources::GetCurrentSet(), false); TODO do we really need this?
    $this->assign('product_and_version', Settings::GetProductAndVersion());
    $this->assign('product_url', Settings::GetProductURL());
    $this->assign('version', WEBIM_VERSION);

    $op = SilentGetOperator();
    if (isset($op)) {
      $this->Assign('operator_name', $op['fullname']);
    }

    
    Browser::SendHtmlHeaders();
    


    parent::display($path);
  }

  function assignCompanyInfoAndTheme() {
    $this->assign('url', Settings::Get('hosturl', Resources::Get('site.url')));
    $this->assign('company', Settings::Get('company_name', Resources::Get('company.webim')));
    $this->assign('logo', Settings::Get('logo', WEBIM_ROOT.'/themes/default/images/logo.gif'));
    $this->assign('theme', Browser::getCurrentTheme());
  }

}

?>