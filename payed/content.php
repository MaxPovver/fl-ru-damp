<?
    
    /**
     * Устарелый шаблон с тарифами ПРО
     */

	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_offers_answers.php");
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/op_codes.php");
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/rating.php");
        
    if($uid) {
        $op_codes = new op_codes();
        $opcodes = $op_codes->getCodes('80,16,65');
    
        if($paid_specs = professions::getPaidSpecs($uid))
            $paid_spec_cnt = count($paid_specs);
        $free_spec_cnt = is_pro() ? 5 : 1;
        $spec_cnt = $paid_spec_cnt + $free_spec_cnt;
        $paid_spec_price = $opcodes[professions::OP_PAID_SPEC]['sum']*$paid_spec_cnt;

        $poa = new projects_offers_answers();
        $poa->GetInfo($uid);
        $poa_codes = $poa->GetOpCodes();
    
	   $user = new freelancer();
        // Изменяем авто продление PRO, если нужно
        if(strtolower($_GET['pro_auto_prolong'])=='on') {
            $user->setPROAutoProlong('on',$uid);
        }
        if(strtolower($_GET['pro_auto_prolong'])=='off') {
            $user->setPROAutoProlong('off',$uid);
        }

	    $user->GetUser($_SESSION['login']);
	    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
	    $account = new account();
        $ok = $account->GetInfo($uid, true);

        $u_is_pro_auto_prolong = $user->GetField($uid, $e, 'is_pro_auto_prolong', false); // Включено ли у юзера автоматическое продление PRO

        require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/professions.common.php");
        $xajax->printJavascript('/xajax/');
?>
<script type="text/javascript">
var account_sum = <?= round($account->sum, 2)?>;
var op = [];
<? foreach ($poa_codes as $ammount=>$sum) { ?>
op[<?=$ammount?>] = <?= round($sum, 2); ?>;
<? } ?>
var SPARAMS={<?
if($paid_specs)
foreach($paid_specs as $i=>$prof) {
    echo ($i ? ',' : '') . $i . ':[' . (int)$prof['paid_id'] . ',' . (int)$prof['prof_id'] . ']';
}
?>};
</script>
<? } ?>


<script type="text/javascript">
var alowLogin = function(){
    if($('login_inp').get('value') != '' && $('pass_inp').get('value') != ''){
        $('auth_form').submit();
    };
}
</script>

<div class="page-title">Услуги</div>
<div class="payed-outer">
    <div class="payed-h">
        <h1 class="b-page__title">Профессиональный аккаунт</h1>
        <p>Контакты фрилансеров с <span class="b-icon b-icon__pro b-icon__pro_f " title="платным аккаунтом" alt="платным аккаунтом"></span> аккаунтом видны всем пользователям сайта.</p>

        <div class="payed-h-user">
            <?php if($uid) { ?>
                <strong>Привет, <?=view_avatar($user->login, $user->photo, 1, 1, "")?> <?=$user->uname?><!--<span class="bgrd"></span>--></strong>
                Мы сберегли этот <span class="b-icon b-icon__pro b-icon__pro_f " title="платный аккаунт" alt="платный аккаунт"></span> специально для тебя :)
            <?php } ?>
        </div>
    </div>
    <div class="payed-cnt c">
        <?php if($uid) { ?>
        <div class="payed-cnt-r">
            
            <? if (!payed::IsUserWasPro($uid)): ?>
            <div class="payed-block">
                <b class="b1"></b>
                <b class="b2"><b class="b4"></b></b>
                <b class="b3"></b>
                    <div class="payed-block-in">
                        <form action="/payed/buy.php" method="post" name="testfrmbuy" id="testfrmbuy">
                        <div>
                		<input type="hidden" name="mnth" value="1" />
                		<input type="hidden" name="transaction_id" value="<?=$transaction_id?>" />
                		<input type="hidden" name="action" value="buy" />
                		<input type="hidden" name="oppro" value="<?= payed::get_opcode_action_test_pro(); ?>" />
                        <h4>Пробный <span title="платный аккаунт" class="b-icon b-icon__pro b-icon__pro_t b-icon_top_1"></span> аккаунт:</h4>
                        <p>Мы даём возможность опробовать профессиональный аккаунт тем, кто только начинает свою работу на сайте Free-lance.ru.</p>
                        <p>Вам будут доступны все преимущества профессионального аккаунта. Отличаться будет только цвет значка.</p>
                        <p>
                            <span class="payed-price">
                                <b class="b1"></b>
                                <b class="b2"></b>
                                <span class="payed-price-in">1 неделя <em>=</em> <strong><?= ($test_price = payed::GetProPrice(false, payed::get_opcode_action_test_pro())); ?> руб.</strong></span>
                                <b class="b2"></b>
                                <b class="b1"></b>
                            </span>
                        </p>
                        <div>
                            <a href="javascript:void(0)" onclick="checkTestBalance(this);return checkBalance('block_test_pay', 'testfrmbuy');" class="btn btn-blue"><span class="btn-lc"><span class="btn-m"><span class="btn-txt">Купить</span></span></span></a>
                        </div>
                        <div class="lnk-pay" style="display:none;" id="block_test_pay"><a href="/bill/?paysum=<?= round($test_price - $account->sum, 2)?>" onClick="Cookie.write('need_paysum', '<?= round($test_price-$account->sum, 2)?>');">Пополнить счет на <?=round($test_price - $account->sum,2)?> <?= ending(round($test_price - $account->sum), 'рубль', 'рубля', 'рублей');?></a></div>
						</div>
                        </form>
                    </div>
                <b class="b3"></b>
                <b class="b2"><b class="b4"></b></b>
                <b class="b1"></b>
            </div>     
            <? endif; ?>
            <div class="payed-block">
                <b class="b1"></b>
                <b class="b2"><b class="b4"></b></b>
                <b class="b3"></b>
                <div class="payed-block-in">
                    <form action="/payed/buy.php" method="post" name="frmbuy" id="frmbuy" onsubmit="return checkBalance('block_pro_pay');">
                    <div>
            		<input type="hidden" name="mnth" value="1" />
            		<input type="hidden" name="week" id="week" value="1" />
            		<input type="hidden" name="transaction_id" value="<?=$transaction_id?>" />
            		<input type="hidden" name="action" value="buy" />
                    <h4>Покупка <span class="b-icon b-icon__pro b-icon__pro_f b-icon_top_1" title="платного аккаунта" alt="платного аккаунта"></span> аккаунта:</h4>
                    <? if(is_pro(true)): ?>
                    <?
                    $last_time = $_SESSION['pro_last'];
                    if(floor((strtotime($last_time)-time())/(60*60*24)) > 0) {
                        $last_ending = floor((strtotime($last_time)-time())/(60*60*24));
                        $last_string1 = 'день';
                        $last_string2 = 'дня';
                        $last_string3 = 'дней';
                    } else if (floor((strtotime($last_time)-time())/(60*60)) > 0) {
                        $last_ending = floor((strtotime($last_time)-time())/(60*60));
                        $last_string1 = 'час';
                        $last_string2 = 'часа';
                        $last_string3 = 'часов';
                    } else if (floor((strtotime($last_time)-time())/60) > 0) {
                        $last_ending = floor((strtotime($last_time)-time())/(60));
                        $last_string1 = 'минута';
                        $last_string2 = 'минуты';
                        $last_string3 = 'минут';
                    }
                    if ($last_ending > 0) {?>
                        <p>Ваш <span class="b-icon b-icon__pro b-icon__pro_f " title="платный аккаунт" alt="платный аккаунт"></span> аккаунт истекает через <?=$last_ending?> <?=ending($last_ending, $last_string1, $last_string2, $last_string3)?></p>
                    <? }
                    endif; ?>
                    <table class="buy-pro-tbl">
                        <col width="22" />
                        <col width="70" />
                        <col width="15" />
                        <col width="75" />
                        <col width="15" />
                        <col width="70" />
                        <tr>
                            <td><input type="radio" name="oppro" value="48" onClick="if(this.checked) noSumAmmount(570, 'block_pro_pay', 'pro_pay_sum');" /></td>
                            <td><span class="payed-price"><b class="b1"></b><b class="b2"></b><span class="payed-price-in">570 руб.</span><b class="b2"></b><b class="b1"></b></span></td>
                            <td class="sign">х</td>
                            <td>1 месяц</td>
                            <td class="sign">=</td>
                            <td><strong>570 руб.</strong></td>
                        </tr>
                        <tr>
                            <td><input type="radio" name="oppro" value="49" onClick="if(this.checked) noSumAmmount(1620, 'block_pro_pay', 'pro_pay_sum');" /></td>
                            <td><span class="payed-price"><b class="b1"></b><b class="b2"></b><span class="payed-price-in">540 руб.</span><b class="b2"></b><b class="b1"></b></span></td>
                            <td class="sign">х</td>
                            <td>3 месяца</td>
                            <td class="sign">=</td>
                            <td><strong>1620 руб.</strong></td>
                        </tr>
                        <tr>
                            <td><input type="radio" name="oppro" value="50" onClick="if(this.checked) noSumAmmount(3060, 'block_pro_pay', 'pro_pay_sum');" /></td>
                            <td><span class="payed-price"><b class="b1"></b><b class="b2"></b><span class="payed-price-in">510 руб.</span><b class="b2"></b><b class="b1"></b></span></td>
                            <td class="sign">х</td>
                            <td>6 месяцев</td>
                            <td class="sign">=</td>
                            <td><strong>3060 руб.</strong></td>
                        </tr>
                        <tr>
                            <td><input type="radio" name="oppro" value="51" onClick="if(this.checked) noSumAmmount(5400, 'block_pro_pay', 'pro_pay_sum');" /></td>
                            <td><span class="payed-price"><b class="b1"></b><b class="b2"></b><span class="payed-price-in">450 руб.</span><b class="b2"></b><b class="b1"></b></span></td>
                            <td class="sign">х</td>
                            <td>12 месяцев</td>
                            <td class="sign">=</td>
                            <td><strong>5400 руб.</strong></td>
                        </tr>
                    </table>
                    <div>
                        <div style="white-space:nowrap">
                        <a href="javascript:void(0);" class="btn btn-blue" onClick="checkBalance('block_pro_pay', 'frmbuy');"><span class="btn-lc"><span class="btn-m"><span class="btn-txt">Купить</span></span></span></a>
                        </div>
                        <div class="lnk-pay" style="display:none" id="block_pro_pay"><a href="/bill/" onClick="Cookie.write('need_paysum', $('pro_pay_sum').get('html'));">Пополнить счет на <span id="pro_pay_sum">30</span> <span id="pro_pay_sum_curr">рублей</span></a></div>
                    </div>
                    </div>
                    </form>
                </div>
                <b class="b3"></b>
                <b class="b2"><b class="b4"></b></b>
                <b class="b1"></b>
            </div>

            <? if(is_pro() || $freezed_now) { ?>
            <div class="payed-block payed-block-freezing">
                <b class="b1"></b>
                <b class="b2"><b class="b4"></b></b>
                <b class="b3"></b>
                <div class="payed-block-in">
                    <div class="freezing-terms payed-price">
                        <b class="b1"></b>
                        <b class="b2"></b>
                        <div class="payed-price-in">
                            <a href="javascript:void(0);" onclick="$('freezing-terms').toggleClass('b-shadow_hide');" class="lnk-dot-grey">Условия</a>
                        </div>
                        <b class="b2"></b>
                        <b class="b1"></b>
                    </div>
                    <h4>Заморозка <span class="b-icon b-icon__pro b-icon__pro_z b-icon_top_1" title="Заморожен"></span></h4>
                    <? if((ceil($last_freeze['freezed_days']/7) <= 1 || ($freeze_set || $freezed_now)) && !$freezed_alert) { ?>
                    <form action="/payed/" method="post" name="frmfreeze" id="frmfreeze">
                    <div>
                        <input type="hidden" name="action" value="<?=$freeze_act?>" />
                        <div class="freezing-period c" id="date-selector">
                            <input type="hidden" name="pro_last" value="<?=$_SESSION['pro_last']?>" />
                            <div class="freezing-date first">
                                <input class="<?= ($freeze_set || $freezed_now) ? 'freeze_set' : '' ?>" type="hidden" name="from_date" value="<?=$from_time?>" />
                                <label>Начало:</label>
                                <select <?=($freeze_set || $freezed_now) ? 'disabled="disabled"' : ''?> >
                                    <? for($i = 1; $i <= 31; $i ++) { ?>
                                    <option><?= str_pad($i, 2, '0', STR_PAD_LEFT) ?></option>
                                    <? } ?>
                                </select>
                                <select <?=($freeze_set || $freezed_now) ? 'disabled="disabled"' : ''?> >
                                    <? for($i = 1; $i <= 12; $i ++) { ?>
                                    <option><?= str_pad($i, 2, '0', STR_PAD_LEFT) ?></option>
                                    <? } ?>
                                </select>
                                <select <?=($freeze_set || $freezed_now) ? 'disabled="disabled"' : ''?> >
                                    <? for($i = date('Y'); $i <= date('Y')+2; $i ++) { ?>
                                    <option><?=$i?></option>
                                    <? } ?>
                                </select>
                            </div>
                            <div class="freezing-date ">
                                <label>Период заморозки:</label>
                                <select name="to_date" <?=($freeze_set || $freezed_now) ? 'disabled="disabled"' : ''?>>
                                    <option value="1">7 дней</option>
                                    <? if (ceil($last_freeze['freezed_days']/7) < 1 || ceil($last_freeze['freezed_days']/7) == 2) { ?>
                                    <option value="2" <?= ($last_freeze['freezed_cnt'] == 1 && $last_freeze['freezed_days'] > 7) ? 'selected="selected"' : '' ?>>14 дней</option>
                                    <? } ?>
                                </select>
                            </div>
                        </div>
                        <? if($freeze_error) { ?>
                        <?= view_error($freeze_error) ?>
                        <br />
                        <? } ?>
                        <?
                        $act_label = 'Заморозить';
                        $act_descr = 'заморозить аккаунт';
                        if($freeze_set) {
                            $act_label = 'Отменить';
                            $act_descr = 'отменить заморозку';
                        }
                        if($freezed_now) {
                            $act_label = 'Разморозить';
                            $act_descr = 'разморозить аккаунт';
                        }
                        ?>
                        <div class="freez-attent" id="freez-attent" style="display:none;">В соответствии с <a href="https://feedback.fl.ru/article/details/id/129" target="_blank">правилами</a> предоставления услуги при выборе данного периода (14 дней) вы больше не сможете воспользоваться "Заморозкой PRO" в этом году</div>
                        <a href="javascript:void(0)" class="btn btn-<?= ($freeze_set || $freezed_now) ? 'pink' : 'green' ?>" onclick="return $('freezing-confirm').setStyle('display', '');"><span class="btn-lc"><span class="btn-m"><span class="btn-txt"><?=$act_label?></span></span></span></a>
                        <div class="freezing-ok" id="freezing-confirm" style="display:none;">
                            <strong>Вы уверены, что хотите <?=$act_descr?>?</strong>
                            <?if(!$freeze_set && !$freezed_now) { ?>Доступно до двух раз в год.<? } ?>
                            <?if($freezed_now) { ?>Вы частично использовали срок заморозки. При разморозке неиспользованные дни от выбранного периода сгорят.<? } ?>
                            <div class="freezing-btns"><input type="button" value="Да" class="i-btn" onclick="return $('frmfreeze').submit();" />&nbsp;
                                <input type="button" value="Нет" class="i-btn" onclick="return $('freezing-confirm').setStyle('display', 'none');" />
                            </div>
                        </div>
                     </div>
                    </form>
                    <? } else { ?>
                    <p class="freezing-msg">Заморозка доступна 2 раза в год (2 периода по 7 дней). Вы уже использовали эту функцию.</p>
                    <? } ?>
                </div>
                <b class="b3"></b>
                <b class="b2"><b class="b4"></b></b>
                <b class="b1"></b>
            </div>
												


<div id="freezing-terms" class="b-shadow b-shadow_center b-shadow_width_350 b-shadow_hide b-shadow_zindex_2">
	<div class="b-shadow__right">
		<div class="b-shadow__left">
			<div class="b-shadow__top">
				<div class="b-shadow__bottom">
					<div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_15">


                            <h4 class="b-shadow__h3">Заморозка</h4>
                            <div class="b-shadow__txt">Два раза в год вы можете приостановить действие вашего PRO-аккаунта. Воспользуйтесь этой функцией, если собираетесь в отпуск или будете оффлайн определенный период времени. </div>
                            <div class="b-shadow__txt">Заморозка доступна 2 раза в год (2 периода по 7 дней).</div>
                            <div class="b-shadow__txt">При досрочной разморозке неиспользованные дни от выбранного периода сгорают.</div>
                            <? if ( date("Y-m-d") < '2012-01-01' ) { ?>
                                <div class="b-shadow__txt" style="color:red">Обратите внимание: в связи с тем, что фактически заморозка аккаунта PRO начинает действовать с 00:00 следующих суток, то заморозка, произведенная 31&nbsp;декабря&nbsp;2011 года, активируется в 00:00 1&nbsp;января&nbsp;2012 года и будет засчитана как заморозка профессионального аккаунта за 2012 год.</div>
                            <? } ?>
                            <div class="ov-lnks">
                                <a href="javascript:void(0);" onclick="$(this).getParent('.b-shadow').toggleClass('b-shadow_hide');" class="lnk-dot-blue">Закрыть</a>
                            </div>


					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="b-shadow__tl"></div>
	<div class="b-shadow__tr"></div>
	<div class="b-shadow__bl" style="bottom:auto !important"></div>
	<div class="b-shadow__br" style="bottom:auto !important"></div>
</div>


												
												
												
												
												
            <? } ?>

			<? if($user->is_pro=='t'): ?>
			<div class="payed-block">
       <a name="pro_autoprolong"></a>
                <b class="b1"></b>
                <b class="b2"><b class="b4"></b></b>
                <b class="b3"></b>
                <div class="payed-block-in">
                    <h4>Автопродление <span class="b-icon b-icon__pro b-icon__pro_f8 b-icon_top_1"></span></h4>
                    <p>Теперь вам не нужно следить за сроком действия профессионального аккаунта.</p>
                    <p>При включении данной опции с вашего счета ежемесячно будет списываться 570 рублей (при их наличии на вашем личном счете).</p>
					<div class="payed-block-btns c">
    					<? if($u_is_pro_auto_prolong=='t'): ?>
    					    <a href="/payed/?pro_auto_prolong=off#pro_autoprolong" class="btn btn-pink"><span class="btn-lc"><span class="btn-m"><span class="btn-txt">Отключить</span></span></span></a>
                        <? else: ?>
                            <a href="/payed/?pro_auto_prolong=on#pro_autoprolong" class="btn btn-green"><span class="btn-lc"><span class="btn-m"><span class="btn-txt">Включить</span></span></span></a>
                        <? endif; ?>
					   
					</div>
                </div>
                <b class="b3"></b>
                <b class="b2"><b class="b4"></b></b>
                <b class="b1"></b>
            </div>
            <? endif; ?>
            
            
		</div>
        <?php } else{ ?>
        <div class="payed-cnt-r">
        <div class="b-fon b-fon_padbot_20">
            <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffebbf"> <span class="b-fon__attent_pink"></span>Чтобы купить аккаунт PRO, войдите или<br /><a href="/registration/" class="b-layout__link b-layout__link_color_fd6c30">зарегистрируйтесь</a> как фрилансер.</div>
        </div>        
		<div class="b-layout">
        <form action="/" method="post" id="auth_form">
        <table cellspacing="0" cellpadding="0" border="0" class="b-layout__table">
                <tr class="b-layout__tr">
                    <td class="b-layout__one b-layout__one_width_55"><label for="login_inp" class="b-layout__txt b-layout__txt_block b-layout__txt_padtop_5">Логин</label></td>
                    <td class="b-layout__one  b-layout__one_padbot_20">
                        <div class="b-combo">
                            <div class="b-combo__input">
                                <input type="text" tabindex="100" name="login" size="80" value="" class="b-combo__input-text" id="login_inp" /><label class="b-combo__label" for="login_inp"></label>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr class="b-layout__tr">
                    <td class="b-layout__one b-layout__one_width_55"><label for="pass_inp" class="b-layout__txt b-layout__txt_block b-layout__txt_padtop_5">Пароль</label></td>
                    <td class="b-layout__one b-layout__one_padbot_20">
                        <div class="b-combo">
                            <div class="b-combo__input">
                                <input type="password" tabindex="101" name="passwd" size="80" value="" class="b-combo__input-text" id="pass_inp" /><label class="b-combo__label" for="pass_inp"></label>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr class="b-layout__tr">
                    <td class="b-layout__one b-layout__one_width_55">&nbsp;</td>
                    <td class="b-layout__one b-layout__one_padbot_20 b-layout__one_padright_10">
                        <div class="b-check">
                            <input type="checkbox" tabindex="102" name="autologin" value="1" class="b-check__input" id="remember" />
                            <label class="b-check__label b-check__label_fontsize_13" for="remember">Запомнить пароль</label>
                        </div>
                    </td>
                </tr>
        </table>
    
                
        <div class="b-buttons b-buttons_padleft_57">
            <a tabindex="103" href="javascript:void()" onclick="alowLogin(); return false;" class="b-button b-button_valign_top b-button_rectangle_color_green">
                <span class="b-button__b1">
                    <span class="b-button__b2">
                        <span class="b-button__txt">Войти</span>
                    </span>
                </span>
            </a>
            &nbsp;&nbsp;
            <div class="b-buttons__txt"><a href="/remind/" class="b-buttons__link">восстановить пароль</a> <span class="b-buttons__txt">или</span><br /> <a href="/registration/" class="b-buttons__link b-buttons__link_color_fd6c30">зарегистрироваться</a></div>
        </div>
        <input type="hidden" value="login" name="action">
        <input type="hidden" value="/payed/" name="redirect">										
        </form>
    </div>        
        </div>
        <?php } ?>

        <div class="payed-cnt-l">
            <div class="payed-compar-tbl">
                <b class="b1"></b>
                <b class="b2"><b class="b4"></b></b>
                <b class="b3"></b>
                <div class="payed-compar-tbl-in">
                    <table class="payed-compar">
                        <col />
                        <col width="110" />
                        <col width="120" />
                        <thead>
                            <tr>
                                <th>Возможности</th>
                                <td class="td-pro"><strong>Аккаунт <span class="b-icon b-icon__pro b-icon__pro_f " title="платный аккаунт" alt="платный аккаунт"></span></strong></td>
                                <td>Базовый аккаунт</td>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th style="background-color:#eeffe2">Ваши контакты видны работодателям</th>
                                <td class="td-pro"><img src="/images/check.gif" alt="&diams;" /></td>
                                <td style="background-color:#eeffe2">—</td>
                            </tr>
                            <tr>
                                <th>Количество ответов на проекты (в месяц)</th>
                                <td class="td-pro">Неограниченно</td>
                                <td><?= projects_offers_answers::FREE_ANSWERS_CNT; ?></td>
                            </tr>
                            <tr>
                                <th>Возможность отвечать на проекты с пометкой «Только для <span class="b-icon b-icon__pro b-icon__pro_f " title="владельцев платного аккаунта" alt="владельцев платного аккаунта"></span>»</th>
                                <td class="td-pro"><img src="/images/check.gif" alt="&diams;" /></td>
                                <td>—</td>
                            </tr>
                            <tr>
                                <th>Размещение ваших ответов на проекты выше остальных — в зоне <span class="b-icon b-icon__pro b-icon__pro_f " title="платный аккаунт" alt="платный аккаунт"></span></th>
                                <td class="td-pro"><img src="/images/check.gif" alt="&diams;" /></td>
                                <td>—</td>
                            </tr>
                            <tr>
                                <th>Размещение в каталоге выше остальных — в зоне <span class="b-icon b-icon__pro b-icon__pro_f " title="платный аккаунт" alt="платный аккаунт"></span></th>
                                <td class="td-pro"><img src="/images/check.gif" alt="&diams;" /></td>
                                <td>—</td>
                            </tr>
                            <tr>
                                <th>Рейтинг x 1.2</th>
                                <td class="td-pro"><img src="/images/check.gif" alt="&diams;" /></td>
                                <td>—</td>
                            </tr>
                            <tr>
                                <th>Количество специализаций, по которым вы размещаетесь в <a href="/freelancers/">каталоге</a></th>
                                <td class="td-pro">5</td>
                                <td>1</td>
                            </tr>
                            <tr>
                                <th>Возможность загружать работы в ответах на проекты</th>
                                <td class="td-pro"><img src="/images/check.gif" alt="&diams;" /></td>
                                <td>—</td>
                            </tr>
                            <tr>
                                <th>Превью работ в портфолио</th>
                                <td class="td-pro"><img src="/images/check.gif" alt="&diams;" /></td>
                                <td>—</td>
                            </tr>
                            <tr>
                                <th>Размещение работ в каталоге фрилансеров в разделе «<a href="/portfolio/">Работы</a>»</th>
                                <td class="td-pro"><img src="/images/check.gif" alt="&diams;" /></td>
                                <td>—</td>
                            </tr>
                            <tr>
                                <th>Возможность создавать <a href="/commune/">сообщества</a></th>
                                <td class="td-pro"><img src="/images/check.gif" alt="&diams;" /></td>
                                <td>—</td>
                            </tr>
                            <tr>
                                <th>Отсутствие рекламы на личной странице</th>
                                <td class="td-pro"><img src="/images/check.gif" alt="&diams;" /></td>
                                <td>—</td>
                            </tr>
                            <tr>
                                <th>Возможность изменять заголовок личной страницы</th>
                                <td class="td-pro"><img src="/images/check.gif" alt="&diams;" /></td>
                                <td>—</td>
                            </tr>
                            <tr>
                                <th>Возможность показать лучшие работы из разных специализаций</th>
                                <td class="td-pro"><img src="/images/check.gif" alt="&diams;" /></td>
                                <td>—</td>
                            </tr>
                            <tr class="last">
                                <th>Реклама вашего профиля в проектах</th>
                                <td class="td-pro"><img src="/images/check.gif" alt="&diams;" /></td>
                                <td>—</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <b class="b3"></b>
                <b class="b2"><b class="b4"></b></b>
                <b class="b1"></b>
            </div>
        </div>
	</div>
</div>