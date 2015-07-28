<?php

?>
<h1 class="b-layout__title b-layout__title_fs30 b-layout__title_color_333 b-layout__title_padbot_37">
    Укажите специализацию проекта,<br/>
    чтобы мы подобрали <strong>подходящих исполнителей</strong>
</h1>
<div class="b-vertical-select" data-element-vertical-select="role">
    <div class="b-vertical-select__optgroup">
        <div class="b-vertical-select__title">
            Категория
        </div>        
        <div class="b-vertical-select__optgroup_wrapper">
         <?php 
            $itemblocks = array();
            foreach ($professions as $profession): 
                
                if ($profession['gid'] <= 0):
                    continue;
                endif;
                
                $is_exist = isset($itemblocks[$profession['gid']]);
                $itemblocks[$profession['gid']][] = $profession;
                if (!$is_exist):
         ?>
            <a data-optgroup-link="<?=$profession['gid']?>" 
               class="b-vertical-select__optgroup__link <?php if($default_group == $profession['gid']):?>active<?php endif; ?>" href="javascript:void(0);">
                    <?=$profession['gname']?>
            </a>
         <?php  endif; ?>
         <?php endforeach; ?>
        </div>
    </div>
    <div class="b-vertical-select__option">
        <div data-option-content="true" class="b-vertical-select__option_wrapper">
            <div class="b-vertical-select__title">
                Специализация
            </div>            
            <form enctype="application/x-www-form-urlencoded" 
                  method="post" 
                  action=".">
<?php
            foreach ($itemblocks as $gid => $itemblock):
?>        
        <div data-optgroup-block="<?=$gid?>" 
             class="b-radio b-radio_layout_vertical <?php if ($default_group != $gid): ?>g-hidden<?php endif; ?>">
<?php
                foreach ($itemblock as $element):
?>
                <div class="b-radio__item">
                    <input id="el-category-<?=$element['id']?>" 
                           type="radio" 
                           class="b-radio__input" 
                           name="subcategory" 
                           value="<?=$element['id']?>" <?php if ($default_spec == $element['id']): ?>checked="checked"<?php endif; ?>/>
                    <label title="<?=$element['name']?>" class="b-radio__label <?php if ($default_spec == $element['id']): ?>active<?php endif; ?>" 
                           for="el-category-<?=$element['id']?>">
                        <?=$element['name']?>
                    </label>
                </div>
<?php
                endforeach;
?>
        </div>
<?php
            endforeach;
?>
            
            <div class="b-buttons b-buttons_center b-buttons_padtop_40 b-buttons_float_left b-buttons_width_full">
                <button type="submit" class="b-button b-button_nowrap b-button_flat b-button_flat_green b-button_flat_med">
                    Далее <span class="b-icon b-icon__rarr b-icon_margleft_20 b-icon_top_2"></span>
                </button>
            </div>            
            
            <input type="hidden" name="category" value="0" />    
            </form>
        </div>
    </div>
</div>