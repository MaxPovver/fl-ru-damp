<?
require_once (ABS_PATH.'/classes/session_Memcached.php');
$session = new session();
if(defined('NEO')) { return; }

if($scd = (isset($GLOBALS['domain4cookie']) ? $GLOBALS['domain4cookie'] : $domain4cookie)) {
    session_set_cookie_params(0, '/', $scd, false, true);
}

session_set_save_handler(array(&$session, 'open'),
                          array(&$session, 'close'),
                          array(&$session, 'read'),
                          array(&$session, 'write'),
                          array(&$session, 'destroy'),
                          array(&$session, 'gc'));
register_shutdown_function('session_write_close');
