<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/clients.php");
$cl = new clients();
$clients = $cl->getClients('RANDOM()', 5);
if (!count($clients)) $clients = array();

$searchLinkFlag = 0;
if (get_uid(false)) {
    if ( $_SESSION["role"][0] != '1') {
        $searchLinkFlag = 1;
    }
}
?>
<div style="position:absolute;top:260px; width:100%;margin-top:<?= $extraMarginTop ?>px">
<div style="margin:0 auto;	min-width:1000px;	max-width:1280px;">
<div class="b-promo b-promo_main">
    <table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
        <tr class="b-layout__tr">
            <td class="b-layout__one b-layout__one_width_50ps b-layout__one_padtb_20">
                <table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0" style="max-width:620px; min-width:500px; float:right;">
                    <tr class="b-layout__tr">
                        <td class="b-layout__one b-layout__one_padleft_20">
                            <div class="b-promo__txt b-promo__txt_fontsize_34 b-promo__txt_bold b-promo__txt_lineheight_1"><?= $pUStat['u']['count'] ?></div>
                            <div class="b-promo__txt"><?= $pUStat['u']['phrase'] ?></div>
                        </td>
                        <td class="b-layout__one b-layout__one_padleft_20">
                            <div class="b-promo__txt b-promo__txt_fontsize_34 b-promo__txt_bold b-promo__txt_lineheight_1"><?= $pUStat['p']['count'] ?></div>
                            <div class="b-promo__txt"><?= $pUStat['p']['phrase'] ?></div>
                        </td>
                        <td class="b-layout__one b-layout__one_padleft_20">
                            <div class="b-promo__txt b-promo__txt_fontsize_34 b-promo__txt_bold b-promo__txt_lineheight_1 b-promo__txt_relative b-promo__valuta"><?= $pUStat['s']['count'] ?></div>
                            <div class="b-promo__txt"><?= $pUStat['s']['phrase'] ?></div>
                        </td>
                    </tr>
                </table>
            </td>
            <td class="b-layout__one b-layout__one_padleft_20 b-layout__one_padright_20 b-layout__one_bg_fff b-layout__one_padtb_5">
                <table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0" style="max-width:600px; min-width:440px; ">
                    <tr class="b-layout__tr">
                        <td class="b-layout__one" colspan="5">
                            <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_left">
                                <a class="b-layout__link" href="/clients">Клиенты фрилансеров</a>
                            </div>
                        </td>
                    </tr>
                    <tr class="b-layout__tr">
                        <? foreach ($clients as $client) { ?>
                            <td class="b-layout__one">
                                <a target="_blank" href="<?= $client['link_client'] ?>" class="b-promo__link"> 
                                    <img width="80" height="57" src="<?= WDCPREFIX ?>/clients/<?= $client['logo'] ?>" alt="<?= $client['client_name'] ?>" title="<?= $client['client_name'] ?>" class="b-promo__photo-free"> 
                                </a>
                            </td>
                        <? } ?>
                    </tr>
                </table> 
            </td>
        </tr>
    </table>
</div>
</div>
</div>