                            <div class="form form-complite form-nr-frlc">
                                <div class="form-h">
                                    <b class="b1"></b>
                                    <b class="b2"></b>
                                    <div class="form-h-in">
                                        <h3>Исполнитель отказался от новых условий к задаче!</h3>
                                    </div>
                                </div>
                                <div class="form-in">
                                    <form action="." method="post" id="changedFrm">
                                    	<div class="form-block first">
                                    	    <div class="form-el">
                                    	        <strong class="reason-title">Причина:</strong>
                                    	        <div class="reason-txt">
                                    	            <p><?=$stage->data['frl_refuse_reason'] ? reformat($stage->data['frl_refuse_reason'], 100) : 'Не указана'?></p>
                                    	        </div>
                                    	    </div>
                                    	</div>
                                    	<div class="form-block last">
                                    	    <div class="form-el">
                                    	        <div class="nr-prj-btns c">
                                    	            <input type="submit" name="cancel" value="Отменить изменения" class="i-btn" />
                                    	            <input type="submit" name="resend" value="Повторный запрос" class="i-btn i-bold" />
                                                    <? if($new_dead_time_ex || $new_work_time_ex || $new_cost_ex || $new_descr_ex) { ?>
                                                        <input type="button" value="Редактировать условия" class="i-btn i-bold" onclick="document.location.href='?site=editstage&id=<?=$stage->data['id']?>&v=1'"/>
                                                    <? } ?>
                                    	        </div>
                                    	    </div>
                                    	</div>
                                        <input type="hidden" name="site" value="<?=$site?>" />
                                        <input type="hidden" name="id" value="<?=$stage->data['id']?>" />
                                        <input type="hidden" name="action" value="resolve_changes" />
                                    </form>
                                </div>
                                <b class="b2"></b>
                                <b class="b1"></b>
                            </div>
