<?php

/**
 * Шаблон виджета TServiceOrderDebtMessage
 * Сообщение о возможной блокировки ТУ из-за не погашения долга ЛС
 */

?>
<div class="b-fon">
    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_35 b-fon__body_fontsize_13 b-fon__body_bg_ffeeeb">
        <span class="b-icon b-icon_sbr_rattent b-icon_margleft_-25"></span>
        <?php if($debt_info['is_blocked'] == 't') {?>
        
        Сожалеем, но мы вынуждены были скрыть ваши Типовые услуги в каталоге и профиле, без возможности продавать их и получать отзывы.<br/>
        Как только задолженность на Личном счете в аккаунте будет погашена, мы разблокируем ваши услуги.
        
        <?php }else{ ?>
        
        Обращаем внимание, что <span class="b-layout__bold">до <?php echo date('d.m.Y', strtotime($debt_info['date'])) ?></span> (включительно) вам необходимо погасить задолженность на Личном счете в аккаунте.<br/> 
        Иначе мы вынуждены будем скрыть ваши Типовые услуги в каталоге и профиле, без возможности продавать их и получать отзывы.
        
        <?php } ?>

        <br/>
        <a class="b-layout__link" href="/bill/">Пополнить счет и погасить задолженность</a>
        
    </div>
</div>