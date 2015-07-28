<?php
ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

require_once("classes/config.php");
require_once("classes/log.php");
require_once("classes/multi_log.php");
$log = new log('hourly/'.SERVER.'-%d%m%Y[%H].log', 'w');

$log->writeln('------------ BEGIN hourly (start time: ' . date('d.m.Y H:i:s') . ') -----');

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/smail.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/contacts.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/messages.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sitemap.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/CFile.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stats.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/hh.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stat_collector.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/pay_place.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/rating.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/maintenance.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/search_parser.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/project_exrates.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/static_compress.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/blogs.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/commune.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/spam.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/attachedfiles.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/commune.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/articles.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users_suspicious_contacts.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/FreelancerCatalog.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/annoy.php");


$mail = new smail();
$mail2 = new smail2();
$spam = new spam();
$H = (int)date('H');


/**
 * Чистим счетчик неправильного 
 * ввода пароля для пользователя
 */
annoy::clearRepeatPassByCnt();

//$cfile = new CFile();
//$log->TRACE( $cfile->removeDeleted() );

if ( $H == 0 ) {
    $log->TRACE( $traffic_stat->calculateStatsIp() );
}

// Обновляем карту сайта
// try {
//     $log->TRACE( sitemap::update('blogs') );
// } catch(Exception $e) {
// 	$log->TRACE($e->getMessage());
// }

try {
    $log->TRACE( sitemap::update('projects'));
} catch(Exception $e) {
	$log->TRACE($e->getMessage());
}

try {
    $log->TRACE( sitemap::update('commune') );
} catch (Exception $e) {
    $log->TRACE($e->getMessage());
}

// try {
//     $log->TRACE( sitemap::update('articles'));
// } catch (Exception $e) {
//     $log->TRACE($e->getMessage());
// }

try {
    $log->TRACE( sitemap::update('portfolio') );
} catch (Exception $e) {
    $log->TRACE($e->getMessage());
}

try {
    $log->TRACE( sitemap::update('users') );
} catch (Exception $e) {
    $log->TRACE($e->getMessage());
}

try {
    $log->TRACE( sitemap::update('catalog') );
} catch (Exception $e) {
    $log->TRACE($e->getMessage());
}

try {
    $log->TRACE( sitemap::update('userpages') );
} catch (Exception $e) {
    $log->TRACE($e->getMessage());
}

try {
    $log->TRACE( sitemap::update('tservices') );
} catch (Exception $e) {
    $log->TRACE($e->getMessage());
}

try {
    $log->TRACE( sitemap::generateMainSitemap() );
    $log->TRACE( sitemap::send() );
} catch (Exception $e) {
    $log->TRACE($e->getMessage());
}

// Чистим сессии загруженны, но не использованных файлов
$log->TRACE( attachedfiles::clearOldSessions() );

//------------------------------------------------------------------------------

// Рассылка ПРО о том что ПРО закончится через день
// Вызываем для тех у кого включена опция и для тех у кого нет
// но сообщение в итоге шлем всем одинаковое см getPROEnding
// так как пока автопродление неиспользуется
$log->TRACE( payed::getPROEnding(true, 3));// За 3 дня для тех у кого включено автопродление
$log->TRACE( payed::getPROEnding(true, 1));// За 1 день для тех у кого включено автопродление
$log->TRACE( payed::getPROEnding(false, 3)); // За 3 дня для тех у кого не включено автопродление
$log->TRACE( payed::getPROEnding(false, 1)); // За 1 день для тех у кого не включено автопродление


//@todo: пока отключаем уведомления об автопродлении ПРО так как отключили автопродление
//@todo: тут еще и пытается продлить
//$log->TRACE( payed::checkAutoPRO());

// рассылаем email для тех у кого включено автопродление PRO и он закончится через 1 день
// @todo: пока автопродление не используется
// @todo: кстати непонятно зачем отдельный метод если getPROEnding справяется с этой задачей?
//$log->TRACE( payed::AlertPROEnding() );

//------------------------------------------------------------------------------


// Функция автоподьема проектов если в них в течении 2х дней не было ни одного ответа
$log->TRACE( projects::autoSetTopProject());

// Раз в час пересчитываем счетчики сообществ (пересчитываем только сообщество "Общение")
$log->TRACE( commune::recalcThemesCountCommunes(commune::COMMUNE_BLOGS_ID) );

if(date("H") == 1) {
    $log->TRACE( $mail->SendWarnings() ); // Отправляет предупреждению юзеру о том, что аккаунт ПРО истекает в ближайшие дни.
    $temp = new users;
    $rpath = "";
    $log->TRACE( $temp->DropInactive() );
    // Пишем статистику для админки #0003426
    $log->TRACE( stats::writeGeneralStat() );
    // Пересчет цен работ фрилансеров в портфолио
    $log->TRACE( professions::calcAvgPrices() );
}

if (date("H") == 2) {

	$log->TRACE( $mail->ContestReminder() );
	$log->TRACE( $mail->ContestEndReminder() );

    // отмена не оплаченных заказов
	$log->TRACE(billing::checkOldReserve());
}


//------------------------------------------------------------------------------

/**
 * Уведомления закреплений ТУ
 */
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/tservices_smail.php');
$tservices_smail = new tservices_smail();
$log->TRACE($tservices_smail->remind24hEndBinds());//за сутки
$log->TRACE($tservices_smail->remindBindsUp());//если опустился ниже 4 позиции включительно

//------------------------------------------------------------------------------

//За сутки до завершения срока действия закрепления
$mail->remindFreelancerbindsProlong();

//После того, как закрепление опустилось ниже середины списка закреплений (и в списке больше одного закрепления)
$mail->remindFreelancerbindsUp();


//------------------------------------------------------------------------------

/**
 * Обновление количества пользователей в разделах каталога фрилансеров
 */
$catalog = new FreelancerCatalog();
$log->TRACE($catalog->recalcCounters());

//------------------------------------------------------------------------------

/**
 * Пересчет количества пользователей ТУ в данной категории
 */
if (date("H") == 2) {
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/tservices/tservices_categories.php");
	tservices_categories::ReCalcCategoriesCount();
}

//------------------------------------------------------------------------------

if (date("H") == 6) {
	$log->TRACE( professions::ReCalcProfessionsCount() );
    $hh = new hh();
    $log->TRACE( $hh->delOldFilters() );
    $log->TRACE( $mail->employerHelpInfo() );
}
$log->TRACE( professions::PaidSpecsEndingReminder() );

// ban
$usr=new users();
$log->TRACE( $usr->GetBanTimeout() );

//выкидываем неактивных юзеров
$log->TRACE( $usr->UpdateInactive() );

if (date("H") == 0 || date("H") == 6 || date("H") == 12 || date("H") == 18) {
    // генерация xml для webprof
    $log->TRACE( freelancer::webprofGenerateRss('upload/webprof.xml') );
}

// генерация xml для Яндекс.Работа
$log->TRACE( new_projects::yandexGenerateRss('upload/yandex-office.xml', array(4)) );
$log->TRACE( new_projects::yandexGenerateRss('upload/yandex-project.xml', array(1, 2, 7)) );

// генерация xml для Jooble.ru, indeed и trovit
$projects_for_xml  = new_projects::getProjectsForXml('1 month');
$log->TRACE( new_projects::joobleGenerateRss('upload/jooble.xml', $projects_for_xml) );
$log->TRACE( new_projects::indeedGenerateRss('upload/indeed.xml', $projects_for_xml) );
$log->TRACE( new_projects::trovitGenerateRss('upload/trovit.xml', $projects_for_xml) );

// генерация xml для joobradio
if(date("H")==4) {
    $log->TRACE( new_projects::jobradioGenerateRss('upload/jobradio.xml') );
    
    if ( users_suspicious_contacts::getResetContacts() ) {
        users_suspicious_contacts::resetContacts();
        users_suspicious_contacts::setResetContacts();
    }
}
// генерация xml для careerjet
if(date("H")==23) {
    $log->TRACE( new_projects::careerjetGenerateRss('upload/careerjet.xml') );
}
// генерация xml для adWords
if(date("H")==3) {
    $log->TRACE( new_projects::adWords('upload/adwords.csv') );
}

// сбор статистики.
$scl = new stat_collector();
$log->TRACE( $scl->Run() );
$log->TRACE( $scl->wordsStatRun() );
if(date("H") == 1) {
    // разделение stat_monthly
    $log->TRACE( $scl->stat_monthly_split() );
}


// Отсылаем email тем у кого заканчивается закрепление проекта наверху главной страницы
$log->TRACE( $mail->EndTopDaysPrjSendAlerts() );

if (date("H") == 7){
    $log->TRACE( $mail->sendYdDayRegistry() );
    //$log->TRACE( $mail->SbrReqvAlerts() );
    $log->TRACE( $mail->SbrDeadlineAlert() );
}


// платные места на верху
$pp = new pay_place();
$log->TRACE( $pp->clearOldData() );
$pp = new pay_place(1);
$log->TRACE( $pp->clearOldData() );


if ( date('H') == 6 ) {
    $stc = new static_compress();
    $log->TRACE( $stc->cleaner() );
}

$rating = new rating();
if(date('H') == 1) {
    //$rating = new rating();
    //$log->TRACE( $rating->calcDaily() );
    $log->TRACE( $rating->calcMonthly() );
}
$log->TRACE( $rating->calcDaily() );

// перенесено в /minutly.php
/*if(date('H') >= 0 && date('H') <= 5) { 
    // разморозка ПРО
    $log->TRACE( payed::freezeUpdateProUsers() );
}*/

if(date('H') == 0) {
    
    //Пересчет курсов на основании курсов валют ЦБ
    $log->TRACE( project_exrates::updateCBRates() );
    
    // Уведомление о разбане на сайте
    $log->TRACE( $mail->sendReminderUsersUnBan(1) ); // за 1 день до
    
    // Напоминание о необходимости активирвать аккаунт через два дня после регистрации
    $log->TRACE( $mail->activateAccountNotice() ); //
}

// отправляем уведомления о новых топиках в сообществах.
$log->TRACE( $mail->CommuneNewTopic() );

// Рассылка по базе контактов в /siteadmin/contacts
$log->TRACE( $mail->SendMailToContacts() );


/*
 * Рассылка новый проектов фрилансерам
 * @depricated: перенесена в отдельный крон /hourly_newsletter_frl.php
 *
if((int)date('H') == 1) {
    $log->TRACE( $mail->NewProj2($users) );
}
*/



//------------------------------------------------------------------------------



/*
// Рассылки -------------------------------------
if ( date('d-H') == '26-02' ) {
    // Рассылка PRO работодателям, которые зарегистрировались менее 30 дней назад
    $log->TRACE( $spam->proEmpRegLess30() );
}

if ( date('d-H') == '26-03' ) {
    // Рассылка не PRO работодателям, которые зарегистрировались менее 30 дней назад
    $log->TRACE( $spam->noProEmpRegLess30() );
}

if ( date('d-H') == '27-03' ) {
    // Рассылка фрилансерам, которые зарегистрировались на сайте менее 30 дней назад и не купили никакой ПРО
    $log->TRACE( $spam->frlNotBuyPro() );
}
    
if ( date('d-H') == '27-04' ) {
    // Рассылка фрилансерам, которые купили тестовый ПРО и не купили обычный ПРО в течение месяца
    $log->TRACE( $spam->frlBuyTestPro() );
}
    
if ( date('d-H') == '27-05' ) {
    // Рассылка фрилансерам, которые купили тестовый ПРО и после него только однажды купили обычный
    $log->TRACE( $spam->frlBuyProOnce() );
}

if ( date('d-H') == '26-04' ) {
    // Рассылка работодателям  у которых на счету есть 35+ бонусных FM.
    $log->TRACE( $spam->empBonusFm() );
}

if ( $H == 5 ) {
    // Рассылка фрилансерам, у которых через 2 недели заканчивается про на 6 или 12 месяцев
    $log->TRACE( $spam->frlEndingPro() );
}
    
if ( $H == 6 ) {
    // #0015818: Рассылка работодателям по конкурсам без бюджета
    $log->TRACE( $mail->sendEmpContestWithoutBudget() );
}

if ( date('d-H') == '15-02' ) {
    // Рассылка работодателям активным за 30 дней, но не публиковавшим проектов
    $log->TRACE( $spam->empProNotPubPrj() );
}

if ( date('d-H') == '15-03' ) {
    // Рассылка работодателям активным за 30 дней, но не публиковавшим проектов
    $log->TRACE( $spam->empNoProNotPubPrj() );
}
    
if ( date('d-H') == '15-04' ) {
    // #0015221: Рассылка PRO работодателям опубликовавшим платный проект или конкурс в течение 30 дней
    $log->TRACE( $spam->empProPubPrj30Days() );
}

if ( date('d-H') == '15-05' ) {
    // #0015221: Рассылка не PRO работодателям опубликовавшим платный проект или конкурс в течение 30 дней
    $log->TRACE( $spam->empNoProPubPrj30Days() );
}

if ( date('d-H') == '07-04' ) {
    // Рассылка работодателям купившим рассылку в течение 30 дней
    $log->TRACE( $spam->empProBuyMass30Days() );
}

if ( date('d-H') == '07-05' ) {
    // Рассылка работодателям купившим рассылку в течение 30 дней
    $log->TRACE( $spam->empNoProBuyMass30Days() );
}

if (date('d-H') == '28-03') {
    // спам пользователям с незаполненым профилем
    $pmail = new pmail;
    $log->TRACE( $pmail->withoutProfileFrelancers() );
    $log->TRACE( $pmail->withoutProfileEmployers() );
}

if (date('d-H') == '28-04') {
    // спам неактивным пользователям
    $pmail = new pmail;
    $log->TRACE( $pmail->noActiveFreelancers() );
    $log->TRACE( $pmail->noActiveEmployers() );
}

//-----------------------------------------------
*/
//Сбор поисковых запросов - разбор лога
if (date('H') == 6) {
    $parser = search_parser::factory(1);
    $log->TRACE( $parser->cleanByLimit() );
    $log->TRACE( $parser->parseRaw() );
}

//Сбор поисковых запросов - филтрация лога (все запросы кроме исполнителей и проектов)
if (date('H') == 7) {
    $parser = search_parser::factory(1);
    $log->TRACE( $parser->filterRaw() );
}

//Сбор поисковых запросов - филтрация лога (запросы по юзерам)
if (date('H') == 8) {
    $parser = search_parser::factory(1);
    $log->TRACE( $parser->filterRaw('users') );
}

//Сбор поисковых запросов - филтрация лога (запросы по проектам)
if (date('H') == 9) {
    $parser = search_parser::factory(1);
    $log->TRACE( $parser->filterRaw('projects') );
    $log->TRACE( $parser->cleanup() );
}

//Очистка "мусора" создающегося при вставке в визивиг изображений и не сохранении комментария (таблицы commune_attach, file_commune и articles_comments_files, file
if (date('H') == 23) {
    //$log->TRACE( commune::removeWysiwygTrash());
    $log->TRACE( articles::removeWysiwygTrash());
}

// Каждый день первого числа формируем документ ITO за прошлый месяц
/*
if(date('j') == 1 && date('H') == 1) {
    $prevMonth = time() - 3600 * 24 * 2; // Вычитаем два дня на всякий случай
    $log->TRACE( sbr_meta::generateDocITO(array(0 => date('Y-m-01', $prevMonth), 1 => date('Y-m-t', $prevMonth)), false, 'xlsx'));
}
*/

//Очистка логов ПСКБ из базы
/*
if(date('H') == 5) {
    // $log_pskb = new log_pskb();
    // $log->TRACE( $log_pskb->clearCloneData() );
    // $log->TRACE( $log_pskb->packOldData(true) );
}
*/


//////////////////// !!! добавлять НАД этой строкой !!! ///////////////////////

$mt = new Maintenance();
if ( in_array($H, array(2, 9, 21)) ) {
    $log->TRACE( $mt->analyze('master', Maintenance::MODE_VACUUM) );
} else if ( in_array($H, array(3, 6, 10, 13, 16, 19, 22)) ) {
    $log->TRACE( $mt->analyze('master', Maintenance::MODE_ANALYZE) );
}


$log->writeln('------------ END hourly    (total time: ' . $log->getTotalTime() . ') ---------------');