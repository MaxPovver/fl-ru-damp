<?php include('xajax.php');?>
<h2>Услуги</h2>
<div class="docs-block c">
    <div class="docs-content c">
        <div class="docs-cnt">
            <div class="docs-breadcrumb">
                <a href="/service/docs/">Вернуться на главную</a>
            </div>
            <h3><?= $section['name'];?></h3>
<? include('search_form.php');?>
            <?php if(is_array($search_results) && count($search_results)){ ?>
            <div class="help-search-res">
                <div class="help-search-info">Найдено <?= count($search_results).' '.getTermination(count($search_results), array('совпадение','совпадения','совпадений'));?></div>
            <ol start="1">
            <?php foreach($search_results as $res){ ?>


                <li>
            <h4><a href="/service/docs/document/?id=<?= $res['id'];?>"><?= $res['name'];?></a></h4>
            <p><?= $res['desc'];?></p>

        </li>
            <?} ?>
        </ol>
            </div>
           <?}else{ //if ?>
            <div class="help-search-fail"> 
				<strong>Увы, по вашему запросу не найдено совпадений.</strong><br>
				Пожалуйста, попробуйте сформулировать запрос иначе и повторить поиск. Вы можете обратиться в <a href="https://feedback.fl.ru/">Службу поддержки</a>.
            </div>
            <?php } ?>
        </div>
    </div>
</div>
