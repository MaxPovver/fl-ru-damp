<div class="bill-h c">
	<div class="bill-help">
	
		<div class="b-tel-help b-tel-help_width_265">
		    <img class="b-tel-help__photo" src="/images/temp/photo.jpg" alt="" width="50" height="50" />
        	<div class="b-tel-help__tooltip b-tel-help__tooltip_205x41">
        	    <? if(NY2012TIME) { ?>
        	    <div class="b-tel-help__txt b-tel-help__txt_bold">С 31 декабря по 9 января</div>
		        <div class="b-tel-help__txt">телефон работать не будет</div>
        		<? } else {?>
        		<div class="b-tel-help__txt b-tel-help__txt_bold">Возникли сложности?</div>
        		<div class="b-tel-help__txt">Не беда, я вам помогу!</div> 
        		<? }?>
        	</div>
        	<div class="b-tel-help__txt b-tel-help__txt_bold"><a class="b-footer__link" href="https://feedback.free-lance.ru/" target="_blank"><strong>Задать вопрос</strong></a></div>
        </div>	
	</div>
	<? if ($$uid) { ?>
    <div class="bill-b">
		<div class="bill-b-in">
            На счету <strong><?= round($$account->sum, 2); ?></strong> руб.
            <? if (is_emp()) { ?>
            &nbsp;&nbsp;&nbsp;&nbsp;
            <a href="/bezopasnaya-sdelka/?filter=disable" class="b-button b-button_round_green" style="position:relative;top:-3px;">
                <span class="b-button__b1">
                    <span class="b-button__b2">
                        <span class="b-button__txt">Зарезервировать деньги для Безопасной Сделки</span>
                    </span>
                </span>
            </a>
            <? } ?>
        </div>
		<? if ($$account->bonus_sum > 0): ?><div class="bill-b-in">На бонусном счету <strong><?= round($$account->bonus_sum, 2); ?></strong> руб.</div><? endif; ?>
		<div class="b-layout__txt_relative b-layout__txt_margtop_-10">Номер вашего счета: <?=$$account->id?></div>
		
		<?php $exrates = new exrates(); ?>
		<?/*<div style=" width: 80px; background-color: #FFEDA9; padding: 7px; margin: 7px 0 0;">
            <span style="white-space:nowrap"><strong>1 FM</strong> = <?=$exrates->GetField(15, $err, 'val')?> руб.</span>
		</div>*/?>
	</div>
    <? } ?>
	<?/* <div class="bill-FM">
		<p><strong>Free-Money (FM)</strong> &ndash; это условная валюта сайта Free-lance.ru,<br />
		используемая для внутренних расчетов на сайте.<br /><br />
		Смотрите в разделе Помощь:<br /><a href="/help/?q=809">Валюта сайта (FM) и личный счет на сайте</a></p>
	</div>*/?>
	<?php if(date('Ymd') <= '20120110') { ?>
	<div class="bill-news-txt">Денежные средства, поступившие по безналичному расчету после 12:00 мск 28 декабря 2011 г., будут зачислены после 10 января 2012 г.</div>
	<?php }//if?>
	
			<?/*<div class="b-fon  b-fon_float_left b-fon_clear_left b-fon_padtop_20">
					<div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_35 b-fon__body_fontsize_13 b-fon__body_bg_ffebbf b-fon__body_bord_e7cca5">
					<span class="b-fon__attent_pink"></span>Обмен FM на другие валюты запрещен. Вывод FM с Личного счета не производится. 
				</div>
			</div>*/?>
            <?php if($$master) { ?>
            <script type="text/javascript" src="/scripts/wizard/wizard.js"></script>
            <script type="text/javascript">
            window.addEvent('domready', 
                function() {
                    calcAmmountOfOption($$('.scalc-click-dis'), $('scalc_result'));
                }
            );
                
            var ac_sum = <?= round($_SESSION['ac_sum'],2)?>;
            </script>
            <h3 class="b-layout__h3 b-layout__h3_padtop_30 b-layout__h3_clear_both b-layout__h3_padbot_20">В мастере регистрации вы заказали услуги:</h3>
            <?php 
            $tr_id = intval($_REQUEST['transaction_id']);
            $account = new account();
            $transaction_id = $account->start_transaction($_SESSION['uid'], $tr_id);
            
            // узнаем выбран ли или куплен аккаунт ПРО
            require_once $_SERVER['DOCUMENT_ROOT'].'/classes/projects.php';
            $is_pro = is_pro();
            if (!$is_pro) {
                // перебираем все выбранные платные опции
                foreach($$master as $k=>$pay) {
                    if ($pay['op_code'] == 15) {
                        $is_pro = true;
                        break;
                    }
                }
            }
            // бонусная сумма (для ПРО = 10, для неПРО = 0)
            $proBonus = $is_pro ? new_projects::PRICE_ADDED : 0;
            ?>
            <input type="hidden" name="pro_bonus" value="<?= $proBonus; ?>">
            <input type="hidden" name="is_pro" value="<?= is_pro() ? 1 : 0 ?>">
            <form method="POST" name="wizard_operation" id="wizard_operation" action="/payed/wizard_operation.php">
                <input type="hidden" name="action" value="buy">
                <input type="hidden" name="isEmp" id="isEmp" value="<?= is_emp() ? 1 : 0 ?>">
                <input type="hidden" name="transaction_id" value="<?=$transaction_id?>" />
            <?php foreach($$master as $option) {
                if($option['op_type'] == 'contest') $project_name = $option['contest_name'];
                else $project_name = $option['project_name'];
                
                // если уже куплен ПРО, то выделение цветом - бесплатно
                if (is_pro() && $option['op_code'] == 53 && $option['option'] == 'color') {
                    if (!is_array($$disabled)) {
                        $$disabled = array(0);
                    }
                    $$disabled[$option['id']] = true;                    
                }
                if(!$$disabled[$option['id']]) {
                    // учитываем количество дней для "закрепления проекта на верху"
                    if ($option['option'] == 'top') {
                        $days = $option['op_count'];
                    } else {
                        $days = 1;
                    }
                    // 
                    $ammount = $option['ammount'] - ($option['op_code'] != 15 && is_emp() ? ($proBonus * $days) : 0);
                    $sum += $ammount;
                } ?>
                <div class="b-check b-check_padbot_15 ">
                    <input id="pay<?= (int)$option['id']?>" class="b-check__input scalc-click-dis" name="operation[]" type="checkbox" value="<?= $option['id']?>" checked="checked" 
                           <?= ($$disabled[$option['id']] ? 'disab="1"':"")?>
                           price="<?= (int) $option['ammount'];?>" <?= ($$dis[$option['id']]) ? "dis='{$$dis[$option['id']]}'":""?> top_count="<?= (int)$option['op_count']?>" option="<?= $option['option']?>" op_code="<?= (int)$option['op_code']?>" pid="<?=$option['id']?>"/>
                    <label for="b-check2" class="b-check__label b-check__label_fontsize_13"><?php switch($option['op_code']) {
                            case 15:
                                ?>Аккаунт <div class="b-check__pro b-check__pro_e"></div> на <?= ($option['ammount']/570)?> <?= ending($option['ammount']/570, "месяц", "месяца", "месяцев");?><?
                                break;
                            case 53:
                                switch($option['option']) {
                                    case 'top':
                                        ?>Закрепление <?=$option['op_type'] == 'contest'?"конкурса":"проекта"?> «<?=$project_name?>» на верху ленты<?
                                        break;
                                    case 'color':
                                        ?>Выделение цветом <?=$option['op_type'] == 'contest'?"конкурса":"проекта"?> «<?=$project_name?>»<?
                                        break;
                                    case 'bold':
                                        ?>Выделение жирным <?=$option['op_type'] == 'contest'?"конкурса":"проекта"?> «<?=$project_name?>»<?
                                        break;
                                    case 'logo':
                                        ?>Логотип для <?=$option['op_type'] == 'contest'?"конкурса":"проекта"?> «<?=$project_name?>»<?
                                        break;
                                    default:
                                        ?>Платный <?=$option['op_type'] == 'contest'?"конкурс":"проект"?> «<?=$project_name?>»<?
                                        break;
                                }
                                break;
                            case 9:
                            case 106:
                                ?>Публикация конкурса «<?=$project_name?>»<?
                                break;
                            case 61:
                                ?>
                                <?=$option['op_count']?> <?=ending($pay['op_count'], 'платный','платных','платных')?> <?=ending($option['op_count'], 'ответ', 'ответа', 'ответов')?> на <?= ending($option['op_count'], 'проект', 'проекты', 'проекты')?>
                                <?= $$is_pay_pro? '<span class="b-check__txt b-check__txt_color_6db335">&mdash; входят в стоимость аккаунта PRO</span>':''?>
                                <?
                                break;
                            case 76:
                                ?>Аккаунт <div class="b-check__pro b-check__pro_f"></div> на 1 неделю<?
                                break;
                            case 48:
                                ?>Аккаунт <div class="b-check__pro b-check__pro_f"></div> на 1 месяц<?    
                                break;
                            case 49:
                                ?>Аккаунт <div class="b-check__pro b-check__pro_f"></div> на 3 месяца<?
                                break;
                            case 50:
                                ?>Аккаунт <div class="b-check__pro b-check__pro_f"></div> на 6 месяцев<?
                                break;
                            case 51:
                                ?>Аккаунт <div class="b-check__pro b-check__pro_f"></div> на 1 год<?
                                break;        
                    } ?></label>
                </div>
            <?php }//foreach?>
            </form>
			<h3 class="b-layout__h3 b-layout__h3_padbot_25">Это стоит <span class="b-layout__txt b-layout__txt_color_fd6c30"><span id="scalc_result"><?= $sum;?></span> <?= ending($sum, 'рубль', 'рубля', 'рублей');?></span></h3>
			<div class="b-buttons">
				<a href="javascript:void(0)" onclick="if(!$(this).hasClass('b-button_rectangle_color_disable'))$('wizard_operation').submit();" id="wizard_button" class="b-button b-button_rectangle_color_green <?= ($sum > $_SESSION['ac_sum'])?"b-button_rectangle_color_disable":""?>">
						<span class="b-button__b1">
								<span class="b-button__b2">
										<span class="b-button__txt">Оплатить услуги</span>
								</span>
						</span>
				</a>
				<div id="wizard_error_btn" class="b-buttons__txt b-buttons__txt_padleft_10 b-buttons__txt_color_ee1d16" <?= ($sum > $_SESSION['ac_sum'])?"":"style='display:none'"?>>У вас недостаточно денег на счете. Способы пополнения &mdash; ниже.</div>
			</div>
            <?php }//if?>
	
	
	<div class="bill-norisk">
		<table style="width:100%;">
			<col width="140" />
			<col />
			<col width="100" />
			<? if($$sbr_reserved): ?>
				<? foreach($$sbr_reserved as $s): if($s->scheme_type == sbr::SCHEME_LC) continue; ?>
				<tr>
					<th><strong><?=sbr_meta::view_cost($s->getReserveSum(), $s->cost_sys)?></strong></th>
					<td><a href="<? if ($s->scheme_type == sbr::SCHEME_AGNT || $s->scheme_type != sbr::SCHEME_PDRD) { ?>/norisk2/?id=<?=$s->id?><? } else { ?>/sbr/?id=<?=$s->id?><? } ?>"><?=reformat($s->name, 40, 0, 1)?></a></td>
					<td><?=date('d.m.Y H:i', strtotime($s->reserved_time))?></td>
				</tr>
				<? endforeach; ?>
			<? endif; ?>
		</table>
	</div>
</div>

<div class="b-menu b-menu_tabs b-menu_bg_fff9e7">
    <ul class="b-menu__list b-menu__list_overflow_hidden b-menu__list_padleft_20">
        <li class="b-menu__item <?=($$page=="index"?' b-menu__item_active':'')?>"><a class="b-menu__link" href="/<?=$$name_page?>/"><span class="b-menu__b1">Пополнить счет</span></a></li>
        <li class="b-menu__item <?=($$page=="history"?' b-menu__item_active':'')?>"><a class="b-menu__link" href="/<?=$$name_page?>/history/"><span class="b-menu__b1">История счета</span></a></li>
        <li class="b-menu__item <?=($$page=="buy"?' b-menu__item_active':'')?>"><a class="b-menu__link b-menu__link_bold" href="/<?=$$name_page?>/buy/"><span class="b-menu__b1">Оплатить услуги</span></a></li>
        <?php if(hasPermissions('payments')) { ?>
        <li class="b-menu__item <?=($$page=="send"?' b-menu__item_active':'')?>"><a class="b-menu__link" href="/<?=$$name_page?>/send/"><span class="b-menu__b1">Перевести деньги</span></a></li>
        <?php }//if?>
    </ul>
</div>


