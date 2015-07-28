<?php 

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/wizard/step_wizard_registration.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/wizard/wizard_billing.php';

/**
 * Класс для работы с этапами мастера по регистрации фрилансеров 
 * 
 */
class step_freelancer extends step_wizard_registration
{
    /**
     * Позиция шага регистрации у фрилансеров
     *  
     */
    const STEP_REGISTRATION_CONFIRM = 3;
    
    /**
     * Код операции платного ответа 
     */
    const OFFERS_OP_CODE = 61;
    
    /**
     * Количество проектов на начальной странице
     */
    const DEF_PROJECTS_PER_PAGE = 3;
    
    /**
     * Количетство догружаемых проектов 
     */
    const LOAD_PROJECTS_PER_PAGE = 10;
    
    /**
     * Максимальные значения стоимости часа работы
     * @var array
     */
    public $MAX_COST_HOUR = array(
        0 => 300,  // USD
        1 => 250,  // Euro
        2 => 7500, // Руб
        3 => 400   // FM
    );
    
    /**
     * Максимальная стоимость месяца работы
     * @var array
     */
    public $MAX_COST_MONTH = array(
        0 => 10000,  // USD
        1 => 8000,   // Euro
        2 => 250000, // Руб
        3 => 13000   // FM
    );
    
    /**
     * Максимальное значение опыта работы 
     */
    const MAX_YEAR_VALUE = 100;
    
    /**
     * Статическая функция для возврата ид операций по покупке ПРО
     * 
     * @return array
     */
    public static function getOperationCodePRO() {
        return array(76, 48, 49, 50, 51);
    }
    
    /**
     * Вывод и обработка информации
     */
    public function render() {
        switch($this->pos) {
            // Этап - "Поиск проекта и ответ на него"
            case 1:
                $this->actionProjects();
                break;
            // Этап - "Заполнение портфолио"
            case 2:
                $this->actionPortfolio();
                break;
            // Этап - "Регистрация"
            case 3:
                if($this->isCompleted()) {
                    $this->parent->setNextStep($this->parent->getPosition() + 1);
                    header("Location: /wizard/registration/");
                    exit;
                }
                /*if ($this->status == step_wizard::STATUS_COMPLITED) {
                    $this->transferWizardContent();
                }*/
                $this->registration(step_wizard_registration::TYPE_WIZARD_FRL);
                break;
            // Этап - "Дополнительные возможности"
            case 4:
                $this->completeData(step_wizard_registration::TYPE_WIZARD_FRL);
                break;  
            // Этап - "Оплата услуг" и завершение мастера
            case 5:
                $this->actionCompletedWizard();
                break; 
            // По умолчанию этап - "Поиск проекта и ответ на него"
            default:
                $this->actionProjects();
                break;
        }
    }
    
    /**
     * Обрабатываем данные для ввывода информации по шагу 
     */
    public function actionProjects() {
        require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php";
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
        
        $prj_id = __paramInit('int', 'project', null, 0);
        
        if($prj_id > 0) {
            $obj_project = new projects();
            $project = $obj_project->GetPrjCust($prj_id);
        }
        
        if($project['id'] > 0) {
            require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/users.php";
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/opinions.php");
            
            // Загружаем данные по отзывам автора проекта
            $op_data                = opinions::getCounts($project['user_id'], array('frl', 'norisk', 'all', 'total'));
            //$op_data['norisk']['a'] = ( (int)$op_data['norisk']['p'] + (int)$op_data['norisk']['n'] + (int)$op_data['norisk']['m'] );
            //$op_data['all']['a']    = ( (int)$op_data['all']['p']    + (int)$op_data['all']['n']    + (int)$op_data['all']['m'] );
            $op_data['total']['a']    = ( (int)$op_data['total']['p']    + (int)$op_data['total']['n']    + (int)$op_data['total']['m'] );
            
            $user = new users();
            $user->GetUserByUID($project['user_id']);
            $registered      = strtolower(ElapsedMnths(strtotime($project['reg_date'])));
            $is_offer        = $this->isOfferProject($project['id']);
            $count_offer     = $this->countOffers();
            $count_pay_offer = $this->countPayOffers();
            // Максимальное количество ответов для пользователя
            $max_offers = 3 + $count_pay_offer; 
            // Обработка запросов на странице
            $action     = __paramInit('string', null, 'action');
            if($action && ($count_offer < $max_offers || $project['kind'] == 7 || $action == 'paid_offer')) {
                $error  = $this->actionProcessingProjects($action, $project);
            }
            include $_SERVER['DOCUMENT_ROOT']."/wizard/registration/steps/tpl.step.answer.php";
        } else {
            $filter      = array();
            $category    = $_COOKIE[$this->parent->getCookieName('categories')];
            $subcategory = $_COOKIE[$this->parent->getCookieName('subcategories')];
            if($category > 0) {
                $cat[] = professions::GetGroupName($category);
                $filter['active']   = 't';
                $filter['my_specs'] = 'f';
                $filter['categories'][0] = array($category => '0');
                
                if ($subcategory > 0) {
                    $cat[] = professions::GetProfName($subcategory);
                    unset($filter['categories'][0]);
                    $filter['categories'][1] = array($subcategory => '1');
                }
                
                $category_name = implode(": ", $cat);
            }
            $obj_project = new new_projects();
            $obj_project->page_size = step_freelancer::DEF_PROJECTS_PER_PAGE;
            $projects =  $obj_project->getProjects($count, -1, 1, false, $filter, true, false, NULL, true);
            include $_SERVER['DOCUMENT_ROOT']."/wizard/registration/steps/tpl.step.search.php";
        }
    }
    
    /**
     * Обработка запросов на странице
     * 
     * @param array $project данные проекта по которому идет ответ
     * @return array 
     */
    public function actionProcessingProjects($action, $project) {
        switch($action) {
            case 'create_offer':
                $insert['project_id']    = $project['id'];
                $insert['wiz_uid']       = $this->getWizardUserID();
                $insert['post_date']     = "NOW()";
                $insert['descr']         = __paramInit('string', null, 'answer');
                $insert['cost_from']     = __paramInit('float', null, 'from_budget');
                $insert['cost_to']       = __paramInit('float', null, 'to_budget');
                $insert['cost_currency'] = __paramInit('integer', null, 'currency_db_id');
                $insert['time_from']     = __paramInit('float', null, 'from_time');
                $insert['time_to']       = __paramInit('float', null, 'to_time');
                $insert['time_type']     = __paramInit('integer', null, 'time_db_id');

                foreach($_POST['work_namefile'] as $pos=>$filename) {
                    $prev[$pos] = __paramValue('string', $filename);
                    if($_POST['work_idfile'][$pos] > 0) {
                        $previd[] = intval($_POST['work_idfile'][$pos]);
                    }
                }
                $insert['pict1'] = $prev[1];
                $insert['pict2'] = $prev[2];
                $insert['pict3'] = $prev[3];

                if($insert['descr'] == '' && !$previd) {
                    $error['answer'] = 'Введите описание предложения';
                }
                
                if(strlen(stripslashes($_POST['answer'])) > 1000) {
                    $error['answer'] = 'Исчерпан лимит символов для поля (1000 символов)';
                }

                // проверка бюджета
                if(strlen($insert['cost_to']) > 6) {
                    $insert['cost_to'] = 999999;
                    $error['cost_to'] = 'Слишком большая сумма';
                }
                if(strlen($insert['cost_from']) > 6) {
                    $insert['cost_from'] = 999998;
                    $error['cost_from'] = 'Слишком большая сумма';
                }
                
                // проверка срока
                if(strlen($insert['time_to']) > 3) {
                    $insert['time_to'] = 999;
                    $error['time_to'] = 'Слишком большой срок';
                }
                if(strlen($insert['time_from']) > 3) {
                    $insert['time_from'] = 998;
                    $error['time_from'] = 'Слишком большой срок';
                }
                
                
                if(!$error) {
                    $id_offer = $this->createOffer($insert);

                    if($id_offer) {
                        // Обновляем ид родителя файлов
                        $this->_db->update("file_wizard", array("src_id" => $id_offer, "type" => 2), "id IN (?l)", $previd);
                        header("Location: /wizard/registration/?action=next&complited=1");
                        exit;
                    }
                }
                return $error;
                break;
            case 'paid_offer':
                $answer = __paramInit("integer", null, "answer");
                $payed  = array(
                    "op_code" => step_freelancer::OFFERS_OP_CODE,
                    "wiz_uid" => $this->getWizardUserID()
                );
                // Количество платных ответов
                switch($answer) {
                    case 1:
                        $payed['ammount'] = 1;
                        $payed['option']  = 1; 
                        break;
                    case 5:
                        $payed['ammount'] = 4;
                        $payed['option']  = 5; 
                        break;
                    case 10:
                        $payed['ammount'] = 7;
                        $payed['option']  = 10;
                        break;
                    default:
                        $error = "Ошибка операции";
                        break;
                }

                if(!$error) {
                    $bill_id = wizard_billing::addPaidOption($payed);
                    // Если операция удачна возвращаем на тот же проект для ответа на него
                    if($bill_id > 0) {
                        header("Location: /wizard/registration/?project={$project['id']}");
                        exit;
                    } else {
                        $error = "Ошибка записи операции";
                    }
                }
                return $error;
                break;
            default:
                return "Неопознанная операция";
                break;
        }
    }
    
    /**
     * Проверяем ответил ли уже польщователь на проект или нет через мастер
     * 
     * @param type $prj_id  ИД Проекта
     * @return integer Возвращает ИД ответа 
     */
    public function isOfferProject($prj_id) {
        if(!$prj_id) return false;
        return $this->_db->val("SELECT id FROM wizard_offers WHERE project_id = ?i AND wiz_uid = ?", $prj_id, $this->getWizardUserID());
    }
    
    /**
     * Общее количество ответов на проект через мастер
     * 
     * @return integer 
     */
    public function countOffers() {
        return $this->_db->val("SELECT COUNT(*) 
                                FROM wizard_offers wo
                                INNER JOIN projects p ON p.id = wo.project_id AND p.kind <> 7 
                                WHERE wo.wiz_uid = ? ", $this->getWizardUserID());
    }
    
    /**
     * Количество купленных платных ответов через мастер
     * 
     * @return integer 
     */
    public function countPayOffers() {
        return $this->_db->val("SELECT SUM(option) FROM wizard_billing WHERE op_code = ? AND wiz_uid = ?", step_freelancer::OFFERS_OP_CODE, $this->getWizardUserID());
    }
     
    /**
     * Создание ответа на проект
     * 
     * @param array $insert Данные по ответу на проект 
     * @return type 
     */
    public function createOffer($insert) {
        $id = $this->_db->insert("wizard_offers", $insert, "id");
        return $id;
    }
    
    /**
     * Берем ответы на проект созданные через мастер 
     * (только проекты не для ПРО, не закрытые и незаблокированные и пользователь проекта не забанен)
     * 
     * @param integer|string $uid         Ид пользователя (если int - то это ИД зарегистрированного пользователя, если string - Ид пользователя мастера)
     * @param string  $limit              Лимит выборки обычных ответов
     * @param boolean $not_pro            Берем проекты где могут отвечать только ПРО или нет (если true - не берем)
     * @return array - Данные ответов
     */
    public function getWizardOffers($uid = false, $limit = 3, $not_pro = true) {
        if(!$uid) $uid = $this->getWizardUserID();
        
        if(is_integer($uid)) {
            $where[] = "wo.reg_uid = ?";
        } else {
            $where[] = "wo.wiz_uid = ?";
        }
        
        if($not_pro) {
            $where[] = "p.pro_only <> true";
        }
        
        $str = implode(" AND ", $where);
        $con = "SELECT wo.*, p.kind FROM 
                wizard_offers wo
                INNER JOIN projects p ON p.id = wo.project_id
                INNER JOIN employer e ON e.uid = p.user_id 
                LEFT JOIN projects_blocked pb ON pb.project_id = p.id
                WHERE p.closed = false AND p.kind = 7 AND pb.id IS NULL AND e.is_banned = B'0' " . ( $str ? " AND " . $str : "" ) . "            
                ORDER BY wo.id ASC";
        
        $sql = "SELECT wo.*, p.kind FROM 
                wizard_offers wo
                INNER JOIN projects p ON p.id = wo.project_id
                INNER JOIN employer e ON e.uid = p.user_id 
                LEFT JOIN projects_blocked pb ON pb.project_id = p.id
                WHERE p.closed = false AND p.kind <> 7 AND pb.id IS NULL AND e.is_banned = B'0' " . ( $str ? " AND " . $str : "" ) . "            
                ORDER BY wo.id ASC LIMIT {$limit}";
        
        $sql_union = "( $con ) UNION ( $sql )";        
                
        return $this->_db->rows($sql_union, $uid, $uid);
    }
    
    /**
     * Чистим ответы к заблокированным проектам и к закрытым тк на них уже не ответить 
     * + конкурсы закончились
     * 
     */
    public function clearOffers() {
        $sql = "
            DELETE FROM wizard_offers WHERE id IN (
                SELECT wo.id FROM 
                wizard_offers wo
                INNER JOIN projects p ON p.id = wo.project_id
                INNER JOIN employer e ON e.uid = p.user_id 
                LEFT JOIN projects_blocked pb ON pb.project_id = p.id
                WHERE wo.wiz_uid = ? AND p.closed = true AND pb.id IS NULL AND e.is_banned = B'0' OR (p.kind = 7 AND p.end_date < NOW())           
            )";
        
        $this->_db->query($sql, $this->getWizardUserID());
    }
    
    /**
     * Обновляем ответ фрилансера
     * 
     * @param array $update  Данные для обновления
     * @return boolean 
     */
    public function updateOffers($update) {
        return $this->_db->update("wizard_offers", $update, "wiz_uid = ?", $this->getWizardUserID());
    }
    
    /**
     * Обработка информации по шагу портфолио 
     */
    public function actionPortfolio() {
        require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php";
        
        $category = intval($_COOKIE['your_categories']);
        $spec     = intval($_COOKIE['your_subcategories']);
        
        if($category > 0) {
            $category_name = professions::GetGroupName($category);
        } else {
            $category = 0;
        }
        if($spec > 0) {
            $subcategory_name = professions::GetProfName($spec);
        } else {
            $spec = 0;
        }
        
        $action = __paramInit('string', null, 'action');
        
        if($action == 'upd_portf') {
            $error = $this->actionProcessingPortfolio();
            if($error) {
                $data         = $this->request;
                $portf_insert = $this->portf_insert;
                /*if($data['resume'] > 0) {
                    $resume = new CFile($data['resume']);
                }*/
            }
        } else {
            // запись в базе для текущего портфолио
            $field = $this->parent->getFieldsUser();
            // все данные портфолио
            $data  = unserialize($field['portfolio']);
            // сохраненные работы для текущего портфолио
            $portf_insert = $this->getWorks();

        }
        if($data['resume'] > 0) {
            $resume = new CFile($data['resume']);
        }

        $curr_hour_name  = $this->CURRENCY_TYPE[$data['cost_type_hour']];
        $curr_month_name = $this->CURRENCY_TYPE[$data['cost_type_month']];
        // подготовка специализации для вывода в шаблон
        $spec = $data['spec'];
        $specString = professions::GetProfNameWP($spec, '/', null, false);
        list($category_name, $subcategory_name) = explode('/', $specString);
        
        $count_portf   = $this->getCountWorks();
        $answersExists = $this->_getCountAnswers();
        
        include $_SERVER['DOCUMENT_ROOT']."/wizard/registration/steps/tpl.step.portfolio.php";
    }
    
    /**
     * Возвращает количество ответов пользователя на проекты
     * @return int 
     * */
    private function _getCountAnswers() {
        $uid = $this->getWizardUserID();
        $query = "SELECT COUNT(id) FROM wizard_offers WHERE wiz_uid = '$uid'";
        return $this->_db->val($query);
    }
    /**
     * Обработка данных в шаге порфтоило
     * 
     * @return string 
     */
    public function actionProcessingPortfolio() {
        $error = array();
        
        $type        = __paramInit('int', null, 'spec_column_id');
        $spec        = __paramInit('int', null, 'spec_db_id');
        $spec_name   = __paramInit('string', null, 'spec');
        if($type == 0) {
            $error['spec'] = 'Вы должны выбрать свою специализацию';
        } else {
            $data['spec_orig'] = professions::GetProfessionOrigin($spec);
        }

        $data['spec']            = $spec;
        $data['exp']             = __paramInit('int', null, 'exp');
        $data['cost_hour']       = __paramInit('float', null, 'cost_hour');
        $data['cost_type_hour']  = __paramInit('int', null, 'currency_hour_db_id');
        $curr_hour_name          = __paramInit('string', null, 'currency_hour');
        $data['cost_month']      = __paramInit('float', null, 'cost_month');
        $data['cost_type_month'] = __paramInit('int', null, 'currency_month_db_id');
        $curr_month_name         = __paramInit('string', null, 'currency_month');

        $data['resume']    = __paramInit('int', null, 'resume_id');
        $data['info']      = stripslashes(__paramInit('string', null, 'info', ''));
        if(strlen($data['info']) > 4000) {
            $error['info'] = 'Исчерпан лимит символов для этого поля (4000 символов)';
        }
        $data['in_office'] = $_POST['in_office'] == 1 ? 1 : 0;

        if($data['resume'] > 0) {
            $resume = new CFile($data['resume']);
        }

        if (($data['exp'] < 0) || ($data['exp'] > step_freelancer::MAX_YEAR_VALUE)) {
            $error['exp'] = 'Недопустимое значение. Опыт работы должен быть в пределе от 0 до ' . ( step_freelancer::MAX_YEAR_VALUE ) . '.';
        }

        if (($data['cost_hour'] < 0) || ($data['cost_hour'] > $this->MAX_COST_HOUR[$data['cost_type_hour']])) {
            $error['cost_hour']  = 'Недопустимое значение. Стоимость часа работы должна быть в пределе ' . view_range_cost2(0, $this->MAX_COST_HOUR[$data['cost_type_hour']], '', '', false, $data['cost_type_hour'] . '.');
        }
        if (($data['cost_month'] < 0) || ($data['cost_month'] > $this->MAX_COST_MONTH[$data['cost_type_month']])) {
            $error['cost_month'] = 'Недопустимое значение. Стоимость месяца работы должна быть в пределе ' . view_range_cost2(0, $this->MAX_COST_MONTH[$data['cost_type_month']], '', '', false, $data['cost_type_month']) . '.';
        }

        if(count($error) <=0) {
            $insert['portfolio'] = serialize($data);
            $this->parent->saveFieldsInfo($insert);
        }

        // Обрабатываем порфтолио
        if(is_array($_POST['name'])) {
            foreach($_POST['name'] as $k=>$value) {
                $value = __paramValue('string', stripslashes($value));
                $descr = __paramValue('string', stripslashes($_POST['descr'][$k]));
                $id  = __paramValue('int', $_POST['id'][$k]); // id работы, если он есть
                $link  = __paramValue('string', stripslashes($_POST['link'][$k]));
                $link  = preg_replace("/^http:\/\//", "", trim($link));
                
                if ( $value == '' && $descr == '' && $link == '' && empty($_POST['pict_id'][$k]) ) {
                    continue;
                }
                if(is_empty_html($value)) {
                    $error['portf'.$k]['name'] = "Введите название работы";
                }
                if(strlen($descr) > 1500) {
                    $error['descr'.$k]['name'] = "Исчерпан лимит символов для этого поля (1500 символов)";
                }
                if ($link != '' && !url_validate($link)) {
                    $error['portf'.$k]['link'] = "Поле заполнено некорректно";
                }

                $portf_insert[] = array(
                    "name"    => $value,
                    "pict_id" => ( $_POST['pict_id'][$k] > 0  ? intval($_POST['pict_id'][$k]) : null),
                    "link"    => $link,
                    "prof_id" => $spec,
                    "wiz_uid" => $this->getWizardUserID(),
                    "descr"   => $descr,
                    "id"      => $id
                );
            }
        }

        if (count($error) <= 0 ) {
            if (count($portf_insert) > 0) {
                $ids = $this->createWorks($portf_insert);
            }

            $this->parent->setCompliteStep(true);
            $this->parent->setNextStep( $this->parent->getPosition() + 1 );

            header("Location: /wizard/registration/");
            exit;
        }
        
        $this->request      = $data;
        $this->portf_insert = $portf_insert;
        
        return $error;
    }
    
    /**
     * Добавляем все порфтолио в таблицу
     * 
     * @param array $portfs  Портфолио
     * @return array - Ид добавленных портфолио 
     */
    public function createWorks($works) {
        if(is_array($works)) {
            foreach($works as $k=>$work) {
                // если есть id, значит работа уже сохранена - значить обновляем
                $id = $work['id'];
                unset($work['id']);
                if (!$id) {
                    $ids[$k] = $this->createWork($work);
                } else {
                    $ids[$k] = $this->updateWork($work, $id);
                }
            }
            return $ids;
        }
        return false;
    }
    
    /**
     * Создание работы в портфолио 
     * 
     * @param array $portfolio данные по работе  
     * @return boolean|integer 
     */
    public function createWork($work) {
        return $this->_db->insert('wizard_portfolio', $work, 'id');
    }
    
    /**
     * Обновлене работы в портфолио 
     * 
     * @param array $work данные по работе
     * @param integer $id id работы
     * @return boolean|integer 
     */
    public function updateWork($work, $id) {
        if ($this->_db->update('wizard_portfolio', $work, 'id = ?i', $id)) {
            return $work['id'];
        } else {
            return null;
        };
        //$this->_db->update("file_wizard", array("src_id" => $id_offer, "type" => 2), "id IN (?l)", $previd);
    }
    
    /**
     * Считаем количество добавленых работ в портфолио
     * 
     * @param string $wiz_uid ИД пользователя мастера
     * @return type 
     */
    public function getCountWorks($wiz_uid = false) {
        if(!$wiz_uid) $wiz_uid = $this->getWizardUserID();
        return $this->_db->val("SELECT COUNT(*) FROM wizard_portfolio WHERE wiz_uid = ?", $wiz_uid);
    }
    
    /**
     * Берем работы пользователя 
     * 
     * @param string $wiz_uid Ид пользователя мастера
     * @return array
     */
    public function getWorks($wiz_uid = false) {
        if(!$wiz_uid) $wiz_uid = $this->getWizardUserID();
        return $this->_db->rows("SELECT * FROM wizard_portfolio WHERE wiz_uid = ?", $wiz_uid);
    }
    
    /**
     * Обрабатываем информацию по шагу Оплата услуг (завершение мастера)
     */
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
            if(in_array($pay['op_code'], step_freelancer::getOperationCodePRO())) {
                $is_pro = true;
                $op_id  = $pay['id'];
            } else {
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
            header("Location: /registration/wellcome/freelancer.php");
            exit;
        }
    }
    
    /**
     * Перенос всех данных введнных в мастере на боевые таблицы
     * должен запускаться синхронно с завершением работы мастера
     * 
     * @return type 
     */
    public function transferWizardContent() {
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/users.php";
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/log.php";
        $this->log  = new log('wizard/transfer-'.SERVER.'-%d.log', 'a', '%d.%m.%Y %H:%M:%S : ');
        
        $user = new users();
        $user->GetUserByUID(wizard::getUserIDReg());
        $this->user = $user;
        // Чистим ответы на заблокированные проекты
        $this->clearOffers();
        // некоторые ответы могут остатся в этой таблице поэтому обновляем им Ид пользователя (при завершении мастера все данные по Ид пользователя мастера удаляются)
        $this->updateOffers(array('reg_uid' => wizard::getUserIDReg()));
        // пользователь может быть уже PRO (если он ранее был авторизован) - учитываем это
        $pro = is_pro();
        $limit = $pro ? "all" : 3;
        // Берем все конкурсы + 3 обычных проекта для публикации ответов (без конкурсов и проектов для ПРО)
        $offers  = $this->getWizardOffers(false, $limit, !$pro);
        if($offers) {
            $error = $this->transferOffers($offers);
        }
        // Пишем данные пользователя введенные в шаге портфолио
        $field = $this->parent->getFieldsUser();
        $data  = unserialize($field['portfolio']);
        if($data) {
            $error = $this->transferUserInformation($data);
        }
        
        //Перенос порфтолио
        $works = $this->getWorks();
        if($works) {
            $error = $this->transferWorks($works);
        }
        
        return $error;
    }
    
    /**
     * Перенос работ портфолио на боевые таблицы
     * 
     * @param array $works   Созданные в мастере работы
     * @return array 
     */
    public function transferWorks($works) {
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/portfolio.php";
                
        foreach($works  as $k=>$portf) {
            $pict = new CFile($portf['pict_id']);
            $new_position = $k+1;
            
            if($portf['pict_id'] > 0) {
                $sm_pict = $this->_db->val("SELECT id FROM file_wizard WHERE fname = ?", "sm_" . $pict->name);
                $files[] = array('id' => $portf['pict_id']);
                if($sm_pict) $files[] = array('id' => $sm_pict);
                
                $table = 'file';
                $dir    = "users/".substr($this->user->login, 0, 2)."/".$this->user->login."/upload/";
                list($pict, $sm_pict) = $this->transferFiles($files, $table, $dir);
                $pict    = new CFile($pict['id']);
                $sm_pict = new CFile($sm_pict['id']);
            }

            $error = portfolio::AddPortf(wizard::getUserIDReg(), $portf['name'], $pict, $sm_pict, $portf['link'], $portf['descr'], $portf['prof_id'], 
                                         null, null, null, null, null, $file_error, $preview_error, $new_position);

            if($file_error)  $error = $file_error;
            if($preview_error) $error = $preview_error;
            
            if(!$error) {
                $delete_work[] = $portf['id'];
            } else {
                $error_work[]  = $error . " - работа #{$portf['id']}";
            }

            unset($error);
        }

        if($error_work) {
            foreach($error_work as $error) {
                $this->log->writeln("Error transfer portfolio content - user (" . wizard::getUserIDReg() . "|" . $this->getWizardUserID() . ") - Error: {$error}");
            }
        }

        if($delete_work) {
            $this->_db->query("DELETE FROM wizard_portfolio WHERE id IN (?l) AND wiz_uid = ?", $delete_work, $this->getWizardUserID());
        }
        
        return $error_work;
    }
    
    /**
     * Обновляем данные пользователя введенные в шаге портфолио
     * 
     * @param array $data Данные пользователя
     */
    public function transferUserInformation($data) {
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/freelancer.php";
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/professions.php";
        
        $frl = new freelancer();
        if($data['resume'] > 0) {
            $dir    = "users/" . substr($this->user->login, 0, 2) . "/" . $this->user->login . "/resume/";
            $table  = 'file';
            $files = array(
                array('id' => $data['resume'])
            );
            $resume = $this->transferFiles($files, $table, $dir);
        }

        $frl->exp               = $data['exp'];
        $frl->cost_hour         = $data['cost_hour'];
        $frl->cost_month        = $data['cost_month'];
        $frl->cost_type_hour    = $data['cost_type_hour'];
        $frl->cost_type_month   = $data['cost_type_month'];
        $frl->spec_text         = $data['info'];
        $frl->in_office         = $data['in_office'];
        $frl->spec              = $data['spec'];
        $frl->spec_orig         = $data['spec_orig'];
        $frl->resume_file       = $resume[0]['fname'];
        $error_db = $frl->Update(wizard::getUserIDReg(), $res);

        $prof = new professions();
        if ($data['spec_orig'] > 0) {
            $error_db = $prof->UpdatePortfChoise(wizard::getUserIDReg(), array($data['spec_orig']));
        }
        
        // Если нет ошибок очищаем таблицу
        if($error_db) {
            $this->log->writeln("Error transfer data user content - user (" . wizard::getUserIDReg() . "|" . $this->getWizardUserID() . ") - Error: {$error_db}");
        } else {
            $this->_db->query("DELETE FROM wizard_fields WHERE id = ? AND wiz_uid = ?", $field['id'], $this->getWizardUserID());
        }
        
        return $error_db;
    }
    
    /**
     * Переносим ответы на проекты в работающие таблицы
     * 
     * @param array $offers  Ответы на проекты
     * @return array
     */
    public function transferOffers($offers) {
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/projects_offers.php";
        require_once $_SERVER['DOCUMENT_ROOT']."/classes/contest.php";
        
        foreach ($offers as $k => $offer) {
            $pict1 = str_replace("sm_", "", $offer['pict1']);
            $pict2 = str_replace("sm_", "", $offer['pict2']);
            $pict3 = str_replace("sm_", "", $offer['pict3']);
            
            // Переносим файлы в рабочие папки сайта
            $files = $this->_db->rows("SELECT DISTINCT id FROM file_wizard WHERE fname IN (?l)", array($pict1, $pict2, $pict3));
            if ($files) {
                $dir   = "users/" . substr($this->user->login, 0, 2) . "/" . $this->user->login . "/upload/";
                $table = 'file';
                $picts = $this->transferFiles($files, $table, $dir, false);
                
                $sm_files = $this->_db->rows("SELECT DISTINCT id FROM file_wizard WHERE fname IN (?l)", array($offer['pict1'], $offer['pict2'], $offer['pict3']));
                if ($sm_files) {
                    $sm_picts = $this->transferFiles($sm_files, $table, $dir, false);
                }
            }

            if ($offer['kind'] != 7) {
                $error = projects_offers::AddOffer( wizard::getUserIDReg(), $offer['project_id'], $offer['cost_from'], $offer['cost_to'], $offer['cost_currency'], 
                                                    $offer['time_from'], $offer['time_to'], $offer['time_type'], $offer['descr'], 0, 0, 0, 
                                                    null, null, null, null, null, null, $picts[0]['fname'], $picts[1]['fname'], $picts[2]['fname'], 
                                                    $sm_picts[0]['fname'], $sm_picts[1]['fname'], $sm_picts[2]['fname']);
            } else {
                // Пишем ответ на конкурс
                $contest = new contest($offer['project_id'], wizard::getUserIDReg());
                $error   = $contest->CreateOffer($offer['descr'], implode('/', $files), false);
                if ($picts && $contest->new_oid) {
                    $content_pict = array();
                    foreach ($picts as $k => $pict) {
                        $content_pict[] = array(
                            'uid'       => wizard::getUserIDReg(),
                            'file'      => $pict['id'],
                            'prev'      => $sm_picts[$k]['id'],
                            'orig_name' => $pict['orig_name'],
                            'post_date' => date('Y-m-d H:i:s')
                        );
                    }
                    $contest->addOfferFiles($contest->new_oid, $content_pict);
                }
            }

            if (!$error) {
                $delete_offers[] = $offer['id'];
            } else {
                $error_offer[]   = $error. " - ответ на проект #{$offer['id']}";
            }
            unset($error);
        }
        
        // Очищаем перенесенные данные если нет ошибок если есть выводим
        if($error_offer) {
            foreach($error_offer as $error) {
                $this->log->writeln("Error transfer offer content - user (" . wizard::getUserIDReg() . "|" . $this->getWizardUserID() . ") - Error: {$error}");
            }
        } else {
            if($delete_offers) {
                $this->_db->query("DELETE FROM wizard_offers WHERE id IN (?l) AND wiz_uid = ?", $delete_offers, $this->getWizardUserID());
            }
        }
        
        return $error_offer;
    }
}

?>