<?

if (!defined('IS_SITE_ADMIN')) {
    header ("Location: /404.php"); 
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/projects_complains.php';

$menu_item = 23;
if ($js_file) {
    $js_file[] = 'adm.complain_types.js';
} else {
    $js_file = array('adm.complain_types.js');
}

$moder = __paramInit('bool', 'moder', null, true);

if ($action && $action === 'save') {
    $add = array();
    $edit = array();
    $delete = array();
    
    foreach($_POST['name'] as $cTypeKey => $cTypeName) {
        if (!trim($cTypeName)) {
            continue;
        }
        
        
        $ctID = __paramValue('int', $_POST['id'][$cTypeKey]);
        $ctName = trim(__paramValue('string', $_POST['name'][$cTypeKey]));
        $ctTextarea = __paramValue('bool', $_POST['textarea'][$cTypeKey]);
        $ctRequired = __paramValue('bool', $_POST['required'][$cTypeKey]);
        $ctPos = __paramValue('int', $_POST['pos'][$cTypeKey]);
        if ($ctPos > 99) {
            $ctPos = 99;
        } elseif ($ctPos < -99) {
            $ctPos = -99;
        } elseif (!$ctPos) {
            $ctPos = 1;
        }
        $ctDel = __paramValue('int', $_POST['del'][$cTypeKey]);
        
        if (!$ctID && !$ctDel) {
            $add[] = array(
                'name'      => $ctName,
                'textarea'  => $ctTextarea,
                'required'  => $ctRequired,
                'pos'       => $ctPos,
            );
        } elseif ($ctDel && $ctID) {
            $delete[] = array('id' => $ctID);
        } elseif ($ctID && !$ctDel) {
            $edit[] = array(
                'id'        => $ctID,
                'name'      => $ctName,
                'textarea'  => $ctTextarea,
                'required'  => $ctRequired,
                'pos'       => $ctPos,
            );
        }
    }
    projects_complains::updateTypes($add, $edit, $delete, $moder);
}

$complainTypes = projects_complains::getTypes($moder, false);

$css_file   = array( 'moderation.css', 'nav.css' );
include $rpath.'template.php';

?>
