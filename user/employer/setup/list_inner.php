<?
if (!$_in_setup) {header ("HTTP/1.0 403 Forbidden"); exit;}
?>
<form action="." method="post">
  <div class="b-layout b-layout_padtop_20">
	<h2 class="b-layout__title">Настройка закладок</h2>
	<? if ($info_msg) print('<div class="b-layout__txt b-layout__txt_padbot_20">'.view_info($info_msg).'</div>') ?>
	<div class="b-layout__txt b-layout__txt_padbot_20">В разделе &laquo;Моя страница&raquo; отображать следующие закладки:</div>
	<div class="b-check b-check_padbot_5">
	<input class="b-check__input" type="checkbox" id="ch6" name="inform" value="0" disabled="disabled"  checked="checked" /> 
	<label class="b-check__label b-check__label_fontsize_13" for="ch6">Информация</label>
	</div>
	<input type="hidden" name="action" value="tabs_change" />
    <div class="b-buttons b-buttons_padtop_20">
	   <button type="submit" name="btn" class="b-button b-button_flat b-button_flat_green">Изменить</button>
    </div>
  </div>
</form>
