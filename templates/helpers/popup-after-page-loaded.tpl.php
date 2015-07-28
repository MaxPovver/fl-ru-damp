<?php

/**
 * Шаблон всплывающего окошка после перезагрузки страницы
 */

?>
<div class="b-shadow b-shadow_block b-shadow_center b-shadow_width_520 b-shadow__quick">
    <div class="b-shadow__body b-shadow__body_pad_20">
        <?php if(isset($title)): ?>
        <h2 class="b-layout__title">
            <?=$title?>
        </h2>
        <?php endif; ?>
        <div class="b-layout__txt">
            <?=$message?>
        </div>
   </div>    
   <span class="b-shadow__icon b-shadow__icon_close"></span>
</div>