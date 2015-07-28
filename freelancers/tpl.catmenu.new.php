<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");
if ($uid && !is_emp()) {
  if($specs = professions::GetProfessionsByUser($uid, FALSE))
    $specs = professions::GetMirroredProfs(implode(',', $specs));
}
?>

<div class="b-catalog b-fon b-fon_bg_eef1f2 <?php if(is_emp()){ ?>b-catalog_emp<?php } ?>" data-menu="true" data-menu-descriptor="freelancer-type">
    <b class="b-fon__b1"></b>
    <b class="b-fon__b2"></b>
			<div class="b-fon__body">
        <ul class="b-catalog__list " id="accordion">
            <?php
            $sTagB = '<div class="b-catalog__item-inner b-catalog__item-inner_pad_5_10">'.(( $cat_menu_employers ) ? ($page > 1 ? '<a href="/employers/" class="b-catalog__link b-catalog__link_bold b-catalog__link_color_000">' : '').'<span class="b-catalog__item-current b-catalog__item-current_color_000">' : '<a href="/employers/" class="b-catalog__link b-catalog__link_bold b-catalog__link_color_000">');
            $sTagE = (( $cat_menu_employers ) ? '</span>'.($page > 1 ? '</a>' : '') : '</a>').'</div>';
            ?>
            <!-- <li class="b-catalog__item b-catalog__item_bg_74bb54"><?=$sTagB?>Работодатели<?=$sTagE?></li> -->
            <?php
            $sTagB = '<div class="b-catalog__item-inner b-catalog__item-inner_pad_5_10">'.(( $grey_catalog ) ? ($prof_id || $page > 1 ? '<a href="/freelancers/" class="b-catalog__link b-catalog__link_bold b-catalog__link_color_000">' : '').'<span class="b-catalog__item-current b-catalog__item-current_color_000">' : '<a href="/freelancers/" class="b-catalog__link b-catalog__link_bold b-catalog__link_color_000">');
            $sTagE = (( $grey_catalog ) ? '</span>'.($prof_id || $page > 1 ? '</a>' : '') : '</a>').'</div>';
            ?>
            <li class="b-catalog__item b-catalog__item_bg_eef1f2" data-menu-opener="true" data-menu-descriptor="freelancer-type" ><?=$sTagB?>Фрилансеры<?=$sTagE?></li>
            
            <?php 
            $iter = 0;
            $size = sizeof($profs);
            $prof = $profs[$iter++];
            $grnum = 0;
            if (!$freelancer && get_uid(0) && !is_emp()) {
                $freelancer = new freelancer();
            }
            if ($freelancer) {
                $freelancer->GetUserByUID($uid);
            }
            while ($iter <= $size) {
                if (!$prof) break;
                $lastgrname = $prof['groupname'];
                if ($prof['groupid'] == $prof_group_id){
                    $gr_init_num = $grnum;
                }
                if (!$lastgrname) break;
                $num = 1;
            ?>
            <li class="b-catalog__item b-catalog__item_bg_f5">
							<div class="b-catalog__item-inner b-catalog__item-inner_pad_3_10_0">
                
                <?php if (trim($prof['grouplink'])): ?>
                    <a class="toggler b-catalog__link b-catalog__link_color_000 b-catalog__link_padbot_7  <?php if ($prof['groupid'] == $prof_group_id || $prof['groupid'] == $prof_group_parent_id):?>b-catalog__link_active<?php endif;?>" href="/freelancers/<?=trim($prof['grouplink'])?>"><?=$prof['groupname']?></a>
                <?php else: ?>
                    <a class="toggler b-catalog__link b-catalog__link_color_000 b-catalog__link_padbot_7" href="javascript:void(null);"><?=$prof['groupname']?></a>
                <?php endif; ?>
                                
                <ul id="submenu<?=$iter?>" class="element b-catalog__inner-list" <?php if ($prof['groupid'] == $prof_group_id || $prof['groupid'] == $prof_group_parent_id):?>style="display: block !important;"<?php endif;?>>
                    <?php
                    do {
                        $in_spec = ($uid && ((is_array($specs) && in_array($prof['id'], $specs))) && ($freelancer->cat_show == 't'));
                        // #0011761 пункт 12!
                        $sTagB   = ( $prof['id'] == $prof_id && $page == 1 ) ? '<a href="/freelancers/'.$prof['link'].'/" class="b-catalog__link b-catalog__link_color_000 b-catalog__item-current">' : '<a href="/freelancers/'.$prof['link'].'/" class="b-catalog__link b-catalog__link_color_000">';
                        $sTagE   = ( $prof['id'] == $prof_id && $page == 1 ) ? '</a>' : '</a>';
                        if($prof['id'] == 194) { // #0015197
                            $prof['profname'] = str_replace("/", "/<br/>", $prof['profname'])."";
                        }
                    ?>
                    <li class="b-catalog__item b-catalog__item_bordtop_color_e7e6e5">
                        <?=$sTagB?><?=$prof['profname']?><?=$sTagE?>
                        <?php if ($in_spec) { ?>
                            <em class="b-catalog__here b-catalog__here_right b-catalog__here_full" title="Вы здесь">Вы здесь</em>
                        <? } //if ?>
                    </li>    
                    <?php
                        if ($prof['id'] == $prof_id) {
                            $cur_prof = $prof;
                            $group_id = $prof['groupid'];
                            $gr_init_num = $grnum;
                        }
                        $prof = $profs[$iter++];
                        $num++;
                    } while ($lastgrname == $prof['groupname']) ?>
                </ul>
							</div>
            </li>
            <?php
            $grnum++;
            }//while?>
        </ul>
			</div>
    <b class="b-fon__b2 b-fon__b2_clear_left"></b>
    <b class="b-fon__b1"></b>
</div><!-- b-catalog -->
<script type="text/javascript">
var resizeTimer;

window.addEvent('resize', function(){
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function() {
        var size=window.getSize();  
				if(size.x<1330) {
						$$('.b-catalog__here').each(function(el) { el.removeClass('b-catalog__here_full'); el.removeClass('b-catalog__here_mid');el.addClass('b-catalog__here_small') }); 
				} 
				if(size.x<1360 && size.x>1331) {
						$$('.b-catalog__here').each(function(el) { el.removeClass('b-catalog__here_full'); el.removeClass('b-catalog__here_small'); el.addClass('b-catalog__here_mid'); }); 
				} 
				if(size.x>1361) {
						$$('.b-catalog__here').each(function(el) { el.removeClass('b-catalog__here_mid'); el.removeClass('b-catalog__here_small'); el.addClass('b-catalog__here_full'); }); 
				} 
    }, 50);
});

var size=window.getSize();

				if(size.x<1330) {
						$$('.b-catalog__here').each(function(el) { el.removeClass('b-catalog__here_full'); el.removeClass('b-catalog__here_mid');el.addClass('b-catalog__here_small') }); 
				} 
				if(size.x<1360 && size.x>1331) {
						$$('.b-catalog__here').each(function(el) { el.removeClass('b-catalog__here_full'); el.removeClass('b-catalog__here_small'); el.addClass('b-catalog__here_mid'); }); 
				} 
				if(size.x>1361) {
						$$('.b-catalog__here').each(function(el) { el.removeClass('b-catalog__here_mid'); el.removeClass('b-catalog__here_small'); el.addClass('b-catalog__here_full'); }); 
				} 
</script>

