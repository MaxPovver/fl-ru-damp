<? require_once($_SERVER['DOCUMENT_ROOT'].'/drafts/content_header.php'); ?>

<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/blogs.common.php");
$xajax->printJavascript('/xajax/');
?>

    <? if($drafts) { ?>
        <form id="draft_frm" action="/drafts/" method="post">
				<div>
        <input type="hidden" name="p" value="blogs" />
        <input type="hidden" name="draft_frm_action" id="draft_frm_action" value="" />
        
<div class="b-fon">
		<b class="b-fon__b1"></b>
		<b class="b-fon__b2"></b>
		<div class="b-fon__body">
    			<span class="b-check b-check_padleft_10 b-check_inline-block b-check_valign_middle"><input id="dellall_draft" class="b-check__input" type="checkbox" onClick="DraftsToggleDeleteAll(this);" name="dellall_draft" value="1" /></span> &#160;<button onClick="DraftDeleteSubmit(0); return false;">Удалить</button> 
  </div>
		<b class="b-fon__b2"></b>
		<b class="b-fon__b1"></b>
</div>
        

        <? if($is_ban) { ?>
        
<div class="b-fon b-fon_bg_fcc">
		<b class="b-fon__b1"></b>
		<b class="b-fon__b2"></b>
		<div class="b-fon__body">
				<div class="b-layout__txt">Команда Free-lance.ru заблокировала вам возможность оставлять записи в сервисе &laquo;Блоги&raquo; по причине: <?=$ban['comment']?></div>
  </div>
		<b class="b-fon__b2"></b>
		<b class="b-fon__b1"></b>
</div>
        
        
        <? } ?>

    	<table class="tbl-projects-draft">
            <colgroup>
        <col width="30" />
				<col width="" />
				<col width="200" />
				<col width="120" />
				<col width="80" />
				<col width="60" />
			</colgroup>
                <? foreach($drafts as $draft) { ?>
                <? if($draft['post_id']) { $blogmsg = blogs::GetMsgInfo($draft['post_id'], $error, $perm); } ?>
                <tr class="b-layout__tr">
					<td class="b-layout__one b-layout__one_bordbot_ccc b-layout__one_padtb_10"><div class="b-check b-check_padleft_10 b-check_top_2"><input id="del_draft_<?=$draft['id']?>" class="b-check__input" type="checkbox" name="del_draft[]" value="<?=$draft['id']?>" onClick="DraftsCheckToggleDeleteAll(this);" /></div></td>
					<td class="b-layout__one b-layout__one_bordbot_ccc b-layout__one_padtb_10"><div class="b-layout__txt">
                        <? if($is_ban || $draft['is_blocked']) { ?>
                        <?=($draft['title']!=''?reformat(htmlspecialchars($draft['title']),27,0,1):'[без названия]')?>
                        <? } else { ?>
                        <a class="b-layout__link" href="<?=($draft['post_id'] ? getFriendlyUrl('blog',$blogmsg['thread_id'])."?id={$draft['post_id']}&draft_id={$draft['id']}&action=edit" : getFriendlyUrl('blog_group',$draft['category'])."?draft_id={$draft['id']}#bottom")?>"><?=($draft['title']!=''?reformat(htmlspecialchars($draft['title']),27,0,1):'[без названия]')?></a>
                        <? } ?>
                    </div>
                    </td>
					<td class="b-layout__one b-layout__one_bordbot_ccc b-layout__one_padtb_10"><div class="b-layout__txt"><a class="b-layout__link b-layout__link_color_6db335" href="/blogs/viewgroup.php?gr=<?=$draft['category']?>"><?=$draft['category_title']?></a></div></td>
    				<td class="b-layout__one b-layout__one_bordbot_ccc b-layout__one_padtb_10"><div class="b-layout__txt"><?=$draft['pdate']?></div></td>
					<td class="b-layout__one b-layout__one_bordbot_ccc b-layout__one_padtb_10"><div class="b-layout__txt"><? if($is_ban || $draft['is_blocked']) { ?>&nbsp;<? } else { ?><a class="b-layout__link" href="" onClick="xajax_PostDraft(<?=$draft['id']?>, 3 <?=($draft['post_id']?',1':'')?>); return false;">Опубликовать</a><? } ?></div></td>
					<td class="b-layout__one b-layout__one_bordbot_ccc b-layout__one_padtb_10"><div class="b-layout__txt"><a class="b-layout__link b-layout__link_color_c10600" href="" onClick="DraftDeleteSubmit(<?=$draft['id']?>); return false;">Удалить</a></div></td>
				</tr>
                <? } ?>
        </table>
				</div>
        </form>
    <? } else { ?>
    
<div class="b-fon">
		<b class="b-fon__b1"></b>
		<b class="b-fon__b2"></b>
		<div class="b-fon__body">
				В данный момент у вас нет сохраненных блогов.
  </div>
		<b class="b-fon__b2"></b>
		<b class="b-fon__b1"></b>
</div>
    
    <? } ?>

<form id="f_frm" style="display:none;" action="" method="post">
<div>
    <input id="f_draft_id" type="hidden" name="draft_id"  value="" />
    <input id="f_draft_post_id" type="hidden" name="draft_post_id" value="" />

    <input id="f_olduser" type="hidden" name="olduser" value="" />

    <input id="f_msg_name" type="hidden" name="msg_name" value="" />
    <input id="f_name" type="hidden" name="name" value="" />
    <input id="f_msg" type="hidden" name="msg" value="" />
    <input id="f_yt_link" type="hidden" name="yt_link" value="" />
    <input id="f_is_close_comments" type="checkbox" name="close_comments" value="1" />
    <input id="f_is_private" type="checkbox" name="is_private" value="1" />
    <input id="f_category" type="hidden" name="category" value="" />

    <input id="f_sub_ord" type="hidden" name="sub_ord" value="" />

    <input type="hidden" name="ord" value="" />
    <input id="f_tr" type="hidden" name="tr" value="" />
    <input id="f_reply" type="hidden" name="reply" value="" />
    <input id="f_page" type="hidden" name="page" value="" />
    <input id="f_pagefrom" type="hidden" name="pagefrom" value="" />
    <input id="f_onpage" type="hidden" name="onpage" value="" />
    <input id="f_action" type="hidden" name="action" value="" />

    <input id="f_attachedfiles_session" type="hidden" name="attachedfiles_session" value="" />

    <input id="f_poll_question" type="hidden" name="question" value="" />
    <input id="f_poll_type" type="hidden" name="multiple" value="" />
    <div id="f_poll_answers"></div>
</div>

</form>
