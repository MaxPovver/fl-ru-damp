{{include "header.tpl"}}
<div class="body c">
	<div class="main c">
					<h1 class="b-page__title">Мой счет</h1>
		<div class="rcol-big">
			{{include "bill/bill_menu.tpl"}}

			
			<div class="tabs-in bill-t-in bill-sms-info">
    			<h3>Оплата с помощью SMS</h3>
    			<div class="bill-info">
    				<p>Только для России, Беларуси и Украины (<a href="http://rates.planet3.ru/Ext.aspx" target="_blank">Другие операторы</a>)</p>
    				<p class="bill-mobile c">
    					<img src="/images/mobile/beeline.png" alt="Билайн" height="49" />
    					<img src="/images/mobile/mts.png" alt="МТС" height="49" />
    					<img src="/images/mobile/megafon.png" alt="Мегафон" height="49" />
    					<img src="/images/mobile/velcom.png" alt="VELCOM" height="49" />
    					<img src="/images/mobile/life.png" alt="life:)" height="49" />
    				</p>
    				<div class="form fs-o form-sms-txt">
    					<b class="b1"></b>
    					<b class="b2"></b>
    					<div class="form-in">
    						Отправьте SMS с кодом &nbsp;
    						<span class="sms-code-big">
    							<span class="sms-l">
    								<span class="sms-in">
    									<strong>free 1+<span id="lp-acc-value"><?=$_SESSION['login']?></span></strong>
    								</span>
    							</span>
    						</span>
    						на нужный номер:
    						<span class="form-space bill-sms-space">Здесь пробел, обязательно!</span>
    					</div>
    					<b class="b2"></b>
    					<b class="b1"></b>
    				</div>
    				<div class="bill-sms-tbl">
    					<b class="b1"></b>
    					<b class="b2"></b>
    					<div class="bill-sms-tbl-in">
							<table cellpadding="0" cellspacing="0"> 
								<thead>
									<tr>
										<th class="col1">Сумма&nbsp;пополнения</th>
										<th class="col2">Номер&nbsp;SMS</th>
										<th class="cols">Россия</th>
										<th class="cols">Беларусь</th>
										<th class="cols">Украина</th>
										<th class="col7">Комментарий</th>
									</tr>
								</thead>
								<tbody> 
								    <? foreach(sms_services::$services['1'] as $phone=>$aOne):$i++ ?>
								    <tr <?if($aOne==end(sms_services::$services['1'])):?>class="last"<? endif; ?>> 
										<td class="col1"><?=$aOne['fm_sum']?> FM</td>
										<td class="col2"><span class="sms-code-r"><span class="sms-code-l"><span class="sms-code-m"><strong><?=$phone?></strong></span></span></span></td>
										<td class="cols"><?=$aOne['rur_sum']?>&nbsp;RUR</td>
										<td class="cols"><?= "{$aOne['byr_sum']}&nbsp;BYR" ?></td>
										<td class="cols"><?=$aOne['uah_sum']?>&nbsp;UAH</td>
										<td class="col7"><?=sms_services::$tariffs[$phone]['descr']?></td>
									</tr>
									<? endforeach; ?>
								</tbody> 
							</table> 
						</div>
						<b class="b2"></b>
						<b class="b1"></b>
					</div>
					<p>Стоимость доступа к услугам контент-провайдера устанавливается вашим оператором. Подробную информацию можно узнать:</p>
					<p>&ndash; в разделе &laquo;Услуги по коротким номерам&raquo; на сайте <a href="http://www.mts.ru" target="_blank">www.mts.ru</a></p>
					<p>&ndash; в контактном центре по телефону 8 800 333 0890 (0890 для абонентов мтс)</p>
				</div>
			</div>
			
			
			
		</div>
	</div>
</div>
{{include "footer.tpl"}}