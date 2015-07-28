<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; } ?>
<style>
#table_list {border-right: solid 1px gray; border-bottom: solid 1px gray;}
#table_list td, #table_list th {border-left: solid 1px gray; border-top: solid 1px gray;padding: 5px;}
#table_list th {background: #e6fdb7;text-align: center;}
#table_form {border-bottom: solid 1px #b2b2b2;}
#table_form td {padding: 5px;}
</style>

<h3>Альфа-банк: Зачисление средств</h3>

<?php
if ( $_SESSION['success'] == 'ok' ) {
	?><span style="color:green;">Действие выполнено успешно</span><?php
}

$_SESSION['success'] = '';
?>

<span style="color:red;"><?=$sError?></span>

<form name="frm" action="/siteadmin/alpha/?ds=<?=date("d-m-Y",strtotime($ds))?>&de=<?=date("d-m-Y",strtotime($de))?>" method="post">
<input type="hidden" name="action" value="add">
<table id="table_form" width="100%" border="0" cellpadding="0" cellspacing="0">
<tr>
    <td>Логин:&nbsp;</td>
    <td><input type="text" name="login" id="login" value="<?=(isset($sLogin)?$sLogin:'')?>"/></td>
</tr>
<tr>    
    <td>Сумма (рублей):&nbsp;</td>
    <td><input type="text" name="summ" id="summ" value="<?=(isset($_POST['summ'])?htmlspecialchars($_POST['summ']):'')?>"/></td>
</tr>
<tr>
    <td>Дата:&nbsp;</td>
    <td valign="top">
    <div class="form-el">
        <div class="form-value">
        <input type="text" name="date" id="date" value="<?=(isset($sDate)?date('d-m-Y', $sDate):date('d-m-Y'))?>"/>
        <a href="javascript:void(0)" id="date-btn"><img src="../../../images/btns/calendar.png" width="21" height="22" alt="" /></a>
        <input type="text" name="time" id="time" value="<?=(isset($_POST['time'])?htmlspecialchars($_POST['time']):date('H:i:s'))?>"/>
        </div>
        </div>
    </td>
</tr>
<tr>
    <td colspan="2" style="padding: 10px 0 15px;">
        <input type="submit" value=" Зачислить ">
    </td>
</tr>
</table>
</form>

<form action="./" name="daterange" id="goaction">
<table cellpadding="0" cellspacing="0" border="0" style="margin-top: 15px;">
<tr>
    <td>
        с: <input class="plain" name="ds" value="<?=date("d-m-Y",strtotime($ds))?>" size="12" style="border: 1px solid #DFDFDF; height: 21px"><a href="javascript:void(0)" onclick="if(self.gfPop)gfPop.fStartPop(document.daterange.ds,document.daterange.de);return false;"><img class="PopcalTrigger" align="absmiddle" src="/scripts/DateRange/calbtn.gif" width="34" height="22" border="0" alt=""></a>
        по: <input class="plain" name="de" value="<?=date("d-m-Y",strtotime($de))?>" size="12" style="border: 1px solid #DFDFDF; height: 21px"><a href="javascript:void(0)" onclick="if(self.gfPop)gfPop.fEndPop(document.daterange.ds,document.daterange.de);return false;"><img class="PopcalTrigger" align="absmiddle" src="/scripts/DateRange/calbtn.gif" width="34" height="22" border="0" alt=""></a>
    </td>
    <td style="padding-left:15px;"><input type="submit" value="OK" style="height: 21px"></td>
</tr>
</table>
</form>


    <br/>

<table id="table_list" width="100%" cellpadding="0" cellspacing="0">
<tr>
    <th><strong>Дата</strong></th>
    <th><strong>Логин</strong></th>
    <?/* <th><strong>Сумма, FM</strong></th> */?>
    <th><strong>Сумма, рубли</strong></th>
    <th>&nbsp;</th>
</tr>
<?php
if ( count($aData) ) {
    $nSummR  = 0;
    $nSummFM = 0;
	foreach ( $aData  as $aOne) {
?>
<tr>
    <td><?=date("d.m.Y H:i:s",strtotime($aOne['from_date']))?></td>
    <td><?=$aOne['uname']?> <?=$aOne['usurname']?> [<?=$aOne['login']?>]</td>
    <?/* <td><?=$aOne['ammount']?></td> */?>
    <td><?=$aOne['trs_sum']?></td>
    <td><a id="del_alpha_pay_id_<?=$aOne['id']?>" href="/siteadmin/alpha/?ds=<?=date("d-m-Y",strtotime($ds))?>&de=<?=date("d-m-Y",strtotime($de))?>&action=del&id=<?=$aOne['id']?>&uid=<?=$aOne['uid']?>" onClick="return addTokenToLink('del_alpha_pay_id_<?=$aOne['id']?>', 'Вы действительно хотите отменить зачисление средств?')" title="Удалить"><strong>X</strong></a></td>
</tr>
<?php
        $nSummR  += $aOne['trs_sum'];
        $nSummFM += $aOne['ammount'];
	}
?>
<tr>
    <td colspan="2"><strong>Итого</strong></td>
    <?/*<td><strong><?=sprintf('%01.2f', $nSummFM)?></strong></td>*/?>
    <td><strong><?=sprintf('%01.2f', $nSummR)?></strong></td>
    <td>&nbsp;</td>
</tr>
<?php
}
else {
?>
<tr>
    <td colspan="5">Нет данных за указанный период</td>
</tr>
<?php
}
?>
</table>

<script type="text/javascript">
<?php if ( isset($bAskForce) && $bAskForce ): ?>
if (confirm('Отмена зачисление средств приведет к отрицательному балансу на счету пользователя.\nВсе равно отменить?')) {
    window.location = '/siteadmin/alpha/?ds=<?=date("d-m-Y",strtotime($ds))?>&de=<?=date("d-m-Y",strtotime($de))?>&action=del&id=<?=$sId?>&uid=<?=$sUid?>&force=1';
}
<?php endif; ?>

window.addEvent('domready', function() {
    try {
        new tcal ({
            'formname': 'frm',
            'controlname': 'date',
            'iconId': 'date-btn'
        });
    } catch (err) {}
    
    if ($('date')) {
        $('date').addEvent('change', function() {
            re = /([\d]{2})-([\d]{2})-([\d]{4})/;
            ds = this.get('value');

            dt = new Date();
            dt.parse([ds.replace(re, '$3'), ds.replace(re, '$2'), ds.replace(re, '$1')].join('-'));
        });
        $('date').fireEvent('change');
    }
});
</script>

<iframe width=132 height=142 name="gToday:contrast" id="gToday:contrast" src="/scripts/DateRange/ipopeng.htm" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
</iframe>