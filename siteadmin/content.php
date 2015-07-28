<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; } ?>
<h1>Администрирование</h1>
<table width="100%" border="0" cellspacing="2" cellpadding="19" style="border-collapse:separate !important">
<tr valign="top">
    <td width="120" bgcolor="#FFFFFF" class="box2" style="padding:10px 0">
	    <? include ($rpath."/siteadmin/leftmenu.php")?>
	</td>
	<td style="background:#fff; vertical-align:top; height:400px; padding:10px;" class="box2">
	<? include ($inner_page)?>
	</td>
</tr>
</table>