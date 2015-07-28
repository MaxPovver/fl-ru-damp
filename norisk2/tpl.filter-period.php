<? 
if(!isset($name_element)) $name_element = 'filter';
for($i=0;$i<2;$i++) { $d = $i?'to':'from'; ?>
    <?=($i ? '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;' : '')?>
    <select name="<?= $name_element; ?>[<?=$d?>][day]">
      <option value="0">+&infin;</option>
      <? if($filter[$d]['day']) { ?><option value="<?=(int)$filter[$d]['day']?>" selected="selected"></option><? } ?>
    </select>
    <select name="<?= $name_element; ?>[<?=$d?>][month]" onchange="SBR.fillDays('<?=$d?>', '<?= $name_element; ?>')">
      <option value="0"></option>
      <? foreach($MONTHA as $n=>$m) { ?>
        <option value="<?=$n?>"<?=($n==$filter[$d]['month'] ? ' selected="selected"' : '')?>><?=$m?></option>
      <? } ?>
    </select>
    <select name="<?= $name_element; ?>[<?=$d?>][year]" onchange="SBR.fillDays('<?=$d?>', '<?= $name_element; ?>')">
      <option value="0"></option>
      <? for($y=2008;$y<=date('Y');$y++) { ?>
        <option value="<?=$y?>"<?=($y==$filter[$d]['year'] ? ' selected="selected"' : '')?>><?=$y?></option>
      <? } ?>
    </select>
<? } ?>
<script type="text/javascript">
window.addEvent('domready', function() { 
    if(window.SBR) {
        SBR.fillDays('from', '<?= $name_element; ?>');
        SBR.fillDays('to', '<?= $name_element; ?>');
    }
} );
</script>
