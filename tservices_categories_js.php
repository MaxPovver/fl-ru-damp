<?php 
require_once $_SERVER["DOCUMENT_ROOT"]."/classes/stdf.php";
$membuf = new memBuff();
$memkey = "b-combo-gettucategories";
$s = $membuf->get($memkey);
if (!$s) 
{
    $DB = new DB('master');
    $sql = "SELECT 
                g.id AS gid, 
                g.title AS gname, 
                p.id AS id, 
                p.title AS name 
            FROM tservices_categories AS g 
            INNER JOIN tservices_categories AS p ON p.parent_id = g.id 
            ORDER BY g.id, p.id";
    $rows = $DB->rows($sql);

    $result = array();

    if (count($rows))
        foreach ($rows as $k => $i) {
            if ($result[$i["gid"]] === null) {
                $result[$i["gid"]] = array('0' => iconv("WINDOWS-1251", "UTF-8//IGNORE", $i["gname"]));
                if ($i["id"] !== null)
                    $result[$i["gid"]][$i["id"]] = iconv("WINDOWS-1251", "UTF-8//IGNORE", $i["name"]);
                else
                    $result[$i["gid"]] = iconv("WINDOWS-1251", "UTF-8//IGNORE", $i["gname"]);
            }
            else if (is_array($result[$i["gid"]])) {
                $result[$i["gid"]] [$i["id"]] = iconv("WINDOWS-1251", "UTF-8//IGNORE", $i["name"]);
            }
        }
   $s = json_encode($result);
   $membuf->add($memkey, $s, 3600);
}
print('var tuCategories = '.$s.';');