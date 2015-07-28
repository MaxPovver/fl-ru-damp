<div class="b-menu b-menu_line">
    <?php if (($user->login == $_SESSION['login'] || hasPermissions('users'))) { ?>
       <div class="b-menu__right-item  b-menu__item_del-main"><a class="b-menu__link b-menu__link_del" href="/users/<?= $user->login ?>/setup/delete/" title="Удалить аккаунт">Удалить аккаунт</a></div>
    <?php } ?>
    <ul class="b-menu__list" data-menu="true" data-menu-descriptor="profile-nav">
	<?php if(!($inner == "main_inner.php" || $inner == "portfolio_in_setup.php"||$inner == "tpl.portfolio.php"
            || $inner == "inform_inner.php"||$inner == "finance_inner.php" || $inner == "foto_inner.php"
            || $inner == "mailer_inner.php" || $inner == "list_inner.php" || $inner == "safety_inner.php" || $inner == '')) { ?>
        <li class="b-menu__item b-menu__item_active b-page__ipad b-page__iphone" data-menu-opener="true" data-menu-descriptor="profile-nav" style="margin-top:-40px !important;">
            <a class="b-menu__link" href="#" title="Не выбрано">
                Не выбрано
            </a>
        </li>
    <?php } ?>
        <li class="b-menu__item <? if ($inner == "main_inner.php") {?> b-menu__item_active<?php }?>" <? if ($inner == "main_inner.php") {?>data-menu-opener="true" data-menu-descriptor="profile-nav"<?php }?>>
            <a class="b-menu__link" href="/users/<?=$user->login?>/setup/main/" title="Основные настройки">
            Основные настройки
            </a>
		</li>
  	<? if (substr($user->tabs, 0, 1)  == 1) { if ($activ_tab == -1) {$activ_tab = 1; $inner = "tpl.portfolio.php";}?>
        <li class="b-menu__item <?=($activ_tab==1 ? ' b-menu__item_active' : '')?>" <?= $activ_tab == 1 ? 'data-menu-opener="true" data-menu-descriptor="profile-nav"' : ''; ?>>
            <a class="b-menu__link" href="/users/<?=$user->login?>/setup/portfolio/" title="<?=view_tab_name($user->tab_name_id)?>">
                <?=view_tab_name($user->tab_name_id)?>
            </a>
		</li>
  	<? } ?>
  	<? if (substr($user->tabs, 2, 1)  == 1) { if ($activ_tab == -1) {$activ_tab = 3; $inner = "inform_inner.php";} ?>
        <li class="b-menu__item <?=($activ_tab==3 ? ' b-menu__item_active' : '')?>" <?= $activ_tab == 3 ? 'data-menu-opener="true" data-menu-descriptor="profile-nav"' : ''; ?>>
            <a class="b-menu__link" href="/users/<?=$user->login?>/setup/info/" title="Информация">
                Информация
            </a>
		</li>
  	<? } ?>
  	<? if ($activ_tab == -1) {$activ_tab = 5; $inner = "finance_inner.php";}?>
        <li class="b-menu__item <?=($activ_tab==5 ? ' b-menu__item_active' : '')?>" <?= $activ_tab == 5 ? 'data-menu-opener="true" data-menu-descriptor="profile-nav"' : ''; ?>>
            <a class="b-menu__link" href="/users/<?=$user->login?>/setup/finance/" title="Финансы">
                Финансы
            </a>
		</li>
        
        <li class="b-menu__item <? if ($inner == "foto_inner.php") {?>b-menu__item_active<?php }?>" <? if ($inner == "foto_inner.php") {?>data-menu-opener="true" data-menu-descriptor="profile-nav"<?php }?>>
            <a class="b-menu__link" href="/users/<?=$user->login?>/setup/foto/" title="Фотография">
            Фотография
            </a>
		</li>
        <li class="b-menu__item <? if ($inner == "mailer_inner.php") {?>b-menu__item_active<?php }?>" <? if ($inner == "mailer_inner.php") {?>data-menu-opener="true" data-menu-descriptor="profile-nav"<?php }?>>
            <a class="b-menu__link" href="/users/<?=$user->login?>/setup/mailer/" title="Уведомления">
            Уведомления
            </a>
		</li>
        <li class="b-menu__item <? if ($inner == "list_inner.php") {?>b-menu__item_active<?php }?>" <? if ($inner == "list_inner.php") {?>data-menu-opener="true" data-menu-descriptor="profile-nav"<?php }?>>
            <a class="b-menu__link" href="/users/<?=$user->login?>/setup/tabssetup/" title="Закладки">
            Закладки
            </a>
		</li>
        <li class="b-menu__item b-menu__item_safe-main <? if ($inner == "safety_inner.php") {?>b-menu__item_active<?php }?>" <? if ($inner == "safety_inner.php") {?>data-menu-opener="true" data-menu-descriptor="profile-nav"<?php }?>>
            <a class="b-menu__link" href="/users/<?=$user->login?>/setup/safety/" title="Безопасность">
            Безопасность
            </a>
		</li>
      <?php if ($user->login == $_SESSION['login'] || hasPermissions('users')) { ?>
        <li class="b-menu__item b-menu__item_yet i-shadow">
            <a class="b-menu__link" href="#" title="Еще" onClick="$('menu_yet').toggleClass('b-shadow_hide');return false;">Еще</a>
            <div id="menu_yet" class="b-shadow b-shadow_m b-shadow_pad_10 b-shadow_width_140 b-shadow_top_20 b-shadow_left_-53 b-shadow_hide">
                  <div class="b-layout__txt b-menu__item_safe"><a class="b-menu__link" href="/users/<?=$user->login?>/setup/safety/" title="Безопасность">Безопасность</a></div>
               <?php if (($user->login == $_SESSION['login'] || hasPermissions('users'))) { ?>
                  <div class="b-layout__txt b-menu__item_del"><a class="b-menu__link" href="/users/<?= $user->login ?>/setup/delete/" title="Удалить аккаунт">Удалить аккаунт</a></div>
               <?php } ?>
               <span class="b-shadow__icon b-shadow__icon_nosik"></span>
            </div>
		</li>
      <?php } ?>
    </ul>
</div>



