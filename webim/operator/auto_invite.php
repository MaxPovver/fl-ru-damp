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

$TITLE_KEY = 'leftMenu.auto_invites';

require_once(dirname(__FILE__).'/inc/admin_prolog.php');


require_once('../classes/functions.php');
require_once('../classes/class.operator.php');
require_once('../classes/class.adminurl.php');
require_once('../classes/class.smartyclass.php');
require_once('../classes/class.json.php');
require_once('../classes/models/generic/class.mapperfactory.php');


Operator::getInstance()->IsCurrentUserAdminOrRedirect();

$errors = array();

if (isset($_REQUEST['act'])) {
  switch ($_REQUEST['act']) {
    case 'delete':
      if(isset($_REQUEST['autoinviteid'])) {  
         MapperFactory::getMapper("AutoInvite")->delete($_REQUEST['autoinviteid']);
      }
      break;
  }
  header("Location: " . AdminURL::getInstance()->getURL('auto_invites'));
  exit;
} elseif (isset($_REQUEST['submitted'])) {
  inviteSubmitted();
} elseif (isset($_REQUEST['autoinviteid'])) {
  showInvite();
} else {
  $TML = new SmartyClass($TITLE_KEY);
  $TML->assign('mode', 'new_invite');
  $TML->display('auto_invite.tpl');
}
require_once(dirname(__FILE__).'/inc/admin_epilog.php');
exit;

function showInvite() {
  global $TITLE_KEY;
  $TML = new SmartyClass($TITLE_KEY);
  $TML->assign('mode', 'edit_invite');
  setInvite($TML);

  $TML->display('auto_invite.tpl');
}

function setInvite($TML) {
  if (!isset($_REQUEST['autoinviteid'])) {
    return;
  }
  
  $invite = MapperFactory::getMapper("AutoInvite")->getById($_REQUEST['autoinviteid']);
  
  if ($invite === null) {
    header("Location: " . AdminURL::getInstance()->getURL("auto_invites"));
    exit;
  }

  $TML->assign("text", $invite['text']);
  $json = new Json(SERVICES_JSON_LOOSE_TYPE);
  $conditions = $json->decode($invite['conditions']);

  if(isset($conditions['came_from'])) {
    $TML->assign("came_from", $conditions['came_from']);
  }
  
  if(isset($conditions['number_of_pages'])) {
    $TML->assign("number_of_pages", $conditions['number_of_pages']);
  }
  
  if(isset($conditions['time_on_site'])) {
    $TML->assign("time_on_site", $conditions['time_on_site']);
  }
  
  if(isset($conditions['order_matters'])) {
    $TML->assign("order_matters", 1);
  } 
  
  if(isset($conditions['visited_pages']) && is_array($conditions['visited_pages'])) {
    $visted_page=array();
    $visted_page_time=array();
     
    foreach ($conditions['visited_pages'] as $v) {
      if(isset($v['url']) && isset($v['time'])) {
        $visited_page[] = "/".trim($v['url'], "/");
        $visited_page_time[] = $v['time'];
      }
    }
    
    $TML->assign("visited_page", $visited_page);
    $TML->assign("visited_page_time", $visited_page_time);
  }
}

function inviteSubmitted() {
  global $TITLE_KEY;
  $TML = new SmartyClass($TITLE_KEY);
  $isNew = empty($_REQUEST['autoinviteid']);

  $toCheck = array('text' => 'form.field.text');
  
  foreach ($toCheck as $field => $res) {
    if (empty($_REQUEST[$field])) {
      $errors[] = Resources::Get("errors.required", array(Resources::Get($res)));
    }
  }
  
  if(!isset($_REQUEST['number_of_pages']) || (!empty($_REQUEST['number_of_pages']) && !is_numeric($_REQUEST['number_of_pages']))) {
    $errors[] = Resources::Get("errors.not_numeric.number_of_pages"); 
  }
  
  if(!isset($_REQUEST['time_on_site']) || !is_numeric($_REQUEST['time_on_site'])) {
    $errors[] = Resources::Get("errors.not_numeric.time_on_site"); 
  }
  
  $visited_pages = array();
  
  if(isset($_REQUEST['visited_page']) && 
      is_array($_REQUEST['visited_page']) && 
      isset($_REQUEST['visited_page_time']) &&
       is_array($_REQUEST['visited_page_time'])
    ) {
      
      foreach($_REQUEST['visited_page'] as $k=>$v) {
        if(!empty($v)) {
	        if(!isset($_REQUEST['visited_page_time'][$k]) || !is_numeric($_REQUEST['visited_page_time'][$k])) {
	          $errors[] = Resources::Get("errors.not_numeric.visited_page_time"); 
	        } else {
	          array_push($visited_pages, array("url" => $v, "time" => $_REQUEST['visited_page_time'][$k]));
	        }
        }
      } 
      
    }
    
  $hash = array();
  
  if (empty($errors)) {
    $hash['text'] = $_REQUEST['text'];
    $hash['conditions'] = array();
    
    if(isset($_REQUEST['came_from']) && !empty($_REQUEST['came_from'])) {
      $hash['conditions']['came_from'] = $_REQUEST['came_from'];
    }
    
    if(isset($_REQUEST['number_of_pages']) && !empty($_REQUEST['number_of_pages'])) {
      $hash['conditions']['number_of_pages'] = $_REQUEST['number_of_pages'];
    }
    
    if(isset($_REQUEST['time_on_site']) && !empty($_REQUEST['time_on_site'])) {
      $hash['conditions']['time_on_site'] = $_REQUEST['time_on_site'];
    }
    
    if(isset($_REQUEST['order_matters']) && !empty($_REQUEST['order_matters'])) {
        $hash['conditions']['order_matters'] = 1;
    }
    
    if(count($visited_pages) > 0 ) {
      $hash['conditions']['visited_pages'] = $visited_pages;
    }
   
    $json = new Json();
    $hash['conditions'] = $json->encode($hash['conditions']);
    
    if ($isNew) {
      $autoinviteToUpdateId = MapperFactory::getMapper("AutoInvite")->save($hash);
    } else {
      $autoinviteToUpdateId = $_REQUEST['autoinviteid'];
      $hash['autoinviteid'] = $autoinviteToUpdateId;
      MapperFactory::getMapper("AutoInvite")->save($hash);
    }
    
    header("Location: " . AdminURL::getInstance()->getURL('auto_invites'));
    exit;
  }

  foreach (array('order_matters', 'text', 'came_from', 'visited_page', 'visited_page_time', 'time_on_site', 'number_of_pages') as $f) {
    if (isset($_REQUEST[$f]) && !empty($_REQUEST[$f])) {
      $TML->assign($f, $_REQUEST[$f]);
    }
  }
  
  $TML->assign('errors', $errors);
  $TML->display('auto_invite.tpl');
}

?>