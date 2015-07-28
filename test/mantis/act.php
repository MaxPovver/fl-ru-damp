<?php

/**
 * Генератора акта выполненных работ
 */


//------------------------------------------------------------------------------


ini_set('display_errors',1);
error_reporting(E_ALL ^ E_NOTICE);


ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

if(!isset($_SERVER['DOCUMENT_ROOT']) || !strlen($_SERVER['DOCUMENT_ROOT']))
{    
    $_SERVER['DOCUMENT_ROOT'] = rtrim(realpath(pathinfo(__FILE__, PATHINFO_DIRNAME) . '/../../'), '/');
} 


require_once('act_config.php');

//------------------------------------------------------------------------------


$results = array();
if(count($argv) > 1) parse_str(implode('&', array_slice($argv, 1)), $_GET);


//------------------------------------------------------------------------------


if(empty($_GET))
{
    $help = '
Параметры:
h - Отработано часов
p - Ставка в час
d - За какой месяц в формате MM.YYYY (по умолчанию за текущий)      
n - Сколько последних проектов взять из мантиса
f - Id фильтра в мантисе
n1 - Нумер акта
n2 - Нумер приложения

Пример:
bash$ sudo php /fl/beta/test/mantis/act.php h=176 p=100 d=06.2014 n=15 f=222

';
    print_r(iconv('WINDOWS-1251', 'UTF-8',$help));
    exit;
}


//------------------------------------------------------------------------------
// Настройки
//------------------------------------------------------------------------------

//URL API Мантиса
define('MANTISCONNECT_URL', 'https://beta.free-lance.ru/mantis/api/soap/mantisconnect.php');

//Расположение шаблонов акта
define('TEMPLATES_PATH',realpath(pathinfo(__FILE__, PATHINFO_DIRNAME) . '/docs/') . '/');

define('ACT_NUM',isset($_GET['n1'])?intval($_GET['n1']):DEFAULT_ACT_NUM);
define('AFX_NUM',isset($_GET['n2'])?intval($_GET['n2']):DEFAULT_AFX_NUM);

//------------------------------------------------------------------------------


$opt = array(
    'login'    => BASIC_USERNAME,
    'password' => BASIC_PASSWORD,
     //'trace' => true
    //'keep_alive' => true,
    //'cache_wsdl' => WSDL_CACHE_NONE
    //'soap_version' => '1.2'
);

$args = array(
    'username' => MANTIS_USERNAME,
    'password' => MANTIS_PASSWORD,
    'project_id' => MANTIS_PROJECT_ID,
    'filter_id' => isset($_GET['f'])?intval($_GET['f']):MANTIS_FILTER_ID,
    'page_number' => 1,
    'per_page' => isset($_GET['n'])?intval($_GET['n']):30//Берем по больше чтобы захватить за нужный нам месяц
);


$date = isset($_GET['d'])?$_GET['d']:date('m.Y');
$date_from = '01.' . $date;
$date_from_time = strtotime($date_from);
$date_to = date('t.m.Y',  $date_from_time);
$date_to_time = strtotime($date_to);


try 
{
    //Получаем несколько последних тикетов по фильтру
    $client = new SoapClient(MANTISCONNECT_URL . '?wsdl', $opt);
    $issues = $client->__soapCall('mc_filter_get_issue_headers', $args);

    //Отсеиваем тикеты не за нужный нам месяц
    $issues = array_filter($issues,function($obj) use($date_from_time, $date_to_time){
        $last_update = strtotime($obj->last_updated);
        return !($last_update < $date_from_time || $last_update > $date_to_time);
    });
    
    //print_r($issues[count($issues)-1]);
    //exit;
    
    //По шаблону нам 15 хватит
    $issues = array_slice($issues, -17, 17);
    
    //print_r(array_keys($issues));
    //exit;
    
    
} 
catch (SoapFault $e) 
{
    $results['error'] = print_r($e->getMessage(),true);//$e->faultstring;
}



//------------------------------------------------------------------------------


require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/stdf.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/num_to_word.php');
//require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/odt2pdf.php');
require_once('ActOdt2Pdf.php');


if (!empty($issues)) 
{
    $total_hours = isset($_GET['h'])?intval($_GET['h']):DEFAULT_TOTAL_HOURS;
    $per_hour = isset($_GET['p'])?intval($_GET['p']):DEFAULT_PER_HOUR;
    
    $total_price = $per_hour * $total_hours;
    $total_price_format = number_format($total_price, 2, ',', '');
    $total_price_parts = explode(',', $total_price_format);
    
    $act_date = '&laquo;' . date( 'j', $date_to_time ) . '&raquo; ' . monthtostr( date('n', $date_to_time), true ) . ' ' . date( 'Y', $date_to_time ); 
    $affix_date_time = $date_from_time;//strtotime('+ 1 day', $date_to_time);
    $affix_date = '&laquo;' . date( 'j', $affix_date_time ) . '&raquo; ' . monthtostr( date('n', $affix_date_time), true ) . ' ' . date( 'Y', $affix_date_time );
    
    $date_dog_time = strtotime(DATE_DOG);
    $date_dog = date( 'j', $date_dog_time ) . ' ' . monthtostr( date('n', $date_dog_time), true ) . ' ' . date( 'Y', $date_dog_time ); 

    //Подстановка для акта
    $act_val = array(
        '$date_dog' => $date_dog,
        '$date_dog2' => DATE_DOG,
        '$worker_addr' => WORKER_ADDR_TXT,
        '$worker_fio' => WORKER_FIO_TXT,
        '$worker_bank' => WORKER_BANK_TXT,
        '$worker_sign' => WORKER_SIGN_TXT,
        '$num' => ACT_NUM,
        '$act_date' => $act_date,
        '$affix_num' => AFX_NUM,
        '$date_from' => $date_from,
        '$date_to' => $date_to,
        '$per_hour' => $per_hour . ' руб. 00 коп',
        '$total_hours' => $total_hours,
        '$total_price' => $total_price_format,
        '$total_price2' => $total_price_parts[0],
        '$total_price3' => $total_price_parts[1],
        '$total_price_txt' => trim(str_replace(array('рублей','рубля',' Российской Федерации','00 копеек'),array('','','',''),num2str($total_price_parts[0])))
    );

    //Подстановка для приложения
    $act_affix = array(
        '$num' => AFX_NUM,
        '$date_dog' => $date_dog,
        '$date_from' => $date_from,
        '$date_to' => $date_to,
        '$act_date' => $affix_date,//$act_date,
        '$worker_addr' => WORKER_ADDR_TXT,
        '$worker_fio' => WORKER_FIO_TXT,
        '$worker_bank' => WORKER_BANK_TXT,
        '$worker_sign' => WORKER_SIGN_TXT,
        '$services' => ''
    );
    
    
    $cnt = count($issues);
    $h = ($total_hours / $cnt);
    $ah = round($h);
    $lh = $total_hours - ($ah * ($cnt - 1));

    foreach($issues as $key => $issue)
    {
        $idx = $key + 1;
        
        $summary = iconv('UTF-8', 'WINDOWS-1251', $issue->summary);
        $act_val['$service' . $idx] = "#{$issue->id} {$summary}";
        $act_affix['$services'] .= $act_val['$service' . $idx] . (($idx < $cnt)? PHP_EOL . PHP_EOL : '');
        
        $hours = ($key < $cnt-1)?$ah:$lh;
        $act_val['$hours' . $idx] = $hours;
        
        $cost = ($per_hour * $hours);
        $act_val['$price' . $idx] = number_format($cost, 2, ',', '');
    }
    
    

    //--------------------------------------------------------------------------
    
    
    
    $pdf = new ActOdt2Pdf('act_template.odt');
    $pdf->setStopRemove(true);
    $pdf->setFolder(TEMPLATES_PATH);
    $pdf->convert($act_val);
    $content = $pdf->Output(NULL, 'S');
    
    $file = new CFile();
    $file->path = "uploader/{$date}/";
    $file->name = sprintf(iconv('WINDOWS-1251', 'UTF-8', ACT_FILENAME),ACT_NUM) . '.pdf'; //basename($file->secure_tmpname($file->path, '.pdf'));
    $file->size = strlen($content);
    $file->putContent($file->path . $file->name, $content);
    $results[$file->name] = $file->path . $file->name;
    
    $content = $pdf->getOdtContent();
    $file->name = sprintf(iconv('WINDOWS-1251', 'UTF-8', ACT_FILENAME),ACT_NUM) . '.odt';
    $file->size = strlen($content);
    $file->putContent($file->path . $file->name, $content);    
    
    $pdf->remove();
    unset($pdf);
    
    //--------------------------------------------------------------------------
    
    $pdf = new ActOdt2Pdf('affix_template.odt');
    $pdf->setStopRemove(true);
    $pdf->setFolder(TEMPLATES_PATH);
    $pdf->convert($act_affix);
    $content = $pdf->Output(NULL, 'S');
    
    $file = new CFile();
    $file->path = "uploader/{$date}/";
    $file->name = sprintf(iconv('WINDOWS-1251', 'UTF-8', AFFIX_FILENAME),AFX_NUM) . '.pdf';//basename($file->secure_tmpname($file->path, '.pdf'));
    $file->size = strlen($content);
    $file->putContent($file->path . $file->name, $content);  
    $results[$file->name] = $file->path . $file->name;
    
    $content = $pdf->getOdtContent();
    $file->name = sprintf(iconv('WINDOWS-1251', 'UTF-8', AFFIX_FILENAME),AFX_NUM) . '.odt';
    $file->size = strlen($content);
    $file->putContent($file->path . $file->name, $content);  
    
    $pdf->remove();
    unset($pdf);
}



//------------------------------------------------------------------------------



array_walk($results, function(&$value, $key){
    $value = sprintf('%s = %s'.PHP_EOL, $key, $value);
});

print_r(implode('', $results));

exit;