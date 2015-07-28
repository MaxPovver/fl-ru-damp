<?
ob_start();
if(get_uid()):?>

<script>
/*function InitHideFav() {
	HideFavFloat(0,0);
	HideFavOrderFloat(currentOrderStr);
}

document.body.onclick = InitHideFav;*/
</script>
<?endif;?>
<?include(strtolower($commune_output));
$str = ob_get_clean();
print($str);
?>

<? /*if($site=='Topic' && $draft_id) { ?>
<script type="text/javascript">
eval($('c_edit_lnk').get('onclick'));
</script>
<? }*/?>
