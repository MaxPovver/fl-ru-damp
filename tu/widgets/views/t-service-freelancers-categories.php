<?php
if(!defined('IN_STDF')) {
	header("HTTP/1.0 404 Not Found");
	exit;
}

/**
 * Представление для виджета TServiceFreelancersCategories
 *
 *
 * @var TServiceFreelancersCategories $this
 * @var array $profs
 */

if(isset($profs) && count($profs)) 
{ 

?>
<div id="specialis" class="b-layout b-layout_clear_both b-layout_top_40 b-layout_overflow_hidden b-page__desktop">
    <?php $groups_repeat = array(); ?> 
        <h2 class="b-layout__title">Фрилансеры по специализациям</h2>
        <div class="b-layout b-layout_col_4 b-layout_col_2_ipad b-layout_col_1_iphone">
        <?php foreach ($profs as $prof): ?>
            <?php if (!isset($groups_repeat[$prof['grouplink']]) && ($groups_repeat[$prof['grouplink']] = 1)): ?>
                    <div class="b-layout__txt b-layout__txt_inline-block"><a class="b-layout__link b-layout__link_fontsize_11" href="/freelancers/<?=$prof['grouplink']?>"><?=$prof['groupname']?></a></div><br>
            <?php endif; ?>
        <?php endforeach; ?>
        </div>
    <?php /*
    <h2 class="b-layout__title">
        Фрилансеры по специализациям
    </h2>
    <?php foreach($categories as $i => $parentCategory) { 
            if(!$parentCategory['link']) continue;
    ?>
    <div class="b-layout__txt b-layout__txt_width_24ps b-layout__txt_inline-block b-layout__txt_valign_top">
        <a class="b-layout__link b-layout__link_fontsize_11" href="<?php echo tservices_helper::category_url($parentCategory['link']); ?>">
            <?php echo $parentCategory['title'] ?>
        </a>
    </div>
    <?php } ?>
	*/ ?>
</div>
<?php
}