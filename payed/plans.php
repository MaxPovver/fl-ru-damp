<?php

/**
 * Вывод списка тарифов ПРО
 */

?>
<link href='https://fonts.googleapis.com/css?family=Open+Sans:300,400&subset=cyrillic,latin' rel='stylesheet' type='text/css'>
<div class="b-layout g-txt_center">
<?php

$is_emp = isset($is_emp) && $is_emp == true;
$list = payed::getPayedPROList($is_emp?'emp':'frl');

//$cnt = count($list);
//$last_key = key(end($list));
foreach ($list as $key => $pay):
    
    if ($pay['day']):
        $txt_time = ending($pay['day'], 'день', 'дня', 'дня');
        $days = $pay['day'];
        $title = "{$pay['day']} {$txt_time}";
        $value = $pay['day'];
    elseif ($pay['week']):
        $txt_time = ending($pay['week'], 'неделя', 'недели', 'недель');
        $days = $pay['week']*7;
        $title = "{$pay['week']} {$txt_time}";
        $value = $pay['week'];
    else:
        if ($pay['month'] == 12):
            $txt_time = 'год';
            $title = "1 {$txt_time}";
            $value = 1;
        else:   
            $txt_time = ending($pay['month'], 'месяц', 'месяца', 'месяцев');
            $title = "{$pay['month']} {$txt_time}";
            $value = $pay['month'];
        endif;
        $days = $pay['month']*30;
    endif;

    //$perday = ($days > 0)?round($pay['cost'] / $days):null;
    //$txt_perday = ending($perday, 'рубль', 'рубля', 'рублей') . " в день";
    
    $txt_total = 'руб.';//ending($pay['cost'], 'рубль', 'рубля', 'рублей');// . (($value > 1)?" за {$value} ":" в ") . $txt_time;
    
    $old_perday = null;
    if (isset($pay['old_cost'])):
        $old_perday = ($days > 0)?round($pay['old_cost'] / $days):null;
    endif;
?>
    <div class="b-layout b-layout_inline-block b-layout_block_iphone" id="pro_payed_<?=$pay['opcode']?>">
        <div class="b-promo__buy b-promo__buy_grey payed_form <?php if(isset($pay['class'])): echo $pay['class']; endif; ?>" id="payed_form_<?=$pay['opcode']?>">
            <div class="b-promo__buy-head">
                <?= $title ?>
                <?php if (isset($pay['badge'])): ?>
                <br/>
                <div class="b-layout__txt b-layout__txt_badge">
                    <?=$pay['badge']?>
                </div>
                <?php endif; ?>                
            </div>
            <div class="b-promo__buy-body">
                <?php if(isset($perday) && $perday): ?>
                <div class="b-layout__txt b-layout__txt_color_fd6c30 b-layout__txt_fontsize_34 b-layout__txt_lineheight_1 b-layout__txt_padtop_25">
                    <?php if($old_perday): ?>
                        <span class="b-layout__txt_through b-layout__txt_color_d7d7d7"><?= $old_perday ?></span>&nbsp;
                    <?php endif; ?>
                    <?= $perday ?>
                </div>
                <div class="b-layout__txt">
                    <?= $txt_perday ?>
                </div>
                <?php endif; ?>
                
                <?php if (isset($pay['sale'])): ?>
                <div class="b-layout__txt b-layout__txt_sale">
                    экономия
                    <span class="b-layout__txt_sale__persent"><?= $pay['sale'] ?></span>
                </div>
                <?php elseif (isset($pay['sale_txt'])): ?>
                <div class="b-layout__txt b-layout__txt_sale_txt">
                    <?= $pay['sale_txt'] ?>
                </div>
                <?php endif; ?>
                
                <div class="b-buttons b-buttons_center b-buttons_padbot_15">
                    <?php if (isset($disabled_pay_button)): ?>
                    <a class="b-button b-button_flat b-button_flat_green b-button_flat_mid b-button_disabled" href="javascript:void(0)">
                        Купить
                    </a>                    
                    <?php else: ?>
                    <a id="is_enough_<?= $pay['opcode']?>" 
                       class="b-button b-button_flat b-button_flat_green b-button_flat_mid <?php if ($current_uid): if(isset($is_emp_plans)): ?>__ga__pro__emp_buy<?php else: ?>__ga__pro__frl_buy<?php endif; endif; ?>" 
                       href="javascript:void(0)" 
                       <?php if ($current_uid > 0): ?>data-popup="<?=quickPaymentPopupPro::getInstance()->getPopupId()?>" 
                       data-popup-params="<?=$pay['opcode']?>" 
                       <?php else: ?>onclick="<?="window.location = '/registration/?user_action=buypro';"?>"<?php endif; ?>
                       <?php if($is_emp): ?>
                       data-ga-event="{ec: 'customer', ea: 'customer_propage_buybutton_clicked',el: '<?= op_codes::getLabel($pay['opcode']) ?>'}"<?php else: ?>
                       data-ga-event="{ec: 'freelancer', ea: 'freelancer_propage_buybutton_clicked',el: '<?= op_codes::getLabel($pay['opcode']) ?>'}"<?php endif; ?>>
                        Купить
                    </a>
                    <?php endif; ?>
                </div>
                
                <?php if(isset($pay['old_cost'])): ?>
                <div class="b-layout__txt b-layout__txt_ff_os b-layout__txt_fontsize_16 b-layout__txt_lineheight_1 b-layout__txt_color_f1645b">
                    <span class="b-layout__txt_through b-layout__txt_color_99">&nbsp;<?=$pay['old_cost']?>&nbsp;</span>&nbsp;
                    <?= $pay['cost']?> <?= $txt_total ?>
                </div>  
                <?php else: ?>
                <div class="b-layout__txt b-layout__txt_ff_os b-layout__txt_fontsize_16 b-layout__txt_lineheight_1">
                    <?= $pay['cost']?> <?= $txt_total ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php
endforeach;
?>
</div>