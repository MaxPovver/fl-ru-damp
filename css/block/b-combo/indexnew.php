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

<script type="text/javascript">
 <?
 require_once $_SERVER["DOCUMENT_ROOT"]."/classes/stdf.php";
 /**
  * На сайте скрипты professions_js.php и cities_js.php подключены в шаблоне template3.php
  * */
 require_once $_SERVER["DOCUMENT_ROOT"]."/professions_js.php";
 require_once $_SERVER["DOCUMENT_ROOT"]."/cities_js.php";
 ?> 
/*Определение переменной _TOKEN_KEY при использовании инпутов на сайте не нужно, так как
 * она уже определена  
 */
 var _TOKEN_KEY = '<?=$_SESSION['rand']?>';
 
 function getSpec() {
    //по смене группы специальностей подгружаем специальности в другой список
    var spec = ComboboxManager.getInput("spec");
    var id = $("profgroup_db_id").get("value");    
    if (Number(id)) {
        spec.breadCrumbs[-1] = id; 	 
        spec.reload(''); 	 
        spec.show(true);
    }
}
//получаем компанию
function getCompany() {
    var cb = ComboboxManager.getInput("cu5");
    var id = 164;
    cb.loadRecord(id, "get_user_or_company_info", "type=company");
}
//получаем пользователя (для списка пользователи и компании)
function getUserExample() {
    var cb = ComboboxManager.getInput("cu5");
    var id = 164;
    cb.loadRecord(id, "get_user_or_company_info", "type=user");
}
 
</script>
</head>
<body>	

<h3>Пользователи и компании</h3>
<div class="b-combo">
	<div class="b-combo__input  b-combo__input_resize b-combo__input_dropdown b-combo__input_width_140 b-combo__input_max-width_350  b-combo__input_arrow-user_yes b_combo__input_request_id_getusersandcompanies">
		<input id="cu5" class="b-combo__input-text" name="" type="text" size="80" value="" first_section_text ="Пользователи"   second_section_text ="Компании" count_measure_1="человек" count_measure_2="компаний"/>
		<label class="b-combo__label" for="cu5"></label>
		<span class="b-combo__arrow-user"></span>
	</div>
</div>
<input type="button" value="Получить пользователя" onclick="getUserExample()" />
<input type="button" value="Получить компанию" onclick="getCompany()" />
<h3>Группы специальностей</h3>
<div class="b-combo">
	<div class="b-combo__input b-combo__input_multi_dropdown b-combo__input_init_professionsList b-combo__input_width_150 b-combo__input_arrow_yes b-combo__input_max-width_450">
		<input id="profgroup" class="b-combo__input-text" name="testname" type="text"  size="80" value=""  onchange="getSpec()" />
		<span class="b-combo__arrow"></span>
	</div>
</div>
 
<h3>Специальности</h3>
<div class="b-combo b-combo_inline-block">
        <div class="b-combo__input b-combo__input_multi_dropdown cut_column_1_form_profgroup_set_parent_1000 b-combo__input_width_245 b-combo__input_resize b-combo__input_arrow_yes b-combo__input_max-width_450">
            <input type="text" id="spec" class="b-combo__input-text b-combo__input-text_fontsize_15" size="80" value="">
            <span class="b-combo__arrow" id="city_arrow"></span>
        </div>
</div>

<h3>Поиск пользователей (авторизуйтесь например как testwork1/123456 на <a href="/" target="_blank">главной</a>)</h3>
<div class="b-combo">
<!-- drop_down_not_use_cache -->
	<div class="b-combo__input  b-combo__input_resize b-combo__input_dropdown b-combo__input_width_140 b-combo__input_max-width_250  b-combo__input_arrow-user_yes b_combo__input_request_id_getuserlistold">
		<input id="c5" class="b-combo__input-text" name="" type="text" size="80" value="" />
		<label class="b-combo__label" for="c5"></label>
		<span class="b-combo__arrow-user"></span>
	</div>
</div>

<h3>Поиск пользователей (только фриленсеры)</h3>
<div class="b-combo">
<!-- drop_down_not_use_cache -->
	<div class="b-combo__input  b-combo__input_resize b-combo__input_dropdown b-combo__input_width_140 b-combo__input_max-width_250  b-combo__input_arrow-user_yes b_combo__input_request_id_getuserlistold get_only_freelancers">
		<input id="c51" class="b-combo__input-text" name="" type="text" size="80" value="" />
		<label class="b-combo__label" for="c5"></label>
		<span class="b-combo__arrow-user"></span>
	</div>
</div>
	
<h3>Поиск пользователей (только работодатели)</h3>
<div class="b-combo">
	<div class="b-combo__input  b-combo__input_resize b-combo__input_dropdown b-combo__input_width_140 b-combo__input_max-width_250  b-combo__input_arrow-user_yes b_combo__input_request_id_getuserlistold get_only_employer">
		<input id="c52" class="b-combo__input-text" name="" type="text" size="80" value="" />
		<label class="b-combo__label" for="c5"></label>
		<span class="b-combo__arrow-user"></span>
	</div>
</div>

<h3>Поиск пользователей (только в СБР/контактах)</h3>
<div class="b-combo">
	<div class="b-combo__input  b-combo__input_resize b-combo__input_dropdown b-combo__input_width_140 b-combo__input_max-width_250  b-combo__input_arrow-user_yes b_combo__input_request_id_getuserlistold search_in_sbr">
		<input id="c53" class="b-combo__input-text" name="" type="text" size="80" value="" />
		<label class="b-combo__label" for="c5"></label>
		<span class="b-combo__arrow-user"></span>
	</div>
</div>	

<h3>Поиск пользователей (только в общем списке)</h3>
<div class="b-combo">
	<div class="b-combo__input  b-combo__input_resize b-combo__input_dropdown b-combo__input_width_140 b-combo__input_max-width_250  b-combo__input_arrow-user_yes b_combo__input_request_id_getuserlistold search_in_userlist">
		<input id="c54" class="b-combo__input-text" name="" type="text" size="80" value="" />
		<label class="b-combo__label" for="c5"></label>
		<span class="b-combo__arrow-user"></span>
	</div>
</div>	
<!--h3>Поиск пользователей (sphinx)</h3>
<div class="b-combo">

	<div class="b-combo__input  b-combo__input_resize b-combo__input_dropdown b-combo__input_width_140 b-combo__input_max-width_250  b-combo__input_arrow-user_yes b_combo__input_request_id_getuserlist">
		<input id="c5sphinx" class="b-combo__input-text" name="" type="text" size="80" value="bolvan" />
		<label class="b-combo__label" for="c5"></label>
		<span class="b-combo__arrow-user"></span>
	</div>
</div-->
	
	
<h3> Простое поле ввода</h3>
<div class="b-combo">
	<div class="b-combo__input b-combo__input_width_100 b-combo__input_max-width_400 b-combo__input_resize">
		<input id="c1" class="b-combo__input-text" name="" type="text" size="80" value="" />
		<label class="b-combo__label" for="c1"></label>
	</div>
</div>

<script type="text/javascript">
	var currencyList = {1:"RUR",  
						2:"USD", 
				 	    3:"EURO"
					};				
</script>

<h3>Список валют - используем  глобальную переменную javaScript для инициализации</h3>
<div style="color:#ff0000; font-weight:bold">Внимание! Идентификаторы ввалют в примере на данный момент фейковые, пример предназначен просто для демонстрации.<br>
Как получить список валют с сервера читайте здесь: http://beta.free-lance.ru/wiki/doku.php?id=code:inputs, раздел 3.2
</div>
<div class="b-combo">
	<div class="b-combo__input b-combo__input_multi_dropdown b-combo__input_init_currencyList show_all_records b-combo__input_width_150 b-combo__input_resize   b-combo__input_max-width_450 b-combo__input_arrow_yes ">
		<input id="currency_list" class="b-combo__input-text" name="" type="text" size="80" readonly="readonly"/>		
		<span class="b-combo__arrow"></span>
	</div>
</div>

<h3>Специальности </h3>
<div class="b-combo">
	<div class="b-combo__input b-combo__input_multi_dropdown show_all_records b-combo__input_width_150 b-combo__input_resize  b-combo__input_max-width_450 b-combo__input_arrow_yes  b-combo__input_init_professionsList drop_down_default_-1 multi_drop_down_default_column_1">
		<input id="c2" class="b-combo__input-text" name="" type="text" size="80" value="QA (тестирование)" />		
		<span class="b-combo__arrow"></span>
	</div>
</div>

<h3>Специальности  - пример очистки поля по нажатию кнопки</h3>
<div class="b-combo">
	<div class="b-combo__input b-combo__input_multi_dropdown b-combo__input_width_150 show_all_records b-combo__input_resize  b-combo__input_max-width_450 b-combo__input_arrow_yes  b-combo__input_init_professionsList drop_down_default_538 multi_drop_down_default_column_0">
		<input id="c2_1" class="b-combo__input-text" name="" type="text" size="80" value="Все разделы" />	
		<span class="b-combo__arrow"></span>
	</div>
</div>

<input type="button" onclick="ComboboxManager.setDefaultValue('c2_1', 'Все разделы', 0)" value="Clear" />

<!-- b-combo__input_on_load_request_id_getrelevantcountries-->
<h3>Страны - новые значения не допустимы (on change)</h3>
<div class="b-combo">
	<div class="b-combo__input b-combo__input_multi_dropdown b-combo__input_width_150 b-combo__input_resize b-combo__input_arrow_yes  b-combo__input_init_citiesList b-combo__input_on_click_request_id_getcities b-combo__input_max-width_450 all_value_id_0_0_Все+страны all_value_id_1_0_Все+города exclude_value_1_0">
		<input id="country" class="b-combo__input-text" name="testname" type="text"  size="80" value=""  onchange="alert('вызов onchcnge')" />
		<span class="b-combo__arrow"></span>
	</div>
</div>

<input type="button" value="testonCh" onclick="$('country').value='777'" />

<h3>Страны -  новые значения  допустимы</h3>
<div class="b-combo">
	<div class="b-combo__input b-combo__input_multi_dropdown allow_create_value_1 b-combo__input_resize b-combo__input_width_150  b-combo__input_arrow_yes b-combo__input_init_citiesList b-combo__input_on_click_request_id_getcities all_value_id_0_0_Все+страны all_value_id_1_0_Все+города drop_down_default_1 multi_drop_down_default_column_0">
		<input id="countryRelevant" class="b-combo__input-text"  value="Москва" type="text" />
		<span class="b-combo__arrow"></span>
	</div>
</div>

<h3>Страны -  пункт "Все страны" не выводится</h3>
<div class="b-combo">
	<div class="b-combo__input b-combo__input_multi_dropdown b-combo__input_width_150 b-combo__input_resize b-combo__input_arrow_yes  b-combo__input_init_citiesList b-combo__input_on_click_request_id_getcities b-combo__input_max-width_450 exclude_value_0_0">
		<input id="country" class="b-combo__input-text" name="" type="text"  size="80" value="" />		
		<span class="b-combo__arrow"></span>
	</div>
</div>

<h3>Страны - пустое значение не допустимо</h3>
<div class="b-combo">
	<div class="b-combo__input allow_create_value_1 b-combo__input_multi_dropdown b-combo__input_width_150 b-combo__input_resize b-combo__input_arrow_yes b-combo__input_init_citiesList b-combo__input_on_click_request_id_getcities b-combo__input_max-width_450  exclude_value_0_0 disallow_null">
		<input id="country" class="b-combo__input-text" name="" type="text"  size="80" value="" />		
		<span class="b-combo__arrow"></span>
	</div>
</div>

<h3>Проверка трех колонок</h3>
<div class="b-combo">
	<div class="b-combo__input b-combo__input_multi_dropdown b-combo__input_width_150 b-combo__input_resize b-combo__input_max-width_450 b-combo__input_arrow_yes  b-combo__input_init_threeData use_scroll">
		<input id="c12" class="b-combo__input-text" name="" type="text" size="80" value="" />		
		<span class="b-combo__arrow"></span>
	</div>
</div>



<!-- use_past_date -->
<h3>Календарь без возможности выбора прошедшей даты </h3>
<div class="b-combo">
	<div class="b-combo__input  b-combo__input_calendar b-combo__input_error date_min_limit_2012_05_11 b-combo__input_width_140 b-combo__input_max-width_140   date_format_use_text no_set_date_on_load">
		<input id="c3" class="b-combo__input-text" name="frr" type="text" size="80" value="11 мая 2019" />
		<label class="b-combo__label" for="c3"></label>
		<span class="b-combo__arrow-date"></span>
	</div>
</div>

<h3>Календарь с возможностью выбора прошедшей даты  (class="... use_past_date ...") и недопустимостью ввода пустого значения (при пустом значениии подставляется текущая дата) </h3>
<div class="b-combo">
	<div class="b-combo__input b-combo__input_calendar b-combo__input_width_140 b-combo__input_max-width_140 use_past_date date_format_use_text no_set_date_on_load disallow_null set_current_date_on_null">
		<input id="c223" class="b-combo__input-text" name="fr2r" type="text" size="80" value="13.05.2012" />
		<label class="b-combo__label" for="c3"></label>
		<span class="b-combo__arrow-date"></span>
	</div>
</div>







<div id="container" style="padding-top:100px">
<input type="text" name="label" value="????" readonly="readonly"/>
</div>


<input type="button" value="Append CountryList" onclick="ComboboxManager.prepend($('container'), 
'b-combo__input allow_create_value_1 b-combo__input_multi_dropdown b-combo__input_width_150 b-combo__input_resize b-combo__input_arrow_yes  b-combo__input_on_load_request_id_getrelevantcountries  b-combo__input_on_click_request_id_getcities b-combo__input_max-width_450  exclude_value_0_0 disallow_null', 'newNew');" />
<input type="button" value="Append" onclick="append()" />
<input type="button" value="Prepend" onclick="prepend()" />
<input type="button" value="Remove" onclick="remove()" />
</body>
</html>
