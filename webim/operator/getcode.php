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

require_once('../classes/functions.php');
require_once('../classes/class.smartyclass.php');
require_once('../classes/class.operator.php');
require_once('../classes/class.settings.php');
require_once('../classes/class.button.php');

Operator::getInstance()->IsCurrentUserAdminOrRedirect();

$TML = new SmartyClass();

$image = verify_param("image", "/^[\w\.]+$/", "webim");
$theme = verify_param("theme", "/^\w+$/", "default");

$TML->assign('theme', $theme);

//$TML->assign('availableImages', Button::enumAvailableImages(WEBIM_CURRENT_LOCALE));
//$TML->assign('availableThemes', enumAvailableThemes());
//$TML->assign('availableDepartments', MapperFactory::getMapper("Department")->enumDepartments(Resources::getCurrentLocale()));
$TML->assign('params', Button::getParameters());

$lang = Resources::getCurrentLocale();

$showhost =    verify_param("include_host_url", "/^y$/", "") == "y" ;
$includeTracker = verify_param("add_track_code", "/^y$/", "") == "y";
$forcesecure = verify_param("secure", "/^y$/", "") == "y";
$chooseoperator = verify_param("choose_operator", "/^\w+$/", "");
$chatimmediately = verify_param("chat_immediately", "/^y$/", "") == "y";
$departmentkey = verify_param("department_key", "/^\w+$/");
$choosedepartment = verify_param("choose_department", "/^y$/", "") == "y";
$locale = verify_param("locale", "/^([a-z]{2})$/", Resources::getCurrentLocale());



$size = array();   
if (function_exists('gd_info')) { // TODO: for other file types
  $info = gd_info();
  $filename = dirname(__FILE__)."/../".Button::getImageNameFromParam($image, null, $lang, null, true);
  if (file_exists($filename)) { // isset($info['GIF Read Support']) && $info['GIF Read Support'] && TODO check other file types
    $size = @getimagesize ($filename);
  }
}

$location = WEBIM_ROOT;
if ($showhost) {
  $location =($forcesecure ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . WEBIM_ROOT;
}

$alt = Resources::Get('webim.online.consultant');
$departmentParam = !empty($departmentkey) ? "&departmentkey=".$departmentkey : "";

$button_img = '<img alt="'.$alt.'" src="'.$location.'/button.php?bim='.$image.'&amp;lang='.$locale.$departmentParam.'" border="0"';
if (!empty($size)) {
  $button_img .=  ' width="'.$size[0].'" height="'.$size[1].'" ';
}
$button_img .= ' />'; 
  $chooseOperatorParam = !empty($chooseoperator) ? "&chooseoperator=".$chooseoperator : "";
$chooseDepartmentParam = !empty($choosedepartment) ? "&choosedepartment=1" : "";
$chatimmediatelyParam = !empty($chatimmediately) ? "&chatimmediately=1" : "";
$link = $location."/client.php?theme=$theme"."&amp;lang=".$locale.$chooseOperatorParam.$chooseDepartmentParam.$departmentParam.$chatimmediatelyParam;
$temp = get_popup($link, $button_img, "", "webim_".getWindowNameSuffix(), "toolbar=0, scrollbars=0, location=0, menubar=0, width=540, height=480, resizable=1", empty($chatimmediately));
$buttonCode = "<!-- webim button -->".$temp."<!-- /webim button -->";

$trackerCode = getTrackerCode($location, $theme, $forcesecure);

$buttonCode .= getAutoInviteCode($location, $theme);

$code = $includeTracker ? $trackerCode.$buttonCode : $buttonCode;

$TML->assign('code', htmlspecialchars($code));
$TML->assign('code_raw', $code);
$TML->assign('image', $image);


Browser::SendHtmlHeaders();
$TML->display('gen_button.tpl');

?>