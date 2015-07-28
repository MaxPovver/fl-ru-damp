<?
    /**
     * Шаблон катрочки проекта
     */

     $projectObject = new_projects::initData($project);

?>

            <div class="b-layout b-layout_overflow_hidden b-layout_margbot_10">
				<?php 
                 $cfile = new cfile($project["logo_id"]);
                 if ( $cfile->id ) {
                     if ( trim($project["link"]) ) {
                 ?>
                         <a target="_blank" rel="nofollow" href="<?=$project["link"]?>" class="b-post__link">
                             <img alt="" src="<?= WDCPREFIX."/".$cfile->path."/".$cfile->name ?>" class="b-post__pic b-post__pic_float_right b-post__pic_clear_right b-post__pic_margleft_10">
                         </a>
                  <?
                      } else {
                  ?>
                         <img alt="" src="<?= WDCPREFIX."/".$cfile->path."/".$cfile->name ?>" class="b-post__pic b-post__pic_float_right b-post__pic_clear_right b-post__pic_margleft_10">
                  <?
                      }
                  }
                  ?>
                
                
          		
				<? if ($project['prefer_sbr'] === 't' && $project['kind'] == 7) {
                    // этот шаблон используется для предпросмотра проекта/конкурса при публикации, поэтому проверяем конкурс или проект 
                 ?>
                    <div class="b-layout__txt_padbot_20 b-layout__txt"><?= ($project['kind'] == 7) ? 'Выплата вознаграждения через' : 'Предпочитаю оплату работы через' ?> <a href="/promo/bezopasnaya-sdelka/" target="_blank" class="b-layout__link">Безопасную Сделку</a> <?= view_sbr_shield('', 'b-icon_top_2') ?></div>
                <? } ?>
				
				
				<?php $can_change_prj = hasPermissions("projects"); ?>
                
          			
          			
                 
                 
                 
                        <script type="text/javascript">
                            function updateFavorites(elm, pid) {
                                var req = new Request({
                                    url: '/projects/index.php?p=changePrjFavState&pid='+pid,
                                    onSuccess: function(data) {
                                        if (data == 2) {
                                            $(elm).removeClass('b-icon__star_yellow');
                                            $(elm).set('title', 'Добавить в Избранное');
                                        } else if (data == 1) {
                                            $(elm).addClass('b-icon__star_yellow');
                                            $(elm).set('title', 'Убрать из Избранного');
                                        }
                                    }
                                }).post('action=changePrjFavState&u_token_key=<?=$_SESSION['user_token'];?>');
                            }
                        </script>
            			<?php if ($project['name']) {
            				$sBox1 = '';
            				if (intval($project['sbr_id'])) $sBox1 .= "<a href=\"/".sbr::NEW_TEMPLATE_SBR."/".($project['sbr_emp_id']==$uid||$project['sbr_frl_id']==$uid ? "?id={$project['sbr_id']}" : '')."\" title=\"Безопасная Сделка\"><img src=\"/images/shield_sm.gif\" alt=\"Безопасная Сделка\" class=\"sbr_p\" /></a>";
            				if ($project['ico_closed'] == "t")  $sBox1 .= "<img src=\"/images/ico_closed.gif\" alt=\"Проект закрыт\" class=\"sbr_p\"/>";
                            $sTitle = $project['moderator_status'] === '0' && $project['kind'] != 4 && $project['is_pro'] != 't' ? $stop_words->replace($project['name']) : $project['name'];
            				?>
																												<?
            			} ?>

                
            			<div id="projectp<?=$project['id']?>" class="b-layout__txt b-layout__txt_padbot_20">
                            <?php $sDescr = $project['moderator_status'] === '0' && $project['kind'] != 4 && $project['is_pro'] != 't' ? $stop_words->replace($project['descr']) : $project['descr']; ?>
                            <?=reformat($sDescr, 50, 0, 0, 1);?>
                        </div>
                <?
                if ($project['attach']) {
                	$str = viewattachLeft( $project['login'], $project['attach'], "upload", $file, 1000, 600, 307200, $project['attach'], 0, 0, 1 );
                	print("<tr><td>&nbsp;</td><td><br>".$str."<br></td></tr>");
                } elseif ( isset($project_attach) && is_array($project_attach) ) {  $project_attach = array_reverse($project_attach);
                    ?>
                    <div class="b-layout b-layout_padbot_15">
                    <?php
                    $nn = 1;
                	foreach ( $project_attach as $attach )
                	{
                		$str = viewattachLeft( NULL, $attach["name"], $attach['path'], $file, 0, 0, 0, 0, 0, 0, $nn );
                		echo '<div class = "b-layout__txt b-layout__txt_padbot_5">', $str, '</div>';
                        $nn++;
                	}
                	?>
                    </div>
                <?php } //elseif ?>

                <? if(video_validate($project['videolnk'])) { ?>
					<?=show_video($project['id'], video_validate($project['videolnk']) );?>
                <? } ?>

                <?php if($project['kind'] != 9): ?>         
                <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_bold">Разделы:</div>
                <div class="b-layout__txt b-layout__txt_fontsize_11 <?php if (!($project['ico_payed']=='t' || $project['is_upped'] == 't'||$project['kind'] == 4)) {?>b-layout__txt_padbot_20<?php } ?>"><?=$isPreview ? $project['spec_txt'] : projects::_getSpecsStr($project_specs, ' / ', ', ', true);?></div>
                
		<?php if ($project['ico_payed']=='t' || $project['is_upped'] == 't'||$project['kind'] == 4){?>
                <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padbot_20">
                    <?php if ($project['ico_payed']=='t' || $project['is_upped'] == 't'){?>Платный проект <?php if ($project['kind'] == 4) { ?>&mdash; в<?php } ?><?php } //if?><?php
                     if ($project['kind'] == 4) { ?><?php if (!($project['ico_payed']=='t' || $project['is_upped'] == 't')){?>В<?php } //if?>акансия <?= (($project['country'])?" (".$project['country_name'].(($project['city'])?", ".$project['city_name']:"").")":"") ?><?php } //if?>
                </div>
                <?php } //if?>
                <?php endif; ?>    	
                
                <div class="b-layout__txt b-layout__txt_padbot_30">
                    <div class="b-layout__txt b-layout__txt_bold b-layout__txt_fontsize_11">Опубликован:</div>
                    <div class="b-layout__txt b-layout__txt_fontsize_11">
                        <?= dateFormat("d.m.Y | H:i", $project['create_date']) ?>
                        <?= (($project['post_date'] !== $project['create_date']) ? dateFormat("[поднят: d.m.Y | H:i]", $project['post_date']) : "") ?>  
                        <?= (($project['edit_date']) ? dateFormat("[последние изменения: d.m.Y | H:i]", $project['edit_date']) : "") ?>  
                    </div>
                    <?php if($project['kind'] != 9): ?> 
                    <?php
                    if (($uid && ($project["user_id"] == $uid) && ($project['is_blocked'] != 't')) || hasPermissions('projects')) {
                        ?>
                        <?php if ($project["closed"] == "f") { ?>
                            <div class="b-layout__txt b-layout__txt_fontsize_11">
                                В списке проектов <? /* "<?=GetKind($project["kind"])?>" */ ?> <a id="pos_link_<?= $project['id'] ?>" class="b-layout__link" href="#" onclick="xajax_getStatProject(<?= $project['id'] ?>, '<?= $project['payed_to'] ?>', '<?= $project['now'] ?>', '<?= $project['payed'] ?>', '<?= $project['post_date'] ?>', '<?= $project['kind'] ?>', '<?= $project['comm_count'] ?>', '<?= $project['offers_count'] ?>'); return false;">Подробнее&hellip;</a>
                                <span id="prj_pos_<?= $project['id'] ?>" class="b-layout__txt b-layout__txt_fontsize_11"></span>
                            </div>
                        <?php }//if?>
                    <?php }//if ?>
                    <?php endif; ?> 
                </div>
           </div>
                   
                <?php 
                if (($uid && ($project["user_id"] == $uid) && ($project['is_blocked'] != 't')) || hasPermissions('projects')) { 
                ?>
            	
                <?php
                    if($projectObject->isNotPayedVacancy() && !$projectObject->isClosed() && !(hasPermissions('projects') && $project['login']!=$_SESSION["login"])):
                ?>
                <div class="b-fon b-fon_clear_both b-fon_margbot_20">
                    <div class="b-fon__body b-fon__body_padtop_10 b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffebbf">
                        <span class="b-icon b-icon_sbr_oattent b-icon_margleft_-25"></span>
                        Ваш проект перенесен в раздел Вакансии. <br/>
                        Пожалуйста, оплатите его размещение, чтобы видеть отклики фрилансеров и иметь возможность выбрать Исполнителя.
                    </div>
                </div>
                <?php
                    endif;
                ?>

                <div class="b-buttons b-buttons_padbot_30">
                    
                   <?php if($project['kind'] != 9 && $project['closed'] != "t" && ($project["user_id"] == $uid)): ?>
                        <?php if($projectObject->isNotPayedVacancy()): ?>
                            <a class="b-button b-button_flat b-button_flat_green __project_close_hide" href="/public/?step=1&public=<?= $project['id'] ?>&popup=1">
                                Оплатить размещение за <?=$projectObject->getProjectInOfficePrice($projectObject->isOwnerPro())?> руб.
                            </a>                            
                        <?php else: ?>
                            <a class="b-button b-button_flat b-button_flat_green __project_close_hide" href="/public/?step=1&public=<?= $project['id'] ?>">
                                Получить больше предложений
                            </a>
                        <?php endif; ?>
                        &#160;&#160;&#160;
                   <?php endif; ?>                
                    
                   <span class="b-layout__txt">
                        <?php if (hasPermissions('projects') && $project['login']!=$_SESSION["login"]) { ?>
<?php 
                            if($projectObject->isNotPayedVacancy()):
?>
                            <span class="b-txt b-txt_bold b-txt_color_de2c2c b-txt_padright_20">Вакансия еще не оплачена</span>
<?php 
                            endif; 
?>   
                        <script type="text/javascript">
                            var PROJECT_BANNED_PID = 'p<?= $project['id']?>';
                            var PROJECT_BANNED_URI = '<?=$GLOBALS['host']?>/projects/?pid=<?=$project['id']?>';
                            var PROJECT_BANNED_NAME = "<?=htmlspecialchars($project['name'])?>";
                        </script>
                            <?php if ( $project['warn']<3 && !$project['is_banned'] && !$project['ban_where'] ) { ?>
                                <span class='warnlink-<?= $project['user_id']?>'><a class='b-layout__link b-layout__link_dot_c10600' href='javascript: void(0);' onclick='banned.warnUser(<?= $project['user_id']?>, 0, "projects", "p<?= $project['id']?>", 0); return false;'>Сделать предупреждение (<span class='warncount-<?= $project['user_id']?>'><?= ($project['warn'] ? $project['warn'] : 0);?></span>)</a></span>&#160;&#160;
                            <?php } else /*if (!$project['is_banned'])*/ { 
                                $sBanTitle = (!$project['is_banned'] && !$project['ban_where']) ? 'Забанить!' : 'Разбанить';
                                ?>
                               <span class='warnlink-<?= $project['user_id']?>'><a class='b-layout__link b-layout__link_dot_c10600' href="javascript:void(0);" onclick="banned.userBan(<?=$project['user_id']?>, 'p<?= $project['id']?>',0)"><?=$sBanTitle?></a></span>&#160;&#160;
                            <?php }// elseif ?>
                               <span id="project-button-<?=$project['id']?>"><a class='b-layout__link b-layout__link_dot_c10600' href="javascript:;" onclick="banned.<?=($project['is_blocked']? 'unblockedProject': 'blockedProject')?>(<?=$project['id']?>)"><?=($project['is_blocked']? 'Разблокировать': 'Заблокировать')?></a></span>&#160;&#160;
<?php
                                if ($projectObject->isAllowMovedToVacancy()): 
?>
                               <a onclick="return confirm('Сделать вакансией?');" class='b-layout__link b-layout__link_dot_c10600' href="/projects/makevacancy/?id=<?=$project['id']?>">Сделать вакансией</a>&#160;&#160;
<?php
                                endif;
?>
                               <a class='b-layout__link' href="/public/?step=1&public=<?= $project['id']?>&red=<?= rawurlencode($_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']);?>" onClick="popupQEditPrjShow(<?=$project['id']?>, event); return false;">Редактировать</a>&#160;&#160;
                        <?php } else if ($uid && ($project["user_id"] == $uid) && ($project['is_blocked'] != 't')) { //if?>
                               <?php if($project['kind'] != 9): ?>
                                    <?php if($project["closed"] == 't') {?>
                                        <a class='b-layout__link __project_close_hide' href="/projects/index.php?action=prj_close&pid=<?=$project["id"]?>">Публиковать еще раз</a>&#160;&#160;
                                    <?php } else { //if ?>
                                        <a class='b-layout__link __project_close_hide' href="/projects/index.php?action=prj_close&pid=<?=$project["id"]?>">Снять с публикации</a>&#160;&#160;
                                    <?php } //else?>
                               <?php endif; ?>
                               
                               <?php if(!$projectObject->isNotPayedVacancy()): ?>         
                               <a class='b-layout__link __project_close_hide' href="/public/?step=1&public=<?=$project["id"]?>&red=<?=rawurlencode("/users/" . $project["login"] . "/setup/projects/")?>">
                                   Редактировать
                               </a>
                               <?php endif; ?>
                        <?php } ?>
                    </span>
                </div>
            	    
                <?php } ?>
                
                <?php if (!(hasPermissions('projects') && $project['login']!=$_SESSION["login"])) { ?>
                    <?php if((is_emp()&&($project["user_id"] != $uid))||(($project['exec_id'] && $exec_info)&&(!get_uid(false)))) {?>
                         <div class="b-buttons b-buttons_padbot_30">
                           <a class="b-button b-button_flat b-button_flat_green" href="<?=$answer_button_href?>"><?=$answer_button_text?></a>&#160;&#160;&#160;
                           <span class="b-layout__txt b-layout__txt_valign_middle">
                                <a class='b-layout__link' href="<?php if ($project['kind'] == 4) { ?>/projects/?kind=4<?php } else { ?>/projects/<?php } ?>">Посмотреть другие <?php if ($project['kind'] == 4) { ?>вакансии<?php } else { ?>проекты<?php } ?></a>
                           </span>
                         </div>
            	    <?php }//if ?>
                    <?php if ($obj_offer->IsPrjOfferExists($project['id'], get_uid(false))){ ?>
                         <div class="b-buttons b-buttons_padbot_30">
                                <?php if($project['kind'] != 9): ?>
                                <div class="b-layout__txt b-layout__txt_padtop_10 b-layout__txt_float_right i-shadow"><?php
                                    $templates = array(
                                        uploader::getTemplate('uploader', 'project_abuse/'),
                                        uploader::getTemplate('uploader.file', 'project_abuse/'),
                                        uploader::getTemplate('uploader.popup', 'project_abuse/'),
                                    );
                                    uploader::init(array(
                                        'abuse_uploader' => array(
                                            'umask'  => uploader::umask('prj_abuse'),
                                            'validation' => array('allowedExtensions' => array('jpg', 'gif', 'png', 'jpeg'), 'restrictedExtensions' => array(), 'sizeLimit' => tmp_project::MAX_FILE_SIZE),
                                            'text'   => array('uploadButton' => iconv('cp1251', 'utf8', 'Прикрепить файлы'))
                                        )
                                    ), $templates);
                                    $complain = true;
                                    $obj_project->IsHaveComplain($project['id'],get_uid(), $complain);
                                    
                                    $is_project_complain_sent = $obj_project->isComplainSent($project['id']);
                                    
                                    require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_complains.php");
                                    $complainTypes = projects_complains::getTypes();
                                    ?><? include($_SERVER['DOCUMENT_ROOT'] . "/projects/tpl.abuse.php"); 
                                    ?>
                                    
                                    <span class="b-layout__txt_color_c10600">Пожаловаться на проект: 
                                    &nbsp;&nbsp;&nbsp;<a class="b-layout__link b-layout__link_dot_c10600 abuse-employer-project-link" href="javascript:void(0)">Работодателю</a>
                                    &nbsp;/&nbsp; <a class="b-layout__link b-layout__link_dot_c10600 abuse-moderator-project-link" href="javascript:void(0)">Модератору</a>
                                    </span> 
                                    &#160; <img class="b-layout__pic b-layout__pic_absolute b-layout__txt_hide" id="project_abuse_success" style="top:-8px; left:400px" src="/images/thanks.png" width="80" height="36">
                                </div>
                                <?php endif; ?>
                                <a class='b-layout__link' href="<?php if ($project['kind'] == 4) { ?>/projects/?kind=4<?php } else { ?>/projects/<?php } ?>">Посмотреть другие <?php if ($project['kind'] == 4) { ?>вакансии<?php } else { ?>проекты<?php } ?></a>
                         </div>
                    <?php } else { ?>
			 <?php $sTitle = $project['moderator_status'] === '0' && $project['kind'] != 4 && $project['is_pro'] != 't' ? $stop_words->replace($project['name'], 'plain', false) : $project['name']; ?>
                         <?php if ( !is_emp()&&((!($project['exec_id'] && $exec_info)))) { ?>
                             <div class="b-buttons b-buttons_padbot_30">
				  <?php if(get_uid(false) && ($project['kind'] != 9)): ?>
                                    <div class="b-layout__txt b-layout__txt_padtop_10 b-layout__txt_float_right i-shadow"><?php
                                        $templates = array(
                                            uploader::getTemplate('uploader', 'project_abuse/'),
                                            uploader::getTemplate('uploader.file', 'project_abuse/'),
                                            uploader::getTemplate('uploader.popup', 'project_abuse/'),
                                        );
                                        uploader::init(array(
                                            'abuse_uploader' => array(
                                                'umask'  => uploader::umask('prj_abuse'),
                                                'validation' => array('allowedExtensions' => array('jpg', 'gif', 'png', 'jpeg'), 'restrictedExtensions' => array(), 'sizeLimit' => tmp_project::MAX_FILE_SIZE),
                                                'text'   => array('uploadButton' => iconv('cp1251', 'utf8', 'Прикрепить файлы'))
                                            )
                                        ), $templates);
                                        $complain = true;
                                        $obj_project->IsHaveComplain($project['id'],get_uid(), $complain);
                                        
                                        $is_project_complain_sent = $obj_project->isComplainSent($project['id']);
                                        
                                        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_complains.php");
                                        $complainTypes = projects_complains::getTypes();
                                        ?><? include($_SERVER['DOCUMENT_ROOT'] . "/projects/tpl.abuse.php"); 
                                        ?>
                                        
                                        <span class="b-layout__txt_color_c10600">Пожаловаться на проект: 
                                        &nbsp;&nbsp;&nbsp;<a class="b-layout__link b-layout__link_dot_c10600 abuse-employer-project-link" href="javascript:void(0)">Работодателю</a>
                                        &nbsp;/&nbsp; <a class="b-layout__link b-layout__link_dot_c10600 abuse-moderator-project-link" href="javascript:void(0)">Модератору</a>
                                        </span> 
                                        &#160; <img class="b-layout__pic b-layout__pic_absolute b-layout__txt_hide" id="project_abuse_success" style="top:-8px; left:400px" src="/images/thanks.png" width="80" height="36">
                                        
                                         
                                    </div>
                                 <?php endif; ?>
                                
                                 <?php if($project['pro_only'] == 't' && !$is_pro || $project['verify_only'] == 't' && !$is_verify || (@$answers->offers < 1 && !$is_pro)) {?>
                                    <a data-popup="project_answer_popup" data-url="<?=$answer_button_href?>" class="b-button b-button_flat b-button_flat_green" href="<?=$answer_button_href?>">
                                        Ответить на <?php if ($project['kind'] == 4) { ?>вакансию<?php } else { ?>проект<?php } ?>
                                    </a>&#160;&#160;&#160;
                                    <?php if(get_uid(false) > 0) echo projects_helper::renderAnswerPopup(array('project' => $project,'is_pro' => $is_pro,'is_verify' => $is_verify)); ?>
                                 <?php } ?>
                                 <span class="b-layout__txt b-layout__txt_valign_middle">
                                    <?php if(!get_uid(false)) {?>
                                     <a class='b-layout__link' href="/public/?step=1&kind=<?=$project['kind']?>"><?=$answer_button_text?></a>&#160;&#160;
                                    <?php }//if ?>
                                    <a class='b-layout__link' href="<?php if ($project['kind'] == 4) { ?>/projects/?kind=4<?php } else { ?>/projects/<?php } ?>">Посмотреть другие <?php if ($project['kind'] == 4) { ?>вакансии<?php } else { ?>проекты<?php } ?></a>
                                 </span>
                             </div>
						 <?php }//if?>
                    <?php } ?>
                <?php } ?>
                