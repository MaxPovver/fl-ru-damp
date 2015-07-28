<?
// $t_role - роль пользователя, если не определена в заметке.
// $name - логин пользователя
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/notes.php");
	$nt = new notes();
	$nrl = 0;
	$nuid =  get_uid(false);
	$note = $nt->GetNote($nuid, $name, $error);
	if (!$note['n_text']) {
    if($note_version==2)
      $text = 'Видеть написанное будете только вы.';
    else
      $text = "Вы можете оставить заметку о&nbsp;пользователе. Видеть написанное будете только вы и никто другой.";
		$nrl = 1;
 } else {
     $text = $note['n_text'];

     $text = reformat($text, 24, 0, 0, 1, 24);

     $t_role = $note['role'];
 }
	unset ($s_role);
	if (substr($t_role, 0, 1)  == '1') $s_role= "_emp"; else $s_role= "_frl";
	if ($nrl) $s_role= "";
	if (isset($inc)) $inc++; else { $inc = 0; }
	?>
<script type="text/javascript">
<!--
<? if ($inc == 0) { ?>
login = new Array();
rl = new Array();
txt = new Array();
src = new Array();
act = new Array();
<? } ?>
login[<?=$inc?>] = '<?=$name?>';
rl[<?=$inc?>] = <?=substr($t_role, 0, 1);?>;
txt[<?=$inc?>] = '<?=input_ref_scr($note['n_text'])?>';
src[<?=$inc?>] = '<?=input_ref_scr($note['n_text'])?>';
act[<?=$inc?>] = '<?=(($note['n_text'])?"update":"add")?>';
//-->
</script>
<? if ($note_version==2) { ?>
  <div style="padding-bottom:10px" id="notetd<?=$inc?>">
    <b>Ваша заметка:</b><br/>
    <div id="notetext<?=$inc?>"><?=$text?></div>
    <? if ($_SESSION['uid']) { ?><a href="javascript:UpdateNote(<?=$inc?>,2,'<?=$textarea_w?>')" class="blue"><? } else { ?><a href="/fbd.php" class="blue"><? } ?>Изменить</a>
  </div>
<? } else { ?>
  <td class="note<?=$s_role?> note-cont" id="notetd<?=$inc?>"<? if ($rowspan) { ?> rowspan="<?=$rowspan?>"<? } ?>>
  <em><strong>Заметка:&nbsp;</strong><span id="notetext<?=$inc?>" > <?=$text?></span></em>&nbsp;
    <? if ($_SESSION['uid']) { ?>
    <a href="javascript:UpdateNote(<?=$inc?>)" class="blue">
    <? } else { ?><a href="/fbd.php" class="blue"><? } ?> Изменить</a>
  </td>
<? } ?>
