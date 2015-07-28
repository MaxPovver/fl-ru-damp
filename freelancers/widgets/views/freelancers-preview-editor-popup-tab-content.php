<?php

/**
 * Ўаблон контентной области попапа выбора превью
 */

?>
        <aside class="b-layout__side b-layout__side_content">
<?php
            foreach($value['elements'] as $element): 
                $element_id = "{$key}_{$element->id}";
?>
            <label for="<?=$element_id?>" 
                   class="i-pic i-pic_port i-pic_port_z-index_inherit i-pic_pad_10 i-pic_height_220 i-pic_margbot_30 i-pic_bord_green_hover i-pic_pointer">
<?php
                if ($element->isPortfolio() && $element->isText()):
?>
                <?=$element->getDescr()?>
<?php
                else:
?>
                <div class="b-layout b-layout_relative">
                    <?php if($image_tag = $element->getThumbnail()): ?>
                        <?=$image_tag?>
                    <?php elseif ($image_url = $element->getThumbnailUrl()): ?>
                    <img width="200" height="150" class="b-pic" src="<?=$image_url?>" alt="" title=""/>
                    <?php else: ?>
                    <div class="b-pic b-pic_no_img b-pic_w200_h150 b-pic_bg_f2"></div>
                    <?php endif; ?>
                </div>
<?php
                endif;
?>
                <div class="b-layout__txt b-layout__txt_padtop_10 b-layout_overflow_hidden">
                    <?= $element->getTitle(); ?>
                </div>
                
                <input id="<?=$element_id?>" type="radio" name="value" value="<?=$element_id?>" class="g-hidden" />
            </label>
<?php 
            endforeach;
?>
        </aside>
<?php
        echo new_paginator2($value['page'], $value['pages'], 4, "%stab={$key}&page=%d%s");