<?php
if(!defined('IN_STDF')) 
{ 
    header("HTTP/1.0 404 Not Found");
    exit;
}

$class_bg = ($type == 'error')?'b-fon__body_bg_ffeeeb':'b-fon__body_bg_f0ffdf b-fon__body_bordbot_dfedcf';
$class_ico = ($type == 'error')?'b-icon b-icon_sbr_rattent b-icon_margleft_-25':'b-icon b-icon_sbr_gok b-icon_margleft_-25';
$message = (is_array($message))?$message:array($message);

?>
<div class="b-fon b-fon_width_full b-fon_padbot_17" onclick="$(this).dispose()">
    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_35 b-fon__body_fontsize_13 <?=$class_bg?>">
        <span class="<?=$class_ico?>"></span>
        <?php
                foreach($message as $mes):
        ?>                    
                    <?=$mes;?><br>
        <?php            
                endforeach;
        ?>   
    </div>
</div>