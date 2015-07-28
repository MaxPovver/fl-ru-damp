<?   
  $promotion_page = 1;
  $g_page_id = "0|12";
  $stretch_page = true;
  $showMainDiv  = true;
  $rpath='../';
  require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
  require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");
  require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");
  require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
  require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/billing.php");
  session_start();
  $uid = get_uid();
  
  $account = new account();
  if (!$_SESSION['uid']) {
      header_location_exit('/fbd.php');
  }
  $bill = new billing($_SESSION['uid']);
  $login = __paramInit('string','user',NULL);
  $DEBUG = (hasPermissions('users') && $login && $login!=$_SESSION['login']);

  if($DEBUG) {
    if(!hasPermissions('users') && $_SESSION['login']!='sll' || !$login) { header('Location: /404.php'); exit; }
    $user = new freelancer();
    $user->GetUser($login);
    $uid = $user->uid;
    // если нет $uid значит нет такого логина или это работодатель
    if (!$uid) {
        header_location_exit('/404.php');
    }
    $account->GetInfo(get_uid());
    $account->sum = $account->sum ? $account->sum : 0;
    $iAmAdmin = true;
  }
  else {
    if(!$uid) {
      header('Location: /fbd.php');
      exit;
    }
    if(is_emp()) {
      header('Location: /frl_only.php');
      exit;
    }
    $user = new freelancer();
    $user->GetUserByUID($uid);
    $account->GetInfo($uid);
    $account->sum = $account->sum ? $account->sum : 0;
    $_SESSION['ac_sum'] = $account->sum;
    $_SESSION['ac_sum_rub'] = $account->sum_rub;
    $transaction_id = $account->start_transaction($uid, $tr_id);
  }

  if($user->is_pro=='t')
    $no_banner = true;



  require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/promotion.php");
  require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
  require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");
  require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/rating.php");


  $mode   = __paramInit('int', 'mode', 'mode', 0);
  $mode_array = array(promotion::MODE_FP_MAIN_ID, promotion::MODE_FP_CTLG_ID);
  if (!in_array($mode, $mode_array)) {
      include( ABS_PATH . '/404.php' );
      exit;
  }
  $tool   = __paramInit('int', 'tool', 'tool', 0);
  $tool_array = array(promotion::TOOL_FP_ID, promotion::TOOL_PRO_ID);
  if (!in_array($tool, $tool_array)) {
      include( ABS_PATH . '/404.php' );
      exit;
  }
  $action = __paramInit('string', NULL, 'action');
  $bm     = __paramInit('int', 'bm','bm', 0);
  $tr_id  = __paramInit('int',NULL,'transaction_id');
  $ratingmode   = __paramInit('string', 'ratingmode', 'ratingmode', 'month');

  $prm_is_FP  = ($tool==promotion::TOOL_FP_ID && $mode==promotion::MODE_FP_MAIN_ID);
  $prm_is_CTG = ($tool==promotion::TOOL_FP_ID && $mode==promotion::MODE_FP_CTLG_ID);
  $prm_is_PRO = ($tool==promotion::TOOL_PRO_ID);

  $time  = time();
  $YEAR       = date('Y', $time);
  $MNAMES = array('Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь');
  $MSIZES = array(31,!($YEAR%4) && $YEAR%100 || !($YEAR%400)?29:28,31,30,31,30,31,31,30,31,30,31);
  $NOW        = date('Y-m-d H:i:s',$time);
  $YESTERDAY  = date('Y-m-d',$time - 24*3600);
  $TOMORROW   = date('Y-m-d',$time + 24*3600);
  $TOMORROW_TIME = strtotime($TOMORROW);
  $MONTHDAY   = date('Y-m',$time).'-01';
  $MONTHDAY_TIME = strtotime($MONTHDAY);
  $TODAY      = date('Y-m-d', $time);
  $HOUR       = date('G', $time);
  $TODAY_TIME = strtotime($TODAY);
  $TODAY_DAY  = date('j', $time);
  $MONTH      = date('n', $time);
  $MONTH_SIZE = $MSIZES[$MONTH-1];


  if(defined('STAT_DISABLED') && STAT_DISABLED
     && !($bm==promotion::BM_GUESTS && defined('PROMOTION_GUESTS_DB_ALIAS'))
  ) {
      $content = 'disabled.php';
  } else {
      $content = $bm==promotion::BM_GUESTS ? 'guests.php' : 'content.php';
  }

  $show_rating = (!$mode && !$tool && !$bm);

  
  $header = "../header.php";
  $css_file = array( '/css/promotion.css', '/css/nav.css' );

  $footer = "../footer.html";
  $js_file = array( 'raphael-min.js', 'svg.js' );

  include ("../template.php");


  function getBookmarksStyles($bmCnt, $curPos)
  {
    $arr = NULL;
    for($i=0; $i<$bmCnt; $i++)
      $arr[$i] = ($curPos==$i ? 'act_menu' : (!$i ? 'user_menu_l' : ($curPos == $i-1 ? 'user_menu_la' : 'user_menu')));
    $arr[] = $curPos==$bmCnt-1 ? 'lmenu_activ_r.gif' : 'lmenu_passiv_r.gif';
    return $arr;
  }