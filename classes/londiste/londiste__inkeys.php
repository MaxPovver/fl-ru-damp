<?php

class londiste__inkeys extends londiste {
    

    protected function _loadHelperInfo() {
        $DB = new DB($this->master_alias);
        $rows = $DB->cache(600)->rows('SELECT * FROM londiste_helper__inkeys');
        foreach($rows as $r) {
            $this->_helper[$r['t_name']][$r['f_name']] = $r;
        }
    }
    
    function DB($t_name, $f_name, $values) {
        $DB = new DB($this->master_alias);
        if ( !$this->_helper[$t_name][$f_name] ) {
            return $DB;
        }

        $rh = $DB->row("SELECT *, f_mod + (f_lag||' seconds')::interval <= now() as expired FROM londiste_helper__inkeys WHERE t_name = ? AND f_name = ?", $t_name, $f_name);
        $db_alias = $this->master_alias . " {$rh['slave_1']} {$rh['slave_2']} {$rh['slave_3']} {$rh['slave_4']}";
        if($rh && $rh['expired'] != 't') { // expired означает, что записи добавлялись очень давно и можно спокойно брать из слейвов.
            foreach($values as $v) {
                if($v >= $rh['v_min']) {
                    $db_alias = $this->master_alias;
                    break;
                }
            }
        }

        //echo '[==========='.$db_alias.'=========] ';
        return new DB($db_alias);
    }

    function select($t_name, $f_name, $values, $order_by = NULL, $add_where = NULL, $limit = NULL) {
        $values = is_array($values) ? $values : array($values);
        $DB = self::DB($t_name, $f_name, $values);
        $where = "WHERE {$f_name} IN (?l)" . ($add_where ? " AND ({$add_where})" : '');
        $order_by = $order_by ? "ORDER BY {$order_by}" : '';
        $limit = $limit ? "LIMIT {$limit}" : "";
        $rows = $DB->{$this->_qtype}("SELECT * FROM {$t_name} {$where} {$order_by} {$limit}", $values);
        //echo $DB->sql;
        return $rows;
    }
    
}
