<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/template.php');

/**
 * Попап обращения в арбитраж
 */
class ReservesArbitragePopup {
    
    const TPL_MAIN_PATH = '/templates/reserves/';
    const TPL_POPUP_DEFAULT = 'arbitrage_popup.tpl.php';
    
    const POPUP_ID_PREFIX = 'order_arbitrage_popup_%d';
    
    public $data;
    
    public function init() {
        global $js_file;
        $js_file['tservices_order_arbitrage'] = 'tservices/tservices_order_arbitrage.js';
    }

    public function run() {
        echo Template::render(ABS_PATH . self::TPL_MAIN_PATH . self::TPL_POPUP_DEFAULT, $this->data);
    }
    
    public static function getPopupId($id)
    {
        return sprintf(static::POPUP_ID_PREFIX, $id);
    }
}
