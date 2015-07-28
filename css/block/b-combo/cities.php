<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>b-combo</title>
<link href="b-combo.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/scripts/mootools-new.js"></script>
<script type="text/javascript" src="/scripts/b-combo/b-combo-dynamic-input.js?rand=<?=rand(1000,9999)?>"></script>
<script type="text/javascript" src="/scripts/b-combo/b-combo-multidropdown.js?rand=<?=rand(1000,9999)?>"></script>
<script type="text/javascript" src="/scripts/b-combo/b-combo-autocomplete.js?rand=<?=rand(1000,9999)?>"></script>
<script type="text/javascript" src="/scripts/b-combo/b-combo-calendar.js?rand=<?=rand(1000,9999)?>"></script>
<script type="text/javascript" src="/scripts/b-combo/b-combo-manager.js?rand=<?=rand(1000,9999)?>"></script>

<script type="text/javascript" >
/* Внимание!  Определение этой переменной при использовании инпутов на сайте не нужно, так как
 * она уже определена  
 */
 <?
 require_once $_SERVER["DOCUMENT_ROOT"]."/classes/stdf.php";
 require_once $_SERVER["DOCUMENT_ROOT"]."/classes/memBuff2.php";
 require_once $_SERVER["DOCUMENT_ROOT"]."/classes/search/sphinxapi.php";
 ?> 
 var _TOKEN_KEY = '<?=$_SESSION['rand']?>'; 
 
 /*
 Пример работы с двумя одноколоночными списками
 */
 function loadCities() {
	 var cities = ComboboxManager.getInput("cities");	 
	 var id = $("country_db_id").get("value");
	 cities.loadData("getcities", id);	 
 }
</script>

</head>
<body>	


<h3>Страны </h3>
<div class="b-combo">
	<div class="b-combo__input b-combo__input_multi_dropdown b-combo__input_width_150 b-combo__input_resize b-combo__input_arrow_yes  b-combo__input_on_load_request_id_getrelevantcountries  b-combo__input_max-width_450 all_value_id_0_0_Все+страны  exclude_value_1_0">
		<input id="country" class="b-combo__input-text" name="testname" type="text"  size="80" value=""  onchange="loadCities()" />		
		<span class="b-combo__arrow"></span>
	</div>
</div>


<h3>Города </h3>
<div class="b-combo">
	<div class="b-combo__input b-combo__input_multi_dropdown b-combo__input_width_150 b-combo__input_resize b-combo__input_arrow_yes  b-combo__input_max-width_450 all_value_id_0_0_Все+города  exclude_value_1_0">
		<input id="cities" class="b-combo__input-text" name="testname" type="text"  size="80" value=""  />		
		<span class="b-combo__arrow"></span>
	</div>
</div>

</body>
</html>
