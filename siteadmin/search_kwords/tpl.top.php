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
        <h3>Топ запросы</h3>
        <? include_once ('tpl.navigation.php'); ?>
        <br/>
        
        <div class="form form-nr-docs-sort ">
            <b class="b1"></b>
            <b class="b2"></b>
            <div class="form-in">
                <div class="form-b">
                    <div class="form-block first last">
                        <div class="form-el" style="text-transform: uppercase; font-size: 14px;">
                            
                            &nbsp;&nbsp;&nbsp;&nbsp;
                            <? if ($start != 'all') { ?>
                            <a href="?tab=top&s=all">Все</a>
                            <? } else { ?>
                            <strong>Все</strong>
                            <? } ?>
                            &nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;
                            <? if ($start != 'users') { ?>
                            <a href="?tab=top&s=users">По исполнителям</a>
                            <? } else { ?>
                            <strong>По исполнителям</strong>
                            <? } ?>
                            &nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;
                            <? if ($start != 'projects') { ?>
                            <a href="?tab=top&s=projects">По проектам</a>
                            <? } else { ?>
                            <strong>По проектам</strong>
                            <? } ?>
                            &nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;
                            <? if ($start != 'more') { ?>
                            <a href="?tab=top&s=more">По разделам сайта</a>
                            <? } else { ?>
                            <strong>По разделам сайта</strong>
                            <? } ?>
                        </div>
                    </div>
                </div>
            </div>
            <b class="b2"></b>
            <b class="b1"></b>
        </div>
        
        <!-- Таблица клиентов и кампаний -->
        <table class="tbl-cnc">
            <thead>
                <tr>
                    <th width="25px">
                        #
                    </th>
                    <th width="230">
                        Строка запроса
                    </th>
                    <th width="60">
                        Кол-во повторов (N)
                    </th>
                    <th width="60">
                        Кол-во совпадений (M)
                    </th>
                    <th width="80">
                        Вес (W)  
                    </th>
                </tr>
            </thead>
            <tbody>
            <? foreach ($data as $row) { ?>
                    <tr id="query<?= $row['id'] ?>">
                        <td class="c-st" width="25px">
                            &bull;
                        </td>
                        <td>
                            <?= change_q_x($row['query'], TRUE, FALSE)  ?>
                        </td>
                        <td>
                            <?= $row['cnt'] ?>
                        </td>
                        <td>
                            <?= $row['match_cnt'] ?>
                        </td>
                        <td>
                            <strong><?= $row['weight'] ?></strong>
                        </td>
                    </tr>
                <? } ?>
                    <tr id="deleteFrm" style="display:none;">
                        <td colspan="7">
                            <form name="frm" method="post" action="">
                                <input type="hidden" name="action" value="add_filter"/>
                                <input type="hidden" name="query" value=""/>
                                <div class="form form-cnc">
                                    <b class="b1"></b>
                                    <b class="b2"></b>
                                    <div class="form-in">
                                        <div class="form-block first">
                                            <div class="form-el">
                                                Чтобы удаляемый запрос или его часть больше не попадали в эту таблицу, нужно заполнить эту форму.
                                            </div>
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
                                                <button type="submit">Удалить запрос и создать фильтр</button> или <button onclick="return deleteQueryOnly(this)">Просто удалить запрос</button> 
                                            </div>
                                        </div>
                                    </div>
                                    <b class="b2"></b>
                                    <b class="b1"></b>
                                </div>
                            </form>
                        </td>
                    </tr>
            </tbody>
        </table>
        
        <?= new_paginator2($page, $pages, 3, "%s?" . urldecode(url($_GET, array('p' => '%d'))) . "%s") ?>
        
    </div>
</div>