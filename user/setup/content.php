<? /*if($template == 'template2.php') { ?>
 	
 	<h1 class="b-page__title">Настройки</h1>
 	<div class="acc-settings">
 	  <? include($fpath."info2.php") ?>
 	  <? include($fpath."usermenu.php") ?>
 		<?  if ($inner) include ($fpath.$inner); else print("&nbsp;")?>
   </div>
   
<? 
  return;
  } */
?>
<div class="page-profile">
    <h1 class="b-page__title">Настройки профиля</h1>
    
    <?php if ($alert_message): ?>
        <div class="b-fon b-fon_inline-block b-fon_padbot_20">
            <div class="b-fon__body b-fon__body_pad_15  b-fon__body_padleft_30 b-fon__body_lineheight_18 b-fon__body_padright_40 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf">
                <span class="b-icon b-icon_sbr_gok b-icon_margleft_-25"></span><?php echo $alert_message?> 
            </div>
        </div>        
    <?php endif; ?>
    <div class="b-layout b-layout_padbot_20">
       <? include ($fpath."info.php")?>
    </div>
    <? include ($fpath."usermenu.php")?>
    <?  if ($inner) include ($fpath.$inner); else print("&nbsp;")?>
    <?php /* if(!is_emp($user->role)) printBanner240(is_pro() ,0,$g_page_id); */ ?>
</div>