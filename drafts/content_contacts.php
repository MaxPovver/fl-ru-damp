<? require_once($_SERVER['DOCUMENT_ROOT'].'/drafts/content_header.php'); ?>

<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/contacts.common.php");
$xajax->printJavascript('/xajax/');
?>

    <? if($drafts) { ?>
        <form id="draft_frm" action="/drafts/" method="post">
				<div>
        <input type="hidden" name="p" value="contacts" />
        <input id="draft_frm_action" type="hidden" name="draft_frm_action" value="" />
        
<div class="b-fon">
		<b class="b-fon__b1"></b>
		<b class="b-fon__b2"></b>
		<div class="b-fon__body">
    			<span class="b-check b-check_padleft_10 b-check_inline-block b-check_valign_middle"><input id="dellall_draft" class="b-check__input" type="checkbox" onClick="DraftsToggleDeleteAll(this);" name="dellall_draft" value="1" /></span> &#160;<button onClick="DraftDeleteSubmit(0); return false;">Удалить</button> 
  </div>
		<b class="b-fon__b2"></b>
		<b class="b-fon__b1"></b>
</div>
        
        
     
    	<table class="b-layout__table b-layout__table_width_full">
    		<colgroup>
          <col width="30" />
    			<col width="" />
    			<col width="120" />
    			<col width="80" />
    			<col width="60" />
    		</colgroup>
                <? foreach($drafts as $draft) { ?>
                <tr class="b-layout__tr">
    				<td class="b-layout__one b-layout__one_bordbot_ccc b-layout__one_padtb_10"><div class="b-check b-check_padleft_10 b-check_top_2"><input id="del_draft_<?=$draft['id']?>" class="b-check__input" type="checkbox" name="del_draft[]" value="<?=$draft['id']?>" onClick="DraftsCheckToggleDeleteAll(this);" /></div></td>
    				<td class="b-layout__one b-layout__one_bordbot_ccc b-layout__one_padtb_10"><div class="b-layout__txt"><a class="b-layout__link" href="/contacts/?from=<?=$draft['to_login']?>&draft_id=<?=$draft['id']?>">Сообщение для <?=$draft['uname']?> <?=$draft['usurname']?> [<?=$draft['to_login']?>]</a></div></td>
    				<td class="b-layout__one b-layout__one_bordbot_ccc b-layout__one_padtb_10"><div class="b-layout__txt"><?=$draft['pdate']?></div></td>
    				<td class="b-layout__one b-layout__one_bordbot_ccc b-layout__one_padtb_10"><div class="b-layout__txt"><a class="b-layout__link" href="" onClick="xajax_PostDraft(<?=$draft['id']?>, 2); return false;">Отправить</a></div></td>
    				<td class="b-layout__one b-layout__one_bordbot_ccc b-layout__one_padtb_10"><div class="b-layout__txt"><a class="b-layout__link b-layout__link_color_c10600" href="" onClick="DraftDeleteSubmit(<?=$draft['id']?>); return false;" >Удалить</a></div></td>
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
				<div class="b-layout__txt">В данный момент у вас нет сохраненных сообщений.</div>
  </div>
		<b class="b-fon__b2"></b>
		<b class="b-fon__b1"></b>
</div>
    
    
    <? } ?>

<form id="f_frm" style="display:none;" action="" method="post">
<div>
    <?php
        if ( empty($_SESSION['msg_csrf']) || !is_array($_SESSION['msg_csrf']) ) {
            $_SESSION['msg_csrf'] = array();
        }
        if ( count($_SESSION['msg_csrf']) > 40 ) {
            array_shift($_SESSION['msg_csrf']);
        }
        $_SESSION['msg_csrf'][] = $msg_csrf = md5(uniqid(rand(), true));
    ?>
	<input type="hidden" name="msg_csrf" value="<?=$msg_csrf?>" />
    <textarea id="f_msg" name="msg" rows="" cols=""></textarea>
    <input id="f_msg_to" type="hidden" name="msg_to" value="" />
    <input id="f_action" type="hidden" name="action" value="post_msg" />
    <input id="f_draft_id" type="hidden" name="draft_id" value="" />
    <input id="f_to_login" type="hidden" name="to_login" value="" />
    <input id="f_attachedfiles_session" type="hidden" name="attachedfiles_session" value="" />
</div>
</form>
