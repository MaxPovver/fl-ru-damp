<?
$rpath = "../";

$header = "../header.php";
$footer = "../footer.html";
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_offers.php");
session_start();
$uid = get_uid();
$pid = intvalPgSql((int)trim($_GET['pid']));
$wid = intvalPgSql((int)trim($_GET['wid']));
$name = trim($_GET['user']);

$user_obj = new users();
$user_obj->GetUser($name);
$uid = $user_obj->GetUid($error, $name);
$proj = array();
$proj = (array)$user_obj;

// Проект.
$obj_project = new projects();
$project = $obj_project->GetPrjCust($pid);

if (!$project || !$user_obj->uid) {
    $fpath = "../"; include("../404.php");
    exit;
} else {
    // Предложения по данному проекту.
    $obj_offer = new projects_offers();
    $offer = $obj_offer->GetPrjOffer($pid, $user_obj->uid);
    
    $width = $height = 0;
    if (!$offer) {
        $fpath = "../"; include("../404.php");
        exit;
    } else {
        if ($project['kind'] == 2) {
            $pict_name = '';
            foreach ($offer['attach'] as $key => $value) {
                if ($value['id'] == $wid) {
                    $pict_name = $value['pict'];
                }
            }
            if ($pict_name == '') {
                $fpath = "../"; include("../404.php");
                exit;
            }
        } else {
            $pict_name = $offer['pict' . $wid];
        }
    	if ($pict_name) {
    		$proj['pict'] = $pict_name;
    		$str = viewattach($offer['login'], $pict_name, "upload", $file, -1, -1, 1048576, 1);    		
    	}
    	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/static_compress.php");
        $stc = new static_compress;
 
$template = 'template2.php';
$content = 'tpl.viewproj.php'; 
include ("../".$template);exit;   	
?><?
    }
}
?>
