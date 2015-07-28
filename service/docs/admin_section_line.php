<td class="chk">
    <input name="section_<?= $section['id']; ?>" value="<?= $section['id']; ?>" <?= $section['count'] ? 'disabled="disabled"' : ''?> type="checkbox"/>
</td>
<td class="num"><?= $npp; ?>.</td>
<td class="sort">
    <a onclick="xajax_SectionMoveTo(<?= $section['id']; ?>,'up'); return false;" href="#"><img src="/images/arrow2-top<?= $section['is_first'] == 't' ? '-a' : '';?>.png" alt=""></a><a onclick="xajax_SectionMoveTo(<?= $section['id']; ?>,'down'); return false;" href="#"><img src="/images/arrow2-bottom<?= $section['is_last'] == 't' ? '-a' : '';?>.png" alt=""></a>
</td>
<th>
    <input type="hidden" id="section_name_<?= $section['id']; ?>" value='<?= htmlspecialchars($section['name'], ENT_QUOTES)?>'>
    <span class="mc-g-o"><span id="count_docs_<?=$section['id']?>"><?= $section['count']; ?></span>&nbsp;&nbsp; <a href="#" onclick="showSectionEdit('edit',<?= $section['id']; ?>); return false;"><img src="/images/ico-e-u.png" alt="Редактировать"></a><span id="del_block_sec_<?=$section['id']?>" <?if($section['count']) print("style='display:none'");?>>&nbsp;&nbsp; <a href="#" onclick="xajax_DeleteSectionHTML(<?= $section['id']; ?>, <?= $num; ?>); return false;"><img src="/images/btn-remove2.png" alt="Удалить"></a></span>&nbsp;&nbsp;</span>
            <span><?= htmlspecialchars(hyphen_words($section['name'])); ?></span>
</th>