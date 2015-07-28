<?php

$fix = FALSE;
$nosleep = FALSE;

if ( !empty($argv) && count($argv) > 1 ) {
    for ( $i=1; $i<count($argv); $i++ ) {
        if ( $argv[$i] == 'fix' ) $fix = TRUE;
        if ( $argv[$i] == 'nosleep' ) $nosleep = TRUE;
    }
}

$sd = date('H:i:s');

require_once '../classes/config.php';
require_once '../classes/DB.php';

$master = new DB('master');
$plproxy = new DB('plproxy');

$nodes  = array();
$unodes = array();
$n = $plproxy->col("SELECT * FROM plproxy.get_cluster_partitions('usercluster')");
for ($i=0; $i<count($n); $i++) {
	$s = preg_split("/\s+/", $n[$i]);
	$pg_db["node{$i}"]['port'] = 5432;
	foreach ($s as $v) {
		preg_match("/(.+?)\=(.+)/", $v, $o);
		if ($o[1] == 'password') {
			$pg_db["node{$i}"]['pwd'] = $o[2];
		} else if ($o[1] == 'dbname') {
			$pg_db["node{$i}"]['name'] = $o[2];
		} else {
			$pg_db["node{$i}"][$o[1]] = $o[2];
		}
	}
	$flag = TRUE;
	for ($j=0; $j<count($nodes); $j++) {
		if (
			$pg_db["node{$i}"]['name'] == $pg_db["node{$j}"]['name']
			&& $pg_db["node{$i}"]['user'] == $pg_db["node{$j}"]['user']
			&& $pg_db["node{$i}"]['host'] == $pg_db["node{$j}"]['host']
			&& $pg_db["node{$i}"]['pwd'] == $pg_db["node{$j}"]['pwd']
			&& $pg_db["node{$i}"]['port'] == $pg_db["node{$j}"]['port']
		) {
			$flag = FALSE;
			break;
		}
	}
	if ($flag) {
		//$unodes[] = $pg_db["node{$i}"];
		$unodes[] = $i;
	}
	$nodes[$i] = new DB("node{$i}");
}
$NODEMASK = count($nodes)-1;

p("\n^^ freelancers ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^\n");


$fields = array(
    'uid', 'login', 'uname', 'usurname', 'email', 'is_banned', 'is_pro', 'is_pro_test', 'is_pro_new', 'role',
    'photo', 'photosm', 'subscr', 'reg_date', 'spec', 'spec_orig'
);

$i = 0;
$e = 0;
$ee = 0;
$es = 0;
$empty = 0;
$res = $master->query("SELECT ".implode($fields, ',')." FROM freelancer");
while ( $r = pg_fetch_assoc($res) ) {
    $r1 = $nodes[0]->row("SELECT ".implode($fields, ',')." FROM freelancer WHERE uid = ?", $r['uid']);
    $r2 = $nodes[1]->row("SELECT ".implode($fields, ',')." FROM freelancer WHERE uid = ?", $r['uid']);
    $er = FALSE;
    $eer = FALSE;
    $f1s = '';
    $f2s = '';
    $f1u = '';
    $f2u = '';
    $eempty = FALSE;
    if (empty($r1)) {
        p("{$r['login']} NOT IN Node1\n");
        if ($fix) $nodes[0]->insert('freelancer', $r);
        $eempty = TRUE;
    }
    if (empty($r2)) {
        p("{$r['login']} NOT IN Node2\n");
        if ($fix) $nodes[1]->insert('freelancer', $r);
        $eempty = TRUE;
    }
    if ($eempty) {
        $empty++;
    } else {
        foreach ( $fields as $field ) {
            if ( !eq_field($field, $r[$field], $r1[$field], $v) ) {
                $f1s .= "{$field},";
                $f1u .= $DB->parse("{$field} = ?,", $v);
                if ( $field == 'email' ) {
                    $eer = TRUE;
                }
                $er = TRUE;
            }
            if ( !eq_field($field, $r[$field], $r2[$field], $v) ) {
                $f2s .= "{$field},";
                $f2u .= $DB->parse("{$field} = ?,", $v);
                if ( $field == 'email' ) {
                    $eer = TRUE;
                }
                $er = TRUE;
            }
        }
        if ( $f1s ) {
            p("{$r['login']} in Node1: " . substr($f1s, 0, strlen($f1s)-1) . "\n");
            if ($fix) {
                $nodes[0]->query("UPDATE freelancer SET " . substr($f1u, 0, strlen($f1u)-1) . " WHERE uid = {$r['uid']}");
            }
            $er = TRUE;
        }
        if ( $f2s ) {
            p("{$r['login']} in Node2: " . substr($f2s, 0, strlen($f2s)-1) . "\n");
            if ($fix) {
                $nodes[1]->query("UPDATE freelancer SET " . substr($f2u, 0, strlen($f2u)-1) . " WHERE uid = {$r['uid']}");
            }
            $er = TRUE;
        }
        $e += $er? 1: 0;
        $ee += $eer? 1: 0;
    }
    if ( $i != 0 && $i % 5000 == 0 ) {
        p("$i records checked. Sleep...\n");
        if ($fix && !$nosleep) {
            sleep(30);
        }
    }
    $i++;
    
}

p("\n^^ employers ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^\n");


$fields = array(
    'uid', 'login', 'uname', 'usurname', 'email', 'is_banned', 'is_pro', 'is_pro_test', 'is_pro_new', 'role',
    'photo', 'photosm', 'subscr', 'reg_date'
);

//$i = 0;
//$e = 0;
//$ee = 0;
$es = 0;
$res = $master->query("SELECT ".implode($fields, ',')." FROM employer");
while ( $r = pg_fetch_assoc($res) ) {
    $r1 = $nodes[0]->row("SELECT ".implode($fields, ',')." FROM employer WHERE uid = ?", $r['uid']);
    $r2 = $nodes[1]->row("SELECT ".implode($fields, ',')." FROM employer WHERE uid = ?", $r['uid']);
    $er = FALSE;
    $eer = FALSE;
    $f1s = '';
    $f2s = '';
    $f1u = '';
    $f2u = '';
    $eempty = FALSE;
    if (empty($r1)) {
        p("{$r['login']} NOT IN Node1\n");
        if ($fix) $nodes[0]->insert('employer', $r);
        $eempty = TRUE;
    }
    if (empty($r2)) {
        p("{$r['login']} NOT IN Node2\n");
        if ($fix) $nodes[1]->insert('employer', $r);
        $eempty = TRUE;
    }
    if ($eempty) {
        $empty++;
    } else {
        foreach ( $fields as $field ) {
            if ( !eq_field($field, $r[$field], $r1[$field], $v) ) {
                $f1s .= "{$field},";
                $f1u .= $DB->parse("{$field} = ?,", $v);
                if ( $field == 'email' ) {
                    $eer = TRUE;
                }
                $er = TRUE;
            }
            if ( !eq_field($field, $r[$field], $r2[$field], $v) ) {
                $f2s .= "{$field},";
                $f2u .= $DB->parse("{$field} = ?,", $v);
                if ( $field == 'email' ) {
                    $eer = TRUE;
                }
                $er = TRUE;
            }
        }
        if ( $f1s ) {
            p("{$r['login']} in Node1: " . substr($f1s, 0, strlen($f1s)-1) . "\n");
            if ($fix) {
                $nodes[0]->query("UPDATE employer SET " . substr($f1u, 0, strlen($f1u)-1) . " WHERE uid = {$r['uid']}");
            }
            $er = TRUE;
        }
        if ( $f2s ) {
            p("{$r['login']} in Node2: " . substr($f2s, 0, strlen($f2s)-1) . "\n");
            if ($fix) {
                $nodes[1]->query("UPDATE employer SET " . substr($f2u, 0, strlen($f2u)-1) . " WHERE uid = {$r['uid']}");
            }
            $er = TRUE;
        }
        $e += $er? 1: 0;
        $ee += $eer? 1: 0;
    }
    if ( $i != 0 && $i % 5000 == 0 ) {
        p("$i records checked. Sleep...\n");
        if ($fix && !$nosleep) {
            sleep(30);
        }
    }
    $i++;
    
}


p("\n^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^\n");

p("$e update errors find\n");
p("$ee email errors find\n");
p("$empty insert errors find\n");

$se = date('H:i:s');
p("Started: $sd, Ended: $se");



function p($text) {
    static $fp;
    if ( empty($fp) ) {
        @unlink('/var/tmp/check-msync.log');
        $fp = fopen('/var/tmp/check-msync.log', 'a');
    }
    fwrite($fp, $text);
    echo $text;
}

function eq_field($name, $m_val, $n_val, &$r) {
    $r = '';
    switch ($name) {
        case 'uid':
        case 'login':
        case 'email':
        case 'role':
        case 'subscr':
        case 'reg_date':
            if ( $m_val !== $n_val ) {
                $r = $m_val;
                return FALSE;
            }
            break;
        case 'uname':
        case 'usurname':
        case 'photo':
        case 'photosm':
            if ( is_null($m_val) ) {
                if ( $n_val !== '') {
                    $r = '';
                    return FALSE;
                }
            } else {
                if ( $m_val !== $n_val ) {
                    $r = $m_val;
                    return FALSE;
                }
            }
            break;
        case 'is_pro':
        case 'is_pro_test':
        case 'is_pro_new':
            if ( is_null($m_val) ) {
                if ( $n_val !== 'f' ) {
                    $r = 'f';
                    return FALSE;
                }
            } else {
                if ( $m_val !== $n_val ) {
                    $r = $m_val;
                    return FALSE;
                }
            }
            break;
        case 'is_banned':
            if ( is_null($m_val) ) {
                if ( $n_val !== '0' ) {
                    $r = '0';
                    return FALSE;
                }
            } else {
                if ( $m_val !== $n_val ) {
                    $r = $m_val;
                    return FALSE;
                }
            }
            break;
        case 'spec':
        case 'spec_orig':
            if ( is_null($m_val) ) {
                if ( $n_val !== 0 ) {
                    $r = '0';
                    return FALSE;
                }
            } else {
                if ( $m_val !== $n_val ) {
                    $r = $m_val;
                    return FALSE;
                }
            }
            break;
    }
    return TRUE;
}