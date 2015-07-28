<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

if($_GET['updateplain']) {
    $sql = "SELECT * FROM help";
    $res = pg_query(DBConnect(), $sql);
    while($q = @pg_fetch_array($res)) {
        $q['text'] = preg_replace("/'/","\'",$q['text']);
        $sql = "UPDATE help SET text_plain='".strip_tags(preg_replace("/&nbsp;/"," ",$q['text']))."' WHERE id=".$q['id'];
        pg_query(DBConnect(),$sql);
    }
    exit;
}

$ids = array();

$sql = "SELECT * FROM help_categories WHERE name='Q!!!'";
$c = @pg_fetch_array(pg_query(DBConnect(),$sql));
if($c['id']) {
    $sql = "DELETE FROM help WHERE category_id=".$c['id'];
    pg_query(DBConnect(),$sql);
    $sql = "SELECT * FROM faq";
    $res = pg_query(DBConnect(),$sql);
    $n=1;
    while($q = pg_fetch_array($res)) {
        $q['question'] = preg_replace("/'/","\'",$q['question']);
        $q['answer'] = preg_replace("/'/","\'",$q['answer']);
        $sql = "INSERT INTO help(anotation,name,text,num_order,category_id,views,is_draft,text_plain) values('','".$q['question']."','".$q['answer']."',$n,".$c['id'].",0,'f','".strip_tags($q['answer'])."')";
        pg_query(DBConnect(),$sql);
        $sql = "SELECT currval('help_id_seq') as id;";
        $nid = pg_fetch_array(pg_query(DBConnect(),$sql));
        $ids[$q['id']] = $nid['id'];
        $n++;
    }
    // заменяем старые id на новые в тексте
    $sql = "SELECT * FROM help";
    $res = pg_query(DBConnect(),$sql);
    $n = 1;
    while($q = pg_fetch_array($res)) {
        preg_match_all("/id=\d{1,}/",$q['text'],$m);
        if($m) {
            foreach($m[0] as $v) {
                $q['text'] = preg_replace("/$v/","q=".$ids[preg_replace("/id=/","",$v)],$q['text']);
            }
        }
        $q['text'] = preg_replace("/'/","\'",$q['text']);
        $sql = "UPDATE help SET text='".$q['text']."' WHERE id=".$q['id'];
        pg_query(DBConnect(),$sql);
        $n++;
    }    
}
?>
