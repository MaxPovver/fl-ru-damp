<?php

define('LOG_FILE', '/var/tmp/plproxy-checker.log');

require_once '../classes/config.php';
require_once '../classes/DB.php';

class DBe extends DB {

	public function squery($sql) {
		if (!preg_match("/^SELECT/i", $sql)) {
			$fp = fopen(LOG_FILE, 'a');
			fwrite($fp, "$sql\n");
			fclose($fp);
		}
		return parent::squery($sql);
	}

}

@unlink(LOG_FILE);

$master = new DBe('master');
$plproxy = new DBe('plproxy');

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
	$nodes[$i] = new DBe("node{$i}");
}
$NODEMASK = count($nodes)-1;


// тест всех подключений
$p = $master->val("SELECT 'Master is connect'");
if ($p) {
	mess($p);
} else {
	die('Master is not connect');
}
$p = $plproxy->val("SELECT 'PlProxy is connect'");
if ($p) {
	mess($p);
} else {
	die('PlProxy is not connect');
}
for ($i=0; $i<count($nodes); $i++) {
	$p = $nodes[$i]->val("SELECT 'Node $i is connect'");
	if ($p) {
		mess($p);
	} else {
		die("Node $i is not connect");
	}
}
mess("All connects is OK.");


/**
 *
 * —инхронизаци€ freelancer, employer, users_uid между нодами и базой freelance
 *
 */
$tables = array(
	array('freelancer', array('uid', 'login', 'uname', 'usurname', 'email', 'reg_date', 'photo', 'photosm', 'is_banned', 'role', 'subscr', 'is_pro', 'is_pro_test', 'is_pro_new', 'spec', 'spec_orig')),
	array('employer', array('uid', 'login', 'uname', 'usurname', 'email', 'reg_date', 'photo', 'photosm', 'is_banned', 'role', 'subscr', 'is_pro', 'is_pro_test', 'is_pro_new')),
	array('users_uid', array('uid'))
);

foreach ($tables as $table) {

	mess("Start check {$table[0]} from freelance to nodes...");
	$res1 = $master->query("SELECT \"".implode('","', $table[1])."\" FROM {$table[0]}");

	while ($r1 = pg_fetch_assoc($res1)) {

		foreach ($unodes as $nodenum) {

			$node = $nodes[$nodenum];
			$r2 = $node->row("SELECT \"".implode('","', $table[1])."\" FROM {$table[0]} WHERE uid = ?", $r1['uid']);

			if (empty($r2)) {
				$node->insert($table[0], $r1);
				mess("NODE {$nodenum}: Insert into {$table[0]} with uid = {$r1['uid']}. Very bad!!!");
			} else if ($diff = eq_rows($r1, $r2, array())) {
				$node->update($table[0], $diff, "uid = ?", $r1['uid']);
				mess("NODE {$nodenum}: Update {$table[0]} with uid = {$r1['uid']}. Very bad!!!");
			}

		}

	}

}


/**
 *
 * —инхронизаци€ сообщений между нодами
 *
 */
mess("Start check messages between nodes...");

foreach ($unodes as $nodenum) {
	
	$node = $nodes[$nodenum];
	$res  = $node->query("SELECT * FROM messages");
	
	while ($n1_mess = pg_fetch_assoc($res)) {

		// дл€ сообщений массовой рассылки
		if (($unodes[$n1_mess['from_id'] & $NODEMASK] == $nodenum) && $n1_mess['to_id'] == 0) {
			for ($i=0; $i<count($unodes); $i++) {
				if ($unodes[$i] != $nodenum) {
					$n2 = $nodes[$unodes[$i]];
					$n2_mess = $n2->row("SELECT * FROM messages WHERE id = ?", $n1_mess['id']);
					if (empty($n2_mess)) {
						$n2->insert('messages', $n1_mess);
						mess("NODE {$unodes[$i]}: Insert massending message with id = {$n1_mess['id']}");
					} else if ($diff = eq_rows($n1_mess, $n2_mess, array(''))) {
						$n2->update('messages', $diff, "id = ?", $n1_mess['id']);
						mess("NODE {$unodes[$i]}: Update massending message with id = {$n1_mess['id']}");
					}
				}
			}
			continue;
		}

		// если отправитель и получатель с одном ноде, то пропускаем
		if ($unodes[$n1_mess['from_id'] & $NODEMASK] == $unodes[$n1_mess['to_id'] & $NODEMASK] || ($n1_mess['to_id'] == 0)) {
			continue;
		}

		// если это нод отправител€ (оригинал сообщени€)
		if ($unodes[$n1_mess['from_id'] & $NODEMASK] == $nodenum) {
			$n2num = $unodes[$n1_mess['to_id'] & $NODEMASK];
			$n2 = $nodes[$n2num];
			$n2_mess = $n2->row("SELECT * FROM messages WHERE id = ?", $n1_mess['id']);
			// добавление или удаление сообщени€ в случае несоответсви€ (кроме read_time)
			if (empty($n2_mess)) {
				$n2->insert('messages', $n1_mess);
				mess("NODE {$n2num}: Insert message with id = {$n1_mess['id']}. PgQ bug.");
			} else if ($diff = eq_rows($n1_mess, $n2_mess, array('read_time'))) {
				$n2->update('messages', $diff, "id = ?", $n1_mess['id']);
				mess("NODE {$n2num}: Update message with id = {$n1_mess['id']}. PgQ bug.");
			}
			// проверка read_time
			if (!empty($n2_mess) && ($n1_mess['read_time'] != $n2_mess['read_time']) && ($n2_mess['read_time'] == "1970-01-01 00:00:00")) {
				$n2->query("UPDATE messages SET read_time = ? WHERE id = ?", $n1_mess['read_time'], $n1_mess['id']);
				mess("NODE {$n2num}: Update read_time for message with id = {$n1_mess['id']}. Not updated original record. Very bad!!!");
			}
		// если это нод получател€ (копи€ сообщени€)
		} else if ($unodes[$n1_mess['to_id'] & $NODEMASK] == $nodenum) {
			$n2num = $unodes[$n1_mess['from_id'] & $NODEMASK];
			$n2 = $nodes[$n2num];
			$n2_mess = $n2->row("SELECT * FROM messages WHERE id = ?", $n1_mess['id']);
			// если копи€ сообщени€ есть, а оригинала нет
			if (empty($n2_mess)) {
				$n2->insert('messages', $n1_mess);
				mess("NODE {$n2num}: Insert message with id = {$n1_mess['id']}. Lost original record. Very bad!!!");
			// провер€ем read_time
			} else if (($n1_mess['read_time'] != $n2_mess['read_time']) && ($n2_mess['read_time'] == "1970-01-01 00:00:00")) {
				$n2->query("UPDATE messages SET read_time = ? WHERE id = ?", $n1_mess['read_time'], $n1_mess['id']);
				mess("NODE {$n2num}: Update read_time for message with id = {$n1_mess['id']}. PgQ bug.");
			}
		}
	
	}

}


/**
 *
 * —инхронизаци€ teams между нодами
 *
 */
mess("Start check teams between nodes...");

foreach ($unodes as $nodenum) {

	$node = $nodes[$nodenum];
	$res  = $node->query("SELECT * FROM teams");

	while ($t1 = pg_fetch_assoc($res)) {

		// если добавивший и добавленный в одном ноде, то пропускаем
		if ($unodes[$t1['user_id'] & $NODEMASK] == $unodes[$t1['target_id'] & $NODEMASK]) {
			continue;
		}

		// если это нод добавившего
		if (($unodes[$t1['user_id'] & $NODEMASK]) == $nodenum) {
			$n2num = $unodes[$t1['user_id'] & $NODEMASK];
			$n2 = $nodes[$n2num];
			$t2 = $n2->row("SELECT * FROM teams WHERE user_id = ? AND target_id = ?", $t1['user_id'], $t1['target_id']);
			if (empty($t2)) {
				$n2->insert('teams', $t1);
				mess("NODE {$n2num}: Insert into teams with user_id = {$t1['user_id']} AND target = {$t1['target_id']}. PgQ bug.");
			} else if ($diff = eq_rows($t1, $t2, array())) {
				$n2->update('teams', $diff, "user_id = ? AND target_id = ?", $t1['user_id'], $t1['target_id']);
				mess("NODE {$n2num}: Update teams with user_id = {$t1['user_id']} AND target = {$t1['target_id']}. PgQ bug.");
			}
		// если это нод добавленного
		} else if (($unodes[$t1['target_id'] & $NODEMASK]) == $nodenum) {
			$n2num = $unodes[$t1['user_id'] & $NODEMASK];
			$n2 = $nodes[$n2num];
			$t2 = $n2->row("SELECT * FROM teams WHERE user_id = ? AND target_id = ?", $t1['user_id'], $t1['target_id']);
			if (empty($t2)) {
				$n2->insert('teams', $t1);
				mess("NODE {$n2num}: Insert into teams with user_id = {$t1['user_id']} AND target = {$t1['target_id']}. No original record! Very bad!!!");
			}
		}

	}

}

/**
 * 
 * —инхронизаци€ сообщений с базой freelance
 * 
 * 
 */
mess("Start check messages from nodes to freelance...");

foreach ($unodes as $nodenum) {

	$node = $nodes[$nodenum];
	$res  = $node->query("SELECT * FROM messages");

	while ($n_mess = pg_fetch_assoc($res)) {

		if (($unodes[$n_mess['from_id'] & $NODEMASK] != $nodenum) && $n_mess['to_id'] != 0) {
			continue;
		}

		$m_mess = $master->row("SELECT * FROM messages WHERE id = ?", $n_mess['id']);
		if (empty($m_mess)) {
			$diff = $n_mess;
			unset($diff['files']);
			$master->insert('messages', $diff);
			mess("MASTER: Insert message with id = {$n_mess['id']}. PgQ bug.");
		} else if ($diff = eq_rows($n_mess, $m_mess, array('files'))) {
			$master->update('messages', $diff, "id = ?", $n_mess['id']);
			mess("MASTER: Update message with id = {$n_mess['id']}. PgQ bug.");
		}

		$n_files = $node->array_to_php($n_mess['files']);
		$m_files = $master->col("SELECT fid FROM messages_files WHERE mid = ?", $n_mess['id']);
		$replace_files = FALSE;
		if (count($n_files) != count($m_files)) {
			$replace_files = TRUE;
		} else {
			if (!empty($n_files)) {
				foreach ($n_files as $v) {
					if (!in_array($v, $m_files)) {
						$replace_files = TRUE;
						break;
					}
				}
			}
		}
		if ($replace_files) {
			$master->query("DELETE FROM messages_files WHERE mid = ?", $n_mess['id']);
			if (!empty($n_files)) {
				$master->query("INSERT INTO messages_files (mid, fid, fname) SELECT ?, file.id, file.fname FROM file WHERE file.id IN (?l)", $n_mess['id'], $n_files);
			}
			mess("MASTER: Update files with message id = {$n_mess['id']}");
		}

		if (($argv[1] == 'messages_zeros') && ($n_mess['to_id'] == 0)) {
			$n_res = $plproxy->query("SELECT message_id, user_id, to_visible, read_time FROM messages_zeros_userdata(?)", $n_mess['id']);
			$m_res = $master->query("SELECT * FROM messages_zeros WHERE message_id = ?", $n_mess['id']);
			$changed = 0;
			$added   = 0;
			while ($n_zero = pg_fetch_assoc($n_res)) {
				$n = TRUE;
				while ($m_zero = pg_fetch_assoc($m_res)) {
					if ($n_zero['user_id'] == $m_zero['user_id']) {
						$n = FALSE;
						if ($diff = eq_rows($n_zero, $m_zero, array())) {
							$master->update('messages_zeros', $diff, 'message_id = ? AND user_id = ?', $n_zero['message_id'], $n_zero['user_id']);
							$changed++;
						}
						break;
					}
				}
				if ($n) {
					$master->insert('messages_zeros', $n_zero);
					$added++;
				}
				pg_result_seek($m_res, 0);
			}
			if ($added || $changed) {
				mess("MASTER: Update massending user list for id = {$n_mess['id']}. Added {$added}, changed {$changed}. PgQ bug.");
			}
		}


	}

}


/**
 *
 * —инхронизаци€ teams между нодами и базой freelance
 *
 */
mess("Start check teams from nodes to freelance...");

foreach ($unodes as $nodenum) {

	$node = $nodes[$nodenum];
	$res  = $node->query("SELECT * FROM teams");

	while ($t1 = pg_fetch_assoc($res)) {

		// если добавивший из другого нода, то пропускаем
		if ($unodes[$t1['user_id'] & $NODEMASK] != $nodenum) {
			continue;
		}

		$t2 = $master->row("SELECT * FROM teams WHERE user_id = ? AND target_id = ?", $t1['user_id'], $t1['target_id']);

		if (empty($t2)) {
			$master->insert('teams', $t1);
			mess("MASTER: Insert into teams with user_id = {$t1['user_id']} AND target = {$t1['target_id']}. PgQ bug.");
		} else if ($diff = eq_rows($t1, $t2, array())) {
			$master->update('teams', $diff, "user_id = ? AND target_id = ?", $t1['user_id'], $t1['target_id']);
			mess("MASTER: Update teams with user_id = {$t1['user_id']} AND target = {$t1['target_id']}. PgQ bug.");
		}

	}

}


/**
 *
 * —инхронизаци€ ignor, notes, mess_folders, mess_ustf между нодами и базой freelance
 *
 */
$tables = array(
	array("ignor", "user_id = ? AND target_id = ?", "user_id", "target_id"),
	array("mess_folders_N", "id = ?", "id"),
	array("mess_ustf", "id = ?", "id"),
	array("mess_folders", "id = ?", "id"),
	array("notes", "id = ?", "id")
);

foreach ($tables as $table) {

	if ($table[0] == 'mess_folders_N') {
		$table[0] = 'mess_folders';
		$eq_filter = array('users_count');
	} else {
		$eq_filter = array();
	}

	mess("Start check {$table[0]} from nodes to freelance...");

	foreach ($unodes as $nodenum) {

		$node = $nodes[$nodenum];
		$res  = $node->query("SELECT * FROM {$table[0]}");

		while ($r1 = pg_fetch_assoc($res)) {
			$r2 = $master->row("SELECT * FROM {$table[0]} WHERE {$table[1]}", $r1[$table[2]], (empty($table[3])? "": $r1[$table[3]]));

			if (isset($r2['users_cont'])) {
				$r1['users_cont'] = $r1['users_count'];
				unset($r1['users_count']);
			}
			$mess_id = ($table[0] == 'ignor')? "user_id = {$r1['user_id']} AND target_id = {$r1['target_id']}": "id = {$r1['id']}";

			if (empty($r2)) {
				$master->insert($table[0], $r1);
				mess("MASTER: Insert into {$table[0]} with {$mess_id}. PgQ bug.");
			} else if ($diff = eq_rows($r1, $r2, $eq_filter)) {
				$master->update($table[0], $diff, $table[1], $r1[$table[2]], (empty($table[3])? "": $r1[$table[3]]));
				mess("MASTER: Update {$table[0]} with {$mess_id}. PgQ bug.");
			}
		}
		
	}
}


mess("Complete.");

function eq_rows($row1, $row2, $skip) {
	$keys = array_keys($row1);
	$res  = array();
	foreach ($keys as $key) {
		if (!in_array($key, $skip) && $row1[$key] != $row2[$key]) {
			$res[$key] = $row1[$key];
		}
	}
	return $res;
}

function mess($message) {
	$m = $message."\n";
	echo $m;
}
