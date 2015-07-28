<script type="text/javascript">var openedProjects=new Array();var isPrjCssOpened=<?=($isPrjOpened? 'true' : 'false')?>;</script>

<?php 
$can_change_prj = hasPermissions("projects");

if($can_change_prj) {
	$quickEditPoputType = 1;
    require_once($_SERVER['DOCUMENT_ROOT'].'/projects/tpl.prj-quickedit.php');
?>

<div id="popup_budget" class="b-shadow b-shadow_inline-block b-shadow_width_335 b-shadow_center b-shadow_zindex_3 b-shadow_hide">
	<div class="b-shadow__right">
		<div class="b-shadow__left">
			<div class="b-shadow__top">
				<div class="b-shadow__bottom">
					<div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_20">
						<h2 class="b-shadow__title b-shadow__title_padbot_15">Редактирование бюджета</h2>
						<div id="popup_budget_prj_name" class="b-layout__txt b-layout__txt_padbot_15"></div>
                        
						<div class="b-form b-form_padbot_20">
							<div class="b-combo b-combo_inline-block b-combo_margright_10">
								<div class="b-combo__input b-combo__input_width_60">
									<input id="popup_budget_prj_price" class="b-combo__input-text b-combo__input-text_fontsize_15" name="cost" type="text" size="80" maxlength="6" value="" />
								</div>
							</div><div
                             class="b-combo b-combo_inline-block b-combo_margright_10" >
								<div class="b-combo__input b-combo__input_multi_dropdown drop_down_default_2 b-combo__input_init_projQuickEditCurrency b-combo__input_width_60 b-combo__input_min-width_40 b-combo__input_arrow_yes reverse_list">
                                    <input id="popup_budget_prj_currency" class="b-combo__input-text b-combo__input-text_fontsize_15" type="text" size="80" readonly="readonly" />	
                                    <span class="b-combo__arrow"></span>
                                </div>
                            </div><div
                             class="b-combo b-combo_inline-block " >
								<div class="b-combo__input b-combo__input_multi_dropdown drop_down_default_1 b-combo__input_init_projQuickEditCostby b-combo__input_width_100 b-combo__input_arrow_yes">
                                    <input id="popup_budget_prj_costby" class="b-combo__input-text b-combo__input-text_fontsize_15" type="text" size="80" />		
                                    <span class="b-combo__arrow"></span>
                                </div>
                            </div>
						</div>
                        
                        <div class="b-check b-check_padbot_10 b-check_clear_both">
                            <input id="popup_budget_prj_agreement" class="b-check__input" name="agreement" type="checkbox" value="1" />
                            <label class="b-check__label b-check__label_fontsize_13" for="popup_budget_prj_agreement">по договорённости</label>
                        </div>
                        
                        <div id="popup_budget_prj_price_error" class="b-fon b-fon_bg_ff6d2d b-fon_padtop_10" style="display: none; ">
                            <b class="b-fon__b1"></b>
                            <b class="b-fon__b2"></b>
                            <div class="b-fon__body b-fon__body_pad_5_10 b-fon__body_fontsize_13 ">
                                <span class="b-fon__attent"></span><div class="b-fon__txt b-fon__txt_margleft_20">Бюджет заполнен не верно</div>
                            </div>
                            <b class="b-fon__b2"></b>
                            <b class="b-fon__b1"></b>
                        </div>
                        
						<div class="b-buttons b-buttons_padtop_15">
							<a id="popupBtnSaveBudget" href="javascript:void(0)" class="b-button b-button_flat b-button_flat_green">Сохранить</a>							
                            <span class="b-buttons__txt">&nbsp;или&nbsp;</span>
							<a class="b-buttons__link b-buttons__link_dot_c10601 b-shadow__close" href="javascript:void(0)">закрыть без изменений</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php } //if ?>


<?php foreach ($list as $row) { ?>
	<?php $project = projects::initData($row); ?>
	<? if ($row['t_is_ontop'] && !$row['strong_top'] && isset($_COOKIE['hidetopprjlenta']) && $_COOKIE['hidetopprjlenta']==1 && $_COOKIE['hidetopprjlenta_time']<strtotime($row['create_date'])) { $hidetopprjlenta_more = 1; } ?>
	<div class="b-post <?= $row['is_color'] == 't' ? 'b-post_bg_fffded' : '' ?> b-post_padbot_15 b-post_margbot_20 b-post_bordbot_eee b-post_relative <?=($row['t_is_ontop'] && !$row['strong_top']) ? 'topprjpay' : '' ?>" id="project-item<?= $row['id'] ?>" <?=($row['t_is_ontop'] && !$row['strong_top'] && isset($_COOKIE['hidetopprjlenta']) && $_COOKIE['hidetopprjlenta']==1 && $_COOKIE['hidetopprjlenta_time']>strtotime($row['create_date'])) ? 'style="display: none;"' : '' ?>>
	<?php require($_SERVER['DOCUMENT_ROOT'].'/projects/tpl.lenta-item.php'); ?>
	</div>
<?php } ?>

<? if (empty($list)) { ?>
    <div class="b-post b-post_padtop_60">
        <h4 class="b-post__h4 b-post__h4_padbot_5 b-post__h4_center"><?= $kind == 2 || $kind == 7 ? 'Конкурсов' : ($kind == 4 ? 'Вакансий' : 'Проектов') ?> не найдено</h4>
        <div class="b-post__txt b-post__txt_center">Попробуйте изменить параметры фильтра</div>
    </div>
<? } ?>

<?php if(!$this->hide_rss): ?>
<div class="b-rss b-rss_padbot_15">
    <script type="text/javascript">
        var RSS_LINK = '/rss/<?= $rss_link ?>';
        document.write('<a class="b-rss__link b-rss__link_dot_0f71c8" href="javascript:void(0)" onClick="showRSS(); return false;">Подписаться через RSS</a>');
    </script>
		
		
		
<div class="i-shadow">		
<div id="rsso" class="b-shadow b-shadow_inline-block b-shadow_width_380 b-shadow_zindex_3 b-shadow_hide" >
					<div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_20">
						<div class="b-layout__txt b-layout__txt_padbot_15">Выберите раздел, на который хотите подписаться</div>
						<div class="b-combo b-combo_margbot_20">
							<div class="b-combo__input b-combo__input_width_280  b-combo__input_multi_dropdown b-combo__input_arrow_yes b-combo__input_init_professionsList sort_cnt drop_down_default_-1 multi_drop_down_default_column_0 override_value_id_1_0_Веcь+раздел exclude_value_0_0">
								<input id="popup_profgroup" class="b-combo__input-text b-combo__input-text_fontsize_15" type="text"  size="80" />
							</div>
						</div>
						<div class="b-buttons b-buttons_padtop_15">
							<a class="b-button b-button_flat b-button_flat_green" href="javascript:void(0)" onclick="gotoRSS(); document.getElementById('rsso').style.display='none'; return false;">Подписаться</a>							
							<span class="b-buttons__txt b-buttons__txt_padleft_10">или</span>
							<a class="b-buttons__link b-buttons__link_dot_c10601 b-shadow__close" href="javascript:void(0)" onclick="$(this).getParent('div.overlay').setStyle('display', 'none'); return false;">закрыть</a>
						</div>
					</div>
</div>
</div>


</div>
<?php endif; ?>
<?php if(!$this->hide_paginator): ?>
    <?= new_paginator2($this->page, $this->pages, array(3, $this->filter ? 1 : 3), "%s?".urldecode(url('page'.($kind==2 || $kind==8 ? '' : ',kind'), array('page' => '%d', 'kind' => $kind)))."%s", true) ?> 
<?php endif; ?>