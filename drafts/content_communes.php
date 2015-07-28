<? require_once($_SERVER['DOCUMENT_ROOT'].'/drafts/content_header.php'); ?>

<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/commune.common.php");
$xajax->printJavascript('/xajax/');
?>

    <? if($drafts) { ?>
        <form id="draft_frm" action="/drafts/" method="post">
				<div>
        <input type="hidden" name="p" value="communes" />
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
                <tr class="b-layout__tr">
					<td class="b-layout__one b-layout__one_bordbot_ccc b-layout__one_padtb_10"><div class="b-check b-check_padleft_10 b-check_top_2"><input id="del_draft_<?=$draft['id']?>" class="b-check__input" type="checkbox" name="del_draft[]" value="<?=$draft['id']?>" onClick="DraftsCheckToggleDeleteAll(this);" /></div></td>
					<td class="b-layout__one b-layout__one_bordbot_ccc b-layout__one_padtb_10"><div class="b-layout__txt"><a class="b-layout__link" href="<?=($draft['post_id'] ? getFriendlyURL("commune", $draft['post_id'])."?om=0&draft_id={$draft['id']}" : getFriendlyURL("commune_commune", $draft['commune_id'])."?draft_id={$draft['id']}")?>" <?=($draft['is_member'] != 't'? "onclick=\"alert('Вы не состоите в данном сообществе или заблокированы в нем'); return false\"": "")?>><?=($draft['title']!=''?reformat(htmlspecialchars($draft['title']),27,0,1):'[без названия]')?></a></div></td>
                    <td class="b-layout__one b-layout__one_bordbot_ccc b-layout__one_padtb_10"><div class="b-layout__txt"><a class="b-layout__link b-layout__link_color_6db335" href="/commune/?id=<?=$draft['commune_id']?>"><?=reformat($draft['commune_title'],37,0,1)?></a></div></td>
    				<td class="b-layout__one b-layout__one_bordbot_ccc b-layout__one_padtb_10"><div class="b-layout__txt"><?=$draft['pdate']?></div></td>
					<td class="b-layout__one b-layout__one_bordbot_ccc b-layout__one_padtb_10"><div class="b-layout__txt"><a class="b-layout__link" href="" onClick="<?=($draft['is_member'] == 't'? "xajax_PostDraft({$draft['id']}, 4 " . ($draft['post_id']?',1':'') . ")": "alert('Вы не состоите в данном сообществе или заблокированы в нем')")?>; return false;">Опубликовать</a></div></td>
					<td class="b-layout__one b-layout__one_bordbot_ccc b-layout__one_padtb_10"><div class="b-layout__txt"><a class="b-layout__link b-layout__link_color_c10600" href="" onClick="DraftDeleteSubmit(<?=$draft['id']?>); return false;">Удалить</a></div></td>
				</tr>
                <? } ?>
        </table>
				</div>
        </form>
    <? } else { ?>
    
<div class="b-fon b-fon_bg_fcc">
		<b class="b-fon__b1"></b>
		<b class="b-fon__b2"></b>
		<div class="b-fon__body">
				<div class="b-layout__txt">В данный момент у вас нет сохраненных тем.</div>
  </div>
		<b class="b-fon__b2"></b>
		<b class="b-fon__b1"></b>
</div>
    
    <? } ?>

<form id="f_frm" style="display:none;" action="" method="post">
<div>
    <input id="f_draft_id" type="hidden" name="draft_id" value="" />
    <input id="f_id" type="hidden" name="id" value="" /> 
    <input id="f_action" type="hidden" name="action" value=""/> 
    <input id="f_category_id" type="hidden" name="category_id" value="" />
    <input id="f_title" type="hidden" name="title" value="" />  
    <textarea id="f_msgtext" name="msgtext" cols="" rows=""></textarea>
    <input id="f_youtube_link" type="hidden" name="youtube_link" value="" />  
    <input id="f_close_comments" type="checkbox" name="close_comments" value="1" />
    <input id="f_is_private" type="checkbox" name="is_private" value="1" />
    <input type="hidden" id="f_draft_post_id" name="draft_post_id" value="" />
    <input id="f_top_id" type="hidden" name="top_id" value="" /> 
    <input id="f_message_id" type="hidden" name="message_id" value="" /> 
    <input id="f_page" type="hidden" name="page" value="1" /> 
    <input id="f_attachedfiles_session" type="hidden" name="attachedfiles_session" value="" />

    <input type="hidden" name="user_login" value="" /> 

    <input type="hidden" name="parent_id" value="" /> 
    <input type="hidden" name="om" value="0" /> 

    <input type="hidden" name="cat" value="" /> 

    <input id="f_poll_question" type="hidden" name="question" value="" />
    <input id="f_poll_type" type="hidden" name="multiple" value="" />
    <div id="f_poll_answers"></div>
</div>
</form>
