<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

$l = $_GET['login'];

$sql = "SELECT * FROM users WHERE LOWER(login)=LOWER('$l')";
$q_user = pg_query(DBConnect(),$sql);
if(pg_num_rows($q_user)) {
    $user = pg_fetch_array($q_user);
    $sql = "UPDATE users SET boss_note='' WHERE uid=".$user['uid'];
    pg_query(DBConnect(),$sql);
    $sql = "DELETE FROM notes WHERE to_id=".$user['uid'];
    pg_query(DBConnect(),$sql);
    echo 'User and boss notes deleted';
} else {
    echo 'User not found';
}

?>

