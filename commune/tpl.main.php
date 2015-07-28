<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/commune.common.php");
$xajax->printJavascript('/xajax/');


global $id, $uid, $page, $om, $g_page_id, $user_mod, $rating;

$group_id = __paramInit('int', 'gr', NULL);

$gr_prm = $group_id === NULL ? '' : "gr={$group_id}&";
$s_prm = !$search ? '' : "&search={$search}";


$allCommCnt  = 0; // Всего сообществ.
$pageCommCnt = 0; // Количество сообществ на данной странице.
// Разделы.
if (!($commune_groups = commune::GetGroups()))
    $commune_groups = array();

// начало нумерации сообществ для своей сортировки
$start_position = ($page - 1) * $limit;

$pageCommCnt = count($communes);

// Классы закладок.
$bmCls = getBookmarksStyles(commune::OM_CM_COUNT, $om);
?>

<div class="b-community">
<? include ('in_out_dialog.php');?>
<? seo_start();?>  
<a class="b-button b-button_flat b-button_flat_green b-button_float_right b-button_margbot_-10"  href="?site=Create">Создать сообщество</a>
<?= seo_end();?>  


<?php
$crumbs = array();
if(!$gr_id) {
    //$crumbs[] = array("title"=>"Сообщества", "url"=>"");
} else {
    $crumbs[] = array("title"=>"Сообщества", "url"=>"/commune/");
    $crumbs[] = array("title"=>$sGroup, "url"=>"");
}
?>
<? /*= $gr_id ? getCrumbs($crumbs, "commune") : '<h1 class="b-page__title">Сообщества фрилансеров</h1>' */?>
<?= getCrumbs($crumbs, "commune")?>
<h1 class="b-page__title">Сообщества фрилансеров</h1>

<? ob_start(); ?>
<div class="b-menu b-menu_line b-menu_clear_both">
    <ul class="b-menu__list b-menu__list_padleft_28ps" data-menu="true" data-menu-descriptor="community-list">
        <li class="b-menu__item<?= $bmCls[commune::OM_CM_BEST] ?>" <?=((!$bmCls[commune::OM_CM_BEST] || $page > 1) ? '' : 'data-menu-opener="true" data-menu-descriptor="community-list" ')?>>
            <a href="?om=" class="b-menu__link "><span class="b-menu__b1">Лучшие</span></a>
        </li>
        <? seo_start();?>  
        <li class="b-menu__item<?= $bmCls[commune::OM_CM_POPULAR] ?>" <?=((!$bmCls[commune::OM_CM_POPULAR] || $page > 1) ? '' : 'data-menu-opener="true" data-menu-descriptor="community-list" ')?>>
    		<a href="?om=<?= commune::OM_CM_POPULAR ?>" class="b-menu__link "><span class="b-menu__b1">Популярные</span></a>
        </li>
        <li class="b-menu__item<?= $bmCls[commune::OM_CM_ACTUAL] ?>" <?=((!$bmCls[commune::OM_CM_ACTUAL] || $page > 1) ? '' : 'data-menu-opener="true" data-menu-descriptor="community-list" ')?>>
           	<a href="?om=<?= commune::OM_CM_ACTUAL ?>" class="b-menu__link "><span class="b-menu__b1">Актуальные</span></a>
        </li>
        <li class="b-menu__item<?= $bmCls[commune::OM_CM_NEW] ?>" <?=((!$bmCls[commune::OM_CM_NEW] || $page > 1) ? '' : 'data-menu-opener="true" data-menu-descriptor="community-list" ')?>>
           	<a href="?om=<?= commune::OM_CM_NEW ?>" class="b-menu__link "><span class="b-menu__b1">Новые</span></a>
        </li>
        <?php if(get_uid(false)) { ?>
        <li class="b-menu__item<?= $bmCls[commune::OM_CM_MY] ?>" <?=((!$bmCls[commune::OM_CM_MY] || $page > 1) ? '' : 'data-menu-opener="true" data-menu-descriptor="community-list" ')?>>
            <a href="?om=<?= commune::OM_CM_MY ?>" class="b-menu__link "><span class="b-menu__b1">Я создал</span></a>
        </li>
        <li class="b-menu__item<?= $bmCls[commune::OM_CM_JOINED] ?>" <?=((!$bmCls[commune::OM_CM_JOINED] || $page > 1) ? '' : 'data-menu-opener="true" data-menu-descriptor="community-list" ')?>>
            <a href="?om=<?= commune::OM_CM_JOINED ?>" class="b-menu__link "><span class="b-menu__b1">Я вступил</span></a>
        </li>
        <?php } ?>
        <li class="b-menu__item b-menu__item_promo b-page__desktop"><?php require_once($_SERVER['DOCUMENT_ROOT'] . "/banner_promo.php"); ?></li>
        <?= seo_end();?>  
    </ul>
</div>


<div class="b-layout b-layout_padtop_20">    
    <div class="b-layout__right b-layout__right_width_72ps b-layout__right_float_right">
		<? seo_start();?>  
        <div class="b-search b-layout b-layout_padbot_30">
            <form id="search_frm" method="get" action=".">
            <table cellspacing="0" cellpadding="0" class="b-search__table">
                <tr class="b-search__tr">
                    <td class="b-search__input">
                            <div class="b-input b-input_height_24">
                                <input type="text" name="search" class="b-input__text" id="b-input" value="<?= htmlspecialchars(stripslashes($_GET['search'])); ?>" placeholder="<?php if(!$_GET['search']) { ?>Найти сообщество, пост, комментарий<?php } else {  htmlspecialchars(stripslashes($_GET['search']));  } ?>">
                                <input type="hidden" name="om" value="<?= $om;?>">
                            </div>
                    </td>
                    <td class="b-search__button b-search__button_padleft_10">
                        <a href="javascript:void(0)" onclick="$('search_frm').submit()" class="b-button b-button_flat b-button_flat_grey">Найти</a>
                    </td>
                </tr>
            </table>
            </form>
        </div>
        <?= seo_end();?> 
         
            <? $is_empty_commune = (count($communes) == 0);
            if ($om) { ?>
            <div class="b-layout__txt b-layout__txt_padbot_20 b-layout__txt_bold">
            <?php
                switch ($om) {
                    case commune::OM_CM_BEST : print(!$is_empty_commune ? 'Первыми стоят сообщества с самым большим рейтингом' : 'Сообществ нет');
                        break;
                    case commune::OM_CM_POPULAR : print(!$is_empty_commune ? 'Первыми стоят сообщества с наибольшим количеством участников' : 'Сообществ нет');
                        break;
                    case commune::OM_CM_ACTUAL: print(!$is_empty_commune ? 'Первыми стоят сообщества, в которых недавно наблюдалась активность' : 'Сообществ нет');
                        break;
                    case commune::OM_CM_NEW : print(!$is_empty_commune ? 'Первыми стоят сообщества, созданные позже' : 'Сообществ нет');
                        break;
                    case commune::OM_CM_MY : print(!$is_empty_commune ? 'Сообщества, которые вы создали' : 'Вы еще не создали ни одного сообщества' );
                        break;
                    case commune::OM_CM_JOINED : print(!$is_empty_commune ? 'Первыми стоят сообщества, в которые вы вступили позже' : 'Вы еще не вступили ни в одно сообщество');
                        break;
                }
			?>
           </div>
            <? } ?>
        
        <?php if ($om == commune::OM_CM_JOINED && !$is_empty_commune) { 
            $href = '/commune/?'.$gr_prm.($om ? 'om='.$om : ''); ?>
            <div class="b-layout__txt b-layout__txt_padbot_20">
                Отсортировать
                <div class="b-filter" style="z-index: 10; ">
                    <div class="b-filter__body">
                        <a href="#" class="b-filter__link b-filter__link_ie7_top_3 b-filter__link_dot_0f71c8 b-layout__link_fontsize_13">
                            <?
                                switch ($sub_om) {
                                    case commune::OM_CM_JOINED_ACCEPTED : print('по дате вступления');
                                        break;
                                    case commune::OM_CM_JOINED_CREATED : print('по дате создания сообщества');
                                        break;
                                    case commune::OM_CM_JOINED_BEST : print('по рейтингу сообщества');
                                        break;
                                    case commune::OM_CM_JOINED_LAST : print('по дате последней темы в сообществе');
                                        break;
                                    case commune::OM_CM_JOINED_MY : print('по моим предпочтениям');
                                        break;
                                }
                            ?>
                        </a>
                    </div>
                    <div class="b-shadow b-shadow_marg_-32 b-filter__toggle b-filter__toggle_hide">
                                        <div class="b-shadow__body b-shadow__body_pad_15 b-shadow__body_bg_fff">
                                            <ul class="b-filter__list">
                                                <li class="b-filter__item b-filter__item_padbot_10 b-filter__item_lineheight_1 b-filter__item_msie_lineheight_15">
                                                    <a class="b-filter__link<? if ($sub_om != commune::OM_CM_JOINED_ACCEPTED) { ?> b-filter__link_dot_0f71c8<? } else { ?> b-filter__link_no<? } ?>" onclick="window.location='<?=$href?>&sub_om=<?=commune::OM_CM_JOINED_ACCEPTED?>'">по дате вступления</a>
                                                    <span class="b-filter__marker b-filter__marker_top_4 b-filter__marker_galka<? if ($sub_om != commune::OM_CM_JOINED_ACCEPTED) { ?> b-filter__marker_hide<? } ?>"></span>
                                                </li>
                                                <li class="b-filter__item b-filter__item_padbot_10 b-filter__item_lineheight_1 b-filter__item_msie_lineheight_15">
                                                    <a class="b-filter__link<? if ($sub_om != commune::OM_CM_JOINED_CREATED) { ?> b-filter__link_dot_0f71c8<? } else { ?> b-filter__link_no<? } ?>" onclick="window.location='<?=$href?>&sub_om=<?=commune::OM_CM_JOINED_CREATED?>'">по дате создания сообщества</a>
                                                    <span class="b-filter__marker b-filter__marker_top_4 b-filter__marker_galka<? if ($sub_om != commune::OM_CM_JOINED_CREATED) { ?> b-filter__marker_hide<? } ?>"></span>
                                                </li>
                                                <li class="b-filter__item b-filter__item_padbot_10 b-filter__item_lineheight_1 b-filter__item_msie_lineheight_15">
                                                    <a class="b-filter__link<? if ($sub_om != commune::OM_CM_JOINED_BEST) { ?> b-filter__link_dot_0f71c8<? } else { ?> b-filter__link_no<? } ?>" onclick="window.location='<?=$href?>&sub_om=<?=commune::OM_CM_JOINED_BEST?>'">по рейтингу сообщества</a>
                                                    <span class="b-filter__marker b-filter__marker_top_4 b-filter__marker_galka<? if ($sub_om != commune::OM_CM_JOINED_BEST) { ?> b-filter__marker_hide<? } ?>"></span>
                                                </li>
                                                <li class="b-filter__item b-filter__item_padbot_10 b-filter__item_lineheight_1 b-filter__item_msie_lineheight_15">
                                                    <a class="b-filter__link<? if ($sub_om != commune::OM_CM_JOINED_LAST) { ?> b-filter__link_dot_0f71c8<? } else { ?> b-filter__link_no<? } ?>"onclick="window.location='<?=$href?>&sub_om=<?=commune::OM_CM_JOINED_LAST?>'">по дате последней темы в сообществе</a>
                                                    <span class="b-filter__marker b-filter__marker_top_4  b-filter__marker_galka<? if ($sub_om != commune::OM_CM_JOINED_LAST) { ?> b-filter__marker_hide<? } ?>"></span>
                                                </li>
                                                <li class="b-filter__item b-filter__item_padbot_3 b-filter__item_lineheight_1 b-filter__item_msie_lineheight_15">
                                                    <a class="b-filter__link<? if ($sub_om != commune::OM_CM_JOINED_MY) { ?> b-filter__link_dot_0f71c8<? } else { ?> b-filter__link_no<? } ?>"onclick="window.location='<?=$href?>&sub_om=<?=commune::OM_CM_JOINED_MY?>'">по моим предпочтениям</a>
                                                    <span class="b-filter__marker b-filter__marker_top_4  b-filter__marker_galka<? if ($sub_om != commune::OM_CM_JOINED_MY) { ?> b-filter__marker_hide<? } ?>"></span>
                                                </li>
                                            </ul>
                                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
                
        <? //__commPrintPage( $page, $communes, $groupCommCnt, $sub_om, $search )?>
        <? include(ABS_PATH . "/commune/tpl.communes_list.php"); ?>
        <?//= ($is_empty_commune && $om !== commune::OM_CM_MY ? '<div class="b-layout__txt b-layout__txt_padbot_20 b-layout__txt_bold">Сообществ нет</div>' : '') ?>

        <?
        $uq = array();
        if($om) $uq[] = "om={$om}";
        if($rating) $uq[] = "rating={$rating}";
        if($gr_prm) $uq[] = $gr_prm;
        if($s_prm) $uq[] = $s_prm;
        $url_p = "%s/commune/?".implode("&", $uq).(count($uq) ? '&' : '')."page=%d%s";
        echo new_paginator($page, $pages, 3, $url_p);
        ?>
    </div>
    
    
    <div class="b-layout__left b-layout__left_width_25ps">
        <div class="b-menu b-menu_vertical b-menu_padbot_20">
            <? if (!$om) { ?>
            <? seo_start();?>  
            <ul class="b-menu__list">
                <li class="b-menu__item b-menu__item_padbot_5">
                    <div class="b-menu__number b-menu__number_fontsize_11">
                    </div>
                    <? if ($rating) { ?>
                        <a class="b-menu__link" href="?rating=">C любым рейтингом</a>
                    <? } else { ?>
                        <a class="b-menu__link b-menu__h" style="color: #000;" href="?rating=">C любым рейтингом</a>
                    <? } ?>
                </li>
                <li class="b-menu__item b-menu__item_padbot_5">
                    <div class="b-menu__number b-menu__number_fontsize_11">
                    </div>
                    <? if ($rating != 'bronze') { ?>
                        <a class="b-menu__link" href="?rating=bronze">Бронзовые</a>
                        <span class="b-menu__txt b-menu__txt_fontsize_11">&nbsp;с рейтингом от 50</span>
                    <? } else { ?>
                        <a class="b-menu__link b-menu__h" style="color: #000;" href="?rating=bronze">Бронзовые</a>
                    <? } ?>
                </li>
                <li class="b-menu__item b-menu__item_padbot_5">
                    <div class="b-menu__number b-menu__number_fontsize_11">
                    </div>
                    <? if ($rating != 'silver') { ?>
                        <a class="b-menu__link" href="?rating=silver">Серебряные</a>
                        <span class="b-menu__txt b-menu__txt_fontsize_11">&nbsp;от 200</span>
                    <? } else { ?>
                        <a class="b-menu__link b-menu__h" style="color: #000;" href="?rating=silver">Серебряные</a>
                    <? } ?>
                </li>
                <li class="b-menu__item b-menu__item_padbot_5">
                    <div class="b-menu__number b-menu__number_fontsize_11">
                    </div>
                    <? if ($rating != 'gold') { ?>
                        <a class="b-menu__link" href="?rating=gold">Золотые</a>
                        <span class="b-menu__txt b-menu__txt_fontsize_11">&nbsp;от 1000</span>
                    <? } else { ?>
                        <a class="b-menu__link b-menu__h" style="color: #000;" href="?rating=gold">Золотые</a>
                    <? } ?>
                </li>
            </ul>
            <?= seo_end();?>  
            <? } ?>
        </div>
        <div class="b-menu b-menu_width_240 b-menu_vertical b-menu_padbot_20">
            <ul class="b-menu__list">
                <?
                $html = '';
                $i = 0;
                $gCnt = count($commune_groups);
                for ($i; $i < $gCnt; $i++) {
                    $grp = $commune_groups[$i];
                    $allCommCnt += (int) $grp['a_count'];
                    $cnt = $grp['a_count'] ? " {$grp['a_count']}" : '';
                    $cls = $i == $gCnt - 1 ? ' class="last"' : '';
                    $html .= '<li class="b-menu__item b-menu__item_padbot_5">' .
                            (
                            //$group_id != $grp['id'] || ($group_id == $grp['id'] && $page > 1) ? "<a ". (($group_id == $grp['id'] && $page > 1) ? ' style="font-weight: bolder; color: #666;"' : '') ." href='".getFriendlyURL('commune_group', $grp['id']).'?'.($om ? "&om={$om}" : '') . ($rating ? '&rating=' . $rating : '') . "'>{$grp['name']}{$cnt}</a>" : "<strong>{$grp['name']}{$cnt}</strong>"
                            $group_id != $grp['id'] || ($group_id == $grp['id'] && $page > 1) ?
                                '<div class="b-menu__number b-menu__number_fontsize_11">'.$cnt.'</div>'.
                                '<a class="b-menu__link"'.
                                " href='".getFriendlyURL('commune_group', $grp['id']).
                                (($om || $rating)? ('?'.($om ? "&om={$om}" : '').($rating ? '&rating=' . $rating : '')): '').
                                "'>{$grp['name']}</a>" 
                                : 
                                '<div class="b-menu__number b-menu__number_fontsize_11">'.$cnt.'</div>'.
                                '<a class="b-menu__link b-menu__h" style="color: #000;"'.
                                " href='".getFriendlyURL('commune_group', $grp['id']).
                                (($om || $rating)? ('?'.($om ? "&om={$om}" : '').($rating ? '&rating=' . $rating : '')): '').
                                "'>{$grp['name']}</a>" 
                            ) .
                            "</li>";
                }
                $cnt = $allCommCnt ? " {$allCommCnt}" : '';
                ?>
                <li class="b-menu__item b-menu__item_padbot_5">
                    <div class="b-menu__number b-menu__number_fontsize_11"><?= $cnt ?></div>
                    <? if ( $group_id === NULL && $page == 1 ) { ?>
                        <a class="b-menu__link b-menu__h" style="color: #000;" href="/commune/<?= ($om ? '?om='.$om : '') ?>">Все сообщества</a>
                    <? } else { ?>
                        <a class="b-menu__link" href="/commune/<?= ($om ? '?om='.$om : '') ?>">Все сообщества</a>
                    <? } ?>
                </li>
                <?= $html ?>
            </ul>
        </div>
        
        <div class="b-layout b-layout_width_240">
            <!-- Banner 240x400 -->
            <?= printBanner240(false, true);?>
            <!-- end of Banner 240x400 -->
        </div>
    </div>
            
</div>
<a id="upper" class="b-page__up" href="#" style=" visibility:hidden;"></a>

</div>