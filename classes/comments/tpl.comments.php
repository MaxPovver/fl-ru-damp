<script>
<?php if($this->enableNewWysiwyg) { ?>
    var editor_customConfig = '<?= $this->configNewWywiwyg?>';
<?php }?>
    var ARTICLE = <?=$this->_resource_id?>;
    var SNAME = '<?=strtolower($this->_sname)?>';
    var new_comments = new Array();
    var nav_position = 0;
    var commentsInit = function() {
        updateGlobalAnchor();
    <? if($alert) { ?>
       <? if($reply != 'NULL' && $this->_task == 'add') { ?>
        commentAdd($('c_<?=$reply?>').getElement('.cl-com a'));
       <? } elseif($reply != 'NULL' && $this->_task == 'edit') { ?>
           formMoveTo($('c_<?=$reply?>'));
       <? } else { ?>
            $('comm-show-form').fireEvent('click');
       <? } ?>
       <?= $reply !='NULL' ? 'document.getElement("div.cl-form form").getParent("li").scrollIntoView(true);' : '' ?>
       <?= $reply =='NULL' ? 'document.getElement("div.cl-form").scrollIntoView(true);' : '' ?>
       <?= isset($alert[3]) ? 'toggleYoutube();' : '' ?>
       <?= isset($alert[2]) ? 'toggleFiles();' : '' ?>
       
        

    <? } ?>
    <? if($this->is_new_template) { ?>
        var reg = /#(c)_(\d*)/;
        var arr = null;
        if (arr = reg.exec(window.location.hash)) {
            setAnchor(arr[1],arr[2]);
        }  
     <? }//if?>
     if(document.location.hash.length) {
         <?php if($this->is_new_template) {?>
            $$('a.b-post__anchor[href*='+document.location.hash.replace('#', '')+']').addClass('b-post__anchor_black');
         <?php } else { //if?>
            $$('a.cl-anchor[href*='+document.location.hash.replace('#', '')+']').addClass('cur');
         <?php }//else?>
     }
     document.addEvent('hashchange', function(val) {
         if(!$$('a.cl-anchor[href*='+val+']')) return;
         $$('a.cl-anchor').removeClass('cur');
         $$('a.cl-anchor[href*='+val+']').addClass('cur');
     });

    <? if($_SESSION['c_new_id']>0) { ?>
        var loc = String();
        loc = document.location.href;
        loc = loc.split('#');
        var link = <?=$_SESSION['c_new_id']?>;
        document.location = loc[0] + '#c_<?=$_SESSION['c_new_id']?>';
        setAnchor('c', <?=$_SESSION['c_new_id']?>);
        setDisplayAnchor(document.getElementById('link_anchor_<?=$_SESSION['c_new_id']?>'));
        try {
            var t = $('c_' + link).getParent('li.cl-li');
        } catch(e){;}
        if(t != undefined) {
            t.removeClass('cl-li-hidden-c');
            t.getElements('li.cl-li').removeClass('cl-li-hidden-c');
            t.getElements('a.cl-thread-toggle').set('text', 'Свернуть ветвь');
        }
        <? unset($_SESSION['c_new_id']); ?>
    <? } ?>
    
    NavForNewComments();
    
    };
</script>
<div class="nav-cl" id="nav_comm" style="display:none">
    <div class="nav-cl-block">
        <a href="javascript:void(0)" class="nav-cl-uarr" onclick="navComments('prev')">?</a>
        <strong id="nav_comm_count">0</strong>
        <a href="javascript:void(0)" class="nav-cl-darr" onclick="navComments('next')">?</a>
    </div>
</div>
<?php if($this->_options['no_comments'] == true && !$this->msg_num) {?>
<div style="font-size: 18px; padding: 0pt 20px 20px;">Автор запретил оставлять комментарии</div>
<?php } else if($this->is_new_template) { //if?>
<div class="comment-list" id="cl" onclick="updateGlobalAnchor();">
    <ul class="b-post__links b-post__links_padtop_20 b-post__links_float_right">
        <? if($this->msg_num) { ?>
            <? if($this->enableHiddenThreads) { ?>
                <li class="b-post__links-item"><a href="" class="lnk-dot-<?=in_array(-1, $this->_hidden) ? '666' : '999'?> cl-show-all">Показать все ветви</a></li>
                <li class="b-post__links-item b-post__links-item_padleft_10"><a href="" class="lnk-dot-<?=in_array(-1, $this->_hidden) ? '999' : '666'?> cl-hide-all">Свернуть все ветви</a></li>
            <? } ?>
        <? } ?>
        <? if($uid) { ?>
            <?php if ($this->_sname == "Commune") { ?>
            <li class="b-post__links-item b-post__links-item_padleft_30">
                <? if (!$this->_options['readonly']) { ?>
                    <a href="javascript:void(0)" id="subscribe_to_comm" class="b-post__link b-post__link_dot_0f71c8" onclick="xajax_SubscribeTheme(<?=$this->_resource_id ?>, 1); return false;"><?php if (!$user_is_subscribe_on_topic) {?>Подписаться на комментарии<?} else {?>Отписаться от комментариев <?} ?></a>
                <? } ?>                    
            </li>
            <?php }?>
            <li class="b-post__links-item b-post__links-item_padleft_30">
                <? if (!$this->_options['readonly']) { ?>
                    <a href="javascript:void(0)" id="comm-show-form" class="b-post__link b-post__link_dot_0f71c8">Прокомментировать</a>
                <? } ?>
            </li>
        <? } ?>
    </ul>
    <?php if($this->msg_num || $uid ) { ?>
    <h2 class="b-post__title b-post__title_padbot_15 b-post__title_padtop_14 b-post__title_width_250"><?= $this->msg_num;?> <?=ending($this->msg_num, "комментарий", "комментария", "комментариев")?></h2>
    <?php }//if?>
    <? if($this->msg_num) { ?>
        <?= $comments_html ?>
    <? } ?>
    <? if($uid) { ?>
        <?= $form ?>
    <? }?>
</div>
<?php } else { //else?>
<div class="comment-list" id="cl" onclick="updateGlobalAnchor();">
    <ul class="cl-thread-o">
    <? if($this->msg_num) { ?>
        <? if($this->enableHiddenThreads) { ?>
								<li><a href="" class="lnk-dot-<?=in_array(-1, $this->_hidden) ? '666' : '999'?> cl-show-all">Показать все ветви</a></li>
								<li><a href="" class="lnk-dot-<?=in_array(-1, $this->_hidden) ? '999' : '666'?> cl-hide-all">Свернуть все ветви</a></li>
        <? } ?>
    <? } ?>
    <? if($uid) { ?>
    <li>
        <? if (!$this->_options['readonly']) { ?>
        <a href="javascript:void(0)" id="comm-show-form" class="lnk-dot-999">Комментировать</a>
        <? } else { ?>
        <a href="javascript:void(0)" onclick="alert('<?= $this->_options['readonly_alert'] ?>')" class="lnk-dot-999">Комментировать</a>
        <? } ?>
    </li>
    <? } ?>
    </ul>
    <? if($this->msg_num || $uid ) { ?>
    <h3>Комментарии<?= $this->msg_num ? " ({$this->msg_num})" : '' ?>:</h3>
    <? } ?>
    <a name="comments"></a>
    <? if($this->msg_num) { ?>
    <?= $comments_html ?>
    <? } ?>
    <? if($uid) { ?>
    <?= $form ?>
    <? }?>
</div>
<?php }//else?>