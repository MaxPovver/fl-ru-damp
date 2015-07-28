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
?>
<table class="b-layout__table b-layout__table_width_full" cellspacing="0" cellpadding="0" border="0">
    <tbody>
        <tr class="b-layout__tr">
            <td class="b-layout__left b-layout__left_width_72ps">
                <? if ($lc['state'] == pskb::STATE_NEW) { ?>
                <div class="b-fon b-fon_width_full">
                    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf">
                        <?php if($lc['ps_emp'] != onlinedengi::BANK_YL) { ?>  
                        <div class="b-layout__txt b-layout__txt_padbot_10"><img src="/images/loading-green.gif" style="float:left;margin-right:10px;">
                            Идет оплата. Это может занять от нескольких секунд до нескольких минут. <a class="b-layout__link" href="/<?= sbr::NEW_TEMPLATE_SBR; ?>/?site=reserve&id=<?= $sbr->data['id'] ?>">Проверить статус оплаты</a><br />
                            Если оплата затянулась, обратитесь в <a class="b-layout__link" href="/about/feedback/">службу поддержки</a>.<br />
                        <?php }?>  
                        <? if ($lc['ps_emp'] == onlinedengi::BANK_YL) { ?>
                            Вам необходимо произвести оплату в любом банке по реквизитам, указанным в п.9 Заявления на открытие аккредитива. Работа по сделке начнется с момента фактического поступления денежных средств на указанный в п.9 Заявления расчетный счет. Не забудьте, что в назначении платежа должны быть в обязательном порядке указаны номер и дата Договора. 
                        <? } else { ?>
                                Работа по сделке начнется только после фактического поступления отправленных вами денежных средств.</div>
                        <? } ?>
                        
                        <? if($lc['sended'] != 1 && $lc['ps_emp'] != onlinedengi::BANK_YL) { ?>
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
                        <div class="b-layout__txt b-layout__txt_padbot_10">
                            Если по какой-либо причине вы еще не произвели платеж, то сможете это сделать перейдя по <a class="b-layout__link" href="javascript:void(0)" onclick="<?= $lc['ps_emp'] != onlinedengi::CARD ? "$('reserveForm').submit();" : "pskb_frame({$lc['lc_id']}, '" .pskb::getNonceSign($lc['lc_id']). "')" ?>">ссылке</a>.
                        </div>
                        <? }//if ?>
                        
                        <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_padtop_10"><a class="b-layout__link" href="/<?= sbr::NEW_TEMPLATE_SBR; ?>/">Перейти к списку сделок</a>.</div>
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
                        <? /*if ($lc['ps_emp'] == onlinedengi::BANK_YL) { ?>
                            <div class="b-buttons b-buttons_padtop_10"> <a href="/sbr/?site=invoiced&id=<?=$sbr_id?>" class="b-button b-button_rectangle_color_green"> <span class="b-button__b1"> <span class="b-button__b2"> <span class="b-button__txt">Получить квитанцию</span> </span> </span> </a> </div>
                        <? }*/ ?>
                    </div>
                </div>
                <? } ?>
                <? if ($lc['state'] == pskb::STATE_FORM) { ?>
                <div class="b-fon b-fon_width_full">
                    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf">
                        <div class="b-layout__txt b-layout__txt_padbot_10"><span class="b-icon b-icon_sbr_gok b-icon_margleft_-25"></span>Идет подготовка к резервированию, это может занять от нескольких секунд до минуты...</div>
                        <div class="b-buttons b-buttons_padtop_10"> <a href="javascript:void(0)" onclick="document.location.reload();" class="b-button b-button_flat b-button_flat_green">Обновить страницу</a> </div>
                    </div>
                </div>
                <? } ?>
                <script>
                    // обновление страницы через 59 секунд
                    window.addEvent('load', function(){
                        var waitTime = 29000;
                        setTimeout(function(){
                            xajax_checkState(<?= $sbr->id;?>);
//                            window.location.reload();
                        }, waitTime)
                    });
                </script>
            </td>
            <td class="b-layout__right"></td>
        </tr>
    </tbody>
</table>

<? if ($sbr->isEmp())  { $disable_reload = true; include_once $_SERVER['DOCUMENT_ROOT'] . '/sbr/employer/tpl.pskb-cards.php'; } ?>