<?php if(!defined('IN_STDF')) { 
    header("HTTP/1.0 404 Not Found");
    exit();
}?>
<div class="b-menu b-menu_line">
    <?php if ($user->login == $_SESSION['login']) { ?>
       <div class="b-menu__right-item"><a class="b-menu__link" href="/users/<?= $user->login ?>/setup/"><span class="b-icon b-icon__cont b-icon__cont_setemp b-icon_top_-2"></span>Настройки</a></div>
    <?php } else if ( hasPermissions('users')) { ?>
       <div class="b-menu__right-item"><a class="b-menu__link" href="/users/<?= $user->login ?>/setup/finance/"><span class="b-icon b-icon__cont b-icon__cont_setemp b-icon_top_-2"></span>Финансы</a></div>
    <?php } ?>
    <ul class="b-menu__list" data-menu="true" data-menu-descriptor="profile-nav">
        <?php  if ($activ_tab == -1) {
                $activ_tab = 1;
                $inner = "projects_inner.php";
        } ?>
        <li class="b-menu__item <?= $activ_tab == 1 ? 'b-menu__item_active' : ''; ?>" <?= $activ_tab == 1 ? 'data-menu-opener="true" data-menu-descriptor="profile-nav"' : ''; ?>>
			<a class="b-menu__link" href="/users/<?= $user->login ?>/projects/" title="Проекты" >
				<span class="b-menu__b1">Проекты и конкурсы</span>
			</a>
		</li>
		<li class="b-menu__item <?= $activ_tab == 5 ? 'b-menu__item_active' : ''; ?>" <?= $activ_tab == 5 ? 'data-menu-opener="true" data-menu-descriptor="profile-nav"' : ''; ?>>
			<a class="b-menu__link" href="/users/<?= $user->login ?>/opinions/" title="Отзывы" >
				<span class="b-menu__b1">Отзывы</span>
			</a>
		</li>
				<?php //if (substr($user->tabs, 2, 1) == 1) { ?>
        <li class="b-menu__item <?= $activ_tab == 2 ? 'b-menu__item_active' : ''; ?>" <?= $activ_tab == 2 ? 'data-menu-opener="true" data-menu-descriptor="profile-nav"' : ''; ?>>
			<a class="b-menu__link" href="/users/<?= $user->login ?>/info/" title="Информация" >
				<span class="b-menu__b1">Информация</span>
			</a>
    	</li>
        <?php //} ?>
        <?php if ($activ_tab == -1) {
                $activ_tab = 6;
                $inner = "rating_inner.php";
        }?>
        <li class="b-menu__item b-menu__item_last <?= $activ_tab == 11 ? 'b-menu__item_active' : ''; ?>" <?= $activ_tab == 11 ? 'data-menu-opener="true" data-menu-descriptor="profile-nav"' : ''; ?>>
			<a class="b-menu__link" href="/users/<?= $user->login ?>/rating/" title="Рейтинг" >
				<span class="b-menu__b1">Рейтинг</span>
			</a>
		</li>
        <?php if((($user->uid == $uid) || hasPermissions('users')) && TServiceOrderModel::model()->isExist($user->uid)){ ?>
        <li class="b-menu__item b-menu__item_last <?= $activ_tab == 12 ? 'b-menu__item_active' : ''; ?>" <?= $activ_tab == 12 ? 'data-menu-opener="true" data-menu-descriptor="profile-nav"' : ''; ?>>
            <a class="b-menu__link" href="/users/<?= $user->login ?>/tu-orders/" title="Заказы" >
                <span class="b-menu__b1">Заказы</span>
            </a>
        </li>        
        <?php } ?>        
    </ul>
</div>



