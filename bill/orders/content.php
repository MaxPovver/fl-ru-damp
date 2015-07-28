<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/bill.common.php");
$xajax->printJavascript('/xajax/');

?>

<div class="b-layout b-layout__page">
    <h1 class="b-page__title">Мои услуги</h1>
    <?php include($_SERVER['DOCUMENT_ROOT'] . "/bill/widget/tpl.right_column.php"); ?>

    <div class="b-layout__one b-layout__one_width_72ps" id="services-list">
        <?php include($_SERVER['DOCUMENT_ROOT'] . "/bill/tpl.head_menu.php"); ?>
        
        <?php
        if(!is_emp($bill->user['role'])) { 
            $is_user_was_pro = $bill->IsUserWasPro();
        }
        $pro_payed = payed::getPayedPROList( is_emp($bill->user['role'])? 'emp' : 'frl' );
        
        foreach($pro_payed as $p) {
            $pro_type[$p['opcode']] = $p;
        }
        $payed_sum = 0; //реальная сумма

        foreach($bill->list_service as $service) {
            include ($_SERVER['DOCUMENT_ROOT'] . "/bill/orders/services/" . billing::getTemplateByService($service['service']));
            $payed_sum += ($bill->pro_exists_in_list_service && ($service['pro_ammount'] > 0 || $service['op_code'] == 53) ? $service['pro_ammount'] : $service['ammount']);
        }//foreach //подсчитали реальную сумму к оплате
        $bill->calcPayedSum($payed_sum);
        ?>
    </div>
    <form method="post" id="payment" action="/bill/payment/">
        <input type="hidden" name="transaction" value="<?=$bill->account->start_transaction($bill->user['uid'], $tr_id)?>" />
        <input type="hidden" name="action" value="payment"/>
    <div class="b-layout__txt b-layout__txt_padtop_30 b-layout__txt_fontsize_22">
        Итого: <span class="b-layout__txt b-layout__txt_fontsize_22 b-layout__txt_color_fd6c30 b-layout__txt_bold"><span class="payed-sum"><?= to_money($payed_sum, 2);?></span> руб.</span>
    </div>

    <div class="b-layout__txt b-layout__txt_padbot_10 <?= $bill->payed_sum['acc'] > 0 ? "" : "b-layout__txt_hide"?>" id="payacc_sum">
        C личного счета будет списано <span class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_color_fd6c30 b-layout__txt_bold"><span class="payed_account_sum"><?= to_money($bill->payed_sum['acc'], 2)?></span> руб.</span>
    </div>
    <div class="b-layout__txt b-layout__txt_padbot_10 <?= $bill->payed_sum['ref'] > 0 ? "" : "b-layout__txt_hide"?>" id='refund_sum'>
        Оставшаяся после оплаты сумма <span class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_color_fd6c30 b-layout__txt_bold"><span id="refund_account_sum"><?=to_money($bill->payed_sum['ref'], 2) ?></span> руб.</span>  будет возвращена вам на счет
    </div>
    <div class="b-buttons b-buttons_padtop_20">
        <a class="b-button b-button_rectangle_color_green" href="javascript:void(0)" onclick="if(!$(this).hasClass('b-button_rectangle_color_disable')) { $(this).addClass('b-button_rectangle_color_disable'); $('payment').submit() }">
            <span class="b-button__b1">
                <span class="b-button__b2">
                    <span class="b-button__txt">
                        <span id="pay_btn_name"><?= $bill->acc['sum'] < $payed_sum ? 'Перейти к оплате' : 'Оплатить' ?></span><span id="add_pay_sum" class="b-button__colored b-button__colored_fd6c30 <?= ( $bill->acc['sum'] < $payed_sum ? "": "b-layout__txt_hide")?>">&#160;<span class="add_payed_sum" ><?= to_money($bill->payed_sum['pay'], 2)?></span> руб.</span>
                    </span>
                </span>
            </span>
        </a>&nbsp;&nbsp;&nbsp;<span class="b-buttons__txt">или</span> <a class="b-buttons__link service-clear-confirm" href="javascript:void(0)">удалить список</a>
    </div>
    </form>
    <div class="b-shadow b-shadow_zindex_11 b-shadow_center b-shadow_width_540 b-shadow_hide" id="clear_confirm">
        <div class="b-shadow__right">
            <div class="b-shadow__left">
                <div class="b-shadow__top">
                    <div class="b-shadow__bottom">
                        <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_15">
                            <h2 class="b-shadow__title b-shadow__title_padbot_15">Удаление списка заказов</h2>
                            <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padbot_15">Действие необратимо. Вы не сможете восстановить список заказов. Деньги, потраченные на оплату этого списка, будут зачислены на ваш счет.</div>
                            <div class="b-buttons">
                                <a href="javascript:void(0)" class="b-button b-button_rectangle_color_red service-orders-clear">
                                    <span class="b-button__b1">
                                        <span class="b-button__b2">
                                            <span class="b-button__txt">Удалить список</span>
                                        </span>
                                    </span>
                                </a>
                                <span class="b-buttons__txt"> &nbsp;&nbsp;или </span> <a class="b-buttons__link" onclick="$('clear_confirm').addClass('b-shadow_hide');" href="javascript:void(0)">закрыть</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <span class="b-shadow__icon b-shadow__icon_close"></span>
    </div>

    <span id="wallet">
        <?php
        $popup_content   = $_SERVER['DOCUMENT_ROOT'] . "/bill/widget/popups/popup.wallet.php";
        include ( $_SERVER['DOCUMENT_ROOT'] . "/bill/widget/tpl.popup.php" );
        ?>
    </span>

</div>

<script>
    var orders = new Services({
        acc_sum: '<?= $bill->acc['sum']?>',
        min_sum: '<?= billing::MINIMUM_PAYED_SUM; ?>'
    });
</script>