<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/tservices/tservices_binds.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/quick_payment/quickPaymentPopupTservicebind.php');


/**
 * Виджет - Тизер закрепления услуг
 */
class TServiceBindTeaser {
    
    const TPL_MAIN_PATH = '/tu/widgets/views/';
    
    const TPL_DEFAULT = 't-service-bind-teaser.php';
    const TPL_INNER = 't-service-bind-teaser-hor.php';
   
    public $kind;
    public $uid;
    public $prof_id;
    public $is_inner;
    
    protected $data;
    
    /**
     * Инициализация тизера
     * @global array $js_file
     * @param array $options [kind, uid]
     */
    public function init($options = array())
    {
        global $js_file;
        $js_file[] = "tservices/tservices_binds.js";

        if ($options) {
            $this->kind = (int)$options['kind'];
            $this->uid = (int)$options['uid'];
        }
        
        
        $time_to = time() + 7*86400;
        $use_bind_popup = false;
        $tu_bind_teaser = array(
            'date' => date('j',$time_to) . ' ' . monthtostr(date('n',$time_to),true),
            'href' => 'javascript:void(0);'
        );

        $tservices_binds = new tservices_binds($this->kind);
        $tservices_class = new tservices($this->uid);

        $profs = array();
        if ($this->kind == tservices_binds::KIND_SPEC) {
            $profs[] = $this->prof_id;
        } elseif ($this->kind == tservices_binds::KIND_GROUP) {
            $tservices_categories = new tservices_categories();
            $categories = $tservices_categories->getCategoriesByParent($this->prof_id);
            foreach ($categories as $category) {
                $profs[] = $category['id'];
            }
        }

        if ($countBindedTu = $tservices_binds->countBindedTu($this->uid, (int)$this->prof_id)) {
            $binded_text = ending($countBindedTu, 'ваша услуга', 'ваши услуги', 'ваших услуг');
            $tu_bind_teaser['subtitle'] = $countBindedTu . ' ' . $binded_text. ' уже <br>закреплен'.($countBindedTu>1?'ы':'а').' в этом разделе';
            if ($tservices_class->hasUnbindedTservices($this->kind, $this->uid, $profs)) {
                $use_bind_popup = true;
                $tu_bind_teaser['title'] = 'Закрепите еще одну услугу';
                $tu_bind_teaser['btn_text'] = 'Закрепить';
            } else {
                $tu_bind_teaser['title'] = 'Добавьте еще одну услугу<br>и закрепите ее здесь';
                $tu_bind_teaser['href'] = '/users/'.$_SESSION['login'].'/tu/new/';
                $tu_bind_teaser['btn_text'] = 'Добавить';
            }        
        } else {
            if ($tservices_class->hasUserTservice(true, $profs)) {
                $use_bind_popup = true;
                $tu_bind_teaser['title'] = 'Закрепите здесь услугу';
                $tu_bind_teaser['btn_text'] = 'Закрепить';
            } else {
                $tu_bind_teaser['title'] = 'Добавьте свою услугу<br>и закрепите ее здесь';
                $tu_bind_teaser['href'] = '/users/'.$_SESSION['login'].'/tu/new/';
                $tu_bind_teaser['btn_text'] = 'Добавить';
            }
        }

        if ($use_bind_popup) {
            quickPaymentPopupTservicebind::getInstance()->init(array(
                'uid' => $this->uid,
                'kind' => $this->kind,
                'prof_id' => $this->prof_id
            ));
            $tu_bind_teaser['popup_id'] = quickPaymentPopupTservicebind::getInstance()->getPopupId(0);
            $tu_bind_teaser['popup'] = quickPaymentPopupTservicebind::getInstance()->render();
        }
        
        $this->data = $tu_bind_teaser;
        
        $this->data['price'] = $tservices_binds->getPrice(false, $this->uid, $this->prof_id);
        $this->data['main_div_class'] = $this->kind == tservices_binds::KIND_LANDING 
                ? 'b-layout__tu-cols b-layout__tu-cols_height_330'
                : 'i-pic i-pic_port i-pic_width_225 i-pic_margbot_30';
    }
    
    public function run() 
    {
        $template = $this->is_inner ? self::TPL_INNER : self::TPL_DEFAULT;
        echo Template::render(ABS_PATH . self::TPL_MAIN_PATH . $template, $this->data);
    }
}