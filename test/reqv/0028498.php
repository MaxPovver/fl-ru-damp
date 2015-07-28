<?php

##0028498

ini_set('display_errors',1);
error_reporting(E_ALL ^ E_NOTICE);


ini_set('max_execution_time', 0);
ini_set('memory_limit', '512M');

if(!isset($_SERVER['DOCUMENT_ROOT']) || !strlen($_SERVER['DOCUMENT_ROOT']))
{    
    $_SERVER['DOCUMENT_ROOT'] = rtrim(realpath(pathinfo(__FILE__, PATHINFO_DIRNAME) . '/../../'), '/');
}


require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

ini_set("auto_detect_line_endings", true);
$content = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/temp/ndfl-23.12.14.csv');
//$content = iconv('cp866', 'windows-1251', $content);

$data = array_map(function($value){
    //return str_getcsv($value,';','');
    return explode(';', $value);
}, explode("\n", $content));

/*
В аттаче приложен файл с исправленными данными по контрагентам.
Нужно данные из него внести в базу по каждому пользователю.
А именно - по логину найти пользователя и прописать ему на сайте:
- Имя
- Фамилию
- Отчество
- Серию и номер паспорта
- Дату выдачи паспорта
- Кем выдан паспорт
- Адрес регистрации/прописки
- ИНН компании
- Дату рождения
Данные взять из соответствующих полей файла.
 */

$idx = 0;
$part = 0;
foreach ($data as $key => $el) {
    if(!$key) {
        continue;
    }

    $form_type = $el[6] == 1 ? 2 : 1;
    
    $name = trim($el[4]) . ' ' . trim($el[2]) . ' ' . trim($el[3]);
    $name = trim($name);
    
    $dpa = explode('.', $el[9]);
    $date_pass = trim($dpa[2]).'-'.$dpa[1].'-'.$dpa[0];

    $dpb = explode('.', $el[13]);
    $date_birth = trim($dpb[2]).'-'.trim($dpb[1]).'-'.trim($dpb[0]);
    
    $_sql .= "UPDATE sbr_reqv SET "
       .(!empty($name)?"_{$form_type}_fio = '{$name}', ":"")
       ."_1_idcard_ser = '{$el[7]}', "
       ."_1_idcard = '{$el[8]}', "
       ."_1_idcard_from = '{$date_pass}', "
       ."_1_idcard_by = '{$el[10]}', "
       .($form_type == 1 ? '_1_address_reg' : '_2_address_jry')." = '{$el[11]}', "
       .($form_type == 1 ? '' : ($el[5] == 'Россия' ? "_2_inn = '{$el[12]}', " : "_2_rnn = '{$el[12]}', "))
       ."_{$form_type}_birthday = '{$date_birth}' "
       ."WHERE user_id = (SELECT uid FROM users WHERE login = '{$el[1]}');\n";

    if ($idx > 1000) {
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/temp/0028498-{$part}.sql", $_sql);
        $part++;
        $idx = 0;
        $_sql = '';
    }  
    
    $idx++;
}





exit;