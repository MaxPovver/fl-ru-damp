<form method="POST" action="<?=$url?>">
<?php foreach ($formData as $name => $value): ?>
    <input name="<?=$name?>" value="<?=$value?>" type="hidden" />
<?php endforeach; ?>
</form>