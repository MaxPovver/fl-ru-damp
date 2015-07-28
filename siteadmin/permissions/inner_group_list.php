<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }
if(!(hasPermissions('adm') && hasPermissions('permissions'))) {
  header ("Location: /404.php");
  exit;
}
?>

<strong>Права доступа. Список группы</strong>

<br><br>

<a href="?action=group_add">Добавить новую группу</a>

<br><br>

<? if($groups) { ?>
    <table width="100%" cellpadding="5" cellspacing="5"  class="tbl-pad5">
        <tr style="background-color: #eeeeee;">
            <td>Название</td>
            <td>Действие</td>
        </tr>
        <? foreach($groups as $group) { ?>
        <tr>
            <td><?=$group['name']?></td>
            <td>
                <? if($group['id']!=0) { ?>
                    [<a href="?action=group_edit&id=<?=$group['id']?>">редактировать</a>]<? if($group['id']!=1) { ?>&nbsp;&nbsp;&nbsp;[<a id="del_group_<?=$group['id']?>" href="?action=group_delete&id=<?=$group['id']?>" onClick="return addTokenToLink('del_group_<?=$group['id']?>', 'Вы действительно хотите удалить группу?')">удалить</a>]<? } ?>
                <? } ?>
            </td>
        </tr>
        <? } ?>
    </table>
<? } ?>
