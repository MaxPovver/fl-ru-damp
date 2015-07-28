<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/bill.common.php");
$xajax->printJavascript('/xajax/');
?>

    <h1 class="b-page__title">Управление услугами</h1>
    <div class="b-layout__one b-layout__one_width_25ps b-layout__one_padbot_30 b-layout__right_float_right b-layout__one_width_full_ipad b-layout_padbot_10_ipad">
       <?php include($_SERVER['DOCUMENT_ROOT'] . "/bill/widget/tpl.score.php"); ?>
    </div>
    <div id="services-list" class="b-layout__one b-layout__one_float_left b-layout__one_width_72ps b-layout__one_width_full_ipad">
        <?php include($_SERVER['DOCUMENT_ROOT'] . "/bill/tpl.head_menu.php"); ?>
        
        <?php 
        
        if( !empty($bill->list_types_services['active']) ) {
            $is_active = true;
            ?><h2 class="b-layout__title">Подключенные</h2><?
             
            foreach($bill->list_types_services['active'] as $service) {
                include ($_SERVER['DOCUMENT_ROOT'] . "/bill/services/" . billing::getTemplateByService($service['service']));
            }
        }//if
        
        if( !empty($bill->list_types_services['lately']) ) { 
            $is_lately = true;
            ?><h2 class="b-layout__title <?= !$is_active?"":"b-layout__title_padtop_50"?>">Вы недавно покупали</h2><?
            
            foreach($bill->list_types_services['lately'] as $service) {
                include ($_SERVER['DOCUMENT_ROOT'] . "/bill/services/" . billing::getTemplateByService($service['service']));
            }
        }//if
        
        if( !empty($bill->list_types_services['notused']) ) {
            $notusedText = (empty($bill->list_types_services['active']) && empty($bill->list_types_services['lately'])) ? 'Вы можете заказать' : 'Вы также можете заказать'
            ?><h2 class="b-layout__title <?= !($is_active||$is_lately)?"":"b-layout__title_padtop_50"?>"><?= $notusedText ?></h2><?

            foreach($bill->list_types_services['notused'] as $service) {
                include ($_SERVER['DOCUMENT_ROOT'] . "/bill/services/" . billing::getTemplateByService($service['service']));
            }
        }//if
        ?>

        <span id="wallet">
        <?php
        $popup_content   = $_SERVER['DOCUMENT_ROOT'] . "/bill/widget/popups/popup.wallet.php";
        include ( $_SERVER['DOCUMENT_ROOT'] . "/bill/widget/tpl.popup.php" );
        ?>
        </span>
    </div>
    
<div class="b-layout__one b-layout__one_width_25ps b-layout__one_float_left b-layout__one_margleft_3ps b-layout__one_width_full_ipad">
    <?php include($_SERVER['DOCUMENT_ROOT'] . "/bill/widget/tpl.right_column.php"); ?>
</div>
<input type="hidden" name="tr_id" id="tr_id" value="" />
<script>
    var orders = new Services();
</script>
<style type="text/css">
@media screen and (max-width: 700px){
.b-shadow_width_540{
	width:80%;
	min-width:300px;
	}
.b-shadow__title .i-shadow{ display:block;}
}
</style>

