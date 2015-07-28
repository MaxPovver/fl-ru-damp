<?php if ($activ_tab == 5) { ?>
    <div id="op_header"> 
        <a name="op_head"></a>
        <div class="ops-bar">
            <ul>
                <?= opinions::view_op_nav_bar($user, __paramInit('string', 'sort', null), $period) ?>
            </ul>
        </div>
    </div>
    <h2 class="b-layout__title b-layout__title_padtop_10 b-layout__title_padleft_15"><?= $opCount . ending($opCount, ' отзыв', ' отзыва', ' отзывов') . ' ' . mb_strtolower($author_filter_string) . ' ' . mb_strtolower($filter_string) ?></h2>
<? } ?>
