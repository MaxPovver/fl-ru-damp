<?php
if($attach=$projects->GetAllAttach($prj['id'])) {          
              foreach ($attach as $a) 
              {?>
               <? if ($a["virus"][3] != 1)  {?>
              		<div class="flw_offer_attach">
              			<div style="width:250px; float:left">
	              			<a href="<?=WDCPREFIX.'/'.$a['path'].$a['name']?>" target="_blank">Загрузить</a> 
	              			(<?=$a['ftype']?>; <?=ConvertBtoMB($a['size'])?> )
	              		</div>
	              			
              			<? 
              			switch ($a["virus"]) { 
							 case "":?> <span title="Антивирусом проверяются файлы, загруженные после 1 июня 2011 года" class="avs-nocheck">Файл не проверен антивирусом</span> <?
              				 break;
              				
              				 case "0000":?> <span title="Антивирусом проверяются файлы, загруженные после 1 июня 2011 года" class="avs-ok">Проверено антивирусом</span> <?
              				 break;
              				 
              				 case "0010":?> <span title="Антивирусом проверяются файлы, загруженные после 1 июня 2011 года" class="avs-errcheck">Невозможно проверить</span> <?
              				 break;              				 
              			}
              			?>
              		</div>
              	<? }?>
            <?}
 }