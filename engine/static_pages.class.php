<?php
class static_pages {
    static function get($alias) {
        $res = front::og("db")->select("SELECT * FROM static_pages WHERE alias = ? LIMIT 1;", $alias)->fetchRow();
        if(!$res) $res = array("alias"=>$alias);
        return $res;
    }
}
?>