<?php define( 'IS_SITE_ADMIN', 1 );
$rpath = "../../";
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
	session_start();
	get_uid(false);
	if (!(hasPermissions('statsaccounts') || hasPermissions('tmppayments'))) { exit; }
$idMonth = date('m'); //дефолтный месяц
$idYear = date('Y'); //дефолтный год
$iBarWidth = (is_numeric(InGet('y')) && !is_numeric(InGet('m')))?30:20; //ширина ячейки
if (InGet('y') == 'all') {
    $iBarWidth = 50;
}
$iHeight = 20; //отступ снизу
$sFont = ABS_PATH.'/siteadmin/account/Aricyr.ttf';

// Максимальная высота отдельного блока
$blockMaxHeight = 200;

define('OP_SIZE',12);

error_reporting(E_ALL & ~E_NOTICE);

$DB = new DB('master');

function getOP($op, $date_from='2006-10-10', $date_to='now()', $bYear, $bYearAll = FALSE) {
    global $DB;
    if($op[0]==7||$op[0]==4||$op[0]==5) {
        $cond = " AND op_code=12 ";
    } else { 
        $cond = '';
    }
 	$_op = $op[0];
    
	$opcode = " op_code IN('".implode("','",$op)."') ";
	$op =  (is_array($op))? "payment_sys IN ('" . implode("','", $op) . "')" : "payment_sys = '$op'";
    $op .= $cond;
    
        $to_char = 'MM';
        if ($bYearAll) $to_char = 'YYYY';
    
	if ($bYear) {
		$sql = "SELECT SUM(round(ammount,2)) as ammount, to_char(op_date,'{$to_char}') as _day, payment_sys, count(*) as count_op FROM
			account_operations WHERE op_date >= '".$date_from."' AND op_date < '".$date_to."' AND ".$op."  GROUP BY to_char(op_date,'{$to_char}'), payment_sys ORDER BY to_char(op_date,'{$to_char}')";
	}
	else {
		$sql = "SELECT SUM(round(ammount,2)) as ammount, extract(day from op_date) as _day, payment_sys, count(*) as count_op FROM
			account_operations WHERE op_date >= '".$date_from."' AND op_date < '".$date_to."'::date+'1day'::interval AND ".$op." GROUP BY _day, payment_sys ORDER BY  _day";
	}

 /*
  * Безнал ДОК
  */
 if($_op == 4) {
  $dt_to = $bYear ? '' : "::date+'1day'::interval";
  $b_day = $bYear ? "to_char(accop.op_date,'{$to_char}')" : "extract(day from accop.op_date)";
  $b_day_grp = $bYear ? "to_char(accop.op_date,'{$to_char}')" : "_day";

  $op = array(4);
	 $op =  (is_array($op))? "accop.payment_sys IN ('" . implode("','", $op) . "') AND accop.op_code=12" : "accop.payment_sys = '$op' AND accop.op_code=12";
		$sql = "SELECT SUM(round(accop.ammount,2)) as ammount,
            {$b_day} as _day,
            999 as payment_sys,
            COUNTBOOL(rq.file_sf IS NOT NULL AND rq.file_act IS NOT NULL) as count_op FROM account_operations accop
          INNER JOIN reqv_ordered rq ON rq.billing_id = accop.id
          WHERE accop.op_date >= '".$date_from."'
            AND accop.op_date < '".$date_to."'{$dt_to}
            AND ".$op."
          GROUP BY {$b_day_grp}, accop.payment_sys ORDER BY  {$b_day_grp}";
	}


	//echo $sql.'<br>';
	$res = $DB->rows($sql);
	return $res;
}


$bYear = false;
$bYearAll = false;
if (is_numeric(InGet('y'))) {
	if (is_numeric(InGet('m'))) {
		$date_from = InGet('y').'-'.InGet('m').'-1';
		$date_to = InGet('y').'-'.InGet('m').'-'.date('t',mktime(0,0,0, InGet('m')+1, null, InGet('y')));

		$iMonth = InGet('m');
		$iYear = InGet('y');
	}
	else {
		$date_from = InGet('y').'-1-1';
		$date_to   = (InGet('y')+1).'-01-01';
		$bYear = true;
		$iMonth = $idMonth;
		$iYear = InGet('y');
	}
} elseif (InGet('y') == 'all') {
    $date_from = '2006-10-10';
    $date_to = date('Y-m-') . date('t');
    $bYearAll = $bYear = TRUE;
    $iMonth = date('m', strtotime($date_from));
    $iYear = date('Y', strtotime($date_from));
} else {
	$date_from = $idYear.'-'.$idMonth.'-1';
	$date_to = $idYear.'-'.$idMonth.'-'.date('t',mktime(0,0,0, $idMonth, 1, $idYear));
	$iMonth = $idMonth;
	$iYear = $idYear;
}




$iMaxDays = $iMax = ($bYear)?12:date('t',mktime(0,0,0, $iMonth, 1, $iYear)); //вычисление кол-ва дней в текущем месяце
if ($bYearAll) {
    $iMaxDays = $iMax = date('Y') - $iYear +1;
}
$iFMperPX = (!$bYear)?30:(30*30); //масштаб

for ($i=1; $i<= OP_SIZE + 1; $i++) {
	for ($j=0; $j<=$iMaxDays; $j++) {
		$graphValues[$i][$j] = 0;
        $graphCountOperations[$i][$j] = 0;
        $graphPercentOperations[$i][$j] = 0;
	}
}

$graphLabels = array();
for ($j = 1; $j <= $iMaxDays+1; $j++) {
    $graphLabels[] = $j;
}

if ($bYearAll) {
    $graphValues = $graphCountOperations = $graphPercentOperations = array();

    $graphLabels = array();
    for ($j = 0; $j <= $iMaxDays; $j++) {
        $graphLabels[] = ($iYear + $j);
    }
    
    for ($i = 1; $i<= OP_SIZE + 1; $i++) {
        foreach ($graphLabels as $j => $yr) {
            $graphValues[$i][$j] = 0;
            $graphCountOperations[$i][$j] = 0;
            $graphPercentOperations[$i][$j] = 0;
        }
    }
}


//// для теста
//foreach ($graphValues as $j => $row) {
//    foreach ($row as $i => $row1) {
//        $rr = 5350/ (count($row)-(count($row)-$i-1));
//        $graphValues[$j][$i] = rand($rr, 150)/$iFMperPX;
//    }
//}

$imgHeight = 0;
for ($i=1; $i<= OP_SIZE + 1; $i++) {
	$res = getOP(array($i), $date_from, $date_to, $bYear, $bYearAll);
	$aTemp = $res;
    if($i == 4) {
        $temp = $aTemp; // Сохраняем безнал для безнала Док
    }
	if (isset($aTemp[0]['payment_sys'])) {
		switch ($aTemp[0]['payment_sys']) {
			case 1:
				$l = 6; //WMZ
				$rate = EXCH_WMZ;
				break;
			case 2:
				$l = 4; //WMR - Ч
				$rate = EXCH_WMR;
				break;
			case 3:
				$l = 7; //ЯД
				$rate = EXCH_YM;
				break;
			case 4:
				$l = 3; //БН
				$rate = EXCH_TR;
				break;
			case 5:
				$l = 1; //CБ
				$rate = EXCH_CARD;
				break;
			case 6:
				$l = 8; //Кредитко
				$rate = EXCH_CARD;
				break;
			case 7:
				$l = 9; //СМС
				$rate = EXCH_SMS;
				break;
			case 8:
				$l = 10; //ОСМП
				$rate = EXCH_OSMP;
				break;
			case 9:
				$l = 11; //Киви
				$rate = EXCH_OSMP;
				break;
			case 10:
				$l = 5; //WMR - Б
				$rate = EXCH_WMR;
				break;
            case 11:
				$l = 12; //Альфа-банк
				$rate = EXCH_TR;
				break;
            case 13:
				$l = 13; //Веб-кошелек
				$rate = EXCH_WEBPAY;
				break;
			case 999:
				$l = 2; //БН док
				$rate = EXCH_TR;
				break;
		}
  
  if ($bYearAll) {
      foreach ($aTemp as $k => $v) {
          $aTemp[$k]['_day'] = array_search($v['_day'], $graphLabels)+1;
      }
  }

		for ($j=0; $j<count($aTemp); $j++) {
			$iAmount = $aTemp[$j]['ammount']/$iFMperPX;
            $graphValues[$l][$aTemp[$j]['_day']-1] = $iAmount;
            // Безнал. Док обрабатываем по другому
            if($l == 2 && $aTemp[$j]['count_op'] == 0) {
                $graphValues[$l][$aTemp[$j]['_day']-1] = 0.1;
            } else if($l == 2) {
                $iAmount = $temp[$j]['ammount']/$iFMperPX;
                $p = 1 / ( round($temp[$j]['count_op'] / $aTemp[$j]['count_op']) );
                $graphValues[$l][$aTemp[$j]['_day']-1] = $iAmount * $p;
            }
            $graphCountOperations[$l][$aTemp[$j]['_day']-1] = $aTemp[$j]['count_op'];
		}
	}
}

$k = 0; $graphStyle[0]['max'] = 0;
for ($i=0; $i<=$iMaxDays; $i++) {
	$iSumm = 0;
    $iCount = 0;

	for ($j=1; $j<=count($graphValues); $j++) {
  		if($j == 2) continue; // Пропускаем Безнал Док
		if (isset($graphValues[$j][$i])) {
		    $iSumm += $graphValues[$j][$i];
            $iCount += $graphCountOperations[$j][$i];
        }
	}
	//echo $iSumm.'<br>';
	$graphValues[0][$k] = $iSumm;
    $graphCountOperations[0][$k] = $iCount;
	if ($iSumm > $graphStyle[0]['max'])
	$graphStyle[0]['max'] = $iSumm;
	$k++;
}

// максимальное значение в строке
foreach ($graphValues as $row => $cols) {
    $graphStyle[$row]['max'] = $cols[0];
    foreach ($cols as $col) {
        if ($col > $graphStyle[$row]['max']) {
            $graphStyle[$row]['max'] = $col;
        }
    }
}

$tmp = array();
$mpl = 1;
foreach ($graphStyle as $k => $v) {
    $tmp[] = $v['max'];
}
if (count($tmp)) {
    rsort($tmp);
    $max_h = $tmp[0] == 0?1:$tmp[0];
    $mpl = $blockMaxHeight/$max_h;
}
$mpl = $mpl > 1 ? 1 : $mpl;

foreach ($graphStyle as $k => $v) {
    $graphStyle[$k]['max'] = $graphStyle[$k]['max'] * $mpl;
    $imgHeight += $graphStyle[$k]['max'];
}

$imgHeight += (OP_SIZE + 2)*40; //добавляем промежутки
$imgWidth = $iMax*$iBarWidth+100;


//print_r($graphValues);

$k = 0; $graphStyle[0]['max'] = 0;
//for ($i=0; $i<=$iMaxDays; $i++) {
//    $p = 100;
//	for ($j=1; $j<=count($graphValues); $j++) {
//     if($j == 2) continue; // Пропускаем Безнал Док
//        $graphPercent[$j][$i] = 0;
//		if (isset($graphValues[$j][$i]) && $graphValues[0][$k]!=0 && $graphValues[$j][$i]!=0) {
//            //echo round($graphValues[$j][$i]*$iFMperPX).'-'.$graphValues[0][$k].'<br>';
//            $graphPercent[$j][$i] = round(round($graphValues[$j][$i]*$iFMperPX)*100/round($graphValues[0][$k]*$iFMperPX));
//        }
//	}
//	$k++;
//}

//print_r($graphPercent);
//print $imgHeight;

$image=imagecreate($imgWidth, $imgHeight); //создается картинка, исходя из количества дней\месяцев, максимальных значений
imagecolorallocate($image, 255, 255, 255);


$graphStyle[0]['color'] = imagecolorallocate($image, 0, 0, 0); //сумма
$graphStyle[1]['color'] = imagecolorallocate($image, 140, 140, 140); //СБ
$graphStyle[2]['color'] = imagecolorallocate($image, 73, 146, 52); //безнал док
$graphStyle[3]['color'] = imagecolorallocate($image, 73, 146, 52); //безнал
$graphStyle[4]['color'] = imagecolorallocate($image, 40, 121, 159); //WMR
$graphStyle[5]['color'] = imagecolorallocate($image, 40, 121, 159); //WMR
$graphStyle[6]['color'] = imagecolorallocate($image, 40, 121, 159); //WMZ
$graphStyle[7]['color'] = imagecolorallocate($image, 204, 51, 51); //ЯД
$graphStyle[8]['color'] = imagecolorallocate($image, 27, 203, 209); //Кредитка
$graphStyle[9]['color'] = imagecolorallocate($image, 89, 94, 170); //СМС
$graphStyle[10]['color'] = imagecolorallocate($image, 209, 143, 27); //ОСМП
$graphStyle[11]['color'] = imagecolorallocate($image, 209, 143, 27); //Киви
$graphStyle[12]['color'] = imagecolorallocate($image, 255, 0, 0);  //Альфа-банк
$graphStyle[13]['color'] = imagecolorallocate($image, 100, 100, 100);  //Веб-кошелек

$graphStyle[0]['text'] 	= 'Сумма';
$graphStyle[1]['text'] 	= 'СБ';
$graphStyle[2]['text'] 	= 'Безнал док';
$graphStyle[3]['text'] 	= 'Безнал';
$graphStyle[4]['text'] 	= 'WMRч';
$graphStyle[5]['text'] 	= 'WMRб';
$graphStyle[6]['text'] 	= 'WMZ';
$graphStyle[7]['text'] 	= 'ЯД';
$graphStyle[8]['text'] 	= 'Кредитка';
$graphStyle[9]['text'] 	= 'СМС';
$graphStyle[10]['text'] = 'ОСМП';
$graphStyle[11]['text'] = 'Киви';
$graphStyle[12]['text'] = 'Альфа';
$graphStyle[13]['text'] = 'Веб-кошелек';

$colorWhite=imagecolorallocate($image, 255, 255, 255);
$colorGrey=imagecolorallocate($image, 192, 192, 192);
$colorDarkBlue=imagecolorallocate($image, 153, 153, 153);
$colorViolent=imagecolorallocate($image, 89, 94, 170);

for ($i=0; $i<count($graphValues); $i++) {
	//вычисляем откуда начать прорисовку графика
	if ($i) {
		$iMaxHeight = $graphValues[$i-1][0];
		for ($k=1; $k<count($graphValues[$i-1]); $k++) {
			$iMaxHeight = ($graphValues[$i-1][$k] > $iMaxHeight)?$graphValues[$i-1][$k]:$iMaxHeight;
		}
		$iHeight += $iMaxHeight*$mpl+35; // +15 - расстояние между строчками
	}

	for ($j=0; $j<count($graphValues[$i]); $j++) {

		imageline($image, $j*$iBarWidth+2 + 100, $imgHeight-$iHeight, $j*$iBarWidth+$iBarWidth + 100, $imgHeight-$iHeight, $colorGrey);
		if (!$i) {
			$iz = ($j+1 > 10)?3.7:2.5;
			imagefttext($image, '7', 0, $j*$iBarWidth+round($iBarWidth/$iz) + 100, $imgHeight - 5, $colorDarkBlue, $sFont, $graphLabels[$j]);
		}
		if ($graphValues[$i][$j]) {
			imagefilledrectangle($image, $j*$iBarWidth+2 + 100, ($imgHeight-$iHeight-$graphValues[$i][$j]*$mpl), ($j+1)*$iBarWidth + 100, $imgHeight-$iHeight, $graphStyle[$i]['color']);
			//надпись количества FM
			$color = (!$i)?$graphStyle[$i]['color']:$colorDarkBlue;
            if(isset($graphPercent[$i][$j])) {
                imagefttext($image, '7', 0, $j*$iBarWidth + 100+2, $imgHeight-$iHeight-$graphValues[$i][$j]-22, $color, $sFont, ($graphValues[$i][$j]*$iFMperPX)."\n".$graphCountOperations[$i][$j]."\n".$graphPercent[$i][$j].'%');
            } else {
                if($i == 2) { // вывод Значений БезналДок
                    imagefttext($image, '7', 0, $j*$iBarWidth + 100+2, $imgHeight-$iHeight-($graphValues[$i][$j]*$mpl)-12, $color, $sFont, "\n".$graphCountOperations[$i][$j]);
                } else {
                    imagefttext($image, '7', 0, $j*$iBarWidth + 100+2, $imgHeight-$iHeight-($graphValues[$i][$j]*$mpl)-12, $color, $sFont, ($graphValues[$i][$j]*$iFMperPX)."\n".$graphCountOperations[$i][$j]);
                }
            }
		}
	}
	$iFontSizeTitle = 8;
	$aBox = imageftbbox($iFontSizeTitle, 0, $sFont,$graphStyle[$i]['text']);
	$width = abs($aBox[0]) + abs($aBox[2]);
	imagefttext($image, $iFontSizeTitle, 0, 85-$width, $imgHeight-$iHeight, $graphStyle[$i]['color'], $sFont, $graphStyle[$i]['text']);
}

//надпись за какой промежуток показан график
$aMonthes[1] = 'январь';
$aMonthes[2] = 'февраль';
$aMonthes[3] = 'март';
$aMonthes[4] = 'апрель';
$aMonthes[5] = 'май';
$aMonthes[6] = 'июнь';
$aMonthes[7] = 'июль';
$aMonthes[8] = 'август';
$aMonthes[9] = 'сентябрь';
$aMonthes[10] = 'октябрь';
$aMonthes[11] = 'ноябрь';
$aMonthes[12] = 'декабрь';


$sString = 'Ввод валют';
imagefttext($image, '18', 0, 100, 40, $colorGrey, $sFont, $sString);

header("Pragma: no-cache");
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sun, 1 Jan 1995 01:00:00 GMT"); // Это какая-нибудь давно прошедшая дата
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // это строчка говорит, что наш скрипт всегда изменен
header("Content-type: image/png");
imagepng($image);
imagedestroy($image);

?>
