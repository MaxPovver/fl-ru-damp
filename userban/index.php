<?
/**
 * !!! ЭТУ СТРАНИЦУ НЕ ИСПОЛЬЗОВАТЬ - ТЕПЕРЬ БАН ДЕЛАЕТСЯ АЯКСМ С ВЕДЕНИЕМ ЛОГА АДМИНСКИХ ДЕЙСТВИЙ
 */
header_location_exit( '/404.php' );

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/opinions.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
session_start();
$no_banner = 1;
$enter=true;

$bIsWhere = true;
$bIsTime  = true;

if ( $_REQUEST['returnpath'] == '/siteadmin/suspicious-users/' ) {
	$bIsWhere = false;
	$bIsTime  = false;
}

if (hasPermissions('users')) {
    if ($_POST["uid"]) {
        /* !!! НЕ РАСКОММЕНТИРОВАТЬ
        $usr=new users();
        if ($_POST["where"]>0) {
            $usr->Ban($_POST["uid"],1,(int)$_POST["reason"],$_POST["comment"],($_POST["alltime"] ? 0 : $_POST["time"]),0,0,intval($_POST["where"]));
            $sm = new smail();
            $error = $sm->SendBlogsBan( $_POST["uid"], (int)$_POST["reason"] );
        } else {
            $usr->Ban($_POST["uid"],1,(int)$_POST["reason"],$_POST["comment"],($_POST["alltime"] ? 0 : $_POST["time"]),0,0,0,intval($_POST['no_send']));
            $opin=new opinions();
            $opin->HideOpin($_POST["uid"]);
        }
        header("Location: ".$_POST["returnpath"]);
        exit;
        */
    }


    $login = (trim($_GET["uid"]));

    $usr=new users();
    $error=$usr->GetUser($login);
    
    if ($usr->login){
    	$sbr = sbr_meta::getInstance(sbr_meta::ADMIN_ACCESS, $usr->login, is_emp($usr->role));
    	$sbrs = $sbr->getActives();
    }
    
    if (!$error) {
        $content = "content.php";
    }

}
else {
    $content = "error.php";
}
$content_bgcolor = '#ffffff';
$header = "../header.php";
$footer = "../footer.html";

$page_title = "Фрилансер. Работодатель. Удаленная работа. Поиск работы. Предложение работы. Портфолио фрилансеров. FL.ru";
$page_keyw = "фрилансер, работодатель, удаленная работа, поиск работы, предложение работы, портфолио фрилансеров, разработка сайтов, программирование, переводы, тексты, дизайн, арт, реклама, маркетинг, прочее, fl.ru";
$page_descr = "Фрилансер. Работодатель.Удаленная работа. Поиск работы. Предложение работы. Портфолио фрилансеров. Разработка сайтов, Программирование, Переводы, Тексты, Дизайн, Арт, Реклама, Маркетинг, Прочее. FL.ru";
include("../template.php");
?>
