<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/freelancers.common.php");
//require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/fptext.common.php");

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancers_filter.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stat_collector.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/kwords.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/rating.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/project_exrates.php");

$cur_prof = $promo_profs = null;
$prfs = new professions();
$profs = $prfs->GetAllProfessions("", 0, 1);

// Сортировка категорий профессий по названию
usort($profs, function($a, $b) {
    return strcmp($a['groupname'], $b['groupname']);
});

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
    usort($promo_profs, function($a, $b) {
        return strcmp($a['profname'], $b['profname']);
    });
}

$favs = $freelancer->GetFavorites($prof_id, $uid, $filter_apply, $ff);

$xajax->printJavascript('/xajax/');
?>
<script type="text/javascript">var ___isIE5_5 = 1;</script>
<![if lt IE 5.5]>
<script type="text/javascript">var ___isIE5_5 = 0;</script>
<![endif]>
<script type="text/javascript">
    var ___WDCPREFIX = '<?= WDCPREFIX ?>';
</script>




<div class="b-freelancers-collection <?php if ($cur_prof || $prof_group_id && $prof_name) { ?>b-breadcrumbed-freelancers-collection<?php } ?>">
    <a name="frl" id="frl_anc"></a>

    <?php
    $crumbs = array();
    if ($cur_prof) {
        $crumbs[] = array("title" => "Каталог фрилансеров", "url" => "/freelancers/");
        $crumbs[] = array("title" => $cur_prof['groupname'], "url" => "/freelancers/" . $cur_prof['grouplink'] . '/');
        $crumbs[] = array("title" => $cur_prof['profname'], "url" => "");
        $pageTitle = $cur_prof['groupname'] . " / " . $cur_prof['profname'];
    } elseif ($prof_group_id && $prof_name) {
        $crumbs[] = array("title" => "Каталог фрилансеров", "url" => "/freelancers/");
        $crumbs[] = array("title" => $prof_name, "url" => "");
        $pageTitle = $prof_name;
    } else {
        //$crumbs[] = array("title"=>"Все фрилансеры", "url"=>"");
        $pageTitle = 'Каталог фрилансеров';
    }
    ?>

<?= getCrumbs($crumbs, "freelancers") ?>

    <div class="b-layout__right b-layout__right_relative">


        <h1 class="b-page__title"><?= (isset($page_h1) && $page_h1) ? $page_h1 : $pageTitle ?><span class="b-layout__txt b-layout__txt_float_right b-layout__txt_padtop_10"><?php require_once($_SERVER['DOCUMENT_ROOT'] . "/banner_promo.php"); ?></span></h1>
    </div>

<?php
// Категории (профессии) для фильтрации
include (dirname(__FILE__) . '/tpl.categories_top.php');
?>

    <div class="b-layout__right b-layout__right_relative b-layout__left_width_72ps b-layout__left_float_left">
        <?
        if ($f_country_id && $cur_prof['id']) {
            $cur_prof['descr_text'] = professions::GetProfGEOField($cur_prof['id'], 'descr_text', $f_country_id, $f_city_id);
            $cur_prof['descr_text2'] = professions::GetProfGEOField($cur_prof['id'], 'descr_text2', $f_country_id, $f_city_id);
        }
        ?>

        <?php
        // если пользователь неавторизован и на страницу попали с Директа или AdWords, то не показываем рекламный блок
        $utm_source = $_GET['utm_source'];
        if (!( (get_uid(0) < 1) && ($utm_source === "yandex" || $utm_source === "google") )):
            ?>
            <span id="catalog_promo"></span>
        <?php endif; ?>


        <div class="b-menu b-menu_line b-menu_relative b-menu_padbot_10 b-menu__cat b-menu_zindex_6">
            <div class=" cat-tab">
                <div class="b-menu b-menu_padbot_10"  data-accordion="true" data-accordion-descriptor="worktype">
                    <ul class="b-menu__list">
                        <? seo_start(); ?>
                        <li class="b-menu__item <?php if ($show_all_freelancers): ?>b-menu__item_active <?php endif; ?>" <?php if ($show_all_freelancers): ?> data-accordion-opener="true" data-accordion-descriptor="worktype"<?php endif; ?>><a class="b-menu__link" href="/freelancers/<?= ($prof_link ? $prof_link : '') ?>" title="Все фрилансеры"><span class="b-menu__b1">Все фрилансеры</span></a></li>
                        <li class="b-menu__item"><a class="b-menu__link" href="/portfolio/<?= ($prof_id ? '?prof=' . $prof_id : '') ?>" title="Работы"><span class="b-menu__b1">Работы</span></a></li>
                        <li class="b-menu__item"><a class="b-menu__link" href="/clients/<?= ($prof_id) ? '?prof=' . $prof_id : "" ?>" title="Клиенты"><span class="b-menu__b1">Клиенты</span></a></li>
                        <li class="b-menu__item b-menu__item_last b-page__ipad b-page__iphone"><a class="b-menu__link" href="/profi/"><span class="b-menu__b1">PROFI</span></a></li>
                        <li class="b-menu__item b-menu__item_padbot_null b-page__desktop"><a class="b-menu__link" href="/profi/"><span class="b-icon b-icon__profi b-icon_valign_bas" data-profi-txt="Лучшие фрилансеры сайта FL.ru. Работают на сайте более 2-х лет, прошли верификацию личности и имеют не менее 98% положительных отзывов."></span></a></li>
                        <?= seo_end(); ?>
                    </ul>
                </div>
            </div>							
        </div>

<?php include ($_SERVER['DOCUMENT_ROOT'] . "/freelancers/search/tpl.form-search.php"); ?>                        


<?php
if ($f_country_id && $cur_prof['id']) {
    $prof_descr = professions::GetProfGEOField($cur_prof['id'], 'descr', $f_country_id, $f_city_id);
}
?>
<table class="catalog-freelancers" cellpadding="0" cellspacing="0" border="0">
    
    
<? if ($frls): ?>
    <col />
    <col  />
    <col  />
    <col />
    <col  />
    <col  />
    <col  />
    
    <thead>
        <tr>
            <th colspan="" class="cf-getpro"></th>
            <th class="<? if (!isset($order) || $order == '' || $order == 'gnr') { ?> cf-sort-active cf-lc <? } else { ?> cf-sortable cf-lc <? } ?> <? if (!isset($order) || $order == '' || $order == 'gnr') { ?><? if (isset($direction) && $direction == 1) { ?>cf-sort-desc<? } else { ?>cf-sort-asc<? } ?><? } ?>">
                <a href=".?order=gnr<?= $hhf_prm ?><? if (!isset($order) || $order == '' || $order == 'gnr') { ?>&dir=<? if ($direction == 1) { ?>0<? } else { ?>1<? }
            } ?><?= ($fav_show) ? "&fs=" . $fav_show : "" ?><?= ($page > 1) ? "&page=" . $page : "" ?><?= ($keyword) ? "&keyword=" . $keyword : "" ?><?= ($user_info['login']) ? "&login=" . $user_info['login'] : "" ?><?= ($user_info['uname']) ? "&uname=" . $user_info['uname'] : "" ?><?= ($user_info['usurname']) ? "&usurname=" . $user_info['usurname'] : "" ?><?= ($user_info['site']) ? "&site=" . $user_info['site'] : "" ?><?= ($user_info['icq']) ? "&icq=" . $user_info['icq'] : "" ?><?= ($user_info['phone']) ? "&phone=" . $user_info['phone'] : "" ?><?= ($user_info['ljuser']) ? "&ljuser=" . $user_info['ljuser'] : "" ?><?= ($user_info['servises']) ? "&servises=" . $user_info['servises'] : "" ?><?= ($excl['city']) ? "&city=" . $excl['city'] : "" ?><?= ($excl['country']) ? "&country=" . $excl['country'] : "" ?>">Рейтинг</a></th>

                        <th class="<? if (isset($order) && $order == 'sbr') { ?> cf-sort-active <? } else { ?> cf-sortable <? } ?> <? if (isset($order) && $order == 'sbr') { ?><? if (isset($direction) && $direction == 1) { ?>cf-sort-desc<? } else { ?>cf-sort-asc<? } ?><? } ?>">

                            <a href=".?order=sbr<?= $hhf_prm ?><? if (isset($order) && $order == 'sbr') { ?>&dir=<? if ($direction == 1) { ?>0<? } else { ?>1<? }
            } ?><?= ($fav_show) ? "&fs=" . $fav_show : "" ?><?= ($page > 1) ? "&page=" . $page : "" ?><?= ($keyword) ? "&keyword=" . $keyword : "" ?><?= ($user_info['login']) ? "&login=" . $user_info['login'] : "" ?><?= ($user_info['uname']) ? "&uname=" . $user_info['uname'] : "" ?><?= ($user_info['usurname']) ? "&usurname=" . $user_info['usurname'] : "" ?><?= ($user_info['site']) ? "&site=" . $user_info['site'] : "" ?><?= ($user_info['icq']) ? "&icq=" . $user_info['icq'] : "" ?><?= ($user_info['phone']) ? "&phone=" . $user_info['phone'] : "" ?><?= ($user_info['ljuser']) ? "&ljuser=" . $user_info['ljuser'] : "" ?><?= ($user_info['servises']) ? "&servises=" . $user_info['servises'] : "" ?><?= ($excl['city']) ? "&city=" . $excl['city'] : "" ?><?= ($excl['country']) ? "&country=" . $excl['country'] : "" ?>">Рекомен-<br />дации</a></th>    

                        <th class="<? if (isset($order) && $order == 'ops') { ?> cf-sort-active <? } else { ?> cf-sortable <? } ?> <? if (isset($order) && $order == 'ops') { ?><? if (isset($direction) && $direction == 1) { ?>cf-sort-desc<? } else { ?>cf-sort-asc<? } ?><? } ?>">

                            <a href=".?order=ops<?= $hhf_prm ?><? if (isset($order) && $order == 'ops') { ?>&dir=<? if ($direction == 1) { ?>0<? } else { ?>1<? }
            } ?><?= ($fav_show) ? "&fs=" . $fav_show : "" ?><?= ($page > 1) ? "&page=" . $page : "" ?><?= ($keyword) ? "&keyword=" . $keyword : "" ?><?= ($user_info['login']) ? "&login=" . $user_info['login'] : "" ?><?= ($user_info['uname']) ? "&uname=" . $user_info['uname'] : "" ?><?= ($user_info['usurname']) ? "&usurname=" . $user_info['usurname'] : "" ?><?= ($user_info['site']) ? "&site=" . $user_info['site'] : "" ?><?= ($user_info['icq']) ? "&icq=" . $user_info['icq'] : "" ?><?= ($user_info['phone']) ? "&phone=" . $user_info['phone'] : "" ?><?= ($user_info['ljuser']) ? "&ljuser=" . $user_info['ljuser'] : "" ?><?= ($user_info['servises']) ? "&servises=" . $user_info['servises'] : "" ?><?= ($excl['city']) ? "&city=" . $excl['city'] : "" ?><?= ($excl['country']) ? "&country=" . $excl['country'] : "" ?>">Мнения</a></th>
                        <th class="<? if (isset($order) && $order == 'pph') { ?> cf-sort-active <? } else { ?> cf-sortable <? } ?> <? if (isset($order) && $order == 'pph') { ?><? if (isset($direction) && $direction == 1) { ?>cf-sort-desc<? } else { ?>cf-sort-asc<? } ?><? } ?>">

                            <a href=".?order=pph<?= $hhf_prm ?><? if (isset($order) && $order == 'pph') { ?>&dir=<? if ($direction == 1) { ?>0<? } else { ?>1<? }
            } else { ?>&dir=1<? } ?><?= ($fav_show) ? "&fs=" . $fav_show : "" ?><?= ($page > 1) ? "&page=" . $page : "" ?><?= ($keyword) ? "&keyword=" . $keyword : "" ?><?= ($user_info['login']) ? "&login=" . $user_info['login'] : "" ?><?= ($user_info['uname']) ? "&uname=" . $user_info['uname'] : "" ?><?= ($user_info['usurname']) ? "&usurname=" . $user_info['usurname'] : "" ?><?= ($user_info['site']) ? "&site=" . $user_info['site'] : "" ?><?= ($user_info['icq']) ? "&icq=" . $user_info['icq'] : "" ?><?= ($user_info['phone']) ? "&phone=" . $user_info['phone'] : "" ?><?= ($user_info['ljuser']) ? "&ljuser=" . $user_info['ljuser'] : "" ?><?= ($user_info['servises']) ? "&servises=" . $user_info['servises'] : "" ?><?= ($excl['city']) ? "&city=" . $excl['city'] : "" ?><?= ($excl['country']) ? "&country=" . $excl['country'] : "" ?>">Цена <br />за час</a></th>

    <? if ($prof_type) { ?>

                            <th class="<? if (isset($order) && $order == 'pp1') { ?> cf-sort-active <? } else { ?> cf-sortable <? } ?> <? if (isset($order) && $order == 'pp1') { ?><? if (isset($direction) && $direction == 1) { ?>cf-sort-desc<? } else { ?>cf-sort-asc<? } ?><? } ?>">

                                <a href=".?order=pp1<?= $hhf_prm ?><? if (isset($order) && $order == 'pp1') { ?>&dir=<? if ($direction == 1) { ?>0<? } else { ?>1<? }
                    } else { ?>&dir=1<? } ?><?= ($fav_show) ? "&fs=" . $fav_show : "" ?><?= ($page > 1) ? "&page=" . $page : "" ?><?= ($keyword) ? "&keyword=" . $keyword : "" ?><?= ($user_info['login']) ? "&login=" . $user_info['login'] : "" ?><?= ($user_info['uname']) ? "&uname=" . $user_info['uname'] : "" ?><?= ($user_info['usurname']) ? "&usurname=" . $user_info['usurname'] : "" ?><?= ($user_info['site']) ? "&site=" . $user_info['site'] : "" ?><?= ($user_info['icq']) ? "&icq=" . $user_info['icq'] : "" ?><?= ($user_info['phone']) ? "&phone=" . $user_info['phone'] : "" ?><?= ($user_info['ljuser']) ? "&ljuser=" . $user_info['ljuser'] : "" ?><?= ($user_info['servises']) ? "&servises=" . $user_info['servises'] : "" ?><?= ($excl['city']) ? "&city=" . $excl['city'] : "" ?><?= ($excl['country']) ? "&country=" . $excl['country'] : "" ?>">Цена за<br />1000 зн.</a></th>

                        <? } else { ?>

                            <th class="<? if (isset($order) && $order == 'ppp') { ?> cf-sort-active <? } else { ?> cf-sortable <? } ?>  <? if (isset($order) && $order == 'ppp') { ?><? if (isset($direction) && $direction == 1) { ?>cf-sort-desc<? } else { ?>cf-sort-asc<? } ?><? } ?>">

                                <a href=".?order=ppp<?= $hhf_prm ?><? if (isset($order) && $order == 'ppp') { ?>&dir=<? if ($direction == 1) { ?>0<? } else { ?>1<? }
                            } else { ?>&dir=1<? } ?><?= ($fav_show) ? "&fs=" . $fav_show : "" ?><?= ($page > 1) ? "&page=" . $page : "" ?><?= ($keyword) ? "&keyword=" . $keyword : "" ?><?= ($user_info['login']) ? "&login=" . $user_info['login'] : "" ?><?= ($user_info['uname']) ? "&uname=" . $user_info['uname'] : "" ?><?= ($user_info['usurname']) ? "&usurname=" . $user_info['usurname'] : "" ?><?= ($user_info['site']) ? "&site=" . $user_info['site'] : "" ?><?= ($user_info['icq']) ? "&icq=" . $user_info['icq'] : "" ?><?= ($user_info['phone']) ? "&phone=" . $user_info['phone'] : "" ?><?= ($user_info['ljuser']) ? "&ljuser=" . $user_info['ljuser'] : "" ?><?= ($user_info['servises']) ? "&servises=" . $user_info['servises'] : "" ?><?= ($excl['city']) ? "&city=" . $excl['city'] : "" ?><?= ($excl['country']) ? "&country=" . $excl['country'] : "" ?>">Цена <br />за проект</a></th>

                        <? } ?>

                        <th class="<? if (isset($order) && $order == 'ppm') { ?> cf-sort-active cf-rc <? } else { ?> cf-sortable cf-rc <? } ?> <? if (isset($order) && $order == 'ppm') { ?><? if (isset($direction) && $direction == 1) { ?>cf-sort-desc<? } else { ?>cf-sort-asc<? } ?><? } ?>">

                            <a href=".?order=ppm<?= $hhf_prm ?><? if (isset($order) && $order == 'ppm') { ?>&dir=<? if ($direction == 1) { ?>0<? } else { ?>1<? }
                    } else { ?>&dir=1<? } ?><?= ($fav_show) ? "&fs=" . $fav_show : "" ?><?= ($page > 1) ? "&page=" . $page : "" ?><?= ($keyword) ? "&keyword=" . $keyword : "" ?><?= ($user_info['login']) ? "&login=" . $user_info['login'] : "" ?><?= ($user_info['uname']) ? "&uname=" . $user_info['uname'] : "" ?><?= ($user_info['usurname']) ? "&usurname=" . $user_info['usurname'] : "" ?><?= ($user_info['site']) ? "&site=" . $user_info['site'] : "" ?><?= ($user_info['icq']) ? "&icq=" . $user_info['icq'] : "" ?><?= ($user_info['phone']) ? "&phone=" . $user_info['phone'] : "" ?><?= ($user_info['ljuser']) ? "&ljuser=" . $user_info['ljuser'] : "" ?><?= ($user_info['servises']) ? "&servises=" . $user_info['servises'] : "" ?><?= ($excl['city']) ? "&city=" . $excl['city'] : "" ?><?= ($excl['country']) ? "&country=" . $excl['country'] : "" ?>">Цена <br />в месяц</a></th>
                    </tr>
                </thead>
<? else: ?>
                <thead>
                    <tr>
                        <th class="cf-getpro"><? if ($_SESSION['login'] && !is_pro()): ?><a href="/payed/">Получить аккаунт</a> <a href="/payed/"><?= is_emp() ? view_pro_emp() : view_pro(false, false, false) ?></a><? endif; ?></th>      

                    </tr> 
                </thead>
<? endif; ?>


<?php if ($is_binded_hide): ?>
                <tr><td colspan="7" style="height:30px"></td></tr>  
                <tbody> 
                    <tr class="is-pro">
                        <td colspan="7" class="">
                            <div class="cat-add b-layout__txt b-layout__txt_center b-layout__txt_bold b-layout_pad_5">
                                <div class="b-icon b-icon__cat_add b-icon_absolute b-icon_left_-30"></div>
                                Ваш профиль временно скрыт в этом разделе &mdash; для его восстановления 
                                <a class="b-layout__link b-layout__link_bold b-layout__link_no-decorat" href="/users/<?= $_SESSION['login'] ?>/setup/specaddsetup/">измените специализацию</a> 
                                <?php if (!is_pro()): ?>
                                    или <a class="b-layout__link b-layout__link_bold b-layout__link_no-decorat" href="/payed/">купите аккаунт PRO</a>
    <?php endif; ?>
                            </div>
                        </td>
                    </tr> 
                </tbody>  
<?php endif; ?>



<?php if ($allow_frl_bind): ?>
                <tr><td colspan="7" style="height:30px"></td></tr>  
                <tbody> 
                    <tr class="is-pro">
                        <td colspan="7" class="">
                            <div class="cat-add b-layout__txt b-layout__txt_center b-layout__txt_bold b-layout_pad_5">
                                <div class="b-icon b-icon__cat_add b-icon_absolute b-icon_left_-30"></div>
                                <a class="b-layout__link b-layout__link_bold b-layout__link_no-decorat" onClick="$('quick_payment_frlbind').toggleClass('b-shadow_hide');
                                        return false;" href="#">
                                    Закрепите профиль сверху на неделю за <?= view_cost_format(quickPaymentPopupFrlbind::getInstance()->getPrice(), false); ?> руб.
                                </a> 
                                &mdash; будьте первым и самым заметным для Заказчиков!
                            </div>
                        </td>
                    </tr> 

                </tbody>  
            <?php endif; ?>

<?php if ($binded_to && !$is_binded_hide): ?>
                <tr><td colspan="7" style="height:30px"></td></tr>  
                <tbody> 
                    <tr class="is-pro">
                        <td colspan="7" class="">
                            <div class="cat-add b-layout__txt b-layout__txt_center b-layout__txt_bold b-layout_pad_5">
                                <div class="b-icon b-icon__cat_add b-icon_absolute b-icon_left_-30"></div>
                                Ваш профиль закреплен до <?= dateFormat('d.m.Y H:i', $binded_to) ?> &mdash;
                                <a class="b-layout__link b-layout__link_bold b-layout__link_no-decorat" onClick="$('quick_payment_frlbind').toggleClass('b-shadow_hide');
                                        return false;" href="#">
                                    продлите срок закрепления
                                </a> 
    <?php if (!$is_bind_first): ?>
                                    или 
                                    <a class="b-layout__link b-layout__link_bold b-layout__link_no-decorat" onClick="$('quick_payment_frlbindup').toggleClass('b-shadow_hide');
                                            return false;" href="#">
                                        поднимите профиль на первое место
                                    </a> 
    <?php endif; ?>
                            </div>
                        </td>
                    </tr> 

                </tbody>  
<?php endif; ?>

            <tr><td colspan="7" style="height:30px"></td></tr>                                                                  



            <tbody>  
                <? if (!$frls): ?>
                    <tr>
                        <td ><?= $filter_apply ? "Попробуйте изменить критерии поиска" : "Фрилансеров не найдено" ?> </td>
                    </tr>  
                <? endif; ?>


                <? foreach ($frls as $frl): ?>

                    <tr class="cf-line <?php if ($frl['is_pro'] == 't'): ?>is-pro<?php endif; ?>">
                        <?php /*
                          <td class="cf-fav">
                          <div>
                          <a href="javascript:void(1)"><? if (($frl['uid'] != $uid) && ($uid > 0)) { ?><img id="favstar_<?=$frl['uid']?>" src="/images/<? if ($table == 0) { ?>ico_star_<? if (in_array($frl['uid'], $favs) || $fav_show) { ?>yellow<? } else { ?>empty<? } ?>_green<? } else { ?>ico_star_<? if (in_array($frl['uid'], $favs) || $fav_show) { ?>yellow<? } else { ?>empty<? } ?>_grey<? } ?>.gif" alt="" width="10" height="11" border="0" style="cursor:pointer" onClick="xajax_AddFav(<?=$frl['uid']?>, <?=$prof_id?>, '<?=$frl['is_pro']?>')"><? } else { ?><img src="/images/1.gif" alt="" width="10" height="11" border="0"><? } ?></a>
                          </div>
                          </td>
                         */ ?>
                        <td class="cf-user">
                            <?php if ($frl['is_binded']): ?>
                                <div class="b-icon b-icon__cat_pin b-icon_absolute b-icon_left_-30"></div>
                                <?php endif; ?>
                                <?= view_avatar($frl['login'], $frl['photo'], 1, 0, "cf-avatar") ?>
                            <div class="cf-user-in">
                                <?
                                $frl['role'] = $GLOBALS['frlmask'];
                                $kw_param = ($kword_stat) ? '&kw=' . urlencode(stripslashes($kword_stat)) : '';
                                print(view_user2($frl, '', 'freelancer-name', '', '?f=' . stat_collector::REFID_CATALOG . '&stamp=' . $_SESSION['stamp'] . $kw_param . '#' . $anchor, TRUE, TRUE, "yaCounter6051055.reachGoal('frl_cat_ref');"));
                                ?>
                                <span class="cf-spec">
                                    <?= ((!$section && $prof_id <= 0) || $frl['its_his_main_spec'] == 't' ? 'Специализация' : 'Дополнительная специализация') ?>: <?= $prof_id > 0 ? $prof_name : $frl['profname'] ?>
                                    <?php
                                    $frl_info_for_reg = unserialize($frl['info_for_reg']);
                                    $str_location = '';
                                    if ($region_filter_country_id) {
                                        if ($frl['country']) {
                                            if (!($frl_info_for_reg['country'] && !get_uid(false))) {
                                                $str_location = $frl['str_country'];
                                            }
                                        }
                                        if ($frl['city']) {
                                            if (!($frl_info_for_reg['city'] && !get_uid(false))) {
                                                $str_location .= ($str_location ? " / " . $frl['str_city'] : $frl['str_city']);
                                            }
                                        }
                                    }
                                    if ($str_location) {
                                        echo "<br>Регион: {$str_location}";
                                    }
                                    ?>
                                </span>



    <? if ($frl['status_type'] != -1) { ?><?= freelancer::viewStatus($frl['status_type'], true) ?> <? } ?> 
                            </div>
                        </td>
                        <td><?= rating::round($frl['t_rating']) ?></td>
                        <td style="width:65px;">
    <? seo_start(); ?>

                            <span class="review-type">+</span><span class="review-plus"><a href="/users/<?= $frl['login'] ?>/opinions/?from=sbr&sort=1&f=<?= stat_collector::REFID_CATALOG ?>&stamp=<?= $_SESSION['stamp'] ?><?= $kw_param ?>"><?= zin($frl['total_opi_plus']) ?></a></span><br /> 
                            <span class="review-type"></span><span class="review-neitral"><a href="/users/<?= $frl['login'] ?>/opinions/?from=sbr&sort=2&f=<?= stat_collector::REFID_CATALOG ?>&stamp=<?= $_SESSION['stamp'] ?><?= $kw_param ?>"><?= zin($frl['total_opi_null']) ?></a></span><br /> 
                            <span class="review-type">-</span><span class="review-minus"><a href="/users/<?= $frl['login'] ?>/opinions/?from=sbr&sort=3&f=<?= stat_collector::REFID_CATALOG ?>&stamp=<?= $_SESSION['stamp'] ?><?= $kw_param ?>"><?= zin($frl['total_opi_minus']) ?></a></span>                                         
                            <?= seo_end(); ?>
                        </td>
                        <td style="width:65px;">
                            <? seo_start(); ?>
                            <span class="review-type">+</span><span class="review-plus"><a href="/users/<?= $frl['login'] ?>/opinions/?from=users&sort=1&f=<?= stat_collector::REFID_CATALOG ?>&stamp=<?= $_SESSION['stamp'] ?><?= $kw_param ?>"><?= zin($frl['sg']) ?></a></span><br />
                            <span class="review-type"></span><span class="review-neitral"><a href="/users/<?= $frl['login'] ?>/opinions/?from=users&sort=2&f=<?= stat_collector::REFID_CATALOG ?>&stamp=<?= $_SESSION['stamp'] ?><?= $kw_param ?>"><?= zin($frl['se']) ?></a></span><br />
                            <span class="review-type">-</span><span class="review-minus"><a href="/users/<?= $frl['login'] ?>/opinions/?from=users&sort=3&f=<?= stat_collector::REFID_CATALOG ?>&stamp=<?= $_SESSION['stamp'] ?><?= $kw_param ?>"><?= zin($frl['sl']) ?></a></span>
    <?= seo_end(); ?>
                        </td>
                        <td style="width:65px;"><?= view_cost2($frl['frl_cost_hour'], '', '', true, $frl['frl_cost_type_hour']) ?></td>
                        <td class="price_prj" style="width:70px;"><? if ($prof_type) { ?><?= view_cost2($frl['cost_1000'], '', '', true, $frl['cost_type']) ?><? } else { ?><?= view_cost2($frl['cost_from'], 'от', '', true, $frl['cost_type']) ?><? } ?></td>
                        <td style="width:65px;"><?= view_cost2($frl['cost_month'], '', '', true, $frl['cost_type_month']) ?></td>
                    </tr>
                    <tr>
                        <td style="height:10px" colspan="7"></td>
                    </tr>

                    <?
                    if (isset($works[$frl['uid']])
                            // && $ff['show_preview'] == '1'
                            && $table == 0 // ПРО-юзеры
                            && substr($frl['tabs'], 0, 1) == 1) {
                        $is_preview = false;
                        $j = 0;
                        ?>
        <? // Работы в каталоге  ?>
                        <tr class="cf-preview">
                            <td colspan="7">
                                <table class="cat-txt-prew" cellpadding="0" cellspacing="0" border="0" width="100%">
                                    <tr>
                                        <?php
                                        $j = 0;
                                        foreach ($works[$frl['uid']] as $work) {
                                            if (!$is_preview = ($work['pict'] || $work['prev_pict'] || $work['descr']))
                                                continue;
                                            if (++$j > 3)
                                                break;
                                            ?>
                                                <?php if ($is_preview) { ?>
                                                <?php if ($work['prev_type'] == 1) { ?>
                                                    <td class="b-portfolio-text-clause">
                                                        <h4 class="b-layout__txt b-layout__txt_center b-layout__txt_fontsize_11 b-layout__txt_ellipsis b-layout__txt_width_225"><a class="b-layout__link b-layout__link_bold" href="/users/<?= $frl['login'] ?>/viewproj.php?prjid=<?= $work['id'] ?>&f=<?= stat_collector::REFID_CATALOG ?>" target="_blank" title="<?=htmlspecialchars(htmlspecialchars_decode($work['name']))?>"><?= reformat($work['name'], 17, 0, 1) ?></a></h4>
                                                            <?= viewdescr($frl['login'], reformat2($work['descr'], 42, 0, 1)) ?></td>
                                                        <?php } else {//if?> 
                                                    <td itemscope itemtype="http://schema.org/ImageObject">
                                                        <h4 class="b-layout__txt b-layout__txt_center  b-layout__txt_fontsize_11 b-layout__txt_ellipsis b-layout__txt_width_225 b-layout_center b-layout__txt_padbot_5"><a class="b-layout__link b-layout__link_bold" href="/users/<?= $frl['login'] ?>/viewproj.php?prjid=<?= $work['id'] ?>&f=<?= stat_collector::REFID_CATALOG ?>" target="_blank" title="<?=htmlspecialchars(htmlspecialchars_decode($work['name']))?>" itemprop="name"><?= reformat($work['name'], 17, 0, 1) ?></a></h4>
                                                        <a href="/users/<?= $frl['login'] ?>/viewproj.php?prjid=<?= $work['id'] ?>&f=<?= stat_collector::REFID_CATALOG ?>" target="_blank" title="<?= reformat2($work['name'], null, null, 1) ?>">
                                                    <?= view_preview($frl['login'], $work['prev_pict'], "upload", $align, true, true, '', 200) ?>
                                                        </a>
                                                        <span class="b-layout_hide" itemprop="description"><?= SeoTags::getInstance()->getImageDescription() ?></span>
                                                    </td>
                                                <?php }//else ?>  
                                            <?php }//if?>
                                        <?php } ?>
        <? for ($i = $j + 1; $i <= 3; $i++) { ?>
                                            <td>&nbsp;</td>
                        <? } //for ?>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    <? } ?>

                    <?
                 endforeach;
                ?>


            </tbody>
        </table>

<? if ($page == 1 && $cur_prof['descr_text2']) { ?>
            <div class="b-layout__txt b-layout__txt_padbot_30 wysiwyg-style"><?= $cur_prof['descr_text2'] ?></div>
        <? } ?>


            <? if (is_emp()) { ?>
            <div class="b-banner b-banner_pf b-banner_margbot_20">
            <?php if (!is_pro()) { ?>
                    <a class="b-banner__link" href="/payed/" title="Хотите связываться с фрилансерами напрямую? Приобретите аккаунт PRO"><img class="b-banner__pf" src="/images/banners/1.png" alt="Хотите связываться с фрилансерами напрямую? Приобретите аккаунт PRO"/></a>
    <?php } ?>
            </div>
        <? } elseif (!get_uid()) { ?>
            <div class="b-banner b-banner_pf b-banner_margbot_20">
                <a class="b-banner__link" href="/promo/<?= sbr::NEW_TEMPLATE_SBR; ?>" title="Воспользуйтесь сервисом «Безопасная Сделка»"><img class="b-banner__pf" src="/css/block/b-banner/b-banner__sbr.png" alt="«Безопасная Сделка»"/></a>
            </div>
<? }//if ?>




        <?
        // Страницы
        echo new_paginator($page, $pages, 3, $sHref, "href");










        /* $count = 100;
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
          echo $sBox; */

        //</div> Страницы закончились
        ?>



        <div class="b-layout b-layout_clear_both b-layout_padtop_30">
            <h2 class="b-layout__txt b-layout__txt_color_666 b-layout__txt_bold b-layout__txt_padbot_10">
                <?= SeoTags::getInstance()->getFooterHead() ?>
            </h2>
            <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_color_666">
<?= SeoTags::getInstance()->getFooterText() ?>
            </div>
        </div>


            <?php if (!get_uid() && $page == 1) { ?>
            <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padbot_30 wysiwyg-style">
                <br />
    <?= isset($page_seo_text) ? $page_seo_text : '' ?>
            </div>
        <?php } ?>
    </div>
    <div class="b-layout__left b-layout__left_width_25ps b-layout__right_margleft_3ps b-layout__right_float_left">
        <? // if ($uid) include($_SERVER['DOCUMENT_ROOT'] . '/freelancers/tpl.filter.php'); ?>
<? /* include($_SERVER['DOCUMENT_ROOT'] . '/freelancers/tpl.catmenu.new.php'); */ ?>

<?php if (is_emp() || !get_uid(false)) { ?><div class="b-buttons b-buttons_padbot_20 b-buttons_padtop_15">
                <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_15">Не нашли подходящего исполнителя?</div>
                <a class="b-button b-button_flat b-button_flat_green b-button_block" href="/public/?step=1&kind=1" onClick="_gaq.push(['_trackPageview', '/virtual/employer/button_project_create']);
                        ga('send', 'pageview', '/virtual/employer/button_project_create');
                        yaCounter6051055reachGoal('proekt_dobavlen');">Опубликуйте проект</a>
            </div><?php } ?>

        <div class="b-pay-place b-page__desktop" id="pay_place_left" pl="r"></div>

        <!-- Banner 240x400 -->
                <?= printBanner240(false, false, $g_page_id); // include ($rpath."banner240.php"); ?>
        <!-- end of Banner 240x400 -->
                <?php if (!get_uid(false)) { ?>        
            <div id="seo_block" class="b-layout b-layout_padtop_20">
                <h2 class="b-layout__txt b-layout__txt_color_666 b-layout__txt_bold b-layout__txt_padbot_10">
            <?= SeoTags::getInstance()->getSideHead() ?>
                </h2>
                <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_color_666">
    <?= SeoTags::getInstance()->getSideText() ?>
                </div>
            </div>    
<?php } ?>        
    </div>             





<?php if (false): ?>

        <div id="popup_region_filter" class="b-shadow b-shadow_zindex_3 b-shadow_center b-shadow_width_350 b-shadow_hide">
            <div class="b-shadow__right">
                <div class="b-shadow__left">
                    <div class="b-shadow__top">
                        <div class="b-shadow__bottom">
                            <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_20">
                                <form id="popup_region_filter_frm" action="/freelancers/<?= ($prof_link ? "{$prof_link}/" : ($prof_id ? '?prof=' . $prof_id : '')) ?>" method="POST">
                                    <h2 class="b-shadow__title">Выберите регион</h2>
                                    <table class="b-layout__table b-layout__table_width_full">
                                        <tr class="b-layout__tr">
                                            <td class="b-layout__one b-layout__one_padbot_10 b-layout__one_width_60"><label class="b-layout__txt b-layout__txt_padtop_3" for="b-select__country">Страна</label></td>
                                            <td class="b-layout__one b-layout__one_padbot_10">
                                                <div class="b-select">
                                                    <select id="b-select__country" name="country" class="b-select__select" onChange="RegionFilterCityUpd(this.value)">
                                                        <option value="0">Все страны</option>
    <? foreach ($region_filter_countries as $countid => $country): ?>
                                                            <option value="<?= translit(strtolower($country)) ?>"<? if ($countid == $region_filter_country_id) echo(" selected='selected'") ?>><?= $country ?></option>
    <? endforeach; ?>
                                                    </select>
                                                </div>
                                                <input type='hidden' name='action' value='region_filter'>
                                            </td>
                                        </tr>
                                        <tr class="b-layout__tr">
                                            <td class="b-layout__one b-layout__one_padbot_10 b-layout__one_width_60"><label class="b-layout__txt b-layout__txt_padtop_3" for="b-select__city">Город</label></td>
                                            <td class="b-layout__one b-layout__one_padbot_10">
                                                <div class="b-select">
                                                    <select id="b-select__city" name="city" class="b-select__select">
                                                        <option value="0">Все города</option>
    <? if (sizeof($region_filter_cities)) foreach ($region_filter_cities as $cityid => $city): ?>
                                                                <option value="<?= translit(strtolower($city)) ?>"<? if ($cityid == $region_filter_city_id) echo(" selected='selected'") ?>><?= $city ?></option>
        <? endforeach; ?>
                                                    </select>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                    <div class="b-buttons b-buttons_padleft_57">
    <? $req = $prof_link ? "/freelancers/{$prof_link}/" : "/freelancers/" . ($prof_id ? "?prof=$prof_id" : ''); ?>
                                        <a class="b-button b-button_rectangle_color_transparent" 
                                           onclick="debug_redirectSubdomain('<?= $req ?>', '<?= $GLOBALS['host'] ?>',
                                                           $('b-select__city').get('value') != 0 ? $('b-select__city').get('value') : $('b-select__country').get('value'),
                                                           '<?= HTTP_PREFIX; ?>');
                                                   return false;" href="javascript:void(0)">
                                            <span class="b-button__b1">
                                                <span class="b-button__b2">
                                                    <span class="b-button__txt">Применить</span>
                                                </span>
                                            </span>
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <span class="b-shadow__icon b-shadow__icon_close"></span>
        </div>

    <?php endif; ?>

<?php
if ($allow_frl_bind || $binded_to) {
    echo quickPaymentPopupFrlbind::getInstance()->render();
}
?>
<?php
if ($binded_to && !$is_bind_first) {
    echo quickPaymentPopupFrlbindup::getInstance()->render();
}
?>


    <style type="text/css">.b-icon__pro_team{ top:1px;} .b-icon__shield{ top:0 !important;}</style>
</div>

<a id="upper" class="b-page__up" href="#" style=" visibility:hidden;"></a>




