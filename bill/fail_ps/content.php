<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/bill.common.php");
$xajax->printJavascript('/xajax/');
?>
<a name="top"></a>
<div class="b-layout b-layout__page" style="margin-top:0px">
    <div class="body">
        <div class="main"> <a name="top"></a>
            <div class="b-layout b-layout__page">
                <div class="b-menu b-menu_crumbs">
                    <ul class="b-menu__list">
                        <li class="b-menu__item"><a class="b-menu__link" href="/bill/">Мои услуги</a>&nbsp;&rarr;&nbsp;</li>
                    </ul>
                </div>
                <h1 class="b-page__title">Подключение платежной системы</h1>

                <? include($_SERVER['DOCUMENT_ROOT'] . "/bill/widget/tpl.right_column.php"); ?>

                <div class="b-layout__one b-layout__one_width_72ps">
                    <h2 class="b-layout__title">Ошибка</h2>
                    <div class="b-layout__txt b-layout__txt_fontsize_15"><?= $error; ?></div>
                    <span id="wallet">
                    <?php
                        $popup_content   = $_SERVER['DOCUMENT_ROOT'] . "/bill/widget/popups/popup.wallet.php";
                        include ( $_SERVER['DOCUMENT_ROOT'] . "/bill/widget/tpl.popup.php" );
                        ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>