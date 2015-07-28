<?php

    $fullname = "{$user['uname']} {$user['usurname']} [{$user['login']}]";
    //$data_url = tservices_helper::getOrderCardUrl($order_id);
    $hash = md5(TServiceOrderModel::SOLT . $order_id);
    
    //$emp_feedback = $employer['feedback'];
    //$is_emp_feedback = !empty($emp_feedback);
    //$emp_is_good = ($employer['rating'] > 0);
    $emp_color = ($emp_is_good)?'6db335':'c10600';
    $emp_feedback = reformat($emp_feedback, 30);    
    
    
    //$frl_feedback = $freelancer['feedback'];
    //$is_frl_feedback = !empty($frl_feedback);
    //$frl_is_good = ($freelancer['rating'] > 0);
    $frl_color = ($frl_is_good)?'6db335':'c10600';
    $frl_feedback = reformat($frl_feedback, 30); 
    
    $no_reserve_warning = 'При прямой оплате вы самостоятельно регулируете все претензии по качеству, срокам и оплате выполненной работы.';
    
    //@todo: Используется только в сообщениях статуса для админа
    //Пока обойдемся без имен и так ясно кто есть кто.
    $frl_fullname = "";//(isset($freelancer))?"{$freelancer['uname']} {$freelancer['usurname']} [{$freelancer['login']}]":"";
    $emp_fullname = "";//(isset($employer))?"{$employer['uname']} {$employer['usurname']} [{$employer['login']}]":"";
    
    
    $order_url = $GLOBALS['host'] . tservices_helper::getOrderCardUrl($order_id);

    $icon_prefix = $pay_type == TServiceOrderModel::PAYTYPE_RESERVE ? 'bs' : 'po';
    
    //--------------------------------------------------------------------------
    
    
    if($order_status == TServiceOrderModel::STATUS_NEW)
    {
        if($is_adm)
        {
//------------------------------------------------------------------------------
// Новый заказ.
// Статус для админа.
//------------------------------------------------------------------------------            
?>
        <table class="b-layout__table">
            <tr class="b-layout__tr">
                <td class="b-layout__td">
                    <div class="b-layout__txt b-layout__txt_color_000">
                        Исполнитель <?php echo $frl_fullname ?> получил уведомление о заказанной услуге.<br/>
                        Комиссия <?php echo tservices_helper::cost_format($tax_price,true, false, false) ?> (<?php echo $tax*100 ?>% от заявленного бюджета).
                    </div>
                </td>                
            </tr> 
        </table> 
<?php
        }
        elseif($is_emp)
        {
//------------------------------------------------------------------------------
// Новый заказ. 
// Статус для заказчик, он может отменить
//------------------------------------------------------------------------------ 

            $icon_action = $pay_type == TServiceOrderModel::PAYTYPE_RESERVE && $is_reserve_accepted ? 'pay' : 'edit';
?>
     <table class="b-layout__table b-layout__table_width_full">
         <tr class="b-layout__tr">
         <td class="b-layout__td b-layout__td_width_60 b-layout__td_ipad b-layout__td_width_null_ipad">
             <img class="b-user__pic"  alt="" src="/images/po/<?=$icon_prefix?>_<?=$icon_action?>.png"/>
         </td>
         <td class="b-layout__td b-layout__td_ipad">
<?php 
             if($pay_type == TServiceOrderModel::PAYTYPE_RESERVE): 
//------------------------------------------------------------------------------
// Новый заказ по схеме резерва средств.
// Статус для заказчика - резерв средств.
//------------------------------------------------------------------------------                 
                 if($is_reserve_accepted):
                     $reserve_tax = $reserve_data['tax']*100;
                     $reserve_price = tservices_helper::cost_format($reserve_data['reserve_price'],true, false, false);
                     $fn_url = sprintf("/users/%s/setup/finance/", $employer['login']);
?>
             <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout_float_right b-layout__txt_float_none_iphone b-layout__txt_padbot_5">
                <span class="b-icon b-icon_sbr_oask"></span>
                <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/list/27457-baza-znanij-flru/?category=12682">
                    О Безопасных сделках
                </a>
             </div>
             <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                Заказ с оплатой через Безопасную сделку &mdash; резервирование суммы
             </div>
             <div class="b-layout__txt b-layout__txt_padbot_10 b-fon_overflow_hidden">
                 Исполнитель подтвердил заказ и готов его выполнить. Пожалуйста, зарезервируйте сумму оплаты (+<?=$reserve_tax?>% комиссии) на сайте &mdash; после этого начнется выполнение работы по заказу.
<?php 
                if(!$reserve->isEmpFinanceReqvsValid()): 
?>
                <br/><br/>
                Обратите внимание: перед резервированием вам необходимо заполнить данные на странице "Финансы".
<?php                
                endif;
?>
             </div>
             <?php if($reserve->isStatusError()): ?>
             <div class="b-layout__txt b-layout__txt_padbot_10 b-fon_overflow_hidden">
<?php
                    if($reserve->getReasonReserve()): 
?>
                 <strong>Резервирование приостановлено по причине: </strong>
                 <?=$reserve->getReasonReserve()?>
<?php
                    else:
?>
                 К сожалению, при резерве суммы возникла ошибка. 
                 Пожалуйста, проверьте, правильные ли реквизиты 
                 указаны на странице Финансы, 
                 и повторите запрос.
<?php
                    endif;
?>
             </div>
             <?php elseif($reserve->isEmpFinanceFailStatus()): ?>
             <div class="b-layout__txt b-layout__txt_padbot_10 b-fon_overflow_hidden">
                 К сожалению, на странице Финансы указаны некорректные 
                 данные<?php if($reason = $reserve->getEmpFinanceBlockedReason()): ?>: <?=$reason?>.<?php else: ?>.<? endif; ?>
                 <br/>Для перехода к процессу резервирования укажите, пожалуйста, корректные данные.
             </div>
             <?php endif; ?>
             <div class="b-buttons">
<?php
                   if($reserve->isEmpAllowFinance()):
?>
                 <?php if($reserve->isStatusError()): ?>
                 <a href="javascript:void(0);" 
                    class="b-button b-button_flat b-button_flat_green" 
                    data-duplicate="1"
                    data-popup="quick_payment_reserve" 
                    data-url="<?=$order_url?>">
                     Повторить резерв <?=$reserve_price?>
                 </a>                 
                  <a href="<?=$fn_url?>" 
                     class="b-button b-button_flat b-button_flat_green" 
                     data-duplicate="1">
                     Перейти на страницу "Финансы"
                  </a>  
                 <?php else: ?>
                 <a class="b-button b-button_flat b-button_flat_green b-button_margright_10" 
                    data-url="<?=$order_url?>"
                    data-scrollto = "form-block"
                    href="javascript:void(0);"
                    onclick="if($('tservice-message-textarea')) $('tservice-message-textarea').focus();">
                     Обсудить заказ
                 </a>
                 <a href="javascript:void(0);" 
                    class="b-button b-button_flat b-button_flat_green" 
                    data-duplicate="1"
                    data-popup="quick_payment_reserve" 
                    data-url="<?=$order_url?>">
                     Зарезервировать <?=$reserve_price?>
                 </a>                 
                 <?php endif; ?>
<?php
                    elseif(!$reserve->isEmpFinanceValid()):
?>
                  <a href="<?=$fn_url?>" 
                     class="b-button b-button_flat b-button_flat_green" 
                     data-duplicate="1">
                     Перейти на страницу "Финансы"
                  </a>
<?php
                    else:
?>
                  <a href="javascript:void(0)" 
                    class="b-button b-button_flat b-button_flat_green b-button_disabled">
                    Проверка данных модератором 
                  </a>
<?php
                    endif;
?>
                 <span class="b-buttons__txt b-button__txt_padbot_10_ipad">&#160; или &#160;</span>
                 <a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_color_0f71c8" 
                    href="<?php echo tservices_helper::getOrderStatusUrl($order_id, 'cancel'); ?>"
                    data-duplicate="2">
                     отменить заказ
                 </a>
                 
             </div> 
<?php
                    if(!$is_list && $reserve->isEmpAllowFinance()):
                        $this->widget('quickPaymentPopupReserve', array(
                            'reserveInstance' => $reserve,
                            //@todo: передав обьект резерва можно постепенно убрать все ниже
                            'reserve_id' => $reserve_data['id'],
                            'uid' => $reserve_data['emp_id'],
                            'opt' => array(
                                'price' => tservices_helper::cost_format($reserve_data['price'],true, false, false),
                                'reserve_price' => $reserve_price,
                                'tax' => $reserve_tax.'%',
                                'tax_price' => tservices_helper::cost_format($reserve_data['tax_price'],true, false, false),
                                'fn_url' => ($reserve->isAllowEditFinance($reserve_data['emp_id'], true))?$fn_url:false,
                                'order_url' => $order_url
                            )
                        ));
                    
                        if($reserve->isEmpJuri()):
?>
             <div class="b-layout__txt b-layout__txt_padtop_10 b-layout__txt_padbot_5">
                 <table class="b-layout__table">
                     <tr>
                         <td class="b-layout__td b-layout__td_padright_5">
                             <input type="checkbox" name="reserve_send_docs" id="reserve_send_docs" checked="checked" />
                         </td>
                         <td>
                            <label for="reserve_send_docs">
                                После завершения заказа отправить оригиналы закрывающих документов по адресу: <br/>
                                <?=$this->getEmpAddress()?>&nbsp;
                                <a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_color_0f71c8" href="<?=$fn_url?>">Изменить адрес</a>
                            </label>                             
                         </td>
                     </tr>
                 </table>
             </div>
<?php                    
                        endif;
                    endif;
                    
                else:
//------------------------------------------------------------------------------
// Новый заказ по схеме резерва средств.
// Статус для заказчика - подтверждение исполнителем.
//------------------------------------------------------------------------------                    
?>
            <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout_float_right b-layout__txt_float_none_iphone b-layout__txt_padbot_5">
                <span class="b-icon b-icon_sbr_oask"></span>
                <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/list/27457-baza-znanij-flru/?category=12682">
                    О Безопасных сделках
                </a>
            </div>
             <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                Заказ с оплатой через Безопасную сделку &mdash; обсуждение условий
             </div>
             <div class="b-layout__txt b-layout__txt_padbot_10 b-fon_overflow_hidden">
                 Пожалуйста, обсудите с Исполнителем все условия сотрудничества, согласуйте сроки и стоимость работы. Как только Исполнитель подтвердит заказ (согласится на его выполнение), вы сможете зарезервировать сумму оплаты (+комиссию) и начать сотрудничество.
             </div>
             <div class="b-buttons b-buttons_padbot_10">
                 <a class="b-button b-button_flat b-button_flat_green b-button_margright_10" 
                    data-url="<?=$order_url?>"
                    data-scrollto = "form-block"
                    href="javascript:void(0);"
                    onclick="if($('tservice-message-textarea')) $('tservice-message-textarea').focus();">
                     Обсудить условия
                 </a>
                 <a class="b-button b-button_flat b-button_flat_red" 
                    href="<?php echo tservices_helper::getOrderStatusUrl($order_id, 'cancel'); ?>"
                    data-duplicate="1">
                     Отменить заказ
                 </a>
                 <span class="b-buttons__txt b-button__txt_padbot_10_ipad">&#160; или &#160;</span>
                 <a class="b-button" 
                    data-url="<?=$order_url?>" 
                    data-popup="tu_edit_budjet" 
                    data-duplicate="2"
                    href="javascript:void(0);" 
                    onClick="yaCounter6051055.reachGoal('zakaz_change');$('tu_edit_budjet').removeClass('b-shadow_hide');">
                     <span class="b-button__txt_underline">
                         изменить срок, сумму или тип оплаты
                     </span>
                 </a>
             </div>            
<?php
                endif;
            else: 
//------------------------------------------------------------------------------
// Новый заказ по обычной схеме.
// Статус для заказчика.
//------------------------------------------------------------------------------                
?>
            <?php /*
            <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_right">
                <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/topic/483819-soglasovanie-uslovij/">
                    Подробнее о согласовании заказа
                </a>
            </div> 
				*/ ?>            
             <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">Заказ с прямой оплатой &mdash; обсуждение условий</div>
             <div class="b-layout__txt b-layout__txt_padbot_10">
                 Пожалуйста, обсудите с Исполнителем все условия сотрудничества, согласуйте сроки и стоимость работы, а также порядок ее оплаты с возможной предоплатой. Как только Исполнитель подтвердит заказ (согласится на его выполнение), вы сможете начать сотрудничество.
             </div>
             <div class="b-buttons b-buttons_padbot_10">
                 <a class="b-button b-button_flat b-button_flat_green b-button_margright_10" 
                    data-url="<?=$order_url?>"
                    data-scrollto = "form-block"
                    href="javascript:void(0);"
                    onclick="if($('tservice-message-textarea')) $('tservice-message-textarea').focus();">
                     Обсудить условия
                 </a>
                 <a class="b-button b-button_flat b-button_flat_red" 
                    href="<?php echo tservices_helper::getOrderStatusUrl($order_id, 'cancel'); ?>"
                    data-duplicate="1">
                     Отменить заказ
                 </a>
                 <span class="b-buttons__txt b-button__txt_padbot_10_ipad">&#160; или &#160;</span>
                 <a class="b-button" 
                    data-url="<?=$order_url?>" 
                    data-popup="tu_edit_budjet" 
                    data-duplicate="2"
                    href="javascript:void(0);" 
                    onClick="yaCounter6051055.reachGoal('zakaz_change');$('tu_edit_budjet').removeClass('b-shadow_hide');">
                     <span class="b-button__txt_underline">
                        изменить срок, сумму или тип оплаты
                     </span>
                 </a>
             </div>

            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_fontsize_11 b-layout__txt_padleft_20">
                <span class="b-icon b-icon_sbr_oattent b-icon_margleft_-20"></span>
                <?=$no_reserve_warning?><br />
                Чтобы избежать претензий и финансовых рисков, рекомендуем сотрудничать 
                через "Безопасную сделку" (поменять тип оплаты в заказе на "Безопасная сделка").
            </div>
<?php 
            endif; 
?>
         </td>
         </tr>
     </table>
<?php

        }
        else
        {
//------------------------------------------------------------------------------
// Новый заказ.
// Статусы для исполнителя.
//------------------------------------------------------------------------------  
            $icon_action = $pay_type == TServiceOrderModel::PAYTYPE_RESERVE && $is_reserve_accepted ? 'pay' : 'edit';
?>
    <table class="b-layout__table b-layout__table_width_full">
        <tr class="b-layout__tr">
        <td class="b-layout__td b-layout__td_width_60 b-layout__td_ipad b-layout__td_width_null_ipad">
            <img class="b-user__pic"  alt="" src="/images/po/<?=$icon_prefix?>_<?=$icon_action?>.png"/>
        </td>
        <td class="b-layout__td b-layout__td_ipad ">  
<?php 
        if($pay_type == TServiceOrderModel::PAYTYPE_RESERVE):
//------------------------------------------------------------------------------
// Новый заказ по схеме резерва средств.
// Статусы для исполнителя - успешное подтвержнение и ожидание резерва средств
//------------------------------------------------------------------------------            
            if($is_reserve_accepted):
?>
             <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout_float_right b-layout__txt_float_none_iphone b-layout__txt_padbot_5">
                <span class="b-icon b-icon_sbr_oask"></span>
                <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/list/27457-baza-znanij-flru/?category=12682">
                    О Безопасных сделках
                </a>
             </div>
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                Заказ с оплатой через Безопасную сделку &mdash; резервирование суммы
            </div>
            <div class="b-layout__txt b-layout__txt_padbot_10">
                Вы подтвердили заказ и готовность его выполнить. Далее вам необходимо дождаться, пока Заказчик зарезервирует на сайте сумму оплаты, и только после этого начать выполнение работы по заказу.
            </div>
            <div class="b-buttons b-buttons_padbot_10">
                 <a class="b-button b-button_flat b-button_flat_green b-button_margright_10" 
                    data-url="<?=$order_url?>"
                    data-scrollto = "form-block"
                    href="javascript:void(0);"
                    onclick="if($('tservice-message-textarea')) $('tservice-message-textarea').focus();">
                     Обсудить заказ
                 </a>
            </div>
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_fontsize_11 b-layout__txt_padleft_20">
                <span class="b-icon b-icon_sbr_oattent b-icon_margleft_-20"></span>
                Пожалуйста, не начинайте выполнение работы, пока Заказчик не зарезервирует сумму оплаты. 
                Вы получите уведомление, как только сумма будет перечислена на сайт.
            </div>
            
<?php 
            else: 
//------------------------------------------------------------------------------
// Новый заказ по схеме резерва средств.
// Статусы для исполнителя - запрос на подтверждение сделки.
//------------------------------------------------------------------------------                
?> 
            <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout_float_right b-layout__txt_float_none_iphone b-layout__txt_padbot_5">
                <span class="b-icon b-icon_sbr_oask"></span>
                <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/list/27457-baza-znanij-flru/?category=12682">
                    О Безопасных сделках
                </a>
            </div>
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                Заказ с оплатой через Безопасную сделку &mdash; обсуждение условий 
            </div>            
            <div class="b-layout__txt b-layout__txt_padbot_20">
                Пожалуйста, обсудите с Заказчиком все условия сотрудничества, согласуйте сроки и стоимость работы. Как только вы подтвердите заказ (согласитесь на его выполнение), Заказчик сможет зарезервировать сумму оплаты и начнет сотрудничество с вами.
            </div>    
<?php 
            endif;
        else: 
//------------------------------------------------------------------------------
// Новый заказ по обычной схеме.
// Статус для исполнителя - подстверждение или отказ от заказа.
//------------------------------------------------------------------------------            
?>
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">Заказ с прямой оплатой &mdash; обсуждение условий</div>
            <div class="b-layout__txt b-layout__txt_padbot_20">
               Пожалуйста, обсудите с Заказчиком все условия сотрудничества, согласуйте сроки и стоимость работы, а также порядок ее оплаты с возможной предоплатой. Как только вы подтвердите заказ (согласитесь на его выполнение), Заказчик начнет сотрудничество с вами.
            </div>     
<?php 
        endif;
        
        if(!$is_reserve_accepted):
?>               
            <div class="b-buttons b-buttons_padbot_10">
                 <a class="b-button b-button_flat b-button_flat_green b-button_margright_10" 
                    data-url="<?=$order_url?>"
                    data-scrollto = "form-block"
                    href="javascript:void(0);"
                    onclick="if($('tservice-message-textarea')) $('tservice-message-textarea').focus();">
                     Обсудить условия
                 </a>
                <a href="javascript:void(0);" 
                   class="b-button b-button_flat b-button_flat_green" 
                   onclick="TServices_Order.showAcceptPopup(<?=$order_id?>);"
                   data-duplicate="1">
                    Подтвердить заказ
                </a>
                <span class="b-buttons__txt b-button__txt_padbot_10_ipad">&#160; или &#160;
                <a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_color_0f71c8" 
                   href="<?php echo tservices_helper::getOrderStatusUrl($order_id, 'decline'); ?>"
                   data-duplicate="2"
                   >
                    отказаться от него
                </a>
                </span>
            </div>
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_fontsize_11 b-layout__txt_padleft_20">
                <span class="b-icon b-icon_sbr_oattent b-icon_margleft_-20"></span>
                <?php if ($pay_type == TServiceOrderModel::PAYTYPE_RESERVE): ?>
                    Изменение суммы, срока или типа оплаты в заказе возможно только со стороны Заказчика.
                <?php else: ?>
                    <?=$no_reserve_warning?><br />
                    Чтобы избежать претензий и рисков неоплаты, рекомендуем сотрудничать 
                    через "Безопасную сделку" (Заказчику поменять тип оплаты в заказе на "Безопасная сделка").
                <?php endif; ?>
            </div>
                      
            <?php
                //рендерим попап для фрилансера
                $this->widget('TServiceOrderStatusPopup', array('data' => array(
                    'idx' => $order_id,
                    'title' => $order_title,
                    'price' => $order_price,
                    'tax' => $tax,
                    'days' => $order_days,
                    'pay_type' => $pay_type
                )));
            ?>
<?php
        endif;   
?>
        </td>
        </tr>
    </table>      
<?php

        }//ELSE
    }
    elseif($order_status == TServiceOrderModel::STATUS_CANCEL)
    {
        if($is_adm)
        {
//------------------------------------------------------------------------------
// Отмена заказа заказчиком.
// Статус для админа.
//------------------------------------------------------------------------------            
?>
        <table class="b-layout__table">
            <tr class="b-layout__tr">
                <td class="b-layout__td">
                    <div class="b-layout__txt">
                        Заказчик <?php echo $emp_fullname ?> отменил свой заказ.
                    </div>
                </td>                
            </tr> 
        </table>  
<?php
        }
        elseif($is_emp)
        {
//------------------------------------------------------------------------------
// Отмена заказа заказчиком.
// Статус для заказчика.
//------------------------------------------------------------------------------             
?>
        <table class="b-layout__table b-layout__table_width_full">
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_width_60 b-layout__td_ipad b-layout__td_width_null_ipad">
                    <img class="b-user__pic"  alt="" src="/images/po/<?=$icon_prefix?>_cancel.png"/>
                </td>
                <td class="b-layout__td b-layout__td_ipad">
                    <?php if($pay_type == TServiceOrderModel::PAYTYPE_RESERVE): ?>
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout_float_right b-layout__txt_float_none_iphone b-layout__txt_padbot_5">
                        <span class="b-icon b-icon_sbr_oask"></span>
                        <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/list/27457-baza-znanij-flru/?category=12682">
                            О Безопасных сделках
                        </a>
                     </div>
                    <?php endif; ?>
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        <?php if($pay_type == TServiceOrderModel::PAYTYPE_RESERVE): ?>
                            Заказ с оплатой через Безопасную сделку 
                        <?php else: ?>
                            Заказ с прямой оплатой
                        <?php endif; ?>
                        &mdash; заказ отменен
                    </div>
                    <div class="b-layout__txt b-layout__txt_padbot_10">
                        Сотрудничество по заказу отменено. При необходимости вы можете предложить Исполнителю новый заказ с новыми условиями сотрудничества.
                    </div>
                </td>
            </tr>
        </table>
<?php 

        }
        else
        {
//------------------------------------------------------------------------------
// Отмена заказа заказчиком.
// Статус для исполнителя.
//------------------------------------------------------------------------------                     
?>
        <table class="b-layout__table b-layout__table_width_full">
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_width_60 b-layout__td_ipad b-layout__td_width_null_ipad">
                    <img class="b-user__pic"  alt="" src="/images/po/<?=$icon_prefix?>_cancel.png"/>
                </td>
                <td class="b-layout__td b-layout__td_ipad">
                    <?php if($pay_type == TServiceOrderModel::PAYTYPE_RESERVE): ?>
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout_float_right b-layout__txt_float_none_iphone b-layout__txt_padbot_5">
                        <span class="b-icon b-icon_sbr_oask"></span>
                        <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/list/27457-baza-znanij-flru/?category=12682">
                            О Безопасных сделках
                        </a>
                     </div>
                    <?php endif; ?>
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        <?php if($pay_type == TServiceOrderModel::PAYTYPE_RESERVE): ?>
                            Заказ с оплатой через Безопасную сделку 
                        <?php else: ?>
                            Заказ с прямой оплатой
                        <?php endif; ?>
                        &mdash; заказ отменен
                    </div>
                    <div class="b-layout__txt b-layout__txt_padbot_20">
                        К сожалению, Заказчик отменил свой заказ. При необходимости 
                        вы можете выяснить у него причины отмены и обговорить возможность 
                        нового заказа с новыми условиями сотрудничества.
                    </div>
                </td>
            </tr>
        </table>
<?php 

        }//ELSE
    }
    elseif($order_status == TServiceOrderModel::STATUS_DECLINE)
    {
        if($is_adm)
        {
//------------------------------------------------------------------------------
// Отказ от заказа исполнителем.
// Статус для админа.
//------------------------------------------------------------------------------            
?>
        <table class="b-layout__table">
            <tr class="b-layout__tr">
                <td class="b-layout__td">                     
                    <div class="b-layout__txt">
                        Исполнитель <?php echo $frl_fullname ?> отказался от выполнения заказа.
                    </div>
                </td>                
            </tr> 
        </table>            
<?php
        }
        elseif($is_emp)
        {
//------------------------------------------------------------------------------
// Отказ от заказа исполнителем.
// Статус для заказчика.
//------------------------------------------------------------------------------            
?>
        <table class="b-layout__table b-layout__table_width_full">
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_width_60 b-layout__td_ipad b-layout__td_width_null_ipad">
                    <img class="b-user__pic"  alt="" src="/images/po/<?=$icon_prefix?>_cancel.png">
                </td>
                <td class="b-layout__td b-layout__td_ipad">
                    <?php if($pay_type == TServiceOrderModel::PAYTYPE_RESERVE): ?>
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout_float_right b-layout__txt_float_none_iphone b-layout__txt_padbot_5">
                        <span class="b-icon b-icon_sbr_oask"></span>
                        <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/list/27457-baza-znanij-flru/?category=12682">
                            О Безопасных сделках
                        </a>
                     </div>
                    <?php endif; ?>
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        <?php if($pay_type == TServiceOrderModel::PAYTYPE_RESERVE): ?>
                            Заказ с оплатой через Безопасную сделку 
                        <?php else: ?>
                            Заказ с прямой оплатой
                        <?php endif; ?>
                        &mdash; заказ отменен
                    </div>
                    <div class="b-layout__txt b-layout__txt_padbot_10">
                        К сожалению, Исполнитель отказался от выполнения вашего заказа. 
                        При необходимости вы можете выяснить у него причины отказа и предложить 
                        новый заказ с новыми условиями сотрудничества.
                    </div>
                </td>
            </tr>
        </table>
<?php 

        }
        else
        {
//------------------------------------------------------------------------------
// Отказ от заказа исполнителем.
// Статус для исполнителя.
//------------------------------------------------------------------------------            
?>
        <table class="b-layout__table b-layout__table_width_full">
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_width_60 b-layout__td_ipad b-layout__td_width_null_ipad">
                    <img class="b-user__pic"  alt="" src="/images/po/<?=$icon_prefix?>_cancel.png">
                </td>
                <td class="b-layout__td b-layout__td_ipad">
                    <?php if($pay_type == TServiceOrderModel::PAYTYPE_RESERVE): ?>
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout_float_right b-layout__txt_float_none_iphone b-layout__txt_padbot_5">
                        <span class="b-icon b-icon_sbr_oask"></span>
                        <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/list/27457-baza-znanij-flru/?category=12682">
                            О Безопасных сделках
                        </a>
                     </div>
                    <?php endif; ?>
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        <?php if($pay_type == TServiceOrderModel::PAYTYPE_RESERVE): ?>
                            Заказ с оплатой через Безопасную сделку 
                        <?php else: ?>
                            Заказ с прямой оплатой
                        <?php endif; ?>
                        &mdash; заказ отменен
                    </div>
                    <div class="b-layout__txt b-layout__txt_padbot_20">
                        Сотрудничество по заказу отменено. При необходимости вы можете 
                        обговорить с Заказчиком возможность нового заказа с новыми 
                        условиями сотрудничества.
                    </div>
                </td>
            </tr>
        </table>        
<?php
        }
    }
    elseif($order_status == TServiceOrderModel::STATUS_ACCEPT)
    {
        if($is_adm)
        {
//------------------------------------------------------------------------------
// Заказ в работе.
// Статус для админа.
//------------------------------------------------------------------------------            
?>
        <table class="b-layout__table">
            <tr class="b-layout__tr">
            <?php if (isset($reserve_data['arbitrage_id'])) { ?>
                <td class="b-layout__td">
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        <?=$reserve_data['arbitrage_is_emp']=='t'?'Заказчик':'Исполнитель'?> обратился в Арбитраж
                    </div>
                    <div class="b-layout__txt b-layout__txt_margbot_10 b-layout__txt_fontsize_11 b-layout__txt_italic b-layout__txt_padleft_15 b-layout__txt_margleft_15 b-layout_bordleft__666 b-layout__txt_color__666">
                        <?=$reserve_data['arbitrage_message']?>
                    </div>
                    
                    <?php
                        //рендерим форму для арбитра
                        $this->widget('ReservesArbitrageForm', array('data' => array(
                            'order_id' => $order_id,
                            'price' => $reserve_data['price']
                        )));
                    ?>
                </td>
            <?php } else { ?>
                <td class="b-layout__td">
                    <div class="b-layout__txt">
                        Исполнитель <?php echo $frl_fullname ?> подтвердил заказ и выполняет его.
                    </div>
                </td>
            <?php } ?>
            </tr>
        </table>            
<?php
        }
        elseif($is_emp)
        { 
            $icon_action = $reserve_data['arbitrage_id'] > 0 ? 'arbitrage' : 'run';
?>
        <table class="b-layout__table b-layout__table_width_full">
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_width_60 b-layout__td_ipad b-layout__td_width_null_ipad">
                    <img class="b-user__pic"  alt="" src="/images/po/<?=$icon_prefix?>_<?=$icon_action?>.png"/>
                </td>
                <td class="b-layout__td b-layout__td_ipad">
<?php
//------------------------------------------------------------------------------
// Заказ в работе по схеме резерва средств.
// Статус для заказчика.
//------------------------------------------------------------------------------
                if($pay_type == TServiceOrderModel::PAYTYPE_RESERVE):
                    
                    
                    //Есть заявка на арбитраж
                    if(isset($reserve_data['arbitrage_id'])):
?>
             <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout_float_right b-layout__txt_float_none_iphone b-layout__txt_padbot_5">
                <span class="b-icon b-icon_sbr_oask"></span>
                <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/list/27457-baza-znanij-flru/?category=12682">
                    О Безопасных сделках
                </a>
             </div>
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        Заказ с оплатой через Безопасную сделку &mdash; арбитраж
                    </div>
                    <div class="b-layout__txt b-layout__txt_padbot_15">
                        <?=$reserve_data['arbitrage_is_emp']=='t'?'Вы обратились':'Исполнитель обратился'?> в Арбитраж, передав заказ на рассмотрение Арбитру. В течение нескольких дней 
                        он изучит ситуацию и предпримет меры для урегулирования конфликта, возникшего между вами и 
                        исполнителем. По окончании арбитражного рассмотрения будет вынесено независимое решение 
                        (о выплате, возврате или разделении зарезервированной суммы). Ожидайте, пожалуйста.
                    </div>
                    <div class="b-layout__txt b-layout__txt_color_666 b-layout__txt_padbot_5 b-layout__txt_bold">
                        Причина обращения в Арбитраж:
                    </div>
                    <div class="b-layout__txt b-layout__txt_margbot_20 b-layout__txt_fontsize_11 b-layout__txt_italic b-layout__txt_padleft_15 b-layout__txt_margleft_15 b-layout_bordleft__666 b-layout__txt_color__666">
                        <?=$reserve_data['arbitrage_message']?>
                    </div>
                    
                    <div class="b-buttons b-buttons_padbot_10">
                         <a class="b-button b-button_flat b-button_flat_green b-button_margright_10" 
                            data-url="<?=$order_url?>"
                            data-scrollto = "form-block"
                            href="javascript:void(0);"
                            onclick="if($('tservice-message-textarea')) $('tservice-message-textarea').focus();">
                             Обсудить заказ
                         </a>
                    </div>          

<?php
                    else:
                        
                    
?>
             <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout_float_right b-layout__txt_float_none_iphone b-layout__txt_padbot_5">
                <span class="b-icon b-icon_sbr_oask"></span>
                <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/list/27457-baza-znanij-flru/?category=12682">
                    О Безопасных сделках
                </a>
             </div>
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        Заказ с оплатой через Безопасную сделку &mdash; выполнение работы
                    </div>            
                    <div class="b-layout__txt b-layout__txt_padbot_15">
                        Сумма зарезервирована, Исполнитель выполняет работу по заказу. В процессе сотрудничества вы можете продолжить обсуждение заказа, задания в нем и полученных от Исполнителя результатов. Как только работа будет завершена и принята вами, пожалуйста, завершите заказ и оставьте отзыв о сотрудничестве.
                    </div> 
                    <div class="b-buttons b-buttons_padbot_15">
                         <a class="b-button b-button_flat b-button_flat_green b-button_margright_10" 
                            data-url="<?=$order_url?>"
                            data-scrollto = "form-block"
                            href="javascript:void(0);"
                            onclick="if($('tservice-message-textarea')) $('tservice-message-textarea').focus();">
                             Обсудить заказ
                         </a>
                        <a class="b-button b-button_flat b-button_flat_green" 
                           href="javascript:void(0);"
                           data-popup="<?=TServiceOrderFeedback::getPopupId($order_id)?>"
                           data-duplicate="1">Завершить сотрудничество</a>
                        <span class="b-buttons__txt b-button__txt_padbot_10_ipad">&#160; или &#160;</span>
                        <a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_color_0f71c8" 
                           href="javascript:void(0);" 
                           data-url="<?=$order_url?>"
                           data-popup="<?=ReservesArbitragePopup::getPopupId($order_id)?>"
                           data-duplicate="2">
                            обратиться в Арбитраж
                        </a>
                    </div>
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_fontsize_11 b-layout__txt_padleft_20"><span class="b-icon b-icon_top_1 b-icon_sbr_oattent b-icon_margleft_-20"></span>Если в процессе сотрудничества у вас возникнут проблемы с Исполнителем, рекомендуем обратиться в Арбитраж и урегулировать конфликт с помощью Арбитра.</div>
                    <?php $this->widget('ReservesArbitragePopup', array('data' => array(
                        'idx' => $order_id
                    ))) ?>
<?php
                    endif;
                else:
//------------------------------------------------------------------------------
// Заказ в работе по обычной схеме. 
// В любое время заказчик может завершить работу и написать отзыв.
//------------------------------------------------------------------------------                    
?>
                    <?php /*
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_right">
                        <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/topic/483824-vyipolnenie-rabotyi-sotrudnichestvo-i-perepiska/">
                            Подробнее о процессе сотрудничества
                        </a>
                    </div> 
						  */ ?>                   
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        Заказ с прямой оплатой &mdash; выполнение работы
                    </div>                      
                    <div class="b-layout__txt b-layout__txt_padbot_15">
Исполнитель подтвердил заказ и приступил к его выполнению. В процессе сотрудничества вы можете продолжить обсуждение заказа, задания в нем и полученных от Исполнителя результатов. Как только работа будет завершена и принята вами, пожалуйста, завершите заказ и оставьте отзыв о сотрудничестве. 
                    </div>
                    <div class="b-buttons b-buttons_padbot_15">
                       <a class="b-button b-button_flat b-button_flat_green b-button_margright_10" 
                          data-url="<?=$order_url?>"
                          data-scrollto = "form-block"
                          href="javascript:void(0);"
                          onclick="if($('tservice-message-textarea')) $('tservice-message-textarea').focus();">
                           Обсудить заказ
                       </a>
                        <a class="b-button b-button_flat b-button_flat_green" 
                           href="javascript:void(0);" 
                           data-popup="<?=TServiceOrderFeedback::getPopupId($order_id)?>"
                           data-duplicate="1">
                            Завершить сотрудничество
                        </a>
                    </div> 
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_fontsize_11 b-layout__txt_padleft_20">
                        <span class="b-icon b-icon_top_1 b-icon_sbr_oattent b-icon_margleft_-20"></span>
                        <?=$no_reserve_warning?>
                    </div>
                    
                    
<?php
                endif;

                $this->widget('TServiceOrderFeedback', array('data' => array(
                    'idx' => $order_id,
                    'hash' => $hash,
                    'pay_type' => $pay_type,
                    'rating' => $frl_rating,
                    'is_close' => false
                )));
?>
                </td>
            </tr>
        </table>
<?php 

        }
        else
        {
  
            $icon_action = $reserve_data['arbitrage_id'] > 0 ? 'arbitrage' : 'run';
?> 
        <table class="b-layout__table b-layout__table_width_full">
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_width_60 b-layout__td_ipad b-layout__td_width_null_ipad">
                    <img class="b-user__pic"  alt="" src="/images/po/<?=$icon_prefix?>_<?=$icon_action?>.png"/>
                </td>
                <td class="b-layout__td b-layout__td_ipad">
<?php
//------------------------------------------------------------------------------
// Заказ в работе по схеме резерва средств.
// Статус для исполнителя.
//------------------------------------------------------------------------------
                if($pay_type == TServiceOrderModel::PAYTYPE_RESERVE):
                    
                    //Есть заявка на арбитраж
                    if(isset($reserve_data['arbitrage_id'])):
?>
             <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout_float_right b-layout__txt_float_none_iphone b-layout__txt_padbot_5">
                <span class="b-icon b-icon_sbr_oask"></span>
                <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/list/27457-baza-znanij-flru/?category=12682">
                    О Безопасных сделках
                </a>
             </div>
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        Заказ с оплатой через Безопасную сделку &mdash; арбитраж
                    </div>
                    <div class="b-layout__txt b-layout__txt_padbot_15">
                        <?=$reserve_data['arbitrage_is_emp']=='t'?'Заказчик обратился':'Вы обратились'?> в Арбитраж, передав заказ на рассмотрение Арбитру. 
                        В течение нескольких дней он изучит ситуацию и предпримет меры для урегулирования конфликта, 
                        возникшего между вами и заказчиком. По окончании арбитражного рассмотрения будет вынесено независимое решение 
                        (о выплате, возврате или разделении зарезервированной суммы). Ожидайте, пожалуйста.
                    </div>
                    <div class="b-layout__txt b-layout__txt_color_666 b-layout__txt_padbot_5 b-layout__txt_bold">
                        Причина обращения в Арбитраж:
                    </div>
                    <div class="b-layout__txt b-layout__txt_margbot_20 b-layout__txt_fontsize_11 b-layout__txt_italic b-layout__txt_padleft_15 b-layout__txt_margleft_15 b-layout_bordleft__666 b-layout__txt_color__666">
                        <?=$reserve_data['arbitrage_message']?>
                    </div>
                    <div class="b-buttons b-buttons_padbot_10">
                         <a class="b-button b-button_flat b-button_flat_green b-button_margright_10" 
                            data-url="<?=$order_url?>"
                            data-scrollto = "form-block"
                            href="javascript:void(0);"
                            onclick="if($('tservice-message-textarea')) $('tservice-message-textarea').focus();">
                             Обсудить заказ
                         </a>
                    </div>          
<?php
                    else:
?> 
             <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout_float_right b-layout__txt_float_none_iphone b-layout__txt_padbot_5">
                <span class="b-icon b-icon_sbr_oask"></span>
                <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/list/27457-baza-znanij-flru/?category=12682">
                    О Безопасных сделках
                </a>
             </div>
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        Заказ с оплатой через Безопасную сделку &mdash; выполнение работы
                    </div>            
                    <div class="b-layout__txt b-layout__txt_padbot_15">
                        Сумма зарезервирована, Заказчик ожидает выполнения работы по заказу. В процессе сотрудничества вы можете продолжить обсуждение заказа, задания в нем и передаваемых вами результатов. Как только работа будет завершена, пожалуйста, сообщите об этом Заказчику, чтобы он мог завершить заказ с перечислением вам зарезервированной суммы оплаты.
                    </div> 
                    <div class="b-buttons b-buttons_padbot_15">
                         <a class="b-button b-button_flat b-button_flat_green b-button_margright_10" 
                            data-url="<?=$order_url?>"
                            data-scrollto = "form-block"
                            href="javascript:void(0);"
                            onclick="if($('tservice-message-textarea')) $('tservice-message-textarea').focus();">
                             Обсудить заказ
                         </a>
                        <a class="b-button b-button_flat b-button_flat_green" 
                           href="<?php echo tservices_helper::getOrderStatusUrl($order_id, 'done'); ?>"
                           data-duplicate="1">
                            Уведомить о выполненной работе
                        </a>
                        <span class="b-buttons__txt b-button__txt_padbot_10_ipad">&#160; или &#160;</span>
                        <a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_color_0f71c8" 
                           href="javascript:void(0);" 
                           data-url="<?=$order_url?>"
                           data-popup="<?=ReservesArbitragePopup::getPopupId($order_id)?>"
                           data-duplicate="2">
                            обратиться в Арбитраж
                        </a>                      
                    </div>
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padleft_20">
                        <span class="b-icon b-icon_sbr_oattent b-icon_top_1 b-icon_margleft_-20"></span>Если в процессе сотрудничества у вас возникнут проблемы с Заказчиком, рекомендуем обратиться в Арбитраж и урегулировать конфликт с помощью Арбитра.
                    </div>
                    <?php $this->widget('ReservesArbitragePopup', array('data' => array(
                        'idx' => $order_id
                    ))) ?>
<?php
                    endif;
                    
                else:
//------------------------------------------------------------------------------
// Заказ в работе по обычной схеме. 
// В любое время исполнитель может уведомить заказчика о готовности работы.
//------------------------------------------------------------------------------
?>
                    <?php /*
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_right">
                        <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/topic/483824-vyipolnenie-rabotyi-sotrudnichestvo-i-perepiska/">
                            Подробнее о процессе сотрудничества
                        </a>
                    </div>
						  */ ?>
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        Заказ с прямой оплатой &mdash; выполнение работы
                    </div>                    
                    <div class="b-layout__txt b-layout__txt_padbot_15">
                        Вы подтвердили заказ, тем самым приступив к его выполнению. В процессе сотрудничества вы можете продолжить обсуждение заказа, задания в нем и передаваемых вами результатов. Как только работа будет завершена, пожалуйста, сообщите об этом Заказчику, чтобы он мог завершить заказ и оставить отзыв о сотрудничестве. 
                    </div>
                    <div class="b-buttons b-buttons_padbot_15">
                         <a class="b-button b-button_flat b-button_flat_green b-button_margright_10" 
                            data-url="<?=$order_url?>"
                            data-scrollto = "form-block"
                            href="javascript:void(0);"
                            onclick="if($('tservice-message-textarea')) $('tservice-message-textarea').focus();">
                             Обсудить заказ
                         </a>
                        <a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_color_0f71c8" 
                           href="<?php echo tservices_helper::getOrderStatusUrl($order_id, 'done'); ?>"
                           data-duplicate="1">
                            Уведомить о выполненной работе
                        </a>
                    </div> 
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padleft_20">
                        <span class="b-icon b-icon_sbr_oattent b-icon_top_1 b-icon_margleft_-20"></span>
                        <?=$no_reserve_warning?>
                    </div>
<?php

                endif;
                
?>
                </td>
            </tr>  
        </table>
<?php   

        }
    }
    elseif($order_status == TServiceOrderModel::STATUS_FIX)
    {
//------------------------------------------------------------------------------
// Заказчик отправил на доработку те продолжил сотрудничество.
// Статус для админа.
//------------------------------------------------------------------------------        
        if($is_adm)
        {  
?>
        <table class="b-layout__table">
            <tr class="b-layout__tr">
<?php 
            if (isset($reserve_data['arbitrage_id'])): 
?>
                <td class="b-layout__td">
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        <?=$reserve_data['arbitrage_is_emp']=='t'?'Заказчик':'Исполнитель'?> обратился в Арбитраж
                    </div>
                    <div class="b-layout__txt b-layout__txt_margbot_10 b-layout__txt_fontsize_11 b-layout__txt_italic b-layout__txt_padleft_15 b-layout__txt_margleft_15 b-layout_bordleft__666 b-layout__txt_color__666">
                        <?=$reserve_data['arbitrage_message']?>
                    </div>
                    
                    <?php
                        //рендерим форму для арбитра
                        $this->widget('ReservesArbitrageForm', array('data' => array(
                            'order_id' => $order_id,
                            'price' => $reserve_data['price']
                        )));
                    ?>
                </td>
<?php 
            else: 
?>                
                <td class="b-layout__td">
                    Заказчик <?php echo $emp_fullname ?> продолжил сотрудничество.
                </td>
<?php 
            endif;
?>
            </tr>
        </table>
<?php
        }
        elseif($is_emp)
        {
//------------------------------------------------------------------------------
// Заказчик отправил на доработку те продолжил сотрудничество.
// Статус для заказчика.
//------------------------------------------------------------------------------            

            $icon_action = $reserve_data['arbitrage_id'] > 0 ? 'arbitrage' : 'run';
?>
        <table class="b-layout__table b-layout__table_width_full">
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_width_60 b-layout__td_ipad b-layout__td_width_null_ipad">
                    <img class="b-user__pic"  alt="" src="/images/po/<?=$icon_prefix?>_<?=$icon_action?>.png"/>
                </td>
                <td class="b-layout__td b-layout__td_ipad">
<?php
//------------------------------------------------------------------------------
// Заказчик отправил на доработку те продолжил сотрудничество по схеме резерва средств.
// Статус для заказчика.
//------------------------------------------------------------------------------
                if($pay_type == TServiceOrderModel::PAYTYPE_RESERVE):
                    //Есть заявка на арбитраж
                    if(isset($reserve_data['arbitrage_id'])):
?>
             <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout_float_right b-layout__txt_float_none_iphone b-layout__txt_padbot_5">
                <span class="b-icon b-icon_sbr_oask"></span>
                <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/list/27457-baza-znanij-flru/?category=12682">
                    О Безопасных сделках
                </a>
             </div>
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        Заказ с оплатой через Безопасную сделку &mdash; арбитраж
                    </div>
                    <div class="b-layout__txt b-layout__txt_padbot_15">
                        <?=$reserve_data['arbitrage_is_emp']=='t'?'Вы обратились':'Исполнитель обратился'?> в Арбитраж, передав заказ на рассмотрение Арбитру. В течение нескольких дней 
                        он изучит ситуацию и предпримет меры для урегулирования конфликта, возникшего между вами и 
                        исполнителем. По окончании арбитражного рассмотрения будет вынесено независимое решение 
                        (о выплате, возврате или разделении зарезервированной суммы). Ожидайте, пожалуйста.
                    </div>
                    <div class="b-layout__txt b-layout__txt_color_666 b-layout__txt_padbot_5 b-layout__txt_bold">
                        Причина обращения в Арбитраж:
                    </div>
                    <div class="b-layout__txt b-layout__txt_margbot_20 b-layout__txt_fontsize_11 b-layout__txt_italic b-layout__txt_padleft_15 b-layout__txt_margleft_15 b-layout_bordleft__666 b-layout__txt_color__666">
                        <?=$reserve_data['arbitrage_message']?>
                    </div>
                    
                    <div class="b-buttons b-buttons_padbot_10">
                         <a class="b-button b-button_flat b-button_flat_green b-button_margright_10" 
                            data-url="<?=$order_url?>"
                            data-scrollto = "form-block"
                            href="javascript:void(0);"
                            onclick="if($('tservice-message-textarea')) $('tservice-message-textarea').focus();">
                             Обсудить заказ
                         </a>
                    </div>          

<?php             
                    else:
?>
             <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout_float_right b-layout__txt_float_none_iphone b-layout__txt_padbot_5">
                <span class="b-icon b-icon_sbr_oask"></span>
                <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/list/27457-baza-znanij-flru/?category=12682">
                    О Безопасных сделках
                </a>
             </div>
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        Заказ с оплатой через Безопасную сделку &mdash; выполнение работы
                    </div>            
                    <div class="b-layout__txt b-layout__txt_padbot_15">
                        Заказ возвращен в работу, вы можете продолжить сотрудничество с Исполнителем. Как только работа будет полностью завершена и принята вами, не забудьте завершить заказ и оставить отзыв о сотрудничестве.
                    </div> 
                    <div class="b-buttons b-buttons_padbot_15">
                        <a class="b-button b-button_flat b-button_flat_green" 
                           href="javascript:void(0);"
                           data-popup="<?=TServiceOrderFeedback::getPopupId($order_id)?>">
                            Завершить сотрудничество
                        </a>
                        <span class="b-buttons__txt b-button__txt_padbot_10_ipad">&#160; или &#160;</span>
                        <a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_color_0f71c8" 
                           href="javascript:void(0);" 
                           data-url="<?=$order_url?>"
                           data-duplicate="1"
                           data-popup="<?=ReservesArbitragePopup::getPopupId($order_id)?>">
                            обратиться в Арбитраж
                        </a>                        
                    </div>
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padleft_20">
                        <span class="b-icon b-icon_sbr_oattent b-icon_top_1 b-icon_margleft_-20"></span>Если в процессе сотрудничества у вас возникнут проблемы с Исполнителем, рекомендуем обратиться в Арбитраж и урегулировать конфликт с помощью Арбитра.
                    </div>                    
                    <?php $this->widget('ReservesArbitragePopup', array('data' => array(
                        'idx' => $order_id
                    ))) ?>
<?php
                    endif;
                else:
//------------------------------------------------------------------------------
// Заказчик отправил на доработку те продолжил сотрудничество по обычной схеме.
// Статус для заказчика.
//------------------------------------------------------------------------------                    
?>
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        Заказ с прямой оплатой &mdash; выполнение работы
                    </div>                      
                    <div class="b-layout__txt b-layout__txt_padbot_15">
                        Заказ возвращен в работу. Пожалуйста, обсудите с Исполнителем причины возврата и необходимые правки. Как только работа будет полностью завершена и принята вами, не забудьте завершить заказ, оплатить услуги Исполнителя и оставить отзыв о сотрудничестве.
                    </div>
                    <div class="b-buttons b-buttons_padbot_15">
                        <a class="b-button b-button_flat b-button_flat_green" 
                           href="javascript:void(0);" 
                           data-popup="<?=TServiceOrderFeedback::getPopupId($order_id)?>">
                            Завершить сотрудничество
                        </a>
                    </div>
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padleft_20">
                        <span class="b-icon b-icon_sbr_oattent b-icon_top_1 b-icon_margleft_-20"></span><?php echo $no_reserve_warning ?>
                    </div>                    
<?php
                endif;

                $this->widget('TServiceOrderFeedback', array('data' => array(
                    'idx' => $order_id,
                    'hash' => $hash,
                    'pay_type' => $pay_type,
                    'rating' => $frl_rating,
                    'is_close' => false
                )));
?>                    
                </td>
            </tr>
        </table>
<?php
        }
        else
        {
//------------------------------------------------------------------------------
// Заказчик отправил на доработку те продолжил сотрудничество.
// Статус для исполнителя.
//------------------------------------------------------------------------------            
            $icon_action = $reserve_data['arbitrage_id'] > 0 ? 'arbitrage' : 'run';
?>
        <table class="b-layout__table b-layout__table_width_full">
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_width_60 b-layout__td_ipad b-layout__td_width_null_ipad">
                    <img class="b-user__pic"  alt="" src="/images/po/<?=$icon_prefix?>_<?=$icon_action?>.png"/>
                </td>
                <td class="b-layout__td b-layout__td_ipad">
<?php
//------------------------------------------------------------------------------
// Заказчик отправил на доработку те продолжил сотрудничество по схеме резерва средств.
// Статус для исполнителя.
//------------------------------------------------------------------------------
                if($pay_type == TServiceOrderModel::PAYTYPE_RESERVE):
                    //Есть заявка на арбитраж
                    if(isset($reserve_data['arbitrage_id'])):
?>
             <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout_float_right b-layout__txt_float_none_iphone b-layout__txt_padbot_5">
                <span class="b-icon b-icon_sbr_oask"></span>
                <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/list/27457-baza-znanij-flru/?category=12682">
                    О Безопасных сделках
                </a>
             </div>
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        Заказ с оплатой через Безопасную сделку &mdash; арбитраж
                    </div>
                    <div class="b-layout__txt b-layout__txt_padbot_15">
                        <?=$reserve_data['arbitrage_is_emp']=='t'?'Заказчик обратился':'Вы обратились'?> в Арбитраж, передав заказ на рассмотрение Арбитру. 
                        В течение нескольких дней он изучит ситуацию и предпримет меры для урегулирования конфликта, 
                        возникшего между вами и заказчиком. По окончании арбитражного рассмотрения будет вынесено независимое решение 
                        (о выплате, возврате или разделении зарезервированной суммы). Ожидайте, пожалуйста.
                    </div>
                    <div class="b-layout__txt b-layout__txt_color_666 b-layout__txt_padbot_5 b-layout__txt_bold">
                        Причина обращения в Арбитраж:
                    </div>
                    <div class="b-layout__txt b-layout__txt_margbot_20 b-layout__txt_fontsize_11 b-layout__txt_italic b-layout__txt_padleft_15 b-layout__txt_margleft_15 b-layout_bordleft__666 b-layout__txt_color__666">
                        <?=$reserve_data['arbitrage_message']?>
                    </div>
                    <div class="b-buttons b-buttons_padbot_10">
                         <a class="b-button b-button_flat b-button_flat_green b-button_margright_10" 
                            data-url="<?=$order_url?>"
                            data-scrollto = "form-block"
                            href="javascript:void(0);"
                            onclick="if($('tservice-message-textarea')) $('tservice-message-textarea').focus();">
                             Обсудить заказ
                         </a>
                    </div>          
<?php
                    else:
?>
             <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout_float_right b-layout__txt_float_none_iphone b-layout__txt_padbot_5">
                <span class="b-icon b-icon_sbr_oask"></span>
                <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/list/27457-baza-znanij-flru/?category=12682">
                    О Безопасных сделках
                </a>
             </div>
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        Заказ с оплатой через Безопасную сделку &mdash; выполнение работы
                    </div>            
                    <div class="b-layout__txt b-layout__txt_padbot_15">
                        Заказ возвращен в работу, вы можете продолжить сотрудничество с Заказчиком. Как только работа будет полностью завершена, не забудьте сообщить об этом Закачику, чтобы он мог завершить заказ с перечислением вам зарезервированной суммы оплаты.
                    </div> 
                    <div class="b-buttons b-buttons_padbot_15">
                        <a class="b-button b-button_flat b-button_flat_green" 
                           href="<?php echo tservices_helper::getOrderStatusUrl($order_id, 'done'); ?>"
                           data-duplicate="1">
                            Уведомить о выполненной работе
                        </a>
                        <span class="b-buttons__txt b-button__txt_padbot_10_ipad">&#160; или &#160;</span>
                        <a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_color_0f71c8" 
                           href="javascript:void(0);" 
                           data-url="<?=$order_url?>"
                           data-duplicate="2"
                           data-popup="<?=ReservesArbitragePopup::getPopupId($order_id)?>">
                            обратиться в Арбитраж
                        </a>                       
                    </div>
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padleft_20">
                        <span class="b-icon b-icon_sbr_oattent b-icon_top_1 b-icon_margleft_-20"></span>Если в процессе сотрудничества у вас возникнут проблемы с Заказчиком, рекомендуем обратиться в Арбитраж и урегулировать конфликт с помощью Арбитра.
                    </div>    
<?php 

$this->widget('ReservesArbitragePopup', array('data' => array(
    'idx' => $order_id
))); 
                  endif;
                  
                else:
//------------------------------------------------------------------------------
// Заказчик отправил на доработку те продолжил сотрудничество по обычной схеме. 
// В любое время исполнитель может уведомить заказчика о готовности работы.
//------------------------------------------------------------------------------
?>                    
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        Заказ с прямой оплатой &mdash; выполнение работы
                    </div>                    
                    <div class="b-layout__txt b-layout__txt_padbot_15">
                        Заказчик вернул заказ в работу. Пожалуйста, обсудите с Заказчиком причины возврата и внесите необходимые правки. Как только работа будет полностью завершена, не забудьте сообщить об этом Заказчику, чтобы он мог завершить заказ и оплатить ваши услуги.
                    </div>
                    <div class="b-buttons b-buttons_padbot_15">
                        <a class="b-button b-button_flat b-button_flat_green" 
                           href="<?php echo tservices_helper::getOrderStatusUrl($order_id, 'done'); ?>">
                            Уведомить о выполненной работе
                        </a>
                    </div> 
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padleft_20">
                        <span class="b-icon b-icon_sbr_oattent b-icon_top_1 b-icon_margleft_-20"></span><?php echo $no_reserve_warning ?>
                    </div>    
<?php
                endif;
?>
                </td>
            </tr>
        </table>
<?php
        }
    }
    elseif($order_status == TServiceOrderModel::STATUS_EMPCLOSE)
    {
        
        /**
         * Нужно переработать эту смесь всевозможных статусов при арбитраже
         * обьединение НЕ работает очень много различий и нагромождений!!!
         */
        
        
        if (isset($reserve_data['arbitrage_price'])) {
            $pricePay = $reserve->getArbitragePricePay();
            $priceBack = $reserve->getArbitragePriceBack();
            $pricePayFormatted = tservices_helper::cost_format($pricePay,true, false, false);
            $priceBackFormatted = tservices_helper::cost_format($priceBack,true, false, false);
            $icon_action = $reserve->isClosed() ? 'close' : 'pay';
?>
        <table class="b-layout__table b-layout__table_width_full">
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_width_60 b-layout__td_ipad b-layout__td_width_null_ipad">
                    <img src="/images/po/bs_<?=$icon_action?>.png" alt="" class="b-user__pic">
                </td>
                <td class="b-layout__td b-layout__td_ipad">
             <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout_float_right b-layout__txt_float_none_iphone b-layout__txt_padbot_5">
                <span class="b-icon b-icon_sbr_oask"></span>
                <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/list/27457-baza-znanij-flru/?category=12682">
                    О Безопасных сделках
                </a>
             </div>
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        Заказ с оплатой через Безопасную сделку &mdash; 
                        <?php if($reserve->isClosed()): ?>
                            заказ закрыт
                        <?php else: ?>
                            выплата сумм
                        <?php endif; ?>
                    </div>
                    <?php if(!$reserve->isClosed()): ?>
                    <div class="b-layout__txt b-layout__txt_padbot_10">
                        После рассмотрения ситуации и принятых мер по урегулированию конфликта, возникшего между Заказчиком и Исполнителем, Арбитром вынесено решение:<br>
                    </div>
                    <?php endif; ?>
                    <div class="b-layout__txt b-layout__txt_padbot_10">
                        
                        <?php if($pricePay) { ?>
                            К выплате исполнителю <?=$pricePayFormatted?> &mdash; 
                            <b>
                                <?php if($reserve->isStatusPayPayed()): ?>сумма выплачена
                                <?php elseif($reserve->isStatusPayError()): ?>выплата временно приостановлена
                                <?php else: ?>выплата суммы<?php endif; ?>
                            </b><br/>
                        <?php } ?>
                            
                        <?php if($priceBack) { ?>
                            К возврату заказчику <?=$priceBackFormatted?> &mdash; 
                            <b>
                                <?php if($reserve->isStatusBackPayed()): ?>сумма выплачена
                                <?php elseif($reserve->isStatusBackError()): ?>выплата временно приостановлена
                                <?php else: ?>выплата суммы<?php endif; ?>
                            </b>
                            <?php if($is_emp): ?>
                            
                                <?php if($reserve->isStatusBackError()): ?>
                            
                            <?php if(false): ?>
                                    <?php if(!$reserve->isFrlPhis()): ?>
                            <div class="b-layout__txt">
                                К сожалению, при выплате суммы возникла ошибка. 
                                Пожалуйста, проверьте, правильные ли реквизиты 
                                указаны на странице Финансы, 
                                и повторите запрос на выплату.
                            </div>
                                    <?php endif; ?>
                            <?php endif; ?>
                            
                                <?php elseif(!$reserve->isStatusBackPayed()): ?>
                            <div class="b-layout__txt">
                                Пожалуйста, ожидайте. В ближайшее время сумма возврата 
                                будет перечислена вам на тот же кошелек/счет, 
                                с которого она была зарезервирована.
                            </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php } ?>
                    </div>
                    
                    <?php if($is_emp && $reserve->isStatusBackError()): ?>
                        <?php if($reserve->getReasonPayback()): ?>
                        <div class="b-layout__txt b-layout__txt_padbot_10">
                            <strong>Возврат приостановлен по причине: </strong>
                            <?=$reserve->getReasonPayback()?>
                        </div>
                        <?php endif; ?>
                    <?php elseif(!$is_emp && $reserve->isStatusPayError()): ?>
                        <?php if($reserve->getReasonPayout()): ?>
                        <div class="b-layout__txt b-layout__txt_padbot_10">
                            <strong>Выплата приостановлена по причине: </strong>
                            <?=$reserve->getReasonPayout()?>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php if(!$is_adm && $is_emp) { ?>
                            <?php if (!$is_emp_feedback && $is_allow_feedback) { ?>
                            <div class="b-layout__txt b-layout__txt_padbot_15">
                                <?php if(!$reserve->isSubStatusError()): ?>
                                Процесс выполнения заказа завершен, спасибо за сотрудничество!<br/>
                                <?php endif; ?>
                                Не забудьте до <?=$date_feedback?> оставить отзыв.
                            </div>
                            <div class="b-buttons b-buttons_padbot_15">
                                <a class="b-button b-button_flat b-button_flat_green" 
                                   href="javascript:void(0);" 
                                   data-popup="<?=TServiceOrderFeedback::getPopupId($order_id)?>">
                                    Оставить отзыв
                                </a>  
                                <?php if($is_frl_feedback): ?>
                                <a class="b-button b-buttons_padleft_20" 
                                   href="<?=tservices_helper::getFeedbackUrl($frl_feedback_id)?>">
                                    Отзыв исполнителя
                                </a>
                                <?php endif; ?>                                
                            </div>
<?php
                    $this->widget('TServiceOrderFeedback', array('data' => array(
                        'idx' => $order_id,
                        'hash' => $hash,
                        'pay_type' => $pay_type,
                        'rating' => $frl_rating,
                        'is_close' => true
                    ))); 
?>                       
                            <?php } else { ?>
                    
                            <?php if(!$reserve->isSubStatusError()): ?>
                            <div class="b-layout__txt b-layout__txt_padbot_15">
                                Процесс выполнения заказа завершен, спасибо за сотрудничество!
                            </div>
                            <?php endif; ?>
                    
                            <div class="b-buttons b-buttons_padbot_15">
                                <?php if($is_emp_feedback): ?>
                                <a class="b-button" 
                                   href="<?=tservices_helper::getFeedbackUrl($emp_feedback_id, $freelancer['login'])?>">
                                    Ваш отзыв исполнителю
                                </a>                        
                                <?php endif; ?>
                                <?php if($is_frl_feedback): ?>
                                <a class="b-button <?php if($is_emp_feedback): ?>b-buttons_padleft_20<?php endif; ?>" 
                                   href="<?=tservices_helper::getFeedbackUrl($frl_feedback_id)?>">
                                    Отзыв исполнителя
                                </a>
                                <?php endif; ?>
                            </div>                    
                            <?php } ?>
<?php 
                        } 
                        elseif(!$is_adm && !$is_emp) 
                        { 

                            if($reserve->isStatusPayAllowPayout()) 
                            { 
                                $fn_url = sprintf("/users/%s/setup/finance/", $freelancer['login']);
?>
                                <?php if($reserve->isFrlAllowFinance()): ?>
                            <div class="b-layout__txt b-layout__txt_padbot_15">
                                Подтвердите, пожалуйста, выплату суммы, чтобы мы могли перечислить ее удобным вам способом.
                            </div>
                            <div class="b-buttons b-buttons_padbot_15">
                                <a class="b-button b-button_flat b-button_flat_green" 
                                   data-popup="<?= ReservesPayoutPopup::getPopupId($order_id) ?>" 
                                   data-url="<?= $order_url ?>" 
                                   href="javascript:void(0);">
                                    Подтвердить выплату суммы
                                </a>
                            </div>
<?php
                        //рендерим попап для подтверждения оплаты
                        if(!$is_list):
                            
                            $ndfl = null;
                        
                            if ($reserve->getArbitrageNDFL()) 
                            {
                                $ndfl = tservices_helper::cost_format($reserve->getArbitrageNDFL(),true, false, false);
                            }
                            
                            $this->widget('ReservesPayoutPopup', array(
                                'price' => $reserve->getArbitragePricePay(),
                                'options' => array(
                                    'idx' => $order_id,
                                    'hash' => $hash,
                                    'is_feedback' => $is_frl_feedback,
                                    'is_allow_feedback' => $is_allow_feedback,
                                    'price' => $pricePayFormatted,
                                    'price_ndfl' =>$ndfl,
                                    'price_all' => tservices_helper::cost_format($reserve->getArbitragePriceWithOutNDFL(),true, false, false),
                                    'fn_url' => ($reserve->isAllowEditFinance($reserve_data['frl_id'], false))?$fn_url:false
                            )));
                        endif;
?>
                            <?php elseif(!$reserve->isFrlFinanceValid()): 
                            ?>
                                         
                                <?php if($reserve->isFrlFinanceFailStatus()): ?>
                            <div class="b-layout__txt b-layout__txt_padbot_10 b-fon_overflow_hidden">
                                К сожалению, на странице Финансы указаны некорректные 
                                данные<?php if($reason = $reserve->getFrlFinanceBlockedReason()): ?>: <?=$reason?>.<?php else: ?>.<? endif; ?>
                                <br/>Для перехода к процессу резервирования укажите, пожалуйста, корректные данные.
                            </div>
                                <?php endif; ?>
                            <div class="b-buttons b-buttons_padbot_15">
                                <a href="<?=$fn_url?>" class="b-button b-button_flat b-button_flat_green">
                                    Перейти на страницу "Финансы"
                                </a> 
                            </div>
                            <?php else: ?>
                            <div class="b-buttons b-buttons_padbot_15">
                                <a href="javascript:void(0)" 
                                   class="b-button b-button_flat b-button_flat_green b-button_disabled">
                                    Проверка данных модератором 
                                </a>     
                            </div>
                            <?php endif; ?>
                    
<?php 
                    } else { 
?>
                            <?php if (!$is_frl_feedback && $is_allow_feedback) { ?>
                            <div class="b-layout__txt b-layout__txt_padbot_15">
                                Вы можете оставить отзыв заказчику. Спасибо за сотрудничество!
                            </div>
                            <div class="b-buttons b-buttons_padbot_15">
                                <a class="b-button b-button_flat b-button_flat_green" 
                                   href="javascript:void(0);" 
                                   data-popup="<?=TServiceOrderFeedback::getPopupId($order_id)?>">
                                    Оставить отзыв
                                </a>
                                <?php if($is_emp_feedback): ?>
                                <a class="b-button b-buttons_padleft_20" 
                                   href="<?=tservices_helper::getFeedbackUrl($emp_feedback_id)?>">
                                    Отзыв заказчика
                                </a>
                                <?php endif; ?>                                
                            </div>            
<?php
                    $this->widget('TServiceOrderFeedback', array('data' => array(
                        'idx' => $order_id,
                        'hash' => $hash,
                        'pay_type' => $pay_type,
                        'rating' => $emp_rating,
                        'is_close' => true
                    )));
?>                    
                            <?php } else { ?>
                            <div class="b-layout__txt b-layout__txt_padbot_15">
                                Процесс выполнения заказа завершен, спасибо за сотрудничество!
                            </div>
                            <div class="b-buttons b-buttons_padbot_15">
                                <?php if($is_frl_feedback): ?>
                                <a class="b-button" 
                                   href="<?=tservices_helper::getFeedbackUrl($frl_feedback_id, $employer['login'])?>">
                                    Ваш отзыв заказчику
                                </a>                        
                                <?php endif; ?>
                                <?php if($is_emp_feedback): ?>
                                <a class="b-button <?php if($is_frl_feedback): ?>b-buttons_padleft_20<?php endif; ?>" 
                                   href="<?=tservices_helper::getFeedbackUrl($emp_feedback_id)?>">
                                    Отзыв заказчика
                                </a>
                                <?php endif; ?>
                            </div>                     
                            <?php } ?>
                        <?php } ?>
                    
                    <?php } ?>
                    

                                  
                </td>
            </tr>  
        </table>
<?php
        } else {
        
        if($is_adm)
        {
?>
        <table class="b-layout__table">
            <tr class="b-layout__tr">
                <td class="b-layout__td">
                    <div class="b-layout__txt b-layout__txt_color_000 b-layout__txt_padbot_5">
                        Заказчик <?php echo $emp_fullname ?> завершил сотрудничество и закрыл заказ.
                    </div>
                    <?php if($is_emp_feedback){ ?>
                    <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_bold">
                        Отзыв заказчика:
                    </div>
                    <div class="b-layout__txt b-layout__txt_margbot_10 b-layout__txt_fontsize_11 b-layout__txt_italic b-layout__txt_padleft_15 b-layout__txt_margleft_15  ">
                        <?=$emp_feedback?>
                    </div>
                    <?php } ?> 
                    <?php if($is_frl_feedback){ ?>
                    <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_bold">
                        Отзыв исполнителя:
                    </div>
                    <div class="b-layout__txt b-layout__txt_margbot_10 b-layout__txt_fontsize_11 b-layout__txt_italic b-layout__txt_padleft_15 b-layout__txt_margleft_15  ">
                        <?=$frl_feedback?>
                    </div>  
                    <?php } ?> 
                </td>
            </tr>
        </table>
<?php
        }
        elseif($is_emp)
        {
//------------------------------------------------------------------------------
// Заказчик закрыл заказ с отзывом или без.
// Статус для заказчика.
//------------------------------------------------------------------------------            
            $icon_action = $pay_type == TServiceOrderModel::PAYTYPE_RESERVE && !$reserve->isStatusPayPayed() ? 'pay' : 'close';
?>
        <table class="b-layout__table b-layout__table_width_full">
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_width_60 b-layout__td_ipad b-layout__td_width_null_ipad">
                    <img class="b-user__pic"  alt="" src="/images/po/<?=$icon_prefix?>_<?=$icon_action?>.png"/>
                </td>
                <td class="b-layout__td b-layout__td_ipad">
<?php
                if($pay_type == TServiceOrderModel::PAYTYPE_RESERVE):
//------------------------------------------------------------------------------
// Заказчик закрыл заказ с отзывом или без для схемы с резервом средств.
// Статус для заказчика.
//------------------------------------------------------------------------------
                 
                    if($reserve->isStatusPayPayed()):
//------------------------------------------------------------------------------
// Заказчик закрыл заказ с отзывом или без для схемы с резервом средств.
// Статус для заказчика. Резерв выплачен исполнителю в полном обьеме.
//------------------------------------------------------------------------------ 
                        $price = tservices_helper::cost_format($reserve->getPrice(),true, false, false);
?>
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout_float_right b-layout__txt_float_none_iphone b-layout__txt_padbot_5">
                        <span class="b-icon b-icon_sbr_oask"></span>
                        <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/list/27457-baza-znanij-flru/?category=12682">
                            О Безопасных сделках
                        </a>
                    </div>                    
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        Сотрудничество завершено, заказ закрыт.
                    </div>
                    <div class="b-layout__txt b-layout__txt_padbot_10">
                        К выплате исполнителю <?=$price?> <?=$reserve->isFrlPhis()?'(за вычетом 13% НДФЛ) ':''?>&mdash; <b>сумма выплачена</b>
                    </div>
                    <div class="b-layout__txt b-layout__txt_padbot_15">
                        <?php if($is_allow_feedback && !$is_emp_feedback): ?>
                            Вы можете оставить отзыв исполнителю.<br/>
                        <?php endif; ?>
                        Процесс выполнения заказа завершен, спасибо за сотрудничество!
                    </div>
                    <div class="b-buttons b-buttons_padbot_15">
                        <?php if($is_emp_feedback): ?>
                        <a class="b-button" 
                           href="<?=tservices_helper::getFeedbackUrl($emp_feedback_id, $freelancer['login'])?>">
                            Ваш отзыв исполнителю
                        </a>                        
                        <?php elseif($is_allow_feedback): ?>
                        <a class="b-button b-button_flat b-button_flat_green" 
                           href="javascript:void(0);" 
                           data-popup="<?=TServiceOrderFeedback::getPopupId($order_id)?>">
                            Оставить отзыв
                        </a>
                        <?php endif; ?>
                        <?php if($is_frl_feedback): ?>
                        <a class="b-button <?php if($is_emp_feedback || $is_allow_feedback): ?>b-buttons_padleft_20<?php endif; ?>" 
                           href="<?=tservices_helper::getFeedbackUrl($frl_feedback_id)?>">
                            Отзыв исполнителя
                        </a>
                        <?php endif; ?>
                    </div>
<?php                
                    else:
//------------------------------------------------------------------------------
// Заказчик закрыл заказ с отзывом или без для схемы с резервом средств.
// Статус для заказчика. Ожидание выплаты суммы исполнителю.
//------------------------------------------------------------------------------
                        $price = tservices_helper::cost_format($reserve->getPrice() ,true, false, false);
?>
             <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout_float_right b-layout__txt_float_none_iphone b-layout__txt_padbot_5">
                <span class="b-icon b-icon_sbr_oask"></span>
                <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/list/27457-baza-znanij-flru/?category=12682">
                    О Безопасных сделках
                </a>
             </div>
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        Заказ с оплатой через Безопасную сделку &mdash; выплата сумм
                    </div>                    
                    <div class="b-layout__txt b-layout__txt_padbot_10">
                        Вы приняли и подтвердили результат выполнения работы по заказу. Исполнителю будет перечислена зарезервированная вами сумма оплаты.
                    </div>
                    <div class="b-layout__txt b-layout__txt_padbot_15">
                        К выплате исполнителю <?=$price?> &mdash; <b>выплата суммы</b><br>
                        Процесс выполнения заказа завершен, спасибо за сотрудничество!<br>
                    </div>
                    <?php if(!$is_emp_feedback && $is_allow_feedback): ?>
                    <div class="b-layout__txt b-layout__txt_padbot_15">
                        Не забудьте до <?=$date_feedback?> оставить отзыв.
                    </div>
                    <?php endif; ?>
                    <?php if((!$is_emp_feedback && $is_allow_feedback) || $is_frl_feedback): ?>
                    <div class="b-buttons b-buttons_padbot_15">
                        <?php if($is_emp_feedback): ?>
                        <a class="b-button" 
                           href="<?=tservices_helper::getFeedbackUrl($emp_feedback_id, $freelancer['login'])?>">
                            Ваш отзыв исполнителю
                        </a>  
                        <?php elseif($is_allow_feedback): ?>
                        <a class="b-button b-button_flat b-button_flat_green" 
                           href="javascript:void(0);" 
                           data-popup="<?=TServiceOrderFeedback::getPopupId($order_id)?>">
                            Оставить отзыв
                        </a>                        
                        <?php endif; ?>
                        <?php if($is_frl_feedback): ?>
                        <a class="b-button <?php if($is_emp_feedback || $is_allow_feedback): ?>b-buttons_padleft_20<?php endif; ?>" 
                           href="<?=tservices_helper::getFeedbackUrl($frl_feedback_id)?>">
                            Отзыв исполнителя
                        </a>
                        <?php endif; ?>                        
                    </div>
                    <?php endif; ?>
<?php
                    endif;
                
                    if($is_allow_feedback && !$is_emp_feedback): 
                        $this->widget('TServiceOrderFeedback', array('data' => array(
                            'idx' => $order_id,
                            'hash' => $hash,
                            'pay_type' => $pay_type,
                            'rating' => $frl_rating,
                            'is_close' => true
                        )));
                    endif;
                
                else:
//------------------------------------------------------------------------------
// Заказчик закрыл заказ с отзывом или без для обычной схемы.
// Статус для заказчика.
//------------------------------------------------------------------------------
?>
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        Заказ с прямой оплатой &mdash; заказ закрыт
                    </div>
                    
                    <div class="b-layout__txt b-layout__txt_padbot_10">
                        Процесс выполнения заказа завершен, спасибо за сотрудничество!
                        <?php if (!$is_emp_feedback && $is_allow_feedback): ?>
                            <br>Не забудьте до <?=$date_feedback?> оставить отзыв.
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($is_emp_feedback): ?>
                    <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_bold">
                        <a class="b-layout__link" 
                           href="<?=tservices_helper::getFeedbackUrl($emp_feedback_id, $freelancer['login'])?>">
                            Ваш отзыв
                        </a>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($is_frl_feedback): ?>
                    <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_bold">
                        <a class="b-layout__link" href="<?=tservices_helper::getFeedbackUrl($frl_feedback_id)?>">
                            Отзыв от Исполнителя
                        </a>
                    </div>
                    <?php endif ?>
                    
                    
                    <?php if (!$is_emp_feedback && $is_allow_feedback): ?>
                    
                    <div class="b-buttons b-buttons_padbot_15">
                        <a class="b-button b-button_flat b-button_flat_green" 
                           href="javascript:void(0);" 
                           data-popup="<?=TServiceOrderFeedback::getPopupId($order_id)?>">
                            Оставить отзыв
                        </a>
                    </div>
<?php
                $this->widget('TServiceOrderFeedback', array('data' => array(
                    'idx' => $order_id,
                    'hash' => $hash,
                    'pay_type' => $pay_type,
                    'rating' => $frl_rating,
                    'is_close' => true
                )));
?>
                    
                    <?php endif ?>
                    
                    
<?php
                endif;
?>
                </td>
            </tr>  
        </table>
<?php
        }
        else
        {
//------------------------------------------------------------------------------
// Заказчик закрыл заказ с отзывом или без.
// Статус для исполнителя.
//------------------------------------------------------------------------------            

            $icon_action = $pay_type == TServiceOrderModel::PAYTYPE_RESERVE && !$reserve->isStatusPayPayed() ? 'pay' : 'close';
?>
        <table class="b-layout__table b-layout__table_width_full">
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_width_60 b-layout__td_ipad b-layout__td_width_null_ipad">
                    <img class="b-user__pic" alt="" src="/images/po/<?=$icon_prefix?>_<?=$icon_action?>.png"/>
                </td>                
                <td class="b-layout__td b-layout__td_ipad">
<?php
//------------------------------------------------------------------------------
// Заказчик закрыл заказ с отзывом или без.
// Статус для исполнителя по схеме резерва средств.
//------------------------------------------------------------------------------ 
                if($pay_type == TServiceOrderModel::PAYTYPE_RESERVE):
                   
                    if($reserve->isStatusPayPayed()):
//------------------------------------------------------------------------------
// Заказчик закрыл заказ с отзывом или без.
// Статус для исполнителя по схеме резерва средств.
// Резерв выплачен в полном обьеме.
//------------------------------------------------------------------------------
                        $price = tservices_helper::cost_format($reserve->getPrice(), true, false, false);
?>
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout_float_right b-layout__txt_float_none_iphone b-layout__txt_padbot_5">
                       <span class="b-icon b-icon_sbr_oask"></span>
                       <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/list/27457-baza-znanij-flru/?category=12682">
                           О Безопасных сделках
                       </a>
                    </div>                    
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        Сотрудничество завершено, заказ закрыт.
                    </div>
                    <div class="b-layout__txt b-layout__txt_padbot_10">
                        К выплате исполнителю <?=$price?> <?=$reserve->isFrlPhis()?'(за вычетом 13% НДФЛ) ':''?>&mdash; <b>сумма выплачена</b>
                    </div>
                    <div class="b-layout__txt b-layout__txt_padbot_15">
                        <?php if(!$is_frl_feedback && $is_allow_feedback): ?>
                            Вы можете оставить отзыв заказчику. <br/>
                        <?php endif; ?>
                        Процесс выполнения заказа завершен, спасибо за сотрудничество!
                    </div>
                    <div class="b-buttons b-buttons_padbot_15">
                        <?php if($is_frl_feedback): ?>
                        <a class="b-button" 
                           href="<?=tservices_helper::getFeedbackUrl($frl_feedback_id, $employer['login'])?>">
                            Ваш отзыв заказчику
                        </a>                        
                        <?php elseif($is_allow_feedback): ?>
                        <a class="b-button b-button_flat b-button_flat_green" 
                           href="javascript:void(0);" 
                           data-popup="<?=TServiceOrderFeedback::getPopupId($order_id)?>">
                            Оставить отзыв
                        </a>
                        <?php endif; ?>
                        <?php if($is_emp_feedback): ?>
                        <a class="b-button <?php if($is_frl_feedback || $is_allow_feedback): ?>b-buttons_padleft_20<?php endif; ?>" 
                           href="<?=tservices_helper::getFeedbackUrl($emp_feedback_id)?>">
                            Отзыв заказчика
                        </a>
                        <?php endif; ?>
                    </div>
<?php
                        if(!$is_frl_feedback && $is_allow_feedback):
                            $this->widget('TServiceOrderFeedback', array('data' => array(
                                'idx' => $order_id,
                                'hash' => $hash,
                                'pay_type' => $pay_type,
                                'rating' => $emp_rating,
                                'is_close' => true
                            )));
                        endif;
                        
                    elseif($reserve->isStatusPayInprogress()): 
//------------------------------------------------------------------------------
// Заказчик закрыл заказ с отзывом или без.
// Статус для исполнителя по схеме резерва средств.
// В процессе выплаты. Ожидание ответа сервиса
//------------------------------------------------------------------------------   
                        $pay_type_txt = ReservesHelper::getInstance()->getPayoutType($reserve_data['id']);
                        $price = tservices_helper::cost_format($reserve->getPrice(),true, false, false);
?> 
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout_float_right b-layout__txt_float_none_iphone b-layout__txt_padbot_5">
                       <span class="b-icon b-icon_sbr_oask"></span>
                       <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/list/27457-baza-znanij-flru/?category=12682">
                           О Безопасных сделках
                       </a>
                    </div>
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        Заказ с оплатой через Безопасную сделку &mdash; выплата сумм
                    </div>                    
                    <div class="b-layout__txt b-layout__txt_padbot_15">
                        Заказчик принял и подтвердил результат выполнения работы по заказу.<br />
                        К выплате Исполнителю <?=$price?> &mdash; <b>выплата суммы</b><br />
                        Ожидайте, пожалуйста. <?=$pay_type_txt?> 
                     <?php 
                        if($reserve->getNDFL()):
                            $tax_price = tservices_helper::cost_format($reserve->getNDFL(), true, false, false);
                     ?>  
                        Обратите внимание, что за вас также будет уплачен НДФЛ в размере <?=$tax_price?>.
                     <?php
                        endif;
                     ?>
                    </div>
                    <div class="b-buttons b-buttons_padbot_15">
                        <?php if($is_frl_feedback): ?>
                        <a class="b-button" 
                           href="<?=tservices_helper::getFeedbackUrl($frl_feedback_id, $employer['login'])?>">
                            Ваш отзыв заказчику
                        </a>                        
                        <?php elseif($is_allow_feedback): ?>
                        <a class="b-button b-button_flat b-button_flat_green" 
                           href="javascript:void(0);" 
                           data-popup="<?=TServiceOrderFeedback::getPopupId($order_id)?>">
                            Оставить отзыв
                        </a>
                        <?php endif; ?>
                        <?php if($is_emp_feedback): ?>
                        <a class="b-button <?php if($is_frl_feedback || $is_allow_feedback): ?>b-buttons_padleft_20<?php endif; ?>" 
                           href="<?=tservices_helper::getFeedbackUrl($emp_feedback_id)?>">
                            Отзыв заказчика
                        </a>
                        <?php endif; ?>
                    </div>           
<?php 

                        if(!$is_frl_feedback && $is_allow_feedback):
                            $this->widget('TServiceOrderFeedback', array('data' => array(
                                'idx' => $order_id,
                                'hash' => $hash,
                                'pay_type' => $pay_type,
                                'rating' => $emp_rating,
                                'is_close' => true
                            )));
                        endif;

                    else:
//------------------------------------------------------------------------------
// Заказчик закрыл заказ с отзывом или без.
// Статус для исполнителя по схеме резерва средств.
// Подтвердить выплату суммы.
//------------------------------------------------------------------------------  
                        $is_reserve_error = $reserve->isStatusPayError();
                        $fn_url = sprintf("/users/%s/setup/finance/", $freelancer['login']);
                        $price = tservices_helper::cost_format($reserve->getPrice(),true, false, false); 
?> 
             <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout_float_right b-layout__txt_float_none_iphone b-layout__txt_padbot_5">
                <span class="b-icon b-icon_sbr_oask"></span>
                <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/list/27457-baza-znanij-flru/?category=12682">
                    О Безопасных сделках
                </a>
             </div>
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        Заказ с оплатой через Безопасную сделку &mdash; выплата сумм
                    </div>                    
                    <div class="b-layout__txt b-layout__txt_padbot_10">
                        <?php echo $fullname ?> принял и подтвердил результат выполнения работы по заказу.<br/>
                        К выплате исполнителю <?=$price?> <?=$reserve->isFrlPhis()?'(за вычетом 13% НДФЛ) ':''?>
                        - <b>
                            <?php if($is_reserve_error): ?>
                                выплата временно приостановлена
                            <?php else: ?>
                                ожидание выплаты суммы
                            <?php endif; ?>
                            </b><br/>
                        
                        <?php if(!$reserve->isFrlFinanceFailStatus()): ?>
                            <?php if($is_reserve_error): ?>
                                <?php if($reserve->getReasonPayout()): ?>
                                <strong>Выплата приостановлена по причине: </strong>
                                <?=$reserve->getReasonPayout()?>
                                <?php else: ?>
                                К сожалению, при выплате суммы возникла ошибка. <br/>
                                Пожалуйста, проверьте, правильные ли реквизиты указаны на странице Финансы, и повторите запрос на выплату.
                                <?php endif; ?>
                            <?php else: ?>    
                                Подтвердите, пожалуйста, выплату суммы, чтобы мы могли перечислить ее удобным вам способом.
                                <?php if(!$reserve->isFrlAllowFinance()): ?>
                                <br/><br/>
                                Обратите внимание: перед подтверждением выплаты вам необходимо заполнить данные на странице "Финансы".<br/>
                                <?=session::getFlashMessages('isValidUserReqvs')?>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php endif; ?>    
                    </div>
                    <?php if($reserve->isFrlFinanceFailStatus()): ?>
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-fon_overflow_hidden">
                        К сожалению, на странице Финансы указаны некорректные 
                        данные<?php if($reason = $reserve->getFrlFinanceBlockedReason()): ?>: <?=$reason?>.<?php else: ?>.<? endif; ?>               
                        <br/>Для перехода к процессу выплаты укажите, пожалуйста, корректные данные.
                    </div>
                    <?php endif; ?>
                    <div class="b-buttons b-buttons_padbot_20">
                    <?php if($reserve->isFrlAllowFinance()): ?>
                        <a class="b-button b-button_flat b-button_flat_green" 
                           data-popup="<?=ReservesPayoutPopup::getPopupId($order_id)?>" 
                           data-url="<?=$order_url?>" 
                           href="javascript:void(0);">
                            <?php if($is_reserve_error): ?>
                                Повторно подтвердить выплату
                            <?php else: ?>
                                Подтвердить выплату суммы
                            <?php endif; ?>
                        </a>
                        <?php if($is_reserve_error): ?>
                        <a href="<?=$fn_url?>" class="b-button b-button_flat b-button_flat_green">
                            Перейти на страницу "Финансы"
                        </a>                        
                        <?php endif; ?>
                    <?php elseif(!$reserve->isFrlFinanceValid()): ?>
                        <a href="<?=$fn_url?>" class="b-button b-button_flat b-button_flat_green">
                            Перейти на страницу "Финансы"
                        </a>        
                    <?php else: ?>
                        <a href="javascript:void(0)" 
                           class="b-button b-button_flat b-button_flat_green b-button_disabled">
                            Проверка данных модератором 
                        </a>                        
                    <?php endif; ?>
                        
                        <?php if($is_emp_feedback): ?>
                        <a class="b-button b-buttons_padleft_20" 
                           href="<?=tservices_helper::getFeedbackUrl($emp_feedback_id)?>">
                            Отзыв заказчика
                        </a>
                        <?php endif; ?>
                    </div>
<?php
                        //рендерим попап для подтверждения оплаты
                        if(!$is_list && $reserve->isFrlAllowFinance()) 
                        {
                            $ndfl = null;
                            if ($reserve->getNDFL()) 
                            {
                                $ndfl = tservices_helper::cost_format(
                                        $reserve->getNDFL(),true, false, false);
                            }
        
                            $this->widget('ReservesPayoutPopup', array(
                                'price' => $reserve->getPrice(),
                                'options' => array(
                                    'idx' => $order_id,
                                    'hash' => $hash,
                                    'is_feedback' => $is_frl_feedback,
                                    'is_allow_feedback' => $is_allow_feedback,
                                    'price' => $price,
                                    'price_ndfl' => $ndfl,
                                    'price_all' => tservices_helper::cost_format($reserve->getPriceWithOutNDFL(),true, false, false),
                                    'fn_url' => ($reserve->isAllowEditFinance($reserve_data['frl_id'], false))?$fn_url:false
                            )));
                        }
                        
                    endif;
                else:
?>
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        Заказ с прямой оплатой &mdash; заказ закрыт
                    </div>
                    
                    <div class="b-layout__txt b-layout__txt_padbot_5">
                        Заказчик завершил заказ, спасибо за сотрудничество!
                        <?php if(!$is_emp_feedback && $is_allow_feedback): ?>
                            <br />Не забудьте до <?=$date_feedback?> оставить отзыв.
                        <?php endif; ?>
                    </div>
                    
                    
                    <?php if($is_frl_feedback): ?>
                    <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_bold">
                        <a class="b-layout__link" href="<?=tservices_helper::getFeedbackUrl($frl_feedback_id, $employer['login'])?>">Ваш отзыв</a>
                    </div>
                    <?php endif; ?>
                    
                    <?php if($is_emp_feedback): ?>
                    <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_bold">
                        <a class="b-layout__link" href="<?=tservices_helper::getFeedbackUrl($emp_feedback_id)?>">Отзыв от Заказчика</a>
                    </div>
                    <?php endif; ?>

                    <?php if(!$is_frl_feedback && $is_allow_feedback): ?>
                    <div class="b-buttons b-buttons_padbot_15">
                        <a class="b-button b-button_flat b-button_flat_green" 
                           href="javascript:void(0);" 
                           data-popup="<?=TServiceOrderFeedback::getPopupId($order_id)?>">
                            Оставить отзыв
                        </a>
                    </div>
<?php
                    $this->widget('TServiceOrderFeedback', array('data' => array(
                        'idx' => $order_id,
                        'hash' => $hash,
                        'pay_type' => $pay_type,
                        'rating' => $emp_rating,
                        'is_close' => true
                    )));
?>
                    <?php endif; ?>
<?php
            endif;
?>
                </td>
            </tr>  
        </table>       
<?php

        }
        }
?>

<?php

    } 
    elseif($order_status == TServiceOrderModel::STATUS_FRLCLOSE)
    {
//------------------------------------------------------------------------------
// Исполнитель уведомил о готовности работы
// Статус для админа.
//------------------------------------------------------------------------------        
        if($is_adm)
        {
?>
        <table class="b-layout__table">
            <tr class="b-layout__tr">
                <td class="b-layout__td">
<?php 
                if (isset($reserve_data['arbitrage_id'])): 

?>
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        <?=$reserve_data['arbitrage_is_emp']=='t'?'Заказчик':'Исполнитель'?> обратился в Арбитраж
                    </div>
                    <div class="b-layout__txt b-layout__txt_margbot_10 b-layout__txt_fontsize_11 b-layout__txt_italic b-layout__txt_padleft_15 b-layout__txt_margleft_15 b-layout_bordleft__666 b-layout__txt_color__666">
                        <?=$reserve_data['arbitrage_message']?>
                    </div>
<?php
                        //рендерим форму для арбитра
                        $this->widget('ReservesArbitrageForm', array('data' => array(
                            'order_id' => $order_id,
                            'price' => $reserve_data['price']
                        )));

                else: 
?>                    
                    <div class="b-layout__txt b-layout__txt_color_000 b-layout__txt_padbot_5">
                        Исполнитель <?php echo $frl_fullname ?> завершил сотрудничество и закрыл заказ.
                    </div>
                    <?php if($is_emp_feedback){ ?>
                    <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_bold">
                        Отзыв заказчика:
                    </div>
                    <div class="b-layout__txt b-layout__txt_margbot_10 b-layout__txt_fontsize_11 b-layout__txt_italic b-layout__txt_padleft_15 b-layout__txt_margleft_15 b-layout_bordleft_<?=$emp_color?>">
                        <?=$emp_feedback?>
                    </div>
                    <?php } ?> 
                    <?php if($is_frl_feedback){ ?>
                    <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_bold">
                        Отзыв исполнителя:
                    </div>
                    <div class="b-layout__txt b-layout__txt_margbot_10 b-layout__txt_fontsize_11 b-layout__txt_italic b-layout__txt_padleft_15 b-layout__txt_margleft_15 b-layout_bordleft_<?=$frl_color?>">
                        <?=$frl_feedback?>
                    </div>  
                    <?php } ?> 
<?php
                endif;
?>
                </td>
            </tr>
        </table>
<?php
        }
        elseif($is_emp)
        {
//------------------------------------------------------------------------------
// Исполнитель уведомил о готовности работы. 
// Статус для заказчика.
//------------------------------------------------------------------------------            
            $icon_action = $reserve_data['arbitrage_id'] > 0 ? 'arbitrage' : 'run';   
?>
        <table class="b-layout__table b-layout__table_width_full">
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_width_60 b-layout__td_ipad b-layout__td_width_null_ipad">
                    <img class="b-user__pic"  alt="" src="/images/po/<?=$icon_prefix?>_<?=$icon_action?>.png"/>
                </td>
                <td class="b-layout__td b-layout__td_ipad">
<?php
//------------------------------------------------------------------------------
// Исполнитель уведомил о готовности работы по схеме резерва суммы. 
// Статус для заказчика.
//------------------------------------------------------------------------------
                if($pay_type == TServiceOrderModel::PAYTYPE_RESERVE):
                    
                    //Есть заявка на арбитраж
                    if(isset($reserve_data['arbitrage_id'])):
?>
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_right">
                        <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/topic/483825-arbitrazh-v-zakazah-s-rezervirovaniem/">
                            Подробнее об арбитраже
                        </a>
                    </div>                    
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        Заказ в арбитраже, идет его рассмотрение
                    </div>
                    <div class="b-layout__txt b-layout__txt_padbot_15">
                        <?=$reserve_data['arbitrage_is_emp']=='t'?'Вы обратились':'Исполнитель обратился'?> в Арбитраж, передав заказ на рассмотрение Арбитру. В течение нескольких дней 
                        он изучит ситуацию и предпримет меры для урегулирования конфликта, возникшего между вами и 
                        исполнителем. По окончании арбитражного рассмотрения будет вынесено независимое решение 
                        (о выплате, возврате или разделении зарезервированной суммы). Ожидайте, пожалуйста.
                    </div>
                    <div class="b-layout__txt b-layout__txt_color_666 b-layout__txt_padbot_5 b-layout__txt_bold">
                        Причина обращения в Арбитраж:
                    </div>
                    <div class="b-layout__txt b-layout__txt_margbot_20 b-layout__txt_fontsize_11 b-layout__txt_italic b-layout__txt_padleft_15 b-layout__txt_margleft_15 b-layout_bordleft__666 b-layout__txt_color__666">
                        <?=$reserve_data['arbitrage_message']?>
                    </div>
                    
                    <div class="b-buttons b-buttons_padbot_10">
                         <a class="b-button b-button_flat b-button_flat_green b-button_margright_10" 
                            data-url="<?=$order_url?>"
                            data-scrollto = "form-block"
                            href="javascript:void(0);"
                            onclick="if($('tservice-message-textarea')) $('tservice-message-textarea').focus();">
                             Обсудить заказ
                         </a>
                    </div>          

<?php
                    else:    
?>                    
             <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout_float_right b-layout__txt_float_none_iphone b-layout__txt_padbot_5">
                <span class="b-icon b-icon_sbr_oask"></span>
                <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/list/27457-baza-znanij-flru/?category=12682">
                    О Безопасных сделках
                </a>
             </div>
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        Заказ с оплатой через Безопасную сделку &mdash; работа выполнена
                    </div>
                    <div class="b-layout__txt b-layout__txt_padbot_10">

                        Исполнитель закончил выполнение работы в заказе. Пожалуйста, примите результат работы и завершите сотрудничество (с выплатой зарезервированной суммы Исполнителю) или верните заказ в работу, если он выполнен не полностью.
                                
                    </div>
                    <div class="b-buttons b-buttons_padbot_15">
                         <a class="b-button b-button_flat b-button_flat_green b-button_margright_10" 
                            data-url="<?=$order_url?>"
                            data-scrollto = "form-block"
                            href="javascript:void(0);"
                            onclick="if($('tservice-message-textarea')) $('tservice-message-textarea').focus();">
                             Обсудить заказ
                         </a>
                        <a class="b-button b-button_flat b-button_flat_green" 
                           href="javascript:void(0);" 
                           data-duplicate="1"
                           data-popup="<?=TServiceOrderFeedback::getPopupId($order_id)?>">
                            Завершить сотрудничество
                        </a>
                        <span class="b-buttons__txt b-button__txt_padbot_10_ipad">&#160; или &#160;
                        <a class="b-layout__link" 
                           href="<?php echo tservices_helper::getOrderStatusUrl($order_id, 'fix'); ?>" 
                           data-duplicate="2"
                           onClick="">
                            Вернуть заказ в работу
                        </a>
                        &#160; или &#160;
                        <a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_color_0f71c8" 
                           href="javascript:void(0);" 
                           data-url="<?=$order_url?>"
                           data-popup="<?=ReservesArbitragePopup::getPopupId($order_id)?>"
                           data-duplicate="3">
                            обратиться в Арбитраж
                        </a>
                        </span>
                    </div>
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padleft_20 b-layout__txt_padbot_10">
                        <span class="b-icon b-icon_sbr_oattent b-icon_top_1 b-icon_margleft_-20"></span>Если в процессе сотрудничества у вас возникнут проблемы с Исполнителем, рекомендуем обратиться в Арбитраж и урегулировать конфликт с помощью Арбитра.
                    </div>                    
                    <?php $this->widget('ReservesArbitragePopup', array('data' => array(
                        'idx' => $order_id
                    ))) ?>
<?php
                    endif;
                else:
//------------------------------------------------------------------------------
// Исполнитель уведомил о готовности работы по обычной схеме. 
// Статус для заказчика.
//------------------------------------------------------------------------------                    
?>
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        Заказ с прямой оплатой &mdash; работа выполнена
                    </div>
                    <div class="b-layout__txt b-layout__txt_padbot_10">

                        Исполнитель закончил выполнение работы в заказе. Пожалуйста, примите результат работы и завершите сотрудничество (оплатив услуги Исполнителя) или верните заказ в работу, если он выполнен не полностью.
                                
                    </div>
                    <div class="b-buttons b-buttons_padbot_15">
                         <a class="b-button b-button_flat b-button_flat_green b-button_margright_10" 
                            data-url="<?=$order_url?>"
                            data-scrollto = "form-block"
                            href="javascript:void(0);"
                            onclick="if($('tservice-message-textarea')) $('tservice-message-textarea').focus();">
                             Обсудить заказ
                         </a>
                        <a class="b-button b-button_flat b-button_flat_green" 
                           href="javascript:void(0);" 
                           data-popup="<?=TServiceOrderFeedback::getPopupId($order_id)?>"
                           data-duplicate="1"
                           data-url="<?=$order_url?>">
                            Завершить сотрудничество
                        </a>
                        <span class="b-buttons__txt b-button__txt_padbot_10_ipad">&#160; или &#160;</span>
                        <a class="b-layout__link" 
                           href="<?php echo tservices_helper::getOrderStatusUrl($order_id, 'fix'); ?>" 
                           onClick="" data-duplicate="2">вернуть заказ в работу</a>
                    </div>
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padleft_20 b-layout__txt_padbot_10">
                        <span class="b-icon b-icon_sbr_oattent b-icon_top_1 b-icon_margleft_-20"></span><?php echo $no_reserve_warning ?>
                    </div>                    
<?php
                endif;

                $this->widget('TServiceOrderFeedback', array('data' => array(
                    'idx' => $order_id,
                    'hash' => $hash,
                    'pay_type' => $pay_type,
                    'rating' => $frl_rating,
                    'is_close' => false
                )));
?>                    
                </td>
            </tr>  
        </table>
<?php
        }
        else
        {
//------------------------------------------------------------------------------
// Исполнитель уведомил о готовности работы. 
// Статус для исполнителя.
//------------------------------------------------------------------------------             
            $icon_action = $reserve_data['arbitrage_id'] > 0 ? 'arbitrage' : 'run';
?>
        <table class="b-layout__table b-layout__table_width_full">
            <tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_width_60 b-layout__td_ipad b-layout__td_width_null_ipad">
                    <img class="b-user__pic"  alt="" src="/images/po/<?=$icon_prefix?>_<?=$icon_action?>.png"/>
                </td>
                <td class="b-layout__td b-layout__td_ipad">
                    
<?php
    //------------------------------------------------------------------------------
    // Исполнитель уведомил о готовности работы по схеме резерва суммы. 
    // Статус для исполнителя.
    //------------------------------------------------------------------------------
            if($pay_type == TServiceOrderModel::PAYTYPE_RESERVE):
                        
                    //Есть заявка на арбитраж
                    if(isset($reserve_data['arbitrage_id'])):
?>
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_right">
                        <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/topic/483825-arbitrazh-v-zakazah-s-rezervirovaniem/">
                            Подробнее об арбитраже
                        </a>
                    </div>
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        Заказ в арбитраже, идет его рассмотрение
                    </div>
                    <div class="b-layout__txt b-layout__txt_padbot_15">
                        <?=$reserve_data['arbitrage_is_emp']=='t'?'Заказчик обратился':'Вы обратились'?> в Арбитраж, передав заказ на рассмотрение Арбитру. 
                        В течение нескольких дней он изучит ситуацию и предпримет меры для урегулирования конфликта, 
                        возникшего между вами и заказчиком. По окончании арбитражного рассмотрения будет вынесено независимое решение 
                        (о выплате, возврате или разделении зарезервированной суммы). Ожидайте, пожалуйста.
                    </div>
                    <div class="b-layout__txt b-layout__txt_color_666 b-layout__txt_padbot_5 b-layout__txt_bold">
                        Причина обращения в Арбитраж:
                    </div>
                    <div class="b-layout__txt b-layout__txt_margbot_20 b-layout__txt_fontsize_11 b-layout__txt_italic b-layout__txt_padleft_15 b-layout__txt_margleft_15 b-layout_bordleft__666 b-layout__txt_color__666">
                        <?=$reserve_data['arbitrage_message']?>
                    </div>
                    <div class="b-buttons b-buttons_padbot_10">
                         <a class="b-button b-button_flat b-button_flat_green b-button_margright_10" 
                            data-url="<?=$order_url?>"
                            data-scrollto = "form-block"
                            href="javascript:void(0);"
                            onclick="if($('tservice-message-textarea')) $('tservice-message-textarea').focus();">
                             Обсудить заказ
                         </a>
                    </div>          
<?php
                    else:                        
?>
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout_float_right b-layout__txt_float_none_iphone b-layout__txt_padbot_5">
                       <span class="b-icon b-icon_sbr_oask"></span>
                       <a class="b-layout__link" target="_blank" href="https://feedback.fl.ru/list/27457-baza-znanij-flru/?category=12682">
                           О Безопасных сделках
                       </a>
                    </div>
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                         Заказ с оплатой через Безопасную сделку &mdash; работа выполнена
                    </div>                    
                    <div class="b-layout__txt b-layout__txt_padbot_15">
                        Заказчик уведомлен о том, что вы закончили выполнение заказа. Ожидайте, пожалуйста, когда Заказчик примет результат работы и завершит сотрудничество (с выплатой вам зарезервированной суммы) или вернет заказ в работу, если он выполнен не полностью.
                    </div>
                    
                    
                    
                    <div class="b-buttons b-buttons_padbot_10">
                         <a class="b-button b-button_flat b-button_flat_green b-button_margright_10" 
                            data-url="<?=$order_url?>"
                            data-scrollto = "form-block"
                            href="javascript:void(0);"
                            onclick="if($('tservice-message-textarea')) $('tservice-message-textarea').focus();">
                             Обсудить заказ
                         </a>
                        <span class="b-buttons__txt b-button__txt_padbot_10_ipad">&#160; или &#160;
                        <a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_color_0f71c8" 
                           href="javascript:void(0);" 
                           data-url="<?=$order_url?>"
                           data-duplicate="1"
                           data-popup="<?=ReservesArbitragePopup::getPopupId($order_id)?>">
                            обратиться в Арбитраж
                        </a>
                        </span>
                    </div>  
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padleft_20 b-layout__txt_padbot_10">
                        <span class="b-icon b-icon_sbr_oattent b-icon_top_1 b-icon_margleft_-20"></span>
                        Если в процессе сотрудничества у вас возникнут проблемы с Заказчиком, 
                        рекомендуем обратиться в Арбитраж и урегулировать конфликт с помощью Арбитра.
                    </div>                    
<?php 
                    $this->widget('ReservesArbitragePopup', array('data' => array(
                        'idx' => $order_id
                    ))); 
                    
                 endif;     
                    
            else:
    //------------------------------------------------------------------------------
    // Исполнитель уведомил о готовности работы по обычной схеме. 
    // Статус для исполнителя.
    //------------------------------------------------------------------------------                   
?>
                    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_bold">
                        Заказ с прямой оплатой &mdash; работа выполнена
                    </div>                        
                    <div class="b-layout__txt b-layout__txt_padbot_15">
                        Заказчик уведомлен о том, что вы закончили выполнение заказа. Ожидайте, пожалуйста, когда Заказчик примет результат работы и завершит сотрудничество (оплатив ваши услуги) или вернет заказ в работу, если он выполнен не полностью.
                    </div>
                    <div class="b-buttons b-buttons_padbot_10">
                         <a class="b-button b-button_flat b-button_flat_green b-button_margright_10" 
                            data-url="<?=$order_url?>"
                            data-scrollto = "form-block"
                            href="javascript:void(0);"
                            onclick="if($('tservice-message-textarea')) $('tservice-message-textarea').focus();">
                             Обсудить заказ
                         </a>
                    </div>  
                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padleft_20 b-layout__txt_padbot_10">
                        <span class="b-icon b-icon_sbr_oattent b-icon_top_1 b-icon_margleft_-20"></span><?php echo $no_reserve_warning ?>
                    </div>                    
<?php
            endif;
?>
                </td>
            </tr>
        </table>
<?php
        }
?>
<?php 
    }
