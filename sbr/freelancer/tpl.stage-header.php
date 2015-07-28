<? if($stage->notification['ntype'] == 'sbr_stages.FRL_FEEDBACK' && $head_docs && $stage->sbr->scheme_type == sbr::SCHEME_PDRD2) { $hdoc_cnt = count($head_docs);?>
    <div class="b-layout b-layout_padleft_35 b-layout_padtop_15 b-layout_padbot_15 b-layout_padright_15" id="head_docs">
        <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_color_a0763b">Для того, чтобы получить заработанные деньги, вам нужно скачать, распечатать, подписать <?= ending($hdoc_cnt, 'документ', 'документы', 'документы');?>:</div>
            <table cellspacing="0" cellpadding="0" border="0" class="b-layout__table">
                <tbody>
                    <? foreach($head_docs as $hdoc) { $e = explode(".", $hdoc['file_name']); $ext = $e[count($e)-1];  ?>
                    <tr class="b-layout__tr">
                        <td class="b-layout__middle b-layout__middle_padbot_5">
                            <div class="b-layout__txt">
                                <i class="b-icon b-icon_attach_<?= getICOFile($ext);?>"></i> 
                                <a href="<?= WDCPREFIX; ?>/<?= $hdoc['file_path'] . $hdoc['file_name']?>" class="b-layout__link"><?= $hdoc['name']?></a>, <?= ConvertBtoMB($hdoc['file_size'])?>
                            </div>
                        </td>
                        <td class="b-layout__right b-layout__right_padleft_20 b-layout__right_padbot_5">
                            <div class="b-layout__txt">
                                <a href="<?= WDCPREFIX; ?>/<?= $hdoc['file_path'] . $hdoc['file_name']?>" class="b-layout__link">Скачать</a>
                            </div>
                        </td>
                    </tr>
                    <? }//foreach?>
                </tbody>
            </table>										
        <div class="b-layout__txt b-layout__txt_padtop_5 b-layout__txt_color_a0763b">Подписанный документ нужно отправить по любому удобному вам адресу:</div>
        <div class="b-layout__txt b-layout__txt_bold b-layout__txt_color_a0763b">129223, Москва, а/я 33;</div>
        <div class="b-layout__txt b-layout__txt_bold b-layout__txt_color_a0763b">190031, Санкт-Петербург, Сенная пл., д.13/52, а/я 427; </div>
        <div class="b-layout__txt b-layout__txt_bold b-layout__txt_color_a0763b">420032, Казань, а/я 624;</div>
        <div class="b-layout__txt b-layout__txt_bold b-layout__txt_color_a0763b">454014, Челябинск-14, а/я 2710.</div>
        <div class="b-layout__txt b-layout__txt_color_a0763b b-layout__txt_padbot_10">На конверте обязательно укажите наименование организации ООО «ВААН». </div>
        <div class="b-layout__txt b-layout__txt_color_a0763b">Обычно документы идут в течение от трех дней до двух недель. Если по истечении этого времени вы все еще не получили деньги, <a href="/about/feedback/" class="b-layout__link b-layout__link_bordbot_dot_0f71c8">обратитесь в службу поддержки</a>.</div>
    </div>
<? }?> 