<form method="post">
<div class="b-form b-layout_pad_10 form-in">
    <input name="formname" type="hidden" value="promo_codes"/>
    <div class="b-layout b-layout_padbot_10">
        <div class="b-form__name b-form__name_width_70 b-form__name_padtop_7">Код</div>
        <div class="b-combo b-combo_inline-block">
            <div class="b-combo__input b-combo__input_width_125">
                <input name="code" class="b-combo__input-text" type="text" size="80" value="<?=trim(@$card['code'])?>">
            </div>
        </div>
    </div>
    <div class="b-layout b-layout_padbot_10">
        <div class="b-form__name b-form__name_width_70 b-form__name_padtop_7">Период</div>
        <div class="b-combo b-combo_inline-block">
            <div class="b-combo__input b-combo__input_calendar b-combo__input_width_125 b-combo__input_arrow-date_yes use_past_date">
                <input id="date_s" name="date_s" class="b-combo__input-text" type="text" size="80" value="<?=@$card['date_start']?dateFormat('Y-m-d', @$card['date_start']) : ''?>">
                <span class="b-combo__arrow-date"></span> 
            </div>
        </div>

        <div class="b-layout__txt b-layout__txt_padtop_3 b-layout__txt_inline-block b-layout__txt_valign_top">&#160;—&#160;</div>

        <div class="b-combo b-combo_inline-block">
            <div class="b-combo__input b-combo__input_calendar b-combo__input_width_125 b-combo__input_arrow-date_yes use_past_date">
                <input id="date_e" name="date_e" class="b-combo__input-text" type="text" size="80" value="<?=@$card['date_end']?dateFormat('Y-m-d', @$card['date_end']) : ''?>">
                <span class="b-combo__arrow-date"></span> 
            </div>
        </div>
    </div>
    <div class="b-layout b-layout_padbot_10">
        <div class="b-form__name b-form__name_width_70 b-form__name_padtop_7">Скидка</div>
        <div class="b-combo b-combo_inline-block">
            <div class="b-combo__input b-combo__input_width_125">
                <input name="discount" class="b-combo__input-text" type="text" size="80" value="<?=@$card['discount_percent'] > 0 ? @$card['discount_percent'] : @$card['discount_price']?>">
            </div>
        </div>
        <select name="is_percent">
            <option value="0"<?=@$card['discount_price'] > 0 ? "selected" : ''?>>руб.</option>
            <option value="1"<?=@$card['discount_percent'] > 0 ? "selected" : ''?>>%</option>
        </select>
    </div>
    
    <div class="b-layout b-layout_padbot_10">
        <div class="b-form__name b-form__name_width_70 b-form__name_padtop_7">Количество</div>
        <div class="b-combo b-combo_inline-block">
            <div class="b-combo__input b-combo__input_width_125">
                <input name="count" class="b-combo__input-text" type="text" size="80" value="<?=@$card['count']?>">
            </div>
        </div>
    </div>
    <div class="b-layout b-layout_padbot_10">
        <div class="b-form__name b-form__name_width_60 b-form__name_padtop_7">Услуги</div>
        
        <div class="b-layout b-layout_inline-block b-layout_width_670 b-layout_padbot_10">
            <?php foreach ($services as $key=>$service):?>
            <div class="b-layout__txt b-layout__txt_width_185 b-layout_float_left b-layout__txt_padleft_10">
                <input type="checkbox" name="service[]" value="<?=$key?>" id="service<?=$key?>" <?=!@$card['services'] || array_search($key, @$card['services'])===false ? '' : ' checked'?> />
                <label for="service<?=$key?>"><?=$service?></label>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    


    <div class="b-layout__txt b-layout__txt_color_c10600 b-layout__txt_margbot_10"><?=@$error?></div>
    <button type="submit" class="b-button_margleft_15">
            <span class="b-button__txt">Сохранить</span>
    </button>
    
</div>
</form>