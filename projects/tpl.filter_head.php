<div class="b-layout__txt b-layout__txt_float_right b-layout__txt_relative b-layout__txt_padtop_8">
    <div class="b-layout__txt b-layout__txt_float_right b-layout__txt_relative">
        <a onClick="togF(this);" 
           class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-filter-toggle-link" 
           href="javascript:void(0)"><?=(($filter_show)?"Свернуть":"Развернуть")?></a>
    </div>
    <div class="b-layout__txt b-layout__txt_float_left b-layout__txt_padright_15">
        <? if ($filter_apply) { ?>
            <a class="b-layout__link b-layout__link_color_55b12e b-layout__link_bold b-layout__link_no-decorat" 
               href="/projects<?=$frm_action2?><?=$prmd?>action=deletefilter<?=$filter_query?>">
                <span class="b-icon b-icon__filtr b-icon__filtr_on"></span> Отключить фильтр
            </a>
        <? } else { ?>
            <a class="b-layout__link b-layout__link_color_969696 b-layout__link_no-decorat" 
               href="/projects<?=$frm_action2?><?=$prmd?>action=activatefilter<?=$filter_query?>">
                <span class="b-icon b-icon__filtr b-icon__filtr_off"></span> Включить фильтр</a>
        <? } ?>
    </div>
    
</div>