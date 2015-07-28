<?
$dlnk = $curr_sbr->id ? "<a href=\"?site=docs&id={$curr_sbr->id}\">" : '';
if($dlnk) $dclnk = '</a>';
?>
<div class="nr-block-imp">
	<b class="b1"></b>
	<b class="b2"></b>
	<div class="form-in">
        Сделка будет считаться завершенной только после подписания <?=$dlnk?>Акта<?=$dclnk?> каждого из участников сделки.
        <? if(!$curr_sbr->checkUserReqvs()) { ?>
          Вам необходимо заполнить реквизиты на странице <a href="/users/<?=$curr_sbr->login?>/setup/finance/">Финансы</a>.
        <? } ?>
	</div>
	<b class="b2"></b>
	<b class="b1"></b>
</div>
