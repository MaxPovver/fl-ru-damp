{{include "header.tpl"}}
<div class="body c">
	<div class="main c">
					<h1 class="b-page__title">Мой счет</h1>
		<div class="rcol-big c">
			{{include "bill/bill_menu.tpl"}}
			<div class="tabs-in bill-t-in c">
    			<h3>Операция прошла успешно!</h3>
       <? if ($$is_pending) { ?>
    				<div class="bill-info">
          				<div class="warning">
          					<b class="b1"></b>
          					<b class="b2"></b>
          					<p><strong>Внимание:</strong> В данный момент идет заключительный этап операции пополнения вашего счета. Пожалуйста, подождите несколько секунд и обновите страницу.</p>
          					<b class="b2"></b>
          					<b class="b1"></b>
          				</div>
        </div>
       <? } ?>
    			<? if($$success_type == 'card') { ?>
    				<div class="bill-info">
          				<div class="warning">
          					<b class="b1"></b>
          					<b class="b2"></b>
          					<p><strong>Внимание:</strong> Пополнение счета с помощью пластиковой карты может занять определенное время.</p>
          					<b class="b2"></b>
          					<b class="b1"></b>
          				</div>
                    </div>

    			<? } else if(is_array($$success)) foreach($$success as $info) { $fullsum += $info['sum']; ?>
    			<div class="form bill-form-tc">
                    <b class="b1"></b>
                    <b class="b2"></b>
                    <div class="form-in">
                        <div class="form-block first last">
                            <div class="form-el">
                                <label class="form-label3" for="">Услуга:</label>
        						<span class="form-input-value"> <?=$info['name']?> <? if($info['descr'] && $info['descr'] != -1): ?>(<?=reformat($info['descr'],60,0,1)?>)<? endif; ?></span>
                            </div>
            				<div class="form-el">
                				<label class="form-label3" for="">Сумма:</label>
                				<span class="form-input-value"> <?= $info['sum'] ?></span>
            				</div>
            				<div class="form-el">
                				<label class="form-label3" for="">Дата:</label>
                				<span class="form-input-value"> <?=date('d.m.Y H:i (P \G\M\T)', strtotime($info['date']))?> </span>
            				</div>
    			         </div>
    			    </div>
    			    <b class="b2"></b>
    			    <b class="b1"></b>
    			</div>
    			<? } ?>
    			<? if($$back) { ?><p><a href="<?=$$back?>">Вернуться</a></p></br><? } ?>
    			<? if($$addinfo) { ?><p><?=$$addinfo?></p></br><? } ?>
                <?
                  if($$tmpPrj && $$tmpPrj->getProject()) {
                  	if ($$account->sum >= $$tmpPrj->getPrice()) {
                  		?><p>У вас есть подготовленный к публикации проект и достаточно денег для его оплаты.</p><?
                  	} else {
                  		?><p>У вас есть подготовленный к публикации проект, но недостаточно денег для его оплаты.</p><?
                  	}
             		?><p><a href="/public/?step=2&pk=<?=$$tmpkey?>">Перейти к проекту</a></p><br/><?
                  }

                  if($_SESSION['masssending'])
                  {
                    require_once ($_SERVER['DOCUMENT_ROOT'] . "/classes/professions.php");
                    if($sss=$_SESSION['masssending']['freelancers']) {
                      $prof_ids = array_keys($sss['sel_profs']);
                      if($prof_ids[0] && ($prof_name = professions::GetProfName($prof_ids[0]))) 
                        $prof_name = " в разделе \"{$prof_name}\"";
                      else
                        $prof_name = ' в разделе "Все фрилансеры"';
                      ?><br/>У Вас есть <a href="/freelancers/?prof=<?=$prof_ids[0]?>" class="blue">незавершенная рассылка<?=$prof_name?></a>.<?
                    }
                    if($sss=$_SESSION['masssending']['masssending']) {
                      ?><br/>У Вас есть <a href="/masssending/" class="blue">незавершенная рассылка по разделам</a>.<?
                    }
                  }
                ?>
				<p>Если у вас возникли вопросы &mdash; обращайтесь к нашему <?= webim_button(2, 'онлайн-консультанту', '')?> или в <a href="//feedback.free-lance.ru" target="_blank">Службу поддержки</a>. С удовольствием ответим.</p>
            </div>
            <?include $_SERVER['DOCUMENT_ROOT']."/engine/templates/bill/bill_promo".$$rand.".tpl"?>
            
		</div>
	</div>
</div>			
	
{{include "footer.tpl"}}
