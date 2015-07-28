<?php
foreach ($sbr_ratings as $sbr_data) { ?>
<div class="norisk-rate-one c">
    <div class="c">
        <span class="nr-num"><?= (++$i) ?>.&nbsp;</span>
        <div class="nr-prj-i" style="width:600px">
            <h4>
                <? if ($sbr_data['project_id']) { ?>
                    <a href="/projects/<?= $sbr_data['project_id'] ?>/" class="blue"><?= reformat($sbr_data['sbr_name'], 70, 0, 1) ?></a>
                <? } else { ?>
                    <strong><?= reformat($sbr_data['sbr_name'], 70, 0, 1) ?></strong>
                <? } ?>
            </h4>
            <p>Работодатель:
                <a href="/users/<?= $sbr_data['login'] ?>" class="employer-name">
                <?= $sbr_data['uname'] . ' ' . $sbr_data['usurname'] ?> [<?= $sbr_data['login'] ?>]
                </a>
            </p>
            <?if($sbr_data['scheme_type'] == sbr::SCHEME_LC || $sbr_data['scheme_type'] == sbr::SCHEME_PDRD2) {?>
            <p>Дата завершения: <?= date('j ' . strtolower($MONTHA[date('n', strtotime($sbr_data['completed']))]) . ' Y года', strtotime($sbr_data['completed'])) ?></p>
            <? $j=0; foreach($sbr_data['stages'] as $stage) {  ?>
            <div class="norisk-rate-one-task<?= ($stage['num']+1) == count($sbr_data['stages']) ? ' last' : ''?> c">	 
                <div class="nr-prj-i last">
                    <h4>Этап: <?=reformat($stage['name'], 80,0,1)?></h4>
                    <p>Дата завершения: <?=date('j '.strtolower($MONTHA[date('n', strtotime($stage['closed_time']))]).' Y года', strtotime($stage['closed_time']))?></p>
                </div>	 
            </div>	
            <? }//foreach?>
            
            <div class="nr-prj-rate">
                <b class="b1"></b>
                <b class="b2"></b>
                <div class="b-in">
                    Полученный рейтинг: <?= $sbr_data['to_rating'] ?>
                </div>
                <b class="b2"></b>
                <b class="b1"></b>
            </div>
            <? } else {?>
                <? $j=0; foreach($sbr_data['stages'] as $stage) {  ?>	 
                <div class="norisk-rate-one-task<?= ($stage['num']+1) == count($sbr_data['stages']) ? ' last' : ''?> c">	 
                    <div class="nr-prj-i last">	 
                      <h4>Этап: <?=reformat($stage['name'], 80,0,1)?></h4>	 
                      <p>Дата завершения: <?=date('j '.strtolower($MONTHA[date('n', strtotime($stage['closed_time']))]).' Y года', strtotime($stage['closed_time']))?></p>	 
                      <div class="nr-prj-rate">	 
                        <b class="b1"></b>	 
                        <b class="b2"></b>	 
                        <div class="b-in">	 
                          Полученный рейтинг: <?=$stage['sto_rating']?>	 
                        </div>	 
                        <b class="b2"></b>	 
                        <b class="b1"></b>	 
                      </div>	 
                    </div>	 
                </div>	 
                <? } ?>
            <? }//if?>
        </div>
    </div>
</div>
<?php } //if ?>