<?
if(!defined('IN_SBR')) exit;
$fpath = 'freelancer/';
$inner = $fpath.'currents.php';

switch($site) {

    case 'Stage' :
        $inner = 'stage.php';
        $stage_id  = __paramInit('int', 'id', 'id');
        if(!($stage = $sbr->initFromStage($stage_id)))
            break;

        $feedback_sent = isset($_SESSION["thnx_block{$stage_id}"]);
        if ($feedback_sent) {
            unset($_SESSION["thnx_block{$stage_id}"]);
        } 

        if($action == 'agree_stage') {
            $ok = __paramInit('bool', NULL, 'ok');
            $version = __paramInit('int', NULL, 'version');
            $sbr_version = __paramInit('int', NULL, 'sbr_version');
            if($ok) {
               if($stage->agreeChanges($version, $sbr_version))
                   header_location_exit("/norisk2/?site=Stage&id={$stage->data['id']}");
            }
            else {
               $reason = stripslashes($_POST['frl_refuse_reason']); // !!!
               $reason = substr(pg_escape_string($reason), 0, 512);
               if($stage->refuseChanges($version, $reason, $sbr_version))
                   header_location_exit("/norisk2/?site=Stage&id={$stage->data['id']}");
            }
        }

        if($stage->status != sbr_stages::STATUS_INARBITRAGE && $stage->status != sbr_stages::STATUS_ARBITRAGED) { // !!! можно убрать условия.
            if($sbr->status == sbr::STATUS_CHANGED && ($stage_changed = ($stage->frl_version && ($stage->frl_version < $stage->version || $sbr->frl_version < $sbr->version)))) {
                $sbr->v_data = $sbr->data;
                $stage->v_data = $stage->data;
                if($sbr->frl_version   < $sbr->version)   $sbr->v_data = $sbr->getVersion($sbr->frl_version, $sbr->data);
                if($stage->frl_version < $stage->version) $stage->v_data = $stage->getVersion($stage->frl_version, $stage->data);
            }
        }
        /*
         Зеленая:
         1) этап завершен нормально и не проставлены отзывы или не выбрана валюта выплат.
         2) этап завершен по арбитражу, вся сделка завершена, валюта выплат по этапу выбрана, отзывы проставлены, но не проставлены отзывы сервису.
         */
        if( $stage->status == sbr_stages::STATUS_COMPLETED
             && (!$stage->frl_feedback_id || !$stage->getPayouts($sbr->uid))
           || $sbr->status == sbr::STATUS_COMPLETED
               && $stage->frl_feedback_id && !$sbr->frl_feedback_id
        )
        {
            $in_completed = true;
            $notnp = __paramInit('bool', NULL, 'notnp');
            $credit_sum = $stage->getPayoutSum(sbr::FRL);
            $inner = 'stage-completed.php';
            $sbr->getExrates();
            if($action=='complete') {
                if($stage->complete($_POST)) {
                    /* @mark_0013241 */
                    //$sbr->setUserReqvHistory($sbr->data['frl_id'], $stage->data['id'], 1);
                    //$sbr->setUserReqvHistory($sbr->data['emp_id'], $stage->data['id'], 1);
                    $_SESSION["thnx_block{$stage_id}"] = 1;
//                    header_location_exit($_POST['sbr_feedback'] ? "/norisk2/?id={$sbr->id}" : "/norisk2/?site=Stage&id={$stage->id}");
                    header_location_exit("/norisk2/?site=Stage&id={$stage->id}");
                }
            }
            $account = new account();
            $account->GetInfo($sbr->uid);
        }


        break;

    default :
        $is_hidden_newmsg   = $sbr->getIdSBRNewMsg($_SESSION['uid']);
        if($action != 'agree') {
            $sbr_currents = $sbr->getCurrents();
            if(!$sbr_currents) {
                header_location_exit('/promo/sbr/');
                break;
            }
        }
        $sbr->getUserReqvs();
        $rez_type = __paramInit('int', NULL, 'rez_type');
        $rt_checked = !!$rez_type;
        if($rt_disabled = $sbr->checkChangeRT()) {
            if(!($rez_type = $sbr->user_reqvs['rez_type']))
                 $rez_type = sbr::RT_RU; // если не установлен флаг в базе, но checkChangeRT, то считаем, что он руський (т.к. до флага только резиденты были).
        }

        $isReqvsFilled = $sbr->checkUserReqvs();

        if($action == 'agree') {
            
            $agree = __paramInit('bool', NULL, 'ok');
            $refuse = __paramInit('bool', NULL, 'refuse');
            $id = __paramInit('int', NULL, 'id');
            if($agree || $refuse) {
                if(!$sbr->initFromId($id, true, false, false))
                    header_location_exit('/404.php');
                if($agree && $rt_checked && $isReqvsFilled) {
                    $version = __paramInit('int', NULL, 'version');
                    if($rez_type==sbr::RT_UABYKZ) {
                        if($sbr->has_norez_overcost)
                            $sbr->error['rez_type'][$sbr->id] = 1;
                    }
                    if(!$sbr->error) {
                        if($rez_type != $sbr->user_reqvs['rez_type']) {
                            //@todo: запрещаем изменять финансы в старой СБР #29196
                            //sbr_meta::setUserReqv($sbr->uid, $rez_type, $sbr->user_reqvs['form_type'], $rrr, TRUE);
                            $sbr->user_reqvs['rez_type'] = $rez_type;
                        }
                        if($sbr->agree($version)) {
                            $sbr_stage = $sbr->getStages();
                            foreach($sbr_stage as $stage) {
                                $sbr->setUserReqvHistory($sbr->uid, intval($stage->data['id']), 0); // Сохраняем для всех этапов, согласие исполнителя
                            }
                            header_location_exit("/norisk2/?id={$id}");
                        }
                    }
                }
                else if($refuse){
                    $reason = stripslashes($_POST['frl_refuse_reason']); // !!!
                    $reason = substr(pg_escape_string($reason), 0, 512);
                    if($sbr->refuse($reason))
                        header_location_exit("/norisk2/?id={$id}");
                }
            }
        }
        $sbr_currents = $sbr->getCurrents();
        $anchor = __paramInit('int', 'id');
        $_SESSION['sbr_tip_old'] = notifications::getSbrTip('old');
        $sbr->setLastView('old');
        break;
}
