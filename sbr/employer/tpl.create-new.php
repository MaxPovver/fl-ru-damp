<?php

$crumbs = 
array(
    0 => array(
        'href' => '/' . sbr::NEW_TEMPLATE_SBR . '/', 
        'name' => '«Мои Сделки»'
    ),
    1 => array(
        'href' => '', 
        'name' => 'Новая сделка'
    )
);
$css_selector_crumbs = "b-page__title_padbot_30";
// Хлебные крошки
include($_SERVER['DOCUMENT_ROOT'] . "/sbr/tpl.sbr-crumbs.php"); 
?>

<div class="b-layout__txt b-layout__txt_fontsize_22">&mdash; <a class="b-layout__link" href="/users/<?=$sbr->login?>/setup/projects/">В открытом проекте</a></div>
<div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padleft_30 b-layout__txt_padbot_40">
    <?php if($projects_cnt['open'] == 1) {?>
    На данный момент у вас 1 открытый проект. Вы можете начать в нем «Безопасную Сделку».
    <?php } else { //if?>
    Выберите любой из <?= $projects_cnt['open']; ?> открытых вами проектов и начните по нему «Безопасную Сделку».
    <?php } //else?>
</div>
<div class="b-layout__txt b-layout__txt_fontsize_22">&mdash; <a class="b-layout__link" href="?site=create">Без публикации проекта</a></div>
<div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padleft_30 b-layout__txt_padbot_40">Если вы определились с исполнителем, создайте проект, который не будет опубликован на главной странице, и сразу начинайте по нему «Безопасную Сделку». </div>
