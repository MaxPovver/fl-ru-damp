<h4>Загруженные реестры</h4>
<?php
if (count($data)) {
    foreach($data as $el) {
?>
<div class="b-layout b-layout__inner_bordtop_c6 b-layout__inner_padtb_20">
    <strong><?=date('d.m.y, H:i:s', strtotime($el['modified']))?></strong> &nbsp;&nbsp;&nbsp;&nbsp; 
    <a class="b-layout__link" href="<?=WDCPREFIX.$dir.$el['fname']?>">Реестр 3</a>
</div>
<?php
    }
} else { 
?>
<p>Ни одного файла реестра еще не создано</p>
<?php 

}

