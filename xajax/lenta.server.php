<?
$rpath = "../";
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/lenta.common.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/lenta.php");

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/commune.php");

function Lenta_Show($page=1) {
    global $DB;
	session_start();
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/portfolio.php");
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/commune.php");
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/links.php");
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/lenta.php");
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/blogs.php");
    /*require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );
    
$stop_words = new stop_words( false );*/

$yt_replace_id = array();
$yt_replace_data = array();

    $uid = get_uid(false);
    $objResponse = new xajaxResponse();
    ob_start();

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
           && $uStatus['is_blocked_commune'] == 0
           && ($uStatus['is_accepted']
               || $uStatus['is_author']))
          $cms .= (!$i++?'':',').$cm_id;
      }
    }

  }

  //if($cms)
  //  $allThemesCount -= commune::GetMyThemesCount($cms, $uid);


  if($lenta && $lenta['all_profs_checked']=='t' || $cms || $pgs || $blg) {
    $items = lenta::GetLentaItems($uid,
                                  ($lenta && $lenta['my_team_checked']=='t'),
                                  ($lenta && $lenta['all_profs_checked']=='t'),
                                  $pgs,
                                  $cms,
                                  ($page-1) * lenta::MAX_ON_PAGE, lenta::MAX_ON_PAGE, $allWorkCount,
                                  $blg
                                  );
  }


  if(!$items)
    $items = array();
    
//    var_dump($favs);

  $stars = array(0=>'bsg.png', 1=>'bsgr.png', 2=>'bsy.png', 3=>'bsr.png');



                            $i=0;
                            foreach($items as $item) {
                                switch ($item['item_type']) {
                                    case '2':
                                    // Сообщества
                                    $top = $item;
                                    $user_mod = $user_comm_mods[$top['commune_id']];
                                    if( ($top['member_is_banned'] && !($user_mod & (commune::MOD_ADMIN | commune::MOD_COMM_AUTHOR | commune::MOD_COMM_MANAGER)))
                                            || ($top['is_private'] == 't' && $top['user_id']!=$uid && !($user_mod & (commune::MOD_ADMIN | commune::MOD_COMM_AUTHOR | commune::MOD_COMM_MANAGER)))
                                            || ($top['is_blocked'] && $top['commune_author_id']!=$uid) )
                                            { continue; }
                                            
                                    $aThemeId = ( is_array($top['theme_id']) ) ? $top['theme_id'] : array($top['theme_id']);
                        			$top['answers'] = $DB->rows("SELECT * FROM commune_poll_answers WHERE theme_id IN (?l) ORDER BY id", array($top['theme_id']) );
                                    $GLOBALS[LINK_INSTANCE_NAME] = new links('commune');
                                    $user_id = $uid;
                                    $mod = $user_mod;
                                    $is_member = $mod & (commune::MOD_ADMIN | commune::MOD_COMM_ACCEPTED | commune::MOD_COMM_AUTHOR);
                                    $is_moder  = $mod & (commune::MOD_ADMIN | commune::MOD_MODER | commune::MOD_COMM_MODERATOR | commune::MOD_COMM_AUTHOR);

                                    $commune_info = commune::getCommuneInfoByMsgID($top['id']);
                            ?>
                                <style>
.lo-m .lo-i-my-d .ac-pro, .lo-m .lo-i-my-d .ac-epro {
margin-right: 0px;
}
.lo .utxt .b-layout__txt .b-icon__lprofi{ vertical-align:baseline !important; top:2px !important;}
.lo .utxt>.b-pic{ margin-right:10px !important;}
</style>
								<div class="lo lo-m" id='idTop_<?=$top['id']?>' style='margin-bottom:0px !important;'>
									<ul class="lo-p">
										<li class="lo-s">
											<div class="b-layout_bordrad_3">
												<a href="/commune" class="lnk-dot-666">Сообщества</a>
											</div>
										</li>
										<li class="post-f-fav">
                                            <? $msg_id = $top['id'];?>
                                            <? if($favs['CM'.$msg_id]): ?> 
                                                <img src="/images/bookmarks/<?=$stars[$favs['CM'.$msg_id]['priority']]?>" alt="" on="<?=($is_fav ? 1 : 0)?>" id="favstar<?='CM'.$msg_id?>" <?if($uid):?>onclick="ShowFavFloatLenta(<?=$msg_id?>, <?=$uid?>, 'CM')" ><?endif;?>
                                            <? else: ?>
                                                <img src="/images/bookmarks/bsw.png" alt="" on="<?=($is_fav ? 1 : 0)?>" id="favstar<?='CM'.$msg_id?>" <?if($uid):?>onclick="ShowFavFloatLenta(<?=$msg_id?>, <?=$uid?>, 'CM')" ><?endif;?>
                                            <? endif; ?>
                                            <ul class="post-f-fav-sel" style="display:none;" id="FavFloat<?=$msg_id?>"></ul>
										</li>
									</ul>
									<div class="utxt">
<? print(  __LentaPrntUsrInfo($top,'user_','','',false,true))?>
										<h3>
                                        <?php if($top['is_private'] == 't') { ?>
                                        <img src="/images/icons/eye-hidden.png" alt="Скрытый пост" title="Скрытый пост">&nbsp;	            
                                        <?php }//if?>
                                        <?php $sTitle   = /*$top['moderator_status'] === '0' ? $stop_words->replace($top['title']) :*/ $top['title']; ?>
                                        <?php $sMessage = /*$top['moderator_status'] === '0' ? $stop_words->replace($top['msgtext']) :*/ $top['msgtext']; ?>
                                        <a href="<?=getFriendlyURL('commune', $top['id'])?>?om=<?=commune::OM_TH_NEW?>"><?=reformat2($sTitle, 30, 0, 1)?></a>&nbsp;</h3>
										<p><?=reformat2($sMessage, 46, 1,0,1);?></p>

                                        <!-- Questions -->
			<? if ($top['question'] != '') { ?>
			<div id="poll-<?=$top['theme_id']?>" class="commune-poll">
				<div class="commune-poll-theme"><?=reformat($top['question'], 43, 0, 1)?></div>
				<div id="poll-answers-<?=$top['theme_id']?>">
				<? if ($top['poll_closed'] == 't') { ?><table class="b-layout__table b-layout__table_width_full"><? } ?>
                <? // если надо вывести только количество ответов
                $showAnswers = $top['poll_votes'] || !$user_id || $top['commune_blocked'] == 't' || $top['user_is_banned'] || $top['member_is_banned'] || !$is_member;
                if ($showAnswers) { ?><table class="poll-variants"><? } ?>
				<?
				$i=0;
				$max = 0;
				if ($top['poll_closed'] == 't') {


					foreach ($top['answers'] as $answer) $max = max($max, $answer['votes']);
				}
				foreach ($top['answers'] as $answer) {
				?>

						<? if ($top['poll_closed'] == 't') { ?>
                        	<tr class="b-layout__tr">
                              <td class="b-layout__left b-layout__left_width_50"><label class="b-layout__txt" for="poll_<?=$i?>"><?=reformat($answer['answer'], 30, 0, 1)?></label></td>
                              <td class="b-layout__middle b-layout__middle_width_30 b-layout__middle_center"><?=$answer['votes']?></td>
                              <td class=" b-layout__right "><div class=" res-line rl1" style="width: <?=($max? round(((100 * $answer['votes']) / $max) * 3): 0)?>px;"></div></td>
                            </tr>
                        <? } else { ?>
                            <? if ($showAnswers) { ?>
                                <tr>
                                    <td class="bp-gres"><?= $answer['votes'] ?></td>
                                    <td>
                                        <label><?= $answer['answer'] ?></label>
                                    </td>
                                </tr>
                            <? } else { ?>
                                <? if ($top['poll_multiple'] == 't') { ?>
                                <div class="b-check b-check_padbot_10">
                                    <input id="poll-<?=$top['theme_id']?>_<?=$i?>" class="b-check__input" type="checkbox" name="poll_vote[]" value="<?=$answer['id']?>" />
                                    <label class="b-check__label b-check__label_fontsize_13" for="poll-<?=$top['theme_id']?>_<?=$i++?>"><?=reformat($answer['answer'], 30, 0, 1)?></label>
                                </div>
                                <? } else { ?>
                                <div class="b-radio__item  b-radio__item_padbot_5">
                                    <table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
                                        <tr class="b-layout__tr">
                                            <td class="b-layout__left b-layout__left_width_15"><input id="poll-<?=$top['theme_id']?>_<?=$i?>" class="b-radio__input b-radio__input_top_-3" type="radio" name="poll_vote" value="<?=$answer['id']?>" /></td>
                                            <td class="b-layout__right"><label class="b-radio__label b-radio__label_fontsize_13" for="poll-<?=$top['theme_id']?>_<?=$i++?>"><?=reformat($answer['answer'], 30, 0, 1)?></label></td>
                                        </tr>
                                    </table>
                                </div>
                                <? } ?>
                            <? } ?>
                            
                        <? } ?>

				<? } ?>
                <? if ($showAnswers) { ?></table><? } ?>
				<? if ($top['poll_closed'] == 't') { ?></table><? } ?>
				</div>
				<? if (!$top['poll_votes'] && $user_id && $top['poll_closed'] != 't' && $top['commune_blocked'] != 't' && !$top['user_is_banned'] && !$top['member_is_banned'] && $is_member) { ?>
                
                <div class="b-buttons b-buttons_inline-block">
                    <span id="poll-btn-vote-<?=$top['theme_id']?>">
                        <a class="b-button b-button_flat b-button_flat_grey" href="javascript: return false;" onclick="poll.vote('Commune', <?=$top['theme_id']?>); return false;">Ответить</a>&nbsp;&nbsp;&nbsp;                
                    </span>                
					<span id="poll-btn-result-<?=$top['theme_id']?>" ><a class="b-buttons__link b-buttons__link_dot_0f71c8" href="javascript: return false;" onclick="poll.showResult('Commune', <?=$top['theme_id']?>); return false;">Посмотреть результаты</a></span>
                </div>
				<? } else { ?>
				<span id="poll-btn-vote-<?=$top['theme_id']?>"></span>
				<span id="poll-btn-result-<?=$top['theme_id']?>"></span>
				<? } ?>
			</div>
            <br />
			<? } ?>
                                        <!-- /Questions -->

                                        <!-- Youtube -->
<?
if($top['yt_link']) {
    $tmp_yt_id = $top['id'].'ytlink'.mt_rand(1,1000000);
    $tmp_yt_data = show_video($top['id'], $top['yt_link']);
    array_push($yt_replace_id, '/'.$tmp_yt_id.'/');
    array_push($yt_replace_data, $tmp_yt_data);
    echo "<div style='padding-top: 20px'>".$tmp_yt_id."</div><br/>";
}
?>


<?
//            (($top['youtube_link'])? ("<div style='padding-top: 20px'>".show_video($top['id'], $top['youtube_link'])."</div><br/>"):"")
?>

                                        <!-- /Youtube -->

                                        <!-- Attach -->
<?
                                        if ($top['attach']) {
                                            $attach = $top['attach'][0];
                                            if ($attach['fname']) {
                                                $att_ext = strtolower(CFile::getext($attach['fname']));
                                                if ($att_ext == "swf") {
                                                    print("<br/>".viewattachExternal($top['user_login'], $attach['fname'], "upload", "/blogs/view_attach.php?user=".$top['user_login']."&attach=".$attach['fname'])."<br/>");
                                                } elseif($att_ext == 'flv') {
                                                    print("<br/>".viewattachLeft($top['user_login'], $attach['fname'], "upload", $file, 1000, 470, 307200, true, (($attach['small']=='t')?1:0))."<br/>");
                                                } else {
                                                    print("<br/>".viewattachLeft($top['user_login'], $attach['fname'], "upload", $file, 1000, 470, 307200, !($attach['small']=='t'), (($attach['small']=='t')?1:0))."<br/>");
                                                }
                                            }
                                            echo '<br/>';
                                            if (sizeof($top['attach']) > 1)
                                            {
                                                echo "<a href=\"".getFriendlyURL('commune', $top['id'])."\"><b>".blogs::ShowMoreAttaches(sizeof($top['attach']))."</b></a><br/><br/>";

                                            }
                                        }
?>

                                        <!-- /Attach -->


									</div>
									<ul class="lo-i">
                                        <?
                                        $post_year = dateFormat('Y', $top['post_time']);
                                        ?>
                                        <li class="lo-i-cm">
                                            <a href="<?=getFriendlyURL('commune', $top['id'])?>" <?=($top['current_count']==NULL && intval($top['count_comments'])!=0 ? 'style="font-weight:bold;"' : '')?>><?=($top['closed_comments']=='t'?"Комментирование закрыто":"Комментарии (".intval($top['count_comments']).")")?></a>
                                            <? if($top['closed_comments']=='f') {
                                                $top['current_count'] = $top['current_count'] == '' ? $top['a_count']-1 : $top['current_count'];
                                                if($top['a_count'] > 1)
                                                $unread = ($top['a_count']-1) - $top['current_count'];
                                                if($unread > 0) {
                                                ?>
                                                <a href="<?=getFriendlyURL('commune', $top['id'])?>#unread" style="color:#6BA813; font-weight:bold;">(<?=$unread?> <?=(($unread==1)?"новый":"новых")?>)</a>
                                                <?
                                                }
                                                $unread = 0;
                                            } ?>
                                        </li>
										<li class="lo-i-c"><a href="/commune/?id=<?=$top['commune_id']?>"><?=$top['commune_name']?></a>, <a href="/commune/?gr=<?=$top['commune_group_id']?>"><?=$top['commune_group_name']?></a></li>
										<li><?=($post_year > 2000 ? dateFormat("d.m.Y H:i", $top['post_time']) : '')?></li>
									</ul>
								</div>
                                <br>

                            <?
                                    break;
                                    case '1':
                                      // Портфолио
                                      $work = $item;
                                      if ($work['work_is_blocked']) {
                                           continue;
                                      }
                                      $is_fav = (isset($favs['PF'.$work['portfolio_id']]) ? 1 : 0);
                                      $msg_id = $work['portfolio_id'];
                            ?>
								<div class="lo lo-m" style='margin-bottom:0px !important;'>
									<ul class="lo-p">
										<li class="lo-s">
											<div class="b-layout_bordrad_3">
												<a href="/portfolio" class="lnk-dot-666">Работы</a>
											</div>
										</li>
										<li class="post-f-fav">
                                            <? if($favs['PF'.$msg_id]): ?> 
                                                <img src="/images/bookmarks/<?=$stars[$favs['PF'.$msg_id]['priority']]?>" alt="" on="<?=($is_fav ? 1 : 0)?>" id="favstar<?='PF'.$msg_id?>" <?if($uid):?>onclick="ShowFavFloatLenta(<?=$msg_id?>, <?=$uid?>, 'PF')" ><?endif;?>
                                            <? else: ?>
                                                <img src="/images/bookmarks/bsw.png" alt="" on="<?=($is_fav ? 1 : 0)?>" id="favstar<?='PF'.$msg_id?>" <?if($uid):?>onclick="ShowFavFloatLenta(<?=$msg_id?>, <?=$uid?>, 'PF')" ><?endif;?>
                                            <? endif; ?>
                                            <ul class="post-f-fav-sel" style="display:none;" id="FavFloat<?=$msg_id?>"></ul>
										</li>
									</ul>
									<div class="utxt">
                                        <? print(  __LentaPrntUsrInfo($work,'user_','','',false,true))?>
                                        <?php $sTitle = /*$work['moderator_status'] === '0' ? $stop_words->replace($work['name']) :*/ $work['name']; ?>
										<h3><a href="/users/<?=$work['user_login']?>/viewproj.php?prjid=<?=$work['portfolio_id']?>"><?=reformat2($sTitle, 40, 0, 1)?></a>&nbsp;</h3>
                                        <?php
                                        $is_preview = ($work['pict'] || $work['prev_pict']);
                                        if($is_preview && $work['prev_type']!=1) {
                                            echo view_preview($work['user_login'], $work['prev_pict'], "upload", $align, true, true, '', 200)."<br/><br/>";
                                        }
                                        close_tags($work['descr'],array('b', 'i'));
                                        $sDescr = /*$work['moderator_status'] === '0' ? $stop_words->replace($work['descr']) :*/ $work['descr'];
                                        ?>
										<p><?=reformat($sDescr, 80, 0, 0, 1)?></p>



									</div>
									<ul class="lo-i">
                                        <?
                                        $post_year = dateFormat('Y', $work['post_time']);
                                        ?>
										<li class="lo-i-c"><a href="/freelancers/?prof=<?=$work['prof_id']?>"><?=$work['prof_name']?></a></li>
										<li><?=($post_year > 2000 ? dateFormat("d.m.Y H:i", $work['post_time']) : '')?></li>
									</ul>
								</div>
                                <br>
                            <?
                                break;

                            case '4':
                            // Блоги
                                    $item['thread_id'] = $item['theme_id'];
									$item['answers'] = $DB->rows("SELECT * FROM blogs_poll_answers WHERE thread_id IN (?l) ORDER BY id", array($item['thread_id']));
                                    $GLOBALS[LINK_INSTANCE_NAME] = new links('blogs');
                                    $user_id = $uid;
                                    ?>
								<div class="lo lo-m" id='idBlog_<?=$item['thread_id']?>' style='margin-bottom:0px !important;'>
									<ul class="lo-p">
										<li class="lo-s">
											<div class="b-layout_bordrad_3">
												<a href="/blogs" class="lnk-dot-666">Блоги</a>
											</div>
										</li>
										<li class="post-f-fav">
                                            <? $msg_id = $item['theme_id'];?>
                                            <? if($favs['BL'.$msg_id]): ?> 
                                                <img src="/images/bookmarks/<?=$stars[$favs['BL'.$msg_id]['priority']]?>" alt="" on="<?=($is_fav ? 1 : 0)?>" id="favstar<?='BL'.$msg_id?>" <?if($uid):?>onclick="ShowFavFloatLenta(<?=$msg_id?>, <?=$uid?>, 'BL')" ><?endif;?>
                                            <? else: ?>
                                                <img src="/images/bookmarks/bsw.png" alt="" on="<?=($is_fav ? 1 : 0)?>" id="favstar<?='BL'.$msg_id?>" <?if($uid):?>onclick="ShowFavFloatLenta(<?=$msg_id?>, <?=$uid?>, 'BL')" ><?endif;?>
                                            <? endif; ?>
                                            <ul class="post-f-fav-sel" style="display:none;" id="FavFloat<?=$msg_id?>"></ul>
										</li>
									</ul>
									<div class="utxt">
                                        <? print(  __LentaPrntUsrInfo($item,'user_','','',false,true))?>
                                        <?php $sTitle   = /*$item['moderator_status'] === '0' ? $stop_words->replace($item['title']) :*/ $item['title']; ?>
                                        <?php $sMessage = /*$item['moderator_status'] === '0' ? $stop_words->replace($item['msgtext']) :*/ $item['msgtext']; ?>
										<h3><a href="<?=getFriendlyURL("blog", $item['theme_id'])?>"><?=reformat2($sTitle, 30, 0, 1)?></a>&nbsp;</h3>
										<p><?=reformat($sMessage, 46, 1,-($item['is_chuck']=='t'),1);?></p>

                                        <!-- Questions -->
			<? if ($item['question'] != '') { ?>
			<div id="poll-<?=$item['thread_id']?>" class="poll">
				<div class="commune-poll-theme"><?=reformat($item['question'], 43, 0, 1)?></div>
				<div id="poll-answers-<?=$item['thread_id']?>">
				<? if ($item['poll_multiple'] != 't') { ?><div class="b-radio b-radio_layout_vertical"><? } ?>
				<? if ($item['poll_closed'] == 't') { ?><table class="b-layout__table b-layout__table_width_full"><? } ?>
				<?
				$i=0;
				$max = 0;
				if ($item['poll_closed'] == 't') {
					foreach ($item['answers'] as $answer) $max = max($max, $answer['votes']);
				}
				foreach ($item['answers'] as $answer) {
				?>
				
				<? if ($item['poll_closed'] == 't') { ?>
                    <tr class="b-layout__tr">
                      <td class="b-layout__left b-layout__left_width_50"><label class="b-layout__txt" for="poll_<?=$i?>"><?=reformat($answer['answer'], 30, 0, 1)?></label></td>
                      <td class="b-layout__middle b-layout__middle_width_30 b-layout__middle_center"><?=$answer['votes']?></td>
                      <td class=" b-layout__right "><div class=" res-line rl1" style="width: <?=($max? round(((100 * $answer['votes']) / $max) * 3): 0)?>px;"></div></td>
                    </tr>
				<? } else { ?>
					<? if ($item['poll_votes'] || !$user_id) { ?>
						<div class="bp-gres"><?=$answer['votes']?></div>
					<? } else { ?>
						
						<? if ($item['poll_multiple'] == 't') { ?>
                        	<div class="b-check b-check_padbot_10">
								<input id="poll-<?=$item['thread_id']?>_<?=$i?>" class="b-check__input" type="checkbox" name="poll_vote[]" value="<?=$answer['id']?>" />
                                <label class="b-check__label b-check__label_fontsize_13" for="poll-<?=$item['thread_id']?>_<?=$i++?>"><?=reformat($answer['answer'], 30, 0, 1)?></label>
                            </div>
						<? } else { ?>
                        	<div class="b-radio__item  b-radio__item_padbot_5">
                            	<table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
                                	<tr class="b-layout__tr">
                                    	<td class="b-layout__left b-layout__left_width_15"><input id="poll-<?=$item['thread_id']?>_<?=$i?>" class="b-radio__input b-radio__input_top_-3" type="radio" name="poll_vote" value="<?=$answer['id']?>" /></td>
                                        <td class="b-layout__right"><label class="b-radio__label b-radio__label_fontsize_13" for="poll-<?=$item['thread_id']?>_<?=$i++?>"><?=reformat($answer['answer'], 30, 0, 1)?></label></td>
                                    </tr>
                                </table>
                            </div>
						<? } ?>
					<? } ?>
					
				<? } ?>
				
				<? } ?>
				<? if ($item['poll_closed'] == 't') { ?></table><? } ?>
				<? if ($item['poll_multiple'] != 't') { ?></div><? } ?>
				</div>
				<? if (!$item['poll_votes'] && $user_id && $item['poll_closed'] != 't') { ?>
                <div class="b-buttons b-buttons_inline-block">
                    <span id="poll-btn-vote-<?=$item['thread_id']?>">
                        <a class="b-button b-button_flat b-button_flat_grey" href="javascript: return false;"  onclick="poll.vote('Blogs', <?=$item['thread_id']?>); return false;">Ответить</a>                
                        &nbsp;&nbsp;&nbsp;
                    </span>                
                    <span id="poll-btn-result-<?=$item['thread_id']?>"><a class="b-buttons__link b-buttons__link_dot_0f71c8" href="javascript: return false;" onclick="poll.showResult('Blogs', <?=$item['thread_id']?>); return false;">Посмотреть результаты</a>&nbsp;&nbsp;&nbsp;</span>
                </div>
				<? } else { ?>
				<span id="poll-btn-vote-<?=$item['thread_id']?>"></span>
				<span id="poll-btn-result-<?=$item['thread_id']?>"></span>
				<? } ?>
			</div>
            <br/>
			<? } ?>
                                        <!-- /Questions -->

                                        <!-- Youtube -->
<?
if($item['yt_link']) {
    $tmp_yt_id = $item['id'].'ytlink'.rand(1,1000000);
    $tmp_yt_data = show_video($item['id'], $item['yt_link']);
    array_push($yt_replace_id, '/'.$tmp_yt_id.'/');
    array_push($yt_replace_data, $tmp_yt_data);
    echo "<div style='padding-top: 20px'>".$tmp_yt_id."</div><br/>";
}
?>
<?
//            =(($item['yt_link'])? ("<div style='padding-top: 20px'>".show_video($item['id'], $item['yt_link'])."</div>"):"")
?>
                                        <!-- /Youtube -->

                                        <!-- Attach -->
<?
                                        if ($item['attach']) {
                                            $attach = $item['attach'][0];
                                            if ($attach['fname']) {
                                                $att_ext = strtolower(CFile::getext($attach['fname']));
                                                if ($att_ext == "swf") {
                                                    print("<br/>".viewattachExternal($item['user_login'], $attach['fname'], "upload", "/blogs/view_attach.php?user=".$item['user_username']."&attach=".$attach['fname'])."<br/>");
                                                } elseif($att_ext == 'flv') {
                                                    print("<br/>".viewattachLeft($item['user_login'], $attach['fname'], "upload", $file, 1000, 470, 307200, true, (($attach['small']==2)?1:0))."<br/>");
                                                } else {
                                                    print("<br/>".viewattachLeft($item['user_login'], $attach['fname'], "upload", $file, 1000, 470, 307200, !$attach['small'], (($attach['small']==2)?1:0))."<br/>");
                                                }
                                            }
                                            echo '<br/>';
                                            if (sizeof($item['attach']) > 1)
                                            {
                                                echo "<a href=\"".getFriendlyURL("blog", $item['theme_id'])."\"><b>".blogs::ShowMoreAttaches(sizeof($item['attach']))."</b></a><br/><br/>";
                                            }
                                        }
?>

                                        <!-- /Attach -->
									</div>
									<ul class="lo-i">
                                        <?
                                        $post_year = dateFormat('Y', $item['post_time']);
                                        ?>
                                        <li class="lo-i-cm">
                                            <a href="<?=getFriendlyURL("blog", $item['theme_id'])?>" <?=($item['current_count']==NULL && intval($item['count_comments'])!=0 ? 'style="font-weight:bold;"' : '')?>><?=($item['closed_comments']=='t'?"Комментирование закрыто":"Комментарии (".intval($item['count_comments']).")")?></a>
                                            <?php
                                            if (isset($item['status_comments']) && $item['count_comments'] > 0 && $item['status_comments'] < $item['count_comments'] && $item['status_comments'] != -100 && $item['closed_comments']=='f')
                                            {
                                              $new_comments_num = $item['count_comments'] - $item['status_comments'];
                                              ?>
                                              <a href="<?=getFriendlyURL("blog", $item['theme_id'])?>#unread" style="color:#6BA813; font-weight:bold;">(<?=$new_comments_num?> <?=(($new_comments_num==1)?"новый":"новых")?>)</a>
                                              <?
                                            } else if(isset($item['status_comments']) && $item['count_comments'] > 0 && $item['status_comments'] < $item['count_comments'] && $item['status_comments'] == -100 && $item['closed_comments']=='f') {
                                                $new_comments_num = $item['count_comments'];
                                              ?>
                                              <a href="<?=getFriendlyURL("blog", $item['theme_id'])?>#unread" style="color:#6BA813; font-weight:bold;">(<?=$new_comments_num?> <?=(($new_comments_num==1)?"новый":"новых")?>)</a>
                                              <?    
                                            }
                                            ?>
                                        </li>
										<li class="lo-i-c"><a href="<?=getFriendlyURL("blog_group", $item['commune_group_id'])?>"><?=$item['commune_group_name']?></a></li>
										<li><?=($post_year > 2000 ? dateFormat("d.m.Y H:i", $item['post_time']) : '')?></li>
									</ul>
								</div>
                                <br>
                                    <?
                                        break;
                                }
                                $i++;
                            }
                            ?>

                    <?
//                      $allThemesCount = lenta::GetLentaThemesCount($cms);
                    ?>


<br/>
                        <?

    	                // Страницы

                        $count = 4;
    	                $pages = ceil(($allWorkCount + $allThemesCount) / lenta::MAX_ON_PAGE);
    	                
        $html = '<div class="b-pager" >';
        
        if (is_array($count)) {
            list($scount, $ecount) = $count;
        } else {
            $scount = $ecount = $count;
        }
        if($pages > 1){
            $start = $page - $scount;
            if($start<1) $start = 1;

            $end = $page + $ecount;
            if($end>$pages) $end = $pages;
        
            
            $html .= '<ul class="b-pager__back-next">';
            if($page < $pages) {
                $html .= "<input type=\"hidden\" id=\"next_navigation_link1\" value=\"".(($page+1))."\">";
                $html .= '<li class="b-pager__next" id="nav_next_not_active1"><a class="b-pager__link" href="javascript:void(0)" onClick="document.location.href=\'#lentatop\'; spiner.show(); xajax_Lenta_Show('.(($page+1)).'); return false;" id="PrevLink"></a>&nbsp;&nbsp;</li>';
            } 
            if($page > 1) {
                $html .= "<input type=\"hidden\" id=\"pre_navigation_link1\" value=\"".(($page-1))."\">";
                $html .= '<li class="b-pager__back">&nbsp;&nbsp;<a id="NextLink" class="b-pager__link" href="javascript:void(0)" onClick="document.location.href=\'#lentatop\'; spiner.show(); xajax_Lenta_Show('.(($page-1)).'); return false;"></a></li>';
            } 
            $html .= '</ul>';
            $html .= '<ul class="b-pager__list">';
            for($i=$start;$i<=$end;$i++) {
                if($i == $start && $start > 1) {  
                    $html .= '<li class="b-pager__item"><a class="b-pager__link" href="javascript:void(0)" onClick="document.location.href=\'#lentatop\'; spiner.show(); xajax_Lenta_Show(1); return false;">1</a></li>';  
                    if($i==3) $html .= '<li class="b-pager__item"><a class="b-pager__link" href="javascript:void(0)" onClick="document.location.href=\'#lentatop\'; spiner.show(); xajax_Lenta_Show(2); return false;">2</a></li>'; 
                    elseif($i!=2) $html .= "<li class='b-pager__item'>&hellip;</li>";
                }
                $html .= ( $page == $i ? '<li class="b-pager__item b-pager__item_active"><span class="b-pager__b1"><span class="b-pager__b2">'.$i.'</span></span></li>' : '<li class="b-pager__item"><a class="b-pager__link" href="javascript:void(0)" onClick="document.location.href=\'#lentatop\'; spiner.show(); xajax_Lenta_Show('.$i.'); return false;">'.$i.'</a></li>' );
                if($i == $end && ($pages-1) > $end) { 
                    $html .= '<li class="b-pager__item">&hellip;</li>';
                }
            }
            $html .= '</ul>';
        }
        echo $html.'</div>';
    	                        // Страницы закончились?>
    	                  
          
<?


    $content = ob_get_contents();
    ob_end_clean();
    $content_js = '';
    if($yt_replace_data) {
        foreach($yt_replace_data as $key=>$value) {
            $yt_replace_data[$key] = preg_replace("/^(.*)<script.*$/sm","$1",$value);
            $content_js .= preg_replace("/^(.*<script type='text\/javascript'>)(.*)(<\/script>)$/sm","$2",$value);
        }
        $content = preg_replace($yt_replace_id, $yt_replace_data, $content);
        $objResponse->script($content_js);
    }

    $objResponse->assign('lenta-cnt', 'innerHTML', $content);
    $objResponse->script($content_js);
    $objResponse->script('spiner.hide();');
    $objResponse->script('$$("#lenta_cats_checkboxes input[type=checkbox]").each(function(el) { el.set("disabled", false); });');
    $objResponse->script('fix_banner();');
    return $objResponse;
}

                	function buildNavigation($iCurrent, $iStart, $iAll, $sHref) {
                		$sNavigation = '';
                		for ($i=$iStart; $i<=$iAll; $i++) {
                			if ($i != $iCurrent) {
                				$sNavigation .= "<a href=\"\" onClick=\"document.location.href='#lentatop'; spiner.show(); xajax_Lenta_Show($i); return false;\" >".$i."</a>&nbsp;";
                			}else {
                				$sNavigation .= '<span class="page"><span><span>'.$i.'</span></span></span>&nbsp;';
                			}
                		}
                		return $sNavigation;
                	}

function Lenta_Save($data) {
    session_start();
    $objResponse = new xajaxResponse();
    $uid = get_uid(false);
    if($data['commune_id']) {
        foreach($data['commune_id'] as $k=>$v) {
            if(is_array($v)) { unset($data['commune_id'][$k]); }
        }
    }
    if($data['prof_group_id']) {
        foreach($data['prof_group_id'] as $k=>$v) {
            if(is_array($v)) { unset($data['prof_group_id'][$k]); }
        }
    }
    
    if($data['blog_gr_id']) {
        foreach($data['blog_gr_id'] as $k=>$v) {
            if(is_array($v)) { unset($data['blog_gr_id'][$k]); }
        }
    }

    if(($uid = get_uid(false))) {
      $_POST = $data;
      $has_lenta   = __paramInit('bool', NULL, 'has_lenta', NULL);
      $my_team     = __paramInit('bool', NULL, 'my_team');
      $all_profs   = __paramInit('bool', NULL, 'all_profs');
      $communes    = __paramInit('array', NULL, 'commune_id');
      $prof_groups = __paramInit('array', NULL, 'prof_group_id');
      $blog_groups = __paramInit('array', NULL, 'blog_gr_id');
      lenta::SaveUserSettings($has_lenta, $uid, $my_team, $all_profs, $communes, $prof_groups, $blog_groups);
    }
$objResponse->script('spiner.show(); xajax_Lenta_Show(1);');
    $objResponse->script('fix_banner();');
    return $objResponse;
}

function Lenta_AddFav($fav_id, $pfx, $user_id, $undo=0, $priority=0) {
    $stars = array(0=>'bsg.png', 1=>'bsgr.png', 2=>'bsy.png', 3=>'bsr.png');
    $objResponse = new xajaxResponse();
    $star = "favstar{$pfx}{$fav_id}";
    $li   = "fav{$pfx}{$fav_id}";
    
    $commune_message_id = ($pfx=='CM' ? $fav_id : NULL);
    $portfolio_id = ($pfx=='PF' ? $fav_id : NULL);
    $blog_id = ($pfx=='BL' ? $fav_id : NULL);
    
    if(lenta::AddFav($user_id, $commune_message_id, $portfolio_id, $blog_id, $undo, $priority)) {
        if(!$undo) {
            $objResponse->assign($star, "src", '/images/bookmarks/'.$stars[$priority]);
            $sort = $_COOKIE['lenta_fav_order']!=""?$_COOKIE['lenta_fav_order']:"date"; 
            $favs = lenta::GetFavorites($user_id, $sort);
            $objResponse->assign('lenta_fav_list', 'innerHTML', __lentaPrntFavs($favs, $user_id));
            $objResponse->script("{$star}.setAttribute('on',1);");
            $objResponse->script("$('lenta_count_favs').set('html',".count($favs).")");
            $objResponse->script('$("lenta_fav_sort_by").setStyle("display","block")');
        } else {
            $objResponse->assign($star, "src", '/images/bookmarks/bsw.png');
            $objResponse->remove($li);
            $objResponse->script("$('lenta_count_favs').set('html',parseInt($('lenta_count_favs').get('html'))-1)");
            $objResponse->script( "
                {$star}.setAttribute('on',0);
                favBlock = $('lenta_fav_list');
                if(favBlock) {
                    if(favBlock.innerHTML.match(/<LI[^>]*>/i)==null) {
                      $('lenta_fav_sort_by').setStyle('display','none');
                      favBlock.innerHTML = 'Нет закладок';
                    }
                }
              ");
        }
    }
    
    return $objResponse;
}

/**
 * Сортируем закладки
 *
 */
function Lenta_SortFav($sort="date", $om) {
    global $session;
    
    session_start();
    setcookie("lenta_fav_order", $sort, time()+60*60*24*365, "/");
    $user_id = get_uid(false);  
    $objResponse = new xajaxResponse();
    $favs = lenta::GetFavorites($user_id, $sort);
   
    $objResponse->assign('lenta_fav_list', 'innerHTML', ''.__lentaPrntFavs($favs, $user_id));
    
    return $objResponse;
}

function Lenta_EditFav($msg_id, $priority = 0, $title = "", $action = "edit", $pfx="CM") {
	global $session, $stars;
	session_start();
	$user_id = $_SESSION['uid'];
	$objResponse = new xajaxResponse();

	$msg_id = intval($msg_id);
	$GLOBALS['xajax']->setCharEncoding("windows-1251");

	$action = trim($action);
    
	switch ($action) {
		case "update":
	  		//$title     = pg_escape_string(substr($title, 0, 128));
            $title     = substr($title, 0, 128);
            switch($pfx) {
                case 'CM':
                    $updatefav = lenta::AddFav($user_id, $msg_id, 0, 0, 0, $priority, $title);
                    $fav_href = "/commune/?id={$editfav['commune_id']}&site=Topic&post={$msg_id}";
                    break;
                case 'PF':
                    $updatefav = lenta::AddFav($user_id, 0, $msg_id, 0, 0, $priority, $title);
                    $fav_href = "/users/{$editfav['login']}/viewproj.php?prjid={$msg_id}";
                    break;
                case 'BL':
                    $updatefav = lenta::AddFav($user_id, 0, 0, $msg_id, 0, $priority, $title);
                    $fav_href = "/blogs/view.php?tr={$msg_id}";
                    break;
            }

            $editfav   = lenta::GetFav($user_id, $msg_id, $pfx);
		    $key     = $msg_id;
//		    $fav_href =  ( $pfx=='CM' ? "/commune/?id={$editfav['commune_id']}&site=Topic&post={$msg_id}" : "/users/{$editfav['login']}/viewproj.php?prjid={$msg_id}"); 
		    $outHTML = __lentaPrntFavContent($editfav, $key, $user_id, $pfx, $fav_href);
		    
		    $objResponse->assign("fav".$pfx.$msg_id, "innerHTML", $outHTML);
//		    $objResponse->assign('favstar'.$pfx.$msg_id, "src", "/images/ico_star_{$priority}.gif");
            $objResponse->assign('favstar'.$pfx.$msg_id, "src", "/images/bookmarks/".$stars[$priority]);
		    break;

		case "edit":
			$editfav = lenta::GetFav($user_id, $msg_id, $pfx);
			$editfav['title'] = str_replace("<br/>", "\r\n", (reformat2($editfav['title'], 20, 0, 1)));
			$outHTML = "<table border=\"0\" cellpadding=\"1\" cellspacing=\"0\"><tbody><tr valign=\"top\"><td style=\"padding-left: 3px;\">";
			$outHTML .= "<ul class=\"post-f-fav-sel\">";
			$outHTML .= "<li><IMG alt=\"\" border=\"0\" id='favpic".$pfx.$msg_id."-0' width=\"15\" height=\"15\" src=\"/images/ico_star_0".(($editfav['priority'] != 0)?"_empty":"").".gif\" hspace=\"1\" vspace=\"1\" onclick=\"FavPriorityLenta($msg_id, 0, '$pfx')\" style=\"cursor:pointer;\"></li>";
			$outHTML .= "<li><IMG alt=\"\" border=\"0\" id='favpic".$pfx.$msg_id."-1' width=\"15\" height=\"15\" src=\"/images/ico_star_1".(($editfav['priority'] != 1)?"_empty":"").".gif\" hspace=\"1\" vspace=\"1\" onclick=\"FavPriorityLenta($msg_id, 1, '$pfx')\" style=\"cursor:pointer;\"></li>";
			$outHTML .= "<li><IMG alt=\"\" border=\"0\" id='favpic".$pfx.$msg_id."-2' width=\"15\" height=\"15\" src=\"/images/ico_star_2".(($editfav['priority'] != 2)?"_empty":"").".gif\" hspace=\"1\" vspace=\"1\" onclick=\"FavPriorityLenta($msg_id, 2, '$pfx')\" style=\"cursor:pointer;\"></li>";
			$outHTML .= "<li><IMG alt=\"\" border=\"0\" id='favpic".$pfx.$msg_id."-3' width=\"15\" height=\"15\" src=\"/images/ico_star_3".(($editfav['priority'] != 3)?"_empty":"").".gif\" hspace=\"1\" vspace=\"1\" onclick=\"FavPriorityLenta($msg_id, 3, '$pfx')\" style=\"cursor:pointer;\"></li>";							
			$outHTML .= "</ul></td><td>";
			$outHTML .= "<div class=\"fav-one-edit-txt\">";
			$outHTML .= "<INPUT id='favpriority".$pfx.$msg_id."' type='hidden' value='".$editfav['priority']."'>";
			$outHTML .= "<INPUT id='currtitle' type='hidden' value='".$editfav['title']."'>";
			$outHTML .= "<textarea rows=\"3\" cols=\"7\" id='favtext".$pfx.$msg_id."'>{$editfav['title']}</textarea>";
			$outHTML .= "<div class=\"fav-one-edit-btns\">";									
			$outHTML .= "<INPUT type='button' value='Сохранить' onClick='if(document.getElementById(\"favtext".$pfx.$msg_id."\").value.length>128){alert(\"Слишком длинное название закладки!\");return false;}else{xajax_Lenta_EditFav(".$msg_id.", document.getElementById(\"favpriority".$pfx.$msg_id."\").value, document.getElementById(\"favtext".$pfx.$msg_id."\").value, \"update\", \"$pfx\");}'>";
			$outHTML .= "<INPUT type='button' value='Отмена' onClick='xajax_Lenta_EditFav(".$msg_id.", ".$editfav['priority'].", document.getElementById(\"currtitle\").value, \"default\", \"$pfx\");'>";									
			$outHTML .= "</div></td></tr></tbody></table>";									

//$outHTML = "<li class=\"fav-one-edit c\">";
$outHTML = "<ul class=\"post-f-fav-sel\">";
$outHTML .= "<li><a href=\"\" onclick=\"FavPriorityLenta($msg_id, 0, '$pfx'); return false;\"><img src=\"../../images/bookmarks/bsg.png\" alt=\"\" id='favpic".$pfx.$msg_id."-0' /></a></li>";
$outHTML .= "<li><a href=\"\" onclick=\"FavPriorityLenta($msg_id, 1, '$pfx'); return false;\"><img src=\"../../images/bookmarks/bsgr.png\" alt=\"\" id='favpic".$pfx.$msg_id."-1'/></a></li>";
$outHTML .= "<li><a href=\"\" onclick=\"FavPriorityLenta($msg_id, 2, '$pfx'); return false;\"><img src=\"../../images/bookmarks/bsy.png\" alt=\"\" id='favpic".$pfx.$msg_id."-2'/></a></li>";
$outHTML .= "<li><a href=\"\" onclick=\"FavPriorityLenta($msg_id, 3, '$pfx'); return false;\"><img src=\"../../images/bookmarks/bsr.png\" alt=\"\" id='favpic".$pfx.$msg_id."-3'/></a></li>";
$outHTML .= "</ul>";
$outHTML .= "<div class=\"fav-one-edit-txt\">";
$outHTML .= "<INPUT id='favpriority".$pfx.$msg_id."' type='hidden' value='".$editfav['priority']."'>";
$outHTML .= "<INPUT id='currtitle' type='hidden' value='".$editfav['title']."'>";
$outHTML .= "<textarea rows=\"3\" cols=\"7\" id='favtext".$pfx.$msg_id."'>{$editfav['title']}</textarea>";
$outHTML .= "<div class=\"fav-one-edit-btns\"><input type=\"button\" value=\"Сохранить\" onClick='if(document.getElementById(\"favtext".$pfx.$msg_id."\").value.length>128){alert(\"Слишком длинное название закладки!\");return false;}else{xajax_Lenta_EditFav(".$msg_id.", document.getElementById(\"favpriority".$pfx.$msg_id."\").value, document.getElementById(\"favtext".$pfx.$msg_id."\").value, \"update\", \"$pfx\"); \$(\"fav".$pfx.$msg_id."edit\").dispose();}'/> <input type=\"button\" value=\"Отмена\" onClick='xajax_Lenta_EditFav(".$msg_id.", ".$editfav['priority'].", document.getElementById(\"currtitle\").value, \"default\", \"$pfx\"); \$(\"fav".$pfx.$msg_id."edit\").dispose();' /></div>";
$outHTML .= "</div>";
//$outHTML .= "</li>";

$objResponse->insertAfter("fav".$pfx.$msg_id, "li", "fav".$pfx.$msg_id."edit");		
$objResponse->assign("fav".$pfx.$msg_id."edit", "className", "fav-one-edit");		
$objResponse->assign("fav".$pfx.$msg_id."edit", "innerHTML", $outHTML);		
										
//			$objResponse->assign("fav".$pfx.$msg_id, "innerHTML", $outHTML);
		    break;
		default:
		    $editfav   = lenta::GetFav($user_id, $msg_id, $pfx);
		    $key     = $msg_id;
//		    $fav_href =  ( $pfx=='CM' ? "/commune/?id={$editfav['commune_id']}&site=Topic&post={$msg_id}" : "/users/{$editfav['login']}/viewproj.php?prjid={$msg_id}"); 
            switch($pfx) {
                case 'CM':
                    $updatefav = lenta::AddFav($user_id, $msg_id, 0, 0, 0, $priority, $title);
                    $fav_href = "/commune/?id={$editfav['commune_id']}&site=Topic&post={$msg_id}";
                    break;
                case 'PF':
                    $updatefav = lenta::AddFav($user_id, 0, $msg_id, 0, 0, $priority, $title);
                    $fav_href = "/users/{$editfav['login']}/viewproj.php?prjid={$msg_id}";
                    break;
                case 'BL':
                    $updatefav = lenta::AddFav($user_id, 0, 0, $msg_id, 0, $priority, $title);
                    $fav_href = "/blogs/view.php?tr={$msg_id}";
                    break;
            }
		    $outHTML = __lentaPrntFavContent($editfav, $key, $user_id, $pfx, $fav_href);
		    $objResponse->assign("fav".$pfx.$msg_id, "innerHTML", $outHTML);
		    break;
	}

	return $objResponse;
}

$xajax->processRequest();

?>
