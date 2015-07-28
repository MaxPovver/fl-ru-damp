<div class="norisk-admin c">
	<div class="norisk-in">
        <table class="nr-a-opinions" cellspacing="0">
            <col width="105" />
			<col width="60" />
			<col width="235" />
			<col width="185" />
            <col width="320" />
			<thead>
				<tr>
                    <th>Дата</th>
					<th>Проект</th>
					<th>Пользователь</th>
					<th><div class="b-check"><input id="all_feedbacks_to_promo" class="b-check__input" type="checkbox" /><label class="b-check__label" for="all_feedbacks_to_promo">В промо</label></div></th>
					<th>Оценка</th>
					<th>Комментарий</th>
				</tr>
				<tr>
                    <td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
			</thead>
            <tfoot>
                <tr>
                    <td colspan="6">
                        <div class="pager">
                            <?=new_paginator($page, ceil($page_count/sbr_adm::PAGE_SIZE), 10, "%s?site=admin&mode={$mode}&page=%d%s")?>
                        </div>
                    </td>
                </tr>
            </tfoot>
			<tbody>
                <? foreach($sbr_feedbacks as $sf) { ?>
                <tr class="<?=(++$j%2==0 ? 'even' : 'odd')?>">
                    <td class="nr-a-o-date"><?=date('d.m.Y H:i', strtotime($sf['posted_time']))?></td>
                    <td class="nr-a-o-num"><a href="?access=A&<?=(is_emp($sf['role']) ? 'E' : 'F')?>=<?=$sf['login']?>&id=<?=$sf['sbr_id']?>">#<?=$sf['sbr_id']?></a></td>
                    <td><a href="/users/<?=$sf['login']?>/" class="nr-a-lnk-<?=(is_emp($sf['role']) ? 'emp' : 'frl')?>"><?=($sf['uname'].' '.$sf['usurname'].' ['.$sf['login'].']')?></a></td>
                    <td>
                        <input title="Разрешить/запретить показ этого отзыва в промо-блоке Безопасной Сделки" class="feedback_in_promo" id="feedback_id_<?= $sf['id'] ?>" feedback_id="<?= $sf['id'] ?>" type="checkbox" <?= $sf['in_promo'] === 't' ? 'checked' : '' ?> />
                    </td>
					<td>
						<span class="star-block">
                            <? for($i=1;$i<=10;$i++) { ?>
                            <b<?=($i<=round($sf['avg_rate_srv']) ? ' class="a"' : '')?>></b>
                            <? } ?>
						</span>
					</td>
                    <td><?=reformat($sf['descr_srv'],40,0,1)?></td>
				</tr>
                <? } ?>
			</tbody>
		</table>
	</div>
</div>
<script>
    window.addEvent('domready', function(){
        var $feedbacks = $$('.feedback_in_promo');
        $feedbacks.addEvent('change', function(){
            this.set('disabled', true);
            xajax_addFeedbackToPromo(this.get('feedback_id'), this.get('checked'));
        });
        
        var $allFeedbacksToPromo = $('all_feedbacks_to_promo');
        $allFeedbacksToPromo.addEvent('change', function(){
            this.set('disabled', true);
            var feedbacksID = [];
            $feedbacks.each(function(el){
                feedbacksID.push(el.get('feedback_id'));
            });
            xajax_addFeedbackToPromo(feedbacksID, this.get('checked'));
        });
    });
</script>
