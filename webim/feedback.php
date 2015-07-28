<?php
/* 
 * 
 * Данный файл является частью проекта Веб Мессенджер.
 * 
 * Все права защищены. (c) 2005-2009 ООО "ТОП".
 * Данное программное обеспечение и все сопутствующие материалы
 * предоставляются на условиях лицензии, доступной по адресу
 * http://webim.ru/license.html
 * 
 */
?>
<?php
require_once('classes/functions.php');
require_once('classes/class.thread.php');
require_once('classes/class.smartyclass.php');
require_once('classes/class.settings.php');
require_once('classes/class.visitor.php');

require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stdf.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/captcha.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/feedback.php' );


$captcha = new captcha();
    
$TML = new SmartyClass();
$TML->assignCompanyInfoAndTheme();
$errors = array();
$page = array();

$department = (int)$_REQUEST["department_db_id"];
$canChangeName = Visitor::getInstance()->canVisitorChangeName();
$v = GetVisitorFromRequestAndSetCookie();
$visitorid = $v['id'];
$captcha_num = $v['captcha'];

$message = get_mandatory_param('message');

$has_errors = false;
if( count($_POST) == 0 && count($_FILES) == 0 ) { //костыль. при отправке на сервер большого файла приходит пустой request  и files, как по длругому определить, пока не придумал
    $has_errors = true;
    $errors[0] = 6;
    $errors[1] = iconv("UTF-8", "WINDOWS-1251//IGNORE", "Файл очень велик");
}elseif(!$captcha->checkNumber($captcha_num)) {
    $has_errors = true;
    $errors[0] = 5;
    $errors[1] = iconv("UTF-8", "WINDOWS-1251//IGNORE", "Неверно введен текст");
}elseif (empty($message) || $message == '') {
    $has_errors = true;
    $errors[0] = 4;
    $errors[1] = iconv("UTF-8", "WINDOWS-1251//IGNORE","Заполните это поле");
}elseif (!$department) {
    $has_errors = true;
    $errors[0] = 7;
    $errors[1] = iconv("UTF-8", "WINDOWS-1251//IGNORE","Неверно введен текст");
}

$fileIndex = 1;
if (!$has_errors) {
	$files = array();
	for ( $i = 0; $i < count($_FILES['attach']['name']); $i++ ) {
	    $file = array(
	        'name'     => $_FILES['attach']['name'][$i],
	        'type'     => $_FILES['attach']['type'][$i],
	        'tmp_name' => $_FILES['attach']['tmp_name'][$i],
	        'error'    => $_FILES['attach']['error'][$i],
	        'size'     => $_FILES['attach']['size'][$i]
	    );
	                    
	    if ( !$file['name'] ) continue;
	                    
	    $nTotalSize += $file['size'];
	                   
	    if ( $nTotalSize > feedback::MAX_FILE_SIZE ) {
	    	$has_errors = true;
	        $errors[1] = iconv("UTF-8", "WINDOWS-1251//IGNORE", 'Максимальный объем прикрепленных файлов: ') . (feedback::MAX_FILE_SIZE / (1024*1024)) . iconv("UTF-8", "WINDOWS-1251//IGNORE", 'Мб.');
	        $errors[0] = 6;
	        break;
	    }
	                    
	    $files[] = new CFile( $file );
	                    
	    if ( count($files) > feedback::MAX_FILES ) {
	        $has_errors = true;
	        $errors[1] = iconv("UTF-8", "WINDOWS-1251//IGNORE", "Максимальное кол-во прикрепленных файлов: ") . feedback::MAX_FILES;
	        $errors[0] = 6;
	        break;
	    }
	}
    $attach = array();
    if ( !$has_errors && count($files) ) {
        foreach ( $files as $cfile ) {
            $cfile->max_size = feedback::MAX_FILE_SIZE;
            $cfile->server_root = 1;
            $fr = $cfile->MoveUploadedFile('upload/about/feedback/');
            $sFileAlert = is_string( $cfile->error[0] ) ? $cfile->error : $cfile->error[0];
            if ( !$fr && !$sFileAlert ) {
                $errors[1] = iconv("UTF-8", "WINDOWS-1251//IGNORE", "Ошибка при загрузке файла.");
	            $errors[0] = 6;
                break;
            } else if ( $sFileAlert ) {
                break;
            } else {
                $attach[] = $cfile;
            }
            $fileIndex++;
        }
   }
}

if ( $sFileAlert ) {
    $errors[1] = $sFileAlert;
    $errors[2] = $fileIndex;
    $errors[0] = 6;
    echo "Error:{$errors[0]};{$errors[1]};{$errors[2]}";
    exit();
}


$captcha->setNumber();
$TML->assign('RAND', rand(1000, 9999));
$args=array();
foreach ($_GET as $key=>$item) {
    if ($key != 'action') {
        $args[] = "$key=$item";
    }
}
$TML->assign('chaturi', "./?a=5".join('&', $args));
$TML->assign('MAX_FILES', feedback::MAX_FILES);
$TML->assign('u_token_key', $_SESSION['rand']);
if ($has_errors) {
  echo "Error:{$errors[0]};$errors[1]".($errors[2] ? ';'.$errors[2]:'');
  exit();
}

$visitSessionId = VisitSession::GetInstance()->updateCurrentOrCreateSession();

$params = array();
$params['visitsessionid'] = $visitSessionId;
$params['lastpingvisitor'] = null ;
$params['offline'] = 1;

$threads_count = MapperFactory::getMapper("Thread")->getNonEmptyThreadsCountByVisitorId($visitorid);

$thread = Thread::getInstance()->CreateThread(WEBIM_CURRENT_LOCALE, STATE_CLOSED, $params);
VisitSession::GetInstance()->UpdateVisitSession($visitSessionId, array('hasthread' => 1));
Thread::getInstance()->sendFirstMessageWithVisitorInfo($thread);

Visitor::getInstance()->setVisitorNameCookie($visitor_name);

$feedback = new feedback();
$uid = get_uid(false);
$login = $_SESSION['webim_uname'];
$email = $_SESSION['webim_email'];
$feedback->Add($uid, $login, $email, $department, iconv("UTF-8", "WINDOWS-1251//IGNORE", $message), $attach);
echo "Success: ok";exit();
?>
