<?php

ini_set('display_errors',1);
error_reporting(E_ALL ^ E_NOTICE);


ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

if(!isset($_SERVER['DOCUMENT_ROOT']) || !strlen($_SERVER['DOCUMENT_ROOT']))
{    
    $_SERVER['DOCUMENT_ROOT'] = rtrim(realpath(pathinfo(__FILE__, PATHINFO_DIRNAME) . '/../../'), '/');
} 


require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/DocGen/DocGenReserves.php");



try
{
    
$doc = new DocGenReserves(array(
    'id' => 777,
    'employer' => array('login' => 'employer33')
));
$doc->setField('text5_info', '1111111 2222222 33333333');
$doc->generateActServiceEmp();

}
catch(Exception $e)
{
    print_r(iconv('CP1251','UTF-8',$e->getMessage()));
}





/*
$data = array(
    'price' => array(
        'format' => 'price',
        'option' => '',
        'value' => 1000
     )
);
*/

/*
try
{
    
$doc = new DocGenReserves(array(
    'id' => 777,
    'employer' => array('login' => 'employer33')
));
$doc->setField('datetext_1', '01.08.2014');
$doc->generateActCompletedFrl();

}
catch(Exception $e)
{
    print_r(iconv('CP1251','UTF-8',$e->getMessage()));
}
 */

/*
$docFormatter = new DocGenReservesFormatter();
$fio = $docFormatter->fio(104);
print_r(iconv('CP1251','UTF-8',$fio));
print_r(PHP_EOL);*/

/*
try
{
$doc = new DocGenReserves(array(
    'id' => 777,
    'employer' => array('login' => 'employer33')
));
$doc->generateOffers();
}
catch(Exception $e)
{
    print_r(iconv('CP1251','UTF-8',$e->getMessage()));
}
 */


/*
try
{
    
   $doc = new DocGenReserves(array(
    'id' => 777,
    'employer' => array('login' => 'employer33')
   ));
   
   $doc->generateBankInvoice();
   
}
catch(Exception $e)
{
    print_r(iconv('CP1251','UTF-8',$e->getMessage()));
}
 */
