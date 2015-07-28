{{include "header.tpl"}}
<script type="text/javascript">
	
	billing.init();
    billing.exch=<?=EXCH_YM?>;
	function getMoney(v) {
		$$('#paysum').set('value', v);
		//billing.cur2FM(0, $('paysum'));
		document.getElementById('paysum').focus();
	}
</script>
<div class="body c">
				<div class="main c">
					<h1 class="b-page__title">ћой счет</h1>
					<div class="rcol-big">
						{{include "bill/bill_menu.tpl"}}
					<div class="tabs-in bill-t-in c">
							<form name="ydpay" id="ydpay" method="post" action="https://money.yandex.ru/eshop.xml">
							<div>
							<input class="wide" name="scid" value="2200" type="hidden" />
							<input type="hidden" name="ShopID" value="4551" />
							<input type="hidden" name="Sum" id="ammount" value="" />
							<input type="hidden" name="CustomerNumber" value="<?=$$account->id?>" />
							<h3>ќплата с помощью яндекс.ƒеньги</h3>
							
							<?php /*
							<p>—овместна€ акци€ проекта Free-lance.ru и сервиса яндекс.ƒеньги:</p>
							<ul class="yd-presents">
							<?if(is_emp()):?>
								<li><a href="javascript:void(0)" onClick="getMoney(1000);" class="dt-lnk">ѕополните счет яндекс.ƒеньгами на 1000 рублей</a> и получите в подарок <strong class="ydp-e">аккаунт <a class="b-layout__link" href="/payed/" class="ac-epro"><span title="PRO" class="b-icon b-icon__pro b-icon__pro_e"></span></a></strong> на мес€ц.</li>
								<li><a href="javascript:void(0)" onClick="getMoney(5000);" class="dt-lnk">ѕополните счет яндекс.ƒеньгами на 5000 рублей</a> и получите в подарок <strong class="ydp-e">85 FM на платную публикацию проекта</strong>.</li>
							<?else:?>
								<li><a href="javascript:void(0)" onClick="getMoney(2000);" class="dt-lnk">ѕополните счет яндекс.ƒеньгами на 2000 рублей</a> и получите в подарок <strong>аккаунт <a class="b-layout__link" href="/payed/"><span class="b-icon b-icon__pro b-icon__pro_f" title="PRO"></span></a></strong> на мес€ц.</li>
								<li><a href="javascript:void(0)" onClick="getMoney(5000);" class="dt-lnk">ѕополните счет яндекс.ƒеньгами на 5000 рублей</a> и получите в подарок <strong>размещение в платных местах на главной</strong> в течение недели.</li>
							<?endif; ?>
							<p>«а один перевод денег Ч один подарок.</p>
                            </ul> */
                            ?>
                            
                            
							<div class="bill-left-col2">
								
								<div class="form bill-form">
									<b class="b1"></b>
									<b class="b2"></b>
									<div class="form-in">
										
										<div class="form-block first">
											<div class="form-el" id="paysum_parent">
												<label class="form-label" for="paysum">—умма пополнени€:</label>
												<span class="form-input form-input2">
													<input type="text" value="" maxlength="12" id="paysum" class="i-bold" style="text-align:right;" onchange="billing.cur2FM(0, this); " /> <span id="curname">руб.</span>
                                                    <span class="form-nds">¬ том числе Ќƒ— &mdash; 18%</span>
												</span>
											</div>
										</div>
										<div class="form-block">
											<div class="form-el"><span class="fels">ѕосле нажати€ на кнопку <strong>ќплатить</strong> вы будете перенаправлены на сайт яндекс.ƒеньги.</span></div>
										</div>
										<div class="form-block last">
											<div class="form-btn">
												<input type="submit" value="ќплатить" onClick="return billing.checkSend($('paysum').value);" id="pay" class="i-btn" />
											</div>

										</div>
									</div>
									<b class="b2"></b>
									<b class="b1"></b>
								</div>

                                <?php
                                $need_paysum = (float) $_COOKIE['need_paysum'];
                                if($need_paysum>0) {
                                    ?>
                                    <script type="text/javascript">
                                    $('paysum').set('value', '<?=$need_paysum?>');
                                    billing.cur2FM(0, $('paysum'));
                                    </script>
                                    <?
                                }
                                unset($_COOKIE['need_paysum']);
                                ?>


							</div>
							<div class="bill-right-col2 bill-info">
							    <p style="width:420px;">яндекс.ƒеньги Ц доступный и безопасный способ оплатить услуги и сервисы Free-lance.ru. ѕополнение счета и оплата заказов происход€т в реальном времени <a href="https://money.yandex.ru/" target="_blank">на сайте платежной системы</a>.</p>
							    <p style="width:420px;">¬ы можете пополнить свой счет на Free-lance.ru через веб-приложение <a href="https://money.yandex.ru/" target="_blank">яндекс. ошелек</a>, с помощью специальной программы Ђ<a href="https://money.yandex.ru/" target="_blank">яндекс.ƒеньги</a>ї или же воспользовавшись <a href="https://money.yandex.ru/prepaid.xml" target="_blank">карточками яндекс.ƒенег</a>.</p> 
								
							</div>
							</div>
						</form>	
						</div>
					</div>
				</div>
			</div>
{{include "footer.tpl"}}
