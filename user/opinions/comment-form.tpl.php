<div id="<?= $isFeedback ? 'feedback_' : '' ?>ed_comm_form_<?= $op_id; ?>" comment="<?= (int)(bool)$comment ?>" class="b-layout b-layout_padtop_10">
		<table class="b-layout__table b-layout__table_width_full">
			<tr class="b-layout__tr">
				<td class="b-layout__left b-testimonials-collection-comment-field">
					<div class="b-textarea">
						<textarea id="<?= $isFeedback ? 'feedback_' : '' ?>edit_comm_<?= $op_id; ?>" class="b-textarea__textarea" rows="5" cols="20" onkeyup="opinionCheckMaxLengthUpdater(this);"><?= $comment; ?></textarea>
					</div>
				</td>
				<td class="b-layout__right b-layout__right_padleft_10 b-layout__right_width_60">
					<div class="b-layout__txt b-layout__txt_fontsize_11"><span id="opinion_max_length">0</span>/<?= $opinion_max_length; ?></div>
				</td>
			</tr>
		</table>
		<!--<div id="error_edit_comm_<?= $op_id; ?>"></div>-->
		<div class="b-buttons b-buttons_padtop_10">
			<a id="opinion-comment-form-submit-btn" class="b-button b-button_flat b-button_flat_green" href="javascript:void(0)" onclick="if (!$(this).hasClass('b-button_disable')) {opinionCommentSubmitForm('<?= $op_id; ?>', '<?= $id; ?>', '<?= $from; ?>' <?= $isFeedback ? ', true' : '' ?>)}; return false"><?= ($id ? 'Изменить' : 'Оставить'); ?> комментарий</a>								
			<a class="b-buttons__link b-buttons__link_dot_0f71c8 b-buttons__link_margleft_10" href="javascript:void(0);" onclick="opinionCheckMaxLengthStop('edit_comm_<?= $op_id; ?>'); opinionCancelForm('<?= $op_id; ?>', '<?= $id; ?>' <?= $isFeedback ? ', true' : '' ?>);">Отменить</a>
		</div>
</div>