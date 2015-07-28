<?

$login = 'vladsoft';

error_reporting(E_ERROR);

require_once(realpath(dirname(__FILE__).'/../').'/classes/stdf.php');
require_once(realpath(dirname(__FILE__).'/../').'/classes/account.php');
require_once(realpath(dirname(__FILE__).'/../').'/classes/session.php');
require_once(realpath(dirname(__FILE__).'/../').'/classes/payed.php');
require_once(realpath(dirname(__FILE__).'/../').'/classes/users.php');


$ses = new session;
$user = $DB->row("
                SELECT uid, login
                FROM users
                WHERE login='{$login}';
                ");

$payed = new payed;
$sess  = new session;

$transaction_id = account::start_transaction($user['uid']);
$payed->AdminAddPRO($user['uid'], $transaction_id, '2 days');
$sess->UpdateProEndingDate($user['login']);

echo date('H:m:s')." - {$login}\n\n";

?>
