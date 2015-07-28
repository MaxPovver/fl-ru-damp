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
 * Номер темы в блогах
 */
$theme = 270659; //beta
//$theme = 269322; //localhost
/**
 * Логин пользователя от кого осуществляется рассылка
 * 
 */
$sender = 'admin';

// Выбираем пользователей, ответивших в блоге
$sql = "SELECT DISTINCT (fromuser_id), u.email, u.login, u.uname, u.usurname, usk.key AS ukey FROM blogs_msgs_2013 AS msg
join users AS u ON msg.fromuser_id = u.uid
LEFT JOIN users_subscribe_keys AS usk ON usk.uid = msg.fromuser_id
WHERE thread_id = {$theme} AND msgtext LIKE('%+%')"; //все поставившие + в теме № $theme

if ( defined('HTTP_PREFIX') ) {
    $pHttp = str_replace("://", "", HTTP_PREFIX); // Введено с учетом того планируется включение HTTPS на серверах (для писем в ЛС)
} else {
    $pHttp = 'http';
}
$pHost = str_replace("$pHttp://", "", $GLOBALS['host']);
$eHost = $GLOBALS['host'];

$eSubject = "Тестирование нового сервиса онлайн-консультаций";
$eMessage = "<p>Здравствуйте!</p>
<p>Приглашаем вас принять участие в тестировании нового сервиса онлайн-консультаций Free-lance.ru. Вы получили данное письмо, так как оставили свою заявку в комментариях к обсуждению сервиса.
</p><p>Доступ к прототипу:</p>
<p><a href='http://share.axure.com/prototype/login/3D0N22' target='_blank'>http://share.axure.com/prototype/login/3D0N22</a></p>
<p>Пароль</p>
<p>free-lance.ru</p>
<p>Мы будем рады любым вашим замечаниям и предложениям по данному сервису. Пожалуйста, направляйте свои письма по адресу <a href='mailto:maxim@fl.ru'>maxim@fl.ru</a>.</p>
Приятной работы  с Free-lance.ru!";

// ----------------------------------------------------------------------------------------------------------------
// -- Рассылка ----------------------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------------------------------
$proxy = new DB('plproxy');
$cnt = 0;

$mail = new smtp;
$mail->subject = $eSubject;  // заголовок письма
$mail->message = $eMessage; // текст письма
$mail->recipient = ''; // свойство 'получатель' оставляем пустым
$spamid = $mail->send('text/html');
if (!$spamid) die('Failed!');
// с этого момента рассылка создана, но еще никому не отправлена!
// допустим нам нужно получить список получателей с какого-либо запроса
$i = 0;
$mail->recipient = array();
$res = $DB->query($sql);

//Пользователи из csv файла
$csv_users = array(238010, //первый - только для беты !!
1128473,1155547,1468947,1468976,1468983,1469041,1469059,1469061,1469062,1469133,1469155,1469157,1469301,1469314,1469347,1469367,1469389,1469539,1469576,1469652,1469663,1469717,1469767,1469770,1469782,1469819,1469850,1469934,1469990,1469991,1470361,1470518,1470567,1470597,1470743,1470936,1471064,1471346,1471843,1471903,1471915,1471974,1472267,1472455,1472580,1474276,1474534,1475073,1476128,1476679,1476704,1476968,1477167,1479099,1480387,1480501,1480966,1480971,1481006,1481148,1481157,1481238,1481385,1481589,1481645,1481656,1481769,1481799,1481829,1481871,1481939,1482251,1482442,1482591,1483239,1483363,1484115,1484224,1484319,1486076,1486898,1487747,1487857,1489171,1490071,1491622,1491924,1492225,1493219,1494673,1495484,1495828,1495951,1496263,1496362,1496492,1496532,1496538,1496590,1498101,1499098,1500851,1501011,1501257,1502155,1502634,1502754,1504246,1504312,1505971,1508262,1510246,1511062,1511731,1512033,1518080,1518590,1521419,1521608,1522401,1522801,1523116,1524426,1525618,1528599,1530722,1534576,1534589,1535616,1536919,1537310,1537708,1540578,1543150,1546238,1549018,1549771,1550230,1550604,1553605,1554607,1555649,1559660,1563247,1563591,1563956,1565613,1567125,1568669,1568962,1569262,1569374,1572995,1573080,1577895,1578875,1582796,1589966,1592240,1594618,1595062,1595097,1595401,1597863,1597960,1604859,1607184,1611941,1623084,1627453,1636767,1638180,1638256,1639562,1639931,1640624,1641681,1641916,1642198,1642506,1643543,1643909,1644207,1646079,1647565,1647747,1647790,1648660,1649225,1649707,1649860,1650096,1651141,1652284,1652925,1654841,1654915,1674927);
//Отправить отписавшимся в теме и заодно удалить их из массива данных из csv
while ($row = pg_fetch_assoc($res)) {
	unset( $csv_users[ $row["fromuser_id"] ] );
    if ( strlen($row['ukey']) == 0 ) {
        $row['ukey'] = users::writeUnsubscribeKey($row["uid"]);
    }
    if ( is_email($row['email']) ) {
        $mail->recipient[] = array(
            'email' => $row['email'],
            'extra' => array('first_name' => $row['uname'], 'last_name' => $row['usurname'], 'UNSUBSCRIBE_KEY' => $row['ukey'])
        );
        if (++$i >= 30000) {
            $mail->bind($spamid);
            $mail->recipient = array();
            $i = 0;
        }
    }
    $cnt++;
}
if ($i) {
    $mail->bind($spamid);
    $mail->recipient = array();
}
//Отправить пользователям из файла
$csv_uids = join(",", $csv_users);
$sql = "SELECT u.uid, u.email, u.login, u.uname, u.usurname, usk.key AS ukey FROM  users AS u
LEFT JOIN users_subscribe_keys AS usk ON usk.uid = u.uid
WHERE u.uid IN ({$csv_uids})";
//die($sql);
$i = 0;
$mail->recipient = array();
$res = $DB->query($sql);


while ($row = pg_fetch_assoc($res)) {
    unset( $csv_users[ $row["fromuser_id"] ] );
    if ( strlen($row['ukey']) == 0 ) {
        $row['ukey'] = users::writeUnsubscribeKey($row["uid"]);
    }
    if ( is_email($row['email']) ) {
        $mail->recipient[] = array(
            'email' => $row['email'],
            'extra' => array('first_name' => $row['uname'], 'last_name' => $row['usurname'], 'UNSUBSCRIBE_KEY' => $row['ukey'])
        );
        if (++$i >= 30000) {
            $mail->bind($spamid);
            $mail->recipient = array();
            $i = 0;
        }
    }
    $cnt++;
}
if ($i) {
    $mail->bind($spamid);
    $mail->recipient = array();
}

echo "OK. Total: {$cnt} users\n";
