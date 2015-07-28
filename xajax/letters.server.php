<?php
require_once( $_SERVER['DOCUMENT_ROOT'] . '/xajax/letters.common.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stdf.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/letters.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/users.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/sbr_meta.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/permissions.php' );

session_start();

$objLetters = new letters();

/**
 * Заполнить форму добавления документа выбранным шаблоном
 *
 * @param    integer    $id    ID шаблона
 */
function selectTemplate($id) {
    global $objLetters;
    
    $objResponse = new xajaxResponse();

    if( (hasPermissions('letters') && hasPermissions('adm')) ) {
        $objResponse->script('letters.showAddForm();');
        $template = $objLetters->getTemplate($id);
        if($template) {
            if($template['docs']) {
                for($n=0; $n<count($template['docs'])-1; $n++) {
                    $objResponse->script('letters.M_InsertNewDoc();');
                }
                $num = 0;
                foreach($template['docs'] as $doc) {
                    $objResponse->script('letters.MData['.$num.'] = [];');
                    $objResponse->script("letters.MData[{$num}]['letters_doc_frm_title'] = '{$doc['title']}';");
                    $objResponse->script("letters.MData[{$num}]['letters_doc_frm_group_db_id'] = '".intval($doc['group_id'])."';");
                    $objResponse->script("letters.MData[{$num}]['letters_doc_frm_user_1_db_id'] = '".intval($doc['user_1'])."';");
                    $objResponse->script("letters.MData[{$num}]['letters_doc_frm_user_1_section'] = '".($doc['is_user_1_company']=='t' ? 1 : 0)."';");
                    $objResponse->script("letters.MData[{$num}]['letters_doc_frm_user_2_db_id'] = '".intval($doc['user_2'])."';");
                    $objResponse->script("letters.MData[{$num}]['letters_doc_frm_user_2_section'] = '".($doc['is_user_2_company']=='t' ? 1 : 0)."';");
                    $objResponse->script("letters.MData[{$num}]['letters_doc_frm_user_3_db_id'] = '".intval($doc['user_3'])."';");
                    $objResponse->script("letters.MData[{$num}]['letters_doc_frm_user_3_section'] = '".($doc['is_user_3_company']=='t' ? 1 : 0)."';");
                    $objResponse->script("letters.MData[{$num}]['letters_doc_frm_delivery_db_id'] = '".intval($doc['delivery'])."';");
                    $objResponse->script("letters.MData[{$num}]['letters_doc_frm_delivery_cost'] = '".$doc['delivery_cost']."';");
                    $objResponse->script("letters.MData[{$num}]['letters_doc_frm_parent_db_id'] = '".$doc['parent']."';");
                    $objResponse->script("letters.MData[{$num}]['letters_doc_frm_comment'] = '".$doc['comment']."';");

                    //letters.MData[{$num}]['letters_doc_frm_withoutourdoc'] = ($('letters_doc_frm_withoutourdoc').get('checked') ? 1 : 0);

                    $objResponse->script("letters.MData[{$num}]['letters_doc_frm_user1_status_data'] = '".intval($doc['user_status_1'])."';");
                    $objResponse->script("letters.MData[{$num}]['letters_doc_frm_user2_status_data'] = '".intval($doc['user_status_2'])."';");
                    $objResponse->script("letters.MData[{$num}]['letters_doc_frm_user3_status_data'] = '".intval($doc['user_status_3'])."';");

                    $objResponse->script("letters.MData[{$num}]['letters_doc_frm_user1_status_date_data'] = '".($doc['user_status_date_1'] ? dateFormat('Y-m-d', $doc['user_status_date_1']) : '')."';");
                    $objResponse->script("letters.MData[{$num}]['letters_doc_frm_user2_status_date_data'] = '".($doc['user_status_date_2'] ? dateFormat('Y-m-d', $doc['user_status_date_2']) : '')."';");
                    $objResponse->script("letters.MData[{$num}]['letters_doc_frm_user3_status_date_data'] = '".($doc['user_status_date_3'] ? dateFormat('Y-m-d', $doc['user_status_date_3']) : '')."';");

                    $num++;
                }
                $objResponse->script("letters.M_ShowDoc(1, false);");
            }
        }
        $objResponse->script('letters.spinner.hide();');
    }

    return $objResponse;
}

/**
 * Получить поле для редактирования
 *
 * @param    integer    $id      ID документа
 * @param    string     $filed   Поле
 * @return object xajaxResponse
*/
function getDocField($id, $field) {
    global $objLetters;
    
    $objResponse = new xajaxResponse();

    if( (hasPermissions('letters') && hasPermissions('adm')) ) {

    $doc = $objLetters->getDocument($id);
    if($doc) {
        switch($field) {
            case 'comment':
                $objResponse->assign('letters_form_comment_field_data', 'value', ($doc['comment'] ? $doc['comment'] : ''));
                $objResponse->script("obj_letters_form_comment_field_data.checkSize(true);");
                break;
            case 'delivery':
                $objResponse->script("$('letters_form_delivery_field_data_".intval($doc['delivery'])."').set('checked', true)");
                break;
            case 'delivery_cost':
                $delivery_cost = sprintf("%01.2f", floatval($doc['delivery_cost']));
                if(!$delivery_cost) { $delivery_cost = ''; };
                $objResponse->assign('letters_form_deliverycost_field_data', 'value', $delivery_cost);
                break;
            case 'dateadd':
                $dateadd = preg_replace("/ .*$/","",$doc['date_add']);
                if(!$dateadd) { $dateadd = ''; };
                $objResponse->script('ComboboxManager.getInput("letters_form_dateadd_field_data").setDate("'.$dateadd.'");');
                break;
            case 'datechange':
                $datechange = preg_replace("/ .*$/","",$doc['date_change_status']);
                if(!$datechange) { $datechange = ''; };
                $objResponse->script('ComboboxManager.getInput("letters_form_datechange_field_data").setDate("'.$datechange.'");');
                break;
            case 'status':
                if($doc['user_status_1']==2 || $doc['user_status_1']==3) {
                    $add_status1 = ' '.dateFormat('d.m.Y', $doc['user_status_date_1']);
                }
                if($doc['user_status_2']==2 || $doc['user_status_2']==3) {
                    $add_status2 = ' '.dateFormat('d.m.Y', $doc['user_status_date_2']);
                }
                if($doc['user_status_3']==2 || $doc['user_status_1']==3) {
                    $add_status3 = ' '.dateFormat('d.m.Y', $doc['user_status_date_3']);
                }
                $objResponse->assign('letters_doc_frm_user1_status_data', 'value', intval($doc['user_status_1']));
                $objResponse->assign('letters_doc_frm_user2_status_data', 'value', intval($doc['user_status_2']));
                $objResponse->assign('letters_doc_frm_user3_status_data', 'value', intval($doc['user_status_3']));
                $objResponse->assign('letters_doc_frm_user1_status_date_data', 'value', ($doc['user_status_date_1'] ? dateFormat('Y-m-d', $doc['user_status_date_1']) : ''));
                $objResponse->assign('letters_doc_frm_user2_status_date_data', 'value', ($doc['user_status_date_2'] ? dateFormat('Y-m-d', $doc['user_status_date_2']) : ''));
                $objResponse->assign('letters_doc_frm_user3_status_date_data', 'value', ($doc['user_status_date_3'] ? dateFormat('Y-m-d', $doc['user_status_date_3']) : ''));

                $objResponse->script("
                                var ann = '';
                                if(letters.nn!=0) {
                                    ann = '_'+letters.nn;
                                } else {
                                    ann = '';
                                }
                                var el1 = $('letters_item_status_1_{$id}'+ann);
                                var el2 = $('letters_doc_frm_div_statuses');
                                el2.inject(el1, 'after');
                                ComboboxManager.getInput('letters_doc_frm_div_statuses_st_date_2').setDate();
                                ComboboxManager.getInput('letters_doc_frm_div_statuses_st_date_3').setDate();
                                var val = $('letters_doc_frm_user'+letters.status_num+'_status_data').get('value');
                                var d_val = $('letters_doc_frm_user'+letters.status_num+'_status_date_data').get('value');
                                $('letters_doc_frm_div_statuses_st_'+val).set('checked', true);
                                letters.changeStatus('newpopup', val);
                                if(val==2 || val==3) {
                                    ComboboxManager.getInput('letters_doc_frm_div_statuses_st_date_'+val).setDate(d_val);
                                }
                                $('letters_doc_frm_div_statuses').setStyle('display', 'block');
                                $('letters_doc_frm_div_statuses').getChildren('div').removeClass('b-shadow_hide');
                                letters.spinner.hide();
                                ");



                break;
        }
    }
    }
    return $objResponse;
}

/**
 * Обновить поле документа
 *
 * @param    integer    $id      ID документа
 * @param    string     $filed   Поле
 * @param    string     $data    Значение поля
 * @param    string     $mode    item - простотр документа, list - просмотр списка
 * @return object xajaxResponse
*/
function updateDocField($id, $field, $data, $mode) {
    global $objLetters;
    
    $objResponse = new xajaxResponse();
    
    if( (hasPermissions('letters') && hasPermissions('adm')) ) {

    switch($field) {
        case 'comment':
            $fdata['comment'] = $data;
            if(!$fdata['comment']) {
                $fdata['comment'] = '';
            }
            $objLetters->updateFields($id, $fdata);
            break;
        case 'delivery':
            $fdata['delivery'] = $data['letters_form_delivery_field_data'];
            if(!$fdata['delivery']) {
                $fdata['delivery'] = null;
            }
            $objLetters->updateFields($id, $fdata);
            break;
        case 'delivery_cost':
            $data = preg_replace("/,/", ".", $data);
            $fdata['delivery_cost'] = ($data ? $data : NULL);
            if( ($fdata['delivery_cost'] && !is_numeric($fdata['delivery_cost'])) || ( $fdata['delivery_cost'] &&  floatval($fdata['delivery_cost'])<0 ) ) {
                $fdata['delivery_cost'] = NULL;
            }
            $objLetters->updateFields($id, $fdata);
            break;
        case 'dateadd':
            $fdata['date_add'] = $data;
            $objLetters->updateFields($id, $fdata);
            break;
        case 'datechange':
            $fdata['date_change_status'] = $data;
            $objLetters->updateFields($id, $fdata);
            break;
        case 'status':
            $fdata['user_status_'.$data['letters_doc_frm_user_query']] = $data['letters_doc_frm_user'.$data['letters_doc_frm_user_query'].'_status_data'];
            if(!$fdata['user_status_'.$data['letters_doc_frm_user_query']]) $fdata['user_status_'.$data['letters_doc_frm_user_query']] = NULL;
            $fdata['user_status_date_'.$data['letters_doc_frm_user_query']] = ($data['letters_doc_frm_user'.$data['letters_doc_frm_user_query'].'_status_date_data'] ? $data['letters_doc_frm_user'.$data['letters_doc_frm_user_query'].'_status_date_data'] : NULL);
            $objLetters->updateFields($id, $fdata);
            $objLetters->updateDateStatusChange($id);
            break;
    }
    $objResponse->script("letters.reload_data();");

    }

    return $objResponse;
}


/**
 * Расчитать стоимость доставки
 *
 * @param    string     $ids      ID документов
 * @return object xajaxResponse
*/
function calcDeliveryCost($ids) {
    global $objLetters;
    
    $objResponse = new xajaxResponse();
    
    if( (hasPermissions('letters') && hasPermissions('adm')) ) {
        $ids = preg_replace("/,$/", "", $ids);
        $ids_docs = preg_split("/,/", $ids);
        if($ids_docs) {
            foreach($ids_docs as $k=>$v) {
                if(intVal($v)!=$v) {
                    unset($ids_docs[$k]);
                }
            }
            $cost = $objLetters->calcDeliveryCost($ids_docs);
            $objResponse->assign('letters_selected_delivery_cost_data', 'innerHTML', sprintf("%01.2f", $cost));
            $objResponse->script('$("letters_selected_delivery_cost").setStyle("display", "block");');
        }
        $objResponse->script("letters.spinner.hide();");
    }

    return $objResponse;
}

/**
 * Редактирование статусов(массовое)
 *
 * @param    string     $ids      ID документов
 * @return object xajaxResponse
*/
function showMassStatus($ids) {
    global $objLetters;
    
    $objResponse = new xajaxResponse();
    
    if( (hasPermissions('letters') && hasPermissions('adm')) ) {
        $ids = preg_replace("/,$/", "", $ids);
        $ids_docs = preg_split("/,/", $ids);
        if($ids_docs) {
            foreach($ids_docs as $k=>$v) {
                if(intVal($v)!=$v) {
                    unset($ids_docs[$k]);
                }
            }
            $statuses = $objLetters->getDocumentsStatuses($ids_docs);
            $html = '';
            if($statuses) {
                foreach($statuses as $k=>$status) {
                    $html .= '<div class="b-layout__txt b-layout__txt_nowrap b-layout__txt_padbot_10">
                                    <input id="letters_mass_action_status_div_fld_'.intval($k).'" name="letters_mass_action_status_div_fld_'.intval($k).'" type="hidden" value="">
                                    <input id="letters_mass_action_status_div_fld_'.intval($k).'_date" name="letters_mass_action_status_div_fld_'.intval($k).'_date" type="hidden" value="">
                                    '.$status.'&nbsp;&rarr;&nbsp;<a id="letters_mass_action_status_div_lnk_'.intval($k).'" class="b-layout__link b-layout__link_bordbot_dot_0f71c8" href="#" onClick="letters.massShowChangeStatus('.intval($k).'); return false;">Изменить статус</a>
                              </div>';
                }
                $objResponse->assign('letters_mass_action_status_div_data', 'innerHTML', $html);
                $objResponse->script("$('letters_mass_action_status_div').setStyle('display', 'block');");
                $objResponse->script("window.location = '#letters_mass_action_status_div_a';");
            }
        }
        $objResponse->script("letters.spinner.hide();");
    }

    return $objResponse;
}


/**
 * Обновление статусов(массовое)
 *
 * @param    array     $frm      Информация о статусах
 * @return   object xajaxResponse
*/
function updateMassStatus($frm) {
    global $objLetters;
    
    $objResponse = new xajaxResponse();
    
    if( (hasPermissions('letters') && hasPermissions('adm')) ) {
        $ids = $frm['letters_mass_action_status_div_fld_ids'];
        $ids = preg_replace("/,$/", "", $ids);
        $ids_docs = preg_split("/,/", $ids);
        if($ids_docs) {
            foreach($ids_docs as $k=>$v) {
                if(intVal($v)!=$v) {
                    unset($ids_docs[$k]);
                }
            }
        }
        $ids_statuses = array();
        foreach($frm as $k=>$ifrm) {
            if(preg_match("/^letters_mass_action_status_div_fld_\d{1,}$/",$k)) {
                if($ifrm!=false) {
                    $i = preg_replace("/^letters_mass_action_status_div_fld_/","",$k);
                    $ids_statuses[$i]['id'] = intval($ifrm);
                    $ids_statuses[$i]['date'] = $frm['letters_mass_action_status_div_fld_'.$i.'_date'];
                }
            }

        }
        $objLetters->updateMassStatus($ids_docs, $ids_statuses);
        foreach($ids_docs as $v) {
            $objLetters->updateDateStatusChange($v);
        }

        $objResponse->script("letters.reload_data();");
    }

    return $objResponse;
}

/**
 * Обновление стоимости(массовое)
 *
 * @param    string     $ids      ID документов
 * @param    float      $cost     стоимость
 * @return   object xajaxResponse
*/
function updateMassDeliveryCost($ids, $cost) {
    global $objLetters;
    
    $objResponse = new xajaxResponse();
    
    if( (hasPermissions('letters') && hasPermissions('adm')) ) {
        $ids = preg_replace("/,$/", "", $ids);
        $ids_docs = preg_split("/,/", $ids);
        if($ids_docs) {
            foreach($ids_docs as $k=>$v) {
                if(intVal($v)!=$v) {
                    unset($ids_docs[$k]);
                }
            }
        }
        if( ($cost && !is_numeric($cost)) || ( $cost &&  floatval($cost)<0 ) ) {
            $cost = NULL;
        }

        $objLetters->updateMassDeliveryCost($ids_docs, $cost);

        $objResponse->script("letters.reload_data();");
    }

    return $objResponse;
}

/**
 * Обновление даты(массовое)
 *
 * @param    string     $ids      ID документов
 * @param    string     $date     дата
 * @return   object xajaxResponse
*/
function updateMassDate($ids, $date) {
    global $objLetters;
    
    $objResponse = new xajaxResponse();
    
    if( (hasPermissions('letters') && hasPermissions('adm')) ) {
        $ids = preg_replace("/,$/", "", $ids);
        $ids_docs = preg_split("/,/", $ids);
        if($ids_docs) {
            foreach($ids_docs as $k=>$v) {
                if(intVal($v)!=$v) {
                    unset($ids_docs[$k]);
                }
            }
        }

        $objLetters->updateMassDate($ids_docs, $date);

        $objResponse->script("letters.reload_data();");
    }

    return $objResponse;
}


/**
 * Получить список корреспонденции
 * 
 * @param  integer  $type     Тип корреспонденции (Все, Исходящие, Входящие, В обработке, Архив)
 * @param  array    $filter   Данные фильтра
 * @param  integer  $page     Номер страницы
 * @param  integer  $nums     Количество документов на странице (0 - все)
 * @return object xajaxResponse
 */
function showLetters( $type = 0, $filter = null, $page = 1, $nums = 0 ) {    
    global $objLetters;
    
//    $objResponse = new xajaxResponse();

    if( (hasPermissions('letters') && hasPermissions('adm')) ) {

    $res = array();
    $aData = array();
    $pager_html = '';

    $page = abs(intval($page));
    $page = $page? $page: 1;
    $nums = abs(intval($nums));

    $filter = iconv('CP1251', 'UTF-8', $filter);
    $filter = stripcslashes($filter);
    $filter = json_decode($filter, true);
    if($filter) {
        foreach($filter as $k=>$v) {
            $filter[$k] = encodeCharset('UTF-8', 'CP1251', $v);
        }
    }

    
    $letters = $objLetters->getLetters($type, $filter, $nums, ($page - 1) * $nums);
    $pages   = $nums? intval($objLetters->numsLetters / $nums) + 1: 1;
    if($letters) {
        $qstatuses = $objLetters->getStatuses();
        foreach($qstatuses as $qstatus) {
            $statuses[$qstatus['id']] = $qstatus['title'];
        }
        $statuses[0] = 'Добавить статус';
        $html = '';
        ob_start();

        $letter_num = 1;
        $nn = 0;
        foreach($letters as $key=>$oletter) {
            if($type==2 || $type==6) {
                $letter['number'] = $letter_num;
                $ukey = preg_split("/-/", $key);
                if($ukey[2]=='t') {
                    $letter['company'] = letters::getCompany($ukey[0]);
                } else {
                    $letter['recipient'] = letters::getUserReqvs($ukey[0]);
                }
                $letter['is_company'] = $ukey[2];
                $letter['rdelivery_title'] = $oletter[0]['delivery_title'];
                //require($_SERVER['DOCUMENT_ROOT'].'/siteadmin/letters/tpl.list.header.item.php');
                foreach($oletter as $letter) {
                    $letter['number'] = $letter_num;
                    $nn++;
                    if($letter['is_user_1_company']=='t') {
                        $company = letters::getCompany($letter['user_1']);
                        if($company['frm_type']) {
                            $company['name'] = $company['frm_type'].' "'.$company['name'].'"';
                        }
                        $letter['company1_name'] = $company['name'];
                        $letter['company1'] = $company;
                    } else {
                        $user1 = new users();
                        $user1->GetUserByUID($letter['user_1']);
                        $letter['user1_uname'] = $user1->uname;
                        $letter['user1_usurname'] = $user1->usurname;
                        $letter['user1_login'] = $user1->login;
                        $letter['user1_i'] = letters::getUserReqvs($letter['user_1']);
                    }
                    if($letter['is_user_2_company']=='t') {
                        $company = letters::getCompany($letter['user_2']);
                        if($company['frm_type']) {
                            $company['name'] = $company['frm_type'].' "'.$company['name'].'"';
                        }
                        $letter['company2_name'] = $company['name'];
                        $letter['company2'] = $company;
                    } else {
                        $user2 = new users();
                        $user2->GetUserByUID($letter['user_2']);
                        $letter['user2_uname'] = $user2->uname;
                        $letter['user2_usurname'] = $user2->usurname;
                        $letter['user2_login'] = $user2->login;
                        $letter['user2_i'] = letters::getUserReqvs($letter['user_2']);
                    }
                    if($letter['user_3']) {
                        if($letter['is_user_3_company']=='t') {
                            $company = letters::getCompany($letter['user_3']);
                            if($company['frm_type']) {
                                $company['name'] = $company['frm_type'].' "'.$company['name'].'"';
                            }
                            $letter['company3_name'] = $company['name'];
                            $letter['company3'] = $company;
                        } else {
                            $user3 = new users();
                            $user3->GetUserByUID($letter['user_3']);
                            $letter['user3_uname'] = $user3->uname;
                            $letter['user3_usurname'] = $user3->usurname;
                            $letter['user3_login'] = $user3->login;
                            $letter['user3_i'] = letters::getUserReqvs($letter['user_3']);
                        }
                    }
                    if($letter['file_id']) {
                        $cFile = new CFile($letter['file_id']);
                        $file_link = WDCPREFIX."/".$cFile->path.$cFile->name;
                    } else {
                        $file_link = '';
                    }
                    $aTmp = array(
                        'id'                        => iconv('CP1251', 'UTF-8', $letter['id']),
                        'nn'                        => iconv('CP1251', 'UTF-8', $nn),
                        'ukey0'                     => iconv('CP1251', 'UTF-8', $ukey[0]),
                        'delivery0'                 => iconv('CP1251', 'UTF-8', $oletter[0]['delivery']),
                        'user1_uname'               => iconv('CP1251', 'UTF-8', $letter['user1_uname']),
                        'user1_usurname'            => iconv('CP1251', 'UTF-8', $letter['user1_usurname']),
                        'user1_login'               => iconv('CP1251', 'UTF-8', $letter['user1_login']),
                        'user2_uname'               => iconv('CP1251', 'UTF-8', $letter['user2_uname']),
                        'user2_usurname'            => iconv('CP1251', 'UTF-8', $letter['user2_usurname']),
                        'user2_login'               => iconv('CP1251', 'UTF-8', $letter['user2_login']),
                        'user3_uname'               => iconv('CP1251', 'UTF-8', $letter['user3_uname']),
                        'user3_usurname'            => iconv('CP1251', 'UTF-8', $letter['user3_usurname']),
                        'user3_login'               => iconv('CP1251', 'UTF-8', $letter['user3_login']),
                        'date_change_status'        => iconv('CP1251', 'UTF-8', dateFormat("d.m.Y, H:i", $letter['date_change_status'])),
                        'user_status_1'             => iconv('CP1251', 'UTF-8', $letter['user_status_1']),
                        'user_status_2'             => iconv('CP1251', 'UTF-8', $letter['user_status_2']),
                        'user_status_3'             => iconv('CP1251', 'UTF-8', $letter['user_status_3']),
                        'user_status_1_icon'        => letters::$status_icons[intval($letter['user_status_1'])],
                        'user_status_2_icon'        => letters::$status_icons[intval($letter['user_status_2'])],
                        'user_status_3_icon'        => letters::$status_icons[intval($letter['user_status_3'])],
                        'user_status_1_color'       => letters::$status_colors[intval($letter['user_status_1'])],
                        'user_status_2_color'       => letters::$status_colors[intval($letter['user_status_2'])],
                        'user_status_3_color'       => letters::$status_colors[intval($letter['user_status_3'])],
                        'is_user_1_company'         => $letter['is_user_1_company'],
                        'is_user_2_company'         => $letter['is_user_2_company'],
                        'is_user_3_company'         => $letter['is_user_3_company'],
                        'number'                    => $letter['number'],
                        'group_title'               => iconv('CP1251', 'UTF-8', reformat(htmlspecialchars($letter['group_title']),20)),
                        'group_id'                  => $letter['group_id'],
                        'title'                     => iconv('CP1251', 'UTF-8', reformat(htmlspecialchars($letter['title']),20)),
                        'user_1'                    => $letter['user_1'],
                        'user_2'                    => $letter['user_2'],
                        'user_3'                    => $letter['user_3'],
                        'company1_name'             => iconv('CP1251', 'UTF-8', $letter['company1_name']),
                        'company2_name'             => iconv('CP1251', 'UTF-8', $letter['company2_name']),
                        'company3_name'             => iconv('CP1251', 'UTF-8', $letter['company3_name']),
                        'company1_index'            => iconv('CP1251', 'UTF-8', $letter['company1']['index']),
                        'company1_country_title'    => iconv('CP1251', 'UTF-8', $letter['company1']['country_title']),
                        'company1_city_title'       => iconv('CP1251', 'UTF-8', $letter['company1']['city_title']),
                        'company1_address'          => iconv('CP1251', 'UTF-8', $letter['company1']['address']),
                        'user1_i_form_type'         => $letter['user1_i']['form_type'],
                        'user1_i_1_address'         => iconv('CP1251', 'UTF-8', $letter['user1_i'][1]['address']),
                        'user1_i_2_address'         => iconv('CP1251', 'UTF-8', $letter['user1_i'][2]['address']),
                        'user_status_date_1'        => dateFormat("d.m.Y", $letter['user_status_date_1']),
                        'user1_i_1_fio'             => iconv('CP1251', 'UTF-8', $letter['user1_i'][1]['fio']),
                        'user1_i_2_full_name'       => iconv('CP1251', 'UTF-8', $letter['user1_i'][2]['full_name']),
                        'user2_i_1_fio'             => iconv('CP1251', 'UTF-8', $letter['user2_i'][1]['fio']),
                        'user2_i_2_full_name'       => iconv('CP1251', 'UTF-8', $letter['user2_i'][2]['full_name']),
                        'user3_i_1_fio'             => iconv('CP1251', 'UTF-8', $letter['user3_i'][1]['fio']),
                        'user3_i_2_full_name'       => iconv('CP1251', 'UTF-8', $letter['user3_i'][2]['full_name']),
                        'company2_index'            => iconv('CP1251', 'UTF-8', $letter['company2']['index']),
                        'company2_country_title'    => iconv('CP1251', 'UTF-8', $letter['company2']['country_title']),
                        'company2_city_title'       => iconv('CP1251', 'UTF-8', $letter['company2']['city_title']),
                        'company2_address'          => iconv('CP1251', 'UTF-8', $letter['company2']['address']),
                        'user2_i_form_type'         => $letter['user2_i']['form_type'],
                        'user2_i_1_address'         => iconv('CP1251', 'UTF-8', $letter['user2_i'][1]['address']),
                        'user2_i_2_address'         => iconv('CP1251', 'UTF-8', $letter['user2_i'][2]['address']),
                        'user_status_date_2'        => dateFormat("d.m.Y", $letter['user_status_date_2']),
                        'company3_index'            => iconv('CP1251', 'UTF-8', $letter['company3']['index']),
                        'company3_country_title'    => iconv('CP1251', 'UTF-8', $letter['company3']['country_title']),
                        'company3_city_title'       => iconv('CP1251', 'UTF-8', $letter['company3']['city_title']),
                        'company3_address'          => iconv('CP1251', 'UTF-8', $letter['company3']['address']),
                        'user3_i_form_type'         => $letter['user3_i']['form_type'],
                        'user3_i_1_address'         => iconv('CP1251', 'UTF-8', $letter['user3_i'][1]['address']),
                        'user3_i_2_address'         => iconv('CP1251', 'UTF-8', $letter['user3_i'][2]['address']),
                        'user_status_date_3'        => dateFormat("d.m.Y", $letter['user_status_date_3']),
                        'delivery_title'            => iconv('CP1251', 'UTF-8', $letter['delivery_title']),
                        'delivery_cost'             => sprintf("%01.2f", $letter['delivery_cost']),
                        'parent'                    => $letter['parent'],
                        'parent_title'              => iconv('CP1251', 'UTF-8', reformat(htmlspecialchars($letter['parent_title']),20)),
                        'comment'                   => iconv('CP1251', 'UTF-8', reformat(htmlspecialchars($letter['comment']),20)),
                        'file_link'                 => $file_link,
                        'withoutourdoc'             => $letter['withoutourdoc']
                    );
                    $aData[] = $aTmp;
                }
                $letter_num++;
            } else {
                if($oletter['is_user_1_company']=='t') {
                    $company = letters::getCompany($oletter['user_1']);
                    if($company['frm_type']) {
                        $company['name'] = $company['frm_type'].' "'.$company['name'].'"';
                    }
                    $oletter['company1_name'] = $company['name'];
                    $oletter['company1'] = $company;
                } else {
                    $user1 = new users();
                    $user1->GetUserByUID($oletter['user_1']);
                    $oletter['user1_uname'] = $user1->uname;
                    $oletter['user1_usurname'] = $user1->usurname;
                    $oletter['user1_login'] = $user1->login;
                    $oletter['user1_i'] = letters::getUserReqvs($oletter['user_1']);
                }
                if($oletter['is_user_2_company']=='t') {
                    $company = letters::getCompany($oletter['user_2']);
                    if($company['frm_type']) {
                        $company['name'] = $company['frm_type'].' "'.$company['name'].'"';
                    }
                    $oletter['company2_name'] = $company['name'];
                    $oletter['company2'] = $company;
                } else {
                    $user2 = new users();
                    $user2->GetUserByUID($oletter['user_2']);
                    $oletter['user2_uname'] = $user2->uname;
                    $oletter['user2_usurname'] = $user2->usurname;
                    $oletter['user2_login'] = $user2->login;
                    $oletter['user2_i'] = letters::getUserReqvs($oletter['user_2']);
                }
                if($oletter['user_3']) {
                    if($oletter['is_user_3_company']=='t') {
                        $company = letters::getCompany($oletter['user_3']);
                        if($company['frm_type']) {
                            $company['name'] = $company['frm_type'].' "'.$company['name'].'"';
                        }
                        $oletter['company3_name'] = $company['name'];
                        $oletter['company3'] = $company;
                    } else {
                        $user3 = new users();
                        $user3->GetUserByUID($oletter['user_3']);
                        $oletter['user3_uname'] = $user3->uname;
                        $oletter['user3_usurname'] = $user3->usurname;
                        $oletter['user3_login'] = $user3->login;
                        $oletter['user3_i'] = letters::getUserReqvs($oletter['user_3']);
                    }
                }
                $letter = $oletter;
                if($letter['file_id']) {
                    $cFile = new CFile($letter['file_id']);
                    $file_link = WDCPREFIX."/".$cFile->path.$cFile->name;
                } else {
                    $file_link = '';
                }
                $aTmp = array(
                    'id'                        => iconv('CP1251', 'UTF-8', $letter['id']),
                    'nn'                        => iconv('CP1251', 'UTF-8', $nn),
                    'ukey0'                     => iconv('CP1251', 'UTF-8', $ukey[0]),
                    'delivery0'                 => iconv('CP1251', 'UTF-8', $oletter[0]['delivery']),
                    'user1_uname'               => iconv('CP1251', 'UTF-8', $letter['user1_uname']),
                    'user1_usurname'            => iconv('CP1251', 'UTF-8', $letter['user1_usurname']),
                    'user1_login'               => iconv('CP1251', 'UTF-8', $letter['user1_login']),
                    'user2_uname'               => iconv('CP1251', 'UTF-8', $letter['user2_uname']),
                    'user2_usurname'            => iconv('CP1251', 'UTF-8', $letter['user2_usurname']),
                    'user2_login'               => iconv('CP1251', 'UTF-8', $letter['user2_login']),
                    'user3_uname'               => iconv('CP1251', 'UTF-8', $letter['user3_uname']),
                    'user3_usurname'            => iconv('CP1251', 'UTF-8', $letter['user3_usurname']),
                    'user3_login'               => iconv('CP1251', 'UTF-8', $letter['user3_login']),
                    'date_change_status'        => iconv('CP1251', 'UTF-8', dateFormat("d.m.Y, H:i", $letter['date_change_status'])),
                    'user_status_1'             => iconv('CP1251', 'UTF-8', $letter['user_status_1']),
                    'user_status_2'             => iconv('CP1251', 'UTF-8', $letter['user_status_2']),
                    'user_status_3'             => iconv('CP1251', 'UTF-8', $letter['user_status_3']),
                    'user_status_1_icon'        => letters::$status_icons[intval($letter['user_status_1'])],
                    'user_status_2_icon'        => letters::$status_icons[intval($letter['user_status_2'])],
                    'user_status_3_icon'        => letters::$status_icons[intval($letter['user_status_3'])],
                    'user_status_1_color'       => letters::$status_colors[intval($letter['user_status_1'])],
                    'user_status_2_color'       => letters::$status_colors[intval($letter['user_status_2'])],
                    'user_status_3_color'       => letters::$status_colors[intval($letter['user_status_3'])],
                    'is_user_1_company'         => $letter['is_user_1_company'],
                    'is_user_2_company'         => $letter['is_user_2_company'],
                    'is_user_3_company'         => $letter['is_user_3_company'],
                    'number'                    => $letter['number'],
                    'group_title'               => iconv('CP1251', 'UTF-8', reformat(htmlspecialchars($letter['group_title']),20)),
                    'group_id'                  => $letter['group_id'],
                    'title'                     => iconv('CP1251', 'UTF-8', reformat(htmlspecialchars($letter['title']),20)),
                    'user_1'                    => $letter['user_1'],
                    'user_2'                    => $letter['user_2'],
                    'user_3'                    => $letter['user_3'],
                    'company1_name'             => iconv('CP1251', 'UTF-8', $letter['company1_name']),
                    'company2_name'             => iconv('CP1251', 'UTF-8', $letter['company2_name']),
                    'company3_name'             => iconv('CP1251', 'UTF-8', $letter['company3_name']),
                    'company1_index'            => iconv('CP1251', 'UTF-8', $letter['company1']['index']),
                    'company1_country_title'    => iconv('CP1251', 'UTF-8', $letter['company1']['country_title']),
                    'company1_city_title'       => iconv('CP1251', 'UTF-8', $letter['company1']['city_title']),
                    'company1_address'          => iconv('CP1251', 'UTF-8', $letter['company1']['address']),
                    'user1_i_form_type'         => $letter['user1_i']['form_type'],
                    'user1_i_1_address'         => iconv('CP1251', 'UTF-8', $letter['user1_i'][1]['address']),
                    'user1_i_2_address'         => iconv('CP1251', 'UTF-8', $letter['user1_i'][2]['address']),
                    'user_status_date_1'        => dateFormat("d.m.Y", $letter['user_status_date_1']),
                    'user1_i_1_fio'             => iconv('CP1251', 'UTF-8', $letter['user1_i'][1]['fio']),
                    'user1_i_2_full_name'       => iconv('CP1251', 'UTF-8', $letter['user1_i'][2]['full_name']),
                    'user2_i_1_fio'             => iconv('CP1251', 'UTF-8', $letter['user2_i'][1]['fio']),
                    'user2_i_2_full_name'       => iconv('CP1251', 'UTF-8', $letter['user2_i'][2]['full_name']),
                    'user3_i_1_fio'             => iconv('CP1251', 'UTF-8', $letter['user3_i'][1]['fio']),
                    'user3_i_2_full_name'       => iconv('CP1251', 'UTF-8', $letter['user3_i'][2]['full_name']),
                    'company2_index'            => iconv('CP1251', 'UTF-8', $letter['company2']['index']),
                    'company2_country_title'    => iconv('CP1251', 'UTF-8', $letter['company2']['country_title']),
                    'company2_city_title'       => iconv('CP1251', 'UTF-8', $letter['company2']['city_title']),
                    'company2_address'          => iconv('CP1251', 'UTF-8', $letter['company2']['address']),
                    'user2_i_form_type'         => $letter['user2_i']['form_type'],
                    'user2_i_1_address'         => iconv('CP1251', 'UTF-8', $letter['user2_i'][1]['address']),
                    'user2_i_2_address'         => iconv('CP1251', 'UTF-8', $letter['user2_i'][2]['address']),
                    'user_status_date_2'        => dateFormat("d.m.Y", $letter['user_status_date_2']),
                    'company3_index'            => iconv('CP1251', 'UTF-8', $letter['company3']['index']),
                    'company3_country_title'    => iconv('CP1251', 'UTF-8', $letter['company3']['country_title']),
                    'company3_city_title'       => iconv('CP1251', 'UTF-8', $letter['company3']['city_title']),
                    'company3_address'          => iconv('CP1251', 'UTF-8', $letter['company3']['address']),
                    'user3_i_form_type'         => $letter['user3_i']['form_type'],
                    'user3_i_1_address'         => iconv('CP1251', 'UTF-8', $letter['user3_i'][1]['address']),
                    'user3_i_2_address'         => iconv('CP1251', 'UTF-8', $letter['user3_i'][2]['address']),
                    'user_status_date_3'        => dateFormat("d.m.Y", $letter['user_status_date_3']),
                    'delivery_title'            => iconv('CP1251', 'UTF-8', $letter['delivery_title']),
                    'delivery_cost'             => sprintf("%01.2f", $letter['delivery_cost']),
                    'parent'                    => $letter['parent'],
                    'parent_title'              => iconv('CP1251', 'UTF-8', reformat(htmlspecialchars($letter['parent_title']),20)),
                    'comment'                   => iconv('CP1251', 'UTF-8', reformat(htmlspecialchars($letter['comment']),20)),
                    'file_link'                 => $file_link,
                    'withoutourdoc'             => $letter['withoutourdoc']
                );
                $aData[] = $aTmp;
            }
        }
        echo new_paginator($page, $pages, 4, "%s\"letters.changePage({$type}, %d); return false;\"%s", 'onclick');
        $pager_html = ob_get_contents();
        ob_end_clean();
    }

    
    }

    $res['success'] = true;
    $res['data'] = $aData;
    $res['pager'] = iconv('CP1251', 'UTF-8', $pager_html);

    echo json_encode($res);
//    return $objResponse;
}

/**
 * Изменение свойства "Документ без нашего экземпляра"
 *
 * @param     integer    $id            ID документа
 * @param     boolean    $is_checked    статус свойства
 */
function changeWithoutourdocs($id, $is_checked) {
    global $objLetters;
    
    $objResponse = new xajaxResponse();

    if( (hasPermissions('letters') && hasPermissions('adm')) ) {
        $current = $objLetters->changeWithoutourdocs($id, $is_checked);
//        $objResponse->script('$(letters_withoutourdocs_check_'.$id.').set("checked", "'.($is_checked ? 'true' : 'false').'");');
        $objResponse->script('letters.spinner.hide();');
    }

    return $objResponse;    
}

/**
 * Удалить документ
 *
 * @param  integer  $id ID документа
 * @return object xajaxResponse
 */
function delDoc($id) {
    global $objLetters;
    
    $objResponse = new xajaxResponse();

    if( (hasPermissions('letters') && hasPermissions('adm')) ) {

    $objLetters->delDocument($id);    

    $objResponse->script('letters.spinner.hide();');
    $objResponse->script('letters.hideAddForm();');
    $objResponse->script('letters.changeTabs(1);');

    }

    return $objResponse;    
}

/**
 * Сформировать документы
 *
 * @param  string  $frm  Информация о документах
 * @return object xajaxResponse
 */
function processDocs($frm) {
    global $objLetters;
    
    $objResponse = new xajaxResponse();
    
    if( (hasPermissions('letters') && hasPermissions('adm')) ) {
        $ids = $frm;
        $ids = preg_replace("/,$/", "", $ids);
        $ids_docs = preg_split("/,/", $ids);
        if($ids_docs) {
            foreach($ids_docs as $k=>$v) {
                if(intVal($v)!=$v) {
                    unset($ids_docs[$k]);
                }
            }
        }
        $objLetters->processDocs($ids_docs);

        $objResponse->script("letters.reload_data();");
    }

    return $objResponse;
}

/**
 * Сформировать документы для отправки
 *
 * @param  string  $frm  Информация о документах
 * @return object xajaxResponse
 */
function processSendDocs($frm) {
    global $objLetters;
    
    $objResponse = new xajaxResponse();
    
    if( (hasPermissions('letters') && hasPermissions('adm')) ) {
        $d = preg_split("/\|/", $frm);

        $d_ids = $d[0];
        $d_ids = preg_replace("/,$/", "", $d_ids);
        $ids_docs = preg_split("/,/", $d_ids);
        if($ids_docs) {
            foreach($ids_docs as $k=>$v) {
                if(intVal($v)!=$v) {
                    unset($ids_docs[$k]);
                }
            }
        }

        $u_ids = $d[1];
        $u_ids = preg_replace("/,$/", "", $u_ids);
        $ids_users = preg_split("/,/", $u_ids);
        if($ids_users) {
            foreach($ids_users as $k=>$v) {
                if(intVal($v)!=$v) {
                    unset($ids_users[$k]);
                }
            }
        }

        $delivery_ids = $d[2];
        $delivery_ids = preg_replace("/,$/", "", $delivery_ids);
        $ids_delivery = preg_split("/,/", $delivery_ids);
        if($ids_delivery) {
            foreach($ids_delivery as $k=>$v) {
                if(intVal($v)!=$v && $v!='null') {
                    unset($ids_delivery[$k]);
                } else {
                    if($v=='null') {
                        $ids_delivery[$k] = 0;
                    }
                }
            }
        }

        $docs_without_delivery = $objLetters->processSendDocs($ids_docs, $ids_users, $ids_delivery);

        if($docs_without_delivery) {
            $str = "У следующих документах не выбран тип доставки:\\n";
            foreach($docs_without_delivery as $v) {
                $t = $v['title'];
                $t = preg_replace("/\\\/", '\\', $t);
                $t = preg_replace("/'/", '"', $t);
                $str .= "{$v['id']} - ".$t."\\n";
            }
            $objResponse->script("alert('{$str}');");
            $objResponse->script("letters.spinner.hide();");
        } else {
            $objResponse->script("letters.getArchive();");
        }
    }

    return $objResponse;
}

/**
 * Редактирование документа
 *
 * @param  integer  $id ID документа
 * @return object xajaxResponse
 */
function editDoc($id) {
    global $objLetters;
    
    $objResponse = new xajaxResponse();

    if( (hasPermissions('letters') && hasPermissions('adm')) ) {

    $doc = $objLetters->getDocument($id);    

    if($doc) {
        if(!$doc['user_status_1']) $doc['user_status_1'] = NULL;
        if(!$doc['user_status_2']) $doc['user_status_2'] = NULL;
        if(!$doc['user_status_3']) $doc['user_status_3'] = NULL;
        $objResponse->assign("letters_doc_frm_title", "value", $doc['title']);
        $objResponse->script("
                                $('letters_doc_frm_delivery_cost').set('value', '');
                                $('letters_doc_frm_comment').set('value', '');
                                ComboboxManager.getInput('letters_doc_frm_group').clear();
                                ComboboxManager.getInput('letters_doc_frm_delivery').clear();
                                ComboboxManager.getInput('letters_doc_frm_user_1').clear();
                                ComboboxManager.getInput('letters_doc_frm_user_2').clear();
                                ComboboxManager.getInput('letters_doc_frm_user_3').clear();
                                ComboboxManager.getInput('letters_doc_frm_parent').clear();
                                $('letters_doc_frm_user1_status_data').set('value', 0);
                                $('letters_doc_frm_user1_status_date_data').set('value', '');
                                $('letters_doc_frm_user_1_status_change_lnk').set('html', statuses_list[0]);
                                $('letters_doc_frm_user2_status_data').set('value', 0);
                                $('letters_doc_frm_user2_status_date_data').set('value', '');
                                $('letters_doc_frm_user_2_status_change_lnk').set('html', statuses_list[0]);
                                $('letters_doc_frm_user3_status_data').set('value', 0);
                                $('letters_doc_frm_user3_status_date_data').set('value', '');
                                $('letters_doc_frm_user_3_status_change_lnk').set('html', statuses_list[0]);
                                if($('letters_doc_frm_user_3_div').getStyle('display')=='block') {
                                    letters.toggleUser3();
                                }
                            ");
        if($doc['user_3']) {
            $objResponse->script('letters.toggleUser3();');
            $objResponse->script('ComboboxManager.getInput("letters_doc_frm_user_3").loadRecord('.$doc['user_3'].', "get_user_or_company_info", "type='.($doc['is_user_3_company']=='t' ? 'company' : 'user').'");');
        }
        if($doc['withoutourdoc']=='t') {
            $objResponse->script('$("letters_doc_frm_withoutourdoc").set("checked", true);');
        }

        if($doc['user_status_1']==2 || $doc['user_status_1']==3) {
            $add_status1 = ' '.dateFormat('d.m.Y', $doc['user_status_date_1']);
        }
        if($doc['user_status_2']==2 || $doc['user_status_2']==3) {
            $add_status2 = ' '.dateFormat('d.m.Y', $doc['user_status_date_2']);
        }
        if($doc['user_status_3']==2 || $doc['user_status_1']==3) {
            $add_status3 = ' '.dateFormat('d.m.Y', $doc['user_status_date_3']);
        }
        $objResponse->script("$('letters_doc_frm_user_1_status_change_lnk').set('html', statuses_list[".intval($doc['user_status_1'])."]+'{$add_status1}')");
        $objResponse->script("$('letters_doc_frm_user_2_status_change_lnk').set('html', statuses_list[".intval($doc['user_status_2'])."]+'{$add_status2}')");
        $objResponse->script("$('letters_doc_frm_user_3_status_change_lnk').set('html', statuses_list[".intval($doc['user_status_3'])."]+'{$add_status3}')");
        $objResponse->assign('letters_doc_frm_user1_status_data', 'value', intval($doc['user_status_1']));
        $objResponse->assign('letters_doc_frm_user2_status_data', 'value', intval($doc['user_status_2']));
        $objResponse->assign('letters_doc_frm_user3_status_data', 'value', intval($doc['user_status_3']));
        $objResponse->assign('letters_doc_frm_user1_status_date_data', 'value', ($doc['user_status_date_1'] ? dateFormat('Y-m-d', $doc['user_status_date_1']) : ''));
        $objResponse->assign('letters_doc_frm_user2_status_date_data', 'value', ($doc['user_status_date_2'] ? dateFormat('Y-m-d', $doc['user_status_date_2']) : ''));
        $objResponse->assign('letters_doc_frm_user3_status_date_data', 'value', ($doc['user_status_date_3'] ? dateFormat('Y-m-d', $doc['user_status_date_3']) : ''));

        if($doc['group_id']) {
            $objResponse->script('ComboboxManager.getInput("letters_doc_frm_group").loadRecord('.$doc['group_id'].', "getlettergroupinfo");');
        }
        $objResponse->script('ComboboxManager.getInput("letters_doc_frm_user_1").loadRecord('.$doc['user_1'].', "get_user_or_company_info", "type='.($doc['is_user_1_company']=='t' ? 'company' : 'user').'");');
        $objResponse->script('ComboboxManager.getInput("letters_doc_frm_user_2").loadRecord('.$doc['user_2'].', "get_user_or_company_info", "type='.($doc['is_user_2_company']=='t' ? 'company' : 'user').'");');
        if($doc['parent']) {
            $objResponse->script('ComboboxManager.getInput("letters_doc_frm_parent").loadRecord('.$doc['parent'].', "getletterdocinfo");');
        }
        if($doc['date_add']) {
            $objResponse->script('ComboboxManager.getInput("letters_doc_frm_dateadd").setDate("'.preg_replace("/ .*$/","",$doc['date_add']).'");');
        }


        

        if($doc['file_id']) {
            $cFile = new CFIle();
            $cFile->table = 'file';
            $cFile->GetInfoById($doc['file_id']);

            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/attachedfiles.php");
            $attachedfiles = new attachedfiles('', true);
            $asid = $attachedfiles->createSessionID();
            $attachedfiles->addNewSession($asid);

            $attachedfiles->setFiles(array($cFile->id));

            $p_name = preg_replace("/\..*$/","",$cFile->name);

            ob_start();
            echo '<div id="attachedfiles">';
            require_once($_SERVER['DOCUMENT_ROOT'].'/siteadmin/letters/tpl.attachedfiles.php');
            echo '</div>';
            echo "<input type='hidden' id='attachedfiles_uid' name='attachedfiles_uid' value='".get_uid(false)."'>";
            echo "<input type='hidden' id='attachedfiles_action' name='attachedfiles_action' value=''>";
            echo "<input type='hidden' id='attachedfiles_delete' name='attachedfiles_delete' value=''>";
            echo "<input type='hidden' id='attachedfiles_type' name='attachedfiles_type' value='letters'>";
            echo "<input type='hidden' id='attachedfiles_session' name='attachedfiles_session' value='".$asid."'>";
            echo "<iframe id='attachedfiles_hiddenframe' name='attachedfiles_hiddenframe' style='display:none;'></iframe>";
            $html = ob_get_contents();
            ob_end_clean();
            $objResponse->assign("letters_div_attach", "innerHTML", $html);

            $objResponse->script("(function () {

                                    var attachedfiles_list = new Array();

                                    attachedfiles_list[0] = new Object;
                                    attachedfiles_list[0].id = '".md5($cFile->id)."';
                                    attachedfiles_list[0].name = '".$cFile->original_name."';
                                    attachedfiles_list[0].path = '".WDCPREFIX."/".$cFile->path.$cFile->name."';
                                    attachedfiles_list[0].size = '".ConvertBtoMB($cFile->size)."';
                                    attachedfiles_list[0].type = '".$cFile->getExt()."';

                                    attachedFiles.initComm( 'attachedfiles', 
                                                            '{$attachedfiles->getSession()}',
                                                            attachedfiles_list, 
                                                            '1',
                                                            '".letters::MAX_FILE_SIZE."',
                                                            '".implode(', ', $GLOBALS['disallowed_array'])."',
                                                            'letters',
                                                            '".get_uid(false)."'
                                                            );
                                    attachedFiles.newFile(attachedfiles_list[0].id, attachedfiles_list[0].name, attachedfiles_list[0].path, attachedfiles_list[0].size, attachedfiles_list[0].type);
                                })();
                                $('wd_file_add').setStyle('display', 'none');
                                ");
            $objResponse->assign('attachedfiles_uid', 'value', get_uid(false));
            $objResponse->assign('attachedfiles_session', 'value', $attachedfiles->getSession());
        
            $objResponse->script("$('f_button_actionwork').removeClass('b-button_rectangle_color_disable');");
        }
        $objResponse->script("status_can_submit = true;");
        
        $objResponse->script("$('letters_add_div').getChildren('div').removeClass('b-shadow_hide');");


    }

    $objResponse->script('letters.spinner.hide();');

    }

    return $objResponse;    
}

/**
 * Показать документ
 *
 * @param  integer  $id ID документа
 * @return object xajaxResponse
 */
function showDoc($id) {
    global $objLetters;
    
    $objResponse = new xajaxResponse();

    if( (hasPermissions('letters') && hasPermissions('adm')) ) {

    $doc = $objLetters->getDocument($id);

    if($doc) {
        $qstatuses = $objLetters->getStatuses();
        foreach($qstatuses as $qstatus) {
            $statuses[$qstatus['id']] = $qstatus['title'];
        }
        $statuses[0] = 'Добавить статус';
        $html = '';
        if($doc['is_user_1_company']=='t') {
            $company = letters::getCompany($doc['user_1']);
            if($company['frm_type']) {
                $company['name'] = $company['frm_type'].' "'.$company['name'].'"';
            }
            $doc['company1'] = $company;
        } else {
            $user1 = new users();
            $user1->GetUserByUID($doc['user_1']);
            $doc['user1_uname'] = $user1->uname;
            $doc['user1_usurname'] = $user1->usurname;
            $doc['user1_login'] = $user1->login;
            $doc['user1_i'] = letters::getUserReqvs($doc['user_1']);
        }
        if($doc['is_user_2_company']=='t') {
            $company = letters::getCompany($doc['user_2']);
            if($company['frm_type']) {
                $company['name'] = $company['frm_type'].' "'.$company['name'].'"';
            }
            $doc['company2'] = $company;
        } else {
            $user2 = new users();
            $user2->GetUserByUID($doc['user_2']);
            $doc['user2_uname'] = $user2->uname;
            $doc['user2_usurname'] = $user2->usurname;
            $doc['user2_login'] = $user2->login;
            $doc['user2_i'] = letters::getUserReqvs($doc['user_2']);
        }
        if($doc['user_3']) {
            if($doc['is_user_3_company']=='t') {
                $company = letters::getCompany($doc['user_3']);
                if($company['frm_type']) {
                    $company['name'] = $company['frm_type'].' "'.$company['name'].'"';
                }
                $doc['company3'] = $company;
            } else {
                $user3 = new users();
                $user3->GetUserByUID($doc['user_3']);
                $doc['user3_uname'] = $user3->uname;
                $doc['user3_usurname'] = $user3->usurname;
                $doc['user3_login'] = $user3->login;
                $doc['user3_i'] = letters::getUserReqvs($doc['user_3']);
            }
        }
        $user4 = new users();
        $user4->GetUserByUID($doc['user_add']);
        $doc['useradd_uname'] = $user4->uname;
        $doc['useradd_usurname'] = $user4->usurname;
        $doc['useradd_login'] = $user4->login;
        $history = $objLetters->getHistory($id);
        ob_start();
        require_once($_SERVER['DOCUMENT_ROOT'].'/siteadmin/letters/tpl.view.item.php');
        $html = ob_get_contents();
        ob_end_clean();
        $objResponse->assign('letters_wrapper_view', 'innerHTML', $html);
        $objResponse->script('$("letters_wrapper_view").show();');
        $objResponse->script('$("letters_wrapper").hide();');
        $objResponse->script('$("letters_notfound").hide();');
    }

    $objResponse->script('letters.spinner.hide();');
    
    }

    return $objResponse;
}

/**
 * Получить список корреспонденции группы
 * 
 * @param  integer  $group_id ID группы
 * @return object xajaxResponse
 */
function showGroup( $group_id ) {    
    global $objLetters;
    
    $objResponse = new xajaxResponse();

    if( (hasPermissions('letters') && hasPermissions('adm')) ) {

    $filter['letters_filter_group_db_id'] = $group_id;
    $letters = $objLetters->getLetters(0, $filter);
    if($letters) {
        $qstatuses = $objLetters->getStatuses();
        foreach($qstatuses as $qstatus) {
            $statuses[$qstatus['id']] = $qstatus['title'];
        }
        $statuses[0] = 'Добавить статус';
        $html = '';
        ob_start();
        require_once($_SERVER['DOCUMENT_ROOT'].'/siteadmin/letters/tpl.list.header.php');
        $nn = 0;
        foreach($letters as $letter) {
            if($letter['is_user_1_company']=='t') {
                $company = letters::getCompany($letter['user_1']);
                if($company['frm_type']) {
                    $company['name'] = $company['frm_type'].' "'.$company['name'].'"';
                }
                $letter['company1_name'] = $company['name'];
                $letter['company1'] = $company;
            } else {
                $user1 = new users();
                $user1->GetUserByUID($letter['user_1']);
                $letter['user1_uname'] = $user1->uname;
                $letter['user1_usurname'] = $user1->usurname;
                $letter['user1_login'] = $user1->login;
                $letter['user1_i'] = letters::getUserReqvs($letter['user_1']);
            }
            if($letter['is_user_2_company']=='t') {
                $company = letters::getCompany($letter['user_2']);
                if($company['frm_type']) {
                    $company['name'] = $company['frm_type'].' "'.$company['name'].'"';
                }
                $letter['company2_name'] = $company['name'];
                $letter['company2'] = $company;
            } else {
                $user2 = new users();
                $user2->GetUserByUID($letter['user_2']);
                $letter['user2_uname'] = $user2->uname;
                $letter['user2_usurname'] = $user2->usurname;
                $letter['user2_login'] = $user2->login;
                $letter['user2_i'] = letters::getUserReqvs($letter['user_2']);
            }
            if($letter['user_3']) {
                if($letter['is_user_3_company']=='t') {
                    $company = letters::getCompany($letter['user_3']);
                    if($company['frm_type']) {
                        $company['name'] = $company['frm_type'].' "'.$company['name'].'"';
                    }
                    $letter['company3_name'] = $company['name'];
                    $letter['company3'] = $company;
                } else {
                    $user3 = new users();
                    $user3->GetUserByUID($letter['user_3']);
                    $letter['user3_uname'] = $user3->uname;
                    $letter['user3_usurname'] = $user3->usurname;
                    $letter['user3_login'] = $user3->login;
                    $letter['user3_i'] = letters::getUserReqvs($letter['user_3']);
                }
            }
            echo "<div id='letters_div_item_{$letter['id']}' class='b-fon b-fon_marglr_-10 b-fon_padbot_10'>";
            require($_SERVER['DOCUMENT_ROOT'].'/siteadmin/letters/tpl.list.item.php');
            echo "</div>";
        }
        $html = ob_get_contents();
        ob_end_clean();

        $objResponse->assign('letters_wrapper_view', 'innerHTML', '');
        $objResponse->assign('letters_data', 'innerHTML', $html);
        $objResponse->script('$("letters_data").show();');
        $objResponse->script('$("letters_notfound").hide();');
        $objResponse->assign('letters_h_list_title1', 'innerHTML', $letters[0]['group_title']);
        $objResponse->assign('letters_h_list_title2', 'innerHTML', '');
        $objResponse->script('$("letters_h_list").setStyle("display", "none"); $("letters_h_list_group").setStyle("display", "block"); $("letters_h_list_title1").setStyle("display", "block"); $("letters_h_list_title2").setStyle("display", "block");');
    } else {
        $objResponse->script('$("letters_data").hide();');
        $objResponse->script('$("letters_notfound").show();');
    }

    $objResponse->script('$("letters_wrapper_view").hide();');
    $objResponse->script('$("letters_wrapper").show();');

    $objResponse->script('letters.spinner.hide();');
    
    }

    return $objResponse;
}


/**
 * Получить список корреспонденции для пользователя
 * 
 * @param  integer  $user_id ID пользователя    
 * @return object xajaxResponse
 */
function showByUser( $user_id, $is_company ) {    
    global $objLetters;
    
    $objResponse = new xajaxResponse();

    if( (hasPermissions('letters') && hasPermissions('adm')) ) {

    $filter['letters_filter_get_user_db_id'] = $user_id;
    $filter['letters_filter_get_user_section'] = ($is_company=='t' ? '1' : '0');
    $letters = $objLetters->getLetters(0, $filter);
    if($letters) {
        $qstatuses = $objLetters->getStatuses();
        foreach($qstatuses as $qstatus) {
            $statuses[$qstatus['id']] = $qstatus['title'];
        }
        $statuses[0] = 'Добавить статус';
        $oletters = array();
        foreach($letters as $letter) {
            $oletters[intval($letter['group_id'])][] = $letter;
        }
        $html = '';
        ob_start();
        require_once($_SERVER['DOCUMENT_ROOT'].'/siteadmin/letters/tpl.list.header.php');
        $nn = 0;

foreach($oletters as $oletter) {
        echo '<div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_bold">'.($oletter[0]['group_title'] ? $oletter[0]['group_title'] : '[Без группы]').'</div>';
        foreach($oletter as $letter) {
            if($letter['is_user_1_company']=='t') {
                $company = letters::getCompany($letter['user_1']);
                if($company['frm_type']) {
                    $company['name'] = $company['frm_type'].' "'.$company['name'].'"';
                }
                $letter['company1_name'] = $company['name'];
                $letter['company1'] = $company;
            } else {
                $user1 = new users();
                $user1->GetUserByUID($letter['user_1']);
                $letter['user1_uname'] = $user1->uname;
                $letter['user1_usurname'] = $user1->usurname;
                $letter['user1_login'] = $user1->login;
                $letter['user1_i'] = letters::getUserReqvs($letter['user_1']);
            }
            if($letter['is_user_2_company']=='t') {
                $company = letters::getCompany($letter['user_2']);
                if($company['frm_type']) {
                    $company['name'] = $company['frm_type'].' "'.$company['name'].'"';
                }
                $letter['company2_name'] = $company['name'];
                $letter['company2'] = $company;
            } else {
                $user2 = new users();
                $user2->GetUserByUID($letter['user_2']);
                $letter['user2_uname'] = $user2->uname;
                $letter['user2_usurname'] = $user2->usurname;
                $letter['user2_login'] = $user2->login;
                $letter['user2_i'] = letters::getUserReqvs($letter['user_2']);
            }
            if($letter['user_3']) {
                if($letter['is_user_3_company']=='t') {
                    $company = letters::getCompany($letter['user_3']);
                    if($company['frm_type']) {
                        $company['name'] = $company['frm_type'].' "'.$company['name'].'"';
                    }
                    $letter['company3_name'] = $company['name'];
                    $letter['company3'] = $company;
                } else {
                    $user3 = new users();
                    $user3->GetUserByUID($letter['user_3']);
                    $letter['user3_uname'] = $user3->uname;
                    $letter['user3_usurname'] = $user3->usurname;
                    $letter['user3_login'] = $user3->login;
                    $letter['user3_i'] = letters::getUserReqvs($letter['user_3']);
                }
            }
            echo "<div id='letters_div_item_{$letter['id']}' class='b-fon b-fon_marglr_-10 b-fon_padbot_10'>";
            require($_SERVER['DOCUMENT_ROOT'].'/siteadmin/letters/tpl.list.item.php');
            echo "</div>";
        }
}
        $html = ob_get_contents();
        ob_end_clean();
        $objResponse->assign('letters_wrapper_view', 'innerHTML', '');
        $objResponse->assign('letters_data', 'innerHTML', $html);
        $objResponse->script('$("letters_data").show();');
        $objResponse->script('$("letters_notfound").hide();');

        if($is_company=='t') {
            $company = letters::getCompany($user_id);
            if($company['frm_type']) {
                $company['name'] = $company['frm_type'].' "'.$company['name'].'"';
            }
            $user_title = $company['name'];
            $user_address = "{$company['index']}, {$company['country_title']}, {$company['city_title']}, {$company['address']}";

        } else {
            $user = new users();
            $user->GetUserByUID($user_id);
            $user_sbr = letters::getUserReqvs($user_id);
            $user_title = ($user_sbr['form_type']==1 ? $user_sbr[1]['fio'] : $user_sbr[2]['full_name']);
            $user_address =  ($user_sbr['form_type']==1 ? $user_sbr[1]['index'] : $user_sbr[2]['index']).", ".
                        ($user_sbr['form_type']==1 ? $user_sbr[1]['country'] : $user_sbr[2]['country']).", ".
                        ($user_sbr['form_type']==1 ? $user_sbr[1]['city'] : $user_sbr[2]['city']).", ".
                        ($user_sbr['form_type']==1 ? $user_sbr[1]['address'] : $user_sbr[2]['address']);

        }

        $objResponse->assign('letters_h_list_title1', 'innerHTML', $user_title);
        $objResponse->assign('letters_h_list_title2', 'innerHTML', $user_address);
        $objResponse->script('$("letters_h_list").setStyle("display", "none"); $("letters_h_list_group").setStyle("display", "block"); $("letters_h_list_title1").setStyle("display", "block"); $("letters_h_list_title2").setStyle("display", "block");');

    } else {
        $objResponse->script('$("letters_data").hide();');
        $objResponse->script('$("letters_notfound").show();');
    }

    $objResponse->script('$("letters_wrapper_view").hide();');
    $objResponse->script('$("letters_wrapper").show();');

    $objResponse->script('letters.spinner.hide();');
    
    }

    return $objResponse;
}


/**
 * Ресет загрузки файлов
 *
 * @return object xajaxResponse
 */
function resetAttachedFiles() {
    $objResponse = new xajaxResponse();

    if( (hasPermissions('letters') && hasPermissions('adm')) ) {

    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/attachedfiles.php");
    $attachedfiles = new attachedfiles('', true);
    $asid = $attachedfiles->createSessionID();
    $attachedfiles->addNewSession($asid);

    ob_start();
    echo '<div id="attachedfiles">';
    require_once($_SERVER['DOCUMENT_ROOT'].'/siteadmin/letters/tpl.attachedfiles.php');
    echo '</div>';
    echo "<input type='hidden' id='attachedfiles_uid' name='attachedfiles_uid' value='".get_uid(false)."'>";
    echo "<input type='hidden' id='attachedfiles_action' name='attachedfiles_action' value=''>";
    echo "<input type='hidden' id='attachedfiles_delete' name='attachedfiles_delete' value=''>";
    echo "<input type='hidden' id='attachedfiles_type' name='attachedfiles_type' value='letters'>";
    echo "<input type='hidden' id='attachedfiles_session' name='attachedfiles_session' value='".$asid."'>";
    echo "<iframe id='attachedfiles_hiddenframe' name='attachedfiles_hiddenframe' style='display:none;'></iframe>";
    $html = ob_get_contents();
    ob_end_clean();
    $objResponse->assign("letters_div_attach", "innerHTML", $html);

    $objResponse->script("(function () {
                                    var attachedfiles_list = new Array();


                                    attachedFiles.initComm( 'attachedfiles', 
                                                            '{$asid}',
                                                            attachedfiles_list, 
                                                            '1',
                                                            '".letters::MAX_FILE_SIZE."',
                                                            '".implode(', ', $GLOBALS['disallowed_array'])."',
                                                            'letters',
                                                            '".get_uid(false)."'
                                                            );
                                })();
                                $('wd_file_add').setStyle('display', 'table');
                                ");

    //$objResponse->script("$('f_button_actionwork').addClass('b-button_rectangle_color_disable');");
    $objResponse->script("status_can_submit = true;");
    $objResponse->script("if (attachedFiles.newDesign) attachedFiles.initCommDomready();");

    }

    return $objResponse;
}


/**
 * Добавить корреспонденцию
 * 
 * @param  array    $frm_one    Информация о письме
 * @param  string   $frm_data   JSON если добавляется несколько документов сразу
 * @return object xajaxResponse
 */
function addLetter( $frm_one, $frm_data ) {    
    global $objLetters;
    
    $objResponse = new xajaxResponse();

    if( (hasPermissions('letters') && hasPermissions('adm')) ) {

    $errors = array();

    $frm_data = iconv('CP1251', 'UTF-8', $frm_data);
    $frm_data = stripcslashes($frm_data);
    $frm_data = json_decode($frm_data, true);
    if($frm_data) {
        foreach($frm_data as $k=>$v) {
            $frm_data[$k] = iconv('UTF-8', 'CP1251', $v);
        }
    }

    $n = 0;

    for($n=0; $n<$frm_data['count_docs']; $n++) {
        $frm = array();
        foreach ($frm_data as $k => $v) {
            if(preg_match("/^".$n."-/",$k)) {
                $key = preg_replace("/^".$n."-/", "", $k);
                $frm[$key] = $v;
            }
        }

        $errors = array();
        if(trim($frm['letters_doc_frm_title'])=='') {
            $errors['letters_doc_frm_title'] = 'Вы не ввели название документа';
        }
        /*
        if(trim($frm['letters_doc_frm_group'])=='') {
            $errors['letters_doc_frm_group'] = 'Вы не ввели группу документа';
        }
        */
        if(!$frm['letters_doc_frm_user_1_db_id'] || $frm['letters_doc_frm_user_1_db_id']=='null') {
            $errors['letters_doc_frm_user_1'] = 'Вы не выбрали пользователя';
        } else {
            /*
            if($frm['letters_doc_frm_user1_status_data']==0) {
                $errors['letters_doc_frm_user_1'] = 'Вы не выбрали статус';
            }
            */
        }
        if(!$frm['letters_doc_frm_user_2_db_id'] || $frm['letters_doc_frm_user_2_db_id']=='null') {
            $errors['letters_doc_frm_user_2'] = 'Вы не выбрали пользователя';
        } else {
            /*
            if($frm['letters_doc_frm_user2_status_data']==0) {
                $errors['letters_doc_frm_user_2'] = 'Вы не выбрали статус';
            }
            */
        }
        if($frm['letters_doc_frm_user_3_db_id']) {
            /*
            if($frm['letters_doc_frm_user3_status_data']==0) {
                $errors['letters_doc_frm_user_3'] = 'Вы не выбрали статус';
            }
            */
        }

        if($frm['letters_doc_frm_user_1_db_id']==$frm['letters_doc_frm_user_2_db_id'] && $frm['letters_doc_frm_user_1_db_id'] && $frm['letters_doc_frm_user_2_db_id'] && $frm['letters_doc_frm_user_1_db_id']!='null' && $frm['letters_doc_frm_user_2_db_id']!='null') {
            $errors['letters_doc_frm_user_1'] = 'Пользователи не могут быть одинаковыми';
            $errors['letters_doc_frm_user_2'] = 'Пользователи не могут быть одинаковыми';
        }
        if($frm['letters_doc_frm_user_3_db_id'] && $frm['letters_doc_frm_user_3_db_id']!='null' && $frm['letters_doc_frm_user_2_db_id'] && $frm['letters_doc_frm_user_2_db_id']!='null' && $frm['letters_doc_frm_user_1_db_id'] && $frm['letters_doc_frm_user_1_db_id']!='null') {
            if($frm['letters_doc_frm_user_1_db_id']==$frm['letters_doc_frm_user_3_db_id']) {
                $errors['letters_doc_frm_user_1'] = 'Пользователи не могут быть одинаковыми';
                $errors['letters_doc_frm_user_3'] = 'Пользователи не могут быть одинаковыми';
            }
            if($frm['letters_doc_frm_user_2_db_id']==$frm['letters_doc_frm_user_3_db_id']) {
                $errors['letters_doc_frm_user_2'] = 'Пользователи не могут быть одинаковыми';
                $errors['letters_doc_frm_user_3'] = 'Пользователи не могут быть одинаковыми';
            }
        }

        /*
        if(!$frm['letters_doc_frm_delivery_db_id']) {
            $errors['letters_doc_frm_delivery'] = 'Вы не выбрали тип доставки';
        }
        */
        $frm['letters_doc_frm_delivery_cost'] = preg_replace("/,/", ".", $frm['letters_doc_frm_delivery_cost']);
        if($frm['letters_doc_frm_delivery_cost'] && (!floatval($frm['letters_doc_frm_delivery_cost']) || floatval($frm['letters_doc_frm_delivery_cost'])<0)) {
            $errors['letters_doc_frm_delivery_cost'] = 'Вы не ввели недопустимое значение';
        }
        if($errors) { break; }
    }


    if($errors) {
        foreach($errors as $key=>$val) {
            $objResponse->script("$('{$key}').set('title', '{$val}');");
            $objResponse->script("$('{$key}').getParent().addClass('b-combo__input_error');");
        }
        $objResponse->script("letters.M_ShowDoc(".($n+1).", false);");
    } else {
        for($n=0; $n<$frm_data['count_docs']; $n++) {
            $frm = array();
            foreach ($frm_data as $k => $v) {
                if(preg_match("/^".$n."-/",$k)) {
                    $key = preg_replace("/^".$n."-/", "", $k);
                    $frm[$key] = $v;
                }
            }
            $frm['attachedfiles_file'] = $frm_one['attachedfiles_file'];
            $frm['attachedfiles_uid'] = $frm_one['attachedfiles_uid'];
            $frm['attachedfiles_action'] = $frm_one['attachedfiles_action'];
            $frm['attachedfiles_delete'] = $frm_one['attachedfiles_delete'];
            $frm['attachedfiles_type'] = $frm_one['attachedfiles_type'];
            $frm['attachedfiles_session'] = $frm_one['attachedfiles_session'];

            letters::addDocument($frm);
        }

        $objResponse->script('letters.spinner.hide();');
        $objResponse->script('letters.hideAddForm();');
        $objResponse->script('letters.changeTabs(1);');
    }
    $objResponse->script('status_can_submit = true;');
    }

    return $objResponse;
}

/**
 * Изменить корреспонденцию
 * 
 * @param  integer  $id    ID документа
 * @param  array    $frm   Информация о письме
 * @return object xajaxResponse
 */
function saveLetter( $id, $frm ) {    
    global $objLetters;
    
    $objResponse = new xajaxResponse();

    if( (hasPermissions('letters') && hasPermissions('adm')) ) {

    $errors = array();

    if(trim($frm['letters_doc_frm_title'])=='') {
        $errors['letters_doc_frm_title'] = 'Вы не ввели название документа';
    }
    /*
    if(trim($frm['letters_doc_frm_group'])=='') {
        $errors['letters_doc_frm_group'] = 'Вы не ввели группу документа';
    }
    */
    if(!$frm['letters_doc_frm_user_1_db_id'] || $frm['letters_doc_frm_user_1_db_id']=='null') {
        $errors['letters_doc_frm_user_1'] = 'Вы не выбрали пользователя';
    } else {
        /*
        if($frm['letters_doc_frm_user1_status_data']==0) {
            $errors['letters_doc_frm_user_1'] = 'Вы не выбрали статус';
        }
        */
    }
    if(!$frm['letters_doc_frm_user_2_db_id'] || $frm['letters_doc_frm_user_2_db_id']=='null') {
        $errors['letters_doc_frm_user_2'] = 'Вы не выбрали пользователя';
    } else {
        /*
        if($frm['letters_doc_frm_user2_status_data']==0) {
            $errors['letters_doc_frm_user_2'] = 'Вы не выбрали статус';
        }
        */
    }
    if($frm['letters_doc_frm_user_3_db_id']) {
        /*
        if($frm['letters_doc_frm_user3_status_data']==0) {
            $errors['letters_doc_frm_user_3'] = 'Вы не выбрали статус';
        }
        */
    }

    if($frm['letters_doc_frm_user_1_db_id']==$frm['letters_doc_frm_user_2_db_id'] && $frm['letters_doc_frm_user_1_db_id'] && $frm['letters_doc_frm_user_2_db_id'] && $frm['letters_doc_frm_user_1_db_id']!='null' && $frm['letters_doc_frm_user_2_db_id']!='null') {
        $errors['letters_doc_frm_user_1'] = 'Пользователи не могут быть одинаковыми';
        $errors['letters_doc_frm_user_2'] = 'Пользователи не могут быть одинаковыми';
    }
    if($frm['letters_doc_frm_user_3_db_id'] && $frm['letters_doc_frm_user_3_db_id']!='null' && $frm['letters_doc_frm_user_2_db_id'] && $frm['letters_doc_frm_user_2_db_id']!='null' && $frm['letters_doc_frm_user_1_db_id'] && $frm['letters_doc_frm_user_1_db_id']!='null') {
        if($frm['letters_doc_frm_user_1_db_id']==$frm['letters_doc_frm_user_3_db_id']) {
            $errors['letters_doc_frm_user_1'] = 'Пользователи не могут быть одинаковыми';
            $errors['letters_doc_frm_user_3'] = 'Пользователи не могут быть одинаковыми';
        }
        if($frm['letters_doc_frm_user_2_db_id']==$frm['letters_doc_frm_user_3_db_id']) {
            $errors['letters_doc_frm_user_2'] = 'Пользователи не могут быть одинаковыми';
            $errors['letters_doc_frm_user_3'] = 'Пользователи не могут быть одинаковыми';
        }
    }

    if($errors) {
        foreach($errors as $key=>$val) {
            $objResponse->script("$('{$key}').set('title', '{$val}');");
            $objResponse->script("$('{$key}').getParent().addClass('b-combo__input_error');");
        }
    } else {
        letters::updateDocument($id, $frm);

        $objResponse->script("letters.reload_data();");
        $objResponse->script('letters.hideAddForm();');
    }
    $objResponse->script('status_can_submit = true;');
    
    }

    return $objResponse;
}

/**
 * Показать список компаний
 * 
 * @param  string  $s    Первая буква в названии компании
 * @return object xajaxResponse
 */
function showCompanies($s) {
    global $objLetters;
    
    $objResponse = new xajaxResponse();

    if( (hasPermissions('letters') && hasPermissions('adm')) ) {
        $companies = $objLetters->getCompaniesBySym(strtolower($s));
        $html = '';
        if($companies) {
            ob_start();
            foreach($companies as $company) {
                require($_SERVER['DOCUMENT_ROOT'].'/siteadmin/letters/tpl.list.company.php');
            }
            $html = ob_get_contents();
            ob_end_clean();
            $objResponse->assign('letters_company_lists', 'innerHTML', $html);
        } else {
            $objResponse->assign('letters_company_lists', 'innerHTML', '<div><strong>Компаний не найдено</div>');
        }
        $objResponse->script("letters.spinner.hide();");
    }
    return $objResponse;
}


/**
 * Добавить новый шаблон
 * 
 * @param  string  $data    JSON данные
 * @return object xajaxResponse
 */
function addTemplate($frm_one, $frm_data) {
    global $objLetters;
    
    $objResponse = new xajaxResponse();

    if( (hasPermissions('letters') && hasPermissions('adm')) ) {

    $errors = array();

    $frm_data = iconv('CP1251', 'UTF-8', $frm_data);
    $frm_data = stripcslashes($frm_data);
    $frm_data = json_decode($frm_data, true);
    if($frm_data) {
        foreach($frm_data as $k=>$v) {
            $frm_data[$k] = iconv('UTF-8', 'CP1251', $v);
        }
    }

    $n = 0;

    for($n=0; $n<$frm_data['count_docs']; $n++) {
        $frm = array();
        foreach ($frm_data as $k => $v) {
            if(preg_match("/^".$n."-/",$k)) {
                $key = preg_replace("/^".$n."-/", "", $k);
                $frm[$key] = $v;
            }
        }

        $errors = array();
        $frm['letters_doc_frm_delivery_cost'] = preg_replace("/,/", ".", $frm['letters_doc_frm_delivery_cost']);
    }

        $template_id = letters::addTemplate($frm_data['template_title']);
        for($n=0; $n<$frm_data['count_docs']; $n++) {
            $frm = array();
            foreach ($frm_data as $k => $v) {
                if(preg_match("/^".$n."-/",$k)) {
                    $key = preg_replace("/^".$n."-/", "", $k);
                    $frm[$key] = $v;
                }
            }
            $frm['letters_doc_frm_template_id'] = $template_id;
            letters::addTemplateDoc($frm);
        }

    $objResponse->script('window.location = "/siteadmin/letters/?mode=templates&msg=aok";');
    }

    return $objResponse;
}


/**
 * Сохранить шаблон
 * 
 * @param  string  $data    JSON данные
 * @return object xajaxResponse
 */
function updateTemplate($frm_one, $frm_data) {
    global $objLetters;
    
    $objResponse = new xajaxResponse();

    if( (hasPermissions('letters') && hasPermissions('adm')) ) {

    $errors = array();

    $frm_data = iconv('CP1251', 'UTF-8', $frm_data);
    $frm_data = stripcslashes($frm_data);
    $frm_data = json_decode($frm_data, true);
    if($frm_data) {
        foreach($frm_data as $k=>$v) {
            $frm_data[$k] = iconv('UTF-8', 'CP1251', $v);
        }
    }

    $n = 0;

    for($n=0; $n<$frm_data['count_docs']; $n++) {
        $frm = array();
        foreach ($frm_data as $k => $v) {
            if(preg_match("/^".$n."-/",$k)) {
                $key = preg_replace("/^".$n."-/", "", $k);
                $frm[$key] = $v;
            }
        }

        $errors = array();
        $frm['letters_doc_frm_delivery_cost'] = preg_replace("/,/", ".", $frm['letters_doc_frm_delivery_cost']);
    }

        letters::updateTemplate($frm_data);
        letters::delTemplateDocs($frm_data['template_id']);
        for($n=0; $n<$frm_data['count_docs']; $n++) {
            $frm = array();
            foreach ($frm_data as $k => $v) {
                if(preg_match("/^".$n."-/",$k)) {
                    $key = preg_replace("/^".$n."-/", "", $k);
                    $frm[$key] = $v;
                }
            }
            $frm['letters_doc_frm_template_id'] = $frm_data['template_id'];
            
            letters::addTemplateDoc($frm);
        }

    $objResponse->script('window.location = "/siteadmin/letters/?mode=templates&msg=eok";');
    }

    return $objResponse;
}

$xajax->processRequest();