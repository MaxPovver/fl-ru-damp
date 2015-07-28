
<td class="chk">
    <input type="checkbox">
</td>
<td class="num"><?= $num; ?>.</td>
<td class="sort">
    <a onclick="xajax_SectionMoveTo(<?= $section['id']; ?>,'up'); return false;" href="#"><img src="/images/arrow2-top<?= $section['is_first'] == 't' ? '-a' : '';?>.png" alt=""></a><a onclick="xajax_SectionMoveTo(<?= $section['id']; ?>,'down'); return false;" href="#"><img src="/images/arrow2-bottom<?= $section['is_last'] == 't' ? '-a' : '';?>.png" alt=""></a>
</td>
<th class="dg-remove-true">
    <span class="dg-remove">
        <input onclick="xajax_DeleteSection(<?= $section['id']?>); return false;" type="button" value="Удалить" class="i-btn">&nbsp; <a href="#" onclick="xajax_GetSectionHTML(<?= $section['id']; ?>, <?= $num;?>); return false;" class="lnk-dot-666">Отменить</a>&nbsp;
    </span>
    <span class="mc-g-o"><?= $num; ?>&nbsp;&nbsp; <a href=""><img src="/images/ico-e-u.png" alt="Редактировать"></a>&nbsp;&nbsp; <a href=""><img src="/images/btn-remove2.png" alt="Удалить"></a>&nbsp;&nbsp;</span>
            <?= htmlspecialchars(hyphen_words($section['name'])); ?>
</th>
