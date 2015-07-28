{{include "header.tpl"}}
<script type="text/javascript">
billing.init();
    var rate    = <?php echo EXCH_OSMP; ?>;
    var min_sum = <?php echo qiwipay::MIN_SUM; ?>;
    var max_sum = <?php echo qiwipay::MAX_SUM; ?>;

    var rur2fm = function(){
billing.clearEvent(document.getElementById('qiwi_rur_edit'));
        var rur = document.getElementById('qiwi_rur_edit').value;
if(!billing.isNumeric(String(rur))) return billing.tipView(document.getElementById('qiwi_rur_edit'), 'Пожалуйста, введите числовое значение');
        var fm = rur / rate;
        document.getElementById('qiwi_fm_edit').value = fm % 2 ? fm.toFixed(2) : fm;
billing.clearEvent(document.getElementById('qiwi_rur_edit'));
billing.clearEvent(document.getElementById('qiwi_fm_edit'));
    }

    var fm2rur = function(){
billing.clearEvent(document.getElementById('qiwi_fm_edit'));
        var fm = document.getElementById('qiwi_fm_edit').value;
if(!billing.isNumeric(String(fm))) return billing.tipView(document.getElementById('qiwi_fm_edit'), 'Пожалуйста, введите числовое значение');
        var rur = fm * rate;
        document.getElementById('qiwi_rur_edit').value = rur % 2 ? rur.toFixed(2) : rur;
billing.clearEvent(document.getElementById('qiwi_fm_edit'));
billing.clearEvent(document.getElementById('qiwi_rur_edit'));
}
window.addEvent('domready', function() {
if(document.getElementById('qiwi_rur_edit').value) rur2fm();
<? if (count($$alert)) { ?>
    window.addEvent('domready', function(){
        window.scrollTo(0, $('scroll_to').getPosition().y - 40)
    })
<? } ?>
});
</script>
<div class="body c">
	<div class="main c">
					<h1 class="b-page__title">Мой счет</h1>
		<div class="rcol-big">
			{{include "bill/bill_menu.tpl"}}
   			<div class="tabs-in bill-t-in c">
   				<h3 id="scroll_to">Оплата через QIWI Кошелёк</h3>
   				<div class="bill-left-col2">
   					<div class="form bill-form">
   						<b class="b1"></b>
   						<b class="b2"></b>
   						<div class="form-in">
   						    <form action="." method="post" id="frm" onsubmit="return validateQiwi();">
									<div>
    							<div class="form-block first">

                                    <?/*<div class="form-el">
                                        <label class="form-label" for="qiwi_fm_edit">Сумма пополнения:</label>
                                        <span class="form-input form-input2" id="qiwi_fm_edit_parent">
                                            <input onfocus="if(this.value==0) { this.value=''; $('qiwi_rur_edit').set('value',''); };billing.clearEvent($('qiwi_fm_edit'));" onchange="this.value=this.value.replace(/,/g, '.');fm2rur();" type="text" style="text-align: right" value="" id="qiwi_fm_edit" class="i-bold" /> FM
                                        </span>
                                    </div>*/?>
                                                            
    								<div class="form-el">
    									<label class="form-label" for="qiwi_rur_edit">Сумма пополнения:</label>
    									<span class="form-input form-input2" id="qiwi_rur_edit_parent">
    										<input  type="text" name="sum" value="<?=$$sum?>" maxlength="5" id="qiwi_rur_edit" class="i-bold" style="text-align:right" onchange="this.value=this.value.replace(/,/g, '.');if(this.value.trim()!=''&&isNaN(this.value))this.value=0; " onfocus="if(this.get('value')==0) { this.set('value',''); } if($('qiwi-sum'))$('qiwi-sum').style.display='none';billing.clearEvent($('qiwi_rur_edit'));" /> руб.
    									</span>
    									<? if($$alert['sum']) { ?>
    									<div class="tip" style="left:400px" id="qiwi-sum">
    									    <div class="tip-in"><div class="tip-txt"><div class="tip-txt-in">
    									      <span class="middled"><strong><?=$$alert['sum']?></strong>
    									        <em>Введите сумму от <?=qiwipay::MIN_SUM?> до <?=qiwipay::MAX_SUM?></em>
    									      </span>
    									    </div></div></div>
    									</div>
    									<? } ?>
    								</div>
    							</div>
    							<div class="form-block">
    								<div class="form-el">
    									<label class="form-label3" for="phone">Мобильный телефон:</label>
													<span class="tlf_place tlf_place_ru">в <strong>России</strong> или <a href="javascript:void(0)">Казахстане</a></span>
													<span class="tlf_place tlf_place_kz global_hide">в <a href="javascript:void(0)">России</a> или <strong>Казахстане</strong></span>
													<div class="tlf-input">
                                                        <span class="form-input form-input3" id="phone_parent">
                                                            
                                                            +<span id="phone_code">7</span>&nbsp;
                                                            <span><input type="text" name="phone" value="<?=$$phone?>"  maxlength="10" id="phone" onfocus="if($('qiwi-tel'))$('qiwi-tel').style.display='none';billing.clearEvent($('phone'));" onkeyup="aHint(this)" onchange="aHint(this)"/></span>
                                                        </span>
													</div>
                                                    
    									<? if($$alert['phone']) { ?>
    									<div class="tip" style="left:400px" id="qiwi-tel">
    									    <div class="tip-in"><div class="tip-txt"><div class="tip-txt-in">
    										    <span class="middled"><strong>Введите номер телефона в федеральном формате без "8" и без "+7"</strong>
    										      <em><b>Пример:</b> 9161234567</em>
    										    </span>
    									    </div></div></div>
    									</div>
    									<?php
    									}
    									elseif ( $$alert['max_phone_num'] ) {
                                        ?>
                                        <div class="tip" style="left:400px" id="qiwi-tel">
                                            <div class="tip-in">
                                                <div class="tip-txt">
                                                    <div class="tip-txt-in">
                                                        <span class="middled"><strong>Количество мобильных номеров, использованных для пополнения счета<br/> через сервис QIWI Кошелек, превысило <?=qiwipay::MAX_PHONE_NUM?> номеров</strong></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
    									}
    									elseif ( $$alert['max_pay_num'] ) {
    									?>
                                        <div class="tip" style="left:400px" id="qiwi-tel">
                                            <div class="tip-in">
                                                <div class="tip-txt">
                                                    <div class="tip-txt-in">
                                                        <span class="middled"><strong><?=$$alert['max_pay_num']?></strong></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
    									}
    									?>
    								</div>
    								<div class="form-el">
    									<label class="form-label3" >Комментарий (необязательно):</label>
    									<div class="form-input">
    										<textarea name="comment" rows="5" cols="20" onkeypress="maxlength(this,255)" onchange="maxlength(this,255)" onfocus="this.select();this.onfocus=null;"><?=$$comment?></textarea>
    										<div style="text-align:right"><small>до 255 символов</small></div>
    									</div>

    								</div>
    							</div>
                                                        <div class="form-block">
								<div class="form-el" id="sum_parent">
									<div class="form-input form-captcha" style="float:right">
										<div class="c" style="margin-bottom:5px"><img src="/image.php" alt="" id="rndnumimage" width="130" height="60" onClick="$('rndnumimage').set('src','/image.php?r='+Math.random());" /> <a href="#" onClick="$('rndnumimage').set('src','/image.php?r='+Math.random()); return false;">Обновить код</a></div>
										<input type="text"  name="rndnum" value="" size="5" onfocus="billing.clearEvent(this);" onkeydown="if(event.keyCode==13)document.getElementById('frm').btns.focus();" />
									</div>
									<label class="form-label3" style="width:170px; padding-top:66px;" >Введите код с картинки:</label>
                                                                        <? if($$alert['rndnum']) { ?>
    									<div class="tip" style="left:400px" id="qiwi-tel">
    									    <div class="tip-in"><div class="tip-txt"><div class="tip-txt-in">
    										    <span class="middled"><strong><? echo $$alert['rndnum']; ?></strong>
    										    </span>
    									    </div></div></div>
    									</div>
    									<? } ?>
								</div>
							</div>
    							<div class="form-block last">
    								<div class="form-btn">
    									<input type="submit" value="Выставить счет" class="i-btn" />
    								</div>
    							</div>
    							<input type="hidden" name="action" value="create" />
									</div>
    					    </form>
   						</div>
   						<b class="b2"></b>
   						<b class="b1"></b>
   					</div>
   				</div>

                <?php
                $need_paysum = (float) $_COOKIE['need_paysum'];
                if($need_paysum>0) {
                    ?>
                    <script type="text/javascript">
                    $('qiwi_rur_edit').set('value', '<?=$need_paysum?>');
                    fm2rur();
                    </script>
                    <?
                }
                unset($_COOKIE['need_paysum']);
                ?>

   				<div class="bill-right-col2 bill-info" style="width: 420px;">
   				    <? if($$success) { ?>
   				      <?=view_info("&nbsp;Счет на {$$success} руб. успешно сформирован.")?><br/>
   				    <? } else if ($$alert['qiwi']) { ?>
   				      <?=view_error('<span style="color:red"><strong>Ошибка</strong>.&nbsp;'.$$alert['qiwi'].'</span>')?><br/>
   				    <? } ?>
   				    
   				    <div class="pay-qiwi-logo c">
                		<img src="/images/qiwi-logo.gif" width="150" height="60" alt="" />
                        <a target="_blank" href="http://w.qiwi.ru">w.qiwi.ru</a>
                    </div>
                    
   					<p>
   					  После формирования заявки вам необходимо произвести оплату через терминал QIWI.
   					  Остаток средств можно перечислить на счет мобильного телефона или другого сервиса,
   					  используя уже существующий QIWI Кошелек или зарегистрировав новый.
   					</p>
   					
<?php 
/*
<p>С помощью <a href="#">QIWI Кошелька</a> Вы можете  моментально и без комиссии оплатить тысячи товаров и услуг через  <a href="#">web</a>- и <a href="#">мобильный</a> сайты,  <a href="#">приложения для мобильных устройств</a>, социальных сетей,  <a href="#">SMS-команды</a> и терминалы QIWI. QIWI Кошелек легко <a href="#">пополнить</a> бесплатно в терминалах QIWI и партнеров, салонах сотовой связи, супермаркетах, банкоматах, через интернет- или мобильный банк.</p>
					<p>Совершать платежи Вы можете не только со счета QIWI Кошелька, но и банковской картой, с  лицевого счета мобильного телефона Билайн, МегаФон, МТС, наличными.  
Если у Вас еще нет QIWI Кошелька, Вы можете бесплатно зарегистрировать его на <a href="#">сайте</a>  или в любом из приложений за несколько минут. Остались вопросы? Задайте их в официальном <a href="#">блоге</a> компании. </p>
*/
?>
   					
   					<p>Дополнительную информацию вы можете получить <a target="_blank" href="https://feedback.free-lance.ru/article/details/id/160" class="hlp_lnk">в разделе помощи</a>.</p>

<? /*
   					<p><strong>Адреса терминалов:</strong></p>
   					<ul class="bill-qiwi-addresses">
   						<li><a href="">пр-т Победителей, 14</a></li>
   						<li><a href="">ул. Шафарнянская, 24</a></li>
   						<li><a href="">ул. Куйбышева, 4</a></li>
   						<li><a href="">ул. Гинтовта, 12</a></li>
   						<li><a href="">ул. Нововиленская, 10а</a></li>
   					</ul>
   					<p><a href="">Все терминалы</a></p>
*/ ?>
   				</div>
   			</div>
		</div>
	</div>
</div>

{{include "footer.tpl"}}
