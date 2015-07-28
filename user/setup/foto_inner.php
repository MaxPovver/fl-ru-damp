<?
if (!$_in_setup) {header ("HTTP/1.0 403 Forbidden"); exit;}

require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/users.common.php");
$xajax->printJavascript('/xajax/');

$sGrafExt = "var grafExt = ['" . implode("','", array_diff( $GLOBALS['graf_array'], array('swf', 'gif'))) . "'];";
$nParam   = __paramInit( 'int', 'msg', null, 0 );
?>
<script type="text/javascript">
<?=$sGrafExt?>
</script>
<div class="b-layout b-layout_padtop_20">
<form action="." method="post" enctype="multipart/form-data" name="frm" id="frm"  onsubmit="return specificExt(this['foto'].value, grafExt);">
<input type="hidden" name="pfrom" value="<?=$_REQUEST['pfrom']?>" />
			<h3 class="b-layout__h3">Моя фотография</h3>
			<?/* if ($info_msg) print(view_info($info_msg)) */?>
			<? if ($nParam == 1) print(view_info('Изменения внесены')) ?>
			<? if ($nParam == 1 && $_REQUEST['pfrom']) { ?>
				<script type="text/javascript">window.close();</script>
			<? } ?>
			<input type="hidden" name="MAX_FILE_SIZE" value="102400" />
			<input type="file" name="foto" />
			<?/* if ($error) print(view_error($error)) */?>
			<? if ($nParam == 2) print(view_error('Загружаемое изображение слишком большое. Пожалуйста, уменьшите его размер до 1000*1000 пикселов.')) ?>
			<?php $aAllowedExt = array_diff( $GLOBALS['graf_array'], array('swf', 'gif') ) ?>
			<div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padbot_20">Формат фото: <?=implode(', ', $aAllowedExt )?></div>
        <?php if ($_SESSION['photo']) { ?>
            <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padbot_20"><a class="b-layout__link" href="#" onClick="if (warning(6)) { frm.del.value='1'; frm.submit(); } else return(false);"><img class="b-layout__pic" src="/images/ico_close.gif" alt="" width="9" height="9" /></a>&nbsp;<a class="b-layout__link" href="#" onClick="if (warning(6)) { frm.del.value='1'; frm.submit(); } else return(false);">Удалить фотографию</a></div>
        <?php } ?>
			<input type="hidden" name="del" value="0" />
			<input type="hidden" name="action" value="foto_change" />
   <div class="b-buttons">
      <button class="b-button b-button_flat b-button_flat_green" type="submit" name="btn">Изменить</button>
   </div>
</form>
</div>