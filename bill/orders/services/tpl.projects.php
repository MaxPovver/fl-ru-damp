<div class="b-layout b-layout_pad_10 b-layout_bord_e6 b-layout_relative b-layout_margbot_10 b-promo__servis <?= $service['option'] == 'contest' ? "b-promo__servis_cont" : "b-promo__servis_prj"?> service" data-name="projects_<?= $service['id']?>" pro-discount="1" data-cost-pro="<?= round($service['pro_ammount'])?>" data-cost="<?= $service['ammount']?>">
    <a href="javascript:void(0)" class="b-button b-button_admin_del b-button_float_right service-remove"></a>
    <input type="hidden" name="opcode" value="<?= $service['op_code']; ?>" />
    <h3 class="b-layout__h3 b-layout__h3_padleft_70 b-layout__txt_padleft_null_iphone">
        <? 
        switch($service['option']) {
            case 'contest':
                ?>
                ѕубликаци€ конкурса &nbsp;&nbsp; 
                <span class="b-layout__txt b-layout__txt_fontsize_11 b-layouyt__txt_weight_normal"><a href="http://feedback.fl.ru/topic/397521-publikatsiya-konkursa-opisanie-instruktsiya-stoimost-perepiska-po-konkursu-obyavlenie-pobeditelej/" class="b-layout__link">ѕодробнее об услуге</a></span><input type="hidden" id="ammount_<?= $service['id']?>" name="ammount" value="<?= round(($bill->pro_exists_in_list_service? $service['pro_ammount']: $service['ammount'])/$service['op_count'])?>" /><input type="hidden" id="no_pro_ammount_<?= $service['id']?>" value="<?= $service['ammount'] ?>" />
                <?
                break;
            case 'office':
                ?>
                ѕубликаци€ вакансии &nbsp;&nbsp; 
                <span class="b-layout__txt b-layout__txt_fontsize_11 b-layouyt__txt_weight_normal"><a href="https://feedback.free-lance.ru/article/details/id/133" class="b-layout__link">ѕодробнее об услуге</a></span><input type="hidden" id="ammount_<?= $service['id']?>" name="ammount" value="<?= round(($bill->pro_exists_in_list_service? $service['pro_ammount']: $service['ammount'])/$service['op_count'])?>" /><input type="hidden" id="no_pro_ammount_<?= $service['id']?>" value="<?= $service['ammount'] ?>" />
                <?
                break;
            case 'logo':
                ?>
                «агрузка логотипа в проект или конкурс &nbsp;&nbsp;
                <span class="b-layout__txt b-layout__txt_fontsize_11 b-layouyt__txt_weight_normal"><a href="https://feedback.free-lance.ru/article/details/id/133" class="b-layout__link">ѕодробнее об услуге</a></span><input type="hidden" id="ammount_<?= $service['id']?>" name="ammount" value="<?= round(($bill->pro_exists_in_list_service? $service['pro_ammount']: $service['ammount'])/$service['op_count'])?>" /><input type="hidden" id="no_pro_ammount_<?= $service['id']?>" value="<?= $service['ammount'] ?>" />
                <?
                break;
            case 'urgent':
                ?>
                —рочный проект &nbsp;&nbsp;
                <span class="b-layout__txt b-layout__txt_fontsize_11 b-layouyt__txt_weight_normal"><a href="https://feedback.free-lance.ru/article/details/id/133" class="b-layout__link">ѕодробнее об услуге</a></span><input type="hidden" id="ammount_<?= $service['id']?>" name="ammount" value="<?= round(($bill->pro_exists_in_list_service? $service['pro_ammount']: $service['ammount'])/$service['op_count'])?>" /><input type="hidden" id="no_pro_ammount_<?= $service['id']?>" value="<?= $service['ammount'] ?>" />
                <?
                break;
            case 'hide':
                ?>
                —крытый проект &nbsp;&nbsp;
                <span class="b-layout__txt b-layout__txt_fontsize_11 b-layouyt__txt_weight_normal"><a href="https://feedback.free-lance.ru/article/details/id/133" class="b-layout__link">ѕодробнее об услуге</a></span><input type="hidden" id="ammount_<?= $service['id']?>" name="ammount" value="<?= round(($bill->pro_exists_in_list_service? $service['pro_ammount']: $service['ammount'])/$service['op_count'])?>" /><input type="hidden" id="no_pro_ammount_<?= $service['id']?>" value="<?= $service['ammount'] ?>" />
                <?
                break;
            case 'top':
                ?>
                «акрепление проекта или конкурса в списке на 
                <span class="i-shadow">
                    <a class="b-layout__link b-layout__link_inline-block b-layout__link_bold b-layout__link_fontsize_15 b-layout__link_ygol popup-top-mini-open upd-auto-period-data" href="javascript:void(0)" data-cancel-value="<?= $service['op_count']?>" data-service-id="<?= $service['id']?>" ><?= $service['op_count']?> <?= ending($service['op_count'], 'день', 'дн€', 'дней')?></a>
                    <div class="b-shadow b-shadow_m b-shadow_left_-11 b-shadow_top_25 b-shadow_hide b-shadow_width_380 popup-mini body-shadow-close change-select-period">
                        <div class="b-shadow__right">
                            <div class="b-shadow__left">
                                <div class="b-shadow__top">
                                    <div class="b-shadow__bottom">
                                        <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_10">
                                            <div class="b-layout__txt b-layout__txt_weight_normal">«аказать на 
                                                <div class="b-combo b-combo_inline-block b-combo_valign_mid">
                                                    <a class="b-button b-button_poll_plus b-button_absolute b-button_z-index_3 b-button_top_8 b-button_right_5 pay_place_item_plus" href="javascript:void(0)" onclick="getUpDay(<?= $service['id']?>, 1, this)"></a>
                                                    <a class="b-button b-button_poll_minus b-button_absolute b-button_z-index_3 b-button_top_8 b-button_left_5 pay_place_item_minus" href="javascript:void(0)" onclick="getDownDay(<?= $service['id']?>, 1, this)"></a>
                                                    <div class="b-combo__input b-combo__input_width_80 numeric">
                                                        <input type="hidden" id="ammount_<?= $service['id']?>" name="ammount" value="<?= round($service['ammount']/$service['op_count'])?>" />

                                                        <input type="hidden" id="pro_ammount_<?= $service['id']?>" name="pro_ammount" value="<?= round($service['pro_ammount']/$service['op_count'])?>" />
                                                        <input type="hidden" id="no_pro_ammount_<?= $service['id']?>" value="<?= round($service['ammount']/$service['op_count']) ?>" />
                                                        <input type="text" id="day_<?= $service['id']?>" value="<?= $service['op_count']?>" size="80" maxlength="2" onchange="recalc_projects(<?= $service['id']?>, this);" onkeyup="recalc_projects(<?= $service['id']?>, this);" class="b-combo__input-text b-combo__input-text_center b-combo__input-text_bold js-not_zero_numeric_input" name="days" data-input-type="projects">
                                                    </div>
                                                </div>
                                                <div class="b-layout__txt b-layout__txt_inline-block b-layout__txt_width_60 pay_place_item_day"><?= ending($service['op_count'], 'день', 'дн€', 'дней')?></div>
                                                <div class="b-layout__txt b-layout__txt_inline-block b-layout__txt_color_fd6c30 b-layout__txt_fontsize_15 b-layout__txt_bold">
                                                    <span class="pay_place_item_price" id="sum<?= $service['id']?>"><?= to_money(($bill->pro_exists_in_list_service? $service['pro_ammount']: $service['ammount']))?></span> руб
                                                </div>

                                                <div class="b-buttons b-buttons_padtop_15">
                                                    <a href="javascript:void(0)" class="b-button b-button_rectangle_color_green update-service-projects">
                                                        <span class="b-button__b1">
                                                            <span class="b-button__b2">
                                                                <span class="b-button__txt">»зменить</span>
                                                            </span>
                                                        </span>
                                                    </a>
                                                    <span class="b-buttons__txt"> &nbsp;&nbsp;или </span> <a class="b-buttons__link popup-top-mini-open" data-cancel-value="<?= $service['op_count']?>" data-service-id="<?= $service['id']?>" href="javascript:void(0)">закрыть, не измен€€</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <span class="b-shadow__icon b-shadow__icon_nosik b-shadow__icon_left_30"></span>
                    </div>
                </span> &nbsp;&nbsp;
                <span class="b-layout__txt b-layout__txt_fontsize_11 b-layouyt__txt_weight_normal"><a href="https://feedback.free-lance.ru/article/details/id/133" class="b-layout__link">ѕодробнее об услуге</a></span>
                <?
            default:
                break;
        }
        ?>
    </h3>
    <div class="b-layout__txt b-layout__txt_fontsize_15 b-layout__txt_padbot_15 b-layout__txt_padleft_70 b-layout__txt_padleft_null_iphone">
        <a class="b-layout__link" href="/public/?step=1&kind=<?= $service['info']['kind'];?>&<?= $service['parent_table'] == 'draft_projects' ? "draft_id=" : "public="?><?= $service['parent_id']?>&red="><?= $service['info']['name']?></a>
        
    </div>
    <div class="b-layout__txt b-layout__txt_fontsize_11 b-layout__txt_padbot_20 b-layout__txt_padleft_70 b-layout__txt_padleft_null_iphone">
        <? 
        switch($service['option']) {
            case 'contest':
                ?>
                 онкурс позвол€ет собрать не просто много, а очень много креативных и необычных решений вашей задачи. ¬ы публикуете конкурс и получаете свежие идеи и €ркие предложени€.
                <?
                break;
            case 'office':
                ?>
                ќтличный способ найти сотрудника на посто€нную работу. ќпишите услови€ сотрудничества, размер заработной платы, функциональные об€занности. » все Ч остаетс€ только ждать откликов от кандидатов.
                <?
                break;
            case 'logo':
                ?>
                ≈сли вам нужен насто€щий мастер своего дела, будьте готовы к прозрачному сотрудничеству. „ем больше данных о своей компании вы разместите, тем выше шансы найти опытного специалиста.
                <?
                break;
            case 'top':
                ?>
                ≈сли вам нужно привлечь внимание опытных фрилансеров к своему проекту или конкурсу и получить большое количество креативных идей и решений, закрепите его на главной странице сайта Ц там он получит тыс€чи просмотров.
                <?
            default:
                break;
        }
        ?>
    </div>
    <div class="b-layout__txt b-layout__txt_padleft_70 b-layout__txt_fontsize_22 b-layout__txt_color_fd6c30 b-layout__txt_padleft_null_iphone"><span class="upd-cost-sum"><?= $bill->pro_exists_in_list_service ? ( $service['pro_ammount'] <=0 ? "Ѕесплатно (дл€ пользовател€ аккаунта PRO)" : to_money($service['pro_ammount']) ): to_money($service['ammount'])?></span> <span class="sum-currency"><?= $bill->pro_exists_in_list_service && $service['pro_ammount'] <=0 ? "" : "руб."?></span></div>
</div>