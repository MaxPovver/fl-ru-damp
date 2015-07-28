<?
if(!defined('IN_STDF')) { 
    header("HTTP/1.0 404 Not Found");
    exit();
} 
//require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/projects.common.php");
//$xajax->printJavascript('/xajax/');

#if (!$_in_setup) {header ("HTTP/1.0 403 Forbidden"); exit;}
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/account.php");
$projects = new projects();
if ($_POST['openclose']==1) { $_GET["open"]=1; $_GET["closed"]=0;} 
elseif ($_POST['openclose']==2) { $_GET["closed"]=1;  $_GET["open"]=0;} 
else { $_GET["closed"]=0;  $_GET["open"]=0; }


$account = new account();
$ok = $account->GetInfo(get_uid(), true);

$transaction_id = $account -> start_transaction(get_uid());
$is_emp = is_emp();

//print_r($_POST);

$closed=($_GET["closed"] ? "true" : ($_GET["open"] ? "false" : "" ));
$prjs = $projects->GetCurPrjs(get_uid(), $closed);
$proj_groups = professions::GetAllGroupsLite();
$proj_groups_by_id = array();
foreach($proj_groups as $key => $wrk_prjgroup)
{
    $proj_groups_by_id[$wrk_prjgroup['id']] = $wrk_prjgroup['name'];
}

$conted_prj=$projects->CountMyProjects(get_uid());

?>
<script type="text/javascript">

function closeprj(num){
    document.getElementById('frm').prjid.value = num;
    document.getElementById('frm').action.value = 'prj_close';
    document.getElementById('frm').submit();
}
function upprj(num){
    document.getElementById('frm').prjid.value = num;
    document.getElementById('frm').action.value = 'prj_up';
    document.getElementById('frm').submit();
}

</script>
<table width="100%" align="center" cellpadding="0" cellspacing="0" border="0">
<tr valign="middle">
<td style="padding-left: 10px; padding-top: 10px;"><?=(!$_GET["open"] && !$_GET["closed"] ? "<b>Все</b> (".$conted_prj["all"].")" : '<a class="blue" href="?"><b>Все</b></a> ('.$conted_prj["all"].')' )?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=($_GET["open"] ? "<b>Открытые</b> (".$conted_prj["open"].")" : '<a class="blue" href="#" onclick="javascript: document.frm.openclose.value=1; document.frm.submit();" ><b>Открытые</b></a> ('.$conted_prj["open"].')' )?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=($_GET["closed"] ? "<b>Закрытые</b> (".$conted_prj["closed"].")" : '<a class="blue" href="#" onclick="javascript: document.frm.openclose.value=2; document.frm.submit();"><b>Закрытые</b></a> ('.$conted_prj["closed"].')' )?></td>
<td align="right" style="padding:15px 10px 0px 0px;"><a href="/public/"><IMG alt="Публиковать ваш проект" align="absmiddle" src="/images/post_button.gif" width="178" height="28" border="0"></a></td>
</tr>
</table>
<br>
<?

$i = 0;
if ($prjs) {
 //   print "<pre>";
  //  print_r($prjs);
   // print "</pre>";
   setlocale(LC_ALL, 'ru_RU.CP1251');
    $usr=new users();
    $name=$usr->GetName($_SESSION["uid"], $err);
    $dir = $name["login"];
    ?><div style="height:1px; background:#d7d7d7; width:920px; margin:5px 0;"></div><?
    
    $pj = 0;
    $pn = sizeof($prjs);

    foreach ($prjs as $ikey=>$prj){
        $spec = projects::getPrimarySpec($prj['id']);
        $prj['category'] = $spec['category_id'];
        if (is_new_prj($prj['post_date'])) {
            $blink = (!$is_emp && $prj['pro_only'] == 't' && !$is_pro && ($uid != $prj['user_id']) && !hasPermissions('projects'))?"/proonly.php":"/blogs/view.php?tr=".$prj['thread_id'];
        } else {
            $blink = (!$is_emp && $prj['pro_only'] == 't' && !$is_pro && ($uid != $prj['user_id']) && !hasPermissions('projects'))?"/proonly.php":"/projects/?pid=".$prj['id'].'&f=1';
        }
        $plink = "/users/".$prj['login']."/project/?prjid=".$prj['id'];
        if ($prj['payed'] && $prj["kind"]!=2) {
            ?>
            <table cellpadding="4" cellspacing="0" border="0"><tr valign="top"><td style="padding-left: 10px; padding-bottom: 5px;" width="510px">
            <div class="fl2_date">
			<div class="fl2_date_day">
			<?=str_ago_pub(strtotimeEx($prj['post_date']))?>
			</div>
			<div class="fl2_date_date">
			<?=strftime("%d ",strtotimeEx($prj['post_date'])).monthtostr(strftime("%m",strtotimeEx($prj['post_date']))).strftime(", %A",strtotimeEx($prj['post_date']))?>
			</div>
			<div class="clear"></div>
		</div>
            <div class="hr"></div><div class="fl2_offer bordered">
            <div class="fl2_offer_logo">
                <?if ($prj['logo_name']) {?>
                    <a href="http://<?= formatLink($prj['link'])?>" target="_blank" nofollow ><img border="0" src="<?=WDCPREFIX.'/'.$prj['logo_path'].$prj['logo_name']?>" /></a>
                <?} else {?>
                    <img border="0" src="/images/public_your_logo.gif" />
                <? }?> 
                <div>Платный проект</div></div><?if ($prj['cost']) {?><div class="fl2_offer_budget">Бюджет: <?=CurToChar($prj['cost'], $prj['currency'])?></div><?}?><div class="fl2_offer_header"> <?if ($prj['ico_closed']=='t') {?><a href="/about/prjrules/" title="Проект закрыт"><img src="/images/ico_closed.gif" alt="Проект закрыт" /></a><?}?>
            <? if ($prj["frl_id"]) { ?><a href="/norisk/?prj=<?=$prj['id']?>"><img src="/images/shield_sm.gif"></a><? } ?><a name="/proonly.php" href="<?=$blink?>" class="fl2_offer_header" title=""><?=reformat($prj['name'], 100, 0, 1)?></a></div><div class="fl2_offer_content"><?=ereg_replace("\r","",ereg_replace("\n","",reformat($prj['descr'])))?></div><?

            $attach=$projects->GetAllAttach;
            for ($i=0;$i<count($attach);$i++) {?><div class="flw_offer_attach"><a href="/users/<?=$dir?>/upload/<?=$attach[$i]['name']?>" target="_blank">Загрузить</a> (<?=$attach[$i]['ftype']?>; <?=ConvertBtoMB($attach[$i]['size'])?> )</div><?}?><br><div class="fl2_offer_meta">Прошло времени с момента публикации: <?=ago_pub_x(strtotimeEx($prj['post_date']))?><br />Автор: <a href="/users/<?=$name["login"]?>"><? print $name["uname"]." "; print $name["usurname"]; ?> [<?=$name["login"]?>]</a><br>: <? $category=$proj_groups_by_id[$prj['category']]; print $category; ?><?if ($prj['pro_only']=='t') {?><br /><font  class="fl2_offer_meta2" style="background-color:#fff7ee;">Отвечать на проект могут только пользователи с аккаунтом <a class="b-layout__link" href="/payed/"><span class="b-icon b-icon__pro b-icon__pro_f " title="Платный аккаунт" alt="Платный аккаунт"></span></a></font></div><?}?><div class="fl2_comments_link"><div style="padding:12px 0px 0px 0px;"></div></div>
            <?php if(!($prj["closed"]=="t"&&!$prj["frl_id"])) { ?>
            <table cellpadding="2" cellspacing="0" border="0">
            <tr valign="middle">
            <td><img src="/images/ico_setup.gif" border="0"></td>
            <td><a class="public_blue" href="/public/?step=1&public=<?=$prj["id"]?>&red=<?=rawurlencode("/users/".$name["login"]."/setup/projects/")?>">Редактировать</a></td>
            <td>&nbsp;&nbsp;&nbsp;</td>
            <td><img src="/images/<?=($prj["closed"]=='t' ? "ico_reopen.gif" : "ico_close_round.gif")?>" border="0"></td>
            <td><?if ($prj["is_accepted"] == 't') {?><a class="public_black" href="/norisk/?prj=<?=$prj["id"]?>">Безопасная Сделка</a> <? }elseif ($prj["closed"]=='t') {?><a class="public_black" href="#"   onclick="closeprj(<?=$prj["id"]?>);">Публиковать еще раз</a><?} else {?><a class="public_blue" href="#"   onclick="closeprj(<?=$prj["id"]?>);">Снять с публикации</a><?}?></td>
            </tr>
            </table>
            <?php } ?>
            </div>
            </td> 
            <td width="240px">
            <br><br>
            <table  cellpadding="2" cellspacing="0" border="0">
            <tr>
                <td>&nbsp;</td>
                <td><b>Статистика по объявлению:</b><br><?
                if ($prj["closed"]=="t") { ?><? } else {
            $payed=(($prj["payed_to"]>$prj["now"] && $prj["payed"]) ? 1 : 0 );
            $counte=$projects->CountProjectNew($prj['post_date'], $prj['kind'], $prj['top_from'], $prj['top_to'], $prj['strong_top']);
            $page=floor($counte/$GLOBALS["prjspp"])+1;
            $counte_page=$counte % $GLOBALS["prjspp"];
            ?>
            <a class="public_blue" href="/projects/?kind=<?=$prj['kind']?>&page=<?=$page?>#prj<?=$prj['id']?>"><?=$counte_page?>-е по счету (<?=$page?>-я страница)</a><br>закладка "<?=GetKind($prj["kind"])?>"
            <?}?>

<?
        if (is_new_prj($prj['post_date'])) {
?>
            <br><?=((!$prj["comm_count"] || $prj["comm_count"] % 10==0 || $prj["comm_count"] % 10 >4 || ($prj["comm_count"] >4 &&  $prj["comm_count"]<21)) ?  '<a class="public_blue" href="/blogs/view.php?tr='.$prj['thread_id'].'">'.$prj["comm_count"].' предложений</a>' : (($prj["comm_count"] % 10 == 1 || $prj["comm_count"]==1) ?  '<a class="public_blue" href="/blogs/view.php?tr='.$prj['thread_id'].'">'.$prj["comm_count"].' предложение</a>' : '<a class="public_blue" href="/blogs/view.php?tr='.$prj['thread_id'].'">'.$prj["comm_count"].' предложения</a>'  )   )?><br><br></td>
<?
        }
        else {
?>
            <br><?=((!$prj["offers_count"] || $prj["offers_count"] % 10==0 || $prj["offers_count"] % 10 >4 || ($prj["offers_count"] >4 &&  $prj["offers_count"]<21)) ?  '<a class="public_blue" href="/projects/?pid='.$prj['id'].'&f=1">'.$prj["offers_count"].' предложений</a>' : (($prj["offers_count"] % 10 == 1 || $prj["comm_count"]==1) ?  '<a class="public_blue" href="/projects/?pid='.$prj['id'].'&f=1">'.$prj["offers_count"].' предложение</a>' : '<a class="public_blue" href="/projects/?pid='.$prj['id'].'&f=1">'.$prj["offers_count"].' предложения</a>'  )   )?><br><br></td>
<?
        }
?>
            </tr>
            </table>
            </td>
            <td  class="public_plus_black" align="center" valign="middle"><?=(($prj["closed"]=="t"&&!$prj["frl_id"])? "Снято с публикации" : ($prj["exec_id"] > 0 ? "Исполнитель найден:<br>" . '<a class="blue" href="/users/' . $prj['exec_login'] . '">' . $prj['exec_name'] . ' ' . $prj['exec_surname'] . ' ' . '[' . $prj['exec_login'] . "]" : "Ищется исполнитель"))?><br>
			<? 	
				if ($prj["need_arbiter"] == 't')  print "<a href=\"/norisk/?prj=".$prj['id']."\" class=\"blue\">Проект заморожен арбитражем</a>";
				elseif (!$prj["frl_id"])  print "<a href=\"".(($prj["exec_id"] > 0)?"/".sbr::NEW_TEMPLATE_SBR."sbr/?prj=".$prj['id']."&login=".$prj['exec_login']:"/".sbr::NEW_TEMPLATE_SBR."/?prj=".$prj['id'])."\" class=\"blue\">Начать «Безопасную Сделку»</a>";
				elseif ($prj["is_t3_send"] != 't')  print "<a href=\"/norisk/?prj=".$prj['id']."\" class=\"blue\">Тех. задание не отправлено</a>";
				elseif ($prj["is_accepted"] != 't')  print "<a href=\"/norisk/?prj=".$prj['id']."\" class=\"blue\">Тех. задание не утверждено</a>";
				elseif ($prj["is_money_reserved"] != 't')  print "<a href=\"/norisk/?prj=".$prj['id']."\" class=\"blue\">Деньги не зарезервированы</a>";
				elseif ($prj["is_closed"] != 't')  print "<a href=\"/norisk/?prj=".$prj['id']."\" class=\"blue\">Проект в работе</a>";
				else  print "<a href=\"/norisk/?prj=".$prj['id']."\" class=\"blue\">Проект закончен</a>";
				?>
			</td>
            </tr></table>

	    <?
		if ($pn > $pj+1)
		{

	    ?>
			<div style="height:1px; background:#d7d7d7; width:920px; margin:5px 0;"></div><?
            	}

        }
        
        else {?>
        <table  cellpadding="4" cellspacing="0" border="0"><tr valign="top"><td style="padding-left: 10px;" width="510px">
        <div class="fl2_date">
			<div class="fl2_date_day">
			<?=str_ago_pub(strtotimeEx($prj['post_date']))?>
			</div>
			<div class="fl2_date_date">
			<?=strftime("%d ",strtotimeEx($prj['post_date'])).monthtostr(strftime("%m",strtotimeEx($prj['post_date']))).strftime(", %A",strtotimeEx($prj['post_date']))?>
			</div>
			<div class="clear"></div>
		</div>
            <div class="fl2_offer"><?if ($prj['cost']) {?><div class="fl2_offer_budget">Бюджет: <?=CurToChar($prj['cost'], $prj['currency'])?></div><?}?><div class="fl2_offer_header"> <?if ($prj['ico_closed']=='t') {?><a href="/about/prjrules/" title="Проект закрыт"><img src="/images/ico_closed.gif" alt="Проект закрыт" /></a><?}?>
            <? if ($prj["frl_id"]) { ?><a href="/norisk/?prj=<?=$prj['id']?>"><img src="/images/shield_sm.gif"></a><? } ?><a href="<?=$blink?>"><?=reformat($prj['name'], 100, 0, 1)?></a></div><div class="fl2_offer_content"><?=ereg_replace("\r","",ereg_replace("\n","",reformat($prj['descr'])))?></div><?

            $attach=$projects->GetAllAttach;
            for ($i=0;$i<count($attach);$i++) {?><div class="flw_offer_attach"><a href="/users/<?=$dir?>/upload/<?=$attach[$i]['name']?>" target="_blank">Загрузить</a> (<?=$attach[$i]['ftype']?>; <?=ConvertBtoMB($attach[$i]['size'])?> )</div><?}?><br><div class="fl2_offer_meta">Прошло времени с момента публикации: <?=ago_pub_x(strtotimeEx($prj['post_date']))?><br />Автор: <a href="/users/<?=$name["login"]?>"><? print $name["uname"]." "; print $name["usurname"]; ?> [<?=$name["login"]?>]</a><br />: <? $category=$proj_groups_by_id[$prj['category']]; print $category; ?></div><?if ($prj['pro_only']=='t') {?><br /><font  class="fl2_offer_meta2" style="background-color:#fff7ee;">Отвечать на проект могут только пользователи с аккаунтом <a class="b-layout__link" href="/payed/"><span class="b-icon b-icon__pro b-icon__pro_f " title="Платный аккаунт" alt="Платный аккаунт"></span></a></font></div><?}?><div class="fl2_comments_link"><div style="padding:12px 0px 0px 0px;"></div></div>
            <?php if(!($prj["closed"]=="t"&&!$prj["frl_id"])) { ?>
            <table cellpadding="2" cellspacing="0" border="0">
            <tr valign="middle">
            <td><img src="/images/ico_setup.gif" border="0"></td>
            <td><a class="public_blue" href="/public/?step=1&public=<?=$prj["id"]?>&red=<?=rawurlencode("/users/".$name["login"]."/setup/projects/")?>">Редактировать</a></td>
            <td>&nbsp;&nbsp;&nbsp;</td>
            <td><img src="/images/<?=(($prj["closed"]=='t' || $prj["is_accepted"] =='t') ? "ico_reopen.gif" : "ico_close_round.gif")?>" border="0"></td>
            <td><?if ($prj["is_accepted"] == 't') {?><a class="public_black" href="/norisk/?prj=<?=$prj["id"]?>">Безопасная Сделка</a> <? }elseif ($prj["closed"]=='t') {?><a class="public_black" href="#"   onclick="closeprj(<?=$prj["id"]?>);">Публиковать еще раз</a><?} else {?><a class="public_blue" href="#"   onclick="closeprj(<?=$prj["id"]?>);">Снять с публикации</a><?}?></td>
            </tr>
            </table>
            <?php } ?>
            </div>
         </td> 
            <td width="240px">
             <br><br>
            <table  cellpadding="2" cellspacing="0" border="0">
            <tr>
                <td>&nbsp;</td>
                <td><div class="b-layout__txt b-layout__txt_fontsize_11"><b>Статистика по объявлению:</b><br><?
                if ($prj["closed"]=="t") { ?><? } else {
            $payed=(($prj["payed_to"]>$prj["now"] && $prj["payed"]) ? 1 : 0 );
            $counte=$projects->CountProjectNew($prj['post_date'], $prj['kind'], $prj['top_from'], $prj['top_to'], $prj['strong_top']);
            $page=floor($counte/$GLOBALS["prjspp"])+1;
            $counte_page=$counte % $GLOBALS["prjspp"];
            ?>
            <a class="public_blue" href="/?kind=<?=$prj['kind']?>&page=<?=$page?>#prj<?=$prj['id']?>"><?=$counte_page?>-е по счету (<?=$page?>-я страница)</a><br>закладка "<?=GetKind($prj["kind"])?>"
            <?}?>
<?
        if (is_new_prj($prj['post_date'])) {
?>
            <br><?=((!$prj["comm_count"] || $prj["comm_count"] % 10==0 || $prj["comm_count"] % 10 >4 || ($prj["comm_count"] >4 &&  $prj["comm_count"]<21)) ?  '<a class="public_blue" href="/blogs/view.php?tr='.$prj['thread_id'].'">'.$prj["comm_count"].' предложений</a>' : (($prj["comm_count"] % 10 == 1 || $prj["comm_count"]==1) ?  '<a class="public_blue" href="/blogs/view.php?tr='.$prj['thread_id'].'">'.$prj["comm_count"].' предложение</a>' : '<a class="public_blue" href="/blogs/view.php?tr='.$prj['thread_id'].'">'.$prj["comm_count"].' предложения</a>'  )   )?><br><br>
<?
        }
        else {
?>
            <br><?=((!$prj["offers_count"] || $prj["offers_count"] % 10==0 || $prj["offers_count"] % 10 >4 || ($prj["offers_count"] >4 &&  $prj["offers_count"]<21)) ?  '<a class="public_blue" href="/projects/?pid='.$prj['id'].'&f=1">'.$prj["offers_count"].' предложений</a>' : (($prj["offers_count"] % 10 == 1 || $prj["comm_count"]==1) ?  '<a class="public_blue" href="/projects/?pid='.$prj['id'].'&f=1">'.$prj["offers_count"].' предложение</a>' : '<a class="public_blue" href="/projects/?pid='.$prj['id'].'&f=1">'.$prj["offers_count"].' предложения</a>'  )   )?><br><br>
<?
        }
?>
            </div></td></tr>
           

            <tr valign="top">
            <td style="padding-top: 8px;"></td>
            <td><div class="public_plus"><a href="/public/?step=2&public=<?=$prj["id"]?>&red=<?=rawurlencode("/users/".$name["login"]."/setup/projects/")?>" class="b-button b-button_flat b-button_flat_green b-button_height_30 b-button_block">Получить еще предложений</a></div>
            <? /* if (($account->bonus_sum<50) && ($account->sum<50)) {?><table border="0" cellpadding="0" cellspacing="0"><tr><td class="public_grey_alert">ВНИМАНИЕ! У Вас на счету не хватает<b> <?=50-$account->sum?> FM</b>. После нажатия кнопки Вам будет предложено пополнить счет на указанную сумму.</td></tr></table ><?}*/?>
            </td>
            </tr>

            <?if (!$prj["payed"] && !is_new_prj($prj['post_date'])) {?>
            <tr valign="top">
            <td style="padding-top: 8px;"><img src="/images/pointer.gif" border="0"></td>
            <td></td>
            </tr>
            <?}?>
            </table>
            <br>
            </td>
            <td class="public_plus_black" align="center" valign="middle"><div class="b-layout__txt"><?=(($prj["closed"]=="t"&&!$prj["frl_id"]) ? "Снято с публикации" : ($prj["exec_id"] > 0 ? "Исполнитель найден:<br>" . '<a class="blue" href="/users/' . $prj['exec_login'] . '">' . $prj['exec_name'] . ' ' . $prj['exec_surname'] . ' ' . '[' . $prj['exec_login'] . "]" : "Ищется исполнитель"))?><br>
			<? 	
				if ($prj["need_arbiter"] == 't')  print "<a href=\"/norisk/?prj=".$prj['id']."\" class=\"blue\">Проект заморожен арбитражем</a>";
				elseif (!$prj["frl_id"])  print "<a href=\"".(($prj["exec_id"] > 0)?"/".sbr::NEW_TEMPLATE_SBR."/step2/?prj=".$prj['id']."&login=".$prj['exec_login']:"/".sbr::NEW_TEMPLATE_SBR."/?prj=".$prj['id'])."\" class=\"blue\">Начать «Безопасную Сделку»</a>";
				elseif ($prj["is_t3_send"] != 't')  print "<a href=\"/norisk/?prj=".$prj['id']."\" class=\"blue\">Тех. задание не отправлено</a>";
				elseif ($prj["is_accepted"] != 't')  print "<a href=\"/norisk/?prj=".$prj['id']."\" class=\"blue\">Тех. задание не утверждено</a>";
				elseif ($prj["is_money_reserved"] != 't')  print "<a href=\"/norisk/?prj=".$prj['id']."\" class=\"blue\">Деньги не зарезервированы</a>";
				elseif ($prj["is_closed"] != 't')  print "<a href=\"/norisk/?prj=".$prj['id']."\" class=\"blue\">Проект в работе</a>";
				else  print "<a href=\"/norisk/?prj=".$prj['id']."\" class=\"blue\">Проект закончен</a>";
				?>
			</div></td>
            </tr>  </table>

	    <?
		if ($pn > $pj+1)
		{

	    ?><div style="height:1px; background:#d7d7d7; width:920px; margin:5px 0;"></div><?	
            	}

              }

            $pj ++;

    }

}
    ?>
    <form action="." id="frm"  name="frm" method="POST" ><input type="hidden" name="action" value=""><input type="hidden" name="openclose" value="<?=$openclose?>"><input type="hidden" name="prjid" value=""><input type="hidden" name="transaction_id" value="<?=$transaction_id?>"></form>
