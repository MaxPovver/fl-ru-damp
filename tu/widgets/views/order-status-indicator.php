<?php
if(!$is_ajax):
?>
<div id="order_status_indicator">
<?php
endif;

if(!empty($stages_list)):
?>
<div class="b-tabs b-tabs_margbot_30 b-tabs_margtop_75">
<?php
    foreach($stages_list as $paytype => $stages):
?>
    <div id="order_status_indicator_<?=$paytype?>"<?php if($active_paytype != $paytype): ?> class="b-fon__cont_hide"<?php endif; ?>>
<?php 
    $idx = 0;
    $stage_cnt = count($stages) - 1;
    $was_active = false;
    foreach($stages as $stage):
        $is_last = $stage_cnt == $idx++;
        $is_exclude = isset($stage['exclude']) && $stage['exclude'] === true;
        if($is_exclude) continue;
        
        $is_adv = isset($stage['adv']);
        $is_hide = isset($stage['hide']);
        $is_break = isset($stage['break']);
        
        $org_stage_status = $stage['status'];
        if(!is_array($stage['status'])) $stage['status'] = (array)$stage['status'];

        $is_active = in_array($active_status, $stage['status'], true) || 
                     ($active_status === $org_stage_status);

        if(!$is_active && ($is_adv || $is_break || $is_hide)) continue;
        
        if($is_active && !$is_break) $was_active = true;
        $is_texts = $was_active && isset($stage['texts']);
        
        if($is_break) {
            $is_active = false; 
            $is_last = true;
        }
        
        $text_color = ($is_active)?'6db335':'71';
        $icon = ($is_active)?($is_adv?'atten':'run'):(!$was_active?'done':'');
?>
    <div class="b-tabs__item<?php if($is_active):?> b-tabs__item_current<?php endif; ?>">
       <table class="b-layout__table b-layout__table_width_full">
          <tr class="b-layout__tr">
             <td class="b-layout__td b-layout__td_width_40 b-layout__td_valign_mid b-layout__td_center<?php if(!$is_last): ?> b-layout__td_bordbot_e6<?php endif; ?> b-layout__td_padtb_10">
                 <?php if(!empty($icon)): ?>
                 <span class="b-icon b-icon__tabs_<?=$icon?>"></span>
                 <?php endif; ?>
             </td>
             <td class="b-layout__td<?php if(!$is_last): ?> b-layout__td_bordbot_e6<?php endif; ?> b-layout__td_padtb_10">
                <div class="b-layout__txt b-layout__txt_uppercase b-layout__txt_color_<?=$text_color?><?php if($is_texts): ?> b-layout__txt_padbot_5<?php endif ?> b-layout__txt_bold">
                    <?=$stage['title']?>
                </div>
                <?php
                    if($is_texts):
                        if(is_array($stage['texts'])): 
                            $cnt = count($stage['texts']) - 1;
                            foreach($stage['texts'] as $key => $text): 
                ?>
                <div class="b-layout__txt b-layout__txt_color_<?=$text_color?> b-layout__txt_fontsize_11<?php if($key < $cnt):?> b-layout__txt_padbot_5<?php endif; ?>">
                        <?=$text?>
                </div>
                <?php       endforeach; 
                        else: 
                ?>
                <div class="b-layout__txt b-layout__txt_color_<?=$text_color?> b-layout__txt_fontsize_11">
                        <?=$stage['texts']?>
                </div>                
                <?php 
                        endif;
                    endif;
                ?>
             </td>
          </tr>
       </table>
    </div>
<?php 
    if($is_break) break;
    endforeach; 
?>
    </div>
<?php
    endforeach;
?>    
</div>
<?php 
endif;

if(!$is_ajax):
?>    
</div>
<?php
endif;