<?
$rpath = "../";
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/commune.common.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/commune.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
require_once $_SERVER["DOCUMENT_ROOT"]."/classes/memBuff2.php";

/**
* Предложение на создание нового промо сообщества
*
* @param    array   $frm    Данные введенные пользователем
* @return   object          xajaxResponse
*/
function NewPromoCommune($frm) {
    global $session;
    session_start();
    $objResponse = new xajaxResponse();

    $errors = array();

    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/captcha.php");
    $captcha = new captcha();
        $rnd = $frm['rndnum'];
    if(!$captcha->checkNumber(trim($rnd))) {
        $errors[] = 'captcha';
    }
    if(trim($frm['name'])=='') {
        $errors[] = 'name';
    }
    if(trim($frm['msg'])=='') {
        $errors[] = 'msg';
    }
    $objResponse->script('$("popup_promo_commune").getElements("div[id^=popup_promo_commune_err]").setStyle("display", "none");');
    if(count($errors)) {
        foreach($errors as $error) {
            $objResponse->script('$("popup_promo_commune_err_'.$error.'").setStyle("display", "block");');
        }
    } else {
        $objResponse->script('$("btn_promo_new").setStyle("display", "none");');
        $objResponse->script('$("btn_promo_ok").setStyle("display", "block");');
        $objResponse->script('$("popup_promo_commune").setStyle("display", "none");');
        $objResponse->script('$("claim-name").set("value","");');
        $objResponse->script('$("claim-idea").set("value","");');
        $objResponse->script('$("claim-cap").set("value","");');
        $sm = new smail();
        $sm->NewPromoCommune($frm['name'], $frm['msg']);
    }
    $objResponse->script('$("captcha").set("src","/image.php?r="+Math.random());');
    $objResponse->assign('claim-cap', 'value', '');
    return $objResponse;
}

/**
 * Перемещение сообщества вверх/вниз при своей сортировке в звкладке "Я вступил"
 *
 * @param  int $sCommId сообщество котоое перемещаем
 * @param  string $sSign в какую сторону перемещаем: > - вверх, < - вниз
 * @param  string $group_id код группы для перерисовки списка в случае успеха
 * @param  string $sub_om код дополнительного условия сортировки или вкладки для перерисовки списка в случае успеха
 * @param  int $page номер страницы для перерисовки списка в случае успеха
 * @return object xajaxResponse
 */
function CommuneMove( $sCommId = '', $sSign = '>', $group_id = 0, $sub_om = '', $page = 1 ) {
    global $session;
    session_start();
    $objResponse = new xajaxResponse();
    $sUserId     = get_uid(false);
    
    if ( !$sCommId || !$sUserId ) {
    	return $objResponse;
    }
    
    if ( commune::communeMove($sCommId, $sUserId, $sSign) ) {
        $group_id = ( $group_id ) ? $group_id : null;
        return CommuneGetList($group_id, $sub_om, $page);
    }
    
    return $objResponse;
}

/**
 * Напрямую установить номер сообщества при своей сортировке в звкладке "Я вступил"
 * 
 * @param  int $sCommId сообщество котоое перемещаем
 * @param  int $nOldNum текущий номер сообщества
 * @param  int $nNum номер который желаем присвоить
 * @param  int $total общее количество сообществ
 * @param  string $group_id код группы для перерисовки списка в случае успеха
 * @param  string $sub_om код дополнительного условия сортировки или вкладки для перерисовки списка в случае успеха
 * @param  int $page номер страницы для перерисовки списка в случае успеха
 * @return object xajaxResponse
 */
function CommuneSetPosition( $sCommId = '', $nOldNum = 0, $nNum = 0, $total = 0, $group_id = 0, $sub_om = '', $page = 1 ) {
    global $session;
    session_start();
    
    $objResponse = new xajaxResponse();
    $sUserId     = get_uid(false);
    
    if ( $sCommId && $sUserId && !intval($nNum) ) {
        $objResponse->script("$('commune_set_order_{$sCommId}').setStyle('display', 'none');");
        $objResponse->assign( 'position_time_'.$sCommId, 'value', '' );
    	return $objResponse;
    }
    
    if ( commune::CommuneSetPosition($sCommId, $sUserId, $nOldNum, $nNum, $total) ) {
        // была перенумерация: обновляем список
        $group_id = ( $group_id ) ? $group_id : null;
        return CommuneGetList($group_id, $sub_om, $page);
    }
    
    // перенумерации не было: просто закрываем попап
    $objResponse->script("$('commune_set_order_{$sCommId}').setStyle('display', 'none');");
    $objResponse->assign( 'position_time_'.$sCommId, 'value', '' );
    return $objResponse;
}

/**
 * Перерисовывает список сообществ.
 * 
 * @param  string $group_id код группы для перерисовки списка в случае успеха
 * @param  string $om код дополнительного условия сортировки или вкладки
 * @param  int $page номер страницы
 * @param  string $search если есть поисковоая строка для подсвечивания найденого в названияих и описаниях
 * @return object xajaxResponse
 */
function CommuneGetList( $group_id = null, $om = '', $page = 1, $search = null ) {
    $aNeedUser   = array( commune::OM_CM_JOINED, commune::OM_CM_JOINED_ACCEPTED, commune::OM_CM_JOINED_BEST, commune::OM_CM_JOINED_CREATED, commune::OM_CM_JOINED_LAST, commune::OM_CM_JOINED_MY  );
    $uid         = get_uid(false);
    $user_id     = ( in_array($om, $aNeedUser) ? $uid : NULL);
    $author_id   = ($om == commune::OM_CM_MY ? $uid : NULL);
    $offset      = ($page - 1) * commune::MAX_ON_PAGE;
    $limit       = commune::MAX_ON_PAGE;
    $objResponse = new xajaxResponse();
    
    // начало нумерации сообществ для своей сортировки
    $start_position = ($page - 1) * $limit;
    $groupCommCnt   = 0;
    
    if (($om == commune::OM_CM_MY || $om == commune::OM_CM_JOINED) && !$uid)  {
        // Неавторизовался и зашел в "свои" закладки.
        return $objResponse;
    }
    else {
        if ( in_array($om, $aNeedUser) || $om == commune::OM_CM_MY || $search !== NULL) {
            $communes = commune::GetCommunes($group_id, $author_id, $user_id, $om, (!$uid ? NULL : $uid), $offset, $limit, $search, $groupCommCnt, $user_mod, $rating);
        }
        else {
            $communes = commune::GetCommunes($group_id, NULL, NULL, $om, (!$uid ? NULL : $uid), $offset, $limit, $search, $groupCommCnt, $user_mod, $rating);
        }
        
        $objResponse->assign( 'commune_list', 'innerHTML', __commPrintPage( $page, $communes, $groupCommCnt, $om, $search , true) );
        
        return $objResponse;
    }
}

/**
* Создает новую категорию
*
* @param    array   $frm    Данные для создания категории
*/
function AddCategory($frm) {
    global $session;
    session_start();
    $objResponse = new xajaxResponse();
    $name = $frm['commune_fld_add_category_name'];
    $is_only_for_admin = $frm['commune_fld_add_category_only_for_admin'];
    $commune_id = $frm['commune_id'];
    $om = $frm['om'];
    $uid = get_uid(false);
    if($uid) {
        $status = commune::GetUserCommuneRel($commune_id, $uid);
    }
    if($status['is_moderator']==1 || $status['is_admin']==1 || $status['is_author']==1 || hasPermissions('communes')) {
        $error = 0;
        $name = trim(strip_tags(stripslashes($name)));
        if($name=='' || strlen($name)>commune::MAX_CATEGORY_NAME_SIZE) {
            $error = 1;
            $objResponse->script("alert('Название раздела не может быть пустым и должно содержать не более ".commune::MAX_CATEGORY_NAME_SIZE.' '.  ending(commune::MAX_CATEGORY_NAME_SIZE, ' символ', ' символа', ' символов')."');");
            $objResponse->script("communeObj.initCategories();");
        }
        if(commune::issetCategory($name, $commune_id)) {
            $error = 1;
            $objResponse->script("alert('Раздел с таким названием уже существует.');");
            $objResponse->script("communeObj.initCategories();");
        } 
        if(!$error) {
            commune::addCategory($name, $is_only_for_admin, $commune_id);
            $objResponse->script("xajax_ShowCategoriesList({$commune_id},1,{$om});");
            //$objResponse->script("CommuneCancelAddCategory();");
            $objResponse->script("communeObj.initCategories();");
            $objResponse->assign('editmsg', 'innerHTML', __commPrntCommentForm($commune_id, $om));
            $objResponse->script("xajax_CheckDraftsCommune(); initWysiwyg();");
        }
    }
    return $objResponse;
}

/**
* Обновление категории
*
* @param    array   $frm    Данные категории
*/
function UpdateCategory($frm) {
    global $session;
    session_start();
    $objResponse = new xajaxResponse();
    $id = $frm['category_id'];
    $name = $frm['commune_fld_edit_category_name'];
    $is_only_for_admin = $frm['commune_fld_edit_category_only_for_admin'];
    $commune_id = $frm['commune_id'];
    $om = $frm['om'];
    $uid = get_uid(false);
    if($uid) {
        $status = commune::GetUserCommuneRel($commune_id, $uid);
    }else{
        return $objResponse;
    }
    if($status['is_moderator']==1 || $status['is_admin']==1 || $status['is_author']==1 || hasPermissions('communes')) {
        $error = 0;
        $name = trim(strip_tags(stripslashes($name)));
        
        $allow = "1234567890\t\n !.\"',.!@%^#$;:-<>";
        $str   = "abcdefghijklmnopqrstuvwxyz";
        $cir   = "абвгдеёжзийклмнопрстуфхцчшщъыьэюяАБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ";
        $allow .= $str . strtoupper($str). $cir;
        $tS = "";
        for ($i = 0; $i < strlen($name); $i++) {
            $ch = $name[$i];
            if (strpos($allow, $ch) !== FALSE) $tS .= $ch;
        }
        $name = $tS;
        if($name == '' || strlen($name) > 30) {
            $error = 1;
            $objResponse->script("alert('Название раздела не может быть пустым и должно содержать не более 30 символов ".strlen($name)."');");
        }
        
        $edit_id = commune::issetCategory($name, $commune_id);
        if($edit_id && (int)$edit_id !== (int)$id) {
            $error = 1;
            $objResponse->script("alert('Раздел с таким названием уже существует.');");
        }
        if(!$error) {            
            commune::updateCategory($id, $name, $is_only_for_admin, $commune_id);
            $objResponse->script("xajax_ShowCategoriesList({$commune_id},1,{$om});");
            $objResponse->assign('editmsg', 'innerHTML', __commPrntCommentForm($commune_id, $om));
        }
        $objResponse->script("communeObj.initCategories");
    }
    return $objResponse;
}

/**
* Удаление категории
*
* @param    integer     $category_id         Идентификатор категории
* @param    integer     $commune_id          Идентификатор сообщества
* @param    integer     $om                  ID вкладки
*
*/
function DeleteCategory($category_id, $commune_id, $om) {
    global $session;
    session_start();
    $objResponse = new xajaxResponse();
    $uid = get_uid(false);
    if($uid) {
        $status = commune::GetUserCommuneRel($commune_id, $uid);
    }
    if($status['is_moderator']==1 || $status['is_admin']==1 || $status['is_author']==1 || hasPermissions('communes')) {
        commune::deleteCategory($category_id, $commune_id);
        $objResponse->script("window.location = '/commune/?id={$commune_id}&om={$om}';");
    }
    return $objResponse;
}

/**
* Редактирование категории
*
* @param    integer     $category_id         Идентификатор категории
* @param    integer     $commune_id         Идентификатор сообщества
*
*/
function EditCategory($category_id, $commune_id) {
    global $session;
    session_start();
    $objResponse = new xajaxResponse();
    $uid = get_uid(false);
    if($uid) {
        $status = commune::GetUserCommuneRel($commune_id, $uid);
    }
    if($status['is_moderator']==1 || $status['is_admin']==1 || $status['is_author']==1 || hasPermissions('communes')) {
        $category = commune::getCategory($category_id);
        if($category['is_only_for_admin']=='t') {
            $objResponse->assign('commune_fld_edit_category_only_for_admin_'.$category['id'],'checked', true);
        }
        $objResponse->assign('commune_fld_edit_category_name_'.$category_id,'innerHTML', $category['name']);
        //$objResponse->call('tawlTextareaInit');
    }
    return $objResponse;
}

/**
* Выводит список категорий
*
* @param    integer     $commune_id         Идентификатор сообщества
* @param    boolean     $is_for_admin       Выводить категории для админа сообщества или посетителя
* @param    integer     $om                 ID вкладки
* @param    string      $curr_cat           опционально ID текущей категории для SEO
* @param    integer     $page               опционально номер страницы для SEO
*/
function ShowCategoriesList( $commune_id, $is_for_admin, $om, $curr_cat = '', $page = 0 ) {
    global $session;
    session_start();
    $objResponse = new xajaxResponse();

    $html = __commPrintCategoriesList($commune_id, $om, $curr_cat, $page);
        
    if ($html) {
        $objResponse->assign('commune_categories_list', 'innerHTML', $html);
        $objResponse->script("communeObj.initCategories();");
    }
    return $objResponse;
}

/**
 * Блокировка/разблокировка топика администратором сообщества
 * 
 * @param  int $commune_id ID сообщества
 * @param  int $topic_id ID топика
 * @param  int $topic_id ID сообщения
 * @param  string $action действие ('block' или 'unblock')
 * @return bool true - успех, false - провал
 */
function BlockedTopic( $commune_id = 0, $topic_id = 0, $msg_id = 0, $action = 'unblock' ) {
    global $session;
    session_start();
    $objResponse = new xajaxResponse();
    $commune_id  = intval( $commune_id );
    $topic_id    = intval( $topic_id );
    $msg_id      = intval( $msg_id );
    
    $uid = get_uid( false );
    
    if ( $uid ) {
        $status = commune::GetUserCommuneRel( $commune_id, $uid );
    }
    
    if ( $status['is_moderator'] == 1 || $status['is_admin'] == 1 || $status['is_author'] == 1 || hasPermissions('communes') ) {
        commune::BlockedTopic( $topic_id, $msg_id, $action );
        
        if ( $action == 'block' ) {
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
            
            $user = new users();
            $user->GetUserByUID( $uid );
            
            $html = "<div class='br-moderation-options'>
                <a href='/about/feedback/' class='lnk-feedback' style='color: #fff;'>Служба поддержки</a>
                <div class='br-mo-status'><strong>Сообщение заблокировано!</strong></div>
                <p class='br-mo-info'>"
                . ($user->login ? "Заблокировал: <a href='/users/{$user->login}' style='color: #FF6B3D'>{$user->uname} {$user->usurname} [{$user->login}]</a><br />" : '')
                . "Дата блокировки: ".dateFormat('d.m.Y', $blocked_time).".</p>
            </div>";
            
        	$objResponse->assign( "theme-reason-$topic_id", 'innerHTML', $html );
        	$objResponse->assign( "theme-button-$topic_id", 'innerHTML', '<a href="javascript:void(0)" onclick="if(warning()) xajax_BlockedTopic('.$commune_id.','.$topic_id.','.$msg_id.',\'unblock\')" class="lnk-red">Разблокировать</a>' );
        }
        else {
            $objResponse->assign( "theme-reason-$topic_id", 'innerHTML', '&nbsp;' );
            $objResponse->assign( "theme-button-$topic_id", 'innerHTML', '<a href="javascript:void(0)" onclick="if(warning()) xajax_BlockedTopic('.$commune_id.','.$topic_id.','.$msg_id.',\'block\')" class="lnk-red">Заблокировать</a>' );
        }
    }
    
    return $objResponse;
}

function DeleteTopic($backto, $message_id, $user_id, $mod, $page, $om, $site, $is_fav, $themesCount,$comm_id=false)
{
  global $session;
  session_start();
  $objResponse = new xajaxResponse();

  if(get_uid(false)!=$user_id) { return $objResponse; }
  if(get_uid(false)) {
    $comm = commune::getCommuneIDByMessageID($message_id);
    $status = commune::GetUserCommuneRel($comm, get_uid(false));
  }
    if ( $status["is_author"] != 1 ) { //#0024905
        $msg = commune::GetMessage($message_id);
        if ($msg["user_id"] == get_uid(false)) {
            $status["is_author"] = 1;
        }
    }
  if($status['is_moderator']==1 || $status['is_admin']==1 || $status['is_author']==1 || hasPermissions('communes')) {

  // !!! Нужно очищать форму редактирования, если в ней в данный момент редактируется удаляемое сообщение. А то некрасиво.
  // !!! Можно будет еще добавлять сообщение вниз, то есть брать
  // верхнее сообщение из следующей страницы и добавлять вниз текущей страницы.
  $message_id = __paramValue('int', $message_id);
  
  if($r = commune::DeleteMessage($message_id, intval($user_id), $mod, $deleted_time)) {
    if($site == 'Topic') {
          $objResponse->script('document.location.href = "/commune/?id='.$comm_id.'"');
    }elseif($r<0) { // Больше не удаляется.
			$top = commune::GetTopMessageByAnyOther($message_id, $user_id, $mod, TRUE);
      $objResponse->assign($backto, 'innerHTML',  __commPrntTopic($top, $user_id, $mod, $om, $page, $site, $is_fav));
    }
    else {
      if (!hasPermissions("adm")) {
          $objResponse->remove($backto);
      } else {
      	  $moderator = new users();
      	  $moderator->GetUserByUID( get_uid(false) );
      	  $deleted_time = explode(' ', $deleted_time);
      	  $date = $deleted_time[0];
      	  $time = $deleted_time[1];
      	  $time = explode(":", $time);
      	  $time = $time[0].":".$time[1];
      	  $moderatorInfo = "<span class=\"b-post__moderator_info_red\" Удалнено модератором [{$moderator->login}] {$moderator->uname} {$moderator->usurname}</span> <span class=\"b-post__moderator_info_gray\">[{$date} | {$time}]</span>";
          $objResponse->script("commune_markPostAsDeleted('{$backto}', '{$moderator->login}', '{$moderator->uname}', '{$moderator->usurname}', '{$date}', '{$time}' )");
      }
      if($themesCount) {
        if($themesCount - 1) {
          $objResponse->assign('idThCnt', 'innerHTML',  $themesCount - 1);
          $objResponse->assign('idThCntS', 'innerHTML',  getSymbolicName($themesCount - 1, 'messages'));
        }
        else
          $objResponse->assign('idThBlck', 'innerHTML', '');
      }

      }
  }
  }
  return $objResponse;
}
                                          
function DeleteComment($backto, $message_id, $user_id, $mod, $om, $level, $is_last)
{
  global $session;
  session_start();
  $objResponse = new xajaxResponse();

  if($r = commune::DeleteMessage($message_id, intval($user_id), $mod, $deleted_time)) {
    if($r<0) {
			$top = commune::GetTopMessageByAnyOther($message_id, get_uid(false), $mod, FALSE);
			$top['is_viewed'] = 1;
      $comment = commune::GetAsThread(NULL, $message_id);
      $objResponse->assign($backto, 'innerHTML', __commPrntComment($top, $comment, $user_id, $mod, $om, NULL, $level, $is_last));
    }
    else {
      $objResponse->remove($backto);

      // Удаляем заголовок "Комментарии", если на странице не осталось ни одного комментария.
      $objResponse->script("
        if(window.__commCCnt && !--__commCCnt) {
          var ___ch = document.getElementById('idCommentsHeader');
          if(___ch)
            ___ch.parentNode.removeChild(___ch);
        }
      ");
    }
  }
  $objResponse->remove('idEditCommentForm_'.$message_id);




  return $objResponse;
}

function BanMemberNewComment($member_id, $uid, $commune_id) {
    $objResponse = new xajaxResponse();
    $commune_id = intval($commune_id);
    $member_id  = intval($member_id);
    $uid        = intval($uid);
    require_once $_SERVER['DOCUMENT_ROOT']."/classes/commune.php";
    
    $admin = commune::GetUserCommuneRel($commune_id, get_uid());
    
    if($admin['is_moderator'] || $admin['is_author']) {
        $warncount = 0;
        $r = commune::BanMemberForComment($member_id, $commune_id, $warncount);
        if ( $warncount < 3 ) {
            $objResponse->script( "$$('.warncount-$uid').set('html','$warncount');" );
        } else {
            require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
            $u = new users();
            $u->GetUserByUID($uid);
            $objResponse->script( "$$('.warnlink-$uid').set('html','<a class=\"id-ban-member{$member_id}\" style=\"color:red\" href=\"javascript:void(0)\" onclick=\"xajax_BanNewMember({$member_id})\">Забанить!</a>');" );
        }
        $sm = new smail();
        $comm = commune::GetCommuneByMember($member_id);
        $sm->CommuneMemberAction($comm['member_user_id'], 'WarnMember', $comm);
    }
    return $objResponse;  
}

function BanMemberForComment($pfx, $message_id, $member_id, $user_id, $mod, $om, $level, $is_last)
{
  $objResponse = new xajaxResponse();
  if(get_uid(false)!=$user_id) { return $objResponse; }

  if(get_uid(false)) {
    $comm = commune::getCommuneIDByMessageID($message_id);
    $status = commune::GetUserCommuneRel($comm, get_uid(false));
  }
  if($status['is_moderator']==1 || $status['is_admin']==1 || $status['is_author']==1 || hasPermissions('communes')) {

      if($r=commune::BanMemberForComment($member_id)) {
        
        $top = commune::GetTopMessageByAnyOther($message_id, NULL, $mod, FALSE);
    		$top['is_viewed'] = 1;
        $comment = commune::GetAsThread(NULL, $message_id);
        $objResponse->assign($pfx.$message_id,"innerHTML", __commPrntComment($top, $comment, $user_id, $mod, $om, NULL, $level, $is_last));
        $sm = new smail();
        if($r=='t') {
          $comm = commune::GetCommuneByMember($member_id);
          $sm->CommuneMemberAction($comm['member_user_id'], 'BanMember', $comm);
        }
    //    else if($r=='f')
    //      $sm->CommuneTopicAction($comment, 'WarnMemberForComment');

      }
  }
  return $objResponse;
}


function BanMemberForTopic($backto, $message_id, $member_id, $user_id, $mod, $page, $om, $site, $is_fav)
{
  $objResponse = new xajaxResponse();
  if(get_uid(false)!=$user_id) { return $objResponse; }
  if(get_uid(false)) {
    $comm = commune::getCommuneIDByMessageID($message_id);
    $status = commune::GetUserCommuneRel($comm, get_uid(false));
  }
  if($status['is_moderator']==1 || $status['is_admin']==1 || $status['is_author']==1 || hasPermissions('communes')) {

      if(($r = commune::BanMemberForComment($member_id)) || true) {
    		if($site=='Topic') {
          $top = commune::GetTopMessageByAnyOther($message_id, NULL, $mod, TRUE);
    		  $top['is_viewed'] = 1;
    		}
    		else {
                $top = commune::GetTopMessageByAnyOther($message_id, $user_id, $mod, TRUE);
                $site = 'xajaxCommune';
            }
        //print(__commPrntTopic($top, $user_id, $mod, $om, $page, $site, $is_fav));
        $objResponse->assign($backto, 'innerHTML', __commPrntTopic($top, $user_id, $mod, $om, $page, $site, $is_fav, null, true));
        $comm = commune::GetCommuneByMember($member_id);
        $sm = new smail();
        
        
        
        if($r) {
            $comm = commune::GetCommuneByMember($member_id);
            $sm->CommuneMemberAction($comm['member_user_id'], 'BanMember', $comm);
        } else {
            $comm = commune::GetCommuneByMember($member_id);
            $sm->CommuneMemberAction($comm['member_user_id'], 'WarnMember', $comm);
        }
      }
  }
  return $objResponse;
}


function BanNewMember($member_id)
{
  $objResponse = new xajaxResponse();
  if(get_uid(false)!=$user_id) { return $objResponse; }
  if(get_uid(false)) {
    $comm = commune::GetCommuneByMember($member_id);
    $status = commune::GetUserCommuneRel($comm['commune_id'], get_uid(false));
  }
  if($status['is_moderator']==1 || $status['is_admin']==1 || $status['is_author']==1 || hasPermissions('communes')) {

      if($r=commune::BanMember($member_id)) {
        $comm = commune::GetCommuneByMember($member_id);
        if($r<0) {
            $objResponse->script("window.location = '';");
            $objResponse->script("$$('.id-ban-member{$member_id}').setStyle('display', 'none');");
            $objResponse->script("$$('.b-warncount-{$member_id}').setStyle('display', '');");
            $objResponse->script("$$('.warncount-{$comm['member_user_id']}').set('html', '0');");
        } else {
            $objResponse->script("window.location = '';");
            $objResponse->script("$$('.id-ban-member{$member_id}').set('html', '".($r<0 ? 'Забанить' : 'Разбанить')."')");
        }
        
        $sm = new smail();
        if($r<0)
          $sm->CommuneMemberAction($comm['member_user_id'], 'UnBanMember', $comm);
    //    else
    //      $sm->CommuneMemberAction($comm['member_user_id'], 'BanMember', $comm);
      }
  }

  return $objResponse;
}

function BanMember($backto, $member_id)
{
  $objResponse = new xajaxResponse();
  if(get_uid(false)) {
    $comm = commune::GetCommuneByMember($member_id);
    $status = commune::GetUserCommuneRel( $comm['id'], get_uid(false));
  }
  if($status['is_moderator']==1 || $status['is_admin']==1 || $status['is_author']==1 || hasPermissions('communes')) {
      if($r=commune::BanMember($member_id)) {
        $objResponse->assign($backto, "innerHTML", ($r<0 ? 'Забанить' : 'Разбанить'));
        $objResponse->script("document.getElementById('".$backto."').className = '".($r<0 ? 'lnk-dot-red' : 'lnk-dot-green')."'");
        $objResponse->script("$('user_row_$member_id')".($r<0 ? '.removeClass(\'cau-banned\')' : '.addClass(\'cau-banned\')'));
        $comm = commune::GetCommuneByMember($member_id);
        $sm = new smail();
        if($r<0)
        $sm->CommuneMemberAction($comm['member_user_id'], 'UnBanMember', $comm);
    //    else
    //      $sm->CommuneMemberAction($comm['member_user_id'], 'BanMember', $comm);
    }
  }

  return $objResponse;
}

function AddFav($backto, $backto2, $message_id, $user_id, $om, $undo, $priority=0)
{
  $objResponse = new xajaxResponse();
  if(get_uid(false)!=$user_id) return $objResponse;
  $sort = $_COOKIE['commune_fav_order']!=""?$_COOKIE['commune_fav_order']:"date"; 
  
  if(commune::AddFav($message_id, $user_id, $undo, $priority)) {
    if(!$undo) {
      $objResponse->assign($backto, "src", commune::getStarByPR($priority));
      //$objResponse->assign($backto, "onclick", 'return true;');
      $msg = commune::GetMessage($message_id);
      $favs = commune::GetFavorites($user_id, NULL, $sort, $msg["commune_id"]);
      $objResponse->assign('favBlock', 'innerHTML', __commPrntFavs($favs, $user_id, $om));
      $objResponse->script("{$backto}.setAttribute('on',1);");
    }
    else {
      $objResponse->assign($backto, "src", '/images/bookmarks/bsw.png');
      //$objResponse->assign($backto, "onclick", "ShowFavFloat({$msg_id}, {$user_id}, {$om})");
      $objResponse->remove($backto2);
      $objResponse->script(
      "
        {$backto}.setAttribute('on',0);
        if(favBlock.innerHTML.match(/<LI[^>]*>/i)==null)
          favBlock.innerHTML = 'Нет закладок';
      "
      );

    }
  }
  $objResponse->script("communeObj.initFavs();");
  return $objResponse;
}

/**
 * Сортируем закладки
 *
 */
function sortFav($sort="date", $om, $commune_id = NULL) {
    global $session;
    
    session_start();
    setcookie("commune_fav_order", $sort, time()+60*60*24*365, "/");
    $user_id = get_uid(false);  
    $objResponse = new xajaxResponse();
    if($favs = commune::GetFavorites($user_id, NULL, $sort, $commune_id)) {
        $objResponse->assign('favBlock', 'innerHTML', __commPrntFavs($favs, $user_id, $om));
        $objResponse->script("hlSort('$sort')");
    }
    $objResponse->script("communeObj.initFavs()");
    
    return $objResponse;
}

function EditFav($msg_id, $priority = 0, $title = "", $action = "edit") {
	global $session;
	session_start();
	$user_id = $_SESSION['uid'];
	$objResponse = new xajaxResponse();

	$msg_id = intval($msg_id);
	$GLOBALS['xajax']->setCharEncoding("windows-1251");
    $sort = $_COOKIE['commune_fav_order']!=""?$_COOKIE['commune_fav_order']:"date"; 
	$action = trim($action);
    $title  = trim($title);
	switch ($action) {
		case "update":
	  		$title = substr($title, 0, 128);
  			$updatefav = commune::AddFav($msg_id, $user_id, 0, $priority, $title);
            $editfav = current(commune::GetFavorites($user_id, $msg_id, $sort));
		    $key     = $msg_id;
		    $om      = '';
		    $outHTML = __commPrntFavContent($editfav, $key, $user_id, $om);
		    
		    $objResponse->assign("fav".$msg_id, "innerHTML", $outHTML);
		break;

		case "edit":
			$editfav = current(commune::GetFavorites($user_id, $msg_id, $sort));
			//$editfav = current($editfav);
			$editfav['title'] = preg_replace("/<br.*?>/mix", "\r\n", stripslashes(reformat2($editfav['title'], 20, 0, 1)));
			$outHTML = "<table border=\"0\" cellpadding=\"1\" cellspacing=\"0\"><tbody><tr valign=\"top\"><td style=\"padding-left: 3px;\">";
			$outHTML .= "<ul class=\"post-f-fav-sel\">";
			$outHTML .= "<li><IMG alt=\"\" border=\"0\" id='favpic".$msg_id."-0' width=\"15\" height=\"15\" src=\"".($editfav['priority'] == 0 ? commune::getStarByPR(0) : commune::getEmptyStarByPR(0))."\" hspace=\"1\" vspace=\"1\" onclick=\"FavPriority($msg_id, 0)\" style=\"cursor:pointer;\"></li>";
			$outHTML .= "<li><IMG alt=\"\" border=\"0\" id='favpic".$msg_id."-1' width=\"15\" height=\"15\" src=\"".($editfav['priority'] == 1 ? commune::getStarByPR(1) : commune::getEmptyStarByPR(1))."\" hspace=\"1\" vspace=\"1\" onclick=\"FavPriority($msg_id, 1)\" style=\"cursor:pointer;\"></li>";
			$outHTML .= "<li><IMG alt=\"\" border=\"0\" id='favpic".$msg_id."-2' width=\"15\" height=\"15\" src=\"".($editfav['priority'] == 2 ? commune::getStarByPR(2) : commune::getEmptyStarByPR(2))."\" hspace=\"1\" vspace=\"1\" onclick=\"FavPriority($msg_id, 2)\" style=\"cursor:pointer;\"></li>";
			$outHTML .= "<li><IMG alt=\"\" border=\"0\" id='favpic".$msg_id."-3' width=\"15\" height=\"15\" src=\"".($editfav['priority'] == 3 ? commune::getStarByPR(3) : commune::getEmptyStarByPR(3))."\" hspace=\"1\" vspace=\"1\" onclick=\"FavPriority($msg_id, 3)\" style=\"cursor:pointer;\"></li>";
			$outHTML .= "</ul></td><td>";
			$outHTML .= "<div class=\"fav-one-edit-txt\">";
			$outHTML .= "<INPUT id='favpriority".$msg_id."' type='hidden' value='".$editfav['priority']."'>";
			$outHTML .= "<INPUT id='currtitle' type='hidden' value='".$editfav['title']."'>";
			$outHTML .= "<textarea rows=\"3\" cols=\"7\" id='favtext".$msg_id."'>{$editfav['title']}</textarea>";
			$outHTML .= "<div class=\"fav-one-edit-btns\">";									
			$outHTML .= "<INPUT type='button' value='Сохранить' onClick='if(document.getElementById(\"favtext".$msg_id."\").value.length>128){alert(\"Слишком длинное название закладки!\");return false;}else{xajax_EditFav(".$msg_id.", document.getElementById(\"favpriority".$msg_id."\").value, document.getElementById(\"favtext".$msg_id."\").value, \"update\");}'>";
			$outHTML .= "<INPUT type='button' value='Отмена' onClick='xajax_EditFav(".$msg_id.", ".$editfav['priority'].", document.getElementById(\"currtitle\").value, \"default\");'>";									
			$outHTML .= "</div></td></tr></tbody></table>";											
										

			$objResponse->assign("fav".$msg_id, "innerHTML", $outHTML);
		break;
		default:
		    $editfav = current(commune::GetFavorites($user_id, $msg_id, $sort));
		    $key     = $msg_id;
		    $om      = '';
		    $outHTML = __commPrntFavContent($editfav, $key, $user_id, $om);
		    $objResponse->assign("fav".$msg_id, "innerHTML", $outHTML);
		    break;
	}
    
    $objResponse->script("communeObj.initFavs();");
	return $objResponse;
}

function UpdateNote($user_id, $commune_id, $note) {
    $objResponse = new xajaxResponse();
  
    if(get_uid(false)!=$user_id) return $objResponse;

    $note = change_q_x(stripcslashes($note), FALSE, TRUE, "", false, false);
    if(strlen_real($note) > commune::MEMBER_NOTE_MAX_LENGTH) {
        $note = substr($note, 0, commune::MEMBER_NOTE_MAX_LENGTH);
    }
    $backto = "idNote{$user_id}";
    if (commune::UpdateNoteMP($user_id, $commune_id, $note)) {
        $objResponse->assign($backto, "innerHTML", __commPrntMemberNote($user_id, $commune_id, stripslashes($note), true));
        $objResponse->call('tawlTextareaInit');
    }
    return $objResponse;
}

/**
 * Обновление заметки о пользователе на главной странице сообществ
 *  
 * @param integer $user_id     -  uid пользователя
 * @param integer $commune_id  -  id сообщества
 * @param string  $note        -  заметка
 * @return xajaxResponse 
 */
function UpdateNoteMP($user_id, $commune_id, $note) {
    $objResponse = new xajaxResponse();

    if(get_uid(false)!=$user_id) return $objResponse;

    $note = change_q_x(stripcslashes($note), FALSE, TRUE, "", false, false);
    if (strlen_real($note) > commune::MEMBER_NOTE_MAX_LENGTH) {
        $objResponse->alert('Максимальное количество символов '.commune::MEMBER_NOTE_MAX_LENGTH);
        $objResponse->script('$("ne2'.$user_id.'").getElement("textarea").disabled=false;');
        return $objResponse;
    }

    if (commune::UpdateNoteMP($user_id, $commune_id, $note)) {
        if (preg_match) {
            $objResponse->assign("ne1{$user_id}", "innerHTML", reformat(stripslashes($note), 20, 0, 0, 1, 15));
            $objResponse->script('
                memberNoteForm('.$user_id.');
                $("ne2'.$user_id.'").getElement("textarea").disabled=false;
            ');
        }
    }

    return $objResponse;
}

function Vote($pfx, $commune_id, $user_id, $prev_rating, $vote)
{
  $objResponse = new xajaxResponse();
  if($commune_id==5100) return $objResponse;
  if(get_uid(false)!=$user_id) return $objResponse;
  // !!! Вообще, везде бы, где ajax, проверку такую делать.
  $uStatus = commune::GetUserCommuneRel($commune_id, $user_id);
  if(!$uStatus || !$uStatus['is_accepted'] || $uStatus['is_deleted'] || $uStatus['is_banned'] || is_banned($user_id)) 
    $objResponse->script("document.location.replace('/commune/?id={$commune_id}')"); // перекидываем на гл. страницу, а там его перебросит куда надо.
  else {
    $comm = commune::GetCommune($commune_id, $user_id);
    if ($comm && !$comm['is_blocked']) {
        $v = commune::Vote($commune_id, $user_id, intval($vote));
        // заменяем html'овский минус (&minus;) на -
        $prev_rating = str_replace('–', '-', $prev_rating);
        $rating = intval($prev_rating) + $v;
        $html = __commPrntRating($comm, $user_id, $rating);
        $objResponse->assign($pfx.$commune_id,"innerHTML", $html);
        $objResponse->script("if(window.lockRating{$commune_id}) lockRating{$commune_id}=0;");
    }
  }

  return $objResponse;
}

function VoteTopic($topic_id, $user_id, $mod, $vote)
{
    global $session;
    session_start();
  $objResponse = new xajaxResponse();
  if(!get_uid(false)) return $objResponse;
  if(get_uid(false)!=$user_id) return $objResponse;
  if(!commune_carma::isAllowedVote()) {
      return $objResponse;
  }
  // !!! Вообще, везде бы, где ajax, проверку такую делать.
  $topic = commune::GetTopMessageByAnyOther($topic_id, $user_id, $mod);
  $uStatus = commune::GetUserCommuneRel($topic['commune_id'], $user_id);
  if(((!$uStatus || !$uStatus['is_accepted']) && ((!$uStatus['is_author'] && $topic['user_id'] != $user_id )))
          || $uStatus['is_deleted']
          || $uStatus['is_banned']
          || is_banned($user_id)){
      $objResponse->script("lockRating{$topic_id}=0;");
    return $objResponse;
          } else {
    
    if ($topic && !$topic['is_blocked']) {
        $v = commune::TopicVote($topic_id, $user_id, intval($vote));
//        $rating = intval($prev_rating) + $v;
        //$rating = commune::GetTopicRating($topic_id);
        $html = __commPrntTopicRating(commune::GetTopMessageByAnyOther($topic_id, $user_id, commune::MOD_COMM_MODERATOR), $mod, $user_id);
        $objResponse->assign('topicRate_'.$topic_id,"innerHTML", $html);
        $objResponse->script("if(window.lockRating{$topic_id}) lockRating{$topic_id}=0;");
    }
  }

  return $objResponse;
}

function commPrntCommentForm($id, $om) {
    global $session;
    
    $backto = "editmsg";
    $objResponse = new xajaxResponse();
    $objResponse->assign($backto, 'style.position', 'static');
    $objResponse->assign($backto, 'innerHTML', __commPrntCommentForm($id, $om) );

    $objResponse->call('initWysiwyg');
    $objResponse->call('tawlTextareaInit');
    $objResponse->script('try{__attachInit()}catch(e){}');
    $objResponse->script('try{DraftInit(4)}catch(e){}');
    
    return $objResponse;
}


function CreateCommentForm($backto, $top_id, $message_id, $commune_id, $om, $page=0, $action = 'Create.post', $mod, $adv=0, $draft_id=0, $attachedfiles_session='')
{
  global $session;
  session_start();
  commune::RestoreMarkedAttach($message_id);
  $objResponse = new xajaxResponse();
  if($action == 'Create.post') { // Комментируем сообщение.
    $objResponse->assign($backto, 'style.position', 'static');
    $objResponse->assign($backto, 'innerHTML',
    __commPrntCommentForm($commune_id,
                          $om,
                          $page,
                          $action,
                          $top_id,
                          NULL,
                          $message_id, // Родитель. Добавим ему комментарий.
                          NULL,
                          NULL,
                          (!$page?'Topic':NULL), $mod));
  }
  else {
    // Выводим форму с атрибутами сообщения.
    $objResponse->assign($backto, 'style.position', 'static');
    $objResponse->assign($backto, 'innerHTML',
      __commPrntCommentForm($commune_id,
                            $om,
                            $page,
                            $action,
                            $top_id,
                            $message_id,
                            NULL, // Родитель будет получен фунцией.
                            NULL,
                            NULL,
                            (!$page?'Topic':NULL), $mod,0, $draft_id));
  }

  $objResponse->script(
	// Убиваем предыдущую открытую форму со страницы.
  // Все бы это вовне делать, но асинхронус портит картину...
  // Имеет смысл только на странице комментариев (?site=Topic) 
	// __commLastOpenedForm -- это в реальности не форма, а див, содержащий форму редактирования.
	// и __commLastOpenedForm.action просто хранит значение, определяющее какого типа форму содержит див, для
	// всяких проверок.
  " var editMsg = document.getElementById('{$backto}');
    if(__commLastOpenedForm!=editMsg) {
      try { 
        if(!__commLastOpenedForm) {
          var ___acf = document.getElementById('idAlertedCommentForm');
          if(___acf && ___acf.parentNode)
            __commLastOpenedForm = ___acf.parentNode;
        }
        __commLastOpenedForm.innerHTML = ''; __commLastOpenedForm.style.position='absolute'; 
      } catch(e) {}
    }
    __commLastOpenedForm  = editMsg;
    __commLastOpenedForm.action = '{$action}';
	
	poll.init('Commune', document.getElementById('".$backto."'), ".commune::POLL_ANSWERS_MAX.", '".$_SESSION['CommunePoll_Sess']."');
	if (document.getElementById('question')) maxChars('question', 'polls_error', ".commune::POLL_QUESTION_CHARS_MAX.");
	editMsg.scrollIntoView(true);
	//new mAttach(document.getElementById('files_block'), ".(commune::MAX_FILES-$adv).");
        //mA = new mAttach2(document.getElementById('files_block'), ".(commune::MAX_FILES-$adv).", {p:'btn-add', m:'btn-del', nv:true});
  ");

    $js = "var attachedfiles_list = new Array();\n";
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/attachedfiles.php");
    $attachedfiles = new attachedfiles($attachedfiles_session);
    $attachedfiles_tmpcomm_files = commune::getAttachedFiles($message_id);
    if($attachedfiles_tmpcomm_files) {
        $attachedfiles_comm_files = array();
        foreach($attachedfiles_tmpcomm_files as $attachedfiles_comm_file) {
            $attachedfiles_comm_files[] = $attachedfiles_comm_file;
        }
        $attachedfiles->setFiles($attachedfiles_comm_files);
    }    

    $attachedfiles_files = $attachedfiles->getFiles();
    if($attachedfiles_files) {
        $n = 0;
        foreach($attachedfiles_files as $attachedfiles_file) {
            $js .= "attachedfiles_list[{$n}] = new Object;\n";
            $js .= "attachedfiles_list[{$n}].id = '".md5($attachedfiles_file['id'])."';\n";
            $js .= "attachedfiles_list[{$n}].name = '{$attachedfiles_file['orig_name']}';\n";
            $js .= "attachedfiles_list[{$n}].path = '".WDCPREFIX."/{$attachedfiles_file['path']}{$attachedfiles_file['name']}';\n";
            $js .= "attachedfiles_list[{$n}].size = '".ConvertBtoMB($attachedfiles_file['size'])."';\n";
            $js .= "attachedfiles_list[{$n}].type = '{$attachedfiles_file['type']}';\n";
            $n++;
        }
    }
    $js .= "attachedFiles.init('attachedfiles', 
                               '".$attachedfiles->getSession()."',
                               attachedfiles_list, 
                               '".commune::MAX_FILES."',
                               '".commune::MAX_FILE_SIZE."',
                               '".implode(', ', $GLOBALS['disallowed_array'])."',
                               'commune',
                               '".get_uid(false)."'
                            );";
    $objResponse->script($js);

    $objResponse->call('initWysiwyg');
    $objResponse->call('tawlTextareaInit');
    if($action != 'Create.post') {
        $objResponse->script("DraftInit(4);");
    }
  return $objResponse;
}

// !!! Надо картинку убивать со страницы тогда сразу!
// !!! А может и не надо!
function DeleteAttach($backto, $where_id, $file, $login, $callBack)
{
  global $session, $DB;
  session_start();
  $objResponse = new xajaxResponse();
  
  $sql = "SELECT u.login, u.uid FROM commune c INNER JOIN users u ON u.uid = c.author_id WHERE id = ?";
  $commune = $DB->row($sql, $where_id);
  if($commune['uid'] != get_uid(false) && !hasPermissions('communes')) { // Подразумевает изменение картинки только автором сообщества или админом
      return $objResponse;
  }
  $login = $commune['login'];
  
  if (!($DB->query("UPDATE commune SET image = '' WHERE id = ?", $where_id)))
    return $objResponse;

  $dir = "users/".substr($login,0,2)."/".$login."/upload/"; 
  $file = str_replace('sm_', '', $file);
  if ($file){
      $cfile = new CFile();
      $cfile->Delete(0,$dir,$file);
      $cfile->Delete(0,$dir,'sm_'.$file);
  }

  $objResponse->assign($backto, 'innerHTML', $callBack());

  return $objResponse;
}


//////////// !!!!!!!!!!!!!!!!!!! опросы !!!!!!!!!!!!!!!!!!!!!!!!!! ////////////////

/**
 * 
 * HTML для отображения результата, когда проект еще открыт
 * 
 * @param $objResponse
 * @param $poll          -  массив с вариантами ответов
 * @param $voted         -  пользователь голосовал в этом опросе?
 */	
function CommunePoll_ShowResult($theme_id, &$objResponse, &$poll, $voted) {
	$html = '';

	for ($i=0; $i<count($poll); $i++) {
		$html .= "
			<tr>
				<td class='bp-gres'>{$poll[$i]['votes']}</td>
				<td><label for='poll-{$theme_id}_{$i}'>".reformat($poll[$i]['answer'], 30, 0, 1)."</label></td>
			</tr>
		";
	}
	
	$objResponse->assign('poll-answers-'.$theme_id, 'innerHTML', "<table class='poll-variants'>$html</table>");
	$objResponse->assign('poll-btn-vote-'.$theme_id, 'innerHTML', '');
    $objResponse->assign('poll-btn-close-'.$theme_id, 'innerHTML', '<a class="b-buttons__link b-buttons__link_dot_0f71c8" href="javascript: return false" onclick="poll.close(\'Commune\', '.$theme_id.'); return false;" >Закрыть опрос</a>&nbsp;&nbsp;&nbsp;');
    $html = $voted ? '' : '<a class="b-buttons__link b-buttons__link_dot_0f71c8" href="javascript: return false;" onclick="poll.showPoll(\'Commune\', '.$theme_id.'); return false;" >Скрыть результаты</a>&nbsp;&nbsp;&nbsp;';
	$objResponse->assign('poll-btn-result-'.$theme_id, 'innerHTML', $html);
}

/**
 * 
 * HTML для отображения голосования
 * 
 * @param $objResponse
 * @param $poll          -  массив с вариантами ответов
 * @param $radio - 1 - один вариант ответа, 0 - несколько вариантов ответа
 */	
function CommunePoll_ShowPoll($theme_id, &$objResponse, &$poll, $radio = 1) {
    $sType = ( $radio ) ? 'radio' : 'checkbox';
    $sName = ( $radio ) ? '' : '[]';
    
	for ($i=0; $i<count($poll); $i++) {
		if( $sType == 'radio'){
		  $html .= "
		  <div class=\"b-radio__item b-radio__item_padbot_10\">
			  <table class='b-layout__table b-layout__table_width_full' cellpadding='0' cellspacing='0' border='0'>
				  <tr class='b-layout__tr'>
					  <td class='b-layout__left b-layout__left_width_15'><input id='poll-${theme_id}_${i}'  class=\"b-radio__input b-radio__input_top_-3\" type='$sType' name='poll_vote$sName' value='{$poll[$i]['id']}' /></td>
					  <td class='b-layout__right'><label class=\"b-radio__label b-radio__label_fontsize_13\" for='poll-{$theme_id}_{$i}'>".reformat($poll[$i]['answer'], 30, 0, 1)."</label></td>
				  </tr>
			  </table>
		  </div>";
		}
		elseif( $sType == 'checkbox'){
		  $html .= "
		  <div class=\"b-check b-check_padbot_5\">
			  <input id='poll-${theme_id}_${i}'  class=\"b-check__input\" type='$sType' name='poll_vote$sName' value='{$poll[$i]['id']}' />
			  <label class=\"b-check__label b-check__label_fontsize_13\" for='poll-{$theme_id}_{$i}'>".reformat($poll[$i]['answer'], 30, 0, 1)."</label>
		  </div>";
		}
	}
	if( $sType == 'radio'){
		$objResponse->assign('poll-answers-'.$theme_id, 'innerHTML', "<div class=\"b-radio b-radio_layout_vertical\">$html</div>");
	}
	elseif( $sType == 'checkbox'){
		$objResponse->assign('poll-answers-'.$theme_id, 'innerHTML', "$html");
		}
	$objResponse->assign('poll-btn-vote-'.$theme_id, 'innerHTML', '<a class="b-button b-button_flat b-button_flat_grey" href="javascript: return false;" onclick="poll.vote(\'Commune\', '.$theme_id.'); return false;" >Ответить</a>');
	$objResponse->assign('poll-btn-result-'.$theme_id, 'innerHTML', '<a class="b-buttons__link b-buttons__link_dot_0f71c8" href="javascript: return false;" onclick="poll.showResult(\'Commune\', '.$theme_id.'); return false;">Посмотреть результаты</a>&nbsp;&nbsp;&nbsp;');
	$objResponse->assign('poll-btn-close-'.$theme_id, 'innerHTML', '<a class="b-buttons__link b-buttons__link_dot_0f71c8" href="javascript: return false" onclick="poll.close(\'Commune\', '.$theme_id.'); return false;">Закрыть опрос</a>&nbsp;&nbsp;&nbsp;');
}

/**
 * 
 * HTML для отображения результата, когда проект уже закрыт
 * 
 * @param $objResponse
 * @param $poll          -  массив с вариантами ответов
 */	
function CommunePoll_ShowClosed($theme_id, &$objResponse, &$poll) {
	$max = 0;
	for ($i=0; $i<count($poll); $i++) $max = max($max, $poll[$i]['votes']);
	for ($i=0; $i<count($poll); $i++) {
		$html .= "<tr class=\"quiz-result-txt\">
                <td class=\"bp-gres\">{$poll[$i]['votes']}</td>
                <th>".reformat($poll[$i]['answer'], 30, 0, 1)."</th>
            </tr>
            <tr>
                <td></td>
                <td>
                    <span class=\"quiz-line\" style=\"width: " . ($max? round(((100 * $poll[$i]['votes']) / $max) * 5): 0) . "px; min-width: 8px\"><span><span></span></span></span>
                </td>
            </tr>";
	}
	$objResponse->assign('poll-answers-'.$theme_id, 'innerHTML', "<table class='quiz-results'>$html</table>");
	$objResponse->assign('poll-btn-vote-'.$theme_id, 'innerHTML', '');
	$objResponse->assign('poll-btn-result-'.$theme_id, 'innerHTML', '');
	$objResponse->assign('poll-btn-close-'.$theme_id, 'innerHTML', '<a class="b-buttons__link b-buttons__link_dot_0f71c8" href="javascript: return false" onclick="poll.close(\'Commune\', '.$theme_id.'); return false;" >Открыть опрос</a>&nbsp;&nbsp;&nbsp;');
}

/**
 * Проголосовать или показать результат
 * 
 * @param   integer        $theme_id    id темы
 * @param   integer        $answer_id   id ответа или ноль, если просто отобразить результат
 * @return  xajaxResponse
 */	
function CommunePoll_Vote($theme_id, $answers, $sess) {
	session_start();
	$theme_id = intval($theme_id);
	$uid = $_SESSION['uid'];
	if (!is_array($answers)) {
		$answers = array($answers);
	}
	$tmp = array();
	foreach ($answers as $k=>$v) {
		if (is_numeric($v)) {
			$tmp[] = intval($v);
		}
	}
	$answers = $tmp;
	$objResponse = new xajaxResponse();
	$commune = new commune;
	if ($commune->AccessToTheme($uid, $theme_id) < ($answers? commune::ACL_COMMENTS: commune::ACL_READ)) {
		$objResponse->alert('У Вас нет доступа для голосования в этом сообществе');
		return $objResponse;
	}
	if ($answers) {
		if ($sess && $sess == $_SESSION['rand']) $res = $commune->Poll_Vote($uid, $answers, $error);
		if (!$res) {
			if (!$error) $error = 'Ошибка';
			$objResponse->alert($error);
			
			if($error == "Опрос закрыт") {
			    $poll = $commune->Poll_Answers($theme_id);
			    CommunePoll_ShowClosed($theme_id, $objResponse, $poll);
			    return $objResponse; 
			}
		}
	}
	$poll = $commune->Poll_Answers($theme_id);
	$vote = $commune->Poll_Voted($uid, $theme_id);
	CommunePoll_ShowResult($theme_id, $objResponse, $poll, $vote);
	return $objResponse; 
}

/**
 * Отобразить голосование
 * 
 * @param   integer        $theme_id   id темы
 * @return  xajaxResponse
 */	
function CommunePoll_Show($theme_id, $radio = 1) {
	session_start();
	$theme_id = intval($theme_id);
	$uid = $_SESSION['uid'];
	$objResponse = new xajaxResponse();
	if (commune::AccessToTheme($uid, $theme_id) < commune::ACL_COMMENTS) {
		$objResponse->alert('У Вас нет доступа для голосования в этом сообществе');
		return $objResponse;
	}
	$poll = commune::Poll_Answers($theme_id);
	if (commune::Poll_Voted($uid, $theme_id)) {
		CommunePoll_ShowResult($theme_id, $objResponse, $poll, 1);
	} else {
		CommunePoll_ShowPoll($theme_id, $objResponse, $poll, $radio);
	}
	return $objResponse;
}

/**
 * Закрыть опрос
 *
 * @param   integer        $theme_id    id темы
 * @return  xajaxResponse
 */
function CommunePoll_Close($theme_id) {
	session_start();
	$theme_id = intval($theme_id);
	$uid = $_SESSION['uid'];
	$objResponse = new xajaxResponse();
	if (($access = commune::AccessToTheme($uid, $theme_id)) < commune::ACL_MODER) {
		$objResponse->alert('У Вас нет доступа для этим управления голосованием.');
		return $objResponse;
	}
	$poll = commune::Poll_Answers($theme_id);
	if (commune::Poll_Close($theme_id)) {
		CommunePoll_ShowClosed($theme_id, $objResponse, $poll);
	} else if (commune::Poll_Voted($uid, $theme_id) || $access < commune::ACL_COMMENTS) {
		CommunePoll_ShowResult($theme_id, $objResponse, $poll, 1);
	} else {
	    $aPoll = commune::Poll_Get( $theme_id );
	    $radio = ( $aPoll['multiple'] == 't' ) ? 0 : 1;
		CommunePoll_ShowPoll($theme_id, $objResponse, $poll, $radio);
	}
	return $objResponse;
}

/**
 * Удалить опрос
 *
 * @param   integer   $theme_id   id темы
 * @return  xajaxResponse
 */
function CommunePoll_Remove($theme_id) {
	session_start();
	$theme_id = intval($theme_id);
	$uid = $_SESSION['uid'];
	$objResponse = new xajaxResponse();
	if (($access = commune::AccessToTheme($uid, $theme_id)) < commune::ACL_MODER) {
		$objResponse->alert('У Вас нет доступа для управления этим голосованием.');
		return $objResponse;
	}
	commune::Poll_Remove($theme_id, $msgtext);
	$objResponse->assign("poll-$theme_id", "innerHTML", ($msgtext? "$msgtext<br><br>": ""));
	return $objResponse;
}

/**
 * Подписаться на топик сообщества
 * @param int $theme_id  - id сообщения из commune_messages
 * @param bool $use_new_template  - так как на альфе и боевой используется отчасти старый шаблон, который использует 
 * эту функцию,временно ввожу этот флаг, чтобы сохранить работоспособность обоих вариантов
 * */
function SubscribeTheme($theme_id, $use_new_template = false) {
  session_start();
  if(!isset($_SESSION['uid'])) return;
  
  $subscribeText = 'Подписаться на тему';
  $unsubscribeText = 'Отписаться от темы';
  if ($use_new_template) {
      $subscribeText   = 'Подписаться на комментарии';
      $unsubscribeText = 'Отписаться от комментариев';
  }
  $objResponse = new xajaxResponse();
  $res = commune::SubscribeTheme($theme_id, $_SESSION['uid']);
  $txt = !$res ? $subscribeText : $unsubscribeText;
  if ($use_new_template) {
      $memkey = "comm_topic_subscribe_$theme_id"."_".$_SESSION['uid'];
      $membuf = new memBuff();
      $membuf->delete($memkey);
      $objResponse->script("$('subscribe_to_comm').set('html', '{$txt}');");
  } else {
      $objResponse->script("$('subscrToggle').set('html', '{$txt}');");
  }  
  return $objResponse;
}

/**
 *
 * @param type $commune_id
 * @param type $subscr_value
 * @param type $mode
 * @param bool $fromCommune Если вызов из сообщества
 * @return \xajaxResponse 
 */
function SubscribeCommune($commune_id, $subscr_value, $mode=false, $fromCommune=false){
	session_start();
  	if(!isset($_SESSION['uid'])) return;
  	$cm = new commune();
  	$comm = $cm->getCommune($commune_id,$_SESSION['uid']);
  	$objResponse = new xajaxResponse();

  	$res = commune::setCommuneSubscription($commune_id, $_SESSION['uid'],(bool)$subscr_value);
        if ($fromCommune) {
            $html = __commPrntSubmitButtonFromCommune($comm, $_SESSION['uid'], NULL, $mode);
        } else {
            $html = __commPrntSubmitButton($comm, $_SESSION['uid'], NULL, $mode);
        }
	$objResponse->assign('commSubscrButton_'.$commune_id,"innerHTML", $html);
  	return $objResponse;
}

function JoinDialogCommune($commune_id){
    	session_start();
  	if(!isset($_SESSION['uid'])) return;
        $objResponse = new xajaxResponse();
  	$cm = new commune();
  	$comm = $cm->getCommune($commune_id,$_SESSION['uid']);


  if($comm['author_uid']==$_SESSION['uid'])
    return false;

  $objResponse->script('$("ov_comm_name").assign("innerHTML","'.$comm['name'].'")');
  $func = 'xajax_JoinCommune';
  $msg = '';
  if($comm['current_user_join_status'] == commune::JOIN_STATUS_NOT
          || $comm['current_user_join_status'] == commune::JOIN_STATUS_DELETED){
        $msg = 'Вы действительно хотите вступить в сообщество «<a href="/commune/?id='.$comm['id'].'" id="ov_comm_name">'.$comm['name'].'</a>»?';
        $func = 'xajax_JoinCommune';
  } else if($comm['current_user_join_status'] == commune::JOIN_STATUS_ASKED){
        $msg = 'Ваша заявка на вступление уже направлена администратору сообщества «<a href="/commune/?id='.$comm['id'].'" id="ov_comm_name">'.$comm['name'].'</a>».<br/>Отозвать заявку?';
        $func = 'xajax_OutCommune';
  }else if($comm['current_user_join_status'] == commune::JOIN_STATUS_ACCEPTED){
        $msg = 'Вы действительно хотите покинуть сообщество «<a href="/commune/?id='.$comm['id'].'" id="ov_comm_name">'.$comm['name'].'</a>»?';
        $func = 'xajax_OutCommune';
  }
        $objResponse->assign("ov_msg","innerHTML", $msg);
        $objResponse->script("document.getElementById('ov_submit_btn').onclick = function(){{$func}({$comm['id']}); return false;};");
        $objResponse->script('$("ov-commune-confirm").setStyle("display", "block");');
  	return $objResponse;
}

function JoinCommune($commune_id, $mode=false){
    session_start();
  	if(!isset($_SESSION['uid'])) return;
  	$cm = new commune();
  	$objResponse = new xajaxResponse();
    $result = commune::Join($commune_id, $_SESSION['uid'], false);
    $objResponse->script("$('ov-commune-confirm').setStyle('display','none');");
    $comm = $cm->getCommune($commune_id,$_SESSION['uid']);
    /*
    !!! Посылаем уведомление 
    todo не плохо бы придумать антиспам, пользователь может бесконечно раз выходить и входить в такое сообщество 
    с каждым входом автору сообщества будет высылаться уведомление (skif)
    */
    if($result == commune::JOIN_STATUS_ASKED) { 
        $sm = new smail();
        $sm->CommuneJoinAction($_SESSION['uid'], $comm);
        
    }
    
    if($mode == 1) {
        $href = "'/commune/?id={$commune_id}'";
        $objResponse->script('document.location.href = '.$href);
        return $objResponse;
    }
    if($mode == 2) {
        $objResponse->script('document.location.href = document.location.href');
        return $objResponse;    
    }
    $html = "<a href=\"javascript:void(0)\" onclick=\"xajax_OutCommune({$commune_id}); return false;\"><img src=\"/images/btn-cgoout.png\" alt=\"Выйти из сообщество\"/></a>";
    $objResponse->assign('join_'.$commune_id, 'innerHTML', $html);
    /*$href = "document.location.href";
    if($mode == true) {
        $href = "'/commune/?id={$commune_id}'";
    }
    
    $objResponse->script('document.location.href = '.$href);*/
//  	$html = __commPrntJoinButton($comm, $_SESSION['uid'], null, true);
//        $html_s = __commPrntSubmitButton($comm, $_SESSION['uid'], NULL, $mode);
//        $mAcceptedCnt = $comm['a_count'] - $comm['w_count'] + 1;
//	$objResponse->assign('join_btn_'.$commune_id,"innerHTML", $html);
//        $objResponse->assign('commSubscrButton_'.$commune_id,"innerHTML", $html_s ? $html_s : '');
//        $objResponse->assign('accepted_'.$commune_id,"innerHTML", $mAcceptedCnt);
//        $objResponse->assign('idCommRating_'.$commune_id,'innerHTML',__commPrntRating($comm, $_SESSION['uid']));
  	return $objResponse;
}

function OutCommune($commune_id, $mode=false){
    	session_start();
  	if(!isset($_SESSION['uid'])) return;
  	$cm = new commune();
  	
  	$objResponse = new xajaxResponse();
        commune::Join($commune_id, $_SESSION['uid'], true);
        $comm = $cm->getCommune($commune_id,$_SESSION['uid']);
        $is_restricted = (bitStr2Int($comm['restrict_type']) & commune::RESTRICT_JOIN_MASK);
        /*$url = substr($comm['restrict_type'], 1, 1) == '1' ? '/commune/?id='.$commune_id : 'document.location.href';
        $objResponse->script("$('ov-commune-confirm').setStyle('display','none');");
        $objResponse->script('document.location.href = '.$url);*/
    if($mode == 1 || $mode == 2) {
        $objResponse->script('document.location.href = document.location.href');
        return $objResponse;
    }
    
    $html = '<a href="javascript:void(0)" onclick="xajax_JoinCommune('.$comm["id"].', '.$is_restricted.'); return false;"><img src="/images/btn-сgoin.png" alt="Вступить в сообщество"/></a>'; 
    $objResponse->assign('join_'.$commune_id, 'innerHTML', $html);    
        
  	return $objResponse;
}

function FileMoveTo($id, $cid, $direction = 'up') {
    $objResponse = new xajaxResponse();
    $error = false;
    if ($direction == 'up') {
        $error = commune::MoveUp($id);
    } else {
        $error = commune::MoveDown($id);
    }
    if (!$error) {
        $uploaded = commune::GetAttach($cid, true);
        $page = TPL_COMMUNE_PATH . '/uploaded_files.php';
        ob_start();
        include($page);
        $html = ob_get_contents();
        ob_end_clean();
        $objResponse->assign('uploaded_list', 'innerHTML', $html);
    } else {
        $objResponse->assign('uploaded_list', 'innerHTML', $error);
    }
    return $objResponse;
}

function MsgDelFile($cid, $file_id){
    session_start();
    $cid = __paramValue('int', $cid);
    $file_id = __paramValue('int', $file_id);
//    if(!isset($_SESSION['uid'])) return;
    $objResponse = new xajaxResponse();
    if($result = commune::DeleteAttach($cid, $file_id, true)){
        $uploaded = commune::GetAttach($cid, true);
        $mess['user_login'] = $_SESSION['login'];
        $page = TPL_COMMUNE_PATH . '/uploaded_files.php';
        ob_start();
        include($page);
        $html = ob_get_contents();
        ob_end_clean();
        $objResponse->assign('uploaded_list', 'innerHTML', $html);
        $objResponse->script('$("files_block").style.display = "block"');
    }
//        $objResponse->assign('uploaded_list', 'innerHTML', $error);
    return $objResponse;
}

/**
 * Выставляем роли пользователям сообщества (выставлять может только автор сообщества?? это верно или нет)
 *
 * @param integer $commune_id  ИД Сообщества
 * @param integer $member_id   ИД Пользователя
 * @param boolean $is_moder    Флаг модератора
 * @param boolean $is_manager  Флаг менеджера
 * @return 
 */
function setRoleUser($commune_id, $member_id, $is_moder, $is_manager) {    
    $objResponse = new xajaxResponse();
    if(!isset($_SESSION['uid'])) return;
  	$cm = new commune();  
  	$comm = $cm->GetCommune($commune_id);
  	
    // Только хозяин сообщества может такое творить @todo нужно уточнить кто может назначать админов и менеджеров.
  	if($comm['author_id'] == $_SESSION['uid']) {
  	    $cm->UpdateAdmin($member_id, "", $is_moder, $is_manager, $comm);
  	    if($is_moder OR $is_manager) {
  	        $objResponse->assign('cau_admin'.$member_id, 'innerHTML', "Admin&nbsp;");
  	    } else if(!$is_moder AND !$is_manager) {
  	        $objResponse->assign('cau_admin'.$member_id, 'innerHTML', "");
  	    }
  	    
  	    $objResponse->script('$("is_mod_value'.$member_id.'").set("value", '.intval($is_moder).');');
  	    $objResponse->script('$("is_men_value'.$member_id.'").set("value", '.intval($is_manager).');');
  	}
    
    return $objResponse;
}

function realodCommentForm() {
    global $session;    
}
/**
* Восстановить удаленный пост
* @param string $backto             идентификатор блока
* @param string $message_id         идентификатор записи в базе данных
* @param uint   $user_id            идентификатор пользователя
* @param string $mod                режим
* @param string $page               номер страницы
* @param string $om                 нужен для прохода по страницам, возвратам, переходам
* @param string $site               $site==NULL|'Commune' -- топик выводится на странице сообщества (/commune/),
*                                   $site=='Topic' -- на странице комментариев (/commune/?site=Topic),
*                                   $site=='Lenta' -- в ленте (/lenta/).
* @param string $isFav              находится в закладка пользователя $user_id или нет.
**/
function restoreDeletedPost($backto, $message_id, $user_id, $mod, $page, $om, $site, $is_fav) {
    $objResponse = new xajaxResponse();
    if ( hasPermissions("adm") ) {
        commune::RestoreMessage( $message_id );
        $objResponse->script("commune_RestoreMessage('{$backto}', '{$message_id}', '{$user_id}', '{$mod}', '{$page}', '{$om}', '{$site}', '{$is_fav}');");
    }
    return $objResponse;
}

$xajax->processRequest();

//$GLOBALS['xajax']->setCharEncoding("windows-1251");
?>
