<?php

require '../classes/stdf.php';

$login = $_GET['login'];
$row = $DB->row("SELECT * FROM users WHERE login = ?", $login);
if ( empty($row) ) {
    echo "Пользователя {$login} не существует";
} else {
    $DB->query("UPDATE users SET is_verify = NOT is_verify WHERE login = ?", $login);
    echo "Пользователь {$login} теперь " . (($row['is_verify'] == 't')? 'НЕ ': '') . "верифицирован";
}