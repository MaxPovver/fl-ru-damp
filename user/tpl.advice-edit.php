<script>
window.addEvent('domready', function() {
    <? if($is_link) { ?>
    select_link(1);
    <? } else { //if ?>
    select_link(2);
    <? }//else?>
    <? if (isset($budget)) {?>
    calcSum(<?= $budget?>, 1, <?= sbr_stages::MIN_COST_RUR?>, 0);
    <? }//if?>
});
</script>
<div class="b-layout b-layout_padtop_20">
<!--	<a class="b-layout__link b-layout__link_float_right b-layout__link_fontsize_11 b-layout__link_margright_15 b-layout__link_margtop_10" href="/help/?q=1002" target="_blank">Что это такое?</a>-->
	<div class="b-post b-post_padtop_10 b-post_padright_15 b-post_float_right b-post__txt b-post__txt_fontsize_11"><span class="b-post__qwest"></span> &#160;<a class="b-post__link b-post__link_fontsize_11 b-post__link_color_4e" href="/contacts/?from=opinions">Обратиться к менеджеру за помощью</a></div>
	<h2 class="b-layout__title b-layout__title_pad_0_15_15">Новая рекомендация &#160;&#160;&#160;&#160;    <a class="b-layout__link b-layout__link_fontsize_13" href="/users/<?= $_SESSION['login']?>/opinions/">Назад</a></h2>

	
    <div class="b-post b-post_pad_10_15_15" id="new_advice_<?=$advice['id']?>">
    	<div class="b-post__body b-post__body_bordbot_solid_f0  b-post__body_padbot_20">
    		<div class="b-post__time b-post__time_float_right"><a class="b-post__anchor b-post__anchor_margright_10" href="#"></a><?= date('d.m.Y в H:i', strtotime($advice['create_date']))?></div>
    		<div class="b-post__avatar b-post__avatar_margright_10">
    			<a class="b-post__link" href="/users/<?=$advice['login']?>/"><?= view_avatar($advice['login'], $advice['photo'], 1, 1, 'b-post__userpic') ?></a>
    		</div>
    		<div class="b-post__content b-post__content_margleft_60">
    			<div class="b-username b-username_bold b-username_padbot_10">
    			    <?= view_user3($advice)?>
    		    </div>			
    			<div class="b-post__voice b-post__voice_positive"></div>
    			<div class="b-post__txt b-post__txt_inline-block b-post__txt_valign_top"><?= reformat($advice['msgtext'], 40)?></div>
    		</div>
    	</div>
    	
    	<? if($advice['mod_status'] == paid_advices::MOD_STATUS_DECLINED) { ?>
    	<div class="b-post__foot b-post__foot_padtop_15 b-post__foot_padleft_60 b-buttons">
			<div class="b-post__txt b-post__txt_fontsize_11 b-post__txt_color_c10601 b-post__txt_padbot_10">Заявка была отклонена модераторами</div>
    	<div class="b-fon b-fon_bg_ff6d2d b-fon_width_full b-fon_padbot_20">
				<b class="b-fon__b1"></b>
				<b class="b-fon__b2"></b>
				<div class="b-fon__body b-fon__body_pad_10 b-fon__body_fontsize_13 ">
					<span class="b-fon__attent"></span><div class="b-fon__txt b-fon__txt_margleft_20"><?= htmlspecialchars($advice['mod_msg'])?> <a class="b-fon__link" href="https://feedback.fl.ru/">Служба поддержки</a> </div>
				</div>
				<b class="b-fon__b2"></b>
				<b class="b-fon__b1"></b>
			</div>
			</div>
    	<? } //if?>
    	
    	<? if($advice['mod_status'] == paid_advices::MOD_STATUS_PENDING) { ?>
    	<div class="b-fon b-fon_width_full b-fon_margbot_10">
            <div class="b-fon__body b-fon__body_pad_10 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf b-fon__body_bordbot_dfedcf">
        		Рекомендация отправлена на модерацию. <a class="b-buttons__link b-buttons__link_color_c10601" href="javascript:void(0)" onClick="if(confirm('Вы уверены, что хотите отозвать рекомендацию?')) xajax_RefuseAdvice(<?= (int)$advice['id']?>)">Отозвать и отредактировать</a>
        	</div>
        </div>
        <? } else if($advice['mod_status'] == paid_advices::MOD_STATUS_ACCEPTED ){ ?>
        <div class="b-fon b-fon_width_full b-fon_margbot_10">
            <div class="b-fon__body b-fon__body_pad_10 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf b-fon__body_bordbot_dfedcf">
                Рекомендация прошла модерацию.
            </div>
        </div>
    	<? } else {?>
    	
        
        <? if($is_save && !$error) { ?>
        <div class="b-post__foot b-post__foot_padtop_15 b-post__foot_padleft_60 b-buttons">
            <div class="b-fon b-fon_width_full b-fon_margbot_10" id="save_launcher">
                <div class="b-fon__body b-fon__body_pad_10 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf b-fon__body_bordbot_dfedcf">
                    Данные по рекомендации сохранены.
                </div>
            </div>
        </div>
    	<? } //if?>
    	<form class="b-post__body" method="POST" enctype="multipart/form-data" id="form_advice">
            <?php if($advice['oid']) { ?>
            <input type="hidden" name="opinion_id" value="<?=$advice['oid']?>">
            <?php } else {?>
            <input type="hidden" name="paid_advice_id" value="<?=$advice['id']?>">
            <?php }//else?>
    	    <input type="hidden" name="isReqvsFilled" id="isReqvsFilled" value="<?= (int)$isReqvsFilled?>">
    	    <input type="hidden" name="add_mod" id="add_mod" value="0">
    	    
    	    <? if(isset($_attached['ids'])) {?>
    	       <? foreach($_attached['ids'] as $n=>$k) {?>
    	       <input type="hidden" name="files_uploaded_id[<?=$n?>]" value="<?=intval($k)?>" id="uploaded_<?=$n?>">
    	       <input type="hidden" name="files_uploaded_fname[<?=$n?>]" value="<?=htmlspecialchars($_attached['name'][$n])?>">
    	       <? } // foreach?>
    	    <? } //if?>
    	    <input type="hidden" name="save" id="save_advice" value="1">
    		<div class="b-post__content b-post__content_padleft_60 b-post__content_padtop_20">
    		    <?= isset($error['save'])?print(view_error($error['save'])):''; ?>
    		    
    			<div class="b-post__txt b-post__txt_fontsize_11 b-post__txt_padbot_20">Вам надо указать бюджет сделки, по которой вы сотрудничали, и прикрепить подтверждающие документы.</div>
    		    <span id="error_budget" style="<?= isset($error['budget'])?'':"display:none";?>">
    		    <?print(view_error('Минимальный бюджет проекта - ' .sbr_stages::MIN_COST_RUR_PDRD. ' руб.<br/><br/>'))?>
    		    </span>
                <span id="error_budget_format" style="display:none">
    		    <?print(view_error('Введенные данные не являются числом.<br/><br/>'))?>
    		    </span>
    			<div class="b-post__txt b-post__txt_bold b-post__txt_padbot_10">1. <?= $error['budget'] == 1 ? '<div class="b-post__txt b-post__txt_bold b-post__txt_inline-block b-post__txt_color_c10601">Обязательно укажите бюджет проекта</div>' :"Бюджет проекта (минимальный бюджет проекта - ".sbr_stages::MIN_COST_RUR_PDRD." руб.)"?></div>
    			<div class="b-post__txt b-post__txt_padbot_20">
    				<div class="b-input b-input_inline-block b-input_width_80 b-input_margtop_-2 "><input class="b-input__text" id="sum_rub" name="sum_rub" type="text" value="<?= $budget>0?$budget:''?>" onkeyup="calcSum(this.value, 1, <?= sbr_stages::MIN_COST_RUR_PDRD?>)" maxlength="8" onchange=""/></div>&#160;руб. &#160;&#160;&#160; Вы заплатите 
    				<div class="b-input b-input_inline-block b-input_width_80 b-input_margtop_-2"><input class="b-input__text" id="sum_fm" name="sum_fm" type="text" value="<?= ($payFM)?>" onkeyup="if(event.keyCode != 9) calcSum(this.value, 2, <?= sbr_stages::MIN_COST_RUR_PDRD?>)" maxlength="5" onchange=""/></div>&#160;<span class="b-post b-post__txt_color_fd6c30">руб.</span>
    				<span id="sum_rating"><?= ($HTMLRating!=""?$HTMLRating:"")?></span>
    			</div>
                <? 
                $in_ff3 = (BROWSER_NAME == 'firefox' && BROWSER_VERSION == 3);
                $onclick_upload[1] = $in_ff3 ? 'select_upload_file_ff3(this);' : ((BROWSER_NAME == 'firefox' || BROWSER_NAME == 'opera')?'select_upload_file(1);':'');
                $onclick_upload[2] = $in_ff3 ? 'select_upload_file_ff3(this);' : ((BROWSER_NAME == 'firefox' || BROWSER_NAME == 'opera')?'select_upload_file(2);':'');
                $onclick_upload[3] = $in_ff3 ? 'select_upload_file_ff3(this);' : ((BROWSER_NAME == 'firefox' || BROWSER_NAME == 'opera')?'select_upload_file(3);':'');
                ?>
    			<div class="b-post__txt b-post__txt_bold b-post__txt_padbot_10">2. <?= $error['files'] == 1 ? '<div class="b-post__txt b-post__txt_bold b-post__txt_inline-block b-post__txt_color_c10601">Обязательно загрузите документы' . ($error['files_text'] != '' ? " ({$error['files_text']})" : '') . '</div>'  : 'Документы'?></div>
    			<div class="b-post__txt b-post__txt_padbot_5">
    				<div class="b-post__txt b-post__txt_fontsize_11 b-post__txt_inline-block b-post__txt_width_110 <?= $error['doc_contract'] == 1 ? 'b-post__txt_color_c10601':''?>">Договор</div> 
    				<div class="b-post__txt b-post__txt_inline-block" id="attachedfiles">
        				
        				<span id="upload_txt_1" class="b-post__txt" <?= isset($_attached['name'][1])?'':'style="display:none"'?>><i class="b-icon b-icon_top_5 b-icon_ie7_top_1 b-icon_attach_<?= isset($_attached['ext'][1])?$_attached['ext'][1]:'doc'?>"></i><a class="b-post__link" href="<?= ($_attached['link'][1] ? $_attached['link'][1] : "javascript:void(0)")?>" id="fname_1" target="_blank"><?= isset($_attached['name'][1])?$_attached['name'][1]:''?></a>&#160;или</span>
                        <span class="b-post__txt b-post__txt_relative b-post__txt_overflow_hidden b-post__txt_zoom_1"><input class='b-file__input b-file__input_size_auto' type='file' id='attachedfiles_file_1' name='attachedfiles_file[1]' onchange="select_file(this, 1); checkForm();"><label for="attachedfiles_file_1" id="upload_link_1" class='b-post__label b-post__label_color_0f71c8 b-post__label_dot_0f71c8 b-post__label_lineheight_2' onclick="<?=$onclick_upload[1]?>"><?= !isset($_attached['name'][1])?'загрузить':'выбрать другой'?></label></span>
    				</div>
    			</div>
    			<div class="b-post__txt b-post__txt_padbot_5">
    				<div class="b-post__txt b-post__txt_fontsize_11 b-post__txt_inline-block b-post__txt_width_110 <?= $error['doc_tz'] == 1 ? 'b-post__txt_color_c10601':''?>">Техзадание</div> 
    				<div class="b-post__txt  b-post__txt_inline-block">
    				    
        				<span id="upload_txt_2" class="b-post__txt" <?= isset($_attached['name'][2])?'':'style="display:none"'?>><i class="b-icon b-icon_top_5 b-icon_ie7_top_1 b-icon_attach_<?= isset($_attached['ext'][2])?$_attached['ext'][2]:'doc'?>"></i><a class="b-post__link" href="<?= ($_attached['link'][2] ? $_attached['link'][2] : "javascript:void(0)")?>" id="fname_2" target="_blank"><?= isset($_attached['name'][2])?$_attached['name'][2]:''?></a>&#160;или</span>
                        <span class="b-post__txt b-post__txt_relative b-post__txt_overflow_hidden b-post__txt_zoom_1"><input class='b-file__input b-file__input_size_auto' type='file' id='attachedfiles_file_2' name='attachedfiles_file[2]' onchange="select_file(this, 2); checkForm();"><label for="attachedfiles_file_2" id="upload_link_2" class='b-post__label b-post__label_color_0f71c8 b-post__label_dot_0f71c8  b-post__label_lineheight_2' onclick="<?=$onclick_upload[2]?>"><?= !isset($_attached['name'][2])?'загрузить':'выбрать другой'?></label></span>
    				</div>
    			</div>
    			<div class="b-post__txt b-post__txt_padbot_15">
    				<div class="b-post__txt b-post__txt_fontsize_11 b-post__txt_inline-block b-post__txt_width_110  <?= $error['doc_result'] == 1 || $error['doc_result_link'] == 1 ? 'b-post__txt_color_c10601':''?>">Результаты работы</div> 
    				<div class="b-post__txt  b-post__txt_inline-block b-post__txt_ie7_valign_middle">
    				    
        				<span id="upload_txt_3" class="b-post__txt" <?= isset($_attached['name'][3])?'':'style="display:none"'?>>
                            <i class="b-icon b-icon_top_5 b-icon_ie7_top_1 b-icon_attach_<?= isset($_attached['ext'][3])?$_attached['ext'][3]:'doc'?>"></i><a class="b-post__link" href="<?= ($_attached['link'][3] ? $_attached['link'][3] : "javascript:void(0)")?>" id="fname_3" target="_blank"><?= isset($_attached['name'][3])?$_attached['name'][3]:''?></a>&#160;или
                        </span>
                        <span class="b-post__txt b-post__txt_relative b-post__txt_overflow_hidden b-post__txt_zoom_1" id="upload_link_3_block">
                            <input class='b-file__input b-file__input_size_auto' type='file' id='attachedfiles_file_3' name='attachedfiles_file[3]' onchange="select_file(this, 3); checkForm();">
                            <label for="attachedfiles_file_3" id="upload_link_3" class="b-post__label b-post__label_color_0f71c8 b-post__label_dot_0f71c8  b-post__label_lineheight_2" onclick='<?= $onclick_upload[3]?> select_link(2);'><?= (!isset($_attached['name'][3])?('загрузить'):('выбрать другой'))?></label>
                        </span>
                        <span id="input_link" style="display:none">
                            <div class="b-input b-input_width_150 b-input_inline-block b-input_margtop_3 b-input_ie7_margtop_0 b-input_zindex_2" id="input_link">
                                <input class="b-input__text b-input__text_width_120" name="link_work" id="link_work" type="text" value="<?= htmlspecialchars($link)?>" onchange="checkForm();"/>
                            </div>
                        </span>
                        <span id="reverse_block">&#160;или&nbsp;</span>
                        <a class="b-post__link b-post__link_dot_0f71c8" href="javascript:void(0)" onclick="select_link(1)" id="select_link">указать ссылку</a>
						</div>
						<div class="b-post__txt b-post__txt_fontsize_11 b-post__txt_padleft_115">Общий размер загруженных файлов не более 30 Мб.<br />Запрещены к загрузке: ade, adp, bat, chm, cmd, com, cpl, exe, hta, ins, isp, jse, lib, mde, msc, msp, <br />mst, pif, scr, sct, shb, sys, vb, vbe, vbs, vxd, wsc, wsf, wsh</div>
    			</div>
    			<div class="b-post__txt b-post__txt_bold b-post__txt_padbot_30">3. <?= ($isReqvsFilled?'<div class="b-post__txt b-post__txt_bold b-post__txt_inline-block b-post__txt_color_c10601">':'');?> У вас должен быть заполнен раздел «<a class="b-post__link" href="/users/<?=$_SESSION['login']?>/setup/finance/" target="_blank">Финансы</a>»<?= ($isReqvsFilled?'</div>':'')?></div>
    			<div class="b-buttons">
    				<a class="b-button b-button_rectangle_color_green <?= $isBtnDisabled ? "b-button_rectangle_color_disable" : ""?> b-button_margright_10" onclick="<?= !$isBtnDisabled ? "$('add_mod').set('value', 1); $('form_advice').submit();" : ""?>" href="javascript:void(0)" id="btn_send_moderate">
    					<span class="b-button__b1">
    						<span class="b-button__b2">
    							<span class="b-button__txt">Отправить на модерацию</span>
    						</span>
    					</span>
    				</a>
    				<a class="b-buttons__link" href="javascript:void(0)" onclick="$('form_advice').submit();">сохранить</a>
    				<span class="b-buttons__txt">&#160;или&#160;</span>
                    <?php if($opinion > 0 ) {?>
                    <a class="b-buttons__link b-buttons__link_color_c10601" href="/users/<?= $_SESSION['login']?>/opinions/?from=users&period=0#op_head">отказаться от рекомендации</a>
                    <?php } else {?>
    				<a class="b-buttons__link b-buttons__link_color_c10601" href="javascript:void(0)" onclick="if(confirm('Вы уверены, что хотите отказаться от рекомендации?')) xajax_DeclineAdvice(<?= (int)$advice['id']?>, <?= (int)$advice['status']?>);">отказаться от рекомендации</a>
                    <?php }//else?>
    			</div>
    		</div>
    	</form>
    	<? }//else?>
    </div><!--b-post-->
    													
</div>	