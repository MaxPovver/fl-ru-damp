<?php
$interview_back_url = './' . url($GET, array('id' => null), 0, '?');
?>
<script type="text/javascript">
window.addEvent('domready', function() {
    $$('a[id^="interview_back"]').addEvent('click', function() {
        window.location = '/interview/?<?=$back_url?>';
    });
});
</script>

<?php
$crumbs = array();
$crumbs[] = array("title"=>"Статьи и интервью", "url"=>"/articles/");
$crumbs[] = array("title"=>"Интервью", "url"=>"/interview/");
$crumbs[] = array("title"=>$interview['uname'].' '.$interview['usurname'].' ['.$interview['login'].']');
?>
<div class="b-menu b-menu_crumbs  b-menu_padbot_20"><?=getCrumbs($crumbs)?></div>

<? include($mpath . '/tabs.php'); ?>
<div class="page-interview">
    <a name="page_interview"></a>
    <div class="tnav-interview">
        <div class="interview-pager">
            <? if(isset($navigation['prev'])) { 
                ?>
                <a href="<?=getFriendlyURL('interview', $navigation['prev']['id'])?>">&laquo; <?=$navigation['prev']['uname'] . ' ' . $navigation['prev']['usurname'] . ' [' . $navigation['prev']['login'] . ']'?></a>
            <? } else { /* ?>
                <span>&laquo; предыдущее интервью</span>
            <? */ } ?>
            &nbsp;&nbsp;&nbsp;
            <? if(isset($navigation['next'])) { ?>
                <a href="<?=getFriendlyURL('interview', $navigation['next']['id'])?>"><?=$navigation['next']['uname'] . ' ' . $navigation['next']['usurname'] . ' [' . $navigation['next']['login'] . ']'?> &raquo;</a>
            <? } else { /* ?>
                <span>следующее интервью &raquo;</span>
            <? */ } ?>
        </div>
        <a id="interview_back1" href="javascript:void(0);">Вернуться к списку интервью</a>
    </div>
    <div class="p-interview-in c">
        <div class="interview-avatar">
            <a href="/users/<?=$interview['login']?>">
                <? if ($interview['fname']) {
                    $alt = $interview['uname'] . ' ' . $interview['usurname'] . ' [' . $interview['login'] . ']';
                    $title = 'Интервью - ' . $alt;
                ?>
                <img src="<?=WDCPREFIX . "/{$interview['path']}".(substr($interview['fname'],0,3)=='sm_' ? '' : 'sm_')."{$interview['fname']}"?>" alt="<?= $alt ?>" title="<?= $title ?>" width="100" />
                <? } ?>
            </a>
        </div>
        <div class="interview-one">
            <div class="aih">
                <span class="interview-date"><?=date('d.m.Y в H:i', strtotime($interview['post_time']))?></span>
                <? if(hasPermissions('interviews')) { ?>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <a href="javascript:void(0)" class="lnk-ai" onclick="editInterview(<?=$interview['id']?>)">Редактировать</a> |
                <a href="/interview/?task=del&id=<?=$interview['id']?>&token=<?=$_SESSION['rand']?>" class="lnk-ai" onclick="return (confirm('Уверены?'))">Удалить</a>
                <? } ?>
            </div>
            
            <h1><a href="/users/<?=$interview['login']?>"><?=$interview['uname'] . ' ' . $interview['usurname'] . ' [' . $interview['login'] . ']'?></a></h1>
            <div class="interview-body utxt"><?=$interview['txt']//=reformat($interview['txt'], 100, 0, 1)?></div>
        </div>
    </div>
	
			
			
            	<?= ViewSocialButtons('interview', $interview['uname'] . ' ' . $interview['usurname'] . ' [' . $interview['login'] . ']', true)?>
			
		
	
	
	
    <? if(hasPermissions('interviews')) include('form.php'); ?>
    <div class="bnav-interview">
        <div class="interview-pager">
            <? if(isset($navigation['prev'])) { 
                ?>
                <a href="<?=getFriendlyURL('interview', $navigation['prev']['id'])?>">&laquo; <?=$navigation['prev']['uname'] . ' ' . $navigation['prev']['usurname'] . ' [' . $navigation['prev']['login'] . ']'?></a>
            <? } else { /* ?>
                <span>&laquo; предыдущее интервью</span>
            <? */ } ?>
            &nbsp;&nbsp;&nbsp;
            <? if(isset($navigation['next'])) { ?>
                <a href="<?=getFriendlyURL('interview', $navigation['next']['id'])?>"><?=$navigation['next']['uname'] . ' ' . $navigation['next']['usurname'] . ' [' . $navigation['next']['login'] . ']'?> &raquo;</a>
            <? } else { /* ?>
                <span>следующее интервью &raquo;</span>
            <? */ } ?>
        </div>
        <a id="interview_back2" href="javascript:void(0);">Вернуться к списку интервью</a>
    </div>
</div>
