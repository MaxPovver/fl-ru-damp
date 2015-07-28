<?
if(!defined('IN_SBR')) exit;
$fpath = 'employer/';
$inner = "tpl.sbr-list.php";

switch($site) {
    case 'create' :
        $g_help_id = 211;
        $notFilled = true;
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/attachedfiles.php");
        $action_form = '?site=create';
        $sbr_drafts = $sbr->getDraftsList(3);
        
        $attachedfiles = new attachedfiles($_POST['attachedfiles_session']);
        $stages_files = array();

        $inner = $fpath.'tpl.create.php';
        $js_file = array('sbr3.js', 'attachedfiles2.js' );
        $sbr->stages = array(new sbr_stages($sbr));
        $sbr->data['scheme_type'] = sbr::SCHEME_DEFAULT;
        $sbr->getScheme();
        if($sbr->user_reqvs['rez_type']) {
            $rez_type = $sbr->user_reqvs['rez_type'];
        } else {   
            $rez_type = __paramInit('int', NULL, 'rez_type');
        }
        $rt_checked = !!$rez_type;
        if($rt_disabled = $sbr->checkChangeRT()) {
            if(!($rez_type = $sbr->user_reqvs['rez_type']))
                 $rez_type = sbr::RT_RU; // если не установлен флаг в базе, но checkChangeRT, то считаем, что он руський (т.к. до флага только резиденты были).
        }
        
        if($action == 'create' && $rt_checked) {
            if($_POST['scheme_type'] == sbr::SCHEME_PDRD2) {
                header("Location: /" . sbr::NEW_TEMPLATE_SBR . " /?site=create");
                exit();
            }
            
            if($rez_type != $sbr->user_reqvs['rez_type']) {
                //@todo: запрещаем изменять финансы в старой СБР #29196
                //sbr_meta::setUserReqv($sbr->uid, $rez_type, $sbr->user_reqvs['form_type'], $rrr, TRUE);
                $sbr->user_reqvs['rez_type'] = $rez_type;
            }
            $_POST['cost_sys'] = exrates::BANK; // По умолчанию
            if($sbr->_new_create($_POST, $attachedfiles)) {
                if ($sbr->isDraft()) {
                    header_location_exit('/' . sbr::NEW_TEMPLATE_SBR . '/?site=drafts');
                }
                
                // переход на страницу прокладку                
                header_location_exit('/' . sbr::NEW_TEMPLATE_SBR . '/?site=created&sbr_id=' . $sbr->id);
            } else {
                foreach($sbr->stages as $k => $stage) {
                    if (!is_array($stage->data['attached']) && !count($stage->data['attached'])) continue;
                    $farr = $stage->data['attached'];
                    foreach ($farr as $i => $v) {
                        $v['id'] = md5($v['id']);
                        $farr[$i] = $v;
                    }
                    $stages_files[$k] = $farr;
                }
            }
        }
        
        if($stages_files)
            $stages_files = attachedfiles::getInitJSONContentSBRFiles($stages_files);
            
        if($frl_id = __paramInit('int', 'fid')) {
            $frl = new freelancer();
            $frl->GetUserByUID($frl_id);
            if($frl->uid) {
                $sbr->data['frl_id'] = $frl_id;
                $frl_reqvs = sbr_meta::getUserReqvs($frl_id);
            }
        }
        
        if($prj_id = __paramInit('int', 'pid')) {
            $exec_id = __paramInit('int', 'fid');
            $prj_init = $sbr->initFromProject($prj_id, $exec_id);
            
            if(!$prj_init) {
                unset($sbr->error['project_id']);
                //header_location_exit('/404.php');
            }
        } 
        elseif($tu_id = __paramInit('int','tuid'))
        {
            $tu_init = $sbr->initFromTService($tu_id, $_POST);
            
            //print_r($sbr->error);
            //exit;
            
             //if($tu_init) {
             //   unset($sbr->error['project_id']);
                //header_location_exit('/404.php');
            //}           
        }
        
        
        if($sbr->frl_id) {
            $frl = new freelancer();
            $frl->GetUserByUID($sbr->frl_id);
            // если фрилансера с таким uid не существует
            if ($frl->uid) {
                if(!$sbr->frl_login) $sbr->data['frl_login'] = $frl->login;
                if($frl_reqvs = sbr_meta::getUserReqvs($frl->uid)) {
                    $frl_ftype = (int)$frl_reqvs['form_type'];
                    $frl_rtype = $frl_reqvs['rez_type'];
                }
            }
        }
        
        
        if (!is_array($sbr->data['professions']) || !count($sbr->data['professions'])) {
            $sbr->data['professions'] = array();
            $sbr->data['professions'][] = array(
                'default'           => 0,
                'default_column'    => 0,
                'prof_name'         => ''
            );
        } else {
            foreach ($sbr->data['professions'] as &$prof) {
                if ($prof['subcategory_id']) {
                    $prof['default'] = $prof['subcategory_id'];
                    $prof['default_column'] = 1;
                } else {
                    $prof['default'] = $prof['category_id'];
                    $prof['default_column'] = 0;
                }
            }
            unset($prof);
        }
        
        $sbr_schemes = $sbr->getSchemes();
        break;
        
    case 'created':
        
        $sbrID = __paramInit('int', 'sbr_id', null);
        if (!$sbrID) {
            header_location_exit('/404.php');
        }

        $sbr->initFromId($sbrID);
        
        // чтобы нельзя было смотреть чужих сделок
        if ($sbr->data['emp_id'] != get_uid(0)) {
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
        
        $inner = $fpath.'tpl.created.php';
        
        break;

    case 'editstage' :
        $g_help_id = 213;
        
        $inner = $fpath.'tpl.create.php';
        $js_file = array('sbr3.js', 'attachedfiles2.js' );
        $stage_id = __paramInit('int', 'id', 'stage_id');
        $version = __paramInit('bool', 'v', 'v');
        
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/attachedfiles.php");
        require_once ($_SERVER['DOCUMENT_ROOT'] . '/classes/pskb.php');
        $attachedfiles = new attachedfiles($_POST['attachedfiles_session']);
        
        $stage = $sbr->initFromStage($stage_id);
        if($stage->status == sbr_stages::STATUS_NEW && $sbr->reserved_id > 0) {
            header_location_exit('/404.php');
        }
        $action_form = '?site=editstage&id=' . $stage_id;
        
        $attachedfiles_files = $attachedfiles->getFiles();
        if (!$_POST['attachedfiles_session']) {
            $stage_files = array();
            
            if (count($stage->attach)) {
                foreach($stage->attach as $k => $file) {
                    $stage_files[] = $file['file_id'];
                }
                
                if (count($stage_files)) {
                    $attachedfiles->setFiles($stage_files);
                    $attachedfiles_files = $attachedfiles->getFiles(array(1,3), false, true);
                }
            }
        }
        $stages_files = array($attachedfiles_files);
        // Подготовка данных для JSON
        if($stages_files)
            $stages_files = attachedfiles::getInitJSONContentSBRFiles($stages_files); 
        
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
        $history_frl = $sbr->getUserReqvHistory($stage->id, $sbr->frl_id); // если записалась история значит исполнитель согласился на сделку
        $not_type_changed = (!$sbr->reserved_id && ( $sbr->pskb_pl_id > 0 || $history_frl ) );
        if($action == 'editstage') {
            if($not_type_changed) {
                $_POST['scheme_type'] = $sbr->scheme_type;
            }
            if($_POST['cancel'] || $sbr->_new_edit($_POST, $attachedfiles)) {
                sbr_notification::setNotificationCompleted(array('sbr_stages.REFUSE', 'sbr_stages.FRL_FEEDBACK'), $stage->data['sbr_id'], $stage_id);
                sbr_notification::setNotificationCompleted('sbr.FRL_FEEDBACK', $sbr->data['id'], $sbr->data['id']);
                header_location_exit("/".sbr::NEW_TEMPLATE_SBR."/?site=Stage&id={$stage_id}");
            }
        } else {
            $sbr->retrieveProfession();
        }
        
        if ($sbr->reserved_id) {
            $pskb = new pskb($sbr);
            $data = $pskb->getLCInfo();
            $lc = $data['lc'];
        }
        
        break;


    case 'edit' :
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/attachedfiles.php");
        
        $attachedfiles = new attachedfiles($_POST['attachedfiles_session']);
        
        $inner = $fpath.'tpl.create.php';
        $js_file = array('sbr3.js', 'attachedfiles2.js' );
        if($sbr_id = __paramInit('int', 'id', 'id', 0))
            $sbr->initFromId($sbr_id);
        if(!$sbr_id || $sbr->error[404] || $sbr->status == sbr::STATUS_COMPLETED || !$sbr->data)
            header_location_exit('/404.php');
        
        $action_form = '?site=edit&id=' . $sbr_id;
        
        if(!$sbr->data['frl_id'] || $sbr->data['name'] == '') {
            $notFilled = true;
        }
        $notFilledValues = array('name', 'descr', 'work_days', 'cost');
        foreach($sbr->stages as $k=>$stage) { 
            $aNull = array_keys(array_filter($stage->data, create_function('$a', 'return ($a == "" || $a == null);'))); // Фильтруем и выдаем только незаполненные данные
            $vals  = array_intersect($notFilledValues, $aNull);
            if(sizeof($vals) > 0) { // Проверяем если из отфильтрованых обязательные поля
                $notFilled = true;
                break; // Если есть дальше можно не проверять
            }
        }
        
        $attachedfiles_files = $attachedfiles->getFiles();
        $stages_files = array($attachedfiles_files);

        if (!$_POST['attachedfiles_session']) {
            $stage_files = array();
            // $k - выступает как ключ к сессии файлов
            foreach($sbr->stages as $k=>$stage) {
                if($stage->data['attach']) {
                    if($k > 0) $attachedfiles->addNewSession();  // Первая сессия у нас генерируется вверху
                    $stage_files = $attachedfiles_files = array();
                    foreach ($stage->data['attach'] as $i => $v) {
                        $stage_files[] = $v['file_id'];
                        $v['id']   = md5($v['file_id']);
                        $v['type'] = $v['ftype'];
                        $v['tsize'] = ConvertBtoMB($v['size']);
                        $v['status'] = 3;
                        $attachedfiles_files[] = $v;
                    }
                    $attachedfiles->setFiles($stage_files, 3, $k);
                    $stages_files[$k] = $attachedfiles_files;
                }
            }
        }
        // Подготовка данных для JSON
        if($stages_files)
            $stages_files = attachedfiles::getInitJSONContentSBRFiles($stages_files);
        
        $sbr_schemes = $sbr->getSchemes();
        $rt_checked = true;
        $rt_disabled = $sbr->reserved_id || $sbr->checkChangeRT();
        if(!($rez_type = $sbr->user_reqvs['rez_type']))
             $rez_type = sbr::RT_RU;
        $history_frl = $sbr->getUserReqvHistory($sbr->stages[0]->id, $sbr->frl_id);
        $not_type_changed = (!$sbr->reserved_id && ( $sbr->pskb_pl_id > 0 || $history_frl ) );
        if($action == 'edit') {
            if($not_type_changed) {
                $_POST['scheme_type'] = $sbr->scheme_type;
            }
            if($sbr->data['is_draft'] == 't') {
                $_POST['scheme_type'] = sbr::SCHEME_LC;
            }
            if($_POST['cancel'] || $sbr->_new_edit($_POST, $attachedfiles)) {
                $ok = true;
                if($_POST['sended'] && ($sbr->status==sbr::STATUS_CANCELED || $sbr->status==sbr::STATUS_REFUSED))
                    $ok = $sbr->resendCanceled($sbr->id);
                if($ok)
                    header_location_exit($sbr->isDraft() ? '/'.sbr::NEW_TEMPLATE_SBR.'/?site=drafts' : "/".sbr::NEW_TEMPLATE_SBR."/?id={$sbr->id}");
            }
        }
        
        if ($sbr->data['is_draft'] === 't') {
            $sbr_drafts = $sbr->getDraftsList(3, $sbr->data['id']);
        }
        
        break;


    case 'new' :
        $inner = $fpath.'tpl.create-new.php';
        $projects_cnt = projects::CountMyProjects($sbr->uid, false, true);
        if($projects_cnt['open'] == 0) {
            header_location_exit('/' . sbr::NEW_TEMPLATE_SBR . '/?site=create');
        }
        break;


    case 'Stage' :
        $g_help_id = 218;
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/attachedfiles.php");
        
        $inner = 'stage.php';
        $stage_id  = __paramInit('int', 'id', 'id');
        if(!($stage = $sbr->initFromStage($stage_id)))
            break;
        if($stage->sbr->status == sbr::STATUS_CLOSED && action != 'msg-add') $action = "";
        $is_filled = explode(',',preg_replace('/[}{]/', '', $sbr->user_reqvs['is_filled']));
        $isReqvsFilled[sbr::FT_PHYS] = $is_filled[sbr::FT_PHYS - 1] == 't';
        $isReqvsFilled[sbr::FT_JURI] = $is_filled[sbr::FT_JURI - 1] == 't';
        
        $attachedfiles = new attachedfiles($_POST['attachedfiles_session']);
        $comment_files = array();
        
        $feedback_sent = isset($_SESSION["thnx_block{$stage_id}"]);
        if ($feedback_sent) {
            unset($_SESSION["thnx_block{$stage_id}"]);
        } 
        
        if($action == 'emp_refund') {
            if($stage->refund('sbr_stages.EMP_MONEY_REFUNDED', 2)) {
                sbr_notification::setNotificationCompleted(array('sbr_stages.EMP_PAID'), $stage->data['sbr_id'], $stage->data['id']);
                header_location_exit("/".sbr::NEW_TEMPLATE_SBR."/?site=Stage&id={$stage->data['id']}");
            }
        }
        
        if($action == 'resolve_changes') {
            $resend = __paramInit('bool', NULL, 'resend');
            $cancel = __paramInit('bool', NULL, 'cancel');
            $version = __paramInit('int', NULL, 'version');
            if($resend) {
               if($stage->resendChanges())  // !!!
                   header_location_exit("/".sbr::NEW_TEMPLATE_SBR."/?site=Stage&id={$stage->data['id']}");
            }
            else if($cancel) {
               if($stage->cancelChanges())
                   header_location_exit("/".sbr::NEW_TEMPLATE_SBR."/?site=Stage&id={$stage->data['id']}");
            }
        }
        if($action == 'change_status') {
            $status = __paramInit('int', NULL, 'status');
            $g_help_id = 215;
            $in_completed = true;
            $credit_sum = $stage->getPayoutSum(sbr::EMP);
            //$inner = 'stage-completed.php';
            if(($_POST['feedback'] || $_POST['sbr_feedback']) && $status == sbr_stages::STATUS_COMPLETED) {
                $ok = true;
                $ops_type = $_POST['feedback']['ops_type']!="" ? intval($_POST['feedback']['ops_type']) : null;
                if($_POST['feedback']) {
                    $ok = $stage->feedback($_POST['feedback'], $_POST['sbr_feedback']['descr'] != '' ? $_POST['sbr_feedback'] : null);
                } else if($_POST['sbr_feedback']) {
                    $ok = $sbr->feedback($_POST['sbr_feedback']);
                }
//                if($ok) {
//                    $_SESSION["thnx_block{$stage_id}"] = 1;
//                    header_location_exit("/".sbr::NEW_TEMPLATE_SBR."/?site=Stage&id={$stage->id}");
//                }
            } else {
                $ok = true;
            }

            if($ok) {
                if($sbr->data['reserved_id'] && ( (int) $status == sbr_stages::STATUS_NEW )) {
                    header_location_exit("/".sbr::NEW_TEMPLATE_SBR."/?site=Stage&id={$stage->data['id']}");
                }
                $day = 0;
                if($status == sbr_stages::STATUS_FROZEN) { // Ставим на паузу
                    $day = __paramInit('int', NULL, 'days');
                    if($day <= 0) $day = 1;
                    if($day > 30) $day = 30;
                }

                if($stage->changeStatus($status, $day)) {
                    /* @mark_0013241 */
                    if($status == sbr_stages::STATUS_PROCESS ) {
                        $stage->setUserReqvHistory($stage->sbr->data['frl_id'], $stage->data['id'], 0);
                        $stage->setUserReqvHistory($stage->sbr->data['emp_id'], $stage->data['id'], 0);
                    }
                    header_location_exit("/".sbr::NEW_TEMPLATE_SBR."/?site=Stage&id={$stage->data['id']}");
                }
            }
        }
        if($stage->status != sbr_stages::STATUS_INARBITRAGE && $stage->status != sbr_stages::STATUS_ARBITRAGED) {
            $g_help_id = 222;
            
            foreach($sbr->stages as $key=>$st) {
                if($st->frl_version > $st->version && ($sbr->status == sbr::STATUS_PROCESS || $sbr->status == sbr::STATUS_CHANGED)) {
                    $sbr_changed = true;
                }
            }
            
            $stage_changed_for_frl = (($sbr->status == sbr::STATUS_PROCESS || $sbr->status == sbr::STATUS_CHANGED) && $stage->frl_version && ($stage->frl_version < $stage->version || $sbr->frl_version < $sbr->version));
            
            if($stage_changed_for_frl) {
                $sbr->v_data = $sbr->data;
                $stage->v_data = $stage->data;
                if($sbr->frl_version   < $sbr->version)   $sbr->v_data = $sbr->getVersion($sbr->frl_version, $sbr->data);
                if($stage->frl_version < $stage->version) $stage->v_data = $stage->getVersion($stage->frl_version, $stage->data);
            }
            
            if(($sbr->status == sbr::STATUS_PROCESS || $sbr->status == sbr::STATUS_CHANGED) && ($stage_changed = ($stage->frl_version > $stage->version || $sbr->frl_version > $sbr->version))) {
                $sbr->v_data = $sbr->data;
                $stage->v_data = $stage->data;
                if($sbr->frl_version   > $sbr->version)   { $sbr->data = $sbr->getVersion($sbr->version, $sbr->v_data); $sbr->getScheme(); }
                if($stage->frl_version > $stage->version) $stage->data = $stage->getVersion($stage->version, $stage->v_data);
            }
            // Если деньги зарезервированы то в черновик нельзя
            if($action == 'draft' && !$sbr->data['reserved_id']) {
                if($sbr->frl_id > 0) {
                    $sbr->sbrCanceledSaveEvent();
                }
                if($sbr->draft($sbr->id)) {
                    $pskb = new pskb($sbr);
                    $lc = $pskb->getLC();
                    if($lc['state'] == 'new') {
                        $pskb->_closeLC($lc['lc_id']);
                    }
                    
                    header_location_exit('/' . sbr::NEW_TEMPLATE_SBR . '/?site=drafts');
                }
            }
        }
        if($stage->status == sbr_stages::STATUS_INARBITRAGE || $stage->status == sbr_stages::STATUS_ARBITRAGED) {
            $frl_version = $stage->getVersion($stage->frl_version, $stage->data);
            //$stage->data['descr'] = $frl_version['descr'];
        }
        if( ($stage->status == sbr_stages::STATUS_COMPLETED && !$stage->emp_feedback_id)
           || ($sbr->status == sbr::STATUS_COMPLETED && $stage->emp_feedback_id && !$sbr->emp_feedback_id) 
        )
        {

            $g_help_id = 215;
            $in_completed = true;
            $credit_sum = $stage->getPayoutSum(sbr::EMP);
            //$inner = 'stage-completed.php';
            if($action=='complete' && ($_POST['feedback'] || $_POST['sbr_feedback'])) {
                $ok = true;
                $ops_type = $_POST['feedback']['ops_type']!="" ? intval($_POST['feedback']['ops_type']) : null;
                if($_POST['feedback'] && $stage->isAccessOldFeedback())
                    $ok = $stage->feedback($_POST['feedback'], $_POST['sbr_feedback']['descr'] != '' ? $_POST['sbr_feedback'] : null);
                else if($_POST['sbr_feedback']) {
                    $ok = $sbr->feedback($_POST['sbr_feedback']);
                }
                if($ok) {
                    $_SESSION["thnx_block{$stage_id}"] = 1;
                    header_location_exit("/".sbr::NEW_TEMPLATE_SBR."/?site=Stage&id={$stage->id}");
                }
            }
        }


        break;

    case 'drafts' :
        $g_help_id = 221;
        if($action=='multiset') {
            if($_POST['send']) {
                if($id=$sbr->send($_POST['id']))
                    header_location_exit("/".sbr::NEW_TEMPLATE_SBR."/?id={$id}");
            }
            else if($_POST['delete']) {
                if($sbr->delete($_POST['id']))
                    header_location_exit("/".sbr::NEW_TEMPLATE_SBR."/?site=drafts");
            }
        }
        if($sbr_drafts = $sbr->getDrafts()) {
            $inner = '/employer/tpl.sbr-drafts.php';
            $sbr_count = count($sbr_drafts);
        } else
            header_location_exit('/'.sbr::NEW_TEMPLATE_SBR.'/');
        break;
    case 'invoiced':
        if ($sbr_id = __paramInit('int', 'id', 'id', 0))
            $sbr->initFromId($sbr_id);
        if (!$sbr_id || $sbr->error[404] || $sbr->status == sbr::STATUS_COMPLETED || !$sbr->data)
            header_location_exit('/404.php');
        
        $account = new account();
        $account->GetInfo($sbr->uid);
        $sbr->getReserveSum();
        $_POST['sum']  = $sbr->reserve_sum;
        $print = __paramInit('int', 'print', 0);
        $sbr->showInvoicedAgnt($account, $print ? 'print' : '');
        exit;
        
        break;
    case 'reserve':
        $g_help_id = 214;
        if ($sbr_id = __paramInit('int', 'id', 'id', 0))
            $sbr->initFromId($sbr_id);
        if (!$sbr_id || $sbr->error[404] || $sbr->status == sbr::STATUS_COMPLETED || !$sbr->data)
            header_location_exit('/404.php');
        
        $pskb = new pskb($sbr);
        $lc = $pskb->getLC();
        
        if (in_array($lc['state'], array(pskb::STATE_NEW, pskb::STATE_FORM)) && $lc['state'] != pskb::STATE_ERR) {
            $sbr->getDocs();
            if($sbr->docs) {
                foreach($sbr->docs as $doc) {
                    if($doc['type'] == sbr::DOCS_TYPE_STATEMENT) {
                        $doc_file = new CFile($doc['file_id']);
                        $doc_file->original_name = $doc['name'];
                    }
                }
            }
            //$inner = $fpath . 'tpl.pskb-state-new.php';
            $inner = $fpath . 'tpl.pskb-state-2.php';
            break;
        }
        
        if ($sbr->reserved_id && $sbr->status == sbr::STATUS_CHANGED) {
            $inner = $fpath . 'tpl.reserve-success.php';
            break;
        }
        
        
        
        if (!($sbr->status == sbr::STATUS_PROCESS && $sbr->stages_version == $sbr->frl_stages_version && $sbr->version == $sbr->frl_version && !$sbr->reserved_id)) { 
            $stageID = $sbr->stages[0]->data['id'];
            //header_location_exit('/'.sbr::NEW_TEMPLATE_SBR.'/?id=' . $sbr->id);
            header_location_exit('/'.sbr::NEW_TEMPLATE_SBR."/?site=Stage&id=$stageID");
        }
        
        $account = new account();
        $account->GetInfo($sbr->uid);

        if($sbr->scheme_type == sbr::SCHEME_PDRD2) {
            if (!$js_file) {
                $js_file = array();
            }
            $js_file[] = 'attachedfiles2.js';
            $emp_reqvs = sbr_meta::getUserReqvs(get_uid(false));
            $sbr_schemes = $sbr->getSchemes();
            $frl_reqvs  = $sbr->getUserReqvHistory($sbr->stages[0]->id, $sbr->frl_id);
            $frl_reqvs  = $frl_reqvs['b'];
            $emp_reqvs['form_type'] = sbr::FT_PHYS;
            $sbr_schemes_phys = sbr_meta::jsSchemeTaxes($sbr_schemes, $frl_reqvs, $sbr->getUserReqvs(), sbr::EMP);
            $emp_reqvs['form_type'] = sbr::FT_JURI;
            $sbr_schemes_jury = sbr_meta::jsSchemeTaxes($sbr_schemes, $frl_reqvs, $sbr->getUserReqvs(), sbr::EMP);
            
            
            $is_filled = explode(',',preg_replace('/[}{]/', '', $emp_reqvs['is_filled']));
            $isReqvsFilled[sbr::FT_PHYS] = $is_filled[sbr::FT_PHYS - 1] == 't';
            $isReqvsFilled[sbr::FT_JURI] = $is_filled[sbr::FT_JURI - 1] == 't';
            
            
            $sbr->getReserveSum();
            $bank  = __paramInit('int', 'bank', 'bank');
            $inner = $fpath . 'tpl.reserve'.($bank && !$no_reserve ? '-bn' : '').'.php';
            if($bank && !$no_reserve) {
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/num_to_word.php");
                $form_type = __paramInit('int', 'ft', 'form_type', $sbr->user_reqvs['form_type']);
                $reqv_mode = __paramInit('int', 'rm', 'reqv_mode', 1);
                $save_finance = __paramInit('bool', NULL, 'save_finance');
                if($action=='invoice') {
                    if($sbr->invoiceBank($form_type, $_POST, $account)) {
                        header_location_exit("/" . sbr::NEW_TEMPLATE_SBR . "/?site=reserve&id={$sbr->id}&bank=1&ft={$form_type}&action=show_invoiced");
                    }
                } else if($action=='show_invoiced') {
                    $sbr->showInvoiced($form_type, $account);
                }
                $sbr->getInvoiceReqv($form_type, $reqv_mode);
            }
            
            $sbr->getReserveSum();
            $action = __paramInit('string', 'action', 'action'); 
            $no_reserve = 0;
            if ($sbr->reserve_sum * $sbr->cost2rur() < sbr_stages::MIN_COST_RUR) {
                $no_reserve = 1;
            }
            if($action=='test_reserve' && !$no_reserve) {
                $cost_sys = __paramInit('int', 'cost_sys', 'cost_sys');
                $sbr->setCostSys($cost_sys);
                if($sbr->testReserve($account)) {
                    header_location_exit("/" . sbr::NEW_TEMPLATE_SBR . "/?site=Stage&id={$stage->id}");
                }
            }
            break;
        }
        
        
        $emp_reqvs = $sbr->getEmpReqvs();
        $pskb_emp = new pskb_user($emp_reqvs, 1);
        $paysystems = $pskb_emp->getPsystems();

        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/onlinedengi.php");

        $js_file = array('sbr3.js');

        $bank = __paramInit('int', 'bank', 'bank');
        $inner = $fpath . 'tpl.reserve.php';
        $ndss = 'В том числе НДС - 18% с суммы агентского вознаграждения ООО «Ваан» ('
            . (100 * $sbr->scheme['taxes'][sbr::EMP][sbr::TAX_EMP_COM]['percent'])
            . '%)';

        if ($action == 'draft') {
            if ($sbr->draft($sbr->id))
                header_location_exit('/' . sbr::NEW_TEMPLATE_SBR . '/?site=drafts');
        }

        $sbr->getReserveSum();
        $sbr_cost = $sbr->getTotalCost(false);
//        $sbr->markNotUsedTaxes(sbr::EMP);
        $sbr_schemes = $sbr->getSchemes();
        
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
    default :
        $projects_cnt = projects::CountMyProjects($sbr->uid, false, true);
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
                if($ok) {
                    $pskb = new pskb($sbr);
                    $lc = $pskb->getLC();
                    if($lc['state'] == 'new') {
                        $pskb->closeLC($lc['lc_id']);
                    }
                }
            } else if($del) {
                $ok = $sbr->delete($id);
            }
            if($ok)
                header_location_exit("/".sbr::NEW_TEMPLATE_SBR."/".($del ? '' : "?id={$id}"));
        }
        $filter = __paramInit('string', 'filter');
        $anchor = __paramInit('int', 'id');
        $count_sbr = $sbr->getCountCompleteSbr();
        if(!($sbr_currents = $sbr->_new_getCurrents($filter)) && !$filter && !$count_sbr && !$count_old_sbr) {
            // если есть СБР в черновиках то промо страниццу не показываем, а редиректим в черновики
            $sbr_drafts = $sbr->getDrafts();	 
            $sbr_count = count($sbr_drafts);	 
            if ($sbr_count) {	 
                header_location_exit('/' . sbr::NEW_TEMPLATE_SBR . '/?site=drafts');	 
            } else {
                header_location_exit('/promo/' . sbr::NEW_TEMPLATE_SBR . '/');	 
            }
        } else {
            $now_count = sizeof($sbr_currents);
            //$count_sbr = $count_sbr - $now_count;
            $now_count = ($now_count - $sbr->new_count - 1 ); // новые в выдаче не считаем, отсчет с нуля
            $sbr->getUserReqvs();
        }
        $_SESSION['sbr_tip'] = notifications::getSbrTip();
        $sbr->setLastView();
        break;
}

