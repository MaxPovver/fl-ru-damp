<?php

/* 
 * Шаблон письма рассылки mailer
 */

$unsubscribe_url = $GLOBALS['host']."/unsubscribe/?type=mailer&ukey=%UNSUBSCRIBE_KEY%" . $utm;
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
    </head>
<body style="padding-bottom: 20px;" marginwidth="0" marginheight="0" bottommargin="0" topmargin="0" rightmargin="0" leftmargin="0">
    <font color="#7e7e7e" size="1" face="arial">
        <center>
            Чтобы не пропустить ни одного письма от команды <a style="color:#006ed6" target="_blank" href="<?=$GLOBALS['host']?>">FL.ru</a>, добавьте наш адрес no_reply@free-lance.ru в вашу адресную книгу. 
            <a href="https://feedback.fl.ru/topic/532678-instruktsiya-po-dobavleniyu-email-adresa-flru-v-spisok-kontaktov/">Инструкция</a>
        </center>
    </font>
    <br />
    <?=$message?>
    <br />
    <font color="#7e7e7e" size="1" face="arial">
        <center>
            Вы получили это письмо, т.к. зарегистрированы на сайте <a style="color:#006ed6" target="_blank" href="<?=$GLOBALS['host']?>">FL.ru</a>.
            Вы можете <a style="color:#006ed6" target="_blank" href="<?=$unsubscribe_url?>">отписаться от рассылки</a> и больше не получать подобные письма.
        </center>
    </font>
</body>
</html>