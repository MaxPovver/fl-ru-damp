<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; } ?>
<h2>Поиск</h2>
<div class="admin">
<div class="lm-col">
    <div class="admin-menu">
        <h3>Поиск</h3>

        <? include ($rpath . "/siteadmin/leftmenu.php") ?>

    </div>
</div>
</div>
<div class="r-col">
    <div class="ban-razban">
        <h3>Фильтры</h3>
        <? include_once ('tpl.navigation.php'); ?>
        <br/>

        <form name="frm" method="post" action="">
            <input type="hidden" name="action" value="add_filter"/>
            <div class="form form-cnc">
                <b class="b1"></b>
                <b class="b2"></b>
                <div class="form-in">
                    <div class="form-block first">
                        <h3>Новый фильтр</h3>
                        <div class="form-el">
                            <label class="form-l">Удаляет слова, которые:</label>
                            <div class="form-value">
                                <select name="filter_rule" class="sw205">
                                    <? foreach ($rules as $rule) { ?>
                                        <option value="<?= $rule['id'] ?>"><?= $rule['rule_name'] ?></option>
                                    <? } ?>
                                </select>
                                <input type="text" name="word" class="sw205"/>
                            </div>
                        </div>
                    </div>
                    <div class="form-block last">
                        <div class="form-el form-btns flm">
                            <button type="submit">Создать фильтр</button>
                        </div>
                    </div>
                </div>
                <b class="b2"></b>
                <b class="b1"></b>
            </div>
        </form>
        <!-- Таблица клиентов и кампаний -->
        <table class="tbl-cnc">
            <thead>
                <tr>
                    <th>
                        #
                    </th>
                    <th>
                        Строка
                    </th>
                    <th>
                        Условие
                    </th>
                    <th>&nbsp;</th>
                </tr>
            </thead>
            <tbody>
                <? foreach ($data as $row) { ?>
                    <tr id="query<?= $row['id'] ?>">
                        <td class="c-st">
                            &bull;
                        </td>
                        <td>
                            <?= change_q_x($row['word'], TRUE, FALSE) ?>
                        </td>
                        <td><?= $row['rule_name'] ?></td>

                        <td class="c-prd <?= $order == 'act' ? 'c-id' : '' ?>">
                            <a href="./?tab=filters&action=delete_filter&id=<?= $row['id'] ?>" onclick="return confirm('Точно удалить?')">Удалить</a>
                        </td>
                    </tr>
                <? } ?>
            </tbody>
        </table>

    </div>
</div>
