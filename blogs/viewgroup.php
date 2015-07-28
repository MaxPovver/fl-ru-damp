<?
$g_page_id = "0|6";
$grey_blogs = 1;
$stretch_page = true;
$showMainDiv  = true;
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/blogs.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/links.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/drafts.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stat_collector.php");
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );
session_start();
$uid = get_uid();

//$stop_words = new stop_words( hasPermissions('blogs') );
$js_file = array();

foreach($_GET as $key=>$value) {
    $_GET[$key] = strip_tags($value);
}
if(!$allow_love) {
   $_GET['gr'] = str_replace('55', '2', $_GET['gr']);
   $_POST['category'] = str_replace('55|', '2|', $_POST['category']);
}
else {
    $js_file[] = 'timer.js';
}

if(isset($_GET['pagefrom'])) {
    $_GET['pagefrom'] = intval($_GET['pagefrom']);
}
if(isset($_POST['pagefrom'])) {
    $_POST['pagefrom'] = intval($_POST['pagefrom']);    
}

$_SESSION["prj_ref_link"]='';
$rpath="../";
$ord = __paramInit('string', 'ord');
$ord_get_part = $ord && $ord != 'new' ? '&ord='.$ord : '';
$gr = intval(trim($_GET['gr']));
$tr = __paramInit('int', 'tr');
//if (!$gr) { $gr = 0; $g_page_id = "0|6";} else $g_page_id = "2|".$gr;
$t = 0;//trim($_GET['t']);

if($_GET['gr'] && !$_GET['newurl'] && !$_POST) {
        $query_string = preg_replace("/gr=".$_GET['gr']."/", "", $_SERVER['QUERY_STRING']);
        $query_string = preg_replace("/^&/", "", $query_string);
        $friendly_url = getFriendlyURL("blog_group", $_GET['gr']);
        if(trim($friendly_url) == "") {
            $friendly_url= "/404.php";
        } else {
            $friendly_url = $friendly_url . ($query_string ? "?{$query_string}" : "");
        }
        header ('HTTP/1.1 301 Moved Permanently');
        header ('Location: ' . $friendly_url);
    exit;
}

if (($_GET['gr'] || $_GET['gr'] === '0') && $PDA && !$_GET['action']) {
    $query_string = preg_replace("/gr=".$_GET['gr']."/", "", $_SERVER['QUERY_STRING']);
    $query_string = preg_replace("/^&/", "", $query_string);
    $query_string = $query_string ? "?{$query_string}" : "";
    $locUrl = $_GET['gr'] === '0' ? '/blogs/' : getFriendlyURL("blog_group", $_GET['gr']);
    if(trim($locUrl) == "") {
        $locUrl = "/404.php";
    } else {
        $locUrl = $locUrl . $query_string;
    }
    header ('HTTP/1.1 301 Moved Permanently');
    header ('Location: '.$locUrl);
    exit;
}

$gr = blogs::getGroupId($_GET['grname']);
if(!$gr && $_GET['grname']) {
    header("Location: /404.php");
    exit;
} else if( BLOGS_CLOSED == true) {
    $commune_category = $gr + 1000;
    $ord2om = array('best' => 1, 'relevant' => 2, 'my' => 3);
    $http_query = "";
    if(isset($_GET['ord']) && isset($ord2om[$_GET['ord']])) {
        $query['om'] = $ord2om[$_GET['ord']]; 
    }
    if($commune_category > 1000) {
        $query['cat'] = $commune_category;
    }
    if(isset($query)) {
        $http_query = "?" . http_build_query($query);
    }
    $url_redirect = REDIRECT_BLOG_URL . $http_query;
    if(trim($url_redirect) == "" ) {
        $url_redirect = "/404.php";
    } 
    header("Location: " . $url_redirect);
    exit;
}

$action = trim($_POST['action']);
if (!$action) $action = trim($_GET['action']);

if (!$t) $base = 0; else $base = 1;

$mod = (hasPermissions('blogs'))? 0 : 1;
$blog_obj = new blogs();

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");

if ($_SESSION['uid']) {
$user = new users();
$ban_where=$user->GetField($_SESSION['uid'],$error,"ban_where");
}
else { $ban_where=0; }

$draft_id = __paramInit('int', 'draft_id', 'draft_id');
if ( empty($draft_id) ) {
    $draft_id = null;
}

if($PDA) $blogspp = 20; // Для ПДА выводим 5

if ($ban_where != 1)
switch ($action){
    case "new_tr":
        if(!get_uid()) {
            header("Location: /fbd.php");
            die();
        }
        $alert = array();
        $categ = trim($_POST['category']);
        
        if(!isset($_POST['ontop'])) {
            $ontop = 'f';
        } else {
            $ontop = ($_POST['ontop'] == 't') ? 't' : 'f';
        }
        
        list ($gr,$t) = explode("|", $categ);
        
        $site = __paramInit('string', NULL, 'site');
        //$name = substr(change_q_x($_POST['name'], true), 0, 96);
        //$msg = change_q_x($_POST['msg'], false);
        if (strlen($_POST['msg']) > blogs::MAX_DESC_CHARS) {
            $error_flag = 1;
            $alert[2] = "Максимальный размер сообщения ".blogs::MAX_DESC_CHARS." символов!";
            $msg =& $_POST['msg'];
        } else {
        	if ((trim($_POST['question']) != '')&&is_array($_POST['answers'])) {
        	    $variantExists = false;
        	    foreach ($_POST['answers'] as $answer) {
        	        if (trim($answer) != '') {
        	            $variantExists = 1;
        	            break;
        	        }
        	    }
        	    if ($variantExists) {
        	         $msg = change_q_x(antispam($_POST['msg']), false, false);
        	    } else {
        	        
        	    } 
        	} else {
                $msg = change_q_x(antispam($_POST['msg']), false, false, 'b|br|i|p|ul|li|cut|s|h[1-6]{1}', false, false);
        	}
        }
        $msg_name = $name = substr_entity(change_q_x(antispam($_POST['name']), true, false), 0, 96, true);
        $yt_link = $_POST['yt_link'];
        if ($yt_link != '') {
            $v_yt_link = video_validate($yt_link);
            if(!$v_yt_link) {
                $error_flag = 1; $alert[4] = "Неверная ссылка.";
            } else {
                $yt_link = $v_yt_link;
            }
        }

		// опросы
		$multiple = (bool) $_POST['multiple'];
		$question = '';
		$answers  = array();
		$question = substr_entity(change_q_x( antispam( preg_replace('/&/','&amp;',trim((string) $_POST['question'])) ), false, false, ''), 0, blogs::MAX_POLL_CHARS, true);
		$i = 0;
		if($_POST['answers']) {
    		foreach ($_POST['answers'] as $pa) {
    			if (trim((string) $pa) !== '') {
    				$answers[] = substr_entity(change_q_x(antispam( preg_replace('/&/','&amp;',trim((string) $pa)) ) , false, false, ''), 0, blogs::MAX_POLL_ANSWER_CHARS * 2, true);
    				$i++;
    			}
    		}
		}
		if ($i > 0 && $question === '') {
			$error_flag = 1;
			$alert[5] = 'Введите текст вопроса';
		} else if ($i > blogs::MAX_POLL_ANSWERS) {
			$error_flag = 1;
			$alert[5] = 'Вы можете указать максимум ' . blogs::MAX_POLL_ANSWERS . ' отетов';
		} else if ($i < 2 && $question !== '') {
			$error_flag = 1;
			$alert[5] = 'Нужно указать минимум 2 варианта ответа в голосовании';
		}
		
        if(!($gr = intvalPgSql($gr))) {
          header("Location: /404.php");
          exit;
        }
        if(is_empty_html($msg))
          $msg='';

        if ($_POST['close_comments']) $close_comments = "t"; else $close_comments = "f";
        if ($_POST['is_private']) $is_private = "t"; else $is_private = "f";

        if (!$t) $base = 0; else $base = 1;

        // загрузка файлов
        $attach = $_FILES['attach'];
        $files  = array();
        $countfiles = 0;
        
        if (is_array($attach) && !empty($attach['name'])) {
            $nTotalSize = 0;
            
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
                
                if ( $nTotalSize > blogs::MAX_FILE_SIZE ) {
                	$error_flag = 1;
                	$alert[3]   = 'Максимальный объем прикрепленных файлов: ' . (blogs::MAX_FILE_SIZE / (1024*1024))." Мб";
                	break;
                }
                if(in_array($files[0]->getext($attach['name'][$key]), $GLOBALS['disallowed_array'])) {
                    $error_flag = 1;
                    $alert[3] = "Недопустимый формат файла";
                    break;
                }
            }
        }
        
        if ( count($files) > blogs::MAX_FILES ) { 
			$error_flag = 1; 
			$alert[3]   = "Максимальное кол-во файлов для загрузки: " . blogs::MAX_FILES;
		}

        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/attachedfiles.php");
        if(!$_POST['attachedfiles_session']) {
            $attachedfiles = new attachedfiles('', true);
            $asid = $attachedfiles->createSessionID();
            $attachedfiles->addNewSession($asid);
        } else {
            $attachedfiles = new attachedfiles($_POST['attachedfiles_session']);
            $asid = $_POST['attachedfiles_session'];
        }
        $attachedfiles_info = $attachedfiles->calcFiles();

        if ($msg==='' && $question==='' && empty($alert[5]) && !$countfiles && $yt_link==='' && !$attachedfiles_info['count']) {
            $error_flag = 1; $alert[2] = "Поле заполнено некорректно";
        }
       
        if (($msg!=='' || $attach['name'][0] || $question!=='' || $yt_link!='' || $attachedfiles_info['count']) && get_uid() && !$error_flag) {
			list($alert1, $error_flag, $error, $msg_id, $th_id, $id_gr) = $blog_obj->NewThread(get_uid(), $gr, $base, (string)$name, (string)$msg, $files, getRemoteIP(), $mod, 0, NULL, $yt_link, $close_comments, $is_private, $ontop, $question, $answers, $multiple);

            if(!($alert1 || $error || $error_flag)) { 
                //$nStopWordsCnt = $stop_words->calculate( $msg, $name, $question, $answers );
                $attachedfiles_files = $attachedfiles->getFiles(array(1,3,4));
                $blog_obj->addAttachedFiles($attachedfiles_files, $msg_id, NULL, ($draft_id ? true : false)); 
                $attachedfiles->clear();
                //$blog_obj->insertIntoModeration( $msg_id, $nStopWordsCnt ); // больше не модерируем
            }

            if($draft_id && !($alert1 || $error || $error_flag)) {
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/drafts.php");
                drafts::DeleteDraft($draft_id, get_uid(false), 3);
            }
		}
        
        if ($alert1) $alert = $alert + $alert1;
        
        if ($site == 'journal')
        {
          unset($_SESSION['user.journal.new_tr.result']);
          $question = str_replace(array('"', "'", "\\", '<', '>'), array('&quot;', '&#039;', '&#92;', '&lt;', '&gt;'), stripslashes($_POST['question']));
		  $answers = array();
		  $answers_exists = array();
		  if (!empty($_POST['answers']) && is_array($_POST['answers'])) {
			foreach ($_POST['answers'] as $key=>$answer) $answers[$key] = str_replace(array('"', "'", "\\"), array('&quot;', '&#039;', '&#92;'), stripslashes((string) $answer));
		  }
		  if (!empty($_POST['answers_exists']) && is_array($_POST['answers_exists'])) {
			foreach ($_POST['answers_exists'] as $key=>$answer_exist) $answers_exists[$key] = str_replace(array('"', "'", "\\"), array('&quot;', '&#039;', '&#92;'), stripslashes((string) $answer_exist));
		  }
		  $requestedCategory = __paramInit("string", null, "category");
		  $requestedCategory = explode("|", $requestedCategory);
		  $requestedCategory = (int)$requestedCategory;
		  if ($alert || $error || $error_flag) {
            $_SESSION['user.journal.new_tr.result'] = array(
				'alert' => $alert, 
				'error' => $error, 
				'title' => str_replace(array('"', "'", "\\", '<', '>'), array('&quot;', '&#039;', '&#92;', '&lt;', '&gt;'), stripslashes($_POST['name'])), 
				'msgtext' => str_replace(array('"', "'", "\\", '<', '>'), array('&quot;', '&#039;', '&#92;', '&lt;', '&gt;'), stripslashes($_POST['msg'])), 
				'yt_link' => $yt_link, 
				'answers' => $answers,
				'answers_exists' => $answers_exists
			);
			if (isset($_POST['question'])) $_SESSION['user.journal.new_tr.result']['question'] = $question;
			if ($requestedCategory == 7) {
                header("Location: /users/".$_SESSION['login']."/journal/");
                exit;
            }
          }
          unset($name);
          unset($msg);
          require_once(ABS_PATH . "/classes/yaping.php");
          $yaping = new yaping(); //??? не используется
          //$out = $yaping->doping($gr);
          if ($requestedCategory == 7) {
              header("Location: /users/".$_SESSION['login']."/journal/?tr=-last");
              exit;
          }
        }

        if (!$error && !$error_flag){
            unset($name);
            unset($msg);
            unset($action);
            //if ($gr == 7) header("Location: /users/".$_SESSION['login']."/journal/");
            //else header("Location: /blogs/viewgroup.php?gr=$gr&t=$t");
            require_once(ABS_PATH . "/classes/yaping.php");
            $yaping = new yaping();
            //$out = $yaping->doping($gr);
            
            header( "Location: ".getFriendlyURL("blog_group", $id_gr)."?t=$t{$ord_get_part}&tr=-last". ($PDA ? "#th_{$th_id}" : '') );
            //header("Location: /blogs/viewgroup.php?gr=$gr&t=$t{$ord_get_part}&tr=-last".($PDA ? "#th_{$th_id}" : ''));exit;
            exit;
        }
        break;
    case "restore":
        $id = intval(trim($_GET['id']));
        $uid = get_uid();
        if(!$uid || !hasPermissions('blogs')) {
            header("Location: /fbd.php");
            die();
        }
        $o_gr = $gr;
        if ($id && $uid) $error = $blog_obj->RestoreMsg($uid, $id, $gr, $base, $null, $page, $from, $mod);
        if(!($page   = __paramInit('int', 'page')))
            $page = 1;
        if ($page < 0) $page = 1;     
        if ($base) $t = "prof";
        if ($site == 'journal' || $_GET['site'] == 'journal') {
          header("Location: /users/".$_SESSION['login'].(is_emp()? "/setup": "")."/journal/?page={$page}");
          exit;
        }
        if ($_GET['site'] == 'siteadmin' && hasPermissions('blogs')) {
            //header("Location: /siteadmin/ban-razban/?mode=blogs&p={$_GET['p']}&sort={$_GET['sort']}&admin={$_GET['admin']}&admin={$_GET['search']}");
            header("Location: /siteadmin/ban-razban/?mode=blogs".($_GET['p']? "&p={$_GET['p']}": "").($_GET['p']? "&sort={$_GET['sort']}": "").($_GET['search']? "&search={$_GET['search']}": "").($_GET['admin']? "&admin={$_GET['admin']}": ""));
            exit;
        }
        require_once(ABS_PATH . "/classes/yaping.php");
		$yaping = new yaping();
		//$out = $yaping->doping($gr);
        $gr = ($ord == 'my')? $o_gr: $gr;
        if($_GET['r']) {
            header("Location: ".$_SERVER["HTTP_REFERER"]);
        } else {
            header("Location: ".getFriendlyURL("blog_group", $gr)."?".($t ? "&t=$t" : '').($page>1 ? "&page={$page}" : '')."{$ord_get_part}");
        }
        exit();
        break;
    case "delete":
        $id = intval(trim($_GET['id']));
        $token = $_GET['u_token_key'];
        $uid = get_uid();
        if(!$uid || $token != $_SESSION['rand']) {
            header("Location: /fbd.php");
            die();
        }
        $o_gr = $gr;
        if ($id && $uid) $error = $blog_obj->MarkDeleteBlog($uid, $id, $gr, $base, $null, $page, $from, $mod);
        if(!($page   = __paramInit('int', 'page')))
            $page = 1;
        if ($page < 0) $page = 1;    
        if ($base) $t = "prof";
        if ($site == 'journal' || $_GET['site'] == 'journal') {
          header("Location: /users/".$_SESSION['login'].(is_emp()? "/setup": "")."/journal/?page={$page}");
          exit;
        }
        if ($_GET['site'] == 'siteadmin' && hasPermissions('blogs')) {
            //header("Location: /siteadmin/ban-razban/?mode=blogs&p={$_GET['p']}&sort={$_GET['sort']}&admin={$_GET['admin']}&admin={$_GET['search']}");
            header("Location: /siteadmin/ban-razban/?mode=blogs".($_GET['p']? "&p={$_GET['p']}": "").($_GET['p']? "&sort={$_GET['sort']}": "").($_GET['search']? "&search={$_GET['search']}": "").($_GET['admin']? "&admin={$_GET['admin']}": ""));
            exit;
        }
        require_once(ABS_PATH . "/classes/yaping.php");
		$yaping = new yaping();
		//$out = $yaping->doping($gr);
        $gr = ($ord == 'my')? $o_gr: $gr;
        $back_gr = __paramInit('string', 'back_gr'); // в какую группу вернуться после удаления
        if ($back_gr !== null) $gr = $back_gr;
        if($_GET['r']) {
            header("Location: ".$_SERVER["HTTP_REFERER"]);
        } else {
            header("Location: ".getFriendlyURL("blog_group", $gr)."?".($t ? "&t=$t" : '').($page>1 ? "&page={$page}" : '')."{$ord_get_part}");
        }
        exit();
        break;

    /*case "warn":
        if (hasPermissions('blogs')) {
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/messages.php");
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
            $usr=new users();
            $usr->Warn($_GET["ulogin"]);
            $id = intval(trim($_GET['id']));
            $uid = get_uid();

            messages::SendWarn($_GET["ulogin"],$id);

            //  if ($id && $uid) $error = $blog_obj->DeleteMsg($uid, $id, $gr, $base, $null, $page, $from, $mod);
            if ($base) $t = "prof";
            //if ($gr == 7) header("Location: /users/".$_SESSION['login']."/journal/");
            //else header("Location: /blogs/viewgroup.php?gr=$gr&t=$t");
            header("Location: /blogs/viewgroup.php?gr=$gr&t=$t&ord=$ord");
            exit;
        }
        break;
    */
        
    case "change": 
        if(!get_uid()) {
            header("Location: /fbd.php");
            die();
        }
        
        $alert = array();
        $categ = trim($_POST['category']);
        list ($gr,$t) = explode("|", $categ);
        
        /*
        $gr = intval(trim($_POST['gr']));
        $t = trim($_POST['t']);
        */

        $thread    = __paramInit('int', NULL, 'thread');
        $thread_id = __paramInit('int', NULL, 'thread_id');
        $page      = __paramInit('int', NULL, 'page');
        $site      = __paramInit('string', NULL, 'site');
        $sort      = __paramInit('string', NULL, 'sort');
        $search    = __paramInit('string', NULL, 'search');
        $admin     = __paramInit('int', NULL, 'admin');
        $prev_gr   = __paramInit('int', 'gr');
        
        if (blogs::isTopicDeleted($thread)) {
            $error_flag = 1;
            $alert[2] = "Нельзя редактировать удаленный топик!";
        }
        
        if ( hasPermissions('blogs') ) {
        	$ontop = ( isset($_POST['ontop']) && $_POST['ontop'] == 't') ? 't' : 'f';
        }
        else {
            $ontop = null;
        }

        //$msg = change_q_x($_POST['msg'], false);
        if (strlen($_POST['msg']) > blogs::MAX_DESC_CHARS) {
            $error_flag = 1;
            $alert[2] = "Максимальный размер сообщения ".blogs::MAX_DESC_CHARS." символов!";
            $msg =& $_POST['msg'];
        } else {
            if ((trim($_POST['question']) != '')&&is_array($_POST['answers_exists'])) {
        	    $variantExists = false;
        	    foreach ($_POST['answers_exists'] as $answer) {
        	        if (trim($answer) != '') {
        	            $variantExists = 1;
        	            break;
        	        }
        	    }
        	    if ($variantExists) {
        	        $msg = change_q_x(antispam($_POST['msg']), false, false);
        	    }else {
        	        $msg = change_q_x(antispam($_POST['msg']), false, false, 'b|br|i|p|ul|li|cut|s|h[1-6]{1}', false, false);
        	    }
            } else {
                $msg = change_q_x(antispam($_POST['msg']), false, false, 'b|br|i|p|ul|li|cut|s|h[1-6]{1}', false, false);
            }
        }
        $yt_link = $_POST['yt_link'];
        if ($yt_link != '') {
            $v_yt_link = video_validate($yt_link);
            if(!$v_yt_link) {
                $error_flag = 1; $alert[4] = "Неверная ссылка.";
            } else {
                $yt_link = $v_yt_link;
            }
        }
		
		// опросы
		$question = substr_entity(change_q_x( antispam( preg_replace('/&/','&amp;',trim((string) $_POST['question'])) ), false, false, ''), 0, blogs::MAX_POLL_CHARS, true);
		$answers = array();
		$answers_exists = array();
		$multiple = (bool) $_POST['multiple'];
		if (is_array($_POST['answers']) && !empty($_POST['answers'])) {
			$i = 0;
			foreach ($_POST['answers'] as $pa) {
                if (trim((string) $pa) !== '') {
					$answers[] = substr_entity(change_q_x(antispam( preg_replace('/&/','&amp;',trim((string) $pa)) ), false, false, ''), 0, blogs::MAX_POLL_ANSWER_CHARS * 2, true);
					$i++;
				}
			}
		}
		if (is_array($_POST['answers_exists']) && !empty($_POST['answers_exists'])) {
			foreach ($_POST['answers_exists'] as $key=>$pa) {
                if (trim((string) $pa) !== '') {
					$answers_exists[$key] = substr_entity(change_q_x(antispam( preg_replace('/&/','&amp;',trim((string) $pa)) ), false, false, ''), 0, blogs::MAX_POLL_ANSWER_CHARS * 2, true);
					$i++;
				}
			}
		}
		if ($i > 0 && $question === '') {
			$error_flag = 1;
			$alert[5] = 'Введите текст вопроса';
		} else if ($i > blogs::MAX_POLL_ANSWERS) {
			$error_flag = 1;
			$alert[5] = 'Вы можете указать максимум ' . blogs::MAX_POLL_ANSWERS . ' ответов';
		} else if ($i < 2 && $question !== '') {
			$error_flag = 1;
			$alert[5] = 'Нужно указать минимум 2 варианта ответа в голосовании';
		}
        if(!($gr = intvalPgSql($gr)) && !( $ord == 'my' || $PDA)) {
        	include ABS_PATH."/404.php"; exit;
        }
        
        if(is_empty_html($msg)) $msg='';
        
        if ($_POST['close_comments']) $close_comments = "t"; else $close_comments = "f";
        if ($_POST['is_private']) $is_private = "t"; else $is_private = "f";

        $msg_name = substr_entity(change_q_x(antispam($_POST['name']), true, false), 0, 96, true);
        $attach = $_FILES['attach'];

        $attach_delete= is_array($_POST["delattach"])? $_POST['delattach']: array();
        $attach_have=$_POST["have_attach"];

        $olduser=intval($_POST["olduser"]);
        $us=new users();
        $logins=$us->GetName($olduser,$error);
        $olduserlogin=$logins["login"];
        if (!$olduserlogin) { break; }

        
        if (!$t) $base = 0; else $base = 1;
        
        $attach = $_FILES['attach'];
        $files  = array();
        
        if (is_array($attach) && !empty($attach['name'])) {
            $nTotalSize = 0;
            $aAttach    = blogs::GetAttach( $thread, $attach_delete );
            
            if ( is_array($aAttach) && count($aAttach) ) {
                $dir = 'users/'.substr($olduserlogin, 0, 2)."/$olduserlogin/upload/";
                
            	foreach ( $aAttach as $sFile ) {
            	    $cfile = new CFile( $dir . $sFile );
            	    $nTotalSize += $cfile->size;
            	}
            }
            
            foreach ($attach['name'] as $key=>$v) {
                if ($attach['name'][$key]) {
                    $countfiles++;
                    $files[] = new CFile(array(
                        'name'     => $attach['name'][$key],
                        'type'     => $attach['type'][$key], 
                        'tmp_name' => $attach['tmp_name'][$key], 
                        'error'    => $attach['error'][$key], 
                        'size'     => $attach['size'][$key]
                    ));
                    
                    $nTotalSize += $attach['size'][$key];
                    
                    if ( $nTotalSize > blogs::MAX_FILE_SIZE ) {
                    	$error_flag = 1;
                    	$alert[3]   = 'Максимальный объем прикрепленных файлов: ' . (blogs::MAX_FILE_SIZE / (1024*1024))." Мб";
                    	break;
                    }
                }
            }
        }
        
        $countfiles = blogs::GetAttachCount($thread) + count($files) - count($attach_delete) ;
        
        if ( $countfiles > blogs::MAX_FILES) {
            $error_flag = 1;
            $alert[3] = "Максимальное кол-во файлов для загрузки: " . blogs::MAX_FILES;
        }

        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/attachedfiles.php");
        $attachedfiles_session = $_POST['attachedfiles_session'];

        if(!$attachedfiles_session) {
            $attachedfiles = new attachedfiles('', true);
            $asid = $attachedfiles->createSessionID();
            $attachedfiles->addNewSession($asid);
            $attachedfiles_session = $asid;
        } else {
            $attachedfiles = new attachedfiles($attachedfiles_session);
            $asid = $attachedfiles_session;
        }

        $attachedfiles_info = $attachedfiles->calcFiles();
        if($attachedfiles_session) $countfiles = 0;

        if ( $msg==='' && $question === '' && empty($alert[5]) && !($countfiles || $attachedfiles_info['count']) && $yt_link==='') {
            $error_flag = 1; $alert[2] = "Ошибка. Сообщение не должно быть пустым!";
		} elseif (!$error && !$error_flag && ($msg!=='' || $attach['name'] || $attach_have || $attach_delete || $question || $yt_link || $attachedfiles_info['count'])){
		    $blog_obj->Edit($_SESSION['uid'], $thread, $msg, $msg_name, $files, getRemoteIP(), $err, $mod, NULL, $gr, $t, $attach_delete, $olduserlogin, $yt_link, $close_comments, $is_private, $ontop, null, $question, $answers, $answers_exists, $multiple);
            //$nStopWordsCnt = $stop_words->calculate( $msg, $msg_name, $question, $answers, $answers_exists );
            $attachedfiles_files = $attachedfiles->getFiles(array(1,3,4));
            $blog_obj->addAttachedFiles($attachedfiles_files, $thread, $olduserlogin,($draft_id ? true : false)); 
            $attachedfiles->clear();
            
            //$blog_obj->insertIntoModeration( $thread, $nStopWordsCnt ); // больше не модерируем

		    if ($err) $alert = $alert + $err;
            if($draft_id && !($alert || $error || $error_flag)) {
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/drafts.php");
                drafts::DeleteDraft($draft_id, get_uid(false), 3, true);
            }

        }
		
        if ($site == 'journal')
        {
          unset($_SESSION['user.journal.change.result']);
          if ($alert || $error || $error_flag) {
			$question = str_replace(array('"', "'", "\\", '<', '>'), array('&quot;', '&#039;', '&#92;', '&lt;', '&gt;'), stripslashes($_POST['question']));
			$answers = array();
			$answers_exists = array();
			if (!empty($_POST['answers']) && is_array($_POST['answers'])) {
				foreach ($_POST['answers'] as $key=>$answer) $answers[$key] = str_replace(array('"', "'", "\\"), array('&quot;', '&#039;', '&#92;'), stripslashes((string) $answer));
			}
			if (!empty($_POST['answers_exists']) && is_array($_POST['answers_exists'])) {
				foreach ($_POST['answers_exists'] as $key=>$answer_exist) $answers_exists[$key] = str_replace(array('"', "'", "\\"), array('&quot;', '&#039;', '&#92;'), stripslashes((string) $answer_exist));
			}
            $_SESSION['user.journal.change.result'] = array(
				'alert' => $alert, 
				'error' => $error, 
				'title' => str_replace(array('"', "'", "\\", '<', '>'), array('&quot;', '&#039;', '&#92;', '&lt;', '&gt;'), stripslashes($_POST['name'])), 
				'msgtext' => str_replace(array('"', "'", "\\", '<', '>'), array('&quot;', '&#039;', '&#92;', '&lt;', '&gt;'), stripslashes($_POST['msg'])), 
				'yt_link' => $yt_link,
				'answers' => $answers,
				'answers_exists' => $answers_exists
			);
			if (isset($_POST['question'])) $_SESSION['user.journal.change.result']['question'] = $question;
			header("Location: /users/".$_SESSION['login'].(is_emp()? "/setup": "")."/journal/?action=edit&tr={$thread}&page={$page}");
            exit;
          }
          unset($msg_name);
          unset($msg);
          require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/yaping.php");
          $yaping = new yaping();
          //$out = $yaping->doping($gr);
          header("Location: /users/".$_SESSION['login'].(is_emp()? "/setup": "")."/journal/?tr=$thread&page={$page}");
          exit;
        }
        
        if ($site == 'siteadmin' && hasPermissions('blogs')) {
            if ($alert || $error || $error_flag) {
                $_SESSION['user.siteadmin.change.result'] = array('alert' => $alert, 'error' => $error, 'title' => $msg_name, 'msgtext' => $msg, 'yt_link' => $yt_link);
                header("Location: /siteadmin/ban-razban/?mode=blogs&action=edit&tr={$thread}".($page? "&p={$page}": "").($sort? "&sort={$sort}" : "").($search? "&search={$search}": "").($admin? "&admin={$admin}": ""));
            } else {
                header("Location: /siteadmin/ban-razban/?mode=blogs&tr={$thread}".($page? "&p={$page}": "").($sort? "&sort={$sort}" : "").($search? "&search={$search}": "").($admin? "&admin={$admin}": ""));
            }
            exit;
        }

        if ($error || $error_flag || !isNulArray($alert)) $action = "edit";
        else {
            unset($msg_name);
            unset($msg);
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/yaping.php");
            $yaping = new yaping();
            
            if ( !$gr ) {
            	$gr = __paramInit('int', 'gr');
            }



            if($ord=="my" || $ord=="favs") {
                $t_ord = ( isset($_SESSION["blogs_{$ord}_ord"]) ) ? $_SESSION["blogs_{$ord}_ord"] : (($ord == "my") ? 'my_all' : 'favs_std');
            } else {
                $t_ord = $ord;
            }
            if($t_ord=='my_comments' || $t_ord=='my_all') {
                $thread_id = $thread;
            }

            // с учетом того, что при смене флага "Запретить комментирование" мы меняем раздел
            // нужно вычислять в какой раздел и на какую страницу редиректить юзера
            if ($prev_gr <> $gr) {
                $aData = blogs::getGroupAndPos( $thread_id, get_uid(false), $t_ord );
                $page  = ceil( $aData['pos'] / $blogspp );
                $gr = $aData['id_gr'];
            }
            
            header( "Location: ".getFriendlyURL("blog_group", $gr)."?t=$t{$ord_get_part}&tr=$thread".($page > 1 ? "&page={$page}" : '') );
            
            exit;
        }

    case "edit":
        if(!get_uid()) {
            header("Location: /fbd.php");
            die();
        }

        $edit_tr = $edit_id = intval(trim($tr));
        
        if ($edit_tr) {
            $edit_msg = $blog_obj->GetMsgInfo($edit_tr, $error, $perm);
            if ($edit_msg['fromuser_id'] != get_uid() && $mod){
                unset($edit_msg);
                unset($action);
                header("Location: ".getFriendlyURL("blog_group", $gr)."?t=$t{$ord_get_part}");
            }
        }
        break;
        
}

stat_collector::setStamp(); // stamp

$blog = new blogs();
//$thread = $blog->GetThread($thread, $error);
if (!$page)        $page = intval(trim($_GET['page']));
if ($page < 0) $page = 1; 
if (!$from)        $from = intval(trim($_GET['from']));
if (!$page) $page = intval(trim($_POST['page']));
if (!$page) $page = 1;
$item_page = intval($_POST['page']);
if (!$item_page) $item_page = intval($_GET['page']);
if (!$item_page) {
    $item_page = 1;
    $bPageDefault = true;
}
$ord = trim($_GET['ord']);
if ($ord != "best" && $ord != "my" && $ord != "relevant" && $ord != "favs") $ord = "new";

// определяем подраздел
if( $ord == "my" || $ord == "favs" ) {
    if ( !get_uid() ) {
        header("Location: /fbd.php");
        exit;
    }
    else {
        $sub_ord = __paramInit( 'string', 'sub_ord', 'sub_ord' );
        
        if ( $sub_ord ) {
            if ( $ord == "my" ) {
            	// вкладка "Мои"
            	$sub_ord = ( in_array($sub_ord, blogs::$nav_my) ) ? $sub_ord : blogs::$nav_my[0];
            }
            else {
                // вкладка "Закладки" (других таких пока нет)
                $sub_ord = ( in_array($sub_ord, blogs::$nav_favs) ) ? $sub_ord : blogs::$nav_favs[0];
            }
            
            $_SESSION["blogs_{$ord}_ord"] = $sub_ord;
        }
        else {
            $sub_ord = ( isset($_SESSION["blogs_{$ord}_ord"]) ) ? $_SESSION["blogs_{$ord}_ord"] : (($ord == "my") ? 'my_all' : 'favs_std');
        }
    }
}
else {
    $sub_ord = $ord;
}

// определяем дополнительные условия сортировки
if ( $_GET['order'] ) {
	$order = $_GET['order'];
	$_SESSION["blogs_{$ord}_order"] = $order ;
}
else {
    $order = ( isset($_SESSION["blogs_{$ord}_order"]) ) ? $_SESSION["blogs_{$ord}_order"] : '';
}

$read_only = "";
$get_ord   = ( $ord == "my" || $ord == "favs" ) ? $sub_ord : $ord;

if ( isset($_GET['gr']) && $_GET['gr'] == '0' ) {
	header("HTTP/1.1 301 Moved Permanently");
	header( 'Location: ' . e_url( 'gr' ) );
	exit(0);
}

$themes = $blog->GetGroup($gr, $gr_name, $num_msgs, $page, $err, get_uid(), $mod, $from, $read_only, $get_ord, false, $order);

$error .= $err;

$pages = ceil( $num_msgs / $blogspp );

if ( 
    ($num_msgs == 0 || $num_msgs - 1 < ($page - 1) * $blogspp) && !$bPageDefault 
    || $pages == 1 && !$bPageDefault 
    || $sub_ord == 'favs_list' && !$bPageDefault 
 ) {
    include( ABS_PATH . '/404.php' );
    exit;
}

if (!$gr_name) {
	include ABS_PATH."/404.php"; exit;
}

$additional_header = "<link rel=\"alternate\" type=\"application/rss+xml\" title=\"Блоги на FL.ru (".$gr_name.")\" href=\"/rss/blogs.php?gr=".$gr."&amp;t=".$t."\" />";

if(empty($additional_header)) $additional_header = '';
      $om_clean_uri = array();
        foreach ($_GET as $key => $value) {
            if($value && !in_array($key, array('grname','b','ord','openlevel','newurl'))) $om_clean_uri[] = $key.'='.urlencode($value);
        }
      if(!empty($_GET['id'])) $om_clean_uri[] = 'id='.(int)$_GET['id'];
      if(count($om_clean_uri) || $gr) $additional_header .= '
<link rel="canonical" href="'.($gr ? getFriendlyURL('blog_group', $gr) : '/blogs/').($om_clean_uri ? '?'.htmlspecialchars(implode('&',$om_clean_uri)) : '').'"/>
';

$additional_header .= '
<script type="text/javascript" src="/scripts/blogs_cnt.js"></script>
<script type="text/javascript" src="/scripts/swfobject.js"></script>
<script type="text/javascript" src="/scripts/polls.js"></script>
';
if ($uid) {
    $additional_header .= '
    <script type="text/javascript" src="/scripts/blogs.js"></script>
    <script type="text/javascript" src="/scripts/mAttach.js"></script>
    <script type="text/javascript" src="/scripts/banned.js"></script>
    ';
    $js_file[] = 'drafts.js';
    $js_file[] = 'attachedfiles.js';
}

$footer = $rpath."footer.html";

// определяем шаблон контента
if($PDA && isset($_GET['editcnt'])) {
    $content = "edit_cnt.php"; // PDA
}
else {
	$groups = $blog->GetThemes($error, 1);
	function getGroupName($gr_id, $base){
		global $groups;
		foreach($groups as $ikey => $theme){
			if ($theme['id'] == $gr_id && $base == $theme['t']){
				$ret = $theme['t_name'];
			}
		}
		return $ret;
	}
    if ( $ord == "favs" ) {
        $content = ($sub_ord == 'favs_std') ? "viewgr_cnt.php" : 'favlist_cnt.php';
    }
    else {
        $content = "viewgr_cnt.php";
    }
}

switch ( $ord ) {
    case 'best':     $sOrd = 'Популярные'; break;
    case 'relevant': $sOrd = 'Актуальные'; break;
    case 'my':       $sOrd = 'Мои';        break;
    case 'favs':     $sOrd = 'Закладки';  break;
    default:         $sOrd = 'Новые';      break;
}
$bp = ($page>1?"&bp=".$page:"").($gr?"&b=".$gr:"&b=all");
if (!$gr_name) $gr_name = "Ошибка";
if($ord=='favs') {
    $page_title = $page_keyw = "Закладки в блогах раздела > $gr_name < - " . ( $item_page > 1 ? " - Страница $item_page - " : '' ) . "фриланс, удаленная работа на FL.ru";
    $page_descr = "Закладки в блогах раздела $gr_name на FL.ru";
} else {
    $page_title = $page_keyw = "$sOrd публикации в блогах раздела > $gr_name < - " . ( $item_page > 1 ? "Страница $item_page - " : '' ) . "фриланс, удаленная работа на FL.ru";
    $page_descr = "$sOrd публикации в блогах раздела $gr_name на FL.ru";
}

include ($rpath."template.php");
?>
