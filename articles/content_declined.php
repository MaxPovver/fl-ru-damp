
<?php
$crumbs = array();
$crumbs[] = array("title"=>"Статьи и интервью", "url"=>"/articles/");
$crumbs[] = array("title"=>"Отклоненные", "url"=>"");
?>
<div class="b-menu b-menu_crumbs  b-menu_padbot_20"><?=getCrumbs($crumbs)?></div>

<? include($mpath . '/tabs.php'); ?>
<div class="page-articles">
    <? include($mpath . '/tpl.sorting.php'); ?>
    <div class="p-articles-in c">
        <div class="p-a-cnt b-layout__right b-layout__right_relative b-layout__right_width_72ps b-layout__right_float_right">
            <? if($articles) foreach($articles as $article) { ?>
            <div class="post-one" id="post_<?=$article['id']?>">
                <img src="<?=WDCPREFIX?>/<?=$article['path']?><?=$article['fname']?>" alt="" width="100" class="post-img" />
                <div class="post-txt">
                    <h3><a href="<?=getFriendlyURL('article', $article['id'])?>"><?=!$article['title'] ? 'Без названия' : reformat($article['title'], 32, 0, 1) ?></a></h3>
                    <p class="post-body">
                        <?= reformat($article['short'], 50, 0, 0, 1) ?></p>
                </div>
                <div class="post-f c">
                    <ul>
                        <li class="post-f-lnks">
                            <ul>
                                <li class="first">
                                    <? if(hasPermissions('articles')) { ?>
                                    <a href="javascript:void(0)" style="color: #A23E3E;" onclick="editArticle(<?=$article['id']?>)">Редактировать</a>
                                    &nbsp;|&nbsp;
                                    <a href="javascript:void(0)" style="color: #A23E3E;" onclick="delArticleForm(<?=$article['id']?>);">Удалить</a>
                                    &nbsp;|&nbsp;
                                    <? } ?>
                                    <? if($article['approved'] == 'f' && (hasPermissions('articles'))) { ?>
                                    <a href="/articles/?id=<?=$article['id']?>&task=undecline" style="color: #A23E3E;" onclick="return (confirm('Вы уверены?'));">На модерацию</a>
                                    &nbsp;|&nbsp;
                                    <a href="/articles/?id=<?=$article['id']?>&task=approve" onclick="return (confirm('Вы уверены?'));">Подтвердить</a>
                                    <? } ?>
                                </li>
                            </ul>
                        </li>

                        <li class="post-f-date">
                            <?=date('d.m.Y в H:i', strtotime($article['post_time']))?>
                        </li>
                        <li class="post-f-autor">
                            <a href="/users/<?=$article['login']?>">
                <?= $article['uname'] . ' ' . $article['usurname'] . ' [' . $article['login'] . ']'?>
                            </a>
														<span class="post-f-autor-grad"></span>
                        </li>
                    </ul>
                </div>
            </div>
            <? } ?>
            
            <?=new_paginator($page, $pages, 3, "%s?".urldecode(url('ord,p,page', array('p' => '%d')))."%s")?>
            <? if(hasPermissions('articles')) include('form.php'); ?>
        </div>
        <div class="p-a-left b-layout__left b-layout__left_width_25ps">
            <div class="p-a-popular c">
            </div>
            <div class="favorites">
            </div>
            <!-- Banner 240x400 -->
            <div class="banner_240x400">
                <?= printBanner240(is_pro(), true); ?>
            </div>
            <!-- end of Banner 240x400 -->
        </div>
    </div>
</div>

<div id="del-article-form" class="form fs-o form-adel" style="display: none;">
    <b class="b1"></b>
    <b class="b2"></b>
    <div class="form-in">
                    <form id="del_article_frm" method="post" action="/articles/?task=del-article">
        <div class="form-block first last">
                <h4>Удаление статьи</h4>
            <div class="form-el">
                <label class="form-label2">Укажите причину отказа в публикации (для автора):</label>
                <div class="form-value">
                        <input type="hidden" name="id" value="" />
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
