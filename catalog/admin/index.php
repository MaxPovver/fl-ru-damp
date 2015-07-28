<?php
require_once($_SERVER['DOCUMENT_ROOT']."/classes/seo.php");
session_start();
if(!hasPermissions('seo')) {
    header("Location: /404.php"); 
    exit;
}

/**
 * @todo $_GET['subdomain'] сделано дл€ тестировани€, скорей всего при разборе URI апачем можно будет писать в эту переменную поддомен 
 */
$_SESSION['subdomain'] = $subdomain = ($_GET['subdomain'] ? $_GET['subdomain'] : 'all');

$seo = new seo($subdomain);

$countries = $seo->getCountries();

$direct_link = $direct_id = __paramInit('string', 'direction');
if ($direct_link) {
    $direct = $seo->getDirectionByLink($direct_link);
    $direct_id = null;
    if ($direct['id']) {
        $direct_id = $direct['id'];
    }
} else {
    $direct_id = $seo->getDirectionIdFirst();
}

if (intval($direct_link) == -1) {
    $direct_id = -1;
}

switch($_GET['msgok']) {
    case 1:
        $is_save = true;
        $msgtext = '–аздел успешно добавлен';
        break;
    case 2:
        $is_save = true;
        $msgtext = '–аздел успешно изменен';
        break;
    case 3:
        $is_save = true;
        $msgtext = 'Ќапревление успешно добавлено';
        break;
    case 4:
        $is_save = true;
        $msgtext = 'Ќаправление успешно изменено';
        break;
    case 5:
        $is_save = true;
        $msgtext = 'ѕодраздел успешно добавлен';
        break;
    case 6:
        $is_save = true;
        $msgtext = 'ѕодраздел успешно изменен';
        break;
}

$sections   = $seo->getSections(true, $direct_id);
$subdomains = $seo->getSubdomains(false);
$directions = $seo->getDirections();
$activeItems = json_decode(stripslashes($_COOKIE['seocatalogmenu']));
if(!$activeItems) {
    $activeItems = array();
}



$action = $_POST['action'];

switch($action) {
    case "main":
        
        $update['meta_description'] = __paramInit("String", null, 'meta_description', null);
        $update['meta_keywords']    = __paramInit("String", null, 'meta_keywords', null);
        $update['content']          = __paramInit("String", null, 'content', null);
        $id = __paramInit('int', null, 'subdomain');                
        $seo->updateContentSubdomain($update, $id);
        $subdomain_id = $id;
        break;
    default: 
        break;
}

$rpath = "../../";
$content = "content.php";
$header = $rpath."header.php";
$footer = $rpath."footer.html";
$css_file = array( 'hljs.css');//, 'wysiwyg.css' );
$css_file[] = "seo.css";
$js_file = array( 'highlight.min.js', 'highlight.init.js', /*'mooeditable.new/MooEditable.ru-RU.js', 
    'mooeditable.new/rangy-core.js', 'mooeditable.new/MooEditable.js', 'mooeditable.new/MooEditable.Pagebreak.js', 
    'mooeditable.new/MooEditable.UI.MenuList.js', 'mooeditable.new/MooEditable.Extras.js', 'mooeditable.new/init.js',*/ 
    'seo.js', 'kwords.js', '/kword_js.php' );
$js_file_utf8[] = '/scripts/ckedit/ckeditor.js';
$additional_header = "<BASE href='{$host}'>";
    
include ($rpath."/template.php");
?>
