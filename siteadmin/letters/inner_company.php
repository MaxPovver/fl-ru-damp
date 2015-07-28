<?php
require_once( $_SERVER['DOCUMENT_ROOT'] . '/xajax/letters.common.php' );
$xajax->printJavascript( '/xajax/' );

$sym = letters::getCompaniesSymbols();
?>

<a href="/siteadmin/letters/?mode=add" class="b-button b-button_flat b-button_flat_green b-button_float_right">Добавить сторону</a>

<h2 class="b-layout__title">Стороны</h2>


<div class="b-fon b-fon_width_full b-fon_padbot_10 last-gift-block b-layout__txt_padtop_20" id="letters_msg" <?=($msgstr ? '' : 'style="display: none;"')?>>
    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf">
    	<div class="b-fon__txt b-fon__txt_center b-username">
            <span class="b-fon__txt b-fon__txt_bold"><?=$msgstr?></span>
            <br/>
            <a class="b-fon__link b-fon__link_bordbot_dot_0f71c8 b-fon__link_fontsize_11" href="javascript:void(0)" onclick="$('letters_msg').setStyle('display', 'none'); return false;">Скрыть</a>
        </div> 
    </div>
</div>



<?php if($sym['ru'] || $sym['num']) { ?>
<div class="b-layout__txt b-layout__txt_padtop_20">
	<?php if($sym['ru']) { foreach($sym['ru'] as $tsym) { ?>
		<a class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_bold" href="#" onClick="letters.showCompanies('<?=$tsym?>'); return false;"><?=mb_strtoupper($tsym)?></a>&nbsp;&nbsp;
	<?php } } ?>
	<?php if($sym['num']) { ?>
		<a class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_bold" href="#" onClick="letters.showCompanies('#'); return false;">#</a>
	<?php } ?>
</div>
<?php } ?>


<?php if($sym['en'] || $sym['num']) { ?>
<div class="b-layout__txt b-layout__txt_padtop_20">
	<?php if($sym['en']) { foreach($sym['en'] as $tsym) { ?>
		<a class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_bold" href="#" onClick="letters.showCompanies('<?=$tsym?>'); return false;"><?=mb_strtoupper($tsym)?></a>&nbsp;&nbsp;
	<?php } } ?>
	<?php if($sym['num']) { ?>
		<a class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_bold" href="#" onClick="letters.showCompanies('#'); return false;">#</a>
	<?php } ?>
</div>
<?php } ?>

<div>&nbsp;</div>


<div id="letters_company_lists"></div>