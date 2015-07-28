<?php

/**
 * Class ReservesAdminNavigation
 *
 */
class ReservesAdminNavigation extends CWidget 
{
    public $menu_items = array();
    public $current_action;


    public function init() 
    {
        $default_menu_items = array(
            'index' => array(
                'title' => 'Все Сделки',
                'url' => '?action=index'
            ),
            'frod' => array(
                'title' => 'Подозрительные сделки',
                'url' => '?action=frod'
            ),
            'reestr' => array(
                'title' => 'Реестры',
                'url' => '?action=reestr'
            ),
            'factura' => array(
                'title' => 'Реестр счет-фактур',
                'url' => '?action=factura'
            ),
            'archive' => array(
                'title' => 'Архив документов',
                'url' => '?action=archive'
            )
        );
        
        $this->menu_items = array_merge(
                $default_menu_items, 
                $this->menu_items);
    }
    

    public function run() 
    {
        //собираем шаблон
        $this->render('reserves-admin-navigation', array(
            'menu_items' => $this->menu_items,
            'current_action' => $this->current_action
        ));
    }
}