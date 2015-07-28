<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/clients.php");
$cl = new clients();
$clients = $cl->getClients('RANDOM()', 12);
if (!count($clients)) $clients = array();

$searchLinkFlag = 0;
if (get_uid(false)) {
    if ( $_SESSION["role"][0] != '1') {
        $searchLinkFlag = 1;
    }
}
?>
<div class="b-promo b-promo_main" style="margin-top:<?= $extraMarginTop ?>px">
<a name="b-promo_clients"></a>
	<div id="b-promo__main-inner" class="b-promo__main-inner<?= !$state ? ' b-promo_height_80 b-promo_overflow_hidden' : '' ?>" <?= !$state ? ' style="height:80px"' : '' ?>>
		<div id="promo-minimize" class="b-promo__slide b-promo__slide_up b-promo__slide_padbot_20<?= !$state ? ' b-promo__slide_hide' : '' ?>"><a id="main_link_promo_tgl" class="b-promo__link b-promo__link_float_right" href="javascript:void(0)" onclick="promoSaveCookie('0');">свернуть&#160;<span class="b-promo__slide-arrow b-promo__slide-arrow_top"></span></a></div>
		<div id="promo-maximize" class="b-promo__slide b-promo__slide_down b-promo__slide_padbot_20<?= $state ? ' b-promo__slide_hide' : '' ?>"><a class="b-promo__link b-promo__link_float_right" href="javascript:void(0)" onclick="promoSaveCookie('1');">развернуть&#160;<span class="b-promo__slide-arrow b-promo__slide-arrow_bot"></span></a></div>
        <a class="b-button b-button_big_green"  href="<?= $searchLinkFlag ? "/search/?type=users" : "/wizard/registration/employer"?>" title="поиск фрилансера">»щу фрилансера!</a>
        <a class="b-button b-button_big_yellow"  href="<?= get_uid(false) ? "/search/?type=projects" : "/wizard/registration/free-lancer"?>" title="поиск работы">»щу работу</a>
        <div class="b-layout b-layout_padtop_30 b-promo__main-block<?= !$state ? ' b-layout_hide' : '' ?>" id="mainPromo">
			<table class="b-layout__table b-layout__table_width_full" border="0" cellpadding="0" cellspacing="0">
				<tr class="b-layout__tr">
					<td class="b-layout__gap">&#160;</td>
					<td class="b-layout__left">
						<h2 class="b-layout__title b-layout__title_width_350 b-layout__title_center"><a class="b-promo__link b-promo__link_inline-block" href="/clients/"> лиенты фрилансеров</a></h2>
						<ul class="b-promo__free b-promo__free_width_270">
                            <?php foreach ($clients as $client) { ?>
                                <li class="b-promo__free-item b-promo__free-item_float_left">
                                    <a class="b-promo__link" href="<?= $client['link_client'] ?>" target="_blank">
                                        <img class="b-promo__photo-free b-promo__photo-free_marg_5" src="<?= WDCPREFIX ?>/clients/<?= $client['logo'] ?>" alt="<?= $client['client_name'] ?>" title="<?= $client['client_name'] ?>" width="80" height="57" />
                                    </a>
                                </li>
                            <?php }//foreach?>
						</ul>
					</td>
					<td class="b-layout__middle b-layout__middle_width_300 b-layout__middle_align-center">
						<div class="b-promo__txt b-promo__txt_fontsize_40 b-promo__txt_bold b-promo__txt_lineheight_1"><?= $pUStat['u']['count'] ?></div>
						<div class="b-promo__txt b-promo__txt_padbot_30"><?= $pUStat['u']['phrase'] ?></div>
						<div class="b-promo__txt b-promo__txt_fontsize_40 b-promo__txt_bold b-promo__txt_lineheight_1"><?= $pUStat['p']['count'] ?></div>
						<div class="b-promo__txt b-promo__txt_padbot_30"><?= $pUStat['p']['phrase'] ?></div>
						<div class="b-promo__txt b-promo__txt_fontsize_40 b-promo__txt_bold b-promo__txt_lineheight_1 b-promo__txt_relative b-promo__valuta"><?= number_format($pUStat['s']['count'], 0, ",", " "); ?></div>
						<div class="b-promo__txt b-promo__txt_padbot_30"><?= $pUStat['s']['phrase'] ?></div>
					</td>
					<td class="b-layout__right">&#160;</td>
					<td class="b-layout__gap">&#160;</td>
				</tr>
			</table>
		</div>
		<? if (get_uid(false)) { ?><div id="promo-close-forever" class="b-promo__slide b-promo__slide_close b-promo__slide_bot_-15<?= $state ? ' b-promo__slide_hide' : '' ?>"><a class="b-promo__link b-promo__link_float_right" href="javascript:void(0)" onclick="mainPromoClose();">закрыть совсем&#160;<span class="b-promo__slide-arrow b-promo__slide-arrow_close"></span></a></div><? } ?>
	</div>
</div><!-- b-promo_main -->