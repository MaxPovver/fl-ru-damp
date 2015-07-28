<?php
$pfx = $result['from_id'] == $element->getProperty('engine')->uid ? 't_' : 'f_';
$mark_array = array((string) $result['msg_text'],
                    (string) $result[$pfx.'login'],
                    (string) $result[$pfx.'uname'],
                    (string) $result[$pfx.'usurname']);
                    
list ($msg_text, $login, $uname, $usurname) = $element->mark($mark_array);
$msg_text = strip_tags(preg_replace('~(https?:/){[^}]+}/~', '$1/', $msg_text), "<em><br>");
?>
<div class="search-lenta-item c">
    <span class="number-item"><?= $i?>.</span>
    <span class="search-pic"><a href="/users/<?= $result[$pfx.'login']?>/?f=<?=stat_collector::REFID_SEARCH?>&stamp=<?=$_SESSION['stamp']?>"><?= view_avatar($result[$pfx.'login'], $result[$pfx.'photo'])?></a></span>
    <div class="search-item-body">
        <h4><? $cls = is_emp($result[$pfx.'role']) ? 'empname11' : 'frlname11'; ?>
            
            <?=$session->view_online_status($result[$pfx.'login'])?><span style="line-height:1; font-size:11px; vertical-align:top;"><span class="<?= $cls?>"><a href="/users/<?= $result[$pfx.'login']?>/?f=<?=stat_collector::REFID_SEARCH?>&stamp=<?=$_SESSION['stamp']?>"><?= "{$uname} {$usurname} [{$login}]"?></a> <?= view_mark_user(array('login'=>$result[$pfx.'login'],
                                     'is_team' => $result[$pfx.'is_team'],
                                     'is_profi' => @$result[$pfx.'is_profi'],
                        	         'role'    => $result[$pfx.'role'],
                        	         'is_pro'  => $result[$pfx.'is_pro']));?></span><?php if($result[$pfx.'is_banned'] == 1) {?><span class="red">[Пользователь заблокирован]</span><?php }//if?> <span class="search-mess-time"><?= date('d.m.Y в H:i', strtotime($result['post_time']))?></span></span>
        </h4>
        <p><?= reformat($msg_text, 40, 0, 1)?></p>
        <p class="all-mess"><a href="/contacts/?from=<?= $result[$pfx.'login']?>" target="login">Смотреть все сообщения</a></p>
    </div>
</div><!--/search-lenta-item-->