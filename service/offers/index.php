<?php
$no_banner = 1;
$rpath = "../../";
$offers_page = 1;
$stretch_page       = true;
$showMainDiv        = true;
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/stdf.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/projects_offers_answers.php';
header('Location: /404.php');
        exit;
session_start();
get_uid();

if (!$_SESSION['uid']) { header("Location: /fbd.php"); exit; }
if (is_emp()) { header("Location: /frl_only.php"); exit; }

$answers = new projects_offers_answers;
$action = isset($_GET['action'])? $_GET['action']: '';


if ($action == 'buy') {
    if(isset($_POST['back_uri'])) {
        $_SESSION['bill.GET']['back'] = $_POST['back_uri'];
    }
    $ammount = $_POST['ammount'];
    $cost = $answers->op_codes[$ammount];
    if (!($error = $answers->BuyByFM($_SESSION['uid'], $ammount))) {
        $_SESSION['answers_ammount'] = $_POST['ammount'];
        header("Location: /service/offers/offers_payed.php?answers=$ammount&cost=$cost");
        exit;
    }
}
$page_title = "Ответы на проекты - фриланс, удаленная работа на FL.ru";
$css_file = "projects.css";
$js_file = array( '/css/block/b-promo/b-promo.js' );
$content = "content.php";
$header = "../../header.php";
$footer = "../../footer.html";

include ($rpath."template2.php");

?>