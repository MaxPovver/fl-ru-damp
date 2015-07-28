<?php 
if(isset($buttons) && !empty($buttons)):
    foreach ($buttons as $key => $label):
    $attr = (isset($attrs[$key]))?$attrs[$key]:'';
?>
    <a <?=$attr?> href="/auth/?param=<?= $key ?>" class="b-auth_btn b-auth_btn_flat b-auth_btn_<?= $key ?> b-auth_btn_h40"><?= $label ?></a>
<?php    
    endforeach;
 endif; 
?>
<?php if($with_email): ?>
<span class="b-auth_btn b-auth_btn_txt b-auth_btn_marg_9_5">или <a href="/registration/">зарегистрироваться по e-mail</a></span>
<?php endif; ?>