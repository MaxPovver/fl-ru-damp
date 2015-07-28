<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/registration.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/opinions.php");
$reg = new registration();

/**
 * Выводит кнопки управление комментарием (те, что под комментарием). Используется здесь и в xajax/contest.server.php
 * @param  integer   $pid           id проекта
 * @param  array     $comment       массив с данными о комментарии (строка из таблицы projects_contest_msgs)
 * @param  boolean   $comm_blocked  автор отключил возможность оставлять комментарии?
 * @param  boolean   $project_end   проект закрыт?
 * @param  integer   $level         уровень вложенности комментария
 * @param  string    $prj_name      название проекта
 * @return string                   HTML с кнопками управления
 */
function comment_options($pid, &$comment, $comm_blocked, $project_end, $level, $prj_name='') {
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/registration.php");
    $reg = new registration();
    
    if ($comment['deleted']) {
        if (hasPermissions("comments")) {
		    $html = "<li class='t-o3'><a href='.' onclick='comment.restore({$comment['id']}); return false;'>Восстановить</a></li>";
        }
	} else {
		$i = 1;
		$link = HTTP_PREFIX.$_SERVER['HTTP_HOST'].getFriendlyURL("project", $pid).'?comm='.$comment['id'].'#c-comment-'.$comment['id'];
		$html = "
			" . ($GLOBALS['contest']->is_moder? "<li class='t-o1'><a href='.' onclick='comment.del({$comment['id']}); return false;'>Удалить</a></li>" : "") . "
			" . ((($GLOBALS['contest']->uid == $comment['user_id'] || $GLOBALS['contest']->is_moder) && !$GLOBALS['contest']->is_banned && !$project_end)? "<li class='t-o2'><a href='.' onclick='comment.form({$comment['offer_id']}, {$comment['id']}, {$comment['id']}); return false;'>Редактировать</a></li>": "") . "
			" . (($GLOBALS['contest']->uid && !$comm_blocked && !$GLOBALS['contest']->is_banned && !$project_end)? "<li class='t-o2'><a href='.' onclick='comment.form({$comment['offer_id']}, {$comment['id']}, 0, $level); return false;'>Комментировать</a></li>": "") . "
            		" . ((($GLOBALS['contest']->is_owner || hasPermissions('projects')) && !$project_end && !$comment['is_banned'] && ($GLOBALS['contest']->uid!=$comment['user_id']))? "<li class='t-o2'><a onclick='return confirm(\"".($comment['user_blocked']? "Разблокировать": "Заблокировать")." пользователя?\");' href='".getFriendlyURL("project", $pid)."?action=blockuser&uid={$comment['user_id']}&comm={$comment['id']}'>".($comment['user_blocked']? "Разблокировать": "Заблокировать")." пользователя</a></li>": "") . "
            <li><a href='$link' onclick='prompt(\"Ссылка на комментарий\", \"$link\"); return false;'>Ссылка</a></li>
            <li class='t-o3'><a href=\"#top\" onclick=\"return gotoTopComment(event)\">Вверх</a></li>
		";
        if ($GLOBALS['contest']->is_moder) {
            if ( $comment['warn']<3 && !$comment['is_banned'] && !$comment['ban_where'] ) {
                $html.="<li class='t-o3'><span class='warnlink-".$comment['user_id']."'><a style='color: red' href='.' onclick='banned.warnUser(".$comment['user_id'].", 0, \"projects\", \"p{$pid}c{$comment['id']}\", 0); return false;'>Сделать предупреждение</a> (<span class='warncount-".$comment['user_id']."'>".($comment['warn'] ? $comment['warn'] : 0)."</span>)</span></li>";
            } else { 
                $sBanTitle = (!$comment['is_banned'] && !$comment['ban_where']) ? 'Забанить!' : 'Разбанить';
                $html.="<li class='t-o3'><span class='warnlink-{$comment['user_id']}'><a style='color:red;' href=\"javascript:void(0);\" onclick=\"banned.userBan({$comment['user_id']}, 'p{$pid}c{$comment['id']}',0)\">$sBanTitle</a></span></li>";
            }
        }
	}
	return $html;
}


/**
 * Выводит дерево комментариев. Используется здесь и в xajax/contest.server.php
 * @param  integer   $pid            id проекта
 * @param  string    $name           название проекта
 * @param  array     $comments       массив с деревом комментариев (подробнее в classes/contest.php)
 * @param  boolean   $comm_blocked   автор отключил возможность оставлять комментарии?
 * @param  boolean   $project_end    проект закрыт?
 * @param  integer   $s_level        уровень вложенности комментария
 * @return string                    HTML с кнопками управления
 */
function comments($pid, $name, &$comments, $comm_blocked, $project_end, $s_level=0) {
    global $stop_words, $contest, $project, $session;
    static $level = 0;
	$level = ($s_level? $s_level: $level) + 1;
	$html = '';
	$set_branch_as_read = false;
	for ($i=0,$c=count($comments); $i<$c; $i++) {
        if (($comments[$i]['is_banned'] || $comments[$i]['usr_banned'] || $comments[$i]['user_blocked'] === 't') && !hasPermissions('projects') && !$contest->is_owner) {
            $msg = $msg2 = 'Ответ от заблокированного пользователя';
        } else if (!trim($comments[$i]['deleted'])){
            $sMsg  = $comments[$i]['moderator_status'] === '0' ? $stop_words->replace($comments[$i]['msg']) : $comments[$i]['msg'];
            $msg   = reformat($sMsg, 30, 0, 0, 1);
            $msg2  = reformat($comments[$i]['msg'], 30, 0, 0, 1);
        } else {
            $msg2 = $msg = "Комментарий удален модератором";
            if (hasPermissions("comments")) {
               $moderator = '';
               $moderatorData = new users();
               $moderatorData->GetUserByUID($comments[$i]['deluser_id']);
               if ($moderatorData->login) {
               	   $moderator = ' '.$moderatorData->login.' ('.$moderatorData->uname.' '.$moderatorData->usurname.') ';
               }
               $msg2 = $msg = $msg." $moderator";
            }
        	if ($comments[$i]['deluser_id'] == $comments[$i]['user_id']) {
        	    $msg2 = $msg = "Комментарий удален автором";
        	} else {
        		if (trim($comments[$i]['deleted_reason']) && (hasPermissions("comments") || $comments[$i]['user_id'] == get_uid(false)) ) {
        	        $msg2 = $msg = $msg."<div style='color:#ff0000'>Причина: ".$comments[$i]['deleted_reason']."</div>";
        	    }
        	}
        }
        $a_is_banned = ($comments[$i]['is_banned'] || $comments[$i]['usr_banned']) && hasPermissions('projects');
		$html .= "
			<li class='thread' id='thread-{$comments[$i]['id']}'".(($level >= 9)? " style='margin-left: 0'": "").">
				<a name='c-comment-{$comments[$i]['id']}'></a>
				<div class='comment-one" . (($comments[$i]['deleted'] || $comments[$i]['hidden'])? " comment-deleted": "") . "' id='comment-{$comments[$i]['id']}'>
					<div class='contest-ea'>" . view_avatar($comments[$i]['login'], $comments[$i]['photo'], 1) . "</div>
					<div class='comment-body'>
						<h3 class='username'>".$session->view_online_status($comments[$i]['login'])."
							<a href='/users/{$comments[$i]['login']}' class='".(is_emp($comments[$i]['role'])? 'employer-name': 'freelancer-name')."'>{$comments[$i]['uname']} {$comments[$i]['usurname']} [{$comments[$i]['login']}]</a>&nbsp;".view_mark_user($comments[$i])."&nbsp;".($comments[$i]['completed_cnt'] > 0 ?'<a href="/promo/bezopasnaya-sdelka/" title="Пользователь работал через Безопасную Сделку" target="_blank"><span class="b-icon b-icon__shield b-icon_top_1"></span></a>':'')."
							<span>[" . dateFormat('d.m.Y | H:i', $comments[$i]['post_date']) . "]</span>
							<span id='comment-modified-{$comments[$i]['id']}'>" . ($comments[$i]['modified']? ("[изменен " . dateFormat('d.m.Y | H:i', $comments[$i]['modified']) . "]"): '&nbsp;') . "</span>
							". ($a_is_banned?"<b style=\"color:#ff0000\">Пользователь забанен</b>":"")."
						</h3>
						" . (($_SESSION['uid'] && $comments[$i]['is_new'])? "<p><img src='/images/mark-new.png' width='53' height='12' alt='новое' class='mark-new' /></p>": "") . "
						<div id='comment-change-{$comments[$i]['id']}'>
						<p id='comment-msg-{$comments[$i]['id']}' ".($a_is_banned?"style='color:silver'":"").".>".$msg."</p>
						<div id='comment-msg-original-{$comments[$i]['id']}' style='display:none'>".$msg2."</div>
						<script type=\"text/javascript\">
                        banned.addContext( 'p{$pid}c{$comments[$i]['id']}', 3, '".HTTP_PREFIX."{$_SERVER['HTTP_HOST']}".getFriendlyURL("project", $pid)."?comm={$comments[$i]['id']}#comment-{$comments[$i]['id']}', \"".htmlspecialchars($name)."\" );
                        </script>
						<ul class='thread-options' id='comment-options-{$comments[$i]['id']}'>
							" . comment_options($pid, $comments[$i], $comm_blocked, $project_end, $level, $name) . "
						</ul>
                        <div id='warnreason-".$comments[$i]['id']."-".$comments[$i]['user_id']."' style='display:none; padding: 0 0 5px 0px;'>&nbsp;</div>
						</div>
					</div>
				</div>
				" . (empty($comments[$i]['comments'])? '': ('<ul>'.comments($pid, $name, $comments[$i]['comments'], $comm_blocked, $project_end).'</ul>')) . "
			</li>
		";
        if ( $comments[$i]['is_new'] ) {
            $set_branch_as_read = true;
        }
	}
	if ($set_branch_as_read) {
        $p = new projects;
        $data = array("id" => $pid, "kind" => 7, "user_id" => $project["user_id"] );
        $p->SetRead($data, get_uid( false ), true);
    }
	--$level;
	return $html;
}

// если нужны только функции вывода комментариев (для xajax/contest.server.php), то сразу выходим
if (defined('FUNCTIONS_ONLY')) return;


/***************************************************************************************/
require_once $_SERVER['DOCUMENT_ROOT'].'/xajax/contest.common.php';

$contest->ClearTempFiles();

$daysOfWeek = array(1=>'понедельник', 'вторник', 'среду', 'четверг', 'пятницу', 'субботу', 'воскресенье');
$users = array();

$show_info = TRUE;

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/project_exrates.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/HTML/projects_lenta.php");
$op_data = opinions::getCounts($project['user_id'], array('frl', 'norisk', 'all', 'total'));
$project_exRates = project_exrates::GetAll();
$translate_exRates = array(0 => 2, 1 => 3, 2 => 4, 3 => 1);

/* Не используется?
$category = professions::GetGroup(intval($project['category']), $err);
if($category['name'] && $project['subcategory'])
   $category['name'] .= '&nbsp;/&nbsp;'.professions::GetProfName($project['subcategory']);
*/

// сессия для защиты от накрутках в голосованиях
if ($_SESSION['uid']) {
    mt_srand();
	$_SESSION['contest_sess'] = md5(mt_rand() . mktime());
}

$up_price = array ( 'kon'    => new_projects::getPriceByCode(( is_pro() ? new_projects::OPCODE_KON_UP : new_projects::OPCODE_KON_UP_NOPRO )));
?>



<? $xajax->printJavascript('/xajax/'); ?>
<script type="text/javascript">
/*
var cookie = getCookie('contestCM');
if ( cookie ) {
    try {
        cookie = unserialize(cookie);
        if ( typeof cookie == 'object' ) {
            var expire = new Date();
            expire.setFullYear(expire.getFullYear() + 1);
            setCookie('contestCM', JSON.encode(cookie), {'expires': expire});
        }
    } catch(e) { }
}
*/

pid = <?=$project['id']?>;

<?php if ($contest->is_moder) { ?>
    var PROJECT_BANNED_PID = 'p<?= $project['id']?>';
    var PROJECT_BANNED_URI = '<?=$GLOBALS['host']?><?=getFriendlyURL("project", $project['id'])?>';
    var PROJECT_BANNED_NAME = "<?=htmlspecialchars($project['name'])?>";
<?php } ?>

<? if ($errmsg) { ?>

alert('<?=$errmsg?>');
window.location = '<?=getFriendlyURL("project", $project['id'])."?".($_GET['comm']? '&comm='.htmlspecialchars($_GET['comm'], ENT_QUOTES): "").($_GET['offer']? '&offer='.htmlspecialchars($_GET['offer'], ENT_QUOTES): "").($_GET['filter']? '&filter='.htmlspecialchars($_GET['filter'], ENT_QUOTES) :"")?>';

<? } ?>

<? if ((strtotime($project['end_date']) < mktime()) && ($contest->is_owner || $contest->is_moder) && empty($contest->positions)) { ?>
var i = 0;
candidates = [];

<? foreach ($contest->offers as $offer) { ?>
    //offers_id[i++] = <?=$offer['id'];?>;
	<? if ($offer['selected'] == 't' && $offer['is_deleted'] == 'f') { ?>
	candidates[i++] = { uid: <?=$offer['user_id']?>, login: '<?=htmlspecialchars($offer['login'])?>' }
	<? } ?>
<? } ?>
<? } ?>

<? 
$all_msg_count = 0;
$all_offers_count = 0;
$cm = array();
if ( !empty($_COOKIE['contestCM']) ) {
    $cm = json_decode(stripslashes($_COOKIE['contestCM']));
    if ( $cm instanceof stdClass && !empty($cm->$project['id']) && is_array($cm->$project['id']) ) {
        $cm = $cm->$project['id'];
    } else {
        $cm = array();
    }
}
foreach ($contest->offers as $offer) { 
    //if ($project['pro_only'] == 't' && !$is_pro && $project['user_id']!=$_SESSION['uid'] && !hasPermissions('projects')) continue;
    if($offer['user_id']!=$uid && $offer['is_deleted']=='t' && !hasPermissions('projects')) continue;
    if($_GET['filter']=='candidates' && $offer['selected']!='t') continue;
    if ( in_array($offer['id'], $cm) ) {
        $close_all[$offer['id']] = true;
        $c[$offer['id']] = true;
    } else {
        $c[$offer['id']] = false;
    }
    $ofid[] = $offer['id']; 
    if(!($offer['user_id']!=$uid && $offer['is_deleted']=='t' && !hasPermissions('projects'))) {
      $all_msg_count+=$offer['msg_count'];
      $all_offers_count++;
    }
}?>
var pid = <?=$project['id']?>;
var offers_closed = new Array();
var offers_id = new Array();//(<?=is_array($ofid)?implode(",", $ofid):""?>);
<?if(is_array($ofid)): for($i=0;$i<count($ofid);$i++):?>
    offers_id[<?=$i?>] = <?=$ofid[$i]?>;
    offers_closed[<?=$ofid[$i]?>] = <?=intval($c[$ofid[$i]])?>;
<?endfor; endif;?>

<? if(count($ofid) == count($close_all)): ?>
var display = 0;
<? else: ?>
var display = 1;
<? endif; ?>

var contest_is_pro = <?=(is_pro() ? 1 : 0)?>;

function allCommentsTree(dsp) {
    if(dsp != undefined) display = dsp;
    for(var i=0;i<offers_id.length;i++) {
        commentsTree(pid, offers_id[i], display);
    }
    
    offers_closed.each(function(item, index, array){
        array[index] = display;
    })
    
    if(display == 1) {
        display = 0;
        document.getElementById('allct').innerHTML = 'Развернуть все ветви комментариев';
    } else {
        display = 1;
        document.getElementById('allct').innerHTML = 'Свернуть все ветви комментариев';
    }
}

function checkClosed(ofr) {
    if(offers_closed[ofr] == 1) {
        offers_closed[ofr] = 0;    
    } else {
        offers_closed[ofr] = 1;
    }
    var tr = 0;
    var fl = 0;

    offers_closed.each(function(item, index){
        if(item == 1) {
            tr = tr+1;
        } else if(item == 0) {
            fl = fl+1;
        }
    })
    
    if(tr == offers_id.length) {
        display = 0;
        document.getElementById('allct').innerHTML = 'Развернуть все ветви комментариев';    
    } else if(fl == offers_id.length) {
        display = 1;
        document.getElementById('allct').innerHTML = 'Свернуть все ветви комментариев' 
    }
}

</script>

<?php
if ( hasPermissions('projects') ) {
	include_once( $_SERVER['DOCUMENT_ROOT'] . '/filter_specs.php' );
}
?>


<? /********************************************************
   ** Описание проекта **
   *********************************************************/ ?>
<?php
if(is_emp()) {
    $href = "/frl_only.php";
} elseif(get_uid(false)) {
    
    //if($project['pro_only'] == 't' && !$is_pro && $project['user_id']!=$_SESSION['uid'] && !hasPermissions('projects')) {
    if($project['verify_only'] == 't' && !$is_verify && $project['user_id']!=$_SESSION['uid']) {
        $href = "javascript: quickVerShow();";
        $quick_verification=1;
    } elseif($project['pro_only'] == 't' && !$is_pro && $project['user_id']!=$_SESSION['uid']) {
        $href = "/payed/";
    //} elseif($project['verify_only'] == 't' && !$is_verify && $project['user_id']!=$_SESSION['uid'] && !hasPermissions('projects')) {
    } else {
        $href = "#offer-edit";
    }
    
} else {
    $href = "/registration/?from_prj={$project['id']}";
}
?>

<a name="top"><img src="/images/1.gif" width="1" height="1"></a>
<div class="b-free-share_float_right b-free-share_padtop_5"><?=ViewSocialButtons('project', $sTitle, false, false, $sDescr)?></div>

<h1 class="b-page__title">
    <? if (hasPermissions('projects') && $project['ico_prepay'] == 't') { ?><img src="/images/ico_prepay.gif" alt="Предоплата" width="21" height="21" class="ico-prepay">&nbsp;<? } ?>
    <?if ($project['ico_closed']=='t') {?><img src="/images/ico_closed.gif" alt="Проект закрыт" /><?}?>
    <span id="prj_name_<?=$project['id']?>"><?=reformat($sTitle, 30, 0, 1)?></span>
</h1>
<?php // include(dirname(__FILE__).'/only_pro_verify.inc.php') ?>

                 <?php
                 $can_change_prj = hasPermissions("projects");
                 if($can_change_prj) {
                 ?>
                    <?php 
                    $quickEditPoputType = 2;
                    require_once($_SERVER['DOCUMENT_ROOT'].'/projects/tpl.prj-quickedit.php'); 
                    ?>

                    <div id="popup_budget" class="b-shadow b-shadow_inline-block b-shadow_width_335 b-shadow_center b-shadow_zindex_11 b-shadow_hide">
                        <div class="b-shadow__right">
                            <div class="b-shadow__left">
                                <div class="b-shadow__top">
                                    <div class="b-shadow__bottom">
                                        <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_20">
                                            <div class="b-shadow__title b-shadow__title_padbot_15">Редактирование бюджета</div>
                                            <div id="popup_budget_prj_name" class="b-layout__txt b-layout__txt_padbot_15"></div>

                                            <div class="b-form b-form_padbot_20">
                                                <div class="b-combo b-combo_inline-block b-combo_margright_10">
                                                    <div class="b-combo__input b-combo__input_width_60">
                                                        <input id="popup_budget_prj_price" class="b-combo__input-text b-combo__input-text_fontsize_15" name="cost" type="text" size="80" maxlength="6" value="" />
                                                    </div>
                                                </div>
                                                <div class="b-combo b-combo_inline-block b-combo_margright_10" >
                                                    <div class="b-combo__input b-combo__input_multi_dropdown drop_down_default_0 b-combo__input_init_projQuickEditCurrency b-combo__input_width_45 b-combo__input_min-width_40 b-combo__input_arrow_yes">
                                                        <input id="popup_budget_prj_currency" class="b-combo__input-text b-combo__input-text_fontsize_15" type="text" size="80" readonly="readonly" />
                                                        <span class="b-combo__arrow"></span>
                                                    </div>
                                                </div>
                                                <div class="b-combo b-combo_inline-block b-combo_margright_10" >
                                                    <div class="b-combo__input b-combo__input_multi_dropdown drop_down_default_1 b-combo__input_init_projQuickEditCostby b-combo__input_width_60 b-combo__input_min-width_40 b-combo__input_arrow_yes" >
                                                        <input id="popup_budget_prj_costby" class="b-combo__input-text b-combo__input-text_fontsize_15" type="text" size="80" readonly="readonly"/>        
                                                        <span class="b-combo__arrow"></span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="b-check b-check_padbot_10 b-check_clear_both">
                                                <input id="popup_budget_prj_agreement" class="b-check__input" name="agreement" type="checkbox" value="1">
                                                <label class="b-check__label b-check__label_fontsize_13" for="popup_budget_prj_agreement">по договорённости</label>
                                            </div>

                                            <div id="popup_budget_prj_price_error" class="b-fon b-fon_bg_ff6d2d b-fon_padtop_10" style="display: none; ">
                                                <b class="b-fon__b1"></b>
                                                <b class="b-fon__b2"></b>
                                                <div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_fontsize_13 ">
                                                    <span class="b-fon__attent"></span><div class="b-fon__txt b-fon__txt_margleft_20">Бюджет заполнен не верно</div>
                                                </div>
                                                <b class="b-fon__b2"></b>
                                                <b class="b-fon__b1"></b>
                                            </div>

                                            <div class="b-buttons b-buttons_padtop_15">
                                                <a id="popupBtnSaveBudget" href="javascript:void(0)" class="b-button b-button_flat b-button_flat_green">Сохранить</a>                            
                                                <span class="b-buttons__txt">&nbsp;или&nbsp;</span>
                                                <a class="b-buttons__link b-buttons__link_dot_c10601 b-shadow__close" href="javascript:void(0)">закрыть без изменений</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="b-shadow__tl"></div>
                        <div class="b-shadow__tr"></div>
                        <div class="b-shadow__bl"></div>
                        <div class="b-shadow__br"></div>
                    </div>
                 <?
                 }
                 ?>

<div id="contest_info_<?=$prj_id?>" class="contest-view">
<?php require_once($_SERVER['DOCUMENT_ROOT'] . "/projects/contest_item.php"); ?>
</div>

<? if (!$project['is_blocked'] || $contest->is_moder) { ?>

<? /********************************************************
   ** Победители **
   *********************************************************/ ?>
<? if ($contest->positions) { ?>
<a name="winner"></a>
<?//var_dump($contest);?>

<table class="contest-places">
	<tr>
		<td class="contest-place contest-first">

			<h3>Первое место занял <br /><a href="<?=getFriendlyURL("project", $project['id'])?>?offer=<?=$contest->positions[1]['id']?>#offer-<?=$contest->positions[1]['id']?>"><?=hyphen_words($contest->positions[1]['uname'])?> <?=hyphen_words($contest->positions[1]['usurname'])?></a>
            </h3>

		</td>
		<td width="12"></td>
		<td class="contest-place contest-second">
			<h3>Второе место <? if ($contest->positions[2]) { ?>занял <br /><a href="<?=getFriendlyURL("project", $project['id'])?>?offer=<?=$contest->positions[2]['id']?>#offer-<?=hyphen_words($contest->positions[2]['id'])?>"><?=$contest->positions[2]['uname']?> <?=hyphen_words($contest->positions[2]['usurname'])?></a><? } else { ?>	<br />не указано<? } ?></h3>
		</td>
		<td width="12"></td>
		<td class="contest-place contest-third">
			<h3>Третье место <? if ($contest->positions[3]) { ?>занял <br /><a href="<?=getFriendlyURL("project", $project['id'])?>?offer=<?=$contest->positions[3]['id']?>#offer-<?=hyphen_words($contest->positions[3]['id'])?>"><?=$contest->positions[3]['uname']?> <?=hyphen_words($contest->positions[3]['usurname'])?></a><? } else { ?><br />не указано<? } ?></h3>
		</td>
	</tr>
</table>
<? } ?>

<? //if ($no_answers && !$contest->is_pro) include "content_no_answers.php"; ?>

<a name="offers"></a>

<div class="contest-comments" id="contest-comments" <?php if (!$contest->offers || $all_offers_count==0) { ?>style="display:none"<?php }//if?>>
    
<? /********************************************************
   ** Форма добавление работы **
   *********************************************************/ ?>


<? if ((($contest->uid && !$contest->is_emp) || $contest->offer) && !$project['contest_end'] && !(($project['pro_only'] == 't' && !$is_pro && $project['user_id']!=$_SESSION['uid'] && !hasPermissions('projects')))) { ?>
    <script type="text/javascript">
        window.addEvent("domready", function() {
            var textarea = new resizableTextarea($$("div.rtextarea"), { handler: ".handler", modifiers: {x: false, y: true}, size: {y:[170, 1000]}});
        });
    </script>
	<a name="offer-edit"></a>
	<div class="contest-add " id="add-offer"<? if (!$contest->offer && !$offer_autofocus) { ?> style="display: none"<? } ?>>
	<h2 class="b-layout__title b-layout__title_padbot_20">Ваши конкурсные работы</h2>
	<?= view_hint_access_action('Чтобы участвовать в конкурсе');?>
	<div class="ca-label">Добавить работы</div>
	<div class="ca-form">
		<input type="hidden" name="action" value="add_pic">
		<div id="ca-iboxes"><noscript>Для загрузки работ вам необходимо включить JavaScript</noscript></div>
		<div class="ca-managment">
			<table cellpadding="0" border="0">
			<tr>
				<td class="ca-add-btn"><a href="." onclick="boxes.add(); return false;"><img src="/images/add.gif" border="0"></a></td>
				<td class="ca-box-ico"><a href="." onclick="boxes.add(); return false;"><img src="/images/box_grey.gif" border="0"></a></td>
				<td class="ca-box-ico"><a href="." onclick="boxes.add(); return false;"><img src="/images/box_grey.gif" border="0"></a></td>
				<td class="ca-box-ico"><a href="." onclick="boxes.add(); return false;"><img src="/images/box_grey.gif" border="0"></a></td>
				<td class="ca-add-text"><a href="." onclick="boxes.add(); return false;">Добавить еще 3 поля</a></td>
				<td class="ca-add-info">Для файлов до 2Мбайт. Разрешены к загрузке файлы следующих форматов:<br/>gif, jpeg, png, swf, zip, rar, xls, txt, doc, docx, rtf, pdf, psd, mp3 </td>
			</tr>
			</table>
		</div>
	</div>
	
	<form id="contFormSent" action="<?=$_SERVER['REQUEST_URI']?>" method="POST" onsubmit="return sendOffer();">
	<input type="hidden" name="action" value="<?=($contest->offer? 'change': 'add')?>">
	<input type="hidden" id="files" name="files" value="">
    <input name="hash" type="hidden" value="<?=$hash?>" /> 
	<div class="ca-label">
		Комментарий
	</div>
	<div class="ca-form">
        <div class="rtextarea">
		<textarea class="ca-comment" id="comment-box" name="comment" style="resize: none;" 
                onchange="checkMaxChars('comment-box', 'ca-comment-warn', <?=contest::CHARS_ON_OFFER?>)" 
                onkeypress="checkMaxChars('comment-box', 'ca-comment-warn', <?=contest::CHARS_ON_OFFER?>)"><?=($contest->offer? $contest->offer['descr']: '')?></textarea>
        </div>
        
		<table cellpadding="0" cellspacing="0" border="0" class="ca-dsbl">
		<tr>
			<td><input name="comm_blocked"<?=(($contest->offer && $contest->offer['comm_blocked'] == 't')? ' checked': '')?> type="checkbox"<?=(($contest->is_pro || $contest->is_moder)? '': ' disabled')?>></td>
			<td>Запретить другим пользователям комментировать моё предложение.</td>
			<td><span class="ca-pro-text">Данная функция доступна только для</span></td>
			<td><a class="b-layout__link" href="/payed/"><span class="b-icon b-icon__pro b-icon__pro_f"></span></a></td>
		</tr>
		</table>
		<div id="ca-comment-warn" class="ca-comment-warn">&nbsp;</div>
	</div>
	
	<div class="b-buttons b-buttons_padbot_20 b-buttons_clear_both b-buttons_padleft_140 b-buttons_padleft_null_iphone">
		<input id="offer_submit" class="b-button b-button_flat b-button_flat_green" type="submit" value=" <?=($contest->offer? "Сохранить изменения": "Добавить работы в конкурс")?> "> &#160;&#160;
		<a id="offer_reset" class="b-buttons__link" href="#" type="reset" onclick="<?=($contest->offer? "location.href = '".getFriendlyURL("project", $project['id'])."?offer={$contest->offer['id']}'": "resetOffer()")?>; return false;">Отменить добавление</a>
	</div>
	</form>

	</div>
	
<? } ?>
    

<? 
/********************************************************
 ** Список заблокированых пользователей **
 *********************************************************/ 	
if ($_GET['filter'] == 'banned') {
	
	$banned = $contest->GetBanned();
	if (empty($banned)) {
	
?>
	<div class="no-offers">Нет пользователей</div>

	<? } else { ?>
	
	<style>
		.lpl-avatar { margin-right: 0 }
		LI { list-style: none }
	</style>
	
	<ul class="comments-list">
		<? foreach ($banned as $user) { ?>
		<li class="thread">
			<div class="suggest-one ">
					<div class="suggest-options">
						<? if ($contest->is_owner || $contest->is_moder) { ?>
						<span class="so-btns so-ban">
							Забанен<br />
							<img src="/images/u-ban.png" title="Забанен автором проекта" alt="Забанен" width="49" height="16" />
						</span>
						<? } ?>
					</div>
				<div class="contest-ea"><?=view_avatar($user['login'], $user['photo'], 1)?></div>
				<div class="suggest-info">
					<h3 class="username"><?=$session->view_online_status($user['login'])?>
						
						<a href="/users/<?=$user['login']?>" class="<?=(is_emp($user['role'])? 'employer-name': 'freelancer-name')?>"><?=$user['uname']?> <?=$user['usurname']?> [<?=$user['login']?>]</a> <?=view_mark_user($user)?>&nbsp;&nbsp;
						<? if (($contest->is_owner || hasPermissions('projects')) && !$project['contest_end']) { ?>
							<div style="margin-top: 22px"><a href="<?=getFriendlyURL("project", $project['id'])?>?action=blockuser&amp;uid=<?=$user['uid']?>&amp;filter=banned" onclick="return confirm('Разблокировать пользователя?')">Разблокировать пользователя</a></div>
						<? } ?>
					</h3>
				</div>
			</div>
		</li>
		<? } ?>
	</ul>

<? 
	}  // empty($banned)

} else {  // $_GET['filter'] == 'banned'
 /********************************************************
  ** Список предложений **
  *********************************************************/ ?>

	<? if ($contest->offers && $all_offers_count) { $foto_alt = $project['name']; ?>
   
<?php if (!$contest->offers || $all_offers_count==0) { ?>
<h2 id="contest-answer-header" class="contest-answer">Нет пользователей</h2>
	
<?php 

} else  { 

?>
<h2 id="contest-answer-header" class="b-layout__title b-layout__title_padbot_20">	
    <?/*
	<a href="." onclick="ShowHide('add-offer'); var textarea = new resizableTextarea($$('div.rtextarea'), { handler: '.handler', modifiers: {x: false, y: true}, size: {y:[170, 1000]}}); return false;">
	<? if ($contest->uid && !$contest->is_emp && !$contest->has_offer && !$project['contest_end'] && $project['closed'] != 't' && !$contest->is_banned) { ?>
	<img src="/images/post_work.png" alt="Разместить работу" width="174" height="28" class="post_button" />
	<? } ?>
	</a>*/?>
	<?
	switch ($_GET['filter']) {
		case 'candidates': 
			echo 'Конкурсные работы кандидатов';
			break;
		case 'banned': 
			echo 'Забаненные пользователи';
			break;
		default: 
			echo 'Конкурсные работы';
	}
	?>
</h2>
<?} //else?>
<? if ($_GET['filter'] != 'banned') { ?>
<div class="contest-colapse-line" id="contest-comments-treelink" <? if($all_msg_count >= 1): ?>style="display:block"<? else: ?>style="display:none"<? endif; ?>><strong><a href="javascript:void(0)" onClick="allCommentsTree(); return false;" id="allct">Свернуть все ветви комментариев</a> (<span id="co-all"><?=(int)$all_msg_count?></span>)</strong></div>
<? } ?>
   
	<ul class="coments-list">
	
	<?php
	foreach ($contest->offers as $offer) {
        if($offer['user_id'] == get_uid(false) || $offer['user_id'] == $project["user_id"] ) contest::markReadComments(get_uid(false));
        if($_GET['filter']=='candidates' && $offer['selected']=='f') continue;
	    $a_is_banned = (($offer['is_banned'] || $offer['usr_banned']) && hasPermissions('projects'));
        if($offer['user_id']!=$uid && $offer['is_deleted']=='t' && !hasPermissions('projects')) continue;
	?>
	<li class="thread thread-offer <?=$offer['is_deleted']=='t'?'comment-deleted':''?>" id="offer-<?=$offer['id']?>">
		<a name="c-offer-<?=$offer['id']?>"></a>
        <a name="freelancer_<?=$offer['user_id']?>"></a>
		<div class="suggest-one <?= isset($_SESSION['uid']) && $offer['user_id'] == $_SESSION['uid'] ? 'suggest-mine' : ''?>">
				<div class="suggest-options">
					<? $candidate = ($offer['selected'] == 't' || $contest->positions[1]['user_id'] == $offer['user_id'] || $contest->positions[2]['user_id'] == $offer['user_id'] || $contest->positions[3]['user_id'] == $offer['user_id']); ?>
					<? if (($contest->is_owner || $contest->is_moder) && $offer['user_blocked']) { ?>
						<span class="so-btns so-ban">
                                                        <?= $offer['user_id'] == $_SESSION['uid'] ? "<br /><br />" : ""?>
							Забанен<br />
							<img src="/images/u-ban.png" title="Забанен автором проекта" alt="Забанен" width="49" height="16" />
						</span>
					<? } else if ($contest->is_owner && $project['closed'] != 't') { ?>
						<span id="select-<?=$offer['id']?>" class="so-btns"<?=($candidate? ' style="display: none"': '')?>>
							Кандидат<br />
							<? if (!$contest->positions) { ?>
							<a href="." class="__ga__project__contest_candidate" onclick="candidate.add(<?=$offer['id']?>); return false;"><img src="/images/u-candidate.png" title="Добавить в кандидаты на выполнение проекта" alt="Кандидат" width="49" height="16" /></a>
							<? } else { ?>
							<img src="/images/u-candidate.png" title="Добавить в кандидаты на выполнение проекта" alt="Кандидат" width="49" height="16" />
							<? } ?>
						</span>
						<span id="selected-<?=$offer['id']?>" class="so-btns"<?=($candidate? '': ' style="display: none"')?>>
							Кандидат<br />
							<img src="/images/candidate.png" title="Кандидат на выполнение проекта" alt="Кандидат" width="51" height="16" />
							<? if (!$contest->positions) { ?>
							<a href="." onclick="candidate.del(<?=$offer['id']?>); return false;"><img src="/images/remove-candidate.png" title="Удалить из кандидатов" alt="Удалить" width="17" height="16" /></a>
							<? } else { ?>
							<img src="/images/remove-candidate.png" title="Удалить из кандидатов" alt="Удалить" width="17" height="16" />
							<? } ?>
						</span>
					<? } ?>
     </div>
			<div class="contest-ea"><?=view_avatar($offer['login'], $offer['photo'], 1)?></div>
			<div class="suggest-info">
				<h3 class="username"><?=$session->view_online_status($offer['login'])?>
				
					<a href="/users/<?=$offer['login']?>" class="freelancer-name"><?=$offer['uname']?> <?=$offer['usurname']?> [<?=$offer['login']?>]</a>&nbsp;<?= view_mark_user($offer);?> <?=($offer['completed_cnt'] > 0 ?' <a class="b-layout__link" href="/promo/bezopasnaya-sdelka/" title="Пользователь работал через Безопасную Сделку" target="_blank"><span class="b-icon b-icon__shield b-icon_top_1"></span></a>':'');?>
     					<span>[<?=dateFormat('d.m.Y | H:i', $offer['post_date'])?>]<?=($offer['modified']? dateFormat(" [внесены изменения: d.m Y | H:i]", $offer['modified']): '')?></span>
					<? if($a_is_banned): ?><b style="color:#ff6600"><nobr>Пользователь забанен</nobr></b><? endif; ?>
					<?/* if(hasPermissions('projects')): ?><b style="color:#ff0000"><nobr>Внимание! Это платный проект!</nobr></b><? endif; */?>
					
				</h3><?
				if ( $offer["user_id"] != get_uid(false) && $offer["is_new"] ) { ?>
                    <p><img width="53" height="12" class="mark-new" alt="новое" src="/images/mark-new.png"></p><?
                    $p = new projects;
                    $data = array("id" => $contest->pid, "kind" => 7, "user_id" => $project["user_id"] );
                    $p->SetRead($data, get_uid( false ), true);
				}?>
			</div>
				
                <? if (($offer['is_banned'] || $offer['usr_banned'] || $offer['user_blocked'] === 't')  && !hasPermissions('projects') && !$contest->is_owner) { ?>
    				<div class="suggest-comment-txt">
    				  Ответ от заблокированного пользователя
    				</div>
                <? } else { ?>
    				<div class="suggest-comment-txt" <?if($a_is_banned):?>style="color:silver"<?endif;?>>
                      <?php $sDescr   = $offer['moderator_status'] === '0' ? $stop_words->replace($offer['descr']) : $offer['descr']; ?>
    				  <?=reformat($sDescr, 30, 0, 0, 1)?>
    				</div>
    				
    				<div class="moderator_info">
                    <?php if ($offer['is_deleted'] == 't' && ($offer['user_id']) ) {?>
                        <div class="suggest-comment-txt" style="color:red; ">
                          Предложение удалено <?=($offer['user_id'] != $offer['deluser_id']?'модератором':'автором')?> <?php if (hasPermissions('projects')) {
                              $moderator = new users();
                              $moderator->GetUserByUID($offer['deluser_id']);
                              print $moderator->login ?> (<?=$moderator->uname ?> <?=$moderator->usurname ?>)                      
                          <?php }?>
                          <?php if($offer['deleted_reason']) {?>
                          <br/>
                          Причина: <?=$offer['deleted_reason']?>
                          <?php }?>
                        </div>
                    <?php }?>
                    </div>
    				
    				<? if (!empty($offer['attach'])) { ?>
    				<div class="suggest-portfolio ">
    					<ul>
    						<? $m = 1; foreach ($offer['attach'] as $attach) { if($m > 3) $m = 1;?>
                                <? $cClass = "portfpic{$m}"; $m++;?>
    							<? /* $sClass = ($uid && $attach['is_new']) ? 'sp-new' : '' */ ?>
    							<li class=" <?=$cClass?>" style="max-height:232px">
              <div class="b-layout__txt b-layout__txt_inline-block b-layout__txt_left">
                 <?php if ($uid && $attach['is_new']) {
                           $prjData = array ("id" => $contest->pid, "user_id" => $projects["user_id"], "kind"=>7);
                           $set_read_prj = new projects;
                           $set_read_prj->SetRead($prjData, get_uid(false) );
                 ?>

                    <div class="b-prev b-prev_width_208">
                       <dl class="b-prev__list">
                         <dt class="b-prev__dt b-prev__dt_active b-prev__dt_fontsize_9 b-prev__dt_top_3 b-prev__dt_bold">новое</dt>
                         <dd class="b-prev__dd">
                            <div class="b-prev__rama b-prev__rama_height_200">
                                      <table class="b-layout__table b-layout__table_width_200 b-layout__table_height_200" cellpadding="0" cellspacing="0" border="0">
                                         <tr class="b-layout__tr">
                                             <td class="b-layout__one b-layout__one_center b-layout__one_valign_middle">
                                                  <?php
                                                  $aData = getAttachDisplayData( $attach['upload_login'], $attach['filename'], "upload", 200, 200, 307200, $attach['prevname'] );
                                                  if ( $aData['file_mode'] || $aData['virus_flag'] || $aData['file_ext'] === 'swf') {
                                                  ?>
                                                  <div class="filesize">
                                                  <a class="b-layout__link" <?=$aData['link']?> target="_blank"><img class="b-layout__pic" src="/images/<?=$aData['file_ico']?>" alt="<?=$aData['file_name']?>" title="<?=$aData['file_name']?>" /></a>
                                                  <div><?=$aData['file_ext']?> <?=$aData['file_size_str']?>&nbsp;&nbsp;</div>
                                                  </div>
                                                  <?php
                                                  }
                                                  else {
                                                  
                                                  if ( $attach['prevname'] ) {
                                                  echo '<a class="b-layout__link" href="'.WDCPREFIX .'/users/'.$attach['upload_login'].'/upload/'.$attach['filename'].'" target="_blank" alt="'."{$foto_alt} фото {$attach['filename']}".'"><img class="b-layout__pic" src="'.WDCPREFIX.'/users/'.$attach['upload_login'].'/upload/'.$aData['file_name'].'" alt="'.$attach['filename'].'" title="'."{$foto_alt} фото {$attach['filename']}".'" width="'.$aData['img_width'].'" height="'.$aData['img_height'].'" /></a>';
                                                  }
                                                  else {
                                                  echo '<img class="b-layout__pic" src="'.WDCPREFIX.'/users/'.$attach['upload_login'].'/upload/'.$aData['file_name'].'" alt="'.$attach['filename'].'" title="'."{$foto_alt} фото {$aData['file_name']}".'" width="'.$aData['img_width'].'" height="'.$aData['img_height'].'" />';
                                                  }
                                                  }
                                                  ?>
                                             </td>
                                        </tr>
                                      </table>
                            </div>
                         </dd>
                       </dl>
                    </div>

                 <? } else {?>
                  <div class="b-layout__txt b-layout__txt_fontsize_10 b-layout__txt_color_808080"><?=dateFormat("Добавлено d.m.Y в H:i", $attach['post_date'])?></div>
                  <table class="b-layout__table b-layout__table_width_200 b-layout__table_height_200" cellpadding="0" cellspacing="0" border="0">
                     <tr class="b-layout__tr">
                         <td class="b-layout__one b-layout__one_center b-layout__one_valign_middle b-layout__one_bord_efeee2">
                  <?php
                  $aData = getAttachDisplayData( $attach['upload_login'], $attach['filename'], "upload", 200, 200, 307200, $attach['prevname'] );
                  if ( $aData['file_mode'] || $aData['virus_flag'] || $aData['file_ext'] === 'swf') {
                  ?>
                  <div class="filesize">
                  <a class="b-layout__link" <?=$aData['link']?> target="_blank"><img class="b-layout__pic" src="/images/<?=$aData['file_ico']?>" alt="<?=$aData['file_name']?>" title="<?=$aData['file_name']?>" /></a>
                  <div><?=$aData['file_ext']?> <?=$aData['file_size_str']?>&nbsp;&nbsp;</div>
                  </div>
                  <?php
                  }
                  else {
                  
                  if ( $attach['prevname'] ) {
                  echo '<a class="b-layout__link" href="'.WDCPREFIX .'/users/'.$attach['upload_login'].'/upload/'.$attach['filename'].'" target="_blank" alt="'."{$foto_alt} фото {$attach['filename']}".'"><img class="b-layout__pic" src="'.WDCPREFIX.'/users/'.$attach['upload_login'].'/upload/'.$aData['file_name'].'" alt="'.$attach['filename'].'" title="'."{$foto_alt} фото {$attach['filename']}".'" width="'.$aData['img_width'].'" height="'.$aData['img_height'].'" /></a>';
                  }
                  else {
                  echo '<img class="b-layout__pic" src="'.WDCPREFIX.'/users/'.$attach['upload_login'].'/upload/'.$aData['file_name'].'" alt="'.$attach['filename'].'" title="'."{$foto_alt} фото {$aData['file_name']}".'" width="'.$aData['img_width'].'" height="'.$aData['img_height'].'" />';
                  }
                  }
                  ?>
                         </td>
                    </tr>
                  </table>
                 <? } ?>
                  <div class="avs-contest"><span class="<?=$aData['virus_class']?>" <?=($aData['virus_class'] == 'avs-nocheck' ? 'title="Антивирусом проверяются файлы, загруженные после 1&nbsp;июня&nbsp;2011&nbsp;года"' : '')?>><nobr><?=$aData['virus_msg']?></nobr></span></div>
               </div>
    							</li>
    						<? } ?>
    					</ul>
    				</div>

    				<? } ?>
            <? } ?>
			
			
			
			<div class="thread-start" id="comments-place-<?=$offer['id']?>">
				<? 
					$i = 1; 
					$link = HTTP_PREFIX.$_SERVER['HTTP_HOST'].getFriendlyURL("project", $project['id'])."?offer={$offer['id']}#c-offer-{$offer['id']}";
					$closed = in_array($offer['id'], $cm);
				?>
				<ul class="thread-options">
                    <? if(($offer['user_id'] == $uid && $contest->positions[1]['user_id']!=$uid && $contest->positions[2]['user_id']!=$uid && $contest->positions[3]['user_id']!=$uid) || hasPermissions('projects')) { ?>
                        <? if($offer['is_deleted']=='f') { ?>
                            <li><a href="." id="rr_lnk_<?=$offer['id']?>" onclick="removeOffer(<?=$project['id']?>, <?=$offer['id']?>); return false;">Удалить</a></li>
                        <? } elseif ( $offer['deluser_id'] == $uid || hasPermissions('projects') ) { ?>
                            <li><a href="." id="rr_lnk_<?=$offer['id']?>" onclick="restoreOffer(<?=$project['id']?>, <?=$offer['id']?>); return false;">Восстановить</a></li>
                        <? } ?>
                    <? } ?>
                    
					<? if ($contest->is_moder) { ?><li class="t-o<?=$i++?>"><a href="." onclick="deleteOffer(<?=$offer['id']?>,<?=($offer['user_id']==get_uid()?'1':'0')?>); return false;">Удалить навсегда</a></li><? } ?>
					<? if (($contest->is_moder || $offer['user_id'] == $uid) && !$contest->is_banned && !$project['contest_end']) {
                        if ($project['pro_only'] === 't' && !$is_pro) { ?>
                            <li class="t-o<?=$i?>"><a href="javascript:void(0)" onclick="alert('Данная функция доступна только пользователям с аккаунтом PRO.')">Редактировать</a></li>
                        <? } elseif ($project['verify_only'] === 't' && !is_verify()) { ?>
                            <li class="t-o<?=$i?>"><a href="javascript:void(0)" onclick="alert('Данная функция доступна только верифицированным пользователям.')">Редактировать</a></li>
                        <? } else { ?>
                            <li class="t-o<?=$i?>"><a href="<?=getFriendlyURL("project", $project['id'])?>?offer-edit=<?=$offer['id']?>">Редактировать</a></li>
                        <? }
                    } ?>
					<? if ($contest->uid && !($offer['comm_blocked']=='t' && $uid!=$offer['user_id'] && $uid!=$project['user_id']) && !$contest->is_banned && !$project['contest_end']) { ?><li class="t-o<?=$i?>" id="comment<?=$offer['id'];?>" <?= ( $offer['is_deleted'] == 't' ? "style='display:none'" : "" ); ?>><a href="." onclick='comment.form(<?=$offer['id']?>, 0); return false;'>Комментировать</a></li><? } ?>
					<? if (($contest->is_owner || hasPermissions('projects')) && !$project['contest_end']  && !$offer['usr_banned']) { ?><li class="t-o<?=$i++?>"><a  href="<?=getFriendlyURL("project",$project['id'])?>?action=blockuser&uid=<?=$offer['user_id']?>&amp;offer=<?=$offer['id']?>" onclick="return confirm('<?=($offer['user_blocked']? 'Разблокировать': 'Заблокировать')?> пользователя?')"><?=($offer['user_blocked']? 'Разблокировать': 'Заблокировать')?> пользователя</a></li><? } ?>
					<li><a href="<?=$link?>" onclick="prompt('Ссылка на предложение', '<?=$link?>'); return false;">Ссылка</a></li>
					<li class="t-o3"><a  href="#top" onclick="return gotoTopComment(event)">Вверх</a></li>
<? if ($contest->is_moder) { ?>
<? if ( $offer['warn']<3 && !$offer['usr_banned'] && !$offer['usr_ban_where'] ) { ?>
<li class="t-o3"><span class="warnlink-<?=$offer['user_id']?>"><a style="color: red" href="." onclick='banned.warnUser(<?=$offer['user_id']?>, 0, "projects", "p<?=$project['id']?>", 0); return false;'>Сделать предупреждение</a> (<span class='warncount-<?=$offer['user_id']?>'><?=($offer['warn'] ? $offer['warn'] : 0)?></span>)</span></li>
<? } else { 
    $sBanTitle = (!$offer['usr_banned'] && !$offer['usr_ban_where']) ? 'Забанить!' : 'Разбанить';
    ?>
<li class="t-o3"><span class="warnlink-<?=$offer['user_id']?>"><a style="color:red;" href="javascript:void(0);" onclick="banned.userBan(<?=$offer['user_id']?>, 'p<?=$project['id']?>',0)"><?=$sBanTitle?></a></span></li>
<? } ?>
<? } ?>
					<li class="t-o4" id="commtree-<?=$offer['id']?>"><? /* if ($offer['new_comments']) { ?>(есть новые сообщения) <? } */ ?><? if ($offer['msg_count']) { ?><b><a id="to-<?=$offer['id']?>" href="." onclick="commentsTree(<?=$project['id']?>, <?=$offer['id']?>); checkClosed(<?=$offer['id']?>); return false;"><?=($closed? 'Развернуть': 'Свернуть')?> ветвь</a> (<span id="co-<?=$offer['id']?>"><?=(string) $offer['msg_count']?></span>)</b><? } else { ?>&nbsp;<? } ?></li>
				</ul>
<div id="warnreason-<?=$offer['id'].'-'.$offer['user_id']?>" style="display:none; padding: 0 0 5px 0px;">&nbsp;</div>
			</div>
		</div>
        <?
        $c_blocked = ($offer['comm_blocked']=='t' && $uid!=$offer['user_id'] && $uid!=$project['user_id']);
        ?>		
		<?=($offer['msg_count']? ('<ul class="thread-list"' . ($closed? ' style="display: none"': '') . ' id="comments-'.$offer['id'].'">'.comments($project['id'], $project['name'], $offer['comments'], ($c_blocked), $project['contest_end']).'</ul>'): '<ul style="display: none" id="comments-'.$offer['id'].'"><div style="display: none" id="to-'.$offer['id'].'"></ul>')?>
	</li>
	
	<? } ?>
	
	</ul>

	<? } ?>


<?
}  // $_GET['filter'] == 'banned'
?>

</div>

<script type="text/javascript">

	<?php if ((!$contest->is_emp || $contest->is_moder) && $uid && !$project['contest_end']): ?>

        var files = [ ];
        <? if ($contest->offer['attach']) { ?>
        var i = 0;
        <? foreach ($contest->offer['attach'] as $file) { ?>
        files[i++] = {
            filename: '<?=$file['fname']?>',
            displayname: '<?=addslashes($file['orig_name'])?>',
            preview: '<?=$file['prev_fname']?>',
            time: '<?=date('Добавлено d.m.Y в H:i', strtotime($file['modified']))?>',
            dir: '<?=$file['upload_login']?>',
            fileID: 'o<?=$file['id']?>'
        }
        <? } ?>
        <? } ?>

        var time_limit = <?=(ini_get('max_input_time'))?>;

        var iboxes_pid = '<?=$project['id']?>';
        var boxes_params = {
            path: '<?=WDCPREFIX?>/users/<?=$edit['login']?>/upload/',
            WDCPERFIX: '<?=WDCPREFIX?>',
            isAdd: <?=($project['pro_only'] == 't' && is_pro()) || $project['pro_only'] == 'f' ? 'true' : 'false'?>
        };
        var boxes, iboxes;
    
	<?php endif; ?>
    <? if($contest->offers && count($ofid) == count($close_all)): ?>
    document.getElementById('allct').innerHTML = 'Развернуть все ветви комментариев';
    <? endif; ?>
	<? if ($_GET['offer']) { ?>goAncor('c-offer-<?=intval($_GET['offer'])?>');<? } ?>
	<? if ($_GET['comm']) { ?>goAncor('c-comment-<?=intval($_GET['comm'])?>');<? } ?>
	<? if ($_GET['offer-edit']) { ?>goAncor('offer-edit');<? } ?>
	<? if ($contest->is_emp) { ?> 
        var comment_params = {is_emp: true, pro_html: '<?=view_pro_emp(); ?>'};
    <?} else {?>
        var comment_params = {is_emp: false, pro_html: '<?=view_pro(); ?>'};
    <? } ?>
</script>

<? } ?>
<div class="b-popup b-popup_center" id="note_user">
	 <b class="b-popup__c1"></b>
	 <b class="b-popup__c2"></b>
	 <b class="b-popup__t"></b>
	 <div class="b-popup__r">
		 <div class="b-popup__l">
			 <form class="b-popup__body " action="">
			    <input type="hidden" name="rating" id="note_rating" value="0<?//(int)$req['rating']?>">
                <input type="hidden" name="userid" id="note_userid" value="">
                <input type="hidden" name="userid" id="note_action" value=""> 
				<div class="b-textarea">
					<textarea class="b-textarea__textarea b-textarea__textarea__height_140" id="notesTxt" name="" cols="80" onkeyup="checknote(this)" rows="5"></textarea>
				</div>
				 <div class="b-popup__foot">
					<div class="b-buttons">
						<a class="b-buttons__link  b-popup__delete" href="javascript:void(0)" onclick="$(this).getParent('div.b-popup_center').setStyle('display', 'none'); return false;">Отменить</a>
						<a class="b-button b-button_rectangle_transparent" onclick="xajax_addNotes($('note_userid').get('value'), $('notesTxt').get('value'), $('note_rating').get('value'), $('note_action').get('value'), 100)" href="javascript:void(0)">
							<span class="b-button__b1">
								<span class="b-button__b2 b-button__b2_padlr_5">
									<span class="b-button__txt">Сохранить</span>
								</span>
							</span>
						</a>
					</div>
				 </div>
			 </form>
		 </div>
	 </div>
	 <b class="b-popup__b"></b>
	 <b class="b-popup__c3"></b>
	 <b class="b-popup__c4"></b>
</div>
<?php

if ($contest->is_moder) { 
    include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/ban_overlay.php' );
    include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/warn_overlay.php' );
}
?>


<? if($quick_verification==1 || $_GET['vok'] || $_GET['verror']) { $quick_verification_type = 'project'; require_once($_SERVER['DOCUMENT_ROOT'] . "/templates/quick_verification.php"); } ?>

<? if($_GET['quickprj_ok'] && $_SESSION['quickprj_ok']) { require_once($_SERVER['DOCUMENT_ROOT'] . "/templates/quick_buy_prj.php"); } ?>


<?php

//показываем попап успешной покупки ПРО после редиректа
$quickPRO_type = 'project'; 
require_once($_SERVER['DOCUMENT_ROOT'] . "/templates/quick_buy_pro_win.php"); 

?>

<div class="b-layout b-layout_padtop_20">
    <h2 class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_color_666 b-layout_top_100 b-layout__txt_padbot_10 b-layout__txt_weight_normal">
        <?= SeoTags::getInstance()->getFooterText() ?>
    </h2>
</div>

<style>.b-free-share__like{ display:none;}</style>

<? if(isset($_SESSION['new_public']) && $_SESSION['new_public']) { require_once($_SERVER['DOCUMENT_ROOT'] . "/projects/tpl.popup_share.php"); } ?>