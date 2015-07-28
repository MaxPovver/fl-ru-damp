<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/template.php');

/**
 * Попап обращения в арбитраж
 */
class ReservesArbitrageForm {
    
    const TPL_MAIN_PATH = '/templates/reserves/';
    const TPL_POPUP_DEFAULT = 'arbitrage_form.tpl.php';
    
    public $data;
    
    public function init() {
        global $js_file;
        $js_file['tservices_order_arbitrage'] = 'reserves/arbitrage_form.js';
    }

    public function run() {
        echo Template::render(ABS_PATH . self::TPL_MAIN_PATH . self::TPL_POPUP_DEFAULT, $this->data);
    }
    
}
