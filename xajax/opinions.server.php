<?
$rpath = "../";
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/opinions.common.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/opinions.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr_meta.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");

/**
 * 
 * @deprecated #0015627
 * 
 */
function AddOpinion($source_uid, $dest_uid, $message, $rating, $counter, $from = 'frl') {
    return false;
    $source_uid = get_uid(false);
	$objResponse = new xajaxResponse();
	$message     = trim($message);
	
        if(!in_array($rating,array(-1,0,1))){
            $objResponse->assign("rating_error","innerHTML",'Вы не выбрали категорию отзыва');
        }elseif(opinions::CheckUserCanPost($source_uid, $dest_uid) != 0){
            // левый пользователь
        }
        elseif ( $message == '' ) {
            $objResponse->script( "opinionFormError('error_msg');" );
        }
        elseif ( strlen($message) > opinions::$opinion_max_length ) {
            $objResponse->script( "opinionMaxLengthError('msg', ".opinions::$opinion_max_length.");" );
        }
        else{

            $message = str_replace('&', '&amp;', $message);
            $message = stripslashes($message);
            $message = change_q_x($message, FALSE, TRUE, "", false, false);
            
            $error = opinions::NewMsg($source_uid, $dest_uid, $message, $rating, getRemoteIP(), $new_id);
            if(!$error){
                    $msg = opinions::GetLastMessage($source_uid, $dest_uid);
                if($msg){
                    $user = new users();
                    $user->GetUserByUID($dest_uid);
                    $objResponse->remove("form_container");
                    
                    if ((is_emp() && $from == 'frl') || (!is_emp() && $from == 'emp')) {
                        $from = $from == 'frl' ? 'emp' : 'frl';
                        $objResponse->redirect("/users/{$user->login}/opinions/?from={$from}#o_{$new_id}");
                        return $objResponse;
                    }
                    
                    $objResponse->prepend("messages_container","innerHTML",  opinions::printTheme($msg, $from, $counter, $user));
                    $objResponse->prepend("messages_container","innerHTML",  opinions::printAddForm($source_uid, $dest_uid, $from));

                    $nt = $rating == 1 ? 'plus' : ($rating == -1 ? 'minus' : 'neitral');
                    $objResponse->call('opinionChConuters', NULL, 'ops-'.$from.$nt);
                    
                    $objResponse->script("opinionCheckMaxLengthStop('msg');");
                    $objResponse->assign('no_messages','innerHTML','');
                }else{
                    $objResponse->script("alert('".serialize($msg)."')");
                }
            }else{
                $objResponse->script("alert('".$error."')");
            }
            
        }
        return $objResponse;
}

function EditOpinion($op_id, $message, $rating, $counter, $from = 'frl'){
    @session_start();
	$objResponse = new xajaxResponse();
	$message     = trim($message);
	
        if(!in_array($rating,array(-1,0,1))){
            $objResponse->assign("rating_error","innerHTML",'Вы не выбрали категорию отзыва');
        }
        elseif ( $message == '' ) {
            $objResponse->script( "opinionFormError('error_edit_msg_{$op_id}');" );
        }
        elseif ( strlen($message) > opinions::$opinion_max_length ) {
            $objResponse->script( "opinionMaxLengthError('edit_msg_{$op_id}', ".opinions::$opinion_max_length.");" );
        }
        else{

            $message = str_replace('&', '&amp;', $message);
            $message = stripslashes($message);
            $message = change_q_x($message, FALSE, TRUE, "", false, false);

            $msg = opinions::GetMessageById($op_id);
            $old_rating = $msg['rating'];
            if (get_uid(0) == $msg['fromuser_id'] || hasPermissions('users')) {
                $error = opinions::Edit($msg['fromuser_id'], $op_id, $message, $rating, getRemoteIP(), hasPermissions('users'), get_uid(0));
            } else {
                $error = "Ошибка";
            }
            if(!$error){
                $msg = opinions::GetMessageById($op_id);
                if($msg){

                    $user = new users();
                    $user->GetUserByUID($_SESSION['page_user_id']);
                    
                    $objResponse->assign("opid_".$op_id,"innerHTML", opinions::printTheme($msg, $from, $counter, false));
                    
                    $ot = $old_rating == 1 ? 'plus' : ($old_rating == -1 ? 'minus' : 'neitral');
                    $nt = $rating == 1 ? 'plus' : ($rating == -1 ? 'minus' : 'neitral');
                    $objResponse->call('opinionChConuters', 'ops-'.$from.$ot, 'ops-'.$from.$nt);
                    
                    $block_class = 'ops-one-'.$nt;
                    $objResponse->script("$('opid_$op_id').removeClass('ops-one-plus').removeClass('ops-one-neitral').removeClass('ops-one-minus');");
                    $objResponse->script("$('opid_$op_id').addClass('$block_class')");
                    $objResponse->script("opinionCheckMaxLengthStop('edit_msg_{$op_id}');");

                }else{
                    $objResponse->script("alert('".serialize($msg)."')");
                }
            }else{
                $objResponse->script("alert('".$error."')");
            }

        }
        return $objResponse;
}


function EditOpinionForm($op_id,$from='frl'){
	$objResponse = new xajaxResponse();
        $ele_id = 'msg_cont_'.$op_id;
        $msg_id = 'message_text_'.$op_id;
        $edit_block = 'edit_block_'.$op_id;
        $objResponse->script("$('$edit_block').setStyle('display', 'none');");
        $objResponse->script("$('$msg_id').setStyle('display', 'none');");
        $objResponse->prepend($ele_id, "innerHTML",  opinions::printEditOpForm($op_id,$from));
        
        $objResponse->script("opinionCheckMaxLengthStart('opinion', 'edit_msg_{$op_id}'); window._opiLock = false;");
        
        return $objResponse;
}

/**
 * 
 * @param type $op_id
 * @param type $from
 * @param type $isFeedback если true значит это отзыв из СБР, если false - мнение
 * @return \xajaxResponse
 */
function AddOpComentForm($op_id, $from='frl', $isFeedback = false){
	$objResponse = new xajaxResponse();
        if ($isFeedback) {
            $prefix = 'feedback_';
        } else {
            $prefix = '';
        }
        $link_id = $prefix . 'comment_content_'.$op_id;
        $objResponse->script("opinionCloseAllForms()");
        $objResponse->script("$('$link_id').setStyle('display', 'none');");
        $objResponse->prepend($prefix . 'comment_' . $op_id, "innerHTML",  opinions::printEditComForm($op_id, $from, $isFeedback));
        
        $objResponse->script("opinionCheckMaxLengthStart('coment', '" . $prefix . "edit_comm_{$op_id}'); window._opiLock = false;");
        $objResponse->script("opinionCheckMaxLengthUpdater('" . $prefix . "edit_comm_{$op_id}')");
        $objResponse->script("$('" . $prefix . "comment_$op_id').setStyle('display', '')");
        
        return $objResponse;
}

function EditOpinionComm($op_id, $comm_id, $text, $from='frl', $isFeedback = false){
    @session_start();
    $objResponse = new xajaxResponse();
    $text = trim($text);
    
    if ( $text == '' ) {
        $objResponse->script( "opinionCommentFormError('$op_id');" );
    }
    elseif ( strlen(stripslashes($text)) > opinions::$comment_max_length ) {
        $objResponse->script( "opinionMaxLengthError('edit_comm_{$op_id}', ".opinions::$comment_max_length.");" );
    }
    else {
        if ($isFeedback) {
            $msg = sbr_meta::getFeedback($op_id, true);
        } else {
            $msg = opinions::GetMessageById($op_id);
        }
    
        $text = str_replace('&', '&amp;', $text);
        $text = stripslashes($text);
        $text = change_q_x($text, FALSE, TRUE, "", false, false);
        
        $prefix = $isFeedback ? 'feedback_' : '';
    
        if ( (int)$comm_id > 0 ) { // Редактируем
            if ($isFeedback) {
                opinions::editCommentFeedback($text, get_uid(false), $comm_id, $msg['fromuser_id']);
            } else {
                opinions::editCommentOpinion($text, get_uid(false), $comm_id);
            }
            $objResponse->script("$('" . $prefix . "comment_content_{$op_id}').setStyle('display', '');
                                  $('" . $prefix . "opinion_btn_edit_comment_{$op_id}').setProperty('disabled', '');
                              $('" . $prefix . "ed_comm_form_{$op_id}').dispose();");
            //$objResponse->assign($prefix . "comment_text_{$op_id}", "innerHTML", $text);
            $html = opinions::printCommentOpinions($op_id, $isFeedback);
            $objResponse->script("$('" . $prefix . "comment_cont_{$op_id}').empty()");
            $objResponse->append($prefix . "comment_cont_{$op_id}", "innerHTML", $html);
        } else { // Добавляем
            if ($isFeedback) {
                opinions::newCommentFeedback($text, get_uid(false), $op_id);
            } else {
                opinions::newCommentOpinion($text, get_uid(false), $op_id);
            }
            $html = opinions::printCommentOpinions($op_id, $isFeedback);
            $objResponse->script("$('" . $prefix . "ed_comm_form_{$op_id}').dispose();");
            $objResponse->append($prefix . "comment_cont_{$op_id}", "innerHTML", $html);
        }
        
        $objResponse->script("opinionCheckMaxLengthStop('" . $prefix . "edit_comm_{$op_id}');");
    }
    
    return $objResponse;
}

function DeleteOpinion($op_id,$from='frl'){
    @session_start();
    $op_id = intval($op_id);
    $objResponse = new xajaxResponse();
    $msg = opinions::GetMessageById($op_id);
    $old_rating = $msg['rating'];
    if (get_uid(0) == $msg['fromuser_id'] || hasPermissions('users')) {
        $error = opinions::DeleteMsg($msg['fromuser_id'], $op_id, hasPermissions('users'));
    } else {
        $error = "Вы не можете удалить мнение об этом пользователе.";
    }
    if(!$error){
        //$user = new users();
        //$user->GetUserByUID($_SESSION['page_user_id']);
        
        $ot = $old_rating == 1 ? 'plus' : ($old_rating == -1 ? 'minus' : 'neitral');
        $objResponse->call('opinionChConuters', 'ops-'.$from.$ot); ///?????
        $objResponse->remove("opinion_$op_id");
        
        //$objResponse->remove("opid_$op_id");
        //$objResponse->remove("form_container");
        // @deprecated #0015627
        // $objResponse->prepend("messages_container","innerHTML",  opinions::printAddForm(get_uid(false),$msg['touser_id'],$from)); 
        //$objResponse->call('opinionsFormBtns');
        
    }else{
        $objResponse->script("alert('$error')");
    }
    return $objResponse;
}

/**
 * удаляет комментарий к мнению, отзыву
 * @param type $op_id
 * @param type $comm_id
 * @param type $from
 * @param type $isFeedback если true - это отзыв
 * @return \xajaxResponse
 */
function DeleteOpinionComm($op_id, $comm_id, $from='frl', $isFeedback = false){
    @session_start();
    $objResponse = new xajaxResponse();
    if ($isFeedback) {
        $msg = sbr_meta::getFeedback($op_id, true);
    } else {
        $msg = opinions::GetMessageById($op_id);
    }
    if (get_uid(0) == $msg['touser_id'] || hasPermissions('users')) {
        $error = opinions::deleteComment($comm_id, get_uid(false), hasPermissions('users'), $isFeedback);
    } else {
        $error = 'Вы не можете удалить комментарий.';
    }
    
    $prefix = $isFeedback ? 'feedback_' : '';
    
    if(!$error){
        $objResponse->script("$('{$prefix}opinion_btn_add_comment_{$op_id}').setStyle('display', ''); 
                              $('{$prefix}opinion_btn_add_comment_{$op_id}').setProperty('disabled', '');
                              $('{$prefix}opinion_btn_edit_comment_{$op_id}').setProperty('disabled', '');
                              $('{$prefix}comment_content_{$op_id}').dispose();");
    }else{
        $objResponse->script("alert('$error')");
    }
    return $objResponse;
}


$xajax->processRequest();
?>
