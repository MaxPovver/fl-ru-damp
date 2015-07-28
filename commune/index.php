<?   //if (count($_POST) == 0) die('gracias');
//echo '<pre>'; print_r($_POST); echo '</pre>'; exit;
  $g_page_id = "0|11";  // !!!
  $rpath='../';
  $grey_commune = 1;
  $stretch_page = true;
  $showMainDiv  = true;
  require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/links.php");
  require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/commune.php");
  require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
  require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
  require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

  session_start();

  $uid = get_uid();
  $communeid = isset($_GET['communeid']) ? $_GET['communeid'] : commune::getCommuneIDByMessageID(__paramInit('int', 'post'));
  if($communeid == commune::COMMUNE_BLOGS_ID) {
      $grey_commune = 0;
      $grey_blogs = 1;
  }
  $is_site_admin = hasPermissions('communes');
  
  $header = "../header.php";
  
// Формируем JS внизу страницы
define('JS_BOTTOM', true);

  $css_file = array( '/css/block/b-search/b-search.css', '/css/block/b-spinner/b-spinner.css', '/css/block/b-voting/b-voting.css', '/css/block/b-button/_m/b-button_m.css', '/css/nav.css', '/css/block/b-free-share/b-free-share.css', '/css/block/b-menu/_vertical/b-menu_vertical.css', 'commune.css' );

  $js_file  = array( 'punycode.min.js', 'mAttach2.js', 'polls.js', 'mAttach.js', 'commune.js', 'drafts.js', 'attachedfiles.js', 
      'highlight.min.js', 'highlight.init.js', 'mooeditable.commune/MooEditable.ru-RU.js', 'mooeditable.commune/rangy-core.js', 
      'mooeditable.commune/MooEditable.js', 'mooeditable.commune/MooEditable.Pagebreak.js', 'mooeditable.commune/MooEditable.UI.MenuList.js', 
      'mooeditable.commune/MooEditable.Extras.js', 'mooeditable.commune/init.js', 'tawl_bem.js', 'polls_new.js',
      '/css/block/b-filter/b-filter.js', '/css/block/b-shadow/b-shadow.js', 'comments.all.js');//, 'ckeditor/ckeditor.js' );
  $content = "content.php";
  $footer = "../footer.html";
  
  if($is_site_admin) {
      $js_file[] = 'banned.js';
  }

  $draft_id = intval(__paramInit('int', 'draft_id'));

       function BuildNavigation($iCurrent, $iStart, $iAll, $sHref){
		$sNavigation = '';
		for ($i=$iStart; $i<=$iAll; $i++) {
			if ($i != $iCurrent) {
				$sNavigation .= "<a href=\"".$sHref.$i."\" >".$i."</a>&nbsp;";
			}else {
				$sNavigation .= '<span class="page">'.$i.'</span>&nbsp;';
			}
		}
		return $sNavigation;
	}

	function ShowPages($page,$pages,$uri){
            $sBox = '';
		// Страницы
		if ($pages > 1){
		    $sBox = "
		    <div class=\"pager\">";
                        if ($page == $pages){
				$sBox .= "
				<span class=\"page-next\">следующая&nbsp;&nbsp;&rarr;</span>";
			}else {
                            $next_uri = $uri.($page+1);
				$sBox .= "
				<span class=\"page-next\"><a href=\"".$next_uri."\">следующая</a>&nbsp;&nbsp;&rarr;</span>";
			}
			if ($page == 1){
				$sBox .= "
				<span class=\"page-back\">&larr;&nbsp;&nbsp;предыдущая</span>";
			}else {
                            $prev_uri = $uri.($page-1);
				$sBox .= "
				<span class=\"page-back\">&larr;&nbsp;&nbsp;<a href=\"".$prev_uri."\">предыдущая</a></span>";
			}

			//в начале
			if ($page <= 10) {
				$sBox .= BuildNavigation($page, 1, ($pages>10)?($page+4):$pages, "{$uri}");
				if ($pages > 15) {
					$sBox .= '...';
				}
			}
			//в конце
			elseif ($page >= $pages-10) {
				$sBox .= '...';
				$sBox .= BuildNavigation($page, $page-5, $pages, $uri);
			}else {
				$sBox .= '...';
				$sBox .= BuildNavigation($page, $page-4, $page+4, $uri);
				$sBox .= '...';
			}
            $sBox .= "</div>";
		} // Страницы закончились

		return $sBox;
	}



  $user_mod = 0;
  $comm = NULL;
  $restrict_type = commune::RESTRICT_JOIN_MASK | commune::RESTRICT_READ_MASK;
                                            
	$result = NULL;
	$request = NULL;
	$error = NULL;

  $site       = __paramInit('string', 'site', 'site');
  $mode       = __paramInit('string', 'mode', 'mode');
  $id         = __paramInit('int', 'id', 'id');
  $cat         = __paramInit('int', 'cat', 'cat');
  $om         = __paramInit('int', 'om','om', 0);
  $rating      = __paramInit('string', 'rating', 'rating', '');
  $page       = __paramInit('int', 'page', 'page', 1);
  $bPageDefault = ( isset($_REQUEST['page']) ) ? false : true;
  $order_by = __paramInit('string', 'order', 'order', ($mode == 'Asked' ? 'asked_desc' : 'date_desc'));
  if((int) $cat < 1) $cat = 0;

  // возможно лучше доработать функцию __paramInit
  if((int)$page < 1) $page = extractInteger($_REQUEST['page'], 1);
  $action     = __paramInit('string', 'action', 'action');
  $top_id     = __paramInit('string', 'post', 'top_id');
  $message_id = NULL;

  list($t, $c) = split('[.]',$top_id);
  if($t) $top_id = intvalPgSql($t);
  if($c) $comment_id = intvalPgSql($c);

  if($_GET['post'] && $_GET['site']=='Topic' && !$_GET['newurl'] && $_SERVER['REQUEST_METHOD']!='POST' ) {
      $query_string = preg_replace("/post=" . preg_quote($_GET['post']) ."/", "", $_SERVER['QUERY_STRING']);
      $query_string = preg_replace("/site=Topic/", "", $query_string);
      //$query_string = preg_replace("/^&{1,}/", "", $query_string);
      $query_array = explode('&', $query_string);
      $query_string = '';
      $first_param = true;
      foreach ($query_array as $key=>$value) {
          if ($value) {
              if ($first_param === true) { // перед первым параметром не ставим &
                  $first_param = false;
              } else {
                  $query_string .= '&';
              }
              $query_string .= $value;
          }
      }
      
      header ('HTTP/1.1 301 Moved Permanently');
      header ('Location: '.getFriendlyURL('commune', intval($_GET['post'])).($query_string ? "?{$query_string}" : ""));
      exit;
  }

  if($_GET['id'] && $_SERVER['REQUEST_METHOD']!='POST') {
      $query_string = preg_replace("/id=" . preg_quote($_GET['id']) . "/", "", $_SERVER['QUERY_STRING']);
      $query_string = preg_replace("/^&{1,}/", "", $query_string);
      header ('HTTP/1.1 301 Moved Permanently');
      header ('Location: '.getFriendlyURL('commune_commune', intval($_GET['id'])).($query_string ? "?{$query_string}" : ""));
      exit;
  }
  
  if($_GET['communeid']) {
    $comm_info = commune::getCommuneInfoForFriendlyURL($_GET['communeid']);
    if(!$comm_info) {
      header('Location: /404.php');
      exit;
    } else {
      $url_parts = parse_url($_SERVER['REQUEST_URI']);
      $friendly_url = getFriendlyURL('commune_commune', $comm_info['id']);
      if(strtolower($url_parts['path'])!=$friendly_url) {
        header ('HTTP/1.1 301 Moved Permanently');
        header ('Location: '.$friendly_url);
        exit;
      }
    }
    $_GET['id'] = $comm_info['id'];
    $id = $comm_info['id'];
  }
  
  if ($action == "wysiwygUploadImage") {
  	$permissions = hasPermissions('communes', $uid) || hasPermissions('comments', $uid) || hasPermissions('adm', $uid);
  	if (!$commune_id) {
  		$commune_id = __paramInit("int", "id", "id");
  	}
    if (!$commune_id) {
  		$commune_id = __paramInit("int", "communeid", "communeid");
  	}
    if (!$commune_id) {
  		$commune_id = __paramInit("int", "commune_id", "commune_id");
  	}
  	if (!intval($commune_id)) {
  	    $comm_info = commune::getCommuneInfoByMsgID($top_id);
  	    $commune_id = $comm_info['commune_id'];
  	}
  	$commune_member = commune::GetUserCommuneRel($commune_id, $uid);
    if ($permissions > 0 || $commune_member['is_accepted'] || $commune_member['is_author']) {
        $info = getimagesize($_FILES['wysiwyg_uploadimage']['tmp_name']);
        if ($info['mime'] && strpos($info['mime'], 'shockwave-flash') === false) {
            $cfile = new CFile($_FILES['wysiwyg_uploadimage'], "file_commune");                    
            $fname = $cfile->MoveUploadedFile($_SESSION['login']."/upload");
            if ($cfile->image_size['width'] > commune::IMAGE_MAX_WIDTH || $cfile->image_size['height'] > commune::IMAGE_MAX_HEIGHT) {
                $cfile->Delete($cfile->id);
                echo "status=fileTooBig&msg=Размер изображения превышает максимально допустимый: ".commune::IMAGE_MAX_WIDTH." x ".commune::IMAGE_MAX_HEIGHT;
                exit;
            }
            if ($fname) {
                //добавить данные о файле
                commune::addWysiwygFile($cfile);
                //запомнить идентификатор временного файла                                                                         
                $_SESSION['wysiwyg_inline_files'][$cfile->id] = $cfile->id; 
                $link = WDCPREFIX."/users/".substr($_SESSION['login'], 0, 2)."/".$_SESSION['login']."/upload/".$fname;
                $imgWidth = $cfile->image_size['width'];
                $imgHeight = $cfile->image_size['height'];
                echo "status=uploadSuccess&url={$link}&width=$imgWidth&height=$imgHeight";
            }else {                       
                echo "status=uploadFailed&msg=Ошибка загрузки файла";
                exit;
            }
        }else {
            echo "status=wrongFormat&msg=Загрузите изображение формата gif, png или jpg";
        }
    }else echo "status=fail&msg=У вас недостаточно прав, чтобы оставить этот комментарий";
    exit;
  }
  

  if($site=='Topic' && $top_id) {
    $comm_info = commune::getCommuneInfoByMsgID($top_id);
    if(!$comm_info) {
      header('Location: /404.php');
      exit;
    } else {
      $url_parts = parse_url($_SERVER['REQUEST_URI']);
      $friendly_url = getFriendlyURL('commune', $top_id);
      if(strtolower($url_parts['path'])!=$friendly_url) {
        header ('HTTP/1.1 301 Moved Permanently');
        header ('Location: '.$friendly_url);
        exit;
      }
    }
    $id = $comm_info['commune_id'];
  }

  if(!$uid) {
    $checkType = '';
    if($_GET['grname']) {
      $checkType = 'commune_group';
    } elseif($_GET['communeid']) {
      $checkType = 'commune_commune';
    } elseif($_GET['post'] && $_GET['site']=='Topic') {
      $checkType = 'commune_post';
    } else {
      if ($site != "Create") {
        $checkType = 'commune';
      } else {
        header( "HTTP/1.1 401 Authorization Required");
        header( "Location: /fbd.php" );
        exit;
      }
    }
  }

  // если это редактирование черновика
  if ($draft_id && $action !== 'do.Edit.post') {
      $site = 'Editdraft';
      $action = 'Edit.post';
  }
  // если пытаемся опубликовать из черновика
  if ($draft_id && $action === 'do.Edit.post') {
      $site = 'Newtopic';
      $action = 'do.Create.post';
  }

  if(defined('NEO')) { xFront::creaker()->fillGlobals(get_defined_vars()); }

  $reloc = __commShaolin($error, $comm, $top, $restrict_type, $user_mod);

  $restrict_type = bitStr2Int($comm['restrict_type']);


    if ( $restrict_type & commune::RESTRICT_READ_MASK ) {
        if ( $site != 'Join' && $action != 'Join'
         && ! ($user_mod & (commune::MOD_COMM_AUTHOR | commune::MOD_COMM_ACCEPTED | commune::MOD_ADMIN | commune::MOD_MODER)) )
        {
          $content = 'join.php';
          if(!get_uid()) {
              $css_file = array();
              include ("../fbd.php");
              exit;
          }
          include ("../template2.php");
          exit;
        }
    }
  

  if($reloc) {
    header("Location: {$reloc}");
    exit;
  }
  
  if( ($om >= 4 && !get_uid(false) ) || ($om == 3 && $id > 0 && !get_uid(false)) )  {
    include( ABS_PATH . '/404.php' );
    exit;       
  }
  
  if($comm['name']!="") {
      $page_title = $page_keyw = "Сообщество > {$comm['name']} < - фриланс, удаленная работа на FL.ru";
      $page_descr = LenghtFormatEx($comm['descr'], 250, '', 0);
  }

  if($error) {
    $commune_output = 'error.php';
    include ("../template2.php");
    exit;
  }

 
  // Подключаем RSS.
  if($user_mod & (commune::MOD_COMM_ACCEPTED | commune::MOD_COMM_AUTHOR))
    $additional_header = '<link rel="alternate" type="application/rss+xml" title=\'Сообщество "'.$comm['name'].'" на FL.ru\' href="/rss/commune.php?id='.$id.'" />';
	// Если сюда попали, значит все препятствия пройдены, нужно выполнять операцию.
    
    if ($_POST['action'] != 'wysiwygUploadImage' && $_POST['action'] != 'add_comment' && $_POST['action'] != 'do.Create.post' && $_POST['action'] != 'do.Edit.post') {
      $_SESSION['wysiwyg_inline_files'] = array();
    }
	switch($site)
	{
    	case 'Create' :			      $commune_output = 'create.php';		  break;
    	case 'Edit' :			        $commune_output = 'create.php';			break;
    	case 'Admin' :			      $commune_output = 'admin.php';			break;
    	case 'Admin.members' :
    	    $adminCnt  = commune::GetAdminCount($id) + 1; // + создатель
    	    $joinedCnt = $comm['a_count'] - $comm['w_count'] - $adminCnt;
    	    $nPagesCnt = ( $mode=='Asked' ) ? $comm['w_count'] : $joinedCnt;
            $user_filter = __paramInit('int', 'type', 'type', 0);
            if($user_filter == 1) $nPagesCnt = $adminCnt;
            elseif($user_filter == 2) $nPagesCnt = $comm['a_count'] - $comm['w_count'] - $adminCnt;
            else $nPagesCnt = ( $mode=='Asked' ) ? $comm['w_count'] : $joinedCnt;
    	    $pages     = ceil( $nPagesCnt / commune::MAX_MEMBERS_ON_PAGE );
    	    if ( !$action && 
                ( ($nPagesCnt == 0 || $nPagesCnt - 1 < ($page - 1) * commune::MAX_MEMBERS_ON_PAGE) && !$bPageDefault 
                || $pages == 1 && !$bPageDefault )
            ) {
            	include( ABS_PATH . '/404.php' );
                exit;
            }
    	    
    	    $commune_output = 'members.php';
    	    break;
        case 'Members' :
            $joinedCnt = $comm['a_count'] - $comm['w_count'];
            $pages     = ceil($joinedCnt / commune::MAX_MEMBERS_ON_PAGE);
            
            if ( 
                ($joinedCnt == 0 || $joinedCnt - 1 < ($page - 1) * commune::MAX_MEMBERS_ON_PAGE) && !$bPageDefault 
                || $pages == 1 && !$bPageDefault 
            ) {
            	include( ABS_PATH . '/404.php' );
                exit;
            }
            
            $commune_output = 'all.php';
            $js_file[] = '/css/block/b-input-hint/b-input-hint.js';
            break;
        case 'Join' :             $commune_output = 'join.php';       break;
        case 'Topic' :

            $foto_alt = $top['title'];
            
            /*require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );
            $stop_words = new stop_words( hasPermissions('communes') );*/
            
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/comments/CommentsCommune.php");
            $params = array(
                'theme_id' => $top['theme_id'],
                'hidden_threads' => $top['hidden_threads'],
            );
            
            $is_user_member = ($user_mod & commune::MOD_COMM_ACCEPTED);
            $is_user_admin = ($user_mod & (commune::MOD_COMM_AUTHOR | commune::MOD_ADMIN | commune::MOD_MODER | commune::MOD_COMM_MODERATOR));
            $is_top_author  = $top['user_id'] == get_uid(0);
            
            if ((!$is_user_member && !$is_user_admin && !$is_top_author) || $top['is_blocked_s'] == 't' || $top['is_blocked_c'] == 't') {
                $params['readonly'] = 1;
                $params['readonly_alert'] = 'Вы не являетесь членом данного сообщества. Данная функция вам недоступна.';
            }
            if ($top['close_comments'] == 't') {
                $params['no_comments'] = true;
                $params['readonly']    = 1;
            }
            if ($is_user_member) {
                $params['readonly_alert'] = 'Комментирование закрыто.';
            }
            $params['is_permission'] = commune::setAccessComments($user_mod);
            
            if(!$params['is_permission'] && $is_top_author) {
                $params['is_permission'] = 4;
            }
            
            if(commune::isBannedCommune($user_mod) || $top['deleted_id']) {
                $params['readonly'] = 1;
            }
            
            if(!commune_carma::isAllowedVote()) {
                $params['deny_vote'] = true; 
            }
            
            if(__paramInit('string', 'cmtask', 'cmtask')=='delete' || __paramInit('string', 'cmtask', 'cmtask')=='restore') {
              if($_GET['token']!=$_SESSION['rand']) { header("Location: /404.php"); exit; }
            }
            $comments = new CommentsCommune($top['id'], $top['last_viewed_time'], $params);
            $comments->tpl_path = $_SERVER['DOCUMENT_ROOT'] . "/classes/comments/";
            $comments_html = $comments->render();
            
            $js_file_utf8[] = '/scripts/ckedit/ckeditor.js';
            $js_file[] = '/scripts/commune_card.js';
            // если собираемся редактировать пост
            if (__paramInit('string', 'taction') === 'edit') {
                
                if (!get_uid(1)) {
                    header_location_exit('/fbd.php');
                }
                
                if(!$action) $action = 'Edit.post';
                $commune_output = 'tpl.topic_form.php';
                $js_file = array_merge($js_file, array( /*'highlight.min.js', 'highlight.init.js', 'mooeditable.new/rangy-core.js', 
                    'mooeditable.new/MooEditable.js', 'mooeditable.new/MooEditable.ru-RU.js', 'mooeditable.new/MooEditable.Pagebreak.js', 
                    'mooeditable.new/MooEditable.UI.MenuList.js', 'mooeditable.new/MooEditable.Extras.js', 'mooeditable.new/init.js', 
                    'mAttach2.js',*/ 'comment_form.js' ));
            } else {
                $commune_output = 'tpl.comments.php';
            }
            
            $sTitle    = /*$top['moderator_status'] === '0' ? $stop_words->replace($top['title'], 'plain', false)    :*/ $top['title'];
            $sMessage  = /*$top['moderator_status'] === '0' ? $stop_words->replace($top['msgtext'], 'plain', false)  :*/ $top['msgtext'];
            $sQuestion = /*$top['moderator_status'] === '0' ? $stop_words->replace($top['question'], 'plain', false) :*/ $top['question'];
            
            if ( $top['title'] ) {
                $page_title = $page_keyw = "{$sTitle} - сообщества > {$comm['name']} < - фриланс, удаленная работа на FL.ru";
            } else {
                $html_title = strip_tags(substr($sMessage, 0, 30) . (strlen($sMessage)>30?"...":""));
                if($html_title == "") $html_title = substr($sQuestion, 0, 30) . (strlen($sQuestion)>30?"...":"");
                $page_title = $page_keyw = "{$html_title} - сообщества > {$comm['name']} < - фриланс, удаленная работа на FL.ru";
            }
            $FBShare = array(
                "title"       => ( $sTitle ? $sTitle : $html_title ),
                "description" => "",
                "image"       => HTTP_PREFIX."www.free-lance.ru/images/free-lance_logo.jpg"  
            );
            $page_descr = LenghtFormatEx($comm['descr'], 250, '', 0);
        
          break;
          case 'Newtopic' :
            $cur_user = new users(); $cur_user->GetUserByUID(get_uid(false)); 
            if( !(( ($comm['id']==5100 || $comm['id']==1008) && $cur_user->is_team=='t') || ($comm['id']!=5100 &&$comm['id']!=1008) ) ) { header('Location: '.getFriendlyURL('commune_commune', $comm['id'])); exit; }
            $commune_output = 'tpl.topic_form.php';
            $js_file_utf8[] = '/scripts/ckedit/ckeditor.js';
            $js_file = array_merge($js_file, array( /*'highlight.min.js', 'highlight.init.js', 'mooeditable.new/rangy-core.js', 
                'mooeditable.new/MooEditable.js', 'mooeditable.new/MooEditable.ru-RU.js', 'mooeditable.new/MooEditable.Pagebreak.js', 
                'mooeditable.new/MooEditable.UI.MenuList.js', 'mooeditable.new/MooEditable.Extras.js', 'mooeditable.new/init.js', 
                'mAttach2.js',*/ 'comment_form.js' ));
            break;
        
        case 'Editdraft':
            $commune_output = 'tpl.topic_form.php';
            $js_file_utf8[] = '/scripts/ckedit/ckeditor.js';
            $js_file = array_merge($js_file, array( /*'highlight.min.js', 'highlight.init.js', 'mooeditable.new/rangy-core.js', 
                'mooeditable.new/MooEditable.js', 'mooeditable.new/MooEditable.ru-RU.js', 'mooeditable.new/MooEditable.Pagebreak.js', 
                'mooeditable.new/MooEditable.UI.MenuList.js', 'mooeditable.new/MooEditable.Extras.js', 'mooeditable.new/init.js', 
                'mAttach2.js',*/ 'comment_form.js' ));
            break;

		default :
		    
		    if ( isset($_GET['om']) && $_GET['om'] == '0' ) {
            	header("HTTP/1.1 301 Moved Permanently");
            	header( 'Location: ' . e_url( 'om' ) );
            	exit(0);
            }

			if($mode=='Back')
			{
        if($top_id && ($mess = commune::GetMessage($top_id)))
				{
          $om = __paramInit('int', 'om', 'om', commune::OM_TH_NEW); 
          $page = ceil(commune::GetTopMessagePosition($top_id, $uid, $om) / (float)commune::MAX_TOP_ON_PAGE);
          $page = $mess['pos'] !== NULL ? 1 : $page;
          header("Location: /commune/?id={$mess['commune_id']}".($om ? "&om={$om}":'')."&page={$page}".($rating ? '&rating='.$rating : ''));
				}
        else
    		  header("Location: /commune/");

        exit;
			}

			if(!$id) {
			    // дополнительный параметр сортировки
                if ( $_GET['sub_om'] ) {
                    $sub_om = $_GET['sub_om'];
                    setcookie( "commune_{$om}_ord", $sub_om, strtotime('+10 years') );
                }
                else {
                    $sub_om = ( isset($_COOKIE["commune_{$om}_ord"]) ) ? $_COOKIE["commune_{$om}_ord"] : (($om == commune::OM_CM_JOINED) ? commune::OM_CM_JOINED_ACCEPTED : $om);
                }

                if($_GET['gr'] && !$_GET['newurl'] && $_SERVER['REQUEST_METHOD']!='POST') {
                    $query_string = preg_replace("/gr=" . preg_quote($_GET['gr']) . "/", "", $_SERVER['QUERY_STRING']);
                    $query_string = preg_replace("/^&{1,}/", "", $query_string);
                    header ('HTTP/1.1 301 Moved Permanently');
                    header ('Location: '.getFriendlyURL('commune_group', intval($_GET['gr'])).($query_string ? "?{$query_string}" : ""));
                    exit;
                }
                $group_info = commune::getGroupByLink($_GET['grname']);
                if(!$group_info && $_GET['grname']) {
                  header('Location: /404.php');
                  exit;
                }
                $_GET['gr'] = $group_info['id'];
                
                // Сообщества.
                $gr_id     = __paramInit('int', 'gr', NULL);
                $search    = stripslashes( __paramInit('string', 'search', NULL) );
                $sAuthorId = ( $om == commune::OM_CM_MY ? $uid : NULL );
                $sUserId   = ( $om == commune::OM_CM_JOINED ? $uid : NULL );
                $offset    = ($page - 1) * commune::MAX_ON_PAGE;
                $limit     = commune::MAX_ON_PAGE;

               
                $groupCommCnt = 0;  // Количество сообществ в данном разделе.
                
                if ( ($om == commune::OM_CM_MY || $om == commune::OM_CM_JOINED) && !$uid ) { // Неавторизовался и зашел в "свои" закладки.
                    $communes = NULL;
                }
                else {
                    if ( $om == commune::OM_CM_JOINED || $om == commune::OM_CM_MY || $search !== NULL ) {
                        $communes = commune::GetCommunes($gr_id, $sAuthorId, $sUserId, $sub_om, (!$uid ? NULL : $uid), $offset, $limit, $search, $groupCommCnt, $user_mod, $rating);
                    }
                    else {
                        $communes = commune::GetCommunes($gr_id, NULL, NULL, $om, (!$uid ? NULL : $uid), $offset, $limit, $search, $groupCommCnt, $user_mod, $rating);
                    }
                }
                
                if (!$communes) {
                    $communes = array();
                    $groupCommCnt = 0;
                }
                
                $pages = ceil( $groupCommCnt / commune::MAX_ON_PAGE );
                
                if ( 
                    ($groupCommCnt == 0 || $groupCommCnt - 1 < ($page - 1) * commune::MAX_ON_PAGE) && !$bPageDefault 
                    || $pages == 1 && !$bPageDefault 
                ) {
                	include( ABS_PATH . '/404.php' );
                    exit;
                }
                
                switch ( $om ) {
                    case commune::OM_CM_POPULAR: $sOrd = 'Популярные';  break;
                    case commune::OM_CM_ACTUAL:  $sOrd = 'Актуальные';  break;
                    case commune::OM_CM_NEW:     $sOrd = 'Новые';       break;
                    case commune::OM_CM_MY:      $sOrd = 'Я создал';    break;
                    case commune::OM_CM_JOINED:  $sOrd = 'Я вступил в'; break;
                    case commune::OM_CM_BEST:
                    default:
                        $sOrd = 'Лучшие';
                        break;
                }
                
                if ( $gr_id ) {
                    $aGroup = commune::getGroupById( $gr_id );
                    $sGroup = $aGroup['name'];
                }
                else {
                    $sGroup = 'Все Сообщества';
                }
                
                $page_title = $page_keyw = $sOrd . ' сообщества раздела > ' . $sGroup . ' < - ' . ( $page > 1 ? "Страница $page - " : '' ) . ' фриланс, удаленная работа на FL.ru';
                $page_descr = $sOrd . ' сообщества раздела ' . $sGroup . ' на FL.ru';
                
                $commune_output = 'tpl.main.php';
			}
			else {
                // Количество всего тем в сообществе.
                $communeThemesCounts = commune::getCommuneThemesCount($comm['id']);
                if (hasPermissions('communes')) {
                    $themesCount = $communeThemesCounts['count'];
                } elseif ($comm['author_id'] == get_uid(false) || $comm['is_moderator'] === 't') {
                    $themesCount = $communeThemesCounts['count'] - $communeThemesCounts['admin_hidden_count'];
                } else {
                    $themesCount = $communeThemesCounts['count'] - $communeThemesCounts['hidden_count'];
                }
                if ($om == commune::OM_TH_MY) {
                    $themesCount = $uid ? commune::GetMyThemesCount($id, $uid, $cat) : 0;
                }
                //else if(!($user_mod & (commune::MOD_ADMIN)))           // !!! Не, похоже не надо. На количество страниц это влияние не оказывает,
                //  $themesCount -= commune::GetBannedThemesCount($id);  // т.к. запрос не использует данное условие, то есть в офсете есть и забаны.
                // Просто на конкретной странице окажется меньше чем commune::MAX_TOP_ON_PAGE
                // топов и все.
                
                if ($cat && $om != commune::OM_TH_MY) {
                    $themesCount = commune::GetCategoryThemesCount($id, $cat);
                }
                
                $pages = ceil( $themesCount / commune::MAX_TOP_ON_PAGE );
                
                if ($page > 1 && !in_array($action, array('do.Create.post','do.Edit.post')) && ( 
                    ($themesCount == 0 || $themesCount - 1 < ($page - 1) * commune::MAX_TOP_ON_PAGE) && !$bPageDefault 
                    || $pages == 1 && !$bPageDefault 
                )) {
                    include( ABS_PATH . '/404.php' );
                    exit;
                }
                
                if( $comm['name'] != "" ) {
                    $page_title = $page_keyw = "Сообщество > {$comm['name']} <". ($page > 1 ? " - Страница $page" : '') .' - фриланс, удаленная работа на FL.ru';
                    $page_descr = LenghtFormatEx($comm['descr'], 250, '', 0);
                }
                
                $FBShare = array(
                    "title"       => $comm['name'],
                    "description" => "",
                    "image"       => HTTP_PREFIX."www.free-lance.ru/images/free-lance_logo.jpg"  
                );

                $commune_output = 'tpl.commune.php';
			}

			break;
	}


  switch($action)
  {
    case 'do.Update.admin' :
      
      $alert = NULL;

      $member_id    = __paramInit('array', NULL, 'member_id');
      $note         = __paramInit('array', NULL, 'note');
      $is_moderator = __paramInit('array', NULL, 'is_moderator');
      $is_manager   = __paramInit('array', NULL, 'is_manager');

      $mCnt = count($member_id);
      for($i=0; $i<$mCnt; $i++) {
        $n = change_q_new($note[$i], TRUE);
        if(strlen($n) > commune::MEMBER_NOTE_MAX_LENGTH)
          $n = substr($n, 0, commune::MEMBER_NOTE_MAX_LENGTH);
        commune::UpdateAdmin(intval($member_id[$i]), $n, ($is_moderator[$i] ? 1 : 0), ($is_manager[$i] ? 1 : 0));
      }

      header("Location: /commune/?id={$id}&site=Admin");
      exit;

      break;

    
    case 'do.Add.admin' :
      
      $commune_output = 'admin.php';

      $user_login = __paramInit('string', 'user_login', NULL);
      $alert = NULL;

      if(!trim($user_login)) {
        header("Location: /commune/?id={$id}&site=Admin");
        exit;
      }

      $r = commune::AddAdmin($id, $user_login, $e);
      if(!$r)
        $alert['user_login'] = "Не удалось совершить операцию: {$e}";
      else if($r<0)
        if(strtolower($user_login) == strtolower($_SESSION['login'])) {
            $alert['user_login'] = "Нельзя назначить создателя сообщества администратором.";
        } else {
            $alert['user_login'] = "Пользователь с таким логином не существует либо он не состоит в данном сообществе.";
        }
      else {
        $sm = new smail();
        if($user_id = users::GetUid($e, $user_login))
          $sm->CommuneMemberAction($user_id, $action, $comm);
        header("Location: /commune/?id={$id}&site=Admin");
        exit;
      }
      
      break;


    case 'do.Remove.admin' :
      
      $commune_output = 'admin.php';

      $member_id  = __paramInit('int', 'm', NULL); 
      $alert = NULL;

      if(!$member_id) {
        header("Location: /commune/?id={$id}&site=Admin");
        exit;
      }
    
      if(!($user_id=commune::RemoveAdmin($member_id)))
        $alert = 'Ошибка!'; // !!! Куда-то ее вывести...
      else {
        $sm = new smail();
        $sm->CommuneMemberAction($user_id, $action, $comm);
        header("Location: /commune/?id={$id}&site=Admin");
        exit;
      }

      break;


    case 'do.Kill.member' :
    case 'do.Accept.member' :
    case 'do.Unaccept.member' :
      
      $commune_output = 'members.php';
      
      $member_id  = __paramInit('int', 'm', NULL); 
      $alert = NULL;

      if(!$member_id) {
        header("Location: /commune/?id={$id}&site=Admin.members");
        exit;
      }

      if(!($user_id=commune::AcceptMember($member_id, ($action=='do.Accept.member' ? 0 : 1))))
        $alert = 'Ошибка!'; // !!! Куда-то ее вывести...
      else {
        $sm = new smail();
        $sm->CommuneMemberAction($user_id, $action, $comm);
        header("Location: /commune/?id={$id}&site=Admin.members".($action == 'do.Kill.member' ? '' : '&mode=Asked'));
        exit;
      }

      break;
      

    case 'Create.post' :
    case 'Edit.post' :
      break;

			
    case 'do.Create.post' :
    case 'do.Edit.post' :
      $alert = NULL;

      $cur_user = new users(); $cur_user->GetUserByUID(get_uid(false)); 
      if( !( ($comm['id']!=5100 && $comm['id']!=1008) || ($cur_user->is_team=='t' && ($comm['id']==5100 || $comm['id']==1008)) ) ) { header('Location: '.getFriendlyURL('commune_commune', $comm['id'])); exit; }
      
      
      $om             = __paramInit('int', 'om', 'om', commune::OM_TH_NEW);
      $user_login     = __paramInit('string', NULL, 'user_login');
      $message_id     = __paramInit('int', NULL, 'message_id'); // Если есть, то оно редактируется.

      $parent_id      = __paramInit('int', NULL, 'parent_id');
      $pos            = __paramInit('int', NULL, 'pos');
      $title          = antispam(__paramInit('htmltext', NULL, 'title', ''));
        // дополнительно обрезаем отформатированую строку до 256 символов (максимальное количество в базе)
        $title = substr($title, 0 , 256);
        $title = $title === false ? '' : $title;
      $category_id    = __paramInit('int', NULL, 'category_id');
      if(commune::IS_NEW_WYSIWYG) {
          $msgtext        = __paramValue('ckedit', antispam($_POST['msgtext']));//antispam(__paramInit('wysiwyg_tidy', NULL, 'msgtext', ''));
          //$msgtext = stripslashes($msgtext);
      } else {
        $msgtext        = antispam(__paramInit('wysiwyg_tidy', NULL, 'msgtext', ''));
      }
      $youtube_link   = __paramInit('html', NULL, 'youtube_link', '');
	  //$attach         = __paramInit('string', NULL, 'prev_attach');
      //$youtube_link   = str_replace('watch?v=', 'v/', $youtube_link);
	  $question       = antispam(trim(__paramInit('string', NULL, 'question')));
	  $answers        = is_array($_POST['answers'])? $_POST['answers']: array();
	  $answers_exists = is_array($_POST['answers_exists'])? $_POST['answers_exists']: array();
	  $multiple       = __paramInit('int', NULL, 'multiple', '0');

      $close_comments      = __paramInit('int', NULL, 'close_comments');
      $is_private          = __paramInit('int', NULL, 'is_private');

      $user_data = commune::GetUserCommuneRel($id,$uid);
      if((!$user_data['is_accepted'] || $user_data['is_deleted'] || $user_data['is_banned'])
              && (!hasPermissions('communes') && !$user_data['is_author'])){
                header("Location: /commune/?id={$id}&om={$om}".($rating ? '&rating='.$rating : ''));
                exit();
              }
	  if($_POST['delattach']) {
	      $deleted_file = $_POST['delattach'];
	      $file = new CFile();
	      $file->table = "file_commune";
	      foreach($deleted_file as $i=>$id_del) {
	          $file->Delete($id_del);
	      }
	  }
      // $file       = isset($_FILES['file']) ? new CFile($_FILES['file']) : '';
     if (!($user_mod & (commune::MOD_COMM_AUTHOR | commune::MOD_COMM_ADMIN)) && !hasGroupPermissions('administrator')) $pos = false;
      
      $user_id = $uid;

      if(!$user_login)
        $user_login = $_SESSION['login'];

      $request['parent_id'] = $parent_id;
      $request['title'] = $title;
      $request['category_id'] = $category_id;
      $request['msgtext'] = $msgtext;
      $request['youtube_link'] = $youtube_link;
      $request['user_login'] = $user_login;
      $request['pos'] = $pos;
      $request['small'] = 0;
	  $request['question'] = $question;
	  $request['answers'] = $answers;
	  $request['answers_exists'] = $answers_exists;
	  $request['multiple'] = $multiple;
	  $request['close_comments'] = $close_comments;
	  $request['is_private'] = $is_private;
      $small=0;
      $attach_name = NULL;


      // загрузка файлов
        $files = array();
		$attach = $_FILES['attach'];
		$countfiles = 0;
		if (is_array($attach) && !empty($attach['name'])) {
		    $nTotalSize = 0;
            $aAttach    = commune::GetAttach( $message_id, true );
            
            if ( is_array($aAttach) && count($aAttach) ) {
            	foreach ( $aAttach as $sFile ) {
            	    $nTotalSize += $sFile['size'];
            	}
            }
			foreach ($attach['name'] as $key=>$v) {
				if (!$attach['name'][$key]) continue;
				$countfiles++;
				$files[] = new CFile(array(
					'name'     => $attach['name'][$key],
					'type'     => $attach['type'][$key], 
					'tmp_name' => $attach['tmp_name'][$key], 
					'error'    => $attach['error'][$key], 
					'size'     => $attach['size'][$key]
				));
				
				$nTotalSize += $attach['size'][$key];
				
				if ( $nTotalSize > commune::MAX_FILE_SIZE ) {
                	$alert['attach']   = 'Максимальный объем прикрепленных файлов: ' . (commune::MAX_FILE_SIZE / (1024*1024))." Мб";
                	break;
                }
			}
			
			
		}
		
		if ( count($files) > commune::MAX_FILES ) { 
			$alert['attach']   = "Максимальное кол-во файлов для загрузки: " . commune::MAX_FILES;
		}
		
      if($files) {
		if(!($err = tryLoadFile($files, 'upload', $user_login, $attach_name, $small,
                                commune::MSG_IMAGE_MAX_WIDTH, commune::MSG_IMAGE_MAX_HEIGHT, commune::MSG_FILE_MAX_SIZE)))
        {
		  //$request['small'] = $small;
        }
        else {
          $alert['attach'] = $err;
          $attach_name = NULL;
        }
      }
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/attachedfiles.php");
            $attachedfiles = new attachedfiles($_POST['attachedfiles_session']);
            $attachedfiles_files = $attachedfiles->getFiles(array(1,3,4));
            
            
	   //$question = change_q_x_a(antispam(trim((string) $question)), false, false, '');
       if(strlen_real($question) > commune::POLL_QUESTION_CHARS_MAX) {
           $len = strlen($question);
           $rlen = strlen_real($question);
           $question = substr($question, 0, $len - ($rlen - commune::POLL_QUESTION_CHARS_MAX));
       }
	   $answers = array();
	   $answers_exists = array();
	   $multiple = (bool) $_POST['multiple'];
	   $acount  = 0;
	   if ($request['answers'] && is_array($request['answers'])) {
		 foreach ($request['answers'] as $key=>$answer) {
		    if (trim($answer) != '') {
				$answers[] = __paramValue('string', $answer, commune::POLL_ANSWER_CHARS_MAX * 2);
				++$acount;
		    } else {
                unset($request['answers'][$key]);
            }
		 }
	   }
       $request['answers'][] = ''; // добавляем последний пустой ответ
	   if ($_POST['answers_exists'] && is_array($_POST['answers_exists'])) {
		foreach ($_POST['answers_exists'] as $key=>$answer) {
			if (intval($key) && trim($answer) != '') {
				$answers_exists[intval($key)] = __paramValue('string', $answer, commune::POLL_ANSWER_CHARS_MAX * 2);
				++$acount;
			}
		}
	   }
	   if ($acount > 0 && $question == '') {
			$alert['polls'] = 'Введите текст вопроса';
	   } else if ($acount > commune::POLL_ANSWERS_MAX && $question != '') {
			$alert['polls_question'] = 'Вы можете указать максимум '.commune::POLL_ANSWERS_MAX.' ответов';
	   } else if ($acount < 2 && $question != '') {
			$alert['polls_question'] = 'Нужно указать минимум 2 варианта ответа';
	   }

      if(strlen($_POST['title']) > commune::MSG_TITLE_MAX_LENGTH)
        $alert['title'] = 'Количество символов превышает допустимое ('.commune::MSG_TITLE_MAX_LENGTH.')';

      if(is_empty_html($msgtext) && $question == '' && empty($alert) && $nTotalSize == 0 && $youtube_link == '' && count($attachedfiles_files) == 0) {
        $alert['msgtext'] = 'Поле заполнено некорректно';
        $msgtext='';
      }
      else if(strlen($msgtext) > commune::MSG_TEXT_MAX_LENGTH)
        $alert['msgtext'] = 'Количество символов превышает допустимое';

       if ($youtube_link != '') {
		 if ($video = video_validate($youtube_link)) {
		   $request['youtube_link'] = $video;
		 } else {
           $alert['youtube'] = 'Неверная ссылка';
         }
       }

	   
      if($alert) {
      	  
		//if(!$file->name) $request['attach'] = $prev_attach;
		  
	  } else {
        $draft_id = intval(__paramInit('int', 'draft_id', 'draft_id'));
        if($message_id = commune::CreateMessage($request, $id, $user_id, $message_id, $files, $question, $answers, $answers_exists, $multiple)) {
            commune::DeleteMarkedAttach($message_id);

            
            commune::addAttachedFiles($attachedfiles_files, $message_id, NULL, ($draft_id ? true : false)); 
            $attachedfiles->clear();

					if($site!='Topic') {
						if($action=='do.Edit.post' && ($om==commune::OM_TH_ACTUAL || $om==commune::OM_TH_MY))
						  $page=1;
            if($action=='do.Create.post' && ($om==commune::OM_TH_POPULAR)) {
						  $page=1;
						  $om=commune::OM_TH_NEW;
						}

            require_once($_SERVER['DOCUMENT_ROOT']."/classes/drafts.php");

            if($draft_id) { drafts::DeleteDraft($draft_id, get_uid(false), 4); }
            
            // пингуем Яндекс.Блоги, если сообщество открытое
            if ((int)$comm['restrict_type']{0} === 0) {
                require_once($_SERVER['DOCUMENT_ROOT']."/classes/IXR.php");
                $pingClient = new IXR_Client('ping.blogs.yandex.ru', '/RPC2');
                // Что посылаем в пингах
                // Название сайта
                $siteName = 'Free-lance.ru';
                // Адрес сайта
                $siteURL  = $GLOBALS['host'];
                // Адрес страницы, которая изменилась (например)
                $pageURL  = $siteURL . getFriendlyURL('commune', $message_id);
                // Адрес страницы с фидом
                $feedURL  = $siteURL . '/rss/commune.php?id=' . $id;
                
                // для проверки работоспособности нужно передать реальные страницы со свободным доступом
                // а также раскомментировать EXIT чуть ниже
                //$siteURL  = 'http://www.free-lance.ru';
                //$pageURL  = 'http://www.free-lance.ru/commune/professionalnyie/47/chrnyiy-spisok/2110877/pravila.html';
                //$feedURL  = 'http://www.free-lance.ru/rss/commune.php?id=47';

                // Посылаем challange-запрос
                if (defined('SERVER') && SERVER != 'release') {
//                    echo "отладочный режим для тикета 0019174 <br />";
//                    $pingClient->debug = true;
//                    $res = $pingClient->query('weblogUpdates.ping', $siteName, $siteURL, $pageURL, $feedURL);
//                    echo '<br /> [' .  $pingClient->getErrorCode().'] '.$pingClient->getErrorMessage();
                    //exit;
                } else {
                    ob_start();
                    $res = $pingClient->query('weblogUpdates.ping', $siteName, $siteURL, $pageURL, $feedURL);
                    ob_end_clean();
                }
                
            }; 
            header ('Location: '.getFriendlyURL('commune_commune', $id).($query_string ? "?{$query_string}" : "").'#o'.$message_id);
//            exit;
          }
          else
          {
//            if($action=='do.Create.post') {
//              if($parent_id) {
//                $sm = new smail();
//                $sm->CommuneNewComment($message_id);
//              }
//            }
            $o = ($top_id==$parent_id && $action=='do.Create.post')? '-last' : ($message_id? $message_id: $parent_id);
            if($o == 0) $o = "";
            require_once($_SERVER['DOCUMENT_ROOT']."/classes/drafts.php");
            if($draft_id) { drafts::DeleteDraft($draft_id, get_uid(false), 4); }
            header("Location: /commune/?id={$id}&site=Topic&post={$top_id}".($top_id==$message_id ? '' : ".{$message_id}").($om ? "&om={$om}":'')."&o=$o".($rating ? '&rating='.$rating : '').($o? "#o$o": '')); // Бежим на страницу комментариев.
//            exit;
          }
				}
			}

      break;


    case 'Create' :
    case 'Edit' :
      
      break;


    case 'do.Edit' :
    case 'do.Create' :
      
      $alert = NULL;
      
      $name          = __paramInit('string', NULL, 'name', '', commune::NAME_MAX_LENGTH*2); // внешний substr по размеру поля в базе.
      $descr         = __paramInit('string', NULL, 'descr', '', commune::DESCR_MAX_LENGTH);
      $group_id      = __paramInit('int', NULL, 'group_id'); 
      $author_id     = __paramInit('int', NULL, 'author_id'); 
      $author_login  = __paramInit('string', NULL, 'author_login'); 
      $restrict_join = __paramInit('int', NULL, 'restrict_join'); 
      $restrict_read = __paramInit('int', NULL, 'restrict_read'); 

      $file = isset($_FILES['file']) ? new CFile($_FILES['file']) : '';
     	$rt = 0;

      if(!$author_id || $action=='do.Create')
        $author_id = $uid;

      if(!$author_login || $action=='do.Create')
        $author_login = $_SESSION['login'];

      if($restrict_join)
        $rt |= commune::RESTRICT_JOIN_MASK;

      if($restrict_read)
        $rt |= commune::RESTRICT_READ_MASK;

      $request['name'] = $name;
      $request['descr'] = $descr;
      $request['group_id'] = $group_id;
      $request['restrict_type'] = $rt;
      $request['author_id'] = $author_id;
      $request['author_login'] = $author_login;
      $request['small'] = 0;
      $small=0;   // в commune small пока не используется. Просто заглушка.

      $image_name = NULL;

      if($file && $file->name) {
        $comm_exts = explode(',', commune::IMAGE_EXTENSIONS);
        $file_ext = $file->getext();
        if(in_array($file_ext, $comm_exts))
        {
          if(!($err = tryLoadFile($file, 'upload', $author_login, $image_name, $small,
                                  commune::IMAGE_MAX_WIDTH, commune::IMAGE_MAX_HEIGHT, commune::FILE_MAX_SIZE, 1)))
            $request['small'] = $small;
          else {
            $image_name = NULL;
            $alert['image'] = $err;
          }
        }
        else
          $alert['image'] = 'Недопустимый тип файла.';
      }

      if(!$name)
        $alert['name'] = 'Это поле не должно быть пустым.';

      if(!($descr_len = strlen(stripslashes($_POST['descr']))))
        $alert['descr'] = 'Это поле не должно быть пустым.';

      if(!$group_id)
        $alert['group_id'] = 'Укажите раздел.';

      if($descr_len > commune::DESCR_MAX_LENGTH)
        $alert['descr'] = 'Количество символов превышает допустимое.';
      $descr = substr($descr, 0, commune::DESCR_MAX_LENGTH*2); // размер поля в базе.

      if($alert)
        $commune_output = 'create.php';
      else
        if(commune::CreateCommune($group_id, $author_id, $name, $descr, $image_name, $rt, $small, $id)) {
          if($action=='do.Create')
            header("Location: /commune/?om=".commune::OM_CM_MY);
          else
            header("Location: /commune/?id={$id}");
          exit;
        }

      break;


    case 'Join' :

      $user_login = __paramInit('string', NULL, 'login', 0); 
      $fromPage = urldecode(__paramInit('string', 'fp', 'fp'));
      $out = __paramInit('int', NULL, 'out', 0); 
      $curr_login = get_login($uid);

      if($user_login != $curr_login) {
        header("Location: /404.php");
        exit;
      }

      if(!$out) {
        if($user_mod & commune::MOD_COMM_ACCEPTED ) {
          header("Location: /commune/?id={$id}");
          exit;
        }
      }

      $result = commune::Join($id, $uid, $out); // commune::JOIN_STATUS_ACCEPTED
      if($result==commune::JOIN_STATUS_ASKED) {
        $sm = new smail();
        $sm->CommuneJoinAction($uid, $comm);
      }

//      if(!$result) {
        if($out){
            header('Location: /'.($fromPage ? $fromPage : 'commune/'));
        }else{
            header("Location: /commune/?id={$id}");
        }
//        if(!$out) header("Location: /commune/?id={$id}");
//        else      header('Location: '.($fromPage ? $fromPage : '/commune/'));
//        exit;
//      }

      $commune_output = 'join.php';

      break;


      case 'Delete' :
        
        if(commune::Delete($id)) {
          header("Location: /commune/");
          exit;
        }

      break;

  }

  if(empty($additional_header)) $additional_header = '';
      $om_clean_uri = array();
      if(!empty($_GET['id'])) $om_clean_uri[] = 'id='.(int)$_GET['id'];
      if(!empty($_GET['site'])) $om_clean_uri[] = 'site='.(string)$_GET['site'];
      if(!empty($_GET['post'])) $om_clean_uri[] = 'post='.(int)$_GET['post'];
      if(count($om_clean_uri)) $additional_header .= '
<link rel="canonical" href="/commune/?'.htmlspecialchars(implode('&',$om_clean_uri)).'"/>
';


  include ("../template2.php");






// Рабочие функции -------------------------------------------------------------



    function tryLoadFile(&$files, $dir, $login, &$file_name, &$is_smalled, $max_img_width = 0, $max_img_height = 0, $max_size = 2097152, $is_fix_size = 0) {
        
        if(!is_array($files)) {
            $files = array(0=>$files);
        } else {
            $is_array = true;
        }
        //$files = $file; //unset($file);
        foreach($files as $file) {
            $error              = NULL;
        	$is_smalled         = 0;
        	$file->max_size     = $max_size;
            $file->resize       = 0;
            $file->proportional = 1;
            
            if($is_array) {
                $file->server_root  = 1;
                $file->table        = 'file_commune';
                $path_dir           = "users/".substr($login, 0, 2)."/".$login."/".$dir."/";
                $file_name          = $file->MoveUploadedFile($path_dir);
            } else {
                $file_name = $file->MoveUploadedFile($login."/".$dir);
            }
            
            $error             .= $file->StrError('<br />');
        	
            if(!$error) {
                $ext = $file->getext();
                if(in_array($ext, $GLOBALS['graf_array']) && $ext != "swf" && $ext != "flv" && $max_img_width && $max_img_height) {
                    if(!$file->image_size['width'] || !$file->image_size['height']) {
                        return "Невозможно уменьшить картинку.";
                    }
                    if($file->image_size['width'] > $max_img_width || $file->image_size['height'] > $max_img_height) {
                        if($is_fix_size) {
                            $file->Delete($file->id);
                            return "Размеры картинки превышают максимально допустимые: {$max_img_width}x{$max_img_height} px.";
                        }
                        $smfile = clone $file;
                        if(!$smfile->img_to_small("sm_".$file_name,array('width'=>$max_img_width, 'height'=>$max_img_height))) {
                            $error = "Невозможно уменьшить картинку.";
                        } else {
                            $is_smalled = 1;
                        }
                    }
                } else {
                    $is_smalled = 0;
                }
                
                $file->is_smalled = $is_smalled;
            }
            
            if($error) return $error; // Если есть ошибка дальше загружать файлы бессмысленно
        }
        
        return $error;
    /*} else {
        $error = NULL;
    	$is_smalled = 0;
    
    	$files->max_size = $max_size;
        //$file->max_image_size = array('width'=>$max_img_width, 'height'=>$max_img_height, 'less'=>$is_fix_size);
        $files->resize = 0;
        $files->proportional = 1;
        $file_name = $files->MoveUploadedFile($login."/".$dir);
        $error .= $files->StrError('<br />');
    	
        if(!$error) 
        {
          $ext = $files->getext();
          if(in_array($ext, $GLOBALS['graf_array'])
             && $ext != "swf"
             && $ext != "flv"
             && $max_img_width
             && $max_img_height)
          {
    
            if(!$files->image_size['width'] || !$files->image_size['height'])
              return "Невозможно уменьшить картинку.";
    
            if($files->image_size['width'] > $max_img_width || $files->image_size['height'] > $max_img_height)
            {
              if($is_fix_size) {
                $files->Delete($files->id);
                return "Размеры картинки превышают максимально допустимые: {$max_img_width}x{$max_img_height} px.";
              }
              if(!$files->img_to_small("sm_".$file_name,array('width'=>$max_img_width, 'height'=>$max_img_height)))
                $error = "Невозможно уменьшить картинку.";
              else
                $is_smalled = 1;
            }
          }
          else
            $is_smalled = 0;
        }
    
        return $error;
    }*/
  }




  function getBookmarksStyles($bmCnt, $curPos)
  {
    $arr = NULL;
    for($i=0; $i<$bmCnt; $i++)
      $arr[$i] = ($curPos==$i ? ' b-menu__item_active' : false);// (!$i ? 'user_menu_l' : ($curPos == $i-1 ? 'user_menu_la' : 'user_menu')));
    $arr[] = $curPos==$bmCnt-1 ? 'lmenu_activ_r.gif' : 'lmenu_passiv_r.gif';
    return $arr;
  }


	// stdf
//  function array2tree(
  function transformArray2Tree(
	 $array, // исходный массив, содержащий неупорядоченные узлы дерева. Каждый
	         // узел должен иметь идентификатор и знать идентификатор своего родителя.
	 $id_name, // имя члена, который хранит идентификатор узла.
	 $parent_name, // имя члена, который хранит идентификатор родительского узла.
	 $top_id=NULL, // идентификатор корня дерева. Может быть NULL. Используется для получения
	               // первого уровня дерева.
	 $return_mode = 'FULL') // режим возврата. SIMPLE || FULL.
	// Все исходные идентификаторы -- целые числа. Ид. узла не может быть NULL.
	// $array -- неассоциативный массив, узлы должны быть доступны через простой индекс
	// в непрерывном диапазоне [0, count($array)-1]. А вот узлы -- это уже ассоциативные массивы.
  // Получаем упорядоченное дерево в виде списка: i:level|j:level ... k:level,
  // где i, j, ..., k -- индекс узла, соответствующий индексу элемента массива $array;
  // level -- уровень вложенности, начиная с 1.
  // SIMPLE:
	// Преобразуем список в такой, примерно, массив:
	//
	//   $tree[0]='k:1'       --> нужно взять элемент $array[k]
	//   $tree[1]='i:1'       --> нужно взять элемент $array[i]
	//     $tree[2]='j:2'     --> нужно взять элемент $array[j]
	//   ...
	//       $tree[n]='q:3'   --> нужно взять элемент $array[q]
	//     $tree[n+1]='p:2'   --> нужно взять элемент $array[p]
	//
	// То есть, пробегая по массиву с начала до конца, нужно просто извлекать нужный элемент
	// из исходного массива по индексу, хранящемуся в $tree.
  // FULL:
	// Немного обработаем дерево, пройдемся еще раз по массиву.
	// Возвращается целиком копия исходного массива, только упорядоченная и
	// дополненная информацией о каждом узле: уровень узла (level) и
	// флаг "последний в поддереве" (is_last). Поддерево -- это вся ветвь, корнем которой является
	// элемент с уровнем вложенности 1. Причем, последний в поддереве, имеется в виду самый
  // левый (мысленно поверни поддерево корнем к верху), а не самый удаленный.
	{
  	if(empty($array))
			return $array;


  	$i=0;
    $xml = "<ROOT>";
    foreach($array as $a) $xml .= "<node idx='".($i++)."' id='".$a[$id_name]."' pid='".$a[$parent_name]."'/>";
  	$xml .= "</ROOT>";
  	$xmlDoc = new DOMDocument;
  	$xslDoc = new DOMDocument;
  	$xmlDoc->loadXML($xml);
  	$xslDoc->loadXML(
  	"<?xml version='1.0' encoding='windows-1251'?> 
  	 <xsl:transform xmlns:xsl='http://www.w3.org/1999/XSL/Transform' version='1.0'>
  	 <xsl:output method='text' encoding='windows-1251'/>
  	 <xsl:key name='children' match='node' use='@pid'/>
  	 <xsl:template match='/'>
  		 <xsl:apply-templates select='ROOT/node[@pid=".($top_id===NULL ? "''" : $top_id)."]'/>
  	 </xsl:template>
  	 <xsl:template match='node'>
  		 <xsl:param name='level' select='1'/>
  		 <xsl:value-of select=\"concat(@idx,':',\$level,'|')\"/>
  		 <xsl:apply-templates select=\"key('children', @id)\">
  			 <xsl:with-param name='level' select='\$level + 1'/>
  		 </xsl:apply-templates>
  	 </xsl:template>
  	 </xsl:transform>"
  	);
  	$xslProcessor = new XSLTProcessor;
  	$xslProcessor->importStyleSheet($xslDoc);
  	$tree = $xslProcessor->transformToXML($xmlDoc);
		

    // SIMPLE
  	$tree = split("\|",substr($tree,0,strlen($tree)-1));
		if($return_mode=='SIMPLE')
			return $tree;


    // FULL
		$tree_array = array();

    if($len = count($tree))
     	list($nidx, $nlevel) = split(":",$tree[0]);

    for($i=0; $i<$len; $i++)
		{
     	$level=$nlevel;
			$tree_array[$i] = $array[$nidx];
			$tree_array[$i]['level'] = $level;
     	list($nidx,$nlevel) = split(":",$tree[$i+1]);
			$tree_array[$i]['is_last'] = (int)($nlevel==1);
		}

		return $tree_array;
  }
?>