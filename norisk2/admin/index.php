<?
if(!defined('IN_SBR')) exit;
$fpath = 'admin/';
if(!$site)
    $site = 'admin';

switch($site) {

default:    
case 'admin':
    $css_file = array( 'norisk-admin.css', 'nav.css' );
    $content = $fpath.'admin-content.php';
    $inner = $fpath.'admin-all.php';
    $mode = __paramInit('string', 'mode', 'mode', 'all');
    if (!in_array($mode, array('arbitrage', 'all', 'feedbacks', 'payouts', 'reports', 'lc'))) {
        header_location_exit('/404.php');
    }
    $page = __paramInit('int', 'page', 'page', 1);
    $dir = __paramInit('string', 'dir', 'dir', $mode == 'payouts' ? 'ASC' : 'DESC');
    $dir_col = __paramInit('int', 'dir_col', 'dir_col', 0);
    $filter = isset($_GET['filter']) ? array_map('stripslashes', $_GET['filter']) : NULL;
    if($filter)
        $filter_prms = '&'.str_replace('%', '%%', http_build_query(array('filter'=>$filter)));
    if($page < 0) $page = 1;
    $sbr_count = $sbr->getCount();
    $page_count = $sbr_count[$mode];
    if($filter && $mode!='lc') {
        $func = 'count' . $mode;
        $page_count = $sbr->$func($filter);
    }

    switch($mode) {
        case 'arbitrage' :
            $inner = $fpath.'tpl.admin-arbitrage.php';
            $sbr_all = $sbr->getArb($page, $dir, $dir_col, $filter);
            break;
        case 'all' :
            $sbr_all = $sbr->getAll($mode, $page, $dir, $dir_col, $filter);
            break;
        case 'feedbacks' :
            $inner = $fpath.'admin-feedbacks.php';
            if(!($sbr_feedbacks = $sbr->getAllFeedbacks($page)))
                $sbr_feedbacks = array();
            break;
        case 'payouts' :
            $inner = $fpath.'admin-payouts.php';
            $user_id = __paramInit('int', 'user_id', 'user_id');
            $stage_id = __paramInit('int', 'stage_id', 'stage_id');
            $stage = $sbr->initFromStage($stage_id, false);
            if($sbr->isAdmin() || $sbr->isAdminFinance()) {
                if($action == 'payout') {
                    $stage->payout($user_id);
                }
                if($action == 'unpayout') {
                    $stage->unpayout($user_id);
                }
                if($action == 'refund') {
                    $payment_id = __paramInit('int', 'payment_id', 'payment_id');
                    $debug      = __paramInit('int', 'debug', 'debug');
                    $sbr->refund($payment_id, $stage, $debug);
                    //header_location_exit("/norisk2/?site=admin&mode=payouts");
                }
            }
            $sbr_payouts = $sbr->getAllPayouts($page, $dir, $dir_col, $filter);
            require_once($_SERVER['DOCUMENT_ROOT'].'/classes/yd_payments.php');
            $yd = new yd_payments();
            if(!is_release()) {
                $yd->DEBUG = array(
                  'address'=>$GLOBALS['host'].'/norisk2/admin/yd-server-test.php'
                );
                if(defined('BASIC_AUTH')) {
                    $yd->DEBUG['headers'] = 'Authorization: Basic '.base64_encode(BASIC_AUTH)."\r\n";
                }
            }
            $yd_balance = $yd->balance();
            break;
        case 'reports' :
            $inner = $fpath.'admin-reports.php';
            $filter = $_POST['filter'];
            $ndfl_action = __paramInit('bool', 'ndfl', 'ndfl');
            $rev_action  = __paramInit('bool', 'act_rev', 'act_rev');
            $yd_report  = __paramInit('bool', 'yd_report', 'yd_report');
            if(!isset($filter['from']))
                $filter['from'] = array('day'=>1, 'month'=>date('n'), 'year'=>date('Y'));
            if(!$filter['to']['day']||!$filter['to']['month']||!$filter['to']['year']) $filter['to'] = NULL;
            if(!$filter['from']['day']||!$filter['from']['month']||!$filter['from']['year']) $filter['from'] = NULL;
            if(!$filter['to'])
                $filter['to'] = array('day'=>date('d'), 'month'=>date('n'), 'year'=>date('Y'));
            if($ndfl_action) {
                $sbr->printNdflReport($filter);
            } else if ($rev_action) {
                $sbr->printRevisionReport($filter);
            } else if ($yd_report) {
                $sbr->printYdReport($filter);
            } else if($filter['cost_sys']) {
                $sbr->printReports($filter);
            } 

            break;
        case 'lc':
            $inner = $fpath.'admin-lc.php';
            $pskb = new pskb();
            $f_state = __paramInit('string', 'state');
            $f_search = stripslashes(__paramInit('string', 'search'));
            $f_ps_emp = __paramInit('string', 'ps_emp');
            $f_ps_frl = __paramInit('string', 'ps_frl');
            $f_date_cover = $_GET['date_cover'];
            $f_date_end   = $_GET['date_end'];
            $build = $_GET;
            unset($build['page']);
            $url_build =  urldecode(http_build_query($build));
            if(!($pskb_list = $pskb->searchLC($page, $_GET, $page_count)))
                $pskb_list = array();
            break;
    }
    break;
case 'Stage':
    $inner = 'stage.php';
    $stage_id  = __paramInit('int', 'id', 'id');
    $stage = $sbr->initFromStage($stage_id);
    if($action=='arb_resolve' && $sbr->isAdmin()) {
        $resolve = __paramInit('bool', NULL, 'sendform');
        $cancel = __paramInit('bool', NULL, 'cancel');
        if($resolve) {
            if(!($iagree = __paramInit('bool', NULL, 'iagree')))
                $stage->error['arbitrage']['iagree'] = 'Необходимо подтверждение';
            else {
                if($stage->arbResolve($_POST)) {
                    $frl_percent = $stage->request['frl_percent'] / 100;
                    $stage->getArbitrage(false, false); // Раз вынесли решение берем арбитраж, для корректного расчета процентов
                    if($frl_percent != 1 && $stage->sbr->scheme_type == sbr::SCHEME_LC ) {
                        $pskb = new pskb($stage->sbr);
                        $lc = $pskb->getLC();

                        $credit_sys = intvalPgSql(pskb::$exrates_map[$lc['ps_emp']]);
                        $stage->setPayoutSys($credit_sys, true, sbr::EMP);   
                    }
                    
                    header_location_exit("/norisk2/?site=Stage&id={$stage->id}");
                }
            }
        }
        elseif($cancel) {
            if($stage->arbCancel())
                header_location_exit("/norisk2/?site=Stage&id={$stage->id}");
        }
    }
    
    $arbitrsList = $stage->getArbitrs();

    break;
case 'docs':
    $inner = $fpath.'docs.php';
    $sbr_id  = __paramInit('int', 'id', 'sbr_id');
    $stage_id  = __paramInit('int', 'sid', 'stage_id');
    if(!$sbr->initFromId($sbr_id, true, false, false))
        break;
    $site_uri = "?site=docs&id={$sbr->id}".($stage_id ? "&sid={$stage_id}" : '');

    if($sbr->isAdmin())
        switch($action) {
            case 'add_doc' :
                if($sbr->addDocR($_POST, $_FILES))
                    header_location_exit("/norisk2/{$site_uri}");
                break;
            case 'edit_doc' :
                if($sbr->editDocR($_POST, $_FILES))
                    header_location_exit("/norisk2/{$site_uri}");
                break;
            case 'delete' :
                if($sbr->delDocs($_POST['id']))
                    header_location_exit("/norisk2/{$site_uri}");
                break;
            default:
                list($action, $mode) = explode('=', $action);
                if($action=='set_access') {
                    if($sbr->setDocAccess($_POST['id'], (int)$mode))
                        header_location_exit("/norisk2/{$site_uri}");
                }
                else if($action=='set_status') {
                    if($sbr->setDocStatus($_POST['id'], (int)$mode))
                        header_location_exit("/norisk2/{$site_uri}");
                }
                break;
        }
    $sbr->getDocs();
    break;

// куда может ходить
case 'history':
    break;
}
