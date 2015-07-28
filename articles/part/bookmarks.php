<? if(!$is_ajax) {

    switch($order) {
        case 'time':
            $order = 'дате';
            break;
        case 'priority':
            $order = 'важности';
            break;
        case 'title':
            $order = 'алфавиту';
            break;
    }
?>
<div class="favorites">
    <h3>Закладки</h3>
    <div class="fav-sort ">
        <strong>Сортировка по</strong>
        <div>
            <a href=""><span><?=$order?></span> <img src="/images/ico_fav_arrow.gif" alt="" /></a>
            <ul style="display: none;">
                <li><a href="">дате</a></li>
                <li><a href="">важности</a></li>
                <li><a href="">алфавиту</a></li>
            </ul>
        </div>
    </div>
    <ul class="fav-list">
<? } ?>
        <? if($bookmarks) foreach($bookmarks as $b) { ?>
        <li id="fav-<?=$b['article_id']?>">
            <input type="hidden" value="<?=!$b['bookmark_title'] ? (!$b['title'] ? 'Без названия' : $b['title'] ) : $b['bookmark_title']?>" />
            <img src="/images/ico_star_<?=$b['bookmark']-1?>.gif" alt="" />
            <span>
                <a class="b-layout__link" href="?id=<?=$b['article_id']?>">
                    <?=!$b['bookmark_title'] ? (!$b['title'] ? 'Без названия' : reformat($b['title'],17, 0, 1) ) : reformat($b['bookmark_title'], 17, 0, 1)?></a>
                <em>
                    <img style="cursor: pointer;" src="/images/ico_close2.gif" onclick="deleteBookmark(<?=$b['article_id']?>)" title="Удалить" alt="Удалить" />&nbsp;&nbsp;<img style="cursor: pointer;" src="/images/ico_edit2.gif" onclick="editBookmark(<?=$b['article_id']?>)" title="Редактировать" alt="Редактировать" />
                </em>
            </span>
        </li>
        <? } ?>
    </ul>
<? if(!$is_ajax) { ?>
    <ul class="fav-list-tpl fav-list">
        <li class="no-bookmarks" style="display: <?=!$bookmarks ? '' : 'none' ?>">Нет закладок</li>
        <li class="fav-one-edit c" style="display: none;">
            <form action="">
                <ul class="post-f-fav-sel">
                    <li><img src="/images/ico_star_0_empty.gif" alt="" /></li>
                    <li><img src="/images/ico_star_1_empty.gif" alt="" /></li>
                    <li><img src="/images/ico_star_2_empty.gif" alt="" /></li>
                    <li><img src="/images/ico_star_3_empty.gif" alt="" /></li>
                </ul>
                <div class="fav-one-edit-txt">
                    <textarea rows="3" cols="7"></textarea>
                    <div class="fav-one-edit-btns"><input onclick="saveBookmark(this)" type="button" value="Сохранить" /> <input onclick="favCancelEdit('favCancelEditInp')" id="favCancelEditInp" type="button" value="Отмена" /></div>
                </div>
            </form>
        </li>
    </ul>
</div>
<? } ?>