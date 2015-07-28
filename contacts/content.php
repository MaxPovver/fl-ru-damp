<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/contacts.common.php");
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
	$xajax->printJavascript('/xajax/');
?>
<script type="text/javascript">
<!--
var old_num = 0;
var old_cur = 0;
var old_name = '';
var old_cont = 0;
var old_logins = '';
var inner = false;

function htmlspecialchars(str) {
	return str.replace(new RegExp("['|\"|&]", "g"), function(f) {
		switch (f) {
			case '"':
				return "&quot;";
			case "'":
				return "&#039;";
			case "&":
				return "&amp;";
			default:
                return f;
		}
	});
}

function htmlEscape(str) {
	return str.replace(new RegExp("['|\"|\\\\]", "g"), function(f) {
		switch (f) {
			case '"':
				return "\\&quot;";
			case "'":
				return "\\&#039;";
			case "\\":
				return "\\\\";
            default:
                return f;
		}
	});
}


function GetForm(num,cur,name,cont,logins){
	var re = new RegExp('"',"gi");
	var src_name = name.replace(re, '&quot;');
	re = new RegExp("'","gi");
	src_name  = src_name.replace(re, '&#039;');
out = "<form action=\"javascript:void(null);\" method=\"post\" name=\"rnfrm\" id=\"rnfrm\" onsubmit=\"submitRnFolderForm()\">\
<div>\
<input type=\"hidden\" id=\"action\" name=\"action\" value=\"renfolder\">\
<input type=\"hidden\" id=\"cur_folder\" name=\"cur_folder\" value=\""+cur+"\">\
<input type=\"hidden\" id=\"cont\" name=\"cont\" value=\""+cont+"\">\
<input type=\"hidden\" id=\"logins\" name=\"logins\" value=\""+logins+"\">\
<input type=\"hidden\" id=\"id\" name=\"id\" value=\""+num+"\">\
<table width=\"218\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">\
<tr>\
	<td colspan=\"2\" style=\"padding-bottom:4px;\">Новое имя:</td>\
<\/tr>\
<tr>\
	<td colspan=\"2\" style=\"padding-bottom:4px;\"><input maxlength=\"63\" type=\"text\" id=\"new_name\" name=\"new_name\" value=\""+htmlspecialchars(name)+"\" style=\"width: 218px;\" onblur=\"if(this.value=='') this.value='<?=$fld_name?>';\">\</td>\
<\/tr>\
<tr>\
	<td><input type=\"button\" name=\"resetbtn\" id=\"resetbtn\" value=\"Отменить\" onClick='resetfld(\""+num+"\",\""+cur+"\",\""+htmlspecialchars(htmlEscape(src_name)) +"\",\""+cont+"\",\""+logins+"\");'></td>\
	<td align=\"right\"><input type=\"submit\" name=\"savebtn\" id=\"savebtn\" value=\"Сохранить\"></td>\
<\/tr>\
<\/table>\
<\/div>\
<\/form>";
	return(out);
}

function rename(num,cur,name,cont,logins){
	re = /&quot;/gi
	name = name.replace(re, '"');
	re = /&#039;/gi
	name = name.replace(re, '\'');
	td = document.getElementById('li_folder'+num);
	if (old_num > 0){
	  resetfld(old_num,old_cur,old_name,old_cont,old_logins)
	}
	td.innerHTML = GetForm(num,cur, name,cont,logins);
	old_num = num;
	old_cur = cur;
	old_name = name;
	old_cont = cont;
	old_logins = logins;
}
	
function resetfld(num,cur,name,cont,logins){
	var li = $('li_folder'+num);
  	folder_html = '';
  	if (num == cur)
  	{
  	  folder_html = '<span style="float:left;"><img class="li" src="/images/ico_dir.gif" /></span>' + name;
  	}
  	else
  	{
  	  folder_html = '<a href="/contacts/?folder='+num+'"><span style="float:left;"><img class="li" src="/images/ico_dir.gif" /></span></a><a href="/contacts/?folder='+num+'" class="blue">'+name+'</a>';
  	}
  	folder_html = folder_html+" (<span id=\"fldcount"+num+"\">"+cont+"<\/span>)";
  	folder_html = folder_html+"<div style=\"margin-top: 7px;\" align=\"right\"><a href=\"\/contacts\/?action=delfolder&id="+num+"\" onClick=\"return warning(9)\" title=\"Удалится только папка. Контакты переместятся в&nbsp;папку &laquo;Все&raquo;.\">Удалить</a> | <a href=\"javascript:rename('"+num+"','"+cur+"','"+htmlspecialchars(htmlEscape(name))+"','"+cont+"','"+logins+"');\">Переименовать<\/a><\/div><div style=\"clear:both;\"></div>";
	li.set("html", folder_html);
	var ls = li.getElements("a");
	if (ls.length > 1) {
		var a = ls[1];
		if (a.offsetWidth > 166) {
			var s = a.innerHTML;
            s = s.replace(/(\(\s?[0-9]+\s?\))/gi, " $1");
			var q = "";
			for (var i = 0; i < s.length; i++) {
				q += s.charAt(i);
				if ( (i % 15 == 0)&&(i > 0) ) q += " ";
			}
			a.set("html", q);
		}
	}	
}

function submitRnFolderForm()
{
	xajax.$('savebtn').disabled=true;
//	xajax.$('savebtn').value="Подождите";
	xajax_RnFolder(xajax.getFormValues("rnfrm"));
	return false;
}

function show_fpopup(img,num)
{
	document.getElementById(img).blur();
	document.getElementById(num).toggleClass('b-layout_hide');
}

function hide_fpopup(num)
{
  if (!inner)
  {
    e = document.getElementById(num);
    e.addClass('b-layout_hide');
  }
}

function mouseout(num)
{
  setTimeout("hide_fpopup('"+num+"')", 500);
}
//-->
</script>
<?
if ($inner) {
    include ($inner);
} else {

require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/mess_folders.php");
$cf = new mess_folders();
$cf->from_id = get_uid();
$folders = $cf->GetAll();

// Определяю название текущей папки. Лучше вынести в метод или функцию.
if ( !$pm_folder ) {
    if ($cur_folder > 0)
    {
      foreach ($folders as $folder)
      {
        if ($folder['id'] == $cur_folder)
        {
          $cur_folder_name = $folder['fname'];
        }
      }
    }
    else
    {
        switch ($cur_folder)
        {
          case -1:
            $cur_folder_name = 'Избранные';
            $sSuff = 'team/';
            break;
          case -2:
            $cur_folder_name = 'Игнорирую';
            $sSuff = 'ignor/';
            break;
          case -3:
            $cur_folder_name = 'Удаленные';
            $sSuff = 'del/';
            break;
          case -4:
            $cur_folder_name = 'Пользователи с моими заметками (в сообщениях)';
            $sSuff = 'notes/';
            break;
          case -5:
            $cur_folder_name = 'Пользователи с моими заметками (на всем сайте)';
            $sSuff = 'allnotes/';
            break;
          case -6:
            $cur_folder_name = 'Платные рассылки';
            $sSuff = 'mass/';
            break;
          case -7:
            $cur_folder_name = 'Непрочитанные';
            $sSuff = 'unread/';
            break;
          default:
            $cur_folder_name = 'Все';
            $sSuff = '';
            break;
        }
    }
}
else {
    $aCurrFolder     = messages::pmAutoFolderGetById( get_uid(), $pm_folder );
    $aMassSend       = masssending::Get( $aCurrFolder['mass_send_id'] );
    $cur_folder_name = $aCurrFolder['name'];
}
// \ Определение названия текущей папки.

$arr_logins = array();

$users_folders = $msgs->GetUsersInFolders(get_uid());
	
if($contacts) {
    $i=0;
    foreach($contacts as $contact) {
        if(!$predefined_count) {
            if($i++ < ($page-1) * $blogspp) continue;
            if($i > ($page) * $blogspp) break;
        }
        $arr_logins[] = $contact['login'];
    }
  $mess_logins = join('~', $arr_logins);
}
?>

<h1 class="b-page__title">Сообщения /
   <?=reformat($cur_folder_name, 15, 0, 1)?>
</h1>

<div id="rightCol" class="b-layout__one b-layout__one_width_25ps b-layout__right_float_right b-layout__one_width_full_ipad">
  <div id="content">
    <div class="box">
      <?$fld_txt = "Новая папка"?>
      <form action="/contacts/" method="post" name="crfrm" id="crfrm" onSubmit="if($('fldname').get('value')=='<?=$fld_txt?>') { $('fldname').set('value', ''); }">
        <div>
          
<div class="b-search">
 <table class="b-search__table" cellpadding="0" cellspacing="0"><tr class="b-search__tr"><td class="b-search__input">
  <div class="b-input b-input_height_24">
   <input id="fldname" class="b-input__text" name="name" type="text"  placeholder="<?=$fld_txt?>" />
   </div>
 </td><td class="b-search__button">
 <a class="b-button b-button_flat b-button_flat_grey b-button_margleft_5" href="#" onclick="$('crfrm').submit(); return false;" >Создать</a>
 </td></tr></table>
</div>
          
          
          <!--
          <input type="text" maxlength="63" id="fldname" name="name" value="<?=$fld_txt?>" style="width: 135px;" onblur="if(this.value=='') this.value='<?=$fld_txt?>';" onfocus="if(this.value=='<?=$fld_txt?>') this.value='';" />
          <input type="submit" class="btn" value="Создать" style="vertical-align: middle;" />
          -->
          <input type="hidden" name="action" value="addfolder" />
        </div>
      </form>
      <? if ($error) {?>
      <div class="errorBox"><img src="/images/ico_error.gif" alt="" width="22" height="18"  style="float:left;margin:0px 8px 8px 0px; border:0;" />
        <?=$error?>
      </div>
      <?}?>
    </div>
    <ul>
      <li class="last">
        <? if (!$pm_folder && $cur_folder == 0) { ?>
        <img class="li" src="/images/ico_dir_nd.gif" />Все
        <? } else { ?>
        <a href="/contacts/"><img class="li" src="/images/ico_dir_nd.gif" alt="" /></a><a href="/contacts/" class="blue">Все</a>
        <? } ?>
      </li>
      <li class="last">
        <? if ($cur_folder == -7) { ?>
        <img class="li" src="/images/icon_unread.gif" alt="" />Непрочитанные
        <? } else { ?>
        <a href="/contacts/unread/"><img class="li" src="/images/icon_unread.gif" alt="" /></a><a href="/contacts/unread/" class="blue">Непрочитанные</a>
        <? } ?>
      </li>
    </ul>
    <?
  if ($folders) {
   $folder_count = sizeof($folders);
   /*
  ?>
  <ul class="custom">
  <? foreach ($folders as $ikey => $folder) { ?>
  <li id="li_folder<?=$folder['id']?>" <? if ($ikey+1 == $folder_count) { ?>class="last"<? } ?>><? if ($cur_folder == $folder['id']) { ?><img class="li" src="/images/ico_dir.gif" /><?=$folder['fname']?><? } else { ?><a href="/contacts/?folder=<?=$folder['id']?>"><img class="li" src="/images/ico_dir.gif" /></a><a href="/contacts/?folder=<?=$folder['id']?>" class="blue"><span id="fldname<?=$folder['id']?>"><?=reformat($folder['fname'], 15, 0, 1)?></span></a><? } ?> (<span id="fldcount<?=$folder['id']?>"><?=$folder['users_count']?></span>)
  <div align="right"><a href="/contacts/?action=delfolder&id=<?=$folder['id']?>" onClick="return warning(9)" title="Удалится только папка. Контакты переместятся в&nbsp;папку &laquo;Все&raquo;.">Удалить</a> | <a href='javascript: rename("<?=$folder['id']?>","<?=$cur_folder?>", "<?=str_replace(array("\\", "&quot;", "&#039;"), array("\\\\", "\\&quot;", "\\&#039;"), $folder['fname']);?>","<?=$folder['users_count']?>","<?=$mess_logins?>");'>Переименовать</a></div></li>
  <? } ?>
  </ul>
  <? 
  */
   ?>
    <ul>
      <?php foreach ($folders as $ikey => $folder) { ?>
      <li id="li_folder<?=$folder['id']?>" <? if ($ikey+1 == $folder_count) { ?>class="last"<? } ?>>
        <? if ($cur_folder == $folder['id']) { ?>
        <span style="float:left;"><img class="li" src="/images/ico_dir.gif" alt="" /></span>
        <? $folder['fname'] = preg_replace("#(\(\s?[0-9]+\s?\))#si", " $1", $folder['fname']); print reformat($folder['fname'], 15, 0, 1);?>
        <? } else { ?>
        <a href="/contacts/?folder=<?=$folder['id']?>"><span style="float:left;"><img class="li" src="/images/ico_dir.gif" alt="" /></span></a><a href="/contacts/?folder=<?=$folder['id']?>" class="blue">
        <? $folder['fname'] = preg_replace("#(\(\s?[0-9]+\s?\))#si", " $1", $folder['fname']); print reformat($folder['fname'], 15, 0, 1); ?>
        </a>
        <? } ?>
        (<span id="fldcount<?=$folder['id']?>">
        <?=$folder['users_count']?>
        </span>)
        <div style="margin-top: 7px; text-align:right"><a href="/contacts/?action=delfolder&id=<?=$folder['id']?>&token_key=<?=$_SESSION['rand']?>" onClick="return warning(9)" title="Удалится только папка. Контакты переместятся в&nbsp;папку &laquo;Все&raquo;.">Удалить</a> | <a href='javascript: rename("<?=$folder['id']?>","<?=$cur_folder?>", "<?=str_replace("\\", "\\\\", htmlspecialchars($folder['fname']));?>","<?=$folder['users_count']?>","<?=$mess_logins?>");'>Переименовать</a></div>
        <div style="clear:both;"></div>
      </li>
      <?php } ?>
    </ul>
    <?php
   
  } ?>
    <ul>
      <li>
        <? if ($cur_folder == -1) { ?>
        <img class="li" src="/images/ico_dir_nd.gif" alt="" />Избранные
        <? } else { ?>
        <a href="/contacts/team/"><img class="li" src="/images/ico_dir_nd.gif" alt="" /></a><a href="/contacts/team/" class="blue">Избранные</a>
        <? } ?>
      </li>
      <li>
        <? if ($cur_folder == -2) { ?>
        <img class="li" src="/images/ico_dir_nd.gif" alt="" />Игнорирую
        <? } else { ?>
        <a href="/contacts/ignor/"><img class="li" src="/images/ico_dir_nd.gif" alt="" /></a><a href="/contacts/ignor/" class="blue">Игнорирую</a>
        <? } ?>
      </li>
      <li class="last">
        <? if ($cur_folder == -6) { ?>
        <img class="li" src="/images/ico_dir_gr.gif" alt="" />Платные рассылки
        <? } else { ?>
        <a href="/contacts/mass/"><img class="li" src="/images/ico_dir_gr.gif" alt="" /></a><a href="/contacts/mass/" class="blue">Платные рассылки</a>
        <? } ?>
      </li>
    </ul>
    <ul style='border-bottom:0'>
      <li class="last">
        <? if ($cur_folder == -4 || $cur_folder==-5) { ?>
        <div style="float:left; margin:2px 0px 30px 0px;"><img class="li" src="/images/ico_dir_notes.gif" alt="" /></div>
        Смотреть всех пользователей с моими заметками
        <? } else { ?>
        <a href="/contacts/notes/">
        <div style="float:left; margin:2px 0px 30px 0px;"><img class="li" src="/images/ico_dir_notes.gif" alt="" /></div>
        </a><a href="/contacts/notes/" class="blue">Смотреть всех пользователей с моими заметками</a>
        <? } ?>
      </li>
    </ul>
    
    <?/*
  <ul class="del">
  <li><? if ($cur_folder == -3) { ?><img class="li" src="/images/ico_trash.gif" alt="" />Удаленные<? } else { ?><a href="/contacts/del/"><img class="li" src="/images/ico_trash.gif" alt="" /></a><a href="/contacts/del/" class="blue">Удаленные</a><? } ?></li>
  </ul>
  */?>
  </div>
  <?php /*
  <div class="b-layout b-layout_padtop_30">
    <div class="b-chat b-chat_inline-block b-chat_width_40">
            <div class="b-chat__body b-chat__body_padtb_3_0">
    <span class="b-chat__icon b-chat__icon_main"></span>
            </div>
    </div><div 
     class="b-layout__txt b-layout__txt_inline-block b-layout__txt_padleft_5 b-layout__txt_lineheight_15  b-layout__txt_valign_top">Быстрочат <span id="qchat_link_wrapper"><?php if (!(int)$_SESSION["chat"] ) {?>отключен<?} else {?>включен<?} ?></span><br /><a class="b-layout__link" href="javascript:void(0);" onclick="<?if (!(int)$_SESSION["chat"] ) {?>quickchat_on();<?} else {?>quickchat_off();<?} ?> return false;" id="qchat_swicth"><?if (!(int)$_SESSION["chat"] ) {?>Включить<?} else { ?>Отключить<?} ?></a></div>
    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padtop_5 b-layout__txt_padbot_20">Быстрочат позволяет быстро отвечать на сообщения прямо на той странице, где вы находитесь. <a class="b-layout__link" href="https://feedback.fl.ru">Подробнее о быстрочате</a></div>
  </div>   
  */ ?>
  <?php /* Автоматические папки для массовых рассылок личных менеджеров */ ?>
  <?php 
$sUid = $_SESSION['uid'];
if ( 
    in_array($sUid, $aPmUserUids) 
    || SERVER === 'local' || SERVER === 'beta' || SERVER === 'alpha' 
) { 
    $aYears = messages::pmAutoFoldersGetYears( $sUid );
    if ( $aYears ) {
        $sCurrYear = ( $pm_year ) ? $pm_year : $aYears[0];
        $nFolders  = messages::pmAutoFoldersCount( $sUid, $sCurrYear );
        $nOffset   = ( $pm_offset ) ? $pm_offset : 0;
        $aFolders  = messages::pmAutoFolders( $sUid, $sCurrYear, messages::PM_AUTOFOLDERS_PP, $nOffset );
        $bNext     = ( ($nNext = $nOffset + messages::PM_AUTOFOLDERS_PP) + 1 <= $nFolders );
        $bPrev     = ( ($nPrev = $nOffset - messages::PM_AUTOFOLDERS_PP) > 0 );
    ?>
  <div id="block-archives">
    <h3>Ответы на платные рассылки</h3>
    <ul class="archive-year c">
      <?php
        foreach ( $aYears as $sOne ) { 
            $sClass = ( $sOne == $sCurrYear ) ? ' class="active"' : '';
            $sClick = ( $sOne == $sCurrYear ) ? '' : ' onclick="xajax_PmFolders('.$sUid.', ' . $sOne . ', 0);"';
        ?>
      <li <?=$sClass?>><a href="javascript:void(0);" <?=$sClick?>>
        <?=$sOne?>
        </a></li>
      <?php } ?>
    </ul>
    <ul class="archive-list c">
      <?php 
        foreach ( $aFolders as $aOne ) { 
        ?>
      <li id="pm_folder<?=$aOne['id']?>" <?=($aOne['id'] == $pm_folder ? ' class="active"' : '')?>> <a href="javascript:void(0);"> <span class="ar-del" onclick="pmFolderDel(<?=$sUid?>, <?=$aOne['id']?>, <?=$sCurrYear?>, <?=$nOffset?>);"></span> <span class="ar-edit" onclick="xajax_PmFolderEdit(<?=$sUid?>, <?=$aOne['id']?>, <?=$sCurrYear?>, <?=$nOffset?>);"></span> <span class="archive-date" onclick="pmFolderGo(<?=$sCurrYear?>, <?=$aOne['id']?>, <?=$nOffset?>)">
        <?=date('d/m', strtotime($aOne['post_date']))?>
        </span> <span title="<?=reformat($aOne['name'], 64, 0, 1)?>" class="archive-text" onclick="pmFolderGo(<?=$sCurrYear?>, <?=$aOne['id']?>, <?=$nOffset?>)">
        <?=reformat($aOne['name'], 64, 0, 1)?>
        <b></b></span> </a> </li>
      <?php } ?>
    </ul>
    <?php if ($nNext || $bPrev ) { ?>
    <p class="archive-prev">
      <?php if ($bPrev) { ?>
      <a onclick="xajax_PmFolders(<?=$sUid?>, <?=$sCurrYear?>, <?=$nPrev?>);" href="javascript:void(0);" class="lnk-dot-grey">&laquo;Следующие</a>
      <?php } ?>
      <?php if ($bNext && $bPrev ) { ?>
      &nbsp;|&nbsp;
      <?php } ?>
      <?php if ($bNext) { ?>
      <a onclick="xajax_PmFolders(<?=$sUid?>, <?=$sCurrYear?>, <?=$nNext?>);" href="javascript:void(0);" class="lnk-dot-grey">Предыдущие&raquo;</a>
      <?php } ?>
    </p>
    <?php } ?>
  </div>
  <script type="text/javascript">
function pmFolderGo( year, folder, offset ) {
    window.location = '/contacts/?pmy='+year+'&pmf='+folder+'&pmo='+offset;
}

function pmFolderDel( uid, folder, year, offset ) {
    if ( confirm('Удалить папку?') ) {
        xajax_PmFolderDel(uid, folder, year, offset);
    }
}

function pmFolderEdit( uid, folder, year, offset ) {
    var name = $('pm_fname'+folder).get('value').replace(/(^\s+)|(\s+$)/g, "");
    
    if ( name.length < 1 ) {
        alert('Укажите название папки');
        return false;
    }
    
    xajax_PmFolderEdit(uid, folder, year, offset, 'update', name);
}
</script>
  <?php
    }
}
?>
  <?php /* Автоматические папки для массовых рассылок личных менеджеров */ ?>
  <?= printBanner240(is_pro()); ?>
</div>
<div id="leftCol" class="b-layout__one b-layout__one_float_left b-layout__one_width_72ps b-layout__one_width_full_ipad">
  <div id="content" >
    <div class="box" style="margin-bottom:0">
      <form action="/contacts/<?=$sSuff?>" name="srfrm" id="srfrm">
        <div>
          <?php
    if ( $pm_folder ) {
     ?>
          <input type="hidden" name="pmy" value="<?=$pm_year?>" />
          <input type="hidden" name="pmf" value="<?=$pm_folder?>" />
          <?php
    }
    elseif ( $cur_folder > 0 ) {
        ?>
          <input type="hidden" name="folder" value="<?=$cur_folder?>" />
          <?php
    }
    ?>
    
<div class="b-search">
 <table class="b-search__table" cellpadding="0" cellspacing="0"><tr class="b-search__tr"><td class="b-search__input">
  <div class="b-input b-input_height_24">
   <input id="fl2_search_input2" class="b-input__text" name="find" type="text" placeholder="<?=$tag?>" />
   </div>
 </td><td class="b-search__button">
 <a id="fl2_search_submit" class="b-button b-button_flat b-button_flat_grey b-button_margleft_5" href="#"  onclick="if(document.getElementById('fl2_search_input2').value.length > 3) $('srfrm').submit(); return false;">Найти</a>
 </td></tr></table>
</div>
    
    
        </div>
      </form>
      <? if($cur_folder==-4 || $cur_folder==-5) { ?>
      <br/>
      [<a href="/contacts/notes/">В сообщениях</a>]&nbsp;&nbsp;[<a href="/contacts/allnotes/">На всём сайте</a>]
      <? } ?>
      <div class="clear"></div>
    </div>
    <?php /* массовая рассылка */ 
  if ( $pm_folder ) {
  ?>
    <div style="padding: 5px 15px 15px;border-bottom: 1px solid #C6C6C6;">
      <div class="form fs-p">
        <div style="padding: 7px 15px 10px; background-color: #F0EFED;"> <strong>
          <?=$_SESSION['name'].' '.$_SESSION['surname'].' ['.$_SESSION['login'].']'?>
          </strong>
          <?=date('d.m.Y', strtotime($aCurrFolder['post_date']))?>
          в
          <?=date('H:i:s', strtotime($aCurrFolder['post_date']))?>
          <br />
          <br />
          <?=reformat($aMassSend[0]['msgtext'],30,0,0,1)?>
          <br />
          <br />
          <hr />
          <?php 
   $aCond = array();
   
   if ( $aMassSend[0]['to_pro'] == 't' ) $aCond[] = 'Только с PRO аккаунтом';
   
            if ( $aMassSend[0]['prof_names'] && ($prof_names = explode(',', $aMassSend[0]['prof_names']))) {
                foreach($prof_names as $name) $aCond[] = $name;
            }
            else {
                $aCond[] = 'Общий каталог';
            }
            
            if ($aMassSend[0]['positive'] == 't') $aCond[] = 'С положительными отзывами';
   
   if ($aMassSend[0]['no_negative'] == 't') $aCond[] = 'Без отрицательных отзывов';
   
   if ($aMassSend[0]['free'] == 't') $aCond[] = 'Только свободные';
   
   if ($aMassSend[0]['favorites'] == 't') $aCond[] = 'У меня в избранных';
   
   if ($aMassSend[0]['portfolio'] == 't') $aCond[] = 'Только с примерами работ';
   
   if ($aMassSend[0]['sbr'] == 't') $aCond[] = 'С успешными «Безопасными Сделками»';
   
   if ($aMassSend[0]['office' == 't']) $aCond[] = 'Ищет работу в офисе';
   
   //if ($aMassSend[0]['rank']) $aCond[] = 'Фрилансер '. $aMassSend[0]['rank'] .'-го разряда';
   
   if ($aMassSend[0]['exp_from'] || $aMassSend[0]['exp_to'] ) { 
       $sExp = 'Опыт работы: ';
       
       if ( $aMassSend[0]['exp_from'] && $aMassSend[0]['exp_to'] ) {
        $sExp .= $aMassSend[0]['exp_from'] .'-'. $aMassSend[0]['exp_to'];
       }
       elseif ( $aMassSend[0]['exp_from'] ) {
           $sExp .= 'от '. $aMassSend[0]['exp_from'];
       }
       else {
           $sExp .= 'до '. $aMassSend[0]['exp_to'];
       }
       
       $sExp   .= ' лет';
       $aCond[] = $sExp;
    }
    
    if ( count($aCond) ) {
                echo implode(', ', $aCond);
    }
    ?>
        </div>
      </div>
    </div>
    <?php }
    /* массовая рассылка */ ?>
    
    <!-- WARNING -->
    <? include($_SERVER['DOCUMENT_ROOT']. '/contacts/tpl.warning.php'); ?>
    <!-- WARNING -->
    <style type="text/css">.b-icon__ver{ position:relative; top:-1px;}</style>
    
    <form  method="post" name="frm" id="frm">
      <div>
        <input type="hidden" name="action" value="" />
        <input type="hidden" name="selected" id="sel" value="" />
        <table class="b-layout__table b-layout__table_width_full" border="0" cellspacing="0" cellpadding="0">
          <?
      if($contacts)
      {
         //Получаем is_profi флаг пользователей
         $ids = array();
         foreach($contacts as $contact) {
             if(is_emp($contact['role'])) {
                 continue;
             }
             $ids[] = $contact['uid'];
         } 
         
         $usersIsProfi = array();
         if($ids) {
             $userObj = new users();
             $usersIsProfi = $userObj->getUsersProfi($ids);
         }

         $i=0;
         foreach($contacts as $contact)
         {
             if(!$predefined_count) {
                 if($i++ < ($page-1) * $blogspp) continue;
                 if($i > ($page) * $blogspp) break;
             }
             $cnt_role = (substr($contact['role'], 0, 1)  == '0')? "frl" : "emp";
             
             if(isset($usersIsProfi[$contact['uid']])){
                 $contact['is_profi'] = $usersIsProfi[$contact['uid']];
             }
    ?>
          <tr id="ur<?=$contact['login']?>" class="b-layout__tr qpr">
            <td class=" b-layout__left_pad_10">
              <table width="100%" cellspacing="0" cellpadding="0" border="0">
                <tr class="n_qpr" style="vertical-align:top">
                  <td style="width:60px; text-align:left"><a href="/users/<?=$contact['login']?>">
                    <?=view_avatar($contact['login'], $contact['photo'])?>
                    </a></td>
                  <td class="cont_inner">
                    <div class="b-username b-username_inline">
                      <?=$session->view_online_status($contact['login'])?><span class="<?=$cnt_role?>name11"><a href="/users/<?=$contact['login']?>" class="<?=$cnt_role?>name11">
                      <?=($contact['uname']." ".$contact['usurname'])?>
                      </a> [<a href="/users/<?=$contact['login']?>" class="<?=$cnt_role?>name11"><?=$contact['login']
                      ?></a>]</span><?= view_mark_user($contact);?>
                    </div>
                    <?php /*if(is_emp() && !is_emp($contact['role'])) {?>
                    <a href="/sbr/?site=create&fid=<?=$contact['uid']?>" class="b-button b-button_flat b-button_flat_grey b-button_float_right">Начать «Безопасную Сделку»</a>
                    <?php }//if */?>
                    <?=($contact['is_banned']==1 ? "<br /><span style='color: #FF500B;'><strong>Пользователь&nbsp;заблокирован</strong></span>" : "")?>
                    <br />
                    <? if($cur_folder!=-5) { ?>
                    <div class="b-layout__txt b-layout__txt_padtb_5" style="clear:right"><a href="/contacts/?from=<?=$contact['login']?>" class="b-layout__link">Сообщений</a> (<?=($contact['msg_count'])?>)</div>
                    <div class="folders">
                      <?
                     $ihim = $contact['my_last_post']; // Когда я ему последний раз писал.
                     $heme = $contact['his_last_post']; // Когда он мне последний раз писал.
                     $i_am_last = 0; // 1, если последний раз писал я.
                     if ($heme && (!$contact['i_last_read'] || strtotime($heme) > strtotime($contact['i_last_read']))) {
                       ?>
                      <span class="newmess"><a href="/contacts/?from=<?=$contact['login']?>"><img class="i_newmess" src="/images/ico_newmail.gif" alt="" width="11" height="9" style="border:0;" /></a> <a href="/contacts/?from=<?=$contact['login']?>" class="white">Новые сообщения</a></span>
                      <? }
                     else
                     {
                       if($heme) { // Он мне писал.
                         if($ihim) { // Я ему тоже писал.
                           if(strtotimeEx($heme) > strtotimeEx($ihim)) { // Последний раз писал он. Я прочел, так как это не новое сообщение.
                             ?>
                      Вы предпочли промолчать<br />
                      <?
                           }
                           else // Последний раз писал я.
                             $i_am_last = 1;
                         }
                         else { // Я ему никогда не писал.
                           ?>
                      Вы не ответили
                      <?
                         }
                       }
                       else // Он никогда мне не писал, а я писал.
                         $i_am_last = 1;

                       if($i_am_last) {
                         if(strtotimeEx($contact['he_last_read']) > strtotimeEx($ihim)) { // Он читал сообщения после того как я ему написал. То есть, он прочел.
                           ?>
                      <img src="/images/ico_envelop_op.gif" alt="" width="10" height="12" style="border:0;" />&nbsp;Ваше сообщение прочитано
                      <?=date("d.m в H:i",strtotimeEx($contact['he_last_read']))?>
                      <?
                         }
                         else {
                           ?>
                      Ваше сообщение не прочитано
                      <?
                         }
                       }
                     }  
                  ?>
                    </div>
                    <div class="vfolders">
                      <? if ($folders) foreach ($folders as $folder) { ?>
                      <div id="vfolder<?=$folder['id']."u".$contact['login']?>" class="<? if ($users_folders[$contact['uid']] && in_array($folder['id'], $users_folders[$contact['uid']])) { ?>active<? } else { ?>passive<? } ?>">
                        <?=reformat($folder['fname'],25,0,1)?>
                      </div>
                      <? } ?>
                      <div id="vfolder-1<?="u".$contact['login']?>" class="<? if (($users_folders[$contact['uid']] && in_array(-1, $users_folders[$contact['uid']]))) { ?>active<? } else { ?>passive<? } ?>">Избранные</div>
                      <div id="vfolder-2<?="u".$contact['login']?>" class="<? if ($users_folders[$contact['uid']] && in_array(-2, $users_folders[$contact['uid']])) { ?>active<? } else { ?>passive<? } ?>">Игнорирую </div>
                      <?php if ($users_folders[$contact['uid']] && in_array(-6, $users_folders[$contact['uid']])) { ?>
                      <div class="active delivery">Платные рассылки</div>
                      <?php } ?>
                    </div>
                    <? if($contact['is_banned']!=1) { ?>
                    <a href="javascript:void(null);" onClick="show_fpopup('ipopup<?=$folder['id']."u".$contact['login']?>','fpopup<?=$folder['id']."u".$contact['login']?>')" onMouseOut="inner=false;mouseout('fpopup<?=$folder['id']."u".$contact['login']?>')"><img id="ipopup<?=$folder['id']."u".$contact['login']?>" class="i_2folder" src="/images/2folder.gif" alt="" /></a>
                    <div class="folders">
                      <div onMouseOver="inner=true;" onMouseOut="inner=false;mouseout('fpopup<?=$folder['id']."u".$contact['login']?>')" class="fpopup b-layout_hide" id="fpopup<?=$folder['id']."u".$contact['login']?>" name="fpopup<?=$folder['id']."u".$contact['login']?>">
                        <?
                      $u = new users();
                      if(empty($contact['login'])) {
                          $u->GetUser($contact['login2']);
                      } else {
                          $u->GetUser($contact['login']);
                      }
                    ?>
                        <? if ($folders) { 
                        foreach ($folders as $folder) { ?>
                        <div onMouseOver="inner=true;" onMouseOut="inner=false;" id="folder<?=$folder['id']."u".$contact['login']?>" <? if ($users_folders[$contact['uid']] && in_array($folder['id'], $users_folders[$contact['uid']])) { ?>class="active"<? } else { ?>class="passive"<? } ?> onClick="xajax_ChFolder(<?=$folder['id']?>,<?=$cur_folder?>,'<?=$contact['login']?>');">
                          <?=reformat($folder['fname'],25,0,1)?>
                        </div>
                        <br />
                        <? } ?>
                        <br />
                        <? } ?>
                        <div onMouseOver="inner=true;" onMouseOut="inner=false;" id="folder-1<?="u".$contact['login']?>" <? if ($users_folders[$contact['uid']] && in_array(-1, $users_folders[$contact['uid']])) { ?>class="active"<? } else { ?>class="passive"<? } ?> onClick="xajax_ChFolder(-1,<?=$cur_folder?>,'<?=$contact['login']?>');">Избранные</div>
                        <br />
                        <? if(!in_array($u->login, $usersNotBeIgnored)) { ?>
                        <div onMouseOver="inner=true;" onMouseOut="inner=false;" id="folder-2<?="u".$contact['login']?>" <? if ($users_folders[$contact['uid']] && in_array(-2, $users_folders[$contact['uid']])) { ?>class="active"<? } else { ?>class="passive"<? } ?> onClick="xajax_ChFolder(-2,<?=$cur_folder?>,'<?=$contact['login']?>');">Игнорирую</div>
                        <br />
                        <? } ?>
                        <div onMouseOver="inner=true;" onMouseOut="inner=false;" class="blue" onClick="if (warning(3)) {document.getElementById('sel').value=<?=$contact['uid']?>;frm.action.value='delete'; frm.submit();} else return(false);">Удалить</div>
                      </div>
                    </div>
                    <? } ?>
                    <? } ?>
                  </td>
                </tr>
              </table>
            </td>
            <? $name=$contact['login']; $t_role = $contact['role']; include ("../user/note.php") ?>
          </tr>
          <? } // foreach($contacts as $contact)
  // Страницы 
 $pages = ceil($count / $blogspp);
    if ($pages > 1){ ?>
          <tr>
            <td colspan="2" style="background:#fff; padding:10px;">
              <?php /*
            $url_pager = '';
            if ($cur_folder > 0) {
                $url_pager = '%s?folder='.$cur_folder.'&page=%d%s';
            } else {
                $url_pager = '%s?page=%d%s';
            }*/
            
            // В постраничности используется sprintf, поэтому недопустимы символы процента,
            // Их тут обычно и не должно быть
            $uri = str_replace('%', '', $sHref);
            $url_pager = '%s'.e_url( 'page', '', $uri ).'%d%s';
            echo new_paginator($page, $pages, 4, $url_pager);
            //echo get_pager2($pages,$page,$url_pager);
            ?></td>
          </tr>
          <? } // Страницы закончились
   } else { ?>
          <tr class="qpr">
            <td style="text-align:center" colspan="2"> Ничего не найдено </td>
          </tr>
          <? } ?>
        </table>
      </div>
    </form>
  </div>
</div>
<? } ?>

