<div class="b-layout b-layout_padleft_60 b-layout_padtop_20 b-layout__txt_padleft_null_iphone b-layout_bordbot_dedfe0" id="messages">
    <?php foreach ($messages as $message) { 
        $block_class = $message['is_read']=='f' && $message['reciever_id'] == get_uid(FALSE)
                ? 'b-layout_margbot_10 b-fon b-fon_bg_e4faeb b-fon_pad_10'
                : 'b-layout_margbot_20';
        $font_class = (($frl_id == $message['author_id']) || ($message['is_read']=='f' && $message['reciever_id'] == get_uid(FALSE))) 
                ? 'b-layout__link_color_000'
                : 'b-layout__link_color_6db335';  
    ?>
    <div class="b-layout <?php echo $block_class ?>">
        <div class="b-layout__txt">
            <a class="b-layout__link <?php echo $font_class ?> b-layout__link_bold" href="/users/<?php echo $message['login'] ?>">
                <?php 
                    echo $message['uname'] ? $message['uname'].' ' : '';
                    echo $message['usurname'] ? $message['usurname'].' ' : ''; 
                ?>
                [<?php echo $message['login']?>]</a> 
            [<?php echo date("d.m.Y | H.i", strtotime($message['sent'])); ?>]
        </div>
        <div class="b-layout__txt"><?php echo reformat($message['message'], 30, 0, -1) ?></div>
        <?php if (count($message['files'])) { ?>
        <div class="filesize1">
            <?php foreach ($message['files'] as $file) { ?>
            <div class="flw_offer_attach">
                <span class="b-icon b-icon_attach_png"></span> 
                <a class="b-layout__link" href="<?=WDCPREFIX.'/'.$file['path'].$file['fname']?>" target="_blank"><?=$file['original_name']?></a>
            </div>
            <?php } ?>
        </div>
        <?php } ?>
        <?php /*if (get_uid(FALSE) == $message['author_id']) { ?>
            <div class="b-layout__txt"><a class="b-layout__link b-layout__link_dot_c10600" href="#">Редактировать</a></div>
        <?php }*/ ?>
    </div>
    <?php } ?>
</div>