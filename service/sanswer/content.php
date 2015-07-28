<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
// идет в релиз из-за связей, поэтому пока 404
header_location_exit('/404.php');
?>
<script type="text/javascript">
window.addEvent('domready', function() {

    $('promo_answer_slider2').setStyle('display', 'none');
    $('promo_answer_slider1').removeClass('b-promo__answer_slider1');
    $('promo_answer_slider2').setStyle('opacity', '0');

	var tm;
    $$('#answer-select .b-check__input').addEvent('click',function(){
	   if(this.get('checked')==true){
    		$('promo_answer_slider1').addClass('b-promo__answer_slider1');
            $('promo_answer_slider2').setStyle('display', 'block');
            tm = setTimeout( function() { $('promo_answer_slider2').tween('opacity', 1); }, 1500 );
    	} else {
		  clearTimeout(tm);
          $('promo_answer_slider2').setStyle('display', 'none');
		  $('promo_answer_slider1').removeClass('b-promo__answer_slider1');
		  $('promo_answer_slider2').setStyle('opacity', '0');
	   }
	});
})
</script>

<div class="b-layout__right b-layout__right_relative b-layout__right_width_72ps b-layout__right_float_right">
    <div class="b-menu b-menu_crumbs">
        <ul class="b-menu__list">
            <li class="b-menu__item"><a href="/service/" class="b-menu__link">Все услуги сайта</a>&#160;&rarr;&#160;</li>
        </ul>
    </div>
    <h1 class="b-page__title b-page__title_padbot_17">Выделение ответа в проекте</h1>
    <a name="top"></a>
    <p class="b-layout__txt b-layout__txt_padbot_40">Увеличьте свои шансы получить проект &mdash; сделайте свой ответ заметнее, чем <br />у всех остальных.</p>

    <div class="b-layout">
            <table class="b-layout__table">
                <tr class="b-layuot__tr">
                    <td class="b-layout__left b-layout__left_width_500">
                        <h2 class="b-promo__h2 b-promo__h2_padbot_40">Стоимость услуги &mdash; <span class="b-promo__txt b-promo__txt_fontsize_22 b-promo__txt_color_fd6c30"><?= number_format(projects_offers_answers::COLOR_FM_COST, 0, ",", "")?> рублей</span>.</h2>

                        <h2 class="b-promo__h2 b-promo__h2_padbot_14">Почему это выгодно</h2>
                        <ul class="b-promo__list b-promo__list_padbot_37">
                            <li class="b-promo__item b-promo__item_lineheight_1 b-promo__item_margbot_10"><span class="b-promo__item-number b-promo__item-plus"></span>Ваше объявление заметнее, чем у всех остальных.</li>
                            <li class="b-promo__item b-promo__item_lineheight_1 b-promo__item_margbot_10"><span class="b-promo__item-number b-promo__item-plus"></span>Вы возвышаетесь в глазах заказчика.</li>
                        </ul>
                        <h2 class="b-promo__h2 b-promo__h2_padbot_14">Как это работает</h2>
                        <ul class="b-promo__list b-promo__list_padbot_37">
                            <li class="b-promo__item b-promo__item_lineheight_1 b-promo__item_margbot_10"><span class="b-promo__item-number b-promo__item-number_1"></span>Найдите интересующий вас <a class="b-promo__link" href="/" target="_blank">проект</a>.</li>
                            <li class="b-promo__item b-promo__item_lineheight_1 b-promo__item_margbot_10"><span class="b-promo__item-number b-promo__item-number_2"></span>Добавьте свое предложение.</li>
                            <li class="b-promo__item b-promo__item_lineheight_1 b-promo__item_margbot_10"><span class="b-promo__item-number b-promo__item-number_3"></span>Поставьте галочку &laquo;Выделить предложение цветом&raquo;
                                <img class="b-promo__answer b-promo__answer_margtop_10" src="/css/block/b-promo/b-promo__answer.png" alt="" width="391" height="38" /></li>
                        </ul>
                    </td>
                    <td class="b-layout__right">
                        <h2 class="b-promo__h2 b-promo__h2_padbot_14">Попробуйте</h2>
                        <div id="answer-select" class="b-check b-check_padbot_10">
                            <input id="answer" class="b-check__input" name="" type="checkbox" value="" />
                            <label class="b-check__label b-check__label_fontsize_13" for="answer">выделить предложение цветом</label>
                        </div>
                        <div id="promo_answer_slider1" class="b-promo__answer_slider"><div id="promo_answer_slider2" class="b-promo__answer_slider b-promo__answer_slider2" style="display:none; opacity: 0;"></div></div>
					</td>
                </tr>
            </table>												
    </div>
	<span class="b-promo__profi"></span>													
    <? include("../tpl.help.php"); ?>																												
</div>