							<? if($sbr->error['new_version']) { ?>
							<div class="nr-cancel-reason c">
								<p>Операция (отказ) была отменена, так как заказчик успел внести новые изменения в условия задачи. Пожалуйста, ознакомтесь и примите решение.</p>
							</div>
							<br />
							<? } ?>
							<div class="form form-changed-btns">
								<b class="b1"></b>
								<b class="b2"></b>
								<div class="form-in">
                                    <form action="." method="post" id="changedFrm">
                                    <div>
                                        <div class="nr-prj-btns c">
                                            <div class="btn-margin">
                                                <span class="btn-o-red">
                                                    <a href="javascript:;" onclick="document.getElementById('rrbox').style.display='block'; return false;" class="btnr btnr-red"><span class="btn-lc"><span class="btn-m"><span class="btn-txt">Отказаться</span></span></span></a>
                                                </span>
                                                <span class="btn-o-green">
                                                    <a href="javascript:;" onclick="SBR.sendForm({ok:1})" class="btnr btnr-green2"><span class="btn-lc"><span class="btn-m"><span class="btn-txt">Я согласен с изменениями</span></span></span></a>
                                                    <input type="hidden" name="ok" value="" />
                                                </span>
                                            </div>
                                            <div class="nr-warning">
                                                <strong>Будьте внимательны при согласии с условиями, </strong><br />
                                                текущее действие необратимо без вмешательства Арбитража
                                            </div>
                                            <div class="f-overlay f-o-nr-c" id="rrbox" style="display:none">
                                                <div class="f-overlay-in">
                                                    <h3>Укажите причину отказа</h3>
                                                    <div class="f-overlay-cnt"><textarea name="frl_refuse_reason" rows="5" cols="10" id="rrtext"></textarea></div>
                                                    <div class="f-overlay-btns">
                                                        <input type="submit" value="Отказаться" class="i-btn i-bold" />
                                                        <input type="button" value="Отменить" class="i-btn" onclick="document.getElementById('rrbox').style.display='none'; document.getElementById('rrtext').value=''; return false;" />
                                                  </div>
                                                </div>
                                            </div>
                                        </div>

                                        <input type="hidden" name="version" value="<?=$stage->version?>" />
                                        <input type="hidden" name="sbr_version" value="<?=$sbr->version?>" />
                                        <input type="hidden" name="site" value="<?=$site?>" />
                                        <input type="hidden" name="id" value="<?=$stage->id?>" />
                                        <input type="hidden" name="action" value="agree_stage" />
                                    </div>
                                    </form>
								</div>
								<b class="b2"></b>
								<b class="b1"></b>
							</div>
