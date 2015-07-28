<?
require_once("../classes/stdf.php");


$sql = "SELECT id, poll_answers_old 
        FROM draft_blogs WHERE poll_answers_old != '';
        ";

$res = pg_query(DBConnect(),$sql);
if($res) {
    $sql = '';
    while($d=pg_fetch_array($res)) {
        $answers_new = array();
        $answers_old = preg_split("/\|-\|-\|/", $d['poll_answers_old'], -1, PREG_SPLIT_NO_EMPTY);
        if($answers_old) {
            foreach($answers_old as $v) {
                array_push($answers_new, $v);
            }
            $sql .= $DB->parse("UPDATE draft_blogs SET poll_answers = ?au WHERE id = ?i; ", $answers_new, $d['id']);
        }
    }
    if($sql) {
        pg_query(DBConnect(), $sql);
    }
}
?>
