<?php

if (!$_in_setup) {
    header ("HTTP/1.0 403 Forbidden"); 
    exit;
}

if ($is_adm) {
    require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/account.common.php");
    $xajax->printJavascript('/xajax/');
}

?>
<div class="b-layout b-layout_pad_20">
   <div class="b-fon b-fon_width_full b-fon_padbot_10 b-fon_margbot_20">
        <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffeeeb">
            <span class="b-icon b-icon_sbr_rattent b-icon_margleft_-25"></span>
            Финансовые данные были удалены. 
            Для восстановления, пожалуйста, обратитесь в <a href="https://feedback.fl.ru/">Службу поддержки</a>. 
            Ваши данные будут восстановлены на сайте после запроса в бухгалтерию.
        </div>
    </div>
    <?php if($is_adm): ?>
    <div class="b-buttons b-buttons_padtop_20">
       <a href="javascript:void(0)" 
           onclick="confirm('Восстановить финансовые данные пользователя?') && xajax_repairFinData(<?=$uid?>);" 
           class="b-button b-button_flat b-button_flat_orange">
            Восстановить данные
       </a>
       <span class="b-buttons__txt b-buttons__txt_padleft_20">
           После восстановления финансовые данные будут заблокированы для пользователя. Их необходимо будет проверить и подтвердить/отклонить.<br/>
           Если финансы не подтверждены то пользователь не сможет воспользоваться услугами где они приминимы, например в БС зарезервировать/выплатить средства.
       </span>
    </div>
    <?php endif; ?>
</div>