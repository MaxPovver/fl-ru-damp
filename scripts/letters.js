var status_can_submit = false;
var is_js_cmd = false;
var is_templates_mode = false;

var letters = {
	spinner: null,
	docid: 0,
	mode: 'list',
	status_num: 0,
	statusnum: 0,
	cur_tab: 0,
	nn: 0,
	view_mode: 1,
	view_data: '',
	view_data1: '',
	get_zip: 0,
    nums: 50,
    filter: [],

	resetDialogs: function() {
		letters.massDivHideStatus();
		letters.statusesHide();
		letters.massHideStatus();
		letters.hideAddForm();
		letters.formCommentHide();
		letters.formDeliveryHide();
		letters.formDeliveryCostHide();
		letters.formDateAddHide();
		letters.formDateChangeHide();
		letters.formMassDeliveryCostHide();
		letters.formMassDateHide();
	},

	reload_data: function() {
		switch(letters.view_mode) {
			case 1:
				letters.changeTabs(1);
				break;
			case 2:
				letters.changeTabs(2);
				break;
			case 3:
				letters.changeTabs(3);
				break;
			case 4:
				letters.changeTabs(4);
				break;
			case 5:
				letters.changeTabs(5);
				break;
			case 6:
				letters.changeTabs(6);
				break;
			case 10:
				letters.showGroup(letters.view_data);
				break;
			case 11:
				letters.showByUser(letters.view_data, letters.view_data1);
				break;
			case 20:
				letters.showDoc(letters.view_data);
				break;
			case 30:
				letters.search();
				break;
			case 40:
				letters.searchDocs();
				break;
		}
	},

	search: function() {
		$('letters_filter_search_fld').set('value', $('letters_search_frm_field').get('value'));
		letters.view_mode = 30;
		letters.view_data = $('letters_filter_search_fld').get('value');
		letters.searchDocs();
		$('letters_filter_search_fld').set('value', '');
	},

	resetTabs: function() {
		$('letters_wrapper_view').set('html','');
		letters.mode = 'list';
		letters.cur_tab = 0;
		letters.view_mode = 1;
		letters.view_data = '';
		$('tabs').getElements('li:odd').setStyle("display", "none");
		$('tabs').getElements('li:even').setStyle("display", "inline-block");
		$('letters_mass_action_div_menu_send').setStyle('display', 'none');

		$('letters_mass_action_div_selected_docs').set('html', 0);
		$('letters_selected_delivery_cost').setStyle('display', 'none');
		$('letters_mass_action_div_menu_send').setStyle('display', 'none');
		$('letters_frm_mass_data_ids').set('value', '');

		$('letters_h_list').setStyle('display', 'block');
		$('letters_h_list_group').setStyle('display', 'none');
		$('letters_h_list_user').setStyle('display', 'none');
		$('letters_h_list_title1').setStyle('display', 'none');
		$('letters_h_list_title2').setStyle('display', 'none');

		//letters.checkedRecalc();
	},

	buildList: function(tab, filter, page, nums) {
		filter = JSON.encode(filter);
		$('letters_data').set('html', '');
		letters.spinner.show();
		
		new Request.JSON({
			url: '/xajax/letters.server.php',
			onSuccess: function(resp) {
				if(resp && resp.success && resp.data) {
					var html = '';
					html = html + '<table cellspacing="0" cellpadding="0" border="0" class="b-layout__table b-layout__table_width_full b-layout__table_margtop_15 b-layout__table_margbot_10">\
									<tbody>\
										<tr class="b-layout__tr">\
											<td class="b-layout__one b-layout__one_width_30 b-layout__one_bordbot_double_ccc">\
												<div class="b-check b-check_margtop_5">\
													<input id="letters_check" class="b-check__input" type="checkbox" value="" onClick="letters.checkUncheclAll();">\
												</div>\
											</td>\
											<td class="b-layout__one b-layout__one_width_50 b-layout__one_bordbot_double_ccc">\
												<div class="b-layout__txt b-layout__txt_padbot_5">ID</div>\
											</td>\
											<td class="b-layout__one b-layout__one_padright_10 b-layout__one_bordbot_double_ccc">\
												<div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_fontsize_11 b-layout__txt_lineheight_20">Название документа</div>\
											</td>\
											<td class="b-layout__one b-layout__one_padright_10 b-layout__one_width_150 b-layout__one_bordbot_double_ccc">\
												<div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_fontsize_11 b-layout__txt_lineheight_20">Стороны</div>\
											</td>\
											<td class="b-layout__one b-layout__one_padright_10 b-layout__one_width_100 b-layout__one_bordbot_double_ccc">\
												<div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_fontsize_11 b-layout__txt_lineheight_20">Статус</div>\
											</td>\
											<td class="b-layout__one b-layout__one_width_110 b-layout__one_bordbot_double_ccc">\
												<div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_fontsize_11 b-layout__txt_lineheight_20">Изменение статуса</div>\
											</td>\
										</tr>\
									</tbody>\
								</table>';

					var old_number = 0;
					for(i=0; i<resp.data.length; i++) {
						if(resp.data[i].user_status_1==null || resp.data[i].user_status_1=='') resp.data[i].user_status_1 = 0;
						if(resp.data[i].user_status_2==null || resp.data[i].user_status_2=='') resp.data[i].user_status_2 = 0;
						if(resp.data[i].user_status_3==null || resp.data[i].user_status_3=='') resp.data[i].user_status_3 = 0;

						if(resp.data[i].user1_i_1_fio==null) resp.data[i].user1_i_1_fio = '';
						if(resp.data[i].user2_i_1_fio==null) resp.data[i].user2_i_1_fio = '';
						if(resp.data[i].user3_i_1_fio==null) resp.data[i].user3_i_1_fio = '';
						if(resp.data[i].user1_i_2_fio==null) resp.data[i].user1_i_2_fio = '';
						if(resp.data[i].user2_i_2_fio==null) resp.data[i].user2_i_2_fio = '';
						if(resp.data[i].user3_i_2_fio==null) resp.data[i].user3_i_2_fio = '';

						html_user3 = '';
						if(resp.data[i].user_3) {
							html_user3 = '<tr class="b-layout__tr">\
											    <td class="b-layout__one b-layout__one_padright_10 b-layout__one_width_150">\
													<div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_fontsize_11 b-layout__txt_padtop_2">\
														'+(resp.data[i].is_user_3_company=="t" ? 
															'<span class="b-icon b-icon_'+resp.data[i].user_status_3_icon+' b-icon_margleft_-20 b-icon_top_2"></span><a href="/siteadmin/letters/?mode=edit&id='+resp.data[i].user_3+'" target="_blank" class="b-layout__link b-layout__link_fontsize_11">'+resp.data[i].company3_name+'</a> <a href="#" class="b-layout__link" onClick="letters.showByUser('+resp.data[i].user_3+', \''+resp.data[i].is_user_3_company+'\'); return false;"><span class="b-icon b-icon_sbr_folder b-icon_valign_middle"></span></a>\
															<br>\
															'+(resp.data[i].company3_index ? resp.data[i].company3_index+',' : '')+'\
							                            	'+(resp.data[i].company3_country_title ? resp.data[i].company3_country_title+',' : '')+'\
							                            	'+(resp.data[i].company3_city_title ? resp.data[i].company3_city_title+',' : '')+'\
															'+resp.data[i].company3_address
														:
															'<span class="b-icon b-icon_'+resp.data[i].user_status_3_icon+' b-icon_margleft_-20 b-icon_top_2"></span><a href="/users/'+resp.data[i].user3_login+'" target="_blank" class="b-layout__link b-layout__link_fontsize_11">'+(resp.data[i].user3_i_form_type==1 ? resp.data[i].user3_i_1_fio : resp.data[i].user3_i_2_full_name)+' ['+resp.data[i].user3_login+']</a> <a href="#" class="b-layout__link" onClick="letters.showByUser('+resp.data[i].user_3+', \''+resp.data[i].is_user_3_company+'\'); return false;"><span class="b-icon b-icon_sbr_folder b-icon_valign_middle"></span></a>\
															<br>\
															'+(resp.data[i].user3_i_form_type==1 ? resp.data[i].user3_i_1_address : resp.data[i].user3_i_2_address)
														)+
													'</div>\
							                    </td>\
												<td class="b-layout__one b-layout__one_padright_10 b-layout__one_width_100">\
													<div id="letters_item_status_3_'+resp.data[i].id+(tab==2 || tab==6 ? '_'+resp.data[i].nn : '')+'" class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_padtop_2 b-layout__txt_fontsize_11">\
														<a href="#" class="b-layout__link b-layout__link_fontsize_11 b-layout__link_bordbot_dot_'+resp.data[i].user_status_3_color+'" onClick="letters.nn='+resp.data[i].nn+'; letters.formStatusShow('+resp.data[i].id+',3); return false;">\
															'+statuses_list[parseInt(resp.data[i].user_status_3)]+ (resp.data[i].user_status_3==2 || resp.data[i].user_status_3==3 ? '<br/>'+resp.data[i].user_status_date_3 : '<br/>')+'</a>\
													</div>\
												</td>\
												<td class="b-layout__one b-layout__one_width_110 ">\
													<div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_fontsize_11 b-layout__txt_padtop_2">&nbsp;</div>\
												</td>\
									        </tr>';
						}

						if(tab==2 || tab==6) {
							if(old_number!=resp.data[i].number) {
								html = html + '\
											<div class="b-check">\
												<input id="cover_'+resp.data[i].number+'" class="b-check__input" type="checkbox" onClick="letters.checkUncheckCover('+resp.data[i].number+', this);"><label class="b-check__label b-check__label_fontsize_13 b-check__label_bold" for="cover_'+resp.data[i].number+'">Конверт '+resp.data[i].number+'</label>\
											</div>\
											';
							}
							old_number = resp.data[i].number;
						}
						html = html + "<div id='letters_div_item_"+resp.data[i].id+"' class='b-fon b-fon_marglr_-10 b-fon_padbot_10'>";
						html = html + '\
								<div class="b-fon__body b-fon__body_pad_2_10 b-fon__body_fontsize_13 '+((resp.data[i].user_status_1==1 || resp.data[i].user_status_2==1 || resp.data[i].user_status_3==1 || resp.data[i].user_status_1==2 || resp.data[i].user_status_2==2 || resp.data[i].user_status_3==2 || resp.data[i].user_status_1==3 || resp.data[i].user_status_2==3 || resp.data[i].user_status_3==3) ? 'b-fon__body_bg_f0ffdf' : 'b-fon__body_bg_fff b-fon__body_bordbot_edddda')+'">\
									<table cellspacing="0" cellpadding="0" border="0" class="b-layout__table b-layout__table_width_full">\
										<tbody>\
											<tr class="b-layout__tr">\
												<td rowspan="'+(resp.data[i].user_3 ? '3' : '2')+'" class="b-layout__one b-layout__one_width_30">\
													<div class="b-check b-check_margtop_5">\
														<input id="letters_check_'+resp.data[i].id+'" numcover="'+resp.data[i].number+'" class="b-check__input" '+(tab==2 || tab==6 ? 'u_res="'+resp.data[i].ukey0+'"' : '')+' '+(tab==2 || tab==6 ? 'u_delivery="'+resp.data[i].delivery0+'"' : '')+' type="checkbox" value="'+resp.data[i].id+'" onClick="letters.checkUncheck(this);">\
													</div>\
												</td>\
												<td rowspan="'+(resp.data[i].user_3 ? '3' : '2')+'" class="b-layout__one b-layout__one_width_50">\
													<div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_fontsize_11 b-layout__txt_lineheight_20">'+resp.data[i].id+'</div>\
												</td>\
												<td rowspan="'+(resp.data[i].user_3 ? '3' : '2')+'" class="b-layout__one b-layout__one_padright_30">\
													<div class="b-layout__txt b-layout__txt_padbot_5">'+(resp.data[i].group_title ? '<a href="/siteadmin/letters/?page=group&group='+resp.data[i].group_id+'" class="b-layout__link b-layout__link_color_000 b-layout__link_bold">'+resp.data[i].group_title+'</a> &rarr; ' : '')+'<a href="/siteadmin/letters/?page=doc&doc='+resp.data[i].id+'" class="b-layout__link b-layout__link_bold">'+resp.data[i].title+'</a></div>\
												</td>\
												<td class="b-layout__one b-layout__one_padright_10 b-layout__one_width_150">\
													<div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_fontsize_11 b-layout__txt_padtop_2">\
														'+(resp.data[i].is_user_1_company=="t" ? 
															'<span class="b-icon b-icon_'+resp.data[i].user_status_1_icon+' b-icon_margleft_-20 b-icon_top_2"></span><a href="/siteadmin/letters/?mode=edit&id='+resp.data[i].user_1+'" target="_blank" class="b-layout__link b-layout__link_fontsize_11">'+resp.data[i].company1_name+'</a> <a href="#" class="b-layout__link" onClick="letters.showByUser('+resp.data[i].user_1+', \''+resp.data[i].is_user_1_company+'\'); return false;"><span class="b-icon b-icon_sbr_folder b-icon_valign_middle"></span></a>\
															<br>\
															'+(resp.data[i].company1_index ? resp.data[i].company1_index+',' : '')+'\
							                            	'+(resp.data[i].company1_country_title ? resp.data[i].company1_country_title+',' : '')+'\
							                            	'+(resp.data[i].company1_city_title ? resp.data[i].company1_city_title+',' : '')+'\
															'+resp.data[i].company1_address
														:
															'<span class="b-icon b-icon_'+resp.data[i].user_status_1_icon+' b-icon_margleft_-20 b-icon_top_2"></span><a href="/users/'+resp.data[i].user1_login+'" target="_blank" class="b-layout__link b-layout__link_fontsize_11">'+(resp.data[i].user1_i_form_type==1 ? resp.data[i].user1_i_1_fio : resp.data[i].user1_i_2_full_name)+' ['+resp.data[i].user1_login+']</a> <a href="#" class="b-layout__link" onClick="letters.showByUser('+resp.data[i].user_1+', \''+resp.data[i].is_user_1_company+'\'); return false;"><span class="b-icon b-icon_sbr_folder b-icon_valign_middle"></span></a>\
															<br>\
															'+(resp.data[i].user1_i_form_type==1 ? resp.data[i].user1_i_1_address : resp.data[i].user1_i_2_address)
														)+
													'</div>\
												</td>\
												<td class="b-layout__one b-layout__one_padright_10 b-layout__one_width_100">\
													<div id="letters_item_status_1_'+resp.data[i].id+(tab==2 || tab==6 ? '_'+resp.data[i].nn : '')+'" class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_padtop_2 b-layout__txt_fontsize_11" style="visibility: hidden;">\
														<a href="#" class="b-layout__link b-layout__link_fontsize_11 b-layout__link_bordbot_dot_'+resp.data[i].user_status_1_color+'" onClick="letters.nn='+resp.data[i].nn+'; letters.formStatusShow('+resp.data[i].id+',1); return false;">\
															'+statuses_list[parseInt(resp.data[i].user_status_1)]+ (resp.data[i].user_status_1==2 || resp.data[i].user_status_1==3 ? '<br/>'+resp.data[i].user_status_date_1 : '<br/>')+'</a>\
													</div>\
												</td>\
												<td class="b-layout__one b-layout__one_width_110 ">\
													<div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_fontsize_11 b-layout__txt_padtop_2" id="letters_item_datechange_'+resp.data[i].id+(tab==2 || tab==6 ? '_'+resp.data[i].nn : '')+'">\
														<a href="#" class="b-layout__link b-layout__link_bordbot_dot_000" onClick="letters.nn='+resp.data[i].nn+'; letters.formDateChangeShow('+resp.data[i].id+', \'list\'); return false;">\
															'+resp.data[i].date_change_status+'\
														</a>\
													</div>\
												</td>\
											</tr>\
							 			    <tr class="b-layout__tr">\
											    <td class="b-layout__one b-layout__one_padright_10 b-layout__one_width_150">\
													<div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_fontsize_11 b-layout__txt_padtop_2">\
														'+(resp.data[i].is_user_2_company=="t" ? 
															'<span class="b-icon b-icon_'+resp.data[i].user_status_2_icon+' b-icon_margleft_-20 b-icon_top_2"></span><a href="/siteadmin/letters/?mode=edit&id='+resp.data[i].user_2+'" target="_blank" class="b-layout__link b-layout__link_fontsize_11">'+resp.data[i].company2_name+'</a> <a href="#" class="b-layout__link" onClick="letters.showByUser('+resp.data[i].user_2+', \''+resp.data[i].is_user_2_company+'\'); return false;"><span class="b-icon b-icon_sbr_folder b-icon_valign_middle"></span></a>\
															<br>\
															'+(resp.data[i].company2_index ? resp.data[i].company2_index+',' : '')+'\
							                            	'+(resp.data[i].company2_country_title ? resp.data[i].company2_country_title+',' : '')+'\
							                            	'+(resp.data[i].company2_city_title ? resp.data[i].company2_city_title+',' : '')+'\
															'+resp.data[i].company2_address
														:
															'<span class="b-icon b-icon_'+resp.data[i].user_status_2_icon+' b-icon_margleft_-20 b-icon_top_2"></span><a href="/users/'+resp.data[i].user2_login+'" target="_blank" class="b-layout__link b-layout__link_fontsize_11">'+(resp.data[i].user2_i_form_type==1 ? resp.data[i].user2_i_1_fio : resp.data[i].user2_i_2_full_name)+' ['+resp.data[i].user2_login+']</a> <a href="#" class="b-layout__link" onClick="letters.showByUser('+resp.data[i].user_2+', \''+resp.data[i].is_user_2_company+'\'); return false;"><span class="b-icon b-icon_sbr_folder b-icon_valign_middle"></span></a>\
															<br>\
															'+(resp.data[i].user2_i_form_type==1 ? resp.data[i].user2_i_1_address : resp.data[i].user2_i_2_address)
														)+
													'</div>\
							                    </td>\
												<td class="b-layout__one b-layout__one_padright_10 b-layout__one_width_100">\
													<div id="letters_item_status_2_'+resp.data[i].id+(tab==2 || tab==6 ? '_'+resp.data[i].nn : '')+'" class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_padtop_2 b-layout__txt_fontsize_11">\
														<a href="#" class="b-layout__link b-layout__link_fontsize_11 b-layout__link_bordbot_dot_'+resp.data[i].user_status_2_color+'" onClick="letters.nn='+resp.data[i].nn+'; letters.formStatusShow('+resp.data[i].id+',2); return false;">\
															'+statuses_list[parseInt(resp.data[i].user_status_2)]+ (resp.data[i].user_status_2==2 || resp.data[i].user_status_2==3 ? '<br/>'+resp.data[i].user_status_date_2 : '<br/>')+'</a>\
													</div>\
												</td>\
												<td class="b-layout__one b-layout__one_width_110 ">\
													<div class="b-layout__txt b-layout__txt_padbot_5 b-layout__txt_fontsize_11 b-layout__txt_padtop_2">&nbsp;</div>\
												</td>\
									        </tr>\
									        '+(html_user3)+'\
										</tbody>\
									</table>\
									<div class="b-layout__txt b-layout__txt_padbot_15 b-layout__txt_padleft_80">\
										<span><div class="b-combo b-combo_inline-block"><input id="letters_withoutourdocs_check_'+resp.data[i].id+'" type="checkbox" class="b-check__check" value="1" '+(resp.data[i].withoutourdoc=='t' ? 'checked' : '')+' onClick="letters.changeWithoutourdocs('+resp.data[i].id+');"></div> Документ без нашего экземпляра</span><br/>\
										<span id="letters_item_delivery_'+resp.data[i].id+(tab==2 || tab==6 ? '_'+resp.data[i].nn : '')+'">\
											'+(resp.data[i].delivery_title ? 
												'<a href="#" class="b-layout__link b-layout__link_bordbot_dot_000" onClick="letters.nn='+resp.data[i].nn+'; letters.formDeliveryShow('+resp.data[i].id+'); return false;">'+resp.data[i].delivery_title+'</a>. '
											:
												'<a href="#" class="b-layout__link b-layout__link_bordbot_dot_000" onClick="letters.nn='+resp.data[i].nn+'; letters.formDeliveryShow('+resp.data[i].id+'); return false;">Добавить доставку</a>. '
											)+
										'</span>\
										<span id="letters_item_deliverycost_'+resp.data[i].id+(tab==2 || tab==6 ? '_'+resp.data[i].nn : '')+'">\
											'+(resp.data[i].delivery_cost!='0.00' ? 
												'<a href="#" class="b-layout__link b-layout__link_bordbot_dot_000" onClick="letters.nn='+resp.data[i].nn+'; letters.formDeliveryCostShow('+resp.data[i].id+', \'list\'); return false;">Стоимость '+resp.data[i].delivery_cost+' рублей</a>. '
											:
												'<a href="#" class="b-layout__link b-layout__link_bordbot_dot_000" onClick="letters.nn='+resp.data[i].nn+'; letters.formDeliveryCostShow('+resp.data[i].id+', \'list\'); return false;">Добавить стоимость</a>.'
											)+
										'</span>\
										<br/>\
											'+(resp.data[i].parent && resp.data[i].parent_title ?
												'Документ связан с <a href="/siteadmin/letters/?page=doc&doc='+resp.data[i].parent+'" class="b-layout__link b-layout__link_color_000">ID'+resp.data[i].parent+' '+resp.data[i].parent_title+'</a><br/><br/>'
											: 
												'<br/>'
											)+
										'<span id="letters_item_comment_'+resp.data[i].id+(tab==2 || tab==6 ? '_'+resp.data[i].nn : '')+'">\
											'+(resp.data[i].comment ?
												resp.data[i].comment+'&nbsp;&nbsp;<a class="b-icon b-icon_margtop_4 b-icon_sbr_edit2" href="#" onClick="letters.nn='+resp.data[i].nn+'; letters.formCommentShow('+resp.data[i].id+'); return false;"></a>'
											:
												'<a href="#" class="b-layout__link b-layout__link_bordbot_dot_000" onClick="letters.nn='+resp.data[i].nn+'; letters.formCommentShow('+resp.data[i].id+'); return false;">Добавить примечание</a>'
											)+
										'</span>\
											'+(resp.data[i].file_link ?
												'<br/>\
												<span>\
												<a href="'+resp.data[i].file_link+'" class="b-layout__link b-layout__link_bordbot_dot_000">Электронная версия</a>\
												</span>'
											:
												''
											)+
									'</div>\
								</div>';
						html = html + "</div>";
					}
					html = html + resp.pager;
					$('letters_data').set('html', html);
				}
				if(tab==2 || tab==6) {
					if(letters.get_zip==1) { window.location = "/siteadmin/letters/get.php"; } letters.get_zip = 0;
				} else {

				}
				if(resp.data[0]) {
					$("letters_data").show();
					$("letters_notfound").hide();
				} else {
					$("letters_data").hide();
					$("letters_notfound").show();
				}
				$("letters_wrapper_view").hide();
				$("letters_wrapper").show();
				letters.spinner.hide();
			}
		}).post({
			'xjxfun': 'showLetters',
			'xjxargs': ['N'+tab, 'S'+filter, 'N'+page, 'N'+nums],
			'u_token_key': _TOKEN_KEY
		});
	},

	changeTabs: function(tab) {
		letters.resetTabs();
		letters.cur_tab = tab;
		letters.view_mode = tab;
		$('tabs').getElements('li:nth-child('+(tab*2)+')').setStyle("display", "none");
		$('tabs').getElements('li:nth-child('+(tab*2-1)+')').setStyle("display", "inline-block");
		if(tab==6) {
			$('letters_mass_action_div_menu_send').setStyle('display', 'inline');
		} else {
			$('letters_mass_action_div_menu_send').setStyle('display', 'none');
		}

		letters.buildList(tab, '', 1, letters.nums);
//		xajax_showLetters(tab, '', 1, letters.nums);
	},

	checkedRecalc: function() {
		var letterids = '';
		var obj = $('letters_wrapper').getElements('input[id^=letters_check_]');
		obj.each(function(el){
			if(el.get('checked')==true) {
				letterids = letterids+el.get('value')+',';
			}
		});
		$('letters_frm_mass_data_ids').set('value', letterids);
	},

	checkUncheclAll: function() {
		var count = 0;
		var obj = $('letters_wrapper').getElements('input[id^=letters_check_]');
		var obj_cover = $('letters_wrapper').getElements('input[id^=cover_]');
		if($('letters_check').get('checked')==true) {
			obj.each(function(el){
    			count++;
			});
			obj.set('checked', true);
			if(obj_cover) { obj_cover.set('checked', true); }
		} else {
			obj.set('checked', false);
			if(obj_cover) { obj_cover.set('checked', false); }
		}
		$('letters_mass_action_div_selected_docs').set('html', count);
		letters.checkedRecalc();
	},

	checkUncheck: function(obj) {
		if($(obj.id).get('checked')==true) {
			$('letters_mass_action_div_selected_docs').set('html', parseInt($('letters_mass_action_div_selected_docs').get('html'))+1);
		} else {
			$('letters_mass_action_div_selected_docs').set('html', parseInt($('letters_mass_action_div_selected_docs').get('html'))-1);
		}
		letters.checkedRecalc();
	},

	uncheckAll: function() {
		var obj = $('letters_wrapper').getElements('input[id^=letters_check_]');
		obj.set('checked', false);
		$('letters_check').set('checked', false);
		$('letters_mass_action_div_selected_docs').set('html', 0);
		$('letters_frm_mass_data_ids').set('value', '');
		letters.checkedRecalc();
	},

	checkUncheckCover: function(cover, obj) {
		var letters_o = $('letters_wrapper').getElements('input[numcover='+cover+']');
		var count = 0;
		if($(obj.id).get('checked')==true) {
			letters_o.each(function(el){
	    		if(el.get('checked')==false) count++;
			});
			letters_o.set('checked', true);
			$('letters_mass_action_div_selected_docs').set('html', parseInt($('letters_mass_action_div_selected_docs').get('html'))+count);
		} else {
			letters_o.each(function(el){
	    		if(el.get('checked')==true) count++;
			});
			letters_o.set('checked', false);
			$('letters_mass_action_div_selected_docs').set('html', parseInt($('letters_mass_action_div_selected_docs').get('html'))-count);
		}
		letters.checkedRecalc();
	},

	changeWithoutourdocs: function(id) {
		letters.spinner.show();
		xajax_changeWithoutourdocs(id, $('letters_withoutourdocs_check_'+id).get('checked') );
	},

	massEditStatus: function() {
		if($('letters_frm_mass_data_ids').get('value')) {                        
			letters.resetDialogs();
			letters.spinner.show();
			$('letters_mass_action_status_div_fld_ids').set('value', $('letters_frm_mass_data_ids').get('value'));
			xajax_showMassStatus($('letters_frm_mass_data_ids').get('value'));
			$('letters_data').getElements("input[type=checkbox]").each(
	                function (item){
	                    item.disabled = true;
	                }
	        );
		} else {
			alert('Вы не выбрали документы');
		}
	},

	massEditCost: function() {
		if($('letters_frm_mass_data_ids').get('value')) {                        
			letters.resetDialogs();
			$('letters_form_mass_deliverycost_field_data').set('value', '');
			$('letters_mass_action_cost_div_fld_ids').set('value', $('letters_frm_mass_data_ids').get('value'));
			$('letters_form_mass_deliverycost').setStyle('display', 'block');
			$('letters_form_mass_deliverycost').getChildren('div').removeClass('b-shadow_hide');
			window.location = '#letters_form_mass_deliverycost_a';
			$('letters_data').getElements("input[type=checkbox]").each(
	                function (item){
	                    item.disabled = true;
	                }
	        );
		} else {
			alert('Вы не выбрали документы');
		}
	},

	massEditDate: function() {
		if($('letters_frm_mass_data_ids').get('value')) {                        
			letters.resetDialogs();
			ComboboxManager.getInput("letters_form_mass_date_field_data").setDate();
			$('letters_mass_action_date_div_fld_ids').set('value', $('letters_frm_mass_data_ids').get('value'));
			$('letters_form_mass_date').setStyle('display', 'block');
			$('letters_form_mass_date').getChildren('div').removeClass('b-shadow_hide');
			window.location = '#letters_form_mass_date_a';
			$('letters_data').getElements("input[type=checkbox]").each(
	                function (item){
	                    item.disabled = true;
	                }
	        );
		} else {
			alert('Вы не выбрали документы');
		}
	},

	processDocs: function() {
		if($('letters_frm_mass_data_ids').get('value')) {
			letters.spinner.show();
			xajax_processDocs($('letters_frm_mass_data_ids').get('value'));
		} else {
			alert('Вы не выбрали документы');
		}
	},

	processSendDocs: function() {
		if($('letters_frm_mass_data_ids').get('value')) {
			letters.spinner.show();
			var data = $('letters_frm_mass_data_ids').get('value');
			var userids = '';
			var deliveryids = '';
			var obj = $('letters_wrapper').getElements('input[id^=letters_check_]');
			obj.each(function(el){
				if(el.get('checked')==true) {
					userids = userids+el.get('u_res')+',';
					deliveryids = deliveryids+el.get('u_delivery')+',';
				}
			});
			data = data+'|'+userids+'|'+deliveryids;
			xajax_processSendDocs(data);
		} else {
			alert('Вы не выбрали документы');
		}
	},

	getArchive: function() {
		letters.get_zip = 1;
		letters.reload_data();
	},

	massDivHideStatus: function() {
		$('letters_data').getElements("input[type=checkbox]").each(
                function (item){
                    item.disabled = false;
                }
        );
		$('letters_mass_action_status_div').setStyle('display', 'none');
	},

	massShowChangeStatus: function(status) {
        var el1 = $('letters_mass_action_status_div_lnk_'+status);
        var el2 = $('letters_doc_frm_div_statuses');
        el2.inject(el1, 'after');
        ComboboxManager.getInput('letters_doc_frm_div_statuses_st_date_2').setDate();
        ComboboxManager.getInput('letters_doc_frm_div_statuses_st_date_3').setDate();
        var val = $('letters_mass_action_status_div_fld_'+status).get('value');
        if(val=='') { val = 0; }
        var d_val = $('letters_mass_action_status_div_fld_'+status+'_date').get('value');
        $('letters_doc_frm_div_statuses_st_'+val).set('checked', true);
        letters.changeStatus('newpopup', val);
        if(val==2 || val==3) {
            ComboboxManager.getInput('letters_doc_frm_div_statuses_st_date_'+val).setDate(d_val);
        }
        letters.changeStatus('newpopup', val);
        $('letters_doc_frm_div_statuses').setStyle('display', 'block');
        $('letters_doc_frm_div_statuses').getChildren('div').removeClass('b-shadow_hide');

		$('letters_doc_frm_div_statuses_btn_submit').set('onclick', 'letters.massSetStatus('+status+'); return false;');
	},

	massUpdateStatus: function() {
		letters.spinner.show();
		letters.massDivHideStatus();
		xajax_updateMassStatus(xajax.getFormValues('letters_mass_action_status_div_frm'));
	},

	massUpdateDeliveryCost: function() {
		letters.spinner.show();
		letters.formMassDeliveryCostHide();
		xajax_updateMassDeliveryCost($('letters_mass_action_cost_div_fld_ids').get('value'), $('letters_form_mass_deliverycost_field_data').get('value'));
	},

	massUpdateDate: function() {
		letters.spinner.show();
		letters.formMassDateHide();
		xajax_updateMassDate($('letters_mass_action_date_div_fld_ids').get('value'), $('letters_form_mass_date_field_data_eng_format').get('value'));
	},

	massSetStatus: function(status) {
		var error = 0;
		val = $$('input[name=letters_doc_frm_div_statuses_st]:checked')[0].get('value');
		if(val==2 || val==3) {
			if($('letters_doc_frm_div_statuses_st_date_'+val+'_eng_format').get('value') == '') {
				error = 1;
			}
		}

		if(error==0) {
			var status_add = '';
			$('letters_mass_action_status_div_fld_'+status).set('value', val);
			if(val==2 || val==3) {
				var d = $('letters_doc_frm_div_statuses_st_date_'+val+'_eng_format').get('value');
				$('letters_mass_action_status_div_fld_'+status+'_date').set('value', d);
				status_add = ' '+d.substr(8,2)+'.'+d.substr(5,2)+'.'+d.substr(0,4);
			} else {
				$('letters_mass_action_status_div_fld_'+status+'_date').set('value', '');
			}
			$('letters_mass_action_status_div_lnk_'+status).set('html', statuses_list[val]+status_add);
			letters.massHideStatus();
		}

	},

	massHideStatus: function() {
		$('letters_doc_frm_div_statuses').setStyle('display', 'none');
		$('letters_doc_frm_div_statuses').getChildren('div').addClass('b-shadow_hide');
        var el1 = $('letters_form_start');
        var el2 = $('letters_doc_frm_div_statuses');
        el2.inject(el1, 'after');
	},

	massCalcDelivery: function() {
		if($('letters_frm_mass_data_ids').get('value')) {
			letters.spinner.show();
			xajax_calcDeliveryCost($('letters_frm_mass_data_ids').get('value'));
		} else {
			alert('Вы не выбрали документы');
		}
	},

	showGroup: function(id) {
		letters.resetTabs();
		letters.view_mode = 10;
		letters.view_data = id;
		letters.spinner.show();
		xajax_showGroup(id);
	},

	showByUser: function(id, is_company) {
		letters.resetTabs();
		letters.view_mode = 11;
		letters.view_data = id;
		letters.view_data1 = is_company;
		letters.spinner.show();
		xajax_showByUser(id, is_company);
	},
    
    changePage: function(tab, page) {
        window.location = '#is_top';
		letters.buildList(tab, letters.filter, page, letters.nums);
        //xajax_showLetters(tab, letters.filter, page, letters.nums);
    },

    changeNums: function(tab, nums) {
        letters.nums = nums;
		letters.buildList(tab, letters.filter, 1, nums);
        //xajax_showLetters(tab, letters.filter, 1, nums);
    },
    
	changeStatus: function(frm, val) {
		var f_div = '';
		switch(frm) {
			case 'addForm':
				f_div = 'letters_doc_frm_status_date_div_';
				break;
			case 'filter':
				f_div = 'letters_filter_status_date_div_'
				break;
			case 'popup':
				f_div = 'letters_form_status_date_div_';
				break;
			case 'newpopup':
				f_div = 'letters_doc_frm_div_statuses_st_date_div_';
				break;
		}
		$(f_div+'2').setStyle('visibility', 'hidden');
		$(f_div+'3').setStyle('visibility', 'hidden');
		if(val==2 || val==3) {
			$(f_div+val).setStyle('visibility', 'visible');
		}
	},

	searchDocs: function() {
		letters.resetTabs();
		letters.view_mode = 40;
		letters.view_data = '';
		$('letters_filter_search_fld2').set('value', $('letters_search_frm_field').get('value'));
        letters.filter = xajax.getFormValues('letters_filter');

		letters.buildList(0, letters.filter, 1, letters.nums);
		//xajax_showLetters(0, letters.filter, 1, letters.nums);
	},

	clearSearch: function() {
		$('letters_filter_search_fld2').set('value','');
		ComboboxManager.getInput("letters_filter_add_user").clear();
		$('letters_filter_id').set('value','');
		$('letters_filter_group').set('title', '');
		ComboboxManager.getInput("letters_filter_group").clear();
		$('letters_filter_delivery').set('title', '');
		$('letters_filter_withoutourdoc').set('checked', false);
		ComboboxManager.getInput("letters_filter_delivery").clearSelection();
		ComboboxManager.getInput("letters_filter_get_user").clear();
		ComboboxManager.getInput("letters_filter_status_date_2").setDate();
		ComboboxManager.getInput("letters_filter_status_date_3").setDate();
		ComboboxManager.getInput("letters_filter_change_date_s").setDate();
		ComboboxManager.getInput("letters_filter_change_date_e").setDate();
		ComboboxManager.getInput("letters_filter_add_date_s").setDate();
		ComboboxManager.getInput("letters_filter_add_date_e").setDate();
		$$('input[name=letters_filter_status]').set('checked', false);
		$('letters_filter_status_date_div_2').setStyle('visibility','hidden');
		$('letters_filter_status_date_div_3').setStyle('visibility','hidden');
	},

	showAddForm: function() {
		letters.resetDialogs();

		$('l_form_1').set('html', 'Новый документ <div id="l_form_1_1" class="b-layout__txt b-layout__txt_inline-block"><a class="b-button b-button_poll_plus" href="" onClick="letters.M_InsertNewDoc(); return false;"></a> <a class="b-layout__link b-layout__link_fontsize_15 b-layout__link_bordbot_dot_0f71c8" href="" onClick="letters.M_InsertNewDoc(); return false;">добавить</a></div>');
		letters.countDocM = 1;
		letters.curDocM = 1;
		letters.MData = [];


		$('f_button_actionwork_txt').set('html', 'Создать');
		$('f_button_actionwork').set('onClick', 'letters.addDocument(); return false;');

		$('l_form_1').setStyle('display', 'block');
		$('l_form_10').setStyle('display', 'block');
		$('l_form_3').setStyle('display', 'block');
		$('l_form_4').setStyle('display', 'block');
		$('l_form_5').setStyle('display', 'block');

		$('letters_doc_frm_title').getParent().removeClass('b-combo__input_error');
		$('letters_doc_frm_title').set('title', '');
		$('letters_doc_frm_group').set('title', '');
		$('letters_doc_frm_user_1').set('title', '');
		$('letters_doc_frm_user_2').set('title', '');
		$('letters_doc_frm_user_3').set('title', '');
		$('letters_doc_frm_delivery').set('title', '');
		$('letters_doc_frm_delivery_cost').set('title', '');

		$('letters_doc_frm_title').set('value', '');
		$('letters_doc_frm_delivery_cost').set('value', '');
		$('letters_doc_frm_comment').set('value', '');
		ComboboxManager.getInput("letters_doc_frm_group").clear();
		ComboboxManager.getInput("letters_doc_frm_delivery").clearSelection();		
		ComboboxManager.getInput("letters_doc_frm_user_1").clear();
		ComboboxManager.getInput("letters_doc_frm_user_2").clear();
		ComboboxManager.getInput("letters_doc_frm_user_3").clear();
		ComboboxManager.getInput("letters_doc_frm_parent").clear();
		ComboboxManager.getInput("letters_doc_frm_dateadd").setDate();
		$('letters_doc_frm_user1_status_data').set('value', 0);
		$('letters_doc_frm_user1_status_date_data').set('value', '');
		$('letters_doc_frm_user_1_status_change_lnk').set('html', statuses_list[0]);
		$('letters_doc_frm_user2_status_data').set('value', 0);
		$('letters_doc_frm_user2_status_date_data').set('value', '');
		$('letters_doc_frm_user_2_status_change_lnk').set('html', statuses_list[0]);
		$('letters_doc_frm_user3_status_data').set('value', 0);
		$('letters_doc_frm_user3_status_date_data').set('value', '');
		$('letters_doc_frm_user_3_status_change_lnk').set('html', statuses_list[0]);

		$('letters_doc_frm_withoutourdoc').set('checked', false);

		ComboboxManager.getInput("letters_doc_frm_user_1").loadRecord('4', "get_user_or_company_info", "type=company");

		if($('letters_doc_frm_user_3_div').getStyle('display')=='block') {
			letters.toggleUser3();
		}
		status_can_submit = true;
		xajax_resetAttachedFiles();
		$('letters_add_div').getChildren('.b-shadow').removeClass('b-shadow_hide');
	},

	hideAddForm: function() {
		$('letters_add_div').getChildren('.b-shadow').addClass('b-shadow_hide');
	},

	addDocument: function() {
		if(status_can_submit==true) {
			var data = '';

				letters.M_Save(letters.curDocM);
				var jObject={};
				jObject['count_docs'] = letters.countDocM;
    			for(i in letters.MData) {
    				if (!letters.MData.hasOwnProperty(i)) continue;
    				for(k in letters.MData[i]) {
    					if (!letters.MData[i].hasOwnProperty(k)) continue;
        				jObject[i+'-'+k] = letters.MData[i][k];
        			}
    			}
				data = JSON.stringify(jObject);


			xajax_addLetter(xajax.getFormValues('letters_doc_frm'), data);
			status_can_submit = false;
		}
	},

	toggleUser3: function() {
		if($('letters_doc_frm_user_3_div').getStyle('display')=='none') {
			$('letters_doc_frm_user_3_div').setStyle('display', 'block');
			$('letters_doc_frm_user_3_addlnk').set('html', 'Удалить третью сторону');
			$('letters_doc_frm_user_3_addlnk').getPrevious().removeClass('b-button_poll_plus');
			$('letters_doc_frm_user_3_addlnk').getPrevious().addClass('b-button_poll_minus');
		} else {
			$('letters_doc_frm_user_3_div').setStyle('display', 'none');
			$('letters_doc_frm_user_3_addlnk').set('html', 'Добавить третью сторону');
			$('letters_doc_frm_user_3_addlnk').getPrevious().addClass('b-button_poll_plus');
			$('letters_doc_frm_user_3_addlnk').getPrevious().removeClass('b-button_poll_minus');
		}
		$('letters_doc_frm_user3_status_data').set('value', 0);
		$('letters_doc_frm_user3_status_date_data').set('value', '');
		ComboboxManager.getInput("letters_doc_frm_user_3").clear();
		$('letters_doc_frm_user_3_status_change_lnk').set('html', statuses_list[0]);
	},

	changeUsersQuery: function(num) {
		$('letters_doc_frm_user1_query_n').setStyle('display', 'inline-block');
		$('letters_doc_frm_user2_query_n').setStyle('display', 'inline-block');
		$('letters_doc_frm_user3_query_n').setStyle('display', 'inline-block');
		$('letters_doc_frm_user1_query_a').setStyle('display', 'none');
		$('letters_doc_frm_user2_query_a').setStyle('display', 'none');
		$('letters_doc_frm_user3_query_a').setStyle('display', 'none');

		$('letters_doc_frm_user'+num+'_query_a').setStyle('display', 'inline-block');
		$('letters_doc_frm_user'+num+'_query_n').setStyle('display', 'none');

		$('letters_doc_frm_user_query').set('value', num);
	},

	showDoc: function(id) {
		letters.mode = 'item';
		letters.spinner.show();
		letters.view_mode = 20;
		letters.view_data = id;
		xajax_showDoc(id);	
		$('letters_wrapper').setStyle('display', 'none');
		$('letters_wrapper_view').setStyle('display', 'block');
	},

	editDoc: function(id) {
		letters.resetDialogs();
		letters.mode = 'item';
		letters.spinner.show();

		$('letters_doc_frm_title').getParent().removeClass('b-combo__input_error');
		$('letters_doc_frm_title').set('title', '');
		$('letters_doc_frm_group').set('title', '');
		$('letters_doc_frm_user_1').set('title', '');
		$('letters_doc_frm_user_2').set('title', '');
		$('letters_doc_frm_user_3').set('title', '');
		$('letters_doc_frm_delivery').set('title', '');
		$('letters_doc_frm_delivery_cost').set('title', '');
		ComboboxManager.getInput("letters_doc_frm_dateadd").setDate();
		$('letters_doc_frm_withoutourdoc').set('checked', false);

		$('l_form_1_1').setStyle('display', 'none');
		$('l_form_1_2').setStyle('display', 'none');
		$('l_form_1').setStyle('display', 'none');
		$('l_form_10').setStyle('display', 'none');
		$('l_form_3').setStyle('display', 'none');
		$('l_form_4').setStyle('display', 'none');
		$('l_form_5').setStyle('display', 'none');
		$('f_button_actionwork_txt').set('html', 'Сохранить');
		$('f_button_actionwork').set('onClick', 'letters.saveDocument(); return false;');
		letters.docid = id;
		status_can_submit = true;
		xajax_editDoc(id);	
	},

	saveDocument: function() {
		if(status_can_submit==true) {
			xajax_saveLetter(letters.docid, xajax.getFormValues('letters_doc_frm'));
			status_can_submit = false;
		}
	},

	editDocHide: function() {
		$('letters_edit_doc_div').getChildren('div').addClass('b-shadow_hide');
		letters.mode = 'item';
		letters.spinner.hide();
	},

	delDocument: function(id) {
		letters.spinner.show();
		xajax_delDoc(id);
	},


	statusesShow: function(obj, num) {
		$('letters_doc_frm_div_statuses_btn_submit').set('onclick', 'letters.statusesSet(); return false;');
		letters.status_num = num;
        var el1 = obj;
        var el2 = $('letters_doc_frm_div_statuses');
        el2.inject(el1, 'after');
        ComboboxManager.getInput("letters_doc_frm_div_statuses_st_date_2").setDate();
        ComboboxManager.getInput("letters_doc_frm_div_statuses_st_date_3").setDate();
        var val = $('letters_doc_frm_user'+letters.status_num+'_status_data').get('value');
        var d_val = $('letters_doc_frm_user'+letters.status_num+'_status_date_data').get('value');
        $('letters_doc_frm_div_statuses_st_'+val).set('checked', true);
        letters.changeStatus('newpopup', val);
        if(val==2 || val==3) {
			ComboboxManager.getInput("letters_doc_frm_div_statuses_st_date_"+val).setDate(d_val);
		}
		$('letters_doc_frm_div_statuses').setStyle('display', 'block');
		$('letters_doc_frm_div_statuses').getChildren('div').removeClass('b-shadow_hide');
	},

	statusesHide: function() {
		$('letters_doc_frm_div_statuses').setStyle('display', 'none');
		$('letters_doc_frm_div_statuses').getChildren('div').addClass('b-shadow_hide');
        var el1 = $('letters_form_start');
        var el2 = $('letters_doc_frm_div_statuses');
        el2.inject(el1, 'after');
	},

	statusesSet: function() {
		var error = 0;
		val = $$('input[name=letters_doc_frm_div_statuses_st]:checked')[0].get('value');
		if(val==2 || val==3) {
			if($('letters_doc_frm_div_statuses_st_date_'+val+'_eng_format').get('value') == '') {
				error = 1;
			}
		}

		if(error==0) {
			var status_add = '';
			$('letters_doc_frm_user'+letters.status_num+'_status_data').set('value', val);
			if(val==2 || val==3) {
				var d = $('letters_doc_frm_div_statuses_st_date_'+val+'_eng_format').get('value');
				$('letters_doc_frm_user'+letters.status_num+'_status_date_data').set('value', d);
				status_add = ' '+d.substr(8,2)+'.'+d.substr(5,2)+'.'+d.substr(0,4);
			} else {
				$('letters_doc_frm_user'+letters.status_num+'_status_date_data').set('value', '');
			}
			$('letters_doc_frm_user_'+letters.status_num+'_status_change_lnk').set('html', statuses_list[val]+status_add);
			letters.statusesHide();
		}
	},




	formCommentShow: function(id) {
		letters.resetDialogs();
		letters.docid = id;
        var ann = '';
        if(letters.nn!=0) {
            ann = '_'+letters.nn;
        } else {
            ann = '';
        }
        var el1 = $('letters_item_comment_'+id+ann);
        var el2 = $('letters_form_comment');
        el2.inject(el1, 'after');
		$('letters_form_comment').setStyle('display', 'block');
		$('letters_form_comment').getChildren('div').removeClass('b-shadow_hide');
		$('letters_form_comment_field_data').set('html', '');
		xajax_getDocField(id, 'comment');
	},

	formCommentHide: function() {
		$('letters_form_comment').setStyle('display', 'none');
		$('letters_form_comment').getChildren('div').addClass('b-shadow_hide');
        var el1 = $('letters_form_start');
        var el2 = $('letters_form_comment');
        el2.inject(el1, 'after');
	},

	formCommentUpdate: function() {
		letters.spinner.show();
		letters.formCommentHide();
		xajax_updateDocField(letters.docid, 'comment', $('letters_form_comment_field_data').get('value'), letters.mode);
	},

	formDeliveryShow: function(id) {
		letters.resetDialogs();
		letters.docid = id;
        var ann = '';
        if(letters.nn!=0) {
            ann = '_'+letters.nn;
        } else {
            ann = '';
        }
        var el1 = $('letters_item_delivery_'+id+ann);
        var el2 = $('letters_form_delivery');
        el2.inject(el1, 'after');
		$('letters_form_delivery').setStyle('display', 'block');
		$('letters_form_delivery').getChildren('div').removeClass('b-shadow_hide');
		xajax_getDocField(id, 'delivery');
	},

	formDeliveryHide: function() {
		$('letters_form_delivery').setStyle('display', 'none');
		$('letters_form_delivery').getChildren('div').addClass('b-shadow_hide');
        var el1 = $('letters_form_start');
        var el2 = $('letters_form_delivery');
        el2.inject(el1, 'after');
	},

	formDeliveryUpdate: function() {
		letters.spinner.show();
		letters.formDeliveryHide();
		xajax_updateDocField(letters.docid, 'delivery', xajax.getFormValues('letters_form_delivery_form'), letters.mode);
	},

	formDeliveryCostShow: function(id, type) {
		letters.resetDialogs();
		letters.docid = id;
        var ann = '';
        if(letters.nn!=0) {
            ann = '_'+letters.nn;
        } else {
            ann = '';
        }
        var el1 = $('letters_item_deliverycost_'+id+ann);
        var el2 = $('letters_form_deliverycost');
        el2.inject(el1, 'after');
        switch(type) {
        	case 'list':
        		$('letters_form_deliverycost').setStyle('left', '350px');
        		break;
        	case 'view':
        		$('letters_form_deliverycost').setStyle('left', '430px');
        		break;

        }
		$('letters_form_deliverycost').setStyle('display', 'block');
		$('letters_form_deliverycost').getChildren('div').removeClass('b-shadow_hide');
		xajax_getDocField(id, 'delivery_cost');
	},

	formDateAddShow: function(id, type) {
		letters.resetDialogs();
		letters.docid = id;
        var ann = '';
        if(letters.nn!=0) {
            ann = '_'+letters.nn;
        } else {
            ann = '';
        }
        var el1 = $('letters_item_dateadd_'+id+ann);
        var el2 = $('letters_form_dateadd');
        el2.inject(el1, 'after');
        switch(type) {
        	case 'list':
        		$('letters_form_dateadd').setStyle('left', '350px');
        		break;
        	case 'view':
        		$('letters_form_dateadd').setStyle('left', '430px');
        		break;

        }
		$('letters_form_dateadd').setStyle('display', 'block');
		$('letters_form_dateadd').getChildren('div').removeClass('b-shadow_hide');
		xajax_getDocField(id, 'dateadd');
	},

	formDateChangeShow: function(id, type) {
		letters.resetDialogs();
		letters.docid = id;
        var ann = '';
        if(letters.nn!=0) {
            ann = '_'+letters.nn;
        } else {
            ann = '';
        }
        var el1 = $('letters_item_datechange_'+id+ann);
        var el2 = $('letters_form_datechange');
        el2.inject(el1, 'after');
        switch(type) {
        	case 'list':
        		$('letters_form_datechange').setStyle('left', '350px');
        		break;
        	case 'view':
        		$('letters_form_datechange').setStyle('left', '430px');
        		break;

        }
		$('letters_form_datechange').setStyle('display', 'block');
		$('letters_form_datechange').getChildren('div').removeClass('b-shadow_hide');
		xajax_getDocField(id, 'datechange');
	},

	formDeliveryCostHide: function() {
		$('letters_form_deliverycost').setStyle('display', 'none');
		$('letters_form_deliverycost').getChildren('div').addClass('b-shadow_hide');
        var el1 = $('letters_form_start');
        var el2 = $('letters_form_deliverycost');
        el2.inject(el1, 'after');
	},

	formDateAddHide: function() {
		$('letters_form_dateadd').setStyle('display', 'none');
		$('letters_form_dateadd').getChildren('div').addClass('b-shadow_hide');
        var el1 = $('letters_form_start');
        var el2 = $('letters_form_dateadd');
        el2.inject(el1, 'after');
	},

	formDateChangeHide: function() {
		$('letters_form_datechange').setStyle('display', 'none');
		$('letters_form_datechange').getChildren('div').addClass('b-shadow_hide');
        var el1 = $('letters_form_start');
        var el2 = $('letters_form_datechange');
        el2.inject(el1, 'after');
	},

	formMassDeliveryCostHide: function() {
		$('letters_form_mass_deliverycost').setStyle('display', 'none');
		$('letters_form_mass_deliverycost').getChildren('div').addClass('b-shadow_hide');
		$('letters_data').getElements("input[type=checkbox]").each(
                function (item){
                    item.disabled = false;
                }
        );
	},

	formMassDateHide: function() {
		$('letters_form_mass_date').setStyle('display', 'none');
		$('letters_form_mass_date').getChildren('div').addClass('b-shadow_hide');
		$('letters_data').getElements("input[type=checkbox]").each(
                function (item){
                    item.disabled = false;
                }
        );
	},

	formDeliveryCostUpdate: function() {
		letters.spinner.show();
		letters.formDeliveryCostHide();
		xajax_updateDocField(letters.docid, 'delivery_cost', $('letters_form_deliverycost_field_data').get('value'), letters.mode);
	},

	formDateAddUpdate: function() {
		letters.spinner.show();
		letters.formDateAddHide();
		xajax_updateDocField(letters.docid, 'dateadd', $('letters_form_dateadd_field_data_eng_format').get('value'), letters.mode);
	},

	formDateChangeUpdate: function() {
		letters.spinner.show();
		letters.formDateChangeHide();
		xajax_updateDocField(letters.docid, 'datechange', $('letters_form_datechange_field_data_eng_format').get('value'), letters.mode);
	},

	formStatusShow: function(id, num) {
		letters.resetDialogs();
		letters.docid = id;
		letters.spinner.show();
		letters.status_num = num;
		$('letters_doc_frm_user_query').set('value', num);
		$('letters_doc_frm_div_statuses_btn_submit').set('onclick', 'letters.formStatusUpdate(); return false;');
		xajax_getDocField(id, 'status');
	},

	formStatusHide: function() {
		$('letters_doc_frm_div_statuses').setStyle('display', 'none');
		$('letters_doc_frm_div_statuses').getChildren('div').addClass('b-shadow_hide');
        var el1 = $('letters_form_start');
        var el2 = $('letters_doc_frm_div_statuses');
        el2.inject(el1, 'after');
	},

	formStatusUpdate: function() {
		var error = 0;
		val = $$('input[name=letters_doc_frm_div_statuses_st]:checked')[0].get('value');
		if(val==2 || val==3) {
			if($('letters_doc_frm_div_statuses_st_date_'+val+'_eng_format').get('value') == '') {
				error = 1;
			}
		}

		if(error==0) {
			var status_add = '';
			$('letters_doc_frm_user'+letters.status_num+'_status_data').set('value', val);
			if(val==2 || val==3) {
				var d = $('letters_doc_frm_div_statuses_st_date_'+val+'_eng_format').get('value');
				$('letters_doc_frm_user'+letters.status_num+'_status_date_data').set('value', d);
				status_add = ' '+d.substr(8,2)+'.'+d.substr(5,2)+'.'+d.substr(0,4);
			} else {
				$('letters_doc_frm_user'+letters.status_num+'_status_date_data').set('value', '');
			}
			letters.spinner.show();
			letters.formStatusHide();
			xajax_updateDocField(letters.docid, 'status', xajax.getFormValues('letters_doc_frm'), letters.mode);
		}
	},

	showCompanies: function(s) {
		letters.spinner.show();
		xajax_showCompanies(s);
	},

	submitCompany: function(action) {
		var error = 0;
		if($('frm_company_name').get('value')=='') {
			$('frm_company_name').set('title', 'Вы не ввелили название');
			$('frm_company_name').getParent().addClass('b-combo__input_error');
			error = 1;
		}
		if($('frm_company_index').get('value')=='') {
			$('frm_company_index').set('title', 'Вы не ввелили индекс');
			$('frm_company_index').getParent().addClass('b-combo__input_error');
			error = 1;
		}
		if($('frm_company_address').get('value')=='') {
			$('frm_company_address').set('title', 'Вы не ввелили адрес');
			$('frm_company_address').getParent().addClass('b-combo__input_error');
			error = 1;
		}

		var error_country = 0;
		if(!($('frm_company').getElement('input[name=country_columns[1]]') && $('frm_company').getElement('input[name=country_columns[0]]'))) {
			error_country = 1;
		} else {
			if($('frm_company').getElement('input[name=country_columns[1]]').get('value')==0 || $('frm_company').getElement('input[name=country_columns[0]]')==0) {
				error_country = 1;
			}
		}

		if(error_country==1) {
			error = 1;
			$('frm_company').getElement('input[name=frm_company_countrycity]').set('title', 'Вы не выбрали страну и город');
			$('frm_company').getElement('input[name=frm_company_countrycity]').getParent().addClass('b-combo__input_error');
		}

		if(error==0) {
			$('frm_company').submit();
		}

	},

	countDocM : 1,
	curDocM : 1,
	MData : [],

	M_InsertNewDoc: function() {
		letters.M_Save(letters.curDocM);

		var t_html = '';
		letters.countDocM++;
		letters.curDocM = letters.countDocM;
		for(var i=1; i<=letters.countDocM; i++) {
			if(i==letters.curDocM) {
				t_html = t_html + 'Документ '+i+' ';
			} else {
				t_html = t_html + '<a class="b-layout__link b-layout__link_fontsize_18 b-layout__link_bordbot_dot_0f71c8" href="" onClick="letters.M_ShowDoc('+i+', true); return false;">Документ '+i+'</a> ';
			}
		}
		$('l_form_1').set('html', t_html+ ' <div id="l_form_1_1" class="b-layout__txt b-layout__txt_inline-block"><a class="b-button b-button_poll_plus" href="" onClick="letters.M_InsertNewDoc(); return false;"></a> <a class="b-layout__link b-layout__link_fontsize_15 b-layout__link_bordbot_dot_0f71c8" href="" onClick="letters.M_InsertNewDoc(); return false;">добавить</a></div>');
		letters.M_Reset();
		letters.M_Restore(1);
	},

	M_Save: function(num) {
		letters.MData[num-1] = [];
		letters.MData[num-1]['letters_doc_frm_title'] = $('letters_doc_frm_title').get('value');
		letters.MData[num-1]['letters_doc_frm_group_db_id'] = $('letters_doc_frm_group_db_id').get('value');
		letters.MData[num-1]['letters_doc_frm_group'] = $('letters_doc_frm_group').get('value');
		letters.MData[num-1]['letters_doc_frm_user_1_db_id'] = $('letters_doc_frm_user_1_db_id').get('value');
		letters.MData[num-1]['letters_doc_frm_user_1_section'] = $('letters_doc_frm_user_1_section').get('value');
		letters.MData[num-1]['letters_doc_frm_user_2_db_id'] = $('letters_doc_frm_user_2_db_id').get('value');
		letters.MData[num-1]['letters_doc_frm_user_2_section'] = $('letters_doc_frm_user_2_section').get('value');
		letters.MData[num-1]['letters_doc_frm_user_3_db_id'] = $('letters_doc_frm_user_3_db_id').get('value');
		letters.MData[num-1]['letters_doc_frm_user_3_section'] = $('letters_doc_frm_user_3_section').get('value');
		letters.MData[num-1]['letters_doc_frm_delivery_db_id'] = $('letters_doc_frm_delivery_db_id').get('value');
		letters.MData[num-1]['letters_doc_frm_delivery_cost'] = $('letters_doc_frm_delivery_cost').get('value');
		letters.MData[num-1]['letters_doc_frm_parent_db_id'] = $('letters_doc_frm_parent_db_id').get('value');
		letters.MData[num-1]['letters_doc_frm_comment'] = $('letters_doc_frm_comment').get('value');

		letters.MData[num-1]['letters_doc_frm_withoutourdoc'] = ($('letters_doc_frm_withoutourdoc').get('checked') ? 1 : 0);

		letters.MData[num-1]['letters_doc_frm_user1_status_data'] = $('letters_doc_frm_user1_status_data').get('value');
		letters.MData[num-1]['letters_doc_frm_user2_status_data'] = $('letters_doc_frm_user2_status_data').get('value');
		letters.MData[num-1]['letters_doc_frm_user3_status_data'] = $('letters_doc_frm_user3_status_data').get('value');

		letters.MData[num-1]['letters_doc_frm_user1_status_date_data'] = $('letters_doc_frm_user1_status_date_data').get('value');
		letters.MData[num-1]['letters_doc_frm_user2_status_date_data'] = $('letters_doc_frm_user2_status_date_data').get('value');
		letters.MData[num-1]['letters_doc_frm_user3_status_date_data'] = $('letters_doc_frm_user3_status_date_data').get('value');

	},

	M_Reset: function() {
		$('letters_doc_frm_title').set('value', '');
		$('letters_doc_frm_delivery_cost').set('value', '');
		$('letters_doc_frm_comment').set('value', '');
		$('letters_doc_frm_withoutourdoc').set('checked', false);
		ComboboxManager.getInput("letters_doc_frm_group").clear();
		ComboboxManager.getInput("letters_doc_frm_delivery").reload();		
		ComboboxManager.getInput("letters_doc_frm_user_1").clear();
		ComboboxManager.getInput("letters_doc_frm_user_2").clear();
		ComboboxManager.getInput("letters_doc_frm_user_3").clear();
		ComboboxManager.getInput("letters_doc_frm_parent").clear();
		//ComboboxManager.getInput("letters_doc_frm_dateadd").setDate();
		$('letters_doc_frm_user1_status_data').set('value', 0);
		$('letters_doc_frm_user1_status_date_data').set('value', '');
		$('letters_doc_frm_user_1_status_change_lnk').set('html', statuses_list[0]);
		$('letters_doc_frm_user2_status_data').set('value', 0);
		$('letters_doc_frm_user2_status_date_data').set('value', '');
		$('letters_doc_frm_user_2_status_change_lnk').set('html', statuses_list[0]);
		$('letters_doc_frm_user3_status_data').set('value', 0);
		$('letters_doc_frm_user3_status_date_data').set('value', '');
		$('letters_doc_frm_user_3_status_change_lnk').set('html', statuses_list[0]);

		if($('letters_doc_frm_user_3_div').getStyle('display')=='block') {
			letters.toggleUser3();
		}
	},

	M_ShowDoc: function(num, need_save) {
		if(need_save==true) {
			letters.M_Save(letters.curDocM);
		}

		var t_html = '';
		for(var i=1; i<=letters.countDocM; i++) {
			if(i==num) {
				t_html = t_html + 'Документ '+i+' ';
				$('l_form_1').set('html', 'Документ '+i);
			} else {
				t_html = t_html + '<a class="b-layout__link b-layout__link_fontsize_18 b-layout__link_bordbot_dot_0f71c8" href="" onClick="letters.M_ShowDoc('+i+', true); return false;">Документ '+i+'</a> ';
			}
		}
		$('l_form_1').set('html', t_html+' <div id="l_form_1_1" class="b-layout__txt b-layout__txt_inline-block"><a class="b-button b-button_poll_plus" href="" onClick="letters.M_InsertNewDoc(); return false;"></a> <a class="b-layout__link b-layout__link_fontsize_15 b-layout__link_bordbot_dot_0f71c8" href="" onClick="letters.M_InsertNewDoc(); return false;">добавить</a></div>');

		letters.M_Restore(num);
		letters.curDocM = num;
	},

	M_Restore: function(num) {
		letters.M_Reset();
		$('letters_doc_frm_title').set('value', letters.MData[num-1]['letters_doc_frm_title']);
		ComboboxManager.getInput("letters_doc_frm_group").loadRecord(letters.MData[num-1]['letters_doc_frm_group_db_id'], "getlettergroupinfo");
		if(letters.MData[num-1]['letters_doc_frm_user_1_section']==1) {
			ComboboxManager.getInput("letters_doc_frm_user_1").loadRecord(letters.MData[num-1]['letters_doc_frm_user_1_db_id'], "get_user_or_company_info", 'type=company');
		} else {
			ComboboxManager.getInput("letters_doc_frm_user_1").loadRecord(letters.MData[num-1]['letters_doc_frm_user_1_db_id'], "get_user_or_company_info", 'type=user');
		}
		if(letters.MData[num-1]['letters_doc_frm_user_2_section']==1) {
			ComboboxManager.getInput("letters_doc_frm_user_2").loadRecord(letters.MData[num-1]['letters_doc_frm_user_2_db_id'], "get_user_or_company_info", 'type=company');
		} else {
			ComboboxManager.getInput("letters_doc_frm_user_2").loadRecord(letters.MData[num-1]['letters_doc_frm_user_2_db_id'], "get_user_or_company_info", 'type=user');
		}
		if(letters.MData[num-1]['letters_doc_frm_user_3_section']==1) {
			ComboboxManager.getInput("letters_doc_frm_user_3").loadRecord(letters.MData[num-1]['letters_doc_frm_user_3_db_id'], "get_user_or_company_info", 'type=company');
		} else {
			ComboboxManager.getInput("letters_doc_frm_user_3").loadRecord(letters.MData[num-1]['letters_doc_frm_user_3_db_id'], "get_user_or_company_info", 'type=user');
		}
		ComboboxManager.getInput("letters_doc_frm_delivery").reload(deliveryList[letters.MData[num-1]['letters_doc_frm_delivery_db_id']]);
		$('letters_doc_frm_delivery_cost').set('value', letters.MData[num-1]['letters_doc_frm_delivery_cost']);
		if(letters.MData[num-1]['letters_doc_frm_parent_db_id']) {
			ComboboxManager.getInput("letters_doc_frm_parent").loadRecord(letters.MData[num-1]['letters_doc_frm_parent_db_id'], "getletterdocinfo");
		}
		$('letters_doc_frm_comment').set('value', letters.MData[num-1]['letters_doc_frm_comment']);

		if(letters.MData[num-1]['letters_doc_frm_withoutourdoc']==1) {
			$('letters_doc_frm_withoutourdoc').set('checked', true);
		}
		if(letters.MData[num-1]['letters_doc_frm_user_3_db_id'] || letters.MData[num-1]['letters_doc_frm_user3_status_data']!=0) {
			$('letters_doc_frm_user_3_div').setStyle('display', 'block');
			$('letters_doc_frm_user_3_addlnk').set('html', 'Удалить третью сторону');
			$('letters_doc_frm_user_3_addlnk').getPrevious().removeClass('b-button_poll_plus');
			$('letters_doc_frm_user_3_addlnk').getPrevious().addClass('b-button_poll_minus');
		} else {
			$('letters_doc_frm_user_3_div').setStyle('display', 'none');
			$('letters_doc_frm_user_3_addlnk').set('html', 'Добавить третью сторону');
			$('letters_doc_frm_user_3_addlnk').getPrevious().addClass('b-button_poll_plus');
			$('letters_doc_frm_user_3_addlnk').getPrevious().removeClass('b-button_poll_minus');
		}

		$('letters_doc_frm_user1_status_data').set('value', letters.MData[num-1]['letters_doc_frm_user1_status_data']);
		$('letters_doc_frm_user2_status_data').set('value', letters.MData[num-1]['letters_doc_frm_user2_status_data']);
		$('letters_doc_frm_user3_status_data').set('value', letters.MData[num-1]['letters_doc_frm_user3_status_data']);
		$('letters_doc_frm_user1_status_date_data').set('value', letters.MData[num-1]['letters_doc_frm_user1_status_date_data']);
		$('letters_doc_frm_user2_status_date_data').set('value', letters.MData[num-1]['letters_doc_frm_user2_status_date_data']);
		$('letters_doc_frm_user3_status_date_data').set('value', letters.MData[num-1]['letters_doc_frm_user3_status_date_data']);
		var add_status1 = '';
		var add_status2 = '';
		var add_status3 = '';
		var d = '';
		if(letters.MData[num-1]['letters_doc_frm_user1_status_date_data']) {
			d = letters.MData[num-1]['letters_doc_frm_user1_status_date_data'];
			add_status1 = ' '+d.substr(8,2)+'.'+d.substr(5,2)+'.'+d.substr(0,4);
		}
		if(letters.MData[num-1]['letters_doc_frm_user2_status_date_data']) {
			d = letters.MData[num-1]['letters_doc_frm_user2_status_date_data'];
			add_status2 = ' '+d.substr(8,2)+'.'+d.substr(5,2)+'.'+d.substr(0,4);
		}
		if(letters.MData[num-1]['letters_doc_frm_user3_status_date_data']) {
			d = letters.MData[num-1]['letters_doc_frm_user3_status_date_data'];
			add_status3 = ' '+d.substr(8,2)+'.'+d.substr(5,2)+'.'+d.substr(0,4);
		}
        $('letters_doc_frm_user_1_status_change_lnk').set('html', statuses_list[letters.MData[num-1]['letters_doc_frm_user1_status_data']]+add_status1);
		$('letters_doc_frm_user_2_status_change_lnk').set('html', statuses_list[letters.MData[num-1]['letters_doc_frm_user2_status_data']]+add_status2);
		$('letters_doc_frm_user_3_status_change_lnk').set('html', statuses_list[letters.MData[num-1]['letters_doc_frm_user3_status_data']]+add_status3);

	},

	M_DeleteDoc: function(num) {
		var t_MData = [];
		var i = 0;

		letters.MData.splice(num-1,1);

		if(letters.countDocM>1) {
			letters.countDocM--;
			letters.curDocM = letters.countDocM;
			letters.M_ShowDoc(letters.curDocM, false);
		} else {
			letters.M_Reset();
		}
	},


	countDocTemplate : 1,
	curDocTemplate : 1,
	TemplateData : [],

	TemplateInsertNewDoc: function() {
		letters.TemplateSave(letters.curDocTemplate);

		var t_html = '';
		letters.countDocTemplate++;
		letters.curDocTemplate = letters.countDocTemplate;
		for(var i=1; i<=letters.countDocTemplate; i++) {
			if(i==letters.curDocTemplate) {
				t_html = t_html + 'Документ '+i+' ';
			} else {
				t_html = t_html + '<a class="b-layout__link b-layout__link_fontsize_18 b-layout__link_bordbot_dot_0f71c8" href="" onClick="letters.TemplateShowDoc('+i+', true); return false;">Документ '+i+'</a> ';
			}
		}
		$('l_form_1').set('html', t_html+ ' <div id="l_form_1_1" class="b-layout__txt b-layout__txt_inline-block"><a class="b-button b-button_poll_plus" href="" onClick="letters.TemplateInsertNewDoc(); return false;"></a> <a class="b-layout__link b-layout__link_fontsize_15 b-layout__link_bordbot_dot_0f71c8" href="" onClick="letters.TemplateInsertNewDoc(); return false;">добавить</a></div>');		
		letters.TemplateReset();
		letters.TemplateRestore(1);
	},

	TemplateShowDoc: function(num, need_save) {
		if(need_save==true) {
			letters.TemplateSave(letters.curDocTemplate);
		}

		var t_html = '';
		for(var i=1; i<=letters.countDocTemplate; i++) {
			if(i==num) {
				t_html = t_html + 'Документ '+i+' ';
				$('l_form_1').set('html', 'Документ '+i);
			} else {
				t_html = t_html + '<a class="b-layout__link b-layout__link_fontsize_18 b-layout__link_bordbot_dot_0f71c8" href="" onClick="letters.TemplateShowDoc('+i+', true); return false;">Документ '+i+'</a> ';
			}
		}
		$('l_form_1').set('html', t_html+' <div id="l_form_1_1" class="b-layout__txt b-layout__txt_inline-block"><a class="b-button b-button_poll_plus" href="" onClick="letters.TemplateInsertNewDoc(); return false;"></a> <a class="b-layout__link b-layout__link_fontsize_15 b-layout__link_bordbot_dot_0f71c8" href="" onClick="letters.TemplateInsertNewDoc(); return false;">добавить</a></div>');

		letters.TemplateRestore(num);
		letters.curDocTemplate = num;

	},

	TemplateReset: function() {
		$('letters_doc_frm_title').set('value', '');
		$('letters_doc_frm_delivery_cost').set('value', '');
		$('letters_doc_frm_comment').set('value', '');
		//$('letters_doc_frm_withoutourdoc').set('checked', false);
		ComboboxManager.getInput("letters_doc_frm_group").clear();
		ComboboxManager.getInput("letters_doc_frm_delivery").reload();		
		ComboboxManager.getInput("letters_doc_frm_user_1").clear();
		ComboboxManager.getInput("letters_doc_frm_user_2").clear();
		ComboboxManager.getInput("letters_doc_frm_user_3").clear();
		ComboboxManager.getInput("letters_doc_frm_parent").clear();
		//ComboboxManager.getInput("letters_doc_frm_dateadd").setDate();
		$('letters_doc_frm_user1_status_data').set('value', 0);
		$('letters_doc_frm_user1_status_date_data').set('value', '');
		$('letters_doc_frm_user_1_status_change_lnk').set('html', statuses_list[0]);
		$('letters_doc_frm_user2_status_data').set('value', 0);
		$('letters_doc_frm_user2_status_date_data').set('value', '');
		$('letters_doc_frm_user_2_status_change_lnk').set('html', statuses_list[0]);
		$('letters_doc_frm_user3_status_data').set('value', 0);
		$('letters_doc_frm_user3_status_date_data').set('value', '');
		$('letters_doc_frm_user_3_status_change_lnk').set('html', statuses_list[0]);

		if($('letters_doc_frm_user_3_div').getStyle('display')=='block') {
			letters.toggleUser3();
		}
	},

	TemplateSave: function(num) {
		letters.TemplateData[num-1] = [];
		letters.TemplateData[num-1]['letters_doc_frm_title'] = $('letters_doc_frm_title').get('value');
		letters.TemplateData[num-1]['letters_doc_frm_group_db_id'] = $('letters_doc_frm_group_db_id').get('value');
		letters.TemplateData[num-1]['letters_doc_frm_user_1_db_id'] = $('letters_doc_frm_user_1_db_id').get('value');
		letters.TemplateData[num-1]['letters_doc_frm_user_1_section'] = $('letters_doc_frm_user_1_section').get('value');
		letters.TemplateData[num-1]['letters_doc_frm_user_2_db_id'] = $('letters_doc_frm_user_2_db_id').get('value');
		letters.TemplateData[num-1]['letters_doc_frm_user_2_section'] = $('letters_doc_frm_user_2_section').get('value');
		letters.TemplateData[num-1]['letters_doc_frm_user_3_db_id'] = $('letters_doc_frm_user_3_db_id').get('value');
		letters.TemplateData[num-1]['letters_doc_frm_user_3_section'] = $('letters_doc_frm_user_3_section').get('value');
		letters.TemplateData[num-1]['letters_doc_frm_delivery_db_id'] = $('letters_doc_frm_delivery_db_id').get('value');
		letters.TemplateData[num-1]['letters_doc_frm_delivery_cost'] = $('letters_doc_frm_delivery_cost').get('value');
		letters.TemplateData[num-1]['letters_doc_frm_parent_db_id'] = $('letters_doc_frm_parent_db_id').get('value');
		letters.TemplateData[num-1]['letters_doc_frm_comment'] = $('letters_doc_frm_comment').get('value');

		//letters.TemplateData[num-1]['letters_doc_frm_withoutourdoc'] = ($('letters_doc_frm_withoutourdoc').get('checked') ? 1 : 0);

		letters.TemplateData[num-1]['letters_doc_frm_user1_status_data'] = $('letters_doc_frm_user1_status_data').get('value');
		letters.TemplateData[num-1]['letters_doc_frm_user2_status_data'] = $('letters_doc_frm_user2_status_data').get('value');
		letters.TemplateData[num-1]['letters_doc_frm_user3_status_data'] = $('letters_doc_frm_user3_status_data').get('value');

		letters.TemplateData[num-1]['letters_doc_frm_user1_status_date_data'] = $('letters_doc_frm_user1_status_date_data').get('value');
		letters.TemplateData[num-1]['letters_doc_frm_user2_status_date_data'] = $('letters_doc_frm_user2_status_date_data').get('value');
		letters.TemplateData[num-1]['letters_doc_frm_user3_status_date_data'] = $('letters_doc_frm_user3_status_date_data').get('value');
	},

	TemplateRestore: function(num) {
		letters.TemplateReset();
		$('letters_doc_frm_title').set('value', letters.TemplateData[num-1]['letters_doc_frm_title']);
		ComboboxManager.getInput("letters_doc_frm_group").loadRecord(letters.TemplateData[num-1]['letters_doc_frm_group_db_id'], "getlettergroupinfo");
		if(letters.TemplateData[num-1]['letters_doc_frm_user_1_section']==1) {
			ComboboxManager.getInput("letters_doc_frm_user_1").loadRecord(letters.TemplateData[num-1]['letters_doc_frm_user_1_db_id'], "get_user_or_company_info", 'type=company');
		} else {
			ComboboxManager.getInput("letters_doc_frm_user_1").loadRecord(letters.TemplateData[num-1]['letters_doc_frm_user_1_db_id'], "get_user_or_company_info", 'type=user');
		}
		if(letters.TemplateData[num-1]['letters_doc_frm_user_2_section']==1) {
			ComboboxManager.getInput("letters_doc_frm_user_2").loadRecord(letters.TemplateData[num-1]['letters_doc_frm_user_2_db_id'], "get_user_or_company_info", 'type=company');
		} else {
			ComboboxManager.getInput("letters_doc_frm_user_2").loadRecord(letters.TemplateData[num-1]['letters_doc_frm_user_2_db_id'], "get_user_or_company_info", 'type=user');
		}
		if(letters.TemplateData[num-1]['letters_doc_frm_user_3_section']==1) {
			ComboboxManager.getInput("letters_doc_frm_user_3").loadRecord(letters.TemplateData[num-1]['letters_doc_frm_user_3_db_id'], "get_user_or_company_info", 'type=company');
		} else {
			ComboboxManager.getInput("letters_doc_frm_user_3").loadRecord(letters.TemplateData[num-1]['letters_doc_frm_user_3_db_id'], "get_user_or_company_info", 'type=user');
		}
		ComboboxManager.getInput("letters_doc_frm_delivery").reload(deliveryList[letters.TemplateData[num-1]['letters_doc_frm_delivery_db_id']]);
		$('letters_doc_frm_delivery_cost').set('value', letters.TemplateData[num-1]['letters_doc_frm_delivery_cost']);
		if(letters.TemplateData[num-1]['letters_doc_frm_parent_db_id']) {
			ComboboxManager.getInput("letters_doc_frm_parent").loadRecord(letters.TemplateData[num-1]['letters_doc_frm_parent_db_id'], "getletterdocinfo");
		}
		$('letters_doc_frm_comment').set('value', letters.TemplateData[num-1]['letters_doc_frm_comment']);

		//if(letters.TemplateData[num-1]['letters_doc_frm_withoutourdoc']==1) {
		//	$('letters_doc_frm_withoutourdoc').set('checked', true);
		//}
		if(letters.TemplateData[num-1]['letters_doc_frm_user_3_db_id'] || letters.TemplateData[num-1]['letters_doc_frm_user3_status_data']!=0) {
			$('letters_doc_frm_user_3_div').setStyle('display', 'block');
			$('letters_doc_frm_user_3_addlnk').set('html', 'Удалить третью сторону');
			$('letters_doc_frm_user_3_addlnk').getPrevious().removeClass('b-button_poll_plus');
			$('letters_doc_frm_user_3_addlnk').getPrevious().addClass('b-button_poll_minus');
		} else {
			$('letters_doc_frm_user_3_div').setStyle('display', 'none');
			$('letters_doc_frm_user_3_addlnk').set('html', 'Добавить третью сторону');
			$('letters_doc_frm_user_3_addlnk').getPrevious().addClass('b-button_poll_plus');
			$('letters_doc_frm_user_3_addlnk').getPrevious().removeClass('b-button_poll_minus');
		}

		$('letters_doc_frm_user1_status_data').set('value', letters.TemplateData[num-1]['letters_doc_frm_user1_status_data']);
		$('letters_doc_frm_user2_status_data').set('value', letters.TemplateData[num-1]['letters_doc_frm_user2_status_data']);
		$('letters_doc_frm_user3_status_data').set('value', letters.TemplateData[num-1]['letters_doc_frm_user3_status_data']);
		$('letters_doc_frm_user1_status_date_data').set('value', letters.TemplateData[num-1]['letters_doc_frm_user1_status_date_data']);
		$('letters_doc_frm_user2_status_date_data').set('value', letters.TemplateData[num-1]['letters_doc_frm_user2_status_date_data']);
		$('letters_doc_frm_user3_status_date_data').set('value', letters.TemplateData[num-1]['letters_doc_frm_user3_status_date_data']);
		var add_status1 = '';
		var add_status2 = '';
		var add_status3 = '';
		var d = '';
		if(letters.TemplateData[num-1]['letters_doc_frm_user1_status_date_data']) {
			d = letters.TemplateData[num-1]['letters_doc_frm_user1_status_date_data'];
			add_status1 = ' '+d.substr(8,2)+'.'+d.substr(5,2)+'.'+d.substr(0,4);
		}
		if(letters.TemplateData[num-1]['letters_doc_frm_user2_status_date_data']) {
			d = letters.TemplateData[num-1]['letters_doc_frm_user2_status_date_data'];
			add_status2 = ' '+d.substr(8,2)+'.'+d.substr(5,2)+'.'+d.substr(0,4);
		}
		if(letters.TemplateData[num-1]['letters_doc_frm_user3_status_date_data']) {
			d = letters.TemplateData[num-1]['letters_doc_frm_user3_status_date_data'];
			add_status3 = ' '+d.substr(8,2)+'.'+d.substr(5,2)+'.'+d.substr(0,4);
		}
        $('letters_doc_frm_user_1_status_change_lnk').set('html', statuses_list[letters.TemplateData[num-1]['letters_doc_frm_user1_status_data']]+add_status1);
		$('letters_doc_frm_user_2_status_change_lnk').set('html', statuses_list[letters.TemplateData[num-1]['letters_doc_frm_user2_status_data']]+add_status2);
		$('letters_doc_frm_user_3_status_change_lnk').set('html', statuses_list[letters.TemplateData[num-1]['letters_doc_frm_user3_status_data']]+add_status3);

	},

	TemplateDeleteDoc: function(num) {
		var t_TemplateData = [];
		var i = 0;

		letters.TemplateData.splice(num-1,1);

		if(letters.countDocTemplate>1) {
			letters.countDocTemplate--;
			letters.curDocTemplate = letters.countDocTemplate;
			letters.TemplateShowDoc(letters.curDocTemplate, false);
		} else {
			letters.TemplateReset();
		}
	},

	TemplateAddDoc: function() {
		status_can_submit = true;
		if(status_can_submit==true) {
			var data = '';

				letters.TemplateSave(letters.curDocTemplate);
				var jObject={};
				jObject['count_docs'] = letters.countDocTemplate;
				jObject['template_title'] = $('frm_template_name').get('value');
    			for(i in letters.TemplateData) {
    				if (!letters.TemplateData.hasOwnProperty(i)) continue;
    				for(k in letters.TemplateData[i]) {
    					if (!letters.TemplateData[i].hasOwnProperty(k)) continue;
        				jObject[i+'-'+k] = letters.TemplateData[i][k];
        			}
    			}
				data = JSON.stringify(jObject);


			xajax_addTemplate(xajax.getFormValues('letters_doc_frm'), data);
			status_can_submit = false;
		}
	},

	TemplateUpdateDoc: function() {
		status_can_submit = true;
		if(status_can_submit==true) {
			var data = '';

				letters.TemplateSave(letters.curDocTemplate);
				var jObject={};
				jObject['count_docs'] = letters.countDocTemplate;
				jObject['template_title'] = $('frm_template_name').get('value');
				jObject['template_id'] = $('letters_doc_frm_template_id').get('value');
    			for(i in letters.TemplateData) {
    				if (!letters.TemplateData.hasOwnProperty(i)) continue;
    				for(k in letters.TemplateData[i]) {
    					if (!letters.TemplateData[i].hasOwnProperty(k)) continue;
        				jObject[i+'-'+k] = letters.TemplateData[i][k];
        			}
    			}
				data = JSON.stringify(jObject);


			xajax_updateTemplate(xajax.getFormValues('letters_doc_frm'), data);
			status_can_submit = false;
		}
	},

	selectTemplate: function(id) {
		letters.spinner.show();
		xajax_selectTemplate(id);
	}


}

window.addEvent('domready', function() {
	if($('letters_form_start')) {
		$$('.b-search__ext-link').addEvent('click', function() {
			this.getParent('.b-ext-filter__inner').getChildren('.b-ext-filter__body').toggleClass('b-ext-filter__body_hide');
			this.getNext('.b-ext-filter__toggler').toggleClass('b-ext-filter__toggler_down').toggleClass('b-ext-filter__toggler_up');
			if(this.get('text')=='Свернуть поиск') {
				this.set('text', 'Расширенный поиск');
			} else {
				this.set('text', 'Свернуть поиск');
			}
		});
		letters.spinner = new Spinner('b-ext-filter', {containerPosition: {y: 'top'}});
		if(is_js_cmd!=true) {
			if(is_templates_mode!=true) {
				letters.changeTabs(1);
			}
		}
		if (attachedFiles.newDesign) attachedFiles.initCommDomready();
		obj_letters_form_comment_field_data = new DynamicTextarea($('letters_form_comment_field_data'));
	}

	if($('letters_company_lists')) {
		letters.spinner = new Spinner('r-col');
	}
});


var attachedFiles = {

    obj:                null,
    objID:              null,
    files:              null,
    maxCount:           0,
    maxSize:            0,
    disallowedFormats:  '',
    type:               '', 
    uid:                0,
    count:              0,
    newDesign:           false, // новый дизайн

    changeClasses: function() {
        $(this.objID).getElements('.b-fon__item_last').each(function(item) {item.removeClass('b-fon__item_last');});
        var el = $(this.objID).getElements('.b-fon__item').getLast();
        if(this.count==0) {$('attachedfiles_selectfile_div').addClass('b-fon__item_last');} else {el.addClass('b-fon__item_last');}
    },

    toggleFormatsInfo: function() {
        $('attachedfiles_extensions').toggleClass('b-file__slide_hide');
    },

    clearFileField: function() {
        document.getElementById('attachedfiles_file_div').innerHTML = document.getElementById('attachedfiles_file_div').innerHTML;
        if (this.newDesign) {
            if (temp = $('attachedfiles_file')) temp.addEvent('change', function(){
                attachedFiles.upload.call(attachedFiles)
            });
        }
    },

    upload: function() {
        this.hideError();
        $('attachedfiles_action').set('value', 'add');

        var action_field = '';
        var action_field_id;
        var action_field_name;
        
        
        action_field = this.obj.getParent('form').getElement('input[name=action]');
        if(action_field) {
            action_field_id = action_field.get('id');
            action_field_name = action_field.get('name');
            action_field.removeProperty('id');
            action_field.removeProperty('name');
        }

        var action = this.obj.getParent('form').get('action');
        this.obj.getParent('form').set('action','/attachedfiles.php');
        this.obj.getParent('form').set('target','attachedfiles_hiddenframe');

        if(action_field) {
            action_field.set('id', action_field_id);
            action_field.set('name', action_field_name);
        }

        this.obj.getParent('form').submit();
        $('attachedfiles_error').setStyle('display', 'none');
        $('attachedfiles_uploadingfile').setStyle('display', 'block');
        $('attachedfiles_file').set('disabled', true);

        action_field = this.obj.getParent('form').getElement('input[name=action]');
        if(action_field) {
            action_field_id = action_field.get('id');
            action_field_name = action_field.get('name');
            action_field.removeProperty('id');
            action_field.removeProperty('name');
        }
        
        this.obj.getParent('form').set('target','');
        this.obj.getParent('form').set('action',action);

        if(action_field) {
            action_field.set('id', action_field_id);
            action_field.set('name', action_field_name);
        }

        if (!this.newDesign) { // если не новый дизайн
            if(this.count==0) {
                $('attachedfiles_selectfile_div').removeClass('b-fon__item_last');
                $('attachedfiles_uploadingfile').addClass('b-fon__item_last');
            } else {
                this.changeClasses();
            }
        }
    },

    upload_done: function(fmessage) {
        $('attachedfiles_file').set('disabled', false);
        $('attachedfiles_uploadingfile').setStyle('display', 'none');
        if (!this.newDesign) { // если старый дизайн
            if(fmessage.error != '') {
                $('attachedfiles_error').setStyle('display', 'block');
                $('attachedfiles_errortxt').set('html', fmessage.error);
                if(this.count==0) {
                    $('attachedfiles_selectfile_div').removeClass('b-fon__item_last');
                    $('attachedfiles_error').addClass('b-fon__item_last');
                } else {
                    this.changeClasses();
                }
            } else {
                $('attachedfiles_uploadingfile').setStyle('display', 'none');
                var newFileDiv  = new Element('div', {id: 'attachedfile_'+fmessage.id, html: attachedFiles.getHTMLItem(fmessage.id, fmessage.name, fmessage.path, fmessage.size, fmessage.type)});
                newFileDiv.setProperty('class', 'b-fon__item');
                newFileDiv.inject($('attachedfiles_error'), 'after');
                this.count++;
                this.changeClasses();
            }
        } else { // для нового дизайна (сообщества)
            if(fmessage.error != '') {
                $('attachedfiles_error').setStyle('display', 'block');
                $('attachedfiles_errortxt').set('html', fmessage.error);
            } else {
                this.newFile(fmessage.id, fmessage.name, fmessage.path, fmessage.size, fmessage.type);
                this.count++;
                status_can_submit = true;
                $('wd_file_add').setStyle('display', 'none');
//                $('f_button_actionwork').removeClass('b-button_rectangle_color_disable');
//                var currentDate = new Date();
//                $('wd_work_user_preview').set('src', '');
//                $('wd_work_user_preview').set('src', '/wd/getresizedimage.php?file='+$('attachedfiles_session').get('value')+'&size=litle&r='+(Math.floor(Math.random()*(999999999999))+1)+'_'+currentDate.getTime());
//                $('wd_work_preview_medium_user').set('src', $('wd_work_user_preview').get('src')+'&size=medium');
//                $('wd_work_user_preview').setStyle('display', 'block');
            }
            this.changeClassesNewDesign();
        }
    },
    
    del: function(fid) {
        this.hideError();
        $('attachedfiles_action').set('value', 'delete');
        $('attachedfiles_delete').set('value', fid);

        $('attachedfile_'+fid).destroy();

        var action = this.obj.getParent('form').get('action');
        this.obj.getParent('form').set('action','/attachedfiles.php');
        this.obj.getParent('form').set('target','attachedfiles_hiddenframe');
        this.obj.getParent('form').submit();
        $('attachedfiles_deletingfile').setStyle('display', 'block');
        this.obj.getParent('form').set('target','');
        this.obj.getParent('form').set('action',action);
        if (!this.newDesign) {
            if(this.count==1) {
                $('attachedfiles_deletingfile').addClass('b-fon__item_last');
            } else {
                this.changeClasses();
            }
        }
    },

    del_done: function() {
        $('attachedfiles_deletingfile').setStyle('display', 'none');
        $('attachedfiles_action').set('value', '');
        $('attachedfiles_delete').set('value', '');
        this.count--;
        if (!this.newDesign) {
            this.changeClasses();
        } else {
            this.changeClassesNewDesign();
        }
        status_can_submit = false;
//        $('f_button_actionwork').addClass('b-button_rectangle_color_disable');
//        $('wd_work_user_preview').set('src', '');
//        $('wd_work_preview_medium_user').set('src', '/images/1.gif');
//        $('wd_work_user_preview').setStyle('display', 'none');
        $('wd_file_add').set('style', 'display: table;');
    },

    hideError: function() {
        $('attachedfiles_error').setStyle('display', 'none');
        if (!this.newDesign) {
            this.changeClasses();
        }
    },

    getHTMLItem: function(fid, fname, fpath, fsize, ftype) {
        if(ftype=='docx') ftype = 'doc';
        if(ftype=='xlsx') ftype = 'xls';
        if(ftype=='jpg') ftype = 'jpeg';
        if(ftype=='mkv') ftype = 'hdv';
        if(!(ftype=='swf' || ftype=='mp3' || ftype=='rar' || ftype=='doc' || ftype=='pdf' || ftype=='ppt' || 
             ftype=='rtf' || ftype=='txt' || ftype=='xls' || ftype=='zip' || ftype=='jpeg' || ftype=='png' || 
             ftype=='ai' || ftype=='bmp' || ftype=='psd' || ftype=='gif' || ftype=='flv' || ftype=='wav' || 
             ftype=='ogg' || ftype=='wmv' || ftype=='tiff' || ftype=='avi' || ftype=='hdv' || ftype=='ihd' || ftype=='fla')
          ) {
            ftype = 'unknown';
        }
        if (!this.newDesign) {
            var htmlItem = "<table class='b-icon-layout wdh100'>\
                                    <tbody><tr>\
                                        <td class='b-icon-layout__icon'><i class='b-icon b-icon_attach_"+ftype+"'></i></td>\
                                        <td class='b-icon-layout__files'><a class='b-icon-layout__link' href='"+fpath+"' target='_blank'>"+fname+"</a></td>\
                                        <td class='b-icon-layout__size'>"+fsize+"&nbsp;&nbsp;</td>\
                                        <td class='b-icon-layout__operate'><a class='b-icon-layout__link b-icon-layout__link_dot_a23e3e' href='#' onClick='attachedFiles.del(\""+fid+"\"); return false;'>Удалить</a></td>\
                                    </tr></tbody>\
                                </table>";
            return htmlItem;
        } else {
            var file = $('attachedfiles_template').clone();
            file.setStyle('display', '').set('id', 'attachedfile_' + fid);
            var items = file.getElements('td');
            items[0].getElement('i').addClass('b-icon_attach_' + ftype);
            items[1].getElement('a').set('text', fname).set('href', fpath);
            items[1].set('html', items[1].get('html') + ', ' + fsize);
            
            return file;
        }
    },


    init: function(sObjID, sSession, sFiles, sMaxCount, sMaxSize, sDisallowedFormats, sType, sUID) {
        this.objID = sObjID;
        this.sessionid = sSession;
        this.files = sFiles;
        this.maxCount = sMaxCount;
        this.maxSize = sMaxSize;
        this.disallowedFormats = sDisallowedFormats;
        this.type = sType;
        this.uid = sUID;
        this.count = 0;

        htmlDIV_s = "<b class='b-fon__b1'></b>\
                     <b class='b-fon__b2'></b>\
                      <div class='b-fon__body'>\
                        <div id='attachedfiles_selectfile_div' class='b-fon__item b-fon__item_first'>\
                            <table class='b-file'>\
                                <tbody><tr>\
                                    <td class='b-file__button'>\
                                        <div class='b-file__wrap' id='attachedfiles_file_div'>\
                							<input class='b-file__input' type='file' id='attachedfiles_file' name='attachedfiles_file' onChange='attachedFiles.upload(); return false;'>\
                    							<a class='b-button b-button_rectangle_transparent_small' onclick='return false' href='#'>\
                    								<span class='b-button__b1'>\
                									<span class='b-button__b2'>\
                										Выбрать файл\
                									</span>\
                    								</span>\
                    							</a>\
                						</div>\
                                    </td>\
                                    <td class='b-file__text'>\
                                        <p class='b-file__descript'>\
                                        Общий размер загруженных файлов не более "+(this.maxSize / (1024*1024))+" Мб. <a class='b-file__link b-file__link_color_999 b-file__link_toggle b-file__link_dot_999' href='#' onClick='attachedFiles.toggleFormatsInfo(); return false;'>Запрещенные форматы</a><span class='b-file__slide b-file__slide_hide' id='attachedfiles_extensions'>: "+this.disallowedFormats+".</span>\
                                        </p>\
                                    </td>\
                                </tr></tbody>\
                            </table>\
                        </div>\
                        <div class='b-fon__item' id='attachedfiles_uploadingfile' style='display: none;'>\
                            <table class='b-icon-layout wdh100'>\
                                <tbody><tr>\
                                    <td class='b-icon-layout__icon'><img class='b-fon__loader' src='/images/loader-gray.gif' alt='' width='24' height='24'></td>\
                                    <td class='b-icon-layout__files'>Идет загрузка файла…</td>\
                                    <td class='b-icon-layout__size'>&nbsp;</td>\
                                    <td class='b-icon-layout__operate'>&nbsp;</td>\
                                </tr>\
                            </tbody></table>\
                        </div>\
                        <div class='b-fon__item' id='attachedfiles_deletingfile' style='display: none;'>\
                            <table class='b-icon-layout wdh100'>\
                                <tbody><tr>\
                                    <td class='b-icon-layout__icon'><img class='b-fon__loader' src='/images/loader-gray.gif' alt='' width='24' height='24'></td>\
                                    <td class='b-icon-layout__files'>Идет удаление файла…</td>\
                                    <td class='b-icon-layout__size'>&nbsp;</td>\
                                    <td class='b-icon-layout__operate'>&nbsp;</td>\
                                </tr>\
                            </tbody></table>\
                        </div>\
                        <div class='b-fon__item' id='attachedfiles_error' style='display: none;'>\
                            <table class='b-icon-layout wdh100'>\
                                <tbody><tr>\
                                    <td class='b-icon-layout__icon'><img class='b-fon__loader' src='/images/ico_error.gif' alt='' width='22' height='18'></td>\
                                    <td class='b-icon-layout__files' id='attachedfiles_errortxt' colspan='2'></td>\
                                    <td class='b-icon-layout__operate'><a class='b-icon-layout__link b-icon-layout__link_dot_666' href='#' onClick='attachedFiles.hideError(); return false;'>Скрыть</a></td>\
                                </tr>\
                            </tbody></table>\
                        </div>\
                    ";

        htmlDIV_e = "</div>\
                     <b class='b-fon__b2'></b>\
                     <b class='b-fon__b1'></b>\
                     <input type='hidden' id='attachedfiles_uid' name='attachedfiles_uid' value='"+this.uid+"'>\
                     <input type='hidden' id='attachedfiles_action' name='attachedfiles_action' value=''>\
                     <input type='hidden' id='attachedfiles_delete' name='attachedfiles_delete' value=''>\
                     <input type='hidden' id='attachedfiles_type' name='attachedfiles_type' value='"+this.type+"'>\
                     <input type='hidden' id='attachedfiles_session' name='attachedfiles_session' value='"+this.sessionid+"'>\
                     <iframe id='attachedfiles_hiddenframe' name='attachedfiles_hiddenframe' style='display: none;'></iframe>";

        this.obj = $(this.objID);
        if(attachedFiles.obj) {
            var html = '';
            html = html + htmlDIV_s;

            for (var n=0; n<this.files.length; n++) {
                html = html + "<div class='b-fon__item' id='attachedfile_"+this.files[n].id+"'>" + this.getHTMLItem(this.files[n].id, this.files[n].name, this.files[n].path, this.files[n].size, this.files[n].type) + "</div>";
                this.count++;
            }
            
            html = html + htmlDIV_e;
            this.obj.set('html', html);

            if(this.count==0) {
                $('attachedfiles_selectfile_div').addClass('b-fon__item_last');
            } else {
                this.changeClasses();
            }
        }
    },
    
    // *************************************
    // далее код для СООБЩЕСТВ *************
    // *************************************
    
    // инициализация
    initComm: function(sObjID, sSession, sFiles, sMaxCount, sMaxSize, sDisallowedFormats, sType, sUID) {
        this.objID =                sObjID;
        this.sessionid =            sSession;
        this.files =                sFiles;
        this.maxCount =             sMaxCount;
        this.maxSize =              sMaxSize;
        this.disallowedFormats =    sDisallowedFormats;
        this.type =                 sType;
        this.uid =                  sUID;
        this.newDesign =            true;
        
        this.obj = $(this.objID);
    },
    // запускается после domready
    initCommDomready: function () {
        var temp;
        // реакция на выбор файла
        if (temp = $('attachedfiles_file')) temp.addEvent('change', function(){
            attachedFiles.upload.call(attachedFiles)
        });
        // кнопка СКРЫТЬ ERROR
        if (temp = $('attachedfiles_hide_error')) temp.addEvent('click', function(){
            $('attachedfiles_error').setStyle('display', 'none');
        });
        // уже загруженные файлы
        var files = attachedFiles.files;
        for (var f in files) {
            if (!files.hasOwnProperty(f)) continue
            attachedFiles.newFile(files[f].id, files[f].name, files[f].path, files[f].size, files[f].type);
            attachedFiles.count++;
            attachedFiles.changeClassesNewDesign();
        }
        // закрыть окно с требованиями к файлам
        if (temp = $('attachedfiles_close_info')) temp.addEvent('click', attachedFiles.closeInfo);
    },
    // смена оформления
    changeClassesNewDesign: function () {
    },
    
    // добавить новый файл
    newFile: function (fid, fname, fpath, fsize, ftype) {
        var file = this.getHTMLItem(fid, fname, fpath, fsize, ftype);
        //file = file.inject('attachedfiles_table', 'top');
        file = file.inject('attachedfiles_template', 'before');
        file.getElements('td')[2].getElement('a').addEvent('click', function(){attachedFiles.del(fid)});
    },
    
    // закрыть окно с требованиями к файлу
    closeInfo: function () {
        $(this).getParent('div#attachedfiles_info').addClass('b-filter__toggle_hide');
    }
};
