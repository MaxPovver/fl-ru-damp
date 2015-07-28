<?php
require_once(realpath(__DIR__.'/../classes/stdf.php'));

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    logout();
}

header('Location: /');
