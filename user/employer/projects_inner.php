<? 
if(!defined('IN_STDF')) { 
    header("HTTP/1.0 404 Not Found");
    exit();
}
//require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/projects.common.php");
//$xajax->printJavascript('/xajax/');

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/HTML/projects_lenta.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );
$projects = new projects();
$stop_words = new stop_words(hasPermissions('projects') );
if ($_POST['openclose']==1) { $_GET["open"]=1; $_GET["closed"]=0;} 
elseif ($_POST['openclose']==2) { $_GET["closed"]=1;  $_GET["open"]=0;} 

$uid = $user->uid;

$is_emp = is_emp();

$is_owner = $uid == get_uid(false);
$is_adm = !$is_owner && hasPermissions('projects');

//print_r($_POST);

$closed=($_GET["closed"] ? "true" : ($_GET["all"] ? "" : "false" ));
$kind = __paramInit('int', 'kind', 'kind', 0);
$trash = __paramInit('int', 'trash', 'trash', 0);

if ($trash) {
    
    //Отображаем и открытые, и закрытые
    $closed = '';
    
    if (!($is_owner || $is_adm)) {
        //Корзина доступна только владельцу или админу
        $trash = 0;
    }
    
}


$prjs = $projects->GetCurPrjs($uid, $closed, ($uid == get_uid()), hasPermissions('projects'), $kind, $trash, $page);


/*
 * @todo: Функционал не используетсяю. Пока скрываю.
$proj_groups = professions::GetAllGroupsLite();
$proj_groups_by_id = array();
foreach($proj_groups as $key => $wrk_prjgroup)
{
    $proj_groups_by_id[$wrk_prjgroup['id']] = $wrk_prjgroup['name'];
}
*/

$daysOfWeek = array(1=>'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота', 'Воскресенье');
$conted_prj=$projects->CountMyProjects($uid, ($uid == get_uid() || hasPermissions('projects')), false, $kind);
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
</style>
<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/projects.common.php");
$xajax->printJavascript('/xajax/'); 

//быстрое редактирование проекта
//только админу или владельцу
if ($is_owner || $is_adm) {
    $quickEditPoputType = 4; 
    include_once $_SERVER["DOCUMENT_ROOT"]."/projects/tpl.prj-quickedit.php";
}
 ?>
<div id="popup_budget" class="b-shadow b-shadow_inline-block b-shadow_width_335 b-shadow_center b-shadow_zindex_11 b-shadow_hide" style="width:345px">
                        <div class="b-shadow__right">
                            <div class="b-shadow__left">
                                <div class="b-shadow__top">
                                    <div class="b-shadow__bottom">
                                        <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_20" style="width:268px; padding-right:23px;">
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
                                                    <div class="b-combo__input b-combo__input_multi_dropdown drop_down_default_1 b-combo__input_init_projQuickEditCostby b-combo__input_width_60 b-combo__input_min-width_40 b-combo__input_arrow_yes">
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
                                                <a class="b-buttons__link b-buttons__link_dot_c10601 b-shadow__close" href="javascript:void(0)" onclick="popupHideChangeBudget(); return false;" >закрыть без изменений</a>
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
<?php //конец быстрое редактирование проекта?>
<? include('tpl.filter-prj.php')?>
<?

$i = 0;
if ($prjs) {
    setlocale(LC_ALL, 'ru_RU.CP1251');
    $dir = $user->login;
    ?>
    <?
    
    $pj = 0;
    $pn = sizeof($prjs);

    foreach ($prjs as $ikey=>$prj){
        if (!get_uid(false) && $prj['hide'] == 't') {
            $pn--;
            continue;
        }
        
        $prj['name'] = htmlspecialchars($prj['name'], ENT_QUOTES, 'CP1251', false);
        $prj['descr'] = htmlspecialchars($prj['descr'], ENT_QUOTES, 'CP1251', false);
                
        $project = new_projects::initData($prj);

        //@todo: Лишний запрос. Применение в код ненайдено. Пока скрываю.
        //$spec = projects::getPrimarySpec($prj['id']);
        //$prj['category'] = $spec['category_id'];
        
        if (is_new_prj($prj['post_date'])) {
            $blink = getFriendlyURL("blog", $prj['thread_id']);
        } else {
            $blink = getFriendlyURL("project", $prj);
        }
        $plink = "/users/".$prj['login']."/project/?prjid=".$prj['id'];
        
        $executorExists = false;
        if ($prj['exec_id']) { // если исполнитель выбран
            if ($prj['offer_id']) { // если исполнитель отвечал на проект
                $executorExists = true;
            } elseif ($prj['sbr_id'] && $prj['sbr_status'] >= sbr::STATUS_CHANGED) { // если не отвечал на проект, но выбран исполнителем сделки
                $executorExists = true;
            }
        }
        
        if ($prj['payed'] && $prj["kind"]!=2 && $prj["kind"] != 7) {
            ?>
            <div id="project-item<?=$prj['id']?>" class="b-layout b-layout_pad_10">
            <div class="fl2_date">
				<div class="fl2_date_day">
					<?=str_ago_pub($project->getCreateDateEx())?>
				</div>
				<div class="fl2_date_date">
					<?=strftime("%d ",
                            $project->getCreateDateEx()).
                            monthtostr(strftime("%m",$project->getCreateDateEx())) . ", " . 
                            $daysOfWeek[date("N",$project->getCreateDateEx())]?>
				</div>
				<div class="clear"></div>
			</div>
            <div class="fl2_offer bordered">
            	<div class="fl2_offer_logo">
                    <div>Платный проект</div>
                    <? if ($prj['cost']) { $priceby_str = getPricebyProject($prj['priceby']);?>
                    <div class="fl2_offer_budget">Бюджет:
                    <? if (hasPermissions('projects') && $user->login!=$_SESSION["login"]) {
                            ?><a id="prj_budget_lnk_<?=$prj['id']?>" class="b-post__link  b-post__link_dot_6db335" href="#" onClick="popupShowChangeBudget(<?=$prj['id']?>, '<?=$prj['cost']?>', '<?=$prj['currency']?>', '<?=$prj['priceby']?>', false, <?=$prj['id']?>, 1, 2); return false;"><?=CurToChar($prj['cost'], $prj['currency']) ?><?=$priceby_str?></a>
                     <?} else {
                             print CurToChar($prj['cost'], $prj['currency']).$priceby_str; 
                         }?> 
                    </div>
                    <? } else { 
                        if (hasPermissions('projects') && $user->login!=$_SESSION["login"]) {
                                ?><var class="bujet-dogovor"><a id="prj_budget_lnk_<?=$prj['id']?>" class="b-post__link b-post__link_dot_6db335" href="#" onClick="popupShowChangeBudget(<?=$prj['id']?>, '', 0, 1, true, <?=$prj['id']?>, 1, 2); return false;">По договоренности</a></var><?
                        } 
                        else {
                                ?><var class="bujet-dogovor">По договоренности</var> <?
                        } ?>
                    <? } ?>

                    <div class="b-layout__txt b-layout__txt_float_right b-layout__txt_padbot_5 b-layout_clear_both b-layout__txt_right">
                        <?php $count = (int)(is_new_prj($prj['post_date']) ? $prj["comm_count"] : $prj["offers_count"]); ?>
                        <a class="b-layout__link b-layout__link_color_000" href="<?=$blink?>"><?=$count?> <?=ending($count, 'предложение', 'предложения', 'предложений')?></a>
                        <?php if ($prj['new_messages_cnt'] && ($is_owner || $is_adm)): ?>
                            <br>(<a class="b-layout__link b-layout__link_color_6db335" href="<?=$blink?>"><?=$prj['new_messages_cnt']?> <?=ending($prj['new_messages_cnt'], 'новое сообщение', 'новых сообщения', 'новых сообщений')?></a>)
                        <?php endif; ?>
                    </div>
                    
                	<? if ($prj['logo_name']) {?>
                        <a href="http://<?= formatLink($prj['link'])?>" target="_blank" nofollow  ><img class="b-layout__pic b-layout__pic_float_right b-layout__pic_clear_right"  src="<?=WDCPREFIX.'/'.$prj['logo_path'].$prj['logo_name']?>" alt="" /></a>
                    <? } else {?>
                        <img  src="/images/public_your_logo.gif" alt="" class="b-layout__pic b-layout__pic_float_right b-layout__pic_clear_right"  />
                    <? }?> 
                </div>
                <div class="fl2_offer_header"> 
                    <? /* #0019741 if ($prj['prefer_sbr']=='t') {?><img src="/images/sbr_p.gif" class="sbr_p" title="Работодатель&nbsp;хочет&nbsp;работать&nbsp;через&nbsp;Cделку&nbsp;без&nbsp;риска" alt="Работодатель хочет работать через Cделку без риска"><? } */?>
                	<? if ($prj['sbr_id'] && (hasPermissions('projects') || $prj['sbr_emp_id']==$_SESSION['uid']||$prj['sbr_frl_id']==$_SESSION['uid'])) { ?>
                    	<a href="/<?= sbr::NEW_TEMPLATE_SBR; ?>/<?=($prj['sbr_emp_id']==$_SESSION['uid']||$prj['sbr_frl_id']==$_SESSION['uid']||hasPermissions('projects') ? "?id={$prj['sbr_id']}" : '').(hasPermissions('projects') ? "&access=A&E={$user->login}" : '')?>"><img src="/images/shield_sm.gif" alt="" /></a><? } ?>
                    <? if ($prj['ico_closed']=='t') {?><img src="/images/ico_closed.gif" alt="Проект закрыт" /><? }?>
                        <?php $sTitle = $prj['moderator_status'] === '0' && $prj['kind'] != 4 && $user->is_pro != 't' ? $stop_words->replace($prj['name']) : $prj['name']; ?>
                        <a id="prj_name_<?=$prj["id"] ?>" name="/proonly.php" href="<?=$blink?>" class="fl2_offer_header" title=""><?=reformat($sTitle, 100, 0, 1)?></a>
                 </div>
                 <?php $sDescr = $prj['moderator_status'] === '0' && $prj['kind'] != 4 && $user->is_pro != 't' ? $stop_words->replace($prj['descr']) : $prj['descr'];
                 if (is_new_prj($prj['post_date'])) {
                     $sDescr = reformatExtended($sDescr);
                 }
                 ?>
                 <div class="fl2_offer_content"><?=ereg_replace("\r","",ereg_replace("\n","",reformat($sDescr, 60)))?></div>
<?php 
            //@todo: Список приатаченный файлов для заказчика. Лишняя нагрузка на БД. Пока скрываю.
			//if(get_uid(false)) include dirname(__FILE__)."/attachlist.tpl.php";
?>
            <br />
            <div class="fl2_offer_meta">Прошло времени с момента публикации: 
				<?=ago_pub_x($project->getCreateDateEx())?><br />
                Автор: <a href="/users/<?=$user->login?>"><? print $user->uname." "; print $user->usurname; ?> [<?=$user->login?>]</a><br />
                Раздел: <?=projects::getSpecsStr($prj['id'],' / ', ', ');?><? /* $category=$proj_groups_by_id[$prj['category']]; print $category; */?>
				<? if ($prj['pro_only']=='t') {?>
            		<br /><span  class="fl2_offer_meta2" style="background-color:#fff7ee;">Отвечать на проект могут только пользователи с аккаунтом <a class="b-layout__link" href="/payed/"><span class="b-icon b-icon__pro b-icon__pro_f" alt="платным аккаунтом" title="платным аккаунтом"></span></a></span>
            	<? }?>
            </div>
            <div class="fl2_comments_link">
            	<div style="padding:12px 0px 0px 0px;"></div>
            </div>
            
            <?php if ($is_owner && !$prj['is_blocked']): ?>
            <div class="b-buttons">
                <?php if ($prj['closed']=='t'): ?>
                    <a class="b-buttons__link" 
                       href="javascript:void(0);" 
                       onclick="closeProject(<?=$prj["id"]?>, <?=$kind?>, 0);">Публиковать еще раз</a> &#160;
                <?php else: ?>
                    <a title="Получить больше предложений" 
                       data-scrollto="pay_services"
                       data-url="/public/?step=1&public=<?=$prj["id"]?>"
                       href="javascript:void(0);" 
                       class="b-button b-button_flat b-button_flat_green b-button_nowrap ">Получить больше предложений</a> &#160;
                    <a class="b-buttons__link" 
                          href="javascript:void(0);" 
                          onclick="closeProject(<?=$prj["id"]?>, <?=$kind?>, 1);">Снять с публикации</a> &#160;
                <?php endif; ?>
                <a class="b-buttons__link" 
                   href="/public/?step=1&public=<?=$prj["id"]?>">Редактировать</a> &#160;
                <a class="b-buttons__link" 
                   href="javascript:void(0);" 
                   onclick="moveTrashProject(<?=$prj["id"]?>, <?= $prj['trash']=='t' ? 0 : 1 ?>);">
                <?php if ($prj['trash']=='t'): ?>
                    Восстановить из корзины
                <?php else: ?>
                    Переместить в корзину
                <?php endif; ?>
                </a>
            </div>
            <?php endif; ?>
            
            <? if ($executorExists) { ?>
            <div class="b-fon b-fon_padbot_15">
            		<b class="b-fon__b1"></b>
            		<b class="b-fon__b2"></b>
            		<div class="b-fon__body b-fon__body_pad_10">
            			<span class="b-fon__txt b-fon__txt_float_right b-fon__txt_fontsize_11">Рейтинг: <?= round($prj['exec_rating'],1)?></span>
            			<span class="b-fon__txt b-fon__txt_bold b-fon__txt_fontsize_13">
                            <?php if($prj['kind']==2 || $prj['kind']==7) { ?>
                                Победитель определен:
                            <?php } else { ?>
                                Исполнитель определен:
                            <?php } ?>
                        </span>
            			<div class="b-username b-username_bold b-username_inline">
            			    <a class="b-username__link" href="/users/<?=$prj['exec_login']?>/"><?=($prj['exec_name']." ".$prj['exec_surname'])?></a> <span class="b-username__login b-username__login_color_fd6c30">[<a class="b-username__link" href="/users/<?=$prj['exec_login']?>"><?=$prj['exec_login']?></a>]</span> <?=view_mark_user($prj, "exec_"); ?>
            			</div>
            			<div class="i-opinion i-opinion_padtop_10">
            				<span class="b-opinion">
            					<span class="b-opinion__txt">
                                    <a class="b-opinion__link  b-opinion__link_color_4e" href="/users/<?=$prj['exec_login']?>/opinions/?author=0">Отзывы пользователей</a>
                                    &nbsp;<?= getOpinionLinks($prj['exec_login'], $prj); ?>
                                </span>
                            </span>
            			</div>
            		</div>
            		<b class="b-fon__b2"></b>
            		<b class="b-fon__b1"></b>
                </div>
            <? } ?>
            </div>

            </div> 
		
		<div id="project-reason-<?=$prj['id']?>" style="margin: 10px 30px 10px 30px;<?=($prj['is_blocked']? 'display: block': 'display: none')?>"><? 
		if ($prj['is_blocked']) {
			$moder_login = (hasPermissions('projects'))? $prj['admin_login']: '';
			print HTMLProjects::BlockedProject($prj['blocked_reason'], $prj['blocked_time'], $moder_login, "{$prj['admin_name']} {$prj['admin_uname']}");
		} else {
			print '&nbsp;';
		}
		?></div>

			
	    <?
		if ($pn > $pj+1)
		{

	    ?>


            <?
		}
        }
        else 
        {
            $is_personal = ($prj['kind'] == 9);
            
        ?>
        <table cellpadding="4" cellspacing="0" style="width:100%; border:0" id="project-item<?=$prj["id"] ?>"><tr style="vertical-align:top"><td style="padding-left: 10px; padding-right: 10px;">
        
        <div class="fl2_date">
			<div class="fl2_date_day">
			<?=str_ago_pub($project->getCreateDateEx())?>
			</div>
			<div class="fl2_date_date">
			<?=strftime("%d ", $project->getCreateDateEx()).
                    monthtostr(strftime("%m", $project->getCreateDateEx())). ", " . 
                    $daysOfWeek[date("N", $project->getCreateDateEx())]?>
			</div>
			<div class="clear"></div>
		</div>
            <div class="fl2_offer">
            <?if ($prj['cost']) {
                 $priceby_str = getPricebyProject($prj['priceby']);?>
                 <div class="fl2_offer_budget">Бюджет: <?php 
                     if (hasPermissions('projects') && $user->login!=$_SESSION["login"]) {
                            ?><a id="prj_budget_lnk_<?=$prj['id']?>" class="b-post__link  b-post__link_dot_6db335" href="#" onClick="popupShowChangeBudget(<?=$prj['id']?>, '<?=$prj['cost']?>', '<?=$prj['currency']?>', '<?=$prj['priceby']?>', false, <?=$prj['id']?>, 1, 2); return false;"><?=CurToChar($prj['cost'], $prj['currency']) ?><?=$priceby_str?></a>
                     <?} else {
                             print CurToChar($prj['cost'], $prj['currency']).$priceby_str; 
                         }?>
                 </div>
            <? } else { 
                        if (hasPermissions('projects') && $user->login!=$_SESSION["login"]) {
                                ?><var class="bujet-dogovor"><a id="prj_budget_lnk_<?=$prj['id']?>" class="b-post__link b-post__link_dot_6db335" href="#" onClick="popupShowChangeBudget(<?=$prj['id']?>, '', 0, 1, true, <?=$prj['id']?>, 1, 2); return false;">По договоренности</a></var><?
                        } 
                        else {
                                ?><var class="bujet-dogovor">По договоренности</var> <?
                        } ?>
            <? } ?>

            <?php if (!$is_personal): ?>
            <div class="b-layout__txt b-layout__txt_float_right b-layout__txt_padbot_5 b-layout_clear_both b-layout__txt_right">
                <?php $count = (int)(is_new_prj($prj['post_date']) ? $prj["comm_count"] : $prj["offers_count"]); ?>
                <a class="b-layout__link b-layout__link_color_000" href="<?=$blink?>"><?=$count?> <?=ending($count, 'предложение', 'предложения', 'предложений')?></a>
                <?php if ($prj['new_messages_cnt'] && ($is_owner || $is_adm)): ?>
                    <br>(<a class="b-layout__link b-layout__link_color_6db335" href="<?=$blink?>"><?=$prj['new_messages_cnt']?> <?=ending($prj['new_messages_cnt'], 'новое сообщение', 'новых сообщения', 'новых сообщений')?></a>)
                <?php endif; ?>
            </div>
            <?php endif; ?>
                                
            <? if ($prj['logo_name']) {?>
            <div class="fl2_offer_logo">
                <a href="http://<?= formatLink($prj['link'])?>" target="_blank" nofollow ><img class="b-layout__pic b-layout__pic_float_right b-layout__pic_clear_right"  src="<?=WDCPREFIX.'/'.$prj['logo_path'].$prj['logo_name']?>" alt="" /></a>
            </div>
            <? }?>
            <div class="fl2_offer_header"> 
                <? /* #0019741 if ($prj['prefer_sbr']=='t') {?><img src="/images/sbr_p.gif" class="sbr_p" title="Работодатель&nbsp;хочет&nbsp;работать&nbsp;через&nbsp;Cделку&nbsp;без&nbsp;риска" alt="Работодатель хочет работать через Cделку без риска"><? } */?>
                <? if ($prj['sbr_id'] && (hasPermissions('projects') || $prj['sbr_emp_id']==$_SESSION['uid']||$prj['sbr_frl_id']==$_SESSION['uid'])) { ?><a href="/<?= sbr::NEW_TEMPLATE_SBR; ?>/<?=($prj['sbr_emp_id']==$_SESSION['uid']||$prj['sbr_frl_id']==$_SESSION['uid']||hasPermissions('projects') ? "?id={$prj['sbr_id']}" : '').(hasPermissions('projects') ? "&access=A&E={$user->login}" : '')?>"><img src="/images/shield_sm.gif" alt="" /></a><? } ?>
                <?if ($prj['ico_closed']=='t') {?><img src="/images/ico_closed.gif" alt="Проект закрыт" /><?}?>
                <?php $sTitle = $prj['moderator_status'] === '0' && $prj['kind'] != 4 && $user->is_pro != 't' ? $stop_words->replace($prj['name']) : $prj['name']; ?>
                <a href="<?=$blink?>" id="prj_name_<?=$prj["id"] ?>"><?=reformat($sTitle, 20, 0, 1)?></a>
                <? /* if($prj['prefer_sbr']=='t') { ?><a class="b-txt__lnk b-txt__lnk_fs_11 b-txt__lnk_lh_1" href="/promo/bezopasnaya-sdelka/" title="Предпочитаю работать через Безопасную Сделку"><span class="b-icon b-icon_top_5 b-icon__shield"></span></a><? } */ ?>
                
                <?php if($is_personal): ?>
                <div class="b-layout__txt b-layout__txt_bold b-layout__txt_fontsize_11">
                    Персональный проект для <?=$prj['personal_fullname']?>
                </div>
                <?php endif; ?>
            </div>
                                
            <?php if ($is_owner && $project->isNotPayedVacancy() && !$project->isClosed()): ?>
                <div class="b-fon b-fon_padtop_10 b-fon_clear_both">
                    <div class="b-fon__body b-fon__body_padtop_10 b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffebbf">
                        <span class="b-icon b-icon_sbr_oattent b-icon_margleft_-25"></span>
                        Ваш проект перенесен в раздел Вакансии. Пожалуйста, оплатите его размещение, чтобы видеть отклики фрилансеров и иметь возможность выбрать Исполнителя.
                    </div>
                </div>
            <?php endif; ?>

            <?php $sDescr = $prj['moderator_status'] === '0' && $prj['kind'] != 4 && $user->is_pro != 't' ? $stop_words->replace($prj['descr']) : $prj['descr'];
            if (is_new_prj($prj['post_date'])) {
                $sDescr = reformatExtended($sDescr);
            }
            ?>
            <div class="fl2_offer_content"><?=ereg_replace("\r","",ereg_replace("\n","",reformat($sDescr, 65)))?></div>

<?php 
            //@todo: Список приатаченный файлов для заказчика. Лишняя нагрузка на БД. Пока скрываю.
            /*if(get_uid(false)) include dirname(__FILE__)."/attachlist.tpl.php";*/ 
?>
            <br />
            <div class="fl2_offer_meta">Прошло времени с момента публикации: 
		<?=ago_pub_x($project->getCreateDateEx())?>
                <br />
            	Автор: <a href="/users/<?=$user->login?>"><? print $user->uname." "; print $user->usurname; ?> [<?=$user->login?>]</a>
                <?php if(!$is_personal): ?>
                <br />
                Раздел: <?=projects::getSpecsStr($prj['id'],' / ', ', ');?>
                <?php endif; ?>
		<? /* $category=$proj_groups_by_id[$prj['category']]; print $category;*/ ?>
            </div>
            <? if ($prj['pro_only']=='t' || $prj['verify_only']=='t' || $prj['prefer_sbr']=='t') {?><br />
                <span  class="fl2_offer_meta2" style="background-color:#fff7ee;">
                <? if ($prj['pro_only']=='t' || $prj['verify_only']=='t') {?>Отвечать на проект могут только пользователи с аккаунтом <? if($prj['pro_only']=='t') {?><a class="b-layout__link" href="/payed/"><span class="b-icon b-icon__pro b-icon__pro_f" alt="платным аккаунтом" title="платным аккаунтом"></span></a> <?} if($prj['verify_only']=='t'){?><span class="b-icon b-icon__ver " alt="верифицированным аккаунтом" title="верифицированным аккаунтом"></span><?}?> <? } ?>
                
                </span>
                <? }?>
                <div class="fl2_comments_link">
                	<div style="padding:12px 0px 0px 0px;"></div>
                </div>

                <?php if ($is_owner): ?>
                <div class="b-buttons">
                    <?php if (!$prj['is_blocked'] && (($prj['kind'] != 7) || $prj['ico_closed'] == 'f')): ?>
                    
                        <?php if($project->isNotPayedVacancy() && !$project->isClosed()): ?>
                            <?php if($prj['trash'] != 't'):?>
                            <a title="Опубликовать вакансию за <?=$project->getProjectInOfficePrice($user->is_pro == 't')?> руб."
                                   href="/public/?step=1&public=<?=$prj["id"]?>&popup=1" 
                                   class="b-button b-button_flat b-button_flat_green b-button_nowrap">
                                        Оплатить размещение за <?=$project->getProjectInOfficePrice($user->is_pro == 't')?> руб.
                            </a> &#160; 
                            
                            <?php if ($prj['kind'] != 7): ?>
                            <a class="b-buttons__link" 
                               href="javascript:void(0);" 
                               onclick="closeProject(<?=$prj["id"]?>, <?=$kind?>, 1);">Снять с публикации</a> &#160;
                            <?php endif; ?> 
                            
                            <?php endif; ?>
                        <?php else: ?>
                            <?php if ($project->isClosed() && $prj['kind'] != 7): ?>
                                <a class="b-buttons__link" 
                                   href="javascript:void(0);" 
                                   onclick="closeProject(<?=$prj["id"]?>, <?=$kind?>, 0);">Публиковать еще раз</a> &#160;
                            <?php else: ?>
                                <a title="Получить больше предложений" 
                                   data-scrollto="pay_services"
                                   data-url="/public/?step=1&public=<?=$prj["id"]?>"
                                   href="javascript:void(0);" 
                                   class="b-button b-button_flat b-button_flat_green b-button_nowrap ">Получить больше предложений</a> &#160;
                                   
                                   <?php if ($prj['kind'] != 7): ?>
                                   <a class="b-buttons__link" 
                                      href="javascript:void(0);" 
                                      onclick="closeProject(<?=$prj["id"]?>, <?=$kind?>, 1);">Снять с публикации</a> &#160;
                                   <?php endif; ?> 
                                      
                                   <?php if($prj['trash'] != 't'):?>
                                    <a class="b-buttons__link" href="/public/?step=1&public=<?=$prj["id"]?>">Редактировать</a> &#160;
                                   <?php endif; ?>

                           <?php endif; ?>
                        <?php endif; ?>  
                                    
                    <?php endif; ?>
                    <a class="b-buttons__link" href="javascript:void(0);" 
                       onclick="moveTrashProject(<?=$prj["id"]?>, <?= $prj['trash']=='t' ? 0 : 1 ?>);">
                    <?php if ($prj['trash']=='t'): ?>Восстановить из корзины<?php else: ?>Переместить в корзину<?php endif; ?>
                    </a>
                </div>
                <?php endif; ?>
            
            </div>

            <? if ($executorExists) { ?>
            <div class="b-fon b-fon_padbot_15">
            		<b class="b-fon__b1"></b>
            		<b class="b-fon__b2"></b>
            		<div class="b-fon__body b-fon__body_pad_10">
            			<span class="b-fon__txt b-fon__txt_float_right b-fon__txt_fontsize_11">Рейтинг: <?= round($prj['exec_rating'],1)?></span>
            			<span class="b-fon__txt b-fon__txt_bold b-fon__txt_fontsize_13">
                            <?php if($prj['kind']==2 || $prj['kind']==7) { ?>
                                Победитель определен:
                            <?php } else { ?>
                                Исполнитель определен:
                            <?php } ?>
                        </span>
            			<div class="b-username b-username_bold b-username_inline">
            			    <a class="b-username__link" href="/users/<?=$prj['exec_login']?>/"><?=($prj['exec_name']." ".$prj['exec_surname'])?></a> <span class="b-username__login b-username__login_color_fd6c30">[<a class="b-username__link" href="/users/<?=$prj['exec_login']?>"><?=$prj['exec_login']?></a>]</span> <?=view_mark_user($prj, "exec_"); ?>
            			</div>
            			<div class="i-opinion i-opinion_padtop_10">
            				<span class="b-opinion">
            					<span class="b-opinion__txt">
                                    <a class="b-opinion__link  b-opinion__link_color_4e" href="/users/<?=$prj['exec_login']?>/opinions/">Отзывы пользователей</a>
                                    &nbsp;<?= getOpinionLinks($prj['exec_login'], $prj); ?>
                                </span>
                            </span>
            			</div>
            		</div>
            		<b class="b-fon__b2"></b>
            		<b class="b-fon__b1"></b>
                </div>
            
            <? } ?>
         </td> 
         </tr>  </table>
         <style type="text/css">
									.b-username .b-icon__ver{ position:relative; top:1px;}
									@media screen and (max-width: 700px){.b-username .b-icon__ver{ top:0px;}}
									.safari .b-username .b-icon__ver{ position:relative; top:2px;}
									</style>
		 
		<div id="project-reason-<?=$prj['id']?>" style="margin: 10px 30px 10px 30px;<?=($prj['is_blocked']? 'display: block': 'display: none')?>"><? 
		if ($prj['is_blocked']) {
			$moder_login = (hasPermissions('projects'))? $prj['admin_login']: '';
			
			print HTMLProjects::BlockedProject($prj['blocked_reason'], $prj['blocked_time'], $moder_login, "{$prj['admin_name']} {$prj['admin_uname']}");
		} else {
			print '&nbsp;';
		}
		?></div>


	    <?
		if ($pn > $pj+1)
		{

	    ?>

            <?
            	}
	    }

?>
<?php
                $sBox = '';
                if (hasPermissions('projects') && $user->login!=$_SESSION["login"]) {
                    
                    if($project->isNotPayedVacancy()):
                        $sBox .= '<b style="color:#ff0000; white-space:nowrap">Вакансия еще не оплачена</b> | ';
                    endif;

                	if ( $user->warn < 3 && !$user->is_banned && !$user->ban_where ) {
                		$sBox .= '<span class="warnlink-'.$user->uid.'"><a style="color:Red;" href="javascript: void(0);" onclick="banned.warnUser('.$user->uid.', 0, \'projects\', \'p'.$prj['id'].'\', 0); return false;">Сделать предупреждение (<span class="warncount-'.$user->uid.'">'.($user->warn ? $user->warn : 0).'</span>)</a></span> | ';
                	}
                	else {
                	    $sBanTitle = (!$user->is_banned && !$user->ban_where) ? 'Забанить!' : 'Разбанить';
                		$sBox .= '<span class="warnlink-'.$user->uid.'"><a style="color:Red;" href="javascript:void(0);" onclick="banned.userBan('.$user->uid.', \'p'.$prj['id'].'\', 0);">'.$sBanTitle.'</a></span> | ';
                	}
                }
                if (hasPermissions('projects') && $user->login!=$_SESSION["login"]) {
                	$sBox.="<a href=\"/public/?step=1&public=".$prj['id']."&red=".rawurlencode($_SERVER['REQUEST_URI'])."\" onclick=\"popupQEditPrjShow(".$prj['id'].", event); return false;\">Редактировать</a> ";
                	$sBox.=" | <span id='project-button-{$prj['id']}'><a style='color: red' href='.' onclick='banned.".($prj['is_blocked']? 'unblockedProject': 'blockedProject')."({$prj['id']}); return false;'>".($prj['is_blocked']? 'Разблокировать': 'Заблокировать')."</a></span>";
					$sBox.=" | <a id=\"prj_{$prj['id']}\" style=\"color:Red;\" href=\"?action=prj_delete&prjid=".$prj['id'].($_GET['closed']==1?'&closed=1':($_GET['open']==1?'&open=1':''))."\" onClick=\"return addTokenToLink('prj_{$prj['id']}', 'Вы уверены?')\">Удалить</a>";
                }
                if ($sBox != '') {
                        ?>
<?php if ( hasPermissions('projects') ) { ?>
<script type="text/javascript">
banned.addContext( 'p<?=$prj['id']?>', 3, '<?=$GLOBALS['host']?><?= getFriendlyURL("project", $prj) ?>', "<?=$prj['name']?>" );
</script>
<?php } ?>
                        
                            <div style="text-align:right;font-size:11px; margin:3px 10px 5px 0px;">
                            <?if(hasPermissions('projects') && $prj['payed']):?><b style="color:#ff0000; white-space:nowrap"><?php if ($prj['kind']!=2 && $prj['kind']!=7) {?>Внимание! Это платный проект!<?php } else {?>Конкурс<?php } ?></b> | <?endif;?>
                            <?=$sBox?></div>
                            
                            <div id="warnreason-p<?=$prj['id']?>" style="display:none; margin-bottom: 5px;">&nbsp;</div>
                        <? }
                ?>


<?

	$pj++;

    }
?>

<div class="b-layout b-layout_padtop_20">
<?php
    $cnt_idx = empty($closed) && $trash?'trash':$closed;
    $cnt_keys = array("true" => 'closed', "false" => 'open', "" => 'all', "trash" => 'trash');
    if (isset($cnt_keys[$cnt_idx])) {
        $pages = ceil($conted_prj[$cnt_keys[$cnt_idx]] / projects::PAGE_SIZE);
        echo new_paginator2($page, $pages, 4, "%s?".(($trash>0)?"trash=1&":((($kind>0)?"kind={$kind}&":"").((empty($closed)?'all=1&':(($closed == 'true')?'closed=1&':'')))))."page=%d%s");
    }
?>
</div>
                            
<?php                            
}else {	
    $entity = "пользователя";
    $style = "padding-left:50px; padding-top:5px; padding-bottom:20px;";
    $viewProfileLogin = '';
    $urlData = explode("/", $_SERVER["REQUEST_URI"]);
    $f = 0;
    foreach ($urlData as $item) {
        if ($item == "users"){
            $f = 1; 
    	    continue;
    	}
        if ($f) {
            $viewProfileLogin = $item;
            break;
        }
    }
    if ($_SESSION["uid"]&&($_SESSION["login"] == $viewProfileLogin)) {
        $entity = "вас";
        $style = "padding-left:50px; padding-top:5px;padding-bottom:15px;";
    }
    require_once dirname(__FILE__)."/tpl.noprojects.php";
}
    ?>
<?php
if ( hasPermissions('projects') ) {
    include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/ban_overlay.php' );
    include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/warn_overlay.php' );
}
?>
