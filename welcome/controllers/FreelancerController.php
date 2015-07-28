<?php

class FreelancerController extends CController 
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

    /*
    public function beforeAction($action) 
    {
        
    }
    */
    

    public function actionIndex()
    {
        $this->redirect('/welcome/freelancer/1/');
    }
    
    
    public function action1()
    {
        $this->render('step1'); 
    }
    
    
    public function action2()
    {
        $_SESSION['from_welcome_wizard'] = true;
        $this->render('step2');
    }
    
    
    /*
    protected function _check_wizard($current_step)
    {
    }
     */
}