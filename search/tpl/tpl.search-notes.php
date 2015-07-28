<?php
$mark_array = array((string) $result['n_text'],
                    (string) $result['login'],
                    (string) $result['uname'],
                    (string) $result['usurname']);

list ($text, $login, $uname, $usurname) = $element->mark($mark_array);
$text = strip_tags($text, "<em><br>");
?>

<div class="search-lenta-item c">
    <span class="number-item"><?= $i?>.</span>
    <span class="search-pic"><a href="/users/<?=$result['login']?>/?f=<?=stat_collector::REFID_SEARCH?>&stamp=<?=$_SESSION['stamp']?>"><?= view_avatar($result['login'], $result['photo'])?></a></span>
    <div class="search-item-body">
        <h4>
       
        <? $cls = is_emp($result['role']) ? 'empname11' : 'frlname11'; ?>
        <?=$session->view_online_status($result['login'])?><span class="<?= $cls?>"><a href="/users/<?=$result['login']?>/?f=<?=stat_collector::REFID_SEARCH?>&stamp=<?=$_SESSION['stamp']?>"><?= "{$uname} {$usurname} [{$login}]"?></a></span> <?= view_mark_user($result);?>
        </h4>
        <p><?= reformat($text, 40, 0, 1)?></p>
    </div>
</div><!--/search-lenta-item-->