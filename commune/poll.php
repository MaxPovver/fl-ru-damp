<? if(commune::isBannedCommune($mod)) $top['poll_closed'] = 't'; // Голосование закрыто для заблокированных в сообществе?>

<? if ($top['question'] != '') { ?>
<?php if($actionRating == 'blur') { ?><div class="b-post__txt b-post__txt_opacity_3"><? }//if?>
    <div id="poll-<?= $top['theme_id'] ?>" <?=( intval($top["deleted_id"]) ? 'class="b-post__deleted_txt"' : '' ) ?> >
        <?php $sQuestion = /*$top['moderator_status'] === '0' ? $stop_words->replace($top['question']) :*/ $top['question']; ?>
        <h4 class="b-layout__h4 b-layout__h4_bold"><?= reformat($sQuestion, 43, 0, 1) ?></h4>

        <div  id="poll-answers-<?= $top['theme_id'] ?>">
        <?
        $i = 0;
        $max = 0;
        if ($top['poll_closed'] == 't') {
            foreach ($top['answers'] as $answer)
                $max = max($max, $answer['votes']);
        }
        if ($top['poll_closed'] == 't') {
 ?>
            <table class="quiz-results">
                <tbody>
<? foreach ($top['answers'] as $answer) { 
    $sAnswer = /*$top['moderator_status'] === 0 ? $stop_words->replace($answer['answer']) :*/ $answer['answer'];
    ?>

                <tr class="quiz-result-txt">
                    <td><?= $answer['votes']; ?></td>
                    <th><?= reformat($sAnswer, 30, 0, 1) ?></th>
                </tr>
                <tr>
                    <td></td>
                    <td>
                        <span class="quiz-line" style="width: <?= ($max ? round(((100 * $answer['votes']) / $max) * 5) : 0) ?>px; min-width: 8px"><span><span></span></span></span>
                    </td>
                </tr>
<? } ?>
            </tbody></table>
<? } elseif ($top['poll_votes'] || !$user_id || $top['commune_blocked'] == 't' || $top['user_is_banned'] || $top['member_is_banned'] || !$is_member) { ?>
            <table class="quiz-results">
                <tbody>
<? foreach ($top['answers'] as $answer) { 
    $sAnswer = /*$top['moderator_status'] === 0 ? $stop_words->replace($answer['answer']) :*/ $answer['answer'];
    ?>
                <tr>
                    <td><?= $answer['votes']; ?></td>
                    <th><?= reformat($sAnswer, 30, 0, 1) ?></th>
                </tr>

<? } ?>
            </tbody></table>
<? } else { ?>
            <? if ($top['poll_multiple'] != 't') { ?><div class="b-radio b-radio_layout_vertical"><? } ?>
<? foreach ($top['answers'] as $answer) { 
    $sAnswer = /*$top['moderator_status'] === 0 ? $stop_words->replace($answer['answer']) :*/ $answer['answer'];
    ?>
                
				<? if ($top['poll_multiple'] == 't') { ?>
                	<div class="b-check b-check_padbot_5">
                     <table class="b-layout__table b-layout__table_width_full">
                        <td class="b-layout__one b-layout__one_width_10">
                           <input id="poll-<?= $top['theme_id'] ?>_<?= $i ?>" class="b-check__input" type="checkbox" name="poll_vote[]" value="<?= $answer['id'] ?>" />
                        </td>
                        <td class="b-layout__one">
                        <label class="b-check__label b-check__label_fontsize_13 b-check__label_margleft_5" for="poll-<?= $top['theme_id'] ?>_<?= $i++ ?>"><?= reformat($sAnswer, 30, 0, 1) ?></label>
                        </td>
                     </table>
                  </div>
				<? } else { ?>
					<div class="b-radio__item b-radio__item_padbot_10">
                    	<table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
                            <tr class="b-layout__tr">
                                <td class="b-layout__left b-layout__left_width_15"><input id="poll-<?= $top['theme_id'] ?>_<?= $i ?>" class="b-radio__input" type="radio" name="poll_vote" value="<?= $answer['id'] ?>" /></td>
                                <td class="b-layout__right"><label class="b-radio__label b-radio__label_fontsize_13" for="poll-<?= $top['theme_id'] ?>_<?= $i++ ?>"><?= reformat($sAnswer, 30, 0, 1) ?></label></td>
                            </tr>
                        </table>
                    </div>
				<? } ?>
				

<? } ?>
    <? if ($top['poll_multiple'] != 't') { ?></div><? } ?>
<? } ?>
    </div>




    <div class="post-quiz-btns b-buttons b-buttons_inline-block">
        <?
        if (!$top['poll_votes'] && $user_id && $top['poll_closed'] != 't' && $top['commune_blocked'] != 't' && !$top['user_is_banned'] && !$top['member_is_banned'] && $is_member) {
        ?>
            <span id="poll-btn-vote-<?= $top['theme_id'] ?>">
            	<a class="b-button b-button_flat b-button_flat_grey" href="javascript:void(0)" onclick="poll.vote('Commune', <?= $top['theme_id'] ?>); return false;">Ответить</a>
            </span>&nbsp;&nbsp;&nbsp;
            <span id="poll-btn-result-<?= $top['theme_id'] ?>"><a class="b-buttons__link b-buttons__link_dot_0f71c8" href="javascript:void(0)" onclick="poll.showResult('Commune', <?= $top['theme_id'] ?>); return false;">Посмотреть результаты</a>&nbsp;&nbsp;&nbsp;</span>
<? } else { ?>
            <span id="poll-btn-vote-<?= $top['theme_id'] ?>"></span>
            <span id="poll-btn-result-<?= $top['theme_id'] ?>"></span>
        <? } ?>
        <? if (($top['user_id'] == $user_id && $top['commune_blocked'] != 't' && !$top['user_is_banned'] && !$top['member_is_banned']) || $is_moder) { ?>
            <span id="poll-btn-close-<?= $top['theme_id'] ?>"><a class="b-buttons__link b-buttons__link_dot_0f71c8" href="javascript:void(0)" onclick="poll.close('Commune', <?= $top['theme_id'] ?>); return false;" ><?= (($top['poll_closed'] == 't') ? 'Открыть' : 'Закрыть') ?> опрос</a>&nbsp;&nbsp;&nbsp;</span>
            <span id="poll-btn-remove-<?= $top['theme_id'] ?>"><a class="b-buttons__link b-buttons__link_dot_c10601" href="javascript:void(0)" onclick="poll.remove('Commune', <?= $top['theme_id'] ?>); return false;" >Удалить опрос</a></span>
        <? } ?>
    </div>
</div>
<?php if($actionRating == 'blur') { ?></div><? }//if?>
<? } ?>