<div class="b-fon b-fon_width_full b-fon_margtb_-8_-5 <?= ($stage->data['status'] == sbr::STATUS_COMPLETED && $curr_sbr->data['status'] != sbr::STATUS_COMPLETED ? "b-fon_hide" : "")?> <?= ( sbr_notification::isReaction($stage->notification) && $stpos != $stcount ? 'b-fon_padbot_9' : '');?>">
    <div class="b-fon__body b-fon__body_padtb_5 <?= ( sbr_notification::isReaction($stage->notification) ? "b-fon__body_bg_f0ffdf" : "b-fon__body_bg_fff b-fon__body_hover_bg_f2f4f5");?>">
        <table class="b-layout__table b-layout__table_width_full <?= ( sbr_notification::isReaction($stage->notification) || $stpos == $stcount ? '' : 'b-layout__table_margbot_10');?>" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left">
                    <?
                    $status = (int)$stage->status;
                    $notif = $stage->notification['ntype'];
                    $extraLinkStyle = "";
                    if (($status === sbr_stages::STATUS_COMPLETED || $status === sbr_stages::STATUS_ARBITRAGED)
                        && (strlen($notif) === 0 || $notif === 'sbr_stages.MONEY_PAID' || $notif === 'sbr_stages.EMP_MONEY_REFUNDED')) {
                        $extraLinkStyle = ' b-layout__link_color_80';
                    }
                    if($stage->sbr->status == sbr::STATUS_CANCELED || $stage->sbr->status == sbr::STATUS_REFUSED) {
                        $extraLinkStyle = ' b-layout__link_color_80';
                    }
                    ?>
                    <div class="b-layout__txt b-layout__txt_padleft_35 b-layout__txt_fontsize_15 b-layout__txt_lineheight_18 "><a class="b-layout__link b-layout__link_bold<?= $extraLinkStyle ?>" href="?site=Stage&id=<?=$stage->data['id']?>"><?=reformat($stage->data['name'], 24, 0, 1)?></a></div>
                </td>
                <td class="b-layout__middle b-layout__middle_width_175">
                    <div class="b-layout__txt"><?= $stage->data['int_work_time']?> <?=ending(abs($stage->data['int_work_time']), 'день', 'дня', 'дней')?> на этап</div>
                </td>
                <td class="b-layout__right b-layout__right_width_400">
                    <div class="b-layout__txt b-layout__txt_right_0 b-layout__txt_absolute b-layout__txt_padright_15 b-layout__txt_relative b-layout__txt_zindex_1 <?= sbr_notification::isReaction($stage->notification) ? '' : 'b-layout__txt_hide'?>"><a class="b-layout__link" href="?site=Stage&id=<?=$stage->data['id']?>"><?= sbr_notification::isReaction($stage->notification) ? "Посмотреть" : "Перейти в этап"?></a></div>
                    <div class="b-layout__txt b-layout__txt_relative <?= $stage->getStatusColor()?>"><span class="b-icon b-icon_top_1 <?= $stage->getStatusICO()?>"></span><?= $stage->getStatusName();?><span class="b-layout__hider" style="display:none;"></span></div>
                </td>
            </tr>
        </table>
    </div>
</div>