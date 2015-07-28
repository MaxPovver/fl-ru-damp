<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/freelancers.common.php");
//require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/fptext.common.php");

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancers_filter.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stat_collector.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/kwords.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/rating.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/project_exrates.php");

//$pUStat = firstpage::ShowStats();

/*
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
*/


$cur_prof = $promo_profs = null;
$prfs  = new professions();
$profs = $prfs->GetAllProfessions("",0, 1);

// Сортировка категорий профессий по названию
usort($profs, function($a, $b) { return strcmp($a['groupname'], $b['groupname']);});

// ищем текущую профессию
foreach ($profs as $key => $value) {
    if ($value['id'] == $prof_id) {
        $cur_prof = $value;
        break;
    }
}

if (!$cur_prof) {
    //Ищем профессии для блока
    foreach ($profs as $key => $value) {
        $case = $prof_group_id ? $value['groupid'] == $prof_group_id : false;
        if ($case) {
            $promo_profs[] = $value;
        }
    }
}

// Сортировка подкатегорий профессий по названию
if ($promo_profs) {
    usort($promo_profs, function($a, $b) { return strcmp($a['profname'], $b['profname']);});
}

$favs = $freelancer->GetFavorites($prof_id, $uid, $filter_apply, $ff);

$xajax->printJavascript('/xajax/');

?>
<script type="text/javascript">var ___isIE5_5 = 1;</script>
<![if lt IE 5.5]>
<script type="text/javascript">var ___isIE5_5 = 0;</script>
<![endif]>
<script type="text/javascript">
var ___WDCPREFIX = '<?=WDCPREFIX?>';
</script>




<div class="b-freelancers-collection <?php if($cur_prof||$prof_group_id && $prof_name){ ?>b-breadcrumbed-freelancers-collection<?php } ?>">
<a name="frl" id="frl_anc"></a>


<?php
$crumbs = array();
if($cur_prof) {
    $crumbs[] = array("title"=>"Каталог фрилансеров", "url"=>"/freelancers/");
    $crumbs[] = array("title"=>$cur_prof['groupname'], "url"=>"/freelancers/".$cur_prof['grouplink'].'/?'.$query_string_cat);
    $crumbs[] = array("title"=>$cur_prof['profname'], "url"=>false);
    $pageTitle = $cur_prof['groupname'] . " / " . $cur_prof['profname'];
} elseif ($prof_group_id && $prof_name) {
    $crumbs[] = array("title"=>"Каталог фрилансеров", "url"=>"/freelancers/");
    $crumbs[] = array("title"=>$prof_name, "url"=>"");
    $pageTitle = $prof_name;
} else {
    $pageTitle = 'Каталог фрилансеров';
}
?>

<?=getCrumbs($crumbs, "freelancers")?>
<div class="b-layout__right b-layout__right_relative">


<h1 class="b-page__title"><?= (isset($page_h1) && $page_h1)?$page_h1:$pageTitle ?><span class="b-layout__txt b-layout__txt_float_right b-layout__txt_padtop_10"><?php require_once($_SERVER['DOCUMENT_ROOT'] . "/banner_promo.php"); ?></span></h1>
</div>
                       
<?php // Категории (профессии) для фильтрации
include (dirname(__FILE__).'/../tpl.categories_top.php');
?>

<div class="b-layout__right b-layout__right_relative b-layout__left_width_72ps b-layout__left_float_left">
                       <?php
                        if($f_country_id && $cur_prof['id']) {
                            $cur_prof['descr_text'] = professions::GetProfGEOField($cur_prof['id'], 'descr_text', $f_country_id, $f_city_id);
                            $cur_prof['descr_text2'] = professions::GetProfGEOField($cur_prof['id'], 'descr_text2', $f_country_id, $f_city_id);
                        } 
                        ?>

                        <?php
                            // если пользователь неавторизован и на страницу попали с Директа или AdWords, то не показываем рекламный блок
                            $utm_source = $_GET['utm_source'];
                            if ( !( (get_uid(0) < 1) && ($utm_source === "yandex" || $utm_source === "google") ) ):
                        ?>
                        <span id="catalog_promo"></span>
						<?/*<script type="text/javascript">catalog_promo(<?=$prof_id?>);</script>*/?>
                                                
			<?php endif; ?>
                        
                        
                            <div class="b-menu b-menu_line b-menu_relative b-menu_padbot_10 b-menu__cat b-menu_zindex_6" >
                            <?php
                            
                            
                            
                            
                            
                            
                            if(false):
                            
                            $region_filter_txt = '<strong>Все</strong>';
                            if($filter_apply) {
                                $region_filter_country_id = $mFilter['country'];
                                $region_filter_city_id = $mFilter['city'];
                            } else {
                                $region_filter_country_id = $_SESSION['region_filter_country'];
                                $region_filter_city_id = $_SESSION['region_filter_city'];
                            }
                            $_SESSION['region_filter_country'] = $region_filter_country_id;
                            $_SESSION['region_filter_city'] = $region_filter_city_id;
                            $region_filter_countries = country::GetCountries();
                            if ($region_filter_country_id) {$region_filter_cities = city::GetCities($region_filter_country_id);}
                            if($region_filter_country_id) {
                                foreach ($region_filter_countries as $countid => $country) {
                                    if($countid==$region_filter_country_id) $region_filter_country_txt = $country;
                                }
                                $region_filter_txt = '<strong>'.$region_filter_country_txt.'</strong>';
                                if($region_filter_city_id) {
                                    foreach ($region_filter_cities as $cityid => $city) {
                                        if($cityid==$region_filter_city_id) $region_filter_city_txt = $city;
                                    }
                                    $region_filter_txt .= ', <strong>'.$region_filter_city_txt.'</strong>';
                                }
                            }
                            
                            endif;
                            
                            
                            
                            
                            
                            
                            
                            ?>
                                
                            <?php if(false): ?>    
                            <div class="region_choose">Регион: <?=$region_filter_txt?> &nbsp; <a href="#" onClick="$('popup_region_filter').toggleClass('b-shadow_hide'); return false;">Изменить</a></div>
                            <?php endif; ?>

                            
                            <div class=" cat-tab">
                            <div class="b-menu"  data-accordion="true" data-accordion-descriptor="worktype">
                            
                            <ul class="b-menu__list">
                                <? seo_start();?>
                                <li class="b-menu__item <?php if ($show_all_freelancers):?>b-menu__item_active <?php endif;?>" <?php if ($show_all_freelancers):?>data-accordion-opener="true" data-accordion-descriptor="worktype"<?php endif;?>><a class="b-menu__link" href="/freelancers/<?=($prof_link ? $prof_link : '')?>" title="Все фрилансеры"><span class="b-menu__b1">Все фрилансеры</span></a></li>
				<li class="b-menu__item"><a class="b-menu__link" href="/portfolio/<?=($prof_id ? '?prof='.$prof_id : '')?>" title="Работы"><span class="b-menu__b1">Работы</span></a></li>
				<li class="b-menu__item"><a class="b-menu__link" href="/clients/<?=($prof_id)?'?prof='.$prof_id:""?>" title="Клиенты"><span class="b-menu__b1">Клиенты</span></a></li>
				<li class="b-menu__item b-menu__item_last b-page__ipad b-page__iphone"><a class="b-menu__link" href="/profi/"><span class="b-menu__b1">PROFI</span></a></li>
				<li class="b-menu__item b-menu__item_padbot_null b-page__desktop"><a class="b-menu__link" href="/profi/"><span class="b-icon b-icon__profi b-icon_valign_bas" data-profi-txt="Лучшие фрилансеры сайта FL.ru. Работают на сайте более 2-х лет, прошли верификацию личности и имеют не менее 98% положительных отзывов."></span></a>
                                <?= seo_end(); ?>
                            </ul>
							</div>							
							</div>							
                        </div>

                                                
                                                
                        <? //include($_SERVER['DOCUMENT_ROOT'].'/freelancers/filter.php') ?>
                                                
                        <?php include ($_SERVER['DOCUMENT_ROOT']."/freelancers/search/tpl.form-search.php"); ?>                        
                                                
                                                
                                                
                        <?php
                        
                        if($f_country_id && $cur_prof['id']) {
                            $prof_descr = professions::GetProfGEOField($cur_prof['id'], 'descr', $f_country_id, $f_city_id);
                        }
                        
                        ?>
                        
                        
 
                        <table class="catalog-freelancers" cellpadding="0" cellspacing="0" border="0">
                            <?if($frls):?>
                            <col width="32" />
                            <col />
                            <col />
                            <col />
                            <col />
                            <col />
                            <col />
                            <col />
                            <?endif;?>
                            <thead>
                                <?if($frls):?>
                                <tr>
                                    <th colspan="2" class="cf-getpro">
                                    </th>
                                    <th class="cf-sortable cf-lc">Рейтинг</th>
                                    <th class="cf-sortable">Рекомен-<br />дации</th>
                                    <th class="cf-sortable">Мнения</th>
                                    <th class="cf-sortable">Цена <br />за час</th>
                                    <?php if ($prof_type): ?>
                                    <th class="cf-sortable">Цена за<br />1000 зн.</th>
                                    <?php else: ?>
                                    <th class="cf-sortable">Цена <br />за проект</th>
                                    <?php endif; ?>
                                    <th class="cf-sortable cf-rc">Цена <br />в месяц</th>
                                </tr>
                            </thead>
                            <tbody>    
                                <?else:?>
                                <tr>
                                    <th class="cf-getpro">
                                        <?php if( $_SESSION['login'] && !is_pro() ):?>
                                        <a href="/payed/">Получить аккаунт</a> 
                                        <a href="/payed/"><?=is_emp()?view_pro_emp():view_pro(false,false,false)?></a>
                                        <?php endif; ?>
                                    </th>
                                </tr> 
                            </thead>
                            <tbody>  
                                <tr>
                                    <td><?=$filter_apply?"Попробуйте изменить критерии поиска":"Фрилансеров не найдено"?> </td>
                                </tr>  
                                <?endif;?>
                            

        <?
        $iter = 0;
        $dec = 1;
        $pro_title = 0;
        $frl = $frls[$iter++];
        $frl_old = $frl['login'];
        $table = 0;

        while ($table < 2)
        {
            while ($iter <= $size && (($frl['is_pro'] == 't' && $table == 0) || ($frl['is_pro'] == 'f' && $table == 1)))
            {
                $flg = 0;
                $i = 0;
                if (!$frl) break;  
        ?>

                                <tr class="cf-line <? if ($table == 0) { ?>is-pro<? } ?>">
                                    <td class="cf-fav">
                                        <div>
                                            <a href="javascript:void(1)"><? if (($frl['uid'] != $uid) && ($uid > 0)) { ?><img id="favstar_<?=$frl['uid']?>" src="/images/<? if ($table == 0) { ?>ico_star_<? if (in_array($frl['uid'], $favs) || $fav_show) { ?>yellow<? } else { ?>empty<? } ?>_green<? } else { ?>ico_star_<? if (in_array($frl['uid'], $favs) || $fav_show) { ?>yellow<? } else { ?>empty<? } ?>_grey<? } ?>.gif" alt="" width="10" height="11" border="0" style="cursor:pointer" onClick="xajax_AddFav(<?=$frl['uid']?>, <?=$prof_id?>, '<?=$frl['is_pro']?>')"><? } else { ?><img src="/images/1.gif" alt="" width="10" height="11" border="0"><? } ?></a>
                                        </div>
                                    </td>
                                    <td class="cf-user">
                                        <?=view_avatar($frl['login'], $frl['photo'], 1, 0, "cf-avatar")?>
                                        <div class="cf-user-in">
                                            <?
                                            $frl['role'] = $GLOBALS['frlmask'];
                                            $kw_param = ($kword_stat) ? '&kw='.urlencode(stripslashes($kword_stat)) : '';
                                            print(view_user2($frl,'','freelancer-name','','?f='.stat_collector::REFID_CATALOG.'&stamp='.$_SESSION['stamp'].$kw_param.'#'.$anchor,TRUE, TRUE, "yaCounter6051055.reachGoal('frl_cat_ref');"));
                                            ?>
                                            <span class="cf-spec">
                                                Специализация: <?php if($frl['name_prof']):?><?=$frl['name_prof']?><?php else: ?>Нет специализации<?php endif; ?>
                                                <?php if($frl['additional_spec']) {?><br/>Дополнительные специализации: <?=$frl['additional_spec']?><?php }//if?>

                                                <?php
                                                $frl_info_for_reg =unserialize($frl['info_for_reg']); 
                                                $str_location = '';
                                                if($region_filter_country_id) {
                                                    if($frl['country']) {
                                                        if(!($frl_info_for_reg['country'] && !get_uid(false))) {
                                                            $str_location = $frl['str_country'];
                                                        }
                                                    }
                                                    if($frl['city']) {
                                                        if(!($frl_info_for_reg['city'] && !get_uid(false))) {
                                                            $str_location .= ($str_location ? " / ".$frl['str_city'] : $frl['str_city']);
                                                        }
                                                    }
                                                }
                                                if($str_location) {
                                                    echo "<br>Регион: {$str_location}";
                                                }
                                                ?>
                                            </span>
                                                                                        
                                            
                                            
                                            <? if ($frl['status_type'] != -1) {?><?=freelancer::viewStatus($frl['status_type'], true)?> <? } ?> 
                                        </div>
                                    </td>
                                    <td><?=rating::round($frl['t_rating'])?></td>
                                    <td style="width:65px;">
                                        <? seo_start();?>
                                        <span class="review-type">+</span><span class="review-plus"><a href="/users/<?=$frl['login']?>/opinions/?from=sbr&sort=1&f=<?=stat_collector::REFID_CATALOG?>&stamp=<?=$_SESSION['stamp']?><?=$kw_param?>"><?=zin($frl['total_opi_plus'])?></a></span><br />
                                        <span class="review-type"></span><span class="review-neitral"><a href="/users/<?=$frl['login']?>/opinions/?from=sbr&sort=2&f=<?=stat_collector::REFID_CATALOG?>&stamp=<?=$_SESSION['stamp']?><?=$kw_param?>"><?=zin($frl['total_opi_null'])?></a></span><br />
                                        <span class="review-type">-</span><span class="review-minus"><a href="/users/<?=$frl['login']?>/opinions/?from=sbr&sort=3&f=<?=stat_collector::REFID_CATALOG?>&stamp=<?=$_SESSION['stamp']?><?=$kw_param?>"><?=zin($frl['total_opi_minus'])?></a></span>
                                        <?= seo_end(); ?>
                                    </td>
                                    <td style="width:65px;">
                                        <? seo_start(); ?>
                                        <span class="review-type">+</span><span class="review-plus"><a href="/users/<?=$frl['login']?>/opinions/?from=users&sort=1&f=<?=stat_collector::REFID_CATALOG?>&stamp=<?=$_SESSION['stamp']?><?=$kw_param?>"><?=zin($frl['sg'])?></a></span><br />
                                        <span class="review-type"></span><span class="review-neitral"><a href="/users/<?=$frl['login']?>/opinions/?from=users&sort=2&f=<?=stat_collector::REFID_CATALOG?>&stamp=<?=$_SESSION['stamp']?><?=$kw_param?>"><?=zin($frl['se'])?></a></span><br />
                                        <span class="review-type">-</span><span class="review-minus"><a href="/users/<?=$frl['login']?>/opinions/?from=users&sort=3&f=<?=stat_collector::REFID_CATALOG?>&stamp=<?=$_SESSION['stamp']?><?=$kw_param?>"><?=zin($frl['sl'])?></a></span>
                                        <?= seo_end(); ?>
                                    </td>
                                    <td style="width:65px;"><?=view_cost2($frl['frl_cost_hour'], '', '', true, $frl['frl_cost_type_hour'])?></td>
                                    <td class="price_prj" style="width:70px;"><? if ($prof_type) { ?><?=view_cost2($frl['cost_1000'], '', '', true, $frl['cost_type'])?><? } else { ?><?=view_cost2($frl['cost_from'], 'от', '', true, $frl['cost_type'])?><? } ?></td>
                                    <td style="width:65px;"><?=view_cost2($frl['cost_month'], '', '', true, $frl['cost_type_month'])?></td>
                                </tr>
                                <tr>
                                	<td style="height:10px" colspan="8"></td>
                                </tr>

                <? 
		  if (isset($works[$frl['uid']])
                   // && $ff['show_preview'] == '1'
                    && $table == 0 // ПРО-юзеры
                    && substr($frl['tabs'], 0, 1) == 1)
                {
                      $is_preview = false;
                      $j = 0;
                ?>
<?// Работы в каталоге ?>
                                <tr class="cf-preview">
                                    <td colspan="8">
                                        <table class="cat-txt-prew" cellpadding="0" cellspacing="0" border="0" width="100%">
                                         <tr>
                                            <?php
                                            $j = 0; 
                                            foreach ($works[$frl['uid']] as $work) {
                                                if (++$j > 3) break;
                                                if(!$is_preview = ($work['pict'] || $work['prev_pict'] || $work['descr'])) continue;    
                                            ?>
                                            <?php if($is_preview) { ?>
                                                <?php if ($work['prev_type'] == 1) { ?>
                                                    <td class="b-portfolio-text-clause">
                                                       <h4 class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_ellipsis b-layout__txt_width_225"><a class="b-layout__link b-layout__link_bold" href="/users/<?=$frl['login']?>/viewproj.php?prjid=<?=$work['id']?>&f=<?=stat_collector::REFID_CATALOG?>" target="_blank" title="<?=htmlspecialchars(htmlspecialchars_decode($work['name']))?>"><?=reformat($work['name'], 17, 0, 1)?></a></h4>
																																																				   <?=viewdescr($frl['login'], reformat2($work['descr'], 42, 0, 1))?></td>
                                                <?php } else {//if?> 
                                                    <td>
                                                       <h4 class="b-layout__txt  b-layout__txt_fontsize_11 b-layout__txt_ellipsis b-layout__txt_width_225 b-layout__txt_padbot_5"><a class="b-layout__link b-layout__link_bold" href="/users/<?=$frl['login']?>/viewproj.php?prjid=<?=$work['id']?>&f=<?=stat_collector::REFID_CATALOG?>" target="_blank" title="<?=htmlspecialchars(htmlspecialchars_decode($work['name']))?>"><?=reformat($work['name'], 17, 0, 1)?></a></h4>
                                                    <a href="/users/<?=$frl['login']?>/viewproj.php?prjid=<?=$work['id']?>&f=<?=stat_collector::REFID_CATALOG?>" target="_blank" title="<?=reformat2($work['name'], null, null, 1)?>">
                                                        <?=view_preview($frl['login'], $work['prev_pict'], "upload", $align, true, true, '', 200)?>
                                                    </a>
                                                    </td>
                                                <?php }//else?>  
                                            <?php }//if?>
                                            <?php }//foreach?>
                                            <? for($i=$j+1;$i<=3;$i++) { ?>
                                                <td>&nbsp;</td>
                                            <? } //for?>
                                         </tr>
                                        </table>
                                    </td>
                                </tr>
            <? } ?>

        <?
                $frl = $frls[$iter++];
            }
            $table++;
        }
      ?>


                            </tbody>
                        </table>
                        
                    <? if($page == 1 && $cur_prof['descr_text2']) { ?>
                    <div class="b-layout__txt b-layout__txt_padbot_30 wysiwyg-style"><?=$cur_prof['descr_text2']?></div>
                    <? } ?>
                    

<? if (is_emp()) { ?>
    <div class="b-banner b-banner_pf b-banner_margbot_20">
        <?php if(!is_pro()) { ?>
        <a class="b-banner__link" href="/payed/" title="Хотите связываться с фрилансерами напрямую? Приобретите аккаунт PRO"><img class="b-banner__pf" src="/images/banners/1.png" alt="Хотите связываться с фрилансерами напрямую? Приобретите аккаунт PRO"/></a>
        <?php }?>
    </div>
<? } elseif (!get_uid()) { ?>
    <div class="b-banner b-banner_pf b-banner_margbot_20">
        <a class="b-banner__link" href="/promo/<?= sbr::NEW_TEMPLATE_SBR;?>" title="Воспользуйтесь сервисом «Безопасная Сделка»"><img class="b-banner__pf" src="/css/block/b-banner/b-banner__sbr.png" alt="«Безопасная Сделка»"/></a>
    </div>
<? }//if?>




                    <?php 
                    
                    
                    // Страницы
                    
                    /*
                    $pages = ceil( $count_frl_catalog / $frl_pp ); // альфа-костыль.
                    $sHref = "%s?".
                        ($hhf_prm ? str_replace('&','',$hhf_prm).'&' : '').
                        (($order && $order!='gnr')?"order=$order&":"").
                        (($direction)?"dir=$direction&":"").
                        (($show_all_freelancers)?"show=all&":"").                        
                        (($key_word)?'word='.str_replace('%','%%', urlencode(stripslashes($key_word))).'&':'').
                        "page=%d%s";

                    $pages = ceil($count_frl_catalog / $frl_pp);
                    echo new_paginator($page, $pages, 3, $sHref, "href");
                    */
                    
                    
                    $query_string_menu = str_replace('%','%%', $query_string_menu);
                    $pages = ceil($element->total/$element->getProperty('limit'));
                    print(new_paginator($page, $pages, 3, "%s/freelancers/?{$query_string_menu}&page=%d%s"));
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    /*$count = 100;
                    // Страницы
                    if ($pages > 1){
                        $maxpages = $pages;
                        $i = 1;
                        $sHref = "?".(($order)?"order=$order&":"").(($direction)?"dir=$direction&":"").(($prof_id)?"prof=".$prof_id."&":"")."page=";

                        if ($pages > 32){
                            $i = floor($page/10)*10 + 1;
                            if ($i >= 10 && $page%10 < 5) $i = $i - 5;
                            $maxpages = $i + 22 - floor(log($page,10)-1)*4;
                            if ($maxpages > $pages) $maxpages = $pages;
                            if ($maxpages - $i + floor(log($page,10)-1)*4 < 22 && $maxpages - 22 > 0) $i = $maxpages - 24 + floor(log($page,10)-1)*3;
                        }
                        $sBox = '<table width="100%"><tr>';
                        if ($page == 1){
                            $sBox .= '<td><span class="page-back">предыдущая</span></td>';
                        }else {
                            $sBox .= "<input type=\"hidden\" id=\"pre_navigation_link\" value=\"".($sHref.($page-1))."\">";
                            $sBox .= "<td><span class=\"page-back\"><a href=\"".($sHref.($page-1))."\">предыдущая</a></span></td>";
                        }
                        $sBox .= '<td width="90%" align="center">';
                        //в начале
                        if ($page <= 10) {
                            $sBox .= buildNavigation($page, 1, ($pages>10)?($page+4):$pages, $sHref);
                            if ($pages > 15) {
                                $sBox .= '<span style="padding-right: 5px">...</span>';
                            }
                        }
                        //в конце
                        elseif ($page >= $pages-4) {
                            $sBox .= buildNavigation($page, 1, 5, $sHref);
                            $sBox .= '<span>...</span>';
                            $sBox .= buildNavigation($page, $page-4, $pages, $sHref);
                        }else {
                            $sBox .= buildNavigation($page, 1, 5, $sHref);
                            $sBox .= '<span>...</span>';
                            $sBox .= buildNavigation($page, $page-4, $page+4, $sHref);
                            $sBox .= '<span>...</span>';
                        }

                    $sBox .= '</td>';
                    if ($page == $pages){
                      $sBox .= "<td><span class=\"page-next\">следующая</span></td>";
                    }else {
                      $sBox .= "<input type=\"hidden\" id=\"next_navigation_link\" value=\"".($sHref.($page+1))."\">";
                      $sBox .= "<td><span class=\"page-next\"><a href=\"".($sHref.($page+1))."\">следующая</a></div></td>";
                    }
                    $sBox .= '</tr>';
                    $sBox .= '</table>';
                    }
                    echo $sBox;*/
                            
                    //</div> Страницы закончились
                    ?>
                    
                    
                    
                    
                    
                    
                    <?php if (!get_uid() && $page == 1) { ?>
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padbot_30 wysiwyg-style">
                        <br />
                        <?=isset($page_seo_text)?$page_seo_text:''?>
                    </div>
                    <?php } ?>
</div>
<div class="b-layout__left b-layout__left_width_25ps b-layout__right_margleft_3ps b-layout__right_float_left">
                        <? // if ($uid) include($_SERVER['DOCUMENT_ROOT'] . '/freelancers/tpl.filter.php'); ?>
                        <? //include($_SERVER['DOCUMENT_ROOT'] . '/freelancers/tpl.catmenu.new.php'); ?>
<?php if(is_emp()||!get_uid(false)) { ?><div class="b-buttons b-buttons_padbot_20">
<div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_20">Не нашли подходящего исполнителя?</div>
<a class="b-button b-button_flat b-button_flat_green b-button_block b-button_margtop_-4" href="/public/?step=1&kind=1" onClick="_gaq.push(['_trackPageview', '/virtual/employer/button_project_create']); ga('send', 'pageview', '/virtual/employer/button_project_create'); yaCounter6051055reachGoal('proekt_dobavlen');">Опубликуйте проект</a>
</div><?php } ?>

                    <!-- Banner 240x400 -->
                        <?= printBanner240(false,false,$g_page_id);// include ($rpath."banner240.php"); ?>
                    <!-- end of Banner 240x400 -->
                    
</div>          
                                    


<style type="text/css">.b-icon__pro_team{ top:1px;} .b-icon__shield{ top:0 !important;}</style>
</div>

<a id="upper" class="b-page__up" href="#" style=" visibility:hidden;"></a>




