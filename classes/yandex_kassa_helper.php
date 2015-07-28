<?php

class yandex_kassa_helper {
    
    public static function isAllowKassa() {
        //require_once(ABS_PATH . '/classes/config/quick_payment_config.php');
        
        return true; //in_array($_SESSION['login'], $allowed_logins);
    }
    
}
