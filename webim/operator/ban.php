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
$TITLE_KEY = 'page_ban.title';

require_once(dirname(__FILE__).'/inc/admin_prolog_before.php');



require_once('../classes/functions.php');
require_once('../classes/class.operator.php');
require_once('../classes/class.adminurl.php');
require_once('../classes/class.smartyclass.php');
require_once('../classes/class.thread.php');
require_once('../classes/class.visitsession.php');
require_once('../classes/models/generic/class.mapperfactory.php');


$banMapper = MapperFactory::getMapper("Ban");

$TML = new SmartyClass($TITLE_KEY);
if (!isset($_REQUEST['submitted'])) {
  if (isset($_REQUEST['address'])) {
    $TML->assign('address', $_REQUEST['address']);
  }
  $TML->assign('till', date(getDateTimeFormat(), time() + 24*60*60));  // next day
}
$operator = Operator::getInstance()->GetLoggedOperator();

$errors = array();

if (isset($_REQUEST['submitted'])) {
  $banid = verify_param("banid", "/^(\d{1,9})?$/", "");
  $address = get_mandatory_param("address");
  $till = get_mandatory_param("till");
  $comment = get_mandatory_param('comment');
  
  if (empty($address)) {
      $errors[] = Resources::Get("errors.required", Resources::Get('form.field.address'));
  } elseif (!preg_match("/^(\d{1,3}.\d{1,3}.\d{1,3}.\d{1,3})$/", $address) 
    && !preg_match("/^([a-z0-9-]+\.)+[a-z0-9-]+$/", $address)) {
      $errors[] = Resources::Get("errors.format", Resources::Get("form.field.address"));
  }

  if (empty($till)) {
      $errors[] = Resources::Get("errors.required", Resources::Get('form.field.till'));
  }
  $isCreateMode = empty($banid);

  if (empty($errors)) {
    $existingBan = $banMapper->getBanBydAddress($address);
    
    if (($isCreateMode && !empty($existingBan))
     || (!$isCreateMode && !empty($existingBan) && $banid != $existingBan['banid'])) {
       
      
      $url = WEBIM_ROOT.'/operator/ban.php';
      

      $errors[] = Resources::Get("ban.error.duplicate", array($address, $url.'?banid='.$existingBan['banid']));
    }
  }

  if (empty($errors)) {
   
    $time = strtotime($till);
   
    if($time < 1) {
      $errors[] = Resources::Get("errors.format", Resources::Get("form.field.till"));
    }

    $hashTable = array(
      'till' => date('Y-m-d H:i:s', $time),
      'address' => $address,
      'comment' => $comment,
    );
      
    if ($isCreateMode) {
      $hashTable['created'] = null ;
    } else {
      $hashTable['banid'] = $banid;
    }
    
    $banMapper->save($hashTable);
    
    header("Location: ".AdminURL::getInstance()->getURL('blocked'));
    exit;      
  }

  $TML->assign('address', $address);
  $TML->assign('till', $till);
  $TML->assign('comment', $comment);
} elseif (isset($_REQUEST['banid'])) {
  $banid = verify_param('banid', "/^\d{1,9}$/");
  $ban = $banMapper->getById($banid);


  $TML->assign('address', $ban['address']);
 
  $TML->assign('till', date(getDateTimeFormat(), $ban['till']));
  $TML->assign('comment', $ban['comment']);
} 

require_once(dirname(__FILE__).'/inc/admin_prolog_after.php');

$TML->assign('errors', $errors);
$TML->display('ban.tpl');

require_once(dirname(__FILE__).'/inc/admin_epilog.php');
?>
