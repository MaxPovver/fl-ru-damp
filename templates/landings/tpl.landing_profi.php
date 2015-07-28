<?php

/**
 * Список PROFI для лендинга
 */

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");

$freelancer = new freelancer();
$profiList = $freelancer->getProfiAllRandom(90);

if ($profiList):
?>
<h2 class="b-page__title b-page__title_center">
    <a href="/profi/" class="b-page__title_decor_none b-page__title_color_32">Лучшие исполнители</a>
</h2>
<div class="b-layout b-layout_box b-layout_margbot_30 b-layout_padleft_30_iphone b-layout_padright_15_iphone b-layout_pad_null_r560">
<?php
    foreach($profiList as $profiUser):
        $user_profile_url = sprintf('/users/%s/',$profiUser['login']);
?>
   <div class="b-layout b-layuot_width_33ps b-layuot_width_50ps b-layout__one_width_full_iphone b-layout_float_left b-layout_height_180 profi-fix">
      <div class="b-layout b-layout_padbot_20 b-layout_padright_15">
             <table class="b-layout__table b-fon_bg_f0ffdf_hover b-layout_hover_link_decorate b-layout__table_width_full">
                <tr class="b-layout__tr">
                   <td class="b-layout__td b-layout__td_width_100 b-layout__td_pad_10 b-layout__td_ipad">
                      <a href="<?=$user_profile_url?>" class="b-layout__link"> 
                         <span class="i-pic b-pic_border_radius_50 b-pic_bord_ffa800 b-layout_overflow_hidden"><?=view_avatar($profiUser['login'], $profiUser['photo'], 0, 0, "b-pic b-pic_border_radius_50_safari-win")?></span>
                      </a>
                   </td>
                   <td class="b-layout__td b-layout__td_padtb_10 b-layout__td_padright_10 b-layout__td_padlr_10_ipad b-layout__td_ipad b-layout__td_width_full_ipad">
                      <div class="b-layout__txt b-layout__txt_color_3c b-layout__txt_padbot_5"> 
                         <a href="<?=$user_profile_url?>" class="b-layout__link b-layout__link_color_3c b-layout__link_bold b-layout_hover_link_decorated"><?=$profiUser['uname']?> <?=$profiUser['usurname']?></a> 
                         [<a href="<?=$user_profile_url?>" class="b-layout__link b-layout__link_color_3c b-layout__link_no-decorat"><?=$profiUser['login']?></a>] 
                         <?=view_profi()?>
                      </div>
                      <?php if($profiUser['profname']): ?>
                      <div class="b-layout__txt b-layout__txt_color_ff7f27 b-layout__txt_bold b-layout__txt_padbot_10 b-layout__txt_lineheight_1 b-layout__txt_fontsize_11">
                          <?=$profiUser['profname']?>
                      </div>
                      <?php endif; ?>
                      <div class="b-layout__txt b-layout__txt_color_64 b-layout__txt_fontsize_11">
                          <?= br2br(LenghtFormatEx(reformat($profiUser['status_text'], 40, 0, 1, 25),150)) ?>
                      </div>
                   </td>
                </tr>
             </table>
      </div>
   </div>
<?php
    endforeach;
?>
</div>


<?php
endif;