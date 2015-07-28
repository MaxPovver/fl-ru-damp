<a name="c_<?=$msg['id']?>"></a>
<ul class="cl-i">
    <li><a href="?site=Stage&id=<?=$stage->data['id']?>#c_<?=$msg['id']?>" class="cl-anchor" onclick="SBR_STAGE.setMsgAnchor('c_<?=$msg['id']?>')">#</a></li>
    <li class="cl-time"><?=date('d.m.Y | H:i', strtotime($msg['post_date']))?></li>
    <? if($msg['moduser_id']) { ?>
    <li><img src="/images/ico-e-<?=$mod_a?>.png" title="<?=$mod_alt?>" /></li>
    <? } ?>
</ul>
<? /*
<div class="cl-arr">
    <a href="#c_<?=$msg['parent_id']?>" class="u-anchor">&uarr;</a>
    <a href="#c_3" class="d-anchor">&darr;</a>
</div>
     */ ?>
<a href="/users/<?=$msg['login']?>/" class="freelancer-name"><?=view_avatar($msg['login'], $msg['photo'], 1, 1, $cls="user-avatar")?></a>
<div class="user-info">
    <div class="username">
        
        <a href="/users/<?=$msg['login']?>/" class="<?=($msg['is_admin']=='t' ? 'arbitrage' : (is_emp($msg['role']) ? 'employer' : 'freelancer'))?>-name"><?=($msg['uname'].' '.$msg['usurname'].' ['.$msg['login'].']')?></a><?=view_mark_user($msg);?>&nbsp;<?=$session->view_online_status($msg['login'], false, '&nbsp;')?>
    </div>
    <div class="utxt">
    <? if($msg['deluser_id']) { ?>
      <p><font color="gray"><small>Комментарий удален <?=($msg['deluser_id'] == $msg['user_id'] ? 'автором' : 'администратором')?>: <?=date('d.m.Y | H:i', strtotime($msg['deleted']))?></small></font></p>
    <? } if(!$msg['deluser_id'] || $this->sbr->isAdmin()) { ?>
      <p><?=reformat($msg['msgtext'], 55-$msg['level']*2, 0, 0, 1)?></p>
    <? } ?>
    </div>
    <? if(($msg['attach'] || $msg['yt_link']) && (!$msg['deluser_id'] || $this->sbr->isAdmin())) { ?>
        <div class="form cl-one-att">
            <b class="b1"></b>
            <b class="b2"></b>
            <div class="form-in">
                <ul class="list-files">
                    <?php 
                        if ( $msg['attach'] ) 
                            foreach( $msg['attach'] as $id => $a ) {
                                $aData = getAttachDisplayData(null, $a['name'], $a['path'] );
                    ?>
                    <li><a <?=$aData['link']?> target="_blank"><?=($a['orig_name'] ? $a['orig_name'] : $a['name'])?></a>, <span><?=ConvertBtoMB($a['size'])?></span><span class="avs-norisk <?=$aData['virus_class']?>" <?=($aData['virus_class'] == 'avs-nocheck' ? 'title="Антивирусом проверяются файлы, загруженные после 1&nbsp;июня&nbsp;2011&nbsp;года"' : '')?>><nobr><?=$aData['virus_msg']?></nobr></span></li>
                    <?php
                            }
                    ?>
                </ul>
                <? if($msg['yt_link']) { ?>
                   <br />
                   <?=show_video($msg['id'], $msg['yt_link'])?>
                <? } ?>
            </div>
            <b class="b2"></b>
            <b class="b1"></b>
        </div>
    <? } ?>
        <?php if (($this->sbr->isAdmin() || $this->sbr->isEmp() || $this->sbr->isFrl())) { ?>
					<ul class="cl-o">
							<li class="cl-com first"><a href="javascript:;" onclick="SBR_STAGE.getMsgForm(<?=$msg['id']?>, 0)">Комментировать</a></li>
							<? if($msg['user_id']==$stage->sbr->uid && !$msg['deluser_id'] && $stage->checkMsgEditTime($msg['post_date']) || $this->sbr->isAdmin()) { ?>
									<li class="cl-edit"><a href="javascript:;" onclick="SBR_STAGE.getMsgForm(<?=$msg['id']?>, 1)">Редактировать</a></li>
									<li class="cl-del"><a href="javascript:;" onclick="SBR_STAGE.delMsg(<?=$msg['id']?>)">Удалить</a></li> <? // !!! "Вернуть" ?>
							<? } ?>
							<? /* <li class="last"><a href="" class="cl-thread-toggle">Свернуть ветвь</a></li> */ ?>
					</ul>
        <?php }//if?>
</div>
