<?php 
if(!$not_load_info) {
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/pay_place.php");
    $payPlace = new pay_place($catalog);
    $pp_uids = $payPlace->getUserPlace();

    if(is_array($pp_uids)) {
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
            $usrs = new users();
            $pp_result = $usrs->getUsers("uid IN (".implode(",", array_values($pp_uids)).")");

            foreach($pp_result as $k=>$v) $toppay_usr[$v['uid']] = $v;

            $pp_h = $payPlace->getAllInfo($pp_uids);
    }
}

$uid = get_uid(false);
$is_show_tizer = false; //$uid && !is_emp();

?>
<div id="pay_place_carusel" class="b-carusel b-carusel_width_full" style="top:<?= $caruselTop ?>px">
    <div class="b-carusel__body">
        <div class="b-carusel__inner">
            <ul id="top-payed" class="b-carusel__list <?php if($is_show_tizer): ?>b-carusel__list_width_3196<?php endif; ?>">
                <? include ABS_PATH . "/templates/pay_place/tpl.show.php"; ?>
            </ul>
        </div>
        <span id="carusel_shadow_right" class="b-carusel__shadow-right"></span>
        <span id="carusel_shadow_left" class="b-carusel__shadow-left" style="display:none"></span>
        <?php if($is_show_tizer): ?>
        <span class="b-carusel__add"><a id="carusel_tizer_switcher" class="b-carusel__link b-carusel__link_hide" href="javascript: void(0);">Добавить объявление</a></span>
        <?php endif; ?>
    </div>
    <span class="b-carusel__prev b-carusel__prev_disabled b-carusel-ubtn"></span>
    <span class="b-carusel__next b-carusel-ubtn"></span>
</div><!--b-carusel-->