<?php
if(!defined('IN_STDF')) 
{ 
    header("HTTP/1.0 404 Not Found");
    exit;
}
?>
<?php foreach($feedbacks as $feedback){ ?>
    <article id="feedback-<?php echo $feedback['id']; ?>" class="b-post b-post_margbot_30 b-user b-user_grey">
        <div class="b-post__time b-post__time_float_right b-post__time_fontsize_11 b-post__time_up">
            <?php echo tservices_helper::getOrderCostTxt($feedback['type'], $feedback['cost']); ?>, 
            <?php echo tservices_helper::date_text($feedback['posted_time']); ?>
            <a class="b-post__anchor b-post__anchor_margleft_10" href="#feedback-<?php echo $feedback['id']; ?>"></a>
        </div> 
        <div class="b-icon b-icon__cat_<?php if($feedback['rating'] >= 0){ ?>thumbsup b-icon_top_-3<?php }else{ ?>thumbsdown<?php } ?> b-icon_float_right b-icon_margright_10"></div>
        
        <a class="b-user__link b-user__link_bold b-user__link_color_55b02e" href="/users/<?php echo $feedback['login']; ?>/" title="<?php if(!empty($feedback['login'])){ echo $feedback['uname'] . ' ' . $feedback['usurname']; } else { echo $feedback['login']; } ?>">
            <img class="b-user__pic" width="50" height="50" alt="<?php if(!empty($feedback['login'])){ echo $feedback['uname'] . ' ' . $feedback['usurname']; } else { echo $feedback['login']; } ?>" src="<?php echo tservices_helper::photo_src($feedback['photo'],$feedback['login']) ?>"> 
            <?php if(!empty($feedback['login'])){ echo $feedback['uname'] . ' ' . $feedback['usurname'] . ' [' . $feedback['login'] . ']'; } else { echo $feedback['login']; } ?></a>
        <?php if($feedback['is_pro'] == 't'){ ?>
        <a class="b-txt__lnk" href="/payed-emp/" title="PRO"><span class="b-icon b-icon__pro b-icon__pro_e b-icon_top_3"></span></a>
        <?php } ?>
        <?php if($feedback['is_verify'] == 't'){ ?>
        <a class="b-txt__lnk" href="/promo/verification/" title="Паспортные данные подтверждены"><span class="b-icon b-icon__ver b-icon_top_2"></span></a>
        <?php } ?>
        
        <?php if($feedback['country']){ ?>
        <div class="b-txt b-txt_padtop_5 b-txt_fs_11 b-txt_padleft_60">
            <?php echo $feedback['country_name']; ?><?php if($feedback['city']){ echo ', ' . $feedback['city_name']; } ?>
        </div>
        <?php } ?>
        <div class="b-post__content b-post__content_margleft_60 b-post__content_margleft_null_iphone b-post__content_padtop_15">
            <div class="b-txt b-txt_padbot_20">
                <?php echo nl2br($feedback['descr']); ?>
            </div>
        </div>
    </article>
<?php } ?>
