<?php

/**
 * Шаблон уведомлений под меню сайта
 */

?>
<div class="b-page__desktop">
	<div class="l-outer w-outer">
		<header class="l-header">
			<div class="l-header-inside">
				<section class="l-header-section l-header-second-section">
					<div class="b-general-notification">                
<?php
    switch ($type):
        case SubBarNotificationHelper::TYPE_GUEST_NEW_ORDER:
?>
                        Поздравляем с успешной регистрацией на сайте и созданием первого заказа! 
                        Ваш логин: <span class="b-txt b-txt_color_000"><?=$login?></span>, 
                        пароль: <span class="b-txt b-txt_color_000"><?=$password?></span>
                        <a class="b-general-notification-link b-general-notification-employer-link" href="/users/<?=$login?>/setup/pwd/">
                            Поменять пароль
                        </a>
<?php                        
            break;
        
        case SubBarNotificationHelper::TYPE_GUEST_NEW_PROJECT:
?>        
                        Поздравляем с успешной регистрацией на сайте и публикацией первого проекта!
                        Ваш логин: <span class="b-txt b-txt_color_000"><?=$login?></span>, 
                        пароль: <span class="b-txt b-txt_color_000"><?=$password?></span>
                        <a class="b-general-notification-link b-general-notification-employer-link" href="/users/<?=$login?>/setup/pwd/">
                            Поменять пароль
                        </a>
<?php        
            break;
        
        case SubBarNotificationHelper::TYPE_GUEST_NEW_VACANCY:
?>        
                        Поздравляем с успешной регистрацией на сайте и публикацией первой вакансии!
                        Ваш логин: <span class="b-txt b-txt_color_000"><?=$login?></span>, 
                        пароль: <span class="b-txt b-txt_color_000"><?=$password?></span>
                        <a class="b-general-notification-link b-general-notification-employer-link" href="/users/<?=$login?>/setup/pwd/">
                            Поменять пароль
                        </a>
<?php        
            break;
        
        case SubBarNotificationHelper::TYPE_RESERVE_PROMO:
?>        
                        Выбран способ оплаты - <b>Прямая оплата</b>. Рекомендуем изменить на 
                        <a class="b-general-notification-link b-general-notification-employee-link" href="<?=$url?>">
                            Безопасную сделку
                        </a>
<?php        
            break;
        
        case SubBarNotificationHelper::TYPE_USER_ACTIVATED:
?>
                        Ваш аккаунт успешно активирован
<?php                        
            break;

        
        default:
            echo $text;
            
    endswitch;
?>
					</div>
				</section> 
			</div>
		</header>
	</div>
</div>