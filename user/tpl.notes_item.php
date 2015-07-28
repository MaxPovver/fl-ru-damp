<?php
if(!defined('IN_STDF')) { 
    header("HTTP/1.0 404 Not Found");
    exit();
}
if(count($notes[$rec['uid']]) > 0) {
    $note = $notes[$rec['uid']];
} else {
    $note = false;
}
$is_emp = is_emp($rec['role']);
$cls    = $is_emp?"emp":"frl";
?>
<div class="izbr-item" id="n<?=$rec['uid']?>">
    <span id="elm-offset-<?= $rec['uid']."-".$type?>"></span>
    <?= view_avatar($rec['login'], $rec['photo'],1,1,'b-pic b-pic_fl')?>
    <div class="izbr-text">
        <span class="user-inf">
            <span class="<?=$cls?>name11"><a href="/users/<?=$rec['login']?>" class="<?=$cls?>name11" title="<?=($rec['uname']." ".$rec['usurname'])?>"><?=($rec['uname']." ".$rec['usurname'])?></a> [<a href="/users/<?=$rec['login']?>/" class="<?=$cls?>name11" title="<?=$rec['login']?>"><?=$rec['login']?></a>]</span> <?= view_mark_user($rec);?> 
        </span>
        <?php if(!is_emp($rec['role'])) {?>
            Специализация: <?= professions::GetProfNameWP($rec['spec'], ' / ', "не указано", "lnk-666", true)?>
        <?php }//if?>
        <?php if($_SESSION['uid'] && $_SESSION['uid'] != $rec['uid']) {?>
        <?php /* userFav_* псевдо класс для корректной обработки сценария выполнения скрипта */?>
        <div class="userFav_<?=$rec['uid']?>">
            <?php if($note === false) { ?>
            <div class="sent-mark"><a href="javascript:void(0)" onclick="xajax_getNotesForm(<?= $rec['uid']?>, false, <?=$type?>);">Оставить заметку</a>&nbsp;<span></span></div>
            <?php } else { //if ?>
                <?include (TPL_DIR_NOTES."/tpl.notes-textitem.php"); ?>
            <?php }//else ?>
        </div>
        
        <?php }?>
    </div>
</div>