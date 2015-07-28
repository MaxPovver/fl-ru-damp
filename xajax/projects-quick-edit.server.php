<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/projects-quick-edit.common.php");
/**
 * @param $frm - данные запроса
 * @param $type - тип страницы, с которой была запрошена форма быстрого редактирования проекта
 *                 1 - лента на главной, 
 *                 2 - редактируется конкурс,
 *                 3 - страница проекта в профиле работодателя,
 *                 4 - проект в списке в профиле работодателя
 * */
function quickprjedit_save_prj($frm, $type) {
    $objResponse = new xajaxResponse();
    if(hasPermissions('projects')) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
        $oprj = new new_projects();
        $project = $oprj->getPrj($frm['id']);
        if($project['id']) {
            $objResponse->script('popupQEditPrjHideErrors();');

            $key = $frm['tmpid'];
            $tmpPrj = new tmp_project($key);
            $tmpPrj->init(2);

            $errors = array();

            $frm['name'] = trim($frm['name']);
            $frm['descr'] = trim($frm['descr']);

            if($frm['link']=='Адрес сайта') $frm['link']='';
            $frm['link'] == trim($frm['link']);
            if(!empty($frm['link'])) {
                if(strpos($frm['link'], 'http://') === 0)  $protocol = 'http://';
                if(strpos($frm['link'], 'https://') === 0) $protocol = 'https://';
                if($protocol == '') $protocol = 'http://';
                $frm['link'] = $protocol . ltrim($frm['link'], $protocol);
                
                if(!is_url($frm['link'])) $errors[] = 'logourl';
            }

            if(empty($frm['name'])) $errors[] = 'name';
            if(empty($frm['descr'])) $errors[] = 'descr';
            if($frm['pf_city']) $frm['city'] = $frm['pf_city'];
            
            if($project['kind']==7) {
                if (!preg_match("/^([0-9]{1,2})\-([0-9]{1,2})\-([0-9]{4})$/", $frm['end_date'], $o1) || !checkdate($o1[2], $o1[1], $o1[3])) {
                    $errors[] = 'end_date';
                }
                if (!preg_match("/^([0-9]{1,2})\-([0-9]{1,2})\-([0-9]{4})$/", $frm['win_date'], $o2) || !checkdate($o2[2], $o2[1], $o2[3])) {
                    $errors[] = 'win_date';
                }
                if (!in_array('end_date', $errors) && mktime(0, 0, 0, $o1[2], $o1[1], $o1[3]) <= mktime(0, 0, 0)) {
                    $errors[] = 'end_date_past';
                }
                if (!in_array('end_date', $errors) && mktime(0, 0, 0, $o2[2], $o2[1], $o2[3]) <= mktime(0, 0, 0, $o1[2], $o1[1], $o1[3])) {
                   $errors[] = 'win_date_past';
                }
            }
            
            if(!count($errors)) {
                $c = $frm['categories'];
                $sc =  $frm['subcategories'];

                foreach ($frm['categories'] as $sKey => $value) { 
                    if($value == 0) continue;
                    $check[] = $value."_".$sc[$sKey];
                }
                $uniq = array_unique($check);
                foreach($uniq as $val) {
                    list($cat, $subcat) = explode("_", $val);
                    $check_array[$cat][] = $subcat;
                }

                $categories = array();
                foreach($check_array as $k=>$val) {
                    if(count($val) > 1 && (array_search(0, $val) !== false)) {
                        $categories[] = array('category_id' => $k, 'subcategory_id' => 0);
                        unset($check_array[$k]);
                    } else {
                        foreach($val as $m=>$v) {
                            $categories[] = array('category_id' => $k, 'subcategory_id' => $v);    
                        }
                    }
                }

                $tmpPrj->setProjectField('name', change_q_x(($frm['name'])),true);
                $tmpPrj->setProjectField('descr', change_q_x($frm['descr'], FALSE, TRUE, "", false, false));
                $tmpPrj->setProjectField('pro_only', $frm['pro_only']==1 ? "t" : "f");
                $tmpPrj->setProjectField('verify_only', $frm['verify_only']==1 ? "t" : "f");
                $tmpPrj->setProjectField('strong_top', (int) $frm['strong_top']);
                $tmpPrj->setProjectField('prefer_sbr', $frm['prefer_sbr']==1 ? "t" : "f");

                $tmpPrj->setProjectField('urgent', $frm['is_urgent']==1 ? "t" : "f");
                $tmpPrj->setProjectField('hide', $frm['is_hide']==1 ? "t" : "f");

                switch($frm['kind']) {
                    case 1:
                        $tmpPrj->setProjectField('country', 0);
                        $tmpPrj->setProjectField('city', 0);
                        $tmpPrj->setProjectField('kind', $frm['kind']);
                        break;
                    case 4:
                        $tmpPrj->setProjectField('country', $frm['country']);
                        $tmpPrj->setProjectField('city', $frm['city']);
                        $tmpPrj->setProjectField('kind', $frm['kind']);
                        break;
                }
                if($project['kind']==7) {
                    $tmpPrj->setProjectField('end_date', $frm['end_date']);
                    $tmpPrj->setProjectField('win_date', $frm['win_date']);
                    $tmpPrj->clearWinners();
                }

                $tmpPrj->setCategories($categories);

                $tmpPrj->setProjectField('link', $frm['link']);

                $tmpPrj->setProjectField('is_color', $frm['is_color']==1 ? 't' : 'f');
                $tmpPrj->setProjectField('is_bold', $frm['is_bold']==1 ? 't' : 'f');
                $tmpPrj->setAddedTopDays($frm['top_ok']==1 ? $frm['top_days'] : 0);

                if(!$project['folder_id']) { $tmpPrj->setProjectField('folder_id', 0); }

                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/attachedfiles.php");
                $attachedfiles = new attachedfiles($frm['attachedfiles_session']);
                $attachedfiles_files = $attachedfiles->getFiles(array(1,3,4));
                $tmpPrj->addAttachedFiles($attachedfiles_files);
                $attachedfiles->clear();

              
                $tmpPrj->saveProject(get_uid(false), $ttt);
                require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/project_exrates.php");
                
                require_once( $_SERVER['DOCUMENT_ROOT'] . '/classes/stop_words.php' );
                $stop_words = new stop_words( hasPermissions('projects') );
                
                $objResponse->script('popupQEditPrjHide();');
                switch($type) {
                    case 1:
                    case 4:
                        // Лента проектов
                        global $session;
                        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/projects.php';
                        $prj_id = $project['id'];
                        $obj_project = new new_projects();
                        $tproject = $obj_project->getProjects($num, -1, 1, false, NULL, false, true, $prj_id);

                        $this_kind = $tproject[0]['kind'];
                        $this_uid = get_uid(false);
                        $this_pro_last = $_SESSION['pro_last'];
                        $this_is_pro   = payed::CheckPro($_SESSION['login']);
                        $this_edit_mode = hasPermissions('projects');
                        if ($this_uid) {
                            $this_user_role = $_SESSION['role'];
                        }

                        $this_project = $tproject[0];
                        $row = $this_project;
                        if ($this_edit_mode || $this_project['kind'] == 2 || $this_project['user_id'] == $this_uid || $this_project['offer_id'] || $this_pro_last) {
                            $this_show_data = 1;
                            $row['show_data'] = 1;
                        } else {
                            $this_show_data = 0;
                            $row['show_data'] = 0;
                        }

                        $descr = $row['descr'];
                        $descr = preg_replace("/^ /","\x07",$descr);
                        $descr = preg_replace("/(\n) /","$1\x07",$descr);
                        $descr = reformat(strip_tags(LenghtFormatEx($descr, 180),"<br />"), 50, 1, 0, 1);
                        $descr = preg_replace("/\x07/","&nbsp;",$descr);

                        $row['descr'] = $descr;
                        $row['t_is_payed'] = ($this_project['payed'] && $this_project['kind'] != 2 && $this_project['kind'] != 7);
                        $row['t_is_contest'] = ($this_project['kind'] == 2 || $this_project['kind'] == 7);
                        $row['t_pro_only'] = ($this_project['pro_only'] == "t" );
                        $row['t_verify_only'] = ($this_project['verify_only'] == "t" );
                        $row['t_hide'] = ($this_project['hide'] == "t" );
                        $row['t_urgent'] = ($this_project['urgent'] == "t" );
                        $row['t_prefer_sbr'] = ($this_project['prefer_sbr'] == "t" );
                        $row['priceby'] = $this_project['priceby'];
                        $row['t_is_adm'] = hasPermissions('projects');
                        $row['t_is_ontop'] = (strtotime($this_project['top_to']) >= time());
                        $row['unread'] = ((int) $this_project['unread_p_msgs'] + (int) $this_project['unread_c_msgs'] + (int) $this_project['unread_c_prjs']);
                        $row['t_is_proonly'] = ($this_project['pro_only'] == 't' && !$_SESSION['pro_last'] && !$this_edit_mode && ($this_uid != $this_project['user_id']));

                        $attaches = projects::GetAllAttach($this_project['id']);
                        $attaches = !$attaches ? array() : $attaches;

                        foreach ($attaches as $k => $a) {
                            $a['virus'] = is_null($a['virus']) ? $a['virus'] : bindec($a['virus']);
                            $attaches[$k] = $a;
                        }
                        
                        $row['attaches'] = $this_project['attaches'] = $attaches;

                        $is_ajax = true;
                        $can_change_prj = hasPermissions("projects");
                        
                        $row['friendly_url'] = getFriendlyURL('project', $row['id']);

                        ob_start();
                        if ($type == 1) {
                            $project = projects::initData($row);
                            require_once($_SERVER['DOCUMENT_ROOT'] . "/projects/tpl.lenta-item.php");
                        } else {
                            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/employer.php");
                            $user = new employer();
                            $user->GetUserByUID($this_project['user_id']);
                            require_once($_SERVER['DOCUMENT_ROOT'] . "/projects/tpl.employer-project-item.php");
                        }
                        $html_data = ob_get_contents();
                        ob_end_clean();
                        $objResponse->assign("project-item{$prj_id}", "innerHTML", $html_data);
                        if($row['is_color'] == 't') {
                            $objResponse->script("$('project-item{$prj_id}').addClass('b-post_bg_fffded')");
                        } else {
                            $objResponse->script("$('project-item{$prj_id}').removeClass('b-post_bg_fffded')");
                        }
                        //$objResponse->script('alert("Лента");');
                        //$objResponse->script('window.location.reload();');
                        break;
                    case 2:
                        // Конкурс
                        //$objResponse->script('alert("Конкурс");');
                        $project_exRates = project_exrates::GetAll();
                        $translate_exRates = array(0 => 2, 1 => 3, 2 => 4, 3 => 1);

                        global $session;
                        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/contest.php';
                        $prj_id = $project['id'];
                        $obj_project = new projects();
                        $project = $obj_project->GetPrjCust($prj_id);
                        if(hasPermissions('projects')) {
                            $project_history = $obj_project->GetPrjHistory($prj_id);
                        }
                        $project_attach = $obj_project->GetAllAttach($prj_id);
                        $contest = new contest($project['id'], $uid, is_emp(), ($project['user_id'] == $uid), hasPermissions('projects'), is_pro());
                        $contest->GetOffers((string) $_GET['filter']);
                        $project['contest_end'] = (mktime() > strtotime($project['end_date']));
                        $project['contest_win'] = (mktime() > strtotime($project['win_date']));
                        
                        if(trim($project['contacts']) != '') {
                            $contacts_employer = unserialize($project['contacts']);
                            $empty_contacts_employer = 0;
                            foreach($contacts_employer as $name => $contact) { 
                                if(trim($contact['value']) == '') $empty_contacts_employer++;
                            }
                            $is_contacts_employer_empty = ( count($contacts_employer) == $empty_contacts_employer );
                        }
                        
                        ob_start();
                        require_once($_SERVER['DOCUMENT_ROOT'] . "/projects/contest_item.php");
                        $html_data = ob_get_contents();
                        ob_end_clean();
                        $objResponse->assign("contest_info_{$prj_id}", "innerHTML", $html_data);
                        break;
                    case 3:
                        // Проект
                        $project_exRates = project_exrates::GetAll();
                        $translate_exRates = array(0 => 2, 1 => 3, 2 => 4, 3 => 1);

                        $prj_id = $project['id'];
                        $obj_project = new projects();
                        $project = $obj_project->GetPrjCust($prj_id);
                        if(trim($project['contacts']) != '') {
                            $contacts_employer = unserialize($project['contacts']);
                            $empty_contacts_employer = 0;
                            foreach($contacts_employer as $name=>$contact) { 
                                if(trim($contact['value']) == '') $empty_contacts_employer++;
                            }
                            $is_contacts_employer_empty = ( count($contacts_employer) == $empty_contacts_employer );
                        }
                        $project_attach = $obj_project->GetAllAttach($prj_id);
                        ob_start();
                        require_once($_SERVER['DOCUMENT_ROOT'] . "/projects/tpl.prj-main-info.php");
                        $html_data = ob_get_contents();
                        ob_end_clean();
                        $objResponse->assign("project_info_{$project['id']}", "innerHTML", $html_data);
                        break;
                    default:
                        $objResponse->script('window.location.reload();');
                        break;
                }
            } else {
                $tab1 = 0;
                $tab2 = 0;
                foreach($errors as $error) {
                    switch($error) {
                        case 'end_date':
                            $objResponse->script('$("popup_qedit_prj_fld_err_txt_cal1").set("html", "Неправильная дата");');
                            $objResponse->script('popupQEditPrjShowError("cal1");');
                            break;
                        case 'win_date':
                            $objResponse->script('$("popup_qedit_prj_fld_err_txt_cal2").set("html", "Неправильная дата");');
                            $objResponse->script('popupQEditPrjShowError("cal2");');
                            break;
                        case 'end_date_past':
                            $objResponse->script('$("popup_qedit_prj_fld_err_txt_cal1").set("html", "Дата окончания конкурса не может находиться  в прошлом");');
                            $objResponse->script('popupQEditPrjShowError("cal1");');
                            break;
                        case 'win_date_past':
                            $objResponse->script('$("popup_qedit_prj_fld_err_txt_cal2").set("html", "Дата определения победителя должна быть больше даты окончания конкурса");');
                            $objResponse->script('popupQEditPrjShowError("cal2");');
                            break;
                        case 'logourl':
                            $objResponse->script('$("popup_qedit_prj_fld_err_pay").setStyle("display","block");');
                            $objResponse->script('$("popup_qedit_prj_fld_err_pay_txt").set("html", "Ссылка для логотипа указана не верно");');
                            break;
                        default:
                            $objResponse->script('popupQEditPrjShowError("'.$error.'");');
                            break;
                    }
                    if(in_array($error, array('name','descr','location','end_date','win_date','end_date_past','win_date_past'))) {
                        $tab1++;
                    } elseif (in_array($error, array('logourl'))) {
                        $tab2++;
                    }
                }
                if($tab1) {
                    $objResponse->script("popupQEditPrjMenu(1)");
                } elseif ($tab2) {
                    $objResponse->script("popupQEditPrjMenu(2)");
                }
            }
        }
        $objResponse->script("popupQEditIsProcess = false;");
    }
    return $objResponse;
}

function quickprjedit_get_prj($prj_id) {
    $objResponse = new xajaxResponse();
    if(hasPermissions('projects')) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/country.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/city.php");
        $categories = professions::GetAllGroupsLite();
        $professions = professions::GetAllProfessions();
        array_group($professions, 'groupid');
        $professions[0] = array();
        $oprj = new new_projects();
        $project = $oprj->getPrj($prj_id);
        if($project['id']) {
            $project_categories = $oprj->getSpecs($project['id']);
            if ( empty($project_categories) ) {
                $project_categories[] = array('category_id'=>0, 'subcategory_id'=>0);
            }
            $html_categories = '';
            foreach($project_categories as $project_category) {
                $html_categories .= "<div id='category_line'>
                                        <select name='categories[]' class='b-select__select b-select__select_width_180' onchange='popupQEditPrjRefreshSubCategory(this);'>
                                            <option value='0'>Выберите раздел</option>
                                     ";
				foreach($categories as $cat) {
                    if($cat['id']<=0) continue;
                    $html_categories .= "<option value='{$cat['id']}' ".($project_category['category_id']==$cat['id'] ? ' selected' : '').">{$cat['name']}</option>";
                }

                $html_categories .= "</select>
                                        <select name='subcategories[]' class='b-select__select b-select__select_width_180'>
                                    ";
			    $categories_specs = $professions[$project_category['category_id']];
                for ($i=0; $i<sizeof($categories_specs); $i++) {
                    $html_categories .= "<option value='{$categories_specs[$i]['id']}'".($categories_specs[$i]['id'] == $project_category['subcategory_id'] ? ' selected' : '').">{$categories_specs[$i]['profname']}</option>";
                }

                $html_categories .= "      <option value='0' ".($project_category['subcategory_id']==0 ? ' selected' : '').">Все специализации</option>
                                        </select>
                                     </div>
                                     ";
            }
            $countries = country::GetCountries();
            if($project['country']) {
                $cities = city::GetCities($project['country']);
            }

            $html_location = '';
            $html_location .= "<div><select id='popup_qedit_prj_fld_country' name='country' onChange='popupQEditPrjCityUpd(this.value);'><option value='0'>Страна</option>";
            foreach($countries as $country_id=>$country) {
                $html_location .= "<option value='{$country_id}' ".($country_id==$project['country'] ? 'selected' : '').">{$country}</option>";
            }
            $html_location .= "</select></div>";
            $html_location .= "<div id='frm_city'><select name='city'><option value='0'>Город</option>";
            if($cities) {
                foreach($cities as $city_id=>$city) {
                    $html_location .= "<option value='{$city_id}' ".($city_id==$project['city'] ? 'selected' : '').">{$city}</option>";
                }
            }
            $html_location .= "</select>";
            $objResponse->assign('popup_qedit_prj_fld_id', 'value', $project['id']);
            $objResponse->assign('popup_qedit_prj_fld_name', 'value', htmlspecialchars_decode($project['name'],ENT_QUOTES));
            $objResponse->assign('popup_qedit_prj_fld_descr', 'value', htmlspecialchars_decode($project['descr'],ENT_QUOTES));
            $objResponse->assign('popup_qedit_prj_fld_categories', 'innerHTML', $html_categories);
            $objResponse->assign('popup_qedit_prj_fld_location', 'innerHTML', $html_location);
            $objResponse->script('$("popup_qedit_prj_fld_kind_1").set("checked", false);');
            switch($project['kind']) {
                case 1:
                    $objResponse->script('$("popup_qedit_prj_cal1").setStyle("display", "none");');
                    $objResponse->script('$("popup_qedit_prj_cal2").setStyle("display", "none");');
                    $objResponse->script('$("popup_qedit_prj_kind").setStyle("display", "block");');
                    $objResponse->script('$("popup_qedit_prj_fld_kind_1").set("checked", true);');
                    $objResponse->script('$("popup_qedit_prj_fld_location").setStyle("display", "none");');
                    break;
                case 2:
                    //$objResponse->script('$("sbr_text_block").setStyle("display", "none");');
                    break;
                case 4:
                    $objResponse->script('$("popup_qedit_prj_cal1").setStyle("display", "none");');
                    $objResponse->script('$("popup_qedit_prj_cal2").setStyle("display", "none");');
                    $objResponse->script('$("popup_qedit_prj_kind").setStyle("display", "block");');
                    $objResponse->script('$("popup_qedit_prj_fld_kind_2").set("checked", true);');
                    $objResponse->script('$("popup_qedit_prj_fld_location").setStyle("display", "block");');
                    break;
                case 7:
                    $objResponse->script('$("popup_qedit_prj_cal1").setStyle("display", "block");');
                    $objResponse->script('$("popup_qedit_prj_cal2").setStyle("display", "block");');
                    $objResponse->script('$("popup_qedit_prj_kind").setStyle("display", "none");');
                    $objResponse->script('$("popup_qedit_prj_fld_location").setStyle("display", "none");');
                    $objResponse->script('$("popup_qedit_prj_fld_end_date").set("value", "'.date('d-m-Y',strtotime($project['end_date'])).'");');
                    $objResponse->script('$("popup_qedit_prj_fld_win_date").set("value", "'.date('d-m-Y',strtotime($project['win_date'])).'");');
                    break;
            }
            if($project['pro_only']=='t') {
                $objResponse->script('$("popup_qedit_prj_fld_pro_only").set("checked", true);');
            } else {
                $objResponse->script('$("popup_qedit_prj_fld_pro_only").set("checked", false);');
            }
            if($project['verify_only']=='t') {
                $objResponse->script('$("popup_qedit_prj_fld_verify_only").set("checked", true);');
            } else {
                $objResponse->script('$("popup_qedit_prj_fld_verify_only").set("checked", false);');
            }
            if($project['prefer_sbr']=='t') {
                $objResponse->script('$("popup_qedit_prj_fld_prefer_sbr").set("checked", true);');
            } else {
                $objResponse->script('$("popup_qedit_prj_fld_prefer_sbr").set("checked", false);');
            }
            if($project['strong_top']==1) {
                $objResponse->script('$("popup_qedit_prj_fld_strong_top").set("checked", true);');
            } else {
                $objResponse->script('$("popup_qedit_prj_fld_strong_top").set("checked", false);');
            }
            /*if($project['prefer_sbr']=='t') {
                $objResponse->script('$("popup_qedit_prj_fld_prefer_sbr").set("checked", true);');
            } else {
                $objResponse->script('$("popup_qedit_prj_fld_prefer_sbr").set("checked", false);');
            }*/
            $objResponse->script("var mx = new MultiInput('popup_qedit_prj_fld_categories','category_line', " . (int)($project['is_pro'] === 't') . "); mx.init();");

            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/attachedfiles.php");
            $attchedfiles = new attachedfiles($attachedfiles_session);
            $attachedfiles_tmpprj_files = $oprj->GetAllAttach($project['id']);
            if($attachedfiles_tmpprj_files) {
                $attachedfiles_prj_files = array();
                foreach($attachedfiles_tmpprj_files as $attachedfiles_prj_file) {
                    $attachedfiles_prj_files[] = $attachedfiles_prj_file['file_id'];
                }
                $attchedfiles->setFiles($attachedfiles_prj_files);
            }
            $attachedfiles_files = $attchedfiles->getFiles();
            $js_attachedfiles = 'attachedfiles_list = [];';
            if($attachedfiles_files) {
                $n = 0;
                foreach($attachedfiles_files as $attachedfiles_file) {
                    $js_attachedfiles .= "attachedfiles_list[{$n}] = new Object;\n";
                    $js_attachedfiles .= "attachedfiles_list[{$n}].id = '".md5($attachedfiles_file['id'])."';\n";
                    $js_attachedfiles .= "attachedfiles_list[{$n}].name = '{$attachedfiles_file['orig_name']}';\n";
                    $js_attachedfiles .= "attachedfiles_list[{$n}].path = '".WDCPREFIX."/{$attachedfiles_file['path']}{$attachedfiles_file['name']}';\n";
                    $js_attachedfiles .= "attachedfiles_list[{$n}].size = '".ConvertBtoMB($attachedfiles_file['size'])."';\n";
                    $js_attachedfiles .= "attachedfiles_list[{$n}].type = '{$attachedfiles_file['type']}';\n";
                    $n++;
                }
            }
            $objResponse->script($js_attachedfiles);
            $objResponse->script("attachedFiles.init('popup_qedit_prj_attachedfiles', '".$attchedfiles->getSession()."', attachedfiles_list, ".tmp_project::MAX_FILE_COUNT.", ".tmp_project::MAX_FILE_SIZE.",'".implode(', ', $GLOBALS['disallowed_array'])."', 'project', ".get_uid(false).");");


            if($project['is_color']=='t') {
                $objResponse->script("$('popup_qedit_prj_is_color').set('checked', true); popupQEditPrjToggleIsColor();");
            } else {
                $objResponse->script("$('popup_qedit_prj_is_color').set('checked', false); popupQEditPrjToggleIsColor();");
            }
            if($project['is_bold']=='t') {
                $objResponse->script("$('popup_qedit_prj_is_bold').set('checked', true); popupQEditPrjToggleIsBold();");
            } else {
                $objResponse->script("$('popup_qedit_prj_is_bold').set('checked', false); popupQEditPrjToggleIsBold();");
            }
            if($project['hide']=='t') {
                $objResponse->script("$('popup_qedit_prj_is_hide').set('checked', true); ");
            } else {
                $objResponse->script("$('popup_qedit_prj_is_hide').set('checked', false); ");
            }
            if($project['urgent']=='t') {
                $objResponse->script("$('popup_qedit_prj_is_urgent').set('checked', true); ");
            } else {
                $objResponse->script("$('popup_qedit_prj_is_urgent').set('checked', false); ");
            }

            $objResponse->assign('popup_qedit_prj_logolink', 'value', $project['link']);

            $key = md5(uniqid($uid));
            $tmpPrj = new tmp_project($key);
            $tmpPrj->init(1, $project['id']);
            $remTPeriod = $tmpPrj->getRemainingTopPeriod($remTD, $remTH, $remTM, $remtverb);
            $addedTD = $tmpPrj->getAddedTopDays();
            $objResponse->assign('popup_qedit_prj_fld_tmpid','value',$key);
            if($remTPeriod||$addedTD) {
                $objResponse->script('$("popup_qedit_prj_top_ok").set("checked", true);');
                $objResponse->script('$("popup_qedit_prj_top_ok").set("disabled", true);');
                $objResponse->script('$("popup_qedit_prj_top_ok_tab1_days").set("value", "1");');
                $objResponse->script('$("popup_qedit_prj_top_ok_icon").setStyle("display", "block");');
                $objResponse->script('$("popup_qedit_prj_top_ok_tab1").setStyle("display", "none");');
                $objResponse->script('$("popup_qedit_prj_top_ok_tab2").setStyle("display", "block");');
                $objResponse->script('$("popup_qedit_prj_top_ok_tab1_days").set("disabled", true);');
                $objResponse->script('$("popup_qedit_prj_top_ok_tab2_c").set("checked", false);');
                $objResponse->script('$("popup_qedit_prj_top_ok_tab2_c").set("disabled", false);');
                $objResponse->script('$("popup_qedit_prj_top_ok_tab2_days").set("disabled", false);');
                $objResponse->script('$("popup_qedit_prj_top_ok_tab2_days").set("value", "1");');
                $objResponse->script('$("popup_qedit_prj_top_ok_tab2_left").set("html", "'.$remtverb.' '.$remTPeriod.'");');
            } else {
                $objResponse->script('$("popup_qedit_prj_top_ok").set("checked", false);');
                $objResponse->script('$("popup_qedit_prj_top_ok").set("disabled", false);');
                $objResponse->script('$("popup_qedit_prj_top_ok_tab1_days").set("value", "1");');
                $objResponse->script('$("popup_qedit_prj_top_ok_icon").setStyle("display", "none");');
                $objResponse->script('$("popup_qedit_prj_top_ok_tab1").setStyle("display", "none");');
                $objResponse->script('$("popup_qedit_prj_top_ok_tab2").setStyle("display", "none");');
                $objResponse->script('$("popup_qedit_prj_top_ok_tab1_days").set("disabled", false);');
                $objResponse->script('$("popup_qedit_prj_top_ok_tab2_c").set("checked", false);');
                $objResponse->script('$("popup_qedit_prj_top_ok_tab2_c").set("disabled", true);');
                $objResponse->script('$("popup_qedit_prj_top_ok_tab2_days").set("disabled", true);');
            }

            if($project['logo_id']) {
                $logo = $tmpPrj->getLogo();
                $objResponse->script('$("popup_qedit_prj_use_logo_src").set("href", "'.WDCPREFIX.'/'.$logo['path'].$logo['name'].'");');
                $objResponse->script('$("popup_qedit_prj_use_logo").set("checked", true);');
                $objResponse->script('$("popup_qedit_prj_use_logo").set("disabled", true);');
                $objResponse->script('$("popup_qedit_prj_use_logo_tab").setStyle("display", "block");');
                $objResponse->script('$("popup_qedit_prj_use_logo_tab2").setStyle("display", "none");');
            } else {
                $objResponse->script('$("popup_qedit_prj_use_logo").set("checked", false);');
                $objResponse->script('$("popup_qedit_prj_use_logo").set("disabled", false);');
                $objResponse->script('$("popup_qedit_prj_use_logo_tab").setStyle("display", "none");');
                $objResponse->script('$("popup_qedit_prj_use_logo_tab2").setStyle("display", "none");');
            }

            $tmpPrj->fix();
            
            $objResponse->call("center_popup", ".b-shadow_center-quick");
        }
    }
    return $objResponse;
}

function quickprjedit_save_budget($prj_id, $frm, $type, $page_type) {
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
    $objResponse = new xajaxResponse();
    $budget = NULL;
    if(hasPermissions('projects')) {
        if($frm['agreement']==1 || $frm['cost']==0) {
            $budget = projects::updateBudget($prj_id, 0, 0, 0, true);
            $str_budget_cost = "По договоренности";
            $agreement = true;
            $str_budget_lnk = "popupShowChangeBudget({$prj_id}, '', 0, 1, true, {$prj_id}, ".($type==1 ? 1 : 2).", ".($page_type?$page_type:0)."); return false;";
            $budget_block = '<table cellspacing="0" cellpadding="0">
                                <tr>
                                    <td>
                                        <div class="form">
                                        <b class="b1"></b>
                                        <b class="b2"></b>
                                            <div class="form-in">
                                            <a href="#" id="prj_budget_lnk_'.$prj_id.'" onClick="popupShowChangeBudget('.$prj_id.', \'\', 0, 1, true, '.$prj_id.', '.($type==1 ? 1 : 2).', '.($page_type?$page_type:0).'); return false;">Бюджет по договоренности</a>
                                            </div>
                                        <b class="b2"></b>
                                        <b class="b1"></b>
                                        </div>
                                    </td>
                                </tr>
                             </table>';
            $budget_block_class = "prj_cost prj-dogovor";
        } else {
            if($frm['cost']>0) {
                $budget = projects::updateBudget($prj_id, $frm['cost'], $frm['currency'], $frm['costby'], false);
                $str_budget_cost = CurToChar($budget['cost'], $budget['currency']);
                $agreement = false;
                $str_budget_lnk = "popupShowChangeBudget({$prj_id}, '{$budget['cost']}', '{$budget['currency']}', '{$budget['costby']}', false, {$prj_id}, ".($type==1 ? 1 : 2).', '.($page_type?$page_type:0)."); return false;";
                $budget_block_class = "prj_cost";
                $budget_block = '<div class="budget-block">';
                switch ($budget['budget_type']) {                    
                    default:
                        $budget_price_str = '';
                        $budget_price_class = 'fl-form-grey';
                        break;
                }
                if ($budget['cost'] == '' || $budget['cost'] == 0) {
                    $budget_price_str = '';
                    $budget_price_class = 'fl-form-grey';
                }
                if ($budget_price_str != '') {
                    $budget_block .= '
                         <div class="fl-form fl-form-tr budget-type">
                             '.$budget_price_str.'
                             <span class="cc cc-lt"></span>
                             <span class="cc cc-rt"></span>
                             <span class="cc cc-lb"></span>
                             <span class="cc cc-rb"></span>
                             <span class="budget-type-lug"></span>
                         </div>';
                }
                $budget_block .= '<div class="fl-form '.$budget_price_class.' color-budget">';
                switch ($budget['costby']) {
                    case '1':
                       $priceby_str = "/час";
                       break;
                    case '2':
                       $priceby_str = "/день";
                       break;
                    case '3':
                       $priceby_str = "/месяц";
                       break;
                    case '4':
                       $priceby_str = "/проект";
                       break;
                    default:
                       $priceby_str = "";
                       break;
               }
               if ($budget['cost'] == '' || $budget['cost'] == 0) {
                   $priceby_str = "";
               }
                $budget_block .= '<strong>
                                <a href="#" id="prj_budget_lnk_'.$prj_id.'">Бюджет: '.CurToChar($budget['cost'], $budget['currency']) . $priceby_str.'</a>
                             </strong>';
                if ($budget['cost'] > 0) {
                    $project_exRates = project_exrates::GetAll();
                    $exch = array(1=>'FM', 'USD','Euro','Руб');
                    $translate_exRates = array
                    (
                    0 => 2,
                    1 => 3,
                    2 => 4,
                    3 => 1
                    );
                   $price_other_cur = '';
                   if ($budget['currency'] != 0) {
                       $price_other_cur .= CurToChar(preg_replace('/.00$/', '', sprintf("%.2f", round($budget['cost'] * $project_exRates[trim($translate_exRates[$budget['currency']]) . '2'], 2))), 0) . "AA";
                   }
                   if ($budget['currency'] != 1) {
                       $price_other_cur .= CurToChar(preg_replace('/.00$/', '', sprintf("%.2f", round($budget['cost'] * $project_exRates[trim($translate_exRates[$budget['currency']]) . '3'], 2))), 1) . "AA";
                   }
                   if ($budget['currency'] != 2) {
                       $price_other_cur .= CurToChar(preg_replace('/.00$/', '', sprintf("%.2f", round($budget['cost'] * $project_exRates[trim($translate_exRates[$budget['currency']]) . '4'], 2))), 2) . "AA";
                   }
                   if ($budget['currency'] != 3) {
                       $price_other_cur .= CurToChar(preg_replace('/.00$/', '', sprintf("%.2f", round($budget['cost'] * $project_exRates[trim($translate_exRates[$budget['currency']]) . '1'], 2))), 3) . "AA";
                   }
                   $price_other_cur = preg_replace("/AA$/", "", $price_other_cur);
                   $price_other_cur = preg_replace("/AA/", "&nbsp;—&nbsp;", $price_other_cur);
                   $budget_block .= '<em>'.$price_other_cur.'</em>';
                }
                $budget_block .= '
                             <span class="cc cc-lt"></span>
                             <span class="cc cc-rt"></span>
                             <span class="cc cc-lb"></span>
                             <span class="cc cc-rb"></span>
                         </div>
                     </div>';
            }
        }
        switch ($budget['costby']) {
            case '1':
                $costby_str = "/час";
                break;
            case '2':
                $costby_str = "/день";
                break;
            case '3':
                $costby_str = "/месяц";
                break;
            case '4':
                $costby_str = "/проект";
                break;
            default:
                $costby_str = "";
                break;
        }
        $str_budget_cost = $str_budget_cost.$costby_str;
    }
    if($budget) {
        switch($type) {
            case 1:
                // Лента
                $objResponse->assign("prj_budget_lnk_{$prj_id}", "innerHTML", $str_budget_cost);
                if ($agreement) {
                    $objResponse->script('$("prj_budget_lnk_'.$prj_id.'").getParent().removeClass("b-post__price_bold").removeClass("b-post__price_fontsize_15").addClass("bujet-dogovor");');
                } else {
                    if ($page_type != 2) {
                        $objResponse->script('$("prj_budget_lnk_'.$prj_id.'").getParent().addClass("b-post__price_bold").removeClass("b-post__price_fontsize_13").addClass("b-post__price_fontsize_15");');
                    } else {
                        $objResponse->script('$("prj_budget_lnk_'.$prj_id.'").getParent().removeClass("b-post__price_bold").removeClass("b-post__price_fontsize_13").removeClass("bujet-dogovor");');
                    }
                }
                $objResponse->script("$('prj_budget_lnk_{$prj_id}').addEvent('click', function() { {$str_budget_lnk} });");
                break;
            case 2:
                // Проект
                $objResponse->assign("budget_block", "innerHTML", $budget_block);
                $objResponse->script("$('budget_block').set('class', '{$budget_block_class}');");
                $objResponse->script("$('prj_budget_lnk_{$prj_id}').addEvent('click', function() { {$str_budget_lnk} });");
                break;
        }
    }
    return $objResponse;
}


$xajax->processRequest();
?>
