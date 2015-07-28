<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }
$sStartVal = ($_GET['sdate']) ? $_GET['sdate'] : date('d-m-Y');
$sEndVal   = ($_GET['edate']) ? $_GET['edate'] : date('d-m-Y');
?>


<h3>Отчет по арбитражу</h3>

<form id="frm" name="frm" action="/siteadmin/norisk2/" method="get">
<input type="hidden" name="site" value="arbitrage">
<input type="hidden" name="export" value="go">
<div class="m-cl-bar c">
	<div class="m-cl-bar-sort3">
		Отчет по арбитражу
		с: <input class="plain" name="ds" value="<?=date('d-m-Y', $ds)?>" size="12" style="border: 1px solid #DFDFDF; height: 21px"><a href="javascript:void(0)" onclick="if(self.gfPop)gfPop.fStartPop(document.frm.ds,document.frm.de);return false;"><img class="PopcalTrigger" align="absmiddle" src="DateRange/calbtn.gif" width="34" height="22" border="0" alt=""></a>
        по: <input class="plain" name="de" value="<?=date('d-m-Y', $de)?>" size="12" style="border: 1px solid #DFDFDF; height: 21px"><a href="javascript:void(0)" onclick="if(self.gfPop)gfPop.fEndPop(document.frm.ds,document.frm.de);return false;"><img class="PopcalTrigger" align="absmiddle" src="DateRange/calbtn.gif" width="34" height="22" border="0" alt=""></a>
		<input type="submit" value="Экспорт в Excel" class="i-btn" />
	</div>
</div>
</form>

<?=(($sError) ? view_error($sError) : '')?>

<iframe width=132 height=142 name="gToday:contrast" id="gToday:contrast" src="DateRange/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
</iframe>