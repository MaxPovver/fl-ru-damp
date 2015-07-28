<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/employer.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_offers_answers.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/op_codes.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/rating.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
global $session;
session_start();

$uid = get_uid();

$tr_id = intval($_REQUEST['transaction_id']);

$user = new employer();

$user->GetUser($_SESSION['login']);
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
$account = new account();
$ok = $account->GetInfo($uid, true);
$transaction_id = $account->start_transaction($uid, $tr_id);


?>

<script type="text/javascript">
    var account_sum = <?= round($account->sum,2) ?>;

    function changeMonthPro(x) {
        mpro = $('month_pro').get('html')-0;
        mpro = mpro + x;
        if(mpro<1) {
            mpro = 1;
        }
        $('month_pro').set('html',mpro);
        $('month_sum').set('html',mpro*780);
        $('mnth').set('value',mpro);

        noSumAmmount(mpro*780, 'block_pro_pay', 'pro_pay_sum');
    }
</script>

<h1 class="b-page__title">Данная функция доступна только пользователям с аккаунтом <span title="PRO" class="b-icon b-icon__spro b-icon__spro_e"></span></h1>
<div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padbot_20">Владельцы аккаунта <a class="b-layout__link" href="/payed-emp/"><span class="b-icon b-icon__pro b-icon__pro_e b-icon_top_4" alt="Платный аккаунт" title="Платный аккаунт"></span></a> – это наиболее активная и серьёзная часть аудитории Free-lance.ru. Набор функций профессионального аккаунта предоставляет ряд серьезных преимуществ, которые позволяют стать привлекательнее для серьезных фрилансеров и сделать поиск исполнителей по проектам эффективнее.</div>
            <div class="payed-block payed-block-proonly">
                <b class="b1"></b>
                <b class="b2"><b class="b4"></b></b>
                <b class="b3"></b>
                <div class="payed-block-in">
                    <form action="/payed/buy.php" method="post" name="frmbuy" id="frmbuy" onsubmit="return checkBalance('block_pro_pay');">
            		<input type="hidden" name="mnth" id="mnth" value="1">
            		<input type="hidden" name="transaction_id" value="<?=$transaction_id?>">
            		<input type="hidden" name="action" value="buy">
                    <h3 class="b-layout__h3">Покупка <a class="b-layout__link" href="/payed-emp/"><span class="b-icon b-icon__pro b-icon__pro_e b-icon_top_3" alt="Платный аккаунт" title="Платный аккаунт"></span></a> аккаунта:</h3>
                    <? if($_SESSION['pro_last']): ?>
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
                    } else {
                        $last_ending = floor((strtotime($last_time)-time())/(60));
                        $last_string1 = 'минута';
                        $last_string2 = 'минуты';
                        $last_string3 = 'минут';
                    }
                    ?>
                    <div class="b-layout__txt b-layout__txt_padbot_10">Ваш <a class="b-layout__link" href="/payed-emp/"><span class="b-icon b-icon__pro b-icon__pro_e b-icon_top_3" alt="Платный аккаунт" title="Платный аккаунт"></span></a> аккаунт истекает через <?=$last_ending?> <?=ending($last_ending, $last_string1, $last_string2, $last_string3)?></div>
                    <? endif; ?>
                    <table class="buy-pro-tbl">
    					<tbody>
                            <tr class="first">
    							<td>
									<div class="spinner">
										<span class="spin-btns">
											<input type="image" src="/images/arrow-top.png" name="Увеличить" title="Увеличить" alt="Увеличить" value="&amp;uarr" onclick="changeMonthPro(1); return false;">
											<input type="image" src="/images/arrow-bottom.png" name="Уменьшить" title="Уменьшить" alt="Уменьшить" value="&amp;darr" onclick="changeMonthPro(-1); return false;">
										</span>
										<span class="spin-val" id="month_pro">1</span> мес.
									</div>
								</td>
								<td class="sign">&nbsp;&nbsp;&nbsp;=&nbsp;&nbsp;&nbsp;</td>
								<td><strong><span id="month_sum">780</span> руб.</strong></td>
							</tr>
						</tbody>
                    </table>

                    <div>
                        <a href="javascript:void(0);" class="btn btn-blue" onClick="checkBalance('block_pro_pay', 'frmbuy');"><span class="btn-lc"><span class="btn-m"><span class="btn-txt">Купить</span></span></span></a>
						<div class="lnk-pay" style="display:none" id="block_pro_pay"><a href="/bill/">Пополнить счет на <span id="pro_pay_sum">30</span> руб.</a></div>
					</div>
					</form>
                </div>
                <b class="b3"></b>
                <b class="b2"><b class="b4"></b></b>
                <b class="b1"></b>
            </div>

<h3 class="b-layout__h3">Возможности профессионального аккаунта:</h3>
<div class="b-promo">
	<ul class="b-promo__list">
			<li class="b-promo__item b-promo__item_fontsize_15"><span class="b-promo__item-number b-promo__item-plus"></span>Возможность разместить логотип компании в профиле</li>
			<li class="b-promo__item b-promo__item_fontsize_15"><span class="b-promo__item-number b-promo__item-plus"></span>Возможность бесплатно выделить проект цветом в ленте</li>
			<li class="b-promo__item b-promo__item_fontsize_15"><span class="b-promo__item-number b-promo__item-plus"></span>Возможность разместить подробное описание компании в профиле</li>
			<li class="b-promo__item b-promo__item_fontsize_15"><span class="b-promo__item-number b-promo__item-plus"></span>Консультации специалиста по бесплатному федеральному номеру</li>
			<li class="b-promo__item b-promo__item_fontsize_15"><span class="b-promo__item-number b-promo__item-plus"></span>Приоритетное рассмотрение вопросов в службе поддержки</li>
</ul>
</div>            
<div class="b-layout__txt"><a class="b-layout__link" href="/payed-emp/">и другие полезные преимущества</a></p>

<script type="text/javascript">
changeMonthPro(0);
</script>
