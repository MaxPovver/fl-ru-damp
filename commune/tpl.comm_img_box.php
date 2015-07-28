<div>
    <input type="hidden" name="MAX_FILE_SIZE" value="<?= commune::FILE_MAX_SIZE ?>"/>
    <input type="file" style="width:100%" name="file" class="i-file"/>
    <?= ($error ? view_error($error) : '') ?>
<!--    <br/>
    gif, jpeg. <?= commune::IMAGE_MAX_WIDTH ?>x<?= commune::IMAGE_MAX_HEIGHT ?> пикселей.-->
</div>