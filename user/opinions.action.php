<?php
$sort   = __paramInit('int', 'sort', NULL, 0);
$period = __paramInit('int', 'period', NULL, 0);
$author = __paramInit('int', 'author', NULL, is_emp($p_user->role) ? 1 : 2);
$edit   = __paramInit('int', 'edit', 'edit', 0);
$opinion = __paramInit('int', 'opinion', 'opinion', 0);

switch(__paramInit('string', 'from', 'from', 'total')) {
    case "emp":
        $ops_type = "emp";
        $tbl_type = "employer";
        $filter_type_user = "работодателей";
        $author = 2;
        break;
    case "frl":
        $ops_type = "frl";
        $tbl_type = "freelancer";
        $filter_type_user = "фрилансеров";
        $author = 1;
        break;    
    case "users":
        $ops_type = "users";
        $tbl_type = "users";
        $filter_type_user = "всех пользователей";
        $author = 0;
        break;
    case "norisk":
        $ops_type = "norisk";
        break;
    case "total":
    default: 
        $ops_type = "total";
        $tbl_type = "users";
        break;
}

switch($period) {
    case 1:
        $filter_string = "За последний год";
        break;
    case 2:
        $filter_string = "За последние полгода";
        break;
    case 3:
        $filter_string = "За последний месяц";
        break;
    default:
        $filter_string = "За всё время";
        break;
}
switch($author) {
    case 1:
        $author_filter_string = "От фрилансеров";
        break;
    case 2:
        $author_filter_string = "От работодателей";
        break;
    default:
        $author_filter_string = "От всех пользователей";
        break;
}
$html_query[] = "from={$ops_type}";
if(isset($_GET['sort'])) {
    $html_query[] = "sort=".intval($_GET['sort']);
}
$html_for_filter = implode("&", $html_query);
$html_for_filter = $html_for_filter==""?"":"?".$html_for_filter;
$html_query[] = "period=".$period;

$html_query_string = implode("&", $html_query);
$html_query_string = $html_query_string==""?"":"?".$html_query_string;

//Не используем __paramInit, т.к. значения по умолчанию
// могут реально прийти, а это уже другой адрес
if(isset($_GET['sort']) || isset($_GET['period']) || isset($_GET['from'])) {
    $additional_header .= '
<meta name="robots" content="noindex"/>
';
}

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/opinions.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");

$action = __paramInit('string', NULL, 'action');
if (!$action) {
    $action = __paramInit('string', 'action', NULL);
}

$rand = $_POST['r'] ? $_POST['r'] : $_GET['r'];
if ($action && (!$_SESSION['rand'] || $rand != $_SESSION['rand'])) {
    unset($action);
}

switch ($action) {
    /** 
     * @deprecated #0015627
    case "new":
        $theme = substr(change_q_x($_POST['msg'], false, false, 'br|b|i'), 0, 2000);
        $rating = intval($_POST['rating']);
        if ($rating !== -1 && $rating !== 0 && $rating !== 1) {
            header("Location: /404.php");
            exit;
        }

        if (!$uid || $user->uid == $uid) {
            $error_flag = 1;
        }

        if (!$theme) {
            $error_flag = 1;
            $alert[2] = "Поле заполнено некорректно";
        }

        if ($_POST['rating'] == '') {
            $error_flag = 1;
            $alert[3] = "Укажите характер мнения";
        }

        if (!$error_flag && 0 == opinions::CheckUserCanPost($uid, $user->uid)) {
            $error .= opinions::NewMsg($uid, $user->uid, $theme, $rating, getRemoteIP());
        }

        if (!$error && !$error_flag) {
            unset($theme);
            unset($action);
        }
        break;
    */
    case "delete":
        $id = intval(trim($_GET['msg']));
        if ($id && $uid) {
            $error = opinions::DeleteMsg($uid, $id, hasPermissions('users'));
        }
        //header("Location: /blogs/viewgroup.php?gr=$gr&t=$t");
        break;
    case "delete_com":
        $id = intval(trim($_GET['msg']));
        if ($id && $uid) {
            $error = opinions::deleteComment($id, $uid, hasPermissions('users'));
        }
        break;
    case "cmtopinion":
        $id = intval($_POST['opid']);
        $theme = substr(change_q_x($_POST['comment'], false, false, 'br|b|i'), 0, 3000);

        if (!$uid) {
            $com_error_flag = 1;
        }
        if (!$theme) {
            $com_error_flag = 1;
            $com_alert[2] = "Поле заполнено некорректно";
        }

        if (strlen($_POST['comment']) > 3000) {
            $com_error_flag = 1;
            $com_alert[2] = "Слишком большой комментарий. Допускается не более 3000 знаков";
        }
        $com_msg = $theme;

        if (!$com_error_flag) {
            $error .= opinions::newCommentOpinion($theme, $uid, $id);
        }
        if (!$error && !$com_error_flag) {
            unset($theme);
            unset($action);
        }
        break;
    
    case "edit":
        $edit_tr = intval(trim($_GET['msg']));
        if ($edit_tr) {
            $edit_msg = opinions::GetMsgInfo($edit_tr, $error);
            if ($edit_msg['fromuser_id'] != $uid && !hasPermissions('users')) {
                unset($edit_msg);
                unset($action);
            }
        }
        break;
    case "cmtedit":
        $id_edit = intval($_POST['opid']);
        $theme = substr(change_q_x($_POST['comment'], false, false, 'br|b|i'), 0, 3000);

        $info = opinions::GetMsgComInfo($id_edit);
        $com_msg = $theme;
        $medit = $info['opinion_id'];

        if (!$uid) {
            $ecom_error_flag = 1;
        }

        if (!$theme) {
            $ecom_error_flag = 1;
            $com_alert[2] = "Поле заполнено некорректно";
        }

        if (strlen($_POST['comment']) > 3000) {
            $ecom_error_flag = 1;
            $com_alert[2] = "Слишком большой комментарий. Допускается не более 3000 знаков";
        }

        if (!$ecom_error_flag) {
            $error .= opinions::editCommentOpinion($theme, $uid, $id_edit);
            unset($theme);
            unset($action);
        }
        break;
    case "change":
        $theme = substr(change_q_x($_POST['msg'], false, false, 'br|b|i'), 0, 2000);
        $rating = intval($_POST['rating']);

        if ($rating !== -1 && $rating !== 0 && $rating !== 1) {
            header("Location: /404.php");
            exit;
        }

        $msgid = intval($_POST['msgid']);
        if ($_SESSION['uid'] && $msgid) {
            if ($theme || $attach['name']) {
                $error = opinions::Edit($_SESSION['uid'], $msgid, $theme, $rating, getRemoteIP(), hasPermissions('users'));
            } elseif (!$theme) {
                $error_flag = 1;
                $alert[2] = "Поле заполнено некорректно";
            }
            if ($error || $error_flag) {
                $action = "edit";
            } else {
                unset($msg_name);
                unset($theme);
                //header("Location: /blogs/viewgroup.php?gr=$gr&t=$t");
            }
        }
        break;
}

$ppage = trim($_GET['page']);

if (!$ppage) {
    $ppage = trim($_POST['page']);
}
if (!$ppage) {
    $ppage = 1;
}

$to_id = $user->uid;

// Мнения в кучу #0017304
if ($ops_type == 'total') {
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/paid_advices.php");
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/exrates.php");
    
    if($user->uid == $_SESSION['uid']) {
        $paid_advice = new paid_advices();
        $new_advices = $paid_advice->getAdvices($_SESSION['uid'], $author);
    }
    $exrates = new exrates();
    $EXR     = $exrates->GetAll();
    
    // если в фильтре отзывов у фрилансера задано "от фрилансеров", и также с работодателем - то рекомендаций и платных рекомендаций не будет
    $sameRole = (is_emp($user->role) && $author == 2) || (!is_emp($user->role) && $author == 1);
    if (!$sameRole) {
        $msgs = sbr::getUserFeedbacks($to_id, is_emp($user->role), $sort > 0 ? $sort : false, $period, true, false); // рекомендации
    }
    $msgs2  = opinions::GetMsgs($to_id, $msg_cntr, $ppage, $num_msgs, $error, $tbl_type, $sort > 0 ? $sort : false, $period, $author); // мнения
    $opCount = ($msgs ? count($msgs) : 0) + ($msgs2 ? count($msgs2) : 0);
} elseif ($ops_type != 'norisk') {
    $msgs  = opinions::GetMsgs($to_id, $msg_cntr, $ppage, $num_msgs, $error, $tbl_type, $sort > 0 ? $sort : false, $period, $author);
    $opCount = $msgs ? count($msgs) : 0;   
}

$filterCounts = opinions::getFilterCounts($to_id, is_emp($user->role), $sort, $author, $period);


/**
 * @deprecated #0015627
if ($uid) {
    $can_post = opinions::CheckUserCanPost($uid, $to_id);
}
*/

$_SESSION['page_user_id'] = $user->uid;

if ($ops_type == 'norisk') {
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/paid_advices.php");
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/exrates.php");
    $sbr = new sbr(get_uid(false));
    $isReqvsFilled = !$sbr->checkUserReqvs();
    $exrates = new exrates();
    $EXR     = $exrates->GetAll();
    $paid_advice = new paid_advices();
    $can_post = 0;

    $_attached = array();
    function set_loaded_attach($type, $id, $name, $link = false) {
        global $_attached;
        $_attached['ids'][$type] = $id;
        $_attached['ext'][$type]  = getICOFile(CFile::getext($name));
        if(strlen($name) > 40) {
            $name = substr($name, 0, 18) . '...' . substr($name, strlen($name)-18, 18); 
        }
        $_attached['name'][$type] = $name;
        $_attached['link'][$type] = $link;
    }
    if(isset($_GET['edit']) && intval($_GET['edit']) <= 0) {
        header("Location: /404.php");
        exit;      
    }
    $is_upload_error = ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($_POST) && empty($_FILES));
    if($is_upload_error) {
        $error['files'] = 1;
        $error['files_text'] = 'Размер файла не должен превышать 30 Мб';
    }
    
    $is_transfer = false;
    if($opinion > 0 && get_uid(false) && !$paid_advice->isConvertExist($opinion)) {
        $_opinion = opinions::GetMsgInfo($opinion, $error);
        $advice = opinions::converOpinion2Advice($_opinion);
        if($advice) {
            $is_transfer = true;
        } else {
            //header("Location: /404.php");
            //exit;
        }
        
        if(isset($_POST['save']) && $is_transfer) {
            $is_convert = opinions::setConvertOpinion($opinion);
            if($is_convert) {
                $edit = $paid_advice->add($advice['user_to'], $advice['msgtext'], $advice['user_from'], $advice['create_date'], $opinion);
                $_POST['paid_advice_id']  = $edit;
            } else {
                $error['save'] = 'Не удалось записать рекомендацию';
            }
        }
    }
    
    if($edit > 0 && get_uid(false)) {
        $advice  = $paid_advice->getAdvice((int)$edit, $user->uid);
        
        $filesSize   = 0;
        $bitDisabled = '00000';
        $bitEnabled  = '11111';
        if($advice['docs_link'] != "") {
            $link    = $advice['docs_link'];
            $is_link = true;
            $bitDisabled = $bitDisabled | '00100';
        }

        if($advice['docs_contract'] > 0) {
            $filesSize += $advice['size_contract'];
            $path_file = WDCPREFIX . "/{$advice['path_docs_contract']}{$advice['name_docs_contract']}";
            set_loaded_attach(1, $advice['docs_contract'], $advice['fname_docs_contract'], $path_file);
            $bitDisabled = $bitDisabled | '10000';
        }

        if($advice['docs_tz'] > 0) {
            $filesSize += $advice['size_tz'];
            $path_file = WDCPREFIX . "/{$advice['path_docs_tz']}{$advice['name_docs_tz']}";
            set_loaded_attach(2, $advice['docs_tz'], $advice['fname_docs_tz'], $path_file);
            $bitDisabled = $bitDisabled | '01000';
        }

        if($advice['docs_result_file'] > 0) {
            $filesSize += $advice['size_result'];
            $path_file = WDCPREFIX . "/{$advice['path_docs_result']}{$advice['name_docs_result']}";
            set_loaded_attach(3, $advice['docs_result_file'], $advice['fname_docs_result'], $path_file);
            $bitDisabled = $bitDisabled | '00100';
        }

        if($advice['cost_sum'] >= sbr_stages::MIN_COST_RUR_PDRD) {
            $budget = $advice['cost_sum'];
            $bitDisabled = $bitDisabled | '00010';
        }

        if(!$isReqvsFilled) {
            $bitDisabled = $bitDisabled | '00001';    
        }
        // Проверяем все обязательные поля на заполненность + заполненность вкладки Финансы
        $isBtnDisabled = !(($bitDisabled & $bitEnabled) == $bitEnabled);

        if(isset($_POST['save'])) {
            $id_advice = intval($_POST['paid_advice_id']);
            $link   = trimhttp(trim($_POST['link_work']));
            $budget = round(floatval($_POST['sum_rub']), 2);

            $is_link = ($link!="");
            if(!url_validate($link) && $is_link) {
                $error['doc_result_link'] = 1;
            }

            if($budget < sbr_stages::MIN_COST_RUR_PDRD) {
                $error['budget'] = 1;
            }

            $attached = $_FILES['attachedfiles_file'];
            $old_attached = $_POST['files_uploaded_id'];

            if(trim($attached['name'][1]) == "" && (int)$old_attached[1] <= 0) {
                $error['doc_contract'] = 1;    
            }

            if(trim($attached['name'][2]) == "" && (int)$old_attached[2] <= 0) {
                $error['doc_tz'] = 1;  
            }

            if(trim($attached['name'][3]) == "" && !$is_link && (int)$old_attached[3] <= 0) {
                $error['doc_result'] = 1;  
            }

            if($error['doc_result'] == 1 || $error['doc_contract'] == 1 || $error['doc_tz'] == 1 || $error['doc_result_link'] == 1) {
                $error['files'] = 1;
            }
            $isBtnDisabled = false;
            if($error['files'] == 1 || $isReqvsFilled || $error['budget'] == 1) {
                $isBtnDisabled = true;
            }

            //if(!isset($error['files'])) {
                // Загружаем файлы
                if($attached) {
                    $dir = $_SESSION['login']."/upload";
                    foreach($attached['name'] as $key=>$fname) {
                        if(trim($fname) != "") {
                            $filesSize += $attached['size'][$key]; 
                            if($filesSize > paid_advices::MAX_FILE_SIZE) {
                                $error['doc_contract'] = 1;
                                $error['doc_tz'] = 1;
                                $error['doc_result'] = 1;
                                $error['files'] = 1;
                                $error['files_text'] = 'Общий объем файлов не должен превышать 30 Мб';
                                break;
                            }
                            $file[$key] = new CFile(array('tmp_name' => $attached['tmp_name'][$key],
                                                        'size'     => $attached['size'][$key],
                                                        'name'     => $fname,
                                                        'error'    => $attached['error'][$key]
                            ));
                            $file[$key]->table = 'file_advices';
                            $file[$key]->max_size = paid_advices::MAX_FILE_SIZE;
                            if($file[$key]->max_size <= $attached['size'][$key]) {
                                if($key == 1) $error['doc_contract'] = 1;
                                if($key == 2) $error['doc_tz'] = 1;
                                if($key == 3) $error['doc_result'] = 1;
                                $error['files'] = 1;
                                $error['files_text'] = 'Размер файла не должен превышать 30 Мб';
                                continue;
                            }
                            if(!$file[$key]->error) {
                                $file[$key]->MoveUploadedFile($dir);
                                if($file[$key]->id) {
                                    $path_file =  WDCPREFIX . "/{$file[$key]->path}{$file[$key]->name}";
                                    set_loaded_attach($key, $file[$key]->id, $fname, $path_file);
                                } else {
                                    $error['files'] = 1;
                                    $error['files_text'] = "Недопустимый тип файла";
                                }
                            }
                        }
                    }
                } elseif(isset($old_attached)) {
                    foreach($old_attached as $key=>$fid) {
                        if($fid > 0) {
                            set_loaded_attach($key, $fid, $_POST['files_uploaded_name'][$key]);    
                        }
                    } 
                }

                $data = array('cost_sum'         => $budget,
                                'comm_sum'         => round($budget * paid_advices::PAID_COMMISION, 2),
                                'docs_contract'    => $_attached['ids'][1]>0?$_attached['ids'][1]:NULL,
                                'docs_tz'          => $_attached['ids'][2]>0?$_attached['ids'][2]:NULL,
                                'docs_result_file' => $_attached['ids'][3]>0?$_attached['ids'][3]:NULL,
                                'docs_link'        => $link);
                
                if($_POST['add_mod'] == 1 && count($error) <= 0) {
                    $data['mod_status']  = paid_advices::MOD_STATUS_PENDING;
                    $data['accept_date'] = 'NOW()';
                }

                $error_msg = $paid_advice->update($id_advice, $data);
                if($error_msg) {
                    $error['save'] = $error_msg;
                } else {
                    $is_save = true;
                    
                }
                if($_POST['add_mod'] == 1 && count($error) <= 0) {
                    header("Location: /users/{$_SESSION['login']}/opinions/#n_{$id_advice}");
                    exit;
                }
            //}
        }

        if(!$advice) {
            header("Location: /404.php");
            exit;    
        }
    } else if($edit > 0 && !get_uid(false)) {
        header("Location: /404.php");
        exit;
    }
    
    if($is_convert && isset($_POST['save'])) {
        header("Location: /users/{$_SESSION['login']}/opinions/?from=norisk&edit={$id_advice}");
    }

    if($user->uid == $_SESSION['uid']) {
        $new_advices = $paid_advice->getAdvices($_SESSION['uid']);
    }

    $msgs = sbr::getUserFeedbacks($to_id, is_emp($user->role), $sort > 0 ? $sort : false, $period, true, false);

    $_user = new users();
    if (get_uid(0)) {
        $_user->GetUser($_SESSION['login']);
    }
}

?>