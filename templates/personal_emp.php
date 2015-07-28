<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_offers.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_offers_dialogue.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/notifications.php");

//$cnt_emp_new_offers   = projects_offers::CountOffersForEmp($_SESSION['uid'], true);
//$cnt_emp_new_messages = projects_offers_dialogue::CountMessagesForEmp($_SESSION['uid'], true);

//$sbr_info = sbr_meta::getUserInfo($_SESSION['uid']);
$user_link = "/users/{$_SESSION['login']}";

/*if ($cnt_emp_new_messages > 0) {
    if($cnt_emp_new_messages != $_SESSION['lst_emp_new_messages']['cnt']) {
        $last_emp_new_messages_pid = projects_offers_dialogue::FindLastMessageProjectForEmp($_SESSION['uid']);
    } else {
        $last_emp_new_messages_pid = $_SESSION['lst_emp_new_messages']['pid'];
    }
} else {
    $last_emp_new_messages_pid = false;
}
$_SESSION['lst_emp_new_messages']['cnt'] = $cnt_emp_new_messages;*/

//$asw_w = 205; // ширина переключателя аккаунтов (см. использование здесь и в acc_switch.php).

$anti_login = ($_SESSION['anti_uid']) ? $_SESSION['anti_login'] : 'Логин';

$projects_active = $grey_main && $kind != 8 && $kind != 2 && $kind != 4;
$konkurs_active = $grey_main && $kind == 2;
$vacancy_active = $grey_main && $kind == 4;
$grey_catalog = isset($grey_catalog) && (@$grey_catalog == 1);

//Уведомления ЛС
$tip_msg = notifications::getMessTip();

//Уведомления по БС
$tip_sbr = notifications::getAllSbrTip();
$link_sbr = '/' . sbr::NEW_TEMPLATE_SBR . '/';

//Уведомления гуповое по проектам и заказам
//$tip_group = notifications::getEmpGroupTip();

//Уведомления и ссылки для Проектов
$tip_prj = notifications::getProjectsTipEmp();

//Уведомления и ссылки для Заказов ТУ
if (@$_SESSION['tu_orders']):
    $tip_tu = notifications::getTServicesOrdersTip();
endif;


$account_sum_is_plus = $_SESSION['ac_sum'] >= 0;
$account_sum_format = view_account_format();

?>
<script type="text/javascript">
var notification_delay = '<?=NOTIFICATION_DELAY?>';
var prj_check_delay = '<?=PRJ_CHECK_DELAY?>';
</script>

<div class="b-bar b-bar_fixed">                                    
    <div class="l-outer">

        <header class="l-header">
            <div class="l-header-inside">

                <section class="l-header-section l-header-first-section">

                    <span class="b-logo">
                        <a href="/" class="b-logo-link" title="На главную">FL.ru</a>
                    </span>

                    <ul class="b-primary-menu">
                        <li class="b-primary-menu-clause b-primary-menu-tasks-clause<?php if ($projects_active) { ?> b-primary-menu-current-clause<?php } ?>">
                            <a data-ga-event="{ec: 'customer', ea: 'main_menu_clicked',el: 'projects'}" href="/projects/" class="b-primary-menu-clause-link" title="Список проектов для фрилансеров">Работа</a>
                        </li>
                        <li class="b-primary-menu-clause b-primary-menu-employees-clause<?php if ($grey_catalog) { ?> b-primary-menu-current-clause<?php } ?>">
                            <a data-ga-event="{ec: 'customer', ea: 'main_menu_clicked',el: 'freelancers'}" href="/freelancers/" class="b-primary-menu-clause-link" title="Каталог фрилансеров">Фрилансеры</a>
                        </li>
                        <li class="b-primary-menu-clause b-primary-menu-services-clause <?= isCurrentPage('tu','b-primary-menu-current-clause','',null,array(1 => 'order')) ?>">
                            <a data-ga-event="{ec: 'customer', ea: 'main_menu_clicked',el: 'tu'}" href="/tu/" class="b-primary-menu-clause-link" title="Каталог услуг фрилансеров">Услуги</a>
                        </li>
                        <li class="b-primary-menu-clause b-primary-menu-competitions-clause<?php if ($konkurs_active) { ?> b-primary-menu-current-clause<?php } ?>">
                            <a data-ga-event="{ec: 'customer', ea: 'main_menu_clicked',el: 'konkurs'}" href="/konkurs/" class="b-primary-menu-clause-link" title="Список конкурсов для фрилансеров">Конкурсы</a>
                        </li>
                        <li class="b-primary-menu-clause b-primary-menu-vacancy-clause<?php if ($vacancy_active) {?> b-primary-menu-current-clause<?php }?>">
                            <a data-ga-event="{ec: 'customer', ea: 'main_menu_clicked',el: 'vacancy'}" href="/projects/?kind=4" class="b-primary-menu-clause-link" title="Список вакансий для фрилансеров">Вакансии</a>
                        </li>
                        <li class="b-primary-menu-clause b-primary-menu-search-clause <?= isCurrentPage('search','b-primary-menu-current-clause','') ?>">
                            <a data-ga-event="{ec: 'customer', ea: 'main_menu_clicked',el: 'search'}" href="/search/" class="b-primary-menu-clause-link" title="Поиск по сайту">Поиск</a>
                        </li>
                        <li class="b-primary-menu-clause b-primary-menu-additional-clause">
                            <div class="b-dropdown b-primary-menu-dropdown" data-dropdown="true" data-dropdown-descriptor="primary-menu">
                                <a href="#" class="b-dropdown-opener" data-dropdown-opener="true" title="Навигация">Еще</a>
                                <div class="b-dropdown-concealment g-hidden" data-dropdown-concealment="true">
                                    <ul class="b-dropdown-concealment-options">
                                        <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-tasks-clause<?php if ($projects_active) { ?> b-dropdown-concealment-options-current-clause<?php } ?>">
                                            <a data-ga-event="{ec: 'customer', ea: 'main_menu_clicked',el: 'projects'}" href="/projects/" class="b-dropdown-concealment-options-clause-link" title="Список проектов для фрилансеров">Работа</a>
                                        </li>
                                        <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-employees-clause<?php if ($grey_catalog) { ?> b-dropdown-concealment-options-current-clause<?php } ?>">
                                            <a data-ga-event="{ec: 'customer', ea: 'main_menu_clicked',el: 'freelancers'}" href="/freelancers/" class="b-dropdown-concealment-options-clause-link" title="Каталог фрилансеров">Фрилансеры</a>
                                        </li>
                                        <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-services-clause <?= isCurrentPage('tu','b-dropdown-concealment-options-current-clause','') ?>">
                                            <a data-ga-event="{ec: 'customer', ea: 'main_menu_clicked',el: 'tu'}" href="/tu/" class="b-dropdown-concealment-options-clause-link" title="Каталог услуг фрилансеров">Услуги</a>
                                        </li>
                                        <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-competitions-clause<?php if ($konkurs_active) { ?> b-dropdown-concealment-options-current-clause<?php } ?>">
                                            <a data-ga-event="{ec: 'customer', ea: 'main_menu_clicked',el: 'konkurs'}" href="/konkurs/" class="b-dropdown-concealment-options-clause-link" title="Список конкурсов для фрилансеров">Конкурсы</a>
                                        </li>
                                        <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-vacancy-clause<?php if ($vacancy_active) {?> b-dropdown-concealment-options-current-clause<?php }?>">
                                            <a data-ga-event="{ec: 'customer', ea: 'main_menu_clicked',el: 'vacancy'}" href="/projects/?kind=4" class="b-dropdown-concealment-options-clause-link" title="Список вакансий для фрилансеров">Вакансии</a>
                                        </li>
                                        <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-search-clause">
                                            <a data-ga-event="{ec: 'customer', ea: 'main_menu_clicked',el: 'search'}" href="/search/" class="b-dropdown-concealment-options-clause-link" title="Поиск по сайту">Поиск</a>
                                        </li>
                                        <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-communities-clause <?= isCurrentPage('commune','b-dropdown-concealment-options-current-clause','') ?>">
                                            <a data-ga-event="{ec: 'customer', ea: 'main_menu_clicked',el: 'commune'}" href="/commune/" class="b-dropdown-concealment-options-clause-link" title="Список сообществ">Сообщества</a>
                                        </li>
                                        <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-reclam-clause">
                                            <a data-ga-event="{ec: 'customer', ea: 'main_menu_clicked',el: 'promo_adv'}" href="/promo/adv/" class="b-dropdown-concealment-options-clause-link" title="Реклама на сайте">Реклама на сайте</a>
                                        </li>
                                        <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-faq-clause">
                                            <noindex><a data-ga-event="{ec: 'customer', ea: 'main_menu_clicked',el: 'help'}" onmouseover="UE.Popin.preload();" onclick="UE.Popin.show(); return false;" rel="nofollow" target="_blank" href="https://feedback.fl.ru/" class="b-dropdown-concealment-options-clause-link" title="Помощь">Помощь</a></noindex>
                                        </li>
                                        <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-faq-clause">
                                           <noindex><a data-ga-event="{ec: 'customer', ea: 'main_menu_clicked',el: 'promo_mbm'}" rel="nofollow" target="_blank" href="/promo/mbm/" class="b-dropdown-concealment-options-clause-link" title="Малый бизнес Москвы">Малый бизнес Москвы</a></noindex>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <ul class="b-primary-menu">
                        <li class="b-primary-menu-clause b-primary-menu-faq-clause">
                            <noindex><a rel="nofollow" target="_blank" href="https://feedback.fl.ru/" class="b-primary-menu-clause-link" title="Помощь">Помощь</a></noindex>
                        </li>
                    </ul>

                    <?php  
                        //Форма смены аккаунта
                        require_once("personal_au_form.php"); 
                    ?>

                    <ul class="b-user-menu" data-antiuser="false">
                        <li class="b-user-menu-clause b-user-menu-messages-clause <?= isCurrentPage('contacts','b-user-menu-current-clause','') ?>">
                            <a href="/contacts/" class="b-user-menu-clause-link" title="<?= $tip_msg['tip'] ?>">
                                Сообщения
                                <?= view_event_count_format($tip_msg['count']) ?>
                            </a>
                        </li>
                        <li class="b-user-menu-clause b-user-menu-tasks-clause <?= isCurrentPage(array(3 => 'projects'),'b-user-menu-current-clause','') ?>">
                            <a href="<?= $tip_prj['link'] ?>" class="b-user-menu-clause-link" title="<?= $tip_prj['tip'] ?>">
                                Проекты
                                <?= view_event_count_format($tip_prj['count']) ?>
                            </a>
                        </li>
                        <?php if(isset($tip_tu)): ?>
                        <li class="b-user-menu-clause b-user-menu-orders-clause <?= isCurrentPage(array(2 => 'tu-orders'),'b-user-menu-current-clause','') ?> <?= isCurrentPage(array('tu','order'),'b-user-menu-current-clause','') ?>">
                            <a href="<?= $tip_tu['link'] ?>" class="b-user-menu-clause-link" title="<?= $tip_tu['tip'] ?>">
                                Заказы
                                <?= view_event_count_format($tip_tu['count']) ?>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php /*
                        <li class="b-user-menu-clause b-user-menu-contracts-clause <?= isCurrentPage(sbr::NEW_TEMPLATE_SBR,'b-user-menu-current-clause','') ?>">
                            <a href="<?= $link_sbr ?>" class="b-user-menu-clause-link" title="<?= $tip_sbr['tip'] ?>">
                                Сделки
                                <?= view_event_count_format($tip_sbr['count']) ?>
                            </a>
                        </li>
                        <li class="b-user-menu-clause b-user-menu-digest-clause <?= isCurrentPage('lenta','b-user-menu-current-clause','') ?>">
                            <a href="/lenta/" class="b-user-menu-clause-link" title="Лента новых работ и сообществ">
                                Лента
                            </a>
                        </li>
                        */ ?>
                        <?
                        
                        //Код ни где не используется?
                        
                        $freeze_info = '';
                        if ($_SESSION['freeze_from']) {
                            if ($_SESSION['is_freezed']) {
                                $freeze_info = "После окончания заморозки PRO будет действовать до " . date('d.m.Y', strtotime($_SESSION['payed_to']));
                            } else {
                                $freeze_info = "С учетом заморозки с ";
                                $freeze_info .= date('d.m.Y', strtotime($_SESSION['freeze_from'])) . " по " . date('d.m.Y', strtotime($_SESSION['freeze_to']));
                            }
                        }
                        ?>

                        <? if ($_SESSION['pro_last']) { ?>
                            <li class="b-user-menu-clause b-user-menu-pro-clause">
                                <a href="/payed-emp/" class="b-user-menu-clause-link" title="Активен <?= pro_days($_SESSION['pro_last']) ?>">PRO</a>
                            </li>
                        <? } elseif ($_SESSION['is_freezed']) { ?>
                            <li class="b-user-menu-clause b-user-menu-pro-clause b-user-menu-frozen-pro-clause">
                                <a href="/payed-emp/" class="b-user-menu-clause-link" title="<?= pro_days($_SESSION['freeze_to'], 'по') ?>">PRO</a>
                            </li>
                        <? } else { ?>
                            <li class="b-user-menu-clause b-user-menu-pro-clause b-user-menu-completed-pro-clause">
                                <a href="/payed-emp/" class="b-user-menu-clause-link" title="Купить аккаунт ПРО">PRO</a>
                            </li>
                        <? } ?>
                        <li class="b-user-menu-clause b-user-menu-additional-clause">
                            <div class="b-dropdown b-user-menu-dropdown" data-dropdown="true" data-dropdown-descriptor="user-menu">

                                <a href="<?= $user_link ?>" class="b-dropdown-opener" data-dropdown-opener="true" title="<?= view_fullname() ?>">
                                    <span class="b-dropdown-opener-picture">
                                        <?= view_avatar($_SESSION['login'], $_SESSION['photo'], 0, 1, 'b-dropdown-opener-picture-entity') ?>
                                    </span>
                                </a>
                                <div class="b-dropdown-concealment g-hidden" data-dropdown-concealment="true">
                                    <ul class="b-dropdown-concealment-options">
                                        <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-profile-clause">
                                            <a title="Профиль" class="b-dropdown-concealment-options-clause-link" href="<?= $user_link?>">Профиль</a>
                                        </li>                                       
                                        <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-messages-clause">
                                            <a href="/contacts/" class="b-dropdown-concealment-options-clause-link" title="<?= $tip_msg['tip'] ?>">Сообщения</a>
                                        </li>
                                        <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-tasks-clause">
                                            <a href="<?= $tip_prj['link'] ?>" class="b-dropdown-concealment-options-clause-link" title="<?= $tip_prj['tip'] ?>">Проекты</a>
                                        </li>
                                        <?php if(isset($tip_tu)): ?>
                                        <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-orders-clause">
                                            <a href="<?= $tip_tu['link'] ?>" class="b-dropdown-concealment-options-clause-link" title="<?= $tip_tu['tip'] ?>">Заказы</a>
                                        </li>
                                        <?php endif; ?>
                                        <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-contracts-clause">
                                            <a href="<?= $link_sbr ?>" class="b-dropdown-concealment-options-clause-link" title="<?= $tip_sbr['tip'] ?>">Сделки</a>
                                        </li>
                                        <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-digest-clause">
                                            <a href="/lenta/" class="b-dropdown-concealment-options-clause-link" title="Лента новых работ и сообществ">Лента</a>
                                        </li>
                                        <? if ($_SESSION['pro_last']) { ?>
                                            <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-pro-clause">
                                                <a href="/payed-emp/" class="b-dropdown-concealment-options-clause-link" title="Активен <?= pro_days($_SESSION['pro_last']) ?>">PRO-аккаунт</a>
                                            </li>
                                        <? } elseif ($_SESSION['is_freezed']) { ?>
                                            <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-pro-clause">
                                                <a href="/payed-emp/" class="b-dropdown-concealment-options-clause-link" title="<?= pro_days($_SESSION['freeze_to'], 'по') ?>">PRO-аккаунт</a>
                                            </li>
                                        <? } else { ?>
                                            <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-pro-clause">
                                                <a href="/payed-emp/" class="b-dropdown-concealment-options-clause-link" title="Купить аккаунт ПРО">PRO-аккаунт</a>
                                            </li>
                                        <? } ?>
                                        <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-wallet-clause">
                                            <a href="/bill/history/?period=3" class="b-dropdown-concealment-options-clause-link" title="<?= $account_sum_format ?>">
                                                Счет
                                                <span class="b-txt b-txt_pa b-txt_right_20 b-txt_fs_14 b-txt_lh_40 b-txt_ff_hn b-txt_color_<?php if($account_sum_is_plus): ?>fd6c30<?php else: ?>c10600<?php endif; ?>">
                                                    <?= $account_sum_format ?>
                                                </span>
                                            </a>
                                        </li>
                                        <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-settings-clause">
                                            <a href="<?= $user_link ?>/setup/" class="b-dropdown-concealment-options-clause-link" title="Настройки аккаунта">Настройки</a>
                                        </li>
                                        <?php if (hasPermissions('adm')) { ?>
                                            <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-switch-clause">
                                                <a class="b-dropdown-concealment-options-clause-link" href="/siteadmin/">Админка</a>
                                            </li>
                                        <?php }//if?>
                                        <?php if (hasPermissions('sbr') || hasPermissions('sbr_finance')) { ?>
                                            <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-switch-clause">
                                                <a class="b-dropdown-concealment-options-clause-link" href="/norisk2/?site=admin">Админка БС</a>
                                            </li>
                                            <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-switch-clause">
                                                <a class="b-dropdown-concealment-options-clause-link" href="/siteadmin/reserves/">Админка заказов по БС</a>
                                            </li>                                            
                                        <?php }//if?>
                                            <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-switch-clause">
                                                <a data-toggle-action="antiuser" href="javascript:void(0);" class="b-dropdown-concealment-options-clause-link" title="Перейти в аккаунт фрилансера">Войти фрилансером</a>
                                            </li>
                                            <li class="b-dropdown-concealment-options-clause b-dropdown-concealment-options-exit-clause">
                                                <a href="javascript:void(0);" class="b-dropdown-concealment-options-clause-link" title="Выйти из аккаунта работодателя" onclick="Bar_Ext.logout();">Выйти</a>
                                            </li>
                                    </ul>
                                </div>
                            </div>
                        </li>
                    </ul>

                </section>
            </div>
        </header>

    </div>
</div>
<?= SubBarNotificationHelper::getInstance()->showMessage(); ?>