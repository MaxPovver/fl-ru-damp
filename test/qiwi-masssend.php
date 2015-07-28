<?php

require_once '../classes/stdf.php';
//require_once '../classes/messages.php';
require_once '../classes/memBuff.php';

$master  = new DB('master');
$plproxy = new DB('plproxy');
//$messages = new messages;

$text = "Уважаемый пользователь,
Поздравляем!

Вы правильно ответили на все вопросы каверзного тест-драйва о QIWI-кошельке и получаете 100 баллов рейтинга в подарок от компании QIWI.

Команда Free-lance.ru благодарит вас за участие в жизни нашего портала. 

Приятной работы, 
Ваш Free-lance.ru";

$users = $master->col("
SELECT
	users.uid, users.login, users.uname, users.usurname
FROM (
	SELECT
		a.user_id, SUM(value)
	FROM
		surveys_users_answers a
	INNER JOIN
		surveys_questions_options o ON a.answer_id = o.id
	GROUP BY
		a.user_id
	HAVING
		SUM(value) = 7
) p
INNER JOIN
	surveys_users u ON u.id = p.user_id AND u.date_end IS NOT NULL
INNER JOIN
	users ON users.uid = u.uid
");


$plproxy->query("SELECT messages_masssend(103, ?a, ?, '{}')", $users, $text);

$memBuff = new memBuff();
$memBuff->flushGroup("msgsCnt");