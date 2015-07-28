<?	
$g_page_id = "0|5";
$rpath="../";
$grey_catalog = 1;
$stretch_page = true;
$showMainDiv  = true;
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/portfolio.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/portfolio_filter.php");
session_start();

$uid = get_uid();

$activ_tab = 2;

$page = __paramInit('int', 'page', 'page', 1);
if ( $page < 1 ) {
    $page = 1;
}

$action = strip_tags(trim($_GET['action']));
if (!$action) $action = strip_tags(trim($_POST['action']));

$freelancer = new freelancer();
$portfolio = new portfolio();
$prf_filter = new portfolio_filters();
$filter_apply = false;
// Развернутость / свернутость фильтра.
if (isset($_SESSION['portfolio_filter']))
{
  $filter_show = $_SESSION['portfolio_filter'];
}
else
{
  $filter_show = true;
  $_SESSION['portfolio_filter'] = 1;
}

$_POST['pf_cost_from'] = intval($_POST['pf_cost_from']);
$_POST['pf_cost_to']   = intval($_POST['pf_cost_to']);
$_POST['pf_cost_type'] = intval($_POST['pf_cost_type']);
$prof_id = __paramInit('int', 'prof', 'prof', 0);
$direction = __paramInit('int', 'dir', 'dir');
if ($direction != 1)
  $direction = 0;
$order = __paramInit('string', 'order', 'order');

switch ($action)
{
	case "postfilter":
	    $prf_filter->Save($uid, $_POST['pf_cost_from'], $_POST['pf_cost_to'], $_POST['pf_cost_type'], $rerror, $error);
      $prof_id = clearCRLF($prof_id);
      $order = clearCRLF($order);
      $page = clearCRLF($page);
	    header("Location: /portfolio/?prof={$prof_id}&order={$order}&p={$page}");
	    exit;
		break;
	case "activefilter":
        if(!$prf_filter->IsFilter($user_id)) {
            $prf_filter->Save($uid, $_POST['pf_cost_from'], $_POST['pf_cost_to'], $_POST['pf_cost_type'], $rerror, $error);
        }
	    $prf_filter->setActive($uid);
	    break;	
	case "deletefilter":
	    $prf_filter->DeleteFilter($uid);
		break;
}
$pf = $prf_filter->GetFilter($uid, $error);
$filter_apply = ($pf['is_active']=='t');



$prf_pp = intval(trim($_GET['pp']));
if (!$prf_pp)
{
  $prf_pp = PRF_PP;
}


switch ($order)
{
  default:
    $orderby = "rating";
    $str_rating = "По рейтингу фри-лансера";
    break;
  case "rnd":
    $orderby = "random";
    $str_rating = "Случайно";
    break;
  case "prc":
    $orderby = "costs";
    $str_rating = "По цене работы";
    break;
  case "ops":
    $orderby = "opinions";
    $str_rating = "По отзывам заказчиков фри-лансеру";
    break;
}

$fav_show = intval($_GET['fs']);
if ($fav_show != 1)
{
  $fav_show = 0;
}

$prof_name = "Все работы";
	

if (!$prof_id)
{
  // Подсчитываем количество работ.
  $fav_count = 0;
//    $wrk = $portfolio->GetSpecPortfMain($fav_count, $wrk_size, 1, 0, $orderby, $direction, 1, $filter_apply, $pf);
//		unset($wrk);
//		unset($wrk_size);
  // Выбираем работы.
  $prof_group_name = '';
  $page_title = "Фри-лансер. Удаленная работа. Поиск работы. Предложение работы. Портфолио фри-лансеров. FL.ru";
  $page_keyw = "фри-лансер, удаленная работа, поиск работы, предложение работы, портфолио фри-лансеров, разработка сайтов, программирование, переводы, тексты, дизайн, арт, реклама, маркетинг, прочее, fl.ru";
  $page_descr = "Фри-лансер. Удаленная работа. Поиск работы. Предложение работы. Портфолио фри-лансеров. Разработка сайтов, Программирование, Переводы, Тексты, Дизайн, Арт, Реклама, Маркетинг, Прочее. FL.ru";
}
else
{
  // Подсчитываем количество работ.
  $fav_count = 0;
//    $wrk = $portfolio->GetSpecPortf($prof_id, $fav_count, $wrk_size, 1, 0, $orderby, $direction, 1, $filter_apply, $pf);
//		unset($wrk);
//		unset($wrk_size);
  // Выбираем работы.
  //if ($count && $page > ceil($count / $prf_pp)) exit;
  require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
  $prof_name = professions::GetProfName($prof_id);
  $prof_type = professions::GetProfType($prof_id);
  $prof_link = professions::GetProfField($prof_id, 'link');
//  $prof_group_name = professions::GetProfGroupName($prof_id);
  $g_page_id = "1|".$prof_id;
  $page_title = $prof_name.". Удаленная работа. Поиск работы. Предложение работы. Портфолио фри-лансеров. FL.ru";
  $page_keyw = strtolower($prof_name).", удаленная работа, поиск работы, предложение работы, портфолио фри-лансеров, fl.ru";
  $page_descr = $prof_name.". Удаленная работа. Поиск работы. Предложение работы. Портфолио фри-лансеров. FL.ru";
}

$content = "content.php";
$content_bgcolor = '#ffffff';

//$buffer_on = false;
if ($page < 20) $buffer_on = true;
	
$header = "../header.php";
$footer = "../footer.html";
$freelancers_catalog = true;
$css_file = array( '/css/nav.css' );

include ("../template2.php");
	
?>
