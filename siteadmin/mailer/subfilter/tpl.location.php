<input type="hidden" id="flocation" name="flocation" value="<?= ( !empty($message['flocation'])? 1 : 0 )?>">
<div class="b-fon-subfilter b-fon b-fon_width_full b-fon_padbot_15 <?= !empty($message['flocation'])? "" :"b-fon_hide"; ?>">
    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf i-button">
        <a class="b-button b-button_admin_del b-button_float_right close-block " href="#" onclick="$('flocation').set('value', 0);"></a>
        <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_20 b-layout__txt_fontsize_13 b-layout__txt_float_left">География</div>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_10" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120">
                    <div class="b-layout__txt">Страна</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-select">
                        <select class="b-select__select b-select__select_width_300" id="pf_country" name="country" onChange="updateCitys(this.value)">
                            <option value="">Любая</option>
                            <?php if($countries) { ?>
                            <?php foreach($countries as $id=>$country) { ?>
                            <option value="<?= $id?>" <?= ($message['flocation']['country'] == $id ? "selected" : "");?>><?= $country?></option>
                            <?php } //foreach?>
                            <?php }//if?>
                        </select>
                    </div>
                </td>
            </tr>
        </table>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_10" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120">
                    <div class="b-layout__txt">Город</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-select" id="frm_city">
                        <select class="b-select__select b-select__select_width_300" id="pf_city" name="city"> 
                            <option value="">Все города</option>
                            <?php if($citys) { ?>
                            <?php foreach($citys as $id=>$city) { ?>
                            <option value="<?= $id?>" <?= ($message['flocation']['city'] == $id ? "selected" : "");?>><?= $city?></option>
                            <?php } //foreach?>
                            <?php }//if?>
                        </select>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>