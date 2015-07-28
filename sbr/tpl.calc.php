<?php

$crumbs = 
array(
    0 => array(
        'href' => '/' . sbr::NEW_TEMPLATE_SBR . '/', 
        'name' => '«Мои Сделки»'
    ),
    1 => array(
        'href' => '', 
        'name' => 'Калькулятор «Безопасной Сделки»'
    )
);


if(isset($_GET['hash']) && ($rq = sbr::getSbrCalc($_GET['hash'])) !== false) {
    switch($rq['currency']) {
        case exrates::BANK:
            $currency = "на банковский счет";
            break;
        case exrates::YM:
            $currency = "на Яндекс.Деньги";
            break;
        case exrates::WMR:
            $currency = "на WebMoney";
            break;
        case exrates::FM:
            $currency = "на Счет сайта (в FM)";    
            break;
        case exrates::WEBM:
            $currency = "на Веб-кошелек";   
            break;
    }
    
    $setting = array("usr_type"    => array('type' => $rq['usr_type'],
                                            'text' => $rq['usr_type']==sbr::FRL+1 ? 'исполнитель' : 'заказчик' 
                                   ),
                     "frl_type"    => array('type' => $rq['frl_type'] , 
                                            'text' => $rq['frl_type']==sbr::FT_PHYS?'физическим лицом':'юридическим лицом'
                                   ),
                     "rez_type"    => array('type' => $rq['rez_type'],
                                            'text' => $rq['rez_type']==sbr::RT_RU?'резидентом РФ':'нерезидентом Российской Федерации'
                                   ), 
                     "scheme_type" => array('type' => $rq['scheme_type'],
                                            'text' => $rq['scheme_type']==sbr::SCHEME_LC?'договор с аккредитивной формой расчетов':'договор подряда' 
                                   ),   
                     "currency"    => array('type' => $rq['currency'],
                                            'text' => $currency
                                   ),
                     "calc_role"   => array('type' => 1,
                                            'text' => $rq['usr_type'] == sbr::FRL+1 ? 'с работодателем' : 'с фрилансером'    
                                   )              
                                                           
                    );            
    ?>
    <script type="text/javascript">
    window.addEvent('domready', function() {
        $('bank_scheme').addEvent('click', function() {
            setValueInput('currency', <?= exrates::BANK ?>);        
        });
        sbr_calc($('calcForm'), 'recalc');
        checkRole(<?=($setting['usr_type']['type'])?>); 
    });
    </script>    
<?} else {
    
    if(is_emp()) {
        $_frl_type = array('type' => sbr::FT_PHYS, 'text' => 'физическим лицом');
        $_rez_type = array('type' => sbr::RT_RU, 'text' => 'резидентом РФ');
    } else {
        $_frl_type = array('type' => $sbr->user_reqvs['form_type'] == sbr::FT_JURI ? sbr::FT_JURI : sbr::FT_PHYS, 
                           'text' => $sbr->user_reqvs['form_type'] == sbr::FT_JURI ? 'юридическим лицом':'физическим лицом');
        $_rez_type = array('type' => $sbr->user_reqvs['rez_type'] == sbr::RT_UABYKZ ? $sbr->user_reqvs['rez_type'] : sbr::RT_RU, 
                           'text' => $sbr->user_reqvs['rez_type'] == sbr::RT_UABYKZ ?'нерезидентом Российской Федерации' : 'резидентом РФ');
    }
    
    $setting = array("usr_type"    => array('type' => (is_emp() ? (sbr::EMP + 1) :  (sbr::FRL + 1)),
                                            'text' => is_emp() ? 'заказчик' : 'исполнитель' 
                                   ),
                     "frl_type"    => array('type' => $_frl_type['type'], 
                                            'text' => $_frl_type['text']
                                   ),
                     "rez_type"    => array('type' => $_rez_type['type'],
                                            'text' => $_rez_type['text'],
                                   ), 
                     "scheme_type" => array('type' => sbr::SCHEME_LC,
                                            'text' => 'договор с аккредитивной формой расчетов' 
                                   ),   
                     "currency"    => array('type' => exrates::BANK,
                                            'text' => 'на банковский счет'
                                   ),
                     "calc_role"   => array('type' => 1,
                                            'text' => 'с работодателем'    
                                   )                         
                    );
    ?>
    <script type="text/javascript">
    window.addEvent('domready', function() {
        $('bank_scheme').addEvent('click', function() {
            setValueInput('currency', <?= exrates::BANK ?>);        
        });
        checkRole(<?=($setting['usr_type']['type'])?>);
    });
    </script>  
<?}//if?>
<?= $sbr->isAdmin()? '<div class="norisk-admin c"><div class="norisk-in">' : ''?>   

<form action="" id="calcForm" class="overlay-cls">
<input type="hidden" name="usr_type" id="usr_type" value="<?= ( isset($rq['usr_type'])?$rq['usr_type']: $setting['usr_type']['type'] ) ?>" />
<input type="hidden" name="frl_type" id="frl_type" value="<?= isset($rq['frl_type'])?$rq['frl_type']: $setting['frl_type']['type'] ?>" />
<input type="hidden" name="residency" id="residency" value="<?= isset($rq['rez_type'])?$rq['rez_type']: $setting['rez_type']['type'] ?>" />
<input type="hidden" name="scheme_type" id="scheme_type" value="<?= isset($rq['scheme_type'])?$rq['scheme_type']: $setting['scheme_type']['type'] ?>"  />
<input type="hidden" name="currency" id="currency" value="<?= isset($rq['currency'])?$rq['currency']: $setting['currency']['type'] ?>" /> 

	<div class="b-layout b-layout__page b-promo">
        <table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
            <tr class="b-layout__tr">
                <td class="b-layout__left b-layout__left_center b-layout__left_padtop_80 ">
                    <img class="b-promo__pic" src="/images/promo-icons/big/12.png" alt="" width="83" height="90" />
                </td>
                <td class="b-layout__right b-layout__right_width_73ps">
                <? 
                // Хлебные крошки
                include($_SERVER['DOCUMENT_ROOT'] . "/sbr/tpl.sbr-crumbs.php");
                ?>
		<div class="b-layout__txt b-layout__txt_padbot_20 b-layout__txt_fontsize_15">С помощью калькулятора можно точно рассчитать бюджет «Безопасной Сделки», сумму к оплате Заказчиком и сумму к выплате Исполнителю, а также посмотреть суммы комиссий, налогов и сборов, применимых к выбранному типу сотрудничества.</div>
		
        <div class="b-layout__txt b-layout__txt_padbot_20 b-layout__txt_fontsize_15 b-layout__txt_bold b-layout__txt_lineheight_20">Я &mdash;
		
		<div class="b-filter b-filter_height_15 b-filter_valign_top" id="first_block_tooltip">
			<div class="b-filter__body"><a class="b-filter__link b-filter__link_dot_0f71c8 b-filter__link_bold b-filter__link_fontsize_15" href="#"><?= $setting['usr_type']['text']?></a><span class="b-filter__arrow  b-filter__arrow_0f71c8  "></span></div>
			<div class="b-shadow b-shadow_marg_-11 b-filter__toggle b-filter__toggle_hide">
								<div class="b-shadow__body b-shadow__body_pad_10 b-shadow__body_bg_fff">
									<ul class="b-filter__list">
										<li class="b-filter__item b-filter__item_padbot_10"><a class="b-filter__link b-filter__link_bold b-filter__link_fontsize_15 <?=($setting['usr_type']['type'] == sbr::FRL + 1?"b-filter__link_no":"b-filter__link_dot_0f71c8");?>" href="javascript:void(0)" onclick="checkRole(1); setValueInput('usr_type', <?= (sbr::FRL + 1)?>);">исполнитель</a><span class="b-filter__marker b-filter__marker_galka <?=($setting['usr_type']['type'] == sbr::FRL + 1? "":"b-filter__marker_hide")?>"></span></li>
										<li class="b-filter__item "><a class="b-filter__link <?=($setting['usr_type']['type'] == sbr::EMP + 1?"b-filter__link_no":"b-filter__link_dot_0f71c8");?> b-filter__link_bold b-filter__link_fontsize_15" href="javascript:void(0)" onclick="checkRole(2); setValueInput('usr_type', <?= (sbr::EMP + 1 )?>);">заказчик</a><span class="b-filter__marker b-filter__marker_galka <?=($setting['usr_type']['type'] == sbr::EMP + 1? "":"b-filter__marker_hide")?>"></span></li>
									</ul>
								</div>
			</div>
		</div>, <span id="case_word">являющийся</span> 
		<div class="b-filter b-filter_height_15 b-filter_valign_top">
			<div class="b-filter__body"><a class="b-filter__link b-filter__link_dot_0f71c8 b-filter__link_bold b-filter__link_fontsize_15" href="#"><?= $setting['frl_type']['text']?></a><span class="b-filter__arrow  b-filter__arrow_0f71c8"></span>&#160;</div>
			<div class="b-shadow b-shadow_marg_-11 b-shadow_margleft_-29 b-filter__toggle b-filter__toggle_hide">
								<div class="b-shadow__body b-shadow__body_pad_10 b-shadow__body_bg_fff">
									<ul class="b-filter__list">
										<li class="b-filter__item b-filter__item_padbot_10">
												<div class="b-filter__item">
														<span class="b-tooltip b-tooltip_inline-block b-tooltip_margright_7 b-tooltip_valign_baseline"><span class="b-tooltip__ic"></span></span><a 
														class="b-filter__link b-filter__link_bold b-filter__link_fontsize_15 <?=($setting['frl_type']['type']==sbr::FT_PHYS?"b-filter__link_no":"b-filter__link_dot_0f71c8");?>" href="javascript:void(0)" onclick="setBlockScheme(2); setValueInput('frl_type', <?= sbr::FT_PHYS?>); setBlockScheme(2, 1);">физическим лицом</a><span class="b-filter__marker b-filter__marker_galka <?=($setting['frl_type']['type']==sbr::FT_PHYS?"":"b-filter__marker_hide")?>"></span>&#160;
												</div>
				<div class="i-tooltip i-tooltip_hide">				
						<div class="b-tooltip b-tooltip_margtop_5 b-tooltip_margright_-15 b-tooltip_transparent b-tooltip_nosik_yes b-tooltip_close_yes b-tooltip_fontsize_11 b-tooltip_zoom_1">
							<div class="b-tooltip__right">
								<div class="b-tooltip__left">
									<div class="b-tooltip__top">
										<div class="b-tooltip__topright" style="width:207px;"></div>
										<div class="b-tooltip__bottom">
											<div class="b-tooltip__body b-tooltip__body_width_200">
												<div class="b-tooltip__txt">Физическое лицо – отдельные<br/> граждане любого государства</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="b-tooltip__close"></div>
							<div class="b-tooltip__topleft"></div>
							<div class="b-tooltip__nosik"></div>
							<div class="b-tooltip__tl"></div>
							<div class="b-tooltip__tr"></div>
							<div class="b-tooltip__bl"></div>
							<div class="b-tooltip__br"></div>
						</div>
				</div>
										</li>
										<li class="b-filter__item">
												<div class="b-filter__item">
														<span class="b-tooltip b-tooltip_inline-block b-tooltip_margright_7 b-tooltip_valign_baseline "><span class="b-tooltip__ic"></span></span><a
														 class="b-filter__link b-filter__link_bold b-filter__link_fontsize_15 <?=($setting['frl_type']['type']==sbr::FT_JURI?"b-filter__link_no":"b-filter__link_dot_0f71c8");?>" href="javascript:void(0)" onclick="setValueInput('frl_type', <?= sbr::FT_JURI?>, 0); setBlockScheme(1);">юридическим лицом</a>
														<span class="b-filter__marker b-filter__marker_galka <?=($setting['frl_type']['type']==sbr::FT_JURI?"":"b-filter__marker_hide")?>"></span>
												</div>
				<div class="i-tooltip i-tooltip_hide">				
						<div class="b-tooltip b-tooltip_margtop_5 b-tooltip_margright_-15 b-tooltip_transparent b-tooltip_nosik_yes b-tooltip_close_yes b-tooltip_fontsize_11 b-tooltip_zoom_1">
							<div class="b-tooltip__right">
								<div class="b-tooltip__left">
									<div class="b-tooltip__top">
										<div class="b-tooltip__topright" style="width:207px;"></div>
										<div class="b-tooltip__bottom">
											<div class="b-tooltip__body b-tooltip__body_width_200">
												<div class="b-tooltip__txt">Юридические лица – ООО, ОАО, ЗАО, <br/>товарищества, ОДО, кооперативы. <br/>Сюда же относятся индивидуальные <br/>предприниматели (ИП)</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="b-tooltip__close"></div>
							<div class="b-tooltip__topleft"></div>
							<div class="b-tooltip__nosik"></div>
							<div class="b-tooltip__tl"></div>
							<div class="b-tooltip__tr"></div>
							<div class="b-tooltip__bl"></div>
							<div class="b-tooltip__br"></div>
						</div>
					</div>					
										</li>
									</ul>
								</div>
			</div>
		</div>

		<div class="b-filter b-filter_height_15 b-filter_valign_top" >
			<div class="b-filter__body"><a class="b-filter__link b-filter__link_dot_0f71c8 b-filter__link_bold b-filter__link_fontsize_15" href="#"><?= $setting['rez_type']['text']?></a><span class="b-filter__arrow  b-filter__arrow_0f71c8"></span>,</div><div 
			class="b-shadow b-shadow_marg_-11 b-filter__toggle b-filter__toggle_hide" id="shadow_rez_type">
								<div class="b-shadow__body b-shadow__body_pad_10 b-shadow__body_bg_fff">
									<ul class="b-filter__list">
										<li class="b-filter__item b-filter__item_padbot_10"><a class="b-filter__link b-filter__link_bold b-filter__link_fontsize_15 <?=($setting['rez_type']['type']==sbr::RT_UABYKZ?"b-filter__link_no":"b-filter__link_dot_0f71c8");?>" href="javascript:void(0)" onclick="setValueInput('residency', <?= sbr::RT_UABYKZ?>); ">нерезидентом Российской Федерации</a><span class="b-filter__marker b-filter__marker_galka <?=($setting['rez_type']['type']==sbr::RT_UABYKZ ? "":"b-filter__marker_hide")?>"></span></li>
										<li class="b-filter__item "><a class="b-filter__link b-filter__link_bold b-filter__link_fontsize_15 <?=($setting['rez_type']['type']==sbr::RT_RU?"b-filter__link_no":"b-filter__link_dot_0f71c8");?>" href="javascript:void(0)" onclick="setValueInput('residency', <?= sbr::RT_RU?>);">резидентом РФ</a><span class="b-filter__marker b-filter__marker_galka <?=($setting['rez_type']['type']==sbr::RT_RU ? "":"b-filter__marker_hide")?>"></span></li>
									</ul>
								</div>
			</div></div>
			<span id="second_block_tooltip"></span><span id="calc_role">хочу заключить <?=$setting['calc_role']['text']?></span>

		<?/*<div class="b-filter b-filter_height_15 b-filter_valign_top">
			<div class="b-filter__body"></div>
			 <div class="b-shadow b-shadow_marg_-11 b-filter__toggle b-filter__toggle_hide">
								<div class="b-shadow__body b-shadow__body_pad_10 b-shadow__body_bg_fff">
									<ul class="b-filter__list">
										<li class="b-filter__item b-filter__item_padbot_10"><a class="b-filter__link b-filter__link_bold b-filter__link_fontsize_15 <?=($setting['scheme_type']['type']==sbr::SCHEME_LC?"b-filter__link_no":"b-filter__link_dot_0f71c8");?>" href="javascript:void(0)" onclick="$('note_money').show(); setValueInput('scheme_type', <?= sbr::SCHEME_LC?>); setBlockScheme(4);">договор с аккредитивной формой расчетов</a><span class="b-filter__marker b-filter__marker_galka <?=($setting['scheme_type']['type']==sbr::SCHEME_LC?"":"b-filter__marker_hide")?>"></span></li>
										<li class="b-filter__item "><a class="b-filter__link b-filter__link_bold b-filter__link_fontsize_15 <?=($setting['scheme_type']['type']==sbr::SCHEME_PDRD2?"b-filter__link_no":"b-filter__link_dot_0f71c8");?>" href="javascript:void(0)" onclick="$('note_money').hide(); setValueInput('scheme_type', <?= sbr::SCHEME_PDRD2?>); setBlockScheme(3); ">договор подряда</a><span class="b-filter__marker b-filter__marker_galka <?=($setting['scheme_type']['type']==sbr::SCHEME_PDRD2?"":"b-filter__marker_hide")?>"></span></li>
									</ul>
								</div>
			</div>
                         
		</div>
		 */ ?>
		   <?= $setting['scheme_type']['text']?> &#160;с выводом средств 
			 
		<div class="b-filter b-filter_height_15 b-filter_valign_top">
			<div class="b-filter__body"><a class="b-filter__link b-filter__link_dot_0f71c8 b-filter__link_bold b-filter__link_fontsize_15" href="#"><?= $setting['currency']['text']?></a><span class="b-filter__arrow  b-filter__arrow_0f71c8  "></span></div>
			<div class="b-shadow b-shadow_baseline b-shadow_marg_-11 b-filter__toggle b-filter__toggle_hide">
								<div class="b-shadow__body b-shadow__body_pad_10 b-shadow__body_bg_fff">
									<ul class="b-filter__list" id="block_scheme">
                                        <li class="b-filter__item <?=($setting['frl_type']['type']==sbr::FT_JURI? "" : "b-filter__item_padbot_10");?>"><a class="b-filter__link b-filter__link_bold b-filter__link_fontsize_15 <?=($setting['currency']['type']==exrates::BANK?"b-filter__link_no":"b-filter__link_dot_0f71c8");?>" href="javascript:void(0)" id="bank_scheme">на банковский счет</a><span class="b-filter__marker b-filter__marker_galka <?=($setting['currency']['type']==exrates::BANK ?"":"b-filter__marker_hide")?>"></span></li>
                                        <li class="b-filter__item b-filter__item_padbot_10" <?=($setting['frl_type']['type']==sbr::FT_JURI || $setting['scheme_type']['type'] == sbr::SCHEME_LC?"style='display:none'":"");?>><a class="b-filter__link b-filter__link_bold b-filter__link_fontsize_15 b-filter__pdrd <?=($setting['currency']['type']==exrates::YM?"b-filter__link_no":"b-filter__link_dot_0f71c8");?>" href="javascript:void(0)" onclick="setValueInput('currency', <?= exrates::YM ?>)">на Яндекс.Деньги</a><span class="b-filter__marker b-filter__marker_galka <?=($setting['currency']['type']==exrates::YM?"":"b-filter__marker_hide")?>"></span></li>
                                        <li class="b-filter__item b-filter__item_padbot_10" <?=($setting['frl_type']['type']==sbr::FT_JURI?"style='display:none'":"");?>><a class="b-filter__link b-filter__link_bold b-filter__link_fontsize_15 <?=($setting['currency']['type']==exrates::WMR?"b-filter__link_no":"b-filter__link_dot_0f71c8");?>" href="javascript:void(0)" onclick="setValueInput('currency', <?= exrates::WMR ?>)">на WebMoney</a><span class="b-filter__marker b-filter__marker_galka <?=($setting['currency']['type']==exrates::WMR ?"":"b-filter__marker_hide")?>"></span></li>
                                        <li class="b-filter__item " <?=($setting['frl_type']['type']==sbr::FT_JURI?"style='display:none'":"");?>><a class="b-filter__link b-filter__link_bold b-filter__link_fontsize_15 b-filter__pskb <?=($setting['currency']['type']==exrates::WEBM?"b-filter__link_no":"b-filter__link_dot_0f71c8");?>" href="javascript:void(0)" onclick="setValueInput('currency', <?= exrates::WEBM ?>)" id="webm_scheme">на Веб-кошелек</a><span class="b-filter__marker b-filter__marker_galka <?=($setting['currency']['type']==exrates::WEBM ?"":"b-filter__marker_hide")?>"></span></li>
                                        <li class="b-filter__item " <?=($setting['scheme_type']['type'] == sbr::SCHEME_LC?"style='display:none'":"");?>><a class="b-filter__link b-filter__link_bold b-filter__link_fontsize_15 b-filter__pdrd <?=($setting['currency']['type']==exrates::FM?"b-filter__link_no":"b-filter__link_dot_0f71c8");?>" href="javascript:void(0)" onclick="setValueInput('currency', <?= exrates::FM ?>)">на Счет сайта (в FM)</a><span class="b-filter__marker b-filter__marker_galka <?=($setting['currency']['type']==exrates::FM ?"":"b-filter__marker_hide")?>"></span></li>
                                    </ul>
									
								</div>
			</div>
		</div>
			 
			 
			 </div>
		
		
<div class="b-tax b-tax_margbot_20 b-tax_width_700">
	<div class="b-tax__fon">
		<div class="b-tax__rama-t">
			<div class="b-tax__rama-b">
				<div class="b-tax__rama-l">
					<div class="b-tax__rama-r">
						<div class="b-tax__content b-tax__content_width_600">
                            <div class="b-tax__level b-tax__level_padbot_20">
								<div class="b-tax__txt b-tax__txt_fontsize_22 b-tax__txt_width_340 b-tax__txt_inline-block">Бюджет всех этапов</div>
                                
                                <div class="b-combo b-combo_inline-block ">
                                    <div class="b-combo__input b-combo__input_width_110">
                                        <input class="b-combo__input-text b-combo__input-text_fontsize_22" id="sbr_cost" type="text" name="sbr_cost" size="80" maxlength="10" value="<?=isset($rq)?$rq['sbr_cost']:""?>" />
                                        <label class="b-combo__label" for="sbr_cost"></label>
                                    </div>
                                </div>
                                <div class="b-form__txt b-form__txt_fontsize_15 b-form__txt_bold b-form__txt_padtop_2 b-form__txt_padleft_10">руб.</div>
							</div>
                            
                            
                            <div id="first_block">		
                                <div id="emp_block">
                                    <div class="b-tax__level b-tax__level_padbot_12 b-tax__level_margbot_15 b-tax__level_double b-filter__toggle_hide table_title table_title_emp">
                                        <div class="b-tax__txt b-tax__txt_width_340 b-tax__txt_inline-block b-tax__txt_fontsize_11">Налоги и вычеты</div>
                                        <div class="b-tax__txt b-tax__txt_width_120 b-tax__txt_valign_top b-tax__txt_padtop_2  b-tax__txt_inline-block b-tax__txt_fontsize_11">Сумма, руб.</div>
                                        <div class="b-tax__txt b-tax__txt_width_130 b-tax__txt_valign_top  b-tax__txt_inline-block b-tax__txt_fontsize_11">% от бюджета проекта</div>
                                    </div>
                                    <? foreach($sbr->getSchemes() as $sch) { ?>
                                        <? foreach ($sch['taxes'][1] as $id => $tax) { $is_tax_com = ($id == sbr::TAX_OLD_COM || $id == sbr::TAX_EMP_COM);?>
                                        <div class="b-tax__level b-tax__level_padbot_10 b-tax__level_margbot_10  b-tax__level_bordbot_cfd0c5 b-filter__toggle_hide sbr_taxes" id="taxrow_<?=$sch['type'].'_'.$id?>">
                                            <div class="b-tax__txt b-tax__txt_lineheight_16 b-tax__txt_width_340 b-tax__txt_inline-block">
                                                    <?= $tax['name'];?>
                                                    <div class="i-shadow i-shadow_inline-block" style="display:none">
                                                        <span class="b-shadow__icon b-shadow__icon_quest"></span>
                                                        <div class="b-shadow b-shadow_width_270 b-shadow_left_-117 b-shadow_hide">
                                                                            <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_15">
                                                                                <div class="b-shadow__txt">Эту плату берет портал Free-lance.ru с исполнителя за пользование сервисом «Безопасная Сделка».</div>
                                                                            </div>
                                                            <span class="b-shadow__icon b-shadow__icon_close"></span>
                                                            <span class="b-shadow__icon b-shadow__icon_nosik"></span>
                                                        </div>
                                                    </div>
                                            </div>
                                            <div class="b-tax__txt b-tax__txt_bold b-tax__txt_width_120 b-tax__txt_valign_top b-tax__txt_padtop_2  b-tax__txt_inline-block">+ <span class="second">0.00</span></div>
                                            <div class="b-tax__txt b-tax__txt_width_130 b-tax__txt_valign_top  b-tax__txt_inline-block b-tax__txt_fontsize_11">
                                                <?php if($tax['tax_code'] == 'TAX_FL') {?>
                                                    <img class="b-tax__pic b-tax__pic_float_right b-tax__pic_margtop_-3" src="/css/block/b-tax/f.png" alt="" />
                                                <?php }//if?>
                                                <?= $tax['percent']*100 ?>
                                            </div>
                                        </div>
                                        <? } //foreach?>
                                    <? }//foreach?>
                                    <div class="b-tax__level b-tax__level_padbot_40" id="block_calc_emp">
                                        <div class="b-tax__txt b-tax__txt_fontsize_22 b-tax__txt_width_340 b-tax__txt_inline-block" id="block_calc_emp_text">Вы заплатите</div>

                                        <div class="b-combo b-combo_inline-block ">
                                            <div class="b-combo__input b-combo__input_width_110">
                                                <input class="b-combo__input-text b-combo__input-text_fontsize_22" id="emp_cost" type="text" name="emp_cost" size="80" value="<?=isset($rq)?$rq['emp_cost']:""?>" maxlength="10" disabled/>
                                                <label class="b-combo__label" for="emp_cost"></label>
                                            </div>
                                        </div>
                                        <div class="b-form__txt b-form__txt_fontsize_15 b-form__txt_bold b-form__txt_padtop_2 b-form__txt_padleft_10">руб.</div>
                                    </div>
                                </div>
                            </div>
                            <div id="second_block">	
                                <div id="freelancer_block">
                                    <div class="b-tax__level b-tax__level_padbot_12 b-tax__level_margbot_15 b-tax__level_double b-filter__toggle_hide table_title table_title_frl">
                                        <div class="b-tax__txt b-tax__txt_width_340 b-tax__txt_inline-block b-tax__txt_fontsize_11">Налоги и вычеты</div>
                                        <div class="b-tax__txt b-tax__txt_width_120 b-tax__txt_valign_top b-tax__txt_padtop_2  b-tax__txt_inline-block b-tax__txt_fontsize_11">Сумма, руб.</div>
                                        <div class="b-tax__txt b-tax__txt_width_130 b-tax__txt_valign_top  b-tax__txt_inline-block b-tax__txt_fontsize_11">% от бюджета проекта</div>
                                    </div>
                                    <? foreach($sbr->getSchemes() as $sch) { ?>
                                        <? if (isset($sch['taxes'][0])) foreach ($sch['taxes'][0] as $id => $tax) { $is_tax_com = ($id == sbr::TAX_OLD_COM || $id == sbr::TAX_FRL_COM); ?>
                                        <div class="b-tax__level b-tax__level_padbot_10 b-tax__level_margbot_10  b-tax__level_bordbot_cfd0c5 b-filter__toggle_hide sbr_taxes" id="taxrow_<?=$sch['type'].'_'.$id?>">
                                            <div class="b-tax__txt b-tax__txt_lineheight_16 b-tax__txt_width_340 b-tax__txt_inline-block">
                                                    <?= $tax['name'];?>
                                                    <div class="i-shadow i-shadow_inline-block" style="display:none">
                                                        <span class="b-shadow__icon b-shadow__icon_quest"></span>
                                                        <div class="b-shadow b-shadow_width_270 b-shadow_left_-117 b-shadow_hide">
                                                                            <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_15">
                                                                                <div class="b-shadow__txt">Эту плату берет портал Free-lance.ru с исполнителя за пользование сервисом «Безопасная Сделка».</div>
                                                                            </div>
                                                            <span class="b-shadow__icon b-shadow__icon_close"></span>
                                                            <span class="b-shadow__icon b-shadow__icon_nosik"></span>
                                                        </div>
                                                    </div>
                                            </div>
                                            <div class="b-tax__txt b-tax__txt_bold b-tax__txt_width_120 b-tax__txt_valign_top b-tax__txt_padtop_2  b-tax__txt_inline-block">- <span class="second">0.00</span></div>
                                            <div class="b-tax__txt b-tax__txt_width_130 b-tax__txt_valign_top  b-tax__txt_inline-block b-tax__txt_fontsize_11">
                                                <?php if($tax['tax_code'] == 'TAX_FL') {?>
                                                    <img class="b-tax__pic b-tax__pic_float_right b-tax__pic_margtop_-3" src="/css/block/b-tax/f.png" alt="" />
                                                <?php }//if?>
                                                <?= $tax['percent']*100 ?>
                                            </div>
                                        </div>
                                        <? }//foreach?>
                                    <? }//foreach?>
                                    <div class="b-tax__level b-tax__level_padbot_20" id="block_calc_freelance">
                                        <div class="b-tax__txt b-tax__txt_fontsize_22 b-tax__txt_width_340 b-tax__txt_inline-block" id="block_calc_frl_text">Исполнитель получит</div>

                                        <div class="b-combo b-combo_inline-block ">
                                            <div class="b-combo__input b-combo__input_width_110">
                                                <input id="frl_cost" class="b-combo__input-text b-combo__input-text_fontsize_22" name="frl_cost" type="text" maxlength="10"  size="80" value="<?=isset($rq)?$rq['frl_cost']:""?>" disabled/>
                                            </div>
                                        </div>
                                        <div class="b-form__txt b-form__txt_fontsize_15 b-form__txt_bold b-form__txt_padtop_2 b-form__txt_padleft_10">руб.</div>
                                        <div class="b-tax__txt b-tax__txt_padleft_350 b-tax__txt_padtop_3"><span style="display:none" id="rating_get">и 100 баллов рейтинга</span></div> 
                                    </div>
                                </div>
                            </div>
                            
                            <div class="b-fon finance-min-alert1 b-fon_padbot_20" id="note_money" style="<?= ( $setting['scheme_type']['type'] == sbr::SCHEME_PDRD2 ? 'display:none' : ''); ?>">
                                <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_ffebbf">
                                    <span><span class="b-icon b-icon_sbr_oattent b-icon_margleft_-25"></span>Вывод денежных средств в этапах с бюджетом до 15 000 рублей осуществляется только на веб-кошелек. Подробнее <a href="<?= HTTP_PREFIX ?>feedback.fl.ru/topic/397421-veb-koshelek-obschaya-informatsiya/" class="b-fon__link">о веб-кошельке</a>.
                                    <div id="ya_pay" style="display:none"><br/><span class="b-icon b-icon_sbr_oattent b-icon_margleft_-25"></span>Чтобы получить деньги, вам необходимо принять новое <a href="http://money.yandex.ru/offer.xml?from=llim" class="b-fon__link" target="_blank">соглашение об использовании</a> сервиса &laquo;Яндекс.Деньги&raquo;.</div>
                                </div>
                            </div>
                            
                        </div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
		
        <div class="b-layout__txt b-layout__txt_color_c10600 b-layout__txt_padleft_20 " style="display:none"><span class="b-icon b-icon_margleft_-20 b-icon_sbr_rattent"></span><span id="text_error">Не пополняйте свой FM-счет, из него резервация средств для «Безопасных Сделок» запрещена</span></div>
		<h2 class="b-layout__title b-layout__title_padtop_35 b-layout__title_padbot_20">Поделитесь расчетом <span id="link_role">с работодателем</span></h2>
		<div class="b-form">
			<label class="b-form__name b-form__name_padtop_5 b-form__name_width_150 b-form__name_fontsize_13" for="b-input__text4">Ссылка на этот расчет</label>
			<div class="b-input b-input_height_20 b-input_inline-block b-input_width_360">
				<input id="hash_link" onclick="$(this).select();" class="b-input__text b-input__text_bold" name="link_calc" type="text" size="120" value="" />
			</div>
		</div>
		
		</td>							
    </tr>
</table>
	</div>
	</form>
<style type="text/css">
.fourth{ line-height:1; font-size:11px; vertical-align:baseline; color:#4d4d4d;}
</style>	
       
<?= $sbr->isAdmin()? '</div></div>' : ''?>