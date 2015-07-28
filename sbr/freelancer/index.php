<?
if(!defined('IN_SBR')) exit;
$fpath = 'freelancer/';
$inner = "tpl.sbr-list.php";

/* @var  $sbr sbr */
switch($site) {
    case 'master':
        $g_help_id = 212;
        $inner = 'tpl.stage-master.php';
        $sbr->initFromId($sbr_id);
        if($sbr->data['is_draft'] == 't') {
            header_location_exit('/404.php');
        }
        if(!count($sbr->data) || ( $sbr->status != sbr::STATUS_REFUSED && $sbr->status != sbr::STATUS_NEW ) ) {
            header_location_exit('/404.php');
        }
        if($sbr->status == sbr::STATUS_REFUSED) {
            $inner = 'tpl.stage-master-refuse.php';
        }
        
        if($action == 'agree') {
            $refuse = __paramInit('bool', NULL, 'refuse');
            $id = __paramInit('int', NULL, 'id');
            if($refuse){
                $reason = __paramInit('string', null, 'frl_refuse_reason'); //stripslashes($_POST['frl_refuse_reason']); // !!!
                $reason = substr(pg_escape_string($reason), 0, 512);
                if($sbr->refuse($reason)) {
                    header_location_exit("/".sbr::NEW_TEMPLATE_SBR."/?site=master&id={$id}");
                }
            }
        }
        
        $all_agree = true;
        $position  = 0;
        $active_stage = current($sbr->stages);
        foreach($sbr->stages as $i => $curr_stage) {
            if($curr_stage->data['frl_agree'] == 'f') {
                $all_agree = false;
                $active_stage = $curr_stage;
                $position = $i + 1;
                break;
            }
        }
        if($all_agree) {
            $active_stage = false;
            $position     = count($sbr->stages);
        }
        $is_filled = explode(',',preg_replace('/[}{]/', '', $sbr->user_reqvs['is_filled']));
        $isReqvsFilled[sbr::FT_PHYS] = $is_filled[sbr::FT_PHYS - 1] == 't';
        $isReqvsFilled[sbr::FT_JURI] = $is_filled[sbr::FT_JURI - 1] == 't';
        $frl_reqvs = sbr_meta::getUserReqvs(get_uid(false));
        $sbr_schemes = $sbr->getSchemes();
        
        $frl_reqvs['form_type'] = sbr::FT_PHYS;
        $sbr_schemes_phys = sbr_meta::jsSchemeTaxes($sbr_schemes, $frl_reqvs, $sbr->getUserReqvs(), sbr::FRL);
        $frl_reqvs['form_type'] = sbr::FT_JURI;
        $sbr_schemes_jury = sbr_meta::jsSchemeTaxes($sbr_schemes, $frl_reqvs, $sbr->getUserReqvs(), sbr::FRL);
        $sbr_schemes = $sbr->getSchemes();
        

//        $frl_reqvs = $sbr->getFrlReqvs();
        if ($sbr->scheme_type == sbr::SCHEME_LC) {
            $sbr->checkEnableMethodPayments();
            $pskb_frl = new pskb_user($sbr->getFrlReqvs(), 0, $sbr->getTotalCost());
            $pskb_frl->setOnlyWW($sbr->is_only_ww);
            $paysystems = $pskb_frl->getPsystems();
        } else {
            $totalSum = 0;
            foreach($sbr->stages as $stg) {
                $totalSum += $stg->calcAllTax(sbr::FRL);
            }
        }
        $RT = $sbr->getRatingSum($sbr->cost, sbr_meta::FRL_PERCENT_TAX);
        
        $stages_ids = array_map(create_function('$a', 'return $a->data["id"];'), $sbr->stages);
        break;
    case 'Stage' :
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/attachedfiles.php");
        $inner = 'stage.php';
        $stage_id  = __paramInit('int', 'id', 'id');
        if(!($stage = $sbr->initFromStage($stage_id)))
            break;
        if($stage->sbr->status == sbr::STATUS_CLOSED && $action != 'msg-add') $action = ""; //TODO Сомнительный момент
        $pskb = new pskb($sbr);
        $lc = $pskb->getLC();
        // код для страницы помощи
        $g_help_id = 219;
        if ($stage->data['lc_state'] == pskb::STATE_PASSED) {
            $g_help_id = 217;
        };
        
        $is_filled = explode(',',preg_replace('/[}{]/', '', $sbr->user_reqvs['is_filled']));
        $isReqvsFilled[sbr::FT_PHYS] = $is_filled[sbr::FT_PHYS - 1] == 't';
        $isReqvsFilled[sbr::FT_JURI] = $is_filled[sbr::FT_JURI - 1] == 't';
        
        $attachedfiles = new attachedfiles($_POST['attachedfiles_session']);
        $comment_files = array();
        
        $feedback_sent = isset($_SESSION["thnx_block{$stage_id}"]);
        if ($feedback_sent) {
            unset($_SESSION["thnx_block{$stage_id}"]);
        } 

        if($action == 'frl_refund') {
            if($stage->refund()) {
                sbr_notification::setNotificationCompleted(array('sbr_stages.FRL_PAID', 'sbr_stages.DOC_RECEIVED', 'sbr_stages.FRL_FEEDBACK'), $stage->data['sbr_id'], $stage->data['id']);
                header_location_exit("/".sbr::NEW_TEMPLATE_SBR."/?site=Stage&id={$stage->data['id']}");
            }
        }
        
        if($action == 'agree_stage') {
            $ok = __paramInit('bool', NULL, 'ok');
            $version = __paramInit('int', NULL, 'version');
            $sbr_version = __paramInit('int', NULL, 'sbr_version');
            if($ok) {
               if($stage->agreeChanges($version, $sbr_version))
                   header_location_exit("/".sbr::NEW_TEMPLATE_SBR."/?site=Stage&id={$stage->data['id']}");
            }
            else {
               $reason = __paramInit('string', null, 'frl_refuse_reason');//stripslashes($_POST['frl_refuse_reason']); // !!!
               $reason = substr(pg_escape_string($reason), 0, 512);
               if($stage->refuseChanges($version, $reason, $sbr_version))
                   header_location_exit("/".sbr::NEW_TEMPLATE_SBR."/?site=Stage&id={$stage->data['id']}");
            }
        }

        if($stage->status != sbr_stages::STATUS_INARBITRAGE && $stage->status != sbr_stages::STATUS_ARBITRAGED) { // !!! можно убрать условия.
            $g_help_id = 223;
            if($sbr->status == sbr::STATUS_CHANGED && ($stage_changed = ($stage->frl_version && ($stage->frl_version < $stage->version || $sbr->frl_version < $sbr->version)))) {
                $sbr->v_data = $sbr->data;
                $stage->v_data = $stage->data;
                if($sbr->frl_version   < $sbr->version)   $sbr->v_data = $sbr->getVersion($sbr->frl_version, $sbr->data);
                if($stage->frl_version < $stage->version) $stage->v_data = $stage->getVersion($stage->frl_version, $stage->data);
            }
        }
        if($stage->status == sbr_stages::STATUS_INARBITRAGE || $stage->status == sbr_stages::STATUS_ARBITRAGED) {
            $frl_version = $stage->getVersion($stage->frl_version, $stage->data);
            //$stage->data['descr'] = $frl_version['descr'];
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
            $g_help_id = 216;
            $in_completed = true;
            $notnp = __paramInit('bool', NULL, 'notnp');
            $credit_sum = $stage->getPayoutSum(sbr::FRL);
            //$inner = 'stage-completed.php';
            $sbr->getExrates();
            if($action=='complete') {
                $ops_type = $_POST['feedback']['ops_type']!="" ? intval($_POST['feedback']['ops_type']) : null;
                if ($sbr->scheme_type == sbr::SCHEME_LC) {
                    $_POST['credit_sys'] = pskb::$exrates_map[$sbr->isEmp() ? $lc['ps_emp'] : $lc['ps_frl']];
                    if($stage->completeAgnt($_POST)) {
                        $_SESSION["thnx_block{$stage_id}"] = 1;
                        header_location_exit("/".sbr::NEW_TEMPLATE_SBR."/?site=Stage&id={$stage->id}");
                    }
                } else {
                    if($stage->complete($_POST)) {
                        /* @mark_0013241 */
                        //$sbr->setUserReqvHistory($sbr->data['frl_id'], $stage->data['id'], 1);
                        //$sbr->setUserReqvHistory($sbr->data['emp_id'], $stage->data['id'], 1);
                        $_SESSION["thnx_block{$stage_id}"] = 1;
    //                    header_location_exit($_POST['sbr_feedback'] ? "/norisk2/?id={$sbr->id}" : "/norisk2/?site=Stage&id={$stage->id}");
                        header_location_exit("/".sbr::NEW_TEMPLATE_SBR."/?site=Stage&id={$stage->id}");
                    }
                }
            }
            $account = new account();
            $account->GetInfo($sbr->uid);
        }


        break;
        
    case 'agreed':
        
        $sbrID = __paramInit('int', 'sbr_id', null);
        if (!$sbrID) {
            header_location_exit('/404.php');
        }

        $sbr->initFromId($sbrID);
        
        // чтобы нельзя было смотреть чужих сделок
        if ($sbr->data['frl_id'] != get_uid(0)) {
            header_location_exit('/404.php');
        }
        
        $crumbs = array(
            0 => array(
                'href' => '/' . sbr::NEW_TEMPLATE_SBR . '/', 
                'name' => '«Мои Сделки»'
            ),
            1 => array(
                'href' => '',
                'name' => $sbr->data['name'],
            ),
        );
        
        $inner = $fpath.'tpl.agreed.php';
        
        break;
    case 'archive':
        $sbr->setGetterSchemes(0);
        $is_hidden_newmsg   = $sbr->getIdSBRNewMsg($_SESSION['uid']);
        $isReqvsFilled = $sbr->checkUserReqvs();
        $sbr_currents = $sbr->getCurrents();
        $anchor = __paramInit('int', 'id');
        $_SESSION['sbr_tip_old'] = notifications::getSbrTip('old');
        $sbr->setLastView('old');
        if(!$css_file) $css_file = array();
        if($css_file && !is_array($css_file)) $css_file = array($css_file);
        array_push($css_file, 'norisk-user.css', 'nav.css');
        $included = $_SERVER['DOCUMENT_ROOT'] . "/norisk2/freelancer/currents.php";
        break;
    default: 
        $is_hidden_newmsg   = $sbr->getIdSBRNewMsg($_SESSION['uid']);
        $filter = __paramInit('string', 'filter');
        if($action != 'agree') {
            $sbr_currents = $sbr->_new_getCurrents($filter);
            $now_count    = sizeof($sbr_currents);
            $count_sbr    = $sbr->getCountCompleteSbr();
            $now_count    = ($now_count - $sbr->new_count - 1 );
            // Тут проверка если сделок нет вообще, то есть если нет сделок и нет скрытых сделок тогда мы посылаем в промо
            if(!$sbr_currents && !$count_sbr && !$filter && !$count_old_sbr) { // при активации фильтра будем показывать страницу
                header_location_exit('/promo/' . sbr::NEW_TEMPLATE_SBR . '/');
                break;
            }
        }
        $sbr->getUserReqvs();
        $rez_type = __paramInit('int', NULL, 'rez_type');
        $rt_checked = !!$rez_type;
        $form_type = __paramInit('int', NULL, 'form_type');
        $ft_checked = !!$form_type;
        if (!$ft_checked) {
            $form_type = $sbr->user_reqvs['form_type'];
            $ft_checked = true;
        }
        if($rt_disabled = $sbr->checkChangeRT()) {
            if(!($rez_type = $sbr->user_reqvs['rez_type']))
                 $rez_type = sbr::RT_RU; // если не установлен флаг в базе, но checkChangeRT, то считаем, что он руський (т.к. до флага только резиденты были).
        }

        $isReqvsFilled = $sbr->checkUserReqvs(null, $form_type);
        
        if($action == 'agree') {
            $agree = __paramInit('bool', NULL, 'ok');
            $refuse = __paramInit('bool', NULL, 'refuse');
            $id = __paramInit('int', NULL, 'id');
            if($agree || $refuse) {
                if(!$sbr->initFromId($id, true, false, false))
                    header_location_exit('/404.php');
                
                /**
                 * схема работы через банк
                 */
                if ($sbr->data['scheme_type'] == sbr::SCHEME_LC) {
                    $psys = __paramInit('int', NULL, 'mode_type');
                    
                    $frl_reqvs = $sbr->user_reqvs;
                    $frl_reqvs['rez_type'] = $rez_type;
                    $frl_reqvs['form_type'] = $form_type;
                    $pskb_frl = new pskb_user($frl_reqvs, 0);
                    
                    $isReqvsFilled = !$pskb_frl->checkPsys($psys, $form_type);
                }
                
//                var_dump($agree, $rt_checked, $isReqvsFilled, $ft_checked);
//                die();
                
                if($agree && $rt_checked && $isReqvsFilled && $ft_checked) {
                    $version = __paramInit('int', NULL, 'version');
                    if($rez_type==sbr::RT_UABYKZ) {
                        if($sbr->has_norez_overcost)
                            $sbr->error['rez_type'][$sbr->id] = 1;
                    }
                    
                    if(!$sbr->error) {
                        if($rez_type != $sbr->user_reqvs['rez_type'] || $form_type != $sbr->user_reqvs['form_type']) {
                            //@todo: запрещаем изменять финансы в старой СБР #29196
                            //sbr_meta::setUserReqv($sbr->uid, $rez_type, $form_type, $rrr, false);
                            $sbr->user_reqvs['rez_type'] = $rez_type;
                            $sbr->user_reqvs['form_type'] = $form_type;
                        }
                        if($sbr->data['scheme_type'] == sbr::SCHEME_PDRD2) {
                            foreach($sbr->stages as $_stage) {
                                $_stage->setUserReqvHistory($sbr->data['frl_id'], $_stage->id, 0);
                            }
                        }
                        
                        if ($sbr->data['scheme_type'] == sbr::SCHEME_LC) {
                            $ps = __paramInit('int', NULL, 'mode_type');
                            $pskb = new pskb($sbr);
                            $res = $pskb->prepareLC($ps);
                            
                            if (!$res) {
                                header_location_exit("/".sbr::NEW_TEMPLATE_SBR."/?id={$id}");
                                break;
                            }
                        }
                        if($sbr->agree($version)) {
                            $type_payment = __paramInit('int', NULL, 'type_payment');
                            if($type_payment > 0) {
                                $sbr->setTypePayment($type_payment);
                            }
                            $sbr_stage = $sbr->getStages();
                            foreach($sbr_stage as $stage) {
                                $sbr->setUserReqvHistory($sbr->uid, intval($stage->data['id']), 0); // Сохраняем для всех этапов, согласие исполнителя
                            }
                            //header_location_exit("/".sbr::NEW_TEMPLATE_SBR."/?id={$id}");
                            header_location_exit("/" . sbr::NEW_TEMPLATE_SBR . "/?site=agreed&sbr_id=$id");
                        }
                    }
                }
                else if($refuse){
                    $reason = __paramInit('string', null, 'frl_refuse_reason'); //stripslashes($_POST['frl_refuse_reason']); // !!!
                    $reason = substr(pg_escape_string($reason), 0, 512);
                    if($sbr->refuse($reason))
                        header_location_exit("/".sbr::NEW_TEMPLATE_SBR."/?id={$id}");
                }
            }
        }
        
        $anchor = __paramInit('int', 'id');
        
        $anchor = __paramInit('int', 'id');
        $_SESSION['sbr_tip'] = notifications::getSbrTip();
        $sbr->setLastView();
        break;
}
