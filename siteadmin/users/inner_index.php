<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }
	if (!(hasPermissions('users') && hasPermissions('adm'))) { exit; }
$users = new users();
$frl_pp = intval(trim($_GET['pp']));
if (!$frl_pp) $frl_pp = FRL_PP;
$page = intval(trim($_GET['page']));
if (!$page) $page = 1;
$stype = trim($_GET['type']);
if (!$stype) $stype = trim($_POST['type']);
if ($stype == "emp") $type = "empmask"; else $type = "frlmask";

$action = trim($_GET['action']);
if (!$action) $action = trim($_POST['action']);
if ($stype == "ban")  { $additsql="is_banned='1'"; $no_more = 1; }
if ($stype == "active")  $additsql="active=false";
if ($stype == "warns")  { $additsql="warn > 0"; $no_more = 1; }
if ($stype == "emp" || $stype == "frl" || !$stype) { $additsql="(role&'$empmask')='".$$type."' AND is_banned='0'"; $no_more = 1; }
$can_modify = hasPermissions('users');
switch ($action){
      
    case "activate":
        $login = trim(stripslashes($_GET['login']));
        if ($login) $error = users::SetActive($login);
        require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/users.php');
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/wizard/wizard_registration.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/wizard/step_employer.php");
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/wizard/step_freelancer.php");
        $user = new users();
        $user->GetUser($login);
        if($user->role[0] == 1) {
            $wiz_user = wizard::isUserWizard($user->uid, step_employer::STEP_REGISTRATION_CONFIRM, wizard_registration::REG_EMP_ID);
        } else {
            $wiz_user = wizard::isUserWizard($user->uid, step_freelancer::STEP_REGISTRATION_CONFIRM, wizard_registration::REG_FRL_ID);
        }
        if($wiz_user['id'] > 0) {
            step_wizard::setStatusStepAdmin(step_wizard::STATUS_COMPLITED, $user->uid, $wiz_user['id']);
        }
        break;
    /*case "delete":
        $login = trim($_GET['login']);
        if ($login) $error = users::DeleteUser(0, 0, $error, $login, hasPermissions('users'));
        break;*/
    case "unwarn":
        $login = trim(stripslashes($_GET['user']));
        $usr = new users();
        $error = $usr->UnWarn($login);
        print $error;
        break;
    case "block_money":
        $user_id = intval(stripslashes($_GET['id']));
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
        account::setBlockMoney($user_id, true);
        $search = trim(stripslashes($_POST['search']));
        if (!$search) $search = trim(stripslashes($_GET['search']));
        if (stripslashes($_POST['exact'])) {
            $ss = $DB->parse('?', $search);
            $additsql .= " AND (LOWER(uname) = LOWER({$ss}) OR LOWER(usurname) = LOWER({$ss}) OR LOWER(login) = LOWER({$ss}) OR LOWER(email) = LOWER({$ss}) OR LOWER(old_login) = LOWER({$ss}) OR users.uid IN (SELECT uid FROM users_change_emails_log WHERE LOWER(email) = LOWER({$ss})))";
        } else {
            $ss = $DB->parse('?', "%{$search}%");
            $additsql .= " AND (uname ILIKE {$ss} OR usurname ILIKE {$ss} OR login ILIKE {$ss} OR email ILIKE {$ss} OR icq ILIKE {$ss} OR old_login ILIKE {$ss} OR users.uid IN (SELECT uid FROM users_change_emails_log WHERE email ILIKE {$ss}))";
        }
		$no_more = 0;
        break;
    case "unblock_money":
        $user_id = intval(stripslashes($_GET['id']));
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
        account::setBlockMoney($user_id, false);
        $search = trim(stripslashes($_POST['search']));
        if (!$search) $search = trim(stripslashes($_GET['search']));
        if (stripslashes($_POST['exact'])) {
            $ss = $DB->parse('?', $search);
            $additsql .= " AND (LOWER(uname) = LOWER({$ss}) OR LOWER(usurname) = LOWER({$ss}) OR LOWER(login) = LOWER({$ss}) OR LOWER(email) = LOWER({$ss}) OR LOWER(old_login) = LOWER({$ss}) OR users.uid IN (SELECT uid FROM users_change_emails_log WHERE LOWER(email) = LOWER({$ss})))";
        } else {
            $ss = $DB->parse('?', "%{$search}%");
            $additsql .= " AND (uname ILIKE {$ss} OR usurname ILIKE {$ss} OR login ILIKE {$ss} OR email ILIKE {$ss} OR icq ILIKE {$ss} OR old_login ILIKE {$ss} OR users.uid IN (SELECT uid FROM users_change_emails_log WHERE email ILIKE {$ss}))";
        }
		$no_more = 0;
        break;  
    case "search":
        $search = trim(stripslashes($_POST['search']));
        if (!$search) $search = trim(stripslashes($_GET['search']));
        if (stripslashes($_POST['exact'])) {
            $ss = $DB->parse('?', $search);
            $additsql .= " AND (LOWER(uname) = LOWER({$ss}) OR LOWER(usurname) = LOWER({$ss}) OR LOWER(login) = LOWER({$ss}) OR LOWER(email) = LOWER({$ss}) OR LOWER(old_login) = LOWER({$ss}) OR users.uid IN (SELECT uid FROM users_change_emails_log WHERE LOWER(email) = LOWER({$ss})))";
        } else {
            $ss = $DB->parse('?', "%{$search}%");
            $additsql .= " AND (uname ILIKE {$ss} OR usurname ILIKE {$ss} OR login ILIKE {$ss} OR email ILIKE {$ss} OR icq ILIKE {$ss} OR old_login ILIKE {$ss} OR users.uid IN (SELECT uid FROM users_change_emails_log WHERE email ILIKE {$ss}))";
        }
		$no_more = 0;
        break;
    case "searchip":
        $frl_pp = 30;
        $searchip = trim(stripslashes($_POST['searchip']));
        if (!$searchip) $searchip = trim(stripslashes($_GET['searchip']));
        $users = $users->FindByIp($searchip, $count, $frl_pp, (intval($page-1)*$frl_pp));
        $no_more = 1;
        break;
    case "searchcard":
        $searchcard = trim(stripslashes($_POST['searchcard']));
        if (!$searchcard) $searchcard = trim(stripslashes($_GET['searchcard']));
        $users = $users->FindByCard($searchcard);
        $no_more = 1;
        break;
    case "searchbyid":
        $search = trim(stripslashes($_GET['search']));
        $additsql = "(users.uid = $search)";
        unset($search);
		$no_more = 0;
    case "selacop": 
        $fdate = trim(stripslashes($_GET['fdate']));
        $tdate = trim(stripslashes($_GET['tdate']));
        $akop = trim(stripslashes($_GET['akop']));
        $domain_id = __paramInit( 'int', 'domain_id', null, 0 );
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
        $users = account::GetUsersByAkOp( $akop, $fdate, $tdate, $domain_id );
        foreach($users as $value) {
            if($akop == 1  && $value['op_code'] == 108) $value['ammount'] = "-19.00";
            if($akop == 73 && $value['op_code'] == 108) $value['ammount'] = "-1.00";
            $history_bill[$value['uid']][] = 
                            array("ammount"  => $value['ammount'], 
                                  "op_date"  => $value['op_date'],
                                  "comments" => $value['descr']. ($value['comments']?" ({$value['comments']})":""));
        }
        $no_more = 1;
        break;
    case "change_email":
        $res=users::ChangeMail(trim($_POST["uid"]),trim($_POST["email"]));
        if ($res) {
            $_SESSION['a_alert_msg'] = $res;
        }
        ob_end_clean();
        header('Location: ?type='.$_POST['s_type'].'&page='.$_POST['s_page'].'&search='.$_POST['s_search'].'&searchip='.$_POST['s_searchip'].'&action='.$_POST['s_action'].'#user_'.$_POST['uid']);
        exit;
        break;
    case "change_safety_phone":
        $res = users::ChangeSafetyPhone(trim($_POST["uid"]),$_POST["safety_phone"],$_POST['safety_only_phone']);
        if ($res) {
            $_SESSION['a_alert_msg'] = $res;
        }
        ob_end_clean();
        header('Location: ?type='.$_POST['s_type'].'&page='.$_POST['s_page'].'&search='.$_POST['s_search'].'&searchip='.$_POST['s_searchip'].'&action='.$_POST['s_action'].'#user_'.$_POST['uid']);
        exit;
        break;
    case "change_safety_ip":
        $res = users::ChangeSafetyIP(trim($_POST["uid"]),$_POST["safety_ip"]);
        if ($res) {
            $_SESSION['a_alert_msg'] = $res;
        }
        ob_end_clean();
        header('Location: ?type='.$_POST['s_type'].'&page='.$_POST['s_page'].'&search='.$_POST['s_search'].'&searchip='.$_POST['s_searchip'].'&action='.$_POST['s_action'].'#user_'.$_POST['uid']);
        exit;
        break;
    case "change_safety_ip":
        break;
    case "nulrating":
		users::NullRating(intval($_GET["id"]), hasPermissions('all')); // !!! только админы
		break;
	 case "chmoder":
		users::ChModer(intval($_GET["id"]), hasPermissions('users'));
		break;
	 case "chredact":
	    users::ChRedact(intval($_GET["id"]), hasPermissions('users'));
		break;
}

require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/banned.common.php");
$xajax->printJavascript('/xajax/');

if (!$no_more)
	$users = $users->GetAllEx($count, $infos, $fpinfos, $additsql, "login", $frl_pp, ($page-1)*$frl_pp);
?>
<style>
INPUT.exact { margin-left: 0; }

.bill_info {
    background: silver;
}
.bill_info tbody tr {
    background: #fff;
}
.bill_info thead tr {
    background: whitesmoke;
}
.bill_info tr td {
    border:0px;
}
.bill_info thead tr td {
    text-align:center;
    font-weight: bold;
}
</style>
<!--[if IE]>
<style>
INPUT.exact { margin-left: -4px }
</style>
<![endif]-->
<script type="text/javascript">
banned.addContext( 'all', -1, '', '' );
banned.zero = true;
</script>
<strong>Пользователи</strong><br><br>
<a href="/siteadmin/users/?type=frl" class="blue">Фрилансеры</a> | <a href="/siteadmin/users/?type=emp" class="blue">Работодатели</a> | <a href="/siteadmin/users/?type=ban" class="blue">Забаненные</a> | <a href="/siteadmin/users/?type=active" class="blue">Не активированные</a>
 | <a href="/siteadmin/users/?type=warns" class="blue">С предупреждениями</a>
<br><br>
<table cellspacing="2" cellpadding="2" border="0">
<tr>
<td>
<form action="." method="post">
<table cellpadding="0" cellspacing="0" border="0">
<tr>
    <td>
    <input type="text" name="search" value="<?=htmlspecialchars($search, ENT_QUOTES)?>">
    <input type="hidden" name="action" value="search">
    <input type="hidden" name="type" value="<?=htmlspecialchars($stype, ENT_QUOTES)?>">
    <input type="submit" name="btn" value="Искать">
    </td>
</tr>
<tr>
    <td><input type="checkbox" name="exact" value="1" class="exact"<?=($_POST['exact']? ' checked': '')?>>точное совпадение</td>
</tr>
</table>
</form>
</td>
<td valign="top">
<table cellpadding="0" cellspacing="0" border="0">
<tr>
    <td>
    Искать по ip:
    </td>
    <td>
    <form action="." method="post">
    <input type="text" name="searchip" value="<?=htmlspecialchars($searchip, ENT_QUOTES)?>">
    <input type="hidden" name="action" value="searchip">
    <input type="hidden" name="type" value="<?=htmlspecialchars($stype, ENT_QUOTES)?>">
    <input type="submit" name="btn" value="Искать">
    </form>
    </td>
</tr>
<tr>
    <td>
    По карте:
    </td>
    <td>
    <form action="." method="post">
    <input type="text" name="searchcard" value="<?=htmlspecialchars($searchcard, ENT_QUOTES)?>">
    <input type="hidden" name="action" value="searchcard">
    <input type="hidden" name="type" value="<?=htmlspecialchars($stype, ENT_QUOTES)?>">
    <input type="submit" name="btn" value="Искать">
    </form>
    </td>
</tr>
</table>

</td>
</tr>
</table>
<br>
<table width="100%" border="0" cellspacing="2" cellpadding="2">
<? $info_iter = 0; $fpinfo_iter = 0; if ($users) foreach($users as $ikey=>$user){ 
 $utype = (is_emp($user['role']))?"emp":"frl";
 if ($user['login'] == $old_user['login']) continue;
 $old_user['login'] = $user['login'];
 $safety_ip = '';
 $safety_ips = users::GetSafetyIP($user['uid']);
 while(list($k,$v)=each($safety_ips)) {
     $safety_ip .= $v.", ";
 }
 $safety_ip = preg_replace("/, $/",'',$safety_ip);

	?>
<tr class="qpr">
	<td>
		<table width="100%" cellspacing="0" cellpadding="0" border="0">
		<tr valign="top" class="n_qpr">
			<td width="70" align="center"><a name="user_<?=$user['uid']?>"></a><a href="/users/<?=htmlspecialchars($user['login'], ENT_QUOTES)?>" class="<?=$utype?>name11"><?=view_avatar($user['login'], $user['photo']) ?></a></td>
			<td>
<table width="700">
<tr>
<td>
			<?=$session->view_online_status($user['login'])?> <a href="/users/<?=htmlspecialchars($user['login'], ENT_QUOTES)?>" class="<?=$utype?>name11"><?=htmlspecialchars($user['usurname']." ".$user['uname'])?></a> [<a href="/users/<?=htmlspecialchars($user['login'], ENT_QUOTES)?>" class="<?=$utype?>name11"><?=htmlspecialchars($user['login'], ENT_QUOTES)?></a>] <a href="mailto:<?=htmlspecialchars($user['email'], ENT_QUOTES)?>"><?=htmlspecialchars($user['email'], ENT_QUOTES)?></a>
			<? if ($user['old_login']) {
			   $j = $ikey + 1;
			   unset($old_logs);
			   $old_logs[] = $user['old_login'];
			   while ($users[$j]['login'] == $user['login'])
			   		$old_logs[] = $users[$j++]['old_login'];
				?><font color="red">(<?=implode(", ",$old_logs)?>)</font><? } ?>
</td>
<td width="30">&nbsp;</td>
<td width="280">
                <table cellpadding="0" cellspacing="0">
                    <tr>
                        <td>E-mail:</td>
                        <td>
                            <form action="<?=$_SERVER['PHP_SELF']?>" method="POST" ><input type="hidden" name="action" value="change_email"><input type="hidden" name="uid" value="<?=htmlspecialchars($user['uid'], ENT_QUOTES)?>">
                            <input type="hidden" name="s_type" value="<?=htmlspecialchars($stype, ENT_QUOTES)?>">
                            <input type="hidden" name="s_page" value="<?=htmlspecialchars($page, ENT_QUOTES)?>">
                            <input type="hidden" name="s_action" value="<?=htmlspecialchars($action, ENT_QUOTES)?>">
                            <input type="hidden" name="s_search" value="<?=htmlspecialchars($search, ENT_QUOTES)?>">
                            <input type="hidden" name="s_searchip" value="<?=htmlspecialchars($searchip, ENT_QUOTES)?>">
                            <input type="text" name="email" value="<?=htmlspecialchars($user['email'], ENT_QUOTES)?>"> <input type="submit" value="сменить">
                            </form>
                            </td>
                    </tr>
                </table>
</td>
</table>
			<br>
			<br>

			<? if ($stype != "active") {	?>
			
			<?php
            $sBanTitle = (!$user['is_banned'] && !$user['ban_where']) ? 'Забанить!' : 'Разбанить';
            ?>
            <div style="float: left;" class="warnlink-<?=$user['uid']?>"><a style="color:red;" href="javascript:void(0);" onclick="banned.userBan(<?=$user['uid']?>, 'all',0)"><?=$sBanTitle?></a>&nbsp;</div>| 
            
			<? } if ($stype == "active")	{ ?>

			<a href="/siteadmin/users/?action=activate&amp;login=<?=$user['login']?>&amp;type=<?=$utype?>" class="blue">активировать</a> |

			<? } ?>
			
			<? /* <a href="/siteadmin/users/?action=delete&amp;login=<?=$user['login']?>&amp;type=<?=$utype?>" class="blue" onclick="return warning(20);">удалить</a> */ ?>

			<? if (hasPermissions('users') && $stype != "active") { ?>
                <?php if ( hasPermissions('payments') ) { ?>
                <a href="/siteadmin/bill/?login=<?=$user['login']?>" class="blue">счет</a> | 
                <?php } ?>
			 
                <?php if ( hasPermissions('all') ) { // !!! только админы ?>
                <a href=".?action=nulrating&id=<?=$user['uid']?>" class="blue">обнулить рейтинг</a> | 
                <?php } ?>
			<? } if (hasPermissions('users') && $stype != "active") { ?>
			
			<a href="javascript: void(0);" class="blue" onclick="banned.warnUser(<?=$user['uid']?>, 0, 'admpage', 'all', 0); return false;">сделать предупреждение</a> |
			<? if($user['is_block_money'] == 'f'): ?>
			<a href=".?action=block_money&id=<?=$user['uid']?>&search=<?=$user['login']?>" class="public_red_normal">заблокировать деньги</a>
			<? else: ?>
			<a href=".?action=unblock_money&id=<?=$user['uid']?>&search=<?=$user['login']?>" class="public_red_normal">разблокировать деньги</a>
			<? endif; ?>
			
			<br><br>
			<? if (hasPermissions('users')) { ?> Права: 
			<a href=".?action=chmoder&id=<?=$user['uid']?>" class="blue"><?=((is_moder($user['role'])?"снять":"дать"))?> модератора</a> |
			<a href=".?action=chredact&id=<?=$user['uid']?>" class="blue"><?=((is_redactor($user['role'])?"снять":"дать"))?> редактора</a>
            <? if($user['active']=='f') { ?> | <a href="/siteadmin/users/?action=activate&amp;login=<?=$user['login']?>&amp;type=<?=$utype?>" class="blue">активировать</a><? } ?>
            <br>
            <? } ?>
			<? }elseif($stype == "active") { ?> <BR> <? } ?>

			<br>

            <table cellpadding="0" cellspacing=0"><tr><td valign="top" width="405">
            <table cellpadding="0" cellspacing="0">
                <tr>
                    <td colspan="2">
                    <table>
                    <tr>
                    <td width="50">IP reg:</td>
                    <td><div id="ip_reg_<?=$user['uid']?>" style="float:left; width: 80px;"><?=$user['reg_ip']?></div></td>
                    <td width="30">&nbsp;</td>
                    <td>IP last:</td>
                    <td><div id="ip_last_<?=$user['uid']?>" style="float:left; width: 80px;"><?=$user['last_ip']?></div></td>
                    </tr>
                    </table>
                    <a href="#" onClick="javascript:window.open('/siteadmin/users/last10ip.php?uid=<?=$user['uid']?>&login=<?=$user['login']?>&usurname=<?=$user['usurname']?>&uname=<?=$user['uname']?>','LAST10','width=600,height=400,toolbar=no,location=no'); return false;">Последние 10 IP</a> | <a href="#" onClick="javascript:window.open('/siteadmin/users/last10email.php?uid=<?=$user['uid']?>&login=<?=$user['login']?>&usurname=<?=$user['usurname']?>&uname=<?=$user['uname']?>','EMAIL10','width=600,height=400,toolbar=no,location=no'); return false;">Последние 10 e-mail</a>
                    </td>
                </tr>
                <?
                $js_clipboard .= "clip_reg_{$user['uid']} = new ZeroClipboard.Client();
                                  clip_reg_{$user['uid']}.setHandCursor( true );
                      			  clip_reg_{$user['uid']}.addEventListener('mouseOver', function (client) {
                    				clip_reg_{$user['uid']}.setText( $('ip_reg_{$user['uid']}').get('html') );
                      			  });
                                  clip_reg_{$user['uid']}.glue('ip_reg_{$user['uid']}');

                                  clip_last_{$user['uid']} = new ZeroClipboard.Client();
                                  clip_last_{$user['uid']}.setHandCursor( true );
                      			  clip_last_{$user['uid']}.addEventListener('mouseOver', function (client) {
                    				clip_last_{$user['uid']}.setText( $('ip_last_{$user['uid']}').get('html') );
                      			  });
                                  clip_last_{$user['uid']}.glue('ip_last_{$user['uid']}');
                                ";
                ?>

                <?php if($user['icq']) { ?>
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>ICQ:</td>
                    <td><?=$user['icq']?></td>
                </tr>
                <?php } ?>
            </table>
            <table cellpadding="0" cellspacing="0">
                <?php if($user['sum']) { ?>
                <tr>
                    <td width="90">Приход:</td>
                    <td>$<?=$user['sum']?></td>
                </tr>
                <?php } ?>
                <tr>
                    <td width="90">Предупреждений:</td>
                    <td><span id='warnlink-<?=$user['uid']?>'><?=($user['warn'])? "(<a class='blue' href='javascript: void(0);' onclick='if (document.getElementById(\"warnlist-{$user['uid']}\").style.display==\"none\") xajax_GetWarns({$user['uid']}, \"admpage\"); else document.getElementById(\"warnlist-{$user['uid']}\").style.display = \"none\"; return false;'>".$user['warn']."</a>)": "(0)"?></span></td>
                </tr>
            </table>
            </td>
            <td valign="top">
                <table cellpadding="0" cellspacing="0">
                    <tr>
                        <td>IP:</td>
                        <td>
                            <form action="<?=$_SERVER['PHP_SELF']?>" method="POST" ><input type="hidden" name="action" value="change_safety_ip"><input type="hidden" name="uid" value="<?=htmlspecialchars($user['uid'])?>">
                            <input type="hidden" name="s_type" value="<?=htmlspecialchars($stype, ENT_QUOTES)?>">
                            <input type="hidden" name="s_page" value="<?=htmlspecialchars($page, ENT_QUOTES)?>">
                            <input type="hidden" name="s_action" value="<?=htmlspecialchars($action, ENT_QUOTES)?>">
                            <input type="hidden" name="s_search" value="<?=htmlspecialchars($search, ENT_QUOTES)?>">
                            <input type="hidden" name="s_searchip" value="<?=htmlspecialchars($searchip, ENT_QUOTES)?>">
                            <input type="text" name="safety_ip" value="<?=htmlspecialchars($safety_ip, ENT_QUOTES)?>"> <input type="submit" value="сменить">
                            </form>
                        </td>
                    </tr>
                    <form action="<?=$_SERVER['PHP_SELF']?>" method="POST" ><input type="hidden" name="action" value="change_safety_phone"><input type="hidden" name="uid" value="<?=htmlspecialchars($user['uid'], ENT_QUOTES)?>">
                    <tr>
                        <td>Телефон:</td>
                        <td>
                            <input type="hidden" name="s_type" value="<?=htmlspecialchars($stype, ENT_QUOTES)?>">
                            <input type="hidden" name="s_page" value="<?=htmlspecialchars($page, ENT_QUOTES)?>">
                            <input type="hidden" name="s_action" value="<?=htmlspecialchars($action, ENT_QUOTES)?>">
                            <input type="hidden" name="s_search" value="<?=htmlspecialchars($search, ENT_QUOTES)?>">
                            <input type="hidden" name="s_searchip" value="<?=htmlspecialchars($searchip, ENT_QUOTES)?>">
                            <input type="text" name="safety_phone" value="<?=htmlspecialchars($user['safety_phone'], ENT_QUOTES)?>"> <input type="submit" value="сменить">
                        </td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td>
                            <input type="checkbox" name="safety_only_phone" value="t" <?=($user['safety_only_phone']=='t')?'checked':''?>> только по SMS
                        </td>
                    </tr>
                    </form>
                </table>
            </td></tr></table>
            <br />
			<?
			//print_r($user);
			$ban=users::GetBan($user['uid']);
			?>
            <div style='display:none' id="warnreason-<?=$user['uid']?>">&nbsp;</div>
            <div id="warnlist-<?=$user['uid']?>" class="warnings" style="margin-top: 10px; display: none">&nbsp;</div>
			<?=($user['is_banned'])? "Причина бана: ".$ban['comment']."<br>": ""?>
			</td>
		</tr>
		<?php if(isset($history_bill)) { $history_user = $history_bill[$user['uid']];?>
		<tr style="border:0px">
            <td colspan="2" style="border:0px">
                <br/>
                <table width="100%" class="bill_info" cellspacing="1" cellpadding="3">
                    <colgroup>
                        <col width="20%"/>
                        <col width="20%"/>
                        <col width="60%"/>
                    </colgroup>
                    <thead>
                        <tr>
                            <td>Сумма</td>
                            <td>Дата</td>
                            <td>Комментарий</td>
                        </tr>
                    </thead>
                    <tbody> 
                        <?php foreach($history_user as $event) { ?>
                        <tr>
                            <td align="center"><?=round($event['ammount'],2);?></td>
                            <td align="center"><?=date("d.m.Y H:i", strtotime($event['op_date']))?></td>
                            <td><?=$event['comments']?></td>
                        </tr>
                        <?php } //foreach?>
                    </tbody>
                </table><br/>
            </td>
        </tr>
        <?php } //if?>
		</table>
	</td>
</tr>

<? } ?>
</table>
	<? // Страницы
						$pages = ceil($count / $frl_pp);
			if ($pages > 1){ ?>
	<table border="0" cellspacing="1" cellpadding="0" class="pgs">
		<tr>
			<?
			for ($i = 1; $i <= $pages; $i++) {
			    if ($i != $page){
			?>
			<td width="5"><a href=".?page=<?=$i?>&type=<?=htmlspecialchars($stype, ENT_QUOTES)?>&search=<?=htmlspecialchars($search, ENT_QUOTES)?>&action=<?=htmlspecialchars($action, ENT_QUOTES)?><?= $searchip ? "&searchip=".htmlspecialchars($searchip, ENT_QUOTES) : "" ?>" class="pages"><?=$i?></a></td>
			<? }
				else {?>
			<td class="box"><?=$i?></td>
			<?	} ?>
			<? if (ceil($i/30) == floor($i/30)) { ?> </tr><tr> <? }
			} ?></tr>
		</table>
	 <? } // Страницы закончились?>

<script type="text/javascript">
window.addEvent('domready', function() {
    <?
    if($js_clipboard) {
        echo 'ZeroClipboard.setMoviePath("'.$host.'/scripts/zeroclipboard/ZeroClipboard.swf");';
        echo $js_clipboard;
    }
    ?>
});
</script>

<?php
if($_SESSION['a_alert_msg']) {
    echo '<script>alert("'.htmlspecialchars($_SESSION['a_alert_msg'], ENT_QUOTES).'");</script>';
    unset($_SESSION['a_alert_msg']);
}
?>

<?php include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/ban_overlay.php' ); ?>

<!-- редактирование предупреждения старт -->
<?php
include_once( $_SERVER['DOCUMENT_ROOT'] . '/user/warn_overlay.php' );
?>
<!-- редактирование предупреждения стоп -->