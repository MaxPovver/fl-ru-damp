<?
$new_site_css = true; 
$g_page_id = "0|30";
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr.php';
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/LocalDateTime.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/notifications.php");
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/projects.php';
session_start();
$uid = get_uid();
$site = __paramInit('string', 'site', 'site');

$sbr = sbr_meta::getInstance();
if(!$uid && $site == 'calc') {
    $content = "login_inner.php";
    include($rpath.'template2.php');
    exit;
}
// если еще не было СБР, то открыть можно только калькулятор СБР
if(!$uid || (!$sbr->uid && $site !== 'calc')) {
    header_location_exit('/promo/' . sbr::NEW_TEMPLATE_SBR . '/');
}
$sbr->setGetterSchemes(0);
$count_old_sbr = $sbr->getCountCurrentsSbr();
$sbr->setGetterSchemes(1);
$footer_norisk = true;
setlocale(LC_ALL, "en_US.UTF-8");

define('IN_SBR', true);
define('DEBUG', defined('IS_LOCAL') && IS_LOCAL || defined('SERVER') && (SERVER == 'beta' || SERVER == 'alpha'));

$js_file = array(
    '/scripts/sbr.js',
    '/scripts/sbr2.js',
    '/scripts/finance.js'
);

//$css_file = "norisk-user.css";
$content = '../sbr/content.php';

$action = __paramInit('string', 'action', 'action');
$sbr_id = __paramInit('integer', 'id');
$sbrss_classes = sbr::$ss_classes;
$sbrss_classes[sbr::STATUS_CHANGED][1] = $sbr->isEmp() ? 'Измененные «Безопасные Сделки» без утверждения' : 'Измененные «Безопасные Сделки», ожидающие вашего согласия';

sbr::$ss_classes = array(
    sbr::STATUS_NEW => array('nr-list-new', 'Новые «Безопасные Сделки» без утверждения'),
    sbr::STATUS_CHANGED => array('nr-list-changed', $sbr->isEmp() ? 'Измененные «Безопасные Сделки» без утверждения' : 'Измененные «Безопасные Сделки», ожидающие вашего согласия'),
    sbr::STATUS_PROCESS => array('nr-list-progress', 'В разработке'),
    sbr::STATUS_CANCELED => array('nr-list-canceled', 'Отмененные проекты'),
    sbr::STATUS_REFUSED => array('nr-list-canceled', 'Отклоненные проекты'),
    sbr::STATUS_COMPLETED => array('nr-list-completed', 'Завершенные') );
if ($site !== 'calc') {
    if($sbr->isAdmin() || $sbr->isAdminFinance()) {
        include('../sbr/admin/index.php');
    } else if(!$sbr->isEmp()) 
        include('../sbr/freelancer/index.php');
    else
        include('../sbr/employer/index.php');
}


// Общее.
switch($site) {

    case 'history' :
        $inner = 'history.php';
        $sbr_id  = __paramInit('int', 'id', NULL, 0);
        $dir     = __paramInit('string', 'dir', 'dir', 'ASC');
        $dir_col = __paramInit('string', 'dir_col', 'dir_col', 0);
        if(!$sbr->initFromId($sbr_id, false, false, false))
            header_location_exit('/404.php');
        $sbr_history = $sbr->getHistory($_GET['filter'], $dir_col, $dir);
        $site_uri = "?site=history&id={$sbr_id}";
        break;
    
    case 'master':
        $sbr_id  = __paramInit('int', 'id', NULL, 0);
        $site_uri = "?site=master&id={$sbr_id}";
        if (!$js_file) {
            $js_file = array();
        }
        array_push($js_file, 'attachedfiles2.js');
        if(!$sbr)
            header_location_exit('/'.sbr::NEW_TEMPLATE_SBR.'/');
        break;

    case 'Stage' :
        $site_uri = "?site=Stage&id={$stage->id}";
        if(!$stage)
            header_location_exit('/'.sbr::NEW_TEMPLATE_SBR.'/');
        // Если сделка старая переносим пользователя на старый интерфейс
        if( !$stage->sbr->isNewVersionSbr() ) {
            header_location_exit("/norisk2/?site=Stage&id={$stage->id}");
        }
        $stage_doc = __paramInit('int', 'doc');
        if($action=='arbitration') {
            $overtime = strtotime($sbr->data['dateEndLC'] . ' - ' . pskb::ARBITRAGE_PERIOD_DAYS . " day");
            // Сб, Вс не рабочие дни
            if(date('w', $overtime) == 0 || date('w', $overtime) == 6) {
                $d = date('w', $overtime) == 6 ? 1 : 2;
                $overtime = $overtime - ($d * 3600* 24);
            }
            if(time() > $overtime) {
                header_location_exit("/".sbr::NEW_TEMPLATE_SBR."/?id={$sbr->id}");
            }
            if(!($iagree = __paramInit('bool', NULL, 'iagree'))) {
                $attachedfiles_arb = new attachedfiles($_POST['attachedfiles_session']);
                $attachedfiles_files_arb = $attachedfiles_arb->getFiles();
                if($attachedfiles_files_arb) {
                    $attachedfiles_files_arb = attachedfiles::getInitJSON($attachedfiles_files_arb);
                }
                $stage->error['arbitrage']['iagree'] = 'Необходимо подтверждение';
            } elseif( $stage->status != sbr_stages::STATUS_NEW ) {
                $descr = stripslashes($_POST['descr']);
                $attachedfiles_arb = new attachedfiles($_POST['attachedfiles_session']);
                $attachedfiles_files_arb = $attachedfiles_arb->getFiles();
                
                if($stage->arbitrage($descr, $attachedfiles_files_arb)) 
                    header_location_exit("/".sbr::NEW_TEMPLATE_SBR."/?id={$sbr->id}");
            }
            
            $attachedfiles_arb = new attachedfiles($_POST['attachedfiles_session']);
            $attachedfiles_files_arb = $attachedfiles_arb->getFiles(array(1,3), null, true);
            
            if (is_array($attachedfiles_files_arb)) {
                foreach($attachedfiles_files_arb as &$arbFile) {
                    $arbFile['tsize'] = iconv('CP1251', 'UTF-8', $arbFile['tsize']);
                    $arbFile['name'] = iconv('CP1251', 'UTF-8', $arbFile['name']);
                    $arbFile['orig_name'] = iconv('CP1251', 'UTF-8', $arbFile['orig_name']);
                }
            }
            unset($arbFile);
        }
        
        if(!$js_file) $js_file = array();
        if(!$css_file) $css_file = array();
        if($css_file && !is_array($css_file)) $css_file = array($css_file);
        array_push($js_file,  'highlight.min.js', 'highlight.init.js', /*'mooeditable.new/MooEditable.ru-RU.js', 
                'mooeditable.new/rangy-core.js', 'mooeditable.new/MooEditable.js', 'mooeditable.new/MooEditable.Pagebreak.js', 
                'mooeditable.new/MooEditable.UI.MenuList.js', 'mooeditable.new/MooEditable.Extras.js', 'mooeditable.new/init.js',*/ 'attachedfiles2.js', 'tawl_bem.js');
        array_push($css_file, 'hljs.css');
        $js_file_utf8[] = '/scripts/ckedit/ckeditor.js';
        $stage->initNotification(); 
        $stage->dateVersionTz();
        $stage->orders = $_COOKIE['sbr_order'] == 1 ? 'ASC' : 'DESC';
        $stage->getHistoryStage($stage->orders);
        $stage->getAllFiles();
        $stage->active_event = sbr_notification::getNotificationActive($sbr->id, $stage->id);
        if(!is_array($stage->active_event)) $stage->active_event = array();
        
        
        if(($sbr->status == sbr::STATUS_CANCELED || $sbr->status == sbr::STATUS_REFUSED || $stage->status == sbr_stages::STATUS_ARBITRAGED || $stage->status == sbr_stages::STATUS_COMPLETED) && $stage->history ) {
            foreach ($stage->history as $xact => $history) {
                $current = current($history);
                if($current['abbr'] == 'sbr_stages.MONEY_PAID') {
                    $frl_sum_paid = true;
                }
                if($current['abbr'] == 'sbr_stages.EMP_MONEY_REFUNDED') {
                    $emp_sum_paid = true;
                }
                if ($current['abbr'] == 'sbr.CANCEL') {
                    $stage->canceled_time = strtotime($current['xtime']);
                }
                if($current['abbr'] == 'sbr.REFUSE') {
                    $stage->refused_time = strtotime($current['xtime']);
                }
                if($current['abbr'] == 'sbr_stages.FRL_FEEDBACK') {
                    foreach($stage->sbr->docs as $hdoc) {
                        if($hdoc['type'] == sbr::DOCS_TYPE_ACT || $hdoc['type'] == sbr::DOCS_TYPE_FM_APPL || 
                           $hdoc['type'] == sbr::DOCS_TYPE_WM_APPL || $hdoc['type'] == sbr::DOCS_TYPE_YM_APPL) {
                            $head_docs[] = $hdoc;
                        }
                    }
                    if($head_docs) 
                        $stage->head_docs = $head_docs;
                }
            }
        }
        
        if($stage->sbr->docs) 
            foreach($stage->sbr->docs as $hdoc) {
                if($hdoc['type'] == sbr::DOCS_TYPE_OFFER) {
                    $offers_docs[] = $hdoc;
                }
                if($hdoc['type'] == sbr::DOCS_TYPE_AGENT_REP || $hdoc['type'] == sbr::DOCS_TYPE_ARB_REP || $hdoc['type'] == sbr::DOCS_TYPE_REP) {
                    $arbitrage_docs[] = $hdoc;
                }
            }
        
        if($stage->status == sbr_stages::STATUS_INARBITRAGE || $stage->status == sbr_stages::STATUS_ARBITRAGED) {
            $stage->getArbitrage();
            if((!$sbr->isAdmin() && !$sbr->isAdminFinance()) && $stage->status == sbr_stages::STATUS_ARBITRAGED) {
                $stage->getPayouts();
                $credit_sum = $stage->getPayoutSum();
                if(!$in_completed && (!$stage->payouts[$sbr->uid] && $credit_sum || !$stage->data[$sbr->upfx.'feedback_id'])) {
                    $is_arb_outsys = true;
                    $only_reserved_sys = $is_arb_outsys && $sbr->isEmp();
                    $notnp = __paramInit('bool', NULL, 'notnp');
                    $sbr->getIntrates($stage);
                    $sbr->getExrates();
                    //$inner = 'stage-completed.php';
                    $account = new account();
                    $account->GetInfo($sbr->uid);
                    if($action=='complete') {
                        if ($sbr->scheme_type == sbr::SCHEME_LC) {
                            if($stage->completeAgnt($_POST, $pskb)) {
                                $_SESSION["thnx_block{$stage_id}"] = 1;
                                header_location_exit("/".sbr::NEW_TEMPLATE_SBR."/?site=Stage&id={$stage->id}");
                            }
                        } else {
                            if($stage->complete($_POST, $only_reserved_sys && $sbr->cost_sys==exrates::YM)) {
                                //$sbr->setUserReqvHistory($sbr->data['frl_id'], $stage->id, 1);
                                //$sbr->setUserReqvHistory($sbr->data['emp_id'], $stage->id, 1);
                                $_SESSION["thnx_block{$stage->id}"] = 1;
                                header_location_exit("/".sbr::NEW_TEMPLATE_SBR."/?site=Stage&id={$stage->id}");
                            }
                        }
                        $action = NULL;
                    }
                }
            }
        }

        if($action == 'msg-add') {
            $attachedfiles = new attachedfiles($_POST['attachedfiles_session']);
            $attachedfiles_files = $attachedfiles->getFiles();
            foreach($attachedfiles_files as $att_file) {
                $comment_files[] = md5($att_file['id']);
            }
            if($msg_id = $stage->_new_addMsg($_POST, $attachedfiles_files))
                header_location_exit("?site={$site}&id={$stage->id}#n_{$msg_id}", 1);
        }
        if($action == 'msg-edit') {
            if($msg_id = $stage->editMsg($_POST, $_FILES))
                header_location_exit("?site={$site}&id={$stage->id}#c_{$msg_id}", 1);
        }
        $stage_msgs = $stage->getMsgs();
        $sbr->getDocs(NULL, false, true, $stage->id);
        $stage->setMsgsRead();
        
        $oMemBuff = new memBuff();
        $oMemBuff->delete( 'sbrMsgsCnt'.$sbr->session_uid );
        
        break;


    case 'docs' :
        if($sbr->isAdmin()) break; // здесь только для фрилансера и работодателя.
        $inner = 'docs.php';
        $sbr_id  = __paramInit('int', 'id', 'sbr_id');
        $stage_id  = __paramInit('int', 'sid', 'stage_id');
        if(!$sbr->initFromId($sbr_id, true, false, false))
            header_location_exit("/404.php");
        $site_uri = "?site=docs&id={$sbr->id}".($stage_id ? "&sid={$stage_id}" : '');
        switch($action) {
            case 'add_doc' :
                if($sbr->addDocR($_POST, $_FILES))
                    header_location_exit("/".sbr::NEW_TEMPLATE_SBR."/{$site_uri}");
                break;
        }
        $sbr->getDocs();
        break;


    case 'arbitrage' :
        $stage_id = __paramInit('int', 'id', 'id');
        if(__paramInit('bool', NULL, 'cancel'))
            header_location_exit("/".sbr::NEW_TEMPLATE_SBR."/?site=Stage&id={$stage_id}");

        if(!($stage = $sbr->initFromStage($stage_id)))
            header_location_exit("/404.php");

        if($stage->status == sbr_stages::STATUS_INARBITRAGE || ($stage->status & sbr_stages::STATUS_COMPLETED) == sbr_stages::STATUS_COMPLETED)
            header_location_exit("/".sbr::NEW_TEMPLATE_SBR."/?site=Stage&id={$stage_id}");

        if($action=='arbitration') {
            if(!($iagree = __paramInit('bool', NULL, 'iagree')))
                $stage->error['arbitrage']['iagree'] = 'Необходимо подтверждение';
            else {
                $descr = stripslashes($_POST['descr']);
                if($stage->arbitrage($descr, $_FILES['attach']))
                    header_location_exit("/".sbr::NEW_TEMPLATE_SBR."/?id={$sbr->id}");
            }
        }
        $site_uri = "?site=arbitrage&id={$stage->id}";
        $inner = 'arbitrage.php';
        break;
        
    case 'calc' :
        
        header_location_exit('/404.php');
        
        $g_help_id = 220;
        $rqv = null;
        if ($sbr->isFrl()) {
            $rqv = $sbr->getUserReqvs(get_uid(0));
        }
        $inner = 'tpl.calc.php';
        $js_file = array( '/css/block/b-tooltip/b-tooltip.js', '/css/block/b-filter/b-filter.js' );
        break;
    case 'archive':
        
        if(!$count_old_sbr ) {
            header_location_exit("/" . sbr::NEW_TEMPLATE_SBR . "/");
        }
        $filter = 'archive';
        $inner  = 'tpl.archive.php';
        
        break;

    default :
        break;
}


$css_file = array('norisk-user.css', '/css/nav.css', '/css/block/b-button-multi/b-button-multi.css','/css/block/b-card/b-card.css','/css/block/b-estimate/b-estimate.css', '/css/block/b-tax/b-tax.css', '/css/block/b-icon/_help/b-icon_help.css', '/css/block/b-master/b-master.css', '/css/block/b-master/b-master.css', '/css/block/b-tooltip/b-tooltip.css', '/css/block/b-icon/__role/b-icon__role.css','/css/block/b-menu/_tabs/b-menu_tabs.css', '/css/block/b-input-hint/b-input-hint.css');

$js_file[] = 'mAttach2.js';

include($rpath.'template2.php');
