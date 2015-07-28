<?php
require_once("classes/config.php");
require_once("classes/payed.php");
require_once("classes/pay_place.php");
require_once("classes/commune.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/user_content.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/wallet/walletAlpha.php');
require_once("classes/log.php");


//#0027582 Каждую минуту обрабатывать запросы на размешение в карусели
pay_place::cronRequest();


// Каждые пол часа обновляем статус рассылок
if(date('i') == 30) {
    require_once("classes/mailer.php");
    $mailer = new mailer();
    $mailer->updateStatusSending();
}

// ночные нестыковки во времени при переходе в следующий день #0021788
if(!in_array((int) date('Hi'), array(2358, 2359))) { 
    payed::UpdateProUsers();
}

//@todo: непонятно для чего? 
//если юзер провисел 10 сек с момента публикации 
//то помечаем его как просмотренный хотя его мог никто и неувидеть!
$pp = new pay_place();
$pp->getDoneShow(0);

$user_content = new user_content();
$user_content->releaseDelayedStreams();
$user_content->getQueueCounts();
$user_content->getStreamsQueueCounts();

if (date('i') % 5 == 0) {
    walletAlpha::checkProgressOrders();
}

// Каждые 20 минут пересчитываем счетчики остальных сообществ
if (date('i') % 20 == 0) {
    commune::recalcThemesCountCommunes(null, commune::COMMUNE_BLOGS_ID);
}

if (date('i') % 15 == 0) {
    // проверка статусов платежей paymaster при возврате
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/pmpay.php");
    $pm = new pmpay();
    if(DEBUG) {
        $pm->setDebugUrl($GLOBALS['host'].'/norisk2/admin/pm-server-test.php');
    } 
    $pm->checkRefund();
}
    
if(SERVER === 'release') {
    
    /*
     * @todo: https://beta.free-lance.ru/mantis/view.php?id=29134#c87337
     * 
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/qiwipay.php");
    $qiwipay = new qiwipay();
    $qiwipay->checkBillsStatus($error);
    */
    
    if (date('i') % 10 == 0) {
        // проверка статусов платежей paymaster
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/pmpay.php");
        $pm = new pmpay();
        $pm->checkInvoiced();
    }
}

// запускается в 0 и 1 минуту каждого часа с начала суток до 5 утра
if(date('i') == 0 && date('H') >= 0 && date('H') <= 5) { 
    $log = new log('minutly/'.SERVER.'-%d%m%Y[%H].log', 'w');
    // разморозка ПРО
    $log->TRACE( payed::freezeUpdateProUsers() );
}

professions::autoProlongSpecs();


