<?

$g_page_id = "0|30";
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/sbr.php';
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/notifications.php");
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/projects.php';
session_start();
get_uid();
$sbr = sbr_meta::getInstance();
if(!$sbr->uid)
  header_location_exit('/promo/sbr/');
$sbr->setGetterSchemes(0);
$footer_norisk = true;
setlocale(LC_ALL, "en_US.UTF-8");

define('IN_SBR', true);
define('DEBUG', defined('IS_LOCAL') && IS_LOCAL || defined('SERVER') && (SERVER == 'beta' || SERVER == 'alpha'));
$additional_header = array(
    '/scripts/sbr.js'
);

$css_file = array(
    'moderation.css',
    'norisk-user.css', 
    '/css/nav.css',
    '/css/block/b-menu/_tabs/b-menu_tabs.css' 
);

$content = 'content.php';

$action = __paramInit('string', 'action', 'action');
$site = __paramInit('string', 'site', 'site');
$sbrss_classes = sbr::$ss_classes;
$sbrss_classes[sbr::STATUS_CHANGED][1] = $sbr->isEmp() ? 'Измененные «Безопасные Сделки» без утверждения' : 'Измененные «Безопасные Сделки», ожидающие вашего согласия';

sbr::$ss_classes = array(
    sbr::STATUS_NEW => array('nr-list-new', 'Новые «Безопасные Сделки» без утверждения'),
    sbr::STATUS_CHANGED => array('nr-list-changed', $sbr->isEmp() ? 'Измененные «Безопасные Сделки» без утверждения' : 'Измененные «Безопасные Сделки», ожидающие вашего согласия'),
    sbr::STATUS_PROCESS => array('nr-list-progress', 'В разработке'),
    sbr::STATUS_CANCELED => array('nr-list-canceled', 'Отмененные проекты'),
    sbr::STATUS_REFUSED => array('nr-list-canceled', 'Отклоненные проекты'),
    sbr::STATUS_COMPLETED => array('nr-list-completed', 'Завершенные') );
    
if($sbr->isAdmin() || $sbr->isAdminFinance())
    include('admin/index.php');
else if(!$sbr->isEmp()) 
    include('freelancer/index.php');
else
    include('employer/index.php');


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


    case 'Stage' :
        $site_uri = "?site=Stage&id={$stage->id}";
        if(!$stage)
            header_location_exit('/norisk2/?');
        // Если сделка новая переносим пользователя на новый интерфейс
        if( $stage->sbr->isNewVersionSbr()  && !$sbr->isAdmin()) {
            header_location_exit("/sbr/?site=Stage&id={$stage->id}");
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
                    $inner = 'stage-completed.php';
                    $account = new account();
                    $account->GetInfo($sbr->uid);
                    if($action=='complete') {
                        if($stage->complete($_POST, $only_reserved_sys && $sbr->cost_sys==exrates::YM)) {
                            //$sbr->setUserReqvHistory($sbr->data['frl_id'], $stage->id, 1);
                            //$sbr->setUserReqvHistory($sbr->data['emp_id'], $stage->id, 1);
                            $_SESSION["thnx_block{$stage->id}"] = 1;
                            header_location_exit("/norisk2/?site=Stage&id={$stage->id}");
                        }
                        $action = NULL;
                    }
                }
            }
        }

        if($action == 'msg-add') {
            if($msg_id = $stage->addMsg($_POST, $_FILES))
                header_location_exit("?site={$site}&id={$stage->id}#c_{$msg_id}", 1);
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
                    header_location_exit("/norisk2/{$site_uri}");
                break;
        }
        $sbr->getDocs();
        break;


    case 'arbitrage' :
        $stage_id = __paramInit('int', 'id', 'id');
        if(__paramInit('bool', NULL, 'cancel'))
            header_location_exit("/norisk2/?site=Stage&id={$stage_id}");

        if(!($stage = $sbr->initFromStage($stage_id)))
            header_location_exit("/404.php");

        if($stage->status == sbr_stages::STATUS_INARBITRAGE || ($stage->status & sbr_stages::STATUS_COMPLETED) == sbr_stages::STATUS_COMPLETED)
            header_location_exit("/norisk2/?site=Stage&id={$stage_id}");

        if($action=='arbitration') {
            if(!($iagree = __paramInit('bool', NULL, 'iagree')))
                $stage->error['arbitrage']['iagree'] = 'Необходимо подтверждение';
            else {
                $descr = stripslashes($_POST['descr']);
                if($stage->arbitrage($descr, $_FILES['attach']))
                    header_location_exit("/norisk2/?id={$sbr->id}");
            }
        }
        $site_uri = "?site=arbitrage&id={$stage->id}";
        $inner = 'arbitrage.php';
        break;
        
    case 'calc' :
        header('Location: /bezopasnaya-sdelka/?site=calc');
        exit;
        /*
        $rqv = null;
        if ($sbr->isFrl()) {
            $rqv = $sbr->getUserReqvs(get_uid(0));
        }
        $inner = 'tpl.calc.php';
        $js_file = array( '/css/block/b-tooltip/b-tooltip.js', '/css/block/b-filter/b-filter.js' );
        */
        break;
        
    case 'new':
        header_location_exit("/sbr/?site=new", 0);
        break;
    
    case 'create':
        header_location_exit("/sbr/?site=create", 0);
        break;        
        
    default :
        break;
}

$js_file[] = 'mAttach2.js';

include($rpath.'template2.php');
