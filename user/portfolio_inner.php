<?
if(!defined('IN_STDF')) { 
    header("HTTP/1.0 404 Not Found");
    exit();
}
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/portfolio.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/kwords.php");
setlocale(LC_ALL, 'ru_RU.CP1251');
$portf = new portfolio();
$prjs = $portf->GetPortf($user->uid, 'NULL', true);
$prfs = new professions();
$profs = $prfs->GetAllProfessionsPortfWithoutMirrored($user->uid, "AND t.user_id IS NOT NULL");
$is_not_spec = (sizeof($profs)<=0);
$first_profs = current($profs);
$html_keyword_js = '<a href="/freelancers/?word=$1" class="inherit">$2</a>';
$html_keyword = preg_replace('/\$\d/', '%s', $html_keyword_js);

if($user->uid == $_SESSION['uid']) {
    $spec_modified = professions::getLastModifiedSpec($user->uid);
}
?>

<? if((int) $user->spec == 0 && $user->uid == $_SESSION['uid']) {?>
<div class="b-fon b-fon_pad_20">
    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffeeeb">
        <span class="b-icon b-icon_sbr_rattent b-icon_margleft_-25"></span><a class="b-layout__link" href="/users/<?= $user->login; ?>/setup/specsetup/">Выберите специализацию</a>. Это небходимо, чтобы попасть в каталог фрилансеров, в котором вас найдут заказчики
    </div>
</div>
<? }//if?>
<br />
<script type="text/javascript">var HTML_KWORDTMPL='<?=$html_keyword_js?>'</script>
<table  cellspacing="0" cellpadding="0" style="width:100%;" class="cpt-info">
<tr>
	<td style="width:45%;vertical-align:top;padding:16px 16px 32px 19px;">
	<div style="padding-bottom:15px;vertical-align:top;white-space:nowrap;">Специализация:&nbsp;&nbsp;<?=professions::GetProfNameWP($user->spec, ' / ', 'Нет специализации')?></div>

<?
	$specs_add = professions::GetProfsAddSpec($user->uid);
 $specs_add_string = null;
	
 if ($specs_add) {
     $specs_add_array = array();

     for ($si = 0; $si < sizeof($specs_add); $si++) {
         $specs_add_array[$si] = professions::GetProfNameWP($specs_add[$si], ' / ');
     }

     $specs_add_string = join(", ", $specs_add_array);
 } else {
     $specs_add_string = "Нет";
 }
 
 ?>

 <? if($specs_add_string) { ?>
	<div style="padding-bottom:15px;vertical-align:top;width:280px;">Дополнительные специализации:&nbsp;&nbsp;<?=$specs_add_string?></div>
 <? } ?>
<?php if($spec_modified && !is_pro()) { ?>
    <p style="padding-bottom:15px;">Вы можете сменить выбранные специализации через <?= $spec_modified['days']. ' '.ending($spec_modified['days'], 'день', 'дня', 'дней'); ?></p>
<?php }//if?>
    
<? if ($user->exp > 0) { ?>
	<div style="padding-bottom:15px;vertical-align:top;white-space:nowrap;">Опыт работы:&nbsp;&nbsp;<?=view_exp($user->exp)?></div>
<? } ?>
<? if($user->in_office == 't'):?>
    <div style="padding-bottom:15px;vertical-align:top;white-space:nowrap;"><strong>Ищу долгосрочную работу <span style="display:inline-block; padding: 0 0 0 15px; background: url(/images/icons-sprite.png) no-repeat -100px -335px;">в офисе</span></strong></div>
<?endif; ?>
<? /* #0019741 if($user->prefer_sbr == 't'):?>
    <div style="padding-bottom:15px;vertical-align:top;white-space:nowrap;"><strong>Предпочитаю работать через сервис <span class="sbr-ic"><a href="/promo/sbr/" class="inherit_underline" style="color:#666666;">Сделка без риска</a></span></strong></div>
<?endif;*/ ?>
<? if ($user->cost_hour > 0) { ?>
	<div style="padding-bottom:15px;vertical-align:top;white-space:nowrap;"><strong>Стоимость часа работы</strong> &mdash; <span class="money"><?=view_cost2($user->cost_hour, '', '', false, $user->cost_type_hour)?></span></div>
<? } ?>
<? if ($user->cost_month > 0) { ?>
	<div style="padding-bottom:15px;vertical-align:top;white-space:nowrap;"><strong>Стоимость месяца работы</strong> &mdash; <span class="money"><?=view_cost2($user->cost_month, '', '', false, $user->cost_type_month)?></span></div>
<? } ?>
	</td>
    <?php $sSpecText = $user->isChangeOnModeration( $user->uid, 'spec_text' ) && $user->is_pro != 't' ? $stop_words->replace($user->spec_text) : $user->spec_text; ?>
	<td style="width:55%;vertical-align:top;padding:16px 19px 32px 16px;"><a name="spec_text"></a>
        <?=reformat2( $sSpecText, 30, 0, 1 )?>
        <?php if ( hasPermissions('users') ) { ?>
        <br/>
        <br/>
        <a class="admn" href="javascript:void(0);" onclick="adm_edit_content.editContent('admEditProfile', '<?=$user->uid?>_0', 0, '', {'change_id': 0, 'ucolumn': 'spec_text', 'utable': 'freelancer'})">Редактировать</a>
        <?php } ?>
    </td>
</tr>
</table>
<? if ($_SESSION['login'] == $user->login) { ?>
<div class="change"><div style="padding-right:19px;"><a href="/users/<?=$_SESSION['login']?>/setup/portfolio/"><img src="/images/ico_setup.gif" alt="" width="6" height="9" /></a>&nbsp;<a href="/users/<?=$_SESSION['login']?>/setup/portfolio/">Изменить</a></div></div>
<br />
<? } if ($prjs){ ?>
<?
			$lastprof = -1;
			$j = 0;
			$k = -1;
			if ($prjs) foreach($prjs as $ikey=>$prj){
			if (!$prj['id']) continue;
            $prof_id = $prj['prof_id'];
			if ($prj['is_blocked'] == 't' && $user->uid != get_uid(false) && !hasPermissions('users')) continue;
      if($prj['prof_id']==professions::BEST_PROF_ID || $prj['prof_id']==professions::CLIENTS_PROF_ID) continue;

            $links_keyword = array();
            $links_keyword_hide = array();
            $is_count_project = true;
            $user_keys = kwords::getUserKeys($user->uid, $prof_id);
            $bIsModer  = kwords::isModerUserKeys( $user->uid, $prof_id );
            $c = $kword_count = 0;
            if($user_keys) { 
                $kword_count = count($user_keys);
                foreach($user_keys as $key) { 
                    $sKey = stripslashes($bIsModer ? $stop_words->replace($key, 'plain') : $key);
                    
                    if(++$c > kwords::MAX_KWORDS_PORTFOLIO) {
                        $links_keyword_hide[] = urlencode($sKey).',,'.change_q_x($sKey, true, false);
                    } else {
                        $links_keyword[] = sprintf($html_keyword, urlencode($sKey), change_q_x($sKey, true, false));
                    }
                }
            }                	       
			$curprof = $prj['prof_id'];
			if ($lastprof != $curprof) {
				$i = 1;
				$k++;
				if ($lastprof != -1) {
				?>
		</table>
	</td>
	<td  style="width:14px">&nbsp;</td>
</tr>
</table><br />
				<? } ?>
<table width="100%"  cellspacing="0" cellpadding="0">
<tr>
	<td style="width:19px; height:20px" class="brdtop">&nbsp;</td>
	<td class="brdtop">
     <?
     $rowsp = intval((float)$prj['cost_to'] || (float)$prj['cost_from'])
            + intval($prj['time_from'] || $prj['time_to'])
            + intval((float)$prj['cost_hour'] || false)
            + intval((float)$prj['cost_1000'] || false)
            + 1;
     ?>
    <table width="100%"  cellspacing="0" cellpadding="0" <?=$rowsp > 1 ? 'style="margin-bottom: 10px;"' : ''?> >
    <tr>
    	<td style="width:45%;padding:8px 16px 8px 0px;vertical-align:top;">
            <a name="<?=$prj['prof_id']?>"></a><a href="/users/<?=$user->login?>/#<?=$prj['prof_id']?>">#</a>&nbsp;<a href="/freelancers/<?=$prj['proflink']?>/"><strong><?=$prj['mainprofname']." / ".$prj['profname']?></strong></a>
            <?php if($user_keys) {?>
            <p><?
                 echo implode(", ", $links_keyword);
                 if($kword_count > kwords::MAX_KWORDS_PORTFOLIO ) { 
              ?><span class="prtfl-hellip">&hellip;</span
                ><span class="prfl-tags"><a href="javascript:void(0)">Все <?=$kword_count?> <?=ending($kword_count, 'тег', 'тега', 'тегов')?></a></span
                ><span class="prfl-tags-more" style="display:none"><?=implode(',', $links_keyword_hide)?></span>
              <? } ?>
            </p>
            <?php } //if?>
        </td>
        <?php $sPortfText = $prj['on_moder'] && $user->is_pro != 't' ? $stop_words->replace($prj['portf_text']) : $prj['portf_text']; ?>
    	<td rowspan="<?=$rowsp?>" style="width:55%;padding:8px 16px 8px 16px;vertical-align:top;"><?=nl2br($sPortfText)?></td>
    </tr>

    
    <? if ($prj['proftext'] == 't') { ?>
    <?
    $cost_text = view_cost2($prj['cost_1000'], '', '', false, $prj['cost_type']);
    $cost_hour_text = view_cost2($prj['cost_hour'], '', '', false, $prj['cost_type_hour']);
    if ($cost_text != '')
    {
    ?>
    <tr>
    	<td style="padding:8px 16px 2px 0px; vertical-align: top;">Стоимость тысячи знаков: <span class="money"><?=$cost_text?></span></td>
    </tr>
    <? } ?>
    <? if($cost_hour_text != ''): ?>
    <tr>
    	<td style="padding:8px 16px 2px 0px; vertical-align: top;">Оценка часа работы: <span class="money"><?=$cost_hour_text?></span></td>
    </tr>
    <? endif; ?> 
    <? } else {
    $cost_from_text = view_cost2($prj['cost_from'], '', '', false, $prj['cost_type']);
    $cost_to_text = view_cost2($prj['cost_to'], '', '', false, $prj['cost_type']);
    $cost_hour_text = view_cost2($prj['cost_hour'], '', '', false, $prj['cost_type_hour']);
    if (($cost_to_text != '') || ($cost_from_text != ''))
    {
    ?>
    <tr>
    	<td style="padding:8px 16px 2px 0px; vertical-align: top;">Стоимость работ: <span class="money"><? if ($cost_from_text != '') { ?>от <?=$cost_from_text?> <? } ?><? if ($cost_to_text != '') { ?>до <?=$cost_to_text?><? } ?></span></td>
    </tr>
    <? } ?>
    
    <? if($cost_hour_text != ''): ?>
    <tr>
    	<td style="padding:8px 16px 2px 0px; vertical-align: top;">Оценка часа работы: <span class="money"><?=$cost_hour_text?></span></td>
    </tr>
    <? endif; ?> 
    
    <?
    $time_text = view_range_time($prj['time_from'], $prj['time_to'], $prj['time_type']);
    if ($time_text != '')
    {
    ?>
    <tr>
    	<td style="padding:8px 16px 2px 0px; vertical-align: top;">Сроки: <?=$time_text?>.</td>
    </tr>
    <? } } ?>
    
    <?php if ( hasPermissions('users') ) { ?>
    <tr>
        <td style="padding:8px 16px 2px 0px; vertical-align: top;">
            <a class="admn" href="javascript:void(0);" onclick="adm_edit_content.editContent('admEditPortfChoice', '<?=$user->uid?>_0', 0, '', {'sProfId': <?=$prof_id?>})">Редактировать</a>
        </td>
    </tr>
    <?php } ?>
    </table>
	</td>
	<td style="width:19px; height:20px" class="brdtop">&nbsp;</td>
</tr>

</table>
<table width="100%" cellspacing="0" cellpadding="0">
<tr><td  style="height:8px" colspan="3">&nbsp;</td></tr>
<tr>
	<td width="14">&nbsp;</td>
	<td>
		<table class="portfolio-list" width="100%" border="0" cellspacing="0" cellpadding="3">
		
<?		$lastprof = $curprof;
			}
			if ($prj['id']) {
                $sName = /*$prj['moderator_status'] === '0' ? $stop_words->replace($prj['name'], 'plain') :*/ $prj['name'];
		?>
		<tr>
			<td class="odd"><?=$i?>.</td>
			<td class="even">
                <a href="/users/<?=$user->login?>/viewproj.php?prjid=<?=$prj['id']?>" target="_blank" class="blue" title="<?=htmlspecialchars($sName)?>"><?= reformat($sName, 30)?></a><? $txt_cost = view_cost2($prj['prj_cost'], '', '', false, $prj['prj_cost_type']); $txt_time = view_time($prj['prj_time_value'], $prj['prj_time_type']);?> <span class="money" style="padding-left:8px;"><?=$txt_cost?></span><? if ($txt_cost != '' && $txt_time != '') { ?>, <? } ?><?=$txt_time?>
			<? /* Убраны комментарии к работам if ($prj['show_comms'] == 't') {?> | <a href="/users/<?=$user->login?>/comments/?tr=<?=$prj['id']?>" style="color: #666666;">Комментарии (<?=zin($prj['comms'])?>)</a><? } */ ?>
                <div id="portfolio-block-<?= $prj['id'] ?>" style="display: <?= ($prj['is_blocked'] == 't' ? 'block': 'none') ?>">
                    <? if ($prj['is_blocked'] == 't') { ?>
                    <div class='b-fon b-fon_clear_both b-fon_bg_ff6d2d b-fon_padtop_10 b-fon_padbot_10'>
                        <b class="b-fon__b1"></b>
                        <b class="b-fon__b2"></b>
                        <div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_fontsize_13">
                            <span class="b-fon__attent"></span>
                            <div class="b-fon__txt b-fon__txt_margleft_20">
                                    <span class="b-fon__txt_bold">Работа заблокирована</span>. <?= reformat($prj['blocked_reason'], 24, 0, 0, 1, 24) ?> <a class='b-fon__link' href='https://feedback.fl.ru/'>Служба поддержки</a>
                                    <div class='b-fon__txt'><?php if ( hasPermissions('users') ) { ?><?= ($prj['admin_login'] ? "Заблокировал: <a class='b-fon__link' href='/users/{$prj['admin_login']}'>{$prj['admin_uname']} {$prj['admin_usurname']} [{$prj['admin_login']}]</a><br />": '') ?><?php } ?>
                                    Дата блокировки: <?= dateFormat('d.m.Y H:i', $prj['blocked_time']) ?></div>
                            </div>
                        </div>
                        <b class="b-fon__b2"></b>
                        <b class="b-fon__b1"></b>
                    </div>
                    <? } ?>
                </div>
            </td>
            <td class="odd">
                <?php if ( hasPermissions('users') ) { ?>
                <div id="portfolio-button-<?= $prj['id'] ?>">
                    <a class="admn" href="javascript:void(0);" onclick="banned.<?=($prj['is_blocked']=='t'? 'unblockedPortfolio': 'blockedPortfolio')?>(<?=$prj['id']?>)"><?= $prj['is_blocked']=='f' ? "Заблокировать" : "Разблокировать"; ?></a><br/>
                    <a class="admn" href="javascript:void(0);" onclick="adm_edit_content.editContent('admEditPortfolio', '<?=$prj['id']?>_0', 0, '')">Редактировать</a>
                </div>
                <?php 
                }
                else { ?>&nbsp;<?php }
                ?>
            </td>
		</tr>
		<? $i++; $j++;}
		 else { ?>
		<tr>
			<td style="text-align:center;">В этом разделе нет работ</td>
		</tr>
		<?
		}
		} if ($k > -1) {?>
		</table>
	</td>
	<td style="width:14px">&nbsp;</td>
</tr>
</table><br><? } ?>
<? } if ($k == -1 || !$prjs) {
    if($_SESSION['uid'] == $user->uid) {
        if($is_not_spec) {
            $_SESSION['text_spec'] = true;
            $aHref = "/users/{$_SESSION['login']}/setup/portfsetup/";
        } else {
            $aHref = "/users/{$_SESSION['login']}/setup/portfolio/#prof{$first_profs['id']}";
            $_SESSION['text_spec'] = false;
        }
        ?>
    
        <div class="add-work-b">
        	<p>В вашем портфолио сейчас нет ни одной работы</p><br />
            <a class="b-button b-button_flat b-button_flat_green" href="<?= $aHref?>">Добавить работу</a>
        </div>
    <?php } else {//if?>
        <h2 style="text-align: center;"><?= ($user->tab_name_id == "1"?"Нет услуг":"Нет работ")?></h2>
    <?php } //else?>
<? } ?>