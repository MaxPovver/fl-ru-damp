<?
$rpath = "../";
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/contacts.common.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/messages.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/mess_ustf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/mess_folders.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/teams.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/ignor.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/notes.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/memBuff.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");

/**
 * Возвращает автоматические папки для массовых рассылок личных менеджеров.
 *
 * @param  string $sUid UID пользователя
 * @param  string $sYear год создания папки
 * @param  string $sOffset SQL OFFSET
 * @return object xajaxResponse
 */
function PmFolders( $sUid = 0, $sYear = 0, $sOffset = 0 ) {
    session_start();
	$objResponse = new xajaxResponse();
    
    if ( $_SESSION['uid'] == $sUid ) {
        $aYears = messages::pmAutoFoldersGetYears( $sUid );
        $sHtml  = '';
        
        if ( $aYears ) {
            $aYears    = array_unique( array_merge($aYears, array($sYear)));
            $sCurrYear = intval($sYear);
            $nOffset   = intval($sOffset);
            $nFolders  = messages::pmAutoFoldersCount( $sUid, $sCurrYear );
            $aFolders  = messages::pmAutoFolders( $sUid, $sCurrYear, messages::PM_AUTOFOLDERS_PP, $nOffset );
            $bNext     = ( ($nNext = $nOffset + messages::PM_AUTOFOLDERS_PP) + 1 <= $nFolders );
            $bPrev     = ( ($nPrev = $nOffset - messages::PM_AUTOFOLDERS_PP) >= 0 );
            
            $sHtml = '<h3>Ответы на платные рассылки</h3><ul class="archive-year c">';
            
            sort( $aYears );
            $aYears = array_reverse( $aYears );

            foreach ( $aYears as $sOne ) { 
                $sClass = ( $sOne == $sCurrYear ) ? ' class="active"' : '';
                $sClick = ( $sOne == $sCurrYear ) ? '' : ' onclick="xajax_PmFolders('. $sUid .', '. $sOne .', 0);"';
                $sHtml .= '<li'. $sClass .'><a href="javascript:void(0);"'. $sClick .'>'. $sOne. '</a></li>';
        	 }
        	 
            $sHtml .= '</ul><ul class="archive-list c">';

            foreach ( $aFolders as $aOne ) { 
                $sHtml .= '<li id="pm_folder'. $aOne['id'] .'"'. ($aOne['id'] == $pm_folder ? ' class="active"' : '') .'>
                	<a href="javascript:void(0);">
                        <span class="ar-del" onclick="pmFolderDel('. $sUid .', '. $aOne['id'] .', '. $sCurrYear .', '. $nOffset .');"></span>
                        <span class="ar-edit" onclick="xajax_PmFolderEdit('. $sUid .', '. $aOne['id'] .', '. $sCurrYear .', '. $nOffset .');"></span>
                        <span class="archive-date" onclick="pmFolderGo('. $sCurrYear .', '. $aOne['id'] .', '. $nOffset .')">'. date('d/m', strtotime($aOne['post_date'])) .'</span>
                        <span title="'. reformat($aOne['name'], 64, 0, 1) .'" class="archive-text" onclick="pmFolderGo('. $sCurrYear .', '. $aOne['id'] .', '. $nOffset .')"">'. reformat($aOne['name'], 64, 0, 1) .'<b></b></span>
                    </a>
                </li>';
            }
                 
            $sHtml .= '</ul>';
            
            if ($nNext || $bPrev ) {
            $sHtml .= '<p class="archive-prev">
                '. ($bPrev ? '<a onclick="xajax_PmFolders('. $sUid .', '. $sCurrYear .', '. $nPrev .');" href="javascript:void(0);" class="lnk-dot-grey">&laquo;Следующие</a>' : '') .'
                '. ( $bNext && $bPrev ? '&nbsp;|&nbsp;' : '' ) .'
                '. ($bNext ? '<a onclick="xajax_PmFolders('. $sUid .', '. $sCurrYear .', '. $nNext .');" href="javascript:void(0);" class="lnk-dot-grey">Предыдущие&raquo;</a>' : '' ) .'
            </p>';
            }
        }
        
        if ( $sHtml ) {
            $objResponse->assign( 'block-archives', 'innerHTML', $sHtml );
        }
        else {
            $objResponse->script('$("block-archives").destroy();');
        }
    }
    
    return $objResponse;
}

/**
 * Переименовывает автоматические папки для массовых рассылок личных менеджеров.
 *
 * @param  string $sUid UID пользователя
 * @param  string $sFolderId ID папки
 * @param  string $sYear год создания папки
 * @param  string $sOffset SQL OFFSET
 * @param  string $sAction действие
 * @param  string $sName новое название папки
 * @return object xajaxResponse
 */
function PmFolderEdit( $sUid = 0, $sFolderId = '', $sYear = 0, $sOffset = 0, $sAction = 'edit', $sName = '' ) {
    session_start();
	$objResponse = new xajaxResponse();
	
	if ( 
        $_SESSION['uid'] == $sUid // спрашивает тот кто залогинен
        && $sFolderId // спрашивает папку
        && $aFolder = messages::pmAutoFolderGetById( $sUid, $sFolderId ) // папка того кто спрашивает
    ) {
        if ( $sAction == 'update' ) {
            if ( $sName = trim($sName) ) {
                $sName = change_q_x( $sName, true, false );
                
                if ( !$sError = messages::pmAutoFolderRename($sUid, $sFolderId, $sName) ) {
                    return PmFolders( $sUid, $sYear, $sOffset );
                }
                else {
        		    $objResponse->alert('Ошибка изменения папки');
        		}
            }
            else {
                $objResponse->alert('Укажите название папки');
            }
        }
        elseif ( $sAction == 'edit' || $sAction == 'cancel' ) {
            if ( $sAction == 'edit' ) {
                $sHtml = '<span class="ar-del" onclick="pmFolderDel('. $sUid .', '. $sFolderId .', '. $sYear .', '. (int)$nOffset .');"></span>
                <span class="ar-edit"></span>
                <span class="archive-date">'. date('d/m', strtotime($aFolder['post_date'])) .'</span>
                <div class="form">
                	<div class="form-el">
                    	<div class="form-value">
                    		<textarea id="pm_fname'. $sFolderId .'" maxlength="63" cols="" rows="">'.$aFolder['name'].'</textarea>
                        </div>
                    </div>
                    <div class="form-btn">
                    	<input onclick="pmFolderEdit('. $sUid .', '. $sFolderId .', '. $sYear .', '. (int)$nOffset .');" type="button" value="Сохранить">
                        <a onclick="xajax_PmFolderEdit('. $sUid .', '. $sFolderId .', '. $sYear .', '. (int)$nOffset .', \'cancel\');" href="javascript:void(0);" class="lnk-dot-grey">Отменить</a>
                    </div>
                </div>';
            }
            else {
                $sHtml = '<a href="javascript:void(0);">
                    <span class="ar-del" onclick="pmFolderDel('. $sUid .', '. $sFolderId .', '. $sYear .', '. (int)$nOffset .');"></span>
                    <span class="ar-edit" onclick="xajax_PmFolderEdit('. $sUid .', '. $sFolderId .', '. $sYear .', '. (int)$nOffset .');"></span>
                    <span class="archive-date" onclick="pmFolderGo('. $sYear .', '. $sFolderId .', '. (int)$nOffset .')">'. date('d/m', strtotime($aFolder['post_date'])) .'</span>
                    <span title="'. reformat($aFolder['name'], 64, 0, 1) .'" class="archive-text" onclick="pmFolderGo('. $sYear .', '. $sFolderId .', '. (int)$nOffset .')"">'. reformat($aFolder['name'], 64, 0, 1) .'<b></b></span>
                </a>';
            }
            
            $objResponse->assign( "pm_folder".$sFolderId, "innerHTML", $sHtml );
        }
    }
	
	return $objResponse;
}

/**
 * Удаляет автоматические папки для массовых рассылок личных менеджеров.
 *
 * @param  string $sUid UID пользователя
 * @param  string $sFolderId ID папки
 * @param  string $sYear год создания папки
 * @param  string $sOffset SQL OFFSET
 * @return object xajaxResponse
 */
function PmFolderDel( $sUid = 0, $sFolderId = '', $sYear = 0, $sOffset = 0 ) {
    session_start();
	$objResponse = new xajaxResponse();
	
	if ( 
        $_SESSION['uid'] == $sUid // спрашивает тот кто залогинен
        && $sFolderId // спрашивает папку
        && $aFolder = messages::pmAutoFolderGetById( $sUid, $sFolderId ) // папка того кто спрашивает
    ) {
        if ( !$sError = messages::pmAutoFolderDelete($sUid, $sFolderId) ) {
            return PmFolders( $sUid, $sYear, $sOffset );
        }
        else {
		    $objResponse->alert('Ошибка удаления папки');
		}
    }
	
	return $objResponse;
}

function FormSave($login, $text, $action, $rl, $num){
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
	session_start();
	$objResponse = new xajaxResponse();
	$action = trim($action);
 
    // Режем тег <script>
	$text = strip_only(trim($text),'<script>');
	//$text = stripslashes($text);
	$text = change_q_x($text, FALSE, TRUE, "", false, false);

	if ($rl  == '1') $s_role= "_emp"; else $s_role= "_frl";
	if ($text == '') $s_role = "";
	$noassign = 0;
	$nuid = get_uid(false);
	$user = new users;
	$nTargetId = $user->GetUid( $sError, $login );
	switch ($action) {
		case "add":
			if ($text) $error = notes::Add($nuid, (int)$nTargetId, $text); break;
		case "update":
			if ($text) $error = notes::Update($nuid, (int)$nTargetId, $text);
			else $error = notes::DeleteNote($nuid, (int)$nTargetId);
			break;
		default:
			$noassign = 1;
	}
    $text = stripslashes($text);
	$text = reformat($text, 24, 0, 0, 1, 24);

	if ($s_role == "") $text = "Вы можете оставить заметку о&nbsp;пользователе. Видеть написанное будете только вы и никто другой.";
	if (!$noassign){
		$GLOBALS['xajax']->setCharEncoding("windows-1251");
		$objResponse->assign("notetext".$num,"innerHTML", $text);
		$objResponse->assign("notetd".$num,"className","note".$s_role);
		if (!$s_role == "") $objResponse->script("txt[".$num."] = '".$text."';");
		$objResponse->script("act[".$num."] = '".(($s_role)?"update":"add")."';");
	}
	return $objResponse;
}

function ChFolder($folder_id, $cur_folder, $login){
	session_start();
    $objResponse = new xajaxResponse();
    $user = new users();
    $user->GetUser($login);
    if(!$user->is_banned) {
    	$folder_id = intval($folder_id);
	    $login = addslashes($login);
    	$cur_folder = intval($cur_folder);
    	$uid = get_uid(false);
    	if ($folder_id && $login && $folder_id > 0){
    		$fld = new mess_ustf();
    		$fld->from_id = $uid;
    		$fld->folder = $folder_id;
    		$passive = $fld->Change($login);
    		if ($cur_folder == -3){
      		$to_id = users::GetUid($error, $login);
    			$restored_error = messages::RestoreFromUsers($uid, array($to_id));
    			if ($restored_error){
        		$objResponse->alert($restored_error);
    			}
    			else{
        		$objResponse->assign("ur".$login,"style.display","none");
    			}
    		}
    	} if ($folder_id < 0){
    		if ($folder_id == -1){
    			$fld = new teams();
    			$fld->user_id = $uid;
    			$passive = !$fld->teamsInverseFavorites($login);
      		if ($cur_folder == -3){
        		$to_id = users::GetUid($error, $login);
      			$restored_error = messages::RestoreFromUsers($uid, array($to_id));
      			if ($restored_error){
          		$objResponse->alert($restored_error);
      			}
      			else{
          		$objResponse->assign("ur".$login,"style.display","none");
      			}
      		}
    		}
    		if ($folder_id == -2){
    			$fld = new ignor();
    			$fld->user_id = $uid;
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/memBuff.php");
    			$passive = !$fld->Change($login);
                $objResponse->assign("ur".$login,"style.display","none");
                $objResponse->script("Notification();");
                $memBuff = new memBuff();
                $memBuff->delete("msgsCnt" . $uid); 
      		if ($cur_folder == -3){
        		$to_id = users::GetUid($error, $login);
      			$restored_error = messages::RestoreFromUsers($uid, array($to_id));
      			if ($restored_error){
          		$objResponse->alert($restored_error);
      			}
      			else{
          		$objResponse->assign("ur".$login,"style.display","none");
      			}
      		}
    		}
    		if ($folder_id == -3){
    			$passive = !messages::DeleteFromUsers($login);
    		}
    	}
    	if ($passive){
    		$objResponse->assign("folder".$folder_id."u".$login,"className","active");
    		$objResponse->assign("vfolder".$folder_id."u".$login,"className","active");
    		$inc = 1;
    	}
    	else {
    	  if ($cur_folder == $folder_id)
    	  {
      		$objResponse->assign("ur".$login,"style.display","none");
    	  }
    	  else
    	  {
      		$objResponse->assign("folder".$folder_id."u".$login,"className","");
      		$objResponse->assign("vfolder".$folder_id."u".$login,"className","passive");
    	  }
    		$inc = -1;
    	}
    	$objResponse->script("document.getElementById('fldcount".$folder_id."').innerHTML = ".$inc."+Math.round(document.getElementById('fldcount".$folder_id."').innerHTML);");
    }
	return $objResponse;
}

function ChFolderInner($folder_id, $login){
	session_start();
    $objResponse = new xajaxResponse();
	$folder_id = intval($folder_id);
    $user = new users();
    $user->GetUser($login);
    if(!$user->is_banned) {
    	$login = addslashes($login);
    	$uid = get_uid(false);
    	if ($folder_id && $login && $folder_id > 0){
    		$fld = new mess_ustf();
    		$fld->from_id = $uid;
    		$fld->folder = $folder_id;
    		$passive = $fld->Change($login);
    	}
    	if ($folder_id < 0){
    		if ($folder_id == -1){
    			$fld = new teams();
    			$fld->user_id = $uid;
    			$passive = !$fld->teamsInverseFavorites($login);
    		}
    		if ($folder_id == -2){
    			$fld = new ignor();
    			$fld->user_id = $uid;
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/memBuff.php");
    			$passive = !$fld->Change($login);
                $memBuff = new memBuff();
                $memBuff->delete("msgsCnt" . $uid); 
                $objResponse->script("GetNewMsgCount();");
    		}
    		if ($folder_id == -3){
    			$passive = messages::DeleteFromUsers($login);
        		$objResponse->addScrip("document.location='/contacts/';");
    		}
    	}
    	if ($passive) {
    		$objResponse->assign("folder".$folder_id,"className","active");
    		$objResponse->assign("vfolder".$folder_id,"className","active");
    	}
    	else {
      		$objResponse->assign("folder".$folder_id,"className","");
      		$objResponse->assign("vfolder".$folder_id,"className","passive");
    	}
    }
	return $objResponse;
}

function RnFolder($form_values){
	session_start();
	$fuid = get_uid(false);
	$objResponse = new xajaxResponse();
	$bError = false;
	$fld = new mess_folders();
	$msgs = new messages();
	$contacts = $msgs->GetContacts($fuid);
	$users_folders = $msgs->GetUsersInFolders($fuid);

	if (!isset($form_values['id']))
	{
		$objResponse->alert("Не выбрана папка.");
		$bError = true;
	}
	else
	{
    $folder_id = intval($form_values['id']);
    $cur_folder = intval($form_values['cur_folder']);
    $cont = intval($form_values['cont']);
    $logins = addslashes($form_values['logins']);
    $arr_logins = split('~', $logins);
  	if ($folder_id <= 0)
  	{
  		$objResponse->alert("Не выбрана папка.");
  	}
  	else
  	{
    	if (!isset($form_values['new_name']))
    	{
    		$objResponse->alert("Не указано новое имя папки.");
    	}
    	else
    	{
//        $folder_name = addslashes($form_values['new_name']);
        $folder_name = substr(change_q($form_values['new_name'], false, 64),0,64);
        $request_folder_name = substr(change_q(stripslashes($form_values['new_name']), false, 64),0,64);
     		$srch = array("<", ">");
    		$folder_name = trim(str_replace($srch,"",$folder_name));
    		
      	if (empty($folder_name) || ($folder_name==''))
      	{
      		$objResponse->alert("Не указано новое имя папки.");
          $objResponse->assign("savebtn","disabled",false);
      	}
      	else
      	{
            	$fld->fname = $folder_name;
				$fld->id = $folder_id;
				$fld->from_id = get_uid(false);
            	if (!($ermsg = $fld->Rename($folder_id)))
				{
            	
					$GLOBALS['xajax']->setCharEncoding("windows-1251");
  
				$objResponse->script("old_name='". $folder_name . "';");
          		if ($cur_folder == $folder_id)
            	{
            	  	$folder_html = "<img class=\"li\" src=\"/images/ico_dir.gif\" />" . reformat($request_folder_name,15,0,1);
              		$folder_header = 'Сообщения / ' . reformat($folder_name,15,0,1);
                	$objResponse->assign("cht","innerHTML",$folder_header);
            	}
            	else
            	{
            	  $folder_html = "<a href=\"/contacts/?folder=" . $folder_id . "\"><span style=\"float:left;\"><img class=\"li\" src=\"/images/ico_dir.gif\" /></a></span><a href=\"/contacts/?folder=" . $folder_id . "\" class=\"blue\">" . reformat($request_folder_name,15,0,1) . "</a>";
            	}
            	foreach($arr_logins as $login)
            	{
                	$objResponse->assign("folder".$folder_id."u".$login,"innerHTML",reformat($folder_name,25,0,1));
                	$objResponse->assign("vfolder".$folder_id."u".$login,"innerHTML",reformat($folder_name,25,0,1));
            	}
            		$folder_html .= " (<span id=\"fldcount" . $folder_id . "\">" . $cont . "</span>)";
            		$folder_html .= "<div style=\"margin-top: 17px; text-align:right\"><a href=\"/contacts/?action=delfolder&id=" . $folder_id . "\" onClick=\"return warning(9)\" title=\"Удалится только папка. Контакты переместятся в&nbsp;папку &laquo;Все&raquo;.\">Удалить</a> | <a href='javascript:rename(\"" . $folder_id . "\",\"" . $cur_folder . "\",\"" . str_replace("\\", "\\\\", htmlspecialchars($request_folder_name)) . "\",\"" . $cont . "\",\"" . $logins . "\");'>Переименовать</a></div>";
              		$objResponse->assign("li_folder".$folder_id,"innerHTML",$folder_html);
			  
			   } else {
			     	$objResponse->alert($ermsg);
                 	$objResponse->assign("savebtn","disabled",false);
			   }			
      	}
    	}
  	}
	}
	return $objResponse;
}

/**
 * Возвращает список жалоб на спам для личного сообщения в админке.
 * 
 * с xajax не работает
 * 
 * @param  int $nSpamId ID записи из messages_spam
 * @param  string $sMsgMd5 MD5 хэш текста сообщения
 * @return string json_encode данные
 */
function getSpamComplaints( $nSpamerId = 0, $sMsgMd5 = '' ) {
    session_start();
    
    $res = array();
    
    if ( hasPermissions('projects') && $nSpamerId && $sMsgMd5 ) {
        require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/messages_spam.php');
        
        $oSpam = new messages_spam();
        $aMsgs = $oSpam->getSpamComplaints( $nSpamerId, $sMsgMd5 );
        $aData = array();
        
        foreach ( $aMsgs as $aOne ) {
            $aTmp = array(
                'login'   => iconv('CP1251', 'UTF-8', $aOne['user_login']), 
                'name'    => iconv('CP1251', 'UTF-8', $aOne['user_name']), 
                'surname' => iconv('CP1251', 'UTF-8', $aOne['user_surname']), 
                'date'    => date('d.m.Y', strtotime($aOne['complain_time'])),
                'time'    => date('H:i', strtotime($aOne['complain_time'])),
                'text'    => $aOne['complain_text'] ? iconv('CP1251', 'UTF-8', hyphen_words(reformat($aOne['complain_text'], 45), true)) : ''
            );
        	$aData[] = $aTmp;
        }
        
        $res['success'] = true;
        $res['data']    = $aData;
    } 
    else {
        $res['success'] = false;
    }
    
    echo json_encode( $res );
}

/**
 * Сохраняет жалобу на спам в личных сообщениях
 * 
 * @param  string $sSpamerId UID спамера
 * @param  string $sUserId UID пожаловавшегося пользователя
 * @param  string $sParams JSON строка с массивом параметров жалобы на спам
 * @return object xajaxResponse
 */
function sendSpamComplaint( $sSpamerId = '', $sUserId = '', $sParams = '' ) {
    session_start();
	$objResponse = new xajaxResponse();
    $aParams     = _jsonArray( $sParams );
    
    if ( $sSpamerId && $sUserId && $aParams ) {
    	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/messages_spam.php");
    	
    	$oSpam = new messages_spam();
    	$bSpam = $oSpam->addSpamComplaint( $sSpamerId, $sUserId, $aParams );
    	
    	if ( $bSpam ) {
            $objResponse->assign( 'mess_spam_'.$aParams['num'], 'innerHTML', messages_spam::COMPLAINT_PENDING_TXT );
    	}
    	else {
    	    $objResponse->alert('Ошибка сохранения жалобы');
    	}
    	
    	$objResponse->script("$('spam_complaint_popup').setStyle('display','none');");
    }
    
    return $objResponse;
}


$xajax->processRequest();
?>
