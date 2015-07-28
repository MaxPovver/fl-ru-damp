<?php
require_once("../classes/config.php");
require_once("../classes/log.php");
$server = empty($_GET['server']) ? 'master' : $_GET['server'];
$date   = empty($_GET['date']) ? date('Y-m-d') : $_GET['date'];

if ( !preg_match("/^[a-z]+$/i", $server) ) {
    $server = 'master';
}
if ( !preg_match("/^[-0-9]+$/", $date) ) {
    $date = date('Y-m-d');
}

$file = LOG_DIR."/db/{$server}/{$date}.log";

if ( !file_exists($file) ) {
    $file = "../classes/log/{$server}/{$date}.log"; // ƒл€ просмотра старых логов пробуем старый путь
    if ( !file_exists($file) ) {
        die("Log not exists");
    }
}

$log = file_get_contents($file);

?><html>
<head>
    <title>Log</title>
</head>
<body>
    <pre><?=$log?></pre>
</body>
</html>