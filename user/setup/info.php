<?
	require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/freelancer.php");
	$user = new freelancer();
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
		<?=view_user($user,'','','','',TRUE,FALSE)?>
      </td>
      <td class="b-layout__td b-layout__td_right b-layout__td_width_100 b-layout__td_width_null_ipad b-layout__td_pad_null_ipad">
      </td>
   </tr>
</table>
  
  
  
  
