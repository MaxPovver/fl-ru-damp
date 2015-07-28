<h4>Ранее созданные реестры</h4>
<?php
if (count($data)) {
    foreach($data as $date_start => $subdata) {
        foreach ($subdata as $date_end => $info) {
?>
<div class="b-layout b-layout__inner_bordtop_c6 b-layout__inner_padtb_20">
    <strong><?=date('d.m.y, H:i:s', strtotime($info['date']))?></strong> &nbsp;&nbsp;&nbsp;&nbsp; 
    
    <span><?=date('Y-m-d H:i:s', $date_start)?> &mdash; <?=date('Y-m-d H:i:s', $date_end)?></span> &nbsp;&nbsp;&nbsp;&nbsp; 
    <?php $keys = array(1, 2, 3);
    foreach ($keys as $key) { 
        if (isset($info['reestr'.$key])) {
    ?>    
      <a class="b-layout__link" href="<?=WDCPREFIX.$dir.$info['reestr'.$key]?>">Реестр <?=$key?></a>&nbsp;
    <?php }} ?>
    </div>
<?php
        }
    }
} else { 
?>
<p>Ни одного файла реестра еще не создано</p>
<?php 

}

