<?
if(!defined('IN_SBR')) exit;
$fpath = 'employer/';
$inner = $fpath.'currents.php';

switch($site) {

    case 'create' :
        $inner = $fpath.'create.php';
        $js_file = array( 'mAttach2.js' );
        $sbr->stages = array(new sbr_stages($sbr));
        $sbr->data['scheme_type'] = sbr::SCHEME_DEFAULT;
        $sbr->getScheme();
        $rez_type = __paramInit('int', NULL, 'rez_type');
        $rt_checked = !!$rez_type;
        if($rt_disabled = $sbr->checkChangeRT()) {
            if(!($rez_type = $sbr->user_reqvs['rez_type']))
                 $rez_type = sbr::RT_RU; // если не установлен флаг в базе, но checkChangeRT, то считаем, что он руський (т.к. до флага только резиденты были).
        }
        if($action == 'create' && $rt_checked) {
            if($rez_type != $sbr->user_reqvs['rez_type']) {
                //@todo: запрещаем изменять финансы в старой СБР #29196
                //sbr_meta::setUserReqv($sbr->uid, $rez_type, $sbr->user_reqvs['form_type'], $rrr, TRUE);
                $sbr->user_reqvs['rez_type'] = $rez_type;
            }
            if($sbr->create($_POST, $_FILES)) {
                //if(!$sbr->isDraft()) $sbr->setUserReqvHistory($sbr->uid, $sbr->data['id'], 0);
                header_location_exit($sbr->isDraft() ? '/norisk2/?site=drafts' : "/norisk2/?id={$sbr->id}");
            }
        }
        if($prj_id = __paramInit('int', 'pid'))
            $sbr->initFromProject($prj_id);
        $sbr_schemes = $sbr->getSchemes();
        break;


    case 'editstage' :
        $inner = $fpath.'create.php';
        $js_file = array( 'mAttach2.js' );
        $stage_id = __paramInit('int', 'id', 'stage_id');
        $version = __paramInit('bool', 'v', 'v');
        $stage = $sbr->initFromStage($stage_id);
        $sbr_schemes = $sbr->getSchemes();
        if(!$stage || $sbr->error[404] || $stage->status == sbr_stages::STATUS_COMPLETED || $stage->status == sbr_stages::STATUS_INARBITRAGE || $stage->status == sbr_stages::STATUS_ARBITRAGED)
            header_location_exit('/404.php');
        if($version && ($stage->frl_version > $stage->version || $sbr->frl_version > $sbr->version)) {
            if($stage->frl_version > $stage->version) { $sbr->data['cost'] -= $stage->cost; $stage->data = $stage->getVersion($stage->version, $stage->data); $sbr->data['cost'] += $stage->cost; } // !!!
            if($sbr->frl_version   > $sbr->version)   $sbr->data = $sbr->getVersion($sbr->version, $sbr->data);
        }

        $rt_checked = true;
        $rt_disabled = $sbr->reserved_id || $sbr->checkChangeRT();
        if(!($rez_type = $sbr->user_reqvs['rez_type']))
             $rez_type = sbr::RT_RU;
        if($action == 'editstage') {
            if($_POST['cancel'] || $sbr->edit($_POST, $_FILES))
                header_location_exit("/norisk2/?site=Stage&id={$stage_id}");
        }
        break;


    case 'edit' :
        $inner = $fpath.'create.php';
        $js_file = array( 'mAttach2.js' );
        if($sbr_id = __paramInit('int', 'id', 'id', 0))
            $sbr->initFromId($sbr_id);
        if(!$sbr_id || $sbr->error[404] || $sbr->status == sbr::STATUS_COMPLETED || !$sbr->data)
            header_location_exit('/404.php');
        $sbr_schemes = $sbr->getSchemes();
        $rt_checked = true;
        $rt_disabled = $sbr->reserved_id || $sbr->checkChangeRT();
        if(!($rez_type = $sbr->user_reqvs['rez_type']))
             $rez_type = sbr::RT_RU;
        if($action == 'edit') {
            if($_POST['cancel'] || $sbr->edit($_POST, $_FILES)) {
                $ok = true;
                if($_POST['send'] && ($sbr->status==sbr::STATUS_CANCELED || $sbr->status==sbr::STATUS_REFUSED))
                    $ok = $sbr->resendCanceled($sbr->id);
                if($ok)
                    header_location_exit($sbr->isDraft() ? '/norisk2/?site=drafts' : "/norisk2/?id={$sbr->id}");
            }
        }
        break;


    case 'new' :
        $inner = $fpath.'new.php';
        $projects_cnt = projects::CountMyProjects($sbr->uid, false, true);
        break;


    case 'Stage' :
        $inner = 'stage.php';
        $stage_id  = __paramInit('int', 'id', 'id');
        if(!($stage = $sbr->initFromStage($stage_id)))
            break;

        $feedback_sent = isset($_SESSION["thnx_block{$stage_id}"]);
        if ($feedback_sent) {
            unset($_SESSION["thnx_block{$stage_id}"]);
        } 
        
        if($action == 'resolve_changes') {
            $resend = __paramInit('bool', NULL, 'resend');
            $cancel = __paramInit('bool', NULL, 'cancel');
            $version = __paramInit('int', NULL, 'version');
            if($resend) {
               if($stage->resendChanges())  // !!!
                   header_location_exit("/norisk2/?site=Stage&id={$stage->data['id']}");
            }
            else if($cancel) {
               if($stage->cancelChanges())
                   header_location_exit("/norisk2/?site=Stage&id={$stage->data['id']}");
            }
        }
        if($action == 'change_status') {
            $status = __paramInit('int', NULL, 'status');
            if($stage->changeStatus($status)) {
                /* @mark_0013241 */
                if($status == sbr_stages::STATUS_PROCESS ) {
                    $stage->setUserReqvHistory($stage->sbr->data['frl_id'], $stage->data['id'], 0);
                    $stage->setUserReqvHistory($stage->sbr->data['emp_id'], $stage->data['id'], 0);
                }
                header_location_exit("/norisk2/?site=Stage&id={$stage->data['id']}");
            }
        }
        if($stage->status != sbr_stages::STATUS_INARBITRAGE && $stage->status != sbr_stages::STATUS_ARBITRAGED) {
            if(($sbr->status == sbr::STATUS_PROCESS || $sbr->status == sbr::STATUS_CHANGED) && ($stage_changed = ($stage->frl_version > $stage->version || $sbr->frl_version > $sbr->version))) {
                $sbr->v_data = $sbr->data;
                $stage->v_data = $stage->data;
                if($sbr->frl_version   > $sbr->version)   { $sbr->data = $sbr->getVersion($sbr->version, $sbr->v_data); $sbr->getScheme(); }
                if($stage->frl_version > $stage->version) $stage->data = $stage->getVersion($stage->version, $stage->v_data);
            } else if($sbr->status == sbr::STATUS_PROCESS && $sbr->stages_version == $sbr->frl_stages_version && $sbr->version == $sbr->frl_version && !$sbr->reserved_id) { // !!! проверить. Можно ли резервировать, если фрилансер не согласился с изменениями.
                // !!! Непонятно с комментами. У фрилансера в доступе всегда, а у заказчика тут только страница с резервацией.

                $bank  = __paramInit('int', 'bank', 'bank');
                $inner = $fpath.'stage-reserve'.($bank && !$no_reserve ? '-bn' : '').'.php';
                $ndss = 'В том числе НДС - 18% с суммы агентского вознаграждения ООО "Ваан" ('
                      . (100 * $sbr->scheme['taxes'][sbr::EMP][sbr::TAX_EMP_COM]['percent'])
                      . '%)';
                // если проект в разработке, то в черновики нельзя
                if($action == 'draft' && $sbr->status != sbr::STATUS_PROCESS) {
                    if($sbr->draft($sbr->id))
                        header_location_exit('/norisk2/?site=drafts');
                }

                $sbr->getReserveSum();
                $account = new account();
                $account->GetInfo($sbr->uid);
                
                $no_reserve = 0;
                if ($sbr->reserve_sum * $sbr->cost2rur() < sbr_stages::MIN_COST_RUR) {
                    $no_reserve = 1;
                }

                if($action=='test_reserve' && !$no_reserve) {
                    if($sbr->testReserve($account))
                        header_location_exit("/norisk2/?site=Stage&id={$stage->id}");
                }
                

                if($bank && !$no_reserve) {
                    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/num_to_word.php");
                    $form_type = __paramInit('int', 'ft', 'form_type', $sbr->user_reqvs['form_type']);
                    $reqv_mode = __paramInit('int', 'rm', 'reqv_mode', 1);
                    $save_finance = __paramInit('bool', NULL, 'save_finance');
                    if($action=='invoice') {
                        if($sbr->invoiceBank($form_type, $_POST, $account)) {
                            header_location_exit("/norisk2/?site=Stage&id={$stage->id}&bank=1&ft={$form_type}&action=show_invoiced");
                        }
                    }
                    else if($action=='show_invoiced') {
                        $sbr->showInvoiced($form_type, $account);
                    }
                    $sbr->getInvoiceReqv($form_type, $reqv_mode);
                } elseif (!$no_reserve) {
                    $sbr->setStages(NULL, false);
                    $sbr->markNotUsedTaxes(sbr::EMP);
                }
            }
        }
        if( $stage->status == sbr_stages::STATUS_COMPLETED
             && !$stage->emp_feedback_id
           || $sbr->status == sbr::STATUS_COMPLETED
               && $stage->emp_feedback_id && !$sbr->emp_feedback_id
        )
        {
            $in_completed = true;
            $credit_sum = $stage->getPayoutSum(sbr::EMP);
            $inner = 'stage-completed.php';
            if($action=='complete' && ($_POST['feedback'] || $_POST['sbr_feedback'])) {
                $ok = true;
                if($_POST['feedback'])
                    $ok = $stage->feedback($_POST['feedback'], $_POST['sbr_feedback']);
                else if($_POST['sbr_feedback'])
                    $ok = $sbr->feedback($_POST['sbr_feedback']);
                if($ok) {
                    $_SESSION["thnx_block{$stage_id}"] = 1;
                    header_location_exit("/norisk2/?site=Stage&id={$stage->id}");
                }
            }
        }


        break;

    case 'drafts' :
        header_location_exit('/sbr/?site=drafts');
        if($action=='multiset') {
            if($_POST['send']) {
                if($id=$sbr->send($_POST['id']))
                    header_location_exit("/norisk2/?id={$id}");
            }
            else if($_POST['delete']) {
                if($sbr->delete($_POST['id']))
                    header_location_exit("/norisk2/?site=drafts");
            }
        }
        if($sbr_drafts = $sbr->getDrafts()) {
            $inner = $fpath.'drafts.php';
            $sbr_count = count($sbr_drafts);
        } else
            header_location_exit('/norisk2/');
        break;

    default :
        $is_hidden_newmsg   = $sbr->getIdSBRNewMsg($_SESSION['uid']);
        if($action=='status_action') {
            $resend = __paramInit('bool', NULL, 'resend');
            $del = __paramInit('bool', NULL, 'del');
            $cancel = __paramInit('bool', NULL, 'cancel');
            $id = __paramInit('int', NULL, 'id');
            $ok = true;
            if($resend) {
                $ok = $sbr->resendCanceled($id);
            } else if($cancel) {
                $ok = $sbr->cancel($id);
            } else if($del) {
                $ok = $sbr->delete($id);
            }
            if($ok)
                header_location_exit("/norisk2/".($del ? '' : "?id={$id}"));
        }
        $anchor = __paramInit('int', 'id');
        if(!($sbr_currents = $sbr->getCurrents())) {
            header_location_exit('/promo/sbr/');
        } else {
            $sbr->getUserReqvs();
        }
        $_SESSION['sbr_tip_old'] = notifications::getSbrTip('old');
        $sbr->setLastView('old');
        break;
}

