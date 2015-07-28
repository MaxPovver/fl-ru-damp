<?php
if(!defined('IN_STDF')) {
	header("HTTP/1.0 404 Not Found");
	exit;
}

/**
 * Представление для виджета TServiceCatalogCategories
 *
 *
 * @var TServiceCatalogCategories $this
 * @var array $categories
 */
?>

<?php if (count($categories)): ?>  
<div class="b-cat_outer">    
    <div class="b-cat">
        <?php foreach ($categories as $category): ?>
            <div class="b-cat__item">
                <a class="b-cat__link" href="/tu/<?= $category['link'].$get_params ?>"><?= $category['title'] ?></a>
            </div>
        <?php endforeach; ?>
    </div>
</div>    
<?php endif; ?>

