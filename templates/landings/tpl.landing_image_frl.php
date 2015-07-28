<?php
    $banner_promo_inline = true;
?>

<div class="b-land b-land_bg5 b-land_height_445">
   <div class="b-land__head b-land__head_padtop_50">
       
      <h1 class="b-page__title b-page__title_center b-page__title_color_fff b-page__title_uppercase b-page__title_padbot_10 b-page__title_padbot_10_ipad b-layout__txt_padbot_null_iphone b-page__title_size44">
          Тысячи предложений о работе ежедневно
      </h1>
      
       <h2 class="b-page__title b-page__title_center b-page__title_color_fff">
           Помогите Заказчику сделать правильный выбор
       </h2> 
       
       <div class="b-layout b-layout_padleft_20 b-layout_padright_20 b-layout__txt_center b-layout_margbot_20">
           <div class="b-layout b-layout_inline-block b-layout_width_575 b-layout_pad_20 b-layout_overflow_hidden b-layout_width_auto_iphone">
               <?php if(is_pro()): ?>
                    <a href="/projects/" class="b-layout__title b-layout__title_color_fff">
                        Посмотреть предложения о работе
                    </a>
               <?php else: ?>
                   <a href="/payed/" 
                      class="b-layout__title b-layout__title_color_fff b-layout__title_decor_none __ga__landing__buy_pro_click">
                       Купите аккаунт <span class="b-icon b-icon__spro b-icon__spro_f"></span> и станьте заметнее
                   </a>
                   <br/><br/>
                   <a href="/payed/" 
                      class="b-button b-button_flat b-button_flat_green b-button_nowrap b-button_block_iphone b-button_width_full_iphone __ga__landing__buy_pro_click">&nbsp;&nbsp;&nbsp;Купить&nbsp;&nbsp;&nbsp;</a>
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