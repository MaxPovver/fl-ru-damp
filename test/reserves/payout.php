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
        'decrypt_cert_path' => ABS_PATH . '/classes/reserves/data/depositresponsegenerator.cer',
        'private_key_path' => ABS_PATH . '/classes/reserves/data/private.key',
        'passphrase' => 'tkaevient2014'
    )
));

$clientOrderId = 1497;//1520;//1407;//1497;

//------------------------------------------------------------------------------



$depositionRequest = new DepositionRequest();


$depositionRequest->setDstAccount(25700130535186);
$depositionRequest->setAgentId(200385);
$depositionRequest->setAmount('261.00');
$depositionRequest->setCurrency(643);
$depositionRequest->setClientOrderId($clientOrderId);
$depositionRequest->setPofOfferAccepted(1);
$depositionRequest->setSmsPhoneNumber('+79272540217');


$depositionRequest->setSkrDestinationCardSynonim('df37dc6cdebbb0b8e181a069d6604c5a1f5b15e3_scn');


//$depositionRequest->setSkrDestinationCardSynonim('8d0790ea6807783f2572c5040a86dc777dfcfcee_scn');

//$depositionRequest->setSkrDestinationCardSynonim('8d0790ea6807783f2572c5040a86dc777dfcfcee_scn');

//$depositionRequest->setSkrDestinationCardSynonim('f209339c321e27ba9fc1bb72f6686bdb7ea5aedc_scn');
//$depositionRequest->setSkrDestinationCardSynonim('df37dc6cdebbb0b8e181a069d6604c5a1f5b15e3_scn');
//$depositionRequest->setSkrDestinationCardSynonim('d5f75104004fde13364637baf911073c37478b0c_scn');
//$depositionRequest->setSkrDestinationCardSynonim('246a784938d65740aa6cd175a179121c0cdef707_scn');


/*
$depositionRequest->setBankName('Отделение N8624 Сбербанка России');
$depositionRequest->setBankCity(' г. Пенза');
$depositionRequest->setBankBIK('045655635');
$depositionRequest->setBankCorAccount('30101810000000000635');
$depositionRequest->setBankKPP('583402001');
$depositionRequest->setRubAccount('40817810148005008823');
$depositionRequest->setTmpFirstName('Сергей');
$depositionRequest->setTmpMiddleName('Дмитриевич');
$depositionRequest->setTmpLastName('Сурков');
*/


//$result = $this->apiFacade->testDeposition($depositionRequest);

try 
{
    //$result = $apiFacade->testDeposition($depositionRequest);
    
    $result = $apiFacade->makeDeposition($depositionRequest);
} 
catch (Exception $e) 
{
    $results['test_1 Error Message'] = $e->getMessage();
}   

$results['test_1 isSuccess'] = (int)$result->isSuccess();
$results['test_1 getStatus'] = $result->getStatus();
$results['test_1 getError'] = $result->getError();
$results['test_1 getDefinedParams'] = print_r($result->getDefinedParams(),true);


//print_r($depositionRequest->getDefinedParams());
//exit;




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