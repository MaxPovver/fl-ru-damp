<?
$rpath = "../../";
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
$idMonth = date('m'); //дефолтный месяц
$idYear = date('Y'); //дефотлный год
$iBarWidth = (is_numeric(InGet('y')) && !is_numeric(InGet('m')))?30:20; //ширина ячейки
$iHeight = 20; //отступ снизу
$sFont = ABS_PATH.'/siteadmin/account/Aricyr.ttf';
$DB = new DB('master');

function getOP($op, $date_from='2006-10-10', $date_to='now()', $bYear=false) {
    global $DB;
	if ($bYear) {
		$sql = "SELECT sum(count) as cnt, to_char(date,'MM') as _day FROM stat_feedback WHERE date >= '".$date_from."' AND date < '".$date_to."' AND type=$op   GROUP BY to_char(date,'MM') ORDER BY to_char(date,'MM')";
	}
	else {
		$sql = "SELECT sum(count) as cnt, extract(day from date) as _day FROM stat_feedback WHERE date >= '".$date_from."' AND date < '".$date_to."'::date+'1day'::interval  AND type=$op GROUP BY _day ORDER BY  _day";
	}
//	echo $sql.'<br>';
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
$iFMperPX = (!$bYear)?5:(5*10); //масштаб

for ($i=1; $i<=4; $i++) {
	for ($j=0; $j<=$iMaxDays; $j++) {
		$graphValues[$i][$j] = 0;
		$graphValues2[$i][$j] = 0;
	}
}

$graphStyle[1]['type'] 	= "1";
$graphStyle[2]['type'] 	= "2";
$graphStyle[3]['type'] 	= "3";
$graphStyle[4]['type'] 	= "4";

$graphStyle[1]['addit'] = '';
$graphStyle[2]['addit'] = '';
$graphStyle[3]['addit'] = '';
$graphStyle[4]['addit'] = '';

$imgHeight = 0;
for ($i=1; $i<=4; $i++) {
	$res = getOP($graphStyle[$i]['type'], $date_from, $date_to, $bYear, $graphStyle[$i]['addit']);
	$aTemp = $res;

	if (isset($aTemp[0]['_day'])) {

		$graphStyle[$i]['max'] = abs($aTemp[0]['cnt']/$iFMperPX);
		for ($j=0; $j<count($aTemp); $j++) {
			$iAmount = abs($aTemp[$j]['cnt']/$iFMperPX);
            $ii = $aTemp[$j]['cnt'];
			if ($iAmount > $graphStyle[$i]['max']) {
				$graphStyle[$i]['max'] = $iAmount; //Вычисляем максимальную высоту всего графика
			}

			$graphValues[$i][$aTemp[$j]['_day']-1] = $iAmount;
            $graphValuesV[$i][$aTemp[$j]['_day']-1] = $ii;
			$graphValues2[$i][$aTemp[$j]['_day']-1] = $aTemp[$j]['cnt'];
		}
		$imgHeight += $graphStyle[$i]['max'];
	}
}
//print_r($graphValues2);
$k = 0; $graphStyle[0]['max'] = 0;
for ($i=0; $i<=$iMaxDays; $i++) {
	$iSumm = 0; $iSumm2 = 0; $ii = 0;
	for ($j=1; $j<count($graphValues); $j++) {
		if (isset($graphValues[$j][$i])) {
			$iSumm += $graphValues[$j][$i];
			$ii += $graphValuesV[$j][$i];
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
	$graphValuesV[0][$k] = $ii;
	$graphValues2[0][$k] = $iSumm*$iFMperPX;
	if ($iSumm > $graphStyle[0]['max'])
	$graphStyle[0]['max'] = $iSumm;
	$k++;
}
//print_r($graphValues2);
$imgHeight += $graphStyle[0]['max'] + count($graphValues)*20+40; //прибавляем промежутки к максимальной высоте графика
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
$graphStyle[9]['color'] = imagecolorallocate($image, 111, 177, 92); //Платные проекты
$graphStyle[11]['color'] = imagecolorallocate($image, 179, 36, 36); //PRO
$graphStyle[10]['color'] = imagecolorallocate($image, 0, 103, 56); //PRO работодатели
$graphStyle[12]['color'] = imagecolorallocate($image, 247, 128, 90); //PRO тестовое
$graphStyle[0]['text'] 	= 'Всего';
$graphStyle[1]['text'] 	= 'Общее';
$graphStyle[2]['text'] 	= 'Ошибки';
$graphStyle[3]['text'] 	= 'Финансы';
$graphStyle[4]['text'] 	= 'Проекты';


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
		$iHeight += $iMaxHeight+15; // +15 - расстояние между строчками
	}

	for ($j=0; $j<count($graphValues[$i]); $j++) {

		imageline($image, $j*$iBarWidth+2 + 100, $imgHeight-$iHeight, $j*$iBarWidth+$iBarWidth + 100, $imgHeight-$iHeight, $colorGrey);
		if (!$i) {
			$iz = ($j+1 > 9)?3.7:2.5;
			imagefttext($image, '7', 0, $j*$iBarWidth+round($iBarWidth/$iz) + 100, $imgHeight - 5, $colorDarkBlue, $sFont, $j+1);
		}

		if ($graphValues[$i][$j]) {
			imagefilledrectangle($image, $j*$iBarWidth+2 + 100, ($imgHeight-$iHeight-round($graphValues[$i][$j])), ($j+1)*$iBarWidth + 100, $imgHeight-$iHeight, $graphStyle[$i]['color']);
			//надпись количества FM
			$addD = ($i == 8)?2:1; ///Если подарки, то результат делим на 2
			$color = (!$i)?$graphStyle[$i]['color']:$colorDarkBlue;
            if($i!=0) {
			    imagefttext($image, '7', 0, $j*$iBarWidth + 100+2, $imgHeight-$iHeight-$graphValues[$i][$j]-2, $color, $sFont, round($graphValuesV[$i][$j]));
            } else {
                $iCount = 0;
                for($k=1; $k<count($graphValues2); $k++) {
                    $addD = ($k == 8)?2:1;
                    $iCount += round($graphValuesV[$k][$j]);
                }
                imagefttext($image, '7', 0, $j*$iBarWidth + 100+2, $imgHeight-$iHeight-$graphValues[$i][$j]-2, $color, $sFont, round($iCount));
            }
		}
	}
	$iFontSizeTitle = 8;
	$aBox = imageftbbox($iFontSizeTitle, 0, $sFont,$graphStyle[$i]['text']);
	$width = abs($aBox[0]) + abs($aBox[2]);
	imagefttext($image, $iFontSizeTitle, 0, 90-$width, $imgHeight-$iHeight, $graphStyle[$i]['color'], $sFont, $graphStyle[$i]['text']);
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

$sString = 'Обратная связь';
imagefttext($image, '18', 0, 100, 20, $colorGrey, $sFont, $sString);

header("Pragma: no-cache");
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sun, 1 Jan 1995 01:00:00 GMT"); // Это какая-нибудь давно прошедшая дата
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // это строчка говорит, что наш скрипт всегда изменен
header("Content-type: image/png");
imagepng($image);
imagedestroy($image);

?>
