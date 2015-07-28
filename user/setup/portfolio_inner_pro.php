<?

//@todo: похоже шаблон не используется, но пока не трогаю
//@todo: shop уже везде выпилин! а здесь связь магазина с портфолио что на сайте уже давно нет


if (!$_in_setup) {header ("HTTP/1.0 403 Forbidden"); exit;}
unset($_SESSION['w_select']);	//сбрасываем переменную сессии отмеченных работ (для удаления работ и перетаскивания их из одного раздела в другие)
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/portfolio.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/shop.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/kwords.php");

$DB = new DB('master');

//echo "<pre>";print_r($_SESSION);die();

$prfs = new professions();
$profs = $prfs->GetSpecs($user->login);
$size = sizeof($profs);
$portf = new portfolio();
$prjs = $portf->GetPortf($user->uid, "NULL", true);
$portf_cnt = array();

$shop_categories = shop::GetShopCategorys(false);

if (!$prjs) include("portfolio_in_setup.php");
	else {

    $lp_id = 0;
    $fp_id = 0;
    $fprj_id = 0;
    $lprj_id = 0;

    /**
     * Выбираем список профессий и считаем количество работ в каждом разделе.
     */
    $lastprof = -1;
    $num_prjs = count($prjs);
		$wrk_profs = $wrk_profs_names = array();
    foreach ($prjs as $key => $prj)
    {
			$curprof = $prj['prof_id'];
			$prjs[$key]['prj_pos_start'] = $prjs[$key]['prj_pos_end'] = false;
			$prjs[$key]['wrk_pos_start'] = $prjs[$key]['wrk_pos_end'] = false;
      $portf_cnt[$prj['prof_id']]++;
			if ($lastprof != $curprof)
			{
			  $wrk_profs[] = $key;
        $wrk_profs_names[$prj['prof_id']] = $prj['profname'];
			  $lastprof = $curprof;
			}
    }
    /**
     * Начальная и конечная профессия (для сортировки).
     */
    reset($wrk_profs);
    $prjs[current($wrk_profs)]['wrk_pos_start'] = true;
    end($wrk_profs);
    $prjs[current($wrk_profs)]['wrk_pos_end'] = true;
    $last_wrk = current($wrk_profs);

    $lastprof = -1;
    foreach ($wrk_profs as $key)
    {
      /**
       * Последняя работа в профессии.
       */
		  if (($key > 0) && !is_null($prjs[$key - 1]['name']))
		  {
  			$prjs[$key -1]['prj_pos_end'] = true;
		  }
      /**
       * Первая работа в профессии.
       */
		  if (($key < $num_prjs) && !is_null($prjs[$key]['name']))
		  {
  			$prjs[$key]['prj_pos_start'] = true;
		  }
    }
    /**
     * Последняя работа.
     */
    $prjs[$num_prjs - 1]['prj_pos_end'] = true;
    
    //var_dump($prjs);

require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/portfoliopos.common.php");
$xajax->printJavascript('/xajax/'); 

$aAllowedExt = array_diff( $GLOBALS['graf_array'], array('swf') )
?>
<style type="text/css">
.flt-lnk:link, .flt-lnk:visited{
	text-decoration:none;
	background:url(../images/dot_666.png) repeat-x bottom left;
	color:#666;
	font-weight:400;
}
.flt-lnk:hover{
	text-decoration:none;
	color:#6BB24B;
	background:url(../images/dot_green.png) repeat-x bottom left;
}
.flt-hint-ajax-in{
	padding: 8px;
	line-height: 150%;
}
.flt-hint-ajax{
    line-height: 150%;
	background: #FFFFCC;
	position:absolute;
	top: 34px;
	left: 1px;
	width: 401px;
}
</style>
<script  type="text/javascript">
<!--

function toggle_shop() {
    var _frm = document.getElementById('frm');
    if(_frm.in_shop.checked==true) {
        _frm.shop_cost_type.disabled = false;
        _frm.shop_cost.disabled = false;
        _frm.shop_tags.disabled = false;
        //_frm.shop_info.disabled = false;
        _frm.shop_category.disabled = false;
    } else {
        _frm.shop_cost_type.disabled = true;
        _frm.shop_cost.disabled = true;
        _frm.shop_tags.disabled = true;
        //_frm.shop_info.disabled = true;
        _frm.shop_category.disabled = true;
    }
}

errmsg1 = errmsg2 = errmsg3 = errmsg4 = errmsg5 = errmsg6 = errmsg7 = errmsg100 = errmsg201 = errmsg204 = errmsg205 = errmsg206 = errmsg202 = errmsg207 = errmsg208 = '';
can_move = 1;

<? if ($error_flag) {
	if ($alert[1]) print("errmsg1=\"".ref_scr(view_error($alert[1]))."\";");
  if ($alert[2]) print("errmsg2=\"".ref_scr(view_error($alert[2]))."\";");
	if ($alert[3]) print("errmsg3=\"".ref_scr(view_error($alert[3]))."\";");
	if ($alert[4]) print("errmsg4=\"".ref_scr(view_error($alert[4]))."\";");
	if ($alert[5]) print("errmsg5=\"".ref_scr(view_error($alert[5]))."\";");
	if ($alert[6]) print("errmsg6=\"".ref_scr(view_error($alert[6]))."\";");
	if ($alert[7]) print("errmsg7=\"".ref_scr(view_error($alert[7]))."\";");
	if ($alert[100]) print("errmsg100=\"".ref_scr(view_error($alert[100]))."\";");
	if ($alert[201]) print("errmsg201=\"".ref_scr(view_error($alert[201]))."\";");
	if ($alert[204]) print("errmsg204=\"".ref_scr(view_error($alert[204]))."\";");
	if ($alert[205]) print("errmsg205=\"".ref_scr(view_error($alert[205]))."\";");
	if ($alert[206]) print("errmsg206=\"".ref_scr(view_error($alert[206]))."\";");
	if ($alert[202]) print("errmsg202=\"".ref_scr(view_error($alert[202]))."\";");
	if ($alert[207]) print("errmsg207=\"".ref_scr(view_error($alert[207]))."\";");
	if ($alert[208]) print("errmsg208=\"".ref_scr(view_error($alert[208]))."\";");
?>
window.addEvent('domready', function() {
    if($$('div.errorBox')) {
        new Fx.Scroll(window).toElement($$('div.errorBox')[0].getPrevious());
    }
});
<? } ?>
function add_preview(filepath){
  return;
  ext = filepath.value.substr(filepath.value.length-3,3);
  if ((ext == 'swf') || (ext == 'pdf'))
  {
    document.getElementById('preview').style.display = "block";
    document.getElementById('mfsz').value = "10485760";
  }
  else
  {
    document.getElementById('preview').style.display = "none";
    document.getElementById('mfsz').value = "10485760";
  }
}

function add_preview_bv(filepath){
  ext = filepath.substr(filepath.length-3,3);
  if ((ext == 'swf') || (ext == 'pdf'))
  {
    document.getElementById('preview').style.display = "block";
    document.getElementById('mfsz').value = "10485760";
  }
  else
  {
    document.getElementById('preview').style.display = "none";
    document.getElementById('mfsz').value = "10485760";
  }
}

function getEdFrm(pid)
{
  if (pid && window.location.href.indexOf("#prof" + pid) == -1) {
	  window.location.href += "#prof" + pid;
  }
  if(pid)
    return edfrm.replace(/(form\s+action=)(\S+)/gi, '$1".#prof'+pid+'"');
  return edfrm;
}

function setCaretTo(obj, pos) { 
	if(obj.createTextRange) { 
		var range = obj.createTextRange(); 
		range.move("character", pos); 
		range.select(); 
	} else if(obj.selectionStart) { 
		obj.focus(); 
		obj.setSelectionRange(pos, pos); 
	} 
} 

function tags_count() {
	if (document.getElementById('shop_tags').value) {
		var c = document.getElementById('shop_tags').value.length;
		var r = document.getElementById('shop_tags').value.split(',');
		var wc = r.length;
		var sc = false;
		for (var i=0; i<wc; i++) {
			if (r[i].replace(/(^\s+)|(\s+$)/g, "").length > 20) {
				sc = i;
				break;
			}
		}
		if (wc > 10) {
			document.getElementById('co-warn').style.display = '';
			document.getElementById('co-warn-text').innerHTML = 'Вы превысили количество допустимых тегов';
			document.getElementById('shop_tags').value = document.getElementById('shop_tags').value.substr(0, c - 1);
		} else if (sc !== false) {
			document.getElementById('co-warn').style.display = '';
			document.getElementById('co-warn-text').innerHTML = 'Вы превысили количество допустимых символов в теге';
			var str = '';
			var car = 0;
			var p = true;
			for (var i=0; i<wc; i++) {
				if (i == sc) {
					r[i] = r[i].substr(0, r[i].length-1);
					car += r[i].length;
					p = false;
				}
				str += r[i]+',';
				if (p) car += r[i].length + 1;
			}
			document.getElementById('shop_tags').value = str.substr(0, str.length-1)
			setCaretTo(document.getElementById('shop_tags'), car);
		} else {
			document.getElementById('co-warn').style.display = 'none';
			document.getElementById('co-warn-text').innerHTML = '';
		}
	}
}


function setform(){
edfrm = "<form action=\".\" method=\"post\" name=\"frm\" id=\"frm\" enctype=\"multipart/form-data\" onSubmit=\"if (!allowedExt(this['img'].value)) return false; if(this.checkValidate && !this.checkValidate()) { this.btn.value='Подождите'; this.btn.disabled=true; this.btn1.value='Подождите'; this.btn1.disabled=true; this.btn_cancel.value='Подождите'; this.btn_cancel.disabled=true; }\" style='display:block;'>\
<input type=\"hidden\" name=\"action\" id=\"action\" value=\"portf_change\">\
<input type=\"hidden\" name=\"u_token_key\" value=\"<?=$_SESSION['rand']?>\">\
<input type=\"hidden\" name=\"prof\" value=\"\">\
<input type=\"hidden\" name=\"prjid\" id=\"prjid\" value=\"\">\
<table  style='width:100%' border='0' cellspacing='0' cellpadding='0' class='edit'>\
<tr>\
	<td rowspan='13'  style='width:19px'>&nbsp;<\/td>\
	<td  style='height:25px' colspan='2' id='ff'><div style='padding-top:5px'><strong>Изменить работу<\/strong><\/div><\/td>\
	<td rowspan='10' style='width:19px'  >&nbsp;<\/td>\
<\/tr>\
<tr>\
    <td height='45' valign='middle' colspan='2'>\
    <select id='new_prof' name='new_prof' style='width:638px'>\
<?
foreach ($wrk_profs_names as $key => $cname)
{
  if(!$is_pro && ($key==professions::BEST_PROF_ID || $key==professions::CLIENTS_PROF_ID)) continue;
  //if($key==professions::BEST_PROF_ID && $portf_cnt[professions::BEST_PROF_ID] >= portfolio::MAX_BEST_WORKS) continue;
  echo("<option value='$key'>".preg_replace('/\"/','\"',$cname)."</option>");
}
?>
    </select>\
	<\/td>\
<\/tr>\
<tr>\
	<td height='45' valign='middle' colspan='2'><div class='b-layout__txt b-layout__txt_inline-block b-layout__txt_fontsize_11 b-layout__txt_color_71'>Размещение в разделе:<\/div> <div class='b-radio b-radio_top_-1 b-radio_inline-block'><input id='make_position_first' class='b-radio__input' type='radio' name='make_position' value='first'  \/><label class='b-radio__label b-radio__label_color_71 b-radio__label_fontsize_11' for=\"make_position_first\">поставить первой<\/label><\/div>\
	  <div class='b-radio b-radio_top_-1 b-radio_inline-block'><input class='b-radio__input' type='radio' id='make_position_last' name='make_position' value='last' \/><label class='b-radio__label b-radio__label_color_71 b-radio__label_fontsize_11' for=\"make_position_last\">поставить последней<\/label><\/div>\
	  <div class='b-radio b-radio_top_-1 b-radio_inline-block'><input class='b-radio__input' type='radio' id='make_position_num' name='make_position' value='num' \/><label class='b-radio__label b-radio__label_color_71 b-radio__label_fontsize_11' for=\"make_position_num\">сделать<\/label><\/div>\
	  <div class='b-input b-input_margtop_-2 b-input_inline-block b-input_width_45'><input class='b-input__text b-input__text_align_center' type='text' id='_make_position_num' name='make_position_num' value='' onChange=\"document.getElementById('make_position_num').checked=true;\" \/><\/div> <div class='b-layout__txt b-layout__txt_inline-block b-layout__txt_fontsize_11 b-layout__txt_color_71'>в разделе<\/div>\
	<\/td>\
<\/tr>\
<tr>\
	<td height='20' valign='bottom' colspan='2'>Название<span style='font-weight:bold; color:#f00'>*</span>:<\/td>\
<\/tr>\
<tr>\
	<td width='790' height='25'><div class='b-input'><input type='text' name='pname' id='pname' value='' maxlength='80' class='b-input__text' onkeydown='if (this.value.length > 80) this.value=this.value.slice(0, 80)'><\/div><br>"+errmsg1+"<\/td>\
	<td align='right' style='padding-left:10px;'>Максимум<br /> 80 символов<\/td>\
<\/tr>\
<tr>\
	<td style='height:25px; vertical-align:middle;padding-top:20px;padding-bottom:12px;'>\
	Укажите стоимость разработки \
	<select name='pcosttype' id='pcosttype'><option value='0'>USD<\/option><option value='1'>Euro<\/option><option value='2'>Руб<\/option><\/select>\
	<input type='text' name='pcost' id='pcost' maxlength='10' style='width:65px;margin-right:16px;'> и временные затраты <input type='text' name='ptime' id='ptime' maxlength='3' style='width:50px;'> <select name='ptimeei' id='ptimeei'><option value='0'>в часах</option><option value='1'>в днях</option><option value='2'>в месяцах</option><option value='3'>в минутах</option></select>"+errmsg4+errmsg5+"\
	<\/td>\
	<td width='80' align='right'><\/td>\
<\/tr>\
<tr>\
  <td height='20' valign='bottom' colspan='2'>Загрузить работу:<\/td>\
<\/tr>\
<tr>\
  <td height='auto' colspan='2'><input type='hidden' id='mfsz' name='MAX_FILE_SIZE' value='10485760'><input type='file' id='img' name='img' size='111'<? if ($is_pro) {?> onChange='add_preview(this);'<? } ?>>"+errmsg3+"\
  <span id='renew-prev' class='renew-prew' style='visibility:hidden'><label><input name='upd_prev' value='1' type='checkbox'> Обновить превью?</label></span>\
  <span id='sdpict1' style='display:none;'>&nbsp;&nbsp;<a href='javascript:showpict(1)' class='blue'>Посмотреть загруженный файл</a>&nbsp;&nbsp;<a href='javascript:delpict(1)' title='Удалить'>[x]</a></span>\
  <br />С помощью этого поля возможно загрузить:<br />\
	Файл размером до 10 Мб. Флеш-файлы и картинки весом более 1 Мб открываются в новом окне.<br>\
	Файлы следующих форматов запрещены к загрузке: <?=implode(', ', $GLOBALS['disallowed_array'])?>
  <div id='preview' style='padding-top:10px;'>\
    Загрузить превью:<br />\
    <input type='file' id='sm_img' name='sm_img' size='111'>\
  <span id='sdpict2' style='visibility:hidden'>&nbsp;&nbsp;<a href='javascript:showpict(2)' class='blue'>Посмотреть загруженный файл</a>&nbsp;&nbsp;<a href='javascript:delpict(2)' title='Удалить'>[x]</a></span>\
    <br />С помощью этого поля возможно загрузить превью для закачиваемого файла.<br />\
<? if(!$is_pro) { ?>    <strong>Превью отображается только у пользователей с аккаунтом <a href='/payed/' class='b-layout__link'><span title='Платный аккаунт' class='b-icon b-icon__pro b-icon__pro_f'></span></a></strong><br>\<? } ?>
  	Формат: <?=implode(', ', $aAllowedExt )?>.<br />\
  	Максимальный размер файла: 100 Кб.\
  <\/div>\
  <? if ($alert[7]) { ?>"+errmsg7+"<? } ?>
	<\/td>\
<\/tr>\
<tr>\
	<td height='20' valign='bottom' colspan='2'>Ссылка:<\/td>\
<\/tr>\
<tr>\
	<td height='25'>\
		<table width='100%' border='0' cellspacing='0' cellpadding='0'>\
		<td><div class='b-input'><input type='text' id='link' name='link' class='b-input__text' maxlength='150' style='position:relative' value='http://' ><\/div><\/td><\/tr>\
		<\/table>\
  "+errmsg6+"<\/td>\
	<td>&nbsp;<\/td>\
<\/tr>\
<tr>\
	<td height='20' valign='bottom' colspan='2'>Описание<span style='font-weight:bold; color:#f00'>**</span> (для организации более удобного поиска вашей работы по сайту рекомендуем добавлять осмысленное описание не менее 300 символов):<\/td>\
<\/tr>\
<tr>\
	<td height='110' valign='top'><div class='b-textarea'><textarea cols='74' rows='7' name='descr' id='descr' class='b-textarea__textarea' onkeydown='if (this.value.length > 1500) this.value=this.value.slice(0, 1500)'><\/textarea><\/div>Можно использовать &lt;b&gt;&lt;i&gt;&lt;p&gt;&lt;ul&gt;&lt;li&gt;"+errmsg2+"<\/td>\
	<td align='right' style='padding-left:10px; padding-top:10px;'>Максимум<br /> 1500 символов<\/td>\
<\/tr>\
<tr>\
	<td colspan='2' style=padding-top:10px;>\
		<div class='b-radio b-radio_inline-block'><INPUT class='b-radio__input' checked='checked' name='prev_type' type='radio' value='0' id='prev_type1'><LABEL  class='b-radio__label b-radio__label_color_71 b-radio__label_fontsize_11' for='prev_type1'>Графическое превью<\/LABEL><\/div>&nbsp; &nbsp;\
		<div class='b-radio b-radio_inline-block'><INPUT class='b-radio__input' name='prev_type' type='radio' value='1' id='prev_type2'><LABEL  class='b-radio__label b-radio__label_color_71 b-radio__label_fontsize_11' for='prev_type2'>Текстовое превью</LABEL><\/div> &nbsp; &nbsp; \
		<div class='b-check b-check_ff_top_2 b-check_inline-block b-check_valign_top'><INPUT class='b-check__input' type='checkbox' name='in_shop' id='in_shop' value='1' onChange='toggle_shop();'><LABEL  class='b-check__label b-check__label_color_71 b-check__label_fontsize_11'>Добавить работу в магазин&#160;(<a class='b-layout__link' href='<?=WDCPREFIX?>/about/documents/appendix_2_regulations.pdf' target='_blank'>правила размещения работ в магазине<\/a>)<\/LABEL><\/div><\/td>\
<\/tr>\
<tr>\
	<td colspan='2'><table style='font-size:100%; margin: 10px 0 0 20px;'><tr>\
    <td style='width: 145px; padding: 0 0 10px;'>Раздел<span style='font-weight:bold; color:#f00'>**</span>: </td><td style='padding: 0 0 10px;'>\
        <select style='width: 300px;' name='shop_category' id='shop_category' disabled='true'><option value='0'>Выберите раздел</option>\
				<? foreach ($shop_categories as $ikey=>$cat) { ?>
				<option value='<?=$cat['id']?>'><?=$cat['name']?></option>\
				<? } ?>\
</select>"+errmsg208+"</td></tr><tr><td style='width: 145px; padding: 0 0 10px;'>Стоимость для продажи<span style='font-weight:bold; color:#f00'>**</span>:</td><td style='padding: 0 0 10px;'><select name='shop_cost_type' id='shop_cost_type' disabled='true'><option value='0'>USD<\/option><option value='1'>Euro<\/option><option value='2'>Руб<\/option></select> <input type='text' name='shop_cost' id='shop_cost' maxlength='6' disabled='true'/>"+errmsg100+"</td></tr><tr><td style='width: 145px; padding: 2px 0 10px; vertical-align:top;'>Теги (через запятую):</td><td style='padding: 0 0 10px;'>\
<textarea rows='2' cols='20' style='height: 32px; width: 670px;' name='shop_tags' id='shop_tags' disabled='true' onkeyup='if (!(event.ctrlKey || event.shiftKey)) tags_count()'></textarea>\
<div>Максимум 10 тегов по 20 символов в каждом</div>\
<div id='co-warn' class='errorBox' style='display: none'><img src='/images/ico_error.gif' alt='' height='18' width='22'>&nbsp;<span id='co-warn-text'>&nbsp;</span></div>\
</td></tr>\
<tr>\
	<td colspan='2'><span style='font-weight:bold; color:#f00'>*</span> Поля, обязательные для заполнения<br><span style='font-weight:bold; color:#f00'>**</span> Поля, обязательные для заполнения, при условии добавления работы в магазин<\/td>\
<\/tr>\
</table><\/td>\
<\/tr>\
<tr>\
	<td colspan='4' align='right' style='padding-top:8px;padding-bottom:8px;text-align:right;padding-right:120px;'>\
		<input type='button' name='btn' id='btn' class='btn' value='Удалить' onClick='if (warning(5)) {frm.action.value=\"portf_del\"; frm.submit();} else return(false);'>\
		<input type='button' name='btn_cancel' id='btn_canсel' class='btn' value='Отменить' onClick='cancelprj();'>\
		<input type='submit' name='btn1' id='btn1' class='btn' value='Сохранить'><\/td>\
<\/tr>\
<\/table>\
<\/form>\
\
\
<form action=\".\" method=\"post\" name=\"frm2\" id=\"frm2\" enctype=\"multipart/form-data\" onSubmit=\"this.btn2.value='Подождите'; this.btn2.disabled=true; this.btn12.value='Подождите'; this.btn12.disabled=true; this.btn_cancel2.value='Подождите'; this.btn_cancel2.disabled=true;\" style='display:none;'>\
<input type=\"hidden\" name=\"action\" id=\"action\" value=\"portf_change\">\
<input type=\"hidden\" name=\"u_token_key\" value=\"<?=$_SESSION['rand']?>\">\
<input type=\"hidden\" name=\"v_prof\" value=\"\">\
<input type=\"hidden\" name=\"v_prjid\" id=\"v_prjid\" value=\"\">\
<input type=\"hidden\" name=\"is_video\" id=\"is_video\" value=\"1\">\
<table width='100%' border='0' cellspacing='0' cellpadding='0' class='edit'>\
<tr>\
	<td rowspan='12' width='19'>&nbsp;<\/td>\
	<td height='25' colspan='2' id='ff2'><div style='padding-top:5px'><strong>Изменить работу<\/strong><\/div><\/td>\
	<td rowspan='10' width='19'>&nbsp;<\/td>\
<\/tr>\
<tr>\
	<td height='45' valign='middle' colspan='2'>\
    <select id='v_new_prof' name='v_new_prof' style='width:638px'>\
<?
foreach ($wrk_profs_names as $key => $cname)
{
  if(!$is_pro && ($key==professions::BEST_PROF_ID || $key==professions::CLIENTS_PROF_ID)) continue;
  if($key==professions::BEST_PROF_ID && $portf_cnt[professions::BEST_PROF_ID] >= portfolio::MAX_BEST_WORKS) continue;
  echo("<option value='$key'>".preg_replace('/\"/','\"',$cname)."</option>");
}
?>
    </select>\
	<\/td>\
<\/tr>\
<tr>\
	<td height='45' valign='middle' colspan='2'><div class='b-layout__txt b-layout__txt_inline-block b-layout__txt_fontsize_11 b-layout__txt_color_71'>Размещение в разделе:<\/div>\
    <div class='b-radio b-radio_top_-1 b-radio_inline-block'><input class='b-radio__input' type='radio' id='v_make_position_first' name='v_make_position' value='first' /><label class='b-radio__label b-radio__label_color_71 b-radio__label_fontsize_11' for=\"v_make_position_first\">поставить первой</label><\/div>\
    <div class='b-radio b-radio_top_-1 b-radio_inline-block'><input class='b-radio__input' type='radio' id='v_make_position_last' name='v_make_position' value='last' style='margin-left:16px' /><label class='b-radio__label b-radio__label_color_71 b-radio__label_fontsize_11' for=\"v_make_position_last\">поставить последней</label><\/div>\
    <div class='b-radio b-radio_top_-1 b-radio_inline-block'><input class='b-radio__input' type='radio' id='v_make_position_num' name='v_make_position' value='num' style='margin-left:16px' /><label class='b-radio__label b-radio__label_color_71 b-radio__label_fontsize_11' for=\"v_make_position_num\">сделать</label><\/div> <div class='b-input b-input_margtop_-2 b-input_inline-block b-input_width_45'><input class='b-input__text b-input__text_align_center' type='text' id='v_make_position_num' name='v_make_position_num' value='' onChange=\"document.getElementById('v_make_position_num').checked=true;\" /><\/div> <div class='b-layout__txt b-layout__txt_inline-block b-layout__txt_fontsize_11 b-layout__txt_color_71'>в разделе<\/div>\
	<\/td>\
<\/tr>\
<tr>\
	<td height='20' valign='bottom' colspan='2'>Название<span style='font-weight:bold; color:#f00'>*</span>:<\/td>\
<\/tr>\
<tr>\
	<td width='790' height='25'><input type='text' name='v_pname' id='v_pname' value='' maxlength='40' class='wdh100' onkeydown='if (this.value.length > 40) this.value=this.value.slice(0, 40)'>"+errmsg201+"<\/td>\
	<td align='right' style='padding-left:10px;'>Максимум<br /> 40 символов<\/td>\
<\/tr>\
<tr>\
	<td style='height:25px; vertical-align:middle;padding-top:20px;padding-bottom:12px;'>\
	Укажите стоимость разработки \
	<select name='v_pcosttype' id='v_pcosttype'><option value='0'>USD<\/option><option value='1'>Euro<\/option><option value='2'>Руб<\/option><\/select>\
	<input type='text' name='v_pcost' id='v_pcost' maxlength='10' style='width:65px;margin-right:16px;'> и временные затраты <input type='text' name='v_ptime' id='v_ptime' maxlength='3' style='width:50px;'> <select name='v_ptimeei' id='v_ptimeei'><option value='0'>в часах</option><option value='1'>в днях</option><option value='2'>в месяцах</option><option value='3'>в минутах</option></select>"+errmsg204+errmsg205+"\
	<\/td>\
	<td width='80' align='right'><\/td>\
<\/tr>\
<tr>\
	<td height='20' valign='bottom' colspan='2'>Вставьте в поле ниже ссылку, которую вы получили на видео хостинге YouTube, RuTube или Vimeo:<\/td>\
<\/tr>\
<tr>\
	<td height='25'>\
		<table width='100%' border='0' cellspacing='0' cellpadding='0'>\
		<tr><td width='40'>http://<span style='font-weight:bold; color:#f00'>*</span>&nbsp;<\/td>\
		<td><input type='text' id='v_video_link' maxlength='80' name='v_video_link' class='wdh100'><\/td><\/tr>\
		<tr><td width='40'>&nbsp;<\/td>\
		<td><div style='padding-bottom:10px;'>Внимание! Не используйте html код в поле ввода ссылки.<\/div><\/td><\/tr>\
		<\/table>\
  "+errmsg206+"<\/td>\
	<td>&nbsp;<\/td>\
<\/tr>\
<tr>\
	<td height='20' valign='bottom' colspan='2'>Описание:<\/td>\
<\/tr>\
<tr>\
	<td height='110' valign='top' ><textarea cols='74' rows='7' name='v_descr' id='v_descr' class='wdh100' onkeydown='if (this.value.length > 1500) this.value=this.value.slice(0, 1500)'><\/textarea>Можно использовать &lt;b&gt;&lt;i&gt;&lt;p&gt;&lt;ul&gt;&lt;li&gt;"+errmsg202+"<\/td>\
	<td align='right' style='padding-left:10px; padding-top:10px;'>Максимум<br /> 1500 символов<\/td>\
<\/tr>\
<tr>\
  <td height='auto' colspan='2'>\
  <div id='v_preview' style='padding-top:10px;'>\
    Загрузить превью:<br />\
    <input type='file' id='v_sm_img' name='v_sm_img' size='111'>\
  <span id='v_sdpict2' style='visibility:hidden'><a href='javascript:showpict(2)' class='blue'>Посмотреть загруженный файл</a>&nbsp;&nbsp;<a href='javascript:v_delpict(2)' title='Удалить'>[x]</a></span>\
    <br />С помощью этого поля возможно загрузить превью для закачиваемого файла.<br />\
<? if(!$is_pro) { ?>    <strong>Превью отображается только у пользователей с аккаунтом <a href='/payed/' class='b-layout__link'><span title='Платный аккаунт' class='b-icon b-icon__pro b-icon__pro_f'></span></a></strong><br />\<? } ?>
  	Формат: <?=implode(', ', $aAllowedExt )?>.<br />\
  	Максимальный размер файла: 100 Кб.\
  <\/div>\
  <? if ($alert[207]) { ?>"+errmsg207+"<? } ?>
	<\/td>\
<\/tr>\
<tr>\
	<td colspan='4'  style='padding-top:8px;padding-bottom:8px;text-align:right;padding-right:120px;'>\
		<input type='button' name='btn2' id='btn2' class='btn' value='Удалить' onClick='if (warning(5)) {frm2.action.value=\"portf_del\"; frm2.submit();} else return(false);'>\
		<input type='button' name='btn_cancel2' id='btn_canсel2' class='btn' value='Отменить' onClick='cancelprj();'>\
		<input type='submit' name='btn12' id='btn12' class='btn' value='Сохранить'><\/td>\
<\/tr>\
<\/table>\
</form>";
}

setform();

prjmpos  = new Array();
prjmpnum = new Array();

prjn = new Array();
prjname = new Array();
prjpict = new Array();
prjprevpict = new Array();
prjlink = new Array();
prjdescr = new Array();
prjcost = new Array();
prjcosttype = new Array();
prjtime = new Array();
prjtimeei = new Array();
prjid = new Array();
prjprevtype = new Array();
prjinshop = new Array();
prjshopcategory = new Array();
prjshopcosttype = new Array();
prjshopcost = new Array();
prjshoptags = new Array();
prjshopinfo = new Array();
prjisvideo = new Array();
prjvideolink = new Array();
prjvprevpict = new Array();

var proftxt = new Object();
prof_ids = new Array();
profnames = new Array();
prjinprof = new Array();


<?
	$ilast = $i = 0;
  $lastprof = NULL;
	$j = 0;

  if($prjs) {
    foreach($prjs as $ikey=>$prj) 
    {


        $d_shop = $DB->row("SELECT * FROM shop WHERE portfolio_id=?i", $prj['id']);
        if ( $d_shop ) {
            $d_shop = shop::GetItem($d_shop['id'],get_uid(), hasPermissions('shop'));
            $prj['shop_category'] = $d_shop['category'];
            $prj['shop_cost_type'] = $d_shop['currency'];
            $prj['shop_cost'] = $d_shop['prise'];
            $prj['shop_tags'] = $d_shop['tags'];
            //$prj['shop_info'] = $d_shop['addit'];
            if($prj['in_shop']=='t') { $prj['in_shop'] = 1; } else { $prj['in_shop'] = 0; }
        } else {
            $prj['in_shop'] = 0;
            $prj['shop_category'] = '';
            $prj['shop_cost_type'] = '';
            $prj['shop_cost'] = '';
            $prj['shop_tags'] = '';
            //$prj['shop_info'] = '';
        }

      if(!$is_pro && ($prj['prof_id']==professions::BEST_PROF_ID || $prj['prof_id']==professions::CLIENTS_PROF_ID)) continue;
      if ($prj['id']) {
        if($prj['is_video']=='t') $prj['is_video'] = 1;
        $_POST['make_position'] = intval($_POST['make_position']);
        $_POST['make_position_num'] = intval($_POST['make_position_num']);
        print ("prjmpos[$i]='{$_POST['make_position']}';\nprjmpnum[$i]='{$_POST['make_position_num']}';\nprjn[".$prj['id']."] = '".$i."';\nprjid[$i] = '".$prj['id']."';\nprjname[$i] = '".htmlspecialchars_decode(input_ref_scr($prj['name']))."';\nprjpict[$i] = '".input_ref_scr($prj['pict'])."';\nprjprevpict[$i] = '".input_ref_scr($prj['prev_pict'])."';\nprjlink[$i] = '".input_ref_scr($prj['link'])."';\nprjdescr[$i] = '".htmlspecialchars_decode(input_ref_scr($prj['descr'],true))."';\nprjcost[$i] = '".$prj['prj_cost']."';\nprjcosttype[$i] = '".$prj['prj_cost_type']."';\nprjtime[$i] = '".$prj['prj_time_value']."';\nprjtimeei[$i] = '".$prj['prj_time_type']."';\nprjprevtype[$i] = '".$prj['prj_prev_type']."';\nprjinshop[$i] = '".$prj['in_shop']."';\nprjshopcosttype[$i] = '".$prj['shop_cost_type']."';\nprjshopcost[$i] = '".$prj['shop_cost']."';\nprjshopcategory[$i] = '".$prj['shop_category']."';\nprjshoptags[$i] = '".input_ref_scr($prj['shop_tags'])."';\nprjshopinfo[$i] = '".input_ref_scr($prj['shop_info'])."';\nprjisvideo[$i] = '".$prj['is_video']."';\nprjvideolink[$i] = '".input_ref_scr($prj['video_link'])."';\nprjvprevpict[$i] = '".input_ref_scr($prj['prev_pict'])."';\n\n");
        $i++;
      }
      $curprof = $prj['prof_id'];
      if ($lastprof != $curprof) {
        // if ($lastprof != NULL && $i-$ilast > 1) print("prjinprof[".($j-1)."] = '".($i-$ilast)."';\n");
        // print ("prof_ids[$j] = '".$curprof['prof_id']."';\nprofnames[$j] = '".$curprof['name']."';\n");
        print ("proftxt['".$curprof."']='".($curprof==$prof ? ($prev_type ? 't' : 'f') : $prj['proftext'])."';\n");
        $j++;
        $ilast = $i;
        $lastprof = $curprof;
      }
    }
  }
  //if ($i-$ilast > 0) print("prjinprof[".($j-1)."] = '".($i-$ilast+1)."';\n");
?>

var prjnum = <?=$i?>;
var profnum = <?=$j?>;
var lastobj = 0;
<?
	if ($action == "portf_change" || $error_flag || $error){
 ?>
	//window.navigate("#prof<?=$prof?>");
<? } ?>

  function showpict(pt) {
    var aa,sdbox;
    if(!(sdbox = document.getElementById('sdpict'+pt)) || !sdbox.pict) return;
    if(pt==1)
      aa = 'status=no,toolbar=no,menubar=no,resizable=yes';
    else
      aa = 'height=230,width=230,status=no,toolbar=no,menubar=no,left=500,top=300,resizable=yes';
    window.open('<?=WDCPREFIX?>/users/<?=$user->login?>/upload/'+sdbox.pict, '_blank', aa);
  }

  function delpict(pt) {
    var sdbox;
    if(!(sdbox = document.getElementById('sdpict'+pt)) || !sdbox.pict || !prjid[sdbox.num]) return;
    if(warning(10)) {
      xajax_DelPict('<?=$user->login?>', prjid[sdbox.num], pt);
    }
  }

  function v_delpict(pt) {
    var sdbox;
    if(!(sdbox = document.getElementById('v_sdpict'+pt)) || !sdbox.pict || !prjid[sdbox.num]) return;
    if(warning(10)) {
      xajax_DelPict('<?=$user->login?>', prjid[sdbox.num], pt);
    }
  }

  function aftdelpict(pt) { try {
    if(!(sdbox = document.getElementById('sdpict'+pt))) return;
    sdbox.style.visibility = 'hidden';
    if(pt==2 && (pi=document.getElementById('previmg'+prjid[sdbox.num])) && pi.getAttribute('is_text')!=1)
      pi.innerHTML = '&nbsp;';
    if(pt==1)
      prjpict[sdbox.num] = '';
    else if(pt==2)
      prjprevpict[sdbox.num] = '';

    if(!(sdbox = document.getElementById('v_sdpict'+pt))) return;
    sdbox.style.visibility = 'hidden';
    if(pt==1)
      prjpict[sdbox.num] = '';
    else if(pt==2)
      prjvprevpict[sdbox.num] = '';
  } catch(e) { } }

  function editprj(num, profid) {
      var sdp1 = null;
      var sdp2 = null;
    cancelprj();
		ed=document.getElementById('editform'+profid);
    ed.innerHTML = getEdFrm(profid);
		ed.style.visibility='visible';
    ed.style.background="#FFE5D5";
    lastobj = 'editform'+profid;

		if (document.getElementById('ff'))
			document.getElementById('ff').innerHTML = "<div style='padding-top:5px'><strong>Изменить работу<\/strong><\/div>";

    var best_cnt = <?=intval($portf_cnt[professions::BEST_PROF_ID])?>;
    var best_cnt_max = <?=portfolio::MAX_BEST_WORKS?>;

    if(best_cnt >= best_cnt_max && profid != -3) {
        ed.innerHTML = edfrm.replace(/(<option\svalue='-3'>.*?<\/option>)/gi, '<!--$1-->');
    }
    if(sdp1 = document.getElementById('sdpict1')) {
      if(prjpict[num]) {
        sdp1.style.visibility = 'visible';
        sdp1.num=num;
        sdp1.pict=prjpict[num];
      }
    }
    
    if(uprv = document.getElementById('renew-prev')) {
        uprv.style.visibility = 'visible';
    }
    
    if(sdp2 = document.getElementById('sdpict2')) {
      if(prjprevpict[num]) {
        sdp2.style.visibility = 'visible';
        sdp2.pict=prjprevpict[num];
        sdp2.num=num;
      }
    }

    //if(prjmpos[num] != undefined) {
        //$('make_position_'+prjmpos[num]).set('checked', true);
    //} 

    //alert(profid);
    var _frm = document.getElementById('frm');
    _frm.pname.value = prjname[num];
    _frm.link.value = prjlink[num];
    _frm.descr.value = prjdescr[num];
    _frm.pcost.value = prjcost[num];
    _frm.ptime.value = prjtime[num];
    _frm.ptimeei.selectedIndex = prjtimeei[num];
    _frm.prjid.value = prjid[num];
    _frm.prof.value = profid;
    _frm.new_prof.value = profid;
    _frm.pcosttype.selectedIndex=prjcosttype[num];
    if (prjprevtype[num]==0)
      _frm.prev_type1.checked = true;
    else
      _frm.prev_type2.checked = true;


      if(prjinshop[num]==1) {
        _frm.in_shop.checked = true;
      } else {
        _frm.in_shop.checked = false;
      }
    toggle_shop();
    _frm.shop_category.value = prjshopcategory[num];
    _frm.shop_cost_type.selectedIndex = prjshopcosttype[num];
    _frm.shop_cost.value = prjshopcost[num];
    //_frm.shop_info.value = prjshopinfo[num];
    _frm.shop_tags.value = prjshoptags[num];



    if(prjisvideo[num]=='1') {
        if(v_sdp2 = document.getElementById('v_sdpict2')) {
          if(prjvprevpict[num]) {
            v_sdp2.style.visibility = 'visible';
            v_sdp2.pict=prjvprevpict[num];
            v_sdp2.num=num;
          }
        }

        var _frm2 = document.getElementById('frm2');
        _frm2.v_pname.value = prjname[num];
        _frm2.v_video_link.value = prjvideolink[num];
        _frm2.v_descr.value = prjdescr[num];
        _frm2.v_pcost.value = prjcost[num];
        _frm2.v_ptime.value = prjtime[num];
        _frm2.v_ptimeei.selectedIndex = prjtimeei[num];
        _frm2.v_prjid.value = prjid[num];
        _frm2.v_prof.value = profid;
        _frm2.v_new_prof.value = profid;
        _frm2.v_pcosttype.selectedIndex=prjcosttype[num];

        toggle_form('video');
		if (document.getElementById('ff2'))
			document.getElementById('ff2').innerHTML = "<strong>Изменить видео<\/strong>";

    }
<? if ($is_pro) {?>
    //add_preview_bv(prjpict[num]);
<? } ?>
	}
	
	function viewprof(profid){
    var _frm = document.getElementById('frm');
    _frm.pname.value = "";
    _frm.link.value = "";
    _frm.descr.value = "";
    _frm.prjid.value = "";
    var _frm2 = document.getElementById('frm2');
    _frm2.v_pname.value = "";
    _frm2.v_video_link.value = "";
    _frm2.v_descr.value = "";
    _frm2.v_prjid.value = "";
//   for (i = 0; i<prjnum; i++){
//				a1=document.getElementById('ap1'+i);
//				a2=document.getElementById('ap2'+i);
//				a1.style.fontWeight = 'normal';
//				a2.style.fontWeight = 'normal';
//       }
	}

    function toggle_form(form) {
        switch(form) {
            case 'video':
                document.getElementById('ff2').innerHTML = "<div style='padding-top:5px;'><strong><a href='' style='color:#003399;' onClick='toggle_form(\"work\"); return false;'>Добавить работу</a><\/strong>&nbsp;&nbsp;&nbsp;&nbsp;<strong>Добавить видео</strong></div>";
                document.getElementById('frm').style.display = 'none';
                document.getElementById('frm2').style.display = 'block';
                break;
            case 'work':
		        document.getElementById('ff').innerHTML = "<div style='padding-top:5px;'><strong>Добавить работу<\/strong>&nbsp;&nbsp;&nbsp;&nbsp;<strong><a href='' style='color:#003399;' onClick='toggle_form(\"video\"); return false;'>Добавить видео</a></strong></div>";
                document.getElementById('frm2').style.display = 'none';
                document.getElementById('frm').style.display = 'block';
                break;
        }
    }
	
	function addprj(profid){
    cancelprj();
		lastobj = 'editform'+profid;
		ed=document.getElementById('editform'+profid);
    ed.innerHTML = getEdFrm(profid);
		ed.style.backgroundColor="#FFF7DD";
		document.getElementById('ff').innerHTML = "<div style='padding-top:5px;'><strong>Добавить работу<\/strong>&nbsp;&nbsp;&nbsp;&nbsp;<strong><a href='' style='color:#003399;' onClick='toggle_form(\"video\"); return false;'>Добавить видео</a></strong></div>";
		ed.style.visibility='visible';
    var _frm = document.getElementById('frm');
    var _frm2 = document.getElementById('frm2');
    _frm.prev_type2.checked = !(_frm.prev_type1.checked = (proftxt[profid] != 't'));
    _frm.btn.style.visibility='hidden';
    _frm.prof.value = profid;
    _frm.new_prof.value = profid;
//    _frm2.v_prev_type2.checked = !(_frm2.v_prev_type1.checked = (proftxt[profid] != 't'));
    _frm2.btn2.style.visibility='hidden';
    _frm2.v_prof.value = profid;
    _frm2.v_new_prof.value = profid;
		viewprof(profid);
        $$('input#link').set('value', 'http://');
	}
	
  function cancelprj() {
    if (lastobj) {
      var lo = document.getElementById(lastobj);
      lo.innerHTML = "";
      lo.style.backgroundColor="#FFFFFF";
    }
	}
	
	
	function delprj(prjid){
		document.getElementById('frmdel' + prjid).submit();
	}
	
	function changeProfPos(profid, direc){
		if (can_move){
			can_move = 0;
			xajax_ChangeProfPos(profid, direc);
		}
	}
	
	function changePos(portfid, pid, direc){
		if (can_move){
			can_move = 0;
			xajax_ChangePos(pid, direc, '1', portfid);
		}
	}
	
	function submit_diz(val){
		document.getElementById('frmdiz').submit();
	}
	
	function changeTextPrev(pid, check){
		document.getElementById('prev'+pid).disabled = true;
		ch = 0;
		if (check) ch = 1;
		xajax_ChangeTextPrev(pid, ch);
	}
	
	function changeGrPrev(pid, check){
		document.getElementById('grprev'+pid).disabled = true;
		ch = 0;
		if (check) ch = 1;
		xajax_ChangeGrPrev(pid, ch);
	}
	
  function changePorftPrice(num){
  	cost = document.getElementById('prj_cost_'+num).value;
  	cost_type = document.getElementById('prj_cost_type_'+num).value;
  	time_type = document.getElementById('prj_time_type_'+num).value;
  	time_value = document.getElementById('prj_time_value_'+num).value;
  	xajax_ChangePortfPrice(num, cost, cost_type, time_type, time_value);
  }

	var selectRubric = new Array();

  	function selectRubricCount(profid, projid)
  	{
  		var action = "";

		if (typeof(selectRubric[profid]) == "undefined")
		{
			selectRubric[profid] = 0;
		}

  		if(document.getElementById('w_select_' + profid + '_' + projid).checked)
  		{
  			selectRubric[profid]++;
  			action = "add";
		}
  		else
  		{
  			selectRubric[profid]--;
  			action = "delete";
  		}

		xajax_ChangeProfCountSelected(profid, selectRubric[profid], action, projid);
  	}

  	function deleteRubricWorks(profid)
  	{
  		if (warning_str(document.getElementById('w_delete_' + profid).innerHTML))
  		{
  			document.getElementById('w_delete_prof').value = profid;
  			document.getElementById('form_del_all').submit();
  		}
  		else return(false);
  	}

  	function moveRubricWorks(profid)
  	{
  		var selectedRubric = document.getElementById('w_move_' + profid + '_select').options[document.getElementById('w_move_' + profid + '_select').selectedIndex].value;

      if (selectedRubric != 0)
  		{
	  		if (warning_str(document.getElementById('w_move_' + profid).innerHTML))
  			{
  				document.getElementById('w_move_prof_from').value = profid;
  				document.getElementById('w_move_prof_to').value = selectedRubric;
	  			document.getElementById('form_move_all').submit();
  			}
	  		else return(false);
		}
  		else return(false);
  	}
//-->
</script>
<?

if (isset($action) && $action == 'serv_change' && ($error_serv != ''))
{
  $frm_serv_val['tab_name_id'] = floatval($tab_name_id);
  $frm_serv_val['exp'] = floatval($exp);
	$frm_serv_val['cost_hour'] = $cost_hour;
	$frm_serv_val['cost_month'] = $cost_month;
	$frm_serv_val['cost_type_hour'] = $cost_type_hour;
	$frm_serv_val['cost_type_month'] = $cost_type_month;
	$frm_serv_val['text'] = $text;
	$frm_serv_val['in_office'] = $in_office;
	$frm_serv_val['prefer_sbr'] = $prefer_sbr;
}
else
{
  $frm_serv_val['tab_name_id'] = $user->tab_name_id;
  $frm_serv_val['exp'] = $user->exp;
	$frm_serv_val['cost_hour'] = $user->cost_hour;
	$frm_serv_val['cost_month'] = $user->cost_month;
	$frm_serv_val['cost_type_hour'] = $user->cost_type_hour;
	$frm_serv_val['cost_type_month'] = $user->cost_type_month;
	$frm_serv_val['text'] = $user->spec_text;
	$frm_serv_val['in_office'] = $user->in_office;
	$frm_serv_val['prefer_sbr'] = $user->prefer_sbr;
}

?>

<form action="." method="post" name="form_del_all" id="form_del_all">
<div>
<input type="hidden" name="action" id="action" value="portf_del_all" />
<input type="hidden" name="w_delete_prof" id="w_delete_prof" value="0" />
</div>
</form>

<form action="." method="post" name="form_move_all" id="form_move_all">
<div>
<input type="hidden" name="action" id="action" value="portf_move_all" />
<input type="hidden" name="w_move_prof_from" id="w_move_prof_from" value="0" />
<input type="hidden" name="w_move_prof_to" id="w_move_prof_to" value="0" />
</div>
</form>

<form action="." method="post" enctype="multipart/form-data" name="frm_serv" id="frm_serv" onSubmit="if(tawlFormValidation(this)){this.btn.value='Подождите'; this.btn.disabled=true;}else{return false;}">
<div>
<input type="hidden" name="action" value="serv_change" />
<input type="hidden" name="prjid" value="" />
<? if ($error_serv) { ?><div style="padding:16px 32px 16px 32px;"><? print(view_error($error_serv));?></div><? } ?>
<? if ($info_serv) { ?><div style="padding:8px 0px 0px 26px;"><?=view_info($info_serv)?></div><? } ?>
<table style="width:100%" cellspacing="0" cellpadding="0">
<tr>
	<td style="width:100%;height:60px;padding-left:32px;padding-top:10px">
    <div class="b-select b-select_inline-block">
        <label class="b-select__label" for="b-select__select">Выберите название закладки:</label>
        <select id="tab_name_id" class="b-select__select b-select__select_inline-block b-select__select_width_110" name="tab_name_id">
          <option value="0"<? if ($frm_serv_val['tab_name_id'] == 0) { ?> selected='selected'<? } ?>>Портфолио</option>
          <option value="1"<? if ($frm_serv_val['tab_name_id'] == 1) { ?> selected='selected'<? } ?>>Услуги</option>
        </select>
    </div>
	</td>
	<td style="white-space:nowrap;padding-right:16px;padding-top:10px"><img src="/images/ico_setup.gif" alt="" width="6" height="9" />&nbsp;&nbsp;<a class="blue" href="/users/<?=$user->login?>/setup/portfsetup/">Изменить разделы</a></td>

</tr>
<tr>
	<td colspan="2" style="width:100%;padding-left:32px;padding-right:32px;">
        <a class="blue" href="/users/<?=$user->login?>/setup/specsetup/" id="ap11">Специализация:</a>&nbsp;&nbsp;<?=professions::GetProfNameWP($user->spec, ' / ', 'Нет специализации')?>
    </td>
</tr>
<?php

$specs_add = array();
if ($is_pro) {
    $specs_add = $prfs->GetProfsAddSpec(get_uid());
}

if (!empty($specs_add)) {
    $specs_add_array = array();

    for ($si = 0; $si<sizeof($specs_add); $si++) {
        $specs_add_array[$si] = professions::GetProfNameWP($specs_add[$si], ' / ');
    }

    $specs_add_string = join(", ", $specs_add_array);
} else {
    $specs_add_string = "Нет";
}

?>
<tr>
	<td colspan="2" style="width:100%;padding-left:32px;padding-right:32px; padding-top:12px;"><a class="blue" href="/users/<?=$user->login?>/setup/specaddsetup/" id="ap11">Дополнительные специализации:</a>&nbsp;&nbsp;<?=$specs_add_string?></td>
</tr>
<tr>

	<td colspan="2" style="padding-left:32px;padding-right:32px; padding-top:20px; padding-bottom:8px;">
            <?php //var_dump($user);?>
  <div class="b-check">         
		<input name="cat_show" class="b-check__input" type="checkbox" value="1" <?= !is_pro() ? 'disabled="disabled"' : '' ?> <?= $user->cat_show == 't' || !is_pro() ? 'checked="checked"' : '' ?> id="cat_showl" /> 
  <label class="b-check__label b-check__label_color_71" for="cat_showl"><strong class="b-layout__txt_bold">Разрешить размещение в каталоге</strong> <span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_color_71 b-layout__txt_lineheight_1">(только для <span title="владельцев платного аккаунта" class="b-icon b-icon__pro b-icon__pro_f b-icon_valign_bas"></span>)</span></label>
  </div>
	</td>

</tr>


<?

//}

?>

</table>

<table  cellspacing="0" cellpadding="0" style="margin-top:4px;width:100%">
<tr>

  <td style="height:30px;padding-left:32px;white-space:nowrap;">Опыт работы (в годах)</td>
	<td style="padding-left:6px;"></td>
	<td style="padding-left:6px;padding-right:6px;"><input type="text" name="exp" value="<?=$frm_serv_val['exp']?>" maxlength="2" style="width:60px;" /></td>
	<td style="padding-right:32px;"></td>
</tr>
<tr>
	<td style="height:30px;padding-left:32px;white-space:nowrap;">Укажите стоимость часа вашей работы</td>
	<td style="padding-left:6px;">
	                    <div class="b-select">
                        <select id="cost_type_hour" class="b-select__select" name="cost_type_hour">
                            <option value="0" <?=($frm_serv_val['cost_type_hour'] == 0 ? "selected='selected'" : "")?> >USD</option>
                            <option value="1" <?=($frm_serv_val['cost_type_hour'] == 1 ? "selected='selected'" : "")?>>Euro</option>
                            <option value="2" <?=($frm_serv_val['cost_type_hour'] == 2 ? "selected='selected'" : "")?>>Руб</option>
                        </select>
	                    </div>
	</td>
	<td style="padding-left:6px;padding-right:6px;"><input type="text" id="cost_hour" name="cost_hour" value="<?=floatval($frm_serv_val['cost_hour'])?>" maxlength="6" style="width:60px;" /></td>
	<td rowspan="2" style="padding-right:32px;"> &mdash; данные цены будут выводиться на вашей странице и в общем каталоге</td>
</tr>
<tr>
	<td style="padding-left:32px;white-space:nowrap;">Укажите стоимость месяца вашей работы</td>
	<td style="padding-left:6px; height:22px;">
	
	                    <div class="b-select">
                        <select id="cost_type_month" class="b-select__select" name="cost_type_month">
                            <option value="0" <?=($frm_serv_val['cost_type_month'] == 0 ? "selected='selected'" : "")?> >USD</option>
                            <option value="1" <?=($frm_serv_val['cost_type_month'] == 1 ? "selected='selected'" : "")?>>Euro</option>
                            <option value="2" <?=($frm_serv_val['cost_type_month'] == 2 ? "selected='selected'" : "")?>>Руб</option>
                        </select>
	                    </div>
	
	</td>
	<td style="padding-left:6px;padding-right:6px;"><input type="text" id="cost_month" name="cost_month" value="<?=floatval($frm_serv_val['cost_month']);?>" maxlength="6" style="width:60px;" /></td>
</tr>
<tr>
	<td style="height: 30px; padding-left: 32px; white-space: nowrap;">
    <div class="b-check">
      <input type="checkbox" id="in_officel" name="in_office" value="1" <?=$frm_serv_val['in_office']=="t"?" checked='checked'":""?> class="b-check__input" />
      <label class="b-check__label b-check__label_bold b-check__label_color_71" for="in_officel">Ищу долгосрочную работу <span style="display:inline-block; vertical-align: baseline; line-height:1; padding: 0 0 0 15px; background: url(/images/icons-sprite.png) no-repeat -100px -337px;">в офисе</span> </label>
    </div>
 </td>
	<td colspan="3" style="padding-left: 6px;">
		<?/*<select  style="width: 155px;"" id="pf_country" name="w_country" onChange="cityUpd(this.value)">
            <? foreach ($countries as $countid => $country): ?>
            <option value="<?=$countid?>" <? if ($countid == $mFilter['country']) echo(" selected='selected'") ?> ><?=$country?></option>
            <? endforeach; ?>
        </select>&nbsp;&nbsp;&nbsp;
        <span id="frm_city">
            <select style="width: 155px;"" name="city" id="f_city">
                <? foreach ($cities as $cityid => $city): ?>
                <option value="<?=$cityid?>"<? if ($cityid == $mFilter['city']) echo(" selected='selected'") ?>><?=$city?></option>
                <? endforeach; ?>
            </select>
        </span>*/?>
	</td>
</tr>
<?/* #0019741
<tr>
	<td style="padding-left: 32px; white-space: nowrap;"><label for="prefer_sbr"><strong><input type="checkbox" id="prefer_sbr" name="prefer_sbr" value="1" <?=$frm_serv_val['prefer_sbr']=="t"?"checked='checked'":""?> class="i-chk" style="position:relative; top:-1px;" /> Предпочитаю работать через сервис <span class="sbr-ic">Сделка без риска</span></strong></label></td>
	<td colspan="3" style="padding-left: 6px;"></td>
</tr>
 */ ?>
</table>

<table  cellspacing="0" cellpadding="0" style="margin-top:20px; width:100%">
<tr>
	<td style="width:890px;height:60px;padding-left:32px;padding-right:32px;">Уточнения к услугам в портфолио:<br />
    <textarea class="tawl" rel="<?=$ab_text_max_length?>" cols="60" rows="7" id="ab_text" name="ab_text" style="width:890px; height:100px;"><?=input_ref($frm_serv_val['text'])?></textarea>
	</td>
</tr>
<tr>
	<td style="width:100%;height:60px;padding-right:32px;text-align:right;"><input id="btn" name="btn" type="submit" value="Изменить" />
	</td>
</tr>
</table>
</div>
</form>
<? /*
<table width="100%"  cellspacing="0" cellpadding="0">
<tr>
	<td width="19" height="40">&nbsp;</td>
<? if ($is_pro) {?>	<td align="right">
  	<form action="." method="post" name="frmdiz" id="frmdiz">
    <div>
    <input type="hidden" name="action" id="action" value="diz_ch" />
    <input type="checkbox" class="checkbox" id="design" name="stddiz" value="0" onClick="submit_diz(this.value);" <? if (!$user->design) print "checked='checked'"?> /> Стандартный дизайн портфолио
    </div>
    </form>
  </td><? } ?>
	<td width="19">&nbsp;</td>
</tr>
</table>
*/?>

<? $kwords = new kwords(); ?>




<?
    $lastprof = -1;
    $j = 0;
    $k = -1;
    if ($prjs) foreach($prjs as $ikey=>$prj)
    {
      if((int)$prj['prof_id'] == 0 || (!$is_pro && ($prj['prof_id']==professions::BEST_PROF_ID || $prj['prof_id']==professions::CLIENTS_PROF_ID))) continue;

      $old_error_reporting = error_reporting();
      error_reporting(0);
      if(@$prj['prof_id'] > 0) $prof[$prj['prof_id']] = $prj['prof_id'];
      error_reporting($old_error_reporting);      

      $user_keys = $kwords->getUserKeys(get_uid(), $prj['prof_id']);  
      
			$curprof = $prj['prof_id'];
			if ($lastprof != $curprof) {
				$i = 1;
				$k++;
				if ($lastprof != -1) {
				?>
				</table>
		</div>
				<? }
        if (isset($action) && $action == 'prof_change' && isset($saved_prof_id) && ($prj['prof_id'] == $saved_prof_id) && ($error_prof != ''))
        {
  				$prj['cost_hour']  = $_POST['prof_cost_hour'];
  				$prj['cost_1000']  = $_POST['prof_cost_1000'];
  				$prj['cost_type_hour']  = $_POST['prof_cost_type_hour'];
  				$prj['cost_type']  = $_POST['prof_cost_type'];
  				$prj['cost_from']  = $_POST['prof_cost_from'];
  				$prj['cost_to']    = $_POST['prof_cost_to'];
  				$prj['time_type']  = $_POST['prof_time_type'];
  				$prj['time_from']  = $_POST['prof_time_from'];
  				$prj['time_to']    = $_POST['prof_time_to'];
        }
        else
        {
  				$prj['cost_hour'] = floatval($prj['cost_hour']);
  				$prj['cost_1000'] = floatval($prj['cost_1000']);
  				$prj['cost_type_hour']  = intval($prj['cost_type_hour']);
  				$prj['cost_type']  = intval($prj['cost_type']);
  				$prj['cost_from'] = floatval($prj['cost_from']);
  				$prj['cost_to']   = floatval($prj['cost_to']);
  				$prj['time_type'] = intval($prj['time_type']);
  				$prj['time_from'] = intval($prj['time_from']);
  				$prj['time_to']   = intval($prj['time_to']);
        }

        $wrk_show_preview = $prj['gr_prevs'];
				?>
<div id="sprof<?=$prj['prof_id']?>">
<a name="prof<?=$curprof?>" id="prof<?=$curprof?>"></a>
<form action="#prof<?=$curprof?>" method="post" enctype="multipart/form-data" name="frm_prof_<?=$prj['prof_id']?>" id="frm_prof_<?=$prj['prof_id']?>" onSubmit="this['btn_prof_<?=$prj['prof_id']?>'].value='Подождите'; this['btn_prof_<?=$prj['prof_id']?>'].disabled=true;">
<div>
<input type="hidden" name="action" value="prof_change" />
<input type="hidden" name="prof_id" value="<?=$prj['prof_id']?>" />
<? if ($prj['proftext'] == 't') { ?>
<input type="hidden" name="prof_cost_type" value="<?=$prj['cost_type']?>" />
<input type="hidden" name="prof_cost_type_hour" value="<?=$prj['cost_type_hour']?>" />
<input type="hidden" name="prof_cost_hour" value="<?=$prj['cost_hour']?>" />
<input type="hidden" name="prof_cost_from" value="<?=$prj['cost_from']?>" />
<input type="hidden" name="prof_cost_to" value="<?=$prj['cost_to']?>" />
<input type="hidden" name="prof_time_type" value="<?=$prj['time_type']?>" />
<input type="hidden" name="prof_time_from" value="<?=$prj['time_from']?>" />
<input type="hidden" name="prof_time_to" value="<?=$prj['time_to']?>" />
<? } else { ?>
<input type="hidden" name="prof_cost_1000" value="<?=$prj['cost_1000']?>" />
<? } ?>

<table style="width:100%"  cellspacing="0" cellpadding="0">
<tr>
  <td class="brdtop" style="width:20px;vertical-align:top;padding:8px 0px 0 12px;<?=($prj['prof_id']==professions::BEST_PROF_ID||$prj['prof_id']==professions::CLIENTS_PROF_ID?'background:#ffeda9':'')?>"><?
      if ($prj['wrk_pos_start']) { ?><img id="icoup<?=$curprof?>" src="/images/ico_up0.gif" alt="" width="9" height="9"  onClick="changeProfPos(<?=$prj['prof_id']?>, '-1');" /><?} else { ?><img id="icoup<?=$curprof?>" src="/images/ico_up.gif" alt="" width="9" height="9"  onClick="changeProfPos(<?=$prj['prof_id']?>, '-1');" /><? } ?><br /><?
      if ($prj['wrk_pos_end']) { ?><img id="icodn<?=$curprof?>" src="/images/ico_down0.gif" alt="" width="9" height="9"  style="margin-top:2px;" onClick="changeProfPos(<?=$prj['prof_id']?>, '1');" /><? } else { ?><img id="icodn<?=$curprof?>" src="/images/ico_down1.gif" alt="" width="9" height="9"  style="margin-top:2px;" onClick="changeProfPos(<?=$prj['prof_id']?>, '1');" /><? } ?></td>
  <td class="brdtop" style="padding-right:22px;padding-top:4px;<?=($prj['prof_id']==professions::BEST_PROF_ID||$prj['prof_id']==professions::CLIENTS_PROF_ID?'background:#ffeda9':'')?>">
    <table style="width:100%"  cellspacing="0" cellpadding="0">
    <tr>
      <td style="width:60%"><h1><?=$prj['profname']?></h1></td>
      <td rowspan="3" style="vertical-align:top;text-align:right;padding-top:8px;">
        <table style="width:322px" cellspacing="0" cellpadding="0">
<? if ($prj['proftext'] == 't') { ?>
        <tr>

        	<td style="width:100%;"></td>
        	<td style="padding-top:4px;white-space:nowrap;">Стоимость тысячи знаков</td>
        	<td style="padding-left:6px;padding-top:4px;">
        	

                        <select name="prof_cost_type" id="prof_cost_type">
                            <option value="0" <?=($prj['cost_type'] == 0 ? "selected='selected'" : "")?> >USD</option>
                            <option value="1" <?=($prj['cost_type'] == 1 ? "selected='selected'" : "")?>>Euro</option>
                            <option value="2" <?=($prj['cost_type'] == 2 ? "selected='selected'" : "")?>>Руб</option>
                        </select>

        	</td>
        	<td style="padding-left:6px;padding-right:6px;padding-top:4px;"><input type="text" id="prof_cost_1000" name="prof_cost_1000" value="<?=$prj['cost_1000']?>" maxlength="6" style="width:60px"/></td>
        </tr>
         <tr>
            <td style="width:100%;"></td>
            <td style="padding-top:4px;white-space:nowrap;">Оценка часа работы</td>
        	<td style="padding-left:6px;padding-top:4px;">
        	             <div class="b-select">
                        <select id="prof_cost_type_hour" class="b-select__select" name="prof_cost_type_hour">
                            <option value="0" <?=($prj['cost_type_hour'] == 0 ? "selected='selected'" : "")?> >USD</option>
                            <option value="1" <?=($prj['cost_type_hour'] == 1 ? "selected='selected'" : "")?>>Euro</option>
                            <option value="2" <?=($prj['cost_type_hour'] == 2 ? "selected='selected'" : "")?>>Руб</option>
                        </select>
        	             </div>
        	</td>
        	<td style="padding-left:6px;padding-right:6px;padding-top:4px;"><input type="text" id="prof_cost_hour" name="prof_cost_hour" value="<?=$prj['cost_hour']?>" maxlength="5" style="width:60px"/></td>
        </tr>
<? } else { ?>
        <tr>
        	<td style="border-bottom:1px #f0f4ff solid;padding:6px 0 6px 0;white-space:nowrap;">Стоимость работ
        	             <div class="b-select">
                        <select id="prof_cost_type" class="b-select__select" name="prof_cost_type">
                            <option value="0" <?=($prj['cost_type'] == 0 ? "selected='selected'" : "")?> >USD</option>
                            <option value="1" <?=($prj['cost_type'] == 1 ? "selected='selected'" : "")?>>Euro</option>
                            <option value="2" <?=($prj['cost_type'] == 2 ? "selected='selected'" : "")?>>Руб</option>
                        </select>
                      </div>
        	</td>
        	<td style="border-bottom:1px #f0f4ff solid;padding:6px 0 6px 6px;text-align:right;white-space:nowrap;">от </td>
        	<td style="border-bottom:1px #f0f4ff solid;padding:6px 4px 6px 6px;"><input type="text" id="prof_cost_from" name="prof_cost_from" value="<?=$prj['cost_from']?>" maxlength="10" style="width:60px"/></td>
        	<td style="border-bottom:1px #f0f4ff solid;padding:6px 0 6px 6px;text-align:right;white-space:nowrap;">до </td>
        	<td style="border-bottom:1px #f0f4ff solid;padding:6px 0px 6px 6px;"><input type="text" id="prof_cost_from" name="prof_cost_to" value="<?=$prj['cost_to']?>" maxlength="10" style="width:60px"/></td>
        </tr>
        <tr>
        	<td style="border-bottom:1px #f0f4ff solid;padding:6px 0 6px 0;white-space:nowrap;">Сроки &nbsp;&nbsp;&nbsp;<select id="prof_time_type" name="prof_time_type"><option value='0'<? if ($prj['time_type']==0) { ?> selected="selected"<? } ?>>в часах</option><option value='1'<? if ($prj['time_type']==1) { ?> selected="selected"<? } ?>>в днях</option><option value='2'<? if ($prj['time_type']==2) { ?>  selected="selected"<? } ?>>в месяцах</option><option value='3'<? if ($prj['time_type']==3) { ?> selected="selected"<? } ?>>в минутах</option></select></td>
        	<td style="border-bottom:1px #f0f4ff solid;padding:6px 0 6px 6px;text-align:right;">от</td>
        	<td style="border-bottom:1px #f0f4ff solid;padding:6px 6px 6px 6px;"><input type="text" id="prof_time_from" name="prof_time_from" value="<?=$prj['time_from']?>" maxlength="2" style="width:60px" /></td>
        	<td style="border-bottom:1px #f0f4ff solid;padding:6px 0 6px 6px;text-align:right;">до</td>
        	<td style="border-bottom:1px #f0f4ff solid;padding:6px 0px 6px 6px;"><input type="text" id="prof_time_to" name="prof_time_to" value="<?=$prj['time_to']?>" maxlength="2" style="width:60px" /></td>
        </tr>
        <tr>
        	<td style="padding:6px 0 6px 0;white-space:nowrap;" colspan="2">Оценка часа работы
        	
        	             <div class="b-select">
                        <select class="b-select__select" name="prof_cost_type_hour" id="prof_cost_type_hour">
                            <option value="0" <?=($prj['cost_type_hour'] == 0 ? "selected='selected'" : "")?>  >USD</option>
                            <option value="1" <?=($prj['cost_type_hour'] == 1 ? "selected='selected'" : "")?>>Euro</option>
                            <option value="2" <?=($prj['cost_type_hour'] == 2 ? "selected='selected'" : "")?>>Руб</option>
                        </select>
                      </div>
        	
        	</td>
        	<td style="padding:6px 6px 6px 6px;"><input type="text" id="prof_cost_hour" name="prof_cost_hour" value="<?=$prj['cost_hour']?>" maxlength="5" style="width:60px" /></td>
        	<td style="padding:6px 0 6px 6px;"></td>
        	<td style="padding:6px 0 6px 6px;"></td>
        	<td style="padding:6px 0px 6px 6px;"></td>
        </tr>
<? } ?>
        </table>
      </td>
    </tr>
    <tr>
      <td>Уточнения к разделу:</td>
    </tr>
    <tr>
    <? $profText = ($errorProfText && $saved_prof_id == $prj['prof_id']) ? $errorProfText : $prj['portf_text']; ?>
      <td style="padding-top:0px;padding-right:12px;vertical-align:right; padding-bottom:10px;"><textarea cols="20" rows="4" id="prof_text" name="prof_text" style="width:90%;height:56px;" onkeyup="if (this.value.length > 300) this.value=this.value.slice(0, 300)"><?=input_ref($profText)?></textarea><br />Можно использовать &lt;b&gt;&lt;i&gt;&lt;p&gt;&lt;ul&gt;&lt;li&gt;</td>
    </tr>
    <? if($prj['prof_id'] > 0): ?>
    <tr>
      <td style="padding: 0 0 5px 0;"><br />Ключевые слова:</td>
    </tr>
    <tr>
      <td style="padding-top:0px;padding-right:12px;vertical-align:right;">
            <div style="position:   relative;" id="body_<?=$prj['prof_id']?>"> 
			     <textarea  cols="20" rows="2" name="prof_keys[<?=$prj['prof_id']?>]" id="user_keys_<?=$prj['prof_id']?>" style="width:90%; height:36px;"><?=stripcslashes(implode(", ", $user_keys))?></textarea>
	        </div>
	        Ключевые слова вводятся через запятую.
      </td>
    </tr>
    <? endif; ?>
    <tr>
      <td>
        <? if ($is_pro) { ?> <span style="margin-right:32px;"><div class="b-check"><input id="grprev<?=$curprof?>" class="b-check__input" type="checkbox"  value="1" onClick="changeGrPrev(<?=$curprof?>, this.checked);" <? if ($prj['gr_prevs'] == 't') print "checked='checked'" ?> <? if (!$is_pro) print " disabled='disabled'" ?> /><label class="b-check__label b-check__label_bold" for="grprev<?=$curprof?>">Включить в разделе превью</label><? if (!$is_pro) { ?> <div style="padding:2px 2px 2px 4px;background-color:#FFE4C4;">Только для <a class="b-layout__link" href="/payed/"><span title="владельцев платного аккаунта" class="b-icon b-icon__pro b-icon__pro_f"></span></a></div> <? } } ?></div></span>
      </td>
      <td style="padding:8px 0px 12px 0px;text-align:right;"><input type="submit" id="btn_prof_<?=$prj['prof_id']?>" name="btn_prof_<?=$prj['prof_id']?>" value="Сохранить" /></td>
    </tr>
    </table>
  </td>

  </tr>
  </table>
  <table cellspacing="0" cellpadding="0" style="width:100%;background:<?=($prj['prof_id']==professions::BEST_PROF_ID||$prj['prof_id']==professions::CLIENTS_PROF_ID?'#fff9e3':'#eef2fb')?>">
  <tr>
    <td style="padding:6px 12px 6px 28px;text-align:left;vertical-align:middle;">
      <? if ($error_prof && $saved_prof_id == $prj['prof_id']) print(view_error($error_prof)); ?>
      <? if ($info_prof && $saved_prof_id == $prj['prof_id']) { ?><?=view_info($info_prof)?><? } ?>
      &nbsp;
    </td>
    <td style="padding:6px 4px 6px 6px;text-align:right;vertical-align:middle;">
      <? if($portf_cnt[professions::BEST_PROF_ID] >= portfolio::MAX_BEST_WORKS && $prj['prof_id']==professions::BEST_PROF_ID) { ?>
        Вы не можете добавить больше <?=portfolio::MAX_BEST_WORKS?> работ в этот раздел.
      <? } else { ?>
        <a href="#prof<?=$curprof?>" onClick="addprj('<?=$curprof?>');">
          <img src="/images/btnadd.gif" alt="Добавить работу" width="169" height="28" />
        </a>
      <? } ?>
    </td>
  </tr>
  <tr>
	<td colspan="2">
    <table   cellspacing="0" cellpadding="0" style="background:#F8F8F8; width:100%">
	  <tr>
	  	<td style="width:100%"><div id="w_count_selected_<?=$curprof?>" style="font-weight:bold; padding:21px 30px 21px 30px;">Выделено 0 работ</div></td>

<?
		if (sizeof($wrk_profs_names) > 1)
		{

?>

	  	<td>
	  		<div style="background:#E4E4E4; height:28px; padding:3px 5px 0px 5px;">
			  <table  style="width:100%"  cellspacing="0" cellpadding="2">
			  <tr>
			  	<td style="white-space:nowrap; vertical-align:middle;">Переместить в&#160;</td>
			  	<td>
			  		<select  disabled="disabled" id="w_move_<?=$curprof?>_select">
			  		<option value="0">Выберите раздел...</option>

			  		<?
			  		
			  		foreach($wrk_profs_names as $wkey=>$wvalue)
			  		{
                        if(!$is_pro && ($wkey==professions::BEST_PROF_ID || $wkey==professions::CLIENTS_PROF_ID)) continue;
                        if($wkey==professions::BEST_PROF_ID && $portf_cnt[professions::BEST_PROF_ID] >= portfolio::MAX_BEST_WORKS) continue;
			  			if ($wkey != $curprof)
			  			{
			  		
			  		?>

			  		<option value="<?=$wkey?>"><?=$wvalue?></option>
			  		
			  		<?
			  			}
			  		}
			  		
			  		?>

			  		</select>
			  	</td>
			  	<td><input type="button"  disabled="disabled" id="w_move_<?=$curprof?>_btn" value=" OK " onClick="moveRubricWorks(<?=$curprof?>)" /><div style="display:none;" id="w_move_<?=$curprof?>">Удалить все выделенные работы?</div></td>
			  </tr>
			  </table>
			</div>
	  	</td>

<?
		}

?>
	  	<td><div style="background:#E4E4E4; height:30px; padding:5px 0px 0px 0px; margin:0px 5px 0px 10px;"><input type="button" disabled="disabled"  id="w_delete_<?=$curprof?>_btn" value="Удалить все выделенные работы" onClick="deleteRubricWorks(<?=$curprof?>)" /><div style="display:none;" id="w_delete_<?=$curprof?>">Удалить все выделенные работы?</div></div></td>
	  </tr>
	  </table>
	</td>
  </tr>
<!--
	<tr>
	<td width="19" height="20" class="brdtop">&nbsp;</td>
	<td width="19" height="20" class="brdtop">&nbsp;</td>
	<td align="right" class="brdtop"><img src="/images/ico_plus.gif" alt="" width="9" height="9" />&nbsp;&nbsp;<a href="#prof<?=$curprof?>" onClick="addprj('<?=$curprof?>');">Добавить работу</a></td>
</tr>
-->
</table>
</div>
</form>

<div id="editform<?=$curprof?>" style="visibility: hidden;"></div>

<table cellspacing="0" cellpadding="4" style="width:100%">
<? if ($prj['id']) { ?>
<?	} else { ?>
<tr>
	<td style="text-align:center; height:20px">В этом разделе нет работ</td>
</tr>
<? }
        $end_table='</table>';
		$lastprof = $curprof;
			}
			if ($prj['id']) {
				if ($error_flag && $prj_id == $prj['id']) $errprjnum = $j;
				$prj['prj_cost'] = floatval($prj['prj_cost']);
		?>
<tr>
  <td id="sproj<?=$prj['id']?>a" style="padding-right:0px;padding-left:8px;padding-bottom:0px;" class="boxbt">
		<table cellspacing="0" cellpadding="0"  style="width:100%;margin-right:0px;margin-left:0px;">
		<tr valign="top">
      <td rowspan="3" style="width:6px;padding:7px 0px 2px 16px;"><input type="checkbox" id="w_select_<?=$curprof?>_<?=$prj['id']?>" name="w_select_<?=$curprof?>_<?=$prj['id']?>" value="1" onclick="selectRubricCount(<?=$curprof?>, <?=$prj['id']?>)" /></td>
      <td  rowspan="3" style="width:6px;padding:7px 20px 2px 3px;">
      <? if ($prj['prj_pos_start']) { ?><img id="icoupw<?=$prj['id']?>" src="/images/ico_up0.gif" style="margin-top:2px;" alt="" width="9" height="9"  onClick="changePos(<?=$curprof?>, <?=$prj['id']?>, '-1');" /><? } else { ?><img id="icoupw<?=$prj['id']?>" src="/images/ico_up.gif" style="margin-top:2px;" alt="" width="9" height="9"  onClick="changePos(<?=$curprof?>, <?=$prj['id']?>, '-1');"/><? } ?><br />
      <? if ($prj['prj_pos_end']) { ?><img id="icodnw<?=$prj['id']?>" src="/images/ico_down0.gif" style="margin-top:2px;" alt="" width="9" height="9"  onClick="changePos(<?=$curprof?>, <?=$prj['id']?>, '1');"/><? } else { ?><img id="icodnw<?=$prj['id']?>" src="/images/ico_down1.gif" style="margin-top:2px;" alt="" width="9" height="9"  onClick="changePos(<?=$curprof?>, <?=$prj['id']?>, '1');"/><? } ?></td>
      <td  rowspan="2" id="num<?=$prj['id']?>" style="width:1px;padding-left:5px; padding-top:7px;"><?=$i?>.</td>
      <td colspan="2" style="width:522px; padding-top:7px;">
			<a href="/users/<?=$user->login?>/viewproj.php?prjid=<?=$prj['id']?>" id="ap2<?=$j?>" target="_blank" class="blue" style="font-weight: bold; word-wrap:break-word"><?= reformat($prj['name'], 20, 0, 1)?></a><br />
			<? /*=(($prj['descr'])?$prj['descr']."</br>":"") */?>
			<?=(($prj['link'])?"<div style='margin-top:2px;margin-bottom:2px;'>".reformat($prj['link'])."</div>":"")?></td>
		</tr>
		<tr>
      <td  style="width:200px;padding:8px 0 8px 0;text-align:left;vertical-align:top;" id="previmg<?=$prj['id']?>" is_text="<?=(int)($prj['prj_prev_type']==1)?>">
		  <? if ($wrk_show_preview == 't') { ?>
			<?
        if ($is_pro)
        {
          if ($prj['gr_prevs'] == 't')
          {
            if ($prj['prj_prev_type'])
            {
      ?>
        <div style="width:200px"><?=reformat2($prj['descr'], 27)?></div></div>
			<?
            }
            else
            {
      ?>
				<div  style="width:200px;height:200px;text-align:left;vertical-align:top;"><a href="/users/<?=$user->login?>/viewproj.php?prjid=<?=$prj['id']?>" target="_blank" class="blue"><?=view_preview($user->login, $prj['prev_pict'], "upload", 'left',false,false, '', 200)?></a></div>
			<?
            }
          }
          else
          {
      ?>
				<div  style="width:200px;height:200px;text-align:left;vertical-align:top;"><a href="/users/<?=$user->login?>/viewproj.php?prjid=<?=$prj['id']?>" target="_blank" class="blue"><img src="/images/unimaged.gif" width="200" height="124" alt="Изображение автором не загружено"  /></a></div>
			<?
          }
       } else {
            ?>
&nbsp;
            <?
        }
			?>
			<? } else { ?><div style="width:200px">&nbsp;</div><? } ?></td>
			<td  style="width:284px;padding:8px 8px 8px 32px;">
        <form action="javascript:void(null);" method="post" name="prprfrm<?=$prj['id']?>" id="prprfrm<?=$prj['id']?>" onsubmit="changePorftPrice(<?=$prj['id']?>)">
        <div>
        <input type="hidden" name="prj_id" value="<?=$prj['id']?>" />
    		<table cellspacing="0" cellpadding="2" >
    		<tr valign="top">
    			<td style="white-space:nowrap; padding-bottom:10px">Стоимость разработки&#160;</td>
    			<td style="padding-bottom:10px;" align="left"><input type='text' id='prj_cost_<?=$prj['id']?>' name='prj_cost' maxlength="10" value="<?=$prj['prj_cost']?>" style='width:47px;margin-right:1px;' onchange="document.getElementById('btn_prj_<?=$prj['id']?>').disabled=false" onkeydown="document.getElementById('btn_prj_<?=$prj['id']?>').disabled=false;document.getElementById('prj_msg_<?=$prj['id']?>').innerHTML='&nbsp;';" />
    			
    			
                        <select name="prj_cost_type" id="prj_cost_type_<?=$prj['id']?>">
                            <option value="0" <?=($prj['prj_cost_type'] == 0 ? "selected='selected'" : "")?> >USD</option>
                            <option value="1" <?=($prj['prj_cost_type'] == 1 ? "selected='selected'" : "")?>>Euro</option>
                            <option value="2" <?=($prj['prj_cost_type'] == 2 ? "selected='selected'" : "")?>>Руб</option>
                        </select>
    			</td>
    		</tr>
     		<tr valign="top">
      		<td style="padding-bottom:10px;">Временные затраты</td>
      		<td style="padding-bottom:10px;"><input type='text' id='prj_time_value_<?=$prj['id']?>' name='prj_time_value' maxlength="6" value="<?=$prj['prj_time_value']?>" style='width:47px;' onchange="document.getElementById('btn_prj_<?=$prj['id']?>').disabled=false" onkeydown="document.getElementById('btn_prj_<?=$prj['id']?>').disabled=false;document.getElementById('prj_msg_<?=$prj['id']?>').innerHTML='&nbsp;';" /> <select id='prj_time_type_<?=$prj['id']?>' name='prj_time_type' onchange="document.getElementById('btn_prj_<?=$prj['id']?>').disabled=false" onkeydown="document.getElementById('btn_prj_<?=$prj['id']?>').disabled=false;document.getElementById('prj_msg_<?=$prj['id']?>').innerHTML='&nbsp;';"><option value='0'<? if ($prj['prj_time_type'] == 0) { ?> selected='selected'<? } ?>>в часах</option><option value='1'<? if ($prj['prj_time_type'] == 1) { ?> selected='selected'<? } ?>>в днях</option><option value='2'<? if ($prj['prj_time_type'] == 2) { ?> selected='selected'<? } ?>>в месяцах</option><option value='3'<? if ($prj['prj_time_type'] == 3) { ?> selected='selected'<? } ?>>в минутах</option></select></td>
    		</tr>
     		<tr valign="top">
      		<td style="padding-bottom:10px;"></td>
      		<td><input id="btn_prj_<?=$prj['id']?>" name="btn_prj_<?=$prj['id']?>" type="submit" value="Применить" /></td>
    		</tr>
	        </table>

        <div id="prj_msg_<?=$prj['id']?>" style="width:260px;text-align:left;<? if ($is_pro) { ?> margin-bottom:16px;<? } ?>"><br /></div>
		</div>
    	</form>

			</td>
		</tr>
        <?php if ( $prj['is_blocked'] == 't' ) { ?>
        <tr>
            <td colspan="3">
                <div id="portfolio-block-<?= $prj['id'] ?>" style="display: <?= ($prj['is_blocked'] == 't' ? 'block': 'none') ?>">
                    <? if ($prj['is_blocked'] == 't') { ?>
                    <div class='b-fon b-fon_clear_both b-fon_bg_ff6d2d b-fon_padtop_10 b-fon_padbot_10'>
                        <b class="b-fon__b1"></b>
                        <b class="b-fon__b2"></b>
                        <div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_fontsize_13">
                            <span class="b-fon__attent"></span>
                            <div class="b-fon__txt b-fon__txt_margleft_20">
                                    <span class="b-fon__txt_bold">Работа заблокирована</span>. <?= reformat($prj['blocked_reason'], 24, 0, 0, 1, 24) ?> <a class='b-fon__link' href='https://feedback.fl.ru/'>Служба поддержки</a>
                                    <div class='b-fon__txt'><?php if ( hasPermissions('users') ) { ?><?= ($prj['admin_login'] ? "Заблокировал: <a class='b-fon__link' href='/users/{$prj['admin_login']}'>{$prj['admin_uname']} {$prj['admin_usurname']} [{$prj['admin_login']}]</a><br />": '') ?><?php } ?>
                                    Дата блокировки: <?= dateFormat('d.m.Y H:i', $prj['blocked_time']) ?></div>
                            </div>
                        </div>
                        <b class="b-fon__b2"></b>
                        <b class="b-fon__b1"></b>
                    </div>
                    <? } ?>
                </div>
            </td>
		</tr>
        <?php } ?>
<? /*
		<tr>
			<td id="comments<?=$prj['id']?>"><? if ($prj['show_comms'] == 't') {?><a href="/users/<?=$user->login?>/comments/?tr=<?=$prj['id']?>" class="blue">Комментарии (<?=zin($prj['comms'])?>)</a><? } ?></td>
		</tr>
*/ ?>
		<? /* if (!$is_pro) { ?>
		<tr valign="bottom">
			<td><a href="#prof<?=$curprof?>" name="ap1<?=$j?>" id="ap1<?=$j?>" title="Изменить" onClick="editprj(<?=$j?>,<?=$curprof?>);">Изменить</a> | <a href="#" onClick="if (warning(5)) {frm.action.value='portf_del';document.getElementById('frm').prjid.value=<?=$prj['id']?>; delprj();} else return(false);">Удалить</a></td>
		</tr>
		<? } */?>
		</table>
	</td>

	<? if ($is_pro) { ?>
  <td class="box5fill" id="sproj<?=$prj['id']?>d" style="padding:0px 5px 0px 45px; text-align:center; vertical-align:middle"><input type="button" name="ap1<?=$j?>" id="ap1<?=$j?>" value="Изменить" onClick="editprj(<?=$j?>,<?=$curprof?>);window.location='#prof<?=$curprof?>';" /></td>
	<td  class="boxbtfill" id="sproj<?=$prj['id']?>e" style="padding:0px 20px 0px 0px; text-align:center; vertical-align:middle">
  	<form action="." method="post" name="frmdel<?=$prj['id']?>" id="frmdel<?=$prj['id']?>">
    <div>
    <input type="hidden" name="action" id="action" value="portf_del" />
    <input type="hidden" name="prjid" id="prjid" value="<?=$prj['id']?>" />
    </div>
    </form>
	<input type="button" value="x Удалить" onClick="if (warning(5)) {delprj(<?=$prj['id']?>);} else return(false);" />
  </td>
	<? } else { ?>
	<td class="box5fill" id="sproj<?=$prj['id']?>d" style="padding:0px 5px 0px 45px; text-align:center; vertical-align:middle"><input type="button" name="ap1<?=$j?>" id="ap1<?=$j?>" value="Изменить" onClick="window.location='#prof<?=$curprof?>';editprj(<?=$j?>,<?=$curprof?>);" /></td>
	<td  class="boxbtfill" id="sproj<?=$prj['id']?>e" style="padding:0px 20px 0px 0px; text-align:center; vertical-align:middle">
  	<form action="." method="post" name="frmdel<?=$prj['id']?>" id="frmdel<?=$prj['id']?>">
    <div>
    <input type="hidden" name="action" id="action" value="portf_del" />
    <input type="hidden" name="prjid" id="prjid" value="<?=$prj['id']?>" />
    </div>
    </form>
	<input type="button" value="x Удалить" onClick="if (warning(5)) {delprj(<?=$prj['id']?>);} else return(false);" />
  </td>
	<? } ?>
</tr>
		<? $i++; $j++;}
		 } ?>
<?=$end_table?>

<?if($is_pro && count($prof) > 0):?>
<script>

    
window.onload = function() {
    <?if(is_array($prof)) { foreach($prof as $k):?>
    var KeyWord<?=$k?> = new __key(<?=$k?>);
    KeyWord<?=$k?>.bind(document.getElementById('user_keys_<?=$k?>'), kword, {bodybox:'body_<?=$k?>'});  
    <?endforeach; }?> 
}
</script>
<?endif;?>    
<?
if ($action == "portf_change" && $error_flag) { ?>
<textarea id="portf_change_hidden_descr" name="portf_change_hidden_descr" style="display:none;"><?=$descr?></textarea>
	<script language="JavaScript" type="text/javascript">
<!--
	<? if ($prj_id) { ?>
	editprj(<?=$errprjnum?>,<?=$prof?>);
	<? } else { ?>
	addprj(<?=$prof?>);
    <? if($is_video=='t') { ?>toggle_form('video');<? } ?>
	<? } ?>

  <?php if ($_POST['make_position']=="first" || $_POST['make_position']=="last") {?>
  $('make_position_<?=htmlspecialchars($_POST['make_position'], ENT_QUOTES)?>').set('checked', true);
  <?php }//if?>
  
  <?php if ($_POST['make_position_num']!="") {?>
  $('make_position_num').set('checked', true);
  $('_make_position_num').set('value', "<?= htmlspecialchars($_POST['make_position_num'], ENT_QUOTES)?>");
  <?php }//if?>
	
  var _frm = document.getElementById('frm');
  _frm.pname.value =  "<?=str_replace('"', '\"', htmlspecialchars_decode($name))?>";
  _frm.pcost.value = "<?=$cost?>";
  _frm.pcosttype.value = "<?=$cost_type?>";
  _frm.ptime.value = "<?=$time_value?>";
  _frm.ptimeei.value = "<?=$time_type?>";
  _frm.link.value = "<?=str_replace('"','\"',input_ref_scr($link))?>";
  _frm.descr.value = $('portf_change_hidden_descr').get('value');

  var _frm2 = document.getElementById('frm2');
  _frm2.v_pname.value =  "<?=str_replace('"', '\"', htmlspecialchars_decode($name))?>";
  _frm2.v_pcost.value = "<?=$cost?>";
  _frm2.v_ptime.value = "<?=$time_value?>";
  _frm2.v_ptimeei.value = "<?=$time_type?>";
  _frm2.v_video_link.value = "<?=str_replace('"','\"',input_ref_scr($video_link))?>";
  _frm2.v_descr.value = "<?=str_replace('"', '\"', htmlspecialchars_decode( str_replace("\n", "\\n", $descr)) )?>";


  if(<?=$in_shop?>==1) {
    _frm.in_shop.checked = true;
  } else {
    _frm.in_shop.checked = false;
  }
  toggle_shop();
  _frm.shop_category.value = "<?=$shop_category?>";
  _frm.shop_cost_type.value = "<?=$shop_cost_type?>";
  _frm.shop_cost.value = "<?=$shop_cost?>";
  _frm.shop_tags.value = "<?=$shop_tags?>";
  //_frm.shop_info.value = "<?=$shop_info?>";



	errmsg1 = errmsg2 = errmsg3 = errmsg4 = errmsg5 = errmsg6 = errmsg7 = errmsg100 = errmsg201 = errmsg204 = errmsg205 = errmsg206 = errmsg202 = errmsg207 = '';
	setform();
//-->
</script>
<? } ?>
<? } ?>

