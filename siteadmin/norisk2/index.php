<?
define( 'IS_SITE_ADMIN', 1 );
$no_banner = 1;
$rpath = "../../";
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");
session_start();
$uid = get_uid();
if(!hasPermissions('sbr') && !hasPermissions('sbr_finance'))
    header_location_exit('/404.php');	

$is_edit_access = hasPermissions('sbr');    
    
$sbr = new sbr_adm($uid, $_SESSION['login']);

$content = "../content2.php";
$template = "template2.php";

$site = __paramInit('string', 'site', 'site', 'docsflow');

switch ($site) {
    case 'stat':
    case 'arbitrage':
    case '1c':
    case 'invoice':
        break;
    default :
        $site = 'docsflow';
}

$action = __paramInit('string', 'action', 'action');
$inner_page = "inner_{$site}.php";

$css_file = array( 
    'moderation.css', 
    'norisk-admin2.css',
    'new-admin.css', 
    'nav.css'
);

$header = $rpath."header.php";
$footer = $rpath."footer.html";
$additional_header = '<script type="text/javascript" src="/scripts/sbr.js"></script>';

$stage_id  = __paramInit('int', NULL, 'stage_id');
$scheme = __paramInit('int', 'scheme', 'scheme', 0);
$page = __paramInit('int', 'page', 'page', 1);
$dir = __paramInit('string', 'dir', 'dir', 'DESC');
$dir_col = __paramInit('int', 'dir_col', 'dir_col', 0);
$filter = __paramInit('array', 'filter', 'filter');
if($filter)
    array_walk_recursive($filter, create_function('&$v', '$v=stripslashes($v);'));
if(!$filter['to']['day']||!$filter['to']['month']||!$filter['to']['year']) $filter['to'] = NULL;
if($site == 'docsflow' && !$scheme && !isset($filter['from']))
    $filter['from'] = array('day'=>date('d'), 'month'=>date('n'), 'year'=>date('Y'));
if(!$filter['from']['day']||!$filter['from']['month']||!$filter['from']['year']) $filter['from'] = NULL;

switch($site) {
    case 'docsflow' :
        if($scheme == -1 && $filter['to'] == null) {
            $arch_time = mktime(0,0,0, date('m')-6, date('d'), date('Y'));

            $filter['to'] = array(
                'day' => date('d', $arch_time),
                'month' => date('n', $arch_time),
                'year' => date('Y', $arch_time),
            );
        }

        $docs = $sbr->getDocsFlow($scheme, $filter, $page, $dir, $dir_col, $page_count);
        if ( $docs ) {
            foreach ($docs as $i => $_doc) {
                $_doc['act_sys'] = !is_emp($_doc['role']) && $_doc['act_sys'] == 1 ? 5 : $_doc['act_sys'];
                $docs[$i] = $_doc;
                /* @mark_0013241 */
                $docs[$i]['reqv_history'] = sbr_meta::getUserReqvHistory($docs[$i]['stage_id'], $docs[$i]['user_id']);
                $form_type_e   = $docs[$i]['reqv_history']['e']['form_type'];
                $form_type_b   = $docs[$i]['reqv_history']['b']['form_type'];
                $form_type[$i] = ($form_type_e ? $form_type_e :
                                 ($form_type_b ?$docs[$i]['reqv_history']['b']['form_type']:$docs[$i]['form_type']));
            }
        }
        
        $sbr->getExrates();
        sbr_meta::getReqvFields();
        break;
    case '1c':
        if($action=='export') {
            $date_s = __paramInit('string', 'date_s');
            $date_e = __paramInit('string', 'date_e');
            $file_data_csv = sbr_adm_finance::exportSBRDataToCSV($date_s, $date_e);
            $fname = "sbr_export_1c";
            if($date_s || $date_e) {
                if($date_s) $fname .= "-{$date_s}";
                if($date_e && $date_s!=$date_e) $fname .= "-{$date_e}";
            }
            $fname .= ".csv";
            if($file_data_csv) {
                header("Pragma: public");
                header("Expires: 0"); 
                header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
                header("Cache-Control: private",false);
                header("Content-Type: text/csv"); 
                header("Content-Disposition: attachment; filename=\"{$fname}\";" ); 
                readfile($file_data_csv);
                exit;
            } else {
                $not_result_1c = true;
            }
        }
        break;
    case 'stat' :
        $stats = $sbr->getStats($filter, TRUE);
        break;
    case 'arbitrage' :
        $sError = '';
        
        if (!$ds) $ds = mktime(0, 0, 1, date('m'), date('d'), date('Y'));
        if (!$de) $de = mktime(23, 59, 59, date('m'), date('d'), date('Y'));
        
        if ( $_GET['export'] ) {
            $sStartDate = ($_GET['ds']) ? date('Y-m-d', strtotime($_GET['ds'])) : null;
            $sEndDate   = ($_GET['de']) ? date('Y-m-d', strtotime($_GET['de'])) : null;
            
            if ( $sStartDate && $sEndDate && $sStartDate > $sEndDate ) {
            	$sError = 'Период указан не верно.';
            	break;
            }
            
            $sbr->printArbitrageReport( $sStartDate, $sEndDate );
            
            exit;
        }
        
        $content  = "../content.php";
        $template = "template.php";
        
        break;
        
    case 'invoice':
        if ($action == 'parse_report') {
            if (!$_FILES['report']) {
                $error['report'] = 'Ошибка загрузки файла.';
            }
            
            $file = $_FILES['report']['tmp_name'];
            sbr_adm::parseInvoiceData($file);
            
            header_location_exit('/siteadmin/norisk2/?site=invoice');
        }
        
        $filter = array();
        $filter['f_sbr'] = __paramInit('int', 'f_sbr', null);
        $filter['f_login'] = __paramInit('string_no_slashes', 'f_login', null);
        $filter['f_akkr'] = __paramInit('int', 'f_akkr', null);
        $filter['f_sum'] = __paramInit('int', 'f_sum', null);
        $filter['f_status'] = __paramInit('int', 'f_status', null, -1);
        $filter['f_orderby'] = __paramInit('string_no_slashes', 'f_orderby', null, 'sbr');
        $filter['f_desc'] = __paramInit('bool', 'f_desc', null, false);
        
        $filter['f_actdate'] = __paramInit('string_no_slashes', 'f_actdate', null);
        if (preg_match('#\d?\d.\d?\d.\d\d\d\d#', $filter['f_actdate'])) {
            $filter['f_actdate_pg'] = preg_replace('#(\d?\d).(\d?\d).(\d\d\d\d)#', '$3-$2-$1', $filter['f_actdate']);
        }
        $filter['f_invdate'] = __paramInit('string_no_slashes', 'f_invdate', null);
        if (preg_match('#\d?\d.\d?\d.\d\d\d\d#', $filter['f_invdate'])) {
            $filter['f_invdate_pg'] = preg_replace('#(\d?\d).(\d?\d).(\d\d\d\d)#', '$3-$2-$1', $filter['f_invdate']);
        }
        
        
        $pagesCount = $sbr->getInvoicesPagesCount($filter);
        $page = __paramInit('int', 'page', null, 1);
        if ($pagesCount > 0 && $page > $pagesCount) {
            header_location_exit('/404.php');
        }
        
        // формируем строку параметров
        $filterParams = '';
        foreach ($filter as $key => $value) {
            $filterParams .= '&' . $key . '=' . $value;
        }
        
        $orderLink = './?site=invoice' . $filterParams;
        
        $filter['f_limit'] = sbr_adm::INVOICES_PAGE_SIZE;
        $filter['f_offset'] = ($page - 1) * $filter['f_limit'];
        
        $data = $sbr->getInvoices($filter);
        
        break;
}

if($filter['from'] === NULL) {
    $filter['from'] = array('day'=>0, 'month'=>0, 'year'=>0);
}

if($filter)
    $filter_prms = '&'.http_build_query(array('filter'=>$filter));
    
if($is_edit_access) {
    if(isset($_POST['add_doc'])) {
        $stage = $sbr->initFromStage($stage_id);
        if($sbr->addDocR($_POST, $_FILES))
            header_location_exit("/siteadmin/norisk2/?site={$site}&scheme={$scheme}&page={$page}{$filter_prms}&dir={$dir}&dir_col={$dir_col}#{$_POST['anchor']}", 1);
        $error[$_POST['anchor']] = $sbr->error['docs']['attach'];
    }
    
    if(isset($_GET['recv_docs']) && isset($_GET['suids'])) {
        $sbr->setDocsReceived($_GET['suids'], true);
        header_location_exit("/siteadmin/norisk2/?site={$site}&scheme={$scheme}&page={$page}{$filter_prms}&dir={$dir}&dir_col={$dir_col}#{$_POST['anchor']}", 1);
    }
    
    if(isset($_GET['unrecv_docs']) && isset($_GET['suids'])) {
        $sbr->setDocsReceived($_GET['suids'], false);
        header_location_exit("/siteadmin/norisk2/?site={$site}&scheme={$scheme}&page={$page}{$filter_prms}&dir={$dir}&dir_col={$dir_col}#{$_POST['anchor']}", 1);
    }
}

if(!$filter['to'])
    $filter['to'] = array('day'=>date('d'), 'month'=>date('n'), 'year'=>date('Y'));
    
include ($rpath.$template);

?>
