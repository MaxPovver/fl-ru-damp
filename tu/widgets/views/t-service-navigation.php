<?php if ($is_crumbs) { ?>
    <div class="b-menu b-menu_crumbs">
        <ul class="b-menu__list">
            <li class="b-menu__item"><a href="/tu/" class="b-menu__link">Все услуги фрилансеров</a>&nbsp;&rarr;&nbsp;</li>            

            <?php if(!$cur_cat) { ?>
            <li class="b-menu__item"><?= $cur_cat_group['title'] ?></li>
            <?php }else { ?>
            <li class="b-menu__item"><a href="/tu/<?= $cur_cat_group['link'].$get_params ?>" class="b-menu__link"><?= $cur_cat_group['title'] ?></a>&nbsp;&rarr;&nbsp;</li>
            <li class="b-menu__item"><?= $cur_cat['title'] ?></li>
            <?php } ?>
        </ul>
    </div>
<?php } ?>