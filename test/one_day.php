<?php
/**
 * Скрипт возмещения платных сервисов на 1 сутки. (Настраивается в переменной $setting)
 * 
 * @example
 * php one_day.php start
 * 
 * 
 */
define('LOG_FILE', '/var/tmp/payed-1day.csv');
setlocale(LC_ALL, 'ru_RU.CP1251');

if(!isset($argv[1])) {
    print "\n";
    print "ERROR\n";
    print "Example: php one_day.php start\n";
    exit;
}

require_once '../classes/config.php';
require_once '../classes/DB.php';

$DB = new DB('master');

$setting = array("from" => '2011-10-12 23:00', // Период активности услуги С 
                 "to"   => '2011-10-13 12:00', // период активности услуги По
                 "day"  => '1 day' // Количество возмещаемых дней 
                 );

switch($argv[1]) {
    case "start":
        $csv = "";
        print "\n";
        print "Processing...\n\n";
        
        $sql = "SELECT oc.op_name, o.billing_id, u.login FROM orders o
                INNER JOIN users u ON u.uid = o.from_id 
                INNER JOIN op_codes oc ON oc.id = o.tarif
                WHERE from_date + to_date >= '{$setting['from']}' AND from_date <= '{$setting['to']}' AND payed = 't'
                
                UNION ALL
                
                SELECT oc.op_name, o.billing_id, u.login FROM users_first_page o
                INNER JOIN users u ON u.uid = o.user_id 
                INNER JOIN op_codes oc ON oc.id = o.tarif
                WHERE from_date + to_date >= '{$setting['from']}' AND from_date <= '{$setting['to']}' AND payed = 't'
                
                UNION ALL
                
                SELECT oc.op_name, o.billing_id, u.login FROM projects o
                INNER JOIN users u ON u.uid = o.user_id 
                INNER JOIN account_operations ao ON ao.id = o.billing_id
                INNER JOIN op_codes oc ON oc.id = ao.op_code
                WHERE top_to >= '{$setting['from']}' AND top_from <= '{$setting['to']}'";
        $result = $DB->rows($sql);
        
        if($result)
            foreach($result as $row) {
                $csv .= implode(";", $row)."\n";
            }
        
        $update = "UPDATE orders SET to_date = to_date+'{$setting['day']}'::interval WHERE from_date + to_date >= '{$setting['from']}' AND from_date <= '{$setting['to']}' AND payed = 't';
                   UPDATE users_first_page SET to_date = to_date+'{$setting['day']}'::interval WHERE from_date + to_date >= '{$setting['from']}' AND from_date <= '{$setting['to']}' AND payed = 't';
                   UPDATE projects SET top_to = top_to + '{$setting['day']}' WHERE top_to >= '{$setting['from']}' AND top_from <= '{$setting['to']}';";
        
        $error = $DB->query($update);
        if($error != null) {
            file_put_contents(LOG_FILE, $csv);
            echo "\nDONE\n";
        } else {
            echo "\nERROR :: {$error}\n";
        }
        
        break;
    default:
        echo "ERROR :: Command not found\n";
        break;    
}

?>