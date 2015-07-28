<form method="get" id="arbitrage_form" data-arbitrage-form="1">
    <input type="hidden" name="order_id" value="<?= $order_id ?>" />
    <div class="b-layout__txt b-layout__txt_padbot_15">
        <div class="b-layout__left b-layout__left_width_300 b-layout__left_float_left">
            <span class="b-layout__txt b-layout__txt_padtop_10">Сумма выплаты исполнителю</span>
            <div class="b-combo b-combo_inline-block">
                <div class="b-combo__input b-combo__input_width_50">
                    <input class="b-combo__input-text validate-numeric" name="price" type="text" size="6" value="0" id="arbitrage_sum_frl">
                </div>
            </div>                        
            <span class="b-layout__txt b-layout__txt_padtop_10">руб.</span>
        </div>
        <span class="b-layout__txt b-layout__txt_padtop_10">
            <input type="checkbox" name="allow_fb_frl" id="allow_fb_frl" checked="checked" />
            <label for="allow_fb_frl">Исполнитель может оставить отзыв</label>
        </span>
        
    </div>
    <div class="b-layout__txt b-layout__txt_padbot_15">
        <div class="b-layout__left b-layout__left_width_300 b-layout__left_float_left">
            Сумма возврата заказчику <strong id="arbitrage_sum_emp"><?= $price ?></strong> руб.
        </div>
        <span class="b-layout__txt b-layout__txt_padtop_10">
            <input type="checkbox" name="allow_fb_emp" id="allow_fb_emp" checked="checked" />
            <label for="allow_fb_emp">Заказчик может оставить отзыв</label>
        </span>
    </div>
    
    <div class="b-buttons b-buttons_padbot_15">
        <a class="b-button b-button_flat b-button_flat_green" 
           href="javascript:void(0);" 
           onclick="window.arbitrage_form.submit();" 
           id="arbitrage_apply">
            Вынести решение
        </a>
        <span class="b-buttons__txt b-button__txt_padbot_10_ipad">&#160; или &#160;</span>
        <a class="b-button" href="javascript:void(0);" id="arbitrage_cancel">отменить Арбитраж</a>
    </div>
</form>