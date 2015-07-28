<?php
if(!defined('IN_STDF')) 
{ 
    header("HTTP/1.0 404 Not Found");
    exit;
}
?>     
      <div id="error_<?php echo $field_id ?>" class="error-message i-shadow<?php if(empty($error_text)) { ?> b-shadow_hide<?php } ?> <?php echo $visible_css_selector ?>">
        <div class="b-shadow b-shadow_m b-shadow_top_0 b-shadow_zindex_2">
             <div class="b-shadow__body b-shadow__body_pad_5_10 b-shadow__body_bg_fff">
              <div id="error_txt_<?php echo $field_id ?>" class="b-txt_fs_11 b-layout__txt_color_c10600 b-txt"><?php echo $error_text ?></div>
         </div>
         <span class="b-shadow__icon b-shadow__icon_nosik"></span>
        </div>
      </div>