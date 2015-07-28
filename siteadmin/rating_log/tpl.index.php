<?php if ( !defined('IS_SITE_ADMIN') ) { header('Location: /404.php'); exit; } ?>
        <h3>Лог рейтинга</h3>
        <br/>

        
            
            <div class="form form-cnc">
                <b class="b1"></b>
                <b class="b2"></b>
                <div class="form-in">
                <form name="frm" method="post" action="./">
                    <input type="hidden" name="action" value="search_user"/>
                    <div class="form-block first">
                        <h3>Поиск пользователя</h3>
                        <div class="form-el">
                            <label class="form-l">Логин:</label>
                            <div class="form-value">
                                <input type="text" name="login_user" class="sw205" value="<?= (isset($login)?htmlspecialchars($login):"") ?>"/>
                            </div>
                        </div>
                        <div class="form-el">
                            <label class="form-l">Фильтр по фактору: </label>
                            <div class="form-value">
                                <select name="filter_factor" class="sw205">
                                    <option value="-1" <?= ($filter == false?'selected="selected"':'');?>>Все факторы</option>
                                    <? foreach ($rating->bit_factor as $id=>$name) { ?>
                                        <option value="<?= $id ?>" <?= ($filter === $id?'selected="selected"':'');?>><?= $name ?></option>
                                    <? } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-block last">
                        <div class="form-el form-btns flm">
                            <input type="submit" value="Найти" name="sbm">
                        </div>
                    </div> 
                </form>
                </div>
                <b class="b2"></b>
                <b class="b1"></b>
            </div>
        
        <?php if(count($rlog) > 0) { ?>
        <table class="tbl-cnc">
            <colgroup>
                <col width="30%"></col>
                <col width="20%"></col>
                <col width="10%"></col>
                <col width="40%"></col>
                <col width="10%"></col>
                <col width="10%"></col>
            </colgroup>
            <thead>
                <tr>
                    <th>Пользователь</th>
                    <th>Дата</th>
                    <th>Рейтинг</th>
                    <th>Фактор изменения</th>
                    <th>PRO</th>
                    <th>Вериф</th>
                </tr>
            </thead>
            <tbody>
                <?
                $verify_actions = array(); //флаг перехода  верифицированый / неверефицированых пользователь 
                foreach ($rlog as $row) { $factors = $rating->getBitFactors($row['factor']); ?>
                    <tr id="query<?= $row['log_id'] ?>">
                        <td><a href="/users/<?= $row['login']?>/"><?= "{$row['uname']} {$row['usurname']}  [{$row['login']}]";?></a></td>
                        <td><?= date('d.m.Y [H:i]', strtotime($row['_date'])) ?></td>
                        <td><?= $row['rating'] * ($row['is_verify'] == 't' && $verify_actions[ $row['login'] ] != 1 ? 1.2 : 1)?></td>
                        <?php if ( $row['factor'][32] == '1' ){ 
                            $verify_actions[ $row['login'] ] = 1;
                        }?>
                        <td title="<?=$row['factor']?>">
                        <? foreach($factors as $pos=>$factor) { ?>
                            <?= $rating->bit_factor[$factor]?><br/>
                            <?= count($factors)-1 != $pos? "<hr>":""?>
                        <? }//foreach?>
                        <?php if(count($factors) == 0) {?>
                        Факторы не зафиксированы
                        <?php }//if?>
                        </td>
                        <td>
                        <?php if($row['u_is_pro'] > 0) { ?>
                            <img src='/images/icons/<?=is_emp($row['role'])?"e-pro.png":"f-pro.png"?>' title='C <?= date('d.m.Y H:i', strtotime($row['from_date']))?> до <?= date('d.m.Y H:i', strtotime($row['to_date']))?>'>
                        <?php }?>    
                        </td>
                        <td>
                        <?php if ( $row['is_verify'] === 't' && (!$verificationTime || ($verificationTime <= strtotime($row['_date']))) ) { ?>
                            <?= view_verify() ?>
                        <?php }?>    
                        </td>
                    </tr>
                <? } ?>
            </tbody>
        </table>    
        <?= new_paginator2($page, $pages, 3, '%s/siteadmin/rating_log/?page=%d'.$href.'%s')?>
        <?php } else if($not_search) { //if?>
            <h4>По введенным данным ничего не найдено</h4>
        <?php }?>