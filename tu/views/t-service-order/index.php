<?php 

require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/tservices_orders.common.php");
$xajax->printJavascript('/xajax/');

    
/**
 * @var TServiceCatalogController $this
 * 
 * @var $order заказ на основе ТУ
 */

//Заголовок заказа
$title = reformat(htmlspecialchars($order['title']), 30, 0, 1);
$order_price = tservices_helper::cost_format($order['order_price'], true);
$order_days = tservices_helper::days_format($order['order_days']);
$is_reserve = tservices_helper::isOrderReserve($order['pay_type']);
$is_reserved = $is_reserve && $order['reserve']->isExistReserveData() && $order['reserve']->isStatusReserved();

$class_color = '000';
if($is_reserve):
    $class_color = 'ee1d16';
    if ($is_reserved) $class_color = '6db335';
endif;

?>
<script type="text/javascript">
    var _ORDERID = <?=$order['id']?>;
</script>
<?php $this->renderClip('order-breadcrumbs') ?>
<h1 class="b-page__title">
    <?=$title?>
</h1>
<div class="b-layout b-layout_padtb_10 b-layout_bordtop_dedfe0 b-layout_bordbot_dedfe0 b-layout_margbot_20">
    <div class="b-layout b-layout_float_right b-layout__one_width_full_ipad b-layout_padbot_10_ipad">
        <?php if($allow_change): ?>
        <div class="b-layout__txt b-layout__txt_fontsize_18 b-layout__txt_right b-layout__txt_left_ipad">
            <a data-popup="tu_edit_budjet" 
               class="b-layout__link b-layout__link_bordbot_dot_<?=$class_color?>" 
               href="javascript:void(0);" 
               onClick="yaCounter6051055.reachGoal('zakaz_change');">
                <span class="__tservice_order_price_label"><?php if($is_reserve): ?>Бюджет:<?php else: ?>Стоимость:<?php endif; ?></span> 
                <span class="b-layout__bold" id="tu-container-price">
                    <?=$order_price?>
                </span>
            </a>
        </div>
        <div class="b-layout__txt b-layout__txt_fontsize_18 b-layout__txt_right b-layout__txt_left_ipad">
            <a data-popup="tu_edit_budjet" 
               class="b-layout__link b-layout__link_bordbot_dot_000" 
               href="javascript:void(0);" 
               onClick="yaCounter6051055.reachGoal('zakaz_change');">
                Срок: 
                <span class="b-layout__bold" id="tu-container-days">
                    <?=$order_days?>
                </span>
            </a>
        </div>
        <?php $this->renderClip('order-change-cost-popup') ?>
        <?php else: ?>
        <div class="b-layout__txt b-layout__txt_fontsize_18 b-layout__txt_right b-layout__txt_left_ipad b-layout__txt_color_<?=$class_color?> i-shadow i-shadow_hover_show">
            <?php if($is_reserve): ?>Бюджет:<?php else: ?>Стоимость:<?php endif; ?> 
            <span class="b-layout__bold" id="tu-container-price">
                <?=$order_price?>
            </span>
            <div class="b-shadow b-shadow_hide b-shadow_top_25">
                <div class="b-shadow__body b-shadow__body_pad_15 b-shadow_width_270 b-shadow__body_bg_fff">
                    <div class="b-shadow__txt b-shadow__txt_fontsize_11 b-shadow__txt_normal">
                    <?php if($is_reserve): ?>
                        Бюджет заказа с оплатой через Безопасную 
                        сделку&nbsp;&mdash;&nbsp;<?php if ($is_reserved): ?>успешно<?php else: ?>еще не<?php endif; ?> зарезервирован.
                    <?php else: ?>
                        Бюджет заказа с прямой оплатой.
                    <?php endif; ?>
                    </div>
                </div>
            </div>            
            
        </div>
        <div class="b-layout__txt b-layout__txt_fontsize_18 b-layout__txt_right b-layout__txt_left_ipad i-shadow i-shadow_hover_show">
            Срок: 
            <span class="b-layout__bold" id="tu-container-days">
                <?=$order_days?>
            </span>
            <div class="b-shadow b-shadow_hide b-shadow_top_25">
                <div class="b-shadow__body b-shadow__body_pad_15 b-shadow_width_270 b-shadow__body_bg_fff">
                    <div class="b-shadow__txt b-shadow__txt_fontsize_11 b-shadow__txt_normal">
                        Срок выполнения работы &mdash; начинается<?php if($is_reserve): ?> 
                        с момента резервирования бюджета.
                        <?php else: ?>, как только Исполнитель подтвердил заказ.
                        <?php endif; ?>
                    </div>
                </div>
            </div>            
        </div>        
        <?php endif; ?>
    </div>

    <?php if($is_owner){ ?>
        <?php $this->renderClip('user-profile') ?>
    <?php }else{ ?>
        <div class="b-txt">Заказчик:</div>
        <?php $this->renderClip('employer-profile') ?>
        <br/>
        <div class="b-txt">Исполнитель:</div>
        <?php $this->renderClip('freelancer-profile') ?>
    <?php } ?>
</div>
<div class="b-layout b-layout_bordbot_dedfe0 b-layout_margbot_20 b-layout_padleft_60 b-layout_padbot_20 b-layout__txt_padleft_null_iphone">
    
    <?=tservices_helper::showFlashMessages()?>
    
    <div id="tservices_order_status_<?=$order['id']?>" class="b-fon b-fon_bg_f5 b-fon_pad_10 b-fon_margbot_20 b-fon_overflow_hidden">
        <?php echo $this->renderClip('order-status') ?>  
    </div>
    <?php if($order['type'] == TServiceOrderModel::TYPE_TSERVICE): ?>
    <div class="b-layout__txt b-layout__txt_bold">Что вы получите</div>
    <div class="b-layout__txt b-layout__txt_padbot_20">
        <?=reformat(htmlspecialchars($order['description']), 60, 0, 0, 1)?>
    </div>

    <div class="b-layout__txt b-layout__txt_bold">Что нужно, чтобы начать</div>
    <div class="b-layout__txt b-layout__txt_padbot_20">
        <?=reformat(htmlspecialchars($order['requirement']), 60, 0, 0, 1)?>
    </div>
    <?php if($order['order_extra']){ ?> 
    <div class="b-layout__txt b-layout__txt_bold">Дополнительные услуги</div>
    <div class="b-layout__txt">
        <?php foreach($order['order_extra'] as $idx ){ ?>
            <?php if(!isset($order['extra'][$idx])) continue; ?>
            <?php echo reformat(htmlspecialchars($order['extra'][$idx]['title']), 30, 0, 1); ?><br/>
        <?php } ?>
    </div>
    <?php } ?>
    <?php else: ?>
    <div class="b-layout__txt">
        <?=reformat(htmlspecialchars($order['description']), 60, 0, 0, 1)?>
    </div>    
    <?php endif; ?>
</div>
<?php echo $this->renderClip('order-files') ?>

<?php $this->renderClip('order-messages-form') ?>
<?php $this->renderClip('order-messages') ?>

<div id="history">
    <?php $this->renderClip('order-history') ?>
</div>
<?php //if($is_owner) $this->renderClip('order-feedback-popup'); ?>