<?php
    
    /**
     * PROFI шаблон
     */

if (isset($account)):
    require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/quickpro.common.php");
    $xajax->printJavascript('/xajax/');
?>
<script type="text/javascript">
    var account_sum = <?= round($account->sum, 2)?>;
    var role = 'FRL';
</script>

            <div class="b-fon b-fon_bg_f7f4f2 b-fon_center b-fon_padtop_40 b-fon_padbot_30 b-fon_margbot_30">
               <span class="b-icon b-icon__mprofi"></span>
               <h1 class="b-page__title b-page__title_padbot_null b-page__title_padtop_28">Войдите в список PROFI фрилансеров</h1>
               <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padbot_30">
                   с верификацией, сроком регистрации более 2-х лет, с 10-ю и более отзывами (из которых не менее 98% положительных).
               </div>
               <table class="b-layout__table b-layout__table_width_800 b-layout__table_center b-layout__table_margbot_40">
                  <tr class="b-layout__tr">
                     <td class="b-layout__td">
                     </td>
                     <td class="b-layout__td b-layout__td_width_240 b-layout__td_width_full_ipad">
                        <div class="b-fon b-fon_bg_fff b-fon_pad_15 b-fon__border_radius_3 b-fon_center b-layout_marglr_10_ipad">
                           <div class="b-layout__txt b-layout__txt_fontsize_46 b-layout__txt_bold b-layout__txt_color_6db335">+40%</div>
                           <div class="b-layout__txt b-layout__txt_fontsize_18">К рейтингу в течение<br>срока действия PROFI</div>
                        </div>
                     </td>
                     <td class="b-layout__td b-layout__td_width_20 b-layout__td_padbot_10_ipad">
                     </td>
                     <td class="b-layout__td b-layout__td_width_240 b-layout__td_width_full_ipad">
                        <div class="b-fon b-fon_bg_fff b-fon_pad_15 b-fon__border_radius_3 b-fon_center b-layout_marglr_10_ipad">
                           <div class="b-layout__txt b-layout__txt_fontsize_46 b-layout__txt_bold b-layout__txt_color_f1ba32">PRO</div>
                           <div class="b-layout__txt b-layout__txt_fontsize_18">Все возможности<br>PRO аккаунта</div>
                        </div>
                     </td>
                     <td class="b-layout__td b-layout__td_width_20 b-layout__td_padbot_10_ipad">
                     </td>
                     <td class="b-layout__td b-layout__td_width_240 b-layout__td_width_full_ipad">
                        <div class="b-fon b-fon_bg_fff b-fon_pad_15 b-fon__border_radius_3 b-fon_center b-layout_marglr_10_ipad">
                           <div class="b-layout__txt b-layout__txt_fontsize_46 b-layout__txt_bold b-layout__txt_color_6db335">-20%</div>
                           <div class="b-layout__txt b-layout__txt_fontsize_18">Скидка при покупке<br>любых сервисов</div>
                        </div>
                     </td>
                     <td class="b-layout__td">
                     </td>
                  </tr>
               </table>
<?php
                   $pay = current(payed::getPayedPROFIList());
                   if($pay):
?>
               <a class="b-button b-button_flat b-button_flat_green b-button_flat_big __ga__pro__frl_buy" 
                  href="javascript:void(0)" 
                  onclick="<?="quickPRO_show(); $('quick_pro_f_item_".$pay['opcode']."').set('checked', 'true'); quickPRO_select($('quick_pro_f_item_".$pay['opcode']."'));"?>">
                   <?php if(isProfi()): ?>
                        Продлить PROFI за 5990 руб.
                   <?php else: ?>
                        Стать PROFI за 5990 руб.
                   <?php endif; ?>
               </a>
               <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padtop_10 b-layout__txt_padbot_10">
                   Цена указана за 1 месяц действия аккаунта. Неиспользованные дни действующего PRO будут добавлены к сроку действия PROFI (из расчета 1 день PROFI = 13 дней PRO).
               </div>
<?php
                  endif;
?>
            </div>
<?php
else:
?>
            <div class="b-fon b-fon_bg_f7f4f2 b-fon_center b-fon_padtop_40 b-fon_padbot_30 b-fon_margbot_30">
               <span class="b-icon b-icon__mprofi"></span>
               <h1 class="b-page__title b-page__title_padtop_28">Лучшие фрилансеры сайта FL.ru</h1>
               <table class="b-layout__table b-layout__table_width_910 b-layout__table_center">
                  <tr class="b-layout__tr">
                     <td class="b-layout__td">
                     </td>
                     <td class="b-layout__td b-layout__td_width_290 b-layout__td_width_full_ipad">
                        <div class="b-fon b-fon_bg_fff b-fon_pad_15 b-fon__border_radius_3 b-fon_center b-layout_marglr_10_ipad">
                           <img class="b-pic b-pic_margbot_10" src="<?=WDCPREFIX?>/images/profi/p1.png" width="52" height="61">
                           <div class="b-layout__txt b-layout__txt_fontsize_18">
                               Прошли верификацию<br/>
                               и подтвердили личность
                           </div>
                        </div>
                     </td>
                     <td class="b-layout__td b-layout__td_width_20 b-layout__td_padbot_10_ipad">
                     </td>
                     <td class="b-layout__td b-layout__td_width_290 b-layout__td_width_full_ipad">
                        <div class="b-fon b-fon_bg_fff b-fon_pad_15 b-fon__border_radius_3 b-fon_center b-layout_marglr_10_ipad">
                           <img class="b-pic b-pic_margbot_10" src="<?=WDCPREFIX?>/images/profi/p2.png" width="110" height="61">
                           <div class="b-layout__txt b-layout__txt_fontsize_18">
                               Успешно работают<br/>
                               на сайте более 2-х лет
                           </div>
                        </div>
                     </td>
                     <td class="b-layout__td b-layout__td_width_20 b-layout__td_padbot_10_ipad">
                     </td>
                     <td class="b-layout__td b-layout__td_width_290 b-layout__td_width_full_ipad">
                        <div class="b-fon b-fon_bg_fff b-fon_pad_15 b-fon__border_radius_3 b-fon_center b-layout_marglr_10_ipad">
                           <img class="b-pic b-pic_margbot_10" src="<?=WDCPREFIX?>/images/profi/p3.png" width="57" height="61">
                           <div class="b-layout__txt b-layout__txt_fontsize_18">
                               Не менее 10 отзывов<br/>
                               минимум 98% положительных
                           </div>
                        </div>
                     </td>
                     <td class="b-layout__td">
                     </td>
                  </tr>
               </table>
            </div>
<?php
endif;

            if ($catalogList):
?>
               <table class="b-layout__table b-layout__table_width_full b-page__ipad b-page__iphone b-layout__table_ipad">
                  <tr class="b-layout__tr">
                     <td class="b-layout__td b-layout__td_padtb_15 b-layout__td_bordbot_e6 b-layout__td_ipad b-layout__td_width_33ps_ipad">
                        <div class="b-layout__txt b-layout__txt_fontsize_16 b-layout__txt_bold b-layout__txt_center"><span class="b-icon b-icon__cont b-icon__cont_rate b-icon_top_3"></span>Рейтинг</div>
                     </td>
                     <td class="b-layout__td b-layout__td_padtb_15 b-layout__td_bordbot_e6 b-layout__td_ipad b-layout__td_width_33ps_ipad">
                        <div class="b-layout__txt b-layout__txt_fontsize_16 b-layout__txt_bold b-layout__txt_center"><a class="b-layout__link" href="/promo/bezopasnaya-sdelka/"><span class="b-icon b-icon__bs_small b-icon_top_1"></span></a>Сделки</div>
                     </td>
                     <td class="b-layout__td b-layout__td_padtb_15 b-layout__td_bordbot_e6 b-layout__td_ipad b-layout__td_width_33ps_ipad">
                        <div class="b-layout__txt b-layout__txt_fontsize_16 b-layout__txt_bold b-layout__txt_center"><span class="b-icon b-icon__cont b-icon__cont_op b-icon_valign_middle"></span>Отзывы</div>
                     </td>
                  </tr>
               </table>
                <table class="b-layout__table b-layout__table_width_full b-layout__td_width_full_ipad">
                  <tr class="b-layout__tr b-page__desktop">
                     <td class="b-layout__td b-layout__td_padleft_20 b-layout__td_padtb_15 b-layout__td_valign_mid b-layout__td_width_33ps">
                     </td>
                     <td class="b-layout__td b-layout__td_padtb_15 b-layout__td_valign_mid b-layout__td_bordbot_e6 b-layout__td_ipad b-layout__td_width_11ps b-layout__td_width_33ps_ipad">
                        <div class="b-layout__txt b-layout__txt_fontsize_16 b-layout__txt_bold b-layout__txt_center b-layout__txt_nowrap"><span class="b-icon b-icon__cont b-icon__cont_rate b-icon_top_3"></span>Рейтинг</div>
                     </td>
                     <td class="b-layout__td b-layout__td_padtb_15 b-layout__td_valign_mid b-layout__td_bordbot_e6 b-layout__td_ipad b-layout__td_width_33ps_ipad">
                        <div class="b-layout__txt b-layout__txt_fontsize_16 b-layout__txt_bold b-layout__txt_center b-layout__txt_nowrap">&#160;<a class="b-layout__link" href="/promo/bezopasnaya-sdelka/"><span class="b-icon b-icon__bs_small b-icon_top_1" data-bs-txt='Безопасная сделка - удобный сервис для безопасного сотрудничества между Заказчиками и Исполнителями.'></span></a>Сделки</div>
                     </td>
                     <td class="b-layout__td b-layout__td_padtb_15 b-layout__td_valign_mid b-layout__td_bordbot_e6 b-layout__td_ipad b-layout__td_width_11ps b-layout__td_width_33ps_ipad">
                        <div class="b-layout__txt b-layout__txt_fontsize_16 b-layout__txt_bold b-layout__txt_center b-layout__txt_nowrap">&#160;<span class="b-icon b-icon__cont b-icon__cont_op b-icon_valign_middle"></span>Отзывы</div>
                     </td>
                     <td class="b-layout__td b-layout__td_padleft_20 b-layout__td_padtb_15 b-layout__td_valign_mid b-layout__td_width_33ps">
                     </td>
                  </tr>
                  <?php
                        for($i=0; $i<$cntFirstCLBlock; $i++):
                            
                            if (!isset($catalogList[$i])) {
                                break;
                            }
                            
                            $frl = $catalogList[$i];
                  ?>                
                  <tr class="b-layout__tr">
                     <td class="b-layout__td b-layout__td_padtop_5" colspan="5"></td>
                  </tr>
                  <tr class="b-layout__tr b-fon_bg_f0ffdf_hover b-layout_hover_link_decorate" onClick="document.location='/users/<?=$frl['login']?>/'">
                     <td class="b-layout__td b-layout__td_padleft_20 b-layout__td_padtb_15 b-layout__td_valign_mid">
                        <table class="b-layout__table">
                           <tr class="b-layout__tr">
                              <td class="b-layout__td b-layout__td_width_100 b-layout__td_padright_15 b-layout__td_ipad">
                                 <a class="b-layout__link" href="/users/<?=$frl['login']?>/">
                                     <?=view_avatar($frl['login'], $frl['photo'], 0, 0, "b-pic b-pic_border_radius_50 b-pic_bord_ffa800")?>
                                 </a>
                              </td>
                              <td class="b-layout__td b-layout__td_valign_mid b-layout__td_ipad b-layout__td_width_full_ipad">
                                 <div class="b-layout__txt b-layout__txt_color_64 b-layout__txt_fontsize_15 b-layout__txt_lineheight_1 b-layout__txt_padbot_5">
                                     <a class="b-layout__link b-layout__link_color_64 b-layout__link_bold b-layout_hover_link_decorated" href="/users/<?=$frl['login']?>/"><?=$frl['uname'] . ' ' . $frl['usurname']?></a> 
                                     [<a class="b-layout__link b-layout_h b-layout__link_color_64 b-layout__link_no-decorat" href="/users/<?=$frl['login']?>/"><?=$frl['login']?></a>]
                                 </div>
                                 <?php if($frl['profname']): ?>
                                 <div class="b-layout__txt b-layout__txt_color_ff7f27 b-layout__txt_bold b-layout__txt_padbot_10 b-layout__txt_fontsize_11">
                                    <?=$frl['profname']?>
                                 </div>
                                 <?php endif; ?>
                                 <span class="b-icon b-icon__profi"></span>
                              </td>
                           </tr>
                        </table>
                     </td>
                     <td class="b-layout__td b-layout__td_padtb_15 b-layout__td_valign_mid">
                        <div class="b-layout__txt b-layout__txt_color_64 b-layout__txt_fontsize_15 b-layout__txt_bold b-layout__txt_center b-page__desktop">
                            <?=rating::round($frl['t_rating'])?>
                        </div>
                        
                        <div class="b-layout__txt b-layout__txt_color_64 b-layout__txt_fontsize_15 b-layout__txt_bold b-layout__txt_center b-page__ipad b-page__iphone">
                           <div class="b-layout_float_right b-layout__txt_nowrap b-layuot_width_33ps b-layout__txt_center">
                               <a class="b-layout__link b-layout__link_color_6db335 b-layout__link_bold b-layout_hover_link_decorated" href="/users/<?=$frl['login']?>/opinions/?sort=1&author=0">+ <?=zin($frl['total_opi_plus'])?></a>  |  
                               <a class="b-layout__link b-layout__link_bold b-layout__link_color_c10600 b-layout_hover_link_decorated" href="/users/<?=$frl['login']?>/opinions/?sort=3&author=0">- <?=zin($frl['total_opi_minus'])?></a>
                           </div>
                           <div class="b-layout_float_left b-layout__txt_nowrap b-layuot_width_33ps b-layout__txt_center">
                               <?=rating::round($frl['t_rating'])?>
                           </div>
                           <?php if($frl['completed_cnt'] > 0): ?>
                               <div class="b-layout_inline-block b-layout__txt_nowrap"><?=$frl['completed_cnt']?></div>
                            <?php endif; ?>
                         </div>
                     </td>
                     <td class="b-layout__td b-layout__td_padtb_15 b-layout__td_valign_mid b-page__desktop">
                        <?php if($frl['completed_cnt'] > 0): ?>
                        <div class="b-layout__txt b-layout__txt_color_64 b-layout__txt_fontsize_15 b-layout__txt_bold b-layout__txt_center">
                            <?=$frl['completed_cnt']?>
                        </div>
                         <?php endif; ?>
                     </td>
                     <td class="b-layout__td b-layout__td_padtb_15 b-layout__td_valign_mid b-page__desktop">
                        <div class="b-layout__txt b-layout__txt_color_64 b-layout__txt_fontsize_15 b-layout__txt_bold b-layout__txt_center">
                            <a class="b-layout__link b-layout__link_color_6db335 b-layout__link_bold b-layout_hover_link_decorated" href="/users/<?=$frl['login']?>/opinions/?sort=1&author=0">+ <?=zin($frl['total_opi_plus'])?></a>  |  
                            <a class="b-layout__link b-layout__link_bold b-layout__link_color_c10600 b-layout_hover_link_decorated" href="/users/<?=$frl['login']?>/opinions/?sort=3&author=0">- <?=zin($frl['total_opi_minus'])?></a>
                        </div>
                     </td>
                     <td class="b-layout__td b-layout__td_padleft_20 b-layout__td_padright_20 b-layout__td_padtb_15 b-layout__td_valign_mid">
                        <div class="b-layout__txt b-layout__txt_color_64 b-layout__txt_fontsize_11">
                            <?= br2br(LenghtFormatEx(reformat($frl['status_text'], 40, 0, 1, 25),150)) ?>
                        </div>
                     </td>
                  </tr>
                  <?php if($i < $cntFirstCLBlock-1): ?>
                  <tr class="b-layout__tr">
                     <td class="b-layout__td b-layout__td_padtop_5 b-layout__td_bordbot_e6" colspan="5"></td>
                  </tr>
                  <?php endif; ?>
                  <?php
                        endfor;
                  ?>
                  <?php
                  
                        if ($isMoreCatalogList):
                  ?>
                  <tr class="b-layout__tr">
                     <td class="b-layout__td b-layout__td_padleft_20 b-layout__td_padtop_5"></td>
                     <td class="b-layout__td b-layout__td_bordbot_e6 b-layout__td_padtop_5" colspan="3"></td>
                     <td class="b-layout__td b-layout__td_padtop_5"></td>
                  </tr>
                  <tr class="b-layout__tr">
                     <td class="b-layout__td b-layout__td_padleft_20"></td>
                     <td class="b-layout__td b-layout__td_padtop_20" colspan="3">
                        <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_center">
                            <a class="b-layout__link b-layout__link_bold" href="javascript: void(0);" onclick="$('profiCatalog').removeClass('b-layout_hide');this.getParent('tr').hide().getPrevious('tr').hide();">
                                Полный список PROFI
                            </a>
                        </div>
                     </td>
                     <td class="b-layout__td b-layout__td_padtop_5"></td>
                  </tr>
                  <?php
                        endif;
                  ?>
               </table>
<?php
                if ($isMoreCatalogList):
?>
                <table id="profiCatalog" class="b-layout__table b-layout__table_width_full b-layout_hide">
                  <tr class="b-layout__tr">
                     <td class="b-layout__td b-layout__td_padleft_20 b-layout__td_width_33ps"></td>
                     <td class="b-layout__td b-layout__td_ipad b-layout__td_width_11ps b-layout__td_width_33ps_ipad"></td>
                     <td class="b-layout__td b-layout__td_ipad b-layout__td_width_33ps_ipad"></td>
                     <td class="b-layout__td b-layout__td_ipad b-layout__td_width_11ps b-layout__td_width_33ps_ipad"></td>
                     <td class="b-layout__td b-layout__td_padleft_20 b-layout__td_width_33ps"></td>
                  </tr>
                  <tr class="b-layout__tr">
                     <td class="b-layout__td b-layout__td_padtop_5 b-layout__td_bordbot_e6" colspan="5"></td>
                  </tr>                  
<?php
                    for($i=$cntFirstCLBlock; $i<$cntCatalogList; $i++):
                        
                            if (!isset($catalogList[$i])) {
                                break;
                            }
                            
                            $frl = $catalogList[$i];
?>
                  <tr class="b-layout__tr">
                     <td class="b-layout__td b-layout__td_padtop_5" colspan="5"></td>
                  </tr>
                  <tr class="b-layout__tr b-fon_bg_f0ffdf_hover b-layout_hover_link_decorate" onClick="document.location='/users/<?=$frl['login']?>/'">
                     <td class="b-layout__td b-layout__td_padleft_20 b-layout__td_padtb_15 b-layout__td_valign_mid">
                        <table class="b-layout__table">
                           <tr class="b-layout__tr">
                              <td class="b-layout__td b-layout__td_width_100 b-layout__td_padright_15 b-layout__td_ipad">
                                 <a class="b-layout__link" href="/users/<?=$frl['login']?>/">
                                     <?=view_avatar($frl['login'], $frl['photo'], 0, 0, "b-pic b-pic_border_radius_50 b-pic_bord_ffa800")?>
                                 </a>
                              </td>
                              <td class="b-layout__td b-layout__td_valign_mid b-layout__td_ipad b-layout__td_width_full_ipad">
                                 <div class="b-layout__txt b-layout__txt_color_64 b-layout__txt_fontsize_15 b-layout__txt_lineheight_1 b-layout__txt_padbot_5">
                                     <a class="b-layout__link b-layout__link_color_64 b-layout__link_bold b-layout_hover_link_decorated" href="/users/<?=$frl['login']?>/"><?=$frl['uname'] . ' ' . $frl['usurname']?></a> 
                                     [<a class="b-layout__link b-layout_h b-layout__link_color_64 b-layout__link_no-decorat" href="/users/<?=$frl['login']?>/"><?=$frl['login']?></a>]
                                 </div>
                                 <?php if($frl['profname']): ?>
                                 <div class="b-layout__txt b-layout__txt_color_64 b-layout__txt_padbot_10">
                                    <?=$frl['profname']?>
                                 </div>
                                 <?php endif; ?>
                                 <span class="b-icon b-icon__profi"></span>
                              </td>
                           </tr>
                        </table>
                     </td>
                     <td class="b-layout__td b-layout__td_padtb_15 b-layout__td_valign_mid">
                        <div class="b-layout__txt b-layout__txt_color_64 b-layout__txt_fontsize_15 b-layout__txt_bold b-layout__txt_center b-page__desktop">
                            <?=rating::round($frl['t_rating'])?>
                        </div>
                        
                        <div class="b-layout__txt b-layout__txt_color_64 b-layout__txt_fontsize_15 b-layout__txt_bold b-layout__txt_center b-page__ipad b-page__iphone">
                           <div class="b-layout_float_right b-layout__txt_nowrap b-layuot_width_33ps b-layout__txt_center">
                               <a class="b-layout__link b-layout__link_color_6db335 b-layout__link_bold b-layout_hover_link_decorated" href="/users/<?=$frl['login']?>/opinions/?sort=1&author=0">+ <?=zin($frl['total_opi_plus'])?></a>  |  
                               <a class="b-layout__link b-layout__link_bold b-layout__link_color_c10600 b-layout_hover_link_decorated" href="/users/<?=$frl['login']?>/opinions/?sort=3&author=0">- <?=zin($frl['total_opi_minus'])?></a>
                           </div>
                           <div class="b-layout_float_left b-layout__txt_nowrap b-layuot_width_33ps b-layout__txt_center">
                               <?=rating::round($frl['t_rating'])?>
                           </div>
                           <?php if($frl['completed_cnt'] > 0): ?>
                               <div class="b-layout_inline-block b-layout__txt_nowrap"><?=$frl['completed_cnt']?></div>
                            <?php endif; ?>
                         </div>
                     </td>
                     <td class="b-layout__td b-layout__td_padtb_15 b-layout__td_valign_mid b-page__desktop">
                        <?php if($frl['completed_cnt'] > 0): ?>
                        <div class="b-layout__txt b-layout__txt_color_64 b-layout__txt_fontsize_15 b-layout__txt_bold b-layout__txt_center">
                            <?=$frl['completed_cnt']?>
                        </div>
                         <?php endif; ?>
                     </td>
                     <td class="b-layout__td b-layout__td_padtb_15 b-layout__td_valign_mid b-page__desktop">
                        <div class="b-layout__txt b-layout__txt_color_64 b-layout__txt_fontsize_15 b-layout__txt_bold b-layout__txt_center">
                            <a class="b-layout__link b-layout__link_color_6db335 b-layout__link_bold b-layout_hover_link_decorated" href="/users/<?=$frl['login']?>/opinions/?sort=1&author=0">+ <?=zin($frl['total_opi_plus'])?></a>  |  
                            <a class="b-layout__link b-layout__link_bold b-layout__link_color_c10600 b-layout_hover_link_decorated" href="/users/<?=$frl['login']?>/opinions/?sort=3&author=0">- <?=zin($frl['total_opi_minus'])?></a>
                        </div>
                     </td>
                     <td class="b-layout__td b-layout__td_padleft_20 b-layout__td_padright_20 b-layout__td_padtb_15 b-layout__td_valign_mid">
                        <div class="b-layout__txt b-layout__txt_color_64 b-layout__txt_fontsize_11">
                            <?= br2br(LenghtFormatEx(reformat($frl['status_text'], 40, 0, 1, 25),150)) ?>
                        </div>
                     </td>
                  </tr>
                  <?php if($i < $cntCatalogList-1): ?>
                  <tr class="b-layout__tr">
                     <td class="b-layout__td b-layout__td_padtop_5 b-layout__td_bordbot_e6" colspan="5"></td>
                  </tr>
                  <?php endif; ?>                      
<?php
                    endfor;
?>               
                </table>
<?php
                endif;
?>



<?php
                endif;

if (isset($account)):
    $quickPRO_type = 'profi';
    require_once($_SERVER['DOCUMENT_ROOT'] . "/templates/quick_buy_pro.php"); 
endif;