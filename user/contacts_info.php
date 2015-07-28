<?php
$info_for_reg = unserialize($user->info_for_reg);
$is_first = true;
$maxlength = 35;
if ($user->is_pro == 't') {
    $maxlength = 55;
}
$uid = get_uid(false);
//if ( $user->uid == $uid ) {
    $direct_external_links = $_SESSION['direct_external_links'];
    $_SESSION['direct_external_links'] = 1;
//}
?>
<?php if ($user->site && !($info_for_reg['site'] && !$uid)) { 
            if ( !preg_match("/^[a-z]{3,5}\:\/\//", $user->site) ) {
                $site = 'http://' . $user->site;
            } else {
                $site = $user->site;
            }
        ?>
<?php if($user->uid != $uid) { ?>
<div class="b-layout__hover_bg_f0ffdf b-layout_padlr_10 b-layout_pad_3 b-layout_margbot_3">
   <div class="b-layout__txt b-layout__txt_padleft_25 b-layout__txt_lineheight_1">
      <span class="b-icon b-icon__cont b-icon__cont_www b-icon_margleft_-25"></span>
      <div class="b-layout__txt b-layout__txt_valign_top b-layout__txt_lineheight_1 b-layout__txt_ellipsis b-layout_inline-block b-layout__txt_width_225"><?= reformat($site, 0, 0, 0, 0, 80)?></div>
   </div>
</div>
<?php } else { //if?>
<div class="b-layout__hover_bg_f0ffdf b-layout_padlr_10 b-layout_pad_3 b-layout_margbot_3">
   <div class="b-layout__txt b-layout__txt_padleft_25 b-layout__txt_lineheight_1">
      <span class="b-icon b-icon__cont b-icon__cont_www b-icon_margleft_-25"></span>
      <div class="b-layout__txt b-layout__txt_valign_top b-layout__txt_lineheight_1 b-layout__txt_ellipsis b-layout_inline-block b-layout__txt_width_225"><?= reformat($site,0,0,0,0,80) ?></div>
   </div>
</div>
<?php } //else?>
<?php  $is_first = false;  } ?>
<?php if ($user->site_1 && !($info_for_reg['site'] && !$uid)) {
            if ( !preg_match("/^[a-z]{3,5}\:\/\//", $user->site_1) ) {
                $site = 'http://' . $user->site_1;
            } else {
                $site = $user->site_1;
            }
        ?>
<?php if($user->uid != $uid) { ?>
<div class="b-layout__hover_bg_f0ffdf b-layout_padlr_10 b-layout_pad_3 b-layout_margbot_3">
   <div class="b-layout__txt b-layout__txt_padleft_25 b-layout__txt_lineheight_1">
        <span class="b-icon b-icon__cont b-icon__cont_www b-icon_margleft_-25"></span>
        <div class="b-layout__txt b-layout__txt_valign_top b-layout__txt_lineheight_1 b-layout__txt_ellipsis b-layout_inline-block b-layout__txt_width_225"><?= reformat($site, 0, 0, 0, 0, 80)?></div>
   </div>
</div>
<?php } else { //if?>
<div class="b-layout__hover_bg_f0ffdf b-layout_padlr_10 b-layout_pad_3 b-layout_margbot_3">
   <div class="b-layout__txt b-layout__txt_padleft_25 b-layout__txt_lineheight_1">
        <span class="b-icon b-icon__cont b-icon__cont_www b-icon_margleft_-25"></span>
        <div class="b-layout__txt b-layout__txt_valign_top b-layout__txt_lineheight_1 b-layout__txt_ellipsis b-layout_inline-block b-layout__txt_width_225"><?= reformat($site,0,0,0,0,80) ?></div>
   </div>
</div>
<?php }//else?>
<?php  $is_first = false;  } ?>
<?php if ($user->site_2 && !($info_for_reg['site'] && !$uid)) {
            if ( !preg_match("/^[a-z]{3,5}\:\/\//", $user->site_2) ) {
                $site = 'http://' . $user->site_2;
            } else {
                $site = $user->site_2;
            }
        ?>
<?php if($user->uid != $uid) { ?>
<div class="b-layout__hover_bg_f0ffdf b-layout_padlr_10 b-layout_pad_3 b-layout_margbot_3">
   <div class="b-layout__txt b-layout__txt_padleft_25 b-layout__txt_lineheight_1">
      <span class="b-icon b-icon__cont b-icon__cont_www b-icon_margleft_-25"></span>
      <div class="b-layout__txt b-layout__txt_valign_top b-layout__txt_lineheight_1 b-layout__txt_ellipsis b-layout_inline-block b-layout__txt_width_225"><?= reformat($site, 0, 0, 0, 0, 80)?></div>
   </div>
</div>
<?php } else { //if?>
<div class="b-layout__hover_bg_f0ffdf b-layout_padlr_10 b-layout_pad_3 b-layout_margbot_3">
   <div class="b-layout__txt b-layout__txt_padleft_25 b-layout__txt_lineheight_1">
      <span class="b-icon b-icon__cont b-icon__cont_www b-icon_margleft_-25"></span>
      <div class="b-layout__txt b-layout__txt_valign_top b-layout__txt_lineheight_1 b-layout__txt_ellipsis b-layout_inline-block b-layout__txt_width_225"><?= reformat($site, 0, 0, 0, 0, 80) ?></div>
   </div>
</div>
<?php }//else?>
<?php  $is_first = false;  } ?>
<?php if ($user->site_3 && !($info_for_reg['site'] && !$uid)) { 
            if ( !preg_match("/^[a-z]{3,5}\:\/\//", $user->site_3) ) {
                $site = 'http://' . $user->site_3;
            } else {
                $site = $user->site_3;
            }
        ?>
<?php if($user->uid != $uid) { ?>
<div class="b-layout__hover_bg_f0ffdf b-layout_padlr_10 b-layout_pad_3 b-layout_margbot_3">
   <div class="b-layout__txt b-layout__txt_padleft_25 b-layout__txt_lineheight_1">
        <span class="b-icon b-icon__cont b-icon__cont_www b-icon_margleft_-25"></span>
        <div class="b-layout__txt b-layout__txt_valign_top b-layout__txt_lineheight_1 b-layout__txt_ellipsis b-layout_inline-block b-layout__txt_width_225"><?= reformat($site, 0, 0, 0, 0, 80)?></div>
   </div>
</div>
<?php } else { //if?>
<div class="b-layout__hover_bg_f0ffdf b-layout_padlr_10 b-layout_pad_3 b-layout_margbot_3">
   <div class="b-layout__txt b-layout__txt_padleft_25 b-layout__txt_lineheight_1">
        <span class="b-icon b-icon__cont b-icon__cont_www b-icon_margleft_-25"></span>
        <div class="b-layout__txt b-layout__txt_valign_top b-layout__txt_lineheight_1 b-layout__txt_ellipsis b-layout_inline-block b-layout__txt_width_225"><?= reformat($site, 0, 0, 0, 0, 80)?></div>
   </div>
</div>
<?php }//else?>
<?php $_SESSION['direct_external_links'] = $direct_external_links; ?>
<?php  $is_first = false;  } ?>
<?php if ($user->icq && !($info_for_reg['icq'] && !$uid)) { ?>
<div class="b-layout__hover_bg_f0ffdf b-layout_padlr_10 b-layout_pad_3 b-layout_margbot_3">
   <div class="b-layout__txt b-layout__txt_padleft_25 b-layout__txt_lineheight_1">
        <span class="b-icon b-icon__cont b-icon__cont_icq b-icon_top_-1 b-icon_margleft_-25"></span>
        <span class="b-layout__txt b-layout__txt_valign_top b-layout__txt_lineheight_15 b-layout__txt_ellipsis b-layout_inline-block b-layout__txt_width_225" title="<?= $user->icq ?>">
        <? echo LenghtFormatEx($user->icq, $maxlength); ?>
        </span>
   </div>
</div>
<?php  $is_first = false;  } ?>
<?php if ($user->icq_1 && !($info_for_reg['icq_1'] && !$uid)) { ?>
<div class="b-layout__hover_bg_f0ffdf b-layout_padlr_10 b-layout_pad_3 b-layout_margbot_3">
   <div class="b-layout__txt b-layout__txt_padleft_25 b-layout__txt_lineheight_1">
        <span class="b-icon b-icon__cont b-icon__cont_icq b-icon_top_-1 b-icon_margleft_-25"></span>
        <span class="b-layout__txt b-layout__txt_valign_top b-layout__txt_lineheight_15 b-layout__txt_ellipsis b-layout_inline-block b-layout__txt_width_225" title="<?= $user->icq_1 ?>">
        <? echo LenghtFormatEx($user->icq_1, $maxlength); ?>
        </span>
   </div>
</div>
<?php  $is_first = false;  } ?>
<?php if ($user->icq_2 && !($info_for_reg['icq_2'] && !$uid)) { ?>
<div class="b-layout__hover_bg_f0ffdf b-layout_padlr_10 b-layout_pad_3 b-layout_margbot_3">
   <div class="b-layout__txt b-layout__txt_padleft_25 b-layout__txt_lineheight_1">
      <span class="b-icon b-icon__cont b-icon__cont_icq b-icon_top_-1 b-icon_margleft_-25"></span>
      <span class="b-layout__txt b-layout__txt_valign_top b-layout__txt_lineheight_15 b-layout__txt_ellipsis b-layout_inline-block b-layout__txt_width_225" title="<?= $user->icq_2 ?>">
      <? echo LenghtFormatEx($user->icq_2, $maxlength); ?>
      </span>
   </div>
</div>
<?php  $is_first = false;  } ?>
<?php if ($user->icq_3 && !($info_for_reg['icq_3'] && !$uid)) { ?>
<div class="b-layout__hover_bg_f0ffdf b-layout_padlr_10 b-layout_pad_3 b-layout_margbot_3">
   <div class="b-layout__txt b-layout__txt_padleft_25 b-layout__txt_lineheight_1">
      <span class="b-icon b-icon__cont b-icon__cont_icq b-icon_top_-1 b-icon_margleft_-25"></span>
      <span class="b-layout__txt b-layout__txt_valign_top b-layout__txt_lineheight_15 b-layout__txt_ellipsis b-layout_inline-block b-layout__txt_width_225" title="<?= $user->icq_3 ?>">
      <? echo LenghtFormatEx($user->icq_3, $maxlength); ?>
      </span>
   </div>
</div>
<?php  $is_first = false;  } ?>
<?php if ($user->jabber && !($info_for_reg['jabber'] && !$uid)) { ?>
<div class="b-layout__hover_bg_f0ffdf b-layout_padlr_10 b-layout_pad_3 b-layout_margbot_3">
   <div class="b-layout__txt b-layout__txt_padleft_25 b-layout__txt_lineheight_1">
        <span class="b-icon b-icon__cont b-icon__cont_jb b-icon_margleft_-25"></span>
        <span class="b-layout__txt b-layout__txt_valign_top b-layout__txt_lineheight_15 b-layout__txt_ellipsis b-layout_inline-block b-layout__txt_width_225" title="<?= $user->jabber ?>"><? echo LenghtFormatEx($user->jabber, $maxlength); ?></span>
   </div>
</div>
<?php  $is_first = false;  } ?>
<?php if ($user->jabber_1 && !($info_for_reg['jabber_1'] && !$uid)) { ?>
<div class="b-layout__hover_bg_f0ffdf b-layout_padlr_10 b-layout_pad_3 b-layout_margbot_3">
   <div class="b-layout__txt b-layout__txt_padleft_25 b-layout__txt_lineheight_1">
        <span class="b-icon b-icon__cont b-icon__cont_jb b-icon_margleft_-25"></span>
        <span class="b-layout__txt b-layout__txt_valign_top b-layout__txt_lineheight_15 b-layout__txt_ellipsis b-layout_inline-block b-layout__txt_width_225" title="<?= $user->jabber_1 ?>"><? echo LenghtFormatEx($user->jabber_1, $maxlength); ?></span>
   </div>
</div>
<?php  $is_first = false;  } ?>
<?php if ($user->jabber_2 && !($info_for_reg['jabber_2'] && !$uid)) { ?>
<div class="b-layout__hover_bg_f0ffdf b-layout_padlr_10 b-layout_pad_3 b-layout_margbot_3">
   <div class="b-layout__txt b-layout__txt_padleft_25 b-layout__txt_lineheight_1">
        <span class="b-icon b-icon__cont b-icon__cont_jb b-icon_margleft_-25"></span>
        <span class="b-layout__txt b-layout__txt_valign_top b-layout__txt_lineheight_15 b-layout__txt_ellipsis b-layout_inline-block b-layout__txt_width_225" title="<?= $user->jabber_2 ?>"><? echo LenghtFormatEx($user->jabber_2, $maxlength);  ?></span>
   </div>
</div>
<?php  $is_first = false;  } ?>
<?php if ($user->jabber_3 && !($info_for_reg['jabber_3'] && !$uid)) { ?>
<div class="b-layout__hover_bg_f0ffdf b-layout_padlr_10 b-layout_pad_3 b-layout_margbot_3">
   <div class="b-layout__txt b-layout__txt_padleft_25 b-layout__txt_lineheight_1">
        <span class="b-icon b-icon__cont b-icon__cont_jb b-icon_margleft_-25"></span>
        <span class="b-layout__txt b-layout__txt_valign_top b-layout__txt_lineheight_15 b-layout__txt_ellipsis b-layout_inline-block b-layout__txt_width_225" title="<?= $user->jabber_3 ?>"><? echo LenghtFormatEx($user->jabber_3, $maxlength); ?></span>
   </div>
</div>
<?php  $is_first = false;  } ?>
<?php if ($user->ljuser && !($info_for_reg['ljuser'] && !$uid)) { ?>
<div class="b-layout__hover_bg_f0ffdf b-layout_padlr_10 b-layout_pad_3 b-layout_margbot_3">
   <div class="b-layout__txt b-layout__txt_padleft_25 b-layout__txt_lineheight_1">
        <span class="b-icon b-icon__cont b-icon__cont_lj b-icon_margleft_-25"></span>
        <noindex class="b-layout__txt b-layout__txt_valign_top b-layout__txt_lineheight_15 b-layout__txt_ellipsis b-layout_inline-block b-layout__txt_width_225"><a class="b-layout__link" href="http://<?= $user->ljuser;?>.livejournal.com" rel="nofollow" target="_blank" title="<?= $user->ljuser ?>"><?= LenghtFormatEx($user->ljuser,$maxlength);?></a></noindex>
   </div>
</div>
<?php  $is_first = false;  } ?>
<?php if ($user->lj_1 && !($info_for_reg['lj_1'] && !$uid)) { ?>
<div class="b-layout__hover_bg_f0ffdf b-layout_padlr_10 b-layout_pad_3 b-layout_margbot_3">
   <div class="b-layout__txt b-layout__txt_padleft_25 b-layout__txt_lineheight_1">
        <span class="b-icon b-icon__cont b-icon__cont_lj b-icon_margleft_-25"></span>
        <noindex class="b-layout__txt b-layout__txt_valign_top b-layout__txt_lineheight_15 b-layout__txt_ellipsis b-layout_inline-block b-layout__txt_width_225"><a class="b-layout__link" href="http://<?= $user->lj_1;?>.livejournal.com" target="_blank" rel="nofollow" title="<?= $user->lj_1 ?>"><?= LenghtFormatEx($user->lj_1,$maxlength);?></a></noindex>
   </div>
</div>
<?php  $is_first = false;  } ?>
<?php if ($user->lj_2 && !($info_for_reg['lj_2'] && !$uid)) { ?>
<div class="b-layout__hover_bg_f0ffdf b-layout_padlr_10 b-layout_pad_3 b-layout_margbot_3">
   <div class="b-layout__txt b-layout__txt_padleft_25 b-layout__txt_lineheight_1">
        <span class="b-icon b-icon__cont b-icon__cont_lj b-icon_margleft_-25"></span>
        <noindex class="b-layout__txt b-layout__txt_valign_top b-layout__txt_lineheight_15 b-layout__txt_ellipsis b-layout_inline-block b-layout__txt_width_225"><a class="b-layout__link" href="http://<?= $user->lj_2;?>.livejournal.com" target="_blank" rel="nofollow" title="<?= $user->lj_2 ?>"><?= LenghtFormatEx($user->lj_2,$maxlength);?></a></noindex>
   </div>
</div>
<?php  $is_first = false;  } ?>
<?php if ($user->lj_3 && !($info_for_reg['lj_3'] && !$uid)) { ?>
<div class="b-layout__hover_bg_f0ffdf b-layout_padlr_10 b-layout_pad_3 b-layout_margbot_3">
   <div class="b-layout__txt b-layout__txt_padleft_25 b-layout__txt_lineheight_1">
        <span class="b-icon b-icon__cont b-icon__cont_lj b-icon_margleft_-25"></span>
        <noindex class="b-layout__txt b-layout__txt_valign_top b-layout__txt_lineheight_15 b-layout__txt_ellipsis b-layout_inline-block b-layout__txt_width_225"><a class="b-layout__link" href="http://<?= $user->lj_3;?>.livejournal.com" target="_blank" rel="nofollow" title="<?= $user->lj_3 ?>"><?= LenghtFormatEx($user->lj_3,$maxlength);?></a></noindex>
   </div>
</div>
<?php  $is_first = false;  } ?>
<?php if ($user->skype && !($info_for_reg['skype'] && !$uid)) { ?>
<div class="b-layout__hover_bg_f0ffdf b-layout_padlr_10 b-layout_pad_3 b-layout_margbot_3">
   <div class="b-layout__txt b-layout__txt_padleft_25 b-layout__txt_lineheight_1">
        <span class="b-icon b-icon__cont b-icon__cont_sky b-icon_margleft_-25"></span>
        <div class="b-layout__txt b-layout__txt_valign_top b-layout__txt_lineheight_15 b-layout__txt_ellipsis b-layout_inline-block b-layout__txt_width_225">
            <a class="b-layout__link" href="skype:<?= $user->skype;?>?chat" title="<?= $user->skype ?>"><?=$user->getAnchor('skype', 0, $maxlength)?></a>
        </div>
   </div>
</div>
<?php  $is_first = false;  } ?>
<?php if ($user->skype_1 && !($info_for_reg['skype_1'] && !$uid)) { ?>
<div class="b-layout__hover_bg_f0ffdf b-layout_padlr_10 b-layout_pad_3 b-layout_margbot_3">
   <div class="b-layout__txt b-layout__txt_padleft_25 b-layout__txt_lineheight_1">
        <span class="b-icon b-icon__cont b-icon__cont_sky b-icon_margleft_-25"></span>
        <div class="b-layout__txt b-layout__txt_valign_top b-layout__txt_lineheight_15 b-layout__txt_ellipsis b-layout_inline-block b-layout__txt_width_225"><a class="b-layout__link" href="skype:<?= $user->skype_1;?>?chat" title="<?= $user->skype_1 ?>"><?=$user->getAnchor('skype', 1, $maxlength)?></a></div>
   </div>
</div>
<?php  $is_first = false;  } ?>
<?php if ($user->skype_2 && !($info_for_reg['skype_2'] && !$uid)) { ?>
<div class="b-layout__hover_bg_f0ffdf b-layout_padlr_10 b-layout_pad_3 b-layout_margbot_3">
   <div class="b-layout__txt b-layout__txt_padleft_25 b-layout__txt_lineheight_1">
        <span class="b-icon b-icon__cont b-icon__cont_sky b-icon_margleft_-25"></span>
        <div class="b-layout__txt b-layout__txt_valign_top b-layout__txt_lineheight_15 b-layout__txt_ellipsis b-layout_inline-block b-layout__txt_width_225"><a class="b-layout__link" href="skype:<?= $user->skype_2;?>?chat" title="<?= $user->skype_2 ?>"><?=$user->getAnchor('skype', 2, $maxlength)?></a></div>
   </div>
</div>
<?php  $is_first = false;  } ?>
<?php if ($user->skype_3 && !($info_for_reg['skype_3'] && !$uid)) { ?>
<div class="b-layout__hover_bg_f0ffdf b-layout_padlr_10 b-layout_pad_3 b-layout_margbot_3">
   <div class="b-layout__txt b-layout__txt_padleft_25 b-layout__txt_lineheight_1">
        <span class="b-icon b-icon__cont b-icon__cont_sky b-icon_margleft_-25"></span>
        <div class="b-layout__txt b-layout__txt_valign_top b-layout__txt_lineheight_15 b-layout__txt_ellipsis b-layout_inline-block b-layout__txt_width_225"><a class="b-layout__link" href="skype:<?= $user->skype_3;?>?chat" title="<?= $user->skype_3 ?>"><?=$user->getAnchor('skype', 3, $maxlength)?></a></div>
   </div>
</div>
<?php  $is_first = false;  } ?>
<?php if ($user->second_email && !($info_for_reg['second_email'] && !$uid)) { ?>
<div class="b-layout__hover_bg_f0ffdf b-layout_padlr_10 b-layout_pad_3 b-layout_margbot_3">
   <div class="b-layout__txt b-layout__txt_padleft_25 b-layout__txt_lineheight_1">
        <span class="b-icon b-icon__cont b-icon__cont_mail b-icon_top_1 b-icon_margleft_-25"></span>
        <div class="b-layout__txt b-layout__txt_valign_top b-layout__txt_lineheight_15 b-layout__txt_ellipsis b-layout_inline-block b-layout__txt_width_225"><a class="b-layout__link" href="mailto:<?= $user->second_email;?>"><?=$user->getAnchor('email', 0, $maxlength)?></a></div>
   </div>
</div>
<?php  $is_first = false;  } ?>
<?php if ($user->email_1 && !($info_for_reg['email_1'] && !$uid)) { ?>
<div class="b-layout__hover_bg_f0ffdf b-layout_padlr_10 b-layout_pad_3 b-layout_margbot_3">
   <div class="b-layout__txt b-layout__txt_padleft_25 b-layout__txt_lineheight_1">
        <span class="b-icon b-icon__cont b-icon__cont_mail b-icon_top_1 b-icon_margleft_-25"></span>
        <div class="b-layout__txt b-layout__txt_valign_top b-layout__txt_lineheight_15 b-layout__txt_ellipsis b-layout_inline-block b-layout__txt_width_225"><a class="b-layout__link" href="mailto:<?= $user->email_1;?>"><?=$user->getAnchor('email', 1, $maxlength)?></a></div>
   </div>
</div>
<?php  $is_first = false;  } ?>
<?php if ($user->email_2 && !($info_for_reg['email_2'] && !$uid)) { ?>
<div class="b-layout__hover_bg_f0ffdf b-layout_padlr_10 b-layout_pad_3 b-layout_margbot_3">
   <div class="b-layout__txt b-layout__txt_padleft_25 b-layout__txt_lineheight_1">
        <span class="b-icon b-icon__cont b-icon__cont_mail b-icon_top_1 b-icon_margleft_-25"></span>
        <div class="b-layout__txt b-layout__txt_valign_top b-layout__txt_lineheight_15 b-layout__txt_ellipsis b-layout_inline-block b-layout__txt_width_225"><a class="b-layout__link" href="mailto:<?= $user->email_2;?>"><?=$user->getAnchor('email', 2, $maxlength)?></a></div>
   </div>
</div>
<?php  $is_first = false;  } ?>
<?php if ($user->email_3 && !($info_for_reg['email_3'] && !$uid)) { ?>
<div class="b-layout__hover_bg_f0ffdf b-layout_padlr_10 b-layout_pad_3 b-layout_margbot_3">
   <div class="b-layout__txt b-layout__txt_padleft_25 b-layout__txt_lineheight_1">
        <span class="b-icon b-icon__cont b-icon__cont_mail b-icon_top_1 b-icon_margleft_-25"></span>
        <div class="b-layout__txt b-layout__txt_valign_top b-layout__txt_lineheight_15 b-layout__txt_ellipsis b-layout_inline-block b-layout__txt_width_225"><a class="b-layout__link" href="mailto:<?= $user->email_3;?>"><?=$user->getAnchor('email', 3, $maxlength)?></a></div>
   </div>
</div>
<?php  $is_first = false;  } ?>
<?php if ($user->phone && !($info_for_reg['phone'] && !$uid)) { ?>
<div class="b-layout__hover_bg_f0ffdf b-layout_padlr_10 b-layout_pad_3 b-layout_margbot_3">
   <div class="b-layout__txt b-layout__txt_padleft_25 b-layout__txt_lineheight_1">
        <span class="b-icon b-icon__cont b-icon__cont_tel b-icon_margleft_-25"></span>
        <span class="b-layout__txt b-layout__txt_valign_top b-layout__txt_lineheight_1 b-layout__txt_ellipsis b-layout_inline-block b-layout__txt_width_225 b-layout__txt_padtop_1" title="<? echo $user->phone;?>"><? echo $user->phone;?></span>
   </div>
</div>
<?php  $is_first = false;  } ?>
<?php if ($user->phone_1 && !($info_for_reg['phone_1'] && !$uid)) { ?>
<div class="b-layout__hover_bg_f0ffdf b-layout_padlr_10 b-layout_pad_3 b-layout_margbot_3">
   <div class="b-layout__txt b-layout__txt_padleft_25 b-layout__txt_lineheight_1">
        <span class="b-icon b-icon__cont b-icon__cont_tel b-icon_margleft_-25"></span>
        <span class="b-layout__txt b-layout__txt_valign_top b-layout__txt_lineheight_1 b-layout__txt_ellipsis b-layout_inline-block b-layout__txt_width_225 b-layout__txt_padtop_1" title="<? echo $user->phone_1;?>"><? echo $user->phone_1; ?></span>
   </div>
</div>
<?php  $is_first = false;  } ?>
<?php if ($user->phone_2 && !($info_for_reg['phone_2'] && !$uid)) { ?>
<div class="b-layout__hover_bg_f0ffdf b-layout_padlr_10 b-layout_pad_3 b-layout_margbot_3">
   <div class="b-layout__txt b-layout__txt_padleft_25 b-layout__txt_lineheight_1">
        <span class="b-icon b-icon__cont b-icon__cont_tel b-icon_margleft_-25"></span>
        <span class="b-layout__txt b-layout__txt_valign_top b-layout__txt_lineheight_1 b-layout__txt_ellipsis b-layout_inline-block b-layout__txt_width_225 b-layout__txt_padtop_1" title="<? echo $user->phone_2;?>"><? echo $user->phone_2; ?></span>
   </div>
</div>
<?php  $is_first = false;  } ?>
<?php if ($user->phone_3 && !($info_for_reg['phone_3'] && !$uid)) {?>
<div class="b-layout__hover_bg_f0ffdf b-layout_padlr_10 b-layout_pad_3 b-layout_margbot_3">
   <div class="b-layout__txt b-layout__txt_padleft_25 b-layout__txt_lineheight_1">
        <span class="b-icon b-icon__cont b-icon__cont_tel b-icon_margleft_-25"></span>
        <span class="b-layout__txt b-layout__txt_valign_top b-layout__txt_lineheight_1 b-layout__txt_ellipsis b-layout_inline-block b-layout__txt_width_225 b-layout__txt_padtop_1" title="<? echo $user->phone_3;?>"><? echo $user->phone_3; ?></span>
   </div>
</div>
<?php  $is_first = false;  } ?>
<?php if (!($info_for_reg['country'] && !$uid) && $user->country) { ?>
    <div class="b-layout__hover_bg_f0ffdf b-layout_padlr_10  b-layout_pad_3">
       <div class="b-layout__txt b-layout__txt_padleft_35 b-layout__txt_lineheight_1 b-layout__txt_padtop_1">
            <span class="b-icon b-icon__cont b-icon__cont_map b-icon_margleft_-35"></span>
            <? if ($info_for_reg['country'] && !$uid) { ?>
                <?= $reg_string ?>
            <? } elseif ($user->country) { ?>
                <?= country::GetCountryName($user->country); if ($user->city && !($info_for_reg['city'] && !$uid)) { ?>, <?= city::GetCityName($user->city); } ?>
            <? } ?>
       </div>
    </div>
<?php } ?>

<style type="text/css">
.b-layout__txt_padleft_25 a.b-layout__link, .b-layout__txt_padleft_25  a.b-post__link { color:#000 !important; text-decoration:none;}
.b-layout__txt_padleft_25 a.b-layout__link:hover, .b-layout__txt_padleft_25 a.b-post__link:hover { color:#000 !important; text-decoration: underline;}
a.b-post__link { line-height:1.2;}
.b-layout__txt_width_225{ padding-bottom:1px;}
</style>