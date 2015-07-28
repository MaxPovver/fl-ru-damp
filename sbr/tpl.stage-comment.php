<form action="?Sending...#c_<?=$form_key?>" method="post" id="msg_form<?= $sbr->id?>" >
<table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0" id="form-comment">
    <tr class="b-layout__tr">
        <td class="b-layout__left b-layout__left_padleft_65 <?= $stage->orders == 'DESC' ? 'b-layout__one_padbot_40': ' b-layout__left_padtop_15'?>">
            <h3 class="b-layout__h3">Ваш комментарий</h3>
            <textarea class="ckeditor" id="ckeditor_comments"  rows="5" cols="10" name="msgtext"></textarea>
            
            <div class="b-file b-file_padbot_15 b-file_padtop_7">
                <div class="b-fon b-fon_width_full attachedfiles_comment"></div>
            </div>
           
            <a href="javascript:void(0)" id="stage_add_comment" sbr_id="<?= $sbr->id ?>" class="b-button b-button_flat b-button_flat_green">Добавить комментарий</a>
            
            <input type="hidden" name="site" value="<?=$site?>" />
            <input type="hidden" name="id" value="<?=$stage->id?>" />
            <input type="hidden" name="parent_id" value="0" />
            <input type="hidden" name="msg_id" value="0" />
            <input type="hidden" name="action" value="msg-add" />
        </td>
    </tr>
</table>
</form>
<?= attachedfiles::getFormTemplate('attachedfiles_comment', 'sbr', array(
    'maxfiles' =>    sbr::MAX_FILES,
    'maxsize'  =>    sbr::MAX_FILE_SIZE
)) ?>

<script type="text/javascript">

window.addEvent("domready", function () {

    var stageCommentAttachedfiles = new attachedFiles2( $('form-comment').getElement('.attachedfiles_comment'), {
        'hiddenName':   'attaches[]',
        'files':        <?= json_encode($comment_files) ?>,
        'selectors': {'template' : '.attachedfiles_comment-tpl'}
    });
    });
</script>