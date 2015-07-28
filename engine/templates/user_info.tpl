<a href="/users/<?=$res['login']?>/" class="b-username__link" target="_blank"><?= view_avatar($res['login'], $res['photo']); ?></a>
<?= view_user3($res);?>
<div class="b-username__txt b-username__txt_padtop_5 b-username__txt_fontsize_11 b-username__txt_margleft_60">Безопасных сделок: <?=(int)$sbr_info['success_cnt']?></div>
<div class="b-username__txt b-username__txt_fontsize_11 b-username__txt_margleft_60">
    <a class="b-username__link b-username__link_fontsize_11 b-username__link_color_4e" href="/users/<?= $res['login']?>/opinions/?from=norisk#op_head" target="_blank">Рекомендации <?= !is_emp($res['role']) ? 'работодателей' : 'фрилансеров' ?></a>: 
		<span class="b-username__txt b-username__txt_nowrap">
			<a class="b-username__link b-username__link_fontsize_11 b-username__link_color_6db335" href="/users/<?= $res['login']?>/opinions/?from=norisk&sort=1#op_head" target="_blank">+<?=(int)$ocnt['norisk']['p']?></a>&#160;/&#160;
			<a class="b-username__link b-username__link_fontsize_11 b-username__link_color_4e" href="/users/<?= $res['login']?>/opinions/?from=norisk&sort=2#op_head" target="_blank"><?=(int)$ocnt['norisk']['n']?></a>&#160;/&#160;
			<a class="b-username__link b-username__link_fontsize_11 b-username__link_color_c10600" href="/users/<?= $res['login']?>/opinions/?from=norisk&sort=3#op_head" target="_blank">-<?=(int)$ocnt['norisk']['m']?></a>
		</span>
</div>
<div class="b-username__txt b-username__txt_fontsize_11 b-username__txt_margleft_60">
    <a class="b-username__link b-username__link_fontsize_11 b-username__link_color_4e" href="/users/<?= $res['login']?>/opinions/?from=users#op_head" target="_blank">Мнения пользователей</a>: 
		<span class="b-username__txt b-username__txt_nowrap">
			<a class="b-username__link b-username__link_fontsize_11 b-username__link_color_6db335" href="/users/<?= $res['login']?>/opinions/?from=users&sort=1#op_head" target="_blank">+<?=(int)$ocnt['all']['p']?></a>&#160;/&#160;
			<a class="b-username__link b-username__link_fontsize_11 b-username__link_color_4e" href="/users/<?= $res['login']?>/opinions/?from=users&sort=2#op_head" target="_blank"><?=(int)$ocnt['all']['n']?></a>&#160;/&#160;
			<a class="b-username__link b-username__link_fontsize_11 b-username__link_color_c10600" href="/users/<?= $res['login']?>/opinions/?from=users&sort=3#op_head" target="_blank">-<?=(int)$ocnt['all']['m']?></a>
		</span>
</div>