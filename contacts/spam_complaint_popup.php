<div id="spam_complaint_popup" class="b-shadow b-shadow_center b-shadow_pad_20 b-shadow_zindex_3 b-shadow_width_320 b-shadow_hide">
            <form id="spam_complaint_form" name="spam_complaint_form" action="">
            <h3 class="b-layout__h3">При желании оставьте комментарий для модераторов</h3>
            <div class="b-textarea">
                <textarea id="spam_complaint_txt" name="spam_complaint_txt" class="b-textarea__textarea b-textarea__textarea__height_140 b-textarea__textarea__width_395" cols="80" rows="5"></textarea>
            </div>
                <div class="b-buttons b-buttons_padtop_10">
					<a id="spam_complaint_send" class="b-button b-button_flat b-button_flat_grey" onclick="sendSpamComplaint();return false;"  href="#">Отправить жалобу</a>&#160;
                    <a id="spam_complaint_close" class="b-buttons__link b-buttons__link_dot_039" href="#" onclick="popupSpamComplaint(0);return false;">Закрыть, не отправляя</a>
                </div>
            </form>
</div>