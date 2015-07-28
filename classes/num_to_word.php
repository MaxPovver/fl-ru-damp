<?php 
/**
 * Файл используется для того чтобы выдавать число в строковом виде (для оплаты банковским переводом)
 */
$GLOBALS['_1_2']=array(1=>
"одна ", 
"две ");  
$GLOBALS['_1_19']=array(1=>
"один ",
"два ",
"три ",
"четыре ",
"пять ",
"шесть ",
"семь ",
"восемь ",
"девять ",
"десять ",
"одиннадцать ",
"двенадцать ",
"тринадцать ",
"четырнадцать ",
"пятнадцать ",
"шестнадцать ",
"семнадцать ",
"восемнадцать ",
"девятнадцать ");

$GLOBALS['des']=array(2=>
"двадцать ",
"тридцать ",
"сорок ",
"пятьдесят ",
"шестьдесят ",
"семьдесят ",
"восемьдесят ",
"девяносто ");

$GLOBALS['hang']=array(1=>
"сто ",
"двести ",
"триста ",
"четыреста ",
"пятьсот ",
"шестьсот ",
"семьсот ",
"восемьсот ",
"девятьсот ");

$GLOBALS['namerub']=array(1=>
"рубль Российской Федерации ",
"рубля Российской Федерации ",
"рублей Российской Федерации ");

$GLOBALS['nametho']=array(1=>
"тысяча ",
"тысячи ",
"тысяч ");

$GLOBALS['namemil']=array(1=>
"миллион ",
"миллиона ",
"миллионов ");

$GLOBALS['namemrd']=array(1=>
"миллиард ",
"миллиарда ",
"миллиардов ");

$GLOBALS['kopeek']=array(1=>
"копейка ",
"копейки ",
"копеек ");

$GLOBALS['num_name'] = array(
    1 => 'одному',
    2 => 'двум',
    3 => 'трем',
    4 => 'четырем',
    5 => 'пяти',
    6 => 'шести',
    7 => 'семи',
    8 => 'восьми',
    9 => 'девяти',
    10 => 'десяти',
    11 => 'одинадцати',
    12 => 'двенадцати',
    13 => 'тринадцати',
    14 => 'четырнадцати',
    15 => 'пятнадцати',
    16 => 'шестнадцати',
    17 => 'семнадцати',
    18 => 'восемнадцати',
    19 => 'девятнадцати',
    20 => 'двадцати'
);
/**
 * Функция формирующая число в строкой форме
 *
 * @todo Вид кода ппц, или так и надо?
 * 
 * @global $_l_2    Массив данных по названию чисел
 * @global $_l_19   Массив данных по названию чисел
 * @global $des     Массив данных по названию чисел
 * @global $hang    Массив данных по названию чисел
 * @global $namerub Массив данных по написанию валюты
 * @global $nametho Массив данных по названию чисел
 * @global $namemil Массив данных по названию чисел
 * @global $namemrd Массив данных по названию чисел  
 * 
 * @param integer $i     Число
 * @param string  $words Возвращает словообразование от числа  
 * @param integer $fem   Индекс для окончания. Пример: 1 - тысяча, 2 - тысячи, 3 - тысяч
 * @param integer $f     индекс для рода для "один" и "два".
 */
function semantic($i,&$words,&$fem,$f){  
global $_1_2, $_1_19, $des, $hang, $namerub, $nametho, $namemil, $namemrd;  
$words="";  
if($i >= 100){  
$jkl = intval($i / 100);  
$words.=$hang[$jkl];  
$i%=100;  
}  
if($i >= 20){  
$jkl = intval($i / 10);  
$words.=$des[$jkl];  
$i%=10;
}  
switch($i){  
case 1: $fem=1; break;  
case 2:  
case 3:  
case 4: $fem=2; break;  
default: $fem=3; break;  
}  
if( $i ){  
if( $i < 3 && $f > 0 ){  
if ( $f >= 2 ) {  
$words.=$_1_19[$i];  
}  
else {  
$words.=$_1_2[$i];  
}  
}  
else {  
$words.=$_1_19[$i];  
}  
}  
}  

/**
 * Перевод значения числа в строковое значение 
 * 
 * @example num2str(1234) -> "Тысяча двести тридцать четыре"
 *
 * @global $_l_2    Массив данных по названию чисел
 * @global $_l_19   Массив данных по названию чисел
 * @global $des     Массив данных по названию чисел
 * @global $hang    Массив данных по названию чисел
 * @global $namerub Массив данных по написанию валюты (например рубли, доллары)
 * @global $nametho Массив данных по названию чисел
 * @global $namemil Массив данных по названию чисел
 * @global $namemrd Массив данных по названию чисел 
 * @global $kopeek  Массив данных по написанию мелкой валюты (например копеек, центов)
 * 
 * @param integer $L преобразуемое число
 * @return string Слообразование
 */
function num2str($L, $up = false){  
global $_1_2, $_1_19, $des, $hang, $namerub, $nametho, $namemil, $namemrd, $kopeek;  

$s=" ";  
$s1=" ";  
$s2=" ";
$L = round($L, 2);
$kop = 100*(string)round($L-(int)$L, 2);
$L=intval($L);  
if($L>=1000000000){  
$many=0;  
semantic(intval($L / 1000000000),$s1,$many,3);  
$s.=$s1.$namemrd[$many];  
$L%=1000000000;  
}  

if($L >= 1000000){  
$many=0;  
semantic(intval($L / 1000000),$s1,$many,2);  
$s.=$s1.$namemil[$many];  
$L%=1000000;  
if($L==0){  
$s.="рублей Российской Федерации";  
}  
}  

if($L >= 1000){  
$many=0;  
semantic(intval($L / 1000),$s1,$many,1);  
$s.=$s1.$nametho[$many];  
$L%=1000;  
if($L==0){  
$s.="рублей Российской Федерации";  
}  
}  

if($L != 0){  
$many=0;  
semantic($L,$s1,$many,0);  
$s.=$s1.$namerub[$many];  
}  

if($kop > 0){  
$many=0;  
semantic($kop,$s1,$many,1);  
$s.=$s1.$kopeek[$many];  
}  
else {  
$s.=" 00 копеек";  
}  
if($up) {
    $s = trim($s);
    $s{0} = mb_strtoupper($s{0});
}
return trim($s);  
}  

/**
 * Форматирует число в цену в рублях и копейках
 * 
 * @param  float $sum число
 * @return string
 */
function num2strL($sum) {
   $sum = round($sum,2);
   $rub = (int)$sum;
   $pad = 100*round($sum-$rub, 2);
   $kop = str_pad($pad, 2, '0', $pad < 10 ? STR_PAD_LEFT : STR_PAD_RIGHT);
   return $rub.ending($rub, ' рубль', ' рубля', ' рублей').' '.$kop.ending($kop, ' копейка', ' копейки', ' копеек');
}

/**
 * Форматирует число в цену в рублях и копейках в формате РУБЛИ-КОПЕЙКИ
 * 
 * @param  float $sum число
 * @return string
 */
function num2strD($sum) {
   $sum = round($sum,2);
   $rub = (int)$sum;
   $pad = 100*round($sum-$rub, 2);
   $kop = str_pad($pad, 2, '0', $pad < 10 ? STR_PAD_LEFT : STR_PAD_RIGHT);
   return $rub.'-'.$kop;
}

/**
 * Форматирует число в цену в рублях и копейках
 * 
 * @param  float $L число
 * @return string
 */
function num2strEx($L) {
    include_once dirname(__FILE__).'/sbr.php';
    global $_1_2, $_1_19, $des, $hang, $namerub, $nametho, $namemil, $namemrd, $kopeek;
    
    $L = round($L,2);
    $source = $L;
    $kop = 100*(string)round($L-(int)$L, 2);
    $L = intval($L);
    
    $s = " ";
    $s1 = " ";
    $s2 = " ";
    if($L == 0){
        $s.= 'ноль рублей Российской Федерации ';
    }
    
    if ($L >= 1000000000) {
        $many = 0;
        semantic(intval($L / 1000000000), $s1, $many, 3);
        $s.=$s1 . $namemrd[$many];
        $L%=1000000000;
    }

    if ($L >= 1000000) {
        $many = 0;
        semantic(intval($L / 1000000), $s1, $many, 2);
        $s.=$s1 . $namemil[$many];
        $L%=1000000;
        if ($L == 0) {
            $s = rtrim($s)." рублей Российской Федерации ";
        }
    }

    if ($L >= 1000) {
        $many = 0;
        semantic(intval($L / 1000), $s1, $many, 1);
        $s.=$s1 . $nametho[$many];
        $L%=1000;
        if ($L == 0) {
            $s = rtrim($s)." рублей Российской Федерации ";
        }
    }

    if ($L != 0) {
        $many = 0;
        semantic($L, $s1, $many, 0);
        $s .= rtrim($s1). ' ' . trim($namerub[$many]).' ';
    }
    
    if ($kop > 0) {
        $s .= str_pad($kop, 2, '0', STR_PAD_LEFT).ending($kop, ' копейка', ' копейки', ' копеек');
    } else {
        $s .= "00 копеек";
    }
    setlocale(LC_ALL, "ru_RU.CP1251"); 
    $s = ucfirst(trim($s));
    setlocale(LC_ALL, "en_US.UTF-8"); 
    return trim(sbr_meta::view_cost((float)$source, NULL, false, ',', ' ').' ('.trim($s).')');
}

function numStringName($num) {
    global $num_name;
    
    return $num_name[$num];
}
?>