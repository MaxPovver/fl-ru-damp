<?php 

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/wizard/step_wizard_registration.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/wizard/wizard_billing.php';

/**
 * Класс для работы с этапами мастера по регистрации работодателей
 * 
 */
class step_employer extends step_wizard_registration
{
    public $project_type = 1;
    
    /**
     * Определение платной опции закрепление наверху страницы в БД
     */
    const PROJECT_OPTION_TOP   = 1;
    
    /**
     * Определение платной опции выделение цветом
     */
    const PROJECT_OPTION_COLOR = 2;
    
    /**
     * Определение платной опции выделение текста
     */
    const PROJECT_OPTION_BOLD  = 3;
    
    /**
     * Определение платной опции логтип 
     */
    const PROJECT_OPTION_LOGO  = 4;
    
    /**
     * Позиция шага регистрации у работодателей 
     */
    const STEP_REGISTRATION_CONFIRM = 2;
    
    /**
     * Тип операции в платных опциях для конкурса 
     */
    const BILL_TYPE_CONTEST = 1;
    
    /**
     * Тип операции в платных опциях для проекта 
     */
    const BILL_TYPE_PROJECT = 2;
    
    /**
     * Код операции по покупке PRO 
     */
    const OP_CODE_PRO = 15;
    
    /**
     * Вывод и обработка информации
     */
    public function render() {
        switch($this->pos) {
            // Этап - "Публикация проекта"
            case 1:
                $this->actionProjects();
                break;
            // Этап - "Регистрация"
            case 2:
                if($this->isCompleted()) {
                    $this->parent->setNextStep($this->parent->getPosition() + 1);
                    header("Location: /wizard/registration/");
                    exit;
                }
                $this->registration(step_wizard_registration::TYPE_WIZARD_EMP);
                break;
             // Этап - "Дополнительные возможности"
            case 3:
                $this->completeData(step_wizard_registration::TYPE_WIZARD_EMP);
                break;  
            // Этап - "Оплата услуг"
            case 4:
                $this->actionCompletedWizard();
                break; 
            // По умолчанию этап - "Публикация проекта"
            default:
                require_once ($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
                $categories  = professions::GetAllGroupsLite();
                $professions = professions::GetAllProfessions();
                array_group($professions, 'groupid');
                $professions[0] = array();
                include $_SERVER['DOCUMENT_ROOT']."/wizard/registration/steps/tpl.step.project.php";
                break;
        }
    }
    
    /**
     * Обработка информации шага проекты 
     */
    public function actionProjects() {
        $prj_id = $_SESSION['view_wizard_project'];

        if (!$prj_id) {
            require_once ($_SERVER['DOCUMENT_ROOT'] . "/classes/attachedfiles.php");
            require_once ($_SERVER['DOCUMENT_ROOT'] . "/classes/project_exrates.php");
            require_once ($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
            require_once ($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
            require_once ($_SERVER['DOCUMENT_ROOT'] . "/classes/country.php");
            require_once ($_SERVER['DOCUMENT_ROOT'] . "/classes/city.php");
            
            $prj_exrates = project_exrates::GetAll();
            $categories = professions::GetAllGroupsLite();
            $professions = professions::GetAllProfessions();
            array_group($professions, 'groupid');
            $professions[0] = array();
            $addedPrc  = is_pro() ? 0 : new_projects::PRICE_ADDED;

            $colorPrc  = new_projects::PRICE_COLOR * ($addedPrc > 0 ? 1 : 0);
            $boldPrc   = new_projects::PRICE_BOLD + $addedPrc;
            $logoPrc   = new_projects::PRICE_LOGO + $addedPrc;
            $cTopPrice = new_projects::PRICE_CONTEST_TOP1DAY + $addedPrc;
            $pTopPrice = new_projects::PRICE_TOP1DAY + $addedPrc;
            
            $action    = __paramInit('string', 'action', 'action', null);
            $attachedfiles_session = __paramInit('string', 'attachedfiles_session', 'attachedfiles_session', false);
            
            // проверяем есть ли уже созданый проект
            $projects = $this->getCreatedProjects();
            $existPrjID = count($projects) > 0 ? $projects[0][id] : null;
            
            // выводим ранее сохраненный проект
            if ($action != 'create_project' && $existPrjID) {
                $data = $projects[0];
                list($category, $subcategory) = explode('|', $data['categories']);
                
                $currency_name = $this->CURRENCY_TYPE[$data['currency']];
                $priceby_name = $this->PRICEBY_TYPE[$data['priceby']];
                
                $data['pro_only'] = $data['pro_only'] === 't';
                
                $price = $data['payed'];
                $option = array();
                $option['top'] = $data['top_count'] > 0;
                $option['top_count'] = $data['top_count'];
                $option['color'] = $data['is_color'] === 't';
                $option['bold'] = $data['is_bold'] === 't';
                $option['logo'] = $data['logo_id'] > 0;
                
            } elseif ($action == 'create_project') { // сохраняем новый проект или изменяем старый
                $in_office = __paramInit('int', null, 'in_office', 0);
                $type      = __paramInit('string', null, 'kind');

                if ($type == 'contest') {
                    $data['kind']     = 7;
                    $data['end_date'] = date('d-m-Y', strtotime(__paramInit('string', null, 'end_date_eng_format', 0)));
                    $data['win_date'] = date('d-m-Y', strtotime(__paramInit('string', null, 'win_date_eng_format', 0)));
                } elseif ($in_office == 1) {
                    $location = __paramInit('integer', null, 'location_column_id');
                    if ($location == 1) {
                        $data['city']    = __paramInit('integer', null, 'location_db_id');
                        $data['country'] = country::getCountryByCityId($data['city']);
                    } else {
                        $data['country'] = __paramInit('integer', null, 'location_db_id');
                    }
                    $data['kind'] = 4;
                } else {
                    $data['kind'] = 1;
                }

                $data['name']     = __paramInit('string', null, 'name');
                $data['descr']    = __paramInit('string', null, 'descr');
                $category         = __paramInit('int', null, 'r_category');
                $subcategory      = __paramInit('int', null, 'r_subcategory');
                $agreement        = __paramInit('int', null, 'agreement', 0);
                $data['pro_only'] = __paramInit('int', null, 'pro_only', 0) == 1 ? true : false;

                if ($agreement != 1) {
                    $data['cost']        = __paramInit('int', null, 'cost', 0);
                    $data['priceby']     = __paramInit('int', null, 'r_priceby', 0);
                    $data['currency']    = __paramInit('int', null, 'r_currency', 0);
                    $data['budget_type'] = __paramInit('int', null, 'budget_type', 0);
                }

                if (is_empty_html($data['descr'])) {
                    $error['descr'] = 'Поле не заполнено';
                }

                if (is_empty_html($data['name'])) {
                    $error['name'] = 'Поле не заполнено';
                }
                // проверяем длину необработанной строки, а иначе спецсимволы считаются как несколько символов
                if (strlen(stripslashes($_POST['name'])) > 60) {
                    $error['name'] = 'Превышен лимит - 60 символов';
                }

                if (!$category) {
                    $error['category'] = 'Не выбран раздел';
                } elseif ($subcategory) {
                    $data['categories'] = "{$category}|{$subcategory}";
                } else {
                    $data['categories'] = $category;
                }

                if ($data['cost'] < 0) {
                    $error['cost'] = 'Введите положительную сумму';
                }

                if ($data['cost'] > 999999) {
                    $error['cost'] = 'Слишком большая сумма';
                }

                if ($data['cost'] > 0 && ($data['currency'] < 0 || $data['currency'] > 3)) {
                    $error['currency'] = 'Валюта не определена';
                }

                if ($data['cost'] > 0 && ($data['priceby'] < 1 || $data['priceby'] > 4)) {
                    $error['priceby'] = 'Вид бюджета не определен';
                }
                $descr_limit = projects::LIMIT_DESCR;
                if (strlen_real($data['descr']) > $descr_limit) {
                    $error['descr'] = "Исчерпан лимит символов ($descr_limit)";
                }

                if ($data['kind'] == 7) {
                    if (!preg_match("/^([0-9]{1,2})\-([0-9]{1,2})\-([0-9]{4})$/", $data['end_date'], $o1) || !checkdate($o1[2], $o1[1], $o1[3])) {
                        $error['end_date'] = 'Неправильная дата';
                    }
                    if (!preg_match("/^([0-9]{1,2})\-([0-9]{1,2})\-([0-9]{4})$/", $data['win_date'], $o2) || !checkdate($o2[2], $o2[1], $o2[3])) {
                        $error['win_date'] = 'Неправильная дата';
                    }
                    if (!$error['end_date'] && mktime(0, 0, 0, $o1[2], $o1[1], $o1[3]) <= mktime(0, 0, 0)) {
                        $error['end_date'] = 'Дата окончания конкурса не может находиться  в прошлом';
                    }
                    if (!$error['win_date'] && mktime(0, 0, 0, $o2[2], $o2[1], $o2[3]) <= mktime(0, 0, 0, $o1[2], $o1[1], $o1[3])) {
                        $error['win_date'] = 'Дата определения победителя должна быть больше даты окончания конкурса';
                    }
                }

                $option['top']       = __paramInit('int', null, 'option_top', 0);
                $option['top_count'] = __paramInit('int', null, 'option_top_count', 0);
                $option['color']     = __paramInit('int', null, 'option_color', 0);
                $option['bold']      = __paramInit('int', null, 'option_bold', 0);
                $option['logo']      = __paramInit('int', null, 'option_logo', 0);

                // логотип
                if ($option['logo'] == 1) {
                    $data['logo_link'] = str_replace("http://", "", __paramInit('string', null, 'logo_link', null));
                    $data['logo_id'] = __paramInit('int', null, 'logo_id', null);

                    // если выбрана опция "Логотип со ссылкой", то картинка должна быть обязательно
                    if (!$data['logo_id']) {
                        $error['logo_image'] = 'Отсутствует логотип';
                    }

                    if ($data['logo_link'] === 'Адрес сайта') {
                        $data['logo_link'] = '';
                    }                    
                    if ($data['logo_link'] !== '' && !is_url($data['logo_link'])) {
                        $error['logo_link'] = "Не верно введен адрес";
                    }
                } else {
                    $data['logo_id'] = null;
                }

                $price = 0;
                
                // закрепление на верху
                if ($option['top'] == 1 && $option['top_count'] > 0) {
                    if ($option['top_count'] > 999) {
                        $option['top_count'] = 999;
                    }
                    $price = (int) $option['top_count'] * ( $data['kind'] == 7 ? $cTopPrice : $pTopPrice );
                    $data['top_count'] = (int) $option['top_count'];
                    $pay_option[] = array(
                        "wiz_uid" => $this->getWizardUserID(),
                        "op_code" => new_projects::OPCODE_PAYED,
                        "option"  => step_employer::PROJECT_OPTION_TOP, // Закрепление проекта
                        "type"    => $data['kind'] == 7 ? step_employer::BILL_TYPE_CONTEST : step_employer::BILL_TYPE_PROJECT,
                        "ammount" => $price
                    );
                } else {
                    $data['top_count'] = 0;
                }
                
                // выделение цветом
                if ($option['color'] == 1) {
                    $price += (int) $colorPrc;
                    $data['is_color'] = true;
                    $pay_option[] = array(
                        "wiz_uid" => $this->getWizardUserID(),
                        "op_code" => new_projects::OPCODE_PAYED,
                        "option"  => step_employer::PROJECT_OPTION_COLOR, // Выделение цветом 
                        "type"    => $data['kind'] == 7 ? step_employer::BILL_TYPE_CONTEST : step_employer::BILL_TYPE_PROJECT,
                        "ammount" => $colorPrc
                    );
                } else {
                    $data['is_color'] = false;
                }
                
                // выделение жирным
                if ($option['bold'] == 1) {
                    $price += (int) $boldPrc;
                    $data['is_bold'] = true;
                    $pay_option[] = array(
                        "wiz_uid" => $this->getWizardUserID(),
                        "op_code" => new_projects::OPCODE_PAYED,
                        "option"  => step_employer::PROJECT_OPTION_BOLD, // Выделение текста
                        "type"    => $data['kind'] == 7 ? step_employer::BILL_TYPE_CONTEST : step_employer::BILL_TYPE_PROJECT,
                        "ammount" => $boldPrc
                    );
                } else {
                    $data['is_bold'] = false;
                }
                
                if ($option['logo'] == 1) {
                    $price += (int) $logoPrc;

                    $pay_option[] = array(
                        "wiz_uid" => $this->getWizardUserID(),
                        "op_code" => new_projects::OPCODE_PAYED,
                        "option"  => step_employer::PROJECT_OPTION_LOGO, // Логтип
                        "type"    => $data['kind'] == 7 ? step_employer::BILL_TYPE_CONTEST : step_employer::BILL_TYPE_PROJECT,
                        "ammount" => $logoPrc
                    );
                }

                if ($price > 0) {
                    $data['payed'] = (int) $price;
                }

                if (!$error) {
                    $data['wiz_uid'] = $this->getWizardUserID();
                    // если проект уже есть
                    if ($existPrjID) {
                        // то просто обновляем его
                        $prj_id = $this->updateProject($data, $existPrjID);
                        // и очищаем все платные опции для этого проекта
                        wizard_billing::clearPayedOptions($prj_id);
                    } else {
                        $prj_id = $this->createProject($data);
                    }

                    if ($prj_id && $_POST['attachedfiles_session']) {
                        $attachedfiles = new attachedfiles($_POST['attachedfiles_session']);
                        $files = $attachedfiles->getFiles(array(1,2,3,4));
                        $this->parent->addAttachedFiles($files, $prj_id);
                        $attachedfiles->clear();
                    }
                    
                    if ($prj_id && $data['kind'] == 7) {
                        $insert = array(
                            "wiz_uid" => $data['wiz_uid'],
                            "op_code" => new_projects::OPCODE_KON_NOPRO,
                            "type"    => step_employer::BILL_TYPE_CONTEST,
                            "ammount" => new_projects::getKonkursPrice(),
                            "parent"  => $prj_id
                        );

                        wizard_billing::addPaidOption($insert);
                    }

                    if ($price > 0 && $prj_id) {
                        foreach($pay_option as $k=>$opt) {
                            $opt['parent'] = $prj_id;
                            wizard_billing::addPaidOption($opt);
                        }
                    }

                    if ($prj_id) {
                        $_SESSION['view_wizard_project'] = $prj_id;
                        header("Location: /wizard/registration/");
                        exit;
                    } else {
                        $error['project'] = 'Ошибка записи проекта';
                    }
                }
                
                $currency_name = __paramInit('string', null, 'currency');
                $priceby_name  = __paramInit('string', null, 'priceby');

            }
            
            // Генерируем данные для вывода ошибок и заполнения полей
            $loc[] = country::GetCountryName($data['country']);
            if ($data['city']) {
                $loc[] = city::GetCityName($data['city']);
            }
            $location_name = implode(": ", $loc);

            $cat[] = professions::GetGroupName($category);
            if ($subcategory) {
                $cat[] = professions::GetProfName($subcategory);
            }
            $category_name = implode(": ", $cat);


            if ($data['logo_id']) {
                $file = new CFile($data['logo_id']);
                $logo_path = WDCPREFIX . "/" . $file->path . $file->name;
            }                

            include $_SERVER['DOCUMENT_ROOT'] . "/wizard/registration/steps/tpl.step.project.php";
        } else {
            $project  = $this->getProjectById($prj_id);
            $attached = $this->getProjectAttach($prj_id);

            include $_SERVER['DOCUMENT_ROOT'] . "/wizard/registration/steps/tpl.step.project.view.php";
        }
    }
    
    /**
     * Создаем проект в таблице мастера
     * 
     * @param array $insert  Данные для создания
     * @return type 
     */
    public function createProject($insert) {
        return $this->_db->insert("wizard_projects", $insert, "id");
    }
    
    /**
     * Создаем проект в таблице мастера
     * 
     * @param array $insert  Данные для создания
     * @return integer id
     */
    public function updateProject($insert, $prjID) {
        $res = $this->_db->update("wizard_projects", $insert, "id = ?i", $prjID);
        return ($res !== null && !$this->_db->error) ? $prjID : null;
    }
    
    /**
     * Возвращает проект созданный в мастере по его ИД
     * 
     * @param integer $id  ИД проекта
     * @return array 
     */
    public function getProjectById($id) {
        return $this->_db->row("SELECT wp.*, f.path, f.fname FROM wizard_projects wp LEFT JOIN file f ON f.id = wp.logo_id WHERE wp.id = ?i", $id);
    }
    
    /**
     * Возвращает файлы приложенные к проекту созданному в мастере
     * 
     * @param integer $id  ИД проекта
     * @return array 
     */
    public function getProjectAttach($id) {
        $sql = "SELECT * FROM file_wizard WHERE type = 1 AND src_id = ?i";
        return $this->_db->rows($sql, $id);
    }
    
    /**
     * Вовзвращает все созданные в мастере проекту 
     * 
     * @return array
     */
    public function getCreatedProjects() {
        $sql = "SELECT * FROM wizard_projects WHERE wiz_uid = ?";
        return $this->_db->rows($sql, $this->getWizardUserID());
    }
    
    /**
     * Очищает сессию шага  
     */
    public function clearSessionStep() {
        unset($_SESSION['view_wizard_project']);
    }
    
    
    public function actionCompletedWizard() {
        if($this->isDisable()) {
            header("Location: /wizard/registration/?step=1");
            exit;
        }
        $action = __paramInit('string', null, 'action');
        if($action == 'upd_pay_options') {
            $this->actionProcessingCompletedWizard();
        }
        
        $payed = wizard_billing::getPayedOptions();
        foreach($payed as $pay) {
            if($pay['op_code'] == step_employer::OP_CODE_PRO) {
                $is_pro = true;
                $op_id  = $pay['id'];
            } elseif($pay['op_code'] == 53 && $pay['option'] == step_employer::PROJECT_OPTION_COLOR) {
                $disabled[$pay['id']] = $pay['id'];
            }
        }
        if(!$is_pro) unset($disabled);
        if($disabled) $str_disabled = implode(",", $disabled);
        $dis[$op_id] = $str_disabled;
        include $_SERVER['DOCUMENT_ROOT']."/wizard/registration/steps/tpl.step.buy.php";
    }
    
    /**
     * Функция для обработки данных шага Оплата услуг 
     */
    public function actionProcessingCompletedWizard() {
        $options  = $_POST['options'];
        $selected = $_POST['pay_options'];
        $default  = $_POST['default'];
        if($default) {
            foreach($default as $k=>$v) {
                if($v > 0) $selected[$k] = 1;
            }
        }
        $wizard_billing = new wizard_billing();
        $selecting  = $wizard_billing->selectedPaidOption($options, $selected);
        // Есть выбранные операции переносим
        if($selecting) {
            $delete = $wizard_billing->transferPaidOptionsToDraft($selecting);
            $wizard_billing->deletePaidOptions($delete); // Удаляем все успешно записанные операции, операции которые не записались остаются в базе
        }

        // Публикация проектов и обработка всех остальных данных относящихся к пользователю
        $error = $this->transferWizardContent();

        if(!$error) {
            $this->parent->exitWizard(false);
            // факт того, что пользователь только что зарегестрировался (сбрасывается на страницах wellcome)
            $_SESSION['is_new_user'] = 1;
            header("Location: /registration/wellcome/employer.php");
            exit;
        }
    }
    
    /**
     * Перенос всех данных введнных в мастере на боевые таблицы
     * 
     * @return type 
     */
    public function transferWizardContent() {
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/log.php";
        $this->log  = new log('wizard/transfer-'.SERVER.'-%d.log', 'a', '%d.%m.%Y %H:%M:%S : ');
        
        // Сначала переносим проекты
        $error = $this->transferProjects();
        return $error;
    }
    
    public function transferProjects() {
        $projects = $this->getCreatedProjects();
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/projects.php";
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/drafts.php";
        
        if($projects) {
            $key = md5(microtime());
            $prj = new tmp_project($key);
            $this->setPath();
            
            foreach($projects as $k=>$project) {
                $files = $this->getProjectAttach($project['id']);
                if($project['kind'] == 7) {
                    $tmp_dir = $this->tmpAbsDir;
                } else {
                    $tmp_dir = $this->dstAbsDir;
                }
                if($files) {
                    $table = 'file_projects';
                    $files = $this->transferFiles($files, $table, $tmp_dir);
                }
                
                if($project['kind'] != 7) {
                    $attach  = array_map(create_function('$a', 'return array("file_id" => $a["id"]);'), $files);
                    $insert = array(
                        "user_id"    => ( $_SESSION['uid'] ? $_SESSION['uid'] : $_SESSION['RUID'] ),
                        "name"       => addslashes($project['name']),
                        "descr"      => addslashes($project['descr']),
                        "kind"       => $project['kind'],
                        "cost"       => $project['cost'],
                        "currency"   => $project['currency'],
                        "country"    => ( $project['country'] > 0 ? $project['country'] : 'NULL' ),
                        "city"       => ( $project['city'] > 0 ? $project['city'] : 'NULL' ),
                        "payed"      => '0',
                        "pro_only"   => $project['pro_only'],
                        "logo_id"    => 'NULL',
                        "link"       => $project['logo_link'],
                        "is_color"   => 'f',
                        "is_bold"    => 'f',
                        "billing_id" => 0,
                        "payed_items"=> "000",
                        "folder_id"  => 0,
                        "budget_type"=> $project['budget_type'],
                        "priceby"    => $project['priceby'],
                        "prefer_sbr" => $project['prefer_sbr']
                    );
                    
                    $cat           = explode("|", $project['categories']);
                    $categories[0] = array('category_id' => intval($cat[0]), 'subcategory_id' => intval($cat[1]));

                    $prj->addPrj($insert, $attach, $categories); // Добавляем проект
                    if($insert['id']) {
                        $delete_projects[] = $project['id'];
                    } else {
                        $error_project[] = "Ошибка создания проекта #{$project['id']}";
                    }
                    // Проверяем платные опции если есть обновляем в отложенных операциях тк выше все операции выбранные там уже ушли туда
                    if($insert['id'] && ($project['is_color'] || $project['is_bold'] || $project['top_count'] > 0 || $project['logo_id'] > 0)) {
                        $update = array("parent_id" => $insert['id']);
                        $this->_db->update("draft_account_operations", $update, "parent_id = ? AND op_type = 'project' AND uid = ?", $project['id'], wizard::getUserIDReg());  
                        if ( $project['logo_link'] ) {
                            $update = array("extra" => $project['logo_link']);
                            $this->_db->update("draft_account_operations", $update, "parent_id = ? AND op_type = 'contest' AND option = 'logo' AND uid = ?", $contest['id'], wizard::getUserIDReg());
                        }
                    }
                // Если это конкурс он сразу идет в черновики
                } else {
                    $draft = new drafts();
                    $cat   = explode("|", $project['categories']);
                    
                    $insert = array(
                        "uid"           => ( $_SESSION['uid'] ? $_SESSION['uid'] : $_SESSION['RUID'] ),
                        "name"          => addslashes($project['name']),
                        "descr"         => addslashes($project['descr']),
                        "kind"          => $project['kind'],
                        "cost"          => $project['cost'],
                        "currency"      => $project['currency'],
                        "country"       => $project['country'],
                        "end_date"      => $project['end_date'],
                        "win_date"      => $project['win_date'],
                        "city"          => $project['city'],
                        "pro_only"      => ($project["pro_only"] == 't'? 1 : 0),
                        "budget_type"   => $project['budget_type'],
                        "priceby"       => $project['priceby'],
                        "prefer_sbr"    => $project['prefer_sbr'],
                        "categories"    => array(0 => $cat[0]),
                        "subcategories" => array(0 => $cat[1]),
                    	"logo_id"    => $project['logo_id'],
                        "link"       => $project['logo_link']                        
                    );

                    $contest = $draft->SaveProject($insert, $files);
                    if($contest['id']) {
                        $delete_projects[] = $project['id'];
                    } else {
                        $error_project[] = "ошибка создания конкурса #{$project['id']}";
                    }
                    // Проверяем платные опции если есть пишем в отложенные платежи
                    if($contest['id'] && ($project['is_color'] || $project['is_bold'] || $project['top_count'] > 0 || $project['logo_id'] > 0)) {
                        $update = array("parent_id" => $contest['id']);
                        $this->_db->update("draft_account_operations", $update, "parent_id = ? AND op_type = 'contest' AND uid = ?", $project['id'], wizard::getUserIDReg());
                        if ( $project['logo_link'] ) {
                            $update = array("extra" => $project['logo_link']);
                            $this->_db->update("draft_account_operations", $update, "parent_id = ? AND op_type = 'contest' AND option = 'logo' AND uid = ?", $contest['id'], wizard::getUserIDReg());
                        }
                    }
                }
            }
            
            if($delete_projects) {
                $this->_db->query("DELETE FROM wizard_projects WHERE id IN (?l) AND wiz_uid = ?", $delete_projects, $this->getWizardUserID());
            }
            
            if($error_projects) {
                foreach($error_projects as $error) {
                    $this->log->writeln("Error transfer projects content () - user (" . wizard::getUserIDReg() . "|" . $this->getWizardUserID() . ") - Error: {$error}");
                }
            }
        }
        return $error;
    }
    
    /**
     * Инциализируем пути для перекидывания файлов 
     */
    public function setPath() {
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/projects.php";
        
        $user = new users();
        $user->GetUserByUID(wizard::getUserIDReg());
        $login = $user->login;
        $cfile = new CFile();
        $tmp_path = 'users/'.substr($login, 0, 2).'/'.$login.'/';
        $this->tmpAbsDir = $tmp_path.tmp_project::TMP_DIR.'/';
        $month = date('Ym');
        $this->dstAbsDir = 'projects/upload/' . $month . '/';
    }
}

?>