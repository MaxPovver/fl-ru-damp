<?php

// утилита для простотра данных в сессии пользователя

require_once '../classes/stdf.php';
require_once '../classes/session.php';

$sess = new session;
$s = $sess->get($_GET['login']);
if ($s) {
    $data = $sess->read($s['sid']);
    var_dump($data);
} else {
    die('No session');
}

?>
