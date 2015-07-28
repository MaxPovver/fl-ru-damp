<?php
$rpath = '../';
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/admin_log.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/xajax/admin_log.common.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stdf.php' );
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/permissions.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/commune.php");

/**
 * Возвращает список предупреждение пользователя для попап окна
 * 
 * @param  int $uid UID пользователя
 * @param  array $contextId Контекст (для лога админских действий)
 * @param  string $draw_func способ отображения
 * @return object xajaxResponse
 */
function getUserWarns( $uid = 0, $contextId = '', $draw_func = '' ) {
    session_start();
    $objResponse = new xajaxResponse();
    
    if ( hasPermissions('users') ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/permissions.php' );
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
        
        $user = new users();
        $user->GetUserByUID( $uid );
        
        if ( $user->uid ) {
            $aPermissions = permissions::getUserPermissions( $_SESSION['uid'] );
            $admin_log    = new admin_log( 'user', $_SESSION['uid'], $aPermissions );
        	$aWarns       = $admin_log->getUserWarns( $nCount, $uid );
        	$sCount       = $nCount ? $nCount : '0';
        	$sWarns       = $user->warn ? $user->warn : '0';
        	
        	$objResponse->assign('a_user_warns', 'href', '/users/' . $user->login );
        	$objResponse->assign('s_user_warns', 'innerHTML', $user->uname . ' ' . $user->usurname . ' [' . $user->login . ']' );
        	$objResponse->assign('e_user_warns', 'innerHTML', $sWarns );
        	$objResponse->assign('n_user_warns', 'innerHTML', $sCount );
        	
        	if ( $nCount ) {
        	    $sTable = '<table id="t_user_warns" class="notice-table">';
        	    $nCount = 1;
        	    
        	    foreach ( $aWarns as $aOne ) {
        	        $sReason = $aOne['admin_comment'] ? hyphen_words($aOne['admin_comment'], true) : '&lt;без причины&gt;';
        	        $sAdmin  = $aOne['adm_login'] ? '<a target="_blank" href="/users/'. $aOne['adm_login'] .'">'. $aOne['adm_login'] .'</a>' : 'не известно';
        	        $sDate   = $aOne['act_time'] ? date('d.m.Y H:i', strtotime($aOne['act_time'])) : 'не известно';
        	    	$sTable .= '<tr>
                    	<td class="cell-number">'. $nCount .'.</td>
                    	<td class="cell-uwarn">'. $sReason .'</td>
                    	<td class="cell-who">Выдан: ['. $sAdmin .']
                    	<td class="cell-date">'. $sDate .'</td>
                        <td'.( $aOne['src_id'] ? ' id="i_user_warns_'. $aOne['src_id'] .'"' : '' ).'>'. ( $aOne['src_id'] ? '<a href="javascript:void(0);" onclick="banned.warnUser('.$uid.','.$aOne['src_id'].',\''.$draw_func.'\',\''.$contextId.'\',0);"><img src="/images/btn-remove2.png" alt="" width="11" height="11" /></a>' : '' ) .'</td>
                    </tr>';
        	    	
        	    	$nCount++;
        	    }
        	    
        	    $sTable .= '</table>';
        	    
        		$objResponse->assign('d_user_warns', 'innerHTML', $sTable );
        	}
        	else {
        	    $objResponse->assign('d_user_warns', 'innerHTML', '&nbsp;' );
        	}
        	
        	$sBanTitle = ( $user->is_banned || $user->ban_where ) ? 'Разбанить' : 'Забанить';
        	
        	$objResponse->script( "adminLogOverlayClose();" );
        	$objResponse->script( "$('ov-notice4').setStyle('display', '');" );
        	$objResponse->script( "adjustUserWarnsHTML();" );
        	$objResponse->assign( 'b_user_warns', 'innerHTML', '<button onclick="adminLogOverlayClose();banned.userBan('.$uid.', \''.$contextId.'\',0)">'.$sBanTitle.'</button><a class="lnk-dot-grey" href="javascript:void(0);" onclick="adminLogOverlayClose();">Отмена</a>' );
        }
    }
    
    return $objResponse;
}

/**
 * Изменение блокировки проекта
 * 
 * @param  int $project_id ID проекта
 * @param  int $act_id ID нового действия (admin_actions)
 * @param  int $src_id ID исходного действия (projects_blocked)
 * @param  string $reason причина
 * @param  int $reason_id ID причины, если она выбрана из списка (таблица admin_reasons, где act_id = 9)
 * @return object xajaxResponse
 */
function updatePrjBlock( $project_id, $act_id, $src_id, $reason = '', $reason_id = null ) {
    session_start();
    $objResponse = new xajaxResponse();
    
    if ( hasPermissions('projects') ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/projects.php' );
        
        $projects  = new projects;
        $project   = $projects->GetPrjCust( $project_id );
        $sObjLink  = '/projects/?pid=' . $project_id; // лог админских действий
        $reason_id = ($reason_id) ? $reason_id : null;
        $reason    = str_replace('%USERNAME%', $project['uname'] . ' ' .$project['usurname'], $reason);
        $reason    = change_q( $reason, FALSE, 0, TRUE );
        
        if ( $act_id == 10 && $src_id ) { 
            // разблокируем проект
        	$projects->UnBlocked( $project_id );
    		
    		// пишем лог админских действий
    		admin_log::addLog( admin_log::OBJ_CODE_PROJ, 10, $project['user_id'], $project_id, $project['name'], $sObjLink, 0, '', $reason_id, $reason );
    		
    		// так как появилось новое действие в логе?
    		$objResponse->script( 'window.location="/siteadmin/admin_log/?site=proj";' );
        }
        elseif ( $act_id == 9 && $src_id ) { 
            // редактируем текущую блокировку в projects_blocked, admin_log обновится триггером
            admin_log::updateProjBlock( $src_id, $reason, $reason_id );
            
            $reason = reformat($project['blocked_reason'], 24, 0, 0, 1, 24);
            
            $objResponse->script( 'window.location.reload(true)' );
        }
        elseif ( $act_id == 9 && !$src_id ) { 
            // блокируем проект
    		$sBlockId = $projects->Blocked( $project_id, $reason, $reason_id, $_SESSION['uid'] );
    		$project  = $projects->GetPrjCust( $project_id );
    		
    		// пишем лог админских действий
    		admin_log::addLog( admin_log::OBJ_CODE_PROJ, 9, $project['user_id'], $project_id, $project['name'], $sObjLink, 0, '', $reason_id, $reason, $sBlockId );
    		
    		// так как появилось новое действие в логе?
    		$objResponse->script( 'window.location="/siteadmin/admin_log/?site=proj";' );
        }
    }
    
    return $objResponse;
}

/**
 * Устанавливает поля в форме редактирования блокировки проекта
 * 
 * @param  int $obj_id ID состояние объекта
 * @param  int $last_act Текущее состояние объекта (ID действия из admin_actions)
 * @param  int $src_id ID исходного действия (projects_blocked)
 * @param  int $edit флаг редактирования причины блокировки
 * @return object xajaxResponse
 */
function setPrjBlockForm( $obj_id, $last_act, $src_id = 0, $edit = 0 ) {
    session_start();
    $objResponse = new xajaxResponse();
    
    if ( hasPermissions('projects') ) {
        $sReason  = $customReason = '';
        $reasonId = 0;
        
        if ( $last_act == 10 ) {
            // инициализируем блокировкой по умолчанию
            $nActId = 9;
            $objResponse->assign( 'lr1', 'innerHTML', 'Заблокировать' );
        }
        else {
            if ( $edit ) {
                // инициализируем данными блокировки
                $nActId   = 9;
                $aBlock   = admin_log::getProjBlock( $src_id );
                $sReason  = $aBlock['reason'];
                $reasonId = $aBlock['reason_id'];
                
                $objResponse->assign( 'lr1', 'innerHTML', 'Редактировать блокировку' );
            }
            else {
                // инициализируем разблокировкой по умолчанию
                $nActId = 10;
                $objResponse->assign( 'lr1', 'innerHTML', 'Разблокировать' );
            }
        }
        
        $customReason = $reasonId ? ''   : $sReason;
        $readonly     = $reasonId ? true : false;
        
        $sBanDiv = '<div id="bfrm_div_sel_0"><select><option>Подождите...</option></select></div>' 
            . '<textarea id="bfrm_0" name="bfrm_0" cols="" rows="">' . clearTextForJS( html_entity_decode($sReason, ENT_QUOTES, 'cp1251')) . '</textarea>';
        
        $objResponse->assign( 'prj_ban_div', 'innerHTML', $sBanDiv );
        $objResponse->script( "banned.buffer[0] = new Object();");
        $objResponse->script( "banned.buffer[0].customReason = new Array();");
        $objResponse->script( "banned.buffer[0].reasonId = new Array();");
        $objResponse->script( "banned.buffer[0].act_id = '$nActId';");
        $objResponse->script( "banned.buffer[0].objectId = '$obj_id';");
        $objResponse->script( "banned.buffer[0].srcId = '$src_id';" );
        $objResponse->script( "banned.buffer[0].customReason[$nActId] = '$customReason';" );
        $objResponse->script( "banned.buffer[0].reasonId[$nActId] = '$reasonId';" );
        $objResponse->script( "xajax_getAdminActionReasons( $nActId, '0', '$reasonId' );" );
        $objResponse->script( "$('ov-notice3').setStyle('display', '');" );
    }
    
    return $objResponse;
}

/**
 * Изменение блокировки предложения фрилансера
 * 
 * @param  int $offer_id ID предложения фрилансера
 * @param  int $act_id ID нового действия (admin_actions)
 * @param  int $src_id ID исходного действия (в данном случае равен $obj_id или 0 - просто индикатор)
 * @param  string $reason причина
 * @param  int $reason_id ID причины, если она выбрана из списка (таблица admin_reasons, где act_id = 13)
 * @return object xajaxResponse
 */
function updateOfferBlock( $offer_id, $act_id, $src_id, $reason = '', $reason_id = null ) {
    session_start();
    $objResponse = new xajaxResponse();
    
    if ( hasPermissions('projects') ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/freelancer_offers.php' );
        
        $frl_offers = new freelancer_offers();
        $offer_id   = intval( $offer_id );
        $offer      = $frl_offers->getOfferById( $offer_id );
        
        if ( $offer ) {
            $objUser = new users();
            $objUser->GetUserByUID( $offer['user_id'] );
            
            $sObjName  = $offer['title'];
            $sObjLink  = ''; // нет ссылки на конкретное предложение
            $reason_id = ( $reason_id ) ? $reason_id : 0;
    	    $reason    = str_replace( '%USERNAME%', $objUser->uname . ' ' . $objUser->usurname, $reason );
            $reason    = change_q( $reason, FALSE, 0, TRUE );
            
            if ( $act_id == 14 && $src_id ) { 
                // разблокируем предложение
                $update = array( 'is_blocked' => 'f', 'reason'=> '', 'reason_id' => 0, 'admin' => 0 );
                $frl_offers->Update( $offer_id, $update );
                
                // пишем лог админских действий
                admin_log::addLog( admin_log::OBJ_CODE_OFFER, 14, $offer['user_id'], $offer_id, $sObjName, $sObjLink, 0, '', $reason_id, $reason );
                
                // так как появилось новое действие в логе
                $objResponse->script( 'window.location="/siteadmin/admin_log/?site=offer";' );
            }
            elseif ( $act_id == 13 && $src_id ) { 
                // редактируем текущую блокировку предложения
                admin_log::updateOfferBlock( $src_id, $reason, $reason_id );
                
                $objResponse->script( 'window.location.reload(true)' );
            }
            elseif ( $act_id == 13 && !$src_id ) { 
                // блокируем предложение
                $update = array( 'is_blocked' => 't', 'reason' => $reason, 'reason_id' => $reason_id, 'admin' => $_SESSION['uid'] );
                $frl_offers->Update( $offer_id, $update );
                
                // пишем лог админских действий
                admin_log::addLog( admin_log::OBJ_CODE_OFFER, 13, $offer['user_id'], $offer_id, $sObjName, $sObjLink, 0, '', $reason_id, $reason, $offer_id );
                
                // так как появилось новое действие в логе
                $objResponse->script( 'window.location="/siteadmin/admin_log/?site=offer";' );
            }
        }
        else {
            $objResponse->script( 'adminLogOverlayClose();' );
            $objResponse->alert('Несуществующее предложение');
        }
    }
    
    return $objResponse;
}

/**
 * Устанавливает поля в форме редактирования блокировки предложения фрилансера
 * 
 * @param  int $obj_id ID предложения
 * @param  int $last_act Текущее состояние предложения (ID действия из admin_actions)
 * @param  int $src_id ID исходного действия (в данном случае равен $obj_id или 0 - просто индикатор)
 * @param  int $edit флаг редактирования причины блокировки
 * @return object xajaxResponse
 */
function setOfferBlockForm( $obj_id, $last_act, $src_id = 0, $edit = 0 ) {
    session_start();
    $objResponse  = new xajaxResponse();
    
    if ( hasPermissions('projects') ) {
        $sReason  = $customReason = '';
        $reasonId = 0;
        
        if ( $last_act == 14 ) {
            // инициализируем блокировкой по умолчанию
            $nActId = 13;
            $objResponse->assign( 'lr1', 'innerHTML', 'Заблокировать' );
        }
        else {
            if ( $edit ) {
                // инициализируем данными блокировки
                $nActId   = 13;
                $aBlock   = admin_log::getOfferBlock( $src_id );
                $sReason  = $aBlock['reason'];
                $reasonId = $aBlock['reason_id'];
                
                $objResponse->assign( 'lr1', 'innerHTML', 'Редактировать блокировку' );
            }
            else {
                // инициализируем разблокировкой по умолчанию
                $nActId = 14;
                $objResponse->assign( 'lr1', 'innerHTML', 'Разблокировать' );
            }
        }
        
        $customReason = $reasonId ? ''   : $sReason;
        $readonly     = $reasonId ? true : false;
        
        $sBanDiv = '<div id="bfrm_div_sel_0"><select><option>Подождите...</option></select></div>' 
            . '<textarea id="bfrm_0" name="bfrm_0" cols="" rows="">' . clearTextForJS( html_entity_decode($sReason, ENT_QUOTES, 'cp1251')) . '</textarea>';
        
        $objResponse->assign( 'offer_ban_div', 'innerHTML', $sBanDiv );
        $objResponse->script( "banned.buffer[0] = new Object();");
        $objResponse->script( "banned.buffer[0].customReason = new Array();");
        $objResponse->script( "banned.buffer[0].reasonId = new Array();");
        $objResponse->script( "banned.buffer[0].act_id = '$nActId';");
        $objResponse->script( "banned.buffer[0].objectId = '$obj_id';");
        $objResponse->script( "banned.buffer[0].srcId = '$src_id';" );
        $objResponse->script( "banned.buffer[0].customReason[$nActId] = '$customReason';" );
        $objResponse->script( "banned.buffer[0].reasonId[$nActId] = '$reasonId';" );
        $objResponse->script( "xajax_getAdminActionReasons( $nActId, '0', '$reasonId' );" );
        $objResponse->script( "$('ov-notice3').setStyle('display', '');" );
    }
    
    return $objResponse;
}

/**
 * Возвращает список последних IP с которых заходил пользователь
 * 
 * @param  int $sUid UID пользователя
 * @param  int $nCount опционально. количество, 0 - не ограничено
 * @return object xajaxResponse
 */
function getLastIps( $sUid = '', $nCount = 10 ) {
    session_start();
    $objResponse = new xajaxResponse();
    
    if ( hasPermissions('users') ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
        
        $sTable = '<table id="t_last_ten" class="notice-table">';
        $user = new users();
        $user->GetUserByUID( $sUid );
        
        $objResponse->script( "adminLogOverlayClose();" );
        
        if ( $aRows = $user->getLastIps($sUid, $nCount) ) {
            $nCount = 1;
        	
        	foreach ( $aRows as $aOne ) {
        		$sTable .= '<tr>
                    <td class="cell-number">'. $nCount .'.</td>
                    <td><a href="https://www.nic.ru/whois/?query='. long2ip($aOne['ip']) .'" target="_blank">'. long2ip($aOne['ip']) .'</a></td>
                    <td class="cell-date">'. date('d.m.Y H:i:s', strtotime($aOne['date'])) .'</td>
                </tr>';
        		
        		$nCount++;
        	}
        }
        else {
            $sIp     = $user->GetField( $sUid, $error, 'last_ip' );
            $sTable .= '<tr>
                    <td class="cell-number">1.</td>
                    <td><a href="https://www.nic.ru/whois/?query='. $sIp .'" target="_blank">'. $sIp .'</a></td>
                    <td class="cell-date">'. date('d.m.Y H:i:s', strtotime($user->last_time)) .'</td>
                </tr>';
        }
        
        $sTable .= '</table>';
        
        $objResponse->assign( 'a_last_ten', 'href', '/users/' . $user->login );
        $objResponse->assign( 's_last_ten', 'innerHTML', $user->uname . ' ' . $user->usurname . ' [' . $user->login . ']' );
        $objResponse->assign( 'w_last_ten', 'innerHTML', 'IP' );
        $objResponse->assign( 'd_last_ten', 'innerHTML', $sTable );
        $objResponse->script( "$('ov-notice5').setStyle('display', '');" );
        $objResponse->script( "adjustLastTenHTML();" );
    }
    
    return $objResponse;
}

/**
 * Возвращает список последних email которые устанавливал пользователь
 * 
 * @param  int $sUid UID пользователя
 * @param  int $nCount опционально. количество, 0 - не ограничено
 * @return object xajaxResponse
 */
function getLastEmails(  $sUid = '', $nCount = 10 ) {
    session_start();
    $objResponse = new xajaxResponse();
    
    if ( hasPermissions('users') ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
        
        $sTable = '<table id="t_last_ten" class="notice-table"><tr><td>Пользователь не менял email</td></td></table>';
        $user = new users();
        $user->GetUserByUID( $sUid );
        
        $objResponse->script( "adminLogOverlayClose();" );
        
        $aRows = $user->getLastEmails($sUid, $nCount);
        if (!$aRows) {
            $aRows[] = array(
                'email' => $user->email,
                'date' => $user->reg_date
            );
        }

        $nCount = 1;
        $sTable = '<table id="t_last_ten" class="notice-table">';

        foreach ($aRows as $aOne) {
            $sTable .= '<tr>
                <td class="cell-number">'. $nCount .'.</td>
                <td>'. $aOne['email'] .'</td>
                <td class="cell-date">'. date('d.m.Y H:i:s', strtotime($aOne['date'])) .'</td>
            </tr>';

            $nCount++;
        }

        $sTable .= '</table>';
        
        $objResponse->assign( 'a_last_ten', 'href', '/users/' . $user->login );
        $objResponse->assign( 's_last_ten', 'innerHTML', $user->uname . ' ' . $user->usurname . ' [' . $user->login . ']' );
        $objResponse->assign( 'w_last_ten', 'innerHTML', 'email' );
        $objResponse->assign( 'd_last_ten', 'innerHTML', $sTable );
        $objResponse->script( "$('ov-notice5').setStyle('display', '');" );
        $objResponse->script( "adjustLastTenHTML();" );
    }
    
    return $objResponse;
}

/**
 * Обнуляет рейтинг пользователя.
 * 
 * @param  string $sUid UID пользователя
 * @return object xajaxResponse
 */
function nullRating( $sUid = '' ) {
    session_start();
    $objResponse = new xajaxResponse();
    
    if ( hasPermissions('all') ) { // !!! только админы
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
        $bRet = users::NullRating( $sUid, true );
        
        if ( $bRet ) {
            $objResponse->alert( 'Рейтинг успешно обнулен' );
        }
        else {
            $objResponse->alert( 'Ошибка обнуления рейтинга' );
        }
    }
    
    return $objResponse;
}

/**
 * Устанавливает/снимает блокировку денег пользователя
 * 
 * @param  string $sUsers JSON строка с массивом UID пользователей
 * @param  string $sAction действие: block - устанавливает, unblock - снимает
 * @return object xajaxResponse
 */
function updateMoneyBlock(  $sUsers = '', $sAction = 'block' ) {
    session_start();
    $objResponse = new xajaxResponse();
    
    if ( hasPermissions('payments') ) {
        $aUsers = _jsonArray( $sUsers );
        
        if ( $aUsers ) {
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
            $bBlock  = ( $sAction == 'block' );
            $sTitle  = ( $bBlock ) ? 'Разблокировать деньги' : 'Заблокировать деньги'; 
            $sAction = ( $bBlock ) ? 'unblock' : 'block';
            
        	foreach ($aUsers as $sUid) {
        		account::setBlockMoney( $sUid, $bBlock );
        		$objResponse->assign( "money_$sUid", 'innerHTML', '<a onclick="if (confirm(\'Вы уверены, что хотите '. mb_strtolower($sTitle).'?\')) xajax_updateMoneyBlock(JSON.encode(['.$sUid.']),\''.$sAction.'\')" href="javascript:void(0);">'.$sTitle.'</a>' );
        	}
        	
        	$objResponse->script( 'adminLogCheckUsers(false)' );
        	$objResponse->script( '$("chk_all").checked=false;' );
        }
    }
    
    return $objResponse;
}

/**
 * Активирует пользователей
 * 
 * @param  string $sUsers JSON строка с массивом UID пользователей
 * @param  int $nReload 1 - если нужно перезагрузить страницу
 * @return object xajaxResponse
 */
function activateUser( $sUsers = '', $nReload = 0 ) {
    session_start();
    $objResponse = new xajaxResponse();
    
    if ( hasPermissions('users') ) {
        $aUsers = _jsonArray( $sUsers );
        
        if ( $aUsers ) {
            require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/users.php');
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/wizard/wizard_registration.php");
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/wizard/step_employer.php");
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/wizard/step_freelancer.php");
            
            foreach ($aUsers as $sUid) {
                if ( users::SetActiveByUid($sUid) ) {
                    $user = new users();
                    $user->GetUserByUID($sUid);
                    if($user->role[0] == 1) {
                        $wiz_user = wizard::isUserWizard($sUid, step_employer::STEP_REGISTRATION_CONFIRM, wizard_registration::REG_EMP_ID);
                    } else {
                        $wiz_user = wizard::isUserWizard($sUid, step_freelancer::STEP_REGISTRATION_CONFIRM, wizard_registration::REG_FRL_ID);
                    }
                    step_wizard::setStatusStepAdmin(step_wizard::STATUS_COMPLITED, $sUid, $wiz_user['id']);
                	$objResponse->script("$('activate_$sUid').set('html','');");
                }
            }
            
            $objResponse->script( 'adminLogCheckUsers(false)' );
        	$objResponse->script( '$("chk_all").checked=false;' );
        }
        
        if ( $nReload ) {
        	$objResponse->script( 'window.location.reload(true)' );
        }
    }
    
    return $objResponse;
}

/**
 * Изменить данные привязки аккаунта к телефону
 * 
 * @param  int $sUid UID пользователя
 * @param  string $sPhone телефон
 * @param  string $sPhoneOnly отправлять восстановление пароля только на телефон - 't' или 'f'
 * @param  string $sSafetyMob Входить в финансы только по СМС - 't' или 'f'
 * @return object xajaxResponse
 */
function updateSafetyPhone( $sUid = 0, $sPhone = '', $sPhoneOnly = 'f', $sSafetyMob = 'f' ) {
    session_start();
    $objResponse = new xajaxResponse();
    
    if ( hasPermissions('users') ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/sbr.php' );
        $sPhone = "+" . str_replace("+", "", $sPhone);
        $reqv  = sbr_meta::getUserReqvs($sUid);
        if($reqv[$reqv['form_type']]['mob_phone'] != $sPhone) {
            $nreqv['mob_phone'] = $sPhone;
            $cnt = 0;
            $filter = array(
                'search_phone_exact' => true,
                'search_phone'       => $nreqv['mob_phone']
            );
            sbr_meta::searchUsersPhone($cnt, $filter);
            if($cnt > 0) {
                $res = "Телефон {$sPhone} уже зарегистрирован в системе.";
                $objResponse->assign( "safety_phone$sUid", 'value', $reqv[$reqv['form_type']]['mob_phone'] );
            } else {
            	sbr_meta::$reqv_fields[$reqv['form_type']]['mob_phone']['maxlength'] = 15;
                $error = sbr_meta::setUserReqv($sUid, $reqv['rez_type'], $reqv['form_type'], $nreqv);
            }
        }
        $res   = users::ChangeSafetyPhone( $sUid, $sPhone, $sPhoneOnly );
        $error = sbr_meta::safetyMobPhone($sUid, $sSafetyMob);
        if ( $res) {
            $objResponse->alert($res);
            $objResponse->script( "$('safety_phone_show$sUid').setStyle('display', '');" );
        } else {
            $sChecked = ( $sPhoneOnly == 't' ) ? 'true' : 'false';
            $sDisplay = ( $sPhoneOnly == 't' ) ? ''     : 'none';
            $sSafetyMobDisplay = ( $sSafetyMob == 't' ) ? '' : 'none';
            $objResponse->assign( "safety_phone_value$sUid", 'innerHTML', $sPhone );
            $objResponse->assign( "safety_phone_hidden$sUid", 'value', $sPhone );
            $objResponse->script( "$('safety_only_phone_show$sUid').setStyle('display', '$sDisplay');" );
            $objResponse->script( "$('is_safety_mob_show{$sUid}').setStyle('display', '$sSafetyMobDisplay');" );
            
            $sDisplay = ( trim($sPhone) ) ? '' : 'none';
            $objResponse->script( "$('safety_phone_show$sUid').setStyle('display', '$sDisplay');" );
        }
        
        $objResponse->script( "$('safety_phone_edit$sUid').setStyle('display', 'none');" );
    }
    
    return $objResponse;
}

/**
 * Изменить данные привязки аккаунта к IP
 * 
 * @param  int $sUid UID пользователя
 * @param  string $sIp IP через запятую, дефис или слеш например 10.10.10.1, 10.10.10.5 – 10.10.10.10 или 10.10.10.0/24
 * @return object xajaxResponse
 */
function updateSafetyIp( $sUid = 0, $sIp = '' ) {
    session_start();
    $objResponse = new xajaxResponse();
    
    if ( hasPermissions('users') ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
        
        $res = users::ChangeSafetyIP( $sUid, $sIp );
        
        if ( $res['error_flag'] ) {
            $objResponse->alert($res['alert']);
            $objResponse->script( "$('safety_ip_show$sUid').setStyle('display', '');" );
        }
        else {
            $sDisplay = ( trim($sIp) ) ? '' : 'none';
            $objResponse->assign( "safety_ip_value$sUid", 'innerHTML', $sIp );
            $objResponse->script( "$('safety_ip_show$sUid').setStyle('display', '$sDisplay');" );
        }
        
        $objResponse->script( "$('safety_ip_edit$sUid').setStyle('display', 'none');" );
    }
        
    return $objResponse;
}

/**
 * Изменить Email пользователя
 * 
 * @param  int $sUid UID пользователя
 * @param  string $sEmail новый Email пользователя
 * @return object xajaxResponse
 */
function updateEmail( $sUid = 0, $sEmail = '' ) {
    session_start();
    $objResponse = new xajaxResponse();
    
    if ( hasPermissions('users') ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
        
        $res = users::ChangeMail( trim($sUid), trim($sEmail) );
        
        if ( $res ) {
            $objResponse->alert( $res );
        }
        else {
            $sDisplay = ( trim($sEmail) ) ? '' : 'none';
            $objResponse->assign( "email_value$sUid", 'innerHTML', $sEmail );
        }
        
        $objResponse->script( "$('email_show$sUid').setStyle('display', '');" );
        $objResponse->script( "$('email_edit$sUid').setStyle('display', 'none');" );
    }
    
    return $objResponse;
}

/**
 * Изменить отношение пользователей
 * 
 * @param  int $sUid UID пользователя
 * @param  int $nValue новое значение отношения пользователей
 * @return object xajaxResponse
 */
function updatePop( $sUid = 0, $nValue = 0 ) {
    session_start();
    $objResponse = new xajaxResponse();
    
    if ( hasPermissions('users') ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
        
        $mRes       = null;
        $oUser      = new users();
        $nValue     = intval( $nValue );
        $oUser->pop = $nValue;
        $sError     = $oUser->Update( $sUid, $mRes );
        
        if ( $sError ) {
            $objResponse->alert( $sError );
        }
        else {
            $sClass = $nValue < 0  ? 'b-voting__link_dot_red' : 'b-voting__link_dot_green';
            $sPop   = $nValue != 0 ? $nValue : '0';
            
            $objResponse->assign( "pop$sUid", 'innerHTML', $sPop );
            $objResponse->assign( "pop_input_$sUid", 'value', $sPop );
            $objResponse->script( "\$('pop$sUid').removeClass('b-voting__link_dot_red').removeClass('b-voting__link_dot_green').addClass('$sClass')" );
        }
        
        $objResponse->script( "$('pop_show$sUid').setStyle('display', '');" );
        $objResponse->script( "$('pop_edit$sUid').setStyle('display', 'none');" );
    }
    
    return $objResponse;
}

/**
 * Отключить все уведомления
 * 
 * @param  int $uid UID пользователя
 * @return object xajaxResponse
 */
function stopNotifications( $uid = 0, $role = 'flr' ) {
    session_start();
    $objResponse = new xajaxResponse();
    
    if ( hasPermissions('users') ) {
        $sClass = $role == 'flr' ? 'freelancer' : 'employer';
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/' . $sClass . '.php' );
        
        $users = new $sClass();
        $users->subscr = str_repeat( '0', $GLOBALS['subscrsize'] );
        
        if ( $role == 'flr' ) {
            $users->mailer     = 0;
            $users->mailer_str = '';
        }
        
        $sError = $users->Update( $uid, $res );
        commune::clearSubscription($uid);
        
        if ( empty($sError) ) {
            $objResponse->alert( 'Уведомления отключены' );
        }
        else {
            $objResponse->alert( 'Ошибка сохранения данных' );
        }
    }
    
    return $objResponse;
}

function saveExcDate($date, $type) {
    session_start();
    $objResponse = new xajaxResponse();
    if ( !hasPermissions('admin') ) {
        return $objResponse;
    }
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/LocalDateTime.php' );
    $year = substr($date, 0, 4);
    $odate = new LocalDateTime();
    $edate = $odate->getExcDaysInit($year, false, false);
    // Новый год
    if(!$edate) {
        $edate['year'] = $year;
        switch($type) {
            case 1:
                $edate['holidays'] = $date;
                break;
            case 2:
                $edate['workdays'] = $date;
                break;
        }
        $odate->updateExcDays($edate, 'insert');
        return $objResponse;
    }
    $edit_date = $edate;
    
    $hdate = (strpos($edate['holidays'], $date) != 0 ? ",{$date}" : $date );
    $wdate = (strpos($edate['workdays'], $date) != 0 ? ",{$date}" : $date );
            
    switch($type) {
        case 0:
            $edit_date['holidays'] = str_replace($hdate, '', $edate['holidays']); // Удаляем дату
            $edit_date['workdays'] = str_replace($wdate, '', $edate['workdays']); // Удаляем дату
            break;
        case 1:
            $edit_date['workdays'] = str_replace($wdate, '', $edate['workdays']); // Удаляем дату
            $edit_date['holidays'] .= ($edate['holidays'] == '' ? '' : ',') . $date; 
            break;
        case 2:
            $edit_date['holidays'] = str_replace($hdate, '', $edate['holidays']); // Удаляем дату
            $edit_date['workdays'] .= ($edate['workdays'] == '' ? '' : ',') . $date; 
            break;
    }
    
    $edit_date['holidays'] = $edit_date['holidays'] != '' ? implode(",", $odate->initCollectionDate( $edit_date['holidays'])) : "";
    $edit_date['workdays'] = $edit_date['workdays'] != '' ? implode(",", $odate->initCollectionDate( $edit_date['workdays'])) : "";
    
    $is_changed = false;
    if($edate['holidays'] != $edit_date['holidays']) {
        $edate['holidays'] = $edit_date['holidays'];
        $is_changed = true;
    }
    
    if($edate['workdays'] != $edit_date['workdays']) {
        $edate['workdays'] = $edit_date['workdays'];
        $is_changed = true;
    }
    
    if($is_changed) {
        $odate->updateExcDays($edate);
    }
    return $objResponse;
}

function getLoadExcDate($year) {
    session_start();
    $objResponse = new xajaxResponse();
    if ( !hasPermissions('admin') ) {
        return $objResponse;
    }
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/LocalDateTime.php' );
    
    $odate = new LocalDateTime();
    $edate = $odate->getExcDaysInit($year, false, false);
    
    $resp['success'] = true;
    $resp['holidays'] = iconv("windows-1251", "UTF-8", $edate['holidays']);
    $resp['workdays'] = iconv("windows-1251", "UTF-8", $edate['workdays']);
    echo json_encode( $resp );
}

/**
 * Изменить выделение жирным причины действя админа.
 * 
 * @param  int $id ID причины действя админа.
 * @param  string $is_bold выделять жирным t/f
 * @return obj xajaxResponse
 */
function setReasonBold( $sId = 0, $sBold = 'f' ) {
    session_start();
    $objResponse = new xajaxResponse();
    
    if ( !hasPermissions('adm') ) {
        return $objResponse;
    }
    
    admin_log::setReasonBold( $sId, $sBold == 't' ? $sBold : 'f' );
    $objResponse->script( "$('is_bold_$sId').set( 'disabled', false );" );
    return $objResponse;
}


/**
 * Отключаем/включаем верификацию пользователям
 * 
 * @param integer $uid     ИД пользователя
 * @param boolean $type    вкючить/выключить
 * @return \xajaxResponse
 */
function setVerification( $uid = 0, $type = false ) {
    session_start();
    $objResponse = new xajaxResponse();
    
    if ( hasPermissions('users') ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
        
        $users = new users();
        $users->is_verify = $type;
        
        $sError = $users->Update( $uid, $res );
        if ($type == false) {
        	require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/Verification.php' );
            Verification::decrementStat($uid);
        }
        
        if ( empty($sError) ) {
            $text = $type ? 'Снять верификацию' : 'Дать верификацию';
            $html = '<a href="javascript:void(0);" onclick="user_search.setVerification(' . $uid . ', ' . ( $type ? 'false' : 'true' ) . ');" class="lnk-dot-666" title="' . $text . '"><b>' . $text . '</b></a>';
            $objResponse->assign("verify{$uid}", 'innerHTML', $html);
            if($type) {
                $objResponse->script("$$('#user{$uid} a.user-name').grab(new Element('span', {class:'b-icon b-icon__ver b-icon_valign_middle'}), 'before')");
            } else {
                $objResponse->script("$$('#user{$uid} .b-icon__ver').dispose();");
            }
            $objResponse->alert( $type ? 'Верификация дана' : 'Верификация снята' );
        } else {
            $objResponse->alert( 'Ошибка сохранения данных' );
        }
    }
    
    return $objResponse;
}

$xajax->processRequest();
