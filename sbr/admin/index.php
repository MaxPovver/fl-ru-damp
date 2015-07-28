<?php
if(!defined('IN_SBR')) exit;
$fpath = 'admin/';
if(!$site)
    $site = 'admin';

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/attachedfiles.php");

switch($site) {
    default:    
    case 'admin':
        header_location_exit("/norisk2/?site=admin");
        break;
    case 'Stage':
       // $fpath = 'freelancer/';
        $inner = 'stage.php';
        $stage_id  = __paramInit('int', 'id', 'id');
        $stage = $sbr->initFromStage($stage_id);
        if($action=='arb_resolve' && $sbr->isAdmin()) {
            $resolve = __paramInit('bool', NULL, 'send');
            $cancel = __paramInit('bool', NULL, 'cancel');
            if($resolve) {
                if(!($iagree = __paramInit('bool', NULL, 'iagree')))
                    $stage->error['arbitrage']['iagree'] = 'Необходимо подтверждение';
                else {
                    if($stage->arbResolve($_POST)) {
                        $frl_percent = $stage->request['frl_percent'] / 100;

                        if($frl_percent != 1 && $stage->sbr->scheme_type == sbr::SCHEME_LC ) {
                            $pskb = new pskb($stage->sbr);
                            $lc = $pskb->getLC();

                            $credit_sys = intvalPgSql(pskb::$exrates_map[$lc['ps_emp']]);
                            $stage->setPayoutSys($credit_sys, true, sbr::EMP);   
                        }

                        header_location_exit("/sbr/?site=Stage&id={$stage->id}");
                    }
                }
            }
            elseif($cancel) {
                if($stage->arbCancel())
                    header_location_exit("/sbr/?site=Stage&id={$stage->id}");
            }
        }
        if($stage->status == sbr_stages::STATUS_INARBITRAGE || $stage->status == sbr_stages::STATUS_ARBITRAGED) {
            $frl_version = $stage->getVersion($stage->frl_version, $stage->data);
            //$stage->data['descr'] = $frl_version['descr'];
        }
        break;
}
