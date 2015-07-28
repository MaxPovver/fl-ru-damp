<h2 class="b-page__title b-page__title_padnull">Направления деятельности фрилансеров</h2>
<form id="region_frm" action="" method="get">
	<div class="b-select b-select_padbot_20 b-select_padtop_10">
		<script type="text/javascript">var host = '<?=preg_replace('~^'.HTTP_PREFIX.'(www\.)?~', '', $host)?>'; var allHost = '<?=(preg_match('~'.HTTP_PREFIX.'www\.~', $host)? 'www.': '')?>' + host; </script>
		<label class="b-select__label" for="region">Регион:</label>
		<select id="region" class="b-select__select b-select__select_width_140" name="subdomain" onchange="if($('region').get('value') != '') { if($('region').get('value') == 'all') { url = '<?=HTTP_PREFIX?>'+allHost+'/catalog/'; } else { url = '<?=HTTP_PREFIX?>'+$('region').get('value')+'.'+host+'/catalog/'; }; window.location = url; }">
				<option value="all" <?=(($seo->subdomain['id'] == -1)?'selected="selected"':'')?>>Все</option>
				<?php
				foreach($countries as $country) {
					$country_options = "<option value=''>{$country['country_name']}</option>";
						$n = 0;
						foreach($subdomains as $key=>$row) {
								if($row['country_id']!=$country['id']) continue;
								$country_options .= "<option value='{$row['subdomain']}' ".(($seo->subdomain['id'] == $row['id'])?'selected="selected"':'').">&nbsp;&nbsp;{$row['name_subdomain']}</option>";
								$n = 1;
						}
						if($n) {
								$subdomain_options .= $country_options;
						}
				}
				?>
				<?=$subdomain_options?>
		</select>
	</div>
</form>



<div class="b-page__txt b-page__txt_fontsize_13"><p>Нередко работодатели испытывают трудности при поиске грамотных специалистов в той или иной области из-за того, что недостаточно осведомлены о специфике работы фрилансеров. Обладая дополнительной информацией, выбрать подходящего исполнителя для своих проектов намного легче.</p><p>&nbsp;</p><p>В данном разделе собраны статьи, касающиеся различных направлений деятельности удаленных специалистов. Здесь вы можете узнать самое свежее и новое о фрилансерах и рынке фри-ланса в целом.</p><p>&nbsp;</p><p>Развивайтесь вместе с Free-lance.ru!</p></div>

<div class="b-layout b-layout_padtop_20 b-layout_padbot_20">
	<table class="b-layout__table b-layout__table_width_full">
		<tbody>
			<?php
			$n = 1;
			foreach($sections as $section) {
				if(!$section['subsection']) { continue; }
				
				if($n==1) {
					echo '<tr class="b-layout__tr">';
				}

				echo '<td class="b-layout__one '.($n==4 ? '' : 'b-layout__one_padright_20').' b-layout__one_width_20ps">';
				echo '<h3 class="b-layout__h3">'.$section['name_section'].'</h3>';
				$len = (count($section['subsection'])>5 ? 5 : count($section['subsection']));
				for($i=0; $i<$len; $i++) {
					$item = $section['subsection'][$i];
					echo '<div class="b-layout__txt b-layout__txt_padbot_5">— <a class="b-layout__link" href="'.seo::getFriendlyURL($subdomains[$item['subdomain_id']]['subdomain'], $directions[$item['direct_id']]['name_section_link'], $item['name_section_link'], $section['name_section_link']).'">'.$item['name_section'].'</a></div>';
				}
				if($len==5) {
					echo '<div class="b-layout__txt b-layout__txt_padleft_15 b-layout__txt_padbot_40"><a class="b-layout__link b-layout__link_bordbot_dot_0f71c8" href="'.seo::getFriendlyURL($seo->subdomain['subdomain'], $directions[$section['direct_id']]['name_section_link'], '', $section['name_section_link']).'">Остальные статьи</a></div>';
				} else {
					echo '<div class="b-layout__txt b-layout__txt_padleft_15 b-layout__txt_padbot_40">&nbsp;</div>';					
				}
				echo '</td>';
				if($n==4) {
					echo '</tr>';
					$n=1;
				} else {
					$n++;
				}
			}
			if($n!=1) {
				for($i=$n; $i<=4; $i++) {
					echo '<td class="b-layout__one">&nbsp;</td>';

				}
				echo '</tr>';
			}
			?>
	</tbody></table>
</div>