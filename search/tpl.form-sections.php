<div class="b-menu b-menu_tabs b-menu_padtop_20">
    <ul class="b-menu__list b-menu__list_padleft_10"  data-menu-descriptor="search-nav" data-menu="true">
<? // последний элемент массива
$last_elem = end($search_tabs);
$last_name = $last_elem['name'];
?>
 <?php foreach($search_tabs as $key=>$tab) { ?>
 <? if(($tab['search'] == "messages" || $tab['search'] == "notes") && !get_uid()) continue;?>
        <? $style = "";$dataatr = "";
        if ($tab['active']) {
            $style = "b-menu__item_active";
			$dataatr="data-menu-opener='true' data-menu-descriptor='search-nav'";
        } 
        if ($tab['name'] === $last_name) { 
            $style .= " b-menu__item_last";
        } ?>
        <li class="b-menu__item <?= $style ?>" <?= $dataatr ?>><a class="b-menu__link" href="/search/?type=<?= $tab['search']?>&<?= $query_string_menu?>&only_tab" title="<?= $tab['name']?>"><span class="b-menu__b1"><?= $tab['name']?> <span class="b-menu__digit"><?= $_SESSION['search_elms'][$tab['search']]?></span></span></a></li>
    <?php } //foreach?>
    </ul>
</div>   

<? 
// Нужно последнему пункту li добавить класс b-menu__item_last
?>

