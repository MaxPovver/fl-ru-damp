<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/projects.common.php");
$xajax->printJavascript('/xajax/');

if (!$_in_setup) {header ("HTTP/1.0 403 Forbidden"); exit;}
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/HTML/projects_lenta.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );
$projects = new projects();
$stop_words = new stop_words(hasPermissions('projects') );
if ($_POST['openclose']==1) { $_GET["open"]=1; $_GET["closed"]=0;} 
elseif ($_POST['openclose']==2) { $_GET["closed"]=1;  $_GET["open"]=0;} 

$account = new account();
$account->GetInfo(get_uid());
$transaction_id = $account -> start_transaction(get_uid());
$up_price = array ( 'kon' => new_projects::getPriceByCode(( is_pro() ? new_projects::OPCODE_KON_UP : new_projects::OPCODE_KON_UP_NOPRO )),
                    'prj' => new_projects::getPriceByCode(( is_pro() ? new_projects::OPCODE_UP : new_projects::OPCODE_UP_NOPRO )) );
$is_emp = is_emp();

$closed=($_GET["closed"] ? "true" : ($_GET["all"] ? "" : "false" ));
$kind = __paramInit('int', 'kind', 'kind', 0);
$prjs = $projects->GetCurPrjs(get_uid(), $closed, ($uid == get_uid()), hasPermissions('projects'), $kind);

//$is_new_offers = array_filter($prjs, create_function('$a', 'return ($a["unread_msgs"] > 0); '));
//$count_new_offers = 0;
//foreach($is_new_offers as $new_offers) {
//    $count_new_offers += $new_offers['unread_msgs'];
//}
$tip = notifications::getProjectsTipEmp();
$count_new_offers = $tip['count'];
$proj_groups = professions::GetAllGroupsLite();
$proj_groups_by_id = array();
foreach($proj_groups as $key => $wrk_prjgroup)
{
    $proj_groups_by_id[$wrk_prjgroup['id']] = $wrk_prjgroup['name'];
}
$daysOfWeek = array(1=>'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота', 'Воскресенье');
$conted_prj=$projects->CountMyProjects(get_uid(), ($uid == get_uid() || hasPermissions('projects')), false, $kind);

?>
<style type="text/css">
.lnk-feedback, .lnk-feedback:visited{
	display: inline-block;
	padding: 0 0 0 13px;
	background: #FF6B3D url(/images/icons-sprite.png) no-repeat -710px -232px;
	color: #fff;
	font-weight:900;
}
.lnk-feedback:hover{
	color: #fff;
	text-decoration: none;
}
.br-moderation-options .lnk-feedback {
    float: right;
	position: relative;
	top: 2px;
	right: 5px;
}
.br-mo-status{
    background: #FF6B3D url(/images/icons-sprite.png) no-repeat -872px -225px;
	color: #fff;
	padding: 2px 0 3px 25px;
}
.br-mo-info{
    padding: 2px 0 10px 25px;
	color: #FF6B3D;
}
.br-mo-info a{
	color: #FF6B3D;
}
.br-mo-info a:hover{
	color: #FF6B3D;
	text-decoration:none;
}
.chrome .public_plus_black .b-button{ position:relative; top:-1px;}
</style>

    <form action="/users/<?=$_SESSION['login']?>/setup/" id="frm"  name="frm" method="POST" >
    <div>
    <input type="hidden" name="action" value="" />
    <input type="hidden" name="openclose" value="<?=( ($_GET["open"]==1) ? 1 : (($_GET["closed"]==1) ? 2 : '') )?>" />
    <input type="hidden" name="prjid" value="" />
    <input type="hidden" name="transaction_id" value="<?=$transaction_id?>" />
    <input type="hidden" name="r" value="<?=$_SESSION['rand']?>" />
    </div>
    </form>

<script type="text/javascript">

function closeprj(num){
    document.getElementById('frm').prjid.value = num;
    document.getElementById('frm').action.value = 'prj_close';
    document.getElementById('frm').submit();
}
function upprj(num){
    document.getElementById('frm').prjid.value = num;
    document.getElementById('frm').action.value = 'prj_up';
    document.getElementById('frm').submit();
}

</script>
<table cellpadding="0" cellspacing="0" style="border:0;  width:100%">
<tr style="vertical-align:middle">
<td  style="padding:15px 10px 0px 10px">
<? include($_SERVER['DOCUMENT_ROOT']."/user/employer/tpl.filter-prj.php");?>
</td>
</tr>
</table>
            
			<span  style="width:100%; height:1px; margin:5px auto; display:block; padding:0; background:#d7d7d7"></span>
<? if($count_new_offers > 0) { ?>      
<div class="b-fon b-fon_padlr_10" id="new_offers_content">
		<div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf">
				<span class="b-icon b-icon_sbr_gcom b-icon_margleft_-20"></span>В ваших проектах было добавлено <?= $count_new_offers;?> <?= ending($count_new_offers, 'новое сообщение', 'новых сообщения', 'новых сообщений')?>. <a class="b-layout__link b-layout__link_bordbot_dot_0f71c8" href="javascript:void(0)" onclick="xajax_setReadAllProject();">Пометить все сообщения как прочитанные</a>
		</div>
</div>
<? }//if?>
            
   
<?

$i = 0;
if ($prjs) {
 //   print "<pre>";
  //  print_r($prjs);
   // print "</pre>";
   setlocale(LC_ALL, 'ru_RU.CP1251');
    $usr=new users();
    $name=$usr->GetName($_SESSION["uid"], $err);
    $dir = $name["login"];
    ?>
            <span  style="width:100%; height:1px; margin:5px auto; display:block; padding:0; background:#d7d7d7"></span>
    <?
    
    foreach ($prjs as $ikey=>$prj){
        $upText = $prj['kind'] == 7 ? 'ваш конкурс' : ($prj['kind'] == 4 ? 'вашу вакансию' : 'ваш проект');
        $spec = projects::getPrimarySpec($prj['id']);
        $prj['category'] = $spec['category_id'];
        if (is_new_prj($prj['post_date'])) {
            $blink = (!$is_emp && $prj['pro_only'] == 't' && !$is_pro && ($uid != $prj['user_id']))?"/proonly.php":getFriendlyURL("blog", $prj['thread_id']);
        } else {
            $blink = (!$is_emp && $prj['pro_only'] == 't' && !$is_pro && ($uid != $prj['user_id']))?"/proonly.php":getFriendlyURL("project", $prj['id']);
        }
        $plink = "/users/".$prj['login']."/project/?prjid=".$prj['id'];
        $is_konkurs = new_projects::isKonkurs($prj['kind']);
        $upprc = $up_price[$is_konkurs ? 'kon' : 'prj'];
        if ($prj['payed'] && !$is_konkurs) {
            ?>
            <table cellpadding="4" cellspacing="0" style="width:100%; border:0"><tr style=" vertical-align:top;"><td style="padding-left: 10px; padding-bottom: 5px;">
            <div class="fl2_date">
			<div class="fl2_date_day">
			<?=str_ago_pub(strtotimeEx($prj['create_date']))?>
			</div>
			<div class="fl2_date_date">
			<?=strftime("%d ",strtotimeEx($prj['create_date'])).monthtostr(strftime("%m",strtotimeEx($prj['create_date']))). ", " . $daysOfWeek[date("N",strtotimeEx($prj['create_date']))]?>
			</div>
			<div class="clear"></div>
		</div>
            <div class="hr"></div>
            <div class="fl2_offer bordered" style="overflow:hidden;">
            	<div class="fl2_offer_logo">
                    <div>Платный проект</div>
					<? if ($prj['logo_name']) {?>
                        <a href="http://<?= formatLink($prj['link'])?>" target="_blank" nofollow ><img class="b-layout__pic b-layout__pic_float_right b-layout__pic_clear_right" alt="" src="<?=WDCPREFIX.'/'.$prj['logo_path'].$prj['logo_name']?>" /></a>
                    <? } else {?>
                        <img class="b-layout__pic b-layout__pic_float_right b-layout__pic_clear_right" src="/images/public_your_logo.gif" alt="" />
                    <? }?> 
                    <? if ($prj['cost']) { $priceby_str = getPricebyProject($prj['priceby']);?>
                        <div class="fl2_offer_budget">Бюджет: <?=CurToChar($prj['cost'], $prj['currency']).$priceby_str?></div>
                    <? } else { ?>
                        <var class="bujet-dogovor">По договоренности</var>
                    <? } ?>
                </div>
            <div class="fl2_offer_header"> 
            <? /* #0019741 if ($prj['prefer_sbr']=='t') {?><img src="/images/sbr_p.gif" class="sbr_p" title="Работодатель&nbsp;хочет&nbsp;работать&nbsp;через&nbsp;Cделку&nbsp;без&nbsp;риска" alt="Работодатель хочет работать через Cделку без риска"><? } */?>
            <? if ($prj['sbr_id']) { ?><a href="/<?= sbr::NEW_TEMPLATE_SBR; ?>/?id=<?=$prj['sbr_id']?>"><img src="/images/shield_sm.gif" alt="" /></a><? } ?>
            <?if ($prj['ico_closed']=='t') {?><img src="/images/ico_closed.gif" alt="Проект закрыт" /><?}?>
                <?php $sTitle = $prj['moderator_status'] === '0' && $prj['kind'] != 4 && !is_pro() ? $stop_words->replace($prj['name']) : $prj['name']; ?>
            	<a name="/proonly.php" href="<?=$blink?>?f=<?= $_SESSION['login'] ?>" class="fl2_offer_header" title=""><?=reformat($sTitle, 30, 0, 1)?></a>
            </div>
            <div class="fl2_offer_content">
              <?php $sDescr = $prj['moderator_status'] === '0' && $prj['kind'] != 4 && !is_pro() ? $stop_words->replace($prj['descr']) : $prj['descr']; ?>
              <?=ereg_replace("\r","",ereg_replace("\n","",reformat($sDescr, 50, 0, 0, 1)))?>
            </div><?

            $attach=$projects->GetAllAttach;
            for ($i=0;$i<count($attach);$i++) {?>
                <div class="flw_offer_attach">
                <a href="/users/<?=$dir?>/upload/<?=$attach[$i]['name']?>" target="_blank">Загрузить</a> (<?=$attach[$i]['ftype']?>; <?=ConvertBtoMB($attach[$i]['size'])?> )
                </div>
            <?}?>
            <br />
            <div class="fl2_offer_meta">Прошло времени с момента публикации: <?=ago_pub_x(strtotimeEx($prj['create_date']))?>
            <br />
            Автор: <a href="/users/<?=$name["login"]?>"><? print $name["uname"]." "; print $name["usurname"]; ?> [<?=$name["login"]?>]</a><br />
            Раздел: <?=projects::getSpecsStr($prj['id'],' / ', ', ');?>
            <?if ($prj['pro_only']=='t') {?><br /><span  class="b-layout__txt b-layout__txt_fontsize_11" style="background-color:#fff7ee;">Отвечать на проект могут только пользователи с аккаунтом <a class="b-layout__link" href="/payed/"><span class="b-icon b-icon_top_3 b-icon__pro b-icon__pro_f " title="Платный аккаунт" alt="Платный аккаунт"></span></a> &#160;<? } ?>
            <? /*if ($prj['prefer_sbr']=='t') {?><br />Предпочитаю работать через БС <a class="b-txt__lnk b-txt__lnk_fs_11 b-txt__lnk_lh_1" href="/promo/bezopasnaya-sdelka/"><span class="b-icon b-icon__shield"></span></a></span><?} */ ?>
            <div class="fl2_comments_link"><div style="padding:12px 0px 0px 0px;"></div></div>
			<? if (!$prj['is_blocked']) { ?>
            <?php if(!($prj["closed"]=="t"&&!$prj["frl_id"])) { ?>
            <table cellpadding="2" cellspacing="0" style="border:0">
            <tr style="vertical-align:middle">
            <?php if(!projects::isProjectOfficePostedAfterNewSBR($prj)) { ?>
            <td><img src="/images/ico_setup.gif" alt="" />&#160;</td>
            <td><a class="public_blue" href="/public/?step=1&public=<?=$prj["id"]?>&red=<?=rawurlencode("/users/".$name["login"]."/setup/projects/")?>">Редактировать</a></td>
            <td>&nbsp;&nbsp;&nbsp;</td>
            <?php } ?>
            <td>
                <? if( $prj["kind"] != 7 && $prj["kind"] != 2 ) { ?>
                <?php if(!(projects::isProjectOfficePostedAfterNewSBR($prj) && $prj["closed"]=='t')) { ?>
                <img src="/images/<?=($prj["closed"]=='t' ? "ico_reopen.gif" : "ico_close_round.gif")?>" alt="" />&#160;
                <?php } ?>
                <? } ?>
            </td>
            <td>
                <?if ($prj['sbr_id'] && $prj['sbr_is_draft'] != 't') { $sbr_site =  (strtotime($prj['create_date']) > mktime(0,0,0, 10, 5, 2012) ? sbr::NEW_TEMPLATE_SBR : 'norisk2');?>
                <a class="public_black" href="/<?= $sbr_site; ?>/?id=<?=$prj['sbr_id']?>">Безопасная Сделка</a>
                <? }elseif ($prj["closed"]=='t') {?>
                    <?php if(!projects::isProjectOfficePostedAfterNewSBR($prj)) { ?>
                    <a class="public_black" href="#"   onclick="closeprj(<?=$prj["id"]?>);">Публиковать еще раз</a>
                    <?php } ?>
                <?} else {?>
                    <? if( $prj["kind"] != 7 ) { ?>
                        <a class="public_blue" href="#"   onclick="closeprj(<?=$prj["id"]?>);">Снять с публикации</a>
                    <? } ?>
                <?}?>
            </td>
            </tr>
            </table>
            <? } ?>
            <? } ?>
			
            </div>
            </td> 
            <td style="width:240px">
            
            <? if (!$prj['is_blocked']) { $now = (date('dmY', strtotime($prj['create_date'])) == date('dmY'));?>
			<table  cellpadding="2" cellspacing="0" style="border:0">
            <tr>
                <td>&nbsp;</td>
                <td><div class="b-layout__txt b-layout__txt_padbot_30 b-layout__txt_fontsize_11"><b>Статистика по объявлению:</b><br />
                <? if ($prj["is_new_offers"] == 't') { ?><img src="/images/ico_projects_an.gif" alt="" width="10" height="10" class="new-offer-image" id="new_offer_<?=$prj['id']?>" /> <b><? } ?><?
        if (is_new_prj($prj['post_date'])) {
?>
            <?=((!$prj["comm_count"] || $prj["comm_count"] % 10==0 || $prj["comm_count"] % 10 >4 || ($prj["comm_count"] >4 &&  $prj["comm_count"]<21)) ?  '<a class="public_blue" href="'.getFriendlyURL("blog", $prj['thread_id']).'">'.$prj["comm_count"].' предложений</a>' : (($prj["comm_count"] % 10 == 1 || $prj["comm_count"]==1) ?  '<a class="public_blue" href="'.getFriendlyURL("blog", $prj['thread_id']).'">'.$prj["comm_count"].' предложение</a>' : '<a class="public_blue" href="'.getFriendlyURL("blog", $prj['thread_id']).'">'.$prj["comm_count"].' предложения</a>'  )   )?>
            <? if ($prj["is_new_offers"] == 't') { ?></b><? } ?>
<?
        }
        else {
?>
            <?=((!$prj["offers_count"] || $prj["offers_count"] % 10==0 || $prj["offers_count"] % 10 >4 || ($prj["offers_count"] >4 &&  $prj["offers_count"]<21)) ?  '<a class="public_blue" href="'.$blink.'">'.$prj["offers_count"].' предложений</a>' : (($prj["offers_count"] % 10 == 1 || $prj["comm_count"]==1) ?  '<a class="public_blue" href="'.$blink.'">'.$prj["offers_count"].' предложение</a>' : '<a class="public_blue" href="'.$blink.'">'.$prj["offers_count"].' предложения</a>'  )   )?>
            <? if ($prj["is_new_offers"] == 't') { ?></b><? } ?>
<?
        }
?>
                <br/>
закладка "<?=GetKind($prj['kind'])?>"<br/>
                <?
                if ($prj["closed"]=="t") { ?><? } elseif($now) {
            $payed=(($prj["top_to"]>$prj["now"] && $prj["payed"]) ? 1 : 0 );
            $counte=$projects->CountProjectNew($prj['post_date'], $prj['kind'], $prj['top_from'], $prj['top_to'], $prj['strong_top']);
            $page=floor($counte/$GLOBALS["prjspp"])+1;
            $counte_page=$counte % $GLOBALS["prjspp"];
            if ($counte_page == 0) {
                $counte_page = $GLOBALS["prjspp"];
                $page--;
            }
            ?>
            <a class="public_blue" href="/projects/?kind=<?=$prj['kind']?>&page=<?=$page?>#prj<?=$prj['id']?>"><?=$counte_page?>-е по счету (<?=$page?>-я страница)</a>
            <?} else {?>
            <a class="stat-more" id="pos_link_<?=$prj['id']?>" href="javascript:void(0)" onclick="xajax_getPositionProject(<?=$prj['id']?>, '<?=$prj['top_to']?>', '<?=$prj['now']?>', '<?=$prj['payed']?>', '<?=$prj['post_date']?>', '<?=$prj['kind']?>');">Подробнее...</a>
            <span id="prj_pos_<?=$prj['id']?>"></span>
            <?} //else?>
           </div></td>
            </tr>
            <? if (!is_new_prj($prj["post_date"]) && $prj['is_blocked'] != "t" && $prj['closed'] != "t" && !projects::isProjectOfficePostedAfterNewSBR($prj)) {?>
            <tr style="vertical-align:top">
            <td style="padding: 8px 5px 0 10px;"></td>
            <?php /*<td><div class="public_plus"><a href="/public/?step=2&public=<?=$prj["id"]?>&red=<?=rawurlencode("/users/".$name["login"]."/setup/projects/")?>" class="public_blue">Купить платное объявление</a></div>Вы можете закрепить ваше объявление вверху на любой срок, выделить его среди остальных */ ?>
            <td><div class="public_plus"><a href="/public/?step=1&public=<?= $prj['id'] ?>" class="b-button b-button_flat b-button_flat_green b-button_height_30 b-button_block">Получить еще предложений</a></div>
            </td>
            </tr>
            <?}
            //print_r($prj);
            ?>
            
            
			</table>
			<? } ?>
            </td>
            <td  class="public_plus_black" style="vertical-align:top; padding-top:78px; text-align:center"><div class="b-layout__txt"><?
            if (($prj["closed"]=="t" && !$prj['sbr_id']) || $prj['is_blocked']) {
                $str = 'Снято с публикации<br />';
            } else if ($prj['exec_id'] && $prj['sbr_id'] && $prj['sbr_status'] < sbr::STATUS_CHANGED) {
                $str = 'Возможный ' . ($prj['kind'] == 2 || $prj['kind'] == 7 ? 'победитель' : 'исполнитель') . ' определен:<br /><a class="blue" href="/users/' . $prj['exec_login'] . '">' . $prj['exec_name'] . ' ' . $prj['exec_surname'] . ' ' . '[' . $prj['exec_login'] . "]</a><br />";
			} else if ($prj['exec_id']) {
				if (is_array($prj['exec_id'])) {
					$str = (count($prj['exec_id']) > 1)? (($prj['kind']==2||$prj['kind']==7)?'Победители':'Исполнители').' определены:<br />': (($prj['kind']==2||$prj['kind']==7)?'Победитель':'Исполнитель').' определен:<br />';
					for ($i=0;$i<count($prj['exec_id']);$i++) {
						$str .= '<a class="blue" href="/users/' . $prj['exec_id'][$i]['login'] . '">' . $prj['exec_id'][$i]['uname'] . ' ' . $prj['exec_id'][$i]['usurname'] . ' ' . '[' . $prj['exec_id'][$i]['login'] . "]</a><br />";
					}
				} else {
					$str = (($prj['kind']==2||$prj['kind']==7)?'Победитель':'Исполнитель').' определен:<br /><a class="blue" href="/users/' . $prj['exec_login'] . '">' . $prj['exec_name'] . ' ' . $prj['exec_surname'] . ' ' . '[' . $prj['exec_login'] . "]</a><br />";
				}
			} else {
                $str = "Ищется исполнитель<br />";
			}
			print ($str);

            if ($prj['is_blocked']) print "<b>Проект заблокирован</b>";
            elseif (!$prj['sbr_id']) { /*?>
                <a href="/<?= sbr::NEW_TEMPLATE_SBR ?>/?site=create&pid=<?= $prj['id'] ?>" class="b-button b-button_flat b-button_flat_green b-button_margtop_10"><span id="top-payed-buybtn-text">Начать сделку</span></a>
            <?*/ } elseif ($prj['sbr_is_draft'] == 't')  print "<a href=\"/".sbr::NEW_TEMPLATE_SBR."/?id={$prj['sbr_id']}\" class=\"blue\">Тех. задание не отправлено</a>";
            elseif ($prj['sbr_status'] == sbr::STATUS_NEW)  print "<a href=\"/".sbr::NEW_TEMPLATE_SBR."/?id={$prj['sbr_id']}\" class=\"blue\">Тех. задание не утверждено</a>";
            elseif (!$prj['sbr_reserved_id'])  print "<a href=\"/".sbr::NEW_TEMPLATE_SBR."/?id={$prj['sbr_id']}\" class=\"blue\">Деньги не зарезервированы</a>";
            else print "<a href=\"/".sbr::NEW_TEMPLATE_SBR."/?id={$prj['sbr_id']}\" class=\"blue\">" . sbr::$ss_classes[$prj['sbr_status']][2] . "</a>";
			?>

			</div></td>
            </tr></table>

			<div id="project-reason-<?=$prj['id']?>" style="margin: 10px 20px 10px 20px;<?=($prj['is_blocked']? 'display: block': 'display: none')?>"><? 
			if ($prj['is_blocked']) {
				$moder_login = (hasPermissions('projects'))? $prj['admin_login']: '';
				print HTMLProjects::BlockedProject($prj['blocked_reason'], $prj['blocked_time'], $moder_login, "{$prj['admin_name']} {$prj['admin_uname']}");
			} else {
				print '&nbsp;';
			}
			?></div>
						<div style="height:1px; margin:5px 0; background:#d7d7d7; width:100%;"></div>
<?php
        }
        else 
        {
            
            $is_personal = ($prj['kind'] == 9);
            
?>                                      
        <table cellpadding="4" cellspacing="0" style="border:0; width:100%;"><tr style="vertical-align:top"><td style="padding-left: 10px;">
        <div class="fl2_date">
			<div class="fl2_date_day">
			<?=str_ago_pub(strtotimeEx($prj['create_date']))?>
			</div>
			<div class="fl2_date_date">
			<?=strftime("%d ",strtotimeEx($prj['create_date'])).monthtostr(strftime("%m",strtotimeEx($prj['create_date']))). ", " . $daysOfWeek[date("N",strtotimeEx($prj['create_date']))]?>
			</div>
			<div class="clear"></div>
		</div>
            <div class="fl2_offer">
            <? if ($prj['logo_name']) {?>
            <div class="fl2_offer_logo">
                <a href="http://<?= formatLink($prj['link'])?>" target="_blank" nofollow ><img class="b-layout__pic b-layout__pic_float_right b-layout__pic_clear_right"  src="<?=WDCPREFIX.'/'.$prj['logo_path'].$prj['logo_name']?>" alt="" /></a>
            </div>
            <? }?>
            <?if ($prj['cost']) { $priceby_str = getPricebyProject($prj['priceby']);?>
                <div class="fl2_offer_budget">Бюджет: <?=CurToChar($prj['cost'], $prj['currency']).$priceby_str?></div>
            <? } else { ?>
                <var class="bujet-dogovor">По договоренности</var>
            <? } ?>
            <div class="fl2_offer_header"> 
                <? /* #0019741 if ($prj['prefer_sbr']=='t') {?><img src="/images/sbr_p.gif" class="sbr_p" title="Работодатель&nbsp;хочет&nbsp;работать&nbsp;через&nbsp;Cделку&nbsp;без&nbsp;риска" alt="Работодатель хочет работать через Cделку без риска"><? } */?>
                <? if ($prj['sbr_id']) { ?><a href="/<?= sbr::NEW_TEMPLATE_SBR; ?>/?id=<?=$prj['sbr_id']?>"><img src="/images/shield_sm.gif" alt="" /></a><? } ?>
                <?if ($prj['ico_closed']=='t') {?><img src="/images/ico_closed.gif" alt="Проект закрыт" /><?}?>
                <?php $sTitle = $prj['moderator_status'] === '0' && $prj['kind'] != 4 && !is_pro() ? $stop_words->replace($prj['name']) : $prj['name']; ?>
                <?php $sDescr = $prj['moderator_status'] === '0' && $prj['kind'] != 4 && !is_pro() ? $stop_words->replace($prj['descr']) : $prj['descr']; ?>
                <a href="<?=$blink?>?f=<?= $_SESSION['login'] ?>"><?=reformat($sTitle, 30, 0, 1)?></a>
                
                <?php if($is_personal): ?>
                <div class="b-layout__txt b-layout__txt_bold b-layout__txt_fontsize_11">
                    Персональный проект для <?=$prj['personal_fullname']?>
                </div>
                <?php endif; ?>
            </div>
                
            <div class="fl2_offer_content"><?=ereg_replace("\r","",ereg_replace("\n","",reformat($sDescr, 50, 0, 0, 1)))?></div>
            <?php
                $attach=$projects->GetAllAttach;
                for ($i=0;$i<count($attach);$i++) 
                {
            ?>
            <div class="flw_offer_attach"><a href="/users/<?=$dir?>/upload/<?=$attach[$i]['name']?>" target="_blank">Загрузить</a> (<?=$attach[$i]['ftype']?>; <?=ConvertBtoMB($attach[$i]['size'])?> )</div>
            <?php
                }
            ?>
                <br />
                <div class="fl2_offer_meta">
                    Прошло времени с момента публикации: <?=ago_pub_x(strtotimeEx($prj['create_date']))?>
                    <br />Автор: <a href="/users/<?=$name["login"]?>"><? print $name["uname"]." "; print $name["usurname"]; ?> [<?=$name["login"]?>]</a>
                    <?php if(!$is_personal): ?>
                    <br />Раздел: <?=projects::getSpecsStr($prj['id'],' / ', ', ');?>
                    <?php endif; ?>
                </div>
                <? if ($prj['pro_only']=='t') { ?>
                    <br />
                    <span  class="b-layout__txt b-layout__txt_fontsize_11" style="background-color:#fff7ee;">Отвечать на проект могут только пользователи с аккаунтом <a class="b-layout__link" href="/payed/"><span class="b-icon b-icon_top_3 b-icon__pro b-icon__pro_f " title="Платный аккаунт" alt="Платный аккаунт"></span></a></span>
                <? } ?>
                <? if ($prj['verify_only']=='t' ) { ?>
                    <br />
                    <span  class="fl2_offer_meta2" style="background-color:#fff7ee;">Отвечать на проект могут только пользователи с верифицированным аккаунтом<?= view_verify('верифицированный аккаунт') ?></span>
                <? } ?>
                <? /*if ($prj['prefer_sbr']=='t' ) { ?>
                    <br />
                   <span  class="b-layout__txt b-layout__txt_fontsize_11" style="background-color:#fff7ee;">Предпочитаю работать через БС <a class="b-txt__lnk b-txt__lnk_fs_11 b-txt__lnk_lh_1" href="/promo/bezopasnaya-sdelka/"><span class="b-icon b-icon__shield"></span></a></span>
                <? } */ ?>
                <div class="fl2_comments_link"><div style="padding:12px 0px 0px 0px;"></div></div>
            <? if (!$prj['is_blocked'] && (!$prj['end_date'] || strtotime($prj['end_date']) > time())) { ?>
            <?php if(!($prj["closed"]=="t"&&!$prj["frl_id"])) { ?>
			<table cellpadding="2" cellspacing="0" style="border:0">
            <tr style="vertical-align:middle">
            <?php if(!projects::isProjectOfficePostedAfterNewSBR($prj)) { ?>
            <td><img src="/images/ico_setup.gif" alt="" />&#160;</td>
            <td><a class="public_blue" href="/public/?step=1&public=<?=$prj["id"]?>&red=<?=rawurlencode("/users/".$name["login"]."/setup/projects/")?>">Редактировать</a></td>
            <td>&nbsp;&nbsp;&nbsp;</td>
            <?php } ?>
            <td>
                <? if( $prj["kind"] != 7 ) { ?>
                    <?php if(!(projects::isProjectOfficePostedAfterNewSBR($prj) && $prj["closed"]=='t')) { ?>
                    <img src="/images/<?=(($prj["closed"]=='t' || $prj["is_accepted"] =='t') ? "ico_reopen.gif" : "ico_close_round.gif")?>" alt="" />&#160;
                    <?php } ?>
                <? } ?>
            </td>
            <td>
                <?if ($prj['sbr_id'] && $prj['sbr_is_draft'] != 't') { $sbr_site =  (strtotime($prj['create_date']) > mktime(0,0,0, 10, 5, 2012) ? sbr::NEW_TEMPLATE_SBR : 'norisk2'); ?>
                <a class="public_black" href="/<?= $sbr_site;?>/?id=<?=$prj['sbr_id']?>">Безопасная Сделка</a>
                <? } elseif ($prj["closed"]=='t') { ?>
                    <?php if(!projects::isProjectOfficePostedAfterNewSBR($prj)) { ?>
                    <a class="public_black" href="" onclick="closeprj(<?=$prj["id"]?>); return false;">Публиковать еще раз</a>
                    <?php } ?>
                <? } else { ?>
                    <? if( $prj["kind"] != 7 ) { ?>
                        <a class="public_blue" href="" onclick="closeprj(<?=$prj["id"]?>); return false;">Снять с публикации</a>
                    <? } ?>
                <? } ?>
            </td>
            </tr>
            </table>
			<? } ?>
			<? } ?>
            </div>
         </td> 
            <td style="width:240px">
            <?php if(!$is_personal): ?>
                
            <? if (!$prj['is_blocked']) { $now = (date('dmY', strtotime($prj['create_date'])) == date('dmY'));?>
			<table  cellpadding="2" cellspacing="0" style="border:0">
            <tr>
                <td>&nbsp;</td>
                <td><div class="b-layout__txt b-layout__txt_padbot_30 b-layout__txt_fontsize_11"><b>Статистика по объявлению:</b><br />
                <? if ($prj["is_new_offers"] == 't') { ?><img src="/images/ico_projects_an.gif" alt="" width="10" height="10" class="new-offer-image" id="new_offer_<?=$prj['id']?>"  /> <b><? } ?><?
        if (is_new_prj($prj['post_date'])) {
?>
            <?=((!$prj["comm_count"] || $prj["comm_count"] % 10==0 || $prj["comm_count"] % 10 >4 || ($prj["comm_count"] >4 &&  $prj["comm_count"]<21)) ?  '<a class="public_blue" href="'.getFriendlyURL("blog", $prj['thread_id']).'">'.$prj["comm_count"].' предложений</a>' : (($prj["comm_count"] % 10 == 1 || $prj["comm_count"]==1) ?  '<a class="public_blue" href="'.getFriendlyURL("blog", $prj['thread_id']).'">'.$prj["comm_count"].' предложение</a>' : '<a class="public_blue" href="'.getFriendlyURL("blog", $prj['thread_id']).'">'.$prj["comm_count"].' предложения</a>'  )   )?>
            <? if ($prj["is_new_offers"] == 't') { ?></b><? } ?>
<?
        }
        else {
?>
            <?=((!$prj["offers_count"] || $prj["offers_count"] % 10==0 || $prj["offers_count"] % 10 >4 || ($prj["offers_count"] >4 &&  $prj["offers_count"]<21)) ?  '<a class="public_blue" href="'.$blink.'">'.$prj["offers_count"].' предложений</a>' : (($prj["offers_count"] % 10 == 1 || $prj["comm_count"]==1) ?  '<a class="public_blue" href="'.$blink.'">'.$prj["offers_count"].' предложение</a>' : '<a class="public_blue" href="'.$blink.'">'.$prj["offers_count"].' предложения</a>'  )   )?>
            <? if ($prj["is_new_offers"] == 't') { ?></b><? } ?>
<?
        }
?><br/>
закладка "<?=GetKind($prj['kind'])?>"<br/>
                <?
                if ($prj["closed"]=="t") { ?><? } elseif($now) {
            $payed=(($prj["top_to"]>$prj["now"] && $prj["payed"]) ? 1 : 0 );
            $counte=$projects->CountProjectNew($prj['post_date'], $prj['kind'], $prj['top_from'], $prj['top_to'], $prj['strong_top']);
            $page=floor($counte/$GLOBALS["prjspp"])+1;
            $counte_page=$counte % $GLOBALS["prjspp"];
            if ($counte_page == 0) {
                $counte_page = $GLOBALS["prjspp"];
                $page--;
            }
            
            ?>
            <a class="public_blue" href="/projects/?kind=<?=$prj['kind']?>&page=<?=$page?>#prj<?=$prj['id']?>"><?=$counte_page?>-е по счету (<?=$page?>-я страница)</a>
            <?} else {?>
            <a class="stat-more" id="pos_link_<?=$prj['id']?>" href="javascript:void(0)" onclick="xajax_getPositionProject(<?=$prj['id']?>, '<?=$prj['top_to']?>', '<?=$prj['now']?>', '<?=$prj['payed']?>', '<?=$prj['post_date']?>', '<?=$prj['kind']?>');">Подробнее...</a>
            <span id="prj_pos_<?=$prj['id']?>"></span>
            <?}?>
            </div></td>
            </tr>
            <?
            if ($prj['is_blocked'] != "t" && $prj['closed'] != "t" && !projects::isProjectOfficePostedAfterNewSBR($prj) ) {?>
            <tr style="vertical-align:top">
            <td style="padding: 8px 5px 0 10px;"></td>
            <?php /*<td><div class="public_plus"><a href="/public/?step=2&public=<?=$prj["id"]?>&red=<?=rawurlencode("/users/".$name["login"]."/setup/projects/")?>" class="public_blue">Купить платное объявление</a></div>Вы можете закрепить ваше объявление вверху на любой срок, выделить его среди остальных */ ?>
            <td><div class="public_plus"><a href="/public/?step=1&public=<?= $prj['id'] ?>" class="b-button b-button_flat b-button_flat_green b-button_height_30 b-button_block">Получить еще предложений</a></div>
            </td>
            </tr>
            <?} else { ?>
            <tr style="vertical-align:top">
            <td style="padding: 8px 5px 0 10px;"> </td>
            <td> </td>
            </tr>
            <? } ?>
            <tr style="vertical-align:top">
            <td style="padding: 8px 5px 0 10px;"> </td>
            <td> </td>
            </tr>
            </table>
			<? } ?>
            <br />
            
            <?php endif; ?>
            </td>
            <td class="public_plus_black"  style="vertical-align:top; text-align:center; padding-top:78px;"><div class="b-layout__txt"><?
            if ($prj["closed"]=="t" && !$prj['sbr_id'] || $prj['is_blocked']) {
				$str = 'Снято с публикации<br />';
            } else if ($prj['exec_id'] && $prj['sbr_id'] && $prj['sbr_status'] < sbr::STATUS_CHANGED && $prj['kind'] != 7) {
                $str = 'Возможный исполнитель определен:<br /><a class="blue" href="/users/' . $prj['exec_login'] . '">' . $prj['exec_name'] . ' ' . $prj['exec_surname'] . ' ' . '[' . $prj['exec_login'] . "]</a><br />";
            } else if ($prj['exec_id']) {
				if (is_array($prj['exec_id'])) {
					$str = (count($prj['exec_id']) > 1)? (($prj['kind']==2||$prj['kind']==7)?'Победители':'Исполнители').' определены:<br />': (($prj['kind']==2||$prj['kind']==7)?'Победитель':'Исполнитель').' определен:<br />';
					for ($i=0;$i<count($prj['exec_id']);$i++) {
						$str .= '<a class="blue" href="/users/' . $prj['exec_id'][$i]['login'] . '">' . $prj['exec_id'][$i]['uname'] . ' ' . $prj['exec_id'][$i]['usurname'] . ' ' . '[' . $prj['exec_id'][$i]['login'] . "]</a><br />";
					}
				} else {
					$str = (($prj['kind']==2||$prj['kind']==7)?'Победитель':'Исполнитель').' определен:<br /><a class="blue" href="/users/' . $prj['exec_login'] . '">' . $prj['exec_name'] . ' ' . $prj['exec_surname'] . ' ' . '[' . $prj['exec_login'] . "]</a><br />";
				}
			} else {
                if ($prj['kind']==2||$prj['kind']==7) {
                    $str = "Победитель не определен<br />";
                } else {
                    $str = "Ищется исполнитель<br />";
                }
			}
			print ($str);

			if ($prj['is_blocked']) {
                print "<b>Проект заблокирован</b>";
            } elseif ($is_konkurs && empty($prj['exec_id'])) {
                // ничего не выводим
            } elseif (!$prj['sbr_id']) { /*?>
                <a href="/<?= sbr::NEW_TEMPLATE_SBR ?>/?site=create&pid=<?= $prj['id'] ?>" class="b-button b-button_flat b-button_flat_green b-button_margtop_10"><span id="top-payed-buybtn-text">Начать сделку</span></a>
            <?*/ } elseif ($prj['sbr_is_draft'] == 't')  print "<a href=\"/".sbr::NEW_TEMPLATE_SBR."/?id={$prj['sbr_id']}\" class=\"blue\">Тех. задание не отправлено</a>";
            elseif ($prj['sbr_status'] == sbr::STATUS_NEW)  print "<a href=\"/".sbr::NEW_TEMPLATE_SBR."/?id={$prj['sbr_id']}\" class=\"blue\">Тех. задание не утверждено</a>";
            elseif (($prj['sbr_status'] == sbr::STATUS_PROCESS || $prj['sbr_status'] == sbr::STATUS_CHANGED) && !$prj['sbr_reserved_id'])  print "<a href=\"/".sbr::NEW_TEMPLATE_SBR."/?id={$prj['sbr_id']}\" class=\"blue\">Деньги не зарезервированы</a>";
            else print "<a href=\"/".sbr::NEW_TEMPLATE_SBR."/?id={$prj['sbr_id']}\" class=\"blue\">" . sbr::$ss_classes[$prj['sbr_status']][2] . "</a>";
			?>


			</div></td>
            </tr>  </table>
            
			<div id="project-reason-<?=$prj['id']?>" style="margin: 10px 20px 10px 20px;<?=($prj['is_blocked']? 'display: block': 'display: none')?>"><? 
			if ($prj['is_blocked']) {
				$moder_login = (hasPermissions('projects'))? $prj['admin_login']: '';
				print HTMLProjects::BlockedProject($prj['blocked_reason'], $prj['blocked_time'], $moder_login, "{$prj['admin_name']} {$prj['admin_uname']}");
			} else {
				print '&nbsp;';
			}
			?></div>
			
			<span  style="width:100%; height:1px; margin:5px auto; display:block; padding:0; background:#d7d7d7"></span>
            
            <?}
    }

}else {
    $entity = "вас";
    $style = "padding-left:50px; padding-top:20px;";
    require_once dirname(__FILE__)."/../tpl.noprojects.php";        
}
    ?>
