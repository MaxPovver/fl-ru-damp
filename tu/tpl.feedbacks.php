<?php
if(!defined('IN_STDF')) 
{ 
    header("HTTP/1.0 404 Not Found");
    exit;
}
?>
    <h2 class="b-txt__title b-txt__title_padbot_40">
        Отзывы 
        <div class="b-txt b-txt_normal b-txt_inline-block b-txt_padleft_5">
            <div class="b-icon b-icon__cat_thumbsup"></div>
            <span class="b-txt_normal b-txt_color_55b02e">
                <?php echo $data['plus_feedbacks']; ?>
            </span>
            <div class="b-icon b-icon__cat_thumbsdown b-icon_margleft_5 b-icon_margtop_5"></div>
            <span class="b-txt_normal b-txt_color_ee5b5b">
                <?php echo $data['minus_feedbacks']; ?>
            </span>
        </div>
    </h2>
    <noindex>
    <?php include 'tpl.feedbacks-items.php'; ?>
    </noindex>
    <?php if($is_feedbacks_paginator){ ?>
    <a id="feedbacks_next_page" href="javascript:void(0)" data-id="<?php echo $data['id'] ?>" data-total="<?php echo $data['total_feedbacks'] ?>" class="b-button b-button_more">Ещё отзывы</a>
    <?php } ?>