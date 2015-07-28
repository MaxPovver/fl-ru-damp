<?php include('xajax.php');?>
        <div class="b-menu b-menu_crumbs">
            <ul class="b-menu__list">
                <li class="b-menu__item"><a class="b-menu__link" href="/service/">Все услуги сайта</a>&nbsp;&rarr;&nbsp;</li>
            </ul>
        </div>
        <h1 class="b-page__title">Шаблоны документов</h1>
        <? if (hasPermissions("adm")) {?><div><a href="/service/docs/admin/" style="color:red" target="_blank">Редактировать документы и разделы</a></div><div style="clear:both">&nbsp;</div><? }?>
        <div class="docs-block c">
                <div class="docs-cnt">
                    <div class="b-layout__txt b-layout__txt_padbot_20">Раздел  содержит образцы и шаблоны разнообразных типовых документов. Теперь вам не придется самостоятельно придумывать формулировки для документации. У нас вы найдете большое количество стандартных документов и образцов их заполнения. Использование шаблонов поможет вам сэкономить драгоценное время. Не упускайте такую возможность!</div>
        <? include('search_form.php');?>
                    <div class="b-layout__txt b-layout__txt_padbot_20">Все документы разбиты по рубрикам:</div>
                    <div class="wrap-docs-cats">
                        <?php if ($is_category) { ?>
                            <?php foreach ($cat_blocks as $cat_block) { ?>
                                <ul class="docs-m-cats c">
                                <?php foreach($cat_block as $cat) {?>
                                    <li><span><a class="b-layout__link" href="/service/docs/section/?id=<?= $cat['id']; ?>"><?= trim(htmlspecialchars($cat['name'])); ?></a></span></li>
                                <?php } // foreach ?>
                                </ul>
                            <?php } // foreach ?>
                        <?php } else {// if ?>
                            <ul class="docs-m-cats c"><li style="color:red">Категории отсутствуют</li></ul>
                        <?php }// else  ?>
                    </div>
                    <div class="b-layout__txt b-layout__txt_padbot_10">Последние документы:</div>	 
                    <ul class="docs-m-last">	 
                        <?php if (is_array($last_docs) && count($last_docs)) {	 
                            foreach ($last_docs as $item) { ?>	 
                        <li><span class="d"><?= date('d.m.Y',  strtotime($item['date_create']));?></span>&nbsp; <a class="b-layout__link" href="/service/docs/section/?id=<?= $item['docs_sections_id'];?>#doc<?= $item['id']?>"><?= htmlspecialchars($item['name']);?></a></li>	 
                        <?php } // foreach	 
                        } else {// if ?>	 
                            <li style="color:red">Последние документы отсутствуют</li>	 
        <? }// else  ?>	 
                    </ul>
                    
                </div>
        </div>


