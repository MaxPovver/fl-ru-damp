<?php
require_once dirname(__FILE__). "/../stdf.php";

function resync($name)
{
        $sync = false;

        $nodes = array(1 => 'slave1', 2 => 'slave2');

        $node = '';

        if ($fp = fopen($name, 'r')) {
                fseek($fp, -2000, SEEK_END);
                while (($line = fgets($fp, 500)) !== false) {
                        if ($node) {
                                if (preg_match('/Key \([^)]+\)\=\((\d+)\) is not present in table/', $line, $matches)) {
                                        $id = $matches[1];
                                        $sql = "INSERT INTO users_uid (uid) VALUES ($id)";

                                        $db = new DB($node);
                                        $db->query($sql);
                                        echo "$node: $sql\n";

                                        $sync = true;
                                }

                                if (preg_match('/Remote detail\:.+\(from_id\)\=\((\d+)\)/', $line, $matches) && strpos($line, 'users_id') !== FALSE) {
                                        $id = $matches[1];
                                        $sql = "INSERT INTO users_uid (uid) VALUES ($id)";

                                        $db = new DB($node);
                                        $db->query($sql);
                                        echo "$node: $sql\n";

                                        $sync = true;
                                }

                                if (preg_match('/Remote detail\:.+\(uid\)\=\((\d+)\)/', $line, $matches) && strpos($line, 'users_id') === FALSE) {
                                        $id = $matches[1];
                                        $sql = "DELETE FROM users_uid WHERE uid=$id";

                                        $db = new DB($node);
                                        $db->query($sql);
                                        echo "$node: $sql\n";

                                        $sync = true;
                                }
                        }

                        if (preg_match('/ERROR\:  public\.messages_add\(9\)\: \[freelance_data(\d+)\]/', $line, $matches)) {
                                $node = $nodes[$matches[1]];
                        }

                        if (preg_match('/ERROR\:  public\.teams_add\(4\)\: \[freelance_data(\d+)\]/', $line, $matches)) {
                                $node = $nodes[$matches[1]];
                        }

                        if (preg_match('/ERROR\:  public\.sync_employer\(19\)\: \[freelance_data(\d+)\]/', $line, $matches)) {
                                $node = $nodes[$matches[1]];
                        }

                }
        }

        return $sync;
}

function longwaiting($log_file)
{
        return (time() - filemtime($log_file)) > 120;
}

$nsync = resync('/var/www/_freelance/classes/pgq/logs/plproxy-nsync.pgq');
$msync = resync('/var/www/_freelance/classes/pgq/logs/plproxy-msync.pgq');
$long_spam = longwaiting('/var/www/_freelance/classes/pgq/logs/spam.pgq');

if ($nsync || $msync || $long_spam) {
        exec('/bin/sh /var/www/_freelance/classes/pgq/pgq.restart.sh restart');
}
