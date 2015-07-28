<?
/**
 * тут можно добавлять небольшие обработчики ajax-запросов с клиента, если xajax по какой-то причине использовать не получается
 * для вызова можно использовать следующий код:
   new Request({
        url: '/ajax.php',
        data: {
            action: 'setSpecSplashViewed',
            u_token_key: _TOKEN_KEY
        }
    }).send();
 */

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
session_start();
$action = __paramInit('string', null, 'action');
    
switch ($action) {
    case 'setSpecSplashViewed':
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/splash_screens.php");
        splash_screens::setViewed(splash_screens::SPLASH_NO_SPEC);
        break;
}

?>
