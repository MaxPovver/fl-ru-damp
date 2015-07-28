<?php



?>
<div class="b-post b-post_bordtop_dfe3e4 b-post_padtop_15 b-post_marg_20_10" id="my_div_content_<?=$content_id?>_<?=$id?>_0">
   <?=$_parseHidden?>
   <?=$_parseOkIcon?>
   <?=$_parsePostTime?>
   <div class="b-username b-username_padbot_5"> 
        <?=$user_status?>
        <a class="b-username__link b-username__link_color_fd6c30 b-username__link_fontsize_11 b-username__link_bold" href="/users/<?=$login?>" target="_blank">
            <?=$user_fullname?>
        </a>     
   </div>
   <div class="b-username_padbot_5 <?=$warn_class?>"><?=$warn?></div>
   <?=$_parseMass?>
   <?=$content?>
   <?=$_parseDelIcons?> 
</div>