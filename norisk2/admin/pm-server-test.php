<?php
define('NO_CSRF', true);

switch($_GET['opname']) {
    case 'refundPayment':
        $operation = array(
            'ErrorCode' => 0, 
            'Refund'    => array(
                'RefundID'  => 30149, 
                'Status'    => "PENDING", 
                'ErrorCode' => 0, 
                'Ammount'   => $_GET['ammount']
            )
        );
        break;
    case 'listRefunds':
        $operation = array(
            'ErrorCode' => 0, 
            'Refunds'    => array(
                array(
                    'RefundID'  => rand(1,50000), 
                    'PaymentID' => $_GET['paymentID'],
                    'Status'    => "SUCCESS", 
                    'ErrorCode' => 0
                ),
                array(
                    'RefundID'  => 30149, 
                    'PaymentID' => $_GET['paymentID'],
                    'Status'    => "SUCCESS", 
                    'ErrorCode' => 0
                )
            )
        );
        break;
    default:
        $operation = array(
            'ErrorCode' => -13
        );
        break;
}

echo iconv("WINDOWS-1251", "UTF-8", json_encode($operation));
?>