<?
if ($uid && !is_emp()) {
  if($specs = professions::GetProfessionsByUser($uid, FALSE))
    $specs = professions::GetMirroredProfs(implode(',', $specs));
}
?>
<div class="m-cat-main c">
    <b class="b1"></b>
    <b class="b2"></b>
    <ul id="accordion">
        <?php
        $sTagB = '<span class="wrap-item">'.(( $cat_menu_employers ) ? ($page > 1 ? '<a href="/employers/">' : '').'<strong>' : '<a href="/employers/">');
        $sTagE = (( $cat_menu_employers ) ? '</strong>'.($page > 1 ? '</a>' : '') : '</a>').'</span>';
        ?>
        <li class="all-employers"><?=$sTagB?>Каталог работодателей<?=$sTagE?></li>
        <?php
        $sTagB = '<span class="wrap-item">'.(( $grey_catalog ) ? ($prof_id || $page > 1 ? '<a href="/freelancers/">' : '').'<strong>' : '<a href="/freelancers/">');
        $sTagE = (( $grey_catalog ) ? '</strong>'.($prof_id || $page > 1 ? '</a>' : '') : '</a>').'</span>';
        ?>
        <li class="all-free-lancers"><?=$sTagB?>Каталог фрилансеров<?=$sTagE?></li>
        <? $iter = 0;
        $size = sizeof($profs);
        $prof = $profs[$iter++];
        $grnum = 0;
        while ($iter <= $size){
             if (!$prof) break;
             $lastgrname = $prof['groupname'];
             if (!$lastgrname) break;
             //$proj_groups[] = array('name' => $lastgrname, 'id' => $prof['groupid']);
             $num = 1; ?>
             <li>
                  <a href="javascript:void(null);" class="toggler"><?=$prof['groupname']?></a>
                  <ul class="element" id="submenu<?=$iter?>">
                       <?
                            do {
                                $in_spec = ($uid && ((is_array($specs) && in_array($prof['id'], $specs))));
                                
                                // #0011761 пункт 12!
                                $sTagB   = ( $prof['id'] == $prof_id && $page == 1 ) ? '<span>' : '<a href="/freelancers/'.$prof['link'].'/'.($f_country_lnk ? $f_country_lnk.'/' : '').($f_city_lnk ? $f_city_lnk.'/' : '').'">';
                                $sTagE   = ( $prof['id'] == $prof_id && $page == 1 ) ? '</span>' : '</a>';
                                if($prof['id'] == 194) { // #0015197
                                    $prof['profname'] = str_replace("/", "/<br/>", $prof['profname']);
                                }
  
                        ?>
                       <li <?=($prof['id'] == $prof_id?'class="a"':'')?>>
                            <?if($num==1){?><script type="text/javascript">initCI('submenu<?=$iter?>')</script><?}?><span class="wrap-item"><span class="prf-cnt"><script type="text/javascript">document.write('<?=$prof['count']?>');</script></span><?=$sTagB?><?=$prof['profname']?><?=$sTagE?></span><? if ($in_spec) { ?><em class="here here3" id="here_pointer" title="Вы здесь">Вы здесь</em><? } ?>
                       </li>
                            <?



                            if ($prof['id'] == $prof_id) {
                                $cur_prof = $prof;
                                $group_id = $prof['groupid'];
                                $gr_init_num = $grnum;
                            }
                            $prof = $profs[$iter++];
                            $num++;
                            } while ($lastgrname == $prof['groupname']) ?>
                  </ul>
             </li>
        <?
        $grnum++;
        } ?>
    </ul>
    <b class="b2"></b>
    <b class="b1"></b>
</div>
<script type="text/javascript">
asynccall('initCtg(<?=(isset($gr_init_num)?$gr_init_num:-1)?>)');
var resizeTimer;

window.addEvent('resize', function(){
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function() {
        var size=window.getSize();  
        if(size.x<1043) {
            $$('.here').each(function(el) { el.removeClass('here2'); el.removeClass('here3'); }); 
        } 
        if(size.x<1060 && size.x>1042) {
            $$('.here').each(function(el) { el.removeClass('here2'); el.removeClass('here3'); el.addClass('here2'); }); 
        } 
        if(size.x>1059) {
            $$('.here').each(function(el) { el.removeClass('here2'); el.removeClass('here3'); el.addClass('here3'); }); 
        } 
    }, 50);
});

var size=window.getSize();

if(size.x<1043) {
    $$('.here').each(function(el) { el.removeClass('here2'); el.removeClass('here3'); }); 
} 
if(size.x<1060 && size.x>1042) {
    $$('.here').each(function(el) { el.removeClass('here2'); el.removeClass('here3'); el.addClass('here2'); }); 
} 
if(size.x>1059) {
    $$('.here').each(function(el) { el.removeClass('here2'); el.removeClass('here3'); el.addClass('here3'); }); 
} 
</script>
