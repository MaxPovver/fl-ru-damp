<?php
$op_codes = $answers->GetOpCodes();
?>

<script type="text/javascript">

var ac_sum = <?= round($_SESSION['ac_sum'],2);?>;
var op = [];
<? foreach ($op_codes as $ammount=>$sum) { ?>
op[<?=$ammount?>] = <?=round($sum,2)?>;
<? } //foreach?>
</script>

<div class="b-layout__right b-layout__right_relative b-layout__right_width_72ps b-layout__right_float_right">
	<div class="b-menu b-menu_crumbs">
			<ul class="b-menu__list">
					<li class="b-menu__item"><a class="b-menu__link" href="/service/">Все услуги сайта</a>&#160;&rarr;&#160;</li>
			</ul>
	</div>
	<h1 class="b-page__title">Платные ответы</h1>
	<p class="b-promo__p b-promo__p_fontsize_13 b-promo__p_padbot_20">Пользователи с начальным аккаунтом могут отвечать всего на 5 проектов в месяц. Чтобы отвечать на большее количество проектов, вы можете воспользоваться сервисом «Платные ответы» и приобрести любое количество дополнительных ответов на проекты.</p>
	<h2 class="b-promo__h2 b-promo__h2_padbot_15">Как пользоваться сервисом «Платные ответы»?</h2>
	<ul class="b-promo__list">
			<li class="b-promo__item b-promo__item_fontsize_15 b-promo__item_margbot_20 b-promo__item_pad_null"><span class="b-promo__item-number b-promo__item-number_1"></span>Вы можете приобрести 1 платный ответ или же пакеты ответов со скидкой (5 или 10 ответов).
	<br/>
    			<table class="b-layout__table b-layout__table_width_full">
                	<tr class="b-layout__tr">
                    	<td class="b-layout__left">
                <div class="b-pay-answer <?=(is_pro()?"b-pay-answer_hide":"")?> b-pay-answer_inline-block b-pay-answer_padtop_33 b-pay-answer_padleft_20" <?=(is_pro()?'style="overflow:hidden;width:0px;"':"")?>>	
		<form id="buy_form" action="/service/offers/?action=buy" method="post" style="width:310px;">
			<fieldset class="b-radio b-radio_layout_vertical">
				<div class="b-radio__item b-radio__item_padbot_10">
					<input type="radio" checked="checked" value="1" name="ammount" class="b-radio__input" id="b-radio__answer1" onclick="check_price('30 руб.');">
					<label for="b-radio__answer1" class="b-radio__label b-radio__label_fontsize_15 b-radio__label_lineheight_1">
						<span class="b-pay-answer__txt b-pay-answer__txt_fontsize_15 b-pay-answer__txt_bold b-pay-answer__txt_inline-block b-pay-answer__txt_width_90">
							<span class="b-pay-answer__txt b-pay-answer__txt_fontsize_15 b-pay-answer__txt_inline-block b-pay-answer__txt_width_20 b-pay-answer__txt_align-right">1</span>&#160;ответ</span>
						<span class="b-pay-answer__fm b-pay-answer__fm_fontsize_15">30 руб.</span>
					</label>
				</div>
				<div class="b-radio__item b-radio__item_padbot_10">
					<input type="radio" value="5" name="ammount" class="b-radio__input" id="b-radio__answer2" onclick="check_price('120 руб.');">
					<label for="b-radio__answer2" class="b-radio__label b-radio__label_fontsize_15 b-radio__label_lineheight_1">
						<span class="b-pay-answer__txt b-pay-answer__txt_fontsize_15 b-pay-answer__txt_bold b-pay-answer__txt_inline-block b-pay-answer__txt_width_90">
							<span class="b-pay-answer__txt b-pay-answer__txt_fontsize_15 b-pay-answer__txt_inline-block b-pay-answer__txt_width_20 b-pay-answer__txt_align-right">5</span>&#160;ответов</span>
						<span class="b-pay-answer__fm b-pay-answer__fm_fontsize_15">120 руб.</span>
						<span class="b-pay-answer__economy">Экономия 20%</span>
					</label>
				</div>
				<div class="b-radio__item b-radio__item_padbot_20">
					<input type="radio" value="10" name="ammount" class="b-radio__input" id="b-radio__answer3" onclick="check_price('210 руб.');">
					<label for="b-radio__answer3" class="b-radio__label b-radio__label_fontsize_15 b-radio__label_lineheight_1">
						<span class="b-pay-answer__txt b-pay-answer__txt_fontsize_15 b-pay-answer__txt_bold b-pay-answer__txt_inline-block b-pay-answer__txt_width_90">
							<span class="b-pay-answer__txt b-pay-answer__txt_fontsize_15 b-pay-answer__txt_inline-block b-pay-answer__txt_width_20 b-pay-answer__txt_align-right">10</span>&#160;ответов</span>
						<span class="b-pay-answer__fm b-pay-answer__fm_fontsize_15">210 руб.</span>
						<span class="b-pay-answer__economy">Экономия 30%</span>
					</label>
				</div>
				<div class="b-buttons">
					<a href="javascript:void(0);" class="b-button b-button_rectangle_color_green" id="payed_submit_link">
						<span class="b-button__b1">
							<span class="b-button__b2">
								<span class="b-button__txt" id="payed_submit">Купить за <span class="b-button__colored b-button__colored_fd6c30" id="offers_ammount">30 руб.</span></span>
							</span>
						</span>
					</a><span class="b-buttons__txt b-buttons__txt_padleft_10">или <a href="#" class="b-buttons__link b-buttons__link_toggler b-buttons__link_dot_c10601 b-buttons__link_valign_baseline">закрыть</a></span>
				</div>
				<div class="b-pay-answer__txt b-pay-answer__txt_fontsize_11 b-pay-answer__txt_c10600 b-pay-answer__txt_padtop_15" id="error_answers" style="display:none"><span class="b-pay-answer__error"></span></div>
			</fieldset>
		</form>		
		</div>
        				</td>
                        <td class="b-layout__right">
				<div class="b-promo__note-wrap <?=(!is_pro()?" b-promo__note-wrap_inline-block":"")?> b-promo__note-wrap_padtop_15 " >	
						<div class="b-promo__note b-promo__note_relative">
								<div class="b-promo__note-inner" id="info_block" style="height:134px">
								        <?php if(!is_pro()) {?>
										<h3 class="b-promo__h3 b-promo__h3_padbot_15">Получите неограниченное количество ответов <span alt="Платный аккаунт" title="Платный аккаунт" class="b-icon b-icon__pro b-icon__pro_f"></span></h3>
										<p class="b-promo__p b-promo__p_fontsize_13">Надоело покупать «Платные ответы»? Владельцы «Аккаунта PRO» не имеют ограничений при ответах на опубликованные на сайте проекты. <a class="b-promo__link" href="/payed/">Приобретите «Аккаунт PRO»</a>, и вам больше не надо будет тратить на приобретение платных ответов.</p>
										<?php } else {?>
										<h3 class="b-promo__h3 b-promo__h3_padbot_15">У вас аккаунт <span alt="Платный аккаунт" title="Платный аккаунт" class="b-icon b-icon__pro b-icon__pro_f"></span>&#160;и покупать ответы вам больше не нужно — для вас они бесплатны.</h3>
									    <p class="b-promo__p b-promo__p_fontsize_13">Но если вы все же захотите приобрести платные ответы, их можно будет использовать после окончания срока действия профессионального аккаунта.</p>
										<?php } //else?>
										<p class="b-promo__p b-promo__p_padtop_10 b-promo__p_fontsize_13" <?=(!is_pro()?'style="display:none"':"")?>><a class="b-promo__link b-promo__link_bordbot_dot_0f71c8 b-promo__link_toggler" href="#">Купить ответы</a></p>
								</div>
						</div>
				</div>
                		</td>
                    </tr>
               </table>
			</li>
			<li class="b-promo__item b-promo__item_fontsize_15 b-promo__item_margbot_30 b-promo__item_pad_null"><span class="b-promo__item-number b-promo__item-number_2"></span>Откликайтесь на интересующие вас <a class="b-promo__link"  href="/">проекты</a> при помощи приобретенных ответов.</li>
	</ul>
	<h2 class="b-promo__h2 b-promo__h2_padbot_15">Остались вопросы?</h2>
	<p class="b-promo__p b-promo__p_padbot_10 b-promo__p_fontsize_13"><a class="b-promo__link" href="https://feedback.free-lance.ru/article/details/id/102">Подробнее об услуге в разделе «Помощь»</a>&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;Также вы можете обратиться в <a class="b-promo__link" href="https://feedback.free-lance.ru/ " target="_blank">службу поддержки Free-lance.ru</a></p>
	<span class="b-promo__select-me"></span>
</div>
