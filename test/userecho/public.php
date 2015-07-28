<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/userecho.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/messages.php");


/* 
 * Публикуем запись на UserEcho
 */

$userEcho = new UserEcho();
$result = $userEcho->newTopicComplain('Из теста', 'Не важно');

echo "<p>Результат <strong>newTopic</strong>:</p>";
echo '<pre>';
print_r($result);
echo '</pre>';