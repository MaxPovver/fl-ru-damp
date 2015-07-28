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
        <h3>Условия фильтрации</h3>
        <? include_once ('tpl.navigation.php'); ?>
        <br/>

        <form name="frm" method="post" action="">
            <input type="hidden" name="action" value="add_rule"/>
            <div class="form form-cnc">
                <b class="b1"></b>
                <b class="b2"></b>
                <div class="form-in">
                    <div class="form-block first">
                        <h3>Новое условие</h3>
                        <div class="form-el">
                            <label class="form-l">Имя:</label>
                            <div class="form-value">
                                <input type="text" name="rule_name" class="sw205"/>
                            </div>
                        </div>
                        <div class="form-el">
                            <label class="form-l">Условие:</label>
                            <div class="form-value">
                                <input type="text" name="pattern" class="sw205"/> для подстановки фильтра используется <strong>%s</strong>
                            </div>
                        </div>
                    </div>
                    <div class="form-block last">
                        <div class="form-el form-btns flm">
                            <button type="submit">Сохранить</button>
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
                        Имя
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
                            <?= $row['rule_name'] ?>
                        </td>
                        <td><?= $row['pattern'] ?></td>

                        <td class="c-prd <?= $order == 'act' ? 'c-id' : '' ?>">
                            <a href="./?tab=rules&action=delete_rule&id=<?= $row['id'] ?>" onclick="return confirm('Точно удалить?')">Удалить</a>
                        </td>
                    </tr>
                <? } ?>
            </tbody>
        </table>

    </div>
</div>
