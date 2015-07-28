<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/users.common.php");
$xajax->printJavascript('/xajax/');
if (!$_in_setup) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/commune.php");
$proj_groups = professions::GetAllGroupsLite();
$commune = new commune();
$uid = get_uid();
if (!($communes_my = commune::GetCommunes(NULL, $uid, NULL, commune::OM_CM_MY)))
    $communes_my = array();
if (!($communes = commune::GetCommunes(NULL, NULL, $uid, commune::OM_CM_JOINED, $uid)))
    $communes = array();
foreach ($communes_my as $cm)
    $communes[] = $cm;
$c_signed = $commune->getSubscribedCommunes($uid);
$categories = professions::GetAllGroupsLite();

$professions = professions::GetAllProfessions();
array_group($professions, 'groupid');
$professions[0] = array();
?>
<script type="text/javascript">
//    function fixCommunes(){
//        var ele = $('ch7');
//        var list = $('comm_container').getElements('input[type=checkbox]');
//        for(var i = 0; i < list.length; i++){
//            list[i].disabled = !ele.checked;
//        }
//    }
    window.addEvent('domready', function() {
//        fixCommunes();
    });
</script>

        <script type="text/javascript">
            var sub = new Array();
<?
        foreach ($categories as $cat) {
            $out_s = array();
?>

            sub[<?= $cat['id']; ?>] = new Array(
<?
            if(is_array($professions[$cat['id']])) foreach ($professions[$cat['id']] as $subcat) {

                $out_s[] = " new Array({$subcat['id']}, '".clearTextForJS($subcat['profname'])."') ";
            }
            echo implode(', ', $out_s);
?>
        );
<? } ?>

        function applySubcat(cat){
            document.getElementById('subscr_sub').options.length = 0;
             var option = document.createElement('option');
                    option.value = '0';
                    option.innerHTML = 'Весь раздел';
                    document.getElementById('subscr_sub').appendChild(option);
            if(typeof sub[cat] != 'undefined')
                for(var i = 0; i < sub[cat].length; i++){
                    var option = document.createElement('option');
                    option.value = sub[cat][i][0];
                    option.innerHTML = sub[cat][i][1];
                    document.getElementById('subscr_sub').appendChild(option);
                }
        }
function clearSelect(sid) {
        var oListbox = document.getElementById(sid);
          for (var i=oListbox.options.length-1; i >= 0; i--){
              oListbox.remove(i);
          }
}
var exists_pars = new Array();

function allowAddFilter(cat, scat){
    if(Number(cat) == 0){
        alert('Нельзя выбрать все разделы');
                return false;
    }
    for(var i = 0; i < exists_pars.length; i++){
        if(Number(exists_pars[i][0]) == cat){
            if(Number(exists_pars[i][1]) == 0) {
                alert('Этот раздел уже выбран полностью');
                return false;
            } else if(Number(exists_pars[i][1]) == scat) {
                alert('Этот пункт уже выбран');
                return false;
            } else if (Number(scat) == 0 && Number(exists_pars[i][1]) != 0) {
                alert('Некоторые подразделы данного раздела уже выбраны');
                return false;
            }
            
        }
    }
    
    return true;
}

function getmailer_str(){
    var str = '';
    for(var i = 0; i < exists_pars.length; i++){
        str += ':c'+Number(exists_pars[i][0])+'s'+Number(exists_pars[i][1]);
    }
    return str;
}

function addMailerFilter(){
    var cat = document.getElementById('subscr_cat').value;
    var scat = document.getElementById('subscr_sub').value;
    if(allowAddFilter(cat,scat)) xajax_AddSubscFilter(cat, scat, getmailer_str());
}

function unset(cat,scat){
    for(var i = 0; i < exists_pars.length; i++){
        if(Number(exists_pars[i][0]) == Number(cat) && Number(exists_pars[i][1]) == Number(scat)){
            exists_pars.splice(i,1);
            if(exists_pars.length == 0) $('filter_body_p').style.display = $('ch2').checked ? '' : 'none';
        }
    }
}


 function togglePrj(obj){
     if(obj.checked){
         document.getElementById('filter_body_p').style.display = '';
         document.getElementById('head_filter').style.display = '';
     }else{
         document.getElementById('filter_body_p').style.display = 'none';//exists_pars.length ? '' : 'none';
         document.getElementById('head_filter').style.display = 'none';
         document.getElementById('ch2-a').style.display = exists_pars.length ? 'none' : '';
     }
     
     xajax_togglePrj(obj.checked);
 }  

 
    </script>
<form action="." method="post" id="subscr_form">

        <div class="b-layout b-layout_padtop_20">
            <h2 class="b-layout__title">Настройки уведомлений</h2>
            <?=view_info2($info_msg)?>
            <div class="b-layout__txt b-layout__txt_padbot_10">Присылать по электронной почте следующие уведомления:</div>
            <input type="hidden" name="action" value="update_subscr" />
            <ul class="settings-subscribe">
                <li><label><input type="checkbox" id="ch1" name="newmsgs" class="i-chk" value="1" <? if ($user->subscr[0])
            print "checked='checked'" ?> /> Уведомления о новых сообщениях</label></li>
            <li><label><input type="checkbox" id="ch6" name="commune" class="i-chk" value="1" <? if ($user->subscr[5])
                print "checked='checked'" ?> /> Уведомления о новых действиях в сообществах</label></li>
            <li><label><input type="checkbox" class="i-chk" id="ch7" name="commune_topics" value="1" <? if ($user->subscr[6])
                    print "checked='checked'" ?> onclick="this.getParent('li').getElements('ul input').set('checked', this.get('checked'))" /> Уведомления о новых темах в сообществах</label>

<?php if (is_array($communes) && count($communes)) { ?>
                    <ul>


<? foreach ($communes as $ckey => $comm)
                            if ($comm['id'] != 0) {
 ?>
                                    <li><label><input type="checkbox" class="i-chk" name="comm[]" value="<?= $comm['id'] ?>" id="chcomm<?= $comm['id'] ?>" <? if ($user->subscr[6] && in_array($comm['id'], $c_signed))
                                    print "checked='checked'" ?> onclick="this.get('checked') && $('ch7').set('checked', 1)" /> <?= $comm['name'] ?></label></li>
<? } ?>

                            </ul>
<?php } ?>



                    </li>
                    <li>
                        <label>
                            <input type="checkbox" onclick="togglePrj(this)" class="i-chk"  id="ch2" name="vacan"  value="1" <? if ($user->mailer) print "checked='checked'" ?> /> 
                            Уведомление о новых проектах 
                        </label>
                        <a id="ch2-a" href="javascript:void(0);" onclick="User_Setup.showSpec();" style="display: <?= $user->mailer_str ? 'none' : ''; ?>">(уточнить специализации)</a>
                        <div class="ss-projects" id="filter_body_p" style="display: <?= $user->mailer && $user->mailer_str ? 'block' : 'none'; ?>">
                            <?php include_once(dirname(__FILE__).'/subscr_filter.php');?>
                        </div>
                    </li>
                    <li><label><input type="checkbox" class="i-chk" id="ch9" name="contest"  value="1" <? if ($user->subscr[8])
                            print "checked='checked'" ?> /> Уведомления в конкурсах</label></li>
                    <? if (BLOGS_CLOSED == false) { ?>
                    <li><label><input type="checkbox" class="i-chk" id="ch3" name="comments"  value="1" <? if ($user->subscr[2])
                        print "checked='checked'" ?> /> Комментарии к сообщениям/комментариям в блогах</label></li>
                    <? } ?>
                    <li><label><input type="checkbox" class="i-chk" id="ch4" name="prcomments" value="1"   <? if ($user->subscr[4])
                        print "checked='checked'" ?> /> Комментарии к сообщениям/комментариям в проектах</label></li>
                    <li><label><input type="checkbox" class="i-chk" id="ch8" name="adm_subscr"  value="1" <? if ($user->subscr[7])
                        print "checked='checked'" ?> /> Новости от команды FL.ru</label></li>
                    <li><label><input type="checkbox" class="i-chk" id="ch16" name="payment" value="1" <? if ($user->subscr[15])
                        print " checked='checked'" ?> /> Уведомления личного счета</label></li>
                    <li><label><input type="checkbox" class="i-chk" id="ch10" name="team"  value="1" <? if ($user->subscr[9])
                        print "checked='checked'" ?> /> Уведомления о добавлении в избранные</label></li>
                    <li><label><input type="checkbox" class="i-chk" id="ch13" name="massending" value="1" <? if ($user->subscr[12])
                        print "checked='checked'" ?> /> Получать платные рассылки</label></li>
                    
                    </ul>

                                                    <button class="b-button b-button_flat b-button_flat_green" onclick="$('subscr_form').submit()">Изменить</button>
                                                </div>

</form>
