<?
$crumbs = array(
    array(
        'href' => '/' . sbr::NEW_TEMPLATE_SBR . '/',
        'name' => '«Мои Сделки»'
    ),
    array(
        'href' => '/' . sbr::NEW_TEMPLATE_SBR . '/?id=' . $sbr->id,
        'name' => reformat2($sbr->data['name'])
    ),
    array(
        'href' => '',
        'name' => 'Резервирование денег'
    )
);

include($_SERVER['DOCUMENT_ROOT'] . "/sbr/tpl.sbr-crumbs.php");
include($_SERVER['DOCUMENT_ROOT'] . "/sbr/tpl.stage-user.php");
?>

<table class="b-layout__table b-layout__table_width_full" cellspacing="0" cellpadding="0" border="0">
    <tbody><tr class="b-layout__tr">
            <td class="b-layout__left b-layout__left_width_72ps"><div class="b-fon b-fon_width_full">
                    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_padleft_30 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf">
                        <div class="b-layout__txt b-layout__txt_padbot_10"><span class="b-icon b-icon_sbr_gok b-icon_margleft_-25"></span>Деньги успешно зарезервированы. Исполнитель получит уведомление.<br /> 
                            Перейти <a class="b-fon__link" href="<?= '/' . sbr::NEW_TEMPLATE_SBR . '/?site=Stage&id=' . $sbr->stages[0]->id ?>"><?= (count($sbr->stages) == 1 ? "на страницу сделки" : "в первый этап сделки") ?></a>.</div>
                        <div class="b-layout__txt">Если у вас возникнут вопросы, обращайтесь в <a class="b-layout__link" href="/about/feedback/">службу поддержки</a> или к <?= webim_button(2, 'онлайн-консультанту', 'b-layout__link') ?>.</div>
                    </div>
                </div></td>
            <td class="b-layout__right"></td>
        </tr>
    </tbody>
</table>