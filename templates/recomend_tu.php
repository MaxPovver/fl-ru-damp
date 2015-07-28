<div class="b-layout b-layout_center" style="width:222px;">
    <?php foreach ($tservices as $tservice) { 
        $user = $tservice['user'];
        $user_url = sprintf('/users/%s', $user['login']);
        $tservice_url = sprintf('/tu/%d/%s.html', $tservice['id'], tservices_helper::translit($tservice['title']));
        $avatar_url = tservices_helper::photo_src($user['photo'], $user['login']);

        $hasVideo = false;
        if ($hasVideo)
        {
                $video = current($tservice['videos']);
                $video_thumbnail_url = tservices_helper::setProtocol($video['image']);
                $thumbnail200x150 = '<img width="200" height="150" class="b-pic" src="'.$video_thumbnail_url.'">';
        } elseif(!empty($tservice['file']))
        {
                $image_url = tservices_helper::image_src($tservice['file'],$user['login']);
                $thumbnail200x150 = '<img width="200" height="150" class="b-pic" src="'.$image_url.'">';
        } else
        {
                $thumbnail200x150 = '<div class="b-pic b-pic_no_img b-pic_w200_h150 b-pic_bg_f2"></div>';
        }

        $hasVideo = !empty($tservice['videos']) && count($tservice['videos']);

        $sold_count = isset($tservice['count_sold']) ? $tservice['count_sold'] : $tservice['total_feedbacks'] // Пока сфинск не считает все покупки, будем брать отзывы. #0026584
    ?>
	<figure class="i-pic i-pic_pad_10 i-pic_height_265 i-pic_bord_green_hover">
            <div class="b-layout b-layout_relative">
                <a class="b-pic__lnk b-pic__lnk_relative" href="<?=$tservice_url?>">
                    <?php if($hasVideo) { ?><div class="b-icon b-icon__play b-icon_absolute b-icon_bot_4 b-icon_left_4"></div><?php } ?>
                    <?=$thumbnail200x150?>
                </a>
                <a onclick="TServices_Catalog.orderNow(this);" data-url="<?=$tservice_url?>" href="javascript:void(0);" class="b-pic__price-box b-pic__price-box_pay b-pic__price-box b-pic__price-box_noline"><?=tservices_helper::cost_format($tservice['price'],true)?>                
				<?php if ($sold_count!=0) { ?>
                    <span title="Количество продаж услуги"><span class="b-icon b-icon__tu2 b-icon_top_2"></span> <?=number_format($sold_count, 0, '', ' ')?></span>
                <?php } ?>
                </a>
            </div>
		    <figcaption class="b-layout__txt b-layout__txt_padtop_10 b-layout_overflow_hidden">
                <a class="b-layout__link b-layout__link_no-decorat b-layout__link_color_000 b-layout__link_inline-block" href="<?=$tservice_url?>"><?=LenghtFormatEx(reformat($tservice['title'], 20, 0, 1),80)?></a>
            </figcaption>
                <div class="b-user b-user_padtop_10">
                    <a class="b-user__link b-user__link_color_ec6706" href="<?=$user_url?>/tu/" title="<?=$user['uname'].' '.$user['usurname']?>">
                        <img width="15" height="15" alt="" src="<?=$avatar_url?>" class="b-user__pic b-user__pic_15">
                        <?=view_fullname($user)?></a>
                </div>
        </figure>   
    <?php } ?>
</div>