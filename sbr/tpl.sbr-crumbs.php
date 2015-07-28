<div class="b-menu b-menu_padbot_5 b-menu_crumbs">
    <ul class="b-menu__list">
        <? for($i=0;$i<count($crumbs)-1; $i++) { ?>
        <li class="b-menu__item"><a class="b-menu__link" href="<?= $crumbs[$i]['href']?>"><?= $crumbs[$i]['name']?></a>&#160;&rarr;&#160;</li>
        <? }//for?>
    </ul>
</div>
<?php if(hasPermissions('sbr')  && $_SESSION['access']=='A') { ?>
<div class="b-fon b-fon_float_right">
    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_fontsize_13 b-fon__body_bg_ffeeeb">
		<div class="b-layout__txt"><a class="b-layout__link b-layout__link_float_right" href="<?=($site_uri ? $site_uri.'&' : '?')?>access=U">Выйти</a>Вы видите сделку глазами:</div>
		<div class="b-layout__txt">
            <?php if(!$_SESSION['E'] && !$_SESSION['F']) { ?>Администратора<?php } else {//if?><a href="<?=($site_uri ? $site_uri.'&' : '?')?>access=A" class="b-layout__link">Администратора</a><?php }//else?>&#160;&#160;&#160;
            <?php if($_SESSION['E']) { ?>Работодателя<?php } else {//if?><a href="<?=($site_uri ? $site_uri.'&' : '?')?>access=A&E=<?=$sbr->emp_login?>" class="b-layout__link">Работодателя</a><?php }//else?>&#160;&#160;&#160;
            <?php if($_SESSION['F']) { ?>Исполнителя<?php } else {//if?><a href="<?=($site_uri ? $site_uri.'&' : '?')?>access=A&F=<?=$sbr->frl_login?>" class="b-layout__link">Исполнителя</a><?php }//else?>
        </div>
	</div>
</div>
<?php }//if?>
<h1 class="b-page__title <?= $css_selector_crumbs ? $css_selector_crumbs : "b-page__title_padbot_5"?>"><?= reformat($crumbs[count($crumbs)-1]['name'], 35, 0, 1)?></h1>