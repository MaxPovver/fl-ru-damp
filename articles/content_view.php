<?
$_url = array();
if($ord) $_url['ord'] = $ord;
if($page) $_url['p'] = $page;
if($tab == 'unpublished' && hasPermissions('articles')) $_url['page'] = 'unpublished';
$back_url = http_build_query($_url);

$goto = __paramInit('int', 'goto');

$show_nav = !($article['approved'] == 'f' && $uid && !hasPermissions('articles')) ;

?>

<? if($goto) { ?>
<script>
window.addEvent('domready', function() {
    document.location.href = '#c_<?=$goto?>';
});
</script>
<? } ?>
<?php
$fs = !empty($_COOKIE['article_fs']) ? (int)$_COOKIE['article_fs'] : 12;
?>
<script type="text/javascript">
window.addEvent('domready', function() {
    $$('a[id^="articles_back"]').addEvent('click', function() {
        window.location = '/articles/?<?=$back_url?>';
    });
});
									window.addEvent('domready',
									function() {
										$(document.body).getElement('.post-fs-minus').addEvent('click', function(){
											var fs = $(this).getParent('.post-fsize').getNext().getNext().getNext().getStyle('font-size').toInt();
											if (fs==6){
												return false;
											}else{
												var fsa = fs-2;
												if(fsa==6){
													$(this).addClass('post-fs-d');
												}
												if(fsa==22){
													$(document.body).getElement('.post-fs-plus').removeClass('post-fs-d');
												}
												$(this).getParent('.post-fsize').getNext().getNext().getNext().setStyle('font-size', fsa+'px');
                                                                                                setSizeToCookie(fsa);
											}
										});
										$(document.body).getElement('.post-fs-plus').addEvent('click', function(){
											var fs = $(this).getParent('.post-fsize').getNext().getNext().getNext().getStyle('font-size').toInt();
											if (fs==24){
												return false;
											}else{
												var fsa = fs+2;
												if (fsa==24){
													$(this).addClass('post-fs-d');
												}if(fsa==8){
													$(document.body).getElement('.post-fs-minus').removeClass('post-fs-d');
												}
												$(this).getParent('.post-fsize').getNext().getNext().getNext().setStyle('font-size', fsa+'px');
                                                                                                setSizeToCookie(fsa);
											}
										});

           <?php if($fs <= 6){?>
               $(document.body).getElement('.post-fs-minus').addClass('post-fs-d');
            <?php }elseif($fs >= 24){?>
               $(document.body).getElement('.post-fs-plus').addClass('post-fs-d');
            <?php } ?>
									});
</script>
<script type="text/javascript">
 function setSizeToCookie(size){
   var expiry = new Date();
   expiry.setTime(expiry.getTime() + 24*60*60*1000);
   document.cookie='article_fs='+size+'; path=/; expires=' + expiry.toGMTString();
 }
</script>
<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/ru_RU/all.js#xfbml=1";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>

<?php
$crumbs = array();
$crumbs[] = array("title"=>"Статьи и интервью", "url"=>"/articles/");
$crumbs[] = array("title"=>"Статьи", "url"=>"/articles/");
$crumbs[] = array("title"=>reformat($article['title'], 100, 0, 1));
?>
<div class="b-menu b-menu_crumbs  b-menu_padbot_20"><?=getCrumbs($crumbs)?></div>

<?include($mpath . '/tabs.php')?>
<div class="page-interview">
    <div class="tnav-interview">
        <div class="interview-pager">
        <? if($show_nav) { ?>

            <? if(isset($navigation['prev'])) { 
                ?>
                <a class="b-layout__link" href="<?=getFriendlyURL('article', $navigation['prev']['id'])?>">&laquo; <?=($navigation['prev']['title'] ? reformat($navigation['prev']['title'], 32, 0, 1) : 'Без названия')?></a>
            <? } else { 
                /* ?>
                <span>&laquo; предыдущая статья</span>
                <? */
            } ?>
            &nbsp;&nbsp;&nbsp;
            <? if(isset($navigation['next'])) { ?>
                <a class="b-layout__link" href="<?=getFriendlyURL('article', $navigation['next']['id'])?>"><?=($navigation['next']['title'] ? reformat($navigation['next']['title'], 32, 0, 1) : 'Без названия')?> &raquo;</a>
            <? } else { 
                /* ?>
                <span>следующая статья &raquo;</span>
                <? */ 
            } ?>

        <? } ?>
        </div>
        <a id="articles_back1" class="b-layout__link" href="javascript:void(0);">Вернуться к списку статей</a>
    </div>
    <div id="post_<?=$article['id']?>" class="p-interview-in c">
        <div class="interview-avatar">
            <a href="">
                <img src="<?=WDCPREFIX?>/<?=$article['path']?><?=$article['fname']?>" alt="" width="100" />
            </a>
        </div>
        <div class="interview-one">
            <? if($article['approved'] == 't') { ?>
                <? $classname = $article['rating'] < 0 ? 'pr-minus' : ($article['rating'] >= 1 ? 'pr-plus' : '') ; ?>
                <div class="post-rate" id="rate_<?=$article['id']?>">
                    <? if($uid) { ?>
                    <a href="?page=rate&id=<?=$article['id']?>&to=down"><img src="/images/btn-drate<?=$article['rate_value'] < 0 ? '-dis' : ''?>.png" alt="" /></a>
                    <? } else { ?>
                    <img src="/images/btn-drate-dis.png" alt="" />
                    <? } ?>
                    <span class="post-rate-val <?=$classname?>">
                        <?= ($article['rating'] > 0 ? '+' : '') . intval($article['rating'])?>
                    </span>
                    <? if($uid) { ?>
                    <a href="?page=rate&id=<?=$article['id']?>&to=up"><img src="/images/btn-urate<?=$article['rate_value'] > 0 ? '-dis' : ''?>.png" alt="" /></a>
                    <? } else { ?>
                    <img src="/images/btn-urate-dis.png" alt="" />
                    <? } ?>
                </div>
            <? } ?>
            <div class="post-fsize">
									<a href="javascript:void(0);" class="post-fs-minus">-</a>
									<a href="javascript:void(0);" class="post-fs-plus">+</a>
								</div>
            <div class="aih">
                <span class="interview-date">
                    <?=date('d.m.Y в H:i', strtotime( ($article['approved'] == 't' ?  $article['approve_date'] : $article['post_time']) ))?>
                </span>
                <span class="interview-date">
                    <a href="/users/<?=$article['login']?>">
                        <?= $article['uname'] . ' ' . $article['usurname'] . ' [' . $article['login'] . ']'?>
                    </a>
                </span>
                <? if(hasPermissions('articles')) { ?>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:void(0)" class="lnk-ai" onclick="editArticle(<?=$article['id']?>)">Редактировать</a> |
                    <? if($article['approved'] == 'f') { ?>
                        <a href="javascript:void(0)" class="lnk-ai" onclick="delArticleForm(<?=$article['id']?>);">Удалить</a>
                    <? } else { ?>
                        <a href="/articles/?task=del-article&id=<?=$article['id']?>" class="lnk-ai" onclick="return (confirm('Вы уверены?'));">Удалить</a>
                    <? } ?>
                <? } ?>
                <? if($article['declined'] == 'f' && $article['approved'] == 'f' && (hasPermissions('articles'))) { ?>
                    &nbsp;|&nbsp;
                    <a href="javascript:void(0)"  class="lnk-ai" id="moderator_decline">Отклонить</a>
                <? } else if(hasPermissions('articles')) {?>
                    &nbsp;|&nbsp;
                    <a href="javascript:void(0)" style="color: #A23E3E;" id="moderator_undecline">На модерацию</a>
                <? } //?>
                <? if($article['approved'] == 'f' && (hasPermissions('articles'))) { ?>
                    &nbsp;|&nbsp;
                    <a href="javascript:void(0)" id="moderator_approve">Подтвердить</a>
                <? } ?>
            </div>
            
            <? if (hasPermissions('articles')) { ?>
                <form method="post" id="moderator_form">
                    <input type="hidden" name="task" id="moderator_form_task" />
                    <input type="hidden" name="id" id="moderator_form_article_id" value="<?= $article['id'] ?>" />
                </form>
            <? } ?>

            <? if($article['approved'] == 'f' && (hasPermissions('articles'))) { ?>
            <div id="del-article-form" class="form fs-o form-adel" style="display: none;">
                <b class="b1"></b>
                <b class="b2"></b>
                <div class="form-in">
                        <form method="post" action="/articles/?task=del-article">
                    <div class="form-block first last">
                        <h4>Удаление статьи</h4>
                            <div class="form-el">
                                <label class="form-label2">Укажите причину отказа в публикации (для автора):</label>
                                <div class="form-value">
                                    <input type="hidden" name="id" value="<?=$article['id']?>"/>
                                    <textarea rows="5" cols="20" name="msgtxt"></textarea>
                                    <div class="form-btns">
                                        <button onclick="return delArticleConfirm()">Удалить</button>&nbsp; <a href="javascript:void(0)" onclick="delArticleFormClose()" class="lnk-dot-666">Отменить</a>
                                    </div>
                                </div>
                            </div>
                    </div>
                        </form>
                </div>
                <b class="b2"></b>
                <b class="b1"></b>
            </div>
            <? } ?>
            
            <h1><?=!$article['title'] ? 'Без названия' : reformat($article['title'], 59, 0, 1) ?></h1>
            
            <div class="interview-body utxt" id="interview" style="font-size: <?php echo $fs;?>px">
                <?//=reformat($article['msgtext'], 55, 0, 0, 1)?>
                <?= textWrap($article['msgtext'], 70) ?>
            </div>
            <?php if(($count_tags = count($article['kwords']))>0) { ?>
        <div class="b-tags">
        	<span class="b-tags__txt">Темы:</span>
        	<ul class="b-tags__list">
        	   <?php foreach($article['kwords'] as $n=>$word) { ?> 
        		<li class="b-tags__item"><a class="b-tags__link" href="/articles/?tag=<?=$word['word_id']?>"><?=htmlspecialchars(reformat($word['name'], 25, 0, 1))?></a><?= ( ($count_tags != $n+1 )?", ":"")?></li>
        	   <?php }// foreach?>
        	</ul>
        </div>
        <?php } else {//if?>
        <div class="b-tags">
        	<span class="b-tags__txt" style="display:none">Темы:</span>
        	<ul class="b-tags__list">
        	</ul>
        </div>
        <?php }?>
        </div>
        
    </div>
	
    <?= ViewSocialButtons('articles', $article['title'], true)?>
	
    <div class="p-interview-in" style="padding-left:120px">
        <? if($article['approved'] == 't') include('comments.php'); ?>
    </div>
    <? if(hasPermissions('articles')) include('form.php'); ?>
    <div class="bnav-interview">
        <div class="interview-pager">
        <? if($show_nav) { ?>
            
            <? if(isset($navigation['prev'])) { 
                ?>
                <a class="b-layout__link" href="<?=getFriendlyURL('article', $navigation['prev']['id'])?>">&laquo; <?=($navigation['prev']['title'] ? (reformat($navigation['prev']['title'], 32, 0, 1)) : 'Без названия')?></a>
            <? } else { 
                /* ?>
                <span>&laquo; предыдущая статья</span>
                <? */
            } ?>
            &nbsp;&nbsp;&nbsp;
            <? if(isset($navigation['next'])) { ?>
                <a class="b-layout__link" href="<?=getFriendlyURL('article', $navigation['next']['id'])?>"><?=($navigation['next']['title'] ? (reformat($navigation['next']['title'], 32, 0, 1)) : 'Без названия')?> &raquo;</a>
            <? } else { 
                /* ?>
                <span>следующая статья &raquo;</span>
                <? */ 
            } ?>

        <? } ?>
        </div>
        <a id="articles_back2" class="b-layout__link" href="javascript:void(0);">Вернуться к списку статей</a>
    </div>
</div>
