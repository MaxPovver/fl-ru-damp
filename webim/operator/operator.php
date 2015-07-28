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

require_once(dirname(__FILE__).'/inc/admin_prolog.php');

require_once('../classes/functions.php');
require_once('../classes/class.operator.php');
require_once('../classes/class.adminurl.php');
require_once('../classes/class.smartyclass.php');
require_once('../classes/models/generic/class.mapperfactory.php');

$operator = Operator::getInstance()->GetLoggedOperator();
Operator::getInstance()->IsCurrentUserAdminOrRedirect();

$errors = array();

$TML = new SmartyClass();
$isNew = empty($_REQUEST['operatorid']);

if ($isNew && Operator::getInstance()->isOperatorsLimitExceeded()) {
  $TML->display('operators_limit.tpl');
  require_once(dirname(__FILE__).'/inc/admin_epilog.php');
  die();
}


$TML->assign('mode', $isNew ? 'new_operator' : 'edit_operator');

setAllDepartments();
setAllLocales();

if (isset($_REQUEST['act'])) {
  switch ($_REQUEST['act']) {
    case 'delete':
      deleteOperator();
      break;
    case 'delphoto':
      Operator::getInstance()->UpdateOperator($_REQUEST['operatorid'], array('avatar' => null));
      if ($_REQUEST['operatorid'] == $operator['operatorid']) {
        Operator::getInstance()->RefreshSessionOperator();
      }
      header("Location: " . WEBIM_ROOT . "/operator/operator.php?operatorid=".$_REQUEST['operatorid']);
      break;
  }
} elseif (isset($_REQUEST['submitted'])) {
  operatorSubmitted();
} elseif (isset($_REQUEST['operatorid'])) {
  showOperator();
} else {
  $TML->display('operator.tpl');
}

function showOperator() {
  global $TML;
  setOperator();

  $TML->display('operator.tpl');
}

function setOperator() {
  global $TML;
  if (!isset($_REQUEST['operatorid'])) {
    return;
  }
  
  $op = MapperFactory::getMapper("Operator")->getById($_REQUEST['operatorid']);
  $op_data = MapperFactory::getMapper("OperatorLastAccess")->getById($op['operatorid']);


  if (empty($op)) {
    header("Location: " . WEBIM_ROOT . "/operator/operators.php");
    exit;
  }
  
  $operator_locales = array_map("trim", explode(",", $op_data['locales']));
  $orig_locales = getAvailableLocalesForChat();
  $to_assign_locales = array();
  foreach ($orig_locales as $v) {
     $v['ishaslocale'] = in_array($v['localeid'], $operator_locales);
     $to_assign_locales[] = $v;
  }
  
  $departments = MapperFactory::getMapper("OperatorDepartment")->enumDepartmentsWithOperator($_REQUEST['operatorid'], Resources::getCurrentLocale());
  $TML->assign('departments', $departments);
 
  $TML->assign($op);
  $TML->assign("locales", $to_assign_locales);
  $TML->assign('is_admin', $op['role'] == 'admin');
}

function operatorSubmitted() {
  global $TML, $isNew;
  
  $valid_types = array("gif", "jpg", "png", "jpeg");
  

  $operator = Operator::getInstance()->GetLoggedOperator(false);
  setOperator();

  $toCheck = array('login' => 'form.field.login', 'fullname' => 'form.field.agent_name', 'email' => 'form.field.agent_email');
  if ($isNew) {
    $toCheck['password'] = 'form.field.password';
  }

  foreach ($toCheck as $field => $res) {
    if (empty($_REQUEST[$field])) {
      $errors[] = Resources::Get("errors.required", array(Resources::Get($res)));
    }
  }


  if(empty($errors) && !preg_match("/^[\w_\.]+$/", $_REQUEST['login'])) {
    $errors[] = Resources::Get("page_agent.error.wrong_login");
  }

  if ($_REQUEST['password'] != $_REQUEST['password_confirm']) {
    $errors[] = Resources::Get('my_settings.error.password_match');
  }

  if (empty($errors)) {
    $existingOperator = MapperFactory::getMapper("Operator")->getByLogin($_REQUEST['login']);

    $exists = !empty($existingOperator);
    if ($exists) {
      if ($isNew
      || (!$isNew && $_REQUEST['operatorid'] != $existingOperator['operatorid'])) {
        $errors[] = Resources::Get('page_agent.error.duplicate_login');
      }
    }
  }

  if (empty($errors) && !is_valid_email($_REQUEST['email'])) {    
    $errors[] = Resources::Get('errors.email.format', array(Resources::Get('form.field.agent_email')));;
  }

  $departments = array();
  foreach ($_REQUEST as $key => $value) {
    if (!preg_match("/^departments::(.+)$/", $key, $matches)) {
      continue;
    }
    
    if (isset($_REQUEST[$key]) && $_REQUEST[$key] == 'on') {
      $departments[] = $matches[1];  
    }
  }
  
  $locales = array();
  foreach ($_REQUEST as $key=>$value) {
    if(!preg_match("/^locales::([a-z]{2})$/", $key, $matches)) {
      continue;
    }
    
    if (isset($_REQUEST[$key]) && $_REQUEST[$key] == 'on') {
      $locales[] = $matches[1];  
    }
  }
  
  // restore departments on the page
  $operator = Operator::getInstance()->GetLoggedOperator(false);
  $orig = MapperFactory::getMapper("OperatorDepartment")->enumDepartmentsWithOperator($operator['operatorid'], Resources::getCurrentLocale());
  $toAssign = array();
  foreach ($orig as $d) {
    $d['isindepartment'] = in_array($d['departmentid'], $departments);
    $toAssign[] = $d;
  }
  $TML->assign('departments', $toAssign);
  
  
  
  $orig_locales = getAvailableLocalesForChat();
  $to_assign_locales = array();
  foreach ($orig_locales as $d) {
    $d['ishaslocale'] = in_array($d['localeid'], $locales);
    $to_assign_locales[] = $d;   
  }
  $TML->assign('locales', $to_assign_locales);
  
  $hash = array();
  
  if (empty($errors)) {
    $hash['login'] = $_REQUEST['login'];
    $hash['email'] = $_REQUEST['email'];
    $hash['fullname'] = $_REQUEST['fullname'];
    $hash['role'] = empty($_REQUEST['is_admin']) ? 'operator' : 'admin';


    $op_data_hash['locales'] = implode(",", $locales);
    $op_data_hash['locales'] = empty($op_data_hash['locales']) ? null : $op_data_hash['locales']; //Force mapper to set null for column in DB;
    
    if (!empty($_REQUEST['password'])) {
      $hash['password'] = md5($_REQUEST['password']);
    }




    $operatorToUpdateId = null;
    if ($isNew) {
      $operatorToUpdateId = MapperFactory::getMapper("Operator")->save($hash);
      MapperFactory::getMapper("Operator")->insertOperatorTime( $operatorToUpdateId );
    } else {
      $operatorToUpdateId = $_REQUEST['operatorid'];
      $hash['operatorid'] = $operatorToUpdateId;
      MapperFactory::getMapper("Operator")->save($hash);
    }
    
    $op_data_hash['operatorid'] = $operatorToUpdateId;
    MapperFactory::getMapper("OperatorLastAccess")->save($op_data_hash);
    

    Operator::getInstance()->setOperatorDepartments($operatorToUpdateId, $departments);

    
    $requestFile = $_FILES['avatarFile'];
    if (empty($errors) && isset($requestFile) && !empty($requestFile['name']) && $requestFile['size'] > 0 && $requestFile['error'] == 0) {
      $res = Operator::getInstance()->UploadOperatorAvatar($operatorToUpdateId, $requestFile);
      if (!empty($res)) {
        $errors[] = $res;
      }
      if (empty($errors)) {
        $hash = array();
        $hash['avatar'] = Operator::getInstance()->getAvatarURL($operatorToUpdateId, $requestFile['name']);
        Operator::getInstance()->UpdateOperator($operatorToUpdateId, $hash);
      }
    }
    

    if (empty($errors)) {
      if ($operatorToUpdateId == $operator['operatorid']) {
        Operator::getInstance()->RefreshSessionOperator();
      }
      header("Location: " . AdminURL::getInstance()->getURL('operators'));
      exit;
    }
  }

  foreach (array('login', 'email', 'fullname', 'is_admin') as $f) {
    if (!empty($_REQUEST[$f])) {
      $TML->assign($f, $_REQUEST[$f]);
    }
  }
  $TML->assign('errors', $errors);
  $TML->display('operator.tpl');
  exit;
}

function deleteOperator() {
  Operator::getInstance()->DeleteOperator($_REQUEST['operatorid']);
  header("Location: " . WEBIM_ROOT . "/operator/operators.php");
  exit;
}

function setAllDepartments() {
  global $TML;
  $operator = Operator::getInstance()->GetLoggedOperator(false);
  $departments = MapperFactory::getMapper("OperatorDepartment")->enumDepartmentsWithOperator($operator['operatorid'], Resources::getCurrentLocale());
  $TML->assign('departments', $departments);
}



function setAllLocales() {
    global $TML;
	$TML->assign("locales", getAvailableLocalesForChat());
	
}

?>