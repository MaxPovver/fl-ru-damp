<?php
$mark_array = array((string) $result['title'],
                    (string) $result['msgtext'],
                    (string) $result['login'],
                    (string) $result['uname'],
                    (string) $result['usurname']);  
list ($title, $msgtext, $login, $uname, $usurname) = $element->mark($mark_array);
if($result['parent_id'] !== NULL) {
    $result['pfx'] = 'comments';
}
?>
<div class="search-lenta-item c">
    <span class="number-item"><?= $i?>.</span>
    <div class="search-item-body">
<?
switch($result['pfx']) {
    case "commune":
        ?>
        <h3><a class="search-commune-h" href="/commune/?id=<?=$result['real_id']?>" target="_blank"><?= strip_tags($title, "<em>")?></a></h3>
        <p><?= reformat(strip_tags(deleteHiddenURLFacebook($msgtext), "<br>"), 28, 0, 1)?></p>
		<p>&hellip;</p>
        <?
        break;
    case "categories":
        $last = $element->last_mess[$result['id']];
        ?>
        <h3><a class="search-commune-h" href="/commune/?id=<?=$result['commune_id']?>" target="_blank"><?=$result['commune_name']?></a> <span class="search-arrow">&#8594;</span> <a class="search-commune-h" href="/commune/?id=<?=$result['commune_id']?>&cat=<?=$result['real_id']?>" target="_blank"><?= strip_tags($title, "<em>")?></a></h3>
        <?php if($last) {?>
            <?php foreach($last as $mess) { ?>
             <p class="search-bold"><a href="<?=getFriendlyURL('commune', $mess['id'])?>" target="_blank"><?= strip_tags($mess['title'], "<em>")?></a></p>
            <?php }//foreach;?>
        <?php }//if?>
        <?
        break;
    case "messages":
        $title = ($result['title']!=""?reformat(strip_tags($title, "<em><br>"), 40, 0, 1):"&lt;Без темы&gt;");
        ?>
        <h3><a class="search-commune-h" href="/commune/?id=<?=$result['commune_id']?>" target="_blank"><?=strip_tags($result['commune_name'], "<em>")?></a> <span class="search-arrow">&#8594;</span> <a href="/commune/?id=<?=$result['commune_id']?>&site=Topic&post=<?=$result['real_id']?>" target="_blank"><?=$title?></a></h3>
        <p><?= reformat(strip_tags(deleteHiddenURLFacebook($msgtext), "<br>"), 28, 0, 1)?></p>
		<p>&hellip;</p>
		<ul class="search-meta">
		  <li><?=$session->view_online_status($result['login'])?><a href="/users/<?= $result['login']?>/?f=<?=stat_collector::REFID_SEARCH?>&stamp=<?=$_SESSION['stamp']?>" target="_blank"><?= "{$uname} {$usurname}"?></a> [<a href="/users/<?= $result['login']?>/?f=<?=stat_collector::REFID_SEARCH?>&stamp=<?=$_SESSION['stamp']?>" target="_blank"><?=$login?></a>]</li>
          <li><?= date("&#160;[d.m.Y&#160;|&#160;H:i]", strtotime($result['post_time']));?></li>
         </ul>
        <?
        break;
    case "comments":
        $cls = is_emp($result['role'])?"empname11":"frlname11";
        $result['topic_name'] = ($result['topic_name']!=""?reformat(strip_tags($result['topic_name'], "<em><br>"), 40, 0, 1):"&lt;Без темы&gt;");
        ?>
        <h3><a class="search-commune-h" href="/commune/?id=<?=$result['commune_id']?>" target="_blank"><?=strip_tags($result['commune_name'], "<em>")?></a> <span class="search-arrow">&#8594;</span> <a href="/commune/?id=<?=$result['commune_id']?>&site=Topic&post=<?=$result['top_id']?>#c_<?=$result['real_id']?>" target="_blank"><?=$result['topic_name']?></a></h3>
        <a class="search-user-img" href="/users/<?= $result['login']?>/?f=<?=stat_collector::REFID_SEARCH?>&stamp=<?=$_SESSION['stamp']?>" target="_blank"><?=view_avatar($result['login'], $result['photo'], 1, 1, '')?></a>
        <div class="search-user-block">
            <span class="search-user-name"><?=$session->view_online_status($result['login'])?><span class="<?=$cls?>"><a href="/users/<?= $result['login']?>/?f=<?=stat_collector::REFID_SEARCH?>&stamp=<?=$_SESSION['stamp']?>" target="_blank"><?= "{$uname} {$usurname}"?></a> [<a href="/users/<?= $result['login']?>/?f=<?=stat_collector::REFID_SEARCH?>&stamp=<?=$_SESSION['stamp']?>" target="_blank"><?=$login?></a>]</span><?= date("&#160;[d.m.Y&#160;|&#160;H:i]", strtotime($result['post_time']));?></span>
            <p><?= reformat(strip_tags(deleteHiddenURLFacebook($msgtext), "<br>"), 28, 0, 1)?></p>
        </div>
        <?
        break;
    default: 
        break;
}
?>
    </div>
</div>
