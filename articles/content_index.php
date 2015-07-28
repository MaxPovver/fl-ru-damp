<?php
/**
 * Главная страница, список статей
 */
$sorting = array(
    'date' => 'по дате добавления',
    'comm' => 'по количеству комментариев',
    'views' => 'по количеству просмотров',
    'rating' => 'по оценке',
);
?>

<?php
$crumbs = array();
$crumbs[] = array("title"=>"Статьи и интервью", "url"=>"/articles/");
$crumbs[] = array("title"=>"Статьи", "url"=>"");
?>
<div class="b-menu b-menu_crumbs  b-menu_padbot_20"><?=getCrumbs($crumbs)?></div>



<? include($mpath . '/tabs.php'); ?>
<div class="page-articles">
    <? include($mpath . '/tpl.sorting.php'); ?>
    <div class="p-articles-in c">
        <div class="p-a-cnt b-layout__right b-layout__right_relative b-layout__right_width_72ps b-layout__right_float_right">
            <?php if($is_approved) { ?>
						<div class="b-fon b-fon_margbot_20">
								<div class="b-fon__body b-fon__body_pad_10 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf b-fon__body_bordbot_dfedcf">
								Ваша статья была отправлена на модерацию.  Срок прохождения модерации &mdash; 1 неделя.
							</div>
						</div>
			<?php }//if?>
		    <?php if(isset($_GET['tag'])) { ?>
            <h4>Статьи по теме «<?= htmlspecialchars($tag_name)?>»</h4>		
			<p><a href="/articles/">Показать все</a></p><br/>
			<?php }//if?>
            <? if($articles) foreach($articles as $article) {
                $classname = $article['rating'] < 0 ? 'pr-minus' : ($article['rating'] >= 1 ? 'pr-plus' : '') ;
                ?>
            <div class="post-one" id="post_<?=$article['id']?>">
                <div class="post-rate" id="rate_<?=$article['id']?>">
                    <? if($uid && $uid != $article['user_id']) { ?>
                    <a href="?page=rate&id=<?=$article['id']?>&to=down"><img src="/images/btn-drate<?=$article['rate_value'] < 0 ? '-dis' : ''?>.png" alt="" /></a>
                    <? } else { ?>
                    <img src="/images/btn-drate-dis.png" alt="" />
                    <? } ?>
                    <span class="post-rate-val <?=$classname?>">
                        <?= ($article['rating'] > 0 ? '+' : '') . intval($article['rating'])?>
                    </span>
                    <? if($uid && $uid != $article['user_id']) { ?>
                    <a href="?page=rate&id=<?=$article['id']?>&to=up"><img src="/images/btn-urate<?=$article['rate_value'] > 0 ? '-dis' : ''?>.png" alt="" /></a>
                    <? } else { ?>
                    <img src="/images/btn-urate-dis.png" alt="" />
                    <? } ?>
                </div>
                <? if ($article['fname']) { ?>
                    <img src="<?=WDCPREFIX?>/<?=$article['path']?><?=$article['fname']?>" alt="" width="100" class="post-img" />
                <? } ?>
                <div class="post-txt">
                    <h3><a class="b-layout__link" href="<?=getFriendlyURL('article', $article['id'])?><?= ($ord ? "?ord=$ord" : ""). ($page ? "&p=$page" : "")?>"><?=!$article['title'] ? 'Без названия' : (reformat($article['title'], 32, 0, 1)) ?></a></h3>
                    <p class="post-body">
                        <?= (reformat($article['short'], 50, 0, 0, 1)) ?>
                        <?//= $article['short'] ?>
                    </p>
                </div>
                <div class="post-f c">
                	<table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
                    	<tr class="b-layout__tr">
                        	<td class="b-layout__one b-layout__one_width_10">
                                <div class="post-f-fav" id="post-fav-<?=$article['id']?>">
                                <? if($uid) { ?>
                                    <a href="/articles/?id=<?=$article['id']?>">
                                        <? $star = intval($article['bookmark']) == 0 ? '0_empty' : ($article['bookmark'] != 1 ? intval($article['bookmark'])-1 : 0); ?>
                                        <img src="/images/ico_star_<?=$star?>.gif" alt="" />
                                    </a>
                                <? } ?>
                                </div>
                            </td>
                        	<td class="b-layout__one b-layout__one_width_60">
                                <div class="post-f-date">
                                    <?=date('d.m.Y в H:i', strtotime($article['approve_date']))?>
                                </div>
                            
                            </td>
                        	<td class="b-layout__one b-layout__one_padright_10">
                                <div style=" position:relative; overflow:hidden; white-space:nowrap;" >
                                    <a href="/users/<?=$article['login']?>" title="<?=$article['uname'] . ' ' . $article['usurname'] ?>">
                                        <?=' [' . $article['login'] . ']'?>
                                    </a>
                                    <span class="post-f-autor-grad"></span>
                                </div>
                            </td>
                        	<td class="b-layout__one b-layout__one_padright_10 b-layout__one_width_100">
                            	<div style="float:right; white-space:nowrap;">
									<? if(hasPermissions('articles')) { ?>
                                    <a href="javascript:void(0)" style="color: #A23E3E;" onclick="editArticle(<?=$article['id']?>)">Редактировать</a>
                                    &nbsp;|&nbsp;
                                    <a href="/articles/?task=del-article&id=<?=$article['id']?>" style="color: #A23E3E;" onclick="return (confirm('Вы уверены?'));">Удалить</a>
                                    &nbsp;|&nbsp;
                                    <? } ?>

                                    <a href="<?=getFriendlyURL('article', $article['id'])?>" style="<?=$article['comments_cnt']>0 && $article['lastviewtime'] === NULL ? 'font-weight:bold;' : ''?>">Комментарии (<?=$article['comments_cnt']?>)</a>
                                    <? if($uid && intval($article['comments_unread']) > 0 && $article['lastviewtime'] !== NULL) { ?>
                                    <a href="/articles/?id=<?=$article['id']?>#unread" style="color:#6BA813; font-weight:bold;">
                                        (<?=$article['comments_unread']?> <?=ending($article['comments_unread'], 'новый', 'новых', 'новых')?>)
                                    </a>
                                    <? } ?>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            <? } ?>
            
            <?=new_paginator($page, $pages, 3, "%s?".urldecode(url('ord,p,page', array('p' => '%d')))."%s")?>
            <? if(hasPermissions('articles')) include('form.php'); ?>
        </div>
        <div class="p-a-left b-layout__left b-layout__left_width_25ps">
            <div class="p-a-popular">
            <? if($authors) { ?>
                <h3>Популярные авторы</h3>
                <ul>
                    <? foreach($authors as $author) { ?>
                    <li>
                        <a href="/users/<?=$author['login']?>">
                            <?=view_avatar_info($author['login'], $author['photo'], 1)?>
                        </a>
                    </li>
                    <? } ?>
                </ul>
            <? } ?>
            </div>
            <? if($uid) { include ('part/bookmarks.php'); } ?>
            <?php if(count($pop_tags)>0) { ?>
            <div class="b-menu b-menu_vertical b-menu_padbot_10 b-menu_clear_left b-menu_padtop_10">
                <h3 class="b-menu__title b-menu__title_padbot_10">Темы</h3>
                <ul class="b-menu__list">
                    <?php foreach($pop_tags as $nm=>$tag) { ?>
                    <li class="b-menu__item b-menu__item_padbot_5"><div class="b-menu__b1"><div class="b-menu__number"><?= intval($tag['cnt'])?></div></div><a class="b-menu__link" href="?tag=<?=$tag['word_id']?>"><?= htmlspecialchars(reformat($tag['name'], 25, 0, 1))?></a></li>
                    <?php }//foreach?>
                </ul>
            </div>
            <?php }//if?>
            <!-- Banner 240x400 -->
                <?= printBanner240(false, true); ?>
            <!-- end of Banner 240x400 -->
        </div>
    </div>
</div>
