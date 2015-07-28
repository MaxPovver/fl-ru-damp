<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/promo.common.php");
$xajax->printJavascript('/xajax/');
?>
<div class="b-menu b-menu_crumbs b-layout__right b-layout__right_float_right b-layout__right_width_72ps b-menu_margbot_30">
    <ul class="b-menu__list">
        <li class="b-menu__item"><a href="/service/" class="b-menu__link">Все услуги сайта</a>&nbsp;&rarr;&nbsp;</li>
    </ul>
</div>
<table class="b-layout__table b-layout__table_width_full b-layout__table_clear_both">
    <tr class="b-layout__tr">
        <td class="b-layout__one b-layout__one_center" colspan="3">
            <div class="b-layout__txt b-layout__txt_inline-block b-layout__txt_padleft_100 b-layout__txt_relative">
                <a class="b-layout__link" style="position:absolute; right:15px; top:55px;" href="/bezopasnaya-sdelka/?site=calc" target="_blank">Калькулятор</a><img class="b-layout__pic" src="/images/bs/1.png" alt="" width="218" height="105" />
            </div>
            <h1 class="b-page__title">Безопасная Сделка</h1>
        </td>
    </tr>
    <tr class="b-layout__tr">
        <td class="b-layout__one b-layout__one_center b-layout__one_width_33ps">
            <div class="b-layout__txt b-layout__txt_color_fd6c30 b-layout__txt_fontsize_46 b-layout__txt_bold"><?= $roleStr === 'frl' ? '0' : '9.9-13.9' ?>%</div>
            <div class="b-layout__txt"><?= $roleStr === 'frl' ? 'Комиссия для фрилансера' : 'Комиссия для работодателя' ?></div>
            <? if($roleStr !== 'frl') { ?>
            <div class="b-layout__txt b-layout__txt_fontsize_11" style="color:#000;">(включая все комиссии за открытие аккредитива)</div>
            <? }//if?>
        </td>
        <td class="b-layout__one b-layout__one_center">
            <div class="b-layout__txt b-layout__txt_color_fd6c30 b-layout__txt_fontsize_46 b-layout__txt_bold"><?= sbr_stages::MIN_COST_RUR; ?> руб.</div>
            <div class="b-layout__txt">Минимальный бюджет проекта</div>
        </td>
        <td class="b-layout__one b-layout__one_center b-layout__one_valign_bot b-layout__one_width_33ps">
            <img class="b-layout__pic" src="/images/bs/wm.png" alt="Webmoney" title="Webmoney"  />&#160;&#160;&#160;
            <img class="b-layout__pic" src="/images/bs/pskb.png" alt="Веб-кошелек ПСКБ" title="Веб-кошелек ПСКБ"  />&#160;&#160;&#160;
            <img class="b-layout__pic" src="/images/bs/pk.png" alt="Пластиковые карты" title="Пластиковые карты"  />
            <div class="b-layout__txt b-layout__txt_padtop_10"><?= $roleStr === 'frl' ? 'Большой выбор способов вывода денег' : 'Доступны разные способы оплаты' ?></div>
            <? if($roleStr !== 'frl') { ?><div class="b-layout__txt">&nbsp;</div><? }//if?>
        </td>
    </tr>
</table>
<div class="b-promo__bs-arrow"></div>
<? if ($roleStr === 'frl') { ?>
<table class="b-layout__table b-layout__table_width_full">
    <tr class="b-layout__tr">
        <td class="b-layout__one b-layout__one_padright_20"><div class="b-promo__bs b-promo__bs_1"></div></td>
        <td class="b-layout__one b-layout__one_padbot_30 b-layout__one_width_50ps">
            <h3 class="b-layout__h3">Вас обманывали работодатели?</h3>
            <div class="b-layout__txt">У вас есть неприятный опыт, когда вы выполнили проект, а деньги так и не получили?</div>
        </td>
        <td class="b-layout__one b-layout__one_padright_20 b-layout__one_padleft_50"><div class="b-promo__bs b-promo__bs_4"></div></td>
        <td class="b-layout__one b-layout__one_padbot_30 b-layout__one_width_50ps">
            <h3 class="b-layout__h3">Избавьтесь от рисков</h3>
            <div class="b-layout__txt">Если заказчик неожиданно исчезнет, вы все равно получите деньги за выполненный проект.</div>
        </td>
    </tr>
    <tr class="b-layout__tr">
        <td class="b-layout__one b-layout__one_padright_20"><div class="b-promo__bs b-promo__bs_2"></div></td>
        <td class="b-layout__one b-layout__one_padbot_30 b-layout__one_width_50ps">
            <h3 class="b-layout__h3">Надоели бесконечные поправки?</h3>
            <div class="b-layout__txt">Приходилось вносить правки, подчиняясь безосновательным требованиям заказчиков?</div>
        </td>
        <td class="b-layout__one b-layout__one_padright_20 b-layout__one_padleft_50"><div class="b-promo__bs b-promo__bs_5"></div></td>
        <td class="b-layout__one b-layout__one_padbot_30 b-layout__one_width_50ps">
            <h3 class="b-layout__h3">Работайте уверенно</h3>
            <div class="b-layout__txt">Все изменения в ТЗ вносятся только по обоюдному согласию. Если вас не будут устраивать новые условия, сотрудничество продолжится по утвержденному в начале ТЗ.</div>
        </td>
    </tr>
    <tr class="b-layout__tr">
        <td class="b-layout__one b-layout__one_padright_20"><div class="b-promo__bs b-promo__bs_3"></div></td>
        <td class="b-layout__one b-layout__one_width_50ps">
            <h3 class="b-layout__h3">Вам не доверяют клиенты?</h3>
            <div class="b-layout__txt">У вас маленький рейтинг и недостаточно заказов?</div>
        </td>
        <td class="b-layout__one b-layout__one_padright_20 b-layout__one_padleft_50"><div class="b-promo__bs b-promo__bs_6"></div></td>
        <td class="b-layout__one b-layout__one_width_50ps">
            <h3 class="b-layout__h3">Получите щит БС рядом с именем</h3>
            <div class="b-layout__txt">Всем пользователям, завершившим хотя бы одну Безопасную Сделку, выдается знак (щит), который размещается рядом с именем. Таким пользователям заказчики доверяют больше.</div>
        </td>
    </tr>
</table>
<? } else { ?>
<table class="b-layout__table b-layout__table_width_full">
    <tr class="b-layout__tr">
        <td class="b-layout__one b-layout__one_padright_20"><div class="b-promo__bs b-promo__bs_1"></div></td>
        <td class="b-layout__one b-layout__one_padbot_30 b-layout__one_width_50ps">
            <h3 class="b-layout__h3">Вас обманывали исполнители?</h3>
            <div class="b-layout__txt">У вас есть неприятный опыт, когда вы отдали деньги, а работу так и не получили?</div>
        </td>
        <td class="b-layout__one b-layout__one_padright_20 b-layout__one_padleft_50"><div class="b-promo__bs b-promo__bs_4"></div></td>
        <td class="b-layout__one b-layout__one_padbot_30 b-layout__one_width_50ps">
            <h3 class="b-layout__h3">Избавьтесь от рисков</h3>
            <div class="b-layout__txt">Если фрилансер не справится с проектом, то мы вернем вам деньги.</div>
        </td>
    </tr>
    <tr class="b-layout__tr">
        <td class="b-layout__one b-layout__one_padright_20"><div class="b-promo__bs b-promo__bs_2"></div></td>
        <td class="b-layout__one b-layout__one_padbot_30 b-layout__one_width_50ps">
            <h3 class="b-layout__h3">Строгий дедлайн по проекту?</h3>
            <div class="b-layout__txt">Беспокоитесь, что сроки сдачи проекта будут сорваны?</div>
        </td>
        <td class="b-layout__one b-layout__one_padright_20 b-layout__one_padleft_50"><div class="b-promo__bs b-promo__bs_5"></div></td>
        <td class="b-layout__one b-layout__one_padbot_30 b-layout__one_width_50ps">
            <h3 class="b-layout__h3">Контролируйте ситуацию</h3>
            <div class="b-layout__txt">Работа, выполненная качественно и в срок, &mdash; обязательное условие оплаты услуг фрилансера.</div>
        </td>
    </tr>
    <tr class="b-layout__tr">
        <td class="b-layout__one b-layout__one_padright_20"><div class="b-promo__bs b-promo__bs_3"></div></td>
        <td class="b-layout__one b-layout__one_width_50ps">
            <h3 class="b-layout__h3">Сомневаетесь в качестве?</h3>
            <div class="b-layout__txt">Не уверены, что работа будет соответствовать ТЗ и вашим<br />ожиданиям?</div>
        </td>
        <td class="b-layout__one b-layout__one_padright_20 b-layout__one_padleft_50"><div class="b-promo__bs b-promo__bs_6"></div></td>
        <td class="b-layout__one b-layout__one_width_50ps">
            <h3 class="b-layout__h3">Платите за результат</h3>
            <div class="b-layout__txt">Фрилансер получит гонорар только после того, как вы примете работу. Исключительные права на результат будут полностью принадлежать вам.</div>
        </td>
    </tr>
</table>
<? } ?>
<div class="b-promo__bs-arrow"></div>
<div id="promo-stats">
    <? include($_SERVER['DOCUMENT_ROOT'] . '/promo/sbr/new/tpl.stats.php') ?>
</div>
<div class="b-promo__bs-arrow"></div>
<table class="b-layout__table b-layout__table_width_400 b-layout__table_center">
    <tr class="b-layout__tr">
        <td class="b-layout__one b-layout__one_center">
            <div class="b-layout__txt b-layout__txt_fontsize_34">Как это работает</div>
            <div class="b-layout__txt b-layout__txt_padbot_30">Безопасная Сделка &mdash; это легко и надежно.</div>
            <img class="b-layout__pic" src="/images/bs/3.png" alt="" width="461" height="79" />
            <? if ($roleStr === 'frl') { ?>
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_left b-layout__txt_padtop_30"><div class="b-promo__num">1</div>&#160;&#160;Работодатель предлагает вам заключить сделку.</div>
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_left"><div class="b-promo__num">2</div>&#160;&#160;Вы просматриваете условия сотрудничества и даете свое согласие.</div>
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_left"><div class="b-promo__num">3</div>&#160;&#160;Заказчик одобряет выполненный вами проект.</div>
            <div class="b-layout__txt b-layout__txt_left"><div class="b-promo__num">4</div>&#160;&#160;Мы переводим вам гонорар.</div>
            <? } else { ?>
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_left b-layout__txt_padtop_30 b-layout__txt_padleft_100"><div class="b-promo__num">1</div>&#160;&#160;Опубликуйте проект или конкурс и выберите исполнителя.</div>
            <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_left b-layout__txt_padleft_100"><div class="b-promo__num">2</div>&#160;&#160;Зарезервируйте деньги на специальном счете в банке.</div>
            <div class="b-layout__txt b-layout__txt_nowrap b-layout__txt_left b-layout__txt_padleft_100"><div class="b-promo__num">3</div>&#160;&#160;Проверьте выполненную работу. С вашего согласия мы переведем гонорар фрилансеру.</div>
                <? /*if (get_uid(0)) { ?>
                <div class="b-buttons b-buttons_padtop_40 b-buttons_padbot_10 b-buttons_center">
                    <a class="b-button b-button_big_rectangle_color_green" href="/bezopasnaya-sdelka/?site=new">
                        <span class="b-button__b1">
                            <span class="b-button__b2">
                                <span class="b-button__txt">Создать сделку</span>
                            </span>
                        </span>
                    </a>
                </div>
                <? } */?>
            <? } ?>
        </td>
    </tr>
</table>
<div class="b-promo__bs-arrow"></div>
<? if ($feedbacksFromEmp || $feedbacksFromFrl) { ?>
<div id="promo-feedbacks">
    <? include($_SERVER['DOCUMENT_ROOT'] . '/promo/sbr/new/tpl.feedbacks.php') ?>
</div>
<script>
    (function () {
        
        var
            $newFeedbacksBtn, $feedbacks,
            needUpdate; // если true - значит надо обновить отзывы
        
        window.addEvent('domready', function() {
            
            $newFeedbacksBtn = $('new-feedbacks');
            $feedbacks = $('promo-feedbacks');
            
            window.PromoSBR = {};
            PromoSBR.newFeedbacksLoaded = newFeedbacksLoaded;

            $newFeedbacksBtn.addEvent('click', newFeedbacks);

        });
        
        function newFeedbacks () {
            // анимация исчезновения
            $feedbacks.set('morph', {duration: 500});
            $feedbacks.get('morph').addEvent('complete', hidingComplete);
            $feedbacks.morph({'opacity': 0});
            
            needUpdate = true;
        }
        
        function hidingComplete () {
            if (!needUpdate) {
                return;
            }
            xajax_getPromoFeedbacks();
            needUpdate = false;
        }
        
        function newFeedbacksLoaded () {
            // анимация появления
            $feedbacks.morph({'opacity': 1});
        }
        
    })()
</script>
<div class="b-buttons b-buttons_center">
    <a href="javascript:void(0)" id="new-feedbacks" class="b-button b-button_flat b-button_flat_green">Еще отзывы</a>          
</div>
<? } ?>
<div class="b-promo__bs-arrow"></div>
<table class="b-layout__table b-layout__table_width_400 b-layout__table_center">
    <tr class="b-layout__tr">
        <td class="b-layout__one b-layout__one_center">
            <div class="b-promo__bs b-promo__bs_7"></div>
            <div class="b-layout__txt b-layout__txt_fontsize_34 b-layout__txt_padbot_20">Остались вопросы?</div>
            <? if ($roleStr === 'frl') { ?>
            <div class="b-layout__txt b-layout__txt_padbot_10"><a class="b-layout__link" href="http://feedback.fl.ru/topic/397488-kak-frilanseru-soglasitsya-na-bezopasnuyu-sdelku/">Как согласиться на сделку</a></div>
            <div class="b-layout__txt b-layout__txt_padbot_10"><a class="b-layout__link" href="http://feedback.fl.ru/topic/397433-kalkulyator-bezopasnoj-sdelki/">Как рассчитать свой гонорар</a></div>
            <div class="b-layout__txt b-layout__txt_padbot_10"><a class="b-layout__link" href="http://feedback.fl.ru/topic/397422-poryadok-dejstvij-pri-rabote-cherez-bezopasnuyu-sdelku/">Каков порядок работы через Безопасную Сделку</a></div>
            <div class="b-layout__txt b-layout__txt_padbot_10"><a class="b-layout__link" href="http://feedback.fl.ru/topic/397435-dokumentooborot-bezopasnoj-sdelki/">Как оформить договор</a></div>
            <div class="b-layout__txt b-layout__txt_padbot_10"><a class="b-layout__link" href="http://feedback.fl.ru/topic/397431-chto-takoe-arbitrazh-servisa-bezopasnaya-sdelka-i-kak-k-nemu-obratitsya/">Как работает Арбитраж</a></div>
            <div class="b-layout__txt"><a class="b-layout__link" href="http://feedback.fl.ru/topic/397425-zapolnenie-stranitsyi-finansyi-dlya-rabotyi-cherez-bezopasnuyu-sdelku/">Какие данные нужны для работы через Безопасную Сделку</a></div>
            <? } else { ?>
            <div class="b-layout__txt b-layout__txt_padbot_10"><a class="b-layout__link" href="http://feedback.fl.ru/topic/397434-kak-nachat-bezopasnuyu-sdelku/">Как начать Безопасную Сделку</a></div>
            <div class="b-layout__txt b-layout__txt_padbot_10"><a class="b-layout__link" href="http://feedback.fl.ru/topic/397433-kalkulyator-bezopasnoj-sdelki/">Как рассчитать бюджет сделки</a></div>
            <div class="b-layout__txt b-layout__txt_padbot_10"><a class="b-layout__link" href="http://feedback.fl.ru/topic/397428-kak-zarezervirovat-dengi-dlya-bezopasnoj-sdelki/">Как зарезервировать деньги для сделки</a></div>
            <div class="b-layout__txt b-layout__txt_padbot_10"><a class="b-layout__link" href="http://feedback.fl.ru/topic/397432-limityi-na-rezervirovanie-deneg-bankovskoj-kartoj-po-bezopasnoj-sdelke/">Лимиты на сделки по банковским картам</a></div>
            <div class="b-layout__txt b-layout__txt_padbot_10"><a class="b-layout__link" href="http://feedback.fl.ru/topic/397435-dokumentooborot-bezopasnoj-sdelki/">Как оформить договор</a></div>
            <div class="b-layout__txt b-layout__txt_padbot_10"><a class="b-layout__link" href="http://feedback.fl.ru/topic/397431-chto-takoe-arbitrazh-servisa-bezopasnaya-sdelka-i-kak-k-nemu-obratitsya/">Как работает Арбитраж</a></div>
            <div class="b-layout__txt"><a class="b-layout__link" href="http://feedback.fl.ru/topic/397440-upravlenie-bezopasnoj-sdelkoj-dlya-rabotodatelya/">Как управлять сделкой</a></div>
            <? } ?>
        </td>
    </tr>
</table>
