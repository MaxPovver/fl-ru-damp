<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/public.common.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/projects.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/project_exrates.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/CFile.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/sbr_stages.php");
$xajax->printJavascript('/xajax/');

$projectIsEdit = $tmpPrj->isEdit();

if ($project['kind'] == 7) {
    $title = $project['id'] ? 'Конкурс' : 'Новый конкурс';
    $isContest = true;
    $isVacancy = false;
    $titleHint = 'Цель конкурса. Например: Придумать логотип';
    $descrHint = 'Подробно опишите условия конкурса, сроки, другие условия участия.';
    // новая система расчета стоимости публикации конкурса (текущая дата больше даты ввода новой системы, а также это не редактируется проект опубликованный до ввода новой системы)
    $newContestBudget = new_projects::isNewContestBudget($project['post_date']);
    $minBudget = $newContestBudget ? new_projects::NEW_CONTEST_MIN_BUDGET : new_projects::CONTEST_MIN_BUDGET;
} elseif ($project['kind'] == 4) {
    $title = $project['id'] ? 'Вакансия' : 'Новая вакансия';
    $isContest = false;
    $isVacancy = true;
    $titleHint = 'Специалист какой квалификации и на какие задачи вам требуется. Например: Дизайнер иконок на проект';
    $descrHint = 'Подробно опишите задачу, сроки выполнения, другие условия работы.';
} else {
    $title = $exec 
		? ($project['id'] ? 'Персональный проект' : 'Новый персональный проект') 
		: ($project['id'] ? 'Проект' : 'Новый проект (задание на разовую работу)');
    $isContest = false;
    $isVacancy = false;
    $titleHint = 'Что требуется сделать. Например: Дизайн для интернет-магазина детской одежды';
    $descrHint = 'Подробно опишите задачу, сроки выполнения, другие условия работы.';
}

$project['end_date'] = $project['end_date'] ? date('d.m.Y', strtotime($project['end_date'])) : '';
$project['win_date'] = $project['win_date'] ? date('d.m.Y', strtotime($project['win_date'])) : '';

if ($project['logo_id']) {
    $logoFile = new CFile($project['logo_id']);
}

$isVacancyPayed = $tmpPrj->isNotPayedVacancy() == false;

$addedPrc = is_pro() ? 0 : new_projects::PRICE_ADDED;

$priceVacancy = new_projects::getProjectInOfficePrice(is_pro());
$priceVacancy = ($project['id'] && $isVacancyPayed)  ? 0 : $priceVacancy; // если вакансия уже оплачена

$priceContest = new_projects::getKonkursPrice(is_pro());
$priceContest = $project['id'] ? 0 : $priceContest; // если конкурс уже оплачен

if ($isContest) {
    $priceTopDay = is_pro() ? new_projects::PRICE_CONTEST_TOP1DAY_PRO : new_projects::PRICE_CONTEST_TOP1DAY;
} else {
    $priceTopDay = is_pro() ? new_projects::PRICE_TOP1DAYPRO : new_projects::PRICE_TOP1DAY;
}
$priceTopDay += $addedPrc;

$urgentPrice = new_projects::PRICE_URGENT;
$hidePrice = new_projects::PRICE_HIDE;

$priceLogo = is_pro() ? new_projects::PRICE_LOGO : new_projects::PRICE_LOGO_NOPRO;
//$priceLogo += $addedPrc;
$priceLogo = ($project['id'] && $project['logo_id']) ? 0 : $priceLogo; // если логотип уже оплачен

$pExrates = project_exrates::getAll();

//Поля название и цена пробуем заполнить из сессии
if (!$project['name'] && isset($_SESSION['new_project_name'])) {
	$project['name'] = $_SESSION['new_project_name'];
	unset($_SESSION['new_project_name']);
}
if (!$project['cost'] && isset($_SESSION['new_project_cost'])) {
	$project['cost'] = $_SESSION['new_project_cost'];
	unset($_SESSION['new_project_cost']);
	$project['currency'] = 2;
}

$ablePublic = true;

$hideSaveBtn = !$ablePublic || ($project['kind'] == 4 && !$isVacancyPayed && $project['trash'] == 't');
?>
<script>
    window.Public || (window.Public = {});
    Public.step = <?=($step == 2)?2:1?>;
    Public.usePopup = false;
    Public.attachTZSession = '<?= $attachTZ->session[0] ?>';
    Public.attachTZFiles = <?= json_encode($attachTZFiles) ?>;
    Public.attachLogoSession = '<?= $attachLogo->session[0] ?>';
    //Public.attachLogoFiles = <?//= json_encode($attachLogoFiles) ?>;
    
    Public.userIsPro = <?= (int)(bool)is_pro() ?>;
    // сколько денег у пользователя
    Public.accSum = <?= round($_SESSION['ac_sum'], 2) ?>;
    Public.bonSum = <?= round($_SESSION['bn_sum'], 2) ?>;
    // стоимость платных услуг
    Public.vacancyPrice = <?= $priceVacancy ?>;
    Public.topDayPrice = <?= $priceTopDay ?>;
    Public.logoPrice = <?= $priceLogo ?>;
    Public.contestPrice = <?= $priceContest ?>;
    Public.urgentPrice = <?= $urgentPrice ?>;
    Public.hidePrice = <?= $hidePrice ?>;
    
    Public.isVacancyPayed = <?= (int)$isVacancyPayed ?>;
    
    Public.pExrates = {};
    Public.pExrates.usd = <?= (float)$pExrates[24] ?>; // сколько рублей за один доллар
    Public.pExrates.euro = <?= (float)$pExrates[34] ?>; // сколько рублей за один евро
    
    Public.isContest = <?= (int)$isContest ?>;
    Public.isVacancy = <?= (int)$isVacancy ?>;
	Public.isPersonal = <?= (int)$is_personal ?>;
    
    Public.nameMaxLength = 60; // максимальная длина названия проекта
    Public.descrMaxLength = 5000; // максимальная длина описания проекта
    
    Public.minBudget = <?= (int)$minBudget ?>;
    
    Public.isEdit = <?= (int)$projectIsEdit ?>;
    
    // если false, то это значит что публиковать бесплатные проекты бельше нельзя
    Public.ablePublic = <?= (int)$ablePublic ?>;
    
    // минимальный бюджет безопасной сделки
    Public.minSbrBudget = <?= (int)sbr_stages::MIN_COST_RUR ?>;
    
    Public.newTemplate = 1;
    
    <? if ($isContest) { ?>
    Public.contestTaxes = <?= json_encode(new_projects::getContestTaxes()) ?>;
    Public.newContestBudget = <?= (int)$newContestBudget ?>;
    <? } ?>

    function useYaMetrik() {
      <? if(!$projectIsEdit) { ?>
      if(Public.isContest!=1 && Public.isVacancy!=1) {
        if(Public.isPersonal!=1 && $('project_save_btn_sum').get('html')) {  
        } else {
        }
      }

      if(Public.isContest==1) {
        if($('project_save_btn_sum').get('html')) {
        }
      }

      if(Public.isVacancy==1) {
        if($('project_save_btn_sum').get('html')) {
        }
      }
      <? } ?>
    }
    
    <? if ($use_draft) { ?>
    DraftInit(1);
    <? } ?>
    
    <?php if(__paramInit('bool', 'popup', 'popup', false) && $project['kind'] == projects::KIND_VACANCY): ?>
        window.addEvent('domready', function() {
           if ($('quick_pro_win_main')) {
               $('project_save_btn').fireEvent('click');
           } 
        });
    <?php endif; ?>
    
</script>
<table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">

    <tr class="b-layout__tr">
        <td class="b-layout__td b-layout__td_padright_20">
            <h1 id="project_title" class="b-page__title b-page__title_padbot_10">
                <?php if ($step > 1): ?>
                    <?= $title ?> &laquo;<?=$project['name']?>&raquo; опубликован<?php if($isVacancy):?>a<?php endif; ?>.
                <?php else: ?>
                    <?= $title ?>
                <?php endif; ?>
            </h1>
            <?php if ($step > 1): ?>
                <div class="b-layout__txt b-layout__txt_fontsize_18 b-layout__txt_padbot_20">
                    Выделите <?php if($isVacancy):?>вакансию<? elseif ($isContest):?>конкурс<?php else: ?>проект<?php endif; ?> в ленте и получите больше откликов от лучших исполнителей.
                </div>
            <?php else: ?>
                <?php if(($isVacancy || !$isContest) && hasPermissions('projects')): ?>
                    <div class="b-layout__txt b-layout__txt_fontsize_18 b-layout__txt_padbot_20">
                        <a href="/guest/new/<?=($isVacancy)?'vacancy':'project'?>/" class="b-layout__link">Опубликовать от имени другого работодателя</a>
                    </div>
                <?php endif; ?>
                <? if (!$isVacancy) { ?>
                    <div class="b-layout__txt b-layout__txt_padbot_20 b-layout__txt_fontsize_15">Для найма Исполнителя на регулярную работу &mdash; <a href="/public/?step=1&kind=4" class="b-layout__link">разместите вакансию</a></div>
                <?php } ?>
            <?php endif; ?>
        </td>
        <td rowspan="2" class="b-layout__td b-layout__td_width_270 b-fon_bg_fa b-layout__td_pad_10 b-fon__puble">
               <? if (($project['kind'] == 4)||($project['kind'] == 7)) { ?>
                  <div class="b-layout b-layout_margbot_30">
                     <table class="b-layout__table b-layout__table_width_full">
                        <tr class="b-layout__tr">
                           <td class="b-layout__td b-layout__td_width_60 b-layout__td_center"><img class="b-pic" src="/images/project-logo.png" width="33" height="45"></td>
                           <td class="b-layout__td b-layout__td_padleft_10">
                              <div class="b-layout__txt b-layout__txt_padbot_20 b-layout__txt_fontsize_15">Для поиска онлайн-исполнителя на разовую работу</div>
                              <a class="b-button b-button_flat b-button_flat_green b-button_nowrap" href="?step=1&kind=1">Добавьте проект</a>
                           </td>
                        </tr>
                     </table>
                  </div>
                  <? if ($project['kind'] == 4) { ?>
                     <div class="b-layout b-layout_margbot_30">
                        <table class="b-layout__table b-layout__table_width_full">
                           <tr class="b-layout__tr">
                              <td class="b-layout__td b-layout__td_width_60 b-layout__td_center"><img class="b-pic" src="/images/contest-logo.png" width="45" height="44"></td>
                              <td class="b-layout__td b-layout__td_padleft_10">
                                 <div class="b-layout__txt b-layout__txt_padbot_20 b-layout__txt_fontsize_15">Для выбора лучшего<br>результата из нескольких</div>
                                 <a class="b-button b-button_flat b-button_flat_green b-button_nowrap" href="?step=1&kind=7">Устройте конкурс</a>
                              </td>
                           </tr>
                        </table>
                     </div>
                  <?php } else { ?>
                     <div class="b-layout">
                        <table class="b-layout__table b-layout__table_width_full">
                           <tr class="b-layout__tr">
                              <td class="b-layout__td b-layout__td_width_60 b-layout__td_center"><img class="b-pic" src="/images/vacancy-logo.png" width="45" height="52"></td>
                              <td class="b-layout__td b-layout__td_padleft_10">
                                 <div class="b-layout__txt b-layout__txt_padbot_20 b-layout__txt_fontsize_15">Для найма Исполнителя<br>на регулярную работу</div>
                                 <a class="b-button b-button_flat b-button_flat_green b-button_nowrap" href="?step=1&kind=4">Разместите вакансию</a>
                              </td>
                           </tr>
                        </table>
                     </div>
                  <?php } ?>
                  
               <? } else {//if?>
                     <div class="b-layout b-layout_margbot_30">
                        <table class="b-layout__table b-layout__table_width_full">
                           <tr class="b-layout__tr">
                              <td class="b-layout__td b-layout__td_width_60 b-layout__td_center"><img class="b-pic" src="/images/vacancy-logo.png" width="45" height="52"></td>
                              <td class="b-layout__td b-layout__td_padleft_10">
                                 <div class="b-layout__txt b-layout__txt_padbot_20 b-layout__txt_fontsize_15">Для найма Исполнителя<br>на регулярную работу</div>
                                 <a class="b-button b-button_flat b-button_flat_green b-button_nowrap" href="?step=1&kind=4">Разместите вакансию</a>
                              </td>
                           </tr>
                        </table>
                     </div>
                     <div class="b-layout">
                        <table class="b-layout__table b-layout__table_width_full">
                           <tr class="b-layout__tr">
                              <td class="b-layout__td b-layout__td_width_60 b-layout__td_center"><img class="b-pic" src="/images/contest-logo.png" width="45" height="44"></td>
                              <td class="b-layout__td b-layout__td_padleft_10">
                                 <div class="b-layout__txt b-layout__txt_padbot_20 b-layout__txt_fontsize_15">Для выбора лучшего<br>результата из нескольких</div>
                                 <a class="b-button b-button_flat b-button_flat_green b-button_nowrap" href="?step=1&kind=7">Устройте конкурс</a>
                              </td>
                           </tr>
                        </table>
                     </div>
               <?php } ?>
 
            <?php /*<div id="recomend_tu"></div> */ ?>
        </td>
     </tr>
    <tr class="b-layout__tr">
        <td class="b-layout__td b-layout__td_padright_20">
            <form action="/public/" method="post" enctype="multipart/form-data" id="frm">
                <input type="hidden" id="is_exec_quickprj" name="is_exec_quickprj" value="0" />
                <input type="hidden" id="draft_id" name="draft_id" value="<?= $draft_id ?>" />
                <input type="hidden" id="draft_prj_id" name="draft_prj_id" value="<?= $draft_prj_id ?>" />
                <input type="hidden" name="public" value="<?= $project['id'] ?>" />
                <input type="hidden" name="action" value="save" />
                <input type="hidden" name="step" value="1" />
                <input type="hidden" name="kind" value="<?= $project['kind'] ?>" />
				<input type="hidden" name="exec" value="<?= $exec ?>" />
                <input type="hidden" name="pk" value="<?= $key ?>" />
                <input name="attachedfiles_type" id="p_attachedfiles_type" type="hidden" value="project_logo" />
                <input name="attachedfiles_session[]" id="p_attachedfiles_session" type="hidden" />
                <input name="logo_attachedfiles_session" id="p_logo_attachedfiles_session" type="hidden" value="<?= $attachLogo->session[0] ?>" />
                <? if ($logoFile) { ?>
                <input type="hidden" name="logo_file_id" value="<?= $logoFile->id ?>" />
                <? } ?>
                
                <table class="b-layout__table b-layout__table_width_full <?php if ($step > 1): ?>b-layout_hide<?php endif; ?>" cellpadding="0" cellspacing="0" border="0">
					<?php if ($freelancer) { ?>
					<tr class="b-layout__tr">
						<td class="b-layout__one b-layout__one_padbot_20">
                     <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_5">Исполнитель</div>
							<div class="b-layout__txt b-layout__txt_bold">
								<?=$freelancer->uname?> <?=$freelancer->usurname?> [<?=$freelancer->login?>] 
								<?= view_mark_user(array(
									"login"      => $freelancer->login, 
									"is_pro"      => $freelancer->is_pro,
									"is_pro_test" => $freelancer->is_pro_test,
									"is_team"     => $freelancer->is_team,
									"role"        => $freelancer->role), '', true, "&nbsp;");?>
							</div>
						</td>
						<td class="b-layout__one b-layout__one_width_70 b-layout__one_padbot_20"></td>
					</tr>
					<?php } ?>
                    <tr class="b-layout__tr">
                        <td class="b-layout__one b-layout__one_padbot_20">
                            <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_5">Название <? if ($project['kind'] == 4) { ?>вакансии<?php } else if($project['kind'] == 7) { ?>конкурса<?php } else { ?>проекта<?php } ?></div>
                            <div class="b-combo">
                                <div class="b-combo__input b-combo__input_height_35">
                                    <input autofocus class="b-combo__input-text" name="name" id="project_name" type="text" size="80" maxlength="60" value="<?= str_replace('"', "&quot;", $project['name']) ?>" placeholder="Кого вы ищете и какую работу нужно выполнить" />
                                </div>
                            </div>
                            <div id="project_name_error" class="b-layout__txt b-layout__txt_padtop_3 b-layout__txt_color_c10600 b-layout_hide">
                                <span class="b-icon b-icon_sbr_rattent"></span><span id="project_name_error_text"></span>
                            </div>
                            <? /*= $titleHint*/ ?>
                        </td>
                        <td class="b-layout__one b-layout__one_width_70 b-layout__one_padbot_20">
                              <div class="b-layout__txt b-layout__txt_padleft_5 b-layout__txt_padtop_30 b-layout__txt_fontsize_11"><span id="project_name_counter">0</span>/60</div>
                        </td>
                    </tr>
            
                    <tr class="b-layout__tr">
                        <td class="b-layout__one b-layout__one_padbot_5">
                            <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_5">Подробно опишите задание</div>
                            <div class="b-textarea">
                                <textarea class="b-textarea__textarea b-textarea__textarea_min-height_200" id="f2" name="descr" cols="80" rows="5" placeholder="Укажите требования к исполнителю и результату, сроки выполнения и другие условия работы"><?= $project['descr'] ?></textarea>
                            </div>
                            <div id="project_descr_error" class="b-layout__txt b-layout__txt_padtop_3 b-layout__txt_color_c10600 b-layout_hide">
                                <span class="b-icon b-icon_sbr_rattent"></span><span id="project_descr_error_text"></span>
                            </div>
                            <? /*= $descrHint */?>
                        </td>
                        <td class="b-layout__one b-layout__one_width_70 b-layout__one_padbot_5">
                              <div class="b-layout__txt b-layout__txt_padleft_5 b-layout__txt_padtop_30 b-layout__txt_fontsize_11"><span id="project_descr_counter">0</span>/5000</div>
                        </td>
                    </tr>
                    <tr class="b-layout__tr">
                        <td class="b-layout__one b-layout__one_padbot_20">
                            <div id="apf-files" class="b-layout b-layout_relative">
                                <div id="attachedfiles"></div>
                            </div>
                        </td>
                        <td class="b-layout__one b-layout__one_width_70 b-layout__one_padbot_20">&#160;</td>
                    </tr>
                    <? 
                    $templates = array(
                        uploader::getTemplate('uploader', 'project/'),
                        uploader::getTemplate('uploader.file', 'project/'),
                        uploader::getTemplate('uploader.popup', ''),
                    );
                    uploader::init(array(
                        'attachedfiles'  => uploader::sgetLoaderOptions($uploader->resource)
                    ), $templates);
                    ?>

					<?php if (!$is_personal) { ?>
                    <tr class="b-layout__tr">
                        <td class="b-layout__one b-layout__one_padbot_10">
                            <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_5">Специализация <? if ($project['kind'] == 4) { ?>вакансии<?php } else if($project['kind'] == 7) { ?>конкурса<?php } else { ?>проекта<?php } ?></div>
                            <?php
                                
                                for ($i = 0; $i < (is_pro() ? 3 : 1); $i++) { 
                                    
                                    $prof_names = array();
                                    if(isset($prj_categories[$i]['prof_name'])) {
                                        $prof_names = explode(': ', $prj_categories[$i]['prof_name']);
                                    }
                                    
                                    $group_name = array_shift($prof_names);
                                    $prof_name = array_shift($prof_names);
                            ?>
                                <div project_prof_id="<?= $i ?>" class="b-layout__txt <?=($i>0)?'b-layout__txt_padtop_20':''?> project_prof <?= ($i !== 0 && !$prj_categories[$i]['prof_name']) ? 'b-layout_hide' : '' ?> ">

                                    <div class="b-combo b-combo_margbot_10 b-combo_inline-block">
                                      <div class="
                                           b-combo__input 
                                           b-combo__input_multi_dropdown 
                                           b-combo__input_arrow_yes 
                                           b-combo__input_init_professionsList 
                                           show_all_records 
                                           sort_cnt 
                                           exclude_value_0_0 
                                           b-combo__input_height_35
                                           b-combo__input_width_320 
                                           <?php if(@$prj_categories[$i]['category_id'] > 0):?>drop_down_default_<?=$prj_categories[$i]['category_id']?><?php endif; ?>">

                                         <input id="project_profession<?= $i ?>"
                                                type="text" 
                                                placeholder="Выберите раздел" 
                                                value="<?=@$group_name?>" 
                                                name="profession0[group]" 
                                                class="b-combo__input-text"/>
                                         <span class="b-combo__arrow"></span>
                                      </div>
                                    </div>

                                    <?php if (is_pro()): ?>
                                    &nbsp;<a prof_combo_id="project_profession<?= $i ?>" 
                                             class="
                                                project_remove_prof 
                                                b-button 
                                                b-button_admin_del 
                                                b-button_inline-block 
                                                b-button_margtop_10 
                                                b-button_valign_top" 
                                             href="javascript:void(0)"></a>
                                    <?php endif; ?>
                                    
                                    <div id="project_profession<?= $i ?>_spec_ui" 
                                         class="b-combo 
                                                <?php if(!isset($prj_categories[$i]['subcategory_id'])):?>b-combo_hide<?php endif; ?>
                                                b-combo_margleft_20">
                                        <div class="
                                             b-combo__input 
                                             b-combo__input_multi_dropdown 
                                             cut_column_1_form_project_profession<?= $i ?>_set_parent_<?=(@$prj_categories[$i]['category_id'] > 0)?$prj_categories[$i]['category_id']:'1000'?> 
                                             b-combo__input_arrow_yes 
                                             show_all_records 
                                             sort_cnt 
                                             b-combo__input_height_35
                                             b-combo__input_width_300 
                                             <?php if(@$prj_categories[$i]['subcategory_id'] >= 0):?>drop_down_default_<?=$prj_categories[$i]['subcategory_id']?><?php endif; ?>">
                                            <input type="text" 
                                                   value="<?=@$prof_name?>"
                                                   id="project_profession<?= $i ?>_spec" 
                                                   name="profession0[spec]" 
                                                   placeholder="Выберите специализацию (не обязательно)"
                                                   class="b-combo__input-text"/>
                                            <span class="b-combo__arrow" id="city_arrow"></span>
                                        </div>
                                    </div>
                                    
                                </div>
                                
                                <? if ($i === 0) { ?>
                                <div id="project_profession_error" class="b-layout__txt b-layout__txt_padtop_3 b-layout__txt_color_c10600 b-layout_hide">
                                    <span class="b-icon b-icon_sbr_rattent"></span><span>Необходимо выбрать хотя бы одну специализацию</span>
                                </div>
                                <? } ?>
                            <? } ?>
                            <div id="project_double_profession_error" class="b-layout__txt b-layout__txt_padtop_3 b-layout__txt_color_c10600 b-layout_hide">
                                <span class="b-icon b-icon_sbr_rattent"></span><span>У вас несколько раз выбрана одна профессия</span>
                            </div>
                            <?if (is_pro()) { ?>
                            <div class="b-layout__txt b-layout__txt_inline-block <?= $prj_categories[0] && $prj_categories[1] && $prj_categories[2] ? 'b-layout_hide' : '' ?>" style="margin-top: 8px;" id="project_add_prof">
                                <a href="javascript:void(0)" class="b-button b-button_poll_plus"></a> <a class="b-layout__link b-layout__link_bordbot_dot_0f71c8" href="javascript:void(0)">Добавить раздел</a>
                            </div>
                            <? } ?>
                        </td>
                        <td class="b-layout__one b-layout__one_width_70 b-layout__one_padbot_10">
                        </td>
                    </tr>
					<?php } ?>
                    
            
                    
                    <? if ($isVacancy) { ?>
                    <tr class="b-layout__tr">
                        <td class="b-layout__one b-layout__one_padbot_20">
                            <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_5">Нужен исполнитель из&hellip;</div>
                            <div id="project_location_wrap" class="b-combo b-combo_inline-block b-combo_margright_20 b-combo_zindex_2">
                                <div class="b-combo__input b-combo__input_height_35
                                     b-combo__input_multi_dropdown 
                                     b-combo__input_resize 
                                     b-combo__input_width_210 
                                     b-combo__input_max_width_546 
                                     b-combo__input_arrow_yes 
                                     b-combo__input_init_citiesList 
                                     b-combo__input_on_click_request_id_getcities 
                                     override_value_id_0_0_Все+страны 
                                     override_value_id_1_0_Все+города">
                                    <input type="hidden" name="project_location_columns[0]" value="<?= $project['country'] ?>">
                                    <input type="hidden" name="project_location_columns[1]" value="<?= $project['city'] ?>">
                                    <input id="project_location" class="b-combo__input-text" name="location" type="text" size="80" value="<?= $project['location'] ? $project['location'] : 'Все страны' ?>" autocomplete="off">
                                    <label class="b-combo__label" for="project_location"></label>
                                    <span class="b-combo__arrow"></span>
                                </div>
                            </div>
                            <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padtop_3">
                                Если предполагается работа в офисе - укажите, в каком городе он находится.
                            </div>
                            <div id="project_location_error" class="b-layout__txt b-layout__txt_padtop_3 b-layout__txt_color_c10600 b-layout_hide">
                                <span class="b-icon b-icon_sbr_rattent"></span><span>Необходимо выбрать страну и город</span>
                            </div>
                        </td>
                        <td class="b-layout__one b-layout__one_width_70 b-layout__one_padbot_20">&#160;</td>
                    </tr>
                    <? } ?>


                    <?php if(!$isContest): ?>
                    <tr class="b-layout__tr b-project-create-budget">
                        <td class="b-layout__one b-layout__one_padbot_20" colspan="2">
                            <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_5 b-layout__txt_padtop_10">Бюджет</div>
                           <table class="b-layout__table b-layout__table_width_full">
                              <tr class="b-layout__tr">
                                 <td class="b-layout__td b-layout__td_width_340 b-layout__td_ipad">
                            <div class="b-combo b-combo_inline-block b-combo_margright_10">
                                <div class="b-combo__input b-combo__input_width_160 b-combo__input_height_35 <?= $newContestBudget && $projectIsEdit ? 'b-combo__input_disabled' : '' ?>">
                                    <input id="project_cost" class="b-combo__input-text" name="cost" type="text" size="10" maxlength="6" value="<?= $project['cost'] ? $project['cost'] : $minBudget ?>" placeholder="Укажите сумму оплаты" />
                                </div>
                            </div>
                            <script type="text/javascript">var currencyList = {0:"USD", 1:"Евро", 2:"Руб"}</script>
                            <div class="b-combo b-combo_inline-block b-combo_margright_10">
                                <div class="b-combo__input b-combo__input_width_60 b-combo__input_height_35 b-combo__input_arrow_yes b-combo__input_multi_dropdown drop_down_default_<?= (isset($project['cost']) && intval($project['cost']) !== 0) ? (int)$project['currency'] : 2 ?> b-combo__input_init_currencyList reverse_list <?= $newContestBudget && $projectIsEdit ? 'b-combo__input_disabled' : '' ?>">
                                    <input id="project_currency" class="b-combo__input-text" name="currency" type="text" size="80" readonly="readonly"/>
                                    <span class="b-combo__arrow"></span>
                                </div>
                            </div>
                              <script type="text/javascript">var pricebyList = {1:"за час", 2:"за день", 3:"за месяц", 4:"за проект"}</script>
                              <div class="b-combo b-combo_inline-block">
                                  <div class="b-combo__input b-combo__input_width_85 b-combo__input_height_35 b-combo__input_arrow_yes b-combo__input_multi_dropdown drop_down_default_<?= (isset($project['cost']) && intval($project['priceby']) !== 0) ? (int)$project['priceby'] : 4 ?> b-combo__input_init_pricebyList">
                                      <input id="project_priceby" class="b-combo__input-text" name="priceby" type="text" size="10" value="" readonly="readonly" />
                                      <span class="b-combo__arrow"></span>
                                  </div>
                              </div>
                                 </td>
                                 <td class="b-layout__td">
                                    <span class="b-txt b-txt_inline-block b-txt_padtop_10 b-txt_padright_10 b-txt_padleft_10"> или </span> 
                                    <div class="b-check b-check_inline-block b-check_padtop_12">
                                        <input id="project_agreement" class="b-check__input" name="agreement" type="checkbox" value="1" <?= isset($project['cost']) && intval($project['cost']) === 0 ? 'checked' : '' ?> />
                                        <label for="project_agreement" class="b-check__label b-check__label_fontsize_13">По договоренности</label>
                                    </div>
                                 </td>
                              </tr>
                            </table>
                            <div id="project_cost_error" class="b-layout__txt b-layout__txt_padtop_3 b-layout__txt_color_c10600 b-layout_hide">
                                <span class="b-icon b-icon_sbr_rattent"></span><span id="project_cost_error_text"></span>
                            </div>
                            
                            <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_5 b-layout__txt_padtop_20">Способ оплаты</div>
                            
                            <div id="block_payment" <?php if ($choose_bs): ?>data-choosebs="1" <?php endif; ?>class="b-radio b-radio_padtop_20 b-radio_layout_vertical b-fon b-fon_pad_10 b-fon_bg_fa">
                                <div class="b-radio__item b-radio__item_padbot_10">
                                    <input type="radio" value="1" name="prefer_sbr" class="b-radio__input" id="el-pay_type-0" <?= $project['prefer_sbr'] === 't' ? 'checked':'' ?>>
                                    <label for="el-pay_type-0" class="b-radio__label b-radio__label_fontsize_15 b-radio__label_margtop_-1">
                                        Безопасная сделка (с резервированием бюджета) &nbsp;<a target="_blank" href="/promo/bezopasnaya-sdelka/" class="b-layout__link"><span class="b-shadow__icon b-shadow__icon_quest2 b-icon_top_2"></span></a>            
                                    </label>
                                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padleft_20 b-layout__txt_color_808080">
                                        Безопасное сотрудничество с гарантией возврата средств. 
                                        Вы резервируете бюджет заказа на сайте FL.ru - а мы гарантируем 
                                        вам возврат суммы, если работа будет выполнена Исполнителем некачественно или не в срок.            
                                    </div>
                                </div>        
                                <div class="b-radio__item">
                                    <input type="radio" value="0" name="prefer_sbr" class="b-radio__input" id="el-pay_type-1" <?= $project['prefer_sbr'] === 'f' ? 'checked':'' ?>>
                                    <label for="el-pay_type-1" class="b-radio__label b-radio__label_fontsize_15 b-radio__label_margtop_-1">
                                        Прямая оплата Исполнителю на его кошелек/счет            
                                    </label>
                                    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padleft_20 b-layout__txt_color_808080">
                                        Сотрудничество без участия сайта в процессе оплаты. 
                                        Вы сами договариваетесь с Исполнителем о способе и порядке оплаты. 
                                        И самостоятельно регулируете все претензии, связанные с качеством 
                                        и сроками выполнения работы.                                       
                                    </div>
                                </div>        
                            </div>
                         
                            <div id="el-pay_type-error" class="b-layout__txt b-layout__txt_color_c10600 b-layout_hide">
                                <span class="b-icon b-icon_top_10 b-icon_sbr_rattent"></span>
                                <span class="b-layout__txt b-layout__txt_inline-block b-layout__txt_color_c10600" id="el-pay_type-error-text"></span>
                            </div>
                            
                        </td>
                    </tr>
                    <?php else: ?>
                    <tr class="b-layout__tr b-project-create-budget">
                        <td class="b-layout__one b-layout__one_padbot_20" colspan="2">
                           <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_5"><? if ($project['kind'] == 4) { ?>Бюджет<?php } else { ?>Призовой бюджет<?php } ?></div>
                           <table class="b-layout__table b-layout__table_width_full">
                              <tr class="b-layout__tr">
                                 <td class="b-layout__td b-layout__td_width_340 b-layout__td_ipad" <? if ($isContest) { ?>colspan="2"<?php } ?>>
                            <div class="b-combo b-combo_inline-block b-combo_margright_10">
                                <div class="b-combo__input b-combo__input_width_160 b-combo__input_height_35 <?= $newContestBudget && $projectIsEdit ? 'b-combo__input_disabled' : '' ?>">
                                    <input id="project_cost" class="b-combo__input-text" name="cost" type="text" size="10" maxlength="6" value="<?= $project['cost'] ? $project['cost'] : $minBudget ?>"  placeholder="Укажите сумму оплаты" />
                                </div>
                            </div>
                            <script type="text/javascript">var currencyList = {0:"USD", 1:"Евро", 2:"Руб"}</script>
                            <div class="b-combo b-combo_inline-block b-combo_margright_10 b-combo_valign_mid">
                                <div class="b-combo__input b-combo__input_width_60 b-combo__input_height_35 b-combo__input_arrow_yes b-combo__input_multi_dropdown drop_down_default_<?= (isset($project['cost']) && intval($project['cost']) !== 0) ? (int)$project['currency'] : 2 ?> b-combo__input_init_currencyList reverse_list <?= $newContestBudget && $projectIsEdit ? 'b-combo__input_disabled' : '' ?>">
                                    <input id="project_currency" class="b-combo__input-text" name="currency" type="text" size="80" readonly="readonly"/>
                                    <span class="b-combo__arrow"></span>
                                </div>
                            </div>
                            <? if (!$isContest) { ?>
                              <script type="text/javascript">var pricebyList = {1:"за час", 2:"за день", 3:"за месяц", 4:"за проект"}</script>
                              <div class="b-combo b-combo_inline-block b-combo_valign_mid">
                                  <div class="b-combo__input b-combo__input_width_85 b-combo__input_height_35 b-combo__input_arrow_yes b-combo__input_multi_dropdown drop_down_default_<?= (isset($project['cost']) && intval($project['priceby']) !== 0) ? (int)$project['priceby'] : 4 ?> b-combo__input_init_pricebyList">
                                      <input id="project_priceby" class="b-combo__input-text" name="priceby" type="text" size="10" value="" readonly="readonly" />
                                      <span class="b-combo__arrow"></span>
                                  </div>
                              </div>
                            <? } ?>
                            <? if ($isContest && !($newContestBudget && $projectIsEdit)) { ?>
                            <div class="b-layout b-layout_inline-block">
                                <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padtop_2">Минимальный бюджет &mdash; <?= $minBudget ?> руб (не включает в себя стоимость публикации конкурса на сайте).</div>
                                <? if ($newContestBudget && !$projectIsEdit) { ?>
                                <div class="b-layout__txt padtop_10" id="contest_tax_wrap" style="display:none;">Стоимость публикации конкурса <span class="b-layout__txt b-layout__txt_lineheight_1 b-layout__txt_color_fd6c30"><span id="contest_tax"></span> руб.</span></div>
                                <? } ?>
                             </div>
                            <? } ?>
                                 </td>
												 <? if (!$isContest) { ?>
                                 <td class="b-layout__td b-layout__td_ipad">
                                     <span class="b-txt b-txt_inline-block b-txt_padtop_10 b-txt_padright_10 b-txt_padleft_10"> или </span> 
                                     <div class="b-check b-check_inline-block b-check_padtop_12">
                                         <input id="project_agreement" class="b-check__input" name="agreement" type="checkbox" value="1" <?= isset($project['cost']) && intval($project['cost']) === 0 ? 'checked' : '' ?> />
                                         <label for="project_agreement" class="b-check__label b-check__label_fontsize_13">По договоренности</label>
                                     </div>
                                 </td>
                                     <? } ?>
                              </tr>
                            </table>
                            <div id="project_cost_error" class="b-layout__txt b-layout__txt_padtop_3 b-layout__txt_color_c10600 b-layout_hide">
                                <span class="b-icon b-icon_sbr_rattent"></span><span id="project_cost_error_text"></span>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                    
                    <? if ($isContest) { ?>
                    <tr class="b-layout__tr">
                        <td class="b-layout__one b-layout__one_padbot_20">
                            <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_5">Дата окончания конкурса</div>
                            <div class="b-combo">
                                <div class="b-combo__input b-combo__input_width_150 b-combo__input_height_35 b-combo__input_arrow-date_yes b-combo__input_calendar b-combo__input_resize b-combo__input_arrow_yes date_format_use_dot use_past_date <?=($project['end_date'] == '' ? 'no_set_date_on_load' : '')?> year_min_limit_1900">
                                    <input id="project_end_date" class="b-combo__input-text" name="end_date" type="text" size="10" value="<?= str_replace('-', '.', $project['end_date']) ?>" />
                                    <span class="b-combo__arrow-date"></span> 
                                </div>
                            </div>
                            <div id="project_end_date_error" class="b-layout__txt b-layout__txt_padtop_3 b-layout__txt_color_c10600 b-layout_hide">
                                <span class="b-icon b-icon_sbr_rattent"></span><span class="project-error-text"></span>
                            </div>
                        </td>
                        <td class="b-layout__one b-layout__one_width_70 b-layout__one_padbot_20">&#160;</td>
                    </tr>
                    <tr class="b-layout__tr">
                        <td class="b-layout__one b-layout__one_padbot_20">
                            <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_5">Дата награждения победителей</div>
                            <div class="b-combo">
                                <div class="b-combo__input b-combo__input_width_150 b-combo__input_height_35 b-combo__input_arrow-date_yes b-combo__input_calendar b-combo__input_resize b-combo__input_arrow_yes date_format_use_dot use_past_date no_set_date_on_load year_min_limit_1900">
                                    <input id="project_win_date" class="b-combo__input-text" name="win_date" type="text" size="10" value="<?= str_replace('-', '.', $project['win_date']) ?>" />
                                    <span class="b-combo__arrow-date"></span> 
                                </div>
                            </div>
                            <div id="project_win_date_error" class="b-layout__txt b-layout__txt_padtop_3 b-layout__txt_color_c10600 b-layout_hide">
                                <span class="b-icon b-icon_sbr_rattent"></span><span class="project-error-text"></span>
                            </div>
                        </td>
                        <td class="b-layout__one b-layout__one_width_70 b-layout__one_padbot_20">&#160;</td>
                    </tr>
                    <? } ?>
            
                    
                    <?php if(!$isContest && !$isVacancy): ?>
                    
                        <?php if(false): ?>
                    
					<tr class="b-layout__tr b-project-create-filter">
                        
                        <td class="b-layout__one b-layout__one_padtop_2 b-layout__one_padbot_20">
                            <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_5">Ответить на <? if ($project['kind'] == 4) { ?>вакансию<?php } else if($project['kind'] == 7) { ?>конкурс<?php } else { ?>проект<?php } ?> могут только&hellip;</div>
                            <div class="b-fon b-fon_pad_10 b-fon_bg_fa">
                                <div class="b-check">
                                    <input id="project_pro_only" class="b-check__input" name="pro_only" type="checkbox" value="1" <?= $project['pro_only'] === 't' ? 'checked' : ($project['pro_only'] === 'f' ? '' : 'checked') ?> />
                                    <label for="project_pro_only" class="b-check__label b-check__label_fontsize_13">Фрилансеры с аккаунтом <a href="/profi/" target="_blank" class="b-layout__link"><span class="b-icon b-icon__lprofi b-icon_top_2" data-profi-txt="Лучшие фрилансеры сайта FL.ru. Работают на сайте более 2-х лет, прошли верификацию личности и имеют не менее 98% положительных отзывов."></span></a> или  <span title="Пользователи с платным аккаунтом" class="b-icon b-icon__pro b-icon__pro_f b-icon_top_3"></span></label>
                                </div>
                                <?php if(false): ?>
                                <div class="b-check b-check_padtop_10">
                                    <input id="project_ver_only" class="b-check__input" name="verify_only" type="checkbox" value="1" <?= ($project['verify_only'] === 't') ? 'checked' : '' ?> />
                                    <label for="project_ver_only" class="b-check__label b-check__label_fontsize_13">Фрилансеры с верификацией <?= view_verify('Верификация - процесс подтверждения личности с привязкой аккаунта к одной из платежных систем.') ?></label>
                                </div>
                                <?php endif; ?>
                             </div>
                            
                        </td>
                        <td class="b-layout__one b-layout__one_width_70 b-layout__one_padbot_20">&nbsp;</td>
                    </tr>
                    
                        <?php endif; ?>
                    
                    <?php else: ?>
                    
                    <?php if (!$is_personal) { ?>
					<tr class="b-layout__tr b-project-create-filter">
                        <td class="b-layout__one b-layout__one_padbot_20">
                            <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_5">Ответить на <? if ($project['kind'] == 4) { ?>вакансию<?php } else if($project['kind'] == 7) { ?>конкурс<?php } else { ?>проект<?php } ?> могут только&hellip;</div>
                            <div class="b-fon b-fon_pad_10 b-fon_bg_fa">
                                
                                
                                
                            <div class="b-check">
                                <input id="project_pro_only" class="b-check__input" name="pro_only" type="checkbox" value="1" <?= $project['pro_only'] === 't' ? 'checked' : ($project['pro_only'] === 'f' ? '' : 'checked') ?> />
                                <label for="project_pro_only" class="b-check__label b-check__label_fontsize_13">Фрилансеры с аккаунтом <a href="/profi/" target="_blank" class="b-layout__link"><span class="b-icon b-icon__lprofi b-icon_top_2" data-profi-txt="Лучшие фрилансеры сайта FL.ru. Работают на сайте более 2-х лет, прошли верификацию личности и имеют не менее 98% положительных отзывов."></span></a> или  <span title="Пользователи с платным аккаунтом" class="b-icon b-icon__pro b-icon__pro_f b-icon_top_3"></span></label>
                            </div>
                            <?php if(false): ?>
                                <? if (strtotime('2013-07-09 00:00:00') > time()) { ?>
                                    <div class="b-check b-check_padtop_10">
                                        <input type="hidden" name="verify_only" value="0" />
                                        <input id="project_ver_only" class="b-check__input" type="checkbox" disabled="disabled" />
                                        <label for="project_ver_only" class="b-check__label b-check__label_fontsize_13">Только для <?= view_verify('верифицированных пользователей') ?><br> (будет доступно в июле)</label>
                                    </div>
                                <? } else { ?>
                                    <div class="b-check b-check_padtop_10">
                                        <input id="project_ver_only" class="b-check__input" name="verify_only" type="checkbox" value="1" <?= ($project['verify_only'] === 't') ? 'checked' : '' ?> />
                                        <label for="project_ver_only" class="b-check__label b-check__label_fontsize_13">Фрилансеры с верификацией <?= view_verify('Верификация - процесс подтверждения личности с привязкой аккаунта к одной из платежных систем.') ?></label>
                                    </div>
                                <? } ?>
                            <?php endif; ?>
                                
                            <? if (!$isVacancy) { ?>
                            <div class="b-check b-check_padtop_10" id="project_prefer_sbr_wrap" <?= $isContest || $project['cost'] == 0 || $project['cost'] >= sbr_stages::MIN_COST_RUR ? '' : 'style="display:none;"' ?>>
                                <input id="project_prefer_sbr" name="prefer_sbr" class="b-check__input" type="checkbox" value="1" <?= $project['prefer_sbr'] === 't' ? 'checked' : ($project['prefer_sbr'] === 'f' ? '' : 'checked') ?> />
                                <label for="project_prefer_sbr" class="b-check__label b-check__label_fontsize_13"><?= $project['kind'] != 7 ? 'Предпочитаю оплату работы через' : 'Выплата вознаграждения через' ?> <a href="/promo/bezopasnaya-sdelka/" target="_blank" class="b-layout__link">Безопасную Сделку</a> <?= view_sbr_shield('', 'b-icon_top_2') ?></label>
                            </div>
                            <? } ?>
                            </div>
                        </td>
                        <td class="b-layout__one b-layout__one_width_70 b-layout__one_padbot_20">&nbsp;</td>
                    </tr>
					<?php } ?>

                    <?php endif; ?>
                    
                    
                    
                    <? if(!$is_personal && hasPermissions('projects')) { ?>
                    <tr class="b-layout__tr">
                        <td class="b-layout__one b-layout__one_padbot_20">
                            <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_10">Опции для администраторов</div>
                            <div class="b-combo">
                                <div class="b-combo__input b-combo__input_height_35">
                                    <input class="b-combo__input-text" name="videolnk" id="project_videolnk" type="text" size="80" maxlength="60" value="<?= str_replace('"', "&quot;", $project['videolnk']) ?>" placeholder="Добавьте ссылку на видео" />
                                </div>
                            </div>
                            <div id="project_videolnk_error" class="b-layout__txt b-layout__txt_padtop_3 b-layout__txt_color_c10600 b-layout_hide">
                                <span class="b-icon b-icon_sbr_rattent"></span><span id="project_videolnk_error_text"></span>
                            </div>
                            <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padtop_2"></div>
                            
                            <? if(hasPermissions('projects')) { ?>
                                <div class="b-check b-check_padtop_10 b-check_padleft_10">
                                    <input id="project_strong_top" name="strong_top" class="b-check__input" type="checkbox" value="1" />
                                    <label for="project_strong_top" class="b-check__label b-check__label_fontsize_13">Закрепить железно наверху ленты</label>
                                </div>
                            <? }//if?>
                            
                            
                        </td>
                        <td class="b-layout__one b-layout__one_width_70 b-layout__one_padbot_20">
                           
                        </td>
                    </tr>
                    <? } ?>
                    
                  </table>
                
				<?php if ( !$is_personal && $projectIsEdit ) { ?>
				<? $projectOnTop = time() < strtotime($project['top_to']); ?>
                <?php if ($step < 2): ?>
                <h2 id="pay_services" class="b-layout__title<?php if ($scrollToPay): ?> autoscroll<?php endif; ?> b-layout__txt_color_6db335 b-layout__txt_padtop_15 b-layout__title_padbot_20">
                    Выделите <? if ($project['kind'] == 4) { ?>вакансию<?php } else if($project['kind'] == 7) { ?>конкурс<?php } else { ?>проект<?php } ?> и привлеките лучших Исполнителей
                </h2>
                <?php endif; ?>
                 <label for="project_top_ok"></label>
                   <div class="b-check b-fon b-fon_bg_f0ffdf b-fon_pad_20_10 b-fon_bordbot_fff">
                     <table class="b-layout__table b-layout__table_width_full">
                        <tr class="b-layout__tr">
                           <td class="b-layout__td b-layout__td_valign_mid b-layout__td_center b-layout__td_width_30">
                             <input id="project_top_ok" type="checkbox" class="b-check__input" name="top_ok" value="1" <?= $project['top_days'] > 0 ? 'checked' : '' ?>>
                           </td>
                           <td class="b-layout__td b-layout__td_valign_mid b-layout__td_center b-layout__td_width_60 b-layout__td_padright_10">
                               <span class="b-icon b-icon__knpk b-icon_center"></span>
                           </td>
                           <td class="b-layout__td b-layout__td_valign_mid">
                             <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_bold"><? if($projectOnTop) { ?>Продлить закрепление<?php } else { ?>Закрепите <? if ($project['kind'] == 4) { ?>вакансию<?php } else if($project['kind'] == 7) { ?>конкурс<?php } else { ?>проект<?php } ?> в ленте<?php } ?></div>
                             <div id="project_top_ok_s_info" class="b-layout__txt b-layout__txt_fontsize_11">После закрепления <? if ($project['kind'] == 4) { ?>ваша вакансия<?php } else if($project['kind'] == 7) { ?>ваш конкурс<?php } else { ?>ваш проект<?php } ?> окажется на самом верху ленты проектов.<br><? if ($project['kind'] == 4) { ?>ее<?php } else { ?>его<?php } ?> увидит большее число потенциальных исполнителей.</div>
                             <div id="project_top_ok_s_days" class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padbot_5 <?= $project['top_days']>0 ? '' : 'b-layout_hide' ?>"><?= $projectOnTop ? 'Продлить ' : '' ?>на
                                <div class="b-combo b-combo_valign_mid b-combo_inline-block">
                                    <div class="b-combo__input b-combo__input_width_50">
                                        <input id="project_top_days" type="text" name="top_days" maxlength="2" value="<?= $project['top_days'] ? $project['top_days'] : 1 ?>" size="80"  class="b-combo__input-text b-combo__input-text_center">
                                    </div>
                                </div> 
                                <span id="project_top_days_text">дня</span>
         
                                <? if ($projectOnTop) { ?>
                                <br/><br/>Проект уже закреплен до <?= date('d/m/Y', strtotime($project['top_to'])) ?>
                                <? } ?>                           
                            </div>
                           </td>
                           <td class="b-layout__td b-layout__td_valign_mid b-layout__td_width_120">
                              <div class="b-layout__txt b-layout__txt_bold b-layout__txt_color_ff7f27 b-layout__txt_lineheight_1"><span id="project_top_days_price_2"><?= $priceTopDay ?> руб.<br>в день</span><span id="project_top_days_price" style="display: none;"><?= $priceTopDay ?></span></div>
                           </td>
                        </tr>
                     </table>
                   </div>
                 <label for="project_logo_ok"></label>
                   <div class="b-check b-fon b-fon_bg_f0ffdf b-fon_pad_20_10 b-fon_bordbot_fff">
                     <table class="b-layout__table b-layout__table_width_full">
                         <tr class="b-layout__tr">
                                 <td class="b-layout__td b-layout__td_valign_mid b-layout__td_center b-layout__td_width_30">
                                     <input id="project_logo_ok" class="b-check__input" type="checkbox" name="logo_ok" value="1" <?= ($project['logo_id'] ? 'checked="checked"' : '') ?>/>
                                     <input id="project_logo_del" type="hidden" name="logo_del" value="0" />
                                 </td>
                                 <td class="b-layout__td b-layout__td_valign_mid b-layout__td_center b-layout__td_width_60 b-layout__td_padright_10">
                                     <span id="project_logo_1" class="b-icon b-icon__plg" style="display: <?= !$logoFile ? '' : 'none' ?>;"></span>

                                     <div id="project_logo_img_wrap" style="display: <?= $logoFile ? '' : 'none' ?>;">
                                         <div class="i-pic i-pic_inline-block i-pic_50 i-pic_solid_e6">
                                             <img id="project_logo_img" class="b-pic" src="<?= $logoFile ? (WDCPREFIX . '/' . $logoFile->path . $logoFile->name) : '' ?>" width="50" height="50">
                                         </div>
                                     </div>                            
                                 </td>
                                 <td class="b-layout__td b-layout__td_valign_mid">
                                     <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_bold">Добавьте логотип компании и ссылку на нее</div>
                                     <div id="project_logo_2" class="b-layout__txt b-layout__txt_fontsize_11" style="display: <?= $logoFile ? 'none' : 'block' ?>"><? if ($project['kind'] == 4) { ?>Вакансия<?php } else if($project['kind'] == 7) { ?>Конкурс<?php } else { ?>Проект<?php } ?> с логотипом выделяется в ленте проектов,<br>привлекает больше внимания исполнителей, и на <? if ($project['kind'] == 4) { ?>нее<?php } else { ?>него<?php } ?> отвечают чаще.</div>
                                     <div id="project-logo-btn-del" class="b-layout__txt b-layout__txt_inline-block b-layout__txt_padtop_3 b-layout_float_left" style="display:<?= !$logoFile ? 'none' : 'block' ?>">
                                         <a id="project_logo_img_del" class="b-layout__link b-layout__link_bordbot_dot_c7271e" href="#">Удалить логотип</a>&nbsp;&nbsp;
                                     </div> 
                                     <div id="project_logo" class="b-layout_inline-block" style="display: none;">
                                         <div id="project_logo_file" class="b-file project_logo_file_template">
                                             <input name="attachedfiles_type" onchange="('p_attachedfiles_type').set('value', this.get('value'));" type="hidden" value="project_logo" />
                                             <input name="attachedfiles_session[]" onchange="('p_attachedfiles_session').set('value', this.get('value'));" type="hidden" />
                                             <input name="logo_attachedfiles_session" onchange="('p_logo_attachedfiles_session').set('value', this.get('value'));" type="hidden" value="<?= $attachLogo->session[0] ?>" />

                                             <input class="attachedfiles_error" type="hidden" />
                                             <input class="attachedfiles_uploadingfile" type="hidden" />
                                             <input class="attachedfiles_errortxt" type="hidden" />
                                             <div class="attachedfiles_template" style="display:none;visibility:hidden;">
                                                 <div class="project_logo_file_icon" style="display:none"></div>
                                                 <div class="project_logo_file_name" style="display:none"></div>
                                                 <div class="project_logo_file_del" style="display:none"></div>
                                             </div>

                                             <div id="project-logo-btn" class="b-file__wrap b-layout_inline-block">
                                                 <input id="project_logo_input" type="file" class="b-file__input" name="attachedfiles_file">
                                                 <a id="project_logo_file_btn" class="b-button b-button_flat b-button_flat_grey b-button_block attachedfiles_button" href="javascript:void(0);">Загрузить файл логотипа</a>
                                             </div>
                                             
                                             <div id="project_logo_error" class="i-shadow">
                                                 <div class="b-shadow b-shadow_m b-shadow_top_0 b-shadow_pad_10 b-shadow_hide">
                                                     <div class="b-layout__txt b-layout__txt_nowrap b-layout__txt_padright_15 b-layout__txt_color_c4271f">
                                                         <span class="b-icon b-icon_sbr_rattent"></span><span id="project_logo_error_txt">Обязательно прикрепите файл</span>
                                                     </div>
                                                     <span class="b-shadow__icon b-shadow__icon_close b-shadow__icon_right_12 b-shadow__icon_top_12" onClick="$('project_logo_error').getChildren().addClass('b-shadow_hide');"></span>
                                                     <span class="b-shadow__icon b-shadow__icon_nosik b-shadow__icon_left_100"></span>
                                                 </div>
                                             </div>                                                

                                         </div>
                                     </div>
                                     <div id="project_logo_link_block" class="b-combo b-combo_inline-block b-combo_padbot_5"<?= !$logoFile ? 'style="display:none"' : '' ?>>
                                         <div class="b-combo__input b-combo__input_width_180">
                                             <input id="project_logo_link" type="text" size="80" name="link" class="b-combo__input-text b-combo__input-text_color_67" value="<?= $project['link'] ? $project['link'] : '' ?>" placeholder="Адрес сайта по желанию">
                                         </div>
                                     </div> 
                                 </td>
                                 <td class="b-layout__td b-layout__td_valign_mid b-layout__td_width_120">
                                     <div class="b-layout__txt b-layout__txt_bold b-layout__txt_color_ff7f27 b-layout__txt_lineheight_1"><?= $priceLogo ?> руб.</div>
                                 </td>
                             </tr>
                     </table>
                   </div>
                 <label for="project_urgent"></label>
                   <div class="b-check b-fon b-fon_bg_f0ffdf b-fon_pad_20_10 b-fon_bordbot_fff">
                     <table class="b-layout__table b-layout__table_width_full">
                        <tr class="b-layout__tr">
                           <td class="b-layout__td b-layout__td_valign_mid b-layout__td_center b-layout__td_width_30">
                               <input type="hidden" id="hidden_project_urgent" value="<?=($projectIsEdit ? ($project['urgent']=='t' ? '1' : '0') : '0')?>">
                               <input id="project_urgent" class="b-check__input" type="checkbox" value="1" name="urgent" <?= $project['urgent']=='t' ? 'checked' : '' ?> >
                           </td>
                           <td class="b-layout__td b-layout__td_valign_mid b-layout__td_center b-layout__td_width_60 b-layout__td_padright_10">
                               <span class="b-icon b-icon__bfire"></span>
                           </td>
                           <td class="b-layout__td b-layout__td_valign_mid">
                               <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_bold">Выделите меткой &laquo;Срочно&raquo;</div>
                               <div class="b-layout__txt b-layout__txt_fontsize_11"><? if ($project['kind'] == 4) { ?>Вакансия будет отмечена в качестве срочной и выделена в ленте красным значком.<br>Это стимулирует фрилансеров отвечать быстрее именно на вашу вакансию.<?php } else if($project['kind'] == 7) { ?>Конкурс будет отмечен в качестве срочного и выделен в ленте красным значком.<br>Это стимулирует фрилансеров отвечать быстрее именно на ваш конкурс.<?php } else { ?>Проект будет отмечен в качестве срочного и выделен в ленте красным значком.<br>Это стимулирует фрилансеров отвечать быстрее именно на ваш проект.<?php } ?></div>
                           </td>
                           <td class="b-layout__td b-layout__td_valign_mid b-layout__td_width_120">
                               <div class="b-layout__txt b-layout__txt_bold b-layout__txt_color_ff7f27 b-layout__txt_lineheight_1">300 руб.</div>
                           </td>
                        </tr>
                     </table>
                 </div>
                 <label for="project_hide"></label>
                   <div class="b-check b-fon b-fon_bg_f0ffdf b-fon_pad_20_10">
                     <table class="b-layout__table b-layout__table_width_full">
                        <tr class="b-layout__tr">
                           <td class="b-layout__td b-layout__td_valign_mid b-layout__td_center b-layout__td_width_30">
                               <input type="hidden" id="hidden_project_hide" value="<?=($projectIsEdit ? ($project['hide']=='t' ? '1' : '0') : '0')?>">
                               <input id="project_hide" class="b-check__input" type="checkbox" value="1" name="hide" <?= $project['hide']=='t' ? 'checked' : '' ?> >
                           </td>
                           <td class="b-layout__td b-layout__td_valign_mid b-layout__td_center b-layout__td_width_60 b-layout__td_padright_10">
                               <span class="b-icon b-icon__beye"></span>
                           </td>
                           <td class="b-layout__td b-layout__td_valign_mid">
                               <div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_bold">Скройте <? if ($project['kind'] == 4) { ?>вакансию<?php } else if($project['kind'] == 7) { ?>конкурс<?php } else { ?>проект<?php } ?> от поисковых систем</div>
                               <div class="b-layout__txt b-layout__txt_fontsize_11"><? if ($project['kind'] == 4) { ?>Текст скрытой вакансии не индексируется поисковыми системами. <br>И виден он только авторизованным пользователям сайта FL.ru.<?php } else if($project['kind'] == 7) { ?>Текст скрытого конкурса не индексируется поисковыми системами.<br>И виден он только авторизованным пользователям сайта FL.ru.<?php } else { ?>Текст скрытого проекта не индексируется поисковыми системами.<br>И виден он только авторизованным пользователям сайта FL.ru.<?php } ?></div>
                           </td>
                           <td class="b-layout__td b-layout__td_valign_mid b-layout__td_width_120">
                               <div class="b-layout__txt b-layout__txt_bold b-layout__txt_color_ff7f27 b-layout__txt_lineheight_1">300 руб.</div>
                           </td>
                        </tr>
                     </table>
                 </div>
                 
				<?php } ?>
             </form>
             
           <div class="b-layout__txt_fontsize_18 b-layout__txt_bold b-page__ipad b-page__iphone">Рекомендуем услуги</div>
        </td>
     </tr>
 </table>   
            
<?php if ($is_personal) { ?>

	<div class="b-buttons b-buttons_padleft_90">
		<a href="javascript:void(0)" class="b-button b-button_flat b-button_flat_green" id="project_save_btn">Предложить проект</a>
	</div>

<?php } else { ?>
            
	<div id="project_able_public" class="b-buttons b-buttons_padtop_30" <?=$hideSaveBtn ? 'style="display:none"' : ''?>>
	  <table class="b-layout__table">
		<tr class="b-layout__tr">
		  <td class="b-layout__one b-layout__one_padright_10">
			<a id="project_save_btn" class="b-button b-button_flat b-button_flat_green" href="javascript:void(0)">
                <span id="project_save_btn_text"></span> 
                <span id="project_save_btn_sum"></span>
            </a>
            <?php if ($step > 1): ?> 
            &#160;
            <a class="b-layout__link b-layout__link_fontsize_13" href="/projects/<?=$project['id']?>/">
                Перейти к <?php if($isVacancy):?>вакансии<? elseif ($isContest):?>конкурсу<?php else: ?>проекту<?php endif; ?>
            </a>
            <?php endif;  ?>
		  </td>
		  <td class="b-layout__one b-layout__one_padtop_9">
			<span id="project_need_money" style="display:none"><span class="b-buttons__txt b-buttons__txt_color_ee1d16" id="project_need_money_text"></span>&#160;&#160;<a class="b-buttons__link" href="/bill/" target="_blank" id="top-payed-bill" style="display:hide">пополнить счёт</a></span>
			<a id="project_save_to_draft_btn" class="b-buttons__link" href="javascript:void(0)"></a>&#160;&#160;&#160;&#160;  
			<span id="draft_time_save" class="b-buttons__txt time-save" style="float:none; display: none;"></span>
		  </td>
		</tr>
	  </table>
	</div>
   <?php if($project['kind'] == 7) { ?><div class="b-layout__txt b-layout__txt_padtop_20 b-layout__txt_fontsize_11 b-layout__txt_color_80">Обратите внимание: при публикации вы оплачиваете только стоимость размещения конкурса на сайте.<br>Призовые суммы выплачиваются вами отдельно после определения победителей.</div><?php } ?>

<?php } ?>

<? require_once($_SERVER['DOCUMENT_ROOT'] . "/templates/quick_buy_prj.php"); ?>
