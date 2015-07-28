<?php if(!empty($bill->last_history)) { ?>
    <div class="b-layout b-layout_pad_10 b-layout_bord_e6 b-layout_margbot_10">
        <h3 class="b-layout__h3">История операций</h3>
        <?php foreach($bill->last_history as $last_history) { ?>
        <div class="b-layout__txt b-layout__txt_fontsize_15 <?= ($last_history['status'] == 'cancel' ? "b-layout__txt_color_808080" : "")?>" id="res<?=$last_history['id']?>">
                <?php if($last_history['op_code'] != 2000) {?>
                <span class="b-layout__txt b-layout__txt_padleft_10 b-layout__txt_float_right <?= ( $last_history['ammount'] < 0 ? "b-layout__txt_color_c10600" : "b-layout__txt_color_6db335") ?> b-layout__txt_fontsize_15"><?= ( $last_history['ammount'] > 0?"+":"" ) . $last_history['ammount']?></span>
                <?php } elseif($last_history['status'] == 'reserve') { //if?>
                &nbsp;
                <?php } else { //if?>
                <span class="b-layout__txt b-layout__txt_padleft_10 b-layout__txt_float_right b-layout__txt_color_c10600"><div class="b-layout__txt b-layout__txt_fontsize_15">&mdash;</div></span>
                <?php } ?>
                <?= $last_history['op_name']?>
            </div>
            <div class="b-layout__txt b-layout__txt_padbot_15 b-layout__txt_fontsize_11 b-layout__txt_color_808080"><?= date('d.m.Y H:i', strtotime($last_history['op_date']))?></div>
        <?php }//foreach?>
        <div class="b-layout__txt b-layout__txt_fontsize_15"><a class="b-layout__link" href="/bill/history/?period=3">Вся история</a></div>
    </div>
<?php }?>