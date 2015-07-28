<?
if(hasPermissions('sbr')  && $_SESSION['access']=='A') {
    include('admin/header.php');
    return;
}
?>
<div class="nr-h c">
	<div class="nr-start">
        <a href="/sbr/?site=new"><img src="/images/norisk-start.png" alt="Начать новую «Безопасную Сделку»" class="lnk-nr-start" width="176" height="28" /></a>
        <p>Если у вас уже есть открытые проекты, то вы можете начать «Безопасную Сделку» прямо сейчас. Для этого перейдите в раздел &laquo;<a href="/users/<?=$sbr->login?>/setup/projects/">Проекты</a>&raquo;.</p>
	</div>
	<div class="nr-docs">
		<ul>
            <li><a href="<?=sbr::$scheme_types[sbr::SCHEME_AGNT][1]?>" target="_blank">Агентский договор</a></li>
            <li class="first"><a href="/offer_work_employer.pdf" target="_blank">Договор подряда</a></li>
            <li><a href="/help/?c=41">Помощь по «Безопасной Сделке»</a></li>
            <? if(hasPermissions('sbr') || hasPermissions('sbr_finance')) { ?>
              <li><a href="?site=admin">Администрирование</a></li>
            <? } ?>
		</ul>
	</div>
    <? include('tpl.header-manager.php') ?>
</div>
