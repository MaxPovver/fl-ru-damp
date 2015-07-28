<input type="hidden" name="position[<?=$this->__toString()?>]<?= $this->isMore(); ?>" value="<?= $this->getPosition();?>">
<input type="hidden" class="ClassMain" name="<?=$this->__toString()?>Main<?= $this->isMore(); ?>" value="<?= ($this->isMain() ? '1' : '0'); ?>">
<div class="b-layout__txt b-layout__txt_lineheight_1 b-layout__txt_inline-block b-layout__txt_valign_bot ">
    <div><a href="javascript:void(0)" class="upButton"><img src="/images/ico_up.gif" alt="" /></a></div>
    <div><a href="javascript:void(0)" class="downButton"><img src="/images/ico_down.gif" alt="" /></a></div>
</div>
<div class="b-check b-check_inline-block b-check_padleft_10">
    <input id="b-check22" class="b-check__input check_select_block" autocomplete="off" name="<?= $this->__toString();?>Check<?= $this->isMore(); ?>" type="checkbox" value="1" <?= $this->isCheck() ? "checked" : ""?>/>
    <label for="b-check22" class="b-check__label b-check__label_fontsize_15"><?= $this->isMain() ? $this->getTitle() : "Дополнительный блок"?></label>
</div>
<div class="b-fon b-fon_padbot_30">
    <div class="b-fon__body b-fon__body_pad_20">
        <table class="b-layout__table b-layout__table_width_full" border="0" cellpadding="0" cellspacing="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_padbot_10 b-layout__left_width_150"><div class="b-layout__txt b-layout__txt_padtop_7 b-layout__txt_fontsize_11 b-layout__txt_lineheight_13">Название:</div></td>
                <td class="b-layout__right b-layout__right_padbot_10">
                    <div class="b-combo">
                        <div class="b-combo__input">
                            <input class="b-combo__input-text" type="text" value="<?= $this->name; ?>" size="80" name="<?= $this->__toString();?>Name<?= $this->isMore(); ?>" autocomplete="off" />
                        </div>
                    </div>                            
                </td>
            </tr>
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_padbot_10 b-layout__left_width_150"><div class="b-layout__txt b-layout__txt_padtop_7 b-layout__txt_fontsize_11 b-layout__txt_lineheight_13">Ссылка:</div></td>
                <td class="b-layout__right b-layout__right_padbot_10">
                    <div class="b-combo">
                        <div class="b-combo__input <?= $this->_error['link'] ? "b-combo__input_error" : ""?>">
                            <input class="b-combo__input-text" type="text" value="<?= $this->link; ?>" size="80" name="<?= $this->__toString();?>Link<?= $this->isMore(); ?>" autocomplete="off" />
                        </div>
                    </div>                            
                </td>
            </tr>
            <tr class="b-layout__tr">
                <td class="b-layout__left" colspan="2">
                    <?php if($this->isWysiwyg()) { ?>
                    <textarea id="<?= $this->__toString();?>Descr_<?= $this->getPosition();?>" class="ckeditor" cols="" rows="" name="<?= $this->__toString();?>Descr<?= $this->isMore(); ?>" ><?= $this->text; ?></textarea>
                    <?php } else {// if?>
                    <div class="b-textarea">
                        <textarea class="b-textarea__textarea" cols="" rows="" name="<?= $this->__toString();?>Descr<?= $this->isMore(); ?>" autocomplete="off"><?= $this->text; ?></textarea>
                    </div>
                    <?php }//else?>
                </td>
            </tr>
        </table>
    </div>
    <div class="b-layout__txt b-layout__txt_float_right block_create_action">
        <a class="b-layout__link <?= $this->isMain() ? "b-layout__link_bordbot_dot_0f71c8" : "b-layout__link_dot_c10600"?> b-layout__link_fontsize_11" href="javascript:void(0)"><?= $this->isMain() ? "Добавить блок" : "Удалить блок"?></a>
    </div>
</div> 