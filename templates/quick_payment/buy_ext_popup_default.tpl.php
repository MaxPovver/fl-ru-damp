<?php
/**
 * Шаблон поумолчанию popup-окна "быстрой" оплаты
 */
?>
<?php 
            if(!empty($items)): 
?>
            <form>
            <div class="b-radio b-radio_padleft_20">
                <?php foreach($items as $key => $item):
                        $input_id = "{$item['name']}_{$key}";
                ?>
                <div class="b-radio__item b-radio__item_padbot_10 b-radio__item_inline-block b-radio__item_width_200">
                    <input<?=(@$item['checked'])?' checked':''?><?=(@$item['disabled'])?' disabled':''?> type="radio" onclick="" id="<?=$input_id?>" name="<?=@$item['name']?>" value="<?=@$item['value']?>" class="b-radio__input" />
                    <?php if(@$item['disabled'] && @$item['checked']): ?>
                    <input type="hidden" name="<?=@$item['name']?>" value="<?=@$item['value']?>" />
                    <?php endif; ?>
                    <label class="b-radio__label b-radio__label_fontsize_13 b-radio__label_margtop_-2<?=(@$item['checked'])?' b-radio__label_color_6db335':''?>" for="<?=$input_id?>">
                        <span id="" class="b-layout__bold">
                            <?=@$item['title']?>
                        </span>
                        <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_inline_iphone<?=(@$item['checked'])?' b-layout__txt_color_6db335':''?>">
                            <?=@$item['subtitle']?>&nbsp;
                        </div>
                    </label>
                </div>
                <?php endforeach; ?>
            </div>
            </form>
<?php
            endif;