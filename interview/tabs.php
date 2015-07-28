<div class="b-menu b-menu_tabs">
    <ul class="b-menu__list b-menu__list_overflow_hidden b-menu__list_padleft_20">
        <li class="b-menu__item">
						<a class="b-menu__link" href="/articles/" title="Статьи">
								<span class="b-menu__b1">Статьи</span>
						</a>
				</li>
        <? if($articles_unpub) { ?>
        <li class="b-menu__item">
						<a class="b-menu__link" href="/articles/?page=unpublished" title="На модерации">
								<span class="b-menu__b1">На модерации</span>
						</a>
				</li>
        <? } ?>
        <li class="b-menu__item b-menu__item_last b-menu__item_active"><a class="b-menu__link" href="/interview/" title="Интервью"><span class="b-menu__b1">Интервью</span></a></li>
    </ul>
</div>
