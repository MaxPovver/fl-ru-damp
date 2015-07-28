<?php include_once($_SERVER['DOCUMENT_ROOT'] . '/classes/professions.php'); ?>
<?php include_once($_SERVER['DOCUMENT_ROOT'] . '/classes/freelancer.php'); ?>
<?php if(empty($no_reset_filter)){ ?>
<script type="text/javascript">exists_pars = new Array();</script>
<?php } ?>
    <div id="head_filter" style="display: <?= $user->mailer && $user->mailer_str != "" ? '' : 'none'; ?>">
              <div class="b-select b-select_padbot_10">
                <select id="subscr_cat" onchange="applySubcat(this.value); if(this.value != 0){ document.getElementById('addFilterButton').disabled = false;} else { document.getElementById('addFilterButton').disabled = true;}" class="b-select__select b-select__select_width_220">
                    <option value="0">Выберите раздел</option>
                    <?php foreach (professions::GetAllGroupsLite(true) as $cat) { ?>
                        <option value="<?= $cat['id']; ?>"><?= $cat['name']; ?></option>

                    <?php } ?>
                </select>
              </div>
              <div class="b-select b-select_padbot_10">
                <select id="subscr_sub" class="b-select__select b-select__select_width_220"><option value="0">Весь раздел</option></select>
              </div>
                <button class="b-button b-button_flat b-button_flat_grey b-button_margbot_20" type="button" onclick="addMailerFilter()" disabled="disabled" id="addFilterButton">Добавить</button>
            </div>
    <div id="filter_body" class="b-layout__txt b-layout__txt_padbot_10">
        <?php
        $js = '';
        foreach (explode(':', $user->mailer_str) as $vl) {
            if (preg_match("/c([0-9]+)s?([0-9]*)/i", $vl, $res)) {
                $grp = professions::GetGroup($res[1], $error);
                $cat_name = $res[1] ? $grp['name'] : '<em>Все разделы</em>';
                $sub_name = $res[2] ? professions::GetProfName($res[2]) : '<em>Все подразделы</em>';
                ?>
                <div class="b-layout__txt b-layout__txt_padbot_10">
                    <input type="hidden" name="cats[]" value="<?= (int) $res[1]; ?>" />
                    <input type="hidden" name="subcats[]" value="<?= (int) $res[2]; ?>" />
                    <span class="b-layout__bold"><?= $cat_name; ?></span> &#160;
                    <?= $sub_name; ?> &#160;
                    <a href="javascript:void(0)" onclick="xajax_removeSubscFilter(<?= (int) $res[1]; ?>,<?= (int) $res[2]; ?>); unset(<?= (int) $res[1]; ?>,<?= (int) $res[2]; ?>); document.getElementById('filter_body').removeChild(this.parentNode);"><img class="b-layout__pic" src="/images/btn-remove2.png" alt="Удалить" /></a>
                </div>
                <?php
                $js .= 'exists_pars[exists_pars.length] = new Array(' . (int) $res[1] . ',' . (int) $res[2] . '); ';
            }
        }
        ?>
    </div>
<script type="text/javascript"><?= $js; ?></script>