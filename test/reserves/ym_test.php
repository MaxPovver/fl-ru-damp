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
//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/config.php");
//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/profiler.php");
//require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/billing.php");
//require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reqv.php');
//require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/sbr_meta.php');
//require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/reserves/ReservesModelFactory.php');
//require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/YandexMoney3/Array2XML.php');



require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/YandexMoney3/YandexMoney3.php');



//------------------------------------------------------------------------------


$results = array();
//$profiler = new profiler();


//------------------------------------------------------------------------------

use YandexMoney3\Request\DepositionRequest;
use YandexMoney3\Request\BalanceRequest;
use YandexMoney3\YandexMoney3;


//------------------------------------------------------------------------------

$apiFacade = YandexMoney3::getApiFacade();
$apiFacade->setOptions(array(
    'crypt' => array(
        'encrypt_cert_path' => ABS_PATH . '/classes/reserves/data/certnew_Vaan.cer',
        'decrypt_cert_path' => ABS_PATH . '/classes/reserves/data/deposit.cer',
        'private_key_path' => ABS_PATH . '/classes/reserves/data/private.key',
        'passphrase' => 'tkaevient2014'
    )
));

$clientOrderId = rand(700,800);

//------------------------------------------------------------------------------



//------------------------------------------------------------------------------


$results['test_8'] = iconv('CP1251', 'UTF-8', '
--------------------------------------------------------------------------------    
8. 410039303807 - неверная контрольная сумма.
');

$depositionRequest = new DepositionRequest();
$depositionRequest->setAgentId(200385);
//$depositionRequest->setAmount(number_format($orderData['reserve_data']['reserve_price'], 2, '.', ''));
$depositionRequest->setAmount('1.00');
$depositionRequest->setCurrency(10643);
$depositionRequest->setClientOrderId($clientOrderId);
$depositionRequest->setSmsPhoneNumber('+79272540217');
$depositionRequest->setPofOfferAccepted(1);

$depositionRequest->setDstAccount(410039303807);

try 
{
    $result = $apiFacade->makeDeposition($depositionRequest);
} 
catch (\Exception $e) 
{
    $results['test_8 Error Message'] = $e->getMessage();
}    

if($result)
{
    $results['test_8 isSuccess'] = $result->isSuccess();
    $results['test_8 getDefinedParams'] = print_r($result->getDefinedParams(),true);
}



//------------------------------------------------------------------------------


/*
$results['test_7'] = iconv('CP1251', 'UTF-8', '
--------------------------------------------------------------------------------    
7. 410039303350 - счет заблокирован (зачисления на счет запрещены)
');

$depositionRequest = new DepositionRequest();
$depositionRequest->setAgentId(200385);
//$depositionRequest->setAmount(number_format($orderData['reserve_data']['reserve_price'], 2, '.', ''));
$depositionRequest->setAmount('1.00');
$depositionRequest->setCurrency(10643);
$depositionRequest->setClientOrderId($clientOrderId);
$depositionRequest->setSmsPhoneNumber('+79272540217');
$depositionRequest->setPofOfferAccepted(1);

$depositionRequest->setDstAccount(410039303350);

try 
{
    $result = $apiFacade->testDeposition($depositionRequest);
} 
catch (\Exception $e) 
{
    $results['test_7 Error Message'] = $e->getMessage();
}    

if($result)
{
    $results['test_7 isSuccess'] = $result->isSuccess();
    $results['test_7 getDefinedParams'] = print_r($result->getDefinedParams(),true);
}
*/




//------------------------------------------------------------------------------



/*
//1. Запрос о возможности зачисления.
$results['test_1'] = iconv('CP1251', 'UTF-8', '
--------------------------------------------------------------------------------    
1. Запрос о возможности зачисления.
');

$depositionRequest = new DepositionRequest();
$depositionRequest->setAgentId(200385);
//$depositionRequest->setAmount(number_format($orderData['reserve_data']['reserve_price'], 2, '.', ''));
$depositionRequest->setAmount('500.00');
$depositionRequest->setCurrency(10643);
$depositionRequest->setClientOrderId($clientOrderId);
$depositionRequest->setSmsPhoneNumber('+79272540217');
$depositionRequest->setPofOfferAccepted(1);

$depositionRequest->setDstAccount(4100311441902);

try 
{
    $result = $apiFacade->testDeposition($depositionRequest);
} 
catch (\Exception $e) 
{
    $results['test_1 Error Message'] = $e->getMessage();
}    

$results['test_1 isSuccess'] = $result->isSuccess();
$results['test_1 getDefinedParams'] = print_r($result->getDefinedParams(),true);
*/

//------------------------------------------------------------------------------

/*
//2. Операцию зачисления.
$results['test_2'] = iconv('CP1251', 'UTF-8', '
--------------------------------------------------------------------------------
2. Операцию зачисления.
');


try 
{
    $result = $apiFacade->makeDeposition($depositionRequest);
} 
catch (\Exception $e) 
{
    $results['test_2 Error Message'] = $e->getMessage();
}    

$results['test_2 isSuccess'] = $result->isSuccess();
$results['test_2 getDefinedParams'] = print_r($result->getDefinedParams(),true);
*/



//------------------------------------------------------------------------------

/*
//3. Повтор зачисления.
$results['test_3'] = iconv('CP1251', 'UTF-8', '
--------------------------------------------------------------------------------    
3. Повтор зачисления.
');


try 
{
    $result = $apiFacade->testDeposition($depositionRequest);
} 
catch (\Exception $e) 
{
    $results['test_3 Error Message'] = $e->getMessage();
}    

$results['test_3 isSuccess'] = $result->isSuccess();
$results['test_3 getDefinedParams'] = print_r($result->getDefinedParams(),true);
*/

//------------------------------------------------------------------------------

/*
//4. Запрос о результате операции.
$results['test_4'] = iconv('CP1251', 'UTF-8', '
--------------------------------------------------------------------------------
4. Запрос о результате операции.
');


$balanceRequest = new BalanceRequest();
$balanceRequest->setAgentId(200385);
$balanceRequest->setClientOrderId($clientOrderId);

try 
{
    $result = $apiFacade->balance($balanceRequest);
} 
catch (\Exception $e) 
{
    $results['test_4 Error Message'] = $e->getMessage();
}    

$results['test_4 isSuccess'] = $result->isSuccess();
$results['test_4 getDefinedParams'] = print_r($result->getDefinedParams(),true);
*/


//------------------------------------------------------------------------------

//5. Тестирование ситуации, когда превышен разовый лимит (сумма разового зачисления должна быть больше 1000).
/*
$results['test_5'] = iconv('CP1251', 'UTF-8', '
--------------------------------------------------------------------------------
5. Тестирование ситуации, когда превышен разовый лимит (сумма разового зачисления должна быть больше 1000).
');

$depositionRequest->setClientOrderId($clientOrderId * 100);
$depositionRequest->setAmount('1001.00');

try 
{
    $result = $apiFacade->testDeposition($depositionRequest);
} 
catch (\Exception $e) 
{
    $results['test_5 Error Message'] = $e->getMessage();
}    

$results['test_5 isSuccess'] = $result->isSuccess();
$results['test_5 getDefinedParams'] = print_r($result->getDefinedParams(),true);
*/

//------------------------------------------------------------------------------

/*
$results['test_6'] = iconv('CP1251', 'UTF-8', '
--------------------------------------------------------------------------------
6. Тестирование ситуации, когда превышен лимит зачислений за период времени 
(для этого должно быть несколько зачислений, на каждое из которых <1000, но суммарно более чем на 5000).    
');

for($i=0; $i<6; $i++)
{
    $depositionRequest->setClientOrderId(($clientOrderId * 100) + $i);
    $depositionRequest->setAmount('900.00');

    try 
    {
        $result = $apiFacade->testDeposition($depositionRequest);
        
        if(!$result->isSuccess()) break;

        $result = $apiFacade->makeDeposition($depositionRequest);
    } 
    catch (\Exception $e) 
    {
        $results["test_6_{$i} Error Message"] = $e->getMessage();
    }    

    $results["test_6_{$i} isSuccess"] = $result->isSuccess();
    $results["test_6_{$i} getDefinedParams"] = print_r($result->getDefinedParams(),true);
}
*/


//------------------------------------------------------------------------------

//$profiler->start('fill_frl_mem');

//------------------------------------------------------------------------------




//------------------------------------------------------------------------------

//$profiler->stop('fill_frl_mem');

//------------------------------------------------------------------------------


//------------------------------------------------------------------------------

array_walk($results, function(&$value, $key){
    $value = sprintf('%s = %s'.PHP_EOL, $key, $value);
});

print_r(implode('', $results));

exit;