<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; } 
$s = 'style="color: #666;"'; 

if ( !isset($aPermissions) ) {
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/permissions.php");
    $aPermissions = permissions::getUserPermissions( $uid );
}

foreach ( $aPermissions as $sPermission ) {
	$sVar  = 'bHas' . ucfirst( $sPermission );
	$$sVar = true;
}
?>
<?php if ( $bHasAll || $bHasAdm ) { ?>

    <?php if ( $bHasAll || $bHasUsers || $bHasProjects || $bHasBlogs || $bHasCommunes ) { ?>
    <div class="admin-menu">
    	<h3>Действия</h3>
    	<ul>
            <?php if ( $bHasAll || $bHasUsers || $bHasProjects || $bHasBlogs || $bHasCommunes ) { ?>
    		<li><a <?=($menu_item == 1 ? $s : '')?> href="/siteadmin/admin_log/?site=log">Лента всех действий</a></li>
    		<?php } ?>
    		<?php if ( $bHasAll || $bHasUsers ) { ?>
    		<li><a <?=($menu_item == 2 ? $s : '')?> href="/siteadmin/admin_log/?site=user">Нарушители (бан и пред)</a></li>
    		<?php } ?>
    		<?php if ( $bHasAll || $bHasProjects ) { ?>
    		<li><a <?=($menu_item == 3 ? $s : '')?> href="/siteadmin/admin_log/?site=proj">Проекты и конкурсы</a></li>
    		<li><a <?=($menu_item == 10 ? $s : '')?> href="/siteadmin/admin_log/?site=offer">Предложения</a></li>
    		<?php } ?>
    	</ul>
    </div>
    <?php } ?>
    
    <?php if ( $bHasAll || $bHasUsers ) { ?>
    <div class="admin-menu">
    	<h3>IP-адреса</h3>
    	<ul>
    		<li><a <?=($menu_item == 4 ? $s : '')?> href="/siteadmin/user_search/">Поиск пользователей</a></li>
    		<li><a <?=($menu_item == 5 ? $s : '')?> href="/siteadmin/gray_ip">Серый список IP</a></li>
    	</ul>
    </div>
    <?php } ?>
    
    <?php if ( $bHasAll || $bHasProjects || $bHasUsers ) { ?>
    <div class="admin-menu">
    	<h3>Жалобы</h3>
    	<ul>
            <?php if ( $bHasAll || $bHasProjects ) { ?>
    		<li><a <?=($menu_item == 7  ? $s : '')?> href="/siteadmin/ban-razban/?mode=complain">Жалобы на проекты</a></li>
    		<?php } ?>
    		<?php if ( $bHasAll || $bHasUsers ) { ?>
    		<li><a <?=($menu_item == 11 ? $s : '')?> href="/siteadmin/messages_spam">Жалобы на спам</a></li>
    		<?php } ?>
    	</ul>
    </div>
    <?php } ?>
    
    <?php if ( $bHasAll ) { ?>
    <div class="admin-menu">
    	<h3>Модераторы</h3>
    	<ul>
    		<li><a <?=($menu_item == 8 ? $s : '')?> href="/siteadmin/admin_log/?site=stat">Все модераторы</a></li>
    	</ul>
    </div>
    <?php } ?>
    
    <?php if ( $bHasAll || $bHasUsers || $bHasProjects ) { ?>
    <div class="admin-menu">
    	<h3>Настройка</h3>
    	<ul>
    		<li><a <?=($menu_item == 9 ? $s : '')?> href="/siteadmin/admin_log/?site=notice">Уведомления</a></li>
    	</ul>
    </div>
    <?php } ?>
<?php } ?>