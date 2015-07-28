<?
if(hasPermissions('sbr')  && $_SESSION['access']=='A') {
    include('admin/header.php');
    return;
}
?>
<div class="nr-h c">
	<div class="nr-start">
        <p>&nbsp;</p>
	</div>
	<div class="nr-docs">
		<ul>
            <li><a href="<?=sbr::$scheme_types[sbr::SCHEME_AGNT][1]?>" target="_blank">Агентский договор</a></li>
            <li class="first"><a href="/offer_work_free-lancer.pdf" target="_blank">Договор подряда</a></li>
            <li><a href="/help/?c=41">Помощь по «Безопасной Сделке»</a></li>
            <? if(hasPermissions('sbr') || hasPermissions('sbr_finance')) { ?>
              <li><a href="?site=admin">Администрирование</a></li>
            <? } ?>
		</ul>
	</div>
    <? include('tpl.header-manager.php') ?>
</div>
