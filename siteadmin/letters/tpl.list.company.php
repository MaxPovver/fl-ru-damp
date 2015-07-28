<?php
if($company['frm_type']) {
	$company['name'] = $company['frm_type'].' "'.$company['name'].'"';
}
?>
<div class="b-layout__txt b-layout__txt_padbot_15">
	<a class="b-layout__link b-layout__link_bold b-layout__link_fontsize_15" href="/siteadmin/letters/?mode=edit&id=<?=$company['id']?>"><?=reformat(htmlspecialchars($company['name']),100)?></a>
	&nbsp;&nbsp;&nbsp; 
	<?=reformat(htmlspecialchars("{$company['country_title']}, {$company['index']}, {$company['city_title']}, {$company['address']}"),100)?>
</div>