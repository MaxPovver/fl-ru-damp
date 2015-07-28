
               
            <? seo_start($is_ajax)?>
            <? if ($row['cost']) {
                $priceby_str = getPricebyProject($row['priceby']);
                if($row['cost']=='' || $row['cost']==0) {
                    $priceby_str = "";
                }
              }
            ?>
        <?
        $spec = projects::getPrimarySpec($row['id']);
        $row['category'] = $spec['category_id'];
        if (is_new_prj($row['post_date'])) {
            $blink = getFriendlyURL("blog", $row['thread_id']);
        } else {
            $blink = getFriendlyURL("project", $row['id']);
        }
        $plink = "/users/".$row['login']."/project/?prjid=".$row['id'];
        if ($row['payed'] && $row["kind"]!=2 && $row["kind"] != 7) {
            ?>
            <tr style="vertical-align:top"><td style="padding-left: 10px; padding-right: 10px; padding-bottom: 5px;">
            <div class="fl2_date">
                <div class="fl2_date_day">
                    <?=str_ago_pub(strtotimeEx($row['create_date']))?>
                </div>
                <div class="fl2_date_date">
                    <?=strftime("%d ",strtotimeEx($row['create_date'])).monthtostr(strftime("%m",strtotimeEx($row['create_date']))) . ", " . $daysOfWeek[date("N",strtotimeEx($row['create_date']))]?>
                </div>
                <div class="clear"></div>
            </div>
            <div class="hr"></div>
            <div class="fl2_offer bordered">
                <div class="fl2_offer_logo">
                    <div>Платный проект</div>
                    <? if ($row['cost']) { $priceby_str = getPricebyProject($row['priceby']);?>
                    <div class="fl2_offer_budget">Бюджет:
                    <? if (hasPermissions('projects') && $user->login!=$_SESSION["login"]) {
                            ?><a id="prj_budget_lnk_<?=$row['id']?>" class="b-post__link  b-post__link_dot_6db335" href="#" onClick="popupShowChangeBudget(<?=$row['id']?>, '<?=$row['cost']?>', '<?=$row['currency']?>', '<?=$row['priceby']?>', false, <?=$row['id']?>, 1, 2); return false;"><?=CurToChar($row['cost'], $row['currency']) ?><?=$priceby_str?></a>
                     <?} else {
                             print CurToChar($row['cost'], $row['currency']).$priceby_str; 
                         }?> 
                    </div>
                    <? } else { 
                        if (hasPermissions('projects') && $user->login!=$_SESSION["login"]) {
                                ?><var class="bujet-dogovor"><a id="prj_budget_lnk_<?=$row['id']?>" class="b-post__link b-post__link_dot_6db335" href="#" onClick="popupShowChangeBudget(<?=$row['id']?>, '', 0, 1, true, <?=$row['id']?>, 1, 2); return false;">По договоренности</a></var><?
                        } 
                        else {
                                ?><var class="bujet-dogovor">По договоренности</var> <?
                        } ?>
                    <? } ?>
                    <? if ($row['logo_name']) {?>
                        <a href="http://<?= formatLink($row['link'])?>" target="_blank" nofollow  ><img class="b-layout__pic b-layout__pic_float_right b-layout__pic_clear_right"  src="<?=WDCPREFIX.'/'.$row['logo_path'].$row['logo_name']?>" alt="" /></a>
                    <? } else {?>
                        <img  src="/images/public_your_logo.gif" alt="" />
                    <? }?> 
                </div>
                <div class="fl2_offer_header"> 
                    <? /* #0019741 if ($row['prefer_sbr']=='t') {?><img src="/images/sbr_p.gif" class="sbr_p" title="Работодатель&nbsp;хочет&nbsp;работать&nbsp;через&nbsp;Cделку&nbsp;без&nbsp;риска" alt="Работодатель хочет работать через Cделку без риска"><? } */?>
                    <? if ($row['sbr_id'] && (hasPermissions('projects') || $row['sbr_emp_id']==$_SESSION['uid']||$row['sbr_frl_id']==$_SESSION['uid'])) { ?>
                        <a href="/<?= sbr::NEW_TEMPLATE_SBR?>/<?=($row['sbr_emp_id']==$_SESSION['uid']||$row['sbr_frl_id']==$_SESSION['uid']||hasPermissions('projects') ? "?id={$row['sbr_id']}" : '').(hasPermissions('projects') ? "&access=A&E={$user->login}" : '')?>"><img src="/images/shield_sm.gif" alt="" /></a><? } ?>
                    <? if ($row['closed']=='t') {?><img src="/images/ico_closed.gif" alt="Проект закрыт" /><? }?>
                        <?php $sTitle = $row['moderator_status'] === '0' && $row['kind'] != 4 && $user->is_pro != 't' ? $stop_words->replace($row['name']) : $row['name']; ?>
                        <a id="prj_name_<?=$row["id"] ?>" name="/proonly.php" href="<?=$blink?>" class="fl2_offer_header" title=""><?=reformat($sTitle, 100, 0, 1)?></a>
                 </div>
                 <?php $sDescr = $row['moderator_status'] === '0' && $row['kind'] != 4 && $user->is_pro != 't' ? $stop_words->replace($row['descr']) : $row['descr'];
                 if (is_new_prj($row['post_date'])) {
                     $sDescr = reformatExtended($sDescr);
                 }
                 ?>
                 <div class="fl2_offer_content"><?=ereg_replace("\r","",ereg_replace("\n","",reformat($sDescr, 60)))?></div>
                 <? if (is_new_prj($row['post_date'])) { ?>
                        <br /><?=((!$row["comm_count"] || $row["comm_count"] % 10==0 || $row["comm_count"] % 10 >4 || ($row["comm_count"] >4 &&  $row["comm_count"]<21)) ?  '<a class="public_blue" href="'.getFriendlyURL("blog", $row['thread_id']).'">'.$row["comm_count"].' предложений</a>' : (($row["comm_count"] % 10 == 1 || $row["comm_count"]==1) ?  '<a class="public_blue" href="'.getFriendlyURL("blog", $row['thread_id']).'">'.$row["comm_count"].' предложение</a>' : '<a class="public_blue" href="'.getFriendlyURL("blog", $row['thread_id']).'">'.$row["comm_count"].' предложения</a>'  )   )?><br /><br />
<?
            }
            else {
?>
            <br /><?=((!$row["offers_count"] || $row["offers_count"] % 10==0 || $row["offers_count"] % 10 >4 || ($row["offers_count"] >4 &&  $row["offers_count"]<21)) ?  '<a class="public_blue" href="'.$blink.'">'.$row["offers_count"].' предложений</a>' : (($row["offers_count"] % 10 == 1 || $row["comm_count"]==1) ?  '<a class="public_blue" href="'.$blink.'">'.$row["offers_count"].' предложение</a>' : '<a class="public_blue" href="'.$blink.'">'.$row["offers_count"].' предложения</a>'  )   )?><br /><br />
<?
            }
            ?>
            <br />
            <div class="fl2_offer_meta">Прошло времени с момента публикации: 
                <?=ago_pub_x(strtotimeEx($row['create_date']))?><br />
                Автор: <a href="/users/<?=$user->login?>"><? print $user->uname." "; print $user->usurname; ?> [<?=$user->login?>]</a><br />
                Раздел: <?=projects::getSpecsStr($row['id'],' / ', ', ');?><? /* $category=$proj_groups_by_id[$row['category']]; print $category; */?>
                <? if ($row['pro_only']=='t') {?>
                    <br /><span  class="fl2_offer_meta2" style="background-color:#fff7ee;">Отвечать на проект могут только пользователи с аккаунтом <a href="/payed/"><img style="background:none" src="/images/icons/f-pro.png" alt="" class="pro" /></a></span>
                <? }?>
            </div>
            <div class="fl2_comments_link">
                <div style="padding:12px 0px 0px 0px;"></div>
            </div>


            <? if($row['exec_login']) { ?>
            <div class="b-fon b-fon_padbot_15">
                    <b class="b-fon__b1"></b>
                    <b class="b-fon__b2"></b>
                    <div class="b-fon__body b-fon__body_pad_10">
                        <span class="b-fon__txt b-fon__txt_float_right b-fon__txt_fontsize_11">Рейтинг: <?= round($row['exec_rating'],1)?></span>
                        <span class="b-fon__txt b-fon__txt_bold b-fon__txt_fontsize_13">
                            <?php if($row['kind']==2 || $row['kind']==7) { ?>
                                Победитель определен:
                            <?php } else { ?>
                                Исполнитель определен:
                            <?php } ?>
                        </span>
                        <div class="b-username b-username_bold b-username_inline">
                            <a class="b-username__link" href="/users/<?=$row['exec_login']?>/"><?=($row['exec_name']." ".$row['exec_surname'])?></a> <span class="b-username__login b-username__login_color_fd6c30">[<a class="b-username__link" href="/users/<?=$row['exec_login']?>"><?=$row['exec_login']?></a>]</span> <?=view_mark_user($row, "exec_"); ?>
                        </div>
                        <div class="i-opinion i-opinion_padtop_10">
                            <span class="b-opinion">
                                <span class="b-opinion__txt"><a class="b-opinion__link  b-opinion__link_color_4e" href="/users/<?=$row['exec_login']?>/opinions/">Отзывы пользователей</a></span>
                                <span class="b-opinion__txt b-opinion__txt_color_6db335"><? if(($row['sbr_opi_plus'] + $row['ops_all_plus']) > 0) { ?><a class="b-opinion__link b-opinion__link_decoration_no b-opinion__link_color_6db335" href="/users/<?=$row['exec_login']?>/opinions/?sort=1#op_head">+</a><? } ?><a class="b-opinion__link b-opinion__link_color_6db335" href="/users/<?=$row['exec_login']?>/opinions/?sort=1#op_head"><?= (int) ($row['sbr_opi_plus'] + $row['ops_all_plus'])?></a></span>
                                <span class="b-opinion__txt b-opinion__txt_color_4e"><a class="b-opinion__link b-opinion__link_color_4e" href="/users/<?=$exec_info['login']?>/opinions/?sort=2#op_head"><?=(int) ($row['sbr_opi_null'] + $row['ops_all_null']) ?></a></span>
                                <span class="b-opinion__txt b-opinion__txt_color_c10600"><? if(($row['sbr_opi_minus'] + $row['ops_all_minus']) > 0) { ?><a class="b-opinion__link b-opinion__link_decoration_no b-opinion__link_color_c10600" href="/users/<?=$row['exec_login']?>/opinions/?sort=3#op_head">&minus;</a><? } //if?><a class="b-opinion__link b-opinion__link_color_c10600" href="/users/<?=$row['exec_login']?>/opinions/?sort=3#op_head"><?=(int) ($row['sbr_opi_minus'] + $row['ops_all_minus']) ?></a></span>
                            </span>
                        </div>
                    </div>
                    <b class="b-fon__b2"></b>
                    <b class="b-fon__b1"></b>
                </div>
            <? } ?>
            </div>

            </td> 
            </tr>
        

            
        <?
        if ($pn > $pj+1)
        {

        ?>


            <?
        }
        }
        
        else {?>
        <tr style="vertical-align:top"><td style="padding-left: 10px; padding-right: 10px;">
        
        <div class="fl2_date">
            <div class="fl2_date_day">
            <?=str_ago_pub(strtotimeEx($row['create_date']))?>
            </div>
            <div class="fl2_date_date">
            <?=strftime("%d ",strtotimeEx($row['create_date'])).monthtostr(strftime("%m",strtotimeEx($row['create_date']))). ", " . $daysOfWeek[date("N",strtotimeEx($row['create_date']))]?>
            </div>
            <div class="clear"></div>
        </div>
            <div class="fl2_offer">
            <? if ($row['logo_name']) {?>
            <div class="fl2_offer_logo">
                <a href="http://<?= formatLink($row['link'])?>" target="_blank" nofollow ><img  src="<?=WDCPREFIX.'/'.$row['logo_path'].$row['logo_name']?>" alt="" /></a>
            </div>
            <? }?>
            <?if ($row['cost']) {
                 $priceby_str = getPricebyProject($row['priceby']);?>
                 <div class="fl2_offer_budget">Бюджет: <?php 
                     if (hasPermissions('projects') && $user->login!=$_SESSION["login"]) {
                            ?><a id="prj_budget_lnk_<?=$row['id']?>" class="b-post__link  b-post__link_dot_6db335" href="#" onClick="popupShowChangeBudget(<?=$row['id']?>, '<?=$row['cost']?>', '<?=$row['currency']?>', '<?=$row['priceby']?>', false, <?=$row['id']?>, 1, 2); return false;"><?=CurToChar($row['cost'], $row['currency']) ?><?=$priceby_str?></a>
                     <?} else {
                             print CurToChar($row['cost'], $row['currency']).$priceby_str; 
                         }?>
                 </div>
            <? } else { 
                        if (hasPermissions('projects') && $user->login!=$_SESSION["login"]) {
                                ?><var class="bujet-dogovor"><a id="prj_budget_lnk_<?=$row['id']?>" class="b-post__link b-post__link_dot_6db335" href="#" onClick="popupShowChangeBudget(<?=$row['id']?>, '', 0, 1, true, <?=$row['id']?>, 1, 2); return false;">По договоренности</a></var><?
                        } 
                        else {
                                ?><var class="bujet-dogovor">По договоренности</var> <?
                        } ?>
            <? } ?>
            <div class="fl2_offer_header"> 
                <? /* #0019741 if ($row['prefer_sbr']=='t') {?><img src="/images/sbr_p.gif" class="sbr_p" title="Работодатель&nbsp;хочет&nbsp;работать&nbsp;через&nbsp;Cделку&nbsp;без&nbsp;риска" alt="Работодатель хочет работать через Cделку без риска"><? } */?>
                <? if ($row['sbr_id'] && (hasPermissions('projects') || $row['sbr_emp_id']==$_SESSION['uid']||$row['sbr_frl_id']==$_SESSION['uid'])) { ?><a href="/<?= sbr::NEW_TEMPLATE_SBR?>/<?=($row['sbr_emp_id']==$_SESSION['uid']||$row['sbr_frl_id']==$_SESSION['uid']||hasPermissions('projects') ? "?id={$row['sbr_id']}" : '').(hasPermissions('projects') ? "&access=A&E={$user->login}" : '')?>"><img src="/images/shield_sm.gif" alt="" /></a><? } ?>
                <?if ($row['closed']=='t') {?><img src="/images/ico_closed.gif" alt="Проект закрыт" /><?}?>
                <?php $sTitle = $row['moderator_status'] === '0' && $row['kind'] != 4 && $user->is_pro != 't' ? $stop_words->replace($row['name']) : $row['name']; ?>
                <a href="<?=$blink?>" id="prj_name_<?=$row["id"] ?>"><?=reformat($sTitle, 20, 0, 1)?></a>
            </div>
            <?php $sDescr = $row['moderator_status'] === '0' && $row['kind'] != 4 && $user->is_pro != 't' ? $stop_words->replace($row['descr']) : $row['descr'];
            if (is_new_prj($row['post_date'])) {
                $sDescr = reformatExtended($sDescr);
            }
            ?>
            <div class="fl2_offer_content"><?=ereg_replace("\r","",ereg_replace("\n","",reformat($sDescr, 65)))?></div><?
            if (is_new_prj($row['post_date'])) {
?>
            <br /><?=((!$row["comm_count"] || $row["comm_count"] % 10==0 || $row["comm_count"] % 10 >4 || ($row["comm_count"] >4 &&  $row["comm_count"]<21)) ?  '<a class="public_blue" href="'.getFriendlyURL("blog", $row['thread_id']).'">'.$row["comm_count"].' предложений</a>' : (($row["comm_count"] % 10 == 1 || $row["comm_count"]==1) ?  '<a class="public_blue" href="'.getFriendlyURL("blog", $row['thread_id']).'">'.$row["comm_count"].' предложение</a>' : '<a class="public_blue" href="'.getFriendlyURL("blog", $row['thread_id']).'">'.$row["comm_count"].' предложения</a>'  )   )?><br /><br />
<?
            }
            else {
?>
            <br /><?=((!$row["offers_count"] || $row["offers_count"] % 10==0 || $row["offers_count"] % 10 >4 || ($row["offers_count"] >4 &&  $row["offers_count"]<21)) ?  '<a class="public_blue" href="'.$blink.'">'.$row["offers_count"].' предложений</a>' : (($row["offers_count"] % 10 == 1 || $row["comm_count"]==1) ?  '<a class="public_blue" href="'.$blink.'">'.$row["offers_count"].' предложение</a>' : '<a class="public_blue" href="'.$blink.'">'.$row["offers_count"].' предложения</a>'  )   )?><br /><br />
<?
            }
            
            
            ?>
            <br />
            <div class="fl2_offer_meta">Прошло времени с момента публикации: 
                <?=ago_pub_x(strtotimeEx($row['create_date']))?><br />
                Автор: <a href="/users/<?=$user->login?>"><? print $user->uname." "; print $user->usurname; ?> [<?=$user->login?>]</a><br />
                Раздел: <?=projects::getSpecsStr($row['id'],' / ', ', ');?>
                <? /* $category=$proj_groups_by_id[$row['category']]; print $category;*/ ?>
            </div>
            <? if ($row['pro_only']=='t') {?><br />
                <span  class="fl2_offer_meta2" style="background-color:#fff7ee;">Отвечать на проект могут только пользователи с аккаунтом <a href="/payed/"><img style="background:none" src="/images/icons/f-pro.png" alt="" class="pro" /></a></span>
                <? }?>
                <div class="fl2_comments_link">
                    <div style="padding:12px 0px 0px 0px;"></div>
                </div>
            
            </div>

            <? if($row['exec_login']) { ?>
            <div class="b-fon b-fon_padbot_15">
                    <b class="b-fon__b1"></b>
                    <b class="b-fon__b2"></b>
                    <div class="b-fon__body b-fon__body_pad_10">
                        <span class="b-fon__txt b-fon__txt_float_right b-fon__txt_fontsize_11">Рейтинг: <?= round($row['exec_rating'],1)?></span>
                        <span class="b-fon__txt b-fon__txt_bold b-fon__txt_fontsize_13">
                            <?php if($row['kind']==2 || $row['kind']==7) { ?>
                                Победитель определен:
                            <?php } else { ?>
                                Исполнитель определен:
                            <?php } ?>
                        </span>
                        <div class="b-username b-username_bold b-username_inline">
                            <a class="b-username__link" href="/users/<?=$row['exec_login']?>/"><?=($row['exec_name']." ".$row['exec_surname'])?></a> <span class="b-username__login b-username__login_color_fd6c30">[<a class="b-username__link" href="/users/<?=$row['exec_login']?>"><?=$row['exec_login']?></a>]</span> <?=view_mark_user($row, "exec_"); ?>
                        </div>
                        <div class="i-opinion i-opinion_padtop_10">
                            <span class="b-opinion">
                                <span class="b-opinion__txt"><a class="b-opinion__link  b-opinion__link_color_4e" href="/users/<?=$row['exec_login']?>/opinions/">Отзывы пользователей</a></span>
                                <span class="b-opinion__txt b-opinion__txt_color_6db335"><? if(($row['sbr_opi_plus'] + $row['ops_all_plus']) > 0) { ?><a class="b-opinion__link b-opinion__link_decoration_no b-opinion__link_color_6db335" href="/users/<?=$row['exec_login']?>/opinions/?sort=1#op_head">+</a><? } ?><a class="b-opinion__link b-opinion__link_color_6db335" href="/users/<?=$row['exec_login']?>/opinions/?sort=1#op_head"><?= (int) ($row['sbr_opi_plus'] + $row['ops_all_plus'])?></a></span>
                                <span class="b-opinion__txt b-opinion__txt_color_4e"><a class="b-opinion__link b-opinion__link_color_4e" href="/users/<?=$exec_info['login']?>/opinions/?sort=2#op_head"><?=(int) ($row['sbr_opi_null'] + $row['ops_all_null']) ?></a></span>
                                <span class="b-opinion__txt b-opinion__txt_color_c10600"><? if(($row['sbr_opi_minus'] + $row['ops_all_minus']) > 0) { ?><a class="b-opinion__link b-opinion__link_decoration_no b-opinion__link_color_c10600" href="/users/<?=$row['exec_login']?>/opinions/?sort=3#op_head">&minus;</a><? } //if?><a class="b-opinion__link b-opinion__link_color_c10600" href="/users/<?=$row['exec_login']?>/opinions/?sort=3#op_head"><?=(int) ($row['sbr_opi_minus'] + $row['ops_all_minus']) ?></a></span>
                            </span>
                        </div>
                    </div>
                    <b class="b-fon__b2"></b>
                    <b class="b-fon__b1"></b>
                </div>
            
            <? } ?>
         </td> 
         </tr>  
         


        <?
        if ($pn > $pj+1)
        {

        ?>

            <?
                }
        }

?>

