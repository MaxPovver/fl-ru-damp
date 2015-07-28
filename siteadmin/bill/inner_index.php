<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr.php");
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/op_codes.php");
	
	if (!hasPermissions('payments'))
		{header ("Location: /404.php"); exit;}
		
	
	$account = new account();
	$op_codes = new op_codes();
	
	
	
	$user = new users();
	$login = trim($_GET['login']);
	if (!$login) $login = trim($_POST['login']);
	$user->GetUser($login);
	$uid = $user->GetUid($error, $user->login);
	
	$action = trim($_GET['action']);
	if (!$action) $action = trim($_POST['action']);
	
	$ok = $account->GetInfo($uid, true);

	switch ($action) {
		case "blocked":
			$opid = intval($_GET['opid']);
			if($ok = $account->Blocked($_SESSION['uid'], $opid)) {
                header('Location: /siteadmin/bill/?login='.$login);
                exit;
            }
			break;
		case "unblocked":
			$opid = intval($_GET['opid']);
			if($ok = $account->unBlocked($_SESSION['uid'], $opid)) {
                header('Location: /siteadmin/bill/?login='.$login);
                exit;
            }
			break;	
		case "delete":
			$opid = intval($_GET['opid']);
            $op_code = intval($_GET['op_code']);
            $pro_opid = array(1,2,3,4,5,6,15,48,49,50,51,76, 90, 91, 92, 93);
            if(in_array($op_code, $pro_opid)) {
                $sql = "UPDATE users SET is_pro_auto_prolong = FALSE WHERE login = ?";
                $DB->query($sql, $login);
            }
			print $account->Del($uid, $opid);
            header('Location: /siteadmin/bill/?login='.$login);
            exit;
			break;
		case "modify":
            if ( !is_release() || in_array($_SESSION['login'], $GLOBALS['balanceCanChangeAdmins']) ) {
                $ucomment = trim($_POST['ucomment']);
                $scomment = trim($_POST['scomment']);
                $trs_sum = floatval($_POST['val']);
                $sum = floatval($_POST['val']);
                $op_type = floatval($_POST['op_type']);
                $for = floatval($_POST['for']);
                $fort = floatval($_POST['fort']);
                $nrid = floatval($_POST['nrid']);
                $op_date = date('c', strtotime($_POST['date']));
                switch ($op_type){
                    case 0:	
                        $account->depositEx($account->id, $sum, $scomment, $ucomment, 13, $trs_sum, NULL, $op_date); break;
                    case 1: 
                        if ($for && $fort){
                            $account->depositEx($account->id, $sum, $scomment, $ucomment, 12, $for, $fort, $op_date);
                        } else $error = "Укажите исходные валюты! Валюту в FM указывать не обязательно!";
                        break;
                    case 2: 
                         $account->depositBonusEx($account->id, $sum, $scomment, $ucomment, 13, $op_date); break;
                    case 3: 
                        if ($for && $fort && $nrid){
                        $account->deposit($op_id, $account->id, $sum, $ucomment, $fort, $for, 36, $nrid, $op_date);
                        } else $error = "Укажите исходные валюты и номер СбР!";
                }
                header('Location: /siteadmin/bill/?login='.$login);
                exit;
            } else {
                break;
            }
		case "filter":
		    
		    $filter['num_operation'] = trim($_POST['num_operation']);
		    $filter['date_from']     = $_POST['date_from']!= "" ? $_POST['date_from'] : false;
		    $filter['date_to']       = $_POST['date_to']  != "" ? $_POST['date_to']   : false;
		    $filter['sort']          = intval($_POST['sort']);
		    $filter['sum_from']      = $_POST['sum_from'] != "" ? intval($_POST['sum_from']) : false;
		    $filter['sum_to']        = $_POST['sum_to']   != "" ? intval($_POST['sum_to'])   : false;
		    $filter['op_code']       = intval($_POST['event']);
		    $filter['limit']         = intval($_POST['limit']);
		    
 		    break;
	}
	
	if(count($filter)) {
	    $history = $account->GetHistoryByFilter($uid, $filter);
	} else {
	    $history = $account->GetHistory($uid, 1);
	}
	
	$opc = $account->GetHistoryOpCodes($uid);
	foreach($opc as $opcode) $evt[] = $opcode['op_code'];
	$events = $op_codes->getCodes($evt);
	
	require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/bill.common.php");
	$xajax->printJavascript('/xajax/');
?>
<style>
.bt, tr.bt td   {border-top:1px solid #d0d0d0}
.br, tr.br td   {border-right:1px solid #d0d0d0}
.bb, tr.bb td   {border-bottom:1px solid #d0d0d0}
.bl, tr.bl td   {border-left:1px solid #d0d0d0}
.ba, tr.ba td   {border:1px solid #d0d0d0}
.ac, tr.ac td   {text-align:center}
</style>
<h2>История платежей</h2>
Аккаунт: <a href="/users/<?=$user->login?>" class="blue"><?=$user->uname?> <?=$user->usurname?> [<?=$user->login?>]</a><br>
Номер счета: <?=$account->id?>
<h3 style="margin-bottom:0px;">На счету <?= round($account->sum, 2);?> руб.</h3>
<strong>на бонусном счету - <?=zin(round($account->bonus_sum, 2))?> руб.</strong><br /><br />

<? if ( !is_release() || in_array($_SESSION['login'], $GLOBALS['balanceCanChangeAdmins']) ) { ?>
<form action="." method="post">
Изменить счет пользователя
<table cellspacing="2" cellpadding="2" border="0">
<tr>
	<td>на сумму(руб):</td>
	<td><input type="text" name="val"></td>
</tr>
<tr>
	<td>комментарий (для пользователя)</td>
	<td><textarea cols="50" rows="" name="ucomment"></textarea></td>
</tr>
<tr>
	<td>комментарий (для системы)</td>
	<td><textarea cols="50" rows="" name="scomment"></textarea></td>
</tr>
<tr>
	<td>дата операции</td>
	<td><input type="text" name="date" value="<?=date('d.m.Y H:i:s')?>"></td>
</tr>
<tr>
	<td>Валюта</td>
	<td><input type="text" name="for"> 
	<select name="fort">
		<option value="0" SELECTED>нет</option>
		<option value="1">WMZ</option>
		<option value="2">WMR</option>
		<option value="3">ЯД</option>
		<option value="4">Безнал (ЮЛ)</option>
		<option value="5">Безнал (ФЛ)</option>
		<option value="6">ASSIST</option>
		<option value="8">ОСМП</option>
		<option value="7">СМС</option>
	</select> </td>
</tr>
<tr>
	<td>Вид операции</td>
	<td><select name="op_type">
		<option value="0" SELECTED>Другая операция</option>
		<option value="1">Изменение счета</option>
		<option value="2">Изменение бонусного счета</option>
		<option value="3">Резерв под СбР</option>
	</select></td>
</tr>
<tr>
	<td>Номер СбР (id)</td>
	<td><input type="text" name="nrid"></td>
</tr>
</table>
<input type="hidden" name="action" value="modify">
<input type="hidden" name="login" value="<?=$user->login?>">
<input type="submit" value="Ага!">
</form>
<? } ?>

<?php
$limit_options = array(10,20,50,100);
?>
<form name="frm" id="frm" method="POST">
<input type="hidden" name="action" value="filter">
<div class="money-filtr">
    <div class="form "> <!-- fs-dg, для перепопределения -->
      <b class="b1"></b>
      <b class="b2"></b>
      <div class="form-in">
      	<h4>Фильтр</h4>
        <div class="form-block first">
            <table>
                <tr>
                    <td>
                    	<table>
                        	<tr>
                            	<td width="90">№</td>
                                <td class="expl"><input name="num_operation" type="text" class="form-text" value="<?= htmlspecialchars($_POST['num_operation'])?>"/> &#160;&#160;&#160;&#160;Например: 74*</td>
                            </tr>
                        	<tr>
                            	<td>Дата:</td>
                                <td>
                                    <div class="form-value">
                                    <input name="date_from" id="date_form" type="text" value="<?= htmlspecialchars($_POST['date_from'])?>" class="form-text" readonly="readonly" />
                                    <a class="apf-date" id="date_from_btn" href="javascript:void(0);"><img src="../../../images/btns/calendar.png" width="21" height="22" alt="" align="absmiddle" /></a> 
                                    &mdash; 
                                    <input name="date_to" id="date_to" class="form-text" type="text" value="<?= htmlspecialchars($_POST['date_to'])?>" readonly="readonly"/>
                                    <a class="apf-date" id="date_to_btn"><img src="../../../images/btns/calendar.png" width="21" height="22" alt="" align="absmiddle"/></a>
                                    </div>
                                </td>
                            </tr>
                        	<tr>
                            	<td>Сортировать:</td>
                                <td>
                                  <select name="sort">
                                    <option value="1" <?= ($_POST['sort'] == 1?'selected="selected"':'');?>>По дате</option>
                                    <option value="2" <?= ($_POST['sort'] == 2?'selected="selected"':'');?>>По номеру</option>
                                    <option value="3" <?= ($_POST['sort'] == 3?'selected="selected"':'');?>>По сумме</option>
                                  </select>
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td width="50"></td>
                    <td>
                    	<table>
                        	<tr>
                            	<td width="90">Сумма:</td>
                                <td><input name="sum_from" type="text" class="form-text" value="<?= htmlspecialchars($_POST['sum_from'])?>"/> &mdash; <input name="sum_to" type="text" class="form-text" value="<?= htmlspecialchars($_POST['sum_to'])?>"/></td>
                            </tr>
                        	<tr>
                            	<td>Событие:</td>
                                <td>
                                    <select name="event">
                                      <option value="0">Все события</option>
                                      <?php foreach($events as $event) {?>
                                      <option value="<?= $event['id']?>" <?= ($_POST['event'] == $event['id']?'selected="selected"':'');?>><?= $event['op_name']?></option>
                                      <?php } //foreach?>
                                    </select>
                                </td>
                            </tr>
                        	<tr>
                            	<td>Вывести:</td>
                                <td class="how">
                                    <select name="limit">
                                        <option value="0">Все</option>
                                        <?php foreach($limit_options as $limit) {?>
                                        <option value="<?= $limit?>" <?= ($_POST['limit'] == $limit?'selected="selected"':'');?>><?= $limit?></option>
                                        <?php }//foreach?>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
        <div class="form-block last">
        	<div class="form-el form-btns flm">
				<input name="submit" value="Применить" type="submit" />
                <a href="javascript:void(0)" onClick="FilterClearForm();">Сбросить фильтр</a>
			</div>
        </div>
      </div>
      <b class="b2"></b>
      <b class="b1"></b>
    </div>
</div>
</form>

<script>
new tcal ({ 'formname': 'frm', 'controlname': 'date_form', 'iconId': 'date_from_btn' });
new tcal ({ 'formname': 'frm', 'controlname': 'date_to', 'iconId': 'date_to_btn' });

function FilterClearForm()
{
    var elm;
    var form = document.forms["frm"];
    if (form.reset) {
        form.reset();
    }
    for(var i = 0; i<form.elements.length; i++) {
        elm=form.elements[i];
        if(elm.type == 'checkbox') {
            elm.checked=false;
            continue;
        }
        if(elm.type != "button" && elm.type != "submit" && elm.tagName != 'SELECT' && elm.type != "hidden") elm.value = "";
    }
    
}
</script>
<? if ($history) { ?>
<table width="100%" border="0" cellspacing="0" cellpadding="4" class="qpr exch">
<tr>
	<td colspan="6" class="small">&nbsp;</td>
</tr>
<tr>
	<td width="100">№</td>
    <td width="120">Дата</td>
    <td width="80">Баланс</td>
    <td width="80">Сумма</td>
    <td width="220">Событие</td>
    <td>Комментарии</td>
</tr>
<? 
  foreach($history as $ikey => $hist) {
      $comments = $hist['comments'];
      if(in_array($hist['op_code'], array(sbr::OP_RESERVE, sbr::OP_DEBIT, sbr::OP_CREDIT))) {
          $scheme = strtotime($hist['op_date']) > mktime(0,0,0, 11, 1, 2012) ? sbr::SCHEME_LC : sbr::SCHEME_AGNT;
          $comments = sbr_meta::parseOpComment($comments, $user->login, is_emp($user->role), $scheme);        
      }
          

?>
<tr class="small" valign="baseline">
	<td><a name="<?=$hist['id']?>"></a><?=$hist['id']?></td>
	<td><?=date("d.m.Y | H:i",strtotimeEx($hist['op_date']))?></td>
    <td><?= view_cost_format($hist['balance'], false) ?></td>
	<td><?=(($hist['ammount'] > 0)?"+":"") . ( round($hist['ammount'],2) )?></td>
	<td><?=str_replace( '%username%', $user->login, $hist['op_name'] )?><br><br>
	<?php if(!in_array($hist['op_code'], array(12,23))) { ?>
	<a href=".?action=delete&login=<?=$user->login?>&opid=<?=$hist['id']?>&op_code=<?=$hist['op_code']?>" class="blue">Удалить</a> | <a href="" class="blue">Изменить</a> 
	<?php } //if?>
	<? if($hist['op_code'] == 23 && !($hist['is_blocked']>0)): ?> <a href="#" id="lock_<?=$hist['id']?>" onclick="xajax_BlockOperation(<?=$hist['id']?>); return false;" style="color:red;">Заблокировать</a><? endif; ?>
	<? if($hist['is_blocked']>0): ?>| <a href="#" id="lock_<?=$hist['id']?>" onclick="xajax_UnBlockOperation(<?=$hist['id']?>); return false;" style="color:red;">Разблокировать<? endif; ?>
	</td>
	<td><?=($comments ? (stripslashes(reformat($comments, 35, 0, 1)).'<br/>'):'')?>
	<?	if ($hist['op_code'] < 7 || $hist['op_code'] == 12 || $hist['op_code'] == 8 || $hist['op_code'] == 10 || $hist['op_code'] == 11 || $hist['op_code'] == 23
	         || $hist['op_code'] ==131 || $hist['op_code'] ==132 || $hist['op_code'] >= 48 && $hist['op_code'] <= 51 || $hist['op_code'] == 19 || $hist['op_code'] == 20 || $hist['op_code'] == 52 || $hist['op_code'] == 63 || $hist['op_code'] == 64 || $hist['op_code'] == 76) { ?>
	 <a class="blue" style="cursor: pointer" onclick="xajax_ShowBillComms(<?=$hist['id']?>, <?=$uid?>, 2);">Подробнее</a>
	 <div id="bil<?=$hist['id']?>" class="small"></div>
	  <? } ?>
	</td>
</tr>
<? }?>
</table>
<? } else { ?>
<div style="margin: 10px 0 0 5px;">Платежей нет</div>
<? } ?>
