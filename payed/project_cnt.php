<style>
.tarif {
	color: #333333;
	font-size: 13px;
}
</style>
<h1>Услуги</h1>
<table width="100%" border="0" cellspacing="0" cellpadding="19">
<tr valign="top">
	<td height="400" valign="top" bgcolor="#FFFFFF" class="box2" style="color: #333333;">
		<div align="center" style="color: #000000; font-size: 35px; margin-bottom: 25px;">Предоставленные услуги:</div>
		<div style="margin-bottom: 10px;">Платный проект на главной странице.</div>
		<div class="fl2_offer bordered" style="width: 500px;">
		<div class="fl2_offer_logo">
			<? if ($eprj['filename']) { ?>
		 <a href="http://<?=$eprj['link']?>" target="_blank" nofollow ><img src="/users/<?=$eprj['login']?>/upload/<?=$eprj['filename']?>" border="0" /></a>
			<? } ?>
			<div>Платный проект</div>
		</div>
		<div class="fl2_offer_budget">
			Бюджет: <?=CurToChar($eprj['cost'], $eprj['currency'])?>
		</div>
		<div class="fl2_offer_header">
		<?	if ($eprj['no_risk'] == "t") { ?><a href="/norisk2/" title="Безопасная Сделка"><img src="/images/shield.gif" alt="Безопасная Сделка" /></a><? } ?>
		<?=$eprj['name']?>
		</div>

		<div class="fl2_offer_content">
			<?=strip_tags(reformat(LenghtFormatEx($eprj['descr'], 300), 96, 1))?>
		</div>
		<? if ($eprj['attach']) { ?>
		<div class="flw_offer_attach">
			<a href="/users/<?=$eprj['login']?>/upload/<?=$eprj['attach']?>">Загрузить</a>
			<!-- (Rar, 25кб) -->
		</div>
		<? } ?>
		<div class="fl2_offer_meta">
		<? if ($eprj['anon_id']) { ?>
		Автор: Не зарегистрирован <br />
		Категория: <?=$eprj['catname']?><br />
		<? if ($eprj['icq']) { ?>Icq: <?=$eprj['icq']?><br /><? } ?>
		<? if ($eprj['mail']) { ?>Электронная почта: <?=$eprj['mail']?><br /><? } ?>
		<? if ($eprj['phone']) { ?>Телефон: <?=$eprj['phone']?><? } ?>
	<? } else { ?>
		Автор: <a href="/users/<?=$eprj['login']?>"><?=$eprj['uname']?> <?=$eprj['usurname']?> [<?=$eprj['login']?>]</a><br />
		Категория: <?=$eprj['catname']?><br />
	<? } ?>
		</div>
	<? if (is_new_prj($eprj['post_date'])) { ?>
		<div class="fl2_comments_link">
			<a href="javascript:void(0);">Комментарии (0)</a>
		</div>
	<? } ?>
	</div>
		
	</td>
</tr>
</table>