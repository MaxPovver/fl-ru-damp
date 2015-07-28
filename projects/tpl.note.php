<? require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/notes.php");
$note = notes::GetNotes((int)$_SESSION['uid'], (int)$project['user_id'], $error);
if ($note['n_text']) {
    $noteText = reformat($note['n_text'], 22, 0, 0, 1, 22);
    $noteBtn = 'Редактировать';
} else {
    $noteText = '';
    $noteBtn = 'Добавить';
}
?>
<div id="noteTextBlock" class="b-note b-note_inline-block b-fon b-fon_bg_ffeda9">
    <div class="b-fon__b1"></div>
    <div class="b-fon__b2"></div>
    <div class="b-fon__body b-fon__body_pad_5_10">
        <div class="b-note__txt">
            <strong class="b-note__bold">Ваша заметка: </strong><span id="noteText"><?= $noteText ?></span> <a id="noteEditBtn" class="b-note__link b-note__link_bordbot_0f71c8" href="javascript:void(0)"><?= $noteBtn ?></a>
        </div>
    </div>
    <div class="b-fon__b2"></div>
    <div class="b-fon__b1"></div>
</div>
<div id="noteEditBlock" class="b-note b-fon b-fon_bg_ffeda9 b-fon_hide" style=" max-width: 600px;">
    <div class="b-fon__b1"></div>
    <div class="b-fon__b2"></div>
    <div class="b-fon__body b-fon__body_pad_2_5 b-layout">
        <table class="b-layout__table b-layout__table_width_full" border="0" cellpadding="0" cellspacing="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_width_full">
                    <div class="b-textarea ">
                        <textarea id="noteTextarea" class="b-textarea__textarea b-textarea__textarea_height_35" maxlength="200"><?= $note['n_text'] ?></textarea>
                    </div>
                </td>
                <td class="b-layout__right b-layout__right_padleft_5">
                    <a id="noteSaveBtn" href="javascript:void(0)" class="b-button b-button_flat b-button_flat_green">Сохранить</a>
                </td>
            </tr>
        </table>
    </div>
    <div class="b-fon__b2"></div>
    <div class="b-fon__b1"></div>
</div>
<script type="text/javascript">
    var PROJECTS_NOTE_LOGIN = '<?= $project['login'] ?>';
</script>