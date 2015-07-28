{{include "header.tpl"}}

            <?php
            if(hasPermissions('about')) {
                require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/presscenter.common.php");
                $xajax->printJavascript('/xajax/');
            }
            ?>

					<h1 class="b-page__title">Пресс-центр</h1>
							<div class="b-layout__right b-layout__right_width_72ps b-layout__right_float_right pc-team2">
                                <? if(hasPermissions('about')) { ?>
								<ul class="pc-team2-anav">
									<li>
										<span class="lnk-gr">
											<b class="b1"></b><b class="b2"></b>
											<span><a href="#" onClick="HideErrorMessages(); ShowAddCategoryForm(); return false;">Добавить раздел</a></span>
											<b class="b2"></b><b class="b1"></b>
										</span>
									</li>
									<li>
										<span class="lnk-gr">
											<b class="b1"></b><b class="b2"></b>
											<span><a href="#" onClick="HideErrorMessages(); ResetTeamForm(); ShowAddPeopleTeamForm(); return false;"><strong>Добавить сотрудника</strong></a></span>
											<b class="b2"></b><b class="b1"></b>
										</span>
									</li>
								</ul>
                                <? } ?>
								<h2 class="b-layout__title">Команда FL.ru</h2>
								<p class="b-txt_padbot_30">Мы решили, что фрилансеры и работодатели должны знать тех, кто делает сайт удобнее, развивает его и помогает вам решать возникающие вопросы.</p>

                                <? if(hasPermissions('about')) { ?>
                                <form action="/about/team/" method="post" id="dcf">
                                <div>
                                    <input type="hidden" name="action" value="deletecategory" />
                                    <input type="hidden" name="dcf_id" id="dcf_id" value="" />
                                </div>
                                </form>

                                <form action="/about/team/" method="post" id="dtf">
                                <div>
                                    <input type="hidden" name="action" value="deleteteampeople" />
                                    <input type="hidden" name="dtf_id" id="dtf_id" value="" />
                                </div>
                                </form>

								<div class="team-form-out" style="display: none;" id="addcategoryform">
                                    <a name="addcategoryform"></a>
									<h4>Добавить раздел</h4>
									<div class="team-form c">
										<div class="ft-b">
                                            <form action="/about/team/" method="post">
                                            <div>
                                            <input type="hidden" name="action" value="insertcategory" />
											<div class="ft-btn ft-btn2">
												<input type="submit" value="Добавить" class="i-btn i-bold" />
												<input type="button" value="Отменить" class="i-btn" onClick="HideAddCategoryForm();" />
											</div>
											<div class="ft-el ft-num">
												<label>Порядок</label>
												<input type="text" value="<?=$$acf_position?>" id="acf_number" name="acf_number" />
											</div>
											<div class="ft-el ft-part">
												<label>Название</label>
												<input type="text" value="<?=$$acf_name?>" id="acf_name" name="acf_name" />
											</div>
                                            </div>
                                            </form>
										</div>
                                        <? if ($$error_msgs_acf[1]) print(view_error($$error_msgs_acf[1])) ?>
                                        <? if ($$error_msgs_acf[2]) print(view_error($$error_msgs_acf[2])) ?>
									</div>
								</div>

								<div class="team-form-out" style="display: none;" id="editcategoryform">
                                    <a name="editcategoryform"></a>
									<h4>Редактировать раздел</h4>
									<div class="team-form c">
										<div class="ft-b">
                                            <form action="/about/team/" method="post">
                                            <div>
                                            <input type="hidden" name="action" value="updatecategory" />
                                            <input type="hidden" name="ecf_id" id="ecf_id" value="<?=$$ecf_id?>" />
											<div class="ft-btn ft-btn2">
												<input type="submit" value="Сохранить" class="i-btn i-bold" />
												<input type="button" value="Отменить" class="i-btn" onClick="HideEditCategoryForm();" />
											</div>
											<div class="ft-el ft-num">
												<label>Порядок</label>
												<input type="text" id="ecf_number" name="ecf_number" value="<?=$$ecf_position?>" />
											</div>
											<div class="ft-el ft-part">
												<label>Название</label>
												<input type="text" id="ecf_name" name="ecf_name" value="<?=$$ecf_name?>" />
											</div>
                                            </div>
                                            </form>
										</div>
                                        <? if ($$error_msgs_ecf[1]) print(view_error($$error_msgs_ecf[1])) ?>
                                        <? if ($$error_msgs_ecf[2]) print(view_error($$error_msgs_ecf[2])) ?>
									</div>
								</div>

								<div class="team-form-out" id="people_team_form" style="display:none;">
                                    <a name="peopleteamform"></a>
									<h4 id="people_team_form_header">Добавить сотрудника</h4>
                                    <form action="/about/team/" method="post" enctype="multipart/form-data">
                                    <div>
                                    <input type="hidden" id="teampeopleaction" name="action" value="" />
                                    <input type="hidden" id="pt_id" name="pt_id" value="<?=$$p_id?>" />
									<div class="team-form c">
                                    	<table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
                                        	<tr class="b-layout__tr">
                                            	<td class="b-layout__one b-layout__one_width_170">
										<div class="form-photo">
											<img src="" alt="" width="150" height="200" id="pt_photo_file" style="display:none;" />
											<input type="file" value="Обзор" class="i-btn" id="pt_photo" name="pt_photo" size="6" />
											<a href="#" title="Удалить" onClick="xajax_DeletePhoto($('pt_id').get('value')); return false;" id="btnteamdeletephoto"><img src="../../images/btn-remove.png" alt="Удалить" width="21" height="21" class="photo-clear" /></a>
										</div>
                                        		</td>
                                            	<td class="b-layout__one">
                                                
                                    	<table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
                                        	<tr class="b-layout__tr">
                                            	<td class="b-layout__one b-layout__one_padright_10">
												<label for="pt_name">Имя, фамилия</label>
                                                <div class="b-combo">
                                                	<div class="b-combo__input">
														<input id="pt_name" class="b-combo__input-text" type="text" name="pt_name" value="<?=$$p_name?>" />
                                                    </div>
                                                </div>
                                                </td>
                                            	<td class="b-layout__one b-layout__one_width_150">
												<label for="pt_login">Логин</label>
                                                <div class="b-combo">
                                                	<div class="b-combo__input ">
														<input id="pt_login" class="b-combo__input-text" name="pt_login" value="<?=$$p_login?>" type="text" />
                                                    </div>
                                                </div>
												</td>
                                             </tr>
                                        </table>

												<label for="pt_occupation">Должность</label>
                                                <div class="b-combo">
                                                	<div class="b-combo__input">
														<input id="pt_occupation" class="b-combo__input-text" type="text" name="pt_occupation" value="<?=$$p_occupation?>" />
                                                    </div>
                                                </div>
                                    	<table class="b-layout__table b-layout__table_width_full" cellpadding="0" cellspacing="0" border="0">
                                        	<tr class="b-layout__tr">
                                            	<td class="b-layout__one b-layout__one_padright_10">
												<label for="pt_group">Категория</label>
                                                <div class="b-select">
                                                    <select id="pt_group" class="b-select__select" name="pt_group" >
                                                        <? foreach($$groups as $group) { ?>
                                                        <option  value="<?=$group['id']?>" <?=(($$p_group==$group['id'])?'selected="selected"':'')?>><?=$group['title']?></option>
                                                        <? } ?>
                                                    </select>
                                                </div>
                                                </td>
                                            	<td class="b-layout__one b-layout__one_width_50">
												<label for="pt_position">Порядок</label>
                                                <div class="b-combo">
                                                	<div class="b-combo__input 0">
														<input id="pt_position" class="b-combo__input-text" type="text" name="pt_position" value="<?=$$p_position?>" />
                                                    </div>
                                                </div>
												</td>
                                             </tr>
                                        </table>
												<label for="pt_info">Дополнительная информация:</label>
                                                <div class="b-textarea">
													<textarea id="pt_info" class="b-textarea__textarea" rows="5" cols="20" name="pt_info"><?=$$p_info?></textarea>
                                                </div>
                                        		</td>
                                             </tr>
                                        </table>
										<div class="ft-btn">
											<input type="submit" value="Добавить" class="i-btn i-bold" id="people_team_form_btn" />
											<input type="button" value="Отменить" class="i-btn" onClick="HidePeopleTeamForm();" />
										</div>
                                    <? if ($$error_msgs_apf[1]) print(view_error($$error_msgs_apf[1])) ?>
                                    <? if ($$error_msgs_apf[2]) print(view_error($$error_msgs_apf[2])) ?>
                                    <? if ($$error_msgs_apf[3]) print(view_error($$error_msgs_apf[3])) ?>
                                    <? if ($$error_msgs_apf[4]) print(view_error($$error_msgs_apf[4])) ?>
                                    <? if ($$error_msgs_apf[5]) print(view_error($$error_msgs_apf[5])) ?>

									</div>
                                    </div>
                                    </form>
								</div>
                                <? } ?>

                                <? 
                                $gr_count = sizeof($$team_people);
                                $i = -1;
                                $category_ids = '';
                                reset($$groups);
                                ?>

                                <? foreach($$groups as $group): ?>
                                    <? 
                                    $users = $$team_people[$group['id']];
                                    if(!$users) {
                                        if(!hasPermissions('about')) continue;
                                    }
                                    $user_count = sizeof($users);
                                    $i++;
                                    $category_ids .= '#ul_cat_'.$group['id'].',';
                                    ?>
                                    <h4><?=$group['title']?><? if(hasPermissions('about')) { ?> <a href="#" onClick="HideErrorMessages(); ShowEditCategoryForm(<?=$group['id']?>); return false;" title="Редактировать"><img src="/images/btn-edit.png" alt="Редактировать" width="21" height="21" /></a> <a href="#" title="Удалить" onClick="if(confirm('Вы действительно хотите удалить раздел?')) { DeleteCategory(<?=$group['id']?>); return false; } else { return false; }"><img src="/images/btn-remove.png" alt="Удалить" width="21" height="21" /></a><? } ?></h4>
                                    <input type="hidden" id="d_category_name_<?=$group['id']?>" value="<?=$group['title']?>" />
                                    <input type="hidden" id="d_category_number_<?=$group['id']?>" value="<?=$group['position']?>" />
                                    <div class="pc-team2-block c">
                                    <ul class="pc-team2-dnd c" id="ul_cat_<?=$group['id']?>">
                                    <?
                                    if ($users) {
                                        foreach($users as $k=>$val): 
                                    ?>
                                        <li class="pc-team2-dnd-one" id="li_team_<?=$val['id']?>">
									    <div class="pc-team2-one">
                                            <? if (hasPermissions('about')) { ?>
    										<div class="pc-team2-admin">
    											<a href="#" title="Редактировать" onClick="HideErrorMessages(); ShowEditPeopleTeamForm(<?=$val['id']?>); return false;" ><img src="/images/btn-edit.png" alt="Редактировать" width="21" height="21" /></a>
    											<a href="#" title="Удалить" onClick="if(confirm('Вы действительно хотите удалить пользователя?')) { DeleteTeamPeople(<?=$val['id']?>); return false; } else { return false; }" ><img src="/images/btn-remove.png" alt="Удалить" width="21" height="21" /></a>
    										</div>
                                            <? } ?>
                                            <?
                                            if($val['userpic']) {
                                                $val['userpic'] = WDCPREFIX."/team/".$val['userpic'];
                                            } else {
                                                $val['userpic'] = '/images/team_no_foto.gif';
                                            }
                                            ?>
                                            <? if($val['login'] && !hasPermissions('about')): ?><a href="/users/<?=$val['login']?>/"><? endif; ?>
                                            <img src="<?=$val['userpic']?>" alt="<?=$val['name']?>" width="150" height="200" class="handlesClass" id="peoplephoto_<?=$val['id']?>" />
                                            <? if($val['login'] && !hasPermissions('about')): ?></a><? endif; ?>

                                            <strong>
                                            <? if($val['login']): ?><a href="/users/<?=$val['login']?>/"><? endif; ?>
                                                <?=$val['name']?>
                                            <? if($val['login']): ?></a><? endif; ?>
                                            </strong>

										    <?=$val['occupation']?>
									    </div>
                                        </li>
                                    <?
                                        endforeach; 
                                    }
                                    ?>
                                    </ul>
                                    </div>
                                <? endforeach; ?>
                                <?
                                $category_ids = preg_replace("/,$/","",$category_ids);
                                ?>
							</div>
                            {{include "press_center/press_menu.tpl"}}

            <script type="text/javascript">
                <? if($$error_msgs_ecf) { ?>
                    ShowEditCategoryForm(0);
                    window.location = '#editcategoryform';
                <? } ?>
                <? if($$error_msgs_acf) { ?>
                    ShowAddCategoryForm();
                    window.location = '#addcategoryform';
                <? } ?>
                <? if($$error_msgs_apf) { ?>
                    ShowAddPeopleTeamForm();
                    <? if($$p_action=='updatepeople') { ?>
                            $('people_team_form_header').set('html','Редактировать сотрудника');
                            $('teampeopleaction').value="updatepeople";
                            $('people_team_form_btn').value="Сохранить";
                            $('btnteamdeletephoto').setStyle('display','inline');
                            $('pt_id').value='<?=$$p_id?>';
                    <? } ?>
                    window.location = '#peopleteamform';
                <? } ?>
                <? if(hasPermissions('about')) { ?>
                window.addEvent('domready', function(){
                	var mySortables = new Sortables('<?=$category_ids?>', {
                        clone: true,
                        handle: '.handlesClass',
                        onStart: function(el) {
                        },
                        onComplete: function(el) {
                            var data = mySortables.serialize(function(element, index){
                                            return element.getProperty('id').replace('li_team_','') + '=' + index;
                                        }).join('|');
                            xajax_ReorderTeam(data);
                        }
                    });
                });
                <? } ?>
            </script>
<style type="text/css">
@media screen and (max-width: 800px){
.b-layout__page .b-layout__left, .b-layout__right {
    display: block;
    width: 100% !important;
}
}
@media screen and (max-width: 680px){
.pc-team2-dnd-one { width:50%; text-align:center;}
.pc-team2-one{ margin:0 auto; float:none;}
}
@media screen and (max-width: 400px){
.pc-team2-dnd-one { width:100%}
}
</style>

{{include "footer.tpl"}}
