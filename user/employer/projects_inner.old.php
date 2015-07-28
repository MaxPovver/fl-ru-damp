<?
if(!defined('IN_STDF')) { 
    header("HTTP/1.0 404 Not Found");
    exit();
}
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
$projects = new projects();
$uid = $user->GetUid($err);
$prjs = $projects->GetCurPrjs($uid);

 if ($_SESSION['login'] == $user->login) { ?>
 <br>

<div align="right" style="padding-right: 19px;"><a href="/users/<?=$_SESSION['login']?>/setup/projects/"><img src="/images/ico_setup.gif" alt="" width="6" height="9" border="0"></a>&nbsp;<a href="/users/<?=$_SESSION['login']?>/setup/projects/">Изменить</a></div>
<br>

<img src="/images/dot_grey.gif" alt="" width="100%" height="1" border="0"><? } ?><table width="100%" cellspacing="0" cellpadding="19" border="0">
<? 

if ($prjs) foreach ($prjs as $ikey=>$prj){ 
    if ($prj["pro_only"]=='t' && $_SESSION['login'] != $user->login && !$_SESSION['pro_last'] && !hasPermissions('projects')) {
        ?><!--<tr class="qpr"><td class="qp" align="center" colspan="3"><h2>Только для про</h2></td></tr>--><?
    }
    else {
    ?>
	<tr class="qpr">
	<td class="qp"><strong><?=date("d.m",strtotimeEx($prj['post_date']))?> &mdash; <?=reformat($prj['name'],50)?></strong><br>
	<?=reformat($prj['descr'], 50)?>
  <? if (is_new_prj($prj['post_date'])) { ?>
	<div align="right" style="margin-top: 5px;"><b>[<a href="/blogs/view.php?tr=<?=$prj['thread_id']?>" class="blue">
	  <?=($prj['comm_count']?"<b>Комментарии (".zin($prj['comm_count']).")</b>":"Комментарии (".zin($prj['comm_count']).")")?></a>]</b>
	</div><?
  } else { ?>
	<div align="right" style="margin-top: 5px;"><b>[<a href="/projects/?pid=<?=$prj['id']?>" class="blue">
	  <?=($prj['offers_count']?"<b>Комментарии (".zin($prj['offers_count']).")</b>":"Комментарии (".zin($prj['offers_count']).")")?></a>]</b>
	</div><?
  } ?>
	</td>
	<td width="67" align="center" class="qp"><?if ($prj['cost']>0) {?><strong><?=CurToChar($prj['cost'], $prj['currency'])?></strong><?} else{?>&nbsp;<?}?></td>
	<td width="30"><? if ($prj['closed'] == "t") { ?>Проект закрыт<? } else {?><a id="prj<?=$prj['id']?>" name="prj<?=$prj['id']?>" href="/projects/?pid=<?=$prj['id']?>" class="org">Подробнее</a><? } ?>
	</td>
	</tr>
<? } } else {?>
<tr><td align="center"><h2><?= ($user->tab_name_id == "1"?"Нет услуг":"Нет работ")?></h2></td></tr>
<? } ?>
</table>
<form action="/users/<?=$user->login?>/" id="frm"  name="frm" method="POST" ><input type="hidden" name="action" value=""><input type="hidden" name="prjid" value=""></form>
