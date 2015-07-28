<?php
unset($_SESSION['new_public']);

switch ($project['kind']) {
    case 7:
        $type = 'конкурсе';
        $published = "Конкурс опубликован";
        break;
    
    case 4:
        $type = 'вакансии';
        $published = "Вакансия опубликована";
        break;

    default:
        $type = 'проекте';
        $published = "Проект опубликован";
        break;
    
    
}

$project_url = isset($project['url']) 
        ? $project['url']
        : $GLOBALS['host'] . getFriendlyURL('project', $project);

$url = urlencode($project_url);
$price = ($project['cost'] != 0 && $project['price_display']) ? str_replace('&euro;', '€', $project['price_display']) : 'по договоренности';
$title = urlencode(iconv('CP1251', 'UTF-8', html_entity_decode($project['name'].' - '.$price)));
$metrika = "yaCounter6051055.reachGoal('fl_share_proj');";

$banner_promo_inline = true;
?>
<div class="b-shadow b-shadow_center b-shadow_width_380 b-shadow_zindex_3">
    <div class="" style="padding: 20px "><?/* @todo Найти нужный css-класс. Ждем верстальщика */?>
        <div class="b-layout__title b-layout__title_center b-layout__title_padbot_20">
            <?=$published?>
        </div>

        <?php if(false): ?>
        <a class="b-button b-button_flat b-button_flat_green b-button_block" 
            href="/public/?step=1&public=<?= $project['id'] ?>&pay_services=1">
            Привлеките больше исполнителей
        </a>
        <?php endif; ?>
        
        <div class="b-layout__txt b-layout__txt_padbot_20 b-layout__txt_center">
            <strong>Расскажите о <?=$type?> друзьям</strong>
        </div>

        <div class="b-buttons b-buttons_center">
            <div class="custom_images">
                <a href="http://www.facebook.com/share.php?u=<?=$url?>"
                   onclick="<?=$metrika?>javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600');return false;" 
                   class="b-icon b-icon__soc_f"></a>
                <a href="http://vk.com/share.php?url=<?=$url?>&title=<?=$title?>"
                   onclick="<?=$metrika?>javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600');return false;" 
                   class="b-icon b-icon__soc_vk"></a>
                <a href="https://plus.google.com/share?url=<?=$url?>&hl=ru" 
                   onclick="<?=$metrika?>javascript:window.open(this.href, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600');return false;" 
                   class="b-icon b-icon__soc_g"></a>
            </div>
        </div>
    </div>
    <div class="b-layout__txt b-layout__txt_center b-layout__txt_padbot_10 b-layout__txt_padtop_10" style="background:#f5f5f5;">
        <?php require_once($_SERVER['DOCUMENT_ROOT'] . "/banner_promo.php"); ?>
    </div>
    <span class="b-shadow__icon_close"></span>
</div>