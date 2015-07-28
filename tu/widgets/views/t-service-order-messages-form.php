<div class="b-layout b-layout_padleft_60 b-layout__txt_padleft_null_iphone<?=$is_scroll?' autoscroll':''?>" id="form-block">
    <h3 class="b-layout__h3">Обсуждение заказа</h3>
    <form  id="message-form" action="#" method="post" enctype="multipart/form-data">
        <input type="hidden" name="hash" value="<?=$param_hash?>" />
        <input type="hidden" name="orderid" value="<?=$order_id?>" />
        <div class="b-layout__txt b-layout__txt_padtop_10">Новое сообщение:</div>
        <div class="b-textarea">
            <textarea class="b-textarea__textarea" data-validators="minLength:2" id="tservice-message-textarea"></textarea>
        </div>
        <div class="b-layout__txt b-layout__txt_padtop_20">
            <div class="b-layout__txt b-layout__txt_padbot_10">
                <a class="b-layout__link" href="#" 
                   onClick="this.getParent().hide();$('attachedfiles').removeClass('b-layout_hide');return false;"><span class="b-icon b-icon__ref"></span></a>
                <a class="b-layout__link b-layout__link_bordbot_dot_0f71c8" href="#" 
                   onClick="this.getParent().hide();$('attachedfiles').removeClass('b-layout_hide');return false;">
                    Добавить файлы в сообщение
                </a>
            </div>

            <!-- Attaches -->
            <div id="attachedfiles" class="b-fon b-fon_padbot_10 b-layout_hide"></div>
            <script type="text/javascript">
                var TU_ORDER_MSG_SESS = '<?=$attachedfiles_session?>';
                var TU_ORDER_MSG_MAX_FILES = '<?=TServiceMsgModel::MAX_FILES?>';
                var TU_ORDER_MSG_MAX_FILE_SIZE = '<?=TServiceMsgModel::MAX_FILE_SIZE?>';
                var TU_ORDER_MSG_EXT = '<?=implode(', ', $GLOBALS['disallowed_array'])?>';
                var TU_ORDER_MSG_KEY = '<?=get_uid(false)?>';
            </script>
            <!-- /Attaches -->
        </div>
        <div class="b-buttons b-buttons_padtb_10">
            <a href="#" class="b-button b-button_flat b-button_flat_green">Отправить сообщение</a>
        </div>
    </form>
</div>
