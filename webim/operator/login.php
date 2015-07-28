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
$TITLE_KEY = 'page_login.title';

require_once('../classes/functions.php');
require_once('../classes/common.php');
require_once('../classes/class.smartyclass.php');
require_once('../classes/class.operator.php');
require_once('../classes/class.browser.php');

$TML = new SmartyClass($TITLE_KEY);

$errors = array();

if (isset($_REQUEST['login']) && isset($_REQUEST['password'])) {
  $login = get_mandatory_param('login');
  $password = get_mandatory_param('password');
  $remember = isset($_REQUEST['isRemember']) && $_REQUEST['isRemember'] == "on";
  $e = Operator::getInstance()->DoLogin($login, $password, $remember);

  if (isset($e)) {
    $errors[] = $e;
  }

  if (empty($errors)) {
    if (!empty($_REQUEST['redir'])) {
      header("Location: ". $_REQUEST['redir']);
    } else {
      header("Location: ".WEBIM_ROOT."/");
    }
    exit;
  }
}

$TML->assign('errors', $errors);
$TML->assign('isRemember', true);

if (!empty($_REQUEST['redir'])) {
  $TML->assign('redir', htmlspecialchars($_REQUEST['redir']));
}


$status  = verify_param("status", "/^(new)$/", "");
if ($status == "new") {
  $introduction = "true";
  $TML->assign('introduction', $introduction);
}


$TML->display('../templates/login.tpl');

?>