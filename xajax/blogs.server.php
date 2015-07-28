<?
$rpath = "../";
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/blogs.common.php");

function ResetAttachedfiles() {
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/blogs.php");
	$objResponse = new xajaxResponse();
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/attachedfiles.php");
    $attachedfiles = new attachedfiles('', true);
    $asid = $attachedfiles->createSessionID();
    $attachedfiles->addNewSession($asid);

    $objResponse->assign("attachedfiles", "innerHTML", '');

    $objResponse->script("
    								var attachedfiles_list = new Array();
                                    attachedFiles.init('attachedfiles', 
                                                       '{$asid}',
                                                       attachedfiles_list, 
                                                       '".blogs::MAX_FILES."',
                                                       '".blogs::MAX_FILE_SIZE."',
                                                       '".implode(', ', $GLOBALS['disallowed_array'])."',
                                                       'blog',
                                                       '".get_uid(false)."'
                                                       );

                                ");
   	return $objResponse;

}

function AddFavBlog($thread_id, $priority = 0, $is_inner = 0, $action = "add", $gr_num = 0) {
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/blogs.php");
	global $session;
	session_start();
	$uid = $_SESSION['uid'];
    
    $thread_id = intval($thread_id);
    $priority  = intval($priority);
    $is_inner  = intval($is_inner);
    $gr_num    = intval($gr_num);

	$objResponse = new xajaxResponse();

	$blogs = new blogs();
	
	if ($thread_id && $uid){
		$info = $blogs->ChangeFav($thread_id, $priority, $uid, $action);
	}

	$refresh_order = ( isset($_SESSION["blogs_favs_order"]) ) ? $_SESSION["blogs_favs_order"] : 'date';
	
	if (isset($info) || $refresh_order != "") {
	    $nFavCnt = 0;
		$favs    = $blogs->GetFavorites($uid, $refresh_order, $gr_num);
		
	 	if ($favs) { 
		 	$inner='<ul>';
	 		foreach ($favs as $ikey => $fav) { 
	 		    if ( $ikey == $thread_id ) {
	 		    	$nFavCnt = $fav['fav_cnt'];
	 		    }
	 		    
                $inner .= 
                '<li id="fav'.$ikey.'">
                    <span class="opt">
            			<img onClick="xajax_EditFavBlog('.$ikey.', '.$gr_num.')" src="/images/ico-e-u.png" alt="Редактировать" style="cursor: pointer;">&nbsp;&nbsp;
            			<img onClick="xajax_DelFavBlog('.$ikey.', '.$gr_num.')" src="/images/btn-remove2.png" alt="Удалить" style="cursor: pointer;">
            		</span>
            		<span class="stat"><img src="/images/bookmarks/'.blogs::$priority_img[ $fav['priority'] ].'" alt=""> '.blogs::$priority_name[ $fav['priority'] ].'</span>
            		<a href="/blogs/view.php?tr='.$ikey.'">'.( $fav['title'] ? reformat($fav['title'], 37, 0, 1) : '<без темы>' ).'</a>
            		<input type="hidden" id="favpriority'.$ikey.'" value="'.$fav['priority'].'">
                </li>';
            }
			$inner.='</ul>';
		}	
		if ($info[1]) {
			if (!$is_inner)
			{
				$objResponse->assign("fav_ul","innerHTML",$inner);
				$objResponse->assign("favpriority","innerHTML",$priority);	
			}
			$objResponse->assign("favstar".$thread_id,"src",'/images/bookmarks/'.blogs::$priority_img[$priority]);
		}
		else {
			if (!$is_inner)
			{
				if (!$favs) {  $inner.='<div>Нет закладок</div>';	}
				$objResponse->assign("fav_ul","innerHTML",$inner);
				$objResponse->assign("favpriority","innerHTML",$priority);	
			}
			$objResponse->assign("favstar".$thread_id,"src",'/images/bookmarks/'.blogs::$priority_img[$priority]);
		}

		if ($action == "delete")
		{
			$objResponse->assign("favstar".$thread_id,"src",'/images/bookmarks/bsw.png');
		}
		
		$objResponse->assign( "favcnt$thread_id", 'innerHTML', '<span>'.$nFavCnt.'</span>' );
	}
	return $objResponse;
}

function DelFavBlog( $thread_id, $gr_num = 0 ) {
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/blogs.php");
	global $session;
	session_start();
	$uid = $_SESSION['uid'];
	
	$objResponse = new xajaxResponse();
	$blogs = new blogs();
	
	$fav = $blogs->GetFavoriteByThreadID( $uid, $thread_id );
	
	if ( $fav ) {
        $outHTML = '<span class="opt-del">
			<button onclick="xajax_AddFavBlog('.$thread_id.', 0, 0, \'delete\', '.$gr_num.')">Удалить</button>
			<a href="javascript:void(0);" onclick="xajax_AddFavBlog('.$thread_id.', 0, 0, \'\', '.$gr_num.')" class="lnk-dot-666">Отмена</a>
		</span>
		<a href="/blogs/view.php?tr='.$thread_id.'">'.( $fav['title'] ? reformat($fav['title'], 37, 0, 1) : '<без темы>' ).'</a>';
		
		$objResponse->assign( "fav".$thread_id, "innerHTML", $outHTML );
	}
	
	return $objResponse;
}

function EditFavBlog($thread_id, $gr_num = 0, $priority = 0, $title = "", $action = "edit"){
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/blogs.php");
	global $session;
	session_start();
	$uid = $_SESSION['uid'];
	$objResponse = new xajaxResponse();

	$thread_id = intval($thread_id);
	$GLOBALS['xajax']->setCharEncoding("windows-1251");

	$action = trim($action);

	switch ($action) {
		case "update":
			$blogs = new blogs();

			$title = change_q_x($title, true, false);
			$updatefav = $blogs->UpdateFav($thread_id, $uid, $priority, $title);
			
			return AddFavBlog( 0, 0, 0, '', $gr_num );
		break;

		case "edit":
			$blogs   = new blogs();
			$editfav = $blogs->GetFavoriteByThreadID($uid, $thread_id);
			$outHTML = 
			'<span class="opt-edit">
    			<select name="sel_favs_priority" id="sel_favs_priority" onChange="FavPriority('.$thread_id.', this.value)">
                    <option value="0"'.($editfav['priority'] == 0 ? ' selected' : '').'>'.blogs::$priority_name[0].'</option>
                    <option value="1"'.($editfav['priority'] == 1 ? ' selected' : '').'>'.blogs::$priority_name[1].'</option>
                    <option value="2"'.($editfav['priority'] == 2 ? ' selected' : '').'>'.blogs::$priority_name[2].'</option>
                    <option value="3"'.($editfav['priority'] == 3 ? ' selected' : '').'>'.blogs::$priority_name[3].'</option>
    			</select>
    			<button onClick="if(document.getElementById(\'favtext'.$thread_id.'\').value.length>250){alert(\'Слишком длинное название закладки!\');return false;}else{xajax_EditFavBlog('.$thread_id.', '.$gr_num.', document.getElementById(\'favpriority'.$thread_id.'\').value, document.getElementById(\'favtext'.$thread_id.'\').value, \'update\');}">Ок</button>
    			<a href="javascript:void(0);" onClick="xajax_EditFavBlog('.$thread_id.', '.$gr_num.', '.$editfav['priority'].', document.getElementById(\'currtitle\').value, \'update\');" class="lnk-dot-666">Отмена</a>
    		</span>
    		<input type="text" id="favtext'.$thread_id.'" value="'.$editfav['title'].'" class="i-txt">';
			$outHTML .= "<input id='favpriority".$thread_id."' type='hidden' value='".$editfav['priority']."'>";
			$outHTML .= "<input id='currtitle' type='hidden' value='".$editfav['title']."'>";
            
            $objResponse->script("$('fav$thread_id').addClass('li-edit');");
			
			$objResponse->assign("fav".$thread_id,"innerHTML",$outHTML);
		break;
	}

	return $objResponse;
}


function openlevel($thread, $mod, $begin, $end, $thispage, $blog_thread, $lastlink, $ord){
	
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/blogs.php");
	global $session;
	session_start();
	$uid = $_SESSION['uid'];
	$objResponse = new xajaxResponse();
	$blog= new blogs();
	$ret='';
	 $cur_user_msgs = array();
	list($gr_name, $gr_id, $gr_base) = $blog->GetThread($thread, $err, $mod, get_uid(false));
	$blog->GetThreeId($begin, $threearr ,0);
	$parent_login = $blog->login;
			while ($blog->GetNext()){
				$stopwrite=true;	
				foreach ($threearr as $temp) {
					if ($blog->id==$temp) { $stopwrite=false; break; }
				}
				if ($stopwrite) { continue; }
				$msg_num++;
				$allow_del = 0;
				if ($last_id == $blog->id) print("<a name=\"post\" id=\"post\"></a>");
				if ($blog->id == $edit_id && $blog->login == $_SESSION['login']) print("<a name=\"edit\" id=\"edit\"></a>");
				if ($blog->attach) $str = viewattachLeft($blog->login, $blog->attach, "upload", $file, 1000, 600, 307200, !$blog->small, (($blog->small==2)?1:0));
				$padding = ($blog->level > 19) ? 380 : ($blog->level*20);
				if (in_array($blog->reply, $cur_user_msgs)) $allow_del = 1;
				if ($blog->login == $_SESSION['login']) $cur_user_msgs[] = $blog->id;
				
				
				
				$ret.='<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr valign="top" ';
				
				$ret.='><td style="';
				if ($blog->level) { $ret.='padding-left: '.$padding.'px;'; } 
				$ret.='padding-right: 10px;">'.view_avatar($blog->login, $blog->photo).'
			</td>
			<td class="bl_text" width="100%">';
			if ($winner == $blog->id) { $ret.='<a name="winner" id="winner"></a> '; } 
			if ($blog->payed) { $ret.=view_pro(); } 
			$ret.=$session->view_online_status($blog->login);
			$ret.='<font class="'.$blog->cnt_role.'name11"><a href="/users/'.$blog->login.'" class="'.$blog->cnt_role.'name11" title="'.($blog->uname." ".$blog->usurname).'">'.($blog->uname." ".$blog->usurname).'</a> [<a href="/users/'.$blog->login.'" class="'.$blog->cnt_role.'name11" title="'.$blog->login.'">'.$blog->login.'</a>]</font>&nbsp;&nbsp;'.date("[d.m.Y | H:i]",strtotimeEx($blog->post_time));
			if ($blog->deleted) {
			     if (isset($blog->thread) && is_array($blog->thread) && (count($blog->thread) > 0))
			     {
  			     $buser_id = $blog->thread;
  			     $buser_id = array_pop($buser_id);
  			     $buser_id = $buser_id['fromuser_id'];
			     }
			     if ($blog->deluser_id == $blog->fromuser_id) { $ret.='<br><br>Комментарий удален автором '.date("[d.m.Y | H:i]",strtotimeEx($blog->deleted)); }
			     elseif ($blog->deluser_id == $buser_id) { $ret.='<br><br>Комментарий удален автором темы '.date("[d.m.Y | H:i]",strtotimeEx($blog->deleted));
			     } else { $ret.='<br><br>Комментарий удален модератором';  if (!$mod) { $ret.='( '; 
			     $del_user = $user->GetName($blog->deluser_id, $err); $ret.=($del_user['login'] . ' : ' . $del_user['usurname'] . ' ' . $del_user['uname']); $ret.=' ) '; } $ret.=date("[d.m.Y | H:i]",strtotimeEx($blog->deleted)); } $ret.='<br><br>';
			   } else {
			     if ($blog->modified) { $ret.='&nbsp; &nbsp;';
			       if ($blog->modified_id == $blog->fromuser_id) { $ret.='[внесены изменения: '.date("d.m.Y | H:i]",strtotimeEx($blog->modified)); } 
      			 else { $ret.='Отредактировано модератором'; 
      			 if (!$mod) { $ret.='( '; $mod_user = $user->GetName($blog->modified_id, $err); $ret.=($mod_user['login'] . ' : ' . $mod_user['usurname'] . ' ' . $mod_user['uname']); $ret.=' ) '; } $ret.=' '.date("[d.m.Y | H:i]",strtotimeEx($blog->modified)); } } 
  			$ret.='<br>';
  			if ($winner == $blog->id) { $ret.='<font color="#000099" style="font-size:20px">Победитель</font>'; } 
  			$ret.='<br>';
  			if ($blog->new == 't') { $ret.='<img src="/images/ico_new_blog.gif" alt="" width="44" height="12" border="0"><br>'; } 
  			if ($blog->title) { $ret.=' <font class="bl_name">';
  			if ($blog->login == "Anonymous"){
  				list($name, $mail) = sscanf($blog->title, "%s @@@: %s");
  				$ret.=$name." ".$mail;
  			} else $ret.=reformat($blog->title, 30); $ret.='</font><br>'; } 
  			$ret.=reformat($blog->msgtext, 50).'<br>';
  			if ($blog->attach){
  				if ($file) $ret.="<br>".$str."<br>";
  				 else $ret.="</td></tr><tr class=\"qpr\"><td colspan=\"2\"><br>".$str;
  			 } 
  				$ret.='<br>';
  			if ($gr_base == 5 && !$winner && $parent_login == $_SESSION['login']) { $ret.="<input type=\"submit\" name=\"btn\" value=\"Это победитель\" onClick=\"if (warning(0)) window.location.replace('./view.php?tr=".$thread."&ord='.$ord.'&winner=".$blog->id."'); else return false;\">"; } 
  			$ret.='<div style="color: #D75A29;font-size:9px;';
  			if ($blog->attach && !$file) { $ret.=' padding-left: '.($padding+60).'px;'; } $ret.='">';
  			if ($blog->login == $_SESSION['login'] || $parent_login == $_SESSION['login'] || $allow_del || !$mod) {
  			$ret.=' <a href="'.$form_uri.'?id='.$blog->id.'&amp;action=delete&ord='.$ord.'" style="color: #D75A29;" onclick="return warning(1);">Удалить</a> |';
  			} if ($blog->login == $_SESSION['login'] || (!$mod)) {
  			$ret.='<a href="'.$form_uri.'?id='.$blog->id.'&amp;action=edit&ord='.$ord.'&amp;tr='.$thread.'" style="color: #D75A29;">Редактировать</a> |';
  			 } 
  			$ret.="<a href=\"javascript: void(0);\" onclick=\"javascript: answer('".$blog->id."', '".($blog->attach ? $blog->attach : '')."', '".get_login($_SESSION["uid"])."'); document.getElementById('frm').olduser.value = '".$_SESSION["uid"]."'; \" ";
  			
  			$ret.='style="color: #D75A29">Комментировать</a> |
  			<a href="/blogs/view.php'."?tr=".$blog_thread.($thispage ? "&pagefrom=".$thispage : "")."&openlevel=".$blog->id."&ord=".$ord."#o".$blog->id.'" style="color: #D75A29">Ссылка</a> 
  			</div>
						</td>
		</tr>
		<tr'; 
  		if (!$blog->level || $lastlink==$blog->id) { $ret.=' class="qpr"'; } $ret.='><td colspan="2" ><br></td></tr>
		</table>
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr class="n_qpr"><td colspan="3" id="form'.$blog->id.'">';
		if ($blog->id == $edit_id && ($blog->login == $_SESSION['login'] || !$mod)) { $ret.="
			<script language=\"JavaScript\" type=\"text/javascript\">
			<!--
			answer(".$blog->id.", '".($blog->attach ? $blog->attach : '')."', '".get_login($_SESSION["uid"])."');
			document.getElementById('frm').olduser.value = '".$_SESSION["uid"]."';
			document.getElementById('frm').msg_name.value = '".($error_flag)?input_ref_scr($msg_name):input_ref_scr($blog->title)."';
			document.getElementById('frm').msg.value = '".($error_flag)?input_ref_scr($msg):input_ref_scr($blog->msgtext)."';
			document.getElementById('frm').btn.value = 'Сохранить';
			document.getElementById('frm').action.value = 'change';
			//-->
			</script>";
		} 
  		 } 
		$ret.="</td></tr>
		</table>";				
				
				}
	$objResponse->assign($begin,"innerHTML",$ret);
	return $objResponse;
}

function CorporativeTags($tag_id = 0) {
	global $DB;
	$count = $DB->val("SELECT COUNT(*) FROM (SELECT DISTINCT tag_id FROM corp_tags) as t");
    $tags = $DB->rows(
		"SELECT t.name, COUNT(*) as count, t.id 
        FROM corp_tags c 
        INNER JOIN tags t ON c.tag_id = t.id 
        INNER JOIN corporative_blog b ON b.id = c.corp_id AND b.date_deleted IS NULL
        GROUP BY t.name, t.id ORDER BY count DESC, name "
	); 
	
	ob_start();
	include_once($_SERVER['DOCUMENT_ROOT']."/engine/templates/about/corporative_tags.tpl");
	$data = ob_get_contents();
	ob_get_clean();
	
	$objResponse = new xajaxResponse();

	$objResponse->assign("tags_content", "innerHTML", $data);
	
	return $objResponse;	
}

function searchCorporativeTag($tag, $id) {
	global $DB;
	$tag  = strtolower(change_q($tag));
	$tags = $DB->rows("SELECT name FROM tags WHERE lower(name) LIKE '{$tags}%' ORDER BY name LIMIT 10");
    
    if($tags) {
    	 $html = "<span id='search_content' class='search-tag-list'><ul>";
    
	    foreach($tags as $k=>$val) {
	    	$html .= "<li><a href='javascript:void(0)' onClick='\$j(\\\"#input_$id\\\").val(\\\"{$val['name']}\\\");\$j(\\\"#search_content\\\").remove();'>{$val['name']}</a></li>";
	    }
	    
	    $html .= "</ul></span>";
	    
	    $objResponse = new xajaxResponse();
	    
	    /*$over = '$j("#search_content").mouseleave(function() {';
		$over .= '$j("body").bind("click", function(){';
		$over .= '$j("#search_content").remove()';
		$over .= '})'; 
		$over .= '})';
		$over .= '$j("#search_content").mouseenter(function(){';
		$over .= '$j("body").unbind("click");';
		$over .= '})';
		
	    
		$objResponse->script('$j("#search_content").remove(); $j("#tag_'.$id.'").append("'.$html.'")');
		$objResponse->script($over);//'$j("#search_content").mouseout(function(){ $j("body").bind("click", function(){alert("1")}) })');*/
		return $objResponse;
	} 
	
	$objResponse = new xajaxResponse();
	$objResponse->script("$$('#search_content').destroy();");
	return $objResponse;
}


/**
 * 
 * HTML для отображения результата, когда проект еще открыт
 * 
 * @param $objResponse
 * @param $poll          -  массив с вариантами ответов
 * @param $voted         -  пользователь голосовал в этом опросе?
 */	
function BlogsPoll_ShowResult($thread_id, &$objResponse, &$poll, $voted) {
	$html = '';
	
	for ($i=0; $i<count($poll); $i++) {
		$html .= "
			<tr>
				<td class='bp-gres'>{$poll[$i]['votes']}</td>
				<td><label for='poll-${thread_id}_${i}'>".reformat($poll[$i]['answer'], 40, 0, 1)."</label></td>
			</tr>
		";
	}
	
	$objResponse->assign('poll-answers-'.$thread_id, 'innerHTML', "<table class='poll-variants'>$html</table>");
	$objResponse->assign('poll-btn-vote-'.$thread_id, 'innerHTML', '');
	$html = $voted? '': '<a class="b-buttons__link b-buttons__link_dot_0f71c8" href="javascript: return false;" onclick="poll.showPoll(\'Blogs\', '.$thread_id.'); return false;">Скрыть результаты</a>&nbsp;&nbsp;&nbsp;';
	$objResponse->assign('poll-btn-result-'.$thread_id, 'innerHTML', $html);
	$objResponse->assign('poll-btn-close-'.$thread_id, 'innerHTML', '<a class="b-buttons__link b-buttons__link_dot_0f71c8" href="javascript: return false" onclick="poll.close(\'Blogs\', '.$thread_id.'); return false;" >Закрыть опрос</a>&nbsp;&nbsp;&nbsp;');
}

/**
 * 
 * HTML для отображения голосования
 * 
 * @param $objResponse
 * @param $poll          -  массив с вариантами ответов
 * @param $radio - 1 - один вариант ответа, 0 - несколько вариантов ответа
 */	
function BlogsPoll_ShowPoll($thread_id, &$objResponse, &$poll, $radio = 1) {
    $sType = ( $radio ) ? 'radio' : 'checkbox';
    $sName = ( $radio ) ? '' : '[]';
    
	for ($i=0; $i<count($poll); $i++) {
		if( $sType == 'radio'){
		$html .= "
			<div class=\"b-radio__item b-radio__item_padbot_10\">
				<table class='b-layout__table b-layout__table_width_full' cellpadding='0' cellspacing='0' border='0'>
					<tr class='b-layout__tr'>
						<td class='b-layout__left b-layout__left_width_15'><input id='poll-${thread_id}_${i}' class='b-radio__input b-radio__input_top_-3' type='$sType' name='poll_vote$sName' value='{$poll[$i]['id']}' /></td>
						<td class='b-layout__right'><label class='b-radio__label b-radio__label_fontsize_13' for='poll-${thread_id}_${i}'>".reformat($poll[$i]['answer'], 40, 0, 1)."</label></td>
					</tr>
				</table>
			</div>";
		}
		elseif( $sType == 'checkbox'){
		$html .= "
			<div class=\"b-check b-check_padbot_5\">
				<input id='poll-${thread_id}_${i}' class='b-check__input' type='$sType' name='poll_vote$sName' value='{$poll[$i]['id']}' />
				<label class='b-check__label b-check__label_fontsize_13' for='poll-${thread_id}_${i}'>".reformat($poll[$i]['answer'], 40, 0, 1)."</label>
			</div>";
		}
	}

		if( $sType == 'radio'){
			$objResponse->assign('poll-answers-'.$thread_id, 'innerHTML', "<div class=\"b-radio b-radio_layout_vertical\">$html</div>");
		}
		elseif( $sType == 'checkbox'){
			$objResponse->assign('poll-answers-'.$thread_id, 'innerHTML', "$html");
		}
	$objResponse->assign('poll-btn-vote-'.$thread_id, 'innerHTML', '<a class="b-button b-button_rectangle_color_transparent" href="javascript: return false;" onclick="poll.vote(\'Blogs\', '.$thread_id.'); return false;"><span class="b-button__b1"><span class="b-button__b2"><span class="b-button__txt">Ответить</span></span></span></a>&nbsp;&nbsp;&nbsp;');
	$objResponse->assign('poll-btn-result-'.$thread_id, 'innerHTML', '<a class="b-buttons__link b-buttons__link_dot_0f71c8" href="javascript: return false;" onclick="poll.showResult(\'Blogs\', '.$thread_id.'); return false;" >Посмотреть результаты</a>&nbsp;&nbsp;&nbsp;');
	$objResponse->assign('poll-btn-close-'.$thread_id, 'innerHTML', '<a class="b-buttons__link b-buttons__link_dot_0f71c8" href="javascript: return false" onclick="poll.close(\'Blogs\', '.$thread_id.'); return false;">Закрыть опрос</a>&nbsp;&nbsp;&nbsp;');
}

/**
 * 
 * HTML для отображения результата, когда проект уже закрыт
 * 
 * @param $objResponse
 * @param $poll          -  массив с вариантами ответов
 */	
function BlogsPoll_ShowClosed($thread_id, &$objResponse, &$poll) {
	$max = 0;
	for ($i=0; $i<count($poll); $i++) $max = max($max, $poll[$i]['votes']);
	for ($i=0; $i<count($poll); $i++) {
		$html .= "
			<tr>
				<td class='bp-vr'><label for='poll-${thread_id}_${i}'>".reformat($poll[$i]['answer'], 40, 0, 1)."</label></td>
				<td class='bp-res'>{$poll[$i]['votes']}</td>
				<td><div class='res-line rl1' style='width: " . ($max? round(((100 * $poll[$i]['votes']) / $max) * 3): 0) . "px;'></div></td>
			</tr>
		";
	}
	$objResponse->assign('poll-answers-'.$thread_id, 'innerHTML', "<table class='poll-variants'>$html</table>");
	$objResponse->assign('poll-btn-vote-'.$thread_id, 'innerHTML', '');
	$objResponse->assign('poll-btn-result-'.$thread_id, 'innerHTML', '');
	$objResponse->assign('poll-btn-close-'.$thread_id, 'innerHTML', '<a class="b-buttons__link b-buttons__link_dot_0f71c8" href="javascript: return false" onclick="poll.close(\'Blogs\', '.$thread_id.'); return false;">Открыть опрос</a>&nbsp;&nbsp;&nbsp;');
}

/**
 * 
 * Проголосовать или показать результат
 * 
 * @param integer $thread_id  id треда
 * @param array   $answers    id ответов (если 0, то просто отобразить результат)
 */	
function BlogsPoll_Vote($thread_id, $answers, $sess) {
	require_once $_SERVER['DOCUMENT_ROOT']."/classes/blogs.php";
	require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
	session_start();
	$uid = intval($_SESSION['uid']);
	$user = new users();
	$thread_id = intval($thread_id);
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
	$ban_where = $user->GetField($uid, $error, "ban_where");
	if ($ban_where == 1) {
		$objResponse->alert('Вам закрыт доступ в блоги');
		return $objResponse;
	}
	$blog = new blogs;
	if (!empty($answers)) {
		if ($sess && $sess == $_SESSION['rand']) {
			$res = $blog->Poll_Vote($uid, $answers, $error);
		}
		if (!$res) {
			if (!$error) {
				$error = 'Ошибка <> '. $sess .' <> '. $_SESSION['rand'];
			}
			$objResponse->alert($error);
		}
	}
	$poll = $blog->Poll_Answers($thread_id);
	$voted = $blog->Poll_Voted($uid, $thread_id);
	BlogsPoll_ShowResult($thread_id, $objResponse, $poll, $voted);
	return $objResponse;
}

/**
 * 
 * Отобразить голосование
 * 
 * @param integer $thread_id id треда
 * @param $radio - 1 - один вариант ответа, 0 - несколько вариантов ответа
 */	
function BlogsPoll_Show($thread_id, $radio = 1) {
	require_once $_SERVER['DOCUMENT_ROOT']."/classes/blogs.php";
	require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
	session_start();
	$uid = intval($_SESSION['uid']);
	$user = new users();
	$ban_where = $user->GetField($uid, $error, "ban_where");
	if ($ban_where == 1) {
		$objResponse->alert('Вам закрыт доступ в блоги');
		return $objResponse;
	}
	$thread_id = intval($thread_id);
	$objResponse = new xajaxResponse();
	$blog = new blogs;
	$poll = $blog->Poll_Answers($thread_id);
	if ($blog->Poll_Voted($uid, $thread_id)) {
		BlogsPoll_ShowResult($thread_id, $objResponse, $poll, 1);
	} else {
		BlogsPoll_ShowPoll($thread_id, $objResponse, $poll, $radio);
	}
	return $objResponse;
}

/**
 * 
 * Закрыть/Открыть голосование
 * 
 * @param integer $thread_id   id треда
 */	
function BlogsPoll_Close($thread_id) {
	global $DB;
	require_once $_SERVER['DOCUMENT_ROOT']."/classes/blogs.php";
	require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
	session_start();
	$uid = intval($_SESSION['uid']);
	$user = new users();
	$ban_where = $user->GetField($uid, $error, "ban_where");
	if ($ban_where == 1) {
		$objResponse->alert('Вам закрыт доступ в блоги');
		return $objResponse;
	}
	$thread_id = intval($thread_id);
	$objResponse = new xajaxResponse();
	$msg = $DB->row("
		SELECT bm.fromuser_id, bp.question, bp.multiple, bb.thread_id AS blocked
		FROM blogs_msgs AS bm 
		LEFT JOIN blogs_poll AS bp ON bp.thread_id = bm.thread_id
		LEFT JOIN blogs_blocked AS bb ON bb.thread_id = bm.thread_id 
		WHERE bm.thread_id = ? AND bm.reply_to IS NULL
	", $thread_id);
	if ($msg['question'] && ((!$msg['blocked'] && $uid == $msg['fromuser_id']) || hasPermissions('blogs'))) {
		$blog = new blogs;
		$poll = $blog->Poll_Answers($thread_id);
		if ($blog->Poll_Close($thread_id)) {
			BlogsPoll_ShowClosed($thread_id, $objResponse, $poll);
		} else if ($blog->Poll_Voted($uid, $thread_id)) {
			BlogsPoll_ShowResult($thread_id, $objResponse, $poll, 1);
		} else {
		    $radio = ( $msg['multiple'] == 't' ) ? 0 : 1;
			BlogsPoll_ShowPoll($thread_id, $objResponse, $poll, $radio );
		}
	}
	return $objResponse;
}

/**
 * 
 * Удалить голосование
 * 
 * @param integer $thread_id   id треда
 */	
function BlogsPoll_Remove($thread_id) {
	global $DB;
	require_once $_SERVER['DOCUMENT_ROOT']."/classes/blogs.php";
	session_start();
	$uid = intval($_SESSION['uid']);
	$thread_id = intval($thread_id);
	$objResponse = new xajaxResponse();
	$msg = $DB->row("
		SELECT bm.fromuser_id, bp.question, bb.thread_id AS blocked
		FROM blogs_msgs AS bm 
		LEFT JOIN blogs_poll AS bp ON bp.thread_id = bm.thread_id
		LEFT JOIN blogs_blocked AS bb ON bb.thread_id = bm.thread_id 
		WHERE bm.thread_id = $thread_id AND bm.reply_to IS NULL
	");
	if ($msg['question'] && ((!$msg['blocked'] && $uid == $msg['fromuser_id']) || hasPermissions('blogs'))) {
		$blog = new blogs;
		$blog->Poll_Remove($thread_id, $msgtext);
		$objResponse->assign("poll-$thread_id", 'innerHTML', ($msgtext? "$msgtext<br><br>": ""));
	}
	return $objResponse;
}

function SetBlogSubscribe($thread_id) {
    require_once $_SERVER['DOCUMENT_ROOT']."/classes/blogs.php";
	session_start();
	$uid = $_SESSION['uid'];  
    $thread_id = intval($thread_id);
	$objResponse = new xajaxResponse();
	
	$blog = new blogs;
	
	if($uid && $thread_id) {
	   $blog->SetMail($thread_id, $uid, 't');  
	   $objResponse->assign("blog_subscribe", "innerHTML", "Отписаться от темы");
	   $objResponse->script("$('blog_subscribe').onclick = function(){ xajax_DelBlogSubscribe({$thread_id}); }");
	}
	
	return $objResponse;
}

function DelBlogSubscribe($thread_id) {
    require_once $_SERVER['DOCUMENT_ROOT']."/classes/blogs.php";
	session_start();
	$uid = $_SESSION['uid'];  
    $thread_id = intval($thread_id);
	$objResponse = new xajaxResponse();
	
	$blog = new blogs;
	
	if($uid && $thread_id) {
	   $blog->SetMail($thread_id, $uid, 'f');  
	   $objResponse->assign("blog_subscribe", "innerHTML", "Подписаться на тему");
	   $objResponse->script("$('blog_subscribe').onclick = function(){ xajax_SetBlogSubscribe({$thread_id}); }");
	}
	
	return $objResponse;
}

$xajax->processRequest();
?>
