<?php
$mark_array = array((string) $result['title'],
                    (string) $result['login'],
                    (string) $result['uname'],
                    (string) $result['usurname'],
                    (string) strip_tags($result['msgtext']));

list ($title, $login, $uname, $usurname,  $message) = $element->mark($mark_array);
$real_id = ($result['is_interview'] == 1?($result['id']/2):(($result['id']-1)/2));
if($result['is_interview'] == 1) {
    $linkHref = "/interview/?id=$real_id";
} else {
    $linkHref = "/articles/?id=$real_id&p=1";
}
?>

<div class="search-lenta-item search-article c">
                        
                    	<span class="number-item"><?= $i?>.</span>
                        <span class="search-pic">
                            <a href="<?=$linkHref?>" target="_blank">
                                <img src="<?=WDCPREFIX?>/<?=$result['fpath']?><?=$result['logo']?>" alt="" width="100" />
                            </a>
                        </span>
                        <div class="search-item-body">
                        	<h3>
                        	<?php if($result['is_interview'] == 1){?>
                        	<a href="/interview/?id=<?= $real_id?>" target="_blank">Интервью. <?= "{$uname} {$usurname} [{$login}]"?></a>
                        	<?php } else { //if?>
                        	<a href="/articles/?id=<?= $real_id?>&p=1" target="_blank"><?= reformat(strip_tags($title, "<em><br>"), 40, 0, 1)?></a>
                        	<?php } //else?>
                        	</h3>
                            <p><?= reformat(strip_tags($message, "<em><br>"), 40, 0, 1)?></p>
                            <ul class="search-meta">
                            	<?php if($result['is_interview'] != 1){?><li>Автор: <b><a href="/users/<?= $result['login']?>/?f=<?=stat_collector::REFID_SEARCH?>&stamp=<?=$_SESSION['stamp']?>"><?= "{$uname} {$usurname} [{$login}]"?></a></b>  &#160;| &#160;</li><?php }//if?>
                            	<li><?=date("d.m.Y в H:i", strtotime($result['post_time']))?> <?= $result['comments_cnt'] !== NULL?"&#160;| &#160;":""?></li>
                            	<?php if($result['comments_cnt'] !== NULL) { ?>
                            	<li class="search-comm"><a href="/articles/?id=<?=$real_id?>&p=1" target="_blank"><?= $result['comments_cnt']?> <?=ending($result['comments_cnt'], "комментарий", "комментария", "комментариев")?></a></li>
                            	<?php }//if?>
                            </ul>
                        </div>
</div><!--/search-lenta-item-->