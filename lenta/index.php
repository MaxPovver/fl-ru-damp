<?   
  $g_page_id = "0|16";
  $rpath='../';
  $grey_lenta = 1;
  $stretch_page = true;
  $showMainDiv  = true;
  require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
  session_start();
  $uid = get_uid();

  if(!($uid = get_uid())) {
    header("Location: /fbd.php");
    exit;
  }

  require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/portfolio.php");
  require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
  require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/commune.php");
  require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/links.php");
  require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/lenta.php");
  require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
  require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/blogs.php");



  $header = "../header.php";
  //$additional_header = "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"Блоги на Free-lance.ru (".$gr_name.")\" href=\"/rss/blogs.php?gr=".$gr."&amp;t=".$t."\" />";
  $css_file = array( "lenta.css", 'commune.css', '/css/nav.css' );
  $content = "content.php";
  $js_file = array( 'polls.js', 'commune.js' );
  $footer = "../footer.html";

  $user_mod = commune::MOD_ADMIN * (hasPermissions('communes'));
  $user_mod |= commune::MOD_PRO * (users::IsPro($uid, $e) ? 1 : 0);
  $user_mod |= commune::MOD_EMPLOYER * ((int)is_emp());
  $user_mod |= commune::MOD_BANNED * is_banned($uid);

  $page   = __paramInit('int', 'page', 'page', 1);
  $action = __paramInit('string', NULL, 'action');

  switch($action)
  {
    case "Save" :
      
      $has_lenta   = __paramInit('bool', NULL, 'has_lenta', NULL);
      $my_team     = __paramInit('bool', NULL, 'my_team');
      $all_profs   = __paramInit('bool', NULL, 'all_profs');
      $communes    = __paramInit('array', NULL, 'commune_id');
      $prof_groups = __paramInit('array', NULL, 'prof_group_id');

      if(!lenta::SaveUserSettings($has_lenta, $uid, $my_team, $all_profs, $communes, $prof_groups))
        ; // ошибка.

      header("Location: /lenta/");
      exit;
      
      break;

    default:
      break;
  }



  include ("../template2.php");
?>
