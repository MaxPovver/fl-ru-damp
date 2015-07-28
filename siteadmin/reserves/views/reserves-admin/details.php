<?php


?>
<table width="100%">
    <tr>
        <td>
            <h3>
                <a target="_blank" href="<?=$reserveInstance->getTypeUrl()?>">
                    <?=$reserveInstance->getReserveNum()?>
                </a>
            </h3>            
        </td>
        <td style="text-align: right;">
            <?=$form?>
        </td>
    </tr>
</table>
<br/>
<table cellspacing="10">
    <tr>
        <td style="vertical-align:top;">
            
            <table class="nr-a-tbl" cellspacing="5">
                
                <thead>
                    <tr>
                        <th colspan="2">
                            <h3><i>О резерве</i></h3>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="nr-a-tbl_tr">
                        <td><b>Статус:</b></td>
                        <td>
                            <b>сделки:</b> <?=$reserveInstance->getStatusText()?> (status: <?=$reserveInstance->getStatus()?>)<br/>
                            <b>заказа:</b> <?=$reserveInstance->getSrcStatus()?>
                        </td>
                    </tr> 
                    <tr class="nr-a-tbl_tr">
                        <td><b>Статус выплаты:</b></td>
                        <td>
                            <?=$reserveInstance->getStatusPayTxt()?> 
                            (status_pay: <?=(int)$reserveInstance->getStatusPay()?>)
                        </td>
                    </tr>
                    <tr class="nr-a-tbl_tr">
                        <td><b>Статус возврата:</b></td>
                        <td>
                            <?=$reserveInstance->getStatusBackTxt()?> 
                            (status_back: <?=(int)$reserveInstance->getStatusBack()?>)
                        </td>
                    </tr>                    
                    <tr class="nr-a-tbl_tr">
                        <td><b>Дата создания:</b></td>
                        <td>
                            <?=$reserveInstance->getDateByKey('date')?>
                        </td>
                    </tr>                     
                    <tr class="nr-a-tbl_tr">
                        <td><b>Дата резерва средств:</b></td>
                        <td>
                            <?=$reserveInstance->getDateByKey('date_reserve')?>
                        </td>
                    </tr>                    
                    <tr class="nr-a-tbl_tr">
                        <td><b>Дата завершения:</b></td>
                        <td>
                            <?=$reserveInstance->getDateByKey('date_complete')?>
                        </td>
                    </tr>                     
                    <tr class="nr-a-tbl_tr">
                        <td><b>Cумма сделки:</b></td>
                        <td>
                            <?=$reserveInstance->getPriceByKey('price', true)?> 
                        </td>
                    </tr> 
                    <tr class="nr-a-tbl_tr">
                        <td><b>Комиссия:</b></td>
                        <td>
                            <?=$reserveInstance->getPriceByKey('tax_price', true)?>
                            (<?=$reserveInstance->getTax()?> %)
                        </td>
                    </tr> 
                    <tr class="nr-a-tbl_tr">
                        <td><b>Cумма резерва:</b></td>
                        <td>
                            <?=$reserveInstance->getPriceByKey('reserve_price', true)?>
                        </td>
                    </tr>      
                    <?php if ($reserveInstance->isReserveByService()): ?>
                    <tr class="nr-a-tbl_tr">
                        <td><b>ID платежа по ЯД:</b></td>
                        <td>
                            account_operation_id: <?=$reserveInstance->getAccountOperationId()?><br/>
                            invoice_id: <b><?=$reserveInstance->getInvoiceId()?></b>
                        </td>
                    </tr>
                    <?php elseif($reserveInstance->isAllowBankReserve()): ?>
                    
                    <?php if($reserveInstance->getReasonReserve()): ?>
                    <tr class="nr-a-tbl_tr">
                        <td><b>Текущая причина<br/>отказа в резерве:</b></td>
                        <td>
                            <?=$reserveInstance->getReasonReserve()?>
                        </td>
                    </tr>                    
                    <?php endif; ?>
                    
                    <tr class="nr-a-tbl_tr">
                        <td>
                            <form action="" method="POST" enctype="application/x-www-form-urlencoded">
                                <input type="submit" name="submit" value="Подтвердить резерв" />
                                <input type="hidden" name="do" value="accept_reserve" />
                            </form>
                        </td>
                        <td>
                            <a href="javascript:void(0);" onclick="showDeclinePopup('decline_reserve')">Отклонить резерв</a>

                            <div id="decline_reserve" class="b-shadow b-shadow_center b-shadow_width_580 b-shadow__quick b-shadow_hide">
                                <div class="b-shadow__body b-shadow__body_pad_20">
                                    <h2 class="b-layout__title">Отклонить резерв</h2>
                                    <div class="b-layout b-layout_padleft_20">
                                        <form method="post" action="">
                                            <input type="hidden" name="do" value="" />
                                            <div class="b-textarea">
                                                <textarea placeholder="Введите текст причины" name="message" maxlength="500" cols="80" rows="1" class="b-textarea__textarea b-textarea__textarea_italic" id="message"></textarea>
                                            </div>
                                            <div class="b-buttons b-buttons_padtop_20">
                                                <button type="submit" class="b-button b-button_flat b-button_flat_green">Отклонить</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <span class="b-shadow__icon b-shadow__icon_close"></span>
                            </div>
                            
                            
                        </td>
                    </tr>                    
                    <?php endif; ?>
                </tbody>
            </table>
            <?php if(false): ?>
            <br/>
            <table class="nr-a-tbl" cellspacing="5">
                <thead>
                    <tr>
                        <th colspan="2">
                            <h4><i>О заказе</i></h4>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="nr-a-tbl_tr">
                        <td>Статус резерва:</td>
                        <td></td>
                    </tr>                
                    <tr class="nr-a-tbl_tr">
                        <td>Дата создания заказа:</td>
                        <td><?=$reserveInstance->getSrcDate()?></td>
                    </tr>
                    <tr class="nr-a-tbl_tr">
                        <td>Reserve ID:</td>
                        <td><?=$reserveInstance->getID()?></td>
                    </tr>  
                </tbody>
            </table>
            <?php endif; ?>
        </td>
        <td style="vertical-align:top;">
            <?php
                if($reserveInstance->isArbitrage()):
            ?>
            <table class="nr-a-tbl" cellspacing="5">
                <thead>
                    <tr>
                        <th colspan="2">
                            <h3><i>Арбитраж</i></h3>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="nr-a-tbl_tr">
                        <td><b>Статус:</b></td>
                        <td><?=$reserveInstance->isArbitrageClosed()?'закрыт':'открыт'?></td>
                    </tr>  
                    
                    <tr class="nr-a-tbl_tr">
                        <td><b>Дата создания:</b></td>
                        <td><?=$reserveInstance->getDateByKey('arbitrage_date')?></td>
                    </tr>
                    <tr class="nr-a-tbl_tr">
                        <td><b>Дата закрытия:</b></td>
                        <td><?=$reserveInstance->getDateByKey('arbitrage_date_close')?></td>
                    </tr>  
                    <tr class="nr-a-tbl_tr">
                        <td><b>Обратился:</b></td>
                        <td><?=($reserveInstance->getReserveDataByKey('arbitrage_is_emp') == 't')?'заказчик':'исполнитель'?></td>
                    </tr>
                    <tr class="nr-a-tbl_tr">
                        <td><b>Обращение:</b></td>
                        <td><?=$reserveInstance->getReserveDataByKey('arbitrage_message')?></td>
                    </tr>
                    <?php if($reserveInstance->isArbitrageClosed()): ?>
                    <tr class="nr-a-tbl_tr">
                        <td><b>Решение:</b></td>
                        <td>
                            <?php if($reserveInstance->getArbitragePriceWithOutNDFL() > 0): ?>
                                <?php if($reserveInstance->getReserveDataByKey('arbitrage_payback') > 0): ?>
                            Выплатить: <b><?=$reserveInstance->getPriceByKey('arbitrage_price', true)?></b> <br/>
                            Вернуть: <b><?=$reserveInstance->getPriceByKey('arbitrage_payback', true)?></b>
                                <?php else: ?>
                            Выплатить: <b>100% (<?=$reserveInstance->getPriceByKey('arbitrage_price', true)?>)</b>
                                <?php endif; ?>
                            <?php else: ?>
                            Вернуть: <b>100% (<?=$reserveInstance->getPriceByKey('arbitrage_payback', true)?>)</b>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr class="nr-a-tbl_tr">
                        <td><b>Разрешить написать отзыв:</b></td>
                        <td>
                            Заказчик: <b><?=$reserveInstance->getReserveDataByKey('arbitrage_allow_fb_emp')?'да':'нет'?></b> <br/>
                            Исполнитель: <b><?=$reserveInstance->getReserveDataByKey('arbitrage_allow_fb_frl')?'да':'нет'?></b>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <br/>
<?php
                endif;
                
                if($reserveInstance->reserveIsPayed()):
                
                $files = $reserveInstance->getFiles();
                $is_close = $reserveInstance->isClosed();
?>
            <form action="" method="POST" enctype="application/x-www-form-urlencoded">
            <table class="nr-a-tbl" cellspacing="5">
                <thead>
                    <tr>
                        <th colspan="3">
                            <h3><i>Файлы документов</i></h3>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($files): 
                            foreach($files as $file): 
                                if (in_array($file['doc_type'], array(80))) {
                                    continue;
                                }
                    ?>
                    <tr class="nr-a-tbl_tr">
                        <td>
                            <input type="checkbox" name="docs[]" value="<?=$file['doc_type']?>" checked="checked" />
                        </td>
                        <td>
                            <?=date('d.m.Y H:i',strtotime($file['modified']))?>
                        </td>
                        <td>
                            <a href="<?=WDCPREFIX .'/'. $file['url']?>"><?=$file['original_name']?></a>
                        </td>
                    </tr> 
                    <?php 
                            endforeach; 
                          endif; 
                    ?>
                </tbody>
                
                <tfoot>
                    <tr><td colspan="3"><br/></tr>
                    <?php if($is_close): ?>
                    <tr>
                        <td colspan="2">
                            Дата Акта и Отчета
                        </td>
                        <td>
                            <div class="b-combo">
                                <div class="b-combo__input b-combo__input_width_100">
                                    <input type="text" class="b-combo__input-text" value="" name="file_date" id="file_date" />
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endif ?>
                    <tr>
                        <td colspan="3">
                            <br/>
                            <input type="submit" name="submit" value="Обновить" />
                            <input id="create" type="checkbox" name="create" value="1" checked="checked"/>
                            <label for="create">и создать отсутствующие</label>
                        </td>
                    </tr>
                </tfoot>
            </table>
            </form>
<?php
            endif;
?>
        </td>        
        <td style="vertical-align:top;">
<?php
            $payouts = $reserveInstance->getPayoutsInfo();
            if($payouts):
                $payout_reqv = $payouts['reqv'];
                if($payout_reqv):
?>
            <table class="nr-a-tbl" cellspacing="5">
                <thead>
                    <tr>
                        <th>
                            <h3><i>Реквизиты на Выплату</i></h3>
                        </th>
                        <th style="text-align: right;">
                            <a data-reqv-show="true" href="javascript:void(0);">Показать</a>
                            <a data-reqv-hide="true" style="display: none;" href="javascript:void(0);">Скрыть</a>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="nr-a-tbl_tr">
                        <td><b>Выплата на:</b></td>
                        <td><?=$payout_reqv['pay_type_txt']?></td>
                    </tr>
                    <tr class="nr-a-tbl_tr">
                        <td><b>Создание:</b></td>
                        <td><?=$payout_reqv['date']?></td>
                    </tr>                    
                    <tr class="nr-a-tbl_tr">
                        <td><b>Обновление:</b></td>
                        <td><?=$payout_reqv['last']?></td>
                    </tr>                     
                    <?php 
                        if(is_array($payout_reqv['fields'])):
                            if (isset($payout_reqv['fields']['moderator_login']) && 
                                !empty($payout_reqv['fields']['moderator_login'])):
                    ?>
                    <tr class="nr-a-tbl_tr" data-reqv="true" style="display: none;">
                        <td><b>Модератор:</b></td>
                        <td><a href="<?="{$host}/users/{$payout_reqv['fields']['moderator_login']}"?>"><?=$payout_reqv['fields']['moderator_login']?></a></td>
                    </tr>                    
                    <?php
                            endif;
                            
                            unset($payout_reqv['fields']['moderator_login'], $payout_reqv['fields']['moderator_uid']);
                    
                            foreach($payout_reqv['fields'] as $key => $value):
                                if(empty($value)) continue;
                    ?>
                    <tr class="nr-a-tbl_tr" data-reqv="true" style="display: none;">
                        <td><b><?=$key?>:</b></td>
                        <td><?=$value?></td>
                    </tr>                     
                    <?php
                            endforeach;
                        endif; 
                    ?>
                    
                    <?php if($payout_reqv['is_allow_change_status']): ?>
                    
                    <?php if($reserveInstance->getReasonPayout()): ?>
                    <tr class="nr-a-tbl_tr">
                        <td><b>Текущая причина<br/>отказа в выплате:</b></td>
                        <td>
                            <?=$reserveInstance->getReasonPayout()?>
                        </td>
                    </tr>                    
                    <?php endif; ?>
                    
                    <tr class="nr-a-tbl_tr">
                        <td>
                            <form action="" method="POST" enctype="application/x-www-form-urlencoded">
                                <input type="submit" name="submit" value="Подтвердить выплату" />
                                <input type="hidden" name="do" value="accept_pay" />
                            </form>
                        </td>
                        <td>
                            <a href="javascript:void(0);" onclick="showDeclinePopup('decline_pay');">Отклонить выплату</a>

                            <div id="decline_pay" class="b-shadow b-shadow_center b-shadow_width_580 b-shadow__quick b-shadow_hide">
                                <div class="b-shadow__body b-shadow__body_pad_20">
                                    <h2 class="b-layout__title">Отклонить выплату</h2>
                                    <div class="b-layout b-layout_padleft_20">
                                        <form method="post" action="">
                                            <input type="hidden" name="do" value="" />
                                            <div class="b-textarea">
                                                <textarea placeholder="Введите текст причины" name="message" maxlength="500" cols="80" rows="1" class="b-textarea__textarea b-textarea__textarea_italic" id="message"></textarea>
                                            </div>
                                            <div class="b-buttons b-buttons_padtop_20">
                                                <button type="submit" class="b-button b-button_flat b-button_flat_green">Отклонить</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <span class="b-shadow__icon b-shadow__icon_close"></span>
                            </div>
                            
                            
                        </td>
                    </tr>
                    <?php endif; ?>
                    
                </tbody>
            </table>
<?php            
                endif;
                
                $payout_list = $payouts['list'];
                if($payout_list):
?>
            <br/>
            <h3><i>Запросы к ЯД на Выплату</i></h3>
            <?php if(!is_release()): ?>
            <p>* на тесте статусы выплат не меняются они всегда "новый"</p>
            <?php endif; ?>
            <table class="nr-a-tbl" cellspacing="5">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Сумма</th>
                        <th>Статус</th>
                        <th>Ошибка</th>
                        <th>Создание</th>
                        <th>Обновление</th>
                        <th>Попыток</th>
                        <th>Инфо</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($payouts['list'] as $item): ?>
                    <tr class="nr-a-tbl_tr">
                        <td><?=$item['id']?></td>
                        <td><?=$item['price']?></td>
                        <td><?=$item['status_txt']?> (<?=$item['status']?>)</td>
                        <td><?=$item['error']?></td>
                        <td><?=$item['date']?></td>
                        <td><?=$item['last']?></td>
                        <td><?=$item['cnt']?></td>
                        <td><?=$item['techmessage']?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
            <?php
                $payout_log = $payouts['log'];
                if($payout_log):
            ?>
            <br/>
            <h3><i>Лог ошибок запросов на Выплату</i></h3>
            <table class="nr-a-tbl" cellspacing="5">
                <thead>
                    <tr>
                        <th>Дата</th>
                        <th>Сообщение</th>
                    </tr>
                </thead>
                <tbody>   
                    <?php foreach($payout_log as $log): ?>
                    <tr class="nr-a-tbl_tr">
                        <td>
                            <?=date('d.m.Y H:i',strtotime($log['date']))?>
                        </td>
                        <td>
                            <?=$log['message']?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>            
            <?php endif; ?> 
<?php
            endif;
            
            
            $paybackList = $reserveInstance->getPaybackInfo();
            if($paybackList):
?>
            <br/>
            <h3><i>Запрос к ЯД на Возврат</i></h3>
            <table class="nr-a-tbl" cellspacing="5">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Сумма</th>
                        <th>Статус</th>
                        <th>Ошибка</th>
                        <th>Создание</th>
                        <th>Обновление</th>
                        <th>Попыток</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($paybackList as $item): ?>
                    <tr class="nr-a-tbl_tr">
                        <td><?=$item['id']?></td>
                        <td><?=$item['price']?></td>
                        <td><?=$item['status_txt']?> (<?=$item['status']?>)</td>
                        <td><?=$item['error']?></td>
                        <td><?=$item['date']?></td>
                        <td><?=$item['last']?></td>
                        <td><?=$item['cnt']?></td>
                    </tr>
                    <?php endforeach; ?> 
                </tbody>
            </table>  
            
            <?php if(!$reserveInstance->isStatusBackPayed()): ?>
            <br/>
            <form action="" method="POST" enctype="application/x-www-form-urlencoded">
                <input type="submit" name="submit" value="Подтвердить возврат" />
                <input type="hidden" name="do" value="accept_back" />
            </form>
            <?php endif; ?>
            
<?php
            elseif(!$reserveInstance->isReserveByService() && 
                    $reserveInstance->isStatusBackAllowChange()):
?>
            <br/>
            
            <table class="nr-a-tbl" cellspacing="5">
               <thead>
                   <tr>
                       <th colspan="2">
                            <h3><i>Запрос на Возврат по безналу</i></h3>
                       </th>
                    </tr>
                </thead>
                <tbody>
                    
                    <?php if($reserveInstance->getReasonPayback()): ?>
                    <tr class="nr-a-tbl_tr">
                        <td><b>Текущая причина<br/>отказа возврата:</b></td>
                        <td>
                            <?=$reserveInstance->getReasonPayback()?>
                        </td>
                    </tr>                    
                    <?php endif; ?>
                    
                    <tr class="nr-a-tbl_tr">
                        <td>
                            <form action="" method="POST" enctype="application/x-www-form-urlencoded">
                                <input type="submit" name="submit" value="Подтвердить возврат" />
                                <input type="hidden" name="do" value="accept_back" />
                            </form>
                        </td>
                        <td>
                            <a href="javascript:void(0);" onclick="showDeclinePopup('decline_back');">Отклонить возврат</a>

                            <div id="decline_back" class="b-shadow b-shadow_center b-shadow_width_580 b-shadow__quick b-shadow_hide">
                                <div class="b-shadow__body b-shadow__body_pad_20">
                                    <h2 class="b-layout__title">Отклонить возврат</h2>
                                    <div class="b-layout b-layout_padleft_20">
                                        <form method="post" action="">
                                            <input type="hidden" name="do" value="" />
                                            <div class="b-textarea">
                                                <textarea placeholder="Введите текст причины" name="message" maxlength="500" cols="80" rows="1" class="b-textarea__textarea b-textarea__textarea_italic" id="message"></textarea>
                                            </div>
                                            <div class="b-buttons b-buttons_padtop_20">
                                                <button type="submit" class="b-button b-button_flat b-button_flat_green">Отклонить</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <span class="b-shadow__icon b-shadow__icon_close"></span>
                            </div>
                            
                            
                        </td>
                    </tr>
                    
                </tbody>
            </table>                      
<?php            
            endif;
?>
        </td>
    </tr>
</table>
<script type="text/javascript">
    Locale.use('ru-RU');
    
    $$('[data-reqv-show]').addEvent('click', function(){
        this.hide();
        $$('[data-reqv]').show('table-row');
        $$('[data-reqv-hide]').show('inline');
        return false;
    });
    
    $$('[data-reqv-hide]').addEvent('click', function(){
        this.hide();
        $$('[data-reqv]').hide();
        $$('[data-reqv-show]').show('inline');
        return false;
    });
    

    var fileDatePicker = new Picker.Date($('file_date'), {
        timePicker: false,
        format: "%d.%m.%Y",
        positionOffset: {x: 0, y: 0}
    });
    
    
    function showDeclinePopup($do)
    {
        var popup = $($do);
        if (popup) {
            popup.getElement('input[name=do]').set('value', $do);
            popup.removeClass('b-shadow_hide');
        }
    }
    
</script>