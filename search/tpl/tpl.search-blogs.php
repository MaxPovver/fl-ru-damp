<?
list ($title, $msgtext, $login, $uname, $usurname) = $element->mark(array((string) $result['title'], (string) $result['msgtext'], (string) $result['login'], (string) $result['uname'], (string) $result['usurname']));
$is_comment = ($result['reply_to'] !== NULL);
$is_comment?$result['pfx'] = 'comments':$result['pfx'] = 'messages';
?>

<div class="search-lenta-item c">
    <span class="number-item"><?= $i?>.</span>
    <div class="search-item-body">
<? switch($result['pfx']) {
        case "messages":
        $title = ($result['title']!=""?reformat(strip_tags($title, "<em><br>"), 40, 0, 1):"&lt;Без темы&gt;");
        ?>
        <h3><a class="search-commune-h" href="/blogs/viewgroup.php?gr=<?=$result['id_gr']?>" target="_blank"><?=$result['group_name']?></a> <span class="search-arrow">&#8594;</span> <a href="/blogs/view.php?tr=<?=$result['thread_id']?>" target="_blank"><?=$title?></a></h3>
        <p><?= reformat(strip_tags(deleteHiddenURLFacebook($msgtext), "<em><br>"), 28, 0, 1)?></p>
		<p>&hellip;</p>
		<ul class="search-meta">
		  <li><a href="/users/<?= $result['login']?>/?f=<?=stat_collector::REFID_SEARCH?>&stamp=<?=$_SESSION['stamp']?>" target="_blank"><?= "{$uname} {$usurname}"?></a> [<a href="/users/<?= $result['login']?>/?f=<?=stat_collector::REFID_SEARCH?>&stamp=<?=$_SESSION['stamp']?>" target="_blank"><?=$login?></a>]</li>
          <li><?= date("&#160;[d.m.Y&#160;|&#160;H:i]", strtotime($result['post_time']));?></li>
         </ul>
        <?
        break;
    case "comments":
        $cls = is_emp($result['role'])?"empname11":"frlname11";
        $result['title'] = ($result['title']!=""?reformat(strip_tags($result['title'], "<em><br>"), 40, 0, 1):"&lt;Без темы&gt;");
        $result['main_title'] = ($result['main_title']!=""?reformat(strip_tags($result['main_title'], "<em><br>"), 40, 0, 1):"&lt;Без темы&gt;");
        ?>
        <h3><a class="search-commune-h" href="/blogs/view.php?tr=<?=$result['thread_id']?>" target="_blank"><?=$result['main_title']?></a> <span class="search-arrow">&#8594;</span> <a href="/blogs/view.php?tr=<?=$result['thread_id']?>&openlevel=<?=$result['id']?>#o<?=$result['id']?>" target="_blank"><?=$result['title']?></a></h3>
        <a class="search-user-img" href="/users/<?= $result['login']?>/?f=<?=stat_collector::REFID_SEARCH?>&stamp=<?=$_SESSION['stamp']?>" target="_blank"><?=view_avatar($result['login'], $result['photo'], 1, 1, '')?></a>
        <div class="search-user-block">
            <span class="search-user-name"><span class="<?=$cls?>"><a href="/users/<?= $result['login']?>/?f=<?=stat_collector::REFID_SEARCH?>&stamp=<?=$_SESSION['stamp']?>" target="_blank"><?= "{$uname} {$usurname}"?></a> [<a href="/users/<?= $result['login']?>/?f=<?=stat_collector::REFID_SEARCH?>&stamp=<?=$_SESSION['stamp']?>" target="_blank"><?=$login?></a>]</span><?= date("&#160;[d.m.Y&#160;|&#160;H:i]", strtotime($result['post_time']));?></span>
            <p><?=reformat(strip_tags(deleteHiddenURLFacebook($msgtext), "<em><br>"), 28, 0, 1)?></p>
        </div>
        <?
        break;
    default: 
        break;
}?>
    </div><br /><br />
</div><!--/search-lenta-item-->