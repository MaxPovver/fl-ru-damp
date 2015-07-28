<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/siteadmin/reserves/models/ReservesAdminBankReportGeneratorModel.php');

$reservesAdminBankReportGeneratorModel = new ReservesAdminBankReportGeneratorModel();


$payouts = array(array(
    'frl_id' => 1,
    'email' => 'test@gmail.com',
    'path' => '/dir/',
    'fname' => 'file.pdf',
    'order_id' => 42,
    'frl_fio' => 'Леонид Агутин',
    'price' => 5000,
    'emp_fio' => 'Анжелика Варум'
));

$paybacks = array(array(
    'frl_id' => 1,
    'email' => 'tester@gmail.com',
    'path' => '/newdir/',
    'fname' => 'file2.pdf',
    'order_id' => 47,
    'frl_fio' => 'Леонид Агутин',
    'price' => 500,
    'emp_fio' => 'Анжелика Варум'
));

echo '<a href="'.WDCPREFIX . $reservesAdminBankReportGeneratorModel->generate2($payouts, $paybacks).'">Файл</a>';