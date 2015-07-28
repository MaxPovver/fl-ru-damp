<?php
if(!defined('IN_STDF')) 
{ 
    header("HTTP/1.0 404 Not Found");
    exit;
} 
?>
       <table class="b-txt b-layoub-fon_padt__table b-layout__table_width_full">
           <tr class="b-layout__tr">
               <td class="b-layout__td b-layout__td_width_33ps b-layout__td_width_full_ipad">
                   <div class="b-txt_color_80">Срок выполнения</div>
                   <div class="b-txt_fs_25 b-txt_bold b-txt_lh_1 __tservice_days" data-days="<?php echo $data['days'] ?>">
                       <?php echo $data['days'] ?> <?php echo ending($data['days'], 'день', 'дня', 'дней'); ?>
                   </div>
               </td>
               <td class="b-layout__td b-layout__td_width_33ps b-layout__td_width_full_ipad">
               <?php if($data['total_feedbacks']){ ?> 
                   <div class="b-txt_color_80">
                       Отзывы 
                       <?php if(false){ ?>
                       <div class="b-txt b-txt_normal b-txt_inline-block b-txt_padleft_5">
                           <div class="b-icon b-icon__cat_thumbsup"></div>
                           <span class="b-txt_normal b-txt_color_55b02e">
                               <?php echo $data['plus_feedbacks'] ?>
                           </span>
                           <div class="b-icon b-icon__cat_thumbsdown b-icon_margleft_5 b-icon_margtop_5"></div>
                           <span class="b-txt_normal b-txt_color_ee5b5b">
                               <?php echo $data['minus_feedbacks'] ?>
                           </span>
                       </div>
                       <?php } ?>
                   </div>
                   <div class="b-txt_fs_25 b-txt_bold b-txt_lh_1 b-txt_inline-block"><?php echo $data['perplus_feedbacks'] ?>%</div>
                   <div class="b-txt_color_80 b-txt_inline-block">положительных из <?php echo $data['total_feedbacks'] ?></div>
               <?php }else{ ?>
                   <div class="b-txt_color_80 b-txt_padtop_15">Отзывов пока нет</div>
               <?php } ?>
               </td>
               <td class="b-layout__td b-layout__td_width_33ps b-layout__td_right b-layout__td_valign_mid b-layout__td_width_full_ipad">
                   <?php if($is_owner || $is_adm){ ?>
                   <a href="<?php echo tservices_helper::edit_link($user_obj->login, $data['id']); ?>" class="b-button b-button_flat b-button_flat_green">
                       Редактировать
                   </a>
                   <?php if($is_adm){ ?>
                   <a id="__tservices_blocked" href="javascript:void(0);" onclick="banned.delReason('22_<?php echo $data['id'] ?>_0', 0, '', 0);" class="b-button b-button_flat b-button_flat_red <?php if($data['is_blocked'] == 't'){ ?>b-button_hide<?php } ?>">
                       Заблокировать
                   </a>
                   <a id="__tservices_unblocked" href="javascript:void(0);" onclick="banned.unBlocked('22_<?php echo $data['id'] ?>_0');" class="b-button b-button_flat b-button_flat_orange <?php if($data['is_blocked'] == 'f'){ ?>b-button_hide<?php } ?>">
                       Разблокировать
                   </a>
                   <?php } ?>
                   <?php }else{ ?>
                   <a <?php if(@$is_frl): ?>
                       href="/registration/?type=empl&user_action=tu"
                       <?php elseif(is_emp()): ?> 
                       onclick="TServices.showPopup();" href="javascript:void(0)"
                       <?php else: ?>
                       onclick="TServices_Order_Auth.showPopup();" href="javascript:void(0)"
                       <?php endif; ?>
                       class="b-button b-button_flat <?php if(/*is_emp()*/true){ ?>b-button_flat_green<?php } else { ?>b-button_flat_grey b-button_flat_grey_pad_10_20 b-button_disabled i-shadow __tservice_emp_only<?php } ?>">
                      Заказать за <span class="__tservice_price" data-price="<?php echo $data['price'] ?>"><?php echo tservices_helper::cost_format($data['price'], false)?></span> p.
                   </a>
                   <?php } ?>
               </td>
           </tr>
       </table>