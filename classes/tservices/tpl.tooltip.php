<?php
if(!defined('IN_STDF')) 
{ 
    header("HTTP/1.0 404 Not Found");
    exit;
}
?> 
<span class="b-page__desktop">                   
    <div class="b-filter__body i-shadow i-shadow_inline-block i-shadow_margleft_5">
        <a class="b-filter__link b-shadow__icon b-shadow__icon_quest2 b-shadow__icon_margbot_-2" href="javascript:void(0)"></a>
        <div class="b-shadow b-filter__toggle b-filter__toggle_hide">
            <div class="b-shadow__body b-shadow__body_pad_15 b-shadow_width_270 b-shadow__body_bg_fff">
                <div class="b-shadow__txt b-shadow__txt_fontsize_11 b-shadow__txt_normal">
                    <?php echo $message ?>
                </div>
            </div>
        </div>
    </div>
</span>