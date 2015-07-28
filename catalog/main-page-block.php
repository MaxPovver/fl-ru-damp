<div class="b-menu b-menu_seo b-menu_padtop_20 b-menu_padbot_20 b-menu_bordtop_b2">
	<div class="b-menu__inner">
		<h3 class="b-menu__h4 b-menu__h4_padbot_10">О разделах каталога фрилансеров:</h3>
		<table class="b-menu__table b-menu__table_width_full" cellpadding="0" cellspacing="0" border="0">
			<tbody>
			<?php
			$n=1;
			foreach($seo_catalog_data as $seo_direct) {
				if($n==1) {
					echo '<tr class="b-menu__tr">';
				}
				echo '<td class="b-menu__td b-menu__td_padright_20 b-menu__td_padbot_10"><a class="b-menu__link b-menu__link_fontsize_11" href="'.seo::getFriendlyURL('',$seo_direct['name_section_link'],'').'">'.reformat($seo_direct['dir_name']).'</a></td>';
				if($n==8) {
					echo '</tr>';
					$n=1;
				} else {
					$n++;
				}
			}
			if($n!=1) {
				for($i=$n; $i<=8; $i++) {
					echo '<td class="b-menu__td b-menu__td_padright_20 b-menu__td_padbot_10">&nbsp;</td>';
				}
				echo '</tr>';
			}
			?>
			</tbody>
		</table>
	</div>
</div>