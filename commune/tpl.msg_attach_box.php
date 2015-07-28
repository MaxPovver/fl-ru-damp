<div id="attach">
    <div id="ad_button">
        <div>
            <div id="attaches" style="padding-bottom: 5px">
                <input type="file" name="attach[]" class="input-file" size="50">
                <span class="addButton" style="font-size: 16px;">&nbsp;</span>
            </div>
        </div>
        <? /*
          <input type="hidden" name="MAX_FILE_SIZE" value="<?=commune::MSG_FILE_MAX_SIZE?>"/>
          <input type="file" style="width:100%" name="file"/> */ ?>

        С помощью этого поля возможно загрузить:
        <ul>
            <li>Картинку: <?= commune::MSG_IMAGE_MAX_WIDTH ?>x<?= commune::MSG_IMAGE_MAX_HEIGHT ?> пикселей. <?= (commune::MSG_IMAGE_MAX_SIZE / 1024) ?> Кб. </li>
            <li>
                Вы можете прикрепить до <?=commune::MAX_FILES ?> файлов общим объемом не более <?=(commune::MAX_FILE_SIZE / (1024*1024))?> Мб.<br/>
                Файлы следующих форматов запрещены к загрузке: <?=implode(', ', $GLOBALS['disallowed_array'])?>
            </li>
        </ul>
    </div>
    <script type="text/javascript">
        new mAttach(document.getElementById('attaches'), <?= (commune::MAX_FILES - $max) ?>);
    </script>
    <br/><?= ($error ? view_error($error) . '<br/>' : '') ?>
</div>