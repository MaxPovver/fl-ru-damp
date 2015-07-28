<?php
/**
 * Модерирование пользовательского контента. Выбор типа заблокированных сущностей. Шаблон.
 * 
 * @author Max 'BlackHawk' Yastrembovich
 */
if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }
?>

<h2 class="b-layout__title">Заблокированные</h2>

<?php 
if ( !empty($aContents) && is_array($aContents) && count($aContents) ) {
    foreach ( $aContents as $sId => $aOne ) {
    ?><div class="b-layout__txt b-layout__txt_padbot_10"><a class="b-layout__link" href="/siteadmin/user_content/?site=blocked&cid=<?=$sId?>"><?=$aOne['name']?></a></div>
    <?php
    }
}
?>