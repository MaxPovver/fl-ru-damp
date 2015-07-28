<?
$rpath = "../../";
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
	session_start();
	get_uid(false);
if (!(hasPermissions('adm') && (hasPermissions('stats') || hasPermissions('tmppayments') ) )) { exit; }
$idMonth = date('m'); //дефолтный месяц
$idYear = date('Y'); //дефотлный год
$iBarWidth = (is_numeric(InGet('y')) && !is_numeric(InGet('m')))?30:20; //ширина ячейки
$iHeight = 20; //отступ снизу
$sFont = ABS_PATH.'/siteadmin/account/Aricyr.ttf';
$graphStyle = array();
$DB = new DB('master');

function getOP($op, $date_from='2006-10-10', $date_to='now()', $bYear=false, $addit="") {
    global $DB;
    if($op[0]==23) { $cond = " AND ammount>=0 "; } else { $cond = ""; }
    if(in_array($op[0],array('p0','p1','p2','p3'))) { $is_prj_addit = true; $prj_op = str_replace('p','',$op[0]); }
	if ($addit) $addit = " AND ". $addit;
	$op =  (is_array($op))? "op_code IN ('" . implode("','", $op) . "')" : "op_code = '$op'";

	if ($bYear) {
        if($is_prj_addit) {
    		$sql = "SELECT floor(SUM(p.ammount)) as sum, COUNT(p.*) as ammount, to_char(ac.op_date,'MM') as _day FROM
    			projects_payments as p INNER JOIN account_operations as ac ON p.opid=ac.id WHERE ac.op_date >= '".$date_from."' AND ac.op_date < '".$date_to."' AND pay_type=".$prj_op."  GROUP BY to_char(ac.op_date,'MM') ORDER BY to_char(ac.op_date,'MM')";
        } else {
    		$sql = "SELECT floor(SUM(ammount)) as sum, COUNT(*) as ammount, to_char(op_date,'MM') as _day FROM
    			account_operations INNER JOIN account ON account.id=account_operations.billing_id INNER JOIN users ON users.uid=account.uid WHERE op_date >= '".$date_from."' AND op_date < '".$date_to."' AND ".$op.$cond.$addit."  GROUP BY to_char(op_date,'MM') ORDER BY to_char(op_date,'MM')";
        }
	}
	else {
        if($is_prj_addit) {
    		$sql = "SELECT floor(SUM(p.ammount)) as sum, COUNT(p.*) as ammount, extract(day from ac.op_date) as _day FROM
    			projects_payments as p INNER JOIN account_operations as ac ON p.opid=ac.id WHERE ac.op_date >= '".$date_from."' AND ac.op_date < '".$date_to."'::date+'1day'::interval  AND pay_type=".$prj_op." GROUP BY _day ORDER BY  _day";
        } else {
    		$sql = "SELECT floor(SUM(ammount)) as sum, COUNT(*) as ammount, extract(day from op_date) as _day FROM
    			account_operations INNER JOIN account ON account.id=account_operations.billing_id INNER JOIN users ON users.uid=account.uid WHERE op_date >= '".$date_from."' AND op_date < '".$date_to."'::date+'1day'::interval  AND ".$op.$cond.$addit." GROUP BY _day ORDER BY  _day";
        }
	}
	//echo $sql.'<br>';
	$res = $DB->rows($sql);
	return $res;
}

$bYear = false;
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
}
else {
	//echo $idMonth.'<br>';
	//echo date('t',mktime(0,0,0, intval($idMonth), 1, intval($idYear)));
	$date_from = $idYear.'-'.$idMonth.'-1';
	$date_to = $idYear.'-'.$idMonth.'-'.date('t',mktime(0,0,0, intval($idMonth), 1, intval($idYear)));
	$iMonth = $idMonth;
	$iYear = $idYear;
}

$iMaxDays = $iMax = ($bYear)?12:date('t',mktime(0,0,0, $iMonth, 1, $iYear)); //Вычисление максимального количества дней\месяцев в текущем месяце\годе
$iFMperPX = (!$bYear)?30:(30*10); //масштаб

for ($i=1; $i<=19; $i++) {
	for ($j=0; $j<=$iMaxDays; $j++) {
		$graphValues[$i][$j] = 0;
		$graphValues2[$i][$j] = 0;
	}
}

$graphStyle[1]['op_codes'] 	= array(23);
$graphStyle[2]['op_codes'] 	= array(7);
$graphStyle[3]['op_codes'] 	= array(9);
$graphStyle[4]['op_codes'] 	= array(21);
$graphStyle[5]['op_codes'] 	= array(20);
$graphStyle[6]['op_codes'] 	= array(19);
$graphStyle[7]['op_codes'] 	= array(10,11);
$graphStyle[8]['op_codes'] 	= array(16,17,18,34,35);
$graphStyle[9]['op_codes']  = array('p0');
$graphStyle[10]['op_codes']  = array('p1');
$graphStyle[11]['op_codes']  = array('p2');
$graphStyle[12]['op_codes']  = array('p3');
$graphStyle[13]['op_codes']  = array(8,53,54);
$graphStyle[14]['op_codes'] = array(15);
$graphStyle[15]['op_codes'] = array(1,2,3,4,5,6,15,48,49,50,51);
$graphStyle[16]['op_codes'] = array(47);
$graphStyle[17]['op_codes'] = array(61,62);
$graphStyle[18]['op_codes'] = array(55,65,69);
$graphStyle[19]['op_codes'] = array(70);

$graphStyle[1]['addit'] = '';
$graphStyle[2]['addit'] = '';
$graphStyle[3]['addit'] = '';
$graphStyle[4]['addit'] = '';
$graphStyle[5]['addit'] = '';
$graphStyle[6]['addit'] = '';
$graphStyle[7]['addit'] = '';
$graphStyle[8]['addit'] = '';
$graphStyle[9]['addit'] = '';
$graphStyle[10]['addit'] = '';
$graphStyle[11]['addit'] = '';
$graphStyle[12]['addit'] = '';
$graphStyle[13]['addit'] = '';
$graphStyle[14]['addit'] = "role&'".$empmask."' = '".$empmask."'";
$graphStyle[15]['addit'] = "role&'".$empmask."' = '".$frlmask."'";
$graphStyle[16]['addit'] = '';
$graphStyle[17]['addit'] = '';
$graphStyle[18]['addit'] = '';
$graphStyle[19]['addit'] = '';

$imgHeight = 0;
for ($i=1; $i<=count($graphStyle); $i++) {
	$res = getOP($graphStyle[$i]['op_codes'], $date_from, $date_to, $bYear, $graphStyle[$i]['addit']);
	$aTemp = $res;

	if (isset($aTemp[0]['_day'])) {

		$graphStyle[$i]['max'] = abs($aTemp[0]['sum']/$iFMperPX);
		for ($j=0; $j<count($aTemp); $j++) {
			$iAmount = abs($aTemp[$j]['sum']/$iFMperPX);
			if ($iAmount > $graphStyle[$i]['max']) {
				$graphStyle[$i]['max'] = $iAmount; //Вычисляем максимальную высоту всего графика
			}

            $graphValues[$i][$aTemp[$j]['_day']-1] = $iAmount;
			$graphValues2[$i][$aTemp[$j]['_day']-1] = $aTemp[$j]['ammount'];
            if($aTemp[$j]['sum']<0) {
			    $graphValues3[$i][$aTemp[$j]['_day']-1] += -1*$aTemp[$j]['sum'];
            } else {
			    $graphValues3[$i][$aTemp[$j]['_day']-1] += $aTemp[$j]['sum'];
            }
		}
		$imgHeight += $graphStyle[$i]['max'];
	}
}
//echo '<pre>'; print_r($graphValues3); echo '</pre>';
$k = 0; $graphStyle[0]['max'] = 0;
for ($i=0; $i<=$iMaxDays; $i++) {
	$iSumm = 0; $iSumm2 = 0;
	for ($j=1; $j<count($graphValues); $j++) {
		if (isset($graphValues[$j][$i]) && !in_array($graphStyle[$j]['op_codes'][0],array('p0','p1','p2','p3'))) {
			$iSumm += $graphValues[$j][$i];
		}

		if (isset($graphValues2[$j][$i])) {
			$iSumm2 += $graphValues2[$j][$i];
		}
	}

	for ($j=1; $j<count($graphValues2); $j++) {
		if (isset($graphValues2[$j][$i])) {
			$iSumm2 += $graphValues2[$j][$i];
		}
	}

	$graphValues[0][$k] = $iSumm;
	$graphValues2[0][$k] = $iSumm*$iFMperPX;
	if ($iSumm > $graphStyle[0]['max'])
	$graphStyle[0]['max'] = $iSumm;
	$k++;
}
//print_r($graphValues2);
$imgHeight += $graphStyle[0]['max'] + count($graphValues)*30; //прибавляем промежутки к максимальной высоте графика
$imgWidth = $iMax*$iBarWidth+100;


$image=imagecreate($imgWidth, $imgHeight); //создаем график с учетом максимальной высоты и ширины.
imagecolorallocate($image, 255, 255, 255);

$graphStyle[0]['color'] = imagecolorallocate($image, 0, 0, 0); //Сумма
$graphStyle[1]['color'] = imagecolorallocate($image, 103, 135, 179); //Перевели денег
$graphStyle[2]['color'] = imagecolorallocate($image, 111, 177, 92); //Подняли проект
$graphStyle[3]['color'] = imagecolorallocate($image, 111, 177, 92); //Конкурсы
$graphStyle[4]['color'] = imagecolorallocate($image, 140, 140, 140); //Перемешения
$graphStyle[5]['color'] = imagecolorallocate($image, 140, 140, 140); //Места внутри кат.
$graphStyle[6]['color'] = imagecolorallocate($image, 140, 140, 140); //Места в каталоге
$graphStyle[7]['color'] = imagecolorallocate($image, 140, 140, 140); //Места на первой
$graphStyle[8]['color'] = imagecolorallocate($image, 103, 135, 179); //Подарки
$graphStyle[9]['color'] = imagecolorallocate($image, 111, 177, 92); //Платные проекты, логотип
$graphStyle[10]['color'] = imagecolorallocate($image, 111, 177, 92); //Платные проекты, подсветка фоном
$graphStyle[11]['color'] = imagecolorallocate($image, 111, 177, 92); //Платные проекты, жирный шрифт
$graphStyle[12]['color'] = imagecolorallocate($image, 111, 177, 92); //Платные проекты, закрепление наверху
$graphStyle[13]['color'] = imagecolorallocate($image, 111, 177, 92); //Платные проекты
$graphStyle[14]['color'] = imagecolorallocate($image, 0, 103, 56); //PRO работодатели
$graphStyle[15]['color'] = imagecolorallocate($image, 179, 36, 36); //PRO
$graphStyle[16]['color'] = imagecolorallocate($image, 247, 128, 90); //PRO тестовое
$graphStyle[17]['color'] = imagecolorallocate($image, 147, 128, 90); //Ответы на проекты
$graphStyle[18]['color'] = imagecolorallocate($image, 90, 60, 90); //Карусел
$graphStyle[19]['color'] = imagecolorallocate($image, 60, 90, 60); //Смена логина

$graphStyle[0]['text'] 	= 'Сумма';
$graphStyle[1]['text'] 	= 'Перевели денег';
$graphStyle[2]['text'] 	= 'Подняли проект';
$graphStyle[3]['text'] 	= 'Конкурсы';
$graphStyle[4]['text'] 	= 'Перемешения';
$graphStyle[5]['text'] 	= 'Места внутри кат.';
$graphStyle[6]['text'] 	= 'Места в каталоге';
$graphStyle[7]['text'] 	= 'Места на первой';
$graphStyle[8]['text'] 	= 'Подарки';
$graphStyle[9]['text'] 	= '- логотип';
$graphStyle[10]['text'] = '- фон';
$graphStyle[11]['text'] = '- шрифт';
$graphStyle[12]['text'] = '- закрепление';
$graphStyle[13]['text'] 	= 'Платные проекты';
$graphStyle[14]['text'] = 'PRO р-тель';
$graphStyle[15]['text'] = 'PRO фрилансер';
$graphStyle[16]['text'] = 'PRO тест';
$graphStyle[17]['text'] = 'Ответы на проекты';
$graphStyle[18]['text'] = 'Карусель';
$graphStyle[19]['text'] = 'Смена логина';


$colorWhite=imagecolorallocate($image, 255, 255, 255);
$colorGrey=imagecolorallocate($image, 192, 192, 192);
$colorDarkBlue=imagecolorallocate($image, 153, 153, 153);

for ($i=0; $i<count($graphValues); $i++) {
	//вычисляем откуда начать прорисовку графика
	if ($i) {
		$iMaxHeight = $graphValues[$i-1][0];
		for ($k=1; $k<count($graphValues[$i-1]); $k++) {
			$iMaxHeight = ($graphValues[$i-1][$k] > $iMaxHeight)?$graphValues[$i-1][$k]:$iMaxHeight;
		}
		$iHeight += $iMaxHeight+25; // +15 - расстояние между строчками
	}

	for ($j=0; $j<count($graphValues[$i]); $j++) {

		imageline($image, $j*$iBarWidth+2 + 100, $imgHeight-$iHeight, $j*$iBarWidth+$iBarWidth + 100, $imgHeight-$iHeight, $colorGrey);
		if (!$i) {
			$iz = ($j+1 > 9)?3.7:2.5;
			imagefttext($image, '7', 0, $j*$iBarWidth+round($iBarWidth/$iz) + 100, $imgHeight - 5, $colorDarkBlue, $sFont, $j+1);
		}

        if ($graphValues2[$i][$j]) {
			imagefilledrectangle($image, $j*$iBarWidth+2 + 100, ($imgHeight-$iHeight-round($graphValues[$i][$j])), ($j+1)*$iBarWidth + 100, $imgHeight-$iHeight, $graphStyle[$i]['color']);
			//надпись количества FM
			$addD = ($i == 8)?2:1; ///Если подарки, то результат делим на 2
            $color = (!$i)?$graphStyle[$i]['color']:$colorDarkBlue;
            if($i!=0) {
                imagefttext($image, '7', 0, $j*$iBarWidth + 100+2, $imgHeight-$iHeight-$graphValues[$i][$j]-12, $color, $sFont, round($graphValues2[$i][$j]/$addD)."\n".$graphValues3[$i][$j]);
            } else {
                $iCount = 0;
                for($k=1; $k<count($graphValues2); $k++) {
                    $addD = ($k == 8)?2:1;
                    if(!in_array($graphStyle[$k]['op_codes'][0],array('p0','p1','p2','p3'))) {
                        $iCount += round($graphValues2[$k][$j]/$addD);
                    }
                }
                imagefttext($image, '7', 0, $j*$iBarWidth + 100+2, $imgHeight-$iHeight-$graphValues[$i][$j]-12, $color, $sFont, round($graphValues2[$i][$j]/$addD)."\n".$iCount);
            }
		}
	}
	$iFontSizeTitle = 8;
	$aBox = imageftbbox($iFontSizeTitle, 0, $sFont,$graphStyle[$i]['text']);
	$width = abs($aBox[0]) + abs($aBox[2]);
    imagefttext($image, $iFontSizeTitle, 0, 92-$width, $imgHeight-$iHeight, $graphStyle[$i]['color'], $sFont, $graphStyle[$i]['text']);
}


$aMonthes[1] = 'Январь';
$aMonthes[2] = 'Февраль';
$aMonthes[3] = 'Март';
$aMonthes[4] = 'Апрель';
$aMonthes[5] = 'Май';
$aMonthes[6] = 'Июнь';
$aMonthes[7] = 'Июль';
$aMonthes[8] = 'Август';
$aMonthes[9] = 'Сентябрь';
$aMonthes[10] = 'Октябрь';
$aMonthes[11] = 'Ноябрь';
$aMonthes[12] = 'Декабрь';

$sString = 'Продажи сервисов';
imagefttext($image, '18', 0, 100, 20, $colorGrey, $sFont, $sString);

/*header("Pragma: no-cache");
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sun, 1 Jan 1995 01:00:00 GMT"); // Это какая-нибудь давно прошедшая дата
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // это строчка говорит, что наш скрипт всегда изменен
header("Content-type: image/png");*/
imagepng($image);
imagedestroy($image);

?>

