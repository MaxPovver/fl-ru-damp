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
$TITLE_KEY = 'leftMenu.client_settings';

require_once(dirname(__FILE__).'/inc/admin_prolog.php');

require_once('../classes/functions.php');
require_once('../classes/class.operator.php');
require_once('../classes/class.smartyclass.php');

$answersKey = 'answers_'.WEBIM_CURRENT_LOCALE;


Operator::getInstance()->IsCurrentUserAdminOrRedirect();

$TML = new SmartyClass($TITLE_KEY);

$errors = array();

if (isset($_REQUEST['dellogo'])) {
  Settings::getInstance()->Set('logo', '');  	
}

if (isset($_POST['submitted'])) {
  $fields = array('company_name', 'hosturl', $answersKey, 'superviser_email', 'from_email', 'offline_email', 'stats_email', 'max_sessions');
  $emails = array('stats_email' => 'settings.stats_email', 'superviser_email' => 'settings.superviser_email', 'from_email' => 'settings.from_email', 'offline_email' => 'settings.offline_email');
  $params = array();

  foreach ($fields as $key) {
    if (isset($_REQUEST[$key])) {
      $params[$key] = get_mandatory_param($key);
    }
  }


  foreach ($emails as $key => $res) {
      if (empty($params[$key])) {
        $errors[] = Resources::Get("errors.required", Resources::Get($res));
      } elseif($key == 'stats_email') {
         $stats_emails = array_map("trim", explode(",", $params[$key]));
         foreach ($stats_emails as $e) {
           if (!isValidEmail($e)) {
             $errors[] = Resources::Get("errors.email.format", Resources::Get($res));
             break;
           }  
         }
      }
      elseif (!isValidEmail($params[$key])) {
        $errors[] = Resources::Get("errors.email.format", Resources::Get($res));
      }
  }

  if (isset($params['max_sessions']) && notEmpty($params['max_sessions']) && (!is_numeric($params['max_sessions']) || ($params['max_sessions'] < 0))) {
  	$errors[] = Resources::Get("error.max_sessions.value");
  }
  
  if (empty($params['company_name'])) {
    $errors[] = Resources::Get("errors.required", array(Resources::Get('settings.company.webim')));
  }
  
  
  $requestFile = $_FILES['logo'];
  if (empty($errors) && isset($requestFile) && !empty($requestFile['name']) && $requestFile['size'] > 0 && $requestFile['error'] == 0) {
    $dir = "../images/logo/";
    $destFilename = "site_logo";
	$uploadResult = uploadFile($requestFile, $dir, $destFilename);
    if (!empty($uploadResult)) {
      $errors[] = $uploadResult;
    }
    if (empty($errors)) {
      $hash = array();
      $params['logo'] = WEBIM_ROOT . '/images/logo/' . constructFileNameFromUploadedFile($requestFile, $destFilename);
    }
  }
  

  if (empty($errors)) {
    
    $params[$answersKey] = remove_empty_strings($params[$answersKey]);
    
    foreach ($params as $key => $value) {
      Settings::getInstance()->Set($key, $value);
    }

    header("Location: " . WEBIM_ROOT . "/operator/settings.php");
    exit;
  } else {
    foreach ($fields as $f) {
      $TML->assign($f, $params[$f]);
    }
//    $TML->assign('logo', get_mandatory_param('logo'));
    $TML->assign('answers_value', $params[$answersKey]);
  }
} else { // not submitted
  $TML->assign('answers_value', Settings::Get($answersKey));
  $TML->assign(Settings::getInstance()->GetAll());
}

$TML->assign('answers_key', $answersKey);
$TML->assign('availableLocales', Resources::GetAvailableLocales());
$TML->assign('errors', $errors);

$TML->display('settings.tpl');

require_once(dirname(__FILE__).'/inc/admin_epilog.php');

function notEmpty($string) {
  return !empty($string);
}

function remove_empty_strings($strings) {
  $strings = str_replace("\r", "", $strings);

  $strarray = explode("\n", $strings);
  $strarray = array_filter($strarray , "notEmpty");
  return implode("\n", $strarray);
}


?>