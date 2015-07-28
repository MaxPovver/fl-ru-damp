<? if($sbr->data['reserved_id']) {?>
<div class="i-shadow">
<div class="b-shadow  b-shadow_zindex_3 b-shadow_width_660 <?= $stage->error['arbitrage'] ? '' : 'b-shadow_hide'?>" id="arbitrage_form">
    <form id="arbitrageFrm" method="post" enctype="multipart/form-data">
        <div class="b-shadow__right">
            <div class="b-shadow__left">
                <div class="b-shadow__top">
                    <div class="b-shadow__bottom">
                        <div class="b-shadow__body b-shadow__body_bg_fff b-shadow__body_pad_20">
                            <h1 class="b-shadow__title b-shadow__title_fontsize_34 b-shadow__title_padbot_15">Обращение в Арбитраж</h1>
                            <div class="b-shadow__txt b-shadow__txt_padbot_5">Арбитраж Free-lance.ru — независимая комиссия, разбирающая спорные вопросы, возникающие в ходе проведения «Безопасной Сделки». К рассмотрению принимается переписка в сообщениях на сайте и внутри задачи. Личная почта, ICQ, Skype и прочие мессенджеры во внимание не берутся. </div>
                            <div class="b-shadow__txt b-shadow__txt_padbot_5">Отправляйте жалобу только в случае спорной ситуации с <?= ( $sbr->isEmp()?'исполнителем': 'заказчиком'); ?>, решить которую не удается. Учтите, что отозвать заявку из арбитража невозможно. По результатам разбирательства задача будет закрыта, а бюджет распределен между вами и <?= ( $sbr->isEmp()?'исполнителем': 'заказчиком'); ?>.</div>
                            <div class="b-shadow__txt b-shadow__txt_padbot_20">Если у вас просто вопрос по работе сервиса «Безопасная Сделка», обратитесь в нашу <a class="b-layout__link" href="https://feedback.fl.ru/">службу поддержки</a>.</div>
                            <div class="b-textarea <?= $stage->error['arbitrage']['descr']? 'b-textarea_error' : ''?>">
                                <textarea class="b-textarea__textarea b-textarea__textarea_height_250" name="descr" cols="" rows="" onfocus="$(this).getParent('.b-textarea').removeClass('b-textarea_error');"><?=htmlspecialchars($descr)?></textarea>        
                            </div>
                            
                            
                            <div class="b-file b-file_padtop_5">
                                <div class="b-fon b-fon__body b-fon__body_bg_fff attachedfiles_arb"></div>
                            </div>
                            
                            <div class="b-check b-check_padtop_15">
                                <input type="checkbox" name="iagree" id="f1" onclick="check_arb(this.checked)" class="b-check__input" />
                                <label class="b-check__label b-check__label_fontsize_13" for="f1">Я понимаю, что отозвать жалобу будет невозможно</label>
                            </div>	
                            <div class="b-buttons b-buttons_padtop_15">
                                <a class="b-button b-button_flat b-button_flat_green b-button_disabled"  href="javascript:void(0)" onclick="if(!$(this).hasClass('b-button_disabled')) $('arbitrageFrm').submit();" id="send_arbitrage">Обратиться в арбитраж</a>
                                <span class="b-buttons__txt b-buttons__txt_padleft_10">или</span>
                                <a class="b-buttons__link b-buttons__link_dot_c10601" href="javascript:void(0)" onclick="toggle_arb();">закрыть, не отправляя</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <input type="hidden" name="id" value="<?=$stage->id?>" />
            <input type="hidden" name="site" value="<?=$site?>" />
            <input type="hidden" name="action" value="arbitration" />
        </div>
        <div class="b-shadow__tl"></div>
        <div class="b-shadow__tr"></div>
        <div class="b-shadow__bl"></div>
        <div class="b-shadow__br"></div>
        <div class="b-shadow__icon b-shadow__icon_close" onclick="toggle_arb();"></div>
    </form>
</div> 
</div>   
<?= attachedfiles::getFormTemplate('attachedfiles_arb', 'sbr', array(
    'maxsize'  =>    sbr::MAX_FILE_SIZE,
    'maxfiles' =>    sbr::MAX_FILES,
    'graph_hint' =>  false
)) ?>
<script type="text/javascript">
window.addEvent("domready", function () {
    new attachedFiles2( $('arbitrageFrm').getElement('.attachedfiles_arb'), {
        'hiddenName':   'attaches[]',
        'files':        <?= json_encode($attachedfiles_files_arb) ?>
    }, '<?= $attachedfiles_arb->session[0]; ?>');
    });
</script>
<? }//if?>
