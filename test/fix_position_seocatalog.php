<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/seo.php");

/**
 * Исправление сбитых позиций в SEO-каталоге /catalog/ 
 */

$sql = "SELECT parent FROM seo_sections 
        WHERE parent <> 0 
        GROUP BY parent 
        HAVING COUNT(parent) <> MAX(pos_num) OR MIN(pos_num) <> 1";

$parents = $DB->rows($sql);
$seo = new seo();

if($parents) {
    foreach($parents as $k=>$v) {
        $sql  = "SELECT id FROM seo_sections WHERE parent = {$v['parent']} ORDER BY id ASC";

        $rows = $DB->rows($sql);

        if($rows) {
            foreach($rows as $i=>$row) {
                $info['pos_num'] = $i+1;
                $seo->updateSection($row['id'], $info);
                
            }
            echo "Update parent - {$v['parent']}<br/>";
        }
    }
}

?>