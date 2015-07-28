<?
$prev_tab = 1;
?>

<div class="b-menu b-menu_line">
    <?php if (($user->login == $_SESSION['login'] || hasPermissions('users'))) { ?>
       <div class="b-menu__right-item"><a class="b-menu__link b-menu__link_del" href="/users/<?= $user->login ?>/setup/delete/">Удалить аккаунт</a></div>
    <?php } ?>
   <ul class="b-menu__list" data-menu="true" data-menu-descriptor="profile-nav">
	<?php if (!(!$activ_tab || $inner == "inform_inner.php"||$inner == "../../setup/finance_inner.php"||$inner == '')) { ?>
        <li class="b-menu__item b-menu__item_active b-page__ipad b-page__iphone" data-menu-opener="true" data-menu-descriptor="profile-nav" style="margin-top:-40px !important;">
            <a class="b-menu__link" href="#" title="Не выбрано">
                Не выбрано
            </a>
        </li>
    <?php } ?>
        <li class="b-menu__item <? if ($inner == "../../setup/main_inner.php") {?>b-menu__item_active <?php } ?>" <? if ($inner == "../../setup/main_inner.php") {?>data-menu-opener="true" data-menu-descriptor="profile-nav"<?php } ?>>
            <a class="b-menu__link" href="/users/<?=$user->login?>/setup/main/" title="Основные настройки" >
            Основные настройки
            </a>
        </li>
        <? $activ_tab = ($activ_tab==-1 ? 2 : $activ_tab);?>
        <li class="b-menu__item <?=($activ_tab==2 ? ' b-menu__item_active' : '')?>" <?=($activ_tab==2 ? 'data-menu-opener="true" data-menu-descriptor="profile-nav"' : '')?>>
            <a class="b-menu__link" href="/users/<?=$user->login?>/setup/info/" title="Информация">
                Информация
            </a>
		</li>
        <li class="b-menu__item <?=($activ_tab==5 ? ' b-menu__item_active' : '')?>" <?=($activ_tab==5 ? 'data-menu-opener="true" data-menu-descriptor="profile-nav"' : '')?>>
            <a class="b-menu__link" href="/users/<?=$user->login?>/setup/finance/" title="Финансы">
                Финансы
            </a>
        </li>
        
        
        
        <li class="b-menu__item <? if ($inner == "../../setup/foto_inner.php") {?>b-menu__item_active<?php } ?>" <? if ($inner == "../../setup/foto_inner.php") {?>data-menu-opener="true" data-menu-descriptor="profile-nav"<?php } ?>>
            <a class="b-menu__link" href="/users/<?=$user->login?>/setup/foto/" title="Фотография">
            Фотография
            </a>
        </li>
        <li class="b-menu__item <? if ($inner == "mailer_inner.php") {?>b-menu__item_active<?php } ?>" <? if ($inner == "mailer_inner.php") {?>data-menu-opener="true" data-menu-descriptor="profile-nav"<?php } ?>>
            <a class="b-menu__link" href="/users/<?=$user->login?>/setup/mailer/" title="Уведомления">
            Уведомления
            </a>
        </li>
        <li class="b-menu__item <? if ($inner == "../../setup/safety_inner.php") {?>b-menu__item_active<?php } ?>" <? if ($inner == "../../setup/safety_inner.php") {?>data-menu-opener="true" data-menu-descriptor="profile-nav"<?php } ?>>
            <a class="b-menu__link" href="/users/<?=$user->login?>/setup/safety/" title="Безопасность">
            Безопасность
            </a>
        </li>
    </ul>
</div>

