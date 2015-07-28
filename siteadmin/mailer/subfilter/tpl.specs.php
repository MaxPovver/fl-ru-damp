<input type="hidden" id="fspec" name="fspec" value="<?= ( !empty($message['fspec'])? 1 : 0 )?>">
<div class="b-fon-subfilter b-fon b-fon_width_full b-fon_padbot_15 <?= !empty($message['fspec']) ? "" : "b-fon_hide"; ?>">
    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf i-button">
        <a class="b-button b-button_admin_del b-button_float_right close-block " href="#" onclick="$('fspec').set('value', 0);"></a>
        <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_20 b-layout__txt_fontsize_13 b-layout__txt_float_left">Специализация</div>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_10" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120">
                    <div class="b-layout__txt">Основная</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-select">
                        <select name="fspec_orig" class="b-select__select b-select__select_width_300">
                            <option value="0">Любая</option>
                            <?php if( $specs ) { ?>
                            <?php foreach($specs as $key=>$value) { ?>
                            <option value="<?= $value['id'];?>" <?= ($value['id'] == $message['fspec']['spec_orig']?"selected":"")?>><?=$value['name'];?></option>
                            <?php }//foreach?>
                            <?php }//if?>
                        </select>
                    </div>
                </td>
            </tr>
        </table>
        <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_10" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_120">
                    <div class="b-layout__txt">Дополнительная</div>
                </td>
                <td class="b-layout__right">
                    <div class="b-select">
                        <select name="fspec_dspec[0]" class="b-select__select b-select__select_width_300">
                            <option value="0">Любая</option>
                            <?php if( $specs ) { ?>
                            <?php foreach($specs as $key=>$value) { ?>
                            <option value="<?= $value['id'];?>" <?= ($value['id'] == $message['fspec']['specs'][0]['spec']?"selected":"")?>><?=$value['name'];?></option>
                            <?php }//foreach?>
                            <?php }//if?>
                        </select>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>