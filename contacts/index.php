<?
$g_page_id = "0|20";
$stretch_page = true;
$showMainDiv  = true;
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/messages.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/masssending.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/splash_screens.php");
session_start();
$uid = get_uid();

if($_GET['action'] == 'delfolder' || $_POST['action'] == 'delfolder') {
    if (!$_SESSION['rand']) {
        $_SESSION['rand'] = csrf_token();
    }
    if(!$_GET['token_key'] || ($_GET['token_key'] != $_SESSION['rand'])) {
        $_GET = array();
        $_REQUEST = array();
        $_POST = array();
    }
}


require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/registration.php");
$REG    = new registration();
$REG->listenerAccess(array('action' => 'look-contacts'));

$rpath = "../";
$footer_contacts = true;
if (!$_SESSION['uid']) { include("../fbd.php"); exit;}

$no_banner = !!is_pro();

$chat_with = trim($_GET['from']);
if($chat_with == $_SESSION['login']) {
    header("Location: /403.php");
    exit;
}

//$_SESSION['do_show_splash'] = splash_screens::SPLASH_MESSAGES;
if (!$chat_with) $chat_with = trim($_POST['msg_to']);

if (!preg_match("/^[a-zA-Z0-9_]+[-a-zA-Z0-9_]{1,}$/", $chat_with)) $chat_with = "";

$action = trim($_POST['action']);
if (!$action) $action = trim($_GET['action']);
$page = trim($_GET['p']);
//phpinfo(); exit;

if ($page == 'view_attach'){ include('view_attach.php'); exit;}

$draft_id  = intval(__paramInit('int', 'draft_id', 'draft_id'));


switch ($action) {
//	case "accept_new_rules":
//		splash_screens::setViewed(splash_screens::SPLASH_MESSAGES);
//		header("Location: {$_GET['url']}");
//		exit;
//		break;
	case "post_msg":

        $isNeedUseCaptcha = messages::isNeedUseCaptcha(get_uid(false));
		$chat_user = new users();
		$chat_user->GetUser($chat_with);
        if($chat_user->is_banned && !hasPermissions('users')) {
            $error_flag = 1; 
            $alert[3] = "Этот пользователь заблокирован. Вы не можете отправить ему личное сообщение";
        }
        
        //Разрешено ли пользователю отправлять сообщения
        if (!isset($error_flag) && is_emp($chat_user->role) && 
            !messages::isAllowed($chat_user->uid, $uid) ) {
                $chat_with = '';
                break;
        }
        
        
		$msg = antispam(__paramInit('html', NULL, 'msg'));
		$prjname = __paramInit('string', NULL, 'prjname');
		$attachedfiles_session = __paramInit('string', NULL, 'attachedfiles_session');
		//$attach = new CFile($_FILES['attach']);
		
		// загрузка файлов
		$files = array();
		$attach = $_FILES['attach'];
		if (is_array($attach) && !empty($attach['name'])) {
			foreach ($attach['name'] as $key=>$v) {
				if (!$attach['name'][$key]) continue;
				$files[] = new CFile(array(
					'name'     => $attach['name'][$key],
					'type'     => $attach['type'][$key], 
					'tmp_name' => $attach['tmp_name'][$key], 
					'error'    => $attach['error'][$key], 
					'size'     => $attach['size'][$key]
				));
			}
		}

        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/attachedfiles.php");
        $attachedfiles = new attachedfiles($attachedfiles_session);
		$attachedfiles_info = $attachedfiles->calcFiles();
		
		if ((!$msg || trim($msg) == "") && !(sizeof($files) || $attachedfiles_info['count'])) {
            $error_flag = 1; 
            $alert[2] = "Поле заполнено некорректно";
        } elseif($msg && strlen($msg) > messages::MAX_MSG_LENGTH) {
            $error_flag = 1; 
            $alert[2] = "Вы ввели слишком большое сообщение. Текст сообщения не должен превышать 20 000 символов.";
        }

        if($isNeedUseCaptcha) {
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/captcha.php");
            $captchanum = $_POST['captchanum'];
            $captcha = new captcha($captchanum);
            $rnd = $_POST['rndnum'];
            if(!$captcha->checkNumber(trim($rnd))) {
                $error_flag = 1; $alert[4] = "Вы ввели неверную комбинацию символов";
            }
        }
        
        // если запрошено обновление капчи в PDA
        $newCaptcha = $_POST['newcaptcha'];
        if ($newCaptcha) {
            $alert = array();
        }
        
		if ($chat_with != $_SESSION['login'] && !$error_flag){
			list($alert,$error) = messages::Add(get_uid(), $chat_with, $msg, $files, 0, false, $attachedfiles_session);
			if (!$error && isNulArray($alert)) {
                
                messages::updateSendLog(get_uid(false));
                
                if($draft_id) {
                    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/drafts.php");
                    drafts::DeleteDraft(intval($draft_id), get_uid(false), 2, true);
                }
                
                //Если сообщение фрилансеру то разрешаем отправку сообщений обратно
                if (!is_emp($chat_user->role)) {
                    messages::setIsAllowed($uid, $chat_user->uid);
                }
                
				unset($msg);
				header("Location: ".$_SERVER["REQUEST_URI"]);
				exit;
			}
		}
		break;
	case "delete":
		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/teams.php");
		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/ignor.php");
		$selected = $_POST['selected'];
		$error = ignor::DeleteEx(get_uid(), $selected);
		$error = messages::DeleteFromUsers(get_uid(), $selected);
		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/memBuff.php");
		$memBuff = new memBuff();
		$memBuff->delete("msgsCnt".get_uid(false));
		break;
	case "addfolder":
		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/mess_folders.php");
		$name = substr(change_q($_POST['name'], false, 64),0,64);
 		$srch = array("'", "\"", "<", ">");
		$name = trim(str_replace($srch,"",$name));
   	if (empty($name) || ($name==''))
   	{
      $error_flag = 1;
      $error = 'Не указано имя папки';
   	}
   	else
   	{
   		$newfolder = new mess_folders();
		$newfolder->fname = $name;
   		$newfolder->from_id = get_uid();
   		if ($error = $newfolder->Add($error)) {
			$error_flag = 1;
		}
   	}
    if(!$error) { header("Location: ".$_SERVER["REQUEST_URI"]); exit; }
		break;
	case "delfolder":
		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/mess_folders.php");
		$id = intval($_GET['id']);
		$newfolder = new mess_folders();
		$newfolder->from_id = get_uid();
		$newfolder->id = $id;
		$newfolder->Del();
		break;
    default:
        if ($_SERVER["REQUEST_METHOD"] == 'POST' && empty($_POST)) {
            $alert[1] = 'Вы превысили максимально допустимый размер файлов';
        }
        break;
}

$css_file = array( 'contacts.css', '/css/nav.css', '/css/block/b-search/b-search.css', '/css/block/b-captcha/b-captcha.css' );

$js_file  = array( 'note.js' );

if ($chat_with) {
	if ($chat_with != $_SESSION['login']) {
		$inner = "dialog.php";
		$js_file = array_merge( $js_file, array('mAttach.js', 'drafts.js', '/css/block/b-popup/b-popup.js', 
            '/css/block/b-textarea/b-textarea.js', 'attachedfiles.js') );
		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/ignor.php");
		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/messages_spam.php");
		$msgs = new messages();
		$curpage = intval(trim($_GET['curpage']));
		if (!$curpage) $curpage = intval(trim($_POST['curpage']));
		if (!$curpage||($curpage < 0)) $curpage = 1;
		$page_size = $PDA ? 10 : $GLOBALS['msgspp'];
		$dialog = $msgs->GetMessages(get_uid(), $chat_with, $num_msgs_from, $curpage, $page_size);
		//$_SESSION['newmsgs'] = $msgs->GetNewMsgCount($_SESSION['uid'], $err);
		$user = new users();
	     
		$user->GetUser($chat_with);
                if( (!is_pro(true, $user->uid) && !is_emp($user->role)) && count($dialog) == 0 && is_emp($_SESSION['role'])) {
                    $is_contact_splash = true;
                }
                
		if ($user->login == '') //|| ($user->is_banned && !hasPermissions('users')))
		{
			header("Location: /404.php");
		}
		// Если пользователь забанен
		if($user->is_banned && !hasPermissions('users')) {
            $error_flag = 1; $alert[3] = "Этот пользователь заблокирован. Вы не можете отправить ему личное сообщение";
        }

		$dlg_user = users::GetUid($err,$user->login);
		$dlg_user_login = $user->login;
        
        
		if ($post_denied = (ignor::CheckIgnored($dlg_user, $_SESSION['uid']) || in_array($user->login, array('admin', 'Anonymous')))) $error = "Пользователь запретил отправлять ему сообщения";
		
        if (!$post_denied && is_emp($user->role)) {
            $is_allow_messages = messages::isAllowed($user->uid, $uid);
            $post_denied = !$is_allow_messages;
        }
        
        
        $prjname = $_POST['prjname'];
		$cnt_role = (substr($user->role, 0, 1)  == '0')? "frl" : "emp";
		require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/memBuff.php");
        $memBuff = new memBuff();
        $memBuff->delete("msgsCnt" . get_uid(false)); 
#		$css_file = "";
	}
} else {
	switch ($page) {
	    case "unread": $cur_folder = -7; break;
		case "team" : $cur_folder = -1; break;
		case "ignor" : $cur_folder = -2; break;
		case "del" : $cur_folder = -3; break;
		case "notes" : $cur_folder = -4; break;
		case "allnotes" : $cur_folder = -5; break;
        case "mass" : $cur_folder = -6; break;
	}
	
	$pm_year   = intval($_GET['pmy']);
	$pm_folder = intval($_GET['pmf']);
	$pm_offset = intval($_GET['pmo']);
	
	if (!$cur_folder) $cur_folder = intval($_GET['folder']);
	
	$find = trim($_GET['find']);
	$msgs = new messages();
	
	if ( !$page = __paramInit('int', 'page', NULL, NULL) ) {
	    $page = 1;
	    $bPageDefault = true;
	}
	
	if ($page <= 0) $page = 1;
    
	if ( !$pm_folder ) {
	    // пользовательские папки
        if ( $cur_folder == -5 ) {
            $contacts = $msgs->GetContactsWithNote( get_uid(), $find );
        }
        else {
        	$contacts = $msgs->GetContacts( get_uid(), $cur_folder, $find, $blogspp, ($page-1) * $blogspp, $predefined_count );
        }
	}
	else {
	    // автоматические папки для массовых рассылок личных менеджеров
	    $contacts = $msgs->pmAutoFolderGetContacts( get_uid(), $pm_folder, $find );
	}
    
    $count = $predefined_count ? $predefined_count : sizeof( $contacts);
    $pages = ceil( $count / $blogspp );
    
    if ( 
        ($count == 0 || $count < ($page - 1) * $blogspp) && !$bPageDefault 
        || $pages == 1 && !$bPageDefault 
     ) {
        include( ABS_PATH . '/404.php' );
        exit;
    }

}

	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");
	$is_pro = payed::CheckPro($_SESSION['login']);
	

$header = "../header.php";
$footer = "../footer.html";
$content = "content.php";

include ("../template3.php");