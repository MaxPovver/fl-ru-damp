<? if($tags): $c = count($tags); foreach($tags as $k=>$val): ?>
	<a <?if($tag_id == $val['id']):?>href="/about/corporative/" style="font-size:150%;font-weight:bold;"<?else:?>href="/about/corporative/tags/<?=$val['id']?>/oblako/"<?endif;?>><?=$val['name']?></a><?=($c==$k+1?'':',')?>
<?endforeach; ?>
<? else: ?>
Тегов нет.
<? endif; ?>