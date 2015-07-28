{{include "header.tpl"}}
<div class="body c">
	<div class="main c">
					<h1 class="b-page__title">Мой счет</h1>
		<div class="rcol-big c">
			{{include "bill/bill_menu.tpl"}}
			<div class="tabs-in bill-t-in c">
    			<h3>Операция не выполнена.</h3>
    			<div class="form bill-form-tc">
                    <b class="b1"></b>
                    <b class="b2"></b>
                    <div class="form-in">
                        <div class="form-block first last">
                            <div class="form-el">
                                <label class="form-label3" for="">Ошибка:</label>
        						<span class="form-input-value"><?=($$error ? $$error : 'Неизвестная ошибка.')?></span>
                            </div>
    			         </div>
    			    </div>
    			    <b class="b2"></b>
    			    <b class="b1"></b>
    			</div>
    			<? if($$back) { ?><p><a href="<?=$$back?>">Вернуться</a></p></br><? } ?>
    			<? if($$addinfo) { ?><p><?=$$addinfo?></p></br><? } ?>
				<p>Если у вас возникли вопросы &mdash; обращайтесь в <a href="//feedback.free-lance.ru" target="_blank">Службу поддержки</a>. С удовольствием ответим.</p>
            </div>
		</div>
	</div>
</div>			
	
{{include "footer.tpl"}}