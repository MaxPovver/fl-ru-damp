<?php

/**
 * Основной шаблон списка ПРО услуг
 */

$current_uid = get_uid(false);

if($current_uid > 0) {
    require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/quick_payment.common.php");
    $xajax->printJavascript('/xajax/');
?>
<script type="text/javascript">
var account_sum = <?= round($account->sum, 2)?>;
var role = 'FRL';
</script>
<? } else { //if?>
<script type="text/javascript">
var alowLogin = function(){
    if($('login_inp').get('value') != '' && $('pass_inp').get('value') != ''){
        $('auth_form').submit();
    };
}
</script>
<? } //else?>
<div class="b-layout b-layout_padtop_15 g-txt_center">

    <h1 id="header_payed_pro" class="b-layout__title b-layout__title_bold b-layout__title_fs30 b-layout__title_color_ff8600 b-layout__title_padbot_30">
        <?php if ($pro_last): ?>
        Профессиональный аккаунт
        <div class="b-layout__txt b-layout__txt_padtop_5 b-layout__txt_center b-layout__txt_fontsize_20">
            Действует до <?= date('d.m.Y', strtotime($pro_last)) ?>
        </div>
        <?php else: ?>
        Получи профеcсиональный аккаунт фрилансера<br/> и зарабатывай больше!
        <?php endif; ?>
    </h1>
    
    <div class="b-layout__txt b-layout__txt_fontsize_25 b-layout__txt_color_333 b-layout__txt_padbot_80">
        С PRO аккаунтом ты <span class="b-layout__txt_color_ff8600 b-layout__txt_italic">увеличишь свой доход</span><br/> 
        за счет повышения рейтинга на 20% и доступа к премиум проектам.
    </div>
    
    <?php if (false && !$pro_last && (isWasPlatipotom() || isAllowTestPro())): ?>
    <div class="b-fon b-fon_inline-block b-fon_pad_10 b-fon_bg_d3f2c0 b-fon__nosik_bot b-fon_margbot_20">
        Теперь можно приобрести аккаунт, оплатив его потом (через сервис <a class="b-layout__link" href="http://PlatiPotom.ru" target="_blank">PlatiPotom.ru</a>). 
        Вы станете PRO сразу, а оплатите его с отсрочкой до 30 дней.
    </div>
    <?php endif; ?>
<?php 

include_once("tpl.setting.pro.php");
include_once('plans.php');

if ($current_uid > 0):
    //Вывод попапа оплаты
    echo quickPaymentPopupPro::getInstance()->render();
    

    //@todo: временное решение сообщения об успешной покупки ПРО основанное на старом шаблоне, 
    //@todo: потом нужно перенести в quickPaymentPopupPro
    if (isset($_GET['quickpro_ok'])):
        require_once($_SERVER['DOCUMENT_ROOT'] . "/templates/quick_buy_pro_win.php");
    endif;
endif;

?>
</div>