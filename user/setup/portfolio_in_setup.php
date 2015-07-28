<?
if (!$_in_setup) {header ("HTTP/1.0 403 Forbidden"); exit;}
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/users.common.php");
$xajax->printJavascript('/xajax/');
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
$prfs = new professions();
$profs = $prfs->GetAllProfessionsPortfWithoutMirrored(get_uid());
$mirrors = $prfs->GetAllMirroredProfsId();
?>
<script type="text/javascript">

var mirrors = [];
<? for ($i=0,$m=count($mirrors); $i<$m; $i++) { ?>
mirrors[<?=$i?>] = {main: <?=$mirrors[$i]['main_prof']?>, mirror: <?=$mirrors[$i]['mirror_prof']?>};
<? } ?>

/**
 * @param int  id           - идентификатор чекбокса
 * @param bool flag         - если определен, то копируется в checked   
 * @param int  noDisableId  - если определен, то чекбоксу с id = 'lb' + noDisableId disable не устанавливается     
 */
function checkMirrors(id, flag, noDisableId) {
    var f = $('lb' + id).checked;
    if (String(flag) != "undefined") {
        $('lb' + id).disabled = $('lb' + id).checked = f = flag;
    }
    var parentFound = 0;
    for (var i = 0; i < mirrors.length; i++) {
    	if (mirrors[i].main == id) {
            var chb = $('lb' + mirrors[i].mirror);
            chb.checked  = f;
            if (!f || (mirrors[i].mirror != noDisableId)) {
                chb.disabled = f;
            }
        }else if ((mirrors[i].mirror == id)&& (parentFound == 0) ) {
        	checkMirrors(mirrors[i].main, f, id);
        	parentFound = 1;        	
        }
    }
}
</script>

<form id="frm" action="/users/<?=$user->login?>/setup/portfsetup/" method="post">
	    <?php if($_SESSION['text_spec']) { ?>
        <div class="b-layout__txt_padtop_10">
	    <div class="b-fon b-fon_bg_fff9bf b-fon_pad_5_10 b-fon_margbot_20">
	        Для добавления работ необходимо выбрать разделы портфолио
        </div>
        </div>
        <?php } else {//if?>
	    <p style="padding:15px 0">Выберите разделы, в которых намерены разместить свои работы:</p>
	    <?php } //else?>
      <table cellspacing="0" cellpadding="0" class="b-layout__table b-layout__table_width_full">
        <tr class="b-layout__tr">
          <td class="b-layout__td b-layout__td_width_33ps b-layout__td_padbot_20 b-layout__td_width_full_ipad">
            <? 
              $lastgrname = NULL;
              $j=0;
              global $DB;
              $u_id = get_uid(false);
              $list = array();
              foreach($DB->rows("SELECT DISTINCT P.prof_id,M.mirror_prof  FROM portfolio P
                      LEFT JOIN
                        mirrored_professions M
                        ON M.main_prof = P.prof_id
                      WHERE P.user_id = {$u_id}") as $key => $value){
                  
                  $list[] = (int)$value['prof_id'];
                  if((int)$value['mirror_prof']) $list[] = (int)$value['mirror_prof'];
              }

              foreach($profs as $prof)
              { 
                if($lastgrname != $prof['groupname'])
                {
                  if($j) {
                    print('</td>');
                    if($j % 3 == 0) {
                      print('</tr><tr class="b-layout__tr">');
                    }
                    print('<td  class="b-layout__td b-layout__td_width_33ps b-layout__td_padbot_20 b-layout__td_width_full_ipad">');
                  }

                  ?><h3 class="b-layout__h3"><?=$prof['groupname']?></h3><?
                  $j++;
                  $lastgrname = $prof['groupname'];
                }
              ?>
                    <div class="b-check b-check_padbot_5">
                       <input class="b-check__input" type="checkbox" <?= $prof['checked'] && in_array($prof['id'], $list) ? 'disabled="disabled"' : '';?> name="prof[]" value="<?=$prof['id']?>" onchange="checkMirrors(this.value);" id="lb<?=$prof['id']?>"<?=($prof['checked'] ? 'checked="checked"' : '')?> />
                      <label class="b-check__label" for="lb<?=$prof['id']?>"><?=$prof['profname']?></label>
                   </div>
           <? } ?>
          </td>
        </tr>
      </table>

	<input type="hidden" name="action" value="portf_choise" />
    <div class="b-buttons">
	   <a class="b-button b-button_flat b-button_flat_green b-button_float_right" onclick="$('frm').submit();"><?php if($_SESSION['text_spec']) { ?>Сохранить и добавить новую работу<?php } else {?>Сохранить изменения<?php }//else?></a>
    </div>

</form>

<script type="text/javascript">
var checked = [];
var profs = document.getElementById('frm')['prof[]'];
for (var i=0; i<profs.length; i++) {
    if (profs[i].checked) checked[checked.length] = profs[i].value;
}
for (var i=0; i<checked.length; i++) checkMirrors(checked[i]);
</script>
