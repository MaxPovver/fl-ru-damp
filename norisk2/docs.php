<script type="text/javascript">
var SBR;
window.addEvent('domready', function() { SBR = new Sbr('docsAddFrm'); } );
Sbr.prototype.ERRORS=<?=sbr_meta::jsInputErrors($sbr->error['docs'])?>;
</script>
<div class="tabs-in nr-tabs-in2">
    <div class="lnk-nr-back">
        <a href=".">Вернуться в проекты по «Безопасным Сделкам»</a>
    </div>
    <div class="nr-prnt-project">
        Документы проекта: <strong class="nr-ico">#<?=$sbr->id?></strong> <a href="?id=<?=$sbr->id?>"><?=$sbr->name?></a>
    </div>
    <? if($sbr->docs) { ?>
        <div class="form form-nr-docs">
            <b class="b1"></b>
            <b class="b2"></b>
            <div class="form-in">
                <table>
                    <col width="40" />
                    <col width="100" />
                    <col />
                    <col width="60" />
                    <col width="45" />
                    <col width="140" />
                    <col width="105" />
                    <col width="195" />
                    <? $docs_cnt = count($sbr->docs); foreach($sbr->docs as $doc) { 
                        $aData = getAttachDisplayData(null, $doc['file_name'], $doc['file_path'] );
                        ?>
                        <tr class="<?=(++$i==1 ? 'first' : '')?><?=($i==$docs_cnt ? ' last' : '')?>">
                            <td class="nr-d-c1"><?=$i?>.</td>
                            <td>
                              <? if($doc['stage_id'] && ($stg=$sbr->getStageById($doc['stage_id']))) { ?>
                                <a href="/norisk2/?site=Stage&id=<?=$stg->id?>">Этап <?=$stg->getouterNum()?></a>
                              <? } else { ?>&nbsp;<? } ?>
                            </td>
                            <td><a <?=$aData['link']?> target="_blank"><?=$doc['name']?></a></td>
                            <td><?=ConvertBtoMB($doc['file_size'])?></td>
                            <td><?=strtoupper(CFile::getext($doc['file_name']))?></td>
                            <td class="nr-d-c5"><?=sbr::$docs_ss[$doc['status']][0]?></td>
                            <td><?=date('d.m.Y H:i', strtotime($doc[sbr::$docs_ss[$doc['status']][1]]))?></td>
                            <td align="right"><span style="font-size: 11px;" class="<?=$aData['virus_class']?>" <?=($aData['virus_class'] == 'avs-nocheck' ? 'title="Антивирусом проверяются файлы, загруженные после 1&nbsp;июня&nbsp;2011&nbsp;года"' : '')?>><nobr><?=$aData['virus_msg']?></nobr></span></td>
                        </tr>
                    <? } ?>
                </table>
            </div>
            <b class="b2"></b>
            <b class="b1"></b>
        </div>
    <? }
    else {
        ?>
        <div class="form form-nr-docs">
            <b class="b1"></b>
            <b class="b2"></b>
            <div class="form-in">
            Документы не загружены
            </div>
            <b class="b2"></b>
            <b class="b1"></b>
        </div>
        <?php
    }
     ?>
    <?/*=$sbr->doc_form($sbr->post_doc, $stage_id, FALSE)*/?>
    <p><strong>Обратите внимание</strong><br/><br/>
	Исполнитель не может получить деньги до получения ресурсом всех необходимых документов.</p>
</div>
