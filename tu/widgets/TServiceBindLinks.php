<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/tservices_binds.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/quick_payment/quickPaymentPopupTservicebind.php');


/**
 * Âèäæåò - Ññûëêè íà ïğîäëåíèå è ïîäíÿòèå çàêğåïëåííîé óñëóãè
 */
class TServiceBindLinks {
    
    const TPL_MAIN_PATH = '/tu/widgets/views/';
    
    const TPL_DEFAULT = 't-service-bind-links.php';
    const TPL_INNER = 't-service-bind-links-hor.php';
   
    public $kind;
    public $uid;
    public $is_inner;
    public $date_stop;
    public $allow_up;
    public $tservice_id;
    
    protected $data;
    
    /**
     * Èíèöèàëèçàöèÿ òèçåğà
     * @global array $js_file
     * @param array $options [kind, uid]
     */
    public function init($options = array())
    {

        if ($options) {
            $this->kind = (int)$options['kind'];
            $this->uid = (int)$options['uid'];
        }
        $this->data = array();
        
        $tservices_binds = new tservices_binds($this->kind);
        
        $this->data['bind_up_price'] = $tservices_binds->getPrice(true, $this->uid);
        $this->data['date_stop'] = dateFormat('j', $this->date_stop) 
                . ' ' 
                . monthtostr(dateFormat('m', $this->date_stop), true);
        $this->data['allow_up'] = $this->allow_up;
        $this->data['tservice_id'] = $this->tservice_id;
        
    }
    
    public function run() 
    {
        $template = $this->is_inner ? self::TPL_INNER : self::TPL_DEFAULT;
        echo Template::render(ABS_PATH . self::TPL_MAIN_PATH . $template, $this->data);
    }
}