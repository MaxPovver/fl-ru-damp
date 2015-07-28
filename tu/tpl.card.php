<?php
if(!defined('IN_STDF')) 
{ 
    header("HTTP/1.0 404 Not Found");
    exit;
}

//$price_txt = tservices_helper::cost_format($data['price'], true, false, false);

$category_spec_title = isset($data['category_spec_title'])? htmlspecialchars($data['category_spec_title']):null;
$category_group_title = isset($data['category_group_title'])? htmlspecialchars($data['category_group_title']):null;
$category_stitle = null;

?>
<?php echo tservices_helper::showFlashMessages() ?>

<div class="b-layout__side b-layout__side_content" itemscope itemtype="http://schema.org/ImageObject">
<div class="b-menu b-menu_crumbs b-menu_padtop_6">
    <ul class="b-menu__list">  
        <?php if(false){ ?>
        <li class="b-menu__item">  
            <a class="b-menu__link" href="/users/<?=$user_obj->login ?>/">
                Профиль</a>&nbsp;&RightArrow;&nbsp;
        </li>    
        <?php } ?>
        <li class="b-menu__item">
            <a class="b-menu__link" href="/users/<?=$user_obj->login ?>/tu/">
                <?=$is_owner?'Мои услуги':'Все услуги фрилансера'?>
            </a>
            &nbsp;/&nbsp;
            <?php if ($category_group_title): ?>
                <a class="b-menu__link" href="/tu/<?=($category_stitle = $data['category_group_link'])?>/">
                    <?=$category_group_title?>
                </a>
            <?php
                endif;
                if ($category_spec_title):
            ?>
                &nbsp;&RightArrow;&nbsp;
                <a class="b-menu__link" href="/tu/<?=($category_stitle = $data['category_spec_link'])?>/">
                    <?=$category_spec_title?>
                </a>
            <?php endif; ?>
        </li>
    </ul>
</div>
<h1 class="b-page__title" itemprop="name">
    <?php echo reformat($data['title'], 30, 0, 1); ?>
</h1>
</div>

<div class="b-layout__side b-layout__side_margbot_30">
    <div id="sticky">
        <div class="b-fon_bg_f2 b-fon_pad_10_15">
            <?php include('tpl.sticky-desktop.php'); ?>
        </div>
        <?php /*
        <div class="b-fon_bg_f2 b-fon_pad_15 b-page__iphone"> 
            <?php include('tpl.sticky-mobile.php'); ?>
        </div>
		*/ ?>
    </div>
</div>

    <?php 
        $cnt_img = count($data['images']);
        $cnt_vds = ($data['videos'])?count($data['videos']):0;
        
        $hide_img = ($cnt_img == 1 && $cnt_vds == 0);
        $hide_vds = ($cnt_img == 0 && $cnt_vds == 1);

        if($cnt_img || $cnt_vds){ 
    ?>
    <div class="b-gallery">
        <div class="b-gallery__wrapper"></div>
        <ul class="b-gallery__items b-gallery__items_padbot_20">
            <?php if($cnt_vds) foreach($data['videos'] as $key => $video){ ?>
                <?php if(empty($video['video'])) continue; ?>
            <li class="b-gallery__item <?php if($hide_vds){ ?>b-gallery__item_display_none<?php } ?>">
                <a data-type="video" data-autoplay="<?php if($key > 0){ ?>1<?php }else{ ?>0<?php } ?>" data-source="<?php echo $video['video']; ?>" class="b-gallery__link" href="javascript:void(0)">
                    <div class="b-gallery__play-btn"></div>
                    <img alt="" class="b-gallery__thumb" src="<?php echo tservices_helper::setProtocol($video['image']); ?>" />
                </a>
            </li> 
            <?php } ?>
            <?php if($cnt_img) foreach($data['images'] as $key => $image){ ?>
            <li class="b-gallery__item <?php if($hide_img){ ?>b-gallery__item_display_none<?php } ?>">
                <a data-type="image" data-source="<?php echo tservices_helper::image_src($image['fname'], $user_obj->login) ?>" class="b-gallery__link" href="javascript:void(0)">
                    <img alt="<?=$key==0?reformat($data['title'], 30, 0, 1):''?>" 
                         title="<?=$key==0?sprintf('Услуги фрилансера %s: %s', $user_obj->login, reformat($data['title'], 30, 0, 1)):''?>" 
                         class="b-gallery__thumb" 
                         src="<?php echo tservices_helper::image_src($image['fname'], $user_obj->login,'thumb_') ?>"
                         <?php if(!$hide_img)echo ' itemprop="contentUrl"'?> />
                </a>
            </li>
            <?php } ?>
        </ul>
    </div>
    <?php } ?>
    
    
    <h2 class="b-txt__title b-txt__title_padnull b-txt_lh_1">Что вы получите</h2>
    <div class="b-txt b-txt_padbot_30">
        <?=reformat($data['description'], 60, 0, 0, 1)?>
    </div> 
    
    <div class="b-txt b-txt_padbot_30">
        <strong>Способ выполнения работы: </strong>
        <?php if($data['is_meet'] == 't'){ ?>
            с возможной личной встречей в <?php echo $data['location'] ?>
        <?php }else{ ?>
            онлайн удаленно
        <?php } ?>
    </div>
    
    <h2 class="b-txt__title b-txt__title_padnull b-txt_lh_1 b-txt__title_padnull">Что нужно, чтобы начать</h2>
    <div class="b-txt b-txt_padbot_40">
        <?=reformat($data['requirement'], 60, 0, 0, 1)?>
    </div>  
    
    <form action="<?php echo tservices_helper::getOrderUrl($data['id']) ?>" id="__form_tservice" method="post" enctype="multipart/form-data">
        
    <?php if($data['extra']){ ?>    
    <h2 class="b-txt__title b-txt_lh_1 b-txt__title_padnull">Дополнительно</h2>
    <table class="b-layout__table_margbot_40 b-layout__table">
            <?php 
                $cnt = count($data['extra']);
                foreach($data['extra'] as $key => $el ){ 
                $is_last = ($cnt == ($key + 1));
            ?>
           <tr class="b-layout__tr">
               <td class="b-layout__td b-layout__td_padtb_15 <?php if(!$is_last){?> b-layout__td_bordbot_c3<?php } ?>">
                   <input type="checkbox" data-price="<?php echo $el['price']; ?>" data-days="<?php echo $el['days']; ?>" value="<?php echo $key ?>" name="extra[]" class="b-check b-check__input __tservice_on_extra" id="extra_<?php echo $key ?>">
               </td>
               <td class="b-layout__td b-layout__td_padleft_15 b-layout__td_padtb_15 b-layout__td_width_full<?php if(!$is_last){?> b-layout__td_bordbot_c3<?php } ?>">
                   <label class="b-check__label b-check__label_ptsans" for="extra_<?php echo $key ?>"> 
                       <?php echo reformat($el['title'], 30, 0, 1); ?>
                       <div class="b-txt b-txt_fs_11 b-txt_padtop_5 b-txt_lh_1">
                           <?php if(!$el['days']){ ?>
                           В тот же срок
                           <?php }else{ ?>
                           Дополнительно <?php echo $el['days'] ?> <?php echo ending($el['days'], 'день', 'дня', 'дней'); ?>
                           <?php } ?>
                       </div>
                   </label>
               </td>
               <td class="b-layout__td b-layout__td_padleft_15 b-layout__td_padright_35 b-layout__td_padtb_15 b-layout__td_nowrap b-layout__td_right b-layout__td_line-height_1 b-post__price_ptsans<?php if(!$is_last){?> b-layout__td_bordbot_c3<?php } ?>">
                   <?php echo tservices_helper::cost_format($el['price'],true,true) ?>
               </td>
          </tr>
          <?php } ?>
    </table> 
    <?php } ?>
    
    <?php if($data['is_express'] == 't'){ ?>
    <h2 class="b-txt__title b-txt_lh_1 b-txt__title_padnull">Срочность</h2>
    <table class="b-txt b-txt_margbot_40 b-layout__table">    
           <tr class="b-layout__tr">
               <td class="b-layout__td b-layout__td_padtb_15 b-layout__td_line-height_1">
                   <input data-price="<?php echo $data['express_price'] ?>" data-days="<?php echo $data['express_days'] ?>" type="checkbox" value="1" name="is_express" class="b-check__input __tservice_on_express" id="is_express">
               </td>
               <td class="b-layout__td b-layout__td_padleft_15 b-layout__td_padtb_15 b-layout__td_width_full b-layout__td_line-height_1">
                   <label class="b-check__label b-check__label_ptsans" for="is_express">
                       Могу выполнить срочно за <strong><?php echo $data['express_days']; ?> <?php echo ending($data['express_days'], 'день', 'дня', 'дней'); ?></strong>
                   </label>
               </td>
               <td class="b-layout__td b-layout__td_padleft_15 b-layout__td_padright_35 b-layout__td_padtb_15 b-layout__td_nowrap b-layout__td_right b-layout__td_line-height_1 b-post__price_ptsans">
                   <?php echo tservices_helper::cost_format($data['express_price'], true, true); ?>
               </td>
          </tr>
   </table>
   <?php } ?> 
        <input type="hidden" name="tu_id" id="tu_id" value="<?php echo $data['id'] ?>" />
    </form>
    
<div class="b-layout__side <?php if(count($feedbacks)){ ?>b-layout__side_margbot_40<?php } ?>">
     <div id="sticky-bottom">
        <div class="b-fon_bg_f2 b-fon_pad_10_15">
            <?php include('tpl.sticky-desktop.php'); ?>
        </div>
     </div>
        <?php /*
        <div class="b-fon_bg_f2 b-fon_pad_15 b-page__iphone"> 
            <?php include('tpl.sticky-mobile.php'); ?>
        </div>
		*/ 
        ?>
</div>
<?php 

if(count($feedbacks)):
    include('tpl.feedbacks.php');
endif; 

if(isset($tserviceOrderPopup)): 
    $tserviceOrderPopup->run();
endif;