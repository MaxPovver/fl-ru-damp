<?php
$rpath = "../../";
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");

$DB = new DB('master');

$syear = $_GET['y'];
$smonth = $_GET['m'];

$data = array();
$days = date('t',mktime(0,0,0, $smonth, 1, $syear));
for($n=1;$n<=$days;$n++) { $data[$n] = 0; }

$sql = "SELECT u_reg, extract(day from date) as day FROM stat_data WHERE date>=? AND date<=?";
$q = $DB->rows($sql, $syear."-".$smonth."-01", $syear."-".$smonth."-".$days);
$is_null = 1;
if($q) {
    foreach($q as $s) {
        if($s['u_reg']!=0) $is_null = 0;
        $data[$s['day']] = $s['u_reg'];
    }
}

// Standard inclusions   
include("pChart/pData.class");
include("pChart/pChart.class");

// Dataset definition 
$DataSet = new pData;
$DataSet->AddPoint($data,"S1");
$DataSet->AddPoint(array_keys($data),"S2");
$DataSet->AddSerie("S1");
$DataSet->SetAbsciseLabelSerie("S2");

// Initialise the graph
$Test = new pChart(700,230);

if($is_null) $Test->setFixedScale(0,1,1);

$Test->setFontProperties("Fonts/tahoma.ttf",8);
$Test->setGraphArea(50,30,665,200);
$Test->drawFilledRoundedRectangle(7,7,693,223,5,240,240,240);
$Test->drawRoundedRectangle(5,5,695,225,5,230,230,230);
$Test->drawGraphArea(255,255,255,TRUE);
$Test->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_NORMAL,150,150,150,TRUE,0,2);   
$Test->drawGrid(4,TRUE,230,230,230,50);


// Draw the cubic curve graph
$Test->drawCubicCurve($DataSet->GetData(),$DataSet->GetDataDescription());

// Finish the graph
$Test->setFontProperties("Fonts/tahoma.ttf",10);
$Test->drawTitle(50,22,"Регистрации",50,50,50,585);
$Test->Stroke();
?>

