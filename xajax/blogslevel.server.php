<?
$rpath = "../";
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/blogslevel.common.php");

function openlevel($thread, $mod, $begin, $end, $thispage, $blog_thread, $lastlink){
	
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
			<td class="bl_text" style="width: '.(670 - (($blog->level > 19) ? 380 : ($blog->level*20))).'px;">';
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
  			if ($gr_base == 5 && !$winner && $parent_login == $_SESSION['login']) { $ret.="<input type=\"submit\" name=\"btn\" value=\"Это победитель\" onClick=\"if (warning(0)) window.location.replace('./view.php?tr=".$thread."&winner=".$blog->id."'); else return false;\">"; } 
  			$ret.='<div style="color: #D75A29;font-size:9px;';
  			if ($blog->attach && !$file) { $ret.=' padding-left: '.($padding+60).'px;'; } $ret.='">';
  			if ($blog->login == $_SESSION['login'] || $parent_login == $_SESSION['login'] || $allow_del || !$mod) {
  			$ret.=' <a href="'.$form_uri.'?id='.$blog->id.'&amp;action=delete" style="color: #D75A29;" onclick="return warning(1);">Удалить</a> |';
  			} if ($blog->login == $_SESSION['login'] || (!$mod)) {
  			$ret.='<a href="'.$form_uri.'?id='.$blog->id.'&amp;action=edit&amp;tr='.$thread.'" style="color: #D75A29;">Редактировать</a> |';
  			 } 
  			$ret.="<a href=\"javascript: void(0);\" onclick=\"javascript: answer('".$blog->id."', '".($blog->attach ? $blog->attach : '')."', '".get_login($_SESSION["uid"])."'); document.getElementById('frm').olduser.value = '".$_SESSION["uid"]."'; \" ";
  			
  			$ret.='style="color: #D75A29">Комментировать</a> |
  			<a href="/blogs/view.php'."?tr=".$blog_thread.($thispage ? "&pagefrom=".$thispage : "")."&openlevel=".$blog->id."#o".$blog->id.'" style="color: #D75A29">Ссылка</a> 
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

$xajax->processRequest();
?>