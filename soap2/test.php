<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>
<head>
	<title>Untitled</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<style>
td, span, div, .std{
font-family: Tahoma;
font-size: 11px;
color: #666666;
font-weight: normal;
}

.frlname11{
font-size: 11px;
color: #666666;
font-weight: bold;
}

img.pro{
  background-color:none;
	width: 26px;
	height: 11px;
	border-width:0px;
	margin-right: 3px;
}

.freelancerU img.pro{
	width: 26px;
	height: 11px;
	border-width:0px;
}

.cl9{
color: #909090;
}

.c_grey{
color: #909090;
	font-weight:bold;
	display:block;
}


.freelancerU_content a.blue {
	font-weight:bold;
	display:block;
	color:#003399;
}
.u_active{
  font-size: 80%;
  color: #ff6b3d;
  margin-right:16px;
}

.u_inactive{
  font-size: 80%;
  color: #477ad9;
  margin-right:16px;
}

.prj_bold {
font-weight:bold;
color: #000000;
}

.prj_a {
color: #000000;
text-decoration: none;
font-family: Tahoma;
font-size: 11px;
color: #666666;
font-weight: normal;
}
.user_blue {
	font-weight:bold;
	color:#003399;
}

</style>
    </head>

<body>
    формат фильтра
    $filter
    [0] - включен? = 0
    [1] - бюджет от = 0
    [2] - бюджет до = 0
    [3] - Показывать с неуказанным бюджетом = 1
    [4] - разработка сайтов = 1 (2 Разработка сайта)
    [5] - программирование = 1 (1 Программирование)
    [6] - Переводы тексты = 1 (3 Тексты, переводы)
    [7] - Дизайнарт = 1 (4 Дизайн)
    [8] - реклама-маркетинг = 1 (5 Реклама, маркетинг)
    [9] - прочее = 1 (6 Прочее)
    [10] - 0 - free
    [11] - 1 - office
    [12] - 2 - koncurs
    [13] - 3 - partnership

<pre>
<?php 

  ini_set("soap.wsdl_cache_enabled", "0"); // отключаем кэширование WSDL 
  $client = new SoapClient("fltray.wsdl"); 
  $lastprj =0;
  //print "!!!"; mb9o1vu4i097xn84utp3t96epdwiux5g
  //print_r($client->ReadMess('mb9o1vu4i097xn84utp3t96epdwiux5g', array(1,2))); 
  //print_r($client->AuthUser('russkiy-lance','3434905','') ); 
  //echo "RESPONSE:\n" . $client->__getLastResponse() . "\n";
  print_r($client->GetAllInfo('wog7de8dul4b5q81einq779wxk25bxy8',0,array(1,1),array(1,0,0,0,0,0,0,0,0,0,0,0,0,0))) 
  //print $client->SendMess('mb9o1vu4i097xn84utp3t96epdwiux5g', 'test222!!!', 2, 47325); 
  //print $client->CheckVersion("2.0.0.1");
?> 

</body>
</html>
