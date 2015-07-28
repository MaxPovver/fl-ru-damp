<? if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; } ?>
<table class="b-layout__table margtop_10">
	<tr class="b-layout__one b-layout__one_valign_bot">
		<td class="b-layout__left_width_240 b-layout__txt_padbot_30"><div class="b-layout__title b-layout__txt_padbot_5">Жалобы</div></td>
		<td class="b-layout__right_width_90 b-layout__txt_padbot_30 b-layout__one_right b-layout__one_padright_15"><div class="b-layout__title b-layout__txt_right b-layout__txt_padbot_5"><?=$complains_all['sum'] ?></div></td>
		<td class="b-layout__right_width_30 b-layout__txt_padbot_30"></td>
	</tr>
	<tr class="b-layout__one">
		<td class="b-layout__left_width_240 b-layout__txt_padbot_5"><div class="b-layout__txt b-layout__txt_fontsize_15">На проекты от pro</div></td>
		<td class="b-layout__right_width_90 b-layout__txt_padbot_5 b-layout__one_right b-layout__one_padright_15"><div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_right"><?=$complains_all['pro'] ?></div></td>
		<td class="b-layout__right_width_30 b-layout__txt_padbot_5"><div class="b-layout__txt b-layout__txt_fontsize_11 relative top_4"><?= round(100*$complains_all['pro']/$complains_all['sum']).'%' ?></div></td>
	</tr>
	<tr class="b-layout__one">
		<td class="b-layout__left_width_240 b-layout__txt_padbot_5"><div class="b-layout__txt b-layout__txt_fontsize_15">На проекты не от pro</div></td>
		<td class="b-layout__right_width_90 b-layout__txt_padbot_5 b-layout__one_right b-layout__one_padright_15"><div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_right"><?=$complains_all['nopro'] ?></div></td>
		<td class="b-layout__right_width_30 b-layout__txt_padbot_5"><div class="b-layout__txt b-layout__txt_fontsize_11 relative top_4"><?= round(100*$complains_all['nopro']/$complains_all['sum']).'%' ?></div></td>
	</tr>
	<tr class="b-layout__one">
		<td colspan="3" class="b-layout__txt_padbot_15 b-layout__txt_padtop_30"><div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_bold">Бюджет проектов</div></td>
	</tr>
	<tr class="b-layout__one">
		<td class="b-layout__left_width_240 b-layout__txt_padbot_5"><div class="b-layout__txt b-layout__txt_fontsize_15">По договоренности</div></td>
		<td class="b-layout__right_width_90 b-layout__txt_padbot_5 b-layout__one_right b-layout__one_padright_15"><div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_right"><?=$complains_by_cost['result']['d'] ?></div></td>
		<td class="b-layout__right_width_30 b-layout__txt_padbot_5"><div class="b-layout__txt b-layout__txt_fontsize_11 relative top_4"><?=round(100*$complains_by_cost['result']['d']/$complains_all['sum'])?>%</div></td>
	</tr>
    <? for($i=0; $i<=$bcnt; $i++) { ?>
        <tr class="b-layout__one">
            <td class="b-layout__left_width_240 b-layout__txt_padbot_5"><div class="b-layout__txt b-layout__txt_fontsize_15"><?=$complains_by_cost['diaps'][$i]['html']?> руб.</div></td>
            <td class="b-layout__right_width_90 b-layout__txt_padbot_5 b-layout__one_right b-layout__one_padright_15"><div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_right"><?=$complains_by_cost['result'][$i]?></div></td>
            <td class="b-layout__right_width_30 b-layout__txt_padbot_5"><div class="b-layout__txt b-layout__txt_fontsize_11 relative top_4"><?=round(100*$complains_by_cost['result'][$i]/$complains_all['sum'])?>%</div></td>
        </tr>
    <? } ?>
	<tr class="b-layout__one">
		<td colspan="3" class="b-layout__txt_padbot_15 b-layout__txt_padtop_30"><div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_bold">ТОП 10 подкатегорий проектов</div></td>
	</tr>
    <? foreach($complains_categ as $i => $category) { ?>
        <tr class="b-layout__one">
            <td class="b-layout__left_width_240 b-layout__txt_padbot_5"><div class="b-layout__txt b-layout__txt_fontsize_15"><?=$category['name']?></div></td>
            <td class="b-layout__right_width_90 b-layout__txt_padbot_5 b-layout__one_right b-layout__one_padright_15"><div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_right"><?=$category['cnt']?></div></td>
            <td class="b-layout__right_width_30 b-layout__txt_padbot_5"><div class="b-layout__txt b-layout__txt_fontsize_11 relative top_4"><?=round(100*$category['cnt']/$complains_all['sum'])?>%</div></td>
        </tr>
    <? } ?>
</table>