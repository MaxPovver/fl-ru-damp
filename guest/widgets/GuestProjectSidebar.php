<?php


class GuestProjectSidebar extends CWidget
{
    public $is_project = true;


    public function run() 
    {
        $this->render('guest-project-sidebar', array('is_project' => $this->is_project));
    }
}