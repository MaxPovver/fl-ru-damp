<? 

// Вкладки для template2.php 

?>
<div class="b-menu b-menu_line">
    <ul class="b-menu__list">
  	<? if (substr($user->tabs, 0, 1)  == 1) { $activ_tab = ($activ_tab==-1 ? 1 : $activ_tab); ?>
        <li class="b-menu__item <?=($activ_tab==1 ? ' b-menu__item_active' : '')?>"><a class="b-menu__link" href="/users/<?=$user->login?>/setup/portfolio/" title="<?=view_tab_name($user->tab_name_id)?>"><span class="b-menu__b1"><?=view_tab_name($user->tab_name_id)?></a></span></a></li>
  	<? } ?>
  	<? if (substr($user->tabs, 2, 1)  == 1) { $activ_tab = ($activ_tab==-1 ? 3 : $activ_tab); ?>
        <li class="b-menu__item <?=($activ_tab==3 ? ' b-menu__item_active' : '')?>"><a class="b-menu__link" href="/users/<?=$user->login?>/setup/info/" title="Информация"><span class="b-menu__b1">Информация</span></a></li>
  	<? } ?>
<?php

    $activ_tab = ($activ_tab==-1 ? 5 : $activ_tab);
    
?>
        <li class="b-menu__item b-menu__item_last <?=($activ_tab==5 ? ' b-menu__item_active' : '')?>"><a class="b-menu__link" href="/users/<?=$user->login?>/setup/finance/" title="Финансы"><span class="b-menu__b1">Финансы</span></a></li>
    </ul>
</div>
