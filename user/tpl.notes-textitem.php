<?php
if(!defined('IN_STDF')) { 
    header("HTTP/1.0 404 Not Found");
    exit();
}
ob_start(); /*Ўаблон используетс€ в подстановке его в innerHTML через AJAX*/?>
<table class="wrap-izbr">
  <tr>
    <td>
      <div class="b-fon b-fon_bg_fff9bf b-fon_pad_10 b-fon__border_radius_3">
            <span class="izbr-del" onclick="if(confirm('¬ы действительно хотите удалить заметку?')) xajax_delNote(<?= $_SESSION['uid']?>, <?= $rec['uid']?>, <?=$type?>)"></span>
            <span class="izbr-edit" onclick="xajax_getNotesForm(<?= $_SESSION['uid']?>, <?=$rec['uid']?>, <?=(int)$type?>)"></span><p class="text_notes_<?= $rec['uid']?>"><?= nl2br(reformat($note['n_text'], 19, 0, 0))?></p>
      </div>
    </td>
  </tr>
</table>
<?php
$html = ob_get_clean();
$html = str_replace(array("\r", "\n"), "", $html); // ”бираем переносы чтобы через AJAX не было проблем
print($html);
?>