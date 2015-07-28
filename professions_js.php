<?php 

define('IS_PHP_JS', true);

require_once $_SERVER["DOCUMENT_ROOT"]."/classes/stdf.php";
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");

$membuf = new memBuff();
$memkey = "b-combo-getprofandgroups";
$s = $membuf->get($memkey);

if (!$s) {

    $rows = professions::GetProfessionsAndGroup('g.cnt DESC, p.pcount DESC NULLS LAST');

    $result = array();
    foreach ($rows as $k => $i) {

        if ($result[$i["gid"]] === null) {
            $result[$i["gid"]] = array(
                '0' =>  array(iconv("WINDOWS-1251", "UTF-8//IGNORE", $i["gname"]), "{$i["gcnt"]}"),
                "undefined_value" => array(iconv("WINDOWS-1251", "UTF-8//IGNORE", 'Все специальности'),"0")
            );

            if ($i["id"] !== null) { 
                $result[$i["gid"]] [$i["id"]] = array(iconv("WINDOWS-1251", "UTF-8//IGNORE", $i["name"]), "{$i["pcount"]}");
            } else {
                $result[$i["gid"]] = array(iconv("WINDOWS-1251", "UTF-8//IGNORE", $i["gname"]), "{$i["gcnt"]}");
            }

         } else if ( is_array($result[$i["gid"]]) ) {
            $result[$i["gid"]] [$i["id"]] = array(iconv("WINDOWS-1251", "UTF-8//IGNORE", $i["name"]), "{$i["pcount"]}");
         }

    }

   $s = json_encode($result);
   $membuf->add($memkey, $s);
}
print('var professionsList = '.$s.'; professionsList["0"]["0"] = ["Другое","0"]; professionsList["0"]["undefined_value"] = ["Нет специализации","0"];');