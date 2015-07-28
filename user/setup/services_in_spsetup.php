<?php

if (!$_in_setup) {header ("HTTP/1.0 403 Forbidden"); exit;}
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
$prfs = new professions();
$profs = $prfs->GetAllProfessionsSpec($user->uid);

$main_spec = null;
foreach ($profs as $p) {
    if($p['checked']) $main_spec = $p['id'];
}

$specs_add = array();
if(is_pro()) {
    $specs_add = $prfs->GetProfsAddSpec($uid);
}

$mirr_specs = array();
$all_checked = $specs_add;
if(count($all_checked)) {
    $mirr_specs = $prfs->GetMirroredProfs(implode(",", $all_checked));
    if(count($mirr_specs)) $mirr_specs = array_diff($mirr_specs, $all_checked);
}

require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/quickpro.common.php");
$xajax->printJavascript('/xajax/');

?>
<div class="b-layout b-layout_padtop_20">
<form id='idProfs' action="/users/<?=$user->login?>/setup/portfolio/" method="post">
<h3 class="b-layout__h3">Выберите вашу специализацию:</h3>

<div class="b-radio b-radio__item_padbot_20">
  <input class="b-radio__input" type="radio" id="lb000" name="spec" value="0" <? if (0 == $user->spec) print("checked='checked'")?>  />
  <label class="b-radio__label" for="lb000">Нет специализации</label>
</div>


      <table class="b-layout__table b-layout__table_width_full">
        <tr class="b-layout__tr">
          <td class="b-layout__td b-layout__td_padbot_20 b-layout__td_width_33ps b-layout__td_width_full_ipad">
            <? 
              $lastgrname = NULL;
              $j=0;
              foreach($profs as $prof)
              { 
                if($lastgrname != $prof['groupname'])
                {
                  if($j) {
                    print('</td>');
                    if($j % 3 == 0) {
                      print('</tr><tr class="b-layout__tr">');
                    }
                    print('<td class="b-layout__td b-layout__td_padbot_20 b-layout__td_width_33ps b-layout__td_width_full_ipad">');
                  }

                  ?><h4 class="b-layout__txt b-layout__txt_padbot_10"><?=$prof['groupname']?></h4><?
                  $j++;
                  $lastgrname = $prof['groupname'];
                }
              ?>
                   <div class="b-radio b-radio__item_padbot_10">
                      <input class="b-radio__input" <?=in_array($prof['id'], $specs_add) || in_array($prof['id'], $mirr_specs) ? 'disabled="disabled"' : ''?>  type="radio" name='spec' value="<?=$prof['id']?>" id="lb<?=$prof['id']?>"<?=($prof['checked'] ? ' checked="checked"' : '')?> />
                      <label class="b-radio__label" for="lb<?=$prof['id']?>"><?=$prof['profname']?></label>
                   </div>
           <? } ?>
          </td>
        </tr>
      </table>

<div class="b-buttons">
		<input type="hidden" name="action" value="spec_change" />
        <button type="submit" name="btn" id="btn_spec_change" class="b-button b-button_flat b-button_flat_green b-button_float_right">Изменить</button>
</div>
</form>
</div>
<style type="text/css">
.msie .spssetup label{ position:relative; top:3px;}
</style>