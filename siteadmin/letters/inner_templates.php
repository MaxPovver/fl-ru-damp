<?php
require_once( $_SERVER['DOCUMENT_ROOT'] . '/xajax/letters.common.php' );
$xajax->printJavascript( '/xajax/' );

?>

<a href="/siteadmin/letters/?mode=add_template" class="b-button b-button_round_green b-button_float_right">
    <span class="b-button__b1">
        <span class="b-button__b2">
             <span class="b-button__txt">Добавить шаблон</span>
        </span>
    </span>
</a>

<h2 class="b-layout__title">Шаблоны</h2>


<div class="b-fon b-fon_width_full b-fon_padbot_10 last-gift-block b-layout__txt_padtop_20" id="letters_msg" <?=($msgstr ? '' : 'style="display: none;"')?>>
    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf">
    	<div class="b-fon__txt b-fon__txt_center b-username">
            <span class="b-fon__txt b-fon__txt_bold"><?=$msgstr?></span>
            <br/>
            <a class="b-fon__link b-fon__link_bordbot_dot_0f71c8 b-fon__link_fontsize_11" href="javascript:void(0)" onclick="$('letters_msg').setStyle('display', 'none'); return false;">Скрыть</a>
        </div> 
    </div>
</div>



<div>&nbsp;</div>


<div id="letters_company_lists">

	<?php if($templates_list) { ?>
        <?php foreach ($templates_list as $template){ ?>
        <div class="b-layout__txt b-layout__txt_padbot_15">
            <a class="b-layout__link b-layout__link_bold b-layout__link_fontsize_15" href="/siteadmin/letters/?mode=edit_template&id=<?=$template['id']?>"><?=($template['title'] ? $template['title'] : '[без названия]')?></a> [<a id="tpl_id_<?=$template['id']?>" class="b-fon__link b-fon__link_bordbot_dot_0f71c8 b-fon__link_fontsize_11" href="/siteadmin/letters/?mode=del_template&id=<?=$template['id']?>" onclick="return addTokenToLink('tpl_id_<?=$template['id']?>', 'Вы уверены?')" >удалить</a>]
        </div>
        <?php } ?>
	<?php } else { ?>
		<div><strong>Шаблонов не найдено</div>
	<?php } ?>

</div>