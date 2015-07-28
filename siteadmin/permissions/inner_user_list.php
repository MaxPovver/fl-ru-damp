<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }
if(!(hasPermissions('adm') && hasPermissions('permissions'))) {
  header ("Location: /404.php");
  exit;
}
?>

<strong>Права доступа. Список пользователей</strong>

<br><br>

<form action="index.php">
<input type="hidden" name="action" value="user_list">
Группа: 
<select name="group_id">
    <option value="-3" <?=($group_id==-3?'selected':'')?>>* Все пользователи</option>
    <option value="-4" <?=($group_id==-4?'selected':'')?>>* Пользователи без прав</option>
    <option value="-2" <?=($group_id==-2?'selected':'')?>>* Без группы</option>
    <option value="-1" <?=($group_id==-1?'selected':'')?>>* Все группы</option>
    <? foreach($groups as $group) { ?>
        <option value="<?=$group['id']?>" <?=(($group['id']==$group_id)?'selected':'')?>><?=$group['name']?></option>
    <? } ?>
</select>
&nbsp;&nbsp;&nbsp;&nbsp;
Логин:
<input type="text" name="login" value="<?=change_q_new($login)?>"> 
<input type="submit" value=" Показать ">
</form>

<br><br>

<table width="100%" border="0" cellspacing="5" cellpadding="5">
<? if($users) { ?>
    <? foreach($users as $user) { ?>
        <?
        $user_groups = permissions::getUserGroups($user['uid']);
        $user_rights = permissions::getUserExtraRights($user['uid']);
        $utype = (is_emp($user['role']) ? 'emp' : 'frl');
        ?>
        <tr>
            <td>
                <table width="100%" cellspacing="0" cellpadding="0" border="0">
            		<tr valign="top" class="n_qpr">
            			<td width="70" align="center"><a name="user_<?=$user['uid']?>"></a><a href="/users/<?=$user['login']?>" class="<?=$utype?>name11"><?=view_avatar($user['login'], $user['photo']) ?></a></td>
                        <td>
                            <?=$session->view_online_status($user['login'])?> <a href="/users/<?=$user['login']?>" class="<?=$utype?>name11"><?=($user['usurname']." ".$user['uname'])?></a> [<a href="/users/<?=$user['login']?>" class="<?=$utype?>name11"><?=$user['login']?></a>] <a href="mailto:<?=$user['email']?>"><?=$user['email']?></a>
                            <br/><br/>
                            <b>Группы:</b> 
                            <? if($user_groups) { ?>
                                <?
                                $groups_str = "";
                                foreach($user_groups as $user_group) {
                                    $groups_str .= $user_group['name'].", ";
                                }
                                $groups_str = preg_replace("/, $/", "", $groups_str);
                                ?>
                                <?=$groups_str?>
                            <? } else { ?>
                                -
                            <? } ?>
                            <br/><br/>
                            <b>Дополнительные права:</b> 
                            <? if($user_rights) { ?>
                                <?
                                $rights_str = "";
                                foreach($user_rights as $user_right) {
                                    if($user_right['is_allow']=='t') {
                                        $color = '#00ff00';
                                    } else {
                                        $color = '#ff0000';
                                    }
                                    $rights_str .= "<span style='color: {$color};'>{$user_right['name']}</span>, ";
                                }
                                $rights_str = preg_replace("/, $/", "", $rights_str);
                                ?>
                                <?=$rights_str?>
                            <? } else { ?>
                                -
                            <? } ?>
                            <br/><br/>
                            <? if($user_rights || $user_groups) { ?>[<a id="uid_del_<?=$user['uid']?>" href="?action=user_delete&uid=<?=$user['uid']?>" onClick="return addTokenToLink('uid_del_<?=$user['uid']?>', 'Вы действительно хотите удалить все права пользователя?')">удалить все права</a>] <? } ?>[<a href="?action=user_edit&uid=<?=$user['uid']?>">редактировать</a>]
                            <br/><hr>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    <? } ?>
<? } else { ?>
    <tr><td>Пользователей не найдено</td></tr>
<? } ?>
</table>
