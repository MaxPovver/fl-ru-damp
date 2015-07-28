<?
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/employer.php");
	$user = new employer();

    // Изменяем авто продление PRO, если нужно
    if(strtolower($_GET['pro_auto_prolong'])=='on') {
        $user->setPROAutoProlong('on',get_uid());
    }
    if(strtolower($_GET['pro_auto_prolong'])=='off') {
        $user->setPROAutoProlong('off',get_uid());
    }

	$user->GetUser($_SESSION['login']);
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
	$account = new account();
	$ok = $account->GetInfo($_SESSION['uid'], true);

    $u_is_pro_auto_prolong = $user->GetField($uid, $e, 'is_pro_auto_prolong', false); // Включено ли у юзера автоматическое продление PRO
?>
<script type="text/javascript">
tr = true;

	function chang(t){
		var amm = <?=$account->sum?>;
		var s = t;
		var re = /^[0-9]*$/i;
		if ( s.match(re) == null) { tr = false; return (false); document.getElementById('buy').disabled = true;}
		v = t * 10;

		if (v > amm) { document.getElementById('error').className = 'error vis'; document.getElementById('buy').disabled = true;
			}else{
				document.getElementById('buy').disabled = false;document.getElementById('error').className = 'error';
			}
		document.getElementById('it').innerHTML='Всего к оплате: <span>' + v + '</span> FM';
	
		return (true);
	}

</script>



					<h2>Услуги</h2>
					<div class="promo-page c">
						<h3 class="emp-payed-title">Станьте привлекательнее для<br />серьезных исполнителей</h3>
						<div class="emp-payed-left-col">
							<div class="emp-promo">
								<b class="b1"></b>
								<b class="b2"></b>
								<div class="emp-promo-in c">
									<img src="../images/emp-payed-promo1.png" alt="" class="ep-left" />
									<div class="ep-txt">
										<strong>Бесплатное выделение вашего проекта</strong> в общей ленте. Такую вакансию обязательно заметят в общей ленте.
									</div>
								</div>
								<b class="b2"></b>
								<b class="b1"></b>
							</div>
							<div class="emp-promo">
								<b class="b1"></b>
								<b class="b2"></b>
								<div class="emp-promo-in c">
									<img src="../images/emp-payed-promo2.png" alt="" class="ep-right" />
									<div class="ep-txt2">
										Получите возможность <strong>указать больше информации о себе</strong> в каталоге &ndash; разместив логотип и описание компании.
									</div>
								</div>
								<b class="b2"></b>
								<b class="b1"></b>
							</div>
						</div>
						<div class="emp-payed-right-col">
						
							<p>Работодатель c аккаунтом PRO — активный участник проекта, размещающий большой объем заказов на сайте, внимательно относящийся к собственному имиджу и качеству выполнения своих заданий.</p>

							<p>Серьезный подход работодателя к своему аккаунту позволяет <nobr>фрилансеру</nobr> быть более уверенным в справедливой оплате своего труда, а также в отношении к выполненной работе.</p>

							<p>Серьезный заказчик требует серьезного подхода и поэтому работодатель с аккаунтом PRO вправе расcчитывать на серьезный подход к делу со стороны исполнителя.</p>

							<p>В первую очередь Free-lance.ru рассматривает все конфликтные ситуации с участием работодателей с аккаунтом PRO и разбирает их особенно тщательно.</p>

							<p>При возникновении конфликтной ситуации, пожалуйста, обратитесь к нам по адресу <a href="mailto:info@free-lance.ru">info@free-lance.ru</a>, мы обязательно поможем вам.</p>

							<p><span>*Все вышесказанное не означает, что среди тех, кто не пользуется платными сервисами, нет достойных работодателей. Удачной работы и приятного дня.</span></p>
							<div class="pay-block">
                                <form action="./buy.php" method="post" name="frmbuy" id="frmbuy">
                                <div>
								<div class="pay-inpt">Количество месяцев: <input type="text" size="3"  name="mnth" id="mnth" value="<?=floor($account->sum/10)?>" onKeyUp="return (chang(this.value));" /></div>
								<div class="pay-inpt" id="it">Всего к оплате: <span>10</span> FM</div>
                                <div id="error" class="error <? if ($error) { ?>vis<? } ?>"><?=view_error3("Недостаточно средств. В данный момент на счету ".$account->sum."&nbsp;FM<br /> <a href=\"/bill/\" class=\"blue\">Пополнить счет</a>")?><br /></div>
								<div>
                                    <a href="javascript:void(0);" class="btn btn-blue" name="buy" id="buy" onClick="$('frmbuy').submit();"><span class="btn-lc"><span class="btn-m"><span class="btn-txt">Оплатить</span></span></span></a>
                                    &nbsp;<a href="/bill/webmoney/" class="btn btn-green"><span class="btn-lc"><span class="btn-m"><span class="btn-txt">Получить бесплатно</span></span></span></a></div>

                				<input type="hidden" name="transaction_id" value="<?=$transaction_id?>" />
                				<input type="hidden" name="action" value="buy" />
                                </div>
                                </form>
							</div>

                            <? if($user->is_pro=='t') {?>
							<div class="pay-block">
                                <a name="pro_autoprolong"></a>
								<h4>Автопродление&nbsp;&nbsp;<span class="b-icon b-icon__pro b-icon__pro_e8"></span></h4>
								<p>Теперь вам не нужно следить за сроком действия<br />аккаунта PRO.<br />Если у вас есть деньги на счету, то включив эту опцию,<br />ежемесячно с вашего счета будет списываться 10FM.</p>
								<div>
                                    <? if($u_is_pro_auto_prolong=='t') { ?>
                                        <a href="javascript:void(0);" class="btn btn-pink" onClick="location='/payed/?pro_auto_prolong=off#pro_autoprolong';"><span class="btn-lc"><span class="btn-m"><span class="btn-txt">Отключить</span></span></span></a>
                                    <? } else { ?>
                                        <a href="javascript:void(0);" class="btn btn-green" onClick="location='/payed/?pro_auto_prolong=on#pro_autoprolong';"><span class="btn-lc"><span class="btn-m"><span class="btn-txt">Включить</span></span></span></a>
                                    <? } ?>
                                </div>
							</div>
                            <? } ?>
       
						</div>
					</div>




<script type="text/javascript">
<!--
chang(document.getElementById('mnth').value);
//-->
</script>


