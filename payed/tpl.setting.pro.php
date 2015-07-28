<?php

if (!($freeze_set || $freezed_now)):
    return;
endif;

?>
<div class="b-layout__txt <?= !$pro_last ? "b-layout__txt_hide" : ""?> buyed_pro">
    <table class="b-layout__table b-layout__table_center b-layout__table_width_940 b-layout__table_margtop_20 b-layout__table_margbot_30">
       <tr class="b-layout__tr">
          <td id="autoprolong_html" class="b-layout__td b-layout__td_left b-layout__td_width_50ps b-layout__td_width_full_ipad">
<? if($u_is_pro_auto_prolong=='t') { ?>
            <?php
            $wallet = WalletTypes::initWalletByType(get_uid(false));
            if($wallet != false) {
                include($_SERVER['DOCUMENT_ROOT'] . "/bill/widget/tpl.info_wallet.php");
            } else {
                $service = array(
                    'auto' => $u_is_pro_auto_prolong,
                    'id'   => get_uid(false)
                );
                $_SESSION['redirect_uri_wallet'] = is_emp() ? '/payed-emp/' : '/payed/';

                ?>
                <span class="walletInfo">
                    <div class="b-layout__h3 b-layout__h3_padbot_5">Автопродление</div>

                </span>
                <span id="wallet">
                    <?
                    $popup_content   = $_SERVER['DOCUMENT_ROOT'] . "/bill/widget/popups/popup.wallet.php";
                    include ( $_SERVER['DOCUMENT_ROOT'] . "/bill/widget/tpl.popup.php" );
                    ?>
                </span>
                <?
            } ?>
            <div class="b-layout__txt b-layout__txt_fontsize_11">
                <a href="javascript:void(0)" class="b-button b-button_flat b-button_flat_green b-button_flat-size_medium b-button_float_right auto_prolong auto_prolong_btn" data-check="<?= $u_is_pro_auto_prolong?>">
                    <?= $u_is_pro_auto_prolong == 't' ? 'Выключить' : 'Включить'?>   
                </a>
                Вы можете автоматически продлевать действие аккаунта PRO каждый месяц при наличии <?= payed::PRICE_FRL_PRO?> рублей на личном счёте.
            </div>
<? } else {
            ?>
            <?php if(false && !is_emp() && (isWasPlatipotom() || isAllowTestPro())) { ?>
               <div class="b-fon b-fon_pad_10 b-fon_bg_d3f2c0 b-fon__nosik_bot">
                  Теперь можно приобрести аккаунт, оплатив его потом (через сервис <a class="b-layout__link" href="http://PlatiPotom.ru" target="_blank">PlatiPotom.ru</a>).<br>Вы станете PRO сразу, а оплатите его с отсрочкой до 30 дней.
               </div>
            <?php } ?>
<?php } ?>
          </td>
          <td class="b-layout__td b-layout__td_right b-layout__td_width_50ps b-layout__td_width_full_ipad">
<? if($pro_last) { ?>
  <? if( (strtotime($pro_last)-time())/86400 > 1 || $_SESSION['is_freezed'] || $freeze_set || $freezed_now ) { ?>
              <?php if( $freeze_set || $freezed_now ): ?>
              <div class="b-layout__h3_padbot_5">
                  <span class="b-layout__h3">Заморозка</span> 
                  <div class="i-shadow i-shadow_inline-block b-layout__txt b-layout__txt_padleft_20">
						
						<div class="b-shadow b-shadow_width_380 b-shadow_top_15 b-shadow_margleft_-175 b-shadow_zindex_3 b-shadow_hide b-shadow_width_300_ipad b-shadow_left_100_ipad">
                            <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_15">
                                                <div class="b-shadow__txt" style="line-height:100%;">
													<span class="b-layout__txt b-layout__txt_fontsize_11">
														Четыре раза в год вы можете приостановить действие вашего PRO-аккаунта. Воспользуйтесь этой функцией, если собираетесь в отпуск или будете оффлайн определенный период времени.
														<span class="block padtop_5">Заморозка доступна 4 раза в год (4 периода по 7 дней).</span>
														При досрочной разморозке неиспользованные дни от выбранного периода сгорают. 
                                                        <?php if(!$freeze_disabled && $last_freeze['freezed_cnt'] < 4) { $cnt = (4 - $last_freeze['freezed_cnt']);?>
                            <?
                            $cnt = 7;
                            $ending_1 = 'осталось 7 дней заморозки';
                            if (floor((28-$last_freeze['freezed_days'])/7) == 4 && !$_SESSION['is_freezed']) {
                              $cnt=28;
                              $ending_1 = 'осталось 28 дней заморозки';
                            } elseif ( (floor((28-$last_freeze['freezed_days'])/7) >= 3 && !$_SESSION['is_freezed']) ) {
                              $cnt=21;
                              $ending_1 = 'остался 21 день заморозки';
                            } elseif (floor((28-$last_freeze['freezed_days'])/7) >= 2 && !$_SESSION['is_freezed']) {
                              $cnt=14;
                              $ending_1 = 'осталось 14 дней заморозки';
                            }
                            ?>
														<span class="block padtop_10"><b>У вас <?=$ending_1 ?></b></span>
                                                        <?php }//if?>
													</span>
												</div>
						    </div>
						    <span class="b-shadow__icon b-shadow__icon_close"></span>
						    <span class="b-shadow__icon b-shadow__icon_nosik b-shadow__icon_nosik_left_110_ipad"></span>
						</div>
						
	              		<a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_bordbot_dot_41 b-layout__link_inline-block b-layout__link_lineheight_1 terms_btn" href="javascript:void(0)">Условия</a>
					</div>
              </div>
              <div id="freeze_disable" class="b-layout__txt <?= ($freeze_disabled?'':"b-layout__txt_hide");?> b-layout__txt_fontsize_11">
                  Заморозка доступна 4 раза в год (4 периода по 7 дней). Вы уже использовали эту функцию.
              </div>
              <div class="b-layout__txt <?= ( ( $freeze_set || $freezed_now ) ? '' : "b-layout__txt_hide"); ?>" id="freeze_on">
                    <span class="b-layout__txt b-layout__txt_padtop_5 b-layout__txt_inline-block b-layout__txt_fontsize_11 freeze_info" id="freeze_info">
                        <? if ($_SESSION['is_freezed']) { ?>
                            Ваш аккаунт заморожен с <b><?= date('d.m.Y', strtotime($_SESSION['freeze_from'])) ?> </b> на <b> <?= (strtotime($last_freeze['to_time'])-strtotime($last_freeze['from_time']))/60/60/24 ?> <?= getSymbolicName((strtotime($last_freeze['to_time'])-strtotime($last_freeze['from_time']))/60/60/24, 'day') ?></b>
                        <? } else if (( $freeze_set || $freezed_now)) { //if?>
                            Ваш аккаунт будет заморожен с <b><?= date('d.m.Y', strtotime($from_time)) ?> </b> на <b> <?= (strtotime($last_freeze['to_time'])-strtotime($last_freeze['from_time']))/60/60/24 ?> <?= getSymbolicName((strtotime($last_freeze['to_time'])-strtotime($last_freeze['from_time']))/60/60/24, 'day') ?></b>
                        <? }//if?>

                    </span>
                    <a href="javascript:void(0)" class="b-button b-button_margtop_-4 b-button_flat b-button_flat_blue b-button_flat-size_medium margleft_10 freezed_btn">
                        <?php if ($_SESSION['is_freezed']) { ?>
                            Разморозить
                        <?php } else if ($_SESSION['freeze_from']) {//if?>
                            Отменить
                        <?php } else { //else if?>
                            Заморозить
                        <?php } //else?>
                    </a>
              </div>
              <div class="b-layout__txt <?= ( !($freeze_set || $freezed_now) ?'':"b-layout__txt_hide");?> <?= $freeze_disabled?'b-layout__txt_hide':''?>" id="freeze_enable">
                       
                  Заморозка отключена.
                  
                  <div class="g-hidden">
                  
                        <span class="b-layout__txt b-layout__txt_padtop_5 b-layout__txt_inline-block">c&nbsp;</span>
                        <div id="freez_calendar" class="b-combo b-combo_inline-block b-combo_zindex_2">
                            <div class="b-combo__input b-combo__input_calendar b-combo__input_width_170 b-combo__input_arrow-date_yes <?= $dateFrozenMinLimit; ?> <?= $dateFrozenMaxLimit ?>">
                                <input type="text" value="<?= date('d.m.Y', strtotime('+ 1 day')); ?>" size="80" name="freez_date" id="freez_date" class="b-combo__input-text">
                                <label for="freez_date" class="b-combo__label"></label>
                                <span class="b-combo__arrow-date"></span> 
                            </div>
                        </div>
                        <input type="hidden" name="freez_type" id="freez_type" value="1">
                        <span class="b-layout__txt b-layout__txt_padtop_5 b-layout__txt_padbot_10 b-layout__txt_inline-block freeze_info">&nbsp;на
                            <span class="b-layout__text-selected freeze_type" id="ftype1">7</span><? if (floor((28-$last_freeze['freezed_days'])/7) >= 2 && !$_SESSION['is_freezed']) { ?>, 
                            <span class="b-layout__text-noselected b-post__label freeze_type" id="ftype2">14</span><? } ?><? if (floor((28-$last_freeze['freezed_days'])/7) >= 3 && !$_SESSION['is_freezed']) { ?>, 
                            <span class="b-layout__text-noselected b-post__label freeze_type" id="ftype3">21</span><? } ?><? if (floor((28-$last_freeze['freezed_days'])/7) == 4 && !$_SESSION['is_freezed']) { ?>, 
                            <span class="b-layout__text-noselected b-post__label freeze_type" id="ftype4">28</span><? } ?>
                            <!--
                            <? if (ceil($last_freeze['freezed_days']/7) < 1 || ceil($last_freeze['freezed_days']/7) == 2 && !$_SESSION['is_freezed']) { ?>
                            или <span class="b-layout__text-noselected b-post__label freeze_type" id="ftype2">14</span>
                            <? }//if?>
                            -->
                            дней
                        </span>

                        <input type="hidden" name="action_freeze" id="action_freeze" value="<?= $freeze_act; ?>" />

                        <a href="javascript:void(0)" class="b-button b-button_margtop_-4 b-button_flat b-button_flat_blue b-button_flat-size_medium margleft_10 freezed_btn">
                            <?php if($_SESSION['is_freezed']) {?>
                            Разморозить
                            <?php } else if($_SESSION['freeze_from']) {//if?>
                            Отменить
                            <?php } else { //else if?>
                            Заморозить
                            <?php } //else?>
                        </a>
                  
                  </div>
                  
              </div>
              <?php endif; ?>
  <? } ?>
<? } ?>
          </td>
       </tr>
    </table>
</div>