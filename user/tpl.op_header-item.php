<?php
if(!defined('IN_STDF')) { 
    header("HTTP/1.0 404 Not Found");
    exit();
}
?>
<li class="<?=$cls?>">
    <?=$aL?><?=$title?><?=$tR?>
    <? foreach($ops as $key=>$val) {
        ++$i;
        $sval = ($i==1 ? '+&nbsp;' : ($i==3 ? '-&nbsp;' : '')) . '<span id="ops-'.$type.$key.'">'.(int)$val.'</span>';
        if($i == $sort && $is_active) { ?><b><?=$sval?></b>&nbsp;&nbsp;<? }
        else { ?><a href="/users/<?=$user->login?>/opinions/?from=<?=$type?>&sort=<?=$i?>&period=<?=$period?>#op_head" class='ops-<?=$key?>'><?=$sval?></a>&nbsp; <? }
    } ?>
    <?=$aR?>
</li>
