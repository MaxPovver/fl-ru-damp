<?
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/sbr.common.php");
$xajax->printJavascript('/xajax/');
?>

<script type="text/javascript">
window.addEvent('domready', function() { 
    <? if($sbr_id && $site == '') { ?>
        var anchor_sbr = <?= intval($sbr_id)?>;
        
        if($('sbrList'+anchor_sbr)) {
            JSScroll($('sbrList'+anchor_sbr));
            //new Fx.Scroll(window, {duration : 0}).toElement($('sbrList'+anchor_sbr));
            return true;
        } else {
            xajax_loadSbr(anchor_sbr);
        } 
    <? }//else?>
});
</script>
<? if(!file_exists($_SERVER['DOCUMENT_ROOT'] . "/sbr/".$inner)) {
    header_location_exit("/404.php", 1);
} //?>
<? include ($_SERVER['DOCUMENT_ROOT'] . "/sbr/".$inner); ?>