<?php /* if($sbr->isEmp()) {?>
<a href="/<?= sbr::NEW_TEMPLATE_SBR; ?>/?site=<?= $projects_cnt['open'] == 0 ? 'create' : 'new';?>" class="b-button b-button_flat b-button_flat_green b-button_float_right">Начать новую сделку</a>
<?php }//if */?>

<h1 class="b-page__title">«Мои Сделки»</h1>

<?php 

// Подключаем окно помощи в Этапе СБР
include ($_SERVER['DOCUMENT_ROOT'] . "/sbr/tpl.help.php");
// Меню 
include ($_SERVER['DOCUMENT_ROOT'] . "/sbr/tpl.header.php");

?>

<? foreach($sbr_drafts as $sbr_id => $curr_sbr) { ?>
<div class="b-fon b-fon_padbot_20 b-fon_width_full " id="draftsbr_<?= $sbr_id; ?>">
    <div class="b-fon__body b-fon__body_fontsize_13 b-fon__body_bg_fff">
        <table cellspacing="0" cellpadding="0" border="0" class="b-layout__table b-layout__table_width_full">
            <tbody>
                <tr class="b-layout__tr">
                    <td class="b-layout__left b-layout__left_pad_5_15 b-layout__hover_bg_f2f4f5">
                        <span class="i-button"><a class="b-button b-button_admin_del b-button_float_right b-button_margtop_7" href="javascript:void(0)" onclick="if(confirm('Вы хотите удалить черновик?')) xajax_deleteDraftSbr(<?= $sbr_id;?>);"></a></span>
                        <div class="b-layout__txt b-layout__txt_padtop_5 b-layout__txt_padright_15 b-layout__txt_float_right"><?= date('d.m.Y в H:i', strtotime($curr_sbr->data['posted']))?></div>
                        <div class="b-layout__txt b-layout__txt_fontsize_22 b-layout__txt_margright_150"><a class="b-layout__link" href="?site=edit&id=<?=$sbr_id?>"><?=reformat($curr_sbr->data['name'],38,0,1)?></a></div>
                    </td>
                    <td class="b-layout__right b-layout__right_width_400"></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<? } //foreach?>
