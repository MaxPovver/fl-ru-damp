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
$TITLE_KEY = 'menu.department';

require_once(dirname(__FILE__).'/inc/admin_prolog.php');


require_once('../classes/functions.php');
require_once('../classes/class.department.php');
require_once('../classes/class.smartyclass.php');
require_once('../classes/class.adminurl.php');
require_once('../classes/models/generic/class.mapperfactory.php');


Operator::getInstance()->IsCurrentUserAdminOrRedirect();

$errors = array();

if (isset($_REQUEST['act'])) {
  switch ($_REQUEST['act']) {
    case 'delete':
      deleteDepartment();
      break;
  }
} elseif (isset($_REQUEST['submitted'])) {
  departmentSubmitted();
} elseif (isset($_REQUEST['id'])) {
  showDepartment();
} else {
  $TML = new SmartyClass($TITLE_KEY);
  $TML->assign('mode', 'new');
  $TML->display('department.tpl');
}

function showDepartment() {
  global $TITLE_KEY;
  $TML = new SmartyClass($TITLE_KEY);
  $TML->assign('mode', 'edit');
  setDepartment($TML);

  $TML->display('department.tpl');
}

function setDepartment($TML) {
  if (!isset($_REQUEST['id'])) {
    return;
  }
  
  $d = Department::getInstance()->getById($_REQUEST['id'], Resources::getCurrentLocale());



  if (empty($d)) {
    header("Location: " . WEBIM_ROOT . "/operator/departments.php");
    exit;
  }

  $TML->assign($d);
}

function departmentSubmitted() {
  global $TITLE_KEY;
  $TML = new SmartyClass($TITLE_KEY);
  setDepartment($TML);
  $isNew = empty($_REQUEST['id']);

  $toCheck = array('departmentname' => 'form.field.departmentname');

  foreach ($toCheck as $field => $res) {
    if (empty($_REQUEST[$field])) {
      $errors[] = Resources::Get("errors.required", array(Resources::Get($res)));
    }
  }

  if(empty($errors) && !preg_match("/^[a-z_\.\d]*$/", $_REQUEST['departmentkey'])) {
    $errors[] = Resources::Get("page_department.error.wrong_departmentkey");
  }

  if (empty($errors) && !empty($_REQUEST['departmentkey'])) {
    $existing = MapperFactory::getMapper("Department")->getByDepartmentKey($_REQUEST['departmentkey']);

    $exists = !empty($existing);
    if ($exists) {
      if ($isNew
      || (!$isNew && $_REQUEST['id'] != $existing['departmentid'])) {
        $errors[] = Resources::Get('page_department.error.duplicate_department_departmentkey');
      }
    }
  }

  $hash = array();
  
  $department_key = empty($_REQUEST['departmentkey']) ? 
  	makeKeyUnique(generateDepartmentKey()) : 
  	makeKeyUnique($_REQUEST['departmentkey'], !empty($_REQUEST['id']) ? $_REQUEST['id'] : null);
  
  if(!$department_key) 
  	$errors[] = Resources::Get('page_department.error.unable_make_unique_key');
  	
  if (empty($errors)) {
    $hash['departmentkey'] = $department_key;
//    $hash['departmentkey'] = empty($_REQUEST['departmentkey']) ? iconv(WEBIM_ENCODING, 'latin-1', $_REQUEST['departmentname']) : $_REQUEST['departmentkey']; // translit
    $hash['departmentname'] = $_REQUEST['departmentname'];
    if (isset($_REQUEST['id'])) {
      $hash['departmentid'] = $_REQUEST['id'];
    }

    $id = Department::getInstance()->save($hash, Resources::getCurrentLocale());
    $url = AdminURL::getInstance()->getURL('departments');
    header("Location: " . $url);
  }



  
  foreach (array('departmentkey', 'departmentname') as $f) {
    if (!empty($_REQUEST[$f])) {
      $TML->assign($f, $_REQUEST[$f]);
    }
  }
  $TML->assign('errors', $errors);
  $TML->display('department.tpl');
  exit;
}

function generateDepartmentKey() {
  $key = $_REQUEST['departmentname'];

  if(strtolower(WEBIM_ENCODING) !== "cp1251") {
  	$key = smarticonv(WEBIM_ENCODING, "cp1251", $key);
  }

  $key = texttranslit($key);
  $key = preg_replace('/\s+/', '_', $key);
  $key = preg_replace('/[^\w\_\.\d]/', '', $key);
  $key = strtolower($key);
  
  return $key;
}

function makeKeyUnique($k, $id = null) {
  $key = $k;
  $dm = MapperFactory::getMapper("Department");

  $i = 0;
  while($i < 100) { //Try to find unique key if i > 100 return false and show error to user;
	$i++;
  	$existing = $dm->getByDepartmentKey($key);
	$exist = !empty($existing);
	
	if($exist && $id != $existing['departmentid']) {
		$tokens = explode("__", $key);
	    $next_value = count($tokens) > 1 ? intval($tokens[1]) : 0;
	    $next_value++;
		$key = $tokens[0]."__".$next_value;
		continue;
	}
	
	return $key;
  }
  
  return false;
}

function deleteDepartment() {
  Department::getInstance()->deleteDepartment($_REQUEST['id']);
  header("Location: " . AdminURL::getInstance()->getURL('departments'));
  exit;
}

require_once(dirname(__FILE__).'/inc/admin_epilog.php');
?>