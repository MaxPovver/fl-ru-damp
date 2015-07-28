<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");

/**
 * Class TServiceFreelancersCategories
 *
 * Виджет - список категорий специализаций фрилансеров
 */
class TServiceFreelancersCategories extends CWidget {

    public function run() 
    {
        //на случай если уже есть глобальная 
        //переменная в нужными данными
        global $profs;

        if(!isset($profs))
        {
            $prfs = new professions();
            $profs = $prfs->GetAllProfessions("", 0, 1);
            //@todo: передлагаю закешировать навечно чейчас на 60 сек в методе выше
        }
        
        $this->render('t-service-freelancers-categories', array(
            'profs' => $profs,
        ));
    }
}