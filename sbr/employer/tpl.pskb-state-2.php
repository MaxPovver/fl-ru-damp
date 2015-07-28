<?
$crumbs = array(
    array(
        'href' => '/' . sbr::NEW_TEMPLATE_SBR . '/',
        'name' => '«Мои Сделки»'
    ),
    array(
        'href' => '/' . sbr::NEW_TEMPLATE_SBR . '/?id=' . $sbr->id,
        'name' => reformat2($sbr->data['name'])
    )
);

include($_SERVER['DOCUMENT_ROOT'] . "/sbr/tpl.sbr-crumbs.php");
include($_SERVER['DOCUMENT_ROOT'] . "/sbr/tpl.stage-user.php");

$tenMinutesPast = time() - strtotime($lc['created']) > 600; // если прошло 10 минут
//$tenMinutesPast = false;
?>

<? if ($lc['state'] == pskb::STATE_NEW) { ?>
    
    <div id="reservation" <?= $tenMinutesPast ? 'style="display:none"' : '' ?>>
        <h2 class="b-layout__title">Идет процесс оплаты. Пожалуйста, подождите.</h2>
    </div>
    <div id="reservation-after10min" <?= $tenMinutesPast ? '' : 'style="display:none"' ?>>
        <h2 class="b-layout__title">С момента оплаты прошло более 10 минут, а деньги так и не были зарезервированы?</h2>
        <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_bold b-layout__txt_padbot_10"><span class="b-icon b-icon_help_article" style="width:24px; margin-top:-10px;"></span>Проверьте, пожалуйста, списались ли деньги с вашего счета, и выберите один из пунктов ниже:</div>
        <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padbot_10 b-layout__txt_padleft_30"><a class="b-layout__link" href="http://feedback.free-lance.ru/article/details/id/1267" target="_blank">Да, деньги списаны</a></div>  
        <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padleft_30 b-layout__txt_padbot_30"><a class="b-layout__link" href="http://feedback.free-lance.ru/article/details/id/1268" target="_blank">Нет, деньги не списаны</a></div>
    </div>
    <? if ($lc['ps_emp'] != onlinedengi::BANK_YL) { ?>
        <div class="b-layout__txt b-layout__txt_padbot_20 b-layout__txt_fontsize_15">
            Работа по сделке начнется только после фактического поступления отправленных вами денежных средств.
            <? if ($lc['sended'] != 1) { ?>
            <br />Если по какой-либо причине вы не произвели платеж, то вы можете это сделать сейчас.
            <? } ?>
        </div>
        <? if ($lc['sended'] != 1) { ?>
            <a href="javascript:void(0)" class="b-button b-button_flat b-button_flat_green" onclick="<?= $lc['ps_emp'] != onlinedengi::CARD ? "$('reserveForm').submit();" : "pskb_frame({$lc['lc_id']}, '" .pskb::getNonceSign($lc['lc_id']). "')" ?>">Произвести оплату</a>
            <form id="reserveForm" action="<?= !defined('PSKB_TEST_MODE') ? onlinedengi::REQUEST_URL : onlinedengi::REQUEST_TEST_URL ?>" method="POST">
                <input type="hidden" name="project" value="<?= onlinedengi::PROJECT_ID ?>" />
                <input type="hidden" name="amount" value="<?= $sbr->getReserveSum() ?>" />
                <input type="hidden" name="nick_extra" value="<?= $sbr->id ?>" />
                <input type="hidden" name="comment" value="<?= $sbr->getContractNum() ?>" />
                <input type="hidden" name="source" value="<?= onlinedengi::SOURCE_ID ?>" />
                <input type="hidden" name="order_id" value="<?= $lc['lc_id'] ?>" />
                <input type="hidden" name="nickname" value="<?= $lc['lc_id'] ?>" />
                <input type="hidden" name="mode_type" value="<?= $lc['ps_emp']; ?>" />
            </form>
        <? } ?>
        
    <? } else { ?>
        <div class="b-layout__txt b-layout__txt_padbot_20 b-layout__txt_fontsize_15">Работа по сделке начнется только после фактического поступления отправленных вами денежных средств.</div>
    <? } ?>
    
    
        
    <?php if($doc_file) {?>
        <table cellspacing="0" cellpadding="0" border="0" class="b-layout__table">
            <tbody>
                <tr class="b-layout__tr">
                    <td class="b-layout__middle b-layout__middle_padbot_5"><div class="b-layout__txt"><i class="b-icon b-icon_attach_pdf"></i> <a class="b-layout__link" href="<?= WDCPREFIX; ?>/<?=$doc_file->path . $doc_file->name?>"><?= $doc_file->original_name?></a>, <?= ConvertBtoMB($doc_file->size)?></div></td>
                    <td class="b-layout__right b-layout__right_padleft_20 b-layout__right_padbot_5"><div class="b-layout__txt"><a class="b-layout__link" href="<?= WDCPREFIX; ?>/<?=$doc_file->path . $doc_file->name?>">Скачать файл</a></div></td>
                </tr>
            </tbody>
        </table>
    <?php } //if ?>
    <?php if($lc['ps_emp'] == onlinedengi::BANK_YL && !$doc_file) { ?>
        <table cellspacing="0" cellpadding="0" border="0" class="b-layout__table">
            <tbody>
                <tr class="b-layout__tr">
                    <td class="b-layout__middle b-layout__middle_padbot_5" id="content_statement_doc"><img src="/images/loading-green.gif" style="float:left;margin-right:10px;"></td>
                    <td class="b-layout__right b-layout__right_padleft_20 b-layout__right_padbot_5" id="info_statement_doc"></td>
                </tr>
            </tbody>
        </table>
        <script type="text/javascript">
            window.addEvent('domready', function() {
                xajax_generateStatement(<?= $sbr->id;?>);
            });
        </script>
    <?php }?>
    
<? } ?>
        
<? if ($lc['state'] == pskb::STATE_FORM) { ?>
    <div class="b-fon b-fon_width_full">
        <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf">
            <div class="b-layout__txt b-layout__txt_padbot_10"><span class="b-icon b-icon_sbr_gok b-icon_margleft_-25"></span>Идет подготовка к резервированию, это может занять от нескольких секунд до минуты...</div>
            <div class="b-buttons b-buttons_padtop_10"> <a href="javascript:void(0)" onclick="document.location.reload();" class="b-button b-button_flat b-button_flat_green">Обновить страницу</a> </div>
        </div>
    </div>
<? } ?>

<? if (!$tenMinutesPast || $lc['state'] == pskb::STATE_FORM) { ?>
<script>
    // обновление страницы через 29 секунд
    window.addEvent('load', function(){
        var waitTime = 29000;
        setTimeout(function(){
            xajax_checkState(<?= $sbr->id;?>);
        }, waitTime)
    });
</script>
<? } ?>

<? if ($sbr->isEmp())  { $disable_reload = true; include_once $_SERVER['DOCUMENT_ROOT'] . '/sbr/employer/tpl.pskb-cards.php'; } ?>