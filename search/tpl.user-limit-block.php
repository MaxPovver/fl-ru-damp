<div class="take-num">
    <?= $bottomLimitBlock ? '<div class="b-layout__txt b-layout__txt_fontsize_11">' : '' ?>
        Выводить по <select name="limit_page" onchange="addUserLimit('<?= $query_string?>', this.value, <?= $is_search!='' || $top_projects_cnt?1:0?>)">
            <?php foreach($user_limit_array as $i=>$val) {?>
            <option value="<?=$i?>" <?= ($userLimit == $i?'selected="selected"':'')?>><?=$val?></option>
            <?php } //foreach?>
        </select> записей
    <?= $bottomLimitBlock ? '</div>' : ''?>
</div>  