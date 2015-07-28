<?php

define('NO_CSRF', 1);

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/stdf.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Verification.php';

$uid = get_uid(false);

if ( $uid ) {
    
    if ( preg_match('/^[-_0-9a-zA-Z]+$/', $_GET['code']) ) {
        $verification = new verification;
        if ( !$verification->ffCommit($uid, $_GET['code']) ) {
            $error = $verification->error;
        }
    } else {
        $error = 'Произошла ошибка при работе с сервисом FF.RU (no code).';
    }
    
} else {

    $error = 'Вы не авторизованы';
    
}

if ( empty($error) && $verification->is_pro ) {
    $_SESSION['verifyStatus'] = array( 'status' => 1 );
    $_SESSION['is_verify'] = 't';
} elseif($error) {
    $_SESSION['verifyStatus'] = array( 'status' => 0, 'text' => $error);
}

?><html>
<head>
    <title>Верификация через сервис FF.RU</title>
</head>
<body>
    <script type="text/javascript">
        if ( window.opener ) {
            <? if($verification->is_pro) { ?>
            window.opener.location = '/promo/verification/?service=ff&done';
            <? } else { //if?>
            window.opener.location = '/bill/orders/';
            <? }//else?>
            window.close();
        } else {
            window.location = '/promo/verification/?service=ff&done';
        }
    </script>
</body>
</html>
        

