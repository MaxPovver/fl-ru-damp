<?php

class CustomerController extends CController 
{
    /**
     * Инициализация контроллера
     */
    public function init() 
    {
        parent::init();
        
        $uid = get_uid(false);
        
        if ($uid) {
            //Если уже авторизован то на главную
            $this->redirect('/');
        }
        
        $this->layout = '//layouts/content';
    }


    /**
     * Обработка события до какого-либо экшена
     * 
     * @param string $action
     * @return bool
     */

    public function beforeAction($action) 
    {
        $current_step = intval($action);
        $this->_check_wizard($current_step);
        unset($_SESSION['customer_wizard_filled']);
    }
    

    public function actionIndex()
    {
        $this->redirect('/welcome/customer/1/');
    }
    
    
    public function action1()
    {
        $this->render('step1');
    }
    
    
    public function action2()
    {
        global $js_file;
        
        require_once(ABS_PATH . "/classes/professions.php");
        
        $category = __paramInit('int', NULL, 'category', NULL);
        $subcategory = __paramInit('int', NULL, 'subcategory', NULL);
        
        if ($category > 0 && $subcategory > 0 && 
            professions::isExistProfId($subcategory, $category)) {
            
            $data = array();
            
            $data['kind'] = 1;//проект!
            $data['pro_only'] = true;
            $data['verify_only'] = false;
            
            $data['categories'][] = array(
                'category_id' => $category,
                'subcategory_id' => $subcategory
            );
            
            $_SESSION['customer_wizard'] = $data;
            
            $this->redirect('/welcome/customer/3/');
        }
        

        
        $professions = professions::GetProfessionsAndGroup('g.cnt DESC, p.pcount DESC NULLS LAST');
        $suffix = isset($_SESSION['pda']) && $_SESSION['pda'] == 1?'_pda':'';

        $js_file['ElementsFactory'] = 'form/ElementsFactory.js';
        $js_file['ElementVerticalSelect'] = 'form/VerticalSelect.js';
        
        $this->render("step2{$suffix}", array(
            'professions' => $professions,
            'default_group' => 2,
            'default_spec' => 9
        ));
    }
    
    
    public function action3()
    {
        require_once(ABS_PATH . "/welcome/models/CustomerNewProjectForm.php");
        
        $form = new CustomerNewProjectForm(array('step' => 3));
        
        if (isset($_POST) && 
            sizeof($_POST) > 0 && 
            $form->isValid($_POST)) {
            
            $data = $form->getValues();
            $data['IDResource'] = @$data['IDResource'][0];

            $_SESSION['customer_wizard'] = array_merge($_SESSION['customer_wizard'], $data);
                    
            $this->redirect('/welcome/customer/4/');
        }
        
        $this->render('step3', array(
            'form' => $form->render()
        ));
    }    
    
    
    public function action4()
    {
        require_once(ABS_PATH . "/welcome/models/CustomerNewProjectForm.php");
        
        $form = new CustomerNewProjectForm(array('step' => 4));
        
        if (isset($_POST) && 
            sizeof($_POST) > 0 && 
            $form->isValid($_POST)) {
            
            $data = $form->getValues();
            
            $cost_element = $form->getElement('cost');
            $is_agreement = $cost_element->getValue('agreement') == 1;
            $data['cost'] = ($is_agreement)?0:$data['cost'];
            $data['currency'] = ($is_agreement)?0:$cost_element->getValue('currency_db_id');
            $data['priceby'] = ($is_agreement)?1:$cost_element->getValue('priceby_db_id');
            
            $_SESSION['customer_wizard'] = array_merge($_SESSION['customer_wizard'], $data);
            $_SESSION['customer_wizard']['filled'] = true;
            
            $_SESSION['from_welcome_wizard'] = true;
            
            $this->redirect('/registration/?type=empl');
        }        
        
        $this->render('step4', array(
            'form' => $form->render()
        ));        
    }

    


    /**
     * Проверка последовательности прохода мастера
     * 
     * @param type $current_step
     */
    protected function _check_wizard($current_step)
    {
        $prev_step = isset($_SESSION['customer_wizard_step'])?$_SESSION['customer_wizard_step']:1;
        
        if ($prev_step >= $current_step - 1) {
            $_SESSION['customer_wizard_step'] = $current_step;
        } else {
            $this->redirect("/welcome/customer/{$prev_step}/");
        }
    }
}