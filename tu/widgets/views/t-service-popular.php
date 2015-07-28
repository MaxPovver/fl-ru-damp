<?php

if ($tservices):
?>
<h3 class="b-layout__title b-layout__title_padbot_20 <?php if(isset($options['title_css'])): echo $options['title_css']; endif; ?>">
    <?php if(isset($options['fullname'])): ?>
    Все услуги фрилансера <?=$options['fullname']?>
    <?php else: ?>
        <?php if(isset($options['title'])): echo $options['title']; else: ?>Другие услуги<?php endif; ?>
        <?php if(isset($category_title) && $category_title): ?> по специализации «<?= $category_title ?>»<?php endif; ?>
    <?php endif; ?>
</h3>
<div class="b-layout b-layout_box">
<?php 
foreach($tservices as $key => $tservice): 
    $user = $tservice['user'];
    $tservice_url = sprintf('/tu/%d/%s.html', $tservice['id'], tservices_helper::translit($tservice['title']));
    $tservice_title = LenghtFormatEx(reformat($tservice['title'], 20, 0, 1),80);
    $alt = $tservice_title;
    $title = sprintf("Услуги фрилансера %s: %s", $tservice['login'], $tservice_title);

    if (!empty($tservice['file'])) {
        $image_url = tservices_helper::image_src($tservice['file'], $tservice['login']);
        $thumbnail200x150 = '<img width="200" height="150" class="b-pic" src="'.$image_url.'" alt="'.$alt.'" title="'.$title.'">';
    } else {
        $thumbnail200x150 = '<div class="b-pic b-pic_no_img b-pic_w200_h150 b-pic_bg_f2"></div>';
    }

    $hasVideo = !empty($tservice['videos']) && count($tservice['videos']);
    // Пока сфинск не считает все покупки, будем брать отзывы. #0026584
    $sold_count = isset($tservice['count_sold']) ? $tservice['count_sold'] : $tservice['total_feedbacks']; 
    $avatar_url = tservices_helper::photo_src($tservice['photo'], $tservice['login']);
    $user_url = sprintf('/users/%s', $tservice['login']);
?>
        <figure class="i-pic i-pic_port i-pic_port_z-index_inherit i-pic_pad_10 i-pic_height_265 i-pic_bord_green_hover <?php if(isset($options['item_css'])): echo $options['item_css']; endif; ?>">     

            <div class="b-layout b-layout_relative">
                <a href="<?=$tservice_url?>" class="b-pic__lnk b-pic__lnk_relative">
                    <?php if($hasVideo): ?>
                        <div class="b-icon b-icon__play b-icon_absolute b-icon_bot_4 b-icon_left_4"></div>
                    <?php endif; ?>
                    <?=$thumbnail200x150?>
                </a>
                <a class="b-pic__price-box b-pic__price-box_pay b-pic__price-box b-pic__price-box_noline" href="javascript:void(0);" data-url="<?=$tservice_url?>" onclick="TServices_Catalog.orderNow(this);">
                    <?=tservices_helper::cost_format($tservice['price'],true)?>			
                    <?php if ($sold_count > 0): ?>
                        <span title="Количество продаж услуги"><span class="b-icon b-icon__tu2 b-icon_top_2"></span> <?=number_format($sold_count, 0, '', ' ')?></span>
                    <?php endif; ?>
                </a>
            </div>
            
            <figcaption class="b-layout__txt b-layout__txt_padtop_10 b-layout_overflow_hidden">
                <a class="b-layout__link b-layout__link_no-decorat b-layout__link_color_000 b-layout__link_inline-block" href="<?=$tservice_url?>">
                    <?=$tservice_title?>
                </a>
            </figcaption>
            
            <div class="b-user b-user_padtop_10">
                <?php $fullname = view_fullname($tservice, true); ?>
                <a class="b-user__link b-user__link_color_ec6706" title="<?=$fullname?>" href="<?=$user_url?>">
                    <img width="15" height="15" class="b-user__pic b-user__pic_15" src="<?=$avatar_url?>" alt="<?=$fullname?>">
                    <?=$fullname?></a>
                <span class="b-user_nowrap">
                    <a title="<?=$fullname?>" href="<?=$user_url?>/tu/" class="b-user__link b-user__link_color_ec6706">[<?=$tservice['login']?>]</a><?=view_user_label($tservice)?>
                </span>
            </div>

        </figure>
<?php endforeach; ?>
</div>
<?php if(!isset($options['user_id'])): ?>
<div class="b-pager b-pager_marg_0">
    <ul class="b-pager__list">
        <li class="b-pager__item">
            <a class="b-pager__link" href="/tu/<?php if (isset($category_stitle) && $category_stitle): echo $category_stitle . '/'; endif; ?>">
                Все услуги
            </a>
        </li>
    </ul>
</div>
<?php endif; ?>
<?php endif; ?>