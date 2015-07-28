<script>
    window.addEvent('domready', function(){
        spam.sendFromSearch();
    })
</script>
<?
// ссылка на поиск
$url = $_SERVER['REQUEST_URI'];
$url = preg_replace('~.*?\?(.*)~', '$1', $url);
$url = preg_replace('~from_search=\d\&(.*)~', '$1', $url);
$url = preg_replace('~search_count=\d\&(.*)~', '$1', $url);


?>
<div class="b-layout__txt b-layout__txt_padbot_10">Вы перешли из поиска. По <a class="b-layout__link" href="/search/?<?= $url ?>">вашему запросу</a> <?= ending($searchCount, 'нашелся', 'нашлось', 'нашлось') . ' ' . $searchCount . " " . ending($searchCount, 'пользователь', 'пользователя', 'пользователей') ?>.</div>
<div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_fontsize_11"><span class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_bold">Обратите внимание!</span> Стоимость рассылки зависит от общего количества фрилансеров, которым будет отправлено сообщение. Это количество может отличаться от числа, полученного путем простого сложения представителей выбранных вами разделов, так как один и тот же фрилансер может иметь несколько специализаций на сайте.</div>
<div class="masss-mess-b">
    <h4>Сообщение</h4>
    <div class="form masss-mess">
        <b class="b1"></b>
        <b class="b2"></b>
        <div class="form-in">
            <div class="form-block first">
                <div class="form-el">
                    <textarea id="msg" name="msg" rows="6" cols="100" onfocus="if (this.className != '') { this.className = ''; this.value = '' }"><?=($params['msg']? htmlspecialchars($params['msg']): '')?></textarea>
                </div>
                                    <div>
                            <strong>Важно!</strong> Рассылка предназначена только для реальных целевых проектов и поиска исполнителей. Реклама к рассылке не допускается.
О размещении рекламы на сайте вы можете узнать <a target="_blank" href="/press/adv/">здесь</a><br /><br />
                        </div>
            </div>
            <div class="form-block last">
                <div class="form-el">
                    <div class="masss-files<?=(empty($params['files'])? ' flt-hide': ' flt-show')?>" id="masss-files" page="0">
                        <div class="masss-files-clip">
                            <a href="javascript: void(0);" class="flt-tgl-lnk lnk-dot-blue">Прикрепленные файлы (развернуть)</a>
                        </div>
                        <div id="flt-masss-files" class="flt-cnt-masssend с" >
                            <div class="cl-form-files c"> 
                                <ul id="mf-files-list" class="form-files-added"></ul> 
                                <ul class="form-files-list">
                                    <li id="c">
                                        <input id="mf-file" name="attach" type="file" />
                                        &nbsp;&nbsp;<img id="mf-load" src="/images/loader-gray.gif" style="display: none" />
                                    </li>
                                </ul>
                                <div class="masss-files-inf">
                                    <p><strong>Вы можете прикрепить до <?=masssending::MAX_FILES?> файлов общим объемом не более <?=ConvertBtoMB(masssending::MAX_FILE_SIZE)?>.</strong><br />
                                    Файлы следующих форматов запрещены к загрузке: <?=implode(', ', $GLOBALS['disallowed_array'])?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <b class="b2"></b>
        <b class="b1"></b>
    </div>
</div>

<input type="hidden" name="from_search" value="2" />
<input type="hidden" name="search_string" value="<?= __paramInit('htmltext','search_string','search_string') ?>" />
<input type="hidden" name="search_count" value="<?= $searchCount ?>" />
<? 
$fromSearchAction = __paramInit('string', 'action', 'action');
if ($fromSearchAction === 'search_advanced') { ?>
    <input type="hidden" name="advanced_search" value="1" />
    <input type="hidden" id="sbr_is_positive" name="sbr_is_positive" value="<?= __paramInit('bool', 'sbr_is_positive', 'sbr_is_positive', false) ?>" />
    <input type="hidden" name="sbr_not_negative" value="<?= __paramInit('bool', 'sbr_not_negative', 'sbr_not_negative', false) ?>" />
    <input type="hidden" name="opi_is_positive" value="<?= __paramInit('bool', 'opi_is_positive', 'opi_is_positive', false) ?>" />
    <input type="hidden" name="opi_not_negative" value="<?= __paramInit('bool', 'opi_not_negative', 'opi_not_negative', false) ?>" />
    <input type="hidden" name="only_free" value="<?= __paramInit('bool', 'only_free', 'only_free', false) ?>" />
    <input type="hidden" name="is_preview" value="<?= __paramInit('bool', 'is_preview', 'is_preview', false) ?>" />
    <input type="hidden" name="in_fav" value="<?= __paramInit('bool', 'in_fav', 'in_fav', false) ?>" />
    <? $searchValuesSBR = __paramInit('array', 'success_sbr', 'success_sbr', array(false, false, false)); ?>
    <input type="hidden" name="success_sbr[0]" value="<?= __paramValue('bool', $searchValues[0]) ?>" />
    <input type="hidden" name="success_sbr[1]" value="<?= __paramValue('bool', $searchValues[1]) ?>" />
    <input type="hidden" name="success_sbr[2]" value="<?= __paramValue('bool', $searchValues[2]) ?>" />
    <input type="hidden" name="success_sbr[3]" value="<?= __paramValue('bool', $searchValues[3]) ?>" />
    <input type="hidden" name="in_office" value="<?= __paramInit('bool', 'in_office', 'in_office', false) ?>" />
    <input type="hidden" name="is_pro" value="<?= __paramInit('bool', 'is_pro', 'is_pro', false) ?>" />
    <? $searchValuesExp = __paramInit('array', 'exp', 'exp', array()); ?>
    <input type="hidden"   name="exp[]" value="<?= __paramValue('int', $searchValuesExp[0]) ?>" />
    <input type="hidden"   name="exp[]" value="<?= __paramValue('int', $searchValuesExp[1]) ?>" />
    <? $searchValuesAge = __paramInit('array', 'age', 'age', array()); ?>
    <input type="hidden"   name="age[]" value="<?= __paramValue('int', $searchValuesAge[0]) ?>" />
    <input type="hidden"   name="age[]" value="<?= __paramValue('int', $searchValuesAge[1]) ?>" />
    <input type="hidden"   name="pf_country" value="<?= __paramInit('int', 'pf_country', 'pf_country', 0) ?>" />
    <input type="hidden"   name="pf_city" value="<?= __paramInit('int', 'pf_city', 'pf_city', 0) ?>" />
    <? $searchValuesProf = __paramInit('array', 'pf_categofy', 'pf_categofy', array());
    foreach ($searchValuesProf as $key1 => $profs) {
        foreach ($profs as $key2=>$prof) {
            echo '<input type="hidden" name="pf_categofy[' . $key1 . '][' . $key2 . ']" value="' . $key1 . '" />' . PHP_EOL;
        }
    }
    $searchValuesCostType = __paramInit('array', 'cost_type', 'cost_type', array());
    $searchValuesCostFrom = __paramInit('array', 'from_cost', 'from_cost', array());
    $searchValuesCostTo = __paramInit('array', 'to_cost', 'to_cost', array());
    $searchValuesCostCurr = __paramInit('array', 'curr_type', 'curr_type', array());
    foreach($searchValuesCostType as $key=>$value) {
        echo '<input type="hidden" name="cost_type[]" value="' . __paramValue('int', $value) . '" />';
        echo '<input type="hidden" name="from_cost[]" value="' . __paramValue('int', $searchValuesCostFrom[$key]) . '" />';
        echo '<input type="hidden" name="to_cost[]" value="' . __paramValue('int', $searchValuesCostTo[$key]) . '" />';
        echo '<input type="hidden" name="curr_type[]" value="' . __paramValue('int', $searchValuesCostCurr[$key]) . '" />';
    }
?>
<? } ?>

<input type="hidden" name="prof_group" id="prof_groups" value="" />


<div class="masss-res с">
    <div class="masss-res-b">
        <b class="b1"></b>
        <b class="b2"></b>

        <div class="masss-res-b-in" id="calc_done">
            <h4>Результат</h4>
            <p>Получателей:&nbsp;&nbsp;&nbsp;<strong id="users"><?=format($calc['count'])?></strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            Стоимость:&nbsp;&nbsp;&nbsp;<strong id="costFM"><?=format($calc['cost'])?></strong> руб.</p>
            <em class="masss-price">1 получатель &ndash; <?=format($tariff['no_pro'], 1)?> руб, 1 получатель с PRO &ndash; <?=format($tariff['pro'], 1)?> руб</em>
        </div>

        <div class="masss-res-b-in" id="calc_waiting" style="display: none;">
            <h4>Результат</h4>
            <p>Получателей:&nbsp;&nbsp;&nbsp;<span id="calc_waiting_users">расчитывается</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            Стоимость:&nbsp;&nbsp;&nbsp;<span id="calc_waiting_cost">расчитывается</span></p>
            <em class="masss-price">1 получатель &ndash; <?=format($tariff['no_pro'], 1)?> руб, 1 получатель с PRO &ndash; <?=format($tariff['pro'], 1)?> руб</em>
        </div>



        <b class="b2"></b>
        <b class="b1"></b>

    </div>

    <span class="mass-no-money" id="warning"><?=$masssending->error?></span>

    <div class="masss-btn">
        <div class="b-buttons">
            <a href="." onclick="this.blur(); return SendIt();" class="b-button b-button_flat b-button_flat_green">
            <span id="button_load_span">Отправить на модерацию</span>
            <img style="display: none;" id="button_load_img" height="6" width="26" alt="" src="/css/block/b-button/b-button__load.gif" class="b-button__load" />
            </a>	
            <span id="calc_msg" class="b-buttons__txt b-buttons__txt_color_80 b-buttons__txt_padleft_10"></span>
        </div>							
    </div>
<p style="padding-top:10px;">Премодерация рассылки осуществляется в рабочие часы с 10:00 до 18:00.</p>
</div>