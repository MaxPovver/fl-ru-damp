<?
define( 'IS_SITE_ADMIN', 1 );
$no_banner = 1;
$rpath = "../../";
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/banner_promo.php");
session_start();
get_uid();
/**
  * Проверка заполнености полей
  * @param banner_promo &$bannerData - объект класса с информацией о баннере  
  * @param $id                       - идентификатор редактируемой записи 
  * */
function b_promo_validate(&$bannerData, $recId = 0) {

        $error = 0;
        $errObject = new StdClass();                
        $text  = __paramInit("string", null, "text");
        $type  = __paramInit("string", null, "ban_type");
        $code_txt = __paramInit("string", null, "code_txt");
        $bannerData->files = $file = $bannerData->saveImg($err);
        if ($type == 'image' && (trim($text) == "")&&(trim($file->name) == "")) {
            $errObject->entityError = "Нужно ввести текст ссылки или загрузить изображение.";
            if ($err) $errObject->entityError .= "<br/>$err";            
            $error = 1;
        }
        if($type == 'code' && trim($code_txt) == "") {
            $errObject->entityError = "Нужно ввести текст кода.";
            if ($err) $errObject->entityError .= "<br/>$err";            
            $error = 1;
        }
        if ($err) $errObject->entityError = "$err";
        $from = __paramInit("string", null, "from_date");
        $to   = __paramInit("string", null, "to_date");
        if ($from == '') $from = date('Y-m-d');
        if ($to == '')   $to = date('Y-m-d');
        $a = explode("-", $from);
        $a = array_reverse($a);
        $bannerData->from = $from = join("-", $a);
        $a = explode("-", $to);
        $a = array_reverse($a);
        $bannerData->to = $to = join("-", $a);
        
        $pattern   = "#[0-9]{4}\-[0-9]{2}\-[0-9]{2}#";        
        if (!preg_match($pattern, $from, $m)||!preg_match($pattern, $to, $m)) {
            $errObject->dateError = "Ошибка при вводе даты";
            $error = 1;
        }
        $data  = explode("-", $from);
        $fromT = mktime(0, 0, 0, $data[1], $data[2], $data[0]);
        $data  = explode("-", $to);
        $toT   = mktime(0, 0, 0, $data[1], $data[2], $data[0]);
        if ($toT <= $fromT) {
            $errObject->dateError = "Дата начала не должна быть позже даты окончания";
            $error = 1;
        }
        $adv = __paramInit("bool", null, "advertising");
        $page_target = __paramInit("string", null, 'page_target');
        if ($adv&&!$errObject->dateError) {
            $query = "SELECT count(id) FROM ban_promo_types WHERE 
                          ('$from'  < to_date AND '$to' > from_date)                     
                      AND advertising = 't' AND deleted = 'f'
                      AND (page_target = ? OR page_target = '0|0')
            ";
            if ((int)$recId > 0) {
                $query .= " AND id != $recId";
            }
            global $DB;
            $n = $DB->val($query, $page_target);
            if ($n) {
                $errObject->dateError = "В указанный период времени уже размещена рекламная ссылка";
                $error = 1;
            }
        }
        $link = __paramInit("string", null, "banner_link");
        if (!trim($link)) {
        	$error = 1;
        	$errObject->linkError = "Поле обязательно для заполнения";
        }
        if ($errObject->linkError == '') {
            $link = addhttp($link);
            $_POST['banner_link'] = $link;
            //if ((strpos($link, HTTP_PREFIX) !== 0) && (strpos($link, "/")) !== 0) $link = HTTP_PREFIX."$link";            
        }
        $bannerData->link = $link;
        $name = __paramInit("string", null, "name");
        if (!trim($name)) {
        	$error = 1;
        	$errObject->nameError = "Поле обязательно для заполнения";
        }
        if (!$error) $errObject = $error;
        return $errObject;
}
if ( !(hasPermissions('advstat') && hasPermissions('adm')) ) {
    header ("Location: /404.php"); exit;
}
$bpromo = new banner_promo();
$inner_page = "inner_menu.php";
$css_file = array('payed.css','moderation.css','nav.css','calendar.css');
$js_file    = array( 'calendar.js' );

//просмотр информации о статистике баннера
if(isset($_GET['type'])) {
    $type = $bpromo->setType(intval($_GET['type']), true);
    if($type > 0) {
        $inner_page = "inner_index.php";
    } else {
        include "../../404.php";
        exit;
    }
} else {
    $bpromo = new banner_promo();
    $banners = $bpromo->getInfoBanners();
}
	
//редактирование
if(isset($_GET['edit'])) {
    $edit = intval($_GET['edit']);
    $bpromo->setType($edit, true);
    $advChecked    = '';
    $activeChecked = '';
    if ($bpromo->info['advertising'] == 't') $advChecked    = 'checked="checked"';
    if ($bpromo->info['is_activity'] == 't') $activeChecked = 'checked="checked"';
    if ($bpromo->info['is_pro'][0] == '1') $isPROChecked = 'checked="checked"';
    if ($bpromo->info['is_pro'][1] == '1') $isNotPROChecked = 'checked="checked"';
    if ($bpromo->info['is_role'][0] == '1') $isFrlChecked = 'checked="checked"';
    if ($bpromo->info['is_role'][1] == '1') $isEmpChecked = 'checked="checked"';
    $inner_page = "inner_edit.php";
    if(isset($_POST['save'])) {
        // проверка на заполненность полей
        $error = b_promo_validate($bpromo, (int)$_POST['id']);
        if (__paramInit("bool", null, "advertising")) $advChecked    = 'checked="checked"';        
        if (__paramInit("bool", null, "is_activity")) $activeChecked = 'checked="checked"';
        if(!$error) {
            $login_access = __paramInit('string', null, 'login_access');
            $login_access = explode(",", $login_access);
            $login_access = array_map("trim", $login_access);
            $login_access = implode(",", $login_access);
            
            
            $is_pro  = ( $_POST['is_pro'] ? '1' : '0' ) . ( $_POST['is_not_pro'] ? '1' : '0');
            $is_role = ( $_POST['is_frl'] ? '1' : '0' ) . ( $_POST['is_emp'] ? '1' : '0');
            if($is_pro == '00') $is_pro = '11';
            if($is_role == '00') $is_role = '11';
            $edit = $bpromo->saveInfoBanner($_POST['id'], $_POST['name'], $bpromo->from, $bpromo->to, '', 
                                            __paramInit("bool", null, "is_activity"), $bpromo->files->name, $_POST['img_style'], 
                                            $_POST['img_title'], $bpromo->link,  $_POST['link_style'], __paraminit("bool", null, 'advertising'), $_POST['text'], $_POST['type_ban'], $_POST['code_text'],
                                            $login_access, $is_pro, $is_role, $_POST['page_target']);
            if($edit) {
            	$success_string = "Изменения сохранены";//header("Location: /siteadmin/ban_promo/?edit=".$_POST['id']);
            	$advChecked    = '';
                $activeChecked = '';
                $isPROChecked  = '';
                $isNotPROChecked  = '';
                $isFrlChecked  = '';
                $isEmpChecked  = '';
            	if ($bpromo->info['advertising'] == 't') $advChecked    = 'checked="checked"';
                if ($bpromo->info['is_activity'] == 't') $activeChecked = 'checked="checked"';
                if ($bpromo->info['is_pro'][0] == '1') $isPROChecked = 'checked="checked"';
                if ($bpromo->info['is_pro'][1] == '1') $isNotPROChecked = 'checked="checked"';
                if ($bpromo->info['is_role'][0] == '1') $isFrlChecked = 'checked="checked"';
                if ($bpromo->info['is_role'][1] == '1') $isEmpChecked = 'checked="checked"';
            }
        }
    }
}

//удаление
if(isset($_GET['delete'])) {
    if ( $_SESSION["rand"] != $_POST["u_token_key"] ) {
        header ("Location: /404.php");
        exit;
    }
    $del = intval($_GET['delete']);
    
    $deleted = $bpromo->deleteBanner($del);
    if($deleted) header("Location: /siteadmin/ban_promo/");
}

//создание
if(isset($_GET['new'])) {
    $advChecked    = '';
    $activeChecked = '';
    $inner_page = "inner_new.php";
    if(isset($_POST['new'])) {
        // проверка на заполненность полей
        $error = b_promo_validate($bpromo);        
        if (__paramInit("bool", null, "advertising")) $advChecked    = 'checked="checked"';        
        if (__paramInit("bool", null, "is_activity")) $activeChecked = 'checked="checked"'; 
        if (__paramInit("bool", null, "is_pro")) $isPROChecked = 'checked="checked"';
        if (__paramInit("bool", null, "is_not_pro")) $isNotPROChecked = 'checked="checked"';
        if (__paramInit("bool", null, "is_frl")) $isFrlChecked = 'checked="checked"';
        if (__paramInit("bool", null, "is_emp")) $isEmpChecked = 'checked="checked"';
        if (!$error) {
            $login_access = __paramInit('string', null, 'login_access');
            $login_access = explode(",", $login_access);
            $login_access = array_map("trim", $login_access);
            $login_access = implode(",", $login_access);
            
            $is_pro  = ( $_POST['is_pro'] ? '1' : '0' ) . ( $_POST['is_not_pro'] ? '1' : '0');
            $is_role = ( $_POST['is_frl'] ? '1' : '0' ) . ( $_POST['is_emp'] ? '1' : '0');
            if($is_pro == '00') $is_pro = '11';
            if($is_role == '00') $is_role = '11';
            $new = $bpromo->createBanner($_POST['name'], $bpromo->from, $bpromo->to, '', $_POST['is_activity'], 
                                          $_POST['name_img'], $_POST['img_style'], $_POST['img_title'], $bpromo->link, $_POST['link_style'], __paraminit("bool", null, 'advertising'), $_POST['text'], $_POST['type_ban'], $_POST['code_text'], 
                                         $login_access, $is_pro, $is_role, $_POST['page_target']);
            if($new) header("Location: /siteadmin/ban_promo/");
        }
     }
}
$content = "../content.php";
$header = $rpath."header.php";
$footer = $rpath."footer.html";

include ($rpath."template.php");

?>
