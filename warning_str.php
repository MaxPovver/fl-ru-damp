<?
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/stdf.php");
	session_start();
	
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
	<title>Free-lance</title>
</head>
<script type="text/javascript">
<!--
	window.returnValue = false;

	function subm(i){
		window.returnValue = i;
		window.close(i);
	}

	var str = window.dialogArguments;
	
//-->
</script>

<style type="text/css">
.s {
	font-family: Tahoma;
	font-size: 11px;
}
#btn {
	width: 80px;
}
</style>

<body text="#666666">
<table cellspacing="19" cellpadding="0" border="0" height="100%">
<tr>
	<td colspan="2">
	<table cellspacing="0" cellpadding="4" border="0" class="s">
	<tr>
		<td><img src="/images/ico_error.gif" alt="" width="22" height="18" border="0"></td>
		<td><script type="text/javascript">
			<!--
		 		document.write(str);
		 	//-->
			</script>
		</td>
	</tr>
	</table>
	</td>
</tr>
<tr>
	<td valign="bottom"><input type="submit" id="btn" value="Ok" class="s" onClick="subm(true);"></td>
	<td valign="bottom"><input type="submit" id="btn" value="Отмена" class="s" onClick="subm(false);"></td>
</tr>
</table>

<!-- begin of Top100 code -->
<a href="http://top100.rambler.ru/top100/"><img
src="http://counter.rambler.ru/top100.cnt?1367737" alt="Rambler's
Top100" width="1" height="1" border="0" /></a>
<!-- end of Top100 code -->

</body>
</html>
