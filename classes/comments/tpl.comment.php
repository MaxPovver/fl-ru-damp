<? if(($is_new || $data['is_new']) && $data['author_login'] != $_SESSION['login']) { ?>
    <script type="text/javascript">
        new_comments[new_comments.length] = <?=$data['id']?>;
    </script>
    <a name="unread"></a>
    <? } ?>
    <?php
    if($data['author_is_banned']){
                    $show_banned_text = false;
                    if($data['is_permission']){
                        $show_banned_text = true; 
                    }else{
                        $data['msgtext'] = 'Ответ от заблокированного пользователя';
                    }
                }
    
    ?>
    
<!--    <div class="cl-li-in cl-li<?= !$data['level'] ? '-first' : '' ?> <?=$is_new ? 'cl-li-new' : ''?>">-->
        <a name="c_<?=$data['id']?>"></a>
        <? if($msg['hiddenRating']) {?>
            <a href="javascript:void(0);" onclick="$(this).getParent('li.cl-li').removeClass('cl-li-hidden'); $(this).setStyle('display', 'none'); return false;" class="lnk-dot-666 lnk-cl-li-show">Показать комментарий</a>
        <? }//if?>
        <div class="cl-li-info">
            <ul class="cl-i">
                <? if($this->enableRating && $data['deleted'] === NULL) { ?>
                <li class="cl-i-rate">
                    <div class="post-rate" id="rate_<?= $data['id'] ?>">
                        <?php if($data['author'] == get_uid(false) || $this->_options['readonly']) { ?>
                        <a href="javascript:void(0)"><img src="/images/btn-drate-dis.png" alt="" /></a>
                        <span class="post-rate-val <?= $rating_class ?>"><?= ($data['rating'] > 0 ? '+' : '') . (int)$data['rating'] ?></span>
                        <a href="javascript:void(0)"><img src="/images/btn-urate-dis.png" alt="" /></a>
                        <?php } else {?>
                        <a href="javascript:void(0)" onclick="RateComment('<?= $this->_sname ?>', <?= $data['id'] ?>, -1, this)"><img src="/images/btn-drate<?= ( $data['user_rating']  == -1 || !get_uid(false)) ? '-dis' : '' ?>.png" alt="" /></a>
                        <span class="post-rate-val <?= $rating_class ?>"><?= ($data['rating'] > 0 ? '+' : '') . (int)$data['rating'] ?></span>
                        <a href="javascript:void(0)" onclick="RateComment('<?= $this->_sname ?>', <?= $data['id'] ?>, 1, this); "><img src="/images/btn-urate<?= ( $data['user_rating']  == 1 || !get_uid(false)) ? '-dis' : '' ?>.png" alt="" /></a>
                        <?php }//else?>
                    </div>
                </li>
                <? } ?>
                <li><a href="#c_<?=$data['id']?>" class="cl-anchor" title="Ссылка на данный комментарий">#</a></li>
                <li class="cl-time"><?=date('d.m.Y H:i', strtotime($data['created_time']))?></li>
                <li class="p-edited">
                    <? if($data['modified'] && $data['modified'] == $data['author']) { ?>
                    <img src="/images/ico-e-u.png" alt="Отредактировано пользователем" title="Внесены изменения <?=date('d.m.Y в H:i', strtotime($data['modified_time']))?>" />
                    <? } ?>
                    <? if($data['modified'] && $data['modified'] != $data['author'] ) {
                        $moduser = ($data['is_permission']) ? " ({$data['mod_login']} : {$data['mod_uname']} {$data['mod_usurname']})" : "";
                        ?>
                    <img src="/images/ico-e-a.png"
                            alt="<?=$data['access']['update']?>"
                            title="<?=$data['access']['update']?> <?=$moduser?>: <?=date('d.m.Y в H:i', strtotime($data['modified_time']))?>" />
                    <? } ?>
                </li>
            </ul>
            <div class="cl-arr">
                <? if($data['level']) { ?>
                <a href="#c_<?=$data['parent_id']?>" class="u-anchor">&darr;</a>
                <? } ?>
                <a href="#c_3" class="d-anchor">&darr;</a>
            </div>
            <a href="/users/<?=$data['author_login']?>" class="freelancer-name"><?=view_avatar_info($data['author_login'], $data['author_photo'], 1)?></a>
            <div class="username">
                <?=$session->view_online_status($data['author_login'])?><a href="/users/<?=$data['author_login']?>" class="<?= is_emp($data['author_role']) ? 'employer' : 'freelancer' ?>-name"><?= $data['author_uname'] . ' ' . $data['author_usurname'] . ' [' . $data['author_login'] . ']'?></a> <?=$stat?>
                <?
                $stat = ''; /*!!!is_team!!!*/
                $pro = ($data['author_is_pro'] == 't'?(is_emp($data['author_role'])?view_pro_emp():view_pro2($user[$pfx.'is_pro_test']=='t')):""); 
                $is_team = view_team_fl();

                //if ($data['author_is_pro'] == 't')
                    $stat .= ($data['author_is_team']=='t'?$is_team:$pro);
                    $stat .= "&nbsp;" ;
                ?>
                <?php if($show_banned_text){ ?>
                    <p style="text-align: left; padding-top: 10px"><strong style="background: #F2A5A5; paddong: 4px">Пользователь забанен.</strong></p>
                <?php } ?>
            </div>
        </div>
            
        <div class="cl-li-wrap">
            <div class="comment-body utxt">
            <? if($data['deleted'] === NULL && $data['is_banned'] != 't') { 
                $sMsgText = ( $this->enableWysiwyg ) ? wysiwygLinkEncode($data['msgtext']) : $data['msgtext'];
                $sMsgText = reformat( $sMsgText, $wordlength, 0, 0, 1 );
                $sMsgText = ( $this->enableWysiwyg ) ? wysiwygLinkDecode($sMsgText) : $sMsgText;
                ?>
                <?=$sMsgText?>

                <? if($data['yt'] !== NULL) { ?>
                <div class="added-video">
                    <?= show_video($data['id'], $data['yt']) ?>
                </div>
                <? } ?>

                <? global $foto_alt; ?>
                <? if($data['attach']) { ?>
                <table cellpadding="2" cellspacing="0" border="0" width="100%"><tr><td><br>
                    <? /*echo viewattachListNew ($data['attach'], 'upload')*/ 
                    if(isset($data['attach']['id'])) $data['attach'] = array($data['attach']);
                    foreach ( $data['attach'] as $attach ) {
                       $maxwidth = $this->_options['maxwidth']?$this->_options['maxwidth']-($data['level']*$this->_options['minus_level_width']):480;
                       echo '<div class = "flw_offer_attach">', viewattachLeft(null, $attach['fname'], $attach['path'], $file, 1000, $maxwidth, 307200, 0, $attach['small'] == 't' || $attach['small'] === null?1:0, 0, 0, "{$foto_alt} фото {$attach['fname']}"), '</div>';
                    }
                    ?>
                    <br></td></tr></table>
                <? } ?>
            <? } elseif($data['is_banned'] == 't') { ?>
                Пользователь был заблокирован в сообществе
            <? } else if($data['is_permission']) { ?>
                <?= ($data['deleted'] == $data['author'])?'Комментарий удален автором':$data['access']['delete'];?> <?=date('[d.m.Y в H:i]', strtotime($data['deleted_time']));?>
                <?php if(get_uid(false) != $data['author']){ ?>
                         <br/>Удалил <?=$data['mod_login_del'] ?>: (<?=$data['mod_uname_del'] ?> <?=$data['mod_usurname_del'] ?>)
               <?php }?>
               <?php if (trim($data['reason'])) {?>
                   <div style="color:#ff0000;padding-top:5px;">Причина: <?=$data['reason'] ?></div>
               <?php }?>
            <? } else { ?>
                <?= ($data['deleted'] == $data['author'])?'Комментарий удален автором':$data['access']['delete'];?> <?=date('[d.m.Y в H:i]', strtotime($data['deleted_time']));?>
                <?php if (trim($data['reason']) && $data['is_permission']) {?>
                    <div style="color:#ff0000">Причина: <?=$data['reason'] ?></div>
                <?php }?>
            <? } ?>
            </div>
            <ul class="cl-o">
                <? if($uid && $data['deleted'] === NULL && (!$data['author_is_banned'] || $data['is_permission'])) { ?>
                    <li class="cl-com first"><a href="javascript:void(0)" onclick="<?= !$this->_options['readonly'] ? 'commentAdd(this, SNAME)' : "alert('{$this->_options['readonly_alert']}')" ?>">Комментировать</a></li>
                    <? if(!$this->_options['readonly'] && ($uid == $data['author'] || $data['is_permission'])) { ?>
                        <?php if(($data['is_permission'] >= 1 && $data['is_permission'] < 4) || $data['author'] == get_uid(false)){?>
                            <li class="cl-edit"><a href="javascript:void(0)" onclick="commentEdit(this, SNAME, <?=$data['id']?>)">Редактировать</a></li>
                        <?php }//if?>
                        <li class="cl-edit"><a href="?<?= url($_GET, array('cmtask' => 'delete', 'cmid' => $data['id']))?>" onclick="return (confirm('Вы уверены?'));">Удалить</a></li>
                        <?php if($this->enableWarningUsers && $this->_options['access'] == 1 && $data['author_uid'] != $uid && false) { ?>
                        <li class="cl-edit warnlink-<?= $data['author_uid']?>">
                            <script type="text/javascript">
                                banned.addContext( 'comment-block-<?=$data['id']?>', 3, '<?= sprintf($this->maskLinkForComment, $GLOBALS['host'].$_SERVER['REQUEST_URI'], $data['id'])?>', "<?=htmlspecialchars($data['title'])?>" );
                            </script>
                            <a href="javascript:void(0)" style="color:red" onclick='banned.warnUser(<?=$data['author_uid']?>, 0, "comments", "comment-block-<?=$data['id']?>", 0); return false;'>Сделать предупреждение</a>
                            <div class="b-buttons__txt">— <span class='warncount-<?= $data['author_uid']?>'><?= (int)$data['warn']?></span></div>
                        </li>
                        <?php } elseif($this->enableWarningUsers && $this->_options['access'] > 1 && $this->_options['access'] < 4 && $data['author_uid'] != $uid) {//if?>
                        <li class="cl-edit warnlink-<?= $data['author_uid']?>">
                            <?php if($data['warn_count'] >=3 || $data['is_banned'] == 't') { ?>
                            <a class="id-ban-member<?=$data['member_id']?>" style="color:red" href="javascript:void(0)" onclick="<?= $this->userBanFunction?>(<?=$data['member_id']?>)"><?= $data['is_banned'] == 't'?'Разбанить!':'Забанить!'?></a>
                            <?php } ?>
                            <div class="b-warncount-<?= $data['member_id']?>" <?= ($data['warn_count'] >=3 || $data['is_banned'] == 't')?"style='display:none'":""?>>
                                <a href="javascript:void(0)" style="color:red" onclick="<?= $this->warningFunction."({$data['member_id']}, {$data['author_uid']}, {$data['resource_id']})"?>">Сделать предупреждение</a>
                                <div class="b-buttons__txt">— <span class='warncount-<?= $data['author_uid']?>'><?= (int)$data['warn_count']?></span></div>
                            </div>
                        </li>
                        <?php }//elseif?>
                    <? } ?>
                <?php } elseif ( $data['is_permission'] && (!$data['mod_access'] || $data['is_permission'] <= $data['mod_access']) ) { ?>
                        <li class="cl-edit"><a href="?<?= url($_GET, array('cmtask' => 'restore', 'cmid' => $data['id']))?>" onclick="return (confirm('Вы уверены?'));">Восстановить</a></li>
                <? } ?>
                <? //if($has_child) { ?>
                    <li class="last"><a href="javascript:void(0)" class="cl-thread-toggle" style="display:none;"><?=$is_hidden ? 'Развернуть ' : 'Свернуть '?> ветвь</a></li>
                <? //} ?>
            </ul>
            <div id="comment-block-<?= $data['id'] ?>">&nbsp;</div>
        </div>
<!--    </div>-->
