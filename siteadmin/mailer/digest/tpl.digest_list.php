<input type="hidden" name="position[<?=$this->__toString()?>]<?= $this->isCreated()? "[]" : ""; ?>" value="<?= $this->getPosition();?>">
<div class="b-layout__txt b-layout__txt_lineheight_1 b-layout__txt_inline-block b-layout__txt_valign_bot ">
    <div><a href="javascript:void(0)" class="upButton"><img src="/images/ico_up.gif" alt="" /></a></div>
    <div><a href="javascript:void(0)" class="downButton"><img src="/images/ico_down.gif" alt="" /></a></div>
</div>
<div class="b-check b-check_inline-block b-check_padleft_10">
    <input id="b-check22" class="b-check__input check_select_block" name="<?= $this->__toString();?>Check<?= $this->isCreated()? "[]" : ""; ?>" type="checkbox" value="1" <?= $this->isCheck() ? "checked" : ""?> autocomplete="off"/>
    <label for="b-check22" class="b-check__label b-check__label_fontsize_15"><?= $this->getTitle(); ?></label>
</div>
<div class="b-fon b-fon_padbot_30">
    <div class="b-fon__body b-fon__body_pad_20">
        <table class="b-layout__table b-layout__table_width_full" border="0" cellpadding="0" cellspacing="0">
            <? for( $i=0;$i<$this->getListSize();$i++) { ?>
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_150">
                    <div class="b-layout__txt b-layout__txt_padtop_7 b-layout__txt_fontsize_11 b-layout__txt_lineheight_13"><?= $i == 0 ? $this->getTitleField() : ""; ?></div>
                </td>
                <td class="b-layout__right">
                    <div class="b-combo b-combo_padbot_10 b-input-hint">
                        <label class="b-input-hint__label b-input-hint__label_overflow_hidden" for="<?= $this->__toString();?>Link_<?= $i?>"><?= ($i==0 && !isset($this->links[$i]) ? $this->hint : ""); ?></label>
                        <div class="b-combo__input b-combo__input_width_530 <?= $this->_error[$i] ? "b-combo__input_error" : ""?>">
                            <input id="<?= $this->__toString();?>Link_<?= $i?>" class="b-combo__input-text <?= isset($this->links[$i]) ? "" : "b-combo__input-text_color_a7"?>" type="text" name="<?= $this->__toString();?>Link[]" value="<?= isset($this->links[$i]) ? $this->links[$i] : '' ;?>" autocomplete="off" />
                        </div>
                    </div>
                    <?/*
                    <div class="b-combo b-combo_padbot_10">
                        <div class="b-combo__input">
                            <div class="b-input-hint">
                            <input class="b-combo__input-text b-combo__input-text_color_a7" type="text" name="<?= $this->__toString();?>Link[]" value="<?= ($i==0 && !isset($this->links[$i])? $this->hint : ( isset($this->links[$i]) ? $this->links[$i] : '' )) ;?>" size="80" />
                        </div>
                    </div>*/?>
                    <?if($i+1 == $this->getListSize() && $this->isAutoComplete()) { ?>
                    <a class="b-button b-button_rectangle_color_transparent" href="javascript:void(0)" onclick="xajax_setAutoComplete('<?= $this->__toString();?>', $(this).getParent('span').getElement('input[type=checkbox]').checked)">
                        <span class="b-button__b1">
                            <span class="b-button__b2">
                                <span class="b-button__txt">Заполнить автоматически</span>
                            </span>
                        </span>
                    </a>
                <? }//if?>
                </td>
                <td class="b-layout__one b-layout__one_padleft_10">
                <? if($this->isAdditionFields()) { ?>
                <a href="javascript:void(0)" class="b-button <?= ($i == 0? "b-button_hide" : "");?> b-button_admin_del"></a>
                <? }//if?></td>
            </tr>
            <? }//for?>
        </table>
        <? if($this->isAdditionFields()) { ?>
        <div class="b-layout__txt b-layout__txt_padleft_150 block_add_fld"><a class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_fontsize_11" href="javascript:void(0)">Добавить</a></div>
        <? }//if?>
    </div>
    <div class="b-layout__txt b-layout__txt_float_right block_create_action">
        <a class="b-layout__link b-layout_hide b-layout__link_bordbot_dot_0f71c8 b-layout__link_fontsize_11" href="javascript:void(0)">Добавить блок</a>
    </div>
</div> 