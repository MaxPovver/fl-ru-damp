<?php

/**
 * Проверка и обработка данных верификации
 */

//Если пришли от WM отключаем проверку CSRF  
if (isset($_POST['WmLogin_WMID'])) {
    $allow_fp = true;
    define('NO_CSRF', 1);
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/stdf.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Verification.php';

$uid = get_uid(false);

if ($uid <= 0) {
    header_location_exit('/promo/verification/');
}

$service = __paramInit('string', 'service');
$verification = new Verification;

switch ($service) {
    
    case 'webmoney': 
        if (!$verification->webmoney($uid)) {
            $error = $verification->getError();
            session::setFlashMessage($error, 'verify_error');
        }
        break;
    
        
    //@todo: можно перенести из income обработку ЯД верификации    

    default:
       header_location_exit('/promo/verification/');
}

?>
<html>
    <body>
        <script type="text/javascript">
            window.close();
        </script>
    </body>
</html>