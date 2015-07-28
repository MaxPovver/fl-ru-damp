<?php
if(!defined('IN_STDF')) { 
    header("HTTP/1.0 404 Not Found");
    exit();
}
?>


<div class="b-menu b-menu_line">
    <?php if ($user->login == $_SESSION['login']) { ?>
       <div class="b-menu__right-item"><a class="b-menu__link" href="/users/<?= $user->login ?>/setup/"><span class="b-icon b-icon__cont b-icon__cont_setfrl b-icon_top_-2"></span>Настройки</a></div>
    <?php } else if ( hasPermissions('users')) { ?>
       <div class="b-menu__right-item"><a class="b-menu__link" href="/users/<?= $user->login ?>/setup/finance/"><span class="b-icon b-icon__cont b-icon__cont_setfrl b-icon_top_-2"></span>Финансы</a></div>
    <?php } ?>
    <ul class="b-menu__list" data-menu="true" data-menu-descriptor="profile-nav" >
				<?php if (substr($user->tabs, 0, 1) == 1 || hasPermissions('users')) { 
						if ($activ_tab == -1) {
								$activ_tab = 1;
								$inner = "portfolio_inner.php";
        }?>
        <li class="b-menu__item <?= $activ_tab == 1 ? 'b-menu__item_active' : ''; ?> b-menu_portfolio-item" <?= $activ_tab == 1 ? 'data-menu-opener="true" data-menu-descriptor="profile-nav"' : ''; ?>>
			<a class="b-menu__link" href="/users/<?= $user->login ?>/portfolio/" title="<?= view_tab_name($user->tab_name_id) ?>">
				<span class="b-menu__b1"><?= view_tab_name($user->tab_name_id) ?><?=((substr($user->tabs,0,1)==0 && hasPermissions('users'))?' [с]':'')?></span>
			</a>
		</li>
        <?php } ?>
                
        <?php 
        $hasPerm = hasPermissions('users');
        if ( (substr($user->tabs, 7, 1) == 1 && $hide_tu_for_others == FALSE) || $hasPerm) { 
            if ($activ_tab == -1) {
                $activ_tab = 2;
                $inner = "tu_inner.php";
            }    
        ?>        
        <li class="b-menu__item <?= $activ_tab == 2 ? 'b-menu__item_active' : ''; ?> b-menu_services-item" <?= $activ_tab == 2 ? 'data-menu-opener="true" data-menu-descriptor="profile-nav"' : ''; ?>>
            <a class="b-menu__link" href="/users/<?= $user->login ?>/tu/" title="Типовые услуги">
                <span class="b-menu__b1">Типовые услуги<?= (!$user->tabs[7] && $hasPerm) ? ' [c]' : '' ?></span>
            </a>
        </li> 
        <?php } ?> 
        
        <? if ($activ_tab == -1) {
							$activ_tab = 5;
							$inner = "opinions_inner.php";
							$ops_type='norisk';
				} ?>
        <li class="b-menu__item <?= $activ_tab == 5 ? 'b-menu__item_active' : ''; ?> b-menu_testimonials-item" <?= $activ_tab == 5 ? 'data-menu-opener="true" data-menu-descriptor="profile-nav"' : ''; ?>>
			<a class="b-menu__link" href="/users/<?= $user->login ?>/opinions/" title="Отзывы">
				<span class="b-menu__b1">Отзывы</span>
			</a>
		</li>
        <?php if (substr($user->tabs, 2, 1) == 1) { ?>
        <li class="b-menu__item <?= $activ_tab == 3 ? 'b-menu__item_active' : ''; ?> b-menu_information-item" <?= $activ_tab == 3 ? 'data-menu-opener="true" data-menu-descriptor="profile-nav"' : ''; ?>>
			<a class="b-menu__link" href="/users/<?= $user->login ?>/info/" title="Информация">
				<span class="b-menu__b1">Информация</span>
			</a>
		</li>
		<?php } ?>
		<?php
		if (substr($user->tabs, 4, 1) == 1 || (hasPermissions('users'))) {
				if ($activ_tab == -1) {
						$activ_tab = 6;
						$inner = "rating_inner.php";
				}
				?>
		<li class="b-menu__item b-menu__item_last <?= $activ_tab == 6 ? 'b-menu__item_active' : ''; ?> b-menu_rating-item " <?= $activ_tab == 6 ? 'data-menu-opener="true" data-menu-descriptor="profile-nav"' : ''; ?>>
			<a class="b-menu__link" href="/users/<?= $user->login ?>/rating/" title="Рейтинг">
				<span class="b-menu__b1">Рейтинг<?=((substr($user->tabs,4,1)==0 && hasPermissions('users'))?' [с]':'')?></span>
			</a>
		</li>
		<?php } ?>
    </ul>
</div>






