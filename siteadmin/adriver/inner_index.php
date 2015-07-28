<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; } ?>
<h1>
    Справочник ключевых слов для настройки таргетинга в AdRiver
</h1>


<div class="b-layout">
    <table class="b-layout__table b-layout__table_width_full">
        <colgroup>
            <col>
            <col>
        </colgroup>
        <thead>
            <tr>
                <th class="b-layout__td b-layout__td_pad_10">
                    <strong>Тип</strong>
                </th>
                <th class="b-layout__td b-layout__td_center b-layout__td_pad_10">
                    <strong>Ключевое слово</strong>
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="b-layout__td b-layout__td_pad_10">

                    <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_10">
                        Каталог фрилансеров
                    </div>

                    <div class="b-combo b-combo_inline-block">
                      <div class="
                           b-combo__input 
                           b-combo__input_width_300 
                           b-combo__input_multi_dropdown 
                           b-combo__input_arrow_yes 
                           b-combo__input_init_professionsList 
                           show_all_records 
                           sort_cnt 
                           exclude_value_0_0
                           ">
                         <input id="fprofession" 
                                type="text" 
                                placeholder="Выберите специализацию" 
                                value="" 
                                name="fprofession" 
                                class="b-combo__input-text" />
                         <span class="b-combo__arrow"></span>
                      </div>
                    </div>
                    
                </td>
                <td class="b-layout__td b-layout__td_center b-layout__td_pad_10">
                    <div class="b-txt b-txt_padtop_30 b-txt_fs_14 b-txt_color_000 b-txt_bold">
                        <span id="fprofession_keyword"></span>
                    </div>
                </td>
            </tr>
            
            <tr>
                <td class="b-layout__td b-layout__td_pad_10">

                    <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_10">
                        Специализация пользователя
                    </div>

                    <div class="b-combo b-combo_inline-block">
                      <div class="
                           b-combo__input 
                           b-combo__input_width_300 
                           b-combo__input_multi_dropdown 
                           b-combo__input_arrow_yes 
                           b-combo__input_init_professionsList 
                           show_all_records 
                           sort_cnt 
                           exclude_value_0_0
                           ">
                         <input id="uprofession" 
                                type="text" 
                                placeholder="Выберите специализацию" 
                                value="" 
                                name="uprofession" 
                                class="b-combo__input-text" />
                         <span class="b-combo__arrow"></span>
                      </div>
                    </div>
                    
                </td>
                <td class="b-layout__td b-layout__td_center b-layout__td_pad_10">
                    <div class="b-txt b-txt_padtop_30 b-txt_fs_14 b-txt_color_000 b-txt_bold">
                        <span id="uprofession_keyword"></span>
                    </div>
                </td>
            </tr>            
            
            <tr>
                <td class="b-layout__td b-layout__td_pad_10">

                    <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_10">
                        Каталог услуг (ТУ)
                    </div>

                    <div class="b-combo b-combo_inline-block">
                      <div class="
                           b-combo__input 
                           b-combo__input_width_300 
                           b-combo__input_multi_dropdown 
                           b-combo__input_arrow_yes 
                           b-combo__input_on_load_request_id_gettucategories
                           show_all_records 
                           sort_cnt 
                           exclude_value_0_0
                           ">
                         <input id="tprofession" 
                                type="text" 
                                placeholder="Выберите специализацию" 
                                value="" 
                                name="tprofession" 
                                class="b-combo__input-text" />
                         <span class="b-combo__arrow"></span>
                      </div>
                    </div>
                    
                </td>
                <td class="b-layout__td b-layout__td_center b-layout__td_pad_10">
                    <div class="b-txt b-txt_padtop_30 b-txt_fs_14 b-txt_color_000 b-txt_bold">
                        <span id="tprofession_keyword"></span>
                    </div>
                </td>
            </tr>            
            
            <tr>
                <td class="b-layout__td b-layout__td_pad_10">
                    <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_10">
                        Фильтр списка проектов
                    </div>
                    <div class="b-combo b-combo_inline-block">
                      <div class="
                           b-combo__input 
                           b-combo__input_width_300 
                           b-combo__input_multi_dropdown 
                           b-combo__input_arrow_yes 
                           b-combo__input_init_professionsList
                           show_all_records 
                           sort_cnt 
                           exclude_value_0_0
                           ">
                         <input id="pprofession" 
                                type="text" 
                                placeholder="Выберите специализацию" 
                                value="" 
                                name="pprofession" 
                                class="b-combo__input-text" />
                         <span class="b-combo__arrow"></span>
                      </div>
                    </div>
                </td>
                <td class="b-layout__td b-layout__td_center b-layout__td_pad_10">
                    <div class="b-txt b-txt_padtop_30 b-txt_fs_14 b-txt_color_000 b-txt_bold">
                        <span id="pprofession_keyword"></span>
                    </div>
                </td>
            </tr>             
            
        </tbody>
    </table>
</div>