<?php

/**
 * Вывод планов покупки ПРО на другие страницы
 * включая попап для покупки, так же везде нужно
 * подключить xajax скрипты
 */

$current_uid = get_uid(false);

//Не показываем блок при условии
if ($current_uid <= 0 || is_pro() || is_emp() || 
   (isset($g_page_id) && in_array($g_page_id, array('0|9', '0|35', '0|26', '0|993'))) || 
   !isAllowTestPro()) {
    
    return;
}


require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");

?>
<div class="b-layout b-layout__page">
    <div class="body">
        <div class="main">    
            <div id="header_payed_pro" class="b-page__title b-page__title_center b-page__title_padbot_5">
                Купите сегодня аккаунт PRO на месяц со скидкой 45%
            </div>
            <div class="b-layout__txt b-layout__txt_fontsize_16 b-layout__txt_center b-layout__txt_padbot_20">
                Доверие Заказчиков. Неограниченное число откликов на проекты/конкурсы/вакансии. Доступ к проектам/конкурсам/вакансиям — «только для PRO».
            </div>
<?php

include_once('plans.php');
require_once($_SERVER['DOCUMENT_ROOT'] . "/templates/quick_buy_pro.php");

?>
        </div>
    </div>
</div>