<?
define( 'IS_SITE_ADMIN', 1 );
$no_banner = 1;
$rpath = "../../";
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/messages.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");

session_start();
$uid = get_uid();

if (!(hasPermissions('adm') && hasPermissions('adminspam'))) {
	header("Location: /404.php");
	exit;
}

if ($_GET['cache'] == 'clear') {
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/memBuff.php");
    $memBuff = new memBuff();
    $memBuff->touchTag("msgsCnt");
}

$FROM = 'admin'; // логин, от кого отправл€ть рассылку
$DB = new DB('master');
// отправл€ем от админа
$sql = "SELECT uid FROM users WHERE login = ?";
$row = $DB->val($sql, $FROM);
if ($row) {
	$send_uid = $row;
} else {
	$send_uid = $uid;
	$FROM = $_SESSION['login'];
}
$messages = new messages($send_uid);

$content = "../content.php";

$css_file = array('moderation.css','nav.css' );
$js_file  = array( 'highlight.min.js', 'highlight.init.js', 'mAttach.js');
$js_file_utf8[] = '/scripts/ckedit/ckeditor.js';
$inner_page = "inner_index.php";

$action = trim($_GET['action']);
if (!$action)
	$action = trim($_POST['action']);

$selectedProfs = array(0 => 'NULL');
$toEmail = 1;
$toWrk = 0;
$toFrl = 0;
$toPro = 1;
$toNotPro = 1;

switch ($action) {
	case "post_msg" :

        $GLOBALS['disable_link_processing'] = TRUE;
		$msg = __paramInit('ckedit', null, 'msg', null);
		//$attach = new CFile($_FILES['attach']);
		$toEmail = $_POST['toEmail'] ? 'SpamFromAdmin' : '';
		$toWrk = $_POST['toWrk'] ? 1 : 0;
		$toFrl = $_POST['toFrl'] ? 1 : 0;
		$toPro = $_POST['toPro'] ? 1 : 0;
        $toLogins = $_POST['toLogins'] ? 1 : 0;
		$toNotPro = $_POST['toNotPro'] ? 1 : 0;
		if (isset($_POST['prof']))
			$selectedProfs = $_POST['prof'];


		// загрузка файлов
		$files = array();
		$attach = $_FILES['attach'];
		if (is_array($attach) && !empty($attach['name'])) {
			$i = 0;
			foreach ($attach['name'] as $key => $v) {
				if (!$attach['name'][$key])
					continue;
				$files[$i] = new CFile(array(
							'name' => $attach['name'][$key],
							'type' => $attach['type'][$key],
							'tmp_name' => $attach['tmp_name'][$key],
							'error' => $attach['error'][$key],
							'size' => $attach['size'][$key]
						));
				$ext = $files[$i]->getext();
				if ( !in_array($ext, $GLOBALS['disallowed_array']) ) {
					if ($files[$i]->size > 0) {
						$name = $files[$i]->MoveUploadedFile("{$FROM}/contacts");
						if (!isNulArray($files[$i]->error)) {
							$alert[1] = "ќдин или несколько файлов не удовлетвор€ют услови€м загрузки";
							$error_flag = 1;
						}
					}
				} else {
					$alert[1] = "ќдин или несколько файлов имеют неправильный формат.";
					$error_flag = 1;
				}
				$i++;
			}
		}



		$error = 0;
		if (!$msg && !$name) {
			$error_flag = 1;
			$alert[2] = "ѕоле заполнено некорректно";
		}
		if (!$error_flag) {
			if ($toFrl) {
				$aProfs = NULL;
				$tp = $toPro && $toNotPro ? NULL : (bool) $toPro;
				foreach ($selectedProfs as $prof) {
					if (!$prof || $prof == 'NULL' || $prof == 'empty') {
						$aProfs = NULL;
						break;
					}

					$is_group = 0;
					if (substr($prof, 0, 2) == '::') {
						$id = substr($prof, 2);
						$is_group = 1;
					}
					else
						$id = intval($prof);

					$aProfs[] = array('id' => $id, 'is_group' => $is_group, 'to_pro' => $tp);
				}

				$message_id = $messages->masssendToFreelancers(stripslashes($msg), $tp, $aProfs, $toEmail, $files);
			} else if ($toWrk) {
				if (!($message_id = $messages->masssendToEmployers(stripslashes($msg), NULL, $toEmail, $files))) {
					$error = "¬нутренн€€ ошибка";
				}
            } else if ($toLogins) {
                $recipients = array_map('trim', explode(',', $_POST['logins']));
				if (!($message_id = $messages->masssendTo(stripslashes($msg), $recipients, $toEmail, $files))) {
					$error = "¬нутренн€€ ошибка";
				}
			} else {
				if (!($message_id = $messages->masssendToAll(stripslashes($msg), NULL, $toEmail, $files))) {
					$error = "¬нутренн€€ ошибка";
				}
			}
		}


		if (!$error && !$alert) {
			/*if ($toEmail) {
				$sql = "DELETE FROM variables WHERE name='admin_message_id' AND (value=? OR value IS NULL);
                INSERT INTO variables (name, value) VALUES ('admin_message_id', ?)";
				$DB->query($sql, $message_id, $message_id);
			}

			require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/memBuff.php");
            $memBuff = new memBuff();
            $memBuff->touchTag("msgsCnt");*/

			header("Location: /siteadmin/admin/?result=success");
			exit;
		} /*elseif ($error && $name) {
			$attach->Delete($attach->id);
		}*/

		break;
}


$header = $rpath . "header.php";
$footer = $rpath . "footer.html";

include ($rpath . "template.php");
