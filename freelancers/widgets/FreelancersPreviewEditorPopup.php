<?php

require_once(ABS_PATH . '/classes/yii/CPopup.php');

class FreelancersPreviewEditorPopup extends CPopup
{
    const TAB_TU = 'tu';
    const TAB_PF = 'pf';

    static $types = array(
        self::TAB_PF, 
        self::TAB_TU);

    protected $tabs = array(
        self::TAB_PF => 'Портфолио',
        self::TAB_TU => 'Типовые услуги'
    );
    
    protected $limit = 6;

    protected $current_tab = self::TAB_PF;

    protected function initJS() 
    {
        return array(
            'tab/tab' => 'tab/tab.js',
            'popup/freelancersPreviewEditorPopup' => 'popup/freelancersPreviewEditorPopup.js'
        );
    }        
    
    
    
    protected function _getTabTu($user, $page = 1)
    {
        require_once(ABS_PATH . '/classes/tservices/tservices.php');
        require_once(ABS_PATH . '/tu/models/TServiceItemIterator.php');
        
        $tab = null;
        
        $tservices = new tservices($user['uid']);
        $list = $tservices->setPage($this->limit, $page)->getShortList();
        if ($list) {
            $total = $tservices->getCount();
            $pages = ceil($total / $this->limit);
            $tab = array(
                'elements' => new TServiceItemIterator($user, $list),
                'pages' => $pages,
                'page' => $page
            );            
        }
        
        return $tab;
    }


    protected function _getTabPf($user, $page = 1)
    {
        require_once(ABS_PATH . '/classes/portfolio.php');
        require_once(ABS_PATH . '/portfolio/models/PortfolioItemIterator.php');
        
        $tab = null;
        
        $portfolio = new portfolio();
        $list = $portfolio->getList($user['uid'], $page, $this->limit);
        
        if ($list) {
            $total = $portfolio->CountAll($user['uid']);
            $pages = ceil($total / $this->limit);
            $tab = array(
                'elements' => new PortfolioItemIterator($user, $list),
                'pages' => $pages,
                'page' => $page                
            );
        }
        
        return $tab;
    }

    
    
    protected function getTabMethodName($name)
    {
        return "_getTab" . ucfirst($name);
    }


    
    public function getCurrentTab()
    {
        return $this->current_tab;
    }

    

    protected function init($params) 
    {
        $page = isset($params['page']) && 
                $params['page'] > 0? intval($params['page']):1;
        
        $this->current_tab = isset($params['tab']) && 
                             isset($this->tabs[$params['tab']])? 
                $params['tab'] : $this->current_tab;


        $user = array(
            'uid' => $_SESSION['uid'],
            'login' => $_SESSION['login']
        );
        
        $options = array(
            'popup_title' => 'Выберите превью для отображения в каталоге',
            'popup_width' => 720
        );        

        if ($this->is_ajax) {
             
            $method_name = $this->getTabMethodName($this->current_tab);
            $tab_data = $this->{$method_name}($user, $page);
            
            $content = Template::render(__DIR__ . '/views/freelancers-preview-editor-popup-tab-content.php', array(
                'value' => $tab_data,
                'key' => $this->current_tab
            ));
            
        } else {
            $tabs = array();
            $first_exist_tab = null;
            
            foreach ($this->tabs as $key => $value) {
                $method_name = $this->getTabMethodName($key);
                
                $tab_data = $this->{$method_name}($user, $page);
                if ($tab_data) {
                    $tabs[$key] = $tab_data;
                    $tabs[$key]['title'] = $value;
                    $tabs[$key]['active'] = $key == $this->current_tab;
                    
                    if (!$first_exist_tab) {
                        $first_exist_tab = $key;
                    }
                }
            }

            //Если табов активных поумолчанию нет то включаем первую существующую
            if (!isset($tabs[$this->current_tab]['active']) && $first_exist_tab) {
                $tabs[$first_exist_tab]['active'] = true;
            }
            
            
            $group_id = isset($params['group_id'])?$params['group_id']:0;
            $prof_id = isset($params['prof_id'])?$params['prof_id']:0;
            $hash = paramsHash(array($group_id,$prof_id));
            
            $content = Template::render(__DIR__ . '/views/freelancers-preview-editor-popup.php', array(
                'tabs' => $tabs,
                'tab_panel_name' => "{$this->id}Tab",
                'group_id' => $group_id,
                'prof_id' => $prof_id,
                'hash' => $hash        
            ));
            
        }
        
        $this->setContent($content);

        return $options;
    }
}