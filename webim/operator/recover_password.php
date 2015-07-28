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
require_once('../classes/common.php');
require_once('../classes/class.smartyclass.php');
require_once('../classes/class.operator.php');
require_once('../classes/class.browser.php');
require_once('../classes/class.thread.php');

$errors = null;
$act = $_REQUEST['act'];
switch ($act) {
  case 'send':
    if (isset($_REQUEST['submitted'])) {
      $e = sendFormSubmitted();

      if (isset($e)) {
        $errors[] = $e;
      }


      $TML = new SmartyClass();
      setLocaleLinkArguments($TML);
      if (empty($errors)) {
        $TML->display('../templates/recover_password_sent.tpl');
      } else {
        $TML->assign('errors', $errors);
        $TML->assign('login', $_REQUEST['login']);
        $TML->display('../templates/recover_password.tpl');
      }
    } else {
      $TML = new SmartyClass();      
      setLocaleLinkArguments($TML);
      $TML->display('../templates/recover_password.tpl');
    }
    break;
  case 'recover':
    if (isset($_REQUEST['submitted'])) {
      if (empty($_REQUEST['password'])) {
        $errors[] = Resources::Get("errors.required", array(Resources::Get('form.field.password')));
      }

      if ($_REQUEST['password'] != $_REQUEST['password_confirm']) {
        $errors[] = Resources::Get('my_settings.error.password_match');
      }

      if (empty($errors)) {
        $o = Operator::getInstance()->GetOperatorByLogin($_REQUEST['login']);
        $recoveryTime = $o['recoverytime'];
        if (empty($o)) {
          $errors[] = Resources::Get("errors.operator_not_found", array(Resources::Get('form.field.login')));
        } elseif ($o['recoverytoken'] != $_REQUEST['token'] || 
                  getCurrentTime() - $recoveryTime > PASSWORD_RECOVER_TIMEOUT) {
          $errors[] = Resources::Get("errors.token_invalid", WEBIM_ROOT.'/operator/recover_password.php?act=send');
        }
      }



      if (empty($errors)) {
        $hash['password'] = md5($_REQUEST['password']);
        $hash['recoverytoken'] = null;
        $hash['recoverytime'] = null;
        Operator::getInstance()->UpdateOperator($o['operatorid'], $hash);
        Operator::getInstance()->DoLogin($_REQUEST['login'], $_REQUEST['password']);
        header('Location: '.WEBIM_ROOT);

      } else {
        $TML = new SmartyClass();
        setLocaleLinkArguments($TML);
        $TML->assign('errors', $errors);
        $TML->assign('password', $_REQUEST['password']);
        $TML->assign('password_confirm', $_REQUEST['password_confirm']);
        $TML->display('../templates/recover_password_new_password.tpl');
      }

    } else {
      showNewPasswordForm();
    }
}


function showNewPasswordForm() {
  $TML = new SmartyClass();
  setLocaleLinkArguments($TML);
  $TML->display('../templates/recover_password_new_password.tpl');
}

function sendFormSubmitted() {
  $login = trim($_REQUEST['login']);
  if (empty($login)) {
    return Resources::Get("errors.required", array(Resources::Get('form.field.login')));
  }
  $o = Operator::getInstance()->GetOperatorByLogin($login);

  if (empty($o)) {
    return Resources::Get("errors.operator_not_found", array(Resources::Get('form.field.login')));
  }

  Operator::getInstance()->SendRecoverPasswordMail($o['operatorid'], $_SERVER['HTTP_HOST']);
}

function buildLocaleArguments() {
  $result = null;
  foreach(array('act', 'token', 'login') as $p) {
    if (!empty($_REQUEST[$p])) {
      $result .= "&$p=".$_REQUEST[$p];
    } else {
      break;
    }
  }
  return $result;
}

function setLocaleLinkArguments($TML) {
  $TML->assign('link_arguments', buildLocaleArguments());  
}
?>