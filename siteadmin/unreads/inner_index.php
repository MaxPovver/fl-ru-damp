<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; }
if (!hasPermissions('adm') || !hasPermissions('unreadsmsg')){
    header ("Location: /404.php"); 
	exit;
}
	
if($_POST['action']) {
    $action = $_POST['action'];
    
    switch($action) {
        // читкаем сообщения которые не прочитаны
        case "read":
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_offers_dialogue.php");
            $cls  = new projects_offers_dialogue();
            if($_POST['is_frl'] == 1) {
                $cls->getUnread2Read($_POST['msg']);        
            } else {
                $cls->getUnread2Read($_POST['msg'], false);
            }
            break;
    }
}

if(trim($_GET['login']) != "") {
    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/users.php");
    
    $login = pg_escape_string(trim($_GET['login']));
    $mod   = intval($_GET['mod']);
    $user = new users();
    $user->GetUser($login);
    
    switch($mod) {
        case 1:
            require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_offers_dialogue.php");
            $cls  = new projects_offers_dialogue();
            if(!is_emp($user->role) ) {
                $info = $cls->FindAllUnreadMessageFrl($user->uid); // Для Работодателей будет другая функция
            } else {
                $info = $cls->FindAllUnreadMessageEmp($user->uid); 
            }
            break;     
    }
}



?>
<style>
</style>

<script>
function toggleCheckBox() {
    if($('secret').value == 0) {
        $('secret').value = 1; 
        $$('input.msg').setProperty('checked', 1); 
    } else { 
        $('secret').value = 0; 
        $$('input.msg').setProperty('checked', 0); 
    }   
}

function emptyToggle() {
    $('secret').value = 0;     
}
</script>
<h1>Отчеты о непрочитанных сообщениях</h1>
<div>
    <input type="hidden" name="secret" id="secret" value="0">
    <form method="GET">
        Логин: <input type="text" name="login" value="<?=$login?>">
        <select name="mod">
            <option value="1">Сообщения в диалогах/предложениях к проекту</option>
        </select>
        <input type="submit" value="Искать">
    </form>
    <? if($login): ?>
    <h3>Результаты поиска:</h3>
    
    <div style="border:1px silver solid;">
        <p style="float:left;padding:5px;"><?=view_avatar($user->login, $user->photo);?></p>
        <p style="float:left"><?=(view_user2($user));?></p>
        <div style="clear:both">&nbsp;</div>
    </div>
    <br/>
        <? if($mod==1 && $info): ?>
        <form method="POST">
        <input type="hidden" value="1" name="mod">
        <input type="hidden" value="<?=!is_emp($user->role)?>" name="is_frl">
        <div style="text-align:center">
            <? if(is_emp($user->role)): ?>
            <table cellpadding="3" cellspacing="1" style="background:#c0c0c0;width:100%">
                <thead>
                    <tr style="background:#fff">
                        <td><img src="/images/check.gif" onClick="toggleCheckBox();" style="cursor:pointer"/></td>
                        <td>Фрилансер</td>
                        <td>Проект</td>
                        <td>Фрилансер забанен</td>
                        <td>Проект заблокирован</td>
                        <td>Дата сообщения</td>
                    </tr>
                </thead>
                <tbody>
                    <? foreach($info as $k=>$val): ?>
                    <tr style="background:#fff">
                        <td><input type="checkbox" name="msg[<?=$val['id']?>]" class="msg" value="<?=$val['id']?>" onClick="emptyToggle();"></td>
                        <td><a href="/users/<?=$val['login']?>/" target="_blank"><?=$val['login']?></a></td>
                        <td><a href="<?=getFriendlyURL("project", $val['project_id'])?>" target="_blank"><?=$val['project_name']?></a></td>
                        <td><? if($val['is_frl_ban']): ?><strong style="color:red"><?=date('d.m.Y H:i:s', strtotime($val['from']))?></strong><? else: ?> - <? endif; ?></td>
                        <td><? if($val['is_blocked']): ?><strong style="color:red"><?=date('d.m.Y H:i:s', strtotime($val['blocked_time']))?><? else: ?> - <? endif; ?></td>
                        <td><?=date('d.m.Y H:i:s', strtotime($val['post_date']));?></td>
                    </tr>
                    <? endforeach; ?>
                    
                </tbody>
            </table>
            <? else: ?>
            <table cellpadding="3" cellspacing="1" style="background:#c0c0c0;width:100%">
                <thead>
                    <tr style="background:#fff">
                        <td><img src="/images/check.gif" onClick="toggleCheckBox();" style="cursor:pointer"/></td>
                        <td>Работодатель</td>
                        <td>Проект</td>
                        <?/* <td>Ссылка на проект</td> */?>
                        <td>Работодатель забанен</td>
                        <td>Проект заблокирован</td>
                        <td>Проект для ПРО</td>
                        <td>Дата сообщения</td>
                    </tr>
                </thead>
                <tbody>
                    <? foreach($info as $k=>$val): ?>
                    <tr style="background:#fff">
                        <td><input type="checkbox" name="msg[<?=$val['id']?>]" class="msg" value="<?=$val['id']?>" onClick="emptyToggle();"></td>
                        <td><a href="/users/<?=$val['login']?>/" target="_blank"><?=$val['login']?></a></td>
                        <td><a href="<?=getFriendlyURL("project", $val['project_id'])?>" target="_blank"><?=$val['project_name']?></a></td>
                        <?/* <td><?=$GLOBALS['host']?>/projects/?pid=<?=$val['project_id']?></td> */?>
                        <td><? if($val['is_emp_ban']): ?><strong style="color:red"><?=date('d.m.Y H:i:s', strtotime($val['from']))?></strong><? else: ?> - <? endif; ?></td>
                        <td><? if($val['is_blocked']): ?><strong style="color:red"><?=date('d.m.Y H:i:s', strtotime($val['blocked_time']))?><? else: ?> - <? endif; ?></td>
                        <td>
                        <?if($val['pro_only'] == 't'):?>
                            <?=(!is_pro($user->is_pro)?'<strong style="color:red">Да</strong>':'Да')?>
                        <?else:?>
                            Нет
                        <?endif;?>
                        <?($val['pro_only']=='t'?"Да":"Нет")?></td>
                        <td><?=date('d.m.Y H:i:s', strtotime($val['post_date']));?></td>
                    </tr>
                    <? endforeach; ?>
                    
                </tbody>
            </table>
            <? endif; ?>
            
        </div>
        
        Действие с сообщениями: <select name="action">
        <option value="read">Прочитать</option>
        </select> 
        <input type="submit" name="sbm" value="Ок" onCLick="return confirm('Вы уверены?');">
        </form>
        <? else: ?>
        <strong>Данных нет</strong>
        <? endif; ?>
    <? endif; ?>
</div>
