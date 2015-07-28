<?php
define('IS_SITE_ADMIN', 1);
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");;
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");;
session_start();

$uid = get_uid();

if (!hasPermissions('sbr')) {
    header_location_exit( '/404.php' );
}

$months = array(1=>'€нварь', 'февраль', 'март', 'апрель', 'май', 'июнь', 'июль', 'август', 'сент€брь', 'окт€брь', 'но€брь', 'декабрь');

$menu_item   = 4;
$rpath       = '../../';
$css_file   = array( 'moderation.css', 'new-admin.css' );
$js_file     = array( 'zeroclipboard/ZeroClipboard.js', 'user_search.js', 'admin_log.js', 'banned.js' );
$header      = $rpath . 'header.php';
$inner_page  = "index_inner.php";
$content     = '../content22.php';
$footer      = $rpath . 'footer.html';
$template    = 'template2.php';

$cmd          = __paramInit('string', 'cmd', 'cmd', '');

if($cmd == 'generate') {
    $month = __paramInit('integer', 'month', 'month', 1);
    $year  = __paramInit('integer', 'year', 'year', date('Y'));
    $period = array(
        0 => date('Y-m-01', mktime(0,0,0, $month, 1, $year)),
        1 => date('Y-m-t', mktime(0,0,0,$month, 1, $year))
    );
    $doc = __paramInit('string', 'doc', 'doc', '');
    
    if(sbr_meta::generateDocITO($period, false, $doc)) {
        header_location_exit("/siteadmin/sbr_ito/");
    }
} 

if($cmd == 'upload') {
    $login = 'admin';
    $dir = "users/" . substr($login, 0, 2) . "/{$login}/upload/";
    $doc_id = intval($_POST['doc_id']);
    if(is_array($_FILES['attachedfiles_file']) && !$_FILES['attachedfiles_file']['error'] && $doc_id > 0) { 
        $fname = $_FILES['attachedfiles_file']['name'];
        $ext = substr(strrchr($fname, "."), 1);
        
        if($ext != 'odt' && $ext != 'xlsx') {
            ?>
            <script>
                alert('‘ормат загружаемого файла должен быть xlsx или odt');
                window.top.document.body.style.cursor = 'default';
            </script>    
            <?
            exit;
        }
        $cFile = new CFile($_FILES['attachedfiles_file']);
        $cFile->table = 'file';
        $cFile->MoveUploadedFile($dir);
        
        if($cFile->id) {
            $ito    = current(sbr_meta::getITODocs($doc_id));
            sbr_meta::updateITOFile($cFile->id, $doc_id);
            
            $date = $ito['date_period'];
            $date_create_id = "date_create_" . date('Yn', strtotime($date));
            $file_name_id = "file_name_" . date('Yn', strtotime($date));
            $link = WDCPREFIX."/{$cFile->path}{$cFile->name}";
            
            $name = '—качать »“ќ за ' . $months[date('n', strtotime($ito['date_period']))] . ' ' . date('Y', strtotime($ito['date_period'])) . '.' . $ext;
            ?>
            <script>
                window.top.successUploadFile('<?= $date_create_id?>', '<?= $file_name_id;?>', '<?= date("d.m.Y H:i")?>', '<?=$link?>', '<?=$name?>');
                alert('‘айл загружен');
            </script>    
            <?
            
        } else {
            ?>
            <script>
                alert('ќшибка загрузки файла');
            </script>    
            <?
        }
        
    } else {
        ?>
        <script>
            alert('ќшибка загрузки файла');
        </script>    
        <?
    }
    ?>
    <script>
        window.top.document.body.style.cursor = 'default';
    </script>    
    <?
    exit;
}

$docs_ito = sbr_meta::getITODocs();

include( $rpath . $template );
