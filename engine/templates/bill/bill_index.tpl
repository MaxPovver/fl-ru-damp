{{include "header.tpl"}}
<div class="body c">
				<div class="main c">
					<h1 class="b-page__title">Мой счет</h1>
					<div class="rcol-big">
						{{include "bill/bill_menu.tpl"}}
						<div class="tabs-in bill-t-in c">
							<h3>Выберите способ оплаты</h3>
                                                        <? if(is_emp()){ ?>
							<div class="form bill-norisk-imp fs-o">
								<b class="b1"></b>
								<b class="b2"></b>
								<div class="form-in">
                                    Средства с личного счета не могут быть использованы для резервирования в «Безопасной Сделке». Зачисленную сумму можно будет потратить только на приобретение платных сервисов сайта.
								</div>
								<b class="b2"></b>
								<b class="b1"></b>
							</div>
                                                        <? } ?>
							<div class="bill-v c">

								<h4>Платежные системы</h4>
								<div class="bill-v-in">
                                    <a class="b-button b-button_bill b-button_bill_mid b-button_margright_5" href="/<?=$$name_page?>/webmoney/">        
                                        <span class="b-button__b1">
                                      <span class="b-button__b2">
                                          <img class="b-button__pic" src="/images/bill-wm1.png" alt="WebMoney" title="WebMoney"/>
                                            </span>
                                        </span>
                                    </a>
                                    <a class="b-button b-button_bill b-button_bill_mid b-button_margright_5" href="/<?=$$name_page?>/yandex/">        
                                        <span class="b-button__b1">
                                      <span class="b-button__b2">
                                                <img class="b-button__pic" src="/images/bill-yad.png" alt="Yandex деньги" />
                                            </span>
                                        </span>
                                    </a>
                                    <a class="b-button b-button_bill b-button_bill_mid b-button_margright_5" href="/<?=$$name_page?>/qiwipurse/">        
                                        <span class="b-button__b1">
                                      <span class="b-button__b2">
                                                <img class="b-button__pic" src="/images/bill-qp.png" alt="QIWI кошелек" />
                                            </span>
                                        </span>
                                    </a>
                                    <a class="b-button b-button_bill b-button_bill_mid b-button_margright_5" href="/<?=$$name_page?>/webpay/">        
                                        <span class="b-button__b1">
                                      <span class="b-button__b2">
                                                <img class="b-button__pic" src="/images/veb-koshel.png" alt="Веб-кошелек" style="margin:15px 10px;" />
                                            </span>
                                        </span>
                                    </a>
								</div>
							</div>
							<div class="bill-v c">
								<h4>Терминалы оплаты и наличные</h4>
								<div class="bill-v-in">
                                    <a class="b-button b-button_bill b-button_bill_mid b-button_margright_5" href="/<?=$$name_page?>/qiwi/">        
                                        <span class="b-button__b1">
                                      <span class="b-button__b2">
                                                <img class="b-button__pic" src="/images/bill-osmp.png" alt="ОСМП" />
                                            </span>
                                        </span>
                                    </a>
                                    <a class="b-button b-button_bill b-button_bill_mid b-button_margright_5" href="/<?=$$name_page?>/qiwi/">        
                                        <span class="b-button__b1">
                                      <span class="b-button__b2">
                                                <img class="b-button__pic" src="/images/bill-qiwi.png" alt="QIWI" />
                                            </span>
                                        </span>
                                    </a>
                                    <a class="b-button b-button_bill b-button_bill_mid b-button_margright_5" href="/<?=$$name_page?>/svyasnoy/">        
                                        <span class="b-button__b1">
                                      <span class="b-button__b2">
                                                <img class="b-button__pic" src="/images/cvyaznoy.png" alt="Связной" style="margin-bottom:1px" />
                                            </span>
                                        </span>
                                    </a>
                                    <a class="b-button b-button_bill b-button_bill_mid b-button_margright_5" href="/<?=$$name_page?>/euroset/">        
                                        <span class="b-button__b1">
                                      <span class="b-button__b2">
                                                <img class="b-button__pic" src="/images/euroset.png" alt="Евросеть" style="margin-bottom:1px" />
                                            </span>
                                        </span>
                                    </a>
                                
										<?/*<li><a href="/<?=$$name_page?>/elecsnet"><span><img src="/images/bill-elecsnet.png" alt="Элекснет" width="151" height="51"></span></a></li>*/?>

								</div>
							</div>
							<div class="bill-v c">
								<h4>Пластиковые карты</h4>
								<div class="bill-v-in">
                                    <a class="b-button b-button_bill b-button_bill_mid b-button_margright_5" href="/<?=$$name_page?>/card/">        
                                        <span class="b-button__b1">
                                      <span class="b-button__b2">
                                                <img class="b-button__pic" src="/images/bill-mc.png" alt="MasterCard"  />
                                            </span>
                                        </span>
                                    </a>
                                    <a class="b-button b-button_bill b-button_bill_mid b-button_margright_5" href="/<?=$$name_page?>/card/">        
                                        <span class="b-button__b1">
                                      <span class="b-button__b2">
                                                <img class="b-button__pic" src="/images/bill-visa.png" alt="Visa" />
                                            </span>
                                        </span>
                                    </a>
								</div>
							</div>
                            <? /* временно отключено #0019358
							<div class="bill-v c">
								<h4>С помощью SMS</h4>
								<div class="bill-v-in">
                                
                                    <a class="b-button b-button_bill b-button_bill_mid b-button_margright_5" href="/<?=$$name_page?>/sms/">        
                                        <span class="b-button__b1">
                                      <span class="b-button__b2">
                                                <img class="b-button__pic" src="/images/bill-mts.png" alt="МТС" />
                                            </span>
                                        </span>
                                    </a>
                                    <a class="b-button b-button_bill b-button_bill_mid b-button_margright_5" href="/<?=$$name_page?>/sms/">        
                                        <span class="b-button__b1">
                                      <span class="b-button__b2">
                                                <img class="b-button__pic" src="/images/bill-beeline.png" alt="Билайн" />
                                            </span>
                                        </span>
                                    </a>
                                    <a class="b-button b-button_bill b-button_bill_mid b-button_margright_5" href="/<?=$$name_page?>/sms/">        
                                        <span class="b-button__b1">
                                      <span class="b-button__b2">
                                                <img class="b-button__pic" src="/images/bill-megafon.png" alt="Мегафон" />
                                            </span>
                                        </span>
                                    </a>
                                    <a class="b-button b-button_bill b-button_bill_mid b-button_margright_5" href="/<?=$$name_page?>/sms/">        
                                        <span class="b-button__b1">
                                      <span class="b-button__b2">
												<div class="b-button__txt b-button__txt_color_0f71c8  b-button__txt_padlr_15 b-button__txt_padtop_17">Другой оператор</div>
                                            </span>
                                        </span>
                                    </a>
								</div>
							</div>*/?>
							
							<div class="bill-v c">
								<h4>Интернет-банк</h4>
								<div class="bill-v-in">
                                    <a class="b-button b-button_bill b-button_bill_mid b-button_margright_5" href="/<?=$$name_page?>/alphabank/">        
                                        <span class="b-button__b1">
                                      <span class="b-button__b2">
                                                <img class="b-button__pic" src="/images/bill-alfa.png" alt="Альфа-Банк" />
                                            </span>
                                        </span>
                                    </a>
								</div>
							</div>
							<div class="bill-v c">
								<h4>Безналичный расчет</h4>
								<div class="bill-v-in">
                                    <a class="b-button b-button_bill b-button_bill_mid b-button_margright_5" href="/<?=$$name_page?>/bank/">        
                                        <span class="b-button__b1">
                                      <span class="b-button__b2">
                                                <div class="b-button__txt b-button__txt_padlr_25 b-button__txt_color_0f71c8 b-button__txt_padtop_17">Счёт для юридических лиц и ИП</div>
                                            </span>
                                        </span>
                                    </a>
                                    <a class="b-button b-button_bill b-button_bill_mid b-button_margright_5" href="/<?=$$name_page?>/sber/">        
                                        <span class="b-button__b1">
                                            <span class="b-button__b2">
                                                <div class="b-button__txt b-button__txt_padlr_25 b-button__txt_color_0f71c8 b-button__txt_padtop_17">Квитанция для физических лиц</div>
                                            </span>
                                        </span>
                                    </a>
								</div>
							</div>
							
						</div>
					</div>

				</div>
			</div>
{{include "footer.tpl"}}	
