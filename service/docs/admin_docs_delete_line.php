<td class="chk">
		<input type="checkbox" checked="true">
	</td>
	<th>
		<a href=""><?= hyphen_words($doc['name'])?></a>
	</th>
	<td class="doc-remove" colspan="3">
		<input onclick="xajax_DeleteDoc(<?= $doc['id']?>, <?= $doc['docs_sections_id']?>); return false;" type="button" value="Удалить" class="i-btn">&nbsp; <a href="#" onclick="xajax_GetDocHTML(<?= $doc['id']?>); return false;" class="lnk-dot-666">Отменить</a>&nbsp;
	</td>