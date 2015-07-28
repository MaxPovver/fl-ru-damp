<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/links.php");
$GLOBALS[LINK_INSTANCE_NAME] = new links('blogs');


$is_yt_link = ($edit_msg['yt_link'] || $yt_link);
$uid = get_uid();

$answers = $blog->Poll_GetPostAnswers($edit_msg);

/*require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );
$stop_words = new stop_words( hasPermissions('blogs') );*/
?>
<script>
var IE = '\v'=='v';
if(IE) {
    window.addEvent('domready', function() {
       var winScroller = new Fx.Scroll(window);
        if($('scroll' + window.location.hash.replace("#", ""))) {
            winScroller.toElement('scroll' + window.location.hash.replace("#", ""));
        }
    });
}
</script>
<script type="text/javascript">
<!--
var yt_link = <? if ($is_yt_link) { ?>true<? } else { ?>false<? } ?>;
var blogs_max_desc_chars = <?=blogs::MAX_DESC_CHARS?>;
//-->
</script>
<?if ($uid){?>
<?if ($_COOKIE['blogs_favs_order'] != ""){?>
<script type="text/javascript">
<!--
<?
if ($_COOKIE['blogs_favs_order'] == "priority") $currentOrderStr = 1;
elseif ($_COOKIE['blogs_favs_order'] == "abc") $currentOrderStr = 2;
else $currentOrderStr = 0;
?>
order_now = "<?=$_COOKIE['blogs_favs_order'];?>";
currentOrderStr = "<?=$currentOrderStr;?>";

//-->
</script>
<?}?>
<?}?>
<?
if ($err || !$gr_name) {
die;
	include ("../404_inner.php");
} else {

///////////////////////////////////////////////////////////////////////
////////////////////////stat_collector/////////////////////////////////
///////////////////////////////////////////////////////////////////////
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stat_collector.php");
///////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////

	$favs = $blog->GetFavorites( get_uid(), $_GET["order"] );
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/blogs.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/blogs.common.php");
	$xajax->printJavascript('/xajax/');


    // такая же есть в view_cnt.php, user/journal_inner.php и xajax/banned.server.php
    function BlockedThreadHTML($reason, $date, $moder_login='', $moder_name='') {
        return "
            <div class='br-moderation-options'>
                <a href='/help/?all' class='lnk-feedback' style='color: #fff;'>Служба поддержки</a>
                <div class='br-mo-status'><strong>Топик заблокирован.</strong> Причина: ".str_replace("\n", "<br />", $reason)."</div>
                <p class='br-mo-info'>".
                ($moder_login? "Заблокировал: <a href='/users/$moder_login' style='color: #FF6B3D'>$moder_name [$moder_login]</a><br />": '').
                "Дата блокировки: ".dateFormat('d.m.Y H:i', $date)."</p>
            </div>
        ";
    }
    

?>

<table border="0" width="100%" cellpadding="0" cellspacing="0">
  <tr valign="middle">
    <td align="left"><?php
            $crumbs = array();
            if(!$gr) {
                $crumbs[] = array("title"=>"Блоги", "url"=>"");
            } else {
                $crumbs[] = array("title"=>"Блоги", "url"=>"/blogs/");
                $crumbs[] = array("title"=>$gr_name, "url"=>"");
            }
            ?>
      <div class="b-menu b-menu_crumbs ">
        <?=getCrumbs($crumbs)?>
      </div></td>
    <td align="right"><? seo_start();?>
      <a class="b-button b-button_round_green b-button_float_right" onclick="return resetBlogForm()" href="<?=($_SESSION['login']? '#bottom': '/fbd.php')?>"><span class="b-button__b1"><span class="b-button__b2"><span class="b-button__txt">Написать сообщение</span></span></span></a>
      <?= seo_end();?></td>
  </tr>
</table>
<div class="b-menu b-menu_tabs">
  <ul class="b-menu__list b-menu__list_overflow_hidden b-menu__list_padleft_28ps">
    <li class="b-menu__item <?php  ($ord != "best" && $ord != "my" && $ord != "relevant" && $ord != "favs") ? print 'b-menu__item_active': print '';?>"> <a class="b-menu__link" href="<?php print $href. ($sHref ? '&' : '?').'ord=new' ?>" title="Новые"> <span class="b-menu__b1">Новые</span> </a> </li>
    <? seo_start();?>
    <li class="b-menu__item <?php  ($ord == "best") ? print 'b-menu__item_active': print '';?>"> <a class="b-menu__link" href="<?php print  $href.($sHref ? '&' : '?').'ord=best' ?>" title="Популярные"> <span class="b-menu__b1">Популярные</span> </a> </li>
    <li class="b-menu__item <?php  ($ord == "relevant") ? print 'b-menu__item_active': print '';?>"> <a class="b-menu__link" href="<?php print  $href.($sHref ? '&' : '?').'ord=relevant' ?>" title="Актуальные"> <span class="b-menu__b1">Актуальные</span> </a> </li>
    <? if (get_uid(0)) { ?>
    <li class="b-menu__item <?php  ($ord == "my") ? print 'b-menu__item_active': print '';?>"> <a class="b-menu__link" href="<?php print  $href.($sHref ? '&' : '?').'ord=my' ?>" title="Мои"> <span class="b-menu__b1">Мои</span> </a> </li>
    <li class="b-menu__item b-menu__item_last <?php  ($ord == "favs") ? print 'b-menu__item_active': print '';?>"> <a class="b-menu__link" href="<?php print  $href.($sHref ? '&' : '?').'ord=favs' ?>" title="Закладки"> <span class="b-menu__b1">Закладки</span> </a> </li>
    <? } ?>
    <?= seo_end();?>
  </ul>
</div>
<div id="content">
  <a name="tm-last"></a>
  <div id="rightcl" class="b-layout__right b-layout__right_relative b-layout__right_width_72ps b-layout__right_float_right">
    <?php include_once(ABS_PATH.'/blogs/valentin.php');?>
    <?php if ( $ord == 'my' ): ?>
    <?php $href   = getFriendlyURL("blog_group", $gr).'?'.($base ? "&t=prof" : "").'&ord=my';?>
    <ul class="tabs-sort-nav">
      <?php if ( $sub_ord == 'my_all' ): ?>
      <li class="active">Все</li>
      <?php else: ?>
      <li><a href="<?=$href?>&sub_ord=my_all" class="lnk-dot-666">Все</a></li>
      <?php endif; ?>
      <?php if ( $sub_ord == 'my_posts' ): ?>
      <li class="active">Посты</li>
      <?php else: ?>
      <li><a href="<?=$href?>&sub_ord=my_posts" class="lnk-dot-666">Посты</a></li>
      <?php endif; ?>
      <?php if ( $sub_ord == 'my_comments' ): ?>
      <li class="active">Комментарии</li>
      <?php else: ?>
      <li><a href="<?=$href?>&sub_ord=my_comments" class="lnk-dot-666">Комментарии</a></li>
      <?php endif; ?>
    </ul>
    <?php elseif ( $ord == 'favs' ): ?>
    <?php /*$href = getFriendlyURL("blog_group", $gr).'?'.($base ? "&t=prof" : "").'&ord=favs';*/ ?>
        <div class="b-layout__txt b-layout__txt_float_right b-layout__txt_width_225">
        	<span class="b-layout__txt b-layout__txt_inline-block b-layout__txt_padtop_2">Упорядочить:</span>
         <div class="b-select b-select_width_140 b-select_float_right">
      <select id="sel_blogs_favs_sort" class="b-select__select" name="sel_blogs_favs_sort" onchange="window.location='/blogs/viewgroup.php/?ord=<?=$ord?>&order='+this.value+'&grname=<?=strtolower(translit($gr_name)) ?>';">
        <option value="date" <?php echo (! $order || $order == 'date') ? ' selected="selected"' : ''?>>по дате добавления</option>
        <option value="priority" <?php echo ($order == 'priority') ? ' selected="selected"' : ''?>>по важности</option>
        <option value="abc" <?php echo ($order == 'abc') ? ' selected="selected"' : ''?>>по алфавиту</option>
      </select>
      </div>
    </div>
    <?php
            	$order = substr($_GET["order"], 0, strlen("priority"));            	
            	//$href = "/blogs/viewgroup.php/?ord=$ord&order=$order";
            	$href = getFriendlyURL('blog_group', $gr) . "?ord=$ord&order=$order";
            ?>
    <ul class="tabs-sort-nav">
      <?php if ( $sub_ord == 'favs_std' ): ?>
      <li class="active">Стандартный</li>
      <?php else: ?>
      <li><a href="<?=$href?>&sub_ord=favs_std" class="lnk-dot-666">Стандартный</a></li>
      <?php endif; ?>
      <?php if ( $sub_ord == 'favs_list' ): ?>
      <li class="active">Списком</li>
      <?php else: ?>
      <li><a href="<?=$href?>&sub_ord=favs_list" class="lnk-dot-666">Списком</a></li>
      <?php endif; ?>
    </ul>
    <?php endif; ?>
    <? if ($error) print("<div>".view_error($error)."</div>")?>
    <? if ($themes) { $theme = current($themes); $i = 0;
 /**/
        do {  $i++; 
            if ( $theme['reply_to'] ) { // comment
        		    $allow_del = ( $theme['login'] == $_SESSION['login'] ) ? 1 : 0;
        		    $cnt_role  = (substr($theme['role'], 0, 1)  == '0')? "frl" : "emp";
        		    
        		    if ($theme['attach'] && is_array($theme['attach'])) {
                        $attach_html = '';
                        foreach ($theme['attach'] as $i=>$attach) {
                            $i++;
                            $att_ext = CFile::getext($attach['fname']);
                            if($i != count($theme['attach'])) $br = "<br /><br />";
                            else $br = "";
                            /*if ($att_ext == "swf") {
                                $attach_html .= viewattachExternal($theme['login'], $attach['fname'], "upload", "/blogs/view_attach.php?user=".$theme['login']."&attach=".$attach['fname']).$br;
                            } elseif($att_ext == 'flv') {
                                $attach_html .= viewattachLeft($theme['login'], $attach['fname'], "upload", $file, 1000, 470, 307200, false).$br;
                            } else {
                                $attach_html .= '<div class="flw_offer_attach">'.viewattachLeft($theme['login'], $attach['fname'], "upload", $file, 1000, 600, 307200, false, (($attach['small']==2)?1:0)).'</div>';
                            }*/
                            $attach_html .= '<div class="flw_offer_attach">'.viewattachLeft($theme['login'], $attach['fname'], "upload", $file, 1000, 600, 307200, false, (($attach['small']==2)?1:0)).'</div>';
                        }
                    }
        		?>
    <div class="blog " id="tm<?=$theme['id']?>" >
      <div id="scrollb<?=$theme['thread_id']?>"></div>
      <a name="tm<?=$theme['id']?>"></a> <a name="b<?= $theme['thread_id']?>"></a>
      <div class="upic">
        <?=view_avatar_info($theme['login'], $theme['photo'], 1)?>
      </div>
      <div class="blogcnt">
        <div class="<?=$cnt_role?>login b-layout__txt b-layout__txt_fontsize_11">
          <? /*!!!is_team!!!*/
                $pro = ($theme['payed'] == 't'?(is_emp($theme['role'])?view_pro_emp():view_pro2(($theme['payed_test'] == 't')?true:false)):""); 
                $is_team = view_team_fl();
                ?>
          <?php /*if ($theme['payed'] == 't') { ?><?=(is_emp($theme['role'])?view_pro_emp():view_pro2(($theme['payed_test'] == 't')?true:false))?><? } */?>
          <span class="<?=$cnt_role?>login b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_bold">
          <a href="/users/<?=$theme['login']?>/?f=<?=stat_collector::REFID_BLOGS?>&stamp=<?=$_SESSION['stamp']?>" title="<?=($theme['uname']." ".$theme['usurname'])?>">
          <?=user_in_color($theme['uname']." ".$theme['usurname'],$theme['role'],$theme['payed'])?>
          </a>
          <?=user_in_color('[',$theme['role'],$theme['payed'])?>
          <a href="/users/<?=$theme['login']?>/?f=<?=stat_collector::REFID_BLOGS?>&stamp=<?=$_SESSION['stamp']?>" title="<?=$theme['login']?>">
          <?=user_in_color($theme['login'],$theme['role'],$theme['payed'])?>
          </a>
          </span>
          <?=user_in_color(']',$theme['role'],$theme['payed'])?>
          <?=$theme['is_team']=='t'?$is_team:$pro?><?= is_verify($theme['login']) ? view_verify() : ''?> <?= ( $theme['completed_cnt'] > 0 ? view_sbr_shield() : '' );?>
          <span class=" b-layout__txt b-layout__txt_fontsize_11">
          <?=date("[d.m.Y | H:i]",strtotimeEx($theme['post_time']))?>
          <? 
                    if ($theme['deleted']) { 
                        if ($theme['deluser_id'] == $theme['fromuser_id']) { 
                            if (!hasPermissions('blogs')) echo "<br /><br />"; 
                        ?>
          Комментарий удален автором
          <?=date("[d.m.Y | H:i]",strtotimeEx($theme['deleted'])); 
                        }
                        else { 
                            if (!hasPermissions('blogs')) echo "<br /><br />"; ?>
          Комментарий удален модератором
          <? 
                            if (!$mod) { ?>
          (
          <? $del_user = $user->GetName($theme['deluser_id'], $err); print($del_user['login'] . ' : ' . $del_user['usurname'] . ' ' . $del_user['uname']); ?>
          )
          <? } ?>
          <?=date("[d.m.Y | H:i]",strtotimeEx($theme['deleted']));?>
          <?
                        }
                    }
                    
                    if (!$theme['deleted'] || hasPermissions('blogs')) {
                        if ($theme['modified']) { ?>
          &nbsp; &nbsp;
          <?
                            if (!$theme['modified_id'] || $theme['modified_id'] == $theme['fromuser_id']) { ?>
          [внесены изменения:
          <?=date("d.m.Y | H:i]",strtotimeEx($theme['modified'])); }
                            else {?>
          Отредактировано модератором
          <? if (!$mod) { ?>
          (
          <? $mod_user = $user->GetName($theme['modified_id'], $err); print($mod_user['login'] . ' : ' . $mod_user['usurname'] . ' ' . $mod_user['uname']); ?>
          )
          <? } ?>
          <?=date("[d.m.Y | H:i]",strtotimeEx($theme['modified']));?>
          <?
                            }
                        }
                        
                        if ($theme['is_banned']  && hasPermissions('blogs')) {
                            ?>
          <font color="#000000"><b>Пользователь забанен.</b></font>
          <?
                        }
                    }
                    ?>
          <br />
          </span> </div>
        <? 
            
            if (!$theme['deleted'] || hasPermissions('blogs')) {
                if ($theme['deleted'] && hasPermissions('blogs')) { ?>
        <font color="#CCCCCC">
        <? }
                
                if ($theme['is_banned']  && !hasPermissions('blogs')) {
                    ?>
        Ответ от заблокированного пользователя
        <?
                }
                else {
                    if ($theme['title']!=='') {
                        ?>
        <h4 class="cl-post-title"><span><a href="<?=getFriendlyURL("blog", $theme['thread_id'])?><?="?openlevel=".$theme['id'].$ord_get_part."#o".$theme['id']?>">
          <?
                        if ($theme['login'] == "Anonymous") {
                        list($name, $mail) = sscanf($theme['title'], "%s @@@: %s");
                        print $name." ".$mail;
                        } 
                        else {
                            $sTitle = /*($theme['moderator_status'] === '0' && $theme['payed'] != 't') ? $stop_words->replace($theme['title']) :*/ $theme['title'];
                            print reformat($sTitle,37,0,1); } ?>
          </a></span></h4>
        <?
                    }
                    ?>
        <?php $sMessage = /*($theme['moderator_status'] === '0' && $theme['payed'] != 't') ? $stop_words->replace($theme['msgtext']) :*/ $theme['msgtext']; ?>
        <div class="blog-one-cnt" id="message<?=$theme['id']?>" <?=($theme['deleted'] && hasPermissions('blogs'))?"style='color:#CCCCCC'":""?>>
          <?=reformat($sMessage, 45, 0, -($theme['is_chuck']=='t'), 1)?>
        </div>
        <br />
        <? if ($theme['attach']) {
                        print("<br />".$attach_html."<br />");
                    }
                    
                    if ($theme['yt_link']) {
                        print('<br clear="all" /><center>' . show_video($theme['id'],$theme['yt_link']) . '</center><br />');
                    }
                    ?>
        <div id="warnreason-<?=$theme['id']?>" style="display: none">&nbsp;</div>
        <? if ($theme['deleted'] && hasPermissions('blogs')) { ?>
        </font>
        <? } ?>
        <br />
        <table width="100%" border="0" cellpadding="0" cellspacing="0">
          <tr valign="middle">
            <td align="left" nowrap width="100%"  style="color: #D75A29; font-size:9px;"><?
                            //  || $parent_login == $_SESSION['login']
                            if ((!$theme['deleted'] && !$theme['is_banned']) && !$theme['is_blocked']) {	//|| hasPermissions('blogs')
                                $del_allow = $gr_base == 101 ? ($theme['login'] == $_SESSION['login'] || hasPermissions('blogs')) : $allow_del;
                                if (($theme['login'] == $_SESSION['login'] || $del_allow || !$mod) && (!$post_only || !$mod)) { ?>
              <a href="<?=htmlspecialchars($_SERVER['PHP_SELF']."?".$clearQueryStrOpen)?>&amp;id=<?=$theme['id']?>&amp;action=delete&ord=<?=htmlspecialchars($_GET["ord"])?>&u_token_key=<?=$_SESSION['rand']?>&r=1" style="color: #D75A29;" onclick="return warning(1);">Удалить</a> |
              <? } if (($theme['login'] == $_SESSION['login'] || !$mod || hasPermissions('blogs')) && (!$post_only || !$mod)) {?>
              <a href="<?=getFriendlyURL('blog_group', $gr)?>?action=edit&amp;tr=<?=$theme['id']?><?=($page>1?'&amp;page='.$page:'')?>&amp;t=<?=$t?>&ord=<?=$ord?>#edit" style="color: #D75A29;">Редактировать</a> | 
              <!--
                                <a href="<?=($gr_base == 101 ? $form_uri.'&' : '?')?>gr=<?=$gr?>&tr=<?=$theme['id']?>&amp;action=edit&page=<?=htmlspecialchars($_GET["page"])?>&ord=<?=htmlspecialchars($_GET["ord"])?>" style="color: #D75A29;">Редактировать</a> |
                                -->
              
              <? } ?>
              <? if ($form_uri != "/payed/") { ?>
              <?/*if (!$theme['closed_comments'] && !$theme['is_blocked']){ ?>
                                <a <?if ($_SESSION["login"]){?> href="javascript: void(0);" onclick="javascript:answer(<?=$theme['id']?>,'', '<?=get_login($uid)?>'); document.getElementById('frm').olduser.value = '<?=$uid?>'; document.getElementById('frm').scrollIntoView(true);" <?}else{?>href="/fbd.php"<?}?> style="color: #D75A29">Комментировать</a> |
                                <? }*/ ?>
              <? } ?>
              <a href="<?=getFriendlyURL("blog", $theme['thread_id'])."?openlevel=".$theme['id'].$ord_get_part."#o".$theme['id']?>" style="color: #D75A29">Ссылка</a>
              <?
                            }
                            else if ($theme['deleted']) {	 ?>
              <a href="<?=htmlspecialchars($_SERVER['PHP_SELF'])."?".htmlspecialchars($clearQueryStrOpen)?>&amp;id=<?=$theme['id']?>&amp;action=restore&ord=<?=htmlspecialchars($_GET["ord"])?>&r=1" style="color: #D75A29;" onclick="return warning(1);">Вернуть</a>
              <? } ?></td>
            <td nowrap><?if (hasPermissions('blogs') && $theme['login']!=$_SESSION["login"] && $theme['login']['login']!="admin") {
                            ?>
              <script type="text/javascript">
                            banned.addContext( 'blog_msg_<?=$theme['id']?>', 2, '<?="{$GLOBALS['host']}".getFriendlyURL("blog", $theme['thread_id'])."?openlevel=".$theme['id'].$ord_get_part."#o".$theme['id']?>', "<?=($theme['title']!==''? $theme['title'] : '<без темы>')?>" );
                            </script>
              <?php
                            if ( $theme['warn']<3 && !$theme['is_banned'] && !$theme['ban_where'] ) {
                            ?>
              <span class="warnlink-<?=$theme['fromuser_id']?>"><a style="color: #D75A29; font-size:9px;" href="javascript: void(0);" onclick="banned.warnUser(<?=$theme['fromuser_id']?>, 0, 'blogs', 'blog_msg_<?=$theme['id']?>', 0); return false;">Предупредить (<span class="warncount-<?=$theme['fromuser_id']?>">
              <?=($theme['warn'] ? $theme['warn'] : 0)?>
              </span>)</a></span>
              <?
                            }
                            else {
                                $sBanTitle = (!$theme['is_banned'] && !$theme['ban_where']) ? 'Забанить!' : 'Разбанить';
                            ?>
              <span class="warnlink-<?=$theme['fromuser_id']?>"><a href="javascript:void(0);" onclick="banned.userBan(<?=$theme['fromuser_id']?>, 'blog_msg_<?=$theme['id']?>',0)" style="color: Red;font-size:9px;">
              <?=$sBanTitle?>
              </a></span> |
              <?php
                            }
                            }?></td>
          </tr>
        </table>
        <?
                }
            }
            ?>
      </div>
    </div>
    <div class="clear" style="border-bottom: 1px solid #C6C6C6; margin-bottom:20px;"></div>
    <?php                
            }
            else { // post
            $tags_line = array();
            if ( $theme['title'] == '' && $theme['calc_title'] ) {
                $theme['title'] = $theme['calc_title'];
            }         	
        if ((!$theme['is_banned'] || hasPermissions('blogs')) && (!$theme['is_blocked'] || hasPermissions('blogs') || $theme['uid']==$uid)) {
            
                        $cnt_role = (substr($theme['role'], 0, 1)  == '0')? "frl" : "emp";?>
    <div class="blog " id="tm<?=$theme['id']?>">
      <div id="scrollb<?=$theme['thread_id']?>"></div>
      <a name="tm<?=$theme['id']?>"></a> <a name="b<?=$theme['thread_id']?>"></a>
      <div <? if ($from && $from ==$theme['thread_id']) { ?>id="msg"<? } ?> class="upic">
        <?=view_avatar_info($theme['login'],$theme['photo'], 1)?>
      </div>
      <div class="blogcnt">
        <?
                                        if(!$mod && $theme['deleted']) {
                                            $css_deleted = ' style="color:#cccccc;" ';
                                        } else {
                                            $css_deleted = '';
                                        }
                                        ?>
        <div class="<?=$cnt_role?>login b-layout__txt b-layout__txt_fontsize_11">
          <? if ($theme['title']!=='' && !strcmp($theme['login'],"Anonymous")){
                                	preg_match("/^([^(@@@:)]*)@@@: ([^\s]*)/", $theme['title'], $matches);
                                	$theme['uname'] = $matches[1];
                                	$theme['msgtext'] = "<a class='mailto-login' href=\"mailto:".$matches[2]."\">".$matches[2]."</a><br />".$theme['msgtext'];
                                	$theme['title'] = "";
                                }
                                ?>
          <? seo_start()?>
          <?/*!!!is_team!!!*/
                $pro = ($theme['payed'] == 't'?(is_emp($theme['role'])?view_pro_emp():view_pro2(($theme['payed_test'] == 't')?true:false)):""); 
                $is_team = view_team_fl();
                ?>
          <?  /*if ($theme['payed'] == 't') { ?><?=(is_emp($theme['role'])?view_pro_emp():view_pro2(($theme['payed_test'] == 't')?true:false))?><? } */?>
          <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_bold <?=$cnt_role?>login">
          <a id="user_<?=$theme['id']?>_1" href="/users/<?=$theme['login']?>/" title="<?=($theme['uname']." ".$theme['usurname'])?>">
          <?=user_in_color($theme['uname']." ".$theme['usurname'],$theme['role'],$theme['payed'])?>
          </a>
          <?=user_in_color('[',$theme['role'],$theme['payed'])
          ?><a id="user_<?=$theme['id']?>_2" href="/users/<?=$theme['login']?>/" title="<?=$theme['login']
          ?>"><?=user_in_color($theme['login'],$theme['role'],$theme['payed'])
          ?></a><?=user_in_color(']',$theme['role'],$theme['payed'])
          ?> <?=$theme['is_team']=='t'?$is_team:$pro?><?= is_verify($theme['login']) ? view_verify() : ''?> <?= ( $theme['completed_cnt'] > 0 ? view_sbr_shield() : '' );?>
          <? if ($mod == 0) {?>
          <a class="mailto-login" href="mailto:<?=$theme['email']?>">
          <?=$theme['email']?>
          </a>
          <? } ?>
          </span>
          <?= seo_end()?>
          <span class="b-layout__txt b-layout__txt_fontsize_11">
          <?=date("[d.m.Y | H:i]",strtotimeEx($theme['post_time']))?>
          <script type="text/javascript">clear_link('#user_<?=$theme['id']?>_1', '?f=<?=stat_collector::REFID_BLOGS?>&stamp=<?=$_SESSION['stamp']?>'); clear_link('#user_<?=$theme['id']?>_2', '?f=<?=stat_collector::REFID_BLOGS?>&stamp=<?=$_SESSION['stamp']?>');</script>
          <?

                                        if ($theme['modified']) { ?>
          &nbsp;
          <?

                                        if ($theme['modified_id'] == $theme['fromuser_id']) { ?>
          [внесены изменения:
          <?=date("d.m.Y | H:i]",strtotimeEx($theme['modified'])); }
                                        else { ?>
          [Отредактировано модератором
          <? if (!$mod) { ?>
          (
          <? $user = new users; $mod_user = $user->GetName($theme['modified_id'], $err); print($mod_user['login'] . ' : ' . $mod_user['usurname'] . ' ' . $mod_user['uname']); ?>
          )
          <? } ?>
          <?=date("d.m.Y | H:i]",strtotimeEx($theme['modified']));?>
          <?} }
                                        
                                        if ($theme['deleted']  && (hasPermissions('blogs') || $theme['fromuser_id'] == get_uid(false))) {?>
          &nbsp; &nbsp;
          <?
			if (!$theme['deluser_id'] || $theme['deluser_id'] == $theme['fromuser_id']) { ?>
          [блог удален :
          <?=date("d.m.Y | H:i]",strtotimeEx($theme['deleted'])); }
     			 else { ?>
          <span style="color:#FF0000">Удалено модератором
          <? if (!$mod) { ?>
          (
          <? if (!$user) $user = new user(); $mod_user = $user->GetName($theme['deluser_id'], $err); print($mod_user['login'] . ' : ' . $mod_user['usurname'] . ' ' . $mod_user['uname']); ?>
          )
          <? } ?>
          </span>
          <?=date("[d.m.Y | H:i]",strtotimeEx($theme['deleted']));?>
          <?}?>
          <br />
          <?}

                                        if ($theme['is_banned']) {?>
          <span style="color:#FF0000"><b>Пользователь забанен.</b></span>
          <?}
                               			?>
          </span>
          <? if ($theme['deleted']  && (hasPermissions('blogs') || $theme['fromuser_id'] == get_uid(false))) {?>
          <? if($theme['deleted_reason']) { ?>
          <div style="padding-left:28px"> <span style="color:#777777;font-weight:bold">Причина:</span> <span style="color:#FF0000">
            <?=$theme['deleted_reason'] ?>
            </span> </div>
          <?php } ?>
          <?} ?>
        </div>
        <? if($theme['title']!=='') { ?>
        <div class="header header-margin-bottom">
          <? if ($theme['ontop']=='t'){?>
          <img src="/images/tp-w.gif" alt="" />
          <? } ?>
          <? if($theme['is_private']=='t') { ?>
          <img src="/images/icons/eye-hidden.png" alt="Скрытый пост" />&nbsp;
          <? } ?>
          <?php 
                                            $sTitle  = /*($theme['moderator_status'] === '0' && $theme['payed'] != 't') ? $stop_words->replace($theme['title']) :*/ $theme['title']; 
                                            $sTitle2 = /*($theme['moderator_status'] === '0' && $theme['payed'] != 't') ? $stop_words->replace($theme['title'], 'plain') :*/ $theme['title']; 
                                        ?>
          <a href="<?=getFriendlyURL("blog", $theme['thread_id'])?>" class="bl_name" title="<?=$sTitle2?>" <?=$css_deleted?>>
          <?=reformat($sTitle,37,0,1)?>
          </a> </div>
        <? } else { ?>
        <?if($theme['is_private']=='t'){?>
        <div class="header header-margin-bottom"><img src="/images/icons/eye-hidden.png" alt="Скрытый пост" />&nbsp;</div>
        <? } ?>
        <? } ?>
        <div>
          <table border="0" cellspacing="0" cellpadding="0" width="100%">
            <tr>
              <td><? seo_start(); ?>
                <?php $sMessage = /*($theme['moderator_status'] === '0' && $theme['payed'] != 't') ? $stop_words->replace($theme['msgtext']) :*/ $theme['msgtext']; ?>
                <div class="blog-one-cnt" id="message<?=$theme['thread_id']?>" <?=$css_deleted?>>
                  <?=reformat($sMessage, 45, 1, -($theme['is_chuck']=='t'), 1)?>
                </div>
                <br />
                <? 
										// опросы
										$i = 0;
										if ($theme['has_poll']) { 
											$max = 0;
											if ($theme['poll_closed'] == 't') {
												foreach ($theme['poll'] as $poll) $max = max($max, $poll['votes']);
											}
										?>
                <div id="poll-<?=$theme['thread_id']?>" class="poll">
                  <?php $sQuestion = /*($theme['moderator_status'] === '0' && $theme['payed'] != 't') ? $stop_words->replace($theme['poll_question']) :*/ $theme['poll_question']; ?>
                  <div class="poll-theme">
                    <?=reformat($sQuestion, 40, 0, 1)?>
                  </div>
                  <div id="poll-answers-<?=$theme['thread_id']?>">
                    <? if (($theme['poll_closed'] == 't')||($theme['poll_votes'] || !$_SESSION['uid'] || $ban_where == 1 || $theme['is_blocked'])) { ?>
                    <table class="poll-variants">
                      <? foreach ($theme['poll'] as $poll) { ?>
                      <?php $sAnswer = /*($theme['moderator_status'] === '0' && $theme['payed'] != 't') ? $stop_words->replace($poll['answer']) :*/ $poll['answer']; ?>
                      <tr>
                        <? if ($theme['poll_closed'] == 't') { ?>
                        <td class="bp-vr"><label for="poll_<?=$i?>">
                            <?=reformat($sAnswer, 40, 0, 1)?>
                          </label></td>
                        <td class="bp-res"><?=$poll['votes']?></td>
                        <td><div class="res-line rl1" style="width: <?=($max? round(((100 * $poll['votes']) / $max) * 3): 0)?>px;"></div></td>
                        <? } else { ?>
                        <? if ($theme['poll_votes'] || !$_SESSION['uid'] || $ban_where == 1 || $theme['is_blocked']) { ?>
                        <td class="bp-vr"><label for="poll_<?=$i?>">
                            <?=reformat($sAnswer, 40, 0, 1)?>
                          </label></td>
                        <td class="bp-gres"><?=$poll['votes']?></td>
                        <td><div class="res-line rl1" style="width: <?=($max? round(((100 * $poll['votes']) / $max) * 3): 0)?>px;"></div></td>
                        <? } ?>
                        <? } ?>
                      </tr>
                      <? } ?>
                    </table>
                      <? } ?>
                    
                    <? if (!(($theme['poll_closed'] == 't')||($theme['poll_votes'] || !$_SESSION['uid'] || $ban_where == 1 || $theme['is_blocked']))) { ?>
                    <? if (!($theme['poll_multiple'] == 't')) { ?><div class="b-radio b-radio_layout_vertical"><? } ?>
                      <? foreach ($theme['poll'] as $poll) { ?>
                      <?php $sAnswer = /*($theme['moderator_status'] === '0' && $theme['payed'] != 't') ? $stop_words->replace($poll['answer']) :*/ $poll['answer']; ?>
                        <? if ($theme['poll_multiple'] == 't') { ?>
                          <div class="b-check b-check_padbot_5">
                            <input id="poll-<?=$theme['thread_id']?>_<?=$i?>" class="b-check__input" type="checkbox" name="poll_vote[]" value="<?=$poll['id']?>" />
                            <label class="b-check__label b-check__label_fontsize_13" for="poll-<?=$theme['thread_id']?>_<?=$i++?>">
                              <?=reformat($sAnswer, 40, 0, 1)?>
                            </label>
                          </div>
                          <? } else { ?>
                          <div class="b-radio__item b-radio__item_padbot_10">
                            <table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
                              <tr class="b-layout__tr">
                                <? if (!$theme['deleted']){ ?>
                                <td class="b-layout__left b-layout__left_width_15"><input id="poll-<?=$theme['thread_id']?>_<?=$i?>" class="b-radio__input" type="radio" name="poll_vote" value="<?=$poll['id']?>" /></td>
                                <?} ?>
                                <td class="b-layout__right"><label class="b-radio__label b-radio__label_fontsize_13" for="poll-<?=$theme['thread_id']?>_<?=$i++?>">
                                    <?=reformat($sAnswer, 40, 0, 1)?>
                                  </label></td>
                              </tr>
                            </table>
                          </div>
                          <? } ?>
                      <? } ?>
                     <? if (!($theme['poll_multiple'] == 't')) { ?></div><? } ?>      
                      <? } ?>
                          
                  </div>
                  <div class="poll-options">
                    <? if (!$theme['poll_votes'] && $_SESSION['uid'] && $theme['poll_closed'] != 't' && $ban_where != 1 && !$theme['is_blocked'] && !$theme['deleted']) { ?>
                    <div class="b-buttons b-buttons_inline-block"> <span id="poll-btn-vote-<?=$theme['thread_id']?>"> <a class="b-button b-button_rectangle_color_transparent" href="javascript: return false;" onclick="poll.vote('Blogs', <?=$theme['thread_id']?>); return false;"><span class="b-button__b1"><span class="b-button__b2"><span class="b-button__txt">Ответить</span></span></span></a>&nbsp;&nbsp;&nbsp; </span> <span id="poll-btn-result-<?=$theme['thread_id']?>"><a class="b-buttons__link b-buttons__link_dot_0f71c8" href="javascript: return false;" onclick="poll.showResult('Blogs', <?=$theme['thread_id']?>); return false;">Посмотреть результаты</a>&nbsp;&nbsp;&nbsp;</span> </div>
                    <? } else { ?>
                    <span id="poll-btn-vote-<?=$theme['thread_id']?>"></span> <span id="poll-btn-result-<?=$theme['thread_id']?>"></span>
                    <? } ?>
                    <? if (($theme['fromuser_id'] == $_SESSION['uid'] && $ban_where != 1 && !$theme['is_blocked'] && !$theme['deleted']) || hasPermissions('blogs')) { ?>
                    <span id="poll-btn-close-<?=$theme['thread_id']?>"><a class="b-buttons__link b-buttons__link_dot_0f71c8" href="javascript: return false" onclick="poll.close('Blogs', <?=$theme['thread_id']?>); return false;">
                    <?=(($theme['poll_closed'] == 't')? 'Открыть': 'Закрыть')?>
                    опрос</a>&nbsp;&nbsp;&nbsp;</span> <span id="poll-btn-remove-<?=$theme['thread_id']?>"><a class="b-buttons__link b-buttons__link_dot_0f71c8" href="javascript: return false" onclick="poll.remove('Blogs', <?=$theme['thread_id']?>); return false;">Удалить опрос</a></span>
                    <? } ?>
                  </div>
                </div>
                <? } ?>
                <?
                                        if ($theme['attach']) {
                                            $attach = $theme['attach'][0];
                                            if ($attach['fname']) {
                                                $att_ext = strtolower(CFile::getext($attach['fname']));
                                                /*if ($att_ext == "swf") {
                                                    print("<br />".viewattachExternal($theme['login'], $attach['fname'], "upload", "/blogs/view_attach.php?user=".$theme['login']."&attach=".$attach['fname'])."<br />");
                                                } elseif($att_ext == 'flv') {
                                                    print("<br />".viewattachLeft($theme['login'], $attach['fname'], "upload", $file, 1000, 470, 307200, false, (($attach['small']==2)?1:0))."<br />");
                                                } else {
                                                    print('<div class="flw_offer_attach">'.viewattachLeft($theme['login'], $attach['fname'], "upload", $file, 1000, 470, 307200, false, (($attach['small']==2)?1:0))."</div>");
                                                }*/
                                                print('<div class="flw_offer_attach">'.viewattachLeft($theme['login'], $attach['fname'], "upload", $file, blogs::MAX_IMAGE_HEIGHT, blogs::MAX_IMAGE_WIDTH, 307200, false, (($attach['small']==2)?1:0), 1, 0, $theme['title'])."</div>");
                                            }

                                            if (sizeof($theme['attach']) > 1)
                                            {
                                                echo "<br /><a href=\"".getFriendlyURL("blog", $theme['thread_id'])."\"><b>".$blog->ShowMoreAttaches(sizeof($theme['attach']))."</b></a>";
                                            }
                                            
                                        }?>
                <?= seo_end()?>
                <?if ($theme['yt_link']) {
                                                // Не понял зачем это надо, всеравно ничего здесь не выводится
                                                show_video($theme['id'], $theme['yt_link']);
                                            }

                                        if ($ord == 'relevant') {
                                        	if ($theme['num'] > 1) {
                                              ?>
                <table align="right" border="0" cellpadding="0" cellspacing="0" width="100%">
                  <tr>
                    <td class="lastime" align="right">Последний комментарий был
                      <?=( (time() - strtotimeEx($theme['last_activity']))<60 ? "только что" : ago_pub(strtotimeEx($theme['last_activity']))." назад")?></td>
                  </tr>
                </table>
                <?
                                        	}
                                        	/*
                                        	else
                                        	{
                                        	?>
                                        	Комментариев еще не было
                                        	<?
                                        	}
                                        	*/
                                        }
                                        ?></td>
            </tr>
          </table>
        </div>
        <?
                        if ($theme['yt_link'])
                        {
                            print('<br clear="all" /><center>' . show_video($theme['id'],$theme['yt_link']) . '</center><br /><br />');
                        }
                        ?>
        <div id="thread-reason-<?=$theme['thread_id']?>" style="margin-top: 10px;<?=($theme['is_blocked']? 'display: block': 'display: none')?>">
          <? 
							if ($theme['is_blocked']) {
								$moder_login = (hasPermissions('blogs'))? $theme['moder_login']: '';
								$reason      = reformat( $theme['reason'], 24, 0, 0, 1, 24 );
								print BlockedThreadHTML($reason, $theme['blocked_time'], $moder_login, "{$theme['moder_name']} {$theme['moder_uname']}");
							} else {
								print '&nbsp;';
							}
						?>
        </div>
        <div id="warnreason-<?=$theme['thread_id']?>" style="display:none">&nbsp;</div>
      </div>
      <div class="footer">
        <?
		if ($uid)
		{
?>
        <div class="star-outer"><img src="/images/bookmarks/<?=(!isset($favs[$theme['thread_id']]))?'bsw.png':blogs::$priority_img[$favs[$theme['thread_id']]['priority']]?>" alt="Добавить в закладки" title="Добавить в закладки" class="star" id="favstar<?=$theme['thread_id']?>" onclick="ShowFavFloat(<?=$theme['thread_id']?>)" />
          <div id="FavFloat<?=$theme['thread_id']?>" style="postiton:absolute; margin-left:-11px; margin-top:-16px;"></div>
        </div>
        <div id="favcnt<?=$theme['thread_id']?>" class="favor-number"><span>
          <?=$theme['fav_cnt']?>
          </span></div>
        <?
		}
				else {
?>
        <div class="star-outer"><img src="/images/bookmarks/bsw.png" alt="" title="" class="star" id="favstar<?=$theme['thread_id']?>" style="position: absolute;" /></div>
        <div  class="favor-number"><span>
          <?=$theme['fav_cnt']?>
          </span></div>
        <?php
		}
?>
        <div class="section-blog">
          <div class="section">Раздел:</div>
          <? seo_start();?>
          <div class="small"> <a href="<?=getFriendlyURL("blog_group", $theme['id_gr'])?><?=((($theme['t'])?"?t=prof":""))?>">
            <?=getGroupName($theme['id_gr'],$theme['t'])?>
            </a> </div>
        </div>
        <?= seo_end();?>
        <div class="commline">
          <? if(!$mod && $theme['deleted']) { ?>
          <a href="/blogs/viewgroup.php?id=<?=$theme['id']?>&amp;action=restore&page=<?=$page?>&ord=<?=$ord?>&r=1" onclick="return warning(14);">Восстановить</a> |
          <? } else { ?>
          <?
							if (hasPermissions('blogs') && $theme['login']!=$_SESSION["login"] && $theme['login']!="admin") {
							    ?>
          <script type="text/javascript">
                                banned.addContext( 'blog_<?=$theme['thread_id']?>', 2, '<?=$GLOBALS['host']?><?=getFriendlyURL("blog", $theme['thread_id'])?>', "<?=($theme['title']!==''? $theme['title'] : '<без темы>')?>" );
                                </script>
          <?php
                                if(hasPermissions('users')) {
								if ( $theme['warn'] < 3 && !$theme['is_banned'] && !$theme['ban_where'] ) {
									?>
          <span class="admin-top-link"><span class="warnlink-<?=$theme['uid']?>"><a style="color: #D75A29; font-size:9px;" href="javascript: void(0);" onclick="banned.warnUser(<?=$theme['uid']?>, 0, 'blogs', 'blog_<?=$theme['thread_id']?>', 0); return false;">Предупредить (<span class="warncount-<?=$theme['uid']?>">
          <?=($theme['warn'] ? $theme['warn'] : 0)?>
          </span>)</a></span> |
          <?
								}
								else /*if (!$theme['is_banned'])*/ {
								    $sBanTitle = (!$theme['is_banned'] && !$theme['ban_where']) ? 'Забанить!' : 'Разбанить';
									?>
          <span class="admin-top-link"><span class="warnlink-<?=$theme['uid']?>"><a href="javascript:void(0);" onclick="banned.userBan(<?=$theme['fromuser_id']?>, 'blog_<?=$theme['thread_id']?>',0)" style="color: Red;font-size:9px;">
          <?=$sBanTitle?>
          </a></span> |
          <?php
								}
                                }
                                ?>
          <span id="thread-button-<?=$theme['thread_id']?>"><a style="color: Red; font-size:9px;" href="javascript: void(0);" onclick="banned.<?=($theme['is_blocked']? 'unblockedThread': 'blockedThread')?>(<?=$theme['thread_id']?>); return false;">
          <?=($theme['is_blocked']? 'Разблокировать': 'Блокировать')?>
          </a></span></span>
          <?
							}
  			     ?>
          <? $can_edit = false;
                                   if (($theme['uid'] == $_SESSION['uid'] && !$theme['is_blocked'] && !$theme['deleted']) || !$mod) {
                                   $can_edit = true;
                                ?>
          <a href="/blogs/viewgroup.php?id=<?=$theme['id']?>&amp;action=delete&page=<?=$page?>&ord=<?=$ord?>&u_token_key=<?=$_SESSION['rand']?>&r=1" onclick="return warning(1);">Удалить</a> | 
          <a href="<?=getFriendlyURL('blog_group', $gr)?>?action=edit&amp;tr=<?=$theme['id']?><?=($page>1?'&amp;page='.$page:'')?>&amp;t=<?=$t?>&ord=<?=$ord?>#edit" onclick="return restoreBlogForm(this);">Редактировать</a>
          <? }
                                      if ($can_edit) echo "|";
                                      $new_comments_str = "";
                                      $new_comments_style = "";

                                      if ($_SESSION["uid"])
                                      {
                                          if (!isset($theme['status']) && ($theme['num']) > 1)
                                          {
                                              $new_comments_style = " style=\"font-weight:bold;\"";
                                          }
                                          elseif ($theme['num'] > 1 && $theme['status'] < $theme['num']-1 && $theme['status'] != -100)
                                          {
                                              $new_comments_num = $theme['num'] - $theme['status'] - 1;
                                              $new_comments_str = "<a href=\"".getFriendlyURL("blog", $theme['thread_id'])."#unread\" style=\"color:#6BA813; font-weight:bold;\">(".$new_comments_num." ".(($new_comments_num==1)?"новый":"новых").")</a>";
                                          }
                                      }
                               ?>
          <? } ?>
          <?php 
$sScrollTo = '';

if ( hasPermissions('blogs') || !$theme['deleted'] ) { 
    if ( $theme['num'] > 1 && $theme['close_comments'] != "t" ) {
    	$sScrollTo = '#comments';
    }
}
?>
          <?php if($theme['title']!='') { seo_start(); }?>
          <a href="<?=getFriendlyURL("blog", $theme['thread_id']).$sScrollTo?>"<?=$new_comments_style?>>
          <?=((($theme['close_comments']=="t"))?"Комментирование закрыто":"Комментарии (".($theme['num']-1).")")?>
          </a>
          <?=$new_comments_str?>
          <?php if($theme['title']!='') { ?>
          <?= seo_end();?>
          <?php } ?>
        </div>
      </div>
    </div>
    <? } 
            }
        } while ($theme = next($themes));}
                else print "<div style='width: 100%; text-align: center;clear:left;'><h2>Сообщений нет</h2></div>"?>
    <table cellpadding="0" cellspacing="0" border="0" width="100%">
      <tr>
        <td align="left" width="100%" ><?php
function buildNavigation($iCurrent, $iStart, $iAll, $sHref) {
	$sNavigation = '';
	for ($i=$iStart; $i<=$iAll; $i++) {
		if ($i != $iCurrent) {
			$sNavigation .= "<a href=\"".$sHref.$i."\" >".$i."</a>";
		}else {
			$sNavigation .= '<b style="margin-right: 5px">'.$i.'</b>';
		}
	}
	return $sNavigation;
}
// массив с параметрими для url
$url_params = array();
if ($ord && $ord != 'new') {
    $url_params[] = 'ord='.$ord;
}
if ($base) {
    $url_params[] = 't=prof';
}
//               параметры                   последний амперсанд
$sHref = '%s?' . implode('&', $url_params) . (count($url_params) ? '&' : '') . 'page=%d%s';
//$sHref = '%s?'.($base ? '&amp;t=prof' : '').(($ord&&($ord != "new")) ? $ord_get_part."&amp;" : '').'page=%d%s';
print(new_paginator2($page, $pages, 4, $sHref, true));
?></td>
      </tr>
      <tr>
        <td class="blog-rss" align="left" style="padding-top: 15px; padding-bottom: 10px"><? seo_start();?>
          <a href="/rss/blogs.php?gr=<?=$gr?>" ><img src="/images/ico_rss.gif" alt="" /></a>&nbsp;&nbsp;<a href="/rss/blogs.php?gr=<?=$gr?>">Фри-ланс</a>
          <?= seo_end();?></td>
      </tr>
    </table>
    <? require_once($_SERVER['DOCUMENT_ROOT']."/blogs/msgform.php"); ?>
  </div>
  
  <div id="leftcl" class="b-layout__left b-layout__left_width_25ps">
      <form action="/search/" name="searh_frm" id="search_frm" method="GET">
        <div>
          <input type="hidden" name="type" value="blogs" />
          <input type="hidden" name="action" id="search_config_action" value="search" />
          <? seo_start();?>
          <div class="b-search b-search_padbot_20">
              <table class="b-search__table" cellpadding="0" cellspacing="0" border="0"><tr class="b-search__tr"><td class="b-search__input">
              <div class="b-input-hint">
                  <div class="b-input b-input_height_24">
                      <input id="fl2_search_input2" class="b-input__text" name="search_string" type="text" />
                </div>
              </div>
              </td><td class="b-search__button">
              <a id="fl2_search_submit" onclick="$('search_frm').submit();" class="b-button b-button_rectangle_color_transparent" href="#" >
                  <span class="b-button__b1">
                      <span class="b-button__b2">
                          <span class="b-button__txt">Найти</span>
                      </span>
                  </span>
              </a>
              </td></tr></table>
          </div>
          <?= seo_end();?>
          <div style="overflow: hidden; height: 0px;">
            <input type="hidden" id="search_config_type_projects" name="search_type[projects]" value="1" />
            <input type="hidden" id="search_config_type_people" name="search_type[people]" value="1" />
            <input type="hidden" id="search_config_type_works" name="search_type[works]" value="1" />
            <input type="hidden" id="search_config_type_messages" name="search_type[messages]" value="1" />
            <input type="hidden" id="search_config_type_commune" name="search_type[commune]" value="1" />
            <input type="hidden" id="search_config_type_blogs" name="search_type[blogs]" value="1" />
            <input type="hidden" id="search_config_type_articles" name="search_type[articles]" value="1" />
            <input type="hidden" id="search_config_type_shop" name="search_type[shop]" value="1" />
            <input type="hidden" id="search_config_type_notes" name="search_type[notes]" value="1" />
          </div>
        </div>
      </form>
    <? $ban_cat = 3; include ($_SERVER['DOCUMENT_ROOT'] . "/banner_under_cat.php")?>
      <h2>Раздел</h2>
      <ul class="group">
        <?
            $size = sizeof($groups);
            $sum  = 0;
            
            for ($i = 0; $i < $size; $i++) {
                $theme = $groups[$i];
                if( in_array($theme['id'], blogs::$copiny_group) ) {
                    $feedback_copiny[] = $theme;
                    continue; // Перенесли в архив #0023264
                }
                $love = (int)$theme['id'] == 55;
                
                if($love && !$allow_love) continue;

                $tname = $theme['t_name'];
                if ($theme['id'] == 7 && $theme['t'] == 0) $tname = "<strong>".$theme['t_name']."</strong>";
                if ( $theme['id'] == $gr && $base == $theme['t'] && $page == 1 )
                $group_line .= $addit."<li".($love ? ' style="background: url(/images/icons/heart.png) no-repeat 2px 7px;" ' : '').(($i == $size - 1)?" class=\"last\"":"").">".($love ? '<span id="love_time_simple" style="float:right">00:00:00</span>' : '').$theme['t_name']." (".zin($theme['num']).")</li>\n";
                else $group_line .= $addit."<li".($love ? ' style="background: url(/images/icons/heart.png) no-repeat 2px 7px;" ' : '').(($i == $size - 1)?" class=\"last\"":"").">".($love ? '<span id="love_time_simple" style="float:right">00:00:00</span>' : '')."<a ". (($theme['id'] == $gr && $base == $theme['t']) ? ' style="color: #666;"' : '') ." href=\"".getFriendlyURL("blog_group", $theme['id']).(($theme['t'] || ($ord != "new"))? ('?'.(($theme['t'])?"&amp;t=prof":"").(($ord != "new")?"&amp;ord=$ord":"")): '')."\" title=\"".$theme['t_name']."\">".$tname." (".zin($theme['num']).")</a></li>\n";
                $sum = $sum + $theme['num'];
            } 
            ?>
        <li>
          <? if ($gr || (!$gr && $page > 1)) {?>
          <a <?=((!$gr && $page > 1) ? ' style="font-weight: bolder; color: #666;"' : '')?> href="/blogs/<?=($ord)?"?ord=$ord":""?>" title="Все вместе">
          <? } ?>
          <strong>Все вместе</strong> (<?=$sum?>)
          <? if ($gr || (!$gr && $page > 1)) {?>
          </a>
          <? } ?>
        </li>
        <?=$group_line?>
      </ul>
      <? if(!empty($feedback_copiny)) { ?>
      <div class="group_copini">
        <div class="b-layout__txt b-layout__txt_padtop_5 b-layout__txt_padbot_5 b-layout__txt_padleft_5 b-layout__txt_fontsize_11 b-layout__txt_bold">Сообщество поддержки</div>
        <ul class="group">
            <?php foreach($feedback_copiny as $theme) { ?>
                <li><a href="https://feedback.fl.ru/" target="_blank"><?= $theme['t_name']; ?></a></li>
            <?php }//foreach?>
        </ul>
      </div>
      <? }//if?>
    <?php if($allow_love){ ?>
    <script type="text/javascript">
var launchdate=new cdLocalTime("love_time_simple", '<?=date("F d, Y H:i:s")?>', 0, '<?=VALENTIN_DATE_END?>');
launchdate.displaycountdown("days", formatresults3)
</script>
    <?php } ?>
    <!-- Banner 240x400 -->
    <?= printBanner240(false, true); ?>
    <!-- end of Banner 240x400 --> 
  </div>
  
  <div class="clear"></div>
</div>
<?}?>
<script type="text/javascript">

if (document.getElementById('poll-question')) {
        domReady( function() {
            document.getElementById('poll-question').value = document.getElementById('poll-question-source').value;
            if(document.getElementById('msg'))
                document.getElementById('msg').value = ($('msg_source')? $('msg_source').value : null);
        } );
        poll.init('Blogs', document.getElementById('editmsg'), <?= blogs::MAX_POLL_ANSWERS ?>, '<?= $_SESSION['rand'] ?>');
    maxChars('poll-question', 'poll-warn', <?= blogs::MAX_POLL_CHARS ?>);
}
else {

    domReady( function() {
        if(document.getElementById('msg'))
            document.getElementById('msg').value = ($('msg_source')? $('msg_source').value : null);
    } );
}


<? if (!empty($tr) && $_GET['action'] != 'edit') { ?>goToAncor('tm<?=$tr?>');<? } ?>

<? if (isset($alert) || $action=='edit') { ?>goToAncor('edit');<? } ?>


<? if ($uid) { ?>

function InitHideFav()
{
	HideFavFloat(0,0);
	HideFavOrderFloat(currentOrderStr);
}

document.body.onclick = InitHideFav;

<? } ?>

var gr_value = '<?=$gr_value?>';

</script>
<?php
if ( hasPermissions('blogs') ) {
    include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/ban_overlay.php' );
    include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/warn_overlay.php' );
    include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/del_overlay.php' );
}
?>
