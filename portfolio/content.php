<?
$filter_page = 4;
$filter_show = isset($_COOKIE['new_pf'.$filter_page]) ? $_COOKIE['new_pf'.$filter_page] : 1;

require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/portfolio.common.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
$prfs = new professions();
$profs = $prfs->GetAllProfessions("",0, 1);



if (!$prof_id) {
    $prtfs = $portfolio->GetSpecPortfMain($count, $size, $prf_pp, ($page - 1) * $prf_pp, $orderby, $direction, $fav_show, $filter_apply, $pf);
    if ($prtfs === false) {
        $size = 0;
    } else {
        $size = sizeof($prtfs);
    }
} else {
	$prtfs = $portfolio->GetSpecPortf($prof_id, $count, $size, $prf_pp, ($page - 1) * $prf_pp, $orderby, $direction, $fav_show, $filter_apply, $pf);
	if ($prtfs === false) {
	   $size = 0;
	} else {
	    $size = sizeof($prtfs);
	}
	
	
}

$xajax->printJavascript('/xajax/');
?>
<?php /*
<script type="text/javascript">var ___isIE5_5 = 1;</script>
<![if lt IE 5.5]>
<script type="text/javascript">var ___isIE5_5 = 0;</script>
<![endif]>
*/ ?>
<script type="text/javascript">
var ___WDCPREFIX = '<?=WDCPREFIX?>';
</script>

<a id="frl_anc" name="frl"></a>

    <h1 class="b-page__title">Все работы</h1>
<div class="b-layout__right b-layout__right_relative b-layout__left_width_72ps b-layout__left_float_left">
    <div class="b-menu b-menu_line b-menu_padbot_10 b-menu__cat" >
        <div class=" cat-tab">
            <div class="b-menu"  data-accordion="true" data-accordion-descriptor="worktype">
                <ul class="b-menu__list">
                    <li class="b-menu__item">
                        <a class="b-menu__link" href="/freelancers/" title="Все фрилансеры">
                            <span class="b-menu__b1">Все фрилансеры</span>
                        </a>
                    </li>
                    <li class="b-menu__item b-menu__item_active" data-accordion-opener="true" data-accordion-descriptor="worktype">
                        <a class="b-menu__link" href="/portfolio/<?= ($prof_id ? '?prof=' . $prof_id : '') ?>" title="Работы">
                            <span class="b-menu__b1">Работы</span>
                        </a>
                    </li>
                    <li class="b-menu__item"><a class="b-menu__link" href="/clients/<?= ($prof_id) ? '?prof=' . $prof_id : "" ?>" title="Клиенты"><span class="b-menu__b1">Клиенты</span></a></li>
				<li class="b-menu__item b-menu__item_last b-page__ipad b-page__iphone"><a class="b-menu__link" href="/profi/"><span class="b-menu__b1">PROFI</span></a></li>
				<li class="b-menu__item b-menu__item_padbot_null b-page__desktop"><a class="b-menu__link" href="/profi/"><span class="b-icon b-icon__profi b-icon_valign_bas" data-profi-txt="Лучшие фрилансеры сайта FL.ru. Работают на сайте более 2-х лет, прошли верификацию личности и имеют не менее 98% положительных отзывов."></span></a></li>
                    <li class="b-menu__item b-menu__item_promo b-page__desktop"><?php require_once($_SERVER['DOCUMENT_ROOT'] . "/banner_promo.php"); ?></li>
                </ul>
            </div>
        </div>
    </div>
		
        <div class="b-layout__txt b-layout__txt_padbot_20">В разделе представлены работы только пользователей расширенного аккаунта <a class="b-layout__link" href="/payed/"><span title="платного аккаунта" class="b-icon b-icon__pro b-icon__pro_f"></span></a></div>
    <!-- Фильтр -->
    <div id="flt-works" class="flt-out <?=(($filter_show)?"flt-show":"flt-hide")?>" page="<?=$filter_page?>">
        <form action="/portfolio/" method="post" enctype="multipart/form-data">
        <div>
        <input type="hidden" name="action" value="postfilter" />
        <input type="hidden" name="prof" value="<?=$prof_id?>" />
        <input type="hidden" name="order" value="<?=$order?>" />
        <input type="hidden" name="dir" value="<?=$direction?>" />
        <div class="flt-bar">
            <a href="javascript: void(0);" class="flt-tgl-lnk"><?=(($filter_show)?"Свернуть":"Развернуть")?></a>
            <h3>Фильтр</h3> 
             <? if ($filter_apply): ?> 
             <span class="flt-stat flt-on">включен&nbsp;&nbsp;&nbsp;<a href="/portfolio/index.php?action=deletefilter<?=($fav_show) ? "&fs=" . $fav_show : ''?><?=($order) ? "&order=" . $order : ''?><?=($direction) ? "&dir=" . $direction : ''?><?=($prof_id)?"&prof=".$prof_id:""?>" class="flt-lnk">отключить</a></span> 
             <? else: ?>
             <span class="flt-stat flt-off">отключен&nbsp;&nbsp;&nbsp;<a href="/portfolio/index.php?action=activefilter<?=($fav_show) ? "&fs=" . $fav_show : ''?><?=($order) ? "&order=" . $order : ''?><?=($direction) ? "&dir=" . $direction : ''?><?=($prof_id)?"&prof=".$prof_id:""?>" class="flt-lnk">включить</a></span> 
             <? endif; ?>
        </div>
        <div class="flt-cnt">
            <div class="flt-block flt-b-fc">
                <label class="flt-lbl">Стоимость работы:</label>
                <div class="flt-b-in">
                    <span class="flt-prm">
                        <input type="text" size="10" class="flt-prm1" id="pf_cost_from" name="pf_cost_from" value="<?=($pf['cost_from']>0?$pf['cost_from']:"")?>" maxlength="6" /> &mdash; <input type="text" size="10" class="flt-prm1" id="pf_cost_to" name="pf_cost_to" value="<?=($pf['cost_to']>0?$pf['cost_to']:"")?>" maxlength="6" />&nbsp;&nbsp;
                        <select class="pf-sel" name="pf_cost_type">
                            <option value="2" <?=(!strlen($pf['cost_type']) || $pf['cost_type'] == 2 ? "selected=\"selected\"" : "")?>>Руб</option>
                            <option value="0" <?=(strlen($pf['cost_type']) && $pf['cost_type'] == 0 ? "selected=\"selected\"" : "")?>>USD</option>
                            <option value="1" <?=($pf['cost_type'] == 1 ? "selected=\"selected\"" : "")?>>Euro</option>
                        </select>
                    </span>
                </div>
                    <input type="button" id="pf_save" name="pf_save" class="i-btn" value="Применить фильтр" onClick="submit();"/>
            </div>
        </div>
        </div>
        </form>
    </div>
    <!-- Конец фильтра -->
    <? if($size == 0): ?>
    <div>Работ не найдено.</div>
    <? else: ?>
    <div class="b-menu b-menu_simple b-menu_padbot_20 b-menu_bordbot_b2">
      <ul class="b-menu__list">
          <li class="b-menu__item b-menu__item_margright_15"><a class="b-menu__link b-menu__link_fontsize_11 <? if (!$order || $order == 'rating'): ?>b-menu__link_bold<? endif; ?>" href="./?order=rating<?=($prof_id)?"&prof=".$prof_id:""?><?=($fav_show)?"&fs=".$fav_show:""?>">По рейтингу фрилансера</a></li>
          <li class="b-menu__item b-menu__item_margright_15"><a class="b-menu__link b-menu__link_fontsize_11 <? if ($order == 'rnd'): ?>b-menu__link_bold<? endif; ?>" href="./?order=rnd<?=($prof_id)?"&prof=".$prof_id:""?><?=($fav_show)?"&fs=".$fav_show:""?>">Случайно</a></li>
          <li class="b-menu__item b-menu__item_margright_15"><a class="b-menu__link b-menu__link_fontsize_11 <? if ($order == 'prc'): ?>b-menu__link_bold<? endif; ?>" href="./?order=prc<?=($prof_id)?"&prof=".$prof_id:""?><?=($fav_show)?"&fs=".$fav_show:""?>">По цене работы</a></li>
          <li class="b-menu__item"><a class="b-menu__link b-menu__link_fontsize_11 <? if ($order == 'ops'): ?>b-menu__link_bold<? endif; ?>" href="./?order=ops<?=($prof_id)?"&prof=".$prof_id:""?><?=($fav_show)?"&fs=".$fav_show:""?>">По отзывам заказчиков фрилансеру</a></li>
      </ul>
    </div>                
    <table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0">
        <? if($prtfs): ?>
        <? $i=0;foreach ($prtfs as $key => $prf): if($i>=3) $i = 0; ?>
        <?=($i==0?"<tr class='b-layout__tr'>":"");$i++;?>
            <td class="b-layout__td b-layout__td_bordbot_c3 b-layout__td_padright_20 b-layout__td_padtb_20 b-layout__td_width_33ps">
                    <h3 class="b-layout__h3"><a class="b-layout__link b-layout__link_bold" href="/users/<?=$prf['login']?>/viewproj.php?prjid=<?=$prf['id']?>" target="_blank" title="<?=$prf['name']?>"><?=reformat($prf['name'], 25, 0, 1)?></a></h3>
                    <? if ($prf['prev_type']==1 || ($prof_id == 0 && isset($prf['is_text']) && $prf['is_text'] == 't')): ?>
                        <?=viewdescr($prf['login'], reformat($prf['descr'], 42, 0, 1))?>
                    <? else: ?>
                    <a href="/users/<?=$prf['login']?>/viewproj.php?prjid=<?=$prf['id']?>" target="_blank" title="<?=$prf['name']?>">
                        <?=view_preview($prf['login'], $prf['prev_pict'], "upload", 'left', false, false, $prf['name'], 200)?>
                    </a>
                    <? endif; ?>
                    <div class="b-layout__txt b-layout__txt_padtop_10">
                        <a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_bold" href="/users/<?=$prf['login']?>/"><?=$prf['uname']?> <?=$prf['usurname']?> [<?=$prf['login']?>]</a><br /> 
                        <? $txt_cost = view_cost2($prf['cost'], '', '', true, $prf['cost_type']);  $txt_time = view_time($prf['time_value'], $prf['time_type']);?>
                        <strong class="b-layout__txt b-layout__txt_bold"><?=$txt_cost?></strong><? if ($txt_cost != '' && $txt_time != '') { ?>, <? } ?><?=$txt_time?>
                    </div>
            </td>
        <?=($i==3?"</tr>":"")?>   
        <? endforeach; ?>
        <? if($i<3): ?>
            <? for($k=$i;$k<3;$k++) { echo "<td class='b-layout_one'>&#160;</td>"; } echo "</tr>";?>
        <? endif; ?>
        <? endif; ?>
    </table>
    <? endif; ?>
    
    <? 
    $pages = ceil($count / $prf_pp); 
    $sHref = "%s?".(($order)?"order=$order&":"").(($direction)?"dir=$direction&":"").(($prof_id)?"prof=".$prof_id."&":"")."page=%d%s";
    echo new_paginator($page, $pages, 3, $sHref);
    ?>
</div>

<div class="b-layout__left b-layout__left_width_25ps b-layout__right_margleft_3ps b-layout__right_float_left">
    
    <? /* include($_SERVER['DOCUMENT_ROOT'] . '/freelancers/tpl.catmenu.new.php'); */ ?>
                        
    <!-- Banner 240x400 -->
    <?= printBanner240(false); ?>
    <!-- end of Banner 240x400 -->

    
    <? if(!get_uid() && $prof_descr != ''): ?>
    <div class="main-text-seo">
        <b class="b1"></b>
        <b class="b2"></b>
		<div class="main-text-seo-in"> <?=$prof_descr?></div>
		<b class="b2"></b>
		<b class="b1"></b>
	</div>
    <? endif; ?>
</div>
<style type="text/css">
.flt-b-lc_last{ padding-left:155px;}
@media screen and (max-width: 1000px){
.b-layout__page .b-layout__left .b-catalog{ top:20px;}
.b-layout__title{ margin-right:150px !important;}
.b-layout__right{ width:100% !important;}
.b-layout__page .body .main td.b-layout__td{ display:table-cell;}
.b-layout__page .body .main tr.b-layout__tr{ display:table-row;}
.b-layout__page .body .main table.b-layout__table{ display:table;}
.flt-prm{ display:block; width:240px;}
}
@media screen and (max-width: 650px){
.b-layout__page .body .main td.b-layout__td, .b-layout__page .body .main tr.b-layout__tr, .b-layout__page .body .main table.b-layout__table{ display: block; width:100%;}
}
@media screen and (max-width: 450px){
.b-layout__page .body .main td.b-layout__td, .b-layout__page .body .main tr.b-layout__tr, .b-layout__page .body .main table.b-layout__table{ display: block; width:100%;}
.flt-block .flt-lbl{ float:none; padding:0;}
.flt-b-lc_last{ padding-left:15px !important; padding-top:7px !important;}
}
</style>			
