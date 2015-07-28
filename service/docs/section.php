<?php include('xajax.php');?>

      <div class="docs-block c">
        <div class="docs-content c">
          <div class="docs-cnt">
            <div class="docs-breadcrumb"> <a class="b-layout__link" href="/service/docs/">Вернуться на главную</a> </div>
            <h3>
              <?= htmlspecialchars($section['name']);?>
            </h3>
            <? include('search_form.php');?>
            <?php if(is_array($docs) && count($docs)){ ?>
            <?php foreach($docs as $doc){ ?>
            <div class="docs-one">
              <h4><a class="b-layout__link" name="doc<?=$doc['id']?>"></a>
                <?= htmlspecialchars($doc['name']);?>
              </h4>
              <p><span class="d">Добавлен
                <?= date("d.m.Y",strtotime($doc['date_create']));?>
                <?php if($doc['date_update']){?>
                &nbsp;&nbsp;&nbsp;Обновлен
                <?= date("d.m.Y",strtotime($doc['date_update']));}?>
                </span></p>
              <p>
                <?= reformat(htmlspecialchars($doc['desc']));?>
              </p>
              <ul class="added-files-list">
                <? if(is_array($doc['attach']) && count($doc['attach'])) foreach($doc['attach'] as $file){?>
                <li class="<?= $file['ico_class'];?>"><a class="b-layout__link" href="<?= WDCPREFIX.$file['path'].$file['fname'];?>" target="_blank">
                  <?= $file['file_name'];?>
                  </a>&nbsp;&nbsp;<span><?php echo $file['file_size'];?> </span></li>
                <?}?>
              </ul>
            </div>
            <?} ?>
            <?}else{ //if ?>
            <div style="color:red; padding-bottom:100px;">Документов в данном разделе нет</div>
            <?php } ?>
          </div>
        </div>
      </div>
