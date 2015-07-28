<?php if($sbr->isAdmin()) { ?>
<script type="text/javascript">
var SBR;
window.addEvent('domready', function() { SBR = new Sbr('<?=$sbr->post_doc['id'] ? 'docsEditFrm' : 'docsAddFrm'?>'); } );
Sbr.prototype.ERRORS=<?=sbr_meta::jsInputErrors($sbr->error['docs'])?>;
var checked_cnt=0;
</script>
<?php }?>
<div class="tabs-in nr-tabs-in2">
	<div class="lnk-nr-back">
        <a href=".">Вернуться в проекты по «Безопасным Сделкам»</a>
	</div>
	<div class="nr-prnt-project">
        Документы проекта: <strong class="nr-ico">#<?=$sbr->id?></strong> <a href="?id=<?=$sbr->id?>"><?=$sbr->name?></a>
	</div>
    <? if($sbr->docs) { ?>
        <?php if($sbr->isAdmin()) { ?><form action="." method="post" onsubmit="if(!checked_cnt)return false;if(this['action'].value=='delete')return window.confirm('Вы действительно хотите удалить выбранные документы?');"><?php }//if?>
            <div class="form form-nr-docs">
                <b class="b1"></b>
                <b class="b2"></b>
                <div class="form-in">
                    <table id="docsTbl">
                        <col width="40" />
                        <col width="20" />
                        <col width="90" />
                        <col />
                        <col width="180" />
                        <col width="40" />
                        <col width="40" />
                        <col width="5" />
                        <col width="105" />
                        <?php if($sbr->isAdmin()) { ?><col width="50" /><?php }//if?>
                        <? $docs_cnt = count($sbr->docs); foreach($sbr->docs as $doc) { ?>
                            <tr class="<?=(++$i==1 ? 'first' : '')?><?=($i==$docs_cnt ? ' last' : '')?><?=($sbr->post_doc['id']==$doc['id'] ? ' tr-edit' : '')?>"<?=($sbr->post_doc['id']==$doc['id'] ? ' id="edit_tr"' : '')?>>
                                <th><input type="checkbox" name="id[]" value="<?=$doc['id']?>" onclick="checked_cnt+=(this.checked*2-1)" <?=(!$sbr->isAdmin()?"disabled":"")?>/></th>
                                <td class="nr-d-c1"><?=$i?>.</td>
                                <td>
                                  <? if($doc['stage_id'] && ($stg=$sbr->getStageById($doc['stage_id']))) { ?>
                                    <a href="/norisk2/?site=Stage&id=<?=$stg->id?>">Этап <?=$stg->getOuterNum()?></a>
                                  <? } else { ?>&nbsp;<? } ?>
                                </td>
                                <td><a href="<?=WDCPREFIX.'/'.$doc['file_path'].$doc['file_name']?>" target="_blank"><?=$doc['name']?></a></td>
                                <td class="nr-d-c6">
                                  <? if(($doc['access_role'] & sbr::DOCS_ACCESS_EMP) == sbr::DOCS_ACCESS_EMP) { ?><span class="nr-d-e">Работодатель</span><? } ?>
                                  <? if(($doc['access_role'] & sbr::DOCS_ACCESS_FRL) == sbr::DOCS_ACCESS_FRL) { ?><span class="nr-d-f">Исполнитель</span><? } ?>
                                </td>
                                <td><?=ConvertBtoMB($doc['file_size'])?></td>
                                <td><?=strtoupper(CFile::getext($doc['file_name']))?></td>
                                <td class="nr-d-c5"><?=sbr::$docs_ss[$doc['status']][0]?></td>
                                <td><?=date('d.m.Y H:i', strtotime($doc[sbr::$docs_ss[$doc['status']][1]]))?></td>
                                <?php if($sbr->isAdmin()) { ?>
                                <td><a href="javascript:;" title="Редактировать" onclick="SBR.initDocForm(this, <?=$doc['sbr_id']?>, <?=$doc['id']?>)"><img src="/images/btn-edit2.png" alt="Редактировать" /></a>&nbsp;
                                    <a href="javascript:;" title="Удалить" onclick="SBR.delDoc(this, <?=$doc['sbr_id']?>, <?=$doc['id']?>)"><img src="/images/btn-remove2.png" alt="Удалить" /></a>
                                </td>
                                <?php }//if?>
                            </tr>
                        <? } ?>
                    </table>
                </div>
                <b class="b2"></b>
                <b class="b1"></b>
            </div>
       <?php if($sbr->isAdmin()) { ?>
            <div class="nr-docs-options">С отмеченными:
                <select name="action" style="width:auto">
                    <? foreach(sbr::$docs_access as $id=>$val) { ?>
                      <option value="set_access=<?=$id?>">Доступ: <?=$val[0]?></option>
                    <? } ?>
                    <option>------------</option>
                    <? foreach(sbr::$docs_ss as $id=>$val) { ?>
                      <option value="set_status=<?=$id?>">Статус: <?=$val[0]?></option>
                    <? } ?>
                    <option>------------</option>
                    <option value="delete">Удалить</option>
                </select>
                <input type="hidden" name="site" value="<?=$site?>" />
                <input type="hidden" name="sbr_id" value="<?=$sbr->id?>" />
                <input type="submit" value="Выполнить" class="i-btn" />
            </div>
        </form>
        <?php } //if?>
    <? } ?>
    <?php if($sbr->isAdmin()) { ?>
    <div id="doc_edit_box"><? if($sbr->post_doc['id']) echo $sbr->doc_form($sbr->post_doc, $stage_id, TRUE); ?></div>
    <?=$sbr->doc_form($sbr->post_doc && !$sbr->post_doc['id'] ? $sbr->post_doc : NULL, $stage_id, FALSE)?>
     <?php } //if?>
	<p><strong>Обратите внимание</strong><br/><br/> Исполнитель не может получить деньги до получения ресурсом всех необходимых документов.</p>
</div>
