<?
if ($uid && !is_emp()) {
  if($specs = professions::GetProfessionsByUser($uid, FALSE))
    $specs = professions::GetMirroredProfs(implode(',', $specs));
}
?>
<ul id="accordion">
    <li class="all-employers"><a href="/employers/">Работодатели</a></li>
    <li class="all-lancers"><a href="/freelancers">Все фрилансеры</a></li>
    <? $iter = 0;
    $size = sizeof($profs);
    $prof = $profs[$iter++];
    $grnum = 0;
    while ($iter <= $size){
         if (!$prof) break;
         $lastgrname = $prof['groupname'];
         if (!$lastgrname) break;
         $proj_groups[] = array('name' => $lastgrname, 'id' => $prof['groupid']);
         $num = 1; ?>
         <li>
              <a href="javascript:void(null);" class="toggler"><?=$prof['groupname']?></a>
              <ul class="element" id="submenu<?=$iter?>">
                   <?
                        do {
                          $in_spec = FALSE; // ($uid && ((is_array($specs) && in_array($prof['id'], $specs)))); ?>
                   <li<?=($prof['id'] == $prof_id ? ' class="active"' : '')?>>
                        <?if($num==1){?><script type="text/javascript">initCI('submenu<?=$iter?>')</script><?}?><span class="prf-cnt"><?=$prof['count']?></span><a href="/freelancers/<?=$prof['link']?>/"><?=$prof['profname']?></a><? if ($in_spec) { ?>&nbsp;<span class="prf-this">&larr;&nbsp;<span>Вы&nbsp;здесь</span></span><? } ?>
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
<script type="text/javascript">asynccall('initCtg(<?=(isset($gr_init_num)?$gr_init_num:-1)?>)')</script>
