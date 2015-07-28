<?
require_once ($_SERVER['DOCUMENT_ROOT'].'/classes/links.php');
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/blogs.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stat_collector.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/drafts.php");
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );

//$stop_words = new stop_words( hasPermissions('blogs') );
$g_page_id = "0|6";
$grey_blogs = 1;
$stretch_page = true;
$showMainDiv  = true;
session_start();
$uid = get_uid();
$rpath="../";


if($_GET['tr'] && !$_GET['newurl'] && !$_POST) {
  $msg_id = $_GET['tr'];
    $query_string = preg_replace("/tr={$msg_id}/", "", $_SERVER['QUERY_STRING']);
    $query_string = preg_replace("/^&/", "", $query_string);
    $friendly_url = getFriendlyURL("blog", $msg_id);
    if(trim($friendly_url) == "") {
        $friendly_url= "/404.php";
    } else {
        $friendly_url = $friendly_url . ($query_string ? "?{$query_string}" : "");
    }
    
    header ('HTTP/1.1 301 Moved Permanently');
    header ('Location: ' . $friendly_url);
    exit;
}

$url_parts = parse_url($_SERVER['REQUEST_URI']);
if($_GET['tr'] && !(PDA && $_GET['newcnt'])) {
    $friendly_url = getFriendlyURL('blog', $_GET['tr']);
    if(trim($friendly_url) == "" ) {
        $friendly_url= "/404.php";
    } 
    if(strtolower($url_parts['path'])!=$friendly_url) {
        header ('HTTP/1.1 301 Moved Permanently');
        header ('Location: '.$friendly_url);
        exit;
    }
}


foreach($_GET as $key=>$value) {
    $_GET[$key] = strip_tags($value);
}
if(isset($_GET['bp'])) {
    $_GET['bp'] = intval($_GET['bp']);
}
if(isset($_GET['pagefrom'])) {
    $_GET['pagefrom'] = intval($_GET['pagefrom']);
}
if(isset($_POST['pagefrom'])) {
    $_POST['pagefrom'] = intval($_POST['pagefrom']);    
}
if(BLOGS_CLOSED == true && $_GET['tr']) {
    require_once $_SERVER['DOCUMENT_ROOT']. "/classes/commune.php";
    $commune_theme = commune::getCommunePostByThreadID(intval($_GET['tr']));
    
    $http_query = "";
    if(isset($_GET['openlevel'])) {
        $comment_id = commune::getCommuneMessageByBlogID(intval($_GET['openlevel']));
        if($comment_id)
            $http_query = "#c_{$comment_id}";
    }
    $url_redirect  = getFriendlyUrl('commune', $commune_theme);
    
    if(trim($url_redirect) == "" ) {
        $url_redirect = "/404.php";
    } else {
        $url_redirect = $url_redirect . $http_query;
    }
    header("Location: " . $url_redirect);
    exit;
}
$action = trim($_POST['action']);
if (!$action) $action = trim($_GET['action']);
if (!$action) $action = trim($_POST['actions']);
if ($_POST["ord"]) { $_GET["ord"]=$_POST["ord"]; }
$_GET["ord"]= preg_replace("'([\w]*).*'si","$1",strip_tags($_GET["ord"]));

$thread = intval(trim($_GET['tr']));
if (!$thread) $thread = intval(trim($_POST['tr']));

$reply = intval(trim($_POST['reply']));

$mod = (hasPermissions('blogs'))? 0 : 1;


if ($_SESSION['uid']) {
$user = new users();
$ban_where=$user->GetField($_SESSION['uid'],$error,"ban_where");
}
else { $ban_where=0; }

$draft_id = __paramInit('int', 'draft_id', 'draft_id');

if (!$ban_where)
switch ($action) {
    case "post_msg":
        if(!$uid) {
            header("Location: /fbd.php");
            die();
        }
        $filecount = 0;
        if (strlen($_POST['msg']) > blogs::MAX_DESC_CHARS) {
            $error_flag = 1;
            $alert[1] = "Максимальный размер сообщения ".blogs::MAX_DESC_CHARS." символов!";
        }
        $msg = change_q_x(antispam($_POST['msg']), false, false, 'b|br|i|p|ul|li|cut|s|h[1-6]{1}', false, false);
        $msg = trim($msg);
        $msg_name = change_q_x(antispam(substr($_POST['msg_name'], 0, 96)), true, false);
        $attach = $_FILES['attach'];
        $yt_link = $_POST['yt_link'];
        if ($yt_link != '') {
            $v_yt_link = video_validate($yt_link);
            if(!$v_yt_link) {
                $error_flag = 1; $alert[4] = "Неверная ссылка.";
            } else {
                $yt_link = $v_yt_link;
            }
        }
        $tn = 0;
        if ($_SESSION['uid'] && $reply && $thread ){
            // загрузка файлов
            $files = array();
            $attach = $_FILES['attach'];
                
            if (is_array($attach) && !empty($attach['name'])) {
                $nTotalSize = 0;
                
                foreach ($attach['name'] as $key=>$v) {
                    if (!$attach['name'][$key]) continue;
                    $filecount++;
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
                    	$alert[3]   = 'Максимальный объем прикрепленных файлов: ' . (blogs::MAX_FILE_SIZE / (1024*1024));
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
		    $ctrl_msg = $msg;
		   	$ctrl_msg = preg_replace("#&\w+;#si", "", $msg);
           if (!$ctrl_msg && !count($files) && !$yt_link && !$attachedfiles_info['count']){
                $error_flag = 1;
                $alert[1] = "Поле заполнено некорректно";
            }

            if(!$error_flag && is_empty_html($msg)) $msg='';
            if (!$error_flag && !$f_name){
                $blogs = new blogs();
                $last_id = $blogs->Add($uid, $reply, $thread, $msg, $msg_name, $files, getRemoteIP(), $err, 0, $yt_link);

                $attachedfiles_files = $attachedfiles->getFiles();
                $blogs->addAttachedFiles($attachedfiles_files, $last_id, NULL); 
                $attachedfiles->clear();
                
                //$nStopWordsCnt = $stop_words->calculate( $msg, $msg_name );
                //$blogs->insertIntoModeration( $last_id, $nStopWordsCnt, ($reply ? 1 : 0) ); // больше не модерируем

                if($err==403) {
		            header("Location: /403.php"); exit;
                }
                //$ret = $blogs->GetMsgInfo($last_id, $err, $perm); че это вообще такое?
                if (is_array($err) && !isNulArray($err)) {$error_flag = 1; $alert = $err;}
                if ($ret['base'] == 3 || $ret['base'] == 5){
                    $kind = $ret['kind'];
                    include($rpath."classes/buffer_prjs.php");
                }
                //$error .= $err;
                if ($last_id) {
                    header("Location: ".getFriendlyURL("blog", $thread)."?pagefrom=".($_POST['onpage'] ? $_POST['onpage'] : $_POST['pagefrom']).'&openlevel='.$last_id."&ord=".$_GET["ord"].($PDA ? '#o'.$last_id : ''));exit;
                }
            }/* elseif (!$msg && !$filecount){
                $error_flag = 1; $alert[1] = "Поле заполнено некорректно";
            }*/
        }
        if($PDA)
            $content = "new_cnt.php";
        
        break;
    case "delete":
        $token = $_GET['u_token_key'];
        if(!$uid || $token != $_SESSION['rand']) {
            header("Location: /fbd.php");
            die();
        }
        
        $id = intval(trim($_GET['id']));
        if ($id && $uid){
            $msg = blogs::GetMsgInfo($id,$error,$perm);
            $thread = blogs::MarkDeleteMsg($uid, $id, getRemoteIP(), $err, $mod);
            
            $blog_data = new blogs();
            $blog_data->GetThread($thread, $err, $mod, $uid);
            
            if (hasPermissions('blogs') && $msg['fromuser_id'] != $uid && $blog_data->fromuser_id!= $uid) {
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/messages.php");
                messages::SendMsgDelWarn($id, $msg);
            }
            
            // пишем лог админских действий: удаление комментария в блоге
            if ( hasPermissions('blogs') && $msg['fromuser_id'] != $uid ) {
                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/admin_log.php' );
                
            	$sLink   = getFriendlyURL( 'blog', $thread );
            	$sReason = '<a href="'. $sLink . "?openlevel={$id}#o{$id}" 
            	   . '" target="_blank">Комментарий</a> от <a href="' . $GLOBALS['host'] . '/users/' . $msg['login'] 
            	   . '" target="_blank">' . $msg['uname'] . ' ' . $msg['usurname'] . ' [' . $msg['login'] . ']</a>';
            	
            	admin_log::addLog( 
            	   admin_log::OBJ_CODE_BLOG, admin_log::ACT_ID_BLOG_DEL_COMM, 
            	   $blog_data->fromuser_id, $thread, $blog_data->title, $sLink, 0, '', 0, $sReason 
            	);
            }
        }
        exit(header("Location: ".getFriendlyURL("blog", $_GET['tr'])."?openlevel={$id}#o{$id}".($_GET["ord"]?"&ord=".$_GET["ord"]:"")));
        break;
    case "restore":
        if(!$uid) {
            header("Location: /fbd.php");
            die();
        }
        $id = intval(trim($_GET['id']));
        if ($id && $uid){
            $thread = blogs::RestoreDeleteMsg($uid, $id, getRemoteIP(), $err, $mod);
            $msg    = blogs::GetMsgInfo( $id, $error, $perm );
            
            // пишем лог админских действий: восстановление комментария в блоге
            if ( hasPermissions('blogs') && $msg['fromuser_id'] != $uid ) {
                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/admin_log.php' );
                
            	$sLink   = getFriendlyURL( 'blog', $thread );
            	$sReason = '<a href="'. $sLink . "?openlevel={$id}#o{$id}" 
            	   . '" target="_blank">Комментарий</a> от <a href="' . $GLOBALS['host'] . '/users/' . $msg['login'] 
            	   . '" target="_blank">' . $msg['uname'] . ' ' . $msg['usurname'] . ' [' . $msg['login'] . ']</a>';
                
            	$blog_data = new blogs();
            	$blog_data->GetThread( $thread, $err, $mod, $uid );
            	
            	admin_log::addLog( 
            	   admin_log::OBJ_CODE_BLOG, admin_log::ACT_ID_BLOG_RST_COMM, 
            	   $blog_data->fromuser_id, $thread, $blog_data->title, $sLink, 0, '', 0, $sReason 
            	);
            }
        }
        exit(header("Location: ".getFriendlyURL("blog", $_GET['tr'])."?openlevel={$id}#o{$id}".($_GET["ord"]?"&ord=".$_GET["ord"]:"")));
        break;

        /*
        $ret = blogs::GetMsgInfo($id, $err, $perm);
        $error = blogs::DeleteMsg($uid, $id, $gr, $base, $thread, $nulla, $nullb, $mod);
}
if ($ret){
if ($ret['base'] == 3 || $ret['base'] == 5){
$kind = $ret['kind'];
include($rpath."classes/buffer_prjs.php");
}
}
*/
        //header("Location: /blogs/view.php?tr=$thread");

    /*case "warn":
        if (hasPermissions('blogs')) {
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/messages.php");
            $usr=new users();
            $usr->Warn($_GET["ulogin"]);
            $id = intval(trim($_GET['blogid']));
            messages::SendWarn($_GET["ulogin"],$id);
           // blogs::MarkDeleteMsg($uid, $id, getRemoteIP(), $err, $mod);
            header("Location: /blogs/view.php?tr=".$_GET["id"]."&openlevel=".$_GET["openlevel"]."&ord=".$_GET["ord"]."#o".$_GET["openlevel"]);
            exit;
        }
        break;*/


    case "change":

        if(!isset($_POST['msg_name'])) $_POST['msg_name'] = $_POST['name'];
        
        if ($_POST['close_comments']) $close_comments = "t"; else $close_comments = "f";
        if ($_POST['is_private']) $is_private = "t"; else $is_private = "f";
        $categ = __paramInit('string', NULL, 'category',0);
        list ($gr,$t) = explode("|", $categ);
      
        //if (!$t) $base = 0; else $base = 1;

        if (strlen($_POST['msg']) > blogs::MAX_DESC_CHARS) {
            $error_flag = 1;
            $alert[1] = "Максимальный размер сообщения ".blogs::MAX_DESC_CHARS." символов!";
        }
        $msg = change_q_x(antispam($_POST['msg']), false, false, 'b|br|i|p|ul|li|cut|s|h[1-6]{1}', false, false);
        $msg_name = substr_entity(change_q_x(antispam($_POST['msg_name']), true, false), 0, 96, true);
        $yt_link = $_POST['yt_link'];
        if ($yt_link != '') {
            $v_yt_link = video_validate($yt_link);
            if(!$v_yt_link) {
                $error_flag = 1; $alert[4] = "Неверная ссылка.";
            } else {
                $yt_link = $v_yt_link;
            }
        }
        $attach = $_FILES['attach'];
        $tn = 0;

        if(!$error_flag && is_empty_html($msg))
          $msg='';

		// опросы
		$question = substr_entity(change_q_x( antispam( trim((string) $_POST['question']) ), false, false, ''), 0, blogs::MAX_POLL_CHARS, true);
		$answers = array();
		$answers_exists = array();
		$multiple = (bool) $_POST['multiple'];
		if (is_array($_POST['answers']) && !empty($_POST['answers'])) {
			$i = 0;
			foreach ($_POST['answers'] as $pa) {
                if (trim((string) $pa) !== '') {
					$answers[] = substr_entity(change_q_x(antispam( preg_replace('/&/','&amp;',(string) trim($pa)) ), false, false, ''), 0, blogs::MAX_POLL_ANSWER_CHARS * 2, true);
					$i++;
				}
			}
		}
		if (is_array($_POST['answers_exists']) && !empty($_POST['answers_exists'])) {
			foreach ($_POST['answers_exists'] as $key=>$pa) {
                if (trim((string) $pa) !== '') {
					$answers_exists[$key] = substr_entity(change_q_x(antispam( preg_replace('/&/','&amp;',(string) trim($pa)) ), false, false, ''), 0, blogs::MAX_POLL_ANSWER_CHARS * 2, true);
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
		
        //$attach_delete=$_POST["delattach"];
        $attach_delete= is_array($_POST["delattach"])? $_POST['delattach']: array();
        $attach_have=$_POST["have_attach"];

        $olduser=intval($_POST["olduser"]);
        $us=new users();
        $logins=$us->GetName($olduser,$error);
        $olduserlogin=$logins["login"];
        if (!$olduserlogin) { break; }

        if ($_SESSION['uid'] && $reply && $thread) {
            // загрузка файлов
            $files = array();
            $attach = $_FILES['attach'];
            
            if (is_array($attach) && !empty($attach['name'])) {
                $nTotalSize = 0;
                $aAttach    = blogs::GetAttach( $reply, $attach_delete );
                
                if ( is_array($aAttach) && count($aAttach) ) {
                    $dir = 'users/'.substr($olduserlogin, 0, 2)."/$olduserlogin/upload/";
                    
                	foreach ( $aAttach as $sFile ) {
                	    $cfile = new CFile( $dir . $sFile );
                	    $nTotalSize += $cfile->size;
                	}
                }
                
                foreach ($attach['name'] as $key=>$v) {
                    if ($attach['name'][$key]) {
                        $filecount++;
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
                        	$alert[3]   = 'Максимальный объем прикрепленных файлов: ' . (blogs::MAX_FILE_SIZE / (1024*1024));
                        	break;
                        }
                    }
                }
            }
            
            $filecount = blogs::GetAttachCount($reply) + count($attach['name']) - count($attach_delete);
            
            if ( $filecount > blogs::MAX_FILES) {
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
            if($attachedfiles_session) $filecount = 0;

			
            if ($msg==='' && $question==='' && empty($alert[5]) && !($filecount || $attachedfiles_info['count']) && $yt_link==='') {
                $error_flag = 1; $alert[1] = "Ошибка. Сообщение не должно быть пустым!";
            }
            elseif (!$error && !$error_flag && ($msg!=='' || $attach['name'] || $attach_have || $attach_delete || $question || $yt_link || $attachedfiles_info['count'])){
                $blogs = new blogs();
                
                if ( hasPermissions('blogs') ) {
                	$ontop = ( isset($_POST['ontop']) && $_POST['ontop'] == 't') ? 't' : 'f';
                }
                else {
                    $ontop = null;
                }
                
                if($_POST['not_is_private']) $is_private = 'n';
                if($_POST['not_close_comments']) $close_comments = 'n';
                $blogs->Edit($_SESSION['uid'], $reply, $msg, $msg_name, $files, getRemoteIP(), $err, $mod, '', $gr, $t , $attach_delete, $olduserlogin, $yt_link, $close_comments, $is_private, $ontop, null, $question, $answers, $answers_exists, $multiple);

                $attachedfiles_files = $attachedfiles->getFiles(array(1,3,4));
                $blogs->addAttachedFiles($attachedfiles_files, $reply, $olduserlogin, ($draft_id ? true : false)); 
                $attachedfiles->clear();
                
                //$nStopWordsCnt = $stop_words->calculate( $msg, $msg_name, $question, $answers, $answers_exists );
                //$blogs->insertIntoModeration( $reply, $nStopWordsCnt, (!$categ ? 1 : 0) ); // больше не модерируем

                if (is_array($err) && !isNulArray($err)) {
                    $error_flag = 1;
                    $alert[3] = $err[3];
                    $action = "edit";
                    $edit_id = $reply;
                } else {
                    $last_id = $reply;
                    if($draft_id) {
                        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/drafts.php");
                        drafts::DeleteDraft($draft_id, get_uid(false), 3);
                    }
                    header("Location: ".getFriendlyURL("blog", $thread)."?pagefrom=".intval($_POST['pagefrom']).'&openlevel='.$reply."&ord=".$_GET["ord"]);
                }

            }
           

        }
        if ($error_flag) { $action = "edit"; $edit_id = $reply; } else break;

    case "edit":
        $edit_id = intval(trim($_GET['id']));
        $form_uri = "/blogs/view.php?id=".htmlspecialchars($_GET['id']);
        
        $blogs    = new blogs();
        $edit_msg = $blogs->GetMsgInfo($edit_id, $error, $perm);
        
        if($PDA){
            $edit_comment = true;
            $form_uri .= ($error_flag ? '&action=edit&editcnt': '')."&tr=".intval($_GET['tr']);
            $content = "edit_cnt.php";
        }
        break;        

   case "deletewinner":
        $winner = intval(trim($_GET['winner']));
        if ($winner && $thread && $_SESSION['uid']) {
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");

            projects::DeleteWinner($_SESSION['uid'], $winner);
            header("Location: /blogs/view.php?tr=$thread&ord=".$_GET["ord"]);
            exit;
        }
        break;
}

$winner = intval(trim($_GET['winner']));

if ($winner && $thread && $_SESSION['uid']) {
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
    projects::SetWinner($_SESSION['uid'], $winner);
    //header("Location: /blogs/view.php?tr=$thread");
    header("Location: /blogs/view.php?tr=$thread&pagefrom=".intval($_POST['pagefrom'])."&ord=".$_GET["ord"]);
}

$base = (int)(!!trim($_GET['t']));
//$theme = intval(trim($_GET['b']));

$blog = new blogs();
list($gr_name, $gr_id, $gr_base) = $blog->GetThread($thread, $err, $mod, $uid);
$main = $blog->id;

if ($gr_base == 5) {
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
    $winner = projects::CheckWinner($gr_id);
}

if ($blog->id_gr && ($blog->base==3 || $blog->base==5)) {
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");

    $attach_ar=projects::GetAllAttach($blog->id_gr);
}

if ($gr_base == 3 || $gr_base == 4 || $gr_base == 5 || !isset($gr_base)) $grey_blogs = 0;

if (($gr_base == 3 || $gr_base == 5)  && !hasPermissions('blogs')){
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
    if (projects::CheckProOnly($thread) == 't' && !$_SESSION['pro_last'] && $_SESSION['login'] !== $blog->login && !is_emp()) header("Location: /proonly.php");
}

if($blog->deleted && !(hasPermissions('blogs'))) {
    include ABS_PATH."/404.php"; exit;
}

if ($blog->is_private && $uid != $blog->fromuser_id && !(hasPermissions('blogs')) ) {
	include ABS_PATH."/404.php"; exit;
}

$sTitle   = /*($blog->moderator_status === '0' && $blog->payed != 't') ? $stop_words->replace($blog->title, 'plain', false) :*/ $blog->title;
$sMessage = /*($blog->moderator_status === '0' && $blog->payed != 't') ? $stop_words->replace($blog->msgtext, 'plain', false) :*/ $blog->msgtext;

if ($err || $blog->msg_num == 0 || ($blog->is_private && $blog->deleted && $blog->fromuser_id != $_SESSION['uid'] && !hasPermissions('blogs'))) {
    include ABS_PATH."/404.php"; exit;
} else {
    if ( $blog->thread[0]['read_comments'] != $blog->msg_num-1 && $uid > 0 ) {
        $error .= $blog->SetRead($thread, $uid, $blog->msg_num-1);
    }
    $allow_del = 0;
    $cur_user_msgs = array();
    $multiattach_mode = 1;

    $blog_gr = new blogs();
    $groups = $blog_gr->GetThemes($error, 1);
    if(!$content)
        $content = "view_cnt.php";
    
    $FBShare = array(
        "title"       => $sTitle,
        "description" => "",
        "image"       => HTTP_PREFIX."www.free-lance.ru/images/free-lance_logo.jpg" 
    );
}
if($PDA) {
   if(isset($_GET['newcnt'])) $content = "new_cnt.php";
   if(isset($_GET['editcnt'])) $content = "edit_cnt.php";
}

$footer = $rpath."footer.html";

if (!$gr_name) $gr_name = "Ошибка";

if($blog->poll !== null && $blog->title == "") {
    $sQuestion  = /*($blog->moderator_status === '0' && $blog->payed != 't') ? $stop_words->replace($blog->poll_question, 'plain', false) :*/ $blog->poll_question;
    $html_title = substr($blog->poll_question, 0, 30) . (strlen($blog->poll_question)>30?"...":"");
} else {
    $html_title = $blog->title == "" ? substr($sMessage, 0, 30) . (strlen($sMessage)>30?"...":"") : $sTitle;
    
    if($html_title == "") $html_title = 'Блоги';
} 

$page_title = strip_tags($html_title) . ' - фриланс, удаленная работа на FL.ru';
$page_keyw = strtolower($gr_name).", удаленная работа, фри-ланс, дизайнер, программист, менеджер, иллюстратор, верстальщик, оптимизатор, копирайтер";
$page_descr = LenghtFormatEx(htmlspecialchars(strip_tags($sMessage), ENT_QUOTES, 'cp1251'),250,'',0);//$gr_name.". Работодатель.Удаленная работа. Поиск работы. Предложение работы. Портфолио фрилансеров. Разработка сайтов, Программирование, Переводы, Тексты, Дизайн, Арт, Реклама, Маркетинг, Прочее. Free-lance.ru";

$page_title    = deleteHiddenURLFacebook($page_title);
$page_keyw     = deleteHiddenURLFacebook($page_keyw);
$page_descr    = deleteHiddenURLFacebook($page_descr);
$blog->title   = deleteHiddenURLFacebook($blog->title);
$sTitle        = deleteHiddenURLFacebook($sTitle);
stat_collector::setStamp(); // stamp
// Добавляем Open Graph Tags для FaceBook
if($blog){
    global $additional_header;
    if(empty($additional_header)) $additional_header = '';
    
    $additional_header .= '
        <meta property="og:type" content="blog"/>
        <meta property="og:title" content="'.($blog->title ? $sTitle : 'Блоги на FL.ru').'"/>
        <meta property="og:url" content="'.urlencode(HTTP_PREFIX.'www.free-lance.ru'.getFriendlyURL("blog", $blog->id)).'"/>
        <meta property="og:site_name" content="Free-lance.ru"/>
        <meta property="og:image" content="'.HTTP_PREFIX.'www.free-lance.ru/images/free-lance_logo.jpg"/>
        <meta property="og:description" content="'.deleteHiddenURLFacebook(str_replace('"', "'", $sMessage)).'"/>';
}

if (!$edit_msg['reply_to'] && $action == "edit") {
    $use_draft = 1;
} else {
    $use_draft = 0;
}

if(empty($additional_header)) $additional_header = '';
      $om_clean_uri = array();
        foreach ($_GET as $key => $value) {
            if($value && !in_array($key, array('tr','b','ord','openlevel','newurl'))) $om_clean_uri[] = $key.'='.urlencode($value);
        }
      if(!empty($_GET['id'])) $om_clean_uri[] = 'id='.(int)$_GET['id'];
      if(count($om_clean_uri) || $_GET['tr']) $additional_header .= '
<link rel="canonical" href="'.getFriendlyURL('blog', $_GET['tr']).($om_clean_uri ? '?'.htmlspecialchars(implode('&',$om_clean_uri)) : '').'"/>
';
      
$js_file[] = 'attachedfiles.js';
$js_file[] = 'blogs_cnt.js';
$js_file[] = 'swfobject.js';
$js_file[] = 'banned.js';

if (empty($no_poll)) {
    $js_file[] = 'polls.js';
}
if ($uid) {
    $js_file[] = 'blogs.js';
}
if (!empty($multiattach_mode)) {
    $js_file[] = 'mAttach.js';
}
if($use_draft) { 
    $js_file[] = 'drafts.js';
}


include ($rpath."template.php");

?>
