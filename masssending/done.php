<h2>Услуги</h2>
<div class="masssending-block c">
	<div class="masssending-content">
		<h3>Рассылка по каталогу</h3>
		<p><strong>Спасибо, что воспользовались данной услугой!</strong></p>
		<p>Рассылка будет произведена после проверки модератором, по окончании проверки вам будет выслано уведомление.</p>
		<div class="form form-masssending-complete">
			<b class="b1"></b>
			<b class="b2"></b>
			<div class="form-in">
				<table>
				<tr>
					<th>Количество получателей:</th>
					<td><strong><?=format(intval($_GET['count']))?></strong></td>
				</tr>
				<tr>
					<th>Сумма за услугу рассылки:</th>
					<td><strong><?=format(floatval($_GET['cost']))?> руб.</strong></td>
				</tr>
				</table>
			</div>
			<b class="b2"></b>
			<b class="b1"></b>
		</div>
		<p>Если у вас возникли вопросы, обратитесь в <a href="https://feedback.fl.ru/">Службу поддержки</a>.</p>
	</div>
</div>
