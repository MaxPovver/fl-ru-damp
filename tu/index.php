<?


//$g_page_id = "0|102";
$rpath="../";

$stretch_page = true;
$showMainDiv  = true;

$css_file = array('/css/block/b-icon/__cat/b-icon__cat.css', '/css/block/b-free-share/b-free-share.css');
$js_file[] = "banned.js";
$js_file[] = "tservices/fineuploader.js";
$js_file[] = "tservices/mootools-elements.from.js";
$js_file[] = "tservices/tservices.js";
$js_file[] = "tservices/tservices_order_auth.js";

$content = "content.php";
$header = "../header.php";
$footer = "../footer.html";

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/yii/tinyyii.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' ); //???
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/city.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/search/sphinxapi.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/tservices/tservices_helper.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/tservices/functions.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/tservices/tservices.php");
require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/models/TServiceOrderModel.php');
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/user_phone.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/seo/SeoTags.php");

const feedbacks_per_page = 5;

session_start();

$tuid = __paramInit('int','tuid',NULL,0);
if(!$tuid) 
{
    //header("Location: /404.php"); 
    //exit;
    
    include ABS_PATH . '/404.php'; 
    exit;
}

//------------------------------------------------------------------------------

//Получаем ТУ
$tservices = new tservices();
$data = $tservices->getCard($tuid, false);

if(!$data)
{
    include ABS_PATH . '/404.php'; 
    exit;
}

//------------------------------------------------------------------------------

//Проверяем уровень доступа
$is_owner = (get_uid() == $data['user_id']);
$is_active = ($data['active'] == 't');
$is_adm = hasPermissions('tservices');
$is_allow = ($is_owner || $is_adm);

if((!($is_owner || $is_adm) && !$is_active) )
{
    include ABS_PATH . '/404.php'; 
    exit;
}

//------------------------------------------------------------------------------

//Если ТУ заблокирована и юзер не владелец и не админ то 404
if(!($is_owner || $is_adm) && $data['is_blocked'] == 't')
{
    $content = 'tpl.blocked.php';
    include ("../template3.php");
    exit;
}

//------------------------------------------------------------------------------

//Получаем инфо о владельце ТУ
$user_obj = new freelancer();
$user_obj->GetUserByUID($data['user_id']);
$uid = $user_obj->uid;

if(!$uid) 
{
    include ABS_PATH . '/404.php'; 
    exit;
}


//------------------------------------------------------------------------------
// Блокируем ТУ для всех остальных если пользователь имеет задолженность

$_debt_info = TServiceOrderModel::model()->isDebt($user_obj->uid);

if( $_debt_info && !$is_owner && !$is_adm && $_debt_info['is_blocked'] == 't')
{
    include ABS_PATH . '/404.php'; 
    exit;
}

//------------------------------------------------------------------------------


//Пользователь скрыл вкладку ТУ
//Доступна только ему и админам

/*
if (substr($user_obj->tabs, 7, 1) == 0 && !(hasPermissions('users'))) 
{
    include ABS_PATH . '/404.php'; 
    exit;
}
*/

/**
 * Оставляем доступной карточку для всех 
 * если вкладка ТУ скрыта на время индексации каталога
 */
if (substr($user_obj->tabs, 7, 1) == 0)
{
    $sphinxClient = new SphinxClient;
    $sphinxClient->SetServer(SEARCHHOST, SEARCHPORT);
    $sphinxClient->SetIDRange($data['id'],$data['id']);
    $queryResult = $sphinxClient->Query("","tservices;delta_tservices");
    $in_catalog = isset($queryResult['matches'][$data['id']]);
    
    if (!$in_catalog && !$is_allow) 
    {
        include ABS_PATH . '/404.php'; 
        exit;        
    }
}

//------------------------------------------------------------------------------

$user_phone_block = user_phone::getInstance()->render(user_phone::PLACE_HEADER);

//------------------------------------------------------------------------------

$_SESSION['tu_ref_uri'] = @$_SESSION['ref_uri'];

//------------------------------------------------------------------------------

//Форматирование кол-ва отзывов
if($data['total_feedbacks'])
{
    $total = intval($data['total_feedbacks']);
    $plus = intval($data['plus_feedbacks']);
    $data['perplus_feedbacks'] = ($plus > 0)? round($plus * 100 / $total) : 0;
}


$feedbacks = $tservices->setPage(feedbacks_per_page)->getFeedbacks($data['id']);
$is_feedbacks_paginator = $data['total_feedbacks'] > count($feedbacks);


//------------------------------------------------------------------------------


//SEO
SeoTags::getInstance()->initTServicesCard($data, $user_obj);

$page_title = SeoTags::getInstance()->getTitle();
$page_descr = SeoTags::getInstance()->getDescription();
$page_keyw = SeoTags::getInstance()->getKeywords();
$canonical_url = $GLOBALS['host'] . tservices_helper::card_link($data['id'],$data['title']);

//------------------------------------------------------------------------------

//Получение текстового наименования города возможной встречи
if($data['is_meet'] === 't')
{
    $city = new city();
    $data['location'] = /*$city->getCountryName($data['city']) . ": " .*/ 'г. ' . $city->getCityName($data['city']);
}


//------------------------------------------------------------------------------


//Виджет попап окошка при заказе услуги
//непоказываем фрилансерам

$tserviceOrderPopup = NULL;
$is_frl = !is_emp() && get_uid(false);

if(!$is_frl)
{
    
    require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/widgets/TServiceOrderPopup.php');
    $tserviceOrderPopup = new TServiceOrderPopup();
    $tserviceOrderPopup->init(array(
        'title' => $data['title'],
        'frl_fullname' => "{$user_obj->uname} {$user_obj->usurname} [{$user_obj->login}]",
        'price' => $data['price'],
        'days' => $data['days'],
        'category_id' => $data['category_id']
    ));
}


//------------------------------------------------------------------------------

if (!$is_owner) {
    //Популярные услуги из этой же категории или пользователя если данная услуга закреплена
    require_once($_SERVER['DOCUMENT_ROOT'] . '/tu/widgets/TServicesPopular.php');
    require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/tservices_binds.php');
    
    $tservicesPopular = new TServicesPopular();
    $tservicesPopularOptions = array(
        'title_css' => 'b-layout__title_padtop_40',
        'item_css' => 'i-pic_port_col4_desktop'
    );
    
    $isBinded = tservices_binds::isBinded($data['id']);
    if ($isBinded) {
        $tservicesPopularOptions['user_id'] = $uid;
        $tservicesPopularOptions['limit'] = 100;
        $tservicesPopularOptions['fullname'] = view_fullname($user_obj);
    }
    
    $tservicesPopular->setOptions($tservicesPopularOptions);
    $tservicesPopular->init($data['category_id'], $data['id']);
}

//------------------------------------------------------------------------------


$inner = 'tpl.card.php';
include ("../template3.php");