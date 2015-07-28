<div class="b-layout__txt"><a class="b-layout__link" href="/siteadmin/letters/?mode=company">Все стороны</a> &rarr;</div>

<?php if($_GET['mode']=='edit') { ?>
<h2 class="b-layout__title">Редактирование стороны: <?=htmlspecialchars($company['name'])?></h2>
<?php } else { ?>
<h2 class="b-layout__title">Новая сторона</h2>
<?php } ?>

<form action="/siteadmin/letters/?mode=<?=htmlspecialchars($_GET['mode'])=='edit' ? 'update' : 'insert' ?>" method="POST" id="frm_company">
<input type="hidden" name="frm_company_id" value="<?=$company['id']?>">
<table cellspacing="0" cellpadding="0" border="0" class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20">
	<tbody>
		<tr class="b-layout__tr">
			<td class="b-layout__left b-layout__left_width_175">
				<div class="b-layout__txt b-layout__txt_padtop_5">Тип организации</div>
			</td>
			<td class="b-layout__right">
				<div class="b-combo b-combo_inline-block">
					<div class="b-combo__input b-combo__input_width_50">
						<input type="text" class="b-combo__input-text" id="frm_company_type" name="frm_company_type" size="10" value="<?=htmlspecialchars($company['frm_type'])?>">
					</div>
				</div>
				<div class="b-layout__txt b-layout__txt_fontsize_11">Укажите, если есть</div>
			</td>
		</tr>
	</tbody>
</table>

<table cellspacing="0" cellpadding="0" border="0" class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20">
	<tbody>
		<tr class="b-layout__tr">
			<td class="b-layout__left b-layout__left_width_175">
				<div class="b-layout__txt b-layout__txt_padtop_5">Ф.И.О/Название компании</div>
			</td>
			<td class="b-layout__right">
				<div class="b-combo b-combo_inline-block">
					<div class="b-combo__input b-combo__input_width_400">
						<input type="text" class="b-combo__input-text" id="frm_company_name" name="frm_company_name" size="80" value="<?=htmlspecialchars($company['name'])?>">
					</div>
				</div>
			</td>
		</tr>
	</tbody>
</table>

<table cellspacing="0" cellpadding="0" border="0" class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20">
	<tbody>
		<tr class="b-layout__tr">
			<td class="b-layout__left b-layout__left_width_175">
				<div class="b-layout__txt b-layout__txt_padtop_5">ФИО представителя</div>
			</td>
			<td class="b-layout__right">
				<div class="b-combo">
					<div class="b-combo__input b-combo__input_width_400">
						<input type="text" class="b-combo__input-text" id="frm_company_fio" name="frm_company_fio" size="80" value="<?=htmlspecialchars($company['fio'])?>">
					</div>
				</div>
				<div class="b-layout__txt b-layout__txt_fontsize_11">Укажите, если есть</div>
			</td>
		</tr>
	</tbody>
</table>

<table cellspacing="0" cellpadding="0" border="0" class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20">
	<tbody>
		<tr class="b-layout__tr">
			<td class="b-layout__left b-layout__left_width_175">
				<div class="b-layout__txt b-layout__txt_padtop_5">Страна, город</div>
			</td>
			<td class="b-layout__right">
				<div class="b-combo">
					<div class="b-combo__input b-combo__input_multi_dropdown b-combo__input_width_150 b-combo__input_resize b-combo__input_arrow_yes b-combo__input_init_citiesList b-combo__input_on_click_request_id_getcities b-combo__input_max-width_450  exclude_value_0_0 disallow_null <?=($_GET['mode']=='edit' ? "drop_down_default_{$company['city']} multi_drop_down_default_column_1" : "")?>">
						<input id="country" class="b-combo__input-text" id="frm_company_countrycity" name="frm_company_countrycity" type="text"  size="80" value="<?=htmlspecialchars($_GET['mode'])=='edit' ? "{$company['country_title']}: {$company['city_title']}" : "" ?>" />		
						<span class="b-combo__arrow"></span>
					</div>
				</div>
			</td>
		</tr>
	</tbody>
</table>


<table cellspacing="0" cellpadding="0" border="0" class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20">
	<tbody>
		<tr class="b-layout__tr">
			<td class="b-layout__left b-layout__left_width_175">
				<div class="b-layout__txt b-layout__txt_padtop_5">Индекс</div>
			</td>
			<td class="b-layout__right">
				<div class="b-combo">
					<div class="b-combo__input b-combo__input_width_400">
						<input type="text" class="b-combo__input-text" id="frm_company_index" name="frm_company_index" size="80" value="<?=htmlspecialchars($company['index'])?>">
					</div>
				</div>
			</td>
		</tr>
	</tbody>
</table>


<table cellspacing="0" cellpadding="0" border="0" class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20">
	<tbody>
		<tr class="b-layout__tr">
			<td class="b-layout__left b-layout__left_width_175">
				<div class="b-layout__txt b-layout__txt_padtop_5">Адрес</div>
			</td>
			<td class="b-layout__right">
				<div class="b-combo">
					<div class="b-combo__input b-combo__input_width_400">
						<input type="text" class="b-combo__input-text" id="frm_company_address" name="frm_company_address" size="80" value="<?=htmlspecialchars($company['address'])?>">
					</div>
				</div>
			</td>
		</tr>
	</tbody>
</table>

<div class="b-buttons b-buttons_padtop_40 b-buttons_padbot_20 b-buttons_padleft_180">
	<a href="" onClick="letters.submitCompany('<?=htmlspecialchars($_GET['mode'])?>'); return false;" class="b-button b-button_flat b-button_flat_green">Сохранить</a>
	<span class="b-buttons__txt b-buttons__txt_padleft_10">или</span>
	<a href="/siteadmin/letters/?mode=company" class="b-buttons__link b-buttons__link_dot_c10601">закрыть не сохраняя</a>
</div>
</form>