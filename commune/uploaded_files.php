
    <?
//    var_dump($uploaded);
    if (is_array($uploaded)) {
        $i = 0;
        foreach ($uploaded as $attach) {
            $attachFile = new CFile("users/" . substr($mess['user_login'], 0, 2) . "/" . $mess['user_login'] . "/upload/" . $attach['fname']);
            $attach['ftype'] = $attachFile->getext();
            $is_first = $i == 0;
            $is_last = $i == count($uploaded) - 1;
            $i++;
    ?>
            <li>
                <span class="ffa-sort">
                    <img onclick="xajax_FileMoveTo(<?= $attach['id'] ?>, <?= $cid ?>, 'up'); return false;" src="/images/arrow2-top<?= $is_first ? '-a' : ''; ?>.png" alt="">
                    <img onclick="xajax_FileMoveTo(<?= $attach['id'] ?>, <?= $cid ?>, 'down'); return false;" src="/images/arrow2-bottom<?= $is_last ? '-a' : ''; ?>.png" alt="">
                </span>
                <a href="javascript:void(0)" onclick="xajax_MsgDelFile(<?= $cid ?>,<?= $attach['id'] ?>)" title="Удалить"><img src="/images/btn-remove2.png" alt="Удалить"></a><a href="<?= WDCPREFIX ?>/users/<?= $mess['user_login'] ?>/upload/<?= $attach['fname'] ?>" target="_blank" class="mime <?= getIcoClassByExt($attach['fname']);?>"><?= $attach['ftype'];?></a>, <?= sizeFormat($attach['size']); ?>
            </li>
    <? } ?>
<? } ?>
