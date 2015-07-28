<?php

$settings_url = sprintf('/users/%s/setup/main/', $login);
$contact_url = sprintf('/contacts/?from=%s',$freelancer['login']);
$fullname = "{$freelancer['uname']} {$freelancer['usurname']} [{$freelancer['login']}]";

?>
         <h1 class="b-page__title">
             Регистрация успешно завершена!
         </h1>
         
         <div class="b-layout b-layout_margright_250">
         
            <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padbot_10">
                Вы зарегистрировались на сайте в роли заказчика
            </div>
             
            <table class="b-layout__table b-layout__table_margbot_10">
               <tr class="b-layout__tr">
                  <td class="b-layout__td b-layout__td_padright_10">
                      <div class="b-layout__txt b-layout__txt_fontsize_15">
                          Ваш логин:
                      </div>
                  </td>
                  <td class="b-layout__td">
                      <div class="b-layout__txt b-layout__txt_bold b-layout__txt_fontsize_15">
                          <?php echo $login ?>
                      </div>
                  </td>
               </tr>
               <tr class="b-layout__tr">
                  <td class="b-layout__td b-layout__td_padright_10">
                      <div class="b-layout__txt b-layout__txt_fontsize_15">
                          Пароль:
                      </div>
                  </td>
                  <td class="b-layout__td">
                      <div class="b-layout__txt b-layout__txt_bold b-layout__txt_fontsize_15">
                          <?php echo $passwd ?>
                      </div>
                  </td>
               </tr>
            </table>
             
            <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padbot_20">
                При необходимости вы можете поменять пароль в <a class="b-layout__link" href="<?php echo $settings_url ?>">настройках аккаунта</a>
            </div>
         
            <div class="b-fon b-fon_bg_f5 b-fon_pad_10 b-fon_margbot_20 b-fon_overflow_hidden">
               <table class="b-layout__table">
                  <td class="b-layout__td b-layout__td_width_60 b-layout__td_ipad b-layout__td_width_null_ipad">
                      <img class="b-user__pic"  alt="" src="/images/ico_po_offers.gif"/>
                  </td>
                  <td class="b-layout__td b-layout__td_ipad">
                      <div class="b-layout__txt b-layout__txt_padbot_10">
                          <?php echo $fullname ?> получил уведомление о заказанной вами услуге.<br/>
                          Как только он подтвердит заказ, начнется его выполнение. Ожидайте, пожалуйста.
                      </div>
                  </td>
               </table>
            </div>
             
             <div class="b-buttons">
                   <a href="<?php echo $order_url ?>" class="b-button b-button_flat b-button_flat_green">Открыть заказ</a>
                   <span class="b-layout__txt b-layout__txt_fontsize_11">&#160; или <a class="b-layout__link" href="<?php echo $contact_url ?>">связаться с исполнителем</a></span>
             </div>
         
         </div>