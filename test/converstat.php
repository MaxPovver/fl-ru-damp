<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");


$sql = "select distinct(date(\"from\")) as d from users_ban where date(\"from\") is not null";
$res = pg_query(DBConnect(),$sql);
$d = '';
while($s=pg_fetch_array($res)) {
    $d .= "'".$s['d']."',";
    $sql = "SELECT COUNT(users_ban.id) as b_frl FROM users_ban INNER JOIN users ON users_ban.uid=users.uid WHERE (users.role&'100000')='000000' AND date(\"from\")='".$s['d']."'";
    $bf = @pg_fetch_array(pg_query(DBConnect(),$sql));
    $sql = "SELECT COUNT(users_ban.id) as b_emp FROM users_ban INNER JOIN users ON users_ban.uid=users.uid WHERE (users.role&'100000')='100000' AND date(\"from\")='".$s['d']."'";
    $be = @pg_fetch_array(pg_query(DBConnect(),$sql));
    echo $s['d'].' - '.$bf['b_frl'].' - '.$be['b_emp'].'<br>';
    $sql = "UPDATE stat_data SET b_frl=".((int)$bf['b_frl']).", b_emp=".((int)$be['b_emp'])." WHERE date='".$s['d']."'";
    pg_query(DBConnect(),$sql);
}
$d = preg_replace("/,$/","",$d);
$sql = "UPDATE stat_data SET b_emp=0, b_frl=0 WHERE date NOT IN ($d)";
pg_query(DBConnect(),$sql);
?>
