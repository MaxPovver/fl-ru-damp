<?php
if(!defined('IN_STDF')) {
	header("HTTP/1.0 404 Not Found");
	exit;
}

/**
 * Представление для виджета TServiceCatalog
 *
 *
 * @var TServiceCatalog $this
 * @var array $categoriesTree
 */
?>

<div class="b-catalog b-fon b-fon_bg_74bb54">
	<b class="b-fon__b1"></b>
	<b class="b-fon__b2"></b>
	<div class="b-fon__body">
		<ul class="b-catalog__list " id="accordion">
			<?php foreach($categoriesTree as $i => $parentCategory) { ?>
				<li class="b-catalog__item b-catalog__item_bg_f5">
					<div class="b-catalog__item-inner b-catalog__item-inner_pad_3_10_0">

						<?php if (!is_null($parentCategory['category_id'])) { ?>
							<?php if (trim($parentCategory['category_link'])) { ?>
								<a class="toggler b-catalog__link b-catalog__link_color_000
										b-catalog__link_padbot_7" href="/tu/<?=trim($parentCategory['category_link'])?>"><?=$parentCategory['category_title']?></a>
							<?php } else { ?>
								<a class="toggler b-catalog__link b-catalog__link_color_000 b-catalog__link_padbot_7" href="javascript:void(null);"><?=$parentCategory['category_title']?></a>
							<?php } ?>
						<?php } ?>

						<?php if (count($parentCategory['children'])>0) { ?>
							<ul id="submenu<?=$i?>" class="element b-catalog__inner-list" <?php if (is_null($parentCategory['category_id'])) { ?>style="display: block !important;"<?php } ?>>
								<?php foreach($parentCategory['children'] as $j => $childCategory) { ?>
									<li class="b-catalog__item b-catalog__item_bordtop_color_e7e6e5">
										<!-- span class="b-catalog__number">
											<script type="text/javascript">document.write('<?= $childCategory['category_count'] ?>');</script>
										</span -->
										<a href="/tu/<?=trim($childCategory['category_link'])?>/" class="b-catalog__link b-catalog__link_color_000"><?= $childCategory['category_title']?></a>
									</li>
								<?php } // foreach($parentCategory['children'] as $j => $childCategory) ?>
							</ul> <!-- / <ul id="submenuN" class="element b-catalog__inner-list"> -->
						<?php } // if ($parentCategory['children']) ?>
					</div>
				</li>
			<?php } // foreach($categories as $i => $category) ?>
		</ul> <!-- / <ul class="b-catalog__list " id="accordion"> -->
	</div>
	<b class="b-fon__b2 b-fon__b2_clear_left"></b>
	<b class="b-fon__b1"></b>
</div><!-- b-catalog -->

