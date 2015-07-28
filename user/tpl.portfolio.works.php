<?php
    
    $is_adm = hasPermissions('users');
    
?>
<table width="100%" cellspacing="0" cellpadding="0"  >
    <tr>
        <td style="width:14px" >&nbsp;</td>
        <? // если $iWantPro == true значит находимся в режиме показа ПРО для НЕПРО
        if( ( ( $pinfo['gr_prevs'] == 't' && $user->is_pro =='t' ) || $iWantPro ) && $work[0]['id'] != null ) {?>
        <td>
            <?php if($is_owner) {?>
            <div class="b-layout__txt b-layout__txt_float_right b-layout__txt_lineheight_1 b-layout__txt_margleft_-15 b-layout__txt_nowrap">
                <span class="b-layout__txt b-layout__txt_fontsize_22 b-layout__txt_color_808080 b-layout__txt_valign_middle">+</span>
                <a onclick="portfolio.editContent('openEditWork', '<?= $_SESSION['uid']?>', {'prof_id' : '<?= $prof_id;?>' })" href="javascript:void(0)" class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_bold b-layout__link_fontsize_11">Добавить работу</a>
            </div>
            <?php }//if?>
            <?php foreach($prjs as $in=>$blocks) { ?>
            
            <table class="preview-work b-layout__table b-layout__table_ipad">
                    <tr class="b-layout__tr">
                    <?php $k=0; foreach($blocks as $prj) { 
                        $k++;
                        $txt_cost = view_cost2($prj['prj_cost'], '', '', false, $prj['prj_cost_type']); 
                        $txt_time = view_time($prj['prj_time_value'], $prj['prj_time_type']);
                        $is_txt_time = ($txt_cost != '' && $txt_time != '');
                    ?>
                        <td class="b-layout__td b-layout__td_width_33ps b-layout__td_ipad b-layout__td_block_iphone b-layout__td_width_full_iphone"
                            <?php if(!$prj['prj_prev_type']) echo ' itemscope itemtype="http://schema.org/ImageObject"' ?>>
                            <div class="h-work">
                                <strong><a itemprop="name" class="blue" target="_blank" href="/users/<?= $user->login?>/viewproj.php?prjid=<?=$prj['id']?>"><?= (reformat($prj['name'], 25, 0, 1))?></a></strong>
                            </div>
                            <div class="b-work b-work_bg_ff">
                                <?php 
                                if($prj['prj_prev_type']) {
                                    $sDescr = /*$prj['moderator_status'] === '0' ? $stop_words->replace($prj['descr']) :*/ $prj['descr'];
                                    print("<p style='padding-bottom:7px'>".reformat2($sDescr,25,0,1)."</p>"); // Для текста нужен свой блок <p> с отступом вконце @todo
                                } else { //if 
                                    $sName = /*$prj['moderator_status'] === '0' ? $stop_words->replace($prj['name'], 'plain', false) :*/ $prj['name'];
                                    ?>
                                <a title="<?=htmlspecialchars(htmlspecialchars_decode($sName, ENT_QUOTES))?>" 
                                   class="blue" 
                                   target="_blank" 
                                   href="/users/<?=$user->login?>/viewproj.php?prjid=<?=$prj['id']?>" 
                                   style="text-decoration:none">
                                    <?=view_preview2($user->login, $prj['prev_pict'], "upload", 'center', false, false, htmlspecialchars($sName), 200)?>
                                </a>
                                <span class="b-layout_hide" itemprop="description"><?=SeoTags::getInstance()->getImageDescription() ?></span>
                                <?php } //else ?>
                                <p><span class="money"><?= $txt_cost?></span><?= ($is_txt_time ? ", ":"") . ($txt_time != ''?$txt_time:"")?></p>

                                <div id="portfolio-block-<?= $prj['id'] ?>" style="display: <?= ($prj['is_blocked'] == 't' ? 'block': 'none') ?>">
                                    <? if ($prj['is_blocked'] == 't') { ?>
                                    <div class='b-fon b-fon_clear_both b-fon_bg_ff6d2d b-fon_padtop_10 b-fon_padbot_10'>
                                        <b class="b-fon__b1"></b>
                                        <b class="b-fon__b2"></b>
                                        <div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_fontsize_13">
                                            <span class="b-fon__attent"></span>
                                            <div class="b-fon__txt b-fon__txt_margleft_20">
                                                    <span class="b-fon__txt_bold">Работа заблокирована</span>. <?= reformat($prj['blocked_reason'], 24, 0, 0, 1, 24) ?> <a class='b-fon__link' href='https://feedback.fl.ru/'>Служба поддержки</a>
                                                    <div class='b-fon__txt'><?php if ($is_adm) { ?><?= ($prj['admin_login'] ? "Заблокировал: <a class='b-fon__link' href='/users/{$prj['admin_login']}'>{$prj['admin_uname']} {$prj['admin_usurname']} [{$prj['admin_login']}]</a><br />": '') ?><?php } ?>
                                                    Дата блокировки: <?= dateFormat('d.m.Y H:i', $prj['blocked_time']) ?></div>
                                            </div>
                                        </div>
                                        <b class="b-fon__b2"></b>
                                        <b class="b-fon__b1"></b>
                                    </div>
                                    <? } ?>
                                </div>

                                <?php if ($is_adm && !$is_owner) { ?>
                                <div id="portfolio-button-<?= $prj['id'] ?>" style="clear:left;">
                                    <a class="admn" href="javascript:void(0);" onclick="banned.<?=($prj['is_blocked']=='t'? 'unblockedPortfolio': 'blockedPortfolio')?>(<?=$prj['id']?>)"><?= $prj['is_blocked']=='f' ? "Заблокировать" : "Разблокировать"; ?></a><br/>
                                    <a class="admn" href="javascript:void(0);" onclick="adm_edit_content.editContent('admEditPortfolio', '<?=$prj['id']?>_0', 0, '')">Редактировать</a>
                                </div>
                                <?php } elseif($is_owner) { ?>
                                <div id="portfolio-button-<?= $prj['id'] ?>" style="clear:left;">
                                    <a class="admn" href="javascript:void(0);" onclick="portfolio.editContent('openEditWork', '<?= $_SESSION['uid']?>', {'id' : '<?=$prj['id']?>', 'prof_id' : '<?= $prof_id;?>' })">Редактировать</a>
                                </div>
                                <?php } ?>
                             </div>
                        </td>
                    <?php } //foreach ?>
                    <?php for($ii=$k;$ii<3;$ii++) { ?>
                        <td class="b-layout__td b-layout__td_width_33ps b-layout__td_ipad b-layout__td_block_iphone b-layout__td_width_full_iphone">&nbsp;</td>
                    <?php }?>
                    </tr>
            </table>    
            <?php } //foreach ?>
        </td>
        <?php } else { // if?>
        <td style="vertical-align:top;padding:6px 0px 6px 0px;">    
            <table class="portfolio-list" width="100%" cellspacing="0" cellpadding="3">
                <?php if($is_owner && !$add_work[$prof_id]) { $add_work[$prof_id] = true; ?>
                <tr>
                    <td class="odd"></td>
                    <td class="even"></td>
                    <td class="odd">
                        <div class="b-layout__txt b-layout__txt_lineheight_1 b-layout__txt_margleft_-15 b-layout__txt_nowrap">
                            <span class="b-layout__txt b-layout__txt_fontsize_22 b-layout__txt_color_808080 b-layout__txt_valign_middle">+</span>
                            <a class="b-layout__link b-layout__link_bordbot_dot_0f71c8 b-layout__link_bold b-layout__link_fontsize_11" 
                               href="javascript:void(0)"
                               onclick="xajax_openEditWork(null, {id:0, prof_id:<?= $prof_id?>});">Добавить работу</a>
                        </div>
                    </td>
                </tr>
                <?php } //if?>
                <?php if(isset($pp_noblocks[$prof_id])) {
                foreach($pp_noblocks[$prof_id] as $i=>$prj) {
                    if($prj['id'] === null) continue;
                    $sName = /*$prj['moderator_status'] === '0' ? $stop_words->replace($prj['name'], 'plain') :*/ $prj['name'];
                    ?>
                <tr>
                    <td class="odd"><?=($i+1)?>.</td>
                    <td class="even">
                        <a href="/users/<?=$user->login?>/viewproj.php?prjid=<?=$prj['id']?>" target="_blank" class="blue"><?=$sName?></a><? $txt_cost = view_cost2($prj['prj_cost'], '', '', false, $prj['prj_cost_type']); $txt_time = view_time($prj['prj_time_value'], $prj['prj_time_type']);?> <span class="money" style="margin-left:8px;"><?=$txt_cost?></span><? if ($txt_cost != '' && $txt_time != '') { ?>, <? } ?><?=$txt_time?> &nbsp;
                        <div id="portfolio-block-<?= $prj['id'] ?>" style="display: <?= ($prj['is_blocked'] == 't' ? 'block': 'none') ?>">
                            <? if ($prj['is_blocked'] == 't') { ?>
                            <div class='b-fon b-fon_clear_both b-fon_bg_ff6d2d b-fon_padtop_10 b-fon_padbot_10'>
                                <b class="b-fon__b1"></b>
                                <b class="b-fon__b2"></b>
                                <div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_fontsize_13">
                                    <span class="b-fon__attent"></span>
                                    <div class="b-fon__txt b-fon__txt_margleft_20">
                                            <span class="b-fon__txt_bold">Работа заблокирована</span>. <?= reformat($prj['blocked_reason'], 24, 0, 0, 1, 24) ?> <a class='b-fon__link' href='https://feedback.fl.ru/'>Служба поддержки</a>
                                            <div class='b-fon__txt'><?php if ($is_adm) { ?><?= ($prj['admin_login'] ? "Заблокировал: <a class='b-fon__link' href='/users/{$prj['admin_login']}'>{$prj['admin_uname']} {$prj['admin_usurname']} [{$prj['admin_login']}]</a><br />": '') ?><?php } ?>
                                            Дата блокировки: <?= dateFormat('d.m.Y H:i', $prj['blocked_time']) ?></div>
                                    </div>
                                </div>
                                <b class="b-fon__b2"></b>
                                <b class="b-fon__b1"></b>
                            </div>
                            <? } ?>
                        </div>
                    </td>
                    <td class="odd">
                        <?php if ($is_adm && !$is_owner ) { ?>
                        <div id="portfolio-button-<?= $prj['id'] ?>">
                            <a class="admn" href="javascript:void(0);" onclick="banned.<?=($prj['is_blocked']=='t'? 'unblockedPortfolio': 'blockedPortfolio')?>(<?=$prj['id']?>)"><?= $prj['is_blocked']=='f' ? "Заблокировать" : "Разблокировать"; ?></a><br/>
                            <a class="admn" href="javascript:void(0);" onclick="adm_edit_content.editContent('admEditPortfolio', '<?=$prj['id']?>_0', 0, '')">Редактировать</a>
                        </div>
                        <?php } elseif($is_owner) { ?>
                        <div id="portfolio-button-<?= $prj['id'] ?>">
                            <a class="admn" href="javascript:void(0);" onclick="portfolio.editContent('openEditWork', '<?= $_SESSION['uid']?>', {'id' : '<?=$prj['id']?>', 'prof_id' : '<?= $prof_id;?>' })">Редактировать</a>
                        </div>
                        <?php } ?>
                    </td>
                </tr>
                <?php } //foreach?>
                <?php } //if?>
            </table>	
        </td>
        <?php } // else?>
        <td style="width:14px">&nbsp;</td>
    </tr>
</table>