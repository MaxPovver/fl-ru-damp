<? require_once($_SERVER['DOCUMENT_ROOT'].'/drafts/content_header.php'); ?>

    <? if($drafts) { ?>
        <form id="draft_frm" action="/drafts/" method="post">
				<div>
        <input type="hidden" name="p" value="projects" />
        <input type="hidden" name="draft_frm_action" id="draft_frm_action" value="" />
        <div class="form form-drafts-op">
    		<b class="b1"></b>
    		<b class="b2"></b>
    		<div class="form-in">
    			<span class="i-chk"><input type="checkbox" onClick="DraftsToggleDeleteAll(this);" id="dellall_draft" name="dellall_draft" value="1" /></span> <button onClick="DraftDeleteSubmit(0); return false;">Удалить</button> 
    		</div>
	    	<b class="b2"></b>
    		<b class="b1"></b>
	    </div>
    	<table class="tbl-projects-draft">
    		<colgroup>
          <col width="13" />
    			<col width="" />
    			<col width="120" />
    			<col width="80" />
    			<col width="60" />
    		</colgroup>
            <tbody>
                <? foreach($drafts as $draft) { ?>
                <tr>
    				<td><span class="i-chk"><input type="checkbox" id="del_draft_<?=$draft['id']?>" name="del_draft[]" value="<?=$draft['id']?>" onClick="DraftsCheckToggleDeleteAll(this);" /></span></td>
                    <td><a href="/public/?step=1&kind=<?=$draft['kind']?><?=($draft['prj_id']?"&public={$draft['prj_id']}":"")?>&draft_id=<?=$draft['id']?>&red="><?=($draft['name']!=''?reformat(str_replace(array("<", ">"), array('&lt;', '&gt;'), $draft['name']),27,0,1):'[без названия]')?></a></td>
    				<td><?=$draft['pdate']?></td>
    				<td><a href="/public/?step=1&kind=<?=$draft['kind']?><?=($draft['prj_id']?"&public={$draft['prj_id']}":"")?>&draft_id=<?=$draft['id']?>&red=&auto_draft=1">Опубликовать</a></td>
    				<td><a href="" onClick="DraftDeleteSubmit(<?=$draft['id']?>); return false;" class="lnk-dred">Удалить</a></td>
    			</tr>
                <? } ?>
    		</tbody>
        </table>
        </div>
        </form>
    <? } else { ?>
        <div class="form fs-p fd-w">
    		<b class="b1"></b>
			<b class="b2"></b>
			<div class="form-in">
				В данный момент у вас нет сохраненных проектов.
			</div>
			<b class="b2"></b>
			<b class="b1"></b>
		</div>
    <? } ?>
