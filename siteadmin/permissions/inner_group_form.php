<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }
if(!(hasPermissions('adm') && hasPermissions('permissions'))) {
  header ("Location: /404.php");
  exit;
}
?>

<strong>Права доступа. <?=(($action=='group_add')?'Добавление':'Редактирование')?> группы</strong>

<br><br>

<form method="POST" action="index.php" onSubmit="return checkGroupForm();">
    <input type="hidden" name="action" value="<?=(($action=='group_add')?'group_insert':'group_update')?>">
    <input type="hidden" name="id" value="<?=$id?>">
    Название: <input type="text" id="group_name" name="name" value="<?=$group['name']?>">
    <br /><br />
    Права доступа:
    <br /><br />
    <table>
    <? foreach($rights as $right) { ?>
        <tr>
        <td valign="top" style="padding-bottom:5px;">
            <input type="checkbox" name="rights[]" value="<?=$right['id']?>" <?=( is_array($group['rights']) && in_array($right['id'],$group['rights'])?'checked':'')?>> <?=$right['name']?>
        </td>
        </tr>
    <? } ?>
    </table>
    <br>
    <input type="submit" value=" <?=(($action=='group_add')?'Добавить':'Сохранить')?> ">
</form>

<script type="text/javascript">
    function checkGroupForm() {
        if(document.getElementById('group_name').value=='') {
            alert('Название группы не может быть пустым');
            return false;
        } else {
            return true;
        }
    }
</script>
