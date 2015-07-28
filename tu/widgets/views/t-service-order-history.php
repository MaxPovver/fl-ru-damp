<?php if (count($history)) { ?>
<div class="b-layout b-layout_padleft_60 b-layout_padtop_20 b-layout__txt_padleft_null_iphone">
   <h3 class="b-layout__h3 b-layout__h3_color_808080 b-layout__h3_padbot_20">История изменений заказа</h3>
   <div class="b-layout__txt b-layout__txt_color_808080">
      <?php foreach($history as $event) { ?>
      <?=date('d.m.Y в H:i', strtotime($event['date']))?> - <?=reformat($event['description'], 60, 0, 0, 1)?><br/>
      <?php } ?>
   </div>
</div>
<?php } ?>