<div class="b-check">
	<input id="cover_<?=$letter['number']?>" class="b-check__input" type="checkbox" onClick="letters.checkUncheckCover(<?=$letter['number']?>, this);"><label class="b-check__label b-check__label_fontsize_13 b-check__label_bold" for="cover_<?=$letter['number']?>">Конверт <?=$letter['number']?></label>
</div>
<table class="b-layout__table b-layout__table_margbot_10" cellpadding="0" cellspacing="0" border="0">
	<tbody>
		<tr class="b-layout__tr">
			<td class="b-layout__left b-layout__left_padright_5">
				<div class="b-layout__txt">Тип</div>
			</td>
			<td class="b-layout__right">
				<div class="b-layout__txt"><?=$letter['rdelivery_title']?></div>
			</td>
		</tr>
		<tr class="b-layout__tr">
			<td class="b-layout__left b-layout__left_padright_5">
				<div class="b-layout__txt">Кому</div>
			</td>
			<td class="b-layout__right">
				<div class="b-layout__txt">
					<?php if($letter['is_company']=='t') { ?>
					<?=$letter['company']['name']?>
					<?php } else { ?>
					<?=($letter['recipient']['form_type']==1 ? $letter['recipient'][1]['fio'] : $letter['recipient'][2]['full_name'])?>
					<?php } ?>
				</div>
			</td>
		</tr>
		<tr class="b-layout__tr">
			<td class="b-layout__left b-layout__left_padright_5">
				<div class="b-layout__txt">Куда</div>
			</td>
			<td class="b-layout__right">
				<div class="b-layout__txt">
					<?php if($letter['is_company']=='t') { ?>
					<?=$letter['company']['index']?>,
					<?=$letter['company']['country_title']?>,
					<?=$letter['company']['city_title']?>,
					<?=$letter['company']['address']?>
					<?php } else { ?>
					<?=($letter['recipient']['form_type']==1 ? $letter['recipient'][1]['address'] : $letter['recipient'][2]['address'])?>
					<?php } ?>
				</div>
			</td>
		</tr>
	</tbody>
</table>
