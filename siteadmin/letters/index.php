<?php
define( 'IS_SITE_ADMIN', 1 );
$no_banner = 1;
$rpath = "../../";

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/letters.php");

session_start();
$uid = get_uid();

if( !(hasPermissions('letters') && hasPermissions('adm')) ) {
  header ("Location: /404.php");
  exit;
}

$content = "../content2.php";
$css_file    = array( 'nav.css', '/css/block/b-button/_m/b-button_m.css', '/css/block/b-search/b-search.css','/css/block/b-menu/_tabs/b-menu_tabs.css','moderation.css' );
$js_file = array( 'letters.js' );
$header = $rpath."header.php";
$footer = $rpath."footer.html";

switch($_GET['mode']) {
	case 'templates':
		switch($_GET['msg']) {
			case 'aok':
				$msgstr = 'Шаблон добавлен';
				break;
			case 'eok':
				$msgstr = 'Информация о шаблоне сохранена';
				break;
			case 'dok':
				$msgstr = 'Шаблон удален';
				break;
			default:
				$msgstr = '';
				break;
		}
		$templates_list = letters::getTemplatesList();
		$inner_page = "inner_templates.php";
		break;
	case 'add_template':
		$inner_page = "inner_templates_frm.php";
		break;
	case 'edit_template':
		$template = letters::getTemplate(intval($_GET['id']));
		$inner_page = "inner_templates_frm.php";
		break;
	case 'del_template':
        if ( $_SESSION["rand"] != $_POST["u_token_key"] ) {
            header ("Location: /404.php");
            exit;
        }
		letters::delTemplate(intval($_GET['id']));
		header('Location: /siteadmin/letters/?mode=templates&msg=dok');
		exit;
		break;
	case 'company':
		switch($_GET['msg']) {
			case 'aok':
				$msgstr = 'Сторона добавлена';
				break;
			case 'eok':
				$msgstr = 'Информация о стороне сохранена';
				break;
			default:
				$msgstr = '';
				break;
		}
		$inner_page = "inner_company.php";
		break;
	case 'add':
		$inner_page = "inner_company_frm.php";
		break;
	case 'insert':
		letters::addCompany($_POST);
		header('Location: /siteadmin/letters/?mode=company&msg=aok');
		exit;
		break;
	case 'edit':
		$company = letters::getCompany($_GET['id']);
		if(!$company) {
			header('Location: /siteadmin/letters/?mode=company');
			exit;
		}
		$inner_page = "inner_company_frm.php";
		break;
	case 'update':
		letters::updateCompany($_POST);
		header('Location: /siteadmin/letters/?mode=company&msg=eok');
		exit;
		break;
	default:
		$inner_page = "inner_index.php";
		break;
}

$page = $_GET['page'];
switch($page) {
	case 'tab':
		$js_cmd = 'tab';
		$js_cmd_var1 = intval($_GET['tab']);
		break;
	case 'group':
		$js_cmd = 'group';
		$js_cmd_var1 = intval($_GET['group']);
		break;
	case 'doc':
		$js_cmd = 'doc';
		$js_cmd_var1 = intval($_GET['doc']);
		break;
}

include ($rpath."template2.php");
?>