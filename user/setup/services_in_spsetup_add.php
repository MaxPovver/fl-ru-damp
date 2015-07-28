<?
if(!defined('IN_STDF')) { 
    header("HTTP/1.0 404 Not Found");
    exit();
}
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/professions.common.php");
$xajax->printJavascript('/xajax/');
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects_offers_answers.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
$paid_specs = professions::getPaidSpecs($uid);
$paid_cnt = 0;
$add_cnt = 0;
$free_cnt = (!!$is_pro)*PROF_SPEC_ADD;
$ex_specs = array();
if($paid_specs) {
    $paid_cnt = count($paid_specs);
    foreach($paid_specs as $ps) {
        if($ps['prof_id'])
            $ex_specs[] = $ps['prof_id'];
    }
}
if($is_pro) {
    if($add_specs = professions::getAddSpecs($uid)) {
       $add_cnt = count($add_specs);
       foreach($add_specs as $as)
           $ex_specs[] = $as['prof_id'];
   }
}
if($user->spec_orig)
    $ex_specs[] = $user->spec_orig;

$all_cnt = $paid_cnt + $free_cnt;

$specs = array();
for($i=0,$a=0,$p=0,$j=0,$aa=$add_cnt; $i<$all_cnt; $i++) {
    if($add_cnt < $free_cnt && $i < $free_cnt-$add_cnt) $prof = array();
    else if($add_cnt && $a < $add_cnt) {$prof = $add_specs[$a++]; $aa--;}
    else if($paid_cnt) $prof = $paid_specs[$p++];
    $specs[] = $prof;
}

$a = $add_cnt; $p = 1;
foreach($specs as $i => $spec) {
    if(isset($spec['prof_id']) && isset($specs[($i-($spec['paid_id'] ? 0 : 1))]['prof_id'])) $specs[$i]['top'] = true;
    if(isset($spec['prof_id']) && isset($specs[($i+($spec['paid_id'] ? 1 : 0))]['prof_id'])) $specs[$i]['bot'] = true;

    if(!$is_pro && $i == 0) $specs[$i]['top'] = false;
    if(!$paid_cnt && $i == PROF_SPEC_ADD-1) $specs[$i]['bot'] = false;
}

if($ex_specs)
    $ex_specs = professions::GetMirroredProfs(implode(',', $ex_specs));
$categories = professions::GetAllGroupsLite(true, true);
$sub_categories = professions::GetProfList();
?>
<script type="text/javascript">
var CATEGORIES={<? // категории/подкатегории: {ид_кат:{имя_кат:{ид_подкат:имя_подкат,ид_подкат:...}},ид_кат:...}
foreach($sub_categories as $sc) {
    $cc = $sc['prof_group'];
    $ccname = $categories[$cc]['name'];
    if($lcc!=$cc) {
        echo ($lcc ? '}},' : '') . "$cc:{'$ccname':{";
        $lcc = $cc;
        $j=0;
    }
    echo ($j++ ? ',' : '') . "{$sc['id']}:'{$sc['name']}'";
}
if($lcc) echo '}}';
?>};
var EX_SPECS={<?
if($ex_specs) {
    $i=0;
    foreach($ex_specs as $ex) {
        echo ($i++ ? ',' : '') . "$ex:1";
    }
}
?>};
var SPARAMS={<?
foreach($specs as $i=>$prof) {
    echo ($i ? ',' : '') . $i . ':[' . (int)$prof['paid_id'] . ',' . (int)$prof['prof_id'] . ',' . (int)$prof['prof_origin'] . ',' . (int)$prof['group_id'] . ']';
}
?>};

</script>
<div class="b-layout b-layout_padtop_20">
    <? if($specs) { ?>
        <? if($is_pro) { ?>
          <div class="b-layout__txt b-layout__txt_padbot_10">У вас есть возможность выбрать <?=$all_cnt.ending($all_cnt, ' дополнительную специализацию', ' дополнительные специализации', ' дополнительных специализаций')?><?=($paid_cnt ? ', включая '.$paid_cnt.ending($paid_cnt, ' платную', ' платные', ' платных') : '')?>:</div>
        <? } else { ?>
          <div class="b-layout__txt b-layout__txt_padbot_10">У вас есть возможность выбрать <?=$paid_cnt.ending($paid_cnt, ' дополнительную специализацию', ' дополнительные специализации', ' дополнительных специализаций')?>:</div>
        <? } ?>

        <form action=".#page" method="post" id="saveSpecFrm" onsubmit="if(this.oldprof_id.value==0&&this.prof_id.value==0){alert('Необходимо выбрать специализацию.');return false;}">
        <div>
            <table class="tbl-eight-specs" id="specsTbl">
                <?
                  foreach($specs as $i=>$prof) {
                ?>
                <tr>
                    <td class="es-c1"><span class="b-page__desktop"><?=($i+1)?>.</span></td>
                    <td class="es-c2"><span class="b-page__ipad b-page__iphone"><?=($i+1)?>.</span>
                        <a href="javascript:;" onclick="moveSpec(this, -1)" style="display:<?= $prof['top'] ? '' : 'none' ?>;"><img src="/images/arrow2-top.png" alt="Вверх" /></a><?
                        ?><img style="display:<?= $prof['top'] ? 'none' : '' ?>;" src="/images/arrow2-top-a.png" alt="Вверх" /><?
                        ?><a href="javascript:;" onclick="moveSpec(this, +1)" style="display:<?= $prof['bot'] ? '' : 'none' ?>;"><img src="/images/arrow2-bottom.png" alt="Вниз" /></a><?
                        ?><img style="display:<?=$prof['bot'] ? 'none': ''?>;" src="/images/arrow2-bottom-a.png" alt="Вниз" />
                    </td>
                    <? if($prof['prof_id']) { ?>
                      <td><strong><?=$prof['group_name']?></strong></td>
                      <td><a href="/freelancers/?prof=<?=$prof['prof_id']?>"><?=$prof['name']?></a></td>
                    <? } else { ?>
                      <td colspan="2"><em>Дополнительная специализация не указана, <a href="javascript:;" onclick="editSpec(this)" class="lnk-dot-blue">выберите из списка</a>.</em></td>
                    <? } ?>
                    <td class="es-c6"><a href="javascript:;" onclick="editSpec(this)"><img src="/images/btn-edit2.png" alt="Редактировать" /></a></td>
                </tr>
                <? } ?>
                <tr id="editspec_box" style="display:none">
                    <td class="es-c1">&nbsp;</td>
                    <td class="es-c2"><a href=""><img src="/images/arrow2-top-a.png" alt="Вверх" /></a><a href=""><img src="/images/arrow2-bottom.png" alt="Вниз" /></a></td>
                    <td></td>
                    <td id="subcat_box"></td>
                    <td>
                        <input type="submit" value="Сохранить изменения" class="i-bold i-btn" name="savespec_btn" />
                        <input type="button" value="Отменить" class="i-btn" onclick="cleanEdit()" />
                    </td>
                </tr>
            </table>
            <input type="hidden" name="oldprof_id" value="" />
            <input type="hidden" name="paid_id" value="" />
            <input type="hidden" name="action" value="save_spec_add" />
        </div>
        </form>
        <?/* #0022795 <p>Купить еще больше дополнительных специализаций или продлить действие уже существующих, вы можете на <a href="/payed/">странице управления платными услугами</a>.</p> */?>
    <? } /*#0022795else { ?>
        <p>Сейчас у вас нет дополнительных специализаций. Вы можете приобрести их на <a href="/payed/">странице управления платными услугами</a>.</p>
    <? } */?>
    <div class="b-layout__txt">Если у вас возникли вопросы &ndash; обратитесь в <a href="https://feedback.fl.ru/">Службу поддержки</a>. С удовольствием ответим.</div>
</div>
