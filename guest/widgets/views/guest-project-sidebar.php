<div class="b-layout b-layout_margbot_30">
    <table class="b-layout__table b-layout__table_width_full">
        <tr class="b-layout__tr">
            <td class="b-layout__td b-layout__td_width_60 b-layout__td_center">
                <img width="45" height="44" src="<?=WDCPREFIX?>/images/contest-logo.png" class="b-pic"/>
            </td>
            <td class="b-layout__td b-layout__td_padleft_10">
                <div class="b-layout__txt b-layout__txt_padbot_20 b-layout__txt_fontsize_15">
                    Для выбора лучшего<br/>
                    результата из нескольких
                </div>
                <a href="/public/?step=1&kind=7" class="b-button b-button_flat b-button_flat_green b-button_nowrap">
                    Устройте конкурс
                </a>
            </td>
        </tr>
    </table>
</div>
<?php if($is_project):?>
<div class="b-layout">
    <table class="b-layout__table b-layout__table_width_full">
        <tbody><tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_width_60 b-layout__td_center">
                    <img width="45" height="52" src="<?=WDCPREFIX?>/images/vacancy-logo.png" class="b-pic"/>
                </td>
                <td class="b-layout__td b-layout__td_padleft_10">
                    <div class="b-layout__txt b-layout__txt_padbot_20 b-layout__txt_fontsize_15">
                        Для найма Исполнителя<br/>
                        на регулярную работу
                    </div>
                    <a href="/guest/new/vacancy/" class="b-button b-button_flat b-button_flat_green b-button_nowrap">
                        Разместите вакансию
                    </a>
                </td>
            </tr>
        </tbody>
    </table>
</div>
<?php else: ?>
<div class="b-layout">
    <table class="b-layout__table b-layout__table_width_full">
        <tbody><tr class="b-layout__tr">
                <td class="b-layout__td b-layout__td_width_60 b-layout__td_center">
                    <img width="33" height="45" src="<?=WDCPREFIX?>/images/project-logo.png" class="b-pic"/>
                </td>
                <td class="b-layout__td b-layout__td_padleft_10">
                    <div class="b-layout__txt b-layout__txt_padbot_20 b-layout__txt_fontsize_15">
                        Для поиска онлайн-исполнителя на разовую работу
                    </div>
                    <a href="/guest/new/project/" class="b-button b-button_flat b-button_flat_green b-button_nowrap">
                        Добавить проект
                    </a>
                </td>
            </tr>
        </tbody>
    </table>
</div>
<?php endif; ?>