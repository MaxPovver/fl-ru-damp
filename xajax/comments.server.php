<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/xajax/comments.common.php';
require_once $_SERVER['DOCUMENT_ROOT'] ."/classes/stdf.php";

/**
 * Выводит форму редактировая комментария в админке комментариев
 *
 * @param  integer  $type  Тип группы комментариев
 * @param  integer  $id    id комментария
 * @return xajaxResponse
 */
function EditComment($type, $id) {
 require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/comments.php';

	session_start();
	$objResponse = new xajaxResponse();

	if(!hasPermissions('comments')) {
		return $objResponse;
	}
	
	$comments = new comments;
	$item = $comments->GetItem(intval($type), intval($id));

	$show_title = false;
	$show_files = ($type == comments::T_ARTICLES);
	$show_video = ($type == comments::T_ARTICLES);

	if (!empty($item)) {
	    define( 'IS_SITE_ADMIN', 1 );
	    require_once $_SERVER['DOCUMENT_ROOT'] . '/siteadmin/comments/blocks.php';
		$objResponse->assign("edit-{$type}-{$id}", 'innerHTML', CommentEditor($item, $show_title, $show_files, $show_video));
		if ($show_video) {
			$objResponse->script("$$('.cl-form-files li input[type=image]').addEvent('click', FilesList)");
		}
	}

	return $objResponse;

}


function RateComment($sname, $item, $dir) {
    
    session_start();
    $objResponse = new xajaxResponse();

    $uid = get_uid(false);
    if(!$uid) {
        return $objResponse;
    }

    $obj = null;
    
    switch(strtolower($sname)) {
        case 'commune':
            if(!commune_carma::isAllowedVote()) {
                return $objResponse;
            }
            require_once $_SERVER['DOCUMENT_ROOT'] ."/classes/comments/CommentsCommune.php";
            $comments = new CommentsCommune($item);
            $data     = $comments->getData($item);
            if($data['author'] == $uid) return $objResponse; // За свой коммент голосовать нельзя 
            break;
        default:
            return $objResponse;
    }

    $result = $comments->RateComment($uid, $item, $dir);
    $jsfunct = "RateCommentCallback";
    if($comments->is_new_template) {
        $jsfunct = "RateCommentCallbackNew";
    }
    $objResponse->call($jsfunct, $item, $dir);

    return $objResponse;
    
}


function GetComment($sname, $item) {
    
    session_start();
    $objResponse = new xajaxResponse();

    $uid = get_uid(false);
    if(!$uid) {
        return $objResponse;
    }

    switch(strtolower($sname)) {
        case 'commune':
            require_once $_SERVER['DOCUMENT_ROOT'] ."/classes/comments/CommentsCommune.php";
            $comments = new CommentsCommune( $item, date('Y-m-d H:i:s') );

            break;
        case 'articles':
            require_once $_SERVER['DOCUMENT_ROOT'] ."/classes/comments/CommentsArticles.php";
            $comments = new CommentsArticles( $item, date('Y-m-d H:i:s') );

            break;
        case 'adminlog':
            require_once $_SERVER['DOCUMENT_ROOT'] ."/classes/comments/CommentsAdminLog.php";
            $comments = new CommentsAdminLog( $item, date('Y-m-d H:i:s') );
            break;
        default:
            return $objResponse;
    }

    $result = $comments->getData($item);

    $attach = $result['attach'];
    unset($result['attach']);
    $msg = $result;
    if ($comments->enableWysiwyg) {
        $msg['msgtext'] = str_replace('  ', '&nbsp;&nbsp;', $msg['msgtext']);
    } else {
        $msg['msgtext'] = str_replace(array('&#039;'), array('\''), $msg['msgtext']);
        $msg['msgtext'] = htmlspecialchars_decode($msg['msgtext']);
    }
//    $msg['msgtext'] = preg_replace_callback("/<([^\s>]+)[^>](.*?)*>/si",
//            create_function('$matches', 'return str_replace("&nbsp;", " ", $matches[0]);'),
//        $msg['msgtext']);
    if($comments->enableNewWysiwyg) $msg['msgtext'] = html2wysiwyg($msg['msgtext']);
    $objResponse->call('commentEditCallback', $msg, $attach);

    return $objResponse;

}

$xajax->processRequest();

?>
