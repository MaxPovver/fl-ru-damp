<?php 
require_once $_SERVER["DOCUMENT_ROOT"]."/classes/stdf.php";
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
$membuf = new memBuff();
$memkey = "b-combo-getprofandgroups";
$s = $membuf->get($memkey);
if (!$s) {
$rows = professions::GetProfessionsAndGroup();
$result = array();
foreach ($rows as $k=>$i) {                         
    if ($result[$i["gid"]] === null) {
        $result[$i["gid"]] = array(
            '0' =>  iconv("WINDOWS-1251", "UTF-8//IGNORE", $i["gname"]),
            "undefined_value" => iconv("WINDOWS-1251", "UTF-8//IGNORE", 'Все специальности') 
        );
        if ($i["id"] !== null) $result[$i["gid"]] [$i["id"]] = iconv("WINDOWS-1251", "UTF-8//IGNORE", $i["name"]);
        else $result[$i["gid"]] = iconv("WINDOWS-1251", "UTF-8//IGNORE", $i["gname"]);
     }
     else if ( is_array($result[$i["gid"]]) ) {
        $result[$i["gid"]] [$i["id"]] = iconv("WINDOWS-1251", "UTF-8//IGNORE", $i["name"]);
     }
}
   $s = json_encode($result);
   $membuf->add($memkey, $s);
}
print('var professionsList = '.$s.'; professionsList["0"]["0"] = "Другое";professionsList["0"]["undefined_value"] = "Нет специализации";');