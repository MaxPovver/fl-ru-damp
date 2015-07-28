<?
if (!$_in_setup) {header ("HTTP/1.0 403 Forbidden"); exit;}
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/users.common.php");
$xajax->printJavascript('/xajax/');
?>
<form action="." method="post">
  <div class="b-layout b-layout_padtop_20">
	<h2 class="b-layout__title">Настройка закладок</h2>
	<? if ($info_msg) print('<div class="b-layout__txt b-layout__txt_padbot_20">'.view_info($info_msg).'</div>') ?>
	<div class="b-layout__txt b-layout__txt_padbot_20">В разделе &laquo;Моя страница&raquo; отображать следующие закладки:</div>
	<div class="b-check b-check_padbot_5">
	<input class="b-check__input" type="checkbox" id="ch1" name="portf" value="1" <? if ($user->tabs[0]) print "checked='checked'" ?>  /> 
	<label class="b-check__label b-check__label_fontsize_13" for="ch1"><?=view_tab_name($user->tab_name_id)?></label>
	</div>
<!--
	<input type="checkbox" name="serv" value="1" <? if ($user->tabs[1]) print "checked='checked'" ?> /> Услуги<br /><br />
-->
	<div class="b-check b-check_padbot_5">
	<input class="b-check__input" type="checkbox" id="ch8" name="tu" value="1" <? if ($user->tabs[7]) print "checked='checked'" ?> /> 
	<label class="b-check__label b-check__label_fontsize_13" for="ch8">Типовые услуги</label>
	</div>	
	<div class="b-check b-check_padbot_5">
	<input class="b-check__input" type="checkbox" id="ch3" name="info" value="1" disabled="disabled" <? if ($user->tabs[2]) print "checked='checked'" ?> /> 
	<label class="b-check__label b-check__label_fontsize_13" for="ch3">Информация</label>
	</div>	
	<div class="b-check b-check_padbot_5">
	<input class="b-check__input" type="checkbox" id="ch4" name="rating" value="1" <? if ($user->tabs[4]) print "checked='checked'" ?> /> 
	<label class="b-check__label b-check__label_fontsize_13" for="ch4">Рейтинг</label>
	</div>	
	<input type="hidden" name="action" value="tabs_change" />
    <div class="b-buttons b-buttons_padtop_20">
	   <button type="submit" name="btn" class="b-button b-button_flat b-button_flat_green">Изменить</button>
    </div>
  </div>
</form>
