<?php

/**
 * Class TServiceOrderStatusPopup
 * Âèäæåò ïîêàçûâàåò ïîïàï ôğèëàíñåğó ïåğåä ñìåíîé ñòàòóñà ñ çàêàçå ÒÓ
 */

class TServiceOrderStatusPopup extends CWidget 
{
    public $data = array();

    public function init($data = array()) 
    {
        parent::init();
        if(!empty($data)) $this->data = $data;
    }

    public function run() 
    {
        //ñîáèğàåì øàáëîí
        $this->render("t-service-order-status-frl-popup", $this->data);
    }
}