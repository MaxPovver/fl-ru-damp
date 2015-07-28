<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }

$action = $_POST['action'];
$users  = new users();


if($action) {
    switch($action) {
        case "addteam":
            $users->GetUser($_POST['login']);
            
            if($users->uid && $users->is_team == 'f') {
                $users->is_team = 't';
                $user_update = new users();
                $user_update->is_team = 't';
                $error = $user_update->Update($users->uid, $error);
                
                if($error) $error_login = $error;    
            } else {
                if($users->is_team == 't') {
                    $error_login = "Пользователь с логином ".$users->login." уже находится в команде Free-lance.ru";
                } else {
                    $error_login = "Пользователя с логином ".$_POST['login']." не существует";
                }
            }
            break;
        case "delteam":
            $users->GetUser($_POST['login']);
            
            if($users->uid && $users->is_team == 't') {
                $users->is_team = 'f';
                $user_update = new users();
                $user_update->is_team = 'f';
                $error = $user_update->Update($users->uid, $error);
                
                if($error) $error_login = $error;    
            } else {
                if($users->is_team == 'f') {
                    $error_login = "Пользователя с логином ".$_POST['login']." нет в команде Free-lance.ru";
                } else {
                    $error_login = "Пользователя с логином ".$_POST['login']." не существует";
                }
            }
            
            break;    
    }
}

$users_team = $users->GetUsers("is_team = 't'", "login ASC");
?>
<style>
    table.team {
        background: black;
        cellpadding:1px;
    }
    .team thead tr {
        background: gray;
    }
    .team thead tr td {
        font-weight:bold;
        color:white;
        text-align:center;
    }
    .team tbody tr.env {
        background: white
    }
    .team tbody tr.odd {
        background: whitesmoke;
    }
</style>
<strong>Команда <a href="/about/team/" target="_blank">Free-lance.ru</a></strong><br/><br/>

<form method="POST" action=".">
<input type="hidden" name="action" value="addteam">
Добавить в команду пользователя<br/>
Логин: <input type="text" name="login"> <input type="submit" value="Добавить">
<?php if($error_login) {?><?= view_error(htmlspecialchars($error_login));?><?php } //if?>
</form>

<form method="POST" action="." id="delform">
<input type="hidden" name="action" value="delteam">
<input type="hidden" name="login" value="" id="login_team">
</form>
<br/> 
<table class="team brd-tbl" width="100%" cellpadding="3" cellspacing="1" >
    <colgroup>
    <col width="5%">
    <col width="85%">
    <col width="10%">
    </colgroup>
    <thead>
        <tr>
            <td>№</td>
            <td>Пользователи находящиеся в команде Free-lance.ru</td>
            <td>Настройка</td>
        </tr>
    </thead>
    <tbody>
        <?php if($users_team) {?>
        <?php foreach($users_team as $i=>$uteam) { ?>
        <tr class='<?= ($i%2!=0?"odd":"env")?>'>
            <td align="center" style="text-align:center"><?=($i+1)?></td>
            <td>
                <table> 
                    <tr> 
                        <td><?=view_avatar($uteam['login'], $uteam['photo'])?><td/>
                        <td><?=view_user($uteam);?></td>
                    </tr>
                </table>
            </td>
            <td align="center" style="text-align:center">[<a href="javascript:void(0)" onclick="if(confirm('Удалить пользователя из команды?')) {$('login_team').set('value', '<?=$uteam['login']?>'); $('delform').submit(); } else { return false; }" class="public_red">удалить</a>]</td>
        </tr>
        <?php } //foreach?>
        <?php } else { //if?>
        <tr class="env">
            <td colspan="3" align="center" style="text-align:center"><strong>Пользователей в команде нет</strong></td>
        </tr>
        <?php } // else?>
    </tbody>
</table>    