<?php
    $new_project_url = $uid > 0?sprintf("/public/?step=1&kind=1"):"/guest/new/project/";
    $banner_promo_inline = true;
?>
<div class="b-land b-land_bg5 b-land_height_445">
    <div class="b-land__head b-land__head_padtop_80">
       
      <h1 class="b-page__title b-page__title_center b-page__title_color_fff b-page__title_uppercase b-page__title_padbot_10 b-page__title_padbot_10_ipad b-layout__txt_padbot_null_iphone b-page__title_size44">
          1 000 000 исполнителей для вас
      </h1>
      
       <h2 class="b-page__title b-page__title_center b-page__title_color_fff">
           Бесплатно опубликуйте задание и сразу получите предложения
       </h2> 
       
       <div class="b-layout b-layout_padleft_20 b-layout_padright_20 b-layout__txt_center b-layout_margbot_20">
           
           <div class="b-layout b-layout_inline-block b-layout_width_575 b-layout_pad_20 b-layout_overflow_hidden b-layout_width_auto_iphone">
               <a <?php if (!$uid): ?>data-ga-event="{ec: 'user', ea: 'main_customerbutton_clicked',el: 'customer'}"<?php endif; ?>
                  href="<?=$new_project_url?>" 
                  class="b-button b-button_flat b-button_flat_green b-button_flat_big b-button_flat_width_220 b-button_marglr_10_10 b-button_margbot_10_ipad">
                   Найти исполнителя
               </a>
               <?php if (!$uid): ?>
               <a  data-ga-event="{ec: 'user', ea: 'main_freelancerbutton_clicked',el: 'freelancer'}" 
                   href="/registration/" 
                   class="b-button b-button_flat b-button_flat_orange b-button_flat_big b-button_flat_width_220 b-button_marglr_10_10 b-button_margbot_10_ipad">
                   Найти работу
               </a>
               <?php endif; ?>
           </div>
           
       </div>
       
        <div class="b-layout b-layout_padleft_20 b-layout_padright_20 b-layout__txt_center">
            <div class="b-menu__banner b-menu__banner_ln1 b-menu__banner_inline">
                <a target="_blank" href="/promo/bezopasnaya-sdelka/" class="b-menu__link-banner b-menu__link-banner_margtopnull"><span class="b-icon b-icon__shield"></span>Работайте с гарантией через Безопасную сделку</a>
            </div>
        </div>

   </div>
</div>