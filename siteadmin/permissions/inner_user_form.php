<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }
if(!(hasPermissions('adm') && hasPermissions('permissions'))) {
  header ("Location: /404.php");
  exit;
}

$js = "";

?>

<style>
.red { color: #f00; }
.green { color: #0f0; }
</style>

<script type="text/javascript">
var group_orig = 0;
var rights_orig = new Array();
var rights_group = new Array();
</script>

<strong>Права доступа. <?=(($action=='user_add')?'Добавление':'Редактирование')?> пользователя</strong>

<br><br>

<form method="POST" action="index.php">
    <input type="hidden" name="action" value="<?=(($action=='user_add')?'user_insert':'user_update')?>">
    <input type="hidden" name="uid" value="<?=$user->uid?>">
    Пользователь: 
    <? if($action=='user_add') { ?>
        <input type="text" id="group_name" name="name" value="">
    <? } else { ?>
        <? $utype = (is_emp($user->role)?'emp':'frl'); ?>
        <?=$session->view_online_status($user->login)?> <a href="/users/<?=$user->login?>" class="<?=$utype?>name11"><?=($user->usurname." ".$user->uname)?></a> [<a href="/users/<?=$user->login?>" class="<?=$utype?>name11"><?=$user->login?></a>] <a href="mailto:<?=$user->email?>"><?=$user->email?></a>
        <br><br>
        <a href="" onClick="rights_reset(); return false;">Сбросить в исходное состояние</a>
    <? } ?>
    <br><br>
    Группы: 
    <br>
    <select name="groups" id="rights_group">
        <? foreach($groups as $group) { ?>
            <option value="<?=$group['id']?>" <?=(in_array($group['id'],$user_groups)?'selected':'')?>><?=$group['name']?></option>
            <?
            if(in_array($group['id'],$user_groups)) {
                $js .= "group_orig = {$group['id']};\n";
            }
            $js .= "rights_group[{$group['id']}] = new Array();\n";
            if($group['rights']) {
                $n = 0;
                foreach($group['rights'] as $v) {
                    $js .= "rights_group[{$group['id']}][{$v}] = $v;\n";
                    $n++;
                }
            }
            ?>
        <? } ?>
    </select>
    <br><br>

    <table>
        <? foreach($rights as $right) { ?>
            <?
            $checked_allow = '';
            $red = '';
            if(in_array($right['id'], $user_rights_allow) || in_array($right['id'], $user_groups_rights)) {
                $checked_allow = 'checked';
            }
            if(in_array($right['id'], $user_rights_disallow)) {
                $checked_allow = '';
                $red = "red";
            }
            if(in_array($right['id'], $user_rights_allow)) {
                $red = "green";
            }
            if($checked_allow) {
                $js .= "rights_orig[{$right['id']}] = {$right['id']};\n";
            }
            ?>
            <tr>
                <td valign="top">
                    <input type="checkbox" <?=$checked_allow?> id="rights_allow_<?=$right['id']?>" name="rights_allow[]" value="<?=$right['id']?>"> <span id="rights_allow_txt_<?=$right['id']?>" class="<?=$red?>"><?=$right['name']?></span>
                </d>
            </tr>
        <? } ?>
    </table>

    <br><br>
    <input type="submit" value=" <?=(($action=='user_add')?'Добавить':'Сохранить')?> ">
</form>

<script type="text/javascript">
    <?=$js?>
    window.addEvent('domready', function() {
        $('rights_group').addEvent('change', function(){
            $$('input[name^=rights_allow]').each(function(el){
                el.set('disabled', false);
                el.set('checked', false);
                if(rights_group[$('rights_group').get('value')][el.get('value')] || $('rights_group').get('value')==0) {
                    el.set('checked', true);
                }
                $('rights_allow_txt_'+el.get('value')).removeClass('red');
                $('rights_allow_txt_'+el.get('value')).removeClass('green');
                if($('rights_group').get('value')==0) {
                    el.set('disabled', true);
                }
            });
        });

        $$('input[name^=rights_allow]').addEvent('click', function(){
            if(this.get('checked')) {
                $('rights_allow_txt_'+this.get('value')).removeClass('red');
                if(rights_group[$('rights_group').get('value')][this.get('value')]== undefined) {
                    $('rights_allow_txt_'+this.get('value')).addClass('green');
                }
            } else {
                $('rights_allow_txt_'+this.get('value')).removeClass('green');
                if(rights_group[$('rights_group').get('value')][this.get('value')]) {
                    $('rights_allow_txt_'+this.get('value')).addClass('red');
                }
            }
        });

        if(group_orig==0) {
            $$('input[name^=rights_allow]').each(function(el){
                $('rights_allow_txt_'+el.get('value')).removeClass('red');
                $('rights_allow_txt_'+el.get('value')).removeClass('green');
                el.set('disabled', true);
                el.set('checked', true);
            });
        }
    });

    function rights_reset() {
        $('rights_group').set('value', group_orig);
        $$('input[name^=rights_allow]').each(function(el){
            $('rights_allow_txt_'+el.get('value')).removeClass('red');
            $('rights_allow_txt_'+el.get('value')).removeClass('green');
            if(group_orig==0) {
                el.set('disabled', true);
                el.set('checked', true);
            } else {
                el.set('disabled', false);
                el.set('checked', false);
                if(rights_orig[el.get('value')]) {
                    el.set('checked', true);
                    if(rights_group[$('rights_group').get('value')][el.get('value')]== undefined) {
                        $('rights_allow_txt_'+el.get('value')).addClass('green');
                    }
                } else {
                    if(rights_group[$('rights_group').get('value')][el.get('value')]) {
                        $('rights_allow_txt_'+el.get('value')).addClass('red');
                    }
                }
            }
        });
    }

</script>


