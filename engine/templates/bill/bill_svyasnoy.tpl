<?php if ($$uri[0]!='print'): ?>
{{include "header.tpl"}}
<?php endif; ?>

<div class="body c">
	<div class="main c">
        <?php if ($$uri[0]!='print'): ?>
					<h1 class="b-page__title">Мой счет</h1>
		<?php endif; ?>
		<div class="rcol-big">
			<?php if ($$uri[0]!='print'): ?>
            {{include "bill/bill_menu.tpl"}}
            <?php endif; ?>
            
		      <div class="tabs-in bill-t-in">
                <?php if ($$uri[0]!='print'): ?>
				<a href="/bill/svyasnoy/print" target="_blank" id="a_print" class="btnr-mb bl-print"><span class="btn-lc"><span class="btn-m"><span class="btn-txt">Распечатать</span></span></span></a>
				<?php endif; ?>
                
				<h3>Пополнение счета в &laquo;Связном&raquo;</h3>
				<?php if ($$uri[0]!='print'): ?>
                                <div class="bill-info-logo">
                                    <img src="/images/cvyaznoy.png" alt="" /><br />
                                    <a href="http://www.svyaznoy.ru/" target="_blank">www.svyaznoy.ru</a>
                                </div>
				<?php endif; ?>
                                <div class="bill-info">
					<h4>Описание системы</h4>
					<p>Платежи в «Связном» – возможность пополнить личный счет как при помощи терминалов, так и через кассу «Связного». Данный способ пополнения доступен только на территории Российской Федерации.</p>
					<p>Процессинговый центр и телефонная служба поддержки «Связного» работают круглосуточно. Все платежи зачисляются в режиме онлайн.</p>

					<h4>Пополнение счета через терминал</h4>
					<div class="ol">
						<b class="b1"></b>
						<b class="b2"></b>
						<div class="ol-in">
							<ol>
							   <li><span>Найдите в своем городе любой терминал «Связной».</span></li>
							   <li><span>Зайдите в раздел «Оплата услуг», выберите категорию услуг «Интернет».</span></li>
							   <li><span>Выберите Free-lance.ru среди предложенных компаний (нажмите на значок Free-lance.ru на дисплее автомата). После этого на экране автомата появится виртуальная клавиатура.</span></li>
							   <li><span>Введите ваш логин (который вы используете для входа на сайт) с помощью клавиатуры.</span></li>
							   <li><span>Внесите необходимое количество денег в терминал и нажмите кнопку «Оплатить».</span></li>
							   <li><span>Получите и сохраните квитанцию об оплате до зачисления денежных средств.</span></li>
							   <li><span>Деньги будут автоматически перечислены на личный счет на Free-lance.ru.</span></li>
							</ol>
						</div>
						<b class="b2"></b>
						<b class="b1"></b>
					</div>

					<h4>Пополнение счета через кассу магазина</h4>
					<div class="ol">
						<b class="b1"></b>
						<b class="b2"></b>
						<div class="ol-in">
							<ol>
							   <li><span>Зайдите в своем городе в любой магазин «Связной».</span></li>
							   <li><span>Сообщите кассиру о необходимости пополнения личного счета на Free-lance.ru.</span></li>
							   <li><span>Назовите ваш логин (который вы используете для входа на сайт) и сумму к оплате.</span></li>
							   <li><span>Подтвердите правильность номера счета подписью в пречеке.</span></li>
							   <li><span>Внесите необходимое количество денежных средств.</span></li>
							   <li><span>Сохраните чек до поступления денег на личный счет на Free-lance.ru.</span></li>
							</ol>
						</div>
						<b class="b2"></b>
						<b class="b1"></b>
					</div>
                    <p>Обращаем ваше внимание на то, что оплата с помощью терминалов или кассы «Связного» доступна только пользователям, находящимся на территории Российской Федерации.</p>
					<p>Обратите внимание: при пополнении счёта в «Связном» Free-lance.ru не берет процент за перевод. При пополнении счета в размере свыше 49 рублей «Связной» также не взимает комиссию.</p>
					



					<div class="b-fon b-fon_bg_fcc b-fon_width_full">
							<b class="b-fon__b1"></b>
							<b class="b-fon__b2"></b>
							<div class="b-fon__body b-fon__body_pad_5"><strong>Внимание:</strong> Вы не можете зарезервировать деньги под «Безопасную сделку» с помощью платежей в «Связном». </div>
							<b class="b-fon__b2"></b>
							<b class="b-fon__b1"></b>
					</div>

					<h4>Что делать, если деньги не были перечислены на счет?</h4>
					<p>В случае, если пополнение счета не произошло, напишите нам в <a href="/about/feedback/">службу поддержки</a> и пришлите копию чека об оплате (скан). Мы обязательно решим проблему.</p>
				</div>

			</div>
		</div>
	</div>
</div>

<?php if ($$uri[0]!='print'): ?>
{{include "footer.tpl"}}
<?php endif; ?>

<?php if ($$uri[0]=='print'): ?>
<script type="text/javascript">window.print();</script> 
<?php endif; ?>
