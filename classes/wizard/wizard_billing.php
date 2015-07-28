<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/wizard/wizard.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/wizard/step_wizard.php';

/**
 * Класс для работы с платными операциями заказанными в мастере
 *  
 */
class wizard_billing
{
    function __construct($uid = false) {
        if(!$uid) $uid = $_SESSION['uid'];
        $this->uid = intval($uid);
    }
    
    /**
     * Обработка оплаты операций
     * 
     * @param type $operations  Операции к оплате
     * @return boolean 
     */
    function paymentOptions($operations) {
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/wizard/step_freelancer.php';
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/wizard/step_employer.php';
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_offers_answers.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/payed.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/drafts.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/log.php";
        $this->log  = new log('wizard/payed-'.SERVER.'-%d.log', 'a', '%d.%m.%Y %H:%M:%S : ');
        $payed_operation = $this->getDraftAccountOperationsByIds($operations, $this->uid);
        $account = new account;
        if($payed_operation) {
            // выбран ли или уже куплен аккаунт ПРО
            $is_pro = is_pro();
            if (!$is_pro) {
                foreach($payed_operation as $option) {
                    if ($option['op_code'] == 15) {
                        $is_pro = true;
                        break;
                    }
                }
            }
            // перебираем все позиции и переделываем с учетом ПРО
            if ($is_pro) {
                foreach($payed_operation as &$option) {
                    switch($option['op_code']) {
                        case new_projects::OPCODE_KON_NOPRO:
                            $option['op_code'] = new_projects::OPCODE_KON;
                            break;
                        case new_projects::OPCODE_PAYED:
                            $option['ammount'] = $option['ammount'] - new_projects::PRICE_ADDED;
                            break;
                        default:
                            break;
                    }
                }
            }
            unset($option);
            
            $transaction_id = $account->start_transaction($this->uid);
            foreach($payed_operation as $option) {
                $ok[$option['id']] = $this->billingOperation($option, $transaction_id);
                if($ok[$option['id']]) {
                    $delete = $this->deleteDraftAccountOperation($option['id']);
                    if(!$delete) {
                        $this->log->writeln("Error delete draft account operation - user (" . wizard::getUserIDReg() . ") - option #{$option['id']}");
                    }
                } else {
                    $this->log->writeln("Error billing operation - user (" . wizard::getUserIDReg() . ") - option #{$option['id']}");
                }
            }
            $account->commit_transaction($transaction_id, $this->uid, null);
            return true;
        }
        
        return false;
    }
    
    /**
     * Обработка и оплата операций
     * 
     * @global type $DB
     * @param type $option
     * @return boolean 
     */
    function billingOperation($option, $transaction_id) {
        global $DB;
        $ok = false;
        $account = new account();
        switch($option['op_code']) {
            // Аккаунт ПРО у фрилансера
            case 48:
            case 49:
            case 50:
            case 51:
            case 76:
                // Удаляем операции по покупке ответов - публикуем ответы
                $prof = new payed();
                $ok   = $prof->SetOrderedTarif($this->uid, $transaction_id, 1, "Аккаунт PRO", $option['op_code'], $error);
                if($ok) {
                    $_SESSION['pro_last'] = payed::ProLast($_SESSION['login']);
                    $_SESSION['pro_last'] = $_SESSION['pro_last']['freeze_to'] ? false : $_SESSION['pro_last']['cnt'];
                    $userdata = new users();
                    $_SESSION['pro_test'] = $userdata->GetField($this->uid, $error2, 'is_pro_test', false);
                    
                    $this->clearBlockedOperations(step_freelancer::OFFERS_OP_CODE);
                    $step_frl = new step_freelancer();
                    $offers   = $step_frl->getWizardOffers($this->uid, 'all', false);
                    if($offers) {
                        require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
                        $step_frl->log  = $this->log;
                        $step_frl->user = new users();
                        $step_frl->user->GetUserByUID($this->uid);
                        $step_frl->transferOffers($offers);
                    }
                    $this->showProjectsFeedbacks();
                }
                break; 
            // Аккаунт ПРО у работодателя
            case 15:
                $prof = new payed();
                $ok   = $prof->SetOrderedTarif($this->uid, $transaction_id, 1, "Аккаунт PRO", $option['op_code'], $error);
                if($ok) {
                    $_SESSION['pro_last'] = payed::ProLast($_SESSION['login']);
                    $_SESSION['pro_last'] = $_SESSION['pro_last']['freeze_to'] ? false : $_SESSION['pro_last']['cnt'];
                    $userdata = new users();
                    $_SESSION['pro_test'] = $userdata->GetField($this->uid, $error2, 'is_pro_test', false);
                }
                // Обновляем выбор цвета для проектов тк он для ПРО бесплатный
                $colorProjects = $this->updateColorProject();
                $prj = new new_projects();
                foreach($colorProjects as $k=>$project) {
                    $delete_color[] = $project['op_id'];
                    if($project['country'] == null) $project['country'] = 'null';
                    if($project['city'] == null) $project['city'] = 'null';
                    $project['name'] = addslashes($project['name']);
                    $project['descr'] = addslashes($project['descr']);
                    if($project['logo_id'] <= 0) $project['logo_id'] = 'null';
                    $project['payed_items'] = $project['payed_items'] | '010';
                    $project['is_color']    = 't';
                    $prj->editPrj($project, false);
                }
                // Удаляем данные операции
                if($delete_color) {
                    $this->deleteDraftAccountOperation($delete_color);
                }
                break;
            // Публикация конкурса
            case new_projects::OPCODE_KON:
            case new_projects::OPCODE_KON_NOPRO:
                require_once $_SERVER['DOCUMENT_ROOT'].'/classes/wizard/step_wizard_registration.php';
                
                $drafts  = new drafts();
                $draft   = $drafts->getDraft($option['parent_id'], $this->uid, 1);
                // Если еще не опубликован
                if(!$draft['prj_id']) {
                    $project_id = $draft['id'];
                    $error = $account->Buy($bill_id, $transaction_id, $option['op_code'], $this->uid, $option['descr'], $option['comment'], 1, 0);
                    $ok = ($bill_id > 0);
                    if($bill_id) {
                        $color = $DB->val("SELECT id FROM draft_account_operations WHERE parent_id = ? AND op_type = 'contest' AND option = 'color' AND uid = ?", $project_id, wizard::getUserIDReg());
                        $draft['billing_id'] = $bill_id;
                        $draft['folder_id']  = 'null';
                        $draft['payed']      = '0';
                        $draft['payed_items']= '000';
                        if(is_pro() && $color > 0) {
                            $draft['is_color']   = 't';
                        } else {
                            $draft['is_color']   = 'f';
                        }
                        $draft['win_date']   = date('d-m-Y', strtotime($draft['win_date']));
                        $draft['end_date']   = date('d-m-Y', strtotime($draft['end_date']));
                        $draft['is_bold']    = 'f';
                        $draft['user_id']    = $this->uid;
                        if($draft['country'] == null) $draft['country'] = 'null';
                        if($draft['city'] == null) $draft['city'] = 'null';
                        $draft['name'] = addslashes($draft['name']);
                        $draft['descr'] = addslashes($draft['descr']);
                        if($draft['logo_id'] <= 0) $draft['logo_id'] = 'null';
                        $prj = new new_projects();
                        $attachedfiles_tmpdraft_files = drafts::getAttachedFiles($option['parent_id'], 4);
                        
                        if ($attachedfiles_tmpdraft_files) {
                            $attachedfiles_tmpdraft_files = array_map(create_function('$a', 'return array("id" => $a);'), $attachedfiles_tmpdraft_files);
                        }
                        if($attachedfiles_tmpdraft_files) {
                            $month = date('Ym');
                            $dir = 'projects/upload/' . $month . '/';
                            $files = step_wizard_registration::transferFiles($attachedfiles_tmpdraft_files, 'file_projects', $dir);
                        }
                        $spec = $draft["categories"];
                        $spec = explode("|", $spec);
                        $spec = array(array('category_id'=>$spec[0], 'subcategory_id'=>$spec[1]));
                        
                        $prj->addPrj($draft, $files);                        
                        $prj->saveSpecs($draft["id"], $spec);
                        // смотрим были ли выбраны платные опции для опубликованного конкурса
                        if($draft['id'] != $project_id && $draft['id'] > 0) {
                            if($this->sleep[$project_id]) {
                                foreach($this->sleep[$project_id] as $k=>$opt) {
                                    $opt['parent_id'] = $draft['id'];
                                    $this->billingOperation($opt);
                                }
                            } else {
                                //Обновляем родителя на всякий случай
                                $update = array("parent_id" => $draft['id']);
                                $DB->update("draft_account_operations", $update, "parent_id = ? AND op_type = 'contest' AND uid = ?", $project_id, wizard::getUserIDReg()); 
                                $this->sleep_parent[$project_id] = $draft['id'];
                            }
                            $DB->update("draft_projects", array('prj_id' => $draft['id']), "id = ? AND uid = ?", $project_id, wizard::getUserIDReg());
                            
                        }
                    }
                }
                break;
            // Платный проект/конкурс
            case 53:
                $prj = new new_projects();
                if($this->sleep_parent[$option['parent_id']]) {
                    $option['parent_id'] = $this->sleep_parent[$option['parent_id']];
                }
                $project = $prj->getProject($option['parent_id']);
                if(!$project['id']) {
                    $this->sleep[$option['parent_id']][$option['id']] = $option;
                    return true;
                } else {
                    unset($this->sleep[$option['parent_id']]);
                }
                if($project['country'] == null) $project['country'] = 'null';
                if($project['city'] == null) $project['city'] = 'null';
                $project['name'] = addslashes($project['name']);
                $project['descr'] = addslashes($project['descr']);
                if($project['logo_id'] <= 0) $project['logo_id'] = 'null';
                $project['folder_id']  = 'null';
                
                $items = array();
                switch($option['option']) {
                    case 'top':
                        $project['top_days'] = $option['op_count'];
                        break;
                    case 'color':
                        $is_pay  = ($project['payed_items'] & '010');
                        if($is_pay != '010') { 
                            $project['payed_items'] = $project['payed_items'] | '010';
                            $project['is_color']    = 't';
                            $items['color']         = true;
                            if(is_pro()) {
                                $is_payed = true;
                                $prj->SavePayedInfo($items, $project['id'], null, $project['top_days']);
                                $prj->editPrj($project, false);
                            }
                        } else {
                            $is_payed = true;
                        }
                        break;
                    case 'bold':
                        $is_pay  = ($project['payed_items'] & '001');
                        if($is_pay != '001') { 
                            $project['payed_items'] = $project['payed_items'] | '001';
                            $project['is_bold']     = 't';
                            $items['bold']          = true;
                        } else {
                            $is_payed = true;
                        }
                        break;
                    case 'logo':
                        $is_pay  = ($project['payed_items'] & '100');
                        if($is_pay != '100') {
                            $key = md5(microtime());
                            $prj = new tmp_project($key);
                            $prj->init(1);
                            $fu = new CFile($option['src_id']);
                            $ext = $fu->getext();
                            $tmp_dir  = $prj->getDstAbsDir();
                            $tmp_name = $fu->secure_tmpname($tmp_dir, '.'.$ext);
                            $tmp_name = substr_replace($tmp_name,"",0,strlen($tmp_dir));
                            $fu->table = 'file_projects';
                            $r = $fu->_remoteCopy($tmp_dir.$tmp_name);
                            $project['payed_items'] = $project['payed_items'] | '100';
                            $project['logo_id']     = $fu->id;
                            $items['logo']          = true;
                            if ( $option['extra'] ) {
                                $project['link'] = $option['extra'];
                            }
                        } else {
                            $is_payed = true;
                        }
                        break;
                }
                
                if(!$is_payed) {
                    $error = $account->Buy($bill_id, $transaction_id, $option['op_code'], $this->uid, $option['descr'], $option['comment'], $option['ammount'], 0);
                    $ok = ($bill_id > 0);
                    $project['billing_id'] = $bill_id;

                    $prj->SavePayedInfo($items, $project['id'], $bill_id, $project['top_days']);
                    $prj->editPrj($project, false);
                } else {
                    $ok = true;
                }
                
                break;
            // Платные ответы на проекты
            case 61:
                $answers = new projects_offers_answers();
                $error = $answers->BuyByFM($this->uid, $option['op_count'], $transaction_id, 0);
                if (!$error) {
                    $ok = true;
                    $_SESSION['answers_ammount'] = $option['op_count'];
                    // Публикуем ответы
                    $step_frl = new step_freelancer();
                    $offers   = $step_frl->getWizardOffers($this->uid, $option['op_count']);
                    if($offers) {
                        require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
                        $step_frl->log  = $this->log;
                        $step_frl->user = new users();
                        $step_frl->user->GetUserByUID($this->uid);
                        $step_frl->transferOffers($offers);
                    }
                }
                break;
        }
        
        return $ok;
    }
    
    /**
     * Обновляем проекты если были выбраны опции выделения цветом и куплен ПРО аккаунт
     * 
     * @global type $DB
     * @return type 
     */
    function updateColorProject() {
        global $DB;
        $sql = "SELECT p.*, dao.id as op_id 
                FROM draft_account_operations dao
                INNER JOIN projects p ON p.id = dao.parent_id AND p.user_id = dao.uid
                WHERE dao.op_code = 53 AND dao.uid = ?i AND dao.option = 'color'";
        return $DB->rows($sql, $this->uid);
    }
    
    /**
     * Удаляем операции которые блокируются при покупке ПРО
     * 
     * @global type $DB
     * @param type $op_code Код операции
     * @return type 
     */
    function clearBlockedOperations($op_code, $option = false) {
        global $DB;
        if($option) $option = "option = '{$option}'";
        return $DB->query("DELETE FROM draft_account_operations WHERE op_code = ?i AND uid = ?i " . ( $option? " AND {$option}" : "" ) , $op_code, $this->uid);
    }
    
    /**
     * Удаляем оплаченные операции
     * 
     * @global type $DB
     * @param type $id
     * @return type 
     */
    function deleteDraftAccountOperation($id) {
        global $DB;
        if(is_array($id)) {
            $where = "id IN (?l)";
        } else {
            $where = "id = ?i";
        }
        return $DB->query("DELETE FROM draft_account_operations WHERE {$where} AND uid = ?i", $id, $this->uid);
    }
    
    /**
     * Возвращает операции по Ид пользователя
     * 
     * @global type $DB
     * @param type $uid Ид пользователя
     * @return boolean 
     */
    public function getDraftAccountOperations($uid = false) {
        global $DB;
        if(!$uid) $uid = $_SESSION['uid'];
        if(!$uid) return false;
        
       return $DB->rows("SELECT dao.*, p.name as project_name, dp.name as contest_name
                          FROM draft_account_operations dao
                          LEFT JOIN projects p ON p.id = dao.parent_id AND dao.op_type = 'project' AND p.user_id = dao.uid 
                          LEFT JOIN draft_projects dp ON dp.id = dao.parent_id AND dao.op_type = 'contest' AND dp.uid = dao.uid
                          WHERE dao.uid = ?", $uid);
    }
    
    /**
     * Возвращает операции по Ид операции
     * 
     * @global type $DB
     * @param array|integer $operations  Ид операций, может быть массив ИД
     * @param integer $uid
     * @return array 
     */
    public function getDraftAccountOperationsByIds($operations, $uid = false) {
        global $DB;
        if(!$uid) $uid = $_SESSION['uid'];
        if(!$uid) return false;
        
        $sql = "SELECT dao.*, p.name as project_name, dp.name as contest_name
                FROM draft_account_operations dao
                LEFT JOIN projects p ON p.id = dao.parent_id AND dao.op_type = 'project' AND p.user_id = dao.uid
                LEFT JOIN draft_projects dp ON dp.id = dao.parent_id AND dao.op_type = 'contest' AND dp.uid = dao.uid
                WHERE dao.uid = ?";
        
        if(is_array($operations)) {
            $sql .= " AND dao.id IN (?l)";
            
        } else {
            $sql .= " AND dao.id = ?";
        }
        
        return $DB->rows($sql, $uid, $operations);
    }
    
    /**
     * Добавляем платную операцию
     * 
     * @global type $DB
     * @param array $insert  Данные для записи
     * @return integer ID опции 
     */
    public function addPaidOption($insert) {
        global $DB;
        
        return $DB->insert("wizard_billing", $insert, "id");
    }
    
    /**
     * Редактируем платную опцию
     * 
     * @global type $DB
     * @param array   $update  Данные для редактирования
     * @param integer $id      ИД операции
     * @return boolean
     */
    public function editPaidOption($update, $id) {
        global $DB;
        
        return $DB->update("wizard_billing", $update, "id = ?i", $id);
    }
    
    /**
     * Берем платные операции пользователя по Ид операции
     * 
     * @global type $DB
     * @param integer|array $id  Ид операции или массив ИД операций
     * @return array 
     */
    public function getPaidOptionById($id) {
        global $DB;
        
        if(is_array($id)) {
            return $DB->rows("SELECT * FROM wizard_billing WHERE id IN (?l) AND wiz_uid = ?", $id, step_wizard::getWizardUserID());
        } else {
            return $DB->rows("SELECT * FROM wizard_billing WHERE id = ? AND wiz_uid = ?", $id, step_wizard::getWizardUserID());
        }
    }
    
    /**
     * Удаляем платные операции
     * 
     * @global type $DB
     * @param array $delete Массив Ид для удаления
     * @return boolean 
     */
    public function deletePaidOptions($delete) {
        global $DB;
        if(!$delete) return false;
        return $DB->query("DELETE FROM wizard_billing WHERE id IN (?l) AND wiz_uid = ?", $delete, step_wizard::getWizardUserID());
    }
    
    /**
     * Выбираем платные опции которые будем оплачивать, удаляем те которые не выбирали
     *  
     * @param array $option     Платные операции пользователя
     * @param array $selected   Выбранные к оплате платные операции пользователя
     */
    public function selectedPaidOption($options, $selected) {
        if($options) {
            $select = false;
            foreach($options as $payID => $val) {
                $payID = intval($payID);
                if(!$selected[$payID]) {
                    $delete[] = (int) $payID;
                } else {
                    $select[] = (int) $payID;
                }
            }
            
            if($delete) {
                // Удаляем не выбранные опции
                $this->updateParentsOptions($delete);
                $this->deletePaidOptions($delete);
            }
            
            return $select;
        }
        
        return true;
    }
    
    /**
     * Обновляем родителей платных опций при их удалении
     * 
     * @param array $selected    Массив Ид выбранных на удаление опции
     */
    public function updateParentsOptions($selected) {
        global $DB;
        
        $options = $this->getPaidOptionById($selected);
        if($options) {
            foreach ($options as $key => $option) {
                switch ($option['op_code']) {
                    // Платный проект
                    case 53:
                        switch ($option['option']) {
                            // Выделение на верху
                            case 1:
                                $sql = "UPDATE wizard_projects SET top_count = null, payed = payed - {$option['ammount']} 
                                        WHERE id = ? AND wiz_uid = ?";
                                break;
                            // Выделение цветом
                            case 2:
                                $sql = "UPDATE wizard_projects SET is_color = false, payed = payed - {$option['ammount']} 
                                        WHERE id = ? AND wiz_uid = ?";
                                break;
                            // Выделение текста
                            case 3:
                                $sql = "UPDATE wizard_projects SET is_bold = false, payed = payed - {$option['ammount']} 
                                        WHERE id = ? AND wiz_uid = ?";
                                break;
                            // Логотип
                            case 4:
                                $logo = $DB->val("SELECT logo_id FROM wizard_projects WHERE id = ? AND wiz_uid = ?", $option['parent'], step_wizard::getWizardUserID());
                                $cfile = new CFile();
                                $cfile->Delete($logo);
                                $sql = "UPDATE wizard_projects SET logo_id = null, logo_link = null, payed = payed - {$option['ammount']} 
                                        WHERE id = ? AND wiz_uid = ?";
                                break;
                        }
                        
                        $DB->query($sql, $option['parent'], step_wizard::getWizardUserID());
                        break;
                    // Конкурс -- Удалять не будем конкурс из базы тк мы конкурс все равно пишем в черновик и потом пользователь может его опубликовать и оплатить 
                    case 9:
                        //$this->_db->query("DELETE FROM wizard_projects WHERE id = ?i AND wiz_uid = ?", $option['parent'], step_wizard::getWizardUserID());
                        break;
                }
            }
        }
    }
    
    /**
     * Переносим все выбранные платные операции в черновики операций
     * 
     * @param array $selected Платные операции
     */
    public function transferPaidOptionsToDraft($selected) {
        // На всякий случай берем переносимые опции из базы чтобы не было подлогов
        $options = $this->getPaidOptionById($selected);
        $log  = new log('wizard/transfer-'.SERVER.'-%d.log', 'a', '%d.%m.%Y %H:%M:%S : ');
        if($options) {
            foreach($options as $option) {
                $id = $this->createDraftAccountOperation($option);
                if($id) {
                    $delete[] = $id;
                } else {
                    $log->writeln("Error transfer paid option to draft - user (" . wizard::getUserIDReg() . "|" . step_wizard::getWizardUserID() . ") - option #{$option['id']} (wizard_billing)");
                }
            }
            return $delete;
        }
        
        return false;
    }
    
    /**
     * Создание отложенной платной опции на основе опции созданной в мастере
     *  
     * @param type $option  Данные опции созданной в мастере @see table - wizad_billing
     * @return null|boolean     
     */
    public function createDraftAccountOperation($option) {
        global $DB;
        switch($option['op_code']) {
            // Публикация конкурса
            case 9:
            case 106:
                $descr     = "Публикация конкурса";
                $count     = 1;
                $op_type   = 'contest';
                $parent_id = $option['parent'];
                $src_id = $str_option = null;
                break;
            // Платный проект/конкурс
            case 53:
                $step_emp = new step_employer();
                $project   = $step_emp->getProjectById($option['parent']);
                $parent_id = $option['parent'];
                if($project['kind'] == 7) {
                    $title   = "конкурс";
                    $op_type = 'contest';
                } else {
                    $title   = "проект";
                    $op_type = 'project';
                }
                $count  = 1;
                $src_id = $str_option = null;
                $descr  = "Платный {$title} / ";
                switch($option['option']) {
                    case step_employer::PROJECT_OPTION_TOP:
                        $str_option  = 'top';
                        $count   = $project['top_count'];
                        $descr  .= "закрепление наверху на " . (int)$project['top_count'] . " ". ending($project['top_count'], "день", "дня", "дней");
                        break;
                    case step_employer::PROJECT_OPTION_COLOR:
                        $str_option  = 'color';
                        $descr  .= "подсветка фоном";
                        break;
                    case step_employer::PROJECT_OPTION_BOLD:
                        $str_option  = 'bold';
                        $descr  .= "жирный шрифт";
                        break;
                    case step_employer::PROJECT_OPTION_LOGO: 
                        $str_option  = 'logo';
                        $descr  .= "логотип";
                        $src_id  = $project['logo_id'];
                        break;
                }
                break;
            // Покупка аккаунта ПРО
            case 48:
            case 49:
            case 50:
            case 51:
            case 76:
            case 15:
                $descr = "Аккаунт PRO";
                $count = 1;
                $src_id = $parent_id = $str_option = $op_type = null;
                break;
            // Покупка платных ответов
            case step_freelancer::OFFERS_OP_CODE:
                $descr  = "Покупка ответов на проекты (кол-во: {$option['option']})";
                $count  = $option['option'];
                $src_id = $parent_id = $str_option = $op_type = null;
                break;
        }
        
        $pay_options = array(
            "uid"       => wizard::getUserIDReg(),
            "op_code"   => $option['op_code'],
            "op_type"   => $op_type,
            "option"    => $str_option,
            "parent_id" => $parent_id,
            "src_id"    => $src_id,
            "op_count"  => $count,
            "ammount"   => $option['ammount'],
            "descr"     => $descr,
            "comment"   => $descr,
            "status"    => null
        );
        
        $id = $DB->insert("draft_account_operations", $pay_options, 'id');
        
        if($id) {
            $this->draft[] = $id;
            return $option['id'];
        }
        
        return false;
    }
    
    /**
     * Платные опции выбранные пользователем
     * 
     * @return array 
     */
    public function getPayedOptions() {
        global $DB;
        $sql = "SELECT wb.*,  oc.op_name, wp.name as project_name, wp.logo_id, wp.is_color, wp.is_bold, wp.top_count
                FROM wizard_billing wb
                INNER JOIN op_codes oc ON oc.id = wb.op_code
                LEFT JOIN wizard_projects wp ON (wp.id = wb.parent AND wp.wiz_uid = wb.wiz_uid AND (wb.type = ? OR wb.type = ?))
                WHERE wb.wiz_uid = ?
                ORDER by wb.type DESC, wb.parent ASC";
        return  $DB->rows($sql, step_employer::BILL_TYPE_CONTEST, step_employer::BILL_TYPE_PROJECT, step_wizard::getWizardUserID()); 
    }
    
    /**
     * удаляет все платные опции пользователя или только для конкретного проекта
     * @param integer $parent = null : родитель платной опции (например id проекта)
     */
    function clearPayedOptions ($parent = null) {
        global $DB;
        if ($parent) {
            $par = " AND parent = " . $parent;
        } else {
            $par = "";
        }
        $sql = "DELETE FROM wizard_billing WHERE wiz_uid = ?" . $par;
        return $DB->query($sql, step_wizard::getWizardUserID());
    }
    
    /**
     * Отображаем отзывы к проектам, которые он оставил, пока не был ПРО
     * 
     * @global type $DB
     * @return type 
     */
    function showProjectsFeedbacks() {
        global $DB;
        return $DB->query("UPDATE projects_feedbacks SET show = TRUE WHERE user_id = ?i;", $this->uid);
    }
}

?>