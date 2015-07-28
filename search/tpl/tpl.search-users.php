<?php


$mark_array = array((string) $result['login'],
                    (string) $result['uname'],
                    (string) $result['usurname'],
                    (string) $result['country_name'],
                    (string) $result['city_name'],
                    (string) $result['name_prof']);

list ($login, $uname, $usurname, $country_name, $city_name, $name_prof) = $element->mark($mark_array);

$limit_default = $element->getOpts('limit');
$spec_text = reformat(strip_tags((string) $result['spec_text'] . ' ' . (string) $result['resume']), 30);
$element->setOpts('limit', 45);
list($spec_text) = $element->mark(array($spec_text));
$element->setOpts('limit', $limit_default);

if (!preg_match('/<em>.*?<\/em>/si', $spec_text)) {
    $spec_text = null;
} else {
    $spec_text = strip_tags($spec_text, '<em>');
}

$work = $element->works[$result['id']];
?>

<table class="search-user">
    <?php if($first_element) {?>
    <tr>
        <th></th>
        <th></th>
        <th>Статус:</th>
        <th>Отзывы:</th>
        <th class="last">Стоимость работ:</th>
    </tr>
    <?php }//if?>
    <tr class="search-cls"><td colspan="5"></td></tr>
	<tr>
        <td class="first">
            <span class="number-item"><?=$i?>.</span>
            <span class="search-pic b-page__desktop b-page__ipad"><?=view_avatar($result['login'], $result['photo'])?></span>
        </td>
        <td class="search-user-info">
           <div class="b-layout__txt b-layout__txt_float_right b-page__desktop">
            <?php if(!get_uid(false)||is_emp()) { ?><a href="/new-personal-order/<?=$result['login']?>/" class="b-button b-button_flat b-button_flat_green b-button_margbot_10" >Предложить заказ</a><?php } ?>
            <div class="b-layout__txt b-layout__txt_right"><a class="b-layout__link" href="/users/<?=$result['login']?>/">Перейти в портфолио</a></div>
           </div>
            <span class="b-page__iphone"><?=view_avatar($result['login'], $result['photo'])?></span>
            <div class="user-info">
                <? $cls = is_emp($result['role']) ? 'empname11' : 'frlname11'; ?>
                <?=$session->view_online_status($result['login'])?><span style="font-size:13px"><span class="<?= $cls?>"><a title="<?=$result['uname'].' '.$result['usurname']?>" class="<?= $cls?>" href="/users/<?=$result['login']?>/?f=<?=stat_collector::REFID_SEARCH?>&stamp=<?=$_SESSION['stamp']?>"><?= "{$uname} {$usurname} [{$login}]";?></a></span>&#160;<?= view_mark_user($result);?></span>
                
                
                <?php if($result['name_prof']) {?><span class="spec">Специализация: <?= ($result['name_prof']?$name_prof:"Нет специализации")?></span><?php }//if?>
                <?php if($result['additional_spec']) {?><span class="spec">Дополнительные специализации: <?=$result['additional_spec']?></span><?php }//if?>
                <span class="country_name"><?=$country_name?$country_name:""?><?=$country_name&&$city_name?", ":""?><?= $city_name?$city_name:""?></span>
                <span class="user-opyt">Опыт работы: <?= view_exp($result['exp'])?><?php if($result['in_office']=='t'){ ?><br/><span class="run-men">Ищу работу в офисе</span><?php }//if?></span>
                <? if ($spec_text) { ?>
                <span class=""><br/>&laquo;<?= $spec_text ?>&raquo;</span>
                <? } ?>
            </div>
            <?php if(!get_uid(false)||is_emp()) { ?><a href="/new-personal-order/<?=$result['login']?>/" class="b-button b-button_flat b-button_flat_green b-button_float_right b-page__iphone b-page__ipad" >Предложить заказ</a><?php }//if?>
            <span class="b-layout__txt b-page__iphone b-page__ipad"><a class="b-layout__link" href="/users/<?=$result['login']?>/">Перейти в портфолио</a></span>
        </td>
                        	<?php if(!is_emp($result['role'])) { ?>
                        	<td class="search-user-stat">
                            	<ul class="statuses">
                                    <li class="<?= getStatusUserCSS($result['status_type']) ?>"><span><span id="statusTitle"><?= $status_users[$result['status_type']] ?></span></span></li>
                                </ul>
                            </td>
                            <?php }//if?>
                        	<td class="search-user-rate">
                                        <a class="ops-plus" href="/users/<?=$result['login']?>/opinions/?sort=1#op_head">+&nbsp;<?= (int)($result['all_opi_plus']) ?></a><span class="b-page__iphone"><br></span>&#160;
                                        <a class="ops-neitral" href="/users/<?=$result['login']?>/opinions/?sort=2#op_head"><?= (int)($result['all_opi_null']) ?></a><span class="b-page__iphone"><br></span>&#160;
                                        <a class="ops-minus" href="/users/<?=$result['login']?>/opinions/?sort=3#op_head">-&nbsp;<?= (int)($result['all_opi_minus'])?></a>
                        	</td>
                        	<td class="last">
                        	       <table>
                        	        <?php if((int)$result['cost_hour'] > 0) {?>  
									<tr>
										<td>За час: <span class="b-page__iphone"><br></span><b><?= view_cost2($result['cost_hour'], '', '', false, $result['cost_type_hour'])?></b></td>
									</tr>
									<?php }//if?>
									<?php if((int)$result['cost_1000'] > 0) {?>
									<tr>
										<td class="prstvk"></td>
									</tr>
									<tr>
										<td>За 1000 зн.: <span class="b-page__iphone"><br></span><b><?= view_cost2($result['cost_1000'], '', '', false, $result['cost_type'])?></b></td>
									</tr>
									<?php }//if?>
									<?php if((int)$result['cost_from'] > 0 || (int)$result['cost_from'] > 0){ ?>
									<tr>
										<td class="prstvk"></td>
									</tr>
									<tr>
										<td>За проект: <span class="b-page__iphone"><br></span><b><?= view_range_cost($result['cost_from'], $result['cost_to'], '', '', false, $result['cost_type'])?></b></td>
									</tr>
									<?php }//if?>
									<?php if((int)$result['cost_month'] > 0) { ?>
									<tr>
										<td class="prstvk"></td>
									</tr>
									<tr>
										<td>За месяц: <span class="b-page__iphone"><br></span><b><?= view_cost2($result['cost_month'], '', '', false, $result['cost_type_month'])?></b></td>
									</tr>
									<?php }//if?>
								</table>
                            </td>
                        </tr>
                        <tr class="search-cls">
                        	<td colspan="5"></td>
                        </tr>
                        <?php if(!is_emp($result['role'])) { ?>
                            <?php if($work && $result['is_pro'] == 't') {?>
                        <tr>
                        	<td class="first"></td>
                            <td colspan="4" class="search-pic-item-wrap">
                                <table class="search-pic-item">
                                    <tr>
                                        <?php for($k=0;$k<3;$k++) { ?>
                                        <td class="<?php    
																						switch($k){
																							case '0': print 'first';break;
																							case '1': break;
																							case '2': print 'last';break;
																						}
																					?>">
                                            <?php if($work[$k]['id'] > 0 && $work[$k]['prev_type'] == 0) {?>
                                            <div class="search-pic-inner">
                                                <a href="/users/<?=$result['login']?>/viewproj.php?prjid=<?=$work[$k]['id']?>" target="_blank" title="<?=htmlspecialchars($work[$k]['name'])?>">
                                                    <?=view_preview($result['login'], $work[$k]['prev_pict'], "upload", 'center', true, true, '', 200)?>
                                                </a>
                                            </div>
                                            <?php } else if($work[$k]['id'] > 0) {//if?>
                                            <div class="search-pic-inner">
                                                <h4><a href="/users/<?=$result['login']?>/viewproj.php?prjid=<?=$work[$k]['id']?>"><?= reformat(strip_tags($work[$k]['name'], "<em><br>"), 20)?></a></h4>
                                                <p><?= reformat(strip_tags($work[$k]['descr'], "<em><br>"), 35)?></p>
                                            </div>
                                            <?php } //else if?>
                                        </td>
                                        <?php } //for?>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr class="search-cls">
                        	<td colspan="5"></td>
                        </tr>
                            <?php }//if?>
                        <?php }//if?>
					</table>
