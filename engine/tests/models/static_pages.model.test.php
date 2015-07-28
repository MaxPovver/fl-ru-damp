<?php
class static_pages {
    static function get($alias) {
        return front::og("db")->select("SELECT * FROM static_pages WHERE alias = ? LIMIT 1;", $alias)->fetchRow();
    }
}
?>