<? 
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/buttons/multi_buttons.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/LocalDateTime.php");
$multi = new multi_buttons();

if($stage->version != $stage->frl_version) {// Фрилансер еще не согласился, для расчета берем старую дату
    $frl_version = $stage->getVersion($stage->frl_version, $stage->data);
    $work_time = intval($frl_version['work_time']);
    $start_time = $frl_version['start_time'];
} else {
    $work_time = intval($stage->work_time);
    $start_time = $stage->start_time;
}
$work_time = $work_time < 0 ? 0 : $work_time;
$cdate = new LocalDateTime(date('d.m.Y', strtotime($start_time . ' + ' . $work_time . 'day')));
$cdate->getWorkForDay(pskb::PERIOD_EXP);
$days      = ($work_time + pskb::PERIOD_EXP) . "day";
$overtime  = strtotime($start_time . ' + ' . $days);
if($sbr->data['lc_id'] > 0) {
    $overtime = strtotime($sbr->data['dateEndLC'] . ' - ' . pskb::ARBITRAGE_PERIOD_DAYS . " day");
    // Сб, Вс не рабочие дни
    if(date('w', $overtime) == 0 || date('w', $overtime) == 6) {
        $d = date('w', $overtime) == 6 ? 1 : 2;
        $overtime = $overtime - ($d * 3600* 24);
    }
} else {
    $overtime = null;
}

// Если в арбитраже действий, делать никаких нельзя, независимо от статуса СБР, если завершено кнопок уже никаких не будет
if($stage->data['status'] == sbr_stages::STATUS_INARBITRAGE || $stage->data['status'] == sbr_stages::STATUS_ARBITRAGED || $stage->status == sbr_stages::STATUS_COMPLETED) return;

// Инициируем все доступные кнопки один раз @todo Что-то тут надо придумать
$arbitrage = new buttons('Обратиться в арбитраж', 'red', 'arbitrage');
$arbitrage->addEvent("onclick", "toggle_arb();");

$condition = new buttons('Посмотреть условия сделки', null, 'condition');
$condition->setLink("/" . sbr::NEW_TEMPLATE_SBR . "/?site=master&id={$sbr->id}");

$refuse = new buttons('Отказаться от сделки', 'red', 'refuse');
$refuse->addEvent("onclick", "$('refuse_dialog').toggleClass('b-shadow_hide'); return false;");

$agree = new buttons('Согласиться с изменениями', null, 'action_stage');
$agree->addEvent("onclick", "submitForm($('actionStageForm'), {action:'agree_stage', ok:1});");

$refuse_stage = new buttons('Отказаться от изменений', 'red', 'refuse_stage');
$refuse_stage->addEvent("onclick", "$('refuse_stage_dialog').toggleClass('b-shadow_hide'); return false;");

switch($sbr->status) {
    case sbr::STATUS_NEW:
        
        $multi->addButton($condition);
        $multi->addButton($refuse);
        
        break;
    case sbr::STATUS_CHANGED:
        if($sbr->data['reserved_id']) { // Деньги зарезервированы, тут еще зависимость от статусов будет
            if($stage_changed) { 
                if($stage->data['status'] == sbr_stages::STATUS_PROCESS && $stage->v_data['status'] == sbr_stages::STATUS_NEW) {
                    $agree->setName('Приступить к работе'); // Меняем название кнопки
                }

                $multi->addButton($agree);

                // Отказатся нельзя от изменений когда сделку переводят из состояния Не начат
                if($stage->v_data['status'] != sbr_stages::STATUS_NEW) { 
                    $multi->addButton($refuse_stage);
                }
            }
            if($stage->status != sbr_stages::STATUS_NEW) {
                $multi->addButton($arbitrage);
            }
        } else { // Деньги не зарезервированы
            // Если есть изменения в текущей сделке
            if($stage_changed) {
                $multi->addButton($agree);
                $multi->addButton($refuse_stage);
            } 
            
            $multi->addButton($refuse);
        }
        
        break;
    case sbr::STATUS_PROCESS:
        if($sbr->data['reserved_id'] && $stage->status != sbr_stages::STATUS_NEW) { // Деньги зарезервированы
            $multi->addButton($arbitrage);
        } else { // Деньги еще не зарезервированы
            $multi->addButton($refuse);
        }
        break;
    case sbr::STATUS_REFUSED:
    case sbr::STATUS_CANCELED:
        
        break;
}

// Если время вышло #0020166 #0023680
if(time() > $overtime && $overtime !== null) {
    $multi->removeButton($arbitrage);
}

if($sbr->lc_id > 0) {
    $multi->removeButton($refuse);
}

// Выводим сгенерированные кнопки
$multi->view();
// Если есть кнопка арбитража
if($multi->isButton('arbitrage')) {
    include ($_SERVER['DOCUMENT_ROOT'] . "/sbr/arbitrage.php");
}

// Если есть кнопки из блока действий по этапу сделки
if($multi->isButton('action_stage')) {
    ?>
    <form method="post" id="actionStageForm">
        <input type="hidden" name="ok" value="" />
        <input type="hidden" name="version" value="<?=$stage->version?>" />
        <input type="hidden" name="sbr_version" value="<?=$sbr->version?>" />
        <input type="hidden" name="id" value="<?=$stage->id?>" />
        <input type="hidden" name="site" value="<?=$site?>" />
        <input type="hidden" name="action" value="" /> 
    </form>   
    <?
}

if($multi->isButton('refuse_stage')) {
    ?>
    <form method="post" id="refuseStageForm">
        <div class="b-shadow b-shadow_center b-shadow_zindex_11 b-shadow_width_620 b-shadow_hide" id="refuse_stage_dialog">
            <div class="b-shadow__right">
                <div class="b-shadow__left">
                    <div class="b-shadow__top">
                        <div class="b-shadow__bottom">
                            <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_20">
                                <h1 class="b-shadow__title b-shadow__title_fontsize_34 b-shadow__title_padbot_15">Отказ от изменений</h1>
                                <div class="b-shadow__txt b-shadow__txt_padbot_20">Пожалуйста, укажите причину, по которой вы не принимаете изменения:</div>
                                <div class="b-textarea">
                                        <textarea class="b-textarea__textarea b-textarea__textarea_height_140 max-height_140 noresize" name="frl_refuse_reason" cols="" rows=""></textarea>
                                </div>
                                <div class="b-buttons b-buttons_padtop_15">
                                    <a class="b-button b-button_flat b-button_flat_green"  href="javascript:void(0)" onclick="submitForm($('refuseStageForm'))">Отправить отказ</a>
                                    <span class="b-buttons__txt b-buttons__txt_padleft_10">или</span>
                                    <a class="b-buttons__link b-buttons__link_dot_c10601" href="javascript:void(0)" onclick="$('refuse_stage_dialog').toggleClass('b-shadow_hide'); return false;">закрыть, не отправляя</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <span class="b-shadow__icon b-shadow__icon_close"></span>
        </div>
        <input type="hidden" name="ok" value="" />
        <input type="hidden" name="version" value="<?=$stage->version?>" />
        <input type="hidden" name="sbr_version" value="<?=$sbr->version?>" />
        <input type="hidden" name="site" value="<?=$site?>" />
        <input type="hidden" name="id" value="<?=$stage->id?>" />
        <input type="hidden" name="action" value="agree_stage" />
    </form>
    <?
}

if($multi->isButton('refuse')) {
    ?>
    <form action="?id=<?= $sbr->id;?>" method="post" id="refuseForm">
        <div class="b-shadow b-shadow_center b-shadow_zindex_11 b-shadow_width_620 b-shadow_hide" id="refuse_dialog">
            <div class="b-shadow__right">
                <div class="b-shadow__left">
                    <div class="b-shadow__top">
                        <div class="b-shadow__bottom">
                            <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_20">
                                <h1 class="b-shadow__title b-shadow__title_fontsize_34 b-shadow__title_padbot_15">Отказ от сделки</h1>
                                <div class="b-shadow__txt b-shadow__txt_padbot_20">Пожалуйста, укажите причину, по которой вы отказываетесь от сотрудничества в сделке:</div>
                                <div class="b-textarea">
                                        <textarea class="b-textarea__textarea b-textarea__textarea_height_140 max-height_140 noresize" name="frl_refuse_reason" cols="" rows=""></textarea>
                                </div>
                                <div class="b-buttons b-buttons_padtop_15">
                                    <a class="b-button b-button_flat b-button_flat_green"  href="javascript:void(0)" onclick="submitForm($('refuseForm'), {'refuse' : 1})">Отправить отказ</a>
                                    <span class="b-buttons__txt b-buttons__txt_padleft_10">или</span>
                                    <a class="b-buttons__link b-buttons__link_dot_c10601" href="javascript:void(0)" onclick="$('refuse_dialog').toggleClass('b-shadow_hide'); return false;">закрыть, не отправляя</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <span class="b-shadow__icon b-shadow__icon_close"></span>
        </div>
        <input type="hidden" name="refuse" value="">
        <input type="hidden" name="id" value="<?=$sbr->data['id']?>" />
        <input type="hidden" name="version" value="<?=$sbr->data['version']?>" />
        <input type="hidden" name="action" value="agree" />
    </form>
    <?
}

?>

