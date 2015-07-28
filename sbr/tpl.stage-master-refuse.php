<?php
$crumbs = 
array(
    0 => array(
        'href' => '/' . sbr::NEW_TEMPLATE_SBR . '/', 
        'name' => '«Мои Сделки»'
    ),
    1 => array(
        'href' => '',
        'name' => $sbr->data['name'] . ' ' . $sbr->getContractNum()
    )
);
// Хлебные крошки
include("tpl.sbr-crumbs.php"); 

// Заказчик или исполнитель
include($_SERVER['DOCUMENT_ROOT'] . "/sbr/tpl.stage-user.php");

// Оыкно помощи
include ($_SERVER['DOCUMENT_ROOT'] . "/sbr/tpl.help.php");

?>

<div class="b-fon b-fon_width_full">
    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf">
			<span class="b-icon b-icon_sbr_gok b-icon_margleft_-20"></span>Вы отказались от этой сделки. Заказчик получил уведомление. <a class="b-fon__link" href="/<?= sbr::NEW_TEMPLATE_SBR; ?>/">Вернуться к списку сделок</a>.
	</div>
</div>	