<div class="b-land">
   <div class="b-land__head"> 
      <h1 class="b-page__title b-page__title_center b-page__title_color_fff b-page__title_uppercase b-page__title_padbot_30 b-page__title_padbot_10_ipad b-layout__txt_padbot_null_iphone b-page__title_size44"><?php if(is_emp() || !get_uid(false)){ ?>МИЛЛИОН ИСПОЛНИТЕЛЕЙ ДЛЯ ВАС<?php } else { ?>ТЫСЯЧИ ПРОЕКТОВ ДЛЯ ВАС<?php } ?></h1>
      <div class="b-layout b-layout__txt_center b-layout_pad_10">
         <a class="choose_freelancer_button b-button b-button_land b-button_land_bg_green b-button_width_190 b-button_width_240_ipad " href="/freelancers/">
            Каталог исполнителей
         </a>
      </div>
      <div class="b-layout b-layout__txt_center b-layout_padtop_50 b-layout_pad_null_ipad">
         <div class="b-layout b-layout_inline-block b-layout_hover_color_ff">
            <?php require_once($_SERVER['DOCUMENT_ROOT'] . "/banner_promo.php"); ?>
         </div>
      </div>
   </div>
   <div class="b-land__foot">
      <div class="b-layout b-layout_overflow_hidden b-layuot_max-width_1280 b-layout_center">
         <table class="b-layout__table b-layout__table_width_full">
            <tr class="b-layout__tr">
               <td class="b-layout__td b-layout__td_center b-layout__td_width_null_ipad">
                  <a class="create_tu_button b-button b-button_land b-button_width_200 b-button_margbot_10_ipad b-button_land_bg_green b-button_width_full_ipad b-page__desktop" href="/tu/">
                     <?php if(is_emp() || !get_uid(false)){ ?>Заказать услугу<?php } else { ?>Добавить услуги<?php } ?>
                  </a>
               </td>
               <td class="b-layout__td b-layout__td_center b-layout__td_padlr_10_ipad b-layout__td_width_50ps_ipad b-layout__td_width_full_iphone">
                  <a class="create_tu_button b-button b-button_land b-button_width_200 b-button_margbot_10_ipad b-button_land_bg_green b-button_width_full_ipad b-page__ipad b-page__iphone" href="/tu/">
                     <?php if(is_emp() || !get_uid(false)){ ?>Заказать услугу<?php } else { ?>Добавить услуги<?php } ?>
                  </a>
                  <a class="create_project_button b-button b-button_land b-button_width_200 b-button_margbot_10_ipad b-button_land_bg_green b-button_width_full_ipad" href="<?php if(is_emp() || !get_uid(false)){ ?>/public/?step=1&kind=1<?php } else { ?>/projects/<?php } ?>">
                     <?php if(is_emp() || !get_uid(false)){ ?>Опубликовать проект<?php } else { ?>Ответить на проекты<?php } ?>
                  </a>
                  <a class="create_vacancy_button b-button b-button_land b-button_width_200 b-button_margbot_10_ipad b-button_land_bg_green b-button_width_full_ipad b-page__iphone" href="<?php if(is_emp() || !get_uid(false)){ ?>/public/?step=1&kind=4<?php } else { ?>/projects/?kind=4<?php } ?>">
                     <?php if(is_emp() || !get_uid(false)){ ?>Разместить вакансию<?php } else { ?>Посмотреть вакансии<?php } ?>
                  </a>
                  <a class="create_contest_button b-button b-button_land b-button_width_200 b-button_land_bg_green b-button_width_full_ipad b-page__iphone" href="<?php if(is_emp() || !get_uid(false)){ ?>/public/?step=1&kind=7<?php } else { ?>/konkurs/<?php } ?>">
                     <?php if(is_emp() || !get_uid(false)){ ?>Устроить конкурс<?php } else { ?>Участвовать в конкурсах<?php } ?>
                  </a>
               </td>
               <td class="b-layout__td b-layout__td_center b-layout__td_width_null_ipad b-layout__td_padright_10 b-layout__td_padleft_10 b-layout__td_pad_null_iphone b-layout__td_pad_null_ipad b-layout__td_valign_mid">
                  <a class="b-button b-button_circ b-page__desktop" href="#top"></a>
               </td>
               <td class="b-layout__td b-layout__td_center b-layout__td_padlr_10_ipad b-layout__td_width_null_iphone b-layout__td_pad_null_iphone">
                  <a class="create_vacancy_button b-button b-button_land b-button_width_200 b-button_margbot_10_ipad b-button_land_bg_green b-button_width_full_ipad b-page__desktop b-page__ipad" href="<?php if(is_emp() || !get_uid(false)){ ?>/public/?step=1&kind=4<?php } else { ?>/projects/?kind=4<?php } ?>">
                     <?php if(is_emp() || !get_uid(false)){ ?>Разместить вакансию<?php } else { ?>Посмотреть вакансии<?php } ?>
                  </a>
                  <a class="create_contest_button b-button b-button_land b-button_width_200 b-button_land_bg_green b-button_width_full_ipad b-page__ipad" href="<?php if(is_emp() || !get_uid(false)){ ?>/public/?step=1&kind=7<?php } else { ?>/konkurs/<?php } ?>">
                     <?php if(is_emp() || !get_uid(false)){ ?>Устроить конкурс<?php } else { ?>Участвовать в конкурсах<?php } ?>
                  </a>
               </td>
               <td class="b-layout__td b-layout__td_center b-layout__td_width_null_ipad">
                  <a class="create_contest_button b-button b-button_land b-button_width_200 b-button_land_bg_green b-button_width_full_ipad b-page__desktop b-button_nowrap" href="<?php if(is_emp() || !get_uid(false)){ ?>/public/?step=1&kind=7<?php } else { ?>/konkurs/<?php } ?>">
                     <?php if(is_emp() || !get_uid(false)){ ?>Устроить конкурс<?php } else { ?>Участвовать в конкурсах<?php } ?>
                  </a>
               </td>
            </tr>
         </table>
      </div>
   </div>
</div>