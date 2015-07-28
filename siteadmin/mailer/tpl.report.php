<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/xajax/mailer.common.php");
$xajax->printJavascript('/xajax/'); 
?>

<script type="text/javascript">
window.addEvent('domready', 
function() {
    $$('.b-fon-subfilter').addClass('b-fon_hide');
    $$('.b-layout__txt').removeClass('b-layout__txt_hide');
    
	$$('.show-filter').addEvent('click',function(){
		this.getParent('.b-layout__txt').getParent('.b-layout__txt').getNext('.b-layout__inner').toggleClass('b-layout__inner_hide');
		this.getPrevious('.b-layout__ygol').toggleClass('b-layout__ygol_hide');
		return false;
		})
	$$('.show-settings a').addEvent('click',function(){
		this.getParent('.b-layout__txt').getNext('.b-fon').toggleClass('b-fon_hide');
		this.getParent('.b-layout__txt').toggleClass('b-layout__txt_hide');
		return false;
		})
	$$('.mail-pause').addEvent('click',function(){
		this.getParent('.b-layout__txt').getElement('.b-layout__mail-icon').toggleClass('b-layout__mail-icon_black').toggleClass('b-layout__mail-icon_pause');
		if(this.get('text')=='Поставить на паузу'){this.set('text', 'Снять с паузы')}else{this.set('text', 'Поставить на паузу')}
		return false;
		});
    
    
    $$('.b-button_admin_del').dispose();
    $('filter_employer').getElements('select').each(function(el){
        el.disabled = true;
    });
    $('filter_freelancer').getElements('select').each(function(el){
        el.disabled = true;
    });
    $$('.b-combo__input-text').each(function(el) {
        el.setProperty('readonly', 'readonly');
    });
    $$('.b-check__input').each(function(el) {
        el.disabled = true;
    });
    $$('.b-combo__input').addClass('b-combo__input_disabled');
})
</script>

<div class="b-layout">	
    <a class="b-button b-button_flat b-button_flat_green b-button_float_right close-block "  href="/siteadmin/mailer/?action=edit&id=<?=$message['id']?>">Повторить рассылку</a>
    <h2 class="b-layout__title b-layout__title_padbot_30">Отчёт по рассылке  &#160;&#160;&#160;<a class="b-layout__link b-layout__link_fontsize_13" href="/siteadmin/mailer/">Все рассылки</a></h2>
    <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_5" cellpadding="0" cellspacing="0" border="0">
        <tr class="b-layout__tr">
            <td class="b-layout__left b-layout__left_width_130">
                <div class="b-layout__txt">Тема письма</div>
            </td>
            <td class="b-layout__right">
                <div class="b-layout__txt b-layout__txt_bold b-layout__txt_fontsize_15"><?= reformat($message['subject'], 30)?></div>
            </td>
        </tr>
    </table>
    <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_20" cellpadding="0" cellspacing="0" border="0">
        <tr class="b-layout__tr">
            <td class="b-layout__left b-layout__left_width_130">
                <div class="b-layout__txt">Текст письма</div>
            </td>
            <td class="b-layout__right">
                <div class="b-layout__txt b-layout__txt_padbot_10"><?= reformat($message['message'], 30)?></div>
                
                <?php if($attachedfiles_files) { ?>
                <div class="b-fon b-fon_padbot_20 b-fon_width_full b-file">
                    <div class="b-fon__body b-fon__body_pad_10 b-fon__body_fontsize_13 b-fon__body_bg_f0ffdf i-button">
                        <table cellspacing="0" cellpadding="0" border="0" class="b-icon-layout__table">
                            <tbody>
                                <?php foreach($attachedfiles_files as $file) { $cFile = new CFile($file['fid']); ?>
                                <tr class="b-icon-layout__tr">
                                    <td class="b-icon-layout__icon b-icon-layout__icon_height_25"><i class="b-icon b-icon_attach_<?= $cFile->getext();?>"></i></td>
                                    <td class="b-icon-layout__files b-icon-layout__files_fontsize_13"><a href="<?= (WDCPREFIX."/{$cFile->path}/{$cFile->name}");?>" class="b-icon-layout__link b-icon-layout__link_fontsize_13"><?=$cFile->original_name?></a>, <?=ConvertBtoMB($cFile->size);?></td>
                                </tr>
                                <?php }//foreach?>
                            </tbody>
                        </table>							
                    </div>
                </div>	
                <?php }//if?>
            </td>
        </tr>
    </table>
    <table class="b-layout__table b-layout__table_width_full b-layout__table_margbot_5" cellpadding="0" cellspacing="0" border="0">
        <tr class="b-layout__tr">
            <td class="b-layout__left b-layout__left_width_130">
                <div class="b-layout__txt">Получатели</div>
            </td>
            <td class="b-layout__right">
                <div class="b-layout__txt b-layout__txt_bold b-layout__txt_fontsize_15">
                    <?php if ($message['filter_file']) { ?>
                        <a href="<?=WDCPREFIX.'/mailer/'.$message['filter_file']?>" target="_blank">из файла</a>
                    <?php } else echo (int) ($sum_rec).' '.ending((int) ($sum_rec), "человек", "человека", "человек")?></div>
            </td>
        </tr>
    </table>
<?php if (!$message['filter_file']) { ?>
    <div class="b-layout__txt b-layout__txt_margleft_130 b-layout__txt_padbot_5 b-username">
        <span class="b-username__role b-username__role_emp"></span>
        <span class="b-username__txt b-username__txt_color_6db335">Работодатели</span> &mdash; <?= ($message['filter_emp'] > 0 ? (int) $message['count_rec_emp'] : 0); ?> &#160;&#160;
        <span class="b-layout__txt b-layout__txt_inline-block b-layout__txt_padleft_5 b-layout__txt_top_-1">
            <span class="b-layout__ygol  b-layout__ygol_hide"></span>
            <a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_bordbot_dot_0f71c8 show-filter" href="#">Показать фильтры</a>
        </span>
    </div>
    <?$message['filter_emp'] = false; // Не показывать фильтр по умолчанию?>
    <? include ("tpl.filter.emp.php"); ?>

    <div class="b-layout__txt b-layout__txt_margleft_130 b-layout__txt_padbot_5 b-username">
        <span class="b-username__role b-username__role_frl"></span>
        <span class="b-username__txt b-username__txt_color_fd6c30">Фрилансеры</span> &mdash; <?= ($message['filter_frl'] > 0 ? (int) $message['count_rec_frl'] : 0);?> &#160;&#160;
        <span class="b-layout__txt b-layout__txt_inline-block b-layout__txt_padleft_5 b-layout__txt_top_-1">
            <span class="b-layout__ygol  b-layout__ygol_hide"></span>
            <a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_bordbot_dot_0f71c8 show-filter" href="#">Показать фильтры</a>
        </span>
    </div>
    <?$message['filter_frl'] = false; // Не показывать фильтр по умолчанию?>
    <? include ("tpl.filter.frl.php"); ?>
<?php } ?>

    <table class="b-layout__table b-layout__table_width_full b-layout__table_margtop_30" cellpadding="0" cellspacing="0" border="0">
        <tr class="b-layout__tr">
            <td class="b-layout__left b-layout__left_width_130">
                <div class="b-layout__txt">Отправлено</div>
            </td>
            <td class="b-layout__right">
                <div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_10 b-layout__txt_fontsize_15"><?= date('d.m.Y в H:i', strtotime($message['real_date_sending']))?></div>
                <div class="b-layout__txt b-layout__txt_padbot_5">
                    <?= ($message['type_regular'] > 1)?"Рассылается регулярно.": ""?>

                    <?php if($message['type_regular'] > 1) {?>
                    <?=  mailer::$TYPE_REGULAR[$message['type_regular']];?>
                    <?= !empty(mailer::$SUB_TYPE_REGULAR[$message['type_regular']]) ? strtolower(mailer::$SUB_TYPE_REGULAR[$message['type_regular']][$message['type_send_regular']]) : ""; ?>
                    <?php }//if?>
                </div>
                <?php if($message['type_regular'] > 1) {?>
                <div class="b-layout__txt b-layout__txt_padbot_5">
                    <input type="hidden" id="status_sending" value="<?=$message['status_sending']?>">
                    <span class="b-layout__mail-icon <?= $message['status_sending'] == 1?"b-layout__mail-icon_black":"b-layout__mail-icon_pause"?> b-layout__mail-icon_top_4 b-layout__mail-icon_margleft_-15 b-layout__mail-icon_margright_4"></span>
                    Следующая рассылка <?=date('d.m.Y в H:i', strtotime($message['date_sending']))?>.&#160;&#160;
                    <a class="b-layout__link b-layout__link_fontsize_11 b-layout__link_bordbot_dot_0f71c8 mail-pause" href="javascript:void(0)" onclick="xajax_setStatusSending(<?=(int)$message['id']?>, $('status_sending').get('value'));"><?= $message['status_sending'] == 1?"Поставить на паузу":"Снять с паузы"?></a>
                </div>
                <?php } //if?>
            </td>
        </tr>
    </table>
		
		
		<!--
		<table class="b-layout__table b-layout__table_width_full b-layout__table_margtop_30" cellpadding="0" cellspacing="0" border="0">
			<tr class="b-layout__tr">
					<td class="b-layout__left b-layout__left_width_130">&#160;</td>
					<td class="b-layout__right">
						<div class="b-layout__txt b-layout__txt_bold b-layout__txt_padbot_10 b-layout__txt_fontsize_15">Отчет</div>
						<table class="b-layout__table b-layout__table_margbot_5" cellpadding="0" cellspacing="0" border="0">
							<tr class="b-layout__tr">
									<td class="b-layout__left b-layout__left_width_150">
										<div class="b-layout__txt">Прочли письмо</div>
									</td>
									<td class="b-layout__right b-layout__right_right b-layout__right_width_60">
										<div class="b-layout__txt b-layout__txt_bold b-layout__txt_fontsize_15">736 188</div>
									</td>
							</tr>
						</table>
						<table class="b-layout__table b-layout__table_margbot_15" cellpadding="0" cellspacing="0" border="0">
							<tr class="b-layout__tr">
									<td class="b-layout__left b-layout__left_width_150 b-layout__left_bordbot_double_ccc">
										<div class="b-layout__txt b-layout__txt_italic b-layout__txt_fontsize_11 b-layout__txt_padbot_5">Ссылка</div>
									</td>
									<td class="b-layout__right b-layout__right_width_120 b-layout__right_bordbot_double_ccc b-layout__right_right">
										<div class="b-layout__txt b-layout__txt_italic b-layout__txt_fontsize_11 b-layout__txt_padbot_5">Кол-во переходов, шт</div>
									</td>
							</tr>
						</table>
						<table class="b-layout__table b-layout__table_margbot_5" cellpadding="0" cellspacing="0" border="0">
							<tr class="b-layout__tr">
									<td class="b-layout__left b-layout__left_width_150">
										<div class="b-layout__txt b-layout__txt_padbot_5">%URL_PORTFOLIO%</div>
									</td>
									<td class="b-layout__right b-layout__right_right b-layout__right_width_60">
										<div class="b-layout__txt b-layout__txt_bold b-layout__txt_fontsize_15 b-layout__txt_padbot_5">513 382</div>
									</td>
							</tr>
							<tr class="b-layout__tr">
									<td class="b-layout__left b-layout__left_width_150">
										<div class="b-layout__txt b-layout__txt_padbot_5">%URL_LK%</div>
									</td>
									<td class="b-layout__right b-layout__right_right b-layout__right_width_60">
										<div class="b-layout__txt b-layout__txt_bold b-layout__txt_fontsize_15 b-layout__txt_padbot_5">75 140</div>
									</td>
							</tr>
							<tr class="b-layout__tr">
									<td class="b-layout__left b-layout__left_width_150">
										<div class="b-layout__txt b-layout__txt_padbot_5">%URL_BILL%</div>
									</td>
									<td class="b-layout__right b-layout__right_right b-layout__right_width_60">
										<div class="b-layout__txt b-layout__txt_bold b-layout__txt_fontsize_15 b-layout__txt_padbot_5">109 881</div>
									</td>
							</tr>
						</table>
					</td>
			</tr>
		</table>
	-->
	
	
	
	
</div>