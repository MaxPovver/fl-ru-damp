<?php
/**
 * Уведомление работодателям
 * */
ini_set('max_execution_time', '0');
ini_set('memory_limit', '512M');

require_once '../classes/stdf.php';
require_once '../classes/memBuff.php';
require_once '../classes/smtp.php';
require_once '../classes/users.php';
/**
 * Логин пользователя от кого осуществляется рассылка
 * 
 */
$sender = 'admin';

if ( defined('HTTP_PREFIX') ) {
    $pHttp = str_replace("://", "", HTTP_PREFIX); // Введено с учетом того планируется включение HTTPS на серверах (для писем в ЛС)
} else {
    $pHttp = 'http';
}
$eHost = $GLOBALS['host'];

$eSubject = "Верификация: быстро как никогда";
$eMessage = "<p>Здравствуйте!</p>
<p>Вы можете пройти верификацию на Free-lance.ru нажатием одной кнопки, так как идентифицированы в Веб-кошельке (с номером %PHONE%). Именно поэтому вам не нужно совершать дополнительных действий.
</p><p>Зайдите на <a href='{$eHost}/promo/verification/?utm_source=newsletter4&utm_medium=email&utm_campaign=pscb_verif' target='_blank'>промо-страницу верификации</a> и нажмите кнопку «Через Веб-кошелек». Через несколько минут вы получите статус «Верифицирован». </p>
<p>Напоминаем, что верификация – это высокий рейтинг, безлимитные ответы и спецпроекты для фрилансеров, доверие к вам со стороны других пользователей. Ознакомьтесь с <a href=\"http://feedback.free-lance.ru/article/details/id/1713\" target=\"_blank\">инструкцией по верификации</a> через Веб-кошелек ПСКБ. </p>
<br/>
<p>Команда Free-lance.ru</p>
";

//Пользователи из csv файла
$csv_users = array("jusoft@yandex.ru" => '+71111112222', "milkusik@gmail.com" => '+71111112223', "mywwork@gmail.com" => '+71111112224'//первыe - только для беты !!
,"nvs@pscb.ru" => '79219188356',"kurbatova2@gmail.com" => '79046330950',"ka@pscb.ru" => '79095800040',"leaderdv@mail.ru" => '79052001616',"office@gekko.by" => '375445725555',"nvs@pscb.ru" => '79219188356',"comedie@rambler.ru" => '79034731235',"nikita.terehov@gekko.by" => '375291621010',"grigor007@mail.ru" => '79297166063',"rya-ira@ya.ru" => '79199987714',"martimar@mail.ru" => '79032407467',"ka@pscb.ru" => '70111111111',"den.fitshopspb@gmail.com" => '79062475207',"komp-w@yandex.ru" => '79818930206',"ksiowork@gmail.com" => '79627035793',"n1003@yandex.ru" => '79199880940',"ramina1987@mail.ru" => '79516607065',"info@okospace.com" => '79260150011',"iappstee@gmail.com" => '79189179629',"krylya77@gmail.com" => '79507235075',"creationis@yandex.ru" => '79096440985',"shipiloff@gmail.com" => '79268008183',"lifestyle.91@mail.ru" => '79051365324',"wokkamsk@yandex.ru" => '79192815301',"igeltsov@gmail.com" => '79292192015',"sunway.supply@gmail.com" => '79241121283',"ivgrun@gmail.com" => '79525946082',"mborovkov@gmail.com" => '79200299987',"pevnevv@mail.com" => '79119784081'
);
// ----------------------------------------------------------------------------------------------------------------
// -- Рассылка ----------------------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------------------------------

$cnt = 0;

$mail = new smtp;
$mail->subject = $eSubject;  // заголовок письма
$mail->message = $eMessage; // текст письма
$mail->recipient = ''; // свойство 'получатель' оставляем пустым
$spamid = $mail->send('text/html');
if (!$spamid) die('Failed!');
// с этого момента рассылка создана, но еще никому не отправлена!

$mail->recipient = array();
$i = 0;
//Отправить сообщения
foreach ($csv_users as $email=>$phone) {
    if ( is_email($email) ) {
        $mail->recipient[] = array(
            'email' => $email,
            'extra' => array('PHONE' => $phone)
        );
        $mail->bind($spamid);
        $mail->recipient = array();
        $cnt++;
    }
}

echo "OK. Total: {$cnt} users\n";
