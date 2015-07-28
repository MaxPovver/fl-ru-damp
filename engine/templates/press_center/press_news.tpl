{{include "header.tpl"}}
<div class="body clear">
    <div class="main  clear">
        <h2>Пресс-центр</h2>
        <div class="rcol-big">
            <div class="press-center clear">            
                {{include "press_center/press_menu.tpl"}}
                <div class="pc-content">
                    <? if(hasPermissions('about')) { ?><div style="float:right;">[<a href="javascript:void(0);" onclick="admin.openPopup('news', 0); return false;">Добавить новость</a>]</div><? } ?>
                    <h3>Архив новостей</h3>
                    <ul class="pc-na clear">
                        <? if($$years) foreach($$years as $year) : ?>
                            <? if($$selected_year == $year): ?>
                                <li><strong><?=$year;?></strong></li>
                            <? else: ?>
                                <li><a href="/press/news/year/<?=$year;?>/"><?=$year;?></a></li>
                            <? endif; ?>
                        <? endforeach; ?>
                    </ul>
                    <ul class="pc-news-list">
                        <? if($$news) foreach($$news as $news_one) : ?>
                        <li>
                            <strong><?=$news_one["post_date"];?></strong><br />
                            <a href="/press/news/<?=$news_one["id"];?>/"><?=stripslashes($news_one["header"]);?></a>
                                <? if(hasPermissions('about')) { ?>
                                    <a href="javascript:void(0);" onclick="admin.openPopup('news', <?=$news_one["id"];?>);"><img  src="/images/ico_edit_news.gif" alt="Редактировать новость" style="height:19px; width:20px; border:0; vertical-align:middle" /></a>
                                    <a href="javascript:void(0);" onclick="admin.loadAndExec('newsList', 'newsClass.deleteItem', [<?=$news_one["id"];?>, function() {admin.reload()}]);"><img style="height:19px; width:20px; border:0; vertical-align:middle" src="/images/ico_delete_news.gif" alt="Удалить новость"/></a>
                                <? } ?>
                        </li>
                        <? endforeach; ?>
                        <? if($$one_news) : ?>
                            <div style="float:right;">[<a href="javascript:void(0);" onclick="history.go(-1);"><strong style="font-weight:bold;">Назад</strong></a>]</div>
                            <? if(hasPermissions('about')) { ?><div style="float:right;">[<a href="javascript:void(0);" onclick="admin.openPopup('news', <?=$$one_news["id"];?>);">Редактировать новость</a>]</div><? } ?>
                            <h3><?=stripslashes($$one_news["header"]);?></h3>
                            <?=stripslashes($$one_news["n_text"]);?>
                        <? endif; ?> 
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
{{include "footer.tpl"}}
