<script type="text/javascript" src="/scripts/mAttach2.js"></script>
<script type="text/javascript">
SbrStage.prototype.MAX_MSG_FILES=<?=sbr_stages::MAX_MSG_FILES?>;
SbrStage.prototype.id=<?=$stage->data['id']?>;
var SBR_STAGE = new SbrStage();
<? if(!$action) { ?>
window.addEvent('domready', function() { SBR_STAGE.setMsgAnchor(); } );
<? } ?>
</script>

	<div class="nr-discuss">
        <?php if (($sbr->isAdmin() || $sbr->isEmp() || $sbr->isFrl())) { ?><a href="#c_0" class="btngr btn-right" onclick="window.setTimeout('document.getElementById(\'msg_form0\').msgtext.focus()',0)"><span class="btn-lc"><span class="btn-m"><span class="btn-txt">Добавить комментарий</span></span></span></a><?php }//if?>
		<h4>Обсуждение проекта</h4>
        <div class="comment-list" id="cl">
					<?php if(($stage_msgs)||($sbr->docs)) { ?>
            <ul class="cl-ul">
                <? if($stage_msgs) foreach($stage_msgs as $msg) { $stage->msg_node($msg); } // !!! ?>
                <? if($sbr->docs) foreach($sbr->docs as $doc) { $sbr->doc_node($doc); } ?>
            </ul>
					<?php } //if?>
        </div>
        <?php if (($sbr->isAdmin() || $sbr->isEmp() || $sbr->isFrl())) { ?>
		<div class="nr-discuss-comment">
            <br />
            <a name="c_0"></a>
			<h4>Добавить комментарий</h4>
            <p class="nr-discuss-imp">
              Просим обратить внимание на то, что <strong>Арбитраж принимает во внимание переписку только в комментариях к проекту</strong>.<br />
              Не рассматривается переписка в иных сервисах, например Skype, ICQ или электронной почте.
            </p>
            <div id="msg_form_box0">
                <? 
                if($stage->post_msg && !$stage->post_msg['parent_id'] && !$stage->post_msg['id'])
                       echo $stage->msg_form($stage->post_msg, $stage->error['msgs'], true);
                   else
                       echo $stage->msg_form(array('stage_id'=>$stage->id, 'id'=>'0'), NULL, true); // !!! нужно вместо 0 что-нибудь другое.
                ?>
            </div>
		</div>
		<?php } //if?>
	</div>
