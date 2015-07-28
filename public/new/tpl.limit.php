<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/professions.common.php");
$xajax->printJavascript('/xajax/');

?>
<table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">

    <tr class="b-layout__tr">
        <td class="b-layout__td">

            <h1 id="project_title" class="b-page__title">
                Новый проект
            </h1>

            <div class="b-layout__txt b-layout__txt_padbot_20">
                Вы уже исчерпали лимит по количеству публикуемых проектов в сутки.<br/>
                Очередной проект вы можете опубликовать сейчас (<a href="/payed-emp/">купив аккаунт ПРО</a>) или через <?php echo $last_prj_date ?>.
            </div>
            
            <a title="Купить PRO" href="/payed/"  class="b-button b-button_flat b-button_flat_green">
                Купить PRO
            </a>
            
        </td>
        <td class="b-layout__td b-layout__td_width_340 b-layout__td_padleft_20 b-layout__td_padtop_10">
           <div class="b-layout__title">Вы также можете</div>
           <? if ($project['kind'] == 7) { ?>
           <div class="b-layout b-layout_pad_20 b-layout_2bord_e6 b-layout_bordrad_3 b-layout_margbot_10">
              <table class="b-layout__table b-layout__table_width_full">
                 <tr class="b-layout__tr">
                    <td class="b-layout__td b-layout__td_width_60 b-layout__td_center"><img class="b-pic" src="/images/project-logo.png" width="46" height="62"></td>
                    <td class="b-layout__td b-layout__td_padleft_20">
                       <div class="b-layout__txt b-layout__txt_padbot_20">Добавьте проект, если вам нужен онлайн-исполнитель для разового выполнения какой-либо работы по вашим задачам.</div>
                       <a class="b-button b-button_flat b-button_flat_green" href="?step=1&kind=1">Добавить проект</a>
                    </td>
                 </tr>
              </table>
           </div>
           <? } else {//if?>
           <div class="b-layout b-layout_pad_20 b-layout_2bord_e6 b-layout_bordrad_3 b-layout_margbot_10">
              <table class="b-layout__table b-layout__table_width_full">
                 <tr class="b-layout__tr">
                    <td class="b-layout__td b-layout__td_width_60 b-layout__td_center"><img class="b-pic" src="/images/contest-logo.png" width="60" height="58"></td>
                    <td class="b-layout__td b-layout__td_padleft_20">
                       <div class="b-layout__txt b-layout__txt_padbot_20">Если нет времени на поиск исполнителя - устройте конкурс, чтобы выбрать лучший вариант работы из числа представленных.</div>
                       <a class="b-button b-button_flat b-button_flat_green" href="?step=1&kind=7">Устроить конкурс</a>
                    </td>
                 </tr>
              </table>
           </div>
           <?php } ?>
           <? if ($project['kind'] == 4) { ?>
           <div class="b-layout b-layout_pad_20 b-layout_2bord_e6 b-layout_bordrad_3 b-layout_margbot_10">
              <table class="b-layout__table b-layout__table_width_full">
                 <tr class="b-layout__tr">
                    <td class="b-layout__td b-layout__td_width_60 b-layout__td_center"><img class="b-pic" src="/images/project-logo.png" width="46" height="62"></td>
                    <td class="b-layout__td b-layout__td_padleft_20">
                       <div class="b-layout__txt b-layout__txt_padbot_20">Добавьте проект, если вам нужен онлайн-исполнитель для разового выполнения какой-либо работы по вашим задачам.</div>
                       <a class="b-button b-button_flat b-button_flat_green" href="?step=1&kind=1">Добавить проект</a>
                    </td>
                 </tr>
              </table>
           </div>
           <? } else {//if?>
           <div class="b-layout b-layout_pad_20 b-layout_2bord_e6 b-layout_bordrad_3">
              <table class="b-layout__table b-layout__table_width_full">
                 <tr class="b-layout__tr">
                    <td class="b-layout__td b-layout__td_width_60 b-layout__td_center"><img class="b-pic" src="/images/vacancy-logo.png" width="52" height="60"></td>
                    <td class="b-layout__td b-layout__td_padleft_20">
                       <div class="b-layout__txt b-layout__txt_padbot_20">Разместите вакансию  с поиском исполнителя в офис, если вам нужен сотрудник в компанию на постоянную офисную работу.</div>
                       <a class="b-button b-button_flat b-button_flat_green" href="?step=1&kind=4">Разместить вакансию</a>
                    </td>
                 </tr>
              </table>
           </div>
           <?php } ?>
        </td>
     </tr>
 </table>   