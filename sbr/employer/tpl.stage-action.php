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
$work_time = $work_time < 0 ? 0 : $work_time; // Если проект уже просрочен даем еще 5 дней с момента отмены арбитража
$cdate = new LocalDateTime(date('d.m.Y', strtotime($start_time . ' + ' . $work_time . 'day')));
$cdate->getWorkForDay(pskb::PERIOD_EXP);
$days      = ($work_time + $cdate->getCountDays()) . "day";
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
// Если в арбитраже, действий делать никаких нельзя, независимо от статуса СБР
if($stage->data['status'] == sbr_stages::STATUS_INARBITRAGE || $stage->data['status'] == sbr_stages::STATUS_ARBITRAGED) return;

// Инициируем все доступные кнопки один раз @todo Чтото тут надо придумать
$edit = new buttons('Изменить условия', null, 'edit');
$edit->setLink("/" . sbr::NEW_TEMPLATE_SBR . "/?site=editstage&id={$stage->id}");
$edit->addEvent("onclick", "window.location = '/" . sbr::NEW_TEMPLATE_SBR . "/?site=editstage&id={$stage->id}';");

$cancel = new buttons('Отменить сделку', 'red', 'cancel');
$cancel->addEvent("onclick", "if(confirm('Отменить сделку?')) { submitForm($('actionSbrForm'), {action: 'status_action', cancel:1}); }");

$draft = new buttons('Отказаться, поместить проект в черновик', 'red', 'action_stage');
$draft->addEvent("onclick", "submitForm($('actionStageForm'), {action:'draft'})");

$arbitrage = new buttons('Обратиться в арбитраж', 'red', 'arbitrage');
$arbitrage->addEvent("onclick", "toggle_arb();");

$complete = new buttons('Принять работу', null, 'complete');
$complete->setLink("/" . sbr::NEW_TEMPLATE_SBR . "/?site=Stage&id={$stage->id}&event=complete");

$pause = new buttons('Поставить на паузу', null, 'pause');
$pause->addEvent("onclick", "view_sbr_popup('pause_confirm');");
//$pause->addEvent("onclick", "submitForm($('actionStageForm'), {action: 'change_status', status:" . sbr_stages::STATUS_FROZEN . "});");
      
$inwork = new buttons('Вернуть в работу', null, 'action_stage');
$inwork->addEvent("onclick", "submitForm($('actionStageForm'), {action: 'change_status', status:" . sbr_stages::STATUS_PROCESS . "});");

$resend = new buttons('Повторный запрос', null, 'action_stage');
$resend->addEvent("onclick", "submitForm($('actionStageForm'), {action: 'resolve_changes', resend:1});");

$rollback = new buttons('Отменить изменения', 'red', 'action_stage');
$rollback->addEvent("onclick", "submitForm($('actionStageForm'), {action: 'resolve_changes', cancel:1});");
                
$reserved = new buttons('Зарезервировать деньги', null, 'reserved');
$reserved->setLink("/" . sbr::NEW_TEMPLATE_SBR . "/?site=reserve&id={$sbr->id}");

switch($sbr->status) {
    case sbr::STATUS_NEW:
        $draft->setName("Поместить проект в черновик");
        $multi->addButton($cancel);
        $multi->addButton($edit);
        $multi->addButton($draft);
        
        break;
    case sbr::STATUS_CHANGED:
        if($sbr->data['reserved_id']) { // Деньги зарезервированы, тут еще зависимость от статусов будет
            if($stage->data['status'] == sbr_stages::STATUS_NEW) {
                //if($stage->num > 0 && $sbr->stages[$stage->num-1]->data['status'] == sbr_stages::STATUS_INARBITRAGE) {
                //    $inwork->setName('Поставить в работу');
                //    $multi->addButton($inwork);
                //}
                break; // Если этап не начат и деньги зарезервированы с этим этапом ничего нельзя делать???
            }
            // Из статуса Не начат - В работе -- можно изменить только 1 раз, причем пока фрилансер не согласился статус этапа менять нельзя
            if($stage->data['status'] == sbr_stages::STATUS_PROCESS) { 
                
                $multi->addButton($arbitrage);
                
                if($stage->v_data['status'] != sbr_stages::STATUS_NEW) {
                    $multi->addButton($pause);
                }
                $multi->addButton($edit);
                
                break;
            }
            
            if($stage->data['status'] == sbr_stages::STATUS_FROZEN) { // Этап на паузе ждем соглашения исполнителя с этим делом
                
                //$multi->addButton($inwork);
                $multi->addButton($arbitrage);
                $multi->addButton($edit);
            }
            
        } else { // Деньги не зарезервированы
            
            $multi->addButton($cancel);
            //$multi->addButton($draft);
            $multi->addButton($edit);
        }
        
        break;
    case sbr::STATUS_PROCESS:
        if($sbr->data['reserved_id']) { // Деньги зарезервированы
            
            if($stage_changed) { // Необходима реакция Заказчика
                
                $multi->addButton($edit);
                $multi->addButton($resend);
                $multi->addButton($rollback);
                $multi->addButton($arbitrage);
                
                break;
            }
            
            // Если этап не начат и деньги зарезервированы с этим этапом ничего нельзя делать??? После завершения предыдущего этапа он автоматически пойдет в работу
            if($stage->data['status'] == sbr_stages::STATUS_NEW) {
                //if($stage->num > 0 && $sbr->stages[$stage->num-1]->data['status'] == sbr_stages::STATUS_INARBITRAGE) {
                //    $inwork->setName('Поставить в работу');
                //    $multi->addButton($inwork);
                //}
                break; 
            }
            if($stage->data['status'] == sbr_stages::STATUS_PROCESS) { // Этап в работе
                
                $multi->addButton($complete);
                $multi->addButton($arbitrage);
                $multi->addButton($pause);
                $multi->addButton($edit);
            }
            
            if($stage->data['status'] == sbr_stages::STATUS_FROZEN) { // Этап на паузе
                
                $multi->addButton($inwork);
                $multi->addButton($arbitrage);
                $multi->addButton($complete);
                $multi->addButton($edit);
            }
            
        } elseif($stage_changed) { // Исполнитель отказался от изменений
            $edit->setName('Изменить условия сделки');
            
            $multi->addButton($edit);
            $multi->addButton($resend);
            $multi->addButton($rollback);
            //$multi->addButton($draft);
            $multi->addButton($cancel);

        } elseif($sbr_changed) { // Если какая то сделка требует реакции от работодателя, пока деньги не зарезервированы
            
            $multi->addButton($cancel);
            //$multi->addButton($draft);
            $multi->addButton($edit);
            
        } else { // Деньги еще не зарезервированы
            
            $multi->addButton($reserved);
            //$multi->addButton($draft);
            $multi->addButton($cancel);
            $multi->addButton($edit);
        }
        break;
    case sbr::STATUS_REFUSED:
    case sbr::STATUS_CANCELED:
        $draft->setName('Поместить проект в черновик');
        
        $edit->setLink("/sbr/?site=edit&id={$sbr->id}");
        
        $multi->addButton($edit);
        $multi->addBUtton($draft);
        
        break;
}
// Если время вышло #0020166 #0023680
if(time() > $overtime && $overtime !== null) {
    $multi->removeButton($arbitrage);
}
 
// Выводим сгенерированные кнопки
$multi->view();

// Если есть кнопка арбитража
if($multi->isButton('arbitrage')) {
    include ($_SERVER['DOCUMENT_ROOT'] . "/sbr/arbitrage.php");
}

// Если есть кнопка отмены СБР
if($multi->isButton('cancel')) {
    ?>
    <form id="actionSbrForm" action="?id=<?= $sbr->id;?>" method="post">
    	<div>
            <input type="hidden" name="cancel" value="" />
            <input type="hidden" name="id" value="<?= $sbr->id;?>" />
            <input type="hidden" name="action" value="" />
        </div>
    </form>
    <?
}

// Если есть кнопка Поставить на паузу
if($multi->isButton('pause') ) {
    $dateMaxLimit = "date_max_limit_" . date('Y_m_d', strtotime('+ 30 days'));
    $dateMinLimit = "date_min_limit_" . date('Y_m_d', strtotime('+ 1 day'));
    ?>
    <div class="i-shadow i-shadow_zindex_110" id="pause_confirm">
        <div class="b-shadow b-shadow_center b-shadow_width_350 b-shadow_hide">
            <div class="b-shadow__right">
                <div class="b-shadow__left">
                    <div class="b-shadow__top">
                        <div class="b-shadow__bottom">
                            <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_20">
                                <h2 class="b-shadow__title b-shadow__title_padbot_10">Пауза по этапу</h2>
                                <div class="b-shadow__txt b-shadow__txt_padbot_20 b-shadow__txt_fontsize_11">Установите срок паузы в любом из полей ниже</div>

                                <div class="b-layout__txt b-layout__txt_padtop_4 b-layout__txt_inline-block b-layout__txt_width_20">на</div>
                                <div class="b-combo b-combo_inline-block b-combo_padbot_20">
                                    <div class="b-combo__input b-combo__input_multi_dropdown show_all_records use_scroll b-combo__input_visible_height_200 b-combo__input_width_170 b-combo__input_arrow_yes b-combo__input_init_listPauseDays drop_down_default_7">
                                        <input class="b-combo__input-text" id="count_pause_days" name="count_pause_days" type="text" size="80" value="7 дней" onchange="changePauseDays();"/>
                                    </div>
                                </div>
                                <div class="b-layout__txt">
                                    <div class="b-layout__txt b-layout__txt_padtop_4 b-layout__txt_inline-block b-layout__txt_width_20">до</div>
                                    <div class="b-combo b-combo_inline-block">
                                        <div class="b-combo__input b-combo__input_calendar b-combo__input_width_170 b-combo__input_arrow-date_yes use_past_date <?= $dateMinLimit; ?> <?= $dateMaxLimit ?>">
                                            <input class="b-combo__input-text" id="pause_date" name="pause_date" type="text" size="80" value="<?= date("d.m.Y", strtotime('+7day')); ?>" onchange="changePauseDays(1);"/>
                                            <span class="b-combo__arrow-date"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="b-buttons b-buttons_padleft_25 b-buttons_padtb_20">
                                    <a class="b-button b-button_flat b-button_flat_green" href="javascript:void(0)" onclick="submitForm($('actionStageForm'), {action: 'change_status', status: '<?= sbr_stages::STATUS_FROZEN?>', days: $('count_pause_days_db_id').get('value')});" style=" overflow:visible;">Пауза</a>
                                </div>
                                <div class="b-shadow__txt b-shadow__txt_fontsize_11">Максимальная длительность паузы &mdash; 30 календарных дней. Работа по сделке будет автоматически возобновлена по истечении назначенного срока.</div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <a href="javascript:void(0);" onclick="$('pause_confirm').getElement('.b-shadow').addClass('b-shadow_hide'); $('b-shadow_sbr__overlay').dispose(); return false;"><span class="b-shadow__icon b-shadow__icon_close"></span></a>
        </div>
    </div>
    <?
}

// Если есть кнопка Принять работу
if($multi->isButton('complete') ) {
    ?>
    <div class="i-shadow i-shadow_zindex_110" id="completed_confirm">
        <div class="b-shadow b-shadow_hide b-shadow_center" >
            <div class="b-shadow__right">
                <div class="b-shadow__left">
                    <div class="b-shadow__top">
                        <div class="b-shadow__bottom">
                            <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_20 b-layout">
                                <div class="b-shadow__txt b-shadow__txt_padbot_5">Вы уверены, что хотите принять работу?</div> 
                                <div class="b-shadow__txt b-shadow__txt_padbot_10">Обратите внимание: отменить действие будет невозможно.<br />Исполнитель получит деньги за данный этап «Безопасной Сделки».</div>
                                <div class="b-buttons ">
                                    <a href="javascript:void(0)" onclick="submitForm($('actionStageForm'), {action: 'change_status', status: '<?= sbr_stages::STATUS_COMPLETED?>'});" class="b-button b-button_flat b-button_flat_green">Принять работу</a>
                                    <span class="b-buttons__txt">&#160;&#160;&#160;или</span>
                                    <a class="b-buttons__link b-buttons__link_dot_0f71c8" href="javascript:void(0)" onclick="$('completed_confirm').getElement('.b-shadow').addClass('b-shadow_hide'); $('b-shadow_sbr__overlay').dispose(); return false;">отменить</a>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="b-shadow__tl"></div>
            <div class="b-shadow__tr"></div>
            <div class="b-shadow__bl"></div>
            <div class="b-shadow__br"></div>
            <a href="javascript:void(0);" onclick="$('completed_confirm').getElement('.b-shadow').addClass('b-shadow_hide'); $('b-shadow_sbr__overlay').dispose(); return false;"><span class="b-shadow__icon b-shadow__icon_close"></span></a>
        </div>
    </div>
    <!-- <div class="b-shadow__overlay b-shadow__overlay_bg_black" id="b-shadow_sbr__overlay"></div> -->
    <?
}

// Если есть кнопки из блока действий по этапу сделки
if($multi->isButton('action_stage') || $multi->isButton('complete') || $multi->isButton('pause')) {
    ?>
    <form id="actionStageForm" method="post">
    	<div>
            <input type="hidden" name="cancel" value="0" />
            <input type="hidden" name="resend" value="0" />
            <input type="hidden" name="id" value="<?=$stage->id?>" />
            <input type="hidden" name="site" value="<?=$site?>" />
            <input type="hidden" name="status" value="" />
            <input type="hidden" name="action" value="" /> 
            <input type="hidden" name="days" value="" />
        </div>
    </form>   
<? 
}
?>