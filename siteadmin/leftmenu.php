<?php 
// !!! нумерация подсветки пунктов меню: заняты номера 1-24 и 100

if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; } 
  require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/masssending.php");
  require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/paid_advices.php");
  $paid_advice = new paid_advices();
  $stat_advice = $paid_advice->getStatAdvices();
  $mass_sending_new_cnt = masssending::GetCount(masssending::OM_NEW);
  $s = 'style="color: #666;"';
  $c = 'class="blue"';
  
  // количество жалоб о спаме
  require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/messages_spam.php' );
  $nMessagesSpamCount = messages_spam::getSpamCount();
  
  // количество жалоб на проекты
  if ( !isset($nComplainProjectsCount) ) {
      require_once $_SERVER['DOCUMENT_ROOT'].'/classes/projects.php';
      $nComplainProjectsCount = projects::GetComplainPrjsCount();
  }
?>

<div class="admin-menu">

    <h3>Модераторская</h3>
    <?php  

    if ( !isset($aPermissions) ) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/permissions.php");
        $aPermissions = permissions::getUserPermissions( get_uid(false) );
    }

    foreach ( $aPermissions as $sPermission ) {
        $sVar  = 'bHas' . ucfirst( $sPermission );
        $$sVar = true;
    }

    ?>
    <?php if ( $bHasAll || $bHasAdm ) { ?>

        <?php if ( $bHasAll || $bHasUsers || $bHasProjects || $bHasBlogs || $bHasCommunes ) { ?>
        - Действия<br/>
        <?php if ( $bHasAll || $bHasUsers || $bHasProjects || $bHasBlogs || $bHasCommunes ) { ?>
        -- <a <?=($menu_item == 1 ? $s : $c)?> href="/siteadmin/admin_log/?site=log">Лента действий</a><br/>
        <?php } ?>
        <?php if ( $bHasAll || $bHasUsers ) { ?>
        -- <a <?=($menu_item == 2 ? $s : $c)?> href="/siteadmin/admin_log/?site=user">Нарушители</a><br/>
        <?php } ?>
        <?php if ( $bHasAll || $bHasProjects ) { ?>
        -- <a <?=($menu_item == 3 ? $s : $c)?> href="/siteadmin/admin_log/?site=proj">Проекты и конкурсы</a><br/>
        <?php } ?>
        <?php } ?>

        <?php if ( $bHasAll || $bHasUsers ) { ?>
        <br/>- <a <?=($menu_item == 4 ? $s : $c)?> href="/siteadmin/user_search/">Пользователи</a><br/>
        <? } ?>
        <?php if ( $bHasAll || $bHasGrayip ) { ?>
        -- <a <?=($menu_item == 5 ? $s : $c)?> href="/siteadmin/gray_ip">Серый список IP</a><br/>
        <? } ?>
        <?php if ( $bHasAll || $bHasUsers ) { ?>
        -- <a href="/siteadmin/ban-razban/?mode=users" class="blue">Пользователи</a><br/>
        <? } ?>
        <?php if ( $bHasAll || $bHasSuspicioususers ) { ?>
        -- <a href="/siteadmin/suspicious-users/" class="blue">Подозрительные пользователи<? $countSuspiciousUsers=users::GetCountSuspiciousUsers(); echo ($countSuspiciousUsers?" ({$countSuspiciousUsers})":'') ?></a><br/>
        <? } ?>
        <?php if ( $bHasAll || $bHasSuspiciousip ) { ?>
        -- <a href="/siteadmin/suspicious-ip/" class="blue">Подозрительные IP</a><br/>
        <? } ?>
        <?php if ( $bHasAll || $bHasUsers ) { ?>
        -- <a href="/siteadmin/users/" class="blue">Пользователи (вся база)</a><br/>
        <? } ?>
        <?php if ( $bHasAll || $bHasUnreadsmsg ) { ?>
        -- <a href="/siteadmin/unreads/" class="blue">Непрочитанные сообщения</a><br/>
        <? } ?>
        <?php if ( $bHasAll || $bHasUserphone ) { ?>
        -- <a href="/siteadmin/user_phone/" class="blue">Мобильные телефоны (финансы)</a><br/>
        <? } ?>

        <?php if ( $bHasAll || $bHasProjects || $bHasUsers ) { ?>
        <br/>- Жалобы<br/>
        <?php if ( $bHasAll || $bHasProjects ) { ?>
        -- <a <?=($menu_item == 12  ? $s : $c)?> href="/siteadmin/ban-razban/?mode=complain">Жалобы на проекты<?=( !empty($nComplainProjectsCount) ? " ($nComplainProjectsCount)" : '' )?></a><br/>
        -- <a <?=($menu_item == 23  ? $s : $c)?> href="/siteadmin/ban-razban/?mode=complain_types">Типы жалоб на проекты</a><br/>
        <?php } ?>
        <?php if ( $bHasAll || $bHasUsers ) { ?>
        -- <a <?=($menu_item == 11 ? $s : $c)?> href="/siteadmin/messages_spam">Жалобы на спам<?=( !empty($nMessagesSpamCount) ? " ($nMessagesSpamCount)" : '' )?></a><br/>
        -- <a <?=($menu_item == 20 ? $s : $c)?> href="/siteadmin/messages_archive_spam/">Архив жалоб на спам</a><br/>
        -- <a <?=($menu_item == 24  ? $s : $c)?> href="/siteadmin/complaints_stats/">Статистика жалоб</a><br/>
        <?php } ?>
        <?php } ?>
        
        <?php if ( $bHasAll || $bHasUsers || $bHasProjects || $bHasBlogs || $bHasCommunes  || $bHasArticles ) { ?>
        <br/>- Пользовательский контент<br/>
        -- <a <?=($menu_item == 15 ? $s : $c)?> href="/siteadmin/user_content/?site=choose">Выбрать поток</a><br/>
            <?php if ( $bHasAll || $bHasUsers ) { ?>
        -- <a <?=($menu_item == 18 ? $s : $c)?> href="/siteadmin/user_content/?site=blocked">Заблокированные</a><br/>
            <?php } ?>
            <?php if ( $bHasAll ) { ?>
        -- <a <?=($menu_item == 16 ? $s : $c)?> href="/siteadmin/user_content/?site=shifts">Смены</a><br/>
        -- <a <?=($menu_item == 17 ? $s : $c)?> href="/siteadmin/user_content/?site=streams">Настройка потоков</a><br/>
        -- <a <?=($menu_item == 14 ? $s : $c)?> href="/siteadmin/stop_words">Стоп-слова</a><br/>
            <?php } ?>
        <?php } ?>
        
        <br/>
    <?php } ?>
       
    <? if (hasPermissions('communes')) { ?>- <a href="/siteadmin/ban-razban/?mode=commune" class="blue">Сообщества</a><br/><? } ?>
    <br/>

    
    <h3>Администрирование</h3>
    <? if ( $bHasAll || $bHasChangelogin ) { ?>- <a href="/siteadmin/login/" class="blue">Изменение логина</a><br/><? } ?>
    <? if ( $bHasAll || $bHasUsers || $bHasProjects || $bHasBlogs || $bHasCommunes ) { ?>- <a href="/siteadmin/proj_reasons/" class="blue">Причины действий мод.</a><br/><br/><? } ?>
    
    <?php if ($bHasAll) { ?>- <a href="/siteadmin/promo_codes/" class="blue">Промо-коды</a><br/><br/><?php } ?>
    
    <?php if (hasPermissions('adm') && hasPermissions('meta')) { ?>
    - SEO-данные<br/>
    -- <a href="/siteadmin/seo/">Мета-теги разделов</a><br/><br/>
    <?php } ?>

    <?if(hasPermissions('adm') && hasPermissions('ratinglog')){?>
    - <a href="/siteadmin/rating_log/" class="blue">Рейтинг логи</a><br/>
    <?}?>
    
    <? if (hasPermissions('adm') && hasPermissions('permissions')) { ?>
    <br/>- Права доступа<br/>
    -- <a href="/siteadmin/permissions/?action=group_list" class="blue">Группы</a><br/>
    -- <a href="/siteadmin/permissions/?action=user_list" class="blue">Пользователи</a><br/>
    <? } ?>
    
    <? if (hasPermissions('adm') && (hasPermissions('sbr') || hasPermissions('sbr_finance') || hasPermissions('tmppayments') )) { ?>
	  <br/>- Документооборот (СБР)<br/>
    <? } ?>
      
    <? if (hasPermissions('adm') && (hasPermissions('sbr') || hasPermissions('sbr_finance')  )) { ?>
        --- <a href="/siteadmin/norisk2/?site=docsflow&scheme=1" class="<?=htmlspecialchars($_GET['site'])==docsflow&&$_GET['scheme']==1 ? 'inherit' : 'blue'?>">Агент</a><br/>
        --- <a href="/siteadmin/norisk2/?site=docsflow&scheme=2" class="<?=htmlspecialchars($_GET['site'])==docsflow&&$_GET['scheme']==2 ? 'inherit' : 'blue'?>">Подряд</a><br/>
        --- <a href="/siteadmin/norisk2/?site=docsflow&scheme=0" class="<?=htmlspecialchars($_GET['site'])==docsflow&&!$_GET['scheme'] ? ' inherit' : 'blue'?>">Все</a><br/>
        --- <a href="/siteadmin/norisk2/?site=stat" class="<?=htmlspecialchars($_GET['site'])=='stat' ? 'inherit' : 'blue'?>">Статистика</a><br/>
        --- <a href="/siteadmin/norisk2/?site=arbitrage" class="<?=htmlspecialchars($_GET['site'])=='arbitrage' ? 'inherit' : 'blue'?>">Арбитраж</a><br/>
        --- <a href="/siteadmin/norisk2/?site=docsflow&scheme=-1" class="<?=htmlspecialchars($_GET['site'])==docsflow&&$_GET['scheme']==-1 ? ' inherit' : 'blue'?>">Архив</a><br/>
        --- <a href="/siteadmin/norisk2/?site=1c" class="<?=htmlspecialchars($_GET['site'])=='1c' ? 'inherit' : 'blue'?>">Экспорт в CSV</a><br/>
        --- <a href="/siteadmin/sbr_ito/">ИТО</a><br/>
        --- <a href="/siteadmin/norisk2/?site=invoice" class="<?=htmlspecialchars($_GET['site'])=='invoice' ? 'inherit' : 'blue'?>">Акты и Счет-фактуры</a><br/>
    <? } ?>

    <? if (hasPermissions('adm') && (hasPermissions('sbr') || hasPermissions('sbr_finance') || hasPermissions('tmppayments') )) { ?>
      -- <a href="/siteadmin/sbr_stat/" class="blue">Статистика по СБР</a><br/>
    <? } ?>
    <? if (hasPermissions('adm') && (hasPermissions('sbr') || hasPermissions('sbr_finance') )) { ?>
      -- <a href="/siteadmin/sbr_reestr" class="blue">Реестры для 1С</a><br/>
      -- <a href="/siteadmin/sbr_reestr?action=import" class="blue">Реестры для сайта</a><br/>
	  <br/>
	<? } ?>
	
	<? if(hasPermissions('adm') && hasPermissions('teamfl')) {?>
	  - <a href="/siteadmin/team/" class="blue">Команда Free-lance.ru</a><br/><br/>
    <? } ?>
    <? if (hasPermissions('communes')) { ?>
      - <a href="/siteadmin/commune/" class="blue">Сообщества</a><br/><br/>
    <? } ?>

	<? if (hasPermissions('adminspam')) { ?>
	- <a href="/siteadmin/admin/" class="blue">Администрация (спам)</a><br/>
    <? } ?>
    <? if (hasPermissions('mailer')) { ?>
    - <a href="/siteadmin/mailer/" class="blue">Новая рассылка</a><br/><br/>
    <? } ?>
    <? if (hasPermissions('stats') || hasPermissions('tmppayments')) { ?>
	- <a href="/siteadmin/stats/" class="blue">Статистика</a><br/><br/>
	<? } ?>

	<? if (hasPermissions('masssending')) { ?>		
	- <a href="/siteadmin/masssending/" class="blue">Заявки на рассылку по разделам<?=($mass_sending_new_cnt ? " ($mass_sending_new_cnt)" : '')?></a><br/><br/>
	<? } ?>

	<? if (hasPermissions('adm') && hasPermissions('seo')) { ?>
	- <a href="/siteadmin/search_kwords/" class="blue">Поиск по сайту</a><br/><br/>
	<? } ?>
	
    <? if (hasPermissions('adm')  && (hasPermissions('statsaccounts') || hasPermissions('tmppayments')) ) { ?>
	- <a href="/siteadmin/account/" class="blue">Статистика (счета)</a><br/>
    <? } ?>
    <? if (hasPermissions('adm')  && hasPermissions('ouraccounts')) { ?>
	- <a href="/siteadmin/staff/" class="blue">Свои аккаунты</a><br/><br/>
	<? } ?>
	

<? if ($bHasAll || $bHasBank) { ?>
- Безнал<br/>
<? } ?>	
<? if ($bHasAll || $bHasBankalpha) { ?>
-- <a href="/siteadmin/alpha/" class="blue">альфа-банк</a><br/>
<? } ?> 
<? if ($bHasAll || $bHasPayments) { ?>
-- <a href="/siteadmin/billinvoices/" class="blue">Заказчики пополнение ЛС</a><br/>
<? } ?>
<? if ($bHasAll || $bHasBank || $bHasPayments || $bHasBankalpha) { ?>
<br/>
<? } ?>


<? if ($bHasAll || $bHasPayservices) { ?>
- <a href="/siteadmin/rating/" class="blue">Рейтинг</a><br/>
<br/>
<? } ?>

<? if ($bHasAll || $bHasAdvstat) { ?>
- Рекламная ст-ка<br/>
-- <a href="/siteadmin/ban_promo/" class="blue">Промо баннеры</a><br/>
<br/>
<? } ?>


<? if ($bHasAll || $bHasLetters) { ?>
- <a href="/siteadmin/letters/" class="blue">Корреспонденция</a><br/>
-- <a href="/siteadmin/letters/?mode=company" class="blue">Стороны</a><br/>
-- <a href="/siteadmin/letters/?mode=templates" class="blue">Шаблоны</a><br/>
<br/>
<? } ?>


<? if ($bHasAll || $bHasOffdocuments) { ?>
<a href="/siteadmin/davupload/?mode=files" class="blue">Загрузка файлов на DAV</a><br/>
<br/>
<? }//if?>


<? if ($bHasAll || $bHasTservices) { ?>
- Типовые услуги<br/>
-- <a href="/siteadmin/tservices/?mode=orders" class="blue">Заказы ТУ</a><br/>
<br/>
<? } ?>

<? if ($bHasAll || $bHasNewsletter ) { ?>
- <a href="/siteadmin/newsletter/" class="blue">Баннеры для ежедневной рассылки о новых проектах</a>
<br/>
<? } ?>

<? if ($bHasAll || $bHasUsers ) { ?>
<br/>
- <a href="/siteadmin/adriver/" class="blue">Справочник ключевых слов для AdRiver</a>
<br/>
<? } ?>

</div>