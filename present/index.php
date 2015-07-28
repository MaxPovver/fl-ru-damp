<?
$rpath = "../";
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/present.php");
session_start();
get_uid();

if(get_uid() <= 0) {
  header("Location: /404.php");
  exit;
}

$id = intval($_GET['id']);

if ($id) {
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
	$acc = new account();
	$acc->GetInfo($_SESSION['uid']);
	$_SESSION['ac_sum'] = $acc->sum;
        $_SESSION['ac_sum_rub'] = $acc->sum_rub;
	$opinfo = present::GetGiftInfo($id);
	if ($opinfo['to_uid'] != get_uid()) {
    header("Location: /404.php");
    exit;
	}

	$pr_txt = $opinfo['op_name'];
	if($opinfo['op_code']==17)
  	$pr_txt = "Первая страница в подарок";

/*
	switch ($opinfo['op_code']){
		case 16: $pr_txt = "Аккаунт ПРО"; break;
		case 17: $pr_txt = "Первая страница &#150; 1 неделя"; break;
		case 18: $pr_txt = "Первая страница &#150; 1 месяц"; break;
		case 23: $pr_txt = $opinfo['ammount']." FM"; break;
		case 26: $pr_txt = "Аккаунт ПРО"; break;
		case 27: $pr_txt = "Первая страница &#150; 1 неделя"; break;
		case 34: $pr_txt = "Первая страница &#150; новогодний подарок &#150; 1 неделя"; break;
		case 35: $pr_txt = "Аккаунт ПРО &#150; новогодний подарок"; break;
		case 42: $pr_txt = "Аккаунт ПРО &#150; подарок на 8 марта"; break;
		default: $pr_txt = "";
	}
*/

	if ($pr_txt && $opinfo['billing_id'] == $acc->id){
		$info = $acc->GetHistoryInfo($opinfo['id'], $_SESSION['uid'], 3);
		$user = new users();
		$user->GetUser($opinfo['login']);
		$cnt_role = (!is_emp($user->role))? "frl" : "emp";
		present::SetGiftResv($id, $_SESSION['uid']);
		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");
		$is_pro = payed::CheckPro($user->login);
  $_SESSION['pro_last'] = payed::ProLast($_SESSION['login']);
  $_SESSION['pro_last'] = $_SESSION['pro_last']['freeze_to'] ? false : $_SESSION['pro_last']['cnt'];
	}
}

if (!$user) {
  header("Location: /404.php");
  exit;
}
$css_file[] = "/css/styles/present.css";

$header = "../header.php";
$footer = "../footer.html";
$content = "content.php";
$page_title = "Фрилансер. Работодатель. Удаленная работа. Поиск работы. Предложение работы. Портфолио фрилансеров. FL.ru";
$page_keyw = "фрилансер, работодатель, удаленная работа, поиск работы, предложение работы, портфолио фрилансеров, разработка сайтов, программирование, переводы, тексты, дизайн, арт, реклама, маркетинг, прочее, fl.ru";
$page_descr = "Фрилансер. Работодатель.Удаленная работа. Поиск работы. Предложение работы. Портфолио фрилансеров. Разработка сайтов, Программирование, Переводы, Тексты, Дизайн, Арт, Реклама, Маркетинг, Прочее. FL.ru";
include("../template.php");
?>
