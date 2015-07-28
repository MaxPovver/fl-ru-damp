<?php 
    if($isAllowBillInvoice && $isValidBillInvoice):   
?>
<script type="text/template" id="bill_invoice_template">
    <a target="_blank" href="{link}" class="b-layout__link b-layout__link_fontsize_13">{name}</a>
    <div class="b-layout__txt b-layout__txt_padleft_10 b-layout__txt_inline-block b-layout__txt_fontsize_11">
        дл€ пополнени€ банковским переводом
        <a class="b-layout__link" href="javascript:void(0);" onclick="xajax_removeBillInvoice({num});">”далить</a>
    </div>
</script>   
<?php
    endif;
?>
<table class="b-layout__table b-layout__table_width_full">
    <tr class="b-layout__tr">
        <td class="b-layout__td">
            <div class="b-layout b-layout_pad_10 b-layout_bord_e6 b-layout_width_250 b-layout_margbot_20">
                <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_bold">Ќа счету: <span class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_color_fd6c30"><?= to_money($bill->acc['sum'], 2)?> руб. <?= $bill->acc['bonus_sum'] > 0 ? "(+ ".to_money($bill->acc['bonus_sum'], 2) . " руб.)" : ""?></span></div>
                <div class="b-layout__txt b-layout__txt_fontsize_11">Ќомер вашего счета: <?= $bill->acc['id'];?> </div>
            </div>
        </td>
        <?php 
            if($isAllowBillInvoice): 
                $fin_url = sprintf("/users/%s/setup/finance/", $_SESSION['login']);
        ?>
        <td class="b-layout__td b-layout__td_padleft_20<?php if (!$showReserveNotice): ?> b-layout__td_padtop_10<?php endif; ?> b-layout__td_width_full">
        <?php
            if($isValidBillInvoice):
                $is_billInvoice = isset($billInvoice) && $billInvoice;
        ?>
            <div id="bill_invoice_create" class="b-layout<?php if($is_billInvoice): ?> b-layout_hide<?php endif; ?>">
                <a href="javascript:void(0);" data-popup="<?=quickPaymentPopupBillInvoice::getInstance()->getPopupId()?>" class="b-button b-button_flat b-button_flat_green b-button_nowrap">
                    —формировать счет на оплату
                </a>
                <div class="b-layout__txt b-layout__txt_padleft_10 b-layout__txt_inline-block b-layout__txt_fontsize_11">
                    дл€ пополнени€ банковским переводом &nbsp;&nbsp;&nbsp;<a class="b-layout__link" href="<?=$fin_url?>">ѕроверить и изменить реквизиты</a>
                </div>
                <?php if ($showReserveNotice): ?>
                <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padtop_10 b-layout__txt_padbot_10">
                    <strong>¬нимание!</strong> ¬ Ѕезопасных сделках не предусмотрено резервирование с личного счета.<br/>
                    —редства, зачисленные на личный счет, могут быть потрачены только на платные сервисы сайта.
                </div>
                <?php endif; ?>
                <?=quickPaymentPopupBillInvoice::getInstance()->render();?>
            </div>
            <div id="bill_invoice_remove" class="b-layout b-layout_padtop_10 <?php if(!$is_billInvoice): ?> b-layout_hide<?php endif; ?>">
                <?php if($is_billInvoice): ?>
                    <a target="_blank" href="<?=WDCPREFIX . '/' . $billInvoice['file']?>" class="b-layout__link b-layout__link_fontsize_13"><?=$billInvoice['name']?></a>
                    <div class="b-layout__txt b-layout__txt_padleft_10 b-layout__txt_inline-block b-layout__txt_fontsize_11">
                        дл€ пополнени€ банковским переводом &nbsp;&nbsp;&nbsp;<a  class="b-layout__link" href="javascript:void(0);" onclick="xajax_removeBillInvoice(<?=$billInvoice['invoice_id']?>);">”далить</a>
                    </div>
                <?php endif; ?>
            </div>
            <?php else: ?>
                <a href="<?=$fin_url?>" class="b-button b-button_flat b-button_flat_green b-button_nowrap">
                    «аполнить реквизиты
                </a>
                <div class="b-layout__txt b-layout__txt_padleft_10 b-layout__txt_inline-block b-layout__txt_fontsize_11">
                    дл€ пополнени€ банковским переводом
                </div>
            <?php endif; ?>
        </td>
        <?php endif; ?>
        
        <?php if($isAllowAddFunds): ?>
        <td class="b-layout__td b-layout__td_padleft_20 b-layout__td_padtop_10 b-layout__td_width_full">
                <a href="javascript:void(0);" 
                   data-popup="<?=quickPaymentPopupAccount::getInstance()->getPopupId()?>" 
                   class="b-button b-button_flat b-button_flat_green b-button_nowrap">
                    ѕополнить счет
                </a>
        </td>
        <?php endif; ?>
    </tr>
</table>

<?php if ($isAllowAddFunds): ?>
    <?=quickPaymentPopupAccount::getInstance()->render()?>
<?php endif; ?>