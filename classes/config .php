<?php
if(!isset($_SERVER['REQUEST_METHOD'])) {
    ini_set('error_log', '/var/log/php.log');
}

define('HTTP_PREFIX', isset($_SERVER['HTTP_NGINX_HTTPS']) ? 'https://' : 'http://'); // !!! ‰Îˇ ÍÓÌ‡, pgq Ë Ô. ÔÓ ÛÏÓÎ˜‡ÌË˛ ÔÓ Ë‰ÂÂ ‰ÓÎÊÂÌ ·˚Ú¸ https.
$host = HTTP_PREFIX . "beta.fl.ru";
//$host = HTTP_PREFIX . "beta.fl.ru";
$GLOBALS['neo_host'] = 'https://beta.fl.ru';

$PDA = ($_SERVER['HTTP_HOST'] == 'p.' . str_replace(HTTP_PREFIX, '', $host));
$abs_path = "/var/www/_beta/html/";
if (!$_SERVER['DOCUMENT_ROOT']) $_SERVER['DOCUMENT_ROOT'] = $abs_path;
define("ABS_PATH", $_SERVER['DOCUMENT_ROOT']);
define('FTS_PROJECTS', false);

//  Ó‰ Ò˜ÂÚ˜ËÍ‡ Google Analytics
define('GA_COUNTER_CODE', 'UA-49313708-3');

$pg_db['master']['name'] = "beta";
$pg_db['master']['user'] = "beta";
$pg_db['master']['host'] = "localhost";
$pg_db['master']['pwd'] = "LKUdkjas832lkjsj";
$pg_db['master']['port'] = '5435';//"6432";
$pg_db['master_standby'] = $pg_db['master'];
$pg_db['master_standby']['port'] = 5435;//6433; // !!! ÔÓ˜ËÌËÚ¸

$pg_db['slave1']['name'] = "beta_data1";
$pg_db['slave1']['user'] = "beta";
$pg_db['slave1']['host'] = "localhost";
$pg_db['slave1']['pwd'] = "LKUdkjas832lkjsj";
$pg_db['slave1']['port'] = '5435';//"6432";

$pg_db['slave2']['name'] = "beta_data1";
$pg_db['slave2']['user'] = "beta";
$pg_db['slave2']['host'] = "localhost";
$pg_db['slave2']['pwd'] = "LKUdkjas832lkjsj";
$pg_db['slave2']['port'] = '5435';//"6432";

$pg_db['banner']['name'] = "beta";
$pg_db['banner']['user'] = "beta"; // !!! Á‡‚ÂÒÚË ÓÚ‰ÂÎ¸ÌÛ˛ ·‡ÁÛ.
$pg_db['banner']['host'] = "localhost";
$pg_db['banner']['pwd'] = "LKUdkjas832lkjsj";
$pg_db['banner']['port'] = '5435';//"6432";
 
$pg_db['plproxy']['name'] = "beta_data1";
$pg_db['plproxy']['user'] = "beta";
$pg_db['plproxy']['host'] = "localhost";
$pg_db['plproxy']['pwd'] = "LKUdkjas832lkjsj";
$pg_db['plproxy']['port'] = '5435';//"6432";

$pg_db['stat']['name'] = "beta_stat";
$pg_db['stat']['user'] = "beta";
$pg_db['stat']['host'] = "localhost";
$pg_db['stat']['pwd'] = "LKUdkjas832lkjsj";
$pg_db['stat']['port'] = '5435';//"6432";

$pg_db['spam']['name'] = "beta_proxy";
$pg_db['spam']['user'] = "beta";
$pg_db['spam']['host'] = "localhost";
$pg_db['spam']['pwd'] = "LKUdkjas832lkjsj";
$pg_db['spam']['port'] = '5435';//"6432";

$pg_db['slave'] = $pg_db['master'];

$pg_db_standby_defaults = array(
    'master' => array(
        1 => 0x00000004|0x00000001, // DB::STBY_NOAUTH|DB::STBY_CACHED,
        2 => 0x00000002|0x00000008, // DB::STBY_NOACT|DB::STBY_NOSHELL,
        3 => 10,
        4 => 1,
        5 => false,
    ),
);

define("SEARCHHOST", "localhost");
define("SEARCHPORT", 3312);

$dbmyname = "freelance";
$dbmyuser = "freelance";
$dbmypwd = "HLK3n4xuRG";
$dbmyhost = "localhost";
$domain4cookie = ".beta.fl.ru";

$memcachedServers = array("localhost");
$memcachedSessionServer = 'localhost';
$memcachedBannersServers = array('localhost');
define ("USE_MEMCACHED", true);


//  ÓÌÙË„ WebDav
$WDCS = array (
  1 => array (
    'server'=>'localhost', 
    'prefix'=>HTTP_PREFIX.'dav.beta.fl.ru',//'betadav.free-lance.ru', //dav.beta.fl.ru 
    'port'=>81, 
    'user'=>'DAV', 
    'pass'=>'x2GUJoOQ', 
    'debug'=>false),
);
$_wdc = $WDCS[rand(1, count($WDCS))];
define('WDCPREFIX', $_wdc['prefix']);
define('WDCPREFIX_LOCAL', 'http://' . $_wdc['server'].':'.$_wdc['port']); // ÚÛÚ ÔÓÍ‡ ÚÓÎ¸ÍÓ "http".
unset($_wdc);


define("CACHE_DBNAME","betacache");
define("CACHE_DBUSER","freelance");
define("CACHE_DBPWD","lfrnc");
define("CACHE_DBHOST","localhost");
define("CACHE_DBPORT","5433");

define('DRWEB_DEAMON', '/usr/bin/drwebdc');
define('DRWEB_HOST', '127.0.0.1');
define('DRWEB_PORT', '3000');
define('DRWEB_STORE', '/var/www/_beta/webdav');

// ¬ÂÒËˇ ÍÎËÂÌÚ‡ FLTray.
define('FLTRAY_VERSION', '3.0.0');
define('FLTRAY_WIN_VERSION',   FLTRAY_VERSION);
define('FLTRAY_MAC_VERSION',   FLTRAY_VERSION);
define('FLTRAY_LINUX_VERSION', FLTRAY_VERSION);

/*
 * –?–º—è —Å–µ—Ä–≤–∞–∫–∞. 
 * –î–ª—è –ª–æ–∫–∞–ª—å–Ω–æ–≥–æ —Ä–∞–∑—Ä–∞–±–æ—Ç—á–∏–∫–æ–≤ - local,
 * –∞–ª—å—Ñ–∞ - alpha,
 * –±–µ—Ç–∞ - beta,
 * —Ä–µ–ª–∏–∑ - release (–≤—Å–µ —Å–µ—Ä–≤–∞–∫–∏ —Ä–µ–ª–∏–∑–∞ —Å —ç—Ç–∏–º –∏–º–µ–Ω–µ–º!)
 */
define("SERVER", 'beta');
define('BASIC_AUTH', "freelance:mRfLjovLToupZM0");

// √Ó‚ÓËÚ Ó ÚÓÏ, ˜ÚÓ webdav ÒË‰ËÚ Ì‡ nginx Ë ‚ÍÎ˛˜ÂÌ create_full_put_path. ¬ Ú‡ÍÓÏ ÒÎÛ˜‡Â ÌÂÓ·ˇÁ‡ÚÂÎ¸ÌÓ ÒÓÁ‰‡‚‡Ú¸ Ó‰ËÚÂÎ¸ÒÍËÂ ‰ËÂÍÚÓËË ‰Îˇ PUT.
define('WD_CREATE_FULL_PUT_PATH', true);


define('COOKIE_PWD_SALT', 'beta*S)oQ2R]@7ZEb!wfv\gy-(ch?7_(Be8[asulw3ht876f1');
define('PRIVATE_PWD_SALT', 'beta7KLjds2093uijsadkjncvs 90--12kJHUSDx.p');


// ﬂ˘ËÍË ‰Îˇ ÚÂÒÚÂÓ‚ (·ÂÁÓÔ‡ÒÌÓÂ ÚÂÒÚËÓ‚‡ÌËÂ Ì‡ ·ÂÚÂ).
// »ÒÔÓÎ¸ÁÛÂÚÒˇ ‚ Ô‡Â define('SERVER', 'beta');
$TESTERS_MAIL = array (
/*
 // emails for feedback test
 'info@free-lance.ru',
 'tester@free-lance.ru',
 'norisk@free-lance.ru',
 'help@free-lance.ru',
 'manager@free-lance.ru',
*/
 // ----
 'orlov@agimind.ru',
 'admin@free-lance.ru',
 'vgavran@gmail.com',
 'v.gavran@gmail.com',
 'vg.avran@gmail.com',
 'vga.vran@gmail.com',
 'vgav.ran@gmail.com',
 'vgavr.an@gmail.com',
 'vgavra.n@gmail.com',
 'v.g.avran@gmail.com',
 'vg.a.vran@gmail.com',
 'vga.v.ran@gmail.com',
 'vgav.r.an@gmail.com',
 'vgavr.a.n@gmail.com',
 'v.ga.vran@gmail.com',
 'v.gav.ran@gmail.com',
 'v.gavr.an@gmail.com',
 'v.gavra.n@gmail.com',
 'vg.av.ran@gmail.com',
 'vg.avr.an@gmail.com',
 'vg.avra.n@gmail.com',
 'vga.vr.an@gmail.com',
 'vga.vra.n@gmail.com',
 'vgavran@gmail.com',
 'v.g.a.vran@gmail.com',
 'vg.a.v.ran@gmail.com',
 'vga.v.r.an@gmail.com',
 'vgav.r.a.n@gmail.com',
 'v.g.a.v.ran@gmail.com',
 'vg.a.v.r.an@gmail.com',
 'vga.v.r.a.n@gmail.com',
 'v.g.a.v.r.a.n@gmail.com',
 'kt@free-lance.ru',
 'orlov@free-lance.ru',
 'yabrus@gmail.com',
 'kazakov@fl.ru',
 'kazakov@free-lance.ru',
 'dezinger@gmail.com',
 'iturbaza@gmail.com',
 'ak_soft@list.ru',
 'kazakov@as.ru',
 'ddezinger@yandex.ru',
 'ddezinger@yandex.ua',
 'ddezinger@yandex.kz'
// 'danil@onyanov.ru',
// 'danyamaster@ya.ru',
// 'danyamaster@yandex.ru',
// 'danyamaster@yandex.ua',
// 'danyamaster@yandex.kz',
// 'onyanov@free-lance.ru',
// 'onyanov@fl.ru'
);

// 0 -- Ò‡ÈÚ ÓÚÍ˚Ú ‰Îˇ ‚ÒÂı. »Ì‡˜Â Ò‡ÈÚ Á‡Í˚Ú Ë Á‰ÂÒ¸ ÔË¯ÂÚÒˇ ÒÔÂˆ. ÍÓ‰, ÍÓÚÓ˚È ËÒÔÓÎ¸ÁÛÂÚÒˇ ‰Îˇ ‰ÓÒÚÛÔ‡ Ì‡ Ò‡ÈÚ.
$__IS_CLOSED = 0;//'asdjfiowe939393930002002';
if( !$__IS_CLOSED && (SERVER==='beta' || SERVER==='alpha' || IS_LOCAL===true) && isset($_GET['IS_CLOSED']) ) {
    $__IS_CLOSED = $_GET['IS_CLOSED'];
}
define('IS_CLOSED', $__IS_CLOSED);
define('IS_CLOSED_UNTIL', '2:46');

define("COMPRESS_STATIC", true);
define('PSKB_TEST_MODE', true);

// “ÓÍÂÌ ‰Îˇ ‰ÓÒÚÛÔ‡ Í MixPanel
define('MIXPANEL_PROJECT_TOKEN', 'ac88c8fcd72b9a0d6deefab083252a99');



//------------------------------------------------------------------------------
// –ù–∞—Å—Ç—Ä–æ–π–∫–∏ —Å–µ—Ä–≤–∏—Å–∞ –±–µ–∫–∞–ø–∞ —Ñ–∞–π–ª–æ–≤ –≤ –æ–±–ª–∞—á–Ω–æ–µ –∑—Ä–∞–Ω–∏–ª–∏—â–µ

/*
$BACKUP_SERVICE = array(
    'active' => true,
    'type' => 'AzureBlob',
    'options' => array(
	//–ø–µ—Ä—Ñ–∏–∫—Å –ø–æ–ª–Ω–æ–≥–æ –ø—É—Ç–∏ –æ—Ç–∫—É–¥–∞ –±—Ä–∞—Ç—å —Ñ–∞–π–ª
        'FilePrefix' => realpath(ABS_PATH . '/../webdav'),//–¥–ª—è –±–µ—Ç—ã –∏ –∞–ª—å—Ñ—ã —É–∫–∞–∑–∞–Ω–Ω–æ–µ –∑–Ω–∞—á–µ–Ω–∏–µ –Ω–æ—Ä–º
            
        //–Ω–∞—Å—Ç—Ä–æ–π–∫–∏ –¥–ª—è —Ö—Ä–∞–Ω–∏–ª–∏—â–∞
        'DefaultEndpointsProtocol' => 'https',
        'AccountName' => 'portalvhdscs9w1rhddm7rf',
        'AccountKey' => 'O7zHtOhoGGLxpoZbgQ01bEiQvrFQwoefrwCBHTYnGv9pqNYOIN4636VygfCU9aRaXWO388R9Vuhj1yKYA/GNMg=='
    )
);
*/
                                                                 
//------------------------------------------------------------------------------
//–°–ø–∏—Å–æ–∫ –∞–¥—Ä–µ—Å—Å–æ–≤ –¥–ª—è —Ä–∞—Å—Å—ã–ª–∫–µ –ø—Ä–µ–¥—É–ø—Ä–µ–∂–¥–µ–Ω–∏—è –æ –≤–æ–∑–º–æ–∂–Ω–æ–º –§—Ä–æ–¥–µ –ø–æ –ë–°

$NOTIFY_FROD_EMAILS = array(
    'kazakov@fl.ru',
    'dezinger@gmail.com',
    'vgavran@gmail.com',
    'kt@fl.ru'
);
    
    
//------------------------------------------------------------------------------
// –Ø–î —Å–µ—Ä–≤–∏—Å –≤—ã–ø–ª–∞—Ç

// –¢–æ–ª—å–∫–æ –¥–ª—è –±–æ—è
//define('YM_PAYOUT_ENCRYPT_CERT_FILE', __DIR__ . '/reserves/data/certnew_Vaan.cer');
//define('YM_PAYOUT_DECRYPT_CERT_FILE', __DIR__ . '/reserves/data/depositresponsegenerator.cer');
//define('YM_PAYOUT_PRIVATE_KEY_FILE',  __DIR__ . '/reserves/data/private.key');
//define('YM_PAYOUT_PASSPHRASE', 'tkaevient2014');


//–¢–æ–ª—å–∫–æ –¥–ª—è –±–µ—Ç–∞/–∞–ª—å—Ñ–∞/–ª–æ–∫–∞–ª–∞
define('YM_PAYOUT_TEST_URL', 'https://freelance:mRfLjovLToupZM0@' . str_replace(HTTP_PREFIX, '', $host) . '/bill/test/ym.php?m=%s');

//------------------------------------------------------------------------------
      
/**
* –ö–ª—é—á API –¥–ª—è USERECHO
*/
define('USERECHO_API_KEY', 'cda5420ca9a94382bae2736392827c98');
        
/**
* API-—Ç–æ–∫–µ–Ω –¥–ª—è USERECHO
*/
define('USERECHO_API_TOKEN', '02d5b17f81f768983bf5f5f12731f52ebd81f173');
          
/**
* PROJECT_KEY –¥–ª—è USERECHO
* */
define('USERECHO_PROJECT_KEY', 'fl');
            
            
//------------------------------------------------------------------------------


define('FACEBOOK_APP_ID', '1417314125245378');
define('FACEBOOK_APP_SECRET', '1c7c22c490eca04c47d44c9db6d845b1'); 

//------------------------------------------------------------------------------ 

define('VK_APP_ID', '4841972');
define('VK_APP_SECRET', 'GWe0QNaEx0QfTr0rPJrB');

//------------------------------------------------------------------------------

define('ODNOKLASSNIKI_APP_ID', '1129975296');
define('ODNOKLASSNIKI_APP_PUBLIC', 'CBAPALFEEBABABABA');
define('ODNOKLASSNIKI_APP_SECRET', '5BA56A4998B9FC0B527BAEB6'); 

//------------------------------------------------------------------------------

//define('BANNER_ADRIVER_SID', 194290); 
define('BANNER_ADRIVER_SID', 204428); 

//------------------------------------------------------------------------------

//–í–µ—Ä–∏—Ñ–∏–∫–∞—Ü–∏—è —á–µ—Ä–µ–∑ WebMoney
define('WM_VERIFY_URL_UD', 'E8A544F8-CE44-4292-AC23-A49E010FF699');
define('WM_VERIFY_AUTHCHECK_WMID', '284917267100');
define('WM_VERIFY_WMID', '362345269260');
define('WM_VERIFY_KEYFILE', ABS_PATH . '/classes/WMXI/keys/362345269260.kwm');
define('WM_VERIFY_KEYPASS', 'xyitebe');

//------------------------------------------------------------------------------
