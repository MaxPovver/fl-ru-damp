<td class="chk">
    <input onclick="checkSel()" type="checkbox" value="<?= $doc['id']; ?>" name="doc_cb[]"/>
</td>
<th>
    <div style="word-wrap: break-word; width:640px;"><a href="/service/docs/section/?id=<?= $doc['docs_sections_id'];?>#doc<?= $doc['id']?>"><?= htmlspecialchars(hyphen_words($doc['name'])); ?></a></div>
</th>
<td class="cat">
    <a href="/service/docs/section/?id=<?= $doc['docs_sections_id']; ?>" name="section_name_<?= $doc['docs_sections_id']; ?>"><?= htmlspecialchars(hyphen_words($doc['section_name'])); ?></a>
</td>
<td class="d">
    <?= date("d.m.Y", strtotime($doc['date_create'])); ?>
</td>
<td class="ops">
    <a href="#" onclick="xajax_EditDocFormPrepare(<?= $doc['id']; ?>); return false;"><img src="/images/ico-e-u.png" alt="Редактировать"></a>&nbsp;&nbsp; <a href=""><img src="/images/btn-remove2.png" alt="Удалить" onclick="xajax_DeleteDocHTML(<?= $doc['id']; ?>); return false;"></a>&nbsp;
</td>