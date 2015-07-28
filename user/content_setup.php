<?php
if(!defined('IN_STDF')) { 
    header("HTTP/1.0 404 Not Found");
    exit();
}
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr valign="top">
	<td colspan="2">
		<h1>Настройки</h1>
	</td>
</tr>
<tr valign="top">
	<td>
		<table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF" class="box2">
		<tr bgcolor="#FAFAFA">
			<td height="130" align="left" style="padding:0 17px 0 17px"><?include ($fpath."info.php")?></td>
		</tr>
		<tr >
			<td bgcolor="#FAFAFA"><?include ($fpath."usermenu.php")?></td>
		</tr>
		<tr valign="top">
			<td height="247" style="padding-top:20px"><?  if ($inner) include ($fpath.$inner); else print("&nbsp;")?></td>
		</tr>
		</table>
	</td>
	<? //if (!$is_pro && false) {?>
	<td>
	<?= printBanner240(is_pro()); ?>
	</td>
	<? //} ?>
</tr>
</table>
