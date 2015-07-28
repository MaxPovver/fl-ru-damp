<?php
//error_reporting(E_ALL);
$front_req = array();
$front_req = $_GET;
foreach($_REQUEST as $k=>$v) {
   $front_req[$k] = $v;
}
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once("const.php");
include_once(getcwd().'/engine/system/front.class.php');
include_once('function.php');

session_start(); 
get_uid();

define("DIR_SEP", DIRECTORY_SEPARATOR);
define("ROOT_DIR", getcwd().DIR_SEP);

spl_autoload_register(array('front', 'load_class'));

define("URI", $_SERVER['REQUEST_URI']);

include_once(ROOT_DIR."engine/structure.php");   

system_db_layer::setConnection(DBConnect());
front::os("db", system_db_layer::getInstance());

front::os("tpl", new system_tpl_layer());
front::og("tpl")->set("host", $host);
front::og("tpl")->set("IS_LOCAL", IS_LOCAL);
front::$_req  = $front_req;

front::setMap($map);

if(!defined("NO_URL_MAPPING")) {
    front::exec_uri($_GET['pg']);
/*
    $req_uri = array_shift(explode("?" ,$_SERVER['REQUEST_URI']));
    if(file_exists(getcwd() . $req_uri)) {
        if(is_file(getcwd() . $req_uri)) {
            include(getcwd() . $req_uri); 
        } elseif(is_dir(getcwd() . $req_uri) ) {
            chdir(getcwd() . $req_uri);  
            include(getcwd() . DIR_SEP . 'index.php'); 
        }    
    }
*/
}
                             
?>