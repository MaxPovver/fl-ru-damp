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
require_once('../classes/class.operator.php');
require_once('../classes/class.smartyclass.php');


$operator = Operator::getInstance()->GetLoggedOperator();

$errors = array();

if (isset($_REQUEST['act'])) {
  switch ($_REQUEST['act']) {
    case 'delphoto':
      Operator::getInstance()->UpdateOperator($operator['operatorid'], array('avatar' => null));
      Operator::getInstance()->RefreshSessionOperator();
      header("Location: " . WEBIM_ROOT . "/operator/profile.php");
      break;
  }
} elseif (isset($_REQUEST['submitted'])) {
  operatorSubmitted();
} else {
  showOperator();
}

function showOperator() {
  $TML = new SmartyClass();
  $TML->assign('mode', 'profile');

  setOperator($TML);

  $TML->display('operator.tpl');
}

function setOperator($TML) {
  $op = Operator::getInstance()->GetLoggedOperator();



  $TML->assign($op);

}

function operatorSubmitted() {
  $operator = Operator::getInstance()->GetLoggedOperator();

  $TML = new SmartyClass();
  setOperator($TML);
  $TML->assign('mode', 'profile');

  $toCheck = array('login' => 'form.field.login', 'fullname' => 'form.field.agent_name', 'email' => 'form.field.agent_email');

  foreach ($toCheck as $field => $res) {
    if (empty($_REQUEST[$field])) {
      $errors[] = Resources::Get("errors.required", array(Resources::Get($res)));
    }
  }

  if(empty($errors) && !preg_match("/^[\w_]+$/", $_REQUEST['login'])) {
    $errors[] = Resources::Get("page_agent.error.wrong_login");
  }

  if ($_REQUEST['password'] != $_REQUEST['password_confirm']) {
    $errors[] = Resources::Get('my_settings.error.password_match');
  }

  if (!empty($_REQUEST['password']) && md5($_REQUEST['password_existing']) != $operator['password']) {
    $errors[] = Resources::Get('my_settings.error.password_existing');
  }

  $hash = array();

  
  $requestFile = $_FILES['avatarFile'];



  
  if (empty($errors) && isset($requestFile) && !empty($requestFile['name']) && $requestFile['size'] > 0 && $requestFile['error'] == 0) {
    $res = Operator::getInstance()->UploadOperatorAvatar($operator['operatorid'], $requestFile);
    if (isset($res)) {
      $errors[] = $res;
    }
    if (empty($errors)) {
      $hash['avatar'] = Operator::getInstance()->getAvatarURL($operator['operatorid'], $requestFile['name']);
    }
  }
  

  if (empty($errors)) {
    $hash['login'] = $_REQUEST['login'];
    $hash['email'] = $_REQUEST['email'];
    $hash['fullname'] = $_REQUEST['fullname'];

    if (!empty($_REQUEST['password'])) {
      $hash['password'] = md5($_REQUEST['password']);
    }

    Operator::getInstance()->UpdateOperator($operator['operatorid'], $hash);
    Operator::getInstance()->RefreshSessionOperator();
    header("Location: " . WEBIM_ROOT . "/operator/profile.php");
    exit;
  }

  foreach (array('login', 'email', 'fullname') as $f) {
    $TML->assign($f, $_REQUEST[$f]);
  }

  $TML->assign('errors', $errors);
  $TML->display('../templates/operator.tpl');
  exit;
}

?>