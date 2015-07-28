<script type="text/javascript">
var domain4cookie = '<?=$GLOBALS['domain4cookie']?>';
window.addEvent('domready', 
    function() {
        <?php if($category > 0) { ?>
        ComboboxManager.getInput("category").breadCrumbs[0] = '<?=$category?>';
        <?php }//if?>
        <?php if($subcategory > 0) { ?>
        ComboboxManager.getInput("category").breadCrumbs[1] = '<?=$subcategory?>';
        <?php }//if?>
    }
); 
</script>
<div class="b-layout__right b-layout__right_width_72ps b-layout__right_float_right  b-layout__right_padbot_20">
    <div class="b-layout__txt b-layout__txt_padbot_10">Ётот мастер поможет вам сделать все, чтобы увеличить свои шансы найти достойную работу. ѕожалуйста, внимательно изучите информацию, представленную ниже, и пройдите все шаги мастера. Ќам очень важно, чтобы вы получили на нашем сайте интересную и хорошо оплачиваемую работу.</div>
    <div class="b-layout__txt b-layout__txt_padbot_10">—амый быстрый и простой способ найти работу Ц ответить на проект. ѕроекты Ц это объ€влени€, размещаемые заказчиками. ¬ них описываетс€ задача, которую необходимо выполнить, указываютс€ услови€ работы и размер оплаты.</div>
    <div class="b-layout__txt b-layout__txt_padbot_10">¬ам нужно отвечать на те проекты, которые вы сможете выполнить. ≈сли вы подойдете работодателю, он выберет вас исполнителем и начнет с вами сотрудничество.</div>
    <div class="b-layout__txt b-layout__txt_padbot_40">ƒл€ начала мы расскажем, как найти интересующий вас проект.</div>
    <h2 class=" b-layout__title">јктуальные проекты</h2>

    <div class="b-ext-filter b-ext-filter_margbot_15">
        <div class="b-ext-filter__inner b-ext-filter__inner_pad_5 b-layout">
            <div class="i-prompt">
                <div class="b-prompt b-prompt_left_-270 b-prompt_top_-55 b-prompt_width_200">
                    <div class="b-prompt__txt b-prompt__txt_color_fd6c30 b-prompt__txt_italic">”кажите свою специализацию, и мы покажем только подход€щие дл€ вас проекты.</div>
                    <div class="b-prompt__arrow b-prompt__arrow_3 b-prompt__arrow_left_80"></div>
                </div>
            </div>
            
            <table cellspacing="0" cellpadding="0" border="0" class="b-layout__table b-layout__table_width_full">
                <tbody>
                    <tr class="b-layout__tr">
                        <td class="b-layout__left b-layout__left_padright_10 b-layout__left_width_140">
                            <div class="b-combo b-combo_inline-block">
                                <div class="b-combo__input b-combo__input_width_125 b-combo__input_multi_dropdown b-combo__input_resize b-combo__input_arrow_yes b-combo__input_init_professionsList drop_down_default_<?= $subcategory ? (int)$subcategory : (int)$category?> multi_drop_down_default_column_<?= $subcategory ? "1" : "0"?>">
                                    <input type="text" value="<?= $category_name ? $category_name : "¬ыберите раздел";?>" size="80" name="category" class="b-combo__input-text b-combo__input-text_fontsize_15" id="category" onchange="search_project($('search-request').get('value'));">
                                    <label for="category" class="b-combo__label b-combo__label_fontsize_15"></label>
                                    <span id="category_arrow" class="b-combo__arrow"></span>
                                </div>
                            </div>
                        </td>
                        <td class="b-layout__right">
                            <div class="b-combo b-input-hint b-search">
                                <label class="b-input-hint__label b-input-hint__label_overflow_hidden" id="hint" for="search-request">‘ильтр по ключевым словам</label>
                                <div class="b-combo__input">
                                    <input id="search-request" class="b-combo__input-text" name="" size="80" type="text" onkeyup="search_project(this.value);" onblur="if(this.value.length == 0) $('hint').removeClass('b-input-hint__label_hide')">
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div><!-- b-ext-filter__body -->
    </div><!-- b-ext-filter -->
    
    <div id="project_content" class="b-layout__txt">
    <?php include ($_SERVER['DOCUMENT_ROOT']."/wizard/registration/steps/tpl.step.search.project.php"); ?>
    </div>
    <span id="project_loader_content" style="display:none"></span>
    
    <div class="b-fon b-fon_bg_8fd15f" id="project_search_hint">
        <div class="i-prompt">
            <div class="b-prompt b-prompt_width_470 b-prompt_center b-prompt_padbot_50">
                <div class="b-prompt__txt  b-prompt__txt_color_fd6c30 b-prompt__txt_italic">≈сли ни один проект вам не подошел, нажмите на кнопку</div>
                <div class="b-prompt__arrow b-prompt__arrow_5 b-prompt__arrow_right_70 b-prompt_top_35"></div>
            </div>
        </div>
        <input type="hidden" name="page" id="page-search" value="1"/>
        <div class="b-fon__body b-fon__body_pad_10 b-fon__body_center" id="load_project">
            <a href="javascript:void(0)" onclick="loading_projects($('page-search').get('value'))" class="b-button b-button_rectangle_color_transparent <?= ($count <= 3 ? "b-button_disabled" : "")?>">
                <span class="b-button__b1">
                    <span class="b-button__b2">
                        <span class="b-button__txt">«агрузить еще 10 проектов</span>
                    </span>
                </span>
            </a>									
        </div>
    </div>

    <div class="b-buttons b-buttons_padtop_40">
        <a href="/wizard/registration/?action=next&complited=1" class="b-button b-button_rectangle_color_green">
            <span class="b-button__b1">
                <span class="b-button__b2 b-button__b2_padlr_15">
                    <span class="b-button__txt">ѕродолжить</span>
                </span>
            </span>
        </a>&#160;&#160;
        <a href="/wizard/registration/?action=next&complited=1" class="b-buttons__link">пропустить этот шаг</a>
        <span class="b-buttons__txt">&#160;или&#160;</span>
        <a href="/wizard/registration/?action=exit" class="b-buttons__link b-buttons__link_color_c10601">выйти из мастера</a>
    </div>
    
</div>