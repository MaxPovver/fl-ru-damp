<? // Вкладки для template2.php ?>
<div class="tabs">
	<ul class="clear">
  		<li class="tab1<?=($activ_tab==1 ? ' active' : '')?>"><span><a href="/users/<?=$user->login?>/setup/projects/">Проекты и конкурсы</a></span></li>
        <li class="tab3<?=($activ_tab==3 ? ' active' : '')?>"><span><a href="/users/<?=$user->login?>/setup/info/">Информация</a></span></li>
		<li class="tab5<?=($activ_tab==5 ? ' active' : '')?>"><span><a href="/users/<?=$user->login?>/setup/finance/">Финансы</a></span></li>
	</ul>
</div>
