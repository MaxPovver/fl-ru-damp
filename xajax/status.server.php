<?

$rpath = "../";
require_once ($_SERVER['DOCUMENT_ROOT'] . "/xajax/status.common.php");
require_once ($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");

function SaveStatus($text, $statusType, $login = NULL) {
    session_start();
    $freelancer = new freelancer();
    $text = addslashes(substr(stripslashes(trim($text)), 0, 200));
    close_tags($text, 's');
    $freelancer->status_text = antispam(htmlspecialchars(htmlspecialchars_decode(change_q_x(trim($text), true, false), ENT_QUOTES), ENT_QUOTES));
    $freelancer->status_type = intval($statusType);
    if ($freelancer->statusToStr($statusType)) {
        $stdStatus = "";
        $objResponse = new xajaxResponse();
        $uid = (hasPermissions('users') && $login != $_SESSION['login']) ? $freelancer->GetUid($err, $login) : get_uid(false);
        $pro = (hasPermissions('users') && $login != $_SESSION['login']) ? is_pro(true, $uid) : is_pro();
        $error = $freelancer->Update($uid, $res);
        if (!$freelancer->status_text)
            $freelancer->status_text = $stdStatus;
        $freelancer->status_text = stripslashes($freelancer->status_text);

        switch ($freelancer->status_type) {
			case 1:
				$status_cls = 'b-status b-status_busy';
				break;
			case 2:
				$status_cls = 'b-status b-status_abs';
				break;
			case -1:
				$status_cls = 'b-status b-status_no';
				break;
			default:
				$status_cls = 'b-status b-status_free';
        }
        
        if (!$noassign) {
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );
            $stop_words = new stop_words( hasPermissions('users') );
            $sStatusText = $pro ? $freelancer->status_text : $stop_words->replace( $freelancer->status_text );
            //$GLOBALS['xajax']->setCharEncoding("windows-1251");
            $jsobj = json_encode(array('data' => iconv('CP1251', 'UTF8', $freelancer->status_text)));
            $objResponse->assign("statusText", "innerHTML", ($freelancer->status_text == $stdStatus) ? "" : reformat($sStatusText, 40, 0, 1, 25) );
            $objResponse->assign("statusTitle", "innerHTML", $freelancer->statusToStr($statusType));
//            $objResponse->assign("statusTitle", "style.display", $statusType > -1 ? '' : 'none');
            $objResponse->script("statusType = {$statusType};
			                      statusTxt = document.getElementById('statusText').innerHTML;
			                      statusTxtSrc = {$jsobj};");
        }
        
        $objResponse->script("$('bstatus').erase('class');
             $('bstatus').addClass('{$status_cls}');");
    }
    return $objResponse;
}

$GLOBALS['xajax']->processRequest();
?>
