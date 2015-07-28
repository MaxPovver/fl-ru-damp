<div class="b-layout__txt b-layout__txt_padbot_10"><a class="b-layout__link b-layout__link_bold b-layout__link_color_000" href="/drafts/?p=projects">Черновики</a></div>
<? foreach($drafts as $draft) { ?>
    <div class="b-layout__txt b-layout__txt_padbot_10 b-layout__txt_width_225 b-layout__txt_break-word">
        <a class="b-layout__link" href="/public/?step=1&kind=<?= $draft['kind'] ?>&draft_id=<?= $draft['id'] ?>"><?= str_replace(array("<", ">"), array('&lt;', '&gt;'), $draft['name']) ?></a>
    </div>
<? } ?>
<? if ($moreDraftsCount > 0) { ?>
<div class="b-layout__txt b-layout__txt_fontsize_11">И <a class="b-layout__link b-layout__link_fontsize_11" href="/drafts/?p=projects">еще <?= $moreDraftsCount ?></a></div>
<? } ?>