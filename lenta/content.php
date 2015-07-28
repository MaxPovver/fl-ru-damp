<?  

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
  require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/lenta.common.php");


  $xajax->printJavascript('/xajax/');

  if(!($groups = professions::GetAllGroupsLite()))
    return;

  if(!($lenta = lenta::GetUserLenta($uid)));

   $sort = $_COOKIE['lenta_fav_order']!=""?$_COOKIE['lenta_fav_order']:"date"; 
   $favs = lenta::GetFavorites($uid, $sort);
  

  if(!($myComms = commune::GetCommunes(NULL, $uid, NULL, commune::OM_CM_MY)))
    $myComms = array();

  if(!($joinedComms = commune::GetCommunes(NULL, NULL, $uid, commune::OM_CM_JOINED)))
    $joinedComms = array();

  // Все сообщества, доступные пользователю.
  $communes = array();
  foreach($myComms as $comm) $communes[] = $comm;
  foreach($joinedComms as $comm) $communes[] = $comm;

  // Блоги
  if(!($blog_grs=blogs::GetThemes($error, 1)))
    $blog_grs = array();

  $blg=NULL;
  if($lenta && $lenta['blog_grs'] && !empty($lenta['blog_grs']))
      $blg = implode(',', $lenta['blog_grs']);

  $pgs=NULL;
  if($lenta['all_profs_checked']=='f') {
    if($lenta && $lenta['prof_groups'] && !empty($lenta['prof_groups']))
      $pgs = implode(',', $lenta['prof_groups']);
  }

  $allThemesCount = 0;
  $cms=NULL;
  $user_comm_mods = array();

  $DB = new DB('master');

  // Отбираем среди выбранных ранее сообществ в ленте, те которые до сих пор остаются
  // доступными ему для просмотра в ленте (его могли удалить или забанить). А также заполняем массив user_mod-ов на каждое из сообществ.
  if($lenta && $lenta['communes'] && !empty($lenta['communes']))
  {
    $i=0;
    $cms = '';
    foreach($lenta['communes'] as $cm_id) {
      if($uStatus = commune::GetUserCommuneRel($cm_id, $uid)) {
        $ucm = $user_mod;
        $ucm |= commune::MOD_COMM_MODERATOR * $uStatus['is_moderator'];
        $ucm |= commune::MOD_COMM_MANAGER * $uStatus['is_manager'];
        $ucm |= commune::MOD_COMM_ADMIN * ($uStatus['is_admin'] || $uStatus['is_moderator'] || $uStatus['is_manager']);
        $ucm |= commune::MOD_COMM_AUTHOR * $uStatus['is_author'];
        $ucm |= commune::MOD_COMM_ASKED * $uStatus['is_asked'];
        $ucm |= commune::MOD_COMM_ACCEPTED * ($uStatus['is_accepted'] || ($ucm & commune::MOD_COMM_ADMIN));
        $ucm |= commune::MOD_COMM_BANNED * $uStatus['is_banned'];
        $ucm |= commune::MOD_COMM_DELETED * $uStatus['is_deleted'];
        $user_comm_mods[$cm_id] = $ucm;
        if(!$uStatus['is_deleted']
           && !$uStatus['is_banned']
           && ($uStatus['is_accepted']
               || $uStatus['is_author']))
          $cms .= (!$i++?'':',').$cm_id;
      }
    }

  }

  if(!$items)
    $items = array();
    
    //var_dump($favs);

  $stars = array(0=>'bsg.png', 1=>'bsgr.png', 2=>'bsy.png', 3=>'bsr.png');



$all_communes_check = 0;
if(count($communes)) {
    foreach($communes as $comm) {
        if(($lenta && in_array($comm['id'], $lenta['communes']))) {
            $all_communes_check++;
        }
    }
}
if($all_communes_check==count($communes) && $all_communes_check!=0) { $all_communes_check = true; } else { $all_communes_check = false; }

$all_blog_grs_check = 0;
if(count($blog_grs)) {
    foreach($blog_grs as $blog_gr) {
        if(($lenta && in_array($blog_gr['id_gr'], $lenta['blog_grs']))) {
            $all_blog_grs_check++;
        }
    }
}
if($all_blog_grs_check==count($blog_grs) && $all_blog_grs_check!=0) { $all_blog_grs_check = true; } else { $all_blog_grs_check = false; }

$all_groups_check = 0;
foreach($groups as $grp) {
    if(!$grp['id'] || ($lenta && in_array($grp['id'], $lenta['prof_groups']))) {
        $all_groups_check++;
    }
}
if($all_groups_check==count($groups) && $all_groups_check!=0) { $all_groups_check = true; } else { $all_groups_check = false; }
?>
<script type="text/javascript">
    function oopg() {
        var i, len, profs = document.getElementsByName('prof_group_id[]');
        len = profs.length;
        for(i=0;i<len;i++)
            profs[i].disabled = !profs[i].disabled;
    }
    
    function InitHideFav() {
    	HideFavFloat(0,0);
    	HideFavOrderFloatLenta(currentOrderStr);
    }
    
    document.body.onclick = InitHideFav;

    function toggle_visibility(id) {
        var e = document.getElementById(id);
        if(e.style.display == 'block')
            e.style.display = 'none';
        else
            e.style.display = 'block';
    }

    function toggle_checkbox(id) {
        var e = $(id);
        if(e.get('checked') == true)
            e.set('checked',false);
        else
            e.set('checked',true);
    }

    function toggle_all_checkbox(id) {
        if($('all_'+id).get('checked')==true) {
            $$('#'+id+' input[type=checkbox]').each(function(el) { el.set('checked',true); });
        } else {
            $$('#'+id+' input[type=checkbox]').each(function(el) { el.set('checked',false); });
        }
    }

    function lenta_check_subcats(id) {
        ch_count = 0;
        al_count = 0;
        $$('#'+id+' input[type=checkbox]').each(function(el) { al_count++; if(el.get('checked')) { ch_count++; } });
        if(ch_count>0) {
            $('all_'+id).set('checked', true);
        } else {
            $('all_'+id).set('checked', false);
        }
    }

    function disable_lenta_cats_checkbox() {
        $$("#lenta_cats_checkboxes input[type=checkbox]").each(function(el) { el.set("disabled", true); });
    }
</script>




					<h1 id='hh' class="b-page__title">Лента</h1>
					<div class="page-lenta">
						<div class="p-lenta-in c">
                            <a name="lentatop"></a>
							<div id="lenta-cnt" class="lenta-cnt b-layout__right b-layout__right_relative b-layout__right_width_72ps b-layout__right_float_right">

<? if(1==2) { ?>
                            <?
                            $i=0;
                            foreach($items as $item) {
                                switch ($item['item_type']) {
                                    case '2':
                                    // Сообщества
                                    $top = $item;
                                    $user_mod = $user_comm_mods[$top['commune_id']];
                                    if( ($top['member_is_banned'] && !($user_mod & (commune::MOD_ADMIN | commune::MOD_COMM_AUTHOR | commune::MOD_COMM_MANAGER)))
                                            || ($top['is_private'] == 't' && $top['user_id']!=$uid && !($user_mod & (commune::MOD_ADMIN | commune::MOD_COMM_AUTHOR | commune::MOD_COMM_MANAGER))) )
                                            { continue; }
                                    $sql = "SELECT * FROM commune_poll_answers WHERE theme_id IN (".$top['theme_id'].") ORDER BY id";
                                    $res = $DB->rows($sql);
                                    if($res) {
                            			foreach ($res as $row) {
                            				$top['answers'][] = $row;
                            			}
                                    }
                                    $GLOBALS[LINK_INSTANCE_NAME] = new links('commune');
                                    $user_id = $uid;
                                    $mod = $user_mod;
                                    $is_member = $mod & (commune::MOD_ADMIN | commune::MOD_COMM_ACCEPTED | commune::MOD_COMM_AUTHOR);
                                    $is_moder  = $mod & (commune::MOD_ADMIN | commune::MOD_MODER | commune::MOD_COMM_MODERATOR | commune::MOD_COMM_AUTHOR);


                            ?>
								<div class="lo" id='idTop_<?=$top['id']?>'>
									<ul class="lo-p">
										<li class="lo-s">
											<div class="b-layout_bordrad_3">
												<a href="" class="lnk-dot-666">Сообщества</a>
											</div>
										</li>
										<li class="post-f-fav">
                                            <? $msg_id = $top['id'];?>
                                            <? if($favs['CM'.$msg_id]): ?> 
                                                <img src="/images/bookmarks/<?=$stars[$favs['CM'.$msg_id]['priority']]?>" alt="" on="<?=($is_fav ? 1 : 0)?>" id="favstar<?='CM'.$msg_id?>" <? if($uid):?>onclick="ShowFavFloatLenta(<?=$msg_id?>, <?=$uid?>, 'CM')" /><? endif;?>
                                            <? else: ?>
                                                <img src="/images/bookmarks/bsw.png" alt="" on="<?=($is_fav ? 1 : 0)?>" id="favstar<?='CM'.$msg_id?>" <? if($uid):?>onclick="ShowFavFloatLenta(<?=$msg_id?>, <?=$uid?>, 'CM')" /><? endif;?>
                                            <? endif; ?>
                                            <div id="FavFloat<?=$msg_id?>"></div>
										</li>
									</ul>
									<div class="utxt">
										<h3><a href="/commune/?id=<?=$top['commune_id']?>&site=Topic&post=<?=$top['id']?>&om=<?=commune::OM_TH_NEW?>"><?=reformat2($top['title'], 30, 0, 1)?></a></h3>
										<p><?=reformat2($top['msgtext'], 46, 1,0,1);?></p>

                                        <!-- Questions -->
			<? if ($top['question'] != '') { ?>
			<div id="poll-<?=$top['theme_id']?>" class="commune-poll">
				<div class="commune-poll-theme"><?=reformat($top['question'], 43, 0, 1)?></div>
				<div id="poll-answers-<?=$top['theme_id']?>">
				<table class="poll-variants">
				<?
				$i=0;
				$max = 0;
				if ($top['poll_closed'] == 't') {
					foreach ($top['answers'] as $answer) $max = max($max, $answer['votes']);
				}
				foreach ($top['answers'] as $answer) {
				?>
				<tr>
				<? if ($top['poll_closed'] == 't') { ?>
					<td class="bp-vr"><label for="poll_<?=$i?>"><?=reformat($answer['answer'], 30, 0, 1)?></label></td>
					<td class="bp-res"><?=$answer['votes']?></td>
					<td><div class="res-line rl1" style="width: <?=($max? round(((100 * $answer['votes']) / $max) * 3): 0)?>px;"></div></td>
				<? } else { ?>
					<? if ($top['poll_votes'] || !$user_id || $top['commune_blocked'] == 't' || $top['user_is_banned'] || $top['member_is_banned'] || !$is_member) { ?>
						<td class="bp-gres"><?=$answer['votes']?></td>
					<? } else { ?>
						<td colspan="2">
						<? if ($top['poll_multiple'] == 't') { ?>
							<div class="b-check  b-check_padbot_5">
								<input class="b-check__input" type="checkbox" name="poll_vote[]" id="poll-<?=$top['theme_id']?>_<?=$i?>" value="<?=$answer['id']?>" /><label class="b-check__label b-check__label_fontsize_13" for="poll-<?=$top['theme_id']?>_<?=$i++?>"><?=reformat($answer['answer'], 30, 0, 1)?></label>
                            </div>
						<? } else { ?>
                        	<div class="b-radio__item  b-radio__item_padbot_5">
                            	<table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
                                	<tr class="b-layout__tr">
                                    	<td class="b-layout__left b-layout__left_width_15"><input class="b-radio__input b-radio__input_top_-3" type="radio" name="poll_vote" id="poll-<?=$top['theme_id']?>_<?=$i?>" value="<?=$answer['id']?>" /></td>
                                     <td class="b-layout__right"><label class="b-radio__label b-radio__label_fontsize_13" for="poll-<?=$top['theme_id']?>_<?=$i++?>"><?=reformat($answer['answer'], 30, 0, 1)?></label></td>
                                 </tr>
                              </table>
                            </div>
						<? } ?>
					</td>
					<? } ?>
				<? } ?>
				</tr>
				<? } ?>
				</table>
				</div>
                
                
				<div class="commune-poll-options">
				<? if (!$top['poll_votes'] && $user_id && $top['poll_closed'] != 't' && $top['commune_blocked'] != 't' && !$top['user_is_banned'] && !$top['member_is_banned'] && $is_member) { ?>
                
                    <div class="b-buttons b-buttons_inline-block">
					<span id="poll-btn-vote-<?=$top['theme_id']?>"><a class="b-button b-button_flat b-button_flat_grey" href="javascript: return false;" onclick="poll.vote('Commune', <?=$top['theme_id']?>); return false;">Ответить</a>&nbsp;&nbsp;&nbsp;</span>
					<span id="poll-btn-result-<?=$top['theme_id']?>"><a class="b-buttons__link b-buttons__link_dot_0f71c8" href="javascript: return false;" onclick="poll.showResult('Commune', <?=$top['theme_id']?>); return false;">Посмотреть результаты</a>&nbsp;&nbsp;&nbsp;</span>
                    </div>
                
                
				<? } else { ?>
				<span id="poll-btn-vote-<?=$top['theme_id']?>"></span>
				<span id="poll-btn-result-<?=$top['theme_id']?>"></span>
				<? } ?>
				</div>
			</div>
            <br />
			<? } ?>
                                        <!-- /Questions -->

                                        <!-- Youtube -->
            <?=(($top['youtube_link'])? ("<div style='padding-top: 20px'>".show_video($top['id'], $top['youtube_link'])."</div>"):"")?>
                                        <!-- /Youtube -->

                                        <!-- Attach -->
            <? if ($top['attach']) {
                
                foreach ($top['attach'] as $attach) {
                    $att_ext = CFile::getext($attach['fname']);
                    $str = '';
                    if ($att_ext == "swf") {
                        $str = viewattachExternal($top['user_login'], $attach['fname'], 'upload', "/blogs/view_attach.php?user=".$top['user_login']."&attach=".$attach['fname']);
                        //$str = viewattachLeft($top['user_login'], $attach['fname'], 'upload', $file, 1000, 470, 307200,  1, !$attach['small'], (($attach['small']==2)?1:0));
                    } else {
                        $str = viewattachLeft($top['user_login'], $attach['fname'], 'upload', $file, commune::MSG_IMAGE_MAX_HEIGHT, commune::_MSG_IMAGE_MAX_WIDTH, commune::MSG_IMAGE_MAX_SIZE,  !($attach['small']=='t'), (int)($attach['small']=='t'));
                            if(commune::_MSG_IMAGE_MAX_WIDTH > 520) {
                              $str = preg_replace_callback('/(width=\"?)(\d+)(\"?\s+height=\"?\d+\"?)/i', '_reWidthImg', $str);
                            }
                    }
                    
                    print("<br /><br />".$str."<br />");
                    
                    break;
                }
                    if (sizeof($top['attach']) > 1) {
                        $cntfl = count($top['attach'])-1;
                        echo "<a href=\"/commune/?id=".$top['commune_id']."&site=Topic&post=".$top['id']."&om=".commune::OM_TH_NEW."\" style=\"color:#003399\"><b>Внутри еще $cntfl фай".ending($cntfl, "л", "ла", "лов")."</b></a><br/><br/>";
                    }
                    
             }?>
                                        <!-- /Attach -->
									</div>
									<ul class="lo-i">
                                        <?
                                        $post_year = dateFormat('Y', $top['post_time']);
                                        ?>
										<li class="lo-i-c"><a href="/commune/?id=<?=$top['commune_id']?>"><?=$top['commune_name']?></a>, <a href="/commune/?gr=<?=$top['commune_group_id']?>"><?=$top['commune_group_name']?></a></li>
										<li><?=($post_year > 2000 ? dateFormat("d.m.Y H:i", $top['post_time']) : '')?></li>
                                        <li class="lo-i-u"><? print(  __LentaPrntUsrInfo($top,'user_'))?></li>
									</ul>
								</div>

                            <?
                                    break;
                                    case '1':
                                      // Портфолио
                                      $work = $item;
                                      $is_fav = (isset($favs['PF'.$work['portfolio_id']]) ? 1 : 0);
                                      $msg_id = $work['portfolio_id'];
                            ?>
								<div class="lo">
									<ul class="lo-p">
										<li class="lo-s">
											<div class="b-layout_bordrad_3">
												<a href="" class="lnk-dot-666">Работы</a>
											</div>
										</li>
										<li class="post-f-fav">
                                            <? if($favs['PF'.$msg_id]): ?> 
                                                <img src="/images/bookmarks/<?=$stars[$favs['PF'.$msg_id]['priority']]?>" alt="" on="<?=($is_fav ? 1 : 0)?>" id="favstar<?='PF'.$msg_id?>" <?if($uid):?>onclick="ShowFavFloatLenta(<?=$msg_id?>, <?=$uid?>, 'PF')" /><?endif;?>
                                            <? else: ?>
                                                <img src="/images/bookmarks/bsw.png" alt="" on="<?=($is_fav ? 1 : 0)?>" id="favstar<?='PF'.$msg_id?>" <?if($uid):?>onclick="ShowFavFloatLenta(<?=$msg_id?>, <?=$uid?>, 'PF')" /><?endif;?>
                                            <? endif; ?>
                                            <div id="FavFloat<?=$msg_id?>"></div>
										</li>
									</ul>
									<div class="utxt">
										<h3><a href="/users/<?=$work['user_login']?>/viewproj.php?prjid=<?=$work['portfolio_id']?>"><?=reformat2($work['name'], 40, 0, 1)?></a></h3>
										<p><?=reformat2($work['descr'], 80, 0)?></p>
									</div>
									<ul class="lo-i">
                                        <?
                                        $post_year = dateFormat('Y', $work['post_time']);
                                        ?>
										<li class="lo-i-c"><a href="/freelancers/?prof=<?=$work['prof_id']?>"><?=$work['prof_name']?></a></li>
										<li><?=($post_year > 2000 ? dateFormat("d.m.Y H:i", $work['post_time']) : '')?></li>
                                        <li class="lo-i-u"><? print(  __LentaPrntUsrInfo($work,'user_'))?></li>
									</ul>
								</div>
                            <?
                                break;
                                    case '4':
                                        // Блоги
                                    $item['thread_id'] = $item['theme_id'];
                                    $sql = "SELECT * FROM blogs_poll_answers WHERE thread_id IN (".$item['thread_id'].") ORDER BY id";
                        			$res = $DB->rows($sql);
                                    if($res) {
                            			foreach ($res as $row) {
                            				$item['answers'][] = $row;
                            			}
                                    }
                                    $GLOBALS[LINK_INSTANCE_NAME] = new links('blogs');
                                    $user_id = $uid;
                                    ?>
								<div class="lo" id='idBlog_<?=$item['thread_id']?>'>
									<ul class="lo-p">
										<li class="lo-s">
											<div class="b-layout_bordrad_3">
												<a href="" class="lnk-dot-666">Блоги</a>
											</div>
										</li>
										<li class="post-f-fav" style="visibility: hidden;">
                                            <? $msg_id = $item['id'];?>
                                            <? if($favs['BL'.$msg_id]): ?> 
                                                <img src="/images/bookmarks/<?=$stars[$favs['BL'.$msg_id]['priority']]?>" alt="" on="<?=($is_fav ? 1 : 0)?>" id="favstar<?='BL'.$msg_id?>" <?if($uid):?>onclick="ShowFavFloatLenta(<?=$msg_id?>, <?=$uid?>, 'BL')" /><?endif;?>
                                            <? else: ?>
                                                <img src="/images/bookmarks/bsw.png" alt="" on="<?=($is_fav ? 1 : 0)?>" id="favstar<?='BL'.$msg_id?>" <?if($uid):?>onclick="ShowFavFloatLenta(<?=$msg_id?>, <?=$uid?>, 'BL')" /><?endif;?>
                                            <? endif; ?>
                                            <div id="FavFloat<?=$msg_id?>"></div>
										</li>
									</ul>
									<div class="utxt">
										<h3><a href="<?=getFriendlyURL("blog", $item['theme_id'])?>"><?=reformat2($item['title'], 30, 0, 1)?></a></h3>
										<p><?=reformat($item['msgtext'], 46, 1,-($item['is_chuck']=='t'),1);?></p>

                                        <!-- Questions -->
			<? if ($item['question'] != '') {?>
			<div id="blog-poll-<?=$item['thread_id']?>" class="poll">
				<div class="commune-poll-theme"><?=reformat($item['question'], 43, 0, 1)?></div>
				<div id="poll-answers-<?=$item['thread_id']?>">
				<table class="poll-variants">
				<?
				$i=0;
				$max = 0;
				if ($item['poll_closed'] == 't') {
					foreach ($item['answers'] as $answer) $max = max($max, $answer['votes']);
				}
				foreach ($item['answers'] as $answer) {
				?>
				<tr>
				<? if ($item['poll_closed'] == 't') { ?>
					<td class="bp-vr"><label for="poll_<?=$i?>"><?=reformat($answer['answer'], 30, 0, 1)?></label></td>
					<td class="bp-res"><?=$answer['votes']?></td>
					<td><div class="res-line rl1" style="width: <?=($max? round(((100 * $answer['votes']) / $max) * 3): 0)?>px;"></div></td>
				<? } else { ?>
					<? if ($item['poll_votes'] || !$user_id) { ?>
						<td class="bp-gres"><?=$answer['votes']?></td>
					<? } else { ?>
						<td colspan="2">
						<? if ($item['poll_multiple'] == 't') { ?>
							<div class="b-check  b-check_padbot_5">
								<input class="b-check__input" type="checkbox" name="poll_vote" id="poll-<?=$item['thread_id']?>_<?=$i?>" value="<?=$answer['id']?>" /><label class="b-check__label b-check__label_fontsize_13" for="poll-<?=$item['thread_id']?>_<?=$i++?>"><?=reformat($answer['answer'], 30, 0, 1)?></label>
                            </div>
						<? } else { ?>
							<div class="b-radio__item  b-radio__item_padbot_5">
                            	<table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
                                	<tr class="b-layout__tr">
                                    	<td class="b-layout__left b-layout__left_width_15"><input class="b-radio__input b-radio__input_top_-3" type="radio" name="poll_vote" id="poll-<?=$item['thread_id']?>_<?=$i?>" value="<?=$answer['id']?>" /></td>
                                        <td class="b-layout__right"><label class="b-radio__label b-radio__label_fontsize_13" for="poll-<?=$item['thread_id']?>_<?=$i++?>"><?=reformat($answer['answer'], 30, 0, 1)?></label></td>
                                    </tr>
                                </table>
                           </div>
						<? } ?>
					</td>
					<? } ?>
				<? } ?>
				</tr>
				<? } ?>
				</table>
				</div>
				<div class="commune-poll-options">
				<? if (!$item['poll_votes'] && $user_id && $item['poll_closed'] != 't') { ?>
                
                    <div class="b-buttons b-buttons_inline-block">
					<span id="poll-btn-vote-<?=$item['thread_id']?>"><a class="b-button b-button_flat b-button_flat_grey" href="javascript: return false;" onclick="poll.vote('Blogs', <?=$item['thread_id']?>); return false;">Ответить</a>&nbsp;&nbsp;&nbsp;</span>
					<span id="poll-btn-result-<?=$item['thread_id']?>"><a class="b-buttons__link b-buttons__link_dot_0f71c8" href="javascript: return false;" onclick="poll.showResult('Blogs', <?=$item['thread_id']?>); return false;">Посмотреть результаты</a>&nbsp;&nbsp;&nbsp;</span>
                    </div>
                
				<? } else { ?>
				<span id="poll-btn-vote-<?=$item['thread_id']?>"></span>
				<span id="poll-btn-result-<?=$item['thread_id']?>"></span>
				<? } ?>
				</div>
			</div>
			<? } ?>
                                        <!-- /Questions -->

                                        <!-- Youtube -->
            <?=(($item['yt_link'])? ("<div style='padding-top: 20px'>".show_video($item['id'], $item['yt_link'])."</div>"):"")?>
                                        <!-- /Youtube -->

                                        <!-- Attach -->
                                        <!-- /Attach -->
									</div>
									<ul class="lo-i">
                                        <?
                                        $post_year = dateFormat('Y', $item['post_time']);
                                        ?>
										<li class="lo-i-c"><a href="/blogs/viewgroup.php?gr=<?=$item['commune_group_id']?>"><?=$item['commune_group_name']?></a></li>
										<li><?=($post_year > 2000 ? dateFormat("d.m.Y H:i", $item['post_time']) : '')?></li>
                                        <li class="lo-i-u"><? print(  __LentaPrntUsrInfo($item,'user_'))?></li>
									</ul>
								</div>
                                    <?
                                        break;
                                }
                                $i++;
                            }
                            ?>

                    <?
                      $allThemesCount = lenta::GetLentaThemesCount($cms);
                    ?>

                    <script type="text/javascript">

                    var isCtrl = false;
                    var isFocus = false;
                    document.onkeydown = function(e) {
                    	if (!e) e = window.event;
                    	var k = e.keyCode;
                    	if (e.ctrlKey && !isFocus) {
                    		if (k == 13) isCtrl=true;
                    		if (document.getElementById ) {
                    			var d;
                    			if (k == 37) {
                    				obj = document.getElementById('pre_navigation_link1');
                    				if (obj && obj.value)
                    				document.location = obj.value;
                    			}
                    			if (k == 39) {
                    				obj = document.getElementById('next_navigation_link1');
                    				if (obj && obj.value)
                    				document.location = obj.value;
                    			}
                    		}
                    	}
                    }
                    </script>

                        <div class="pager">
                        <?
    	                // Страницы
    	                $pages = ceil(($allWorkCount + $allThemesCount) / lenta::MAX_ON_PAGE);
    	                if ($pages > 1){
    	                	$maxpages = $pages;
    	                	$i = 1;
    	                	$sHref = '?page=';

    	                	if ($pages > 32){
    	                		$i = floor($page/10)*10 + 1;
    	                		if ($i >= 10 && $page%10 < 5) $i = $i - 5;
    	                		$maxpages = $i + 22 - floor(log($page,10)-1)*4;
    	                		if ($maxpages > $pages) $maxpages = $pages;
    	                		if ($maxpages - $i + floor(log($page,10)-1)*4 < 22 && $maxpages - 22 > 0) $i = $maxpages - 24 + floor(log($page,10)-1)*3;

    	                	}
    	                	$sBox = '';
    	                	if ($page == 1){
    	                		//$sBox .= '<span class="page-back" id="nav_pre_not_active1">&larr;&nbsp;&nbsp;предыдущая</span>';
    	                	}else {
    	                		$sBox .= "<input type=\"hidden\" id=\"pre_navigation_link1\" value=\"".($sHref.($page-1))."\"/>";
    	                		$sBox .= "<span class=\"page-back\">&larr;&nbsp;&nbsp;<a href=\"".($sHref.($page-1))."\">предыдущая</a></span>";
    	                	}
    	                	$sBox .= '';

    	                	//в начале
    	                	if ($page <= 10) {
    	                		$sBox .= buildNavigation($page, 1, ($pages>10)?($page+4):$pages, $sHref);
    	                		if ($pages > 15) {
    	                			$sBox .= '&nbsp;...';
    	                			//$sBox .= buildNavigation($page, $pages-5, $pages, $sHref);
    	                		}
    	                	}
    	                	//в конце
    	                	elseif ($page >= $pages-10) {
    	                		$sBox .= buildNavigation($page, 1, 5, $sHref);
    	                		$sBox .= '&nbsp;...';
    	                		$sBox .= buildNavigation($page, $page-5, $pages, $sHref);
    	                	}else {
    	                		$sBox .= buildNavigation($page, 1, 5, $sHref);
    	                		$sBox .= '&nbsp;...';
    	                		$sBox .= buildNavigation($page, $page-4, $page+4, $sHref);
    	                		$sBox .= '&nbsp;...';
    	                		//$sBox .= buildNavigation($page, $pages-5, $pages, $sHref);
    	                	}
    	                	/*for ($i; $i <= $maxpages; $i++) {

    	                	if ($i != $page){
    	                	?>
    	                	<td><a href="/blogs/viewgroup.php?<? if ($tag) { ?>tag=<?=$tag?><? } else { ?>gr=<?=($gr.(($base)?"&amp;t=prof":""))?><? } ?><? if ($findw) { ?>&amp;findw=1<? } ?>&amp;page=<?=$i?><?=($ord)?"&amp;ord=$ord":""?>" style="color: #666;"><?=$i?></a></td>
    	                	<? } else { ?>
    	                	<td class="box"><?=$i?></td>

    	                	<? } }
    	                	if ($pages > 25 && $maxpages < $pages-1){ ?>
    	                	<td>...</td>
    	                	<td><a href="/blogs/viewgroup.php?<? if ($tag) { ?>tag=<?=$tag?><? } else { ?>gr=<?=($gr.(($base)?"&amp;t=prof":""))?><? } ?><? if ($findw) { ?>&amp;findw=1<? } ?>&amp;page=<?=($pages - 1)?><?=($ord)?"&amp;ord=$ord":""?>" style="color: #666;"><?=($pages - 1)?></a></td>
    	                	<td><a href="/blogs/viewgroup.php?<? if ($tag) { ?>tag=<?=$tag?><? } else { ?>gr=<?=($gr.(($base)?"&amp;t=prof":""))?><? } ?><? if ($findw) { ?>&amp;findw=1<? } ?>&amp;page=<?=($pages)?><?($ord)?"&amp;ord=$ord":""?>" style="color: #666;"><?=($pages)?></a></td>

    	                	<?        }*/
                        $sBox .= '';
                        if ($page == $pages){
                          //$sBox .= "<span class=\"page-next\" id=\"nav_next_not_active1\">следующая&nbsp;&nbsp;&rarr;</span>";
                        }else {
                          $sBox .= "<input type=\"hidden\" id=\"next_navigation_link1\" value=\"".($sHref.($page+1))."\"/>";
                          $sBox .= "<span class=\"page-next\" id=\"nav_next_not_active1\"><a href=\"".($sHref.($page+1))."\">следующая</a>&nbsp;&nbsp;&rarr;</span>";
                        }
                        $sBox .= '';
                        $sBox .= '';
    	                }
    	                $sBox .= '';
    	                echo $sBox;
    	                        // Страницы закончились?>
    	                  
                    </span>
                    <?php
                	function buildNavigation($iCurrent, $iStart, $iAll, $sHref) {
                		$sNavigation = '';
                		for ($i=$iStart; $i<=$iAll; $i++) {
                			if ($i != $iCurrent) {
                				$sNavigation .= "<a href=\"".$sHref.$i."\" >".$i."</a>&nbsp;";
                			}else {
                				$sNavigation .= '<span class="page">'.$i.'</span>&nbsp;';
                			}
                		}
                		return $sNavigation;
                	}
                    ?>



							</div>
<? } ?>
						</div>
							<div class="page-left b-layout__left b-layout__left_width_25ps">
                                <form id="lentaForm" action="." method="post">
																<div>
                                <input type="hidden" name="action" value="Save" />
                                <? if($lenta) { ?>
                                <input type="hidden" name="has_lenta" value="1" />
                                <? } ?>
								<div class="ls b-layout_bord_c6 b-layout_bordrad_3">
									<div class="ls-in" id="lenta_cats_checkboxes">
										<div class="fo">
											<label for="idCBMyRec">
                                                <input type="checkbox" disabled="disabled" class="i-chk" id="idCBMyRec" name="my_team" <?=($lenta && $lenta['my_team_checked']=='t' ? ' checked="checked"' : '')?> onClick="xajax_Lenta_Save(xajax.getFormValues('lentaForm')); disable_lenta_cats_checkbox();" />
                                                <? if($user_mod & commune::MOD_EMPLOYER) { ?>
                                                    Моя команда
                                                <? } else { ?>
                                                    Избранные мной
                                                <? } ?>
                                            </label>
										</div>
										<ul>
											<li>
												<label><input type="checkbox" disabled="disabled" id="all_cat_lenta_portfolio" class="i-chk" <?=((count($lenta['prof_groups'])>0)?'checked="checked"':'')?> onClick="toggle_all_checkbox('cat_lenta_portfolio'); xajax_Lenta_Save(xajax.getFormValues('lentaForm')); disable_lenta_cats_checkbox();" /><a href="" class="lnk-dot-blue" onClick="toggle_visibility('cat_lenta_portfolio'); return false;">Новые работы</a></label>
												<ul id="cat_lenta_portfolio" style="display: none;">
                                                    <?
                                                    foreach($groups as $grp) {
                                                        if(!$grp['id']) // !!!
                                                            continue;
                                                    ?>
                                                    <li><label for="idCBGrp<?=$grp['id']?>"><input type="checkbox" disabled="disabled" class="i-chk" id="idCBGrp<?=$grp['id']?>" name="prof_group_id[]" value="<?=$grp['id']?>" <?=($lenta && in_array($grp['id'], $lenta['prof_groups']) ? ' checked="checked"' : '')?> onClick="lenta_check_subcats('cat_lenta_portfolio'); xajax_Lenta_Save(xajax.getFormValues('lentaForm')); disable_lenta_cats_checkbox();" /><?=$grp['name']?></label></li>
                                                    <?
                                                    }
                                                    ?>
												</ul>
											</li>
											<li>
												<label><input type="checkbox" disabled="disabled" id="all_cat_lenta_commune" <?=((count($lenta['communes'])>0)?'checked="checked"':'')?> class="i-chk" onClick="toggle_all_checkbox('cat_lenta_commune'); xajax_Lenta_Save(xajax.getFormValues('lentaForm')); disable_lenta_cats_checkbox();" /><a href="" onClick="toggle_visibility('cat_lenta_commune'); return false;" class="lnk-dot-blue">Сообщества</a></label>
												<ul id="cat_lenta_commune" style="display:none;">
                                                    <?
                                                    if(count($communes)) {
                                                        foreach($communes as $comm) {
                                                            if($is_checked = ($lenta && in_array($comm['id'], $lenta['communes'])))
                                                            //$allThemesCount += $comm['themes_count'];
                                                    ?>
                                                            <li><label><input type="checkbox" disabled="disabled" class="i-chk" id="idCBComm<?=$comm['id']?>" name="commune_id[]" value="<?=$comm['id']?>" <?=($is_checked ? ' checked="checked"' : '')?> onClick="lenta_check_subcats('cat_lenta_commune'); xajax_Lenta_Save(xajax.getFormValues('lentaForm')); disable_lenta_cats_checkbox();" /><a href="<?=getFriendlyUrl('commune_commune',$comm['id'])?>"><?=reformat($comm['name'], 27)?></a></label></li>
                                                    <?
                                                        }
                                                    }
                                                    ?>
												</ul>
											</li>
                                            <? if(BLOGS_CLOSED == false) { ?>
											<li>
												<label><input type="checkbox" disabled="disabled" id="all_cat_lenta_blogs" <?=((count($lenta['blog_grs'])>0)?'checked="checked"':'')?> class="i-chk" onClick="toggle_all_checkbox('cat_lenta_blogs'); xajax_Lenta_Save(xajax.getFormValues('lentaForm')); disable_lenta_cats_checkbox();" /><a href="" onClick="toggle_visibility('cat_lenta_blogs'); return false;" class="lnk-dot-blue">Блоги</a></label>
												<ul id="cat_lenta_blogs" style="display:none;">
                                                    <?
                                                    if(count($blog_grs)) {
                                                        foreach($blog_grs as $blog_gr) {
                                                            $love = (int) $blog_gr['id'] == 55;
                                                            if ( $love && !$allow_love ) {
                                                                continue;
                                                            }
                                                            if($is_checked = ($lenta && in_array($blog_gr['id_gr'], $lenta['blog_grs'])))
                                                            //$allThemesCount += $comm['themes_count'];
                                                    ?>
                                                            <li><label><input type="checkbox" disabled="disabled" class="i-chk" id="idCBBlog<?=$blog_gr['id_gr']?>" name="blog_gr_id[]" value="<?=$blog_gr['id_gr']?>" <?=($is_checked ? ' checked="checked"' : '')?> onClick="lenta_check_subcats('cat_lenta_blogs'); xajax_Lenta_Save(xajax.getFormValues('lentaForm')); disable_lenta_cats_checkbox();" /><a href="<?=getFriendlyUrl('blog_group',$blog_gr['id'])?>"><?=$blog_gr['t_name']?></a></label></li>
                                                    <?
                                                        }
                                                    }
                                                    ?>
												</ul>
											</li>
                                            <? } ?>
										</ul>
									</div>
								</div>
                                </div>
																</form>
								<div class="favorites b-layout_bord_c6 b-layout_bordrad_3 b-layout b-layout_margbot_20">
									<div class="bm-in">
										<span class="bm-num" id="lenta_count_favs"><?=count($favs)?></span>
										<h3><a href="javascript: toggle_visibility('lenta_fav_sortby'); toggle_visibility('lenta_fav_list');" class="lnk-dot-blue">Закладки</a></h3>
										<div class="fav-sort " id="lenta_fav_sortby" style="display:none;">
                                            <? if ($favs): ?>
                                                <? if ($_COOKIE['lenta_fav_order'] != ""): ?>
                                                    <script type="text/javascript">
                                                    <!--
                                                    <?
                                                    if ($_COOKIE['lenta_fav_order'] == "priority") $currentOrderStr = 1;
                                                    elseif ($_COOKIE['lenta_fav_order'] == "abc") $currentOrderStr = 2;
                                                    else $currentOrderStr = 0;
                                                    ?>
                                                    order_now = "<?=htmlspecialchars($_COOKIE['lenta_fav_order']);?>";
                                                    currentOrderStr = "<?=$currentOrderStr;?>";
                                                    //-->


                                                    </script>
                                                <?endif;?>
                                            <?endif;?>
                                            <div id="lenta_fav_sort_by" <?=($favs?'':'style="display:none;"')?>>
    											<strong>Сортировка по</strong> 
                                                <div id="fav_order">
                                                    <div id="fav_order_float" style="display:none;position:absolute;top:2px;z-index:10"></div>
                                                    <a href="javascript:void(0)" onclick="ShowFavOrderFloatLenta()"><script type="text/javascript">document.write(fav_orders[order_now])</script>&nbsp;<img src="/images/ico_fav_arrow.gif" alt="" /></a>
                                                </div>
                                            </div>
										</div>
                                        <ul class="fav-list" id="lenta_fav_list" style="display:none;">
                                            <?=($favs?__lentaPrntFavs($favs, $uid):"Нет закладок")?>
                                        </ul>
									</div>
								</div>
								<!-- Banner 240x400 -->
                                <div class="banner_240x400">
                                    <?= printBanner240(false); ?>
                                </div>
								<!-- end of Banner 240x400 -->
							</div>
					</div>
                </div>

  <script type="text/javascript">
     var winSize = $(window).getSize();
	 var spinnerLeft=winSize.x<600?100:271;
	poll.sess = '<?=$_SESSION['rand']?>';

                    var isCtrl = false;
                    var isFocus = false;
                    document.onkeydown = function(e) {
                    	if (!e) e = window.event;
                    	var k = e.keyCode;
                    	if (e.ctrlKey && !isFocus) {
                    		if (k == 13) isCtrl=true;
                    		if (document.getElementById ) {
                    			var d;
                    			if (k == 37) {
                    				obj = document.getElementById('pre_navigation_link1');
                    				if (obj && obj.value) {
                                        spiner.show();
                                        xajax_Lenta_Show(obj.value);
                                    }
//                    				document.location = obj.value;
                    			}
                    			if (k == 39) {
                    				obj = document.getElementById('next_navigation_link1');
                    				if (obj && obj.value) {
                                        spiner.show();
                                        xajax_Lenta_Show(obj.value);
                                    }
//                    				document.location = obj.value;
                    			}
                    		}
                    	}
                    }

         spiner = new Spinner('lenta-cnt', {containerPosition: {position:{x:'left',y:'top'},offset:{x:spinnerLeft,y:100}}});
         spiner.show();

    xajax_Lenta_Show(1);

  </script>

<style type="text/css">.b-icon__ver{ position:relative; top:1px;} .b-icon__pro{top:4px;}</style>

