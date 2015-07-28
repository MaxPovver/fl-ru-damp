<?php include('xajax.php');?>
<h2>Услуги</h2>
<div class="docs-block c">
    <div class="docs-content c">
        <div class="docs-cnt">
            <div class="docs-breadcrumb">
                <a href="/service/docs/">Вернуться на главную</a>
            </div>
            <h3><?= htmlspecialchars($section['name']);?></h3>
            <? include('search_form.php');?>
            <?php if(is_array($doc) && count($doc)){ ?>
            
            <div class="docs-one">
                <h4><?= htmlspecialchars($doc['name']);?></h4>
                <p><span class="d">Добавлен <?= date("d.m.Y",strtotime($doc['date_create']));?><?php if($doc['date_update']){?>&nbsp;&nbsp;&nbsp;Обновлен <?= date("d.m.Y",strtotime($doc['date_update']));}?></span></p>
                <p><?= htmlspecialchars($doc['desc']);?></p>
                <ul class="added-files-list">
                    <? if(is_array($doc['attach']) && count($doc['attach'])) foreach($doc['attach'] as $file){?>
                    <li class="<?= $file['ico_class'];?>"><a href="<?= WDCPREFIX.$file['path'].$file['fname'];?>"><?= $file['file_name'];?></a>&nbsp;&nbsp;<span><?php echo $file['file_size'];?></span></li>
                    <?}?>
                </ul>
            </div>
           <?}else{ //if ?>
            <div style="color:red">Страница не найдена</div>
            <?php } ?>
        </div>
    </div>
</div>
