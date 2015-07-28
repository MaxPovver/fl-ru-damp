<div class="b-layout__right b-layout__right_relative b-layout__left_width_72ps">
    <h1 class="b-page__title">Автоответы на проект</h1>

    <div class="b-page__filter">
        <div class="b-menu b-menu_line b-menu_relative b-menu_padbot_10">
            <div class="b-menu__filter"></div>

            <ul class="b-menu__list">
                <li class="b-menu__item "><a href="/projects/?kind=5" class="b-menu__link"><span class="b-page__desktop">Вся работа</span><span class="b-page__ipad b-page__iphone">Все</span></a></li>
                <li class="b-menu__item "><a class="b-menu__link" href="/projects/?kind=1">Проекты</a></li>
                <li class="b-menu__item "><a class="b-menu__link" href="/projects/?kind=4">Вакансии</a></li>
                <li class="b-menu__item "><a class="b-menu__link" href="/konkurs/">Конкурсы</a></li>
            </ul>
        </div>
    </div>

    <!--<div class="b-page__lenta ">-->
    <div class="b-page__lenta ">
        <?php /*
        <div class="b-layout b-layout_padtop_20 b-layout_padbot_20 b-layout_bordbot_b2">            
            <?php
                //Для примера
                require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/quick_payment.common.php");
                $xajax->printJavascript('/xajax/');
            ?>
            
            <?=quickPaymentPopupAutoresponse::getInstance()->render(array('reserve_price' => 77777))?>
            
            <div class="b-layout__txt b-layout__txt_padbot_20">Составьте текст ответа и он будет добавляться автоматически к проектам по выбранным вами критериям. <br>Стоимость одного автоответа — <?=autoresponse::$config['price']?> руб.</div>
            
            <?php if (!$form->hasErrors()): ?>
                <a class="b-button b-button_flat b-button_flat_green" href="#" onclick="this.getNext('.b-layout').removeClass('b-layout_hide');this.dispose();return false;">Добавить автоответ</a>
            <?php endif; ?>

            <iframe name="quick_ar_iframe" id="quick_ar_iframe" style="display: none;"></iframe>

            <div class="b-layout <?php if (!$form->hasErrors()):?>b-layout_hide<?php endif;?>">
                <form method="post" action="/autoresponse/" id="frm">
                    <table class="b-layout__table b-layout__table_width_full">
                        <tbody>
                            <tr class="b-layout__tr">
                                <?=$form->getElement('descr')->render();?>
                            </tr>

                            <tr class="b-layout__tr">
                                <?=$form->getElement('only_4_cust')->render();?>
                            </tr>

                            <tr class="b-layout__tr">
                                <?=$form->getElement('total')->render();?>
                            </tr>

                            <tr class="b-layout__tr">
                                <td class="b-layout__td b-layout__td_padbot_10" colspan="2">
                                    <h3 class="b-layout__h3">Критерии выбора проектов</h3>
                                </td>
                            </tr>

                            <tr class="b-layout__tr">
                                <td class="b-layout__td b-layout__td_width_120 b-layout__td_padbot_20"><div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padtop_2">Специализация</div></td>
                                <td class="b-layout__td b-layout__td_padbot_20">
                                    <div class="b-combo b-combo__input_width_250 b-combo_margright_5 b-combo_zindex_3">
                                      <div class="b-combo__input b-combo__input_width_250 b-combo__input_multi_dropdown 
                                           b-combo__input_orientation_right b-combo__input_resize b-combo__input_max-width_450 
                                           b-combo__input_visible_height_200 b-combo__input_arrow_yes 
                                           b-combo__input_init_professionsList sort_abc  
                                           drop_down_default_0 multi_drop_down_default_column_0 
                                           exclude_value_0_0 sort_abc">
                                          <input id="filter_category" class="b-combo__input-text" name="filter_category" type="text" size="80" value="Все специализации" />
                                          <span class="b-combo__arrow"></span>
                                      </div>
                                    </div>

                                    <div class="b-layout__txt b-layout__txt_padtop_5 b-layout__txt_color_c10600 b-layout_hide" id="el-filter_category-error">
                                        <span class="b-icon b-icon_sbr_rattent"></span>
                                        <span id="el-filter_category-error-text"></span>
                                    </div>
                                </td>
                            </tr>

                            <tr class="b-layout__tr">
                                <?=$form->getElement('filter_budget')->render();?>
                            </tr>

                            <tr class="b-layout__tr">
                                <td colspan="3">
                                     <a href="javascript:void(0);" id="ar-save-btn" 
                                        class="b-button b-button_flat b-button_flat_green">
                                            Купить за <span id="ar-submit-price"><?=autoresponse::$config['default_quantity'] * autoresponse::$config['price']?></span> руб.
                                     </a>
                                </td>                                 
                            </tr>

                        </tbody>
                    </table>
                </form>
            </div>    
        </div>
        */ ?>

        <?php if ($autoresponse_list): ?>
            <?php foreach ($autoresponse_list as $response): ?>
                <div class="b-layout b-layout_padtop_30 b-layout_padbot_50 b-layout_bordbot_b2">
                    <div class="b-layout__txt b-layout__txt_float_right b-layout__txt_bold">Осталось автоответов: <?=$response->data['remained']?></div>
                    <div class="b-layout__txt b-layout__txt_padbot_10">
                        <span class="b-layout__bold">
                            Куплено автоответов: <?=$response->data['total']?> 
                        </span>
                        &mdash; 
                        <span class="b-layout__txt b-layout__txt_color_808080">
                            <?=dateFormat("d.m.Y в H:i", $response->data['payed_date'])?>
                        </span>
                    </div>
                    <div class="b-fon b-fon_bg_fa b-fon_pad_20">
                        <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padbot_20">
                            <?=reformat($stop_words->replace(htmlspecialchars($response->data['descr'])), 30, 0, 0, 1)?>
                        </div>
                        <?php if ($response->toBoolean($response->data['only_4_cust'])):?>
                            <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_color_000">Автоответ виден только заказчику (автору проекта).</div>
                        <?php endif; ?>
                        <?php if ($response->data['filter_subcategory_id'] || $response->data['filter_category_id']): ?>
                            <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_color_000">
                                Размещение в специализации:
                                <?php if ($response->data['filter_subcategory_id']): ?> 
                                    <?=professions::GetProfNameWP($response->data['filter_subcategory_id'], ' / ')?>
                                <?php elseif ($response->data['filter_category_id']): ?>
                                    <?=professions::GetProfGroupTitle($response->data['filter_category_id'])?> 
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_color_000">
                            <?php if ($response->data['filter_budget']): ?>                            
                                <?php $currencyList = array('USD', 'евро', 'руб'); ?>
                                <?php $pricebyList = array(1 => 'за час', 2 => 'за день', 3 => 'за месяц', 4 => 'за проект'); ?>
                                Бюджет проектов: от <?=$response->data['filter_budget']?> 
                                <?=isset($currencyList[$response->data['filter_budget_currency']])?$currencyList[$response->data['filter_budget_currency']]:' руб'?>
                                <?=isset($pricebyList[$response->data['filter_budget_priceby']])?$pricebyList[$response->data['filter_budget_priceby']]:' за проект'?>
                            <?php else: ?>
                                Бюджет проектов: любой
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <!-- b-page__lenta -->     
</div>

<?php /*
<script type="text/javascript">
    var autoresponse_price = <?=autoresponse::$config['price']?>;
</script>
*/