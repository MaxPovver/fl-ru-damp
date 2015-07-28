<h3><?=($is_edit ? 'Редактировать документ' : 'Новый документ')?></h3>
<div class="form form-nr-docs-add<?=($is_edit ? ' form-nr-docs-edit' : '')?>">
	<b class="b1"></b>
	<b class="b2"></b>
	<div class="form-in">
          <form action="." method="post" enctype="multipart/form-data" id="<?=($is_edit ? 'docsEditFrm' : 'docsAddFrm')?>">
              <div class="form-block first">
                  <div class="form-el">
                      <label class="form-label3">Название документа:</label>
                      <span class="nra-doc-title">
                          <input type="text" name="name" value="<?=html_attr($doc['name'])?>" maxlength="40" />
                      </span>
                      <div class="tip" style="left:585px"></div>
                      <span class="form-hint">Максимум 40 символов</span>
                  </div>
                  <div class="form-el">
                      <label class="form-label3">Этап проекта:</label>
                      <span class="nra-doc-sel">
                          <select name="stage_id">
                              <option value="0">Весь проект</option>
                              <? foreach($sbr->stages as $stg) { ?>
                              <option value="<?=$stg->id?>"<?=($stg->id==$stage_id ? ' selected="true"' : '')?>><?=$stg->getOuterNum().': '.$stg->name?></option>
                              <? } ?>
                          </select>
                      </span>
                  </div>
                  <div class="form-el">
                      <label class="form-label3">Тип документа:</label>
                      <span class="nra-doc-sel">
                          <select name="type">
                              <?
                                foreach(sbr::$docs_types as $type=>$val) {
                                    if(!$sbr->isAdmin() && !($val[1] & (sbr::DOCS_ACCESS_EMP*$sbr->isEmp() | sbr::DOCS_ACCESS_FRL*$sbr->isFrl()))) continue;
                              ?>
                              <option value="<?=$type?>"<?=($type==$doc['type'] ? ' selected="true"' : '')?>><?=$val[0]?></option>
                              <? } ?>
                          </select>
                      </span>
                  </div>
                  <div class="form-el">
                      <label class="form-label3">Выберите файл:</label>
                      <span class="nra-docs-file">
                          <? if($is_edit) { ?>
                            <ul class="form-files-added">
                                <li>
                                    <? /* <a href="javascript:;" title="Удалить" onclick=""><img src="/images/btn-remove2.png" alt="Удалить" /></a> */ ?><a href="<?=WDCPREFIX.'/'.$doc['file_path'].$doc['file_name']?>" target="_blank" class="mime <?=CFile::getext($doc['file_name'])?>"><?=$doc['file_name']?></a>
                                </li>
                            </ul>
                          <? } ?>
                          <span class="form-hint">
                              2 МБ: DOC, DOCX, ZIP, RAR, PDF и др.
                          </span>
                          <input type="file" name="attach" size="23" />
                      </span>
                      <? if($doc['file_id']) { ?>
                        <span class="form-hint">Существующий файл будет заменен новым</span>
                      <? } ?>
                  </div>
                  <? if($sbr->isAdmin()) { ?>
                    <div class="form-el">
                        <label class="form-label3">Статус документа:</label>
                        <span class="nra-doc-sel">
                            <select name="status">
                                <? foreach(sbr::$docs_ss as $id=>$val) { ?>
                                <option value="<?=$id?>"<?=($id==$doc['status'] ? ' selected="true"' : '')?>><?=$val[0]?></option>
                                <? } ?>
                            </select>
                        </span>
                    </div>
                    <div class="form-el">
                        <label class="form-label3">Доступ просмотра:</label>
                        <span class="nra-doc-sel">
                            <select name="access_role">
                                <? foreach(sbr::$docs_access as $id=>$val) { ?>
                                <option value="<?=$id?>"<?=($id==$doc['access_role'] ? ' selected="true"' : '')?>><?=$val[0]?></option>
                                <? } ?>
                            </select>
                        </span>
                    </div>
                  <? } ?>
              </div>
              <div class="form-block last">
                  <div class="form-btn">
                      <input type="submit" value="<?=($is_edit ? 'Редактировать' : 'Загрузить документ')?>" class="i-bold i-btn" />
                  </div>
              </div>
              <input type="hidden" name="site" value="docs" />
              <input type="hidden" name="id" value="<?=$doc['id']?>" />
              <input type="hidden" name="sbr_id" value="<?=$sbr->id?>" />
              <input type="hidden" name="action" value="<?=$is_edit ? 'edit' : 'add'?>_doc" />
              <input type="hidden" name="u_token_key" value="<?=$_SESSION['rand']?>" />
          </form>
	</div>
	<b class="b2"></b>
	<b class="b1"></b>
</div>
