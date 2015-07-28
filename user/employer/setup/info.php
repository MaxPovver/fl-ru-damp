<?
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/employer.php");
	$user = new employer();
	$user->GetUser($login);
?>
<table class="b-layout__table b-layout__table_width_full">
   <tr class="b-layout__tr">
      <td class="b-layout__td b-layout__td_width_60 b-layout__td_padtop_4 b-layout__td_ipad b-layout__td_width_null_ipad b-layout__td_pad_null_ipad b-layout__td_padright_10">
          <a class="b-layout__link" href="/users/<?= $user->login ?><?php if(!hasPermissions('users')){ ?>/setup/foto/<?php } ?>">
              <?=view_avatar($user->login, $user->photo, 0)?>
          </a>
      </td>
      <td class="b-layout__td b-layout__td_ipad b-layout__td_pad_null_ipad">
	        <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padbot_5 b-layout__txt_bold b-layout__txt_color_6db335"><?=$user->uname?> <?=$user->usurname?> [<?=$user->login?>] <?=($user->is_pro == 't')?view_pro_emp(1):'';?></div>
      </td>
      <td class="b-layout__td b-layout__td_right b-layout__td_width_100 b-layout__td_width_null_ipad b-layout__td_pad_null_ipad">
      </td>
   </tr>
</table>
 
 
