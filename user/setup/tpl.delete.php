<?
if (!$_in_setup) {header ("HTTP/1.0 403 Forbidden"); exit;}
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/users.common.php");
$xajax->printJavascript('/xajax/');
//require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/account.common.php");
//$xajax->printJavascript('/xajax/');
//$a_count = $attach ? count($attach) : 0;
?>
<div class="b-layout b-layout_padtop_20">
    <h2 class="b-layout__title">Удаление аккаунта</h2>
    <div class="b-layout__txt b-layout__txt_padbot_10">После того как вы подтвердите удаление своего профиля, мы прекращаем обработку персональных данных, но сохраняем резервную копию ваших данных согласно п. 4.8 и п. 4.11 Пользовательского Соглашения.</div>
    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_padleft_50 b-layout__txt_italic"><span class="b-layout__txt b-layout__txt_inline-block b-layout__txt_width_35 b-layout__txt_margleft_-35">4.8.</span>Персональные данные Заказчика обрабатываются Исполнителем в течение срока их размещения на Сайте. Если персональные данные, размещенные на Сайте или в Профиле Заказчика, будут удалены, то Исполнитель прекращает их обработку. Однако Исполнитель имеет право сохранить резервную копию вышеуказанных данных Заказчика до ликвидации Исполнителя.</div>
    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_padleft_50 b-layout__txt_italic"><span class="b-layout__txt b-layout__txt_inline-block b-layout__txt_width_35 b-layout__txt_margleft_-35">4.11.</span>Исполнитель имеет право сохранить архивную копию и без ущерба для иных положений Соглашения без согласия Заказчика передать данные о Заказчике:</div>
    <ul class="b-layout__list b-layout__list_padleft_50">
        <li class="b-layout__item b-layout__item_padbot_10 b-layout__item_lineheight_15 b-layout__item_italic b-layout__item_style-type_disc">государственным органам, в том числе органам дознания и следствия, и органам местного самоуправления по их мотивированному запросу;</li>
        <li class="b-layout__item b-layout__item_padbot_10 b-layout__item_lineheight_15 b-layout__item_italic b-layout__item_style-type_disc">на основании судебного акта;</li>
        <li class="b-layout__item b-layout__item_lineheight_15 b-layout__item_italic b-layout__item_style-type_disc">в иных предусмотренных действующим законодательством РФ случаях.</li>
    </ul>
    <div class="b-layout__txt b-layout__txt_padbot_10">Доносим до вашего сведения, что вы больше не имеете права регистрировать новые аккаунты, согласно п. 1.1 <a href="https://st.fl.ru/about/documents/appendix_2_regulations.pdf" class="b-layout__link">Правил сайта</a>.</div>
    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_padleft_50 b-layout__txt_italic"><span class="b-layout__txt b-layout__txt_inline-block b-layout__txt_width_35 b-layout__txt_margleft_-35">1.1.</span>Запрещена множественная регистрация Профилей одним Пользователем (один Пользователь может зарегистрировать один Профиль Фрилансера и один Профиль Работодателя). Удаление или блокировка аккаунта не дают права на регистрацию нового.</div>
    <div class="b-layout__txt b-layout__txt_padbot_10">Если вы решите возобновить работу на сайте, просто напишите в <a class="b-layout__link b-layout__link_underline" href="http://feedback.fl.ru/">Службу поддержки</a>, и мы восстановим ваш аккаунт.</div>
    
    
    
    <form name="del_acc_form" method="post" id="del_acc_form">
    <div class="b-check b-check_padbot_40">
        <input type="checkbox" id="b-check1" class="b-check__input" name="" value="">
        <label for="b-check1" class="b-check__label b-check__label_fontsize_13">Я согласен с процедурой удаления</label>
    </div>
    <a href="" class="b-button b-button_flat b-button_flat_red b-button_disabled" id="del_acc">Удалить аккаунт</a>
    <input type="hidden" value="delete" name="action">
    <input type="hidden" value="ba091ffc43a78382662535d87b6317f5" name="u_token_key"></form>
</div>