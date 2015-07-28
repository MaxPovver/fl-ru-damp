<?php

/**
 * Попап при заказе ТУ для заказчика
 */

$title = reformat($title, 30, 0, 1);
$price = tservices_helper::cost_format($price,true, false, false);
$days = $days . ' ' . ending($days, 'день', 'дня', 'дней');

$show_popup = (isset($_POST['popup']));

?>
<div id="tservices_orders_status_popup" class="b-shadow b-shadow_center b-shadow_width_520 <?php if(!$show_popup){ ?>b-shadow_hide <?php } ?>b-shadow__quick" style="display:block;">
    <div class="b-shadow__body b-shadow__body_pad_15_20">
        <h2 class="b-layout__title">
            Заказ услуги
        </h2>
        <div class="b-layout__txt b-layout__txt_padbot_10">
            Вы заказываете у исполнителя <b><?=$frl_fullname?></b><br/> 
            услугу &laquo;<b><?=$title?></b>&raquo;
            <br/>
            на сумму <b><span class="__tservice_price2"><?=$price?></span></b> со сроком выполнения <b><span class="__tservice_days"><?=$days?></span></b>.
            <br/>
            Не забудьте согласовать с исполнителем все условия сотрудничества и порядок оплаты выполненной работы. 
            <br/>
            <br/>
            Исполнитель будет уведомлен о заказе, и как только подтвердит его, начнется выполнение работы.
        </div>
        <div class="b-buttons b-buttons_padtop_20">
            <a href="javascript:void(0);" class="b-button b-button_flat b-button_flat_green" onclick="yaCounter6051055.reachGoal('zakaz_tu'); TServices.onSendToCbr(this, '__form_tservice');">
                <span class="__tservices_orders_feedback_submit_label">Создать заказ и перейти в него</span>
            </a>
            <span class="b-layout__txt b-layout__txt_fontsize_11">&#160; или 
                <a class="b-layout__link" href="javascript:void(0);" onclick="TServices.closePopup();">пока не заказывать услугу</a>
            </span>
        </div>
   </div>    
   <span class="b-shadow__icon b-shadow__icon_close"></span>
</div>