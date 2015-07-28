<?
$rpath = "../";
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.common.php");
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/admin_log.php');

// ---- функции для работы с удалением сущностей ----

/**
 * Устанавливает поля в форме удаления сущностей
 * 
 * @param  string $sId составной уникальный ID сущности
 * @param  string $sDrawFunc имя функции для выполнения после сохранения
 * @param  string $sParams JSON кодированные дополнительные параметры
 * @return xajaxResponse 
 */
function setDelReasonForm( $sId = '', $sDrawFunc = '', $sParams = '' ) {
    session_start();
    $objResponse = new xajaxResponse();
    
    $sUniqId = "delreason_$sId";
    $aParams = _jsonArray( $sParams );
    $sReasonText    = '';
    
    list( $s_content_id, $s_rec_id, $s_rec_type ) = explode( '_', $sId );
    
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/permissions.php' );
    require_once($_SERVER['DOCUMENT_ROOT']."/classes/user_content.php");
    
    $aPermissions = permissions::getUserPermissions( $_SESSION['uid'] );
    $user_content = new user_content( $_SESSION['uid'], $aPermissions );
    
    if ( !$user_content->hasContentPermissions($s_content_id) ) {
        if ( $sDrawFunc == 'stream0' || $sDrawFunc == 'stream1' || $sDrawFunc == 'stream2' ) { // удаление в потоках
            $sHtml = _parsePermissionsDenied( $objResponse );
            $objResponse->script("$('{$aParams['stream_id']}').contentWindow.$('my_div_all').set('html', '$sHtml')");
        }
        
        return $objResponse;
    }
    
    if ( $sDrawFunc == 'stream0' || $sDrawFunc == 'stream1' || $sDrawFunc == 'stream2' ) { // удаление в потоках
        if ( !$user_content->checkContent( $s_content_id, $aParams['stream_id'], $s_rec_id) ) {
            $objResponse->alert( "Пользователь удалил или изменил данные.\nЛибо запись заблокирована." );
            
            if ( $sDrawFunc == 'stream0' ) {
                $objResponse->script( "$('{$aParams['stream_id']}').contentWindow.user_content.getContents();" );
            }
            else {
                $objResponse->script( "$('{$aParams['stream_id']}').contentWindow.$('my_div_content_{$sId}').destroy();" );
                $objResponse->script( "$('{$aParams['stream_id']}').contentWindow.user_content.spinner.hide();" );
            }
            
            return $objResponse;
        }
    }
    
    $s_nActId = user_content::getReasonGroup($s_content_id, $s_rec_type);
    
    $sSelectOptions = _getAdminActionReasonOptions( $s_nActId, $aCurrBan['reason'] );
    
    $objResponse->script( "banned.banUid = '$sUniqId';" );
    $objResponse->script( "banned.buffer['$sUniqId'].act_id=$s_nActId;" );
    $objResponse->script( "banned.buffer['$sUniqId'].customReason[$s_nActId]='$sCustomReason';" );
    
    $sBanDiv = '<div id="bfrm_div_sel_' . $sUniqId . '"><select id="bfrm_sel_' . $sUniqId . '"  class="b-select__select b-select__select_width_full" name="bfrm_sel_' 
       . $sUniqId . '" onchange="banned.setDelReason(\''. $sUniqId .'\');">' . $sSelectOptions . '</select></div>';

    $objResponse->assign( 'delreason_div_select', 'innerHTML', $sBanDiv );

    $sBanDiv = '<textarea id="bfrm_' . $sUniqId . '" name="bfrm_' . $sUniqId . '" cols="" rows="" class="b-textarea__textarea b-textarea__textarea_height_50">' 
        . $sReasonText . '</textarea>';

    $objResponse->assign( 'delreason_div_textarea', 'innerHTML', $sBanDiv );

    $sBanDiv = '<a id="ban_btn" href="javascript:void(0);" class="b-button b-button_flat b-button_flat_green" onclick="banned.commit(banned.banUid,$(\'bfrm_\'+banned.banUid).get(\'value\'))">Сохранить</a>
        <span class="b-buttons__txt b-buttons__txt_padleft_10">или</span>
        <a href="javascript:void(0);" class="b-buttons__link b-buttons__link_dot_c10601" onclick="banned.commit(banned.banUid,(banned.buffer[banned.banUid].action=\'close\'));$(\'ov-notice22-r\').toggleClass(\'b-shadow_hide\');return false;">закрыть, не сохраняя</a>';
    
    $objResponse->assign( 'delreason_ban_btn', 'innerHTML', $sBanDiv );
    
    switch ( $s_content_id ) {
        case user_content::MODER_BLOGS: 
        case user_content::MODER_COMMUNITY: 
            if ( $s_rec_type == 1 ) {
                $sH4 = 'Причина блокировки';
            }
            else {
                $sH4 = 'Причина удаления';
            }
            break;
        case user_content::MODER_PRJ_OFFERS:
            if ( $s_rec_type == 7 ) {
                $sH4 = 'Причина удаления';
            }
            else {
                $sH4 = 'Причина блокировки';
            }
            break;
        case user_content::MODER_PROJECTS:
        case user_content::MODER_PRJ_DIALOG:
        case user_content::MODER_PORTFOLIO:
        case user_content::MODER_SDELAU:
            $sH4 = 'Причина блокировки';
            break;
        case user_content::MODER_MSSAGES:
        case user_content::MODER_ART_COM:
        case user_content::MODER_PROFILE:
        case user_content::MODER_CONTEST_COM:
        case user_content::MODER_PORTF_CHOISE:
        default:
            $sH4 = 'Причина удаления';
            break;
    }
    
    $objResponse->assign( 'delreason_d4', 'innerHTML', $sH4 );
    
    $objResponse->script( "$('ov-notice22-r').toggleClass('b-shadow_hide');" );
    $objResponse->script( "$('ov-notice22-r').setStyle('display', '');" );
    
    $objResponse->script( "$('ban_btn').removeClass('b-button_rectangle_color_disable');" );
    $objResponse->script( "$('ban_btn').addClass('b-button_rectangle_color_green');" );
    
    return $objResponse;
}


/**
 * Разблокирование сущности, ввиде гномика ;)
 * 
 * @param  string $sId составной уникальный ID сущности
 */
function unBlocked($sId){
    session_start();
    $objResponse = new xajaxResponse();
    $uid = get_uid(FALSE);
    
    list( $rec_content_id, $rec_id, $rec_type ) = explode( '_', $sId );
    
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/permissions.php' );
        
    $aPermissions = permissions::getUserPermissions($uid);
    $user_content = new user_content($uid, $aPermissions ); 
    
    if ( $user_content->hasContentPermissions($rec_content_id) ) {
        switch ( $rec_content_id ) {
            
            case user_content::MODER_TSERVICES:
                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/tservices.php' );
                $tserviceObj = new tservices();
                $tserviceObj->unBlocked($rec_id, $uid);
                $objResponse->script("$$('#__tservices_blocked,#__tservices_unblocked').toggleClass('b-button_hide');");  
            break;
        
            case user_content::MODER_SBR_REQV:
                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/sbr_meta.php' );
                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/Finance/FinanceSmail.php' );
                
                if (sbr_meta::reqvUnBlocked($rec_id, $uid, $_SESSION['login'])) {
                    $finSmail = new FinanceSmail();
                    $finSmail->financeUnBlocked($rec_id);
                    $objResponse->script("$$('#__finance_blocked,#__finance_unblocked').toggleClass('b-button_hide');");
                }
                 
            break;
        }
    }
    
    return $objResponse;
}


/**
 * Удаление сущности
 * 
 * @param  string $sId составной уникальный ID сущности
 * @param  int $sUid UID сздателя сущности
 * @param  string $sReason причина удаления
 * @param  string $sDrawFunc имя функции для выполнения после сохранения
 * @param  string $sParams JSON кодированные дополнительные параметры
 * @return xajaxResponse 
 */
function setDeleted( $sId = '', $sUid = 0, $sReason = '', $sDrawFunc = '', $sParams = '' ) {
    session_start();
    $objResponse = new xajaxResponse();
    
    list( $rec_content_id, $rec_id, $rec_type ) = explode( '_', $sId );
        
    $aParams = _jsonArray( $sParams );
    
    if ( $sDrawFunc == 'stream0' || $sDrawFunc == 'stream1' || $sDrawFunc == 'stream2' ) { // удаление в потоках
        resolveContent( $aParams['content_id'], $aParams['stream_id'], $sId, 2, $sUid, $aParams['content_cnt'], $aParams['status'], $aParams['is_sent'], $sReason, $objResponse );
    }
    else { 
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/permissions.php' );
        
        $aPermissions = permissions::getUserPermissions( $_SESSION['uid'] );
        $user_content = new user_content( $_SESSION['uid'], $aPermissions );
        $bSend        = false;
        
        if ( $user_content->hasContentPermissions($rec_content_id) ) {
            if ( strpos($sReason, '%USERNAME%') !== false && $sUid ) {
                $user = new users;
                $user->GetUserByUID( $sUid );
                $sReason = str_replace( '%USERNAME%', $user->uname . ' ' .$user->usurname, $sReason );
            }
            
            switch ( $rec_content_id ) {
                case user_content::MODER_MSSAGES:
                    switch ($sDrawFunc) { // дейсвие из переписки между юзерами
                        case 'updateLetter':
                            updateLetter( $rec_id, $sUid, 2, $sReason, $objResponse );
                            break;
                        case 'delLetter': // в потоке - важно чтобы поток не был потерян либо перехвачен
                        default:
                            delLetter( $aParams['stream_id'], $sUid, $rec_id.'_'.$rec_type, $sReason, $objResponse );
                            break;
                    }
                    break;
                case user_content::MODER_BLOGS:
                    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/blogs.php' );
                    
                    if ( $rec_type == 1 ) { // post
                        blogs::MarkDeleteBlog( $_SESSION['uid'], $rec_id, $group, $base, $thread_id, $page, $msg, 0, $sReason );
                    }
                    else { // comment
                        blogs::MarkDeleteMsg( $_SESSION['uid'], $rec_id, getRemoteIP(), $err, 0, $sReason );
                    }
                    
                    $bSend = true;
                    break;
                    
                case user_content::MODER_TSERVICES:
                    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/tservices.php' );
                    $tserviceObj = new tservices();
                    if ($tserviceObj->Blocked($rec_id, $_SESSION['uid'], $sReason)) {
                        $objResponse->script("$('ov-notice22-r').toggleClass('b-shadow_hide');" );
                        $objResponse->script("$$('#__tservices_blocked,#__tservices_unblocked').toggleClass('b-button_hide');");
                    }
                    break;
                
                case user_content::MODER_SBR_REQV:
                    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/sbr_meta.php' );
                    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/Finance/FinanceSmail.php' );
                    
                    if(sbr_meta::reqvBlocked($rec_id, $_SESSION['uid'], $sReason, 0, $_SESSION['login']))
                    {
                        $finSmail = new FinanceSmail();
                        $finSmail->financeBlocked($rec_id, $sReason);
                        
                        $objResponse->script("$('ov-notice22-r').toggleClass('b-shadow_hide');" );
                        $objResponse->script("$$('#__finance_blocked,#__finance_unblocked').toggleClass('b-button_hide');"); 
                    }                   
                    break;
                    
                default:
                    break;
            }
            
            if ( $bSend ) {
                $user_content->sendNotification( $_SESSION['uid'], $sUid, $rec_content_id, $rec_id, $rec_type, 2, $sReason );
                $objResponse->script( 'window.location.reload(true)' );
            }
        }
        
        return $objResponse;
    }
    
    $objResponse->script( "delete banned.buffer['delreason_$sId'];" );
    $objResponse->script( "parent.$$(\"div[id^='ov-notice']\").setStyle('display', 'none');" );
    $objResponse->script( "parent.$('ov-notice22-r').toggleClass('b-shadow_hide');" );
    
    return $objResponse;
}

// ---- функции для работы с баном пользователей ----

/**
 * Изменение бана пользователя
 * 
 * @param  string $sUsers JSON строка с массивом UID пользователей
 * @param  int $nActId
 * @param  string $sReasonTxt причина
 * @param  int $nReasonId ID причины, если она выбрана из списка (таблица admin_reasons)
 * @param  int $nNoSend опционально. установить в 1 если не нужно оповещать юзера о том что он забанен.
 * @param  string $sContext Контекст (для лога админских действий)
 * @param $sContext
 * @param bool $noticeSbrPartners уведомить партнеров по сделке о блокировке аккаунта
 * @return object xajaxResponse
 */
function updateUserBan( $sUsers = '', $nActId, $sReasonTxt = '', $nReasonId = null, $sDateTo = '', $nNoSend = 0, $sContext = '', $noticeSbrPartners = false ) {
    session_start();
    $objResponse = new xajaxResponse();
    
    if ( hasPermissions('users') ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
        require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/messages_spam.php');
        require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/messages.php');
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/sbr_emp.php' );
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/sbr_frl.php' );
        
        $objUser   = new users();
        $nReasonId = ($nReasonId) ? $nReasonId : null;
        $aContext  = _jsonArray( $sContext );
        $aContext  = $aContext ? $aContext : array('uid' => '', 'code' => 0, 'link' => '', 'name' => '');
        $aUsers    = _jsonArray( $sUsers );
        $bCheck    = true;
        
        if ( $aContext['uid'] == 'moder' ) {
            global $user_content;

            $bCheck = $user_content->checkContent( $aContext['contentId'], $aContext['streamId'], $aContext['recId'] );
        }
        
        if ( $bCheck && $aUsers && hasPermissions('users') ) {
            foreach ( $aUsers as $sUid ) {
                $objUser->GetUserByUID( $sUid );
                            
                if ( $objUser->uid ) {
                    $sReason  = str_replace( '%USERNAME%', $objUser->uname . ' ' . $objUser->usurname, $sReasonTxt );
                    $sReason  = change_q( $sReason, FALSE, 0, TRUE );
                    $sUniqId  = "userban_$sUid";
                    $sObjName = $objUser->uname. ' ' . $objUser->usurname . '[' . $objUser->login . ']';
                    $sObjLink = '/users/' . $objUser->login;
                    
                    if ( $nActId == 1 ) {
                        // разблокируем
                        if ( $objUser->is_banned || $objUser->ban_where ) {
                            if ( $objUser->is_banned ) {
                                $objResponse->script("$$('.warnbutton-$sUid').setStyle('display','');");
                                $objResponse->script("$$('.warnlist-$sUid').set('html','');");
                                $objResponse->script("$$('span[id^=\"warn_{$user->uid}\"]').set('html', '0')");
                                $objResponse->script("$$('div[id^=\"warn_{$user->uid}\"]').set('html', '0')");
                            }
                            //$objUser->ban_where = 0;
                            $objUser->unsetUserBan( $sUid, $objUser->ban_where );
                            
                        	// пишем лог админских действий
                        	$nLogActId = $objUser->is_banned ? 4 : 6;
                            if ($objUser->self_deleted == 't') $nLogActId = admin_log::ACT_ID_RESTORE_ACC;
                    		admin_log::addLog( admin_log::OBJ_CODE_USER, $nLogActId, $sUid, $sUid, $sObjName, $sObjLink, 0, '', $nReasonId, $sReason );
                        }
                        
                        $objResponse->script( "$$('.warnlink-$sUid a').set('html','Забанить!');" );
                        $objResponse->script( "$$('.comm-ban-$sUid a').set('html','Заблокировать');" );
                        $objResponse->script( "$$('.admin-block h4 em').setStyle('display','none');" );

                        if($objUser->uid == $aContext['uid']) {
                            $objResponse->script( "$('banreasonblock-{$objUser->uid}').setStyle('display','none');" );
                        }
                        $warns = $objUser->GetWarns($sUid);
                        $warncount = $warns ? count($warns) : 0;
                        $objResponse->script("if($('warncount-{$sUid}')) { $('warncount-{$sUid}').set('html', {$warncount}); }");

                    }
                    else {
                        if ( $sDateTo ) {
                            $sError = '';
                            $aDate  = explode( '-', $sDateTo );
                            
                            if ( !$aDate[1] || !$aDate[2] || !$aDate[0] || !checkdate($aDate[1], $aDate[2], $aDate[0]) ) {
                                $sError = 'Укажите корректную конечную дату';
                            }
                            elseif ( ($toRes = strtotime($sDateTo)) <= time() ) {
                                $sError = 'Укажите конечную дату в будущем';
                            }
                            
                            if ( $sError ) {
                                $objResponse->alert( $sError );
                                $objResponse->script("$('ban_btn').set( 'disabled', false );$('ban_btn').set( 'value', 'Сохранить' );");
                                return $objResponse;
                            }
                        }
                        
                        if ( $objUser->is_banned || $objUser->ban_where ) {
                            // редактируем текущую блокировку
                            $nBanWhere = ( $nActId == 3 ) ? 0 : 1;
                            $objUser->updateUserBan( $sUid, $objUser->ban_where, $nBanWhere, $sReason, $nReasonId, $sDateTo );
                        }
                        else {
                            $nBanWhere = ( $nActId == 3 ) ? 0 : 1;
                            $sBanId = $objUser->setUserBan( $sUid, $nBanWhere, $sReason, $nReasonId, $sDateTo, $nNoSend );
                            
                            // пишем лог админских действий
                            admin_log::addLog( admin_log::OBJ_CODE_USER, $nActId, $sUid, $sUid, $sObjName, $sObjLink, $aContext['code'], $aContext['link'], $nReasonId, $sReason, $sBanId, $aContext['name'] );
                            
                            // уведомляем партнеров по сбр
                            if ($noticeSbrPartners) {
                                if (is_emp($objUser->role)) {
                                    $sbr = new sbr_emp($sUid);
                                } else {
                                    $sbr = new sbr_frl($sUid);
                                }
                                $sbrPartners = $sbr->_new_getOpenSbrPartners();
                                $messages = new messages();
                                $messages->yourSbrPartnerIsBanned($sbrPartners, $objUser->login);
                            }
                            
                            $objResponse->script( "$$('.warnlink-$sUid a').set('html','Разбанить');" );
                            $objResponse->script( "$$('.comm-ban-$sUid a').set('html','Разблокировать');" );
                            
                            if ( !$nBanWhere ) {
                                $GLOBALS['session']->nullActivityByLogin( $objUser->login );
                                
                            	// статус присутсвия ------
                                $online_status = $GLOBALS['session']->view_online_status($objUser->login, false);
                                $ago = $GLOBALS['session']->ago;
                                if (!$GLOBALS['session']->is_active && $objUser->last_time) {
                                    $fmt = 'ynjGi';
                                    if (time() - ($lt = strtotime($objUser->last_time)) > 24 * 3600) {
                                        $fmt = 'ynjG';
                                        if (time() - $lt > 30 * 24 * 3600)
                                            $fmt = 'ynj';
                                    }
                                    $ago = ago_pub($lt, $fmt);
                                }
                                if(!$ago) $ago = "менее минуты";
                                $online_status .= '&nbsp;<span class="'.($GLOBALS['session']->is_active ? 'u-act' : '').'">'.($GLOBALS['session']->is_active ? 'на сайте' : 'Был'.($objUser->sex == 'f' ? 'а' : '').' на сайте: '.$ago.' назад').'</span>';
                                //-------------------------
                                
                            	$objResponse->script( "$$('.colB .bBA').set('html','$online_status');" );
                            }
                        }
                    
                        $warns = $objUser->GetWarns($sUid);
                        $warncount = $warns ? count($warns) : 0;
                        $objResponse->script("if($('warncount-{$sUid}')) { $('warncount-{$sUid}').set('html', {$warncount}); }");
    
                        $sDisplay = $nBanWhere = ( $nActId == 3 ) ? 'none' : '';
                        if($sDisplay=='' && $warncount==3) { $sDisplay = 'none'; }
                        $objResponse->script("$$('.warnbutton-$sUid').setStyle('display','$sDisplay');");

                        if($objUser->uid == $aContext['uid']) {
                            $sDateToParts = preg_split("/-/", $sDateTo);
                            $admin_info = $objUser->getName(get_uid(), $ee);
                            $objResponse->assign('banreasonblock-text-'.$objUser->uid, 'innerHTML', 'Блокировка '.($nBanWhere?'везде':'в блогах').' '.($sDateTo?'до '.$sDateToParts[2].' '.monthtostr($sDateToParts[1], true).' '.$sDateToParts[0]:'навсегда'));
                            $objResponse->assign('banreasonblock-comment-'.$objUser->uid, 'innerHTML', reformat($sReason, 50));
                            $objResponse->assign('banreasonblock-date-'.$objUser->uid, 'innerHTML', date("d.m.Y H:i"));
                            $objResponse->assign('banreasonblock-admin-'.$objUser->uid, 'innerHTML', $admin_info['login']);
                            $objResponse->assign('banreasonblock-admin-'.$objUser->uid, 'href', '/users/'.$admin_info['login']);
                            $objResponse->script( "$('banreasonblock-{$objUser->uid}').setStyle('display','block');" );                            
                        }
                        
                        // удаляем все жалобы на спам для юзера
                        $oSpam = new messages_spam();
                        $oSpam->deleteSpamBySpamer( $sUid, 3 );
                    }
                    
                    $objResponse->script( "delete banned.buffer['userban_$sUid'];" );
                    $str = "<b>Пользователь&nbsp;забанен</b>";
                    if ($objUser->is_banned) {
                    	$str = "";
                    }
                    $objResponse->script( "
                    if ($('user_banned_".$objUser->uid."')) { 
                    	$('user_banned_".$objUser->uid."').set('html', '$str');
                    }else {
                    	var p = $('user_first_paragraph_".$objUser->uid."');                    	
                    	if (p) {
                    		var span = new Element('span', {\"style\":\"color:#000\", \"id\":\"user_banned_".$objUser->uid."\"});
                    		span.inject(p, 'bottom');
							span.set('html', '$str');                 		
                    	}
                    }
                    " );
                }
            }
        }
        
        if ( $aContext['uid'] == 'gray_ip' ) {
        	$objResponse->script( 'if(window.opener){window.opener.window.location.reload(true);}' );
        }
        
        $objResponse->script( "$$(\"div[id^='ov-notice']\").setStyle('display', 'none');" );
        $objResponse->script( "$('ov-notice22').toggleClass('b-shadow_hide');" );
        $objResponse->script( "if(banned.reload==1){window.location.reload(true);}" );
        $objResponse->script( "if(banned.zero){banned.zeroShow();}" );
        $objResponse->script( "if(typeof(adminLogCheckUsers)!='undefined'){adminLogCheckUsers(false);};if($('chk_all')){\$('chk_all').checked=false;};" );
        
        if ( $aContext['uid'] == 'admin_log_page' || $aContext['uid'] == 'admin_user_search' ) {
        	$objResponse->script( 'window.location.reload(true)' );
        }
        
        if ( $aContext['uid'] == 'moder' ) {
            // если бан делается из потока модерирования
            $objResponse->script( 'user_content.resolveContent(user_content.resolveSid, 2, user_content.resolveUid)' );
            $objResponse->script( "parent.$$(\"div[id^='ov-notice']\").setStyle('display', 'none');" );
            $objResponse->script( "parent.$('ov-notice22').toggleClass('b-shadow_hide');" );
        }
    }
    
    return $objResponse;
}

/**
 * Устанавливает поля в форме редактирования бана пользователя
 * 
 * @param  int $sUid UID пользователя
 * @param  int $edit флаг редактирования причины блокировки
 * @return object xajaxResponse
 */
function setUserBanForm( $sUid = 0, $edit = 0, $contextId = '', $streamType = '' ) {
    session_start();
    $objResponse = new xajaxResponse();
    
    if ( hasPermissions('users') ) {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr_meta.php';
        
        $sUniqId = "userban_$sUid";
        $objUser = new users();
        
        $objUser->GetUserByUID( $sUid );

        $sbrInfo = sbr_meta::getUserInfo($sUid);
        $uncompletedDeals = $sbrInfo['all_cnt'] - $sbrInfo['completed_cnt'];
        $uncompletedDealsText = 'У данного пользователя сейчас ' . $uncompletedDeals . ending($uncompletedDeals, ' активная «Безопасная Сделка»', ' активные «Безопасные Сделки»', ' активных «Безопасных Сделок»');
        
        if ( $objUser->uid ) {
            $sBanTo         = 'ban_forever';
            $aCurrBan       = array( 'reason' => '' );
        	$sReasonText    = '';
        	$sDay = $sMonth = $sYear = $sCustomReason = '';
            
            if ( !$objUser->is_banned && !$objUser->ban_where ) { 
                // юзер не забанен
                $objResponse->assign( 'ban_none', 'disabled', true );
                $objResponse->assign( 'ban_site', 'disabled', false );
                $objResponse->assign( 'ban_blog', 'disabled', false );
                $objResponse->assign( 'ban_site', 'checked', true );
                
                if ($uncompletedDeals) {
                    $objResponse->script('$$("#ban_user_sbrs").removeClass("b-fon_hide")');
                    $objResponse->script('$$("#uncompleted_deals_count").set("text", "' . $uncompletedDealsText . '")');
                    
                    if ( $contextId == 'moder' ) {
                        $objResponse->assign( 'notice_sbr_partners', 'checked', true );
                    }
                }
                else {
                    $objResponse->script('$$("#ban_user_sbrs").addClass("b-fon_hide")');
                }
                
            	$nActId         = 3;
            	$sSelectOptions = _getAdminActionReasonOptions( 3, $aCurrBan['reason'] );
            }
            else {
                // юзер где то забанен
                $objResponse->assign( 'ban_none', 'disabled', ($edit) );
                $objResponse->assign( 'ban_site', 'disabled', (!$edit) );
                $objResponse->assign( 'ban_blog', 'disabled', (!$edit) );
                
                if ( $objUser->is_banned ) { 
                    // юзер забанен на всем сайте
                    $objResponse->assign( 'ban_site', 'checked', true );
                    $objResponse->script('$$("#ban_user_sbrs").addClass("b-fon_hide")');
                    $aCurrBan = $edit ? $objUser->GetBan( $objUser->uid, 0 ) : '';
                    $nActId   = $edit ? 3 : 4;
                }
                else { 
                    // юзер забанен в блогах
                    $objResponse->assign( 'ban_blog', 'checked', true );
                    if ($uncompletedDeals) {
                        $objResponse->script('$$("#ban_user_sbrs").removeClass("b-fon_hide")');
                        $objResponse->script('$$("#uncompleted_deals_count").set("text", ' . $uncompletedDealsText . ')');
                        
                        if ( $contextId == 'moder' ) {
                            $objResponse->assign( 'notice_sbr_partners', 'checked', true );
                        }
                    }
                    else {
                        $objResponse->script('$$("#ban_user_sbrs").addClass("b-fon_hide")');
                    }
                    
                    $aCurrBan = $edit ? $objUser->GetBan( $objUser->uid, 1 ) : '';
                    $nActId   = $edit ? 5 : 4;
                }
                
                $sSelectOptions = _getAdminActionReasonOptions( $nActId, $aCurrBan['reason'] );
                
                if ( $edit ) {
                    $sReasonText   = $aCurrBan['comment'];
                    $sCustomReason = $aCurrBan['reason'] ? '' : $aCurrBan['comment'];
                    $sBanTo        = $aCurrBan['to'] ? 'ban_to_date' : 'ban_forever';
                    $sDay          = $aCurrBan['to'] ? date('d', strtotime($aCurrBan['to'])) : '';
                    $sMonth        = $aCurrBan['to'] ? date('m', strtotime($aCurrBan['to'])) : '';
                    $sYear         = $aCurrBan['to'] ? date('Y', strtotime($aCurrBan['to'])) : '';
                }
                else {
                    $nActId = 1;
                    $objResponse->assign( 'ban_none', 'checked', true );
                }
            }
            
            $objResponse->script( "banned.banUid = '$sUniqId';" );
            $objResponse->script( "banned.buffer['$sUniqId'].act_id=$nActId;" );
            $objResponse->script( "banned.buffer['$sUniqId'].customReason[$nActId]='$sCustomReason';" );
        	$objResponse->script( "banned.buffer['$sUniqId'].reasonId[$nActId]='{$aCurrBan['reason']}';" );
            $objResponse->assign( $sBanTo, 'checked', true );
            
            if ( $sBanTo == 'ban_forever' ) {
                $objResponse->assign( 'ban_day',   'disabled', true );
                $objResponse->assign( 'ban_month', 'disabled', true );
                $objResponse->assign( 'ban_year',  'disabled', true );
            }
            
        	$objResponse->script( "$('ban_day').set('value','$sDay');" );
        	$objResponse->script( "$('ban_month').set('value','$sMonth');" );
        	$objResponse->script( "$('ban_year').set('value','$sYear');" );
        	$objResponse->script( "banned.userBanToggle();" );
            
            $sBanDiv = '<div id="bfrm_div_sel_' . $sUniqId . '"><select id="bfrm_sel_' . $sUniqId . '"  class="b-select__select b-select__select_width_full" name="bfrm_sel_' 
        	   . $sUniqId . '" onchange="banned.setReason(\'userban_'.$sUid.'\');">' . $sSelectOptions . '</select></div>';
            
            $objResponse->assign( 'ban_div_select', 'innerHTML', $sBanDiv );
            
            $sBanDiv = '<textarea id="bfrm_' . $sUniqId . '" name="bfrm_' . $sUniqId . '" cols="" rows="" class="b-textarea__textarea b-textarea__textarea_height_50">' 
                . $sReasonText . '</textarea>';
            
            $objResponse->assign( 'ban_div_textarea', 'innerHTML', $sBanDiv );

            $sBanDiv = '<a id="ban_btn" href="javascript:void(0);" class="b-button b-button_flat b-button_flat_green" onclick="banned.commit(banned.banUid,$(\'bfrm_\'+banned.banUid).get(\'value\'))">Сохранить</a>
                <span class="b-buttons__txt b-buttons__txt_padleft_10">или</span>
                <a href="javascript:void(0);" class="b-buttons__link b-buttons__link_dot_c10601" onclick="banned.commit(banned.banUid,(banned.buffer[banned.banUid].action=\'close\'));$(\'ov-notice22\').toggleClass(\'b-shadow_hide\');return false;">закрыть, не сохраняя</a>';
            $objResponse->assign( 'div_ban_btn', 'innerHTML', $sBanDiv );

            if($contextId=='moder') {
                list( $s_stream_id, $s_content_id, $s_rec_id, $s_rec_type ) = explode( '-', $streamType );
                require_once($_SERVER['DOCUMENT_ROOT']."/classes/user_content.php");
                $s_nActId = user_content::getReasonGroup($s_content_id, $s_rec_type);
                $sSelectOptions = _getAdminActionReasonOptions( $s_nActId, $reasonId );

                $sBanDiv = '<div id="bfrm_div_sel_stream_' . $sUniqId . '"><select id="bfrm_sel_stream_' . $sUniqId . '"  class="b-select__select b-select__select_width_full" name="bfrm_sel_stream_' 
                   . $sUniqId . '" onchange="banned.setReasonStream(\'userban_'.$sUid.'\');">' . $sSelectOptions . '</select></div>';
                $objResponse->assign( 'ban_div_select_stream', 'innerHTML', $sBanDiv );
                $sBanDiv = '<textarea id="bfrm_stream_' . $sUniqId . '" name="bfrm_stream_' . $sUniqId . '" cols="" rows="" class="b-textarea__textarea b-textarea__textarea_height_50">' 
                    . $sReasonText . '</textarea>';
                $objResponse->assign( 'ban_div_textarea_stream', 'innerHTML', $sBanDiv );
                $objResponse->script( "$('ban_div_select_stream').getParent().getParent().setStyle('display', '');" );
                $objResponse->script( "$('ban_div_textarea_stream').getParent().getParent().setStyle('display', '');" );
                $objResponse->script( "$('ban_delreason_title').setStyle('display', '');" );
                $objResponse->script( "banned.buffer['$sUniqId'].streamId = '$s_stream_id';" );
                
                $objResponse->script( "banned.context['moder'].contentId = '$s_content_id';" );
                $objResponse->script( "banned.context['moder'].streamId = '$s_stream_id';" );
                $objResponse->script( "banned.context['moder'].recId = '$s_rec_id';" );

                $sBanDiv = '<a id="ban_btn" href="javascript:void(0);" class="b-button b-button_flat b-button_flat_green" onclick="banned.commit(banned.banUid,$(\'bfrm_\'+banned.banUid).get(\'value\'),$(\'bfrm_stream_\'+banned.banUid).get(\'value\'))">Сохранить</a>
                    <span class="b-buttons__txt b-buttons__txt_padleft_10">или</span>
                    <a href="javascript:void(0);" class="b-buttons__link b-buttons__link_dot_c10601" onclick="banned.commit(banned.banUid,(banned.buffer[banned.banUid].action=\'close\'));$(\'ov-notice22\').toggleClass(\'b-shadow_hide\');return false;">закрыть, не сохраняя</a>';
                $objResponse->assign( 'div_ban_btn', 'innerHTML', $sBanDiv );
            }
            
            $objResponse->assign( 'ban_title', 'innerHTML', 'Блокировка <a class="b-layout__link b-layout__link_bold" href="/users/'. $objUser->login .'">'. $objUser->uname .' '. $objUser->usurname .' ['. $objUser->login .']</a>' );
            $objResponse->script( "$('ov-notice22').toggleClass('b-shadow_hide');" );
            $objResponse->script( "$('ov-notice22').setStyle('display', '');" );
            
        }
    }
    
    return $objResponse;
}

/**
 * Устанавливает поля в форме редактирования бана пользователя для массового бана пользователей
 * 
 * @return object xajaxResponse
 */
function setUserMassBanForm() {
    session_start();
    $objResponse = new xajaxResponse();
    
    if ( hasPermissions('users') ) {
        $objResponse->assign( 'ban_none', 'disabled', false );
        $objResponse->assign( 'ban_site', 'disabled', false );
        $objResponse->assign( 'ban_blog', 'disabled', false );
        $objResponse->assign( 'ban_site', 'checked', true );
        $objResponse->script( "banned.banUid = 'userban_0';" );
        $objResponse->script( "banned.buffer['userban_0'].act_id=3;" );
        $objResponse->script( "banned.buffer['userban_0'].customReason[3]='';" );
    	$objResponse->script( "banned.buffer['userban_0'].reasonId[3]='';" );
        $objResponse->assign( 'ban_forever', 'checked', true );
        
        $objResponse->assign( 'ban_day',   'disabled', true );
        $objResponse->assign( 'ban_month', 'disabled', true );
        $objResponse->assign( 'ban_year',  'disabled', true );
        
    	$objResponse->script( "$('ban_day').set('value','');" );
    	$objResponse->script( "$('ban_month').set('value','');" );
    	$objResponse->script( "$('ban_year').set('value','');" );
        
    	$sSelectOptions  = _getAdminActionReasonOptions( 3, $aCurrBan['reason'] );
        
        $sBanDiv = '<div id="bfrm_div_sel_0"><select id="bfrm_sel_userban_0" name="bfrm_sel_userban_0" onchange="banned.setReason(\'userban_0\');">' . $sSelectOptions . '</select></div>';

        $objResponse->assign( 'ban_div_select', 'innerHTML', $sBanDiv );

        $sBanDiv = '<textarea id="bfrm_userban_0" name="bfrm_userban_0" cols="" rows="" class="b-textarea__textarea b-textarea__textarea_height_50">' . $sReasonText . '</textarea>';

        $objResponse->assign( 'ban_div_textarea', 'innerHTML', $sBanDiv );
        
        $objResponse->assign( 'ban_title', 'innerHTML', 'Массовая блокировка/разблокировка' );
        $objResponse->script( "$('ov-notice22').toggleClass('b-shadow_hide');" );
        $objResponse->script( "$('ov-notice22').setStyle('display', '');" );
        
        $sBanDiv = '<a id="ban_btn" href="javascript:void(0);" class="b-button b-button_flat b-button_flat_green" onclick="setMassBanUser()">Сохранить</a>
            <span class="b-buttons__txt b-buttons__txt_padleft_10">или</span>
            <a href="javascript:void(0);" class="b-buttons__link b-buttons__link_dot_c10601" onclick="adminLogOverlayClose();$(\'ov-notice22\').toggleClass(\'b-shadow_hide\');return false;">закрыть, не сохраняя</a>';
        
        $objResponse->assign( 'div_ban_btn', 'innerHTML', $sBanDiv );
    }
    
    return $objResponse;
}

// ---- функции для работы с варнингами пользователей ----


/**
 * Изменение редактирования пользователя
 * 
 * @param  string $sUsers      JSON строка с массивом UID пользователей
 * @param  int    $nActId      ID действия из admin_actions (1, 2)
 * @param  int    $sWarnId     ID предупреждения при редактировании/снятии
 * @param  string $reasonId    ID причины, если она выбрана из списка (таблица admin_reasons, где act_id = 1,2)
 * @param  string $reasonName  НЕ ИСПОЛЬЗУЕТСЯ. Краткое описание причины действия (из селекта)
 * @param  string $reasonText  Текст причины
 * @param  string $draw_func   способ отображения
 * @param  string $sContext    Контекст (для лога админских действий)
 * @return object xajaxResponse
 */
function updateUserWarn( $sUsers = '', $nActId, $sWarnId = 0, $reasonId = null, $reasonName, $reasonText, $draw_func, $sContext = '' ) {
    session_start();
    $objResponse = new xajaxResponse();
    
    if ( hasPermissions('users') ) {
        $reasonId = ($reasonId) ? $reasonId : null;
        $aContext = _jsonArray( $sContext );
        $aContext = $aContext ? $aContext : array('uid' => '', 'code' => 0, 'link' => '', 'name' => '');
        $aUsers   = _jsonArray( $sUsers );
        $bCheck    = true;
        
        if ( $aContext['uid'] == 'moder' ) {
            global $user_content;

            $bCheck = $user_content->checkContent( $aContext['contentId'], $aContext['streamId'], $aContext['recId'] );
        }
        
        if ( $bCheck && $aUsers && is_string($reasonText) && $reasonText != '' ) {
            require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
            $users = new users;
            
            foreach ( $aUsers as $sUid ) {
                $users->GetUserByUID( $sUid );
                
                if ( $users->uid ) {
                    $sReason  = str_replace( '%USERNAME%', $users->uname . ' ' . $users->usurname, $reasonText );
                    $sReason  = change_q( $sReason, FALSE, 0, TRUE );
                    $sUniqId  = "warnUser$sUid";
                    $sObjName = $users->uname. ' ' . $users->usurname . '[' . $users->login . ']';
                    $sObjLink = '/users/' . $users->login;
                    
                    if ( $nActId == 2 && $sWarnId ) {
                        // снимаем предупреждение
                        $users->UnWarn( $sWarnId );
                        
                        // пишем лог админских действий
                        admin_log::addLog( admin_log::OBJ_CODE_USER, 2, $sUid, $sUid, $sObjName, $sObjLink, 0, '', $reasonId, $sReason );
                        
                        $actFlag = -1;
                    }
                    elseif ( $nActId == 1 && $sWarnId ) {
                        // редактируем предупреждение
                        admin_log::updateUserWarn( $sWarnId, $sReason, $reasonId );
                        
                        $actFlag = 0;
                    }
                    elseif ( $nActId == 1 && !$sWarnId && $users->warn < 3 && !$users->is_banned ) {
                        $aUserContent = array();
                        $sUserContent = '';
                        
                        if ( $aContext['code'] == 2 && preg_match('#^blog_msg_([\d]+)#', $aContext['uid'], $aMatch) ) {
                        	/*require_once( $_SERVER['DOCUMENT_ROOT'].'/classes/blogs.php' );
                        	$aUserContent = blogs::GetMsgInfo( $aMatch[1], $err, $perm );
                        	
                        	if ( $aUserContent ) {
                        		$sUserContent = $aContext['link'] . ($aUserContent['msgtext'] ? "\n\n" . $aUserContent['msgtext'] : '');
                        	}*/
                        }
                        
                        // делаем предупреждение
                        $sNewWarnId = $users->Warn( $users->login, $sReason, $reasonId, $aContext['link'], $sUserContent );
                        
                        // пишем лог админских действий
                        admin_log::addLog( admin_log::OBJ_CODE_USER, 1, $sUid, $sUid, $sObjName, $sObjLink, $aContext['code'], $aContext['link'], $reasonId, $sReason, $sNewWarnId, $aContext['name'] );
                        
                        $actFlag = 1;
                    }
                    
                    WarnsHTML( $draw_func, $sUid, $users, $objResponse, $sUniqId, $aContext['uid'], $actFlag );
                }
            }
        }
        
        $objResponse->script( "$$(\"div[id^='ov-notice']\").setStyle('display', 'none');" );
        $objResponse->script( "if(banned.reload==1){window.location.reload(true);}" );
        $objResponse->script( "if(banned.zero){banned.zeroShow();}" );
        
        if ( $aContext['uid'] == 'moder' ) {
            // если варнинг делается из потока модерирования
            $objResponse->script( 'user_content.resolveContent(user_content.resolveSid, 2, user_content.resolveUid)' );
            $objResponse->script( "parent.$$(\"div[id^='ov-notice']\").setStyle('display', 'none');" );
        }
    }
    
    return $objResponse;
}

/**
 * Устанавливает поля в форме редактирования предупреждения пользователя
 * 
 * @param  int $sUid    UID пользователя
 * @param  int $sWarnId ID предупреждения при редактировании/снятии
 * @param  int $edit флаг редактирования причины предупреждения
 * @return object xajaxResponse
 */
function setUserWarnForm( $sUid = 0, $sWarnId = 0, $edit = 0, $contextId, $streamType ) {
    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
    session_start();
    
    $objResponse = new xajaxResponse();
    $users       = new users;
    
    if ( hasPermissions('users') && !$users->GetUserByUID($sUid) ) {
        $customReason = $reasonId = '';
        $aUserWarn    = admin_log::getUserWarn( $sWarnId );
        $sUniqId      = "warnUser$sUid";
        $sObjName     = $users->uname. ' ' . $users->usurname . ' [' . $users->login . ']';
        $sReason      = '';
        $reasonId     = 0;
        
        if ( !$aUserWarn ) {
            // инициализируем предупреждение по умолчанию
            $nActId = 1;
        	$objResponse->assign( 'warn_label', 'innerHTML', 'Сделать' );
        }
        else {
            if ( $edit ) {
                // инициализируем данными предупреждения
                $nActId   = 1;
                $sReason  = $aUserWarn['reason'];
                $reasonId = $aUserWarn['reason_id'];
                
                $objResponse->assign( 'warn_label', 'innerHTML', 'Редактировать' );
            }
            else {
                // инициализируем снятие предупреждения по умолчанию
                $nActId = 2;
                $objResponse->assign( 'warn_label', 'innerHTML', 'Снять' );
            }
        }
        
        $customReason   = $reasonId ? ''   : $sReason;
        $readonly       = $reasonId ? true : false;
        $sSelectOptions = _getAdminActionReasonOptions( $nActId, $reasonId );
        
        $sBanDiv = '<div id="bfrm_div_sel_' . $sUniqId . '"><select id="bfrm_sel_' . $sUniqId . '" name="bfrm_sel_' . $sUniqId
            . '" onchange="banned.setReason(\''.$sUniqId.'\');">' . $sSelectOptions . '</select></div><textarea id="bfrm_' . $sUniqId
            . '" name="bfrm_' . $sUniqId . '" cols="" rows="">' . $sReason . '</textarea>';
        
        $objResponse->assign( 'warn_div', 'innerHTML', $sBanDiv );
        $objResponse->assign( 'warn_name', 'innerHTML', $sObjName );
        $objResponse->assign( 'warn_name', 'href', '/users/'.$users->login );

        if($contextId=='moder') {
            list( $s_stream_id, $s_content_id, $s_rec_id, $s_rec_type ) = explode( '-', $streamType );
            require_once($_SERVER['DOCUMENT_ROOT']."/classes/user_content.php");
            $s_nActId = user_content::getReasonGroup($s_content_id, $s_rec_type);
            $sSelectOptions = _getAdminActionReasonOptions( $s_nActId, $reasonId );
            $sBanDivStream = '<div id="bfrm_div_sel_stream_' . $sUniqId . '"><select id="bfrm_sel_stream_' . $sUniqId . '" name="bfrm_sel_stream_' . $sUniqId
                . '" onchange="banned.setReasonStream(\''.$sUniqId.'\');">' . $sSelectOptions . '</select></div><textarea id="bfrm_stream_' . $sUniqId
                . '" name="bfrm_stream_' . $sUniqId . '" cols="" rows="">' . $sReason . '</textarea>';
            $objResponse->assign( 'warn_div_stream', 'innerHTML', $sBanDivStream );
            $btnOnclick = "banned.commit(banned.banUid,$('bfrm_'+banned.banUid).get('value'), $('bfrm_stream_'+banned.banUid).get('value') );";
            $objResponse->script( "$('warn_btn').set('onclick', \"{$btnOnclick}\")" );
            $objResponse->script( "banned.buffer['$sUniqId'].streamId = '$s_stream_id';" );
            
            $objResponse->script( "banned.context['moder'].contentId = '$s_content_id';" );
            $objResponse->script( "banned.context['moder'].streamId = '$s_stream_id';" );
            $objResponse->script( "banned.context['moder'].recId = '$s_rec_id';" );
        }

        $objResponse->script( "banned.banUid = '$sUniqId';" );
        $objResponse->script( "banned.buffer['$sUniqId'].act_id = $nActId;");
        $objResponse->script( "banned.buffer['$sUniqId'].customReason[$nActId] = '$customReason';" );
        $objResponse->script( "banned.buffer['$sUniqId'].reasonId[$nActId] = '$reasonId';" );
        $objResponse->script( "$('warn_btn').set('disabled',false);$('warn_close').set('disabled',false);$('warn_btn').set('value','Сохранить');" );
        $objResponse->script( "$('ov-notice').setStyle('display', '');" );
    }
    
    return $objResponse;
}

/**
 * Устанавливает поля в форме редактирования предупреждения пользователя
 * 
 * @param  int $sUid    UID пользователя
 * @param  int $sWarnId ID предупреждения при редактировании/снятии
 * @param  int $edit флаг редактирования причины предупреждения
 * @return object xajaxResponse
 */
function setUserWarnFormNew( $sUid = 0, $sWarnId = 0, $edit = 0 ) {
    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
    session_start();
    
    $objResponse = new xajaxResponse();
    $users       = new users;
    
    if ( hasPermissions('users') && !$users->GetUserByUID($sUid) ) {
        $customReason = $reasonId = '';
        $aUserWarn    = admin_log::getUserWarn( $sWarnId );
        $sUniqId      = "warnUser$sUid";
        $sObjName     = $users->uname. ' ' . $users->usurname . ' [' . $users->login . ']';
        $sReason      = '';
        $reasonId     = 0;
        
        if ( !$aUserWarn ) {
            // инициализируем предупреждение по умолчанию
            $nActId = 1;
        	$objResponse->assign( 'warn_label', 'innerHTML', 'Сделать' );
        }
        else {
            if ( $edit ) {
                // инициализируем данными предупреждения
                $nActId   = 1;
                $sReason  = $aUserWarn['reason'];
                $reasonId = $aUserWarn['reason_id'];
                
                $objResponse->assign( 'warn_label', 'innerHTML', 'Редактировать' );
            }
            else {
                // инициализируем снятие предупреждения по умолчанию
                $nActId = 2;
                $objResponse->assign( 'warn_label', 'innerHTML', 'Снять' );
            }
        }
        
        $customReason   = $reasonId ? ''   : $sReason;
        $readonly       = $reasonId ? true : false;
        $sSelectOptions = _getAdminActionReasonOptions( $nActId, $reasonId );
        
        $sBanDiv = '<div id="bfrm_div_sel_' . $sUniqId . '"><select id="bfrm_sel_' . $sUniqId . '" name="bfrm_sel_' . $sUniqId 
            . '" onchange="banned.setReason(\''.$sUniqId.'\');">' . $sSelectOptions . '</select></div>';
        $sBanTextarea = '<textarea id="bfrm_' . $sUniqId . '" name="bfrm_' . $sUniqId . '" cols="" rows="" class="b-textarea__textarea b-textarea__textarea_height_50">' . $sReason . '</textarea>';
        
        $objResponse->assign( 'warn_div', 'innerHTML', $sBanDiv );
        $objResponse->assign( 'warn_texarea', 'innerHTML', $sBanTextarea );
        $objResponse->assign( 'warn_name', 'innerHTML', $sObjName );
        $objResponse->assign( 'warn_name', 'href', '/users/'.$users->login );
        $objResponse->script( "banned.banUid = '$sUniqId';" );
        $objResponse->script( "banned.buffer['$sUniqId'].act_id = $nActId;");
        $objResponse->script( "banned.buffer['$sUniqId'].customReason[$nActId] = '$customReason';" );
        $objResponse->script( "banned.buffer['$sUniqId'].reasonId[$nActId] = '$reasonId';" );
        $objResponse->script( "$('warn_btn').set('disabled',false);$('warn_close').set('disabled',false);$('warn_btn').set('value','Сохранить');" );
        $objResponse->script( "$('ov-notice').setStyle('display', '');" );
    }
    
    return $objResponse;
}

/**
 * Отобразить список варнингов
 *
 * @param integer $uid         uid пользователя
 * @param string  $draw_func   способ отображения
 */
function GetWarns($uid, $draw_func) {
    $objResponse = new xajaxResponse();
    
    if ( hasPermissions('users') ) {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/users.php';
        
        $uid = intval($uid);
        session_start();
    	if (hasPermissions('users')) {
            $users = new users;
            $users->GetUserByUID( $uid );
            WarnsHTML($draw_func, $uid, $users, $objResponse);
        }
    }
    return $objResponse;
}

/**
 * Изменение DOM HTML и другие дополнительные действия исходя из конкретного случая
 *
 * @param string  $draw_func   способ отображения
 * @param 0bject  $users       объект класса users
 * @param array   $warns       массив с предупреждениями (строки из БД)
 *                             для отображения списка предупреждений
 * @param object  $objResponse xajaxResponse
 * @param string  $uniqId       суфикс DOM элемента с окном предупреждения
 * @param array   $contextId Контекст (для лога админских действий)
 * @param int     $actFlag 0 - нет действия, 1 - сделали предупреждение, -1 - сняли предупреждение
 */
function WarnsHTML( $draw_func, $uid, &$users, &$objResponse, $uniqId='', $contextId = '', $actFlag = 0 ) {
    $warns     = $users->GetWarns( $uid ); // список активных предупреждений
    $is_banned = $users->is_banned; // флаг забанен на всем сайте
    
    switch ($draw_func) {
        case 'admin_messages_spam': // Изменение HTML на странице далоб на спам в новой модераторской
            WarnsHTML_messages_spam( $uid, $objResponse );
            break;
        case 'admuserpage': // Изменение HTML на странице истрии пользователя в новой модераторской
            WarnsHTML_admuserpage( $uid, $warns, $objResponse );
            break;
        case 'admalluserspage': // Изменение HTML на странице Нарушители в новой модераторской
            WarnsHTML_admalluserspage( $objResponse, $warns, $actFlag );
            break;
        case 'user_search': // Изменение HTML на странице поиска пользователей в новой модераторской
            WarnsHTML_user_search( $uid, $warns, $objResponse, $actFlag );
            break;
        case 'admpage': // Изменение HTML на странице поиска пользователей в старой админке
            WarnsHTML_admpage( $uid, $warns, $objResponse );
            break;
        case 'userpage': // Изменение HTML на станице профиля пользователя
            WarnsHTML_userpage( $uid, $warns, $objResponse, $is_banned );
            break;
        case 'siteadmin': // Изменение HTML в админке ban-razban
            WarnsHTML_siteadmin( $uid, $warns, $objResponse, $uniqId );
            break;
        case 'comments':
            WarnsHTML_comment( $uid, $warns, $objResponse, $uniqId, $contextId, $draw_func );
            break;
        case 'comments_articles': // Изменение HTML на странице "Модерирование \ Комментарии" (siteadmin/comments)
            WarnsHTML_comments( $uid, $warns, $objResponse, $uniqId, $contextId, $draw_func );
            break;
        case 'frl_offers':
            WarnsHTML_frl_offers( $uid, $warns, $objResponse, $uniqId, $contextId, $draw_func );
            break;
        case 'streams': // 
            $objResponse->script( 'user_content.setUserWarns(' . $uid . ', ' . count($warns) . ');' );
            break;
        case 'std': // Изменение HTML стандартным способом
        case 'blogs': 
        case 'projects': 
            WarnsHTML_std( $uid, $warns, $objResponse, $uniqId, $contextId, $draw_func );
            break;
        case '': 
        default: // все остальные случаи: скрываем попап, обновляем количество предупреждений
            $warncount = $warns ? count($warns) : 0;
            $objResponse->script( "$$('.warncount-$uid').set('html','$warncount');" );
            break;
    }
}

/**
 * Изменение HTML на странице далоб на спам в новой модераторской
 * 
 * @param int $uid UID пользователя
 * @param obj $objResponse экземпляр класса xajaxResponse
 */
function WarnsHTML_messages_spam( $uid, &$objResponse ) {
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/messages_spam.php");
    
    $oSpam = new messages_spam();
    $oSpam->deleteSpamBySpamer( $uid, 2 );
    
    $objResponse->script( 'window.location.reload(true)' );
}

/**
 * Изменение HTML на странице истрии пользователя в новой модераторской
 * 
 * @param int $uid UID пользователя
 * @param array $warns массив предупреждений
 * @param obj $objResponse экземпляр класса xajaxResponse
 */
function WarnsHTML_admuserpage( $uid, &$warns, &$objResponse ) {
    $nWarnCnt = $warns ? count($warns) : 0;
    
    if ( $nWarnCnt >= 3 ) {
        $objResponse->assign( "div_warn", 'innerHTML', '<button onclick="adminLogWarnMax()" name="btn_warn" type="button" value="btn_warn">Сделать предупреждение</button>' );
    }
    else {
        $objResponse->assign( "div_warn", 'innerHTML', '<button onclick="banned.warnUser('.$uid.', 0, \'admuserpage\', \'admin_log_page\', 0); return false;" name="btn_warn" type="button" value="btn_warn">Сделать предупреждение</button>' );
    }
    
    $objResponse->script( 'window.location.reload(true)' );
    $objResponse->assign( "warnreason-$uid", 'style.display', 'none' );
}

/**
 * Изменение HTML на странице Нарушители в новой модераторской
 * 
 * @param obj $objResponse экземпляр класса xajaxResponse
 * @param array $warns массив предупреждений
 * @param int $actFlag 0 - нет действия, 1 - сделали предупреждение, -1 - сняли предупреждение
 */
function WarnsHTML_admalluserspage( &$objResponse, &$warns, $actFlag = 0 ) {
    if ( $actFlag == 0 && $warns ) {
        foreach ( $warns as $aOne ) {
            $reason = hyphen_words(reformat($aOne['reason'], 45), true);
            $objResponse->assign( 'reason1_'.$aOne['id'], 'innerHTML', $reason );
        }
    }
    else {
        // перебрасываем к новому действию
        $sHref = e_url('page',null,$_SESSION['admin_log_user']) ;
        $objResponse->script( "window.location='$sHref'" );
    }
}

/**
 * Изменение HTML на странице поиска пользователей в модераторской
 * 
 * @param int $uid UID пользователя
 * @param array $warns массив предупреждений
 * @param obj $objResponse экземпляр класса xajaxResponse
 * @param int     $actFlag 0 - нет действия, 1 - сделали предупреждение, -1 - сняли предупреждение
 */
function WarnsHTML_user_search( $uid, &$warns, &$objResponse, $actFlag ) {
    $nWarnCnt = $warns ? count($warns) : 0;
    $sOnclick = ' onclick="xajax_getUserWarns('.$uid.',\'admin_user_search\',\'user_search\');" href="javascript:void(0);"';
    $sWarnLnk = $nWarnCnt > 0 ? '<span class="color-e37101"><a '.$sOnclick.'>Предупреждений: <div id="warn_'.$uid.'" class="warncount-'.$uid.'">'.$nWarnCnt.'</div></a></span>' : '<span><a '.$sOnclick.' class="lnk-dot-666">Нет предупреждений</a></span>';
    $sHtml    = '<span><a href="/siteadmin/bill/?login='.$warns[0]['login'].'" class="color-45a300">Счет пользователя</a></span>'.$sWarnLnk;
    
    if ( $nWarnCnt >= 3 ) {
        $objResponse->assign( "warn-$uid", 'innerHTML', '<a onclick="adminLogWarnMax()" href="javascript:void(0);">Сделать предупреждение</a>' );
    }
    else {
        $objResponse->assign( "warn-$uid", 'innerHTML', '<a onclick="banned.warnUser('.$uid.', 0, \'user_search\', \'admin_user_search\', 0); return false;" href="javascript:void(0);">Сделать предупреждение</a>' );
    }
    
    $objResponse->assign( "search_right_$uid", 'innerHTML', $sHtml );
    $objResponse->script( 'adminLogCheckUsers(false)' );
    $objResponse->script( '$("chk_all").checked=false;' );
    $objResponse->script( 'adminLogOverlayClose();' );
    $objResponse->assign( "warnreason-$uid", 'style.display', 'none' );
    
    if ( $actFlag == -1 && $nWarnCnt == 0 ) {
    	$objResponse->script( 'window.location.reload(true)' );
    }
}

/**
 * Изменение HTML на станице профиля пользователя
 */
function WarnsHTML_userpage( $uid, &$warns, &$objResponse, $is_banned ) {
    if ($warns) {
        $nWarnCount = count( $warns );
        for ( $i=0; $i < $nWarnCount; $i++ ) {
            $html .= "
                <tr>
                    ".(hasPermissions('users')? "
                    <td>
                        [<a  style='font-weight: bold' href='javascript: void(0);' onclick='banned.warnUser({$uid}, {$warns[$i]['id']}, \"userpage\", {$uid}, 0); return false;'>X</a>]&nbsp;
                    </td>
                    ": ""). "
                    <td><span class=\"admn-line\">".($warns[$i]['reason']? reformat(str_replace("\n", "&nbsp;", ($warns[$i]['reason'])),50): "<span style='font-style: italic'>нет примечания</span>"). 
                    ( $warns[$i]['users_content'] ? "&nbsp;" . reformat(str_replace("\n", "&nbsp;", $warns[$i]['users_content']),50) : '' ) 
                    . "</span></td><td>" .
                    ($warns[$i]['admin']? (" <span style='font-style: italic'>(выдан: <a href='/users/{$warns[$i]['admin_login']}'>{$warns[$i]['admin_login']}</a>".($warns[$i]['warn_time']? (', '.dateFormat("d.m.Y H:i", $warns[$i]['warn_time'])): '').")</span>"): '').
                    "</td>".
                "</tr>
            ";
        }
        $objResponse->assign("warnlist-$uid", 'innerHTML', '
            <table><tbody>
            <tr>
                <th colspan=3><strong>Список предупреждений</strong></th>
            </tr>
            '.$html.'
            </tbody>
            </table>
        ');
        $objResponse->assign("warnlist-$uid", 'style.display', 'block');
        
        if ( $nWarnCount >= 3 || $is_banned ) {
            $objResponse->script("$$('.warnbutton-$uid').setStyle('display','none');");
        } else {
            $objResponse->script("$$('.warnbutton-$uid').setStyle('display','');");
        }
        $objResponse->assign("warncount-$uid", 'innerHTML', $nWarnCount);
        $objResponse->script( "$$('.warncount-$uid').set('html','$nWarnCount');" );
    } else {
        $objResponse->assign("warnlist-$uid", 'innerHTML', '');
        $objResponse->assign("warnlist-$uid", 'style.dislay', 'none');
        $objResponse->assign("warncount-$uid", 'innerHTML', '0');
        $objResponse->script( "$$('.warncount-$uid').set('html','0');" );
        $objResponse->script("$$('.warnbutton-$uid').setStyle('display','".($is_banned ? 'none' : '')."');");
    }
    
    $objResponse->assign("warnreason-$uid", 'style.display', 'none');
}

/**
 * Изменение HTML в админке ban-razban
 */
function WarnsHTML_siteadmin($uid, &$warns, &$objResponse) {
    if (!$warns) $warns = array();
    for ($i=0; $i<count($warns); $i++) {
        $html .= "
            <tr>
                ".(hasPermissions('users')? "
                <td class='u-unwarn-button'>
                    <a style='font-weight: bold' href='javascript: void(0);' title='Снять предупреждение' onclick='banned.warnUser({$uid},{$warns[$i]['id']}, \"siteadmin\", \"admin\", 0); return false;'>X</a>

                </td>
                ": ""). "
                <td class='u-warn-item'>".
                    ($warns[$i]['admin']? ("<span class='u-wbox-login'><a href='/users/{$warns[$i]['admin_login']}'>{$warns[$i]['admin_name']} {$warns[$i]['admin_uname']}</a> [<a href='/users/{$warns[$i]['admin_login']}'>{$warns[$i]['admin_login']}</a>]</span> ".
                    ($warns[$i]['warn_time']? ('<span class="u-wbox-time">['.dateFormat("d.m.Y | H:i", $warns[$i]['warn_time']).']</span>'): '').'<br />'): '').
                    "<span class='u-wbox-reason'>".($warns[$i]['reason']? reformat(str_replace("\n", "<br>", $warns[$i]['reason']),50): "<i>нет примечания</i>")."</span>".
                "<td>
            </tr>
        ";
    }
    for ($j=$i; $j<3; $j++) {
        $html .= "
            <tr>
                ".(hasPermissions('users')? "
                <td>&nbsp;</td>
                ": ""). "
                <td class='u-warn-new'><div><a href='javascript:;' onclick='banned.warnUser($uid, 0, \"siteadmin\", \"admin\", 0); return false;'>Сделать предупреждение</a></div></td>
            </tr>
        ";
    }
    $objResponse->assign("warnlist-$uid", 'innerHTML', '
        <table cellpadding="2" cellspacing="0" border="0" style="width: 100%">'.$html.'</table>
        <div id="warnreason-'.$uid.'" style="display:none">&nbsp;</div>
    ');
    $objResponse->assign("warnlist-$uid", 'style.display', 'block');
    $objResponse->assign("warncount1-$uid", 'innerHTML', count($warns));
    $objResponse->assign("warncount2-$uid", 'innerHTML', count($warns));
}

/**
 * Изменение HTML на странице поиска пользователей в старой админке
 */
function WarnsHTML_admpage($uid, &$warns, &$objResponse) {
    if ($warns) {
        for ($i=0; $i<count($warns); $i++) {
            $html .= "
                <tr>
                    ".(hasPermissions('users')? "
                    <td style='vertical-align: top; font-weight: bold; width: 30px;'>
                        [<a class='blue' style='font-weight: bold' href='javascript: void(0);' onclick='banned.warnUser({$uid}, {$warns[$i]['id']}, \"admpage\", \"all\", 0); return false;'>X</a>]
                    </td>
                    ": ""). "
                    <td>".($warns[$i]['reason']? reformat(str_replace("\n", "<br>", ($warns[$i]['reason'])),50): "<span style='font-style: italic'>нет примечания</span>").
                    ($warns[$i]['admin']? (" <span style='font-style: italic'>(выдан: <a class='blue' href='/users/{$warns[$i]['admin_login']}'>{$warns[$i]['admin_login']}</a>".($warns[$i]['warn_time']? (', '.dateFormat("d.m.Y H:i", $warns[$i]['warn_time'])): '').")</span>"): '').
                "</tr>
            ";
        }
        $objResponse->assign("warnlist-$uid", 'innerHTML', '<table cellpadding="2" cellspacing="0" border="0" style="width: 100%; border-top: 1px solid #DCDBD9;">'.$html.'</table>');
        $objResponse->assign("warnlist-$uid", 'style.display', 'block');
        $objResponse->assign("warnlink-$uid", 'innerHTML', "
            (<a class='blue' href='javascript: void(0);' onclick='if (document.getElementById(\"warnlist-$uid\").style.display==\"none\") xajax_GetWarns($uid, \"admpage\"); else document.getElementById(\"warnlist-$uid\").style.display = \"none\"; return false;'>".count($warns)."</a>)
        ");
    } else {
        $objResponse->assign("warnlist-$uid", 'innerHTML', '');
        $objResponse->assign("warnlist-$uid", 'style.dislay', 'none');
        $objResponse->assign("warnlink-$uid", 'innerHTML', '(0)');
    }
    $objResponse->assign("warnreason-$uid", 'style.display', 'none');
}

/**
 * Изменение HTML после предупреждений. общий случай.
 * 
 * @param int $uid UID пользователя
 * @param array $warns массив предупреждений
 * @param obj $objResponse xajaxResponse
 * @param string $uniqId ID DOM элемента, в котором появляется окно c предупреждением (warnlist)
 * @param array $contextId Контекст (для лога админских действий)
 * @param string $drawFunc что рисуем (блоги, проекты, дефиле)
 */
function WarnsHTML_std($uid, &$warns, &$objResponse, $uniqId, $contextId, $drawFunc) {
    $warncount = $warns ? count($warns) : 0;
    $sColor = 'red';
    
    if ( $warncount < 3 ) {
        $objResponse->script( "$$('.warncount-$uid').set('html','$warncount');" );
    }
    else {
        $objResponse->script( "$$('.warnlink-$uid').set('html','<a href=\"javascript:void(0);\" onclick=\"banned.userBan($uid, \'$contextId\',0);\" style=\"color: $sColor;\">Забанить!</a>');" );
    }
    
    $objResponse->script("if($('warnlist-$uid')!='undefined'){xajax_GetWarns($uid,'userpage');}");
    $objResponse->assign( "warnreason-$uniqId", 'style.display', 'none' );
}

/**
 * Изменение HTML после предупреждений. общий случай.
 * 
 * @param int $uid UID пользователя
 * @param array $warns массив предупреждений
 * @param obj $objResponse xajaxResponse
 * @param string $uniqId ID DOM элемента, в котором появляется окно c предупреждением (warnlist)
 * @param array $contextId Контекст (для лога админских действий)
 * @param string $drawFunc что рисуем (блоги, проекты, дефиле)
 */
function WarnsHTML_frl_offers($uid, &$warns, &$objResponse, $uniqId, $contextId, $drawFunc) {
    $warncount = $warns ? count($warns) : 0;
    
    if ( $warncount < 3 ) {
        $objResponse->script( "$$('.warncount-$uid').set('html','$warncount');" );
    }
    else {
        $objResponse->script( "$$('.warnlink-$uid').set('html','<a class=\"b-buttons__link b-buttons__link_dot_c10601 b-buttons__link_margleft_10\" href=\"javascript:void(0);\" onclick=\"banned.userBan($uid, \'$contextId\',0);\">Забанить!</a>');" );
    }
    
    $objResponse->script("if($('warnlist-$uid')!='undefined'){xajax_GetWarns($uid,'userpage');}");
    $objResponse->assign( "warnreason-$uniqId", 'style.display', 'none' );
}


function WarnsHTML_comment($uid, &$warns, &$objResponse, $uniqId, $contextId, $drawFunc) {
    $warncount = $warns ? count($warns) : 0;
    
    if ( $warncount < 3 ) {
        $objResponse->script( "$$('.warncount-$uid').set('html','$warncount');" );
    }
    else {
        $objResponse->script( "$$('.warnlink-$uid').set('html','<a class=\"b-buttons__link b-buttons__link_dot_c10601 b-buttons__link_margleft_10\" href=\"javascript:void(0);\" onclick=\"banned.userBan($uid, \'$contextId\',0);\">Забанить!</a>');" );
    }
    
    $objResponse->script("if($('warnlist-$uid')!='undefined'){xajax_GetWarns($uid,'userpage');}");
    $objResponse->assign( "warnreason-$uniqId", 'style.display', 'none' );
}

/**
 * Изменение HTML в админке комментариев /siteadmin/comments/
 *
 */
function WarnsHTML_comments( $uid, &$warns, &$objResponse, $uniqId, $contextId, $draw_func ) {
    $warncount = $warns? count($warns): 0;
    if ($warncount < 3) {
        $html = "<a href='' onclick='banned.warnUser({$uid}, 0, \"$draw_func\", \"$contextId\", 0); return false;' class='lnk-dot-red'>Сделать предупреждение ({$warncount})</a>";
		$objResponse->assign("warn-{$uniqId}", "innerHTML", $html);
    } else {
        $html = "<a href='' onclick='return warnMax()' class='lnk-dot-red'>Сделать предупреждение ({$warncount})</a>";
		$objResponse->assign("warn-{$uniqId}", "innerHTML", $html);
    }
    $objResponse->assign("warnreason-$uniqId", 'style.display', 'none');
}

// ---- функции для блокировки блогов ----


/**
 * Отображение красного прямоугольника с текстом предупреждения.
 * эта функция еще есть в blogs/view_cnt.php, blogs/viewgr_cnt.php, user/journal_inner.php
 *
 */
function BlockedThreadHTML($reason, $date, $moder_login='', $moder_name='') {
	return "
<div class='br-moderation-options'>
 <a href='/about/feedback/' class='lnk-feedback' style='color: #fff;'>Служба поддержки</a>
<div class='br-mo-status'><strong>Топик заблокирован.</strong> Причина: ".str_replace("\n", "<br>", $reason)."</div>
<p class='br-mo-info'>".
 ($moder_login? "Заблокировал: <a href='/users/$moder_login' style='color: #FF6B3D'>$moder_name [$moder_login]</a><br />": '').
 "Дата блокировки: ".dateFormat('d.m.Y H:i', $date)."</p>
</div>     ";
}

/**
 * Отображение красного прямоугольника с текстом предупреждения.
 * эта функция еще есть в projects/content_frl.php
 *
 */
function BlockedProjectHTML($reason, $date, $moder_login='', $moder_name='') {
	return "
        <div class='b-fon b-fon_clear_both b-fon_bg_ff6d2d b-fon_padtop_10'>
					<b class='b-fon__b1'></b>
					<b class='b-fon__b2'></b>
					<div class='b-fon__body b-fon__body_pad_5_10 b-fon__body_fontsize_13'>
						<span class='b-fon__attent'></span>
						<div class='b-fon__txt b-fon__txt_margleft_20'>
							<span class='b-fon__txt_bold'>Проект заблокирован.</span> ".str_replace("\n", "<br>", $reason)."
								<a class='b-fon__link' href='https://feedback.fl.ru/'>Служба поддержки</a>
								<div class='b-fon__txt'>
									Заблокировал:
									<a class='b-fon__link' href='/users/$moder_login'>$moder_name [$moder_login]</a>
									<br>
									Дата блокировки: ".dateFormat('d.m.Y H:i', $date)."
								</div>
						</div>
					</div>
					<b class='b-fon__b2'></b>
					<b class='b-fon__b1'></b>
				</div>
    ";
}

/**
 * Блокирование/разблокирование треда в блоге
 *
 * @param integer $thread_id     id треда
 * @param string  $reason        причина
 * @param int     $reason_id     ID причины, если она выбрана из списка (таблица admin_reasons, где act_id = 7)
 * @param string  $reason_name   Краткое описание причины действия (из селекта) для лога админских действий
 */
function BlockedThread( $thread_id, $reason, $reason_id = null, $reason_name = '' ) {
	global $DB;
	session_start();
	$objResponse = new xajaxResponse();
	
	if ( hasPermissions('blogs') ) {
	    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/blogs.php';
	    
    	$thread_id = intval($thread_id);
        
    	if (is_string($reason) && $reason != '') {
    		if (hasPermissions('blogs')) {
    			$count = $DB->val("SELECT COUNT(*) FROM blogs_msgs WHERE thread_id = ? AND reply_to IS NULL", $thread_id);
    			if ($count) {
    				$blogs = new blogs;
                    $thread = $blogs->GetThreadMsgInfo($thread_id, $error, $perm);
                    
                    // лог админских действий
    				$sObjName  = $thread['title'] ? $thread['title'] : '<без темы>';
    				$sObjLink  = '/blogs/view.php?tr=' . $thread_id;
    				$reason_id = ($reason_id) ? $reason_id : null;
    				$reason    = str_replace('%USERNAME%', $thread['uname'] . ' ' .$thread['usurname'], $reason);
				    $reason    = change_q($reason, FALSE, 0, TRUE);
                    
    				if ($thread['blocked_time']) {
    					$blogs->UnBlocked($thread_id);
    					
    					// пишем лог админских действий
    					admin_log::addLog( admin_log::OBJ_CODE_BLOG, 8, $thread['fromuser_id'], $thread_id, $sObjName, $sObjLink, 0, '', $reason_id, $reason );
    					
    					$objResponse->assign("thread-reason-$thread_id", 'innerHTML', '&nbsp;');
                        $objResponse->assign("thread-reason-$thread_id", 'style.display', 'none');
    					$objResponse->assign("thread-button-$thread_id", 'innerHTML', "<a style='color: Red; font-size:9px;' href='javascript: void(0);' onclick='banned.blockedThread($thread_id); return false;'>Блокировать</a>");
    				} else {
    					$sBlockId  = $blogs->Blocked($thread_id, $reason, $reason_id, $_SESSION['uid'], false);
    					$thread    = $blogs->GetThreadMsgInfo($thread_id, $error, $perm);
    					
    					// пишем лог админских действий
    					admin_log::addLog( admin_log::OBJ_CODE_BLOG, 7, $thread['fromuser_id'], $thread_id, $sObjName, $sObjLink, 0, '', $reason_id, $reason, $sBlockId );
    					
    					$reason = reformat( $thread['reason'], 24, 0, 0, 1, 24 );
    					$html   = BlockedThreadHTML( $reason, $thread['blocked_time'], $_SESSION['login'], "{$_SESSION['name']} {$_SESSION['surname']}" );
                        
                        $objResponse->assign("thread-reason-$thread_id", 'innerHTML', $html);
                        $objResponse->assign("thread-reason-$thread_id", 'style.display', 'block');
    					$objResponse->assign("thread-button-$thread_id", 'innerHTML', "<a style='color: Red; font-size:9px;' href='javascript: void(0);' onclick='banned.unblockedThread($thread_id); return false;'>Разблокировать</a>");
                    }
    			} else {
    				$objResponse->alert('Несуществующий топик');
    			}
    		}
    	}
	}
	
	return $objResponse;	
}

// ---- функции для блокировки проектов ----

/**
 * Блокирование/разблокирование проекта
 * 
 * @param integer $project_id    id проекта
 * @param string  $reason        причина
 * @param int     $reason_id     ID причины, если она выбрана из списка (таблица admin_reasons, где act_id = 9)
 * @param string  $reason_name   Краткое описание причины действия (из селекта) для лога админских действий
 */
function BlockedProject( $project_id, $reason, $reason_id = null, $reason_name = '' ) {
	global $DB;
	$objResponse = new xajaxResponse();
	$project_id = intval($project_id);

	session_start();
    
	if ( hasPermissions('projects') ) {
	    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/projects.php';
	    
    	if (is_string($reason) && $reason != '') {
    		if (hasPermissions('projects')) {
    			$count = $DB->val("SELECT COUNT(*) FROM projects WHERE id = ?", $project_id);
    			if ($count) {
    				$projects  = new projects;
                    $project   = $projects->GetPrjCust($project_id);
                    $sObjLink  = getFriendlyURL('project', $project_id); // лог админских действий
                    $reason_id = ($reason_id) ? $reason_id : null;
				    $reason    = str_replace('%USERNAME%', $project['uname'] . ' ' .$project['usurname'], $reason);
				    $reason    = change_q($reason, FALSE, 0, TRUE);
                    $mem = new memBuff();
                    $mem->delete("prjMsgsCnt{$project['user_id']}");
                    
    				if ($project['blocked_time']) {
    					$projects->UnBlocked($project_id);
    					
    					// пишем лог админских действий
    					admin_log::addLog( admin_log::OBJ_CODE_PROJ, 10, $project['user_id'], $project_id, $project['name'], $sObjLink, 0, '', $reason_id, $reason );
    					
    					$objResponse->assign("project-reason-$project_id", 'innerHTML', '&nbsp;');
                        $objResponse->assign("project-reason-$project_id", 'style.display', 'none');
    					$objResponse->assign("project-button-$project_id", 'innerHTML', "<a class='b-post__link b-post__link_dot_c10601' href='javascript: void(0);' onclick='banned.blockedProject($project_id); return false;'>Заблокировать</a>");
    				} else {
    				    $projects->DeleteComplains($project_id);
    					$sBlockId = $projects->Blocked($project_id, $reason, $reason_id, $_SESSION['uid']);
    					$project  = $projects->GetPrjCust($project_id);
    
                        // Удаляем черновик
                        require_once($_SERVER['DOCUMENT_ROOT'].'/classes/drafts.php');
                        drafts::DeleteDraftByPrjID($project_id);
    					
    					// пишем лог админских действий
    					admin_log::addLog( admin_log::OBJ_CODE_PROJ, 9, $project['user_id'], $project_id, $project['name'], $sObjLink, 0, '', $reason_id, $reason, $sBlockId );
    					
    					$reason = reformat($project['blocked_reason'], 24, 0, 0, 1, 24);
    					
    					$html = BlockedProjectHTML($reason, $project['blocked_time'], $_SESSION['login'], "{$_SESSION['name']} {$_SESSION['surname']}");
                        $objResponse->assign("project-reason-$project_id", 'innerHTML', $html);
                        $objResponse->assign("project-reason-$project_id", 'style.display', 'block');
    					$objResponse->assign("project-button-$project_id", 'innerHTML', "<a style='color: Red;' href='javascript: void(0);' onclick='banned.unblockedProject($project_id); return false;'>Разблокировать</a>");
                    }
    			} else {
    				$objResponse->alert('Несуществующий проект');
    			}
    		}
    	}
	}
	
	return $objResponse;	
}

/**
 * Блокирование проекта с жалобами
 * 
 * @param integer $project_id    id проекта
 * @param string  $reason        причина
 * @param int     $reason_id     ID причины, если она выбрана из списка (таблица admin_reasons, где act_id = 9)
 * @param string  $reason_name   Краткое описание причины действия (из селекта) для лога админских действий
 */
function BlockedProjectWithComplain( $project_id, $reason, $reason_id = null, $reason_name = '' ) {
	global $DB;
	$objResponse = new xajaxResponse();
	$project_id = intval($project_id);
	session_start();
    
	if ( hasPermissions('projects') ) {
	    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/projects.php';
	    
    	if (is_string($reason) && $reason != '') {
    		if (hasPermissions('projects')) {
    			$count = $DB->val("SELECT COUNT(*) FROM projects WHERE id = ?", $project_id);
    			if ($count) {
    				$projects = new projects;
                    $project = $projects->GetPrjCust($project_id);
                    
                    $reason_id = ($reason_id) ? $reason_id : null;
                    $reason    = str_replace('%USERNAME%', $project['uname'] . ' ' .$project['usurname'], $reason);
                    $reason    = change_q_x($reason, FALSE, TRUE, "", false, false);
                    
                    $projects->SatisfyComplains($project_id);
    				$sBlockId = $projects->Blocked($project_id, $reason, $reason_id, $_SESSION['uid']);
    				
    				// пишем лог админских действий
    				$sObjLink = getFriendlyURL('project', $project_id);
    				admin_log::addLog( admin_log::OBJ_CODE_PROJ, 9, $project['user_id'], $project_id, $project['name'], $sObjLink, 0, '', $reason_id, $reason, $sBlockId );
    				//-----------------------------
    				
    				$objResponse->script("project_banned($project_id);");
    			} else {
    				$objResponse->alert('Несуществующий проект');
    			}
    		}
    	}
	}
	return $objResponse;	
}

/**
 * Устанавливает опции в селекте выбора причины действия администратора
 * 
 * @param  int $actId код действия
 * @param  string $uniqId уникальный индекс массива buffer класса banned (banned.js)
 * @param  string $selId поционально. ID выбранной опции
 * @return object xajaxResponse
 */
function getAdminActionReasons( $actId, $uniqId, $selId = '' ) {
    $objResponse = new xajaxResponse();
    
    $sOut  = '<select id="bfrm_sel_' . $uniqId . '" name="bfrm_sel_' . $uniqId . '" onchange="banned.setReason(\''.$uniqId.'\');" disabled>';
    $sOut .= _getAdminActionReasonOptions( $actId, $selId );
	$sOut .= "</select>";
	
	$objResponse->assign( "bfrm_div_sel_$uniqId", "innerHTML", $sOut );
	$objResponse->script( "banned.buffer['$uniqId'].reasonName = 'Указать вручную';" );
	$objResponse->script( "banned.adjustReasonHTML('$uniqId');" );
	$objResponse->script( "banned.setReason('$uniqId');" );
	
	if ( $actId ) {
    	$objResponse->assign( "bfrm_sel_$uniqId", 'disabled', false );
    	$objResponse->assign( "bfrm_$uniqId", 'disabled', false );
	}
	
	$objResponse->assign( "bfrm_btn_$uniqId", 'disabled', false );
    
    return $objResponse;
}

/**
 * Возвращает HTML код с опциями причины действия администратора
 * 
 * @param  int $actId код действия
 * @param  string $selId поционально. ID выбранной опции
 * @return string HTML код 
 */
function _getAdminActionReasonOptions( $actId, $selId = '' ) {
    $sSel  = ( empty($selId) ) ? ' selected' : '';
    $sOut .= '<option value="" ' . $sSel . ' style="color: #777;">Указать вручную</option>';
    
    $aReasons = admin_log::getAdminReasons( $actId );
    
    if ( $aReasons ) {
    	foreach ( $aReasons as $aOne ) {
    	    $sSel  = ( $selId == $aOne['id'] ) ? ' selected' : '';
            $sBold = $aOne['is_bold'] == 't' ? ' style="background-color: #cdcdcd;"' : ' style="color: #777;"';
    		$sOut .= '<option value="' . $aOne['id'] . '" ' . $sSel . $sBold .'>' . $aOne['reason_name'] . '</option>';
    	}
	}
	
	return $sOut;
}

/**
 * Устанавливает полный текст причины действия администратора в поле формы
 * 
 * @param  string $uniqId уникальный индекс массива buffer класса banned (banned.js)
 * @param  int $reasonId ID причины, полный текст которой нужно установить
 * @return object xajaxResponse
 */
function getAdminActionReasonText( $uniqId, $reasonId ) {
    $objResponse = new xajaxResponse();
    
    $sReason = admin_log::getAdminReasonText( $reasonId );
    
    $objResponse->assign( "bfrm_$uniqId", "value", $sReason );
    $objResponse->script( "banned.reasons['$reasonId'] = '$sReason';" );
    
    return $objResponse;
}

function getAdminActionReasonTextStream( $uniqId, $reasonId ) {
    $objResponse = new xajaxResponse();
    
    $sReason = admin_log::getAdminReasonText( $reasonId );
    
    $objResponse->assign( "bfrm_stream_$uniqId", "value", $sReason );
    
    return $objResponse;
}

function getAdminActionReasonTextDel( $uniqId, $reasonId ) {
    $objResponse = new xajaxResponse();
    
    $sReason = admin_log::getAdminReasonText( $reasonId );
    
    $objResponse->assign( "bfrm_$uniqId", "value", $sReason );
    
    return $objResponse;
}

/**
 * Блокирование/разблокирование сообщества
 * 
 * @param  int    $commune_id
 * @param  string $reason        причина
 * @param  int    $reason_id     ID причины, если она выбрана из списка (таблица admin_reasons, где act_id = 11)
 * @param  string $reason_name   Краткое описание причины действия (из селекта) для лога админских действий
 * @return object xajaxResponse
 */
function BlockedCommune( $commune_id, $reason, $reason_id = null, $reason_name = '' ) {
    global $DB;
    $objResponse = new xajaxResponse();
	$commune_id = intval($commune_id);
	session_start();
    
	if ( hasPermissions('communes') ) {
	    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/commune.php';
	    
    	if (is_string($reason) && $reason != '') {
    		if (hasPermissions('communes')) {
    			$count = $DB->val("SELECT COUNT(*) FROM commune WHERE id = ?", $commune_id);
    			if ($count) {
    				$commune = new commune;
                    $comm = $commune->GetCommune($commune_id, NULL, $_SESSION['role']);
                    
                    // лог админских действий
    				$sObjName  = $comm['name'];
    				$sObjLink  = '/commune/?id=' . $commune_id;
    				$reason_id = ($reason_id) ? $reason_id : null;
                    $reason    = str_replace('%USERNAME%', $comm['author_uname'] . ' ' . $comm['author_usurname'], $reason);
                    $reason    = change_q($reason, FALSE, 0, TRUE);
                    
    				if ($comm['is_blocked']) {
    					$commune->UnBlocked($commune_id);
    					
    					// пишем лог админских действий
    					admin_log::addLog( admin_log::OBJ_CODE_COMM, 12, $comm['author_id'], $commune_id, $sObjName, $sObjLink, 0, '', $reason_id, $reason );
    					
    					$objResponse->assign("blocked-reason-$commune_id", 'innerHTML', '&nbsp;');
                        $objResponse->assign("blocked-reason-$commune_id", 'style.display', 'none');
    					$objResponse->assign("blocked-button-$commune_id", 'innerHTML', '<a class="b-menu__link b-menu__link_fontsize_11 b-menu__link_color_c10600" href="javascript:;" onclick="banned.blockedCommune('.$comm['id'].')">Заблокировать сообщество</a>');
    					$objResponse->assign("commune-reason-$commune_id", 'innerHTML', '&nbsp;');
    				} else {
    					$sBlockId  = $commune->Blocked( $commune_id, $reason, $reason_id, $_SESSION['uid'] );
    					
    					// пишем лог админских действий
    					admin_log::addLog( admin_log::OBJ_CODE_COMM, 11, $comm['author_id'], $commune_id, $sObjName, $sObjLink, 0, '', $reason_id, $reason, $sBlockId );
    					
                        $comm   = $commune->GetCommune($commune_id, NULL, $_SESSION['role']);
                        $reason = reformat($comm['blocked_reason'], 24, 0, 0, 1, 24);
                        $html   = __commPrntBlockedBlock($reason, $comm['blocked_time'], $_SESSION['login'], "{$_SESSION['name']} {$_SESSION['surname']}", $commune_id);
                        $objResponse->assign("blocked-reason-$commune_id", 'innerHTML', $html);
                        $objResponse->assign("blocked-reason-$commune_id", 'style.display', 'block');
                        $objResponse->assign("blocked-button-$commune_id", 'innerHTML', '<a class="b-menu__link b-menu__link_fontsize_11 b-menu__link_color_c10600" href="javascript:;" onclick="banned.unblockedCommune('.$comm['id'].')">Разблокировать сообщество</a>');
                        $objResponse->assign("commune-reason-$commune_id", 'innerHTML', '&nbsp;');
                    }
    			} else {
    				$objResponse->alert('Несуществующее сообщество');
    			}
    		}
    	}
	}
	
	return $objResponse;	
    
}

/**
 * Отображение красного прямоугольника с блокировкой топика
 * 
 * @param  string $reason причина блокировки
 * @return string HTML блок
 */
function BlockedCommuneThemeHTML( $reason = '' ) {
    ob_start(); ?>
        <div class='b-fon b-fon_clear_both b-fon_width_full b-fon_padtop_20'>
            <div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_padleft_30 b-fon__body_bg_ff6d2d">
                <span class="b-fon__attent_white"></span>
                <span class="b-fon__txt b-fon__txt_bold">Пост заблокирован:</span>
                <?php if ($reason) { ?> <?=str_replace("\n", "<br>", ($reason))?><?php } ?>
            </div>
        </div>
    <? $html = ob_get_clean();
    /*return "<div class='br-moderation-options'>
        <a href='/about/feedback/' class='lnk-feedback' style='color: #fff;'>Служба поддержки</a>
        <div class='br-mo-status'><strong>Сообщение заблокировано!</strong> Причина: $reason</div>
        <p class='br-mo-info'>
        Заблокировал: <a href='/users/{$_SESSION['login']}' style='color: #FF6B3D'>{$_SESSION['name']} {$_SESSION['surname']} [{$_SESSION['login']}]</a><br />
        Дата блокировки: ". date('d.m.Y')."</p>
    </div>";*/
    return $html;
}

/**
 * Блокировка/разблокировка топика администратором free-lance.ru
 * 
 * @param  int    $commune_id  ID сообщества
 * @param  int    $topic_id    ID топика
 * @param  int    $topic_id    ID сообщения
 * @param  string $reason      Причина
 * @param  int    $reason_id   ID причины, если она выбрана из списка (таблица admin_reasons, где act_id = 15)
 * @param  string $reason_name Краткое описание причины действия (из селекта) для лога админских действий
 * @return object xajaxResponse
 */
function BlockedCommuneTheme( $commune_id = 0, $topic_id = 0, $msg_id = 0, $reason, $reason_id = null, $reason_name = '' ) {
    global $DB;
	session_start();
    $objResponse = new xajaxResponse();
	$commune_id  = intval( $commune_id );
    $topic_id    = intval( $topic_id );
    $msg_id      = intval( $msg_id );
	
    if ( hasPermissions('communes') ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/commune.php' );
        
    	if (is_string($reason) && $reason != '') {
    	    if (hasPermissions('communes')) {
    	        $count = $DB->val( 'SELECT COUNT(*) FROM commune_themes WHERE id = ?', $topic_id );
    	        
    	        if ( $count ) {
    	            $commune = new commune;
    	            $topic   = commune::GetTopMessageByAnyOther( $msg_id, $_SESSION['uid'], commune::MOD_ADMIN );
    	            
    	            // лог админских действий
    				$sObjName  = $topic['title'];
    				$sObjLink  = '/commune/?id='. $commune_id .'&site=Topic&post=' . $msg_id;
    				$reason_id = ( $reason_id ) ? $reason_id : null;
                    $reason    = str_replace( '%USERNAME%', $topic['user_uname'] . ' ' . $topic['user_usurname'], $reason );
                    $reason    = change_q( $reason, FALSE, 0, TRUE );
    	            
    	            if ( $topic['is_blocked_s'] == 't' ) {
    	                $commune->unblockedCommuneTheme( $topic_id );
    					
    					// пишем лог админских действий
    					admin_log::addLog( admin_log::OBJ_CODE_COMM, 16, $topic['user_id'], $topic_id, $sObjName, $sObjLink, 0, '', $reason_id, $reason );
    					
    					$objResponse->assign( "theme-reason-$topic_id", 'innerHTML', '&nbsp;' );
    					$objResponse->assign( "theme-button-$topic_id", 'innerHTML', '<a href="javascript:void(0)" onclick="banned.blockedCommuneTheme('.$commune_id.','.$topic_id.','.$msg_id.')" class="lnk-red">Заблокировать</a>' );
    	            }
    	            else {
    					$commune->blockedCommuneTheme( $topic, $reason, $reason_id, $_SESSION['uid'] );
    					
    					// пишем лог админских действий
    					admin_log::addLog( admin_log::OBJ_CODE_COMM, 15, $topic['user_id'], $topic_id, $sObjName, $sObjLink, 0, '', $reason_id, $reason, $topic_id );
    					
    					$reason = reformat( $reason, 24, 0, 0, 1, 24 );
                        $html   = BlockedCommuneThemeHTML( $reason );
    					
    					$objResponse->assign( "theme-reason-$topic_id", 'innerHTML', $html);
    					$objResponse->assign( "theme-button-$topic_id", 'innerHTML', '<a href="javascript:void(0)" onclick="banned.unblockedCommuneTheme('.$commune_id.','.$topic_id.','.$msg_id.')" class="lnk-red">Разблокировать</a>' );
    	            }
    	        } 
    	        else {
    				$objResponse->alert( 'Несуществующее сообщение в сообществе' );
    			}
    	    }
    	}
	}
	
	return $objResponse;
}

/**
 * Отображение красного прямоугольника с блокировкой предложения фрилансера
 * 
 * @param  string $reason причина блокировки
 * @return string HTML блок
 */
function BlockedFreelanceOfferHTML( $reason ) {
	return "
        <div class='br-moderation-options'>
            <a href='/about/feedback/' class='lnk-feedback' style='color: #fff;'>Служба поддержки</a>
            <div class='br-mo-status'><strong>Предложение заблокировано.</strong> Причина: ".str_replace("\n", "<br>", $reason)."</div>
        </div>
    ";
}

/**
 * Блокирование/разблокирование предложения фрилансера
 * 
 * @param  int    $offer_id
 * @param  string $reason        причина
 * @param  int    $reason_id     ID причины, если она выбрана из списка (таблица admin_reasons, где act_id = 13)
 * @param  string $reason_name   Краткое описание причины действия (из селекта) для лога админских действий
 * @return object xajaxResponse
 */
function BlockedFreelanceOffer( $offer_id, $reason, $reason_id = null, $reason_name = '' )  {
    session_start();
    $objResponse = new xajaxResponse();
    
    if ( hasPermissions('projects') ) {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/freelancer_offers.php';
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
        
        $frl_offers = new freelancer_offers();
    	$offer_id   = intval( $offer_id );
        $offer      = $frl_offers->getOfferById( $offer_id );
        
        if ( $offer ) {
            $objUser = new users();
            $objUser->GetUserByUID( $offer['user_id'] );
            
        	// лог админских действий
			$sObjName  = $offer['title'];
			$sObjLink  = ''; // нет ссылки на конкретное предложение
			$reason_id = ($reason_id) ? $reason_id : 0;
		    $reason    = str_replace('%USERNAME%', $objUser->uname . ' ' . $objUser->usurname, $reason);
            $reason    = change_q($reason, FALSE, 0, TRUE);
			
			if ( $offer['is_blocked'] == 't' ) { // был заблокирован - разблокируем
				$update = array( 'is_blocked' => false, 'reason'=> '', 'reason_id' => 0, 'admin' => 0 );
				
				// пишем лог админских действий
                admin_log::addLog( admin_log::OBJ_CODE_OFFER, 14, $offer['user_id'], $offer_id, $sObjName, $sObjLink, 0, '', $reason_id, $reason );
                
                $objResponse->assign("freelance-offer-reason-txt-$offer_id", 'innerHTML', '&nbsp;');
                $objResponse->assign("freelance-offer-reason-$offer_id", 'style.display', 'none');
                $objResponse->assign("freelance-offer-button-$offer_id", 'innerHTML', '<a class="b-buttons__link b-buttons__link_dot_c10601 b-buttons__link_margleft_10" href="javascript:void(0);" onclick="banned.blockedFreelanceOffer('.$offer_id.')">Заблокировать</a>');
			}
			else { // был разблокирован - блокируем
			    $objUser = new users();
			    $objUser->GetUserByUID( $offer['user_id'] );
			    
			    $update    = array( 'is_blocked' => true, 'reason' => $reason, 'reason_id' => $reason_id, 'admin' => $_SESSION['uid'] );
			    
			    // пишем лог админских действий
                admin_log::addLog( admin_log::OBJ_CODE_OFFER, 13, $offer['user_id'], $offer_id, $sObjName, $sObjLink, 0, '', $reason_id, $reason, $offer_id );
                
                $reason = reformat( $reason, 24, 0, 0, 1, 24 );
                $html   = BlockedFreelanceOfferHTML( $reason );
                
                $objResponse->assign("freelance-offer-reason-txt-$offer_id", 'innerHTML', $reason);
                $objResponse->assign("freelance-offer-reason-$offer_id", 'style.display', 'block');
                $objResponse->assign("freelance-offer-button-$offer_id", 'innerHTML', '<a class="b-buttons__link b-buttons__link_dot_c10601 b-buttons__link_margleft_10" href="javascript:void(0);" onclick="banned.unblockedFreelanceOffer('.$offer_id.')">Разблокировать</a>');
                
                $objResponse->script( "if(banned.reload==1){window.location.reload(true);}" );
			}
			
			$frl_offers->Update( $offer_id, $update );
			$objResponse->script("$('freelance-offer-block-$offer_id').set('html', '&nbsp;')");
			//$objResponse->assign("freelance-offer-block-$offer_id", 'innerHTML', '&nbsp;');
        }
        else {
            $objResponse->alert('Несуществующее предложение');
        }
    }
    
    return $objResponse;
}

/**
 * Отображение красного прямоугольника с текстом блокировки предложения по проекту
 * эта функция еще есть в projects/content_frl.php
 *
 */
function BlockedProjectOfferHTML($reason, $moder_login='', $moder_name='') {
	return "<div class='b-fon b-fon_clear_both b-fon_bg_ff6d2d b-fon_padtop_10 b-fon_padbot_10'>
        <b class='b-fon__b1'></b>
        <b class='b-fon__b2'></b>
        <div class='b-fon__body b-fon__body_pad_5_10 b-fon__body_fontsize_13'>
            <span class='b-fon__attent'></span>
            <div class='b-fon__txt b-fon__txt_margleft_20'>
                <span class='b-fon__txt_bold'>Предложение заблокировано.</span> ".str_replace("\n", "<br>", $reason)."
                    <a class='b-fon__link' href='https://feedback.fl.ru/'>Служба поддержки</a>
                    <div class='b-fon__txt'>
                        Заблокировал:
                        <a class='b-fon__link' href='/users/$moder_login'>$moder_name [$moder_login]</a>
                        <br>
                        Дата блокировки: ".dateFormat('d.m.Y H:i', date('Y-m-d H:i:s'))."
                    </div>
            </div>
        </div>
        <b class='b-fon__b2'></b>
        <b class='b-fon__b1'></b>
    </div>";
}

/**
 * Блокирование/разблокирование предложения по проекту
 * 
 * @param  int    $offer_id      ID предложения
 * @param  int    $user_id       UID пользователя
 * @param  int    $project_id    ID проекта
 * @param  string $reason        причина
 * @param  int    $reason_id     ID причины, если она выбрана из списка (таблица admin_reasons, где act_id = 27)
 * @param  string $reason_name   Краткое описание причины действия (из селекта) для лога админских действий
 * @return object xajaxResponse
 */
function BlockedProjectOffer( $offer_id, $user_id, $project_id, $reason, $reason_id = null, $reason_name = '' )  {
    session_start();
    $objResponse = new xajaxResponse();
    
    if ( hasPermissions('projects') ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/projects_offers.php' );
        
        $projects_offers = new projects_offers();
    	$offer_id        = intval( $offer_id );
        $offer           = $projects_offers->GetPrjOfferById( $offer_id );
        
        if ( $offer && $offer['id'] == $offer_id ) {
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/projects.php' );
            
            $objUser = new users();
            $objUser->GetUserByUID( $offer['user_id'] );
            
        	// лог админских действий
            $projects  = new projects;
            $project   = $projects->GetPrjCust($project_id);
			$sObjName  = $project['name'];
			$sObjLink  = getFriendlyURL( 'project', $project_id ); 
			$reason_id = ($reason_id) ? $reason_id : 0;
		    $reason    = str_replace('%USERNAME%', $objUser->uname . ' ' . $objUser->usurname, $reason);
            $reason    = change_q($reason, FALSE, 0, TRUE);
			
			if ( $offer['is_blocked'] == 't' ) {
                $projects_offers->UnBlocked( $offer_id );

                // пишем лог админских действий
                admin_log::addLog( admin_log::OBJ_CODE_PROJ, admin_log::ACT_ID_PRJ_UNBLOCK_OFFER, 
                    $user_id, $offer_id, $project['name'], $sObjLink, 0, '', $reason_id, $reason 
                );

                $objResponse->assign("project-offer-block-$offer_id", 'innerHTML', '&nbsp;');
                $objResponse->assign("project-offer-block-$offer_id", 'style.display', 'none');
                $objResponse->assign("project-button-$offer_id", 'innerHTML', '<a class="admn" href="javascript:void(0);" onclick="banned.blockedProjectOffer('.$offer_id.','.$user_id.','.$project_id.')">Заблокировать</a>');
            } else {
                $sBlockId = $projects_offers->Blocked( $offer_id, $user_id, $project_id, $reason, $reason_id, $_SESSION['uid'] );
                
                // пишем лог админских действий
                admin_log::addLog( admin_log::OBJ_CODE_PROJ, admin_log::ACT_ID_PRJ_BLOCK_OFFER, 
                    $user_id, $offer_id, $project['name'], $sObjLink, 0, '', $reason_id, $reason, $sBlockId 
                );

                $reason = reformat($reason, 24, 0, 0, 1, 24);

                $html = BlockedProjectOfferHTML($reason, $_SESSION['login'], "{$_SESSION['name']} {$_SESSION['surname']}");
                $objResponse->assign("project-offer-block-$offer_id", 'innerHTML', $html);
                $objResponse->assign("project-offer-block-$offer_id", 'style.display', 'block');
                $objResponse->assign("project-button-$offer_id", 'innerHTML', '<a class="admn" href="javascript:void(0);" onclick="banned.unblockedProjectOffer('.$offer_id.','.$user_id.','.$project_id.')">Разблокировать</a>');
                $objResponse->script("$('ban_btn').addClass('b-button_rectangle_color_green').removeClass('b-button_rectangle_color_disable');");
            }
        }
        else {
            $objResponse->alert('Несуществующее предложение');
        }
    }
    
    return $objResponse;
}

/**
 * Отображение красного прямоугольника с текстом блокировки работы в портфолио
 * эта функция еще есть в projects/content_frl.php
 *
 */
function BlockedPortfolioHTML($reason, $moder_login='', $moder_name='') {
	return "<div class='b-fon b-fon_clear_both b-fon_bg_ff6d2d b-fon_padtop_10 b-fon_padbot_10'>
        <b class='b-fon__b1'></b>
        <b class='b-fon__b2'></b>
        <div class='b-fon__body b-fon__body_pad_5_10 b-fon__body_fontsize_13'>
            <span class='b-fon__attent'></span>
            <div class='b-fon__txt b-fon__txt_margleft_20'>
                <span class='b-fon__txt_bold'>Работа заблокирована.</span> ".str_replace("\n", "<br>", $reason)."
                    <a class='b-fon__link' href='https://feedback.fl.ru/'>Служба поддержки</a>
                    <div class='b-fon__txt'>
                        Заблокировал:
                        <a class='b-fon__link' href='/users/$moder_login'>$moder_name [$moder_login]</a>
                        <br>
                        Дата блокировки: ".dateFormat('d.m.Y H:i', date('Y-m-d H:i:s'))."
                    </div>
            </div>
        </div>
        <b class='b-fon__b2'></b>
        <b class='b-fon__b1'></b>
    </div>";
}

/**
 * Блокирование/разблокирование работы в портфолио
 * 
 * @param  int    $portfolio_id  ID работы в портфолио
 * @param  string $reason        причина
 * @param  int    $reason_id     ID причины, если она выбрана из списка (таблица admin_reasons, где act_id = 27)
 * @param  string $reason_name   Краткое описание причины действия (из селекта) для лога админских действий
 * @return object xajaxResponse
 */
function BlockedPortfolio( $portfolio_id, $reason, $reason_id = null, $reason_name = '' )  {
    session_start();
    $objResponse = new xajaxResponse();
    
    if ( hasPermissions('users') ) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/portfolio.php");
        
    	$portfolio_id = intval( $portfolio_id );
        $portfolio    = portfolio::GetPrj( $portfolio_id );
        
        if ( $portfolio ) {
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
            
            $objUser = new users();
            $objUser->GetUserByUID( $portfolio['user_id'] );
            
        	// лог админских действий
			$sObjName  = $portfolio['name'];
			$sObjLink  = '/users/'. $objUser->login .'/viewproj.php?prjid='. $portfolio['id']; 
			$reason_id = ($reason_id) ? $reason_id : 0;
		    $reason    = str_replace('%USERNAME%', $objUser->uname . ' ' . $objUser->usurname, $reason);
            $reason    = change_q($reason, FALSE, 0, TRUE);
            $user_id   = $portfolio['user_id'];
			
			if ( $portfolio['is_blocked'] == 't' ) {
                portfolio::UnBlocked( $portfolio_id );

                // пишем лог админских действий
                admin_log::addLog( admin_log::OBJ_CODE_PROJ, admin_log::ACT_ID_PORTFOLIO_UNBLOCK, 
                    $user_id, $portfolio_id, $sObjName, $sObjLink, 0, '', $reason_id, $reason 
                );

                $objResponse->assign("portfolio-block-$portfolio_id", 'innerHTML', '&nbsp;');
                $objResponse->assign("portfolio-block-$portfolio_id", 'style.display', 'none');
                $objResponse->assign("portfolio-button-$portfolio_id", 'innerHTML', '<a class="admn" href="javascript:void(0);" onclick="banned.blockedPortfolio('. $portfolio_id .')">Заблокировать</a>');
            } else {
                $sBlockId = portfolio::Blocked( $portfolio_id, $reason, $reason_id, $_SESSION['uid'] );
                
                // пишем лог админских действий
                admin_log::addLog( admin_log::OBJ_CODE_PROJ, admin_log::ACT_ID_PORTFOLIO_BLOCK, 
                    $user_id, $portfolio_id, $sObjName, $sObjLink, 0, '', $reason_id, $reason, $sBlockId 
                );

                $reason = reformat($reason, 24, 0, 0, 1, 24);

                $html = BlockedPortfolioHTML($reason, $_SESSION['login'], "{$_SESSION['name']} {$_SESSION['surname']}");
                $objResponse->assign("portfolio-block-$portfolio_id", 'innerHTML', $html);
                $objResponse->assign("portfolio-block-$portfolio_id", 'style.display', 'block');
                $objResponse->assign("portfolio-button-$portfolio_id", 'innerHTML', '<a class="admn" href="javascript:void(0);" onclick="banned.unblockedPortfolio('. $portfolio_id .')">Разблокировать</a>');
            }
        }
        else {
            $objResponse->alert('Несуществующее предложение');
        }
    }
    
    return $objResponse;
}

////////////////////

/**
 * Отображение красного прямоугольника с текстом блокировки комментария к предложению по проекту
 * эта функция еще есть в projects/content_frl.php
 *
 */
function BlockedDialogueHTML($reason, $moder_login='', $moder_name='') {
	return "<div class='b-fon b-fon_clear_both b-fon_bg_ff6d2d b-fon_padtop_10 b-fon_padbot_10'>
        <b class='b-fon__b1'></b>
        <b class='b-fon__b2'></b>
        <div class='b-fon__body b-fon__body_pad_5_10 b-fon__body_fontsize_13'>
            <span class='b-fon__attent'></span>
            <div class='b-fon__txt b-fon__txt_margleft_20'>
                <span class='b-fon__txt_bold'>Комментарий заблокирован.</span> ".str_replace("\n", "<br>", $reason)."
                    <a class='b-fon__link' href='https://feedback.fl.ru/'>Служба поддержки</a>
                    <div class='b-fon__txt'>
                        Заблокировал:
                        <a class='b-fon__link' href='/users/$moder_login'>$moder_name [$moder_login]</a>
                        <br>
                        Дата блокировки: ".dateFormat('d.m.Y H:i', date('Y-m-d H:i:s'))."
                    </div>
            </div>
        </div>
        <b class='b-fon__b2'></b>
        <b class='b-fon__b1'></b>
    </div>";
}

/**
 * Блокирование/разблокирование комментария к предложению по проекту
 * 
 * @param  int    $dialogue_id   ID комментария
 * @param  string $reason        причина
 * @param  int    $reason_id     ID причины, если она выбрана из списка (таблица admin_reasons, где act_id = 27)
 * @param  string $reason_name   Краткое описание причины действия (из селекта) для лога админских действий
 * @return object xajaxResponse
 */
function BlockedDialogue( $dialogue_id, $reason, $reason_id = null, $reason_name = '' )  {
    session_start();
    $objResponse = new xajaxResponse();
    
    if ( hasPermissions('users') ) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_offers_dialogue.php");
        
    	$dialogue_id = intval( $dialogue_id );
        $dialogue    = projects_offers_dialogue::getDialogueMessageById( $dialogue_id );
        
        if ( $dialogue ) {
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
            
            $objUser = new users();
            $objUser->GetUserByUID( $dialogue['user_id'] );
            
        	// лог админских действий
			$sObjName  = $dialogue['project_name'];
			$sObjLink  = getFriendlyURL( 'project', $dialogue['project_id'] );
			$reason_id = ($reason_id) ? $reason_id : 0;
		    $reason    = str_replace('%USERNAME%', $objUser->uname . ' ' . $objUser->usurname, $reason);
            $reason    = change_q($reason, FALSE, 0, TRUE);
			
			if ( $dialogue['is_blocked'] == 't' ) {
                projects_offers_dialogue::UnBlocked( $dialogue_id );

                // пишем лог админских действий
                admin_log::addLog( admin_log::OBJ_CODE_PROJ, admin_log::ACT_ID_PRJ_DIALOG_UNBLOCK, 
                    $dialogue['user_id'], $dialogue_id, $sObjName, $sObjLink, 0, '', $reason_id, $reason 
                );

                $objResponse->assign("dialogue-block-$dialogue_id", 'innerHTML', '&nbsp;');
                $objResponse->assign("dialogue-block-$dialogue_id", 'style.display', 'none');
                $objResponse->assign("dialogue-button-$dialogue_id", 'innerHTML', '<a class="admn" href="javascript:void(0);" onclick="banned.blockedDialogue('. $dialogue_id .')">Заблокировать</a>');
            } else {
                $sBlockId = projects_offers_dialogue::Blocked( $dialogue_id, $reason, $reason_id, $_SESSION['uid'] );
                
                // пишем лог админских действий
                admin_log::addLog( admin_log::OBJ_CODE_PROJ, admin_log::ACT_ID_PRJ_DIALOG_BLOCK, 
                    $dialogue['user_id'], $dialogue_id, $sObjName, $sObjLink, 0, '', $reason_id, $reason, $sBlockId 
                );

                $reason = reformat($reason, 24, 0, 0, 1, 24);

                $html = BlockedDialogueHTML($reason, $_SESSION['login'], "{$_SESSION['name']} {$_SESSION['surname']}");
                $objResponse->assign("dialogue-block-$dialogue_id", 'innerHTML', $html);
                $objResponse->assign("dialogue-block-$dialogue_id", 'style.display', 'block');
                $objResponse->assign("dialogue-button-$dialogue_id", 'innerHTML', '<a class="admn" href="javascript:void(0);" onclick="banned.unblockedDialogue('. $dialogue_id .')">Разблокировать</a>');
            }
        }
        else {
            $objResponse->alert('Несуществующее предложение');
        }
    }
    
    return $objResponse;
}

$xajax->processRequest();
?>
