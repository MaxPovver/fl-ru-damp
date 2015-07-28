<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }

$sTitle = admin_log::$aObj[$aActions[0]['obj_code']]['name'].': '.$aActions[0]['act_name'];
require_once( $_SERVER['DOCUMENT_ROOT'] . '/xajax/admin_log.common.php' );
$xajax->printJavascript( '/xajax/' );
?>
<form method="GET">
Действие: <select name="act_sel" id="act_sel" onchange="this.form.submit();">
<?php foreach ( $aActions as $aOne ) { 
    $sSel   = ($aOne['id'] == $act_sel) ? ' selected' : '';
    $sTitle = ($aOne['id'] == $act_sel) ? admin_log::$aObj[$aOne['obj_code']]['name'].': '.$aOne['act_name'] : $sTitle;
?>
<option value="<?=$aOne['id']?>" <?=$sSel?>><?=admin_log::$aObj[$aOne['obj_code']]['name']?>: <?=$aOne['act_name']?></option>
<?php } ?>
</select>
</form>

<h1 style="padding-top:10px;">Причины действия "<?=$sTitle?>"</h1>

<h3><?=$sFormTitle?></h3>
<form action="/siteadmin/proj_reasons/" method="post" name="frm" id="frm">
<input type="hidden" name="act_sel" id="act_sel" value="<?=$act_sel?>">
<table border="0" cellspacing="1" cellpadding="2"  class="tbl-pad5">
<tr>
	<td width="100">Название:</td>
	<td width="300">
	   <input type="text" name="reason_name" value="<?=(isset($_POST['reason_name']) ? htmlspecialchars($_POST['reason_name']) : $aReason['reason_name'])?>" style="width: 100%;">
	   <?=(($sNameError) ? view_error($sNameError) : '')?>
	</td>
</tr>
<tr>
	<td width="100" valign="top">Текст:</td>
	<td width="300">
	   <p style="padding-bottom:10px;">%USERNAME% будет автоматически заменено на ФИО пользователя.</p>
	   <textarea name="reason_text" rows="10" cols="70"><?=(isset($_POST['reason_text']) ? htmlspecialchars($_POST['reason_text']) : $aReason['reason_text'])?></textarea>
	   <?=(($sTextError) ? view_error($sTextError) : '')?>
	</td>
</tr>
<tr>
	<td width="100" valign="top">Выделять жирным:</td>
	<td width="300">
       <?php
       $sCheched = '';
       if ( isset($_POST['cmd']) ) {
           $sCheched = !empty($_POST['is_bold']) ? ' checked' : '';
       }
       else {
           $sCheched = $aReason['is_bold'] == 't' ? ' checked' : '';
       }
       ?>
        <input type="checkbox" name="is_bold" id="is_bold" value="1" <?=$sCheched?>>
	</td>
</tr>
<tr>
    <td>&nbsp;</td>
	<td height="40" colspan="2" align="right">
	   <input type="hidden" name="cmd" value="go">
	
    	<?php if ( $sAction == 'edit' ): ?>
    	
    	<input type="submit" name="btn" value="Изменить" class="btn">
    	<input type="button" name="btn" value="Отменить" class="btn" onClick="window.location='/siteadmin/proj_reasons?act_sel=<?=$act_sel?>';">
    	<input type="hidden" name="action" value="edit">
    	<input type="hidden" name="id" value="<?=$aReason['id']?>">
    	
    	<?php else: ?>
    	
    	<input type="submit" name="btn" value="Создать" class="btn">
    	<input type="hidden" name="action" value="add">
    	
    	<?php endif; ?>
	</td>
</tr>
</table>

<h3>Причины действия "<?=$sTitle?>"</h3>

<table cellspacing="1" cellpadding="2" border="0" class="tbl-pad5">
<tr>
    <td>Указать вручную</td>
    <td style="padding-left: 25px;">Изменить&nbsp;|&nbsp;Удалить&nbsp;|&nbsp;Выделять жирным</td>
</tr>
<?php if ( $aReasons ): ?>
    <?php foreach ( $aReasons as $aOne ): ?>
<tr>
    <td><?=reformat($aOne['reason_name'])?></td>
    <td style="padding-left: 25px;">
        <a href="/siteadmin/proj_reasons/?action=edit&act_sel=<?=$act_sel?>&id=<?=$aOne['id']?>" class="blue">Изменить</a>&nbsp;|&nbsp;
        <a href="/siteadmin/proj_reasons/?action=del&act_sel=<?=$act_sel?>&id=<?=$aOne['id']?>" id="del_id_<?=$aOne['id']?>" onclick="return addTokenToLink('del_id_<?=$aOne['id']?>', 'Удалить причину блокировки?')" class="blue">Удалить</a>&nbsp;|&nbsp;
        <input type="checkbox" name="is_bold_<?=$aOne['id']?>" id="is_bold_<?=$aOne['id']?>" value="1" <?=($aOne['is_bold'] == 't' ? ' checked' : '')?> onclick="setReasonBold(<?=$aOne['id']?>)">
    </td>
</tr>
    <?php endforeach; ?>
<?php endif; ?>
</table>
