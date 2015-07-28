<?php
if(!defined('IN_STDF')) { 
    header("HTTP/1.0 404 Not Found");
    exit();
}
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/portfolio.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/kwords.php");
$stop_words = new stop_words( hasPermissions('users') );
setlocale(LC_ALL, 'ru_RU.CP1251');
$portf = new portfolio();
$prjs = $portf->GetPortf($user->uid, 'NULL', true);
$prfs = new professions();
$profs = $prfs->GetAllProfessionsPortfWithoutMirrored($user->uid, "AND t.user_id IS NOT NULL");
$is_not_spec = (sizeof($profs)<=0);
$first_profs = current($profs);
$specs_add = professions::GetProfsAddSpec($user->uid);
if ($specs_add) {
    $specs_add_array = array();
    for ($si = 0; $si<sizeof($specs_add); $si++) {
        $specs_add_array[$si] = professions::GetProfNameWP($specs_add[$si], ' / ');
	}
	$specs_add_string = join(", ", $specs_add_array);
} else {
    $specs_add_string = "Нет";
}

$html_keyword_js = '<a href="/freelancers/?word=$1" class="inherit">$2</a>';
$html_keyword = preg_replace('/\$\d/', '%s', $html_keyword_js);

if($prjs) {
    $i = $block = 0;
    $size_block = 3;
    // Рассортировываем портфолио
    foreach($prjs as $prj) {
        if ($prj['is_blocked'] == 't' && $user->uid != get_uid(false) && !hasPermissions('users')) continue;
        if($i >= $size_block || $prj['prof_id'] != $old_prof) {
            $block++;
            $i = 0; 
        }
        $pp[$prj['prof_id']][$block][]  = $prj;
        $pp_noblocks[$prj['prof_id']][] = $prj;
        
        $sName = /*$prj['moderator_status'] === '0' ? $stop_words->replace($prj['name'], 'plain') :*/ $prj['name'];
        $pt[$prj['prof_id']][$block][$prj['id']] = $sName;
        if(!isset($pname[$prj['prof_id']])) {
            $pname[$prj['prof_id']] = $prj;
        }
        
        $i++;
        $old_prof = $prj['prof_id'];
    }
}
?>
<style type="text/css">
.b-page a.b-layout__link:link {color:#0F71C8 !important}
.b-page a.b-layout__link:hover {color:#8c0400 !important}
</style>
<br />
<?php if($_SESSION['login'] == $user->login) { ?>
    <?php if($user->is_pro!='t' && (int) $user->spec == 0) { ?>
        <div class="b-fon b-fon_pad_20">
            <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffeeeb">
                <span class="b-icon b-icon_sbr_rattent b-icon_margleft_-25"></span><a class="b-layout__link" href="/users/<?= $user->login; ?>/setup/specsetup/">Выберите специализацию</a>. Это небходимо, чтобы попасть в каталог фрилансеров, в котором вас найдут заказчики
            </div>
        </div>
    <?php } elseif($user->is_pro!='t' && (int) $user->spec != 0) { ?>
        <?php print view_error4('Внимание! Вы отображаетесь в каталоге только по своей специализации. Чтобы увеличить количество специализаций, необходимо перейти на аккаунт ' . view_pro()); ?>
    <?php } ?>
<? } ?>

<script type="text/javascript">var HTML_KWORDTMPL='<?=$html_keyword_js?>'</script>
<div class="prtfl c">
    <div class="prtfl-r"><a name="spec_text"></a>
        <?php $sSpecText = $user->isChangeOnModeration( $user->uid, 'spec_text' ) && $user->is_pro != 't' ? $stop_words->replace($user->spec_text) : $user->spec_text; ?>
        <p><?=reformat2( $sSpecText, 50, 0,  0 )?></p>
        
        <?php if ( hasPermissions('users') ) { ?>
        <a class="admn" href="javascript:void(0);" onclick="adm_edit_content.editContent('admEditProfile', '<?=$user->uid?>_0', 0, '', {'change_id': 0, 'ucolumn': 'spec_text', 'utable': 'freelancer'})">Редактировать</a>
        <?php } ?>
    </div>
    <div class="prtfl-l">
        <p>Специализация:&nbsp;&nbsp;<?=professions::GetProfNameWP($user->spec,' / ', "Нет специализации")?></p>
        <p>Дополнительные специализации:&nbsp;&nbsp;<?=$specs_add_string?></p>
        <?php if($user->exp > 0) {?>
        <p style="padding-top:10px; border-top:1px solid #d7dadc">Опыт работы:&nbsp;&nbsp;<?=view_exp($user->exp)?></p>
        <?php } //if?>
        <?php if($user->in_office == 't') { ?>
        <p><strong>Ищу долгосрочную работу <span class="run-men" >в офисе</span></strong></p>
        <?php } //if?>
        <?php /* #0019741 if($user->prefer_sbr == 't') { ?>
        <p><strong>Предпочитаю работать через сервис <span class="sbr-ic"><a href="/promo/sbr/" class="inherit_underline" style="color: #666666;">Сделка без риска</a></span></strong></p>
        <?php } *///if?>
        <?php if ($user->cost_hour > 0) { ?>
        <p><strong>Стоимость часа работы</strong> &mdash; <span class="money"><?=view_cost2($user->cost_hour, '', '', false, $user->cost_type_hour)?></span></p>
        <?php } //if?>
        <?php if ($user->cost_month > 0) { ?>
        <p><strong>Стоимость месяца работы</strong> &mdash; <span class="money"><?=view_cost2($user->cost_month, '', '', false, $user->cost_type_month)?></span></p>
        <?php } //if?>
    </div>         
    <?php if ($_SESSION['login'] == $user->login) { ?>
    <div class="change"><div style="padding-right:19px;"><a href="/users/<?=$_SESSION['login']?>/setup/portfolio/"><img src="/images/ico_setup.gif" alt="" width="6" height="9" /></a>&nbsp;<a href="/users/<?=$_SESSION['login']?>/setup/portfolio/">Изменить</a></div></div>
    <?php } //if ?>
</div>
<?php
foreach($pp as $prof_id=>$prjs) { 
    $pinfo = $pname[$prof_id]; 
    if(!$pinfo['id']) continue;
    if(!$is_pro && ($prof_id == professions::BEST_PROF_ID || $prof_id == professions::CLIENTS_PROF_ID)) continue;
    
    $is_link       = ($prof_id != professions::BEST_PROF_ID && $prof_id != professions::CLIENTS_PROF_ID);
    $is_background = ($prof_id == professions::BEST_PROF_ID || $prof_id == professions::CLIENTS_PROF_ID);
    
    $rowsp = intval((float)$pinfo['cost_to'] || (float)$pinfo['cost_from'])
            + intval($pinfo['time_from'] || $pinfo['time_to'])
            + intval((float)$pinfo['cost_hour'] || false)
            + intval((float)$info['cost_1000'] || false)
            + 1;
    if ($pinfo['proftext'] == 't') { 
        $cost_text      = view_cost2($pinfo['cost_1000'], '', '', false, $pinfo['cost_type']);
        $cost_hour_text = view_cost2($pinfo['cost_hour'], '', '', false, $pinfo['cost_type_hour']); 
    } else {
        $time_text      = view_range_time($pinfo['time_from'], $pinfo['time_to'], $pinfo['time_type']);
        $cost_from_text = view_cost2($pinfo['cost_from'], '', '', false, $pinfo['cost_type']);
        $cost_to_text   = view_cost2($pinfo['cost_to'], '', '', false, $pinfo['cost_type']);
        $cost_hour_text = view_cost2($pinfo['cost_hour'], '', '', false, $pinfo['cost_type_hour']);
    }
    
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
?>
    <div class="stripe" <?= ($is_background?' style="background:#ffeda9"':' style="background:#E5EAF5"')?>>
        <?php $sPortfText = $pinfo['on_moder'] && $user->is_pro != 't' ? $stop_words->replace($pinfo['portf_text']) : $pinfo['portf_text']; ?>
        <div class="stripe-r"><p><?=nl2br(trim(reformat($sPortfText, 54, 0, 1)))?></p></div>    	   	
        <div class="stripe-l">
            <h4>
                <a name="<?= $prof_id?>"></a><a href="/users/<?= $user->login?>/#<?= $prof_id?>" class="inherit">#</a>&nbsp;
                <?= ($is_link?'<a href="/freelancers/'.$pinfo['proflink'].'/" class="inherit">':"")?><strong style="color:#000000"><?=($prof_id>=0 ? $pinfo['mainprofname'].' / ' : '').$pinfo['profname']?></strong><?= ($is_link?'</a>':"")?> 
            </h4>
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
            <?php if ($pinfo['proftext'] == 't') { ?>
                <?php if($cost_text != '') {?>
                <p>Стоимость тысячи знаков: <span class="money"><?= $cost_text?></span></p>
                <?php } //if?>
                <?php if($cost_hour_text != '') {?>
                <p>Оценка часа работы: <span class="money"><?= $cost_hour_text?></span></p>
                <?php } //if?>
            <?php } else { //if?>
                <?php if(($cost_to_text != '') || ($cost_from_text != '')) { 
                    $from = $cost_from_text != '' ? "от ".$cost_from_text : "";
                    $to   = $cost_to_text   != '' ? "до ".$cost_to_text   : "";?>
                <p>Стоимость работ: <span class="money"><?= $from." ".$to?></span></p>
                <?php } //if?>
                <?php if($cost_hour_text != '') {?>
                <p>Оценка часа работы: <span class="money"><?= $cost_hour_text?></span></p>
                <?php } //if?>
                <?php if($time_text != '') {?>
                <p>Сроки: <?= $time_text?></p>
                <?php } //if?>
            <?php } // else?>
                
            <?php if ( hasPermissions('users') ) { ?>
            <br/>
            <a class="admn" href="javascript:void(0);" onclick="adm_edit_content.editContent('admEditPortfChoice', '<?=$user->uid?>_0', 0, '', {'sProfId': <?=$prof_id?>})">Редактировать</a>
            <?php } ?>
        </div>
    </div>

    <table width="100%" cellspacing="0" cellpadding="0"  >
    <tr>
    	<td style="width:14px" >&nbsp;</td>
    	<? // если $iWantPro == true значит находимся в режиме показа ПРО для НЕПРО
        if($pinfo['gr_prevs'] == 't' || $iWantPro) {?>
    	<td>
        	<?php foreach($prjs as $in=>$blocks) { ?>
        	<table width="100%" cellspacing="0" cellpadding="0" border="0" class="n_qpr preview-work">
                    <tr class="qpr">
                    <?php $k=0; foreach($pt[$prof_id][$in] as $id=>$prj_name) { $k++; ?>
                        <td>
                            <div class="h-work">
                                <strong><a class="blue" target="_blank" href="/users/<?= $user->login?>/viewproj.php?prjid=<?= $id?>"><?= (reformat($prj_name, 25, 0, 1))?></a></strong>
                      	    </div>
                  	    </td>
                    <?php } //foreach?>
                    <?php for($ii=$k;$ii<3;$ii++) { ?>
                        <td>&nbsp;</td>
                    <?php }?>
                    </tr>
                    <tr class="qpr">
                    <?php $k = 0; foreach($blocks as $prj) { 
                        $k++;
                        $txt_cost = view_cost2($prj['prj_cost'], '', '', false, $prj['prj_cost_type']); 
                        $txt_time = view_time($prj['prj_time_value'], $prj['prj_time_type']);
                        $is_txt_time = ($txt_cost != '' && $txt_time != '');
                    ?>
                        <td>
                            <div class="b-work b-work_bg_ff">
                                <?php 
                                if($prj['prj_prev_type']) {
                                    $sDescr = /*$prj['moderator_status'] === '0' ? $stop_words->replace($prj['descr']) :*/ $prj['descr'];
                                    print("<p style='padding-bottom:7px'>".reformat2($sDescr,25,0,1)."</p>"); // Для текста нужен свой блок <p> с отступом вконце @todo
                                } else { //if 
                                    $sName = /*$prj['moderator_status'] === '0' ? $stop_words->replace($prj['name'], 'plain', false) :*/ $prj['name'];
                                    ?>
              	                <a title="<?=htmlspecialchars($sName)?>" class="blue" target="_blank" href="/users/<?=$user->login?>/viewproj.php?prjid=<?=$prj['id']?>" style="text-decoration:none">
                        			<?=view_preview($user->login, $prj['prev_pict'], "upload", 'center', false, false, htmlspecialchars($sName), 200)?>
                                </a>
                                <?php } //else ?>
                                <p><span class="money"><?= $txt_cost?></span><?= ($is_txt_time ? ", ":"") . ($txt_time != ''?$txt_time:"")?></p>

                                
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
                                
                                <?php if ( hasPermissions('users') ) { ?>
                                <div id="portfolio-button-<?= $prj['id'] ?>">
                                    <a class="admn" href="javascript:void(0);" onclick="banned.<?=($prj['is_blocked']=='t'? 'unblockedPortfolio': 'blockedPortfolio')?>(<?=$prj['id']?>)"><?= $prj['is_blocked']=='f' ? "Заблокировать" : "Разблокировать"; ?></a><br/>
                                    <a class="admn" href="javascript:void(0);" onclick="adm_edit_content.editContent('admEditPortfolio', '<?=$prj['id']?>_0', 0, '')">Редактировать</a>
                                </div>
                                <?php 
                                }
                                else { ?>&nbsp;<?php }
                                ?>
                                
                                
                             </div>
                  	    </td>
                    <?php } //foreach ?>
                    <?php for($ii=$k;$ii<3;$ii++) { ?>
                        <td>&nbsp;</td>
                    <?php }?>
                    </tr>
            </table>    
            <?php } //foreach ?>
        </td>
        <?php } else { // if?>
        <td style="vertical-align:top;padding:6px 0px 6px 0px;">    
            <table class="portfolio-list" width="100%" cellspacing="0" cellpadding="3">
                <?php foreach($pp_noblocks[$prof_id] as $i=>$prj) {
                    $sName = /*$prj['moderator_status'] === '0' ? $stop_words->replace($prj['name'], 'plain') :*/ $prj['name'];
                    ?>
                <tr>
        			<td class="odd"><?=($i+1)?>.</td>
        			<td class="even">
                        <a href="/users/<?=$user->login?>/viewproj.php?prjid=<?=$prj['id']?>" target="_blank" class="blue"><?=$sName?></a><? $txt_cost = view_cost2($prj['prj_cost'], '', '', false, $prj['prj_cost_type']); $txt_time = view_time($prj['prj_time_value'], $prj['prj_time_type']);?> <span class="money" style="margin-left:8px;"><?=$txt_cost?></span><? if ($txt_cost != '' && $txt_time != '') { ?>, <? } ?><?=$txt_time?> &nbsp;
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
                            <div class="b-layout__txt b-layout__txt_lineheight_1 b-layout__txt_margleft_-15 b-layout__txt_nowrap">
                               <span class="b-layout__txt b-layout__txt_fontsize_22 b-layout__txt_color_808080 b-layout__txt_valign_middle">&#43;</span>
                               <a class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_bold b-layout__link_fontsize_11" href="#">Добавить работу</a>
                            </div>
                            <a class="admn" href="javascript:void(0);" onclick="banned.<?=($prj['is_blocked']=='t'? 'unblockedPortfolio': 'blockedPortfolio')?>(<?=$prj['id']?>)"><?= $prj['is_blocked']=='f' ? "Заблокировать" : "Разблокировать"; ?></a><br/>
                            <a class="admn" href="javascript:void(0);" onclick="adm_edit_content.editContent('admEditPortfolio', '<?=$prj['id']?>_0', 0, '')">Редактировать</a>
                        </div>
                        <?php 
                        }
                        else { ?>&nbsp;<?php }
                        ?>
                    </td>
        		</tr>
        		<?php } //foreach?>
        	</table>	
        </td>
        <?php } // else?>
	    <td style="width:14px">&nbsp;</td>
    </tr>
</table>
<?php }//foreach
if(!$is_count_project) { 
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
        	<p>В вашем портфолио сейчас нет ни одной работы</p><br/>
            <a class="b-button b-button_flat b-button_flat_green" href="<?= $aHref?>">Добавить работу</a>
        </div>
    <?php } else {//if?>
        <h2 style="text-align: center;"><?= ($user->tab_name_id == "1"?"Нет услуг":"Нет работ")?></h2>
    <?php } //else?>
<?php } //if ?>