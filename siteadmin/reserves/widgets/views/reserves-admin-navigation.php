<?php
    $cnt = count($menu_items);
    $idx = 0;
?>
    <div class="b-menu b-menu_tabs">
        <?php if ($menu_items): ?>
        <ul class="b-menu__list b-menu__list_padleft_10">
            <?php foreach($menu_items as $action => $item): ?>
            <li class="b-menu__item
                <?php if($current_action == $action):?> b-menu__item_active<?php endif; ?>
                <?php if($cnt == ++$idx): ?> b-menu__item_last<?php endif; ?>">
                <a class="b-menu__link" href="<?=$item['url']?>">
                    <span class="b-menu__b1">
                        <?=$item['title']?>
                    </span>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>