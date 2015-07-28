<?php 
define( 'IS_SITE_ADMIN', 1 );
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/seo/SeoTags.php");
session_start();

$uid = get_uid();
if(!(hasPermissions('adm') && hasPermissions('meta'))) {
  header ("Location: /404.php");
  exit;
}
$gAction = __paramInit('string', 'action', null, '');
$pAction = __paramInit('string', null, 'action', '');

$seoValues = new SeoValues();

$css_file = array();
switch ($pAction) {
    case 'edit':
        $id = __paramInit('int', 'id', 'id');
        $post = array();
        for($i=1; $i<=SeoValues::SIZE_TEXT; $i++) {
            $post['tu_title_'.$i] = __paramInit('string', null, 'tu_title_'.$i, '');
            $post['tu_text_'.$i] = __paramInit('string', null, 'tu_text_'.$i, '');
            $post['f_title_'.$i] = __paramInit('string', null, 'f_title_'.$i, '');
            $post['f_text_'.$i] = __paramInit('string', null, 'f_text_'.$i, '');
        }
        for($i=1; $i<=SeoValues::SIZE_KEY; $i++) {
            $post['key_'.$i] = __paramInit('string', null, 'key_'.$i, '');
        }
        $is_update = $seoValues->save($id, $post);
        break;
    default:
        break;
}

switch($gAction) {
    case 'edit':
        $id = __paramInit('int', 'id');
        $card = $seoValues->getCardById($id);
        $inner_page = "tpl.edit.php";
        break;
    default:
        $list = $seoValues->getList();
        $inner_page  = "tpl.index.php";
        break;
}

array_push($css_file, "moderation.css", 'new-admin.css', 'nav.css');

$menu_item   = 100;
$rpath       = '../../';
$header      = $rpath . 'header.php';
$footer      = $rpath . 'footer.html';
$content     = '../content2.php';
$template    = 'template2.php';


include( $rpath . $template );

?>