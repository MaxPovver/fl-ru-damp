<?php
require_once( $_SERVER['DOCUMENT_ROOT'] . '/xajax/user_content.common.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stdf.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/permissions.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/template.php');

session_start();

$aPermissions = permissions::getUserPermissions( $_SESSION['uid'] );
$user_content = new user_content( $_SESSION['uid'], $aPermissions );
$stop_words   = new stop_words( true );
$sTeam        = view_team_fl().'&nbsp;';

/**
 * Массовое утверждение записей
 * 
 * @param  int $content_id идентификатор сущности из admin_contents
 * @param  string $stream_id идентификатор потока
 * @param  string $sid JSON строка с массивом идентификаторов записей
 * @param  string $user_id JSON строка с массивом UID авторов записей
 * @param  int $content_cnt количество записей в потоке
 * @param  int $status статус сущностей: 0 - для модерирования, 1 - утвержденные, 2 - удаленные
 * @param  string $is_sent JSON строка с массивом флагов было ли отправлено уведомление
 * @return object xajaxResponse
 */
function massApproveContent( $content_id = 0, $stream_id = '', $sid = '', $user_id = '', $content_cnt = 0, $status = 0, $is_sent = '' ) {
    global $user_content;
    
    $objResponse = new xajaxResponse();
    
    $objResponse->script('user_content.spinner.hide(true);');
    
    if ( $user_content->hasContentPermissions($content_id) && $status != 1 ) {
        $aStream     = array();
        $checkStream = $user_content->checkStream( $content_id, $stream_id, $_SESSION['uid'], $aStream );
        
        if ( $checkStream ) {
            $aSid  = _jsonArray( $sid );
            $aUid  = _jsonArray( $user_id );
            $aSend = _jsonArray( $is_sent );
            $nSid  = count($aSid);
            
            if ( is_array($aSid) && $nSid && is_array($aUid) && count($aUid) 
                && is_array($aSend) && count($aSend) && $nSid == count($aUid) && $nSid == count($aSend) 
            ) { 
                for ( $i = 0; $i < $nSid; $i++ ) {
                    list( $rec_content_id, $rec_id, $rec_type ) = explode( '_', $aSid[$i] );
                    $user_content->resolveContent( $stream_id, $_SESSION['uid'], $aUid[$i], $rec_content_id, $rec_id, $rec_type, 1, $aSend[$i] );
                    
                    $content_cnt--;
                    $objResponse->script( "$('my_div_content_{$sid}').destroy();" );
                }
                
                $user_content->checkStream( $content_id, $stream_id, $_SESSION['uid'], $aStream, $nSid );
                
                if ( !$status ) {
                    $nLimit = user_content::CONTENTS_PER_PAGE - $content_cnt;

                    if ( $content_id != user_content::MODER_MSSAGES ) {
                        $user_content->chooseContent( $content_id, $stream_id, $aStream['stream_num'], $nLimit );
                    }
                    elseif ( !$status && $content_cnt < user_content::MESSAGES_PER_PAGE ) {
                        $user_content->chooseContent( 1, $stream_id, $aStream['stream_num'], user_content::MESSAGES_PER_PAGE );
                    }
                    
                    $nLimit = $content_id == user_content::MODER_MSSAGES ? user_content::MESSAGES_PER_PAGE : user_content::CONTENTS_PER_PAGE;
                    
                    _parseContents( $objResponse, $aStream, $content_id, $user_content->getContent($content_id, $stream_id, 0, 0, $nLimit) );
                }
                
                $objResponse->script( "parent.$('span_num_{$stream_id}').set('html', '#{$aStream['title_num']}')" );
            }
            
            $objResponse->script( "parent.$('check_{$stream_id}').set('checked', false)" );
            $objResponse->script( "$(user_content.scrollWindow).scrollTo(0, user_content.scrollPosition);" );
        }
        else {
            _loseStream( $objResponse, $stream_id );
        }
    }
    else {
        _parsePermissionsDenied( $objResponse );
    }
    
    $objResponse->script('user_content.spinner.resize();');
    
    return $objResponse;
}

/**
 * Удаление записи с предупреждением или баном
 * 
 * @param  int $content_id идентификатор сущности из admin_contents
 * @param  string $stream_id идентификатор потока
 * @param  int $sid идентификатор записи
 * @param  int $action действие: 1 - утвердить, 2 - удалить, 3 - удалить и предупредить, 4 - удалить и забанить
 * @param  int $user_id UID автора записи
 * @return object xajaxResponse
 */
function resolveAndBan( $content_id = 0, $stream_id = '', $sid = 0, $action = 1, $user_id = 0 ) {
    global $user_content;
    
    $objResponse = new xajaxResponse();
    
    if ( $user_content->hasContentPermissions($content_id) ) {
        $aStream     = array();
        $checkStream = $user_content->checkStream( $content_id, $stream_id, $_SESSION['uid'], $aStream );
        
        if ( $checkStream ) {
            if ( $action == 2 ) { // !!! мне кажется сюда мы не должны попадать
                list( $content_id, $rec_id, $rec_type ) = explode( '_', $sid );
                $bResolve = $user_content->checkContent( $content_id, $stream_id, $rec_id );
                    if ( $bResolve ) {
                        $objResponse->script("user_content.addContext('$sid');");

                        $objResponse->script( "banned.delReason({$user_id}, 'moder', '{$content_id}-{$rec_type}');" );
                    }
                    else {
                        $objResponse->alert( "Пользователь удалил или изменил данные.\nЛибо запись заблокирована." );
                        $objResponse->script('user_content.getContents();');
                    }
            }
            if ( $action == 3 || $action == 4 ) {
                $bResolve = true;
                list( $content_id, $rec_id, $rec_type ) = explode( '_', $sid );
                
                if ( $user_id == $_SESSION['uid'] ) {
                    $objResponse->alert('Вы не можете предупредить или забанить самого себя');
                    $bResolve = false;
                }

                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
                $oUsers = new users();
                $oUsers->GetUserByUID($user_id);
                if ( $action == 3 && $oUsers->warn > 2 && $bResolve ) {
                    $objResponse->alert('У пользователя уже есть 3 предупреждения');
                    $bResolve = false;
                }

                if ( $action == 4 && ($oUsers->is_banned || $oUsers->ban_where) && $bResolve ) {
                    $objResponse->alert('Пользователь уже забанен');
                    $bResolve = false;
                }
                
                if ( $bResolve ) {
                    $bResolve = $user_content->checkContent( $content_id, $stream_id, $rec_id );
                    
                    if ( $bResolve ) {
                        $objResponse->script("user_content.addContext('$sid');");

                        if ( $bResolve && $action == 3 ) {
                            $objResponse->script( "parent.banned.warnUser({$user_id}, 0, '', 'moder', 0, '{$stream_id}-{$content_id}-{$rec_id}-{$rec_type}');" );
                        }

                        if ( $bResolve && $action == 4 ) {
                            $objResponse->script( "parent.banned.userBan({$user_id}, 'moder', 0, '{$stream_id}-{$content_id}-{$rec_id}-{$rec_type}');" );
                        }
                    }
                    else {
                        $objResponse->alert( "Пользователь удалил или изменил данные.\nЛибо запись заблокирована." );
                        $objResponse->script('user_content.getContents();');
                    }
                }
            }
        }
        else {
            _loseStream( $objResponse, $stream_id );
        }
    }
    else {
        _parsePermissionsDenied( $objResponse );
    }
    
    return $objResponse;
}
        

/**
 * Утверждение/удаление записи
 * 
 * @param  int $content_id идентификатор сущности из admin_contents
 * @param  string $stream_id идентификатор потока
 * @param  int $sid идентификатор записи
 * @param  int $action действие: 1 - утвердить, 2 - удалить, 3 - удалить и предупредить, 4 - удалить и забанить
 * @param  int $user_id UID автора записи
 * @param  int $content_cnt количество записей в потоке
 * @param  int $status статус сущностей: 0 - для модерирования, 1 - утвержденные, 2 - удаленные
 * @param  string $is_sent было ли отправлено уведомление
 * @param  object ссылка на $objResponse опционально
 * @return object xajaxResponse
 */
function resolveContent( $content_id = 0, $stream_id = '', $sid = 0, $action = 1, $user_id = 0, $content_cnt = 0, $status = 0, $is_sent = '', $reason = '', &$objResponse = null ) {
    global $user_content;
    
    if ( !$objResponse ) {
        $objResponse = new xajaxResponse();
    }
    
    $objResponse->script('user_content.spinner.hide(true);');
    
    if ( $user_content->hasContentPermissions($content_id) ) {
        $aStream     = array();
        $checkStream = $user_content->checkStream( $content_id, $stream_id, $_SESSION['uid'], $aStream, 1 );
        
        if ( $checkStream ) {
            if ( $action > 0 && $action < 3 ) {
                list( $rec_content_id, $rec_id, $rec_type ) = explode( '_', $sid );
                
                if ( strpos($reason, '%USERNAME%') !== false && $user_id ) {
                    $user = new users;
                    $user->GetUserByUID($user_id);
                    $reason = str_replace('%USERNAME%', $user->uname . ' ' .$user->usurname, $reason);
                }
                
                if ( $user_content->resolveContent($stream_id, $_SESSION['uid'], $user_id, $rec_content_id, $rec_id, $rec_type, $action, $is_sent, $reason) ) {
                    $user_content->sendNotification( $_SESSION['uid'], $user_id, $rec_content_id, $rec_id, $rec_type, $action, $reason );
                }
                else {
                    $objResponse->alert( "Пользователь удалил или изменил данные.\nЛибо запись заблокирована." );
                }

                if ( $status < 2 || $action == 1 ) {
                    $content_cnt--;
                    $objResponse->script( "$('my_div_content_{$sid}').destroy();" );
                }

                if ( !$status ) {
                    $nLimit = user_content::CONTENTS_PER_PAGE - $content_cnt;

                    if ( $content_id != user_content::MODER_MSSAGES ) {
                        $user_content->chooseContent( $content_id, $stream_id, $aStream['stream_num'], $nLimit );
                    }
                    
                    $nLimit = $content_id == user_content::MODER_MSSAGES ? user_content::MESSAGES_PER_PAGE : user_content::CONTENTS_PER_PAGE;
                    
                    _parseContents( $objResponse, $aStream, $content_id, $user_content->getContent($content_id, $stream_id, 0, 0, $nLimit) );
                }
                
                $objResponse->script( "parent.$('span_num_{$stream_id}').set('html', '#{$aStream['title_num']}')" );
            }
        }
        else {
            _loseStream( $objResponse, $stream_id );
        }
    }
    else {
        _parsePermissionsDenied( $objResponse );
    }
    
    $objResponse->script('user_content.spinner.resize();');
    
    return $objResponse;
}

/**
 * Возвращает сущности для модерирования
 * 
 * @param  int $content_id идентификатор сущности из admin_contents
 * @param  string $stream_id идентификатор потока
 * @param  int $status статус сущностей: 0 - для модерирования, 1 - утвержденные, 2 - удаленные
 * @param  int $last_id для статус = 1, 2 - последний полученный ID
 * @param  int $content_cnt количество записей в потоке
 * @return object xajaxResponse
 */
function getContents( $content_id = 0, $stream_id = '', $status = 0, $last_id = 0, $content_cnt = 0 ) {    
    global $user_content;
    
    $objResponse = new xajaxResponse();
    
    $objResponse->script('user_content.spinner.hide(true);');
    
    if ( $user_content->hasContentPermissions($content_id) ) {
        $aStream     = array();
        $checkStream = $user_content->checkStream( $content_id, $stream_id, $_SESSION['uid'], $aStream );
        
        if ( $checkStream ) {
            $nLimit = $status ? user_content::TWITTER_PER_PAGE : ($content_id == user_content::MODER_MSSAGES ? user_content::MESSAGES_PER_PAGE : user_content::CONTENTS_PER_PAGE);
            
            $aContents = $user_content->getContent( $content_id, $stream_id, $status, $last_id, $nLimit );
            _parseContents( $objResponse, $aStream, $content_id, $aContents, $status, $content_cnt );
            $objResponse->script( "parent.$('span_num_{$stream_id}').set('html', '#{$aStream['title_num']}')" );
        }
        else {
            _loseStream( $objResponse, $stream_id );
        }
    }
    else {
        _parsePermissionsDenied( $objResponse );
    }
    
    $objResponse->script('user_content.spinner.resize();');
    
    return $objResponse;
}

/**
 * Возвращает заблокированые 
 * 
 * @global object $user_content user_content
 * @param  int $content_id идентификатор сущности из admin_contents
 * @param  string $login фильтр по логину, или пустая строка
 * @param  string $login_ex точное совпадениелогина, или пустая строка
 * @param  string $from фильтр по дате - начальная дата
 * @param  string $to фильтр по дате - конечная дата
 * @param  int $last_id последний полученный ID
 * @return object xajaxResponse
 */
function getBlocked( $content_id = 0, $login = '', $login_ex = '', $from = '', $to = '', $last_id = 2147483647 ) {
    global $user_content, $stop_words;
    
    $objResponse = new xajaxResponse();
    
    $objResponse->script('user_content.spinner.hide(true);');
    
    if ( $user_content->hasContentPermissions($content_id) ) {
        $sReturn = '';
        $nLimit  = user_content::MESSAGES_PER_PAGE;
        $aFilter = array(
            'login'     => $login    ? $login    : '',
            'login_ex'  => $login_ex ? $login_ex : '',
            'date_from' => $from     ? $from     : date('Y-m-d'),
            'date_to'   => $to       ? $to       : date('Y-m-d')
        );
        
        switch ( $content_id ) {
            case user_content::MODER_MSSAGES:     $aContents = $user_content->getBlockedMessages( $aFilter, $last_id, $nLimit ); break;
            case user_content::MODER_BLOGS:       $aContents = $user_content->getBlockedBlogs( $aFilter, $last_id, $nLimit ); break;
            case user_content::MODER_COMMUNITY:   $aContents = $user_content->getBlockedCommunity( $aFilter, $last_id, $nLimit ); break;
            case user_content::MODER_PROJECTS:    $aContents = $user_content->getBlockedProjects( $aFilter, $last_id, $nLimit ); break;
            case user_content::MODER_PRJ_OFFERS:  $aContents = $user_content->getBlockedPrjOffers( $aFilter, $last_id, $nLimit ); break;
            case user_content::MODER_ART_COM:     $aContents = $user_content->getBlockedArtCom( $aFilter, $last_id, $nLimit ); break;
            case user_content::MODER_PRJ_DIALOG:  $aContents = $user_content->getBlockedPrjDialog( $aFilter, $last_id, $nLimit ); break;
            case user_content::MODER_CONTEST_COM: $aContents = $user_content->getBlockedContestCom( $aFilter, $last_id, $nLimit ); break;
            case user_content::MODER_PORTFOLIO:   $aContents = $user_content->getBlockedPortfolio( $aFilter, $last_id, $nLimit ); break;
            case user_content::MODER_SDELAU:      $aContents = $user_content->getBlockedSdelau( $aFilter, $last_id, $nLimit ); break;
            default:                              $aContents = array(); break;
        }
        
        if ( is_array($aContents) && count($aContents) ) {
            switch ( $content_id ) {
                case user_content::MODER_MSSAGES:
                    foreach ( $aContents as $aOne ) {
                        $nLastId = $aOne['id'];
                        $sAttach = '';

                        if ( $aOne['files'] ) {
                            $nn = 1;

                            foreach ( $aOne['files'] as $attach )
                            {
                                $aData = getAttachDisplayData( $aOne['f_user']['login'], $attach['fname'], 'contacts', 1000, 300, 307200, 0 );

                                if ( $aData && $aData['success'] ) {
                                    if ( $aData['file_mode'] || $aData['virus_flag'] || $att_ext == "swf" ) {
                                        $sAttach .= _parseAttach( $aData );
                                    }
                                    else {
                                        $sAttach .= "<div class=\"b-fon__body b-fon__body_pad_5 b-fon__body_bg_ffebbf b-fon__body_margbot_1\"><img src=\"".WDCPREFIX.'/users/'.$aOne['f_user']['login'].'/contacts/'.$aData['file_name']."\" alt=\"{$aData['file_name']}\" title=\"{$aData['file_name']}\" width=\"{$aData['img_width']}\" height=\"{$aData['img_height']}\" /></div>";
                                    }

                                    $nn++;
                                }
                            }
                            
                            $sAttach = _wrapAttach( $sAttach );
                        }
                        
                        $sUserClass = is_emp($aOne['f_user']['role']) ? '6db335' : 'fd6c30';
                        
                        $sMessage = $stop_words->replace($aOne['msg_text']);
                        $sMessage = reformat( $sMessage, 50, 0, -($aOne['f_user']['is_chuck']=='t'), 1 );
                        $sOnClick = "window.open('/siteadmin/user_content/?site=blocked&mode=letters&fid={$aOne['from_id']}&tid={$aOne['to_id']}', '_blank');";
                        $sReturn .= '
<div class="b-post b-post_bordtop_dfe3e4 b-post_padtop_15 b-post_marg_20_10" id="my_div_content_'. $aOne['id'] .'">
    '. _parsePostTime( 20, $aOne['post_time'] ) .'
    <div class="b-username b-username_padbot_5"><a class="b-username__link b-username__link_color_'. $sUserClass .' b-username__link_fontsize_11 b-username__link_bold" href="/users/'. $aOne['f_user']['login'] .'" target="_blank">'. $aOne['f_user']['uname'] .' '. $aOne['f_user']['usurname'] .' ['. $aOne['f_user']['login'] .']</a></div>
    <div class="b-username b-username_bold b-username_padbot_10">Кому: <a class="b-username__link b-username__link_color_000  b-username__link_bold" href="/users/'. $aOne['t_user']['login'] .'" target="_blank">'. $aOne['t_user']['uname'] .' '. $aOne['t_user']['usurname'] .' ['. $aOne['t_user']['login'] .']</a></div>
    <div class="b-post__txt b-post__txt_fontsize_15">'. $sMessage .'</div>
    '. $sAttach .'
    <div class="b-fon b-fon_padtop_20">
        <div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_bg_f5" style="height:23px;">
            '. _parseEditIcon( 'admEditContacts', $aOne['id'], 20, '0', "{'from_id': {$aOne['f_user']['uid']}, 'is_sent': '{$aOne['is_sent']}'}" ) .'
            <a onclick="user_content.approveLetter('. $aOne['id'] .', '. $aOne['from_id'] .')" class="b-button b-button_mini b-button_margleft_10 b-button_float_right" href="javascript:void(0);"><span class="b-button__icon b-button__icon_ok"></span></a>
            <a class="b-button b-button_mini b-button_margleft_10 b-button_float_right" href="#" onclick="'. $sOnClick .'" title="Переписка"><span class="b-button__icon b-button__icon_com"></span></a>
        </div>
    </div>
</div>';
                    }
                    break;
                case user_content::MODER_BLOGS:        $sReturn = _parseBlogs( $nLastId, $aContents, 20 ); break;
                case user_content::MODER_COMMUNITY:    $sReturn = _parseCommunity( $nLastId, $aContents, 20 ); break;
                case user_content::MODER_PROJECTS:     $sReturn = _parseProjects( $nLastId, $aContents, 20 ); break;
                case user_content::MODER_PRJ_OFFERS:   $sReturn = _parseProjectsOffers( $nLastId, $aContents, 20 ); break;
                case user_content::MODER_ART_COM:      $sReturn = _parseArticleComments( $nLastId, $aContents, 20 ); break;
                case user_content::MODER_PROFILE:      $sReturn = _parseProfile( $nLastId, $aContents, 20 ); break;
                case user_content::MODER_PRJ_DIALOG:   $sReturn = _parseProjectsDialog( $nLastId, $aContents, 20 ); break;
                case user_content::MODER_CONTEST_COM:  $sReturn = _parseContestComments( $nLastId, $aContents, 20 ); break;
                case user_content::MODER_PORTF_CHOISE: $sReturn = _parsePortfChoice( $nLastId, $aContents, 20 ); break;
                case user_content::MODER_PORTFOLIO:    $sReturn = _parsePortfolio( $nLastId, $aContents, 20 ); break;
                case user_content::MODER_SDELAU:       $sReturn = _parseSdelau( $nLastId, $aContents, 20 ); break;
            }
            
            $objResponse->append( 'my_div_contents', 'innerHTML', $sReturn );
            $objResponse->script( "user_content.lastID = '$nLastId';" );
            $objResponse->script( "user_content.afterScroll();" );
        }
        elseif ( $last_id == 2147483647 ) {
            $objResponse->append( 'my_div_contents', 'innerHTML', '<div class="b-post b-post_padtop_15">Нет заблокированных записей, удовлетворяющих условиям выборки</div>' );
        }
    }
    else {
        _parsePermissionsDenied( $objResponse );
    }
    
    $objResponse->script( '$("my_div_wait").destroy();' );
    $objResponse->script('user_content.spinner.resize();');
    
    return $objResponse;
}

/**
 * Разблокировака сущностей
 * 
 * @param  int $sid идентификатор записи
 * @param  int $from_id идентификатор пользователя
 * @return object xajaxResponse
 * @param  string $is_sent было ли отправлено уведомление
 */
function unblock( $sid = '', $from_id = 0, $is_sent = '' ) {
    global $user_content;

    $objResponse = new xajaxResponse();
    list( $content_id, $rec_id, $rec_type ) = explode( '_', $sid );
    
    if ( $user_content->hasContentPermissions($content_id) ) {
        $objResponse->script('user_content.spinner.hide(true);');
        $user_content->unblock( $content_id, $from_id, $rec_id, $rec_type, $is_sent );
        $objResponse->script( "$('my_div_content_{$sid}').destroy();" );
        $objResponse->script('user_content.spinner.resize();');
    }
    else {
        _parsePermissionsDenied( $objResponse );
    }
    
    return $objResponse;
}

/**
 * Возвращает диалог при чтении переписки в заблокированных личных сообщениях
 * 
 * @param  int $nFromId uid отправиля (то есть одного)
 * @param  int $nToId uid получателя (то есть другого)
 * @param  int $nCurpage номер страницы
 * @return object xajaxResponse
 */
function getBlockedLetters( $nFromId = 0, $nToId = 0, $nCurpage = 1 ) {
    global $user_content, $stop_words;
    
    $objResponse = new xajaxResponse();
    
    $objResponse->script('user_content.spinner.hide(true);');
    
    if ( hasPermissions('users') ) {
        require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );

        $aDialog = array();
        $nDialog = 0;
        $sDialog = '';

        $oFromUser = new users();
        $oFromUser->GetUserByUID( $nFromId );

        $oToUser = new users();
        $oToUser->GetUserByUID( $nToId );

        if ( $oFromUser->login && $oToUser->login ) {
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/messages.php' );

            $oMsgs    = new messages();
            $aDialog  = $oMsgs->GetMessagesForModers( $nFromId, $oToUser->login, $nCurpage, 20 );

            if ( $aDialog ) {
                foreach ( $aDialog as $aOne ) {
                    $sClass   = $aOne['from_id'] == $nFromId ? 'b-post__txt_color_a7a7a6' : '';
                    $sLogin   = $aOne['from_id'] == $nFromId ? $oFromUser->login    : $oToUser->login;
                    $sName    = $aOne['from_id'] == $nFromId ? $oFromUser->uname    : $oToUser->uname;
                    $sSurname = $aOne['from_id'] == $nFromId ? $oFromUser->usurname : $oToUser->usurname;
                    $sIsChuck = $aOne['from_id'] == $nFromId ? $oFromUser->is_chuck : $oToUser->is_chuck;
                    $sAttach  = '';
                    $sAction  = $aOne['deleted'] ? 'user_content.updateLetter('. $aOne['id'] . ', '. $aOne['from_id'] .', 1)' : 'parent.banned.delReason(\'1_'. $aOne['id'] .'_0\', '. $aOne['from_id'] .', \'updateLetter\', {})';
                    $sIcon    = $aOne['deleted'] ? 'ok' : 'del';

                    if ( $aOne['files'] ) {
                        $nn = 1;

                        foreach ( $aOne['files'] as $attach )
                        {
                            $str = viewattachLeft( $sLogin, $attach["fname"], 'contacts', $file, 0, 0, 0, 0, 0, 0, $nn );
                            $sAttach .= '<div class = "flw_offer_attach">' . $str . '</div>';
                            $nn++;
                        }

                        $sAttach = '<div class="b-icon-layout b-icon-layout_padtop_5">'. $sAttach .'</div>';
                    }

                    $msg_text = $stop_words->replace($aOne['msg_text']);
                    $msg_text = reformat( $msg_text, 50, 0, -($sIsChuck=='t'), 1 );
                    $sDeleted = $aOne['deleted'] ? ' [Сообщение удалено модератором]' : '';
                    $sDialog .= '
<div class="b-post b-post_bordtop_dfe3e4 b-post_padtop_15 b-post_marg_20_10" id="my_div_content_'. $aOne['id'] .'">
    <span id="my_action_'. $aOne['id'] .'" class="b-button_float_right">
    '. ( $aOne['moderator_status'] !== '0' ? '<a onclick="'. $sAction .'" class="b-button b-button_float_right b-button_mini" href="javascript:void(0);"><span class="b-button__icon b-button__icon_'. $sIcon .'"></span></a>' : '') .'
    </span>

    <div class="b-post__txt '. $sClass .'"><span class="b-post__txt b-post__txt_bold '. $sClass .'">'. $sName .' '. $sSurname.' ['. $sLogin.']</span> '. date("d.m.y в H:i",strtotimeEx($aOne['post_time'])) . '<span id="my_deleted_'. $aOne['id'] .'" style="color:red;">' . $sDeleted .'</span>:</div>
    <div class="b-post__txt '. $sClass .'">
       '. $msg_text .'
    </div>

    '. $sAttach .'
</div>
';
                }

                $objResponse->append( 'my_div_contents', 'innerHTML', $sDialog );
                $objResponse->script( "user_content.afterScroll();" );
                $objResponse->script( "user_content.getLettersPage++;" );
            }
        }

        $objResponse->script( '$("my_div_wait").destroy();' );
    }
    
    $objResponse->script('user_content.spinner.resize();');
    
    return $objResponse;
}

/**
 * Утверждение/удаление личного сообщения при чтении переписки в заблокированных личных сообщениях
 * 
 * @param  int $sId ID личного сообщения
 * @param  int $sId UID отправителя
 * @param  int $sAction 1 - утвердить, 2 - удалить
 * @param  string $reason причина удаления
 * @return object xajaxResponse
 */
function updateLetter( $sId = 0, $sFromId = 0, $sAction = 1, $sReason = '', &$objResponse = null ) {
    global $user_content;
    
    if ( !$objResponse ) {
        $objResponse = new xajaxResponse();
    }
    
    if ( hasPermissions('users') && $sAction > 0 && $sAction < 5 ) {
        $nAction  = $sAction == 1 ? 1 : 2;
        $user_content->resolveMessages( '', $_SESSION['uid'], $sFromId, $sId, 0, $nAction, '', $sReason );
        
        $sAction  = $nAction == 2 ? 'user_content.updateLetter('. $sId . ', '. $sFromId .', 1)' : 'parent.banned.delReason(\'1_'. $sId .'_0\', '. $sFromId .', \'updateLetter\', {})';
        $sIcon    = $nAction == 2 ? 'ok' : 'del';
        $sDeleted = $nAction == 2 ? ' [Сообщение удалено модератором]' : '';
        $sLink    = '<a onclick="'. $sAction .'" class="b-button b-button_float_right b-button_mini" href="javascript:void(0);"><span class="b-button__icon b-button__icon_'. $sIcon .'"></span></a>';
        
        if ( $nAction == 2 ) {
            $objResponse->script( "parent.$$(\"div[id^='ov-notice']\").setStyle('display', 'none');" );
            $objResponse->script( "parent.$('ov-notice22-r').toggleClass('b-shadow_hide');" );
        }
        
        $objResponse->script('user_content.spinner.hide(true);');
        $objResponse->assign( 'my_action_' . $sId, 'innerHTML', $sLink );
        $objResponse->assign( 'my_deleted_' . $sId, 'innerHTML', $sDeleted );
        $objResponse->script('user_content.spinner.resize();');
    }
    
    return $objResponse;
}

/**
 * Утвердить личное сообщение в заблокированных личных сообщениях
 * 
 * @param  int $sId ID личного сообщения
 * @param  int $sId UID отправителя
 * @return object xajaxResponse
 */
function approveLetter( $sId = 0, $sFromId = 0 ) {
    global $user_content;
    
    $objResponse = new xajaxResponse();
    
    if ( hasPermissions('users') ) {
        $user_content->resolveMessages( '', $_SESSION['uid'], $sFromId, $sId );
        $objResponse->script('user_content.spinner.hide(true);');
        $objResponse->script( "$('my_div_content_{$sId}').destroy();" );
        $objResponse->script('user_content.spinner.resize();');
    }
    
    return $objResponse;
}

/**
 * Возвращает диалог при чтении переписки в потоке лички
 * 
 * @param  string $stream_id идентификатор потока
 * @param  int $nFromId uid отправиля (то есть одного)
 * @param  int $nToId uid получателя (то есть другого)
 * @param  int $nMsgId id сообщения из которого смотрят переписку
 * @param  int $nCurpage номер страницы
 * @return object xajaxResponse
 */
function getLetters( $stream_id = '', $nFromId = 0, $nToId = 0, $nMsgId = 0, $nCurpage = 1 ) {
    global $user_content, $stop_words, $sTeam;
    
    $objResponse = new xajaxResponse();
    
    $objResponse->script('user_content.spinner.hide(true);');
    
    if ( $user_content->hasContentPermissions(1) ) {
        $aStream     = array();
        $checkStream = $user_content->checkStream( 1, $stream_id, $_SESSION['uid'], $aStream );
        
        if ( $checkStream ) {
            require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
            
            $aDialog = array();
            $sDialog = '';
            
            $oFromUser = new users();
            $oFromUser->GetUserByUID( $nFromId );

            $oToUser = new users();
            $oToUser->GetUserByUID( $nToId );

            if ( $oFromUser->login && $oToUser->login ) {
                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/messages.php' );

                $oMsgs    = new messages();
                $aDialog  = $oMsgs->GetMessagesForModers( $nFromId, $oToUser->login, $nCurpage, 20 );
                
                if ( $aDialog ) {
                    $sProF = $oFromUser->is_pro == 't' ? ( is_emp($oFromUser->role) ? view_pro_emp() : view_pro2( $oFromUser->is_pro_test == 't' ? true : false) ).'&nbsp;' : ''; 
                    $sProT = $oToUser->is_pro   == 't' ? ( is_emp($oToUser->role)   ? view_pro_emp() : view_pro2( $oToUser->is_pro_test   == 't' ? true : false) ).'&nbsp;' : ''; 
                    $sProF = $oFromUser->is_team == 't' ? $sTeam : $sProF;
                    $sProT = $oToUser->is_team   == 't' ? $sTeam : $sProT;
                    
                    foreach ( $aDialog as $aOne ) {
                        $sClass   = $aOne['from_id'] == $nFromId ? 'b-post__txt_color_a7a7a6' : '';
                        $sLogin   = $aOne['from_id'] == $nFromId ? $oFromUser->login    : $oToUser->login;
                        $sName    = $aOne['from_id'] == $nFromId ? $oFromUser->uname    : $oToUser->uname;
                        $sSurname = $aOne['from_id'] == $nFromId ? $oFromUser->usurname : $oToUser->usurname;
                        $sIsChuck = $aOne['from_id'] == $nFromId ? $oFromUser->is_chuck : $oToUser->is_chuck;
                        $sPro     = $aOne['from_id'] == $nFromId ? $sProF               : $sProT;
                        $sAttach  = '';
                        //$sClickD  = 'user_content.delLetter('. $aOne['from_id'] .', \''. $aOne['id'] .'_'. ($aOne['id'] == $nMsgId ? '1' : '2') .'\')';
                        $sJSParams = "{'content_id': 1, 'stream_id': '{$stream_id}'}";
                        $sClickD  = 'parent.banned.delReason(\''. 1 .'_'. $aOne['id'] .'_0\', '. $aOne['from_id'] .', \'delLetter\', '. $sJSParams .')';

                        if ( $aOne['files'] ) {
                            $nn = 1;

                            foreach ( $aOne['files'] as $attach )
                            {
                                $aData = getAttachDisplayData( $sLogin, $attach['fname'], 'contacts', 1000, 300, 307200, 0 );

                                if ( $aData && $aData['success'] ) {
                                    if ( $aData['file_mode'] || $aData['virus_flag'] || $att_ext == "swf" ) {
                                        $sAttach .= _parseAttach( $aData );
                                    }
                                    else {
                                        $sAttach .= "<div class=\"b-fon__body b-fon__body_pad_5 b-fon__body_bg_ffebbf b-fon__body_margbot_1\"><img src=\"".WDCPREFIX.'/users/'.$sLogin.'/contacts/'.$aData['file_name']."\" alt=\"{$aData['file_name']}\" title=\"{$aData['file_name']}\" width=\"{$aData['img_width']}\" height=\"{$aData['img_height']}\" /></div>";
                                    }

                                    $nn++;
                                }
                            }
                            
                            $sAttach = _wrapAttach( $sAttach );
                        }
                        
                        $msg_text = $aOne['moderator_status'] === '0' ? $stop_words->replace($aOne['msg_text']) : $aOne['msg_text'];
                        $msg_text = reformat( $msg_text, 50, 0, -($sIsChuck=='t'), 1 );
                        $sDeleted = $aOne['deleted'] ? ' [Сообщение удалено модератором]' : '';
                        $sDialog .= '
<div class="b-post b-post_bordtop_dfe3e4 b-post_padtop_15 b-post_marg_20_10" id="my_div_content_'. $aOne['id'] .'_'. ($aOne['id'] == $nMsgId ? '1' : '2') .'">
    '. ( !$aOne['deleted'] && ($aOne['moderator_status'] !== '0' || ($aOne['moderator_status'] === '0' && $aOne['id'] == $nMsgId)) ? '<a id="my_del_link_'. $aOne['id'] .'" onclick="'. $sClickD .'" class="b-button b-button_float_right b-button_mini" href="javascript:void(0);"><span class="b-button__icon b-button__icon_del"></span></a>' : '') .'

    <div class="b-post__txt '. $sClass .' b-post__txt_fontsize_15">'. $sPro .'<span class="b-post__txt b-post__txt_bold '. $sClass .'">'. $sName .' '. $sSurname.' ['. $sLogin.']</span> '. date("d.m.y в H:i",strtotimeEx($aOne['post_time'])) . '<span id="my_deleted_'. $aOne['id'] .'" style="color:red;">' . $sDeleted .'</span>'.':</div>
    <div class="b-post__txt '. $sClass .' b-post__txt_fontsize_15">
       '. $msg_text .'
    </div>

    '. $sAttach .'
</div>
';
                    }

                    $objResponse->append( 'my_div_contents', 'innerHTML', $sDialog );
                    $objResponse->script( "user_content.afterScroll();" );
                    $objResponse->script( "user_content.getLettersPage++;" );
                }
            }
            
            $objResponse->script( '$("my_div_wait").destroy();' );
        }
        else {
            _loseStream( $objResponse, $stream_id );
        }
    }
    else {
        _parsePermissionsDenied( $objResponse );
    }
    
    $objResponse->script('user_content.spinner.resize();');
    
    return $objResponse;
}

/**
 * Удаление личного сообщения
 * 
 * @param  string $stream_id идентификатор потока
 * @param  string $from_id идентификатор отправителя сообщения
 * @param  string $sid идентификатор сообщения
 * @param  string $reason причина удаления
 * @param  object ссылка на $objResponse опционально
 */
function delLetter( $stream_id = '', $from_id = 0, $sid = '', $reason = '', &$objResponse = null ) {
    global $user_content;
    
    if ( !$objResponse ) {
        $objResponse = new xajaxResponse();
    }
    
    $objResponse->script('user_content.spinner.hide(true);');
    
    if ( $user_content->hasContentPermissions(1) ) {
        $aStream     = array();
        $checkStream = $user_content->checkStream( 1, $stream_id, $_SESSION['uid'], $aStream, 1 );
        
        if ( $checkStream ) {
            list( $sMsgId, $sMsgKind ) = explode( '_', $sid );
            
            $user_content->resolveContent( $stream_id, $_SESSION['uid'], $from_id, 1, $sMsgId, 0, 2, '', $reason );
            
            $objResponse->script( "parent.$$(\"div[id^='ov-notice']\").setStyle('display', 'none');" );
            $objResponse->script( "parent.$('ov-notice22-r').toggleClass('b-shadow_hide');" );
            $objResponse->assign( 'my_deleted_' . $sMsgId, 'innerHTML', ' [Сообщение удалено модератором]' );
            $objResponse->script( "$('my_del_link_{$sMsgId}').destroy();" );
        }
        else {
            _loseStream( $objResponse, $stream_id );
        }
    }
    else {
        _parsePermissionsDenied( $objResponse );
    }
    
    $objResponse->script('user_content.spinner.resize();');
    
    return $objResponse;
}

/**
 * Парсит порцию HTML содержимого в зависимости от контента
 * 
 * @param  object $objResponse xajaxResponse
 * @param  array $aStream данные о потоке
 * @param  int $content_id идентификатор сущности из admin_contents
 * @param  array $contents массив сущностей
 * @param  int $status статус: 0 - не проверенно, 1 - утверждено, 2 - удалено
 * @param  int $content_cnt количество записей в потоке
 * @return string HTML
 */
function _parseContents( &$objResponse, $aStream = array(), $content_id = 0, $contents = array(), $status = 0, $content_cnt = 0 ) {
    $sReturn = '';
    
    $objResponse->script( '$("my_div_wait").destroy();' );
    
    if ( is_array($contents) && count($contents) ) {
        $nLastId = 0;
        
        switch ($content_id) {
            case user_content::MODER_MSSAGES:
                // Личные сообщения
                $sReturn = _parseContacts( $nLastId, $aStream, $contents, $status );
                break;
            case user_content::MODER_BLOGS:
                // Блоги: посты и комментарии
                $sReturn = _parseBlogs( $nLastId, $contents, $status, $aStream );
                break;
            case user_content::MODER_COMMUNITY:
                // Сообщества: посты и комментарии
                $sReturn = _parseCommunity( $nLastId, $contents, $status, $aStream );
                break;
            case user_content::MODER_PROJECTS:
                // Проекты
                $sReturn = _parseProjects( $nLastId, $contents, $status, $aStream );
                break;
            case user_content::MODER_PRJ_OFFERS:
                // Предложения в проектах
                $sReturn = _parseProjectsOffers( $nLastId, $contents, $status, $aStream );
                break;
            case user_content::MODER_ART_COM:
                // Комментарии в статьях
                $sReturn = _parseArticleComments( $nLastId, $contents, $status, $aStream );
                break;
            case user_content::MODER_PROFILE:
                // Изменения в профилях
                $sReturn = _parseProfile( $nLastId, $contents, $status, $aStream );
                break;
            case user_content::MODER_PRJ_DIALOG:
                // Комментарии к предложениям по проектам
                $sReturn = _parseProjectsDialog( $nLastId, $contents, $status, $aStream );
                break;
            case user_content::MODER_CONTEST_COM:
                // Комментарии к предложениям конкурсов
                $sReturn = _parseContestComments( $nLastId, $contents, $status, $aStream );
                break;
            case user_content::MODER_PORTF_CHOISE:
                // Уточнения к разделам в портфолио
                $sReturn = _parsePortfChoice( $nLastId, $contents, $status, $aStream );
                break;
            case user_content::MODER_PORTFOLIO:
                // Работы в портфолио
                $sReturn = _parsePortfolio( $nLastId, $contents, $status, $aStream );
                break;
            case user_content::MODER_TSERVICES:
                //Типовые услуги
                $sReturn = _parseTServices( $nLastId, $contents, $status, $aStream );
                break;
            
            case user_content::MODER_SBR_REQV:
                //Реквизиты в разделе финансы
                $sReturn = _parseSbrReqv( $nLastId, $contents, $status, $aStream );
                break;
            
            case user_content::MODER_SDELAU:
                // Предложения фрилансеров "Сделаю"
                $sReturn = _parseSdelau( $nLastId, $contents, $status, $aStream );
                break;
            case user_content::MODER_PRJ_COM:
                // Сборная: Предложения в проектах/конкурсах, комментарии к предложениям в проектах/конкурсах, Предложения фрилансеров Сделаю
                $sReturn = _parsePrjCom( $nLastId, $contents, $status, $aStream );
                break;
            case user_content::MODER_COMMENTS:
                // Сборная: Комментарии: магазин, статьи
                $sReturn = _parseComments( $nLastId, $contents, $status, $aStream );
                break;
            case user_content::MODER_PORTF_UNITED:
                // Сборная: Работы в портфолио, Уточнения к разделам в портфолио
                $sReturn = _parsePortfUnited( $nLastId, $contents, $status, $aStream );
                break;
            case user_content::MODER_BLOGS_UNITED:
                // Сборная: Блоги: посты и комментарии, Комментарии в Комментарии в статьях
                $sReturn = _parseBlogsUnited( $nLastId, $contents, $status, $aStream );
                break;
            case user_content::MODER_USER_UNITED:
                // Сборная: Изменения в профилях и Уточнения к разделам в портфолио
                $sReturn = _parseUserUnited( $nLastId, $contents, $status, $aStream );
                break;
            default:
                break;
        }
        
        if ( $status ) {
            $objResponse->append( 'my_div_contents', 'innerHTML', $sReturn );
            $objResponse->script( "user_content.lastID = '$nLastId';" );
        }
        else {
            $objResponse->assign( 'my_div_contents', 'innerHTML', $sReturn );
            $objResponse->script('user_content.playSound();');
        }
        
        $objResponse->script( 'user_content.contentCnt = '. ( $status ? 'user_content.contentCnt + ' : '' ) . count($contents) .';' );
        
        if ( $status ) {
            $objResponse->script( "user_content.afterScroll();" );
        }
        
        $objResponse->script( "hljsDomready();" );
    }
    else {
        if ( $status ) {
            if ( !$content_cnt ) {
                $sKind = ( $status == 1 ) ? 'проверенных' : 'заблокированных';
                $objResponse->assign( 'my_div_contents', 'innerHTML', '<div class="b-post b-post_pad_10_15_15">На данный момент нет '. $sKind .' записей</div>' );
            }
        }
        else {
            $objResponse->script( 'user_content.playSoundFlag = true;' );
            $objResponse->assign( 'my_div_contents', 'innerHTML', '<div class="b-post b-post_pad_10_15_15">На данный момент нет новых записей</div>' );
            $objResponse->script( "setTimeout('user_content.chooseContent();', ". (user_content::MODER_CHOOSE_REFRESH * 1000) .")" );
        }
    }
    
    if ( $status ) {
        $objResponse->script( 'user_content.playSoundFlag = false;' );
    }
    
    return $sReturn;
}

/**
 * Парсит порцию HTML содержимого для личных сообщений
 * 
 * @param  int $last_id возвращает последний Id сообщений
 * @param  array $aStream данные о потоке
 * @param  array $content массив данных из базы
 * @param  int $status статус: 0 - не проверенно, 1 - утверждено, 2 - удалено
 * @return string HTML
 */
function _parseContacts( &$last_id, $aStream = array(), $content = array(), $status = 0 ) {
    global $user_content, $stop_words, $sTeam;
    
    $nCnt    = count($content);
    $sReturn = '';
    
    if ( !$status && count($content) < user_content::MESSAGES_PER_PAGE ) {
        $user_content->chooseContent( 1, $aStream['stream_id'], $aStream['stream_num'], user_content::MESSAGES_PER_PAGE );
    }
    elseif ( !$status ) {
        array_pop($content);
    }
    
    foreach ( $content as $aOne ) {
        $last_id = $aOne['moder_num'];
        $sAttach = '';
        
        if ( $aOne['files'] ) {
            $nn = 1;
            
            foreach ( $aOne['files'] as $attach )
            {
                $aData = getAttachDisplayData( $aOne['f_user']['login'], $attach['fname'], 'contacts', 1000, 300, 307200, 0 );
                
                if ( $aData && $aData['success'] ) {
                    if ( $aData['file_mode'] || $aData['virus_flag'] || $att_ext == "swf" ) {
                        $sAttach .= _parseAttach( $aData );
                    }
                    else {
                        $sAttach .= "<div class=\"b-fon__body b-fon__body_pad_5 b-fon__body_bg_ffebbf b-fon__body_margbot_1\"><img src=\"".WDCPREFIX.'/users/'.$aOne['f_user']['login'].'/contacts/'.$aData['file_name']."\" alt=\"{$aData['file_name']}\" title=\"{$aData['file_name']}\" width=\"{$aData['img_width']}\" height=\"{$aData['img_height']}\" /></div>";
                    }
                    
                    $nn++;
                }
            }
            
            //$sAttach = '<div class="b-icon-layout b-fon">'. $sAttach .'</div>';
            $sAttach = _wrapAttach( $sAttach );
        }
        
        $aOne['f_user']['id']         = $aOne['id'];
        $aOne['f_user']['content_id'] = $aOne['content_id'];
        $aOne['f_user']['stream_id']  = $aStream['stream_id'];
        $aOne['f_user']['from_id']    = $aOne['from_id'];
        $aOne['f_user']['to_id']      = $aOne['to_id'];
        
        $aOne['is_sent']       = '0';
        $aOne['context_code']  = '9';
        $aOne['context_link']  = '';
        $aOne['context_title'] = 'Личное сообщение для '. $aOne['t_user']['uname'] .' '. $aOne['t_user']['usurname'] .' ['. $aOne['t_user']['login'] .']';
        $aOne['user_id']       = $aOne['from_id'];
        
        $sUserClass = is_emp($aOne['f_user']['role']) ? '6db335' : 'fd6c30';
        $sJSParams  = "{'from_id': {$aOne['f_user']['uid']}, 'stream_id': '{$aStream['stream_id']}', 'content_cnt': $nCnt, 'status': $status, 'is_sent': '{$aOne['is_sent']}'}";
        $sEditIcon  = _parseEditIcon( 'admEditContacts', $aOne['id'], $status, '0', $sJSParams );
        $sJSParams  = "{'content_id': ". user_content::MODER_MSSAGES .", 'stream_id': '{$aStream['stream_id']}', 'content_cnt': $nCnt, 'status': $status, 'is_sent': '{$aOne['is_sent']}'}";
        
        $sModified = ($aOne['modified_id'] && $aOne['modified_id'] != $aOne['from_id']) ? '<div class="b-post__txt b-post__txt_padbot_15"><span style="color:red;">Сообщение было отредактировано. '. ($aOne['modified_reason'] ? 'Причина: '.$aOne['modified_reason'] : 'Без причины') .'</span></div>' : '';
        $sMessage = xmloutofrangechars($aOne['msg_text']);
        $sMessage = $status != 1 ? $stop_words->replace( $sMessage ) : $sMessage;
        $sMessage = !$sMessage ? '&nbsp;' : $sMessage;
        $sMessage = reformat( $sMessage, 50, 0, -($aOne['f_user']['is_chuck']=='t'), 1 );
        $sEdit    = $status == 1 ? 'Редактировать' : 'Редактировать и утвердить';
        
        $sProF    = $aOne['f_user']['is_pro'] == 't' ? ( is_emp($aOne['f_user']['role']) ? view_pro_emp() : view_pro2( $aOne['f_user']['is_pro_test'] == 't' ? true : false) ).'&nbsp;' : ''; 
        $sProT    = $aOne['t_user']['is_pro'] == 't' ? ( is_emp($aOne['t_user']['role']) ? view_pro_emp() : view_pro2( $aOne['t_user']['is_pro_test'] == 't' ? true : false) ).'&nbsp;' : ''; 
        
        $sReturn .= '
<div class="b-post b-post_bordtop_dfe3e4 b-post_padtop_15 b-post_marg_20_10" id="my_div_content_'. $aOne['content_id'] .'_'. $aOne['id'] .'_0">
    '. _parseHidden( $aOne ) .'
    '. _parseOkIcon( $status, $aOne['content_id'], $aOne['id'], '0', $aOne['from_id'] ) .'
    '. _parsePostTime( $status, $aOne['post_time'] ) .'
    <div class="b-username b-username_padbot_5">'. ($aOne['f_user']['is_team'] == 't' ? $sTeam : $sProF ) .'<a class="b-username__link b-username__link_color_'. $sUserClass .' b-username__link_fontsize_11 b-username__link_bold" href="/users/'. $aOne['f_user']['login'] .'" target="_blank">'. $aOne['f_user']['uname'] .' '. $aOne['f_user']['usurname'] .' ['. $aOne['f_user']['login'] .']</a></div>
    '. ( $aOne['f_user']['warn'] ? '<div class="b-username_padbot_5"><a onclick="parent.user_content.getUserWarns('.$aOne['from_id'].');" href="javascript:void(0);" class="notice">Предупреждения:&nbsp;<span id="warn_'.$aOne['from_id'].'_'. user_content::MODER_MSSAGES .'_'. $aOne['id'] .'">'. intval($aOne['f_user']['warn']) .'</span></a></div>' : '<div class="b-username_padbot_5 user-notice">Предупреждений нет</div>') . '
    <div class="b-username b-username_bold b-username_padbot_10">Кому: '. ($aOne['t_user']['is_team'] == 't' ? $sTeam : $sProT ) .'<a class="b-username__link b-username__link_color_000  b-username__link_bold" href="/users/'. $aOne['t_user']['login'] .'" target="_blank">'. $aOne['t_user']['uname'] .' '. $aOne['t_user']['usurname'] .' ['. $aOne['t_user']['login'] .']</a></div>
    '. _parseMass( $aOne, $status, '0' ) .'
    <div class="b-post__txt b-post__txt_fontsize_15">'. $sMessage .'</div>
    
        '. $sAttach .'
        
        '. $sModified .'
        '. _parseDelIcons( $aOne['f_user'], 'uid', $status, '0', $sJSParams, $sEditIcon ) .'
</div>';
    }
    
    return $sReturn;
}

/**
 * Парсит порцию HTML содержимого для блогов
 * 
 * @param  int $last_id возвращает последний Id блогов
 * @param  array $content массив данных из базы
 * @param  int $status статус: 0 - не проверенно, 1 - утверждено, 2 - удалено
 * @param  array $aStream данные о потоке
 * @return string HTML
 */
function _parseBlogs( &$last_id, $content = array(), $status = 0, $aStream = array() ) {
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/blogs.php' );
    
    $blogs   = new blogs;
    $groups  = $blogs->GetThemes( $error, 0 );
    $nCnt    = count( $content );
    $sReturn = '';
    
    foreach ( $content as $aOne ) {
        $last_id  = $aOne['moder_num'];
        $sReturn .= _parseBlogOne( $aOne, $status, $aStream, $nCnt, user_content::MODER_BLOGS, $groups );
    }
    
    return $sReturn;
}

/**
 * Парсит HTML одного поста или комментария в блогах
 * 
 * @param  array $aOne массив с данными комментария
 * @param  int $status статус: 0 - не проверенно, 1 - утверждено, 2 - удалено
 * @param  array $aStream данные о потоке
 * @param  int $nCnt количество записей в потоке
 * @param  int $nContentId идентификатор сущности из admin_contents (фактический из потоков, то есть со сборными)
 * @param  array $groups список разделов блогов для постов
 * @return string HTML
 */
function _parseBlogOne( $aOne = array(), $status = 0, $aStream = array(), $nCnt = 0, $nContentId = 0, $groups = array() ) {
    global $stop_words, $sTeam;
    
    $sAttach = '';
    
    if ( $aOne['attach'] ) {
        $nn = 1;
        
        foreach ( $aOne['attach'] as $attach ) {
            $ext      = CFile::getext($attach['fname']);
            $is_image = in_array( $ext, $GLOBALS['graf_array'] );
            
            if ( $is_image && $ext != 'swf' && $ext != 'flv' ) {
                $filename = ( $attach['small'] == 2 ) ? 'sm_' . $attach["fname"] : $attach["fname"];
                $width    = ( $attach['small'] == 2 ) ? 300 : ( $attach['width'] > 300 ? 300 : $attach['width']);
                $link     = WDCPREFIX . '/users/' . $aOne['login'] . '/upload/' . $attach["fname"];
                $sAttach .= "<div class=\"b-fon__body b-fon__body_pad_5 b-fon__body_bg_ffebbf b-fon__body_margbot_1\"><a href=\"{$link}\" target=\"_blank\" title=\"{$attach["fname"]}\" alt=\"{$attach["fname"]}\"><img src=\"".WDCPREFIX.'/users/'.$aOne['login'].'/upload/'.$filename."\" alt=\"$filename\" title=\"$filename\" width=\"$width\" /></a></div>";
            }
            else {
                $aData    = getAttachDisplayData( $aOne['login'], $attach["fname"], 'upload' );
                $sAttach .= _parseAttach( $aData );
            }
            
            $nn++;
        }
        
        //$sAttach = '<div class="b-icon-layout b-fon">'. $sAttach .'</div>';
        $sAttach = _wrapAttach( $sAttach );
    }
    
    $sPoll = '';
    
    if ( $aOne['poll_question'] && !$aOne['reply_to'] ) {
        $sQuestion = xmloutofrangechars($aOne['poll_question']);
        $sQuestion = $status != 1 ? $stop_words->replace( $sQuestion ) : $sQuestion;
        $sPoll     = '<div class="b-post__txt b-post__txt_bold b-post__txt_fontsize_15">Опрос: '. reformat( $sQuestion, 40, 0, 1 ) .'</div>';
        $nn        = 1;
        
        foreach ( $aOne['poll'] as $poll ) {
            $sAnswer = xmloutofrangechars($poll['answer']);
            $sAnswer = $status != 1 ? $stop_words->replace( $sAnswer ) : $sAnswer;
            $sPoll  .= '<div class="b-post__txt b-post__txt_padbot_5 b-post__txt_fontsize_15"><span class="b-post__txt b-post__txt_bold b-post__txt_color_6bad6c b-post__txt_fontsize_15">'. $nn .'.</span> '. reformat( $sAnswer, 40, 0, 1 ) .'</div>';
            $nn++;
        }
    }
    
    $sGroupName = '';
    
    if ( !$aOne['reply_to'] ) {
        foreach( $groups as $theme ) {
            if ( $theme['id'] == $aOne['id_gr'] ) {
                $sTitle = $theme['t_name'];
            }
        }
        
        $sLink = getFriendlyURL( 'blog_group', $aOne['id_gr'] );
        $sGroupName = '<div class="b-post__txt b-post__txt_padtop_10 b-post__txt_fontsize_11"><span class="b-post__bold">Раздел:</span> <a class="b-post__link b-post__link_fontsize_11" href="'. $sLink .'" target="_blank">'. $sTitle .'</a></div>';
    }
    
    // TODO: видео через аякс не работает так как там нужно выполнение яваскрипта
    $sYouTube = $aOne['youtube_link'] ? '<div class="b-post__txt b-post__txt_bold b-post__txt_fontsize_15">Ссылка на YouTube/RuTube/Vimeo видео:</div><a href="' . $aOne['youtube_link'] .'" target="_blank">' . $aOne['youtube_link'] .'</a>' : '';
    $sKind    = $aOne['reply_to'] ? '2' : '1';
    $sLink    = getFriendlyURL( 'blog', $aOne['src_id'] );
    $sLink   .= $aOne['reply_to'] ? '?openlevel=' . $aOne['id'] . '#o' . $aOne['id'] : '';
    $sTitle   = trim( str_replace( '&nbsp;', ' ', $aOne['src_name']));
    $sTitle   = xmloutofrangechars($sTitle);
    $sTitle   = $status != 1 ? $stop_words->replace( $sTitle ) : $sTitle;
    $sTitle   = reformat( $sTitle, 52, 0, 1 );
    $sMessage = xmloutofrangechars($aOne['msgtext']);
    $sMessage = $status != 1 ? $stop_words->replace( $sMessage ) : $sMessage;
    $sMessage = !$sTitle && !$sMessage ? '&nbsp;' : $sMessage;
    $sMessage = reformat($sMessage, 83, 0, -($aOne['is_chuck']=='t'), 1);
    
    $aOne['context_code']  = '2';
    $aOne['context_link']  = $sLink;
    $aOne['context_title'] = ($aOne['src_name'] !== '' ? xmloutofrangechars($aOne['src_name']) : '<без темы>');
    
    $sUserClass = is_emp($aOne['role']) ? '6db335' : 'fd6c30';
    $sMsgClass  = $sTitle ? '' : 'b-post__txt_padbot_5';
    $sKindIco   = '<img class="b-post__pic b-post__pic_valign_mid" src="/images/frame-'. ($aOne['reply_to'] ? 'comment' : 'post') .'.png" alt="" />&nbsp;'; 
    $sTitle     = $sTitle ? '<div class="b-post__txt b-post__txt_padbot_5 b-post__txt_fontsize_15">'. $sKindIco .' <a class="b-post__link b-post__link_bold b-post__link_fontsize_15" href="'. $sLink .'" target="_blank">'. $sTitle .'</a></div>' : '';
    
    $sJSParams = "{'content_id': ". $nContentId .", 'stream_id': '{$aStream['stream_id']}', 'content_cnt': $nCnt, 'status': $status, 'is_sent': '{$aOne['is_sent']}'}";
    $sEditIcon  = _parseEditIcon( 'admEditBlogs', $aOne['id'], $status, $sKind, $sJSParams );
    
    $sPro = $aOne['is_pro'] == 't' ? preg_replace('#<a[^>]+>(.+)</a>#', '$1', (is_emp($aOne['role']) ? view_pro_emp() : view_pro2( $aOne['is_pro_test'] == 't' ? true : false))).'&nbsp;' : ''; 
    
    $sReturn .= '
<div class="b-post b-post_bordtop_dfe3e4 b-post_padtop_15 b-post_marg_20_10" id="my_div_content_'. $aOne['content_id'] .'_'. $aOne['id'] .'_'. $sKind .'">
    '. _parseHidden( $aOne, $sKind ) .'
    '. _parseOkIcon( $status, $aOne['content_id'], $aOne['id'], $sKind, $aOne['user_id'] ) .'
    '. _parsePostTime( $status, $aOne['post_time'] ) .'
    <div class="b-username b-username_padbot_10">
        '. ($aOne['is_team'] == 't' ? $sTeam : $sPro ) .'<a class="b-username__link b-username__link_color_'. $sUserClass .' b-username__link_fontsize_11 b-username__link_bold" href="/users/'. $aOne['login'] .'" target="_blank">'. $aOne['uname'] .' '. $aOne['usurname'] .' ['. $aOne['login'] .']</a>
        <a class="b-post__anchor b-post__anchor_margleft_10" href="'. $sLink .'" target="_blank"></a>
    </div>
    '. ( $aOne['warn'] ? '<div class="b-username_padbot_5"><a onclick="parent.user_content.getUserWarns('.$aOne['user_id'].');" href="javascript:void(0);" class="notice">Предупреждения:&nbsp;<span id="warn_'.$aOne['user_id'].'_'. $aOne['content_id'] .'_'. $aOne['id'] .'">'. intval($aOne['warn']) .'</span></a></div>' : '<div class="b-username_padbot_5 user-notice">Предупреждений нет</div>') . '
    '. _parseMass( $aOne, $status, $sKind ) .'
    '. $sTitle .'
    <div class="b-post__txt '. $sMsgClass .' b-post__txt_fontsize_15">'. ($sTitle ? '' : $sKindIco) . $sMessage .'</div>
    '. $sPoll .'
    '. $sAttach .'
    '. $sYouTube .'
    '. $sGroupName .'
    '. _parseDelIcons( $aOne, 'user_id', $status, $sKind, $sJSParams, $sEditIcon ) .'
</div>';
    
    return $sReturn;
}

/**
 * Парсит порцию HTML содержимого для сообществ
 * 
 * @param  int $last_id возвращает последний Id сообществ
 * @param  array $content массив данных из базы
 * @param  int $status статус: 0 - не проверенно, 1 - утверждено, 2 - удалено
 * @param  array $aStream данные о потоке
 * @return string HTML
 */
function _parseCommunity( &$last_id, $content = array(), $status = 0, $aStream = array() ) {
    global $stop_words, $sTeam;
    
    $nCnt    = count($content);
    $sReturn = '';
    
    foreach ( $content as $aOne ) {
        $last_id = $aOne['moder_num'];
        $sAttach = '';
        
        if ( $aOne['attach'] ) {
            $nn = 1;
            
            foreach ( $aOne['attach'] as $attach )
            {
                $ext      = CFile::getext($attach['fname']);
                $is_image = in_array( $ext, $GLOBALS['graf_array'] );
                
                if ( $is_image && $ext != 'swf' && $ext != 'flv' ) {
                    $filename = ( $attach['small'] == 't' ) ? 'sm_' . $attach["fname"] : $attach["fname"];
                    $width    = ( $attach['small'] == 't' ) ? 300 : ( $attach['width'] > 300 ? 300 : $attach['width']);
                    $link     = WDCPREFIX . '/users/' . $aOne['login'] . '/upload/' . $attach["fname"];
                    
                    $sAttach .= "<div class=\"b-fon__body b-fon__body_pad_5 b-fon__body_bg_ffebbf b-fon__body_margbot_1\"><a href=\"{$link}\" target=\"_blank\" title=\"{$attach["fname"]}\" alt=\"{$attach["fname"]}\"><img src=\"".WDCPREFIX.'/users/'.$aOne['login'].'/upload/'.$filename."\" alt=\"$filename\" title=\"$filename\" width=\"$width\" /></a></div>";
                }
                else {
                    $aData    = getAttachDisplayData( $aOne['login'], $attach["fname"], 'upload' );
                    $sAttach .= _parseAttach( $aData );
                }
                
                $nn++;
            }
            
            //$sAttach = '<div class="b-icon-layout b-fon">'. $sAttach .'</div>';
            $sAttach = _wrapAttach( $sAttach );
        }
        
        $sPoll = '';
        
        if ( $aOne['question'] && !$aOne['parent_id'] ) {
            $sQuestion = xmloutofrangechars($aOne['question']);
            $sQuestion = $status != 1 ? $stop_words->replace( $sQuestion ) : $sQuestion;
            $sPoll     = '<div class="b-post__txt b-post__txt_bold b-post__txt_fontsize_15">Опрос: '. reformat( $sQuestion, 30, 0, 1 ) .'</div>';
            $nn        = 1;
            
            foreach ( $aOne['answers'] as $poll ) {
                $sAnswer = xmloutofrangechars($poll['answer']);
                $sAnswer = $status != 1 ? $stop_words->replace( $sAnswer ) : $sAnswer;
                $sPoll  .= '<div class="b-post__txt b-post__txt_padbot_5 b-post__txt_fontsize_15"><span class="b-post__txt b-post__txt_bold b-post__txt_color_6bad6c b-post__txt_fontsize_15">'. $nn .'.</span> '. reformat( $sAnswer, 30, 0, 1 ) .'</div>';
                $nn++;
            }
        }
        
        // TODO: видео через аякс не работает так как там нужно выполнение яваскрипта
        $sYouTube = $aOne['youtube_link'] ? '<div class="b-post__txt b-post__txt_bold b-post__txt_fontsize_15">Ссылка на YouTube/RuTube/Vimeo видео:</div><a href="' . $aOne['youtube_link'] .'" target="_blank">' . $aOne['youtube_link'] .'</a>' : '';
        
        $sKind    = $aOne['parent_id'] ? '2' : '1';
        $sLink    = getFriendlyURL( 'commune', $aOne['top_id'] );
        $sLink   .= $aOne['parent_id'] ? '#c_' . $aOne['id'] : '';
        $sTitle   = trim( str_replace( '&nbsp;', ' ', $aOne['title']));
        $sTitle   = xmloutofrangechars($sTitle);
        $sTitle   = $status != 1 ? $stop_words->replace( $sTitle ) : $sTitle;
        $sTitle   = reformat( $sTitle, 40, 0, 1 );
        $sMessage = xmloutofrangechars($aOne['msgtext']);
        $sMessage = $status != 1 ? $stop_words->replace( $sMessage ) : $sMessage;
        $sMessage = reformat( $sMessage, 46, 0, 0, 1 );
        
        $aOne['context_code']  = '4';
        $aOne['context_link']  = $sLink;
        $aOne['context_title'] = ($aOne['title']!=='' ? xmloutofrangechars( $aOne['title'] ) : '<без темы>');
        
        $sUserClass = is_emp($aOne['role']) ? '6db335' : 'fd6c30';
        $sMsgClass  = $sTitle ? '' : 'b-post__txt_padbot_5';
        $sTitle     = $sTitle ? '<div class="b-post__txt b-post__txt_padbot_5 b-post__txt_fontsize_15"><a class="b-post__link b-post__link_bold b-post__link_fontsize_15" href="'. $sLink .'" target="_blank">'. $sTitle .'</a></div>' : '';
        
        $sJSParams = "{'content_id': ". user_content::MODER_COMMUNITY .", 'stream_id': '{$aStream['stream_id']}', 'content_cnt': $nCnt, 'status': $status, 'is_sent': '{$aOne['is_sent']}'}";
        $sEditIcon = _parseEditIcon( 'admEditCommunity', $aOne['id'], $status, $sKind, $sJSParams );
        $sPro      = $aOne['is_pro'] == 't' ? ( is_emp($aOne['role']) ? view_pro_emp() : view_pro2( $aOne['is_pro_test'] == 't' ? true : false) ).'&nbsp;' : ''; 
        
        $sReturn .= '
<div class="b-post b-post_bordtop_dfe3e4 b-post_padtop_15 b-post_marg_20_10" id="my_div_content_'. $aOne['content_id'] .'_'. $aOne['id'] .'_'. $sKind .'">
    '. _parseHidden( $aOne, $sKind ) .'
    '. _parseOkIcon( $status, $aOne['content_id'], $aOne['id'], $sKind, $aOne['user_id'] ) .'
    '. _parsePostTime( $status, $aOne['post_time'] ) .'
    <div class="b-username b-username_padbot_10">
        '. ($aOne['is_team'] == 't' ? $sTeam : $sPro ) .'<a class="b-username__link b-username__link_color_'. $sUserClass .' b-username__link_fontsize_11 b-username__link_bold" href="/users/'. $aOne['login'] .'" target="_blank">'. $aOne['uname'] .' '. $aOne['usurname'] .' ['. $aOne['login'] .']</a>
        <a class="b-post__anchor b-post__anchor_margleft_10" href="'. $sLink .'" target="_blank"></a>
    </div>
    '. ( $aOne['warn'] ? '<div class="b-username_padbot_5"><a onclick="parent.user_content.getUserWarns('.$aOne['user_id'].');" href="javascript:void(0);" class="notice">Предупреждения:&nbsp;<span id="warn_'.$aOne['user_id'].'_'. user_content::MODER_COMMUNITY .'_'. $aOne['id'] .'">'. intval($aOne['warn']) .'</span></a></div>' : '<div class="b-username_padbot_5 user-notice">Предупреждений нет</div>') . '
    '. _parseMass( $aOne, $status, $sKind ) .'
    '. $sTitle .'
    <div class="b-post__txt '. $sMsgClass .' b-post__txt_fontsize_15">'. $sMessage .'</div>
    '. $sPoll .'
    '. $sAttach .'
    '. $sYouTube .'
    '. _parseDelIcons( $aOne, 'user_id', $status, $sKind, $sJSParams, $sEditIcon ) .'
</div>';
    }
    
    return $sReturn;
}

/**
 * Парсит порцию HTML содержимого для проектов
 * 
 * @param  int $last_id возвращает последний Id проектов
 * @param  array $content массив данных из базы
 * @param  int $status статус: 0 - не проверенно, 1 - утверждено, 2 - удалено
 * @param  array $aStream данные о потоке
 * @return string HTML
 */
function _parseProjects( &$last_id, $content = array(), $status = 0, $aStream = array() ) {
    global $user_content, $stop_words, $sTeam;
    
    $nCnt    = count($content);
    $sReturn = '';
    $aEmpId  = array();
    
    foreach ( $content as $aOne ) {
        $aEmpId[] = $aOne['user_id'];
    }
    
    $aPrjCnt = $user_content->getProjectsPer24( $aEmpId ); // TODO: вынести в модель
    
    foreach ( $content as $aOne ) {
        $last_id = $aOne['moder_num'];
        
        $sAttach = '';
        
        if ( $aOne['attach'] ) {
            $nn = 1;
            
            foreach ( $aOne['attach'] as $attach )
            {
                $aData = getAttachDisplayData( null, $attach["name"], $attach['path'], 1000, 300, 307200, 0 );
                
                if ( $aData && $aData['success'] ) {
                    if ( $aData['file_mode'] || $aData['virus_flag'] || $aData['file_ext'] == "swf" ) {
                        $sAttach .= _parseAttach( $aData );
                    }
                    else {
                        $sAttach .= "<div class=\"b-fon__body b-fon__body_pad_5 b-fon__body_bg_ffebbf b-fon__body_margbot_1\"><img src=\"".WDCPREFIX.'/'.$attach['path'].$attach["name"]."\" alt=\"{$aData['file_name']}\" title=\"{$aData['file_name']}\" width=\"{$aData['img_width']}\" height=\"{$aData['img_height']}\" /></div>";
                    }
                    
                    $nn++;
                }
            }
            
            $sAttach = '<div class="b-icon-layout b-post__txt_padbot_15">'. $sAttach .'</div>';
        }
        
        $sLogo = '';
        
        if ( $aOne['logo_id'] ) {
            $cfile = new cfile($aOne['logo_id']);
            
             if ( $cfile->id && trim($aOne['link']) ) {
                 $sLogo = '<div class="b-post__txt b-post__txt_padbot_15"><strong>Лого: </strong><br/>
                     <div>
                         <a target="_blank" rel="nofollow" href="'. $aOne['link'] .'" target="_blank" class="b-post__link">
                             <img alt="" src="'. WDCPREFIX . '/' . $cfile->path . '/' . $cfile->name .'" class="b-post__pic b-post__pic_clear_right">
                         </a>
                     </div>';
              }
        }
        
        $sPayed   = $aOne['kind'] == 7 ? 'Конкурс!' : (( $aOne['ico_payed']=='t' || $aOne['is_upped'] == 't' ) ? 'Платный проект!' : '');
        $sPayed   = $sPayed ? '<div class="b-post__txt b-post__txt_padbot_5 b-post__txt_fontsize_15"><span class="b-post__txt b-post__txt_color_c10601">'. $sPayed .'</span></div>' : '';
        $sTitle   = xmloutofrangechars($aOne['name']);
        $sTitle   = $aOne['kind'] != 4 && $status != 1 ? $stop_words->replace($sTitle) : $sTitle;
        $sTitle   = reformat( $sTitle, 30, 0, 1 );
        $sDescr   = xmloutofrangechars($aOne['descr']);
        $sDescr   = $aOne['kind'] != 4 && $status != 1 ? $stop_words->replace($sDescr) : $sDescr;
        $sDescr   = preg_replace( "/^ /", "\x07", $sDescr );
        $sDescr   = preg_replace( "/(\n) /", "$1\x07", $sDescr );
        $sDescr   = reformat( $sDescr, 30, 0, 0, 1 );
        $sDescr   = preg_replace( "/\x07/", "&nbsp;", $sDescr );
        $sLink    = $GLOBALS['host'] . getFriendlyURL( 'project', $aOne['id'] );
        $sPrjCnt  = isset($aPrjCnt[$aOne['user_id']]) ? $aPrjCnt[$aOne['user_id']] : '0';
        $sOffice  = $aOne['kind'] != 4 ? '' : '<div class="b-post__txt b-post__txt_padbot_5 b-post__txt_fontsize_15" style="color: #cc4642;"> Проект в офис. Разрешен обмен контактами.</div>';
        
        $aOne['context_code']  = '3';
        $aOne['context_link']  = $sLink;
        $aOne['context_title'] = xmloutofrangechars( $aOne['name'] );
        
        $sKindIco = $aOne['kind'] == 7 ? 'kont' : 'prj';
        
        $sJSParams = "{'content_id': ". user_content::MODER_PROJECTS .", 'stream_id': '{$aStream['stream_id']}', 'content_cnt': $nCnt, 'status': $status, 'is_sent': '{$aOne['is_sent']}'}";
        $sEditIcon = _parseEditIcon( 'admEditProjects', $aOne['id'], $status, ($aOne['kind'] == 7 ? 7 : 0), $sJSParams );
        
        $sPro = $aOne['is_pro'] == 't' ? preg_replace('#<a[^>]+>(.+)</a>#', '$1', view_pro_emp()).'&nbsp;' : ''; 
        
        $projectObject = new_projects::initData(new_projects::getPrj($aOne['id']));
        $allow_vacancy = $projectObject->isAllowMovedToVacancy();
        $moveToVacancy = $allow_vacancy ? '
            <div class="b-post__txt b-post__txt_padbot_10 b-post__txt_fontsize_11">
                <a onclick="user_content.make_vacancy(\''.$aOne['content_id'] .'_'. $aOne['id'] .'_0\');" class="b-post__txt b-post__txt_padbot_10 b-post__txt_fontsize_11" href="#">Сделать вакансией</a>
            </div>' : '';
        
        
        $sReturn .= '
<div class="b-post b-post_bordtop_dfe3e4 b-post_padtop_15 b-post_marg_20_10" id="my_div_content_'. $aOne['content_id'] .'_'. $aOne['id'] .'_0">
    '. _parseHidden( $aOne ) .'
    '. _parseOkIcon( $status, $aOne['content_id'], $aOne['id'], '0', $aOne['user_id'] ) .'
    '. _parsePostTime( $status, $aOne['post_time'] ) .'
    <div class="b-username b-username_padbot_5">'. ($aOne['is_team'] == 't' ? $sTeam : $sPro ) .'<a class="b-username__link b-username__link_color_6db335 b-username__link_fontsize_11 b-username__link_bold" href="/users/'. $aOne['login'] .'?kind=0&all=1" target="_blank">'. $aOne['uname'] .' '. $aOne['usurname'] .' ['. $aOne['login'] .']</a></div>
    '. $sPayed . '
    '. ( $aOne['warn'] ? '<div class="b-username_padbot_5"><a onclick="parent.user_content.getUserWarns('.$aOne['user_id'].');" href="javascript:void(0);" class="notice">Предупреждения:&nbsp;<span id="warn_'.$aOne['user_id'].'_'. user_content::MODER_PROJECTS .'_'. $aOne['id'] .'">'. intval($aOne['warn']) .'</span></a></div>' : '<div class="b-username_padbot_5 user-notice">Предупреждений нет</div>') . '
    <div class="b-post__txt b-post__txt_padbot_10 b-post__txt_fontsize_11"><img class="b-post__pic b-post__pic_valign_mid" src="/images/frame-'. $sKindIco .'.png" alt="" /> ('. $sPrjCnt .' за 24 часа)'. ($aOne['pro_only'] == 't' ? '&nbsp;Только для <span class="b-icon b-icon__pro b-icon__pro_f"></span>' : '') .'</div>
    '. $sOffice .'
    '. _parseMass( $aOne, $status, '0' ) .'
    <div class="b-post__txt b-post__txt_padbot_5 b-post__txt_fontsize_15"><a class="b-post__link b-post__link_bold b-post__link_fontsize_15" href="'. $sLink .'" target="_blank">'. $sTitle .'</a></div>
    <div class="b-post__txt b-post__txt_padbot_5 b-post__txt_fontsize_15">'. $sDescr .'</div>
    '. $sAttach .'
    '. $sLogo .'
    <div class="b-post__txt b-post__txt_padtop_5 b-post__txt_fontsize_11"><span class="b-post__bold">Раздел:</span> '. $aOne['specs'] .'</div>
    '. ($aOne['pro_only'] == 't' ? '<div class="b-post__txt b-post__txt_padtop_10 b-post__txt_fontsize_11">Только для <span class="b-icon b-icon__pro b-icon__pro_f"></span></div>' : '') .'
    '. $moveToVacancy .'
    '. _parseDelIcons( $aOne, 'user_id', $status, '0', $sJSParams, $sEditIcon ) .'
</div>';
    
    }
    
    return $sReturn;
}

/**
 * Парсит порцию HTML содержимого для предложений по проектам
 * 
 * @param  int $last_id возвращает последний Id проектов
 * @param  array $content массив данных из базы
 * @param  int $status статус: 0 - не проверенно, 1 - утверждено, 2 - удалено
 * @param  array $aStream данные о потоке
 * @return string HTML
 */
function _parseProjectsOffers( &$last_id, $content = array(), $status = 0, $aStream = array() ) {
    $sReturn = '';
    $nCnt    = count($content);
    
    foreach ( $content as $aOne ) {
        $last_id  = $aOne['moder_num'];
        $sReturn .= _parseProjectsOffersOne( $aOne, $status, $aOne['kind'], $aStream, $nCnt, user_content::MODER_PRJ_OFFERS );
    }
    
    return $sReturn;
}

/**
 * Парсит HTML одного предложения по проектам
 * 
 * @param  array $aOne массив с данными комментария
 * @param  int $status статус: 0 - не проверенно, 1 - утверждено, 2 - удалено
 * @param  string $sKind опционально. тип записи
 * @param  array $aStream данные о потоке
 * @param  int $nCnt количество записей в потоке
 * @param  int $nContentId идентификатор сущности из admin_contents (фактический из потоков, то есть со сборными)
 * @return string HTML
 */
function _parseProjectsOffersOne( $aOne = array(), $status = 0, $sKind = '0', $aStream = array(), $nCnt = 0, $nContentId = 0 ) {
    global $stop_words, $user_content, $sTeam;
    
    $sReturn = '';
    $sAttach = '';
    
    if ( $sKind == 7 ) {
        if ( !empty($aOne['attach']) ) {
            $nn = 1;

            foreach ( $aOne['attach'] as $attach ) {
                $aData = getAttachDisplayData( $aOne['login'], $attach['filename'], "upload", 200, 200, 307200, $attach['prevname'] );
                
                if ( $aData['file_mode'] || $aData['virus_flag'] ) {
                    $sAttach .= _parseAttach( $aData ); 
                }
                else {
                    if ( $attach['prevname'] ) {
                        $sAttach .= '<div class="b-fon__body b-fon__body_pad_5 b-fon__body_bg_ffebbf b-fon__body_margbot_1"><a href="'.WDCPREFIX .'/users/'.$aOne['login'].'/upload/'.$attach['filename'].'" target="_blank" alt="'."{$attach['filename']}".'"><img src="'.WDCPREFIX.'/users/'.$aOne['login'].'/upload/'.$aData['file_name'].'" alt="'.$attach['filename'].'" title="'."{$attach['filename']}".'" width="'.$aData['img_width'].'" height="'.$aData['img_height'].'" /></a></div>';
                    }
                    else {
                        $sAttach .= '<div class="b-fon__body b-fon__body_pad_5 b-fon__body_bg_ffebbf b-fon__body_margbot_1"><img src="'.WDCPREFIX.'/users/'.$aOne['login'].'/upload/'.$aData['file_name'].'" alt="'.$attach['filename'].'" title="'."{$aData['file_name']}".'" width="'.$aData['img_width'].'" height="'.$aData['img_height'].'" /></div>';
                    }
                }
                
                $nn++;
            }
        }
    }
    else {
        $nn = 1;

        for ( $i = 1; $i <= 3; $i++ ) {
            if ( $aOne['pict' . $i] != '' ) { 
                if ($aOne['prev_pict'.$i] != '') {
                    $link     = WDCPREFIX . '/users/' . $aOne['login'] . '/upload/' . $aOne['pict'.$i];
                    $sAttach .= "<div class=\"b-fon__body b-fon__body_pad_5 b-fon__body_bg_ffebbf b-fon__body_margbot_1\"><a href=\"{$link}\" target=\"_blank\" title=\"{$attach["fname"]}\" alt=\"{$attach["fname"]}\"><img src=\"".WDCPREFIX.'/users/'.$aOne['login'].'/upload/'.$aOne['prev_pict'.$i]."\" alt=\"{$aOne['pict' . $i]}\" title=\"{$aOne['pict' . $i]}\" /></a></div>";
                }
                else {
                    $aData = getAttachDisplayData( $aOne['login'], $aOne['pict'.$i], "upload" );
                    $sAttach .= _parseAttach( $aData );
                }
                
                $nn++;
            }
        }
    }
    
    $sAttach = $sAttach ? _wrapAttach( $sAttach ) : '';
    
    $bIsModer = $user_content->hasContentPermissions( $nContentId, permissions::getUserPermissions($aOne['user_id']) );
    $sModified = ($sKind != 7 && $aOne['moduser_id'] && ($aOne['moduser_id'] != $aOne['user_id'] || $bIsModer)) ? '<div class="b-post__txt b-post__txt_padbot_15"><span style="color:red;">Предложение было отредактировано. '. ($aOne['modified_reason'] ? 'Причина: '.$aOne['modified_reason'] : 'Без причины') .'</span></div>' : '';
    $sLink    = $GLOBALS['host'] . getFriendlyURL( 'project', $aOne['src_id'] );
    $sDescr   = $sKind == 7 ? $aOne['post_text'] : $aOne['dialog_root'];
    $sDescr   = xmloutofrangechars(($sKind != 4 && $status != 1) ? $stop_words->replace( $sDescr, 'html', true, 'suspect' ) : $sDescr);
    $sDescr   = !$sDescr ? '&nbsp;' : $sDescr;
    $sOffice  = $sKind != 4 ? '' : '<span style="color: #cc4642;"> Проект в офис. В ответах разрешен обмен контактами.</span>';
    
    $aOne['context_code']  = '3';
    $aOne['context_link']  = $sLink;
    $aOne['context_title'] = xmloutofrangechars( $aOne['src_name'] );
    
    if ( $sKind == 7 ) {
        $sLink .= '?offer=' . $aOne['id'] . '#offer-' . $aOne['id'];
    }
    else {
        $sLink .= '?#freelancer_' . $aOne['user_id'];
    }
    
    $sJSParams = "{'project_id': {$aOne['src_id']}, 'user_id': {$aOne['user_id']}, 'is_pro': '{$aOne['is_pro']}', 'content_id': $nContentId, 'stream_id': '{$aStream['stream_id']}', 'content_cnt': $nCnt, 'status': $status, 'is_sent': '{$aOne['is_sent']}'}";
    $sEditIcon = _parseEditIcon( 'admEditPrjOffers', $aOne['id'], $status, $sKind, $sJSParams );
    $sKindIco  = $sKind == 7 ? 'kont' : 'prj';
    $sJSParams = "{'content_id': $nContentId, 'stream_id': '{$aStream['stream_id']}', 'content_cnt': $nCnt, 'status': $status, 'is_sent': '{$aOne['is_sent']}'}";
    $sPro      = $aOne['is_pro'] == 't' ? view_pro2( $aOne['is_pro_test'] == 't' ? true : false) .'&nbsp;' : '';
    
    $sReturn .= '
<div class="b-post b-post_bordtop_dfe3e4 b-post_padtop_15 b-post_marg_20_10" id="my_div_content_'. $aOne['content_id'] .'_'. $aOne['id'] .'_'. $sKind .'">
    '. _parseHidden( $aOne, $sKind ) .'
    '. _parseOkIcon( $status, $aOne['content_id'], $aOne['id'], $sKind, $aOne['user_id'] ) .'
    '. _parsePostTime( $status, $aOne['post_time'] ) .'
    <div class="b-username b-username_padbot_5">'. ($aOne['is_team'] == 't' ? $sTeam : $sPro ) .'<a class="b-username__link b-username__link_color_fd6c30 b-username__link_fontsize_11 b-username__link_bold" href="/users/'. $aOne['login'] .'" target="_blank">'. $aOne['uname'] .' '. $aOne['usurname'] .' ['. $aOne['login'] .']</a></div>
    '. ( $aOne['warn'] ? '<div class="b-username_padbot_5"><a onclick="parent.user_content.getUserWarns('.$aOne['user_id'].');" href="javascript:void(0);" class="notice">Предупреждения:&nbsp;<span id="warn_'.$aOne['user_id'].'_'. $aOne['content_id'] .'_'. $aOne['id'] .'">'. intval($aOne['warn']) .'</span></a></div>' : '<div class="b-username_padbot_5 user-notice">Предупреждений нет</div>') . '
    '. $sOffice .'
    '. _parseMass( $aOne, $status, $sKind ) .'
    <div class="b-post__txt b-post__txt_padbot_5 b-post__txt_fontsize_15">'. reformat( $sDescr, 50, 0, 0, 1 ) .'</div>
    '. $sAttach .'
    '. $sModified .'
    <div class="b-post__txt b-post__txt_fontsize_11"><img class="b-post__pic b-post__pic_valign_mid" src="/images/frame-'. $sKindIco .'.png" alt="" /> <a class="b-post__link b-post__link_fontsize_11" href="'. $sLink .'" target="_blank">'. reformat(xmloutofrangechars($aOne['src_name']), 30, 0, 1) .'</a></div>
    '. _parseDelIcons( $aOne, 'user_id', $status, $sKind, $sJSParams, $sEditIcon ) .'
</div>';
    
    return $sReturn;
}


/**
 * Парсит порцию HTML содержимого для комментариев в статьях
 * 
 * @param  int $last_id возвращает последний Id проектов
 * @param  array $content массив данных из базы
 * @param  int $status статус: 0 - не проверенно, 1 - утверждено, 2 - удалено
 * @param  array $aStream данные о потоке
 * @return string HTML
 */
function _parseArticleComments( &$last_id, $content = array(), $status = 0, $aStream = array() ) {
    $nCnt    = count($content);
    $sReturn = '';
    
    foreach ( $content as $aOne ) {
        $last_id  = $aOne['moder_num'];
        $sReturn .= _parseArticleCommentOne( $aOne, $status, '0', $aStream, $nCnt, user_content::MODER_ART_COM );
    }
    
    return $sReturn;
}

/**
 * Парсит HTML одного комментария в статьях
 * 
 * @param  array $aOne массив с данными комментария
 * @param  int $status статус: 0 - не проверенно, 1 - утверждено, 2 - удалено
 * @param  string $sKind опционально. тип записи
 * @param  array $aStream данные о потоке
 * @param  int $nCnt количество записей в потоке
 * @param  int $nContentId идентификатор сущности из admin_contents (фактический из потоков, то есть со сборными)
 * @return string HTML
 */
function _parseArticleCommentOne( $aOne = array(), $status = 0, $sKind = '0', $aStream = array(), $nCnt = 0, $nContentId = 0 ) {
    global $stop_words, $sTeam;
    
    $sAttach = '';
    
    if ( $aOne['attach'] ) {
        $nn = 1;

        foreach ( $aOne['attach'] as $attach ) {
            $aData = getAttachDisplayData( null, $attach['fname'], $attach['path'] );
            $sAttach .= _parseAttach( $aData );
        }
    }

    $sLink    = getFriendlyURL( 'article', $aOne['src_id'] ) . '#c_' . $aOne['id'];
    $aTitle   = !$aOne['src_name'] ? 'Без названия' : reformat( xmloutofrangechars($aOne['src_name'] ), 59, 0, 1);
    $sMsgText = wysiwygLinkEncode( xmloutofrangechars($aOne['msgtext']) );
    $sMsgText = $status != 1 ? $stop_words->replace($sMsgText) : $sMsgText;
    $sMsgText = reformat( $sMsgText, 45, 0, 0, 1 );
    $sMsgText = wysiwygLinkDecode( $sMsgText );
    
    $aOne['context_code']  = '6';
    $aOne['context_link']  = $sLink;
    $aOne['context_title'] = xmloutofrangechars( $aOne['src_name'] );
    
    $sYoutubeLink = '';
    
    if ( trim($aOne['youtube_link']) ) {
        $url  = preg_replace("/^(http:\/\/youtu\.be\/([-_A-Za-z0-9]+))/i", HTTP_PREFIX."youtube.com/v/$2", $aOne['youtube_link']);
	    $url  = str_replace('watch?v=', 'v/', $url);
	    if (!stripos($url, 'fs=1')) $url .= '&fs=1';
	    $sYoutubeLink = ' 
        <object width="300" height="247" type="application/x-shockwave-flash" id="myytplayer_youtube-1376" style="text-align: center;" data="'.$url.'"><param name="allowfullscreen" value="true"><param name="allowscriptaccess" value="always"><param name="wmode" value="opaque">
        <embed src="'.$url.'" 
		width="300" height="247" name="ytplayer-youtube-'.$aOne['id'].'" id="myytplayer_youtube-'.$aOne['id'].'" align="middle"
		allowScriptAccess="always" allowFullScreen="true" wmode="opaque"
		type="application/x-shockwave-flash"
		pluginspage="http://www.macromedia.com/go/getflashplayer" />
        </object>
        ';
    }
    
    $sUserClass = is_emp($aOne['role']) ? '6db335' : 'fd6c30';
    $sJSParams  = "{'content_id': $nContentId, 'stream_id': '{$aStream['stream_id']}', 'content_cnt': $nCnt, 'status': $status, 'is_sent': '{$aOne['is_sent']}'}";
    $sEditIcon  = _parseEditIcon( 'admEditArtCom', $aOne['id'], $status, $sKind, $sJSParams );
    $sKindIco   = '<img class="b-post__pic b-post__pic_valign_mid" src="/images/frame-articles.png" alt="" />&nbsp;'; 
    
    $sPro = $aOne['is_pro'] == 't' ? preg_replace('#<a[^>]+>(.+)</a>#', '$1', (is_emp($aOne['role']) ? view_pro_emp() : view_pro2( $aOne['is_pro_test'] == 't' ? true : false))).'&nbsp;' : ''; 
    
    $sReturn .= '
<div class="b-post b-post_bordtop_dfe3e4 b-post_padtop_15 b-post_marg_20_10" id="my_div_content_'. $aOne['content_id'] .'_'. $aOne['id'] .'_'. $sKind .'">
    '. _parseHidden( $aOne, $sKind ) .'
    '. _parseOkIcon( $status, $aOne['content_id'], $aOne['id'], $sKind, $aOne['user_id'] ) .'
    '. _parsePostTime( $status, $aOne['post_time'] ) .'
    <div class="b-username b-username_padbot_10">'. ($aOne['is_team'] == 't' ? $sTeam : $sPro ) .'<a class="b-username__link b-username__link_color_'. $sUserClass .' b-username__link_fontsize_11 b-username__link_bold" href="/users/'. $aOne['login'] .'" target="_blank">'. $aOne['uname'] .' '. $aOne['usurname'] .' ['. $aOne['login'] .']</a></div>
    '. ( $aOne['warn'] ? '<div class="b-username_padbot_5"><a onclick="parent.user_content.getUserWarns('.$aOne['user_id'].');" href="javascript:void(0);" class="notice">Предупреждения:&nbsp;<span id="warn_'.$aOne['user_id'].'_'. $aOne['content_id'] .'_'. $aOne['id'] .'">'. intval($aOne['warn']) .'</span></a></div>' : '<div class="b-username_padbot_5 user-notice">Предупреждений нет</div>') . '
    '. _parseMass( $aOne, $status, $sKind ) .'
    <div class="b-post__txt b-post__txt_padbot_5 b-post__txt_fontsize_15">'. $sKindIco .' <a class="b-post__link b-post__link_bold b-post__link_fontsize_15" href="'. $sLink .'" target="_blank">'. $aTitle .'</a></div>
    <div class="b-post__txt b-post__txt_fontsize_15">'. $sMsgText .'</div>
    '. $sAttach . 
    $sYoutubeLink. _parseDelIcons( $aOne, 'user_id', $status, $sKind, $sJSParams, $sEditIcon ) .'
</div>';
    
    return $sReturn;
}

/**
 * Парсит порцию HTML содержимого для изменений в профилях юзеров
 * 
 * @param  int $last_id возвращает последний Id проектов
 * @param  array $content массив данных из базы
 * @param  int $status статус: 0 - не проверенно, 1 - утверждено, 2 - удалено
 * @param  array $aStream данные о потоке
 * @return string HTML
 */
function _parseProfile( &$last_id, $content = array(), $status = 0, $aStream = array() ) {
    $sReturn = '';
    $nCnt    = count($content);
    
    foreach ( $content as $aOne ) {
        $last_id  = $aOne['moder_num'];
        $sReturn .= _parseProfileOne( $aOne, $status, '0', $aStream, $nCnt, user_content::MODER_PROFILE );
    }
    
    return $sReturn;
}

/**
 * Парсит HTML одного изменения в профилях юзеров
 * 
 * @param  array $aOne массив с данными комментария
 * @param  int $status статус: 0 - не проверенно, 1 - утверждено, 2 - удалено
 * @param  string $sKind опционально. тип записи
 * @param  array $aStream данные о потоке
 * @param  int $nCnt количество записей в потоке
 * @param  int $nContentId идентификатор сущности из admin_contents (фактический из потоков, то есть со сборными)
 * @return string HTML
 */
function _parseProfileOne( $aOne = array(), $status = 0, $sKind = '0', $aStream = array(), $nCnt = 0, $nContentId = 0 ) {
    global $stop_words, $sTeam;
    
    $sReturn = '';
    $sLinkSuff = '';
    
    switch ( $aOne['ucolumn'] ) {
        case 'uname':
            $sColumn = 'Имя';
            break;
        case 'usurname':
            $sColumn = 'Фамилия';
            break;
        case 'pname':
            $sColumn = 'Заголовок страницы';
            break;
        case 'spec_text':
            $sColumn = 'Уточнения к услугам в портфолио';
            $sLinkSuff = '/#spec_text';
            break;
        case 'resume_file':
            $sColumn = 'Файл резюме';
            $sLinkSuff = '/info/#resume_file';
            break;
        case 'resume':
            if ( $aOne['utable'] == 'freelancer' ) {
                $sColumn = 'Текст резюме';
            }
            else {
                $sColumn = 'Дополнительная информация';
            }
            
            $sLinkSuff = '/info/#resume_file';
            break;
        case 'konk':
            $sColumn = 'Участие в конкурсах и награды';
            $sLinkSuff = '/info/#konk';
            break;
        case 'company':
            $sColumn = 'О компании';
            $sLinkSuff = '/info/#company';
            break;
        case 'status_text':
            $sColumn = 'Статус';
            break;
        case 'photo':
            $sColumn = 'Аватар';
            break;
        case 'logo':
            $sColumn = 'Логотип компании';
            $sLinkSuff = '/info/#logo';
            break;
        case 'compname':
            $sColumn = 'Компания';
            $sLinkSuff = '/info/#compname';
            break;
        default:
            $sColumn = '';
            break;
    }

    $sLink    = $GLOBALS['host'] . '/users/' . $aOne['login'];
    $sMsgText = $status != 1? $stop_words->replace( xmloutofrangechars($aOne['new_val']), 'html', true, 'suspect' ): xmloutofrangechars($aOne['new_val']);
    $sMsgText = reformat( $sMsgText, 45, 0, 0, 1 );

    if ( $aOne['ucolumn'] == 'resume_file' ) {
        $sMsgText = '<a href="'. WDCPREFIX .'/users/'. $aOne['login'] .'/resume/'. $aOne['new_val'] .'" class="blue" target="_blank">Резюме загружено</a>';
    }
    
    if ( $aOne['ucolumn'] == 'photo' ) {
        $sMsgText = '<br/>'.view_avatar( $aOne['login'], $aOne['new_val'], 0, 1, '' );
    }
    
    if ( $aOne['ucolumn'] == 'logo' ) {
        $sMsgText = '<br/><img src="'.WDCPREFIX.'/users/'.$aOne['login'].'/logo/'.$aOne['new_val'].'" border="0">';
    }
    
    $aOne['is_sent']       = '0';
    $aOne['context_code']  = '1';
    $aOne['context_link']  = $sLink;
    $aOne['context_title'] = $aOne['uname'] .' '. $aOne['usurname'] . ' ['. $aOne['login'] .']';
    
    $sJSParams = "{'content_id': ". $nContentId .", 'change_id': '{$aOne['id']}', 'ucolumn': '{$aOne['ucolumn']}', 'utable': '{$aOne['utable']}', 'stream_id': '{$aStream['stream_id']}', 'content_cnt': $nCnt, 'status': $status, 'is_sent': '{$aOne['is_sent']}'}";
    $sEditIcon = _parseEditIcon( 'admEditProfile', $aOne['user_id'], $status, $sKind, $sJSParams );
    $sJSParams = "{'content_id': ". $nContentId .", 'stream_id': '{$aStream['stream_id']}', 'content_cnt': $nCnt, 'status': $status, 'is_sent': '{$aOne['is_sent']}'}";
    
    $sUserClass = is_emp($aOne['role']) ? '6db335' : 'fd6c30';
    $sPRO = $aOne['moderator_status'] == -1 ? '<div class="b-post__txt b-post__txt_fontsize_11"><span style="color: #cc4642;">У пользователя был аккаунт PRO</span></div>' : '';
    $sPro = $aOne['is_pro'] == 't' ? preg_replace('#<a[^>]+>(.+)</a>#', '$1', (is_emp($aOne['role']) ? view_pro_emp() : view_pro2( $aOne['is_pro_test'] == 't' ? true : false))).'&nbsp;' : ''; 

    $sReturn .= '
<div class="b-post b-post_bordtop_dfe3e4 b-post_padtop_15 b-post_marg_20_10" id="my_div_content_'. $aOne['content_id'] .'_'. $aOne['id'] .'_'. $sKind .'">
    '. _parseHidden( $aOne, $sKind ) .'
    '. _parseOkIcon( $status, $aOne['content_id'], $aOne['id'], $sKind, $aOne['user_id'] ) .'
    '. _parsePostTime( $status, $aOne['post_time'] ) .'
    <div class="b-username b-username_padbot_5">'. ($aOne['is_team'] == 't' ? $sTeam : $sPro ) .'<a class="b-username__link b-username__link_color_'. $sUserClass .' b-username__link_fontsize_11 b-username__link_bold" href="/users/'. $aOne['login'] . $sLinkSuff . '" target="_blank">'. $aOne['uname'] .' '. $aOne['usurname'] .' ['. $aOne['login'] .']</a></div>
    '. ( $aOne['warn'] ? '<div class="b-username_padbot_5"><a onclick="parent.user_content.getUserWarns('.$aOne['user_id'].');" href="javascript:void(0);" class="notice">Предупреждения:&nbsp;<span id="warn_'.$aOne['user_id'].'_'. user_content::MODER_PROFILE .'_'. $aOne['id'] .'">'. intval($aOne['warn']) .'</span></a></div>' : '<div class="b-username_padbot_5 user-notice">Предупреждений нет</div>') . '
    '. $sPRO .'
    '. _parseMass( $aOne, $status, $sKind ) .'
    <div class="b-post__txt b-post__txt_fontsize_15"><span class="b-post__bold">'. $sColumn .'</span>: '. $sMsgText .'</div>
    '. _parseDelIcons( $aOne, 'user_id', $status, $sKind, $sJSParams, $sEditIcon ) .'
</div>';
    
    return $sReturn;
}



/**
 * Парсит порцию HTML содержимого для изменений в реквизитах финансов
 * 
 * @param  int $last_id возвращает последний Id проектов
 * @param  array $content массив данных из базы
 * @param  int $status статус: 0 - не проверенно, 1 - утверждено, 2 - удалено
 * @param  array $aStream данные о потоке
 * @return string HTML
 */
function _parseSbrReqv( &$last_id, $content = array(), $status = 0, $aStream = array() )
{
    $sReturn = '';
    $nCnt    = count($content);
    
    foreach ($content as $aOne)
    {
        $link = sprintf('%s/users/%s/setup/finance/', $GLOBALS['host'], $aOne['login']);
        
        $content = Template::render(ABS_PATH . '/templates/user_content/sbr_reqv/content.tpl.php', array(
            'sTitle' => $link,
            'sLink' => $link
        ));
        
        $sJSParams = "{'content_id': {$aOne['content_id']}, 'stream_id': '{$aStream['stream_id']}', 'content_cnt': $nCnt, 'status': $status, 'is_sent': '{$aOne['is_sent']}'}";
        $sEditIcon = '';//_parseEditIcon( 'admEditTServices', $aOne['id'], $status, '0', $sJSParams );
        
        $sReturn .= Template::render(ABS_PATH . '/templates/user_content/layout.tpl.php', array(
            'content_id' => $aOne['content_id'],
            'id' => $aOne['id'],
            '_parseHidden' => _parseHidden($aOne),
            '_parseOkIcon' => _parseOkIcon($status, $aOne['content_id'], $aOne['id'], '0', $aOne['user_id']),
            '_parsePostTime' => _parsePostTime($status, $aOne['last']),
            'login' => $aOne['login'],
            'user_fullname' => view_fullname($aOne),
            'user_status' => view_mark_user($aOne),
            '_parseDelIcons' => _parseDelIcons($aOne, 'user_id', $status, '0', $sJSParams, $sEditIcon),
            'warn_class' => !$aOne['warn']?'user-notice':'',
            'warn' => $aOne['warn']?'<a onclick="parent.user_content.getUserWarns('.$aOne['user_id'].');" href="javascript:void(0);" class="notice">Предупреждения:&nbsp;<span id="warn_'.$aOne['user_id'].'_'. user_content::MODER_SBR_REQV .'_'. $aOne['id'] .'">'. intval($aOne['warn']) .'</span></a>':'Предупреждений нет',
            '_parseMass' => _parseMass($aOne, $status, '0'),
            'content' => $content 
        ));
    }
    
    return $sReturn;
}



function _parseTServices( &$last_id, $content = array(), $status = 0, $aStream = array() )
{
    global /*$user_content, */ $stop_words, $sTeam;
    
    //Общий шаблончик типовой услуги в потоке
    $_template = <<<EOT
<div class="b-post b-post_bordtop_dfe3e4 b-post_padtop_15 b-post_marg_20_10" id="my_div_content_{{content_id}}_{{id}}_0">
   {{_parseHidden}}
   {{_parseOkIcon}}
   {{_parsePostTime}}
   <div class="b-username b-username_padbot_5"> 
        {{user_status}}
        <a class="b-username__link b-username__link_color_fd6c30 b-username__link_fontsize_11 b-username__link_bold" href="/users/{{login}}" target="_blank">
            {{user_fullname}}
        </a>     
   </div>
   <div class="b-username_padbot_5 {{warn_class}}">{{warn}}</div>
   {{_parseMass}}
   <div class="b-post__txt b-post__txt_padbot_20 b-post__txt_fontsize_15">
       <table class="b-layout__table" cellspacing="0" cellpadding="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__cell_width_100ps">
                    <a class="b-post__link b-post__link_bold b-post__link_fontsize_15" href="{{sLink}}" target="_blank">{{sTitle}}</a>
                </td>
                <td class="b-layout__txt_nowrap b-layout__txt_padleft_20">
                    {{sDays}}
                </td>
                <td class="b-layout__txt_nowrap b-layout__txt_padleft_20">
                    {{sPrice}}
                </td>
            </tr>
       </table> 
   </div>    
   <div class="b-post__txt b-post__txt_fontsize_15 b-post__txt_padbot_10">
        <i><span class="b-post__bold">Раздел: </span> {{sCategory}}   </i>
   </div>
   <div class="b-post__txt b-post__txt_fontsize_15 b-post__bold b-post__txt_padbot_10">
        Что вы получите
   </div>
   <div class="b-post__txt b-post__txt_padbot_20 b-post__txt_fontsize_15">
       {{sDescr}}
   </div>         
   <div class="b-post__txt b-post__txt_fontsize_15 b-post__bold b-post__txt_padbot_10">
        Что нужно, чтобы начать
   </div>
   <div class="b-post__txt b-post__txt_padbot_20 b-post__txt_fontsize_15">
       {{sReq}}
   </div>    
   {{sExtra}}
   {{sExpress}}
   {{_parseDelIcons}}         
</div>
EOT;
    
    //Шаблон доп.опций
    $_extra_template = <<<EOT
   <div class="b-post__txt b-post__txt_fontsize_15 b-post__bold b-post__txt_padbot_10">
        Дополнительно        
   </div>
   <div class="b-post__txt b-post__txt_padbot_20 b-post__txt_fontsize_15">         
        <table class="b-layout__table" cellspacing="0" cellpadding="0" border="0">
           {{sItems}}
        </table>
   </div>
EOT;
    //Шаблон элемента доп.опций
    $_extra_item_template = <<<EOT
    <tr class="b-layout__tr">
       <td class="b-layout__cell_width_100ps">{{sTitle}}</td>
       <td class="b-layout__txt_nowrap b-layout__txt_padleft_20">{{sDays}}</td>
       <td class="b-layout__txt_nowrap b-layout__txt_padleft_20">{{sPrice}}</td>
    </tr>
EOT;
    
    //Шаблон срочного выполнения работы
    $_express_template = <<<EOT
    <div class="b-post__txt b-post__txt_fontsize_15 b-post__bold b-post__txt_padbot_10">
       Срочность 
    </div>    
    <div class="b-post__txt b-post__txt_padbot_20 b-post__txt_fontsize_15">
        <table class="b-layout__table" cellspacing="0" cellpadding="0" border="0">
           <tr class="b-layout__tr">
                <td class="b-layout__cell_width_100ps">
                    Могу выполнить срочно за {{sDays}}
                </td>
                <td class="b-layout__txt_nowrap b-layout__txt_padleft_20">
                    {{sPrice}}
                </td>
           </tr> 
        </table>        
    </div>
EOT;
    

    
    $sReturn = '';
    $nCnt    = count($content);
    
    foreach ($content as $aOne)
    {
        $last_id = $aOne['moder_num'];
        
        $sLink = sprintf('%s/tu/%d/%s.html','',$aOne['id'],translit(strtolower(htmlspecialchars_decode($aOne['title'], ENT_QUOTES))));
        $sPro = $aOne['is_pro'] == 't' ? preg_replace('#<a[^>]+>(.+)</a>#', '$1', view_pro_emp()).'&nbsp;' : ''; 

        $sTitle   = xmloutofrangechars($aOne['title']);
        $sTitle   = $status != 1 ? $stop_words->replace($sTitle) : $sTitle;
        $sTitle   = reformat( $sTitle, 30, 0, 1 );
        
        $sDescr   = xmloutofrangechars($aOne['description']);
        $sDescr   = $status != 1 ? $stop_words->replace($sDescr) : $sDescr;
        $sDescr   = reformat($sDescr, 50);
        
        $sRequirement = xmloutofrangechars($aOne['requirement']);
        $sRequirement   = $status != 1 ? $stop_words->replace($sRequirement) : $sRequirement;
        $sRequirement   = reformat($sRequirement, 50);
        
        
        $extra = array();
        
        if($aOne['extra'])
        {
            $string = preg_replace('!s:(\d+):"(.*?)";!se', "'s:'.strlen('$2').':\"$2\";'", $aOne['extra']);
            $extra = unserialize($string);
        }        
        
        $extra_html = '';
        
        if(count($extra))
        {
            foreach($extra as $item)
            {
                $_data = array(
                    'sTitle' => xmloutofrangechars($item['title']),
                    'sPrice' => number_format($item['price'],0,',',' ') . ' руб.',
                    'sDays' => $item['days'] . ' ' . ending($item['days'], 'день', 'дня', 'дней')
                );
                
                $keys = array_map(function($el){return "{{{$el}}}";}, array_keys($_data));
                $values = array_values($_data);            
                $extra_html .= str_replace($keys, $values, $_extra_item_template);
            }
            
            $extra_html = str_replace('{{sItems}}', $extra_html, $_extra_template);
        }
        
        
        $express_html = '';
        
        if($aOne['is_express'] == 't')
        {
            $express_html = str_replace(array(
                '{{sDays}}',
                '{{sPrice}}'
            ), array(
                $aOne['express_days'] . ' ' . ending($aOne['express_days'], 'день', 'дня', 'дней'),
                number_format($aOne['express_price'],0,',',' ') . ' руб.'
            ), $_express_template);
        }

        
        
        $aOne['is_sent']       = '0';
        $aOne['context_code']  = '1';///???
        $aOne['context_link']  = $sLink;
        $aOne['context_title'] = $aOne['uname'] .' '. $aOne['usurname'] . ' ['. $aOne['login'] .']';
        
        
        $sJSParams = "{'content_id': {$aOne['content_id']}, 'stream_id': '{$aStream['stream_id']}', 'content_cnt': $nCnt, 'status': $status, 'is_sent': '{$aOne['is_sent']}'}";
        $sEditIcon = '';//_parseEditIcon( 'admEditTServices', $aOne['id'], $status, '0', $sJSParams );
    
        
        
        $_data = array(
            'content_id' => $aOne['content_id'],
            'id' => $aOne['id'],
            '_parseHidden' => _parseHidden($aOne),
            '_parseOkIcon' => _parseOkIcon($status, $aOne['content_id'], $aOne['id'], '0', $aOne['user_id']),
            '_parsePostTime' => _parsePostTime($status, $aOne['date']),
            'login' => $aOne['login'],
            'user_status' => $aOne['is_team'] == 't' ? $sTeam : $sPro,
            'user_fullname' => $aOne['context_title'],
            'warn_class' => !$aOne['warn']?'user-notice':'',
            'warn' => $aOne['warn']?'<a onclick="parent.user_content.getUserWarns('.$aOne['user_id'].');" href="javascript:void(0);" class="notice">Предупреждения:&nbsp;<span id="warn_'.$aOne['user_id'].'_'. user_content::MODER_TSERVICES .'_'. $aOne['id'] .'">'. intval($aOne['warn']) .'</span></a>':'Предупреждений нет',
            '_parseDelIcons' => _parseDelIcons($aOne, 'user_id', $status, '0', $sJSParams, $sEditIcon),
            '_parseMass' => _parseMass($aOne, $status, '0'),
            'sTitle' => $sTitle,
            'sLink' => $sLink,
            'sDescr' => $sDescr,
            'sReq' => $sRequirement,
            'sPrice' => number_format($aOne['price'],0,',',' ') . ' руб.',
            'sDays' => $aOne['days'] . ' ' . ending($aOne['days'], 'день', 'дня', 'дней'),
            'sExtra' => $extra_html,
            'sExpress' => $express_html,
            'sCategory' => $aOne['category_group_title'] . ($aOne['category_spec_title']?' &rarr; ' . $aOne['category_spec_title']:'')
        );
        
        
        $keys = array_map(function($el){return "{{{$el}}}";}, array_keys($_data));
        $values = array_values($_data);
        $sReturn .= str_replace($keys, $values, $_template);
    }
    
    
    
   // $sReturn = print_r($content,true);
    
    
    return $sReturn;
}






/**
 * Парсит порцию HTML содержимого для комментариев к предложениям по проектам
 * 
 * @param  int $last_id возвращает последний Id проектов
 * @param  array $content массив данных из базы
 * @param  int $status статус: 0 - не проверенно, 1 - утверждено, 2 - удалено
 * @param  array $aStream данные о потоке
 * @return string HTML
 */
function _parseProjectsDialog( &$last_id, $content = array(), $status = 0, $aStream = array() ) {
    global $stop_words;
    
    $nCnt    = count($content);
    $sReturn = '';
    
    foreach ( $content as $aOne ) {
        $last_id  = $aOne['moder_num'];
        $sReturn .= _parseProjectsDialogOne( $aOne, $status, $aOne['kind'], $aStream, $nCnt, user_content::MODER_PRJ_DIALOG );
    }
    
    return $sReturn;
}

/**
 * Парсит HTML одного комментария к предложениям по проектам
 * 
 * @param  array $aOne массив с данными комментария
 * @param  int $status статус: 0 - не проверенно, 1 - утверждено, 2 - удалено
 * @param  string $sKind опционально. тип записи
 * @param  array $aStream данные о потоке
 * @param  int $nCnt количество записей в потоке
 * @param  int $nContentId идентификатор сущности из admin_contents (фактический из потоков, то есть со сборными)
 * @return string HTML
 */
function _parseProjectsDialogOne( $aOne = array(), $status = 0, $sKind = '0', $aStream = array(), $nCnt = 0, $nContentId = 0 ) {
    global $stop_words, $user_content, $sTeam;
    
    $sReturn = '';
    $sLink    = $GLOBALS['host'] . getFriendlyURL( 'project', $aOne['src_id'] );
    $sDescr   = xmloutofrangechars(($sKind != 4 && $status != 1) ? $stop_words->replace( rtrim(strip_tags($aOne['post_text'])), 'html', true, 'suspect' ) : $aOne['post_text']);
    $sOffice  = $sKind != 4 ? '' : '<span style="color: #cc4642;"> Проект в офис. В ответах разрешен обмен контактами.</span>';
    
    $aOne['context_code']  = '3';
    $aOne['context_link']  = $sLink;
    $aOne['context_title'] = xmloutofrangechars( $aOne['src_name'] );
    
    $sJSParams = "{'content_id': $nContentId, 'stream_id': '{$aStream['stream_id']}', 'content_cnt': $nCnt, 'status': $status, 'is_sent': '{$aOne['is_sent']}'}";
    $sEditIcon = _parseEditIcon( 'admEditPrjDialog', $aOne['id'], $status, $sKind, $sJSParams );
    
    $sUserClass = is_emp($aOne['role']) ? '6db335' : 'fd6c30';
    
    $bIsModer = $user_content->hasContentPermissions( $nContentId, permissions::getUserPermissions($aOne['user_id']) );
    $sModified = ($aOne['moduser_id'] && ($aOne['moduser_id'] != $aOne['user_id'] || $bIsModer) ) ? '<div class="b-post__txt b-post__txt_padbot_15"><span style="color:red;">Сообщение было отредактировано. '. ($aOne['modified_reason'] ? 'Причина: '.$aOne['modified_reason'] : 'Без причины') .'</span></div>' : '';
    
    $sPro = $aOne['is_pro'] == 't' ? preg_replace('#<a[^>]+>(.+)</a>#', '$1', (is_emp($aOne['role']) ? view_pro_emp() : view_pro2( $aOne['is_pro_test'] == 't' ? true : false))).'&nbsp;' : ''; 
    
    $sReturn .= '
<div class="b-post b-post_bordtop_dfe3e4 b-post_padtop_15 b-post_marg_20_10" id="my_div_content_'. $aOne['content_id'] .'_'. $aOne['id'] .'_'. $sKind .'">
    '. _parseHidden( $aOne, $sKind ) .'
    '. _parseOkIcon( $status, $aOne['content_id'], $aOne['id'], $sKind, $aOne['user_id'] ) .'
    '. _parsePostTime( $status, $aOne['post_time'] ) .'
    <div class="b-username b-username_padbot_5">'. ($aOne['is_team'] == 't' ? $sTeam : $sPro ) .'<a class="b-username__link b-username__link_color_'. $sUserClass .' b-username__link_fontsize_11 b-username__link_bold" href="/users/'. $aOne['login'] .'" target="_blank">'. $aOne['uname'] .' '. $aOne['usurname'] .' ['. $aOne['login'] .']</a></div>
    '. ( $aOne['warn'] ? '<div class="b-username_padbot_5"><a onclick="parent.user_content.getUserWarns('.$aOne['user_id'].');" href="javascript:void(0);" class="notice">Предупреждения:&nbsp;<span id="warn_'.$aOne['user_id'].'_'. $aOne['content_id'] .'_'. $aOne['id'] .'">'. intval($aOne['warn']) .'</span></a></div>' : '<div class="b-username_padbot_5 user-notice">Предупреждений нет</div>') . '
    '. $sOffice .'
    '. _parseMass( $aOne, $status, $sKind ) .'
    <div class="b-post__txt b-post__txt_padbot_5 b-post__txt_fontsize_15">'. reformat($sDescr, 50, 0, 0, 1) .'</div>
    '. $sModified .'
    <div class="b-post__txt b-post__txt_fontsize_11"><img class="b-post__pic b-post__pic_valign_mid" src="/images/frame-prj.png" alt="" /> <a class="b-post__link b-post__link_fontsize_11" href="'. $sLink .'?#comment_'. $aOne['offer_id'] .'_'. $aOne['id'] .'" target="_blank">'. reformat(xmloutofrangechars($aOne['src_name']), 30, 0, 1) .'</a></div>
    '. _parseDelIcons( $aOne, 'user_id', $status, $sKind, $sJSParams, $sEditIcon ) .'
</div>';
    
    return $sReturn;
}

/**
 * Парсит порцию HTML содержимого для комментариев к предложениям конкурсов
 * 
 * @param  int $last_id возвращает последний Id проектов
 * @param  array $content массив данных из базы
 * @param  int $status статус: 0 - не проверенно, 1 - утверждено, 2 - удалено
 * @param  array $aStream данные о потоке
 * @return string HTML
 */
function _parseContestComments( &$last_id, $content = array(), $status = 0, $aStream = array() ) {
    $nCnt    = count($content);
    $sReturn = '';
    
    foreach ( $content as $aOne ) {
        $last_id  = $aOne['moder_num'];
        $sReturn .= _parseContestCommentsOne( $aOne, $status, '0', $aStream, $nCnt, user_content::MODER_CONTEST_COM );
    }
    
    return $sReturn;
}

/**
 * Парсит HTML одного комментария к предложениям конкурсов
 * 
 * @param  array $aOne массив с данными комментария
 * @param  int $status статус: 0 - не проверенно, 1 - утверждено, 2 - удалено
 * @param  string $sKind опционально. тип записи
 * @param  array $aStream данные о потоке
 * @param  int $nCnt количество записей в потоке
 * @param  int $nContentId идентификатор сущности из admin_contents (фактический из потоков, то есть со сборными)
 * @return string HTML
 */
function _parseContestCommentsOne( $aOne = array(), $status = 0, $sKind = '0', $aStream = array(), $nCnt = 0, $nContentId = 0 ) {
    global $stop_words, $sTeam;
    
    $sReturn = '';
    $sLink   = $GLOBALS['host'] . getFriendlyURL( 'project', $aOne['src_id'] ) . '?comm='. $aOne['id'] .'#comment-'. $aOne['id'];
    $sDescr  = $status != 1? $stop_words->replace( xmloutofrangechars(xmloutofrangechars($aOne['post_text'])), 'html', true, 'suspect' ): xmloutofrangechars(xmloutofrangechars($aOne['post_text']));
    
    $aOne['context_code']  = '3';
    $aOne['context_link']  = $sLink;
    $aOne['context_title'] = xmloutofrangechars( $aOne['src_name'] );
    
    $sUserClass = is_emp($aOne['role']) ? '6db335' : 'fd6c30';
    $sJSParams  = "{'content_id': $nContentId, 'stream_id': '{$aStream['stream_id']}', 'content_cnt': $nCnt, 'status': $status, 'is_sent': '{$aOne['is_sent']}'}";
    $sEditIcon  = _parseEditIcon( 'admEditContestCom', $aOne['id'], $status, $sKind, $sJSParams );
    
    $sPro = $aOne['is_pro'] == 't' ? preg_replace('#<a[^>]+>(.+)</a>#', '$1', (is_emp($aOne['role']) ? view_pro_emp() : view_pro2( $aOne['is_pro_test'] == 't' ? true : false))).'&nbsp;' : ''; 
    
    $sReturn .= '
<div class="b-post b-post_bordtop_dfe3e4 b-post_padtop_15 b-post_marg_20_10" id="my_div_content_'. $aOne['content_id'] .'_'. $aOne['id'] .'_'. $sKind .'">
    '. _parseHidden( $aOne, $sKind ) .'
    '. _parseOkIcon( $status, $aOne['content_id'], $aOne['id'], $sKind, $aOne['user_id'] ) .'
    '. _parsePostTime( $status, $aOne['post_time'] ) .'
    <div class="b-username b-username_padbot_5">'. ($aOne['is_team'] == 't' ? $sTeam : $sPro ) .'<a class="b-username__link b-username__link_color_'. $sUserClass .' b-username__link_fontsize_11 b-username__link_bold" href="/users/'. $aOne['login'] .'" target="_blank"">'. $aOne['uname'] .' '. $aOne['usurname'] .' ['. $aOne['login'] .']</a></div>
    '. ( $aOne['warn'] ? '<div class="b-username_padbot_5"><a onclick="parent.user_content.getUserWarns('.$aOne['user_id'].');" href="javascript:void(0);" class="notice">Предупреждения:&nbsp;<span id="warn_'.$aOne['user_id'].'_'. $aOne['content_id'] .'_'. $aOne['id'] .'">'. intval($aOne['warn']) .'</span></a></div>' : '<div class="b-username_padbot_5 user-notice">Предупреждений нет</div>') . '
    '. _parseMass( $aOne, $status, $sKind ) .'
    <div class="b-post__txt b-post__txt_padbot_5 b-post__txt_fontsize_15">'. reformat( $sDescr, 30, 0, 0, 1 ) .'</div>
    <div class="b-post__txt b-post__txt_fontsize_11"><img class="b-post__pic b-post__pic_valign_mid" src="/images/frame-kont.png" alt="" /> <a class="b-post__link b-post__link_fontsize_11" href="'. $sLink .'" target="_blank">'. reformat(xmloutofrangechars($aOne['src_name']), 30, 0, 1) .'</a></div>
    '. _parseDelIcons( $aOne, 'user_id', $status, $sKind, $sJSParams, $sEditIcon ) .'
</div>';
    
    return $sReturn;
}

/**
 * Парсит порцию HTML содержимого для уточнений к разделам в портфолио
 * 
 * @param  int $last_id возвращает последний Id проектов
 * @param  array $content массив данных из базы
 * @param  int $status статус: 0 - не проверенно, 1 - утверждено, 2 - удалено
 * @param  array $aStream данные о потоке
 * @return string HTML
 */
function _parsePortfChoice( &$last_id, $content = array(), $status = 0, $aStream = array() ) {
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/kwords.php' );
    
    $nCnt    = count($content);
    $sReturn = '';
    
    foreach ( $content as $aOne ) {
        $last_id  = $aOne['moder_num'];
        $sReturn .= _parsePortfChoiceOne( $aOne, $status, '0', $aStream, $nCnt, user_content::MODER_PORTF_CHOISE );
    }
    
    return $sReturn;
}

/**
 * Парсит HTML одного уточнения к разделам в портфолио
 * 
 * @param  array $aOne массив с данными комментария
 * @param  int $status статус: 0 - не проверенно, 1 - утверждено, 2 - удалено
 * @param  string $sKind опционально. тип записи
 * @param  array $aStream данные о потоке
 * @param  int $nCnt количество записей в потоке
 * @param  int $nContentId идентификатор сущности из admin_contents (фактический из потоков, то есть со сборными)
 * @return string HTML
 */
function _parsePortfChoiceOne( $aOne = array(), $status = 0, $sKind = '0', $aStream = array(), $nCnt = 0, $nContentId = 0 ) {
    global $stop_words, $sTeam;
    
    $sReturn  = '';
    $sMsgText = '';

    if ( $aOne['ucolumn'] == 'text' ) {
        $sMsgText = $status != 1? $stop_words->replace( xmloutofrangechars($aOne['new_val']), 'html', true, 'suspect' ): xmloutofrangechars($aOne['new_val']);
    }
    else {
        $user_keys = kwords::getUserKeys( $aOne['user_id'], $aOne['prof_id'] );
        $aTmp = array();
        
        if ( $user_keys ) {
            foreach ( $user_keys as $key ) { 
                $aTmp[] = $status != 1? $stop_words->replace( change_q_x(stripslashes(xmloutofrangechars($key))), 'html', true, 'suspect' ): change_q_x(stripslashes(xmloutofrangechars($key)));
            }

            $sMsgText = implode( ', ', $aTmp );
        }
    }

    $sLink    = $GLOBALS['host'] . '/users/' . $aOne['login'];
    $sColumn  = $aOne['ucolumn'] == 'text' ? 'Уточнения к разделу' : 'Ключевые слова';
    $sMsgText = reformat( $sMsgText, 54, 0, 1 );
    
    $aOne['is_sent']       = '0';
    $aOne['context_code']  = '1';
    $aOne['context_link']  = $sLink;
    $aOne['context_title'] = $aOne['uname'] .' '. $aOne['usurname'] . ' ['. $aOne['login'] .']';
    
    $sJSParams = "{'sProfId': {$aOne['prof_id']}, 'change_id': '{$aOne['id']}', 'content_id': $nContentId, 'stream_id': '{$aStream['stream_id']}', 'content_cnt': $nCnt, 'status': $status, 'is_sent': '{$aOne['is_sent']}'}";
    $sEditIcon = _parseEditIcon( 'admEditPortfChoice', $aOne['user_id'], $status, $sKind, $sJSParams );
    $sJSParams = "{'content_id': $nContentId, 'stream_id': '{$aStream['stream_id']}', 'content_cnt': $nCnt, 'status': $status, 'is_sent': '{$aOne['is_sent']}'}";
    $sPRO = $aOne['moderator_status'] == -1 ? '<div class="b-post__txt b-post__txt_fontsize_11"><span style="color: #cc4642;">У пользователя был аккаунт PRO</span></div>' : '';
    $sPro      = $aOne['is_pro'] == 't' ? view_pro2( $aOne['is_pro_test'] == 't' ? true : false) .'&nbsp;' : '';
    
    $sReturn .= '
<div class="b-post b-post_bordtop_dfe3e4 b-post_padtop_15 b-post_marg_20_10" id="my_div_content_'. $aOne['content_id'] .'_'. $aOne['id'] .'_'. $sKind .'">
    '. _parseHidden( $aOne, $sKind ) .'
    '. _parseOkIcon( $status, $aOne['content_id'], $aOne['id'], $sKind , $aOne['user_id'] ) .'
    '. _parsePostTime( $status, $aOne['post_time'] ) .'
    <div class="b-username b-username_padbot_5">'. ($aOne['is_team'] == 't' ? $sTeam : $sPro ) .'<a class="b-username__link b-username__link_color_fd6c30 b-username__link_fontsize_11 b-username__link_bold" href="/users/'. $aOne['login'] . '/#'.$aOne['prof_id'] . '" target="_blank">'. $aOne['uname'] .' '. $aOne['usurname'] .' ['. $aOne['login'] .']</a></div>
    '. ( $aOne['warn'] ? '<div class="b-username_padbot_5"><a onclick="parent.user_content.getUserWarns('.$aOne['user_id'].');" href="javascript:void(0);" class="notice">Предупреждения:&nbsp;<span id="warn_'.$aOne['user_id'].'_'. $aOne['content_id'] .'_'. $aOne['id'] .'">'. intval($aOne['warn']) .'</span></a></div>' : '<div class="b-username_padbot_5 user-notice">Предупреждений нет</div>') . '
    '. $sPRO .'
    '. _parseMass( $aOne, $status, $sKind ) .'
    <div class="b-post__txt b-post__txt_fontsize_15"><span class="b-post__bold">'. $sColumn .'</span>: '. $sMsgText .'</div>
    '. _parseDelIcons( $aOne, 'user_id', $status, $sKind, $sJSParams, $sEditIcon ) .'
</div>';
    
    return $sReturn;
}

/**
 * Парсит порцию HTML содержимого для работ в портфолио
 * 
 * @param  int $last_id возвращает последний Id проектов
 * @param  array $content массив данных из базы
 * @param  int $status статус: 0 - не проверенно, 1 - утверждено, 2 - удалено
 * @param  array $aStream данные о потоке
 * @return string HTML
 */
function _parsePortfolio( &$last_id, $content = array(), $status = 0, $aStream = array() ) {
    $sReturn = '';
    $nCnt    = count($content);
    
    foreach ( $content as $aOne ) {
        $last_id  = $aOne['moder_num'];
        $sReturn .= _parsePortfolioOne( $aOne, $status, '0', $aStream, $nCnt, user_content::MODER_PORTFOLIO );
    }
    
    return $sReturn;
}

/**
 * Парсит HTML одной работы в портфолио
 * 
 * @param  array $aOne массив с данными комментария
 * @param  int $status статус: 0 - не проверенно, 1 - утверждено, 2 - удалено
 * @param  string $sKind опционально. тип записи
 * @param  array $aStream данные о потоке
 * @param  int $nCnt количество записей в потоке
 * @param  int $nContentId идентификатор сущности из admin_contents (фактический из потоков, то есть со сборными)
 * @return string HTML
 */
function _parsePortfolioOne( $aOne = array(), $status = 0, $sKind = '0', $aStream = array(), $nCnt = 0, $nContentId = 0 ) {
    global $stop_words, $user_content, $sTeam;
    
    $sReturn = '';
    $sAttach = '';
    
    if ( $aOne['is_video'] == 't' ) { // работа есть видео
        if ( $aOne['prev_pict'] ) { // есть отдельно загруженное превью
            $sInner = '<img src="'.WDCPREFIX.'/users/'.$aOne['login'].'/upload/'.$aOne['prev_pict'].'" alt="'.$aOne['prev_pict'].'" title="'.$aOne['prev_pict'].'" />';
        }
        else { // нет отдельно загруженного превью
            $sInner = $aOne['video_link'];
        }
        
        $sAttach = '<div class="b-post__txt b-post__txt_padbot_15 b-post__txt_fontsize_15"><strong>Ссылка на YouTube/RuTube/Vimeo видео:</strong> <br/><a href="http://' . $aOne['video_link'] .'" target="_blank">' . $sInner .'</a></div>';
    }
    elseif ( $aOne['pict'] ) { // работа есть файл
        $ext      = CFile::getext( $aOne['pict'] );
        $preview  = $aOne['prev_pict'];
        $sPreview = '';
        
        if ( in_array($ext, $GLOBALS['graf_array']) && $ext != 'swf' ) { // работа есть картинка
            if ( $aOne['pict'] != substr($preview, 3, strlen($preview)) ) { // превью сделано не на основе оригинальной картинки либо вообще отсутствует
                $sInner = '<img src="'.WDCPREFIX.'/users/'.$aOne['login'].'/upload/tn_'.$aOne['pict'].'" alt="'.$aOne['pict'].'" title="'.$aOne['pict'].'" />';
                
                if ( $preview ) { // превью загружено отдельно
                    $sPreview = 'Превью: <br/><img src="'.WDCPREFIX.'/users/'.$aOne['login'].'/upload/'.$preview.'" alt="'.$preview.'" title="'.$preview.'" />';
                }
            }
            else { // превью сделано на основе оригинальной картинки
                $sInner = '<img src="'.WDCPREFIX.'/users/'.$aOne['login'].'/upload/'.$preview.'" alt="'.$preview.'" title="'.$preview.'" />';
            }
        }
        else { //работа не есть картинка
            if ( $preview ) {  // есть отдельно загруженное превью
                $sInner = '<img src="'.WDCPREFIX.'/users/'.$aOne['login'].'/upload/'.$preview.'" alt="'.$preview.'" title="'.$preview.'" />';
            }
            else {  // нет отдельно загруженного превью
                $sInner = 'Работа';
            }
        }
        
        $sAttach = '<div class="b-post__txt b-post__txt_padbot_15 b-post__txt_fontsize_15">
            <a href="' . WDCPREFIX . '/users/'. $aOne['login'] .'/upload/'. $aOne['pict'] .'" target="_blank">'. $sInner .'</a><br/>
            '. $sPreview .'
            </div>';
    }

    $txt_cost    = view_cost2( $aOne['cost'], '', '', false, $aOne['cost_type'] );
    $txt_time    = view_time( $aOne['time_value'], $aOne['time_type'] );
    $is_txt_time = ( $txt_cost != '' && $txt_time != '' );

    $sLink    = $GLOBALS['host'] . '/users/' . $aOne['login'];
    $sLink2   = $aOne['link'] ? '<div class="b-post__txt b-post__txt_padbot_15 b-post__txt_fontsize_15"><strong>Ссылка:</strong> <br/><a href="' . $aOne['link'] .'" target="_blank">' . $aOne['link'] .'</a></div>' : '';
    $sTitle   = $status != 1? $stop_words->replace( xmloutofrangechars($aOne['name']) ): xmloutofrangechars($aOne['name']);
    $sTitle   = reformat( $sTitle, 52, 0, 1 );
    $aOne['descr'] = nl2br($aOne['descr']); // грязный хак так как close_tags стала съедать переносы строк
    $sMessage = close_tags($aOne['descr'],'b,i,p,ul,li');
    $sMessage = $status != 1? $stop_words->replace( xmloutofrangechars($aOne['descr']) ): xmloutofrangechars($aOne['descr']);
    $sMessage = reformat( $sMessage, 60, 0, 0, 1 );
    
    $aOne['is_sent']       = '0';
    $aOne['context_code']  = '1';
    $aOne['context_link']  = $sLink;
    $aOne['context_title'] = $aOne['uname'] .' '. $aOne['usurname'] . ' ['. $aOne['login'] .']';
    
    $sJSParams = "{'content_id': $nContentId, 'stream_id': '{$aStream['stream_id']}', 'content_cnt': $nCnt, 'status': $status, 'is_sent': '{$aOne['is_sent']}'}";
    $sEditIcon = _parseEditIcon( 'admEditPortfolio', $aOne['id'], $status, $sKind, $sJSParams );
    
    $bIsModer = $user_content->hasContentPermissions( $nContentId, permissions::getUserPermissions($aOne['user_id']) );
    $sModified = ($aOne['moduser_id'] && ($aOne['moduser_id'] != $aOne['user_id'] || $bIsModer) ) ? '<div class="b-post__txt b-post__txt_padbot_15"><span style="color:red;">Работа была отредактирована. '. ($aOne['modified_reason'] ? 'Причина: '.$aOne['modified_reason'] : 'Без причины') .'</span></div>' : '';
    $sPRO = $aOne['moderator_status'] == -1 ? '<div class="b-post__txt b-post__txt_fontsize_11"><span style="color: #cc4642;">У пользователя был аккаунт PRO</span></div>' : '';
    $sPro      = $aOne['is_pro'] == 't' ? view_pro2( $aOne['is_pro_test'] == 't' ? true : false) .'&nbsp;' : ''; 
    $sReturn  .= '
<div class="b-post b-post_bordtop_dfe3e4 b-post_padtop_15 b-post_marg_20_10" id="my_div_content_'. $aOne['content_id'] .'_'. $aOne['id'] .'_'. $sKind .'">
    '. _parseHidden( $aOne, $sKind ) .'
    '. _parseOkIcon( $status, $aOne['content_id'], $aOne['id'], $sKind, $aOne['user_id'] ) .'
    '. _parsePostTime( $status, $aOne['post_time'] ) .'
    <div class="b-username b-username_padbot_5">'. ($aOne['is_team'] == 't' ? $sTeam : $sPro ) .'<a class="b-username__link b-username__link_color_fd6c30 b-username__link_fontsize_11 b-username__link_bold" href="/users/'. $aOne['login'] .'" target="_blank">'. $aOne['uname'] .' '. $aOne['usurname'] .' ['. $aOne['login'] .']</a></div>
    '. ( $aOne['warn'] ? '<div class="b-username_padbot_5"><a onclick="parent.user_content.getUserWarns('.$aOne['user_id'].');" href="javascript:void(0);" class="notice">Предупреждения:&nbsp;<span id="warn_'.$aOne['user_id'].'_'. $aOne['content_id'] .'_'. $aOne['id'] .'">'. intval($aOne['warn']) .'</span></a></div>' : '<div class="b-username_padbot_5 user-notice">Предупреждений нет</div>') . '
    '. $sPRO .'
    '. _parseMass( $aOne, $status, $sKind ) .'
    <div class="b-post__txt b-post__txt_padbot_10 b-post__txt_fontsize_15"><span class="b-post__bold">Новая работа:</span> <a class="b-post__link b-post__link_fontsize_15" href="/users/'. $aOne['login'] .'/viewproj.php?prjid='. $aOne['id'] .'" target="_blank">'. $sTitle .'</a></div>
    <div class="b-post__txt b-post__txt_fontsize_15">'. $sMessage .'</div>
    <div class="b-post__txt b-post__txt_fontsize_15">'. $txt_cost . ($is_txt_time ? ', ' : '') . ($txt_time != '' ? $txt_time : '') .'</div>

    '. $sAttach .'
    '. $sLink2 .'

    '. $sModified .'
    '. _parseDelIcons( $aOne, 'user_id', $status, $sKind, $sJSParams, $sEditIcon ) .'
</div>';
    
    return $sReturn;
}

/**
 * Парсит порцию HTML содержимого для Предложения фрилансеров "Сделаю"
 * 
 * @param  int $last_id возвращает последний Id проектов
 * @param  array $content массив данных из базы
 * @param  int $status статус: 0 - не проверенно, 1 - утверждено, 2 - удалено
 * @param  array $aStream данные о потоке
 * @return string HTML
 */
function _parseSdelau( &$last_id, $content = array(), $status = 0, $aStream = array() ) {
    $nCnt    = count($content);
    $sReturn = '';
    
    foreach ( $content as $aOne ) {
        $last_id  = $aOne['moder_num'];
        $sReturn .= _parseSdelauOne( $aOne, $status, '0', $aStream, $nCnt, user_content::MODER_SDELAU );
    }
    
    return $sReturn;
}

/**
 * Парсит HTML одного Предложения фрилансеров "Сделаю"
 * 
 * @param  array $aOne массив с данными комментария
 * @param  int $status статус: 0 - не проверенно, 1 - утверждено, 2 - удалено
 * @param  string $sKind опционально. тип записи
 * @param  array $aStream данные о потоке
 * @param  int $nCnt количество записей в потоке
 * @param  int $nContentId идентификатор сущности из admin_contents (фактический из потоков, то есть со сборными)
 * @return string HTML
 */
function _parseSdelauOne( $aOne = array(), $status = 0, $sKind = '0', $aStream = array(), $nCnt = 0, $nContentId = 0 ) {
    global $stop_words, $sTeam;
    
    $sReturn = '';
    $sTitle   = $status != 1? $stop_words->replace( htmlspecialchars(xmloutofrangechars($aOne['title'])) ): htmlspecialchars(xmloutofrangechars($aOne['title']));
    $sTitle   = reformat($sTitle, 35, 0, 1);
    $sMessage = $status != 1? $stop_words->replace( htmlspecialchars(xmloutofrangechars($aOne['post_text'])) ): htmlspecialchars(xmloutofrangechars($aOne['post_text']));
    $sMessage = reformat($sMessage, 50);
    $sProf    = xmloutofrangechars($aOne['src_name']) . ( $aOne['profname'] != 'Нет специализации' ? ' &rarr; <a class="b-freelancer__link" href="/freelancers/'. $aOne['link']. '/" target="_blank">'. $aOne['profname'] .'</a>' : '');
    
    $aOne['is_sent']       = '0';
    $aOne['context_code']  = '8';
    $aOne['context_link']  = '/sdelau/#offer' . $aOne['id'];
    $aOne['context_title'] = xmloutofrangechars( htmlspecialchars($aOne['title']) ) .' '. $aOne['usurname'] . ' ['. $aOne['login'] .']';
    
    $sPro = $aOne['is_pro'] == 't' ? preg_replace('#<a[^>]+>(.+)</a>#', '$1', view_pro2( $aOne['is_pro_test'] == 't' ? true : false)).'&nbsp;' : ''; 
    
    $sJSParams = "{'content_id': ". $nContentId .", 'stream_id': '{$aStream['stream_id']}', 'content_cnt': $nCnt, 'status': $status, 'is_sent': '{$aOne['is_sent']}'}";
    $sEditIcon = _parseEditIcon( 'admEditSdelau', $aOne['id'], $status, $sKind, $sJSParams );
    
    $sReturn .= '
<div class="b-post b-post_bordtop_dfe3e4 b-post_padtop_15 b-post_marg_20_10" id="my_div_content_'. $aOne['content_id'] .'_'. $aOne['id'] .'_'. $sKind .'">
    '. _parseHidden( $aOne, $sKind ) .'
    '. _parseOkIcon( $status, $aOne['content_id'], $aOne['id'], $sKind, $aOne['user_id'] ) .'
    '. _parsePostTime( $status, $aOne['post_time'] ) .'
    <div class="b-username b-username_padbot_5">'. ($aOne['is_team'] == 't' ? $sTeam : $sPro ) .'<a class="b-username__link b-username__link_color_fd6c30 b-username__link_fontsize_11 b-username__link_bold" href="/users/'. $aOne['login'] .'" target="_blank">'. $aOne['uname'] .' '. $aOne['usurname'] .' ['. $aOne['login'] .']</a></div>
    '. ( $aOne['warn'] ? '<div class="b-username_padbot_5"><a onclick="parent.user_content.getUserWarns('.$aOne['user_id'].');" href="javascript:void(0);" class="notice">Предупреждения:&nbsp;<span id="warn_'.$aOne['user_id'].'_'. $aOne['content_id'] .'_'. $aOne['id'] .'">'. intval($aOne['warn']) .'</span></a></div>' : '<div class="b-username_padbot_5 user-notice">Предупреждений нет</div>') . '
    '. _parseMass( $aOne, $status, $sKind ) .'
    <div class="b-post__txt b-post__txt_padbot_5 b-post__txt_fontsize_15"><span class="b-post__bold">Сделаю:</span> <a class="b-post__link b-post__link_fontsize_15" href="/users/'. $aOne['login'] .'" target="_blank">'. $sTitle .'</a></div>
    <div class="b-post__txt b-post__txt_padbot_5 b-post__txt_fontsize_15">'. $sMessage .'</div>
    <div class="b-post__txt b-post__txt_padtop_10 b-post__txt_fontsize_11"><span class="b-post__bold">Специализация:</span> '. $sProf .'</div>
    '. _parseDelIcons( $aOne, 'user_id', $status, $sKind, $sJSParams, $sEditIcon ) .'
</div>';
    
    return $sReturn;
}

/**
 * Парсит порцию HTML содержимого
 * Сборная: Предложения в проектах/конкурсах, комментарии к предложениям в проектах/конкурсах, Предложения фрилансеров Сделаю
 * 
 * @param  int $last_id возвращает последний Id проектов
 * @param  array $content массив данных из базы
 * @param  int $status статус: 0 - не проверенно, 1 - утверждено, 2 - удалено
 * @param  array $aStream данные о потоке
 * @return string HTML
 */
function _parsePrjCom( &$last_id, $content = array(), $status = 0, $aStream = array() ) {
    $sReturn = '';
    $nCnt    = count($content);
    
    foreach ( $content as $aOne ) {
        $last_id = $aOne['moder_num'];
        
        switch ( $aOne['content_id'] ) {
            case user_content::MODER_PRJ_OFFERS:
                $sReturn .= _parseProjectsOffersOne( $aOne, $status, $aOne['kind'], $aStream, $nCnt, user_content::MODER_PRJ_COM );
                break;
            case user_content::MODER_PRJ_DIALOG:
                $sReturn .= _parseProjectsDialogOne( $aOne, $status, $aOne['kind'], $aStream, $nCnt, user_content::MODER_PRJ_COM );
                break;
            case user_content::MODER_CONTEST_COM:
                $sReturn .= _parseContestCommentsOne( $aOne, $status, '0', $aStream, $nCnt, user_content::MODER_PRJ_COM );
                break;
            case user_content::MODER_SDELAU:
                $sReturn .= _parseSdelauOne( $aOne, $status, '0', $aStream, $nCnt, user_content::MODER_PRJ_COM );
                break;
            default:
                $sReturn .= '';
                break;
        }
    }
    
    return $sReturn;
}

/**
 * Парсит порцию HTML содержимого
 * Сборная: Комментарии: магазин, статьи
 * 
 * @param  int $last_id возвращает последний Id проектов
 * @param  array $content массив данных из базы
 * @param  int $status статус: 0 - не проверенно, 1 - утверждено, 2 - удалено
 * @param  array $aStream данные о потоке
 * @return string HTML
 */
function _parseComments( &$last_id, $content = array(), $status = 0, $aStream = array() ) {
    $sReturn = '';
    $nCnt    = count($content);
    
    foreach ( $content as $aOne ) {
        $last_id = $aOne['moder_num'];
        
        switch ( $aOne['content_id'] ) {
            case user_content::MODER_ART_COM:
                $sReturn .= _parseArticleCommentOne( $aOne, $status, '0', $aStream, $nCnt, user_content::MODER_COMMENTS );
                break;
            default:
                $sReturn .= '';
                break;
        }
    }
    
    return $sReturn;
}

/**
 * Парсит порцию HTML содержимого
 * Сборная: Работы в портфолио, Уточнения к разделам в портфолио
 * 
 * @param  int $last_id возвращает последний Id проектов
 * @param  array $content массив данных из базы
 * @param  int $status статус: 0 - не проверенно, 1 - утверждено, 2 - удалено
 * @param  array $aStream данные о потоке
 * @return string HTML
 */
function _parsePortfUnited( &$last_id, $content = array(), $status = 0, $aStream = array() ) {
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/kwords.php' );
    
    $nCnt    = count($content);
    $sReturn = '';
    
    foreach ( $content as $aOne ) {
        $last_id = $aOne['moder_num'];
        
        switch ( $aOne['content_id'] ) {
            case user_content::MODER_PORTF_CHOISE:
                $sReturn .= _parsePortfChoiceOne( $aOne, $status, '0', $aStream, $nCnt, user_content::MODER_PORTF_UNITED );
                break;
            case user_content::MODER_PORTFOLIO:
                $sReturn .= _parsePortfolioOne( $aOne, $status, '0', $aStream, $nCnt, user_content::MODER_PORTF_UNITED );
                break;
            default:
                $sReturn .= '';
                break;
        }
    }
    
    return $sReturn;
}

/**
 * Парсит порцию HTML содержимого
 * Сборная: Блоги: посты и комментарии, Комментарии в Комментарии в статьях
 * 
 * @param  int $last_id возвращает последний Id проектов
 * @param  array $content массив данных из базы
 * @param  int $status статус: 0 - не проверенно, 1 - утверждено, 2 - удалено
 * @param  array $aStream данные о потоке
 * @return string HTML
 */
function _parseBlogsUnited( &$last_id, $content = array(), $status = 0, $aStream = array() ) {
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/blogs.php' );
    
    $blogs   = new blogs;
    $groups  = $blogs->GetThemes( $error, 0 );
    $sReturn = '';
    $nCnt    = count($content);
    
    foreach ( $content as $aOne ) {
        $last_id = $aOne['moder_num'];
        
        switch ( $aOne['content_id'] ) {
            case user_content::MODER_BLOGS:
                $sReturn .= _parseBlogOne( $aOne, $status, $aStream, $nCnt, user_content::MODER_BLOGS_UNITED, $groups );
                break;
            case user_content::MODER_ART_COM:
                $sReturn .= _parseArticleCommentOne( $aOne, $status, '0', $aStream, $nCnt, user_content::MODER_BLOGS_UNITED );
                break;
            default:
                $sReturn .= '';
                break;
        }
    }
    
    return $sReturn;
}

/**
 * Парсит порцию HTML содержимого
 * Сборная: Изменения в профилях и Уточнения к разделам в портфолио
 * 
 * @param  int $last_id возвращает последний Id проектов
 * @param  array $content массив данных из базы
 * @param  int $status статус: 0 - не проверенно, 1 - утверждено, 2 - удалено
 * @param  array $aStream данные о потоке
 * @return string HTML
 */
function _parseUserUnited( &$last_id, $content = array(), $status = 0, $aStream = array() ) {
    require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/kwords.php' );
    
    $nCnt    = count($content);
    $sReturn = '';
    
    foreach ( $content as $aOne ) {
        $last_id = $aOne['moder_num'];
        
        switch ( $aOne['content_id'] ) {
            case user_content::MODER_PORTF_CHOISE:
                $sReturn .= _parsePortfChoiceOne( $aOne, $status, '0', $aStream, $nCnt, user_content::MODER_USER_UNITED );
                break;
            case user_content::MODER_PROFILE:
                $sReturn .= _parseProfileOne( $aOne, $status, '0', $aStream, $nCnt, user_content::MODER_USER_UNITED );
                break;
            default:
                $sReturn .= '';
                break;
        }
    }
    
    return $sReturn;
}

/**
 * Парсит контролы редактирования записи
 * 
 * @param  string $sFunc название метода adm_edit_content.js
 * @param  int $sId ID записи
 * @param  int $status статус: 0 - не проверенно, 1 - утверждено, 2 - удалено
 * @param  string $sKind тип записи
 * @param  string $sObjParams javascript объект с дополнительными параметрами
 * @return string 
 */
function _parseEditIcon( $sFunc = '', $sId = 0, $status = 2, $sKind = '0', $sObjParams = '{}' ) {
    $sEditIcon = '';
    $sDrawFunc = $status == 20 ? 'blocked' : 'stream'. $status;
    $sParent   = $status != 20 ? 'parent.' : '';
    
    if ( $sFunc && $sId ) {
        $sEditTitle = $status == 1 ? 'Редактировать' : 'Редактировать и утвердить';
        $sEditIcon  = '<a onclick="'. $sParent .'adm_edit_content.editContent(\''. $sFunc .'\', \''. $sId .'_'. $sKind .'\', 0, \''. $sDrawFunc .'\', '. $sObjParams .')" href="javascript:void(0);" class="b-button b-button_mini b-button_margleft_10 b-button_float_right" title="'. $sEditTitle .'"><span class="b-button__icon b-button__icon_edit"></span></a>';
    }
    
    return $sEditIcon;
}

/**
 * Парсит контролы утверждения записи
 * 
 * @param  int $status статус: 0 - не проверенно, 1 - утверждено, 2 - удалено
 * @param  int $content_id идентификатор сущности из admin_contents
 * @param  int $sId ID записи
 * @param  string $sKind тип записи
 * @param  type $sUserId UID автора
 * @return string 
 */
function _parseOkIcon( $status = 2, $content_id = 0, $sId = 0, $sKind = '0', $sUserId = 0 ) {
    return ($status == 1 || $status == 20) ? '' : '<a onclick="'.($content_id==22 ? "yaCounter6051055.reachGoal('confirm_tu');" : "").' user_content.resolveContent(\''. $content_id .'_'. $sId .'_'. $sKind .'\', 1, '. $sUserId .');" class="b-button b-button_margleft_10 b-button_float_right b-button_margtop_-4 b-button_mini" href="javascript:void(0);" title="Утвердить"><span class="b-button__icon b-button__icon_ok"></span></a>';
}

/**
 * Парсит дату/время создания записи
 * 
 * @param  int $status статус: 0 - не проверенно, 1 - утверждено, 2 - удалено
 * @param  type $time  дата/время создания
 * @return string 
 */
function _parsePostTime( $status = 2, $time = 0 ) {
    $time = strtotime( $time );
    //return '<div class="b-post__time b-post__time_float_right b-post__time_bordbot_b2">'. ($status == 20 ? date('d.m.Y в H:i', $time) : date('H:i', $time)) .'</div>';
    return '<div class="b-post__time b-post__time_float_right b-post__time_bordbot_b2">'. date('d.m.Y в H:i', $time) .'</div>';
}

/**
 * Парсит массовое утверждение
 * 
 * @param  array $aOne массив с данными о записи
 * @param  string $sKind опционально. тип записи
 * @param  int $status статус: 0 - не проверенно, 1 - утверждено, 2 - удалено
 * @return string 
 */
function _parseMass( $aOne = array(), $status = 2, $sKind = '0' ) {
    return ($status == 1 || $status == 20) ? '' : '<div class="b-check b-check_float_left b-check_padright_5 b-check_padtop_3"><input class="b-check__input" type="checkbox"  id="mass_sid_'. $aOne['content_id'] .'_'. $aOne['id'] .'_'. $sKind .'" value="'. $aOne['content_id'] .'_'. $aOne['id'] .'_'. $sKind .'" /></div>';
}

/**
 * Парсит скрытые поля
 * 
 * @param  array $aOne массив с данными о записи
 * @param  string $sKind опционально. тип записи
 * @return string 
 */
function _parseHidden( $aOne = array(), $sKind = '0' ) {
    return '<input type="hidden" name="is_sent_'. $aOne['content_id'] .'_'. $aOne['id'] .'_'. $sKind .'" id="is_sent_'. $aOne['content_id'] .'_'. $aOne['id'] .'_'. $sKind .'" value="'. $aOne['is_sent'] .'">
    <input type="hidden" name="ccode_'. $aOne['content_id'] .'_'. $aOne['id'] .'_'. $sKind .'" id="ccode_'. $aOne['content_id'] .'_'. $aOne['id'] .'_'. $sKind .'" value="'. $aOne['context_code'] .'">
    <input type="hidden" name="curl_'. $aOne['content_id'] .'_'. $aOne['id'] .'_'. $sKind .'" id="curl_'. $aOne['content_id'] .'_'. $aOne['id'] .'_'. $sKind .'" value="'. $aOne['context_link'] .'">
    <input type="hidden" name="ctitle_'. $aOne['content_id'] .'_'. $aOne['id'] .'_'. $sKind .'" id="ctitle_'. $aOne['content_id'] .'_'. $aOne['id'] .'_'. $sKind .'" value="'. $aOne['context_title'] .'">
    <input type="hidden" name="uid_'. $aOne['content_id'] .'_'. $aOne['id'] .'_'. $sKind .'" id="uid_'. $aOne['content_id'] .'_'. $aOne['id'] .'_'. $sKind .'" value="'. $aOne['user_id'] .'">';
}

/**
 * Парсит контролы удаления записи (собственно теперь более актуально "нижний блок котнтролов")
 * 
 * @param  array $aOne массив с данными о записи
 * @param  string $user_fld поле с UID автора
 * @param  int $status статус: 0 - не проверенно, 1 - утверждено, 2 - удалено
 * @param  string $sKind опционально. тип записи
 * @param  string $sObjParams javascript объект с дополнительными параметрами
 * @param  string $sEditIcon вносим сюда результат _parseEditIcon
 * @return string
 */
function _parseDelIcons( $aOne = array(), $user_fld = 'user_id', $status = 2, $sKind = '0', $sObjParams = '{}', $sEditIcon = '' ) {
    $sWarn   = $status == 2 ? 'Предупредить' : 'Удалить и предупредить';
    $sBan    = $status == 2 ? 'Забанить' : 'Удалить и забанить';
    $sReturn = '<div class="b-fon b-fon_padtop_20">
        <div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_bg_f5" style="height:23px;">';

    if ( $status == 20 ) {
        $sReturn .= '<a  onclick="user_content.unblock(\''. $aOne['content_id'] .'_'. $aOne['id'] .'_'. $sKind .'\', '. $aOne[$user_fld] .')" class="b-button b-button_mini" href="javascript:void(0);"><span class="b-button__icon b-button__icon_ok"></span></a>'
            . $sEditIcon ;
    }
    else {
        $sReturn .= ( $status == 2 ? '' : '<a onclick="parent.banned.delReason(\''. $aOne['content_id'] .'_'. $aOne['id'] .'_'. $sKind .'\', '. $aOne[$user_fld] .', \'stream'. $status .'\', '. $sObjParams .')" href="javascript:void(0);" class="b-button b-button_mini b-button_margleft_10 b-button_float_right" title="Удалить"><span class="b-button__icon b-button__icon_del"></span></a>' . "\n" ) 
            . ( $aOne[$user_fld] != $_SESSION['uid'] && !$aOne['is_banned'] && $aOne['warn'] < 3 ? '<a onclick="'.($aOne['content_id']==22 ? "yaCounter6051055.reachGoal('block_tu');" : "").' user_content.resolveAndBan(\''. $aOne['content_id'] .'_'. $aOne['id'] .'_'. $sKind .'\', 3, '. $aOne[$user_fld] .')" href="javascript:void(0);" class="b-button b-button_mini b-button_margleft_10 b-button_float_right" title="'. $sWarn .'"><span class="b-button__icon b-button__icon_att"></span></a>' . "\n" : '' ) 
            . ( $aOne[$user_fld] == $_SESSION['uid'] || $aOne['is_banned'] || $aOne['ban_where'] ? '' : '<a onclick="'.($aOne['content_id']==22 ? "yaCounter6051055.reachGoal('block_tu');" : "").' user_content.resolveAndBan(\''. $aOne['content_id'] .'_'. $aOne['id'] .'_'. $sKind .'\', 4, '. $aOne[$user_fld] .')" href="javascript:void(0);" class="b-button b-button_mini b-button_margleft_10 b-button_float_right" title="'. $sBan .'"><span class="b-button__icon b-button__icon_krest"></span></a>' . "\n" ) 
            . $sEditIcon 
            . ( $aOne['content_id'] == 1 ? '<a class="b-button b-button_mini b-button_margleft_10 b-button_float_right" href="/siteadmin/user_content/?site=stream&cid=1&sid='. $aOne['stream_id'] .'&mode=letters&fid='. $aOne['from_id'] .'&tid='. $aOne['to_id'] .'&lid='. $aOne['id'] .'" title="Переписка"><span class="b-button__icon b-button__icon_com"></span></a>' : '' ) 
            . ( $status == 1 ? '' : '<a onclick="'.($aOne['content_id']==22 ? "yaCounter6051055.reachGoal('confirm_tu');" : "").' user_content.resolveContent(\''. $aOne['content_id'] .'_'. $aOne['id'] .'_'. $sKind .'\', 1, '. $aOne[$user_fld] .')" class="b-button b-button_mini" href="javascript:void(0);" title="Утвердить"><span class="b-button__icon b-button__icon_ok"></span></a>' );
    }
        
        $sReturn .= '</div>
            </div>';


    return $sReturn;
}

/**
 * Парсит прикрепленный файл (не картинка)
 * 
 * @param  array $aData данные для отображеня приложенного файла @see getAttachDisplayData
 * @return string 
 */
function _parseAttach( $aData = array() ) {
    return '<div class="b-fon__body b-fon__body_pad_5 b-fon__body_bg_ffebbf b-fon__body_margbot_1">
        <table class="b-icon-layout__table b-icon-layout__table_width_full" cellspacing="0" cellpadding="0" border="0">
        <tr class="b-icon-layout__tr">
            <td class="b-icon-layout__icon"><i class="b-icon b-icon_attach_unknown b-icon_attach_'. $aData['file_ext'] .'"></i></td>
            <td class="b-icon-layout__files"><a '. $aData['link'] .' class="b-icon-layout__link" target="_blank">'. $aData['file_name'] .'</a> ('. $aData['file_size_str'] .')</td>
        </tr>
        </table>
    </div>';
}

/**
 * Оборачивает аттачи в контейнер
 * 
 * @param  string $sAttach аттачи
 * @return string HTML
 */
function _wrapAttach( $sAttach = '' ) {
    return '<div class="b-icon-layout b-fon" style="width: 317px;">'. $sAttach .'</div>';
}

/**
 * Захватывает сущности для модерирования
 * 
 * @param  int $content_id идентификатор сущности из admin_contents
 * @param  string $stream_id идентификатор потока
 * @return object xajaxResponse
 */
function chooseContent( $content_id = 0, $stream_id = '' ) {    
    global $user_content;
    
    $response = array();
    
    if ( $user_content->hasContentPermissions($content_id) ) {
        $aStream     = array();
        $checkStream = $user_content->checkStream( $content_id, $stream_id, $_SESSION['uid'], $aStream );
        
        if ( $checkStream ) {
            $nLimit = $content_id == user_content::MODER_MSSAGES ? user_content::MESSAGES_PER_PAGE : user_content::CONTENTS_PER_PAGE;
            $user_content->chooseContent( $content_id, $stream_id, $aStream['stream_num'], $nLimit );
            $response['success'] = true;
        }
        else {
            $response['success'] = false;
            $response['div'] = iconv( 'CP1251', 'UTF-8', _loseStream($objResponse) );
        }
    }
    else {
        $response['success'] = false;
        $response['div'] = iconv( 'CP1251', 'UTF-8', _parsePermissionsDenied($objResponse) );
    }
    
    echo json_encode( $response );
}

/**
 * Возвращает HTML блок на случай потери потока
 * 
 * @param  object $objResponse xajaxResponse
 * @param  string $stream_id идентификатор потока
 * @return string
 */
function _loseStream( &$objResponse = null, $stream_id = '' ) {
    $sHtml = '<div class="b-post b-post_pad_10_15_15"><span style="color: #cc4642; font-weight: bold;">Поток потерян либо перехвачен</span></div>';
    
    if ( $objResponse ) {
        $objResponse->assign( 'my_div_all', 'innerHTML', $sHtml );
    }
    
    return $sHtml;
}

/**
 * Возвращает HTML блок на случай отсутствия прав
 * 
 * @param  object $objResponse xajaxResponse
 * @param  string $stream_id идентификатор потока
 * @return string
 */
function _parsePermissionsDenied( &$objResponse = null, $stream_id = '' ) {
    $sHtml = '<div class="b-post b-post_pad_10_15_15"><span style="color: #cc4642; font-weight: bold;">Не достаточно прав</span></div>';
    
    if ( $objResponse ) {
        $objResponse->assign( 'my_div_all', 'innerHTML', $sHtml );
    }
    
    return $sHtml;
}

/**
 * Освобождение потока пользователем
 * 
 * @param  int $content_id идентификатор сущности из admin_contents
 * @param  string $stream_id идентификатор потока
 * @return object xajaxResponse
 */
function releaseStream( $content_id = 0, $stream_id = '' ) {    
    global $user_content;
    
    $objResponse = new xajaxResponse();
    
    if ( $user_content->hasContentPermissions($content_id) ) {
        $aStream     = array();
        $checkStream = $user_content->checkStream( $content_id, $stream_id, $_SESSION['uid'], $aStream );
        
        if ( $checkStream ) {
            $user_content->releaseStream( $content_id, $stream_id, $_SESSION['uid'] );
        }
        
        if ( $user_content->getStreamsForUser($_SESSION['uid']) ) {
            $objResponse->script('user_content.spinner.hide(true);');
            $objResponse->script( "$('th_{$stream_id}').destroy();" );
            $objResponse->script( "$('td_{$stream_id}').destroy();" );
            $objResponse->script('user_content.spinner.resize();');
        }
        else {
            $objResponse->script('parent.window.close();');
        }
    }
    else {
        $objResponse->script('parent.window.close();');
    }
    
    return $objResponse;
}

/**
 * Захват потока пользователем
 * 
 * @param  int $content_id идентификатор сущности из admin_contents
 * @param  string $stream_id идентификатор потока
 * @param  int $is_first флаг того, что это первый открываемый фрейм
 * @return object xajaxResponse
 */
function chooseStream( $content_id = 0, $stream_id = '', $is_first = 0 ) {    
    global $user_content;
    
    $objResponse = new xajaxResponse();
    
    if ( $user_content->hasContentPermissions($content_id) ) {
        $sStreamId = $user_content->chooseStream( $content_id, $stream_id, $_SESSION['uid'] );
        
        if ( $sStreamId != $stream_id ) {
            if ( empty($sStreamId) ) {
                $objResponse->alert( 'Захват потока не удался.\nПовторите попытку.' );
            }
        }
        else {
            $aStreams     = $user_content->getStreamsForUser( $_SESSION['uid'] );
            $aContentId   = array(); // id сущностей, для которых есть захваченные потоки
            $aCounters    = array(); // данные счетчиков в шапке захватываемого потока
            $sContentName = '';      // название сущности
            
            foreach ( $aStreams as $aOne ) { // находим нужный поток
                if ( $aOne['stream_id'] == $stream_id ) {
                    break; // обрываем обход. то если это первый поток в сущности - $aContentId не будет id
                }
                
                $aContentId[$aOne['content_id']] = 1; // попутно собираем id сущностей
            }
            
            foreach ( $user_content->contents as $aContent ) { // находим название сущности
                if ( $aContent['id'] == $aOne['content_id'] ) {
                    $sContentName = $aContent['name'];
                    break;
                }
            }
            
            if ( $user_content->isStreamCounters( $aOne['content_id'] ) ) {
                // ели нужно добавляем счтчики - заготовка должна быть в каждом пото такой сущности
                $aCounters = $user_content->getStreamCounters( $aOne['content_id'], false, $bShow );
            }
            
            $bFirstIn = !isset($aContentId[$aOne['content_id']]); // это первый поток данной сущности. нужно ли показывать счетчики
            
            ob_start();
            include_once( $_SERVER['DOCUMENT_ROOT'] . '/siteadmin/user_content/frames_header.php' );
            $sOutput = ob_get_contents();
            ob_end_clean();
            
            $sApproved = in_array($content_id, user_content::$aNoApproved) ? '' : 'проверенные';
            $sRejected = in_array($content_id, user_content::$aNoRejected) ? '' : 'заблокированные';
            
            if ( $is_first == 1 ) {
                $objResponse->create( 'frames_body', 'table', 'frames_table' );
                $objResponse->assign( 'frames_table', 'className', 'b-layout__table b-layout__table_height_99ps b-layout__table_width_full' );
                $objResponse->assign( 'frames_table', 'border', '0' );
                $objResponse->assign( 'frames_table', 'cellpadding', '0' );
                $objResponse->assign( 'frames_table', 'cellspacing', '0' );
                $objResponse->create( 'frames_table', 'tr', 'tr_header' );
                $objResponse->assign( 'tr_header', 'className', 'b-layout__tr' );
                $objResponse->create( 'frames_table', 'tr', 'tr_frames' );
                $objResponse->assign( 'tr_frames', 'className', 'b-layout__tr' );
            }
            
            $objResponse->script( 
                "if ($('$stream_id')) { $('$stream_id').contentWindow.location.reload(true); } 
                else {
                    td1 = new Element('td', {'id': 'th_$stream_id','class': 'b-layout__one b-layout__one_bg_f7 b-layout__one_pad_10 b-layout__one_width_330 b-layout__one_bordright_ccc b-layout__one_bordbot_ccc b-layout__one_height_100',html: '' });
                    td2 = new Element('td', {'id': 'td_$stream_id','class': 'b-layout__one b-layout__one_height_100ps b-layout__one_width_350  b-layout__one_bordright_ccc',html: '<div class=\"box-frame\"><iframe id=\"$stream_id\" src=\"/siteadmin/user_content/?site=stream&cid=$content_id&sid=$stream_id\" frameborder=\"0\" width=\"100%\" height=\"100%\"></iframe></div>' });
                    $('tr_header').adopt(td1);$('tr_frames').adopt(td2);
                };" 
            );
            
            if ( $bFirstIn ) {
                $objResponse->script( "$$('div[id^=\"counters_{$aOne['content_id']}_\"]').addClass('b-shadow_hide');$$('div[id^=\"counters_{$aOne['content_id']}_\"]').setStyle('display', 'none');" );
            }
            
            $objResponse->assign( 'th_'.$stream_id, 'innerHTML', $sOutput );
            $objResponse->script( "user_content.addSoundControl('$stream_id');" );
            $objResponse->script( "user_content.tabMenuItems['$stream_id'] = ['непроверенные', '$sApproved', '$sRejected'];" );
        }
    }
    
    return $objResponse;
}

/**
 * Обновляет счетчики в шапках потоков
 * 
 * @global object $user_content
 * @return xajaxResponse 
 */
function otherCounters() {
    global $user_content;
    
    $objResponse = new xajaxResponse();
    
    if ( $user_content->hasPermissions('choose') ) {
        $nContent = -1;
        $aStreams = $user_content->getStreamsForUser( $_SESSION['uid'] );
        
        foreach ( $aStreams as $aOne ) {
            if ( $nContent != $aOne['content_id'] ) {
                $nContent = $aOne['content_id'];
                
                if ( $user_content->isStreamCounters( $nContent ) ) {
                    $aCounters = $user_content->getStreamCounters( $nContent, true, $bShow );
                    
                    foreach ( $aCounters as $nKey => $nValue ) {
                        $objResponse->assign( $aOne['stream_id'] . '_counters' . $nKey, 'innerHTML', '(' . $nValue . ')' );
                    }
                    
                    if ( $bShow ) {
                        $objResponse->script( "$('counters_{$aOne['content_id']}_{$aOne['stream_id']}').setStyle('display', '');$('counters_{$aOne['content_id']}_{$aOne['stream_id']}').removeClass('b-shadow_hide');" );
                    }
                    else {
                        $objResponse->script( "$('counters_{$aOne['content_id']}_{$aOne['stream_id']}').addClass('b-shadow_hide');$('counters_{$aOne['content_id']}_{$aOne['stream_id']}').setStyle('display', 'none');" );
                    }
                }
            }
        }
        
        $objResponse->script( "setTimeout('xajax_otherCounters();', ". (user_content::MODER_OTHER_CNT_REFRESH * 1000) .")" );
    }
    
    return $objResponse;
}

/**
 * Возвращает обновления для страницы выбора потоков
 */
function updateStreamsForUser() {
    global $user_content;
    
    $response = array();
    
    if ( $user_content->hasPermissions('choose') ) {
        $response['success'] = true;
        $response['update']  = $user_content->updateStreamsForUser();
        $response['queue']   = $user_content->getQueueCounts();
        $response['streams'] = $user_content->getStreamsQueueCounts();
    }
    else {
        $response['success'] = false;
    }
    
    echo json_encode( $response );
}

$xajax->processRequest();
